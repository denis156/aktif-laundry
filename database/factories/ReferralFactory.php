<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helper\Database\ReferralHelper;
use App\Models\Pelanggan;
use App\Models\Promo;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'kode_referral' => ReferralHelper::generateKodeReferral(),
            'promo_referrer_id' => null,
            'promo_referee_id' => null,
            'total_referral' => 0,
            'total_berhasil' => 0,
            'campaign_source' => null,
        ];
    }

    /**
     * Indicate that referral has associated promo rewards.
     */
    public function withPromo(): static
    {
        return $this->state(function (array $attributes) {
            $promoReferrer = Promo::factory()->active()->create([
                'nama_promo' => 'Bonus Referrer',
                'tipe_diskon' => 'nominal',
                'nilai_diskon' => 10000,
            ]);

            $promoReferee = Promo::factory()->active()->create([
                'nama_promo' => 'Diskon Referee',
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 15,
            ]);

            return [
                'promo_referrer_id' => $promoReferrer->id,
                'promo_referee_id' => $promoReferee->id,
            ];
        });
    }

    /**
     * Indicate that referral has some successful conversions.
     */
    public function withConversions(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_referral' => fake()->numberBetween(5, 20),
            'total_berhasil' => fake()->numberBetween(2, 10),
        ]);
    }

    /**
     * Indicate that referral is from a specific campaign.
     */
    public function fromCampaign(string $campaign): static
    {
        return $this->state(fn (array $attributes) => [
            'campaign_source' => $campaign,
        ]);
    }
}
