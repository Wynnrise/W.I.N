<?php
/**
 * api/transit_stops.php
 * Returns SkyTrain/SeaBus/RapidTransit station coordinates as GeoJSON
 * Identifies stations by: location_type=1, zone_id ZN 1/2/3, or "Station" in name
 */
header('Content-Type: application/json');
header('Cache-Control: no-store');

$pdo = new PDO("mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4",
    "u990588858_Multiplex", "Concac1979$",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

try {
    // Include skytrain/seabus AND zone-based rapid transit stops + named stations
    $rows = $pdo->query("
        SELECT DISTINCT stop_id, stop_name, stop_lat, stop_lng, stop_type, zone_id
        FROM   transit_stops
        WHERE  is_ftn = 1
          AND  (
            stop_type IN ('skytrain','seabus','rapidbus')
            OR zone_id IN ('ZN 1','ZN 2','ZN 3')
            OR stop_name LIKE '%Station%'
          )
        ORDER BY stop_name
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo json_encode(['type'=>'FeatureCollection','features'=>[],'error'=>$e->getMessage()]);
    exit;
}

// Deduplicate by station name — one dot per unique station
// Strips bay/platform suffixes to get the base station name
$seen = [];
$features = [];
foreach ($rows as $r) {
    // Normalise name: remove "@ Bay X", "Eastbound", "Westbound" etc
    $baseName = preg_replace('/\s*@\s*Bay\s*\d+/i', '', $r['stop_name']);
    $baseName = preg_replace('/\s*(Eastbound|Westbound|Northbound|Southbound|Platform|Concourse|Entrance|Accessible)\s*/i', ' ', $baseName);
    $baseName = trim(preg_replace('/\s+/', ' ', $baseName));

    if (isset($seen[$baseName])) continue;
    $seen[$baseName] = true;

    $features[] = [
        'type'     => 'Feature',
        'geometry' => ['type' => 'Point', 'coordinates' => [(float)$r['stop_lng'], (float)$r['stop_lat']]],
        'properties' => ['name' => $baseName, 'type' => $r['stop_type'], 'zone' => $r['zone_id']],
    ];
}

echo json_encode(['type' => 'FeatureCollection', 'features' => $features]);