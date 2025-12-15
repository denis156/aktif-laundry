<div class="container mx-auto">
    <x-header title="Buat Pesanan" subtitle="Detail pesanan Anda disini" icon="iconpark.listview-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator />

    <div class="space-y-4 mb-24">
        <x-card title="Layanan" subtitle="Daftar layanan yang di pilih" class="shadow-lg border border-primary"
            body-class="space-y-2">
            <x-slot:menu>
                <x-button label="Tambah" link="{{ route('pesanan.pelanggan') }}"
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
                @if($this->isPromoEnabled())
                <x-select label="Kode Promo (Opsional)" wire:model.live="formData.promo_id" :options="$promoOptions"
                    option-value="id" option-label="name" placeholder="Pilih promo yang tersedia..."
                    hint="Diskon akan dihitung setelah barang ditimbang" />
                @endif

                {{-- Catatan section --}}
                <x-textarea label="Catatan (Opsional)" wire:model="formData.catatan"
                    placeholder="Tambahkan catatan khusus untuk pesanan Anda..." rows="3"
                    hint="Contoh: Jangan pakai pewangi, Ada noda membandel di baju putih, dll." />

                </x-card>
                {{-- Submit button --}}
                <x-slot:actions>
                    <div class="grid grid-cols-2 w-full gap-4">
                        <x-button label="Kembali" link="{{ route('pesanan.pelanggan') }}" wire:navigate
                            class="btn-secondary" />
                        <x-button label="Pesan" type="submit" class="btn-primary" spinner="submit" />
                    </div>
                </x-slot:actions>
        </x-form>
    </div>
</div>
