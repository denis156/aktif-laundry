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
            let kurirMarker = null;
            let routingControl = null;
            let watchId = null;
            let isMapInitialized = false;
            let hasInitialRoute = false;

            function initRuteMap() {
                const mapElement = document.getElementById('map-rute-detail');
                if (!mapElement || isMapInitialized) {
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

                // Add pelanggan marker (default blue marker - tidak diubah)
                pelangganMarker = L.marker([pelangganLat, pelangganLng]).addTo(ruteMap);

                const pelangganNama = @js($pelangganNama);
                const pelangganAlamat = @js($pelangganAlamat);

                const pelangganPopup = `
                    <div class="p-2">
                        <strong>${pelangganNama}</strong><br>
                        <small>${pelangganAlamat}</small>
                    </div>
                `;

                pelangganMarker.bindPopup(pelangganPopup);

                // Add kurir marker dengan custom icon
                const kurirLat = parseFloat($wire.kurirLatitude) || pelangganLat;
                const kurirLng = parseFloat($wire.kurirLongitude) || pelangganLng;

                const customKurirIcon = L.icon({
                    iconUrl: '/images/marker.png',
                    iconSize: [48, 48],
                    iconAnchor: [24, 48],
                    popupAnchor: [0, -48]
                });

                kurirMarker = L.marker([kurirLat, kurirLng], {
                    icon: customKurirIcon,
                    draggable: false
                }).addTo(ruteMap);

                updateKurirPopupContent(kurirLat, kurirLng);

                isMapInitialized = true;

                // Initialize routing jika kurir sudah punya koordinat
                if (kurirLat && kurirLng && (kurirLat !== pelangganLat || kurirLng !== pelangganLng)) {
                    initRouting(kurirLat, kurirLng, pelangganLat, pelangganLng);
                }

                // Start GPS tracking untuk kurir
                startKurirGPSTracking();
            }

            function initRouting(kurirLat, kurirLng, pelangganLat, pelangganLng) {
                // Remove existing routing control jika ada
                if (routingControl) {
                    ruteMap.removeControl(routingControl);
                    routingControl = null;
                }

                // Create routing control
                routingControl = L.Routing.control({
                    waypoints: [
                        L.latLng(kurirLat, kurirLng),
                        L.latLng(pelangganLat, pelangganLng)
                    ],
                    routeWhileDragging: false,
                    addWaypoints: false,
                    draggableWaypoints: false,
                    fitSelectedRoutes: true,
                    showAlternatives: false,
                    lineOptions: {
                        styles: [
                            {color: '#3b82f6', opacity: 0.8, weight: 5}
                        ],
                        extendToWaypoints: true,
                        missingRouteTolerance: 0
                    },
                    createMarker: function() {
                        return null; // Don't create default markers
                    },
                    router: L.Routing.osrmv1({
                        serviceUrl: 'https://router.project-osrm.org/route/v1'
                    })
                }).addTo(ruteMap);

                hasInitialRoute = true;

                // Event listener untuk route found
                routingControl.on('routesfound', function(e) {
                    const routes = e.routes;
                    const summary = routes[0].summary;

                    console.log('Route found:');
                    console.log('Total distance: ' + (summary.totalDistance / 1000).toFixed(2) + ' km');
                    console.log('Total time: ' + Math.round(summary.totalTime / 60) + ' minutes');
                });

                // Hide routing instructions panel
                setTimeout(() => {
                    const routingContainer = document.querySelector('.leaflet-routing-container');
                    if (routingContainer) {
                        routingContainer.style.display = 'none';
                    }
                }, 100);
            }

            function updateRouting(newKurirLat, newKurirLng) {
                const pelangganLat = parseFloat(@js($pelangganLatitude));
                const pelangganLng = parseFloat(@js($pelangganLongitude));

                if (routingControl) {
                    // Update waypoints
                    routingControl.setWaypoints([
                        L.latLng(newKurirLat, newKurirLng),
                        L.latLng(pelangganLat, pelangganLng)
                    ]);
                } else {
                    // Initialize routing jika belum ada
                    initRouting(newKurirLat, newKurirLng, pelangganLat, pelangganLng);
                }
            }

            function updateKurirPopupContent(lat, lng, accuracy = null) {
                if (!kurirMarker) return;

                const accuracyText = accuracy ? `<div class="text-xs text-secondary mt-1">Akurasi: ±${accuracy.toFixed(1)}m</div>` : '';

                const popupContent = `
                    <div class="p-2">
                        <div class="flex items-center gap-2 mb-1">
                            <strong class="text-base">Lokasi Anda (Kurir)</strong>
                        </div>
                        <div class="text-xs text-secondary font-mono">
                            ${lat.toFixed(6)}, ${lng.toFixed(6)}
                        </div>
                        ${accuracyText}
                    </div>
                `;

                kurirMarker.bindPopup(popupContent);
            }

            function startKurirGPSTracking() {
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

                        // Call Livewire method
                        $wire.call('updateKurirLocation', lat, lng, accuracy);

                        // Update kurir marker
                        if (kurirMarker && ruteMap) {
                            kurirMarker.setLatLng([lat, lng]);
                            updateKurirPopupContent(lat, lng, accuracy);

                            // Update routing
                            updateRouting(lat, lng);
                        }
                    },
                    (error) => {
                        // Don't log timeout errors (they're normal in indoor/poor signal conditions)
                        if (error.code !== error.TIMEOUT) {
                            console.error('GPS Error:', error.message);
                        }

                        // Don't update status or retry - watchPosition will keep trying automatically
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 30000, // Increase timeout to 30 seconds
                        maximumAge: 5000 // Allow cached position up to 5 seconds old
                    }
                );
            }

            // Initialize map after DOM ready
            setTimeout(() => {
                initRuteMap();
            }, 500);

            // Cleanup on navigation
            document.addEventListener('livewire:navigating', () => {
                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }
                if (ruteMap) {
                    if (routingControl) {
                        ruteMap.removeControl(routingControl);
                        routingControl = null;
                    }
                    ruteMap.remove();
                    ruteMap = null;
                    pelangganMarker = null;
                    kurirMarker = null;
                    isMapInitialized = false;
                    hasInitialRoute = false;
                }
            });
        </script>
    @endscript
@endif
