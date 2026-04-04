<?php
// ============================================================
//  dev-logout.php  —  Developer sign-out
// ============================================================
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="4;url=index.php">
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="shortcut icon" href="/assets/img/favicon.png">
    <title>Signed Out — Wynnston Concierge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --navy: #002446; --gold: #c9a84c; --cream: #f9f6f0; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--cream);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .signout-card {
            background: #fff;
            border-radius: 16px;
            padding: 48px 40px;
            text-align: center;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 8px 40px rgba(0,36,70,.1);
            border: 1px solid #e8e4dd;
        }
        .signout-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 28px;
            color: #16a34a;
        }
        .signout-card h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--navy);
            margin: 0 0 10px;
        }
        .signout-card p {
            font-size: 14px;
            color: #888;
            margin: 0 0 28px;
            line-height: 1.6;
        }
        .redirect-bar {
            height: 4px;
            background: #e8e4dd;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .redirect-bar-fill {
            height: 100%;
            background: var(--gold);
            border-radius: 4px;
            animation: fillBar 4s linear forwards;
        }
        @keyframes fillBar {
            from { width: 0%; }
            to   { width: 100%; }
        }
        .redirect-note {
            font-size: 12px;
            color: #aaa;
            margin-bottom: 24px;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--navy);
            color: #fff;
            text-decoration: none;
            padding: 11px 24px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            transition: background .2s;
        }
        .btn-home:hover { background: #0065ff; color: #fff; }
        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            color: var(--navy);
            text-decoration: none;
            padding: 11px 24px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            border: 1.5px solid var(--navy);
            transition: all .2s;
            margin-left: 10px;
        }
        .btn-login:hover { background: var(--navy); color: #fff; }
    </style>
</head>
<body>

<div class="signout-card">
    <div class="signout-icon">
        <i class="fas fa-check"></i>
    </div>
    <h2>You've been signed out</h2>
    <p>You have successfully signed out of your Wynnston Concierge developer account. Your session has been cleared.</p>

    <div class="redirect-bar">
        <div class="redirect-bar-fill"></div>
    </div>
    <p class="redirect-note">Redirecting to homepage in a few seconds…</p>

    <div>
        <a href="index.php" class="btn-home"><i class="fas fa-home"></i> Go to Homepage</a>
        <a href="log-in.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Sign Back In</a>
    </div>
</div>

<script>
    // Redirect after 4 seconds
    setTimeout(function() {
        window.location.href = 'index.php';
    }, 4000);
</script>

</body>
</html>