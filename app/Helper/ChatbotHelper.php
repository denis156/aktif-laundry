<?php

declare(strict_types=1);

namespace App\Helper;

use App\Helper\Database\PengaturanHelper;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Promo;
use App\Models\Transaksi;

class ChatbotHelper
{
    /**
     * Get business information context for AI
     */
    public static function getBusinessContext(): string
    {
        $namaToko = PengaturanHelper::getValue('nama_toko', 'Aktif Laundry');
        $whatsapp = PengaturanHelper::getValue('whatsapp', '');
        $email = PengaturanHelper::getValue('email', '');
        $jamBuka = PengaturanHelper::getValue('jam_buka', '08:00');
        $jamTutup = PengaturanHelper::getValue('jam_tutup', '21:00');
        $minBeratKg = PengaturanHelper::getValue('min_berat_kg', 2);

        return <<<CONTEXT
INFORMASI BISNIS:
- Nama Toko: {$namaToko}
- WhatsApp: {$whatsapp}
- Email: {$email}
- Jam Operasional: {$jamBuka} - {$jamTutup}
- Minimum Berat Kiloan: {$minBeratKg} kg
CONTEXT;
    }

    /**
     * Get available services context for AI
     */
    public static function getServicesContext(): string
    {
        $layanan = Layanan::query()
            ->where('status', 'aktif')
            ->orderBy('nama_layanan')
            ->get(['nama_layanan', 'tipe_layanan', 'harga_per_kg', 'harga_per_satuan', 'satuan', 'durasi_jam', 'deskripsi'])
            ->map(function ($item) {
                $harga = $item->tipe_layanan === 'kiloan'
                    ? 'Rp '.number_format((float) $item->harga_per_kg, 0, ',', '.').'/'.$item->satuan
                    : 'Rp '.number_format((float) $item->harga_per_satuan, 0, ',', '.').'/'.$item->satuan;

                $durasi = $item->durasi_jam
                    ? ($item->durasi_jam >= 24 ? round($item->durasi_jam / 24).' hari' : $item->durasi_jam.' jam')
                    : 'estimasi belum ditentukan';

                $desc = $item->deskripsi ? '. '.$item->deskripsi : '';

                return "- {$item->nama_layanan} ({$item->tipe_layanan}): {$harga}, estimasi {$durasi}{$desc}";
            })
            ->join("\n");

        if (empty($layanan)) {
            return 'LAYANAN: Tidak ada layanan tersedia saat ini.';
        }

        return <<<CONTEXT
LAYANAN TERSEDIA:
{$layanan}
CONTEXT;
    }

    /**
     * Get active promos context for AI
     */
    public static function getPromosContext(): string
    {
        $promos = Promo::query()
            ->where('status', 'aktif')
            ->where(function ($query) {
                $query->whereNull('tanggal_berakhir')
                    ->orWhere('tanggal_berakhir', '>=', now());
            })
            ->orderBy('nama_promo')
            ->get(['nama_promo', 'deskripsi', 'tipe_diskon', 'nilai_diskon', 'tanggal_berakhir', 'min_transaksi'])
            ->map(function ($item) {
                $potongan = $item->tipe_diskon === 'persentase'
                    ? $item->nilai_diskon.'%'
                    : 'Rp '.number_format((float) $item->nilai_diskon, 0, ',', '.');

                $berlaku = $item->tanggal_berakhir
                    ? 'berlaku sampai '.date('d M Y', strtotime($item->tanggal_berakhir))
                    : 'tanpa batas waktu';

                $minTrx = $item->min_transaksi
                    ? ', min. transaksi Rp '.number_format((float) $item->min_transaksi, 0, ',', '.')
                    : '';

                $desc = $item->deskripsi ? '. '.$item->deskripsi : '';

                return "- {$item->nama_promo}: potongan {$potongan}, {$berlaku}{$minTrx}{$desc}";
            })
            ->join("\n");

        if (empty($promos)) {
            return 'PROMO: Tidak ada promo aktif saat ini.';
        }

        return <<<CONTEXT
PROMO AKTIF:
{$promos}
CONTEXT;
    }

