<?php
/**
 * Result Page - Show Quiz Completion Status
 * Online Quiz System
 * 
 * Note: Marks are hidden from students - only visible in Admin Dashboard
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require user login
requireLogin();

// Get result ID or quiz_id from URL
$resultId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

$result = null;

if ($resultId > 0) {
    // Get specific result
    global $connection;
    $stmt = $connection->prepare("
        SELECT r.*, q.title as quiz_title, q.description 
        FROM results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        WHERE r.id = ? AND r.user_id = ?
    ");
    $stmt->bind_param("ii", $resultId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
} elseif ($quizId > 0) {
    // Get latest result for this quiz
    global $connection;
    $stmt = $connection->prepare("
        SELECT r.*, q.title as quiz_title, q.description 
        FROM results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        WHERE r.quiz_id = ? AND r.user_id = ? 
        ORDER BY r.created_at DESC 
        LIMIT 1
    ");
    $stmt->bind_param("ii", $quizId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
}

if (!$result) {
    $_SESSION['error'] = 'Result not found.';
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Completed - Online Quiz System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .result-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3rem;
            background: rgba(76, 201, 240, 0.2);
            color: #0a9396;
        }
        .confetti {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">
                <img src="assets/images/logo.png" alt="Logo" style="max-height: 50px;">
            </a>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Result Container -->
    <div class="result-container">
        <div class="result-card fade-in">
            <div class="result-header">
                <div class="result-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Quiz Completed!</h1>
                <p><?php echo htmlspecialchars($result['quiz_title']); ?></p>
            </div>
            
            <div class="result-body">
                <p class="result-message" style="font-size: 1.2rem; margin-bottom: 30px;">
                    Thank you for completing the quiz. Your submission has been recorded successfully.
                </p>
                
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Result Details -->
        <div class="results-table-container fade-in" style="margin-top: 30px;">
            <div class="table-header">
                <h2><i class="fas fa-info-circle"></i> Submission Details</h2>
            </div>
            <table class="results-table">
                <tr>
                    <td><strong>Quiz Title</strong></td>
                    <td><?php echo htmlspecialchars($result['quiz_title']); ?></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td>
                        <span class="badge badge-success">
                            <i class="fas fa-check"></i> Completed
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Date Taken</strong></td>
                    <td><?php echo date('F d, Y \a\t H:i A', strtotime($result['created_at'])); ?></td>
                </tr>
            </table>
            
            <div style="padding: 20px; background: var(--gray-100); border-radius: 8px; margin-top: 20px;">
                <p style="color: var(--gray-600); text-align: center; margin: 0;">
                    <i class="fas fa-info-circle"></i> 
                    Your results will be announced by the administrator. Please check back later or contact your instructor for more information.
                </p>
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
    <script>
        // Simple confetti effect for completion
        document.addEventListener('DOMContentLoaded', function() {
            for (let i = 0; i < 30; i++) {
                const confetti = document.createElement('div');
                confetti.style.cssText = `
                    position: fixed;
                    width: 10px;
                    height: 10px;
                    background: hsl(${Math.random() * 360}, 100%, 50%);
                    left: ${Math.random() * 100}vw;
                    top: -10px;
                    border-radius: 50%;
                    pointer-events: none;
                    z-index: 9999;
                    animation: fall ${2 + Math.random() * 3}s linear forwards;
                `;
                document.body.appendChild(confetti);
                setTimeout(() => confetti.remove(), 5000);
            }
        });
        
        // Add fall animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fall {
                to {
                    transform: translateY(100vh) rotate(720deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
