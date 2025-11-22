<?php

declare(strict_types=1);

namespace App\Livewire\Management\Component;

use App\Helper\Database\PengaturanHelper;
use App\Helper\PhoneNumber;
use App\Helper\QrisConvert;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Receipt extends Component
{
    public int $transaksiId;

    public array $transaksiData = [];

    public array $setting = [];

    public string $pelangganAlamat = '';

    public string $pelangganNoHp = '';

    public string $qrCodeSvg = '';

    public function mount(int $id): void
    {
        $this->transaksiId = $id;
        $this->loadReceiptData();
    }

    protected function loadReceiptData(): void
    {
        try {
            // Get Transaksi Data menggunakan eager loading untuk performa
            $transaksi = Transaksi::with(['pelanggan', 'transaksiLayanan.layanan'])
                ->findOrFail($this->transaksiId);

            $this->transaksiData = [
                'id' => $transaksi->id,
                'kode_transaksi' => $transaksi->kode_transaksi,
                'tanggal_masuk' => $transaksi->tanggal_masuk->format('Y-m-d H:i:s'),
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'subtotal' => $transaksi->subtotal,
                'diskon' => $transaksi->diskon,
                'total' => $transaksi->total,
                'metode_pembayaran' => $transaksi->metode_pembayaran,
                'tanggal_selesai' => $transaksi->tanggal_selesai?->format('Y-m-d H:i:s') ?? '',
                'status' => $transaksi->status,
                'catatan' => $transaksi->catatan ?? '',
                'total_berat' => $transaksi->total_berat,
                'total_item' => $transaksi->total_item,
                'jumlah_layanan' => $transaksi->jumlah_layanan,
            ];

            // Get Setting Data menggunakan PengaturanHelper
            $this->setting = [
                'nama_toko' => PengaturanHelper::getValue('nama_toko', 'Aktif Laundry'),
                'alamat' => PengaturanHelper::getValue('alamat', ''),
                'telepon' => PengaturanHelper::getValue('telepon', ''),
                'whatsapp' => PengaturanHelper::getValue('whatsapp', ''),
                'email' => PengaturanHelper::getValue('email', ''),
                'jam_buka' => PengaturanHelper::getValue('jam_buka', '08:00'),
                'jam_tutup' => PengaturanHelper::getValue('jam_tutup', '21:00'),
            ];

            // Get Pelanggan Data menggunakan Helper PhoneNumber untuk format
            if ($transaksi->pelanggan instanceof Pelanggan) {
                $this->pelangganAlamat = $transaksi->pelanggan->alamat ?? '';
                $this->pelangganNoHp = PhoneNumber::formatLocal($transaksi->pelanggan->no_hp)
                    ?? $transaksi->pelanggan->no_hp;
            }

            // Generate QR Code dynamic on-demand menggunakan QrisConvert Helper
            try {
                $this->qrCodeSvg = QrisConvert::generateOnDemandQrCode((float) $transaksi->total);
            } catch (Exception $qrError) {
                // Jika gagal generate QR, gunakan fallback (kosong)
                $this->qrCodeSvg = '';
                Log::warning('QR Code generation failed for transaction', [
                    'transaksi_id' => $transaksi->id,
                    'error' => $qrError->getMessage(),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to load receipt data', [
                'transaksi_id' => $this->transaksiId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(404, 'Transaksi tidak ditemukan: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.management.component.receipt', [
            'transaksiData' => $this->transaksiData,
            'setting' => $this->setting,
            'pelangganAlamat' => $this->pelangganAlamat,
            'pelangganNoHp' => $this->pelangganNoHp,
            'qrCodeSvg' => $this->qrCodeSvg,
        ]);
    }

    /**
     * Method static untuk generate receipt data (untuk route non-Livewire)
     */
    public static function generateReceiptData(int $id): array
    {
        try {
            // Get Transaksi Data dengan eager loading
            $transaksi = Transaksi::with(['pelanggan', 'transaksiLayanan.layanan'])
                ->findOrFail($id);

            $transaksiData = [
                'id' => $transaksi->id,
                'kode_transaksi' => $transaksi->kode_transaksi,
                'tanggal_masuk' => $transaksi->tanggal_masuk->format('Y-m-d H:i:s'),
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'subtotal' => $transaksi->subtotal,
                'diskon' => $transaksi->diskon,
                'total' => $transaksi->total,
                'metode_pembayaran' => $transaksi->metode_pembayaran,
                'tanggal_selesai' => $transaksi->tanggal_selesai?->format('Y-m-d H:i:s') ?? '',
                'status' => $transaksi->status,
                'catatan' => $transaksi->catatan ?? '',
                'total_berat' => $transaksi->total_berat,
                'total_item' => $transaksi->total_item,
                'jumlah_layanan' => $transaksi->jumlah_layanan,
            ];

            // Get Setting Data
            $setting = [
                'nama_toko' => PengaturanHelper::getValue('nama_toko', 'Aktif Laundry'),
                'alamat' => PengaturanHelper::getValue('alamat', ''),
                'telepon' => PengaturanHelper::getValue('telepon', ''),
                'whatsapp' => PengaturanHelper::getValue('whatsapp', ''),
                'email' => PengaturanHelper::getValue('email', ''),
                'jam_buka' => PengaturanHelper::getValue('jam_buka', '08:00'),
                'jam_tutup' => PengaturanHelper::getValue('jam_tutup', '21:00'),
            ];

            // Get Pelanggan Data menggunakan PhoneNumber Helper
            $pelangganAlamat = '';
            $pelangganNoHp = '';
            if ($transaksi->pelanggan instanceof Pelanggan) {
                $pelangganAlamat = $transaksi->pelanggan->alamat ?? '';
                $pelangganNoHp = PhoneNumber::formatLocal($transaksi->pelanggan->no_hp)
                    ?? $transaksi->pelanggan->no_hp;
            }

            // Generate QR Code dynamic on-demand menggunakan QrisConvert Helper
            $qrCodeSvg = '';
            try {
                $qrCodeSvg = QrisConvert::generateOnDemandQrCode((float) $transaksi->total);
            } catch (Exception $qrError) {
                Log::warning('QR Code generation failed for transaction', [
                    'transaksi_id' => $transaksi->id,
                    'error' => $qrError->getMessage(),
                ]);
            }

            return [
                'transaksiData' => $transaksiData,
                'setting' => $setting,
                'pelangganAlamat' => $pelangganAlamat,
                'pelangganNoHp' => $pelangganNoHp,
                'qrCodeSvg' => $qrCodeSvg,
            ];
        } catch (Exception $e) {
            Log::error('Failed to generate receipt data', [
                'transaksi_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(404, 'Transaksi tidak ditemukan: '.$e->getMessage());
        }
    }
}
