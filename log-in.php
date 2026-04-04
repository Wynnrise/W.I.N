<?php
// ============================================================
//  log-in.php  —  Developer Login (full page)
// ============================================================
require_once __DIR__ . '/dev-auth.php';

if (dev_logged_in()) {
    header('Location: developer-dashboard.php'); exit;
}

$error = '';
$next  = $_GET['next'] ?? 'developer-dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $next     = $_POST['next']          ?? 'developer-dashboard.php';

    if ($email && $password) {
        $s = $pdo->prepare("SELECT * FROM developers WHERE email = ? LIMIT 1");
        $s->execute([$email]);
        $dev = $s->fetch(PDO::FETCH_ASSOC);

        if ($dev && password_verify($password, $dev['password_hash'])) {
            if ($dev['status'] === 'suspended') {
                $error = 'Your account has been suspended. Please contact support.';
            } elseif ($dev['status'] === 'pending') {
                $error = 'Your account is pending approval. You\'ll receive an email once approved.';
            } else {
                // Login success
                $_SESSION['dev_id']          = $dev['id'];
                $_SESSION['dev_name']        = $dev['full_name'];
                $_SESSION['dev_company']     = $dev['company_name'];
                $_SESSION['dev_last_active'] = time();
                // Update last login
                $pdo->prepare("UPDATE developers SET last_login = NOW() WHERE id = ?")->execute([$dev['id']]);
                header('Location: ' . $next); exit;
            }
        } else {
            $error = 'Incorrect email or password.';
        }
    } else {
        $error = 'Please enter your email and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — Wynnston Developer Portal</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="shortcut icon" href="/assets/img/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark:  #0d0d1a;
            --navy:  #002446;
            --gold:  #c9a84c;
            --cream: #f9f6f0;
            --bdr:   #e8e4dd;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--cream); min-height: 100vh; display: flex; flex-direction: column; }

        .ac-topbar { background: transparent; padding: 20px 32px; display: flex; align-items: center; justify-content: space-between; position: absolute; top: 0; left: 0; right: 0; z-index: 10; }
        .ac-topbar a { color: #fff; text-decoration: none; }
        .ac-topbar img { height: 38px; }
        .ac-topbar-link { font-size: 13px; color: #444; }
        .ac-topbar-link a { color: var(--navy); font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }

        .ac-wrap { display: grid; grid-template-columns: 1fr 1fr; flex: 1; min-height: 100vh; }

        /* Left panel */
        .ac-left { background: linear-gradient(135deg, rgba(0,20,50,.82) 0%, rgba(0,36,70,.75) 100%), url('/assets/img/new-banner.jpg') center center / cover no-repeat; padding: 60px 52px; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden; }
        .ac-left::before { content: ''; position: absolute; width: 500px; height: 500px; background: radial-gradient(circle, rgba(201,168,76,.12) 0%, transparent 70%); bottom: -150px; left: -150px; border-radius: 50%; }
        .ac-left-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(201,168,76,.15); border: 1px solid rgba(201,168,76,.3); color: var(--gold); font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; padding: 6px 14px; border-radius: 20px; width: fit-content; margin-bottom: 28px; }
        .ac-left h1 { font-size: 36px; font-weight: 800; color: #fff; line-height: 1.2; margin-bottom: 16px; }
        .ac-left h1 span { color: var(--gold); }
        .ac-left p { color: rgba(255,255,255,.55); font-size: 15px; line-height: 1.8; margin-bottom: 40px; }
        .ac-stat-row { display: flex; gap: 32px; }
        .ac-stat { text-align: center; }
        .ac-stat-num { font-size: 28px; font-weight: 800; color: var(--gold); }
        .ac-stat-label { font-size: 11px; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .8px; margin-top: 2px; }

        /* Right panel */
        .ac-right { background: #fff; padding: 0; display: flex; align-items: center; justify-content: center; }
        .ac-right-inner { width: 100%; max-width: 420px; padding: 48px 32px; }
        .ac-right h2 { font-size: 26px; font-weight: 800; color: var(--dark); margin-bottom: 6px; }
        .ac-right .ac-sub { font-size: 14px; color: #888; margin-bottom: 32px; }

        /* Social buttons */
        .ac-social { display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; }
        .ac-social-btn { display: flex; align-items: center; gap: 12px; padding: 12px 20px; border: 1.5px solid var(--bdr); border-radius: 50px; background: #fff; font-size: 14px; font-weight: 500; color: #333; cursor: pointer; transition: border-color .2s, box-shadow .2s; text-decoration: none; }
        .ac-social-btn:hover { border-color: #aaa; box-shadow: 0 2px 8px rgba(0,0,0,.06); color: #333; }
        .ac-social-btn img { width: 20px; height: 20px; object-fit: contain; }
        .ac-social-btn .ac-icon { width: 20px; text-align: center; }

        .ac-divider { display: flex; align-items: center; gap: 12px; margin: 20px 0 24px; color: #bbb; font-size: 12px; font-weight: 600; letter-spacing: .5px; }
        .ac-divider::before, .ac-divider::after { content: ''; flex: 1; height: 1px; background: var(--bdr); }

        .ac-form-label { font-size: 12px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; display: block; }
        .ac-form-control { width: 100%; padding: 12px 14px; border: 1.5px solid var(--bdr); border-radius: 8px; font-size: 14px; color: var(--dark); background: #fff; transition: border-color .2s, box-shadow .2s; outline: none; margin-bottom: 18px; }
        .ac-form-control:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,36,70,.08); }

        .ac-flex-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; font-size: 13px; }
        .ac-flex-row label { display: flex; align-items: center; gap: 8px; color: #555; cursor: pointer; }
        .ac-flex-row a { color: var(--navy); font-weight: 600; text-decoration: none; }
        .ac-flex-row a:hover { text-decoration: underline; }

        .ac-btn-primary { width: 100%; padding: 14px; background: var(--navy); color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background .2s; }
        .ac-btn-primary:hover { background: #003a7a; }

        .ac-alert-error { background: #fff5f5; border: 1px solid #fcc; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #c00; display: flex; align-items: center; gap: 10px; }

        .ac-footer { text-align: center; margin-top: 28px; font-size: 13px; color: #999; }
        .ac-footer a { color: var(--navy); font-weight: 600; text-decoration: none; }

        .pw-toggle { position: relative; }
        .pw-toggle .ac-form-control { padding-right: 44px; }
        .pw-eye { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #aaa; font-size: 14px; margin-top: -9px; }

        @media (max-width: 900px) {
            .ac-wrap { grid-template-columns: 1fr; }
            .ac-left { display: none; }
            .ac-right { padding: 32px 20px; }
        }
    </style>
</head>
<body>

<div class="ac-topbar">
    <a href="index.php">
        <img src="/assets/img/logo-light.png" alt="Wynnston" onerror="this.style.display='none';this.nextSibling.style.display='block'">
        <span style="display:none;color:#fff;font-weight:800;font-size:18px;letter-spacing:1px;">WYNNSTON</span>
    </a>
    <span class="ac-topbar-link">New developer? <a href="create-account.php">Create account →</a></span>
</div>

<div class="ac-wrap">

    <!-- LEFT -->
    <div class="ac-left">
        <div class="ac-left-badge"><i class="fas fa-gem"></i> Developer Portal</div>
        <h1>Welcome <span>Back</span></h1>
        <p>Sign in to manage your listings, track buyer inquiries, and update your developments on Wynnston Concierge.</p>
        <div class="ac-stat-row">
            <div class="ac-stat">
                <div class="ac-stat-num">3,200+</div>
                <div class="ac-stat-label">Active Listings</div>
            </div>
            <div class="ac-stat">
                <div class="ac-stat-num">100%</div>
                <div class="ac-stat-label">Metro Vancouver</div>
            </div>
            <div class="ac-stat">
                <div class="ac-stat-num">24/7</div>
                <div class="ac-stat-label">Buyer Access</div>
            </div>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="ac-right">
        <div class="ac-right-inner">
            <h2>Sign In</h2>
            <p class="ac-sub">Access your developer dashboard.</p>

            <!-- Social login — available after domain goes live -->
            <div style="background:#f9f9f9;border:1px solid #eee;border-radius:10px;padding:12px 16px;margin-bottom:24px;font-size:13px;color:#888;display:flex;align-items:center;gap:10px;">
                <i class="fas fa-lock" style="color:#bbb;"></i>
                Google &amp; Apple sign-in will be available once the site moves to its permanent domain.
            </div>

            <div class="ac-divider">sign in with email</div>

            <?php if (($_GET['reason'] ?? '') === 'timeout'): ?>
            <div class="ac-alert-error" style="background:#fff8e1;border-color:#ffe082;color:#7a5800;">
                <i class="fas fa-clock"></i> You were automatically signed out after 2 hours of inactivity.
            </div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="ac-alert-error"><i class="fas fa-exclamation-circle"></i><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">

                <label class="ac-form-label">Email Address</label>
                <input type="email" name="email" class="ac-form-control" placeholder="you@company.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autofocus required>

                <label class="ac-form-label">Password</label>
                <div class="pw-toggle">
                    <input type="password" name="password" id="pw" class="ac-form-control" placeholder="••••••••" required>
                    <button type="button" class="pw-eye" onclick="togglePw()"><i class="fas fa-eye" id="pw-icon"></i></button>
                </div>

                <div class="ac-flex-row">
                    <label><input type="checkbox" name="remember"> Remember me</label>
                    <a href="forgot-password.php">Forgot password?</a>
                </div>

                <button type="submit" class="ac-btn-primary">Sign In <i class="fas fa-arrow-right ms-2"></i></button>
            </form>

            <div class="ac-footer">
                Don't have an account? <a href="create-account.php">Create one →</a>
            </div>
        </div>
    </div>

</div>

<script>
function togglePw() {
    var pw = document.getElementById('pw');
    var ic = document.getElementById('pw-icon');
    if (pw.type === 'password') { pw.type = 'text'; ic.className = 'fas fa-eye-slash'; }
    else { pw.type = 'password'; ic.className = 'fas fa-eye'; }
}
</script>
</body>
</html>