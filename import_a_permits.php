<?php
define('ADMIN_PASSWORD', 'Concac1979$');
session_start();
if (isset($_POST['admin_login']) && $_POST['password'] === ADMIN_PASSWORD) $_SESSION['wynston_admin'] = true;
if (empty($_SESSION['wynston_admin'])) { ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#002446;display:flex;align-items:center;justify-content:center;min-height:100vh}.box{background:#fff;border-radius:12px;padding:40px;width:100%;max-width:380px}</style>
</head><body><div class="box">
<h2 style="color:#002446;font-weight:800">Admin Panel</h2>
<p class="text-muted mb-3" style="font-size:13px">COV Permit Import</p>
<form method="POST">
<div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Admin password" required autofocus></div>
<button type="submit" name="admin_login" class="btn w-100" style="background:#002446;color:#fff;font-weight:700">Login</button>
</form></div></body></html>
<?php exit; }

$pdo = new PDO("mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4",
    "u990588858_Multiplex", "Concac1979$",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$message = '';
$step    = $_POST['step'] ?? '';

if ($step === 'import' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = '❌ Upload error: ' . $file['error'];
    } else {
        // Write to temp file and use fgetcsv — handles multiline quoted fields correctly
        $tmpPath = $file['tmp_name'];

        // Strip BOM if present
        $handle = fopen($tmpPath, 'r');
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        // Read header
        $header = fgetcsv($handle);
        $col    = array_flip(array_map('trim', $header));

        // Validate
        $required = ['PermitNumber','Address','Issuedate','GeoLocalArea','geo_point_2d'];
        $missing  = array_filter($required, fn($r) => !isset($col[$r]));
        if ($missing) {
            $message = '❌ Missing columns: ' . implode(', ', $missing);
            fclose($handle);
            goto done;
        }

        $stmt = $pdo->prepare("
            INSERT INTO A_Permit_2026
                (permit_number, address, neighbourhood, permit_type, description,
                 applicant, property_use, project_value, latitude, longitude,
                 issue_date, `year_month`, show_on_plex_map, data_source)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'COV')
            ON DUPLICATE KEY UPDATE
                address       = VALUES(address),
                neighbourhood = VALUES(neighbourhood),
                description   = VALUES(description),
                latitude      = VALUES(latitude),
                longitude     = VALUES(longitude),
                issue_date    = VALUES(issue_date),
                `year_month`  = VALUES(`year_month`)
        ");

        $inserted = 0; $updated = 0; $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) { $skipped++; continue; }

            // Coordinates
            $geo    = trim($row[$col['geo_point_2d']] ?? '');
            $coords = explode(',', $geo);
            if (count($coords) < 2) { $skipped++; continue; }
            $lat = (float)trim($coords[0]);
            $lng = (float)trim($coords[1]);
            if (abs($lat) < 1 || abs($lng) < 1) { $skipped++; continue; }

            // Vancouver bounding box
            if ($lat < 49.1 || $lat > 49.4 || $lng < -123.3 || $lng > -122.9) { $skipped++; continue; }

            $permit_number = trim($row[$col['PermitNumber']] ?? '');
            if (empty($permit_number)) { $skipped++; continue; }

            $address       = trim($row[$col['Address']] ?? '');
            $neighbourhood = trim($row[$col['GeoLocalArea']] ?? '');
            $description   = trim($row[$col['ProjectDescription']] ?? '');
            $applicant     = trim($row[$col['Applicant']] ?? '');
            $property_use  = trim($row[$col['SpecificUseCategory']] ?? '');
            $permit_type   = trim($row[$col['TypeOfWork']] ?? '');
            $project_value = (int)preg_replace('/[^0-9]/', '', $row[$col['ProjectValue']] ?? '0');
            $year_month    = trim($row[$col['YearMonth']] ?? '');

            $issue_raw  = trim($row[$col['Issuedate']] ?? '');
            $issue_date = null;
            if ($issue_raw) {
                $d = date_create($issue_raw);
                if ($d) $issue_date = date_format($d, 'Y-m-d');
            }

            try {
                $stmt->execute([
                    $permit_number, $address, $neighbourhood, $permit_type,
                    $description, $applicant, $property_use, $project_value,
                    $lat, $lng, $issue_date, $year_month
                ]);
                if ($stmt->rowCount() === 1) $inserted++;
                else $updated++;
            } catch (PDOException $e) { $skipped++; }
        }

        fclose($handle);
        $message = "✅ Import complete. <strong>$inserted new</strong> permits added, <strong>$updated</strong> updated, $skipped skipped.";
    }
}

