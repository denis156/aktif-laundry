<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\JenisPakaian;
use App\Models\Kurir;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pengaturan;
use App\Models\Pengiriman;
use App\Models\Promo;
use App\Models\Referral;
use App\Models\Transaksi;
use App\Models\TransaksiLayanan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting comprehensive database seeding...');
        $this->command->newLine();

        // ! Urutan seed harus sesuai dependency
        // ! 1. Pengaturan - Setting untuk format_id_*, min_berat_kg, dll
        $this->seedPengaturan();

        // ! 2. JenisPakaian - 15 jenis pakaian
        $this->seedJenisPakaian();

        // ! 3. Layanan - 8 layanan (Cuci Kering, Setrika, Express, dll)
        $this->seedLayanan();

        // ! 4. User - 1 Super Admin + 2-3 Staf
        $this->seedUsers();

        // ! 5. Pelanggan - 30 pelanggan (50% dengan password, 50% tanpa)
        $this->seedPelanggan();

        // ! 6. Kurir - 5 kurir aktif
        $this->seedKurir();

        // ! 7. Promo - 7 promo (mix semua tipe)
        $this->seedPromo();

        // ! 8. Referral - 5 referral dari pelanggan
        $this->seedReferral();

        // ! 9. Transaksi + TransaksiLayanan - 50 transaksi dengan relasi lengkap
        $this->seedTransaksi();

        // ! 10. Pengiriman - Data jemput/antar untuk beberapa transaksi
        $this->seedPengiriman();

        // ! 11. Pembayaran - Data pembayaran untuk transaksi
        $this->seedPembayaran();

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('🔑 Login Credentials:');
        $this->command->line('   Email: admin@aktiflaundry.com');
        $this->command->line('   Password: password');
        $this->command->newLine();
    }

    /**
     * Seed Pengaturan table dengan data setting penting
     */
    private function seedPengaturan(): void
    {
        $this->command->info('🔧 Seeding Pengaturan...');

        // Create 20 pengaturan (all predefined settings dari factory)
        Pengaturan::factory()->count(20)->create();

        $this->command->info('   ✓ Created 20 pengaturan');
    }

    /**
     * Seed JenisPakaian table
     */
    private function seedJenisPakaian(): void
    {
        $this->command->info('👔 Seeding Jenis Pakaian...');

        // Create all 15 jenis pakaian dari factory
        JenisPakaian::factory()->count(15)->create();

        $this->command->info('   ✓ Created 15 jenis pakaian');
    }

    /**
     * Seed Layanan table
     */
    private function seedLayanan(): void
    {
        $this->command->info('🧺 Seeding Layanan...');

        // Create all 8 layanan dari factory
        Layanan::factory()->count(8)->create();

        $this->command->info('   ✓ Created 8 layanan');
    }

    /**
     * Seed Users (Super Admin + Staff)
     */
    private function seedUsers(): void
    {
        $this->command->info('👤 Seeding Users...');

        // Super Admin
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@aktiflaundry.com',
            'no_hp' => '81234567890',
            'password' => Hash::make('password'),
            'super_admin' => true,
            'metadata' => ['gaji' => 10000000],
        ]);

        // Staff Kasir
        User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@aktiflaundry.com',
            'no_hp' => '81234567891',
            'password' => Hash::make('password'),
            'super_admin' => false,
            'metadata' => ['gaji' => 5000000],
        ]);

        // Staff Admin
        User::factory()->create([
            'name' => 'Siti Aminah',
            'email' => 'siti@aktiflaundry.com',
            'no_hp' => '81234567892',
            'password' => Hash::make('password'),
            'super_admin' => false,
            'metadata' => ['gaji' => 5500000],
        ]);

        $this->command->info('   ✓ Created 3 users (1 Super Admin, 2 Staff)');
    }

    /**
     * Seed Pelanggan (50% dengan password, 50% tanpa)
     */
    private function seedPelanggan(): void
    {
        $this->command->info('👥 Seeding Pelanggan...');

        // 15 pelanggan dengan password (registered)
        Pelanggan::factory()->count(15)->withPassword()->create();

        // 15 pelanggan tanpa password (belum register)
        Pelanggan::factory()->count(15)->create();

        // 3 pelanggan loyal (untuk testing referral)
        Pelanggan::factory()->count(3)->loyal()->withPassword()->create();

        $this->command->info('   ✓ Created 33 pelanggan (18 registered, 15 unregistered, 3 loyal)');
    }

    /**
     * Seed Kurir
     */
    private function seedKurir(): void
    {
        $this->command->info('🏍️  Seeding Kurir...');

        // 3 kurir motor aktif
        Kurir::factory()->count(3)->create([
            'jenis_kendaraan' => 'Motor',
            'status' => 'Aktif',
        ]);

        // 2 kurir mobil aktif
        Kurir::factory()->count(2)->create([
            'jenis_kendaraan' => 'Mobil',
            'status' => 'Aktif',
        ]);

        // 1 kurir tidak aktif
        Kurir::factory()->inactive()->create();

        $this->command->info('   ✓ Created 6 kurir (5 aktif, 1 tidak aktif)');
    }

    /**
     * Seed Promo (mix semua tipe)
     */
    private function seedPromo(): void
    {
        $this->command->info('🎁 Seeding Promo...');

        // 1 promo persen aktif
        Promo::factory()->active()->create([
            'kode_promo' => 'WELCOME10',
            'nama_promo' => 'Diskon Pelanggan Baru',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 10,
            'berlaku_untuk' => 'pelanggan_baru',
        ]);

        // 1 promo nominal aktif
        Promo::factory()->active()->create([
            'kode_promo' => 'HEMAT20K',
            'nama_promo' => 'Hemat 20 Ribu',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 20000,
            'min_transaksi' => 100000,
        ]);

        // 1 promo gratis_kg aktif
        Promo::factory()->active()->create([
            'kode_promo' => 'GRATIS2KG',
            'nama_promo' => 'Gratis 2 Kg',
            'tipe_diskon' => 'gratis_kg',
            'nilai_diskon' => 2,
            'min_transaksi' => 50000,
        ]);

        // 1 promo gratis_hari aktif
        Promo::factory()->active()->create([
            'kode_promo' => 'GRATISHARI',
            'nama_promo' => 'Gratis 1 Hari Express',
            'tipe_diskon' => 'gratis_hari',
            'nilai_diskon' => 1,
        ]);

        // 1 promo cashback aktif
        Promo::factory()->active()->create([
            'kode_promo' => 'CASHBACK15K',
            'nama_promo' => 'Cashback 15 Ribu',
            'tipe_diskon' => 'cashback',
            'nilai_diskon' => 15000,
            'min_transaksi' => 75000,
        ]);

        // 2 promo tidak aktif atau expired
        Promo::factory()->inactive()->create();
        Promo::factory()->expired()->create();

        $this->command->info('   ✓ Created 7 promo (5 aktif, 2 tidak aktif)');
    }

    /**
     * Seed Referral
     */
    private function seedReferral(): void
    {
        $this->command->info('🔗 Seeding Referral...');

        // Ambil 5 pelanggan loyal untuk dijadikan referrer
        $pelangganLoyals = Pelanggan::where('total_transaksi', '>', 0)->take(5)->get();

        foreach ($pelangganLoyals as $pelanggan) {
            Referral::factory()->create([
                'pelanggan_id' => $pelanggan->id,
                'status' => 'Aktif',
            ]);
        }

        $this->command->info('   ✓ Created 5 referral codes');
    }

    /**
     * Seed Transaksi + TransaksiLayanan
     */
    private function seedTransaksi(): void
    {
        $this->command->info('📦 Seeding Transaksi + Transaksi Layanan...');

        $pelanggan = Pelanggan::all();
        $kasir = User::all(); // All users can be kasir
        $layanan = Layanan::all();
        $promoAktif = Promo::where('status', 'Aktif')->get();

        $transaksiCount = 0;
        $transaksiLayananCount = 0;

        // Create 50 transaksi
        for ($i = 0; $i < 50; $i++) {
            $selectedPelanggan = $pelanggan->random();
            $selectedKasir = $kasir->random();

            // Random 1-3 layanan per transaksi
            $jumlahLayanan = fake()->numberBetween(1, 3);
            $selectedLayanan = $layanan->random($jumlahLayanan);

            // Hitung subtotal dari semua layanan
            $subtotal = 0;
            $totalBerat = 0;
            $totalItem = 0;
            $transaksLayananData = [];

            foreach ($selectedLayanan as $lay) {
                if ($lay->tipe_layanan === 'per_kg') {
                    $berat = fake()->randomFloat(2, 2, 10);
                    $harga = $lay->harga_per_kg * $berat;
                    $totalBerat += $berat;

                    // Generate jenis pakaian
                    $jenisPakaianList = ['Kemeja', 'Celana Panjang', 'Kaos', 'Rok', 'Handuk'];
                    $jumlahJenis = fake()->numberBetween(2, 4);
                    $jenisPakaian = [];
                    for ($j = 0; $j < $jumlahJenis; $j++) {
                        $jenisPakaian[] = [
                            'nama' => fake()->randomElement($jenisPakaianList),
                            'jumlah' => fake()->numberBetween(1, 5),
                        ];
                    }

                    $transaksLayananData[] = [
                        'layanan' => $lay,
                        'berat_kg' => $berat,
                        'harga_per_kg' => $lay->harga_per_kg,
                        'jumlah_satuan' => null,
                        'harga_per_satuan' => null,
                        'subtotal' => (int) $harga,
                        'jenis_pakaian' => $jenisPakaian,
                    ];
                } else {
                    $jumlah = fake()->numberBetween(1, 3);
                    $harga = $lay->harga_per_satuan * $jumlah;
                    $totalItem += $jumlah;

                    $transaksLayananData[] = [
                        'layanan' => $lay,
                        'berat_kg' => null,
                        'harga_per_kg' => null,
                        'jumlah_satuan' => $jumlah,
                        'harga_per_satuan' => $lay->harga_per_satuan,
                        'subtotal' => (int) $harga,
                        'jenis_pakaian' => null,
                    ];
                }

                $subtotal += (int) $harga;
            }

            // Random promo (30% chance)
            $promo = null;
            $diskon = 0;
            if (fake()->boolean(30) && $promoAktif->count() > 0) {
                $promo = $promoAktif->random();

                // Simple diskon calculation
                if ($promo->tipe_diskon === 'persen') {
                    $diskon = (int) ($subtotal * $promo->nilai_diskon / 100);
                    if ($promo->diskon_maksimal && $diskon > $promo->diskon_maksimal) {
                        $diskon = $promo->diskon_maksimal;
                    }
                } elseif ($promo->tipe_diskon === 'nominal') {
                    $diskon = $promo->nilai_diskon;
                }
            }

            $total = $subtotal - $diskon;

            // Create transaksi
            $tanggalMasuk = fake()->dateTimeBetween('-1 month', 'now');
            $durasiJam = $selectedLayanan->max('durasi_jam') ?? 24;
            $tanggalSelesai = (clone $tanggalMasuk)->modify("+{$durasiJam} hours");

            $transaksi = Transaksi::create([
                'kode_transaksi' => 'TRX'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'tanggal_masuk' => $tanggalMasuk,
                'kasir_id' => $selectedKasir->id,
                'pelanggan_id' => $selectedPelanggan->id,
                'nama_pelanggan' => $selectedPelanggan->nama,
                'promo_id' => $promo?->id,
                'total_berat' => $totalBerat,
                'total_item' => $totalItem,
                'jumlah_layanan' => $jumlahLayanan,
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'total' => $total,
                'metode_pembayaran' => fake()->randomElement(['Tunai', 'Transfer', 'QRIS', 'Debit', 'E-Wallet']),
                'tanggal_selesai' => $tanggalSelesai,
                'status' => fake()->randomElement(['Menunggu', 'Proses', 'Selesai', 'Diambil']),
                'catatan' => fake()->optional(0.3)->sentence(),
            ]);

            $transaksiCount++;

            // Create transaksi layanan
            foreach ($transaksLayananData as $tl) {
                TransaksiLayanan::create([
                    'transaksi_id' => $transaksi->id,
                    'layanan_id' => $tl['layanan']->id,
                    'nama_layanan' => $tl['layanan']->nama_layanan,
                    'jenis_pakaian' => $tl['jenis_pakaian'],
                    'berat_kg' => $tl['berat_kg'],
                    'harga_per_kg' => $tl['harga_per_kg'],
                    'jumlah_satuan' => $tl['jumlah_satuan'],
                    'harga_per_satuan' => $tl['harga_per_satuan'],
                    'subtotal' => $tl['subtotal'],
                ]);
                $transaksiLayananCount++;
            }

            // Update total_transaksi pelanggan
            $selectedPelanggan->increment('total_transaksi');
        }

        $this->command->info("   ✓ Created {$transaksiCount} transaksi");
        $this->command->info("   ✓ Created {$transaksiLayananCount} transaksi layanan");
    }

    /**
     * Seed Pengiriman (jemput/antar untuk beberapa transaksi)
     */
    private function seedPengiriman(): void
    {
        $this->command->info('🚚 Seeding Pengiriman...');

        $transaksi = Transaksi::all();
        $kurir = Kurir::where('status', 'Aktif')->get();

        $count = 0;

        // 40% transaksi punya pengiriman jemput
        foreach ($transaksi->random((int) ($transaksi->count() * 0.4)) as $trx) {
            $selectedKurir = $kurir->random();

            Pengiriman::factory()->create([
                'transaksi_id' => $trx->id,
                'kurir_id' => $selectedKurir->id,
                'tipe' => 'Jemput',
                'alamat_tujuan' => $trx->pelanggan->alamat ?? fake()->address(),
                'status' => fake()->randomElement(['Selesai', 'Selesai', 'Dalam Perjalanan']),
            ]);

            $count++;
        }

        // 30% transaksi punya pengiriman antar
        foreach ($transaksi->random((int) ($transaksi->count() * 0.3)) as $trx) {
            $selectedKurir = $kurir->random();

            Pengiriman::factory()->create([
                'transaksi_id' => $trx->id,
                'kurir_id' => $selectedKurir->id,
                'tipe' => 'Antar',
                'alamat_tujuan' => $trx->pelanggan->alamat ?? fake()->address(),
                'status' => fake()->randomElement(['Dijadwalkan', 'Selesai']),
            ]);

            $count++;
        }

        $this->command->info("   ✓ Created {$count} pengiriman");
    }

    /**
     * Seed Pembayaran
     */
    private function seedPembayaran(): void
    {
        $this->command->info('💰 Seeding Pembayaran...');

        $transaksi = Transaksi::all();

        $count = 0;

        // 90% transaksi punya data pembayaran
        foreach ($transaksi->random((int) ($transaksi->count() * 0.9)) as $trx) {
            Pembayaran::factory()->create([
                'transaksi_id' => $trx->id,
                'jumlah_bayar' => $trx->total + fake()->randomElement([0, 0, 5000, 10000]),
                'kembalian' => fake()->numberBetween(0, 10000),
                'metode' => $trx->metode_pembayaran,
                'status' => 'Verified',
                'tanggal_bayar' => $trx->tanggal_masuk,
            ]);

            $count++;
        }

        $this->command->info("   ✓ Created {$count} pembayaran");
    }
}
