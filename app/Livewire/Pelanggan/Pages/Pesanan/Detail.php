<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Pages\Pesanan;

use App\Helper\QrisConvert;
use App\Models\Transaksi;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Detail Pesanan')]
#[Layout('layouts.pelanggan.app')]
class Detail extends Component
{
    use WithFileUploads;

    public Transaksi $transaksi;

    public bool $imageModal = false;

    public string $selectedImage = '';

    public string $qrCodeSvg = '';

    public bool $uploadModal = false;

    #[Rule(['fotoBuktiBayar' => 'required'])]
    #[Rule(['fotoBuktiBayar.*' => 'image|max:5120'])]
    public array $fotoBuktiBayar = [];

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

        // Generate QR Code dynamic on-demand untuk pembayaran
        // Hanya generate jika subtotal dan total sudah ada (> 0)
        if ($this->transaksi->subtotal > 0 && $this->transaksi->total > 0) {
            try {
                $this->qrCodeSvg = QrisConvert::generateOnDemandQrCode((float) $this->transaksi->total);
            } catch (Exception $qrError) {
                // Jika gagal generate QR, gunakan fallback (kosong)
                $this->qrCodeSvg = '';
                Log::warning('QR Code generation failed for transaction', [
                    'transaksi_id' => $this->transaksi->id,
                    'error' => $qrError->getMessage(),
                ]);
            }
        } else {
            // Belum ada subtotal/total, skip QR code generation
            $this->qrCodeSvg = '';
        }
    }

    /**
     * Get badge class untuk status transaksi
     */
    public function getStatusBadgeClass(): string
    {
        return \App\Helper\Database\TransaksiHelper::getStatusBadgeClass($this->transaksi->status);
    }

    /**
     * Get badge class untuk status pembayaran
     */
    public function getPaymentStatusBadgeClass(): string
    {
        return \App\Helper\Database\TransaksiHelper::getStatusBayarBadgeClass($this->transaksi->status_bayar);
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

    /**
     * Show image in modal
     */
    public function showImage(string $imageUrl): void
    {
        $this->selectedImage = $imageUrl;
        $this->imageModal = true;
    }

    /**
     * Get foto bukti timbangan URLs
     */
    public function getFotoTimbangan(): array
    {
        $foto = $this->transaksi->foto_bukti_timbangan;

        if (empty($foto)) {
            return [];
        }

        // Jika Collection, convert ke array
        if ($foto instanceof \Illuminate\Support\Collection) {
            $foto = $foto->toArray();
        }

        // Jika JSON string, decode
        if (is_string($foto)) {
            $decoded = json_decode($foto, true);
            $foto = is_array($decoded) ? $decoded : [];
        }

        // Jika array, ambil URLs
        if (is_array($foto)) {
            return array_filter(array_map(function ($item) {
                return is_array($item) ? ($item['url'] ?? '') : $item;
            }, $foto));
        }

        return [];
    }

    /**
     * Get foto bukti pembayaran URLs
     */
    public function getFotoPembayaran(): array
    {
        $foto = $this->transaksi->foto_bukti_pembayaran;

        if (empty($foto)) {
            return [];
        }

        // Jika Collection, convert ke array
        if ($foto instanceof \Illuminate\Support\Collection) {
            $foto = $foto->toArray();
        }

        // Jika JSON string, decode
        if (is_string($foto)) {
            $decoded = json_decode($foto, true);
            $foto = is_array($decoded) ? $decoded : [];
        }

        // Jika array, ambil URLs
        if (is_array($foto)) {
            return array_filter(array_map(function ($item) {
                return is_array($item) ? ($item['url'] ?? '') : $item;
            }, $foto));
        }

        return [];
    }

    /**
     * Remove file dari preview
     */
    public function removeFile(int $index): void
    {
        unset($this->fotoBuktiBayar[$index]);
        $this->fotoBuktiBayar = array_values($this->fotoBuktiBayar);
    }

    /**
     * Upload bukti pembayaran
     */
    public function uploadBuktiBayar(): void
    {
        // Validate
        $this->validate();

        try {
            $uploadedFiles = [];

            // Upload each file
            foreach ($this->fotoBuktiBayar as $file) {
                $filename = 'pembayaran_'.$this->transaksi->id.'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('foto-bukti-pembayaran', $filename, 'public');

                $uploadedFiles[] = [
                    'url' => Storage::url($path),
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uuid' => \Illuminate\Support\Str::uuid()->toString(), // Add UUID for Mary UI compatibility
                ];
            }

            // Update transaksi
            $this->transaksi->update([
                'foto_bukti_pembayaran' => $uploadedFiles,
                'status_bayar' => 'Menunggu Verifikasi',
            ]);

            // Refresh transaksi
            $this->transaksi->refresh();

            // Reset form
            $this->fotoBuktiBayar = [];
            $this->uploadModal = false;

            // Success notification
            $this->dispatch('notify', type: 'success', message: 'Bukti pembayaran berhasil diunggah! Status pembayaran Anda akan segera diverifikasi.');
        } catch (Exception $e) {
            Log::error('Failed to upload payment proof', [
                'transaksi_id' => $this->transaksi->id,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('notify', type: 'error', message: 'Gagal mengunggah bukti pembayaran. Silakan coba lagi.');
        }
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.pages.pesanan.detail');
    }
}
