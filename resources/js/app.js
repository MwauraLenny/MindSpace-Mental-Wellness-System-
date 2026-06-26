import Alpine from 'alpinejs';

window.Alpine = Alpine;

const THEME_KEY = 'mindspace-theme';

const syncThemeIcons = () => {
    const isDark = document.documentElement.classList.contains('dark');

    document.querySelectorAll('[data-theme-icon-sun]').forEach((icon) => {
        icon.classList.toggle('hidden', isDark);
    });

    document.querySelectorAll('[data-theme-icon-moon]').forEach((icon) => {
        icon.classList.toggle('hidden', !isDark);
    });
};

const applyTheme = (theme) => {
    const shouldUseDark = theme === 'dark';
    document.documentElement.classList.toggle('dark', shouldUseDark);
    localStorage.setItem(THEME_KEY, shouldUseDark ? 'dark' : 'light');
    syncThemeIcons();
};

const initTheme = () => {
    const storedTheme = localStorage.getItem(THEME_KEY);
    if (storedTheme === 'dark' || storedTheme === 'light') {
        document.documentElement.classList.toggle('dark', storedTheme === 'dark');
    }

    syncThemeIcons();

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const isDark = document.documentElement.classList.contains('dark');
            applyTheme(isDark ? 'light' : 'dark');
        });
    });
};

Alpine.start();

document.addEventListener('DOMContentLoaded', initTheme);
