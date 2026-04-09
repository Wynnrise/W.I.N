<?php
// ============================================================
// admin.php  —  PRIVATE admin panel for managing listings
// Password protected — change ADMIN_PASSWORD below!
// ============================================================

define('ADMIN_PASSWORD', 'Concac1979$'); 
session_start();

// ── Login / Logout ────────────────────────────────────────────────────────────
if (isset($_POST['admin_login'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = 'Incorrect password.';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
if (empty($_SESSION['admin_logged_in'])) {
    // Show login screen
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #002446; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
            .login-box { background: #fff; border-radius: 12px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
            .login-box h2 { color: #002446; font-weight: 800; margin-bottom: 6px; }
            .login-box p  { color: #888; font-size: 13px; margin-bottom: 24px; }
            .btn-admin { background: #002446; color: #fff; border: none; width: 100%; padding: 12px; border-radius: 8px; font-weight: 700; }
            .btn-admin:hover { background: #0065ff; color: #fff; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>🏢 Admin Panel</h2>
            <p>Multiplex Listings Management</p>
            <?php if (!empty($login_error)): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($login_error) ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Enter admin password" required autofocus>
                </div>
                <button type="submit" name="admin_login" class="btn-admin">Login</button>
            </form>
        </div>

    </body>
    </html>
    <?php
    exit;
}

// ── DB Connection ─────────────────────────────────────────────────────────────
$host = 'localhost';
$db   = 'u990588858_Property';
$user = 'u990588858_Multiplex';
$pass = 'Concac1979$';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB error: " . $e->getMessage());
}

// ── Upload folder setup ──────────────────────────────────────────────────────
$upload_base    = __DIR__ . '/uploads/properties/';
$upload_url_base = '/uploads/properties/';
if (!is_dir($upload_base)) mkdir($upload_base, 0755, true);

// Create address-based subfolder (e.g. "4423-main-st")
function get_property_folder(int $id, string $address, string $upload_base, string $upload_url_base): array {
    $slug = strtolower(trim($address));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = substr($slug, 0, 60) ?: 'property-' . $id;

    $folder_path = $upload_base . $slug . '/';
    $folder_url  = $upload_url_base . $slug . '/';

    // Create subfolders on first use
    foreach (['photos', 'floorplans', 'videos', 'documents'] as $sub) {
        if (!is_dir($folder_path . $sub)) mkdir($folder_path . $sub, 0755, true);
    }
    return ['path' => $folder_path, 'url' => $folder_url, 'slug' => $slug];
}

function handle_upload($file_key, $property_id, $slot, $upload_base, $upload_url_base) {
    if (empty($_FILES[$file_key]['name'])) return null;
    $file    = $_FILES[$file_key];
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif','pdf'];
    if (!in_array($ext, $allowed)) return 'ERR:Only JPG/PNG/WEBP/GIF/PDF allowed';
    if ($file['size'] > 10 * 1024 * 1024) return 'ERR:File too large (max 10MB)';
    $filename = "prop_{$property_id}_{$slot}_" . time() . ".{$ext}";
    if (move_uploaded_file($file['tmp_name'], $upload_base . $filename)) {
        return $upload_url_base . $filename;
    }
    return null;
}

// Subfolder-aware upload handler
function handle_upload_to_folder($file_key, $slot, $folder_path, $folder_url) {
    if (empty($_FILES[$file_key]['name'])) return null;
    $file = $_FILES[$file_key];
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif','pdf','mp4','mov'];
    if (!in_array($ext, $allowed)) return 'ERR:File type not allowed';
    if ($file['size'] > 50 * 1024 * 1024) return 'ERR:File too large (max 50MB)';

    // Route to correct subfolder
    if (in_array($ext, ['mp4','mov'])) {
        $sub = 'videos/';
    } elseif ($ext === 'pdf') {
        $sub = 'floorplans/';
    } elseif (strpos($slot, 'floorplan') !== false) {
        $sub = 'floorplans/';
    } else {
        $sub = 'photos/';
    }

    $filename = $slot . '_' . time() . '.' . $ext;
    $dest_path = $folder_path . $sub . $filename;
    $dest_url  = $folder_url  . $sub . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest_path)) {
        return $dest_url;
    }
    return null;
}

// ── Auto-add tier column if upgrading from old is_paid boolean ───────────────
$__mc = $pdo->query("DESCRIBE multi_2025")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('tier', $__mc)) {
    $pdo->exec("ALTER TABLE multi_2025 ADD COLUMN tier VARCHAR(20) DEFAULT 'free' AFTER is_paid");
    // Migrate old is_paid values: 1 => concierge, 0 => free
    if (in_array('is_paid', $__mc)) {
        $pdo->exec("UPDATE multi_2025 SET tier = 'concierge' WHERE is_paid = 1");
        $pdo->exec("UPDATE multi_2025 SET tier = 'free' WHERE is_paid = 0 OR is_paid IS NULL");
    }
}

// ── Handle form actions ───────────────────────────────────────────────────────
$message = '';

// SAVE EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_property'])) {
    $id = (int)$_POST['id'];
    $address = trim($_POST['address'] ?? 'property-' . $id);

    // Create address-based subfolder
    $folder = get_property_folder($id, $address, $upload_base, $upload_url_base);

    // Process photo uploads img1-img6
    $img_fields = [];
    for ($i = 1; $i <= 6; $i++) {
        $up = handle_upload_to_folder("img{$i}_file", "img{$i}", $folder['path'], $folder['url']);
        if ($up && !strpos($up, 'ERR:') === 0) {
            $img_fields["img{$i}"] = $up;
        } elseif ($up && strpos($up, 'ERR:') === 0) {
            $message .= " ⚠️ Photo {$i}: " . substr($up, 4);
            $img_fields["img{$i}"] = $_POST["img{$i}"] ?? '';
        } else {
            $img_fields["img{$i}"] = $_POST["img{$i}"] ?? '';
        }
    }

    // Process floor plan upload
    $floorplan_val = $_POST['floorplan'] ?? '';
    $fp = handle_upload_to_folder('floorplan_file', 'floorplan', $folder['path'], $folder['url']);
    if ($fp && !strpos($fp, 'ERR:') === 0) {
        $floorplan_val = $fp;
    } elseif ($fp && strpos($fp, 'ERR:') === 0) {
        $message .= " ⚠️ Floorplan: " . substr($fp, 4);
    }

    // Process builder logo upload
    $builder_logo_val = $_POST['builder_logo'] ?? '';
    $bl = handle_upload_to_folder('builder_logo_file', 'builder_logo', $folder['path'], $folder['url']);
    if ($bl && !strpos($bl, 'ERR:') === 0) {
        $builder_logo_val = $bl;
    } elseif ($bl && strpos($bl, 'ERR:') === 0) {
        $message .= " ⚠️ Builder Logo: " . substr($bl, 4);
    }

    $fields = [
        'address'        => $_POST['address']        ?? '',
        'description'    => $_POST['description']    ?? '',
        'property_type'  => $_POST['property_type']  ?? '',
        'est_completion' => $_POST['est_completion']  ?? '',
        'neighborhood'   => $_POST['neighborhood']   ?? '',
        'latitude'       => $_POST['latitude']       ?? 0,
        'longitude'      => $_POST['longitude']      ?? 0,
        'price'          => $_POST['price']          ?? '',
        'bedrooms'       => $_POST['bedrooms']       !== '' ? $_POST['bedrooms'] : null,
        'bathrooms'      => $_POST['bathrooms']      !== '' ? $_POST['bathrooms'] : null,
        'sqft'           => $_POST['sqft']           !== '' ? $_POST['sqft'] : null,
        'parking'        => $_POST['parking']        ?? '',
        'strata_fee'     => $_POST['strata_fee']     ?? '',
        'builder_logo'   => $builder_logo_val,
        'developer_name'     => $_POST['developer_name'] ?? '',
        'developer_bio'      => $_POST['developer_bio'] ?? '',
        'builder_website'=> $_POST['builder_website'] ?? '',
        'builder_awards' => $_POST['builder_awards']  ?? '',
        'virtual_tour_url' => trim($_POST['virtual_tour_url'] ?? ''),
        'video_url'     => (function($url) {
            $url = trim($url ?? '');
            if (empty($url)) return '';
            // Already a valid embed URL — leave it
            if (strpos($url, 'youtube.com/embed/') !== false) return $url;
            if (strpos($url, 'player.vimeo.com/video/') !== false) return $url;
            // youtu.be/VIDEO_ID
            if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $m))
                return 'https://www.youtube.com/embed/' . $m[1];
            // youtube.com/watch?v=VIDEO_ID
            if (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $url, $m))
                return 'https://www.youtube.com/embed/' . $m[1];
            // vimeo.com/VIDEO_ID
            if (preg_match('/vimeo\.com\/(\d+)/', $url, $m))
                return 'https://player.vimeo.com/video/' . $m[1];
            // Return as-is for anything else
            return $url;
        })($_POST['video_url'] ?? ''),
        'floorplan'     => $floorplan_val,
        'amenities'          => $_POST['amenities']     ?? '',
        'features'           => $_POST['features']      ?? '',
        'community_features' => $_POST['community_features'] ?? '',
        'tier'          => in_array($_POST['tier'] ?? 'free', ['free','creative','concierge']) ? ($_POST['tier'] ?? 'free') : 'free',
        'img1'          => $img_fields['img1'],
        'img2'          => $img_fields['img2'],
        'img3'          => $img_fields['img3'],
        'img4'          => $img_fields['img4'],
        'img5'          => $img_fields['img5'],
        'img6'          => $img_fields['img6'],
    ];

    // Build SET clause dynamically — only update columns that exist
    $cols       = $pdo->query("DESCRIBE multi_2025")->fetchAll(PDO::FETCH_COLUMN);
    $set        = [];
    $save_params = [':id' => $id];
    foreach ($fields as $col => $val) {
        if (in_array($col, $cols)) {
            $set[] = "`$col` = :$col";
            $save_params[":$col"] = $val;
        }
    }
    if (!empty($set)) {
        $pdo->prepare("UPDATE multi_2025 SET " . implode(', ', $set) . " WHERE id = :id")->execute($save_params);
        $message = "✅ Property #{$id} saved successfully.";
        // Clear cache for this property so changes show immediately
        $cache_file = __DIR__ . '/cache/property_' . $id . '.json';
        if (file_exists($cache_file)) unlink($cache_file);
    }
    // After save, stay on edit view
    $edit_id = $id;
}

// DELETE
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM multi_2025 WHERE id = :id")->execute([':id' => $del_id]);
    $message = "🗑️ Property #{$del_id} deleted.";
}

// SET TIER
if (isset($_GET['set_tier'])) {
    $tog_id  = (int)$_GET['set_tier'];
    $new_tier = $_GET['tier'] ?? 'free';
    if (!in_array($new_tier, ['free','creative','concierge'])) $new_tier = 'free';
    $pdo->prepare("UPDATE multi_2025 SET tier = ? WHERE id = ?")->execute([$new_tier, $tog_id]);
    $message = "Tier updated to '{$new_tier}' for property #{$tog_id}.";
    header("Location: admin.php?msg=" . urlencode($message));
    exit;
}

// Carry message from redirect
if (!empty($_GET['msg'])) $message = htmlspecialchars($_GET['msg']);

// ── Developer approval actions ────────────────────────────────────────────────
if (isset($_GET['dev_approve'])) {
    $did = (int)$_GET['dev_approve'];
    $pdo->prepare("UPDATE developers SET status='approved' WHERE id=?")->execute([$did]);
    header("Location: admin.php?tab=developers&msg=" . urlencode("✅ Developer approved."));
    exit;
}
if (isset($_GET['dev_suspend'])) {
    $did = (int)$_GET['dev_suspend'];
    $pdo->prepare("UPDATE developers SET status='suspended' WHERE id=?")->execute([$did]);
    header("Location: admin.php?tab=developers&msg=" . urlencode("⛔ Developer suspended."));
    exit;
}
if (isset($_GET['dev_pending'])) {
    $did = (int)$_GET['dev_pending'];
    $pdo->prepare("UPDATE developers SET status='pending' WHERE id=?")->execute([$did]);
    header("Location: admin.php?tab=developers&msg=" . urlencode("🔄 Developer set to pending."));
    exit;
}
if (isset($_GET['dev_delete'])) {
    $did = (int)$_GET['dev_delete'];
    $pdo->prepare("DELETE FROM developers WHERE id=?")->execute([$did]);
    header("Location: admin.php?tab=developers&msg=" . urlencode("🗑️ Developer deleted."));
    exit;
}
if (isset($_POST['dev_set_type'])) {
    $did  = (int)$_POST['dev_id'];
    $type = $_POST['user_type'] ?? 'builder';
    $allowed = ['builder','investor','realtor','broker'];
    if (!in_array($type, $allowed)) $type = 'builder';
    $pdo->prepare("UPDATE developers SET user_type = ? WHERE id = ?")->execute([$type, $did]);
    header("Location: admin.php?tab=developers&msg=" . urlencode("✅ User type updated to '{$type}'."));
    exit;
}

// ── Data Import POST handlers ─────────────────────────────────────────────────

// Heritage import — COV heritage-sites.csv
// Fast: loads all plex lots into PHP memory, matches in PHP, single batch UPDATE
if (isset($_POST['import_heritage']) && isset($_FILES['heritage_csv'])) {
    header('Content-Type: application/json');
    ini_set('max_execution_time', 120);
    ini_set('memory_limit', '256M');

    $file = $_FILES['heritage_csv'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success'=>false,'error'=>'Upload error: '.$file['error']]); exit;
    }

    // Strip UTF-8 BOM, detect delimiter
    $raw = file_get_contents($file['tmp_name']);
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
    $first_line = strtok($raw, "\n");
    $delim = (substr_count($first_line, ';') > substr_count($first_line, ',')) ? ';' : ',';
    file_put_contents($file['tmp_name'], $raw);

    // Parse headers
    $handle = fopen($file['tmp_name'], 'r');
    $raw_headers = fgetcsv($handle, 0, $delim);
    $headers = array_map(fn($h) => strtolower(trim($h)), $raw_headers);
    $cols = array_flip($headers);

    // Load ALL plex lots with coordinates into memory (pid, lat, lng)
    $lots = $pdo->query("SELECT pid, lat, lng FROM plex_properties WHERE lat IS NOT NULL AND lng IS NOT NULL")
                ->fetchAll(PDO::FETCH_ASSOC);

    // Build spatial grid: bucket lots by rounded lat/lng (0.01 degree cells ~1km)
    // So each heritage point only checks lots in its cell and adjacent cells
    $grid = [];
    foreach ($lots as $lot) {
        $gk = round((float)$lot['lat'], 2) . ',' . round((float)$lot['lng'], 2);
        $grid[$gk][] = $lot;
    }

    // Parse heritage CSV, find nearest lot for each point
    $heritage = []; // pid => category
    $allowed = ['A','B','C'];
    $total = 0; $skipped = 0;

    while (($row = fgetcsv($handle, 0, $delim)) !== false) {
        if (count($row) < 3) continue;
        $total++;

        $cat_raw = strtoupper(trim($row[$cols['evaluationgroup'] ?? $cols['category'] ?? 0] ?? ''));
        if (!in_array($cat_raw, $allowed)) { $skipped++; continue; }

        $geo_str = trim($row[$cols['geo_point_2d'] ?? 0] ?? '');
        if (!$geo_str) { $skipped++; continue; }
        $parts = array_map('trim', explode(',', $geo_str));
        if (count($parts) < 2) { $skipped++; continue; }
        $hlat = (float)$parts[0];
        $hlng = (float)$parts[1];
        if (!$hlat || !$hlng) { $skipped++; continue; }

        // Check grid cell + 8 neighbours
        $best_pid  = null;
        $best_dist = PHP_FLOAT_MAX;
        $base_lat  = round($hlat, 2);
        $base_lng  = round($hlng, 2);
        $offsets   = [-0.01, 0, 0.01];

        foreach ($offsets as $dlat) {
            foreach ($offsets as $dlng) {
                $gk = round($base_lat + $dlat, 2) . ',' . round($base_lng + $dlng, 2);
                if (!isset($grid[$gk])) continue;
                foreach ($grid[$gk] as $lot) {
                    $dist = abs((float)$lot['lat'] - $hlat) + abs((float)$lot['lng'] - $hlng);
                    if ($dist < $best_dist && $dist < 0.0004) { // ~40m threshold
                        $best_dist = $dist;
                        $best_pid  = $lot['pid'];
                    }
                }
            }
        }

        if ($best_pid) {
            // If same lot already tagged, keep highest category (A > B > C)
            if (!isset($heritage[$best_pid]) || strcmp($cat_raw, $heritage[$best_pid]) < 0) {
                $heritage[$best_pid] = $cat_raw;
            }
        } else {
            $skipped++;
        }
    }
    fclose($handle);

    // Batch update: reset all, then set matched lots
    $pdo->exec("UPDATE plex_properties SET heritage_category = 'none'");
    $stmt = $pdo->prepare("UPDATE plex_properties SET heritage_category = ? WHERE pid = ?");
    foreach ($heritage as $pid => $cat) {
        $stmt->execute([$cat, $pid]);
    }

    $matched = count($heritage);
    echo json_encode([
        'success' => true,
        'total'   => $total,
        'matched' => $matched,
        'skipped' => $skipped,
        'note'    => "Matched $matched heritage lots by GPS coordinates",
    ]); exit;
}

