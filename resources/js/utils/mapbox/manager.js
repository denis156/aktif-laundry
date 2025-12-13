import { MapboxConfig } from './config.js';

/**
 * Mapbox Map Manager (OOP)
 * Class untuk manage semua operasi map dengan Mapbox GL JS
 */
export class MapboxMapManager {
    constructor(elementId, options = {}) {
        this.elementId = elementId;
        this.map = null;
        this.markers = {};
        this.accuracyCircle = null;
        this.routeSourceId = 'route-source';
        this.routeLayerId = 'route-layer';
        this.watchId = null;
        this.isUpdatingFromMap = false;
        this.isInitialized = false;
        this.currentBearing = 0;
        this.compassHeading = null;
        this.lastPosition = null;
        this.compassEnabled = false;
        this.lastCompassUpdate = 0;
        this.compassThrottle = 100;
        this.targetBearing = 0;
        this.rotationAnimationFrame = null;

        // Merge options dengan default config
        this.options = {
            latitude: options.latitude || MapboxConfig.defaultCoordinates.latitude,
            longitude: options.longitude || MapboxConfig.defaultCoordinates.longitude,
            zoom: options.zoom || MapboxConfig.zoom.default,
            style: options.style || MapboxConfig.defaultStyle,
            draggable: options.draggable !== undefined ? options.draggable : false,
            rotate: options.rotate !== undefined ? options.rotate : false,
            enableCompass: options.enableCompass !== undefined ? options.enableCompass : false,
            smoothCompass: options.smoothCompass !== undefined ? options.smoothCompass : true,
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

        // Check if Mapbox is available
        if (!MapboxConfig.isAvailable()) {
            console.warn('⚠️ Mapbox GL JS not available or token missing/invalid - fallback to Leaflet should be used');
            return this;
        }

        // Set access token
        const token = MapboxConfig.getAccessToken();
        if (!token || token.trim().length === 0) {
            console.warn('⚠️ Mapbox token is empty - fallback to Leaflet should be used');
            return this;
        }

        mapboxgl.accessToken = token;

        try {
            // Create map
            this.map = new mapboxgl.Map({
                container: this.elementId,
                style: MapboxConfig.getStyleUrl(this.options.style),
                center: [this.options.longitude, this.options.latitude],
                zoom: this.options.zoom,
                pitch: 0,
                bearing: 0,
                attributionControl: false,
                dragRotate: this.options.rotate,
                touchPitch: false,
            });
        } catch (error) {
            console.error('❌ Failed to create Mapbox map:', error);
            console.warn('⚠️ Fallback to Leaflet should be used');
            return this;
        }

        // Add navigation controls (zoom & compass) - LEFT SIDE
        this.map.addControl(new mapboxgl.NavigationControl({
            showCompass: this.options.rotate,
            showZoom: true,
            visualizePitch: false,
        }), 'top-left');

        // Setup map click handler
        if (this.options.onMapClick) {
            this.map.on('click', (e) => {
                this.handleMapClick(e);
            });
        }

        // Handle map errors (e.g., invalid token, API errors)
        this.map.on('error', (e) => {
            console.error('❌ Mapbox map error:', e.error);
            console.warn('⚠️ Token might be invalid or expired - consider using Leaflet fallback');

            // Mark as not initialized so fallback can be attempted
            this.isInitialized = false;
        });

        // Wait for map to load - CRITICAL for Mapbox!
        this.map.on('load', () => {
            this.isInitialized = true;
            console.log('✅ Mapbox map loaded successfully');

            // Add style/layer switcher control if enabled - AFTER map loaded
            if (this.options.showLayerControl === true) {
                console.log('🎨 Adding Mapbox style switcher control...');
                this.addStyleSwitcher();
            }

            // Trigger ready callback if provided
            if (this.options.onReady) {
                this.options.onReady(this);
            }
        });

        return this;
    }

    /**
     * Add marker to map
     * @param {string} key - Unique marker key
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {object} options - Marker options
     * @returns {mapboxgl.Marker}
     */
    addMarker(key, lat, lng, options = {}) {
        // Create custom marker element
        const el = document.createElement('div');
        const icon = options.icon || MapboxConfig.markerIcon;

        el.className = 'mapbox-marker';
        el.style.backgroundImage = `url(${icon.iconUrl})`;
        el.style.width = `${icon.iconSize[0]}px`;
        el.style.height = `${icon.iconSize[1]}px`;
        el.style.backgroundSize = 'cover';
        el.style.cursor = 'pointer';

        // Create marker
        const marker = new mapboxgl.Marker({
            element: el,
            anchor: 'bottom',
            draggable: options.draggable || this.options.draggable,
            rotationAlignment: 'map',
            pitchAlignment: 'map',
        })
            .setLngLat([lng, lat])
            .addTo(this.map);

        // Setup drag handler
        if (options.draggable || this.options.draggable) {
            marker.on('dragend', () => {
                this.handleMarkerDrag(key, marker);
            });
        }

        // Add popup if provided
        if (options.popup) {
            const popup = new mapboxgl.Popup({ offset: 25 })
                .setHTML(options.popup);
            marker.setPopup(popup);
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
            this.markers[key].setLngLat([lng, lat]);
        }
    }

    /**
     * Remove marker from map
     * @param {string} key - Marker key
     */
    removeMarker(key) {
        if (this.markers[key]) {
            this.markers[key].remove();
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
            const popup = new mapboxgl.Popup({ offset: 25 })
                .setHTML(content);
            this.markers[key].setPopup(popup);
        }
    }

    /**
     * Add or update accuracy circle
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {number} accuracy - Accuracy in meters
     */
    updateAccuracyCircle(lat, lng, accuracy) {
        // Wait for map to be initialized first
        if (!this.isInitialized) {
            this.map.once('load', () => {
                this.updateAccuracyCircle(lat, lng, accuracy);
            });
            return;
        }

        const sourceId = 'accuracy-circle-source';
        const layerId = 'accuracy-circle-layer';

        // Create circle data
        const circle = this.createCircle([lng, lat], accuracy);
        const color = MapboxConfig.getAccuracyColor(accuracy);

        if (!this.map.getSource(sourceId)) {
            // Add source and layer
            this.map.addSource(sourceId, {
                type: 'geojson',
                data: circle,
            });

            this.map.addLayer({
                id: layerId,
                type: 'fill',
                source: sourceId,
                paint: {
                    'fill-color': color,
                    'fill-opacity': MapboxConfig.accuracyCircle.fillOpacity,
                },
            });

            this.map.addLayer({
                id: `${layerId}-outline`,
                type: 'line',
                source: sourceId,
                paint: {
                    'line-color': color,
                    'line-width': MapboxConfig.accuracyCircle.strokeWidth,
                },
            });
        } else {
            // Update existing circle
            this.map.getSource(sourceId).setData(circle);
            this.map.setPaintProperty(layerId, 'fill-color', color);
            this.map.setPaintProperty(`${layerId}-outline`, 'line-color', color);
        }
    }

    /**
     * Create circle GeoJSON
     * @param {array} center - [lng, lat]
     * @param {number} radiusInMeters - Radius in meters
     * @returns {object} - GeoJSON object
     */
    createCircle(center, radiusInMeters) {
        const points = 64;
        const coords = {
            latitude: center[1],
            longitude: center[0],
        };

        const km = radiusInMeters / 1000;
        const ret = [];
        const distanceX = km / (111.320 * Math.cos(coords.latitude * Math.PI / 180));
        const distanceY = km / 110.574;

        for (let i = 0; i < points; i++) {
            const theta = (i / points) * (2 * Math.PI);
            const x = distanceX * Math.cos(theta);
            const y = distanceY * Math.sin(theta);

            ret.push([coords.longitude + x, coords.latitude + y]);
        }
        ret.push(ret[0]);

        return {
            type: 'Feature',
            geometry: {
                type: 'Polygon',
                coordinates: [ret],
            },
        };
    }

    /**
     * Pan map to coordinates
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {boolean} force - Force pan
     */
    panTo(lat, lng, force = false) {
        if (!this.map) return;

        const currentCenter = this.map.getCenter();
        const distance = Math.sqrt(
            Math.pow(currentCenter.lat - lat, 2) +
            Math.pow(currentCenter.lng - lng, 2)
        );

        if (force || distance > MapboxConfig.updateThresholds.panThreshold) {
            this.map.easeTo({
                center: [lng, lat],
                duration: MapboxConfig.animation.duration,
                easing: (t) => t,
            });
        }
    }

    /**
     * Set map view
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {number} zoom - Zoom level
     */
    setView(lat, lng, zoom = null) {
        if (!this.map) return;

        this.map.flyTo({
            center: [lng, lat],
            zoom: zoom || this.options.zoom,
            duration: MapboxConfig.animation.duration,
        });
    }

    /**
     * Fit bounds to show all markers
     */
    fitBounds() {
        if (!this.map || Object.keys(this.markers).length === 0) return;

        const bounds = new mapboxgl.LngLatBounds();
        Object.values(this.markers).forEach(marker => {
            bounds.extend(marker.getLngLat());
        });

        this.map.fitBounds(bounds, {
            padding: 50,
            maxZoom: 16,
        });
    }

    /**
     * Initialize routing between two points
     * @param {number} startLat - Start latitude
     * @param {number} startLng - Start longitude
     * @param {number} endLat - End latitude
     * @param {number} endLng - End longitude
     */
    async initRouting(startLat, startLng, endLat, endLng) {
        // Wait for map to be initialized first
        if (!this.isInitialized) {
            this.map.once('load', () => {
                this.initRouting(startLat, startLng, endLat, endLng);
            });
            return;
        }

        const coords = `${startLng},${startLat};${endLng},${endLat}`;
        const url = `https://api.mapbox.com/directions/v5/${MapboxConfig.routing.profile}/${coords}?geometries=geojson&access_token=${mapboxgl.accessToken}`;

        try {
            const response = await fetch(url);
            const data = await response.json();

            if (data.routes && data.routes.length > 0) {
                const route = data.routes[0].geometry;

                // Add or update route source
                if (!this.map.getSource(this.routeSourceId)) {
                    this.map.addSource(this.routeSourceId, {
                        type: 'geojson',
                        data: {
                            type: 'Feature',
                            properties: {},
                            geometry: route,
                        },
                    });

                    this.map.addLayer({
                        id: this.routeLayerId,
                        type: 'line',
                        source: this.routeSourceId,
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round',
                        },
                        paint: {
                            'line-color': MapboxConfig.routing.routeColor,
                            'line-width': MapboxConfig.routing.routeWidth,
                            'line-opacity': MapboxConfig.routing.routeOpacity,
                        },
                    });
                } else {
                    this.map.getSource(this.routeSourceId).setData({
                        type: 'Feature',
                        properties: {},
                        geometry: route,
                    });
                }
            }
        } catch (error) {
            console.error('Error fetching route:', error);
        }
    }

