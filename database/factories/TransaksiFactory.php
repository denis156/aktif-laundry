<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helper\Database\TransaksiHelper;
use App\Models\Kurir;
use App\Models\Pelanggan;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaksi>
 */
class TransaksiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pelanggan = Pelanggan::inRandomOrder()->first();
        $kasir = User::inRandomOrder()->first();
        $kurirJemput = fake()->boolean(40) ? Kurir::where('status', 'Aktif')->inRandomOrder()->first() : null;
        $kurirAntar = fake()->boolean(30) ? Kurir::where('status', 'Aktif')->inRandomOrder()->first() : null;

        $tanggalMasuk = fake()->dateTimeBetween('-1 month', 'now');
        $durasiJam = fake()->numberBetween(24, 72);
        $tanggalSelesai = (clone $tanggalMasuk)->modify("+{$durasiJam} hours");

        $subtotal = fake()->numberBetween(30000, 200000);
        $diskon = fake()->randomElement([0, 0, 0, 5000, 10000, 20000]);
        $total = $subtotal - $diskon;

        // Payment data
        $metodePembayaran = fake()->randomElement(TransaksiHelper::getAllMetodePembayaran());
        $tipeBayar = fake()->randomElement(TransaksiHelper::getAllTipeBayar());
        $statusBayar = fake()->randomElement([
            TransaksiHelper::STATUS_BELUM_BAYAR,
            TransaksiHelper::STATUS_SUDAH_BAYAR,
            TransaksiHelper::STATUS_SUDAH_BAYAR,
        ]);

        $tanggalBayar = $statusBayar === TransaksiHelper::STATUS_SUDAH_BAYAR
            ? fake()->dateTimeBetween($tanggalMasuk, 'now')
            : null;

        $jumlahBayar = $statusBayar === TransaksiHelper::STATUS_SUDAH_BAYAR
            ? ($tipeBayar === TransaksiHelper::TIPE_TUNAI
                ? $total + fake()->randomElement([0, 0, 5000, 10000])
                : $total)
            : null;

        return [
            'kode_transaksi' => TransaksiHelper::generateKodeTransaksi(),
            'tanggal_masuk' => $tanggalMasuk,
            'kasir_id' => $kasir?->id ?? User::factory(),
            'pelanggan_id' => $pelanggan?->id ?? Pelanggan::factory(),
            'nama_pelanggan' => $pelanggan?->nama ?? fake()->name(),
            'referral_id' => null,
            // Kurir
            'kurir_jemput_id' => $kurirJemput?->id,
            'kurir_jemput_nama' => $kurirJemput?->nama,
            'kurir_antar_id' => $kurirAntar?->id,
            'kurir_antar_nama' => $kurirAntar?->nama,
            // Totals (akan di-update saat TransaksiLayanan dibuat)
            'total_berat' => 0,
            'total_item' => 0,
            'jumlah_layanan' => 0,
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'total' => $total,
            // Payment
            'metode_pembayaran' => $metodePembayaran,
            'tipe_bayar' => $tipeBayar,
            'status_bayar' => $statusBayar,
            'tanggal_bayar' => $tanggalBayar,
            'jumlah_bayar' => $jumlahBayar,
            // Timeline
            'tanggal_selesai' => $tanggalSelesai,
            'status' => fake()->randomElement(TransaksiHelper::getAllStatus()),
            // Notes
            'catatan' => fake()->optional(0.3)->sentence(),
            'catatan_internal' => fake()->optional(0.2)->sentence(),
            // Images
            'foto_bukti_timbangan' => [],
            'foto_bukti_pembayaran' => [],
        ];
    }

    /**
     * Indicate that transaction uses a referral code.
     */
    public function withReferral(): static
    {
        return $this->state(function (array $attributes) {
            $referral = Referral::inRandomOrder()->first();

            return [
                'referral_id' => $referral?->id,
            ];
        });
    }

    /**
     * Indicate that transaction is paid.
     */
    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            $tanggalBayar = fake()->dateTimeBetween($attributes['tanggal_masuk'], 'now');
            $jumlahBayar = $attributes['tipe_bayar'] === TransaksiHelper::TIPE_TUNAI
                ? $attributes['total'] + fake()->randomElement([0, 5000, 10000])
                : $attributes['total'];

            return [
                'status_bayar' => TransaksiHelper::STATUS_SUDAH_BAYAR,
                'tanggal_bayar' => $tanggalBayar,
                'jumlah_bayar' => $jumlahBayar,
            ];
        });
    }

    /**
     * Indicate that transaction is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransaksiHelper::STATUS_SELESAI,
            'status_bayar' => TransaksiHelper::STATUS_SUDAH_BAYAR,
        ]);
    }
}
