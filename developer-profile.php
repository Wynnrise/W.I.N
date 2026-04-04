<?php
// ============================================================
//  developer-profile.php  —  Edit developer profile + photo
// ============================================================
require_once __DIR__ . '/dev-auth.php';
dev_require_login('log-in.php');

$dev     = dev_current();
$message = '';
$error   = '';

// ── Handle form save ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name    = trim($_POST['full_name']    ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $phone        = trim($_POST['phone']        ?? '');
    $website      = trim($_POST['website']      ?? '');
    $projects     = trim($_POST['projects']     ?? '');

    if (!$full_name)    $error = 'Full name is required.';
    if (!$company_name) $error = 'Company name is required.';

    if (!$error) {
        // ── Handle photo upload ───────────────────────────────
        $logo_path = $dev['logo_path'] ?? '';

        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file    = $_FILES['photo'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];

            if (!in_array($ext, $allowed)) {
                $error = 'Photo must be JPG, PNG, WEBP, or GIF.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'Photo must be under 5MB.';
            } else {
                // Save to /uploads/developers/{id}/
                $dev_dir = __DIR__ . '/uploads/developers/' . $dev['id'] . '/';
                if (!is_dir($dev_dir)) mkdir($dev_dir, 0755, true);

                // Delete old photo if exists
                if ($logo_path && file_exists(__DIR__ . $logo_path)) {
                    @unlink(__DIR__ . $logo_path);
                }

                $filename  = 'profile_' . time() . '.' . $ext;
                $dest_path = $dev_dir . $filename;
                $dest_url  = '/uploads/developers/' . $dev['id'] . '/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                    $logo_path = $dest_url;
                } else {
                    $error = 'Photo upload failed. Check folder permissions.';
                }
            }
        }

        if (!$error) {
            $pdo->prepare("UPDATE developers SET
                full_name    = ?,
                company_name = ?,
                phone        = ?,
                website      = ?,
                projects     = ?,
                logo_path    = ?
                WHERE id = ?")
                ->execute([$full_name, $company_name, $phone, $website, $projects, $logo_path, $dev['id']]);

            // Update session name
            $_SESSION['dev_name']    = $full_name;
            $_SESSION['dev_company'] = $company_name;

            // Refresh dev data
            $dev     = dev_current();
            $message = 'Profile updated successfully.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile — Wynnston Developer Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --dark:#0d0d1a; --navy:#002446; --gold:#c9a84c; --cream:#f9f6f0; --bdr:#e8e4dd; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--cream); margin: 0; }

        .db-topbar { background: var(--dark); padding: 12px 32px; display: flex; align-items: center; justify-content: space-between; }
        .db-topbar img { height: 34px; }
        .db-topbar-right { display: flex; align-items: center; gap: 20px; font-size: 13px; color: #aaa; }
        .db-topbar-right a { color: #aaa; text-decoration: none; }
        .db-topbar-right a:hover { color: #fff; }
        .db-dev-badge { background: rgba(201,168,76,.15); border: 1px solid rgba(201,168,76,.3); color: var(--gold); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }

        .db-layout { display: grid; grid-template-columns: 240px 1fr; min-height: calc(100vh - 58px); }

        /* Sidebar */
        .db-sidebar { background: var(--navy); padding: 28px 0; display: flex; flex-direction: column; }
        .db-avatar { padding: 0 20px 24px; border-bottom: 1px solid rgba(255,255,255,.1); margin-bottom: 8px; }
        .db-avatar-circle { width: 52px; height: 52px; border-radius: 50%; background: rgba(201,168,76,.2); border: 2px solid rgba(201,168,76,.4); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; color: var(--gold); margin-bottom: 10px; overflow: hidden; }
        .db-avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
        .db-avatar h4 { font-size: 14px; font-weight: 700; color: #fff; margin: 0 0 2px; }
        .db-avatar span { font-size: 12px; color: rgba(255,255,255,.4); }
        .db-sidebar-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.25); padding: 16px 20px 6px; }
        .db-nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: rgba(255,255,255,.5); text-decoration: none; font-size: 13px; transition: all .15s; border-left: 3px solid transparent; }
        .db-nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
        .db-nav-item.active { background: rgba(255,255,255,.1); color: #fff; border-left-color: var(--gold); }
        .db-nav-item i { width: 16px; text-align: center; font-size: 13px; }

        /* Main */
        .db-main { padding: 36px 40px; }
        .db-page-title { font-size: 22px; font-weight: 800; color: var(--dark); margin-bottom: 4px; }
        .db-page-sub { font-size: 14px; color: #888; margin-bottom: 28px; }

        /* Cards */
        .db-card { background: #fff; border-radius: 12px; padding: 28px 32px; margin-bottom: 24px; border: 1px solid var(--bdr); }
        .db-card-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--navy); margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid var(--bdr); display: flex; align-items: center; gap: 8px; }
        .db-card-title i { color: var(--gold); }

        /* Form */
        .db-label { font-size: 12px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 6px; }
        .db-control { width: 100%; padding: 10px 13px; border: 1.5px solid var(--bdr); border-radius: 8px; font-size: 14px; color: var(--dark); background: #fff; transition: border-color .2s, box-shadow .2s; outline: none; margin-bottom: 16px; }
        .db-control:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,36,70,.07); }
        textarea.db-control { resize: vertical; min-height: 90px; }
        .db-control[readonly] { background: #f9f9f9; color: #888; cursor: not-allowed; }

        /* Photo upload */
        .photo-wrap { display: flex; align-items: flex-start; gap: 24px; margin-bottom: 20px; }
        .photo-preview { width: 100px; height: 100px; border-radius: 50%; border: 3px solid var(--bdr); overflow: hidden; background: #f0f0f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 36px; font-weight: 800; color: var(--gold); position: relative; cursor: pointer; transition: opacity .2s; }
        .photo-preview:hover { opacity: .85; }
        .photo-preview img { width: 100%; height: 100%; object-fit: cover; }
        .photo-preview .photo-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.45); display: none; align-items: center; justify-content: center; color: #fff; font-size: 20px; border-radius: 50%; }
        .photo-preview:hover .photo-overlay { display: flex; }
        .photo-info { padding-top: 8px; }
        .photo-info h4 { font-size: 14px; font-weight: 700; color: var(--dark); margin: 0 0 4px; }
        .photo-info p { font-size: 12px; color: #888; margin: 0 0 12px; line-height: 1.6; }
        .photo-btn { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--navy); color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .2s; }
        .photo-btn:hover { background: #003a7a; }

        /* Save button */
        .db-btn-save { padding: 12px 36px; background: var(--navy); color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .2s; }
        .db-btn-save:hover { background: #003a7a; }

        /* Alerts */
        .db-alert { border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .db-alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .db-alert-error   { background: #fff5f5; border: 1px solid #fcc; color: #c00; }

        @media (max-width: 900px) {
            .db-layout { grid-template-columns: 1fr; }
            .db-sidebar { display: none; }
            .db-main { padding: 20px 16px; }
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
        <span class="db-dev-badge"><i class="fas fa-building me-1"></i><?= htmlspecialchars($dev['company_name'] ?? '') ?></span>
        <a href="developer-dashboard.php"><i class="fas fa-gauge me-1"></i>Dashboard</a>
        <a href="dev-logout.php"><i class="fas fa-power-off me-1"></i>Log Out</a>
    </div>
</div>

<div class="db-layout">

    <!-- Sidebar -->
    <div class="db-sidebar">
        <div class="db-avatar">
            <div class="db-avatar-circle">
                <?php if (!empty($dev['logo_path'])): ?>
                    <img src="<?= htmlspecialchars($dev['logo_path']) ?>" alt="">
                <?php else: ?>
                    <?= strtoupper(substr($dev['full_name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <h4><?= htmlspecialchars($dev['full_name']) ?></h4>
            <span><?= htmlspecialchars($dev['company_name'] ?? '') ?></span>
        </div>
        <a href="developer-dashboard.php" class="db-nav-item"><i class="fas fa-gauge"></i>Dashboard</a>
        <a href="submit-property.php" class="db-nav-item"><i class="fas fa-plus-circle"></i>Submit Property</a>
        <a href="developer-dashboard.php#my-listings" class="db-nav-item"><i class="fas fa-building"></i>My Listings</a>
        <div class="db-sidebar-label">Account</div>
        <a href="developer-profile.php" class="db-nav-item active"><i class="fas fa-address-card"></i>My Profile</a>
        <a href="change-password.php" class="db-nav-item"><i class="fas fa-key"></i>Change Password</a>
        <div style="flex:1;"></div>
        <a href="dev-logout.php" class="db-nav-item"><i class="fas fa-power-off"></i>Log Out</a>
    </div>

    <!-- Main -->
    <div class="db-main">
        <div class="db-page-title">My Profile</div>
        <p class="db-page-sub">Your information appears on your listings and is used to contact you about submissions.</p>

        <?php if ($message): ?>
        <div class="db-alert db-alert-success"><i class="fas fa-circle-check"></i><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="db-alert db-alert-error"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <!-- Profile Photo -->
            <div class="db-card">
                <div class="db-card-title"><i class="fas fa-camera"></i>Profile Photo</div>
                <div class="photo-wrap">
                    <div class="photo-preview" onclick="document.getElementById('photo_input').click()" id="photo-preview">
                        <?php if (!empty($dev['logo_path'])): ?>
                            <img src="<?= htmlspecialchars($dev['logo_path']) ?>" alt="Profile" id="photo-img">
                        <?php else: ?>
                            <span id="photo-initial"><?= strtoupper(substr($dev['full_name'], 0, 1)) ?></span>
                        <?php endif; ?>
                        <div class="photo-overlay"><i class="fas fa-camera"></i></div>
                    </div>
                    <div class="photo-info">
                        <h4>Company / Profile Photo</h4>
                        <p>This photo appears as your avatar in the developer portal and on your listings page.<br>JPG, PNG, WEBP — max 5MB — square crop recommended.</p>
                        <button type="button" class="photo-btn" onclick="document.getElementById('photo_input').click()">
                            <i class="fas fa-upload"></i>
                            <?= !empty($dev['logo_path']) ? 'Change Photo' : 'Upload Photo' ?>
                        </button>
                        <input type="file" id="photo_input" name="photo" accept="image/*" style="display:none" onchange="previewPhoto(this)">
                        <div id="photo-filename" style="font-size:12px;color:#16a34a;margin-top:8px;display:none;"></div>
                    </div>
                </div>
            </div>

            <!-- Personal Info -->
            <div class="db-card">
                <div class="db-card-title"><i class="fas fa-user"></i>Personal Information</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="db-label">Full Name *</label>
                        <input type="text" name="full_name" class="db-control" value="<?= htmlspecialchars($dev['full_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="db-label">Phone</label>
                        <input type="tel" name="phone" class="db-control" placeholder="(604) 000-0000" value="<?= htmlspecialchars($dev['phone'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="db-label">Email <small style="text-transform:none;font-weight:400;color:#aaa;">(cannot be changed here)</small></label>
                        <input type="email" class="db-control" value="<?= htmlspecialchars($dev['email']) ?>" readonly>
                    </div>
                </div>
            </div>

            <!-- Company Info -->
            <div class="db-card">
                <div class="db-card-title"><i class="fas fa-building"></i>Company Information</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="db-label">Developer / Company Name *</label>
                        <input type="text" name="company_name" class="db-control" value="<?= htmlspecialchars($dev['company_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="db-label">Company Website</label>
                        <input type="url" name="website" class="db-control" placeholder="https://www.yourcompany.com" value="<?= htmlspecialchars($dev['website'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="db-label">Current & Upcoming Projects <small style="text-transform:none;font-weight:400;color:#aaa;">(one per line)</small></label>
                        <textarea name="projects" class="db-control"><?= htmlspecialchars($dev['projects'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Account Info (read only) -->
            <div class="db-card">
                <div class="db-card-title"><i class="fas fa-shield-halved"></i>Account Status</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="db-label">Account Status</label>
                        <?php
                        $sc = match($dev['status']) {
                            'approved'  => 'background:#dcfce7;color:#16a34a;',
                            'suspended' => 'background:#fee2e2;color:#dc2626;',
                            default     => 'background:#fef3c7;color:#b45309;',
                        };
                        ?>
                        <div style="padding:10px 13px;border-radius:8px;font-size:14px;font-weight:700;<?= $sc ?>">
                            <?= ucfirst($dev['status']) ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="db-label">Email Verification</label>
                        <div style="padding:10px 13px;border-radius:8px;font-size:14px;font-weight:700;<?= !empty($dev['email_verified']) ? 'background:#dcfce7;color:#16a34a;' : 'background:#fef3c7;color:#b45309;' ?>">
                            <?= !empty($dev['email_verified']) ? '✓ Verified' : '⏳ Unverified' ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="db-label">Member Since</label>
                        <div style="padding:10px 13px;border-radius:8px;font-size:14px;background:#f9f9f9;color:#555;">
                            <?= date('M j, Y', strtotime($dev['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="db-btn-save">
                <i class="fas fa-save me-2"></i>Save Changes
            </button>

        </form>
    </div>
</div>

<script>
function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];

    // Show filename
    var fn = document.getElementById('photo-filename');
    fn.textContent = '✓ ' + file.name + ' selected — save to upload';
    fn.style.display = 'block';

    // Live preview
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById('photo-preview');
        // Remove text/initial span
        var initial = document.getElementById('photo-initial');
        if (initial) initial.style.display = 'none';
        // Update or create img
        var img = document.getElementById('photo-img');
        if (!img) {
            img = document.createElement('img');
            img.id = 'photo-img';
            img.style.cssText = 'width:100%;height:100%;object-fit:cover;position:absolute;inset:0;';
            preview.insertBefore(img, preview.firstChild);
        }
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}
</script>

</body>
</html>