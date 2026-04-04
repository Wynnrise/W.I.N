<?php
// notify-subscribe.php
// Saves buyer notification requests — called via fetch() from property pages
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$base_dir = __DIR__ . '/Base';
require_once "$base_dir/db.php"; // gives us $pdo

$email       = trim($_POST['email']       ?? '');
$name        = trim($_POST['name']        ?? '');
$property_id = intval($_POST['property_id'] ?? 0);
$source      = trim($_POST['source']      ?? 'unknown');

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid email']);
    exit;
}

try {
    // Create table if it doesn't exist yet
    $pdo->exec("CREATE TABLE IF NOT EXISTS notify_subscribers (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        email        VARCHAR(255) NOT NULL,
        name         VARCHAR(255) DEFAULT '',
        property_id  INT DEFAULT 0,
        source       VARCHAR(100) DEFAULT '',
        created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_property (property_id)
    )");

    // Upsert — don't create duplicates for same email + property combo
    $check = $pdo->prepare("SELECT id FROM notify_subscribers WHERE email = ? AND property_id = ? LIMIT 1");
    $check->execute([$email, $property_id]);

    if (!$check->fetch()) {
        $pdo->prepare("INSERT INTO notify_subscribers (email, name, property_id, source) VALUES (?, ?, ?, ?)")
            ->execute([$email, $name, $property_id, $source]);
    }

    // Optional: notify Tam by email
    $property_info = '';
    if ($property_id > 0) {
        try {
            $prop = $pdo->prepare("SELECT address FROM multi_2025 WHERE id = ? LIMIT 1");
            $prop->execute([$property_id]);
            $row = $prop->fetch(PDO::FETCH_ASSOC);
            if ($row) $property_info = $row['address'];
        } catch (Exception $e) {
            // ddf_listings — try other table
            try {
                $prop2 = $pdo->prepare("SELECT address FROM ddf_listings WHERE id = ? LIMIT 1");
                $prop2->execute([$property_id]);
                $row2 = $prop2->fetch(PDO::FETCH_ASSOC);
                if ($row2) $property_info = $row2['address'];
            } catch (Exception $e2) {}
        }
    }

    $subject = "New Buyer Notification Request — Wynston";
    $body  = "A buyer wants to be notified about a property on Wynston.\n\n";
    $body .= "Email:    {$email}\n";
    $body .= "Name:     " . ($name ?: '—') . "\n";
    $body .= "Property: " . ($property_info ?: "ID #{$property_id}") . "\n";
    $body .= "Source:   {$source}\n";
    $body .= "Time:     " . date('Y-m-d H:i:s') . "\n\n";
    $body .= "— Wynston Notification System";

    $from = "From: noreply@wynston.ca\r\nX-Mailer: PHP/" . phpversion();
    mail('sold@tamwynn.ca', $subject, $body, $from);

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
?>