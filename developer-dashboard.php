<?php
// ============================================================
//  developer-dashboard.php  —  Developer Portal Dashboard
// ============================================================
require_once __DIR__ . '/dev-auth.php';
dev_require_login('log-in.php');

$dev = dev_current();

// ── Stats ─────────────────────────────────────────────────────
$stats = ['total' => 0, 'pending' => 0, 'live' => 0, 'drafts' => 0];
$my_listings = [];
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

$first_name = explode(' ', trim($dev['full_name']))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="shortcut icon" href="/assets/img/favicon.png">
    <title>Dashboard — <?= htmlspecialchars($dev['company_name'] ?? 'Developer') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --dark:#0d0d1a; --navy:#002446; --gold:#c9a84c; --cream:#f9f6f0; --bdr:#e8e4dd; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--cream); margin: 0; }

        /* Top bar */
        .db-topbar { background: var(--dark); padding: 12px 32px; display: flex; align-items: center; justify-content: space-between; }
        .db-topbar img { height: 34px; }
        .db-topbar-right { display: flex; align-items: center; gap: 20px; font-size: 13px; color: #aaa; }
        .db-topbar-right a { color: #aaa; text-decoration: none; transition: color .2s; }
        .db-topbar-right a:hover { color: #fff; }
        .db-dev-badge { background: rgba(201,168,76,.15); border: 1px solid rgba(201,168,76,.3); color: var(--gold); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }

        /* Layout */
        .db-layout { display: grid; grid-template-columns: 240px 1fr; min-height: calc(100vh - 58px); }

        /* Sidebar */
        .db-sidebar { background: var(--navy); padding: 28px 0; display: flex; flex-direction: column; }
        .db-avatar { padding: 0 20px 24px; border-bottom: 1px solid rgba(255,255,255,.1); margin-bottom: 8px; }
        .db-avatar-circle { width: 52px; height: 52px; border-radius: 50%; background: rgba(201,168,76,.2); border: 2px solid rgba(201,168,76,.4); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; color: var(--gold); margin-bottom: 10px; overflow: hidden; }
        .db-avatar h4 { font-size: 14px; font-weight: 700; color: #fff; margin: 0 0 2px; }
        .db-avatar span { font-size: 12px; color: rgba(255,255,255,.4); }
        .db-sidebar-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.25); padding: 16px 20px 6px; }
        .db-nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: rgba(255,255,255,.5); text-decoration: none; font-size: 13px; transition: all .15s; border-left: 3px solid transparent; }
        .db-nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
        .db-nav-item.active { background: rgba(255,255,255,.1); color: #fff; border-left-color: var(--gold); }
        .db-nav-item i { width: 16px; text-align: center; font-size: 13px; }

        /* Main */
        .db-main { padding: 36px 40px; overflow-y: auto; }
        .db-welcome { margin-bottom: 32px; }
        .db-welcome h1 { font-size: 26px; font-weight: 800; color: var(--dark); margin: 0 0 4px; }
        .db-welcome p { font-size: 14px; color: #888; margin: 0; }

        /* Status banner */
        .db-status-banner { border-radius: 10px; padding: 14px 20px; margin-bottom: 28px; display: flex; align-items: center; gap: 14px; font-size: 14px; }
        .db-status-banner.pending { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
        .db-status-banner.approved { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .db-status-banner.suspended { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .db-status-banner i { font-size: 20px; flex-shrink: 0; }

        /* Stat cards */
        .db-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px; }
        .db-stat-card { background: #fff; border-radius: 10px; padding: 20px; border: 1px solid var(--bdr); }
        .db-stat-card .num { font-size: 32px; font-weight: 800; color: var(--dark); line-height: 1; margin-bottom: 4px; }
        .db-stat-card .lbl { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: .6px; font-weight: 600; }
        .db-stat-card .icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; font-size: 15px; }

        /* Quick actions */
        .db-actions { display: flex; gap: 12px; margin-bottom: 32px; flex-wrap: wrap; }
        .db-action-btn { display: flex; align-items: center; gap: 8px; padding: 11px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; transition: all .2s; }
        .db-action-btn.primary { background: var(--navy); color: #fff; }
        .db-action-btn.primary:hover { background: #003a7a; color: #fff; }
        .db-action-btn.outline { background: #fff; color: var(--navy); border: 1.5px solid var(--navy); }
        .db-action-btn.outline:hover { background: var(--navy); color: #fff; }

        /* Listings table */
        .db-section-title { font-size: 16px; font-weight: 800; color: var(--dark); margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; }
        .db-section-title a { font-size: 13px; font-weight: 600; color: var(--navy); text-decoration: none; }
        .db-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; border: 1px solid var(--bdr); font-size: 13px; }
        .db-table th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: #888; padding: 12px 16px; border-bottom: 1px solid var(--bdr); background: #fafaf8; text-align: left; }
        .db-table td { padding: 14px 16px; border-bottom: 1px solid var(--bdr); vertical-align: middle; }
        .db-table tr:last-child td { border: 0; }
        .db-table tr:hover td { background: #fafaf8; }
        .db-table .thumb { width: 48px; height: 36px; border-radius: 4px; object-fit: cover; background: #eee; }
        .db-table .thumb-placeholder { width: 48px; height: 36px; border-radius: 4px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #ccc; font-size: 16px; }

        /* Badges */
        .db-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; }
        .db-badge.pending  { background: #fef3c7; color: #b45309; }
        .db-badge.live     { background: #dcfce7; color: #16a34a; }
        .db-badge.draft    { background: #f3f4f6; color: #6b7280; }
        .db-badge.rejected { background: #fee2e2; color: #dc2626; }
        .db-badge.concierge { background: #dbeafe; color: #1d4ed8; }
        .db-badge.standard  { background: #f3f4f6; color: #6b7280; }

        /* Empty state */
        .db-empty { background: #fff; border-radius: 10px; border: 1px solid var(--bdr); padding: 48px; text-align: center; }
        .db-empty i { font-size: 40px; color: #ddd; display: block; margin-bottom: 16px; }
        .db-empty h3 { font-size: 16px; font-weight: 700; color: #333; margin-bottom: 8px; }
        .db-empty p { font-size: 14px; color: #888; margin-bottom: 20px; }

        /* Profile card */
        .db-profile-card { background: #fff; border-radius: 10px; border: 1px solid var(--bdr); padding: 24px; margin-bottom: 24px; }
        .db-profile-card h3 { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #aaa; margin-bottom: 16px; }
        .db-profile-row { display: flex; gap: 8px; margin-bottom: 10px; font-size: 13px; }
        .db-profile-row .key { color: #888; width: 120px; flex-shrink: 0; }
        .db-profile-row .val { color: var(--dark); font-weight: 600; }

        @media (max-width: 900px) {
            .db-layout { grid-template-columns: 1fr; }
            .db-sidebar { display: none; }
            .db-main { padding: 20px 16px; }
            .db-stats { grid-template-columns: repeat(2,1fr); }
        }
    </style>
</head>
<body>

<!-- Top bar -->
<div class="db-topbar">
    <a href="index.php">
        <img src="/assets/img/logo-light.png" alt="Wynnston"
            onerror="this.style.display='none';this.nextSibling.style.display='block'">
        <span style="display:none;color:#fff;font-weight:800;font-size:18px;">WYNNSTON</span>
    </a>
    <div class="db-topbar-right">
        <span class="db-dev-badge"><i class="fas fa-building me-1"></i><?= htmlspecialchars($dev['company_name'] ?? 'Developer') ?></span>
        <a href="index.php"><i class="fas fa-globe me-1"></i>View Site</a>
        <a href="dev-logout.php"><i class="fas fa-power-off me-1"></i>Log Out</a>
    </div>
</div>

<div class="db-layout">

    <!-- Sidebar -->
    <div class="db-sidebar">
        <div class="db-avatar">
            <div class="db-avatar-circle">
                <?php if (!empty($dev['logo_path'])): ?>
                    <img src="<?= htmlspecialchars($dev['logo_path']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                    <?= strtoupper(substr($dev['full_name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <h4><?= htmlspecialchars($dev['full_name']) ?></h4>
            <span><?= htmlspecialchars($dev['company_name'] ?? '') ?></span>
        </div>
        <a href="developer-dashboard.php" class="db-nav-item active"><i class="fas fa-gauge"></i>Dashboard</a>
        <a href="submit-property.php" class="db-nav-item"><i class="fas fa-plus-circle"></i>Submit Property</a>
        <a href="developer-dashboard.php#my-listings" class="db-nav-item"><i class="fas fa-building"></i>My Listings</a>
        <div class="db-sidebar-label">Account</div>
        <a href="developer-profile.php" class="db-nav-item"><i class="fas fa-address-card"></i>My Profile</a>
        <a href="change-password.php" class="db-nav-item"><i class="fas fa-key"></i>Change Password</a>
        <div style="flex:1;"></div>
        <a href="dev-logout.php" class="db-nav-item" style="margin-top:auto;"><i class="fas fa-power-off"></i>Log Out</a>
    </div>

    <!-- Main content -->
    <div class="db-main">

        <!-- Welcome -->
        <div class="db-welcome">
            <h1>Welcome back, <?= htmlspecialchars($first_name) ?> 👋</h1>
            <p><?= date('l, F j, Y') ?> — <?= htmlspecialchars($dev['company_name'] ?? '') ?></p>
        </div>

        <!-- Account status banner -->
        <?php if ($dev['status'] === 'pending'): ?>
        <div class="db-status-banner pending">
            <i class="fas fa-clock"></i>
            <div>
                <strong>Account Pending Approval</strong><br>
                <span style="font-size:13px;">Your account is under review by the Wynnston team. You'll receive an email once approved — usually within 1 business day. You can still fill out your profile and prepare your listings in the meantime.</span>
            </div>
        </div>
        <?php elseif ($dev['status'] === 'suspended'): ?>
        <div class="db-status-banner suspended">
            <i class="fas fa-ban"></i>
            <div>
                <strong>Account Suspended</strong><br>
                <span style="font-size:13px;">Please contact <a href="mailto:support@wynnstonconcierge.com" style="color:inherit;">support@wynnstonconcierge.com</a> for assistance.</span>
            </div>
        </div>
        <?php else: ?>
        <div class="db-status-banner approved">
            <i class="fas fa-circle-check"></i>
            <div><strong>Account Active</strong> — You can submit and manage your listings.</div>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="db-stats">
            <div class="db-stat-card">
                <div class="icon" style="background:#eef2ff;color:#4f46e5;"><i class="fas fa-building"></i></div>
                <div class="num"><?= $stats['total'] ?></div>
                <div class="lbl">Total Listings</div>
            </div>
            <div class="db-stat-card">
                <div class="icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-circle-check"></i></div>
                <div class="num"><?= $stats['live'] ?></div>
                <div class="lbl">Live / Approved</div>
            </div>
            <div class="db-stat-card">
                <div class="icon" style="background:#fef3c7;color:#b45309;"><i class="fas fa-clock"></i></div>
                <div class="num"><?= $stats['pending'] ?></div>
                <div class="lbl">Pending Review</div>
            </div>
            <div class="db-stat-card">
                <div class="icon" style="background:#f3f4f6;color:#6b7280;"><i class="fas fa-file-pen"></i></div>
                <div class="num"><?= $stats['drafts'] ?></div>
                <div class="lbl">Drafts</div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="db-actions">
            <a href="submit-property.php" class="db-action-btn primary">
                <i class="fas fa-plus"></i>Submit New Listing
            </a>
            <a href="developer-profile.php" class="db-action-btn outline">
                <i class="fas fa-address-card"></i>Edit Profile
            </a>
            <a href="index.php" target="_blank" class="db-action-btn outline">
                <i class="fas fa-globe"></i>View Site
            </a>
        </div>

        <!-- My Listings -->
        <div id="my-listings">
            <div class="db-section-title">
                My Listings
                <a href="submit-property.php">+ Submit New →</a>
            </div>

            <?php if (empty($my_listings)): ?>
            <div class="db-empty">
                <i class="fas fa-building"></i>
                <h3>No listings yet</h3>
                <p>Submit your first pre-sale development to get started.</p>
                <a href="submit-property.php" class="db-action-btn primary" style="display:inline-flex;">
                    <i class="fas fa-plus me-2"></i>Submit a Listing
                </a>
            </div>
            <?php else: ?>
            <table class="db-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Address</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Tier</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_listings as $l): ?>
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
                        <td>
                            <?php
                            $st = $l['submit_status'] ?? 'draft';
                            if ($st === 'pending_review')               { $label = 'Pending Review'; $cls = 'pending'; }
                            elseif ($st === 'approved' || $st === 'live') { $label = 'Live'; $cls = 'live'; }
                            elseif ($st === 'rejected')                  { $label = 'Rejected'; $cls = 'rejected'; }
                            else                                         { $label = 'Draft'; $cls = 'draft'; }
                            ?>
                            <span class="db-badge <?= $cls ?>"><?= $label ?></span>
                        </td>
                        <td>
                            <span class="db-badge <?= $l['is_paid'] ? 'concierge' : 'standard' ?>">
                                <?= $l['is_paid'] ? 'Concierge' : 'Standard' ?>
                            </span>
                        </td>
                        <td style="color:#aaa;font-size:12px;">—</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Profile summary -->
        <div class="db-profile-card" style="margin-top:32px;">
            <h3>Your Profile</h3>
            <div class="db-profile-row"><span class="key">Name</span><span class="val"><?= htmlspecialchars($dev['full_name']) ?></span></div>
            <div class="db-profile-row"><span class="key">Company</span><span class="val"><?= htmlspecialchars($dev['company_name'] ?? '—') ?></span></div>
            <div class="db-profile-row"><span class="key">Email</span><span class="val"><?= htmlspecialchars($dev['email']) ?></span></div>
            <div class="db-profile-row"><span class="key">Phone</span><span class="val"><?= htmlspecialchars($dev['phone'] ?? '—') ?></span></div>
            <div class="db-profile-row"><span class="key">Website</span><span class="val"><?= $dev['website'] ? '<a href="'.htmlspecialchars($dev['website']).'" target="_blank" style="color:var(--navy);">'.htmlspecialchars($dev['website']).'</a>' : '—' ?></span></div>
            <div style="margin-top:16px;">
                <a href="developer-profile.php" class="db-action-btn outline" style="display:inline-flex;font-size:12px;padding:8px 16px;">
                    <i class="fas fa-pen me-1"></i>Edit Profile
                </a>
            </div>
        </div>

    </div>
</div>

</body>
</html>