    /**
     * Get customer transaction status by phone number
     */
    public static function getCustomerTransactionStatus(string $phoneNumber): ?string
    {
        // Clean phone number (remove +, spaces, etc.)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Try different phone number formats
        // Examples:
        // Input: 6282218082107 -> variations: 6282218082107, 82218082107, 082218082107, 2218082107
        // Input: 082218082107 -> variations: 082218082107, 82218082107, 6282218082107
        // Input: 81524089375 -> variations: 81524089375, 081524089375, 6281524089375
        $variations = [
            $cleanPhone, // original number
        ];

        // Add variation with country code 62
        if (str_starts_with($cleanPhone, '0')) {
            // 082218082107 -> 82218082107
            $variations[] = ltrim($cleanPhone, '0');
            // 082218082107 -> 6282218082107
            $variations[] = '62'.ltrim($cleanPhone, '0');
        } elseif (str_starts_with($cleanPhone, '62')) {
            // 6282218082107 -> 82218082107
            $variations[] = substr($cleanPhone, 2);
            // 6282218082107 -> 082218082107
            $variations[] = '0'.substr($cleanPhone, 2);
        } else {
            // 81524089375 -> 081524089375
            $variations[] = '0'.$cleanPhone;
            // 81524089375 -> 6281524089375
            $variations[] = '62'.$cleanPhone;
        }

        // Remove duplicates
        $variations = array_unique($variations);

        // Try to find customer
        $pelanggan = Pelanggan::query()
            ->where(function ($query) use ($variations) {
                foreach ($variations as $variant) {
                    $query->orWhere('no_hp', 'LIKE', "%{$variant}%");
                }
            })
            ->first();

        if (! $pelanggan) {
            return null;
        }

        // Get latest transactions
        $transaksi = Transaksi::query()
            ->where('pelanggan_id', $pelanggan->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get(['kode_transaksi', 'status', 'status_bayar', 'total', 'tanggal_masuk', 'tanggal_selesai'])
            ->map(function ($item) {
                $status = match ($item->status) {
                    'proses' => 'Sedang Diproses',
                    'selesai' => 'Selesai',
                    'diambil' => 'Sudah Diambil',
                    'batal' => 'Dibatalkan',
                    default => ucfirst(str_replace('_', ' ', $item->status)),
                };

                $bayar = $item->status_bayar === 'lunas' ? 'Lunas' : 'Belum Lunas';
                $total = 'Rp '.number_format((float) $item->total, 0, ',', '.');
                $tanggal = date('d M Y', strtotime($item->tanggal_masuk));

                $info = "{$item->kode_transaksi}: {$status}, {$bayar}, {$total} (masuk: {$tanggal})";

                if ($item->tanggal_selesai) {
                    $selesai = date('d M Y', strtotime($item->tanggal_selesai));
                    $info .= " - selesai: {$selesai}";
                }

                return "  {$info}";
            })
            ->join("\n");

        if (empty($transaksi)) {
            return "Pelanggan: {$pelanggan->nama}\nBelum ada transaksi.";
        }

        return <<<CONTEXT
TRANSAKSI PELANGGAN: {$pelanggan->nama}
{$transaksi}
CONTEXT;
    }

    /**
     * Get statistics for AI context
     */
    public static function getStatistics(): string
    {
        $totalPelanggan = Pelanggan::query()->where('status', 'aktif')->count();
        $totalLayanan = Layanan::query()->where('status', 'aktif')->count();
        $totalPromo = Promo::query()->where('status', 'aktif')->count();

        $transaksiHariIni = Transaksi::query()
            ->whereDate('created_at', today())
            ->count();

        return <<<CONTEXT
STATISTIK:
- Total Pelanggan Aktif: {$totalPelanggan}
- Layanan Tersedia: {$totalLayanan}
- Promo Aktif: {$totalPromo}
- Transaksi Hari Ini: {$transaksiHariIni}
CONTEXT;
    }

    /**
     * Build complete context for AI chatbot
     */
    public static function buildContext(?string $senderPhone = null): string
    {
        $context = [
            self::getBusinessContext(),
            self::getServicesContext(),
            self::getPromosContext(),
            self::getStatistics(),
        ];

        // Add customer transaction info if phone number provided
        if ($senderPhone) {
            $customerInfo = self::getCustomerTransactionStatus($senderPhone);
            if ($customerInfo) {
                $context[] = $customerInfo;
            }
        }

        return implode("\n\n", $context);
    }

    /**
     * Find customer by phone number (for ConversationHelper)
     */
    public static function getCustomerByPhone(string $phoneNumber): ?Pelanggan
    {
        // Clean phone number (remove +, spaces, etc.)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Try different phone number formats
        $variations = [
            $cleanPhone, // original number
        ];

        // Add variation with country code 62
        if (str_starts_with($cleanPhone, '0')) {
            // 082218082107 -> 82218082107
            $variations[] = ltrim($cleanPhone, '0');
            // 082218082107 -> 6282218082107
            $variations[] = '62'.ltrim($cleanPhone, '0');
        } elseif (str_starts_with($cleanPhone, '62')) {
            // 6282218082107 -> 82218082107
            $variations[] = substr($cleanPhone, 2);
            // 6282218082107 -> 082218082107
            $variations[] = '0'.substr($cleanPhone, 2);
        } else {
            // 81524089375 -> 081524089375
            $variations[] = '0'.$cleanPhone;
            // 81524089375 -> 6281524089375
            $variations[] = '62'.$cleanPhone;
        }

        // Remove duplicates
        $variations = array_unique($variations);

        // Try to find customer
        return Pelanggan::query()
            ->where(function ($query) use ($variations) {
                foreach ($variations as $variant) {
                    $query->orWhere('no_hp', 'LIKE', "%{$variant}%");
                }
            })
            ->first();
    }
}
