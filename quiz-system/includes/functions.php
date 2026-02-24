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