<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Component;

use App\Helper\Database\LayananHelper;
use App\Models\Layanan;
use Livewire\Component;

class ListLayanan extends Component
{
    public function render(): mixed
    {
        $layananList = Layanan::where('status', 'Aktif')
            ->orderBy('nama_layanan')
            ->get()
            ->sortByDesc(function (Layanan $layanan) {
                return LayananHelper::isPopular($layanan);
            })
            ->map(function (Layanan $layanan) {
                return [
                    'id' => $layanan->id,
                    'nama_layanan' => $layanan->nama_layanan,
                    'tipe_layanan' => $layanan->tipe_layanan,
                    'harga' => $layanan->tipe_layanan === 'per_kg'
                        ? number_format($layanan->harga_per_kg, 0, ',', '.')
                        : number_format($layanan->harga_per_satuan, 0, ',', '.'),
                    'satuan' => $layanan->satuan,
                    'durasi_jam' => $layanan->durasi_jam,
                    'deskripsi' => $layanan->deskripsi,
                    'popular' => LayananHelper::isPopular($layanan),
                    'icon' => LayananHelper::getIcon($layanan),
                    'include' => LayananHelper::getInclude($layanan),
                    'deskripsi_detail' => LayananHelper::getMetadata($layanan, LayananHelper::META_DESKRIPSI_DETAIL, ''),
                ];
            })
            ->values();

        return view('livewire.pelanggan.component.list-layanan', [
            'layananList' => $layananList,
        ]);
    }
}
