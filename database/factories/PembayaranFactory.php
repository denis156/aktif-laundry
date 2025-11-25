<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pembayaran>
 */
class PembayaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $metode = fake()->randomElement(['Tunai', 'Transfer', 'QRIS', 'Debit']);
        $transaksi = Transaksi::inRandomOrder()->first();
        $totalTransaksi = $transaksi ? $transaksi->total : fake()->numberBetween(50000, 500000);

        // Untuk tunai, kadang ada kembalian
        $jumlahBayar = $metode === 'Tunai'
            ? $totalTransaksi + fake()->randomElement([0, 0, 5000, 10000, 20000, 50000])
            : $totalTransaksi;

        $kembalian = $jumlahBayar - $totalTransaksi;

        $tanggalBayar = $transaksi
            ? fake()->dateTimeBetween($transaksi->tanggal_masuk, 'now')
            : fake()->dateTimeBetween('-1 month', 'now');

        $status = fake()->randomElement(['Verified', 'Verified', 'Verified', 'Pending']);
        $buktiTransfer = ($metode === 'Transfer' || $metode === 'QRIS') && $status === 'Verified'
            ? 'bukti_transfer_'.fake()->uuid().'.jpg'
            : null;

        $metadata = [];
        if ($metode === 'Transfer') {
            $metadata = [
                'bank_pengirim' => fake()->randomElement(['BCA', 'Mandiri', 'BRI', 'BNI']),
                'rekening_pengirim' => fake()->numerify('##########'),
                'nama_pengirim' => fake()->name(),
                'waktu_transfer' => $tanggalBayar,
            ];
        }

        return [
            'kode_pembayaran' => 'PBY'.str_pad((string) fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'transaksi_id' => $transaksi ? $transaksi->id : Transaksi::factory(),
            'jumlah_bayar' => $jumlahBayar,
            'kembalian' => $kembalian,
            'metode' => $metode,
            'status' => $status,
            'bukti_transfer' => $buktiTransfer,
            'tanggal_bayar' => $tanggalBayar,
            'verified_at' => $status === 'Verified' ? $tanggalBayar : null,
            'verified_by' => $status === 'Verified' ? (User::inRandomOrder()->first()?->id ?? User::factory()) : null,
            'catatan' => fake()->optional(0.2)->sentence(),
            'metadata' => $metadata,
        ];
    }
}
