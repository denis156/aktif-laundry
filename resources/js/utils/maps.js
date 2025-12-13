/**
 * Unified Maps Entry Point
 * Automatic provider selection: Mapbox (primary) → Leaflet (fallback)
 *
 * Usage:
 *   const map = window.Maps.createMap('map-id', options);
 */

// Import Mapbox first (primary)
import './mapbox/index.js';

// Import Leaflet as fallback
import './leaflet/index.js';

// Helper to safely get configs from global window
function getMapboxUtils() {
    return window.MapboxUtils || {};
}

function getLeafletUtils() {
    return window.LeafletUtils || {};
}

/**
 * Map Provider Enum
 */
export const MapProvider = {
    MAPBOX: 'mapbox',
    LEAFLET: 'leaflet',
};

/**
 * Get current map provider
 * @returns {string} 'mapbox' or 'leaflet'
 */
export function getCurrentProvider() {
    // Check Mapbox availability
    const mapboxUtils = getMapboxUtils();
    if (typeof mapboxgl !== 'undefined' && mapboxUtils.config && mapboxUtils.config.isAvailable && mapboxUtils.config.isAvailable()) {
        return MapProvider.MAPBOX;
    }

    // Fallback to Leaflet
    if (typeof L !== 'undefined') {
        return MapProvider.LEAFLET;
    }

    console.error('No map provider available');
    return null;
}

/**
 * Check if Mapbox is available
 * @returns {boolean}
 */
export function isMapboxAvailable() {
    const mapboxUtils = getMapboxUtils();
    return typeof mapboxgl !== 'undefined' && mapboxUtils.config && mapboxUtils.config.isAvailable && mapboxUtils.config.isAvailable();
}

/**
 * Check if Leaflet is available
 * @returns {boolean}
 */
export function isLeafletAvailable() {
    return typeof L !== 'undefined';
}

/**
 * Get config for current provider
 * @returns {object} MapboxConfig or LeafletConfig
 */
export function getConfig() {
    const provider = getCurrentProvider();

    if (provider === MapProvider.MAPBOX) {
        const mapboxUtils = getMapboxUtils();
        return mapboxUtils.config || null;
    }

    if (provider === MapProvider.LEAFLET) {
        const leafletUtils = getLeafletUtils();
        return leafletUtils.config || null;
    }

    return null;
}

/**
 * Create map manager instance (auto-selects provider)
 *
 * @param {string} elementId - Map container element ID
 * @param {object} options - Map options
 * @param {number} options.latitude - Initial latitude
 * @param {number} options.longitude - Initial longitude
 * @param {number} options.zoom - Zoom level
 * @param {string} options.style - Mapbox style (ignored by Leaflet)
 * @param {boolean} options.draggable - Marker draggable
 * @param {boolean} options.rotate - Enable rotation
 * @param {boolean} options.enableCompass - Enable device compass
 * @param {boolean} options.smoothCompass - Smooth compass rotation
 * @param {boolean} options.showLayerControl - Show layer control (Leaflet only)
 * @param {function} options.onLocationUpdate - Location update callback
 * @param {function} options.onMapClick - Map click callback
 * @param {object} options.wire - Livewire component reference
 * @returns {MapboxMapManager|LeafletMapManager|null}
 */
export function createMap(elementId, options = {}) {
    const provider = getCurrentProvider();

    if (provider === MapProvider.MAPBOX) {
        const mapboxUtils = getMapboxUtils();
        if (mapboxUtils.MapboxMapManager) {
            return new mapboxUtils.MapboxMapManager(elementId, options);
        }
    }

    if (provider === MapProvider.LEAFLET) {
        const leafletUtils = getLeafletUtils();
        if (leafletUtils.LeafletMapManager) {
            return new leafletUtils.LeafletMapManager(elementId, options);
        }
    }

    return null;
}

/**
 * Wait for map provider to be ready
 * @param {function} callback - Callback when ready
 * @param {number} timeout - Timeout in ms (default: 10000)
 */
export function waitForMaps(callback, timeout = 10000) {
    const startTime = Date.now();

    const check = () => {
        if (getCurrentProvider()) {
            callback();
            return;
        }

        if (Date.now() - startTime > timeout) {
            console.error('Map provider timeout');
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
        // Provider info
        getCurrentProvider,
        isMapboxAvailable,
        isLeafletAvailable,
        MapProvider,

        // Factory
        createMap,
        waitForMaps,

        // Config helpers
        getConfig,
        createIcon,
        getDefaultCoordinates,
        getZoomLevels,
        getGpsOptions,

        // Direct access to utils (for advanced usage)
        get MapboxConfig() {
            return getMapboxUtils().config;
        },
        get LeafletConfig() {
            return getLeafletUtils().config;
        },
        get MapboxMapManager() {
            return getMapboxUtils().MapboxMapManager;
        },
        get LeafletMapManager() {
            return getLeafletUtils().LeafletMapManager;
        },
    };
}
