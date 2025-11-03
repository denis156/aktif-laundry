<div>
    <x-header title="Edit Jenis Pakaian" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Kembali" link="{{ route('jenis-pakaian.index') }}" wire:navigate.hover icon="o-arrow-left" class="btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card class="max-w-4xl mx-auto shadow-sm">
        <x-form wire:submit="save">
            <div class="space-y-5">
                <!-- Informasi Dasar -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Kode Jenis"
                        wire:model="formData.kode_jenis"
                        readonly
                        hint="Kode tidak dapat diubah"
                        icon="o-hashtag"
                    />

                    <x-input
                        label="Nama Jenis Pakaian"
                        wire:model="formData.nama_jenis"
                        placeholder="Contoh: Kemeja, Celana Panjang"
                        icon="o-square-3-stack-3d"
                        required
                    />
                </div>

                <!-- Keterangan -->
                <x-textarea
                    label="Keterangan"
                    wire:model="formData.keterangan"
                    placeholder="Jelaskan detail jenis pakaian ini..."
                    rows="3"
                    hint="Opsional"
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
                <x-button label="Batal" link="{{ route('jenis-pakaian.index') }}" wire:navigate.hover class="btn-ghost" />
                <x-button label="Update" type="submit" spinner="save" class="btn-primary" icon="o-check" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
