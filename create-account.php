<?php
// ============================================================
//  create-account.php  —  Developer Sign Up
// ============================================================
require_once __DIR__ . '/dev-auth.php';

// Already logged in → go to dashboard
if (dev_logged_in()) {
    header('Location: dashboard.php'); exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name    = trim($_POST['full_name']    ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $email        = trim($_POST['email']        ?? '');
    $phone        = trim($_POST['phone']        ?? '');
    $website      = trim($_POST['website']      ?? '');
    $projects     = trim($_POST['projects']     ?? '');
    $password     = $_POST['password']          ?? '';
    $password2    = $_POST['password2']         ?? '';
    $first_name   = explode(' ', $full_name)[0];
    $allowed_types = ['builder','investor','realtor','broker'];
    $user_type    = in_array($_POST['user_type'] ?? '', $allowed_types) ? $_POST['user_type'] : 'builder';

    // Validate
    if (!$full_name)    $errors[] = 'Full name is required.';
    if (!$company_name) $errors[] = 'Company name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $password2)  $errors[] = 'Passwords do not match.';
    if (!isset($_POST['gdpr']))    $errors[] = 'Please accept the terms to continue.';
    if (!in_array($user_type, $allowed_types)) $errors[] = 'Please select your account type.';

    if (empty($errors)) {
        // Check duplicate email
        $check = $pdo->prepare("SELECT id FROM developers WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists. <a href="log-in.php" class="text-warning">Log in instead?</a>';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // Create developer folder
            $folder_slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $company_name));
            $dev_folder  = __DIR__ . '/uploads/developers/' . $folder_slug;
            if (!is_dir($dev_folder)) mkdir($dev_folder, 0755, true);

            // Generate email verification token
            $verify_token = bin2hex(random_bytes(32));

            $pdo->prepare("INSERT INTO developers
                (full_name, company_name, email, phone, website, projects, password_hash, status, email_verified, verify_token, user_type)
                VALUES (?,?,?,?,?,?,?,'pending',0,?,?)")
                ->execute([$full_name, $company_name, $email, $phone, $website, $projects, $hash, $verify_token, $user_type]);

            $site_url   = 'https://' . $_SERVER['HTTP_HOST'];
            $admin_url  = $site_url . '/admin.php?tab=developers';
            $verify_url = $site_url . '/verify-email.php?token=' . $verify_token;
            $from       = "From: noreply@wynnstonconcierge.com\r\nX-Mailer: PHP/" . phpversion();

            // ── Email 1: Notify sold@tamwynn.ca ──────────────────
            $ut_label = ucfirst($user_type);
            $body1  = "New developer signup on Wynnston Concierge — pending your approval.\n\n";
            $body1 .= "Name:      {$full_name}\n";
            $body1 .= "Company:   {$company_name}\n";
            $body1 .= "Email:     {$email}\n";
            $body1 .= "Phone:     " . ($phone ?: '—') . "\n";
            $body1 .= "Website:   " . ($website ?: '—') . "\n";
            $body1 .= "User Type: {$ut_label}\n\n";
            $body1 .= "Projects:\n" . ($projects ?: '—') . "\n\n";
            $body1 .= "──────────────────────────────────\n";
            $body1 .= "Approve or reject:\n{$admin_url}\n\n";
            $body1 .= "— Wynnston Concierge System";
            mail('sold@tamwynn.ca', "🔔 New Developer Signup — {$company_name}", $body1,
                $from . "\r\nReply-To: {$email}");

            // ── Email 2: Notify info@wynston.ca ──────────────────
            mail('info@wynston.ca', "🔔 New Developer Signup — {$company_name}", $body1,
                $from . "\r\nReply-To: {$email}");

            // ── Email 3: Welcome email to developer ───────────────
            $welcome  = "Hi {$full_name},\n\n";
            $welcome .= "Welcome to Wynnston Concierge — Vancouver's premier pre-sale real estate platform.\n\n";
            $welcome .= "We've received your developer account application for {$company_name}.\n\n";
            $welcome .= "What happens next:\n";
            $welcome .= "  1. Verify your email address (see your second email)\n";
            $welcome .= "  2. Our team will review your account — usually within 1 business day\n";
            $welcome .= "  3. You'll receive a confirmation email once approved\n";
            $welcome .= "  4. Log in and start submitting your listings\n\n";
            $welcome .= "Your login: {$site_url}/log-in.php\n\n";
            $welcome .= "Questions? Contact us at info@wynston.ca\n\n";
            $welcome .= "— The Wynnston Concierge Team\n";
            $welcome .= "wynnstonconcierge.com";
            mail($email, "Welcome to Wynnston Concierge, {$first_name}!", $welcome,
                $from . "\r\nReply-To: info@wynston.ca");

            // ── Email 4: Verification email to developer ──────────
            $verify  = "Hi {$first_name},\n\n";
            $verify .= "Please verify your email address to complete your Wynnston developer registration.\n\n";
            $verify .= "Click the link below to verify:\n";
            $verify .= "{$verify_url}\n\n";
            $verify .= "This link does not expire. If you didn't create this account, ignore this email.\n\n";
            $verify .= "— The Wynnston Concierge Team";
            mail($email, "Verify Your Email — Wynnston Concierge", $verify,
                $from . "\r\nReply-To: info@wynston.ca");

            $success = true;
        }
    }
}

