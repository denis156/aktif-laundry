<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages;

use App\Helper\ChartData;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Dashboard')]
#[Layout('layouts.management.app')]
class Dashboard extends Component
{
    use Toast;
    use WithPagination;

    public string $currentDateTime = '';

    public string $chartType = 'line'; // line, bar, area

    public string $chartDateFrom = '';

    public string $chartDateTo = '';

    public string $searchPiutang = '';

    // Chart data - Line/Bar Chart
    public array $transaksiChart = [
        'type' => 'line',
        'data' => [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Transaksi',
                    'data' => [],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Berat (Kg)',
                    'data' => [],
                    'backgroundColor' => 'rgba(234, 179, 8, 0.5)',
                    'borderColor' => 'rgb(234, 179, 8)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Item Satuan',
                    'data' => [],
                    'backgroundColor' => 'rgba(168, 85, 247, 0.5)',
                    'borderColor' => 'rgb(168, 85, 247)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y1',
                ],
            ],
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'aspectRatio' => 2,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Transaksi',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Berat (Kg) / Item',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ],
    ];

    // Chart data - Donut Chart (Status)
    public array $statusChart = [
        'type' => 'doughnut',
        'data' => [
            'labels' => ['Menunggu', 'Proses', 'Pengerjaan', 'Selesai', 'Diambil', 'Batal'],
            'datasets' => [
                [
                    'label' => 'Status Transaksi',
                    'data' => [],
                    'backgroundColor' => [
                        'rgba(115, 115, 115, 0.85)',   // Secondary - Menunggu (Warm Gray oklch(45% 0.015 0))
                        'rgba(245, 158, 11, 0.85)',    // Warning - Proses (Warm Amber oklch(72% 0.18 80))
                        'rgba(59, 130, 246, 0.85)',    // Info - Pengerjaan (Trust Blue oklch(56% 0.15 240))
                        'rgba(34, 197, 94, 0.85)',     // Success - Selesai (Fresh Green oklch(58% 0.18 145))
                        'rgba(38, 38, 38, 0.85)',      // Neutral - Diambil (Pure Black oklch(0% 0 0) → darker gray)
                        'rgba(220, 38, 38, 0.85)',     // Error - Batal (Alert Red oklch(54% 0.2 25))
                    ],
                    'borderWidth' => 2,
                ],
            ],
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'aspectRatio' => 1.0,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 10,
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],
            ],
            'layout' => [
                'padding' => [
                    'top' => 15,
                    'bottom' => 10,
                    'left' => 15,
                    'right' => 15,
                ],
            ],
        ],
    ];

    public function mount(): void
    {
        // Set default to last 30 days
        $this->chartDateFrom = now()->subDays(29)->format('Y-m-d');
        $this->chartDateTo = now()->format('Y-m-d');
        $this->updateDateTime();
        $this->loadChartData();
        $this->loadStatusChart();
    }

    public function updateDateTime(): void
    {
        $this->currentDateTime = now()->isoFormat('dddd, D MMMM YYYY - HH:mm');
    }

    public function loadChartData(): void
    {
        // Validate date range
        if (empty($this->chartDateFrom) || empty($this->chartDateTo)) {
            return;
        }

        $from = Carbon::parse($this->chartDateFrom);
        $to = Carbon::parse($this->chartDateTo);

        // Get data for date range (swap handled in ChartData helper)
        $data = ChartData::getDateRangeData($from, $to);

        // Set chart type (area chart is line chart with fill)
        if ($this->chartType === 'area') {
            $this->transaksiChart['type'] = 'line';
            // Enable fill for area chart
            $this->transaksiChart['data']['datasets'][0]['fill'] = 'origin';
            $this->transaksiChart['data']['datasets'][1]['fill'] = 'origin';
            $this->transaksiChart['data']['datasets'][2]['fill'] = 'origin';
        } else {
            $this->transaksiChart['type'] = $this->chartType;
            // Disable fill for line and bar charts
            $this->transaksiChart['data']['datasets'][0]['fill'] = false;
            $this->transaksiChart['data']['datasets'][1]['fill'] = false;
            $this->transaksiChart['data']['datasets'][2]['fill'] = false;
        }

        $this->transaksiChart['data']['labels'] = $data->pluck('label')->toArray();
        $this->transaksiChart['data']['datasets'][0]['data'] = $data->pluck('count')->toArray();
        $this->transaksiChart['data']['datasets'][1]['data'] = $data->pluck('berat')->toArray();
        $this->transaksiChart['data']['datasets'][2]['data'] = $data->pluck('item')->toArray();
    }

    public function loadStatusChart(): void
    {
        // Get count by status (semua status transaksi)
        $statuses = ['Menunggu', 'Proses', 'Pengerjaan', 'Selesai', 'Diambil', 'Batal'];
        $statusCounts = [];

        foreach ($statuses as $status) {
            $statusCounts[] = Transaksi::where('status', $status)->count();
        }

        $this->statusChart['data']['datasets'][0]['data'] = $statusCounts;
    }

    public function updatedChartType(): void
    {
        // Reload chart to apply type changes (including fill for area chart)
        $this->loadChartData();
    }

    public function updatedChartDateFrom(): void
    {
        // Reload chart data when date range changes
        $this->loadChartData();
    }

    public function updatedChartDateTo(): void
    {
        // Reload chart data when date range changes
        $this->loadChartData();
    }

    public function refreshDashboard(): void
    {
        $this->updateDateTime();
        $this->loadChartData();
        $this->loadStatusChart();

        $this->success('Data berhasil diperbarui!', position: 'toast-bottom');
    }

    public function getChartTitle(): string
    {
        return 'Grafik Transaksi';
    }

    public function getChartSubtitle(): string
    {
        if (empty($this->chartDateFrom) || empty($this->chartDateTo)) {
            return 'Data transaksi laundry';
        }

        $from = Carbon::parse($this->chartDateFrom);
        $to = Carbon::parse($this->chartDateTo);

        return $from->locale('id')->isoFormat('D MMM YYYY').' - '.$to->locale('id')->isoFormat('D MMM YYYY');
    }

    public function calendarEvents(): array
    {
        // Get all transactions (last 60 days untuk performa)
        $transaksi = Transaksi::with('pelanggan')
            ->where('tanggal_masuk', '>=', now()->subDays(60))
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        $events = [];

        // Group by date
        $grouped = $transaksi->groupBy(function ($item) {
            return $item->tanggal_masuk->format('Y-m-d');
        });

        foreach ($grouped as $date => $items) {
            $count = $items->count();
            $events[] = [
                'label' => "{$count} Transaksi",
                'description' => $items->pluck('nama_pelanggan')->take(3)->implode(', ').($count > 3 ? '...' : ''),
                'css' => '!bg-neutral/28',
                'date' => Carbon::parse($date),
            ];
        }

        return $events;
    }

    public function render()
    {
        // Statistics - Total Transaksi
        $totalTransaksi = Transaksi::where('status', '!=', 'Batal')->count();
        $transaksiSudahBayar = Transaksi::where('status', '!=', 'Batal')
            ->where('status_bayar', 'Sudah Bayar')
            ->count();
        $transaksiBelumBayar = Transaksi::where('status', '!=', 'Batal')
            ->where('status_bayar', 'Belum Bayar')
            ->count();

        // Statistics - All Time (Pendapatan)
        $totalPendapatan = Transaksi::where('status', '!=', 'Batal')->sum('total');
        $totalPendapatanSudahBayar = Transaksi::where('status', '!=', 'Batal')
            ->where('status_bayar', 'Sudah Bayar')
            ->sum('total');
        $totalPendapatanBelumBayar = Transaksi::where('status', '!=', 'Batal')
            ->where('status_bayar', 'Belum Bayar')
            ->sum('total');

        // Statistics - Bulan Ini
        $pendapatanBulanIni = Transaksi::where('status', '!=', 'Batal')
            ->where('status_bayar', 'Sudah Bayar')
            ->whereMonth('tanggal_masuk', now()->month)
            ->whereYear('tanggal_masuk', now()->year)
            ->sum('total');
        $piutangBulanIni = Transaksi::where('status', '!=', 'Batal')
            ->where('status_bayar', 'Belum Bayar')
            ->whereMonth('tanggal_masuk', now()->month)
            ->whereYear('tanggal_masuk', now()->year)
            ->sum('total');

        // Statistics - Hari Ini
        $pendapatanHariIni = Transaksi::where('status', '!=', 'Batal')
            ->where('status_bayar', 'Sudah Bayar')
            ->whereDate('tanggal_masuk', now())
            ->sum('total');
        $piutangHariIni = Transaksi::where('status', '!=', 'Batal')
            ->where('status_bayar', 'Belum Bayar')
            ->whereDate('tanggal_masuk', now())
            ->sum('total');

        // Top 5 Layanan Terpopuler
        $topLayanan = DB::table('transaksi_layanan')
            ->select(
                'transaksi_layanan.layanan_id',
                'transaksi_layanan.nama_layanan',
                DB::raw('COUNT(DISTINCT transaksi_layanan.transaksi_id) as total_transaksi'),
                DB::raw('MAX(COALESCE(transaksi_layanan.harga_per_kg, transaksi_layanan.harga_per_satuan)) as harga')
            )
            ->whereNull('transaksi_layanan.deleted_at')
            ->groupBy('transaksi_layanan.layanan_id', 'transaksi_layanan.nama_layanan')
            ->orderBy('total_transaksi', 'desc')
            ->limit(5)
            ->get();

        // Transaksi Piutang (Belum Bayar) dengan pagination
        $transaksiPiutang = Transaksi::with(['pelanggan', 'transaksiLayanan', 'kasir'])
            ->where('status', '!=', 'Batal')
            ->where('status_bayar', 'Belum Bayar')
            ->when($this->searchPiutang, function ($query) {
                $query->where(function ($q) {
                    $q->where('kode_transaksi', 'like', "%{$this->searchPiutang}%")
                        ->orWhere('nama_pelanggan', 'like', "%{$this->searchPiutang}%");
                });
            })
            ->orderBy('tanggal_masuk', 'desc')
            ->paginate(5);

        return view('livewire.management.pages.dashboard', [
            'totalTransaksi' => $totalTransaksi,
            'transaksiSudahBayar' => $transaksiSudahBayar,
            'transaksiBelumBayar' => $transaksiBelumBayar,
            'totalPendapatan' => $totalPendapatan,
            'totalPendapatanSudahBayar' => $totalPendapatanSudahBayar,
            'totalPendapatanBelumBayar' => $totalPendapatanBelumBayar,
            'pendapatanBulanIni' => $pendapatanBulanIni,
            'piutangBulanIni' => $piutangBulanIni,
            'pendapatanHariIni' => $pendapatanHariIni,
            'piutangHariIni' => $piutangHariIni,
            'topLayanan' => $topLayanan,
            'transaksiPiutang' => $transaksiPiutang,
            'events' => $this->calendarEvents(),
        ]);
    }
}
