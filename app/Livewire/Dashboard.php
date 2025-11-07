<?php

namespace App\Livewire;

use Carbon\Carbon;
use Mary\Traits\Toast;
use Livewire\Component;
use App\Models\Transaksi;
use Livewire\Attributes\Title;

#[Title('Dashboard')]
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
                ]
            ]
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
                        'text' => 'Jumlah Transaksi'
                    ]
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Berat (Kg) / Item'
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ]
            ]
        ]
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
                ]
            ]
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
                            'size' => 11
                        ]
                    ]
                ],
            ],
            'layout' => [
                'padding' => [
                    'top' => 15,
                    'bottom' => 10,
                    'left' => 25,
                    'right' => 25
                ]
            ]
        ]
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
                ]
            ]
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
                        'text' => 'Jumlah Transaksi'
                    ]
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Berat (Kg) / Item'
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ]
            ]
        ]
    ];

    public function mount()
    {
        $this->updateDateTime();
        $this->loadChartData();
        $this->loadStatusChart();
        $this->loadMonthlyChart();
    }

    public function updateDateTime()
    {
        $this->currentDateTime = now()->isoFormat('dddd, D MMMM YYYY - HH:mm');
    }

    public function loadChartData()
    {
        // Get last 7 days transactions
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);

            // Count transaksi
            $count = Transaksi::whereDate('tanggal_masuk', $date)->count();

            // Sum total berat from transaksi_layanan (per kg)
            $totalBerat = \DB::table('transaksi_layanan')
                ->join('transaksi', 'transaksi_layanan.transaksi_id', '=', 'transaksi.id')
                ->whereDate('transaksi.tanggal_masuk', $date)
                ->whereNull('transaksi_layanan.deleted_at')
                ->sum('transaksi_layanan.berat_kg');

            // Sum total item satuan from transaksi_layanan (per satuan)
            $totalItemSatuan = \DB::table('transaksi_layanan')
                ->join('transaksi', 'transaksi_layanan.transaksi_id', '=', 'transaksi.id')
                ->whereDate('transaksi.tanggal_masuk', $date)
                ->whereNull('transaksi_layanan.deleted_at')
                ->sum('transaksi_layanan.jumlah_satuan');

            $last7Days->push([
                'label' => $date->locale('id')->isoFormat('ddd, D MMM'),
                'count' => $count,
                'berat' => round($totalBerat, 1),
                'item' => (int) $totalItemSatuan,
            ]);
        }

        $this->transaksiChart['data']['labels'] = $last7Days->pluck('label')->toArray();
        $this->transaksiChart['data']['datasets'][0]['data'] = $last7Days->pluck('count')->toArray();
        $this->transaksiChart['data']['datasets'][1]['data'] = $last7Days->pluck('berat')->toArray();
        $this->transaksiChart['data']['datasets'][2]['data'] = $last7Days->pluck('item')->toArray();
    }

    public function loadStatusChart()
    {
        // Get count by status (only Menunggu, Proses, Selesai)
        $statuses = ['Menunggu', 'Proses', 'Selesai'];
        $statusCounts = [];

        foreach ($statuses as $status) {
            $statusCounts[] = Transaksi::where('status', $status)->count();
        }

        $this->statusChart['data']['datasets'][0]['data'] = $statusCounts;
    }

    public function updatedIsLineChart()
    {
        // Update chart type when toggle changes
        $this->transaksiChart['type'] = $this->isLineChart ? 'line' : 'bar';
    }

    public function updatedIsLineChartMonthly()
    {
        // Update monthly chart type when toggle changes
        $this->monthlyChart['type'] = $this->isLineChartMonthly ? 'line' : 'bar';
    }

    public function loadMonthlyChart()
    {
        // Get last 12 months transactions (including current month)
        $last12Months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            // Count transaksi
            $count = Transaksi::whereMonth('tanggal_masuk', $date->month)
                ->whereYear('tanggal_masuk', $date->year)
                ->count();

            // Sum total berat from transaksi_layanan (per kg)
            $totalBerat = \DB::table('transaksi_layanan')
                ->join('transaksi', 'transaksi_layanan.transaksi_id', '=', 'transaksi.id')
                ->whereMonth('transaksi.tanggal_masuk', $date->month)
                ->whereYear('transaksi.tanggal_masuk', $date->year)
                ->whereNull('transaksi_layanan.deleted_at')
                ->sum('transaksi_layanan.berat_kg');

            // Sum total item satuan from transaksi_layanan (per satuan)
            $totalItemSatuan = \DB::table('transaksi_layanan')
                ->join('transaksi', 'transaksi_layanan.transaksi_id', '=', 'transaksi.id')
                ->whereMonth('transaksi.tanggal_masuk', $date->month)
                ->whereYear('transaksi.tanggal_masuk', $date->year)
                ->whereNull('transaksi_layanan.deleted_at')
                ->sum('transaksi_layanan.jumlah_satuan');

            $last12Months->push([
                'label' => $date->locale('id')->isoFormat('MMM YYYY'),
                'count' => $count,
                'berat' => round($totalBerat, 1),
                'item' => (int) $totalItemSatuan,
            ]);
        }

        $this->monthlyChart['data']['labels'] = $last12Months->pluck('label')->toArray();
        $this->monthlyChart['data']['datasets'][0]['data'] = $last12Months->pluck('count')->toArray();
        $this->monthlyChart['data']['datasets'][1]['data'] = $last12Months->pluck('berat')->toArray();
        $this->monthlyChart['data']['datasets'][2]['data'] = $last12Months->pluck('item')->toArray();
    }

    public function refreshDashboard()
    {
        $this->updateDateTime();
        $this->loadChartData();
        $this->loadStatusChart();
        $this->loadMonthlyChart();

        $this->success('Data berhasil diperbarui!', position: 'toast-bottom');
    }

    public function calendarEvents()
    {
        // Get all transactions
        $transaksi = Transaksi::with('pelanggan')
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        $events = [];

        // Group by date
        $grouped = $transaksi->groupBy(function($item) {
            return $item->tanggal_masuk->format('Y-m-d');
        });

        foreach ($grouped as $date => $items) {
            $count = $items->count();
            $events[] = [
                'label' => "$count Transaksi",
                'description' => $items->pluck('pelanggan.nama')->take(3)->implode(', ') . ($count > 3 ? '...' : ''),
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
        $topLayanan = \DB::table('transaksi_layanan')
            ->select(
                'transaksi_layanan.layanan_id',
                'transaksi_layanan.nama_layanan',
                \DB::raw('COUNT(DISTINCT transaksi_layanan.transaksi_id) as total_transaksi'),
                \DB::raw('MAX(COALESCE(transaksi_layanan.harga_per_kg, transaksi_layanan.harga_per_satuan)) as harga')
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

        return view('livewire.dashboard', [
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
