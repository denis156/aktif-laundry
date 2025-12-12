import { LeafletConfig } from './config.js';

/**
 * Leaflet Map Manager (OOP)
 * Class untuk manage semua operasi map dengan Leaflet
 */
export class LeafletMapManager {
    constructor(elementId, options = {}) {
        this.elementId = elementId;
        this.map = null;
        this.markers = {};
        this.accuracyCircle = null;
        this.routingControl = null;
        this.watchId = null;
        this.isUpdatingFromMap = false;
        this.isInitialized = false;
        this.currentBearing = 0; // Store current bearing for rotation

        // Merge options dengan default config
        this.options = {
            latitude: options.latitude || LeafletConfig.defaultCoordinates.latitude,
            longitude: options.longitude || LeafletConfig.defaultCoordinates.longitude,
            zoom: options.zoom || LeafletConfig.zoom.default,
            draggable: options.draggable !== undefined ? options.draggable : false,
            showLayerControl: options.showLayerControl !== undefined ? options.showLayerControl : true,
            rotate: options.rotate !== undefined ? options.rotate : false, // Enable map rotation
            onLocationUpdate: options.onLocationUpdate || null,
            onMapClick: options.onMapClick || null,
            wire: options.wire || null,
        };
    }

    /**
     * Initialize map
     */
    init() {
        const element = document.getElementById(this.elementId);
        if (!element || this.isInitialized) {
            return this;
        }

        // Create map with rotation support
        this.map = L.map(this.elementId, {
            zoomControl: true,
            attributionControl: false,
            dragging: true,
            scrollWheelZoom: true,
            rotate: this.options.rotate, // Enable rotation if specified
            bearing: 0, // Initial bearing
            touchRotate: this.options.rotate, // Enable touch rotation
            rotateControl: false, // Hide default rotate control
        }).setView([this.options.latitude, this.options.longitude], this.options.zoom);

        // Add tile layers
        const mapLayers = LeafletConfig.createMapLayers();
        LeafletConfig.getDefaultTileLayer().addTo(this.map);

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
        return this;
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
     * Add marker to map
     * @param {string} key - Unique marker key
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {object} options - Marker options
     * @returns {L.Marker} - Leaflet marker
     */
    addMarker(key, lat, lng, options = {}) {
        const markerOptions = {
            draggable: options.draggable || this.options.draggable,
            icon: options.icon || LeafletConfig.createMarkerIcon(),
        };

        const marker = L.marker([lat, lng], markerOptions).addTo(this.map);

        // Setup drag handler
        if (markerOptions.draggable) {
            marker.on('dragend', () => {
                this.handleMarkerDrag(key, marker);
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
    updateMarker(key, lat, lng) {
        if (this.markers[key]) {
            this.markers[key].setLatLng([lat, lng]);
        }
    }

    /**
     * Remove marker from map
     * @param {string} key - Marker key
     */
    removeMarker(key) {
        if (this.markers[key]) {
            this.map.removeLayer(this.markers[key]);
            delete this.markers[key];
        }
    }

    /**
     * Update marker popup
     * @param {string} key - Marker key
     * @param {string} content - Popup HTML content
     */
    updateMarkerPopup(key, content) {
        if (this.markers[key]) {
            this.markers[key].bindPopup(content);
        }
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
     * Fit bounds to show all markers
     */
    fitBounds() {
        if (!this.map || Object.keys(this.markers).length === 0) return;

        const group = L.featureGroup(Object.values(this.markers));
        this.map.fitBounds(group.getBounds().pad(0.1));
    }

    /**
     * Initialize routing between two points
     * @param {number} startLat - Start latitude
     * @param {number} startLng - Start longitude
     * @param {number} endLat - End latitude
     * @param {number} endLng - End longitude
     */
    initRouting(startLat, startLng, endLat, endLng) {
        // Remove existing routing
        if (this.routingControl) {
            this.map.removeControl(this.routingControl);
        }

        this.routingControl = L.Routing.control({
            waypoints: [
                L.latLng(startLat, startLng),
                L.latLng(endLat, endLng),
            ],
            routeWhileDragging: false,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            showAlternatives: false,
            lineOptions: {
                styles: [{
                    color: LeafletConfig.routing.routeColor,
                    opacity: LeafletConfig.routing.routeOpacity,
                    weight: LeafletConfig.routing.routeWeight,
                }],
                extendToWaypoints: true,
                missingRouteTolerance: 0,
            },
            createMarker: () => null, // Don't create default markers
            router: L.Routing.osrmv1({
                serviceUrl: LeafletConfig.routing.serviceUrl,
            }),
        }).addTo(this.map);

        // Hide routing instructions panel
        this.hideRoutingInstructions();

        return this.routingControl;
    }

    /**
     * Update routing waypoints
     * @param {number} startLat - Start latitude
     * @param {number} startLng - Start longitude
     * @param {number} endLat - End latitude
     * @param {number} endLng - End longitude
     */
    updateRouting(startLat, startLng, endLat, endLng) {
        if (this.routingControl) {
            this.routingControl.setWaypoints([
                L.latLng(startLat, startLng),
                L.latLng(endLat, endLng),
            ]);
        } else {
            this.initRouting(startLat, startLng, endLat, endLng);
        }
    }

    /**
     * Hide routing instructions panel
     */
    hideRoutingInstructions() {
        setTimeout(() => {
            const routingContainer = document.querySelector('.leaflet-routing-container');
            if (routingContainer) {
                routingContainer.style.display = 'none';
            }
        }, 100);
    }

    /**
     * Start GPS tracking
     * @param {function} onSuccess - Callback on position update
     * @param {function} onError - Callback on error
     */
    startGPSTracking(onSuccess, onError) {
        if (!navigator.geolocation) {
            console.error('Geolocation not supported');
            if (onError) onError(new Error('Geolocation not supported'));
            return;
        }

        this.watchId = navigator.geolocation.watchPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                if (onSuccess) {
                    onSuccess(lat, lng, accuracy);
                }
            },
            (error) => {
                if (error.code !== error.TIMEOUT && onError) {
                    onError(error);
                }
            },
            LeafletConfig.gpsOptions
        );
    }

    /**
     * Stop GPS tracking
     */
    stopGPSTracking() {
        if (this.watchId !== null) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
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
     * Handle marker drag
     * @param {string} key - Marker key
     * @param {L.Marker} marker - Leaflet marker
     */
    handleMarkerDrag(key, marker) {
        this.isUpdatingFromMap = true;

        const position = marker.getLatLng();

        if (this.options.onLocationUpdate) {
            this.options.onLocationUpdate(position.lat, position.lng, key);
        }

        setTimeout(() => {
            this.isUpdatingFromMap = false;
        }, 100);
    }

    /**
     * Rotate map to bearing (in degrees)
     * @param {number} bearing - Bearing in degrees (0-360)
     */
    rotateTo(bearing) {
        if (!this.map || !this.options.rotate) return;

        this.currentBearing = bearing;
        this.map.setBearing(bearing);
    }

    /**
     * Rotate map smoothly to bearing
     * @param {number} bearing - Target bearing in degrees
     */
    smoothRotateTo(bearing) {
        if (!this.map || !this.options.rotate) return;

        const duration = LeafletConfig.rotation.transitionDuration;
        this.currentBearing = bearing;

        // Animate bearing change
        this.map.flyToBearing(bearing, {
            duration: duration / 1000,
        });
    }

    /**
     * Reset map rotation to north (0 degrees)
     */
    resetRotation() {
        this.rotateTo(0);
    }

    /**
     * Get current map bearing
     * @returns {number} - Current bearing in degrees
     */
    getBearing() {
        return this.currentBearing;
    }

    /**
     * Destroy map and cleanup
     */
    destroy() {
        this.stopGPSTracking();

        if (this.routingControl) {
            this.map.removeControl(this.routingControl);
            this.routingControl = null;
        }

        if (this.map) {
            this.map.remove();
            this.map = null;
        }

        this.markers = {};
        this.accuracyCircle = null;
        this.isInitialized = false;
        this.currentBearing = 0;
    }
}

// Make class available globally
if (typeof window !== 'undefined') {
    window.LeafletMapManager = LeafletMapManager;
}
