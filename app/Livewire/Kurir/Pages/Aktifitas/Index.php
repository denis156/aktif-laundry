<?php

declare(strict_types=1);

namespace App\Livewire\Kurir\Pages\Aktifitas;

use App\Helper\AvatarPlaceholder;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Aktifitas Kurir')]
#[Layout('layouts.kurir.app')]
class Index extends Component
{
    public int $limit = 10;

    public bool $showAll = false;

    #[Computed]
    public function totalTransaksi(): int
    {
        $kurir = auth('kurir')->user();

        if (! $kurir) {
            return 0;
        }

        return Transaksi::query()
            ->where(function ($query) use ($kurir) {
                $query->where('kurir_jemput_id', $kurir->id)
                    ->orWhere('kurir_antar_id', $kurir->id)
                    ->orWhere('status', 'Menunggu')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('status', 'Selesai')
                            ->whereNull('kurir_antar_id');
                    });
            })
            ->count();
    }

    #[Computed]
    public function transaksiGrouped(): Collection
    {
        $kurir = auth('kurir')->user();

        if (! $kurir) {
            return collect();
        }

        $query = Transaksi::query()
            ->where(function ($query) use ($kurir) {
                $query->where('kurir_jemput_id', $kurir->id)
                    ->orWhere('kurir_antar_id', $kurir->id)
                    ->orWhere('status', 'Menunggu')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('status', 'Selesai')
                            ->whereNull('kurir_antar_id');
                    });
            })
            ->with([
                'transaksiLayanan.layanan',
                'pelanggan',
                'kurirJemput',
                'kurirAntar',
            ])
            ->orderBy('tanggal_masuk', 'desc');

        if (! $this->showAll) {
            $query->limit($this->limit);
        }

        $transaksi = $query->get();

        return $this->groupTransaksiByPeriod($transaksi);
    }

    #[Computed]
    public function hasMoreData(): bool
    {
        return $this->limit < $this->totalTransaksi;
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

    /**
     * Get avatar URL with priority: pelanggan
     */
    public function getTransaksiAvatar(Transaksi $transaksi): string
    {
        return AvatarPlaceholder::getAvatarOrPlaceholder(
            $transaksi->pelanggan->avatar_url ?? null,
            $transaksi->pelanggan->nama ?? $transaksi->nama_pelanggan,
            256
        );
    }

    public function render(): mixed
    {
        return view('livewire.kurir.pages.aktifitas.index');
    }
}
