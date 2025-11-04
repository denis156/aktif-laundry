<div>
    <!-- HEADER -->
    <x-header title="Dashboard Aktif Laundry" icon="o-home" icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator progress-indicator>
        <x-slot:subtitle>
            <div class="flex items-center gap-2">
                <span class="text-secondary">{{ $currentDateTime }}</span>
            </div>
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-path" label="Refresh" class="btn-primary" wire:click="refreshDashboard" spinner="refreshDashboard" />
        </x-slot:actions>
    </x-header>

    <section class="space-y-6">
        <!-- STATISTICS -->
        <!-- Baris 1: Transaksi Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <x-stat title="Total Transaksi" description="Semua transaksi" value="{{ number_format($totalTransaksi) }}"
                icon="o-clipboard-document-list" color="text-primary" tooltip="Total semua transaksi" />

            <x-stat title="Transaksi Bulan Ini" description="{{ now()->locale('id')->isoFormat('MMMM YYYY') }}"
                value="{{ $transaksiBulanIni }}" icon="s-clipboard-document-list" color="text-info"
                tooltip="Transaksi bulan ini" />

            <x-stat title="Transaksi Hari Ini" description="{{ now()->locale('id')->isoFormat('D MMMM YYYY') }}"
                value="{{ $transaksiHariIni }}" icon="m-clipboard-document-list" color="text-success"
                tooltip="Transaksi hari ini" />
        </div>

        <!-- Baris 2: Pendapatan Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
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

        <!-- CALENDAR -->
        <x-card title="Kalender" subtitle="Pencatatan semua transaksi harian" class="shadow-sm" body-class="flex items-center justify-center">
            <x-calendar :events="$events" weekend-highlight months="4" :selected-date="now()->subMonths(3)->toDateString()" />
        </x-card>

        <!-- CHARTS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- BAR/LINE CHART -->
            <x-card title="7 Hari Terakhir" subtitle="Perbandingan transaksi terakhir minggu ini" class="shadow-sm col-span-2">
                <x-slot:menu>
                    <x-toggle
                        label="Diagram Garis"
                        wire:model.live="isLineChart"
                        hint="Aktifkan untuk diagram garis"
                        right />
                </x-slot:menu>
                <x-chart wire:model="transaksiChart" />
            </x-card>

            <!-- DONUT CHART -->
            <x-card title="Status" subtitle="Kondisi transaksi sedang aktif" class="shadow-sm col-span-1">
                <x-chart wire:model="statusChart" />
            </x-card>
        </div>

        <!-- MONTHLY CHART (12 Months) -->
        <x-card title="12 Bulan Terakhir" subtitle="Pertumbuhan transaksi selama tahun ini" class="shadow-sm">
            <x-slot:menu>
                <x-toggle
                    label="Diagram Garis"
                    wire:model.live="isLineChartMonthly"
                    hint="Aktifkan untuk diagram garis"
                    right />
                </x-slot:menu>
            <x-chart wire:model="monthlyChart" />
        </x-card>
    </section>
</div>
