<div>
    {{-- HEADER --}}
    <x-header title="Lacak Kurir" icon="o-map-pin"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8"
        subtitle="Pantau lokasi dan aktivitas kurir secara real-time" separator progress-indicator>
        <x-slot:actions>
            {{-- Additional actions can be added here in the future --}}
        </x-slot:actions>
    </x-header>

    {{-- MAIN CONTENT --}}
    <x-card class="shadow-sm" body-class="relative w-full h-[78dvh] rounded-lg overflow-hidden">
        {{-- Map Element --}}
        <div id="map-tracking" wire:ignore class="w-full h-full z-0"></div>

        {{-- Zoom Controls - KIRI ATAS --}}
        <div class="absolute top-4 left-4 z-1">
            <livewire:components.maps-controller :map-id="'map-tracking'" position="top-left" :show-zoom="true"
                :show-layers="false" :show-center="false" :show-compass="false" />
        </div>

        {{-- Info Badge - TENGAH ATAS --}}
        <div class="absolute top-4 left-1/2 -translate-x-1/2 z-1" wire:poll.visible="updateActiveKurir">
            <x-badge value="{{ $activeKurirCount }} Kurir Aktif" class="badge-primary badge-md" />
        </div>

        {{-- Layer Controls - KANAN ATAS --}}
        <div class="absolute top-4 right-4 z-1">
            <livewire:components.maps-controller :map-id="'map-tracking'" position="top-right" :show-zoom="false"
                :show-layers="true" :show-center="false" :show-compass="false" :current-layer="'googleTraffic'" />
        </div>
    </x-card>
</div>

@script
<script>
    let mapManager = null;
    let kurirMarkers = {}; // Track existing kurir markers

    function initTrackingMap() {
        const mapElement = document.getElementById('map-tracking');
        if (!mapElement || mapManager) return;

        // Wait for Maps to be available
        if (typeof window.Maps === 'undefined') {
            setTimeout(initTrackingMap, 100);
            return;
        }

        const zoom = window.Maps.getZoomLevels();
        const defaultCoords = window.Maps.getDefaultCoordinates();

        // Create map using Leaflet
        mapManager = window.Maps.createMap('map-tracking', {
            latitude: defaultCoords.latitude,
            longitude: defaultCoords.longitude,
            zoom: zoom.city,
            defaultTileLayer: 'googleTraffic', // Google Traffic as default
            rotate: false,
            enableCompass: false,
            smoothCompass: false,
            showLayerControl: false, // Disabled - using MapsController component
            showZoomControl: false,  // Disabled - using MapsController component
        });

        if (!mapManager) {
            console.error('Failed to create map manager');
            return;
        }

        // Initialize the map
        mapManager.init();

        // Start polling for kurir locations
        startKurirTracking();

        console.log('Tracking map initialized');
    }

    function startKurirTracking() {
        // Listen to Livewire event for kurir location updates
        Livewire.on('kurir-locations-updated', (event) => {
            updateKurirLocations(event.activeKurir);
        });
    }

    function updateKurirLocations(activeKurir) {
        try {
            if (!mapManager || !activeKurir) return;

            // Track which kurir IDs are currently active
            const activeKurirIds = new Set();

            // Update or add markers for each active kurir
            activeKurir.forEach(kurir => {
                const kurirId = `kurir_${kurir.kurir_id}`;
                activeKurirIds.add(kurirId);

                const lat = parseFloat(kurir.latitude);
                const lng = parseFloat(kurir.longitude);

                if (isNaN(lat) || isNaN(lng)) return;

                // Determine icon based on jenis_kendaraan
                let iconType = 'motor'; // default
                if (kurir.jenis_kendaraan === 'Mobil') {
                    iconType = 'mobil';
                }

                // Get bearing for rotation (default 0 if null)
                const bearing = kurir.bearing !== null && kurir.bearing !== undefined
                    ? parseFloat(kurir.bearing)
                    : 0;

                // Check if marker already exists
                if (kurirMarkers[kurirId]) {
                    // Update existing marker position and rotation
                    mapManager.updateMarker(kurirId, lat, lng);
                    mapManager.rotateMarker(kurirId, bearing);
                    mapManager.updateMarkerPopup(kurirId, createKurirPopup(kurir));
                } else {
                    // Add new marker with rotation support
                    kurirMarkers[kurirId] = true;
                    mapManager.addMarker(kurirId, lat, lng, {
                        icon: window.Maps.createIcon(iconType),
                        popup: createKurirPopup(kurir),
                    });
                    // Set initial rotation
                    mapManager.rotateMarker(kurirId, bearing);
                }
            });

            // Remove markers for kurir that are no longer active
            Object.keys(kurirMarkers).forEach(kurirId => {
                if (!activeKurirIds.has(kurirId)) {
                    mapManager.removeMarker(kurirId);
                    delete kurirMarkers[kurirId];
                }
            });

            // Don't auto fit bounds - keep map at default coordinates (Kendari)
            // User can manually zoom/pan to see couriers

        } catch (error) {
            console.error('Error updating kurir locations:', error);
        }
    }

    function createKurirPopup(kurir) {
        // Speed: show 0.0 if null/undefined/0, otherwise show actual speed
        const speedValue = kurir.speed !== null && kurir.speed !== undefined
            ? parseFloat(kurir.speed)
            : 0.0;
        const speedText = `${speedValue.toFixed(1)} km/h`;

        // Truncate alamat jika terlalu panjang
        const maxAlamatLength = 30;
        const alamatTruncated = kurir.pelanggan_alamat && kurir.pelanggan_alamat.length > maxAlamatLength
            ? kurir.pelanggan_alamat.substring(0, maxAlamatLength) + '...'
            : kurir.pelanggan_alamat || '-';

        const pelangganNama = kurir.pelanggan_nama || '-';

        return `
            <div class="p-2">
                <div class="mb-1.5 space-y-0.5">
                    <div class="text-[10px]">
                        <strong class="font-extrabold">Kurir:</strong> <span class="text-xs">${kurir.nama}</span>
                    </div>
                    <div class="text-[10px]">
                        <strong class="font-extrabold">Kecepatan:</strong> <span class="text-xs">${speedText}</span>
                    </div>
                    <div class="text-[10px]">
                        <strong class="font-extrabold">Pelanggan:</strong> <span class="text-xs">${pelangganNama}</span>
                    </div>
                    <div class="text-[10px]">
                        <strong class="font-extrabold">Alamat:</strong> <span class="text-[9px]">${alamatTruncated}</span>
                    </div>
                </div>
                <x-button label="Detail" link="/management/tracking/${kurir.kurir_id}" class="btn-primary btn-xs btn-block" />
            </div>
        `;
    }

    // Initialize map after DOM ready
    setTimeout(() => {
        initTrackingMap();
    }, 500);

    // Cleanup on navigation
    document.addEventListener('livewire:navigating', () => {
        if (mapManager) {
            mapManager.destroy();
            mapManager = null;
            kurirMarkers = {};
        }
    });
</script>
@endscript
