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
            let ruteMap = null;
            let pelangganMarker = null;

            function initRuteMap() {
                const mapElement = document.getElementById('map-rute-detail');
                if (!mapElement) {
                    console.error('Map element not found!');
                    return;
                }

                if (ruteMap) {
                    console.log('Map already initialized');
                    return;
                }

                const pelangganLat = parseFloat(@js($pelangganLatitude));
                const pelangganLng = parseFloat(@js($pelangganLongitude));

                if (isNaN(pelangganLat) || isNaN(pelangganLng)) {
                    console.error('Invalid coordinates:', pelangganLat, pelangganLng);
                    return;
                }

                console.log('Initializing map with coordinates:', pelangganLat, pelangganLng);

                // Initialize map centered on pelanggan location
                ruteMap = L.map('map-rute-detail', {
                    zoomControl: true,
                    attributionControl: false,
                    dragging: true,
                    scrollWheelZoom: true
                }).setView([pelangganLat, pelangganLng], 16);

                // Map layers
                const mapLayers = {
                    'OpenStreetMap': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }),
                    'Google Satellite': L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        attribution: '&copy; Google'
                    }),
                    'Google Street': L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        attribution: '&copy; Google'
                    }),
                    'Google Hybrid': L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        attribution: '&copy; Google'
                    })
                };

                mapLayers['Google Street'].addTo(ruteMap);

                L.control.layers(mapLayers, {}, {
                    position: 'topright',
                    collapsed: true
                }).addTo(ruteMap);

                // Hide attribution
                setTimeout(() => {
                    const attributionElements = document.getElementsByClassName('leaflet-control-attribution');
                    for (let i = 0; i < attributionElements.length; i++) {
                        attributionElements[i].style.display = 'none';
                    }
                }, 100);

                // Add pelanggan marker (default marker)
                pelangganMarker = L.marker([pelangganLat, pelangganLng]).addTo(ruteMap);

                // Simple popup content
                const popupContent = `
                    <div class="p-2">
                        <strong>@js($pelangganNama)</strong><br>
                        <small>@js($pelangganAlamat)</small>
                    </div>
                `;

                pelangganMarker.bindPopup(popupContent);
            }

            // Initialize map after DOM ready
            setTimeout(() => {
                initRuteMap();
            }, 500);

            // Cleanup on navigation
            document.addEventListener('livewire:navigating', () => {
                if (ruteMap) {
                    ruteMap.remove();
                    ruteMap = null;
                    pelangganMarker = null;
                }
            });
        </script>
    @endscript
@endif
