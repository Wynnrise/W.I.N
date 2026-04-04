<?php
// dashboard.php — redirect shim
// Old links pointing to dashboard.php now land here.
// Send developers to their portal; everyone else to login.
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['dev_id'])) {
    header('Location: developer-dashboard.php');
} else {
    header('Location: log-in.php');
}
exit;