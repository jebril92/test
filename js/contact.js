document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        // Form validation
        contactForm.addEventListener('submit', function(e) {
            let isValid = true;
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const subjectSelect = document.getElementById('subject');
            const messageTextarea = document.getElementById('message');
            
            // Clear previous validation styles
            [nameInput, emailInput, subjectSelect, messageTextarea].forEach(element => {
                element.classList.remove('is-invalid');
            });
            
            // Validate name
            if (nameInput.value.trim() === '') {
                nameInput.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate email with regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailInput.value.trim() === '' || !emailRegex.test(emailInput.value.trim())) {
                emailInput.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate subject
            if (subjectSelect.value === '') {
                subjectSelect.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate message (minimum 20 characters)
            if (messageTextarea.value.trim().length < 20) {
                messageTextarea.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Create alert message if it doesn't exist
                if (!document.querySelector('.alert-danger')) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.textContent = 'Veuillez corriger les erreurs dans le formulaire avant de soumettre.';
                    contactForm.insertBefore(alertDiv, contactForm.firstChild);
                }
                
                // Scroll to top of form
                contactForm.scrollIntoView({ behavior: 'smooth' });
            } else {
                // Show loading state on submit button
                const submitButton = contactForm.querySelector('button[type="submit"]');
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Envoi en cours...';
                submitButton.disabled = true;
            }
        });
        
        // Live validation for fields
        const inputs = contactForm.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    if (this.value.trim() !== '') {
                        this.classList.remove('is-invalid');
                    }
                }
            });
        });
        
        // Special validation for email
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value.trim() !== '' && !emailRegex.test(this.value.trim())) {
                    this.classList.add('is-invalid');
                    
                    // Add custom feedback if it doesn't exist
                    let feedback = this.nextElementSibling;
                    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                        feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = 'Veuillez entrer une adresse email valide.';
                        this.parentNode.appendChild(feedback);
                    }
                }
            });
        }
    }
    
    // Navbar scroll behavior
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('mainNav');
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });
    
    // Animation for contact info cards
    const infoCards = document.querySelectorAll('.contact-info-card');
    infoCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-5px)';
        });
    });
});