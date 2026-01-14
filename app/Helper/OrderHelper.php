<?php

declare(strict_types=1);

namespace App\Helper;

use App\Helper\Database\TransaksiHelper;
use App\Helper\Database\TransaksiLayananHelper;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\TransaksiLayanan;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Helper untuk mengelola order flow dalam chatbot WhatsApp
 */
class OrderHelper
{
    /**
     * Parse pesan pelanggan untuk ekstrak layanan yang dipesan
     * Format: "Cuci Lipat 3kg" atau "Cuci Bed Cover 2 lembar" atau "Cuci Lipat 3kg, Setrika 5 lembar"
     *
     * @return array{success: bool, services: array, error: ?string}
     */
    public static function parseOrderMessage(string $message): array
    {
        $message = trim($message);

        // Pattern untuk match berbagai format:
        // - "Cuci Lipat 3kg" atau "Cuci Lipat 3 kg"
        // - "Cuci Bed Cover 2 lembar" atau "Cuci Bed Cover 2lembar"
        // - Multiple: "Cuci Lipat 3kg, Setrika 5 lembar"
        $pattern = '/([a-zA-Z\s]+?)(\d+(?:\.\d+)?)\s*(kg|kilogram|lembar|pcs|piece)/i';

        preg_match_all($pattern, $message, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return [
                'success' => false,
                'services' => [],
                'error' => 'Format pesanan tidak valid. Contoh: "Cuci Lipat 3kg" atau "Cuci Bed Cover 2 lembar"',
            ];
        }

        $services = [];
        foreach ($matches as $match) {
            $namaLayanan = trim($match[1]);
            $quantity = (float) $match[2];
            $unit = strtolower($match[3]);

            // Normalize unit
            if (in_array($unit, ['kg', 'kilogram'])) {
                $unit = 'kg';
            } else {
                $unit = 'satuan';
            }

            $services[] = [
                'nama_layanan' => $namaLayanan,
                'quantity' => $quantity,
                'unit' => $unit,
            ];
        }

        return [
            'success' => true,
            'services' => $services,
            'error' => null,
        ];
    }

    /**
     * Validasi dan match layanan yang dipesan dengan database
     *
     * @param  array  $parsedServices  dari parseOrderMessage
     * @return array{success: bool, matched: array, errors: array}
     */
    public static function matchServices(array $parsedServices): array
    {
        $matched = [];
        $errors = [];

        foreach ($parsedServices as $service) {
            $namaLayanan = $service['nama_layanan'];
            $quantity = $service['quantity'];
            $unit = $service['unit'];

            // Cari layanan berdasarkan nama (fuzzy match)
            $layanan = Layanan::query()
                ->where('status', 'aktif')
                ->where('nama_layanan', 'LIKE', '%'.$namaLayanan.'%')
                ->first();

            if (! $layanan) {
                $errors[] = "Layanan '{$namaLayanan}' tidak ditemukan";

                continue;
            }

            // Validasi unit sesuai tipe layanan
            $tipeLayanan = $layanan->tipe_layanan;
            if ($tipeLayanan === 'per_kg' && $unit !== 'kg') {
                $errors[] = "Layanan '{$layanan->nama_layanan}' harus dalam satuan kg";

                continue;
            }

            if ($tipeLayanan === 'per_satuan' && $unit !== 'satuan') {
                $errors[] = "Layanan '{$layanan->nama_layanan}' harus dalam satuan lembar/pcs";

                continue;
            }

            // Validasi min order jika ada
            if ($layanan->min_order && $quantity < $layanan->min_order) {
                $errors[] = "Layanan '{$layanan->nama_layanan}' minimal order {$layanan->min_order} {$layanan->satuan}";

                continue;
            }

            // Hitung subtotal
            $subtotal = 0;
            if ($tipeLayanan === 'per_kg') {
                $subtotal = (int) ($quantity * $layanan->harga_per_kg);
            } else {
                $subtotal = (int) ($quantity * $layanan->harga_per_satuan);
            }

            $matched[] = [
                'layanan_id' => $layanan->id,
                'nama_layanan' => $layanan->nama_layanan,
                'tipe_layanan' => $tipeLayanan,
                'quantity' => $quantity,
                'unit' => $unit,
                'harga_per_kg' => $layanan->harga_per_kg,
                'harga_per_satuan' => $layanan->harga_per_satuan,
                'subtotal' => $subtotal,
                'durasi_jam' => $layanan->durasi_jam,
            ];
        }

        return [
            'success' => empty($errors),
            'matched' => $matched,
            'errors' => $errors,
        ];
    }

