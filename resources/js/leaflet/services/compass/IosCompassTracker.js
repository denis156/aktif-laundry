import { BaseCompassTracker } from './BaseCompassTracker.js';

/**
 * iOS Compass Tracker
 * iOS-specific compass implementation using webkitCompassHeading
 */
export class IosCompassTracker extends BaseCompassTracker {
    constructor(map, options = {}) {
        super(map, options);
    }

    // ==========================================
    // Platform Info
    // ==========================================

    /**
     * Get platform name
     * @returns {string}
     */
    getPlatform() {
        return 'ios';
    }

    // ==========================================
    // Compass Calculation (iOS-specific)
    // ==========================================

    /**
     * Calculate compass heading from device orientation event
     * iOS uses webkitCompassHeading which is already calibrated and accurate
     *
     * @param {DeviceOrientationEvent} event
     * @returns {number|null} - Heading in degrees (0-360) or null
     */
    calculateCompassHeading(event) {
        const { webkitCompassHeading } = event;

        // iOS: Use webkitCompassHeading (most accurate, already compensated)
        if (webkitCompassHeading !== undefined) {
            // iOS compass is always well calibrated
            this.calibrationQuality = 1.0;

            return webkitCompassHeading;
        }

        // webkitCompassHeading not available (shouldn't happen on iOS)
        this.calibrationQuality = 0;
        return null;
    }

    /**
     * Get map bearing value for iOS
     * iOS needs negative bearing for correct rotation
     *
     * @param {number} bearing - Compass bearing (0-360)
     * @returns {number} - Map bearing
     */
    getMapBearing(bearing) {
        // iOS needs negative bearing
        return -bearing;
    }
}
