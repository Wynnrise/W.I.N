<?php
/**
 * admin_members_tab.php
 * Included by admin.php when $active_tab === 'developers'
 * Full member CRM: list, search, detail view, grant reports, notes
 */

// Add columns if needed (safe to run repeatedly)
$_dcols = $pdo->query("DESCRIBE developers")->fetchAll(PDO::FETCH_COLUMN);
foreach ([
    'daily_report_limit' => "ALTER TABLE developers ADD COLUMN daily_report_limit INT DEFAULT 5",
    'bonus_reports'      => "ALTER TABLE developers ADD COLUMN bonus_reports INT DEFAULT 0",
    'admin_notes'        => "ALTER TABLE developers ADD COLUMN admin_notes TEXT DEFAULT NULL",
    'payment_notes'      => "ALTER TABLE developers ADD COLUMN payment_notes TEXT DEFAULT NULL",
    'subscription_tier'  => "ALTER TABLE developers ADD COLUMN subscription_tier ENUM('free','pro','white_label') DEFAULT 'free'",
    'report_logo_path'   => "ALTER TABLE developers ADD COLUMN report_logo_path VARCHAR(500) DEFAULT NULL",
    'report_bio'         => "ALTER TABLE developers ADD COLUMN report_bio TEXT DEFAULT NULL",
    'report_title'       => "ALTER TABLE developers ADD COLUMN report_title VARCHAR(100) DEFAULT NULL",
] as $_col => $_sql) {
    if (!in_array($_col, $_dcols)) { try { $pdo->exec($_sql); } catch(Exception $e) {} }
}

// ── Action handlers ────────────────────────────────────────────────────────
if (isset($_POST['dev_grant_reports'])) {
    $did   = (int)$_POST['dev_id_grant'];
    $bonus = max(0, min(50, (int)($_POST['bonus_amount'] ?? 5)));
    $pdo->prepare("UPDATE developers SET bonus_reports = bonus_reports + ? WHERE id = ?")->execute([$bonus, $did]);
    if (!empty($_POST['clear_today'])) {
        $pdo->prepare("DELETE FROM pdf_log WHERE developer_id = ? AND DATE(generated_at) = CURDATE()")->execute([$did]);
    }
    echo "<script>window.location='admin.php?tab=developers&dev_view={$did}&msg=" . urlencode("✅ Granted {$bonus} bonus reports.") . "';</script>"; exit;
}
if (isset($_POST['dev_save_notes'])) {
    $did   = (int)$_POST['dev_id_notes'];
    $admin = trim($_POST['admin_notes'] ?? '');
    $pay   = trim($_POST['payment_notes'] ?? '');
    $tier  = in_array($_POST['subscription_tier'] ?? '', ['free','pro','white_label']) ? $_POST['subscription_tier'] : 'free';
    $limit = max(1, min(100, (int)($_POST['daily_report_limit'] ?? 5)));
    $pdo->prepare("UPDATE developers SET admin_notes=?, payment_notes=?, subscription_tier=?, daily_report_limit=? WHERE id=?")
        ->execute([$admin, $pay, $tier, $limit, $did]);
    echo "<script>window.location='admin.php?tab=developers&dev_view={$did}&msg=" . urlencode("✅ Member notes saved.") . "';</script>"; exit;
}

// ── Load members ───────────────────────────────────────────────────────────
$dev_search = trim($_GET['dev_search'] ?? '');
$dev_view   = (int)($_GET['dev_view'] ?? 0);

$dev_q = "SELECT d.*,
    (SELECT COUNT(*) FROM pdf_log pl WHERE pl.developer_id=d.id) as report_count,
    (SELECT COUNT(*) FROM pdf_log pl WHERE pl.developer_id=d.id AND DATE(pl.generated_at)=CURDATE()) as reports_today,
    (SELECT COUNT(*) FROM saved_lots sl WHERE sl.developer_id=d.id) as saved_lots_count,
    (SELECT COUNT(*) FROM acquisition_requests ar WHERE ar.developer_id=d.id) as inquiry_count
