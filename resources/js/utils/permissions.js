/**
 * Browser Permissions Utility
 * Handles notification and location permission checking and management
 */

// Check if notification permission is granted
function hasNotificationPermission() {
    if (!('Notification' in window)) {
        return false;
    }
    return Notification.permission === 'granted';
}

// Check if location permission is granted
async function hasLocationPermission() {
    if (!('geolocation' in navigator)) {
        return false;
    }

    // Try using Permissions API if available
    if ('permissions' in navigator) {
        try {
            const result = await navigator.permissions.query({ name: 'geolocation' });
            return result.state === 'granted';
        } catch (error) {
            // Permissions API not supported, fallback to false
            return false;
        }
    }

    return false;
}

// Request notification permission
async function requestNotificationPermission() {
    if (!('Notification' in window)) {
        return { granted: false, error: 'Browser tidak mendukung notifikasi' };
    }

    if (Notification.permission === 'granted') {
        return { granted: true };
    }

    if (Notification.permission === 'denied') {
        return { granted: false, error: 'Izin notifikasi diblokir. Aktifkan di pengaturan browser Anda.' };
    }

    try {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            return { granted: true };
        } else {
            return { granted: false, error: 'Izin notifikasi ditolak. Aktifkan di pengaturan browser Anda.' };
        }
    } catch (error) {
        return { granted: false, error: 'Gagal meminta izin notifikasi' };
    }
}

// Request location permission
async function requestLocationPermission() {
    if (!('geolocation' in navigator)) {
        return { granted: false, error: 'Browser tidak mendukung lokasi' };
    }

    return new Promise((resolve) => {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                console.log('Lokasi diizinkan:', position.coords.latitude, position.coords.longitude);
                resolve({ granted: true, position });
            },
            (error) => {
                if (error.code === error.PERMISSION_DENIED) {
                    resolve({ granted: false, error: 'Izin lokasi ditolak. Aktifkan di pengaturan browser Anda.' });
                } else {
                    resolve({ granted: false, error: 'Gagal mendapatkan lokasi: ' + error.message });
                }
            }
        );
    });
}

// Show notification
function showNotification(title, body, icon) {
    if (hasNotificationPermission()) {
        new Notification(title, { body, icon });
    }
}

// Setup Alpine.js Store for permissions
export function setupAlpineStore() {
    document.addEventListener('alpine:init', async () => {
        const notificationGranted = hasNotificationPermission();
        const locationGranted = await hasLocationPermission();

        Alpine.store('permissions', {
            notification: notificationGranted,
            location: locationGranted,

            async checkNotification() {
                this.notification = hasNotificationPermission();
                return this.notification;
            },

            async checkLocation() {
                this.location = await hasLocationPermission();
                return this.location;
            },

            async requestNotification() {
                const result = await requestNotificationPermission();
                this.notification = result.granted;
                return result;
            },

            async requestLocation() {
                const result = await requestLocationPermission();
                this.location = result.granted;
                return result;
            },

            async toggleNotification(iconUrl) {
                if (!this.notification) {
                    // User wants to disable - just turn off silently
                    return;
                }

                // User wants to enable - request permission
                const result = await this.requestNotification();

                if (result.granted) {
                    // Show test notification
                    showNotification(
                        'Notifikasi Aktif!',
                        'Anda akan menerima notifikasi untuk pesanan laundry Anda',
                        iconUrl
                    );
                } else {
                    // Permission denied - show error and revert toggle
                    alert(result.error || 'Izin notifikasi ditolak');
                    this.notification = false;
                }
            },

            async toggleLocation() {
                if (!this.location) {
                    // User wants to disable - just turn off silently
                    return;
                }

                // User wants to enable - request permission
                const result = await this.requestLocation();

                if (!result.granted) {
                    // Permission denied - show error and revert toggle
                    alert(result.error || 'Izin lokasi ditolak');
                    this.location = false;
                }
            },

            async init() {
                this.notification = hasNotificationPermission();
                this.location = await hasLocationPermission();
            }
        });

        // Watch for permission changes (if supported)
        if ('permissions' in navigator) {
            try {
                const locationPermission = await navigator.permissions.query({ name: 'geolocation' });
                locationPermission.addEventListener('change', () => {
                    Alpine.store('permissions').location = locationPermission.state === 'granted';
                });
            } catch (error) {
                console.log('Permission change listener not supported');
            }
        }
    });
}

// Setup Livewire navigation listener
export function setupLivewireListener() {
    document.addEventListener('livewire:navigated', async () => {
        if (window.Alpine && Alpine.store('permissions')) {
            await Alpine.store('permissions').init();
        }
    });
}

// Initialize everything
export function initPermissions() {
    setupAlpineStore();
    setupLivewireListener();
}
