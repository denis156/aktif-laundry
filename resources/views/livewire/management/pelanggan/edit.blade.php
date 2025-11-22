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
                <x-file
                    label="Avatar (Opsional)"
                    wire:model="avatar"
                    accept="image/png, image/jpeg, image/jpg"
                    hint="Ukuran maksimal 2MB. Format: JPG, PNG"
                >
                    <img src="{{ $currentAvatarUrl ? asset('storage/' . $currentAvatarUrl) : asset('images/Logo.png') }}" class="h-40 rounded-lg" />
                </x-file>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Kode Pelanggan"
                        wire:model="formData.kode_pelanggan"
                        readonly
                        hint="Kode tidak dapat diubah"
                        icon="o-hashtag"
                    />

                    <x-input
                        label="Nama Pelanggan"
                        wire:model="formData.nama"
                        placeholder="Contoh: Ahmad Rizki"
                        icon="o-user"
                        required
                    />
                    <x-input
                        label="No. HP"
                        wire:model="formData.no_hp"
                        placeholder="Contoh: 08123456789"
                        icon="o-phone"
                        required
                    />

                    <x-input
                        label="Email"
                        type="email"
                        wire:model="formData.email"
                        placeholder="Contoh: pelanggan@email.com"
                        icon="o-envelope"
                        hint="Opsional"
                    />

                    <x-input
                        label="Password"
                        type="password"
                        wire:model="formData.password"
                        placeholder="Kosongkan jika tidak ingin mengubah"
                        icon="o-lock-closed"
                        hint="Isi jika ingin mengubah password"
                    />
                </div>
            </x-card>
        </div>

        {{-- Alamat section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Alamat" subtitle="Informasi alamat lengkap" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-textarea
                    label="Detail Alamat"
                    wire:model="formData.detail_alamat"
                    placeholder="Jalan, nomor rumah, RT/RW"
                    rows="2"
                    hint="Contoh: Jl. Merdeka No. 123, RT 01/RW 02"
                    required
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select
                        label="Kecamatan"
                        wire:model.live="formData.kecamatan"
                        :options="$kecamatanOptions"
                        placeholder="Pilih kecamatan"
                        icon="o-map-pin"
                    />

                    <x-select
                        label="Kelurahan/Desa"
                        wire:model="formData.kelurahan"
                        :options="$kelurahanOptions"
                        placeholder="Pilih kelurahan"
                        hint="{{ empty($formData['kecamatan']) ? 'Pilih kecamatan dulu' : '' }}"
                        icon="o-map"
                        :disabled="empty($formData['kecamatan'])"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Kabupaten/Kota"
                        wire:model="formData.kabupaten_kota"
                        placeholder="Kota Kendari"
                        disabled
                    />

                    <x-input
                        label="Provinsi"
                        wire:model="formData.provinsi"
                        placeholder="Sulawesi Tenggara"
                        disabled
                    />
                </div>
            </x-card>
        </div>

        {{-- Status & Informasi Tambahan section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Status & Informasi Tambahan" subtitle="Status dan informasi pelanggan" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-datetime
                        label="Tanggal Daftar"
                        type="datetime-local"
                        wire:model="formData.tanggal_daftar"
                        icon="o-calendar"
                        required
                    />

                    <x-select
                        label="Status"
                        wire:model="formData.status"
                        icon="o-check-circle"
                        :options="[
                            ['id' => 'Aktif', 'name' => 'Aktif'],
                            ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif']
                        ]"
                        option-value="id"
                        option-label="name"
                        required
                    />

                    <x-input
                        label="Total Transaksi"
                        wire:model="formData.total_transaksi"
                        type="number"
                        placeholder="0"
                        icon="o-shopping-bag"
                        hint="Auto-increment saat transaksi"
                        disabled
                    />

                    <x-input
                        label="Kode Referral Dipakai"
                        wire:model="formData.kode_referral_dipakai"
                        placeholder="Kode referral (jika ada)"
                        icon="o-ticket"
                        hint="Opsional"
                        disabled
                    />

                    <x-input
                        label="Direferensikan Oleh"
                        wire:model="formData.direferensikan_oleh"
                        placeholder="ID Pelanggan referrer"
                        icon="o-user-group"
                        hint="Auto-set dari kode referral"
                        disabled
                    />
                </div>
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('pelanggan.index') }}" wire:navigate />
            <x-button label="Update" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>
