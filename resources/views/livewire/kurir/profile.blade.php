<div class="container mx-auto">
    <x-header title="Profil Saya" subtitle="Kelola informasi profil dan data pribadi Anda" icon="iconpark.user-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator>
        <x-slot:actions>
            <x-button icon="iconpark.lefttwo-o" class="btn-secondary btn-sm" label="Kembali"
                link="{{ route('pengaturan.kurir') }}" responsive />
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
                <div id="map-kurir" wire:ignore style="height: 300px; border-radius: 0.5rem;"></div>
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
            let kurirMap = null;
            let kurirMarker = null;
            let mapInitialized = false;

            $wire.$watch('editAlamatModal', (value) => {
                if (value === true && !mapInitialized) {
                    setTimeout(() => {
                        initKurirMap();
                        mapInitialized = true;
                    }, 500);
                } else if (value === false) {
                    mapInitialized = false;
                }
            });

            function initKurirMap() {
                if (kurirMap) {
                    kurirMap.remove();
                    kurirMap = null;
                    kurirMarker = null;
                }

                const mapElement = document.getElementById('map-kurir');
                if (!mapElement || mapElement.offsetWidth === 0 || mapElement.offsetHeight === 0) {
                    return;
                }

                const mapData = window.LeafletUtils.initLeafletMap('map-kurir', {
                    defaultLat: -3.9778,
                    defaultLng: 122.5145,
                    zoom: 15,
                    wire: $wire,
                    latitudeProperty: 'latitude',
                    longitudeProperty: 'longitude'
                });

                kurirMap = mapData.map;
                kurirMarker = mapData.marker;

                setTimeout(() => {
                    const getLocationBtn = document.getElementById('getLocationBtn');
                    if (!getLocationBtn) {
                        return;
                    }

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

                                $wire.latitude = lat.toFixed(6);
                                $wire.longitude = lng.toFixed(6);

                                const customIcon = L.icon({
                                    iconUrl: '/images/marker.png',
                                    iconSize: [48, 48],
                                    iconAnchor: [24, 48],
                                    popupAnchor: [0, -48]
                                });

                                if (kurirMarker) {
                                    kurirMarker.setLatLng([lat, lng]);
                                } else {
                                    kurirMarker = L.marker([lat, lng], {
                                        draggable: true,
                                        icon: customIcon
                                    }).addTo(kurirMap);

                                    kurirMarker.on('dragend', function() {
                                        const position = kurirMarker.getLatLng();
                                        $wire.latitude = position.lat.toFixed(6);
                                        $wire.longitude = position.lng.toFixed(6);
                                    });
                                }

                                kurirMap.setView([lat, lng], 15);

                                this.innerHTML = btnText;
                                this.disabled = false;
                            },
                            (error) => {
                                let errorMsg = 'Gagal mendapatkan lokasi';

                                switch(error.code) {
                                    case error.PERMISSION_DENIED:
                                        errorMsg = 'Izin lokasi ditolak. Aktifkan izin lokasi di pengaturan browser Anda.';
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        errorMsg = 'Informasi lokasi tidak tersedia.';
                                        break;
                                    case error.TIMEOUT:
                                        errorMsg = 'Waktu permintaan lokasi habis.';
                                        break;
                                }

                                alert(errorMsg);

                                this.innerHTML = btnText;
                                this.disabled = false;
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );
                    });
                }, 100);
            }
        </script>
    @endscript
</div>