done:
$total  = 0; $on_map = 0; $by_hood = []; $by_month = [];
try {
    $total    = $pdo->query("SELECT COUNT(*) FROM A_Permit_2026")->fetchColumn();
    $on_map   = $pdo->query("SELECT COUNT(*) FROM A_Permit_2026 WHERE show_on_plex_map = 1")->fetchColumn();
    $by_hood  = $pdo->query("SELECT neighbourhood, COUNT(*) as cnt FROM A_Permit_2026 GROUP BY neighbourhood ORDER BY cnt DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $by_month = $pdo->query("SELECT `year_month`, COUNT(*) as cnt FROM A_Permit_2026 GROUP BY `year_month` ORDER BY `year_month` DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>COV Permit Import</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f9f6f0}.card{border:none;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08)}.stat-num{font-size:28px;font-weight:800;color:#002446}.btn-navy{background:#002446;color:#fff;border:none}</style>
</head>
<body>
<div class="container py-4" style="max-width:800px">
  <div class="mb-3"><a href="/admin.php" class="btn btn-sm btn-outline-secondary">← Back to Admin</a></div>
  <h4 class="fw-bold mb-1" style="color:#002446">COV Permit Import → A_Permit_2026</h4>
  <p class="text-muted mb-4" style="font-size:13px">
    Monthly update — download from <a href="https://opendata.vancouver.ca/explore/dataset/issued-building-permits/export/" target="_blank">COV Open Data → Issued Building Permits</a>, export as CSV, upload here.
  </p>

  <?php if ($message): ?><div class="alert alert-info mb-4"><?= $message ?></div><?php endif; ?>

  <div class="card p-4 mb-3">
    <h6 class="fw-bold mb-3">Upload COV Permits CSV</h6>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="step" value="import">
      <div class="mb-3"><input type="file" name="csv_file" class="form-control" accept=".csv" required></div>
      <button type="submit" class="btn btn-navy px-4">Import Permits</button>
    </form>
  </div>

  <div class="card p-4 mb-3">
    <div class="row text-center mb-3">
      <div class="col"><div class="stat-num"><?= number_format($total) ?></div><div class="text-muted" style="font-size:12px">Total Permits</div></div>
      <div class="col"><div class="stat-num" style="color:#22c55e"><?= number_format($on_map) ?></div><div class="text-muted" style="font-size:12px">On Map</div></div>
    </div>
    <?php if ($by_hood): ?>
    <div class="row">
      <div class="col-md-6">
        <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#888;margin-bottom:8px">By Neighbourhood</div>
        <?php foreach ($by_hood as $r): ?>
        <div class="d-flex justify-content-between" style="font-size:13px;padding:3px 0;border-bottom:1px solid rgba(0,0,0,.05)">
          <span><?= htmlspecialchars($r['neighbourhood']) ?></span><strong><?= $r['cnt'] ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="col-md-6">
        <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#888;margin-bottom:8px">By Month</div>
        <?php foreach ($by_month as $r): ?>
        <div class="d-flex justify-content-between" style="font-size:13px;padding:3px 0;border-bottom:1px solid rgba(0,0,0,.05)">
          <span><?= htmlspecialchars($r['year_month']) ?></span><strong><?= $r['cnt'] ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>