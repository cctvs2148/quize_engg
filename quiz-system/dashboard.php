<?php
/**
 * User Dashboard
 * Online Quiz System
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require user login
requireLogin();

// Get current user
$user = getCurrentUser();

// Get all active quizzes
$quizzes = getAllQuizzes();

// Get user's previous results
$results = getUserResults($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Online Quiz System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-brain"></i>
                QuizMaster
            </a>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <span style="color: var(--gray-600);">
                    <i class="fas fa-user"></i> 
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </span>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
                <p>Ready to test your knowledge? Choose a quiz below!</p>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card fade-in">
                    <i class="fas fa-clipboard-list"></i>
                    <h3><?php echo count($quizzes); ?></h3>
                    <p>Available Quizzes</p>
                </div>
                <div class="stat-card fade-in">
                    <i class="fas fa-check-circle"></i>
                    <h3><?php echo count($results); ?></h3>
                    <p>Quizzes Completed</p>
                </div>
                <div class="stat-card fade-in">
                    <i class="fas fa-hourglass-half"></i>
                    <h3><?php echo count($quizzes) - count($results); ?></h3>
                    <p>Pending Quizzes</p>
                </div>
            </div>

            <!-- Available Quizzes -->
            <div class="results-table-container fade-in">
                <div class="table-header">
                    <h2><i class="fas fa-clipboard-list"></i> Available Quizzes</h2>
                </div>
                <?php if (empty($quizzes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>No Quizzes Available</h3>
                        <p>Check back later for new quizzes!</p>
                    </div>
                <?php else: ?>
                    <div class="quiz-grid" style="padding: 25px;">
                        <?php foreach ($quizzes as $quiz): ?>
                            <?php 
                            $questionCount = countQuestions($quiz['id']);
                            $hasTaken = hasUserTakenQuiz($_SESSION['user_id'], $quiz['id']);
                            ?>
                            <div class="quiz-card">
                                <div class="quiz-card-header">
                                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                </div>
                                <div class="quiz-card-body">
                                    <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                    <div class="quiz-meta">
                                        <span>
                                            <i class="fas fa-question-circle"></i>
                                            <?php echo $questionCount; ?> Questions
                                        </span>
                                        <span>
                                            <i class="fas fa-clock"></i>
                                            <?php echo $quiz['duration']; ?> Minutes
                                        </span>
                                    </div>
                                </div>
                                <div class="quiz-card-footer">
                                    <?php if ($hasTaken): ?>
                                        <span class="badge badge-primary">
                                            <i class="fas fa-check"></i> Completed
                                        </span>
                                        <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-redo"></i> Retake
                                        </a>
                                    <?php elseif ($questionCount == 0): ?>
                                        <span class="badge badge-danger">No Questions</span>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-play"></i> Start Quiz
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-success">Available</span>
                                        <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-play"></i> Start Quiz
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Previous Results -->
            <div class="results-table-container fade-in" style="margin-top: 30px;">
                <div class="table-header">
                    <h2><i class="fas fa-history"></i> Your Quiz History</h2>
                </div>
                <?php if (empty($results)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-check"></i>
                        <h3>No Quizzes Attempted</h3>
                        <p>Complete a quiz to see your history here!</p>
                    </div>
                <?php else: ?>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Quiz Title</th>
                                <th>Status</th>
                                <th>Date Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['quiz_title']); ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Completed
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($result['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> QuizMaster - Online Quiz System. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/quiz.js"></script>
</body>
</html>