<div wire:poll.visible.10s>
    <!-- HEADER -->
    <x-header title="Data Kurir" icon="o-truck" icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8"
        subtitle="Kelola data kurir dan informasi pengiriman" separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-input placeholder="Cari kurir..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Tambah Kurir" link="{{ route('kurir.create') }}" wire:navigate.hover responsive
                icon="o-plus" class="btn-success" />
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card class="shadow-sm" body-class="border-t-2 border-accent border-dashed p-2" title="Data Kurir"
        subtitle="Kelola informasi kurir dan pengiriman">
        <x-table :headers="$headers" :rows="$kurir" :sort-by="$sortBy" striped with-pagination per-page="perPage"
            :per-page-values="[5, 10, 25, 50]" link="{{ route('kurir.edit', '[id]') }}">
            <x-slot:empty>
                <x-icon name="o-cube" label="Tidak ada data kurir." />
            </x-slot:empty>

            @scope('cell_avatar', $item)
            @php
            $avatarUrl = \App\Helper\AvatarPlaceholder::getAvatarOrPlaceholder($item->avatar_url, $item->nama, 128);
            @endphp
            <div class="flex items-center justify-center">
                <img src="{{ $avatarUrl }}" alt="Avatar {{ $item->nama }}" class="w-10 h-10 rounded-full object-cover">
            </div>
            @endscope

            @scope('cell_no_hp', $item)
            <span class="text-sm">{{ \App\Helper\PhoneNumber::formatLocal($item->no_hp) ?? '-' }}</span>
            @endscope

            @scope('cell_alamat', $item)
            <span class="text-xs">{{ \App\Helper\Database\KurirHelper::getAlamatLengkap($item) }}</span>
            @endscope

            @scope('cell_jenis_kendaraan', $item)
            @if($item->jenis_kendaraan)
            <span class="badge badge-outline badge-sm">{{ $item->jenis_kendaraan }}</span>
            @if($item->no_kendaraan)
            <div class="text-xs text-base-content/60">{{ $item->no_kendaraan }}</div>
            @endif
            @else
            <span class="text-base-content/40">-</span>
            @endif
            @endscope

            @scope('cell_status', $item)
            @if ($item->status === 'Aktif')
            <x-badge value="Aktif" class="badge-success badge truncate" />
            @elseif ($item->status === 'Cuti')
            <x-badge value="Cuti" class="badge-warning badge truncate" />
            @else
            <x-badge value="Tidak Aktif" class="badge-error badge truncate" />
            @endif
            @endscope

            @scope('actions', $item)
            <div class="flex items-center justify-end gap-2">
                <x-button label="Edit" icon="o-pencil" link="{{ route('kurir.edit', $item->id) }}" wire:navigate.hover
                    class="btn-sm btn-outline btn-info" />
                <x-button label="Hapus" icon="o-trash" wire:click="confirmDelete({{ $item->id }}, '{{ $item->nama }}')"
                    class="btn-sm btn-outline btn-error" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Kurir" subtitle="Saring data sesuai kebutuhan" right separator
        with-close-button class="lg:w-1/3">
        <div class="space-y-5">
            <x-select label="Status Kurir" wire:model.live="statusFilter" icon="o-funnel"
                :options="array_merge([['id' => '', 'name' => 'Semua Status']], $statusOptions)" option-value="id"
                option-label="name" />

            <x-select label="Jenis Kendaraan" wire:model.live="jenisKendaraanFilter" icon="o-truck"
                :options="array_merge([['id' => '', 'name' => 'Semua Kendaraan']], $jenisKendaraanOptions)"
                option-value="id" option-label="name" />
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
                <p class="text-sm text-base-content mt-2">Apakah Anda yakin ingin menghapus kurir <span
                        class="font-bold">{{ $deleteName }}</span> ?</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Hapus" wire:click="delete" spinner class="btn-error btn-block" icon="o-trash" />
        </x-slot:actions>
    </x-modal>
</div>
