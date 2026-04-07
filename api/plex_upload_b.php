<?php
// api/plex_upload_b.php
// POST: save rental data from one source (livrent or rebgv)
// Body: { neighbourhood_slug, data_month, source, rent_1br, rent_2br,
//         rent_3br, furnished_premium_pct }

session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'cookie_domain'   => 'wynston.ca',
    'cookie_samesite' => 'Lax',
]);

header('Content-Type: application/json');
header('Cache-Control: no-store');

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$host = 'localhost'; $db = 'u990588858_Property';
$user = 'u990588858_Multiplex'; $pass = 'Concac1979$';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true);
$slug   = trim($input['neighbourhood_slug'] ?? '');
$month  = trim($input['data_month']         ?? '');
$source = trim($input['source']             ?? 'livrent'); // 'livrent' or 'rebgv'

$rent_1br  = $input['rent_1br']              !== '' ? (int)$input['rent_1br']              : null;
$rent_2br  = $input['rent_2br']              !== '' ? (int)$input['rent_2br']              : null;
$rent_3br  = $input['rent_3br']              !== '' ? (int)$input['rent_3br']              : null;
$furn_pct  = $input['furnished_premium_pct'] !== '' ? (float)$input['furnished_premium_pct'] : 20.00;

if (!$slug)  { echo json_encode(['success' => false, 'error' => 'Neighbourhood required']); exit; }
if (!$month) { echo json_encode(['success' => false, 'error' => 'Month required']); exit; }
if (!$rent_1br && !$rent_2br && !$rent_3br) {
    echo json_encode(['success' => false, 'error' => 'Enter at least one rent value']); exit;
}

// Map source to csv_type
$csv_type = ($source === 'rebgv') ? 'rental_rebgv' : 'rental_livrent';

$month_dt = strlen($month) === 7 ? $month . '-01' : $month;
$label    = date('F Y', strtotime($month_dt));

// Versioning: deactivate previous rows for same neighbourhood + month + source
$pdo->prepare("
    UPDATE monthly_market_stats
    SET is_active = 0
    WHERE neighbourhood_slug = ? AND data_month = ? AND csv_type = ?
")->execute([$slug, $month_dt, $csv_type]);

// Insert new row
$pdo->prepare("
    INSERT INTO monthly_market_stats
        (neighbourhood_slug, data_month, csv_type,
         avg_rent_1br, avg_rent_2br, avg_rent_3br,
         furnished_premium_pct, source, is_active, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
")->execute([
    $slug, $month_dt, $csv_type,
    $rent_1br, $rent_2br, $rent_3br,
    $furn_pct,
    $source === 'rebgv' ? 'REBGV Rental' : 'liv.rent',
]);

$source_label = $source === 'rebgv' ? 'REBGV rental' : 'liv.rent';

echo json_encode([
    'success'   => true,
    'message'   => ucfirst($source_label) . " data saved for {$slug} — {$label}",
    'slug'      => $slug,
    'month'     => $label,
    'csv_type'  => $csv_type,
]);