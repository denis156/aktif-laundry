<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Promo;
use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransaksiPromo>
 */
class TransaksiPromoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transaksi = Transaksi::inRandomOrder()->first();
        $promo = Promo::where('status', 'Aktif')->inRandomOrder()->first() ?? Promo::factory()->active();

        return [
            'transaksi_id' => $transaksi?->id ?? Transaksi::factory(),
            'promo_id' => $promo?->id ?? Promo::factory(),
            'kode_promo' => $promo?->kode_promo ?? 'PROMO'.fake()->numerify('###'),
            'nama_promo' => $promo?->nama_promo ?? fake()->words(3, true),
            'tipe_diskon' => $promo?->tipe_diskon ?? 'persen',
            'nilai_diskon_promo' => $promo?->nilai_diskon ?? fake()->numberBetween(5, 20),
            'diskon_diberikan' => fake()->numberBetween(5000, 30000),
        ];
    }
}
