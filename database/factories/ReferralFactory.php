<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Referral>
 */
class ReferralFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pelanggan_id' => Pelanggan::factory(),
            'kode_referral' => 'REF' . strtoupper(Str::random(6)),
            'poin_referrer' => fake()->numberBetween(5000, 20000),
            'diskon_referee' => fake()->numberBetween(5000, 15000),
            'min_transaksi_referee' => fake()->numberBetween(25000, 100000),
            'total_referral' => 0,
            'total_poin' => 0,
            'total_berhasil' => 0,
            'status' => 'Aktif',
            'metadata' => [
                'reward_history' => [],
                'special_reward' => fake()->optional(0.2)->randomElement([
                    'Bonus Poin Double',
                    'Gratis Cuci 1x',
                    'Voucher 50rb',
                ]),
            ],
        ];
    }
}
