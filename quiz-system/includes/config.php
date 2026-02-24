<?php
/**
 * Database Configuration File
 * Online Quiz System
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'quiz_system');

// SMTP Configuration for Email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'Quiz System');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create database connection using mysqli
$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Set charset to utf8mb4
$connection->set_charset("utf8mb4");

/**
 * Helper function to sanitize user input
 */
function sanitize($data) {
    global $connection;
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Helper function to redirect
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Helper function to display alert message
 */
function alert($message, $type = 'success') {
    return "<div class='alert alert-$type'>$message</div>";
}
?>