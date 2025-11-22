<div>
    <x-header title="Edit Kurir" separator progress-indicator>
        <x-slot:subtitle>
            Perbarui informasi kurir
        </x-slot:subtitle>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Dasar section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Dasar" subtitle="Data identitas kurir" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-file label="Avatar (Opsional)" wire:model="avatar" accept="image/png, image/jpeg, image/jpg"
                    hint="Ukuran maksimal 2MB. Format: JPG, PNG">
                    <img src="{{ $currentAvatarUrl ? asset('storage/' . $currentAvatarUrl) : asset('images/Logo.png') }}"
                        class="h-40 rounded-lg" />
                </x-file>

                <x-input label="Kode Kurir" wire:model="formData.kode_kurir" readonly hint="Kode tidak dapat diubah"
                    icon="o-hashtag" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Nama Kurir" wire:model="formData.nama" placeholder="Contoh: Budi Santoso"
                        icon="o-user" required />
                    <x-input label="No. HP" wire:model="formData.no_hp" placeholder="Contoh: 08123456789" icon="o-phone"
                        required />

                    <x-input label="Email" type="email" wire:model="formData.email"
                        placeholder="Contoh: kurir@email.com" icon="o-envelope" hint="Opsional" />

                    <x-input label="Password" type="password" wire:model="formData.password"
                        placeholder="Kosongkan jika tidak ingin mengubah" icon="o-lock-closed"
                        hint="Isi jika ingin mengubah password" />
                </div>
            </x-card>
        </div>

        {{-- Alamat section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Alamat" subtitle="Informasi alamat lengkap" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-textarea label="Detail Alamat" wire:model="formData.detail_alamat"
                    placeholder="Jalan, nomor rumah, RT/RW" rows="2"
                    hint="Contoh: Jl. Merdeka No. 123, RT 01/RW 02" required />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select label="Kecamatan" wire:model.live="formData.kecamatan" :options="$kecamatanOptions"
                        placeholder="Pilih kecamatan" icon="o-map-pin" />

                    <x-select label="Kelurahan/Desa" wire:model="formData.kelurahan" :options="$kelurahanOptions"
                        placeholder="Pilih kelurahan"
                        hint="{{ empty($formData['kecamatan']) ? 'Pilih kecamatan dulu' : '' }}" icon="o-map"
                        :disabled="empty($formData['kecamatan'])" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Kabupaten/Kota" wire:model="formData.kabupaten_kota" placeholder="Kota Kendari"
                        disabled />

                    <x-input label="Provinsi" wire:model="formData.provinsi" placeholder="Sulawesi Tenggara" disabled />
                </div>
            </x-card>
        </div>

        {{-- Kendaraan & Area Layanan section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Kendaraan & Area Layanan" subtitle="Data kendaraan dan area yang dilayani"
                    size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Kolom 1: Informasi Kendaraan --}}
                    <x-select label="Jenis Kendaraan" wire:model.live="formData.jenis_kendaraan" icon="o-truck"
                        :options="[['id' => 'Motor', 'name' => 'Motor'], ['id' => 'Mobil', 'name' => 'Mobil']]" option-value="id" option-label="name" placeholder="Pilih jenis kendaraan" />

                    <x-input label="No. Kendaraan/Plat" wire:model="formData.no_kendaraan"
                        placeholder="Contoh: DT 1234 AB" icon="o-identification" />

                    {{-- Kolom 2: Area Layanan --}}
                    <x-choices label="Kecamatan Layanan" wire:model.live="coverageKecamatan" :options="$coverageKecamatanOptions"
                        icon="o-map-pin" hint="Pilih jenis kendaraan dulu" :disabled="empty($formData['jenis_kendaraan'])" searchable />

                    <x-choices label="Kelurahan/Desa Layanan" wire:model="coverageKelurahan" :options="$coverageKelurahanOptions"
                        icon="o-map" hint="Pilih kecamatan dulu" :disabled="empty($coverageKecamatan)" searchable />
                </div>
            </x-card>
        </div>

        {{-- Status & Informasi Tambahan section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Status & Informasi Tambahan" subtitle="Status dan statistik pengiriman"
                    size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-datetime label="Tanggal Bergabung" wire:model="formData.tanggal_bergabung" icon="o-calendar"
                        required />

                    <x-select label="Status" wire:model="formData.status" icon="o-check-circle" :options="[['id' => 'Aktif', 'name' => 'Aktif'], ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif']]"
                        option-value="id" option-label="name" required />

                    <x-input label="Total Jemput" wire:model="formData.total_jemput" type="number"
                        icon="o-arrow-up-tray" hint="Auto-increment saat jemput" disabled />

                    <x-input label="Total Antar" wire:model="formData.total_antar" type="number"
                        icon="o-arrow-down-tray" hint="Auto-increment saat antar" disabled />
                </div>
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('kurir.index') }}" wire:navigate />
            <x-button label="Update" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>
