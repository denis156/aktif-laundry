<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Component;

use App\Helper\Database\PromoHelper;
use App\Models\Promo;
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
        $promoList = Promo::where('status', 'Aktif')
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            ->orderBy('tanggal_mulai', 'desc')
            ->get()
            ->filter(fn (Promo $promo) => PromoHelper::hasQuota($promo));

        return view('livewire.pelanggan.component.list-promo', [
            'promoList' => $promoList,
        ]);
    }
}
