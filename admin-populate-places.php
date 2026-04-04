<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(600);

while (ob_get_level()) ob_end_clean();

$base_dir = __DIR__ . '/Base';
require_once $base_dir . '/db.php';

// Keep MySQL connection alive
$pdo->setAttribute(PDO::ATTR_TIMEOUT, 300);
try { $pdo->exec("SET SESSION wait_timeout=600, interactive_timeout=600"); } catch(Exception $e) {}

$pdo->exec("CREATE TABLE IF NOT EXISTS places_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    neighbourhood_id INT NOT NULL,
    category VARCHAR(32) NOT NULL,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255) DEFAULT '',
    lat DECIMAL(10,7) NOT NULL,
    lng DECIMAL(10,7) NOT NULL,
    osm_url VARCHAR(255) DEFAULT '',
    fetched_at DATETIME NOT NULL,
    UNIQUE KEY uq_place (neighbourhood_id, category, name(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$cat_tags = array(
    'grocery'    => array(array('shop','supermarket'),array('shop','convenience'),array('shop','grocery')),
    'park'       => array(array('leisure','park'),array('leisure','playground'),array('leisure','garden')),
    'transit'    => array(array('railway','station'),array('highway','bus_stop'),array('amenity','bus_station')),
    'recreation' => array(array('leisure','fitness_centre'),array('leisure','sports_centre'),array('leisure','swimming_pool')),
    'school'     => array(array('amenity','school'),array('amenity','college'),array('amenity','kindergarten')),
    'hospital'   => array(array('amenity','hospital'),array('amenity','clinic'),array('amenity','pharmacy')),
    'restaurant' => array(array('amenity','restaurant'),array('amenity','cafe'),array('amenity','fast_food')),
);

$slug  = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$all   = isset($_GET['all']);
$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;

if (!$slug && !$all) {
    echo '<h2>Populate Places Cache</h2>';
    echo '<p><a href="?slug=oakridge">?slug=oakridge</a> &nbsp; <a href="?all=1">?all=1</a></p>';
    exit;
}

if ($slug) {
    $q = $pdo->prepare("SELECT * FROM neighbourhoods WHERE slug = ? AND is_active = 1 LIMIT 1");
    $q->execute(array($slug));
    $nbs = $q->fetchAll(PDO::FETCH_ASSOC);
} else {
    $q = $pdo->query("SELECT * FROM neighbourhoods WHERE is_active = 1 AND lat_min IS NOT NULL AND lat_max IS NOT NULL ORDER BY area, name");
    $nbs = $q->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($nbs)) { echo 'No neighbourhoods found.'; exit; }

// For ?all=1, process in batches of 5 to avoid timeouts
$batch_size = 5;
$batch = array_slice($nbs, $start, $batch_size);
$total_nbs = count($nbs);
$end = min($start + $batch_size, $total_nbs);

echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
echo '<style>body{font-family:sans-serif;padding:20px;max-width:700px;line-height:1.6;}';
echo '.ok{color:#16a34a;font-weight:bold;} .zero{color:#aaa;} .err{color:red;font-weight:bold;}';
echo '.nb{border:1px solid #ddd;border-radius:6px;padding:12px;margin:10px 0;}';
echo 'h3{margin:0 0 4px;} .next-btn{background:#0065ff;color:#fff;padding:10px 20px;border:none;border-radius:6px;font-size:15px;cursor:pointer;text-decoration:none;display:inline-block;margin-top:12px;}';
echo '</style></head><body>';

if ($all) {
    echo '<h2>Populating Places Cache (' . $end . ' of ' . $total_nbs . ')</h2>';
    echo '<p>Processing neighbourhoods ' . ($start+1) . ' to ' . $end . ' of ' . $total_nbs . '.</p><hr>';
} else {
    echo '<h2>Populating: ' . htmlspecialchars($nbs[0]['name']) . '</h2><hr>';
}
flush();

// Helper: ping DB and reconnect if needed
function db_ping($pdo) {
    try { $pdo->query("SELECT 1"); return $pdo; } catch(Exception $e) { return null; }
}

// Helper: fetch from Overpass with retry
function overpass_query($query) {
    $url = 'https://overpass-api.de/api/interpreter';
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        if ($attempt > 1) {
            echo '&nbsp;&nbsp;Retry ' . $attempt . '...<br>';
            flush();
            sleep(10 * $attempt);
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'data=' . urlencode($query));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Wynston/1.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200 && $raw) {
            $data = json_decode($raw, true);
            return isset($data['elements']) ? $data['elements'] : array();
        }
        // 429 or 504 = rate limited, wait longer
        if ($code === 429 || $code === 504) sleep(15);
    }
    return null; // failed after retries
}

$grand_total = 0;

