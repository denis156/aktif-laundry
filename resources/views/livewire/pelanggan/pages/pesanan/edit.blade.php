<div class="container mx-auto">
    <x-header title="Edit Pesanan" subtitle="Perbarui detail pesanan Anda" separator>
        <x-slot:actions>
            <x-button icon="iconpark.delete-o" class="btn-error btn-sm" label="Hapus"
                @click="$wire.confirmDeleteModal = true" responsive />
        </x-slot:actions>
    </x-header>

    <div class="space-y-4 mb-24">
        <x-card title="Layanan" subtitle="Daftar layanan yang di pilih" class="shadow-lg border border-primary"
            body-class="space-y-2">
            <x-slot:menu>
                <x-button label="Tambah" wire:click="tambahLayanan"
                    class="btn-circle btn-sm btn-link btn-success text-xs" />
            </x-slot:menu>
            @foreach ($layananOptions as $layanan)
            @if (in_array($layanan['id'], $selectedLayananIds))
            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                @if ($layanan['icon'])
                <x-icon name="{{ $layanan['icon'] }}" class="bg-primary/10 p-2 w-10 h-10 rounded-lg shrink-0" />
                @else
                <x-icon name="o-sparkles" class="bg-primary/10 p-2 w-10 h-10 rounded-lg shrink-0" />
                @endif

                <div class="flex-1 min-w-0">
                    <span class="font-semibold text-sm block">{{ $layanan['nama'] }}</span>
                    <span class="text-xs text-primary font-semibold">
                        Rp {{ number_format($layanan['harga'], 0, ',', '.') }}/{{ $layanan['satuan'] }}
                    </span>
                </div>

                <button wire:click="removeLayanan({{ $layanan['id'] }})" type="button"
                    class="btn btn-ghost btn-sm btn-circle shrink-0">
                    <x-icon name="o-trash" class="w-5 h-5 text-error" />
                </button>
            </div>
            @endif
            @endforeach
        </x-card>
        <x-form wire:submit="submit" no-separator>
            <x-card class="shadow-lg border border-primary" title="Pembayaran" subtitle="Pilih metode dan cara pembayaran" body-class="space-y-2">
                {{-- Pembayaran section --}}
                <x-radio label="Kapan Bayar?" wire:model="formData.metode_pembayaran"
                    :options="$metodePembayaranOptions" option-value="id" option-label="name" />

                <x-radio label="Cara Bayar?" wire:model="formData.tipe_bayar" :options="$tipeBayarOptions"
                    option-value="id" option-label="name" />

                {{-- Promo section --}}
                <x-select label="Kode Promo (Opsional)" wire:model.live="formData.promo_id" :options="$promoOptions"
                    option-value="id" option-label="name" placeholder="Pilih promo yang tersedia..."
                    hint="Diskon akan dihitung setelah barang ditimbang" />

                {{-- Catatan section --}}
                <x-textarea label="Catatan (Opsional)" wire:model="formData.catatan"
                    placeholder="Tambahkan catatan khusus untuk pesanan Anda..." rows="3"
                    hint="Contoh: Jangan pakai pewangi, Ada noda membandel di baju putih, dll." />

                </x-card>
                {{-- Submit button --}}
                <x-slot:actions>
                    <div class="grid grid-cols-2 gap-4 w-full">
                        <x-button label="Batal" link="{{ route('detail-pesanan.pelanggan', ['id' => $transaksi->id]) }}" wire:navigate
                            class="btn-secondary" />
                        <x-button label="Simpan" type="submit" class="btn-primary" spinner="submit" />
                    </div>
                </x-slot:actions>
        </x-form>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <x-modal wire:model="confirmDeleteModal" title="Konfirmasi Hapus Pesanan" class="modal-bottom w-full backdrop-blur" persistent>
        <div class="space-y-4">
            <div class="flex justify-center">
                <x-icon name="iconpark.delete-o" class="w-16 h-16 text-error" />
            </div>
            <p class="text-center text-base">Apakah Anda yakin ingin menghapus pesanan ini?</p>
            <p class="text-center text-sm text-base-content/60">Pesanan dengan kode <span class="font-bold">{{ $transaksi->kode_transaksi }}</span> akan dihapus secara permanen</p>
        </div>

        <x-slot:actions>
            <div class="grid grid-cols-2 gap-4 w-full">
                <x-button label="Batal" class="btn-ghost" @click="$wire.confirmDeleteModal = false" />
                <x-button label="Ya, Hapus" class="btn-error" wire:click="hapusPesanan" spinner="hapusPesanan" />
            </div>
        </x-slot:actions>
    </x-modal>
</div>
