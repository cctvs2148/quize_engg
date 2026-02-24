<?php
/**
 * Logout Page
 * Online Quiz System
 */

require_once 'includes/config.php';

// Destroy session and redirect to login
session_unset();
session_destroy();

// Start new session for message
session_start();
$_SESSION['success'] = 'You have been logged out successfully.';

// Redirect to login page
header('Location: login.php');
exit();
?>