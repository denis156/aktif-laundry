<div>
    <x-header title="Tambah Layanan Baru" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Kembali" link="{{ route('layanan.index') }}" wire:navigate.hover icon="o-arrow-left" class="btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card class="max-w-4xl mx-auto shadow-sm">
        <x-form wire:submit="save">
            <div class="space-y-5">
                <!-- Informasi Dasar -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Kode Layanan"
                        wire:model="formData.kode_layanan"
                        placeholder="Auto Generate"
                        readonly
                        hint="Kode dibuat otomatis"
                        icon="o-hashtag"
                    />

                    <x-input
                        label="Nama Layanan"
                        wire:model="formData.nama_layanan"
                        placeholder="Contoh: Cuci Express"
                        icon="o-sparkles"
                        required
                    />
                </div>

                <!-- Harga & Durasi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Harga per Kg"
                        type="number"
                        wire:model="formData.harga_per_kg"
                        placeholder="Contoh: 8000"
                        prefix="Rp"
                        required
                    />

                    <x-input
                        label="Durasi (Jam)"
                        type="number"
                        wire:model="formData.durasi_jam"
                        placeholder="Contoh: 24"
                        suffix="jam"
                        required
                    />
                </div>

                <!-- Deskripsi -->
                <x-textarea
                    label="Deskripsi"
                    wire:model="formData.deskripsi"
                    placeholder="Jelaskan detail layanan ini..."
                    rows="3"
                    hint="Opsional, maksimal 200 karakter"
                />

                <!-- Status -->
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

            <x-slot:actions>
                <x-button label="Batal" link="{{ route('layanan.index') }}" wire:navigate.hover class="btn-ghost" />
                <x-button label="Simpan" type="submit" spinner="save" class="btn-primary" icon="o-check" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
