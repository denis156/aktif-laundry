<div>
    @if ($pelangganLatitude && $pelangganLongitude)
        <div class="h-dvh fixed inset-0 mt-16">
            <div id="map-kurir-position" wire:ignore class="w-full h-full z-40"></div>

            {{-- Zoom Controls - KIRI ATAS --}}
            <div class="fixed top-20 left-4 z-48">
                <livewire:components.maps-controller
                    :map-id="'map-kurir-position'"
                    position="top-left"
                    :show-zoom="true"
                    :show-layers="false"
                    :show-center="false"
                    :show-compass="false"
                />
            </div>

            {{-- Speed Indicator - TENGAH ATAS --}}
            <div class="fixed top-20 left-1/2 -translate-x-1/2 z-48" wire:poll.visible="updateTrackingData">
                <div class="bg-base-100 rounded-lg shadow-lg px-4 py-2">
                    <div class="flex items-center gap-2">
                        <div class="text-lg font-mono font-semibold">
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
                    :map-id="'map-kurir-position'"
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
                    <p class="text-base-content/60 font-medium">Kurir sedang tidak aktif</p>
                    <p class="text-xs text-base-content/40">Belum ada data tracking tersedia</p>
                </div>
            </div>
        </div>
    @endif
</div>

@if ($pelangganLatitude && $pelangganLongitude)
    @script
        <script>
            let mapManager = null;

            function initKurirPositionMap() {
                const mapElement = document.getElementById('map-kurir-position');
                if (!mapElement || mapManager) return;

                // Wait for Maps to be available
                if (typeof window.Maps === 'undefined') {
                    setTimeout(initKurirPositionMap, 100);
                    return;
                }

                const pelangganLat = parseFloat(@js($pelangganLatitude));
                const pelangganLng = parseFloat(@js($pelangganLongitude));
                const pelangganNama = @js($pelangganNama);
                const pelangganAlamat = @js($pelangganAlamat);
                const jenisKendaraan = @js($jenisKendaraan);

                if (isNaN(pelangganLat) || isNaN(pelangganLng)) {
                    console.error('Invalid coordinates:', pelangganLat, pelangganLng);
                    return;
                }

                const zoom = window.Maps.getZoomLevels();
                const kurirLat = parseFloat(@js($kurirLatitude)) || pelangganLat;
                const kurirLng = parseFloat(@js($kurirLongitude)) || pelangganLng;

                // Create map using Leaflet
                mapManager = window.Maps.createMap('map-kurir-position', {
                    latitude: pelangganLat,
                    longitude: pelangganLng,
                    zoom: zoom.city,
                    defaultTileLayer: 'googleTraffic',
                    rotate: false, // No rotation for pelanggan view
                    enableCompass: true,
                    smoothCompass: true,
                    showLayerControl: false,
                    showZoomControl: false,
                });

                if (!mapManager) {
                    console.error('Failed to create map manager');
                    return;
                }

                // Initialize the map
                mapManager.init();

                // Add pelanggan marker (home icon)
                mapManager.addMarker('pelanggan', pelangganLat, pelangganLng, {
                    icon: window.Maps.createIcon('home'),
                    popup: createPelangganPopup(pelangganNama, pelangganAlamat),
                });

                // Determine vehicle icon type
                let vehicleIconType = 'motor';
                if (jenisKendaraan === 'Mobil') {
                    vehicleIconType = 'mobil';
                }

                // Add kurir marker
                mapManager.addMarker('kurir', kurirLat, kurirLng, {
                    icon: window.Maps.createIcon(vehicleIconType),
                    popup: createKurirPopup(),
                });

                // Fit bounds to show both markers
                if (kurirLat !== pelangganLat || kurirLng !== pelangganLng) {
                    mapManager.fitBounds();
                    // Initialize routing
                    mapManager.initRouting(kurirLat, kurirLng, pelangganLat, pelangganLng);
                } else {
                    mapManager.setView(kurirLat, kurirLng, zoom.detail);
                }

                // Listen for tracking updates
                startTrackingUpdates();

                console.log('Kurir position map initialized');
            }

            function startTrackingUpdates() {
                // Listen to Livewire event for tracking data updates
                Livewire.on('tracking-data-updated', (event) => {
                    updateKurirPosition(event.trackingData);
                });
            }

            function updateKurirPosition(data) {
                if (!mapManager || !data) return;

                const lat = parseFloat(data.latitude);
                const lng = parseFloat(data.longitude);
                const bearing = data.bearing !== null && data.bearing !== undefined ? parseFloat(data.bearing) : 0;

                if (isNaN(lat) || isNaN(lng)) return;

                // Update kurir marker position and rotation
                mapManager.updateMarker('kurir', lat, lng);
                mapManager.rotateMarker('kurir', bearing);
                mapManager.updateMarkerPopup('kurir', createKurirPopup());

                // Update routing
                const pelangganLat = parseFloat(@js($pelangganLatitude));
                const pelangganLng = parseFloat(@js($pelangganLongitude));
                mapManager.updateRouting(lat, lng, pelangganLat, pelangganLng);
            }

            function createPelangganPopup(nama, alamat) {
                return `
                    <div class="p-2">
                        <strong class="text-base">Tujuan</strong>
                        <div class="text-sm mt-1">${nama}</div>
                        <div class="text-xs text-secondary mt-1">${alamat}</div>
                    </div>
                `;
            }

            function createKurirPopup() {
                const kurirNama = @js($kurirNama);
                const accuracy = parseFloat(@js($kurirAccuracy)) || null;
                const bearing = parseFloat(@js($kurirBearing)) || null;
                const lat = parseFloat(@js($kurirLatitude)) || 0;
                const lng = parseFloat(@js($kurirLongitude)) || 0;

                const accuracyText = accuracy ? `<div class="text-xs text-secondary mt-1">Akurasi: ±${accuracy.toFixed(1)}m</div>` : '';
                const bearingText = bearing !== null ? `<div class="text-xs text-secondary mt-1">Arah: ${Math.round(bearing)}°</div>` : '';

                return `
                    <div class="p-2">
                        <strong class="text-base">Lokasi Kurir</strong>
                        <div class="text-sm mt-1">${kurirNama}</div>
                        <div class="text-sm mt-1 font-mono">${lat.toFixed(6)}, ${lng.toFixed(6)}</div>
                        ${accuracyText}
                        ${bearingText}
                    </div>
                `;
            }

            // Initialize map after DOM ready
            setTimeout(() => {
                initKurirPositionMap();
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
