<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use App\Models\Transaksi;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detail Pesanan')]
#[Layout('layouts.pelanggan.app')]
class DetailPesanan extends Component
{
    public Transaksi $transaksi;

    public function mount(int $id): void
    {
        $pelanggan = auth('pelanggan')->user();

        // Load transaksi dengan eager loading
        $this->transaksi = Transaksi::with([
            'transaksiLayanan.layanan',
            'transaksiPromo.promo',
            'pelanggan',
            'kasir',
        ])
            ->where('id', $id)
            ->where('pelanggan_id', $pelanggan->id)
            ->firstOrFail();
    }

    /**
     * Get badge class untuk status transaksi
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->transaksi->status) {
            'Menunggu' => 'badge-warning',
            'Proses' => 'badge-info',
            'Selesai' => 'badge-success',
            'Diambil' => 'badge-neutral',
            'Batal' => 'badge-error',
            default => 'badge-ghost',
        };
    }

    /**
     * Get badge class untuk status pembayaran
     */
    public function getPaymentStatusBadgeClass(): string
    {
        return match ($this->transaksi->status_bayar) {
            'Belum Bayar' => 'badge-error',
            'Menunggu Verifikasi' => 'badge-warning',
            'Sudah Bayar' => 'badge-success',
            'Ditolak' => 'badge-error',
            default => 'badge-ghost',
        };
    }

    /**
     * Format layanan item untuk display
     */
    public function formatLayananItem(object $item): array
    {
        $isPerKg = $item->layanan && $item->layanan->tipe_layanan === 'per_kg';

        $quantity = $isPerKg
            ? number_format((float) $item->berat_kg, 1).' kg'
            : (int) $item->jumlah_satuan.' pcs';

        $harga = $isPerKg
            ? 'Rp '.number_format((int) $item->harga_per_kg, 0, ',', '.').'/kg'
            : 'Rp '.number_format((int) $item->harga_per_satuan, 0, ',', '.').'/pcs';

        return [
            'is_per_kg' => $isPerKg,
            'quantity' => $quantity,
            'harga' => $harga,
            'jenis_pakaian' => $isPerKg ? ($item->jenis_pakaian ?? []) : [],
        ];
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.detail-pesanan');
    }
}
