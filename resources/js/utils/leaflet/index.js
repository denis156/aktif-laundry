/**
 * Leaflet Module Entry Point
 * Import Leaflet dan export semua komponen (refactored with OOP + KISS)
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

// Export config
export { LeafletConfig } from './config.js';

// Export main manager (facade)
export { LeafletMapManager } from './manager.js';

// Export core managers (for advanced usage)
export { MapManager } from './core/MapManager.js';
export { MarkerManager } from './core/MarkerManager.js';

// Export services (for advanced usage)
export { RoutingService } from './services/RoutingService.js';
export { GPSTracker } from './services/GPSTracker.js';
export { CompassTracker } from './services/CompassTracker.js';
export { GeocodingService } from './services/GeocodingService.js';

// Import for global access
import { LeafletConfig } from './config.js';
import { LeafletMapManager } from './manager.js';
import { MapManager } from './core/MapManager.js';
import { MarkerManager } from './core/MarkerManager.js';
import { RoutingService } from './services/RoutingService.js';
import { GPSTracker } from './services/GPSTracker.js';
import { CompassTracker } from './services/CompassTracker.js';
import { GeocodingService } from './services/GeocodingService.js';

// Make everything available globally
if (typeof window !== 'undefined') {
    window.LeafletUtils = {
        // Config
        config: LeafletConfig,
        Config: LeafletConfig,

        // Main Manager (facade - recommended for most use cases)
        MapManager: LeafletMapManager,
        LeafletMapManager,

        // Core managers (for advanced usage)
        core: {
            MapManager,
            MarkerManager,
        },

        // Services (for advanced usage)
        services: {
            RoutingService,
            GPSTracker,
            CompassTracker,
            GeocodingService,
        },
    };
}