foreach ($batch as $nb) {
    if (empty($nb['lat_min']) || empty($nb['lat_max'])) {
        echo '<div class="nb"><h3>' . htmlspecialchars($nb['name']) . '</h3>Skipped - no coordinates.</div>';
        flush();
        continue;
    }

    $lat = round(($nb['lat_min'] + $nb['lat_max']) / 2, 5);
    $lng = round(($nb['lng_min'] + $nb['lng_max']) / 2, 5);
    $h   = ($nb['lat_max'] - $nb['lat_min']) * 111000;
    $w   = ($nb['lng_max'] - $nb['lng_min']) * 111000 * cos(deg2rad($lat));
    $rad = (int) min(3000, max(800, sqrt(($w/2)*($w/2) + ($h/2)*($h/2)) * 1.1));

    echo '<div class="nb"><h3>' . htmlspecialchars($nb['name']) . ' (' . htmlspecialchars($nb['area']) . ')</h3>';
    echo 'Radius: ' . $rad . 'm &mdash; querying Overpass...<br>';
    flush();

    // Build combined query
    $around = 'around:' . $rad . ',' . $lat . ',' . $lng;
    $parts  = '';
    foreach ($cat_tags as $cat => $ctags) {
        foreach ($ctags as $t) {
            $parts .= 'node["' . $t[0] . '"="' . $t[1] . '"](' . $around . ');';
            $parts .= 'way["'  . $t[0] . '"="' . $t[1] . '"](' . $around . ');';
        }
    }
    $query = '[out:json][timeout:30];(' . $parts . ');out center 50;';

    $elements = overpass_query($query);

    if ($elements === null) {
        echo '<span class="err">Overpass failed after 3 retries. Skipping.</span></div>';
        flush();
        continue;
    }

    echo 'Got <b>' . count($elements) . '</b> elements. Saving...<br>';
    flush();

    // Ping DB before saving
    try { $pdo->query("SELECT 1"); } catch(Exception $e) {
        echo '<span class="err">DB reconnect needed - please refresh and continue from ?start=' . $start . '</span></div>';
        flush();
        exit;
    }

    // Delete old and save new
    try { $pdo->prepare("DELETE FROM places_cache WHERE neighbourhood_id = ?")->execute(array($nb['id'])); }
    catch(Exception $e) {}

    $ins = $pdo->prepare("INSERT INTO places_cache (neighbourhood_id, category, name, address, lat, lng, osm_url, fetched_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE address=VALUES(address), lat=VALUES(lat), lng=VALUES(lng), osm_url=VALUES(osm_url), fetched_at=NOW()");

    $counts = array();
    $seen   = array();

    foreach ($elements as $el) {
        $etags = isset($el['tags']) ? $el['tags'] : array();
        $cat = null;
        foreach ($cat_tags as $c => $ctags) {
            foreach ($ctags as $t) {
                if (isset($etags[$t[0]]) && $etags[$t[0]] === $t[1]) { $cat = $c; break 2; }
            }
        }
        if (!$cat) continue;

        $name = isset($etags['name']) ? trim($etags['name']) : '';
        if (!$name) {
            foreach (array('amenity','shop','leisure','highway') as $k) {
                if (!empty($etags[$k])) { $name = ucwords(str_replace('_',' ',$etags[$k])); break; }
            }
        }
        if (!$name) continue;

        $elat = isset($el['lat']) ? $el['lat'] : (isset($el['center']['lat']) ? $el['center']['lat'] : null);
        $elng = isset($el['lon']) ? $el['lon'] : (isset($el['center']['lon']) ? $el['center']['lon'] : null);
        if (!$elat || !$elng) continue;

        $key = $cat . '|' . $name . '|' . round($elat,3) . '|' . round($elng,3);
        if (isset($seen[$key])) continue;
        $seen[$key] = true;

        $hn   = isset($etags['addr:housenumber']) ? $etags['addr:housenumber'] : '';
        $st   = isset($etags['addr:street'])      ? $etags['addr:street']      : '';
        $addr = trim($hn . ' ' . $st);
        $osm  = 'https://www.openstreetmap.org/' . ($el['type'] === 'node' ? 'node' : 'way') . '/' . $el['id'];

        try {
            $ins->execute(array($nb['id'], $cat, mb_substr($name,0,255), mb_substr($addr,0,255), round($elat,7), round($elng,7), mb_substr($osm,0,255)));
            $counts[$cat] = isset($counts[$cat]) ? $counts[$cat] + 1 : 1;
            $grand_total++;
        } catch(Exception $e) {}
    }

    foreach (array_keys($cat_tags) as $cat) {
        $n = isset($counts[$cat]) ? $counts[$cat] : 0;
        if ($n > 0) echo $cat . ': <span class="ok">' . $n . ' saved</span><br>';
        else        echo $cat . ': <span class="zero">0 found</span><br>';
    }
    echo '</div>';
    flush();

    sleep(8); // Generous pause to avoid rate limiting
}

echo '<hr><p style="color:green;font-weight:bold;">' . $grand_total . ' places saved in this batch.</p>';

// If running all and there are more to process, show Next button
if ($all && $end < $total_nbs) {
    echo '<p>Next batch: ' . ($end+1) . ' to ' . min($end+$batch_size, $total_nbs) . ' of ' . $total_nbs . '</p>';
    echo '<a class="next-btn" href="?all=1&start=' . $end . '">Continue &rarr; Next ' . $batch_size . ' Neighbourhoods</a>';
} else {
    echo '<p style="color:green;font-weight:bold;">All done!</p>';
    echo '<p style="color:red;font-weight:bold;">Delete this file from your server now.</p>';
}

echo '</body></html>';