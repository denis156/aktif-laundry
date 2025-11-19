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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        $this->command->newLine();

        // 1. Seed Users (Admin & Kasir)
        $this->command->info('👥 Seeding Users...');
        $admin = User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'admin@aktiflaundry.com',
        ]);

        User::factory()->create([
            'name' => 'Kasir 1',
            'email' => 'kasir1@aktiflaundry.com',
        ]);

        User::factory()->create([
            'name' => 'Kasir 2',
            'email' => 'kasir2@aktiflaundry.com',
        ]);

        User::factory()->count(3)->create(); // 3 kasir tambahan
        $this->command->info('   ✓ Created 6 users (1 admin + 5 kasir)');

        // 2. Seed Pengaturan (Settings)
        $this->command->info('⚙️  Seeding Pengaturan...');
        Pengaturan::factory()->count(14)->create(); // 14 default settings
        $this->command->info('   ✓ Created 14 application settings');

        // 3. Seed Jenis Pakaian
        $this->command->info('👕 Seeding Jenis Pakaian...');
        JenisPakaian::factory()->count(15)->create();
        $this->command->info('   ✓ Created 15 jenis pakaian');

        // 4. Seed Layanan
        $this->command->info('🧺 Seeding Layanan...');
        Layanan::factory()->count(8)->create();
        $this->command->info('   ✓ Created 8 layanan (per kg & per satuan)');

        // 5. Seed Pelanggan
        $this->command->info('👤 Seeding Pelanggan...');
        Pelanggan::factory()->count(30)->create();
        $this->command->info('   ✓ Created 30 pelanggan');

        // 6. Seed Kurir
        $this->command->info('🏍️  Seeding Kurir...');
        Kurir::factory()->count(5)->create();
        $this->command->info('   ✓ Created 5 kurir');

        // 7. Seed Promo
        $this->command->info('🎉 Seeding Promo...');
        Promo::factory()->count(5)->create();
        $this->command->info('   ✓ Created 5 promo codes');

        // 8. Seed Referral (untuk beberapa pelanggan)
        $this->command->info('🔗 Seeding Referral...');
        $pelangganWithReferral = Pelanggan::inRandomOrder()->limit(10)->get();
        foreach ($pelangganWithReferral as $pelanggan) {
            Referral::factory()->create([
                'pelanggan_id' => $pelanggan->id,
            ]);
        }
        $this->command->info('   ✓ Created 10 referral codes');

        // 9. Seed Transaksi
        $this->command->info('📝 Seeding Transaksi...');
        $transaksiCount = 50;
        Transaksi::factory()->count($transaksiCount)->create();
        $this->command->info("   ✓ Created {$transaksiCount} transaksi");

        // 10. Seed Transaksi Layanan (detail transaksi)
        $this->command->info('📋 Seeding Transaksi Layanan...');
        $transaksiAll = Transaksi::all();
        $totalLayanan = 0;

        foreach ($transaksiAll as $transaksi) {
            $jumlahLayanan = fake()->numberBetween(1, 3);
            $subtotalTransaksi = 0;
            $totalBerat = 0;
            $totalItem = 0;

            for ($i = 0; $i < $jumlahLayanan; $i++) {
                $transaksiLayanan = TransaksiLayanan::factory()->create([
                    'transaksi_id' => $transaksi->id,
                ]);

                $subtotalTransaksi += $transaksiLayanan->subtotal;
                if ($transaksiLayanan->berat_kg) {
                    $totalBerat += $transaksiLayanan->berat_kg;
                }
                if ($transaksiLayanan->jumlah_satuan) {
                    $totalItem += $transaksiLayanan->jumlah_satuan;
                }
                $totalLayanan++;
            }

            // Update transaksi dengan nilai yang benar
            $diskon = $transaksi->diskon;
            $transaksi->update([
                'jumlah_layanan' => $jumlahLayanan,
                'subtotal' => $subtotalTransaksi,
                'total' => $subtotalTransaksi - $diskon,
                'total_berat' => $totalBerat,
                'total_item' => $totalItem,
            ]);
        }
        $this->command->info("   ✓ Created {$totalLayanan} transaksi layanan details");

        // 11. Seed Pembayaran (untuk transaksi yang sudah selesai/diambil)
        $this->command->info('💰 Seeding Pembayaran...');
        $transaksiDibayar = Transaksi::whereIn('status', ['Selesai', 'Diambil'])
            ->inRandomOrder()
            ->limit(30)
            ->get();

        foreach ($transaksiDibayar as $transaksi) {
            Pembayaran::factory()->create([
                'transaksi_id' => $transaksi->id,
                'status' => 'Verified',
            ]);
        }
        $this->command->info('   ✓ Created ' . $transaksiDibayar->count() . ' pembayaran records');

        // 12. Seed Pengiriman (untuk beberapa transaksi)
        $this->command->info('🚚 Seeding Pengiriman...');
        $transaksiForPengiriman = Transaksi::inRandomOrder()->limit(20)->get();

        foreach ($transaksiForPengiriman as $transaksi) {
            // Jemput
            Pengiriman::factory()->create([
                'transaksi_id' => $transaksi->id,
                'tipe' => 'Jemput',
                'status' => 'Selesai',
            ]);

            // Antar (jika transaksi sudah selesai)
            if (in_array($transaksi->status, ['Selesai', 'Diambil'])) {
                Pengiriman::factory()->create([
                    'transaksi_id' => $transaksi->id,
                    'tipe' => 'Antar',
                    'status' => fake()->randomElement(['Dijadwalkan', 'Selesai']),
                ]);
            }
        }
        $this->command->info('   ✓ Created ~30 pengiriman records');

        // Update total transaksi pelanggan
        $this->command->info('🔄 Updating pelanggan statistics...');
        $pelangganAll = Pelanggan::withCount('transaksi')->get();
        foreach ($pelangganAll as $pelanggan) {
            $pelanggan->update([
                'total_transaksi' => $pelanggan->transaksi_count,
            ]);
        }
        $this->command->info('   ✓ Updated pelanggan total_transaksi');

        // Update kurir statistics
        $this->command->info('🔄 Updating kurir statistics...');
        $kurirAll = Kurir::all();
        foreach ($kurirAll as $kurir) {
            $totalJemput = Pengiriman::where('kurir_id', $kurir->id)
                ->where('tipe', 'Jemput')
                ->count();

            $totalAntar = Pengiriman::where('kurir_id', $kurir->id)
                ->where('tipe', 'Antar')
                ->count();

            $kurir->update([
                'total_jemput' => $totalJemput,
                'total_antar' => $totalAntar,
            ]);
        }
        $this->command->info('   ✓ Updated kurir statistics');

        $this->command->newLine();
        $this->command->info('✅ Database seeded successfully!');
        $this->command->newLine();
        $this->showSummary();
    }

    /**
     * Show seeding summary
     */
    protected function showSummary(): void
    {
        $this->command->info('📊 Seeding Summary:');
        $this->command->table(
            ['Model', 'Count'],
            [
                ['Users', User::count()],
                ['Pengaturan', Pengaturan::count()],
                ['Jenis Pakaian', JenisPakaian::count()],
                ['Layanan', Layanan::count()],
                ['Pelanggan', Pelanggan::count()],
                ['Kurir', Kurir::count()],
                ['Promo', Promo::count()],
                ['Referral', Referral::count()],
                ['Transaksi', Transaksi::count()],
                ['Transaksi Layanan', TransaksiLayanan::count()],
                ['Pembayaran', Pembayaran::count()],
                ['Pengiriman', Pengiriman::count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('🔑 Login Credentials:');
        $this->command->line('   Admin: admin@aktiflaundry.com / password');
        $this->command->line('   Kasir: kasir1@aktiflaundry.com / password');
        $this->command->newLine();
    }
}
