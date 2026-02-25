<?php
/**
 * Quiz Page - Take Quiz
 * Online Quiz System
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require user login
requireLogin();

// Get quiz ID from URL
$quizId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($quizId <= 0) {
    $_SESSION['error'] = 'Invalid quiz ID.';
    redirect('dashboard.php');
}

// Get quiz details
$quiz = getQuizById($quizId);

if (!$quiz) {
    $_SESSION['error'] = 'Quiz not found.';
    redirect('dashboard.php');
}

// Check if user wants to retake the quiz (abandon old attempts and start fresh)
$retake = isset($_GET['retake']) && $_GET['retake'] == '1';

if ($retake) {
    abandonQuizAttempts($_SESSION['user_id'], $quizId);
}

// Get or create quiz attempt (this handles shuffling)
$attempt = getOrCreateQuizAttempt($_SESSION['user_id'], $quizId);

// Get questions in shuffled order based on attempt
$questions = getQuestionsByShuffledOrder($quizId, $attempt['shuffled_question_ids']);

if (empty($questions)) {
    $_SESSION['error'] = 'This quiz has no questions yet.';
    redirect('dashboard.php');
}

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $correctAnswers = 0;
    $wrongAnswers = 0;
    $totalQuestions = count($questions);
    
    foreach ($questions as $question) {
        $questionId = $question['id'];
        $userAnswer = isset($_POST['answer_' . $questionId]) ? (int)$_POST['answer_' . $questionId] : 0;
        
        if ($userAnswer === (int)$question['correct_option']) {
            $score += 1;
            $correctAnswers++;
        } else {
            $wrongAnswers++;
        }
    }
    
    // Save result
    saveResult($_SESSION['user_id'], $quizId, $score, $totalQuestions, $correctAnswers, $wrongAnswers);
    
    // Complete the quiz attempt
    completeQuizAttempt($attempt['id']);
    
    // Redirect to result page
    redirect('result.php?quiz_id=' . $quizId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Online Quiz System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .timer-warning {
            animation: pulse 1s infinite;
        }
        .timer-danger {
            animation: pulse 0.5s infinite;
            background: linear-gradient(135deg, #dc3545, #c82333) !important;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
    </style>
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
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Quiz Container -->
    <div class="quiz-container">
        <!-- Quiz Header -->
        <div class="quiz-header fade-in">
            <div>
                <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
                <p style="color: var(--gray-600); margin-top: 5px;">
                    <i class="fas fa-question-circle"></i> <?php echo count($questions); ?> Questions
                </p>
            </div>
            <div class="quiz-timer">
                <i class="fas fa-clock"></i>
                <span id="timer">00:00</span>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="quiz-progress fade-in">
            <div class="quiz-progress-bar" id="progress-bar" style="width: 0%;"></div>
        </div>

        <!-- Quiz Form -->
        <form id="quiz-form" method="POST" action="">
            <input type="hidden" id="quiz-duration" value="<?php echo $quiz['duration']; ?>">
            <input type="hidden" id="total-questions" value="<?php echo count($questions); ?>">

            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card fade-in">
                    <span class="question-number">Question <?php echo ($index + 1); ?> of <?php echo count($questions); ?></span>
                    <h3 class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></h3>
                    
                    <div class="options-list">
                        <!-- Option A -->
                        <label class="option-item" data-question="<?php echo $question['id']; ?>" data-option="1">
                            <input type="radio" name="answer_<?php echo $question['id']; ?>" value="1" required>
                            <span class="option-letter">A</span>
                            <span class="option-label"><?php echo htmlspecialchars($question['option1']); ?></span>
                        </label>
                        
                        <!-- Option B -->
                        <label class="option-item" data-question="<?php echo $question['id']; ?>" data-option="2">
                            <input type="radio" name="answer_<?php echo $question['id']; ?>" value="2">
                            <span class="option-letter">B</span>
                            <span class="option-label"><?php echo htmlspecialchars($question['option2']); ?></span>
                        </label>
                        
                        <!-- Option C -->
                        <label class="option-item" data-question="<?php echo $question['id']; ?>" data-option="3">
                            <input type="radio" name="answer_<?php echo $question['id']; ?>" value="3">
                            <span class="option-letter">C</span>
                            <span class="option-label"><?php echo htmlspecialchars($question['option3']); ?></span>
                        </label>
                        
                        <!-- Option D -->
                        <label class="option-item" data-question="<?php echo $question['id']; ?>" data-option="4">
                            <input type="radio" name="answer_<?php echo $question['id']; ?>" value="4">
                            <span class="option-letter">D</span>
                            <span class="option-label"><?php echo htmlspecialchars($question['option4']); ?></span>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Submit Button -->
            <div class="text-center mt-4 fade-in">
                <button type="button" onclick="submitQuiz()" class="btn btn-lg btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Quiz
                </button>
            </div>
        </form>
    </div>

    <script src="assets/js/quiz.js"></script>
</body>
</html>