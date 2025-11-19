<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Pelanggan;
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
        static $counter = 1;

        // Ambil pelanggan dan kasir random
        $pelanggan = Pelanggan::inRandomOrder()->first();
        $kasir = User::inRandomOrder()->first();

        $tanggalMasuk = fake()->dateTimeBetween('-1 month', 'now');

        // Multi-layanan akan di-create terpisah via TransaksiLayananFactory
        // Di sini kita hanya buat header transaksi dengan default values
        $jumlahLayanan = fake()->numberBetween(1, 3);
        $subtotal = fake()->numberBetween(30000, 200000);
        $diskon = fake()->randomElement([0, 0, 0, 5000, 10000, 20000]);
        $total = $subtotal - $diskon;

        // Estimasi berat dan item (akan di-update saat TransaksiLayanan dibuat)
        $totalBerat = fake()->randomFloat(2, 2, 10);
        $totalItem = fake()->numberBetween(1, 10);

        // Hitung tanggal selesai (estimasi 24-48 jam)
        $durasiJam = fake()->numberBetween(24, 72);
        $tanggalSelesai = (clone $tanggalMasuk)->modify("+{$durasiJam} hours");

        return [
            'kode_transaksi' => 'TRX' . str_pad((string) $counter++, 3, '0', STR_PAD_LEFT),
            'tanggal_masuk' => $tanggalMasuk,
            'kasir_id' => $kasir ? $kasir->id : User::factory(),
            'pelanggan_id' => $pelanggan ? $pelanggan->id : Pelanggan::factory(),
            'nama_pelanggan' => $pelanggan ? $pelanggan->nama : fake()->name(),
            'total_berat' => $totalBerat,
            'total_item' => $totalItem,
            'jumlah_layanan' => $jumlahLayanan,
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'total' => $total,
            'metode_pembayaran' => fake()->randomElement(['Tunai', 'Non-Tunai']),
            'tanggal_selesai' => $tanggalSelesai,
            'status' => fake()->randomElement(['Menunggu', 'Proses', 'Selesai', 'Diambil']),
            'catatan' => fake()->optional(0.3)->sentence(),
            'metadata' => [
                'foto_bukti_timbangan' => fake()->optional(0.2)->imageUrl(640, 480, 'scale'),
                'foto_bukti_pembayaran' => fake()->optional(0.3)->imageUrl(640, 480, 'payment'),
                'kurir_jemput' => fake()->optional(0.3)->name(),
                'kurir_antar' => fake()->optional(0.3)->name(),
            ],
        ];
    }
}
