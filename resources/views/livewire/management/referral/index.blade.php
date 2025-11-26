<div wire:poll.visible.30s>
    <!-- HEADER -->
    <x-header title="Referral" icon="o-gift" icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8"
        subtitle="Kelola Kode Referral Pelanggan" separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-input placeholder="Cari referral..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Konfigurasi" link="{{ route('referral.pengaturan') }}" wire:navigate.hover responsive
                icon="o-cog" class="btn-accent" />
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
                    @else
                    <span class="text-base-content/40">-</span>
                    @endif
                </div>
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
