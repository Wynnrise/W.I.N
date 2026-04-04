<?php
// ============================================================
// api/plex_upload_a.php  —  Channel A: REBGV Sold Data CSV
// Session 03 — Admin Upload Channels
//
// Accepts: POST multipart/form-data with 'rebgv_csv' file
//          + 'data_month' (YYYY-MM) + 'csv_type' (duplex|detached)
//
// Flow:
//   1. Parse CSV → filter Yr Blt >= 2024
//   2. Geocode each address via Mapbox Geocoding API
//   3. Map REBGV area code → neighbourhood_slug
//   4. INSERT into monthly_market_stats
//   5. Versioning: set previous rows for same nb+month to is_active=0
//
// Returns: JSON {success, inserted, skipped, errors[], rows[]}
// ============================================================

session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// ── DB ────────────────────────────────────────────────────────────────────────
$host = 'localhost';
$db   = 'u990588858_Property';
$user = 'u990588858_Multiplex';
$pass = 'Concac1979$';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'error'=>'DB: '.$e->getMessage()]); exit;
}

// ── Mapbox Geocoding token ────────────────────────────────────────────────────
define('MAPBOX_TOKEN', 'pk.eyJ1IjoiaGVucmluZ3V5ZW4iLCJhIjoiY21uYjg3dTNnMHFkZjJwcHR0bjkwb29ueCJ9.De7GXPlYRlzTJOr9jd5BJg');

// ── REBGV area code → COV neighbourhood_slug + REBGV sub-area name ───────────
// COV combines what REBGV splits. Both are stored:
//   neighbourhood_slug → COV slug (joins to plex_properties, neighbourhoods)
//   rebgv_area         → REBGV sub-area (used for precise comp matching)
// feasibility.php queries rebgv_area first (Tier 1), falls back to slug (Tier 3)
$REBGV_AREA_MAP = [
    // ── Renfrew-Collingwood (COV) = 3 REBGV sub-areas ────────────────────────
    'VVECO' => ['slug'=>'renfrew-collingwood', 'rebgv_area'=>'Collingwood VE'],
    'VVERE' => ['slug'=>'renfrew-collingwood', 'rebgv_area'=>'Renfrew VE'],
    'VVEGW' => ['slug'=>'renfrew-collingwood', 'rebgv_area'=>'Renfrew Heights'],
    'VVERN' => ['slug'=>'renfrew-collingwood', 'rebgv_area'=>'Renfrew East'],

    // ── Downtown ──────────────────────────────────────────────────────────────
    'VVWDT' => ['slug'=>'downtown',            'rebgv_area'=>'Downtown VW'],
    'VVWYA' => ['slug'=>'downtown',            'rebgv_area'=>'Yaletown'],

    // ── Victoria-Fraserview ───────────────────────────────────────────────────
    'VVESV' => ['slug'=>'victoria-fraserview', 'rebgv_area'=>'South Vancouver'],

    // ── Killarney ─────────────────────────────────────────────────────────────
    'VVEKI' => ['slug'=>'killarney',           'rebgv_area'=>'Killarney VE'],

    // ── Kitsilano ─────────────────────────────────────────────────────────────
    'VVWKT' => ['slug'=>'kitsilano',           'rebgv_area'=>'Kitsilano'],

    // ── Mount Pleasant (COV) = 2 REBGV sub-areas ─────────────────────────────
    'VVMTP' => ['slug'=>'mount-pleasant',      'rebgv_area'=>'Mount Pleasant VE'],
    'VVWMP' => ['slug'=>'mount-pleasant',      'rebgv_area'=>'Mount Pleasant VW'],

    // ── West End ──────────────────────────────────────────────────────────────
    'VVWDU' => ['slug'=>'west-end',            'rebgv_area'=>'West End VW'],

    // ── West Point Grey ───────────────────────────────────────────────────────
    'VVWPG' => ['slug'=>'west-point-grey',     'rebgv_area'=>'West Point Grey'],

    // ── Additional Vancouver neighbourhoods ───────────────────────────────────
    'VVEHA' => ['slug'=>'hastings-sunrise',         'rebgv_area'=>'Hastings Sunrise'],
    'VVEKS' => ['slug'=>'kensington-cedar-cottage', 'rebgv_area'=>'Kensington Cedar Cottage'],
    'VVEFR' => ['slug'=>'fraser-ve',                'rebgv_area'=>'Fraser VE'],
    'VVESM' => ['slug'=>'south-marine',             'rebgv_area'=>'South Marine'],
    'VVEKN' => ['slug'=>'knight',                   'rebgv_area'=>'Knight'],
    'VVEMA' => ['slug'=>'main',                     'rebgv_area'=>'Main'],
    'VVEGV' => ['slug'=>'grandview-woodland',       'rebgv_area'=>'Grandview Woodland'],
    'VVWFV' => ['slug'=>'fairview-vw',              'rebgv_area'=>'Fairview VW'],
    'VVWKR' => ['slug'=>'kerrisdale',               'rebgv_area'=>'Kerrisdale'],
    'VVWMR' => ['slug'=>'marpole',                  'rebgv_area'=>'Marpole'],
    'VVWOK' => ['slug'=>'oakridge',                 'rebgv_area'=>'Oakridge'],
    'VVWSC' => ['slug'=>'south-cambie',             'rebgv_area'=>'South Cambie'],
    'VVWSH' => ['slug'=>'shaughnessy',              'rebgv_area'=>'Shaughnessy'],
    'VVWRP' => ['slug'=>'riley-park',               'rebgv_area'=>'Riley Park'],
];

