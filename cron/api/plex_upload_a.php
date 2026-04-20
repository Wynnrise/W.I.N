<?php
// api/plex_upload_a.php — Channel A: Paragon Sold Duplex CSV
// Aggregates individual sales by neighbourhood+month, then upserts one row per group.
// This handles the UNIQUE KEY (neighbourhood_slug, data_month, csv_type) on monthly_market_stats.

session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Fatal error handler — always return JSON, never blank 500
ini_set('display_errors', 0);
register_shutdown_function(function() {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) header('Content-Type: application/json');
        echo json_encode(['success'=>false,'error'=>'Fatal: '.$e['message'].' line '.$e['line']]);
    }
});
@set_time_limit(120);

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

// ── S/A code → COV neighbourhood slug mapping ─────────────────────────────────
// AUTHORITATIVE mapping from Tam's Vancouver East / West workbook.
// 'slugs' is an array — when a code maps to multiple COV neighbourhoods,
// each sale is inserted into EVERY listed slug (option 3: duplicate).
// The $/sqft average stays accurate for each neighbourhood.
// UEL (VVWUL) is intentionally dropped — not inside City of Vancouver.
$REBGV_AREA_MAP = [
    // ── EAST SIDE ──
    'VVECH' => ['slugs'=>['killarney'],                                      'rebgv_area'=>'Champlain Heights'],
    'VVECO' => ['slugs'=>['renfrew-collingwood'],                            'rebgv_area'=>'Collingwood'],
    'VVERE' => ['slugs'=>['renfrew-collingwood'],                            'rebgv_area'=>'Renfrew VE'],
    'VVERH' => ['slugs'=>['renfrew-collingwood'],                            'rebgv_area'=>'Renfrew Heights'],
    'VVEDT' => ['slugs'=>['downtown','strathcona'],                          'rebgv_area'=>'Downtown VE'],
    'VVEFR' => ['slugs'=>['sunset','kensington-cedar-cottage'],              'rebgv_area'=>'Fraser'],
    'VVEFV' => ['slugs'=>['victoria-fraserview'],                            'rebgv_area'=>'Fraserview'],
    'VVEVI' => ['slugs'=>['victoria-fraserview'],                            'rebgv_area'=>'Victoria'],
    'VVEGW' => ['slugs'=>['grandview-woodland'],                             'rebgv_area'=>'Grandview Woodland'],
    'VVEHA' => ['slugs'=>['hastings-sunrise'],                               'rebgv_area'=>'Hastings'],
    'VVESU' => ['slugs'=>['hastings-sunrise'],                               'rebgv_area'=>'Hastings Sunrise'],
    'VVEKL' => ['slugs'=>['killarney'],                                      'rebgv_area'=>'Killarney'],
    'VVEKN' => ['slugs'=>['kensington-cedar-cottage'],                       'rebgv_area'=>'Knight'],
    'VVEMN' => ['slugs'=>['riley-park','sunset'],                            'rebgv_area'=>'Main'],
    'VVEMP' => ['slugs'=>['mount-pleasant'],                                 'rebgv_area'=>'Mount Pleasant'],
    'VVESM' => ['slugs'=>['killarney','victoria-fraserview'],                'rebgv_area'=>'South Marine'],
    'VVESV' => ['slugs'=>['sunset','victoria-fraserview'],                   'rebgv_area'=>'South Vancouver'],
    'VVEST' => ['slugs'=>['strathcona'],                                     'rebgv_area'=>'Strathcona'],

    // ── WEST SIDE ──
    'VVWAR' => ['slugs'=>['arbutus-ridge'],                                  'rebgv_area'=>'Arbutus'],
    'VVWCA' => ['slugs'=>['riley-park','south-cambie'],                      'rebgv_area'=>'Cambie'],
    'VVWSC' => ['slugs'=>['south-cambie'],                                   'rebgv_area'=>'South Cambie'],
    'VVWCB' => ['slugs'=>['downtown'],                                       'rebgv_area'=>'Coal Harbour'],
    'VVWCH' => ['slugs'=>['downtown'],                                       'rebgv_area'=>'Coal Harbour'],  // alias
    'VVWDT' => ['slugs'=>['downtown'],                                       'rebgv_area'=>'Downtown'],
    'VVWYA' => ['slugs'=>['downtown'],                                       'rebgv_area'=>'Yaletown'],
    'VVWWE' => ['slugs'=>['west-end'],                                       'rebgv_area'=>'West End'],
    'VVWDU' => ['slugs'=>['dunbar-southlands'],                              'rebgv_area'=>'Dunbar'],
    'VVWSL' => ['slugs'=>['dunbar-southlands'],                              'rebgv_area'=>'Southlands'],
    'VVWFA' => ['slugs'=>['fairview'],                                       'rebgv_area'=>'Fairview'],
    'VVWFV' => ['slugs'=>['fairview'],                                       'rebgv_area'=>'Fairview VW'],   // alias
    'VVWFC' => ['slugs'=>['fairview'],                                       'rebgv_area'=>'False Creek'],
    'VVWKE' => ['slugs'=>['kerrisdale'],                                     'rebgv_area'=>'Kerrisdale'],
    'VVWKT' => ['slugs'=>['kitsilano'],                                      'rebgv_area'=>'Kitsilano'],
    'VVWMH' => ['slugs'=>['arbutus-ridge','dunbar-southlands'],              'rebgv_area'=>'MacKenzie Heights'],
    'VVWMR' => ['slugs'=>['marpole'],                                        'rebgv_area'=>'Marpole'],
    'VVWMP' => ['slugs'=>['mount-pleasant'],                                 'rebgv_area'=>'Mount Pleasant VW'],
    'VVWOA' => ['slugs'=>['oakridge'],                                       'rebgv_area'=>'Oakridge'],
    'VVWPG' => ['slugs'=>['west-point-grey'],                                'rebgv_area'=>'Point Grey'],
    'VVWQU' => ['slugs'=>['arbutus-ridge','shaughnessy'],                    'rebgv_area'=>'Quilchena'],
    'VVWSW' => ['slugs'=>['marpole','kerrisdale'],                           'rebgv_area'=>'S.W. Marine'],
    'VVWSH' => ['slugs'=>['shaughnessy'],                                    'rebgv_area'=>'Shaughnessy'],
    'VVWSG' => ['slugs'=>['fairview','shaughnessy'],                         'rebgv_area'=>'South Granville'],
    // VVWUL (University / UEL) — DROPPED — not inside City of Vancouver
];

