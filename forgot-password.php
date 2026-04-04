<?php
// ============================================================
//  forgot-password.php  —  Request password reset
// ============================================================
require_once __DIR__ . '/dev-auth.php';

$sent  = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $s = $pdo->prepare("SELECT id, full_name FROM developers WHERE email = ? LIMIT 1");
        $s->execute([$email]);
        $dev = $s->fetch(PDO::FETCH_ASSOC);

        // Always show success (don't reveal if email exists)
        if ($dev) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $pdo->prepare("UPDATE developers SET reset_token = ?, reset_expires = ? WHERE id = ?")
                ->execute([$token, $expires, $dev['id']]);

            $reset_link = 'https://' . $_SERVER['HTTP_HOST'] . '/reset-password.php?token=' . $token;

            // Send email
            $to      = $email;
            $subject = 'Reset Your Wynnston Developer Password';
            $message = "Hi {$dev['full_name']},\n\nClick the link below to reset your password. This link expires in 1 hour.\n\n{$reset_link}\n\nIf you didn't request this, ignore this email.\n\n— Wynnston Concierge Team";
            $headers = "From: noreply@wynnstonconcierge.com\r\nReply-To: support@wynnstonconcierge.com";
            mail($to, $subject, $message, $headers);
        }
        $sent = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password — Wynnston</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --navy: #002446; --gold: #c9a84c; --bdr: #e8e4dd; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f9f6f0; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; }
        .fp-card { background: #fff; border-radius: 16px; padding: 48px 40px; max-width: 440px; width: 100%; box-shadow: 0 8px 40px rgba(0,0,0,.08); text-align: center; }
        .fp-icon { width: 64px; height: 64px; background: #f0f4ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; }
        .fp-icon i { font-size: 24px; color: var(--navy); }
        .fp-card h2 { font-size: 22px; font-weight: 800; color: #0d0d1a; margin-bottom: 8px; }
        .fp-card p { font-size: 14px; color: #888; margin-bottom: 28px; line-height: 1.7; }
        .fp-label { font-size: 12px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .6px; display: block; text-align: left; margin-bottom: 6px; }
        .fp-input { width: 100%; padding: 12px 14px; border: 1.5px solid var(--bdr); border-radius: 8px; font-size: 14px; outline: none; margin-bottom: 20px; transition: border-color .2s; }
        .fp-input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,36,70,.08); }
        .fp-btn { width: 100%; padding: 13px; background: var(--navy); color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background .2s; }
        .fp-btn:hover { background: #003a7a; }
        .fp-back { display: inline-block; margin-top: 20px; font-size: 13px; color: #888; text-decoration: none; }
        .fp-back:hover { color: var(--navy); }
        .fp-success { color: #1a6b3c; }
        .fp-success .fp-icon { background: #f0faf4; }
        .fp-success .fp-icon i { color: #198754; }
        .fp-error { background: #fff5f5; border: 1px solid #fcc; border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #c00; margin-bottom: 16px; text-align: left; }
    </style>
</head>
<body>

<div class="fp-card <?= $sent ? 'fp-success' : '' ?>">
    <?php if ($sent): ?>
        <div class="fp-icon"><i class="fas fa-envelope-circle-check"></i></div>
        <h2>Check Your Email</h2>
        <p>If an account exists for that email address, we've sent a password reset link. Check your inbox — it expires in 1 hour.</p>
        <a href="log-in.php" class="fp-btn" style="display:block;text-decoration:none;line-height:1.5;">Back to Sign In</a>
    <?php else: ?>
        <div class="fp-icon"><i class="fas fa-lock"></i></div>
        <h2>Forgot Password?</h2>
        <p>Enter your developer account email and we'll send you a link to reset your password.</p>
        <?php if ($error): ?><div class="fp-error"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <label class="fp-label">Email Address</label>
            <input type="email" name="email" class="fp-input" placeholder="you@company.com"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autofocus required>
            <button type="submit" class="fp-btn">Send Reset Link <i class="fas fa-paper-plane ms-2"></i></button>
        </form>
        <a href="log-in.php" class="fp-back"><i class="fas fa-arrow-left me-1"></i>Back to Sign In</a>
    <?php endif; ?>
</div>

</body>
</html>