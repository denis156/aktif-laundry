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
        // 5 tipe diskon: persen, nominal, gratis_kg, gratis_hari, cashback
        $tipeDiskon = fake()->randomElement(['persen', 'nominal', 'gratis_kg', 'gratis_hari', 'cashback']);

        // Nilai diskon berdasarkan tipe
        $nilaiDiskon = match ($tipeDiskon) {
            'persen' => fake()->numberBetween(5, 30),
            'nominal' => fake()->numberBetween(5000, 50000),
            'gratis_kg' => fake()->numberBetween(1, 3),
            'gratis_hari' => fake()->numberBetween(1, 2),
            'cashback' => fake()->numberBetween(5000, 25000),
        };

        // Diskon maksimal hanya untuk tipe persen
        $diskonMaksimal = $tipeDiskon === 'persen' ? fake()->numberBetween(10000, 100000) : null;

        $tanggalMulai = fake()->dateTimeBetween('-1 month', '+1 month');
        $tanggalBerakhir = (clone $tanggalMulai)->modify('+'.fake()->numberBetween(7, 90).' days');

        return [
            'kode_promo' => fake()->unique()->regexify('[A-Z]{4,8}[0-9]{0,2}'),
            'nama_promo' => fake()->randomElement([
                'Diskon Pelanggan Baru',
                'Hemat Cuci Kiloan',
                'Weekend Special',
                'Flash Sale',
                'Member Loyal',
                'Promo Akhir Tahun',
                'Cashback Spesial',
            ]),
            'deskripsi' => fake()->sentence(8),
            'tipe_diskon' => $tipeDiskon,
            'nilai_diskon' => $nilaiDiskon,
            'diskon_maksimal' => $diskonMaksimal,
            'min_transaksi' => fake()->randomElement([0, 25000, 50000, 75000, 100000]),
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_berakhir' => $tanggalBerakhir,
            'kuota_total' => fake()->optional(0.7)->numberBetween(10, 100),
            'kuota_terpakai' => 0,
            'max_per_user' => fake()->numberBetween(1, 5),
            'berlaku_untuk' => fake()->randomElement(['semua', 'pelanggan_baru', 'pelanggan_lama']),
            'status' => fake()->randomElement(['Aktif', 'Aktif', 'Tidak Aktif']),
            'metadata' => [
                'banner_image' => fake()->optional(0.3)->imageUrl(800, 400, 'promo'),
                'terms_conditions' => 'Syarat dan ketentuan berlaku',
                'auto_apply' => fake()->boolean(30),
                'min_berat' => fake()->optional(0.3)->numberBetween(2, 5),
                'max_berat' => fake()->optional(0.2)->numberBetween(10, 20),
            ],
        ];
    }

    /**
     * Indicate that promo is for new customers only.
     */
    public function forNewCustomers(): static
    {
        return $this->state(fn (array $attributes) => [
            'berlaku_untuk' => 'pelanggan_baru',
            'nama_promo' => 'Diskon Pelanggan Baru',
        ]);
    }

    /**
     * Indicate that promo is for loyal customers.
     */
    public function forLoyalCustomers(): static
    {
        return $this->state(fn (array $attributes) => [
            'berlaku_untuk' => 'pelanggan_lama',
            'nama_promo' => 'Member Loyal',
        ]);
    }

    /**
     * Indicate that promo is currently active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Aktif',
            'tanggal_mulai' => now()->subDays(7),
            'tanggal_berakhir' => now()->addDays(30),
        ]);
    }

    /**
     * Indicate that promo is currently inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Tidak Aktif',
        ]);
    }

    /**
     * Indicate that promo is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Tidak Aktif',
            'tanggal_mulai' => now()->subDays(60),
            'tanggal_berakhir' => now()->subDays(10),
        ]);
    }
}
