/**
 * Maps Entry Point - Leaflet Only
 *
 * Usage:
 *   const map = window.Maps.createMap('map-id', options);
 */

// Import Leaflet
import './leaflet/index.js';

// Helper to safely get configs from global window
function getLeafletUtils() {
    return window.LeafletUtils || {};
}

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
    const leafletUtils = getLeafletUtils();
    return leafletUtils.config || null;
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
    const leafletUtils = getLeafletUtils();

    if (leafletUtils.LeafletMapManager && isLeafletAvailable()) {
        return new leafletUtils.LeafletMapManager(elementId, options);
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
 * @param {string} type - Icon type: 'default', 'home', 'arrow'
 * @returns {object} Icon config
 */
export function createIcon(type = 'default') {
    const config = getConfig();
    if (!config) return null;

    switch (type) {
        case 'home':
            return config.createHomeMarkerIcon ? config.createHomeMarkerIcon() : config.homeMarkerIcon;
        case 'arrow':
            return config.createArrowMarkerIcon ? config.createArrowMarkerIcon() : config.arrowMarkerIcon;
        default:
            return config.createMarkerIcon ? config.createMarkerIcon() : config.markerIcon;
    }
}

/**
 * Get default coordinates
 * @returns {object} {latitude, longitude}
 */
export function getDefaultCoordinates() {
    const config = getConfig();
    return config ? config.defaultCoordinates : { latitude: -3.9778, longitude: 122.5145 };
}

/**
 * Get default zoom levels
 * @returns {object} {default, detail, city}
 */
export function getZoomLevels() {
    const config = getConfig();
    return config ? config.zoom : { default: 15, detail: 16, city: 13 };
}

/**
 * Get GPS options
 * @returns {object} GPS options
 */
export function getGpsOptions() {
    const config = getConfig();
    return config ? config.gpsOptions : {
        enableHighAccuracy: true,
        timeout: 30000,
        maximumAge: 5000,
    };
}

// Make everything available globally for backward compatibility
if (typeof window !== 'undefined') {
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
            return getLeafletUtils().config;
        },
        get LeafletMapManager() {
            return getLeafletUtils().LeafletMapManager;
        },
    };
}
