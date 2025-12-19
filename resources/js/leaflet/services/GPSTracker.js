import { LeafletConfig } from '../config.js';

/**
 * GPS Tracker Service
 * Handles GPS location tracking (KISS principle)
 */
export class GPSTracker {
    constructor() {
        this.watchId = null;
        this.isTracking = false;
    }

    /**
     * Start GPS tracking
     * @param {function} onSuccess - Callback on position update (lat, lng, accuracy, speed)
     * @param {function} onError - Callback on error
     */
    start(onSuccess, onError) {
        if (!navigator.geolocation) {
            console.error('Geolocation not supported');
            if (onError) onError(new Error('Geolocation not supported'));
            return;
        }

        if (this.isTracking) {
            return; // Already tracking
        }

        this.watchId = navigator.geolocation.watchPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;
                // Speed in m/s (null if not available)
                const speedMs = position.coords.speed;
                // Convert to km/h (null if speed not available)
                const speedKmh = speedMs !== null ? speedMs * 3.6 : null;

                if (onSuccess) {
                    onSuccess(lat, lng, accuracy, speedKmh);
                }
            },
            (error) => {
                // Ignore timeout errors (they're too noisy)
                if (error.code !== error.TIMEOUT && onError) {
                    onError(error);
                }
            },
            LeafletConfig.gpsOptions
        );

        this.isTracking = true;
    }

    /**
     * Stop GPS tracking
     */
    stop() {
        if (this.watchId !== null) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
            this.isTracking = false;
        }
    }

    /**
     * Check if GPS is currently tracking
     * @returns {boolean}
     */
    isActive() {
        return this.isTracking;
    }

    /**
     * Destroy tracker and cleanup
     */
    destroy() {
        this.stop();
    }
}
