<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Component;

use App\Helper\Database\PromoHelper;
use App\Models\Promo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListPromo extends Component
{
    public function getBannerUrl(Promo $promo): string
    {
        if ($promo->banner_image) {
            return asset('storage/'.$promo->banner_image);
        }

        return asset('images/Logo.png');
    }

    public function render(): mixed
    {
        $pelangganId = Auth::id();

        $promoList = Promo::where('status', 'Aktif')
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            ->orderBy('tanggal_mulai', 'desc')
            ->get()
            ->filter(function (Promo $promo) use ($pelangganId) {
                // Cek masih ada kuota
                if (! PromoHelper::hasQuota($promo)) {
                    return false;
                }

                // Jika user login, filter berdasarkan pelanggan
                if ($pelangganId) {
                    // Cek apakah pelanggan di-exclude
                    if (PromoHelper::isPelangganExcluded($promo, $pelangganId)) {
                        return false;
                    }

                    // Cek apakah pelanggan masih bisa pakai promo (max_per_user)
                    if (! PromoHelper::canUserUsePromo($promo, $pelangganId)) {
                        return false;
                    }
                }

                return true;
            });

        return view('livewire.pelanggan.component.list-promo', [
            'promoList' => $promoList,
        ]);
    }
}
