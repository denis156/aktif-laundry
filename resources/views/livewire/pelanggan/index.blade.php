<div wire:poll.visible.30s>
    <!-- HEADER -->
    <x-header title="Daftar Pelanggan" separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-input placeholder="Cari pelanggan..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Tambah Pelanggan" link="{{ route('pelanggan.create') }}" wire:navigate.hover responsive icon="o-plus" class="btn-success" />
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card class="shadow-sm">
        <x-table :headers="$headers" :rows="$pelanggan" :sort-by="$sortBy" striped with-pagination per-page="perPage" :per-page-values="[5, 10, 25, 50]">
            <x-slot:empty>
                <x-icon name="o-cube" label="Tidak ada data pelanggan." />
            </x-slot:empty>

            @scope('cell_tanggal_daftar', $item)
            <span class="text-sm truncate" title="{{ \Carbon\Carbon::parse($item->tanggal_daftar)->format('d F Y H:i:s') }}">{{ \Carbon\Carbon::parse($item->tanggal_daftar)->format('d M Y H:i') }}</span>
            @endscope

            @scope('cell_total_transaksi', $item)
            <span class="badge badge-outline badge-sm">{{ $item->total_transaksi }}x</span>
            @endscope

            @scope('cell_status', $item)
            @if($item->status == 'Aktif')
                <x-badge value="{{ $item->status }}" class="badge-success badge-sm" />
            @else
                <x-badge value="{{ $item->status }}" class="badge-error badge-sm truncate max-w-24" />
            @endif
            @endscope

            @scope('actions', $item)
            <div class="flex items-center justify-end gap-2">
                <x-button
                    label="Edit"
                    icon="o-pencil"
                    link="{{ route('pelanggan.edit', $item->id) }}"
                    wire:navigate.hover
                    class="btn-sm btn-soft btn-info"
                />
                <x-button
                    label="Hapus"
                    icon="o-trash"
                    wire:click="confirmDelete({{ $item->id }}, '{{ $item->nama }}')"
                    class="btn-sm btn-soft btn-error"
                />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Pelanggan" subtitle="Saring data sesuai kebutuhan" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-5">
            <x-select
                label="Status Pelanggan"
                wire:model.live="statusFilter"
                icon="o-funnel"
                :options="[
                    ['id' => '', 'name' => 'Semua Status'],
                    ['id' => 'Aktif', 'name' => 'Aktif'],
                    ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif']
                ]"
                option-value="id"
                option-label="name"
            />
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
                <p class="text-sm text-base-content mt-2">Apakah Anda yakin ingin menghapus pelanggan <span class="font-bold">{{ $deleteName }}</span> ?</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Hapus" wire:click="delete" spinner class="btn-error btn-block" icon="o-trash" />
        </x-slot:actions>
    </x-modal>
</div>
