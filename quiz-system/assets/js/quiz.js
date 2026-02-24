/**
 * Online Quiz System - Quiz JavaScript
 */

// Timer variables
let timerInterval;
let timeRemaining;

// Quiz state
let currentQuestion = 0;
let answers = {};

/**
 * Initialize the quiz timer
 */
function initTimer(durationInMinutes) {
    timeRemaining = durationInMinutes * 60; // Convert to seconds
    updateTimerDisplay();
    
    timerInterval = setInterval(function() {
        timeRemaining--;
        updateTimerDisplay();
        
        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            alert('Time is up! Your quiz will be submitted automatically.');
            submitQuiz();
        }
    }, 1000);
}

/**
 * Update the timer display
 */
function updateTimerDisplay() {
    const timerElement = document.getElementById('timer');
    if (!timerElement) return;
    
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    
    timerElement.textContent = 
        String(minutes).padStart(2, '0') + ':' + 
        String(seconds).padStart(2, '0');
    
    // Add warning class when time is low
    if (timeRemaining <= 60) {
        timerElement.parentElement.classList.add('timer-warning');
    }
    
    if (timeRemaining <= 30) {
        timerElement.parentElement.classList.add('timer-danger');
    }
}

/**
 * Select an option for a question
 */
function selectOption(questionId, optionNumber) {
    // Remove selected class from all options of this question
    const options = document.querySelectorAll(`[data-question="${questionId}"]`);
    options.forEach(option => {
        option.classList.remove('selected');
    });
    
    // Add selected class to clicked option
    const selectedOption = document.querySelector(`[data-question="${questionId}"][data-option="${optionNumber}"]`);
    if (selectedOption) {
        selectedOption.classList.add('selected');
        
        // Update radio button
        const radio = selectedOption.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
        }
    }
    
    // Store answer
    answers[questionId] = optionNumber;
    
    // Update progress
    updateProgress();
}

/**
 * Update the progress bar
 */
function updateProgress() {
    const totalQuestions = parseInt(document.getElementById('total-questions')?.value || 0);
    const answeredQuestions = Object.keys(answers).length;
    
    const progressBar = document.getElementById('progress-bar');
    if (progressBar && totalQuestions > 0) {
        const percentage = (answeredQuestions / totalQuestions) * 100;
        progressBar.style.width = percentage + '%';
    }
    
    // Update answered count
    const answeredCount = document.getElementById('answered-count');
    if (answeredCount) {
        answeredCount.textContent = answeredQuestions;
    }
}

/**
 * Submit the quiz
 */
function submitQuiz() {
    // Stop timer
    if (timerInterval) {
        clearInterval(timerInterval);
    }
    
    // Get form and submit
    const form = document.getElementById('quiz-form');
    if (form) {
        // Check if all questions are answered
        const totalQuestions = parseInt(document.getElementById('total-questions')?.value || 0);
        const answeredQuestions = Object.keys(answers).length;
        
        if (answeredQuestions < totalQuestions) {
            const confirm = window.confirm(
                `You have only answered ${answeredQuestions} out of ${totalQuestions} questions. ` +
                `Are you sure you want to submit?`
            );
            if (!confirm) {
                // Restart timer if cancelled
                const duration = parseInt(document.getElementById('quiz-duration')?.value || 10);
                initTimer(duration);
                return;
            }
        }
        
        form.submit();
    }
}

/**
 * Confirm before leaving quiz page
 */
function confirmExit() {
    if (Object.keys(answers).length > 0) {
        return 'You have unsaved answers. Are you sure you want to leave?';
    }
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize timer if on quiz page
    const durationElement = document.getElementById('quiz-duration');
    if (durationElement) {
        const duration = parseInt(durationElement.value);
        initTimer(duration);
    }
    
    // Add click handlers to options
    const options = document.querySelectorAll('.option-item');
    options.forEach(option => {
        option.addEventListener('click', function() {
            const questionId = this.getAttribute('data-question');
            const optionNumber = this.getAttribute('data-option');
            selectOption(questionId, optionNumber);
        });
    });
    
    // Add beforeunload event for quiz page
    if (document.getElementById('quiz-form')) {
        window.addEventListener('beforeunload', confirmExit);
    }
});

/**
 * Sidebar toggle for admin panel
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

/**
 * Modal functions
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

/**
 * Delete confirmation
 */
function confirmDelete(itemType, callback) {
    if (window.confirm(`Are you sure you want to delete this ${itemType}? This action cannot be undone.`)) {
        if (typeof callback === 'function') {
            callback();
        }
        return true;
    }
    return false;
}

/**
 * Form validation
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('error');
            
            // Show error message
            let errorMsg = field.parentElement.querySelector('.error-message');
            if (!errorMsg) {
                errorMsg = document.createElement('span');
                errorMsg.className = 'error-message';
                errorMsg.textContent = 'This field is required';
                field.parentElement.appendChild(errorMsg);
            }
        } else {
            field.classList.remove('error');
            const errorMsg = field.parentElement.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        }
    });
    
    return isValid;
}

/**
 * Auto-hide alerts after 5 seconds
 */
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});