$base_dir = __DIR__ . '/Base';
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="shortcut icon" href="/assets/img/favicon.png">
    <title>Apply for Access — Wynston W.I.N</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark:   #0d0d1a;
            --navy:   #002446;
            --gold:   #c9a84c;
            --cream:  #f9f6f0;
            --bdr:    #e8e4dd;
            --ss:     'Segoe UI', system-ui, sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--ss);
            background: var(--cream);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top bar ── */
        .ac-topbar {
            background: transparent;
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: absolute;
            top: 0; left: 0; right: 0;
            z-index: 10;
        }
        .ac-topbar a { color: #fff; text-decoration: none; }
        .ac-topbar img { height: 38px; }
        .ac-topbar-link { font-size: 13px; color: #444; }
        .ac-topbar-link a { color: var(--navy); font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }

        /* ── Two-column layout ── */
        .ac-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            flex: 1;
            min-height: 100vh;
        }

        /* ── Left panel ── */
        .ac-left {
            background: linear-gradient(135deg, rgba(0,20,50,.82) 0%, rgba(0,36,70,.75) 100%), url('/assets/img/new-banner.jpg') center center / cover no-repeat;
            padding: 60px 52px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .ac-left::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(201,168,76,.15) 0%, transparent 70%);
            top: -100px; right: -100px;
            border-radius: 50%;
        }
        .ac-left-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(201,168,76,.15);
            border: 1px solid rgba(201,168,76,.3);
            color: var(--gold);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 20px;
            width: fit-content;
            margin-bottom: 28px;
        }
        .ac-left h1 {
            font-size: 36px;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 20px;
        }
        .ac-left h1 span { color: var(--gold); }
        .ac-left p {
            color: rgba(255,255,255,.6);
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 36px;
        }
        .ac-perks { list-style: none; padding: 0; }
        .ac-perks li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: rgba(255,255,255,.75);
            font-size: 14px;
            padding: 10px 0;
        }
        .ac-perks li i { color: var(--gold); margin-top: 2px; flex-shrink: 0; }

        /* ── Right panel ── */
        .ac-right {
            background: #fff;
            padding: 48px 52px;
            overflow-y: auto;
        }
        .ac-right h2 {
            font-size: 26px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 6px;
        }
        .ac-right .ac-sub {
            font-size: 14px;
            color: #888;
            margin-bottom: 32px;
        }

        /* ── Social buttons ── */
        .ac-social { display: flex; flex-direction: column; gap: 10px; margin-bottom: 28px; }
        .ac-social-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            border: 1.5px solid var(--bdr);
            border-radius: 50px;
            background: #fff;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            cursor: pointer;
            transition: border-color .2s, box-shadow .2s;
            text-decoration: none;
        }
        .ac-social-btn:hover { border-color: #aaa; box-shadow: 0 2px 8px rgba(0,0,0,.06); color: #333; }
        .ac-social-btn img { width: 20px; height: 20px; object-fit: contain; }
        .ac-social-btn .ac-icon { width: 20px; text-align: center; font-size: 16px; }

        /* ── Divider ── */
        .ac-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0 24px;
            color: #bbb;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .5px;
        }
        .ac-divider::before, .ac-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--bdr);
        }

        /* ── Form ── */
        .ac-form-label {
            font-size: 12px;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-bottom: 6px;
            display: block;
        }
        .ac-form-control {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--bdr);
            border-radius: 8px;
            font-size: 14px;
            color: var(--dark);
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            margin-bottom: 18px;
        }
        .ac-form-control:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,36,70,.08); }
        .ac-form-control.is-invalid { border-color: #dc3545; }
        textarea.ac-form-control { resize: vertical; min-height: 80px; }

        .ac-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .ac-btn-primary {
            width: 100%;
            padding: 14px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s, transform .1s;
            margin-top: 4px;
        }
        .ac-btn-primary:hover { background: #003a7a; transform: translateY(-1px); }

        /* ── Alerts ── */
        .ac-alert-error {
            background: #fff5f5;
            border: 1px solid #fcc;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #c00;
        }
        .ac-alert-error ul { margin: 0; padding-left: 16px; }
        .ac-alert-success {
            background: #f0faf4;
            border: 1px solid #b3dfc0;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            color: #1a6b3c;
        }
        .ac-alert-success i { font-size: 40px; margin-bottom: 12px; display: block; }
        .ac-alert-success h3 { font-size: 20px; margin-bottom: 8px; }
        .ac-alert-success p { font-size: 14px; color: #555; }

        .ac-section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #aaa;
            margin: 8px 0 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--bdr);
        }

        /* User type cards */
        .ut-card {
            border: 1.5px solid var(--bdr);
            border-radius: 10px;
            padding: 14px;
            text-align: center;
            transition: border-color .15s, background .15s;
            background: #fff;
            height: 100%;
        }
        .ut-card:hover { border-color: #aaa; background: #fafafa; }
        .ut-card.ut-selected { border-color: var(--navy); background: rgba(0,36,70,0.03); box-shadow: 0 0 0 3px rgba(0,36,70,0.08); }
        .ut-card.ut-selected i { color: var(--navy) !important; }

        /* Password strength */
        .pw-strength { height: 4px; border-radius: 2px; background: #eee; margin-top: -14px; margin-bottom: 18px; overflow: hidden; }
        .pw-strength-bar { height: 100%; width: 0; transition: width .3s, background .3s; border-radius: 2px; }

        /* Terms */
        .ac-terms { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #666; margin: 16px 0; }
        .ac-terms input { margin-top: 2px; flex-shrink: 0; }
        .ac-terms a { color: var(--navy); }

        @media (max-width: 900px) {
            .ac-wrap { grid-template-columns: 1fr; }
            .ac-left { display: none; }
            .ac-right { padding: 32px 24px; }
            .ac-row { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
</head>
<body>

<!-- Top bar -->
<div class="ac-topbar">
    <a href="index.php">
        <img src="/assets/img/logo-light.png" alt="Wynnston Concierge" onerror="this.style.display='none';this.nextSibling.style.display='block'">
        <span style="display:none;color:#fff;font-weight:800;font-size:18px;letter-spacing:1px;">WYNNSTON</span>
    </a>
    <span class="ac-topbar-link">Already approved? <a href="log-in.php">Sign in →</a></span>
</div>

<div class="ac-wrap">

    <!-- LEFT panel -->
    <div class="ac-left">
        <div class="ac-left-badge"><i class="fas fa-map-marked-alt"></i> W.I.N Portal</div>
        <h1>Vancouver's <span>Multiplex</span> Intelligence Platform</h1>
        <p>Join builders, investors, realtors, and mortgage brokers using Wynston W.I.N to analyse R1-1 lots, run feasibility pro formas, and move on opportunities faster.</p>
        <ul class="ac-perks">
            <li><i class="fas fa-check"></i> Interactive map of 68,000+ R1-1 zoned lots in Vancouver</li>
            <li><i class="fas fa-check"></i> Full feasibility pro forma — strata, rental, and Outlook tabs</li>
            <li><i class="fas fa-check"></i> Save lots, generate branded PDF reports, share with clients</li>
            <li><i class="fas fa-check"></i> Acquisition pipeline — submit inquiries directly to Wynston</li>
            <li><i class="fas fa-check"></i> Wynston Outlook — 12-month $/sqft forecast by neighbourhood</li>
            <li><i class="fas fa-check"></i> Role-specific dashboard: Builder, Investor, Realtor, Broker</li>
        </ul>
    </div>

    <!-- RIGHT panel -->
    <div class="ac-right">
        <h2>Apply for W.I.N Access</h2>
        <p class="ac-sub">All applications are reviewed by the Wynston team. Approval typically within 1 business day.</p>

        <div style="background:#f9f6f0;border:1px solid #e8e4dd;border-radius:10px;padding:16px 18px;margin-bottom:28px;display:flex;gap:14px;align-items:flex-start;">
            <div style="width:36px;height:36px;background:#002446;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                <i class="fas fa-shield-halved" style="color:#c9a84c;font-size:15px;"></i>
            </div>
            <div>
                <div style="font-size:13px;font-weight:700;color:#002446;margin-bottom:6px;">How access works</div>
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <div style="font-size:12px;color:#555;display:flex;align-items:center;gap:8px;"><span style="width:18px;height:18px;background:#002446;color:#c9a84c;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;">1</span>Complete your professional profile below</div>
                    <div style="font-size:12px;color:#555;display:flex;align-items:center;gap:8px;"><span style="width:18px;height:18px;background:#002446;color:#c9a84c;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;">2</span>Wynston reviews your application — within 1 business day</div>
                    <div style="font-size:12px;color:#555;display:flex;align-items:center;gap:8px;"><span style="width:18px;height:18px;background:#002446;color:#c9a84c;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;">3</span>You receive approval confirmation and full W.I.N access</div>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="ac-alert-success">
            <i class="fas fa-check-circle"></i>
            <h3>Application Submitted</h3>
            <p>Thank you. Your W.I.N access application is under review by the Wynston team. You will receive a confirmation email once approved — typically within 1 business day.</p>
            <p style="margin-top:10px;font-size:12px;color:#888;">Questions? Contact <a href="mailto:info@wynston.ca" style="color:#002446;">info@wynston.ca</a></p>
        </div>

        <?php else: ?>

        <?php if (!empty($errors)): ?>
        <div class="ac-alert-error">
            <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="ac-section-title">I am a...</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:22px;">
                <?php
                $types = [
                    'builder'  => ['fa-hard-hat',          'Builder / Developer',  'Build multiplexes and pre-sale developments'],
                    'investor' => ['fa-chart-line',         'Investor',             'Evaluate ROI, cap rate, and rental returns'],
                    'realtor'  => ['fa-id-badge',           'Realtor',              'Find lots and generate reports for clients'],
                    'broker'   => ['fa-file-invoice-dollar','Mortgage Broker',      'Review NOI and debt coverage for underwriting'],
                ];
                foreach ($types as $val => [$icon, $label, $desc]):
                    $sel = ($_POST['user_type'] ?? 'builder') === $val;
                ?>
                <label style="display:block;cursor:pointer;">
                    <input type="radio" name="user_type" value="<?= $val ?>" <?= $sel ? 'checked' : '' ?> required style="display:none;" class="ut-radio">
                    <div class="ut-card <?= $sel ? 'ut-selected' : '' ?>" data-val="<?= $val ?>">
                        <i class="fas <?= $icon ?>" style="font-size:18px;color:var(--gold);display:block;margin-bottom:6px;"></i>
                        <strong style="font-size:13px;display:block;margin-bottom:3px;"><?= $label ?></strong>
                        <span style="font-size:11px;color:#888;line-height:1.4;"><?= $desc ?></span>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>

            <div class="ac-section-title">Your Information</div>
            <div class="ac-row">
                <div>
                    <label class="ac-form-label">Full Name *</label>
                    <input type="text" name="full_name" class="ac-form-control" placeholder="Jane Smith" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                </div>
                <div>
                    <label class="ac-form-label">Phone</label>
                    <input type="tel" name="phone" class="ac-form-control" placeholder="(604) 000-0000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
            </div>

            <div class="ac-section-title">Company Information</div>
            <label class="ac-form-label">Developer / Company Name *</label>
            <input type="text" name="company_name" class="ac-form-control" placeholder="e.g. Morningstar Homes" value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>" required>

            <label class="ac-form-label">Company Website</label>
            <input type="url" name="website" class="ac-form-control" placeholder="https://www.yourcompany.com" value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">

            <label class="ac-form-label">Current or Upcoming Projects</label>
            <textarea name="projects" class="ac-form-control" placeholder="e.g. The Arch — 42 units in Kitsilano (est. completion 2026)&#10;Maple Ridge Townhomes — Phase 2"><?= htmlspecialchars($_POST['projects'] ?? '') ?></textarea>

            <div class="ac-section-title">Login Credentials</div>
            <label class="ac-form-label">Email Address *</label>
            <input type="email" name="email" class="ac-form-control" placeholder="you@company.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <div class="ac-row">
                <div>
                    <label class="ac-form-label">Password *</label>
                    <input type="password" name="password" id="pw" class="ac-form-control" placeholder="Min. 8 characters" oninput="checkStrength(this.value)" required>
                    <div class="pw-strength"><div class="pw-strength-bar" id="pw-bar"></div></div>
                </div>
                <div>
                    <label class="ac-form-label">Confirm Password *</label>
                    <input type="password" name="password2" class="ac-form-control" placeholder="Repeat password" required>
                </div>
            </div>

            <label class="ac-terms">
                <input type="checkbox" name="gdpr" required>
                <span>I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>. I understand my account requires approval before I can submit listings.</span>
            </label>

            <button type="submit" class="ac-btn-primary">
                Submit Application <i class="fas fa-arrow-right ms-2"></i>
            </button>

        </form>

        <?php endif; ?>

    </div>
</div>

<script>
document.querySelectorAll('.ut-radio').forEach(function(radio) {
    radio.closest('label').addEventListener('click', function() {
        document.querySelectorAll('.ut-card').forEach(c => c.classList.remove('ut-selected'));
        this.querySelector('.ut-card').classList.add('ut-selected');
    });
});

function checkStrength(val) {
    var bar = document.getElementById('pw-bar');
    var score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    var colors = ['#dc3545','#fd7e14','#ffc107','#20c997','#198754'];
    var widths = ['20%','40%','60%','80%','100%'];
    bar.style.width   = widths[score-1] || '0';
    bar.style.background = colors[score-1] || '#eee';
}
</script>
</body>
</html>