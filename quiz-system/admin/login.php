<?php
/**
 * Admin Login Page
 * Online Quiz System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect if already logged in as admin
if (isAdminLoggedIn()) {
    redirect('index.php');
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $result = loginAdmin($username, $password);
        
        if ($result['success']) {
            redirect('index.php');
        } else {
            $error = $result['message'];
        }
    }
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
    <title>Admin Login - Online Quiz System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box fade-in">
            <div class="auth-header" style="background: linear-gradient(135deg, #1a1a2e, #16213e);">
                <h1><i class="fas fa-user-shield"></i> Admin Panel</h1>
                <p>Login to access the admin dashboard</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-control" 
                               placeholder="Enter admin username"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
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
                               placeholder="Enter admin password"
                               required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>Default credentials: <strong>admin</strong> / <strong>admin123</strong></p>
                <p style="margin-top: 10px;">
                    <a href="../login.php" style="color: var(--gray-600); font-size: 0.9rem;">
                        <i class="fas fa-arrow-left"></i> Back to User Login
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="../assets/js/quiz.js"></script>
</body>
</html>