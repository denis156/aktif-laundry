import { LeafletConfig } from '../config.js';

/**
 * Compass Tracker Service
 * Handles device orientation and map rotation (KISS principle)
 */
export class CompassTracker {
    constructor(map, options = {}) {
        this.map = map;
        this.compassHeading = null;
        this.currentBearing = 0;
        this.targetBearing = 0;
        this.lastCompassUpdate = 0;
        this.compassThrottle = 100; // ms
        this.rotationAnimationFrame = null;
        this.compassEnabled = false;
        this.smoothRotation = options.smoothRotation !== undefined ? options.smoothRotation : true;
        this.handleOrientation = null;
        this.onRotationUpdate = options.onRotationUpdate || null;
    }

    /**
     * Start compass tracking
     */
    start() {
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
        this.handleOrientation = this.onOrientationChange.bind(this);
        window.addEventListener('deviceorientationabsolute', this.handleOrientation, true);
        window.addEventListener('deviceorientation', this.handleOrientation, true);
        this.compassEnabled = true;
    }

    /**
     * Handle device orientation event
     * @param {DeviceOrientationEvent} event - Device orientation event
     */
    onOrientationChange(event) {
        // Throttle updates for smooth rotation
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

            // Apply rotation
            if (this.smoothRotation) {
                this.smoothRotateTo(heading);
            } else {
                this.rotateTo(heading);
            }
        }
    }

    /**
     * Rotate map to bearing (in degrees)
     * @param {number} bearing - Bearing in degrees (0-360)
     */
    rotateTo(bearing) {
        if (!this.map) return;

        this.currentBearing = bearing;
        if (this.map.setBearing) {
            this.map.setBearing(-bearing);
        }

        // Trigger callback
        if (this.onRotationUpdate) {
            this.onRotationUpdate(bearing);
        }
    }

    /**
     * Smooth rotate to bearing
     * @param {number} targetHeading - Target compass heading
     */
    smoothRotateTo(targetHeading) {
        if (!this.map) return;

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

                // Trigger callback
                if (this.onRotationUpdate) {
                    this.onRotationUpdate(this.currentBearing);
                }

                this.rotationAnimationFrame = requestAnimationFrame(animate);
            } else {
                // Snap to final position
                this.currentBearing = this.targetBearing;
                if (this.map.setBearing) {
                    this.map.setBearing(-this.currentBearing);
                }

                // Trigger callback
                if (this.onRotationUpdate) {
                    this.onRotationUpdate(this.currentBearing);
                }
            }
        };

        animate();
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
     * Reset map rotation to north (0 degrees)
     */
    reset() {
        this.rotateTo(0);
    }

    /**
     * Get current bearing
     * @returns {number} - Current bearing in degrees
     */
    getCurrentBearing() {
        return this.currentBearing;
    }

    /**
     * Get compass heading
     * @returns {number|null} - Compass heading or null
     */
    getCompassHeading() {
        return this.compassHeading;
    }

    /**
     * Stop compass tracking
     */
    stop() {
        if (this.compassEnabled && this.handleOrientation) {
            window.removeEventListener('deviceorientationabsolute', this.handleOrientation, true);
            window.removeEventListener('deviceorientation', this.handleOrientation, true);
            this.compassEnabled = false;
            this.compassHeading = null;
        }

        // Cancel any pending rotation animation
        if (this.rotationAnimationFrame) {
            cancelAnimationFrame(this.rotationAnimationFrame);
            this.rotationAnimationFrame = null;
        }
    }

    /**
     * Check if compass is active
     * @returns {boolean}
     */
    isActive() {
        return this.compassEnabled;
    }

    /**
     * Destroy tracker and cleanup
     */
    destroy() {
        this.stop();
        this.map = null;
        this.onRotationUpdate = null;
    }
}
