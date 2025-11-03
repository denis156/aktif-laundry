<?php

namespace App\Livewire;

use App\Models\Transaksi;
use App\Models\Pelanggan;
use App\Models\Layanan;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

#[Title('Dashboard')]
class Welcome extends Component
{
    use Toast;

    public string $currentDateTime = '';

    // Chart data
    public array $transaksiChart = [
        'type' => 'bar',
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
            'plugins' => [
                'legend' => [
                    'display' => true,
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

    public function calendarEvents()
    {
        // Get transactions for this month
        $transaksi = Transaksi::with('pelanggan')
            ->whereMonth('tanggal_masuk', now()->month)
            ->whereYear('tanggal_masuk', now()->year)
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
        // Statistics
        $totalTransaksi = Transaksi::count();
        $transaksiHariIni = Transaksi::whereDate('tanggal_masuk', today())->count();
        $totalPelanggan = Pelanggan::where('status', 'Aktif')->count();
        $totalLayanan = Layanan::where('status', 'Aktif')->count();

        // Pendapatan bulan ini
        $pendapatanBulanIni = Transaksi::whereMonth('tanggal_masuk', now()->month)
            ->whereYear('tanggal_masuk', now()->year)
            ->sum('total');

        // Transaksi menunggu
        $transaksiMenunggu = Transaksi::where('status', 'Menunggu')->count();

        return view('livewire.welcome', [
            'totalTransaksi' => $totalTransaksi,
            'transaksiHariIni' => $transaksiHariIni,
            'totalPelanggan' => $totalPelanggan,
            'totalLayanan' => $totalLayanan,
            'pendapatanBulanIni' => $pendapatanBulanIni,
            'transaksiMenunggu' => $transaksiMenunggu,
            'events' => $this->calendarEvents(),
        ]);
    }
}
