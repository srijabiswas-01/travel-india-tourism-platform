(function () {
    const root = document.documentElement;
    const storageKey = 'travelIndiaAdminTheme';

    function applyTheme(theme) {
        const nextTheme = theme === 'dark' ? 'dark' : 'light';
        root.dataset.adminTheme = nextTheme;
        localStorage.setItem(storageKey, nextTheme);
        document.querySelectorAll('.admin-theme-toggle').forEach(function (button) {
            const dark = nextTheme === 'dark';
            button.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
            button.setAttribute('title', dark ? 'Switch to light mode' : 'Switch to dark mode');
            const icon = button.querySelector('i');
            if (icon) icon.className = dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(root.dataset.adminTheme || localStorage.getItem(storageKey) || 'light');
        document.querySelectorAll('.admin-theme-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                applyTheme(root.dataset.adminTheme === 'dark' ? 'light' : 'dark');
            });
        });
    });
}());
