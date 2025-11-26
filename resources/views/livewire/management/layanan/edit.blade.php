<div>
    <x-header title="Edit Layanan" separator progress-indicator>
        <x-slot:subtitle>
            Perbarui informasi layanan
        </x-slot:subtitle>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Dasar section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Dasar" subtitle="Data identitas layanan" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Kode Layanan" wire:model="formData.kode_layanan" readonly
                        hint="Kode tidak dapat diubah" icon="o-hashtag" />

                    <x-input label="Nama Layanan" wire:model="formData.nama_layanan" placeholder="Contoh: Cuci Express"
                        icon="o-sparkles" required />
                </div>

                <x-textarea label="Deskripsi" wire:model="formData.deskripsi"
                    placeholder="Jelaskan detail layanan ini..." rows="3" hint="Opsional, maksimal 200 karakter" />
            </x-card>
        </div>

        {{-- Tipe & Harga section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Tipe & Harga" subtitle="Pengaturan tipe dan harga layanan" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-group label="Tipe Layanan" wire:model.live="formData.tipe_layanan" :options="$tipeLayananOptions"
                    hint="Pilih tipe layanan sesuai kebutuhan" class="checked:btn-primary!" inline />


                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    @if ($formData['tipe_layanan'] === 'per_kg')
                    <x-input label="Harga per Kg" type="number" wire:model="formData.harga_per_kg"
                        placeholder="Contoh: 8000" prefix="Rp" required />
                    @else
                    <x-input label="Harga per Satuan" type="number" wire:model="formData.harga_per_satuan"
                        placeholder="Contoh: 25000" prefix="Rp" required />
                    <x-input label="Satuan" wire:model="formData.satuan" placeholder="Contoh: pcs"
                        hint="Contoh: pcs, lembar, item" />
                    @endif

                    <x-input label="Durasi (Jam)" type="number" wire:model="formData.durasi_jam"
                        placeholder="Contoh: 24" suffix="jam" required />
                    <x-select label="Status" wire:model="formData.status" icon="o-check-circle"
                        :options="[['id' => 'Aktif', 'name' => 'Aktif'], ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif']]"
                        option-value="id" option-label="name" required />
                </div>
            </x-card>
        </div>

        {{-- Informasi Tambahan section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Informasi Tambahan" subtitle="Metadata layanan (Opsional)" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Minimum Order" type="number" wire:model="formData.min_order" placeholder="Contoh: 2"
                        hint="Jumlah minimum order (opsional)"
                        suffix="{{ $formData['tipe_layanan'] === 'per_kg' ? 'kg' : 'pcs' }}" />
                    <x-input label="Maximum Order" type="number" wire:model="formData.max_order"
                        placeholder="Contoh: 50" hint="Jumlah maksimum order (opsional)"
                        suffix="{{ $formData['tipe_layanan'] === 'per_kg' ? 'kg' : 'pcs' }}" />
                </div>

                <div class="space-y-4 my-2">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium mb-2">Yang Termasuk dalam Layanan</span>
                        </label>
                        <livewire:management.component.string-list-input :initialValue="$formData['include']"
                            eventName="includeUpdated" placeholder="Contoh: Cuci bersih dengan detergen premium" />
                        <p class="text-xs opacity-70 mt-1">Daftar fasilitas atau hal yang termasuk dalam layanan ini
                        </p>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium mb-2">Yang Tidak Termasuk dalam Layanan</span>
                        </label>
                        <livewire:management.component.string-list-input :initialValue="$formData['exclude']"
                            eventName="excludeUpdated" placeholder="Contoh: Tidak termasuk pengharum pakaian" />
                        <p class="text-xs opacity-70 mt-1">Daftar hal yang tidak termasuk atau ada batasan dalam
                            layanan</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <livewire:management.component.icon-picker :initialValue="$formData['icon']"
                        eventName="iconSelected" label="Icon" placeholder="Cari icon..."
                        hint="Ketik minimal 2 huruf untuk mencari icon" />

                    <x-group label="Layanan Populer" wire:model="formData.is_popular" :options="$popularOptions"
                        hint="Layanan populer akan ditampilkan lebih menonjol" class="checked:btn-primary!" inline />
                </div>
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('layanan.index') }}" wire:navigate />
            <x-button label="Update" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>
