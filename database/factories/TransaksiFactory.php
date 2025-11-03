<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use App\Models\Layanan;
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

        // Ambil pelanggan dan layanan random
        $pelanggan = Pelanggan::inRandomOrder()->first();
        $layanan = Layanan::inRandomOrder()->first();
        $kasir = User::inRandomOrder()->first();

        // Generate jenis pakaian random
        $jenisPakaianList = ['Kemeja', 'Celana Panjang', 'Celana Pendek', 'Kaos', 'Rok', 'Dress', 'Jaket', 'Handuk', 'Sprei', 'Selimut', 'Sarung'];
        $jumlahJenis = fake()->numberBetween(1, 3);
        $jenisPakaian = [];

        for ($i = 0; $i < $jumlahJenis; $i++) {
            $jenisPakaian[] = [
                'nama' => fake()->randomElement($jenisPakaianList),
                'jumlah' => fake()->numberBetween(1, 5),
            ];
        }

        $tanggalMasuk = fake()->dateTimeBetween('-1 month', 'now');
        $beratKg = fake()->randomFloat(2, 1, 10);
        $hargaPerKg = $layanan ? $layanan->harga_per_kg : 5000;
        $subtotal = $beratKg * $hargaPerKg;
        $diskon = fake()->randomElement([0, 0, 0, 2000, 5000, 10000]);
        $total = $subtotal - $diskon;

        // Hitung tanggal selesai berdasarkan durasi layanan
        $durasiJam = $layanan ? $layanan->durasi_jam : 24;
        $tanggalSelesai = (clone $tanggalMasuk)->modify("+{$durasiJam} hours");

        return [
            'kode_transaksi' => 'TRX' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
            'tanggal_masuk' => $tanggalMasuk,
            'kasir_id' => $kasir ? $kasir->id : 1,
            'pelanggan_id' => $pelanggan ? $pelanggan->id : 1,
            'nama_pelanggan' => $pelanggan ? $pelanggan->nama : 'Guest',
            'layanan_id' => $layanan ? $layanan->id : 1,
            'nama_layanan' => $layanan ? $layanan->nama_layanan : 'Cuci Kering',
            'jenis_pakaian' => $jenisPakaian,
            'berat_kg' => $beratKg,
            'harga_per_kg' => $hargaPerKg,
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'total' => $total,
            'metode_pembayaran' => fake()->randomElement(['Tunai', 'Transfer', 'QRIS', 'Debit']),
            'tanggal_selesai' => $tanggalSelesai,
            'status' => fake()->randomElement(['Menunggu', 'Proses', 'Selesai', 'Diambil']),
            'catatan' => fake()->optional(0.3)->sentence(),
        ];
    }
}
