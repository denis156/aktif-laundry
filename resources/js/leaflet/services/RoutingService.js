import { LeafletConfig } from '../config.js';

/**
 * Routing Service
 * Handles OSRM and Mapbox routing (KISS principle)
 */
export class RoutingService {
    constructor(map) {
        this.map = map;
        this.routeLayer = null;
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
     * Initialize routing between two points (OSRM primary, Mapbox fallback)
     * @param {number} startLat - Start latitude
     * @param {number} startLng - Start longitude
     * @param {number} endLat - End latitude
     * @param {number} endLng - End longitude
     */
    async initRouting(startLat, startLng, endLat, endLng) {
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
     * Clear route from map
     */
    clearRoute() {
        if (this.routeLayer) {
            this.map.removeLayer(this.routeLayer);
            this.routeLayer = null;
        }
    }

    /**
     * Destroy service and cleanup
     */
    destroy() {
        this.clearRoute();
        this.map = null;
    }
}
