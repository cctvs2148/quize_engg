<?php
/**
 * Login Page
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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            redirect('dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}

// Check for success message from registration
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Check for error message from other pages
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Quiz System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box fade-in">
            <div class="auth-header">
                <img src="assets/images/logo.png" alt="Logo" style="max-height: 120px; margin-bottom: 15px;">
                <h1><i class="fas fa-sign-in-alt"></i> Welcome Back</h1>
                <p>Login to continue to your dashboard</p>
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
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="Enter your email"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Enter your password"
                               required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <div style="text-align: center; margin-top: 15px;">
                    <a href="forgot_password.php" style="color: var(--primary-color); font-size: 0.9rem;">
                        <i class="fas fa-key"></i> Forgot Password?
                    </a>
                </div>
            </div>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
                <p style="margin-top: 10px;">
                    <a href="admin/login.php" style="color: var(--gray-600); font-size: 0.9rem;">
                        <i class="fas fa-user-shield"></i> Admin Login
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="assets/js/quiz.js"></script>
</body>
</html>