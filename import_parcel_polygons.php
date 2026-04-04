<?php
/**
 * import_parcel_polygons.php
 * One-time import of property-parcel-polygons.csv
 * Fills lat/lng + lot_area_sqm + lot_width_m for all lots missing coordinates
 * Run via SSH: php import_parcel_polygons.php
 */

set_time_limit(0);
ini_set('memory_limit', '256M');

$pdo = new PDO('mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4',
    'u990588858_Multiplex', 'Concac1979$',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$csv     = __DIR__ . '/data/property-parcel-polygons.csv';
$log     = __DIR__ . '/logs/parcel_import_' . date('Y-m-d_H-i-s') . '.log';

function lg($msg, $log) {
    $l = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents($log, $l, FILE_APPEND);
    echo $l;
}

if (!file_exists($csv)) die("CSV not found: $csv\n");

lg('Starting parcel polygon import', $log);
lg('File size: ' . round(filesize($csv)/1048576, 1) . ' MB', $log);

// ── Geometry helpers ─────────────────────────────────────────
function polygonAreaSqm(array $ring): float {
    $n = count($ring); if ($n < 3) return 0;
    $latC = array_sum(array_column($ring, 1)) / $n;
    $mLat = 111320.0; $mLng = 111320.0 * cos(deg2rad($latC));
    $sum = 0.0;
    for ($i = 0; $i < $n; $i++) {
        $j = ($i+1) % $n;
        $sum += ($ring[$i][0]*$mLng * $ring[$j][1]*$mLat)
              - ($ring[$j][0]*$mLng * $ring[$i][1]*$mLat);
    }
    return abs($sum) / 2.0;
}

function polygonFrontageM(array $ring): float {
    $latC = array_sum(array_column($ring, 1)) / count($ring);
    $mLat = 111320.0; $mLng = 111320.0 * cos(deg2rad($latC));
    $xs = array_map(fn($p) => $p[0]*$mLng, $ring);
    $ys = array_map(fn($p) => $p[1]*$mLat, $ring);
    return min(max($xs)-min($xs), max($ys)-min($ys));
}

// ── Open CSV ──────────────────────────────────────────────────
$handle = fopen($csv, 'r');
$first  = fgets($handle); rewind($handle);
$delim  = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
lg("Delimiter: $delim", $log);

$raw_header = fgetcsv($handle, 0, $delim);
$header = array_map(function($h) {
    $h = trim($h);
    $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
    return strtolower($h);
}, $raw_header);

lg('Columns: ' . implode(', ', $header), $log);

$col = array_flip($header);

// Verify required columns
foreach (['site_id', 'geo_point_2d'] as $req) {
    if (!isset($col[$req])) {
        die("Missing required column: $req — check CSV headers\n");
    }
}

// Check if geom column exists (for area/frontage calculation)
$has_geom = isset($col['geom']);
lg("Geometry column available: " . ($has_geom ? 'yes' : 'no — will use geo_point_2d only'), $log);

// ── Prepared statement ────────────────────────────────────────
$stmt = $pdo->prepare("
    UPDATE plex_properties
    SET lat          = :lat,
        lng          = :lng,
        lot_area_sqm = :area,
        lot_width_m  = :width,
        updated_at   = NOW()
    WHERE pid = :pid
      AND lat IS NULL
");

// ── Process rows ──────────────────────────────────────────────
$processed = 0;
$updated   = 0;
$skipped   = 0;

while (($row = fgetcsv($handle, 0, $delim)) !== false) {
    $processed++;

    $data = [];
    foreach ($header as $i => $col_name) {
        $data[$col_name] = trim($row[$i] ?? '');
    }

    // site_id → pid (remove dashes format: 013092839 → 013-092-839)
    $site_id = $data['site_id'] ?? '';
    if (empty($site_id) || strlen($site_id) < 9) { $skipped++; continue; }
    $pid = substr($site_id,0,3).'-'.substr($site_id,3,3).'-'.substr($site_id,6);

    // geo_point_2d — could be JSON string "{"lon":-123.1,"lat":49.2}"
    // or separate columns depending on CSV export format
    $lat = null; $lng = null;
    $geo_raw = $data['geo_point_2d'] ?? '';

    if (!empty($geo_raw)) {
        // Try JSON parse first
        $geo = json_decode($geo_raw, true);
        if (isset($geo['lat'], $geo['lon'])) {
            $lat = (float)$geo['lat'];
            $lng = (float)$geo['lon'];
        } elseif (isset($geo['latitude'], $geo['longitude'])) {
            $lat = (float)$geo['latitude'];
            $lng = (float)$geo['longitude'];
        } else {
            // Try "lat,lng" format
            $parts = explode(',', $geo_raw);
            if (count($parts) === 2) {
                $lat = (float)trim($parts[0]);
                $lng = (float)trim($parts[1]);
            }
        }
    }

    // Also check for separate lat/lon columns
    if ($lat === null && isset($col['latitude'], $col['longitude'])) {
        $lat = !empty($data['latitude'])  ? (float)$data['latitude']  : null;
        $lng = !empty($data['longitude']) ? (float)$data['longitude'] : null;
    }

    if ($lat === null || $lng === null || $lat == 0 || $lng == 0) {
        $skipped++;
        continue;
    }

    // Calculate area + frontage from geom if available
    $area = null; $width = null;
    if ($has_geom && !empty($data['geom'])) {
        $geom = json_decode($data['geom'], true);
        $ring = $geom['geometry']['coordinates'][0]
             ?? $geom['coordinates'][0]
             ?? null;
        if ($ring && count($ring) >= 3) {
            $area  = round(polygonAreaSqm($ring), 2);
            $width = round(polygonFrontageM($ring), 2);
        }
    }

    try {
        $stmt->execute([
            ':pid'   => $pid,
            ':lat'   => $lat,
            ':lng'   => $lng,
            ':area'  => $area,
            ':width' => $width,
        ]);
        if ($stmt->rowCount() > 0) $updated++;
    } catch (Exception $e) {
        // PID not in plex_properties — not an R1-1 lot, ignore
    }

    if ($processed % 10000 === 0) {
        lg("Progress: $processed rows, $updated coords updated", $log);
    }
}

fclose($handle);

lg('=== IMPORT COMPLETE ===', $log);
lg("Rows processed: $processed", $log);
lg("Coords updated: $updated", $log);
lg("Skipped:        $skipped", $log);
