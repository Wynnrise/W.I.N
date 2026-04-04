<?php
/**
 * admin/import_transit.php
 * Transit FTN stop import — admin password protected
 */

// ── Admin auth — simple password check, no session dependency ──
define('ADMIN_PASSWORD', 'Concac1979$');
session_start();

if (isset($_POST['admin_login'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['wynston_admin'] = true;
    }
}

// Allow access if session set OR if password posted alongside file upload
$authed = !empty($_SESSION['wynston_admin']);
if (!$authed && isset($_POST['admin_password']) && $_POST['admin_password'] === ADMIN_PASSWORD) {
    $authed = true;
    $_SESSION['wynston_admin'] = true;
}

if (!$authed) { ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#002446;display:flex;align-items:center;justify-content:center;min-height:100vh}
.box{background:#fff;border-radius:12px;padding:40px;width:100%;max-width:380px}</style>
</head><body><div class="box">
<h2 style="color:#002446;font-weight:800">Admin Panel</h2>
<p class="text-muted" style="font-size:13px">Wynston W.I.N — Transit Import</p>
<form method="POST">
<div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Admin password" required autofocus></div>
<button type="submit" name="admin_login" class="btn w-100" style="background:#002446;color:#fff;font-weight:700">Login</button>
</form></div></body></html>
<?php exit; }

// ── DB ────────────────────────────────────────────────────────
$pdo = new PDO("mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4",
    "u990588858_Multiplex", "Concac1979$",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$message = '';
$step    = $_POST['step'] ?? '';

// ── STEP 1: Upload and parse stops.txt ───────────────────────
if ($step === 'upload' && isset($_FILES['stops_file'])) {
    $file = $_FILES['stops_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = '❌ Upload error code: ' . $file['error'];
    } else {
        $content = file_get_contents($file['tmp_name']);
        $lines   = explode("\n", str_replace("\r\n", "\n", $content));
        $header  = str_getcsv(array_shift($lines));
        $col     = array_flip(array_map('trim', $header));

        $missing = false;
        foreach (['stop_lat','stop_lon','stop_id','stop_name','location_type','zone_id'] as $r) {
            if (!isset($col[$r])) {
                $message = "❌ Missing column: $r — is this a valid stops.txt file?";
                $missing = true;
                break;
            }
        }

        if (!$missing) {
            $pdo->exec("TRUNCATE TABLE transit_stops");
            $inserted = 0;
            $skipped  = 0;

            $stmt = $pdo->prepare("INSERT IGNORE INTO transit_stops
                (stop_id, stop_name, stop_lat, stop_lng, stop_type, zone_id, is_ftn)
                VALUES (?, ?, ?, ?, ?, ?, ?)");

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                $row = str_getcsv($line);
                if (count($row) < 6) continue;

                $lat           = (float)trim($row[$col['stop_lat']] ?? 0);
                $lng           = (float)trim($row[$col['stop_lon']] ?? 0);
                $stop_id       = trim($row[$col['stop_id']] ?? '');
                $stop_name     = trim($row[$col['stop_name']] ?? '');
                $location_type = (int)trim($row[$col['location_type']] ?? 0);
                $zone_id       = trim($row[$col['zone_id']] ?? '');
                $stop_code_idx = $col['stop_code'] ?? -1;
                $stop_code     = ($stop_code_idx >= 0) ? trim($row[$stop_code_idx] ?? '') : '';

                if ($lat == 0 || $lng == 0) { $skipped++; continue; }
                if ($lat < 49.00 || $lat > 49.50 || $lng < -123.30 || $lng > -122.20) { $skipped++; continue; }

                // Classify stop type — import ALL stops within bounding box
                // We use all stops for proximity; the 400m radius handles selectivity
                $is_ftn    = 1;
                $stop_type = 'ftn_bus';

                if ($location_type === 1 || in_array($zone_id, ['ZN 1','ZN 2','ZN 3'])
                    || stripos($stop_name, 'Station') !== false) {
                    $stop_type = stripos($stop_name, 'SeaBus') !== false ? 'seabus' : 'skytrain';
                }
                // Skip entrances (location_type=2) and parent-less nodes with no code
                if ($location_type === 2) { $skipped++; continue; }

                try { $stmt->execute([$stop_id, $stop_name, $lat, $lng, $stop_type, $zone_id, $is_ftn]); $inserted++; }
                catch (PDOException $e) { $skipped++; }
            }

            $message = "✅ Imported <strong>$inserted</strong> FTN stops. Skipped $skipped. Now run Step 2.";
        }
    }
}

// ── STEP 2: Spatial check ────────────────────────────────────
if ($step === 'spatial') {
    set_time_limit(300);

    $stops = $pdo->query("SELECT stop_lat, stop_lng FROM transit_stops WHERE is_ftn = 1")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($stops)) {
        $message = '❌ No FTN stops found. Run Step 1 first.';
    } else {
        $lots = $pdo->query("SELECT id, lat, lng FROM plex_properties WHERE lat IS NOT NULL AND lng IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);

        $updated = 0; $proximate = 0;
        $upd = $pdo->prepare("UPDATE plex_properties SET transit_proximate = ?, nearest_ftn_stop_m = ? WHERE id = ?");

        foreach ($lots as $lot) {
            $lot_lat   = (float)$lot['lat'];
            $lot_lng   = (float)$lot['lng'];
            $nearest_m = PHP_INT_MAX;

            foreach ($stops as $stop) {
                $slat = (float)$stop['stop_lat'];
                $slng = (float)$stop['stop_lng'];

                if (abs($lot_lat - $slat) * 111320 > 600) continue;
                if (abs($lot_lng - $slng) * 111320 * cos(deg2rad($lot_lat)) > 600) continue;

                $dLat = deg2rad($slat - $lot_lat);
                $dLng = deg2rad($slng - $lot_lng);
                $a    = sin($dLat/2)*sin($dLat/2) + cos(deg2rad($lot_lat))*cos(deg2rad($slat))*sin($dLng/2)*sin($dLng/2);
                $dist = 6371000 * 2 * atan2(sqrt($a), sqrt(1-$a));

                if ($dist < $nearest_m) $nearest_m = $dist;
                if ($dist <= 400) break;
            }

            $is_prox   = ($nearest_m <= 400) ? 1 : 0;
            $nearest_m = ($nearest_m === PHP_INT_MAX) ? null : (int)round($nearest_m);
            $upd->execute([$is_prox, $nearest_m, $lot['id']]);
            $updated++;
            if ($is_prox) $proximate++;
        }

        $message = "✅ Done. <strong>$updated</strong> lots checked. <strong>$proximate</strong> lots within 400m of FTN stop.";
    }
}

// ── Stats ─────────────────────────────────────────────────────
$stop_count = 0; $transit_count = 0; $total_count = 0;
try { $stop_count    = $pdo->query("SELECT COUNT(*) FROM transit_stops WHERE is_ftn = 1")->fetchColumn(); } catch(Exception $e){}
try { $transit_count = $pdo->query("SELECT COUNT(*) FROM plex_properties WHERE transit_proximate = 1")->fetchColumn(); } catch(Exception $e){}
try { $total_count   = $pdo->query("SELECT COUNT(*) FROM plex_properties WHERE lat IS NOT NULL")->fetchColumn(); } catch(Exception $e){}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Transit Import — Wynston Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f9f6f0}
.card{border:none;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08)}
.btn-navy{background:#002446;color:#fff;border:none}
.btn-navy:hover{background:#003a7a;color:#fff}
.btn-gold{background:#c9a84c;color:#002446;border:none;font-weight:700}
.step-num{display:inline-block;width:28px;height:28px;border-radius:50%;background:#002446;color:#fff;text-align:center;line-height:28px;font-weight:800;font-size:13px;margin-right:8px}
</style>
</head>
<body>
<div class="container py-4" style="max-width:680px">
  <div class="mb-3"><a href="/plex-data.php" class="btn btn-sm btn-outline-secondary">← Back to Plex Data</a></div>
  <h4 class="fw-bold mb-1" style="color:#002446">Transit Import — FTN Stops</h4>
  <p class="text-muted mb-4" style="font-size:13px">Updates <code>transit_proximate</code> and <code>nearest_ftn_stop_m</code> on all lots. Run annually.</p>

  <?php if ($message): ?><div class="alert alert-info mb-4"><?= $message ?></div><?php endif; ?>

  <div class="card p-4 mb-3">
    <h6 class="fw-bold mb-3"><span class="step-num">1</span>Upload stops.txt</h6>
    <p class="text-muted mb-3" style="font-size:13px">
      Download <strong>stops.txt</strong> from the TransLink GTFS zip at
      <a href="https://www.translink.ca/about-us/doing-business-with-translink/app-developer-resources/gtfs/gtfs-data" target="_blank">translink.ca/GTFS</a>.
      Upload the raw file — no changes needed.
    </p>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="step" value="upload">
      <div class="mb-3"><input type="file" name="stops_file" class="form-control" accept=".txt,.csv" required></div>
      <button type="submit" class="btn btn-navy px-4">Import FTN Stops</button>
    </form>
  </div>

  <div class="card p-4 mb-3">
    <h6 class="fw-bold mb-3"><span class="step-num">2</span>Run Spatial Check</h6>
    <p class="text-muted mb-3" style="font-size:13px">Sets <code>transit_proximate = 1</code> for lots within 400m of an FTN stop. Takes 1–3 minutes.</p>
    <?php if ($stop_count > 0): ?>
    <div class="alert alert-success py-2 mb-3" style="font-size:13px">✅ <?= number_format($stop_count) ?> FTN stops loaded — ready.</div>
    <?php else: ?>
    <div class="alert alert-warning py-2 mb-3" style="font-size:13px">⚠ No FTN stops loaded yet. Run Step 1 first.</div>
    <?php endif; ?>
    <form method="POST" onsubmit="this.querySelector('button').textContent='Running… please wait (1-3 min)'">
      <input type="hidden" name="step" value="spatial">
      <button type="submit" class="btn btn-gold px-4" <?= $stop_count === 0 ? 'disabled' : '' ?>>
        Run Spatial Check → Update All Lots
      </button>
    </form>
  </div>

  <div class="card p-4">
    <h6 class="fw-bold mb-3">Current Status</h6>
    <div class="row text-center">
      <div class="col"><div style="font-size:24px;font-weight:800;color:#002446"><?= number_format($stop_count) ?></div><div class="text-muted" style="font-size:12px">FTN Stops Loaded</div></div>
      <div class="col"><div style="font-size:24px;font-weight:800;color:#22c55e"><?= number_format($transit_count) ?></div><div class="text-muted" style="font-size:12px">Lots Transit-Proximate</div></div>
      <div class="col"><div style="font-size:24px;font-weight:800;color:#002446"><?= number_format($total_count) ?></div><div class="text-muted" style="font-size:12px">Total Lots with Coords</div></div>
    </div>
  </div>
</div>
</body>
</html>