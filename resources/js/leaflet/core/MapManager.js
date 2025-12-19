import { LeafletConfig } from '../config.js';

/**
 * Core Map Manager
 * Handles basic map initialization and operations (KISS principle)
 */
export class MapManager {
    constructor(elementId, options = {}) {
        this.elementId = elementId;
        this.map = null;
        this.isInitialized = false;
        this.isUpdatingFromMap = false;

        // Merge options with defaults
        this.options = {
            latitude: options.latitude || LeafletConfig.defaultCoordinates.latitude,
            longitude: options.longitude || LeafletConfig.defaultCoordinates.longitude,
            zoom: options.zoom || LeafletConfig.zoom.default,
            showLayerControl: options.showLayerControl !== undefined ? options.showLayerControl : true,
            showZoomControl: options.showZoomControl !== undefined ? options.showZoomControl : false,
            defaultTileLayer: options.defaultTileLayer || LeafletConfig.defaultTileLayer,
            rotate: options.rotate !== undefined ? options.rotate : false,
            onMapClick: options.onMapClick || null,
            onReady: options.onReady || null,
        };
    }

    /**
     * Initialize map
     * @returns {L.Map} - Leaflet map instance
     */
    init() {
        const element = document.getElementById(this.elementId);
        if (!element || this.isInitialized) {
            return this.map;
        }

        // Create map with rotation support
        this.map = L.map(this.elementId, {
            zoomControl: this.options.showZoomControl,
            attributionControl: false,
            dragging: true,
            scrollWheelZoom: true,
            rotate: this.options.rotate,
            bearing: 0,
            touchRotate: this.options.rotate,
            rotateControl: false,
        }).setView([this.options.latitude, this.options.longitude], this.options.zoom);

        // Add tile layers
        const mapLayers = LeafletConfig.createMapLayers();

        // Get default layer key
        const defaultLayerKey = this.options.defaultTileLayer;

        // Add default layer to map
        let defaultLayerAdded = false;
        Object.entries(LeafletConfig.tileLayers).forEach(([key, config]) => {
            if (key === defaultLayerKey) {
                mapLayers[config.name].addTo(this.map);
                defaultLayerAdded = true;
            }
        });

        // Fallback if default layer not found
        if (!defaultLayerAdded) {
            const defaultConfig = LeafletConfig.tileLayers[LeafletConfig.defaultTileLayer];
            if (defaultConfig && mapLayers[defaultConfig.name]) {
                mapLayers[defaultConfig.name].addTo(this.map);
            }
        }

        // Add layer control
        if (this.options.showLayerControl) {
            L.control.layers(mapLayers, {}, {
                position: 'topright',
                collapsed: true,
            }).addTo(this.map);
        }

        // Hide attribution
        this.hideAttribution();

        // Setup map click handler if provided
        if (this.options.onMapClick) {
            this.map.on('click', (e) => {
                this.handleMapClick(e);
            });
        }

        this.isInitialized = true;

        // Trigger ready callback if provided
        if (this.options.onReady) {
            setTimeout(() => {
                this.options.onReady(this.map);
            }, 100);
        }

        return this.map;
    }

    /**
     * Hide Leaflet attribution
     */
    hideAttribution() {
        setTimeout(() => {
            const attributionElements = document.getElementsByClassName('leaflet-control-attribution');
            for (let i = 0; i < attributionElements.length; i++) {
                attributionElements[i].style.display = 'none';
            }
        }, 100);
    }

    /**
     * Pan map to coordinates with threshold check
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {boolean} force - Force pan without threshold check
     */
    panTo(lat, lng, force = false) {
        if (!this.map) return;

        const currentCenter = this.map.getCenter();
        const distance = Math.sqrt(
            Math.pow(currentCenter.lat - lat, 2) +
            Math.pow(currentCenter.lng - lng, 2)
        );

        if (force || distance > LeafletConfig.updateThresholds.panThreshold) {
            this.map.panTo([lat, lng], {
                animate: true,
                duration: LeafletConfig.animation.duration,
                easeLinearity: LeafletConfig.animation.easeLinearity,
            });
        }
    }

    /**
     * Set map view (center + zoom)
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {number} zoom - Zoom level (optional)
     */
    setView(lat, lng, zoom = null) {
        if (!this.map) return;

        const zoomLevel = zoom || this.options.zoom;
        this.map.setView([lat, lng], zoomLevel);
    }

    /**
     * Fit bounds to show markers
     * @param {Array} markers - Array of L.Marker objects
     */
    fitBounds(markers) {
        if (!this.map || !markers || markers.length === 0) return;

        const group = L.featureGroup(markers);
        this.map.fitBounds(group.getBounds().pad(0.1));
    }

    /**
     * Handle map click
     * @param {object} e - Leaflet click event
     */
    handleMapClick(e) {
        this.isUpdatingFromMap = true;

        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        if (this.options.onMapClick) {
            this.options.onMapClick(lat, lng);
        }

        setTimeout(() => {
            this.isUpdatingFromMap = false;
        }, 100);
    }

    /**
     * Get map instance
     * @returns {L.Map|null}
     */
    getMap() {
        return this.map;
    }

    /**
     * Check if map is initialized
     * @returns {boolean}
     */
    isReady() {
        return this.isInitialized;
    }

    /**
     * Destroy map and cleanup
     */
    destroy() {
        if (this.map) {
            this.map.remove();
            this.map = null;
        }

        this.isInitialized = false;
    }
}
