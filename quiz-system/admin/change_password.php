<?php
/**
 * Admin Change Password Page
 * Online Quiz System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdminLogin();

$error = '';
$success = '';

// Handle change password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirm password do not match.';
    } elseif ($oldPassword === $newPassword) {
        $error = 'New password must be different from the current password.';
    } else {
        // Change password
        $result = changeAdminPassword($_SESSION['admin_id'], $oldPassword, $newPassword);
        
        if ($result['success']) {
            $success = $result['message'];
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
    <title>Change Password - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="index.php" class="sidebar-logo">
                    <i class="fas fa-brain"></i>
                    <span>QuizMaster</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-title">Main</span>
                    <a href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <span class="nav-section-title">Manage</span>
                    <a href="quizzes.php">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Quizzes</span>
                    </a>
                    <a href="questions.php">
                        <i class="fas fa-question-circle"></i>
                        <span>Questions</span>
                    </a>
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                    <a href="results.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Results</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <span class="nav-section-title">Account</span>
                    <a href="change_password.php" class="active">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </a>
                    <a href="../index.php">
                        <i class="fas fa-globe"></i>
                        <span>View Site</span>
                    </a>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <h1>Change Password</h1>
                        <p>Update your admin account password</p>
                    </div>
                </div>
                <div class="admin-header-right">
                    <div class="admin-user">
                        <div class="admin-user-avatar">
                            <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                        </div>
                        <span class="admin-user-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="admin-content">
                <div class="admin-card" style="max-width: 600px;">
                    <div class="admin-card-header">
                        <h2><i class="fas fa-key"></i> Change Your Password</h2>
                    </div>
                    
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
                            <label for="old_password">
                                <i class="fas fa-lock"></i> Current Password
                            </label>
                            <input type="password" 
                                   id="old_password" 
                                   name="old_password" 
                                   class="form-control" 
                                   placeholder="Enter your current password"
                                   required>
                        </div>
                        
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
                            <small class="form-text">Password must be at least 6 characters long.</small>
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
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Change Password
                            </button>
                            <a href="index.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Password Guidelines -->
                <div class="admin-card" style="max-width: 600px; margin-top: 20px;">
                    <div class="admin-card-header">
                        <h2><i class="fas fa-info-circle"></i> Password Guidelines</h2>
                    </div>
                    <ul style="padding-left: 20px; color: var(--gray-600);">
                        <li>Password must be at least 6 characters long</li>
                        <li>Use a combination of letters, numbers, and special characters</li>
                        <li>Avoid using easily guessable information</li>
                        <li>Do not reuse your current password</li>
                        <li>Keep your password secure and do not share it</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/quiz.js"></script>
</body>
</html>
