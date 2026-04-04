<?php
/**
 * admin/sync_permits.php
 * Syncs new/updated rows from multi_2025 → A_Permit_2026
 * Run monthly after developers upload new listings.
 * Existing A_Permit_2026 rows are preserved (coordinates/data you've cleaned stay intact).
 * Only NEW rows from multi_2025 are inserted — existing ones are NOT overwritten.
 */

define('ADMIN_PASSWORD', 'Concac1979$');
session_start();
if (isset($_POST['admin_login']) && $_POST['password'] === ADMIN_PASSWORD) $_SESSION['wynston_admin'] = true;
if (empty($_SESSION['wynston_admin'])) { ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#002446;display:flex;align-items:center;justify-content:center;min-height:100vh}.box{background:#fff;border-radius:12px;padding:40px;width:100%;max-width:380px}</style>
</head><body><div class="box">
<h2 style="color:#002446;font-weight:800">Admin Panel</h2>
<p class="text-muted mb-3" style="font-size:13px">Permit Sync</p>
<form method="POST">
<div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Admin password" required autofocus></div>
<button type="submit" name="admin_login" class="btn w-100" style="background:#002446;color:#fff;font-weight:700">Login</button>
</form></div></body></html>
<?php exit; }

$pdo = new PDO("mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4",
    "u990588858_Multiplex", "Concac1979$",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$message = '';
$action  = $_POST['action'] ?? '';

// ── Sync new rows only ────────────────────────────────────────
if ($action === 'sync_new') {
    $result = $pdo->exec("
        INSERT IGNORE INTO A_Permit_2026
        SELECT * FROM multi_2025
        WHERE id NOT IN (SELECT id FROM A_Permit_2026)
    ");
    $message = "✅ Sync complete. <strong>$result new rows</strong> added from multi_2025. Existing A_Permit_2026 data unchanged.";
}

// ── Force overwrite a single row ──────────────────────────────
if ($action === 'overwrite_one' && !empty($_POST['row_id'])) {
    $id = (int)$_POST['row_id'];
    $pdo->exec("DELETE FROM A_Permit_2026 WHERE id = $id");
    $result = $pdo->exec("INSERT INTO A_Permit_2026 SELECT * FROM multi_2025 WHERE id = $id");
    $message = $result ? "✅ Row #$id refreshed from multi_2025." : "⚠ Row #$id not found in multi_2025.";
}

// ── Stats ─────────────────────────────────────────────────────
$total_m  = $pdo->query("SELECT COUNT(*) FROM multi_2025")->fetchColumn();
$total_a  = $pdo->query("SELECT COUNT(*) FROM A_Permit_2026")->fetchColumn();
$on_map   = $pdo->query("SELECT COUNT(*) FROM A_Permit_2026 WHERE show_on_plex_map = 1")->fetchColumn();
$new_in_m = $pdo->query("SELECT COUNT(*) FROM multi_2025 WHERE id NOT IN (SELECT id FROM A_Permit_2026)")->fetchColumn();

// ── Recent listings ───────────────────────────────────────────
$recent = $pdo->query("
    SELECT id, address, submit_status, show_on_plex_map, latitude, longitude
    FROM   A_Permit_2026
    ORDER BY id DESC
    LIMIT  10
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Permit Sync — Wynston Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f9f6f0}
.card{border:none;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08)}
.btn-navy{background:#002446;color:#fff;border:none}
.btn-navy:hover{background:#003a7a;color:#fff}
.btn-gold{background:#c9a84c;color:#002446;border:none;font-weight:700}
.btn-gold:hover{background:#d4b35c}
.stat-num{font-size:28px;font-weight:800;color:#002446}
.badge-on{background:rgba(34,197,94,.15);color:#15803d;font-size:11px;padding:3px 8px;border-radius:10px;font-weight:700}
.badge-off{background:rgba(148,163,184,.15);color:#64748b;font-size:11px;padding:3px 8px;border-radius:10px;font-weight:700}
</style>
</head>
<body>
<div class="container py-4" style="max-width:800px">
  <div class="mb-3"><a href="/admin.php" class="btn btn-sm btn-outline-secondary">← Back to Admin</a></div>

  <h4 class="fw-bold mb-1" style="color:#002446">Permit Sync — multi_2025 → A_Permit_2026</h4>
  <p class="text-muted mb-4" style="font-size:13px">
    Run monthly after developers upload new listings. Only adds NEW rows — your cleaned coordinates and map flags are preserved.
  </p>

  <?php if ($message): ?>
  <div class="alert alert-info mb-4"><?= $message ?></div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="card p-4 mb-3">
    <div class="row text-center">
      <div class="col">
        <div class="stat-num"><?= number_format($total_m) ?></div>
        <div class="text-muted" style="font-size:12px">Rows in multi_2025</div>
      </div>
      <div class="col">
        <div class="stat-num"><?= number_format($total_a) ?></div>
        <div class="text-muted" style="font-size:12px">Rows in A_Permit_2026</div>
      </div>
      <div class="col">
        <div class="stat-num" style="color:#22c55e"><?= number_format($on_map) ?></div>
        <div class="text-muted" style="font-size:12px">Showing on Map</div>
      </div>
      <div class="col">
        <div class="stat-num" style="color:<?= $new_in_m > 0 ? '#f59e0b' : '#94a3b8' ?>"><?= number_format($new_in_m) ?></div>
        <div class="text-muted" style="font-size:12px">New in multi_2025</div>
      </div>
    </div>
  </div>

  <!-- Sync action -->
  <div class="card p-4 mb-3">
    <h6 class="fw-bold mb-2">Monthly Sync</h6>
    <p class="text-muted mb-3" style="font-size:13px">
      Adds <?= number_format($new_in_m) ?> new row(s) from multi_2025 to A_Permit_2026.
      Existing rows in A_Permit_2026 are <strong>not touched</strong> — your cleaned data is safe.
    </p>
    <form method="POST">
      <input type="hidden" name="action" value="sync_new">
      <button type="submit" class="btn btn-gold px-4"
        <?= $new_in_m == 0 ? 'disabled' : '' ?>>
        <?= $new_in_m > 0 ? "Sync $new_in_m New Row(s) →" : 'Nothing to sync — up to date' ?>
      </button>
    </form>
  </div>

  <!-- Recent listings table -->
  <div class="card p-4">
    <h6 class="fw-bold mb-3">Recent Listings in A_Permit_2026</h6>
    <p class="text-muted mb-3" style="font-size:12px">
      Set <code>show_on_plex_map = 1</code> in phpMyAdmin to show a listing on the map.
      Verify coordinates are correct before enabling.
    </p>
    <table class="table table-sm" style="font-size:13px">
      <thead><tr><th>ID</th><th>Address</th><th>Status</th><th>On Map</th><th>Has Coords</th></tr></thead>
      <tbody>
        <?php foreach ($recent as $r): ?>
        <tr>
          <td><?= $r['id'] ?></td>
          <td><?= htmlspecialchars($r['address']) ?></td>
          <td><?= htmlspecialchars($r['submit_status']) ?></td>
          <td><?= $r['show_on_plex_map'] ? '<span class="badge-on">✓ Yes</span>' : '<span class="badge-off">No</span>' ?></td>
          <td><?= ($r['latitude'] && $r['longitude']) ? '<span class="badge-on">✓</span>' : '<span class="badge-off">Missing</span>' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
