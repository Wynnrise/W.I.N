<?php
// ============================================================================
// plex_upload_c.php  —  Channel C — COV Building Permits Importer
// ============================================================================
// Wynston W.I.N  |  Session 19 Part B
//
// Two-phase upsert-safe importer for City of Vancouver building permits.
// Source CSV: https://opendata.vancouver.ca/ (Issued building permits dataset)
//
// Architecture v2.1 rules enforced:
//   - Only permits with unit_count 1-6 stay in Wynston scope
//   - Permits with unit_count > 6 are imported but flagged out-of-scope
//   - Permits with unit_count IS NULL (regex unknown) are SKIPPED entirely
//   - Shadow multi_2025 rows are created automatically for new in-scope permits
//   - Field ownership: COV sync NEVER touches developer-owned fields
//   - unit_count_confidence='admin' values are protected from re-import overwrite
//
// Flow:
//   POST mode=dry_run + csv file  →  parse, classify, stage to /tmp JSON, return token + counts
//   POST mode=commit  + token     →  read staged JSON, wrap in transaction, write, delete file
//
// All responses are JSON. Unicode-safe. Transaction-wrapped on commit.
// ============================================================================

// ── JSON fatal error guard ──────────────────────────────────────────────────
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Only emit if nothing has been sent yet
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo json_encode([
            'success' => false,
            'error'   => 'Fatal PHP error',
            'detail'  => $err['message'] . ' in ' . $err['file'] . ':' . $err['line'],
        ]);
    }
});

// ── Auth ────────────────────────────────────────────────────────────────────
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// ── DB ──────────────────────────────────────────────────────────────────────
$host = 'localhost';
$db   = 'u990588858_Property';
$user = 'u990588858_Multiplex';
$pass = 'Concac1979$';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

// ── Config ──────────────────────────────────────────────────────────────────
$WYNSTON_UNIT_CAP       = 6;                                // in-scope if 1..6
$STAGE_DIR              = sys_get_temp_dir();               // /tmp on Hostinger
$STAGE_PREFIX           = 'cov_import_';
$STAGE_MAX_AGE_SECONDS  = 24 * 3600;                        // lazy cleanup at 24h
$PREVIEW_SAMPLE_LIMIT   = 15;                               // rows shown per bucket in preview

// ── Route ───────────────────────────────────────────────────────────────────
$mode = $_POST['mode'] ?? '';

try {
    switch ($mode) {
        case 'dry_run':
            handle_dry_run($pdo);
            break;
        case 'commit':
            handle_commit($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing or invalid mode. Use dry_run or commit.']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Unhandled exception',
        'detail'  => $e->getMessage(),
    ]);
}

