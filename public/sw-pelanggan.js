// Service Worker untuk Aplikasi Pelanggan - Aktif Laundry
// Cache Strategy: Assets only (CSS, JS, images) - Data tetap online

const CACHE_NAME = 'aktif-laundry-pelanggan'; 
const PELANGGAN_SCOPE = '/pelanggan/';

// Install Event - Skip waiting untuk activate immediately
self.addEventListener('install', (event) => {
    console.log('[SW Pelanggan] Installing service worker...');
    // Skip pre-caching, biarkan cache on-demand saat assets di-request
    event.waitUntil(self.skipWaiting());
});

// Activate Event - Cleanup old caches
self.addEventListener('activate', (event) => {
    console.log('[SW Pelanggan] Activating service worker...');

    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((cacheName) => {
                            // Hapus cache lama yang bukan cache saat ini
                            return cacheName.startsWith('aktif-laundry-pelanggan-') &&
                                   cacheName !== CACHE_NAME;
                        })
                        .map((cacheName) => {
                            console.log('[SW Pelanggan] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        })
                );
            })
            .then(() => {
                console.log('[SW Pelanggan] Service worker activated');
                return self.clients.claim(); // Take control immediately
            })
    );
});

// Fetch Event - Cache strategy untuk assets
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip request dari chrome extension dan non-http(s) schemes
    if (!url.protocol.startsWith('http')) {
        return; // Ignore chrome-extension://, data:, blob:, etc.
    }

    // Hanya handle request untuk scope pelanggan
    if (!url.pathname.startsWith(PELANGGAN_SCOPE) && !isAsset(url.pathname)) {
        return; // Biarkan browser handle request lainnya
    }

    // Strategi untuk assets: Cache First, Network Fallback
    if (isAsset(url.pathname)) {
        event.respondWith(
            caches.match(request)
                .then((cachedResponse) => {
                    if (cachedResponse) {
                        console.log('[SW Pelanggan] Serving from cache:', url.pathname);
                        return cachedResponse;
                    }

                    // Jika tidak ada di cache, fetch dari network dan cache
                    return fetch(request)
                        .then((networkResponse) => {
                            // Clone response karena response hanya bisa digunakan sekali
                            const responseToCache = networkResponse.clone();

                            caches.open(CACHE_NAME)
                                .then((cache) => {
                                    cache.put(request, responseToCache);
                                    console.log('[SW Pelanggan] Cached new asset:', url.pathname);
                                });

                            return networkResponse;
                        })
                        .catch((error) => {
                            console.error('[SW Pelanggan] Fetch failed:', error);
                            throw error;
                        });
                })
        );
    } else {
        // Untuk halaman HTML dan data: Network Only (selalu online)
        event.respondWith(
            fetch(request)
                .catch((error) => {
                    console.error('[SW Pelanggan] Network request failed:', error);
                    throw error;
                })
        );
    }
});

// Helper function untuk detect apakah request adalah asset
function isAsset(pathname) {
    const assetExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot'];
    const isBuildAsset = pathname.startsWith('/build/');
    const hasAssetExtension = assetExtensions.some(ext => pathname.endsWith(ext));

    return isBuildAsset || hasAssetExtension;
}

// Message Event - Handle messages dari client (optional, untuk future features)
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        console.log('[SW Pelanggan] Received SKIP_WAITING message');
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        console.log('[SW Pelanggan] Clearing cache...');
        event.waitUntil(
            caches.delete(CACHE_NAME)
                .then(() => {
                    console.log('[SW Pelanggan] Cache cleared');
                })
        );
    }
});
