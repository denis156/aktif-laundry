/**
 * Dark Mode Utility
 * Handles theme initialization and toggle functionality
 */

// Initialize theme from localStorage or system preference
export function initTheme() {
    const saved = localStorage.getItem('theme');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const shouldBeDark = saved === 'dark' || (!saved && systemDark);

    document.documentElement.setAttribute('data-theme', shouldBeDark ? 'dark' : 'light');
}

// Toggle theme and save to localStorage
export function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.getAttribute('data-theme') === 'dark';
    const newTheme = isDark ? 'light' : 'dark';

    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
}

// Check if dark mode is active
export function isDarkMode() {
    return document.documentElement.getAttribute('data-theme') === 'dark';
}

// Setup Alpine.js Store for dark mode
export function setupAlpineStore() {
    document.addEventListener('alpine:init', () => {
        Alpine.store('darkMode', {
            on: isDarkMode(),

            toggle() {
                this.on = !this.on;
                toggleTheme();
            },

            init() {
                this.on = isDarkMode();
            }
        });
    });
}

// Setup event listener for theme toggle
export function setupThemeListener() {
    window.addEventListener('mary-toggle-theme', () => {
        toggleTheme();
        // Sync Alpine store
        if (window.Alpine && Alpine.store('darkMode')) {
            Alpine.store('darkMode').on = isDarkMode();
        }
    });
}

// Setup Livewire navigation listener
export function setupLivewireListener() {
    document.addEventListener('livewire:navigated', () => {
        initTheme();
        // Sync Alpine store
        if (window.Alpine && Alpine.store('darkMode')) {
            Alpine.store('darkMode').on = isDarkMode();
        }
    });
}

// Initialize everything
export function initDarkMode() {
    initTheme();
    setupAlpineStore();
    setupThemeListener();
    setupLivewireListener();
}
