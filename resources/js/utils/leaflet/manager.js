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
        this.routeLayer = null; // Polyline layer for route (Mapbox/OSRM)
        this.watchId = null;
        this.isUpdatingFromMap = false;
        this.isInitialized = false;
        this.currentBearing = 0; // Store current bearing for rotation
        this.compassHeading = null; // Store compass heading from device orientation
        this.lastPosition = null; // Store last position for bearing calculation
        this.compassEnabled = false; // Flag to track compass state
        this.lastCompassUpdate = 0; // Timestamp for throttling
        this.compassThrottle = 100; // Throttle compass updates (ms)
        this.targetBearing = 0; // Target bearing for smooth rotation
        this.rotationAnimationFrame = null; // Animation frame ID

        // Merge options dengan default config
        this.options = {
            latitude: options.latitude || LeafletConfig.defaultCoordinates.latitude,
            longitude: options.longitude || LeafletConfig.defaultCoordinates.longitude,
            zoom: options.zoom || LeafletConfig.zoom.default,
            draggable: options.draggable !== undefined ? options.draggable : false,
            showLayerControl: options.showLayerControl !== undefined ? options.showLayerControl : true,
            defaultTileLayer: options.defaultTileLayer || LeafletConfig.defaultTileLayer, // Allow custom default tile layer
            rotate: options.rotate !== undefined ? options.rotate : false, // Enable map rotation
            enableCompass: options.enableCompass !== undefined ? options.enableCompass : false, // Enable compass tracking
            smoothCompass: options.smoothCompass !== undefined ? options.smoothCompass : true, // Smooth compass rotation
            onLocationUpdate: options.onLocationUpdate || null,
            onMapClick: options.onMapClick || null,
            onReady: options.onReady || null,
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

        // Get default layer key
        const defaultLayerKey = this.options.defaultTileLayer;

        // Find the default layer from mapLayers and add it to the map
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
            // Use setTimeout to ensure map is fully initialized
            setTimeout(() => {
                this.options.onReady(this);
            }, 100);
        }

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
     * Get Mapbox access token from meta tag
     * @returns {string|null}
     */
    getMapboxToken() {
        const metaTag = document.querySelector('meta[name="mapbox-token"]');
        return metaTag ? metaTag.content : null;
    }

    /**
     * Fetch route from Mapbox Directions API
     * @param {number} startLat - Start latitude
     * @param {number} startLng - Start longitude
     * @param {number} endLat - End latitude
     * @param {number} endLng - End longitude
     * @returns {Promise<object>} - Route data
     */
    async fetchMapboxRoute(startLat, startLng, endLat, endLng) {
        const token = this.getMapboxToken();
        if (!token || !LeafletConfig.routing.mapbox.enabled) {
            throw new Error('Mapbox token not available or Mapbox routing disabled');
        }

        const { baseUrl, profile, options } = LeafletConfig.routing.mapbox;
        const coordinates = `${startLng},${startLat};${endLng},${endLat}`;

        // Build query params
        const params = new URLSearchParams({
            access_token: token,
            geometries: options.geometries,
            overview: options.overview,
            alternatives: options.alternatives,
            steps: options.steps,
        });

        if (options.annotations && options.annotations.length > 0) {
            params.append('annotations', options.annotations.join(','));
        }

        const url = `${baseUrl}/${profile}/${coordinates}?${params.toString()}`;

        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`Mapbox API error: ${response.status}`);
        }

        const data = await response.json();
        if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
            throw new Error('No route found from Mapbox');
        }

        return data.routes[0];
    }

    /**
     * Fetch route from OSRM with timeout
     * @param {number} startLat - Start latitude
     * @param {number} startLng - Start longitude
     * @param {number} endLat - End latitude
     * @param {number} endLng - End longitude
     * @returns {Promise<object>} - Route data
     */
    async fetchOSRMRoute(startLat, startLng, endLat, endLng) {
        if (!LeafletConfig.routing.osrm.enabled) {
            throw new Error('OSRM routing disabled');
        }

        const { serviceUrl, profile } = LeafletConfig.routing.osrm;
        const coordinates = `${startLng},${startLat};${endLng},${endLat}`;
        const url = `${serviceUrl}/${profile}/${coordinates}?overview=full&geometries=geojson`;

        // Add timeout 10 seconds
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000);

        try {
            const response = await fetch(url, { signal: controller.signal });
            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`OSRM API error: ${response.status}`);
            }

            const data = await response.json();
            if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                throw new Error('No route found from OSRM');
            }

            return data.routes[0];
        } catch (error) {
            clearTimeout(timeoutId);
            if (error.name === 'AbortError') {
                throw new Error('OSRM request timeout (10s)');
            }
            throw error;
        }
    }

    /**
     * Initialize routing between two points (OSRM primary, Mapbox fallback)
     * @param {number} startLat - Start latitude
     * @param {number} startLng - Start longitude
     * @param {number} endLat - End latitude
     * @param {number} endLng - End longitude
     */
    async initRouting(startLat, startLng, endLat, endLng) {
        // Remove existing routing
        if (this.routingControl) {
            this.map.removeControl(this.routingControl);
            this.routingControl = null;
        }

        try {
            // Try OSRM first (free)
            const route = await this.fetchOSRMRoute(startLat, startLng, endLat, endLng);
            this.drawRoute(route.geometry);
        } catch (osrmError) {
            try {
                // Fallback to Mapbox (paid)
                const route = await this.fetchMapboxRoute(startLat, startLng, endLat, endLng);
                this.drawRoute(route.geometry);
            } catch (mapboxError) {
                // Both failed - show error
                console.error('[Error] All routing services failed:', mapboxError.message);
            }
        }
    }

    /**
     * Draw route on map from GeoJSON geometry
     * @param {object} geometry - GeoJSON LineString geometry
     */
    drawRoute(geometry) {
        // Remove existing route layer
        if (this.routeLayer) {
            this.map.removeLayer(this.routeLayer);
        }

        // Create polyline from GeoJSON coordinates
        const latLngs = geometry.coordinates.map(coord => [coord[1], coord[0]]);

        this.routeLayer = L.polyline(latLngs, {
            color: LeafletConfig.routing.routeColor,
            opacity: LeafletConfig.routing.routeOpacity,
            weight: LeafletConfig.routing.routeWeight,
        }).addTo(this.map);

        // Fit bounds to show full route
        this.map.fitBounds(this.routeLayer.getBounds(), {
            padding: [50, 50],
        });
    }

    /**
     * Update routing waypoints
     * @param {number} startLat - Start latitude
     * @param {number} startLng - Start longitude
     * @param {number} endLat - End latitude
     * @param {number} endLng - End longitude
     */
    updateRouting(startLat, startLng, endLat, endLng) {
        return this.initRouting(startLat, startLng, endLat, endLng);
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
     * Calculate bearing between two coordinates
     * @param {number} lat1 - Start latitude
     * @param {number} lng1 - Start longitude
     * @param {number} lat2 - End latitude
     * @param {number} lng2 - End longitude
     * @returns {number} - Bearing in degrees (0-360)
     */
    calculateBearing(lat1, lng1, lat2, lng2) {
        const toRad = (deg) => deg * Math.PI / 180;
        const toDeg = (rad) => rad * 180 / Math.PI;

        const dLng = toRad(lng2 - lng1);
        const y = Math.sin(dLng) * Math.cos(toRad(lat2));
        const x = Math.cos(toRad(lat1)) * Math.sin(toRad(lat2)) -
                  Math.sin(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.cos(dLng);

        let bearing = toDeg(Math.atan2(y, x));
        bearing = (bearing + 360) % 360; // Normalize to 0-360

        return bearing;
    }

    /**
     * Start compass tracking using Device Orientation API
     */
    startCompassTracking() {
        if (!this.options.enableCompass) {
            return;
        }

        // Check if device orientation is supported
        if (!window.DeviceOrientationEvent) {
            return;
        }

        // Request permission for iOS 13+
        if (typeof DeviceOrientationEvent.requestPermission === 'function') {
            DeviceOrientationEvent.requestPermission()
                .then(permissionState => {
                    if (permissionState === 'granted') {
                        this.attachOrientationListener();
                    }
                })
                .catch(console.error);
        } else {
            // Non-iOS or older iOS - no permission needed
            this.attachOrientationListener();
        }
    }

    /**
     * Attach device orientation listener
     */
    attachOrientationListener() {
        this.handleOrientation = this.handleOrientation.bind(this);
        window.addEventListener('deviceorientationabsolute', this.handleOrientation, true);
        window.addEventListener('deviceorientation', this.handleOrientation, true);
        this.compassEnabled = true;
    }

    /**
     * Handle device orientation event
     * @param {DeviceOrientationEvent} event - Device orientation event
     */
    handleOrientation(event) {
        // Throttle updates untuk smooth rotation
        const now = Date.now();
        if (now - this.lastCompassUpdate < this.compassThrottle) {
            return;
        }
        this.lastCompassUpdate = now;

        let heading = null;

        // Use absolute orientation if available (more accurate)
        if (event.absolute && event.alpha !== null) {
            heading = event.alpha;
        } else if (event.webkitCompassHeading !== undefined) {
            // iOS Safari uses webkitCompassHeading
            heading = event.webkitCompassHeading;
        } else if (event.alpha !== null) {
            // Fallback to alpha (0-360, where 0 is north)
            heading = 360 - event.alpha;
        }

        if (heading !== null) {
            this.compassHeading = heading;

            // Apply smooth rotation if enabled
            if (this.options.smoothCompass) {
                this.smoothRotateToCompass(heading);
            }
        }
    }

    /**
     * Calculate shortest angle difference between two angles
     * @param {number} from - Current angle (0-360)
     * @param {number} to - Target angle (0-360)
     * @returns {number} - Shortest difference (-180 to 180)
     */
    getShortestAngleDifference(from, to) {
        let diff = to - from;

        // Normalize to -180 to 180
        while (diff > 180) diff -= 360;
        while (diff < -180) diff += 360;

        return diff;
    }

    /**
     * Smooth rotate to compass heading
     * @param {number} targetHeading - Target compass heading
     */
    smoothRotateToCompass(targetHeading) {
        if (!this.map || !this.options.rotate) return;

        this.targetBearing = targetHeading;

        // Cancel existing animation
        if (this.rotationAnimationFrame) {
            cancelAnimationFrame(this.rotationAnimationFrame);
        }

        // Animate rotation
        const animate = () => {
            // Calculate shortest angle difference
            const diff = this.getShortestAngleDifference(this.currentBearing, this.targetBearing);

            // Smooth interpolation (ease out)
            const step = diff * 0.20; // 20% of remaining distance

            if (Math.abs(step) > 0.1) {
                // Update bearing
                this.currentBearing += step;

                // Normalize to 0-360
                if (this.currentBearing < 0) this.currentBearing += 360;
                if (this.currentBearing >= 360) this.currentBearing -= 360;

                // Update map bearing
                if (this.map.setBearing) {
                    this.map.setBearing(-this.currentBearing);
                }

                // Update marker rotation (if exists)
                if (this.markers['kurir'] && this.markers['kurir'].setRotationAngle) {
                    this.markers['kurir'].setRotationAngle(this.currentBearing * 2);
                }

                this.rotationAnimationFrame = requestAnimationFrame(animate);
            } else {
                // Snap to final position
                this.currentBearing = this.targetBearing;
                if (this.map.setBearing) {
                    this.map.setBearing(-this.currentBearing);
                }
                if (this.markers['kurir'] && this.markers['kurir'].setRotationAngle) {
                    this.markers['kurir'].setRotationAngle(this.currentBearing * 2);
                }
            }
        };

        animate();
    }

    /**
     * Stop compass tracking
     */
    stopCompassTracking() {
        if (this.compassEnabled && this.handleOrientation) {
            window.removeEventListener('deviceorientationabsolute', this.handleOrientation, true);
            window.removeEventListener('deviceorientation', this.handleOrientation, true);
            this.compassEnabled = false;
            this.compassHeading = null;
        }
    }

    /**
     * Rotate marker to specific angle
     * @param {string} key - Marker key
     * @param {number} angle - Rotation angle in degrees
     */
    rotateMarker(key, angle) {
        if (this.markers[key] && this.markers[key].setRotationAngle) {
            this.markers[key].setRotationAngle(angle);
        }
    }

    /**
     * Destroy map and cleanup
     */
    destroy() {
        this.stopGPSTracking();
        this.stopCompassTracking();

        // Cancel any pending rotation animation
        if (this.rotationAnimationFrame) {
            cancelAnimationFrame(this.rotationAnimationFrame);
            this.rotationAnimationFrame = null;
        }

        if (this.routingControl) {
            this.map.removeControl(this.routingControl);
            this.routingControl = null;
        }

        if (this.routeLayer) {
            this.map.removeLayer(this.routeLayer);
            this.routeLayer = null;
        }

        if (this.map) {
            this.map.remove();
            this.map = null;
        }

        this.markers = {};
        this.accuracyCircle = null;
        this.isInitialized = false;
        this.currentBearing = 0;
        this.compassHeading = null;
        this.lastPosition = null;
        this.targetBearing = 0;
    }
}

// Make class available globally
if (typeof window !== 'undefined') {
    window.LeafletMapManager = LeafletMapManager;
}
