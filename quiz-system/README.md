# Online Quiz System - QuizMaster

A complete, responsive Online Quiz System built with PHP, MySQL, HTML5, CSS3, and JavaScript.

## Features

### User Side
- User Registration with Name, Email, Password
- User Login / Logout (Session-based authentication)
- Dashboard after login showing available quizzes
- List of available quizzes with details
- Start quiz functionality
- Timer for quiz (JavaScript countdown)
- Multiple Choice Questions (MCQs)
- All questions displayed on one page
- Submit quiz button
- Auto calculate score
- Result page showing:
  - Total Questions
  - Correct Answers
  - Wrong Answers
  - Final Score with percentage
- Results stored in database
- View previous results history

### Admin Side
- Separate Admin Panel
- Admin Login
- Admin Dashboard with statistics
- Add Quiz (Title, Description, Duration, Status)
- Add Questions to Quiz:
  - Question text
  - 4 options (A, B, C, D)
  - Correct answer selection
- Edit Quiz
- Delete Quiz
- Edit Questions
- Delete Questions
- View all registered users
- View all quiz results
- Admin Logout

### Security Features
- Password hashing using `password_hash()` and `password_verify()`
- Prepared statements using mysqli
- SQL injection prevention
- Session protection
- Redirect unauthorized users

### UI Features
- Modern responsive design
- Clean CSS with CSS variables
- Navigation bar
- Sidebar for admin dashboard
- Card-based quiz display
- Mobile responsive layout
- Smooth animations and transitions

---

## Installation Instructions

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP on your computer
3. During installation, make sure to select:
   - Apache
   - MySQL
   - PHP

### Step 2: Start XAMPP Services

1. Open XAMPP Control Panel
2. Click "Start" next to **Apache**
3. Click "Start" next to **MySQL**
4. Both services should show green status

### Step 3: Place Project Files

