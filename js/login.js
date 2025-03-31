document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('container');
    const registerBtn = document.getElementById('register');
    const loginBtn = document.getElementById('login');
    const mobileRegisterBtn = document.getElementById('mobile-register');
    const mobileLoginBtn = document.getElementById('mobile-login');
    const passwordInput = document.getElementById('register-password');
    const confirmInput = document.getElementById('confirm-password');
    const strengthBar = document.getElementById('password-strength-bar');

    const lengthCriteria = document.getElementById('length');
    const uppercaseCriteria = document.getElementById('uppercase');
    const lowercaseCriteria = document.getElementById('lowercase');
    const numberCriteria = document.getElementById('number');
    const specialCriteria = document.getElementById('special');

    if (registerBtn) {
        registerBtn.addEventListener('click', () => {
            container.classList.add('active');
        });
    }

    if (loginBtn) {
        loginBtn.addEventListener('click', () => {
            container.classList.remove('active');
        });
    }

    if (mobileRegisterBtn) {
        mobileRegisterBtn.addEventListener('click', () => {
            container.classList.add('active');
        });
    }

    if (mobileLoginBtn) {
        mobileLoginBtn.addEventListener('click', () => {
            container.classList.remove('active');
        });
    }

    function updatePasswordStrength(password) {
        let strength = 0;

        if (password.length >= 8) strength += 20;
        if (/[A-Z]/.test(password)) strength += 20;
        if (/[a-z]/.test(password)) strength += 20;
        if (/[0-9]/.test(password)) strength += 20;
        if (/[^A-Za-z0-9]/.test(password)) strength += 20;

        if (strengthBar) {
            strengthBar.style.width = strength + '%';

            if (strength <= 40) {
                strengthBar.style.backgroundColor = '#dc3545';
            } else if (strength <= 80) {
                strengthBar.style.backgroundColor = '#ffc107';
            } else {
                strengthBar.style.backgroundColor = '#28a745';
            }
        }
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            updatePasswordStrength(password);

            if (lengthCriteria) {
                if (password.length >= 8) {
                    lengthCriteria.innerHTML = '<i class="fas fa-check-circle" style="color: #28a745;"></i> Au moins 8 caractères';
                } else {
                    lengthCriteria.innerHTML = '<i class="fas fa-times-circle" style="color: #dc3545;"></i> Au moins 8 caractères';
                }
            }

            if (uppercaseCriteria) {
                if (/[A-Z]/.test(password)) {
                    uppercaseCriteria.innerHTML = '<i class="fas fa-check-circle" style="color: #28a745;"></i> Au moins 1 majuscule';
                } else {
                    uppercaseCriteria.innerHTML = '<i class="fas fa-times-circle" style="color: #dc3545;"></i> Au moins 1 majuscule';
                }
            }

            if (lowercaseCriteria) {
                if (/[a-z]/.test(password)) {
                    lowercaseCriteria.innerHTML = '<i class="fas fa-check-circle" style="color: #28a745;"></i> Au moins 1 minuscule';
                } else {
                    lowercaseCriteria.innerHTML = '<i class="fas fa-times-circle" style="color: #dc3545;"></i> Au moins 1 minuscule';
                }
            }

            if (numberCriteria) {
                if (/[0-9]/.test(password)) {
                    numberCriteria.innerHTML = '<i class="fas fa-check-circle" style="color: #28a745;"></i> Au moins 1 chiffre';
                } else {
                    numberCriteria.innerHTML = '<i class="fas fa-times-circle" style="color: #dc3545;"></i> Au moins 1 chiffre';
                }
            }

            if (specialCriteria) {
                if (/[^A-Za-z0-9]/.test(password)) {
                    specialCriteria.innerHTML = '<i class="fas fa-check-circle" style="color: #28a745;"></i> Au moins 1 caractère spécial';
                } else {
                    specialCriteria.innerHTML = '<i class="fas fa-times-circle" style="color: #dc3545;"></i> Au moins 1 caractère spécial';
                }
            }
        });
    }

    if (confirmInput && passwordInput) {
        confirmInput.addEventListener('input', function() {
            if (this.value === passwordInput.value) {
                this.style.borderColor = '#28a745';
            } else {
                this.style.borderColor = '#dc3545';
            }
        });
    }
});