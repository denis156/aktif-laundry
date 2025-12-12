<div>
    <x-header title="Edit Pelanggan" separator progress-indicator>
        <x-slot:subtitle>
            Perbarui informasi pelanggan
        </x-slot:subtitle>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Dasar section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Dasar" subtitle="Data identitas pelanggan" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-file label="Avatar (Opsional)" wire:model="avatar" accept="image/png, image/jpeg, image/jpg"
                    hint="Ukuran maksimal {{ $avatarMaxSizeMB }}MB. Format: JPG, PNG">
                    <img src="{{ $avatarUrl }}" class="h-40 rounded-lg" />
                </x-file>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Kode Pelanggan" wire:model="kode_pelanggan" readonly hint="Kode tidak dapat diubah"
                        icon="o-hashtag" />

                    <x-input label="Nama Pelanggan" wire:model="nama" placeholder="Contoh: Ahmad Rizki" icon="o-user"
                        required />

                    <x-input label="No. HP" wire:model="no_hp" placeholder="Contoh: 08123456789" icon="o-phone"
                        required />

                    <x-input label="Email" type="email" wire:model="email" placeholder="Contoh: pelanggan@email.com"
                        icon="o-envelope" hint="Opsional" />

                    <x-password label="Password Baru" wire:model="password"
                        placeholder="Minimal {{ $passwordMinLength }} karakter"
                        hint="Kosongkan jika tidak ingin mengubah password" icon="o-lock-closed" right />

                    <x-password label="Konfirmasi Password Baru" wire:model="password_confirmation"
                        placeholder="Ketik ulang password baru" icon="o-lock-closed" right />
                </div>
            </x-card>
        </div>

        {{-- Alamat section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Alamat" subtitle="Informasi alamat lengkap" size="text-lg" />
            </div>
            <x-card class="col-span-3" body-class="space-y-4">
                <x-textarea label="Detail Alamat" wire:model.live="detail_alamat"
                    placeholder="Contoh: Jl. Merdeka No. 123, RT 01/RW 02, Dekat Masjid Al-Ikhlas"
                    hint="Isi dengan: nama jalan, nomor rumah, RT/RW, dan patokan (dekat tempat terkenal/masjid/sekolah)"
                    rows="2" required />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select label="Provinsi" wire:model.live="provinsi" :options="$provinsiOptions"
                        placeholder="Pilih provinsi" icon="o-map" />

                    <x-select label="Kabupaten/Kota" wire:model.live="kabupaten_kota" :options="$kabupatenKotaOptions"
                        placeholder="Pilih kabupaten/kota" hint="{{ empty($provinsi) ? 'Pilih provinsi dulu' : '' }}"
                        icon="o-building-office-2" :disabled="empty($provinsi)" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select label="Kecamatan" wire:model.live="kecamatan" :options="$kecamatanOptions"
                        placeholder="Pilih kecamatan" hint="{{ empty($kabupaten_kota) ? 'Pilih kabupaten/kota dulu' : '' }}"
                        icon="o-map-pin" :disabled="empty($kabupaten_kota)" />

                    <x-select label="Kelurahan/Desa" wire:model.live="kelurahan" :options="$kelurahanOptions"
                        placeholder="Pilih kelurahan" hint="{{ empty($kecamatan) ? 'Pilih kecamatan dulu' : '' }}"
                        icon="o-map" :disabled="empty($kecamatan)" />
                </div>
            </x-card>
        </div>

        {{-- Lokasi section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Lokasi" subtitle="Peta lokasi pelanggan" size="text-lg" />
            </div>
            <x-card class="col-span-3" body-class="space-y-4">
                <x-input label="Ekstrak Sharelok Pelanggan" wire:model.live="sharelok"
                    placeholder="Paste URL Google Maps dari pelanggan"
                    hint="Otomatis mengisi Latitude dan Longitude dari URL Google Maps" icon="o-link" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Latitude" wire:model="latitude" type="number" step="any"
                        placeholder="Contoh: -3.9778" hint="Koordinat lintang lokasi pelanggan" icon="o-map-pin" required />

                    <x-input label="Longitude" wire:model="longitude" type="number" step="any"
                        placeholder="Contoh: 122.5145" hint="Koordinat bujur lokasi pelanggan" icon="o-map-pin" required />
                </div>

                <div id="map" wire:ignore class="h-100 z-0 rounded-md"></div>

                <div class="text-sm text-base-content/70">
                    <p>
                        <span class="font-semibold">Petunjuk:</span> Klik pada peta untuk mengubah lokasi pelanggan.
                        Koordinat akan otomatis terisi.
                    </p>
                </div>
            </x-card>
        </div>

        {{-- Status section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Status" subtitle="Status dan tanggal pendaftaran" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-datetime label="Tanggal Daftar" type="datetime-local" wire:model="tanggal_daftar"
                        icon="o-calendar" required />

                    <x-select label="Status" wire:model="status" icon="o-check-circle" :options="$statusOptions"
                        option-value="id" option-label="name" required />
                </div>
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('pelanggan.index') }}" wire:navigate />
            <x-button label="Simpan Perubahan" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>

    @script
        <script>
            let mapManager = null;

            function initPelangganMap() {
                const mapElement = document.getElementById('map');
                if (!mapElement || mapElement.offsetWidth === 0 || mapElement.offsetHeight === 0) {
                    return;
                }

                // Wait for LeafletMapManager
                if (typeof window.LeafletMapManager === 'undefined') {
                    setTimeout(initPelangganMap, 100);
                    return;
                }

                const lat = parseFloat($wire.latitude) || window.LeafletUtils.config.defaultCoordinates.latitude;
                const lng = parseFloat($wire.longitude) || window.LeafletUtils.config.defaultCoordinates.longitude;

                // Initialize map using OOP class
                mapManager = new window.LeafletMapManager('map', {
                    latitude: lat,
                    longitude: lng,
                    zoom: window.LeafletUtils.config.zoom.default,
                    draggable: true,
                    showLayerControl: false,
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

                mapManager.init();

                // Add marker if coordinates exist
                if (lat && lng) {
                    mapManager.addMarker('pelanggan-location', lat, lng, {
                        draggable: true,
                    });
                }
            }

            // Initialize map after DOM ready
            setTimeout(() => {
                initPelangganMap();
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
</div>
