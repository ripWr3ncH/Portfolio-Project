<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: login.php?message=logged_out');
exit;
?>
