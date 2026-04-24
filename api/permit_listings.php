<?php
// ============================================================
//  api/permit_listings.php  —  Session 18
//
//  Standalone enrichment endpoint for the Plex Map.
//  Returns a lookup map of { normalized_address: listing_info }
//  so the map client can detect which permit pins have an
//  active Wynston listing and render thumbnails + View Details.
//
//  Address matching — A_Permit_2026 has NO pid column, so the
//  only way to match a permit to a listing is by address. We
//  normalize aggressively (lowercase, strip punctuation, strip
//  unit prefixes) and the client does the same normalization
//  on the permit address before looking up.
//
//  PUBLIC ENDPOINT — no auth required. Returns only data already
//  visible on /half-map.php and /single-property-2.php. Also
//  serves as the foundation for /api/completed-listings.php
//  (future realtor-facing API).
//
//  Optional query params:
//    ?debug=1    — adds _meta block with row counts + raw addresses
//    ?format=array — returns an array of objects instead of a
//                     keyed map (easier for API consumers)
// ============================================================

// ── Fatal-error handler so API always returns JSON, never HTML ──
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');  // 5-minute cache for map performance

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode([
            'ok' => false,
            'error' => 'internal_error',
            'message' => $err['message']
        ]);
    }
});

// ── DB connection ────────────────────────────────────────────
$host = 'localhost';
$db   = 'u990588858_Property';
$user = 'u990588858_Multiplex';
$pass = 'Concac1979$';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'db_connect_failed']);
    exit;
}

// ── Address normalizer ───────────────────────────────────────
// Must match the client-side normalizer in plex-map/index.php.
//
// Handles two common Vancouver formats:
//   (a) COV permit style:  "481 W 17TH AVENUE, Vancouver, BC V5Y 2A1"
//   (b) Internal style:    "481 17TH AVE W"
// Both must collapse to the same normalized key, e.g. "481 17 ave w".
//
// Strategy:
//   1. Lowercase, trim
//   2. Strip unit/suite prefixes
//   3. Strip ", vancouver, bc [postal]" suffix
//   4. Strip punctuation
//   5. Normalize street-type abbreviations
//   6. Move leading directional (W / E / N / S) to trailing position
//      so "481 w 17 ave" and "481 17 ave w" collide
//   7. Strip ordinal suffixes from numbers (17th -> 17)
//   8. Collapse whitespace
function normalize_address($addr) {
    if (!$addr) return '';
    $s = strtolower(trim($addr));

    // Strip unit/suite prefixes
    $s = preg_replace('/^(#|unit\s+|suite\s+|apt\s+|apartment\s+)[\w\-]+\s*[-,]?\s*/i', '', $s);

    // Strip everything after the first comma — this kills city/province/postal suffixes in one shot
    if (($comma = strpos($s, ',')) !== false) {
        $s = substr($s, 0, $comma);
    }

    // Strip remaining punctuation
    $s = str_replace(['.', ',', "'", '"', '#'], '', $s);

    // Normalize street types (long form -> short)
    $s = preg_replace('/\bstreet\b/', 'st', $s);
    $s = preg_replace('/\bavenue\b/', 'ave', $s);
    $s = preg_replace('/\bboulevard\b/', 'blvd', $s);
    $s = preg_replace('/\bdrive\b/', 'dr', $s);
    $s = preg_replace('/\broad\b/', 'rd', $s);
    $s = preg_replace('/\bplace\b/', 'pl', $s);
    $s = preg_replace('/\bcrescent\b/', 'cres', $s);
    $s = preg_replace('/\bcourt\b/', 'ct', $s);
    $s = preg_replace('/\bhighway\b/', 'hwy', $s);
    $s = preg_replace('/\bparkway\b/', 'pkwy', $s);
    $s = preg_replace('/\blane\b/', 'ln', $s);

    // Strip ordinal suffixes from numbers: "17th" -> "17", "1st" -> "1", "2nd" -> "2", "3rd" -> "3"
    $s = preg_replace('/(\d+)(st|nd|rd|th)\b/', '$1', $s);

    // Collapse whitespace (needed before the directional rearrange)
    $s = preg_replace('/\s+/', ' ', $s);
    $s = trim($s);

    // Reorder directional: "481 w 17 ave" -> "481 17 ave w"
    // Matches: <number> <direction> <rest> -> <number> <rest> <direction>
    if (preg_match('/^(\d+)\s+(n|s|e|w|north|south|east|west)\s+(.+)$/', $s, $m)) {
        $dir = $m[2];
        if ($dir === 'north') $dir = 'n';
        elseif ($dir === 'south') $dir = 's';
        elseif ($dir === 'east')  $dir = 'e';
        elseif ($dir === 'west')  $dir = 'w';
        $s = $m[1] . ' ' . $m[3] . ' ' . $dir;
    }

    // Also normalize trailing "west"/"east" etc to single letter
    $s = preg_replace('/\b(north|south|east|west)\b/', function($m) {
        return $m[1][0];
    }, $s);
    // preg_replace doesn't take callbacks; use preg_replace_callback
    $s = preg_replace_callback('/\b(north|south|east|west)\b/', function($m) {
        return $m[1][0];
    }, $s);

    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

// ── Coordinate key builder (fallback when address matching fails) ──
// Rounds lat/lng to 4 decimals (~11m precision) and creates a string key.
function coord_key($lat, $lng) {
    if ($lat === null || $lng === null || $lat === '' || $lng === '') return '';
    $lat = round((float)$lat, 4);
    $lng = round((float)$lng, 4);
    return sprintf('%.4f,%.4f', $lat, $lng);
}

// ── Query listings ───────────────────────────────────────────
// Filter to approved/live rows only, with at least one image.
// Build rich response so this endpoint can double as the realtor
// API foundation (image array, description, price, developer info).
//
// DEFENSIVE: multi_2025 schema has varied over sessions. We first
// inspect the actual columns and only select what exists. Missing
// fields are returned as null in the response.
try {
    $cols_raw = $pdo->query("DESCRIBE multi_2025")->fetchAll(PDO::FETCH_COLUMN);
    $cols = array_flip($cols_raw);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'schema_check_failed', 'message' => $e->getMessage()]);
    exit;
}

