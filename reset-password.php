<?php
// ============================================================
//  reset-password.php  —  Set new password via token
// ============================================================
require_once __DIR__ . '/dev-auth.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$success = false;

// Validate token
$dev = null;
if ($token) {
    $s = $pdo->prepare("SELECT * FROM developers WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
    $s->execute([$token]);
    $dev = $s->fetch(PDO::FETCH_ASSOC);
}

if (!$dev && !$success) {
    $invalid = true;
} else {
    $invalid = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dev) {
    $pw  = $_POST['password']  ?? '';
    $pw2 = $_POST['password2'] ?? '';

    if (strlen($pw) < 8)   $error = 'Password must be at least 8 characters.';
    elseif ($pw !== $pw2)  $error = 'Passwords do not match.';
    else {
        $hash = password_hash($pw, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE developers SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?")
            ->execute([$hash, $dev['id']]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="shortcut icon" href="/assets/img/favicon.png">
    <title>Reset Password — Wynnston</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --navy: #002446; --bdr: #e8e4dd; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f9f6f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .rp-card { background: #fff; border-radius: 16px; padding: 48px 40px; max-width: 440px; width: 100%; box-shadow: 0 8px 40px rgba(0,0,0,.08); text-align: center; }
        .rp-icon { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; }
        .rp-icon.ok { background: #f0f4ff; } .rp-icon.ok i { color: var(--navy); font-size: 24px; }
        .rp-icon.success { background: #f0faf4; } .rp-icon.success i { color: #198754; font-size: 24px; }
        .rp-icon.danger { background: #fff5f5; } .rp-icon.danger i { color: #dc3545; font-size: 24px; }
        h2 { font-size: 22px; font-weight: 800; color: #0d0d1a; margin-bottom: 8px; }
        p { font-size: 14px; color: #888; margin-bottom: 28px; line-height: 1.7; }
        .rp-label { font-size: 12px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .6px; display: block; text-align: left; margin-bottom: 6px; }
        .rp-input { width: 100%; padding: 12px 14px; border: 1.5px solid var(--bdr); border-radius: 8px; font-size: 14px; outline: none; margin-bottom: 18px; transition: border-color .2s; }
        .rp-input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,36,70,.08); }
        .rp-btn { width: 100%; padding: 13px; background: var(--navy); color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background .2s; }
        .rp-btn:hover { background: #003a7a; }
        .rp-error { background: #fff5f5; border: 1px solid #fcc; border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #c00; margin-bottom: 16px; text-align: left; }
        .pw-strength { height: 4px; border-radius: 2px; background: #eee; margin-top: -14px; margin-bottom: 18px; overflow: hidden; }
        .pw-strength-bar { height: 100%; width: 0; transition: width .3s, background .3s; border-radius: 2px; }
    </style>
</head>
<body>
<div class="rp-card">

    <?php if ($invalid): ?>
        <div class="rp-icon danger"><i class="fas fa-link-slash"></i></div>
        <h2>Link Expired</h2>
        <p>This password reset link is invalid or has expired. Reset links are valid for 1 hour.</p>
        <a href="forgot-password.php" class="rp-btn" style="display:block;text-decoration:none;line-height:1.5;">Request New Link</a>

    <?php elseif ($success): ?>
        <div class="rp-icon success"><i class="fas fa-check-circle"></i></div>
        <h2>Password Updated!</h2>
        <p>Your password has been successfully reset. You can now sign in with your new password.</p>
        <a href="log-in.php" class="rp-btn" style="display:block;text-decoration:none;line-height:1.5;">Sign In Now →</a>

    <?php else: ?>
        <div class="rp-icon ok"><i class="fas fa-key"></i></div>
        <h2>Set New Password</h2>
        <p>Hi <?= htmlspecialchars($dev['full_name']) ?> — choose a strong new password for your Wynnston developer account.</p>
        <?php if ($error): ?><div class="rp-error"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <label class="rp-label">New Password</label>
            <input type="password" name="password" id="pw" class="rp-input" placeholder="Min. 8 characters" oninput="checkStrength(this.value)" required>
            <div class="pw-strength"><div class="pw-strength-bar" id="pw-bar"></div></div>
            <label class="rp-label">Confirm Password</label>
            <input type="password" name="password2" class="rp-input" placeholder="Repeat password" required>
            <button type="submit" class="rp-btn">Update Password <i class="fas fa-shield-halved ms-2"></i></button>
        </form>
    <?php endif; ?>

</div>
<script>
function checkStrength(val) {
    var bar = document.getElementById('pw-bar');
    var score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    var colors = ['#dc3545','#fd7e14','#ffc107','#20c997','#198754'];
    var widths  = ['20%','40%','60%','80%','100%'];
    bar.style.width      = widths[score-1]  || '0';
    bar.style.background = colors[score-1] || '#eee';
}
</script>
</body>
</html>