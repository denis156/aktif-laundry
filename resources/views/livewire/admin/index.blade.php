<div wire:poll.visible.30s>
    <!-- HEADER -->
    <x-header title="Daftar User" separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-input placeholder="Cari user..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Tambah User" link="{{ route('admin.create') }}" wire:navigate.hover responsive icon="o-plus" class="btn-success" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card class="shadow-sm">
        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" striped with-pagination per-page="perPage" :per-page-values="[5, 10, 25, 50]">
            <x-slot:empty>
                <x-icon name="o-cube" label="Tidak ada data user." />
            </x-slot:empty>

            @scope('cell_created_at', $user)
            <span class="text-sm">{{ $user->created_at->format('d M Y H:i') }}</span>
            @endscope

            @scope('actions', $user)
            <div class="flex items-center justify-end gap-2">
                <x-button
                    label="Edit"
                    icon="o-pencil"
                    link="{{ route('admin.edit', $user->id) }}"
                    wire:navigate.hover
                    class="btn-sm btn-soft btn-info"
                />
                <x-button
                    label="Hapus"
                    icon="o-trash"
                    wire:click="confirmDelete({{ $user->id }}, '{{ $user->name }}')"
                    class="btn-sm btn-soft btn-error"
                />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- DELETE CONFIRMATION MODAL -->
    <x-modal wire:model="deleteModal" box-class="max-w-md" class="modal-bottom sm:modal-middle backdrop-blur-md">
        <div class="text-center space-y-2">
            <div class="flex justify-center">
                <x-icon name="o-exclamation-triangle" class="w-18 h-18 p-4 bg-error rounded-full text-error-content" />
            </div>

            <div>
                <h3 class="text-lg font-bold text-error">Konfirmasi Hapus!</h3>
                <p class="text-sm text-base-content mt-2">Data yang sudah dihapus tidak dapat dikembalikan.</p>
                <p class="text-sm text-base-content mt-2">Apakah Anda yakin ingin menghapus user <span class="font-bold">{{ $deleteName }}</span> ?</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Hapus" wire:click="delete" spinner class="btn-error btn-block" icon="o-trash" />
        </x-slot:actions>
    </x-modal>
</div>
