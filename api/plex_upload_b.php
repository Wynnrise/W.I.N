<?php
// ============================================================
// api/plex_upload_b.php  —  Channel B: liv.rent Rental Entry
// Session 03 — Admin Upload Channels
//
// Accepts: POST JSON or form fields:
//   neighbourhood_slug, data_month (YYYY-MM),
//   rent_1br, rent_2br, rent_3br,
//   furnished_premium_pct (optional, default 20),
//   source_note (optional)
//
// Returns: JSON {success, id, message}
// ============================================================

session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

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

// Accept either JSON body or form POST
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$nb_slug     = trim($input['neighbourhood_slug'] ?? '');
$data_month  = trim($input['data_month'] ?? '');
$rent_1br    = isset($input['rent_1br'])    && $input['rent_1br']    !== '' ? (int)$input['rent_1br']    : null;
$rent_2br    = isset($input['rent_2br'])    && $input['rent_2br']    !== '' ? (int)$input['rent_2br']    : null;
$rent_3br    = isset($input['rent_3br'])    && $input['rent_3br']    !== '' ? (int)$input['rent_3br']    : null;
$furnished   = isset($input['furnished_premium_pct']) && $input['furnished_premium_pct'] !== ''
               ? (float)$input['furnished_premium_pct'] : 20.0;
$source_note = trim($input['source_note'] ?? 'liv.rent');

// ── Validate ──────────────────────────────────────────────────────────────────
if (!$nb_slug) {
    echo json_encode(['success'=>false,'error'=>'neighbourhood_slug is required.']); exit;
}
if (!preg_match('/^\d{4}-\d{2}$/', $data_month)) {
    echo json_encode(['success'=>false,'error'=>'data_month must be YYYY-MM.']); exit;
}
if (!$rent_1br && !$rent_2br && !$rent_3br) {
    echo json_encode(['success'=>false,'error'=>'At least one rent value required.']); exit;
}

$data_month_dt = $data_month . '-01';

// ── Versioning: deactivate previous rental rows for this nb + month ───────────
$pdo->prepare("
    UPDATE monthly_market_stats
    SET is_active = 0
    WHERE neighbourhood_slug = :slug
      AND data_month         = :month
      AND csv_type           = 'rental'
")->execute([':slug'=>$nb_slug, ':month'=>$data_month_dt]);

// ── Insert new rental row ──────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    INSERT INTO monthly_market_stats
        (neighbourhood_slug, data_month, csv_type,
         avg_rent_1br, avg_rent_2br, avg_rent_3br,
         furnished_premium_pct, is_active, source, created_at)
    VALUES
        (:slug, :month, 'rental',
         :r1, :r2, :r3,
         :furn, 1, :src, NOW())
");
$stmt->execute([
    ':slug'  => $nb_slug,
    ':month' => $data_month_dt,
    ':r1'    => $rent_1br,
    ':r2'    => $rent_2br,
    ':r3'    => $rent_3br,
    ':furn'  => $furnished,
    ':src'   => $source_note,
]);

$new_id = $pdo->lastInsertId();

echo json_encode([
    'success'  => true,
    'id'       => (int)$new_id,
    'message'  => "Rental data saved for {$nb_slug} — {$data_month}. Previous rows deactivated.",
    'data'     => [
        'neighbourhood_slug'   => $nb_slug,
        'data_month'           => $data_month,
        'avg_rent_1br'         => $rent_1br,
        'avg_rent_2br'         => $rent_2br,
        'avg_rent_3br'         => $rent_3br,
        'furnished_premium_pct'=> $furnished,
        'source'               => $source_note,
    ],
]);