// Helper: return "m.<col>" if col exists, else "NULL" aliased to the requested name
function col_or_null($cols, $col, $alias = null) {
    $alias = $alias ?? $col;
    return isset($cols[$col]) ? "m.`$col` AS `$alias`" : "NULL AS `$alias`";
}

// Resolve the neighbourhood column — schema has used several names
$nb_expr = "NULL AS `neighbourhood`";
foreach (['neighbourhood', 'neighborhood', 'neighbourhood_slug', 'nb', 'area'] as $nb_col) {
    if (isset($cols[$nb_col])) {
        $nb_expr = "m.`$nb_col` AS `neighbourhood`";
        break;
    }
}

$select_parts = [
    "m.`id`",
    "m.`address`",
    col_or_null($cols, 'latitude'),
    col_or_null($cols, 'longitude'),
    $nb_expr,
    col_or_null($cols, 'property_type'),
    col_or_null($cols, 'price'),
    col_or_null($cols, 'bedrooms'),
    col_or_null($cols, 'bathrooms'),
    col_or_null($cols, 'sqft'),
    col_or_null($cols, 'description'),
    col_or_null($cols, 'img1'),
    col_or_null($cols, 'img2'),
    col_or_null($cols, 'img3'),
    col_or_null($cols, 'img4'),
    col_or_null($cols, 'img5'),
    col_or_null($cols, 'img6'),
    col_or_null($cols, 'developer_name'),
    col_or_null($cols, 'developer_bio'),
    col_or_null($cols, 'builder_logo'),
    col_or_null($cols, 'virtual_tour_url'),
    col_or_null($cols, 'video_url'),
    "m.`submit_status`",
    col_or_null($cols, 'tier'),
    col_or_null($cols, 'is_paid'),
    col_or_null($cols, 'pid'),
    col_or_null($cols, 'created_at'),
    col_or_null($cols, 'submitted_by'),
];

try {
    $sql = "SELECT " . implode(",\n                ", $select_parts) . ",
                d.full_name     AS dev_full_name,
                d.company_name  AS dev_company_name
            FROM multi_2025 m
            LEFT JOIN developers d ON d.id = m.submitted_by
            WHERE m.submit_status IN ('approved', 'live')
              AND m.img1 IS NOT NULL
              AND m.img1 <> ''
            ORDER BY m.id DESC";
    $rows = $pdo->query($sql)->fetchAll();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'query_failed', 'message' => $e->getMessage()]);
    exit;
}

