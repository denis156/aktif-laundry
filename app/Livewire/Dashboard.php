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
                    'borderWidth' => 1,
                ]
            ]
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'aspectRatio' => 2,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
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
            'aspectRatio' => 1,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
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
                ]
            ]
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'aspectRatio' => 2,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
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
        $this->currentDateTime = now()->locale('id')->isoFormat('dddd, D MMMM YYYY - HH:mm:ss');
    }

    public function loadChartData()
    {
        // Get last 7 days transactions
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Transaksi::whereDate('tanggal_masuk', $date)->count();

            $last7Days->push([
                'label' => $date->locale('id')->isoFormat('ddd, D MMM'),
                'count' => $count,
            ]);
        }

        $this->transaksiChart['data']['labels'] = $last7Days->pluck('label')->toArray();
        $this->transaksiChart['data']['datasets'][0]['data'] = $last7Days->pluck('count')->toArray();
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
            $count = Transaksi::whereMonth('tanggal_masuk', $date->month)
                ->whereYear('tanggal_masuk', $date->year)
                ->count();

            $last12Months->push([
                'label' => $date->locale('id')->isoFormat('MMM YYYY'),
                'count' => $count,
            ]);
        }

        $this->monthlyChart['data']['labels'] = $last12Months->pluck('label')->toArray();
        $this->monthlyChart['data']['datasets'][0]['data'] = $last12Months->pluck('count')->toArray();
    }

    public function calendarEvents()
    {
        // Get transactions for last 4 months (including this month)
        $startDate = now()->subMonths(3)->startOfMonth();
        $endDate = now()->endOfMonth();

        $transaksi = Transaksi::with('pelanggan')
            ->whereBetween('tanggal_masuk', [$startDate, $endDate])
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
                'css' => '!bg-primary',
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

        return view('livewire.dashboard', [
            'totalTransaksi' => $totalTransaksi,
            'transaksiBulanIni' => $transaksiBulanIni,
            'transaksiHariIni' => $transaksiHariIni,
            'totalPendapatan' => $totalPendapatan,
            'pendapatanBulanIni' => $pendapatanBulanIni,
            'pendapatanHariIni' => $pendapatanHariIni,
            'events' => $this->calendarEvents(),
        ]);
    }
}
