<div wire:poll.visible.30s>
    <!-- HEADER -->
    <x-header title="Transaksi" icon="o-clipboard-document-list"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" subtitle="Riwayat & Manajemen Pesanan"
        separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-input placeholder="Cari transaksi..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Tambah Transaksi" link="{{ route('transaksi.create') }}" wire:navigate.hover responsive
                icon="o-plus" class="btn-success" />
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card class="shadow-sm" body-class="border-t-2 border-accent border-dashed p-2" title="Transaksi Laundry"
        subtitle="Pantau semua pesanan pelanggan">
        <x-slot:menu>
            <div x-data="{ get selectedCount() { return $wire.selected.length } }" class="indicator">
                <template x-if="selectedCount > 0">
                    <span class="indicator-item badge badge-success badge-soft badge-sm" x-text="selectedCount"></span>
                </template>
                <x-button label="Export Excel" icon="o-document-arrow-down" class="btn-success btn-md"
                    wire:click="exportExcel" spinner responsive ::disabled="selectedCount === 0" />
            </div>
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$transaksi" :sort-by="$sortBy" wire:model="selected" selectable striped
            with-pagination per-page="perPage" :per-page-values="[5, 10, 25, 50]"
            link="{{ route('transaksi.edit', '[id]') }}">
            <x-slot:empty>
                <x-icon name="o-cube" label="Tidak ada data transaksi." />
            </x-slot:empty>

            @scope('cell_kasir', $item)
            <span class="truncate">{{ $item->kasir->name ?? '-' }}</span>
            @endscope

            @scope('cell_tanggal_masuk', $item)
            <span class="text-sm truncate">{{ $item->tanggal_masuk->format('d M Y H:i') }}</span>
            @endscope

            @scope('cell_nama_pelanggan', $item)
            <span class="truncate">{{ $item->nama_pelanggan }}</span>
            @endscope

            @scope('cell_layanan_info', $item)
            @if ($item->transaksiLayanan && $item->transaksiLayanan->count() > 0)
            @php
            $layananList = [];

            foreach ($item->transaksiLayanan as $tl) {
            $layananList[] = $tl->nama_layanan;
            }

            $layananText = implode(', ', $layananList);
            if (strlen($layananText) > 50) {
            $layananText = substr($layananText, 0, 47) . '...';
            }
            @endphp
            <span class="truncate" title="{{ $layananText }}">{{ $layananText }}</span>
            @elseif($item->layanan_id)
            {{-- Fallback for old single-layanan transactions --}}
            <span class="truncate">{{ $item->nama_layanan ?? '-' }}</span>
            @else
            <span class="text-base-content/50">-</span>
            @endif
            @endscope

            @scope('cell_total', $item)
            <span class="font-semibold text-success truncate">Rp
                {{ number_format((float) $item->total, 0, ',', '.') }}</span>
            @endscope

            @scope('cell_metode_pembayaran', $item)
            @if ($item->metode_pembayaran == 'Bayar Saat Jemput')
            <x-badge value="Jemput" class="badge-info badge-sm truncate" />
            @elseif($item->metode_pembayaran == 'Bayar Saat Antar')
            <x-badge value="Antar" class="badge-primary badge-sm truncate" />
            @else
            <x-badge value="{{ $item->metode_pembayaran ?? '-' }}" class="badge-accent badge-sm" />
            @endif
            @endscope

            @scope('cell_tipe_bayar', $item)
            @if ($item->tipe_bayar == 'Tunai')
            <x-badge value="Tunai" class="badge-success badge-sm truncate" />
            @elseif($item->tipe_bayar == 'Non-Tunai')
            <x-badge value="Non-Tunai" class="badge-secondary badge-sm truncate" />
            @else
            <span class="text-base-content/50">-</span>
            @endif
            @endscope

            @scope('cell_status', $item)
            @if ($item->status == 'Menunggu')
            <x-badge value="{{ $item->status }}" class="badge-warning badge-sm" />
            @elseif($item->status == 'Proses')
            <x-badge value="{{ $item->status }}" class="badge-info badge-sm" />
            @elseif($item->status == 'Selesai')
            <x-badge value="{{ $item->status }}" class="badge-success badge-sm" />
            @elseif($item->status == 'Diambil')
            <x-badge value="{{ $item->status }}" class="badge-secondary badge-sm" />
            @else
            <x-badge value="{{ $item->status }}" class="badge-error badge-sm" />
            @endif
            @endscope

            @scope('actions', $item)
            <div class="flex items-center justify-end gap-2">
                <x-button label="Edit" icon="o-pencil" link="{{ route('transaksi.edit', $item->id) }}"
                    wire:navigate.hover class="btn-sm btn-outline btn-info" />
                <x-button label="Hapus" icon="o-trash"
                    wire:click="confirmDelete({{ $item->id }}, '{{ $item->kode_transaksi }}')"
                    class="btn-sm btn-outline btn-error" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Transaksi" subtitle="Saring data sesuai kebutuhan" right separator
        with-close-button class="lg:w-1/3">
        <div class="space-y-5">
            @php
            $statusOptionsWithAll = array_merge([['id' => '', 'name' => 'Semua Status']], $statusOptions);
            $metodePembayaranOptionsWithAll = array_merge([['id' => '', 'name' => 'Semua Metode']], $metodePembayaranOptions);
            @endphp

            <x-select label="Status Transaksi" wire:model.live="statusFilter" icon="o-flag"
                :options="$statusOptionsWithAll" option-value="id" option-label="name" />

            <x-select label="Metode Pembayaran" wire:model.live="metodePembayaranFilter" icon="o-credit-card"
                :options="$metodePembayaranOptionsWithAll" option-value="id" option-label="name" />

            <div class="space-y-3">
                <label class="block text-sm font-semibold">Range Tanggal</label>
                <div class="grid grid-cols-1 gap-3">
                    <x-input label="Dari Tanggal" type="date" wire:model.live.debounce="tanggalMulai"
                        icon="o-calendar" />
                    <x-input label="Sampai Tanggal" type="date" wire:model.live.debounce="tanggalAkhir"
                        icon="o-calendar" />
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
                <p class="text-sm text-base-content mt-2">Apakah Anda yakin ingin menghapus transaksi <span
                        class="font-bold">{{ $deleteName }}</span> ?</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Hapus" wire:click="delete" spinner class="btn-error btn-block" icon="o-trash" />
        </x-slot:actions>
    </x-modal>
</div>