// ── Helpers ───────────────────────────────────────────────────────────────────
function geocode_address(string $address): ?array {
    $query = urlencode($address . ', Vancouver, BC, Canada');
    $url   = 'https://api.mapbox.com/geocoding/v5/mapbox.places/'
           . $query . '.json?'
           . http_build_query([
                 'access_token' => MAPBOX_TOKEN,
                 'country'      => 'CA',
                 'proximity'    => '-123.1207,49.2827', // Vancouver centre
                 'types'        => 'address',
                 'limit'        => 1,
             ]);
    $ctx  = stream_context_create(['http' => ['timeout' => 5]]);
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) return null;
    $data = json_decode($json, true);
    if (empty($data['features'])) return null;
    $coords = $data['features'][0]['geometry']['coordinates'];
    return ['lat' => (float)$coords[1], 'lng' => (float)$coords[0]];
}

// ── Validate input ────────────────────────────────────────────────────────────
if (!isset($_FILES['rebgv_csv']) || $_FILES['rebgv_csv']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success'=>false,'error'=>'No CSV file uploaded or upload error.']); exit;
}
$data_month = trim($_POST['data_month'] ?? '');
if (!preg_match('/^\d{4}-\d{2}$/', $data_month)) {
    echo json_encode(['success'=>false,'error'=>'data_month must be YYYY-MM format.']); exit;
}
$csv_type = in_array($_POST['csv_type'] ?? '', ['duplex','detached']) ? $_POST['csv_type'] : 'duplex';
$data_month_dt = $data_month . '-01'; // store as first-of-month DATE

// ── Parse CSV ─────────────────────────────────────────────────────────────────
$f = $_FILES['rebgv_csv']['tmp_name'];
if (strtolower(pathinfo($_FILES['rebgv_csv']['name'], PATHINFO_EXTENSION)) !== 'csv') {
    echo json_encode(['success'=>false,'error'=>'File must be a .csv']); exit;
}

$handle = fopen($f, 'r');
$raw_header = fgetcsv($handle);
if (!$raw_header) {
    echo json_encode(['success'=>false,'error'=>'CSV has no header row.']); exit;
}
$header = array_map(fn($h) => strtolower(trim($h)), $raw_header);
$col    = array_flip($header);

// Required columns — flex matching to handle REBGV export variations
$col_map = [
    'address'      => _find_col($col, ['address','street address','addr']),
    'area'         => _find_col($col, ['area','area code','rebgv area','mls area']),
    'yr_blt'       => _find_col($col, ['yr blt','year built','yrbuilt','yr_blt']),
    'sold_price'   => _find_col($col, ['sold price','saleprice','sale price','soldprice','price']),
    'tot_fl_area'  => _find_col($col, ['totflarea','tot fl area','total floor area','sqft','tot_fl_area','floorarea']),
    'price_per_sqft'=> _find_col($col, ['price per sqft','price/sqft','pricepersqft','$/sqft']),
    'tot_br'       => _find_col($col, ['tot br','total bedrooms','bedrooms','bdrms','tot_br']),
    'status'       => _find_col($col, ['status']),
    'dom'          => _find_col($col, ['dom','days on market']),
];

