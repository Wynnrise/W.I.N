<?php
require_once __DIR__ . '/Base/db.php';

echo "<pre>";

// Show ALL Vancouver neighbourhoods in DB
$q = $pdo->query("SELECT id, name, slug, area, is_active FROM neighbourhoods WHERE area IN ('Vancouver East','Vancouver West') ORDER BY area, name");
$rows = $q->fetchAll(PDO::FETCH_ASSOC);

echo "Total Vancouver rows in DB: " . count($rows) . "\n\n";

$east = array_filter($rows, fn($r) => $r['area'] === 'Vancouver East');
$west = array_filter($rows, fn($r) => $r['area'] === 'Vancouver West');

echo "=== Vancouver East (" . count($east) . ") ===\n";
foreach ($east as $r) {
    echo ($r['is_active'] ? '✓' : '✗') . " [{$r['id']}] {$r['name']} | slug: {$r['slug']}\n";
}

echo "\n=== Vancouver West (" . count($west) . ") ===\n";
foreach ($west as $r) {
    echo ($r['is_active'] ? '✓' : '✗') . " [{$r['id']}] {$r['name']} | slug: {$r['slug']}\n";
}

// Also check if any have null/empty slugs
echo "\n=== Missing slugs ===\n";
$missing = array_filter($rows, fn($r) => empty($r['slug']));
echo count($missing) . " rows with empty slug\n";
foreach ($missing as $r) echo "  [{$r['id']}] {$r['name']}\n";

echo "</pre>";
