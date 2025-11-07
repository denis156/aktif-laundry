<div>
    <x-header title="Edit Layanan" separator progress-indicator>
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
                        readonly
                        hint="Kode tidak dapat diubah"
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

                <!-- Tipe Layanan -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Tipe Layanan</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 cursor-pointer border border-base-300 rounded-lg p-4 hover:bg-base-200 transition {{ $formData['tipe_layanan'] == 'per_kg' ? 'bg-primary/10 border-primary' : '' }}">
                            <input
                                type="radio"
                                value="per_kg"
                                wire:model.live="formData.tipe_layanan"
                                class="radio radio-primary" />
                            <div>
                                <span class="label-text font-medium">Per Kilogram (Kg)</span>
                                <p class="text-xs opacity-70">Untuk pakaian, bahan, dll yang dihitung per berat</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer border border-base-300 rounded-lg p-4 hover:bg-base-200 transition {{ $formData['tipe_layanan'] == 'per_satuan' ? 'bg-primary/10 border-primary' : '' }}">
                            <input
                                type="radio"
                                value="per_satuan"
                                wire:model.live="formData.tipe_layanan"
                                class="radio radio-primary" />
                            <div>
                                <span class="label-text font-medium">Per Satuan (Pieces)</span>
                                <p class="text-xs opacity-70">Untuk bed cover, karpet, gorden, dll</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Harga & Durasi - Dynamic based on tipe layanan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($formData['tipe_layanan'] === 'per_kg')
                        <x-input
                            label="Harga per Kg"
                            type="number"
                            wire:model="formData.harga_per_kg"
                            placeholder="Contoh: 8000"
                            prefix="Rp"
                            required
                        />
                    @else
                        <x-input
                            label="Harga per Satuan"
                            type="number"
                            wire:model="formData.harga_per_satuan"
                            placeholder="Contoh: 25000"
                            prefix="Rp"
                            required
                        />
                        <x-input
                            label="Satuan"
                            wire:model="formData.satuan"
                            placeholder="Contoh: pcs"
                            hint="Contoh: pcs, lembar, item"
                            required
                        />
                    @endif

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
                <x-button label="Update" type="submit" spinner="save" class="btn-primary" icon="o-check" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
