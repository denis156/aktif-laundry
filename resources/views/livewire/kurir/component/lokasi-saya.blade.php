<div wire:poll.10s.visible="refreshStatus">
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

        {{-- Map Container --}}
        @if ($latitude && $longitude)
            <div id="map-lokasi-kurir" wire:ignore class="w-full h-[42dvh] rounded z-40"></div>
        @else
            <div class="flex items-center justify-center h-[42dvh]">
                <div class="text-center space-y-3 p-4">
                    <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mx-auto">
                        <x-icon name="iconpark.localpin-o" class="h-8 text-base-content/40 animate-pulse" />
                    </div>
                    <div class="space-y-1">
                        <p class="text-base-content/60 font-medium">Menginisialisasi GPS...</p>
                        <p class="text-xs text-base-content/40">Pastikan GPS perangkat Anda aktif</p>
                    </div>
                </div>
            </div>
        @endif
    </x-card>
</div>

@script
    <script>
        let lokasiMap = null;
        let lokasiMarker = null;
        let accuracyCircle = null;
        let watchId = null;
        let isMapInitialized = false;

        function initLokasiMap() {
            const mapElement = document.getElementById('map-lokasi-kurir');
            if (!mapElement || isMapInitialized) return;

            const lat = parseFloat($wire.latitude) || -3.9778;
            const lng = parseFloat($wire.longitude) || 122.5145;

            lokasiMap = L.map('map-lokasi-kurir', {
                zoomControl: true,
                attributionControl: false,
                dragging: true,
                scrollWheelZoom: true
            }).setView([lat, lng], 16);

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

            mapLayers['Google Street'].addTo(lokasiMap);

            L.control.layers(mapLayers, {}, {
                position: 'topright',
                collapsed: true
            }).addTo(lokasiMap);

            // Hide attribution
            setTimeout(() => {
                const attributionElements = document.getElementsByClassName('leaflet-control-attribution');
                for (let i = 0; i < attributionElements.length; i++) {
                    attributionElements[i].style.display = 'none';
                }
            }, 100);

            const customIcon = L.icon({
                iconUrl: '/images/marker.png',
                iconSize: [48, 48],
                iconAnchor: [24, 48],
                popupAnchor: [0, -48]
            });

            lokasiMarker = L.marker([lat, lng], {
                icon: customIcon,
                draggable: false
            }).addTo(lokasiMap);

            // Accuracy circle
            accuracyCircle = L.circle([lat, lng], {
                radius: 50,
                color: '#3b82f6',
                fillColor: '#3b82f6',
                fillOpacity: 0.15,
                weight: 2
            }).addTo(lokasiMap);

            updatePopupContent(lat, lng);

            isMapInitialized = true;

            // Start GPS tracking dengan wire:stream
            startGPSTrackingWithStream();
        }

        function updatePopupContent(lat, lng, accuracy = null) {
            if (!lokasiMarker) return;

            const accuracyText = accuracy ? `<div class="text-xs text-base-content/60 mt-1">Akurasi: ±${accuracy.toFixed(1)}m</div>` : '';

            const popupContent = `
                <div class="p-2">
                    <div class="flex items-center gap-2 mb-1">
                        <x-icon name="iconpark.localpin-o" class="w-5 h-5 text-primary" />
                        <strong class="text-base">Lokasi Anda</strong>
                    </div>
                    <div class="text-xs text-base-content/60 font-mono">
                        ${lat.toFixed(6)}, ${lng.toFixed(6)}
                    </div>
                    ${accuracyText}
                </div>
            `;

            lokasiMarker.bindPopup(popupContent);
        }

        function startGPSTrackingWithStream() {
            if (!navigator.geolocation) {
                console.error('Geolocation tidak didukung');
                $wire.call('updateGpsStatus', 'error');
                return;
            }

            // Update status ke "searching"
            $wire.call('updateGpsStatus', 'searching');

            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = position.coords.accuracy;

                    // Call Livewire method dengan wire:stream
                    $wire.call('updateLocation', lat, lng, accuracy);

                    // Update map marker & circle
                    if (lokasiMarker && lokasiMap) {
                        lokasiMarker.setLatLng([lat, lng]);
                        updatePopupContent(lat, lng, accuracy);

                        // Update accuracy circle
                        if (accuracyCircle) {
                            accuracyCircle.setLatLng([lat, lng]);
                            accuracyCircle.setRadius(accuracy);

                            // Change color based on accuracy
                            const color = accuracy <= 10 ? '#22c55e' : accuracy <= 50 ? '#eab308' : '#ef4444';
                            accuracyCircle.setStyle({
                                color: color,
                                fillColor: color
                            });
                        }

                        // Pan map smoothly jika bergerak cukup jauh
                        const currentCenter = lokasiMap.getCenter();
                        const distance = Math.sqrt(
                            Math.pow(currentCenter.lat - lat, 2) +
                            Math.pow(currentCenter.lng - lng, 2)
                        );

                        if (distance > 0.0005) { // ~55 meters
                            lokasiMap.panTo([lat, lng], {
                                animate: true,
                                duration: 1,
                                easeLinearity: 0.5
                            });
                        }
                    }
                },
                (error) => {
                    console.error('GPS Error:', error.message);
                    $wire.call('updateGpsStatus', 'error');

                    // Retry after 5 seconds
                    setTimeout(() => {
                        startGPSTrackingWithStream();
                    }, 5000);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        }

        // Initialize map setelah DOM ready
        setTimeout(() => {
            initLokasiMap();
        }, 500);

        // Cleanup saat navigasi
        document.addEventListener('livewire:navigating', () => {
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            if (lokasiMap) {
                lokasiMap.remove();
                lokasiMap = null;
                lokasiMarker = null;
                accuracyCircle = null;
                isMapInitialized = false;
            }
        });
    </script>
@endscript
