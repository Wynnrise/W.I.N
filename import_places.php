<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

while (ob_get_level()) ob_end_clean();

$base_dir = __DIR__ . '/Base';
require_once $base_dir . '/db.php';

$sql_file = __DIR__ . '/places_cache.sql';

if (!file_exists($sql_file)) {
    die('<p style="color:red;">places_cache.sql not found in public_html folder.</p>');
}

echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
echo '<style>body{font-family:sans-serif;padding:24px;max-width:700px;} .ok{color:green;} .err{color:red;}</style>';
echo '</head><body><h2>Importing places_cache.sql</h2>';
echo '<p>File size: ' . round(filesize($sql_file)/1024/1024, 2) . ' MB</p>';
flush();

$handle = fopen($sql_file, 'r');
if (!$handle) die('<p class="err">Cannot open file.</p>');

$statement = '';
$count = 0;
$errors = 0;
$line_num = 0;

while (!feof($handle)) {
    $line = fgets($handle);
    $line_num++;
    $line = trim($line);

    // Skip comments and empty lines
    if ($line === '' || strpos($line, '--') === 0) continue;

    $statement .= ' ' . $line;

    // Execute when we hit a semicolon at end of line
    if (substr($line, -1) === ';') {
        $statement = trim($statement);
        if ($statement && stripos($statement, 'INSERT') !== false) {
            try {
                $pdo->exec($statement);
                $count++;
                if ($count % 10 === 0) {
                    echo '<span class="ok">.' . $count . ' inserts done..</span><br>';
                    flush();
                }
            } catch (Exception $e) {
                // Ignore duplicate key errors
                if (strpos($e->getMessage(), '1062') === false) {
                    echo '<span class="err">Error at line ' . $line_num . ': ' . htmlspecialchars($e->getMessage()) . '</span><br>';
                    $errors++;
                }
            }
        } elseif ($statement && stripos($statement, 'CREATE') !== false) {
            try { $pdo->exec($statement); } catch (Exception $e) {}
        } elseif ($statement && stripos($statement, 'TRUNCATE') !== false) {
            try { $pdo->exec($statement); } catch (Exception $e) {}
        }
        $statement = '';
    }
}

fclose($handle);

echo '<hr>';
echo '<h3 class="ok">Done! ' . $count . ' INSERT statements executed.</h3>';
if ($errors > 0) echo '<p class="err">' . $errors . ' errors (non-duplicate).</p>';

// Verify
$q = $pdo->query("SELECT COUNT(*) as total, COUNT(DISTINCT neighbourhood_id) as nbs FROM places_cache");
$r = $q->fetch(PDO::FETCH_ASSOC);
echo '<p>Database now has <b>' . $r['total'] . '</b> places across <b>' . $r['nbs'] . '</b> neighbourhoods.</p>';
echo '<p style="color:red;font-weight:bold;">Delete this file and places_cache.sql from your server now.</p>';
echo '</body></html>';
