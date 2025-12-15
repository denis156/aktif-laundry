<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Pages;

use App\Helper\AvatarPlaceholder;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Beranda Pelanggan')]
#[Layout('layouts.pelanggan.app')]
class Beranda extends Component
{
    #[Computed]
    public function totalTransaksi(): int
    {
        return Transaksi::query()
            ->where('pelanggan_id', Auth::guard('pelanggan')->id())
            ->count();
    }

    #[Computed]
    public function transaksiSelesai(): int
    {
        return Transaksi::query()
            ->where('pelanggan_id', Auth::guard('pelanggan')->id())
            ->where('status', 'Selesai')
            ->count();
    }

    #[Computed]
    public function transaksiProses(): int
    {
        return Transaksi::query()
            ->where('pelanggan_id', Auth::guard('pelanggan')->id())
            ->where('status', 'Proses')
            ->count();
    }

    #[Computed]
    public function transaksiTerbaru()
    {
        return Transaksi::query()
            ->where('pelanggan_id', Auth::guard('pelanggan')->id())
            ->with(['transaksiLayanan', 'pengiriman', 'kurirAntar', 'kurirJemput', 'pelanggan'])
            ->latest('tanggal_masuk')
            ->limit(3)
            ->get();
    }

    /**
     * Get avatar URL with priority: kurir antar > kurir jemput > pelanggan
     */
    public function getTransaksiAvatar(Transaksi $transaksi): string
    {
        // Prioritas 1: Kurir Antar (gunakan nama dari kolom, avatar dari relasi)
        if ($transaksi->kurir_antar_nama) {
            $avatarUrl = $transaksi->kurirAntar?->avatar_url ?? null;

            return AvatarPlaceholder::getAvatarOrPlaceholder(
                $avatarUrl,
                $transaksi->kurir_antar_nama,
                256
            );
        }

        // Prioritas 2: Kurir Jemput (gunakan nama dari kolom, avatar dari relasi)
        if ($transaksi->kurir_jemput_nama) {
            $avatarUrl = $transaksi->kurirJemput?->avatar_url ?? null;

            return AvatarPlaceholder::getAvatarOrPlaceholder(
                $avatarUrl,
                $transaksi->kurir_jemput_nama,
                256
            );
        }

        // Prioritas 3: Pelanggan
        return AvatarPlaceholder::getAvatarOrPlaceholder(
            $transaksi->pelanggan->avatar_url ?? null,
            $transaksi->pelanggan->nama,
            256
        );
    }

    public function render()
    {
        return view('livewire.pelanggan.pages.beranda', [
            'totalTransaksi' => $this->totalTransaksi(),
            'transaksiSelesai' => $this->transaksiSelesai(),
            'transaksiProses' => $this->transaksiProses(),
            'transaksiTerbaru' => $this->transaksiTerbaru(),
        ]);
    }
}
