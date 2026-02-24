<?php
/**
 * Reset Password Page
 * Online Quiz System
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';
$validToken = false;
$userData = null;

// Check for token in URL
$token = $_GET['token'] ?? '';

if (!empty($token)) {
    $userData = verifyResetToken($token);
    
    if ($userData) {
        $validToken = true;
    } else {
        $error = 'Invalid or expired reset link. Please request a new one.';
    }
} else {
    $error = 'No reset token provided. Please request a new reset link.';
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $token = $_POST['token'] ?? '';
    
    // Validation
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $result = resetPasswordWithToken($token, $newPassword);
        
        if ($result['success']) {
            $success = $result['message'];
            $validToken = false; // Hide form after success
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Online Quiz System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box fade-in">
            <div class="auth-header">
                <h1><i class="fas fa-lock"></i> Reset Password</h1>
                <p>Create a new password for your account</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($validToken): ?>
                    <?php if ($userData): ?>
                        <div style="text-align: center; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                            <i class="fas fa-user-circle" style="font-size: 40px; color: var(--primary-color);"></i>
                            <p style="margin: 10px 0 0; color: var(--gray-600);">
                                Resetting password for:<br>
                                <strong><?php echo htmlspecialchars($userData['email']); ?></strong>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="form-group">
                            <label for="new_password">
                                <i class="fas fa-key"></i> New Password
                            </label>
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   class="form-control" 
                                   placeholder="Enter new password (min 6 characters)"
                                   minlength="6"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-check-double"></i> Confirm New Password
                            </label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   class="form-control" 
                                   placeholder="Confirm your new password"
                                   minlength="6"
                                   required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                            <i class="fas fa-save"></i> Reset Password
                        </button>
                    </form>
                <?php else: ?>
                    <div style="text-align: center;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: var(--danger-color); margin-bottom: 15px;"></i>
                        <p>The password reset link is invalid or has expired.</p>
                        <p>Please request a new reset link.</p>
                    </div>
                <?php endif; ?>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="login.php" style="color: var(--gray-600); font-size: 0.9rem;">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                    <?php if (!$validToken): ?>
                        <span style="margin: 0 10px;">|</span>
                        <a href="forgot_password.php" style="color: var(--primary-color); font-size: 0.9rem;">
                            Request New Reset Link
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/quiz.js"></script>
</body>
</html>