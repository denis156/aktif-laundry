<div>
    {{-- HEADER --}}
    <x-header title="Dashboard" icon="o-home" icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8"
        separator progress-indicator>
        <x-slot:subtitle>
            <div class="flex items-center gap-2">
                <span class="text-secondary">{{ $currentDateTime }}</span>
            </div>
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-path" label="Refresh" class="btn-secondary btn-outline"
                wire:click="refreshDashboard" spinner="refreshDashboard" responsive />
            <x-button icon="o-calculator" label="Kasir" class="btn-primary" link="{{ route('kasir') }}"
                wire:navigate.hover responsive />
        </x-slot:actions>
    </x-header>

    <section class="space-y-6">
        {{-- DONUT STATUS & TRANSAKSI PIUTANG --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- DONUT CHART STATUS (1 KOLOM) --}}
            <x-card title="Status Transaksi" subtitle="Distribusi status semua transaksi" class="shadow-sm">
                <x-chart wire:model="statusChart" />
            </x-card>

            {{-- TRANSAKSI PIUTANG (2 KOLOM) --}}
            <x-card title="Transaksi Piutang" subtitle="Transaksi yang belum dibayar" class="shadow-sm md:col-span-2">
                <x-slot:menu>
                    <x-input placeholder="Cari pelanggan atau kode..." wire:model.live.debounce="searchPiutang"
                        clearable icon="o-magnifying-glass" class="input-sm w-64" />
                </x-slot:menu>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Kode Transaksi</th>
                                <th>Pelanggan</th>
                                <th>Kasir</th>
                                <th>Layanan</th>
                                <th class="text-right">Total Piutang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksiPiutang as $transaksi)
                            <tr class="cursor-pointer hover:bg-base-200"
                                @click="Livewire.navigate('{{ route('transaksi.edit', $transaksi->id) }}')">
                                <td>
                                    <div class="font-bold text-primary">{{ $transaksi->kode_transaksi }}</div>
                                    <div class="text-xs text-base-content/60">
                                        {{ $transaksi->tanggal_masuk->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="font-semibold">{{ $transaksi->nama_pelanggan }}</div>
                                </td>
                                <td>
                                    <span class="truncate">{{ $transaksi->kasir->name ?? '-' }}</span>
                                </td>
                                <td>
                                    @if ($transaksi->transaksiLayanan && $transaksi->transaksiLayanan->count() > 0)
                                    @php
                                    $layananList = [];
                                    foreach ($transaksi->transaksiLayanan as $tl) {
                                    $layananList[] = $tl->nama_layanan;
                                    }
                                    $layananText = implode(', ', $layananList);
                                    $layananFull = $layananText;
                                    if (strlen($layananText) > 30) {
                                    $layananText = substr($layananText, 0, 27) . '...';
                                    }
                                    @endphp
                                    <span class="truncate text-sm" title="{{ $layananFull }}">{{ $layananText }}</span>
                                    @else
                                    <span class="text-base-content/50 text-sm">-</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <span class="font-bold text-error">Rp
                                        {{ number_format($transaksi->total, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-base-content/50">
                                    Tidak ada transaksi piutang
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($transaksiPiutang->hasPages())
                <x-slot:actions>
                    <div class="w-full mary-table-pagination">
                        {{ $transaksiPiutang->links() }}
                    </div>
                </x-slot:actions>
                @endif
            </x-card>
        </div>

        {{-- STATISTICS CARD --}}
        <x-card title="Statistik Bisnis" subtitle="Ringkasan performa laundry secara menyeluruh" class="shadow-sm">
            <div class="space-y-4">
                {{-- STATISTICS - TOTAL TRANSAKSI & PENDAPATAN --}}
                <div class="stats stats-vertical lg:stats-horizontal shadow-lg w-full">
                    {{-- Total Transaksi --}}
                    <div class="stat place-items-center bg-linear-to-t from-secondary via-secondary/90 to-secondary/60">
                        <div class="stat-figure text-secondary-content">
                            <x-icon name="o-clipboard-document-list" class="w-8 h-8" />
                        </div>
                        <div class="stat-title text-secondary-content">Total Transaksi</div>
                        <div class="stat-value text-secondary-content">{{ number_format($totalTransaksi, 0, ',', '.') }}
                        </div>
                        <div class="stat-desc text-secondary-content/80">Total seluruh transaksi</div>
                    </div>

                    {{-- Total Pendapatan --}}
                    <div class="stat place-items-center bg-linear-to-t from-secondary via-secondary/90 to-secondary/60">
                        <div class="stat-figure text-secondary-content">
                            <x-icon name="o-banknotes" class="w-8 h-8" />
                        </div>
                        <div class="stat-title text-secondary-content">Total Pendapatan</div>
                        <div class="stat-value text-secondary-content">Rp {{
                            \App\Helper\NumberFormat::formatRupiah($totalPendapatan) }}</div>
                        <div class="stat-desc text-secondary-content/80">Total pendapatan & piutang</div>
                    </div>
                </div>

                {{-- STATISTICS - STATUS BAYAR & PENDAPATAN --}}
                <div class="stats stats-vertical lg:stats-horizontal shadow-lg w-full">
                    {{-- Transaksi Sudah Bayar --}}
                    <div class="stat bg-linear-to-br from-success via-success/85 to-success/70">
                        <div class="stat-figure text-success-content">
                            <x-icon name="o-check-circle" class="w-8 h-8" />
                        </div>
                        <div class="stat-title text-success-content">Transaksi Sudah Bayar</div>
                        <div class="stat-value text-success-content">{{ number_format($transaksiSudahBayar, 0, ',', '.')
                            }}</div>
                        <div class="stat-desc text-success-content/80">Transaksi lunas</div>
                    </div>

                    {{-- Transaksi Belum Bayar --}}
                    <div class="stat bg-linear-to-bl from-error via-error/85 to-error/70">
                        <div class="stat-figure text-error-content">
                            <x-icon name="o-exclamation-triangle" class="w-8 h-8" />
                        </div>
                        <div class="stat-title text-error-content">Transaksi Belum Bayar</div>
                        <div class="stat-value text-error-content">{{ number_format($transaksiBelumBayar, 0, ',', '.')
                            }}</div>
                        <div class="stat-desc text-error-content/80">Transaksi belum lunas</div>
                    </div>

                    {{-- Pendapatan --}}
                    <div class="stat bg-linear-to-br from-success via-success/85 to-success/70">
                        <div class="stat-figure text-success-content">
                            <x-icon name="o-check-circle" class="w-8 h-8" />
                        </div>
                        <div class="stat-title text-success-content">Pendapatan</div>
                        <div class="stat-value text-success-content">Rp {{
                            \App\Helper\NumberFormat::formatRupiah($totalPendapatanSudahBayar) }}</div>
                        <div class="stat-desc text-success-content/80">Transaksi yang sudah lunas</div>
                    </div>

                    {{-- Piutang --}}
                    <div class="stat bg-linear-to-bl from-error via-error/85 to-error/70">
                        <div class="stat-figure text-error-content">
                            <x-icon name="o-exclamation-triangle" class="w-8 h-8" />
                        </div>
                        <div class="stat-title text-error-content">Piutang</div>
                        <div class="stat-value text-error-content">Rp {{
                            \App\Helper\NumberFormat::formatRupiah($totalPendapatanBelumBayar) }}</div>
                        <div class="stat-desc text-error-content/80">Transaksi yang belum lunas</div>
                    </div>
                </div>

                {{-- STATISTICS - BULAN INI & HARI INI --}}
                <div class="stats stats-vertical lg:stats-horizontal shadow-lg w-full">
                    {{-- Pendapatan Bulan Ini --}}
                    <div class="stat bg-linear-to-br from-success to-success/70">
                        <div class="stat-figure text-success-content">
                            <x-icon name="o-calendar" class="w-8 h-8" />
                        </div>
                        <div class="stat-title text-success-content">Pendapatan Bulan Ini</div>
                        <div class="stat-value text-success-content">Rp {{
                            \App\Helper\NumberFormat::formatRupiah($pendapatanBulanIni) }}</div>
                        <div class="stat-desc text-success-content/80">Transaksi lunas bulan ini</div>
                    </div>

                    {{-- Piutang Bulan Ini --}}
                    <div class="stat bg-linear-to-br from-error to-error/70">
                        <div class="stat-figure text-error-content">
                            <x-icon name="o-calendar" class="w-8 h-8" />
                        </div>
                        <div class="stat-title text-error-content">Piutang Bulan Ini</div>
                        <div class="stat-value text-error-content">Rp {{
                            \App\Helper\NumberFormat::formatRupiah($piutangBulanIni) }}</div>
                        <div class="stat-desc text-error-content/80">Transaksi belum lunas bulan ini</div>
                    </div>

                    {{-- Pendapatan Hari Ini --}}
                    <div class="stat bg-linear-to-br from-success to-success/70">
                        <div class="stat-figure text-success-content">
                            <x-icon name="o-clock" class="w-8 h-8" />
                        </div>
                        <div class="stat-title text-success-content">Pendapatan Hari Ini</div>
                        <div class="stat-value text-success-content">Rp {{
                            \App\Helper\NumberFormat::formatRupiah($pendapatanHariIni) }}</div>
                        <div class="stat-desc text-success-content/80">Transaksi lunas hari ini</div>
                    </div>

                    {{-- Piutang Hari Ini --}}
                    <div class="stat bg-linear-to-br from-error to-error/70">
                        <div class="stat-figure text-error-content">
                            <x-icon name="o-clock" class="w-8 h-8" />
                        </div>
                        <div class="stat-title text-error-content">Piutang Hari Ini</div>
                        <div class="stat-value text-error-content">Rp {{
                            \App\Helper\NumberFormat::formatRupiah($piutangHariIni) }}</div>
                        <div class="stat-desc text-error-content/80">Transaksi belum lunas hari ini</div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- KALENDER & TOP 5 LAYANAN --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- CALENDAR (1 KOLOM) --}}
            <x-card title="Kalender Transaksi" subtitle="Pencatatan transaksi harian" class="shadow-sm"
                body-class="flex items-center justify-center">
                <x-calendar :events="$events" weekend-highlight months="1"
                    :selected-date="now()->startOfMonth()->toDateString()" />
            </x-card>

            {{-- TOP 5 LAYANAN (2 KOLOM) --}}
            <x-card title="Top 5 Layanan Terpopuler" subtitle="Layanan yang paling banyak digunakan"
                class="shadow-sm md:col-span-2">
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th class="text-center w-12">#</th>
                                <th>Nama Layanan</th>
                                <th class="text-right">Harga</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topLayanan as $index => $layanan)
                            <tr>
                                <td class="text-center">
                                    @if ($index === 0)
                                    <x-icon name="s-trophy" class="w-5 h-5 text-warning" />
                                    @elseif($index === 1)
                                    <x-icon name="s-trophy" class="w-5 h-5 text-base-content/40" />
                                    @elseif($index === 2)
                                    <x-icon name="s-trophy" class="w-5 h-5 text-warning" />
                                    @else
                                    <span class="font-bold text-base-content/50">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-bold">{{ $layanan->nama_layanan }}</div>
                                </td>
                                <td class="text-right">
                                    <span class="font-semibold text-success">Rp
                                        {{ number_format($layanan->harga, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    <x-badge value="{{ $layanan->total_transaksi }}x" class="badge-primary badge-sm" />
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-base-content/50">
                                    Belum ada data layanan
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        {{-- CHART TRANSAKSI - FULL WIDTH --}}
        <x-card title="{{ $this->getChartTitle() }}" subtitle="{{ $this->getChartSubtitle() }}" class="shadow-sm">
            <x-slot:menu>
                <div class="flex items-center gap-3">
                    @php
                    $chartTypeOptions = [
                    ['id' => 'line', 'name' => 'Line Chart'],
                    ['id' => 'area', 'name' => 'Area Chart'],
                    ['id' => 'bar', 'name' => 'Bar Chart'],
                    ];
                    @endphp
                    <x-input label="Dari Tanggal" type="date" wire:model.live="chartDateFrom" class="input-sm w-44" />
                    <x-input label="Sampai Tanggal" type="date" wire:model.live="chartDateTo" class="input-sm w-44" />
                    <x-select label="Tipe" :options="$chartTypeOptions" wire:model.live="chartType"
                        class="select-sm w-40" />
                </div>
            </x-slot:menu>
            <x-chart wire:model="transaksiChart" />
        </x-card>
    </section>
</div>
