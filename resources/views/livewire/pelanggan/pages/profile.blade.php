<div class="container mx-auto">
    <x-header title="Profil Saya" subtitle="Kelola data profil Anda" icon="iconpark.user-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator>
        <x-slot:actions>
            <x-button icon="iconpark.lefttwo-o" class="btn-secondary btn-sm" label="Kembali"
                link="{{ route('pengaturan.pelanggan') }}" responsive />
        </x-slot:actions>
    </x-header>

    <div class="space-y-4 mb-24">
        {{-- Avatar & Nama --}}
        <x-card class="bg-base-200">
            <div class="flex flex-col items-center space-y-4">
                <x-file wire:model="avatar" accept="image/png, image/jpeg, image/jpg">
                    <img src="{{ $avatarUrl }}" class="w-40 h-40 rounded-full object-cover" />
                </x-file>
                <div class="text-center">
                    <p class="font-bold text-xl">{{ $nama }}</p>
                    <p class="font-thin text-sm">Klik gambar untuk upload avatar baru (max {{ $avatarMaxSizeMB }} MB)
                    </p>
                </div>
            </div>
        </x-card>

        {{-- Informasi Kontak --}}
        <div class="space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Informasi Kontak</p>
            <x-card class="shadow-lg border border-primary">
                <div class="space-y-4">
                    {{-- Nama --}}
                    <div>
                        <p class="text-xs text-base-content/60 mb-1">Nama Lengkap</p>
                        <div class="flex justify-between items-center">
                            <span class="text-md font-medium">{{ $nama }}</span>
                            <x-button label="Ubah" icon="iconpark.write-o" class="btn-success btn-sm"
                                @click="$wire.editNamaModal = true" />
                        </div>
                    </div>
                    <div class="divider"></div>
                    {{-- No HP --}}
                    <div>
                        <p class="text-xs text-base-content/60 mb-1">No. Telepon</p>
                        <div class="flex justify-between items-center">
                            <span class="text-md font-medium">{{ $no_hp ?: '-' }}</span>
                            <x-button label="Ubah" icon="iconpark.write-o" class="btn-success btn-sm"
                                @click="$wire.editNoHpModal = true" />
                        </div>
                    </div>
                    <div class="divider"></div>
                    {{-- Email --}}
                    <div>
                        <p class="text-xs text-base-content/60 mb-1">Email</p>
                        <div class="flex justify-between items-center">
                            <span class="text-md font-medium truncate">{{ $email ?: '-' }}</span>
                            <x-button label="Ubah" icon="iconpark.write-o" class="btn-success btn-sm"
                                @click="$wire.editEmailModal = true" />
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Alamat --}}
        <div class="space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Alamat</p>
            <x-card class="shadow-lg border border-primary">
                <div>
                    <p class="text-xs text-base-content/60 mb-2">Alamat Lengkap</p>
                    <p class="text-md">{{ $alamat ?: 'Belum ada alamat' }}</p>
                </div>
                <div class="divider"></div>
                <x-button label="Ubah Alamat" icon="iconpark.write-o" class="btn-success btn-sm btn-block"
                    @click="$wire.editAlamatModal = true" />
            </x-card>
        </div>
    </div>

    {{-- Modal Edit Nama --}}
    <x-modal wire:model="editNamaModal" title="Ubah Nama" class="modal-bottom w-full backdrop-blur" persistent>
        <x-form wire:submit="saveNama" no-separator>
            <x-input label="Nama Lengkap" wire:model="nama" placeholder="Nama Anda" icon="o-user" required />

            <x-slot:actions>
                <div class="grid grid-cols-2 gap-4 w-full">
                    <x-button label="Batal" class="btn-ghost" @click="$wire.editNamaModal = false" />
                    <x-button label="Simpan" type="submit" class="btn-primary" spinner="saveNama" />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal Edit No HP --}}
    <x-modal wire:model="editNoHpModal" title="Ubah No. Telepon" class="modal-bottom w-full backdrop-blur" persistent>
        <x-form wire:submit="saveNoHp" no-separator>
            <x-input label="No. HP" wire:model="no_hp" placeholder="Contoh: 08123456789" icon="o-phone" required />

            <x-slot:actions>
                <div class="grid grid-cols-2 gap-4 w-full">
                    <x-button label="Batal" class="btn-ghost" @click="$wire.editNoHpModal = false" />
                    <x-button label="Simpan" type="submit" class="btn-primary" spinner="saveNoHp" />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal Edit Email --}}
    <x-modal wire:model="editEmailModal" title="Ubah Email" class="modal-bottom w-full backdrop-blur" persistent>
        <x-form wire:submit="saveEmail" no-separator>
            <x-input label="Email" type="email" wire:model="email" placeholder="Contoh: email@contoh.com"
                icon="o-envelope" hint="Opsional" />

            <x-slot:actions>
                <div class="grid grid-cols-2 gap-4 w-full">
                    <x-button label="Batal" class="btn-ghost" @click="$wire.editEmailModal = false" />
                    <x-button label="Simpan" type="submit" class="btn-primary" spinner="saveEmail" />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal Edit Alamat --}}
    <x-modal wire:model="editAlamatModal" title="Ubah Alamat" class="modal-bottom w-full backdrop-blur" persistent>
        <x-form wire:submit="saveAlamat" no-separator>
            <x-select label="Kecamatan" wire:model.live="kecamatan" :options="$kecamatanOptions"
                placeholder="Pilih kecamatan" icon="o-map-pin" required />

            <x-select label="Kelurahan/Desa" wire:model.live="kelurahan" :options="$kelurahanOptions"
                placeholder="Pilih kelurahan" hint="{{ empty($kecamatan) ? 'Pilih kecamatan dulu' : '' }}" icon="o-map"
                :disabled="empty($kecamatan)" required />

            <x-textarea label="Detail Alamat" wire:model.live="detail_alamat"
                placeholder="Nama jalan, No. rumah, RT/RW, Patokan"
                hint="Contoh: Jl. Sudirman No. 45, RT 02/RW 05, Samping Indomaret" rows="3" required />

            {{-- Lokasi di Peta --}}
            <div class="space-y-2">
                <label class="text-sm font-medium">Lokasi di Peta</label>
                <div class="relative overflow-hidden rounded-lg h-78">
                    <div id="map-pelanggan" wire:ignore class="w-full h-full z-40"></div>

                    {{-- Zoom Controller - KIRI --}}
                    <div class="absolute top-2 left-2 z-48">
                        <livewire:components.maps-controller
                            :map-id="'map-pelanggan'"
                            position="top-left"
                            :show-zoom="true"
                            :show-layers="false"
                            :show-center="false"
                            :show-compass="false"
                        />
                    </div>

                    {{-- Layer Controller - KANAN --}}
                    <div class="absolute top-2 right-2 z-48">
                        <livewire:components.maps-controller
                            :map-id="'map-pelanggan'"
                            position="top-right"
                            :show-zoom="false"
                            :show-layers="true"
                            :show-center="false"
                            :show-compass="false"
                        />
                    </div>
                </div>
                <button type="button" id="getLocationBtn" class="btn btn-success btn-sm btn-block mt-2">
                    <x-icon name="iconpark.localpin-o" />
                    Ambil Lokasi Saya
                </button>
                <p class="text-xs text-base-content/60">Klik pada peta atau drag marker untuk set lokasi, atau gunakan tombol
                    di atas untuk deteksi lokasi otomatis</p>
            </div>

            <x-slot:actions>
                <div class="grid grid-cols-2 gap-4 w-full">
                    <x-button label="Batal" class="btn-ghost" @click="$wire.editAlamatModal = false" />
                    <x-button label="Simpan" type="submit" class="btn-primary" spinner="saveAlamat" />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>

    @script
        <script>
            let mapManager = null;

            $wire.$watch('editAlamatModal', (value) => {
                if (value === true && !mapManager) {
                    setTimeout(() => {
                        initPelangganMap();
                    }, 500);
                } else if (value === false && mapManager) {
                    mapManager.destroy();
                    mapManager = null;
                }
            });

            function initPelangganMap() {
                const mapElement = document.getElementById('map-pelanggan');
                if (!mapElement || mapElement.offsetWidth === 0 || mapElement.offsetHeight === 0) {
                    return;
                }

                // Wait for Maps to be available
                if (typeof window.Maps === 'undefined') {
                    setTimeout(initPelangganMap, 100);
                    return;
                }

                const defaults = window.Maps.getDefaultCoordinates();
                const zoom = window.Maps.getZoomLevels();
                const lat = parseFloat($wire.latitude) || defaults.latitude;
                const lng = parseFloat($wire.longitude) || defaults.longitude;

                // Create map using Leaflet
                mapManager = window.Maps.createMap('map-pelanggan', {
                    latitude: lat,
                    longitude: lng,
                    zoom: zoom.default,
                    draggable: true,
                    showLayerControl: false, // Disable default layer control - using MapsController
                    showZoomControl: false, // Disable default zoom control - using MapsController
                    onMapClick: (clickLat, clickLng) => {
                        $wire.latitude = clickLat.toFixed(6);
                        $wire.longitude = clickLng.toFixed(6);
                        mapManager.updateMarker('pelanggan-location', clickLat, clickLng);
                    },
                    onLocationUpdate: (dragLat, dragLng) => {
                        $wire.latitude = dragLat.toFixed(6);
                        $wire.longitude = dragLng.toFixed(6);
                    },
                });

                if (!mapManager) {
                    return;
                }

                mapManager.init();

                // Add marker if coordinates exist
                if (lat && lng) {
                    mapManager.addMarker('pelanggan-location', lat, lng, {
                        draggable: true,
                    });
                }

                // Setup "Ambil Lokasi Saya" button
                setupGetLocationButton();
            }

            function setupGetLocationButton() {
                setTimeout(() => {
                    const getLocationBtn = document.getElementById('getLocationBtn');
                    if (!getLocationBtn) return;

                    // Clone button to remove old listeners
                    const newBtn = getLocationBtn.cloneNode(true);
                    getLocationBtn.parentNode.replaceChild(newBtn, getLocationBtn);

                    newBtn.addEventListener('click', function(e) {
                        e.preventDefault();

                        if (!navigator.geolocation) {
                            alert('Geolocation tidak didukung oleh browser Anda');
                            return;
                        }

                        const btnText = this.innerHTML;
                        this.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Mengambil lokasi...';
                        this.disabled = true;

                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;

                                // Update Livewire properties
                                $wire.latitude = lat.toFixed(6);
                                $wire.longitude = lng.toFixed(6);

                                // Update or add marker
                                if (mapManager.markers['pelanggan-location']) {
                                    mapManager.updateMarker('pelanggan-location', lat, lng);
                                } else {
                                    mapManager.addMarker('pelanggan-location', lat, lng, {
                                        draggable: true,
                                    });
                                }

                                // Center map
                                mapManager.setView(lat, lng, window.LeafletUtils.config.zoom.default);

                                this.innerHTML = btnText;
                                this.disabled = false;
                            },
                            (error) => {
                                const errorMessages = {
                                    [error.PERMISSION_DENIED]: 'Izin lokasi ditolak. Aktifkan izin lokasi di pengaturan browser Anda.',
                                    [error.POSITION_UNAVAILABLE]: 'Informasi lokasi tidak tersedia.',
                                    [error.TIMEOUT]: 'Waktu permintaan lokasi habis.'
                                };

                                alert(errorMessages[error.code] || 'Gagal mendapatkan lokasi');

                                this.innerHTML = btnText;
                                this.disabled = false;
                            },
                            window.LeafletUtils.config.gpsOptions
                        );
                    });
                }, 100);
            }
        </script>
    @endscript
</div>
