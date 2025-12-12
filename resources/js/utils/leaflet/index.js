/**
 * Leaflet Module Entry Point
 * Import Leaflet dan export semua komponen
 */

// Import Leaflet core
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Import Leaflet Rotate (extends L.Map with rotation support)
import 'leaflet-rotate';

// Import Leaflet Routing Machine
import 'leaflet-routing-machine';
import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';

// Make Leaflet available globally
window.L = L;

// Export local modules
export { LeafletConfig } from './config.js';
export { LeafletMapManager } from './manager.js';

// Import for global access
import { LeafletConfig } from './config.js';
import { LeafletMapManager } from './manager.js';

// Make everything available globally
if (typeof window !== 'undefined') {
    window.LeafletUtils = {
        // Config
        config: LeafletConfig,
        Config: LeafletConfig,

        // Manager Class
        MapManager: LeafletMapManager,
        LeafletMapManager,
    };
}
