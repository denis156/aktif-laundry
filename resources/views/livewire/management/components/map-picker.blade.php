@php
    // Sanitize markerId for JavaScript variable names (replace dash with underscore)
    $jsMarkerId = str_replace('-', '_', $markerId);
@endphp

<div>
    {{-- Map Container with Search Overlay --}}
    <div class="relative">
        <div id="map-{{ $markerId }}" wire:ignore class="{{ $mapHeight }} z-0 rounded-md"></div>

        {{-- Search Box Overlay --}}
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 z-1 w-full max-w-2xl px-2">
            <x-choices label="" wire:model.live="selectedLocationId" :options="$locationSearchResults"
                search-function="searchLocation" option-label="name" option-sub-label="description"
                placeholder="Cari alamat atau nama tempat (contoh: Masjid, Grosir)" single searchable
                no-result-text="Tidak ada hasil. Ketik minimal 3 karakter." debounce="300ms" min-chars="3"
                class="select-xs md:select-sm" />
        </div>
    </div>

    @script
        <script>
            let mapManager_{{ $jsMarkerId }} = null;

            function initMap_{{ $jsMarkerId }}() {
                const mapElement = document.getElementById('map-{{ $markerId }}');
                if (!mapElement || mapElement.offsetWidth === 0 || mapElement.offsetHeight === 0) {
                    return;
                }

                // Wait for Maps to be available
                if (typeof window.Maps === 'undefined') {
                    setTimeout(initMap_{{ $jsMarkerId }}, 100);
                    return;
                }

                const defaults = window.Maps.getDefaultCoordinates();
                const zoom = window.Maps.getZoomLevels();
                const lat = parseFloat($wire.latitude) || defaults.latitude;
                const lng = parseFloat($wire.longitude) || defaults.longitude;

                // Create map using Leaflet
                mapManager_{{ $jsMarkerId }} = window.Maps.createMap('map-{{ $markerId }}', {
                    latitude: lat,
                    longitude: lng,
                    zoom: {{ $defaultZoom }},
                    draggable: {{ $draggable ? 'true' : 'false' }},
                    showLayerControl: {{ $showLayerControl ? 'true' : 'false' }},
                    onMapClick: (clickLat, clickLng) => {
                        const latStr = clickLat.toFixed(6);
                        const lngStr = clickLng.toFixed(6);
                        $wire.call('updateCoordinates', latStr, lngStr);
                        mapManager_{{ $jsMarkerId }}.updateMarker('{{ $markerId }}', clickLat, clickLng);
                    },
                    onLocationUpdate: (dragLat, dragLng) => {
                        const latStr = dragLat.toFixed(6);
                        const lngStr = dragLng.toFixed(6);
                        $wire.call('updateCoordinates', latStr, lngStr);
                    },
                });

                if (!mapManager_{{ $jsMarkerId }}) {
                    return;
                }

                mapManager_{{ $jsMarkerId }}.init();

                // Add marker if coordinates exist
                if (lat && lng) {
                    mapManager_{{ $jsMarkerId }}.addMarker('{{ $markerId }}', lat, lng, {
                        draggable: {{ $draggable ? 'true' : 'false' }},
                    });
                }

                // Listen to location selection event from Livewire
                $wire.on('location-selected', ([data]) => {
                    if (!mapManager_{{ $jsMarkerId }}) {
                        return;
                    }

                    // Check if this event is for this map instance
                    if (data.markerId && data.markerId !== '{{ $markerId }}') {
                        return;
                    }

                    const lat = parseFloat(data.lat);
                    const lng = parseFloat(data.lng);

                    if (isNaN(lat) || isNaN(lng)) {
                        return;
                    }

                    // Update map view
                    mapManager_{{ $jsMarkerId }}.setView(lat, lng, 16);

                    // Update or add marker
                    if (mapManager_{{ $jsMarkerId }}.markers['{{ $markerId }}']) {
                        mapManager_{{ $jsMarkerId }}.updateMarker('{{ $markerId }}', lat, lng);
                    } else {
                        mapManager_{{ $jsMarkerId }}.addMarker('{{ $markerId }}', lat, lng, {
                            draggable: {{ $draggable ? 'true' : 'false' }},
                        });
                    }
                });
            }

            // Initialize map after DOM ready
            setTimeout(() => {
                initMap_{{ $jsMarkerId }}();
            }, 500);

            // Cleanup on navigation
            document.addEventListener('livewire:navigating', () => {
                if (mapManager_{{ $jsMarkerId }}) {
                    mapManager_{{ $jsMarkerId }}.destroy();
                    mapManager_{{ $jsMarkerId }} = null;
                }
            });
        </script>
    @endscript
</div>
