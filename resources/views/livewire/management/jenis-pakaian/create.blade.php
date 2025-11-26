<div>
    <x-header title="Tambah Jenis Pakaian Baru" separator progress-indicator>
        <x-slot:subtitle>
            Tambahkan jenis pakaian baru ke sistem
        </x-slot:subtitle>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Dasar section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Dasar" subtitle="Data identitas jenis pakaian" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Kode Jenis" wire:model="kode_jenis" placeholder="Auto Generate" readonly
                        hint="Kode dibuat otomatis" icon="o-hashtag" />

                    <x-input label="Nama Jenis Pakaian" wire:model="nama_jenis"
                        placeholder="Contoh: Kemeja, Celana Panjang" icon="o-square-3-stack-3d" required />
                </div>

                <x-textarea label="Keterangan" wire:model="keterangan"
                    placeholder="Jelaskan detail jenis pakaian ini..." rows="3" hint="Opsional" />

                <x-select label="Status" wire:model="status" icon="o-check-circle" :options="[
                        ['id' => 'Aktif', 'name' => 'Aktif'],
                        ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif']
                    ]" option-value="id" option-label="name" required />
            </x-card>
        </div>

        {{-- Informasi Tambahan section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Informasi Tambahan" subtitle="Metadata jenis pakaian (Opsional)" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <livewire:management.component.icon-picker :initialValue="$icon" eventName="iconSelected" label="Icon"
                    placeholder="Cari icon..." hint="Ketik minimal 2 huruf untuk mencari icon" />

                <x-textarea label="Penanganan Khusus" wire:model="penanganan_khusus"
                    placeholder="Contoh: Harus dicuci terpisah, gunakan pewangi khusus..." rows="3"
                    hint="Opsional - Instruksi khusus untuk pakaian putih atau baju spesial"
                    icon="o-information-circle" />
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('jenis-pakaian.index') }}" wire:navigate />
            <x-button label="Simpan" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>
