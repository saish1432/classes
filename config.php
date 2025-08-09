<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'u261459251_online');
define('DB_PASSWORD', 'Vishraj@9884');
define('DB_NAME', 'u261459251_classes');

// Site Configuration
define('SITE_URL', 'http://localhost');
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Database Connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper Functions
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Start session
session_start();
?>