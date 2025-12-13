/**
 * Mapbox Configuration
 * Central configuration untuk semua Mapbox maps di aplikasi
 */

export const MapboxConfig = {
    /**
     * Get Mapbox access token from meta tag
     */
    getAccessToken() {
        const metaTag = document.querySelector('meta[name="mapbox-token"]');
        return metaTag ? metaTag.content : null;
    },

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
     * Mapbox style URLs
     */
    styles: {
        standard: 'mapbox://styles/mapbox/standard',
        standardSatellite: 'mapbox://styles/mapbox/standard-satellite',
        streets: 'mapbox://styles/mapbox/streets-v12',
        satellite: 'mapbox://styles/mapbox/satellite-v9',
        satelliteStreets: 'mapbox://styles/mapbox/satellite-streets-v12',
        light: 'mapbox://styles/mapbox/light-v11',
        dark: 'mapbox://styles/mapbox/dark-v11',
        outdoors: 'mapbox://styles/mapbox/outdoors-v12',
        navigationDay: 'mapbox://styles/mapbox/navigation-day-v1',
        navigationNight: 'mapbox://styles/mapbox/navigation-night-v1',
    },

    /**
     * Default map style
     */
    defaultStyle: 'navigationDay',

    /**
     * Custom marker icon configuration
     */
    markerIcon: {
        iconUrl: '/images/marker.png',
        iconSize: [48, 48],
        iconAnchor: [24, 48],
    },

    /**
     * Home marker icon (for pelanggan)
     */
    homeMarkerIcon: {
        iconUrl: '/images/home-map-pin.png',
        iconSize: [48, 48],
        iconAnchor: [24, 48],
    },

    /**
     * Arrow marker icon (for kurir with direction)
     */
    arrowMarkerIcon: {
        iconUrl: '/images/arrow-up.png',
        iconSize: [48, 48],
        iconAnchor: [24, 24], // Center anchor for rotation
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
        fillColor: '#3b82f6',
        fillOpacity: 0.15,
        strokeColor: '#3b82f6',
        strokeWidth: 2,
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
        profile: 'mapbox/driving', // driving, walking, cycling, driving-traffic
        routeColor: '#3b82f6',
        routeOpacity: 0.8,
        routeWidth: 5,
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
        duration: 1000, // ms
        easing: 'easeInOutCubic',
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
     * Get style URL
     * @param {string} styleName - Style name from styles object
     * @returns {string} - Mapbox style URL
     */
    getStyleUrl(styleName = null) {
        const style = styleName || this.defaultStyle;
        return this.styles[style] || this.styles.streets;
    },

    /**
     * Check if Mapbox is available
     * @returns {boolean}
     */
    isAvailable() {
        const token = this.getAccessToken();
        // Check if mapboxgl exists AND token is not null/empty/undefined
        return typeof mapboxgl !== 'undefined' &&
               token !== null &&
               token !== undefined &&
               token !== '' &&
               token.trim().length > 0;
    },
};

// Make config available globally
if (typeof window !== 'undefined') {
    window.MapboxConfig = MapboxConfig;
}
