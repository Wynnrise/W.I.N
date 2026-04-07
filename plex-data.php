<?php
// ============================================================
// plex-data.php  —  Wynston W.I.N Market Data Admin
// Session 09 — Consolidated: Channels A+HPI, B, D, Outlook,
//              Build Costs, Population & Census, History
// ============================================================

session_start();
define('ADMIN_PASSWORD', 'Concac1979$');

if (isset($_POST['admin_login'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = 'Incorrect password.';
    }
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: plex-data.php'); exit; }

if (empty($_SESSION['admin_logged_in'])) { ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>W.I.N — Wynston Intelligent Navigator</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#002446;display:flex;align-items:center;justify-content:center;min-height:100vh;}
.lb{background:#fff;border-radius:12px;padding:40px;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.4);}
.lb h2{color:#002446;font-weight:800;}.lb p{color:#888;font-size:13px;margin-bottom:24px;}
.ba{background:#002446;color:#fff;border:none;width:100%;padding:12px;border-radius:8px;font-weight:700;}</style>
</head><body><div class="lb"><h2>W.I.N</h2><p>Wynston Intelligent Navigator — Admin</p>
<?php if (!empty($login_error)): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($login_error) ?></div><?php endif; ?>
<form method="POST"><input type="password" name="password" class="form-control mb-3" placeholder="Admin password" required autofocus>
<button type="submit" name="admin_login" class="ba">Login</button></form></div></body></html>
<?php exit; }

// ── DB ────────────────────────────────────────────────────────────────────────
$host = 'localhost'; $db = 'u990588858_Property';
$user = 'u990588858_Multiplex'; $pass = 'Concac1979$';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die('DB error: ' . $e->getMessage()); }

// ── Load shared data ──────────────────────────────────────────────────────────
$neighbourhoods = $pdo->query("SELECT id, slug, name FROM neighbourhoods WHERE is_active=1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// ── COV official 22 neighbourhoods (plex map + coming soon listings) ──────────
// These slugs must match exactly what's in the neighbourhoods table
// REBGV sub-areas are kept separately for active listings + HPI data
$cov_slugs = [
    'arbutus-ridge','downtown','dunbar-southlands','fairview',
    'grandview-woodland','hastings-sunrise','kensington-cedar-cottage',
    'kerrisdale','killarney','kitsilano','knight','marpole',
    'mount-pleasant','oakridge','renfrew-collingwood','riley-park',
    'shaughnessy','south-cambie','strathcona','sunset',
    'victoria-fraserview','west-end','west-point-grey',
];

// ── REBGV sub-area → COV neighbourhood slug conversion ───────────────────────
// Used when saving market data: REBGV name/slug → COV slug for plex map joins
// Multiple REBGV areas can map to one COV neighbourhood (weighted avg by volume)
$rebgv_to_cov = [
    // Renfrew-Collingwood (4 REBGV sub-areas → 1 COV)
    'collingwood-ve'      => 'renfrew-collingwood',
    'renfrew-ve'          => 'renfrew-collingwood',
    'renfrew-heights'     => 'renfrew-collingwood',
    'south-marine'        => 'renfrew-collingwood',
    // Mount Pleasant (2 REBGV → 1 COV)
    'mount-pleasant-ve'   => 'mount-pleasant',
    'mount-pleasant-vw'   => 'mount-pleasant',
    // Downtown (multiple REBGV → 1 COV)
    'downtown-vw'         => 'downtown',
    'coal-harbour'        => 'downtown',
    'yaletown'            => 'downtown',
    'false-creek'         => 'downtown',
    // Hastings-Sunrise (2 REBGV → 1 COV)
    'hastings'            => 'hastings-sunrise',
    'hastings-sunrise'    => 'hastings-sunrise',
    // Victoria-Fraserview (2 REBGV → 1 COV)
    'fraserview-ve'       => 'victoria-fraserview',
    'south-vancouver'     => 'victoria-fraserview',
    'victoria'            => 'victoria-fraserview',
    // Kensington-Cedar-Cottage (REBGV Fraser VE → COV)
    'fraser-ve'           => 'kensington-cedar-cottage',
    'main'                => 'kensington-cedar-cottage',
    // Dunbar-Southlands
    'dunbar'              => 'dunbar-southlands',
    'southlands'          => 'dunbar-southlands',
    'mackenzie-heights'   => 'dunbar-southlands',
    // Fairview
    'fairview-vw'         => 'fairview',
    // Strathcona
    'strathcona'          => 'strathcona',
    // Sunset (REBGV calls this parts of Kensington + South Vancouver)
    'champlain-heights'   => 'sunset',
    // Direct 1-to-1 mappings (REBGV slug = COV slug)
    'grandview-woodland'  => 'grandview-woodland',
    'kerrisdale'          => 'kerrisdale',
    'killarney'           => 'killarney',
    'kitsilano'           => 'kitsilano',
    'knight'              => 'knight',
    'marpole'             => 'marpole',
    'oakridge'            => 'oakridge',
    'riley-park'          => 'riley-park',
    'shaughnessy'         => 'shaughnessy',
    'south-cambie'        => 'south-cambie',
    'west-end'            => 'west-end',
    'point-grey'          => 'west-point-grey',
    'university'          => 'west-point-grey',
    'sw-marine'           => 'marpole',
    'quilchena'           => 'shaughnessy',
    'south-granville'     => 'shaughnessy',
    'arbutus'             => 'arbutus-ridge',
];

// Filter neighbourhoods table to COV-only rows for plex map dropdowns
$cov_neighbourhoods = array_values(array_filter(
    $neighbourhoods,
    fn($nb) => in_array($nb['slug'], $cov_slugs)
));

$recent_uploads = $pdo->query("
    SELECT neighbourhood_slug, data_month, csv_type,
           COUNT(*) as row_count, MAX(created_at) as uploaded_at
    FROM monthly_market_stats WHERE is_active=1
    GROUP BY neighbourhood_slug, data_month, csv_type
    ORDER BY uploaded_at DESC LIMIT 60
")->fetchAll(PDO::FETCH_ASSOC);

$cmhc_rows = $pdo->query("SELECT * FROM cmhc_benchmarks ORDER BY year DESC, neighbourhood_slug ASC LIMIT 60")->fetchAll(PDO::FETCH_ASSOC);

$lq = $pdo->query("SELECT quarter FROM wynston_outlook_inputs WHERE is_active=1 ORDER BY created_at DESC LIMIT 1")->fetchColumn();
$outlook_inputs = [];
if ($lq) {
    $oi = $pdo->prepare("SELECT * FROM wynston_outlook_inputs WHERE quarter=? AND is_active=1 ORDER BY source_name");
    $oi->execute([$lq]); $outlook_inputs = $oi->fetchAll(PDO::FETCH_ASSOC);
}
$outlook_results = $pdo->query("SELECT * FROM wynston_outlook WHERE is_active=1 ORDER BY weighted_outlook DESC")->fetchAll(PDO::FETCH_ASSOC);
$cost_rows = $pdo->query("SELECT * FROM construction_costs ORDER BY neighbourhood_slug")->fetchAll(PDO::FETCH_ASSOC);

// Population data
$pop_rows = $pdo->query("
    SELECT * FROM neighbourhood_population
    ORDER BY neighbourhood_slug, census_year
")->fetchAll(PDO::FETCH_ASSOC);

// Group pop_rows by slug for display
$pop_by_slug = [];
foreach ($pop_rows as $pr) {
    $pop_by_slug[$pr['neighbourhood_slug']][$pr['census_year']] = $pr;
}

// HPI history last 3 months summary
$hpi_recent = $pdo->query("
    SELECT n.name, h.month_year, h.price_duplex, h.dom_duplex,
           h.hpi_change_yoy, h.sales_duplex
    FROM neighbourhood_hpi_history h
    JOIN neighbourhoods n ON n.id = h.neighbourhood_id
    ORDER BY h.month_year DESC, n.name ASC
    LIMIT 66
")->fetchAll(PDO::FETCH_ASSOC);

$active_tab = $_GET['tab'] ?? 'market-prices';

// ── CSV Template download (HPI bulk) ─────────────────────────────────────────
// Keep identical format to old admin.php template so existing files still work
$_rebgv_area_rows = [
    'vancouver-east' => [
        ['Champlain Heights','','','',''],['Collingwood VE','','','',''],
        ['Downtown VE','','','',''],['Fraser VE','','','',''],
        ['Fraserview VE','','','',''],['Grandview Woodland','','','',''],
        ['Hastings','','','',''],['Hastings Sunrise','','','',''],
        ['Killarney VE','','','',''],['Knight','','','',''],
        ['Main','','','',''],['Mount Pleasant VE','','','',''],
        ['Renfrew Heights','','','',''],['Renfrew VE','','','',''],
        ['South Marine','','','',''],['South Vancouver','','','',''],
        ['Strathcona','','','',''],['Victoria VE','','','',''],
    ],
    'vancouver-west' => [
        ['Arbutus Ridge','','','',''],['Coal Harbour','','','',''],
        ['Downtown VW','','','',''],['Dunbar Southlands','','','',''],
        ['Fairview VW','','','',''],['False Creek','','','',''],
        ['Kerrisdale','','','',''],['Kitsilano','','','',''],
        ['Mackenzie Heights','','','',''],['Marpole','','','',''],
        ['Mount Pleasant VW','','','',''],['Oakridge','','','',''],
        ['Point Grey','','','',''],['Quilchena','','','',''],
        ['Riley Park','','','',''],['Shaughnessy','','','',''],
        ['South Cambie','','','',''],['South Granville','','','',''],
        ['SW Marine','','','',''],['University VW','','','',''],
        ['West End VW','','','',''],['Yaletown','','','',''],
    ],
];

if (isset($_GET['hpi_template'])) {
    $area_key   = $_GET['hpi_template'];
    $area_rows  = $_rebgv_area_rows[$area_key] ?? $_rebgv_area_rows['vancouver-east'];
    $area_label = str_replace('-', '_', $area_key);
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"hpi_import_{$area_label}.csv\"");
    $hdr = ['month_year','neighbourhood_rebgv','price_detached','price_duplex',
            'price_condo','price_townhouse','yoy_detached','yoy_duplex',
            'yoy_condo','yoy_townhouse','sales_detached','sales_duplex',
            'sales_condo','sales_townhouse','dom_detached','dom_duplex',
            'dom_condo','dom_townhouse'];
    $out = fopen('php://output','w'); fputcsv($out, $hdr);
    foreach ($area_rows as $r) { fputcsv($out, array_merge([$_GET['month'] ?? date('Y-m-01')], $r)); }
    fclose($out); exit;
}

// ── REBGV name strings → COV neighbourhood slugs (for HPI bulk import) ─────
// Maps REBGV neighbourhood names to COV official slugs at save time.
// Multiple REBGV areas map to one COV neighbourhood — data weighted by sales volume.
$_rebgv_aliases = [
    // Vancouver East → COV
    'champlain heights'    => 'sunset',
    'collingwood ve'       => 'renfrew-collingwood',
    'downtown ve'          => 'strathcona',
    'fraser ve'            => 'kensington-cedar-cottage',
    'fraserview ve'        => 'victoria-fraserview',
    'grandview woodland'   => 'grandview-woodland',
    'hastings'             => 'hastings-sunrise',
    'hastings sunrise'     => 'hastings-sunrise',
    'killarney ve'         => 'killarney',
    'knight'               => 'knight',
    'main'                 => 'kensington-cedar-cottage',
    'mount pleasant ve'    => 'mount-pleasant',
    'renfrew heights'      => 'renfrew-collingwood',
    'renfrew ve'           => 'renfrew-collingwood',
    'south marine'         => 'renfrew-collingwood',
    'south vancouver'      => 'victoria-fraserview',
    'strathcona'           => 'strathcona',
    'victoria ve'          => 'victoria-fraserview',
    // Vancouver West → COV
    'arbutus ridge'        => 'arbutus-ridge',
    'coal harbour'         => 'downtown',
    'downtown vw'          => 'downtown',
    'dunbar southlands'    => 'dunbar-southlands',
    'fairview vw'          => 'fairview',
    'false creek'          => 'downtown',
    'kerrisdale'           => 'kerrisdale',
    'kitsilano'            => 'kitsilano',
    'mackenzie heights'    => 'dunbar-southlands',
    'marpole'              => 'marpole',
    'mount pleasant vw'    => 'mount-pleasant',
    'oakridge'             => 'oakridge',
    'point grey'           => 'west-point-grey',
    'quilchena'            => 'shaughnessy',
    'riley park'           => 'riley-park',
    'shaughnessy'          => 'shaughnessy',
    'south cambie'         => 'south-cambie',
    'south granville'      => 'shaughnessy',
    'sw marine'            => 'marpole',
    'university vw'        => 'west-point-grey',
    'west end vw'          => 'west-end',
    'yaletown'             => 'downtown',
];

function _hpi_fuzzy_match(string $name, array $nb_lkp, array $aliases): ?int {
    $key = strtolower(trim($name));
    if (isset($aliases[$key])) {
        $slug = $aliases[$key];
        return $nb_lkp[$slug] ?? null;
    }
    $slug = preg_replace('/[^a-z0-9]+/','-', $key);
    $slug = trim($slug,'-');
    return $nb_lkp[$slug] ?? null;
}

// ── HPI Bulk POST handler ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hpi_bulk_confirm'])) {
    $rows_json = $_POST['hpi_rows_json'] ?? '[]';
    $rows      = json_decode($rows_json, true) ?: [];
    $saved = 0;
    foreach ($rows as $r) {
        if (empty($r['nb_id']) || empty($r['month_dt'])) continue;
        $nb_id    = (int)$r['nb_id'];
        $month_dt = $r['month_dt'];
        $fields = [
            'avg_price'       => $r['price_duplex'] ?? null,
            'price_detached'  => $r['price_detached'] ?? null,
            'price_condo'     => $r['price_condo'] ?? null,
            'price_townhouse' => $r['price_townhouse'] ?? null,
            'price_duplex'    => $r['price_duplex'] ?? null,
            'hpi_change_mom'  => null,
            'hpi_change_yoy'  => $r['yoy_duplex'] ?? null,
            'dom_detached'    => $r['dom_detached'] ?? null,
            'dom_duplex'      => $r['dom_duplex'] ?? null,
            'dom_condo'       => $r['dom_condo'] ?? null,
            'dom_townhouse'   => $r['dom_townhouse'] ?? null,
            'sales_detached'  => $r['sales_detached'] ?? null,
            'sales_duplex'    => $r['sales_duplex'] ?? null,
            'sales_condo'     => $r['sales_condo'] ?? null,
            'sales_townhouse' => $r['sales_townhouse'] ?? null,
        ];
        $exists = $pdo->prepare("SELECT id FROM neighbourhood_hpi_history WHERE neighbourhood_id=? AND month_year=?");
        $exists->execute([$nb_id, $month_dt]);
        if ($exists->fetchColumn()) {
            $sets = implode(',', array_map(fn($k) => "$k=:$k", array_keys($fields)));
            $pdo->prepare("UPDATE neighbourhood_hpi_history SET $sets WHERE neighbourhood_id=:nb_id AND month_year=:month")
                ->execute(array_merge($fields, [':nb_id'=>$nb_id,':month'=>$month_dt]));
        } else {
            $cols = implode(',', array_keys($fields));
            $vals = ':' . implode(',:', array_keys($fields));
            $pdo->prepare("INSERT INTO neighbourhood_hpi_history (neighbourhood_id,month_year,$cols) VALUES (:nb_id,:month,$vals)")
                ->execute(array_merge($fields, [':nb_id'=>$nb_id,':month'=>$month_dt]));
        }
        $saved++;
    }
    header("Location: plex-data.php?tab=market-prices&msg=" . urlencode("✅ HPI import complete — {$saved} neighbourhoods updated"));
    exit;
}

// ── HPI CSV Parse POST ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['hpi_csv'])) {
    header('Content-Type: application/json');
    $file = $_FILES['hpi_csv'];
    if ($file['error'] !== UPLOAD_ERR_OK) { echo json_encode(['success'=>false,'error'=>'Upload error']); exit; }
    $month_raw = $_POST['hpi_month'] ?? date('Y-m');
    $month_dt  = $month_raw . '-01';

    $nb_lkp = [];
    foreach ($neighbourhoods as $nb) { $nb_lkp[$nb['slug']] = (int)$nb['id']; }

    $handle = fopen($file['tmp_name'], 'r');
    $header = fgetcsv($handle);
    if (!$header) { echo json_encode(['success'=>false,'error'=>'Empty CSV']); exit; }
    $col = array_flip(array_map('trim', $header));

    $g = fn($k) => isset($col[$k]) ? trim($header[$col[$k]] ?? '') : '';

    $preview = []; $unmatched = [];
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 2) continue;
        $g = fn($k) => isset($col[$k]) && isset($row[$col[$k]]) ? trim($row[$col[$k]]) : '';
        $nb_name = $g('neighbourhood_rebgv') ?: $g('neighbourhood') ?: ($row[1] ?? '');
        if (!$nb_name) continue;
        $nb_id   = _hpi_fuzzy_match($nb_name, $nb_lkp, $_rebgv_aliases);
        $nb_slug = '';
        foreach ($nb_lkp as $s => $id) { if ($id === $nb_id) { $nb_slug = $s; break; } }
        $entry = [
            'nb_name_rebgv'  => $nb_name,
            'nb_id'          => $nb_id,
            'nb_slug'        => $nb_slug,
            'month_dt'       => $month_dt,
            'price_detached' => $g('price_detached') !== '' ? (int)str_replace([',','$'],'',$g('price_detached')) : null,
            'price_duplex'   => $g('price_duplex')   !== '' ? (int)str_replace([',','$'],'',$g('price_duplex'))   : null,
            'price_condo'    => $g('price_condo')    !== '' ? (int)str_replace([',','$'],'',$g('price_condo'))    : null,
            'price_townhouse'=> $g('price_townhouse')!== '' ? (int)str_replace([',','$'],'',$g('price_townhouse')): null,
            'yoy_duplex'     => $g('yoy_duplex')     !== '' ? (float)$g('yoy_duplex')     : null,
            'dom_duplex'     => $g('dom_duplex')      !== '' ? (int)$g('dom_duplex')       : null,
            'sales_duplex'   => $g('sales_duplex')   !== '' ? (int)$g('sales_duplex')     : null,
            'dom_detached'   => $g('dom_detached')   !== '' ? (int)$g('dom_detached')     : null,
            'sales_detached' => $g('sales_detached') !== '' ? (int)$g('sales_detached')   : null,
            'dom_condo'      => $g('dom_condo')       !== '' ? (int)$g('dom_condo')        : null,
            'sales_condo'    => $g('sales_condo')    !== '' ? (int)$g('sales_condo')      : null,
            'dom_townhouse'  => $g('dom_townhouse')  !== '' ? (int)$g('dom_townhouse')    : null,
            'sales_townhouse'=> $g('sales_townhouse')!== '' ? (int)$g('sales_townhouse')  : null,
        ];
        $preview[] = $entry;
        if (!$nb_id) $unmatched[] = $nb_name;
    }
    fclose($handle);
    echo json_encode(['success'=>true,'rows'=>$preview,'unmatched'=>$unmatched,'month'=>$month_dt]);
    exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>W.I.N — Wynston Intelligent Navigator</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
:root{--brand:#002446;--gold:#c9a84c;--blue:#0065ff;--green:#22c55e;--red:#ef4444;--cream:#f9f6f0;}
*{box-sizing:border-box;}
body{background:#f0f4f8;font-family:'Segoe UI',system-ui,sans-serif;margin:0;padding:0;}
.top-bar{background:var(--brand);padding:14px 28px;display:flex;align-items:center;justify-content:space-between;}
.top-bar h1{color:var(--gold);font-size:16px;font-weight:800;margin:0;letter-spacing:.5px;}
.top-bar a{color:rgba(255,255,255,.5);font-size:12px;text-decoration:none;}
.top-bar a:hover{color:#fff;}
.tab-nav{background:#fff;border-bottom:1px solid #e2e8f0;padding:0 24px;display:flex;gap:0;overflow-x:auto;}
.tab-nav a{padding:13px 16px;font-size:12px;font-weight:700;text-decoration:none;border-bottom:3px solid transparent;color:#666;white-space:nowrap;display:flex;align-items:center;gap:5px;transition:.15s;}
.tab-nav a.active{border-bottom-color:var(--brand);color:var(--brand);}
.tab-nav a.tab-outlook.active{border-bottom-color:var(--gold);color:var(--gold);}
.tab-nav a.tab-pop.active{border-bottom-color:#16a34a;color:#16a34a;}
.content{padding:24px;max-width:980px;}
.card{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:24px;margin-bottom:20px;}
.card h3{font-size:15px;font-weight:800;color:var(--brand);margin:0 0 3px;}
.card .sub{font-size:12px;color:#888;margin-bottom:18px;}
.form-label{font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.3px;margin-bottom:4px;display:block;}
.form-control,.form-select{font-size:13px;border-radius:7px;border:1px solid #dde;padding:8px 11px;width:100%;}
.form-control:focus,.form-select:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(0,101,255,.1);outline:none;}
.btn-primary-w{background:var(--brand);color:#fff;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:7px;transition:.2s;}
.btn-primary-w:hover{background:#003a7a;}
.btn-gold{background:var(--gold);color:var(--brand);border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:7px;}
.btn-gold:hover{background:#d4b35c;}
.btn-outline{background:#fff;color:var(--brand);border:1.5px solid #dde;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;}
.btn-outline:hover{border-color:var(--brand);}
.dz{border:2px dashed #c9a84c;border-radius:10px;background:#fffbf0;padding:28px;text-align:center;cursor:pointer;transition:.2s;}
.dz:hover,.dz.dz-active{background:#fff8e6;border-color:#b8922a;}
.dz.dz-selected{background:#f0fdf4;border-color:#16a34a;}
.dz-icon{font-size:28px;color:#c9a84c;margin-bottom:8px;}
.dz-text{font-size:13px;color:#888;}
.result-box{padding:12px 16px;border-radius:8px;font-size:13px;margin-top:10px;}
.result-ok{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;}
.result-err{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;}
.result-info{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;}
.preview-table{width:100%;border-collapse:collapse;font-size:12px;}
.preview-table th{background:#f0f4fb;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#555;padding:8px 10px;border-bottom:1px solid #e2e8f0;text-align:left;}
.preview-table td{padding:8px 10px;border-bottom:1px solid #f4f6fa;}
.preview-table tr:last-child td{border-bottom:none;}
.badge-pill{display:inline-block;padding:2px 9px;border-radius:20px;font-size:10px;font-weight:700;}
.section-divider{border:none;border-top:2px solid #f0f4f8;margin:28px 0;}
.source-row{background:#f9f6f0;border-radius:8px;padding:14px;margin-bottom:10px;}
.source-row label{font-size:11px;font-weight:700;color:#444;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;display:block;}
.sources-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
@media(max-width:700px){.sources-grid{grid-template-columns:1fr;}}
.outlook-bar{height:4px;background:#f0f0f0;border-radius:2px;margin-top:4px;overflow:hidden;}
.outlook-fill{height:100%;border-radius:2px;}
.pop-gap-bull{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:6px;padding:8px 12px;font-size:12px;font-weight:700;}
.pop-gap-bear{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;border-radius:6px;padding:8px 12px;font-size:12px;font-weight:700;}
.pop-gap-neutral{background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;border-radius:6px;padding:8px 12px;font-size:12px;font-weight:700;}
.info-box{background:#f9f6f0;border-left:3px solid var(--gold);border-radius:6px;padding:10px 14px;font-size:12px;color:#666;line-height:1.6;margin-bottom:14px;}
.freq-badge{font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;background:#f1f5f9;color:#475569;}
</style>
</head>
<body>

<div class="top-bar">
  <h1><i class="fas fa-chart-line me-2"></i>W.I.N — Wynston Intelligent Navigator</h1>
  <div style="display:flex;gap:16px;align-items:center;">
    <a href="admin.php"><i class="fas fa-arrow-left me-1"></i>Back to Admin</a>
    <a href="plex-data.php?logout=1"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
  </div>
</div>

<?php if (!empty($_GET['msg'])): ?>
<div style="background:#f0fdf4;color:#15803d;border-bottom:1px solid #bbf7d0;padding:10px 24px;font-size:13px;font-weight:600;">
    <?= htmlspecialchars($_GET['msg']) ?>
</div>
<?php endif; ?>

<!-- Tab nav -->
<div class="tab-nav">
    <a href="plex-data.php?tab=market-prices" class="<?= $active_tab==='market-prices'?'active':'' ?>">
        <i class="fas fa-chart-bar"></i> Market Prices</a>
    <a href="plex-data.php?tab=channel-b" class="<?= $active_tab==='channel-b'?'active':'' ?>">
        <i class="fas fa-home"></i> Rental Data</a>
    <a href="plex-data.php?tab=channel-d" class="<?= $active_tab==='channel-d'?'active':'' ?>">
        <i class="fas fa-landmark"></i> CMHC Benchmarks</a>
    <a href="plex-data.php?tab=outlook" class="tab-outlook <?= $active_tab==='outlook'?'active':'' ?>">
        <i class="fas fa-binoculars"></i> Wynston Outlook</a>
    <a href="plex-data.php?tab=costs" class="<?= $active_tab==='costs'?'active':'' ?>">
        <i class="fas fa-hammer"></i> Build Costs</a>
    <a href="plex-data.php?tab=population" class="tab-pop <?= $active_tab==='population'?'active':'' ?>">
        <i class="fas fa-users"></i> Population &amp; Census</a>
    <a href="plex-data.php?tab=history" class="<?= $active_tab==='history'?'active':'' ?>">
        <i class="fas fa-history"></i> Upload History</a>
</div>

<div class="content">

<?php // ══════════════════════════════════════════════════════════════════════
      // TAB 1 — MARKET PRICES
      // Section A: REBGV Sold $/sqft CSV  →  monthly_market_stats
      // Section B: Neighbourhood HPI Bulk →  neighbourhood_hpi_history
      // ══════════════════════════════════════════════════════════════════════
if ($active_tab === 'market-prices'): ?>

<!-- ── Section A: REBGV Sold $/sqft ────────────────────────────────────────── -->
<div class="card">
    <h3><i class="fas fa-file-csv me-2" style="color:var(--blue)"></i>Section A — REBGV Sold $/sqft
        <span class="freq-badge ms-2">Monthly</span></h3>
    <p class="sub">Upload the REBGV new-build sold CSV (Yr Blt ≥ 2024). Geocoded via Mapbox. Feeds pro forma exit values and confidence tiers. Upload Duplex CSV first, then Detached CSV.</p>
    <div class="info-box">
        <strong>REBGV → COV mapping:</strong> Multiple REBGV sub-areas map to one COV neighbourhood (e.g. Collingwood VE + Renfrew VE + Renfrew Heights + Renfrew East → renfrew-collingwood).
        The weighted average by sales volume is calculated automatically — more sales = more weight. No action needed.
    </div>

    <div id="chA-result"></div>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Data Month</label>
            <input type="month" id="chA_month" class="form-control" value="<?= date('Y-m') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">CSV Type</label>
            <select id="chA_type" class="form-select">
                <option value="duplex">Duplex (primary exit metric)</option>
                <option value="detached">Detached (ceiling benchmark)</option>
            </select>
        </div>
    </div>
    <div class="dz" id="chA_dz" onclick="document.getElementById('chA_file').click()">
        <div><i class="fas fa-file-csv dz-icon" id="chA_dz_icon"></i></div>
        <div class="dz-text" id="chA_dz_text">Drag &amp; drop REBGV CSV here, or click to browse</div>
        <input type="file" id="chA_file" accept=".csv" style="display:none">
    </div>
    <div id="chA_progress" style="display:none;margin-top:12px;padding:12px;background:#f0f4fb;border-radius:8px;font-size:13px;color:#002446;">
        <i class="fas fa-spinner fa-spin me-2"></i><span id="chA_progress_msg">Parsing CSV…</span>
    </div>
    <div id="chA_stats" class="row g-2 mt-2"></div>
    <div id="chA_errors" style="color:#b91c1c;font-size:12px;margin-top:8px;"></div>
    <button class="btn-primary-w mt-3" id="chA_submit" onclick="submitChannelA()">
        <i class="fas fa-upload"></i>Upload &amp; Process
    </button>
    <div id="chA_preview" style="display:none;margin-top:16px;overflow-x:auto;max-height:360px;overflow-y:auto;">
        <div id="chA_table_wrap"></div>
    </div>
</div>

<hr class="section-divider">

<!-- ── Section B: Neighbourhood HPI Bulk ───────────────────────────────────── -->
<div class="card">
    <h3><i class="fas fa-table me-2" style="color:var(--blue)"></i>Section B — Neighbourhood HPI &amp; DOM Bulk Import
        <span class="freq-badge ms-2">Monthly</span></h3>
    <p class="sub">Upload REBGV HPI report data — average prices, days on market, YoY%, and sales counts per neighbourhood. Feeds the Wynston Outlook local momentum layer and market velocity display.</p>
    <div class="info-box">
        <strong>What this feeds:</strong> DOM (days on market) for the map side panel velocity arrow.
        Price by type for the Outlook Layer 2 local momentum signal.
        Sales count for the confidence tier (T1 = 5+ duplex sales).
    </div>

    <div id="hpi-result"></div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label">Data Month</label>
            <input type="month" id="hpi_month" class="form-control" value="<?= date('Y-m') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Area Template</label>
            <select id="hpi_area" class="form-select" onchange="updateHpiTemplateLink()">
                <option value="vancouver-east">Vancouver East</option>
                <option value="vancouver-west">Vancouver West</option>
            </select>
        </div>
        <div class="col-md-5" style="display:flex;align-items:flex-end;">
            <a id="hpi_tpl_link" href="#" class="btn-outline" style="font-size:12px;" onclick="downloadHpiTemplate(event)">
                <i class="fas fa-download me-1"></i>Download Template
            </a>
        </div>
    </div>

    <div class="dz" id="hpi_dz" onclick="document.getElementById('hpi_file').click()">
        <div><i class="fas fa-file-csv dz-icon" id="hpi_dz_icon"></i></div>
        <div class="dz-text" id="hpi_dz_text">Drag &amp; drop HPI CSV here, or click to browse</div>
        <input type="file" id="hpi_file" accept=".csv" style="display:none">
    </div>

    <button class="btn-primary-w mt-3" onclick="uploadHpiCsv()">
        <i class="fas fa-eye"></i>Preview &amp; Match
    </button>

    <div id="hpi_preview_wrap" style="display:none;margin-top:20px;">
        <h5 style="font-size:13px;font-weight:700;color:#002446;margin-bottom:10px;">Preview — Review before saving</h5>
        <div id="hpi_unmatched_warn" style="display:none;margin-bottom:10px;"></div>
        <div style="overflow-x:auto;max-height:400px;overflow-y:auto;">
        <table class="preview-table" id="hpi_preview_table">
            <thead><tr>
                <th><input type="checkbox" id="hpiCheckAll"> All</th>
                <th>REBGV Name</th><th>→ COV Neighbourhood</th>
                <th>Price Duplex</th><th>DOM Duplex</th>
                <th>Sales Duplex</th><th>YoY %</th>
            </tr></thead>
            <tbody id="hpi_preview_tbody"></tbody>
        </table>
        </div>
        <form method="POST" id="hpi_confirm_form" action="plex-data.php?tab=market-prices">
            <input type="hidden" name="hpi_bulk_confirm" value="1">
            <input type="hidden" name="hpi_rows_json" id="hpi_rows_json" value="[]">
            <div class="d-flex gap-3 mt-3">
                <button type="button" class="btn-primary-w" onclick="submitHpiBulk()">
                    <i class="fas fa-save"></i>Save Selected Rows
                </button>
                <button type="button" class="btn-outline" onclick="document.getElementById('hpi_preview_wrap').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($hpi_recent)): ?>
    <div style="margin-top:20px;overflow-x:auto;max-height:260px;overflow-y:auto;">
        <div style="font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Recent HPI Data</div>
        <table class="preview-table">
            <thead><tr><th>Neighbourhood</th><th>Month</th><th>Duplex Avg $</th><th>DOM</th><th>Sales</th><th>YoY%</th></tr></thead>
            <tbody>
            <?php foreach ($hpi_recent as $h): ?>
            <tr>
                <td style="font-weight:600;"><?= htmlspecialchars($h['name']) ?></td>
                <td><?= date('M Y', strtotime($h['month_year'])) ?></td>
                <td><?= $h['price_duplex'] ? '$'.number_format($h['price_duplex']) : '—' ?></td>
                <td><?= $h['dom_duplex'] ?? '—' ?></td>
                <td><?= $h['sales_duplex'] ?? '—' ?></td>
                <td style="color:<?= ($h['hpi_change_yoy']??0)>=0?'#15803d':'#b91c1c' ?>">
                    <?= $h['hpi_change_yoy'] !== null ? (($h['hpi_change_yoy']>=0?'+':'').number_format((float)$h['hpi_change_yoy'],1).'%') : '—' ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php // ══════════════════════════════════════════════════════════════════════
      // TAB 2 — RENTAL DATA (3 sources)
      // ══════════════════════════════════════════════════════════════════════
elseif ($active_tab === 'channel-b'): ?>

<div class="card">
    <h3><i class="fas fa-home me-2" style="color:var(--blue)"></i>Rental Data — Monthly Entry
        <span class="freq-badge ms-2">Monthly</span></h3>
    <p class="sub">Enter rental data from each source below. Select a neighbourhood to see the current Wynston rental estimate for that area.</p>

    <!-- Neighbourhood + Month selector shared across all sections -->
    <div class="row g-3 mb-4">
        <div class="col-md-5">
            <label class="form-label">Neighbourhood</label>
            <select id="rent_slug" class="form-select" onchange="loadRentPreview()">
                <option value="">— Select neighbourhood —</option>
                <?php foreach ($cov_neighbourhoods as $nb): ?>
                <option value="<?= htmlspecialchars($nb['slug']) ?>"><?= htmlspecialchars($nb['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Data Month</label>
            <input type="month" id="rent_month" class="form-control" value="<?= date('Y-m') ?>" onchange="loadRentPreview()">
        </div>
    </div>

    <!-- Live Wynston Estimate Preview -->
    <div id="rent_preview" style="display:none;background:#002446;border-radius:10px;padding:18px 22px;margin-bottom:24px;">
        <div style="font-size:10px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;color:rgba(201,168,76,.7);margin-bottom:12px;">
            Wynston Rental Estimate — <span id="prev_nb_name"></span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;" id="prev_grid">
            <!-- filled by JS -->
        </div>
        <div style="font-size:10px;color:rgba(255,255,255,.3);margin-top:12px;border-top:1px solid rgba(255,255,255,.08);padding-top:10px;">
            Rental estimates are calculated from multiple market sources and updated monthly.
            Refer to the Disclaimer on all reports for full methodology disclosure.
        </div>
    </div>

    <hr class="section-divider">

    <!-- Source 1: liv.rent -->
    <div style="margin-bottom:28px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="background:#eff6ff;border-radius:8px;padding:7px 12px;font-size:11px;font-weight:800;color:#1d4ed8;letter-spacing:.5px;">SOURCE 1</div>
            <div style="font-size:14px;font-weight:800;color:#002446;">liv.rent</div>
            <span class="freq-badge">Monthly</span>
        </div>
        <div id="rent_livrent_result"></div>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">1BR / mo</label>
                <div class="input-group"><span class="input-group-text">$</span>
                <input type="number" id="lr_1br" class="form-control" placeholder="e.g. 2100" oninput="updatePreview()"></div>
            </div>
            <div class="col-md-3">
                <label class="form-label">2BR / mo</label>
                <div class="input-group"><span class="input-group-text">$</span>
                <input type="number" id="lr_2br" class="form-control" placeholder="e.g. 2750" oninput="updatePreview()"></div>
            </div>
            <div class="col-md-3">
                <label class="form-label">3BR / mo</label>
                <div class="input-group"><span class="input-group-text">$</span>
                <input type="number" id="lr_3br" class="form-control" placeholder="e.g. 3200" oninput="updatePreview()"></div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Furnished Premium %</label>
                <div class="input-group">
                <input type="number" id="lr_furn" class="form-control" value="20">
                <span class="input-group-text">%</span></div>
            </div>
        </div>
        <button class="btn-primary-w mt-3" onclick="saveRentSource('livrent')" style="max-width:220px;">
            <i class="fas fa-save"></i>Save liv.rent Data
        </button>
    </div>

    <hr class="section-divider">

    <!-- Source 2: REBGV Rental -->
    <div style="margin-bottom:28px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="background:#f0fdf4;border-radius:8px;padding:7px 12px;font-size:11px;font-weight:800;color:#15803d;letter-spacing:.5px;">SOURCE 2</div>
            <div style="font-size:14px;font-weight:800;color:#002446;">REBGV Rental Data</div>
            <span class="freq-badge">Monthly</span>
        </div>
        <div class="info-box" style="margin-bottom:12px;">
            From the REBGV monthly rental supplement or rental board data. If REBGV does not publish rental data for a neighbourhood this month, leave blank — the estimate will use the remaining sources.
        </div>
        <div id="rent_rebgv_result"></div>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">1BR / mo</label>
                <div class="input-group"><span class="input-group-text">$</span>
                <input type="number" id="rb_1br" class="form-control" placeholder="e.g. 2080" oninput="updatePreview()"></div>
            </div>
            <div class="col-md-3">
                <label class="form-label">2BR / mo</label>
                <div class="input-group"><span class="input-group-text">$</span>
                <input type="number" id="rb_2br" class="form-control" placeholder="e.g. 2700" oninput="updatePreview()"></div>
            </div>
            <div class="col-md-3">
                <label class="form-label">3BR / mo</label>
                <div class="input-group"><span class="input-group-text">$</span>
                <input type="number" id="rb_3br" class="form-control" placeholder="e.g. 3150" oninput="updatePreview()"></div>
            </div>
        </div>
        <button class="btn-primary-w mt-3" onclick="saveRentSource('rebgv')" style="max-width:220px;">
            <i class="fas fa-save"></i>Save REBGV Rental Data
        </button>
    </div>

    <hr class="section-divider">

    <!-- Source 3: CMHC read-only reference -->
    <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="background:#fef3c7;border-radius:8px;padding:7px 12px;font-size:11px;font-weight:800;color:#92400e;letter-spacing:.5px;">SOURCE 3</div>
            <div style="font-size:14px;font-weight:800;color:#002446;">CMHC Annual Benchmark</div>
            <span class="freq-badge">Annual — enter in CMHC tab</span>
        </div>
        <div id="cmhc_ref_display" style="background:#f9f6f0;border-radius:8px;padding:14px 18px;font-size:13px;color:#666;">
            <i class="fas fa-info-circle me-2" style="color:#c9a84c;"></i>
            Select a neighbourhood above to see stored CMHC benchmarks for that area.
            To update CMHC data, use the <a href="plex-data.php?tab=channel-d" style="color:var(--blue);font-weight:700;">CMHC Benchmarks tab</a>.
        </div>
    </div>
</div>

<?php // ══════════════════════════════════════════════════════════════════════
      // TAB 3 — CMHC BENCHMARKS
      // ══════════════════════════════════════════════════════════════════════
elseif ($active_tab === 'channel-d'): ?>

<div class="card">
    <h3><i class="fas fa-landmark me-2" style="color:var(--blue)"></i>CMHC Annual Benchmarks
        <span class="freq-badge ms-2">Annual — January</span></h3>
    <p class="sub">Enter CMHC annual benchmark rents. These are the "floor" comparison for the 3-comparable rental display. Next update due January 2027 when CMHC releases new Rental Market Report.</p>
    <div id="chD-result"></div>
    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label">Neighbourhood</label>
            <select id="chD_slug" class="form-select">
                <option value="">— Select neighbourhood —</option>
                <?php foreach ($cov_neighbourhoods as $nb): ?>
                <option value="<?= htmlspecialchars($nb['slug']) ?>"><?= htmlspecialchars($nb['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Year</label>
            <input type="number" id="chD_year" class="form-control" value="<?= date('Y') ?>" min="2020" max="2035">
        </div>
    </div>
    <div class="row g-3 mt-1">
        <div class="col-md-3">
            <label class="form-label">1BR Benchmark / mo</label>
            <div class="input-group"><span class="input-group-text">$</span>
            <input type="number" id="chD_1br" class="form-control" placeholder="e.g. 2050"></div>
        </div>
        <div class="col-md-3">
            <label class="form-label">2BR Benchmark / mo</label>
            <div class="input-group"><span class="input-group-text">$</span>
            <input type="number" id="chD_2br" class="form-control" placeholder="e.g. 2650"></div>
        </div>
        <div class="col-md-3">
            <label class="form-label">3BR Benchmark / mo</label>
            <div class="input-group"><span class="input-group-text">$</span>
            <input type="number" id="chD_3br" class="form-control" placeholder="e.g. 3100"></div>
        </div>
    </div>
    <button class="btn-primary-w mt-3" onclick="submitChannelD()" style="max-width:240px;">
        <i class="fas fa-save"></i>Save CMHC Benchmark
    </button>
</div>

<?php if (!empty($cmhc_rows)): ?>
<div class="card">
    <h3>CMHC Benchmark History</h3>
    <p class="sub"><?= count($cmhc_rows) ?> rows stored</p>
    <div style="overflow-x:auto;">
    <table class="preview-table">
        <thead><tr><th>Neighbourhood</th><th>Year</th><th>1BR</th><th>2BR</th><th>3BR</th></tr></thead>
        <tbody>
        <?php foreach ($cmhc_rows as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['neighbourhood_slug']) ?></td>
            <td><?= (int)$r['year'] ?></td>
            <td><?= $r['benchmark_1br'] ? '$'.number_format($r['benchmark_1br']) : '—' ?></td>
            <td><?= $r['benchmark_2br'] ? '$'.number_format($r['benchmark_2br']) : '—' ?></td>
            <td><?= $r['benchmark_3br'] ? '$'.number_format($r['benchmark_3br']) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php // ══════════════════════════════════════════════════════════════════════
      // TAB 4 — WYNSTON OUTLOOK
      // ══════════════════════════════════════════════════════════════════════
elseif ($active_tab === 'outlook'): ?>

<div class="card">
    <h3><i class="fas fa-binoculars me-2" style="color:var(--gold)"></i>Wynston Outlook — Quarterly Forecast
        <span class="freq-badge ms-2">Quarterly</span></h3>
    <p class="sub">Enter the 6 bank/broker YoY $/PSF forecasts, then click Calculate. The formula runs 5 layers: Macro (30%) + Local HPI (35%) + Pipeline (10%) + Population (10%) + Supply signal (15%).</p>
    <div class="info-box">
        <strong>Layer weights (5-layer formula):</strong>
        Macro bank forecasts 30% · Local HPI momentum + DOM 35% · Pipeline signal 10% · Population supply-demand gap 10% · CMHC supply/starts 15%.
        Population layer activates once Census data is entered in the Population tab.
        Bank forecasts already reflect BoC rate expectations — no separate rate input needed.
    </div>
    <div id="outlook-result"></div>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Quarter</label>
            <select id="ol_quarter" class="form-select">
                <?php
                $qy = (int)date('Y'); $qm = (int)date('m');
                for ($i=0;$i<8;$i++) {
                    $q = 'Q'.(((ceil($qm/3)-1-$i+16)%4)+1);
                    $y = $qy - (int)floor((4-ceil($qm/3)+$i)/4);
                    $val = "{$y}-Q".(((ceil($qm/3)-1-$i+16)%4)+1);
                    echo "<option value=\"{$val}\"\".($i===0?' selected':'').\">{$val}</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <h5 style="font-size:12px;font-weight:700;color:#555;margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">Bank/Broker Forecasts — YoY $/PSF Change (%)</h5>
    <div class="sources-grid" id="sources-grid">
        <?php
        $default_sources = ['RBC','TD','BMO','BCREA','RE-MAX','Royal LePage'];
        foreach ($default_sources as $src):
            $ev = '';
            foreach ($outlook_inputs as $oi) { if ($oi['source_name']===$src) { $ev=$oi['forecast_psf_yoy']; break; } }
        ?>
        <div class="source-row">
            <label><?= $src ?></label>
            <div class="row g-2">
                <div class="col-7"><div class="input-group">
                    <input type="number" step="0.1" class="form-control src-forecast"
                           data-source="<?= $src ?>" placeholder="e.g. 4.5" value="<?= htmlspecialchars($ev) ?>">
                    <span class="input-group-text" style="font-size:12px;">%</span>
                </div></div>
                <div class="col-5">
                    <input type="date" class="form-control src-date" data-source="<?= $src ?>" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="d-flex gap-3 mt-4">
        <button class="btn-primary-w" onclick="saveOutlookInputs()" style="max-width:180px;">
            <i class="fas fa-save"></i>Save Inputs
        </button>
        <button class="btn-gold" onclick="calculateOutlook()">
            <i class="fas fa-calculator"></i>Calculate Wynston Outlook
        </button>
    </div>
</div>

<?php if (!empty($outlook_results)): ?>
<div class="card">
    <h3>Current Outlook Results — <?= count($outlook_results) ?> neighbourhoods</h3>
    <p class="sub">Tier 1 = high confidence (5+ comps) · Tier 2 = 2–4 comps · Tier 3 = macro fallback</p>
    <div style="overflow-x:auto;">
    <table class="preview-table">
        <thead><tr><th>Neighbourhood</th><th>Outlook %</th><th>Confidence Band</th><th>Tier</th><th>Comps</th><th>Quarter</th></tr></thead>
        <tbody>
        <?php foreach ($outlook_results as $r):
            $pct = (float)$r['weighted_outlook'];
            $c   = $pct >= 5 ? '#15803d' : ($pct >= 0 ? '#c9a84c' : '#b91c1c');
        ?>
        <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($r['neighbourhood_slug']) ?></td>
            <td><span style="font-weight:800;color:<?= $c ?>;font-size:13px;"><?= $pct>=0?'+':'' ?><?= number_format($pct,1) ?>%</span>
                <div class="outlook-bar"><div class="outlook-fill" style="width:<?= min(100,max(0,($pct+15)/30*100)) ?>%;background:<?= $c ?>"></div></div>
            </td>
            <td style="font-size:11px;color:#888;"><?= number_format((float)$r['confidence_band_low'],1) ?>% — <?= number_format((float)$r['confidence_band_high'],1) ?>%</td>
            <td><?php $t=(int)$r['confidence_tier']; $tc=$t===1?'#15803d':($t===2?'#c9a84c':'#b91c1c'); ?>
                <span style="font-weight:700;color:<?= $tc ?>">T<?= $t ?></span></td>
            <td style="text-align:center;"><?= (int)$r['comp_count'] ?></td>
            <td style="font-size:11px;color:#888;"><?= htmlspecialchars($r['quarter']??'—') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php // ══════════════════════════════════════════════════════════════════════
      // TAB 5 — BUILD COSTS
      // ══════════════════════════════════════════════════════════════════════
elseif ($active_tab === 'costs'): ?>

<div class="card">
    <h3><i class="fas fa-hammer me-2" style="color:var(--blue)"></i>Construction Cost Overrides
        <span class="freq-badge ms-2">As needed</span></h3>
    <p class="sub">Set $/sqft cost ranges by neighbourhood. Defaults where no override: Standard $380–$450, Luxury $480–$550, DCL city $18.45, DCL utilities $2.95, Peat contingency $150,000.</p>
    <div id="costs-result"></div>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Neighbourhood</label>
            <select id="cost_slug" class="form-select">
                <option value="">— Select neighbourhood —</option>
                <?php foreach ($cov_neighbourhoods as $nb): ?>
                <option value="<?= htmlspecialchars($nb['slug']) ?>"><?= htmlspecialchars($nb['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-2"><label class="form-label">Std Low $/sqft</label><input type="number" id="cost_std_lo" class="form-control" placeholder="380"></div>
        <div class="col-md-2"><label class="form-label">Std High $/sqft</label><input type="number" id="cost_std_hi" class="form-control" placeholder="450"></div>
        <div class="col-md-2"><label class="form-label">Lux Low $/sqft</label><input type="number" id="cost_lux_lo" class="form-control" placeholder="480"></div>
        <div class="col-md-2"><label class="form-label">Lux High $/sqft</label><input type="number" id="cost_lux_hi" class="form-control" placeholder="550"></div>
        <div class="col-md-2"><label class="form-label">DCL City $/sqft</label><input type="number" step="0.01" id="cost_dcl_city" class="form-control" placeholder="18.45"></div>
        <div class="col-md-2"><label class="form-label">DCL Utils $/sqft</label><input type="number" step="0.01" id="cost_dcl_util" class="form-control" placeholder="2.95"></div>
    </div>
    <div class="row g-3 mt-0">
        <div class="col-md-3"><label class="form-label">Peat Contingency $</label>
            <div class="input-group"><span class="input-group-text">$</span>
            <input type="number" id="cost_peat" class="form-control" placeholder="150000"></div></div>
        <div class="col-md-5"><label class="form-label">Notes <small style="color:#aaa;">(optional)</small></label>
            <input type="text" id="cost_notes" class="form-control" placeholder="e.g. Heritage district premium"></div>
    </div>
    <button class="btn-primary-w mt-3" onclick="saveCost()" style="max-width:240px;">
        <i class="fas fa-save"></i>Save Cost Override
    </button>
</div>

<?php if (!empty($cost_rows)): ?>
<div class="card">
    <h3>Saved Cost Overrides</h3>
    <div style="overflow-x:auto;"><table class="preview-table">
        <thead><tr><th>Neighbourhood</th><th>Std Low</th><th>Std High</th><th>Lux Low</th><th>Lux High</th><th>DCL City</th><th>DCL Utils</th><th>Peat</th><th>Notes</th></tr></thead>
        <tbody>
        <?php foreach ($cost_rows as $r): ?>
        <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($r['neighbourhood_slug']) ?></td>
            <td>$<?= number_format((float)$r['cost_standard_low']) ?></td>
            <td>$<?= number_format((float)$r['cost_standard_high']) ?></td>
            <td>$<?= number_format((float)$r['cost_luxury_low']) ?></td>
            <td>$<?= number_format((float)$r['cost_luxury_high']) ?></td>
            <td>$<?= number_format((float)$r['dcl_city'],2) ?></td>
            <td>$<?= number_format((float)$r['dcl_utilities'],2) ?></td>
            <td>$<?= number_format((float)$r['peat_contingency']) ?></td>
            <td style="font-size:11px;color:#888;"><?= htmlspecialchars($r['notes']??'') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php // ══════════════════════════════════════════════════════════════════════
      // TAB 6 — POPULATION & CENSUS
      // ══════════════════════════════════════════════════════════════════════
elseif ($active_tab === 'population'): ?>

<div class="card">
    <h3><i class="fas fa-users me-2" style="color:#16a34a"></i>Population &amp; Census Data
        <span class="freq-badge ms-2">Every 5 years + annual BC Stats</span></h3>
    <p class="sub">Enter Stats Canada Census data per neighbourhood. Activates the Layer 4 population signal in the Wynston Outlook formula — household growth vs unit growth = supply-demand gap.</p>

    <div class="info-box">
        <strong>Where to get this data:</strong> COV publishes Local Area Profiles that aggregate Census data to each of the 22 neighbourhoods — saving you from Stats Canada's Census Tract math.
        Visit: <a href="https://vancouver.ca/your-government/local-area-profiles.aspx" target="_blank" style="color:var(--blue);font-weight:700;">vancouver.ca → Local Area Profiles</a>
        · Takes ~5 min per neighbourhood · Enter 2021 first, add 2026 data as Stats Canada releases it through 2026–2027.
        <br><br>
        <strong>Priority order:</strong> Renfrew-Collingwood · Mount Pleasant · Hastings-Sunrise · Kensington-Cedar-Cottage · Knight
    </div>

    <div id="pop-result"></div>

    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label">Neighbourhood</label>
            <select id="pop_slug" class="form-select">
                <option value="">— Select neighbourhood —</option>
                <?php foreach ($cov_neighbourhoods as $nb): ?>
                <option value="<?= htmlspecialchars($nb['slug']) ?>"><?= htmlspecialchars($nb['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Census Year</label>
            <select id="pop_year" class="form-select">
                <option value="2021">2021 Census</option>
                <option value="2026">2026 Census (partial)</option>
                <option value="2016">2016 Census (historical)</option>
            </select>
        </div>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-md-3">
            <label class="form-label">Total Population</label>
            <input type="number" id="pop_population" class="form-control" placeholder="e.g. 51890">
        </div>
        <div class="col-md-3">
            <label class="form-label">Total Households</label>
            <input type="number" id="pop_households" class="form-control" placeholder="e.g. 22140">
        </div>
        <div class="col-md-3">
            <label class="form-label">Housing Units Total</label>
            <input type="number" id="pop_units_total" class="form-control" placeholder="e.g. 23500">
        </div>
    </div>
    <div class="row g-3 mt-0">
        <div class="col-md-3">
            <label class="form-label">Owned Units</label>
            <input type="number" id="pop_units_owned" class="form-control" placeholder="e.g. 9200">
        </div>
        <div class="col-md-3">
            <label class="form-label">Rented Units</label>
            <input type="number" id="pop_units_rented" class="form-control" placeholder="e.g. 12900">
        </div>
        <div class="col-md-2">
            <label class="form-label">Median Age</label>
            <input type="number" step="0.1" id="pop_age" class="form-control" placeholder="e.g. 38.4">
        </div>
        <div class="col-md-4">
            <label class="form-label">Median Household Income $</label>
            <div class="input-group"><span class="input-group-text">$</span>
            <input type="number" id="pop_income" class="form-control" placeholder="e.g. 68000"></div>
        </div>
    </div>

    <button class="btn-primary-w mt-3" onclick="savePopulation()" style="max-width:240px;">
        <i class="fas fa-save"></i>Save Census Data
    </button>
</div>

<!-- Population data table -->
<?php if (!empty($pop_by_slug)): ?>
<div class="card">
    <h3>Stored Census Data — Supply-Demand Analysis</h3>
    <p class="sub">Green gap = household demand outpacing unit supply (bullish for rents &amp; prices). Red gap = oversupply signal.</p>
    <div style="overflow-x:auto;">
    <table class="preview-table">
        <thead>
            <tr>
                <th>Neighbourhood</th>
                <th>2021 Households</th><th>2021 Units</th>
                <th>2026 Households</th><th>2026 Units</th>
                <th>HH Growth</th><th>Unit Growth</th>
                <th>Supply-Demand Gap</th>
                <th>Outlook Signal</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pop_by_slug as $slug => $years):
            $y2021 = $years[2021] ?? null;
            $y2026 = $years[2026] ?? null;

            $hh_growth = null; $unit_growth = null; $gap = null;
            if ($y2021 && $y2026 && $y2021['total_households'] > 0 && $y2026['total_households'] > 0) {
                $hh_growth   = round((($y2026['total_households'] - $y2021['total_households']) / $y2021['total_households']) * 100, 1);
                $unit_growth = $y2021['housing_units_total'] > 0
                    ? round((($y2026['housing_units_total'] - $y2021['housing_units_total']) / $y2021['housing_units_total']) * 100, 1)
                    : null;
                $gap = $unit_growth !== null ? round($hh_growth - $unit_growth, 1) : null;
            }
        ?>
        <tr>
            <td style="font-weight:700;color:#002446;"><?= htmlspecialchars($slug) ?></td>
            <td><?= $y2021 ? number_format($y2021['total_households']) : '—' ?></td>
            <td><?= $y2021 ? number_format($y2021['housing_units_total']) : '—' ?></td>
            <td><?= $y2026 ? number_format($y2026['total_households']) : '<span style="color:#aaa;">not yet</span>' ?></td>
            <td><?= $y2026 ? number_format($y2026['housing_units_total']) : '<span style="color:#aaa;">not yet</span>' ?></td>
            <td style="color:<?= $hh_growth !== null ? ($hh_growth >= 0 ? '#15803d' : '#b91c1c') : '#aaa' ?>">
                <?= $hh_growth !== null ? ($hh_growth >= 0 ? '+' : '') . $hh_growth . '%' : '—' ?>
            </td>
            <td><?= $unit_growth !== null ? ($unit_growth >= 0 ? '+' : '') . $unit_growth . '%' : '—' ?></td>
            <td>
                <?php if ($gap !== null): ?>
                <div class="<?= $gap > 1 ? 'pop-gap-bull' : ($gap < -1 ? 'pop-gap-bear' : 'pop-gap-neutral') ?>">
                    <?= $gap >= 0 ? '+' : '' ?><?= $gap ?>%
                </div>
                <?php else: ?>
                <span style="color:#aaa;font-size:12px;">Need both years</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($gap !== null): ?>
                <span style="font-weight:700;color:<?= $gap > 1 ? '#15803d' : ($gap < -1 ? '#b91c1c' : '#64748b') ?>">
                    <?= $gap > 1 ? '▲ Bullish' : ($gap < -1 ? '▼ Bearish' : '● Neutral') ?>
                </span>
                <?php elseif ($y2021): ?>
                <span style="color:#c9a84c;font-size:12px;">Awaiting 2026 data</span>
                <?php else: ?>
                <span style="color:#aaa;font-size:12px;">No data</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <!-- Single-year data (2021 only) -->
    <div style="margin-top:20px;">
    <div style="font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">All Census Entries</div>
    <div style="overflow-x:auto;"><table class="preview-table">
        <thead><tr><th>Neighbourhood</th><th>Year</th><th>Population</th><th>Households</th><th>Units</th><th>Owned</th><th>Rented</th><th>Median Age</th><th>Median Income</th></tr></thead>
        <tbody>
        <?php foreach ($pop_rows as $pr): ?>
        <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($pr['neighbourhood_slug']) ?></td>
            <td><?= (int)$pr['census_year'] ?></td>
            <td><?= $pr['total_population'] ? number_format($pr['total_population']) : '—' ?></td>
            <td><?= $pr['total_households'] ? number_format($pr['total_households']) : '—' ?></td>
            <td><?= $pr['housing_units_total'] ? number_format($pr['housing_units_total']) : '—' ?></td>
            <td><?= $pr['housing_units_owned'] ? number_format($pr['housing_units_owned']) : '—' ?></td>
            <td><?= $pr['housing_units_rented'] ? number_format($pr['housing_units_rented']) : '—' ?></td>
            <td><?= $pr['median_age'] ?? '—' ?></td>
            <td><?= $pr['median_household_income'] ? '$'.number_format($pr['median_household_income']) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    </div>
</div>
<?php endif; ?>

<?php // ══════════════════════════════════════════════════════════════════════
      // TAB 7 — UPLOAD HISTORY
      // ══════════════════════════════════════════════════════════════════════
elseif ($active_tab === 'history'): ?>

<div class="card">
    <h3><i class="fas fa-history me-2" style="color:var(--blue)"></i>Upload History</h3>
    <p class="sub">All data in monthly_market_stats grouped by neighbourhood, month, and type. Only is_active=1 rows shown.</p>
    <?php if (empty($recent_uploads)): ?>
    <div style="text-align:center;padding:40px;color:#aaa;">
        <i class="fas fa-database" style="font-size:36px;display:block;margin-bottom:12px;opacity:.3;"></i>
        No uploads yet. Start with Market Prices → Section A.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;"><table class="preview-table">
        <thead><tr><th>Neighbourhood</th><th>Month</th><th>Type</th><th>Rows</th><th>Uploaded</th></tr></thead>
        <tbody>
        <?php foreach ($recent_uploads as $r): ?>
        <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($r['neighbourhood_slug']) ?></td>
            <td><?= date('M Y', strtotime($r['data_month'])) ?></td>
            <td><span class="badge-pill" style="background:<?=
                $r['csv_type']==='duplex'  ? '#dcfce7;color:#15803d' :
               ($r['csv_type']==='rental'  ? '#e0f2fe;color:#0369a1' :
               ($r['csv_type']==='detached'? '#fef9c3;color:#854d0e' : '#f1f5f9;color:#475569')) ?>"><?= htmlspecialchars($r['csv_type']) ?></span></td>
            <td style="font-weight:700;"><?= (int)$r['row_count'] ?></td>
            <td style="font-size:11px;color:#888;"><?= date('M j Y g:ia', strtotime($r['uploaded_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>

<?php endif; ?>
</div><!-- /content -->

<script>
// ── Channel A ─────────────────────────────────────────────────────────────────
(function(){
    var dz=document.getElementById('chA_dz'); if(!dz)return;
    ['dragenter','dragover'].forEach(function(e){dz.addEventListener(e,function(ev){ev.preventDefault();dz.classList.add('dz-active');});});
    dz.addEventListener('dragleave',function(){dz.classList.remove('dz-active');});
    dz.addEventListener('drop',function(e){e.preventDefault();dz.classList.remove('dz-active');document.getElementById('chA_file').files=e.dataTransfer.files;setChAFile(e.dataTransfer.files[0].name);});
    document.getElementById('chA_file').addEventListener('change',function(){if(this.files[0])setChAFile(this.files[0].name);});
    function setChAFile(n){dz.classList.add('dz-selected');document.getElementById('chA_dz_icon').className='fas fa-check-circle';document.getElementById('chA_dz_text').textContent='✅ '+n;}
})();

function submitChannelA(){
    var file=document.getElementById('chA_file').files[0];
    var month=document.getElementById('chA_month').value;
    var type=document.getElementById('chA_type').value;
    if(!file){showR('chA-result','Please select a CSV file.','err');return;}
    if(!month){showR('chA-result','Please select a data month.','err');return;}
    var fd=new FormData();fd.append('rebgv_csv',file);fd.append('data_month',month);fd.append('csv_type',type);
    document.getElementById('chA_progress').style.display='block';
    document.getElementById('chA_submit').disabled=true;
    document.getElementById('chA_preview').style.display='none';
    document.getElementById('chA-result').innerHTML='';
    var msgs=['Parsing CSV…','Geocoding addresses…','Applying versioning…'];
    var mi=0,pmsg=document.getElementById('chA_progress_msg');
    var tk=setInterval(function(){pmsg.textContent=msgs[mi++%msgs.length];},2500);
    fetch('api/plex_upload_a.php',{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
        clearInterval(tk);
        document.getElementById('chA_progress').style.display='none';
        document.getElementById('chA_submit').disabled=false;
        if(!d.success){showR('chA-result',d.error||'Upload failed.','err');return;}
        showR('chA-result','✅ Upload complete — '+d.inserted+' rows inserted for '+d.month+' ('+d.csv_type+')','ok');
        var stats=document.getElementById('chA_stats');
        stats.innerHTML=pill(d.inserted,'Inserted','#d4f5e2','#1a7a45')+pill(d.skipped,'Skipped','#fef9c3','#854d0e')+pill(d.errors?d.errors.length:0,'Errors','#fee2e2','#b91c1c');
        if(d.errors&&d.errors.length)document.getElementById('chA_errors').innerHTML='<strong>Warnings:</strong><br>'+d.errors.slice(0,10).join('<br>');
        if(d.rows&&d.rows.length){
            var h='<table class="preview-table"><thead><tr><th>Address</th><th>COV Neighbourhood</th><th>REBGV Area</th><th>Yr Blt</th><th>Sold $</th><th>$/PSF</th><th>Geocoded</th></tr></thead><tbody>';
            d.rows.forEach(function(r){h+='<tr><td>'+esc(r.address)+'</td><td>'+esc(r.nb_slug)+'</td><td>'+esc(r.rebgv_area||'—')+'</td><td>'+(r.yr_blt||'—')+'</td><td>'+(r.sold_price?'$'+r.sold_price.toLocaleString():'—')+'</td><td>'+(r.price_per_sqft?'$'+r.price_per_sqft:'—')+'</td><td>'+(r.geocoded?'<span style="color:#15803d">✓</span>':'<span style="color:#b91c1c">✗</span>')+'</td></tr>';});
            h+='</tbody></table>';
            document.getElementById('chA_table_wrap').innerHTML=h;
        }
        document.getElementById('chA_preview').style.display='block';
    })
    .catch(function(e){clearInterval(tk);document.getElementById('chA_progress').style.display='none';document.getElementById('chA_submit').disabled=false;showR('chA-result','Network error: '+e.message,'err');});
}

function pill(v,l,bg,c){return '<div class="col-auto"><div style="background:'+bg+';color:'+c+';border-radius:8px;padding:10px 16px;text-align:center;min-width:80px;"><div style="font-size:22px;font-weight:900;">'+v+'</div><div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;">'+l+'</div></div></div>';}

// ── HPI Bulk ──────────────────────────────────────────────────────────────────
(function(){
    var dz=document.getElementById('hpi_dz'); if(!dz)return;
    ['dragenter','dragover'].forEach(function(e){dz.addEventListener(e,function(ev){ev.preventDefault();dz.classList.add('dz-active');});});
    dz.addEventListener('dragleave',function(){dz.classList.remove('dz-active');});
    dz.addEventListener('drop',function(e){e.preventDefault();dz.classList.remove('dz-active');document.getElementById('hpi_file').files=e.dataTransfer.files;setHpiFile(e.dataTransfer.files[0].name);});
    document.getElementById('hpi_file').addEventListener('change',function(){if(this.files[0])setHpiFile(this.files[0].name);});
    function setHpiFile(n){dz.classList.add('dz-selected');document.getElementById('hpi_dz_icon').className='fas fa-check-circle';document.getElementById('hpi_dz_text').textContent='✅ '+n;}
})();

function downloadHpiTemplate(e){
    e.preventDefault();
    var area=document.getElementById('hpi_area').value;
    var month=document.getElementById('hpi_month').value+'-01';
    window.location.href='plex-data.php?hpi_template='+area+'&month='+month;
}

var _hpiRows=[];
function uploadHpiCsv(){
    var file=document.getElementById('hpi_file').files[0];
    var month=document.getElementById('hpi_month').value;
    if(!file){showR('hpi-result','Please select a CSV file.','err');return;}
    if(!month){showR('hpi-result','Please select a data month.','err');return;}
    var fd=new FormData();fd.append('hpi_csv',file);fd.append('hpi_month',month);
    showR('hpi-result','<i class="fas fa-spinner fa-spin me-2"></i>Parsing CSV…','info');
    fetch('plex-data.php?tab=market-prices',{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
        if(!d.success){showR('hpi-result',d.error||'Parse error.','err');return;}
        showR('hpi-result','','info');
        document.getElementById('hpi-result').innerHTML='';
        _hpiRows=d.rows;
        var tbody=document.getElementById('hpi_preview_tbody');
        tbody.innerHTML='';
        d.rows.forEach(function(r,i){
            var matched=!!r.nb_id;
            var tr=document.createElement('tr');
            tr.innerHTML='<td><input type="checkbox" class="hpi-row-cb" data-idx="'+i+'"'+(matched?' checked':'')+' '+(matched?'':'disabled')+'></td>'+
                '<td style="font-weight:600;">'+esc(r.nb_name_rebgv)+'</td>'+
                '<td style="color:'+(matched?'#15803d':'#b91c1c')+';font-weight:600;">'+(matched?esc(r.nb_slug):'⚠ No match')+'</td>'+
                '<td>'+(r.price_duplex?'$'+r.price_duplex.toLocaleString():'—')+'</td>'+
                '<td>'+(r.dom_duplex??'—')+'</td>'+
                '<td>'+(r.sales_duplex??'—')+'</td>'+
                '<td style="color:'+(r.yoy_duplex>=0?'#15803d':'#b91c1c')+'">'+(r.yoy_duplex!=null?(r.yoy_duplex>=0?'+':'')+r.yoy_duplex+'%':'—')+'</td>';
            tbody.appendChild(tr);
        });
        if(d.unmatched&&d.unmatched.length){
            document.getElementById('hpi_unmatched_warn').style.display='block';
            document.getElementById('hpi_unmatched_warn').innerHTML='<div class="result-box result-err">⚠ Unmatched REBGV names (skipped): '+d.unmatched.join(', ')+'</div>';
        }
        document.getElementById('hpiCheckAll').addEventListener('change',function(){
            document.querySelectorAll('.hpi-row-cb:not(:disabled)').forEach(function(cb){cb.checked=document.getElementById('hpiCheckAll').checked;});
        });
        document.getElementById('hpi_preview_wrap').style.display='block';
    })
    .catch(function(e){showR('hpi-result','Network error: '+e.message,'err');});
}

function submitHpiBulk(){
    var result=[];
    document.querySelectorAll('.hpi-row-cb').forEach(function(cb){
        if(!cb.checked)return;
        var r=_hpiRows[parseInt(cb.dataset.idx)];
        if(r&&r.nb_id)result.push(r);
    });
    if(!result.length){alert('No matched rows selected.');return;}
    document.getElementById('hpi_rows_json').value=JSON.stringify(result);
    document.getElementById('hpi_confirm_form').submit();
}

// ── Rental Data — 3 sources ───────────────────────────────────────────────────
// Weights used in mid-point calculation (45% liv.rent, 35% REBGV, 20% CMHC)
// These weights are internal — never shown to users
var _rentWeights = { livrent: 0.45, rebgv: 0.35, cmhc: 0.20 };
var _cmhcCache = {}; // keyed by neighbourhood slug

function loadRentPreview() {
    var slug  = document.getElementById('rent_slug')?.value;
    var month = document.getElementById('rent_month')?.value;
    if (!slug || !month) return;

    // Fetch existing data for this neighbourhood + month from DB
    fetch('api/get_rent_data.php?slug=' + encodeURIComponent(slug) + '&month=' + encodeURIComponent(month))
    .then(function(r){ return r.json(); })
    .then(function(d){
        // Populate liv.rent fields
        document.getElementById('lr_1br').value = d.livrent?.rent_1br || '';
        document.getElementById('lr_2br').value = d.livrent?.rent_2br || '';
        document.getElementById('lr_3br').value = d.livrent?.rent_3br || '';
        document.getElementById('lr_furn').value = d.livrent?.furnished_premium_pct || 20;

        // Populate REBGV rental fields
        document.getElementById('rb_1br').value = d.rebgv?.rent_1br || '';
        document.getElementById('rb_2br').value = d.rebgv?.rent_2br || '';
        document.getElementById('rb_3br').value = d.rebgv?.rent_3br || '';

        // Store CMHC in cache for preview calc
        _cmhcCache = {
            '1br': parseFloat(d.cmhc?.benchmark_1br) || 0,
            '2br': parseFloat(d.cmhc?.benchmark_2br) || 0,
            '3br': parseFloat(d.cmhc?.benchmark_3br) || 0,
        };

        // Show CMHC reference
        var cmhcDiv = document.getElementById('cmhc_ref_display');
        if (d.cmhc && (d.cmhc.benchmark_1br || d.cmhc.benchmark_2br)) {
            cmhcDiv.innerHTML =
                '<i class="fas fa-check-circle me-2" style="color:#16a34a;"></i>' +
                '<strong>CMHC ' + (d.cmhc.year||'') + ' benchmarks stored:</strong> ' +
                (d.cmhc.benchmark_1br ? '1BR $' + parseInt(d.cmhc.benchmark_1br).toLocaleString() + '/mo  ' : '') +
                (d.cmhc.benchmark_2br ? '2BR $' + parseInt(d.cmhc.benchmark_2br).toLocaleString() + '/mo  ' : '') +
                (d.cmhc.benchmark_3br ? '3BR $' + parseInt(d.cmhc.benchmark_3br).toLocaleString() + '/mo' : '') +
                '  <a href="plex-data.php?tab=channel-d" style="color:var(--blue);font-weight:700;font-size:12px;">Update →</a>';
        } else {
            cmhcDiv.innerHTML =
                '<i class="fas fa-exclamation-triangle me-2" style="color:#c9a84c;"></i>' +
                'No CMHC benchmarks stored for this neighbourhood. ' +
                '<a href="plex-data.php?tab=channel-d" style="color:var(--blue);font-weight:700;">Add CMHC data →</a>';
        }

        // Set neighbourhood name in preview header
        var sel = document.getElementById('rent_slug');
        var nbName = sel.options[sel.selectedIndex]?.text || '';
        document.getElementById('prev_nb_name').textContent = nbName;

        updatePreview();
    }).catch(function(){
        updatePreview();
    });
}

function updatePreview() {
    var sources = {
        livrent: {
            '1br': parseFloat(document.getElementById('lr_1br')?.value) || 0,
            '2br': parseFloat(document.getElementById('lr_2br')?.value) || 0,
            '3br': parseFloat(document.getElementById('lr_3br')?.value) || 0,
        },
        rebgv: {
            '1br': parseFloat(document.getElementById('rb_1br')?.value) || 0,
            '2br': parseFloat(document.getElementById('rb_2br')?.value) || 0,
            '3br': parseFloat(document.getElementById('rb_3br')?.value) || 0,
        },
        cmhc: _cmhcCache,
    };

    var types = ['1br','2br','3br'];
    var labels = {'1br':'1 Bedroom','2br':'2 Bedroom','3br':'3 Bedroom'};
    var hasAny = false;
    var gridHtml = '';

    types.forEach(function(type) {
        var vals = {
            livrent: sources.livrent[type],
            rebgv:   sources.rebgv[type],
            cmhc:    sources.cmhc[type] || 0,
        };

        // Filter active sources
        var active = {};
        Object.keys(vals).forEach(function(src){ if(vals[src] > 0) active[src] = vals[src]; });

        if (Object.keys(active).length === 0) {
            gridHtml += '<div style="background:rgba(255,255,255,.05);border-radius:8px;padding:12px;opacity:.4;">' +
                '<div style="font-size:10px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.8px;">' + labels[type] + '</div>' +
                '<div style="font-size:18px;font-weight:800;color:#fff;margin-top:4px;">—</div>' +
                '<div style="font-size:10px;color:rgba(255,255,255,.3);margin-top:2px;">No data entered</div></div>';
            return;
        }
        hasAny = true;

        // Redistribute weights to active sources only
        var activeWeightSum = 0;
        Object.keys(active).forEach(function(src){ activeWeightSum += _rentWeights[src]; });
        var midPoint = 0;
        Object.keys(active).forEach(function(src){
            midPoint += active[src] * (_rentWeights[src] / activeWeightSum);
        });
        midPoint = Math.round(midPoint);

        gridHtml += '<div style="background:rgba(255,255,255,.07);border-radius:8px;padding:12px;">' +
            '<div style="font-size:10px;color:rgba(201,168,76,.8);text-transform:uppercase;letter-spacing:.8px;">' + labels[type] + '</div>' +
            '<div style="font-size:22px;font-weight:800;color:#fff;margin-top:4px;">$' + midPoint.toLocaleString() + '<span style="font-size:12px;font-weight:400;color:rgba(255,255,255,.5);">/mo</span></div>' +
            '<div style="font-size:10px;color:rgba(255,255,255,.35);margin-top:3px;">' + Object.keys(active).length + ' source' + (Object.keys(active).length > 1 ? 's' : '') + ' combined</div>' +
            '</div>';
    });

    var previewEl = document.getElementById('rent_preview');
    var slug = document.getElementById('rent_slug')?.value;

    if (slug && hasAny) {
        document.getElementById('prev_grid').innerHTML = gridHtml;
        previewEl.style.display = 'block';
    } else if (slug && !hasAny) {
        document.getElementById('prev_grid').innerHTML = gridHtml;
        previewEl.style.display = 'block';
    } else {
        previewEl.style.display = 'none';
    }
}

function saveRentSource(source) {
    var slug  = document.getElementById('rent_slug').value;
    var month = document.getElementById('rent_month').value;
    if (!slug) { showR('rent_' + source + '_result', 'Please select a neighbourhood.', 'err'); return; }
    if (!month){ showR('rent_' + source + '_result', 'Please select a data month.', 'err'); return; }

    var data = { neighbourhood_slug: slug, data_month: month, source: source };

    if (source === 'livrent') {
        data.rent_1br              = document.getElementById('lr_1br').value;
        data.rent_2br              = document.getElementById('lr_2br').value;
        data.rent_3br              = document.getElementById('lr_3br').value;
        data.furnished_premium_pct = document.getElementById('lr_furn').value;
        if (!data.rent_1br && !data.rent_2br && !data.rent_3br) {
            showR('rent_livrent_result', 'Enter at least one rent value.', 'err'); return;
        }
    } else {
        data.rent_1br = document.getElementById('rb_1br').value;
        data.rent_2br = document.getElementById('rb_2br').value;
        data.rent_3br = document.getElementById('rb_3br').value;
        if (!data.rent_1br && !data.rent_2br && !data.rent_3br) {
            showR('rent_rebgv_result', 'Enter at least one rent value.', 'err'); return;
        }
    }

    postJSON('api/plex_upload_b.php', data, function(d) {
        var el = source === 'livrent' ? 'rent_livrent_result' : 'rent_rebgv_result';
        if (d.success) {
            showR(el, '✅ ' + d.message, 'ok');
            updatePreview();
        } else {
            showR(el, '❌ ' + (d.error || 'Error'), 'err');
        }
    });
}

// ── Channel D ─────────────────────────────────────────────────────────────────
function submitChannelD(){
    var data={neighbourhood_slug:document.getElementById('chD_slug').value,year:document.getElementById('chD_year').value,benchmark_1br:document.getElementById('chD_1br').value,benchmark_2br:document.getElementById('chD_2br').value,benchmark_3br:document.getElementById('chD_3br').value};
    if(!data.neighbourhood_slug){showR('chD-result','Please select a neighbourhood.','err');return;}
    if(!data.year){showR('chD-result','Please enter a year.','err');return;}
    if(!data.benchmark_1br&&!data.benchmark_2br&&!data.benchmark_3br){showR('chD-result','Enter at least one benchmark value.','err');return;}
    postJSON('api/plex_upload_d.php',data,function(d){if(d.success)showR('chD-result','✅ '+d.message,'ok');else showR('chD-result','❌ '+(d.error||'Error'),'err');});
}

// ── Wynston Outlook ───────────────────────────────────────────────────────────
function saveOutlookInputs(){
    var quarter=document.getElementById('ol_quarter').value;
    var sources=[];
    document.querySelectorAll('.src-forecast').forEach(function(inp){
        if(!inp.value)return;
        var src=inp.dataset.source;
        var date=document.querySelector('.src-date[data-source="'+src+'"]');
        sources.push({source_name:src,forecast_psf_yoy:inp.value,forecast_date:date?date.value:''});
    });
    if(!sources.length){showR('outlook-result','Enter at least one forecast value.','err');return;}
    postJSON('api/plex_outlook.php',{action:'save_inputs',quarter:quarter,sources:sources},function(d){if(d.success)showR('outlook-result','✅ '+d.message,'ok');else showR('outlook-result','❌ '+(d.error||'Error'),'err');});
}

function calculateOutlook(){
    var quarter=document.getElementById('ol_quarter').value;
    showR('outlook-result','<i class="fas fa-spinner fa-spin me-2"></i>Calculating Wynston Outlook for all neighbourhoods…','info');
    postJSON('api/plex_outlook.php',{action:'calculate',quarter:quarter},function(d){
        if(d.success)showR('outlook-result','✅ Outlook calculated — '+d.neighbourhoods_updated+' neighbourhoods updated for '+d.quarter+' (Macro signal: '+(d.macro_signal>=0?'+':'')+d.macro_signal+'%). <a href="plex-data.php?tab=outlook" style="color:var(--blue);font-weight:700;">Refresh to see results ↗</a>','ok');
        else showR('outlook-result','❌ '+(d.error||'Error'),'err');
    });
}

// ── Build Costs ───────────────────────────────────────────────────────────────
function saveCost(){
    var data={action:'save_cost',neighbourhood_slug:document.getElementById('cost_slug').value,cost_standard_low:document.getElementById('cost_std_lo').value,cost_standard_high:document.getElementById('cost_std_hi').value,cost_luxury_low:document.getElementById('cost_lux_lo').value,cost_luxury_high:document.getElementById('cost_lux_hi').value,dcl_city:document.getElementById('cost_dcl_city').value,dcl_utilities:document.getElementById('cost_dcl_util').value,peat_contingency:document.getElementById('cost_peat').value,notes:document.getElementById('cost_notes').value};
    if(!data.neighbourhood_slug){showR('costs-result','Please select a neighbourhood.','err');return;}
    postJSON('api/plex_costs.php',data,function(d){if(d.success)showR('costs-result','✅ Cost override '+d.action+' for '+d.slug+'. <a href="plex-data.php?tab=costs" style="color:var(--blue);font-weight:700;">Refresh ↗</a>','ok');else showR('costs-result','❌ '+(d.error||'Error'),'err');});
}

// ── Population ────────────────────────────────────────────────────────────────
function savePopulation(){
    var data={
        neighbourhood_slug:document.getElementById('pop_slug').value,
        census_year:document.getElementById('pop_year').value,
        total_population:document.getElementById('pop_population').value,
        total_households:document.getElementById('pop_households').value,
        housing_units_total:document.getElementById('pop_units_total').value,
        housing_units_owned:document.getElementById('pop_units_owned').value,
        housing_units_rented:document.getElementById('pop_units_rented').value,
        median_age:document.getElementById('pop_age').value,
        median_household_income:document.getElementById('pop_income').value,
    };
    if(!data.neighbourhood_slug){showR('pop-result','Please select a neighbourhood.','err');return;}
    if(!data.census_year){showR('pop-result','Please select a census year.','err');return;}
    if(!data.total_population&&!data.total_households&&!data.housing_units_total){showR('pop-result','Enter at least population, households, or housing units.','err');return;}
    postJSON('api/save_population.php',data,function(d){
        if(d.success){
            var msg='✅ '+d.message;
            if(d.gap_data){
                var g=d.gap_data;
                msg+=' · Supply-demand gap: <strong style="color:'+(g.signal==='bullish'?'#15803d':(g.signal==='bearish'?'#b91c1c':'#64748b'))+';">'+(g.gap>=0?'+':'')+g.gap+'%</strong> ('+g.signal+')';
            }
            showR('pop-result',msg,'ok');
            setTimeout(function(){location.reload();},1800);
        } else {
            showR('pop-result','❌ '+(d.error||'Error'),'err');
        }
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function showR(el,msg,type){
    var e=document.getElementById(el); if(!e)return;
    if(!msg){e.innerHTML='';return;}
    var cls=type==='ok'?'result-ok':(type==='err'?'result-err':'result-info');
    e.innerHTML='<div class="result-box '+cls+'">'+msg+'</div>';
}

function postJSON(url,data,cb){
    fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)})
    .then(function(r){return r.json();}).then(cb)
    .catch(function(e){cb({success:false,error:e.message});});
}

function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>