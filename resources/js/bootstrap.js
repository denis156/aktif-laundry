import axios from 'axios';
import { initDarkMode, isDarkMode } from './utils/darkMode';
import { initPermissions } from './utils/permissions';
import { setupPWAInstall } from './utils/pwaInstall';
import { chatRoomData, ChatRoomManager } from './utils/chatRoomManager';

// Import Leaflet maps module
import './leaflet/index.js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Initialize dark mode
initDarkMode();

// Initialize permissions
initPermissions();

// Setup PWA install handler
setupPWAInstall();

// Expose utilities globally for Alpine.js
window.isDarkMode = isDarkMode;
window.chatRoomData = chatRoomData;
window.ChatRoomManager = ChatRoomManager;
