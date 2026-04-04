<?php
// Fetch City of NV (1524228) and District of NV (1524229) as two clean polygons
// Then individual neighbourhoods as centroids (pin markers)
$output_file = __DIR__ . '/northvan-boundaries.geojson';
echo "<pre>Fetching North Van municipality boundaries...\n"; flush();

function stitchWays(array $ways): array {
    if (count($ways) <= 1) return $ways;
    $used = array_fill(0, count($ways), false);
    $chain = $ways[0]; $used[0] = true; $changed = true;
    while ($changed) {
        $changed = false; $head = $chain[0]; $tail = end($chain);
        foreach ($ways as $i => $way) {
            if ($used[$i]) continue; $wh = $way[0]; $wt = end($way);
            if (abs($tail[0]-$wh[0])<0.00001 && abs($tail[1]-$wh[1])<0.00001) { $chain=array_merge($chain,array_slice($way,1)); $used[$i]=true; $changed=true; }
            elseif (abs($tail[0]-$wt[0])<0.00001 && abs($tail[1]-$wt[1])<0.00001) { $chain=array_merge($chain,array_slice(array_reverse($way),1)); $used[$i]=true; $changed=true; }
            elseif (abs($head[0]-$wt[0])<0.00001 && abs($head[1]-$wt[1])<0.00001) { $chain=array_merge(array_slice($way,0,-1),$chain); $used[$i]=true; $changed=true; }
            elseif (abs($head[0]-$wh[0])<0.00001 && abs($head[1]-$wh[1])<0.00001) { $chain=array_merge(array_reverse(array_slice($way,1)),$chain); $used[$i]=true; $changed=true; }
        }
    }
    return [$chain];
}

function fetchRelationPolygon(int $id, string $label): ?array {
    $u = "https://www.openstreetmap.org/api/0.6/relation/$id/full.json";
    $ctx = stream_context_create(['http'=>['timeout'=>20,'user_agent'=>'WynstonCA/1.0']]);
    $raw = @file_get_contents($u, false, $ctx);
    sleep(1);
    if (!$raw) { echo "  $label ($id) → fetch failed\n"; return null; }
    $d = json_decode($raw, true);
    $elements = $d['elements'] ?? [];
    $nodes = []; $ways = [];
    foreach ($elements as $el) {
        if ($el['type']==='node') $nodes[$el['id']] = [$el['lon'], $el['lat']];
        if ($el['type']==='way')  $ways[$el['id']]  = $el['nodes'] ?? [];
    }
    $outers = [];
    foreach ($elements as $el) {
        if ($el['type'] !== 'relation') continue;
        foreach ($el['members'] ?? [] as $m) {
            if ($m['type']!=='way' || !isset($ways[$m['ref']])) continue;
            $pts = array_values(array_filter(array_map(fn($nid)=>$nodes[$nid]??null, $ways[$m['ref']])));
            if (empty($pts)) continue;
            if (($m['role']??'outer') !== 'inner') $outers[] = $pts;
        }
    }
    if (empty($outers)) { echo "  $label → no geometry\n"; return null; }
    $rings = stitchWays($outers); $coords = [];
    foreach ($rings as $ring) { if ($ring[0]!==end($ring)) $ring[]=$ring[0]; $coords[] = $ring; }
    echo "  ✓ $label — ".count($coords[0])." pts\n"; flush();
    return $coords;
}

$features = [];

// City of North Vancouver
$cityCoords = fetchRelationPolygon(1524228, 'City of North Vancouver');
if ($cityCoords) {
    $features[] = ['type'=>'Feature','properties'=>['NAME'=>'City of North Vancouver','name'=>'City of North Vancouver','type'=>'municipality'],'geometry'=>['type'=>'Polygon','coordinates'=>$cityCoords]];
}

// District of North Vancouver
$distCoords = fetchRelationPolygon(1524229, 'District of North Vancouver');
if ($distCoords) {
    $features[] = ['type'=>'Feature','properties'=>['NAME'=>'District of North Vancouver','name'=>'District of North Vancouver','type'=>'municipality'],'geometry'=>['type'=>'Polygon','coordinates'=>$distCoords]];
}

file_put_contents($output_file, json_encode(['type'=>'FeatureCollection','features'=>$features]));
echo "\nSaved ".count($features)." polygons → northvan-boundaries.geojson\n";
echo "Delete this script.\n</pre>";