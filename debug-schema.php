<?php
require_once __DIR__ . '/Base/db.php';
echo "<pre>";

// Show full column list for neighbourhoods table
$cols = $pdo->query("SHOW COLUMNS FROM neighbourhoods")->fetchAll(PDO::FETCH_ASSOC);
echo "=== neighbourhoods table columns ===\n";
foreach ($cols as $col) {
    echo sprintf("%-30s %-20s %s\n", $col['Field'], $col['Type'], $col['Null']==='YES'?'nullable':'NOT NULL');
}

// Show which fields are populated for each neighbourhood
echo "\n=== Data completeness per neighbourhood ===\n";
$rows = $pdo->query("SELECT id, name, slug, area,
    walkscore, transitscore, bikescore,
    population, median_income, area_sqkm,
    hpi_benchmark, price_detached, price_condo, price_townhouse,
    description
    FROM neighbourhoods WHERE is_active=1 ORDER BY area, name")->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    $flags = [];
    if ($r['walkscore'])     $flags[] = 'walk';
    if ($r['population'])    $flags[] = 'pop';
    if ($r['hpi_benchmark']) $flags[] = 'hpi';
    if ($r['description'])   $flags[] = 'desc';
    $missing = array_diff(['walk','pop','hpi','desc'], $flags);
    echo sprintf("[%2d] %-35s %-20s  has:%s  missing:%s\n",
        $r['id'], $r['name'], $r['area'],
        implode(',',$flags) ?: 'none',
        implode(',',$missing) ?: 'none'
    );
}
echo "</pre>";
