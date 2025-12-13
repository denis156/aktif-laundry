/**
 * Mapbox Module Entry Point
 * Import Mapbox GL JS dan export semua komponen
 */

// Import Mapbox GL JS
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

// Make Mapbox available globally
window.mapboxgl = mapboxgl;

// Export local modules
export { MapboxConfig } from './config.js';
export { MapboxMapManager } from './manager.js';

// Import for global access
import { MapboxConfig } from './config.js';
import { MapboxMapManager } from './manager.js';

// Make everything available globally
if (typeof window !== 'undefined') {
    window.MapboxUtils = {
        // Config
        config: MapboxConfig,
        Config: MapboxConfig,

        // Manager Class
        MapManager: MapboxMapManager,
        MapboxMapManager,

        // Check availability
        isAvailable: () => MapboxConfig.isAvailable(),
    };
}
