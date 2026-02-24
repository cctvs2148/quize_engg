<?php
/**
 * Admin - Manage Questions
 * Online Quiz System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdminLogin();

$message = '';
$error = '';

// Get quiz ID from URL
$quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// Get all quizzes for dropdown
$quizzes = getAllQuizzesAdmin();

// If no quiz selected, show quiz selection
if ($quizId === 0 && !empty($quizzes)) {
    $quizId = $quizzes[0]['id'];
}

// Get selected quiz
$selectedQuiz = $quizId > 0 ? getQuizById($quizId) : null;

// Handle Add Question
if (isset($_POST['add_question'])) {
    $qQuizId = (int)$_POST['quiz_id'];
    $questionText = sanitize($_POST['question_text']);
    $option1 = sanitize($_POST['option1']);
    $option2 = sanitize($_POST['option2']);
    $option3 = sanitize($_POST['option3']);
    $option4 = sanitize($_POST['option4']);
    $correctOption = (int)$_POST['correct_option'];
    
    if (empty($questionText) || empty($option1) || empty($option2) || empty($option3) || empty($option4)) {
        $error = 'Please fill in all fields.';
    } else {
        if (addQuestion($qQuizId, $questionText, $option1, $option2, $option3, $option4, $correctOption)) {
            $message = 'Question added successfully!';
        } else {
            $error = 'Failed to add question.';
        }
    }
}

// Handle Edit Question
if (isset($_POST['edit_question'])) {
    $id = (int)$_POST['question_id'];
    $questionText = sanitize($_POST['question_text']);
    $option1 = sanitize($_POST['option1']);
    $option2 = sanitize($_POST['option2']);
    $option3 = sanitize($_POST['option3']);
    $option4 = sanitize($_POST['option4']);
    $correctOption = (int)$_POST['correct_option'];
    
    if (empty($questionText) || empty($option1) || empty($option2) || empty($option3) || empty($option4)) {
        $error = 'Please fill in all fields.';
    } else {
        if (updateQuestion($id, $questionText, $option1, $option2, $option3, $option4, $correctOption)) {
            $message = 'Question updated successfully!';
        } else {
            $error = 'Failed to update question.';
        }
    }
}

// Handle Delete Question
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (deleteQuestion($id)) {
        $message = 'Question deleted successfully!';
    } else {
        $error = 'Failed to delete question.';
    }
}

// Get questions for selected quiz
$questions = $quizId > 0 ? getQuestionsByQuizId($quizId) : [];

// Get question to edit
$editQuestion = null;
if (isset($_GET['edit_question'])) {
    $editQuestion = getQuestionById((int)$_GET['edit_question']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - Admin - Online Quiz System</title>
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
                    <a href="questions.php" class="active">
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
                        <h1>Manage Questions</h1>
                        <p>Add, edit, and delete quiz questions</p>
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

                <!-- Quiz Selection -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><i class="fas fa-filter"></i> Select Quiz</h2>
                    </div>
                    <div class="admin-card-body">
                        <form method="GET" action="">
                            <div class="form-group" style="display: flex; gap: 15px; align-items: flex-end;">
                                <div style="flex: 1;">
                                    <label for="quiz_id">Choose a Quiz</label>
                                    <select id="quiz_id" name="quiz_id" class="form-control" onchange="this.form.submit()">
                                        <?php foreach ($quizzes as $quiz): ?>
                                            <option value="<?php echo $quiz['id']; ?>" <?php echo $quizId == $quiz['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($quiz['title']); ?> 
                                                (<?php echo countQuestions($quiz['id']); ?> questions)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($selectedQuiz): ?>
                    <!-- Add/Edit Question Form -->
                    <div class="form-card" style="margin-top: 30px;">
                        <div class="form-card-header">
                            <h2><?php echo $editQuestion ? 'Edit Question' : 'Add New Question'; ?>
                                <span style="color: var(--gray-500); font-weight: normal; font-size: 0.9rem;">
                                    for "<?php echo htmlspecialchars($selectedQuiz['title']); ?>"
                                </span>
                            </h2>
                        </div>
                        <form method="POST" action="">
                            <div class="form-card-body">
                                <?php if ($editQuestion): ?>
                                    <input type="hidden" name="question_id" value="<?php echo $editQuestion['id']; ?>">
                                <?php endif; ?>
                                <input type="hidden" name="quiz_id" value="<?php echo $quizId; ?>">
                                
                                <div class="form-group">
                                    <label for="question_text">Question Text *</label>
                                    <textarea id="question_text" 
                                              name="question_text" 
                                              class="form-control" 
                                              placeholder="Enter the question"
                                              required><?php echo $editQuestion ? htmlspecialchars($editQuestion['question_text']) : ''; ?></textarea>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                    <div class="form-group">
                                        <label for="option1">Option A *</label>
                                        <input type="text" 
                                               id="option1" 
                                               name="option1" 
                                               class="form-control" 
                                               placeholder="Enter option A"
                                               value="<?php echo $editQuestion ? htmlspecialchars($editQuestion['option1']) : ''; ?>"
                                               required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="option2">Option B *</label>
                                        <input type="text" 
                                               id="option2" 
                                               name="option2" 
                                               class="form-control" 
                                               placeholder="Enter option B"
                                               value="<?php echo $editQuestion ? htmlspecialchars($editQuestion['option2']) : ''; ?>"
                                               required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="option3">Option C *</label>
                                        <input type="text" 
                                               id="option3" 
                                               name="option3" 
                                               class="form-control" 
                                               placeholder="Enter option C"
                                               value="<?php echo $editQuestion ? htmlspecialchars($editQuestion['option3']) : ''; ?>"
                                               required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="option4">Option D *</label>
                                        <input type="text" 
                                               id="option4" 
                                               name="option4" 
                                               class="form-control" 
                                               placeholder="Enter option D"
                                               value="<?php echo $editQuestion ? htmlspecialchars($editQuestion['option4']) : ''; ?>"
                                               required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="correct_option">Correct Answer *</label>
                                    <select id="correct_option" name="correct_option" class="form-control" required>
                                        <option value="1" <?php echo ($editQuestion && $editQuestion['correct_option'] == 1) ? 'selected' : ''; ?>>Option A</option>
                                        <option value="2" <?php echo ($editQuestion && $editQuestion['correct_option'] == 2) ? 'selected' : ''; ?>>Option B</option>
                                        <option value="3" <?php echo ($editQuestion && $editQuestion['correct_option'] == 3) ? 'selected' : ''; ?>>Option C</option>
                                        <option value="4" <?php echo ($editQuestion && $editQuestion['correct_option'] == 4) ? 'selected' : ''; ?>>Option D</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-card-footer">
                                <?php if ($editQuestion): ?>
                                    <button type="submit" name="edit_question" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Question
                                    </button>
                                    <a href="questions.php?quiz_id=<?php echo $quizId; ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php else: ?>
                                    <button type="submit" name="add_question" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Question
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- Questions List -->
                    <div class="admin-card" style="margin-top: 30px;">
                        <div class="admin-card-header">
                            <h2><i class="fas fa-list"></i> Questions for "<?php echo htmlspecialchars($selectedQuiz['title']); ?>"</h2>
                        </div>
                        <?php if (empty($questions)): ?>
                            <div class="empty-state">
                                <i class="fas fa-question-circle"></i>
                                <h3>No Questions Yet</h3>
                                <p>Add questions using the form above.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($questions as $index => $question): ?>
                                <div class="question-item">
                                    <div class="question-item-header">
                                        <span class="question-item-text">
                                            <strong>Q<?php echo ($index + 1); ?>:</strong> 
                                            <?php echo htmlspecialchars($question['question_text']); ?>
                                        </span>
                                        <div class="question-item-actions">
                                            <a href="questions.php?quiz_id=<?php echo $quizId; ?>&edit_question=<?php echo $question['id']; ?>" 
                                               class="action-btn edit" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="questions.php?quiz_id=<?php echo $quizId; ?>&delete=<?php echo $question['id']; ?>" 
                                               class="action-btn delete" 
                                               title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this question?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="question-options">
                                        <div class="question-option <?php echo $question['correct_option'] == 1 ? 'correct' : ''; ?>">
                                            A. <?php echo htmlspecialchars($question['option1']); ?>
                                            <?php if ($question['correct_option'] == 1): ?> ✓<?php endif; ?>
                                        </div>
                                        <div class="question-option <?php echo $question['correct_option'] == 2 ? 'correct' : ''; ?>">
                                            B. <?php echo htmlspecialchars($question['option2']); ?>
                                            <?php if ($question['correct_option'] == 2): ?> ✓<?php endif; ?>
                                        </div>
                                        <div class="question-option <?php echo $question['correct_option'] == 3 ? 'correct' : ''; ?>">
                                            C. <?php echo htmlspecialchars($question['option3']); ?>
                                            <?php if ($question['correct_option'] == 3): ?> ✓<?php endif; ?>
                                        </div>
                                        <div class="question-option <?php echo $question['correct_option'] == 4 ? 'correct' : ''; ?>">
                                            D. <?php echo htmlspecialchars($question['option4']); ?>
                                            <?php if ($question['correct_option'] == 4): ?> ✓<?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="admin-card" style="margin-top: 30px;">
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <h3>No Quiz Selected</h3>
                            <p>Please select a quiz from the dropdown above or <a href="quizzes.php">create a new quiz</a> first.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/quiz.js"></script>
</body>
</html>