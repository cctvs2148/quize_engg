<?php
/**
 * Admin - View All Results
 * Online Quiz System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdminLogin();

// Get all results
$results = getAllResults();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results - Admin - Online Quiz System</title>
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
                    <a href="results.php" class="active">
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
                        <h1>Quiz Results</h1>
                        <p>View all quiz results</p>
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
                        <div class="admin-stat-icon results">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo count($results); ?></h3>
                            <p>Total Results</p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon avg">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo getAverageScore(); ?>%</h3>
                            <p>Average Score</p>
                        </div>
                    </div>
                </div>

                <!-- Results List -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><i class="fas fa-list"></i> All Results</h2>
                    </div>
                    <?php if (empty($results)): ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-bar"></i>
                            <h3>No Results Yet</h3>
                            <p>Quiz results will appear here after users complete quizzes.</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Student Name</th>
                                        <th>Exam Name</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Result</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result): ?>
                                        <?php 
                                        $percentage = round(($result['correct_answers'] / $result['total_questions']) * 100);
                                        $isPassed = $percentage >= 50;
                                        ?>
                                        <tr>
                                            <td><?php echo $result['id']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($result['user_name']); ?>
                                                <br><small style="color: var(--gray-500);"><?php echo htmlspecialchars($result['user_email']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($result['quiz_title']); ?></td>
                                            <td><?php echo $result['correct_answers']; ?> / <?php echo $result['total_questions']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $isPassed ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo $percentage; ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($isPassed): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> PASS
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times"></i> FAIL
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($result['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/quiz.js"></script>
</body>
</html>