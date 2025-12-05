<?php

declare(strict_types=1);

namespace App\Livewire\Kurir;

use App\Helper\AvatarPlaceholder;
use App\Models\Transaksi;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Beranda Kurir')]
#[Layout('layouts.kurir.app')]
class Beranda extends Component
{
    #[Computed]
    public function totalPengiriman(): int
    {
        $kurir = auth('kurir')->user();

        if (! $kurir) {
            return 0;
        }

        return Transaksi::query()
            ->where(function ($query) use ($kurir) {
                $query->where('kurir_jemput_id', $kurir->id)
                    ->orWhere('kurir_antar_id', $kurir->id);
            })
            ->count();
    }

    #[Computed]
    public function totalSelesai(): int
    {
        $kurir = auth('kurir')->user();

        if (! $kurir) {
            return 0;
        }

        return Transaksi::query()
            ->where('status', 'Diambil')
            ->where(function ($query) use ($kurir) {
                $query->where('kurir_jemput_id', $kurir->id)
                    ->orWhere('kurir_antar_id', $kurir->id);
            })
            ->count();
    }

    #[Computed]
    public function totalAntar(): int
    {
        $kurir = auth('kurir')->user();

        if (! $kurir) {
            return 0;
        }

        return Transaksi::query()
            ->where('kurir_antar_id', $kurir->id)
            ->whereIn('status', ['Selesai', 'Diambil'])
            ->count();
    }

    #[Computed]
    public function totalJemput(): int
    {
        $kurir = auth('kurir')->user();

        if (! $kurir) {
            return 0;
        }

        return Transaksi::query()
            ->where('kurir_jemput_id', $kurir->id)
            ->whereIn('status', ['Proses', 'Selesai', 'Diambil'])
            ->count();
    }

    #[Computed]
    public function pengantaranAktif(): Collection
    {
        $kurir = auth('kurir')->user();

        if (! $kurir) {
            return collect();
        }

        // Ambil transaksi dengan status Proses atau Selesai yang melibatkan kurir ini
        return Transaksi::query()
            ->where(function ($query) use ($kurir) {
                // Status Proses: kurir yang jemput
                $query->where(function ($q) use ($kurir) {
                    $q->where('status', 'Proses')
                        ->where('kurir_jemput_id', $kurir->id);
                })
                // Status Selesai: kurir yang antar
                    ->orWhere(function ($q) use ($kurir) {
                        $q->where('status', 'Selesai')
                            ->where('kurir_antar_id', $kurir->id);
                    });
            })
            ->with([
                'transaksiLayanan.layanan',
                'pelanggan',
                'kurirJemput',
                'kurirAntar',
            ])
            ->orderBy('tanggal_masuk', 'desc')
            ->limit(3)
            ->get();
    }

    #[Computed]
    public function transaksiTerbaru(): Collection
    {
        $kurir = auth('kurir')->user();

        if (! $kurir) {
            return collect();
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
            ->with([
                'transaksiLayanan.layanan',
                'pelanggan',
                'kurirJemput',
                'kurirAntar',
            ])
            ->orderBy('tanggal_masuk', 'desc')
            ->limit(3)
            ->get();
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
        return view('livewire.kurir.beranda');
    }
}