// ── Helpers ───────────────────────────────────────────────────────────────────
function clean_price(string $s): ?int {
    $s = preg_replace('/[^0-9.]/', '', $s);
    return $s !== '' ? (int)round((float)$s) : null;
}
function clean_float(string $s): ?float {
    $s = preg_replace('/[^0-9.]/', '', $s);
    return $s !== '' ? (float)$s : null;
}
function _find_col(array $headers, array $names): ?int {
    foreach ($headers as $i => $h) {
        if (in_array(strtolower(trim($h)), $names)) return $i;
    }
    return null;
}

// ── Validate request ──────────────────────────────────────────────────────────
if (!isset($_FILES['rebgv_csv']) || $_FILES['rebgv_csv']['error'] !== UPLOAD_ERR_OK) {
    $err = isset($_FILES['rebgv_csv']) ? $_FILES['rebgv_csv']['error'] : 'missing';
    echo json_encode(['success'=>false,'error'=>'No CSV uploaded. PHP error code: '.$err]); exit;
}
$data_month = trim($_POST['data_month'] ?? '');
if (!preg_match('/^\d{4}-\d{2}$/', $data_month)) {
    echo json_encode(['success'=>false,'error'=>'Invalid data_month. Expected YYYY-MM, got: '.$data_month]); exit;
}
$data_month_dt = $data_month . '-01';

// csv_type: 'duplex' (used in pro forma) or 'detached' (reference only, never in calculations)
$csv_type = in_array($_POST['csv_type'] ?? '', ['duplex','detached']) ? $_POST['csv_type'] : 'duplex';

// ── Open CSV ──────────────────────────────────────────────────────────────────
$tmp = $_FILES['rebgv_csv']['tmp_name'];
$handle = fopen($tmp, 'r');
if (!$handle) {
    echo json_encode(['success'=>false,'error'=>'Could not open uploaded file.']); exit;
}

