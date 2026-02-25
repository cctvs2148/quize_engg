-- Online Quiz System Database
-- Created for XAMPP MySQL

-- Drop existing tables if they exist
DROP TABLE IF EXISTS results;
DROP TABLE IF EXISTS quiz_attempts;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS quizzes;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;

-- Create admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create password_resets table for password reset functionality
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create quizzes table
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT NOT NULL DEFAULT 10 COMMENT 'Duration in minutes',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create questions table
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option1 VARCHAR(500) NOT NULL,
    option2 VARCHAR(500) NOT NULL,
    option3 VARCHAR(500) NOT NULL,
    option4 VARCHAR(500) NOT NULL,
    correct_option TINYINT NOT NULL COMMENT '1, 2, 3, or 4',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create quiz_attempts table for storing shuffled question order
CREATE TABLE quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    shuffled_question_ids TEXT NOT NULL COMMENT 'JSON encoded array of shuffled question IDs',
    status ENUM('in_progress', 'completed', 'abandoned') DEFAULT 'in_progress',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_active_attempt (user_id, quiz_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create results table
CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    correct_answers INT NOT NULL,
    wrong_answers INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (password: admin123)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$RVXvQaKJVoSHt1GXIopcPujVhZxzmSfY2hLPPzJMxdZRL5TJQMoZ2');

-- Insert sample quiz
INSERT INTO quizzes (title, description, duration, status) VALUES 
('PHP Basics Quiz', 'Test your knowledge of PHP fundamentals including variables, arrays, and functions.', 10, 'active'),
('JavaScript Fundamentals', 'A quiz covering JavaScript basics like variables, functions, and DOM manipulation.', 15, 'active'),
('HTML & CSS Quiz', 'Test your understanding of HTML elements and CSS styling.', 10, 'active');

-- Insert sample questions for PHP Quiz (quiz_id = 1)
INSERT INTO questions (quiz_id, question_text, option1, option2, option3, option4, correct_option) VALUES
(1, 'What does PHP stand for?', 'Personal Home Page', 'Hypertext Preprocessor', 'Private Home Page', 'Personal Hypertext Processor', 2),
(1, 'Which symbol is used to declare a variable in PHP?', '$', '@', '#', '&', 1),
(1, 'Which function is used to get the length of a string in PHP?', 'strlen()', 'strlength()', 'length()', 'str_len()', 1),
(1, 'How do you start a PHP block?', '<?php', '<php', '<?', 'Both A and C', 4),
(1, 'Which array is used to collect form data with POST method?', '$_POST', '$_GET', '$_FORM', '$_REQUEST', 1);

-- Insert sample questions for JavaScript Quiz (quiz_id = 2)
INSERT INTO questions (quiz_id, question_text, option1, option2, option3, option4, correct_option) VALUES
(2, 'Which keyword is used to declare a constant in JavaScript?', 'const', 'let', 'var', 'constant', 1),
(2, 'What is the correct way to create a function in JavaScript?', 'function myFunction()', 'function:myFunction()', 'function = myFunction()', 'create myFunction()', 1),
(2, 'How do you call a function named "myFunction"?', 'myFunction()', 'call myFunction()', 'call function myFunction()', 'execute myFunction()', 1),
(2, 'Which method is used to add an element at the end of an array?', 'push()', 'pop()', 'shift()', 'unshift()', 1),
(2, 'What does DOM stand for?', 'Document Object Model', 'Data Object Model', 'Document Oriented Model', 'Data Oriented Model', 1);

-- Insert sample questions for HTML & CSS Quiz (quiz_id = 3)
INSERT INTO questions (quiz_id, question_text, option1, option2, option3, option4, correct_option) VALUES
(3, 'What does HTML stand for?', 'Hyper Text Markup Language', 'High Text Machine Language', 'Hyper Tabular Markup Language', 'None of the above', 1),
(3, 'Which tag is used for the largest heading?', '<h1>', '<h6>', '<heading>', '<head>', 1),
(3, 'What does CSS stand for?', 'Cascading Style Sheets', 'Creative Style Sheets', 'Computer Style Sheets', 'Colorful Style Sheets', 1),
(3, 'Which property is used to change the background color in CSS?', 'background-color', 'bgcolor', 'color', 'background', 1),
(3, 'Which HTML attribute is used to define inline styles?', 'style', 'class', 'id', 'styles', 1);

-- Insert a sample user (password: user123)
INSERT INTO users (name, email, password) VALUES 
('John Doe', 'john@example.com', '$2y$10$1RJatfQmuMHMToEA0GdoS.ExQWACh8kH032IK90VIoutg5OEZetO6');
