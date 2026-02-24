<?php
/**
 * Authentication Functions
 * Online Quiz System
 */

require_once 'config.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Require user login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please login to access this page.";
        redirect('login.php');
    }
}

/**
 * Require admin login - redirect to admin login page if not logged in
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        $_SESSION['error'] = "Please login as admin to access this page.";
        redirect('admin/login.php');
    }
}

/**
 * Register a new user
 */
function registerUser($name, $email, $password) {
    global $connection;
    
    // Check if email already exists
    $stmt = $connection->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Email already exists.'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $connection->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Registration successful. Please login.'];
    } else {
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Login user - Standard email/password login
 */
function loginUser($email, $password) {
    global $connection;
    
    $stmt = $connection->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            return ['success' => true, 'message' => 'Login successful.'];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid email or password.'];
}

/**
 * Login admin
 */
function loginAdmin($username, $password) {
    global $connection;
    
    $stmt = $connection->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            return ['success' => true, 'message' => 'Login successful.'];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid username or password.'];
}

/**
 * Logout user
 */
function logoutUser() {
    session_unset();
    session_destroy();
    redirect('login.php');
}

/**
 * Logout admin
 */
function logoutAdmin() {
    session_unset();
    session_destroy();
    redirect('login.php');
}

/**
 * Get current user data
 */
function getCurrentUser() {
    global $connection;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $connection->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Get current admin data
 */
function getCurrentAdmin() {
    global $connection;
    
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    $stmt = $connection->prepare("SELECT id, username, created_at FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Change admin password
 */
function changeAdminPassword($adminId, $oldPassword, $newPassword) {
    global $connection;
    
    // Get current admin password
    $stmt = $connection->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        return ['success' => false, 'message' => 'Admin not found.'];
    }
    
    $admin = $result->fetch_assoc();
    
    // Verify old password
    if (!password_verify($oldPassword, $admin['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect.'];
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $connection->prepare("UPDATE admins SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $adminId);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Password changed successfully.'];
    } else {
        return ['success' => false, 'message' => 'Failed to change password. Please try again.'];
    }
}

// ==================== PASSWORD RESET FUNCTIONS ====================

/**
 * Generate a secure random token
 */
function generateResetToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Request password reset - Send reset link via email
 */
function requestPasswordReset($email) {
    global $connection;
    
    // Check if user exists
    $stmt = $connection->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        // Don't reveal if email exists or not
        return ['success' => true, 'message' => 'If the email exists, a reset link has been sent.'];
    }
    
    $user = $result->fetch_assoc();
    
    // Generate secure token
    $token = generateResetToken();
    $hashedToken = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Delete any existing reset tokens for this user
    $stmt = $connection->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    
    // Store new token
    $stmt = $connection->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user['id'], $hashedToken, $expiresAt);
    
    if ($stmt->execute()) {
        // Send reset email
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
        
        if (sendPasswordResetEmail($email, $user['name'], $resetLink)) {
            return ['success' => true, 'message' => 'If the email exists, a reset link has been sent.'];
        } else {
            return ['success' => false, 'message' => 'Failed to send reset email. Please try again.'];
        }
    }
    
    return ['success' => false, 'message' => 'Failed to process request. Please try again.'];
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($email, $userName, $resetLink) {
    $subject = "Password Reset - Quiz System";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { display: inline-block; padding: 12px 24px; background: #4a6cf7; color: white; text-decoration: none; border-radius: 5px; }
            .note { color: #666; font-size: 14px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Hello $userName,</h2>
            <p>You requested to reset your password. Click the button below to reset it:</p>
            <p><a href='$resetLink' class='button'>Reset Password</a></p>
            <p class='note'>This link will expire in 15 minutes. If you did not request this, please ignore this email.</p>
            <p>Or copy this link to your browser: <br><code>$resetLink</code></p>
            <br>
            <p>Best regards,<br>Quiz System Team</p>
        </div>
    </body>
    </html>
    ";
    
    // Headers for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">" . "\r\n";
    
    // Try to use PHPMailer if available
    if (file_exists(__DIR__ . '/PHPMailer/PHPMailer.php')) {
        require_once __DIR__ . '/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/SMTP.php';
        require_once __DIR__ . '/PHPMailer/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = 'tls';
            $mail->Port = SMTP_PORT;
            
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($email);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            // Fall back to mail()
        }
    }
    
    // Use PHP's built-in mail function as fallback
    return mail($email, $subject, $message, $headers);
}

/**
 * Verify reset token
 */
function verifyResetToken($token) {
    global $connection;
    
    $hashedToken = hash('sha256', $token);
    
    // Clean up expired tokens
    $stmt = $connection->prepare("DELETE FROM password_resets WHERE expires_at < NOW()");
    $stmt->execute();
    
    // Check if token is valid
    $stmt = $connection->prepare("SELECT pr.user_id, u.email, u.name FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ? AND pr.expires_at > NOW()");
    $stmt->bind_param("s", $hashedToken);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Reset password with token
 */
function resetPasswordWithToken($token, $newPassword) {
    global $connection;
    
    $userData = verifyResetToken($token);
    
    if (!$userData) {
        return ['success' => false, 'message' => 'Invalid or expired reset link.'];
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $connection->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userData['user_id']);
    
    if ($stmt->execute()) {
        // Delete used token
        $hashedToken = hash('sha256', $token);
        $stmt = $connection->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $hashedToken);
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Password reset successful. You can now login with your new password.'];
    }
    
    return ['success' => false, 'message' => 'Failed to reset password. Please try again.'];
}
?>