// Peat import — SQL bbox pre-filter + PHP pip only on candidates
if (isset($_POST['import_peat'])) {
    header('Content-Type: application/json');
    ini_set('memory_limit','256M'); ini_set('max_execution_time',120);

    $builtin = [
        [[-123.132,49.218],[-123.118,49.218],[-123.118,49.228],[-123.132,49.228],[-123.132,49.218]],
        [[-123.095,49.238],[-123.075,49.238],[-123.075,49.248],[-123.095,49.248],[-123.095,49.238]],
        [[-123.105,49.205],[-123.080,49.205],[-123.080,49.218],[-123.105,49.218],[-123.105,49.205]],
    ];

    $raw_polys = [];
    $use_builtin = isset($_POST['peat_builtin']) || !isset($_FILES['peat_geojson']) || $_FILES['peat_geojson']['error']!==UPLOAD_ERR_OK;

    if (!$use_builtin) {
        $gj = json_decode(file_get_contents($_FILES['peat_geojson']['tmp_name']), true);
        if (!$gj||!isset($gj['features'])){echo json_encode(['success'=>false,'error'=>'Invalid GeoJSON']);exit;}
        foreach ($gj['features'] as $feat) {
            $geom=$feat['geometry'];
            if ($geom['type']==='Polygon')      $raw_polys[]=array_map(fn($c)=>[$c[0],$c[1]],$geom['coordinates'][0]);
            elseif($geom['type']==='MultiPolygon') foreach($geom['coordinates'] as $p) $raw_polys[]=array_map(fn($c)=>[$c[0],$c[1]],$p[0]);
        }
    } else {
        $raw_polys = $builtin;
    }

    // Build bbox per polygon
    $poly_boxes = [];
    foreach ($raw_polys as $poly) {
        $lngs=array_column($poly,0); $lats=array_column($poly,1);
        $poly_boxes[]=['coords'=>$poly,'minlat'=>min($lats)-0.0001,'maxlat'=>max($lats)+0.0001,'minlng'=>min($lngs)-0.0001,'maxlng'=>max($lngs)+0.0001];
    }

    function pip_pt(float $lat,float $lng,array $poly):bool {
        $n=count($poly);$inside=false;$j=$n-1;
        for($i=0;$i<$n;$i++){
            $xi=$poly[$i][0];$yi=$poly[$i][1];$xj=$poly[$j][0];$yj=$poly[$j][1];
            if((($yi>$lat)!==($yj>$lat))&&($lng<($xj-$xi)*($lat-$yi)/($yj-$yi)+$xi))$inside=!$inside;
            $j=$i;
        }
        return $inside;
    }

    $pdo->exec("UPDATE plex_properties SET peat_zone=0");
    $stmt_upd = $pdo->prepare("UPDATE plex_properties SET peat_zone=1 WHERE pid=?");
    $flagged=0; $total_checked=0; $already=[];

    foreach ($poly_boxes as $pb) {
        $stmt = $pdo->prepare("SELECT pid,lat,lng FROM plex_properties WHERE lat BETWEEN ? AND ? AND lng BETWEEN ? AND ? AND lat IS NOT NULL");
        $stmt->execute([$pb['minlat'],$pb['maxlat'],$pb['minlng'],$pb['maxlng']]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_checked += count($candidates);
        foreach ($candidates as $lot) {
            if (isset($already[$lot['pid']])) continue;
            if (pip_pt((float)$lot['lat'],(float)$lot['lng'],$pb['coords'])) {
                $stmt_upd->execute([$lot['pid']]);
                $already[$lot['pid']]=1;
                $flagged++;
            }
        }
    }

    echo json_encode(['success'=>true,'lots_checked'=>$total_checked,'flagged'=>$flagged,'polygons'=>count($poly_boxes),'method'=>$use_builtin?'builtin':'geojson']); exit;
}

// Floodplain import — SQL bbox pre-filter + PHP pip only on candidates
if (isset($_POST['import_floodplain']) && isset($_FILES['flood_geojson']) && $_FILES['flood_geojson']['error']===UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    ini_set('memory_limit','256M'); ini_set('max_execution_time',120);

    $gj = json_decode(file_get_contents($_FILES['flood_geojson']['tmp_name']), true);
    if (!$gj || !isset($gj['features'])) {
        echo json_encode(['success'=>false,'error'=>'Invalid GeoJSON']); exit;
    }

    // Parse polygons + compute tight bounding boxes
    $poly_boxes = [];
    foreach ($gj['features'] as $feat) {
        $props = $feat['properties'] ?? [];
        $risk  = $props['flood_risk'] ?? 'high';
        $geom  = $feat['geometry'];
        $rings = [];
        if ($geom['type']==='Polygon')      $rings[] = $geom['coordinates'][0];
        elseif ($geom['type']==='MultiPolygon') foreach($geom['coordinates'] as $p) $rings[] = $p[0];
        foreach ($rings as $ring) {
            $coords = array_map(fn($c)=>[$c[0],$c[1]], $ring);
            $lngs   = array_column($coords,0); $lats = array_column($coords,1);
            $poly_boxes[] = ['risk'=>$risk,'coords'=>$coords,
                'minlat'=>min($lats)-0.0001,'maxlat'=>max($lats)+0.0001,
                'minlng'=>min($lngs)-0.0001,'maxlng'=>max($lngs)+0.0001];
        }
    }

    // Point-in-polygon
    function pip_fl(float $lat,float $lng,array $poly):bool {
        $n=count($poly);$inside=false;$j=$n-1;
        for($i=0;$i<$n;$i++){
            $xi=$poly[$i][0];$yi=$poly[$i][1];$xj=$poly[$j][0];$yj=$poly[$j][1];
            if((($yi>$lat)!==($yj>$lat))&&($lng<($xj-$xi)*($lat-$yi)/($yj-$yi)+$xi))$inside=!$inside;
            $j=$i;
        }
        return $inside;
    }

    // Reset
    $pdo->exec("UPDATE plex_properties SET floodplain_risk='none'");

    $total_checked = 0;
    $matches = []; // pid => risk

    // For each polygon, SQL-fetch only lots within its bbox, then do pip
    foreach ($poly_boxes as $pb) {
        $stmt = $pdo->prepare("
            SELECT pid, lat, lng FROM plex_properties
            WHERE lat BETWEEN ? AND ?
              AND lng BETWEEN ? AND ?
              AND lat IS NOT NULL
        ");
        $stmt->execute([$pb['minlat'], $pb['maxlat'], $pb['minlng'], $pb['maxlng']]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_checked += count($candidates);

        foreach ($candidates as $lot) {
            $lat=(float)$lot['lat']; $lng=(float)$lot['lng'];
            if (pip_fl($lat,$lng,$pb['coords'])) {
                $pid = $lot['pid'];
                // High risk overrides low
                if (!isset($matches[$pid]) || $pb['risk']==='high') {
                    $matches[$pid] = $pb['risk'];
                }
            }
        }
    }

    // Batch update
    $sh = $pdo->prepare("UPDATE plex_properties SET floodplain_risk='high' WHERE pid=?");
    $sl = $pdo->prepare("UPDATE plex_properties SET floodplain_risk='low'  WHERE pid=?");
    $high=$low=0;
    foreach ($matches as $pid=>$risk) {
        if ($risk==='high'){$sh->execute([$pid]);$high++;}
        else               {$sl->execute([$pid]);$low++;}
    }

    echo json_encode([
        'success'       => true,
        'lots_checked'  => $total_checked,
        'high_risk'     => $high,
        'low_risk'      => $low,
        'polygons'      => count($poly_boxes),
    ]); exit;
}

// Neighbourhood boundary import — COV local-area-boundary.csv
// Point-in-polygon for all 64,000 lots, sets neighbourhood_slug to human-readable format
// e.g. nb_012 → renfrew-collingwood
if (isset($_POST['import_neighbourhood_boundary']) && isset($_FILES['boundary_csv']) && $_FILES['boundary_csv']['error']===UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    ini_set('memory_limit','256M'); ini_set('max_execution_time',180);

    // Strip BOM, detect delimiter
    $raw = file_get_contents($_FILES['boundary_csv']['tmp_name']);
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
    $first_line = strtok($raw, "\n");
    $delim = (substr_count($first_line, ';') > substr_count($first_line, ',')) ? ';' : ',';
    file_put_contents($_FILES['boundary_csv']['tmp_name'], $raw);

    $handle = fopen($_FILES['boundary_csv']['tmp_name'], 'r');
    $raw_headers = fgetcsv($handle, 0, $delim);
    $headers = array_map(fn($h) => strtolower(trim($h)), $raw_headers);
    $cols = array_flip($headers);

    if (!isset($cols['name']) || !isset($cols['geom'])) {
        echo json_encode(['success'=>false,'error'=>'Missing Name or Geom columns. Found: '.implode(', ',$headers)]);
        fclose($handle); exit;
    }

    // Name → slug conversion
    function name_to_slug(string $name): string {
        $slug = strtolower(trim($name));
        $slug = str_replace(['é','è','ê'], 'e', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }

    // Point-in-polygon
    function pip_nb(float $lat, float $lng, array $poly): bool {
        $n=count($poly); $inside=false; $j=$n-1;
        for ($i=0;$i<$n;$i++) {
            $xi=$poly[$i][0]; $yi=$poly[$i][1]; $xj=$poly[$j][0]; $yj=$poly[$j][1];
            if ((($yi>$lat)!==($yj>$lat)) && ($lng<($xj-$xi)*($lat-$yi)/($yj-$yi)+$xi)) $inside=!$inside;
            $j=$i;
        }
        return $inside;
    }

    // Parse all 22 neighbourhood polygons + compute bboxes
    $neighbourhoods = [];
    while (($row = fgetcsv($handle, 0, $delim)) !== false) {
        $name = trim($row[$cols['name']] ?? '');
        $geom_raw = trim($row[$cols['geom']] ?? '');
        if (!$name || !$geom_raw) continue;

        $geom = json_decode($geom_raw, true);
        if (!$geom || $geom['type'] !== 'Polygon') continue;

        $coords = array_map(fn($c) => [$c[0], $c[1]], $geom['coordinates'][0]);
        $lngs = array_column($coords, 0);
        $lats  = array_column($coords, 1);

        $neighbourhoods[] = [
            'name'   => $name,
            'slug'   => name_to_slug($name),
            'coords' => $coords,
            'minlat' => min($lats) - 0.0002,
            'maxlat' => max($lats) + 0.0002,
            'minlng' => min($lngs) - 0.0002,
            'maxlng' => max($lngs) + 0.0002,
        ];
    }
    fclose($handle);

    if (empty($neighbourhoods)) {
        echo json_encode(['success'=>false,'error'=>'No valid neighbourhood polygons found']); exit;
    }

    // For each neighbourhood, SQL-fetch candidate lots, run pip
    $stmt_upd = $pdo->prepare("UPDATE plex_properties SET neighbourhood_slug=? WHERE pid=?");
    $updated = 0; $unmatched = 0;
    $matched_pids = [];

    foreach ($neighbourhoods as $nb) {
        // SQL bbox pre-filter — only fetch lots in this neighbourhood's area
        $stmt = $pdo->prepare("
            SELECT pid, lat, lng FROM plex_properties
            WHERE lat BETWEEN ? AND ?
              AND lng BETWEEN ? AND ?
              AND lat IS NOT NULL
        ");
        $stmt->execute([$nb['minlat'], $nb['maxlat'], $nb['minlng'], $nb['maxlng']]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($candidates as $lot) {
            if (isset($matched_pids[$lot['pid']])) continue; // already matched
            if (pip_nb((float)$lot['lat'], (float)$lot['lng'], $nb['coords'])) {
                $stmt_upd->execute([$nb['slug'], $lot['pid']]);
                $matched_pids[$lot['pid']] = $nb['slug'];
                $updated++;
            }
        }
    }

    // Count unmatched (lots outside all polygons — rare edge cases)
    $total = (int)$pdo->query("SELECT COUNT(*) FROM plex_properties WHERE lat IS NOT NULL")->fetchColumn();
    $unmatched = $total - $updated;

    echo json_encode([
        'success'      => true,
        'neighbourhoods' => count($neighbourhoods),
        'lots_updated' => $updated,
        'unmatched'    => $unmatched,
        'note'         => "neighbourhood_slug now set to human-readable format (e.g. renfrew-collingwood)",
        'log'          => array_map(fn($nb) => $nb['slug'], $neighbourhoods),
    ]); exit;
}

// Transit import — stops.txt
if (isset($_POST['import_transit']) && isset($_FILES['transit_csv']) && $_FILES['transit_csv']['error']===UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    $handle=fopen($_FILES['transit_csv']['tmp_name'],'r');
    $headers=array_map(fn($h)=>strtolower(trim($h)),fgetcsv($handle));
    $cols=array_flip($headers);
    if(!isset($cols['stop_lat'])||!isset($cols['stop_lon'])){echo json_encode(['success'=>false,'error'=>'Missing stop_lat or stop_lon columns']);fclose($handle);exit;}
    $pdo->exec("TRUNCATE TABLE transit_stops");
    $stmt=$pdo->prepare("INSERT INTO transit_stops (stop_id,stop_name,lat,lng,is_ftn,updated_at) VALUES (?,?,?,?,1,NOW()) ON DUPLICATE KEY UPDATE stop_name=VALUES(stop_name),lat=VALUES(lat),lng=VALUES(lng),updated_at=NOW()");
    $count=0;
    while(($row=fgetcsv($handle))!==false){
        $sid=trim($row[$cols['stop_id']]??'');
        $sname=trim($row[$cols['stop_name']]??'');
        $lat=(float)($row[$cols['stop_lat']]??0);
        $lng=(float)($row[$cols['stop_lon']]??0);
        if(!$sid||!$lat||!$lng)continue;
        $stmt->execute([$sid,$sname,$lat,$lng]);$count++;
    }
    fclose($handle);
    echo json_encode(['success'=>true,'imported'=>$count]); exit;
}

// Lanes import — COV lanes CSV
if (isset($_POST['import_lanes']) && isset($_FILES['lanes_csv']) && $_FILES['lanes_csv']['error']===UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    $raw=file_get_contents($_FILES['lanes_csv']['tmp_name']);
    $rows=json_decode($raw,true);
    if(!$rows) {
        // Try CSV
        $handle=fopen($_FILES['lanes_csv']['tmp_name'],'r');
        $headers=array_map(fn($h)=>strtolower(trim($h)),fgetcsv($handle));
        $cols=array_flip($headers);
        $rows=[];
        while(($row=fgetcsv($handle))!==false){
            $rows[]=['lat'=>$row[$cols['latitude']??$cols['lat']??0]??0,'lng'=>$row[$cols['longitude']??$cols['lng']??0]??0];
        }
        fclose($handle);
    }
    $pdo->exec("TRUNCATE TABLE lane_segments");
    $stmt=$pdo->prepare("INSERT INTO lane_segments (lat,lng,updated_at) VALUES (?,?,NOW())");
    $count=0;
    foreach($rows as $r){
        $lat=(float)($r['lat']??$r['latitude']??0);
        $lng=(float)($r['lng']??$r['longitude']??0);
        if(!$lat||!$lng)continue;
        $stmt->execute([$lat,$lng]);$count++;
    }
    echo json_encode(['success'=>true,'imported'=>$count]); exit;
}

// Permits import — A_Permit_2026 CSV
if (isset($_POST['import_permits']) && isset($_FILES['permits_csv']) && $_FILES['permits_csv']['error']===UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    $handle=fopen($_FILES['permits_csv']['tmp_name'],'r');
    $headers=array_map(fn($h)=>strtolower(trim(str_replace([' ','-'],['_','_'],$h))),fgetcsv($handle));
    $cols=array_flip($headers);
    $stmt=$pdo->prepare("INSERT INTO A_Permit_2026 (permit_number,address,latitude,longitude,permit_type,issue_date,created_at) VALUES (?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE address=VALUES(address),latitude=VALUES(latitude),longitude=VALUES(longitude)");
    $count=0;$skip=0;
    while(($row=fgetcsv($handle))!==false){
        $pnum=trim($row[$cols['permit_number']??$cols['permitnumber']??$cols['permit']??0]??'');
        $addr=trim($row[$cols['address']??$cols['civic_address']??0]??'');
        $lat=(float)($row[$cols['latitude']??$cols['lat']??0]??0);
        $lng=(float)($row[$cols['longitude']??$cols['lon']??$cols['lng']??0]??0);
        $type=trim($row[$cols['permit_type']??$cols['type']??$cols['work_type']??0]??'');
        $date=trim($row[$cols['issue_date']??$cols['issued_date']??$cols['date']??0]??'');
        if(!$addr){$skip++;continue;}
        $stmt->execute([$pnum,$addr,$lat,$lng,$type,$date?:null]);$count++;
    }
    fclose($handle);
    echo json_encode(['success'=>true,'imported'=>$count,'skipped'=>$skip]); exit;
}

// COV property tax CSV import — streams to existing script logic
if (isset($_POST['import_cov_csv']) && isset($_FILES['cov_csv'])) {
    header('Content-Type: application/json');
    $f = $_FILES['cov_csv'];
    if ($f['error'] !== UPLOAD_ERR_OK) { echo json_encode(['success'=>false,'error'=>'Upload error: '.$f['error']]); exit; }
    // Save to data folder then delegate to existing import script
    $dest = __DIR__ . '/data/property-tax-report.csv';
    if (!is_dir(__DIR__.'/data')) mkdir(__DIR__.'/data', 0755, true);
    if (!move_uploaded_file($f['tmp_name'], $dest)) { echo json_encode(['success'=>false,'error'=>'Could not save file to /data/ folder']); exit; }
    // Run import_cov_csv.php as include (it reads from /data/property-tax-report.csv)
    ob_start();
    $imported = 0; $skipped = 0;
    try {
        // Replicate core logic inline — read CSV, insert/update plex_properties
        ini_set('max_execution_time', 300);
        $handle = fopen($dest, 'r');
        $headers = array_map(fn($h)=>strtolower(trim($h)), fgetcsv($handle));
        $cols = array_flip($headers);
        $stmt = $pdo->prepare("INSERT INTO plex_properties (pid,address,zoning,neighbourhood_slug,assessed_land_value,assessed_improvement_value,assessed_total_value,assessment_year) VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE address=VALUES(address),zoning=VALUES(zoning),assessed_land_value=VALUES(assessed_land_value),assessment_year=VALUES(assessment_year)");
        while (($row = fgetcsv($handle)) !== false) {
            $pid = trim($row[$cols['pid']??0]??'');
            if (!$pid) { $skipped++; continue; }
            $pid_fmt = preg_replace('/[^0-9]/','',$pid);
            if (strlen($pid_fmt)===9) $pid_fmt = substr($pid_fmt,0,3).'-'.substr($pid_fmt,3,3).'-'.substr($pid_fmt,6,3);
            $addr = trim($row[$cols['to_civic_number']??$cols['from_civic_number']??0]??'').' '.trim($row[$cols['street_name']??0]??'');
            $zone = trim($row[$cols['zoning_district']??0]??'');
            $nb   = trim($row[$cols['neighbourhood_code']??0]??'');
            $lv   = (int)($row[$cols['current_land_value']??0]??0);
            $iv   = (int)($row[$cols['current_improvement_value']??0]??0);
            $tv   = $lv + $iv;
            $yr   = (int)($row[$cols['tax_assessment_year']??$cols['report_year']??0]??0);
            $stmt->execute([$pid_fmt, trim($addr), $zone, 'nb_'.$nb, $lv, $iv, $tv, $yr?:null]);
            $imported++;
        }
        fclose($handle);
    } catch (Exception $e) { ob_end_clean(); echo json_encode(['success'=>false,'error'=>$e->getMessage()]); exit; }
    ob_end_clean();
    echo json_encode(['success'=>true,'imported'=>$imported,'skipped'=>$skipped]); exit;
}

// COV parcel polygons import
if (isset($_POST['import_parcel_polygons']) && isset($_FILES['parcel_csv'])) {
    header('Content-Type: application/json');
    $f = $_FILES['parcel_csv'];
    if ($f['error'] !== UPLOAD_ERR_OK) { echo json_encode(['success'=>false,'error'=>'Upload error: '.$f['error']]); exit; }
    ini_set('max_execution_time', 300); ini_set('memory_limit','256M');
    $handle = fopen($f['tmp_name'], 'r');
    $headers = array_map(fn($h)=>strtolower(trim($h)), fgetcsv($handle));
    $cols = array_flip($headers);
    $stmt = $pdo->prepare("UPDATE plex_properties SET lat=?,lng=?,lot_area_sqm=?,lot_width_m=? WHERE pid=?");
    $updated=0; $skipped=0;
    while (($row=fgetcsv($handle))!==false) {
        $site_id = preg_replace('/[^0-9]/','',trim($row[$cols['site_id']??0]??''));
        if (!$site_id){$skipped++;continue;}
        $pid = substr($site_id,0,3).'-'.substr($site_id,3,3).'-'.substr($site_id,6,3);
        $geo = json_decode($row[$cols['geo_point_2d']??0]??'{}',true);
        $lat = (float)($geo['lat']??0); $lng = (float)($geo['lon']??0);
        if (!$lat||!$lng){$skipped++;continue;}
        // Derive area from geom if available — fallback to 0
        $area=0; $width=0;
        $geom_raw = $row[$cols['geom']??0]??'';
        if ($geom_raw) {
            $geom=json_decode($geom_raw,true);
            if ($geom&&isset($geom['coordinates'][0])) {
                $pts=$geom['coordinates'][0]; $n=count($pts);
                $a=0;
                for($i=0;$i<$n-1;$i++) $a+=($pts[$i][0]*$pts[$i+1][1]-$pts[$i+1][0]*$pts[$i][1]);
                $area=abs($a/2)*111320*111320*cos(deg2rad($lat));
                // Frontage = min bounding box side
                $lons=array_column($pts,0); $lats_=array_column($pts,1);
                $dlon=(max($lons)-min($lons))*111320*cos(deg2rad($lat));
                $dlat=(max($lats_)-min($lats_))*111320;
                $width=round(min($dlon,$dlat),2);
            }
        }
        $stmt->execute([$lat,$lng,round($area,2),$width,$pid]);
        $updated++;
    }
    fclose($handle);
    echo json_encode(['success'=>true,'imported'=>$updated,'skipped'=>$skipped]); exit;
}

// ── Submission approval actions ───────────────────────────────────────────────
if (isset($_GET['sub_approve'])) {
    $sid = (int)$_GET['sub_approve'];
    $pdo->prepare("UPDATE multi_2025 SET submit_status='approved' WHERE id=?")->execute([$sid]);
    header("Location: admin.php?tab=submissions&msg=" . urlencode("✅ Listing approved and now live."));
    exit;
}
if (isset($_GET['sub_reject'])) {
    $sid = (int)$_GET['sub_reject'];
    $pdo->prepare("UPDATE multi_2025 SET submit_status='rejected' WHERE id=?")->execute([$sid]);
    header("Location: admin.php?tab=submissions&msg=" . urlencode("❌ Listing rejected."));
    exit;
}
if (isset($_GET['sub_pending'])) {
    $sid = (int)$_GET['sub_pending'];
    $pdo->prepare("UPDATE multi_2025 SET submit_status='pending_review' WHERE id=?")->execute([$sid]);
    header("Location: admin.php?tab=submissions&msg=" . urlencode("🔄 Listing reset to pending review."));
    exit;
}

// ── Neighbourhood HPI — Save ──────────────────────────────────────────────────
// ── Neighbourhood Details — Save walkscores, demographics, description ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_nb_details'])) {
    $nb_id = (int)$_POST['neighbourhood_id'];
    $pdo->prepare("
        UPDATE neighbourhoods SET
            description    = :desc,
            walkscore      = :walk,
            transitscore   = :transit,
            bikescore      = :bike,
            population     = :pop,
            median_income  = :income,
            area_sqkm      = :area
        WHERE id = :id
    ")->execute([
        ':desc'   => trim($_POST['nb_description'] ?? '') ?: null,
        ':walk'   => $_POST['nb_walkscore']    !== '' ? (int)$_POST['nb_walkscore']    : null,
        ':transit'=> $_POST['nb_transitscore'] !== '' ? (int)$_POST['nb_transitscore'] : null,
        ':bike'   => $_POST['nb_bikescore']    !== '' ? (int)$_POST['nb_bikescore']    : null,
        ':pop'    => $_POST['nb_population']   !== '' ? (int)str_replace(',','',$_POST['nb_population'])    : null,
        ':income' => $_POST['nb_median_income']!== '' ? (int)str_replace(',','',$_POST['nb_median_income']) : null,
        ':area'   => $_POST['nb_area_sqkm']    !== '' ? (float)$_POST['nb_area_sqkm']  : null,
        ':id'     => $nb_id,
    ]);
    header("Location: admin.php?tab=neighbourhoods&nb_id={$nb_id}&msg=" . urlencode("✅ Neighbourhood details updated."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hpi'])) {
    $nb_id     = (int)$_POST['neighbourhood_id'];
    $month     = $_POST['month_year'] ?? '';
    $month_dt  = $month ? $month . '-01' : null;

    $fields = [
        'avg_price'       => $_POST['avg_price']       !== '' ? (int)str_replace(',','',$_POST['avg_price'])       : null,
        'hpi_benchmark'   => $_POST['hpi_benchmark']   !== '' ? (int)str_replace(',','',$_POST['hpi_benchmark'])   : null,
        'price_detached'  => $_POST['price_detached']  !== '' ? (int)str_replace(',','',$_POST['price_detached'])  : null,
        'price_condo'     => $_POST['price_condo']     !== '' ? (int)str_replace(',','',$_POST['price_condo'])     : null,
        'price_townhouse' => $_POST['price_townhouse'] !== '' ? (int)str_replace(',','',$_POST['price_townhouse']) : null,
        'price_duplex'    => $_POST['price_duplex']    !== '' ? (int)str_replace(',','',$_POST['price_duplex'])    : null,
        'hpi_change_mom'  => $_POST['hpi_change_mom']  !== '' ? (float)$_POST['hpi_change_mom']  : null,
        'hpi_change_yoy'  => $_POST['hpi_change_yoy']  !== '' ? (float)$_POST['hpi_change_yoy']  : null,
    ];

    // Save hero image URL if provided
    $hero_image = trim($_POST['hero_image'] ?? '');
    if ($hero_image !== '') {
        $pdo->prepare("UPDATE neighbourhoods SET hero_image = ? WHERE id = ?")->execute([$hero_image, $nb_id]);
    } elseif (isset($_POST['hero_image_clear'])) {
        $pdo->prepare("UPDATE neighbourhoods SET hero_image = NULL WHERE id = ?")->execute([$nb_id]);
    }

    // 1. Update the live neighbourhoods row (what the page shows)
    $pdo->prepare("
        UPDATE neighbourhoods SET
            avg_price       = :avg_price,
            hpi_benchmark   = :hpi_benchmark,
            price_detached  = :price_detached,
            price_condo     = :price_condo,
            price_townhouse = :price_townhouse,
            price_duplex    = :price_duplex,
            hpi_change_mom  = :hpi_change_mom,
            hpi_change_yoy  = :hpi_change_yoy,
            price_updated_date = CURDATE()
        WHERE id = :id
    ")->execute(array_merge($fields, [':id' => $nb_id]));

    // 2. Upsert into history table (for the 12-month chart)
    if ($month_dt) {
        $exists = $pdo->prepare("SELECT id FROM neighbourhood_hpi_history WHERE neighbourhood_id=? AND month_year=?");
        $exists->execute([$nb_id, $month_dt]);
        if ($exists->fetchColumn()) {
            $pdo->prepare("
                UPDATE neighbourhood_hpi_history SET
                    avg_price=:avg_price, hpi_benchmark=:hpi_benchmark,
                    price_detached=:price_detached, price_condo=:price_condo,
                    price_townhouse=:price_townhouse, price_duplex=:price_duplex,
                    hpi_change_mom=:hpi_change_mom, hpi_change_yoy=:hpi_change_yoy
                WHERE neighbourhood_id=:nb_id AND month_year=:month
            ")->execute(array_merge($fields, [':nb_id'=>$nb_id, ':month'=>$month_dt]));
        } else {
            $pdo->prepare("
                INSERT INTO neighbourhood_hpi_history
                    (neighbourhood_id, month_year, avg_price, hpi_benchmark,
                     price_detached, price_condo, price_townhouse, price_duplex,
                     hpi_change_mom, hpi_change_yoy)
                VALUES (:nb_id,:month,:avg_price,:hpi_benchmark,
                        :price_detached,:price_condo,:price_townhouse,:price_duplex,
                        :hpi_change_mom,:hpi_change_yoy)
            ")->execute(array_merge($fields, [':nb_id'=>$nb_id, ':month'=>$month_dt]));
        }
    }

    header("Location: admin.php?tab=neighbourhoods&nb_id={$nb_id}&msg=" . urlencode("✅ Prices updated for " . date('F Y', strtotime($month_dt))));
    exit;
}

// ── Bulk Market Import — CSV Parse & Preview ──────────────────────────────────
$bulk_preview_rows = [];
$bulk_parse_errors = [];
$bulk_upload_done  = false;
$bulk_results      = [];

// REBGV name → DB slug alias map
// ── REBGV neighbourhood name → DB slug alias map (all areas) ─────────────────
$_rebgv_aliases = [
    // ── Vancouver East ──────────────────────────────────────────────────────
    'champlain heights'=>'champlain-heights',
    'collingwood ve'=>'collingwood',
    'downtown ve'=>'strathcona',
    'fraser ve'=>'fraser-ve',
    'fraserview ve'=>'fraserview-ve',
    'grandview woodland'=>'grandview-woodland',
    'hastings'=>'hastings',
    'hastings sunrise'=>'hastings-sunrise',
    'killarney ve'=>'killarney',
    'knight'=>'knight',
    'main'=>'main',
    'mount pleasant ve'=>'mount-pleasant-ve',
    'renfrew heights'=>'renfrew-heights',
    'renfrew ve'=>'renfrew-east',
    'south marine'=>'south-marine',
    'south vancouver'=>'south-vancouver',
    'strathcona'=>'strathcona',
    'victoria ve'=>'victoria',
    // ── Vancouver West ──────────────────────────────────────────────────────
    'arbutus ridge'=>'arbutus',
    'coal harbour'=>'coal-harbour',
    'downtown vw'=>'downtown-vw',
    'dunbar southlands'=>'dunbar',
    'fairview vw'=>'fairview-vw',
    'false creek'=>'false-creek',
    'kerrisdale'=>'kerrisdale',
    'kitsilano'=>'kitsilano',
    'mackenzie heights'=>'mackenzie-heights',
    'marpole'=>'marpole',
    'mount pleasant vw'=>'mount-pleasant-vw',
    'oakridge'=>'oakridge',
    'point grey'=>'point-grey',
    'quilchena'=>'quilchena',
    'riley park'=>'riley-park',
    'shaughnessy'=>'shaughnessy',
    'south cambie'=>'south-cambie',
    'south granville'=>'south-granville',
    'sw marine'=>'sw-marine',
    'university vw'=>'university',
    'west end vw'=>'west-end',
    'yaletown'=>'yaletown',
    // ── Burnaby ─────────────────────────────────────────────────────────────
    'big bend'=>'big-bend',
    'brentwood park'=>'brentwood',
    'buckingham heights'=>'buckingham-heights',
    'burnaby hospital'=>'burnaby-hospital',
    'burnaby lake'=>'burnaby-lake',
    'capitol hill'=>'capitol-hill-bn',
    'central burnaby'=>'central-bn',
    'deer lake'=>'deer-lake',
    'edmonds be'=>'edmonds-bn',
    'forest glen bs'=>'forest-glen-bn',
    'government road'=>'government-road',
    'highgate'=>'highgate',
    'metrotown'=>'metrotown',
    'montecito'=>'montecito',
    'parkcrest'=>'parkcrest',
    'renfrew heights bn'=>'renfrew-heights-bn',
    'south slope'=>'south-slope',
    'sperling duthie'=>'sperling-duthie',
    'sullivan heights'=>'sullivan-heights',
    'upper deer lake'=>'upper-deer-lake',
    'willingdon heights'=>'willingdon-heights',
    // ── Richmond ────────────────────────────────────────────────────────────
    'brighouse'=>'brighouse',
    'brighouse south'=>'brighouse-south',
    'broadmoor'=>'broadmoor',
    'east cambie'=>'east-cambie',
    'garden city'=>'garden-city',
    'east richmond'          => 'east-richmond-ri',
    'gilmore'=>'gilmore-ri',
    'hamilton ri'=>'hamilton-ri',
    'ironwood'=>'ironwood',
    'lackner'=>'lackner',
    'mclennan'=>'mclennan',
    'mclennan north'=>'mclennan-north',
    'panorama ridge'=>'panorama-village',
    'quilchena ri'=>'quilchena-ri',
    'riverdale ri'=>'riverdale-ri',
    'seafair'=>'seafair',
    'steveston north'=>'steveston-north',
    'steveston south'=>'steveston-south',
    'steveston village'=>'steveston-village',
    'terra nova'=>'terra-nova',
    'thompson ri'=>'thompson-ri',
    'woodwards'=>'woodwards-ri',
    // ── North Vancouver ─────────────────────────────────────────────────────
    'blueridge nv'=>'blueridge-nv',
    'boulevard'=>'boulevard',
    'canyon heights nv'=>'canyon-heights-nv',
    'capilano highlands'=>'capilano-highlands',
    'central lonsdale'=>'central-lonsdale',
    'deep cove'=>'deep-cove',
    'dollarton'=>'dollarton',
    'edgemont'=>'edgemont',
    'grouse woods'=>'grouse-woods',
    'indian river'=>'indian-river',
    'lower lonsdale'=>'lower-lonsdale',
    'lyndwood'=>'lyndwood',
    'lynn valley'=>'lynn-valley',
    'lynnmour'=>'lynnmour',
    'moodyville'=>'moodyville',
    'pemberton heights'=>'pemberton-heights',
    'princess park'=>'princess-park',
    'queensbury'=>'queensbury',
    'seymour nv'=>'seymour',
    'upper lonsdale'=>'upper-lonsdale',
    // ── West Vancouver ──────────────────────────────────────────────────────
    'altamont'=>'altamont',
    'ambleside'=>'ambleside-wv',
    'bayridge'=>'bayridge',
    'british properties'=>'british-properties',
    'caulfeild'=>'caulfeild',
    'cedardale'=>'cedardale',
    'chartwell'=>'chartwell',
    'cypress park estates'=>'cypress-park-estates',
    'dundarave'=>'dundarave',
    'eagle harbour'=>'eagle-harbour',
    'furry creek'=>'furry-creek',
    'gleneagles'=>'gleneagles',
    'horseshoe bay wv'=>'horseshoe-bay',
    'lions bay'=>'lions-bay',
    'rockridge'=>'rockridge',
    'sentinel hill'=>'sentinel-hill',
    'upper caulfeild'=>'upper-caulfeild',
    'west bay'=>'west-bay-wv',
    'westmount wv'=>'westmount-wv',
    // ── Coquitlam ───────────────────────────────────────────────────────────
    'burke mountain'=>'burke-mountain',
    'cape horn'=>'cape-horn',
    'central coquitlam'=>'central-coq',
    'coquitlam east'=>'coquitlam-east',
    'coquitlam west'=>'coquitlam-west',
    'eagle ridge coq'=>'eagle-ridge-coq',
    'harbour chines'=>'harbour-chines',
    'maillardville'=>'maillardville',
    'mountain meadows'=>'mountain-meadows-coq',
    'north coquitlam'=>'north-coq',
    'ranch park'=>'ranch-park-coq',
    'river springs'=>'river-springs-coq',
    'scott creek'=>'scott-creek-coq',
    'westwood plateau'=>'westwood-plateau',
    // ── Port Coquitlam ──────────────────────────────────────────────────────
    'birchland manor'=>'birchland-manor',
    'central port coquitlam'=>'central-port-coquitlam',
    'citadel poc'=>'citadel-poc',
    'glenwood poc'=>'glenwood-poc',
    'lincoln park poc'=>'lincoln-park-poc',
    'lower mary hill'=>'lower-mary-hill',
    'mary hill'=>'mary-hill',
    'oxford heights'=>'oxford-heights',
    'riverwood'=>'riverwood',
    'woodland acres poc'=>'woodland-acres-poc',
    // ── Port Moody ──────────────────────────────────────────────────────────
    'anmore'=>'anmore',
    'barber street'=>'barber-street-pm',
    'belcarra'=>'belcarra',
    'college park pm'=>'college-park-pm',
    'glenayre'=>'glenayre-pm',
    'heritage mountain'=>'heritage-mountain-pm',
    'heritage woods pm'=>'heritage-woods-pm',
    'ioco'=>'ioco',
    'mountain meadows pm'=>'mountain-meadows-pm',
    'north shore pm'=>'north-shore-pm',
    'port moody centre'=>'port-moody-centre',
];

// ── Per-area REBGV neighbourhood name lists (for templates) ──────────────────
$_rebgv_area_rows = [
    'vancouver-east' => [
        ['Champlain Heights','','','',''],['Collingwood VE','','','',''],
        ['Downtown VE','','','',''],['Fraser VE','','','',''],
        ['Fraserview VE','','','',''],['Grandview Woodland','','','',''],
        ['Hastings','','','',''],['Hastings Sunrise','','','',''],
        ['Killarney VE','','','',''],['Knight','','','',''],
        ['Main','','','',''],['Mount Pleasant VE','','','',''],
        ['Renfrew Heights','','','',''],['Renfrew VE','','','',''],
        ['South Marine','','','',''],['South Vancouver','','','',''],
        ['Strathcona','','','',''],['Victoria VE','','','',''],
    ],
    'vancouver-west' => [
        ['Arbutus Ridge','','','',''],['Coal Harbour','','','',''],
        ['Downtown VW','','','',''],['Dunbar Southlands','','','',''],
        ['Fairview VW','','','',''],['False Creek','','','',''],
        ['Kerrisdale','','','',''],['Kitsilano','','','',''],
        ['MacKenzie Heights','','','',''],['Marpole','','','',''],
        ['Mount Pleasant VW','','','',''],['Oakridge','','','',''],
        ['Point Grey','','','',''],['Quilchena','','','',''],
        ['Riley Park','','','',''],['Shaughnessy','','','',''],
        ['South Cambie','','','',''],['South Granville','','','',''],
        ['SW Marine','','','',''],['University VW','','','',''],
        ['West End VW','','','',''],['Yaletown','','','',''],
    ],
    'burnaby-north' => [
        ['Brentwood Park','','','',''],['Burnaby Lake','','','',''],
        ['Capitol Hill','','','',''],['Central Burnaby','','','',''],
        ['Government Road','','','',''],['Montecito','','','',''],
        ['Parkcrest','','','',''],['Sperling Duthie','','','',''],
        ['Sullivan Heights','','','',''],['Willingdon Heights','','','',''],
    ],
    'burnaby-east' => [
        ['Big Bend','','','',''],['Burnaby Hospital','','','',''],
        ['Forest Glen BS','','','',''],['Highgate','','','',''],
        ['South Slope','','','',''],['Upper Deer Lake','','','',''],
    ],
    'burnaby-south' => [
        ['Buckingham Heights','','','',''],['Deer Lake','','','',''],
        ['Edmonds BE','','','',''],['Metrotown','','','',''],
        ['Renfrew Heights BN','','','',''],
    ],
    'richmond' => [
        ['Brighouse','','','',''],['Brighouse South','','','',''],
        ['Broadmoor','','','',''],['East Cambie','','','',''],
        ['Garden City','','','',''],['Gilmore','','','',''],
        ['Hamilton RI','','','',''],['Ironwood','','','',''],
        ['Lackner','','','',''],['McLennan','','','',''],
        ['McLennan North','','','',''],['Panorama Ridge','','','',''],
        ['Quilchena RI','','','',''],['Riverdale RI','','','',''],
        ['Seafair','','','',''],['Steveston North','','','',''],
        ['Steveston South','','','',''],['Steveston Village','','','',''],
        ['Terra Nova','','','',''],['Thompson RI','','','',''],
        ['Woodwards','','','',''],
    ],
    'north-vancouver' => [
        ['Blueridge NV','','','',''],['Boulevard','','','',''],
        ['Canyon Heights NV','','','',''],['Capilano Highlands','','','',''],
        ['Central Lonsdale','','','',''],['Deep Cove','','','',''],
        ['Dollarton','','','',''],['Edgemont','','','',''],
        ['Grouse Woods','','','',''],['Indian River','','','',''],
        ['Lower Lonsdale','','','',''],['Lyndwood','','','',''],
        ['Lynn Valley','','','',''],['Lynnmour','','','',''],
        ['Moodyville','','','',''],['Pemberton Heights','','','',''],
        ['Princess Park','','','',''],['Queensbury','','','',''],
        ['Seymour NV','','','',''],['Upper Lonsdale','','','',''],
    ],
    'west-vancouver' => [
        ['Altamont','','','',''],['Ambleside','','','',''],
        ['Bayridge','','','',''],['British Properties','','','',''],
        ['Caulfeild','','','',''],['Cedardale','','','',''],
        ['Chartwell','','','',''],['Cypress Park Estates','','','',''],
        ['Dundarave','','','',''],['Eagle Harbour','','','',''],
        ['Furry Creek','','','',''],['Gleneagles','','','',''],
        ['Horseshoe Bay WV','','','',''],['Lions Bay','','','',''],
        ['Rockridge','','','',''],['Sentinel Hill','','','',''],
        ['Upper Caulfeild','','','',''],['West Bay','','','',''],
        ['Westmount WV','','','',''],
    ],
    'coquitlam' => [
        ['Burke Mountain','','','',''],['Cape Horn','','','',''],
        ['Central Coquitlam','','','',''],['Coquitlam East','','','',''],
        ['Coquitlam West','','','',''],['Eagle Ridge Coq','','','',''],
        ['Harbour Chines','','','',''],['Maillardville','','','',''],
        ['Mountain Meadows','','','',''],['North Coquitlam','','','',''],
        ['Westwood Plateau','','','',''],
    ],
    'port-coquitlam' => [
        ['Birchland Manor','','','',''],['Central Port Coquitlam','','','',''],
        ['Citadel PoC','','','',''],['Glenwood PoC','','','',''],
        ['Lincoln Park PoC','','','',''],['Lower Mary Hill','','','',''],
        ['Mary Hill','','','',''],['Oxford Heights','','','',''],
        ['Riverwood','','','',''],['Woodland Acres PoC','','','',''],
    ],
    'port-moody' => [
        ['Anmore','','','',''],['Barber Street','','','',''],
        ['Belcarra','','','',''],['College Park PM','','','',''],
        ['Glenayre','','','',''],['Heritage Mountain','','','',''],
        ['Heritage Woods PM','','','',''],['Ioco','','','',''],
        ['Mountain Meadows PM','','','',''],['North Shore PM','','','',''],
        ['Port Moody Centre','','','',''],
    ],
];

function _bulk_fuzzy_match(string $input, array $lookup, array $aliases): ?array {
    $lc = strtolower(trim($input));
    if (isset($lookup[$lc])) return $lookup[$lc];
    if (isset($aliases[$lc]) && isset($lookup[$aliases[$lc]])) return $lookup[$aliases[$lc]];
    foreach ($lookup as $key => $nb) {
        if (str_contains($key, $lc) || str_contains($lc, $key)) return $nb;
    }
    $best = null; $bestDist = 99;
    foreach ($lookup as $key => $nb) {
        $d = levenshtein($lc, $key);
        if ($d < $bestDist && $d <= 4) { $bestDist = $d; $best = $nb; }
    }
    return $best;
}

// Download CSV template — area-specific
if (isset($_GET['bulk_template'])) {
    $area_key  = preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['bulk_template']));
    $area_rows = $_rebgv_area_rows[$area_key] ?? $_rebgv_area_rows['vancouver-east'];
    $area_label = str_replace('-', '_', $area_key);
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"rebgv_import_{$area_label}.csv\"");
    $hdr = ['month_year','neighbourhood_rebgv','price_detached','price_duplex','price_condo','price_townhouse','yoy_detached','yoy_duplex','yoy_condo','yoy_townhouse','sales_detached','sales_duplex','sales_condo','sales_townhouse','dom_detached','dom_duplex','dom_condo','dom_townhouse'];
    $out = fopen('php://output', 'w');
    fputcsv($out, $hdr);
    $mo = date('Y-m');
    foreach ($area_rows as $r) {
        fputcsv($out, array_merge([$mo, $r[0]], array_fill(0, 16, '')));
    }
    fclose($out);
    exit;
}

// CSV Upload → parse & preview
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_upload_csv']) && isset($_FILES['bulk_csv'])) {
    $f = $_FILES['bulk_csv'];
    if ($f['error'] === UPLOAD_ERR_OK && strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) === 'csv') {
        // Build neighbourhood lookup by name and slug
        $all_nbs_for_bulk = $pdo->query("SELECT id, name, slug, area FROM neighbourhoods WHERE is_active=1 ORDER BY area, name")->fetchAll(PDO::FETCH_ASSOC);
        $nb_lkp = [];
        foreach ($all_nbs_for_bulk as $nb) {
            $nb_lkp[strtolower($nb['name'])] = $nb;
            $nb_lkp[strtolower($nb['slug'])] = $nb;
        }

        // ── Pass 1: read TOTAL* row to get area-level DOM fallbacks ──────────
        $dom_fallback = ['dom_detached'=>null,'dom_duplex'=>null,'dom_condo'=>null,'dom_townhouse'=>null];
        $handle = fopen($f['tmp_name'], 'r');
        $header = fgetcsv($handle);
        $header = array_map(fn($h) => strtolower(trim($h)), $header);
        $col    = array_flip($header);
        while (($row = fgetcsv($handle)) !== false) {
            $nb_name = trim($row[$col['neighbourhood_rebgv'] ?? 1] ?? '');
            if (stripos($nb_name, 'TOTAL') === false) continue;
            $gf = fn($k) => isset($col[$k]) && isset($row[$col[$k]]) ? trim(str_replace(['$',',','+','−','–'], ['','','','-','-'], $row[$col[$k]])) : '';
            $dom_fallback['dom_detached']  = $gf('dom_detached')  !== '' ? (int)$gf('dom_detached')  : null;
            $dom_fallback['dom_duplex']    = $gf('dom_duplex')    !== '' ? (int)$gf('dom_duplex')    : null;
            $dom_fallback['dom_condo']     = $gf('dom_condo')     !== '' ? (int)$gf('dom_condo')     : null;
            $dom_fallback['dom_townhouse'] = $gf('dom_townhouse') !== '' ? (int)$gf('dom_townhouse') : null;
            break; // only need the first TOTAL row
        }
        fclose($handle);

        // ── Pass 2: parse neighbourhood rows, using TOTAL* DOM as fallback ───
        $handle = fopen($f['tmp_name'], 'r');
        fgetcsv($handle); // skip header
        $rn = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rn++;
            if (count($row) < 2) continue;
            $month_raw = trim($row[$col['month_year'] ?? 0] ?? '');
            $nb_name   = trim($row[$col['neighbourhood_rebgv'] ?? 1] ?? '');
            if (!$month_raw || !$nb_name) continue;

            $month_dt = null;
            if (preg_match('/^(\d{4})-(\d{2})$/', $month_raw)) {
                $month_dt = $month_raw . '-01';
            } elseif ($ts = strtotime('01 ' . $month_raw)) {
                $month_dt = date('Y-m-01', $ts);
            }
            if (!$month_dt) { $bulk_parse_errors[] = "Row {$rn}: bad month '{$month_raw}'"; continue; }

            $g = fn($k) => isset($col[$k]) && isset($row[$col[$k]]) ? trim(str_replace(['$',',','+','−','–'], ['','','','-','-'], $row[$col[$k]])) : '';

            // Skip summary/total rows
            if (stripos($nb_name, 'TOTAL') !== false) continue;

            $matched = _bulk_fuzzy_match($nb_name, $nb_lkp, $_rebgv_aliases);
            $pd   = $g('price_detached')  !== '' ? (int)$g('price_detached')  : null;
            $pdup = $g('price_duplex')    !== '' ? (int)$g('price_duplex')    : null;
            $pc   = $g('price_condo')     !== '' ? (int)$g('price_condo')     : null;
            $pt   = $g('price_townhouse') !== '' ? (int)$g('price_townhouse') : null;

            // DOM: use row value if present, fall back to TOTAL* area average
            $dom_d  = $g('dom_detached')  !== '' ? (int)$g('dom_detached')  : $dom_fallback['dom_detached'];
            $dom_du = $g('dom_duplex')    !== '' ? (int)$g('dom_duplex')    : $dom_fallback['dom_duplex'];
            $dom_c  = $g('dom_condo')     !== '' ? (int)$g('dom_condo')     : $dom_fallback['dom_condo'];
            $dom_t  = $g('dom_townhouse') !== '' ? (int)$g('dom_townhouse') : $dom_fallback['dom_townhouse'];

            $bulk_preview_rows[] = [
                'month_dt'        => $month_dt,
                'nb_name_rebgv'   => $nb_name,
                'price_detached'  => $pd,
                'price_duplex'    => $pdup,
                'price_condo'     => $pc,
                'price_townhouse' => $pt,
                'yoy_detached'    => $g('yoy_detached')    !== '' ? (float)$g('yoy_detached')    : null,
                'yoy_duplex'      => $g('yoy_duplex')      !== '' ? (float)$g('yoy_duplex')      : null,
                'yoy_condo'       => $g('yoy_condo')       !== '' ? (float)$g('yoy_condo')       : null,
                'yoy_townhouse'   => $g('yoy_townhouse')   !== '' ? (float)$g('yoy_townhouse')   : null,
                'sales_detached'  => $g('sales_detached')  !== '' ? (int)$g('sales_detached')    : null,
                'sales_duplex'    => $g('sales_duplex')    !== '' ? (int)$g('sales_duplex')      : null,
                'sales_condo'     => $g('sales_condo')     !== '' ? (int)$g('sales_condo')       : null,
                'sales_townhouse' => $g('sales_townhouse') !== '' ? (int)$g('sales_townhouse')   : null,
                'dom_detached'    => $dom_d,
                'dom_duplex'      => $dom_du,
                'dom_condo'       => $dom_c,
                'dom_townhouse'   => $dom_t,
                'dom_from_total'  => ($g('dom_detached') === '' || $g('dom_condo') === '' || $g('dom_townhouse') === ''),
                'hpi_benchmark'   => $pd ?? $pt ?? $pdup ?? $pc,
                'hpi_yoy'         => $g('yoy_detached') !== '' ? (float)$g('yoy_detached')
                                   : ($g('yoy_townhouse') !== '' ? (float)$g('yoy_townhouse')
                                   : ($g('yoy_duplex') !== '' ? (float)$g('yoy_duplex')
                                   : ($g('yoy_condo') !== '' ? (float)$g('yoy_condo') : null))),
                'matched'         => $matched,
                'match_status'    => $matched ? 'matched' : 'unmatched',
            ];
        }
        fclose($handle);
        $bulk_upload_done = true;
    } else {
        $bulk_parse_errors[] = 'Please upload a valid .csv file.';
    }
}

// Confirmed bulk save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_confirm_save'])) {
    $rows_data  = json_decode($_POST['bulk_rows_json'] ?? '[]', true);
    $month_dt   = $_POST['bulk_month'] ?? '';
    $saved = $skipped = $errors = 0;

    foreach ($rows_data as $r) {
        if (empty($r['nb_id'])) { $skipped++; continue; }
        $nb_id = (int)$r['nb_id'];
        $pd    = isset($r['price_detached'])  && $r['price_detached']  !== '' ? (int)$r['price_detached']  : null;
        $pdup  = isset($r['price_duplex'])    && $r['price_duplex']    !== '' ? (int)$r['price_duplex']    : null;
        $pc    = isset($r['price_condo'])     && $r['price_condo']     !== '' ? (int)$r['price_condo']     : null;
        $pt    = isset($r['price_townhouse']) && $r['price_townhouse'] !== '' ? (int)$r['price_townhouse'] : null;
        $hb    = isset($r['hpi_benchmark'])   && $r['hpi_benchmark']   !== '' ? (int)$r['hpi_benchmark']  : null;
        $yy    = isset($r['hpi_yoy'])         && $r['hpi_yoy']         !== '' ? (float)$r['hpi_yoy']      : null;
        $dd    = isset($r['dom_detached'])    && $r['dom_detached']    !== '' ? (int)$r['dom_detached']    : null;
        $ddup  = isset($r['dom_duplex'])      && $r['dom_duplex']      !== '' ? (int)$r['dom_duplex']      : null;
        $dc    = isset($r['dom_condo'])       && $r['dom_condo']       !== '' ? (int)$r['dom_condo']       : null;
        $dt    = isset($r['dom_townhouse'])   && $r['dom_townhouse']   !== '' ? (int)$r['dom_townhouse']   : null;
        $sd    = isset($r['sales_detached'])  && $r['sales_detached']  !== '' ? (int)$r['sales_detached']  : null;
        $sdup  = isset($r['sales_duplex'])    && $r['sales_duplex']    !== '' ? (int)$r['sales_duplex']    : null;
        $sc    = isset($r['sales_condo'])     && $r['sales_condo']     !== '' ? (int)$r['sales_condo']     : null;
        $st    = isset($r['sales_townhouse']) && $r['sales_townhouse'] !== '' ? (int)$r['sales_townhouse'] : null;

        try {
            $pdo->prepare("
                UPDATE neighbourhoods SET
                    price_detached  = COALESCE(:pd,   price_detached),
                    price_duplex    = COALESCE(:pdup, price_duplex),
                    price_condo     = COALESCE(:pc,   price_condo),
                    price_townhouse = COALESCE(:pt,   price_townhouse),
                    hpi_benchmark   = COALESCE(:hb,   hpi_benchmark),
                    hpi_change_yoy  = COALESCE(:yy,   hpi_change_yoy),
                    price_updated_date = CURDATE()
                WHERE id = :id
            ")->execute([':pd'=>$pd,':pdup'=>$pdup,':pc'=>$pc,':pt'=>$pt,':hb'=>$hb,':yy'=>$yy,':id'=>$nb_id]);

            if ($month_dt) {
                $avg = 0; $cnt = 0;
                foreach ([$pd,$pdup,$pc,$pt] as $v) { if ($v) { $avg += $v; $cnt++; } }
                $avg = $cnt ? (int)($avg / $cnt) : null;

                $ex = $pdo->prepare("SELECT id FROM neighbourhood_hpi_history WHERE neighbourhood_id=? AND month_year=?");
                $ex->execute([$nb_id, $month_dt]);
                if ($ex->fetchColumn()) {
                    $pdo->prepare("
                        UPDATE neighbourhood_hpi_history SET
                            avg_price         = COALESCE(:avg,  avg_price),
                            hpi_benchmark     = COALESCE(:hb,   hpi_benchmark),
                            price_detached    = COALESCE(:pd,   price_detached),
                            price_duplex      = COALESCE(:pdup, price_duplex),
                            price_condo       = COALESCE(:pc,   price_condo),
                            price_townhouse   = COALESCE(:pt,   price_townhouse),
                            hpi_change_yoy    = COALESCE(:yy,   hpi_change_yoy),
                            dom_detached      = :dd,
                            dom_duplex        = :ddup,
                            dom_condo         = :dc,
                            dom_townhouse     = :dt,
                            sales_detached    = COALESCE(:sd,   sales_detached),
                            sales_duplex      = COALESCE(:sdup, sales_duplex),
                            sales_condo       = COALESCE(:sc,   sales_condo),
                            sales_townhouse   = COALESCE(:st,   sales_townhouse)
                        WHERE neighbourhood_id=:nb_id AND month_year=:mo
                    ")->execute([
                        ':avg'=>$avg,':hb'=>$hb,':pd'=>$pd,':pdup'=>$pdup,':pc'=>$pc,':pt'=>$pt,':yy'=>$yy,
                        ':dd'=>$dd,':ddup'=>$ddup,':dc'=>$dc,':dt'=>$dt,
                        ':sd'=>$sd,':sdup'=>$sdup,':sc'=>$sc,':st'=>$st,
                        ':nb_id'=>$nb_id,':mo'=>$month_dt
                    ]);
                } else {
                    $pdo->prepare("
                        INSERT INTO neighbourhood_hpi_history
                            (neighbourhood_id, month_year, avg_price, hpi_benchmark,
                             price_detached, price_duplex, price_condo, price_townhouse, hpi_change_yoy,
                             dom_detached, dom_duplex, dom_condo, dom_townhouse,
                             sales_detached, sales_duplex, sales_condo, sales_townhouse)
                        VALUES
                            (:nb_id, :mo, :avg, :hb,
                             :pd, :pdup, :pc, :pt, :yy,
                             :dd, :ddup, :dc, :dt,
                             :sd, :sdup, :sc, :st)
                    ")->execute([
                        ':nb_id'=>$nb_id,':mo'=>$month_dt,':avg'=>$avg,':hb'=>$hb,
                        ':pd'=>$pd,':pdup'=>$pdup,':pc'=>$pc,':pt'=>$pt,':yy'=>$yy,
                        ':dd'=>$dd,':ddup'=>$ddup,':dc'=>$dc,':dt'=>$dt,
                        ':sd'=>$sd,':sdup'=>$sdup,':sc'=>$sc,':st'=>$st
                    ]);
                }
            }
            $saved++;
        } catch (Exception $e) { $errors++; }
    }

    $bulk_results = ['saved'=>$saved,'skipped'=>$skipped,'errors'=>$errors,'month'=>$month_dt];
    if (!$errors) {
        header("Location: admin.php?tab=neighbourhoods&subtab=import&msg=" . urlencode("✅ Bulk import complete — {$saved} neighbourhoods updated for " . date('F Y', strtotime($month_dt))));
        exit;
    }
}

// ── Neighbourhood — Add New ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_new_neighbourhood'])) {
    // Auto-generate slug from name
    $name = trim($_POST['nb_name'] ?? '');
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    // Check slug unique — append -2, -3 if needed
    $base_slug = $slug;
    $i = 2;
    while ($pdo->prepare("SELECT id FROM neighbourhoods WHERE slug=?")->execute([$slug]) &&
           $pdo->prepare("SELECT id FROM neighbourhoods WHERE slug=?")->execute([$slug]) &&
           $pdo->query("SELECT COUNT(*) FROM neighbourhoods WHERE slug='$slug'")->fetchColumn() > 0) {
        $slug = $base_slug . '-' . $i++;
    }

    $pdo->prepare("
        INSERT INTO neighbourhoods
            (slug, name, area, db_neighborhood,
             lat_min, lat_max, lng_min, lng_max,
             description, walkscore, transitscore, bikescore,
             population, median_income, area_sqkm,
             sort_order, is_active)
        VALUES
            (:slug,:name,:area,:db_neighborhood,
             :lat_min,:lat_max,:lng_min,:lng_max,
             :description,:walkscore,:transitscore,:bikescore,
             :population,:median_income,:area_sqkm,
             :sort_order, 1)
    ")->execute([
        ':slug'            => $slug,
        ':name'            => $name,
        ':area'            => trim($_POST['nb_area']            ?? ''),
        ':db_neighborhood' => trim($_POST['nb_db_neighborhood'] ?? ''),
        ':lat_min'         => $_POST['nb_lat_min'] !== '' ? (float)$_POST['nb_lat_min'] : null,
        ':lat_max'         => $_POST['nb_lat_max'] !== '' ? (float)$_POST['nb_lat_max'] : null,
        ':lng_min'         => $_POST['nb_lng_min'] !== '' ? (float)$_POST['nb_lng_min'] : null,
        ':lng_max'         => $_POST['nb_lng_max'] !== '' ? (float)$_POST['nb_lng_max'] : null,
        ':description'     => trim($_POST['nb_description']     ?? ''),
        ':walkscore'       => $_POST['nb_walkscore']   !== '' ? (int)$_POST['nb_walkscore']   : null,
        ':transitscore'    => $_POST['nb_transitscore'] !== '' ? (int)$_POST['nb_transitscore'] : null,
        ':bikescore'       => $_POST['nb_bikescore']   !== '' ? (int)$_POST['nb_bikescore']   : null,
        ':population'      => $_POST['nb_population']  !== '' ? (int)str_replace(',','',$_POST['nb_population'])  : null,
        ':median_income'   => $_POST['nb_median_income'] !== '' ? (int)str_replace(',','',$_POST['nb_median_income']) : null,
        ':area_sqkm'       => $_POST['nb_area_sqkm']   !== '' ? (float)$_POST['nb_area_sqkm'] : null,
        ':sort_order'      => $_POST['nb_sort_order']  !== '' ? (int)$_POST['nb_sort_order']  : 99,
    ]);

    $new_id = $pdo->lastInsertId();
    header("Location: admin.php?tab=neighbourhoods&nb_id={$new_id}&msg=" . urlencode("✅ '{$name}' added! Now enter its monthly HPI data below."));
    exit;
}

