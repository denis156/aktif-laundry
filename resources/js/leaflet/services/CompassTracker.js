import { LeafletConfig } from '../config.js';

/**
 * Advanced Compass Tracker Service
 * Handles device orientation with full iOS/Android support and tilt compensation
 */
export class CompassTracker {
    constructor(map, options = {}) {
        this.map = map;
        this.options = options;

        // Compass state
        this.compassHeading = null;
        this.currentBearing = 0;
        this.targetBearing = 0;
        this.lastCompassUpdate = 0;
        this.compassEnabled = false;

        // Platform detection
        this.platform = this.detectPlatform();
        this.isIOS = this.platform === 'ios';
        this.isAndroid = this.platform === 'android';

        // Event handlers
        this.handleOrientation = null;
        this.handleScreenOrientation = null;

        // Animation
        this.rotationAnimationFrame = null;

        // Callbacks
        this.onRotationUpdate = options.onRotationUpdate || null;
        this.onError = options.onError || null;

        // Settings from options or config
        this.smoothRotation = options.smoothRotation !== undefined
            ? options.smoothRotation
            : true;

        this.compassThrottle = LeafletConfig.compass.throttleInterval;

        // GPS fallback data (will be set externally)
        this.gpsData = {
            speed: 0,
            accuracy: Infinity,
            lastLat: null,
            lastLng: null,
        };

        // Calibration
        this.calibrationQuality = 0;
    }

    // ==========================================
    // Platform Detection
    // ==========================================

