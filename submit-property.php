<?php
// ============================================================
//  submit-property.php  —  Developer Property Submission
//  Phase 2: Gated behind dev login, writes to multi_2025,
//  files go into address-based upload subfolders
// ============================================================
require_once __DIR__ . '/dev-auth.php';
dev_require_login('log-in.php'); // redirect to login if not authenticated

$dev = dev_current();

// ── User-type gate ────────────────────────────────────────────
// Only builders and realtors can submit listings. Other user types
// (investor / broker / home_owner) see an access-denied message with
// instructions to email for a role change. This keeps the code clean —
// no per-role UI branching, just one early exit.
$_dev_user_type = $dev['user_type'] ?? 'builder';
if (!in_array($_dev_user_type, ['builder', 'realtor'])) {
    $_role_label = ucfirst(str_replace('_', ' ', $_dev_user_type));
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Listing Access — Wynston W.I.N Portal</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f9f6f0; margin: 0; padding: 60px 20px; }
            .gate-card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 40px; border: 1px solid #e8e4dd; text-align: center; }
            .gate-icon { font-size: 48px; color: #c9a84c; margin-bottom: 20px; }
            .gate-title { font-size: 22px; font-weight: 800; color: #002446; margin: 0 0 12px; }
            .gate-sub { font-size: 14px; color: #666; line-height: 1.6; margin: 0 0 24px; }
            .gate-email { display: inline-block; background: #002446; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; }
            .gate-back { display: inline-block; margin-left: 12px; color: #666; text-decoration: none; font-size: 13px; }
            .role-badge { display: inline-block; background: rgba(0,36,70,.08); color: #002446; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px; }
        </style>
    </head>
    <body>
        <div class="gate-card">
            <div class="gate-icon"><i class="fas fa-lock"></i></div>
            <div class="role-badge">Your role: <?= htmlspecialchars($_role_label) ?></div>
            <h1 class="gate-title">Listing submission is for builders &amp; realtors</h1>
            <p class="gate-sub">
                Only accounts registered as <strong>Builder</strong> or <strong>Realtor</strong> can submit property listings on Wynston.
                If you are a builder or licensed realtor, please email us and we'll update your account role.
            </p>
            <a href="mailto:tam@wynston.ca?subject=Role%20update%20request" class="gate-email">
                <i class="fas fa-envelope me-2"></i>Email tam@wynston.ca
            </a>
            <a href="developer-dashboard.php" class="gate-back">← Back to dashboard</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$message = '';
$errors  = [];
$success_id = null;

// ── Auto-add any missing columns ─────────────────────────────
$new_columns = [
    'developer_name'     => "ALTER TABLE multi_2025 ADD COLUMN developer_name VARCHAR(255) DEFAULT '' AFTER builder_logo",
    'developer_bio'      => "ALTER TABLE multi_2025 ADD COLUMN developer_bio TEXT AFTER developer_name",
    'virtual_tour_url'   => "ALTER TABLE multi_2025 ADD COLUMN virtual_tour_url VARCHAR(1024) DEFAULT '' AFTER video_url",
    'community_features' => "ALTER TABLE multi_2025 ADD COLUMN community_features TEXT AFTER features",
    'submitted_by'       => "ALTER TABLE multi_2025 ADD COLUMN submitted_by INT DEFAULT NULL AFTER is_paid",
    'submit_status'      => "ALTER TABLE multi_2025 ADD COLUMN submit_status VARCHAR(20) DEFAULT 'draft' AFTER submitted_by",
    // Session 17: Plex Map permit showcase linkage.
    // When a developer claims/showcases their active permit on the Plex Map,
    // the permit PID is stored here so the map can detect "this permit has
    // a listing" via LEFT JOIN on A_Permit_2026.pid = multi_2025.pid.
    'pid'                => "ALTER TABLE multi_2025 ADD COLUMN pid VARCHAR(20) DEFAULT NULL AFTER submit_status",
];
$existing_cols = $pdo->query("DESCRIBE multi_2025")->fetchAll(PDO::FETCH_COLUMN);
foreach ($new_columns as $col => $sql) {
    if (!in_array($col, $existing_cols)) {
        try { $pdo->exec($sql); $existing_cols[] = $col; } catch (Exception $e) {}
    }
}
// Add index on pid for efficient LEFT JOIN in map queries (safe to re-run).
try { $pdo->exec("ALTER TABLE multi_2025 ADD INDEX idx_pid (pid)"); } catch (Exception $e) {}

// ── Permit showcase pre-fill (Session 17) ─────────────────────
// When the user arrives via ?pid=XXX&source=permit_showcase from the
// Plex Map, pre-fill address / neighbourhood / coordinates from the
// lot and permit data so the builder only has to fill in marketing
// details (photos, description, price, etc.).
$showcase_pid       = trim($_GET['pid'] ?? '');
$showcase_mode      = (($_GET['source'] ?? '') === 'permit_showcase' && $showcase_pid !== '');
$showcase_prefill   = [
    'address'      => '',
    'neighborhood' => '',
    'latitude'     => '',
    'longitude'    => '',
];
if ($showcase_mode) {
    // Block duplicate claims: if a live listing already exists for this
    // PID, redirect straight to the detail page instead of showing the
    // showcase form again. Prevents two builders claiming the same permit.
    try {
        $dup = $pdo->prepare("SELECT id FROM multi_2025 WHERE pid = ? AND img1 IS NOT NULL AND img1 <> '' AND submit_status IN ('approved','live') LIMIT 1");
        $dup->execute([$showcase_pid]);
        $existing_listing_id = $dup->fetchColumn();
        if ($existing_listing_id) {
            header('Location: single-property-2.php?id=' . (int)$existing_listing_id);
            exit;
        }
    } catch (Exception $e) { /* fall through — don't block submission if check fails */ }

    // Pull lot info from plex_properties
    try {
        $ls = $pdo->prepare("SELECT address, neighbourhood_slug, lat, lng FROM plex_properties WHERE pid = ? LIMIT 1");
        $ls->execute([$showcase_pid]);
        $lot = $ls->fetch(PDO::FETCH_ASSOC);
        if ($lot) {
            $showcase_prefill['address']   = $lot['address'] ?? '';
            $showcase_prefill['latitude']  = $lot['lat'] ?? '';
            $showcase_prefill['longitude'] = $lot['lng'] ?? '';
            // Convert nb_0XX slug to human-readable via slug_map if available
            if (!empty($lot['neighbourhood_slug'])) {
                $resolved = $lot['neighbourhood_slug'];
                $slug_file = __DIR__ . '/includes/slug_map.php';
                if (file_exists($slug_file)) {
                    require_once $slug_file;
                    if (function_exists('wynston_resolve_slug')) {
                        $resolved = wynston_resolve_slug($lot['neighbourhood_slug']);
                    }
                }
                // Pretty-print slug: "renfrew-collingwood" -> "Renfrew-Collingwood"
                $showcase_prefill['neighborhood'] = implode('-', array_map('ucfirst', explode('-', $resolved)));
            }
        }
    } catch (Exception $e) { /* leave prefill empty — builder will type manually */ }
}

// ── Handle form submission ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_property'])) {
try {

    $address = trim($_POST['address'] ?? '');
    if (!$address) $errors[] = 'Property address is required.';

    // Session 17: permit showcase mode — validated via POST-ed hidden fields
    // (pid + showcase_mode). Authorization checkbox required to prevent
    // casual claims of someone else's permit.
    $post_pid             = trim($_POST['pid'] ?? '');
    $post_showcase_mode   = !empty($_POST['showcase_mode']) && $post_pid !== '';
    if ($post_showcase_mode && empty($_POST['authorize_confirm'])) {
        $errors[] = 'You must confirm you are authorized to represent this project before submitting.';
    }

    if (empty($errors)) {
        // Convert video URL to embed
        $video_raw = trim($_POST['video_url'] ?? '');
        $video_url = '';
        if ($video_raw) {
            if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $video_raw, $m))        $video_url = 'https://www.youtube.com/embed/' . $m[1];
            elseif (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $video_raw, $m))         $video_url = 'https://www.youtube.com/embed/' . $m[1];
            elseif (preg_match('/vimeo\.com\/(\d+)/', $video_raw, $m))               $video_url = 'https://player.vimeo.com/video/' . $m[1];
            else $video_url = $video_raw;
        }

        // Insert new property row (draft, not paid yet)
        $cols_list = implode(',', array_intersect([
            'address','description','property_type','est_completion','neighborhood',
            'latitude','longitude','price','bedrooms','bathrooms','sqft','parking',
            'strata_fee','developer_name','developer_bio','builder_website',
            'builder_awards','virtual_tour_url','video_url','amenities','features',
            'community_features','is_paid','submitted_by','submit_status','pid'
        ], $existing_cols));

        // Session 17: ALL submissions (including permit showcases) go to
        // pending_review. Every listing must be manually reviewed and
        // approved by admin — this is the conversion touchpoint where
        // free-tier submissions can be upsold to creative ($3,000 photo
        // package) or concierge (signed listing contract). Auto-live is
        // NOT used anywhere, by design.
        $_submit_status = 'pending_review';

        $insert_data = [
            ':address'          => $address,
            ':description'      => trim($_POST['description']    ?? ''),
            ':property_type'    => trim($_POST['property_type']  ?? ''),
            ':est_completion'   => trim($_POST['est_completion']  ?? ''),
            ':neighborhood'     => trim($_POST['neighborhood']   ?? ''),
            ':latitude'         => (float)($_POST['latitude']    ?? 0) ?: null,
            ':longitude'        => (float)($_POST['longitude']   ?? 0) ?: null,
            ':price'            => trim($_POST['price']          ?? ''),
            ':bedrooms'         => $_POST['bedrooms']   !== '' ? (int)$_POST['bedrooms']   : null,
            ':bathrooms'        => $_POST['bathrooms']  !== '' ? (int)$_POST['bathrooms']  : null,
            ':sqft'             => $_POST['sqft']       !== '' ? (int)$_POST['sqft']       : null,
            ':parking'          => trim($_POST['parking']        ?? ''),
            ':strata_fee'       => trim($_POST['strata_fee']     ?? ''),
            ':developer_name'   => $dev['company_name'] ?? '',
            ':developer_bio'    => trim($_POST['developer_bio']  ?? ''),
            ':builder_website'  => $dev['website'] ?? '',
            ':builder_awards'   => trim($_POST['builder_awards'] ?? ''),
            ':virtual_tour_url' => trim($_POST['virtual_tour_url'] ?? ''),
            ':video_url'        => $video_url,
            ':amenities'        => trim($_POST['amenities']          ?? ''),
            ':features'         => trim($_POST['features']           ?? ''),
            ':community_features' => trim($_POST['community_features'] ?? ''),
            ':is_paid'          => 0,
            ':submitted_by'     => $dev['id'],
            ':submit_status'    => $_submit_status,
            ':pid'              => $post_showcase_mode ? $post_pid : null,
        ];

        // Only insert columns that exist
        $insert_cols   = [];
        $insert_params = [];
        foreach ($insert_data as $param => $val) {
            $col = ltrim($param, ':');
            if (in_array($col, $existing_cols)) {
                $insert_cols[]   = "`$col`";
                $insert_params[] = $param;
            }
        }

        $sql = "INSERT INTO multi_2025 (" . implode(',', $insert_cols) . ") VALUES (" . implode(',', $insert_params) . ")";
        $stmt = $pdo->prepare($sql);
        // Only bind params that are in our insert
        foreach ($insert_params as $param) {
            $stmt->bindValue($param, $insert_data[$param]);
        }
        $stmt->execute();
        $new_id = (int)$pdo->lastInsertId();

        // Create address-based folder
        $folder = get_property_folder($new_id, $address, __DIR__ . '/uploads/properties/', '/uploads/properties/');

        // Handle file uploads into subfolders
        $img_updates = [];
        for ($i = 1; $i <= 6; $i++) {
            $up = handle_upload_to_folder("img{$i}_file", "img{$i}", $folder['path'], $folder['url']);
            if ($up && strpos($up, 'ERR:') !== 0) $img_updates["img{$i}"] = $up;
        }

        $fp = handle_upload_to_folder('floorplan_file', 'floorplan', $folder['path'], $folder['url']);
        if ($fp && strpos($fp, 'ERR:') !== 0) $img_updates['floorplan'] = $fp;

        $bl = handle_upload_to_folder('builder_logo_file', 'builder_logo', $folder['path'], $folder['url']);
        if ($bl && strpos($bl, 'ERR:') !== 0) $img_updates['builder_logo'] = $bl;

        // Update row with file URLs if any were uploaded
        if (!empty($img_updates)) {
            $set_parts = array_map(function($c) { return "`$c` = :$c"; }, array_keys($img_updates));
            $upd = $pdo->prepare("UPDATE multi_2025 SET " . implode(',', $set_parts) . " WHERE id = :id");
            foreach ($img_updates as $col => $val) $upd->bindValue(":$col", $val);
            $upd->bindValue(':id', $new_id);
            $upd->execute();
        }

        $success_id = $new_id;
    }

} catch (Exception $e) {
    $errors[] = 'Submission error: ' . $e->getMessage();
}
}

// ── Load developer's existing submissions ────────────────────
$my_submissions = [];
try {
    $s = $pdo->prepare("SELECT id, address, property_type, price, submit_status, is_paid, created_at FROM multi_2025 WHERE submitted_by = ? ORDER BY id DESC");
    $s->execute([$dev['id']]);
    $my_submissions = $s->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit Property — Wynnston Developer Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark:  #0d0d1a;
            --navy:  #002446;
            --gold:  #c9a84c;
            --cream: #f9f6f0;
            --bdr:   #e8e4dd;
            --ss:    'Segoe UI', system-ui, sans-serif;
        }
        * { box-sizing: border-box; }
        body { font-family: var(--ss); background: var(--cream); margin: 0; }

        /* ── Top bar ── */
        .sp-topbar {
            background: var(--dark);
            padding: 12px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sp-topbar img { height: 34px; }
        .sp-topbar-right { display: flex; align-items: center; gap: 20px; font-size: 13px; color: #aaa; }
        .sp-topbar-right a { color: #aaa; text-decoration: none; }
        .sp-topbar-right a:hover { color: #fff; }
        .sp-dev-badge {
            background: rgba(201,168,76,.15);
            border: 1px solid rgba(201,168,76,.3);
            color: var(--gold);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ── Layout ── */
        .sp-layout { display: grid; grid-template-columns: 260px 1fr; min-height: calc(100vh - 58px); }

        /* ── Sidebar ── */
        .sp-sidebar {
            background: var(--navy);
            padding: 32px 0;
        }
        .sp-sidebar-section { padding: 0 20px 8px; }
        .sp-sidebar-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,.3);
            padding: 16px 20px 8px;
        }
        .sp-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: rgba(255,255,255,.55);
            text-decoration: none;
            font-size: 13px;
            border-radius: 0;
            transition: background .15s, color .15s;
            border-left: 3px solid transparent;
        }
        .sp-nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
        .sp-nav-item.active { background: rgba(255,255,255,.1); color: #fff; border-left-color: var(--gold); }
        .sp-nav-item i { width: 16px; text-align: center; font-size: 13px; }

        /* ── Main content ── */
        .sp-main { padding: 36px 40px; overflow-y: auto; }
        .sp-page-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 4px;
        }
        .sp-page-sub { font-size: 14px; color: #888; margin-bottom: 32px; }

        /* ── Form card ── */
        .sp-card {
            background: #fff;
            border-radius: 12px;
            padding: 28px 32px;
            margin-bottom: 24px;
            border: 1px solid var(--bdr);
        }
        .sp-card-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--navy);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--bdr);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sp-card-title i { color: var(--gold); }

        .sp-label {
            font-size: 12px;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: .5px;
            display: block;
            margin-bottom: 6px;
        }
        .sp-control {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid var(--bdr);
            border-radius: 8px;
            font-size: 14px;
            color: var(--dark);
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            margin-bottom: 16px;
        }
        .sp-control:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,36,70,.07); }
        select.sp-control { cursor: pointer; }
        textarea.sp-control { resize: vertical; min-height: 100px; }

        /* Upload zones */
        .sp-upload-zone {
            border: 2px dashed var(--bdr);
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            margin-bottom: 16px;
            color: #aaa;
            font-size: 13px;
        }
        .sp-upload-zone:hover { border-color: var(--navy); background: #f8faff; color: var(--navy); }
        .sp-upload-zone i { font-size: 28px; display: block; margin-bottom: 8px; }
        .sp-upload-zone small { display: block; font-size: 11px; margin-top: 4px; color: #bbb; }

        /* Photo grid preview */
        .sp-photo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
        .sp-photo-slot {
            aspect-ratio: 4/3;
            border: 2px dashed var(--bdr);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
            color: #bbb;
            transition: all .2s;
            overflow: hidden;
            position: relative;
        }
        .sp-photo-slot:hover { border-color: var(--navy); color: var(--navy); background: #f8faff; }
        .sp-photo-slot i { font-size: 22px; margin-bottom: 6px; }
        .sp-photo-slot img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .sp-photo-slot .sp-slot-num { position: absolute; top: 6px; left: 8px; background: rgba(0,0,0,.5); color: #fff; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; }

        /* Submit button */
        .sp-btn-submit {
            padding: 14px 40px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s, transform .1s;
        }
        .sp-btn-submit:hover { background: #003a7a; transform: translateY(-1px); }

        /* Alerts */
        .sp-alert { border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; font-size: 14px; }
        .sp-alert-error { background: #fff5f5; border: 1px solid #fcc; color: #c00; }
        .sp-alert-success { background: #f0faf4; border: 1px solid #b3dfc0; color: #1a6b3c; }

        /* Submissions table */
        .sp-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .sp-table th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: #888; padding: 10px 14px; border-bottom: 2px solid var(--bdr); text-align: left; }
        .sp-table td { padding: 12px 14px; border-bottom: 1px solid var(--bdr); color: #333; vertical-align: middle; }
        .sp-table tr:last-child td { border: 0; }
        .sp-table tr:hover td { background: #fafaf8; }

        /* Status badges */
        .sp-status { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .sp-status.pending  { background: #fff8e1; color: #b07800; }
        .sp-status.approved { background: #e8f5e9; color: #2e7d32; }
        .sp-status.draft    { background: #f5f5f5; color: #888; }
        .sp-status.live     { background: #e3f2fd; color: #1565c0; }

        /* Prefilled dev info notice */
        .sp-prefilled {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f0f4ff;
            border: 1px solid #d0daf5;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            color: #334;
            margin-bottom: 20px;
        }
        .sp-prefilled i { color: var(--navy); }

        @media (max-width: 900px) {
            .sp-layout { grid-template-columns: 1fr; }
            .sp-sidebar { display: none; }
            .sp-main { padding: 24px 16px; }
            .sp-photo-grid { grid-template-columns: repeat(2,1fr); }
        }
    </style>
</head>
<body>

<!-- Top bar -->
<div class="sp-topbar">
    <a href="index.php">
        <img src="/assets/img/logo-light.png" alt="Wynnston"
            onerror="this.style.display='none';this.nextSibling.style.display='block'">
        <span style="display:none;color:#fff;font-weight:800;font-size:18px;">WYNNSTON</span>
    </a>
    <div class="sp-topbar-right">
        <span class="sp-dev-badge"><i class="fas fa-building me-1"></i><?= htmlspecialchars($dev['company_name'] ?? 'Developer') ?></span>
        <a href="dashboard.php"><i class="fas fa-gauge me-1"></i>Dashboard</a>
        <a href="dev-logout.php"><i class="fas fa-power-off me-1"></i>Log Out</a>
    </div>
</div>

<div class="sp-layout">

    <!-- Sidebar -->
    <div class="sp-sidebar">
        <div class="sp-sidebar-label">Developer Portal</div>
        <a href="submit-property.php" class="sp-nav-item active"><i class="fas fa-plus-circle"></i>Submit Property</a>
        <a href="developer-dashboard.php" class="sp-nav-item"><i class="fas fa-arrow-left"></i>Back to Dashboard</a>
    </div>

    <!-- Main -->
    <div class="sp-main">

        <div class="sp-page-title">Submit a New Listing</div>
        <p class="sp-page-sub">Fill in your property details. Your submission will be reviewed by the Wynnston team before going live.</p>

        <?php if (!empty($errors)): ?>
        <div class="sp-alert sp-alert-error">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
        </div>
        <?php endif; ?>

        <?php if ($success_id): ?>
        <div class="sp-alert sp-alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Property submitted successfully!</strong> Reference #<?= $success_id ?> — the Wynnston team will review it and be in touch. 
            <a href="submit-property.php" style="color:var(--navy);font-weight:700;">Submit another →</a>
        </div>
        <?php else: ?>

        <form method="POST" enctype="multipart/form-data" action="">

            <?php if ($showcase_mode): ?>
            <!-- Session 17: Permit showcase banner — only rendered when arriving
                 from the Plex Map with ?pid=XXX&source=permit_showcase. -->
            <div style="background:linear-gradient(135deg,#002446 0%,#0a3a6b 100%);color:#fff;border-radius:12px;padding:22px 26px;margin-bottom:24px;display:flex;align-items:flex-start;gap:16px;">
                <div style="font-size:28px;color:#c9a84c;flex-shrink:0;">
                    <i class="fas fa-certificate"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#c9a84c;margin-bottom:6px;">Permit Showcase — Approved Building Permit</div>
                    <div style="font-size:18px;font-weight:800;margin-bottom:6px;">Showcase your approved permit project</div>
                    <div style="font-size:13px;color:rgba(255,255,255,.82);line-height:1.55;">
                        You're showcasing the approved building permit for <strong style="color:#fff;"><?= htmlspecialchars($showcase_prefill['address'] ?: 'PID '.$showcase_pid) ?></strong>.
                        Fill in your marketing details below (photos, description, price). Our team will review your submission and reach out about <strong style="color:#c9a84c;">free, creative, and concierge</strong> listing options.
                    </div>
                    <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:10px;">PID: <?= htmlspecialchars($showcase_pid) ?></div>
                </div>
            </div>
            <!-- Hidden fields carry the permit linkage through the POST -->
            <input type="hidden" name="pid" value="<?= htmlspecialchars($showcase_pid) ?>">
            <input type="hidden" name="showcase_mode" value="1">
            <?php endif; ?>

            <!-- Developer Info (pre-filled, read-only) -->
            <div class="sp-card">
                <div class="sp-card-title"><i class="fas fa-building"></i>Developer Info</div>
                <div class="sp-prefilled">
                    <i class="fas fa-info-circle"></i>
                    Developer name and website are pulled from your account profile. <a href="developer-profile.php" style="color:var(--navy);font-weight:600;">Update profile →</a>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="sp-label">Developer / Company</label>
                        <input type="text" class="sp-control" value="<?= htmlspecialchars($dev['company_name'] ?? '') ?>" readonly style="background:#f9f9f9;color:#888;">
                    </div>
                    <div class="col-md-6">
                        <label class="sp-label">Website</label>
                        <input type="text" class="sp-control" value="<?= htmlspecialchars($dev['website'] ?? '') ?>" readonly style="background:#f9f9f9;color:#888;">
                    </div>
                    <div class="col-12">
                        <label class="sp-label">Developer Description <small style="text-transform:none;font-weight:400;color:#aaa;">(shown on listing page)</small></label>
                        <textarea name="developer_bio" class="sp-control" placeholder="Brief description of your company and what makes your developments stand out..."><?= htmlspecialchars($_POST['developer_bio'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="sp-label">Awards & Recognition <small style="text-transform:none;font-weight:400;color:#aaa;">(one per line)</small></label>
                        <textarea name="builder_awards" class="sp-control" rows="2" placeholder="HAVAN Award 2024&#10;Georgie Award Finalist 2023"><?= htmlspecialchars($_POST['builder_awards'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="sp-card">
                <div class="sp-card-title"><i class="fas fa-home"></i>Property Details</div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="sp-label">Full Address *</label>
                        <input type="text" name="address" class="sp-control" placeholder="e.g. 4423 Main Street, Vancouver, BC V5V 3R4" value="<?= htmlspecialchars($_POST['address'] ?? $showcase_prefill['address']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="sp-label">Neighbourhood</label>
                        <input type="text" name="neighborhood" class="sp-control" placeholder="e.g. Riley Park" value="<?= htmlspecialchars($_POST['neighborhood'] ?? $showcase_prefill['neighborhood']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="sp-label">Property Type</label>
                        <select name="property_type" class="sp-control">
                            <option value="">— Select —</option>
                            <?php foreach (['Condo','Townhouse','Single Family','Half Duplex','Duplex','Multiplex','Apartment','Penthouse','Land'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($_POST['property_type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="sp-label">Est. Completion</label>
                        <input type="text" name="est_completion" class="sp-control" placeholder="e.g. Spring 2026" value="<?= htmlspecialchars($_POST['est_completion'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="sp-label">Starting Price</label>
                        <input type="text" name="price" class="sp-control" placeholder="e.g. $899,000" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="sp-label">Bedrooms</label>
                        <select name="bedrooms" class="sp-control">
                            <option value="">—</option>
                            <?php foreach (['Studio','1','2','3','4','5+'] as $b): ?>
                            <option value="<?= $b ?>" <?= ($_POST['bedrooms'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="sp-label">Bathrooms</label>
                        <select name="bathrooms" class="sp-control">
                            <option value="">—</option>
                            <?php foreach (['1','1.5','2','2.5','3','3.5','4+'] as $b): ?>
                            <option value="<?= $b ?>" <?= ($_POST['bathrooms'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="sp-label">Sq Ft</label>
                        <input type="number" name="sqft" class="sp-control" placeholder="e.g. 850" value="<?= htmlspecialchars($_POST['sqft'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="sp-label">Parking</label>
                        <input type="text" name="parking" class="sp-control" placeholder="e.g. 1 Underground Stall" value="<?= htmlspecialchars($_POST['parking'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="sp-label">Strata Fee</label>
                        <input type="text" name="strata_fee" class="sp-control" placeholder="e.g. $450/mo" value="<?= htmlspecialchars($_POST['strata_fee'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="sp-label">Description</label>
                        <textarea name="description" class="sp-control" rows="4" placeholder="Describe the development, lifestyle, key selling points..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="sp-card">
                <div class="sp-card-title"><i class="fas fa-list-check"></i>Features & Amenities</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="sp-label">Interior Features <small style="text-transform:none;font-weight:400;color:#aaa;">(comma separated)</small></label>
                        <input type="text" name="features" class="sp-control" placeholder="Radiant Heat, AC, Nest, Triple Glazed" value="<?= htmlspecialchars($_POST['features'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="sp-label">Building Amenities <small style="text-transform:none;font-weight:400;color:#aaa;">(comma separated)</small></label>
                        <input type="text" name="amenities" class="sp-control" placeholder="Rooftop Deck, EV Charging, Concierge" value="<?= htmlspecialchars($_POST['amenities'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="sp-label">Lot / Community <small style="text-transform:none;font-weight:400;color:#aaa;">(comma separated)</small></label>
                        <input type="text" name="community_features" class="sp-control" placeholder="Corner Lot, Lane Access, South Facing" value="<?= htmlspecialchars($_POST['community_features'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Photos -->
            <div class="sp-card">
                <div class="sp-card-title"><i class="fas fa-images"></i>Photos <small style="font-weight:400;text-transform:none;font-size:12px;color:#aaa;">(up to 6 — JPG, PNG, WEBP, max 10MB each)</small></div>
                <div class="sp-photo-grid">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div class="sp-photo-slot" onclick="document.getElementById('img<?= $i ?>_file').click()" id="slot<?= $i ?>">
                        <span class="sp-slot-num"><?= $i ?></span>
                        <i class="fas fa-plus"></i>
                        <span>Photo <?= $i ?><?= $i === 1 ? ' (Main)' : '' ?></span>
                        <input type="file" id="img<?= $i ?>_file" name="img<?= $i ?>_file" accept="image/*" style="display:none" onchange="previewPhoto(this, 'slot<?= $i ?>')">
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Media & Documents -->
            <div class="sp-card">
                <div class="sp-card-title"><i class="fas fa-film"></i>Media & Documents</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="sp-label">Video URL <small style="text-transform:none;font-weight:400;color:#aaa;">(YouTube or Vimeo)</small></label>
                        <input type="text" name="video_url" class="sp-control" placeholder="https://www.youtube.com/watch?v=..." value="<?= htmlspecialchars($_POST['video_url'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="sp-label">Virtual Tour URL <small style="text-transform:none;font-weight:400;color:#aaa;">(Matterport, etc.)</small></label>
                        <input type="text" name="virtual_tour_url" class="sp-control" placeholder="https://my.matterport.com/show/?m=..." value="<?= htmlspecialchars($_POST['virtual_tour_url'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="sp-label">Floor Plan <small style="text-transform:none;font-weight:400;color:#aaa;">(PDF, JPG, PNG)</small></label>
                        <div class="sp-upload-zone" onclick="document.getElementById('floorplan_file').click()" id="fp-zone">
                            <i class="fas fa-file-lines"></i>
                            Click to upload floor plan
                            <small>PDF, JPG, PNG — max 10MB</small>
                        </div>
                        <input type="file" id="floorplan_file" name="floorplan_file" accept=".pdf,.jpg,.jpeg,.png,.webp" style="display:none" onchange="updateZone(this,'fp-zone')">
                    </div>
                    <div class="col-md-6">
                        <label class="sp-label">Developer Logo <small style="text-transform:none;font-weight:400;color:#aaa;">(JPG, PNG, WEBP)</small></label>
                        <div class="sp-upload-zone" onclick="document.getElementById('builder_logo_file').click()" id="bl-zone">
                            <i class="fas fa-building"></i>
                            Click to upload logo
                            <small>JPG, PNG, WEBP — max 10MB</small>
                        </div>
                        <input type="file" id="builder_logo_file" name="builder_logo_file" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none" onchange="updateZone(this,'bl-zone')">
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="sp-card">
                <div class="sp-card-title"><i class="fas fa-map-pin"></i>Location Coordinates <small style="font-weight:400;text-transform:none;font-size:12px;color:#aaa;">(optional — used for map and Walk Score)</small></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="sp-label">Latitude</label>
                        <input type="text" name="latitude" class="sp-control" placeholder="e.g. 49.2827" value="<?= htmlspecialchars($_POST['latitude'] ?? $showcase_prefill['latitude']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="sp-label">Longitude</label>
                        <input type="text" name="longitude" class="sp-control" placeholder="e.g. -123.1207" value="<?= htmlspecialchars($_POST['longitude'] ?? $showcase_prefill['longitude']) ?>">
                    </div>
                    <div class="col-12" style="font-size:12px;color:#aaa;">
                        <i class="fas fa-lightbulb me-1" style="color:var(--gold);"></i>
                        Find coordinates: right-click any location on <a href="https://maps.google.com" target="_blank" style="color:var(--navy);">Google Maps</a> → "What's here?"
                    </div>
                </div>
            </div>

            <?php if ($showcase_mode): ?>
            <!-- Session 17: Authorization checkbox — required for permit showcase
                 submissions. Server-side enforcement is in the POST handler. -->
            <div class="sp-card" style="background:#fffbea;border:1px solid #fbe8a6;">
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <input type="checkbox" name="authorize_confirm" id="authorize_confirm" value="1" required style="margin-top:4px;width:18px;height:18px;flex-shrink:0;cursor:pointer;">
                    <label for="authorize_confirm" style="font-size:13px;line-height:1.6;color:#4b3b0a;cursor:pointer;flex:1;">
                        <strong>I confirm I am the developer, builder, realtor, or authorized representative of this project.</strong><br>
                        <span style="font-size:12px;color:#7a6528;">Listings that misrepresent ownership may be removed and the associated account suspended.</span>
                    </label>
                </div>
            </div>
            <?php endif; ?>

            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                <button type="submit" name="submit_property" class="sp-btn-submit">
                    <i class="fas fa-paper-plane me-2"></i>Submit for Review
                </button>
                <span style="font-size:12px;color:#aaa;">
                    <?= $showcase_mode
                        ? 'Your showcase submission will be reviewed by the Wynston team before going live. We\'ll reach out about listing options.'
                        : 'Your listing will be reviewed by our team before publishing.' ?>
                </span>
            </div>

        </form>

        <?php endif; ?>

        <!-- My Submissions -->
        <?php if (!empty($my_submissions)): ?>
        <div class="sp-card" style="margin-top:40px;">
            <div class="sp-card-title"><i class="fas fa-clock-rotate-left"></i>My Submissions</div>
            <table class="sp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Address</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Concierge</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_submissions as $sub): ?>
                    <tr>
                        <td style="color:#aaa;"><?= $sub['id'] ?></td>
                        <td><strong><?= htmlspecialchars($sub['address']) ?></strong></td>
                        <td><?= htmlspecialchars($sub['property_type'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($sub['price'] ?? '—') ?></td>
                        <td>
                            <?php
                            $st = $sub['submit_status'] ?? 'draft';
                            if ($st === 'pending_review') $label = 'Pending';
                                            elseif ($st === 'approved') $label = 'Approved';
                                            elseif ($st === 'live') $label = 'Live';
                                            else $label = 'Draft';
                            ?>
                            <span class="sp-status <?= $st === 'pending_review' ? 'pending' : $st ?>"><?= $label ?></span>
                        </td>
                        <td>
                            <?php if ($sub['is_paid']): ?>
                            <span class="sp-status live">Concierge</span>
                            <?php else: ?>
                            <span class="sp-status draft">Standard</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:#aaa;font-size:12px;"><?= date('M j, Y', strtotime($sub['created_at'] ?? 'now')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div><!-- /sp-main -->
</div><!-- /sp-layout -->

<script>
function previewPhoto(input, slotId) {
    if (!input.files || !input.files[0]) return;
    var slot = document.getElementById(slotId);
    var reader = new FileReader();
    reader.onload = function(e) {
        // Remove old preview
        var old = slot.querySelector('img');
        if (old) old.remove();
        var img = document.createElement('img');
        img.src = e.target.result;
        slot.appendChild(img);
        slot.querySelector('i').style.display = 'none';
        slot.querySelector('span:not(.sp-slot-num)').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

function updateZone(input, zoneId) {
    if (!input.files || !input.files[0]) return;
    var zone = document.getElementById(zoneId);
    zone.innerHTML = '<i class="fas fa-check-circle" style="color:#198754;"></i>' +
        '<span style="color:#198754;font-weight:600;">' + input.files[0].name + '</span>' +
        '<small>' + (input.files[0].size / 1024 / 1024).toFixed(1) + ' MB</small>';
}
</script>

</body>
</html>