// ═══════════════════════════════════════════════════════════════════════════
//  DRY RUN — parse CSV, classify each row, stage to JSON, return token + preview
// ═══════════════════════════════════════════════════════════════════════════
function handle_dry_run(PDO $pdo) {
    global $STAGE_DIR, $STAGE_PREFIX, $STAGE_MAX_AGE_SECONDS, $PREVIEW_SAMPLE_LIMIT;

    // Input validation
    if (!isset($_FILES['permit_csv']) || $_FILES['permit_csv']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'No CSV file uploaded (or upload error)']);
        return;
    }
    $city = trim($_POST['city'] ?? 'vancouver');
    if (!preg_match('/^[a-z_]{1,20}$/', $city)) {
        echo json_encode(['success' => false, 'error' => 'Invalid city name']);
        return;
    }

    // Lazy cleanup of old staging files
    cleanup_stale_staging_files($STAGE_DIR, $STAGE_PREFIX, $STAGE_MAX_AGE_SECONDS);

    // Parse CSV
    $csv_path = $_FILES['permit_csv']['tmp_name'];
    $parse_result = parse_cov_csv($csv_path);
    if (!$parse_result['success']) {
        echo json_encode(['success' => false, 'error' => $parse_result['error']]);
        return;
    }
    $csv_rows = $parse_result['rows'];   // array of assoc arrays, keyed by CSV header
    $total_rows = count($csv_rows);

    if ($total_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'CSV contained no data rows']);
        return;
    }

    // Load existing permits in DB (by permit_number)
    $existing = [];
    $stmt = $pdo->prepare("SELECT permit_number, id, multi_2025_id, unit_count, unit_count_confidence
                           FROM A_Permit_2026 WHERE city = ?");
    $stmt->execute([$city]);
    foreach ($stmt->fetchAll() as $row) {
        $existing[$row['permit_number']] = $row;
    }

    // Classify each CSV row
    $classified = [];
    $buckets = [
        'new_in_scope'       => [],    // NEW, unit_count 1..6 → will INSERT + shadow
        'new_out_of_scope'   => [],    // NEW, unit_count > 6  → will INSERT, cov_present=0, NO shadow
        'new_unknown'        => [],    // NEW, unit_count NULL → SKIP (listed for review)
        'update_in_scope'    => [],    // EXISTS, parses to 1..6 or already set
        'update_out_of_scope'=> [],    // EXISTS, parses to >6  → UPDATE but flag out-of-scope
        'update_unknown'     => [],    // EXISTS, parses to NULL → UPDATE except unit_count
    ];

    foreach ($csv_rows as $csv_row) {
        $parsed = normalize_csv_row($csv_row);
        if (!$parsed['permit_number']) {
            // Skip malformed rows (no permit_number)
            continue;
        }

        $already = $existing[$parsed['permit_number']] ?? null;

        [$unit_count, $confidence] = parse_unit_count($parsed['description']);

        // Build the full staged row for later commit
        $staged = [
            'permit_number'   => $parsed['permit_number'],
            'address'         => $parsed['address'],
            'neighbourhood'   => $parsed['neighbourhood'],
            'permit_type'     => $parsed['permit_type'],
            'description'     => $parsed['description'],
            'applicant'       => $parsed['applicant'],
            'property_use'    => $parsed['property_use'],
            'project_value'   => $parsed['project_value'],
            'latitude'        => $parsed['latitude'],
            'longitude'       => $parsed['longitude'],
            'issue_date'      => $parsed['issue_date'],
            'year_month'      => $parsed['year_month'],
            'unit_count'      => $unit_count,
            'unit_count_conf' => $confidence,
            'existing_id'     => $already['id'] ?? null,
            'existing_multi'  => $already['multi_2025_id'] ?? null,
            'existing_uc_conf'=> $already['unit_count_confidence'] ?? null,
        ];

        // Classification logic
        if ($already === null) {
            // NEW permit
            if ($confidence === 'unknown') {
                $staged['bucket'] = 'new_unknown';
                $buckets['new_unknown'][] = $staged;
            } elseif ($unit_count !== null && $unit_count > 0 && $unit_count <= 6) {
                $staged['bucket'] = 'new_in_scope';
                $buckets['new_in_scope'][] = $staged;
            } else {
                // unit_count > 6
                $staged['bucket'] = 'new_out_of_scope';
                $buckets['new_out_of_scope'][] = $staged;
            }
        } else {
            // EXISTING permit
            if ($confidence === 'unknown') {
                $staged['bucket'] = 'update_unknown';
                $buckets['update_unknown'][] = $staged;
            } elseif ($unit_count !== null && $unit_count > 0 && $unit_count <= 6) {
                $staged['bucket'] = 'update_in_scope';
                $buckets['update_in_scope'][] = $staged;
            } else {
                $staged['bucket'] = 'update_out_of_scope';
                $buckets['update_out_of_scope'][] = $staged;
            }
        }

        $classified[] = $staged;
    }

    // Find orphans (in DB, not in this CSV)
    $csv_permit_numbers = array_flip(array_column($classified, 'permit_number'));
    $orphans = [];
    foreach ($existing as $pn => $row) {
        if (!isset($csv_permit_numbers[$pn])) {
            $orphans[] = ['permit_number' => $pn, 'id' => $row['id']];
        }
    }

    // Write staged data to /tmp
    $token = bin2hex(random_bytes(16));   // 32-hex-char token
    $stage_path = $STAGE_DIR . DIRECTORY_SEPARATOR . $STAGE_PREFIX . $token . '.json';

    $stage_payload = [
        'token'        => $token,
        'city'         => $city,
        'generated_at' => date('c'),
        'csv_total'    => $total_rows,
        'summary'      => [
            'new_in_scope'        => count($buckets['new_in_scope']),
            'new_out_of_scope'    => count($buckets['new_out_of_scope']),
            'new_unknown'         => count($buckets['new_unknown']),
            'update_in_scope'     => count($buckets['update_in_scope']),
            'update_out_of_scope' => count($buckets['update_out_of_scope']),
            'update_unknown'      => count($buckets['update_unknown']),
            'orphans'             => count($orphans),
        ],
        'rows'         => $classified,
        'orphans'      => $orphans,
    ];

    $write_ok = @file_put_contents($stage_path, json_encode($stage_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($write_ok === false) {
        echo json_encode(['success' => false, 'error' => 'Could not write staging file']);
        return;
    }

    // Build preview samples for the UI
    $preview_samples = [
        'new_in_scope'        => preview_sample($buckets['new_in_scope'], $PREVIEW_SAMPLE_LIMIT),
        'new_out_of_scope'    => preview_sample($buckets['new_out_of_scope'], $PREVIEW_SAMPLE_LIMIT),
        'new_unknown'         => preview_sample($buckets['new_unknown'], $PREVIEW_SAMPLE_LIMIT),
        'update_out_of_scope' => preview_sample($buckets['update_out_of_scope'], $PREVIEW_SAMPLE_LIMIT),
        'update_unknown'      => preview_sample($buckets['update_unknown'], $PREVIEW_SAMPLE_LIMIT),
    ];

    echo json_encode([
        'success'         => true,
        'token'           => $token,
        'city'            => $city,
        'csv_total'       => $total_rows,
        'summary'         => $stage_payload['summary'],
        'preview_samples' => $preview_samples,
    ]);
}

// ═══════════════════════════════════════════════════════════════════════════
//  COMMIT — read staged JSON by token, write to DB in a transaction
// ═══════════════════════════════════════════════════════════════════════════
function handle_commit(PDO $pdo) {
    global $STAGE_DIR, $STAGE_PREFIX, $WYNSTON_UNIT_CAP;

    $token = trim($_POST['token'] ?? '');
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
        echo json_encode(['success' => false, 'error' => 'Invalid token']);
        return;
    }
    $stage_path = $STAGE_DIR . DIRECTORY_SEPARATOR . $STAGE_PREFIX . $token . '.json';
    if (!is_file($stage_path)) {
        echo json_encode(['success' => false, 'error' => 'Staging file not found (may have expired)']);
        return;
    }

    $raw = @file_get_contents($stage_path);
    if ($raw === false) {
        echo json_encode(['success' => false, 'error' => 'Could not read staging file']);
        return;
    }
    $staged = json_decode($raw, true);
    if (!is_array($staged) || !isset($staged['rows'], $staged['orphans'], $staged['city'])) {
        echo json_encode(['success' => false, 'error' => 'Staging file is malformed']);
        return;
    }

    $city    = $staged['city'];
    $rows    = $staged['rows'];
    $orphans = $staged['orphans'];

    $counts = [
        'permits_inserted'     => 0,
        'permits_updated'      => 0,
        'permits_skipped'      => 0,   // new_unknown — held back for review
        'shadows_inserted'     => 0,
        'shadows_updated'      => 0,
        'shadows_skipped'      => 0,   // new_out_of_scope has no shadow
        'orphans_flagged'      => 0,
        'admin_uc_preserved'   => 0,   // count of rows where unit_count_confidence=admin protected the value
    ];

    $errors = [];
    $now = date('Y-m-d H:i:s');

    try {
        $pdo->beginTransaction();

        // Prepared statements
        $stmt_insert_permit = $pdo->prepare("
            INSERT INTO A_Permit_2026
              (permit_number, address, neighbourhood, permit_type, unit_count, unit_count_confidence,
               description, applicant, property_use, project_value,
               latitude, longitude, issue_date, `year_month`,
               show_on_plex_map, data_source, city,
               multi_2025_id, cov_last_synced, cov_present_in_latest_import)
            VALUES
              (:permit_number, :address, :neighbourhood, :permit_type, :unit_count, :unit_count_conf,
               :description, :applicant, :property_use, :project_value,
               :latitude, :longitude, :issue_date, :year_month,
               :show_on_plex_map, 'COV', :city,
               NULL, :now, :cov_present)
        ");

        $stmt_update_permit_full = $pdo->prepare("
            UPDATE A_Permit_2026
            SET address = :address,
                neighbourhood = :neighbourhood,
                permit_type = :permit_type,
                unit_count = :unit_count,
                unit_count_confidence = :unit_count_conf,
                description = :description,
                applicant = :applicant,
                property_use = :property_use,
                project_value = :project_value,
                latitude = :latitude,
                longitude = :longitude,
                issue_date = :issue_date,
                `year_month` = :year_month,
                city = :city,
                cov_last_synced = :now,
                cov_present_in_latest_import = :cov_present
            WHERE id = :id
        ");

        // UPDATE variant that PRESERVES unit_count + confidence (for admin-override rows)
        $stmt_update_permit_preserve_uc = $pdo->prepare("
            UPDATE A_Permit_2026
            SET address = :address,
                neighbourhood = :neighbourhood,
                permit_type = :permit_type,
                description = :description,
                applicant = :applicant,
                property_use = :property_use,
                project_value = :project_value,
                latitude = :latitude,
                longitude = :longitude,
                issue_date = :issue_date,
                `year_month` = :year_month,
                city = :city,
                cov_last_synced = :now,
                cov_present_in_latest_import = :cov_present
            WHERE id = :id
        ");

        $stmt_insert_shadow = $pdo->prepare("
            INSERT INTO multi_2025
              (address, neighborhood, latitude, longitude,
               submit_status, data_source, city, permit_id,
               cov_last_synced)
            VALUES
              (:address, :neighborhood, :latitude, :longitude,
               'cov_stub', 'cov_import', :city, :permit_id,
               :now)
        ");

        $stmt_link_permit_to_shadow = $pdo->prepare("
            UPDATE A_Permit_2026 SET multi_2025_id = :multi_id WHERE id = :permit_id
        ");

        $stmt_update_shadow_cov = $pdo->prepare("
            UPDATE multi_2025
            SET address = :address,
                neighborhood = :neighborhood,
                latitude = :latitude,
                longitude = :longitude,
                city = :city,
                cov_last_synced = :now
            WHERE id = :id
        ");

        $stmt_flag_orphan = $pdo->prepare("
            UPDATE A_Permit_2026 SET cov_present_in_latest_import = 0 WHERE id = :id
        ");

        // Process each staged row
        foreach ($rows as $r) {
            $bucket = $r['bucket'];

            // Skip buckets: new_unknown (held back for review)
            if ($bucket === 'new_unknown') {
                $counts['permits_skipped']++;
                continue;
            }

            // Determine in-scope flag
            $in_scope = in_array($bucket, ['new_in_scope', 'update_in_scope'], true);
            $cov_present = $in_scope ? 1 : 0;

            // Pre-bind common params
            $base_params = [
                ':permit_number' => $r['permit_number'],
                ':address'       => $r['address'],
                ':neighbourhood' => $r['neighbourhood'],
                ':permit_type'   => $r['permit_type'],
                ':description'   => $r['description'],
                ':applicant'     => $r['applicant'],
                ':property_use'  => $r['property_use'],
                ':project_value' => $r['project_value'],
                ':latitude'      => $r['latitude'],
                ':longitude'     => $r['longitude'],
                ':issue_date'    => $r['issue_date'],
                ':year_month'    => $r['year_month'],
                ':city'          => $city,
                ':now'           => $now,
                ':cov_present'   => $cov_present,
            ];

            if (in_array($bucket, ['new_in_scope', 'new_out_of_scope'], true)) {
                // ── INSERT permit ──
                $params = $base_params + [
                    ':unit_count'       => $r['unit_count'],
                    ':unit_count_conf'  => $r['unit_count_conf'],
                    ':show_on_plex_map' => $in_scope ? 1 : 0,
                ];
                try {
                    $stmt_insert_permit->execute($params);
                } catch (PDOException $e) {
                    $errors[] = "INSERT permit {$r['permit_number']}: " . $e->getMessage();
                    continue;
                }
                $new_permit_id = (int)$pdo->lastInsertId();
                $counts['permits_inserted']++;

                // Only in-scope new permits get a shadow row
                if ($in_scope) {
                    try {
                        $stmt_insert_shadow->execute([
                            ':address'      => $r['address'],
                            ':neighborhood' => $r['neighbourhood'],
                            ':latitude'     => $r['latitude'],
                            ':longitude'    => $r['longitude'],
                            ':city'         => $city,
                            ':permit_id'    => $new_permit_id,
                            ':now'          => $now,
                        ]);
                        $new_shadow_id = (int)$pdo->lastInsertId();
                        $stmt_link_permit_to_shadow->execute([
                            ':multi_id'  => $new_shadow_id,
                            ':permit_id' => $new_permit_id,
                        ]);
                        $counts['shadows_inserted']++;
                    } catch (PDOException $e) {
                        $errors[] = "INSERT shadow for permit {$r['permit_number']}: " . $e->getMessage();
                        continue;
                    }
                } else {
                    $counts['shadows_skipped']++;
                }

            } elseif (in_array($bucket, ['update_in_scope', 'update_out_of_scope', 'update_unknown'], true)) {
                // ── UPDATE permit ──
                $permit_id = (int)$r['existing_id'];

                // Admin-override preservation: if existing confidence is 'admin',
                // do NOT overwrite unit_count or unit_count_confidence
                $admin_locked = ($r['existing_uc_conf'] === 'admin');
                if ($admin_locked) {
                    $counts['admin_uc_preserved']++;
                }

                // For update_unknown, also preserve unit_count (we couldn't parse a new one)
                // For update_*_of_scope with admin lock, preserve
                $should_preserve_uc = $admin_locked || ($bucket === 'update_unknown');

                // Build UPDATE params — strip :permit_number (UPDATE SQL doesn't use it,
                // and PDO in strict mode rejects unused named params)
                $update_base = $base_params;
                unset($update_base[':permit_number']);

                try {
                    if ($should_preserve_uc) {
                        $params = $update_base + [':id' => $permit_id];
                        $stmt_update_permit_preserve_uc->execute($params);
                    } else {
                        $params = $update_base + [
                            ':unit_count'      => $r['unit_count'],
                            ':unit_count_conf' => $r['unit_count_conf'],
                            ':id'              => $permit_id,
                        ];
                        $stmt_update_permit_full->execute($params);
                    }
                    $counts['permits_updated']++;
                } catch (PDOException $e) {
                    $errors[] = "UPDATE permit {$r['permit_number']}: " . $e->getMessage();
                    continue;
                }

                // Update existing shadow row (COV-owned fields only), if shadow exists
                if (!empty($r['existing_multi'])) {
                    try {
                        $stmt_update_shadow_cov->execute([
                            ':address'      => $r['address'],
                            ':neighborhood' => $r['neighbourhood'],
                            ':latitude'     => $r['latitude'],
                            ':longitude'    => $r['longitude'],
                            ':city'         => $city,
                            ':now'          => $now,
                            ':id'           => (int)$r['existing_multi'],
                        ]);
                        $counts['shadows_updated']++;
                    } catch (PDOException $e) {
                        $errors[] = "UPDATE shadow for permit {$r['permit_number']}: " . $e->getMessage();
                    }
                }
            }
        }

        // Flag orphans
        foreach ($orphans as $orphan) {
            try {
                $stmt_flag_orphan->execute([':id' => (int)$orphan['id']]);
                $counts['orphans_flagged']++;
            } catch (PDOException $e) {
                $errors[] = "FLAG orphan permit {$orphan['permit_number']}: " . $e->getMessage();
            }
        }

        $pdo->commit();

        // Delete staging file after successful commit
        @unlink($stage_path);

        echo json_encode([
            'success' => true,
            'message' => 'Import committed successfully',
            'counts'  => $counts,
            'errors'  => $errors,
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode([
            'success' => false,
            'error'   => 'Commit failed — all changes rolled back',
            'detail'  => $e->getMessage(),
            'counts_before_fail' => $counts,
            'errors'  => $errors,
        ]);
    }
}

// ═══════════════════════════════════════════════════════════════════════════
//  CSV PARSING
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Parse COV-format building permit CSV.
 * - UTF-8 BOM tolerated (stripped if present)
 * - Semicolon delimiter
 * - Double-quote enclosure
 * - Embedded newlines inside quoted fields supported (fgetcsv handles natively)
 * Returns: ['success'=>bool, 'rows'=>[...], 'error'=>string|null]
 */
function parse_cov_csv(string $path): array {
    if (!is_readable($path)) {
        return ['success' => false, 'error' => 'CSV file not readable', 'rows' => []];
    }
    $fh = @fopen($path, 'r');
    if (!$fh) {
        return ['success' => false, 'error' => 'Could not open CSV', 'rows' => []];
    }

    // Strip BOM if present
    $bom = fread($fh, 3);
    if ($bom !== "\xef\xbb\xbf") {
        rewind($fh);
    }

    $header = fgetcsv($fh, 0, ';', '"', '\\');
    if (!$header || count($header) < 5) {
        fclose($fh);
        return ['success' => false, 'error' => 'CSV header not found or too short', 'rows' => []];
    }

    // Required columns
    $required = ['PermitNumber', 'Address', 'IssueDate', 'geo_point_2d'];
    $missing = array_diff($required, $header);
    if (!empty($missing)) {
        fclose($fh);
        return [
            'success' => false,
            'error'   => 'CSV is missing required columns: ' . implode(', ', $missing),
            'rows'    => [],
        ];
    }

    $rows = [];
    while (($cols = fgetcsv($fh, 0, ';', '"', '\\')) !== false) {
        // Skip entirely-blank rows
        if (count(array_filter($cols, fn($v) => $v !== '' && $v !== null)) === 0) {
            continue;
        }
        // Pad or trim to header length
        if (count($cols) < count($header)) {
            $cols = array_pad($cols, count($header), '');
        } elseif (count($cols) > count($header)) {
            $cols = array_slice($cols, 0, count($header));
        }
        $rows[] = array_combine($header, $cols);
    }
    fclose($fh);

    return ['success' => true, 'rows' => $rows, 'error' => null];
}

/**
 * Map CSV raw fields to normalized schema-friendly values.
 * Returns keys matching A_Permit_2026 columns (where applicable).
 */
function normalize_csv_row(array $r): array {
    // Parse lat/lng from geo_point_2d (format: "49.2867542, -123.0449035")
    $lat = null;
    $lng = null;
    $geo = trim($r['geo_point_2d'] ?? '');
    if ($geo !== '') {
        $parts = array_map('trim', explode(',', $geo));
        if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
            $lat = (float)$parts[0];
            $lng = (float)$parts[1];
        }
    }

    // Parse issue_date (YYYY-MM-DD)
    $issue_date = trim($r['IssueDate'] ?? '');
    if ($issue_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $issue_date)) {
        $issue_date = null;
    }
    if ($issue_date === '') $issue_date = null;

    // Derive year_month (use CSV value, or compute from issue_date)
    $year_month = trim($r['YearMonth'] ?? '');
    if ($year_month === '' && $issue_date) {
        $year_month = substr($issue_date, 0, 7);
    }
    if ($year_month === '') $year_month = null;

    // Project value — cast float → int (CSV has "350000.0")
    $project_value = null;
    $pv_raw = trim($r['ProjectValue'] ?? '');
    if ($pv_raw !== '' && is_numeric($pv_raw)) {
        $project_value = (int)round((float)$pv_raw);
    }

    return [
        'permit_number' => trim($r['PermitNumber'] ?? ''),
        'address'       => trim($r['Address'] ?? ''),
        'neighbourhood' => trim($r['GeoLocalArea'] ?? '') ?: null,
        'permit_type'   => trim($r['TypeOfWork'] ?? '') ?: null,
        'description'   => trim($r['ProjectDescription'] ?? '') ?: null,
        'applicant'     => trim($r['Applicant'] ?? '') ?: null,
        'property_use'  => trim($r['PropertyUse'] ?? '') ?: null,
        'project_value' => $project_value,
        'latitude'      => $lat,
        'longitude'     => $lng,
        'issue_date'    => $issue_date,
        'year_month'    => $year_month,
    ];
}

/**
 * Extract unit count from COV ProjectDescription.
 * Returns [int|null unit_count, string 'parsed'|'unknown'].
 * Strategy:
 *   1. Find digit-followed-by-unit patterns ("2-unit", "3 unit", "4 units")
 *   2. If multiple matches, disagree, and small enough → sum (covers front+rear splits)
 *   3. Fallback: "(N) unit" pattern
 *   4. Fallback: word-number ("eight unit")
 *   5. Give up → 'unknown'
 */
function parse_unit_count(?string $desc): array {
    if ($desc === null || trim($desc) === '') {
        return [null, 'unknown'];
    }
    $text = strtolower(str_replace(["\n", "\r"], ' ', $desc));

    // Pattern 1: digit + optional hyphen/space + "unit(s)"
    if (preg_match_all('/(\d+)[\-\s]*units?\b/', $text, $m) && !empty($m[1])) {
        $nums = array_map('intval', $m[1]);
        $uniq = array_unique($nums);
        if (count($uniq) === 1) {
            return [$uniq[0], 'parsed'];
        }
        // Disagree → try sum (front+rear splits like "2-unit front, 1-unit rear")
        $all_small = true;
        foreach ($nums as $n) {
            if ($n > 8) { $all_small = false; break; }
        }
        $sum = array_sum($nums);
        if ($all_small && $sum > 0 && $sum <= 12) {
            return [$sum, 'parsed'];
        }
        return [null, 'unknown'];
    }

    // Pattern 2: "(N) unit(s)"
    if (preg_match('/\((\d+)\)\s*units?\b/', $text, $m)) {
        return [(int)$m[1], 'parsed'];
    }

    // Pattern 3: word-number + "unit(s)"
    $words = [
        'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6,
        'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10, 'eleven' => 11, 'twelve' => 12,
    ];
    foreach ($words as $w => $n) {
        if (preg_match('/\b' . $w . '[\-\s]+units?\b/', $text)) {
            return [$n, 'parsed'];
        }
    }

    return [null, 'unknown'];
}

// ═══════════════════════════════════════════════════════════════════════════
//  HELPERS
// ═══════════════════════════════════════════════════════════════════════════

function preview_sample(array $bucket, int $limit): array {
    $out = [];
    foreach (array_slice($bucket, 0, $limit) as $row) {
        $desc = $row['description'] ?? '';
        $out[] = [
            'permit_number' => $row['permit_number'],
            'address'       => $row['address'],
            'neighbourhood' => $row['neighbourhood'] ?? '',
            'unit_count'    => $row['unit_count'],
            'confidence'    => $row['unit_count_conf'],
            'desc_preview'  => mb_substr(preg_replace('/\s+/', ' ', $desc), 0, 140),
        ];
    }
    return $out;
}

function cleanup_stale_staging_files(string $dir, string $prefix, int $max_age): void {
    $pattern = $dir . DIRECTORY_SEPARATOR . $prefix . '*.json';
    $files = glob($pattern) ?: [];
    $now = time();
    foreach ($files as $f) {
        if (is_file($f) && ($now - filemtime($f)) > $max_age) {
            @unlink($f);
        }
    }
}