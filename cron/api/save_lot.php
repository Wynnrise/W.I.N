<?php
// api/save_lot.php
// POST: save or unsave a lot for the logged-in developer
// Body: { pid, action? }
//   action = 'toggle'  (default) — saves if not saved, unsaves if saved
//   action = 'unsave'            — always removes (used by dashboard Remove button)

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

$input  = json_decode(file_get_contents('php://input'), true);
$pid    = trim($input['pid']    ?? '');
$action = trim($input['action'] ?? 'toggle');
$dev_id = (int)$_SESSION['dev_id'];

if (!$pid) {
    echo json_encode(['success' => false, 'error' => 'PID required']);
    exit;
}

// Check if already saved
$stmt = $pdo->prepare("SELECT id FROM saved_lots WHERE developer_id = ? AND pid = ? LIMIT 1");
$stmt->execute([$dev_id, $pid]);
$existing = $stmt->fetchColumn();

if ($action === 'unsave') {
    // Always remove
    $pdo->prepare("DELETE FROM saved_lots WHERE developer_id = ? AND pid = ?")->execute([$dev_id, $pid]);
    echo json_encode(['success' => true, 'saved' => false, 'message' => 'Lot removed from saved list']);
    exit;
}

// Toggle
if ($existing) {
    // Already saved — unsave it
    $pdo->prepare("DELETE FROM saved_lots WHERE developer_id = ? AND pid = ?")->execute([$dev_id, $pid]);
    echo json_encode(['success' => true, 'saved' => false, 'message' => 'Lot removed from saved list']);
} else {
    // Not saved — get address from plex_properties
    $stmt = $pdo->prepare("SELECT address FROM plex_properties WHERE pid = ? LIMIT 1");
    $stmt->execute([$pid]);
    $address = $stmt->fetchColumn() ?: '';

    $pdo->prepare("
        INSERT INTO saved_lots (developer_id, pid, address, saved_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE saved_at = NOW()
    ")->execute([$dev_id, $pid, $address]);

    echo json_encode(['success' => true, 'saved' => true, 'message' => 'Lot saved to Project Planner']);
}