document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: "smooth"
                });
            }
        });
    });

    // Highlight active accordion items
    const accordionButtons = document.querySelectorAll('.accordion-button');
    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            accordionButtons.forEach(btn => {
                btn.classList.remove('active-question');
            });
            
            // Add active class if this button is not collapsed
            if (!this.classList.contains('collapsed')) {
                this.classList.add('active-question');
            }
        });
    });
    
    // If URL contains hash, open corresponding accordion item
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const targetAccordion = document.getElementById(`collapse${hash}`);
        
        if (targetAccordion) {
            // Close any open accordion items
            document.querySelectorAll('.accordion-collapse.show').forEach(item => {
                item.classList.remove('show');
                const button = document.querySelector(`[data-bs-target="#${item.id}"]`);
                if (button) {
                    button.classList.add('collapsed');
                    button.setAttribute('aria-expanded', 'false');
                }
            });
            
            // Open the target accordion item
            targetAccordion.classList.add('show');
            const button = document.querySelector(`[data-bs-target="#${targetAccordion.id}"]`);
            if (button) {
                button.classList.remove('collapsed');
                button.setAttribute('aria-expanded', 'true');
                button.classList.add('active-question');
            }
            
            // Scroll to the accordion item
            setTimeout(() => {
                targetAccordion.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }
    }
    
    // Handle search form submission
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }
});