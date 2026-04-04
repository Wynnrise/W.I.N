<?php
/**
 * api/permits.php
 * Returns A_Permit_2026 (COV building permits) as GeoJSON for the map.
 * No auth required — permit data is public record.
 */

header('Content-Type: application/json');
header('Cache-Control: no-store');

$pdo = new PDO("mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4",
    "u990588858_Multiplex", "Concac1979$",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

try {
    $rows = $pdo->query("
        SELECT id, permit_number, address, neighbourhood,
               permit_type, description, applicant,
               property_use, latitude, longitude,
               issue_date, `year_month`
        FROM   A_Permit_2026
        WHERE  show_on_plex_map = 1
          AND  latitude  IS NOT NULL
          AND  longitude IS NOT NULL
        ORDER BY issue_date DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['type'=>'FeatureCollection','features'=>[],'error'=>$e->getMessage()]);
    exit;
}

$features = [];
foreach ($rows as $row) {
    $features[] = [
        'type'     => 'Feature',
        'geometry' => [
            'type'        => 'Point',
            'coordinates' => [(float)$row['longitude'], (float)$row['latitude']],
        ],
        'properties' => [
            'id'            => $row['id'],
            'permit_number' => $row['permit_number'],
            'address'       => $row['address'],
            'neighbourhood' => $row['neighbourhood'],
            'permit_type'   => $row['permit_type'],
            'description'   => $row['description'],
            'applicant'     => $row['applicant'],
            'property_use'  => $row['property_use'],
            'issue_date'    => $row['issue_date'],
            'year_month'    => $row['year_month'],
            'marker_type'   => 'permit',
        ],
    ];
}

echo json_encode(['type' => 'FeatureCollection', 'features' => $features]);