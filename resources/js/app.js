import Alpine from 'alpinejs';

window.Alpine = Alpine;

const THEME_KEY = 'mindspace-theme-v2';
const LEGACY_THEME_KEY = 'mindspace-theme';
const THEME_RESET_KEY = 'mindspace-theme-reset-version';
const THEME_RESET_VERSION = '2026-06-29';

const getStoredTheme = () => {
    if (localStorage.getItem(THEME_RESET_KEY) !== THEME_RESET_VERSION) {
        localStorage.setItem(THEME_KEY, 'light');
        localStorage.removeItem(LEGACY_THEME_KEY);
        localStorage.setItem(THEME_RESET_KEY, THEME_RESET_VERSION);
        return 'light';
    }

    const theme = localStorage.getItem(THEME_KEY);
    if (theme === 'dark' || theme === 'light') {
        return theme;
    }

    // Drop old theme key and start from a consistent light default.
    localStorage.removeItem(LEGACY_THEME_KEY);
    localStorage.setItem(THEME_KEY, 'light');
    return 'light';
};

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
    const theme = getStoredTheme();
    document.documentElement.classList.toggle('dark', theme === 'dark');

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
