<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Component;

use App\Models\Layanan;
use Livewire\Component;

class ListLayanan extends Component
{
    public function getVisibleIncludeItems(Layanan $layanan): array
    {
        $items = $layanan->include ?? [];

        return array_slice($items, 0, 3);
    }

    public function getRemainingIncludeCount(Layanan $layanan): int
    {
        $items = $layanan->include ?? [];
        $total = count($items);

        return max(0, $total - 3);
    }

    public function render(): mixed
    {
        $layananList = Layanan::where('status', 'Aktif')
            ->orderBy('is_popular', 'desc')
            ->orderBy('nama_layanan')
            ->get();

        return view('livewire.pelanggan.component.list-layanan', [
            'layananList' => $layananList,
        ]);
    }
}
