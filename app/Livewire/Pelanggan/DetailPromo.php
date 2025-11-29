<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use App\Helper\Database\PromoHelper;
use App\Models\Promo;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Detail Promo')]
#[Layout('layouts.pelanggan.app')]
class DetailPromo extends Component
{
    use Toast;

    public ?Promo $promo = null;

    public function mount(int $id): void
    {
        $this->promo = Promo::where('id', $id)
            ->where('status', 'Aktif')
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            ->first();

        if (! $this->promo) {
            $this->error('Promo tidak ditemukan atau sudah tidak berlaku', position: 'toast-top');
            $this->redirect(route('beranda.pelanggan'), navigate: true);

            return;
        }

        // Cek apakah promo masih bisa digunakan
        $pelangganId = Auth::guard('pelanggan')->id();

        if ($pelangganId && ! PromoHelper::canUserUsePromo($this->promo, $pelangganId)) {
            $this->warning('Promo ini sudah tidak bisa Anda gunakan', position: 'toast-top');
        }

        if (! PromoHelper::hasQuota($this->promo)) {
            $this->warning('Promo ini sudah habis kuotanya', position: 'toast-top');
        }
    }

    public function getBannerUrl(): string
    {
        if ($this->promo && $this->promo->banner_image) {
            return asset('storage/'.$this->promo->banner_image);
        }

        return asset('images/Logo.png');
    }

    public function getNilaiDiskonFormatted(): string
    {
        if (! $this->promo) {
            return '';
        }

        return PromoHelper::formatNilaiDiskon(
            $this->promo->tipe_diskon,
            $this->promo->nilai_diskon
        );
    }

    public function getBerlakuUntukLabel(): string
    {
        if (! $this->promo) {
            return '';
        }

        return match ($this->promo->berlaku_untuk) {
            'semua' => 'Semua Pelanggan',
            'pelanggan_baru' => 'Pelanggan Baru',
            'layanan_tertentu' => 'Layanan Tertentu',
            default => $this->promo->berlaku_untuk,
        };
    }

    public function usePromo(): void
    {
        if ($this->promo) {
            $this->dispatch('use-promo', kode: $this->promo->kode_promo);
            $this->success('Promo '.$this->promo->kode_promo.' berhasil diterapkan!', position: 'toast-top');
            $this->redirect(route('pesanan-form.pelanggan'), navigate: true);
        }
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.detail-promo');
    }
}
