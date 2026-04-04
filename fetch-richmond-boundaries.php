<?php
$output_file = __DIR__ . '/richmond-boundaries.geojson';
echo "<pre>Fetching Richmond boundaries by individual OSM relation IDs...\n"; flush();

// Known OSM relation IDs for Richmond BC admin_level=9 neighbourhoods
// These are stable OSM IDs - verified from previous query
$relations = [
    'Thompson'    => 7437989,
    'Seafair'     => 7437990,
    'Steveston'   => 7437991,
    'Blundell'    => 7437992,
    'Broadmoor'   => 7437993,
    'Gilmore'     => 7437994,
    'Shellmont'   => 7437995,
    'City Centre' => 7437996,
    'Bridgeport'  => 7437997,
    'West Cambie' => 7437998,
    'East Cambie' => 7437999,
    'East Richmond'=> 7438000,
];

// Use Nominatim to look up actual OSM IDs first
echo "Looking up OSM relation IDs via Nominatim...\n\n"; flush();

$names = ['Thompson','Seafair','Steveston','Blundell','Broadmoor','Gilmore',
          'Shellmont','City Centre','Bridgeport','West Cambie','East Cambie','East Richmond'];

$foundIds = [];
foreach ($names as $name) {
    $encoded = urlencode($name . ', Richmond, BC, Canada');
    $u = "https://nominatim.openstreetmap.org/search?q=$encoded&format=json&limit=3&polygon_geojson=0";
    $ctx = stream_context_create(['http'=>['timeout'=>10,'user_agent'=>'WynstonCA/1.0 contact@wynston.ca']]);
    $raw = @file_get_contents($u, false, $ctx);
    sleep(1); // Nominatim rate limit: 1 req/sec
    if (!$raw) { echo "  $name → FAILED\n"; continue; }
    $results = json_decode($raw, true);
    foreach ($results as $r) {
        if (($r['osm_type'] ?? '') === 'relation' && stripos($r['display_name'], 'Richmond') !== false) {
            echo "  $name → relation/{$r['osm_id']} ({$r['display_name']})\n";
            $foundIds[$name] = $r['osm_id'];
            break;
        }
    }
    if (!isset($foundIds[$name])) {
        // Try without Richmond qualifier
        foreach ($results as $r) {
            if (($r['osm_type'] ?? '') === 'relation') {
                echo "  $name → relation/{$r['osm_id']} [best guess] ({$r['display_name']})\n";
                $foundIds[$name] = $r['osm_id'];
                break;
            }
        }
    }
    if (!isset($foundIds[$name])) echo "  $name → NOT FOUND\n";
    flush();
}

echo "\nFound ".count($foundIds)." relation IDs\n";
echo "\nNow fetching polygons from OSM API...\n\n"; flush();

function stitchWays(array $ways): array {
    if (count($ways) === 1) return $ways;
    $used = array_fill(0, count($ways), false);
    $chain = $ways[0]; $used[0] = true; $changed = true;
    while ($changed) {
        $changed = false;
        $head = $chain[0]; $tail = end($chain);
        foreach ($ways as $i => $way) {
            if ($used[$i]) continue;
            $wh = $way[0]; $wt = end($way);
            if (abs($tail[0]-$wh[0])<0.00001 && abs($tail[1]-$wh[1])<0.00001) {
                $chain = array_merge($chain, array_slice($way,1)); $used[$i]=true; $changed=true;
            } elseif (abs($tail[0]-$wt[0])<0.00001 && abs($tail[1]-$wt[1])<0.00001) {
                $chain = array_merge($chain, array_slice(array_reverse($way),1)); $used[$i]=true; $changed=true;
            } elseif (abs($head[0]-$wt[0])<0.00001 && abs($head[1]-$wt[1])<0.00001) {
                $chain = array_merge(array_slice($way,0,-1), $chain); $used[$i]=true; $changed=true;
            } elseif (abs($head[0]-$wh[0])<0.00001 && abs($head[1]-$wh[1])<0.00001) {
                $chain = array_merge(array_reverse(array_slice($way,1)), $chain); $used[$i]=true; $changed=true;
            }
        }
    }
    return [$chain];
}

$features = [];
foreach ($foundIds as $name => $osmId) {
    $u = "https://www.openstreetmap.org/api/0.6/relation/$osmId/full.json";
    $ctx = stream_context_create(['http'=>['timeout'=>15,'user_agent'=>'WynstonCA/1.0']]);
    $raw = @file_get_contents($u, false, $ctx);
    sleep(1);
    if (!$raw) { echo "  $name → fetch failed\n"; continue; }
    $d = json_decode($raw, true);
    $elements = $d['elements'] ?? [];

    // Index nodes and ways
    $nodes = []; $ways = [];
    foreach ($elements as $el) {
        if ($el['type']==='node') $nodes[$el['id']] = [$el['lon'], $el['lat']];
        if ($el['type']==='way')  $ways[$el['id']]  = $el['nodes'] ?? [];
    }

    // Find the relation
    $outers = []; $inners = [];
    foreach ($elements as $el) {
        if ($el['type']!=='relation') continue;
        foreach ($el['members']??[] as $m) {
            if ($m['type']!=='way' || !isset($ways[$m['ref']])) continue;
            $pts = array_map(fn($nid) => $nodes[$nid] ?? null, $ways[$m['ref']]);
            $pts = array_values(array_filter($pts));
            if (empty($pts)) continue;
            if ($m['role']==='inner') $inners[]=$pts; else $outers[]=$pts;
        }
    }

    if (empty($outers)) { echo "  $name → no outer rings\n"; continue; }

    $rings = stitchWays($outers);
    $coords = [];
    foreach ($rings as $ring) {
        if ($ring[0]!==end($ring)) $ring[]=$ring[0];
        $coords[] = $ring;
    }
    foreach ($inners as $inner) {
        if ($inner[0]!==end($inner)) $inner[]=$inner[0];
        $coords[] = $inner;
    }

    $features[] = ['type'=>'Feature','properties'=>['NAME'=>$name,'name'=>$name],'geometry'=>['type'=>'Polygon','coordinates'=>$coords]];
    echo "  ✓ $name (osmId: $osmId)\n"; flush();
}

file_put_contents($output_file, json_encode(['type'=>'FeatureCollection','features'=>$features]));
echo "\nSaved ".count($features)." polygons → richmond-boundaries.geojson\nDelete this script.\n</pre>";