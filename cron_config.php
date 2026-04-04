<?php
/**
 * GET /wynston/api/lots.php
 * Returns all plex_properties as GeoJSON-ready JSON for Mapbox
 * Lightweight — only returns fields the map layer needs
 * Full detail (FSR, costs, etc.) is in lot_detail.php
 */

require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');
header('Cache-Control: public, max-age=3600'); // cache 1hr — data updates nightly

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = "
        SELECT
            pid,
            address,
            lat,
            lng,
            lot_width_m,
            lot_area_sqm,
            transit_proximate,
            nearest_ftn_stop_m,
            profitability_score,
            heritage_category,
            peat_zone
        FROM plex_properties
        WHERE lat IS NOT NULL
          AND lng IS NOT NULL
        ORDER BY pid
    ";

    $lots = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'lots'  => $lots,
        'count' => count($lots),
        'as_of' => date('Y-m-d H:i:s'),
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'lots' => []]);
}
