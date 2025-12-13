<div>
    @if ($pelangganLatitude && $pelangganLongitude)
        <div class="h-dvh fixed inset-0 mt-16 z-40">
            <div id="map-rute-detail" wire:ignore class="w-full h-full"></div>
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

            function initRuteMap() {
                const mapElement = document.getElementById('map-rute-detail');
                if (!mapElement || mapManager) return;

                if (isNaN(pelangganLat) || isNaN(pelangganLng)) {
                    console.error('Invalid coordinates:', pelangganLat, pelangganLng);
                    return;
                }

                // Wait for LeafletMapManager
                if (typeof window.LeafletMapManager === 'undefined') {
                    setTimeout(initRuteMap, 100);
                    return;
                }

                const kurirLat = parseFloat($wire.kurirLatitude) || pelangganLat;
                const kurirLng = parseFloat($wire.kurirLongitude) || pelangganLng;

                // Initialize map using OOP class
                mapManager = new window.LeafletMapManager('map-rute-detail', {
                    latitude: pelangganLat,
                    longitude: pelangganLng,
                    zoom: window.LeafletUtils.config.zoom.city,
                    defaultTileLayer: 'googleTraffic', // Use Google Traffic as default
                    rotate: true,
                    enableCompass: true,
                    smoothCompass: true, // Enable smooth compass rotation
                    showLayerControl: false,
                });

                mapManager.init();

                // Add pelanggan marker (home icon)
                mapManager.addMarker('pelanggan', pelangganLat, pelangganLng, {
                    icon: window.LeafletUtils.config.createHomeMarkerIcon(),
                    popup: `
                        <div class="p-2">
                            <strong class="text-base">${pelangganNama}</strong><br>
                            <small>${pelangganLat.toFixed(6)}, ${pelangganLng.toFixed(6)}</small><br>
                            <small>${pelangganAlamat}</small>
                        </div>
                    `,
                });

                // Add kurir marker (arrow icon)
                mapManager.addMarker('kurir', kurirLat, kurirLng, {
                    icon: window.LeafletUtils.config.createArrowMarkerIcon(),
                    popup: createKurirPopup(kurirLat, kurirLng),
                });

                // Fit bounds to show both markers
                if (kurirLat !== pelangganLat || kurirLng !== pelangganLng) {
                    mapManager.fitBounds();
                    // Initialize routing
                    mapManager.initRouting(kurirLat, kurirLng, pelangganLat, pelangganLng);
                } else {
                    mapManager.setView(kurirLat, kurirLng, window.LeafletUtils.config.zoom.detail);
                }

                // Start compass tracking
                mapManager.startCompassTracking();

                // Start GPS tracking with compass integration
                $wire.call('updateGpsStatus', 'searching');

                mapManager.startGPSTracking(
                    (lat, lng, accuracy) => {
                        // Store position for bearing calculation fallback
                        mapManager.lastPosition = { lat, lng };

                        // Get current heading for display (compass handled separately)
                        const displayHeading = mapManager.compassHeading || mapManager.currentBearing;

                        // Update Livewire
                        $wire.call('updateKurirLocation', lat, lng, accuracy);

                        // Update kurir marker position
                        mapManager.updateMarker('kurir', lat, lng);
                        mapManager.updateMarkerPopup('kurir', createKurirPopup(lat, lng, accuracy, displayHeading));

                        // Update routing
                        mapManager.updateRouting(lat, lng, pelangganLat, pelangganLng);
                    },
                    (error) => {
                        console.error('GPS Error:', error.message);
                    }
                );
            }


            function createKurirPopup(lat, lng, accuracy = null, bearing = null) {
                const accuracyText = accuracy ? `<small>Akurasi: ±${accuracy.toFixed(1)}m</small><br>` : '';
                const bearingText = bearing !== null ? `<small>Arah: ${Math.round(bearing)}°</small>` : '';

                return `
                    <div class="p-2">
                        <strong class="text-base">Lokasi Anda (Kurir)</strong><br>
                        <small>${lat.toFixed(6)}, ${lng.toFixed(6)}</small><br>
                        ${accuracyText}
                        ${bearingText}
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
