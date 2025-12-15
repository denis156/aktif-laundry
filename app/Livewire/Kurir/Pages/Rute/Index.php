<?php

declare(strict_types=1);

namespace App\Livewire\Kurir\Pages\Rute;

use App\Helper\AvatarPlaceholder;
use App\Models\Transaksi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Rute Kurir')]
#[Layout('layouts.kurir.app')]
class Index extends Component
{
    #[Computed]
    public function transaksiRute(): Collection
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

    public function render(): View
    {
        return view('livewire.kurir.pages.rute.index');
    }
}
