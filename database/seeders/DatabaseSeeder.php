<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Helper\Database\TransaksiHelper;
use App\Models\JenisPakaian;
use App\Models\Kurir;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Pengaturan;
use App\Models\Pengiriman;
use App\Models\Promo;
use App\Models\Transaksi;
use App\Models\TransaksiLayanan;
use App\Models\TransaksiPromo;
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
        $this->command->info('🌱 Starting database seeding...');
        $this->command->newLine();

        // ! 1. Pengaturan (MUST BE FIRST - needed by Helpers and Observers)
        $this->seedPengaturan();

        // ! 2. Master Data (no dependencies)
        $this->seedJenisPakaian();
        $this->seedLayanan();

        // ! 3. Users (Staff & Kasir)
        $this->seedUsers();

        // ! 4. Pelanggan (Observer will auto-create Referral if enabled)
        $this->seedPelanggan();

        // ! 5. Kurir
        $this->seedKurir();

        // ! 6. Promo (needed for Transaksi)
        $this->seedPromo();

        // ! 7. Transaksi + TransaksiLayanan + TransaksiPromo
        $this->seedTransaksi();

        // ! 8. Pengiriman
        $this->seedPengiriman();

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed!');
        $this->command->newLine();
        $this->command->info('🔑 Default Credentials:');
        $this->command->line('   Super Admin: admin@aktiflaundry.com / password');
        $this->command->line('   Staff: budi@aktiflaundry.com / password');
        $this->command->newLine();
    }

    /**
     * Seed Pengaturan (CRITICAL - must be first)
     */
    private function seedPengaturan(): void
    {
        $this->command->info('⚙️  Seeding Pengaturan...');

        // Create all required settings
        Pengaturan::factory()->count(25)->create();

        $this->command->info('   ✓ Created 25 pengaturan');
    }

    /**
     * Seed JenisPakaian
     */
    private function seedJenisPakaian(): void
    {
        $this->command->info('👔 Seeding Jenis Pakaian...');

        $jenisPakaianData = [
            ['kode_jenis' => 'JNS001', 'nama_jenis' => 'Kemeja', 'keterangan' => 'Kemeja lengan panjang dan pendek', 'penanganan_khusus' => null, 'icon' => null],
            ['kode_jenis' => 'JNS002', 'nama_jenis' => 'Celana Panjang', 'keterangan' => 'Celana panjang reguler', 'penanganan_khusus' => null, 'icon' => null],
            ['kode_jenis' => 'JNS003', 'nama_jenis' => 'Celana Pendek', 'keterangan' => 'Celana pendek dan training', 'penanganan_khusus' => null, 'icon' => null],
            ['kode_jenis' => 'JNS004', 'nama_jenis' => 'Kaos', 'keterangan' => 'Kaos oblong dan t-shirt', 'penanganan_khusus' => null, 'icon' => null],
            ['kode_jenis' => 'JNS005', 'nama_jenis' => 'Rok', 'keterangan' => 'Rok panjang dan pendek', 'penanganan_khusus' => null, 'icon' => null],
            ['kode_jenis' => 'JNS006', 'nama_jenis' => 'Dress', 'keterangan' => 'Gaun dan terusan', 'penanganan_khusus' => 'Hati-hati dengan aksesoris', 'icon' => null],
            ['kode_jenis' => 'JNS007', 'nama_jenis' => 'Jaket', 'keterangan' => 'Jaket dan hoodie', 'penanganan_khusus' => null, 'icon' => null],
            ['kode_jenis' => 'JNS008', 'nama_jenis' => 'Jas', 'keterangan' => 'Jas formal', 'penanganan_khusus' => 'Gunakan laundry khusus jas', 'icon' => null],
            ['kode_jenis' => 'JNS009', 'nama_jenis' => 'Kebaya', 'keterangan' => 'Kebaya dan pakaian adat', 'penanganan_khusus' => 'Hati-hati dengan bordir dan payet', 'icon' => null],
            ['kode_jenis' => 'JNS010', 'nama_jenis' => 'Handuk', 'keterangan' => 'Handuk mandi', 'penanganan_khusus' => null, 'icon' => null],
            ['kode_jenis' => 'JNS011', 'nama_jenis' => 'Sprei', 'keterangan' => 'Sprei dan bed cover', 'penanganan_khusus' => null, 'icon' => null],
            ['kode_jenis' => 'JNS012', 'nama_jenis' => 'Selimut', 'keterangan' => 'Selimut dan blanket', 'penanganan_khusus' => null, 'icon' => null],
            ['kode_jenis' => 'JNS013', 'nama_jenis' => 'Sarung', 'keterangan' => 'Sarung dan sarung bantal', 'penanganan_khusus' => null, 'icon' => null],
            ['kode_jenis' => 'JNS014', 'nama_jenis' => 'Gordyn', 'keterangan' => 'Gorden jendela', 'penanganan_khusus' => null, 'icon' => null],
            ['kode_jenis' => 'JNS015', 'nama_jenis' => 'Mukena', 'keterangan' => 'Mukena dan perlengkapan sholat', 'penanganan_khusus' => 'Hati-hati dengan bordir', 'icon' => null],
        ];

        foreach ($jenisPakaianData as $data) {
            JenisPakaian::create(array_merge($data, ['status' => 'Aktif']));
        }

        $this->command->info('   ✓ Created 15 jenis pakaian');
    }

    /**
     * Seed Layanan
     */
    private function seedLayanan(): void
    {
        $this->command->info('🧺 Seeding Layanan...');

        $layananData = [
            [
                'kode_layanan' => 'LYN001',
                'nama_layanan' => 'Cuci Kering',
                'tipe_layanan' => 'per_kg',
                'harga_per_kg' => 5000,
                'harga_per_satuan' => null,
                'satuan' => 'kg',
                'durasi_jam' => 24,
                'deskripsi' => 'Cuci dan kering saja',
                'is_popular' => true,
                'icon' => null,
                'include' => ['Cuci', 'Kering'],
                'exclude' => ['Setrika'],
            ],
            [
                'kode_layanan' => 'LYN002',
                'nama_layanan' => 'Cuci Setrika',
                'tipe_layanan' => 'per_kg',
                'harga_per_kg' => 7000,
                'harga_per_satuan' => null,
                'satuan' => 'kg',
                'durasi_jam' => 48,
                'deskripsi' => 'Cuci, kering, dan setrika rapi',
                'is_popular' => true,
                'icon' => null,
                'include' => ['Cuci', 'Kering', 'Setrika', 'Lipat'],
                'exclude' => [],
            ],
            [
                'kode_layanan' => 'LYN003',
                'nama_layanan' => 'Setrika Saja',
                'tipe_layanan' => 'per_kg',
                'harga_per_kg' => 4000,
                'harga_per_satuan' => null,
                'satuan' => 'kg',
                'durasi_jam' => 12,
                'deskripsi' => 'Setrika saja tanpa cuci',
                'is_popular' => false,
                'icon' => null,
                'include' => ['Setrika', 'Lipat'],
                'exclude' => [],
            ],
            [
                'kode_layanan' => 'LYN004',
                'nama_layanan' => 'Express 6 Jam',
                'tipe_layanan' => 'per_kg',
                'harga_per_kg' => 12000,
                'harga_per_satuan' => null,
                'satuan' => 'kg',
                'durasi_jam' => 6,
                'deskripsi' => 'Layanan kilat 6 jam jadi',
                'is_popular' => true,
                'icon' => null,
                'include' => ['Cuci', 'Kering', 'Setrika', 'Lipat'],
                'exclude' => [],
            ],
            [
                'kode_layanan' => 'LYN005',
                'nama_layanan' => 'Express 12 Jam',
                'tipe_layanan' => 'per_kg',
                'harga_per_kg' => 10000,
                'harga_per_satuan' => null,
                'satuan' => 'kg',
                'durasi_jam' => 12,
                'deskripsi' => 'Layanan kilat 12 jam jadi',
                'is_popular' => false,
                'icon' => null,
                'include' => ['Cuci', 'Kering', 'Setrika', 'Lipat'],
                'exclude' => [],
            ],
            [
                'kode_layanan' => 'LYN006',
                'nama_layanan' => 'Cuci Karpet',
                'tipe_layanan' => 'per_satuan',
                'harga_per_kg' => 0,
                'harga_per_satuan' => 150000,
                'satuan' => 'pcs',
                'durasi_jam' => 72,
                'deskripsi' => 'Cuci karpet dan permadani per keping',
                'is_popular' => false,
                'icon' => null,
                'min_order' => 1,
                'include' => [],
                'exclude' => [],
            ],
            [
                'kode_layanan' => 'LYN007',
                'nama_layanan' => 'Cuci Sepatu',
                'tipe_layanan' => 'per_satuan',
                'harga_per_kg' => 0,
                'harga_per_satuan' => 25000,
                'satuan' => 'pasang',
                'durasi_jam' => 48,
                'deskripsi' => 'Cuci sepatu sneakers per pasang',
                'is_popular' => true,
                'icon' => null,
                'min_order' => 1,
                'include' => [],
                'exclude' => [],
            ],
            [
                'kode_layanan' => 'LYN008',
                'nama_layanan' => 'Cuci Boneka',
                'tipe_layanan' => 'per_satuan',
                'harga_per_kg' => 0,
                'harga_per_satuan' => 20000,
                'satuan' => 'pcs',
                'durasi_jam' => 24,
                'deskripsi' => 'Cuci boneka dan mainan per item',
                'is_popular' => false,
                'icon' => null,
                'max_order' => 10,
                'include' => [],
                'exclude' => [],
            ],
        ];

        foreach ($layananData as $data) {
            Layanan::create(array_merge($data, ['status' => 'Aktif']));
        }

        $this->command->info('   ✓ Created 8 layanan');
    }

    /**
     * Seed Users (Super Admin + Staff)
     */
    private function seedUsers(): void
    {
        $this->command->info('👤 Seeding Users...');

        // Super Admin (UserObserver will generate kode_user and avatar_url)
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@aktiflaundry.com',
            'no_hp' => '81234567890',
            'password' => Hash::make('password'),
            'super_admin' => true,
            'gaji' => 10000000,
            'jam_masuk' => '08:00',
            'jam_keluar' => '17:00',
            'alamat' => 'Kendari, Sulawesi Tenggara',
        ]);

        // Staff Kasir
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@aktiflaundry.com',
            'no_hp' => '81234567891',
            'password' => Hash::make('password'),
            'super_admin' => false,
            'gaji' => 5000000,
            'jam_masuk' => '08:00',
            'jam_keluar' => '20:00',
            'alamat' => 'Kendari, Sulawesi Tenggara',
        ]);

        // Staff Admin
        User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@aktiflaundry.com',
            'no_hp' => '81234567892',
            'password' => Hash::make('password'),
            'super_admin' => false,
            'gaji' => 5500000,
            'jam_masuk' => '09:00',
            'jam_keluar' => '21:00',
            'alamat' => 'Kendari, Sulawesi Tenggara',
        ]);

        $this->command->info('   ✓ Created 3 users');
    }

    /**
     * Seed Pelanggan (Observer will auto-create Referral if enabled)
     */
    private function seedPelanggan(): void
    {
        $this->command->info('👥 Seeding Pelanggan...');

        $counter = 1;

        // 15 pelanggan registered
        for ($i = 0; $i < 15; $i++) {
            Pelanggan::factory()->registered()->create([
                'kode_pelanggan' => 'PLG'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT),
            ]);
            $counter++;
        }

        // 15 pelanggan belum register
        for ($i = 0; $i < 15; $i++) {
            Pelanggan::factory()->create([
                'kode_pelanggan' => 'PLG'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT),
            ]);
            $counter++;
        }

        // 5 pelanggan loyal
        for ($i = 0; $i < 5; $i++) {
            Pelanggan::factory()->loyal()->registered()->create([
                'kode_pelanggan' => 'PLG'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT),
            ]);
            $counter++;
        }

        $this->command->info('   ✓ Created 35 pelanggan');
        $this->command->info('   ✓ Referral auto-created by Observer (if enabled)');
    }

    /**
     * Seed Kurir
     */
    private function seedKurir(): void
    {
        $this->command->info('🏍️  Seeding Kurir...');

        $counter = 1;

        // 3 kurir motor aktif
        for ($i = 0; $i < 3; $i++) {
            Kurir::factory()->create([
                'kode_kurir' => 'KUR'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT),
                'jenis_kendaraan' => 'Motor',
                'status' => 'Aktif',
            ]);
            $counter++;
        }

        // 2 kurir mobil aktif
        for ($i = 0; $i < 2; $i++) {
            Kurir::factory()->create([
                'kode_kurir' => 'KUR'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT),
                'jenis_kendaraan' => 'Mobil',
                'status' => 'Aktif',
            ]);
            $counter++;
        }

        // 1 kurir tidak aktif
        Kurir::factory()->inactive()->create([
            'kode_kurir' => 'KUR'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT),
        ]);

        $this->command->info('   ✓ Created 6 kurir');
    }

    /**
     * Seed Promo
     */
    private function seedPromo(): void
    {
        $this->command->info('🎁 Seeding Promo...');

        // 5 promo aktif dengan berbagai tipe
        Promo::factory()->active()->create([
            'kode_promo' => 'WELCOME10',
            'nama_promo' => 'Diskon Pelanggan Baru',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 10,
            'berlaku_untuk' => 'pelanggan_baru',
        ]);

        Promo::factory()->active()->create([
            'kode_promo' => 'HEMAT20K',
            'nama_promo' => 'Hemat 20 Ribu',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 20000,
            'min_transaksi' => 100000,
        ]);

        Promo::factory()->active()->create([
            'kode_promo' => 'GRATIS2KG',
            'nama_promo' => 'Gratis 2 Kg',
            'tipe_diskon' => 'gratis_kg',
            'nilai_diskon' => 2,
            'min_transaksi' => 50000,
        ]);

        Promo::factory()->active()->create([
            'kode_promo' => 'EXPRESS',
            'nama_promo' => 'Gratis Express',
            'tipe_diskon' => 'gratis_hari',
            'nilai_diskon' => 1,
        ]);

        Promo::factory()->active()->create([
            'kode_promo' => 'CASHBACK15K',
            'nama_promo' => 'Cashback 15 Ribu',
            'tipe_diskon' => 'cashback',
            'nilai_diskon' => 15000,
            'min_transaksi' => 75000,
        ]);

        // 2 promo tidak aktif
        Promo::factory()->inactive()->create(['kode_promo' => 'EXPIRED50K']);
        Promo::factory()->inactive()->create(['kode_promo' => 'OLD10']);

        $this->command->info('   ✓ Created 7 promo');
    }

    /**
     * Seed Transaksi + TransaksiLayanan + TransaksiPromo
     */
    private function seedTransaksi(): void
    {
        $this->command->info('📦 Seeding Transaksi...');

        $pelanggan = Pelanggan::all();
        $kasir = User::all();
        $layanan = Layanan::all();
        $promoAktif = Promo::where('status', 'Aktif')->get();

        $transaksiCount = 0;
        $transaksiLayananCount = 0;
        $transaksiPromoCount = 0;

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
                    $jenisPakaianAll = JenisPakaian::all();
                    $jumlahJenis = fake()->numberBetween(2, 4);
                    $jenisPakaian = [];

                    if ($jenisPakaianAll->isNotEmpty()) {
                        $selected = $jenisPakaianAll->random(min($jumlahJenis, $jenisPakaianAll->count()));
                        foreach ($selected as $jp) {
                            $jenisPakaian[] = [
                                'jenis_pakaian_id' => $jp->id,
                                'nama_jenis' => $jp->nama_jenis,
                                'jumlah' => fake()->numberBetween(1, 5),
                            ];
                        }
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

            $tanggalMasuk = fake()->dateTimeBetween('-1 month', 'now');
            $durasiJam = $selectedLayanan->max('durasi_jam') ?? 24;
            $tanggalSelesai = (clone $tanggalMasuk)->modify("+{$durasiJam} hours");

            // Create transaksi
            $transaksi = Transaksi::create([
                'kode_transaksi' => TransaksiHelper::generateKodeTransaksi(),
                'tanggal_masuk' => $tanggalMasuk,
                'kasir_id' => $selectedKasir->id,
                'pelanggan_id' => $selectedPelanggan->id,
                'nama_pelanggan' => $selectedPelanggan->nama,
                'total_berat' => $totalBerat,
                'total_item' => $totalItem,
                'jumlah_layanan' => $jumlahLayanan,
                'subtotal' => $subtotal,
                'total' => $total,
                'metode_pembayaran' => fake()->randomElement(['Bayar Saat Jemput', 'Bayar Saat Antar']),
                'tipe_bayar' => fake()->randomElement(['Tunai', 'Non-Tunai']),
                'status_bayar' => fake()->randomElement(['Belum Bayar', 'Sudah Bayar', 'Sudah Bayar']),
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

            // Create transaksi promo if promo used
            if ($promo) {
                $transaksiPromoData = [
                    'transaksi_id' => $transaksi->id,
                    'promo_id' => $promo->id,
                    'kode_promo' => $promo->kode_promo,
                    'nama_promo' => $promo->nama_promo,
                    'tipe_diskon' => $promo->tipe_diskon,
                    'diterapkan_ke' => 'subtotal',
                    'urutan_apply' => 1,
                ];

                // Set nilai diskon sesuai tipe
                if ($promo->tipe_diskon === 'persen') {
                    $transaksiPromoData['nilai_diskon_persen'] = $promo->nilai_diskon;
                    if ($promo->diskon_maksimal) {
                        $transaksiPromoData['diskon_maksimal'] = $promo->diskon_maksimal;
                    }
                } elseif ($promo->tipe_diskon === 'nominal') {
                    $transaksiPromoData['nilai_diskon_nominal'] = $promo->nilai_diskon;
                } elseif ($promo->tipe_diskon === 'gratis_kg') {
                    $transaksiPromoData['gratis_kg'] = $promo->nilai_diskon;
                } elseif ($promo->tipe_diskon === 'gratis_hari') {
                    $transaksiPromoData['gratis_hari'] = $promo->nilai_diskon;
                }

                TransaksiPromo::create($transaksiPromoData);
                $transaksiPromoCount++;
            }
        }

        $this->command->info("   ✓ Created {$transaksiCount} transaksi");
        $this->command->info("   ✓ Created {$transaksiLayananCount} transaksi layanan");
        $this->command->info("   ✓ Created {$transaksiPromoCount} transaksi promo");
    }

    /**
     * Seed Pengiriman
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
}
