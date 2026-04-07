<?php
// api/get_rent_data.php
// GET: return stored rental data for a neighbourhood + month (all 3 sources)
// Used by plex-data.php rental tab to pre-populate fields and show CMHC reference

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

$slug  = trim($_GET['slug']  ?? '');
$month = trim($_GET['month'] ?? '');

if (!$slug || !$month) {
    echo json_encode(['success' => false, 'error' => 'slug and month required']);
    exit;
}

$month_dt = $month . '-01';

// Pull liv.rent data
$stmt = $pdo->prepare("
    SELECT avg_rent_1br as rent_1br, avg_rent_2br as rent_2br,
           avg_rent_3br as rent_3br, furnished_premium_pct
    FROM monthly_market_stats
    WHERE neighbourhood_slug = ? AND data_month = ?
      AND csv_type = 'rental_livrent' AND is_active = 1
    LIMIT 1
");
$stmt->execute([$slug, $month_dt]);
$livrent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

// Pull REBGV rental data
$stmt = $pdo->prepare("
    SELECT avg_rent_1br as rent_1br, avg_rent_2br as rent_2br,
           avg_rent_3br as rent_3br
    FROM monthly_market_stats
    WHERE neighbourhood_slug = ? AND data_month = ?
      AND csv_type = 'rental_rebgv' AND is_active = 1
    LIMIT 1
");
$stmt->execute([$slug, $month_dt]);
$rebgv = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

// Also check old 'rental' csv_type for backward compatibility
if (!$livrent) {
    $stmt = $pdo->prepare("
        SELECT avg_rent_1br as rent_1br, avg_rent_2br as rent_2br,
               avg_rent_3br as rent_3br, furnished_premium_pct
        FROM monthly_market_stats
        WHERE neighbourhood_slug = ? AND data_month = ?
          AND csv_type = 'rental' AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$slug, $month_dt]);
    $livrent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Pull most recent CMHC benchmark for this neighbourhood
$stmt = $pdo->prepare("
    SELECT benchmark_1br, benchmark_2br, benchmark_3br, year
    FROM cmhc_benchmarks
    WHERE neighbourhood_slug = ?
    ORDER BY year DESC
    LIMIT 1
");
$stmt->execute([$slug]);
$cmhc = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

echo json_encode([
    'success' => true,
    'slug'    => $slug,
    'month'   => $month,
    'livrent' => $livrent,
    'rebgv'   => $rebgv,
    'cmhc'    => $cmhc,
]);
