<?php
// api/saved_lots.php
// GET: return all saved lots for the logged-in developer

session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'cookie_domain'   => 'wynston.ca',
    'cookie_samesite' => 'Lax',
]);

header('Content-Type: application/json');
header('Cache-Control: no-store');

require __DIR__ . '/../dev-auth.php';

if (!isset($_SESSION['dev_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$host = 'localhost';
$db   = 'u990588858_Property';
$user = 'u990588858_Multiplex';
$pass = 'Concac1979$';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

$dev_id = (int) $_SESSION['dev_id'];

$stmt = $pdo->prepare("
    SELECT
        sl.id,
        sl.pid,
        sl.address,
        sl.saved_at,
        sl.notes,
        p.lot_width_m,
        p.lot_depth_m,
        p.lot_area_sqm,
        p.lane_access,
        p.transit_proximate,
        p.heritage_category,
        p.peat_zone,
        p.neighbourhood_slug,
        p.lat,
        p.lng
    FROM saved_lots sl
    LEFT JOIN plex_properties p ON p.pid = sl.pid
    WHERE sl.developer_id = ?
    ORDER BY sl.saved_at DESC
");
$stmt->execute([$dev_id]);
$lots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add eligibility tier and display values to each lot
foreach ($lots as &$lot) {
    $w = (float)($lot['lot_width_m'] ?? 0);
    $lane = (int)($lot['lane_access'] ?? 0);
    $transit = (int)($lot['transit_proximate'] ?? 0);

    if ($w >= 15.1 && $transit && $lane) {
        $lot['tier'] = '6-unit';
        $lot['tier_colour'] = '#22c55e';
    } elseif ($w >= 10.0 && $lane) {
        $lot['tier'] = '4-unit';
        $lot['tier_colour'] = '#14b8a6';
    } elseif ($w >= 7.5 && $lane) {
        $lot['tier'] = 'duplex';
        $lot['tier_colour'] = '#f59e0b';
    } else {
        $lot['tier'] = 'below-min';
        $lot['tier_colour'] = '#94a3b8';
    }

    // Convert to feet for display
    $lot['lot_width_ft']  = $w > 0 ? round($w / 0.3048, 1) : null;
    $lot['lot_area_sqft'] = $lot['lot_area_sqm'] > 0 ? round((float)$lot['lot_area_sqm'] * 10.7639) : null;
}

echo json_encode(['success' => true, 'lots' => $lots, 'count' => count($lots)]);
