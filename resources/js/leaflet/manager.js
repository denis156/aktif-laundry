import { MapManager } from './core/MapManager.js';
import { MarkerManager } from './core/MarkerManager.js';
import { RoutingService } from './services/RoutingService.js';
import { GPSTracker } from './services/GPSTracker.js';
import { CompassTracker } from './services/compass/index.js';
import { GeocodingService } from './services/GeocodingService.js';

/**
 * Leaflet Map Manager (Facade)
 * Orchestrates all map services using composition (OOP + KISS)
 */
export class LeafletMapManager {
    constructor(elementId, options = {}) {
        this.elementId = elementId;
        this.options = options;

        // Core managers
        this.mapManager = null;
        this.markerManager = null;

        // Services
        this.routingService = null;
        this.gpsTracker = null;
        this.compassTracker = null;
        this.geocodingService = null;

        // Legacy support
        this.lastPosition = null;
    }

    /**
     * Initialize map and all services
     * @returns {LeafletMapManager}
     */
    init() {
        // Initialize core map
        this.mapManager = new MapManager(this.elementId, {
            latitude: this.options.latitude,
            longitude: this.options.longitude,
            zoom: this.options.zoom,
            showLayerControl: this.options.showLayerControl,
            defaultTileLayer: this.options.defaultTileLayer,
            rotate: this.options.rotate,
            onMapClick: this.options.onMapClick,
            onReady: this.options.onReady,
        });

        const map = this.mapManager.init();

        if (!map) {
            return this;
        }

        // Initialize marker manager
        this.markerManager = new MarkerManager(map, {
            draggable: this.options.draggable,
            onMarkerDrag: this.options.onLocationUpdate,
        });

        // Initialize routing service
        this.routingService = new RoutingService(map);

        // Initialize GPS tracker
        this.gpsTracker = new GPSTracker();

        // Initialize compass tracker (if enabled)
        if (this.options.enableCompass) {
            this.compassTracker = new CompassTracker(map, {
                smoothRotation: this.options.smoothCompass,
                onRotationUpdate: (bearing) => {
                    // Update marker rotation if exists
                    if (this.markerManager) {
                        this.markerManager.rotate('kurir', bearing * 2);
                    }
                },
            });
        }

        // Initialize geocoding service
        this.geocodingService = new GeocodingService();

        return this;
    }

    // ==========================================
    // Map Operations (delegate to MapManager)
    // ==========================================

    panTo(lat, lng, force = false) {
        this.mapManager?.panTo(lat, lng, force);
    }

    setView(lat, lng, zoom = null) {
        this.mapManager?.setView(lat, lng, zoom);
    }

    fitBounds() {
        if (this.markerManager) {
            const markers = Object.values(this.markerManager.getAll());
            this.mapManager?.fitBounds(markers);
        }
    }

    // ==========================================
    // Marker Operations (delegate to MarkerManager)
    // ==========================================

    addMarker(key, lat, lng, options = {}) {
        return this.markerManager?.add(key, lat, lng, options);
    }

    updateMarker(key, lat, lng) {
        this.markerManager?.update(key, lat, lng);
    }

    removeMarker(key) {
        this.markerManager?.remove(key);
    }

    updateMarkerPopup(key, content) {
        this.markerManager?.updatePopup(key, content);
    }

    rotateMarker(key, angle) {
        this.markerManager?.rotate(key, angle);
    }

    updateAccuracyCircle(lat, lng, accuracy) {
        this.markerManager?.updateAccuracyCircle(lat, lng, accuracy);
    }

    // ==========================================
    // Routing Operations (delegate to RoutingService)
    // ==========================================

    initRouting(startLat, startLng, endLat, endLng) {
        return this.routingService?.initRouting(startLat, startLng, endLat, endLng);
    }

    updateRouting(startLat, startLng, endLat, endLng) {
        return this.routingService?.updateRouting(startLat, startLng, endLat, endLng);
    }

    // ==========================================
    // GPS Operations (delegate to GPSTracker)
    // ==========================================

    startGPSTracking(onSuccess, onError) {
        this.gpsTracker?.start(onSuccess, onError);
    }

    stopGPSTracking() {
        this.gpsTracker?.stop();
    }

    // ==========================================
    // Compass Operations (delegate to CompassTracker)
    // ==========================================

    startCompassTracking() {
        this.compassTracker?.start();
    }

    stopCompassTracking() {
        this.compassTracker?.stop();
    }

    rotateTo(bearing) {
        this.compassTracker?.rotateTo(bearing);
    }

    smoothRotateTo(bearing) {
        this.compassTracker?.smoothRotateTo(bearing);
    }

    resetRotation() {
        this.compassTracker?.reset();
    }

    getBearing() {
        return this.compassTracker?.getCurrentBearing() || 0;
    }

    calculateBearing(lat1, lng1, lat2, lng2) {
        return this.compassTracker?.calculateBearing(lat1, lng1, lat2, lng2) || 0;
    }

    // ==========================================
    // Geocoding Operations (delegate to GeocodingService)
    // ==========================================

    /**
     * Search with autocomplete (returns suggestions without coordinates)
     * @param {string} query - Search query
     * @param {object} options - Search options
     * @returns {Promise<Array>} - Array of suggestions
     */
    searchSuggestions(query, options = {}) {
        return this.geocodingService?.suggest(query, options) || Promise.resolve([]);
    }

    /**
     * Get details with coordinates for a suggestion
     * @param {string} mapboxId - Mapbox ID from suggestion
     * @returns {Promise<object|null>} - Feature with coordinates
     */
    retrievePlace(mapboxId) {
        return this.geocodingService?.retrieve(mapboxId) || Promise.resolve(null);
    }

    /**
     * Forward geocoding - text to coordinates
     * @param {string} query - Search query
     * @param {object} options - Search options
     * @returns {Promise<Array>} - Array of features with coordinates
     */
    geocodeForward(query, options = {}) {
        return this.geocodingService?.forward(query, options) || Promise.resolve([]);
    }

    /**
     * Reverse geocoding - coordinates to text
     * @param {number} lng - Longitude
     * @param {number} lat - Latitude
     * @param {object} options - Search options
     * @returns {Promise<Array>} - Array of features
     */
    geocodeReverse(lng, lat, options = {}) {
        return this.geocodingService?.reverse(lng, lat, options) || Promise.resolve([]);
    }

    // ==========================================
    // Legacy Support Properties
    // ==========================================

    get map() {
        return this.mapManager?.getMap();
    }

    get markers() {
        return this.markerManager?.getAll() || {};
    }

    get currentBearing() {
        return this.compassTracker?.getCurrentBearing() || 0;
    }

    get compassHeading() {
        return this.compassTracker?.getCompassHeading() || null;
    }

    get isInitialized() {
        return this.mapManager?.isReady() || false;
    }

    // ==========================================
    // Cleanup
    // ==========================================

    destroy() {
        // Stop all services
        this.stopGPSTracking();
        this.stopCompassTracking();

        // Destroy all managers and services
        this.routingService?.destroy();
        this.geocodingService?.destroy();
        this.markerManager?.destroy();
        this.mapManager?.destroy();

        // Cleanup
        this.routingService = null;
        this.geocodingService = null;
        this.gpsTracker = null;
        this.compassTracker = null;
        this.markerManager = null;
        this.mapManager = null;
        this.lastPosition = null;
    }
}

// Make class available globally
if (typeof window !== 'undefined') {
    window.LeafletMapManager = LeafletMapManager;
}
