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
        {{-- STATISTICS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            {{-- Baris 1: Transaksi Metrics --}}
            <x-stat title="Total Transaksi" description="Semua transaksi" value="{{ number_format($totalTransaksi) }}"
                icon="o-clipboard-document-list" color="text-primary" tooltip="Total semua transaksi" />

            <x-stat title="Transaksi Bulan Ini" description="{{ now()->locale('id')->isoFormat('MMMM YYYY') }}"
                value="{{ number_format($transaksiBulanIni) }}" icon="s-clipboard-document-list" color="text-info"
                tooltip="Transaksi bulan ini" />

            <x-stat title="Transaksi Hari Ini" description="{{ now()->locale('id')->isoFormat('D MMMM YYYY') }}"
                value="{{ number_format($transaksiHariIni) }}" icon="m-clipboard-document-list" color="text-success"
                tooltip="Transaksi hari ini" />

            {{-- Baris 2: Pendapatan Metrics --}}
            <x-stat title="Total Pendapatan" description="Semua pendapatan"
                value="Rp {{ number_format($totalPendapatan, 0, ',', '.') }}" icon="o-banknotes" color="text-primary"
                tooltip="Total semua pendapatan" />

            <x-stat title="Pendapatan Bulan Ini" description="{{ now()->locale('id')->isoFormat('MMMM YYYY') }}"
                value="Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}" icon="s-banknotes" color="text-info"
                tooltip="Pendapatan bulan ini" />

            <x-stat title="Pendapatan Hari Ini" description="{{ now()->locale('id')->isoFormat('D MMMM YYYY') }}"
                value="Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}" icon="m-banknotes" color="text-success"
                tooltip="Pendapatan hari ini" />
        </div>

        {{-- DONUT STATUS & TOP LAYANAN --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- DONUT CHART STATUS (1 KOLOM) --}}
            <x-card title="Status Transaksi" subtitle="Kondisi transaksi aktif" class="shadow-sm">
                <x-chart wire:model="statusChart" />
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

        {{-- KALENDER & TRANSAKSI TERAKHIR --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- CALENDAR (1 KOLOM) --}}
            <x-card title="Kalender Transaksi" subtitle="Pencatatan transaksi harian" class="shadow-sm"
                body-class="flex items-center justify-center">
                <x-calendar :events="$events" weekend-highlight months="1"
                    :selected-date="now()->startOfMonth()->toDateString()" />
            </x-card>

            {{-- 5 TRANSAKSI TERAKHIR (2 KOLOM) --}}
            <x-card title="5 Transaksi Terakhir" subtitle="Transaksi terbaru yang masuk"
                class="shadow-sm md:col-span-2">
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Kode Transaksi</th>
                                <th>Pelanggan</th>
                                <th class="text-right">Total Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransaksi as $transaksi)
                            <tr>
                                <td>
                                    <div class="font-bold text-primary">{{ $transaksi->kode_transaksi }}</div>
                                    <div class="text-xs text-base-content/60">
                                        {{ $transaksi->tanggal_masuk->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="font-semibold">{{ $transaksi->nama_pelanggan }}</div>
                                </td>
                                <td class="text-right">
                                    <span class="font-bold text-success">Rp
                                        {{ number_format($transaksi->total, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-base-content/50">
                                    Belum ada transaksi
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
                            ['id' => 'bar', 'name' => 'Bar Chart'],
                        ];
                    @endphp
                    <x-select label="Tipe" :options="$chartTypeOptions" wire:model.live="chartType" class="select-sm w-40" />
                    <x-select-group label="Periode" :options="$this->getPeriodOptions()" wire:model.live="chartPeriod"
                        class="select-sm w-64" />
                </div>
            </x-slot:menu>
            <x-chart wire:model="transaksiChart" />
        </x-card>
    </section>
</div>
