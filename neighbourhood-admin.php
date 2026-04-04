<?php
// neighbourhood-admin.php
// Password-protected admin for updating monthly HPI data
// Upload to root directory — access at /neighbourhood-admin.php

$base_dir = __DIR__ . '/Base';
require_once "$base_dir/db.php";

// ── Simple password protection ────────────────────────────────────────────────
$admin_password = 'WynstonAdmin2025!'; // Change this to your preferred password
session_start();

if ($_POST['action'] ?? '' === 'login') {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['nb_admin'] = true;
    } else {
        $login_error = 'Incorrect password.';
    }
}
if ($_GET['logout'] ?? '' === '1') {
    unset($_SESSION['nb_admin']);
    header('Location: neighbourhood-admin.php');
    exit;
}

$authed = !empty($_SESSION['nb_admin']);

// ── Handle HPI update form submission ─────────────────────────────────────────
$success_msg = '';
$error_msg   = '';

if ($authed && ($_POST['action'] ?? '') === 'update_hpi') {
    $nb_id         = (int)$_POST['neighbourhood_id'];
    $month_year    = $_POST['month_year'];         // e.g. 2025-03
    $avg_price     = (int)str_replace(',', '', $_POST['avg_price'] ?? 0);
    $price_det     = (int)str_replace(',', '', $_POST['price_detached'] ?? 0);
    $price_condo   = (int)str_replace(',', '', $_POST['price_condo'] ?? 0);
    $price_town    = (int)str_replace(',', '', $_POST['price_townhouse'] ?? 0);
    $price_duplex  = (int)str_replace(',', '', $_POST['price_duplex'] ?? 0);
    $hpi_bench     = (int)str_replace(',', '', $_POST['hpi_benchmark'] ?? 0);
    $hpi_mom       = (float)$_POST['hpi_change_mom'];
    $hpi_yoy       = (float)$_POST['hpi_change_yoy'];
    $update_date   = date('Y-m-d');

    // Format month_year as first of month for DATE column
    $month_date = $month_year . '-01';

    try {
        // 1. Update current values in neighbourhoods table
        $pdo->prepare("
            UPDATE neighbourhoods SET
                avg_price        = :avg,
                price_detached   = :det,
                price_condo      = :condo,
                price_townhouse  = :town,
                price_duplex     = :dup,
                hpi_benchmark    = :bench,
                hpi_change_mom   = :mom,
                hpi_change_yoy   = :yoy,
                price_updated_date = :upd
            WHERE id = :id
        ")->execute([
            ':avg'   => $avg_price   ?: null,
            ':det'   => $price_det   ?: null,
            ':condo' => $price_condo ?: null,
            ':town'  => $price_town  ?: null,
            ':dup'   => $price_duplex ?: null,
            ':bench' => $hpi_bench   ?: null,
            ':mom'   => $hpi_mom,
            ':yoy'   => $hpi_yoy,
            ':upd'   => $update_date,
            ':id'    => $nb_id
        ]);

        // 2. Upsert into history table for chart
        $pdo->prepare("
            INSERT INTO neighbourhood_hpi_history
                (neighbourhood_id, month_year, avg_price, price_detached, price_condo, price_townhouse, price_duplex, hpi_benchmark, hpi_change_mom, hpi_change_yoy)
            VALUES
                (:id, :my, :avg, :det, :condo, :town, :dup, :bench, :mom, :yoy)
            ON DUPLICATE KEY UPDATE
                avg_price       = VALUES(avg_price),
                price_detached  = VALUES(price_detached),
                price_condo     = VALUES(price_condo),
                price_townhouse = VALUES(price_townhouse),
                price_duplex    = VALUES(price_duplex),
                hpi_benchmark   = VALUES(hpi_benchmark),
                hpi_change_mom  = VALUES(hpi_change_mom),
                hpi_change_yoy  = VALUES(hpi_change_yoy)
        ")->execute([
            ':id'    => $nb_id,
            ':my'    => $month_date,
            ':avg'   => $avg_price   ?: null,
            ':det'   => $price_det   ?: null,
            ':condo' => $price_condo ?: null,
            ':town'  => $price_town  ?: null,
            ':dup'   => $price_duplex ?: null,
            ':bench' => $hpi_bench   ?: null,
            ':mom'   => $hpi_mom,
            ':yoy'   => $hpi_yoy
        ]);

        $success_msg = 'Market data updated successfully for ' . $month_year . '.';

    } catch (Exception $e) {
        $error_msg = 'Error: ' . $e->getMessage();
    }
}

