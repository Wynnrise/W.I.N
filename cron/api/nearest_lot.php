<?php
/**
 * api/nearest_lot.php
 * Returns the nearest lot in plex_properties to a given lat/lng.
 * Used by permit pin clicks to find the matching PID for feasibility.
 *
 * GET params:
 *   lat  — latitude (decimal)
 *   lng  — longitude (decimal)
 *
 * Returns: { pid, address, distance_m } or { error }
 * Only returns a match if within 80m (prevents false matches across streets)
 */

header('Content-Type: application/json');

$lat = (float)($_GET['lat'] ?? 0);
$lng = (float)($_GET['lng'] ?? 0);

if (!$lat || !$lng) {
    echo json_encode(['error' => 'lat and lng required']);
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4",
    "u990588858_Multiplex", "Concac1979$",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Haversine in SQL — find nearest lot within bounding box first,
// then calculate exact distance
$bbox = 0.001; // ~80m in decimal degrees

try {
    $stmt = $pdo->prepare("
        SELECT pid, address, lat, lng,
               (6371000 * 2 * ASIN(SQRT(
                   POWER(SIN(RADIANS(lat - ?) / 2), 2) +
                   COS(RADIANS(?)) * COS(RADIANS(lat)) *
                   POWER(SIN(RADIANS(lng - ?) / 2), 2)
               ))) AS distance_m
        FROM   plex_properties
        WHERE  lat BETWEEN ? AND ?
          AND  lng BETWEEN ? AND ?
          AND  lat IS NOT NULL
        ORDER BY distance_m ASC
        LIMIT  1
    ");
    $stmt->execute([
        $lat, $lat, $lng,
        $lat - $bbox, $lat + $bbox,
        $lng - $bbox, $lng + $bbox,
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

if (!$row || $row['distance_m'] > 80) {
    echo json_encode(['error' => 'No lot found within 80m']);
    exit;
}

echo json_encode([
    'pid'        => $row['pid'],
    'address'    => $row['address'],
    'distance_m' => round($row['distance_m'], 1),
]);
