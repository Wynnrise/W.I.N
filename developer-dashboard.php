<?php
// ============================================================
//  developer-dashboard.php  —  Wynston Developer Portal
//  Merged: Listings dashboard + W.I.N Project Planner
//  User-type aware: builder | investor | realtor | broker
// ============================================================
require_once __DIR__ . '/dev-auth.php';
dev_require_login('log-in.php');

$dev = dev_current();
$user_type = $dev['user_type'] ?? 'builder';

// ── Listings stats (builder / realtor only) ────────────────
$stats = ['total' => 0, 'pending' => 0, 'live' => 0, 'drafts' => 0];
$my_listings = [];
if (in_array($user_type, ['builder', 'realtor'])) {
    try {
        $dev_id = (int)$dev['id'];
        $s = $pdo->prepare("SELECT id, address, property_type, price, submit_status, is_paid, img1
                             FROM multi_2025 WHERE submitted_by = ? ORDER BY id DESC");
        $s->execute([$dev_id]);
        $my_listings = $s->fetchAll(PDO::FETCH_ASSOC);
        foreach ($my_listings as $l) {
            $stats['total']++;
            $st = $l['submit_status'] ?? 'draft';
            if ($st === 'pending_review')               $stats['pending']++;
            elseif (in_array($st, ['approved','live'])) $stats['live']++;
            else                                        $stats['drafts']++;
        }
    } catch (Exception $e) {}
}

// ── W.I.N data for all user types ─────────────────────────
$dev_id      = (int)$dev['id'];
$saved_lots  = [];
$acquisitions = [];
$reports     = [];
$trends      = [];

try {
    // Saved lots
    $s = $pdo->prepare("
        SELECT sl.id, sl.pid, sl.address, sl.saved_at, sl.notes,
               p.lot_width_m, p.lot_area_sqm, p.lane_access,
               p.transit_proximate, p.heritage_category, p.peat_zone,
               p.neighbourhood_slug, p.lat, p.lng
        FROM saved_lots sl
        LEFT JOIN plex_properties p ON p.pid = sl.pid
        WHERE sl.developer_id = ?
        ORDER BY sl.saved_at DESC
    ");
    $s->execute([$dev_id]);
    $saved_lots = $s->fetchAll(PDO::FETCH_ASSOC);

    // Acquisitions (builder + investor only)
    if (in_array($user_type, ['builder', 'investor'])) {
        $s = $pdo->prepare("
            SELECT id, pid, address, status, requested_at, updated_at
            FROM acquisition_requests WHERE developer_id = ?
            ORDER BY updated_at DESC
        ");
        $s->execute([$dev_id]);
        $acquisitions = $s->fetchAll(PDO::FETCH_ASSOC);
    }

    // PDF report history
    $s = $pdo->prepare("
        SELECT pid, address, generated_at, report_id
        FROM pdf_log WHERE developer_id = ?
        ORDER BY generated_at DESC LIMIT 10
    ");
    $s->execute([$dev_id]);
    $reports = $s->fetchAll(PDO::FETCH_ASSOC);

    // Neighbourhood trends
    $trends = $pdo->query("
        SELECT neighbourhood_slug, avg_sold_psf_duplex, data_month
        FROM monthly_market_stats
        WHERE is_active = 1 AND csv_type = 'duplex'
        ORDER BY avg_sold_psf_duplex DESC LIMIT 6
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {}

// ── Helpers ────────────────────────────────────────────────
function get_tier($w, $lane, $transit) {
    $w = (float)$w;
    if ($w >= 15.1 && $transit && $lane) return ['6-Unit', '#22c55e', '#dcfce7', '#166534'];
    if ($w >= 10.0 && $lane)             return ['4-Unit', '#14b8a6', '#ccfbf1', '#0f5540'];
    if ($w >= 7.5  && $lane)             return ['Duplex',  '#f59e0b', '#fef3c7', '#92400e'];
    return ['Below Min', '#94a3b8', '#f1f5f9', '#475569'];
}

$status_labels = [
    'under_review'  => ['Under Review',  '#f59e0b', '#fffbeb', '#92400e'],
    'analysis_sent' => ['Analysis Sent', '#0065ff', '#eff6ff', '#1d4ed8'],
    'negotiation'   => ['Negotiation',   '#22c55e', '#f0fdf4', '#166534'],
    'closed'        => ['Closed',         '#94a3b8', '#f8fafc', '#475569'],
];

// Role display config
$role_config = [
    'builder'  => ['label' => 'Builder',  'icon' => 'fa-hard-hat',    'colour' => '#002446'],
    'investor' => ['label' => 'Investor', 'icon' => 'fa-chart-line',  'colour' => '#0065ff'],
    'realtor'  => ['label' => 'Realtor',  'icon' => 'fa-id-badge',    'colour' => '#22c55e'],
    'broker'   => ['label' => 'Broker',   'icon' => 'fa-file-invoice-dollar', 'colour' => '#c9a84c'],
];
$rc = $role_config[$user_type] ?? $role_config['builder'];

$first_name = explode(' ', trim($dev['full_name']))[0];

// Active tab
$active_tab = $_GET['tab'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="/assets/img/favicon.png">
<title>Dashboard — <?= htmlspecialchars($dev['company_name'] ?? 'Wynston') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
:root {
    --navy: #002446;
    --gold: #c9a84c;
    --cream: #f9f6f0;
    --bdr: #e8e4dd;
    --dark: #0d0d1a;
}
*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--cream); margin: 0; color: var(--dark); }

/* ── Topbar ────────────────────────────── */
.topbar { background: var(--dark); padding: 0 28px; display: flex; align-items: center; justify-content: space-between; height: 54px; position: sticky; top: 0; z-index: 200; }
.topbar-brand { display: flex; align-items: center; gap: 12px; }
.topbar-brand img { height: 30px; }
.topbar-brand-name { color: #fff; font-weight: 800; font-size: 17px; }
.topbar-right { display: flex; align-items: center; gap: 16px; font-size: 13px; color: #aaa; }
.topbar-right a { color: #aaa; text-decoration: none; }
.topbar-right a:hover { color: #fff; }
.role-badge { background: rgba(201,168,76,0.15); border: 1px solid rgba(201,168,76,0.3); color: var(--gold); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }

/* ── Layout ────────────────────────────── */
.layout { display: grid; grid-template-columns: 220px 1fr; min-height: calc(100vh - 54px); }

/* ── Sidebar ───────────────────────────── */
.sidebar { background: var(--navy); display: flex; flex-direction: column; padding-bottom: 24px; }
.sb-user { padding: 20px 18px 16px; border-bottom: 1px solid rgba(255,255,255,0.08); }
.sb-avatar { width: 46px; height: 46px; border-radius: 50%; background: rgba(201,168,76,0.18); border: 2px solid rgba(201,168,76,0.35); display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; color: var(--gold); margin-bottom: 10px; overflow: hidden; }
.sb-avatar img { width: 100%; height: 100%; object-fit: cover; }
.sb-name { font-size: 14px; font-weight: 700; color: #fff; margin: 0 0 2px; }
.sb-company { font-size: 12px; color: rgba(255,255,255,0.4); margin: 0 0 8px; }
.sb-role-pill { display: inline-flex; align-items: center; gap: 5px; background: rgba(201,168,76,0.12); border: 1px solid rgba(201,168,76,0.25); color: var(--gold); font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }

.sb-section-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,0.22); padding: 18px 18px 5px; }
.sb-link { display: flex; align-items: center; gap: 9px; padding: 9px 18px; color: rgba(255,255,255,0.5); font-size: 13px; text-decoration: none; border-left: 3px solid transparent; transition: all 0.12s; }
.sb-link:hover { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.85); text-decoration: none; }
.sb-link.active { background: rgba(255,255,255,0.09); color: #fff; border-left-color: var(--gold); }
.sb-link.win-link { color: rgba(201,168,76,0.7); }
.sb-link.win-link:hover { color: var(--gold); }
.sb-link.win-link.active { color: var(--gold); background: rgba(201,168,76,0.1); border-left-color: var(--gold); }
.sb-link i { width: 16px; text-align: center; font-size: 13px; flex-shrink: 0; }
.sb-spacer { flex: 1; }

/* ── Main area ─────────────────────────── */
.main { background: var(--cream); overflow-y: auto; }
.main-inner { padding: 30px 36px 60px; max-width: 1100px; }

/* ── Page header ───────────────────────── */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 12px; }
.page-title { font-size: 24px; font-weight: 800; color: var(--navy); margin: 0 0 4px; }
.page-sub { font-size: 13px; color: #888; margin: 0; }
.btn-map { background: var(--gold); color: var(--navy); border: none; padding: 10px 22px; border-radius: 8px; font-weight: 700; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
.btn-map:hover { background: #b8973d; color: var(--navy); text-decoration: none; }

/* ── Status banner ─────────────────────── */
.status-banner { border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 14px; font-size: 13px; }
.status-banner.pending  { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
.status-banner.approved { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
.status-banner.suspended{ background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

/* ── Stats row ─────────────────────────── */
.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; margin-bottom: 28px; }
.stat-card { background: #fff; border-radius: 10px; padding: 18px 20px; border: 1px solid var(--bdr); }
.stat-card.gold { border-top: 3px solid var(--gold); }
.stat-num { font-size: 30px; font-weight: 800; color: var(--navy); line-height: 1; }
.stat-lbl { font-size: 12px; color: #888; margin-top: 5px; }

/* ── Section ───────────────────────────── */
.section { margin-bottom: 32px; }
.section-head { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--gold); padding-bottom: 10px; margin-bottom: 16px; }
.section-head h2 { font-size: 16px; font-weight: 700; color: var(--navy); margin: 0; }
.section-head a { font-size: 12px; color: var(--navy); text-decoration: none; }
.section-head a:hover { color: var(--gold); }

/* ── Lot cards ─────────────────────────── */
.lot-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 14px; }
.lot-card { background: #fff; border-radius: 10px; padding: 18px; border: 1px solid var(--bdr); border-left: 4px solid var(--tier-c, #94a3b8); transition: box-shadow 0.15s; }
.lot-card:hover { box-shadow: 0 4px 16px rgba(0,36,70,0.1); }
.lot-addr { font-weight: 700; color: var(--navy); font-size: 14px; margin-bottom: 10px; line-height: 1.35; }
.lot-pills { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
.pill { font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 600; }
.lot-flags { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px; }
.flag { font-size: 10px; background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 10px; }
.flag.red { background: #fee2e2; color: #991b1b; }
.lot-notes { font-size: 12px; color: #666; font-style: italic; margin-bottom: 10px; }
.lot-actions { display: flex; flex-wrap: wrap; gap: 7px; }
.lot-date { font-size: 11px; color: #aaa; margin-top: 8px; }

/* ── Buttons ───────────────────────────── */
.btn-s { font-size: 12px; padding: 6px 13px; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
.btn-view    { background: var(--navy); color: var(--gold); }
.btn-view:hover { background: #003366; color: var(--gold); text-decoration: none; }
.btn-report  { background: #f9f6f0; color: var(--navy); border: 1px solid var(--gold); }
.btn-report:hover { background: #f0ead8; text-decoration: none; color: var(--navy); }
.btn-inquire { background: var(--gold); color: var(--navy); }
.btn-inquire:hover { background: #b8973d; color: var(--navy); text-decoration: none; }
.btn-remove  { background: #fee2e2; color: #991b1b; }
.btn-remove:hover { background: #fecaca; color: #991b1b; text-decoration: none; }
.btn-primary-full { background: var(--navy); color: var(--gold); border: none; padding: 11px 24px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
.btn-primary-full:hover { background: #003366; color: var(--gold); text-decoration: none; }
.btn-outline-full { background: transparent; color: var(--navy); border: 1px solid var(--bdr); padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
.btn-outline-full:hover { background: #fff; text-decoration: none; color: var(--navy); }

/* ── Table ─────────────────────────────── */
.data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; border: 1px solid var(--bdr); }
.data-table thead tr { background: var(--navy); }
.data-table th { padding: 11px 16px; color: var(--gold); font-size: 12px; text-align: left; font-weight: 600; }
.data-table td { padding: 11px 16px; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: #fafaf8; }

/* ── Status badge ──────────────────────── */
.acq-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px; }
.acq-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* ── Listing badge ─────────────────────── */
.db-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; display: inline-block; }
.db-badge.live      { background: #dcfce7; color: #166534; }
.db-badge.pending   { background: #fef3c7; color: #92400e; }
.db-badge.draft     { background: #f1f5f9; color: #475569; }
.db-badge.rejected  { background: #fee2e2; color: #991b1b; }
.db-badge.concierge { background: rgba(201,168,76,0.15); color: #92400e; border: 1px solid rgba(201,168,76,0.4); }
.db-badge.standard  { background: #f1f5f9; color: #475569; }
.thumb { width: 44px; height: 44px; object-fit: cover; border-radius: 6px; }
.thumb-placeholder { width: 44px; height: 44px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #aaa; }

/* ── Profile card ──────────────────────── */
.profile-card { background: #fff; border-radius: 10px; padding: 22px 24px; border: 1px solid var(--bdr); }
.profile-card h3 { font-size: 15px; font-weight: 700; color: var(--navy); margin: 0 0 16px; }
.profile-row { display: flex; padding: 9px 0; border-bottom: 1px solid #f5f5f5; font-size: 13px; }
.profile-row:last-child { border-bottom: none; }
.profile-row .key { color: #888; width: 110px; flex-shrink: 0; }
.profile-row .val { color: var(--dark); font-weight: 500; }

/* ── Trends grid ───────────────────────── */
.trends-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 12px; }
.trend-card { background: #fff; border-radius: 10px; padding: 16px 18px; border: 1px solid var(--bdr); }
.trend-nb { font-size: 12px; font-weight: 600; color: var(--navy); text-transform: capitalize; }
.trend-psf { font-size: 22px; font-weight: 800; color: var(--navy); margin: 4px 0 2px; }
.trend-label { font-size: 11px; color: #aaa; }

/* ── Quick actions ─────────────────────── */
.actions-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 28px; }

/* ── Empty state ───────────────────────── */
.empty-state { text-align: center; background: #fff; border-radius: 10px; padding: 48px 24px; border: 1px solid var(--bdr); color: #aaa; }
.empty-state i { font-size: 36px; display: block; margin-bottom: 12px; }
.empty-state h3 { font-size: 16px; font-weight: 700; color: #666; margin: 0 0 8px; }
.empty-state p { font-size: 13px; margin: 0 0 20px; }

/* ── Role info box ─────────────────────── */
.role-infobox { background: rgba(201,168,76,0.06); border: 1px solid rgba(201,168,76,0.2); border-radius: 8px; padding: 12px 16px; font-size: 12px; color: var(--navy); margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
.role-infobox i { color: var(--gold); font-size: 15px; }

/* ── Toast ─────────────────────────────── */
#toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(80px); background: var(--navy); color: var(--gold); padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; z-index: 9999; transition: transform 0.3s; pointer-events: none; white-space: nowrap; }
#toast.show { transform: translateX(-50%) translateY(0); }
</style>
</head>
<body>

<!-- ── Topbar ───────────────────────────────────────────── -->
<div class="topbar">
    <div class="topbar-brand">
        <a href="index.php">
            <img src="/assets/img/logo-light.png" alt="Wynston"
                onerror="this.style.display='none';this.nextSibling.style.display='block'">
            <span style="display:none;color:#fff;font-weight:800;font-size:17px;">WYNSTON</span>
        </a>
        <span style="color:rgba(255,255,255,0.2);font-size:18px;">|</span>
        <span style="color:var(--gold);font-size:13px;font-weight:600;">W.I.N Portal</span>
    </div>
    <div class="topbar-right">
        <span class="role-badge"><i class="fas <?= $rc['icon'] ?> me-1"></i><?= $rc['label'] ?></span>
        <a href="index.php"><i class="fas fa-globe me-1"></i>Site</a>
        <a href="dev-logout.php"><i class="fas fa-power-off me-1"></i>Log Out</a>
    </div>
</div>

<div class="layout">

<!-- ── Sidebar ──────────────────────────────────────────── -->
<div class="sidebar">
    <div class="sb-user">
        <div class="sb-avatar">
            <?php if (!empty($dev['logo_path'])): ?>
                <img src="<?= htmlspecialchars($dev['logo_path']) ?>" alt="">
            <?php else: ?>
                <?= strtoupper(substr($dev['full_name'], 0, 1)) ?>
            <?php endif; ?>
        </div>
        <div class="sb-name"><?= htmlspecialchars($dev['full_name']) ?></div>
        <div class="sb-company"><?= htmlspecialchars($dev['company_name'] ?? '') ?></div>
        <div class="sb-role-pill">
            <i class="fas <?= $rc['icon'] ?>" style="font-size:10px;"></i>
            <?= $rc['label'] ?>
        </div>
    </div>

    <div class="sb-section-label">Dashboard</div>
    <a href="?tab=overview" class="sb-link <?= $active_tab==='overview' ? 'active' : '' ?>">
        <i class="fas fa-gauge"></i>Overview
    </a>

    <?php if (in_array($user_type, ['builder', 'realtor'])): ?>
    <a href="?tab=listings" class="sb-link <?= $active_tab==='listings' ? 'active' : '' ?>">
        <i class="fas fa-building"></i><?= $user_type === 'realtor' ? 'Client Files' : 'My Listings' ?>
    </a>
    <a href="submit-property.php" class="sb-link">
        <i class="fas fa-plus-circle"></i>Submit Property
    </a>
    <?php endif; ?>

    <div class="sb-section-label">W.I.N Map</div>
    <a href="?tab=planner" class="sb-link win-link <?= $active_tab==='planner' ? 'active' : '' ?>">
        <i class="fas fa-map-pin"></i>
        <?php
        if ($user_type === 'realtor')       echo 'Client Planner';
        elseif ($user_type === 'broker')    echo 'Deal Tracker';
        else                                echo 'Project Planner';
        ?>
    </a>
    <a href="/plex-map/" class="sb-link win-link">
        <i class="fas fa-map-marked-alt"></i>Open Map
    </a>
    <a href="?tab=reports" class="sb-link win-link <?= $active_tab==='reports' ? 'active' : '' ?>">
        <i class="fas fa-file-pdf"></i>PDF Reports
    </a>
    <?php if (in_array($user_type, ['builder', 'investor'])): ?>
    <a href="?tab=acquisitions" class="sb-link win-link <?= $active_tab==='acquisitions' ? 'active' : '' ?>">
        <i class="fas fa-handshake"></i><?= $user_type === 'investor' ? 'Lot Inquiries' : 'Acquisition' ?>
    </a>
    <?php endif; ?>
    <?php if ($user_type === 'realtor'): ?>
    <a href="?tab=adplacement" class="sb-link win-link <?= $active_tab==='adplacement' ? 'active' : '' ?>" style="opacity:0.5;">
        <i class="fas fa-ad"></i>Ad Placement <span style="font-size:9px;background:#c9a84c;color:#002446;padding:1px 6px;border-radius:8px;margin-left:4px;">Soon</span>
    </a>
    <?php endif; ?>

    <div class="sb-section-label">Account</div>
    <a href="developer-profile.php" class="sb-link"><i class="fas fa-address-card"></i>My Profile</a>
    <a href="change-password.php" class="sb-link"><i class="fas fa-key"></i>Change Password</a>
    <div class="sb-spacer"></div>
    <a href="dev-logout.php" class="sb-link"><i class="fas fa-power-off"></i>Log Out</a>
</div>

<!-- ── Main content ──────────────────────────────────────── -->
<div class="main">
<div class="main-inner">

<?php // ─────────────────────────────────────────────────────
      // TAB: OVERVIEW
      // ─────────────────────────────────────────────────────
if ($active_tab === 'overview'): ?>

<div class="page-header">
    <div>
        <div class="page-title">Welcome back, <?= htmlspecialchars($first_name) ?> 👋</div>
        <div class="page-sub"><?= date('l, F j, Y') ?><?= $dev['company_name'] ? ' — ' . htmlspecialchars($dev['company_name']) : '' ?></div>
    </div>
    <a href="/plex-map/" class="btn-map"><i class="fas fa-map-marked-alt"></i>Open W.I.N Map</a>
</div>

<!-- Account status -->
<?php $st = $dev['status'] ?? 'pending'; ?>
<div class="status-banner <?= $st ?>">
    <i class="fas <?= $st==='approved' ? 'fa-circle-check' : ($st==='suspended' ? 'fa-ban' : 'fa-clock') ?>" style="font-size:20px;flex-shrink:0;"></i>
    <div>
        <?php if ($st === 'approved'): ?>
        <strong>Account Active</strong> — Full W.I.N access enabled.
        <?php elseif ($st === 'suspended'): ?>
        <strong>Account Suspended</strong> — Contact <a href="mailto:support@wynston.ca" style="color:inherit;">support@wynston.ca</a>.
        <?php else: ?>
        <strong>Account Pending Approval</strong><br>
        <span style="font-size:12px;">Usually approved within 1 business day. You can explore the map in the meantime.</span>
        <?php endif; ?>
    </div>
</div>

<!-- Stats — adapt by role -->
<div class="stats-row">
    <div class="stat-card gold">
        <div class="stat-num"><?= count($saved_lots) ?></div>
        <div class="stat-lbl"><?= $user_type === 'realtor' ? 'Client lots' : 'Saved lots' ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= count($reports) ?></div>
        <div class="stat-lbl">PDF reports</div>
    </div>
    <?php if (in_array($user_type, ['builder', 'investor'])): ?>
    <div class="stat-card">
        <div class="stat-num"><?= count(array_filter($acquisitions, fn($a) => $a['status'] !== 'closed')) ?></div>
        <div class="stat-lbl">Active inquiries</div>
    </div>
    <?php endif; ?>
    <?php if (in_array($user_type, ['builder', 'realtor'])): ?>
    <div class="stat-card">
        <div class="stat-num"><?= $stats['live'] ?></div>
        <div class="stat-lbl">Live listings</div>
    </div>
    <?php endif; ?>
    <?php if ($user_type === 'broker'): ?>
    <div class="stat-card">
        <div class="stat-num"><?= count($reports) ?></div>
        <div class="stat-lbl">Reports received</div>
    </div>
    <?php endif; ?>
</div>

<!-- Quick actions -->
<div class="actions-row">
    <a href="/plex-map/" class="btn-primary-full"><i class="fas fa-map-marked-alt"></i>Open W.I.N Map</a>
    <?php if (in_array($user_type, ['builder', 'realtor'])): ?>
    <a href="submit-property.php" class="btn-outline-full"><i class="fas fa-plus"></i>Submit Listing</a>
    <?php endif; ?>
    <a href="developer-profile.php" class="btn-outline-full"><i class="fas fa-address-card"></i>Edit Profile</a>
</div>

<!-- Recent saved lots preview -->
<?php if (!empty($saved_lots)): ?>
<div class="section">
    <div class="section-head">
        <h2>📍 Recently Saved Lots</h2>
        <a href="?tab=planner">View all →</a>
    </div>
    <div class="lot-grid">
    <?php foreach (array_slice($saved_lots, 0, 3) as $lot):
        [$tl, $tc] = get_tier($lot['lot_width_m'], $lot['lane_access'], $lot['transit_proximate']);
        $wft = $lot['lot_width_m'] > 0 ? round($lot['lot_width_m'] / 0.3048, 1) . 'ft' : '—';
    ?>
    <div class="lot-card" style="--tier-c:<?= $tc ?>">
        <div class="lot-addr"><?= htmlspecialchars($lot['address']) ?></div>
        <div class="lot-pills">
            <span class="pill" style="background:<?= $tc ?>;color:#fff;"><?= $tl ?></span>
            <span class="pill" style="background:#f1f5f9;color:#475569;"><?= $wft ?></span>
        </div>
        <div class="lot-actions">
            <a class="btn-s btn-view" href="/plex-map/?pid=<?= urlencode($lot['pid']) ?>"><i class="fas fa-map-pin"></i>Map</a>
            <a class="btn-s btn-report" href="/generate-report.php?pid=<?= urlencode($lot['pid']) ?>" target="_blank"><i class="fas fa-file-pdf"></i>Report</a>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Neighbourhood trends -->
<?php if (!empty($trends)): ?>
<div class="section">
    <div class="section-head"><h2>📊 Market Trends</h2></div>
    <div class="trends-grid">
    <?php foreach ($trends as $t): ?>
    <div class="trend-card">
        <div class="trend-nb"><?= htmlspecialchars(str_replace('-', ' ', $t['neighbourhood_slug'])) ?></div>
        <div class="trend-psf">$<?= number_format($t['avg_sold_psf_duplex']) ?><span style="font-size:13px;font-weight:400;color:#888;">/sqft</span></div>
        <div class="trend-label"><?= $t['data_month'] ? date('M Y', strtotime($t['data_month'])) : '' ?> · Duplex</div>
    </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Profile summary -->
<div class="section">
    <div class="section-head"><h2>👤 Your Profile</h2><a href="developer-profile.php">Edit →</a></div>
    <div class="profile-card">
        <div class="profile-row"><span class="key">Name</span><span class="val"><?= htmlspecialchars($dev['full_name']) ?></span></div>
        <div class="profile-row"><span class="key">Company</span><span class="val"><?= htmlspecialchars($dev['company_name'] ?? '—') ?></span></div>
        <div class="profile-row"><span class="key">Email</span><span class="val"><?= htmlspecialchars($dev['email']) ?></span></div>
        <div class="profile-row"><span class="key">Phone</span><span class="val"><?= htmlspecialchars($dev['phone'] ?? '—') ?></span></div>
        <div class="profile-row"><span class="key">Role</span><span class="val"><?= $rc['label'] ?></span></div>
    </div>
</div>

<?php // ─────────────────────────────────────────────────────
      // TAB: PROJECT PLANNER / CLIENT PLANNER / DEAL TRACKER
      // ─────────────────────────────────────────────────────
elseif ($active_tab === 'planner'): ?>

<div class="page-header">
    <div>
        <div class="page-title">
            <?php
            if ($user_type === 'realtor')    echo 'Client Planner';
            elseif ($user_type === 'broker') echo 'Deal Tracker';
            else                             echo 'Project Planner';
            ?>
        </div>
        <div class="page-sub">
            <?php
            if ($user_type === 'realtor')    echo 'Lots saved for clients — generate and send PDF reports';
            elseif ($user_type === 'broker') echo 'Active deals in underwriting — NOI and debt coverage data';
            elseif ($user_type === 'investor') echo 'Track lots by ROI, cap rate, and rental NOI';
            else                             echo 'Track saved lots, generate reports, manage acquisitions';
            ?>
        </div>
    </div>
    <a href="/plex-map/" class="btn-map"><i class="fas fa-map-marked-alt"></i>Open Map</a>
</div>

<?php if (empty($saved_lots)): ?>
<div class="empty-state">
    <i class="fas fa-map-marker-alt"></i>
    <h3>No lots saved yet</h3>
    <p>Open the map, click any lot, and hit <strong>Save Lot</strong> to start tracking.</p>
    <a href="/plex-map/" class="btn-primary-full"><i class="fas fa-map-marked-alt"></i>Open W.I.N Map</a>
</div>
<?php else: ?>
<div class="lot-grid">
<?php foreach ($saved_lots as $lot):
    [$tl, $tc] = get_tier($lot['lot_width_m'], $lot['lane_access'], $lot['transit_proximate']);
    $wft  = $lot['lot_width_m'] > 0 ? round($lot['lot_width_m'] / 0.3048, 1) . 'ft' : '—';
    $asqft = $lot['lot_area_sqm'] > 0 ? number_format($lot['lot_area_sqm'] * 10.7639) . ' sqft' : '—';
    $flags = [];
    if ($lot['heritage_category'] && $lot['heritage_category'] !== 'none') {
        $flags[] = ['Heritage ' . $lot['heritage_category'], in_array($lot['heritage_category'],['A','B'])];
    }
    if ($lot['peat_zone']) $flags[] = ['Peat Zone', true];
    if (!$lot['lane_access']) $flags[] = ['No Lane', false];
?>
<div class="lot-card" style="--tier-c:<?= $tc ?>">
    <div class="lot-addr"><?= htmlspecialchars($lot['address']) ?></div>
    <div class="lot-pills">
        <span class="pill" style="background:<?= $tc ?>;color:#fff;"><?= $tl ?></span>
        <span class="pill" style="background:#f1f5f9;color:#475569;"><?= $wft ?></span>
        <span class="pill" style="background:#f1f5f9;color:#475569;"><?= $asqft ?></span>
    </div>
    <?php if ($flags): ?>
    <div class="lot-flags">
        <?php foreach ($flags as [$fl, $isRed]): ?>
        <span class="flag <?= $isRed ? 'red' : '' ?>"><?= $fl ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($lot['notes']): ?>
    <div class="lot-notes">📝 <?= htmlspecialchars($lot['notes']) ?></div>
    <?php endif; ?>
    <div class="lot-actions">
        <a class="btn-s btn-view" href="/plex-map/?pid=<?= urlencode($lot['pid']) ?>"><i class="fas fa-map-pin"></i>Map</a>
        <a class="btn-s btn-report" href="/generate-report.php?pid=<?= urlencode($lot['pid']) ?>" target="_blank"><i class="fas fa-file-pdf"></i>Report</a>
        <?php if (in_array($user_type, ['builder', 'investor'])): ?>
        <button class="btn-s btn-inquire" onclick="inquireLot('<?= htmlspecialchars($lot['pid']) ?>', '<?= htmlspecialchars(addslashes($lot['address'])) ?>')"><i class="fas fa-handshake"></i>Inquire</button>
        <?php endif; ?>
        <button class="btn-s btn-remove" onclick="removeLot('<?= htmlspecialchars($lot['pid']) ?>', this)"><i class="fas fa-times"></i>Remove</button>
    </div>
    <div class="lot-date">Saved <?= date('M j, Y', strtotime($lot['saved_at'])) ?></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php // ─────────────────────────────────────────────────────
      // TAB: MY LISTINGS (builder / realtor)
      // ─────────────────────────────────────────────────────
elseif ($active_tab === 'listings' && in_array($user_type, ['builder', 'realtor'])): ?>

<div class="page-header">
    <div>
        <div class="page-title"><?= $user_type === 'realtor' ? 'Client Files' : 'My Listings' ?></div>
        <div class="page-sub"><?= $stats['total'] ?> total — <?= $stats['live'] ?> live</div>
    </div>
    <a href="submit-property.php" class="btn-map"><i class="fas fa-plus"></i>Submit New</a>
</div>

<?php if (empty($my_listings)): ?>
<div class="empty-state">
    <i class="fas fa-building"></i>
    <h3>No listings yet</h3>
    <p>Submit your first pre-sale development to get started.</p>
    <a href="submit-property.php" class="btn-primary-full"><i class="fas fa-plus"></i>Submit a Listing</a>
</div>
<?php else: ?>
<table class="data-table">
    <thead>
        <tr>
            <th></th><th>Address</th><th>Type</th><th>Price</th><th>Status</th><th>Tier</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($my_listings as $l):
        $st = $l['submit_status'] ?? 'draft';
        if ($st === 'pending_review')               { $label = 'Pending'; $cls = 'pending'; }
        elseif (in_array($st, ['approved','live'])) { $label = 'Live';    $cls = 'live'; }
        elseif ($st === 'rejected')                 { $label = 'Rejected';$cls = 'rejected'; }
        else                                        { $label = 'Draft';   $cls = 'draft'; }
    ?>
    <tr>
        <td>
            <?php if (!empty($l['img1'])): ?>
            <img src="<?= htmlspecialchars($l['img1']) ?>" class="thumb" alt="">
            <?php else: ?>
            <div class="thumb-placeholder"><i class="fas fa-image"></i></div>
            <?php endif; ?>
        </td>
        <td style="font-weight:600;color:var(--navy);"><?= htmlspecialchars($l['address']) ?></td>
        <td style="color:#666;"><?= htmlspecialchars($l['property_type'] ?? '—') ?></td>
        <td style="color:#666;"><?= htmlspecialchars($l['price'] ?? '—') ?></td>
        <td><span class="db-badge <?= $cls ?>"><?= $label ?></span></td>
        <td><span class="db-badge <?= $l['is_paid'] ? 'concierge' : 'standard' ?>"><?= $l['is_paid'] ? 'Concierge' : 'Standard' ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php // ─────────────────────────────────────────────────────
      // TAB: ACQUISITIONS (builder / investor)
      // ─────────────────────────────────────────────────────
elseif ($active_tab === 'acquisitions' && in_array($user_type, ['builder', 'investor'])): ?>

<div class="page-header">
    <div>
        <div class="page-title"><?= $user_type === 'investor' ? 'Lot Inquiries' : 'Acquisition Tracker' ?></div>
        <div class="page-sub">Track your lot inquiries through the acquisition pipeline</div>
    </div>
</div>

<?php if (empty($acquisitions)): ?>
<div class="empty-state">
    <i class="fas fa-handshake"></i>
    <h3>No inquiries yet</h3>
    <p>Open the map, click a lot, and hit <strong>Inquire for Acquisition</strong>.</p>
    <a href="/plex-map/" class="btn-primary-full"><i class="fas fa-map-marked-alt"></i>Open Map</a>
</div>
<?php else: ?>
<table class="data-table">
    <thead>
        <tr><th>Property</th><th>Status</th><th>Submitted</th><th>Last Update</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($acquisitions as $acq):
        $sl = $status_labels[$acq['status']] ?? ['Unknown', '#94a3b8', '#f8fafc', '#475569'];
    ?>
    <tr>
        <td>
            <strong style="color:var(--navy);"><?= htmlspecialchars($acq['address']) ?></strong><br>
            <span style="font-size:11px;color:#aaa;">PID: <?= htmlspecialchars($acq['pid']) ?></span>
        </td>
        <td>
            <span class="acq-badge" style="background:<?= $sl[2] ?>;color:<?= $sl[3] ?>;">
                <span class="acq-dot" style="background:<?= $sl[1] ?>;"></span>
                <?= $sl[0] ?>
            </span>
        </td>
        <td style="color:#666;font-size:12px;"><?= date('M j, Y', strtotime($acq['requested_at'])) ?></td>
        <td style="color:#666;font-size:12px;"><?= date('M j, Y', strtotime($acq['updated_at'])) ?></td>
        <td><a class="btn-s btn-view" href="/plex-map/?pid=<?= urlencode($acq['pid']) ?>"><i class="fas fa-map-pin"></i>Map</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php // ─────────────────────────────────────────────────────
      // TAB: PDF REPORTS
      // ─────────────────────────────────────────────────────
elseif ($active_tab === 'reports'): ?>

<div class="page-header">
    <div>
        <div class="page-title">PDF Reports</div>
        <div class="page-sub"><?= count($reports) ?> reports generated — click any to regenerate</div>
    </div>
</div>

<?php if (empty($reports)): ?>
<div class="empty-state">
    <i class="fas fa-file-pdf"></i>
    <h3>No reports yet</h3>
    <p>Open the map, click a lot, and hit <strong>Generate PDF Report</strong>.</p>
    <a href="/plex-map/" class="btn-primary-full"><i class="fas fa-map-marked-alt"></i>Open Map</a>
</div>
<?php else: ?>
<table class="data-table">
    <thead>
        <tr><th>Property</th><th>Report ID</th><th>Generated</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($reports as $r): ?>
    <tr>
        <td><strong style="color:var(--navy);"><?= htmlspecialchars($r['address'] ?? $r['pid']) ?></strong></td>
        <td style="font-size:11px;color:#aaa;"><?= htmlspecialchars($r['report_id'] ?? '—') ?></td>
        <td style="color:#666;font-size:12px;"><?= date('M j, Y g:i A', strtotime($r['generated_at'])) ?></td>
        <td>
            <a class="btn-s btn-report" href="/generate-report.php?pid=<?= urlencode($r['pid']) ?>" target="_blank">
                <i class="fas fa-redo"></i>Regenerate
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php endif; // end tab switch ?>

</div><!-- /.main-inner -->
</div><!-- /.main -->
</div><!-- /.layout -->

<div id="toast"></div>

<script>
function showToast(msg, ms = 3500) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), ms);
}

function removeLot(pid, btn) {
    if (!confirm('Remove this lot from your saved list?')) return;
    fetch('/api/save_lot.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ pid, action: 'unsave' })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const card = btn.closest('.lot-card');
            card.style.transition = 'opacity 0.3s';
            card.style.opacity = '0';
            setTimeout(() => { card.remove(); showToast('Lot removed'); }, 300);
        }
    });
}

function inquireLot(pid, address) {
    const msg = prompt('Add a message to Wynston (optional):\n\n' + address, '');
    if (msg === null) return;
    fetch('/api/inquire.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ pid, message: msg })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast(d.already_exists
                ? 'You already have an open inquiry on this lot.'
                : '✅ Inquiry submitted — Wynston will contact you within 4 hours.');
            if (!d.already_exists) setTimeout(() => location.href = '?tab=acquisitions', 2500);
        } else {
            showToast('Error — please try again');
        }
    });
}

// Save lot from map (callable from plex-map/index.php via postMessage if needed)
function saveLot(pid) {
    fetch('/api/save_lot.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ pid, action: 'toggle' })
    })
    .then(r => r.json())
    .then(d => showToast(d.saved ? '💾 Lot saved to Project Planner' : 'Lot removed from saved list'));
}
</script>
</body>
</html>