1. Navigate to your XAMPP installation folder (usually `C:\xampp\` on Windows)
2. Go to the `htdocs` folder
3. Copy the entire `quiz-system` folder into `htdocs`
4. Final path should be: `C:\xampp\htdocs\quiz-system\`

### Step 4: Create Database

#### Method 1: Using phpMyAdmin (Recommended)

1. Open your web browser
2. Go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Click on "Databases" tab
4. Create a new database named `quiz_system`
5. Click "Create"
6. Select the `quiz_system` database from the left sidebar
7. Click on "Import" tab
8. Click "Choose File" and select the `database.sql` file from the `quiz-system` folder
9. Click "Go" at the bottom
10. You should see a success message

#### Method 2: Using MySQL Command Line

1. Open Command Prompt
2. Navigate to MySQL bin folder:
   ```cmd
   cd C:\xampp\mysql\bin
   ```
3. Login to MySQL:
   ```cmd
   mysql -u root -p
   ```
   (Press Enter if no password is set)
4. Create database:
   ```sql
   CREATE DATABASE quiz_system;
   ```
5. Exit MySQL:
   ```sql
   exit
   ```
6. Import the SQL file:
   ```cmd
   mysql -u root -p quiz_system < C:\xampp\htdocs\quiz-system\database.sql
   ```

### Step 5: Configure Database Connection

1. Open `quiz-system/includes/config.php`
2. Verify the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'quiz_system');
   ```
   These are the default XAMPP settings. If you have a different MySQL password, update `DB_PASS`.

### Step 6: Access the Website

1. Open your web browser
2. Go to [http://localhost/quiz-system/](http://localhost/quiz-system/)
3. You should see the homepage

---

## Default Login Credentials

### Admin Login
- **URL:** [http://localhost/quiz-system/admin/login.php](http://localhost/quiz-system/admin/login.php)
- **Username:** `admin`
- **Password:** `admin123`

### User Login
- **URL:** [http://localhost/quiz-system/login.php](http://localhost/quiz-system/login.php)
- **Sample User Email:** `john@example.com`
- **Sample User Password:** `user123`

> **Note:** You can also register a new user account from the registration page.

---

## Project Structure

```
quiz-system/
├── admin/
│   ├── index.php          # Admin dashboard
│   ├── login.php          # Admin login page
│   ├── logout.php         # Admin logout
│   ├── quizzes.php        # Manage quizzes
│   ├── questions.php      # Manage questions
│   ├── users.php          # View users
│   └── results.php        # View all results
├── assets/
│   ├── css/
│   │   ├── style.css      # Main stylesheet
│   │   └── admin.css      # Admin panel styles
│   └── js/
│       └── quiz.js        # Quiz JavaScript (timer, validation)
├── includes/
│   ├── config.php         # Database configuration
│   ├── auth.php           # Authentication functions
│   └── functions.php      # Helper functions
├── index.php              # Homepage
├── login.php              # User login
├── register.php           # User registration
├── dashboard.php          # User dashboard
├── quiz.php               # Take quiz page
├── result.php             # View result page
├── logout.php             # User logout
├── database.sql           # Database schema and sample data
└── README.md              # This file
```

---

## Database Tables

### 1. users
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key, Auto Increment |
| name | VARCHAR(100) | User's full name |
| email | VARCHAR(100) | User's email (unique) |
| password | VARCHAR(255) | Hashed password |
| created_at | TIMESTAMP | Registration date |

### 2. admins
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key, Auto Increment |
| username | VARCHAR(50) | Admin username (unique) |
| password | VARCHAR(255) | Hashed password |
| created_at | TIMESTAMP | Creation date |

### 3. quizzes
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key, Auto Increment |
| title | VARCHAR(255) | Quiz title |
| description | TEXT | Quiz description |
| duration | INT | Duration in minutes |
| status | ENUM | 'active' or 'inactive' |
| created_at | TIMESTAMP | Creation date |

### 4. questions
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key, Auto Increment |
| quiz_id | INT | Foreign Key to quizzes |
| question_text | TEXT | The question |
| option1 | VARCHAR(500) | Option A |
| option2 | VARCHAR(500) | Option B |
| option3 | VARCHAR(500) | Option C |
| option4 | VARCHAR(500) | Option D |
| correct_option | TINYINT | 1, 2, 3, or 4 |
| created_at | TIMESTAMP | Creation date |

### 5. results
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key, Auto Increment |
| user_id | INT | Foreign Key to users |
| quiz_id | INT | Foreign Key to quizzes |
| score | INT | Number of correct answers |
| total_questions | INT | Total questions in quiz |
| correct_answers | INT | Count of correct answers |
| wrong_answers | INT | Count of wrong answers |
| created_at | TIMESTAMP | Date taken |

---

## Usage Guide

### For Users

1. **Register:** Go to the homepage and click "Register" to create an account
2. **Login:** Use your email and password to login
3. **Take Quiz:** From the dashboard, click "Start Quiz" on any available quiz
4. **Answer Questions:** Select your answers for each question
5. **Submit:** Click "Submit Quiz" when done (or wait for timer)
6. **View Results:** See your score immediately after submission
7. **History:** View all your previous results from the dashboard

### For Admins

1. **Login:** Go to `/admin/login.php` and use admin credentials
2. **Dashboard:** View statistics and recent activity
3. **Manage Quizzes:** Add, edit, or delete quizzes
4. **Manage Questions:** Add questions to quizzes with 4 options each
5. **View Users:** See all registered users
6. **View Results:** See all quiz results from all users

---

## Troubleshooting

### "Connection failed" error
- Make sure XAMPP Apache and MySQL services are running
- Check database credentials in `includes/config.php`
- Verify database name is `quiz_system`

### "Page not found" error
- Make sure the project folder is in `htdocs`
- Check the URL is correct: `http://localhost/quiz-system/`

### "Access denied" error
- Check MySQL username and password in `config.php`
- Default XAMPP MySQL user is `root` with no password

### CSS/JS not loading
- Clear browser cache
- Check file paths are correct
- Make sure Apache is running

### Can't login as admin
- Make sure you imported the `database.sql` file
- Default credentials: username `admin`, password `admin123`
- Passwords are hashed, so you can't see them directly in the database

---

## Technologies Used

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla JS)
- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Server:** Apache (XAMPP)
- **Icons:** Font Awesome 6.4.0

---

## Browser Support

- Google Chrome (recommended)
- Mozilla Firefox
- Microsoft Edge
- Safari
- Opera

---

## License

This project is open-source and free to use for educational purposes.

---

## Author

Created as a complete Online Quiz System for educational purposes.

---

## Support

If you encounter any issues:
1. Check the Troubleshooting section above
2. Make sure all files are in place
3. Verify database connection settings
4. Check XAMPP services are running

Enjoy using QuizMaster!