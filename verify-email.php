<?php
// ============================================================
//  verify-email.php  —  Confirms developer email address
// ============================================================
require_once __DIR__ . '/dev-auth.php';

$token   = trim($_GET['token'] ?? '');
$result  = 'invalid'; // invalid | already | success

if ($token) {
    $s = $pdo->prepare("SELECT id, full_name, email_verified FROM developers WHERE verify_token = ? LIMIT 1");
    $s->execute([$token]);
    $dev = $s->fetch(PDO::FETCH_ASSOC);

    if ($dev) {
        if ($dev['email_verified']) {
            $result = 'already';
        } else {
            $pdo->prepare("UPDATE developers SET email_verified = 1, verify_token = NULL WHERE id = ?")
                ->execute([$dev['id']]);
            $result = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verification — Wynnston Concierge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f9f6f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { background: #fff; border-radius: 16px; padding: 48px 40px; max-width: 440px; width: 100%; box-shadow: 0 8px 40px rgba(0,0,0,.08); text-align: center; }
        .icon { width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 28px; }
        h2 { font-size: 22px; font-weight: 800; color: #0d0d1a; margin-bottom: 10px; }
        p { font-size: 14px; color: #888; line-height: 1.7; margin-bottom: 24px; }
        .btn-navy { display: inline-block; padding: 12px 28px; background: #002446; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; }
        .btn-navy:hover { background: #003a7a; color: #fff; }
    </style>
</head>
<body>
<div class="card">

    <?php if ($result === 'success'): ?>
        <div class="icon" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-envelope-circle-check"></i></div>
        <h2>Email Verified!</h2>
        <p>Your email address has been confirmed. Your account is now pending approval by the Wynnston team — you'll receive an email once it's approved.</p>
        <a href="log-in.php" class="btn-navy">Sign In →</a>

    <?php elseif ($result === 'already'): ?>
        <div class="icon" style="background:#eff6ff;color:#1d4ed8;"><i class="fas fa-circle-check"></i></div>
        <h2>Already Verified</h2>
        <p>Your email address was already verified. You can sign in to your developer account.</p>
        <a href="log-in.php" class="btn-navy">Sign In →</a>

    <?php else: ?>
        <div class="icon" style="background:#fff5f5;color:#dc2626;"><i class="fas fa-link-slash"></i></div>
        <h2>Invalid Link</h2>
        <p>This verification link is invalid or has already been used. If you're having trouble, please contact us at <a href="mailto:info@wynston.ca" style="color:#002446;">info@wynston.ca</a>.</p>
        <a href="create-account.php" class="btn-navy">Back to Sign Up</a>
    <?php endif; ?>

</div>
</body>
</html>