    /**
     * Buat transaksi baru dari data order
     *
     * @param  array  $matchedServices  dari matchServices
     * @return array{success: bool, transaksi: ?Transaksi, error: ?string}
     */
    public static function createOrder(
        Pelanggan $pelanggan,
        array $matchedServices,
        ?string $catatan = null
    ): array {
        try {
            DB::beginTransaction();

            // Generate kode transaksi
            $kodeTransaksi = TransaksiHelper::generateKodeTransaksi();

            // Get kasir chatbot (bisa pakai admin atau buat kasir khusus untuk chatbot)
            $kasirChatbot = User::query()
                ->where('email', 'chatbot@aktiflaundry.com')
                ->orWhere('name', 'Chatbot AI')
                ->first();

            if (! $kasirChatbot) {
                // Fallback ke admin pertama
                $kasirChatbot = User::query()
                    ->where('role', 'super_admin')
                    ->first();
            }

            if (! $kasirChatbot) {
                throw new Exception('Kasir untuk chatbot tidak ditemukan');
            }

            // Hitung total
            $subtotal = array_sum(array_column($matchedServices, 'subtotal'));
            $total = $subtotal; // Belum termasuk diskon/promo

            // Tentukan metode pembayaran (default: Bayar Saat Antar untuk order via WA)
            $metodePembayaran = TransaksiHelper::METODE_BAYAR_SAAT_ANTAR;

            // Buat transaksi
            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'tanggal_masuk' => now(),
                'kasir_id' => $kasirChatbot->id,
                'pelanggan_id' => $pelanggan->id,
                'nama_pelanggan' => $pelanggan->nama,
                'subtotal' => $subtotal,
                'total' => $total,
                'metode_pembayaran' => $metodePembayaran,
                'status_bayar' => TransaksiHelper::STATUS_BELUM_BAYAR,
                'status' => TransaksiHelper::STATUS_MENUNGGU,
                'catatan' => $catatan,
                'catatan_internal' => 'Order via WhatsApp Chatbot',
            ]);

            // Buat detail transaksi layanan
            foreach ($matchedServices as $service) {
                $transaksiLayanan = TransaksiLayanan::create([
                    'transaksi_id' => $transaksi->id,
                    'layanan_id' => $service['layanan_id'],
                    'nama_layanan' => $service['nama_layanan'],
                    'berat_kg' => $service['tipe_layanan'] === 'per_kg' ? $service['quantity'] : 0,
                    'jumlah_satuan' => $service['tipe_layanan'] === 'per_satuan' ? (int) $service['quantity'] : 0,
                    'harga_per_kg' => $service['harga_per_kg'] ?? 0,
                    'harga_per_satuan' => $service['harga_per_satuan'] ?? 0,
                    'subtotal' => $service['subtotal'],
                    'jenis_pakaian' => [], // Kosong untuk order via chatbot
                ]);
            }

            // Hitung ulang total transaksi
            TransaksiHelper::hitungUlangTotal($transaksi);

            DB::commit();

            // Reload transaksi dengan relasi
            $transaksi->load('transaksiLayanan', 'pelanggan');

