<div wire:poll.visible.30s>
    <!-- HEADER -->
    <x-header title="Layanan" icon="o-sparkles" icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8"
        subtitle="Jenis Cuci & Tarif Harga" separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-input placeholder="Cari layanan..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Tambah Layanan" link="{{ route('layanan.create') }}" wire:navigate.hover responsive
                icon="o-plus" class="btn-success" />
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card class="shadow-sm" body-class="border-t-2 border-accent border-dashed p-2" title="Daftar Layanan"
        subtitle="Atur jenis cuci dan harga">
        <x-table :headers="$headers" :rows="$layanan" :sort-by="$sortBy" striped with-pagination per-page="perPage"
            :per-page-values="[5, 10, 25, 50]" link="{{ route('layanan.edit', '[id]') }}">
            <x-slot:empty>
                <x-icon name="o-cube" label="Tidak ada data layanan." />
            </x-slot:empty>

            @scope('cell_icon', $item)
            @php
            $icon = \App\Helper\Database\LayananHelper::getIcon($item);
            @endphp
            @if($icon)
            <x-icon name="{{ $icon }}" class="w-6 h-6" />
            @else
            <x-icon name="o-sparkles" class="w-6 h-6 opacity-30" />
            @endif
            @endscope

            @scope('cell_nama_layanan', $item)
            <span class="font-medium">{{ $item->nama_layanan }}</span>
            @endscope

            @scope('cell_tipe_layanan', $item)
            @if ($item->tipe_layanan === 'per_kg')
            <span class="badge badge-primary badge-sm truncate">Per Kg</span>
            @else
            <span class="badge badge-warning badge-sm truncate">Per {{ ucfirst($item->satuan ?? 'pcs') }}</span>
            @endif
            @endscope

            @scope('cell_harga', $item)
            @if ($item->tipe_layanan === 'per_kg')
            <span class="font-semibold text-success truncate">Rp
                {{ number_format((float) $item->harga_per_kg, 0, ',', '.') }}/kg</span>
            @else
            <span class="font-semibold text-warning truncate">Rp
                {{ number_format((float) $item->harga_per_satuan, 0, ',', '.') }}/{{ $item->satuan ?? 'pcs' }}</span>
            @endif
            @endscope

            @scope('cell_durasi_jam', $item)
            <span class="badge badge-outline badge-sm">{{ $item->durasi_jam }} jam</span>
            @endscope

            @scope('cell_is_popular', $item)
            <div class="flex items-center justify-center">
                @php
                $isPopular = \App\Helper\Database\LayananHelper::isPopular($item);
                @endphp
                @if($isPopular)
                <x-icon name="o-check-circle" class="w-6 h-6 text-success" />
                @else
                <x-icon name="o-x-circle" class="w-6 h-6 text-error" />
                @endif
            </div>
            @endscope

            @scope('cell_status', $item)
            @if ($item->status == 'Aktif')
            <x-badge value="{{ $item->status }}" class="badge-success badge-sm" />
            @else
            <x-badge value="{{ $item->status }}" class="badge-error badge-sm truncate max-w-24" />
            @endif
            @endscope

            @scope('actions', $item)
            <div class="flex items-center justify-end gap-2">
                <x-button label="Edit" icon="o-pencil" link="{{ route('layanan.edit', $item->id) }}" wire:navigate.hover
                    class="btn-sm btn-outline btn-info" />
                <x-button label="Hapus" icon="o-trash"
                    wire:click="confirmDelete({{ $item->id }}, '{{ $item->nama_layanan }}')"
                    class="btn-sm btn-outline btn-error" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Layanan" subtitle="Saring data sesuai kebutuhan" right separator
        with-close-button class="lg:w-1/3">
        <div class="space-y-5">
            <x-select label="Status Layanan" wire:model.live="statusFilter" icon="o-funnel" :options="[
                ['id' => '', 'name' => 'Semua Status'],
                ['id' => 'Aktif', 'name' => 'Aktif'],
                ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif'],
            ]" option-value="id" option-label="name" />

            <div class="space-y-3">
                <label class="block text-sm font-semibold">Range Harga per Kg</label>
                <div class="grid grid-cols-2 gap-3">
                    <x-input label="Minimal" type="number" wire:model.live.debounce="minHarga" placeholder="0"
                        prefix="Rp" />
                    <x-input label="Maksimal" type="number" wire:model.live.debounce="maxHarga" placeholder="999999"
                        prefix="Rp" />
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Reset Filter" icon="o-x-mark" wire:click="clear" spinner class="btn-outline btn-error" />
            <x-button label="Terapkan" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>

    <!-- DELETE CONFIRMATION MODAL -->
    <x-modal wire:model="deleteModal" box-class="max-w-md" class="modal-bottom sm:modal-middle backdrop-blur-md">
        <div class="text-center space-y-2">
            <div class="flex justify-center">
                <x-icon name="o-exclamation-triangle" class="w-18 h-18 p-4 bg-error rounded-full text-error-content" />
            </div>

            <div>
                <h3 class="text-lg font-bold text-error">Konfirmasi Hapus!</h3>
                <p class="text-sm text-base-content mt-2">Data yang sudah dihapus tidak dapat dikembalikan.</p>
                <p class="text-sm text-base-content mt-2">Apakah Anda yakin ingin menghapus layanan <span
                        class="font-bold">{{ $deleteName }}</span> ?</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Hapus" wire:click="delete" spinner class="btn-error btn-block" icon="o-trash" />
        </x-slot:actions>
    </x-modal>
</div>
