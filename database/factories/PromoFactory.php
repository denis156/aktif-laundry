<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promo>
 */
class PromoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;

        $tipeDiskon = fake()->randomElement(['persen', 'nominal']);
        $nilaiDiskon = $tipeDiskon === 'persen' ? fake()->numberBetween(5, 30) : fake()->numberBetween(5000, 50000);
        $diskonMaksimal = $tipeDiskon === 'persen' ? fake()->numberBetween(10000, 100000) : null;

        $tanggalMulai = fake()->dateTimeBetween('-1 month', '+1 month');
        $tanggalBerakhir = (clone $tanggalMulai)->modify('+' . fake()->numberBetween(7, 90) . ' days');

        $promoNames = [
            'WELCOME10' => ['nama' => 'Diskon Pelanggan Baru', 'deskripsi' => 'Diskon untuk pelanggan baru'],
            'CUCIHEMAT' => ['nama' => 'Hemat Cuci Kiloan', 'deskripsi' => 'Diskon spesial cuci kiloan'],
            'WEEKEND' => ['nama' => 'Weekend Special', 'deskripsi' => 'Diskon khusus weekend'],
            'FLASH50' => ['nama' => 'Flash Sale', 'deskripsi' => 'Diskon kilat terbatas'],
            'MEMBER20' => ['nama' => 'Member Loyal', 'deskripsi' => 'Cashback untuk member setia'],
        ];

        $promoCode = array_keys($promoNames)[$counter % count($promoNames)];
        $promoData = $promoNames[$promoCode];
        $counter++;

        return [
            'kode_promo' => $promoCode,
            'nama_promo' => $promoData['nama'],
            'deskripsi' => $promoData['deskripsi'],
            'tipe_diskon' => $tipeDiskon,
            'nilai_diskon' => $nilaiDiskon,
            'diskon_maksimal' => $diskonMaksimal,
            'min_transaksi' => fake()->randomElement([0, 25000, 50000, 100000]),
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_berakhir' => $tanggalBerakhir,
            'kuota_total' => fake()->optional(0.7)->numberBetween(10, 100),
            'kuota_terpakai' => 0,
            'max_per_user' => fake()->numberBetween(1, 5),
            'berlaku_untuk' => fake()->randomElement(['semua', 'pelanggan_baru', 'pelanggan_lama']),
            'status' => fake()->randomElement(['Aktif', 'Tidak Aktif']),
            'metadata' => [
                'banner_image' => fake()->optional(0.3)->imageUrl(800, 400, 'promo'),
                'terms_conditions' => 'Syarat dan ketentuan berlaku',
                'auto_apply' => fake()->boolean(30),
            ],
        ];
    }
}
