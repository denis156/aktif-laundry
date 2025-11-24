<div wire:poll.visible.30s>
    <!-- HEADER -->
    <x-header title="Referral" icon="o-gift" icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8"
        subtitle="Kelola Kode Referral Pelanggan" separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-input placeholder="Cari referral..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Tambah Referral" link="{{ route('referral.create') }}" wire:navigate.hover responsive
                icon="o-plus" class="btn-success" />
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card class="shadow-sm" body-class="border-t-2 border-accent border-dashed p-2" title="Daftar Referral"
        subtitle="Kelola kode referral pelanggan">
        <x-table :headers="$headers" :rows="$referral" :sort-by="$sortBy" striped with-pagination per-page="perPage"
            :per-page-values="[5, 10, 25, 50]" link="{{ route('referral.edit', '[id]') }}">
            <x-slot:empty>
                <x-icon name="o-cube" label="Tidak ada data referral." />
            </x-slot:empty>

            @scope('cell_kode_referral', $item)
                <span class="font-mono font-semibold text-primary">{{ $item->kode_referral }}</span>
            @endscope

            @scope('cell_pelanggan', $item)
                <div class="flex flex-col">
                    <span class="font-medium">{{ $item->pelanggan->nama ?? '-' }}</span>
                    @if($item->pelanggan?->no_hp)
                        <span class="text-xs text-base-content/60">{{ $item->pelanggan->no_hp }}</span>
                    @endif
                </div>
            @endscope

            @scope('cell_promo_reward', $item)
                <div class="flex flex-col gap-1 text-xs">
                    {{-- Promo Referrer --}}
                    <div class="flex items-center gap-1">
                        <x-icon name="o-gift" class="w-3 h-3 text-success" />
                        <span class="text-base-content/60">Referrer:</span>
                        @if($item->promoReferrer)
                            <span class="font-medium text-success">{{ $item->promoReferrer->kode_promo }}</span>
                        @elseif($item->poin_referrer > 0)
                            <span class="font-medium">{{ $item->poin_referrer }} Poin</span>
                        @else
                            <span class="text-base-content/40">-</span>
                        @endif
                    </div>

                    {{-- Promo Referee --}}
                    <div class="flex items-center gap-1">
                        <x-icon name="o-ticket" class="w-3 h-3 text-info" />
                        <span class="text-base-content/60">Referee:</span>
                        @if($item->promoReferee)
                            <span class="font-medium text-info">{{ $item->promoReferee->kode_promo }}</span>
                        @elseif($item->diskon_referee > 0)
                            <span class="font-medium">{{ $item->diskon_referee }}%</span>
                        @else
                            <span class="text-base-content/40">-</span>
                        @endif
                    </div>

                    {{-- Min Transaksi --}}
                    @if($item->min_transaksi_referee)
                        <div class="flex items-center gap-1">
                            <x-icon name="o-banknotes" class="w-3 h-3" />
                            <span class="text-base-content/60">Min:</span>
                            <span>Rp {{ number_format($item->min_transaksi_referee, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            @endscope

            @scope('cell_statistik', $item)
                <div class="flex flex-col text-xs">
                    <div class="flex items-center gap-1">
                        <x-icon name="o-users" class="w-3 h-3" />
                        <span>Total: {{ $item->total_referral }}</span>
                    </div>
                    <div class="flex items-center gap-1 text-success">
                        <x-icon name="o-check-circle" class="w-3 h-3" />
                        <span>Berhasil: {{ $item->total_berhasil }}</span>
                    </div>
                    <div class="flex items-center gap-1 text-warning">
                        <x-icon name="o-star" class="w-3 h-3" />
                        <span>Poin: {{ $item->total_poin }}</span>
                    </div>
                </div>
            @endscope

            @scope('cell_status', $item)
                @if ($item->status == 'Aktif')
                    <x-badge value="{{ $item->status }}" class="badge-success badge-sm" />
                @else
                    <x-badge value="{{ $item->status }}" class="badge-error badge-sm" />
                @endif
            @endscope

            @scope('actions', $item)
                <div class="flex items-center justify-end gap-2">
                    <x-button label="Edit" icon="o-pencil" link="{{ route('referral.edit', $item->id) }}"
                        wire:navigate.hover class="btn-sm btn-outline btn-info" />
                    <x-button label="Hapus" icon="o-trash"
                        wire:click="confirmDelete({{ $item->id }}, '{{ $item->kode_referral }}')"
                        class="btn-sm btn-outline btn-error" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Referral" subtitle="Saring data sesuai kebutuhan" right separator
        with-close-button class="lg:w-1/3">
        <div class="space-y-5">
            <x-select label="Status Referral" wire:model.live="statusFilter" icon="o-funnel" :options="[
                ['id' => '', 'name' => 'Semua Status'],
                ['id' => 'Aktif', 'name' => 'Aktif'],
                ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif'],
            ]"
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
                <p class="text-sm text-base-content mt-2">Apakah Anda yakin ingin menghapus referral <span
                        class="font-bold">{{ $deleteName }}</span>?</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Hapus" wire:click="delete" spinner class="btn-error btn-block" icon="o-trash" />
        </x-slot:actions>
    </x-modal>
</div>
