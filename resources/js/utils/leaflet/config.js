/**
 * Leaflet Map Configuration
 * Central configuration untuk semua maps di aplikasi
 */

export const LeafletConfig = {
    /**
     * Default coordinates (Kendari, Sulawesi Tenggara)
     */
    defaultCoordinates: {
        latitude: -3.9778,
        longitude: 122.5145,
    },

    /**
     * Default zoom levels
     */
    zoom: {
        default: 15,
        detail: 16,
        city: 13,
    },

    /**
     * Map tile layers configuration
     */
    tileLayers: {
        openStreetMap: {
            name: 'OpenStreetMap',
            url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            options: {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            },
        },
        googleSatellite: {
            name: 'Google Satellite',
            url: 'https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',
            options: {
                maxZoom: 20,
                attribution: '&copy; Google',
            },
        },
        googleHybrid: {
            name: 'Google Hybrid',
            url: 'https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',
            options: {
                maxZoom: 20,
                attribution: '&copy; Google',
            },
        },
        googleTraffic: {
            name: 'Google Traffic',
            url: 'https://mt1.google.com/vt/lyrs=m,traffic&x={x}&y={y}&z={z}',
            options: {
                maxZoom: 20,
                attribution: '&copy; Google',
            },
        },
        googleStreet: {
            name: 'Google Street',
            url: 'https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',
            options: {
                maxZoom: 20,
                attribution: '&copy; Google',
            },
        },
    },

    /**
     * Default tile layer
     */
    defaultTileLayer: 'googleStreet',

    /**
     * Custom marker icon configuration
     */
    markerIcon: {
        iconUrl: '/images/marker.png',
        iconSize: [48, 48],
        iconAnchor: [24, 48],
        popupAnchor: [0, -48],
    },

    /**
     * Home marker icon (for pelanggan)
     */
    homeMarkerIcon: {
        iconUrl: '/images/home-map-pin.png',
        iconSize: [48, 48],
        iconAnchor: [24, 48],
        popupAnchor: [0, -48],
    },

    /**
     * Arrow marker icon (for kurir with direction)
     */
    arrowMarkerIcon: {
        iconUrl: '/images/arrow-up.png',
        iconSize: [48, 48],
        iconAnchor: [24, 24], // Center anchor for rotation
        popupAnchor: [0, -24],
    },

    /**
     * GPS tracking options
     */
    gpsOptions: {
        enableHighAccuracy: true,
        timeout: 30000,
        maximumAge: 5000,
    },

    /**
     * Accuracy circle styling
     */
    accuracyCircle: {
        color: '#3b82f6',
        fillColor: '#3b82f6',
        fillOpacity: 0.15,
        weight: 2,
    },

    /**
     * Accuracy circle color thresholds (in meters)
     */
    accuracyColors: {
        excellent: { threshold: 10, color: '#22c55e' }, // green
        good: { threshold: 50, color: '#eab308' },      // yellow
        poor: { threshold: Infinity, color: '#ef4444' }, // red
    },

    /**
     * Routing configuration
     */
    routing: {
        // OSRM (Primary - Free)
        osrm: {
            enabled: true,
            serviceUrl: 'https://router.project-osrm.org/route/v1',
            profile: 'driving', // car, bike, foot
        },
        // Mapbox Directions API (Fallback - Paid)
        mapbox: {
            enabled: true,
            baseUrl: 'https://api.mapbox.com/directions/v5',
            profile: 'mapbox/driving-traffic', // driving-traffic, driving, walking, cycling
            options: {
                alternatives: false,
                steps: false,
                geometries: 'geojson',
                overview: 'full',
                annotations: ['distance', 'duration', 'speed'],
            },
        },
        // Visual styling
        routeColor: '#3b82f6',
        routeOpacity: 0.8,
        routeWeight: 5,
    },

    /**
     * Map update thresholds
     */
    updateThresholds: {
        panThreshold: 0.0005, // ~55 meters - when to pan map
        centerThreshold: 0.01, // when to recenter map
    },

    /**
     * Animation settings
     */
    animation: {
        duration: 1,
        easeLinearity: 0.5,
    },

    /**
     * Map rotation settings
     */
    rotation: {
        enabled: true,
        smoothTransition: true,
        transitionDuration: 500, // ms
    },

    /**
     * Get accuracy color based on value
     * @param {number} accuracy - Accuracy in meters
     * @returns {string} - Color hex code
     */
    getAccuracyColor(accuracy) {
        if (accuracy <= this.accuracyColors.excellent.threshold) {
            return this.accuracyColors.excellent.color;
        }
        if (accuracy <= this.accuracyColors.good.threshold) {
            return this.accuracyColors.good.color;
        }
        return this.accuracyColors.poor.color;
    },

    /**
     * Create map layers object for Leaflet
     * @returns {object} - Leaflet layers object
     */
    createMapLayers() {
        const layers = {};

        Object.entries(this.tileLayers).forEach(([key, config]) => {
            layers[config.name] = L.tileLayer(config.url, config.options);
        });

        return layers;
    },

    /**
     * Get default tile layer
     * @returns {L.TileLayer} - Leaflet tile layer
     */
    getDefaultTileLayer() {
        const config = this.tileLayers[this.defaultTileLayer];
        return L.tileLayer(config.url, config.options);
    },

    /**
     * Create custom marker icon
     * @returns {L.Icon} - Leaflet icon
     */
    createMarkerIcon() {
        return L.icon(this.markerIcon);
    },

    /**
     * Create home marker icon
     * @returns {L.Icon} - Leaflet icon
     */
    createHomeMarkerIcon() {
        return L.icon(this.homeMarkerIcon);
    },

    /**
     * Create arrow marker icon (for direction)
     * @returns {L.Icon} - Leaflet icon
     */
    createArrowMarkerIcon() {
        return L.icon(this.arrowMarkerIcon);
    },
};

// Make config available globally
if (typeof window !== 'undefined') {
    window.LeafletConfig = LeafletConfig;
}
