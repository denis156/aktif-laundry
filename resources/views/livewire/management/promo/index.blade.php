<div wire:poll.visible.10s>
    <!-- HEADER -->
    <x-header title="Promo" icon="o-tag" icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8"
        subtitle="Kelola Kode Promo & Diskon" separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-input placeholder="Cari promo..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Tambah Promo" link="{{ route('promo.create') }}" wire:navigate.hover responsive
                icon="o-plus" class="btn-success" />
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card class="shadow-sm" body-class="border-t-2 border-accent border-dashed p-2" title="Daftar Promo"
        subtitle="Kelola promo dan diskon">
        <x-table :headers="$headers" :rows="$promo" :sort-by="$sortBy" striped with-pagination per-page="perPage"
            :per-page-values="[5, 10, 25, 50]" link="{{ route('promo.edit', '[id]') }}">
            <x-slot:empty>
                <x-icon name="o-cube" label="Tidak ada data promo." />
            </x-slot:empty>

            @scope('cell_banner', $item)
            @if($item->banner_image)
            <img src="{{ Storage::url($item->banner_image) }}" alt="Banner {{ $item->nama_promo }}"
                class="w-full aspect-square object-contain rounded-lg bg-base-200 p-1" />
            @else
            <img src="{{ asset('images/Logo.png') }}" alt="Default Banner"
                class="w-full aspect-square object-contain rounded-lg bg-base-200 p-1" />
            @endif
            @endscope

            @scope('cell_kode_promo', $item)
            <span class="font-mono font-semibold text-primary">{{ $item->kode_promo }}</span>
            @endscope

            @scope('cell_nama_promo', $item)
            <div class="flex flex-col">
                <span class="font-medium">{{ $item->nama_promo }}</span>
                @if($item->deskripsi)
                <span class="text-xs text-base-content/60 truncate max-w-xs">{{ $item->deskripsi }}</span>
                @endif
            </div>
            @endscope

            @scope('cell_tipe_diskon', $item)
            @php
            $allTipeDiskonOptions = \App\Helper\Database\PromoHelper::getTipeDiskonOptions();
            $tipeDiskonOption = collect($allTipeDiskonOptions)->firstWhere('id', $item->tipe_diskon);
            $tipeLabel = $tipeDiskonOption['name'] ?? ucfirst($item->tipe_diskon);
            $badgeClass = match($item->tipe_diskon) {
            'persen' => 'badge-warning',
            'nominal' => 'badge-info',
            'gratis_kg', 'gratis_hari' => 'badge-success',
            'cashback' => 'badge-secondary',
            default => 'badge-neutral'
            };
            @endphp
            <span class="badge {{ $badgeClass }} badge-sm truncate">{{ $tipeLabel }}</span>
            @endscope

            @scope('cell_nilai_diskon', $item)
            <span class="font-semibold">{{ \App\Helper\Database\PromoHelper::formatNilaiDiskon($item->tipe_diskon,
                $item->nilai_diskon) }}</span>
            @if($item->diskon_maksimal)
            <div class="text-xs text-base-content/60">
                Max: Rp {{ number_format($item->diskon_maksimal, 0, ',', '.') }}
            </div>
            @endif
            @endscope

            @scope('cell_tanggal_mulai', $item)
            <div class="flex flex-col">
                <span class="font-medium text-sm">{{ $item->tanggal_mulai->format('d M Y') }}</span>
                <span class="text-xs text-base-content/60">{{ $item->tanggal_mulai->format('H:i') }}</span>
            </div>
            @endscope

            @scope('cell_tanggal_berakhir', $item)
            <div class="flex flex-col">
                <span class="font-medium text-sm">{{ $item->tanggal_berakhir->format('d M Y') }}</span>
                <span class="text-xs text-base-content/60">{{ $item->tanggal_berakhir->format('H:i') }}</span>
            </div>
            @endscope

            @scope('cell_kuota', $item)
            @if($item->kuota_total)
            <div class="flex flex-col items-center">
                <span class="text-sm font-medium">{{ $item->kuota_terpakai }}/{{ $item->kuota_total }}</span>
                <progress class="progress progress-primary w-16 h-2" value="{{ $item->kuota_terpakai }}"
                    max="{{ $item->kuota_total }}">
                </progress>
            </div>
            @else
            <span class="badge badge-outline badge-sm truncate">Unlimited</span>
            @endif
            @endscope

            @scope('cell_status', $item)
            @if ($item->status == 'Aktif')
            <x-badge value="{{ $item->status }}" class="badge-success badge-sm truncate" />
            @elseif ($item->status == 'Habis')
            <x-badge value="{{ $item->status }}" class="badge-warning badge-sm truncate" />
            @else
            <x-badge value="{{ $item->status }}" class="badge-error badge-sm truncate" />
            @endif
            @endscope

            @scope('actions', $item)
            <div class="flex items-center justify-end gap-2">
                <x-button label="Edit" icon="o-pencil" link="{{ route('promo.edit', $item->id) }}" wire:navigate.hover
                    class="btn-sm btn-outline btn-info" />
                <x-button label="Hapus" icon="o-trash"
                    wire:click="confirmDelete({{ $item->id }}, '{{ $item->nama_promo }}')"
                    class="btn-sm btn-outline btn-error" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Promo" subtitle="Saring data sesuai kebutuhan" right separator
        with-close-button class="lg:w-1/3">
        <div class="space-y-5">
            <x-select label="Status Promo" wire:model.live="statusFilter" icon="o-funnel" :options="[
                ['id' => '', 'name' => 'Semua Status'],
                ['id' => 'Aktif', 'name' => 'Aktif'],
                ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif'],
                ['id' => 'Habis', 'name' => 'Habis'],
            ]" option-value="id" option-label="name" />

            <x-select label="Tipe Diskon" wire:model.live="tipeDiskonFilter" icon="o-tag" :options="array_merge(
                [['id' => '', 'name' => 'Semua Tipe']],
                collect(\App\Helper\Database\PromoHelper::getTipeDiskonOptions())->map(fn($opt) => ['id' => $opt['id'], 'name' => $opt['name']])->toArray()
            )" option-value="id" option-label="name" />
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
                <p class="text-sm text-base-content mt-2">Apakah Anda yakin ingin menghapus promo <span
                        class="font-bold">{{ $deleteName }}</span>?</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Hapus" wire:click="delete" spinner class="btn-error btn-block" icon="o-trash" />
        </x-slot:actions>
    </x-modal>
</div>
