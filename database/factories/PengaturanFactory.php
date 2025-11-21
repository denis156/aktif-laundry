<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pengaturan>
 */
class PengaturanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $settings = [
            ['key' => 'nama_toko', 'value' => 'Aktif Laundry', 'type' => 'string', 'group' => 'general', 'deskripsi' => 'Nama toko laundry'],
            ['key' => 'whatsapp', 'value' => '81234567890', 'type' => 'string', 'group' => 'contact', 'deskripsi' => 'Nomor WhatsApp'],
            ['key' => 'email', 'value' => 'info@aktiflaundry.com', 'type' => 'string', 'group' => 'contact', 'deskripsi' => 'Email toko'],
            ['key' => 'jam_buka', 'value' => '08:00', 'type' => 'string', 'group' => 'operasional', 'deskripsi' => 'Jam buka toko'],
            ['key' => 'jam_tutup', 'value' => '21:00', 'type' => 'string', 'group' => 'operasional', 'deskripsi' => 'Jam tutup toko'],
            ['key' => 'format_id_jenis_pakaian', 'value' => 'JNS', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Jenis Pakaian'],
            ['key' => 'format_id_layanan', 'value' => 'LYN', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Layanan'],
            ['key' => 'format_id_pelanggan', 'value' => 'PLG', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Pelanggan'],
            ['key' => 'format_id_transaksi', 'value' => 'TRX', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Transaksi'],
            ['key' => 'format_id_kurir', 'value' => 'KUR', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Kurir'],
            ['key' => 'format_id_pengiriman', 'value' => 'PNG', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Pengiriman'],
            ['key' => 'format_id_pembayaran', 'value' => 'PBY', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Pembayaran'],
            ['key' => 'format_id_promo', 'value' => 'PROMO', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Promo'],
            ['key' => 'format_id_referral', 'value' => 'REF', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Referral'],
            ['key' => 'biaya_antar_per_km', 'value' => '2000', 'type' => 'number', 'group' => 'pricing', 'deskripsi' => 'Biaya antar per kilometer'],
            ['key' => 'min_berat_kg', 'value' => '2', 'type' => 'number', 'group' => 'pricing', 'deskripsi' => 'Minimum berat kiloan'],
            ['key' => 'pajak_persen', 'value' => '10', 'type' => 'number', 'group' => 'pricing', 'deskripsi' => 'Persentase pajak'],
            ['key' => 'enable_referral', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'deskripsi' => 'Aktifkan sistem referral'],
            ['key' => 'enable_promo', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'deskripsi' => 'Aktifkan sistem promo'],
        ];

        static $counter = 0;

        if ($counter >= count($settings)) {
            // Random setting jika sudah melewati list
            return [
                'key' => 'custom_'.fake()->word(),
                'value' => fake()->word(),
                'type' => 'string',
                'group' => 'custom',
                'deskripsi' => fake()->sentence(),
            ];
        }

        $setting = $settings[$counter];
        $counter++;

        return $setting;
    }
}
