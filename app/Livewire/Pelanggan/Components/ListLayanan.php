<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Components;

use App\Models\Layanan;
use Livewire\Component;
use Mary\Traits\Toast;

class ListLayanan extends Component
{
    use Toast;

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

    public function toggleLayanan(int $layananId): void
    {
        // Store layanan ID in session
        $selectedLayananIds = [$layananId];
        session(['selected_layanan_ids' => $selectedLayananIds]);

        $layanan = Layanan::find($layananId);
        $namaLayanan = $layanan ? $layanan->nama_layanan : 'Layanan';

        $this->success($namaLayanan.' dipilih!', position: 'toast-top');
        $this->redirect(route('pesanan-form.pelanggan'), navigate: true);
    }

    public function render(): mixed
    {
        $layananList = Layanan::where('status', 'Aktif')
            ->orderBy('is_popular', 'desc')
            ->orderBy('nama_layanan')
            ->get();

        return view('livewire.pelanggan.components.list-layanan', [
            'layananList' => $layananList,
        ]);
    }
}
