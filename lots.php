<?php
/**
 * GET /wynston/api/lot_detail.php?pid=XXX
 * Returns full lot data + basic eligibility for the side panel
 * More logic (costs, exit value, outlook) added in Sessions 04-08
 */

require_once __DIR__ . '/../../config.php';
session_start();

header('Content-Type: application/json');

$pid = trim($_GET['pid'] ?? '');
if (empty($pid)) {
    http_response_code(400);
    echo json_encode(['error' => 'PID required']);
    exit;
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Fetch lot
    $stmt = $pdo->prepare("SELECT * FROM plex_properties WHERE pid = :pid LIMIT 1");
    $stmt->execute([':pid' => $pid]);
    $lot = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lot) {
        http_response_code(404);
        echo json_encode(['error' => 'Lot not found']);
        exit;
    }

    // Fetch constraint flags
    $stmt2 = $pdo->prepare("SELECT * FROM constraint_flags WHERE pid = :pid LIMIT 1");
    $stmt2->execute([':pid' => $pid]);
    $flags = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

    // Basic eligibility — full engine built in Session 04
    $eligibility = calcEligibility($lot);

    // Gate check — is developer logged in?
    $logged_in = isset($_SESSION['developer_id']) && $_SESSION['developer_id'] > 0;

    echo json_encode([
        'lot'        => $lot,
        'flags'      => $flags,
        'eligibility'=> $eligibility,
        'logged_in'  => $logged_in,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}

/**
 * Basic eligibility — skeleton only, full logic in Session 04
 * Returns max unit count based on lot dimensions + transit
 */
function calcEligibility(array $lot): array {
    $width   = (float)($lot['lot_width_m']  ?? 0);
    $area    = (float)($lot['lot_area_sqm'] ?? 0);
    $transit = (bool)($lot['transit_proximate'] ?? false);

    $max_units = 0;
    $eligible_for = [];

    if ($width >= 7.5 && $area >= 200) {
        $max_units = 3;
        $eligible_for[] = '3 units';
    }
    if ($width >= 10.0 && $area >= 306) {
        $max_units = 4;
        $eligible_for[] = '4 units';
    }
    if ($width >= 15.1 && $area >= 557 && $transit) {
        $max_units = 6;
        $eligible_for[] = '6 units (strata)';
        $eligible_for[] = '8 units (secured rental)';
    }

    // 14.9m warning flag
    $near_sixunit = ($width >= 14.5 && $width < 15.1);

    return [
        'max_units'    => $max_units,
        'eligible_for' => $eligible_for,
        'near_sixunit' => $near_sixunit,
        'gap_to_six_m' => $near_sixunit ? round(15.1 - $width, 2) : null,
    ];
}
