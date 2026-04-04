<?php
// Fetch West Vancouver municipality boundary (relation 1524231)
// + probe for any neighbourhood-level polygons
$output_file = __DIR__ . '/westvan-boundaries.geojson';
echo "<pre>Fetching West Vancouver boundaries...\n"; flush();

function stitchWays(array $ways): array {
    if (count($ways) <= 1) return $ways;
    $used = array_fill(0, count($ways), false);
    $chain = $ways[0]; $used[0] = true; $changed = true;
    while ($changed) {
        $changed = false; $head = $chain[0]; $tail = end($chain);
        foreach ($ways as $i => $way) {
            if ($used[$i]) continue; $wh = $way[0]; $wt = end($way);
            if (abs($tail[0]-$wh[0])<0.00001&&abs($tail[1]-$wh[1])<0.00001){$chain=array_merge($chain,array_slice($way,1));$used[$i]=true;$changed=true;}
            elseif(abs($tail[0]-$wt[0])<0.00001&&abs($tail[1]-$wt[1])<0.00001){$chain=array_merge($chain,array_slice(array_reverse($way),1));$used[$i]=true;$changed=true;}
            elseif(abs($head[0]-$wt[0])<0.00001&&abs($head[1]-$wt[1])<0.00001){$chain=array_merge(array_slice($way,0,-1),$chain);$used[$i]=true;$changed=true;}
            elseif(abs($head[0]-$wh[0])<0.00001&&abs($head[1]-$wh[1])<0.00001){$chain=array_merge(array_reverse(array_slice($way,1)),$chain);$used[$i]=true;$changed=true;}
        }
    }
    return [$chain];
}

function fetchRelation(int $id, string $label): ?array {
    $u = "https://www.openstreetmap.org/api/0.6/relation/$id/full.json";
    $ctx = stream_context_create(['http'=>['timeout'=>25,'user_agent'=>'WynstonCA/1.0']]);
    $raw = @file_get_contents($u, false, $ctx);
    sleep(1);
    if (!$raw) { echo "  $label ($id) → fetch failed\n"; return null; }
    $d = json_decode($raw, true);
    $elements = $d['elements'] ?? [];
    $nodes=[]; $ways=[];
    foreach ($elements as $el) {
        if ($el['type']==='node') $nodes[$el['id']]=[$el['lon'],$el['lat']];
        if ($el['type']==='way')  $ways[$el['id']]=$el['nodes']??[];
    }
    $outers=[];
    foreach ($elements as $el) {
        if ($el['type']!=='relation') continue;
        foreach ($el['members']??[] as $m) {
            if ($m['type']!=='way'||!isset($ways[$m['ref']])) continue;
            $pts=array_values(array_filter(array_map(fn($nid)=>$nodes[$nid]??null,$ways[$m['ref']])));
            if (empty($pts)) continue;
            if (($m['role']??'outer')!=='inner') $outers[]=$pts;
        }
    }
    if (empty($outers)) { echo "  $label → no geometry\n"; return null; }
    $rings=stitchWays($outers); $coords=[];
    foreach ($rings as $ring) { if ($ring[0]!==end($ring)) $ring[]=$ring[0]; $coords[]=$ring; }
    echo "  ✓ $label — ".count($coords[0])." pts\n"; flush();
    return $coords;
}

$features = [];

// West Vancouver municipality boundary
$coords = fetchRelation(1524231, 'West Vancouver');
if ($coords) {
    $features[] = ['type'=>'Feature','properties'=>['NAME'=>'West Vancouver','name'=>'West Vancouver','type'=>'municipality'],'geometry'=>['type'=>'Polygon','coordinates'=>$coords]];
}

// Also probe for any neighbourhood relations in West Van bbox
echo "\nProbing for neighbourhood-level relations in West Van...\n"; flush();
$overpass = 'https://overpass-api.de/api/interpreter';
$q = '[out:json][timeout:60];(rel["boundary"]["name"](49.30,-123.30,49.45,-123.08);rel["place"]["name"](49.30,-123.30,49.45,-123.08););out tags;';
$opts = stream_context_create(['http'=>['method'=>'POST','header'=>'Content-Type: application/x-www-form-urlencoded','content'=>'data='.urlencode($q),'timeout'=>60,'user_agent'=>'WynstonCA/1.0']]);
$raw = @file_get_contents($overpass, false, $opts);
$els = json_decode($raw,true)['elements'] ?? [];
echo count($els)." relations found:\n";
$neighbourhoodIds = [];
foreach ($els as $el) {
    $name  = $el['tags']['name'] ?? '?';
    $level = $el['tags']['admin_level'] ?? '-';
    $type  = $el['tags']['boundary'] ?? $el['tags']['place'] ?? '?';
    echo "  [rel {$el['id']} lv:$level] $name ($type)\n";
    // Flag anything that looks like a neighbourhood
    if (in_array($level,['9','10','11']) || in_array($type,['suburb','neighbourhood','quarter'])) {
        $neighbourhoodIds[$name] = $el['id'];
    }
}

if (!empty($neighbourhoodIds)) {
    echo "\nFound ".count($neighbourhoodIds)." neighbourhood-level relations! Fetching polygons...\n"; flush();
    foreach ($neighbourhoodIds as $name => $id) {
        $coords = fetchRelation($id, $name);
        if ($coords) {
            $features[] = ['type'=>'Feature','properties'=>['NAME'=>$name,'name'=>$name,'type'=>'neighbourhood'],'geometry'=>['type'=>'Polygon','coordinates'=>$coords]];
        }
    }
} else {
    echo "\nNo neighbourhood polygons found — will use pins like North Van.\n";
}

file_put_contents($output_file, json_encode(['type'=>'FeatureCollection','features'=>$features]));
echo "\nSaved ".count($features)." features → westvan-boundaries.geojson\nDelete this script.\n</pre>";