// Read headers
$raw_headers = fgetcsv($handle);
if (!$raw_headers) {
    echo json_encode(['success'=>false,'error'=>'CSV appears empty — no header row found.']); exit;
}
$raw_headers[0] = ltrim($raw_headers[0], "\xEF\xBB\xBF"); // strip UTF-8 BOM
$col = array_map('strtolower', array_map('trim', $raw_headers));

$col_map = [
    'status'        => _find_col($col, ['status']),
    'address'       => _find_col($col, ['address']),
    'area'          => _find_col($col, ['s/a','sub area','subarea','sub-area','area','area code']),
    'yr_blt'        => _find_col($col, ['yr blt','year built','yrbuilt','yr_blt']),
    'sold_price'    => _find_col($col, ['sold price','saleprice','sale price','soldprice']),
    'tot_fl_area'   => _find_col($col, ['totflarea','tot fl area','total floor area','sqft','tot_fl_area']),
    'price_per_sqft'=> _find_col($col, ['sold price per sqft','price per sqft','price/sqft','pricepersqft']),
    'sold_date'     => _find_col($col, ['sold date','solddate','sale date']),
    'dom'           => _find_col($col, ['dom','days on market','days on mkt']),
];
$dom_col_found       = ($col_map['dom'] !== null);
$dom_rows_with_value = 0;

// ── Pass 1: read all rows, accumulate by neighbourhood + sold month ───────────
$sales   = [];
$skipped = 0;
$errors  = [];
$rows_out= [];
$row_num = 1;

while (($row = fgetcsv($handle)) !== false) {
    $row_num++;
    if (count($row) < 2) { $skipped++; continue; }

    // Status: P or F only
    $status_val = $col_map['status'] !== null ? strtoupper(trim($row[$col_map['status']] ?? '')) : '';
    if ($status_val !== '' && !in_array($status_val, ['P','F'])) { $skipped++; continue; }

    // Yr Blt filter
    $yr_blt_raw = $col_map['yr_blt'] !== null ? trim($row[$col_map['yr_blt']] ?? '') : '';
    $yr_blt     = $yr_blt_raw !== '' ? (int)$yr_blt_raw : 0;
    if ($yr_blt > 0 && $yr_blt < 2020) { $skipped++; continue; }

    // Address
    $address_raw = $col_map['address'] !== null ? trim($row[$col_map['address']] ?? '') : '';
    if (empty($address_raw)) { $errors[] = "Row {$row_num}: no address"; $skipped++; continue; }

    // S/A code → neighbourhood(s)
    $area_raw    = $col_map['area'] !== null ? strtoupper(trim($row[$col_map['area']] ?? '')) : '';
    $area_match  = $REBGV_AREA_MAP[$area_raw] ?? null;
    $slug_list   = $area_match['slugs']      ?? null;
    $rebgv_area  = $area_match['rebgv_area'] ?? null;
    if (!$slug_list || !is_array($slug_list) || empty($slug_list)) {
        $errors[] = "Row {$row_num}: unknown or dropped S/A '{$area_raw}' — {$address_raw}";
        $skipped++; continue;
    }

    // Prices
    $sold_price     = $col_map['sold_price']     !== null ? clean_price($row[$col_map['sold_price']] ?? '')     : null;
    $sqft_val       = $col_map['tot_fl_area']    !== null ? clean_price($row[$col_map['tot_fl_area']] ?? '')    : null;
    $price_per_sqft = $col_map['price_per_sqft'] !== null ? clean_float($row[$col_map['price_per_sqft']] ?? '') : null;
    if (!$price_per_sqft && $sold_price && $sqft_val && $sqft_val > 0) {
        $price_per_sqft = round($sold_price / $sqft_val, 2);
    }
    if (!$sold_price && !$price_per_sqft) { $skipped++; continue; }
    if ($price_per_sqft && ($price_per_sqft < 100 || $price_per_sqft > 15000)) { $skipped++; continue; }

    // DOM (optional)
    $dom_val = null;
    if ($col_map['dom'] !== null) {
        $d_raw = trim($row[$col_map['dom']] ?? '');
        if ($d_raw !== '') {
            $d_int = (int)preg_replace('/[^0-9]/', '', $d_raw);
            if ($d_int >= 1 && $d_int <= 999) { $dom_val = $d_int; $dom_rows_with_value++; }
        }
    }

    // Sold date → which month does this sale belong to
    $sold_date_raw = $col_map['sold_date'] !== null ? trim($row[$col_map['sold_date']] ?? '') : '';
    $row_month = $data_month_dt;
    if ($sold_date_raw) {
        $ts = strtotime($sold_date_raw);
        if ($ts) $row_month = date('Y-m-01', $ts);
    }

    // ── Duplicate across all mapped COV neighbourhoods ───────────────────────
    foreach ($slug_list as $nb_slug) {
        $key = $nb_slug . '|' . $row_month;
        if (!isset($sales[$key])) {
            $sales[$key] = ['slug'=>$nb_slug, 'rebgv'=>$rebgv_area, 'month'=>$row_month, 'psf'=>[], 'dom'=>[], 'count'=>0];
        }
        if ($price_per_sqft) $sales[$key]['psf'][] = (float)$price_per_sqft;
        if ($dom_val !== null) $sales[$key]['dom'][] = $dom_val;
        $sales[$key]['count']++;
    }

    $rows_out[] = [
        'address'       => $address_raw,
        'nb_slug'       => implode(' + ', $slug_list),
        'rebgv_area'    => $rebgv_area,
        'yr_blt'        => $yr_blt,
        'sold_price'    => $sold_price,
        'price_per_sqft'=> $price_per_sqft,
    ];
}
fclose($handle);