// ── Neighbourhood — Delete ─────────────────────────────────────────────────────
if (isset($_GET['nb_delete'])) {
    $del_id = (int)$_GET['nb_delete'];
    $pdo->prepare("DELETE FROM neighbourhood_hpi_history WHERE neighbourhood_id=?")->execute([$del_id]);
    $pdo->prepare("DELETE FROM neighbourhoods WHERE id=?")->execute([$del_id]);
    header("Location: admin.php?tab=neighbourhoods&msg=" . urlencode("🗑️ Neighbourhood deleted."));
    exit;
}

// ── Community Events — Save ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_event'])) {
    $ev_id = (int)($_POST['event_id'] ?? 0);
    $fields = [
        'title'           => trim($_POST['ev_title']       ?? ''),
        'description'     => trim($_POST['ev_description'] ?? ''),
        'event_date'      => $_POST['ev_date']             ?? '',
        'event_end_date'  => !empty($_POST['ev_end_date'])  ? $_POST['ev_end_date']  : null,
        'event_time'      => trim($_POST['ev_time']        ?? ''),
        'url'             => trim($_POST['ev_url']         ?? ''),
        'location_name'   => trim($_POST['ev_location']    ?? ''),
        'city'            => $_POST['ev_city']             ?? 'Vancouver',
        'neighbourhood_id'=> !empty($_POST['ev_nb_id'])    ? (int)$_POST['ev_nb_id'] : null,
        'category'        => $_POST['ev_category']         ?? 'community',
        'source'          => 'manual',
        'is_active'       => 1,
    ];
    if ($ev_id > 0) {
        $set = implode(', ', array_map(fn($k) => "`$k`=:$k", array_keys($fields)));
        $params = array_combine(array_map(fn($k) => ":$k", array_keys($fields)), array_values($fields));
        $params[':id'] = $ev_id;
        $pdo->prepare("UPDATE community_events SET $set WHERE id=:id")->execute($params);
        $msg = "✅ Event updated.";
    } else {
        $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($fields)));
        $vals = implode(', ', array_map(fn($k) => ":$k", array_keys($fields)));
        $params = array_combine(array_map(fn($k) => ":$k", array_keys($fields)), array_values($fields));
        $pdo->prepare("INSERT INTO community_events ($cols) VALUES ($vals)")->execute($params);
        $msg = "✅ Event added.";
    }
    header("Location: admin.php?tab=events&ev_city=" . urlencode($fields['city']) . "&msg=" . urlencode($msg));
    exit;
}