function _find_col(array $col, array $candidates): ?int {
    foreach ($candidates as $c) {
        if (isset($col[$c])) return $col[$c];
    }
    return null;
}

$inserted  = 0;
$skipped   = 0;
$errors    = [];
$rows_out  = []; // for preview response

$pdo->beginTransaction();

try {
    // Prepare insert statement
    $ins = $pdo->prepare("
        INSERT INTO monthly_market_stats
            (neighbourhood_slug, rebgv_area, data_month, csv_type,
             address, lat, lng,
             sold_price, sqft, price_per_sqft, bedrooms, yr_blt,
             days_on_market, status, is_active, source, created_at)
        VALUES
            (:nb_slug, :rebgv_area, :data_month, :csv_type,
             :address, :lat, :lng,
             :sold_price, :sqft, :price_per_sqft, :bedrooms, :yr_blt,
             :dom, :status, 1, 'rebgv_csv', NOW())
    ");

    $row_num = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $row_num++;
        if (count($row) < 2) { $skipped++; continue; }

        // ── Yr Blt filter: new builds only ───────────────────────────────────
        $yr_blt_raw = $col_map['yr_blt'] !== null ? trim($row[$col_map['yr_blt']] ?? '') : '';
        $yr_blt     = $yr_blt_raw !== '' ? (int)$yr_blt_raw : 0;
        if ($yr_blt > 0 && $yr_blt < 2024) {
            $skipped++;
            continue;
        }

        // ── Address ───────────────────────────────────────────────────────────
        $address_raw = $col_map['address'] !== null ? trim($row[$col_map['address']] ?? '') : '';
        if (empty($address_raw)) { $errors[] = "Row {$row_num}: no address"; $skipped++; continue; }

        // ── Area code → neighbourhood slug + REBGV sub-area ──────────────────
        $area_raw   = $col_map['area'] !== null ? strtoupper(trim($row[$col_map['area']] ?? '')) : '';
        $area_match = $REBGV_AREA_MAP[$area_raw] ?? null;
        $nb_slug    = $area_match['slug']      ?? null;
        $rebgv_area = $area_match['rebgv_area']?? null;
        if (!$nb_slug) {
            // Try text fallback (e.g. CSV has "Collingwood VE" instead of code)
            $fallback   = _area_text_fallback($area_raw);
            $nb_slug    = $fallback['slug']       ?? null;
            $rebgv_area = $fallback['rebgv_area'] ?? null;
        }
        if (!$nb_slug) {
            $errors[] = "Row {$row_num}: unknown area code '{$area_raw}' — address: {$address_raw}";
            $skipped++;
            continue;
        }

        // ── Prices ────────────────────────────────────────────────────────────
        $sold_price    = $col_map['sold_price']    !== null ? clean_price($row[$col_map['sold_price']] ?? '')    : null;
        $sqft          = $col_map['tot_fl_area']   !== null ? clean_price($row[$col_map['tot_fl_area']] ?? '')   : null;
        $price_per_sqft= $col_map['price_per_sqft']!== null ? clean_float($row[$col_map['price_per_sqft']] ?? ''): null;

        // Calculate PSF if not in CSV but we have price + sqft
        if (!$price_per_sqft && $sold_price && $sqft && $sqft > 0) {
            $price_per_sqft = round($sold_price / $sqft, 2);
        }

        $bedrooms = $col_map['tot_br']  !== null ? clean_price($row[$col_map['tot_br']] ?? '')  : null;
        $dom      = $col_map['dom']     !== null ? clean_price($row[$col_map['dom']] ?? '')      : null;
        $status   = $col_map['status']  !== null ? trim($row[$col_map['status']] ?? '')          : null;

        // ── Skip rows with no price data ──────────────────────────────────────
        if (!$sold_price && !$price_per_sqft) {
            $skipped++; continue;
        }

        // ── Geocode ───────────────────────────────────────────────────────────
        $coords = geocode_address($address_raw);
        $lat = $coords['lat'] ?? null;
        $lng = $coords['lng'] ?? null;
        if (!$lat) {
            $errors[] = "Row {$row_num}: geocode failed for '{$address_raw}'";
        }

        // ── Insert ────────────────────────────────────────────────────────────
        $ins->execute([
            ':nb_slug'       => $nb_slug,
            ':rebgv_area'    => $rebgv_area,
            ':data_month'    => $data_month_dt,
            ':csv_type'      => $csv_type,
            ':address'       => $address_raw,
            ':lat'           => $lat,
            ':lng'           => $lng,
            ':sold_price'    => $sold_price,
            ':sqft'          => $sqft,
            ':price_per_sqft'=> $price_per_sqft,
            ':bedrooms'      => $bedrooms,
            ':yr_blt'        => $yr_blt ?: null,
            ':dom'           => $dom,
            ':status'        => $status,
        ]);
        $inserted++;

        $rows_out[] = [
            'address'       => $address_raw,
            'nb_slug'       => $nb_slug,
            'yr_blt'        => $yr_blt,
            'sold_price'    => $sold_price,
            'price_per_sqft'=> $price_per_sqft,
            'geocoded'      => (bool)$lat,
        ];
    }
    fclose($handle);

    // ── Versioning: deactivate previous uploads for same month + type ─────────
    if ($inserted > 0) {
        // Mark ALL rows for this month/type as inactive first
        $pdo->prepare("
            UPDATE monthly_market_stats
            SET is_active = 0
            WHERE data_month = :month
              AND csv_type   = :csv_type
        ")->execute([':month'=>$data_month_dt, ':csv_type'=>$csv_type]);

        // Then re-activate only the rows inserted in this upload (today)
        $pdo->prepare("
            UPDATE monthly_market_stats
            SET is_active = 1
            WHERE data_month       = :month
              AND csv_type         = :csv_type
              AND DATE(created_at) = CURDATE()
        ")->execute([':month'=>$data_month_dt, ':csv_type'=>$csv_type]);
    }

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'error'=>'DB error: '.$e->getMessage()]); exit;
}

