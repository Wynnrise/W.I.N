<?php
header('Content-Type: application/json');
session_start();

$pid = trim($_GET['pid'] ?? '');
if (empty($pid)) {
    http_response_code(400);
    echo json_encode(['error' => 'PID required']);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4',
        'u990588858_Multiplex', 'Concac1979$',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->prepare("SELECT * FROM plex_properties WHERE pid = :pid LIMIT 1");
    $stmt->execute([':pid' => $pid]);
    $lot = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lot) {
        http_response_code(404);
        echo json_encode(['error' => 'Lot not found']);
        exit;
    }

    $stmt2 = $pdo->prepare("SELECT * FROM constraint_flags WHERE pid = :pid LIMIT 1");
    $stmt2->execute([':pid' => $pid]);
    $flags = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

    $width   = (float)($lot['lot_width_m']  ?? 0);
    $area    = (float)($lot['lot_area_sqm'] ?? 0);
    $transit = (bool)($lot['transit_proximate'] ?? false);
    $lane    = (bool)($lot['lane_access'] ?? false);

    $max_units = 0; $eligible_for = [];
    if ($width >= 7.5  && $area >= 200)                    { $max_units = 3; $eligible_for[] = '3 units'; }
    if ($width >= 10.0 && $area >= 306)                    { $max_units = 4; $eligible_for[] = '4 units'; }
    if ($width >= 15.1 && $area >= 557 && $transit)        { $max_units = 6; $eligible_for[] = '6 units (strata)'; $eligible_for[] = '8 units (rental)'; }

    $warning_149 = ($width >= 14.5 && $width < 15.1);
    $logged_in   = isset($_SESSION['developer_id']) && $_SESSION['developer_id'] > 0;

    echo json_encode([
        'lot'         => $lot,
        'flags'       => $flags,
        'eligibility' => [
            'max_units'    => $max_units,
            'eligible_for' => $eligible_for,
            'near_sixunit' => $warning_149,
            'gap_to_six_m' => $warning_149 ? round(15.1 - $width, 2) : null,
            'parking_req'  => $transit ? 0 : round($max_units * 0.5),
        ],
        'logged_in'   => $logged_in,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
