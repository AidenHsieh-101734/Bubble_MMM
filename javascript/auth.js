// Authentication form handling
document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (loginForm) {
        setupLoginForm();
    }

    if (registerForm) {
        setupRegisterForm();
    }

    setupPasswordToggles();
});

/**
 * Setup login form validation and submission
 */
function setupLoginForm() {
    const form = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        // Validate before submission
        if (!validateEmail(emailInput) || !validatePassword(passwordInput)) {
            return;
        }

        // All validation passed - log success
        console.log('✅ Login validation successful:', { email, password });
    });
}

/**
 * Setup register form validation and submission
 */
function setupRegisterForm() {
    const form = document.getElementById('register-form');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm-password');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const username = usernameInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Validate all fields
        const isUsernameValid = validateUsername(usernameInput);
        const isEmailValid = validateEmail(emailInput);
        const isPasswordValid = validatePassword(passwordInput);
        const isConfirmValid = validateConfirmPassword(passwordInput, confirmPasswordInput);


        if (!isUsernameValid || !isEmailValid || !isPasswordValid || !isConfirmValid) {
            return;
        }

        // All validation passed - log success
        console.log('✅ Registration validation successful:', { username, email, password });
    });
}

/**
 * Setup password visibility toggles
 */
function setupPasswordToggles() {
    const toggles = document.querySelectorAll('.password-toggle');

    toggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();

            const inputId = this.id.replace('-toggle', '');
            const input = document.getElementById(inputId);
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

/**
 * Validation functions
 */
function validateEmail(input) {
    const email = input.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const errorElement = document.getElementById('email-error');

    if (!email) {
        showErrorMessage('email-error', 'Email is verplicht');
        input.parentElement.classList.add('error');
        return false;
    }

    if (!emailRegex.test(email)) {
        showErrorMessage('email-error', 'Voer een geldig e-mailadres in');
        input.parentElement.classList.add('error');
        return false;
    }

    clearError('email-error');
    input.parentElement.classList.remove('error');
    return true;
}

function validatePassword(input) {
    const password = input.value;
    const errorElement = document.getElementById('password-error');

    if (!password) {
        showErrorMessage('password-error', 'Wachtwoord is verplicht');
        input.parentElement.parentElement.classList.add('error');
        return false;
    }

    if (password.length < 6) {
        showErrorMessage('password-error', 'Wachtwoord moet minimaal 6 tekens zijn');
        input.parentElement.parentElement.classList.add('error');
        return false;
    }

    clearError('password-error');
    input.parentElement.parentElement.classList.remove('error');
    return true;
}

function validateUsername(input) {
    const username = input.value.trim();
    const errorElement = document.getElementById('username-error');

    if (!username) {
        showErrorMessage('username-error', 'Gebruikersnaam is verplicht');
        input.parentElement.classList.add('error');
        return false;
    }

    if (username.length < 3) {
        showErrorMessage('username-error', 'Gebruikersnaam moet minimaal 3 tekens zijn');
        input.parentElement.classList.add('error');
        return false;
    }

    if (!/^[a-zA-Z0-9_-]+$/.test(username)) {
        showErrorMessage('username-error', 'Gebruikersnaam mag alleen letters, nummers, _ en - bevatten');
        input.parentElement.classList.add('error');
        return false;
    }

    clearError('username-error');
    input.parentElement.classList.remove('error');
    return true;
}

function validateConfirmPassword(passwordInput, confirmInput) {
    const password = passwordInput.value;
    const confirmPassword = confirmInput.value;
    const errorElement = document.getElementById('confirm-error');

    if (!confirmPassword) {
        showErrorMessage('confirm-error', 'Wachtwoordbevestiging is verplicht');
        confirmInput.parentElement.parentElement.classList.add('error');
        return false;
    }

    if (password !== confirmPassword) {
        showErrorMessage('confirm-error', 'Wachtwoorden komen niet overeen');
        confirmInput.parentElement.parentElement.classList.add('error');
        return false;
    }

    clearError('confirm-error');
    confirmInput.parentElement.parentElement.classList.remove('error');
    return true;
}

/**
 * Helper functions
 */
function showErrorMessage(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.classList.add('show');
    }
}

function clearError(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = '';
        element.classList.remove('show');
    }
}



/**
 * Social authentication handlers (placeholder)
 */
document.addEventListener('DOMContentLoaded', function () {
    const socialButtons = document.querySelectorAll('.social-button');

    socialButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const isGoogle = this.classList.contains('google-button');
            const isGithub = this.classList.contains('github-button');

            if (isGoogle) {
                console.log('Google authentication initiated');
                // Implement Google OAuth
            } else if (isGithub) {
                console.log('GitHub authentication initiated');
                // Implement GitHub OAuth
            }
        });
    });
});