FROM developers d";
$dev_params = [];
if ($dev_search) {
    $dev_q .= " WHERE d.full_name LIKE ? OR d.email LIKE ? OR d.company_name LIKE ?";
    $dev_params = ["%$dev_search%", "%$dev_search%", "%$dev_search%"];
}
$dev_q .= " ORDER BY d.created_at DESC";
$dev_stmt = $pdo->prepare($dev_q);
$dev_stmt->execute($dev_params);
$developers = $dev_stmt->fetchAll(PDO::FETCH_ASSOC);
$pending_devs = count(array_filter($developers, fn($d) => $d['status'] === 'pending'));
?>

<?php if ($dev_view > 0):
    // Find member
    $dv = null;
    foreach ($developers as $d) { if ((int)$d['id'] === $dev_view) { $dv = $d; break; } }
    if (!$dv) $dv = $pdo->query("SELECT d.*,
        (SELECT COUNT(*) FROM pdf_log pl WHERE pl.developer_id=d.id) as report_count,
        (SELECT COUNT(*) FROM pdf_log pl WHERE pl.developer_id=d.id AND DATE(pl.generated_at)=CURDATE()) as reports_today,
        (SELECT COUNT(*) FROM saved_lots sl WHERE sl.developer_id=d.id) as saved_lots_count,
        (SELECT COUNT(*) FROM acquisition_requests ar WHERE ar.developer_id=d.id) as inquiry_count
        FROM developers d WHERE d.id={$dev_view}")->fetch(PDO::FETCH_ASSOC);

    if (!$dv) { echo '<div style="padding:40px;color:#dc2626;">Member not found.</div>'; return; }

    $mem_reports = $pdo->prepare("SELECT pid,address,report_id,generated_at FROM pdf_log WHERE developer_id=? ORDER BY generated_at DESC LIMIT 20");
    $mem_reports->execute([$dev_view]);
    $mem_reports = $mem_reports->fetchAll(PDO::FETCH_ASSOC);

    $mem_saved = $pdo->prepare("SELECT sl.pid,sl.address,sl.saved_at,p.lot_width_m,p.neighbourhood_slug FROM saved_lots sl LEFT JOIN plex_properties p ON p.pid=sl.pid WHERE sl.developer_id=? ORDER BY sl.saved_at DESC LIMIT 20");
    $mem_saved->execute([$dev_view]);
    $mem_saved = $mem_saved->fetchAll(PDO::FETCH_ASSOC);

    $mem_inquiries = $pdo->prepare("SELECT pid,address,status,requested_at,updated_at FROM acquisition_requests WHERE developer_id=? ORDER BY updated_at DESC");
    $mem_inquiries->execute([$dev_view]);
    $mem_inquiries = $mem_inquiries->fetchAll(PDO::FETCH_ASSOC);

    $reports_today = (int)($dv['reports_today'] ?? 0);
    $daily_limit   = (int)($dv['daily_report_limit'] ?? 5);
    $bonus         = (int)($dv['bonus_reports'] ?? 0);
    $eff_limit     = $daily_limit + $bonus;
    $st            = $dv['status'] ?? 'pending';
    $st_color      = $st === 'approved' ? '#16a34a' : ($st === 'suspended' ? '#dc2626' : '#b45309');
?>
<div style="flex:1;padding:28px;max-width:1100px;">
    <?php if (!empty($message)): ?><div class="admin-message"><?= $message ?></div><?php endif; ?>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="admin.php?tab=developers" style="color:#666;text-decoration:none;font-size:13px;">← All Members</a>
        <span style="color:#ddd;">/</span>
        <strong style="font-size:14px;color:#002446;"><?= htmlspecialchars($dv['full_name']) ?></strong>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        <!-- Member info -->
        <div style="background:#fff;border-radius:10px;padding:20px;border:1px solid #e2e8f0;">
            <div style="font-size:13px;font-weight:800;color:#002446;border-bottom:2px solid #c9a84c;padding-bottom:8px;margin-bottom:14px;">Member Information</div>
            <div style="display:grid;grid-template-columns:130px 1fr;gap:7px;font-size:13px;">
                <span style="color:#888;">Name</span><strong><?= htmlspecialchars($dv['full_name']) ?></strong>
                <span style="color:#888;">Email</span><a href="mailto:<?= htmlspecialchars($dv['email']) ?>" style="color:#002446;"><?= htmlspecialchars($dv['email']) ?></a>
                <span style="color:#888;">Phone</span><span><?= htmlspecialchars($dv['phone'] ?? '—') ?></span>
                <span style="color:#888;">Company</span><span><?= htmlspecialchars($dv['company_name'] ?? '—') ?></span>
                <span style="color:#888;">Role</span><span style="text-transform:capitalize;"><?= htmlspecialchars($dv['user_type'] ?? '—') ?></span>
                <span style="color:#888;">Status</span><strong style="color:<?= $st_color ?>;"><?= ucfirst($st) ?></strong>
                <span style="color:#888;">Joined</span><span><?= date('M j, Y', strtotime($dv['created_at'])) ?></span>
                <span style="color:#888;">Last login</span><span><?= $dv['last_login'] ? date('M j, Y g:ia', strtotime($dv['last_login'])) : 'Never' ?></span>
                <span style="color:#888;">Subscription</span><strong><?= ucfirst($dv['subscription_tier'] ?? 'free') ?></strong>
            </div>
            <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
                <?php if ($st !== 'approved'): ?>
                <a href="admin.php?dev_approve=<?= $dev_view ?>" style="background:#16a34a;color:#fff;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none;" onclick="return confirm('Approve?')">✓ Approve</a>
                <?php endif; ?>
                <?php if ($st !== 'suspended'): ?>
                <a href="admin.php?dev_suspend=<?= $dev_view ?>" style="background:#dc2626;color:#fff;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none;" onclick="return confirm('Suspend?')">⛔ Suspend</a>
                <?php endif; ?>
                <?php if ($st !== 'pending'): ?>
                <a href="admin.php?dev_pending=<?= $dev_view ?>" style="background:#f59e0b;color:#fff;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none;" onclick="return confirm('Reset to pending?')">↺ Pending</a>
                <?php endif; ?>
                <a href="admin.php?dev_delete=<?= $dev_view ?>" style="background:#ef4444;color:#fff;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none;" onclick="return confirm('Permanently delete this account?')">🗑 Delete</a>
            </div>
        </div>

        <!-- Activity + grant -->
        <div style="background:#fff;border-radius:10px;padding:20px;border:1px solid #e2e8f0;">
            <div style="font-size:13px;font-weight:800;color:#002446;border-bottom:2px solid #c9a84c;padding-bottom:8px;margin-bottom:14px;">Activity Summary</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                <div style="background:#f9f6f0;border-radius:8px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;color:#002446;"><?= (int)($dv['report_count'] ?? 0) ?></div>
                    <div style="font-size:11px;color:#888;">Total reports</div>
                </div>
                <div style="background:<?= $reports_today >= $eff_limit ? '#fef2f2' : '#f0fdf4' ?>;border-radius:8px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;color:<?= $reports_today >= $eff_limit ? '#dc2626' : '#16a34a' ?>;"><?= $reports_today ?>/<?= $eff_limit ?></div>
                    <div style="font-size:11px;color:#888;">Today<?= $bonus > 0 ? " (+{$bonus} bonus)" : '' ?></div>
                </div>
                <div style="background:#f9f6f0;border-radius:8px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;color:#002446;"><?= (int)($dv['saved_lots_count'] ?? 0) ?></div>
                    <div style="font-size:11px;color:#888;">Saved lots</div>
                </div>
                <div style="background:#f9f6f0;border-radius:8px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;color:#002446;"><?= (int)($dv['inquiry_count'] ?? 0) ?></div>
                    <div style="font-size:11px;color:#888;">Inquiries</div>
                </div>
            </div>
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px;">
                <div style="font-size:12px;font-weight:700;color:#92400e;margin-bottom:8px;">Grant Extra PDF Reports</div>
                <form method="POST" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input type="hidden" name="dev_id_grant" value="<?= $dev_view ?>">
                    <input type="number" name="bonus_amount" value="5" min="1" max="50" style="width:70px;padding:6px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;">
                    <label style="font-size:12px;color:#555;display:flex;align-items:center;gap:4px;">
                        <input type="checkbox" name="clear_today"> Clear today's count too
                    </label>
                    <button type="submit" name="dev_grant_reports" style="background:#002446;color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;">Grant</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Notes & subscription -->
    <div style="background:#fff;border-radius:10px;padding:20px;border:1px solid #e2e8f0;margin-bottom:20px;">
        <div style="font-size:13px;font-weight:800;color:#002446;border-bottom:2px solid #c9a84c;padding-bottom:8px;margin-bottom:14px;">CRM Notes & Subscription</div>
        <form method="POST">
            <input type="hidden" name="dev_id_notes" value="<?= $dev_view ?>">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:5px;">Admin Notes <span style="color:#aaa;font-weight:400;">(internal only)</span></label>
                    <textarea name="admin_notes" rows="4" style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;resize:vertical;"><?= htmlspecialchars($dv['admin_notes'] ?? '') ?></textarea>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:5px;">Payment Notes</label>
                    <textarea name="payment_notes" rows="4" style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;resize:vertical;"><?= htmlspecialchars($dv['payment_notes'] ?? '') ?></textarea>
                </div>
            </div>
            <div style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:5px;">Subscription Tier</label>
                    <select name="subscription_tier" style="padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;">
                        <option value="free"        <?= ($dv['subscription_tier'] ?? 'free') === 'free'        ? 'selected' : '' ?>>Free</option>
                        <option value="pro"         <?= ($dv['subscription_tier'] ?? 'free') === 'pro'         ? 'selected' : '' ?>>Pro ($99/mo)</option>
                        <option value="white_label" <?= ($dv['subscription_tier'] ?? 'free') === 'white_label' ? 'selected' : '' ?>>White Label</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:5px;">Daily Report Limit</label>
                    <input type="number" name="daily_report_limit" value="<?= $daily_limit ?>" min="1" max="100" style="width:80px;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;">
                </div>
                <button type="submit" name="dev_save_notes" style="background:#002446;color:#fff;border:none;padding:9px 20px;border-radius:6px;font-size:13px;font-weight:700;cursor:pointer;">Save Notes</button>
            </div>
        </form>
    </div>

    <?php if (!empty($mem_reports)): ?>
    <div style="background:#fff;border-radius:10px;padding:20px;border:1px solid #e2e8f0;margin-bottom:20px;">
        <div style="font-size:13px;font-weight:800;color:#002446;border-bottom:2px solid #c9a84c;padding-bottom:8px;margin-bottom:14px;">📄 Report History (last 20)</div>
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead><tr style="background:#f9f6f0;"><th style="padding:8px;text-align:left;">Property</th><th style="padding:8px;text-align:left;">Report ID</th><th style="padding:8px;text-align:left;">Generated</th><th style="padding:8px;"></th></tr></thead>
            <tbody>
            <?php foreach ($mem_reports as $mr): ?>
            <tr style="border-top:1px solid #f1f5f9;">
                <td style="padding:7px 8px;font-weight:600;color:#002446;"><?= htmlspecialchars($mr['address'] ?? $mr['pid']) ?></td>
                <td style="padding:7px 8px;color:#aaa;font-family:monospace;font-size:11px;"><?= htmlspecialchars($mr['report_id'] ?? '—') ?></td>
                <td style="padding:7px 8px;color:#666;"><?= date('M j, Y g:ia', strtotime($mr['generated_at'])) ?></td>
                <td style="padding:7px 8px;"><a href="/generate-report.php?pid=<?= urlencode($mr['pid']) ?>" target="_blank" style="font-size:11px;color:#002446;font-weight:600;">View →</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($mem_saved)): ?>
    <div style="background:#fff;border-radius:10px;padding:20px;border:1px solid #e2e8f0;margin-bottom:20px;">
        <div style="font-size:13px;font-weight:800;color:#002446;border-bottom:2px solid #c9a84c;padding-bottom:8px;margin-bottom:14px;">📍 Saved Lots (last 20)</div>
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead><tr style="background:#f9f6f0;"><th style="padding:8px;text-align:left;">Property</th><th style="padding:8px;text-align:left;">Neighbourhood</th><th style="padding:8px;text-align:left;">Width</th><th style="padding:8px;text-align:left;">Saved</th></tr></thead>
            <tbody>
            <?php foreach ($mem_saved as $ms): ?>
            <tr style="border-top:1px solid #f1f5f9;">
                <td style="padding:7px 8px;font-weight:600;color:#002446;"><?= htmlspecialchars($ms['address'] ?? $ms['pid']) ?></td>
                <td style="padding:7px 8px;color:#666;"><?= htmlspecialchars(str_replace('-', ' ', $ms['neighbourhood_slug'] ?? '—')) ?></td>
                <td style="padding:7px 8px;color:#666;"><?= $ms['lot_width_m'] > 0 ? round($ms['lot_width_m'] / 0.3048, 1) . 'ft' : '—' ?></td>
                <td style="padding:7px 8px;color:#888;"><?= date('M j, Y', strtotime($ms['saved_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($mem_inquiries)): ?>
    <div style="background:#fff;border-radius:10px;padding:20px;border:1px solid #e2e8f0;">
        <div style="font-size:13px;font-weight:800;color:#002446;border-bottom:2px solid #c9a84c;padding-bottom:8px;margin-bottom:14px;">🤝 Acquisition Inquiries</div>
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead><tr style="background:#f9f6f0;"><th style="padding:8px;text-align:left;">Property</th><th style="padding:8px;text-align:left;">Status</th><th style="padding:8px;text-align:left;">Submitted</th><th style="padding:8px;text-align:left;">Updated</th></tr></thead>
            <tbody>
            <?php foreach ($mem_inquiries as $mi):
                $status_colors = ['under_review' => '#f59e0b', 'analysis_sent' => '#0065ff', 'negotiation' => '#22c55e', 'closed' => '#94a3b8'];
                $sc = $status_colors[$mi['status']] ?? '#94a3b8';
            ?>
            <tr style="border-top:1px solid #f1f5f9;">
                <td style="padding:7px 8px;font-weight:600;color:#002446;"><?= htmlspecialchars($mi['address'] ?? $mi['pid']) ?></td>
                <td style="padding:7px 8px;"><span style="color:<?= $sc ?>;font-weight:700;"><?= ucfirst(str_replace('_', ' ', $mi['status'])) ?></span></td>
                <td style="padding:7px 8px;color:#888;"><?= date('M j, Y', strtotime($mi['requested_at'])) ?></td>
                <td style="padding:7px 8px;color:#888;"><?= date('M j, Y', strtotime($mi['updated_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php else: // Member list view ?>
<div style="flex:1;padding:28px;">
    <?php if (!empty($message)): ?><div class="admin-message"><?= $message ?></div><?php endif; ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap;">
        <div>
            <h2 style="font-size:18px;font-weight:800;color:#0d0d1a;margin:0;">Member Accounts</h2>
            <p style="font-size:13px;color:#888;margin:4px 0 0;"><?= count($developers) ?> members — <?= $pending_devs ?> pending</p>
        </div>
        <form method="GET" style="display:flex;gap:8px;">
            <input type="hidden" name="tab" value="developers">
            <input type="text" name="dev_search" value="<?= htmlspecialchars($dev_search) ?>" placeholder="Search name, email, company…" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;width:240px;">
            <button type="submit" style="background:#002446;color:#fff;border:none;padding:8px 14px;border-radius:6px;font-size:13px;cursor:pointer;">Search</button>
            <?php if ($dev_search): ?><a href="admin.php?tab=developers" style="padding:8px 14px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;color:#666;text-decoration:none;">Clear</a><?php endif; ?>
        </form>
    </div>

    <?php if (empty($developers)): ?>
    <div style="background:#fff;border-radius:10px;padding:48px;text-align:center;color:#aaa;border:1px solid #e2e8f0;">
        <i class="fas fa-user-tie" style="font-size:36px;display:block;margin-bottom:12px;"></i>
        No members found<?= $dev_search ? ' matching "' . htmlspecialchars($dev_search) . '"' : '' ?>.
    </div>
    <?php else: ?>
    <table class="listings-table">
        <thead>
            <tr>
                <th>Member</th><th>Company</th><th>Role</th><th>Tier</th>
                <th>Reports</th><th>Saved</th><th>Inquiries</th>
                <th>Joined</th><th>Last Login</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($developers as $dev): ?>
        <?php
            $ut  = $dev['user_type'] ?? 'builder';
            $st  = $dev['status'] ?? 'pending';
            $sc  = $st === 'approved' ? 'background:#dcfce7;color:#16a34a;' : ($st === 'suspended' ? 'background:#fee2e2;color:#dc2626;' : 'background:#fef3c7;color:#b45309;');
            $utc = ['builder' => '#0369a1', 'investor' => '#6d28d9', 'realtor' => '#166534', 'broker' => '#92400e'][$ut] ?? '#475569';
            $eff = (int)($dev['daily_report_limit'] ?? 5) + (int)($dev['bonus_reports'] ?? 0);
            $today_r = (int)($dev['reports_today'] ?? 0);
        ?>
        <tr style="<?= $st === 'pending' ? 'background:#fffbeb;' : '' ?>">
            <td style="font-weight:600;">
                <a href="admin.php?tab=developers&dev_view=<?= $dev['id'] ?>" style="color:#002446;text-decoration:none;"><?= htmlspecialchars($dev['full_name']) ?></a>
                <?php if ($st === 'pending'): ?><span style="display:inline-block;background:#fef3c7;color:#b45309;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;margin-left:4px;">NEW</span><?php endif; ?>
                <div style="font-size:11px;color:#aaa;"><?= htmlspecialchars($dev['email']) ?></div>
            </td>
            <td style="font-size:12px;color:#555;"><?= htmlspecialchars($dev['company_name'] ?? '—') ?></td>
            <td><span style="background:rgba(0,0,0,.05);color:<?= $utc ?>;font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;"><?= ucfirst($ut) ?></span></td>
            <td style="font-size:11px;font-weight:700;color:#002446;"><?= ucfirst($dev['subscription_tier'] ?? 'free') ?></td>
            <td style="text-align:center;">
                <span style="font-weight:700;color:<?= $today_r >= $eff ? '#dc2626' : '#002446' ?>;"><?= $today_r ?>/<?= $eff ?></span>
                <div style="font-size:10px;color:#aaa;"><?= (int)($dev['report_count'] ?? 0) ?> total</div>
            </td>
            <td style="text-align:center;"><?= (int)($dev['saved_lots_count'] ?? 0) ?></td>
            <td style="text-align:center;"><?= (int)($dev['inquiry_count'] ?? 0) ?></td>
            <td style="font-size:11px;color:#888;"><?= date('M j, Y', strtotime($dev['created_at'])) ?></td>
            <td style="font-size:11px;color:#888;"><?= $dev['last_login'] ? date('M j, g:ia', strtotime($dev['last_login'])) : 'Never' ?></td>
            <td><span style="<?= $sc ?>font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;"><?= ucfirst($st) ?></span></td>
            <td style="white-space:nowrap;">
                <a href="admin.php?tab=developers&dev_view=<?= $dev['id'] ?>" class="action-btn" style="background:#002446;color:#fff;"><i class="fas fa-eye"></i> View</a>
                <?php if ($st !== 'approved'): ?><a href="admin.php?dev_approve=<?= $dev['id'] ?>" class="action-btn" style="background:#16a34a;color:#fff;" onclick="return confirm('Approve?')"><i class="fas fa-check"></i></a><?php endif; ?>
                <?php if ($st !== 'suspended'): ?><a href="admin.php?dev_suspend=<?= $dev['id'] ?>" class="action-btn" style="background:#dc2626;color:#fff;" onclick="return confirm('Suspend?')"><i class="fas fa-ban"></i></a><?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php endif; // end dev_view vs list ?>