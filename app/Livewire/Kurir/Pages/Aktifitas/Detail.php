<?php

declare(strict_types=1);

namespace App\Livewire\Kurir\Pages\Aktifitas;

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
use Mary\Traits\Toast;

#[Title('Detail Aktifitas')]
#[Layout('layouts.kurir.app')]
class Detail extends Component
{
    use Toast;
    use WithFileUploads;

    public Transaksi $transaksi;

    public bool $imageModal = false;

    public string $selectedImage = '';

    public bool $confirmModal = false;

    public bool $confirmAntarModal = false;

    public string $actionType = 'jemput';

    public string $qrCodeSvg = '';

    public bool $uploadModal = false;

    #[Rule(['fotoBuktiBayar' => 'required'])]
    #[Rule(['fotoBuktiBayar.*' => 'image|max:5120'])]
    public array $fotoBuktiBayar = [];

    public function mount(int $id): void
    {
        $kurir = auth('kurir')->user();

        // Load transaksi dengan eager loading
        // Kurir bisa melihat transaksi yang dia jemput/antar atau transaksi yang menunggu/selesai
        $this->transaksi = Transaksi::with([
            'transaksiLayanan.layanan',
            'transaksiPromo.promo',
            'pelanggan',
            'kasir',
            'kurirJemput',
            'kurirAntar',
        ])
            ->where('id', $id)
            ->where(function ($query) use ($kurir) {
                $query->where('kurir_jemput_id', $kurir->id)
                    ->orWhere('kurir_antar_id', $kurir->id)
                    ->orWhere('status', 'Menunggu')
                    ->orWhere('status', 'Selesai');
            })
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
     * Get WhatsApp URL dengan pesan sesuai peran kurir
     */
    public function getWhatsAppUrl(): ?string
    {
        if (! $this->transaksi->pelanggan?->no_hp) {
            return null;
        }

        $kurir = auth('kurir')->user();
        $namaPelanggan = $this->transaksi->nama_pelanggan;
        $namaKurir = $kurir->nama;
        $alamat = $this->transaksi->pelanggan->alamat ?? 'alamat yang terdaftar';

        // Tentukan pesan berdasarkan status transaksi dan peran kurir
        if ($this->transaksi->status === 'Selesai' && ($this->transaksi->kurir_antar_id === $kurir->id || ! $this->transaksi->kurir_antar_id)) {
            // Pesan untuk kurir antar (status Selesai)
            $message = "Halo {$namaPelanggan}, saya {$namaKurir} dari Aktif Laundry.\n\n";
            $message .= "Saya kurir yang akan mengantarkan pakaian laundry Anda dengan kode transaksi *{$this->transaksi->kode_transaksi}*.\n\n";
            $message .= "Pakaian Anda sudah selesai dan siap diantar ke alamat:\n {$alamat}\n\n";
            $message .= 'Mohon pastikan ada yang menerima di lokasi. Terima kasih!';
        } else {
            // Pesan untuk kurir jemput (status Menunggu atau Proses)
            $message = "Halo {$namaPelanggan}, saya {$namaKurir} dari Aktif Laundry.\n\n";
            $message .= "Saya kurir yang akan menjemput pakaian laundry Anda dengan kode transaksi *{$this->transaksi->kode_transaksi}*.\n\n";
            $message .= "Apakah alamat penjemputan sudah sesuai?\n {$alamat}\n\n";
            $message .= 'Mohon konfirmasi jika ada perubahan alamat atau waktu penjemputan. Terima kasih!';
        }

        return \App\Helper\PhoneNumber::getWhatsAppUrl($this->transaksi->pelanggan->no_hp, $message);
    }

    /**
     * Check apakah kurir bisa ambil pesanan
     */
    public function canTakePesanan(): bool
    {
        // Hanya bisa ambil jika belum ada kurir jemput
        return empty($this->transaksi->kurir_jemput_id);
    }

    /**
     * Check apakah bisa menampilkan tombol jemput
     */
    public function showJemputButton(): bool
    {
        // Tampilkan tombol jemput hanya jika:
        // 1. Belum ada kurir jemput
        // 2. Status masih Menunggu
        return $this->transaksi->status === 'Menunggu' &&
               empty($this->transaksi->kurir_jemput_id);
    }

    /**
     * Check apakah bisa menampilkan tombol antar
     */
    public function showAntarButton(): bool
    {
        // Tampilkan tombol antar hanya jika:
        // 1. Status sudah Selesai
        // 2. Belum ada kurir antar
        return $this->transaksi->status === 'Selesai' &&
               empty($this->transaksi->kurir_antar_id);
    }

    /**
     * Ambil pesanan sebagai kurir antar
     */
    public function ambilPesananAntar(): void
    {
        $kurir = auth('kurir')->user();

        // Validasi: pastikan status sudah selesai
        if ($this->transaksi->status !== 'Selesai') {
            $this->error('Pesanan ini belum selesai dikerjakan.', position: 'toast-top', timeout: 5000);

            return;
        }

        // Validasi: pastikan belum ada kurir antar atau ini adalah kurir antar yang sama
        if ($this->transaksi->kurir_antar_id && $this->transaksi->kurir_antar_id !== $kurir->id) {
            $this->error('Pesanan ini sudah diambil oleh kurir lain untuk diantar.', position: 'toast-top', timeout: 5000);

            return;
        }

        try {
            // Update transaksi dengan kurir antar
            $this->transaksi->update([
                'kurir_antar_id' => $kurir->id,
                'kurir_antar_nama' => $kurir->nama,
            ]);

            // Refresh transaksi
            $this->transaksi->refresh();

            // Close modal
            $this->confirmAntarModal = false;

            // Success toast
            $this->success('Pesanan berhasil diambil untuk diantar!', position: 'toast-top', timeout: 5000);

            // Redirect ke halaman rute detail
            $this->redirect(route('rute-detail.kurir', $this->transaksi->id), navigate: true);
        } catch (\Exception $e) {
            Log::error('Failed to take delivery order', [
                'transaksi_id' => $this->transaksi->id,
                'kurir_id' => $kurir->id,
                'error' => $e->getMessage(),
            ]);

            $this->error('Gagal mengambil pesanan. Silakan coba lagi.', position: 'toast-top', timeout: 5000);
        }
    }

    /**
     * Buka modal konfirmasi jemput
     */
    public function openConfirmModal(): void
    {
        $this->actionType = 'jemput';
        $this->confirmModal = true;
    }

    /**
     * Buka modal konfirmasi antar
     */
    public function openConfirmAntarModal(): void
    {
        $this->actionType = 'antar';
        $this->confirmAntarModal = true;
    }

    /**
     * Ambil pesanan sebagai kurir jemput
     */
    public function ambilPesanan(): void
    {
        $kurir = auth('kurir')->user();

        // Validasi: pastikan belum ada kurir jemput
        if ($this->transaksi->kurir_jemput_id) {
            $this->error('Pesanan ini sudah diambil oleh kurir lain.', position: 'toast-top', timeout: 5000);

            return;
        }

        try {
            // Update transaksi dengan kurir jemput
            $this->transaksi->update([
                'kurir_jemput_id' => $kurir->id,
                'kurir_jemput_nama' => $kurir->nama,
            ]);

            // Refresh transaksi
            $this->transaksi->refresh();

            // Close modal
            $this->confirmModal = false;

            // Success toast
            $this->success('Pesanan berhasil diambil! Status akan berubah menjadi Proses.', position: 'toast-top', timeout: 5000);

            // Redirect ke halaman rute detail
            $this->redirect(route('rute-detail.kurir', $this->transaksi->id), navigate: true);
        } catch (\Exception $e) {
            Log::error('Failed to take order', [
                'transaksi_id' => $this->transaksi->id,
                'kurir_id' => $kurir->id,
                'error' => $e->getMessage(),
            ]);

            $this->error('Gagal mengambil pesanan. Silakan coba lagi.', position: 'toast-top', timeout: 5000);
        }
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
            $this->success('Bukti pembayaran berhasil diunggah! Status pembayaran akan segera diverifikasi.', position: 'toast-top', timeout: 5000);
        } catch (Exception $e) {
            Log::error('Failed to upload payment proof', [
                'transaksi_id' => $this->transaksi->id,
                'error' => $e->getMessage(),
            ]);

            $this->error('Gagal mengunggah bukti pembayaran. Silakan coba lagi.', position: 'toast-top', timeout: 5000);
        }
    }

    public function render(): mixed
    {
        return view('livewire.kurir.pages.aktifitas.detail');
    }
}
