<div>
    <x-header title="Tambah Pelanggan Baru" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Kembali" link="{{ route('pelanggan.index') }}" wire:navigate.hover icon="o-arrow-left" class="btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card class="max-w-4xl mx-auto shadow-sm">
        <x-form wire:submit="save">
            <div class="space-y-5">
                <!-- Informasi Dasar -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Kode Pelanggan"
                        wire:model="formData.kode_pelanggan"
                        placeholder="Auto Generate"
                        readonly
                        hint="Kode dibuat otomatis"
                        icon="o-hashtag"
                    />

                    <x-input
                        label="Nama Pelanggan"
                        wire:model="formData.nama"
                        placeholder="Contoh: Ahmad Rizki"
                        icon="o-user"
                        required
                    />
                </div>

                <!-- Kontak -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                </div>

                <!-- Alamat -->
                <x-textarea
                    label="Alamat"
                    wire:model="formData.alamat"
                    placeholder="Masukkan alamat lengkap pelanggan..."
                    rows="3"
                    required
                />

                <!-- Tanggal Daftar & Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Tanggal Daftar"
                        type="date"
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
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Batal" link="{{ route('pelanggan.index') }}" wire:navigate.hover class="btn-ghost" />
                <x-button label="Simpan" type="submit" spinner="save" class="btn-primary" icon="o-check" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
