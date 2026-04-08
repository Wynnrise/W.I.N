<?php
// debug_aliases.php — run once then delete
require_once __DIR__ . '/dev-auth.php';

$host = 'localhost';
$db   = 'u990588858_Property';
$user = 'u990588858_Multiplex';
$pass = 'Concac1979$';
$pdo  = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);

// Build nb_lkp exactly as upload handler does
$neighbourhoods = $pdo->query("SELECT id, slug, name FROM neighbourhoods WHERE is_active=1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$nb_lkp = [];
foreach ($neighbourhoods as $nb) { $nb_lkp[$nb['slug']] = (int)$nb['id']; }

// Include plex-data.php aliases by extracting them
// Test the exact logic inline
$test_names = ['Fraser', 'Fraser VE', 'Knight', 'Main', 'Collingwood VE'];

echo "<pre>";
echo "nb_lkp has " . count($nb_lkp) . " entries\n";
echo "knight in nb_lkp: " . ($nb_lkp['knight'] ?? 'NOT FOUND') . "\n";
echo "kensington-cedar-cottage in nb_lkp: " . ($nb_lkp['kensington-cedar-cottage'] ?? 'NOT FOUND') . "\n\n";

// Read the actual $_rebgv_aliases from plex-data.php without executing whole file
$content = file_get_contents(__DIR__ . '/plex-data.php');
preg_match('/\$_rebgv_aliases\s*=\s*\[(.*?)\];/s', $content, $m);
echo "Alias block found: " . (isset($m[1]) ? "YES (".strlen($m[1])." chars)" : "NO") . "\n";
if (isset($m[1])) {
    echo "'fraser' in block: " . (strpos($m[1], "'fraser'") !== false ? "YES" : "NO") . "\n";
    echo "'knight' => kensington: " . (strpos($m[1], "'knight'               => 'kensington-cedar-cottage'") !== false ? "YES" : "NO") . "\n";
    echo "'knight' => knight: " . (strpos($m[1], "'knight'              => 'knight'") !== false ? "YES" : "NO") . "\n";
}
echo "</pre>";
