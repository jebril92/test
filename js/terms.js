document.addEventListener('DOMContentLoaded', function() {
    const scrollLinks = document.querySelectorAll('.scrollto');
    
    scrollLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const headerOffset = 90;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
                
                scrollLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
    
    function highlightNavLinks() {
        const sections = document.querySelectorAll('section[id]');
        const scrollPosition = window.pageYOffset + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPosition >= sectionTop && 
                scrollPosition < sectionTop + sectionHeight) {
                
                document.querySelector(`.scrollto[href="#${sectionId}"]`)?.classList.add('active');
            } else {
                document.querySelector(`.scrollto[href="#${sectionId}"]`)?.classList.remove('active');
            }
        });
    }
    
    highlightNavLinks();
    window.addEventListener('scroll', highlightNavLinks);
});