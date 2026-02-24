<?php
/**
 * Password Reset Table Setup Script
 * Run this script once to create the password_resets table
 * DELETE THIS FILE AFTER USE!
 */

require_once 'includes/config.php';

// SQL to create password_resets table
$sql = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($connection->query($sql)) {
    echo "<h2 style='color: green; font-family: Arial, sans-serif;'>✓ password_resets table created successfully!</h2>";
    echo "<p style='font-family: Arial, sans-serif;'>The password reset feature is now ready.</p>";
    echo "<p style='color: red; font-family: Arial, sans-serif;'><strong>IMPORTANT: Delete this file (setup_password_reset.php) immediately for security!</strong></p>";
    echo "<a href='login.php' style='font-family: Arial, sans-serif;'>Go to Login Page</a>";
} else {
    echo "<h2 style='color: red; font-family: Arial, sans-serif;'>✗ Error creating table: " . $connection->error . "</h2>";
}
?>