// ── Pass 2: upsert one averaged row per neighbourhood + month ─────────────────
$inserted = 0;
$pdo->beginTransaction();
try {
    $upsert = $pdo->prepare("
        INSERT INTO monthly_market_stats
            (neighbourhood_slug, rebgv_area, data_month, csv_type,
             price_per_sqft, avg_sold_psf_duplex,
             sales_count, sales_count_duplex,
             days_on_market_duplex,
             is_active, source, created_at)
        VALUES
            (:slug, :rebgv, :month, :csv_type,
             :psf, :psf,
             :cnt, :cnt,
             :dom,
             1, 'rebgv_csv', NOW())
        ON DUPLICATE KEY UPDATE
            price_per_sqft        = VALUES(price_per_sqft),
            avg_sold_psf_duplex   = VALUES(avg_sold_psf_duplex),
            sales_count           = VALUES(sales_count),
            sales_count_duplex    = VALUES(sales_count_duplex),
            days_on_market_duplex = VALUES(days_on_market_duplex),
            rebgv_area            = VALUES(rebgv_area),
            is_active             = 1,
            source                = 'rebgv_csv',
            created_at            = NOW()
    ");

    foreach ($sales as $data) {
        if (empty($data['psf'])) continue;
        $avg_psf = round(array_sum($data['psf']) / count($data['psf']), 2);
        $avg_dom = !empty($data['dom']) ? (int)round(array_sum($data['dom']) / count($data['dom'])) : null;
        $upsert->execute([
            ':slug'     => $data['slug'],
            ':rebgv'    => $data['rebgv'],
            ':month'    => $data['month'],
            ':csv_type' => $csv_type,
            ':psf'      => $avg_psf,
            ':cnt'      => $data['count'],
            ':dom'      => $avg_dom,
        ]);
        $inserted++;
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'error'=>'DB error: '.$e->getMessage()]); exit;
}

echo json_encode([
    'success'       => true,
    'message'       => "Saved {$inserted} neighbourhood/month averages from " . count($rows_out) . " sale rows.",
    'csv_type'      => $csv_type,
    'inserted'      => $inserted,
    'skipped'       => $skipped,
    'dom_col_found' => $dom_col_found,
    'dom_populated' => $dom_rows_with_value . ' of ' . count($rows_out) . ' rows had valid DOM values',
    'errors'        => array_slice($errors, 0, 20),
    'rows'          => array_slice($rows_out, 0, 50),
]);