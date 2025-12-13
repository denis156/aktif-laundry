import { LeafletConfig } from '../config.js';

/**
 * Marker Manager
 * Handles all marker operations (KISS principle)
 */
export class MarkerManager {
    constructor(map, options = {}) {
        this.map = map;
        this.markers = {};
        this.accuracyCircle = null;
        this.defaultDraggable = options.draggable !== undefined ? options.draggable : false;
        this.isUpdatingFromMap = false;
        this.onMarkerDrag = options.onMarkerDrag || null;
    }

    /**
     * Add marker to map
     * @param {string} key - Unique marker key
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {object} options - Marker options
     * @returns {L.Marker} - Leaflet marker
     */
    add(key, lat, lng, options = {}) {
        const markerOptions = {
            draggable: options.draggable !== undefined ? options.draggable : this.defaultDraggable,
            icon: options.icon || LeafletConfig.createMarkerIcon(),
        };

        const marker = L.marker([lat, lng], markerOptions).addTo(this.map);

        // Setup drag handler
        if (markerOptions.draggable) {
            marker.on('dragend', () => {
                this.handleDrag(key, marker);
            });
        }

        // Add popup if provided
        if (options.popup) {
            marker.bindPopup(options.popup);
        }

        this.markers[key] = marker;
        return marker;
    }

    /**
     * Update marker position
     * @param {string} key - Marker key
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     */
    update(key, lat, lng) {
        if (this.markers[key]) {
            this.markers[key].setLatLng([lat, lng]);
        }
    }

    /**
     * Update marker popup
     * @param {string} key - Marker key
     * @param {string} content - Popup HTML content
     */
    updatePopup(key, content) {
        if (this.markers[key]) {
            this.markers[key].bindPopup(content);
        }
    }

    /**
     * Rotate marker to specific angle
     * @param {string} key - Marker key
     * @param {number} angle - Rotation angle in degrees
     */
    rotate(key, angle) {
        if (this.markers[key] && this.markers[key].setRotationAngle) {
            this.markers[key].setRotationAngle(angle);
        }
    }

    /**
     * Remove marker from map
     * @param {string} key - Marker key
     */
    remove(key) {
        if (this.markers[key]) {
            this.map.removeLayer(this.markers[key]);
            delete this.markers[key];
        }
    }

    /**
     * Get marker by key
     * @param {string} key - Marker key
     * @returns {L.Marker|null}
     */
    get(key) {
        return this.markers[key] || null;
    }

    /**
     * Get all markers
     * @returns {object} - All markers
     */
    getAll() {
        return this.markers;
    }

    /**
     * Add or update accuracy circle
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {number} accuracy - Accuracy in meters
     */
    updateAccuracyCircle(lat, lng, accuracy) {
        if (!this.accuracyCircle) {
            this.accuracyCircle = L.circle([lat, lng], {
                radius: accuracy,
                ...LeafletConfig.accuracyCircle,
            }).addTo(this.map);
        } else {
            this.accuracyCircle.setLatLng([lat, lng]);
            this.accuracyCircle.setRadius(accuracy);
        }

        // Update color based on accuracy
        const color = LeafletConfig.getAccuracyColor(accuracy);
        this.accuracyCircle.setStyle({
            color: color,
            fillColor: color,
        });
    }

    /**
     * Remove accuracy circle
     */
    removeAccuracyCircle() {
        if (this.accuracyCircle) {
            this.map.removeLayer(this.accuracyCircle);
            this.accuracyCircle = null;
        }
    }

    /**
     * Handle marker drag
     * @param {string} key - Marker key
     * @param {L.Marker} marker - Leaflet marker
     */
    handleDrag(key, marker) {
        this.isUpdatingFromMap = true;

        const position = marker.getLatLng();

        if (this.onMarkerDrag) {
            this.onMarkerDrag(position.lat, position.lng, key);
        }

        setTimeout(() => {
            this.isUpdatingFromMap = false;
        }, 100);
    }

    /**
     * Clear all markers
     */
    clear() {
        Object.keys(this.markers).forEach(key => {
            this.remove(key);
        });
        this.removeAccuracyCircle();
    }

    /**
     * Destroy manager and cleanup
     */
    destroy() {
        this.clear();
        this.map = null;
        this.onMarkerDrag = null;
    }
}
