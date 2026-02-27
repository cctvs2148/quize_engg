<?php
/**
 * Admin - Manage Users
 * Online Quiz System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdminLogin();

$message = '';
$error = '';

// Handle Add User (from slide panel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $result = registerUser($name, $email, $password);
        if ($result['success']) {
            $message = 'User added successfully!';
        } else {
            $error = $result['message'];
        }
    }
}

// Handle Edit User
if (isset($_GET['edit'])) {
    $editUserId = (int)$_GET['edit'];
    global $connection;
    $stmt = $connection->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $editUserId);
    $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
}

// Handle Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $userId = (int)$_POST['user_id'];
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $newPassword = $_POST['password'] ?? '';
    
    if (empty($name) || empty($email)) {
        $error = 'Please fill in all required fields.';
    } else {
        global $connection;
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $connection->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $hashedPassword, $userId);
        } else {
            $stmt = $connection->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $userId);
        }
        
        if ($stmt->execute()) {
            $message = 'User updated successfully!';
            $editUser = null;
        } else {
            $error = 'Failed to update user.';
        }
    }
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $userId = (int)$_GET['delete'];
    
    // Delete user (cascade will delete their results too)
    global $connection;
    $stmt = $connection->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $message = 'User deleted successfully!';
    } else {
        $error = 'Failed to delete user.';
    }
}

// Get all users
$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin - Online Quiz System</title>
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
                    <img src="../assets/images/logo.png" alt="Logo" style="max-height: 50px;">
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
                    <a href="users.php" class="active">
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
                    <a href="change_password.php">
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
                        <h1>Manage Users</h1>
                        <p>View all registered users</p>
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
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Edit User Form -->
                <?php if (isset($editUser) && !empty($editUser)): ?>
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2><i class="fas fa-edit"></i> Edit User</h2>
                            <a href="users.php" class="btn btn-sm btn-secondary">Cancel</a>
                        </div>
                        <form method="POST" action="users.php">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($editUser['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($editUser['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>New Password (leave blank to keep current)</label>
                                <input type="password" name="password" class="form-control" placeholder="Enter new password">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update User
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
                
                <!-- Stats -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo count($users); ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                </div>

                <!-- Users List -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><i class="fas fa-list"></i> All Users</h2>
                    </div>
                    <?php if (empty($users)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>No Users Yet</h3>
                            <p>Registered users will appear here.</p>
                        </div>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Quizzes Taken</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <?php 
                                    // Get user's quiz count
                                    global $connection;
                                    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM results WHERE user_id = ?");
                                    $stmt->bind_param("i", $user['id']);
                                    $stmt->execute();
                                    $quizCount = $stmt->get_result()->fetch_assoc()['count'];
                                    ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div class="admin-user-avatar" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                </div>
                                                <?php echo htmlspecialchars($user['name']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge badge-primary"><?php echo $quizCount; ?> quizzes</span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <a href="?delete=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this user? All their quiz results will also be deleted.');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/quiz.js"></script>
</body>
</html>