// ── Load neighbourhoods for dropdown ─────────────────────────────────────────
$all_nbs = [];
if ($authed) {
    $all_nbs = $pdo->query("SELECT * FROM neighbourhoods WHERE is_active = 1 ORDER BY area, sort_order")->fetchAll(PDO::FETCH_ASSOC);
}

// ── Selected neighbourhood for pre-filling ────────────────────────────────────
$selected_id = (int)($_GET['id'] ?? ($_POST['neighbourhood_id'] ?? 0));
$selected_nb = null;
if ($selected_id && $authed) {
    $sn = $pdo->prepare("SELECT * FROM neighbourhoods WHERE id = ? LIMIT 1");
    $sn->execute([$selected_id]);
    $selected_nb = $sn->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Neighbourhood Admin — Wynston</title>
    <link rel="icon" href="/assets/img/favicon.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
    * { box-sizing:border-box;margin:0;padding:0; }
    body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f4f6fb;min-height:100vh; }

    /* ── Topbar ── */
    .adm-top { background:#002446;padding:14px 24px;display:flex;align-items:center;justify-content:space-between; }
    .adm-top-brand { color:#fff;font-size:16px;font-weight:800;display:flex;align-items:center;gap:10px; }
    .adm-top-brand img { height:32px; }
    .adm-top-links a { color:rgba(255,255,255,.6);font-size:13px;text-decoration:none;margin-left:16px; }
    .adm-top-links a:hover { color:#c9a84c; }

    /* ── Login ── */
    .adm-login { max-width:400px;margin:80px auto;background:#fff;border-radius:14px;padding:40px;box-shadow:0 8px 32px rgba(0,0,0,.12); }
    .adm-login h2 { font-size:22px;font-weight:800;color:#002446;margin-bottom:24px;text-align:center; }
    .adm-input { width:100%;padding:12px 14px;border:1.5px solid #dde;border-radius:8px;font-size:14px;margin-bottom:14px;outline:none; }
    .adm-input:focus { border-color:#002446; }
    .adm-btn-primary { width:100%;padding:13px;background:#002446;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer; }
    .adm-btn-primary:hover { background:#0065ff; }
    .adm-error { background:#fee2e2;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px; }
    .adm-success { background:#dcfce7;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px; }

    /* ── Main layout ── */
    .adm-wrap  { max-width:900px;margin:32px auto;padding:0 16px; }
    .adm-card  { background:#fff;border-radius:12px;padding:28px 32px;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:24px; }
    .adm-card h2 { font-size:18px;font-weight:800;color:#002446;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #eee; }

    /* ── Form ── */
    .adm-form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    .adm-form-group { display:flex;flex-direction:column;gap:6px; }
    .adm-form-group label { font-size:12px;font-weight:700;color:#444;text-transform:uppercase;letter-spacing:.5px; }
    .adm-form-group input, .adm-form-group select { padding:10px 12px;border:1.5px solid #dde;border-radius:8px;font-size:14px;outline:none; }
    .adm-form-group input:focus, .adm-form-group select:focus { border-color:#002446; }
    .adm-form-hint { font-size:11px;color:#aaa;margin-top:2px; }
    .adm-form-full { grid-column:1/-1; }
    .adm-submit { display:flex;justify-content:flex-end;gap:12px;margin-top:20px; }
    .adm-btn-save { background:#002446;color:#fff;border:none;border-radius:8px;padding:12px 28px;font-size:14px;font-weight:700;cursor:pointer; }
    .adm-btn-save:hover { background:#0065ff; }
    .adm-section-label { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#888;margin:20px 0 12px;grid-column:1/-1;border-top:1px solid #f0f0f0;padding-top:16px; }

    /* ── History table ── */
    .adm-table { width:100%;border-collapse:collapse;font-size:13px; }
    .adm-table th { background:#f8f9fc;padding:10px 12px;text-align:left;font-weight:700;color:#444;border-bottom:2px solid #eee; }
    .adm-table td { padding:10px 12px;border-bottom:1px solid #f0f0f0;color:#555; }
    .adm-table tr:hover td { background:#fafafa; }

    @media (max-width:600px) { .adm-form-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>

<div class="adm-top">
    <div class="adm-top-brand">
        <img src="/assets/img/logo-light.png" alt="Wynston">
        Neighbourhood Admin
    </div>
    <?php if ($authed): ?>
    <div class="adm-top-links">
        <a href="neighbourhoods.php" target="_blank"><i class="fas fa-external-link-alt me-1"></i>View Site</a>
        <a href="?logout=1"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
    </div>
    <?php endif; ?>
</div>

<?php if (!$authed): ?>
<!-- ── Login form ── -->
<div class="adm-login">
    <h2><i class="fas fa-lock" style="color:#c9a84c;margin-right:8px;"></i>Wynston Admin</h2>
    <?php if (!empty($login_error)): ?>
    <div class="adm-error"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="action" value="login">
        <input type="password" name="password" class="adm-input" placeholder="Admin password" autofocus>
        <button type="submit" class="adm-btn-primary"><i class="fas fa-sign-in-alt me-2"></i>Sign In</button>
    </form>
</div>

<?php else: ?>
<!-- ── Admin dashboard ── -->
<div class="adm-wrap">

    <?php if ($success_msg): ?><div class="adm-success"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
    <?php if ($error_msg):   ?><div class="adm-error"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

    <!-- ── Update HPI form ── -->
    <div class="adm-card">
        <h2><i class="fas fa-chart-line me-2" style="color:#c9a84c;"></i>Update Monthly Market Data</h2>
        <p style="font-size:13px;color:#666;margin-bottom:20px;">
            Enter data from the REBGV monthly stats package. All prices in CAD. Leave blank if not available for this neighbourhood.
        </p>

        <form method="POST">
            <input type="hidden" name="action" value="update_hpi">
            <div class="adm-form-grid">

                <!-- Neighbourhood & Month -->
                <div class="adm-form-group">
                    <label>Neighbourhood</label>
                    <select name="neighbourhood_id" required onchange="window.location='?id='+this.value">
                        <option value="">— Select neighbourhood —</option>
                        <?php foreach ($all_nbs as $nb): ?>
                        <?php $areas_grouped[$nb['area']][] = $nb; ?>
                        <option value="<?= $nb['id'] ?>" <?= $selected_id == $nb['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($nb['area'] . ' › ' . $nb['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="adm-form-group">
                    <label>Month / Year</label>
                    <input type="month" name="month_year" value="<?= date('Y-m') ?>" required>
                    <span class="adm-form-hint">This will be added to the price history chart</span>
                </div>

                <?php if ($selected_nb): ?>
                <div class="adm-section-label">HPI Benchmark & Changes</div>

                <div class="adm-form-group">
                    <label>HPI Benchmark Price</label>
                    <input type="text" name="hpi_benchmark" value="<?= $selected_nb['hpi_benchmark'] ?>" placeholder="e.g. 1250000">
                    <span class="adm-form-hint">The REBGV composite benchmark for this area</span>
                </div>

                <div class="adm-form-group">
                    <label>Average Price (all types)</label>
                    <input type="text" name="avg_price" value="<?= $selected_nb['avg_price'] ?>" placeholder="e.g. 1180000">
                </div>

                <div class="adm-form-group">
                    <label>Month-over-Month Change (%)</label>
                    <input type="number" step="0.1" name="hpi_change_mom" value="<?= $selected_nb['hpi_change_mom'] ?>" placeholder="e.g. 1.2 or -0.8">
                    <span class="adm-form-hint">Use negative for decrease e.g. -1.2</span>
                </div>

                <div class="adm-form-group">
                    <label>Year-over-Year Change (%)</label>
                    <input type="number" step="0.1" name="hpi_change_yoy" value="<?= $selected_nb['hpi_change_yoy'] ?>" placeholder="e.g. 4.5 or -2.1">
                </div>

                <div class="adm-section-label">Price by Property Type</div>

                <div class="adm-form-group">
                    <label>Detached House</label>
                    <input type="text" name="price_detached" value="<?= $selected_nb['price_detached'] ?>" placeholder="e.g. 1850000">
                </div>

                <div class="adm-form-group">
                    <label>Condo / Apartment</label>
                    <input type="text" name="price_condo" value="<?= $selected_nb['price_condo'] ?>" placeholder="e.g. 680000">
                </div>

                <div class="adm-form-group">
                    <label>Townhouse</label>
                    <input type="text" name="price_townhouse" value="<?= $selected_nb['price_townhouse'] ?>" placeholder="e.g. 1050000">
                </div>

                <div class="adm-form-group">
                    <label>Duplex / Multiplex</label>
                    <input type="text" name="price_duplex" value="<?= $selected_nb['price_duplex'] ?>" placeholder="e.g. 1400000">
                </div>

                <div class="adm-submit adm-form-full">
                    <a href="neighbourhood.php?slug=<?= urlencode($selected_nb['slug']) ?>" target="_blank"
                       style="padding:12px 20px;border:1.5px solid #dde;border-radius:8px;font-size:14px;font-weight:600;color:#555;text-decoration:none;">
                        <i class="fas fa-eye me-1"></i>Preview Page
                    </a>
                    <button type="submit" class="adm-btn-save">
                        <i class="fas fa-save me-2"></i>Save & Publish
                    </button>
                </div>
                <?php else: ?>
                <div style="grid-column:1/-1;padding:20px;background:#f8f9fc;border-radius:8px;text-align:center;color:#aaa;font-size:14px;">
                    <i class="fas fa-arrow-up" style="display:block;margin-bottom:8px;"></i>
                    Select a neighbourhood above to enter this month's data
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ── Recent history ── -->
    <?php if ($selected_nb): ?>
    <div class="adm-card">
        <h2><i class="fas fa-history me-2" style="color:#0065ff;"></i>Price History — <?= htmlspecialchars($selected_nb['name']) ?></h2>
        <?php
        $hist = $pdo->prepare("
            SELECT * FROM neighbourhood_hpi_history 
            WHERE neighbourhood_id = ? 
            ORDER BY month_year DESC 
            LIMIT 12
        ");
        $hist->execute([$selected_nb['id']]);
        $history_rows = $hist->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if (!empty($history_rows)): ?>
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>HPI Benchmark</th>
                    <th>Average</th>
                    <th>Detached</th>
                    <th>Condo</th>
                    <th>Townhouse</th>
                    <th>MoM %</th>
                    <th>YoY %</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($history_rows as $h): ?>
            <tr>
                <td><strong><?= date('M Y', strtotime($h['month_year'])) ?></strong></td>
                <td><?= $h['hpi_benchmark'] ? '$'.number_format($h['hpi_benchmark']) : '—' ?></td>
                <td><?= $h['avg_price']     ? '$'.number_format($h['avg_price'])     : '—' ?></td>
                <td><?= $h['price_detached']? '$'.number_format($h['price_detached']): '—' ?></td>
                <td><?= $h['price_condo']   ? '$'.number_format($h['price_condo'])   : '—' ?></td>
                <td><?= $h['price_townhouse']?'$'.number_format($h['price_townhouse']):'—'?></td>
                <td style="color:<?= $h['hpi_change_mom'] >= 0 ? '#16a34a' : '#dc2626' ?>">
                    <?= $h['hpi_change_mom'] !== null ? ($h['hpi_change_mom'] >= 0 ? '+' : '') . $h['hpi_change_mom'] . '%' : '—' ?>
                </td>
                <td style="color:<?= $h['hpi_change_yoy'] >= 0 ? '#16a34a' : '#dc2626' ?>">
                    <?= $h['hpi_change_yoy'] !== null ? ($h['hpi_change_yoy'] >= 0 ? '+' : '') . $h['hpi_change_yoy'] . '%' : '—' ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color:#aaa;font-size:14px;padding:16px 0;">No history yet. Save this month's data above to start building the chart.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ── All neighbourhoods quick overview ── -->
    <div class="adm-card">
        <h2><i class="fas fa-map-marker-alt me-2" style="color:#c9a84c;"></i>All Neighbourhoods</h2>
        <table class="adm-table">
            <thead>
                <tr><th>Neighbourhood</th><th>Area</th><th>Last Updated</th><th>HPI Benchmark</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($all_nbs as $nb): ?>
            <tr>
                <td><strong><?= htmlspecialchars($nb['name']) ?></strong></td>
                <td><?= htmlspecialchars($nb['area']) ?></td>
                <td><?= $nb['price_updated_date'] ? date('M j, Y', strtotime($nb['price_updated_date'])) : '<span style="color:#f59e0b;">Never updated</span>' ?></td>
                <td><?= $nb['hpi_benchmark'] ? '$'.number_format($nb['hpi_benchmark']) : '—' ?></td>
                <td>
                    <a href="?id=<?= $nb['id'] ?>" style="font-size:12px;color:#0065ff;font-weight:600;">Update</a>
                    &nbsp;·&nbsp;
                    <a href="neighbourhood.php?slug=<?= urlencode($nb['slug']) ?>" target="_blank" style="font-size:12px;color:#888;">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
<?php endif; ?>

</body>
</html>
