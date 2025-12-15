<div class="container mx-auto" wire:poll.visible.10s>
    <x-header title="Pilih Layanan" subtitle="Pilih layanan yang Anda butuhkan" icon="iconpark.listview-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator />

    @if (empty($layananList))
    <div class="text-center py-8">
        <x-icon name="iconpark.handlex-o" class="w-16 h-16 mx-auto text-base-content/40" />
        <p class="text-sm text-base-content/60 mt-2">Belum ada layanan tersedia</p>
    </div>
    @else
    <div class="space-y-4 mb-34">
        @foreach ($layananList as $layanan)
        <x-card wire:key="layanan-{{ $layanan['id'] }}" title="{{ $layanan['nama'] }}"
            subtitle="Rp {{ number_format($layanan['harga'], 0, ',', '.') }}/{{ $layanan['satuan'] }}"
            class="shadow-lg border border-b-4 border-r-4 {{ $layanan['border_class'] }}">
            <x-slot:menu>
                @if ($layanan['is_popular'])
                <x-badge class="badge-sm {{ $layanan['badge_class'] }}" value="Populer" />
                @endif
                @if ($layanan['icon'])
                <x-icon name="{{ $layanan['icon'] }}" class="h-10 {{ $layanan['text_class'] }}" />
                @else
                <x-icon name="o-sparkles" class="h-10 {{ $layanan['text_class'] }}" />
                @endif
            </x-slot:menu>

            <ul class="flex flex-col gap-2 text-xs">
                {{-- Estimasi waktu --}}
                <li class="{{ $layanan['text_class'] }}">
                    <x-icon name="iconpark.checkone-o"
                        class="w-4 h-4 me-2 inline-block {{ $layanan['text_class'] }}" />
                    <span>{{ \App\Helper\Database\LayananHelper::formatEstimasiWaktu($layanan['durasi_jam']) }}</span>
                </li>

                {{-- Deskripsi --}}
                @if ($layanan['deskripsi'])
                <li class="text-base-content/60">
                    <x-icon name="iconpark.info-o" class="w-4 h-4 me-2 inline-block text-base-content/60" />
                    <span>{{ $layanan['deskripsi'] }}</span>
                </li>
                @endif
            </ul>

            <x-slot:actions separator>
                <div class="w-full grid grid-cols-2 gap-2">
                    <x-button label="Detail" class="btn-sm btn-soft btn-neutral"
                        link="{{ route('detail-layanan.pelanggan', ['id' => $layanan['id']]) }}" />
                    @if ($this->isSelected($layanan['id']))
                    <x-button wire:click="toggleLayanan({{ $layanan['id'] }})" label="Batal"
                        class="btn-sm btn-error" />
                    @else
                    <x-button wire:click="toggleLayanan({{ $layanan['id'] }})" label="Gunakan"
                        class="btn-sm {{ $layanan['btn_class'] }}" />
                    @endif
                </div>
            </x-slot:actions>
        </x-card>
        @endforeach
    </div>
    @endif

    {{-- Fixed Bottom Button --}}
    @if (!empty($layananList) && count($selectedLayananIds) > 0)
    @php
    $buttonLabel = 'Lanjut (' . count($selectedLayananIds) . ' Layanan)';
    @endphp

    <div class="fab mb-18">
        <x-button wire:click="lanjutKeForm" :label="$buttonLabel" class="btn-primary btn-md"
            spinner="lanjutKeForm" />
    </div>
    @endif
</div>