// ── Shape listings for response ──────────────────────────────
$debug  = !empty($_GET['debug']);
$format = $_GET['format'] ?? 'map';   // 'map' (default, keyed lookup) | 'array'

$listings_map = [];       // keyed by normalized address
$listings_coord_map = []; // keyed by rounded coord string (fallback)
$listings_arr = [];

foreach ($rows as $r) {
    // Determine effective tier (same fallback logic as dashboard)
    $tier = strtolower(trim($r['tier'] ?? ''));
    if ($tier === '') {
        $tier = !empty($r['is_paid']) ? 'concierge' : 'free';
    }

    // Tier-aware detail URL
    $detail_url = ($tier === 'concierge')
        ? '/concierge-property.php?id=' . (int)$r['id']
        : '/single-property-2.php?id=' . (int)$r['id'];

    // Build image array (only non-empty)
    $images = [];
    for ($i = 1; $i <= 6; $i++) {
        $img = trim($r['img' . $i] ?? '');
        if ($img !== '') $images[] = $img;
    }

    // Developer display name — prefer explicit developer_name on the
    // listing, fall back to company name, then the dev's full name.
    $developer_display = trim($r['developer_name'] ?? '');
    if ($developer_display === '') $developer_display = trim($r['dev_company_name'] ?? '');
    if ($developer_display === '') $developer_display = trim($r['dev_full_name'] ?? '');
    if ($developer_display === '') $developer_display = 'Wynston Developer';

    $listing = [
        'id'                => (int)$r['id'],
        'address'           => $r['address'],
        'latitude'          => isset($r['latitude']) ? (float)$r['latitude'] : null,
        'longitude'         => isset($r['longitude']) ? (float)$r['longitude'] : null,
        'neighbourhood'     => $r['neighbourhood'],
        'property_type'     => $r['property_type'],
        'price'             => $r['price'],
        'bedrooms'          => $r['bedrooms'],
        'bathrooms'         => $r['bathrooms'],
        'sqft'              => $r['sqft'],
        'description'       => $r['description'],
        'thumbnail'         => $images[0] ?? null,
        'images'            => $images,
        'developer_name'    => $developer_display,
        'developer_bio'     => $r['developer_bio'],
        'builder_logo'      => $r['builder_logo'],
        'virtual_tour_url'  => $r['virtual_tour_url'],
        'video_url'         => $r['video_url'],
        'tier'              => $tier,
        'status'            => $r['submit_status'],
        'pid'               => $r['pid'],
        'detail_url'        => $detail_url,
        'submitted_at'      => $r['created_at'],
    ];

    $norm = normalize_address($r['address'] ?? '');
    if ($norm !== '') {
        // Last-write-wins on address collision — newest id wins because
        // we ordered DESC, so the first occurrence here is newest and
        // we keep that one.
        if (!isset($listings_map[$norm])) {
            $listings_map[$norm] = $listing;
        }
    }

    // Populate coord map as fallback when address matching misses
    // (happens on corner lots where COV records the permit against
    // a different street name than the listing uses).
    $ckey = coord_key($listing['latitude'], $listing['longitude']);
    if ($ckey !== '' && !isset($listings_coord_map[$ckey])) {
        $listings_coord_map[$ckey] = $listing;
    }

    $listings_arr[] = $listing;
}

// ── Response ─────────────────────────────────────────────────
$response = ['ok' => true];

if ($format === 'array') {
    $response['listings'] = $listings_arr;
    $response['count']    = count($listings_arr);
} else {
    $response['listings']  = $listings_map;
    $response['by_coords'] = $listings_coord_map;   // fallback lookup for client
    $response['count']     = count($listings_map);
}

if ($debug) {
    $response['_meta'] = [
        'total_rows'     => count($rows),
        'unique_addresses' => count($listings_map),
        'unique_coords'    => count($listings_coord_map),
        'sample_normalized_addresses' => array_slice(array_keys($listings_map), 0, 20),
        'sample_coord_keys'           => array_slice(array_keys($listings_coord_map), 0, 20),
        'generated_at'   => date('c'),
        'schema' => [
            'multi_2025_columns' => $cols_raw,
            'neighbourhood_col_resolved_to' => $nb_expr,
        ],
    ];
}

echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);