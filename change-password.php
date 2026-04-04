<?php
// ============================================================
//  change-password.php  —  Developer Portal Change Password
// ============================================================
require_once __DIR__ . '/dev-auth.php';
dev_require_login('log-in.php');

$dev = dev_current();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current  = $_POST['current_password']  ?? '';
    $new      = $_POST['new_password']      ?? '';
    $confirm  = $_POST['confirm_password']  ?? '';

    if (empty($current) || empty($new) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New password and confirmation do not match.';
    } else {
        // Verify current password
        $s = $pdo->prepare("SELECT password_hash FROM developers WHERE id = ?");
        $s->execute([$dev['id']]);
        $row = $s->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($current, $row['password_hash'])) {
            $error = 'Your current password is incorrect.';
        } else {
            // Save new password
            $new_hash = password_hash($new, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE developers SET password_hash = ? WHERE id = ?")->execute([$new_hash, $dev['id']]);
            $success = 'Your password has been updated successfully.';
        }
    }
}

$first_name = explode(' ', trim($dev['full_name']))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="shortcut icon" href="/assets/img/favicon.png">
    <title>Change Password — <?= htmlspecialchars($dev['company_name'] ?? 'Developer') ?></title>
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
        .db-main { padding: 36px 40px; }

        /* Card */
        .pw-card { background: #fff; border-radius: 12px; border: 1px solid var(--bdr); padding: 36px; max-width: 520px; }
        .pw-card h2 { font-size: 20px; font-weight: 800; color: var(--dark); margin: 0 0 6px; }
        .pw-card p.sub { font-size: 13px; color: #888; margin: 0 0 28px; }

        /* Form */
        .form-label { font-size: 12px; font-weight: 700; color: #555; margin-bottom: 5px; display: block; }
        .form-control { width: 100%; padding: 10px 14px; border: 1.5px solid #dde; border-radius: 8px; font-size: 14px; font-family: inherit; transition: border-color .2s; background: #fff; }
        .form-control:focus { outline: none; border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,36,70,.08); }
        .input-wrap { position: relative; }
        .input-wrap .toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #aaa; font-size: 14px; padding: 0; }
        .input-wrap .toggle-pw:hover { color: var(--navy); }

        /* Requirements */
        .pw-requirements { background: #f8faff; border: 1px solid #e0e8ff; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; }
        .pw-requirements p { font-size: 12px; font-weight: 700; color: var(--navy); margin: 0 0 8px; }
        .pw-req-item { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #888; margin-bottom: 4px; }
        .pw-req-item i { font-size: 11px; width: 14px; }
        .pw-req-item.met { color: #16a34a; }
        .pw-req-item.met i { color: #16a34a; }

        /* Strength bar */
        .pw-strength { margin-bottom: 20px; }
        .pw-strength-label { font-size: 11px; color: #888; margin-bottom: 4px; display: flex; justify-content: space-between; }
        .pw-strength-bar { height: 4px; background: #eee; border-radius: 4px; overflow: hidden; }
        .pw-strength-fill { height: 100%; border-radius: 4px; transition: width .3s, background .3s; width: 0%; }

        /* Alerts */
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 14px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        /* Button */
        .btn-save { background: var(--navy); color: #fff; border: none; padding: 12px 32px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .2s; width: 100%; }
        .btn-save:hover { background: #0065ff; }
        .btn-save:disabled { background: #ccc; cursor: not-allowed; }

        .mb-4 { margin-bottom: 20px; }
        .mb-3 { margin-bottom: 14px; }

        @media (max-width: 900px) {
            .db-layout { grid-template-columns: 1fr; }
            .db-sidebar { display: none; }
            .db-main { padding: 20px 16px; }
            .pw-card { padding: 24px 18px; }
        }
    </style>
</head>
<body>

<!-- Top bar -->
<div class="db-topbar">
    <a href="index.php">
        <img src="/assets/img/logo-light.png" alt="Wynnston"
             onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
        <span style="display:none;color:#fff;font-size:18px;font-weight:800;letter-spacing:1px;">W</span>
    </a>
    <div class="db-topbar-right">
        <span class="db-dev-badge"><i class="fas fa-building me-1"></i><?= htmlspecialchars($dev['company_name'] ?? 'Developer') ?></span>
        <a href="index.php" target="_blank"><i class="fas fa-globe me-1"></i>View Site</a>
        <a href="dev-logout.php"><i class="fas fa-sign-out-alt me-1"></i>Log Out</a>
    </div>
</div>

<!-- Layout -->
<div class="db-layout">

    <!-- Sidebar -->
    <div class="db-sidebar">
        <div class="db-avatar">
            <?php
            $logo = $dev['logo_path'] ?? '';
            $initials = strtoupper(substr($dev['full_name'] ?? 'D', 0, 1));
            ?>
            <div class="db-avatar-circle">
                <?php if (!empty($logo)): ?>
                <img src="<?= htmlspecialchars($logo) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
                <?php else: ?>
                <?= $initials ?>
                <?php endif; ?>
            </div>
            <h4><?= htmlspecialchars($dev['full_name']) ?></h4>
            <span><?= htmlspecialchars($dev['company_name'] ?? '') ?></span>
        </div>

        <div class="db-sidebar-label">Main</div>
        <a href="developer-dashboard.php" class="db-nav-item"><i class="fas fa-gauge"></i>Dashboard</a>
        <a href="submit-property.php" class="db-nav-item"><i class="fas fa-plus-circle"></i>Submit Property</a>

        <div class="db-sidebar-label">Account</div>
        <a href="developer-profile.php" class="db-nav-item"><i class="fas fa-address-card"></i>My Profile</a>
        <a href="change-password.php" class="db-nav-item active"><i class="fas fa-lock"></i>Change Password</a>
        <a href="dev-logout.php" class="db-nav-item" style="margin-top:auto;"><i class="fas fa-sign-out-alt"></i>Log Out</a>
    </div>

    <!-- Main content -->
    <div class="db-main">

        <div style="margin-bottom:28px;">
            <h1 style="font-size:24px;font-weight:800;color:var(--dark);margin:0 0 4px;">Change Password</h1>
            <p style="font-size:14px;color:#888;margin:0;">Update your login password for <?= htmlspecialchars($dev['email']) ?></p>
        </div>

        <div class="pw-card">

            <?php if (!empty($success)): ?>
            <div class="alert-success">
                <i class="fas fa-circle-check"></i><?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
            <div class="alert-error">
                <i class="fas fa-triangle-exclamation"></i><?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="pw-form">

                <!-- Current password -->
                <div class="mb-4">
                    <label class="form-label">Current Password</label>
                    <div class="input-wrap">
                        <input type="password" name="current_password" id="current_password"
                               class="form-control" placeholder="Enter your current password" required autocomplete="current-password">
                        <button type="button" class="toggle-pw" onclick="togglePw('current_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- New password -->
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <div class="input-wrap">
                        <input type="password" name="new_password" id="new_password"
                               class="form-control" placeholder="Enter new password" required autocomplete="new-password"
                               oninput="checkStrength(this.value)">
                        <button type="button" class="toggle-pw" onclick="togglePw('new_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Strength bar -->
                <div class="pw-strength mb-3">
                    <div class="pw-strength-label">
                        <span>Password strength</span>
                        <span id="strength-label">—</span>
                    </div>
                    <div class="pw-strength-bar">
                        <div class="pw-strength-fill" id="strength-fill"></div>
                    </div>
                </div>

                <!-- Requirements -->
                <div class="pw-requirements mb-4">
                    <p><i class="fas fa-shield-halved me-1"></i>Password must include:</p>
                    <div class="pw-req-item" id="req-length"><i class="fas fa-circle"></i>At least 8 characters</div>
                    <div class="pw-req-item" id="req-upper"><i class="fas fa-circle"></i>One uppercase letter</div>
                    <div class="pw-req-item" id="req-number"><i class="fas fa-circle"></i>One number</div>
                    <div class="pw-req-item" id="req-special"><i class="fas fa-circle"></i>One special character (!@#$...)</div>
                </div>

                <!-- Confirm password -->
                <div class="mb-4">
                    <label class="form-label">Confirm New Password</label>
                    <div class="input-wrap">
                        <input type="password" name="confirm_password" id="confirm_password"
                               class="form-control" placeholder="Repeat new password" required autocomplete="new-password"
                               oninput="checkMatch()">
                        <button type="button" class="toggle-pw" onclick="togglePw('confirm_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="match-msg" style="font-size:12px;margin-top:5px;"></div>
                </div>

                <button type="submit" class="btn-save" id="submit-btn">
                    <i class="fas fa-lock me-2"></i>Update Password
                </button>

            </form>
        </div>

    </div><!-- /db-main -->
</div><!-- /db-layout -->

<script>
function togglePw(fieldId, btn) {
    var field = document.getElementById(fieldId);
    var icon  = btn.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function checkStrength(val) {
    var score = 0;
    var hasLength  = val.length >= 8;
    var hasUpper   = /[A-Z]/.test(val);
    var hasNumber  = /[0-9]/.test(val);
    var hasSpecial = /[^A-Za-z0-9]/.test(val);

    // Update requirement indicators
    setReq('req-length',  hasLength);
    setReq('req-upper',   hasUpper);
    setReq('req-number',  hasNumber);
    setReq('req-special', hasSpecial);

    if (hasLength)  score++;
    if (hasUpper)   score++;
    if (hasNumber)  score++;
    if (hasSpecial) score++;

    var fill  = document.getElementById('strength-fill');
    var label = document.getElementById('strength-label');

    var widths = ['0%', '25%', '50%', '75%', '100%'];
    var colors = ['#eee', '#ef4444', '#f59e0b', '#3b82f6', '#16a34a'];
    var labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];

    fill.style.width      = widths[score];
    fill.style.background = colors[score];
    label.textContent     = labels[score];
    label.style.color     = colors[score];

    checkMatch();
}

function setReq(id, met) {
    var el = document.getElementById(id);
    if (met) {
        el.className = 'pw-req-item met';
        el.querySelector('i').className = 'fas fa-circle-check';
    } else {
        el.className = 'pw-req-item';
        el.querySelector('i').className = 'fas fa-circle';
    }
}

function checkMatch() {
    var np = document.getElementById('new_password').value;
    var cp = document.getElementById('confirm_password').value;
    var msg = document.getElementById('match-msg');
    var btn = document.getElementById('submit-btn');

    if (cp.length === 0) {
        msg.textContent = '';
        return;
    }
    if (np === cp) {
        msg.innerHTML = '<span style="color:#16a34a;"><i class="fas fa-check me-1"></i>Passwords match</span>';
    } else {
        msg.innerHTML = '<span style="color:#ef4444;"><i class="fas fa-times me-1"></i>Passwords do not match</span>';
    }
}
</script>

</body>
</html>