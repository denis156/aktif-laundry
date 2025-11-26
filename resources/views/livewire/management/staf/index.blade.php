<div wire:poll.visible.30s>
    <!-- HEADER -->
    <x-header title="Staf" icon="o-user-group" icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8"
        subtitle="Manajemen Pengguna & Hak Akses" separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-input placeholder="Cari staf..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Tambah Staf" link="{{ route('staf.create') }}" wire:navigate.hover responsive icon="o-plus"
                class="btn-success" />
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card class="shadow-sm" body-class="border-t-2 border-accent border-dashed p-2" title="Data Staf"
        subtitle="Kelola akun pengguna sistem">
        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" striped with-pagination per-page="perPage"
            :per-page-values="[5, 10, 25, 50]" link="{{ route('staf.edit', '[id]') }}">
            <x-slot:empty>
                <x-icon name="o-cube" label="Tidak ada data staf." />
            </x-slot:empty>

            @scope('cell_avatar_url', $user)
            @php
                $avatarUrl = \App\Helper\AvatarPlaceholder::getAvatarOrPlaceholder($user->avatar_url, $user->name, 128);
            @endphp
            <div class="flex items-center justify-center">
                <img src="{{ $avatarUrl }}" alt="Avatar {{ $user->name }}"
                    class="w-10 h-10 rounded-full object-cover">
            </div>
            @endscope

            @scope('cell_name', $user)
            <div class="flex flex-col">
                <span class="font-semibold" title="{{ $user->name }}">
                    {{ Str::words($user->name, 3, '...') }}
                </span>
                <span class="text-xs text-gray-500">{{ $user->email }}</span>
            </div>
            @endscope

            @scope('cell_no_hp', $user)
            <span class="text-sm">{{ \App\Helper\PhoneNumber::formatLocal($user->no_hp) ?? '-' }}</span>
            @endscope

            @scope('cell_alamat', $user)
            <span class="text-sm" title="{{ $user->alamat ?? '-' }}">
                {{ Str::words($user->alamat ?? '-', 5, '...') }}
            </span>
            @endscope

            @scope('cell_jam_kerja', $user)
            @php
            $jamMasuk = \App\Helper\Database\UserHelper::getJamMasuk($user);
            $jamKeluar = \App\Helper\Database\UserHelper::getJamKeluar($user);
            @endphp
            @if($jamMasuk && $jamKeluar)
            <span class="text-sm">{{ $jamMasuk }} - {{ $jamKeluar }}</span>
            @else
            <span class="text-sm">-</span>
            @endif
            @endscope

            @scope('cell_gaji', $user)
            @php
            $gaji = \App\Helper\Database\UserHelper::getGaji($user);
            @endphp
            <span class="text-xs font-extrabold text-success">{{ $gaji ? 'Rp ' . number_format($gaji, 0, ',', '.') : '-'
                }}</span>
            @endscope

            @scope('cell_super_admin', $user)
            <div class="flex items-center justify-center">
                @if($user->super_admin)
                <x-icon name="o-check-circle" class="w-6 h-6 text-success" />
                @else
                <x-icon name="o-x-circle" class="w-6 h-6 text-error" />
                @endif
            </div>
            @endscope

            @scope('actions', $user)
            <div class="flex items-center justify-end gap-2">
                <x-button label="Edit" icon="o-pencil" link="{{ route('staf.edit', $user->id) }}" wire:navigate.hover
                    class="btn-sm btn-outline btn-info" />
                <x-button label="Hapus" icon="o-trash" wire:click="confirmDelete({{ $user->id }}, '{{ $user->name }}')"
                    class="btn-sm btn-outline btn-error" />
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
                <p class="text-sm text-base-content mt-2">Apakah Anda yakin ingin menghapus staf <span
                        class="font-bold">{{ $deleteName }}</span> ?</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Hapus" wire:click="delete" spinner class="btn-error btn-block" icon="o-trash" />
        </x-slot:actions>
    </x-modal>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Staf" subtitle="Saring data sesuai kebutuhan" right separator
        with-close-button class="lg:w-1/3">
        <div class="space-y-5">
            <x-select label="Role" wire:model.live="roleFilter" icon="o-funnel" :options="[
                ['id' => '', 'name' => 'Semua Role'],
                ['id' => '1', 'name' => 'Super Admin'],
                ['id' => '0', 'name' => 'Staf'],
            ]" option-value="id" option-label="name" />
        </div>

        <x-slot:actions>
            <x-button label="Reset Filter" icon="o-x-mark" wire:click="clear" spinner class="btn-outline btn-error" />
            <x-button label="Terapkan" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
