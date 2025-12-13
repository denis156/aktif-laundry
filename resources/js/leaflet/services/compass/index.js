import { IosCompassTracker } from './IosCompassTracker.js';
import { AndroidCompassTracker } from './AndroidCompassTracker.js';

/**
 * Detect device platform
 * @returns {string} - 'ios', 'android', or 'desktop'
 */
function detectPlatform() {
    const ua = navigator.userAgent || navigator.vendor || window.opera;

    // iOS detection
    if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) {
        return 'ios';
    }

    // Android detection
    if (/android/i.test(ua)) {
        return 'android';
    }

    return 'desktop';
}

/**
 * Create appropriate CompassTracker based on platform
 * Factory function that returns platform-specific compass tracker
 *
 * @param {L.Map} map - Leaflet map instance
 * @param {object} options - Compass tracker options
 * @returns {BaseCompassTracker} - Platform-specific compass tracker
 */
export function createCompassTracker(map, options = {}) {
    const platform = detectPlatform();

    switch (platform) {
        case 'ios':
            return new IosCompassTracker(map, options);

        case 'android':
            return new AndroidCompassTracker(map, options);

        default:
            // Desktop or unknown - use Android implementation as fallback
            // (it works with standard DeviceOrientationEvent)
            return new AndroidCompassTracker(map, options);
    }
}

// Export individual classes for advanced usage
export { IosCompassTracker } from './IosCompassTracker.js';
export { AndroidCompassTracker } from './AndroidCompassTracker.js';
export { BaseCompassTracker } from './BaseCompassTracker.js';

// Backward compatibility: Export as CompassTracker class-like
export class CompassTracker {
    constructor(map, options = {}) {
        // Return platform-specific instance
        return createCompassTracker(map, options);
    }
}
