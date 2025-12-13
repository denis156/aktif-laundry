<div>
    <x-card class="shadow-lg border border-primary" body-class="overflow-hidden p-0">
        {{-- Header dengan Status Real-time --}}
        <x-slot:title>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <x-icon name="iconpark.localpin-o" class="h-5 w-5 text-primary" />
                    <span>Lokasi Anda</span>
                </div>
                <div wire:stream="gps-status">
                    {!! $this->getGpsStatusBadge() !!}
                </div>
            </div>
        </x-slot:title>

        <x-slot:subtitle>
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-base-content/60">Koordinat:</span>
                    <span wire:stream="coordinates">
                        {!! $this->formatCoordinates() !!}
                    </span>
                </div>
                <div class="flex items-center gap-3 text-xs flex-wrap">
                    <div class="flex items-center gap-1">
                        <span class="text-base-content/60">Akurasi:</span>
                        <span wire:stream="accuracy-info">
                            {!! $this->formatAccuracy() !!}
                        </span>
                    </div>
                </div>
            </div>
        </x-slot:subtitle>

        {{-- Map Container - Always render for JS to init --}}
        <div id="map-lokasi-kurir" wire:ignore class="w-full h-[42dvh] rounded z-40"></div>
    </x-card>
</div>

@script
    <script>
        let mapManager = null;

        function initLokasiMap() {
            const mapElement = document.getElementById('map-lokasi-kurir');
            if (!mapElement || mapManager) return;

            // Wait for Maps to be available
            if (typeof window.Maps === 'undefined') {
                setTimeout(initLokasiMap, 100);
                return;
            }

            const defaults = window.Maps.getDefaultCoordinates();
            const zoom = window.Maps.getZoomLevels();
            const lat = parseFloat($wire.latitude) || defaults.latitude;
            const lng = parseFloat($wire.longitude) || defaults.longitude;

            // Create map using unified entry point
            mapManager = window.Maps.createMap('map-lokasi-kurir', {
                latitude: lat,
                longitude: lng,
                zoom: zoom.detail,
                rotate: true,
                showLayerControl: true, // Enable layer control
            });

            mapManager.init();

            // Add marker
            const marker = mapManager.addMarker('kurir-location', lat, lng, {
                popup: createPopupContent(lat, lng),
            });

            // Add accuracy circle
            mapManager.updateAccuracyCircle(lat, lng, 50);

            // Start GPS tracking
            $wire.call('updateGpsStatus', 'searching');

            mapManager.startGPSTracking(
                (newLat, newLng, accuracy) => {
                    // Update Livewire
                    $wire.call('updateLocation', newLat, newLng, accuracy);

                    // Update marker and accuracy circle
                    mapManager.updateMarker('kurir-location', newLat, newLng);
                    mapManager.updateMarkerPopup('kurir-location', createPopupContent(newLat, newLng, accuracy));
                    mapManager.updateAccuracyCircle(newLat, newLng, accuracy);

                    // Pan to location
                    mapManager.panTo(newLat, newLng);
                },
                (error) => {
                    console.error('GPS Error:', error.message);
                    $wire.call('updateGpsStatus', 'error');
                }
            );
        }

        function createPopupContent(lat, lng, accuracy = null) {
            const accuracyText = accuracy
                ? `<div class="text-xs text-secondary mt-1">Akurasi: ±${accuracy.toFixed(1)}m</div>`
                : '';

            return `
                <div class="p-2">
                    <div class="flex items-center gap-2 mb-1">
                        <strong class="text-base">Lokasi Anda</strong>
                    </div>
                    <div class="text-xs text-secondary font-mono">
                        ${lat.toFixed(6)}, ${lng.toFixed(6)}
                    </div>
                    ${accuracyText}
                </div>
            `;
        }

        // Initialize map after DOM ready
        setTimeout(() => {
            initLokasiMap();
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
