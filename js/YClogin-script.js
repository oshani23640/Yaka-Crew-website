// Login System JavaScript

class LoginSystem {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.checkExistingSession();
    }

    bindEvents() {
        // Form submission
        document.getElementById('login-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });

        // Password toggle
        document.getElementById('toggle-password').addEventListener('click', () => {
            this.togglePasswordVisibility();
        });

        // Enter key on form fields
        document.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.handleLogin();
            }
        });

        // Input focus effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', () => {
                this.clearError();
            });
        });
    }

    async handleLogin() {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const rememberMe = document.getElementById('remember-me').checked;

        // Validation
        if (!username || !password) {
            this.showError('Please enter both username and password');
            return;
        }

        // Show loading state
        this.setLoadingState(true);

        try {
            // Create form data for PHP
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('username', username);
            formData.append('password', password);

            // Make request to PHP login handler
            const response = await fetch('login-handler.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.handleSuccessfulLogin(username, rememberMe, result.data);
            } else {
                this.handleFailedLogin(result.message || 'Invalid credentials');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.handleFailedLogin('Connection error. Please try again.');
        }
    }

    // Remove the validateCredentials method as it's no longer needed

    handleSuccessfulLogin(username, rememberMe, userData = {}) {
        // Store session
        const sessionData = {
            username: username,
            loginTime: new Date().toISOString(),
            rememberMe: rememberMe,
            userData: userData
        };

        if (rememberMe) {
            localStorage.setItem('yakaCrew_adminSession', JSON.stringify(sessionData));
        } else {
            sessionStorage.setItem('yakaCrew_adminSession', JSON.stringify(sessionData));
        }

        // Show success message
        this.showToast('Login successful! Redirecting...', 'success');

        // Redirect to admin panel
        setTimeout(() => {
            window.location.href = 'admin/YCadmin.php';
        }, 1000);
    }

    handleFailedLogin(message = 'Invalid username or password. Please try again.') {
        this.setLoadingState(false);
        this.showError(message);
        
        // Clear password field
        document.getElementById('password').value = '';
        
        // Focus back to username
        document.getElementById('username').focus();
        
        // Add shake animation to form
        const form = document.querySelector('.login-form');
        form.style.animation = 'shake 0.5s ease-in-out';
        setTimeout(() => {
            form.style.animation = '';
        }, 500);
    }

    setLoadingState(loading) {
        const btn = document.getElementById('login-btn');
        const btnText = document.querySelector('.btn-text');
        const spinner = document.querySelector('.loading-spinner');

        if (loading) {
            btn.disabled = true;
            btnText.style.opacity = '0';
            spinner.classList.remove('hidden');
        } else {
            btn.disabled = false;
            btnText.style.opacity = '1';
            spinner.classList.add('hidden');
        }
    }

    togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('toggle-password');
        const icon = toggleBtn.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }

    showError(message) {
        const errorElement = document.getElementById('error-message');
        errorElement.textContent = message;
        errorElement.classList.add('show');

        // Auto-hide after 5 seconds
        setTimeout(() => {
            this.clearError();
        }, 5000);
    }

    clearError() {
        const errorElement = document.getElementById('error-message');
        errorElement.classList.remove('show');
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        
        document.getElementById('toast-container').appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 4000);
    }

    async checkExistingSession() {
        // Check if user is already logged in
        const sessionData = localStorage.getItem('yakaCrew_adminSession') || 
                          sessionStorage.getItem('yakaCrew_adminSession');

        if (sessionData) {
            try {
                const session = JSON.parse(sessionData);
                const loginTime = new Date(session.loginTime);
                const now = new Date();
                const timeDiff = now - loginTime;
                const hoursDiff = timeDiff / (1000 * 60 * 60);

                // Session valid for 24 hours, but verify with server
                if (hoursDiff < 24) {
                    // Verify session with server
                    try {
                        const formData = new FormData();
                        formData.append('action', 'verify');
                        
                        const response = await fetch('login-handler.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();
                        
                        if (result.success) {
                            this.showToast('Existing session found. Redirecting...', 'success');
                            setTimeout(() => {
                                window.location.href = 'admin.html';
                            }, 1000);
                            return;
                        }
                    } catch (error) {
                        console.error('Session verification error:', error);
                    }
                }
            } catch (error) {
                console.error('Error parsing session data:', error);
            }
        }

        // No valid session, stay on login page
        this.focusFirstInput();
    }

    focusFirstInput() {
        setTimeout(() => {
            document.getElementById('username').focus();
        }, 500);
    }

    // Static method to check authentication from other pages
    static isAuthenticated() {
        const sessionData = localStorage.getItem('yakaCrew_adminSession') || 
                          sessionStorage.getItem('yakaCrew_adminSession');

        if (!sessionData) return false;

        try {
            const session = JSON.parse(sessionData);
            const loginTime = new Date(session.loginTime);
            const now = new Date();
            const timeDiff = now - loginTime;
            const hoursDiff = timeDiff / (1000 * 60 * 60);

            return hoursDiff < 24; // 24-hour session
        } catch (error) {
            return false;
        }
    }

    // Static method to get current user
    static getCurrentUser() {
        const sessionData = localStorage.getItem('yakaCrew_adminSession') || 
                          sessionStorage.getItem('yakaCrew_adminSession');

        if (!sessionData) return null;

        try {
            const session = JSON.parse(sessionData);
            return session.username;
        } catch (error) {
            return null;
        }
    }

    // Static method to logout
    static async logout() {
        try {
            // Call logout handler
            const formData = new FormData();
            formData.append('action', 'logout');
            
            await fetch('login-handler.php', {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.error('Logout error:', error);
        }
        
        localStorage.removeItem('yakaCrew_adminSession');
        sessionStorage.removeItem('yakaCrew_adminSession');
        window.location.href = 'login.html';
    }
}

// Initialize login system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new LoginSystem();
});

// Add shake animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
`;
document.head.appendChild(style);
