<?php

declare(strict_types=1);

namespace App\Livewire\Management;

use Carbon\Carbon;
use Mary\Traits\Toast;
use Livewire\Component;
use App\Models\Transaksi;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Helper\Dashboard\ChartDataHelper;

#[Title('Dashboard')]
#[Layout('layouts.management.app')]
class Dashboard extends Component
{
    use Toast;

    public string $currentDateTime = '';

    public bool $isLineChart = true;

    public bool $isLineChartMonthly = false;

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
            'labels' => ['Menunggu', 'Proses', 'Selesai'],
            'datasets' => [
                [
                    'label' => 'Status Transaksi',
                    'data' => [],
                    'backgroundColor' => [
                        'rgba(234, 179, 8, 0.8)',   // Warning - Menunggu
                        'rgba(59, 130, 246, 0.8)',  // Info - Proses
                        'rgba(34, 197, 94, 0.8)',   // Success - Selesai
                    ],
                    'borderWidth' => 2,
                ],
            ],
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'aspectRatio' => 1.3,
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
                    'left' => 25,
                    'right' => 25,
                ],
            ],
        ],
    ];

    // Chart data - Monthly Chart (12 months)
    public array $monthlyChart = [
        'type' => 'bar',
        'data' => [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Transaksi',
                    'data' => [],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
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

    public function mount(): void
    {
        $this->updateDateTime();
        $this->loadChartData();
        $this->loadStatusChart();
        $this->loadMonthlyChart();
    }

    public function updateDateTime(): void
    {
        $this->currentDateTime = now()->isoFormat('dddd, D MMMM YYYY - HH:mm');
    }

    public function loadChartData(): void
    {
        // Use ChartDataHelper untuk get data 7 hari terakhir
        $data = ChartDataHelper::getLast7DaysData();

        $this->transaksiChart['data']['labels'] = $data->pluck('label')->toArray();
        $this->transaksiChart['data']['datasets'][0]['data'] = $data->pluck('count')->toArray();
        $this->transaksiChart['data']['datasets'][1]['data'] = $data->pluck('berat')->toArray();
        $this->transaksiChart['data']['datasets'][2]['data'] = $data->pluck('item')->toArray();
    }

    public function loadStatusChart(): void
    {
        // Get count by status (only Menunggu, Proses, Selesai)
        $statuses = ['Menunggu', 'Proses', 'Selesai'];
        $statusCounts = [];

        foreach ($statuses as $status) {
            $statusCounts[] = Transaksi::where('status', $status)->count();
        }

        $this->statusChart['data']['datasets'][0]['data'] = $statusCounts;
    }

    public function updatedIsLineChart(): void
    {
        // Update chart type when toggle changes
        $this->transaksiChart['type'] = $this->isLineChart ? 'line' : 'bar';
    }

    public function updatedIsLineChartMonthly(): void
    {
        // Update monthly chart type when toggle changes
        $this->monthlyChart['type'] = $this->isLineChartMonthly ? 'line' : 'bar';
    }

    public function loadMonthlyChart(): void
    {
        // Use ChartDataHelper untuk get data 12 bulan terakhir
        $data = ChartDataHelper::getLast12MonthsData();

        $this->monthlyChart['data']['labels'] = $data->pluck('label')->toArray();
        $this->monthlyChart['data']['datasets'][0]['data'] = $data->pluck('count')->toArray();
        $this->monthlyChart['data']['datasets'][1]['data'] = $data->pluck('berat')->toArray();
        $this->monthlyChart['data']['datasets'][2]['data'] = $data->pluck('item')->toArray();
    }

    public function refreshDashboard(): void
    {
        $this->updateDateTime();
        $this->loadChartData();
        $this->loadStatusChart();
        $this->loadMonthlyChart();

        $this->success('Data berhasil diperbarui!', position: 'toast-bottom');
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
        // Statistics Transaksi
        $totalTransaksi = Transaksi::count();
        $transaksiBulanIni = Transaksi::whereMonth('tanggal_masuk', now()->month)
            ->whereYear('tanggal_masuk', now()->year)
            ->count();
        $transaksiHariIni = Transaksi::whereDate('tanggal_masuk', today())->count();

        // Statistics Pendapatan
        $totalPendapatan = Transaksi::sum('total');
        $pendapatanBulanIni = Transaksi::whereMonth('tanggal_masuk', now()->month)
            ->whereYear('tanggal_masuk', now()->year)
            ->sum('total');
        $pendapatanHariIni = Transaksi::whereDate('tanggal_masuk', today())->sum('total');

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

        // 5 Transaksi Terakhir
        $recentTransaksi = Transaksi::with('pelanggan')
            ->orderBy('tanggal_masuk', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.management.dashboard', [
            'totalTransaksi' => $totalTransaksi,
            'transaksiBulanIni' => $transaksiBulanIni,
            'transaksiHariIni' => $transaksiHariIni,
            'totalPendapatan' => $totalPendapatan,
            'pendapatanBulanIni' => $pendapatanBulanIni,
            'pendapatanHariIni' => $pendapatanHariIni,
            'topLayanan' => $topLayanan,
            'recentTransaksi' => $recentTransaksi,
            'events' => $this->calendarEvents(),
        ]);
    }
}
