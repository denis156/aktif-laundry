/**
 * Leaflet Module Entry Point
 * Import Leaflet dan export semua komponen (refactored with OOP + KISS)
 */

// Import Leaflet core
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Import Leaflet Rotate (extends L.Map with rotation support)
import 'leaflet-rotate';

// Import Leaflet Rotated Marker (extends L.Marker with rotation support)
import 'leaflet-rotatedmarker';

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
export { CompassTracker } from './services/compass/index.js';
export { GeocodingService } from './services/GeocodingService.js';

// Import for global access
import { LeafletConfig } from './config.js';
import { LeafletMapManager } from './manager.js';
import { MapManager } from './core/MapManager.js';
import { MarkerManager } from './core/MarkerManager.js';
import { RoutingService } from './services/RoutingService.js';
import { GPSTracker } from './services/GPSTracker.js';
import { CompassTracker } from './services/compass/index.js';
import { GeocodingService } from './services/GeocodingService.js';

// ==========================================
// Helper Functions
// ==========================================

/**
 * Check if Leaflet is available
 * @returns {boolean}
 */
export function isLeafletAvailable() {
    return typeof L !== 'undefined';
}

/**
 * Get config for Leaflet
 * @returns {object} LeafletConfig
 */
export function getConfig() {
    return LeafletConfig;
}

/**
 * Create map manager instance using Leaflet
 *
 * @param {string} elementId - Map container element ID
 * @param {object} options - Map options
 * @param {number} options.latitude - Initial latitude
 * @param {number} options.longitude - Initial longitude
 * @param {number} options.zoom - Zoom level
 * @param {string} options.defaultTileLayer - Default tile layer key
 * @param {boolean} options.draggable - Marker draggable
 * @param {boolean} options.rotate - Enable rotation
 * @param {boolean} options.enableCompass - Enable device compass
 * @param {boolean} options.smoothCompass - Smooth compass rotation
 * @param {boolean} options.showLayerControl - Show layer control
 * @param {function} options.onLocationUpdate - Location update callback
 * @param {function} options.onMapClick - Map click callback
 * @param {object} options.wire - Livewire component reference
 * @returns {LeafletMapManager|null}
 */
export function createMap(elementId, options = {}) {
    if (isLeafletAvailable()) {
        return new LeafletMapManager(elementId, options);
    }

    console.error('Leaflet not available');
    return null;
}

/**
 * Wait for Leaflet to be ready
 * @param {function} callback - Callback when ready
 * @param {number} timeout - Timeout in ms (default: 10000)
 */
export function waitForMaps(callback, timeout = 10000) {
    const startTime = Date.now();

    const check = () => {
        if (isLeafletAvailable()) {
            callback();
            return;
        }

        if (Date.now() - startTime > timeout) {
            console.error('Leaflet timeout');
            return;
        }

        setTimeout(check, 100);
    };

    check();
}

/**
 * Create marker icon based on type
 * @param {string} type - Icon type: 'default', 'home', 'motor', 'mobil'
 * @returns {object} Icon config
 */
export function createIcon(type = 'default') {
    switch (type) {
        case 'home':
            return LeafletConfig.createHomeMarkerIcon ? LeafletConfig.createHomeMarkerIcon() : LeafletConfig.homeMarkerIcon;
        case 'motor':
            return LeafletConfig.createMotorMarkerIcon ? LeafletConfig.createMotorMarkerIcon() : LeafletConfig.motorMarkerIcon;
        case 'mobil':
            return LeafletConfig.createMobilMarkerIcon ? LeafletConfig.createMobilMarkerIcon() : LeafletConfig.mobilMarkerIcon;
        default:
            return LeafletConfig.createMarkerIcon ? LeafletConfig.createMarkerIcon() : LeafletConfig.markerIcon;
    }
}

/**
 * Get default coordinates
 * @returns {object} {latitude, longitude}
 */
export function getDefaultCoordinates() {
    return LeafletConfig.defaultCoordinates;
}

/**
 * Get default zoom levels
 * @returns {object} {default, detail, city}
 */
export function getZoomLevels() {
    return LeafletConfig.zoom;
}

/**
 * Get GPS options
 * @returns {object} GPS options
 */
export function getGpsOptions() {
    return LeafletConfig.gpsOptions;
}

// ==========================================
// Global Exports
// ==========================================

// Make everything available globally
if (typeof window !== 'undefined') {
    // LeafletUtils namespace (for advanced usage)
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

    // Maps namespace (primary API - backward compatible)
    window.Maps = {
        // Check availability
        isLeafletAvailable,

        // Factory
        createMap,
        waitForMaps,

        // Config helpers
        getConfig,
        createIcon,
        getDefaultCoordinates,
        getZoomLevels,
        getGpsOptions,

        // Direct access to Leaflet utils
        get LeafletConfig() {
            return LeafletConfig;
        },
        get LeafletMapManager() {
            return LeafletMapManager;
        },
    };
}
