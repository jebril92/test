document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('needHelp').addEventListener('click', function() {
        window.location.href = '../contact.php';
    });

    const resetForm = document.getElementById('resetPasswordForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(event) {
            const emailInput = this.querySelector('input[type="email"]');
            const emailValue = emailInput.value.trim();
            
            if (!emailValue) {
                event.preventDefault();
                alert('Veuillez entrer votre adresse email.');
                emailInput.focus();
            }
        });
    }
});