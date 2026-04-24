<?php
/**
 * api/permits.php
 * Returns A_Permit_2026 (COV building permits) as GeoJSON for the map.
 * No auth required — permit data is public record.
 *
 * Session 20 changes:
 *  - Added cov_present_in_latest_import = 1 filter (hides out-of-scope condos
 *    and orphaned permits from the map).
 *  - LEFT JOIN multi_2025 on multi_2025_id FK (Session 19 Part A schema).
 *    Returns listing_* fields inline so the client doesn't need to do
 *    address matching via api/permit_listings.php. That endpoint is still
 *    active as a fallback during transition.
 *  - Returns unit_count, unit_count_confidence, multi_2025_id for side panel.
 *  - Defensive column inspection on multi_2025 and A_Permit_2026 to avoid
 *    breaking if schema drifts between environments (dev/staging/prod).
 *
 * Session 20 Option B (finalized):
 *  - Enrichment gated ONLY on submit_status = 'approved'. The
 *    multi_2025.show_on_plex_map flag is IGNORED for enrichment purposes.
 *    Rationale: admin approval IS the live gate. Historical approved
 *    listings had show_on_plex_map=0 because the submit/approve flow
 *    defaulted it and never flipped it, which suppressed banners even
 *    though the FK linkage was correct. Approval = visible.
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode([
            'type'     => 'FeatureCollection',
            'features' => [],
            'error'    => 'internal_error',
            'message'  => $err['message']
        ]);
    }
});

// ── DB connection ──────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4",
        "u990588858_Multiplex",
        "Concac1979\$",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    echo json_encode(['type' => 'FeatureCollection', 'features' => [], 'error' => 'db_connect_failed']);
    exit;
}

// ── Defensive column inspection on multi_2025 ──────────────
try {
    $multi_cols_raw = $pdo->query("DESCRIBE multi_2025")->fetchAll(PDO::FETCH_COLUMN);
    $multi_cols = array_flip($multi_cols_raw);
} catch (Exception $e) {
    $multi_cols = [];
}

$col_or_null = function ($col, $alias = null) use ($multi_cols) {
    $alias = $alias ?? $col;
    return isset($multi_cols[$col]) ? "m.`$col` AS `$alias`" : "NULL AS `$alias`";
};

// ── Check for multi_2025_id on A_Permit_2026 ───────────────
try {
    $permit_cols_raw = $pdo->query("DESCRIBE A_Permit_2026")->fetchAll(PDO::FETCH_COLUMN);
    $permit_cols = array_flip($permit_cols_raw);
} catch (Exception $e) {
    $permit_cols = [];
}

$has_fk              = isset($permit_cols['multi_2025_id']);
$has_present_filter  = isset($permit_cols['cov_present_in_latest_import']);
$has_unit_count      = isset($permit_cols['unit_count']);
$has_unit_confidence = isset($permit_cols['unit_count_confidence']);

// ── Build permit SELECT list ───────────────────────────────
$permit_select = [
    "p.id",
    "p.permit_number",
    "p.address",
    "p.neighbourhood",
    "p.permit_type",
    "p.description",
    "p.applicant",
    "p.property_use",
    "p.latitude",
    "p.longitude",
    "p.issue_date",
    "p.`year_month`",
];
if ($has_unit_count)      $permit_select[] = "p.unit_count";
if ($has_unit_confidence) $permit_select[] = "p.unit_count_confidence";
if ($has_fk)              $permit_select[] = "p.multi_2025_id";

// ── Build listing SELECT list (only if FK + multi_2025 both exist) ──
// Session 20 Option B: show_on_plex_map is NOT part of enrichment logic.
$listing_select = [];
$join_clause    = "";
if ($has_fk && !empty($multi_cols)) {
    $listing_select = [
        "m.`id` AS `listing_id`",
        "m.`submit_status` AS `listing_status`",
        $col_or_null('tier', 'listing_tier'),
        $col_or_null('is_paid', 'listing_is_paid'),
        $col_or_null('img1', 'listing_img1'),
        $col_or_null('developer_name', 'listing_developer_name'),
        $col_or_null('submitted_by', 'listing_submitted_by'),
    ];
    $join_clause = "LEFT JOIN multi_2025 m ON p.multi_2025_id = m.id";
}

// ── Build WHERE clause ─────────────────────────────────────
$where_parts = [
    "p.show_on_plex_map = 1",
    "p.latitude IS NOT NULL",
    "p.longitude IS NOT NULL",
];
if ($has_present_filter) {
    $where_parts[] = "p.cov_present_in_latest_import = 1";
}
$where_sql = "WHERE " . implode("\n  AND ", $where_parts);

// ── Assemble and run query ─────────────────────────────────
$select_sql = implode(",\n       ", array_merge($permit_select, $listing_select));
$sql = "SELECT $select_sql
        FROM A_Permit_2026 p
        $join_clause
        $where_sql
        ORDER BY p.issue_date DESC";

try {
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'type'     => 'FeatureCollection',
        'features' => [],
        'error'    => $e->getMessage()
    ]);
    exit;
}

// ── Developer lookup (one query, in-memory join) ────────────
$dev_names = [];
if (!empty($multi_cols) && isset($multi_cols['submitted_by'])) {
    $dev_ids = [];
    foreach ($rows as $r) {
        if (!empty($r['listing_submitted_by'])) {
            $dev_ids[(int)$r['listing_submitted_by']] = true;
        }
    }
    if (!empty($dev_ids)) {
        try {
            $id_list = implode(',', array_map('intval', array_keys($dev_ids)));
            $dev_rows = $pdo->query("
                SELECT id, full_name, company_name
                FROM developers
                WHERE id IN ($id_list)
            ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($dev_rows as $d) {
                $dev_names[(int)$d['id']] = [
                    'full_name'    => $d['full_name']    ?? '',
                    'company_name' => $d['company_name'] ?? '',
                ];
            }
        } catch (Exception $e) {
            // Non-fatal
        }
    }
}

// ── Shape features ─────────────────────────────────────────
$features = [];
foreach ($rows as $row) {

    // Listing enrichment — Session 20 Option B
    //   1. Listing row actually exists (LEFT JOIN hit, listing_id not null)
    //   2. submit_status = 'approved' (admin approval = live, per Tam)
    //   show_on_plex_map is NOT checked. Approval alone is the gate.
    $has_listing        = false;
    $listing_detail_url = null;
    $listing_tier       = null;
    $listing_thumb      = null;
    $developer_name     = null;
    $listing_id         = null;

    if (!empty($row['listing_id'])
        && isset($row['listing_status'])
        && $row['listing_status'] === 'approved'
    ) {
        $has_listing = true;
        $listing_id  = (int)$row['listing_id'];

        // Tier resolution
        $tier = strtolower(trim((string)($row['listing_tier'] ?? '')));
        if ($tier === '') {
            $tier = !empty($row['listing_is_paid']) ? 'concierge' : 'free';
        }
        $listing_tier = $tier;

        // Detail URL — concierge gets its dedicated page;
        // free and creative share single-property-2.php
        $listing_detail_url = ($tier === 'concierge')
            ? '/concierge-property.php?id=' . $listing_id
            : '/single-property-2.php?id=' . $listing_id;

        // Thumbnail
        $img = trim((string)($row['listing_img1'] ?? ''));
        $listing_thumb = $img !== '' ? $img : null;

        // Developer display name
        $dev_display = trim((string)($row['listing_developer_name'] ?? ''));
        if ($dev_display === '' && !empty($row['listing_submitted_by'])) {
            $did = (int)$row['listing_submitted_by'];
            if (isset($dev_names[$did])) {
                $dev_display = trim($dev_names[$did]['company_name'] ?? '');
                if ($dev_display === '') {
                    $dev_display = trim($dev_names[$did]['full_name'] ?? '');
                }
            }
        }
        $developer_name = $dev_display !== '' ? $dev_display : 'Wynston Developer';
    }

    $props = [
        'id'            => $row['id'],
        'permit_number' => $row['permit_number'],
        'address'       => $row['address'],
        'neighbourhood' => $row['neighbourhood'],
        'permit_type'   => $row['permit_type'],
        'description'   => $row['description'],
        'applicant'     => $row['applicant'],
        'property_use'  => $row['property_use'],
        'issue_date'    => $row['issue_date'],
        'year_month'    => $row['year_month'],
        'marker_type'   => 'permit',
        // Session 20 additions
        'unit_count'            => $row['unit_count']            ?? null,
        'unit_count_confidence' => $row['unit_count_confidence'] ?? null,
        'multi_2025_id'         => $row['multi_2025_id']         ?? null,
        'has_listing'           => $has_listing,
        'listing_id'            => $listing_id,
        'listing_tier'          => $listing_tier,
        'listing_thumbnail'     => $listing_thumb,
        'listing_detail_url'    => $listing_detail_url,
        'developer_name'        => $developer_name,
    ];

    $features[] = [
        'type'       => 'Feature',
        'geometry'   => [
            'type'        => 'Point',
            'coordinates' => [(float)$row['longitude'], (float)$row['latitude']],
        ],
        'properties' => $props,
    ];
}

echo json_encode([
    'type'     => 'FeatureCollection',
    'features' => $features
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);