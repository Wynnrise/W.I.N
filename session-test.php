<?php
require_once __DIR__ . '/dev-auth.php';
echo json_encode([
    'dev_id'      => $_SESSION['dev_id'] ?? 'NOT SET',
    'dev_name'    => $_SESSION['dev_name'] ?? 'NOT SET',
    'last_active' => $_SESSION['dev_last_active'] ?? 'NOT SET',
    'session_id'  => session_id(),
]);