<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Riwayat Transaksi')]
#[Layout('layouts.pelanggan.app')]
class Riwayat extends Component
{
    public int $limit = 10;

    public bool $showAll = false;

    #[Computed]
    public function transaksiGrouped(): Collection
    {
        $pelanggan = auth('pelanggan')->user();

        if (! $pelanggan) {
            return collect();
        }

        $query = Transaksi::query()
            ->where('pelanggan_id', $pelanggan->id)
            ->with(['transaksiLayanan.layanan'])
            ->orderBy('tanggal_masuk', 'desc');

        if (! $this->showAll) {
            $query->limit($this->limit);
        }

        $transaksi = $query->get();

        return $this->groupTransaksiByPeriod($transaksi);
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    public function loadLess(): void
    {
        $this->limit = max(10, $this->limit - 10);
    }

    public function showAllTransaksi(): void
    {
        $this->showAll = true;
    }

    public function showLimitedTransaksi(): void
    {
        $this->showAll = false;
        $this->limit = 10;
    }

    protected function groupTransaksiByPeriod(Collection $transaksi): Collection
    {
        $now = Carbon::now();
        $grouped = collect();

        $periods = [
            'Hari Ini' => fn ($date) => $date->isToday(),
            'Minggu Ini' => fn ($date) => $date->isCurrentWeek() && ! $date->isToday(),
            'Bulan '.ucfirst($now->locale('id')->monthName) => fn ($date) => $date->isCurrentMonth() && ! $date->isCurrentWeek(),
        ];

        // Add previous months dynamically
        for ($i = 1; $i <= 3; $i++) {
            $monthDate = $now->copy()->subMonths($i);
            $monthName = 'Bulan '.ucfirst($monthDate->locale('id')->monthName);
            $periods[$monthName] = function ($date) use ($monthDate) {
                return $date->month === $monthDate->month && $date->year === $monthDate->year;
            };
        }

        foreach ($periods as $periodName => $filterFn) {
            $periodTransaksi = $transaksi->filter(function ($t) use ($filterFn) {
                return $filterFn(Carbon::parse($t->tanggal_masuk));
            });

            if ($periodTransaksi->isNotEmpty()) {
                $grouped->push([
                    'period' => $periodName,
                    'transaksi' => $periodTransaksi,
                ]);
            }
        }

        // Add "Lebih Lama" for older transactions
        $olderTransaksi = $transaksi->filter(function ($t) use ($now) {
            $date = Carbon::parse($t->tanggal_masuk);

            return $date->isBefore($now->copy()->subMonths(3));
        });

        if ($olderTransaksi->isNotEmpty()) {
            $grouped->push([
                'period' => 'Lebih Lama',
                'transaksi' => $olderTransaksi,
            ]);
        }

        return $grouped;
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.riwayat');
    }
}
