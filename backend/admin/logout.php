<?php
require_once '../config/database.php';

// Clear remember me cookie if it exists
if (isset($_COOKIE['admin_remember_token'])) {
    clearRememberMeCookie();
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: login.php?message=logged_out');
exit;
?>
