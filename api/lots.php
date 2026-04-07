<?php
header("Content-Type: application/json");
header("Cache-Control: public, max-age=3600");

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4",
        "u990588858_Multiplex",
        "Concac1979$",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = "
        SELECT pid, address, lat, lng,
               lot_width_m, lot_area_sqm,
               lane_access, transit_proximate,
               nearest_ftn_stop_m, profitability_score,
               heritage_category, peat_zone, has_active_permit,
               floodplain_risk
        FROM   plex_properties
        WHERE  lat IS NOT NULL
          AND  lng IS NOT NULL
        ORDER BY pid
    ";

    $lots = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Build GeoJSON FeatureCollection — required format for Mapbox addSource
    $features = [];
    foreach ($lots as $lot) {
        $features[] = [
            'type'     => 'Feature',
            'geometry' => [
                'type'        => 'Point',
                'coordinates' => [(float)$lot['lng'], (float)$lot['lat']],
            ],
            'properties' => [
                'pid'               => $lot['pid'],
                'address'           => $lot['address'],
                'lot_width_m'       => (float)$lot['lot_width_m'],
                'lot_area_sqm'      => (float)$lot['lot_area_sqm'],
                'lane_access'       => (int)$lot['lane_access'],
                'transit_proximate' => (int)$lot['transit_proximate'],
                'nearest_ftn_stop_m'=> (int)$lot['nearest_ftn_stop_m'],
                'profitability_score'=> (float)$lot['profitability_score'],
                'heritage_category' => $lot['heritage_category'],
                'peat_zone'         => (int)$lot['peat_zone'],
                'has_active_permit'  => (int)($lot['has_active_permit'] ?? 0),
                'floodplain_risk'   => $lot['floodplain_risk'] ?? 'none',
            ],
        ];
    }

    echo json_encode([
        'type'     => 'FeatureCollection',
        'features' => $features,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'type'     => 'FeatureCollection',
        'features' => [],
        'error'    => $e->getMessage(),
    ]);
}