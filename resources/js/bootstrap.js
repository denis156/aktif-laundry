import axios from 'axios';
import { initDarkMode, isDarkMode } from './utils/darkMode';
import { initPermissions } from './utils/permissions';
import './utils/leaflet';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Initialize dark mode
initDarkMode();

// Initialize permissions
initPermissions();

// Expose isDarkMode globally for Alpine.js
window.isDarkMode = isDarkMode;
