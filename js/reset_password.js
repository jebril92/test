document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');

    const lengthCriteria = document.getElementById('length');
    const uppercaseCriteria = document.getElementById('uppercase');
    const lowercaseCriteria = document.getElementById('lowercase');
    const numberCriteria = document.getElementById('number');
    const specialCriteria = document.getElementById('special');

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;

            if (password.length >= 8) {
                lengthCriteria.classList.add('valid');
                lengthCriteria.classList.remove('invalid');
                lengthCriteria.querySelector('i').className = 'fas fa-check-circle';
            } else {
                lengthCriteria.classList.add('invalid');
                lengthCriteria.classList.remove('valid');
                lengthCriteria.querySelector('i').className = 'fas fa-times-circle';
            }

            if (/[A-Z]/.test(password)) {
                uppercaseCriteria.classList.add('valid');
                uppercaseCriteria.classList.remove('invalid');
                uppercaseCriteria.querySelector('i').className = 'fas fa-check-circle';
            } else {
                uppercaseCriteria.classList.add('invalid');
                uppercaseCriteria.classList.remove('valid');
                uppercaseCriteria.querySelector('i').className = 'fas fa-times-circle';
            }

            if (/[a-z]/.test(password)) {
                lowercaseCriteria.classList.add('valid');
                lowercaseCriteria.classList.remove('invalid');
                lowercaseCriteria.querySelector('i').className = 'fas fa-check-circle';
            } else {
                lowercaseCriteria.classList.add('invalid');
                lowercaseCriteria.classList.remove('valid');
                lowercaseCriteria.querySelector('i').className = 'fas fa-times-circle';
            }

            if (/[0-9]/.test(password)) {
                numberCriteria.classList.add('valid');
                numberCriteria.classList.remove('invalid');
                numberCriteria.querySelector('i').className = 'fas fa-check-circle';
            } else {
                numberCriteria.classList.add('invalid');
                numberCriteria.classList.remove('valid');
                numberCriteria.querySelector('i').className = 'fas fa-times-circle';
            }

            if (/[^A-Za-z0-9]/.test(password)) {
                specialCriteria.classList.add('valid');
                specialCriteria.classList.remove('invalid');
                specialCriteria.querySelector('i').className = 'fas fa-check-circle';
            } else {
                specialCriteria.classList.add('invalid');
                specialCriteria.classList.remove('valid');
                specialCriteria.querySelector('i').className = 'fas fa-times-circle';
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