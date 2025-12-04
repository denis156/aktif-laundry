// ==========================================
// PWA INSTALL HANDLER
// ==========================================
// Menangani install PWA prompt

let deferredPrompt = null;

/**
 * Setup PWA install prompt handler
 * Capture beforeinstallprompt event dan simpan untuk digunakan nanti
 */
export function setupPWAInstall() {
    console.log('[PWA] Setting up install handler...');

    window.addEventListener('beforeinstallprompt', (e) => {
        // PENTING: Prevent default mini-infobar/banner dari muncul
        e.preventDefault();

        // Simpan event untuk trigger nanti
        deferredPrompt = e;

        // Expose ke window untuk akses global
        window.deferredPrompt = e;

        console.log('[PWA] beforeinstallprompt event captured and prevented');
    });

    // Listen untuk event setelah app ter-install
    window.addEventListener('appinstalled', () => {
        console.log('[PWA] App installed successfully');
        deferredPrompt = null;
        window.deferredPrompt = null;
    });

    console.log('[PWA] Install handler setup completed');
}

/**
 * Trigger PWA install prompt
 * Function ini akan dipanggil dari browser saat user klik install button
 * @returns {Promise<string>} - 'accepted', 'dismissed', atau 'unavailable'
 */
export async function promptPWAInstall() {
    // Update dari global jika ada
    if (!deferredPrompt && window.deferredPrompt) {
        deferredPrompt = window.deferredPrompt;
    }

    if (!deferredPrompt) {
        console.warn('[PWA] Install prompt not available');
        return 'unavailable';
    }

    try {
        // Show install prompt
        console.log('[PWA] Showing install prompt...');
        deferredPrompt.prompt();

        // Wait for user response
        const { outcome } = await deferredPrompt.userChoice;

        console.log('[PWA] User choice:', outcome);

        // Reset deferred prompt
        deferredPrompt = null;
        window.deferredPrompt = null;

        return outcome;
    } catch (error) {
        console.error('[PWA] Failed to show install prompt:', error);
        return 'error';
    }
}

/**
 * Check apakah PWA sudah ter-install atau sedang berjalan dalam standalone mode
 * @returns {boolean}
 */
export function isPWAInstalled() {
    // Check jika app berjalan dalam standalone mode
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches;

    // Check iOS standalone mode
    const isIOSStandalone = ('standalone' in window.navigator) && window.navigator.standalone;

    return isStandalone || isIOSStandalone;
}

/**
 * Check apakah PWA bisa di-install (deferred prompt tersedia)
 * @returns {boolean}
 */
export function canInstallPWA() {
    return deferredPrompt !== null || window.deferredPrompt !== null;
}

// Expose functions ke window object agar bisa dipanggil dari anywhere
window.pwaInstall = {
    prompt: promptPWAInstall,
    isInstalled: isPWAInstalled,
    canInstall: canInstallPWA,
    setup: setupPWAInstall
};