    /**
     * Update routing waypoints
     */
    updateRouting(startLat, startLng, endLat, endLng) {
        return this.initRouting(startLat, startLng, endLat, endLng);
    }

    /**
     * Start GPS tracking
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
            MapboxConfig.gpsOptions
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
     */
    handleMapClick(e) {
        this.isUpdatingFromMap = true;

        const lat = e.lngLat.lat;
        const lng = e.lngLat.lng;

        if (this.options.onMapClick) {
            this.options.onMapClick(lat, lng);
        }

        setTimeout(() => {
            this.isUpdatingFromMap = false;
        }, 100);
    }

    /**
     * Handle marker drag
     */
    handleMarkerDrag(key, marker) {
        this.isUpdatingFromMap = true;

        const lngLat = marker.getLngLat();

        if (this.options.onLocationUpdate) {
            this.options.onLocationUpdate(lngLat.lat, lngLat.lng, key);
        }

        setTimeout(() => {
            this.isUpdatingFromMap = false;
        }, 100);
    }

    /**
     * Rotate map to bearing
     */
    rotateTo(bearing) {
        if (!this.map || !this.options.rotate) return;

        this.currentBearing = bearing;
        this.map.setBearing(bearing);
    }

    /**
     * Rotate map smoothly
     */
    smoothRotateTo(bearing) {
        if (!this.map || !this.options.rotate) return;

        this.currentBearing = bearing;
        this.map.easeTo({
            bearing: bearing,
            duration: MapboxConfig.rotation.transitionDuration,
        });
    }

