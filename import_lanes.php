<?php
/**
 * admin/import_lanes.php
 * COV lane spatial import — admin password protected
 * Source: opendata.vancouver.ca/explore/dataset/lanes/export/ (CSV)
 * Annual update — every row in the lanes dataset IS a lane, no filtering needed
 */

// ── Admin auth ────────────────────────────────────────────────
define('ADMIN_PASSWORD', 'Concac1979$');
session_start();
if (isset($_POST['admin_login']) && $_POST['password'] === ADMIN_PASSWORD) $_SESSION['wynston_admin'] = true;
$authed = !empty($_SESSION['wynston_admin']);
if (!$authed) { ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#002446;display:flex;align-items:center;justify-content:center;min-height:100vh}
.box{background:#fff;border-radius:12px;padding:40px;width:100%;max-width:380px}</style>
</head><body><div class="box">
<h2 style="color:#002446;font-weight:800">Admin Panel</h2>
<p class="text-muted" style="font-size:13px">Wynston W.I.N — Lane Import</p>
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

// ── STEP 1: Upload and parse lanes CSV ───────────────────────
if ($step === 'upload' && isset($_FILES['lanes_file'])) {
    $file = $_FILES['lanes_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = '❌ Upload error code: ' . $file['error'];
    } else {
        // Strip BOM, normalise line endings
        $content = file_get_contents($file['tmp_name']);
        $content = ltrim($content, "\xEF\xBB\xBF");
        $content = str_replace("\r\n", "\n", $content);
        $lines   = explode("\n", $content);

        // Parse header — semicolon delimited
        $header = array_map('trim', str_getcsv(array_shift($lines), ';'));
        $col    = array_flip($header);

        // Lanes dataset columns: FROM_HUNDRED_BLOCK, Geom, STD_STREET, geo_point_2d
        // Public-streets columns: Geom, HBLOCK, STREETUSE, geo_point_2d
        // Support both formats
        $geo_col    = $col['geo_point_2d'] ?? null;
        $street_col = $col['STD_STREET'] ?? $col['HBLOCK'] ?? null;

        if ($geo_col === null) {
            $message = '❌ Cannot find geo_point_2d column. Headers found: ' . implode(', ', $header);
        } else {
            // Recreate table with correct schema
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
            $pdo->exec("TRUNCATE TABLE lane_segments");
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

            $stmt = $pdo->prepare("
                INSERT INTO lane_segments (hblock, centroid_lat, centroid_lng, street_use)
                VALUES (?, ?, ?, ?)
            ");

            $inserted = 0;
            $skipped  = 0;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $row = str_getcsv($line, ';');
                $geo = trim($row[$geo_col] ?? '');
                $street = $street_col !== null ? trim($row[$street_col] ?? '') : '';

                if (empty($geo)) { $skipped++; continue; }

                // Parse "lat, lng" centroid
                $coords = explode(',', $geo);
                if (count($coords) < 2) { $skipped++; continue; }
                $lat = (float)trim($coords[0]);
                $lng = (float)trim($coords[1]);

                if ($lat == 0 || $lng == 0) { $skipped++; continue; }

                // Vancouver bounding box
                if ($lat < 49.1 || $lat > 49.4 || $lng < -123.3 || $lng > -122.9) {
                    $skipped++; continue;
                }

                try {
                    $stmt->execute([$street, $lat, $lng, 'Lane']);
                    $inserted++;
                } catch (PDOException $e) { $skipped++; }
            }

            $message = "✅ Imported <strong>$inserted</strong> lane segments. Skipped $skipped. Now run Step 2.";
        }
    }
}

// ── STEP 2: Spatial check ────────────────────────────────────
if ($step === 'spatial') {
    set_time_limit(600);

    $lanes = $pdo->query("SELECT centroid_lat, centroid_lng FROM lane_segments")
                 ->fetchAll(PDO::FETCH_ASSOC);

    if (empty($lanes)) {
        $message = '❌ No lane segments found. Run Step 1 first.';
    } else {
        $lots = $pdo->query("
            SELECT id, lat, lng FROM plex_properties
            WHERE lat IS NOT NULL AND lng IS NOT NULL
        ")->fetchAll(PDO::FETCH_ASSOC);

        $updated = 0; $with_lane = 0;
        $upd = $pdo->prepare("UPDATE plex_properties SET lane_access = ? WHERE id = ?");

        foreach ($lots as $lot) {
            $lot_lat  = (float)$lot['lat'];
            $lot_lng  = (float)$lot['lng'];
            $has_lane = 0;

            foreach ($lanes as $lane) {
                $llat = (float)$lane['centroid_lat'];
                $llng = (float)$lane['centroid_lng'];

                // Bounding box pre-filter (50m)
                if (abs($lot_lat - $llat) * 111320 > 80) continue;
                if (abs($lot_lng - $llng) * 111320 * cos(deg2rad($lot_lat)) > 80) continue;

                // Haversine
                $dLat = deg2rad($llat - $lot_lat);
                $dLng = deg2rad($llng - $lot_lng);
                $a    = sin($dLat/2)*sin($dLat/2)
                      + cos(deg2rad($lot_lat))*cos(deg2rad($llat))*sin($dLng/2)*sin($dLng/2);
                $dist = 6371000 * 2 * atan2(sqrt($a), sqrt(1-$a));

                if ($dist <= 40) { $has_lane = 1; break; }
            }

            $upd->execute([$has_lane, $lot['id']]);
            $updated++;
            if ($has_lane) $with_lane++;
        }

        $message = "✅ Done. <strong>$updated</strong> lots checked. "
                 . "<strong>$with_lane</strong> lots have lane access. "
                 . "Refresh the map to see updated pin colours.";
    }
}

// ── Stats ─────────────────────────────────────────────────────
$lane_count = 0; $lane_lots = 0; $total = 0;
try { $lane_count = $pdo->query("SELECT COUNT(*) FROM lane_segments")->fetchColumn(); } catch(Exception $e){}
try { $lane_lots  = $pdo->query("SELECT COUNT(*) FROM plex_properties WHERE lane_access = 1")->fetchColumn(); } catch(Exception $e){}
try { $total      = $pdo->query("SELECT COUNT(*) FROM plex_properties WHERE lat IS NOT NULL")->fetchColumn(); } catch(Exception $e){}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Lane Import — Wynston Admin</title>
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
  <h4 class="fw-bold mb-1" style="color:#002446">Lane Import — COV Lanes Data</h4>
  <p class="text-muted mb-4" style="font-size:13px">
    Updates <code>lane_access</code> on all lots. Run annually.<br>
    Download from: <a href="https://opendata.vancouver.ca/explore/dataset/lanes/export/" target="_blank">opendata.vancouver.ca → Lanes → Export CSV</a>
  </p>

  <?php if ($message): ?>
  <div class="alert alert-info mb-4"><?= $message ?></div>
  <?php endif; ?>

  <div class="card p-4 mb-3">
    <h6 class="fw-bold mb-3"><span class="step-num">1</span>Upload Lanes CSV</h6>
    <p class="text-muted mb-3" style="font-size:13px">
      Upload the raw CSV from COV Open Data — no modifications needed.
    </p>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="step" value="upload">
      <div class="mb-3"><input type="file" name="lanes_file" class="form-control" accept=".csv" required></div>
      <button type="submit" class="btn btn-navy px-4">Import Lane Segments</button>
    </form>
  </div>

  <div class="card p-4 mb-3">
    <h6 class="fw-bold mb-3"><span class="step-num">2</span>Run Spatial Check</h6>
    <p class="text-muted mb-3" style="font-size:13px">
      Sets <code>lane_access = 1</code> for lots within 40m of a lane centroid. Takes 2–5 minutes.
    </p>
    <?php if ($lane_count > 0): ?>
    <div class="alert alert-success py-2 mb-3" style="font-size:13px">
      ✅ <?= number_format($lane_count) ?> lane segments loaded — ready.
    </div>
    <?php else: ?>
    <div class="alert alert-warning py-2 mb-3" style="font-size:13px">
      ⚠ No lane segments loaded yet. Run Step 1 first.
    </div>
    <?php endif; ?>
    <form method="POST" onsubmit="this.querySelector('button').textContent='Running… please wait (2-5 min)'">
      <input type="hidden" name="step" value="spatial">
      <button type="submit" class="btn btn-gold px-4" <?= $lane_count === 0 ? 'disabled' : '' ?>>
        Run Spatial Check → Update All Lots
      </button>
    </form>
  </div>

  <div class="card p-4">
    <h6 class="fw-bold mb-3">Current Status</h6>
    <div class="row text-center">
      <div class="col">
        <div style="font-size:24px;font-weight:800;color:#002446"><?= number_format($lane_count) ?></div>
        <div class="text-muted" style="font-size:12px">Lane Segments Loaded</div>
      </div>
      <div class="col">
        <div style="font-size:24px;font-weight:800;color:#f59e0b"><?= number_format($lane_lots) ?></div>
        <div class="text-muted" style="font-size:12px">Lots With Lane Access</div>
      </div>
      <div class="col">
        <div style="font-size:24px;font-weight:800;color:#002446"><?= number_format($total) ?></div>
        <div class="text-muted" style="font-size:12px">Total Lots with Coords</div>
      </div>
    </div>
  </div>
</div>
</body>
</html>