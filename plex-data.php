<?php
// ============================================================
// admin/plex-data.php  —  Wynston Plex Map Data Admin
// Session 03 — Admin Upload Channels
//
// Channels:
//   A — REBGV Sold CSV (monthly drag-and-drop → monthly_market_stats)
//   B — liv.rent Rental Entry (monthly per neighbourhood)
//   D — CMHC Annual Benchmarks
//   Outlook — Wynston Outlook quarterly input + calculate
//   Costs — Construction $/sqft overrides
//
// Auth: reuses existing admin.php session (admin_logged_in).
// Link this page from admin.php tab bar.
// ============================================================

session_start();
define('ADMIN_PASSWORD', 'Concac1979$');

// ── Auth guard ────────────────────────────────────────────────────────────────
if (isset($_POST['admin_login'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) $_SESSION['admin_logged_in'] = true;
    else $login_error = 'Incorrect password.';
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: plex-data.php'); exit; }
if (empty($_SESSION['admin_logged_in'])) {
    ?><!DOCTYPE html><html><head><meta charset="UTF-8"><title>Plex Data — Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#002446;display:flex;align-items:center;justify-content:center;min-height:100vh;}
    .lb{background:#fff;border-radius:12px;padding:40px;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.4);}
    .lb h2{color:#002446;font-weight:800;margin-bottom:6px;}.lb p{color:#888;font-size:13px;margin-bottom:24px;}
    .ba{background:#002446;color:#fff;border:none;width:100%;padding:12px;border-radius:8px;font-weight:700;}</style>
    </head><body><div class="lb"><h2>🗺️ Plex Data</h2><p>Admin Access</p>
    <?php if (!empty($login_error)): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($login_error) ?></div><?php endif; ?>
    <form method="POST"><input type="password" name="password" class="form-control mb-3" placeholder="Admin password" required autofocus>
    <button type="submit" name="admin_login" class="ba">Login</button></form></div></body></html>
    <?php exit;
}

// ── DB ────────────────────────────────────────────────────────────────────────
$host = 'localhost'; $db = 'u990588858_Property'; $user = 'u990588858_Multiplex'; $pass = 'Concac1979$';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("DB error: ".$e->getMessage()); }

// ── Load neighbourhood list for dropdowns ─────────────────────────────────────
$neighbourhoods = [];
try {
    $neighbourhoods = $pdo->query("SELECT id, slug, name FROM neighbourhoods WHERE is_active=1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ── Load recent upload history ────────────────────────────────────────────────
$recent_uploads = [];
try {
    $recent_uploads = $pdo->query("
        SELECT neighbourhood_slug, data_month, csv_type, COUNT(*) as row_count,
               MAX(created_at) as uploaded_at
        FROM monthly_market_stats
        GROUP BY neighbourhood_slug, data_month, csv_type
        ORDER BY uploaded_at DESC
        LIMIT 20
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ── Load CMHC history ─────────────────────────────────────────────────────────
$cmhc_rows = [];
try {
    $cmhc_rows = $pdo->query("
        SELECT * FROM cmhc_benchmarks ORDER BY year DESC, neighbourhood_slug ASC LIMIT 50
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ── Load Outlook inputs and results ──────────────────────────────────────────
$outlook_inputs  = [];
$outlook_results = [];
$latest_quarter  = '';
try {
    $lq = $pdo->query("SELECT quarter FROM wynston_outlook_inputs WHERE is_active=1 ORDER BY created_at DESC LIMIT 1")->fetchColumn();
    if ($lq) {
        $latest_quarter = $lq;
        $outlook_inputs = $pdo->prepare("SELECT * FROM wynston_outlook_inputs WHERE quarter=? AND is_active=1 ORDER BY source_name")->execute([$lq]) ? [] : [];
        $oi = $pdo->prepare("SELECT * FROM wynston_outlook_inputs WHERE quarter=? AND is_active=1 ORDER BY source_name");
        $oi->execute([$lq]);
        $outlook_inputs = $oi->fetchAll(PDO::FETCH_ASSOC);
    }
    $or = $pdo->query("SELECT * FROM wynston_outlook WHERE is_active=1 ORDER BY weighted_outlook DESC");
    $outlook_results = $or->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ── Load construction costs ────────────────────────────────────────────────────
$cost_rows = [];
try {
    $cost_rows = $pdo->query("SELECT * FROM construction_costs ORDER BY neighbourhood_slug")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$active_tab = $_GET['tab'] ?? 'channel-a';
$msg        = htmlspecialchars($_GET['msg'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plex Data Admin — Wynston</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --brand:#002446; --gold:#c9a84c; --blue:#0065ff; --green:#16a34a; }
        body  { background:#f4f6fb; font-family:'Segoe UI',sans-serif; margin:0; }

        /* ── Top bar ── */
        .topbar { background:var(--brand); color:#fff; padding:14px 28px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; box-shadow:0 2px 12px rgba(0,0,0,.2); }
        .topbar h1 { font-size:18px; font-weight:800; margin:0; }
        .topbar span { font-size:12px; opacity:.6; }
        .topbar-btns { display:flex; gap:10px; align-items:center; }
        .btn-nav { background:rgba(255,255,255,.12); color:#fff; border:none; padding:6px 14px; border-radius:6px; font-size:12px; cursor:pointer; text-decoration:none; font-weight:600; }
        .btn-nav:hover { background:var(--blue); color:#fff; }

        /* ── Tab nav ── */
        .tab-nav { background:#fff; border-bottom:1px solid #e2e8f0; padding:0 28px; display:flex; gap:0; overflow-x:auto; }
        .tab-nav a { padding:14px 18px; font-size:13px; font-weight:600; text-decoration:none; border-bottom:3px solid transparent; color:#666; white-space:nowrap; display:flex; align-items:center; gap:6px; }
        .tab-nav a.active { border-bottom-color:var(--blue); color:var(--blue); }
        .tab-nav a.tab-outlook.active { border-bottom-color:var(--gold); color:var(--gold); }

        /* ── Content ── */
        .content { padding:28px; max-width:1100px; margin:0 auto; }
        .section-card { background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.06); padding:28px; margin-bottom:24px; }
        .section-card h3 { font-size:16px; font-weight:800; color:var(--brand); margin:0 0 4px; }
        .section-card .sub { font-size:13px; color:#888; margin-bottom:20px; }

        /* ── Drop zone ── */
        .drop-zone { border:2px dashed #c0cce0; border-radius:10px; padding:32px 20px; text-align:center; cursor:pointer; background:#f8faff; transition:all .2s; }
        .drop-zone:hover, .drop-zone.dz-active { border-color:var(--blue); background:#eef4ff; }
        .drop-zone i { font-size:36px; color:#bbc; display:block; margin-bottom:10px; }
        .drop-zone strong { font-size:14px; color:#445; display:block; }
        .drop-zone small { font-size:12px; color:#aaa; }
        .dz-selected { border-color:var(--green); background:#f0fdf4; }
        .dz-selected i { color:var(--green); }

        /* ── Form styles ── */
        .form-label { font-size:12px; font-weight:600; color:#555; margin-bottom:4px; }
        .form-control, .form-select { font-size:13px; border-radius:7px; border:1px solid #dde; }
        .form-control:focus, .form-select:focus { border-color:var(--blue); box-shadow:0 0 0 3px rgba(0,101,255,.1); }

        /* ── Buttons ── */
        .btn-primary-w { background:var(--brand); color:#fff; border:none; padding:11px 24px; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer; width:100%; transition:background .2s; }
        .btn-primary-w:hover { background:var(--blue); }
        .btn-gold { background:var(--gold); color:#fff; border:none; padding:11px 28px; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer; }
        .btn-gold:hover { background:#b8922e; }
        .btn-green { background:var(--green); color:#fff; border:none; padding:11px 24px; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer; }
        .btn-green:hover { background:#15803d; }
        .btn-sm-act { background:var(--blue); color:#fff; border:none; padding:5px 12px; border-radius:5px; font-size:11px; cursor:pointer; font-weight:600; }

        /* ── Result/status boxes ── */
        .result-box { border-radius:8px; padding:14px 18px; font-size:13px; font-weight:600; margin-bottom:16px; }
        .result-ok   { background:#d4f5e2; color:#1a7a45; }
        .result-err  { background:#fee2e2; color:#b91c1c; }
        .result-info { background:#e8f0ff; color:#002446; }

        /* ── Preview table ── */
        .preview-table { width:100%; border-collapse:collapse; font-size:12px; margin-top:16px; }
        .preview-table th { background:#f0f4fb; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#555; padding:9px 12px; border-bottom:1px solid #e2e8f0; text-align:left; }
        .preview-table td { padding:9px 12px; border-bottom:1px solid #f4f6fa; }
        .preview-table tr:last-child td { border-bottom:none; }
        .badge-ok   { background:#dcfce7; color:#15803d; padding:2px 9px; border-radius:20px; font-size:10px; font-weight:700; }
        .badge-warn { background:#fef9c3; color:#854d0e; padding:2px 9px; border-radius:20px; font-size:10px; font-weight:700; }
        .badge-err  { background:#fee2e2; color:#b91c1c; padding:2px 9px; border-radius:20px; font-size:10px; font-weight:700; }

        /* ── Outlook sources grid ── */
        .sources-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        @media(max-width:600px) { .sources-grid { grid-template-columns:1fr; } }
        .source-row { background:#f8faff; border-radius:8px; padding:14px; border:1px solid #e2e8f0; }
        .source-row label { font-size:11px; font-weight:700; color:#444; text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px; display:block; }

        /* ── Outlook results table ── */
        .outlook-bar { height:8px; border-radius:4px; background:#e2e8f0; margin-top:4px; }
        .outlook-fill { height:100%; border-radius:4px; background:var(--gold); transition:width .4s; }

        /* ── Costs grid ── */
        .costs-nb-header { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--brand); background:#f0f4fb; padding:8px 14px; border-radius:6px; margin-bottom:8px; }
        .inline-form { display:grid; grid-template-columns:1fr 1fr 1fr 1fr auto; gap:8px; align-items:end; }
        @media(max-width:700px) { .inline-form { grid-template-columns:1fr 1fr; } }

        /* ── History pills ── */
        .history-pill { display:inline-flex; align-items:center; gap:6px; background:#f0f4fb; border-radius:20px; padding:4px 12px; font-size:11px; font-weight:600; color:#445; margin:3px; }
        .history-pill .csv-type { background:var(--brand); color:#fff; border-radius:10px; padding:1px 7px; font-size:10px; }

        /* ── Spinner ── */
        .spinner { display:none; width:20px; height:20px; border:3px solid #e2e8f0; border-top-color:var(--blue); border-radius:50%; animation:spin .7s linear infinite; margin:0 auto; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .loading .spinner { display:inline-block; }
    </style>
</head>
<body>

<!-- Top bar -->
<div class="topbar">
    <div>
        <h1>🗺️ W.I.N — Plex Data Admin</h1>
        <span>Wynston Intelligent Navigator — Market Intelligence</span>
    </div>
    <div class="topbar-btns">
        <a href="../admin.php" class="btn-nav"><i class="fas fa-arrow-left me-1"></i>Main Admin</a>
        <a href="../plex-map/" target="_blank" class="btn-nav"><i class="fas fa-map me-1"></i>Plex Map</a>
        <a href="plex-data.php?logout=1" class="btn-nav"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
    </div>
</div>

<!-- Tab nav -->
<div class="tab-nav">
    <a href="plex-data.php?tab=channel-a" class="<?= $active_tab==='channel-a'?'active':'' ?>">
        <i class="fas fa-file-csv"></i> Channel A — REBGV Sold
    </a>
    <a href="plex-data.php?tab=channel-b" class="<?= $active_tab==='channel-b'?'active':'' ?>">
        <i class="fas fa-home"></i> Channel B — Rental Data
    </a>
    <a href="plex-data.php?tab=channel-d" class="<?= $active_tab==='channel-d'?'active':'' ?>">
        <i class="fas fa-landmark"></i> Channel D — CMHC
    </a>
    <a href="plex-data.php?tab=outlook" class="tab-outlook <?= $active_tab==='outlook'?'active':'' ?>">
        <i class="fas fa-chart-line"></i> Wynston Outlook
    </a>
    <a href="plex-data.php?tab=costs" class="<?= $active_tab==='costs'?'active':'' ?>">
        <i class="fas fa-hammer"></i> Build Costs
    </a>
    <a href="plex-data.php?tab=history" class="<?= $active_tab==='history'?'active':'' ?>">
        <i class="fas fa-history"></i> Upload History
    </a>
</div>

<!-- Content -->
<div class="content">

<?php if ($msg): ?>
<div class="result-box result-ok"><i class="fas fa-check-circle me-2"></i><?= $msg ?></div>
<?php endif; ?>

<?php // ══════════════════════════════════════════════════════
      // CHANNEL A — REBGV SOLD CSV
      // ══════════════════════════════════════════════════════
if ($active_tab === 'channel-a'): ?>

<div class="section-card">
    <h3><i class="fas fa-file-csv me-2" style="color:var(--blue)"></i>Channel A — REBGV Sold Data</h3>
    <p class="sub">Upload your monthly REBGV CSV export. New builds (Yr Blt ≥ 2024) only. Each address is geocoded via Google. Previous data for the same month is versioned out.</p>

    <div id="chA-result"></div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Data Month</label>
            <input type="month" id="chA_month" class="form-control" value="<?= date('Y-m') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">CSV Type</label>
            <select id="chA_type" class="form-select">
                <option value="duplex">Duplex / Multiplex</option>
                <option value="detached">Detached (ceiling benchmark)</option>
            </select>
        </div>
    </div>

    <div class="drop-zone" id="chA_dz" onclick="document.getElementById('chA_file').click()">
        <i class="fas fa-cloud-upload-alt" id="chA_dz_icon"></i>
        <strong id="chA_dz_text">Drag & drop CSV here, or click to browse</strong>
        <small>REBGV export — columns: Address, Area, Yr Blt, Sold Price, TotFlArea, Price Per SQFT, Tot BR, DOM, Status</small>
        <input type="file" id="chA_file" accept=".csv" style="display:none">
    </div>

    <div id="chA_progress" style="display:none;text-align:center;padding:20px 0;">
        <div class="spinner" id="chA_spinner" style="display:inline-block;"></div>
        <p style="font-size:13px;color:#888;margin-top:10px;" id="chA_progress_msg">Parsing CSV and geocoding addresses…</p>
    </div>

    <button class="btn-primary-w mt-3" id="chA_submit" onclick="submitChannelA()">
        <i class="fas fa-upload me-2"></i>Upload &amp; Process
    </button>

    <div id="chA_preview" style="margin-top:20px;display:none;">
        <h5 style="font-size:14px;font-weight:700;color:var(--brand);">Upload Results</h5>
        <div id="chA_stats" class="row g-2 mb-3"></div>
        <div id="chA_errors" style="font-size:12px;color:#b91c1c;margin-bottom:12px;"></div>
        <div id="chA_table_wrap" style="overflow-x:auto;max-height:400px;overflow-y:auto;"></div>
    </div>

    <div style="margin-top:24px;padding-top:20px;border-top:1px solid #f0f0f0;">
        <h5 style="font-size:13px;font-weight:700;color:#555;">Expected CSV Column Headers</h5>
        <p style="font-size:12px;color:#888;">Your REBGV export should include these columns (names are flexible — the importer matches variations):</p>
        <div style="display:flex;flex-wrap:wrap;gap:6px;font-size:11px;">
            <?php foreach (['Address','Area','Yr Blt','Sold Price','TotFlArea','Price Per SQFT','Tot BR','DOM','Status'] as $c): ?>
            <span style="background:#f0f4fb;border-radius:4px;padding:3px 10px;font-weight:600;color:#445;"><?= $c ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php // ══════════════════════════════════════════════════════
      // CHANNEL B — LIV.RENT RENTAL ENTRY
      // ══════════════════════════════════════════════════════
elseif ($active_tab === 'channel-b'): ?>

<div class="section-card">
    <h3><i class="fas fa-home me-2" style="color:var(--blue)"></i>Channel B — liv.rent Rental Data</h3>
    <p class="sub">Enter monthly rental averages from your liv.rent PDF report. One neighbourhood at a time. Previous data for the same month is versioned out.</p>

    <div id="chB-result"></div>

    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label">Neighbourhood</label>
            <select id="chB_slug" class="form-select">
                <option value="">— Select neighbourhood —</option>
                <?php foreach ($neighbourhoods as $nb): ?>
                <option value="<?= htmlspecialchars($nb['slug']) ?>"><?= htmlspecialchars($nb['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Data Month</label>
            <input type="month" id="chB_month" class="form-control" value="<?= date('Y-m') ?>">
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-3">
            <label class="form-label">Avg 1BR Rent / mo</label>
            <div class="input-group">
                <span class="input-group-text" style="font-size:13px;">$</span>
                <input type="number" id="chB_1br" class="form-control" placeholder="e.g. 2100">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label">Avg 2BR Rent / mo</label>
            <div class="input-group">
                <span class="input-group-text" style="font-size:13px;">$</span>
                <input type="number" id="chB_2br" class="form-control" placeholder="e.g. 2750">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label">Avg 3BR Rent / mo</label>
            <div class="input-group">
                <span class="input-group-text" style="font-size:13px;">$</span>
                <input type="number" id="chB_3br" class="form-control" placeholder="e.g. 3200">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label">Furnished Premium %</label>
            <div class="input-group">
                <input type="number" id="chB_furn" class="form-control" value="20" placeholder="20">
                <span class="input-group-text" style="font-size:13px;">%</span>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <label class="form-label">Source Note <small style="color:#aaa;">(optional)</small></label>
            <input type="text" id="chB_src" class="form-control" placeholder="e.g. liv.rent March 2026 report" value="liv.rent">
        </div>
    </div>

    <button class="btn-primary-w mt-3" onclick="submitChannelB()" style="max-width:280px;">
        <i class="fas fa-save me-2"></i>Save Rental Data
    </button>
</div>

<?php // ══════════════════════════════════════════════════════
      // CHANNEL D — CMHC BENCHMARKS
      // ══════════════════════════════════════════════════════
elseif ($active_tab === 'channel-d'): ?>

<div class="section-card">
    <h3><i class="fas fa-landmark me-2" style="color:var(--blue)"></i>Channel D — CMHC Annual Benchmarks</h3>
    <p class="sub">Enter CMHC annual benchmark rents. One row per neighbourhood per year. Existing rows are updated; new years create new rows.</p>

    <div id="chD-result"></div>

    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label">Neighbourhood</label>
            <select id="chD_slug" class="form-select">
                <option value="">— Select neighbourhood —</option>
                <?php foreach ($neighbourhoods as $nb): ?>
                <option value="<?= htmlspecialchars($nb['slug']) ?>"><?= htmlspecialchars($nb['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Year</label>
            <input type="number" id="chD_year" class="form-control" value="<?= date('Y') ?>" min="2020" max="2035">
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-3">
            <label class="form-label">1BR Benchmark / mo</label>
            <div class="input-group">
                <span class="input-group-text" style="font-size:13px;">$</span>
                <input type="number" id="chD_1br" class="form-control" placeholder="e.g. 2050">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label">2BR Benchmark / mo</label>
            <div class="input-group">
                <span class="input-group-text" style="font-size:13px;">$</span>
                <input type="number" id="chD_2br" class="form-control" placeholder="e.g. 2650">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label">3BR Benchmark / mo</label>
            <div class="input-group">
                <span class="input-group-text" style="font-size:13px;">$</span>
                <input type="number" id="chD_3br" class="form-control" placeholder="e.g. 3100">
            </div>
        </div>
    </div>

    <button class="btn-primary-w mt-3" onclick="submitChannelD()" style="max-width:280px;">
        <i class="fas fa-save me-2"></i>Save CMHC Benchmark
    </button>
</div>

<?php if (!empty($cmhc_rows)): ?>
<div class="section-card">
    <h3>CMHC Benchmark History</h3>
    <p class="sub"><?= count($cmhc_rows) ?> rows stored</p>
    <div style="overflow-x:auto;">
    <table class="preview-table">
        <thead><tr><th>Neighbourhood</th><th>Year</th><th>1BR</th><th>2BR</th><th>3BR</th></tr></thead>
        <tbody>
        <?php foreach ($cmhc_rows as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['neighbourhood_slug']) ?></td>
            <td><?= (int)$r['year'] ?></td>
            <td><?= $r['benchmark_1br'] ? '$'.number_format($r['benchmark_1br']) : '—' ?></td>
            <td><?= $r['benchmark_2br'] ? '$'.number_format($r['benchmark_2br']) : '—' ?></td>
            <td><?= $r['benchmark_3br'] ? '$'.number_format($r['benchmark_3br']) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php // ══════════════════════════════════════════════════════
      // WYNSTON OUTLOOK
      // ══════════════════════════════════════════════════════
elseif ($active_tab === 'outlook'): ?>

<div class="section-card">
    <h3><i class="fas fa-chart-line me-2" style="color:var(--gold)"></i>Wynston Outlook — Quarterly Forecast</h3>
    <p class="sub">Enter the 6 bank/broker YoY $/PSF forecasts, then click Calculate to run the three-layer Wynston Outlook formula for all neighbourhoods.</p>

    <div id="outlook-result"></div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Quarter</label>
            <select id="ol_quarter" class="form-select">
                <?php
                $qy = (int)date('Y'); $qm = (int)date('m');
                $cq = 'Q'.(ceil($qm/3));
                for ($i=0;$i<8;$i++) {
                    $q = 'Q'.(((ceil($qm/3)-1-$i+16)%4)+1);
                    $y = $qy - (int)floor((4-ceil($qm/3)+$i)/4);
                    $val = "{$y}-Q".(((ceil($qm/3)-1-$i+16)%4)+1);
                    echo "<option value=\"{$val}\"".($i===0?' selected':'').">{$val}</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <h5 style="font-size:13px;font-weight:700;color:#555;margin-bottom:12px;">Forecast Sources — YoY $/PSF Change (%)</h5>
    <div class="sources-grid" id="sources-grid">
        <?php
        $default_sources = ['RBC','TD','BMO','BCREA','RE-MAX','Royal LePage'];
        foreach ($default_sources as $i => $src):
            $existing_val = '';
            foreach ($outlook_inputs as $oi) {
                if ($oi['source_name'] === $src) { $existing_val = $oi['forecast_psf_yoy']; break; }
            }
        ?>
        <div class="source-row">
            <label><?= $src ?></label>
            <div class="row g-2">
                <div class="col-7">
                    <div class="input-group">
                        <input type="number" step="0.1" class="form-control src-forecast"
                               data-source="<?= $src ?>" placeholder="e.g. 4.5"
                               value="<?= htmlspecialchars($existing_val) ?>">
                        <span class="input-group-text" style="font-size:12px;">%</span>
                    </div>
                </div>
                <div class="col-5">
                    <input type="date" class="form-control src-date" data-source="<?= $src ?>"
                           placeholder="Forecast date" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="d-flex gap-3 mt-4">
        <button class="btn-primary-w" onclick="saveOutlookInputs()" style="max-width:200px;">
            <i class="fas fa-save me-1"></i>Save Inputs
        </button>
        <button class="btn-gold" onclick="calculateOutlook()">
            <i class="fas fa-calculator me-1"></i>Calculate Wynston Outlook
        </button>
    </div>
</div>

<?php if (!empty($outlook_results)): ?>
<div class="section-card">
    <h3>Current Outlook Results</h3>
    <p class="sub">Last calculated — <?= count($outlook_results) ?> neighbourhoods. Tier 1 = high confidence (5+ comps), Tier 3 = macro fallback.</p>
    <div style="overflow-x:auto;">
    <table class="preview-table">
        <thead>
            <tr>
                <th>Neighbourhood</th>
                <th>Outlook %</th>
                <th>Confidence Band</th>
                <th>Tier</th>
                <th>Comps</th>
                <th>Quarter</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($outlook_results as $r):
            $outlook_pct = (float)$r['weighted_outlook'];
            $color = $outlook_pct >= 5 ? '#15803d' : ($outlook_pct >= 0 ? '#c9a84c' : '#b91c1c');
        ?>
        <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($r['neighbourhood_slug']) ?></td>
            <td>
                <span style="font-weight:800;color:<?= $color ?>;font-size:14px;">
                    <?= $outlook_pct >= 0 ? '+' : '' ?><?= number_format($outlook_pct, 1) ?>%
                </span>
                <div class="outlook-bar">
                    <div class="outlook-fill" style="width:<?= min(100, max(0, ($outlook_pct+15)/30*100)) ?>%;background:<?= $color ?>"></div>
                </div>
            </td>
            <td style="font-size:11px;color:#888;">
                <?= number_format((float)$r['confidence_band_low'],1) ?>% — <?= number_format((float)$r['confidence_band_high'],1) ?>%
            </td>
            <td>
                <?php $tier = (int)$r['confidence_tier'];
                      $tc = $tier===1?'#15803d':($tier===2?'#c9a84c':'#b91c1c'); ?>
                <span style="font-weight:700;color:<?= $tc ?>">T<?= $tier ?></span>
            </td>
            <td style="text-align:center;"><?= (int)$r['comp_count'] ?></td>
            <td style="font-size:11px;color:#888;"><?= htmlspecialchars($r['quarter'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php // ══════════════════════════════════════════════════════
      // BUILD COSTS
      // ══════════════════════════════════════════════════════
elseif ($active_tab === 'costs'): ?>

<div class="section-card">
    <h3><i class="fas fa-hammer me-2" style="color:var(--blue)"></i>Construction Cost Overrides</h3>
    <p class="sub">Set $/sqft cost ranges by neighbourhood. Defaults apply where no override exists: Standard $380–$450, Luxury $480–$550, DCL city $18.45, DCL utilities $2.95, Peat contingency $150,000.</p>

    <div id="costs-result"></div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Neighbourhood</label>
            <select id="cost_slug" class="form-select">
                <option value="">— Select neighbourhood —</option>
                <?php foreach ($neighbourhoods as $nb): ?>
                <option value="<?= htmlspecialchars($nb['slug']) ?>"><?= htmlspecialchars($nb['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-2">
            <label class="form-label">Std Low $/sqft</label>
            <input type="number" id="cost_std_lo" class="form-control" placeholder="380">
        </div>
        <div class="col-md-2">
            <label class="form-label">Std High $/sqft</label>
            <input type="number" id="cost_std_hi" class="form-control" placeholder="450">
        </div>
        <div class="col-md-2">
            <label class="form-label">Lux Low $/sqft</label>
            <input type="number" id="cost_lux_lo" class="form-control" placeholder="480">
        </div>
        <div class="col-md-2">
            <label class="form-label">Lux High $/sqft</label>
            <input type="number" id="cost_lux_hi" class="form-control" placeholder="550">
        </div>
        <div class="col-md-2">
            <label class="form-label">DCL City $/sqft</label>
            <input type="number" step="0.01" id="cost_dcl_city" class="form-control" placeholder="18.45">
        </div>
        <div class="col-md-2">
            <label class="form-label">DCL Utils $/sqft</label>
            <input type="number" step="0.01" id="cost_dcl_util" class="form-control" placeholder="2.95">
        </div>
    </div>
    <div class="row g-3 mt-0">
        <div class="col-md-3">
            <label class="form-label">Peat Contingency $</label>
            <div class="input-group">
                <span class="input-group-text" style="font-size:13px;">$</span>
                <input type="number" id="cost_peat" class="form-control" placeholder="150000">
            </div>
        </div>
        <div class="col-md-5">
            <label class="form-label">Notes <small style="color:#aaa;">(optional)</small></label>
            <input type="text" id="cost_notes" class="form-control" placeholder="e.g. Heritage district premium applied">
        </div>
    </div>

    <button class="btn-primary-w mt-3" onclick="saveCost()" style="max-width:280px;">
        <i class="fas fa-save me-2"></i>Save Cost Override
    </button>
</div>

<?php if (!empty($cost_rows)): ?>
<div class="section-card">
    <h3>Saved Cost Overrides</h3>
    <div style="overflow-x:auto;">
    <table class="preview-table">
        <thead>
            <tr>
                <th>Neighbourhood</th>
                <th>Std Low</th><th>Std High</th>
                <th>Lux Low</th><th>Lux High</th>
                <th>DCL City</th><th>DCL Utils</th>
                <th>Peat</th><th>Notes</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cost_rows as $r): ?>
        <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($r['neighbourhood_slug']) ?></td>
            <td>$<?= number_format((float)$r['cost_standard_low']) ?></td>
            <td>$<?= number_format((float)$r['cost_standard_high']) ?></td>
            <td>$<?= number_format((float)$r['cost_luxury_low']) ?></td>
            <td>$<?= number_format((float)$r['cost_luxury_high']) ?></td>
            <td>$<?= number_format((float)$r['dcl_city'],2) ?></td>
            <td>$<?= number_format((float)$r['dcl_utilities'],2) ?></td>
            <td>$<?= number_format((float)$r['peat_contingency']) ?></td>
            <td style="font-size:11px;color:#888;"><?= htmlspecialchars($r['notes'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php // ══════════════════════════════════════════════════════
      // UPLOAD HISTORY
      // ══════════════════════════════════════════════════════
elseif ($active_tab === 'history'): ?>

<div class="section-card">
    <h3><i class="fas fa-history me-2" style="color:var(--blue)"></i>Upload History</h3>
    <p class="sub">All data currently in monthly_market_stats — grouped by neighbourhood, month, and type. Only is_active=1 rows shown.</p>

    <?php if (empty($recent_uploads)): ?>
    <div style="text-align:center;padding:40px;color:#aaa;">
        <i class="fas fa-database" style="font-size:36px;display:block;margin-bottom:12px;opacity:.3;"></i>
        No uploads yet. Start with Channel A.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="preview-table">
        <thead>
            <tr>
                <th>Neighbourhood</th>
                <th>Month</th>
                <th>Type</th>
                <th>Rows</th>
                <th>Uploaded</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($recent_uploads as $r): ?>
        <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($r['neighbourhood_slug']) ?></td>
            <td><?= date('M Y', strtotime($r['data_month'])) ?></td>
            <td>
                <span class="<?= $r['csv_type']==='duplex'?'badge-ok':($r['csv_type']==='rental'?'badge-warn':'') ?>
                             " style="padding:2px 9px;border-radius:20px;font-size:10px;font-weight:700;background:<?=
                    $r['csv_type']==='duplex'  ? '#dcfce7;color:#15803d' :
                   ($r['csv_type']==='rental'  ? '#e8f0ff;color:#002446' :
                   ($r['csv_type']==='detached'? '#fef9c3;color:#854d0e' : '#f0f4fb;color:#444')) ?>">
                    <?= htmlspecialchars($r['csv_type']) ?>
                </span>
            </td>
            <td style="font-weight:700;"><?= (int)$r['row_count'] ?></td>
            <td style="font-size:11px;color:#888;"><?= date('M j Y g:ia', strtotime($r['uploaded_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>
</div><!-- /content -->

<script>
// ── Channel A — File upload + AJAX ────────────────────────────────────────────
(function() {
    var dz = document.getElementById('chA_dz');
    if (!dz) return;
    ['dragenter','dragover'].forEach(function(e) {
        dz.addEventListener(e, function(ev) { ev.preventDefault(); dz.classList.add('dz-active'); });
    });
    dz.addEventListener('dragleave', function() { dz.classList.remove('dz-active'); });
    dz.addEventListener('drop', function(e) {
        e.preventDefault(); dz.classList.remove('dz-active');
        document.getElementById('chA_file').files = e.dataTransfer.files;
        setFileSelected(e.dataTransfer.files[0].name);
    });
    document.getElementById('chA_file').addEventListener('change', function() {
        if (this.files[0]) setFileSelected(this.files[0].name);
    });
    function setFileSelected(name) {
        dz.classList.add('dz-selected');
        document.getElementById('chA_dz_icon').className = 'fas fa-check-circle';
        document.getElementById('chA_dz_text').textContent = '✅ ' + name;
    }
})();

function submitChannelA() {
    var file  = document.getElementById('chA_file').files[0];
    var month = document.getElementById('chA_month').value;
    var type  = document.getElementById('chA_type').value;
    if (!file)  { showResult('chA-result','Please select a CSV file.','err'); return; }
    if (!month) { showResult('chA-result','Please select a data month.','err'); return; }

    var fd = new FormData();
    fd.append('rebgv_csv', file);
    fd.append('data_month', month);
    fd.append('csv_type', type);

    document.getElementById('chA_progress').style.display = 'block';
    document.getElementById('chA_submit').disabled = true;
    document.getElementById('chA_preview').style.display = 'none';
    document.getElementById('chA-result').innerHTML = '';

    var msgs = ['Parsing CSV…','Geocoding addresses…','Applying versioning…'];
    var mi = 0;
    var pmsg = document.getElementById('chA_progress_msg');
    var ticker = setInterval(function() { pmsg.textContent = msgs[mi++ % msgs.length]; }, 2500);

    fetch('../api/plex_upload_a.php', { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            clearInterval(ticker);
            document.getElementById('chA_progress').style.display = 'none';
            document.getElementById('chA_submit').disabled = false;
            if (!d.success) { showResult('chA-result', d.error || 'Upload failed.', 'err'); return; }

            showResult('chA-result',
                '✅ Upload complete — ' + d.inserted + ' rows inserted for ' + d.month + ' (' + d.csv_type + ')',
                'ok');

            // Stats
            var stats = document.getElementById('chA_stats');
            stats.innerHTML =
                pill(d.inserted, 'Inserted', '#d4f5e2','#1a7a45') +
                pill(d.skipped,  'Skipped',  '#fef9c3','#854d0e') +
                pill(d.errors ? d.errors.length : 0, 'Errors', '#fee2e2','#b91c1c');

            // Errors
            if (d.errors && d.errors.length) {
                document.getElementById('chA_errors').innerHTML =
                    '<strong>Warnings:</strong><br>' + d.errors.slice(0,10).join('<br>') +
                    (d.errors.length > 10 ? '<br>…and '+(d.errors.length-10)+' more' : '');
            }

            // Preview table
            if (d.rows && d.rows.length) {
                var html = '<table class="preview-table"><thead><tr>' +
                    '<th>Address</th><th>Neighbourhood</th><th>Yr Blt</th><th>Sold Price</th><th>$/PSF</th><th>Geocoded</th>' +
                    '</tr></thead><tbody>';
                d.rows.forEach(function(r) {
                    html += '<tr>' +
                        '<td>'+esc(r.address)+'</td>' +
                        '<td>'+esc(r.nb_slug)+'</td>' +
                        '<td>'+(r.yr_blt||'—')+'</td>' +
                        '<td>'+(r.sold_price ? '$'+r.sold_price.toLocaleString() : '—')+'</td>' +
                        '<td>'+(r.price_per_sqft ? '$'+r.price_per_sqft : '—')+'</td>' +
                        '<td>'+(r.geocoded ? '<span class="badge-ok">✓</span>' : '<span class="badge-err">✗</span>')+'</td>' +
                        '</tr>';
                });
                html += '</tbody></table>';
                document.getElementById('chA_table_wrap').innerHTML = html;
            }
            document.getElementById('chA_preview').style.display = 'block';
        })
        .catch(function(e) {
            clearInterval(ticker);
            document.getElementById('chA_progress').style.display = 'none';
            document.getElementById('chA_submit').disabled = false;
            showResult('chA-result','Network error: '+e.message,'err');
        });
}

function pill(val, label, bg, color) {
    return '<div class="col-auto"><div style="background:'+bg+';color:'+color+';border-radius:8px;padding:12px 18px;text-align:center;min-width:90px;">' +
        '<div style="font-size:24px;font-weight:900;">'+val+'</div>' +
        '<div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;">'+label+'</div>' +
        '</div></div>';
}

// ── Channel B ─────────────────────────────────────────────────────────────────
function submitChannelB() {
    var data = {
        neighbourhood_slug:    document.getElementById('chB_slug').value,
        data_month:            document.getElementById('chB_month').value,
        rent_1br:              document.getElementById('chB_1br').value,
        rent_2br:              document.getElementById('chB_2br').value,
        rent_3br:              document.getElementById('chB_3br').value,
        furnished_premium_pct: document.getElementById('chB_furn').value,
        source_note:           document.getElementById('chB_src').value,
    };
    if (!data.neighbourhood_slug) { showResult('chB-result','Please select a neighbourhood.','err'); return; }
    if (!data.data_month)         { showResult('chB-result','Please select a data month.','err'); return; }
    if (!data.rent_1br && !data.rent_2br && !data.rent_3br) {
        showResult('chB-result','Please enter at least one rent value.','err'); return;
    }
    postJSON('../api/plex_upload_b.php', data, function(d) {
        if (d.success) showResult('chB-result', '✅ ' + d.message, 'ok');
        else           showResult('chB-result', '❌ ' + (d.error||'Error'), 'err');
    });
}

// ── Channel D ─────────────────────────────────────────────────────────────────
function submitChannelD() {
    var data = {
        neighbourhood_slug: document.getElementById('chD_slug').value,
        year:               document.getElementById('chD_year').value,
        benchmark_1br:      document.getElementById('chD_1br').value,
        benchmark_2br:      document.getElementById('chD_2br').value,
        benchmark_3br:      document.getElementById('chD_3br').value,
    };
    if (!data.neighbourhood_slug) { showResult('chD-result','Please select a neighbourhood.','err'); return; }
    if (!data.year)               { showResult('chD-result','Please enter a year.','err'); return; }
    if (!data.benchmark_1br && !data.benchmark_2br && !data.benchmark_3br) {
        showResult('chD-result','Please enter at least one benchmark value.','err'); return;
    }
    postJSON('../api/plex_upload_d.php', data, function(d) {
        if (d.success) showResult('chD-result', '✅ ' + d.message, 'ok');
        else           showResult('chD-result', '❌ ' + (d.error||'Error'), 'err');
    });
}

// ── Wynston Outlook ───────────────────────────────────────────────────────────
function saveOutlookInputs() {
    var quarter = document.getElementById('ol_quarter').value;
    var sources = [];
    document.querySelectorAll('.src-forecast').forEach(function(inp) {
        if (!inp.value) return;
        var src  = inp.dataset.source;
        var date = document.querySelector('.src-date[data-source="'+src+'"]');
        sources.push({ source_name: src, forecast_psf_yoy: inp.value, forecast_date: date ? date.value : '' });
    });
    if (!sources.length) { showResult('outlook-result','Enter at least one forecast value.','err'); return; }
    var fd = new FormData();
    fd.append('action','save_inputs');
    fd.append('quarter', quarter);
    fd.append('sources', JSON.stringify(sources)); // backend reads $_POST['sources']
    // Send as JSON instead
    postJSON('../api/plex_outlook.php', { action:'save_inputs', quarter:quarter, sources:sources }, function(d) {
        if (d.success) showResult('outlook-result', '✅ ' + d.message, 'ok');
        else           showResult('outlook-result', '❌ ' + (d.error||'Error'), 'err');
    });
}

function calculateOutlook() {
    var quarter = document.getElementById('ol_quarter').value;
    showResult('outlook-result','<i class="fas fa-spinner fa-spin me-2"></i>Calculating Wynston Outlook for all neighbourhoods…','info');
    postJSON('../api/plex_outlook.php', { action:'calculate', quarter:quarter }, function(d) {
        if (d.success) {
            showResult('outlook-result',
                '✅ Outlook calculated — ' + d.neighbourhoods_updated + ' neighbourhoods updated for ' + d.quarter +
                ' (Macro signal: ' + (d.macro_signal >= 0 ? '+' : '') + d.macro_signal + '%). ' +
                '<a href="plex-data.php?tab=outlook" style="color:var(--blue);font-weight:700;">Refresh to see results ↗</a>',
                'ok');
        } else {
            showResult('outlook-result', '❌ ' + (d.error||'Error'), 'err');
        }
    });
}

// ── Build Costs ───────────────────────────────────────────────────────────────
function saveCost() {
    var data = {
        action:              'save_cost',
        neighbourhood_slug:  document.getElementById('cost_slug').value,
        cost_standard_low:   document.getElementById('cost_std_lo').value,
        cost_standard_high:  document.getElementById('cost_std_hi').value,
        cost_luxury_low:     document.getElementById('cost_lux_lo').value,
        cost_luxury_high:    document.getElementById('cost_lux_hi').value,
        dcl_city:            document.getElementById('cost_dcl_city').value,
        dcl_utilities:       document.getElementById('cost_dcl_util').value,
        peat_contingency:    document.getElementById('cost_peat').value,
        notes:               document.getElementById('cost_notes').value,
    };
    if (!data.neighbourhood_slug) { showResult('costs-result','Please select a neighbourhood.','err'); return; }
    postJSON('../api/plex_costs.php', data, function(d) {
        if (d.success) {
            showResult('costs-result', '✅ Cost override ' + d.action + ' for ' + d.slug + '. <a href="plex-data.php?tab=costs" style="color:var(--blue);font-weight:700;">Refresh to see table ↗</a>', 'ok');
        } else {
            showResult('costs-result', '❌ ' + (d.error||'Error'), 'err');
        }
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function showResult(el, msg, type) {
    var cls = type==='ok' ? 'result-ok' : (type==='err' ? 'result-err' : 'result-info');
    document.getElementById(el).innerHTML = '<div class="result-box '+cls+'">'+msg+'</div>';
}

function postJSON(url, data, cb) {
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).then(function(r) { return r.json(); }).then(cb)
      .catch(function(e) { cb({ success:false, error: e.message }); });
}

function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
