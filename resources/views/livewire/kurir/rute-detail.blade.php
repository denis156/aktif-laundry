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
            let map = null;
            let kurirMarker = null;
            let pelangganMarker = null;
            let routingControl = null;
            let watchId = null;
            let lastPosition = null; // Store last position to calculate bearing
            let currentBearing = 0; // Current bearing/heading
            let compassHeading = null; // Compass heading from device orientation
            let useCompass = true; // Flag to use compass or movement bearing
            const pelangganLat = parseFloat(@js($pelangganLatitude));
            const pelangganLng = parseFloat(@js($pelangganLongitude));
            const pelangganNama = @js($pelangganNama);
            const pelangganAlamat = @js($pelangganAlamat);

            function initRuteMap() {
                const mapElement = document.getElementById('map-rute-detail');
                if (!mapElement || map) return;

                if (isNaN(pelangganLat) || isNaN(pelangganLng)) {
                    console.error('Invalid coordinates:', pelangganLat, pelangganLng);
                    return;
                }

                // Wait for Leaflet
                if (typeof L === 'undefined') {
                    setTimeout(initRuteMap, 100);
                    return;
                }

                const kurirLat = parseFloat($wire.kurirLatitude) || pelangganLat;
                const kurirLng = parseFloat($wire.kurirLongitude) || pelangganLng;

                // Initialize map with rotation (start at pelanggan, will fit bounds after markers added)
                map = L.map('map-rute-detail', {
                    zoomControl: true,
                    attributionControl: false,
                    rotate: true,
                    bearing: 0,
                    touchRotate: true,
                }).setView([pelangganLat, pelangganLng], 13);

                // Add tile layer
                L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                }).addTo(map);

                // Hide attribution
                setTimeout(() => {
                    const attrs = document.getElementsByClassName('leaflet-control-attribution');
                    for (let i = 0; i < attrs.length; i++) attrs[i].style.display = 'none';
                }, 100);

                // Add pelanggan marker (home icon)
                const pelangganIcon = L.icon({
                    iconUrl: '/images/home-map-pin.png',
                    iconSize: [48, 48],
                    iconAnchor: [24, 48],
                    popupAnchor: [0, -48],
                });

                pelangganMarker = L.marker([pelangganLat, pelangganLng], { icon: pelangganIcon }).addTo(map);
                pelangganMarker.bindPopup(`
                    <div class="p-2">
                        <strong>${pelangganNama}</strong><br>
                        <small>${pelangganAlamat}</small>
                    </div>
                `);

                // Add kurir marker (arrow icon)
                const kurirIcon = L.icon({
                    iconUrl: '/images/arrow-up.png',
                    iconSize: [48, 48],
                    iconAnchor: [24, 24], // Center anchor for rotation
                    popupAnchor: [0, -24],
                });

                kurirMarker = L.marker([kurirLat, kurirLng], {
                    icon: kurirIcon,
                    rotationAngle: 0, // Initial rotation
                    rotationOrigin: 'center center'
                }).addTo(map);
                kurirMarker.bindPopup(createKurirPopup(kurirLat, kurirLng));

                // Fit bounds to show both markers (kurir and pelanggan)
                if (kurirLat !== pelangganLat || kurirLng !== pelangganLng) {
                    const bounds = L.latLngBounds([
                        [kurirLat, kurirLng],
                        [pelangganLat, pelangganLng]
                    ]);
                    map.fitBounds(bounds, {
                        padding: [80, 80], // Add padding so markers not at edge
                        maxZoom: 16 // Don't zoom in too close
                    });

                    // Initialize routing
                    initRouting(kurirLat, kurirLng, pelangganLat, pelangganLng);
                } else {
                    // If same location, just zoom to that point
                    map.setView([kurirLat, kurirLng], 16);
                }

                // Start compass tracking (Device Orientation)
                startCompassTracking();

                // Start GPS tracking
                $wire.call('updateGpsStatus', 'searching');

                if (navigator.geolocation) {
                    watchId = navigator.geolocation.watchPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            const accuracy = position.coords.accuracy;
                            const gpsHeading = position.coords.heading; // GPS heading (saat bergerak)

                            // Determine which heading to use
                            let effectiveHeading = null;

                            // Priority: 1. Compass (always), 2. GPS heading, 3. Calculated bearing
                            if (compassHeading !== null && !isNaN(compassHeading)) {
                                // Use compass heading (most accurate for device orientation)
                                effectiveHeading = compassHeading;
                            } else if (gpsHeading !== null && !isNaN(gpsHeading)) {
                                // Fallback to GPS heading if compass not available
                                effectiveHeading = gpsHeading;
                            } else if (lastPosition) {
                                // Fallback to calculated bearing from movement
                                const bearing = calculateBearing(
                                    lastPosition.lat,
                                    lastPosition.lng,
                                    lat,
                                    lng
                                );
                                if (!isNaN(bearing) && bearing !== null) {
                                    effectiveHeading = bearing;
                                }
                            }

                            // Update bearing and rotation
                            if (effectiveHeading !== null) {
                                currentBearing = effectiveHeading;

                                // Rotate map to match bearing (inverse for user orientation)
                                if (map.setBearing) {
                                    map.setBearing(-currentBearing);
                                }

                                // Rotate marker to compensate for map rotation
                                if (kurirMarker.setRotationAngle) {
                                    kurirMarker.setRotationAngle(currentBearing * 2);
                                }
                            }

                            // Store current position for next calculation
                            lastPosition = { lat, lng };

                            // Update Livewire
                            $wire.call('updateKurirLocation', lat, lng, accuracy);

                            // Update kurir marker
                            kurirMarker.setLatLng([lat, lng]);
                            kurirMarker.bindPopup(createKurirPopup(lat, lng, accuracy, currentBearing));

                            // Update routing
                            updateRouting(lat, lng, pelangganLat, pelangganLng);
                        },
                        (error) => {
                            if (error.code !== error.TIMEOUT) {
                                console.error('GPS Error:', error.message);
                            }
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 30000,
                            maximumAge: 5000,
                        }
                    );
                }
            }

            function initRouting(startLat, startLng, endLat, endLng) {
                if (routingControl) {
                    map.removeControl(routingControl);
                }

                routingControl = L.Routing.control({
                    waypoints: [
                        L.latLng(startLat, startLng),
                        L.latLng(endLat, endLng)
                    ],
                    routeWhileDragging: false,
                    addWaypoints: false,
                    draggableWaypoints: false,
                    fitSelectedRoutes: false,
                    showAlternatives: false,
                    lineOptions: {
                        styles: [{
                            color: '#3b82f6',
                            opacity: 0.8,
                            weight: 5,
                        }],
                    },
                    createMarker: () => null,
                    router: L.Routing.osrmv1({
                        serviceUrl: 'https://router.project-osrm.org/route/v1',
                    }),
                }).addTo(map);

                // Hide routing instructions
                setTimeout(() => {
                    const routingContainer = document.querySelector('.leaflet-routing-container');
                    if (routingContainer) routingContainer.style.display = 'none';
                }, 100);
            }

            function updateRouting(startLat, startLng, endLat, endLng) {
                if (routingControl) {
                    routingControl.setWaypoints([
                        L.latLng(startLat, startLng),
                        L.latLng(endLat, endLng)
                    ]);
                } else {
                    initRouting(startLat, startLng, endLat, endLng);
                }
            }

            /**
             * Calculate bearing between two coordinates
             * @returns {number} Bearing in degrees (0-360)
             */
            function calculateBearing(lat1, lng1, lat2, lng2) {
                const toRad = (deg) => deg * Math.PI / 180;
                const toDeg = (rad) => rad * 180 / Math.PI;

                const dLng = toRad(lng2 - lng1);
                const y = Math.sin(dLng) * Math.cos(toRad(lat2));
                const x = Math.cos(toRad(lat1)) * Math.sin(toRad(lat2)) -
                          Math.sin(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.cos(dLng);

                let bearing = toDeg(Math.atan2(y, x));
                bearing = (bearing + 360) % 360; // Normalize to 0-360

                return bearing;
            }

            /**
             * Start compass tracking using Device Orientation API
             */
            function startCompassTracking() {
                // Check if device orientation is supported
                if (!window.DeviceOrientationEvent) {
                    console.log('Device orientation not supported');
                    return;
                }

                // Request permission for iOS 13+
                if (typeof DeviceOrientationEvent.requestPermission === 'function') {
                    DeviceOrientationEvent.requestPermission()
                        .then(permissionState => {
                            if (permissionState === 'granted') {
                                attachOrientationListener();
                            } else {
                                console.log('Device orientation permission denied');
                            }
                        })
                        .catch(console.error);
                } else {
                    // Non-iOS or older iOS - no permission needed
                    attachOrientationListener();
                }
            }

            /**
             * Attach device orientation listener
             */
            function attachOrientationListener() {
                window.addEventListener('deviceorientationabsolute', handleOrientation, true);
                window.addEventListener('deviceorientation', handleOrientation, true);
            }

            /**
             * Handle device orientation event
             */
            function handleOrientation(event) {
                let heading = null;

                // Use absolute orientation if available (more accurate)
                if (event.absolute && event.alpha !== null) {
                    heading = event.alpha;
                } else if (event.webkitCompassHeading !== undefined) {
                    // iOS Safari uses webkitCompassHeading
                    heading = event.webkitCompassHeading;
                } else if (event.alpha !== null) {
                    // Fallback to alpha (0-360, where 0 is north)
                    heading = 360 - event.alpha;
                }

                if (heading !== null) {
                    compassHeading = heading;

                    // Update marker rotation in real-time (even without GPS update)
                    if (kurirMarker && map) {
                        if (map.setBearing) {
                            map.setBearing(-compassHeading);
                        }
                        if (kurirMarker.setRotationAngle) {
                            kurirMarker.setRotationAngle(compassHeading * 2);
                        }
                    }
                }
            }

            function createKurirPopup(lat, lng, accuracy = null, bearing = null) {
                const accuracyText = accuracy ? `<div class="text-xs text-secondary mt-1">Akurasi: ±${accuracy.toFixed(1)}m</div>` : '';
                const bearingText = bearing !== null ? `<div class="text-xs text-info mt-1">Arah: ${Math.round(bearing)}°</div>` : '';

                return `
                    <div class="p-2">
                        <div class="flex items-center gap-2 mb-1">
                            <strong class="text-base">Lokasi Anda (Kurir)</strong>
                        </div>
                        <div class="text-xs text-secondary font-mono">
                            ${lat.toFixed(6)}, ${lng.toFixed(6)}
                        </div>
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
                // Remove orientation listeners
                window.removeEventListener('deviceorientationabsolute', handleOrientation, true);
                window.removeEventListener('deviceorientation', handleOrientation, true);

                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }
                if (routingControl && map) {
                    map.removeControl(routingControl);
                    routingControl = null;
                }
                if (map) {
                    map.remove();
                    map = null;
                }
                kurirMarker = null;
                pelangganMarker = null;
                lastPosition = null;
                currentBearing = 0;
                compassHeading = null;
            });
        </script>
    @endscript
@endif