            return [
                'success' => true,
                'transaksi' => $transaksi,
                'error' => null,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to create order from chatbot', [
                'pelanggan_id' => $pelanggan->id,
                'services' => $matchedServices,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'transaksi' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format detail order untuk konfirmasi ke pelanggan
     */
    public static function formatOrderSummary(array $matchedServices, int $total): string
    {
        $summary = "Detail Pesanan:\n\n";

        foreach ($matchedServices as $index => $service) {
            $no = $index + 1;
            $qty = $service['tipe_layanan'] === 'per_kg'
                ? $service['quantity'].' kg'
                : (int) $service['quantity'].' lembar';

            $harga = 'Rp '.number_format($service['subtotal'], 0, ',', '.');

            $summary .= "{$no}. {$service['nama_layanan']}\n";
            $summary .= "   Jumlah: {$qty}\n";
            $summary .= "   Harga: {$harga}\n\n";
        }

        $summary .= 'Total: Rp '.number_format($total, 0, ',', '.')."\n";
        $summary .= "Metode Bayar: Bayar Saat Antar\n\n";
        $summary .= "Ketik 'YA' untuk konfirmasi atau 'BATAL' untuk membatalkan.";

        return $summary;
    }

    /**
     * Format success message setelah order berhasil dibuat
     */
    public static function formatOrderSuccessMessage(Transaksi $transaksi): string
    {
        $message = "🎉 Pesanan berhasil dibuat!\n\n";
        $message .= "Kode Transaksi: {$transaksi->kode_transaksi}\n";
        $message .= 'Total: Rp '.number_format($transaksi->total, 0, ',', '.')."\n\n";

        $message .= "Detail Layanan:\n";
        foreach ($transaksi->transaksiLayanan as $index => $tl) {
            $no = $index + 1;
            $label = TransaksiLayananHelper::getDisplayLabel($tl);
            $message .= "{$no}. {$label}\n";
        }

        $message .= "\nStatus: {$transaksi->status}\n";
        $message .= "Pembayaran: {$transaksi->metode_pembayaran}\n\n";

        // Estimasi waktu selesai
        $estimasi = TransaksiHelper::getTanggalSelesaiTerlama($transaksi);
        if ($estimasi) {
            $message .= "Estimasi Selesai: {$estimasi->format('d M Y H:i')}\n\n";
        }

        $message .= "Kurir kami akan segera menjemput cucian Anda! 🚗\n";
        $message .= 'Terima kasih sudah memesan di Aktif Laundry 💙';

        return $message;
    }

    /**
     * Get available services untuk ditampilkan ke pelanggan
     */
    public static function getAvailableServicesText(): string
    {
        $layananKiloan = Layanan::query()
            ->where('status', 'aktif')
            ->where('tipe_layanan', 'per_kg')
            ->orderBy('nama_layanan')
            ->get();

        $layananSatuan = Layanan::query()
            ->where('status', 'aktif')
            ->where('tipe_layanan', 'per_satuan')
            ->orderBy('nama_layanan')
            ->get();

        $text = "📋 Layanan Tersedia:\n\n";

        if ($layananKiloan->isNotEmpty()) {
            $text .= "🧺 Layanan Kiloan:\n";
            foreach ($layananKiloan as $layanan) {
                $harga = 'Rp '.number_format($layanan->harga_per_kg, 0, ',', '.').'/'.$layanan->satuan;
                $durasi = $layanan->durasi_jam >= 24
                    ? round($layanan->durasi_jam / 24).' hari'
                    : $layanan->durasi_jam.' jam';

                $text .= "- {$layanan->nama_layanan}: {$harga} (estimasi {$durasi})\n";
            }
            $text .= "\n";
        }

        if ($layananSatuan->isNotEmpty()) {
            $text .= "👕 Layanan Satuan:\n";
            foreach ($layananSatuan as $layanan) {
                $harga = 'Rp '.number_format($layanan->harga_per_satuan, 0, ',', '.').'/'.$layanan->satuan;
                $durasi = $layanan->durasi_jam >= 24
                    ? round($layanan->durasi_jam / 24).' hari'
                    : $layanan->durasi_jam.' jam';

                $text .= "- {$layanan->nama_layanan}: {$harga} (estimasi {$durasi})\n";
            }
        }

        $text .= "\nContoh Pesan:\n";
        $text .= "- 'Cuci Lipat 3kg'\n";
        $text .= "- 'Cuci Bed Cover 2 lembar'\n";
        $text .= "- 'Cuci Lipat 3kg, Setrika 5 lembar'\n";

        return $text;
    }
}
