/**
 * Leaflet Map Utility
 * Fungsi helper untuk inisialisasi dan manajemen peta Leaflet
 */

/**
 * Inisialisasi peta Leaflet dengan marker interaktif
 *
 * @param {string} mapElementId - ID dari element div untuk peta
 * @param {object} options - Opsi konfigurasi
 * @param {number} options.defaultLat - Latitude default (default: -3.9778 - Kendari)
 * @param {number} options.defaultLng - Longitude default (default: 122.5145 - Kendari)
 * @param {number} options.zoom - Zoom level (default: 15)
 * @param {object} options.wire - Livewire component instance ($wire)
 * @param {string} options.latitudeProperty - Nama property latitude di Livewire (default: 'latitude')
 * @param {string} options.longitudeProperty - Nama property longitude di Livewire (default: 'longitude')
 * @returns {object} - Object berisi {map, marker}
 */
export function initLeafletMap(mapElementId, options = {}) {
    const {
        defaultLat = -3.9778,
        defaultLng = 122.5145,
        zoom = 15,
        wire = null,
        latitudeProperty = 'latitude',
        longitudeProperty = 'longitude'
    } = options;

    if (!wire) {
        return null;
    }

    const lat = wire[latitudeProperty] ? parseFloat(wire[latitudeProperty]) : defaultLat;
    const lng = wire[longitudeProperty] ? parseFloat(wire[longitudeProperty]) : defaultLng;

    const map = L.map(mapElementId, {
        zoomControl: true,
        attributionControl: false
    }).setView([lat, lng], zoom);

    // Definisikan berbagai tile layers
    const mapLayers = {
        'OpenStreetMap': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
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

    // Set default layer (Google Street)
    mapLayers['Google Street'].addTo(map);

    // Tambahkan layer control
    L.control.layers(mapLayers, {}, {
        position: 'topright',
        collapsed: true
    }).addTo(map);

    // Sembunyikan atribut Leaflet dengan CSS (backup method)
    setTimeout(() => {
        const attributionElements = document.getElementsByClassName('leaflet-control-attribution');
        for (let i = 0; i < attributionElements.length; i++) {
            attributionElements[i].style.display = 'none';
        }
    }, 100);

    // Flag untuk mencegah loop update
    let isUpdatingFromMap = false;

    // Buat ikon marker kustom
    const customIcon = L.icon({
        iconUrl: '/images/marker.png',
        iconSize: [48, 48], // ukuran ikon (diperbesar)
        iconAnchor: [24, 48], // titik anchor ikon (tengah bawah)
        popupAnchor: [0, -48] // posisi popup relatif terhadap ikon
    });

    // Tambahkan marker
    let marker = null;
    if (wire[latitudeProperty] && wire[longitudeProperty]) {
        marker = L.marker([lat, lng], {
            draggable: true,
            icon: customIcon
        }).addTo(map);

        marker.on('dragend', function() {
            isUpdatingFromMap = true;
            const position = marker.getLatLng();
            wire[latitudeProperty] = position.lat.toFixed(6);
            wire[longitudeProperty] = position.lng.toFixed(6);
            setTimeout(() => { isUpdatingFromMap = false; }, 100);
        });
    }

    // Klik pada peta untuk menambah/pindahkan marker
    map.on('click', function(e) {
        isUpdatingFromMap = true;
        const clickLat = e.latlng.lat;
        const clickLng = e.latlng.lng;

        if (marker) {
            marker.setLatLng([clickLat, clickLng]);
        } else {
            marker = L.marker([clickLat, clickLng], {
                draggable: true,
                icon: customIcon
            }).addTo(map);

            marker.on('dragend', function() {
                isUpdatingFromMap = true;
                const position = marker.getLatLng();
                wire[latitudeProperty] = position.lat.toFixed(6);
                wire[longitudeProperty] = position.lng.toFixed(6);
                setTimeout(() => { isUpdatingFromMap = false; }, 100);
            });
        }

        wire[latitudeProperty] = clickLat.toFixed(6);
        wire[longitudeProperty] = clickLng.toFixed(6);
        setTimeout(() => { isUpdatingFromMap = false; }, 100);
    });

    // Watch untuk perubahan latitude dari input
    wire.$watch(latitudeProperty, (value) => {
        if (value && wire[longitudeProperty] && !isUpdatingFromMap) {
            const newLat = parseFloat(value);
            const newLng = parseFloat(wire[longitudeProperty]);

            if (marker) {
                marker.setLatLng([newLat, newLng]);
            } else {
                marker = L.marker([newLat, newLng], {
                    draggable: true,
                    icon: customIcon
                }).addTo(map);

                marker.on('dragend', function() {
                    isUpdatingFromMap = true;
                    const position = marker.getLatLng();
                    wire[latitudeProperty] = position.lat.toFixed(6);
                    wire[longitudeProperty] = position.lng.toFixed(6);
                    setTimeout(() => { isUpdatingFromMap = false; }, 100);
                });
            }

            const currentCenter = map.getCenter();
            const distance = Math.abs(currentCenter.lat - newLat) + Math.abs(currentCenter.lng - newLng);
            if (distance > 0.01) {
                map.setView([newLat, newLng], zoom);
            }
        }
    });

    // Watch untuk perubahan longitude dari input
    wire.$watch(longitudeProperty, (value) => {
        if (value && wire[latitudeProperty] && !isUpdatingFromMap) {
            const newLat = parseFloat(wire[latitudeProperty]);
            const newLng = parseFloat(value);

            if (marker) {
                marker.setLatLng([newLat, newLng]);
            } else {
                marker = L.marker([newLat, newLng], {
                    draggable: true,
                    icon: customIcon
                }).addTo(map);

                marker.on('dragend', function() {
                    isUpdatingFromMap = true;
                    const position = marker.getLatLng();
                    wire[latitudeProperty] = position.lat.toFixed(6);
                    wire[longitudeProperty] = position.lng.toFixed(6);
                    setTimeout(() => { isUpdatingFromMap = false; }, 100);
                });
            }

            const currentCenter = map.getCenter();
            const distance = Math.abs(currentCenter.lat - newLat) + Math.abs(currentCenter.lng - newLng);
            if (distance > 0.01) {
                map.setView([newLat, newLng], zoom);
            }
        }
    });

    return { map, marker };
}

/**
 * Ekstrak koordinat dari berbagai format Google Maps URL
 *
 * @param {string} url - URL Google Maps
 * @returns {object|null} - Object {lat, lng} atau null jika tidak ditemukan
 */
export function extractCoordinatesFromUrl(url) {
    if (!url) return null;

    // Pattern untuk berbagai format Google Maps URL
    const patterns = [
        // Format: @-3.992409,122.517426
        /@(-?\d+\.?\d*),(-?\d+\.?\d*)/,
        // Format: !3d-3.992409!4d122.517426
        /!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/,
        // Format: 3�59'32.7"S 122�31'02.7"E (DMS)
        /(\d+)�(\d+)'([\d.]+)"([NS])\s+(\d+)�(\d+)'([\d.]+)"([EW])/,
    ];

    for (const pattern of patterns) {
        const matches = url.match(pattern);
        if (matches) {
            if (matches.length === 9) {
                // DMS format
                const lat = convertDMSToDecimal(
                    parseInt(matches[1]),
                    parseInt(matches[2]),
                    parseFloat(matches[3]),
                    matches[4]
                );
                const lng = convertDMSToDecimal(
                    parseInt(matches[5]),
                    parseInt(matches[6]),
                    parseFloat(matches[7]),
                    matches[8]
                );
                return { lat, lng };
            } else if (matches.length === 3) {
                // Decimal format
                return {
                    lat: parseFloat(matches[1]),
                    lng: parseFloat(matches[2])
                };
            }
        }
    }

    return null;
}

/**
 * Konversi koordinat DMS (Degrees Minutes Seconds) ke Decimal
 *
 * @param {number} degrees - Derajat
 * @param {number} minutes - Menit
 * @param {number} seconds - Detik
 * @param {string} direction - Arah (N/S/E/W)
 * @returns {number} - Koordinat dalam format decimal
 */
function convertDMSToDecimal(degrees, minutes, seconds, direction) {
    let decimal = degrees + (minutes / 60) + (seconds / 3600);

    if (direction === 'S' || direction === 'W') {
        decimal *= -1;
    }

    return parseFloat(decimal.toFixed(6));
}

// Make functions available globally for Livewire @script
if (typeof window !== 'undefined') {
    window.LeafletUtils = {
        initLeafletMap,
        extractCoordinatesFromUrl
    };
}
