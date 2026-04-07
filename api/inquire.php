<?php
// api/inquire.php
// POST: create acquisition request and fire alert email to Tam
// Body: { pid, message: '' }

session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'cookie_domain'   => 'wynston.ca',
    'cookie_samesite' => 'Lax',
]);

header('Content-Type: application/json');
header('Cache-Control: no-store');

require __DIR__ . '/../dev-auth.php';

if (!isset($_SESSION['dev_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$host = 'localhost';
$db   = 'u990588858_Property';
$user = 'u990588858_Multiplex';
$pass = 'Concac1979$';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

$input   = json_decode(file_get_contents('php://input'), true);
$pid     = trim($input['pid'] ?? '');
$message = trim($input['message'] ?? '');
$dev_id  = (int) $_SESSION['dev_id'];

if (!$pid) {
    echo json_encode(['success' => false, 'error' => 'PID required']);
    exit;
}

// Get lot data
$stmt = $pdo->prepare("SELECT address, lot_width_m, lot_area_sqm, neighbourhood_slug, lat, lng FROM plex_properties WHERE pid = ? LIMIT 1");
$stmt->execute([$pid]);
$lot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lot) {
    echo json_encode(['success' => false, 'error' => 'Lot not found']);
    exit;
}

// Get developer info
$stmt = $pdo->prepare("SELECT full_name, email FROM developers WHERE id = ? LIMIT 1");
$stmt->execute([$dev_id]);
$dev = $stmt->fetch(PDO::FETCH_ASSOC);

// Check for existing open request
$stmt = $pdo->prepare("
    SELECT id FROM acquisition_requests
    WHERE developer_id = ? AND pid = ? AND status NOT IN ('closed')
    LIMIT 1
");
$stmt->execute([$dev_id, $pid]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo json_encode([
        'success' => true,
        'already_exists' => true,
        'message' => 'You already have an open inquiry on this lot. Our team will be in touch.'
    ]);
    exit;
}

// Create acquisition request
$stmt = $pdo->prepare("
    INSERT INTO acquisition_requests (developer_id, pid, address, status, requested_at, updated_at)
    VALUES (?, ?, ?, 'under_review', NOW(), NOW())
");
$stmt->execute([$dev_id, $pid, $lot['address']]);
$request_id = $pdo->lastInsertId();

// Send alert email to Tam via PHPMailer
$email_sent = false;
try {
    require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/../PHPMailer/src/SMTP.php';
    require __DIR__ . '/../PHPMailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply@wynston.ca';
    $mail->Password   = 'Concac1979$';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('noreply@wynston.ca', 'Wynston W.I.N');
    $mail->addAddress('tam@wynston.ca', 'Tam Nguyen');
    $mail->isHTML(true);

    $width_ft = $lot['lot_width_m'] > 0 ? round($lot['lot_width_m'] / 0.3048, 1) . 'ft' : 'N/A';
    $area_sqft = $lot['lot_area_sqm'] > 0 ? number_format($lot['lot_area_sqm'] * 10.7639) . ' sqft' : 'N/A';

    $mail->Subject = '🔔 Acquisition Inquiry — ' . $lot['address'];
    $mail->Body = '
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:Arial,sans-serif;background:#f9f6f0;margin:0;padding:24px;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
  <div style="background:#002446;padding:24px 32px;">
    <h1 style="color:#c9a84c;margin:0;font-size:20px;">🔔 New Acquisition Inquiry</h1>
    <p style="color:#fff;margin:8px 0 0;font-size:14px;">Wynston Intelligent Navigator</p>
  </div>
  <div style="padding:32px;">
    <h2 style="color:#002446;margin:0 0 16px;">' . htmlspecialchars($lot['address']) . '</h2>
    <table style="width:100%;border-collapse:collapse;margin-bottom:24px;">
      <tr style="background:#f9f6f0;">
        <td style="padding:10px 14px;font-weight:bold;color:#002446;width:40%;">PID</td>
        <td style="padding:10px 14px;color:#333;">' . htmlspecialchars($pid) . '</td>
      </tr>
      <tr>
        <td style="padding:10px 14px;font-weight:bold;color:#002446;">Neighbourhood</td>
        <td style="padding:10px 14px;color:#333;">' . htmlspecialchars($lot['neighbourhood_slug']) . '</td>
      </tr>
      <tr style="background:#f9f6f0;">
        <td style="padding:10px 14px;font-weight:bold;color:#002446;">Lot Width</td>
        <td style="padding:10px 14px;color:#333;">' . $width_ft . '</td>
      </tr>
      <tr>
        <td style="padding:10px 14px;font-weight:bold;color:#002446;">Lot Area</td>
        <td style="padding:10px 14px;color:#333;">' . $area_sqft . '</td>
      </tr>
    </table>
    <div style="background:#002446;border-radius:6px;padding:20px 24px;margin-bottom:24px;">
      <h3 style="color:#c9a84c;margin:0 0 8px;font-size:15px;">Builder</h3>
      <p style="color:#fff;margin:0;font-size:16px;font-weight:bold;">' . htmlspecialchars($dev['full_name']) . '</p>
      <p style="color:#c9a84c;margin:4px 0 0;font-size:14px;">' . htmlspecialchars($dev['email']) . '</p>
    </div>
    ' . ($message ? '<div style="background:#fffbf0;border-left:3px solid #c9a84c;padding:16px 20px;margin-bottom:24px;border-radius:0 6px 6px 0;"><p style="color:#002446;margin:0;font-size:14px;font-style:italic;">"' . htmlspecialchars($message) . '"</p></div>' : '') . '
    <p style="text-align:center;margin-top:24px;">
      <a href="https://wynston.ca/plex-map/?pid=' . urlencode($pid) . '" style="background:#c9a84c;color:#002446;text-decoration:none;padding:12px 28px;border-radius:6px;font-weight:bold;font-size:14px;">View on W.I.N Map →</a>
    </p>
    <p style="color:#94a3b8;font-size:12px;margin-top:24px;">Request ID: #' . $request_id . ' | Received: ' . date('F j, Y g:i A') . '</p>
  </div>
</div>
</body>
</html>';

    $mail->AltBody = "New Acquisition Inquiry\n\n"
        . "Address: {$lot['address']}\n"
        . "PID: {$pid}\n"
        . "Builder: {$dev['full_name']} ({$dev['email']})\n"
        . ($message ? "Message: {$message}\n" : '')
        . "\nRequest ID: #{$request_id}";

    $mail->send();
    $email_sent = true;

} catch (Exception $e) {
    // Email failed silently — request still created
    error_log('Wynston inquire.php mailer error: ' . $e->getMessage());
}

echo json_encode([
    'success'    => true,
    'request_id' => $request_id,
    'email_sent' => $email_sent,
    'message'    => 'Your inquiry has been submitted. Our team will contact you within 4 hours.'
]);
