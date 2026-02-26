<?php
/**
 * Index Page - Landing Page
 * Online Quiz System
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Get active quizzes for preview
$quizzes = getAllQuizzes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Quiz System - Test Your Knowledge</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="assets/images/logo.png" alt="Logo" style="max-height: 50px;">
            </a>
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to QuizMaster</h1>
            <p>Challenge yourself with our interactive quizzes. Test your knowledge, track your progress, and compete with others!</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn btn-lg btn-primary">
                    <i class="fas fa-user-plus"></i> Get Started
                </a>
                <a href="login.php" class="btn btn-lg btn-outline" style="color: white; border-color: white;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section style="padding: 60px 0; background: white;">
        <div class="container">
            <h2 class="text-center mb-4" style="color: #1a1a2e;">Why Choose QuizMaster?</h2>
            <div class="quiz-grid" style="padding: 0;">
                <div class="stat-card">
                    <i class="fas fa-question-circle"></i>
                    <h3>Multiple Quizzes</h3>
                    <p>Access various quizzes on different topics</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Timed Tests</h3>
                    <p>Challenge yourself with timed quizzes</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Track Progress</h3>
                    <p>View your results and improvement</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Mobile Friendly</h3>
                    <p>Take quizzes on any device</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Available Quizzes Preview -->
    <section style="padding: 60px 0; background: #f8f9fa;">
        <div class="container">
            <h2 class="text-center mb-4" style="color: #1a1a2e;">Available Quizzes</h2>
            <?php if (empty($quizzes)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>No Quizzes Available</h3>
                    <p>Check back later for new quizzes!</p>
                </div>
            <?php else: ?>
                <div class="quiz-grid" style="padding: 0;">
                    <?php foreach ($quizzes as $quiz): ?>
                        <?php $questionCount = countQuestions($quiz['id']); ?>
                        <div class="quiz-card fade-in">
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
                                <span class="badge badge-success">Active</span>
                                <a href="login.php" class="btn btn-sm btn-primary">
                                    <i class="fas fa-play"></i> Start Quiz
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="text-center mt-4">
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus"></i> Register to Start
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> QuizMaster - Online Quiz System. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/quiz.js"></script>
</body>
</html>