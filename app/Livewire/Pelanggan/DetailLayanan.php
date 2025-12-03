<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use App\Helper\Database\LayananHelper;
use App\Models\Layanan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Detail Layanan')]
#[Layout('layouts.pelanggan.app')]
class DetailLayanan extends Component
{
    use Toast;

    public ?Layanan $layanan = null;

    public string $tabSelected = 'detail-tab';

    public function getColorClass(): string
    {
        return $this->layanan->is_popular ? 'warning' : 'success';
    }

    public function mount(int $id): void
    {
        $this->layanan = Layanan::where('id', $id)
            ->where('status', 'Aktif')
            ->first();

        if (! $this->layanan) {
            $this->error('Layanan tidak ditemukan atau tidak aktif', position: 'toast-top');
            $this->redirect(route('beranda.pelanggan'), navigate: true);

            return;
        }
    }

    public function getIconUrl(): string
    {
        if ($this->layanan && $this->layanan->icon_path) {
            return asset('storage/'.$this->layanan->icon_path);
        }

        return asset('images/Logo.png');
    }

    public function getHargaFormatted(): string
    {
        if (! $this->layanan) {
            return '';
        }

        return LayananHelper::formatHarga($this->layanan->harga ?? 0, $this->layanan->satuan_harga ?? 'satuan');
    }

    public function pesanSekarang(): void
    {
        $this->dispatch('select-layanan', layananId: $this->layanan->id);
        $this->success('Layanan '.$this->layanan->nama.' telah dipilih!', position: 'toast-top');
        $this->redirect(route('pesanan-form.pelanggan'), navigate: true);
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.detail-layanan');
    }
}
