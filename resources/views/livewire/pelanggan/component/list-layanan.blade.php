<div class="w-full space-y-2">
    <div class="flex justify-between items-center">
        <h1 class="text-lg font-bold text-base-content/80 uppercase">Layanan Kami</h1>
    </div>

    @if ($layananList->isEmpty())
        <div class="text-center py-8">
            <x-icon name="iconpark.handlex-o" class="w-16 h-16 mx-auto text-base-content/40" />
            <p class="text-sm text-base-content/60 mt-2">Belum ada layanan tersedia</p>
        </div>
    @else
        <div class="w-full space-x-4 flex overflow-x-auto snap-x snap-mandatory no-scrollbar">
            @foreach ($layananList as $layanan)
                @php
                    // Tentukan warna berdasarkan index atau popular
                    $colorClass = $layanan['popular'] ? 'warning' : 'success';

                    // Ambil include items
                    $includeItems = $layanan['include'];
                    $visibleCount = min(count($includeItems), 4);
                    $remainingCount = count($includeItems) - $visibleCount;
                @endphp

                <x-card title="{{ $layanan['nama_layanan'] }}" subtitle="Rp {{ $layanan['harga'] }}/{{ $layanan['satuan'] }}"
                    class="w-[78dvw] shadow-lg border border-b-5 border-r-5 border-{{ $colorClass }} text-{{ $colorClass }} flex-none snap-start">
                    <x-slot:menu>
                        @if ($layanan['popular'])
                            <x-badge class="badge-sm badge-{{ $colorClass }}" value="Populer" />
                        @endif
                        @if (!empty($layanan['icon']))
                            <x-icon name="{{ $layanan['icon'] }}" class="h-10 text-{{ $colorClass }}" />
                        @else
                            <x-icon name="o-sparkles" class="h-10 text-{{ $colorClass }}" />
                        @endif
                    </x-slot:menu>

                    <ul class="flex flex-col gap-2 text-xs">
                        {{-- Estimasi waktu --}}
                        <li class="text-{{ $colorClass }}">
                            <x-icon name="iconpark.checkone-o"
                                class="w-4 h-4 me-2 inline-block text-{{ $colorClass }}" />
                            <span>{{ \App\Helper\Database\LayananHelper::formatEstimasiWaktu($layanan['durasi_jam']) }}</span>
                        </li>

                        {{-- Include items --}}
                        @foreach (array_slice($includeItems, 0, $visibleCount) as $item)
                            <li class="text-{{ $colorClass }}">
                                <x-icon name="iconpark.checkone-o"
                                    class="w-4 h-4 me-2 inline-block text-{{ $colorClass }}" />
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach

                        {{-- Tampilkan sisa item jika ada --}}
                        @if ($remainingCount > 0)
                            <li class="text-base-content/60">
                                <x-icon name="iconpark.more-o"
                                    class="w-4 h-4 me-2 inline-block text-base-content/60" />
                                <span>+{{ $remainingCount }} keuntungan lainnya</span>
                            </li>
                        @endif
                    </ul>

                    <x-slot:actions separator>
                        <div class="w-full grid grid-cols-2 gap-2">
                            <x-button label="Detail" class="btn-sm btn-soft btn-neutral" />
                            <x-button label="Pesan" class="btn-sm btn-{{ $colorClass }}" />
                        </div>
                    </x-slot:actions>
                </x-card>
            @endforeach
        </div>
    @endif
</div>
