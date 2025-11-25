<?php

declare(strict_types=1);

namespace App\Livewire\Management;

use App\Helper\Dashboard\ChartDataHelper;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Dashboard')]
#[Layout('layouts.management.app')]
class Dashboard extends Component
{
    use Toast;

    public string $currentDateTime = '';

    public string $chartType = 'line'; // line, bar

    public string $chartPeriod = 'monthly'; // Format: weekly-YYYY-MM-DD, monthly-YYYY-MM, yearly-YYYY

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

    public function mount(): void
    {
        // Set default to current month
        $this->chartPeriod = 'monthly-'.now()->format('Y-m');
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
        // Parse period: {type}-{date}
        $parts = explode('-', $this->chartPeriod);
        $periodType = $parts[0]; // weekly, monthly, yearly

        // Load data based on selected period
        if ($periodType === 'weekly' && count($parts) === 4) {
            // Format: weekly-YYYY-MM-DD
            $date = Carbon::parse("{$parts[1]}-{$parts[2]}-{$parts[3]}");
            $data = ChartDataHelper::getWeekData($date);
        } elseif ($periodType === 'monthly' && count($parts) === 3) {
            // Format: monthly-YYYY-MM
            $date = Carbon::parse("{$parts[1]}-{$parts[2]}-01");
            $data = ChartDataHelper::getMonthData($date);
        } elseif ($periodType === 'yearly' && count($parts) === 2) {
            // Format: yearly-YYYY
            $date = Carbon::parse("{$parts[1]}-01-01");
            $data = ChartDataHelper::getYearData($date);
        } else {
            // Default to current month
            $data = ChartDataHelper::getMonthData(now());
        }

        // Set chart type
        $this->transaksiChart['type'] = $this->chartType;

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

    public function updatedChartType(): void
    {
        // Update chart type when selection changes
        $this->transaksiChart['type'] = $this->chartType;
    }

    public function updatedChartPeriod(): void
    {
        // Reload chart data when period changes
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
        // Parse period type
        $parts = explode('-', $this->chartPeriod);
        $periodType = $parts[0];

        if ($periodType === 'weekly' && count($parts) === 4) {
            $date = Carbon::parse("{$parts[1]}-{$parts[2]}-{$parts[3]}");
            $endDate = $date->copy()->addDays(6);

            return 'Transaksi Mingguan';
        } elseif ($periodType === 'monthly' && count($parts) === 3) {
            $date = Carbon::parse("{$parts[1]}-{$parts[2]}-01");

            return 'Transaksi Bulanan';
        } elseif ($periodType === 'yearly' && count($parts) === 2) {
            return 'Transaksi Tahunan';
        }

        return 'Grafik Transaksi';
    }

    public function getChartSubtitle(): string
    {
        // Parse period type
        $parts = explode('-', $this->chartPeriod);
        $periodType = $parts[0];

        if ($periodType === 'weekly' && count($parts) === 4) {
            $date = Carbon::parse("{$parts[1]}-{$parts[2]}-{$parts[3]}");
            $endDate = $date->copy()->addDays(6);

            return $date->locale('id')->isoFormat('D MMM').' - '.$endDate->locale('id')->isoFormat('D MMM YYYY');
        } elseif ($periodType === 'monthly' && count($parts) === 3) {
            $date = Carbon::parse("{$parts[1]}-{$parts[2]}-01");

            return 'Periode '.$date->locale('id')->isoFormat('MMMM YYYY');
        } elseif ($periodType === 'yearly' && count($parts) === 2) {
            return 'Periode Tahun '.$parts[1];
        }

        return 'Data transaksi laundry';
    }

    public function getPeriodOptions(): array
    {
        $options = [
            'Minggu' => [],
            'Bulan' => [],
            'Tahun' => [],
        ];

        // Generate last 8 weeks (including current week)
        for ($i = 0; $i < 8; $i++) {
            $date = now()->subWeeks($i)->startOfWeek();
            $endDate = $date->copy()->endOfWeek();
            $options['Minggu'][] = [
                'id' => 'weekly-'.$date->format('Y-m-d'),
                'name' => $date->locale('id')->isoFormat('D MMM').' - '.$endDate->locale('id')->isoFormat('D MMM YYYY'),
            ];
        }

        // Generate last 12 months (including current month)
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $options['Bulan'][] = [
                'id' => 'monthly-'.$date->format('Y-m'),
                'name' => $date->locale('id')->isoFormat('MMMM YYYY'),
            ];
        }

        // Generate last 5 years (including current year)
        for ($i = 0; $i < 5; $i++) {
            $year = now()->year - $i;
            $options['Tahun'][] = [
                'id' => 'yearly-'.$year,
                'name' => 'Tahun '.$year,
            ];
        }

        return $options;
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
