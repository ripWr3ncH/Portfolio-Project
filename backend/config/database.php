<?php
// Database configuration
$host = 'localhost';
$dbname = 'portfolio_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Ensure proper UTF8MB4 charset
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Admin credentials (you should hash the password in production)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Change this password!

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function uploadImage($file, $uploadDir = '../uploads/projects/') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    }
    
    return false;
}

function deleteImage($filename, $uploadDir = '../uploads/projects/') {
    $filePath = $uploadDir . $filename;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Helper function to get project statistics
function getProjectStats() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total projects
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects");
        $stats['total'] = $stmt->fetchColumn();
        
        // Completed projects
        $stmt = $pdo->query("SELECT COUNT(*) as completed FROM projects WHERE status = 'completed'");
        $stats['completed'] = $stmt->fetchColumn();
        
        // Featured projects
        $stmt = $pdo->query("SELECT COUNT(*) as featured FROM projects WHERE is_featured = 1");
        $stats['featured'] = $stmt->fetchColumn();
        
        return $stats;
    } catch (PDOException $e) {
        return ['total' => 0, 'completed' => 0, 'featured' => 0];
    }
}

// Helper function to get education statistics
function getEducationStats() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total education records
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM education");
        $stats['total'] = $stmt->fetchColumn();
        
        // Current education
        $stmt = $pdo->query("SELECT COUNT(*) as current FROM education WHERE is_current = 1");
        $stats['current'] = $stmt->fetchColumn();
        
        return $stats;
    } catch (PDOException $e) {
        return ['total' => 0, 'current' => 0];
    }
}

// Helper function to get recent activities
function getRecentActivities($limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
?>
