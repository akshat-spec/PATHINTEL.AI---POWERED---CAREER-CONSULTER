/**
 * Simple Dark Mode Toggle
 * Persists user preference via localStorage
 */

document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('dark-mode-toggle');
    const body = document.body;

    // Check saved preference
    const darkMode = localStorage.getItem('darkMode');

    // Enable dark mode if saved or if system preference matches (optional)
    if (darkMode === 'enabled') {
        body.classList.add('dark-mode');
        updateIcon(true);
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            body.classList.toggle('dark-mode');

            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                updateIcon(true);
            } else {
                localStorage.setItem('darkMode', 'disabled');
                updateIcon(false);
            }
        });
    }

    function updateIcon(isDark) {
        if (!toggleBtn) return;
        const iconInfo = toggleBtn.querySelector('i');
        const textSpan = toggleBtn.querySelector('span');

        if (isDark) {
            if (iconInfo) iconInfo.className = 'fa fa-sun-o';
            if (textSpan) textSpan.textContent = ' Light Mode';
        } else {
            if (iconInfo) iconInfo.className = 'fa fa-moon-o';
            if (textSpan) textSpan.textContent = ' Dark Mode';
        }
    }
});
