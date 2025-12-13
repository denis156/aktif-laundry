import { BaseCompassTracker } from './BaseCompassTracker.js';
import { LeafletConfig } from '../../config.js';

/**
 * Android Compass Tracker
 * Android-specific compass implementation using alpha/beta/gamma
 */
export class AndroidCompassTracker extends BaseCompassTracker {
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
        return 'android';
    }

    // ==========================================
    // Compass Calculation (Android-specific)
    // ==========================================

    /**
     * Calculate compass heading from device orientation event
     * Android uses alpha (with magnetometer) for compass direction
     *
     * @param {DeviceOrientationEvent} event
     * @returns {number|null} - Heading in degrees (0-360) or null
     */
    calculateCompassHeading(event) {
        const { absolute, alpha, beta, gamma } = event;

        // Android: Calculate from alpha, beta, gamma
        if (alpha === null) {
            return null;
        }

        // Check if device has magnetometer (absolute: true)
        if (!absolute) {
            // No magnetometer - cannot determine true compass
            this.calibrationQuality = 0;
            return null;
        }

        // Check device tilt
        const tilt = this.calculateDeviceTilt(beta, gamma);
        const isTooTilted = tilt > LeafletConfig.compass.maxTiltAngle;

        if (isTooTilted && !LeafletConfig.compass.compensateTilt) {
            // Device too tilted and compensation disabled
            return null;
        }

        // Calculate heading - use alpha directly
        let heading = alpha;

        // Apply compass calibration offset
        if (LeafletConfig.compass.calibrationOffset) {
            heading = (heading + LeafletConfig.compass.calibrationOffset) % 360;
            if (heading < 0) heading += 360;
        }

        // Estimate calibration quality based on tilt
        this.calibrationQuality = this.estimateCalibrationQuality(tilt, absolute);

        return heading;
    }

    /**
     * Get map bearing value for Android
     * Android needs positive bearing for correct rotation
     *
     * @param {number} bearing - Compass bearing (0-360)
     * @returns {number} - Map bearing
     */
    getMapBearing(bearing) {
        // Android needs positive bearing
        return bearing;
    }

    // ==========================================
    // Android-specific Helper Methods
    // ==========================================

    /**
     * Calculate device tilt angle (how far from horizontal)
     * @param {number} beta - Front-to-back tilt (-180 to 180)
     * @param {number} gamma - Left-to-right tilt (-90 to 90)
     * @returns {number} - Tilt angle in degrees (0 = horizontal)
     */
    calculateDeviceTilt(beta, gamma) {
        if (beta === null || gamma === null) {
            return 0;
        }

        // Calculate total tilt using pythagorean theorem
        // Normalize beta to 0-90 range (we only care about absolute tilt)
        const normalizedBeta = Math.abs(beta) > 90
            ? 180 - Math.abs(beta)
            : Math.abs(beta);

        const tilt = Math.sqrt(
            Math.pow(normalizedBeta, 2) +
            Math.pow(gamma, 2)
        );

        return tilt;
    }

    /**
     * Estimate calibration quality
     * @param {number} tilt - Device tilt angle
     * @param {boolean} absolute - Has magnetometer
     * @returns {number} - Quality (0-1)
     */
    estimateCalibrationQuality(tilt, absolute) {
        if (!absolute) {
            return 0; // No magnetometer
        }

        // Quality decreases with tilt
        // 0° tilt = 1.0 quality
        // 45° tilt = 0.5 quality
        // 90° tilt = 0 quality
        const tiltFactor = Math.max(0, 1 - (tilt / 90));

        return tiltFactor;
    }

    /**
     * Compensate compass heading for device tilt (Advanced - currently unused)
     * Uses simplified tilt compensation algorithm
     *
     * @param {number} alpha - Compass heading (0-360)
     * @param {number} beta - Front-to-back tilt (-180 to 180)
     * @param {number} gamma - Left-to-right tilt (-90 to 90)
     * @returns {number} - Compensated heading (0-360)
     */
    compensateTilt(alpha, beta, gamma) {
        // For small tilts, no compensation needed
        const tilt = this.calculateDeviceTilt(beta, gamma);
        if (tilt < 10) {
            return alpha;
        }

        // Convert to radians
        const toRad = (deg) => deg * Math.PI / 180;
        const toDeg = (rad) => rad * 180 / Math.PI;

        const alphaRad = toRad(alpha);
        const betaRad = toRad(beta);
        const gammaRad = toRad(gamma);

        // Simplified tilt compensation using rotation matrix
        // This approximation works well for tilts up to ~45 degrees
        const cosAlpha = Math.cos(alphaRad);
        const sinAlpha = Math.sin(alphaRad);
        const cosBeta = Math.cos(betaRad);
        const sinBeta = Math.sin(betaRad);
        const cosGamma = Math.cos(gammaRad);
        const sinGamma = Math.sin(gammaRad);

        // Calculate compass heading on horizontal plane
        const x = cosAlpha * cosBeta + sinAlpha * sinBeta * sinGamma;
        const y = sinAlpha * cosBeta - cosAlpha * sinBeta * sinGamma;

        let compensatedHeading = toDeg(Math.atan2(y, x));

        // Normalize to 0-360
        if (compensatedHeading < 0) {
            compensatedHeading += 360;
        }

        return compensatedHeading % 360;
    }
}
