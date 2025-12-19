<div>
    @if ($pelangganLatitude && $pelangganLongitude)
        <div class="h-dvh fixed inset-0 mt-16">
            <div id="map-rute-detail" wire:ignore class="w-full h-full z-40"></div>

            {{-- Zoom Controls - KIRI ATAS --}}
            <div class="fixed top-20 left-4 z-48">
                <livewire:components.maps-controller
                    :map-id="'map-rute-detail'"
                    position="top-left"
                    :show-zoom="true"
                    :show-layers="false"
                    :show-center="false"
                    :show-compass="false"
                />
            </div>

            {{-- Speed Indicator - TENGAH ATAS --}}
            <div class="fixed top-20 left-1/2 -translate-x-1/2 z-48">
                <div class="bg-base-100 rounded-lg shadow-lg px-4 py-2">
                    <div class="flex items-center gap-2">
                        <div class="text-lg font-mono font-semibold" wire:poll.visible>
                            @if ($kurirSpeed !== null)
                                {{ number_format($kurirSpeed, 1) }}
                            @else
                                0.0
                            @endif
                            <span class="text-sm text-base-content/60">km/h</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Other Controls - KANAN ATAS --}}
            <div class="fixed top-20 right-4 z-48">
                <livewire:components.maps-controller
                    :map-id="'map-rute-detail'"
                    position="top-right"
                    :show-zoom="false"
                    :show-layers="true"
                    :show-center="true"
                    :show-compass="true"
                    :current-layer="'googleTraffic'"
                />
            </div>
        </div>
    @else
        <div class="flex items-center justify-center h-screen bg-base-200">
            <div class="text-center space-y-3 p-4">
                <div class="w-16 h-16 rounded-full bg-base-300 flex items-center justify-center mx-auto">
                    <x-icon name="iconpark.localpin-o" class="h-8 text-base-content/40" />
                </div>
                <div class="space-y-1">
                    <p class="text-base-content/60 font-medium">Lokasi tidak tersedia</p>
                    <p class="text-xs text-base-content/40">Koordinat pelanggan belum diset</p>
                </div>
            </div>
        </div>
    @endif
</div>

