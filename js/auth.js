// auth.js - Authentication functions for AnatomyAR Pro

/**
 * Handle user signup
 * @param {Event} event - Form submit event
 */
function handleSignup(event) {
    event.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    // Clear previous error messages
    document.getElementById('error-message').textContent = '';
    
    // Validate passwords match
    if (password !== confirmPassword) {
        document.getElementById('error-message').textContent = 'Passwords do not match';
        return;
    }
    
    // Prepare data for API
    const userData = {
        email: email,
        password: password
    };
    
    // Show loading state
    document.getElementById('signup-btn').textContent = 'Creating account...';
    document.getElementById('signup-btn').disabled = true;
    
    // Send signup request to API
    fetch('api/controllers/signup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            // Signup successful
            alert('Account created successfully! Please log in.');
            window.location.href = 'login.html';
        } else {
            // Signup failed
            document.getElementById('error-message').textContent = data.message;
            document.getElementById('signup-btn').textContent = 'Sign Up';
            document.getElementById('signup-btn').disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('error-message').textContent = 'An error occurred. Please try again.';
        document.getElementById('signup-btn').textContent = 'Sign Up';
        document.getElementById('signup-btn').disabled = false;
    });
}

/**
 * Handle user login
 * @param {Event} event - Form submit event
 */
function handleLogin(event) {
    event.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    // Clear previous error messages
    document.getElementById('error-message').textContent = '';
    
    // Prepare data for API
    const userData = {
        email: email,
        password: password
    };
    
    // Show loading state
    document.getElementById('login-btn').textContent = 'Logging in...';
    document.getElementById('login-btn').disabled = true;
    
    // Send login request to API
    fetch('api/controllers/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            // Login successful
            // Store user data in localStorage (in a real app, you might use sessions or JWT)
            localStorage.setItem('currentUser', JSON.stringify({
                id: data.id,
                email: data.email
            }));
            
            // Redirect to dashboard
            window.location.href = 'dashboard.html';
        } else {
            // Login failed
            document.getElementById('error-message').textContent = data.message;
            document.getElementById('login-btn').textContent = 'Log In';
            document.getElementById('login-btn').disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('error-message').textContent = 'An error occurred. Please try again.';
        document.getElementById('login-btn').textContent = 'Log In';
        document.getElementById('login-btn').disabled = false;
    });
}

/**
 * Check if user is logged in
 * @returns {boolean} - True if user is logged in
 */
function isLoggedIn() {
    return localStorage.getItem('currentUser') !== null;
}

/**
 * Log out user
 */
function logout() {
    localStorage.removeItem('currentUser');
    window.location.href = 'login.html';
}

// Add event listeners if elements exist
document.addEventListener('DOMContentLoaded', function() {
    // For signup page
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', handleSignup);
    }
    
    // For login page
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Redirect if not logged in (for protected pages)
    if (window.location.pathname.includes('dashboard.html') && !isLoggedIn()) {
        window.location.href = 'login.html';
    }
}); 