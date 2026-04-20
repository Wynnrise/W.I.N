<?php
session_start();

// Read JSON body early — postJSON sends JSON not $_POST
$_raw_body = file_get_contents('php://input');
$_json_body = ($_raw_body && trim($_raw_body)) ? (json_decode($_raw_body, true) ?: []) : [];
header('Content-Type: application/json');
header('Cache-Control: no-store');
if (empty($_SESSION['admin_logged_in'])) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Not authorised']); exit; }

$pdo = new PDO("mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4",
    'u990588858_Multiplex','Concac1979$',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

$body = $_json_body ?: $_POST;

$slug      = trim($body['neighbourhood_slug'] ?? '');
$month_raw = trim($body['data_month'] ?? '');
$r1        = isset($body['avg_rent_1br'])  && $body['avg_rent_1br']  !== '' ? (int)$body['avg_rent_1br']  : null;
$r2        = isset($body['avg_rent_2br'])  && $body['avg_rent_2br']  !== '' ? (int)$body['avg_rent_2br']  : null;
$r3        = isset($body['avg_rent_3br'])  && $body['avg_rent_3br']  !== '' ? (int)$body['avg_rent_3br']  : null;
$prem      = isset($body['furnished_premium_pct']) && $body['furnished_premium_pct'] !== '' ? (float)$body['furnished_premium_pct'] : 20.0;
$csv_type  = trim($body['csv_type'] ?? 'rental');
$source    = trim($body['source'] ?? 'liv.rent');

if (!$slug || !$month_raw) {
    echo json_encode(['success'=>false,'error'=>'Missing neighbourhood or month']); exit;
}

// Normalise month to YYYY-MM-01
$month_dt = date('Y-m-01', strtotime($month_raw));
if (!$month_dt || $month_dt === '1970-01-01') {
    echo json_encode(['success'=>false,'error'=>'Invalid month: '.$month_raw]); exit;
}

try {
    // Deactivate previous rows for this nb+month+type
    $pdo->prepare("UPDATE monthly_market_stats SET is_active=0
                   WHERE neighbourhood_slug=? AND data_month=? AND csv_type=?")
        ->execute([$slug, $month_dt, $csv_type]);

    // Insert or update
    $pdo->prepare("INSERT INTO monthly_market_stats
        (neighbourhood_slug, data_month, csv_type,
         avg_rent_1br, avg_rent_2br, avg_rent_3br,
         furnished_premium_pct, source, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ON DUPLICATE KEY UPDATE
            avg_rent_1br          = VALUES(avg_rent_1br),
            avg_rent_2br          = VALUES(avg_rent_2br),
            avg_rent_3br          = VALUES(avg_rent_3br),
            furnished_premium_pct = VALUES(furnished_premium_pct),
            source                = VALUES(source),
            is_active             = 1")
        ->execute([$slug, $month_dt, $csv_type, $r1, $r2, $r3, $prem, $source]);

    echo json_encode([
        'success'  => true,
        'message'  => "✅ {$source} data saved for {$slug} — " . date('F Y', strtotime($month_dt)),
        'slug'     => $slug,
        'month'    => $month_dt,
        'csv_type' => $csv_type,
    ]);
} catch(Exception $e) {
    echo json_encode(['success'=>false,'error'=> $e->getMessage()]);
}
