/**
 * Geocoding Service
 * Handles Mapbox Search Box API and Geocoding API (KISS principle)
 */
export class GeocodingService {
    constructor() {
        this.sessionToken = null;
        this.sessionStartTime = null;
        this.sessionMaxDuration = 60 * 60 * 1000; // 60 minutes
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
     * Generate or get session token (for billing optimization)
     * @returns {string} - UUIDv4 session token
     */
    getSessionToken() {
        const now = Date.now();

        // Create new session if no token or session expired
        if (!this.sessionToken || !this.sessionStartTime ||
            (now - this.sessionStartTime) > this.sessionMaxDuration) {
            this.sessionToken = this.generateUUID();
            this.sessionStartTime = now;
        }

        return this.sessionToken;
    }

    /**
     * Generate UUIDv4
     * @returns {string}
     */
    generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    /**
     * Search Box API - Suggest (autocomplete)
     * Returns search suggestions without coordinates
     *
     * @param {string} query - Search query
     * @param {object} options - Search options
     * @param {number} options.limit - Max results (default: 5, max: 10)
     * @param {string} options.proximity - Bias results: 'ip' or 'lng,lat'
     * @param {string} options.language - ISO language code (e.g., 'id', 'en')
     * @param {string} options.types - Feature types: 'address', 'poi', 'place', etc.
     * @param {string} options.country - ISO 3166 country code (e.g., 'id')
     * @param {string} options.bbox - Bounding box: 'minLng,minLat,maxLng,maxLat'
     * @returns {Promise<Array>} - Array of suggestions
     */
    async suggest(query, options = {}) {
        const token = this.getMapboxToken();
        if (!token) {
            throw new Error('Mapbox token not available');
        }

        if (!query || query.trim().length === 0) {
            return [];
        }

        const sessionToken = this.getSessionToken();

        // Build query params
        const params = new URLSearchParams({
            q: query.trim(),
            access_token: token,
            session_token: sessionToken,
            limit: options.limit || 5,
        });

        // Add optional parameters
        if (options.proximity) {
            params.append('proximity', options.proximity);
        }
        if (options.language) {
            params.append('language', options.language);
        }
        if (options.types) {
            params.append('types', options.types);
        }
        if (options.country) {
            params.append('country', options.country);
        }
        if (options.bbox) {
            params.append('bbox', options.bbox);
        }

        const url = `https://api.mapbox.com/search/searchbox/v1/suggest?${params.toString()}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Mapbox Suggest API error: ${response.status}`);
            }

            const data = await response.json();
            return data.suggestions || [];
        } catch (error) {
            console.error('[Error] Geocoding suggest failed:', error.message);
            return [];
        }
    }

    /**
     * Search Box API - Retrieve
     * Get detailed information including coordinates for a suggestion
     *
     * @param {string} mapboxId - Mapbox ID from suggest result
     * @returns {Promise<object|null>} - Feature with coordinates
     */
    async retrieve(mapboxId) {
        const token = this.getMapboxToken();
        if (!token) {
            throw new Error('Mapbox token not available');
        }

        if (!mapboxId) {
            throw new Error('Mapbox ID is required');
        }

        const sessionToken = this.getSessionToken();

        const params = new URLSearchParams({
            access_token: token,
            session_token: sessionToken,
        });

        const url = `https://api.mapbox.com/search/searchbox/v1/retrieve/${mapboxId}?${params.toString()}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Mapbox Retrieve API error: ${response.status}`);
            }

            const data = await response.json();
            if (data.features && data.features.length > 0) {
                return data.features[0];
            }

            return null;
        } catch (error) {
            console.error('[Error] Geocoding retrieve failed:', error.message);
            return null;
        }
    }

    /**
     * Geocoding API - Forward Geocoding
     * Convert text address to coordinates
     *
     * @param {string} query - Search query
     * @param {object} options - Search options
     * @param {number} options.limit - Max results (default: 5, max: 10)
     * @param {string} options.proximity - Bias results: 'lng,lat'
     * @param {string} options.language - ISO language code
     * @param {string} options.types - Feature types
     * @param {string} options.country - ISO 3166 country code
     * @param {boolean} options.autocomplete - Enable partial matches (default: true)
     * @returns {Promise<Array>} - Array of features with coordinates
     */
    async forward(query, options = {}) {
        const token = this.getMapboxToken();
        if (!token) {
            throw new Error('Mapbox token not available');
        }

        if (!query || query.trim().length === 0) {
            return [];
        }

        // Build query params
        const params = new URLSearchParams({
            q: query.trim(),
            access_token: token,
            limit: options.limit || 5,
            autocomplete: options.autocomplete !== undefined ? options.autocomplete : true,
        });

        // Add optional parameters
        if (options.proximity) {
            params.append('proximity', options.proximity);
        }
        if (options.language) {
            params.append('language', options.language);
        }
        if (options.types) {
            params.append('types', options.types);
        }
        if (options.country) {
            params.append('country', options.country);
        }

        const url = `https://api.mapbox.com/search/geocode/v6/forward?${params.toString()}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Mapbox Forward Geocoding API error: ${response.status}`);
            }

            const data = await response.json();
            return data.features || [];
        } catch (error) {
            console.error('[Error] Forward geocoding failed:', error.message);
            return [];
        }
    }

    /**
     * Geocoding API - Reverse Geocoding
     * Convert coordinates to place name
     *
     * @param {number} lng - Longitude
     * @param {number} lat - Latitude
     * @param {object} options - Search options
     * @param {number} options.limit - Max results (default: 1)
     * @param {string} options.language - ISO language code
     * @param {string} options.types - Feature types to return
     * @returns {Promise<Array>} - Array of features
     */
    async reverse(lng, lat, options = {}) {
        const token = this.getMapboxToken();
        if (!token) {
            throw new Error('Mapbox token not available');
        }

        // Build query params
        const params = new URLSearchParams({
            longitude: lng,
            latitude: lat,
            access_token: token,
            limit: options.limit || 1,
        });

        // Add optional parameters
        if (options.language) {
            params.append('language', options.language);
        }
        if (options.types) {
            params.append('types', options.types);
        }

        const url = `https://api.mapbox.com/search/geocode/v6/reverse?${params.toString()}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Mapbox Reverse Geocoding API error: ${response.status}`);
            }

            const data = await response.json();
            return data.features || [];
        } catch (error) {
            console.error('[Error] Reverse geocoding failed:', error.message);
            return [];
        }
    }

    /**
     * Helper: Extract coordinates from feature
     * @param {object} feature - GeoJSON feature
     * @returns {object|null} - {lng, lat} or null
     */
    getCoordinates(feature) {
        if (!feature || !feature.geometry || !feature.geometry.coordinates) {
            return null;
        }

        const [lng, lat] = feature.geometry.coordinates;
        return { lng, lat };
    }

    /**
     * Helper: Format feature as simple result object
     * @param {object} feature - GeoJSON feature
     * @returns {object} - Formatted result
     */
    formatResult(feature) {
        const coords = this.getCoordinates(feature);
        const properties = feature.properties || {};

        return {
            name: properties.name || properties.place_formatted || 'Unknown',
            address: properties.place_formatted || properties.full_address || '',
            coordinates: coords,
            mapboxId: properties.mapbox_id || null,
            featureType: properties.feature_type || feature.type,
            context: properties.context || {},
        };
    }

    /**
     * Clear session token (force new session)
     */
    clearSession() {
        this.sessionToken = null;
        this.sessionStartTime = null;
    }

    /**
     * Destroy service and cleanup
     */
    destroy() {
        this.clearSession();
    }
}
