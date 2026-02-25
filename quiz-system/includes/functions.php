<?php
/**
 * Helper Functions
 * Online Quiz System
 */

require_once 'config.php';

// ==================== QUIZ FUNCTIONS ====================

/**
 * Get all active quizzes
 */
function getAllQuizzes() {
    global $connection;
    
    $stmt = $connection->prepare("SELECT * FROM quizzes WHERE status = 'active' ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get all quizzes (for admin)
 */
function getAllQuizzesAdmin() {
    global $connection;
    
    $stmt = $connection->prepare("SELECT * FROM quizzes ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get quiz by ID
 */
function getQuizById($quizId) {
    global $connection;
    
    $stmt = $connection->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Create new quiz
 */
function createQuiz($title, $description, $duration) {
    global $connection;
    
    $stmt = $connection->prepare("INSERT INTO quizzes (title, description, duration) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $title, $description, $duration);
    
    if ($stmt->execute()) {
        return ['success' => true, 'id' => $connection->insert_id];
    }
    return ['success' => false, 'message' => 'Failed to create quiz.'];
}

/**
 * Update quiz
 */
function updateQuiz($id, $title, $description, $duration, $status) {
    global $connection;
    
    $stmt = $connection->prepare("UPDATE quizzes SET title = ?, description = ?, duration = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssisi", $title, $description, $duration, $status, $id);
    
    return $stmt->execute();
}

/**
 * Delete quiz
 */
function deleteQuiz($id) {
    global $connection;
    
    $stmt = $connection->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
}

// ==================== QUESTION FUNCTIONS ====================

/**
 * Get questions by quiz ID
 */
function getQuestionsByQuizId($quizId) {
    global $connection;
    
    $stmt = $connection->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get question by ID
 */
function getQuestionById($questionId) {
    global $connection;
    
    $stmt = $connection->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Add question
 */
function addQuestion($quizId, $questionText, $option1, $option2, $option3, $option4, $correctOption) {
    global $connection;
    
    $stmt = $connection->prepare("INSERT INTO questions (quiz_id, question_text, option1, option2, option3, option4, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $quizId, $questionText, $option1, $option2, $option3, $option4, $correctOption);
    
    return $stmt->execute();
}

/**
 * Update question
 */
function updateQuestion($id, $questionText, $option1, $option2, $option3, $option4, $correctOption) {
    global $connection;
    
    $stmt = $connection->prepare("UPDATE questions SET question_text = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ?, correct_option = ? WHERE id = ?");
    $stmt->bind_param("sssssii", $questionText, $option1, $option2, $option3, $option4, $correctOption, $id);
    
    return $stmt->execute();
}

/**
 * Delete question
 */
function deleteQuestion($id) {
    global $connection;
    
    $stmt = $connection->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
}

/**
 * Count questions in a quiz
 */
function countQuestions($quizId) {
    global $connection;
    
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM questions WHERE quiz_id = ?");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'];
}

// ==================== QUIZ ATTEMPT FUNCTIONS ====================

/**
 * Check if quiz_attempts table exists, create if not
 */
function ensureQuizAttemptsTableExists() {
    global $connection;
    
    $result = $connection->query("SHOW TABLES LIKE 'quiz_attempts'");
    
    if ($result->num_rows === 0) {
        $createTableSQL = "
            CREATE TABLE quiz_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                quiz_id INT NOT NULL,
                shuffled_question_ids TEXT NOT NULL COMMENT 'JSON encoded array of shuffled question IDs',
                status ENUM('in_progress', 'completed', 'abandoned') DEFAULT 'in_progress',
                started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at DATETIME NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        $connection->query($createTableSQL);
    }
}

/**
 * Get or create an active quiz attempt for a user
 * Returns the attempt with shuffled question IDs
 */
function getOrCreateQuizAttempt($userId, $quizId) {
    global $connection;
    
    // Ensure table exists
    ensureQuizAttemptsTableExists();
    
    // Check for existing in-progress attempt
    $stmt = $connection->prepare("
        SELECT * FROM quiz_attempts 
        WHERE user_id = ? AND quiz_id = ? AND status = 'in_progress' 
        ORDER BY started_at DESC LIMIT 1
    ");
    $stmt->bind_param("ii", $userId, $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    // Create new attempt with shuffled questions
    $questions = getQuestionsByQuizId($quizId);
    $questionIds = array_column($questions, 'id');
    
    // Shuffle using user-specific seed for unique randomization per user
    $seed = $userId . $quizId . time();
    $shuffledIds = shuffleWithSeed($questionIds, $seed);
    
    $shuffledJson = json_encode($shuffledIds);
    
    $stmt = $connection->prepare("
        INSERT INTO quiz_attempts (user_id, quiz_id, shuffled_question_ids, status) 
        VALUES (?, ?, ?, 'in_progress')
    ");
    $stmt->bind_param("iis", $userId, $quizId, $shuffledJson);
    $stmt->execute();
    
    return [
        'id' => $connection->insert_id,
        'user_id' => $userId,
        'quiz_id' => $quizId,
        'shuffled_question_ids' => $shuffledJson,
        'status' => 'in_progress'
    ];
}

/**
 * Shuffle array with a seed for reproducible randomization
 */
function shuffleWithSeed($array, $seed) {
    $shuffled = $array;
    
    // Convert seed to a numeric value for mt_srand
    $seedValue = abs(crc32($seed));
    
    // Seed the random number generator
    mt_srand($seedValue);
    
    // Use Fisher-Yates shuffle with seeded random
    $count = count($shuffled);
    for ($i = $count - 1; $i > 0; $i--) {
        // Generate random index between 0 and $i
        $j = mt_rand(0, $i);
        
        // Swap elements
        $temp = $shuffled[$i];
        $shuffled[$i] = $shuffled[$j];
        $shuffled[$j] = $temp;
    }
    
    // Reset random seed
    mt_srand();
    
    return $shuffled;
}

/**
 * Get questions in shuffled order based on attempt
 */
function getQuestionsByShuffledOrder($quizId, $shuffledQuestionIds) {
    global $connection;
    
    $questionIds = json_decode($shuffledQuestionIds, true);
    
    if (empty($questionIds)) {
        return getQuestionsByQuizId($quizId);
    }
    
    // Create placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
    $types = str_repeat('i', count($questionIds));
    
    $stmt = $connection->prepare("SELECT * FROM questions WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$questionIds);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    
    // Order questions according to shuffled order
    $orderedQuestions = [];
    $questionMap = [];
    
    foreach ($questions as $question) {
        $questionMap[$question['id']] = $question;
    }
    
    foreach ($questionIds as $id) {
        if (isset($questionMap[$id])) {
            $orderedQuestions[] = $questionMap[$id];
        }
    }
    
    return $orderedQuestions;
}

/**
 * Complete a quiz attempt
 */
function completeQuizAttempt($attemptId) {
    global $connection;
    
    // Ensure table exists
    ensureQuizAttemptsTableExists();
    
    $stmt = $connection->prepare("
        UPDATE quiz_attempts 
        SET status = 'completed', completed_at = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $attemptId);
    
    return $stmt->execute();
}

/**
 * Abandon a quiz attempt (when starting fresh)
 */
function abandonQuizAttempts($userId, $quizId) {
    global $connection;
    
    // Ensure table exists
    ensureQuizAttemptsTableExists();
    
    $stmt = $connection->prepare("
        UPDATE quiz_attempts 
        SET status = 'abandoned' 
        WHERE user_id = ? AND quiz_id = ? AND status = 'in_progress'
    ");
    $stmt->bind_param("ii", $userId, $quizId);
    
    return $stmt->execute();
}

/**
 * Get attempt by ID
 */
function getAttemptById($attemptId) {
    global $connection;
    
    // Ensure table exists
    ensureQuizAttemptsTableExists();
    
    $stmt = $connection->prepare("SELECT * FROM quiz_attempts WHERE id = ?");
    $stmt->bind_param("i", $attemptId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// ==================== RESULT FUNCTIONS ====================

/**
 * Save quiz result
 */
function saveResult($userId, $quizId, $score, $totalQuestions, $correctAnswers, $wrongAnswers) {
    global $connection;
    
    $stmt = $connection->prepare("INSERT INTO results (user_id, quiz_id, score, total_questions, correct_answers, wrong_answers) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiii", $userId, $quizId, $score, $totalQuestions, $correctAnswers, $wrongAnswers);
    
    return $stmt->execute();
}

/**
 * Get user results
 */
function getUserResults($userId) {
    global $connection;
    
    $stmt = $connection->prepare("
        SELECT r.*, q.title as quiz_title 
        FROM results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get all results (for admin)
 */
function getAllResults() {
    global $connection;
    
    $stmt = $connection->prepare("
        SELECT r.*, q.title as quiz_title, u.name as user_name, u.email as user_email 
        FROM results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Check if user has already taken a quiz
 */
function hasUserTakenQuiz($userId, $quizId) {
    global $connection;
    
    $stmt = $connection->prepare("SELECT id FROM results WHERE user_id = ? AND quiz_id = ?");
    $stmt->bind_param("ii", $userId, $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// ==================== USER FUNCTIONS ====================

/**
 * Get all users
 */
function getAllUsers() {
    global $connection;
    
    $stmt = $connection->prepare("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get user by ID
 */
function getUserById($userId) {
    global $connection;
    
    $stmt = $connection->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Count total users
 */
function countUsers() {
    global $connection;
    
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'];
}

/**
 * Count total quizzes
 */
function countQuizzes() {
    global $connection;
    
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM quizzes");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'];
}

/**
 * Count total results
 */
function countResults() {
    global $connection;
    
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM results");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'];
}

/**
 * Get average score
 */
function getAverageScore() {
    global $connection;
    
    $stmt = $connection->prepare("SELECT AVG(score) as avg_score FROM results");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return round($row['avg_score'], 2);
}
?>