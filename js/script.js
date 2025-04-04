// Navbar Scroll Effect
window.addEventListener('scroll', function() {
  const navbar = document.getElementById('mainNav');
  if (window.scrollY > 50) {
    navbar.classList.add('navbar-scrolled');
  } else {
    navbar.classList.remove('navbar-scrolled');
  }
});

// Active Link Highlighting
const sections = document.querySelectorAll('section[id]');

function highlightNavLink() {
  const scrollY = window.pageYOffset;
  
  sections.forEach(current => {
    const sectionHeight = current.offsetHeight;
    const sectionTop = current.offsetTop - 100;
    const sectionId = current.getAttribute('id');
    
    if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
      document.querySelector('.navbar-nav a[href*=' + sectionId + ']').classList.add('active');
    } else {
      document.querySelector('.navbar-nav a[href*=' + sectionId + ']').classList.remove('active');
    }
  });
}

window.addEventListener('scroll', highlightNavLink);

// URL Shortener Form Functionality
// document.addEventListener('DOMContentLoaded', function() {
//   const shortenForm = document.getElementById('shorten-form');
//   const linkResult = document.getElementById('link-result');
//   const copyBtn = document.getElementById('copy-btn');
  
//   shortenForm.addEventListener('submit', function(e) {
//     e.preventDefault();
//     const longUrl = document.getElementById('long-url').value;
    
//     // In a real application, you would send the URL to your server
//     // For this static demo, we'll just show the result
    
//     // Generate a random short code
//     const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
//     let shortCode = '';
//     for (let i = 0; i < 6; i++) {
//       shortCode += chars.charAt(Math.floor(Math.random() * chars.length));
//     }
    
//     // Display the shortened URL
//     document.getElementById('short-url').textContent = 'https://lnkshrk.fr/' + shortCode;
//     linkResult.style.display = 'block';
    
//     // Smooth scroll to result
//     linkResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
//   });
  
//   // Copy button functionality
//   copyBtn.addEventListener('click', function() {
//     const shortUrl = document.getElementById('short-url').textContent;
//     navigator.clipboard.writeText(shortUrl).then(function() {
//       // Change button text temporarily
//       const originalText = copyBtn.innerHTML;
//       copyBtn.innerHTML = '<i class="fas fa-check me-1"></i> Copié!';
      
//       setTimeout(function() {
//         copyBtn.innerHTML = originalText;
//       }, 2000);
//     });
//   });
  
// });

// Smooth scrolling for anchor links
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
