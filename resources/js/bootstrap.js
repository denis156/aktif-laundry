import axios from 'axios';
import { initDarkMode, isDarkMode } from './utils/darkMode';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Initialize dark mode
initDarkMode();

// Expose isDarkMode globally for Alpine.js
window.isDarkMode = isDarkMode;
