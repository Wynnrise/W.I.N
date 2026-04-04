<?php
$output_file = __DIR__ . '/burnaby-boundaries.geojson';
echo "<pre>Fetching layer 44 — OCP Community Plan Area Boundaries...\n"; flush();

$ctx = stream_context_create(['http'=>['timeout'=>20,'user_agent'=>'Mozilla/5.0']]);

$url = 'https://gis.burnaby.ca/arcgis/rest/services/BurnabyMap/BBY_PUBLIC_TOC/MapServer/44/query?where=1%3D1&outFields=*&outSR=4326&f=geojson&returnGeometry=true';

$raw = @file_get_contents($url, false, $ctx);
if (!$raw) { echo "FAILED — no response\n</pre>"; exit; }

$data = json_decode($raw, true);
if (!empty($data['error'])) { echo "Error: ".$data['error']['message']."\n</pre>"; exit; }

$features = $data['features'] ?? [];
echo "Got ".count($features)." features\n\n";

// Show all properties on first feature
echo "=== Properties ===\n";
foreach (($features[0]['properties'] ?? []) as $k => $v) echo "  $k => $v\n";

echo "\n=== All area names ===\n";
foreach ($features as $f) {
    $p = $f['properties'];
    // Try all possible name fields
    $name = $p['COMMUNITY_PLAN'] ?? $p['CommunityPlan'] ?? $p['NAME'] ?? $p['name'] ?? $p['LABEL'] ?? '???';
    echo "  - $name\n";
}

file_put_contents($output_file, $raw);
echo "\nSaved to burnaby-boundaries.geojson — delete this script when done.\n</pre>";