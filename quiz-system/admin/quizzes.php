<?php
/**
 * Admin - Manage Quizzes
 * Online Quiz System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdminLogin();

$message = '';
$error = '';

// Handle Add Quiz
if (isset($_POST['add_quiz'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $duration = (int)$_POST['duration'];
    $status = sanitize($_POST['status']);
    
    if (empty($title) || empty($duration)) {
        $error = 'Please fill in all required fields.';
    } else {
        $result = createQuiz($title, $description, $duration);
        if ($result['success']) {
            $message = 'Quiz created successfully!';
        } else {
            $error = 'Failed to create quiz.';
        }
    }
}

// Handle Edit Quiz
if (isset($_POST['edit_quiz'])) {
    $id = (int)$_POST['quiz_id'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $duration = (int)$_POST['duration'];
    $status = sanitize($_POST['status']);
    
    if (empty($title) || empty($duration)) {
        $error = 'Please fill in all required fields.';
    } else {
        if (updateQuiz($id, $title, $description, $duration, $status)) {
            $message = 'Quiz updated successfully!';
        } else {
            $error = 'Failed to update quiz.';
        }
    }
}

// Handle Delete Quiz
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (deleteQuiz($id)) {
        $message = 'Quiz deleted successfully!';
    } else {
        $error = 'Failed to delete quiz.';
    }
}

// Get all quizzes
$quizzes = getAllQuizzesAdmin();

// Get quiz to edit
$editQuiz = null;
if (isset($_GET['edit'])) {
    $editQuiz = getQuizById((int)$_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - Admin - Online Quiz System</title>
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
                    <a href="quizzes.php" class="active">
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
                        <h1>Manage Quizzes</h1>
                        <p>Create, edit, and delete quizzes</p>
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

                <!-- Add/Edit Quiz Form -->
                <div class="form-card">
                    <div class="form-card-header">
                        <h2><?php echo $editQuiz ? 'Edit Quiz' : 'Add New Quiz'; ?></h2>
                    </div>
                    <form method="POST" action="">
                        <div class="form-card-body">
                            <?php if ($editQuiz): ?>
                                <input type="hidden" name="quiz_id" value="<?php echo $editQuiz['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="title">Quiz Title *</label>
                                <input type="text" 
                                       id="title" 
                                       name="title" 
                                       class="form-control" 
                                       placeholder="Enter quiz title"
                                       value="<?php echo $editQuiz ? htmlspecialchars($editQuiz['title']) : ''; ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" 
                                          name="description" 
                                          class="form-control" 
                                          placeholder="Enter quiz description"><?php echo $editQuiz ? htmlspecialchars($editQuiz['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="duration">Duration (minutes) *</label>
                                <input type="number" 
                                       id="duration" 
                                       name="duration" 
                                       class="form-control" 
                                       placeholder="Enter duration in minutes"
                                       value="<?php echo $editQuiz ? $editQuiz['duration'] : '10'; ?>"
                                       min="1"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="active" <?php echo ($editQuiz && $editQuiz['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($editQuiz && $editQuiz['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-card-footer">
                            <?php if ($editQuiz): ?>
                                <button type="submit" name="edit_quiz" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Quiz
                                </button>
                                <a href="quizzes.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php else: ?>
                                <button type="submit" name="add_quiz" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Quiz
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Quizzes List -->
                <div class="admin-card" style="margin-top: 30px;">
                    <div class="admin-card-header">
                        <h2><i class="fas fa-list"></i> All Quizzes</h2>
                    </div>
                    <?php if (empty($quizzes)): ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <h3>No Quizzes Yet</h3>
                            <p>Add your first quiz using the form above.</p>
                        </div>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Duration</th>
                                    <th>Questions</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quizzes as $quiz): ?>
                                    <?php $questionCount = countQuestions($quiz['id']); ?>
                                    <tr>
                                        <td><?php echo $quiz['id']; ?></td>
                                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                        <td><?php echo $quiz['duration']; ?> min</td>
                                        <td><?php echo $questionCount; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $quiz['status']; ?>">
                                                <?php echo ucfirst($quiz['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="questions.php?quiz_id=<?php echo $quiz['id']; ?>" 
                                                   class="action-btn view" 
                                                   title="Manage Questions">
                                                    <i class="fas fa-question-circle"></i>
                                                </a>
                                                <a href="quizzes.php?edit=<?php echo $quiz['id']; ?>" 
                                                   class="action-btn edit" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="quizzes.php?delete=<?php echo $quiz['id']; ?>" 
                                                   class="action-btn delete" 
                                                   title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this quiz? All questions and results will be deleted too.');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
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