echo json_encode([
    'success'  => true,
    'inserted' => $inserted,
    'skipped'  => $skipped,
    'errors'   => $errors,
    'month'    => $data_month,
    'csv_type' => $csv_type,
    'rows'     => array_slice($rows_out, 0, 50), // preview first 50 rows
]);

// ── Area text fallback (when CSV has text names instead of codes) ─────────────
// Returns same structure as $REBGV_AREA_MAP: ['slug'=>..., 'rebgv_area'=>...]
function _area_text_fallback(string $area): ?array {
    $lc = strtolower($area);
    $map = [
        // Renfrew-Collingwood sub-areas
        'collingwood'         => ['slug'=>'renfrew-collingwood', 'rebgv_area'=>'Collingwood VE'],
        'collingwood ve'      => ['slug'=>'renfrew-collingwood', 'rebgv_area'=>'Collingwood VE'],
        'renfrew ve'          => ['slug'=>'renfrew-collingwood', 'rebgv_area'=>'Renfrew VE'],
        'renfrew'             => ['slug'=>'renfrew-collingwood', 'rebgv_area'=>'Renfrew VE'],
        'renfrew heights'     => ['slug'=>'renfrew-collingwood', 'rebgv_area'=>'Renfrew Heights'],
        'renfrew east'        => ['slug'=>'renfrew-collingwood', 'rebgv_area'=>'Renfrew East'],
        // Downtown
        'downtown'            => ['slug'=>'downtown',            'rebgv_area'=>'Downtown VW'],
        'downtown vw'         => ['slug'=>'downtown',            'rebgv_area'=>'Downtown VW'],
        'yaletown'            => ['slug'=>'downtown',            'rebgv_area'=>'Yaletown'],
        // Victoria-Fraserview
        'victoria'            => ['slug'=>'victoria-fraserview', 'rebgv_area'=>'South Vancouver'],
        'victoria ve'         => ['slug'=>'victoria-fraserview', 'rebgv_area'=>'South Vancouver'],
        'fraserview'          => ['slug'=>'victoria-fraserview', 'rebgv_area'=>'South Vancouver'],
        'south vancouver'     => ['slug'=>'victoria-fraserview', 'rebgv_area'=>'South Vancouver'],
        // Killarney
        'killarney'           => ['slug'=>'killarney',           'rebgv_area'=>'Killarney VE'],
        'killarney ve'        => ['slug'=>'killarney',           'rebgv_area'=>'Killarney VE'],
        // Kitsilano
        'kitsilano'           => ['slug'=>'kitsilano',           'rebgv_area'=>'Kitsilano'],
        // Mount Pleasant
        'mount pleasant'      => ['slug'=>'mount-pleasant',      'rebgv_area'=>'Mount Pleasant VE'],
        'mount pleasant ve'   => ['slug'=>'mount-pleasant',      'rebgv_area'=>'Mount Pleasant VE'],
        'mount pleasant vw'   => ['slug'=>'mount-pleasant',      'rebgv_area'=>'Mount Pleasant VW'],
        // West End
        'west end'            => ['slug'=>'west-end',            'rebgv_area'=>'West End VW'],
        'west end vw'         => ['slug'=>'west-end',            'rebgv_area'=>'West End VW'],
        // West Point Grey
        'west point grey'     => ['slug'=>'west-point-grey',     'rebgv_area'=>'West Point Grey'],
        'point grey'          => ['slug'=>'west-point-grey',     'rebgv_area'=>'West Point Grey'],
        // Additional
        'hastings'            => ['slug'=>'hastings-sunrise',         'rebgv_area'=>'Hastings Sunrise'],
        'hastings sunrise'    => ['slug'=>'hastings-sunrise',         'rebgv_area'=>'Hastings Sunrise'],
        'kensington'          => ['slug'=>'kensington-cedar-cottage', 'rebgv_area'=>'Kensington Cedar Cottage'],
        'cedar cottage'       => ['slug'=>'kensington-cedar-cottage', 'rebgv_area'=>'Kensington Cedar Cottage'],
        'grandview'           => ['slug'=>'grandview-woodland',       'rebgv_area'=>'Grandview Woodland'],
        'grandview woodland'  => ['slug'=>'grandview-woodland',       'rebgv_area'=>'Grandview Woodland'],
        'fraser ve'           => ['slug'=>'fraser-ve',                'rebgv_area'=>'Fraser VE'],
        'fraser'              => ['slug'=>'fraser-ve',                'rebgv_area'=>'Fraser VE'],
        'south marine'        => ['slug'=>'south-marine',             'rebgv_area'=>'South Marine'],
        'marpole'             => ['slug'=>'marpole',                  'rebgv_area'=>'Marpole'],
        'oakridge'            => ['slug'=>'oakridge',                 'rebgv_area'=>'Oakridge'],
        'fairview'            => ['slug'=>'fairview-vw',              'rebgv_area'=>'Fairview VW'],
        'fairview vw'         => ['slug'=>'fairview-vw',              'rebgv_area'=>'Fairview VW'],
        'main'                => ['slug'=>'main',                     'rebgv_area'=>'Main'],
        'riley park'          => ['slug'=>'riley-park',               'rebgv_area'=>'Riley Park'],
        'south cambie'        => ['slug'=>'south-cambie',             'rebgv_area'=>'South Cambie'],
        'shaughnessy'         => ['slug'=>'shaughnessy',              'rebgv_area'=>'Shaughnessy'],
        'kerrisdale'          => ['slug'=>'kerrisdale',               'rebgv_area'=>'Kerrisdale'],
        'dunbar'              => ['slug'=>'dunbar',                   'rebgv_area'=>'Dunbar Southlands'],
        'dunbar southlands'   => ['slug'=>'dunbar',                   'rebgv_area'=>'Dunbar Southlands'],
        'champlain heights'   => ['slug'=>'champlain-heights',        'rebgv_area'=>'Champlain Heights'],
        'knight'              => ['slug'=>'knight',                   'rebgv_area'=>'Knight'],
        'strathcona'          => ['slug'=>'strathcona',               'rebgv_area'=>'Strathcona'],
    ];
    return $map[$lc] ?? null;
}