    /**
     * Reset rotation
     */
    resetRotation() {
        this.rotateTo(0);
    }

    /**
     * Get current bearing
     */
    getBearing() {
        return this.currentBearing;
    }

    /**
     * Calculate bearing between two coordinates
     */
    calculateBearing(lat1, lng1, lat2, lng2) {
        const toRad = (deg) => deg * Math.PI / 180;
        const toDeg = (rad) => rad * 180 / Math.PI;

        const dLng = toRad(lng2 - lng1);
        const y = Math.sin(dLng) * Math.cos(toRad(lat2));
        const x = Math.cos(toRad(lat1)) * Math.sin(toRad(lat2)) -
                  Math.sin(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.cos(dLng);

        let bearing = toDeg(Math.atan2(y, x));
        bearing = (bearing + 360) % 360;

        return bearing;
    }

    /**
     * Start compass tracking
     */
    startCompassTracking() {
        if (!this.options.enableCompass) {
            return;
        }

        if (!window.DeviceOrientationEvent) {
            console.log('Device orientation not supported');
            return;
        }

        if (typeof DeviceOrientationEvent.requestPermission === 'function') {
            DeviceOrientationEvent.requestPermission()
                .then(permissionState => {
                    if (permissionState === 'granted') {
                        this.attachOrientationListener();
                    }
                })
                .catch(console.error);
        } else {
            this.attachOrientationListener();
        }
    }

    /**
     * Attach orientation listener
     */
    attachOrientationListener() {
        this.handleOrientation = this.handleOrientation.bind(this);
        window.addEventListener('deviceorientationabsolute', this.handleOrientation, true);
        window.addEventListener('deviceorientation', this.handleOrientation, true);
        this.compassEnabled = true;
    }

    /**
     * Handle device orientation
     */
    handleOrientation(event) {
        const now = Date.now();
        if (now - this.lastCompassUpdate < this.compassThrottle) {
            return;
        }
        this.lastCompassUpdate = now;

        let heading = null;

        if (event.absolute && event.alpha !== null) {
            heading = event.alpha;
        } else if (event.webkitCompassHeading !== undefined) {
            heading = event.webkitCompassHeading;
        } else if (event.alpha !== null) {
            heading = 360 - event.alpha;
        }

        if (heading !== null) {
            this.compassHeading = heading;

            if (this.options.smoothCompass) {
                this.smoothRotateTo(heading);
            } else {
                this.rotateTo(heading);
            }
        }
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
     * Rotate marker
     */
    rotateMarker(key, angle) {
        if (this.markers[key]) {
            const el = this.markers[key].getElement();
            if (el) {
                el.style.transform = `rotate(${angle}deg)`;
            }
        }
    }

    /**
     * Add custom style switcher control
     */
    addStyleSwitcher() {
        const styles = [
            { name: 'Standard', id: 'standard' },
            { name: 'Satellite Streets', id: 'satelliteStreets' },
            { name: 'Navigation Day', id: 'navigationDay' },
            { name: 'Navigation Night', id: 'navigationNight' },
        ];

        class StyleSwitcherControl {
            constructor(styles, manager) {
                this.styles = styles;
                this.manager = manager;
            }

            onAdd(map) {
                this._map = map;
                this._container = document.createElement('div');
                this._container.className = 'mapboxgl-ctrl mapboxgl-ctrl-group';

                // Create button with layers icon (similar to Leaflet)
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'mapboxgl-ctrl-icon mapboxgl-ctrl-layers';
                button.title = 'Layers';
                button.setAttribute('aria-label', 'Toggle layers');

                // Add layers icon (similar to Leaflet)
                button.style.backgroundImage = `url('data:image/svg+xml;charset=utf-8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path fill="%23333" d="M10 1L3 5l7 4 7-4-7-4zm0 8L3 5v4l7 4 7-4V5l-7 4z"/><path fill="%23333" d="M3 13l7 4 7-4v-2l-7 4-7-4v2z"/></svg>')`;
                button.style.backgroundRepeat = 'no-repeat';
                button.style.backgroundPosition = 'center';
                button.style.backgroundSize = '70%';
                button.style.width = '29px';
                button.style.height = '29px';
                button.style.display = 'block';

                // Create dropdown (similar style to Leaflet)
                const dropdown = document.createElement('div');
                dropdown.className = 'mapbox-style-switcher-dropdown';
                dropdown.style.cssText = `
                    display: none;
                    position: absolute;
                    background: white;
                    border: 2px solid rgba(0,0,0,0.2);
                    border-radius: 4px;
                    box-shadow: 0 1px 5px rgba(0,0,0,0.4);
                    padding: 10px;
                    min-width: 180px;
                    right: 0;
                    top: 100%;
                    margin-top: 10px;
                    z-index: 9999;
                    font-family: "Helvetica Neue", Arial, Helvetica, sans-serif;
                    font-size: 12px;
                    line-height: 1.5;
                `;

                // Add title (like Leaflet)
                const title = document.createElement('div');
                title.textContent = 'Base Layers';
                title.style.cssText = `
                    font-weight: bold;
                    margin-bottom: 8px;
                    padding-bottom: 6px;
                    border-bottom: 1px solid #ddd;
                    color: #333;
                `;
                dropdown.appendChild(title);

                // Add style options as radio buttons (like Leaflet)
                this.styles.forEach((style, index) => {
                    const label = document.createElement('label');
                    label.style.cssText = `
                        display: flex;
                        align-items: center;
                        padding: 4px 0;
                        cursor: pointer;
                        color: #333;
                    `;

                    const radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = 'mapbox-style';
                    radio.value = style.id;
                    radio.checked = index === 0; // First one checked by default
                    radio.style.marginRight = '8px';

                    const text = document.createElement('span');
                    text.textContent = style.name;

                    label.onmouseover = () => {
                        label.style.background = '#f4f4f4';
                    };
                    label.onmouseout = () => {
                        label.style.background = 'transparent';
                    };

                    label.onclick = () => {
                        this.manager.changeStyle(style.id);
                        dropdown.style.display = 'none';
                    };

                    label.appendChild(radio);
                    label.appendChild(text);
                    dropdown.appendChild(label);
                });

                // Toggle dropdown
                button.onclick = (e) => {
                    e.stopPropagation();
                    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
                };

                // Close dropdown when clicking outside
                document.addEventListener('click', () => {
                    dropdown.style.display = 'none';
                });

                this._container.appendChild(button);
                this._container.appendChild(dropdown);
                this._container.style.position = 'relative';

                return this._container;
            }

            onRemove() {
                this._container.parentNode.removeChild(this._container);
                this._map = undefined;
            }
        }

        const control = new StyleSwitcherControl(styles, this);
        this.map.addControl(control, 'top-right');
        console.log('✅ Mapbox style switcher control added to map');
    }

    /**
     * Change map style
     * @param {string} styleId - Style ID from config
     */
    changeStyle(styleId) {
        if (!this.map) return;

        const styleUrl = MapboxConfig.getStyleUrl(styleId);

        // Store current markers and other state
        const currentCenter = this.map.getCenter();
        const currentZoom = this.map.getZoom();
        const currentBearing = this.map.getBearing();
        const currentPitch = this.map.getPitch();

        // Change style
        this.map.setStyle(styleUrl);

        // Restore view
        this.map.once('style.load', () => {
            this.map.setCenter(currentCenter);
            this.map.setZoom(currentZoom);
            this.map.setBearing(currentBearing);
            this.map.setPitch(currentPitch);

            // Re-add markers (they're removed when style changes)
            Object.entries(this.markers).forEach(([key, marker]) => {
                // Markers are DOM elements, they persist across style changes
                // No need to re-add them
            });
        });
    }

    /**
     * Destroy map
     */
    destroy() {
        this.stopGPSTracking();
        this.stopCompassTracking();

        if (this.rotationAnimationFrame) {
            cancelAnimationFrame(this.rotationAnimationFrame);
            this.rotationAnimationFrame = null;
        }

        Object.values(this.markers).forEach(marker => marker.remove());
        this.markers = {};

        if (this.map) {
            this.map.remove();
            this.map = null;
        }

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
    window.MapboxMapManager = MapboxMapManager;
}
