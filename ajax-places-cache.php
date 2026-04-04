<?php
/**
 * ajax-places-cache.php
 * Receives Overpass results from the browser and saves them to places_cache table.
 * Called by neighbourhood.php JS when cache is stale or empty.
 */
$base_dir = __DIR__ . '/Base';
require_once "$base_dir/db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo '{"ok":false}'; exit;
}

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (empty($data['neighbourhood_id']) || empty($data['category']) || !isset($data['places'])) {
    http_response_code(400); echo '{"ok":false,"error":"missing fields"}'; exit;
}

$nb_id    = (int)$data['neighbourhood_id'];
$category = preg_replace('/[^a-z_]/', '', $data['category']); // sanitize
$places   = $data['places'];

if (!$nb_id || !$category || !is_array($places)) {
    http_response_code(400); echo '{"ok":false}'; exit;
}

try {
    // Ensure table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS places_cache (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        neighbourhood_id INT NOT NULL,
        category         VARCHAR(32) NOT NULL,
        name             VARCHAR(255) NOT NULL,
        address          VARCHAR(255) DEFAULT '',
        lat              DECIMAL(10,7) NOT NULL,
        lng              DECIMAL(10,7) NOT NULL,
        osm_url          VARCHAR(255) DEFAULT '',
        fetched_at       DATETIME NOT NULL,
        UNIQUE KEY uq_place (neighbourhood_id, category, name(100))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Delete old entries for this neighbourhood+category before re-inserting
    $del = $pdo->prepare("DELETE FROM places_cache WHERE neighbourhood_id = ? AND category = ?");
    $del->execute([$nb_id, $category]);

    // Insert new results
    $ins = $pdo->prepare("INSERT IGNORE INTO places_cache
        (neighbourhood_id, category, name, address, lat, lng, osm_url, fetched_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

    $count = 0;
    foreach ($places as $p) {
        if (empty($p['name']) || empty($p['lat']) || empty($p['lng'])) continue;
        $ins->execute([
            $nb_id,
            $category,
            mb_substr(trim($p['name']), 0, 255),
            mb_substr(trim($p['address'] ?? ''), 0, 255),
            round((float)$p['lat'], 7),
            round((float)$p['lng'], 7),
            mb_substr(trim($p['osm_url'] ?? ''), 0, 255),
        ]);
        $count++;
    }

    echo json_encode(['ok' => true, 'saved' => $count, 'category' => $category]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}