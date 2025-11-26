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
            // General Settings
            ['key' => 'nama_toko', 'value' => 'Aktif Laundry', 'type' => 'string', 'group' => 'general', 'deskripsi' => 'Nama toko laundry'],
            ['key' => 'alamat_toko', 'value' => 'Jl. Contoh No. 123, Kendari', 'type' => 'string', 'group' => 'general', 'deskripsi' => 'Alamat toko'],

            // Contact Settings
            ['key' => 'whatsapp', 'value' => '81234567890', 'type' => 'string', 'group' => 'contact', 'deskripsi' => 'Nomor WhatsApp'],
            ['key' => 'email', 'value' => 'info@aktiflaundry.com', 'type' => 'string', 'group' => 'contact', 'deskripsi' => 'Email toko'],

            // Operational Settings
            ['key' => 'jam_buka', 'value' => '08:00', 'type' => 'string', 'group' => 'operasional', 'deskripsi' => 'Jam buka toko'],
            ['key' => 'jam_tutup', 'value' => '21:00', 'type' => 'string', 'group' => 'operasional', 'deskripsi' => 'Jam tutup toko'],

            // Format ID Settings (VERY IMPORTANT)
            ['key' => 'format_id_jenis_pakaian', 'value' => 'JNS', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Jenis Pakaian'],
            ['key' => 'format_id_layanan', 'value' => 'LYN', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Layanan'],
            ['key' => 'format_id_pelanggan', 'value' => 'PLG', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Pelanggan'],
            ['key' => 'format_id_transaksi', 'value' => 'TRX', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Transaksi'],
            ['key' => 'format_id_kurir', 'value' => 'KUR', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Kurir'],
            ['key' => 'format_id_pengiriman', 'value' => 'PNG', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Pengiriman'],
            ['key' => 'format_id_promo', 'value' => 'PROMO', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Promo'],
            ['key' => 'format_id_referral', 'value' => 'REF', 'type' => 'string', 'group' => 'format', 'deskripsi' => 'Format ID Referral'],

            // Pricing Settings
            ['key' => 'biaya_antar_per_km', 'value' => '2000', 'type' => 'number', 'group' => 'pricing', 'deskripsi' => 'Biaya antar per kilometer'],
            ['key' => 'min_berat_kg', 'value' => '2', 'type' => 'number', 'group' => 'pricing', 'deskripsi' => 'Minimum berat kiloan'],
            ['key' => 'pajak_persen', 'value' => '0', 'type' => 'number', 'group' => 'pricing', 'deskripsi' => 'Persentase pajak'],

            // Feature Settings (IMPORTANT for Observers)
            ['key' => 'enable_referral', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'deskripsi' => 'Aktifkan sistem referral'],
            ['key' => 'enable_promo', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'deskripsi' => 'Aktifkan sistem promo'],
            ['key' => 'enable_loyalty_points', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'deskripsi' => 'Aktifkan sistem loyalty points'],

            // Referral Settings (Optional - will be set by seeder if needed)
            ['key' => 'default_promo_referrer_id', 'value' => '', 'type' => 'number', 'group' => 'referral', 'deskripsi' => 'ID Promo default untuk referrer'],
            ['key' => 'default_promo_referee_id', 'value' => '', 'type' => 'number', 'group' => 'referral', 'deskripsi' => 'ID Promo default untuk referee'],
        ];

        static $index = 0;

        if ($index >= count($settings)) {
            // Random setting jika sudah melewati list
            $index++;

            return [
                'key' => 'custom_'.fake()->word().'_'.$index,
                'value' => fake()->word(),
                'type' => 'string',
                'group' => 'custom',
                'deskripsi' => fake()->sentence(),
            ];
        }

        $setting = $settings[$index];
        $index++;

        return $setting;
    }
}
