// DOM Elements
const signInContainer = document.getElementById('signInContainer');
const signUpContainer = document.getElementById('signUpContainer');
const toggleLink = document.getElementById('toggleLink');
const toggleText = document.getElementById('toggleText');
const formTitle = document.getElementById('form-title');
const messageBox = document.getElementById('message-box');
const messageText = document.getElementById('message-text');
const generateUsernameBtn = document.getElementById('generateUsernameBtn');
const generatePasswordBtn = document.getElementById('generatePasswordBtn');
const usernameInput = document.getElementById('username_signup');
const passwordInput = document.getElementById('password_signup');
const rememberMeCheckbox = document.getElementById('remember_me');
const termsAgreementCheckbox = document.getElementById('terms_agreement');

// Check if there's a saved username in cookies
function checkRememberMe() {
    const rememberedUsername = getCookie('remembered_username');
    if (rememberedUsername) {
        document.getElementById('username_signin').value = rememberedUsername;
        rememberMeCheckbox.checked = true;
    }
}

// Cookie functions
function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Show message function
function showMessage(message, isError = true) {
    messageText.textContent = message;
    messageBox.style.display = 'block';
    
    if (isError) {
        messageBox.className = 'message-box error';
    } else {
        messageBox.className = 'message-box success';
    }
    
    setTimeout(() => {
        messageBox.style.display = 'none';
    }, 5000);
}

// Toggle between Sign In and Sign Up forms
function toggleForms() {
    if (signInContainer.style.display === 'none') {
        // Switch to sign in
        signInContainer.style.display = 'block';
        signUpContainer.style.display = 'none';
        toggleText.textContent = "New to GitHub?";
        toggleLink.textContent = "Create an account.";
        formTitle.textContent = "Sign in to GitHub";
    } else {
        // Switch to sign up
        signInContainer.style.display = 'none';
        signUpContainer.style.display = 'block';
        toggleText.textContent = "Already have an account?";
        toggleLink.textContent = "Sign in.";
        formTitle.textContent = "Create an account";
    }
}

// Event Listeners
toggleLink.addEventListener('click', function(event) {
    event.preventDefault();
    toggleForms();
});

// Form Submission Handling
document.getElementById('signInForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    const username = document.getElementById('username_signin').value;
    const password = document.getElementById('password_signin').value;
    const rememberMe = rememberMeCheckbox.checked;
    
    // Simple validation
    if (!username || !password) {
        showMessage('Please fill in all fields');
        return;
    }
    
    try {
        // Send login request to backend
        const response = await fetch('auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'signin',
                username: username,
                password: password,
                remember_me: rememberMe
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Sign in successful!', false);
            
            // Save username to cookie if "Remember me" is checked
            if (rememberMe) {
                setCookie('remembered_username', username, 30); // Remember for 30 days
            } else {
                setCookie('remembered_username', '', -1); // Delete cookie
            }
            
            // In a real application, you would redirect or set authentication tokens
        } else {
            showMessage(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('An error occurred during sign in');
    }
});

document.getElementById('signUpForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    const username = document.getElementById('username_signup').value;
    const email = document.getElementById('email_signup').value;
    const password = document.getElementById('password_signup').value;
    const termsAgreement = termsAgreementCheckbox.checked;
    
    // Simple validation
    if (!username || !email || !password) {
        showMessage('Please fill in all fields');
        return;
    }
    
    if (!termsAgreement) {
        showMessage('You must agree to the Terms of Service and Privacy Policy');
        return;
    }
    
    if (password.length < 8) {
        showMessage('Password must be at least 8 characters long');
        return;
    }
    
    try {
        // Send signup request to backend
        const response = await fetch('auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'signup',
                username: username,
                email: email,
                password: password
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Account created successfully!', false);
            // Switch to sign in form after successful registration
            setTimeout(() => {
                toggleForms();
            }, 2000);
        } else {
            showMessage(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('An error occurred during sign up');
    }
});

// Generate random username
generateUsernameBtn.addEventListener('click', function() {
    const adjectives = ['clever', 'smart', 'quick', 'bright', 'wise', 'sharp', 'brilliant', 'keen'];
    const nouns = ['fox', 'wolf', 'eagle', 'hawk', 'lion', 'tiger', 'bear', 'owl'];
    const randomNum = Math.floor(Math.random() * 1000);
    
    const adjective = adjectives[Math.floor(Math.random() * adjectives.length)];
    const noun = nouns[Math.floor(Math.random() * nouns.length)];
    
    usernameInput.value = `${adjective}-${noun}-${randomNum}`;
});

// Generate random password
generatePasswordBtn.addEventListener极狐 ('click', function() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
    let password = '';
    
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    passwordInput.value = password;
});

// Check for remembered username on page load
document.addEventListener('DOMContentLoaded', function() {
    checkRememberMe();
});