// ── Community Events — Delete ─────────────────────────────────────────────────
if (isset($_GET['ev_delete'])) {
    $pdo->prepare("DELETE FROM community_events WHERE id=?")->execute([(int)$_GET['ev_delete']]);
    header("Location: admin.php?tab=events&msg=" . urlencode("🗑️ Event deleted."));
    exit;
}

// ── Community Events — Toggle active ─────────────────────────────────────────
if (isset($_GET['ev_toggle'])) {
    $pdo->prepare("UPDATE community_events SET is_active = 1 - is_active WHERE id=?")->execute([(int)$_GET['ev_toggle']]);
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'admin.php?tab=events'));
    exit;
}
try {
    $developers = $pdo->query("SELECT * FROM developers ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
$pending_devs = count(array_filter($developers, function($d) { return $d['status'] === 'pending'; }));

// ── Pending submissions count (for tab badge) ─────────────────────────────────
$pending_submissions = 0;
try {
    $ps = $pdo->query("SELECT COUNT(*) FROM multi_2025 WHERE submit_status='pending_review' AND submitted_by IS NOT NULL");
    $pending_submissions = (int)$ps->fetchColumn();
} catch (Exception $e) {}

// ── Check which columns exist (do this once, early) ──────────────────────────
$existing_cols = $pdo->query("DESCRIBE multi_2025")->fetchAll(PDO::FETCH_COLUMN);
function col_exists($col, $existing_cols) { return in_array($col, $existing_cols); }

// ── Auto-add new columns if they don't exist yet ─────────────────────────────
$new_columns = [
    'developer_name'     => "ALTER TABLE multi_2025 ADD COLUMN developer_name VARCHAR(255) DEFAULT '' AFTER builder_logo",
    'virtual_tour_url'   => "ALTER TABLE multi_2025 ADD COLUMN virtual_tour_url VARCHAR(1024) DEFAULT '' AFTER video_url",
    'developer_bio'      => "ALTER TABLE multi_2025 ADD COLUMN developer_bio TEXT AFTER developer_name",
    'community_features' => "ALTER TABLE multi_2025 ADD COLUMN community_features TEXT AFTER features",
];
foreach ($new_columns as $col => $sql) {
    if (!in_array($col, $existing_cols)) {
        try { $pdo->exec($sql); $existing_cols[] = $col; } catch (Exception $e) { /* ignore if already exists */ }
    }
}

// ── Load property for edit ────────────────────────────────────────────────────
$edit_property = null;
// $edit_id may already be set from the save handler above
if (!isset($edit_id)) {
    $edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
} else {
    // After a save, also check GET in case both are set
    if (isset($_GET['edit'])) $edit_id = (int)$_GET['edit'];
}

if ($edit_id > 0) {
    $s = $pdo->prepare("SELECT * FROM multi_2025 WHERE id = :id LIMIT 1");
    $s->execute([':id' => $edit_id]);
    $edit_property = $s->fetch(PDO::FETCH_ASSOC);
}

// ── Load all listings ─────────────────────────────────────────────────────────
$search       = $_GET['search'] ?? '';
$list_sql     = "SELECT * FROM multi_2025";
$list_params  = [];
if (!empty($search)) {
    $list_sql .= " WHERE address LIKE :s OR neighborhood LIKE :s";
    $list_params[':s'] = "%$search%";
}
$list_sql .= " ORDER BY id DESC";
$stmt     = $pdo->prepare($list_sql);
$stmt->execute($list_params);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Load neighbourhoods (for HPI tab) ────────────────────────────────────────
$neighbourhoods = [];
try {
    $neighbourhoods = $pdo->query("SELECT * FROM neighbourhoods WHERE is_active=1 ORDER BY area, sort_order")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
$nb_selected_id = (int)($_GET['nb_id'] ?? ($_POST['neighbourhood_id'] ?? 0));
$nb_is_new      = ($_GET['nb_id'] ?? '') === 'new';
$nb_selected    = null;
$nb_history     = [];

// Distinct neighborhood values from multi_2025 for the dropdown helper
$distinct_neighborhoods = [];
try {
    $distinct_neighborhoods = $pdo->query("SELECT DISTINCT neighborhood FROM multi_2025 ORDER BY neighborhood")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

if ($nb_selected_id && !$nb_is_new) {
    foreach ($neighbourhoods as $r) { if ($r['id'] == $nb_selected_id) { $nb_selected = $r; break; } }
    try {
        $nh = $pdo->prepare("SELECT * FROM neighbourhood_hpi_history WHERE neighbourhood_id=? ORDER BY month_year DESC LIMIT 12");
        $nh->execute([$nb_selected_id]);
        $nb_history = $nh->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

// ── Load community events (for Events tab) ────────────────────────────────────
$ev_filter_city  = $_GET['ev_city'] ?? 'Vancouver';
$ev_edit_id      = (int)($_GET['ev_edit'] ?? 0);
$ev_edit         = null;
$ev_month        = $_GET['ev_month'] ?? date('Y-m');          // e.g. 2026-03
$ev_month_start  = $ev_month . '-01';
$ev_month_end    = date('Y-m-t', strtotime($ev_month_start)); // last day of month
$events_list     = [];
try {
    $evq = $pdo->prepare("
        SELECT e.*, n.name as nb_name
        FROM community_events e
        LEFT JOIN neighbourhoods n ON n.id = e.neighbourhood_id
        WHERE e.city = ? AND e.event_date BETWEEN ? AND ?
        ORDER BY e.event_date ASC, e.neighbourhood_id IS NULL ASC
    ");
    $evq->execute([$ev_filter_city, $ev_month_start, $ev_month_end]);
    $events_list = $evq->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
if ($ev_edit_id) {
    try {
        $evq2 = $pdo->prepare("SELECT * FROM community_events WHERE id=? LIMIT 1");
        $evq2->execute([$ev_edit_id]);
        $ev_edit = $evq2->fetch(PDO::FETCH_ASSOC);
        if ($ev_edit) $ev_filter_city = $ev_edit['city'];
    } catch (Exception $e) {}
}
$cities_list = ['Vancouver','North Vancouver','Burnaby','Richmond','West Vancouver','New Westminster','Coquitlam','Port Coquitlam','Port Moody'];
$categories_list = ['festival'=>'Festival','market'=>'Market','community'=>'Community','recreation'=>'Recreation','arts'=>'Arts & Culture','sports'=>'Sports','family'=>'Family','other'=>'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — Listings Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --brand: #002446; --blue: #0065ff; }
        body  { background: #f4f6fb; font-family: 'Segoe UI', sans-serif; }

        /* ── Top bar ── */
        .admin-topbar {
            background: var(--brand);
            color: #fff;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }
        .admin-topbar h1 { font-size: 18px; font-weight: 800; margin: 0; letter-spacing: 0.5px; }
        .admin-topbar span { font-size: 12px; opacity: 0.6; }
        .logout-btn { background: rgba(255,255,255,0.12); color: #fff; border: none; padding: 6px 16px; border-radius: 6px; font-size: 12px; cursor: pointer; text-decoration: none; }
        .logout-btn:hover { background: #0065ff; color: #fff; }

        /* ── Stats bar ── */
        .stats-bar { display: flex; gap: 16px; padding: 20px 28px; flex-wrap: wrap; }
        .stat-card { background: #fff; border-radius: 10px; padding: 16px 22px; flex: 1; min-width: 140px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .stat-card .num  { font-size: 28px; font-weight: 800; color: var(--brand); }
        .stat-card .lbl  { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }

        /* ── Main layout ── */
        .admin-body { display: flex; gap: 0; min-height: calc(100vh - 60px); }
        .listings-panel { flex: 1; padding: 0 28px 28px; }
        .edit-panel { width: 520px; background: #fff; border-left: 1px solid #e2e8f0; padding: 24px; overflow-y: auto; max-height: calc(100vh - 60px); position: sticky; top: 60px; }

        /* ── Search bar ── */
        .search-row { display: flex; gap: 10px; margin-bottom: 16px; align-items: center; padding-top: 20px; }
        .search-row input { flex: 1; border-radius: 8px; border: 1px solid #dde; padding: 9px 14px; font-size: 13px; }
        .search-row input:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 3px rgba(0,101,255,0.1); }

        /* ── Table ── */
        .listings-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); font-size: 13px; }
        .listings-table thead th { background: var(--brand); color: #fff; padding: 12px 14px; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        .listings-table tbody tr { border-bottom: 1px solid #f0f2f6; transition: background 0.15s; }
        .listings-table tbody tr:hover { background: #f8faff; }
        .listings-table tbody tr.active-row { background: #eef4ff; }
        .listings-table td { padding: 11px 14px; vertical-align: middle; }
        .listings-table td.address-cell { font-weight: 600; color: var(--brand); max-width: 220px; }
        .listings-table td.address-cell small { display: block; font-weight: 400; color: #888; font-size: 11px; }

        /* Paid badge */
        .badge-free     { background:#f0f2f6; color:#888;    font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px; text-transform:uppercase; }
        .badge-creative { background:#fef9ec; color:#b45309; font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px; text-transform:uppercase; }
        .badge-concierge{ background:#d4f5e2; color:#1a7a45; font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px; text-transform:uppercase; }

        /* Action buttons */
        .btn-edit   { background: var(--blue); color: #fff; border: none; padding: 5px 12px; border-radius: 5px; font-size: 11px; cursor: pointer; text-decoration: none; }
        .btn-edit:hover { background: #0050cc; color: #fff; }
        .btn-del    { background: #fee2e2; color: #dc2626; border: none; padding: 5px 10px; border-radius: 5px; font-size: 11px; cursor: pointer; }
        .btn-del:hover { background: #dc2626; color: #fff; }
        .btn-toggle { background: #f0f4ff; color: var(--brand); border: none; padding: 5px 10px; border-radius: 5px; font-size: 11px; cursor: pointer; }
        .btn-toggle:hover { background: var(--blue); color: #fff; }
        .btn-view   { background: #f0fdf4; color: #16a34a; border: none; padding: 5px 10px; border-radius: 5px; font-size: 11px; cursor: pointer; text-decoration: none; }
        .btn-view:hover { background: #16a34a; color: #fff; }

        /* ── Edit panel ── */
        .edit-panel h3 { font-size: 16px; font-weight: 800; color: var(--brand); margin-bottom: 4px; }
        .edit-panel .edit-address { font-size: 12px; color: #888; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f0f0f0; }
        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--blue); margin: 20px 0 10px; padding-bottom: 6px; border-bottom: 2px solid #eef2ff; }
        .form-label   { font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; }
        .form-control, .form-select { font-size: 13px; border-radius: 7px; border: 1px solid #dde; }
        .form-control:focus, .form-select:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(0,101,255,0.1); }
        .btn-save { background: var(--brand); color: #fff; border: none; padding: 11px 28px; border-radius: 8px; font-weight: 700; font-size: 14px; width: 100%; cursor: pointer; transition: background 0.2s; }
        .btn-save:hover { background: var(--blue); }
        .paid-toggle-wrap { display: flex; align-items: center; gap: 10px; background: #f8faff; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; }
        .paid-toggle-wrap label { font-size: 13px; font-weight: 600; color: var(--brand); margin: 0; }
        .paid-toggle-wrap small  { font-size: 11px; color: #888; display: block; }

        /* ── No edit selected ── */
        .no-edit { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; color: #bbb; text-align: center; gap: 12px; }
        .no-edit i { font-size: 48px; opacity: 0.3; }

        /* ── Message toast ── */
        .admin-message { background: #d4f5e2; color: #1a7a45; padding: 12px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }

        /* ── File upload UI ──────────────────────────────────────────────── */
        .upload-drop-zone {
            border: 2px dashed #c0cce0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            background: #f8faff;
            transition: all 0.2s;
            display: flex; flex-direction: column; align-items: center; gap: 6px;
        }
        .upload-drop-zone:hover { border-color: var(--blue); background: #eef4ff; }
        .upload-drop-zone i    { font-size: 24px; color: #aab; }
        .upload-drop-zone span { font-size: 13px; color: #667; font-weight: 600; }
        .upload-drop-zone small{ font-size: 11px; color: #aaa; }
        .file-selected-name    { font-size: 12px; color: #16a34a; margin-top: 6px; font-weight: 600; }
        .current-file-preview  { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 8px 12px; font-size: 12px; display: flex; align-items: center; gap: 8px; }
        .current-file-preview i{ color: #16a34a; }
        .current-file-preview a{ color: #002446; font-weight: 600; text-decoration: none; flex: 1; }
        .replace-hint          { font-size: 10px; color: #aaa; }

        /* ── Photo grid ──────────────────────────────────────────────────── */
        .photo-upload-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        .photo-slot { display: flex; flex-direction: column; gap: 4px; }
        .photo-slot-preview {
            aspect-ratio: 4/3;
            border-radius: 6px;
            overflow: hidden;
            position: relative;
            background: #f0f3f8;
            border: 2px dashed #d0d8e8;
        }
        .photo-slot-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .photo-empty-slot {
            width: 100%; height: 100%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            cursor: pointer; gap: 4px; color: #bbc;
            transition: background 0.15s;
        }
        .photo-empty-slot:hover { background: #e8f0ff; color: var(--blue); }
        .photo-empty-slot i    { font-size: 20px; }
        .photo-empty-slot span { font-size: 10px; font-weight: 600; text-transform: uppercase; }
        .photo-remove-btn {
            position: absolute; top: 4px; right: 4px;
            background: rgba(220,38,38,0.85); color: #fff;
            border: none; border-radius: 50%;
            width: 22px; height: 22px; font-size: 14px;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            line-height: 1;
        }
        .btn-replace-photo {
            background: #f0f4ff; color: var(--brand);
            border: none; border-radius: 4px;
            font-size: 10px; font-weight: 700;
            padding: 3px 8px; cursor: pointer; width: 100%;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .btn-replace-photo:hover { background: var(--blue); color: #fff; }

        @media (max-width: 900px) {
            .admin-body { flex-direction: column; }
            .edit-panel { width: 100%; max-height: none; position: static; border-left: none; border-top: 1px solid #e2e8f0; }
        }

        /* ── Bulk Import UI ─────────────────────────────────────────────── */
        .bulk-drop-zone {
            border: 2.5px dashed #c0cce0; border-radius: 12px; padding: 48px 24px;
            text-align: center; background: #f8faff; cursor: pointer; transition: all .2s;
        }
        .bulk-drop-zone:hover,.bulk-drop-zone.dz-active { border-color: #0065ff; background: #eef4ff; }
        .bulk-drop-zone .dz-icon { font-size: 40px; color: #c0cce0; display: block; margin-bottom: 12px; transition: color .2s; }
        .bulk-drop-zone.dz-active .dz-icon { color: #0065ff; }
        .bulk-preview-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .bulk-preview-table thead th { background: #f8faff; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #666; padding: 9px 11px; border-bottom: 1px solid #eaeef5; }
        .bulk-preview-table tbody td { padding: 9px 11px; border-bottom: 1px solid #f4f6fa; vertical-align: middle; }
        .bulk-preview-table tbody tr.row-unmatched { background: #fff8f8; }
        .bulk-preview-table tbody tr.row-matched:hover { background: #f0f9ff; }
        .bulk-badge-ok   { background: #dcfce7; color: #15803d; padding: 2px 9px; border-radius: 20px; font-size: 10px; font-weight: 700; white-space: nowrap; }
        .bulk-badge-warn { background: #fee2e2; color: #b91c1c; padding: 2px 9px; border-radius: 20px; font-size: 10px; font-weight: 700; white-space: nowrap; }
        .bulk-stat-pill  { background: #f0f4fb; border-radius: 10px; padding: 14px 18px; text-align: center; }
        .bulk-stat-pill strong { display: block; font-size: 24px; font-weight: 900; color: #002446; }
        .bulk-stat-pill span   { font-size: 10px; color: #999; text-transform: uppercase; letter-spacing: .4px; }
        .btn-bulk-save { background: #16a34a; color: #fff; border: none; padding: 11px 28px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; }
        .btn-bulk-save:hover { background: #15803d; }
    </style>
</head>
<body>

<!-- Top bar -->
<div class="admin-topbar">
    <div>
        <h1>🏢 Listings Manager</h1>
        <span><?= count($listings) ?> properties in database</span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <a href="half-map.php" target="_blank" class="logout-btn"><i class="fas fa-eye me-1"></i>View Site</a>
        <a href="admin.php?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
    </div>
</div>

<!-- Stats bar -->
<?php
$total     = count($listings);
$free       = count(array_filter($listings, function($r) { return ($r['tier'] ?? 'free') === 'free'; }));
$creative   = count(array_filter($listings, function($r) { return ($r['tier'] ?? '') === 'creative'; }));
$concierge  = count(array_filter($listings, function($r) { return ($r['tier'] ?? '') === 'concierge'; }));
$with_coords = count(array_filter($listings, function($r) { return !empty($r['latitude']) && (float)$r['latitude'] != 0; }));
?>
<div class="stats-bar">
    <div class="stat-card"><div class="num"><?= $total ?></div><div class="lbl">Total Properties</div></div>
    <div class="stat-card"><div class="num" style="color:#888;"><?= $free ?></div><div class="lbl">Free Tier</div></div>
    <div class="stat-card"><div class="num" style="color:#c9a84c;"><?= $creative ?></div><div class="lbl">Creative Package</div></div>
    <div class="stat-card"><div class="num" style="color:#16a34a;"><?= $concierge ?></div><div class="lbl">Concierge</div></div>
    <div class="stat-card"><div class="num" style="color:#0065ff;"><?= $with_coords ?></div><div class="lbl">Has Coordinates</div></div>
</div>

<!-- Tab navigation -->
<?php $active_tab = $_GET['tab'] ?? 'listings'; ?>
<div style="background:#fff;border-bottom:1px solid #e2e8f0;padding:0 28px;display:flex;gap:0;">
    <a href="admin.php?tab=listings" style="padding:14px 20px;font-size:13px;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $active_tab==='listings' ? '#0065ff' : 'transparent' ?>;color:<?= $active_tab==='listings' ? '#0065ff' : '#666' ?>;">
        <i class="fas fa-building me-2"></i>Listings
    </a>
    <a href="admin.php?tab=developers" style="padding:14px 20px;font-size:13px;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $active_tab==='developers' ? '#0065ff' : 'transparent' ?>;color:<?= $active_tab==='developers' ? '#0065ff' : '#666' ?>;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-user-tie"></i>Developers
        <?php if ($pending_devs > 0): ?>
        <span style="background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;"><?= $pending_devs ?></span>
        <?php endif; ?>
    </a>
    <a href="admin.php?tab=submissions" style="padding:14px 20px;font-size:13px;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $active_tab==='submissions' ? '#0065ff' : 'transparent' ?>;color:<?= $active_tab==='submissions' ? '#0065ff' : '#666' ?>;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-inbox"></i>Submissions
        <?php if ($pending_submissions > 0): ?>
        <span style="background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;"><?= $pending_submissions ?></span>
        <?php endif; ?>
    </a>
    <a href="admin.php?tab=neighbourhoods" style="padding:14px 20px;font-size:13px;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $active_tab==='neighbourhoods' ? '#0065ff' : 'transparent' ?>;color:<?= $active_tab==='neighbourhoods' ? '#0065ff' : '#666' ?>;">
        <i class="fas fa-map-marker-alt me-2"></i>Neighbourhoods
    </a>
    <a href="admin.php?tab=events" style="padding:14px 20px;font-size:13px;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $active_tab==='events' ? '#0065ff' : 'transparent' ?>;color:<?= $active_tab==='events' ? '#0065ff' : '#666' ?>;">
        <i class="fas fa-calendar-alt me-2"></i>Community Events
    </a>
    <a href="admin.php?tab=neighbourhoods&subtab=import" style="padding:14px 20px;font-size:13px;font-weight:700;text-decoration:none;border-bottom:3px solid <?= ($active_tab==='neighbourhoods' && ($_GET['subtab']??'')==='import') ? '#16a34a' : 'transparent' ?>;color:<?= ($active_tab==='neighbourhoods' && ($_GET['subtab']??'')==='import') ? '#16a34a' : '#16a34a' ?>;display:flex;align-items:center;gap:6px;">
        <i class="fas fa-file-csv"></i>&nbsp;Bulk Market Import
        <span style="background:#16a34a;color:#fff;font-size:9px;font-weight:800;padding:2px 6px;border-radius:10px;letter-spacing:.3px;">NEW</span>
    </a>
    <a href="plex-data.php" style="padding:14px 20px;font-size:13px;font-weight:700;text-decoration:none;border-bottom:3px solid transparent;color:#c9a84c;display:flex;align-items:center;gap:6px;">
        <i class="fas fa-map-marked-alt"></i>&nbsp;Plex Data
        <span style="background:#c9a84c;color:#fff;font-size:9px;font-weight:800;padding:2px 6px;border-radius:10px;letter-spacing:.3px;">W.I.N</span>
    </a>
    <a href="admin.php?tab=imports" style="padding:14px 20px;font-size:13px;font-weight:700;text-decoration:none;border-bottom:3px solid <?= $active_tab==='imports' ? '#c9a84c' : 'transparent' ?>;color:<?= $active_tab==='imports' ? '#c9a84c' : '#c9a84c' ?>;display:flex;align-items:center;gap:6px;">
        <i class="fas fa-file-import"></i>&nbsp;Data Import
        <span style="background:#7c3aed;color:#fff;font-size:9px;font-weight:800;padding:2px 6px;border-radius:10px;letter-spacing:.3px;">NEW</span>
    </a>
</div>

<!-- Main body -->
<div class="admin-body">

<?php if ($active_tab === 'developers'): ?>
<?php include __DIR__ . '/admin_members_tab.php'; ?>
<?php elseif ($active_tab === 'submissions'): ?>
<!-- ══════════════════════════════════════════════════════════
     SUBMISSIONS TAB
════════════════════════════════════════════════════════════ -->
<div style="flex:1;padding:28px;">
    <?php if (!empty($message)): ?>
    <div class="admin-message"><?= $message ?></div>
    <?php endif; ?>
    <h2 style="font-size:18px;font-weight:800;color:#0d0d1a;margin:0 0 20px;">Developer Submissions</h2>
    <?php
    $submissions = [];
    try {
        $submissions = $pdo->query("
            SELECT m.*, d.full_name as dev_name, d.company_name, d.email as dev_email
            FROM multi_2025 m
            LEFT JOIN developers d ON m.submitted_by = d.id
            WHERE m.submitted_by IS NOT NULL
            ORDER BY m.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
    ?>
    <?php if (empty($submissions)): ?>
    <div style="background:#fff;border-radius:10px;padding:48px;text-align:center;color:#aaa;border:1px solid #e2e8f0;">
        <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:12px;"></i>
        No developer submissions yet.
    </div>
    <?php else: ?>
    <table class="listings-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Address</th>
                <th>Developer</th>
                <th>Type</th>
                <th>Price</th>
                <th>Status</th>
                <th>Tier</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($submissions as $sub): ?>
        <tr>
            <td style="color:#aaa;font-size:12px;"><?= $sub['id'] ?></td>
            <td style="font-weight:600;color:#0065ff;"><?= htmlspecialchars($sub['address']) ?></td>
            <td style="font-size:12px;">
                <strong><?= htmlspecialchars($sub['company_name'] ?? $sub['dev_name'] ?? '—') ?></strong><br>
                <span style="color:#888;"><?= htmlspecialchars($sub['dev_email'] ?? '') ?></span>
            </td>
            <td style="font-size:12px;"><?= htmlspecialchars($sub['property_type'] ?? '—') ?></td>
            <td style="font-size:12px;"><?= htmlspecialchars($sub['price'] ?? '—') ?></td>
            <td>
                <?php $st = $sub['submit_status'] ?? 'draft';
                if ($st === 'pending_review')               $sc = 'background:#fef3c7;color:#b45309;';
                elseif ($st === 'approved' || $st === 'live') $sc = 'background:#dcfce7;color:#16a34a;';
                elseif ($st === 'rejected')                  $sc = 'background:#fee2e2;color:#dc2626;';
                else                                         $sc = 'background:#f3f4f6;color:#888;'; ?>
                <span style="<?= $sc ?>font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;"><?= ucfirst(str_replace('_',' ',$st)) ?></span>
            </td>
            <td>
                <?php $__st = $sub['tier'] ?? ($sub['is_paid'] ? 'concierge' : 'free');
                    $__tc = ['free'=>['#f0f2f6','#888'],'creative'=>['#fef9ec','#b45309'],'concierge'=>['#d4f5e2','#1a7a45']];
                    $__tn = ['free'=>'Free','creative'=>'Creative','concierge'=>'Concierge'];
                    $__tc2 = $__tc[$__st] ?? $__tc['free']; ?>
                    <div style="display:flex;flex-direction:column;gap:3px;">
                        <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:<?= $__tc2[0] ?>;color:<?= $__tc2[1] ?>;"><?= $__tn[$__st] ?></span>
                        <div style="display:flex;gap:2px;">
                            <a href="admin.php?set_tier=<?= $sub['id'] ?>&tier=free" style="font-size:9px;padding:1px 5px;border-radius:6px;text-decoration:none;background:<?= $__st==='free'?'#888':'#eee' ?>;color:<?= $__st==='free'?'#fff':'#555' ?>;">Free</a>
                            <a href="admin.php?set_tier=<?= $sub['id'] ?>&tier=creative" style="font-size:9px;padding:1px 5px;border-radius:6px;text-decoration:none;background:<?= $__st==='creative'?'#b45309':'#eee' ?>;color:<?= $__st==='creative'?'#fff':'#555' ?>;">Creative</a>
                            <a href="admin.php?set_tier=<?= $sub['id'] ?>&tier=concierge" style="font-size:9px;padding:1px 5px;border-radius:6px;text-decoration:none;background:<?= $__st==='concierge'?'#1a7a45':'#eee' ?>;color:<?= $__st==='concierge'?'#fff':'#555' ?>;">Concierge</a>
                        </div>
                    </div>
            </td>
            <td style="white-space:nowrap;">
                <?php if ($st === 'pending_review'): ?>
                <a href="admin.php?sub_approve=<?= $sub['id'] ?>&tab=submissions" class="action-btn" style="background:#16a34a;color:#fff;" onclick="return confirm('Approve this listing and make it live?')"><i class="fas fa-check"></i> Approve</a>
                <a href="admin.php?sub_reject=<?= $sub['id'] ?>&tab=submissions" class="action-btn" style="background:#dc2626;color:#fff;" onclick="return confirm('Reject this listing?')"><i class="fas fa-times"></i> Reject</a>
                <?php elseif ($st === 'approved' || $st === 'live'): ?>
                <a href="admin.php?sub_pending=<?= $sub['id'] ?>&tab=submissions" class="action-btn" onclick="return confirm('Reset to pending review?')"><i class="fas fa-rotate-left"></i> Unpublish</a>
                <?php elseif ($st === 'rejected'): ?>
                <a href="admin.php?sub_approve=<?= $sub['id'] ?>&tab=submissions" class="action-btn" style="background:#16a34a;color:#fff;" onclick="return confirm('Approve this listing?')"><i class="fas fa-check"></i> Approve</a>
                <?php endif; ?>
                <a href="admin.php?edit=<?= $sub['id'] ?>&tab=listings" class="action-btn"><i class="fas fa-pen"></i> Edit</a>
                <a href="admin.php?delete=<?= $sub['id'] ?>" class="action-btn" style="background:#ef4444;color:#fff;" onclick="return confirm('Delete this submission?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php elseif ($active_tab === 'listings'): ?>
<!-- ══════════════════════════════════════════════════════════
     LISTINGS TAB (original content)
════════════════════════════════════════════════════════════ -->

    <!-- LEFT: Listings table -->
    <div class="listings-panel">

        <?php if (!empty($message)): ?>
            <div class="admin-message"><?= $message ?></div>
        <?php endif; ?>

        <!-- Search -->
        <form method="GET" class="search-row">
            <input type="text" name="search" placeholder="🔍  Search by address or neighbourhood..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-edit px-4">Search</button>
            <?php if (!empty($search)): ?>
                <a href="admin.php" class="btn-del px-3">Clear</a>
            <?php endif; ?>
        </form>

        <table class="listings-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Address</th>
                    <th>Neighbourhood</th>
                    <th>Type</th>
                    <th>Est.</th>
                    <th>Tier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listings as $row): ?>
                <tr class="<?= ($edit_id == $row['id']) ? 'active-row' : '' ?>">
                    <td style="color:#bbb;font-size:11px;"><?= $row['id'] ?></td>
                    <td class="address-cell">
                        <?= htmlspecialchars($row['address']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['neighborhood'] ?? '') ?></td>
                    <td style="font-size:11px;color:#666;"><?= htmlspecialchars($row['property_type'] ?? '') ?></td>
                    <td style="font-size:11px;"><?= htmlspecialchars($row['est_completion'] ?? '') ?></td>
                    <td>
                        <?php
                        $__t = $row['tier'] ?? 'free';
                        $__tiers = ['free'=>'Free','creative'=>'Creative','concierge'=>'Concierge'];
                        $__next  = ['free'=>'creative','creative'=>'concierge','concierge'=>'free'];
                        ?>
                        <div style="display:flex;flex-direction:column;gap:4px;align-items:flex-start;">
                            <span class="badge-<?= $__t ?>"><?= $__tiers[$__t] ?></span>
                            <div style="display:flex;gap:3px;flex-wrap:wrap;">
                                <a href="admin.php?set_tier=<?= $row['id'] ?>&tier=free&search=<?= urlencode($search) ?>" style="font-size:10px;padding:1px 6px;border-radius:8px;text-decoration:none;<?= $__t==='free' ? 'background:#888;color:#fff;' : 'background:#eee;color:#555;' ?>">Free</a>
                                <a href="admin.php?set_tier=<?= $row['id'] ?>&tier=creative&search=<?= urlencode($search) ?>" style="font-size:10px;padding:1px 6px;border-radius:8px;text-decoration:none;<?= $__t==='creative' ? 'background:#b45309;color:#fff;' : 'background:#eee;color:#555;' ?>">Creative</a>
                                <a href="admin.php?set_tier=<?= $row['id'] ?>&tier=concierge&search=<?= urlencode($search) ?>" style="font-size:10px;padding:1px 6px;border-radius:8px;text-decoration:none;<?= $__t==='concierge' ? 'background:#1a7a45;color:#fff;' : 'background:#eee;color:#555;' ?>">Concierge</a>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="admin.php?edit=<?= $row['id'] ?>&search=<?= urlencode($search) ?>" class="btn-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            <a href="<?= ($row['tier'] ?? 'free') === 'concierge' ? 'concierge-property.php' : 'single-property-2.php' ?>?id=<?= $row['id'] ?>" target="_blank" class="btn-view">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn-del" onclick="if(confirm('Delete this property?')) window.location='admin.php?delete=<?= $row['id'] ?>'">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- RIGHT: Edit panel -->
    <div class="edit-panel">
        <?php if ($edit_property): ?>

            <h3><i class="fas fa-pen me-2"></i>Edit Property</h3>
            <div class="edit-address">#<?= $edit_property['id'] ?> — <?= htmlspecialchars($edit_property['address']) ?></div>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $edit_property['id'] ?>">

                <!-- Tier selector -->
                <div class="paid-toggle-wrap" style="flex-direction:column;align-items:flex-start;gap:10px;">
                    <label style="font-size:13px;font-weight:700;color:#002446;margin:0;">Listing Tier</label>
                    <div style="display:flex;gap:0;border-radius:8px;overflow:hidden;border:1.5px solid #e0e0e0;width:100%;">
                        <?php $__ct = $edit_property['tier'] ?? 'free'; ?>
                        <label style="flex:1;margin:0;cursor:pointer;">
                            <input type="radio" name="tier" value="free" <?= $__ct==='free' ? 'checked' : '' ?> style="display:none;">
                            <div style="padding:10px 6px;text-align:center;font-size:12px;font-weight:700;background:<?= $__ct==='free' ? '#555' : '#f5f5f5' ?>;color:<?= $__ct==='free' ? '#fff' : '#888' ?>;transition:all .2s;" onclick="setTier(this,'free')">
                                🏠 Free<br><span style="font-size:10px;font-weight:400;">Dev uploads own content</span>
                            </div>
                        </label>
                        <label style="flex:1;margin:0;cursor:pointer;border-left:1.5px solid #e0e0e0;border-right:1.5px solid #e0e0e0;">
                            <input type="radio" name="tier" value="creative" <?= $__ct==='creative' ? 'checked' : '' ?> style="display:none;">
                            <div style="padding:10px 6px;text-align:center;font-size:12px;font-weight:700;background:<?= $__ct==='creative' ? '#b45309' : '#f5f5f5' ?>;color:<?= $__ct==='creative' ? '#fff' : '#888' ?>;transition:all .2s;" onclick="setTier(this,'creative')">
                                🎨 Creative<br><span style="font-size:10px;font-weight:400;">Our floorplans & renders</span>
                            </div>
                        </label>
                        <label style="flex:1;margin:0;cursor:pointer;">
                            <input type="radio" name="tier" value="concierge" <?= $__ct==='concierge' ? 'checked' : '' ?> style="display:none;">
                            <div style="padding:10px 6px;text-align:center;font-size:12px;font-weight:700;background:<?= $__ct==='concierge' ? '#1a7a45' : '#f5f5f5' ?>;color:<?= $__ct==='concierge' ? '#fff' : '#888' ?>;transition:all .2s;" onclick="setTier(this,'concierge')">
                                ⭐ Concierge<br><span style="font-size:10px;font-weight:400;">Signed listing contract</span>
                            </div>
                        </label>
                    </div>
                    <small style="color:#888;">Concierge = dedicated microsite. Free &amp; Creative = standard listing page.</small>
                </div>
                <script>
                function setTier(el, val) {
                    document.querySelectorAll('[name="tier"]').forEach(function(r){ r.checked = false; });
                    el.closest('label').querySelector('input').checked = true;
                    // Reset all panels
                    var colors = {free:['#555','#f5f5f5','#888'],creative:['#b45309','#f5f5f5','#888'],concierge:['#1a7a45','#f5f5f5','#888']};
                    el.closest('.paid-toggle-wrap').querySelectorAll('label > div').forEach(function(d){
                        var t = d.closest('label').querySelector('input').value;
                        d.style.background = t===val ? colors[t][0] : colors[t][1];
                        d.style.color = t===val ? '#fff' : colors[t][2];
                    });
                }
                </script>

                <!-- Basic Info -->
                <div class="section-title">Basic Information</div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($edit_property['address'] ?? '') ?>">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Property Type</label>
                        <input type="text" name="property_type" class="form-control" value="<?= htmlspecialchars($edit_property['property_type'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Neighbourhood</label>
                        <input type="text" name="neighborhood" class="form-control" value="<?= htmlspecialchars($edit_property['neighborhood'] ?? '') ?>">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Est. Completion</label>
                        <input type="text" name="est_completion" class="form-control" value="<?= htmlspecialchars($edit_property['est_completion'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Price <?php if (!col_exists('price', $existing_cols)) echo '<small style="color:#f00">(col missing)</small>'; ?></label>
                        <input type="text" name="price" class="form-control" placeholder="T.B.A." value="<?= htmlspecialchars($edit_property['price'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($edit_property['description'] ?? '') ?></textarea>
                </div>

                <!-- Details -->
                <div class="section-title">Property Details</div>
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label">Bedrooms</label>
                        <input type="number" name="bedrooms" class="form-control" value="<?= htmlspecialchars($edit_property['bedrooms'] ?? '') ?>">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Bathrooms</label>
                        <input type="number" name="bathrooms" class="form-control" value="<?= htmlspecialchars($edit_property['bathrooms'] ?? '') ?>">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Sq Ft</label>
                        <input type="number" name="sqft" class="form-control" value="<?= htmlspecialchars($edit_property['sqft'] ?? '') ?>">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Parking</label>
                        <input type="text" name="parking" class="form-control" placeholder="e.g. 2 surface stalls" value="<?= htmlspecialchars($edit_property['parking'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Strata Fee</label>
                        <input type="text" name="strata_fee" class="form-control" placeholder="e.g. $350/mo" value="<?= htmlspecialchars($edit_property['strata_fee'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Amenities <small style="color:#aaa">(comma separated)</small></label>
                    <input type="text" name="amenities" class="form-control" placeholder="e.g. Rooftop Deck, EV Charging, Storage" value="<?= htmlspecialchars($edit_property['amenities'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Features <small style="color:#aaa">(comma separated)</small></label>
                    <input type="text" name="features" class="form-control" placeholder="e.g. Radiant Heat, Triple Glazed Windows" value="<?= htmlspecialchars($edit_property['features'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Lot / Community Features <small style="color:#aaa">(comma separated)</small></label>
                    <input type="text" name="community_features" class="form-control" placeholder="e.g. Corner Lot, South Facing, Lane Access, Mountain Views" value="<?= htmlspecialchars($edit_property['community_features'] ?? '') ?>">
                </div>

                <!-- Media -->
                <div class="section-title">Media</div>
                <div class="mb-3">
                    <label class="form-label">Video URL <small style="color:#aaa">(paste any YouTube or Vimeo link — auto-converted)</small></label>
                    <input type="text" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...  or  https://vimeo.com/123456789" value="<?= htmlspecialchars($edit_property['video_url'] ?? '') ?>">
                </div>

                <!-- Floor Plan Upload -->
                <div class="mb-3">
                    <label class="form-label">Floor Plan</label>
                    <?php if (!empty($edit_property['floorplan'])): ?>
                    <div class="current-file-preview mb-2">
                        <i class="fas fa-file-alt"></i>
                        <a href="<?= htmlspecialchars($edit_property['floorplan']) ?>" target="_blank">
                            <?= basename($edit_property['floorplan']) ?>
                        </a>
                        <span class="replace-hint">Upload new file below to replace</span>
                    </div>
                    <?php endif; ?>
                    <div class="upload-drop-zone" onclick="document.getElementById('floorplan_file').click()" id="fp-zone">
                        <i class="fas fa-drafting-compass"></i>
                        <span>Click or drag floor plan here</span>
                        <small>PDF, JPG, PNG — max 10MB</small>
                    </div>
                    <input type="file" id="floorplan_file" name="floorplan_file" accept=".pdf,.jpg,.jpeg,.png,.webp" style="display:none" onchange="previewFile(this, 'fp-zone', 'fp-preview')">
                    <div id="fp-preview" class="file-selected-name"></div>
                    <!-- Keep existing path if no new upload -->
                    <input type="hidden" name="floorplan" value="<?= htmlspecialchars($edit_property['floorplan'] ?? '') ?>">
                </div>

                <!-- Photo Uploads -->
                <div class="section-title">Photos (up to 6)</div>
                <div class="photo-upload-grid">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div class="photo-slot">
                        <div class="photo-slot-preview" id="slot-preview-<?= $i ?>">
                            <?php if (!empty($edit_property['img'.$i])): ?>
                                <img src="<?= htmlspecialchars($edit_property['img'.$i]) ?>" alt="Photo <?= $i ?>">
                                <button type="button" class="photo-remove-btn" onclick="clearSlot(<?= $i ?>)" title="Remove">×</button>
                            <?php else: ?>
                                <div class="photo-empty-slot" onclick="document.getElementById('img<?= $i ?>_file').click()">
                                    <i class="fas fa-plus"></i>
                                    <span>Photo <?= $i ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="img<?= $i ?>_file" name="img<?= $i ?>_file"
                               accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none"
                               onchange="previewPhoto(this, <?= $i ?>)">
                        <input type="hidden" name="img<?= $i ?>" id="img<?= $i ?>_val"
                               value="<?= htmlspecialchars($edit_property['img'.$i] ?? '') ?>">
                        <?php if (!empty($edit_property['img'.$i])): ?>
                        <button type="button" class="btn-replace-photo"
                                onclick="document.getElementById('img<?= $i ?>_file').click()">
                            Replace
                        </button>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
                </div>

                <!-- Builder Info -->
                <div class="section-title">Builder / Developer Info</div>

                <!-- Developer Name -->
                <div class="mb-3">
                    <label class="form-label">Developer Name <small style="color:#aaa">(shown on the listing page)</small></label>
                    <input type="text" name="developer_name" class="form-control" placeholder="e.g. Morningstar Homes" value="<?= htmlspecialchars($edit_property['developer_name'] ?? '') ?>">
                </div>

                <!-- Developer Bio -->
                <div class="mb-3">
                    <label class="form-label">Developer Description <small style="color:#aaa">(shown beside logo on listing page)</small></label>
                    <textarea name="developer_bio" class="form-control" rows="4" placeholder="e.g. Morningstar Homes has been building award-winning communities across Metro Vancouver for over 20 years. Known for their quality craftsmanship, sustainable building practices, and attention to detail, Morningstar delivers homes that stand the test of time."><?= htmlspecialchars($edit_property['developer_bio'] ?? '') ?></textarea>
                    <div class="form-text text-muted" style="font-size:11px;margin-top:3px;">Write 2–4 sentences about the developer. Appears on the listing page in the "About the Developer" section.</div>
                </div>

                <!-- Builder Logo -->
                <div class="mb-3">
                    <label class="form-label">Builder Logo</label>
                    <?php if (!empty($edit_property['builder_logo'])): ?>
                    <div class="current-file-preview mb-2">
                        <img src="<?= htmlspecialchars($edit_property['builder_logo']) ?>" style="height:40px;object-fit:contain;border-radius:4px;">
                        <a href="<?= htmlspecialchars($edit_property['builder_logo']) ?>" target="_blank">Current logo</a>
                        <span class="replace-hint">Upload new file below to replace</span>
                    </div>
                    <?php endif; ?>
                    <div class="upload-drop-zone" onclick="document.getElementById('builder_logo_file').click()" id="bl-zone">
                        <i class="fas fa-building"></i>
                        <span>Click to upload builder logo</span>
                        <small>JPG, PNG, WEBP — max 10MB</small>
                    </div>
                    <input type="file" id="builder_logo_file" name="builder_logo_file" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none" onchange="previewFile(this, 'bl-zone', 'bl-preview')">
                    <div id="bl-preview" class="file-selected-name"></div>
                    <input type="hidden" name="builder_logo" value="<?= htmlspecialchars($edit_property['builder_logo'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Builder Website <small style="color:#aaa">(full URL)</small></label>
                    <input type="text" name="builder_website" class="form-control" placeholder="https://www.buildersite.com" value="<?= htmlspecialchars($edit_property['builder_website'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Awards & Accolades <small style="color:#aaa">(one per line)</small></label>
                    <textarea name="builder_awards" class="form-control" rows="3" placeholder="e.g. HAVAN Award 2023 — Best Multi-Family Development&#10;Georgie Award Finalist 2022"><?= htmlspecialchars($edit_property['builder_awards'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Virtual Tour URL <small style="color:#aaa">(Matterport, YouTube 360, or any embed URL)</small></label>
                    <input type="text" name="virtual_tour_url" class="form-control" placeholder="https://my.matterport.com/show/?m=..." value="<?= htmlspecialchars($edit_property['virtual_tour_url'] ?? '') ?>">
                    <div class="form-text text-muted" style="font-size:11px;margin-top:4px;">Paste any Matterport, YouTube, or Vimeo link — it will be embedded automatically.</div>
                </div>

                <!-- Coordinates -->
                <div class="section-title">Coordinates</div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" class="form-control" value="<?= $edit_property['latitude'] ?? '' ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" class="form-control" value="<?= $edit_property['longitude'] ?? '' ?>">
                    </div>
                </div>

                <button type="submit" name="save_property" class="btn-save">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </form>

        <?php else: ?>
            <div class="no-edit">
                <i class="fas fa-pen-to-square"></i>
                <p>Click <strong>Edit</strong> on any property<br>to update its details here.</p>
            </div>
        <?php endif; ?>
    </div>

<?php elseif ($active_tab === 'neighbourhoods' && ($_GET['subtab'] ?? '') === 'import'): ?>
<!-- ══════════════════════════════════════════════════════════
     BULK MARKET IMPORT TAB
════════════════════════════════════════════════════════════ -->
<div style="flex:1;padding:28px;max-width:1100px;">

<?php if (!empty($message)): ?>
<div class="admin-message" style="margin-bottom:20px;"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if (!empty($bulk_results)): ?>
<!-- ── IMPORT DONE ── -->
<div style="background:#f0fdf4;border:2px solid #86efac;border-radius:14px;padding:36px;text-align:center;margin-bottom:24px;">
    <i class="fas fa-check-circle" style="font-size:44px;color:#16a34a;display:block;margin-bottom:14px;"></i>
    <h3 style="font-size:20px;font-weight:900;color:#002446;margin-bottom:6px;">Import Complete!</h3>
    <p style="color:#555;margin-bottom:20px;">
        <?= date('F Y', strtotime($bulk_results['month'])) ?> market data saved.
    </p>
    <div class="row justify-content-center g-3" style="max-width:420px;margin:0 auto 24px;">
        <div class="col-4"><div class="bulk-stat-pill"><strong style="color:#16a34a;"><?= $bulk_results['saved'] ?></strong><span>Saved</span></div></div>
        <div class="col-4"><div class="bulk-stat-pill"><strong style="color:#d97706;"><?= $bulk_results['skipped'] ?></strong><span>Skipped</span></div></div>
        <div class="col-4"><div class="bulk-stat-pill"><strong style="color:#dc2626;"><?= $bulk_results['errors'] ?></strong><span>Errors</span></div></div>
    </div>
    <a href="admin.php?tab=neighbourhoods&subtab=import" style="background:#002446;color:#fff;border-radius:9px;padding:11px 24px;font-size:13px;font-weight:700;text-decoration:none;display:inline-block;">
        <i class="fas fa-upload me-2"></i>Import Another Month
    </a>
    &nbsp;
    <a href="admin.php?tab=neighbourhoods" style="color:#555;font-size:13px;font-weight:600;text-decoration:none;">
        View Neighbourhoods →
    </a>
</div>

<?php elseif ($bulk_upload_done && !empty($bulk_preview_rows)): ?>
<!-- ── PREVIEW TABLE ── -->
<?php
    $bm_count = count(array_filter($bulk_preview_rows, fn($r) => $r['match_status']==='matched'));
    $bu_count = count($bulk_preview_rows) - $bm_count;
    $sample_month = $bulk_preview_rows[0]['month_dt'] ?? null;
    // Reload all neighbourhoods for the manual assign dropdown
    $all_nbs_dropdown = $pdo->query("SELECT id, name, area FROM neighbourhoods WHERE is_active=1 ORDER BY area, name")->fetchAll(PDO::FETCH_ASSOC);
?>
<div style="background:#fff;border:1px solid #eaeef5;border-radius:14px;padding:24px;margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
        <div>
            <h3 style="font-size:17px;font-weight:800;color:#002446;margin:0;">
                Preview — <?= $sample_month ? date('F Y', strtotime($sample_month)) : '' ?>
            </h3>
            <p style="font-size:12px;color:#888;margin:4px 0 0;">Review before saving. Assign any unmatched rows manually.</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <span class="bulk-badge-ok"><i class="fas fa-check me-1"></i><?= $bm_count ?> matched</span>
            <?php if ($bu_count): ?><span class="bulk-badge-warn"><i class="fas fa-question me-1"></i><?= $bu_count ?> unmatched</span><?php endif; ?>
        </div>
    </div>

    <?php if (!empty($bulk_parse_errors)): ?>
    <div class="alert alert-warning py-2 mb-3" style="font-size:12px;">
        <?php foreach ($bulk_parse_errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" id="bulkConfirmForm" action="admin.php?tab=neighbourhoods&subtab=import">
        <input type="hidden" name="bulk_confirm_save" value="1">
        <input type="hidden" name="bulk_month" value="<?= htmlspecialchars($sample_month ?? '') ?>">
        <input type="hidden" name="bulk_rows_json" id="bulk_rows_json">

        <div style="overflow-x:auto;">
        <table class="bulk-preview-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="bulkCheckAll" checked title="Select/deselect all"></th>
                    <th>REBGV Name</th>
                    <th>Matched To</th>
                    <th style="text-align:right;">Detached</th>
                    <th style="text-align:right;">Duplex</th>
                    <th style="text-align:right;">Condo</th>
                    <th style="text-align:right;">Townhouse</th>
                    <th style="text-align:right;">YoY Chg</th>
                    <th style="text-align:center;">DOM</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bulk_preview_rows as $i => $r): ?>
            <tr class="row-<?= $r['match_status'] ?>">
                <td><input type="checkbox" class="bulk-row-cb" data-idx="<?= $i ?>" <?= $r['match_status']==='matched'?'checked':'' ?>></td>
                <td style="font-weight:600;color:#002446;white-space:nowrap;"><?= htmlspecialchars($r['nb_name_rebgv']) ?></td>
                <td>
                    <?php if ($r['matched']): ?>
                        <span style="font-weight:700;color:#002446;font-size:12px;"><?= htmlspecialchars($r['matched']['name']) ?></span>
                        <span style="font-size:10px;color:#aaa;display:block;"><?= htmlspecialchars($r['matched']['area']) ?></span>
                    <?php else: ?>
                        <select class="form-select form-select-sm" id="bulk_manual_<?= $i ?>" style="font-size:11px;min-width:200px;">
                            <option value="">— assign manually —</option>
                            <?php foreach ($all_nbs_dropdown as $nb): ?>
                            <option value="<?= $nb['id'] ?>"><?= htmlspecialchars($nb['name']) ?> (<?= $nb['area'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </td>
                <td style="text-align:right;font-size:12px;"><?= $r['price_detached']  ? '$'.number_format($r['price_detached'])  : '<span style="color:#ddd;">—</span>' ?></td>
                <td style="text-align:right;font-size:12px;"><?= $r['price_duplex']    ? '$'.number_format($r['price_duplex'])    : '<span style="color:#ddd;">—</span>' ?></td>
                <td style="text-align:right;font-size:12px;"><?= $r['price_condo']     ? '$'.number_format($r['price_condo'])     : '<span style="color:#ddd;">—</span>' ?></td>
                <td style="text-align:right;font-size:12px;"><?= $r['price_townhouse'] ? '$'.number_format($r['price_townhouse']) : '<span style="color:#ddd;">—</span>' ?></td>
                <td style="text-align:right;font-size:12px;">
                    <?php if ($r['hpi_yoy'] !== null):
                        $c = (float)$r['hpi_yoy'] >= 0 ? '#16a34a' : '#dc2626';
                    ?><strong style="color:<?= $c ?>;"><?= ((float)$r['hpi_yoy']>=0?'+':'').number_format((float)$r['hpi_yoy'],1) ?>%</strong>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td style="text-align:center;font-size:11px;white-space:nowrap;">
                    <?php
                    $dom_parts = [];
                    if ($r['dom_detached'])  $dom_parts[] = '<span title="Detached"><i class="fas fa-home" style="color:#0065ff;margin-right:2px;"></i>'  . $r['dom_detached']  . 'd</span>';
                    if ($r['dom_duplex'])    $dom_parts[] = '<span title="Duplex"><i class="fas fa-columns" style="color:#7c3aed;margin-right:2px;"></i>'  . $r['dom_duplex']    . 'd</span>';
                    if ($r['dom_condo'])     $dom_parts[] = '<span title="Condo"><i class="fas fa-building" style="color:#0891b2;margin-right:2px;"></i>'  . $r['dom_condo']     . 'd</span>';
                    if ($r['dom_townhouse']) $dom_parts[] = '<span title="Townhouse"><i class="fas fa-th-large" style="color:#16a34a;margin-right:2px;"></i>' . $r['dom_townhouse'] . 'd</span>';
                    if ($dom_parts) {
                        echo '<div style="display:flex;flex-direction:column;gap:2px;align-items:center;">' . implode('', $dom_parts) . '</div>';
                        if (!empty($r['dom_from_total'])) {
                            echo '<div style="font-size:9px;color:#f59e0b;margin-top:2px;" title="DOM from area TOTAL row">area avg</div>';
                        }
                    } else {
                        echo '<span style="color:#ddd;">—</span>';
                    }
                    ?>
                </td>
                <td>
                    <?php if ($r['match_status']==='matched'): ?>
                    <span class="bulk-badge-ok"><i class="fas fa-check me-1"></i>Auto</span>
                    <?php else: ?>
                    <span class="bulk-badge-warn"><i class="fas fa-question me-1"></i>Manual</span>
                    <?php endif; ?>
                </td>
            </tr>
            <script>
            window._bulkRows = window._bulkRows || [];
            window._bulkRows[<?= $i ?>] = <?= json_encode([
                'nb_id'          => $r['matched']['id'] ?? null,
                'nb_name'        => $r['nb_name_rebgv'],
                'price_detached' => $r['price_detached'],
                'price_duplex'   => $r['price_duplex'],
                'price_condo'    => $r['price_condo'],
                'price_townhouse'=> $r['price_townhouse'],
                'hpi_benchmark'  => $r['hpi_benchmark'],
                'hpi_yoy'        => $r['hpi_yoy'],
                'dom_detached'   => $r['dom_detached'],
                'dom_duplex'     => $r['dom_duplex'],
                'dom_condo'      => $r['dom_condo'],
                'dom_townhouse'  => $r['dom_townhouse'],
                'sales_detached' => $r['sales_detached'],
                'sales_duplex'   => $r['sales_duplex'],
                'sales_condo'    => $r['sales_condo'],
                'sales_townhouse'=> $r['sales_townhouse'],
            ]) ?>;
            </script>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:20px;flex-wrap:wrap;gap:12px;">
            <a href="admin.php?tab=neighbourhoods&subtab=import" style="color:#666;font-size:13px;font-weight:600;text-decoration:none;">
                <i class="fas fa-arrow-left me-1"></i>Upload Different File
            </a>
            <button type="button" onclick="doBulkSave()" class="btn-bulk-save">
                <i class="fas fa-database me-2"></i>Save Checked Rows to Database
            </button>
        </div>
    </form>
</div>

<?php else: ?>
<!-- ── UPLOAD FORM ── -->
<div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">

<!-- Left: upload area -->
<div style="flex:1;min-width:320px;">

    <?php if (!empty($bulk_parse_errors)): ?>
    <div class="alert alert-danger mb-3" style="font-size:12px;"><?= implode('<br>', array_map('htmlspecialchars', $bulk_parse_errors)) ?></div>
    <?php endif; ?>

    <div style="background:#fff;border:1px solid #eaeef5;border-radius:14px;padding:24px;margin-bottom:20px;">
        <div style="font-size:13px;font-weight:700;color:#002446;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #f0f4ff;">
            <i class="fas fa-upload me-2" style="color:#0065ff;"></i>Upload Monthly CSV
        </div>

        <form method="POST" enctype="multipart/form-data" id="bulkUploadForm" action="admin.php?tab=neighbourhoods&subtab=import">
            <input type="hidden" name="bulk_upload_csv" value="1">
            <div class="bulk-drop-zone" id="bulkDropZone" onclick="document.getElementById('bulk_csv').click()">
                <i class="fas fa-file-csv dz-icon" id="bulkDropIcon"></i>
                <div id="bulkDropText" style="font-weight:700;color:#555;margin-bottom:6px;">Click or drag & drop your CSV here</div>
                <small style="color:#aaa;">REBGV neighbourhood data — .csv format</small>
            </div>
            <input type="file" name="bulk_csv" id="bulk_csv" accept=".csv" style="display:none;" required>

            <div style="margin-top:18px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <button type="submit" class="btn-save" style="width:auto;padding:11px 28px;">
                    <i class="fas fa-eye me-2"></i>Preview & Match Neighbourhoods
                </button>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <select id="tpl_area" class="form-select form-select-sm" style="width:auto;font-size:12px;">
                        <option value="vancouver-east">Vancouver East</option>
                        <option value="vancouver-west">Vancouver West</option>
                        <option value="burnaby-north">Burnaby North</option>
                        <option value="burnaby-east">Burnaby East</option>
                        <option value="burnaby-south">Burnaby South</option>
                        <option value="richmond">Richmond</option>
                        <option value="north-vancouver">North Vancouver</option>
                        <option value="west-vancouver">West Vancouver</option>
                        <option value="coquitlam">Coquitlam</option>
                        <option value="port-coquitlam">Port Coquitlam</option>
                        <option value="port-moody">Port Moody</option>
                    </select>
                    <a id="tpl_dl_link" href="admin.php?tab=neighbourhoods&subtab=import&bulk_template=vancouver-east"
                       style="font-size:13px;color:#0065ff;font-weight:600;text-decoration:none;white-space:nowrap;">
                        <i class="fas fa-download me-1"></i>Download Template
                    </a>
                </div>
            </div>
            <script>
            document.getElementById('tpl_area').addEventListener('change', function() {
                document.getElementById('tpl_dl_link').href =
                    'admin.php?tab=neighbourhoods&subtab=import&bulk_template=' + this.value;
            });
            </script>
        </form>
    </div>

    <div style="background:#fff;border:1px solid #eaeef5;border-radius:14px;padding:24px;">
        <div style="font-size:13px;font-weight:700;color:#002446;margin-bottom:14px;">
            <i class="fas fa-list-ol me-2" style="color:#0065ff;"></i>How It Works
        </div>
        <ol style="padding-left:18px;margin:0;font-size:13px;color:#555;">
            <li style="padding:7px 0;"><strong>Download the template</strong> → already has all Vancouver East neighbourhoods pre-filled from the Feb 2026 REBGV PDF.</li>
            <li style="padding:7px 0;"><strong>Each month</strong>, update the prices &amp; YoY % from the new REBGV PDF. Takes ~5 minutes.</li>
            <li style="padding:7px 0;"><strong>Upload</strong> → the tool auto-matches REBGV names to your database. Manual dropdown for any unmatched.</li>
            <li style="padding:7px 0;"><strong>Preview &amp; save</strong> → updates both live neighbourhood pages AND the 12-month history chart instantly.</li>
            <li style="padding:7px 0;"><strong>Re-upload is safe</strong> — same month re-imported updates existing data, never duplicates.</li>
        </ol>
    </div>
</div>

<!-- Right: column guide -->
<div style="width:320px;flex-shrink:0;">
    <div style="background:#fff;border:1px solid #eaeef5;border-radius:14px;padding:24px;margin-bottom:20px;">
        <div style="font-size:13px;font-weight:700;color:#002446;margin-bottom:14px;">
            <i class="fas fa-table me-2" style="color:#0065ff;"></i>CSV Column Reference
        </div>
        <table style="width:100%;font-size:11px;border-collapse:collapse;">
            <thead><tr style="background:#f8faff;">
                <th style="padding:7px 10px;text-align:left;color:#888;border-bottom:1px solid #eaeef5;">Column</th>
                <th style="padding:7px 10px;text-align:left;color:#888;border-bottom:1px solid #eaeef5;">Example</th>
                <th style="padding:7px 10px;border-bottom:1px solid #eaeef5;"></th>
            </tr></thead>
            <tbody>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>month_year</code></td><td style="padding:5px 10px;color:#555;">2026-02</td><td style="padding:5px 10px;color:#16a34a;font-weight:700;font-size:10px;">Required</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>neighbourhood_rebgv</code></td><td style="padding:5px 10px;color:#555;">Collingwood VE</td><td style="padding:5px 10px;color:#16a34a;font-weight:700;font-size:10px;">Required</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>price_detached</code></td><td style="padding:5px 10px;color:#555;">1592100</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>price_duplex</code></td><td style="padding:5px 10px;color:#555;">1415000</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>price_condo</code></td><td style="padding:5px 10px;color:#555;">533300</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>price_townhouse</code></td><td style="padding:5px 10px;color:#555;">868200</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>yoy_detached</code></td><td style="padding:5px 10px;color:#555;">-7.7</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>yoy_duplex</code></td><td style="padding:5px 10px;color:#555;">-5.2</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>yoy_condo</code></td><td style="padding:5px 10px;color:#555;">-8.6</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>yoy_townhouse</code></td><td style="padding:5px 10px;color:#555;">-13.4</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>sales_detached</code></td><td style="padding:5px 10px;color:#555;">4</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>sales_duplex</code></td><td style="padding:5px 10px;color:#555;">3</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>sales_condo</code></td><td style="padding:5px 10px;color:#555;">15</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>sales_townhouse</code></td><td style="padding:5px 10px;color:#555;">1</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>dom_detached</code></td><td style="padding:5px 10px;color:#555;">35</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>dom_duplex</code></td><td style="padding:5px 10px;color:#555;">26</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr style="border-bottom:1px solid #f4f6fa;"><td style="padding:5px 10px;"><code>dom_condo</code></td><td style="padding:5px 10px;color:#555;">30</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
                <tr><td style="padding:5px 10px;"><code>dom_townhouse</code></td><td style="padding:5px 10px;color:#555;">25</td><td style="padding:5px 10px;color:#aaa;font-size:10px;">Optional</td></tr>
            </tbody>
        </table>
        <p style="font-size:10px;color:#aaa;margin-top:10px;margin-bottom:0;">Dollar signs, commas, and + signs are stripped automatically. Blank cells preserve existing DB values. Works for all areas — just pick the right template.</p>
    </div>

    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:18px 20px;">
        <div style="font-size:12px;font-weight:700;color:#92400e;margin-bottom:8px;"><i class="fas fa-lightbulb me-1"></i>Pro tip</div>
        <p style="font-size:12px;color:#92400e;margin:0;">
            Templates are available for all areas: Vancouver East &amp; West, Burnaby (North/East/South), Richmond, North Vancouver, West Vancouver, Coquitlam, Port Coquitlam, and Port Moody. Select the area from the dropdown, download its template, fill in the prices from the matching REBGV PDF section, and upload. You can upload multiple CSVs — one per area — each month.
        </p>
    </div>
</div>

</div><!-- /upload flex -->
<?php endif; // end upload form ?>

</div><!-- /bulk import tab -->

<?php elseif ($active_tab === 'neighbourhoods'): ?>
<!-- ══════════════════════════════════════════════════════════
     NEIGHBOURHOODS TAB — Monthly HPI Data Entry
════════════════════════════════════════════════════════════ -->
<div style="flex:1;padding:28px;display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">

    <!-- Left: neighbourhood list -->
    <div style="width:260px;flex-shrink:0;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#999;margin-bottom:12px;">Select Neighbourhood</div>
        <?php
        $current_area = '';
        foreach ($neighbourhoods as $nb):
            if ($nb['area'] !== $current_area):
                if ($current_area !== '') echo '</div>';
                $current_area = $nb['area'];
                echo '<div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#0065ff;margin:14px 0 6px;">' . htmlspecialchars($current_area) . '</div>';
                echo '<div>';
            endif;
            $is_sel = ($nb['id'] == $nb_selected_id);
        ?>
        <a href="admin.php?tab=neighbourhoods&nb_id=<?= $nb['id'] ?>"
           style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:8px;text-decoration:none;margin-bottom:3px;font-size:13px;font-weight:<?= $is_sel?'700':'500' ?>;
                  background:<?= $is_sel?'#002446':'#fff' ?>;color:<?= $is_sel?'#fff':'#444' ?>;border:1px solid <?= $is_sel?'#002446':'#eaeef5' ?>;">
            <?= htmlspecialchars($nb['name']) ?>
            <?php if (!empty($nb['price_updated_date'])): ?>
            <span style="font-size:10px;opacity:.6;"><?= date('M Y', strtotime($nb['price_updated_date'])) ?></span>
            <?php else: ?>
            <span style="font-size:10px;color:#f59e0b;<?= $is_sel?'opacity:.7':'' ?>">No data</span>
            <?php endif; ?>
        </a>
        <?php endforeach; if ($current_area !== '') echo '</div>'; ?>

        <!-- Add new button -->
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid #eaeef5;">
            <a href="admin.php?tab=neighbourhoods&nb_id=new"
               style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 14px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;background:#0065ff;color:#fff;border:none;">
                <i class="fas fa-plus"></i> Add Neighbourhood
            </a>
        </div>
    </div>

    <!-- Right: HPI form + history -->
    <div style="flex:1;min-width:320px;">

        <?php if (!empty($message)): ?>
        <div class="admin-message" style="margin-bottom:20px;"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($nb_is_new): ?>
        <!-- ── ADD NEW NEIGHBOURHOOD FORM ── -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div>
                <h2 style="font-size:20px;font-weight:800;color:#002446;margin:0;">Add New Neighbourhood</h2>
                <p style="font-size:13px;color:#888;margin:4px 0 0;">The page will go live instantly at <code>/neighbourhood.php?slug=...</code></p>
            </div>
            <a href="admin.php?tab=neighbourhoods" style="background:#f4f6fb;color:#888;border-radius:7px;padding:8px 14px;font-size:12px;font-weight:700;text-decoration:none;">
                <i class="fas fa-arrow-left me-1"></i>Cancel
            </a>
        </div>

        <form method="POST" action="admin.php?tab=neighbourhoods&nb_id=new">
        <input type="hidden" name="save_new_neighbourhood" value="1">

        <div style="background:#fff;border:1px solid #eaeef5;border-radius:12px;padding:24px;margin-bottom:20px;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#0065ff;margin-bottom:14px;padding-bottom:8px;border-bottom:2px solid #f0f4ff;">
                Basic Info
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Neighbourhood Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="nb_name" class="form-control" placeholder="e.g. Mount Pleasant" required>
                    <div style="font-size:10px;color:#aaa;margin-top:3px;">Slug auto-generated from name</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Area <span style="color:#dc2626;">*</span></label>
                    <select name="nb_area" class="form-select" required>
                        <option value="">— Select area —</option>
                        <option value="Vancouver East">Vancouver East</option>
                        <option value="Vancouver West">Vancouver West</option>
                        <option value="Burnaby">Burnaby</option>
                        <option value="Richmond">Richmond</option>
                        <option value="North Vancouver">North Vancouver</option>
                        <option value="West Vancouver">West Vancouver</option>
                        <option value="Coquitlam">Coquitlam</option>
                        <option value="Port Coquitlam">Port Coquitlam</option>
                        <option value="Port Moody">Port Moody</option>
                        <option value="New Westminster">New Westminster</option>
                        <option value="Surrey">Surrey</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Coming Soon DB Match <span style="color:#dc2626;">*</span></label>
                    <select name="nb_db_neighborhood" class="form-select">
                        <option value="">— None / Not applicable —</option>
                        <?php foreach ($distinct_neighborhoods as $dn): ?>
                        <option value="<?= htmlspecialchars($dn) ?>"><?= htmlspecialchars($dn) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-size:10px;color:#aaa;margin-top:3px;">Must match the <code>neighborhood</code> value in multi_2025</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="nb_sort_order" class="form-control" placeholder="99" value="99">
                    <div style="font-size:10px;color:#aaa;margin-top:3px;">Lower = appears first in the list</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="nb_description" class="form-control" rows="3" placeholder="Write 2–3 sentences about this neighbourhood. What makes it unique? What's driving development here?"></textarea>
                </div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #eaeef5;border-radius:12px;padding:24px;margin-bottom:20px;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#0065ff;margin-bottom:14px;padding-bottom:8px;border-bottom:2px solid #f0f4ff;">
                Active Listings Bounding Box
                <span style="font-size:10px;font-weight:400;color:#aaa;text-transform:none;letter-spacing:0;margin-left:8px;">Used to pull MLS® listings by location</span>
            </div>
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 14px;font-size:12px;color:#92400e;margin-bottom:14px;">
                <i class="fas fa-lightbulb me-2"></i>
                <strong>How to get coordinates:</strong> Open Google Maps, right-click the SW corner of the neighbourhood → copy coordinates. Repeat for NE corner.
                Vancouver lat is ~49.2–49.3 · lng is ~-123.0 to -123.2 (always negative)
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Lat Min (South)</label>
                    <input type="text" name="nb_lat_min" class="form-control" placeholder="49.2350">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Lat Max (North)</label>
                    <input type="text" name="nb_lat_max" class="form-control" placeholder="49.2550">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Lng Min (West)</label>
                    <input type="text" name="nb_lng_min" class="form-control" placeholder="-123.0450">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Lng Max (East)</label>
                    <input type="text" name="nb_lng_max" class="form-control" placeholder="-123.0150">
                </div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #eaeef5;border-radius:12px;padding:24px;margin-bottom:20px;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#0065ff;margin-bottom:14px;padding-bottom:8px;border-bottom:2px solid #f0f4ff;">
                Livability Scores &amp; Demographics <span style="font-size:10px;font-weight:400;color:#aaa;text-transform:none;letter-spacing:0;margin-left:8px;">All optional</span>
            </div>
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Walk Score</label>
                    <input type="number" name="nb_walkscore" class="form-control" placeholder="0–100" min="0" max="100">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Transit Score</label>
                    <input type="number" name="nb_transitscore" class="form-control" placeholder="0–100" min="0" max="100">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bike Score</label>
                    <input type="number" name="nb_bikescore" class="form-control" placeholder="0–100" min="0" max="100">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Population</label>
                    <input type="text" name="nb_population" class="form-control" placeholder="e.g. 24000">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Median Income</label>
                    <input type="text" name="nb_median_income" class="form-control" placeholder="e.g. 76000">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Area (km²)</label>
                    <input type="text" name="nb_area_sqkm" class="form-control" placeholder="e.g. 3.10">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-save" style="width:auto;padding:12px 36px;font-size:15px;">
            <i class="fas fa-plus me-2"></i>Create Neighbourhood Page
        </button>
        </form>

        <?php elseif ($nb_selected): ?>

        <!-- Header -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div>
                <h2 style="font-size:20px;font-weight:800;color:#002446;margin:0;"><?= htmlspecialchars($nb_selected['name']) ?></h2>
                <p style="font-size:13px;color:#888;margin:4px 0 0;">
                    <?= htmlspecialchars($nb_selected['area']) ?>
                    <?php if (!empty($nb_selected['price_updated_date'])): ?>
                    &nbsp;·&nbsp; Last updated <?= date('F j, Y', strtotime($nb_selected['price_updated_date'])) ?>
                    <?php endif; ?>
                </p>
            </div>
            <a href="neighbourhood.php?slug=<?= urlencode($nb_selected['slug']) ?>" target="_blank"
               style="background:#f0f4ff;color:#0065ff;border-radius:7px;padding:8px 14px;font-size:12px;font-weight:700;text-decoration:none;">
                <i class="fas fa-eye me-1"></i>Preview Page
            </a>
            <a href="admin.php?tab=neighbourhoods&nb_delete=<?= $nb_selected['id'] ?>"
               onclick="return confirm('Delete <?= htmlspecialchars(addslashes($nb_selected['name'])) ?> and all its price history? This cannot be undone.')"
               style="background:#fee2e2;color:#dc2626;border-radius:7px;padding:8px 14px;font-size:12px;font-weight:700;text-decoration:none;">
                <i class="fas fa-trash me-1"></i>Delete
            </a>
        </div>

        <!-- Neighbourhood Details Form -->
        <div style="background:#fff;border:1px solid #eaeef5;border-radius:12px;padding:24px;margin-bottom:24px;">
            <div style="font-size:13px;font-weight:700;color:#002446;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #f0f4ff;">
                <i class="fas fa-info-circle me-2" style="color:#0065ff;"></i>Neighbourhood Details
            </div>
            <form method="POST" action="admin.php?tab=neighbourhoods&nb_id=<?= $nb_selected['id'] ?>">
                <input type="hidden" name="save_nb_details" value="1">
                <input type="hidden" name="neighbourhood_id" value="<?= $nb_selected['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="nb_description" class="form-control" rows="3"
                              placeholder="2–3 sentences about this neighbourhood..."><?= htmlspecialchars($nb_selected['description'] ?? '') ?></textarea>
                </div>

                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#0065ff;margin:14px 0 10px;padding-bottom:6px;border-bottom:1px solid #f0f4ff;">
                    Livability Scores
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-walking me-1" style="color:#0065ff;"></i>Walk Score</label>
                        <input type="number" name="nb_walkscore" class="form-control" min="0" max="100"
                               placeholder="0–100" value="<?= htmlspecialchars($nb_selected['walkscore'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-bus me-1" style="color:#0065ff;"></i>Transit Score</label>
                        <input type="number" name="nb_transitscore" class="form-control" min="0" max="100"
                               placeholder="0–100" value="<?= htmlspecialchars($nb_selected['transitscore'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-bicycle me-1" style="color:#0065ff;"></i>Bike Score</label>
                        <input type="number" name="nb_bikescore" class="form-control" min="0" max="100"
                               placeholder="0–100" value="<?= htmlspecialchars($nb_selected['bikescore'] ?? '') ?>">
                    </div>
                </div>

                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#0065ff;margin:14px 0 10px;padding-bottom:6px;border-bottom:1px solid #f0f4ff;">
                    Demographics
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-users me-1" style="color:#0065ff;"></i>Population</label>
                        <input type="text" name="nb_population" class="form-control"
                               placeholder="e.g. 12400" value="<?= htmlspecialchars($nb_selected['population'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-dollar-sign me-1" style="color:#0065ff;"></i>Median Income</label>
                        <input type="text" name="nb_median_income" class="form-control"
                               placeholder="e.g. 88000" value="<?= htmlspecialchars($nb_selected['median_income'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-ruler-combined me-1" style="color:#0065ff;"></i>Area (km²)</label>
                        <input type="text" name="nb_area_sqkm" class="form-control"
                               placeholder="e.g. 3.10" value="<?= htmlspecialchars($nb_selected['area_sqkm'] ?? '') ?>">
                    </div>
                </div>

                <button type="submit" class="btn-save" style="width:auto;padding:10px 28px;">
                    <i class="fas fa-save me-2"></i>Save Details
                </button>
            </form>
        </div>

        <!-- HPI Entry Form -->
        <div style="background:#fff;border:1px solid #eaeef5;border-radius:12px;padding:24px;margin-bottom:24px;">
            <div style="font-size:13px;font-weight:700;color:#002446;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #f0f4ff;">
                <i class="fas fa-chart-line me-2" style="color:#0065ff;"></i>Enter Monthly HPI Data
            </div>
            <form method="POST" action="admin.php?tab=neighbourhoods&nb_id=<?= $nb_selected['id'] ?>">
                <input type="hidden" name="save_hpi" value="1">
                <input type="hidden" name="neighbourhood_id" value="<?= $nb_selected['id'] ?>">

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Month <span style="color:#dc2626;">*</span></label>
                        <input type="month" name="month_year" class="form-control"
                               value="<?= date('Y-m') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">HPI Benchmark <small style="color:#aaa;font-weight:400;">(optional)</small></label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;">$</span>
                            <input type="text" name="hpi_benchmark" class="form-control" style="padding-left:22px;"
                                   placeholder="0" value="<?= htmlspecialchars($nb_selected['hpi_benchmark'] ?? '') ?>">
                        </div>
                        <div style="font-size:10px;color:#aaa;margin-top:3px;">The REBGV composite HPI index value</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Average Price <small style="color:#aaa;font-weight:400;">(optional)</small></label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;">$</span>
                            <input type="text" name="avg_price" class="form-control" style="padding-left:22px;"
                                   placeholder="0" value="<?= htmlspecialchars($nb_selected['avg_price'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#0065ff;margin:16px 0 10px;padding-bottom:6px;border-bottom:1px solid #f0f4ff;">
                    By Property Type
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-home me-1" style="color:#0065ff;"></i>Detached</label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;">$</span>
                            <input type="text" name="price_detached" class="form-control" style="padding-left:22px;"
                                   placeholder="0" value="<?= htmlspecialchars($nb_selected['price_detached'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-building me-1" style="color:#0065ff;"></i>Condo</label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;">$</span>
                            <input type="text" name="price_condo" class="form-control" style="padding-left:22px;"
                                   placeholder="0" value="<?= htmlspecialchars($nb_selected['price_condo'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-th-large me-1" style="color:#0065ff;"></i>Townhouse</label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;">$</span>
                            <input type="text" name="price_townhouse" class="form-control" style="padding-left:22px;"
                                   placeholder="0" value="<?= htmlspecialchars($nb_selected['price_townhouse'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-columns me-1" style="color:#0065ff;"></i>Duplex / Multiplex</label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;">$</span>
                            <input type="text" name="price_duplex" class="form-control" style="padding-left:22px;"
                                   placeholder="0" value="<?= htmlspecialchars($nb_selected['price_duplex'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#0065ff;margin:16px 0 10px;padding-bottom:6px;border-bottom:1px solid #f0f4ff;">
                    Price Changes
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Month-over-Month % Change</label>
                        <div style="position:relative;">
                            <input type="text" name="hpi_change_mom" class="form-control" style="padding-right:28px;"
                                   placeholder="e.g. -1.2 or +2.5" value="<?= htmlspecialchars($nb_selected['hpi_change_mom'] ?? '') ?>">
                            <span style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;">%</span>
                        </div>
                        <div style="font-size:10px;color:#aaa;margin-top:3px;">Negative = price drop (e.g. -4.6)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Year-over-Year % Change</label>
                        <div style="position:relative;">
                            <input type="text" name="hpi_change_yoy" class="form-control" style="padding-right:28px;"
                                   placeholder="e.g. -10.0 or +5.2" value="<?= htmlspecialchars($nb_selected['hpi_change_yoy'] ?? '') ?>">
                            <span style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;">%</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-save" style="width:auto;padding:11px 32px;">
                    <i class="fas fa-save me-2"></i>Save &amp; Update Page
                </button>
            </form>
        </div>

        <!-- Hero Image Override -->
        <div style="background:#fff;border:1px solid #eaeef5;border-radius:12px;padding:24px;margin-bottom:24px;">
            <div style="font-size:13px;font-weight:700;color:#002446;margin-bottom:6px;padding-bottom:10px;border-bottom:2px solid #f0f4ff;">
                <i class="fas fa-image me-2" style="color:#0065ff;"></i>Hero Photo
                <span style="font-size:11px;font-weight:400;color:#aaa;margin-left:8px;">Optional — auto-generates from neighbourhood name if left blank</span>
            </div>

            <?php if (!empty($nb_selected['hero_image'])): ?>
            <div style="margin-bottom:14px;border-radius:8px;overflow:hidden;position:relative;height:120px;">
                <img src="<?= htmlspecialchars($nb_selected['hero_image']) ?>" style="width:100%;height:100%;object-fit:cover;">
                <div style="position:absolute;inset:0;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;">
                    <span style="color:#fff;font-size:11px;font-weight:700;background:rgba(0,0,0,.5);padding:4px 12px;border-radius:20px;">Current custom photo</span>
                </div>
            </div>
            <?php else: ?>
            <div style="background:#f8faff;border:1.5px dashed #c0cce0;border-radius:8px;padding:16px;text-align:center;margin-bottom:14px;">
                <i class="fas fa-magic" style="color:#0065ff;font-size:20px;display:block;margin-bottom:6px;"></i>
                <div style="font-size:12px;font-weight:600;color:#555;">Auto-generating from: <em>"<?= htmlspecialchars(preg_replace('/\s+(VE|VW|RI|NV|WV|BN|BE|BS|PM|PoC)$/i', '', $nb_selected['name'])) ?> <?= explode(' ', $nb_selected['area'])[0] ?>"</em></div>
                <div style="font-size:11px;color:#aaa;margin-top:3px;">Unsplash keyword search · refreshes daily · no cost</div>
            </div>
            <?php endif; ?>

            <form method="POST" action="admin.php?tab=neighbourhoods&nb_id=<?= $nb_selected['id'] ?>">
                <input type="hidden" name="save_hpi" value="1">
                <input type="hidden" name="neighbourhood_id" value="<?= $nb_selected['id'] ?>">
                <input type="hidden" name="month_year" value="<?= date('Y-m') ?>">
                <div class="mb-2">
                    <label class="form-label">Custom photo URL <small style="color:#aaa;">(paste any image URL — Unsplash, your own server, etc.)</small></label>
                    <input type="url" name="hero_image" class="form-control" placeholder="https://images.unsplash.com/photo-..."
                           value="<?= htmlspecialchars($nb_selected['hero_image'] ?? '') ?>">
                </div>
                <div style="display:flex;align-items:center;gap:12px;margin-top:10px;">
                    <button type="submit" class="btn-save" style="width:auto;padding:8px 20px;font-size:13px;">
                        <i class="fas fa-save me-1"></i>Save Photo
                    </button>
                    <?php if (!empty($nb_selected['hero_image'])): ?>
                    <button type="submit" name="hero_image_clear" value="1" class="btn-save"
                            style="width:auto;padding:8px 20px;font-size:13px;background:#fee2e2;color:#dc2626;"
                            onclick="return confirm('Remove custom photo and revert to auto-generated?')">
                        <i class="fas fa-times me-1"></i>Remove &amp; Auto-generate
                    </button>
                    <?php endif; ?>
                    <a href="https://unsplash.com/s/photos/<?= urlencode(preg_replace('/\s+(VE|VW|RI|NV|WV|BN|BE|BS|PM|PoC)$/i', '', $nb_selected['name']) . ' ' . explode(' ', $nb_selected['area'])[0]) ?>"
                       target="_blank" style="font-size:12px;color:#0065ff;text-decoration:none;font-weight:600;">
                        <i class="fas fa-external-link-alt me-1"></i>Browse Unsplash photos
                    </a>
                </div>
            </form>
        </div>
        <?php if (!empty($nb_history)): ?>
        <div style="background:#fff;border:1px solid #eaeef5;border-radius:12px;padding:24px;">
            <div style="font-size:13px;font-weight:700;color:#002446;margin-bottom:16px;">
                <i class="fas fa-history me-2" style="color:#0065ff;"></i>Price History (Last 12 Months)
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:#f8f9fc;">
                        <th style="padding:8px 12px;text-align:left;color:#888;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #eaeef5;">Month</th>
                        <th style="padding:8px 12px;text-align:right;color:#888;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #eaeef5;">Detached</th>
                        <th style="padding:8px 12px;text-align:right;color:#888;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #eaeef5;">Condo</th>
                        <th style="padding:8px 12px;text-align:right;color:#888;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #eaeef5;">Townhouse</th>
                        <th style="padding:8px 12px;text-align:right;color:#888;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #eaeef5;">MoM</th>
                        <th style="padding:8px 12px;text-align:right;color:#888;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #eaeef5;">YoY</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($nb_history as $h): ?>
                <tr style="border-bottom:1px solid #f4f6fa;">
                    <td style="padding:9px 12px;font-weight:600;color:#002446;"><?= date('M Y', strtotime($h['month_year'])) ?></td>
                    <td style="padding:9px 12px;text-align:right;color:#555;"><?= $h['price_detached'] ? '$'.number_format($h['price_detached']) : '—' ?></td>
                    <td style="padding:9px 12px;text-align:right;color:#555;"><?= $h['price_condo']    ? '$'.number_format($h['price_condo'])    : '—' ?></td>
                    <td style="padding:9px 12px;text-align:right;color:#555;"><?= $h['price_townhouse']? '$'.number_format($h['price_townhouse'])  : '—' ?></td>
                    <td style="padding:9px 12px;text-align:right;">
                        <?php if ($h['hpi_change_mom'] !== null && $h['hpi_change_mom'] !== ''):
                            $v=(float)$h['hpi_change_mom']; $c=$v>=0?'#16a34a':'#dc2626';
                        ?><span style="color:<?= $c ?>;font-weight:700;"><?= $v>=0?'+':'' ?><?= number_format($v,1) ?>%</span><?php endif; ?>
                    </td>
                    <td style="padding:9px 12px;text-align:right;">
                        <?php if ($h['hpi_change_yoy'] !== null && $h['hpi_change_yoy'] !== ''):
                            $v=(float)$h['hpi_change_yoy']; $c=$v>=0?'#16a34a':'#dc2626';
                        ?><span style="color:<?= $c ?>;font-weight:700;"><?= $v>=0?'+':'' ?><?= number_format($v,1) ?>%</span><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- Nothing selected yet -->
        <div style="background:#fff;border:1px solid #eaeef5;border-radius:12px;padding:64px;text-align:center;color:#bbb;">
            <i class="fas fa-map-marker-alt" style="font-size:48px;display:block;margin-bottom:16px;opacity:.25;"></i>
            <p style="font-size:15px;font-weight:600;color:#888;">Select a neighbourhood from the list<br>to update its monthly HPI data.</p>
            <p style="font-size:12px;color:#aaa;margin-top:8px;">Data updates automatically refresh the neighbourhood page and extend the 12-month price trend chart.</p>
        </div>
        <?php endif; ?>

    </div>
</div>
<?php elseif ($active_tab === 'events'): ?>
<!-- ══════════════════════════════════════════════════════════
     COMMUNITY EVENTS TAB
════════════════════════════════════════════════════════════ -->
<div style="flex:1;padding:28px;">

<?php if (!empty($message)): ?>
<div class="admin-message" style="margin-bottom:20px;"><?= $message ?></div>
<?php endif; ?>

<!-- Top bar: city filter + month nav + add button -->
<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:24px;">
    <h2 style="font-size:18px;font-weight:800;color:#002446;margin:0;">Community Events</h2>

    <!-- City tabs -->
    <div style="display:flex;gap:4px;background:#f4f6fb;border-radius:8px;padding:4px;">
        <?php foreach ($cities_list as $c): ?>
        <a href="admin.php?tab=events&ev_city=<?= urlencode($c) ?>&ev_month=<?= $ev_month ?>"
           style="padding:5px 12px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;
                  background:<?= $ev_filter_city===$c?'#002446':'transparent' ?>;
                  color:<?= $ev_filter_city===$c?'#fff':'#666' ?>;">
            <?= $c ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Month navigation -->
    <div style="display:flex;align-items:center;gap:6px;margin-left:auto;">
        <?php
        $prev_month = date('Y-m', strtotime($ev_month_start . ' -1 month'));
        $next_month = date('Y-m', strtotime($ev_month_start . ' +1 month'));
        ?>
        <a href="admin.php?tab=events&ev_city=<?= urlencode($ev_filter_city) ?>&ev_month=<?= $prev_month ?>"
           style="background:#f4f6fb;color:#555;border-radius:6px;padding:6px 10px;text-decoration:none;font-size:13px;">
            <i class="fas fa-chevron-left"></i>
        </a>
        <span style="font-size:14px;font-weight:700;color:#002446;min-width:110px;text-align:center;">
            <?= date('F Y', strtotime($ev_month_start)) ?>
        </span>
        <a href="admin.php?tab=events&ev_city=<?= urlencode($ev_filter_city) ?>&ev_month=<?= $next_month ?>"
           style="background:#f4f6fb;color:#555;border-radius:6px;padding:6px 10px;text-decoration:none;font-size:13px;">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>

    <a href="admin.php?tab=events&ev_city=<?= urlencode($ev_filter_city) ?>&ev_month=<?= $ev_month ?>&ev_edit=new"
       style="background:#0065ff;color:#fff;border-radius:7px;padding:9px 18px;font-size:13px;font-weight:700;text-decoration:none;">
        <i class="fas fa-plus me-1"></i>Add Event
    </a>
</div>

<div style="display:flex;gap:24px;align-items:flex-start;">

<!-- Left: event list -->
<div style="flex:1;">

    <?php if (in_array($ev_filter_city, ['Vancouver', 'North Vancouver'])): ?>
    <!-- RSS notice -->
    <div style="background:#f0f4ff;border:1px solid #c7d9ff;border-radius:8px;padding:12px 16px;font-size:12px;color:#002446;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-rss" style="color:#0065ff;font-size:16px;"></i>
        <span><strong><?= $ev_filter_city ?></strong> events are also auto-pulled from the city RSS feed and shown on neighbourhood pages. Manual events below are shown in addition to RSS events.</span>
    </div>
    <?php else: ?>
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;font-size:12px;color:#92400e;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-pencil-alt" style="color:#f59e0b;font-size:14px;"></i>
        <span><strong><?= $ev_filter_city ?></strong> — manual entry only. Update on the 1st of each month.</span>
    </div>
    <?php endif; ?>

    <?php if (empty($events_list)): ?>
    <div style="background:#fff;border:1px solid #eaeef5;border-radius:10px;padding:48px;text-align:center;color:#aaa;">
        <i class="fas fa-calendar-alt" style="font-size:36px;display:block;margin-bottom:12px;opacity:.3;"></i>
        <p style="font-size:14px;">No events entered for <?= date('F Y', strtotime($ev_month_start)) ?> in <?= htmlspecialchars($ev_filter_city) ?>.</p>
        <a href="admin.php?tab=events&ev_city=<?= urlencode($ev_filter_city) ?>&ev_month=<?= $ev_month ?>&ev_edit=new"
           style="color:#0065ff;font-weight:700;font-size:13px;">+ Add the first event</a>
    </div>
    <?php else: ?>
    <div style="background:#fff;border:1px solid #eaeef5;border-radius:10px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f8f9fc;">
                    <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#888;border-bottom:1px solid #eaeef5;">Date</th>
                    <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#888;border-bottom:1px solid #eaeef5;">Event</th>
                    <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#888;border-bottom:1px solid #eaeef5;">Scope</th>
                    <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#888;border-bottom:1px solid #eaeef5;">Category</th>
                    <th style="padding:10px 14px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#888;border-bottom:1px solid #eaeef5;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($events_list as $ev):
                $is_past = strtotime($ev['event_date']) < strtotime('today');
            ?>
            <tr style="border-bottom:1px solid #f4f6fa;<?= $is_past?'opacity:.55;':'' ?><?= $ev_edit_id==$ev['id']?'background:#f0f4ff;':'' ?>">
                <td style="padding:10px 14px;white-space:nowrap;">
                    <span style="font-weight:700;color:#002446;"><?= date('M j', strtotime($ev['event_date'])) ?></span>
                    <?php if (!empty($ev['event_end_date']) && $ev['event_end_date'] !== $ev['event_date']): ?>
                    <span style="color:#aaa;font-size:11px;">– <?= date('M j', strtotime($ev['event_end_date'])) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($ev['event_time'])): ?>
                    <div style="font-size:11px;color:#888;"><?= htmlspecialchars($ev['event_time']) ?></div>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 14px;">
                    <div style="font-weight:600;color:#002446;"><?= htmlspecialchars($ev['title']) ?></div>
                    <?php if (!empty($ev['location_name'])): ?>
                    <div style="font-size:11px;color:#888;"><i class="fas fa-map-marker-alt" style="color:#0065ff;margin-right:3px;"></i><?= htmlspecialchars($ev['location_name']) ?></div>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 14px;">
                    <?php if (empty($ev['neighbourhood_id'])): ?>
                    <span style="background:#dbeafe;color:#1d4ed8;font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;">City-wide</span>
                    <?php else: ?>
                    <span style="background:#dcfce7;color:#15803d;font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;"><?= htmlspecialchars($ev['nb_name'] ?? 'Neighbourhood') ?></span>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 14px;">
                    <span style="background:#f4f6fb;color:#555;font-size:10px;font-weight:600;padding:3px 8px;border-radius:20px;text-transform:capitalize;"><?= htmlspecialchars($ev['category']) ?></span>
                </td>
                <td style="padding:10px 14px;text-align:center;white-space:nowrap;">
                    <a href="admin.php?tab=events&ev_city=<?= urlencode($ev_filter_city) ?>&ev_month=<?= $ev_month ?>&ev_edit=<?= $ev['id'] ?>"
                       class="btn-edit" style="font-size:11px;padding:4px 10px;">Edit</a>
                    <a href="admin.php?tab=events&ev_toggle=<?= $ev['id'] ?>&ev_city=<?= urlencode($ev_filter_city) ?>&ev_month=<?= $ev_month ?>"
                       class="btn-toggle" style="font-size:11px;padding:4px 10px;" title="<?= $ev['is_active']?'Hide':'Show' ?>">
                        <i class="fas fa-<?= $ev['is_active']?'eye-slash':'eye' ?>"></i>
                    </a>
                    <a href="admin.php?tab=events&ev_delete=<?= $ev['id'] ?>&ev_city=<?= urlencode($ev_filter_city) ?>&ev_month=<?= $ev_month ?>"
                       class="btn-del" style="font-size:11px;padding:4px 8px;"
                       onclick="return confirm('Delete this event?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p style="font-size:11px;color:#aaa;margin-top:8px;"><?= count($events_list) ?> event(s) for <?= date('F Y', strtotime($ev_month_start)) ?> · Faded rows are past events this month</p>
    <?php endif; ?>

</div><!-- /event list -->

<!-- Right: Add/Edit form -->
<?php if (isset($_GET['ev_edit'])): ?>
<div style="width:380px;flex-shrink:0;background:#fff;border:1px solid #eaeef5;border-radius:12px;padding:22px;position:sticky;top:80px;">

    <?php
    $is_edit_mode = $ev_edit !== null;
    $form_title   = $is_edit_mode ? 'Edit Event' : 'Add Event';
    $def          = $ev_edit ?? [];
    ?>
    <div style="font-size:15px;font-weight:800;color:#002446;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #f0f4ff;">
        <i class="fas fa-calendar-plus me-2" style="color:#0065ff;"></i><?= $form_title ?>
    </div>

    <form method="POST" action="admin.php?tab=events&ev_city=<?= urlencode($ev_filter_city) ?>&ev_month=<?= $ev_month ?>">
        <input type="hidden" name="save_event" value="1">
        <input type="hidden" name="event_id" value="<?= $is_edit_mode ? $ev_edit['id'] : 0 ?>">

        <div class="mb-3">
            <label class="form-label">Event Title <span style="color:#dc2626;">*</span></label>
            <input type="text" name="ev_title" class="form-control" required
                   value="<?= htmlspecialchars($def['title'] ?? '') ?>" placeholder="e.g. Renfrew Community Fair">
        </div>

        <div class="row g-2 mb-3">
            <div class="col-7">
                <label class="form-label">Start Date <span style="color:#dc2626;">*</span></label>
                <input type="date" name="ev_date" class="form-control" required
                       value="<?= htmlspecialchars($def['event_date'] ?? $ev_month_start) ?>">
            </div>
            <div class="col-5">
                <label class="form-label">Time <small style="color:#aaa;">(opt.)</small></label>
                <input type="text" name="ev_time" class="form-control" placeholder="e.g. 10am–3pm"
                       value="<?= htmlspecialchars($def['event_time'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">End Date <small style="color:#aaa;">(for multi-day events)</small></label>
            <input type="date" name="ev_end_date" class="form-control"
                   value="<?= htmlspecialchars($def['event_end_date'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">City</label>
            <select name="ev_city" class="form-select">
                <?php foreach ($cities_list as $c): ?>
                <option value="<?= $c ?>" <?= ($def['city'] ?? $ev_filter_city)===$c?'selected':'' ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Scope</label>
            <select name="ev_nb_id" class="form-select">
                <option value="">City-wide — appears on all <?= htmlspecialchars($ev_filter_city) ?> pages</option>
                <?php foreach ($neighbourhoods as $nb):
                    $nb_city_map = [
                        'Vancouver East'=>'Vancouver','Vancouver West'=>'Vancouver',
                        'North Vancouver'=>'North Vancouver','Burnaby'=>'Burnaby',
                        'Richmond'=>'Richmond','West Vancouver'=>'West Vancouver',
                        'New Westminster'=>'New Westminster','Coquitlam'=>'Coquitlam',
                        'Port Coquitlam'=>'Port Coquitlam','Port Moody'=>'Port Moody',
                    ];
                    if (($nb_city_map[$nb['area']] ?? '') !== $ev_filter_city) continue;
                ?>
                <option value="<?= $nb['id'] ?>" <?= ($def['neighbourhood_id'] ?? '')==$nb['id']?'selected':'' ?>>
                    <?= htmlspecialchars($nb['name']) ?> only
                </option>
                <?php endforeach; ?>
            </select>
            <div style="font-size:10px;color:#aaa;margin-top:3px;">City-wide events appear on every neighbourhood page in that city</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="ev_category" class="form-select">
                <?php foreach ($categories_list as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= ($def['category'] ?? 'community')===$val?'selected':'' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Location Name <small style="color:#aaa;">(opt.)</small></label>
            <input type="text" name="ev_location" class="form-control" placeholder="e.g. Renfrew Community Centre"
                   value="<?= htmlspecialchars($def['location_name'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Description <small style="color:#aaa;">(opt.)</small></label>
            <textarea name="ev_description" class="form-control" rows="2"
                      placeholder="1–2 sentences about the event"><?= htmlspecialchars($def['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-4">
            <label class="form-label">Link / URL <small style="color:#aaa;">(opt.)</small></label>
            <input type="url" name="ev_url" class="form-control" placeholder="https://..."
                   value="<?= htmlspecialchars($def['url'] ?? '') ?>">
        </div>

        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn-save" style="flex:1;padding:10px;">
                <i class="fas fa-save me-1"></i><?= $is_edit_mode ? 'Save Changes' : 'Add Event' ?>
            </button>
            <a href="admin.php?tab=events&ev_city=<?= urlencode($ev_filter_city) ?>&ev_month=<?= $ev_month ?>"
               style="background:#f4f6fb;color:#666;border-radius:8px;padding:10px 14px;text-decoration:none;font-size:13px;font-weight:600;display:flex;align-items:center;">
                Cancel
            </a>
        </div>
    </form>
</div>
<?php endif; ?>

</div><!-- /flex row -->
</div><!-- /events tab -->


<?php elseif ($active_tab === 'imports'): ?>
<div style="flex:1;padding:28px;max-width:900px;">

<?php if (!empty($message)): ?>
<div class="admin-message" style="margin-bottom:20px;"><?= $message ?></div>
<?php endif; ?>

<div style="margin-bottom:28px;">
    <h2 style="font-size:20px;font-weight:800;color:#002446;margin:0 0 4px;">Data Import Centre</h2>
    <p style="font-size:13px;color:#888;margin:0;">All COV open data, TransLink, and constraint imports in one place.</p>
</div>

<?php
$sections = [
    ['id'=>1,'icon'=>'fa-city','bg'=>'#e0f2fe','ic'=>'#0369a1','title'=>'COV Property Tax Data','sub'=>'plex_properties — R1-1 lot dimensions, zoning, assessed values','freq'=>'Annual — January'],
    ['id'=>2,'icon'=>'fa-train','bg'=>'#fee2e2','ic'=>'#dc2626','title'=>'TransLink FTN Stops','sub'=>'transit_stops — SkyTrain + frequent bus network stations','freq'=>'Annual'],
    ['id'=>3,'icon'=>'fa-road','bg'=>'#f0fdf4','ic'=>'#16a34a','title'=>'COV Lane Segments','sub'=>'lane_segments — rear lane centroids for lane_access spatial check','freq'=>'Annual'],
    ['id'=>4,'icon'=>'fa-star','bg'=>'#fefce8','ic'=>'#ca8a04','title'=>'COV Building Permits','sub'=>'A_Permit_2026 — approved building permits (gold star pins on map)','freq'=>'Monthly'],
    ['id'=>5,'icon'=>'fa-landmark','bg'=>'#eff6ff','ic'=>'#1d4ed8','title'=>'Heritage Register','sub'=>'plex_properties.heritage_category — Category A, B, C designation','freq'=>'Semi-annual'],
    ['id'=>6,'icon'=>'fa-layer-group','bg'=>'#fef3c7','ic'=>'#92400e','title'=>'Peat Zone','sub'=>'plex_properties.peat_zone — Vancouver peat bog zones','freq'=>'One-time'],
    ['id'=>7,'icon'=>'fa-water','bg'=>'#eff6ff','ic'=>'#2563eb','title'=>'Floodplain','sub'=>'plex_properties.floodplain_risk — COV designated + Still Creek','freq'=>'One-time'],
    ['id'=>8,'icon'=>'fa-map','bg'=>'#f0fdf4','ic'=>'#16a34a','title'=>'Neighbourhood Boundaries','sub'=>'plex_properties.neighbourhood_slug — fixes all 64,000 lot assignments','freq'=>'Annual'],
];
foreach ($sections as $s):
?>
<div style="background:#fff;border-radius:12px;border:1px solid #e8e4dd;margin-bottom:16px;overflow:hidden;">
    <div style="display:flex;align-items:center;gap:14px;padding:18px 22px;border-bottom:1px solid #f0f0f0;cursor:pointer;user-select:none;" onclick="impToggle(<?= $s['id'] ?>)">
        <div style="width:38px;height:38px;border-radius:9px;background:<?= $s['bg'] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas <?= $s['icon'] ?>" style="color:<?= $s['ic'] ?>;font-size:16px;"></i>
        </div>
        <div style="flex:1;">
            <div style="font-size:15px;font-weight:700;color:#002446;"><?= $s['title'] ?></div>
            <div style="font-size:12px;color:#888;margin-top:2px;"><?= $s['sub'] ?></div>
        </div>
        <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:12px;background:#f1f5f9;color:#475569;margin-right:10px;white-space:nowrap;"><?= $s['freq'] ?></span>
        <i class="fas fa-chevron-down" id="chev-<?= $s['id'] ?>" style="color:#aaa;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div id="imp-body-<?= $s['id'] ?>" style="display:none;">
        <div style="padding:22px;">

<?php if ($s['id']===1): ?>
            <div style="font-size:12px;color:#666;background:#f9f6f0;border-radius:6px;padding:10px 14px;margin-bottom:12px;border-left:3px solid #c9a84c;line-height:1.6;">
                Source: <code style="background:#e8e4dd;padding:1px 6px;border-radius:4px;">opendata.vancouver.ca</code> → <strong>property-tax-report</strong> → filter REPORT_YEAR = current year → Export CSV<br>
                Then download <strong>property-parcel-polygons.csv</strong> (no filter needed) for coordinates.
            </div>
            <form id="frm-1a" onsubmit="return false;">
                <input type="hidden" name="import_cov_csv" value="1">
                <div style="margin-bottom:16px;">
                    <label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;display:block;margin-bottom:5px;">property-tax-report.csv</label>
                    <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
                        <input type="file" name="cov_csv" accept=".csv" required style="flex:1;padding:9px 12px;border:1.5px dashed #c9a84c;border-radius:8px;background:#fffbf0;font-size:13px;">
                        <button type="button" onclick="doImport('1a')" style="background:#002446;color:#c9a84c;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">Import Tax Data</button>
                    </div>
                </div>
            </form>
            <form id="frm-1b" onsubmit="return false;">
                <input type="hidden" name="import_parcel_polygons" value="1">
                <div style="margin-bottom:16px;">
                    <label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;display:block;margin-bottom:5px;">property-parcel-polygons.csv</label>
                    <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
                        <input type="file" name="parcel_csv" accept=".csv" required style="flex:1;padding:9px 12px;border:1.5px dashed #c9a84c;border-radius:8px;background:#fffbf0;font-size:13px;">
                        <button type="button" onclick="doImport('1b')" style="background:#002446;color:#c9a84c;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">Import Parcel Polygons</button>
                    </div>
                </div>
            </form>
            <div id="res-1a" style="display:none;margin-top:4px;margin-bottom:12px;font-size:12px;padding:12px;border-radius:8px;font-family:monospace;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></div>
            <div id="res-1b" style="display:none;margin-top:4px;margin-bottom:12px;font-size:12px;padding:12px;border-radius:8px;font-family:monospace;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></div>
            <div style="background:#f0f4f8;border-radius:6px;padding:12px 16px;font-size:12px;color:#666;">
                <strong style="color:#002446;">Large files?</strong> Run via SSH on Hostinger instead:<br>
                <code style="font-size:11px;line-height:2;color:#002446;">
                    php /home/u990588858/domains/wynston.ca/public_html/import_cov_csv.php<br>
                    php /home/u990588858/domains/wynston.ca/public_html/import_parcel_polygons.php
                </code>
            </div>

<?php elseif ($s['id']===2): ?>
            <div style="font-size:12px;color:#666;background:#f9f6f0;border-radius:6px;padding:10px 14px;margin-bottom:12px;border-left:3px solid #c9a84c;line-height:1.6;">
                Source: translink.ca GTFS → Download zip → extract <code style="background:#e8e4dd;padding:1px 6px;border-radius:4px;">stops.txt</code><br>
                Columns needed: stop_id, stop_name, stop_lat, stop_lon
            </div>
            <form id="frm-2" onsubmit="return false;">
                <input type="hidden" name="import_transit" value="1">
                <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
                    <div style="flex:1;"><label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;display:block;margin-bottom:5px;">stops.txt</label>
                    <input type="file" name="transit_csv" accept=".txt,.csv" required style="width:100%;padding:9px 12px;border:1.5px dashed #c9a84c;border-radius:8px;background:#fffbf0;font-size:13px;"></div>
                    <button type="button" onclick="doImport(2)" style="background:#002446;color:#c9a84c;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">Import</button>
                </div>
            </form>
            <div id="res-2" style="display:none;margin-top:12px;font-size:12px;padding:12px;border-radius:8px;font-family:monospace;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></div>

<?php elseif ($s['id']===3): ?>
            <div style="font-size:12px;color:#666;background:#f9f6f0;border-radius:6px;padding:10px 14px;margin-bottom:12px;border-left:3px solid #c9a84c;line-height:1.6;">
                Source: <code style="background:#e8e4dd;padding:1px 6px;border-radius:4px;">opendata.vancouver.ca</code> → search <strong>"lanes"</strong> → Export CSV or GeoJSON<br>
                Columns needed: latitude, longitude (centroids)
            </div>
            <form id="frm-3" onsubmit="return false;">
                <input type="hidden" name="import_lanes" value="1">
                <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
                    <div style="flex:1;"><label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;display:block;margin-bottom:5px;">Lanes CSV or GeoJSON</label>
                    <input type="file" name="lanes_csv" accept=".csv,.geojson,.json" required style="width:100%;padding:9px 12px;border:1.5px dashed #c9a84c;border-radius:8px;background:#fffbf0;font-size:13px;"></div>
                    <button type="button" onclick="doImport(3)" style="background:#002446;color:#c9a84c;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">Import</button>
                </div>
            </form>
            <div id="res-3" style="display:none;margin-top:12px;font-size:12px;padding:12px;border-radius:8px;font-family:monospace;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></div>

<?php elseif ($s['id']===4): ?>
            <div style="font-size:12px;color:#666;background:#f9f6f0;border-radius:6px;padding:10px 14px;margin-bottom:12px;border-left:3px solid #c9a84c;line-height:1.6;">
                Source: <code style="background:#e8e4dd;padding:1px 6px;border-radius:4px;">opendata.vancouver.ca</code> → <strong>issued-building-permits</strong> → filter New Building → Export CSV
            </div>
            <form id="frm-4" onsubmit="return false;">
                <input type="hidden" name="import_permits" value="1">
                <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
                    <div style="flex:1;"><label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;display:block;margin-bottom:5px;">Building Permits CSV</label>
                    <input type="file" name="permits_csv" accept=".csv" required style="width:100%;padding:9px 12px;border:1.5px dashed #c9a84c;border-radius:8px;background:#fffbf0;font-size:13px;"></div>
                    <button type="button" onclick="doImport(4)" style="background:#002446;color:#c9a84c;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">Import</button>
                </div>
            </form>
            <div id="res-4" style="display:none;margin-top:12px;font-size:12px;padding:12px;border-radius:8px;font-family:monospace;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></div>
            <div style="margin-top:14px;background:#f9f6f0;border-radius:8px;padding:14px;">
                <div style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Run after import — refresh has_active_permit flags</div>
                <code style="font-size:11px;display:block;line-height:1.8;color:#002446;">UPDATE plex_properties SET has_active_permit = 0;<br>UPDATE plex_properties p INNER JOIN A_Permit_2026 a ON ABS(p.lat - a.latitude) &lt; 0.0005 AND ABS(p.lng - a.longitude) &lt; 0.0005 SET p.has_active_permit = 1;</code>
            </div>

<?php elseif ($s['id']===5): ?>
            <div style="font-size:12px;color:#666;background:#f9f6f0;border-radius:6px;padding:10px 14px;margin-bottom:12px;border-left:3px solid #c9a84c;line-height:1.6;">
                Source: <code style="background:#e8e4dd;padding:1px 6px;border-radius:4px;">opendata.vancouver.ca</code> → search <strong>"heritage register"</strong> → Export CSV<br>
                Columns needed: ADDRESS and CATEGORY (A / B / C). Resets all lots to 'none' first.
            </div>
            <form id="frm-5" onsubmit="return false;">
                <input type="hidden" name="import_heritage" value="1">
                <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
                    <div style="flex:1;"><label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;display:block;margin-bottom:5px;">Heritage Register CSV</label>
                    <input type="file" name="heritage_csv" accept=".csv" required style="width:100%;padding:9px 12px;border:1.5px dashed #c9a84c;border-radius:8px;background:#fffbf0;font-size:13px;"></div>
                    <button type="button" onclick="doImport(5)" style="background:#002446;color:#c9a84c;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">Import</button>
                </div>
            </form>
            <div id="res-5" style="display:none;margin-top:12px;font-size:12px;padding:12px;border-radius:8px;font-family:monospace;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></div>

<?php elseif ($s['id']===6): ?>
            <div style="font-size:12px;color:#666;background:#f9f6f0;border-radius:6px;padding:10px 14px;margin-bottom:12px;border-left:3px solid #c9a84c;line-height:1.6;">
                Source: <code style="background:#e8e4dd;padding:1px 6px;border-radius:4px;">opendata.vancouver.ca</code> → search <strong>"soil survey"</strong> → Export GeoJSON<br>
                Spatial check against all 64,000 lots. Takes 30–60 seconds.<br>
                Or use built-in approximate zones (Cambie/Marpole, Knight/Kensington, South Vancouver).
            </div>
            <form id="frm-6" onsubmit="return false;">
                <input type="hidden" name="import_peat" value="1">
                <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
                    <div style="flex:1;"><label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;display:block;margin-bottom:5px;">Peat Zone GeoJSON (optional)</label>
                    <input type="file" name="peat_geojson" accept=".geojson,.json" style="width:100%;padding:9px 12px;border:1.5px dashed #c9a84c;border-radius:8px;background:#fffbf0;font-size:13px;"></div>
                    <button type="button" onclick="doImport(6)" style="background:#002446;color:#c9a84c;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">Import GeoJSON</button>
                </div>
                <div style="text-align:center;color:#aaa;font-size:12px;margin-bottom:10px;">— or —</div>
                <button type="button" onclick="doImport(6,{peat_builtin:'1'})" style="background:#f9f6f0;color:#002446;border:1px solid #e8e4dd;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;width:100%;">Use Built-in Approximate Zones</button>
            </form>
            <div id="res-6" style="display:none;margin-top:12px;font-size:12px;padding:12px;border-radius:8px;font-family:monospace;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></div>



<?php elseif ($s['id']===7): ?>
            <div style="font-size:12px;color:#666;background:#f9f6f0;border-radius:6px;padding:10px 14px;margin-bottom:12px;border-left:3px solid #c9a84c;line-height:1.6;">
                Upload the combined floodplain GeoJSON (COV Designated Floodplain + Still Creek).<br>
                Already downloaded from VanMap — layer IDs 186 and 33.<br>
                Spatial check against all 64,000 lots. Takes ~30 seconds.
            </div>
            <form id="frm-7" onsubmit="return false;">
                <input type="hidden" name="import_floodplain" value="1">
                <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
                    <div style="flex:1;"><label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;display:block;margin-bottom:5px;">Floodplain GeoJSON</label>
                    <input type="file" name="flood_geojson" accept=".geojson,.json" required style="width:100%;padding:9px 12px;border:1.5px dashed #c9a84c;border-radius:8px;background:#fffbf0;font-size:13px;"></div>
                    <button type="button" onclick="doImport(7)" style="background:#002446;color:#c9a84c;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">Import</button>
                </div>
            </form>
            <div id="res-7" style="display:none;margin-top:12px;font-size:12px;padding:12px;border-radius:8px;font-family:monospace;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></div>

<?php elseif ($s['id']===8): ?>
            <div style="font-size:12px;color:#666;background:#f9f6f0;border-radius:6px;padding:10px 14px;margin-bottom:12px;border-left:3px solid #c9a84c;line-height:1.6;">
                Source: <code style="background:#e8e4dd;padding:1px 6px;border-radius:4px;">opendata.vancouver.ca</code> → <strong>local-area-boundary</strong> → Export CSV<br>
                Updates <code style="background:#e8e4dd;padding:1px 6px;border-radius:4px;">neighbourhood_slug</code> for all 64,000 lots using exact COV polygon boundaries.<br>
                Replaces <code style="background:#e8e4dd;padding:1px 6px;border-radius:4px;">nb_012</code> format with <code style="background:#e8e4dd;padding:1px 6px;border-radius:4px;">renfrew-collingwood</code> — improves market data joins and Outlook accuracy.
            </div>
            <form id="frm-8" onsubmit="return false;">
                <input type="hidden" name="import_neighbourhood_boundary" value="1">
                <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
                    <div style="flex:1;"><label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;display:block;margin-bottom:5px;">local-area-boundary.csv</label>
                    <input type="file" name="boundary_csv" accept=".csv" required style="width:100%;padding:9px 12px;border:1.5px dashed #c9a84c;border-radius:8px;background:#fffbf0;font-size:13px;"></div>
                    <button type="button" onclick="doImport(8)" style="background:#002446;color:#c9a84c;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">Import</button>
                </div>
            </form>
            <div id="res-8" style="display:none;margin-top:12px;font-size:12px;padding:12px;border-radius:8px;font-family:monospace;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></div>

<?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

</div>

<script>
window.impToggle = function(id) {
    var el = document.getElementById('imp-body-' + id);
    var ch = document.getElementById('chev-' + id);
    if (!el) { alert('imp-body-' + id + ' not found'); return; }
    var open = el.style.display === 'block';
    el.style.display = open ? 'none' : 'block';
    if (ch) ch.style.transform = open ? '' : 'rotate(180deg)';
};

window.doImport = async function(id, extra) {
    var form = document.getElementById('frm-' + id);
    var res  = document.getElementById('res-' + id);
    if (!form || !res) return;
    res.style.display = 'block';
    res.style.background = '#f0f4f8';
    res.style.color = '#333';
    res.textContent = 'Running... please wait.';
    var fd = new FormData(form);
    if (extra) Object.keys(extra).forEach(function(k){ fd.append(k, extra[k]); });
    try {
        var r = await fetch('admin.php?tab=imports', { method:'POST', body:fd });
        var d = await r.json();
        if (d.success) {
            res.style.background = '#f0fdf4'; res.style.color = '#166534';
            var msg = 'Done!\n';
            if (d.imported    != null) msg += 'Imported: ' + d.imported + '\n';
            if (d.matched     != null) msg += 'Matched: '  + d.matched  + '\n';
            if (d.skipped     != null) msg += 'Skipped: '  + d.skipped  + '\n';
            if (d.flagged     != null) msg += 'Flagged: '  + d.flagged  + '\n';
            if (d.high_risk   != null) msg += 'High risk: '+ d.high_risk+ '\n';
            if (d.low_risk    != null) msg += 'Low risk: ' + d.low_risk + '\n';
            if (d.lots_checked!= null) msg += 'Lots checked: ' + d.lots_checked + '\n';
            if (d.log && d.log.length) msg += '\n' + d.log.join('\n');
            res.textContent = msg;
        } else {
            res.style.background = '#fef2f2'; res.style.color = '#991b1b';
            res.textContent = 'Error: ' + (d.error || 'Unknown');
        }
    } catch(e) {
        res.style.background = '#fef2f2'; res.style.color = '#991b1b';
        res.textContent = 'Request failed: ' + e.message;
    }
};
</script>

<?php endif; // end tab switch ?>
</div><!-- /admin-body -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Preview a photo file in its slot
function previewPhoto(input, slot) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById('slot-preview-' + slot);
        preview.innerHTML =
            '<img src="' + e.target.result + '" alt="Photo ' + slot + '">' +
            '<button type="button" class="photo-remove-btn" onclick="clearSlot(' + slot + ')" title="Remove">×</button>';
        // Clear the hidden value so old URL is not kept
        document.getElementById('img' + slot + '_val').value = '';
    };
    reader.readAsDataURL(input.files[0]);
}

// Clear a photo slot (removes image, clears hidden value)
function clearSlot(slot) {
    document.getElementById('slot-preview-' + slot).innerHTML =
        '<div class="photo-empty-slot" onclick="document.getElementById(\'img' + slot + '_file\').click()">' +
        '<i class="fas fa-plus"></i><span>Photo ' + slot + '</span></div>';
    document.getElementById('img' + slot + '_val').value = '';
    document.getElementById('img' + slot + '_file').value = '';
}

// Preview a floor plan / generic file
function previewFile(input, zoneId, previewId) {
    if (!input.files || !input.files[0]) return;
    var name = input.files[0].name;
    document.getElementById(previewId).textContent = '✅ Selected: ' + name;
    document.getElementById(zoneId).style.borderColor = '#16a34a';
    document.getElementById(zoneId).style.background  = '#f0fdf4';
}

// ── Bulk Import — drag & drop + save ──────────────────────────────────────────
(function() {
    var dz = document.getElementById('bulkDropZone');
    if (!dz) return;
    ['dragenter','dragover'].forEach(function(ev) {
        dz.addEventListener(ev, function(e) { e.preventDefault(); dz.classList.add('dz-active'); });
    });
    dz.addEventListener('dragleave', function() { dz.classList.remove('dz-active'); });
    dz.addEventListener('drop', function(e) {
        e.preventDefault(); dz.classList.remove('dz-active');
        var fi = document.getElementById('bulk_csv');
        fi.files = e.dataTransfer.files;
        if (fi.files[0]) {
            document.getElementById('bulkDropText').textContent = '✅ ' + fi.files[0].name;
            document.getElementById('bulkDropIcon').className = 'fas fa-check-circle dz-icon';
            document.getElementById('bulkDropIcon').style.color = '#16a34a';
        }
    });
    document.getElementById('bulk_csv').addEventListener('change', function() {
        if (this.files[0]) {
            document.getElementById('bulkDropText').textContent = '✅ ' + this.files[0].name;
            document.getElementById('bulkDropIcon').className = 'fas fa-check-circle dz-icon';
            document.getElementById('bulkDropIcon').style.color = '#16a34a';
        }
    });
})();

// Select all / deselect
var bulkCheckAll = document.getElementById('bulkCheckAll');
if (bulkCheckAll) {
    bulkCheckAll.addEventListener('change', function() {
        document.querySelectorAll('.bulk-row-cb').forEach(function(cb) { cb.checked = bulkCheckAll.checked; });
    });
}

function doBulkSave() {
    var rows = window._bulkRows || [];
    var result = [];
    document.querySelectorAll('.bulk-row-cb').forEach(function(cb) {
        if (!cb.checked) return;
        var i = parseInt(cb.dataset.idx);
        var r = Object.assign({}, rows[i]);
        var sel = document.getElementById('bulk_manual_' + i);
        if (sel && sel.value) r.nb_id = parseInt(sel.value);
        if (r.nb_id) result.push(r);
    });
    if (!result.length) { alert('No rows selected or matched. Check the rows you want to save.'); return; }
    document.getElementById('bulk_rows_json').value = JSON.stringify(result);
    document.getElementById('bulkConfirmForm').submit();
}
</script>

</body>
</html>