    /**
     * Detect device platform
     * @returns {string} - 'ios', 'android', or 'desktop'
     */
    detectPlatform() {
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
     * Check if device supports DeviceOrientation
     * @returns {boolean}
     */
    isDeviceOrientationSupported() {
        return 'DeviceOrientationEvent' in window;
    }

    /**
     * Check if iOS 13+ permission API is available
     * @returns {boolean}
     */
    requiresPermission() {
        return typeof DeviceOrientationEvent?.requestPermission === 'function';
    }

    // ==========================================
    // Screen Orientation
    // ==========================================

    /**
     * Get current screen orientation angle
     * @returns {number} - 0, 90, 180, or 270 degrees
     */
    getScreenOrientation() {
        // Modern API
        if (screen.orientation) {
            return screen.orientation.angle || 0;
        }

        // Legacy API
        if (window.orientation !== undefined) {
            return Math.abs(window.orientation);
        }

        return 0;
    }

    /**
     * Listen for screen orientation changes
     */
    attachScreenOrientationListener() {
        this.handleScreenOrientation = () => {
            // Screen orientation changed, force compass recalculation
            this.lastCompassUpdate = 0;
        };

        if (screen.orientation) {
            screen.orientation.addEventListener('change', this.handleScreenOrientation);
        } else {
            window.addEventListener('orientationchange', this.handleScreenOrientation);
        }
    }

    /**
     * Remove screen orientation listener
     */
    detachScreenOrientationListener() {
        if (!this.handleScreenOrientation) return;

        if (screen.orientation) {
            screen.orientation.removeEventListener('change', this.handleScreenOrientation);
        } else {
            window.removeEventListener('orientationchange', this.handleScreenOrientation);
        }

        this.handleScreenOrientation = null;
    }

    // ==========================================
    // Compass Calculation
    // ==========================================

    /**
     * Calculate true compass heading from device orientation
     * @param {DeviceOrientationEvent} event
     * @returns {number|null} - Heading in degrees (0-360) or null
     */
    calculateCompassHeading(event) {
        const { absolute, alpha, beta, gamma, webkitCompassHeading } = event;

        // iOS: Use webkitCompassHeading (most accurate, already compensated)
        if (this.isIOS && webkitCompassHeading !== undefined) {
            this.calibrationQuality = 1.0; // iOS compass is always well calibrated
            return webkitCompassHeading;
        }

        // Android/Others: Calculate from alpha, beta, gamma
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
     * Compensate compass heading for device tilt
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

    // ==========================================
    // GPS Fallback
    // ==========================================

    /**
     * Update GPS data for fallback calculation
     * @param {number} lat - Current latitude
     * @param {number} lng - Current longitude
     * @param {number} speed - Current speed (m/s)
     * @param {number} accuracy - GPS accuracy (meters)
     */
    updateGPSData(lat, lng, speed, accuracy) {
        this.gpsData = {
            speed: speed || 0,
            accuracy: accuracy || Infinity,
            lastLat: lat,
            lastLng: lng,
        };
    }

    /**
     * Calculate GPS bearing from movement
     * @param {number} currentLat
     * @param {number} currentLng
     * @returns {number|null} - Bearing or null
     */
    calculateGPSBearing(currentLat, currentLng) {
        if (!this.gpsData.lastLat || !this.gpsData.lastLng) {
            return null;
        }

        // Use existing calculateBearing method
        return this.calculateBearing(
            this.gpsData.lastLat,
            this.gpsData.lastLng,
            currentLat,
            currentLng
        );
    }

    /**
     * Decide whether to use GPS bearing instead of compass
     * @returns {boolean}
     */
    shouldUseGPSBearing() {
        if (!LeafletConfig.compass.fallbackToGPS) {
            return false;
        }

        // Use GPS if:
        // 1. Calibration quality too low
        // 2. Moving fast enough
        // 3. GPS accuracy good enough
        const lowCalibration = this.calibrationQuality < LeafletConfig.compass.minCalibrationQuality;
        const movingFast = this.gpsData.speed >= LeafletConfig.compass.gpsMinSpeed;
        const goodAccuracy = this.gpsData.accuracy <= LeafletConfig.compass.gpsMinAccuracy;

        return lowCalibration && movingFast && goodAccuracy;
    }

    // ==========================================
    // Start/Stop Tracking
    // ==========================================

    /**
     * Start compass tracking
     */
    async start() {
        if (!this.isDeviceOrientationSupported()) {
            const error = new Error('DeviceOrientation not supported');
            if (this.onError) this.onError(error);
            return;
        }

        if (this.compassEnabled) {
            return; // Already tracking
        }

        // Request permission (iOS 13+)
        if (this.requiresPermission()) {
            try {
                const permission = await DeviceOrientationEvent.requestPermission();
                if (permission !== 'granted') {
                    const error = new Error('Permission denied for DeviceOrientation');
                    if (this.onError) this.onError(error);
                    return;
                }
            } catch (error) {
                if (this.onError) this.onError(error);
                return;
            }
        }

        // Attach listeners
        this.attachOrientationListener();
        this.attachScreenOrientationListener();
    }

    /**
     * Attach device orientation listener
     */
    attachOrientationListener() {
        this.handleOrientation = this.onOrientationChange.bind(this);

        // Try absolute first (has magnetometer)
        window.addEventListener('deviceorientationabsolute', this.handleOrientation, true);

        // Fallback to regular
        window.addEventListener('deviceorientation', this.handleOrientation, true);

        this.compassEnabled = true;
    }

    /**
     * Handle device orientation event
     * @param {DeviceOrientationEvent} event
     */
    onOrientationChange(event) {
        // Throttle updates
        const now = Date.now();
        if (now - this.lastCompassUpdate < this.compassThrottle) {
            return;
        }
        this.lastCompassUpdate = now;

        // Debug mode - log raw values
        if (LeafletConfig.compass.debug) {
            console.log('🧭 Compass Debug:', {
                platform: this.platform,
                absolute: event.absolute,
                alpha: event.alpha,
                beta: event.beta,
                gamma: event.gamma,
                webkitCompassHeading: event.webkitCompassHeading,
                webkitCompassAccuracy: event.webkitCompassAccuracy,
            });
        }

        // Calculate compass heading
        let heading = this.calculateCompassHeading(event);

        // Debug calculated heading
        if (LeafletConfig.compass.debug && heading !== null) {
            console.log('📍 Calculated Heading:', heading.toFixed(2) + '°',
                       'Quality:', (this.calibrationQuality * 100).toFixed(0) + '%');
        }

        // Fallback to GPS bearing if needed
        if (heading === null || this.shouldUseGPSBearing()) {
            if (this.gpsData.lastLat && this.gpsData.lastLng) {
                // Use last known GPS bearing (would need to be calculated externally)
                // For now, we just skip if no compass heading available
                return;
            }
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
     * Stop compass tracking
     */
    stop() {
        if (!this.compassEnabled) {
            return;
        }

        // Remove listeners
        if (this.handleOrientation) {
            window.removeEventListener('deviceorientationabsolute', this.handleOrientation, true);
            window.removeEventListener('deviceorientation', this.handleOrientation, true);
            this.handleOrientation = null;
        }

        this.detachScreenOrientationListener();

        // Cancel animation
        if (this.rotationAnimationFrame) {
            cancelAnimationFrame(this.rotationAnimationFrame);
            this.rotationAnimationFrame = null;
        }

        this.compassEnabled = false;
        this.compassHeading = null;
        this.calibrationQuality = 0;
    }

    // ==========================================
    // Map Rotation
    // ==========================================

    /**
     * Rotate map to bearing (instant)
     * @param {number} bearing - Bearing in degrees (0-360)
     */
    rotateTo(bearing) {
        if (!this.map) return;

        this.currentBearing = bearing;

        if (this.map.setBearing) {
            this.map.setBearing(bearing); // No negative - direct bearing
        }

        // Trigger callback
        if (this.onRotationUpdate) {
            this.onRotationUpdate(bearing);
        }
    }

    /**
     * Smooth rotate to bearing (animated)
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
                    this.map.setBearing(this.currentBearing); // No negative
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
                    this.map.setBearing(this.currentBearing); // No negative
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
     * Reset map rotation to north (0 degrees)
     */
    reset() {
        this.rotateTo(0);
    }

    // ==========================================
    // Utility Methods
    // ==========================================

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

    // ==========================================
    // Getters
    // ==========================================

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
     * Get calibration quality
     * @returns {number} - Quality (0-1)
     */
    getCalibrationQuality() {
        return this.calibrationQuality;
    }

    /**
     * Get platform
     * @returns {string} - 'ios', 'android', or 'desktop'
     */
    getPlatform() {
        return this.platform;
    }

    /**
     * Check if compass is active
     * @returns {boolean}
     */
    isActive() {
        return this.compassEnabled;
    }

    // ==========================================
    // Cleanup
    // ==========================================

    /**
     * Destroy tracker and cleanup
     */
    destroy() {
        this.stop();
        this.map = null;
        this.onRotationUpdate = null;
        this.onError = null;
    }
}
