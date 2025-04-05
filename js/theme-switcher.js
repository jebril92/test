document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    
    const savedTheme = localStorage.getItem('urlink-theme');
    
    if (savedTheme === 'dark') {
        enableDarkTheme();
    } else {
        enableLightTheme();
    }
    
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    function toggleTheme() {
        const isDarkTheme = document.documentElement.classList.contains('dark-theme');
        
        if (isDarkTheme) {
            enableLightTheme();
            localStorage.setItem('urlink-theme', 'light');
        } else {
            enableDarkTheme();
            localStorage.setItem('urlink-theme', 'dark');
        }
    }
    
    function enableDarkTheme() {
        document.documentElement.classList.add('dark-theme');
        updateThemeToggleIcon(true);
    }
    
    function enableLightTheme() {
        document.documentElement.classList.remove('dark-theme');
        updateThemeToggleIcon(false);
    }
    
    function updateThemeToggleIcon(isDark) {
        if (!themeToggle) return;
        
        if (isDark) {
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            themeToggle.setAttribute('title', 'Passer au thème clair');
        } else {
            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            themeToggle.setAttribute('title', 'Passer au thème sombre');
        }
    }
});