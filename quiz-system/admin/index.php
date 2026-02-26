<?php
/**
 * Admin Dashboard
 * Online Quiz System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdminLogin();

// Get statistics
$totalUsers = countUsers();
$totalQuizzes = countQuizzes();
$totalResults = countResults();
$avgScore = getAverageScore();

// Get recent results
$recentResults = getAllResults();
$recentResults = array_slice($recentResults, 0, 5);

// Get recent users
$recentUsers = getAllUsers();
$recentUsers = array_slice($recentUsers, 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Quiz System</title>
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
                    <a href="index.php" class="active">
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
                        <h1>Dashboard</h1>
                        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
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
                <!-- Stats -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo $totalUsers; ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon quizzes">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo $totalQuizzes; ?></h3>
                            <p>Total Quizzes</p>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon results">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo $totalResults; ?></h3>
                            <p>Total Results</p>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon avg">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo $avgScore; ?>%</h3>
                            <p>Avg Score</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Results -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><i class="fas fa-chart-bar"></i> Recent Quiz Results</h2>
                        <a href="results.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <?php if (empty($recentResults)): ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-bar"></i>
                            <h3>No Results Yet</h3>
                            <p>Quiz results will appear here.</p>
                        </div>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Quiz</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentResults as $result): ?>
                                    <?php $percentage = round(($result['correct_answers'] / $result['total_questions']) * 100); ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($result['user_name']); ?>
                                            <br><small style="color: var(--gray-500);"><?php echo htmlspecialchars($result['user_email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($result['quiz_title']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $percentage >= 50 ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $percentage; ?>%
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($result['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Recent Users -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><i class="fas fa-users"></i> Recent Users</h2>
                        <a href="users.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <?php if (empty($recentUsers)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>No Users Yet</h3>
                            <p>Registered users will appear here.</p>
                        </div>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
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