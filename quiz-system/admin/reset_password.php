<?php
/**
 * Admin Password Reset Script
 * Run this script once to reset the admin password
 * DELETE THIS FILE AFTER USE!
 */

require_once '../includes/config.php';

// New password to set
$newPassword = 'admin123';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update admin password
$stmt = $connection->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $hashedPassword);

if ($stmt->execute()) {
    echo "<h2 style='color: green; font-family: Arial, sans-serif;'>✓ Admin password has been reset successfully!</h2>";
    echo "<p style='font-family: Arial, sans-serif;'><strong>New credentials:</strong></p>";
    echo "<ul style='font-family: Arial, sans-serif;'>";
    echo "<li>Username: <strong>admin</strong></li>";
    echo "<li>Password: <strong>admin123</strong></li>";
    echo "</ul>";
    echo "<p style='color: red; font-family: Arial, sans-serif;'><strong>IMPORTANT: Delete this file (reset_password.php) immediately for security!</strong></p>";
    echo "<a href='login.php' style='font-family: Arial, sans-serif;'>Go to Admin Login</a>";
} else {
    echo "<h2 style='color: red; font-family: Arial, sans-serif;'>✗ Error resetting password: " . $connection->error . "</h2>";
}
?>