@if ($pelangganLatitude && $pelangganLongitude)
    @script
        <script>
            let mapManager = null;
            const pelangganLat = parseFloat(@js($pelangganLatitude));
            const pelangganLng = parseFloat(@js($pelangganLongitude));
            const pelangganNama = @js($pelangganNama);
            const pelangganAlamat = @js($pelangganAlamat);
            const jenisKendaraan = @js($jenisKendaraan);

            function initRuteMap() {
                const mapElement = document.getElementById('map-rute-detail');
                if (!mapElement || mapManager) return;

                if (isNaN(pelangganLat) || isNaN(pelangganLng)) {
                    console.error('Invalid coordinates:', pelangganLat, pelangganLng);
                    return;
                }

                // Wait for Maps to be available
                if (typeof window.Maps === 'undefined') {
                    setTimeout(initRuteMap, 100);
                    return;
                }

                const zoom = window.Maps.getZoomLevels();
                const kurirLat = parseFloat($wire.kurirLatitude) || pelangganLat;
                const kurirLng = parseFloat($wire.kurirLongitude) || pelangganLng;

                // Create map using Leaflet
                mapManager = window.Maps.createMap('map-rute-detail', {
                    latitude: pelangganLat,
                    longitude: pelangganLng,
                    zoom: zoom.city,
                    defaultTileLayer: 'googleTraffic', // Google Traffic for routing
                    rotate: true,
                    enableCompass: true,
                    smoothCompass: true,
                    showLayerControl: false, // Disabled - using MapsController component
                    showZoomControl: false, // Disabled - using MapsController component
                });

                if (!mapManager) {
                    return;
                }

                mapManager.init();

                // Add pelanggan marker (home icon)
                mapManager.addMarker('pelanggan', pelangganLat, pelangganLng, {
                    icon: window.Maps.createIcon('home'),
                    popup: createPelangganPopup(pelangganLat, pelangganLng),
                });

                // Determine vehicle icon type based on jenis_kendaraan
                let vehicleIconType = 'motor'; // default to motor
                if (jenisKendaraan === 'Mobil') {
                    vehicleIconType = 'mobil';
                }

                // Add kurir marker (vehicle icon based on jenis_kendaraan)
                mapManager.addMarker('kurir', kurirLat, kurirLng, {
                    icon: window.Maps.createIcon(vehicleIconType),
                    popup: createKurirPopup(kurirLat, kurirLng),
                });

                // Fit bounds to show both markers
                if (kurirLat !== pelangganLat || kurirLng !== pelangganLng) {
                    mapManager.fitBounds();
                    // Initialize routing
                    mapManager.initRouting(kurirLat, kurirLng, pelangganLat, pelangganLng);
                } else {
                    const zoom = window.Maps.getZoomLevels();
                    mapManager.setView(kurirLat, kurirLng, zoom.detail);
                }

                // Start compass tracking
                mapManager.startCompassTracking();

                // Start GPS tracking with compass integration
                $wire.call('updateGpsStatus', 'searching');

                mapManager.startGPSTracking(
                    (lat, lng, accuracy, speedKmh) => {
                        // Store position for bearing calculation fallback
                        mapManager.lastPosition = { lat, lng };

                        // Update GPS data to CompassTracker for fallback (if compass quality is poor)
                        if (mapManager.compassTracker) {
                            // Pass speed to compass tracker (if available)
                            const speedMs = speedKmh !== null ? speedKmh / 3.6 : 0;
                            mapManager.compassTracker.updateGPSData(lat, lng, speedMs, accuracy);
                        }

                        // Get current heading for display (compass handled separately)
                        const displayHeading = mapManager.compassHeading || mapManager.currentBearing;

                        // Update Livewire with speed
                        $wire.call('updateKurirLocation', lat, lng, accuracy, speedKmh);

                        // Update kurir marker position
                        mapManager.updateMarker('kurir', lat, lng);
                        mapManager.updateMarkerPopup('kurir', createKurirPopup(lat, lng, accuracy, displayHeading, speedKmh));

                        // Update routing
                        mapManager.updateRouting(lat, lng, pelangganLat, pelangganLng);
                    },
                    (error) => {
                        console.error('GPS Error:', error.message);
                    }
                );
            }


            function createPelangganPopup(lat, lng) {
                return `
                    <div class="p-2">
                        <strong class="text-base">Tujuan</strong>
                        <div class="text-sm mt-1">${pelangganNama}</div>
                        <div class="text-xs text-secondary mt-1">${pelangganAlamat}</div>
                    </div>
                `;
            }

            function createKurirPopup(lat, lng, accuracy = null, bearing = null, speedKmh = null) {
                const accuracyText = accuracy ? `<div class="text-xs text-secondary mt-1">Akurasi: ±${accuracy.toFixed(1)}m</div>` : '';
                const bearingText = bearing !== null ? `<div class="text-xs text-secondary mt-1">Arah: ${Math.round(bearing)}°</div>` : '';
                const speedText = speedKmh !== null ? `<div class="text-xs text-secondary mt-1">Kecepatan: ${speedKmh.toFixed(1)} km/h</div>` : '';

                return `
                    <div class="p-2">
                        <strong class="text-base">Lokasi Anda</strong>
                        <div class="text-sm mt-1 font-mono">${lat.toFixed(6)}, ${lng.toFixed(6)}</div>
                        ${accuracyText}
                        ${bearingText}
                        ${speedText}
                    </div>
                `;
            }

            // Initialize map after DOM ready
            setTimeout(() => {
                initRuteMap();
            }, 500);

            // Cleanup on navigation
            document.addEventListener('livewire:navigating', () => {
                if (mapManager) {
                    mapManager.destroy();
                    mapManager = null;
                }
            });
        </script>
    @endscript
@endif
