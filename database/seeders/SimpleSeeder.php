<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\JenisPakaian;
use App\Models\Kurir;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SimpleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting simple database seeding...');
        $this->command->newLine();

        // 1. Create Jenis Pakaian
        $this->seedJenisPakaian();

        // 2. Create Layanan
        $this->seedLayanan();

        // 3. Create 1 Admin
        $this->seedAdmin();

        // 4. Create 2 Kurir
        $this->seedKurir();

        // 5. Create 2 Pelanggan
        $this->seedPelanggan();

        $this->command->newLine();
        $this->command->info('✅ Simple seeding completed!');
        $this->command->newLine();
        $this->command->info('🔑 Default Credentials:');
        $this->command->line('   Admin: admin@aktiflaundry.com / password');
        $this->command->line('   Kurir 1: kurir1@aktiflaundry.com / password');
        $this->command->line('   Kurir 2: kurir2@aktiflaundry.com / password');
        $this->command->line('   Pelanggan 1: pelanggan1@aktiflaundry.com / password');
        $this->command->line('   Pelanggan 2: pelanggan2@aktiflaundry.com / password');
        $this->command->newLine();
    }

    /**
     * Seed Jenis Pakaian
     */
    private function seedJenisPakaian(): void
    {
        $this->command->info('👔 Seeding Jenis Pakaian...');

        $jenisPakaianData = [
            ['kode_jenis' => 'JNS001', 'nama_jenis' => 'Kemeja', 'keterangan' => 'Kemeja lengan panjang dan pendek'],
            ['kode_jenis' => 'JNS002', 'nama_jenis' => 'Celana Panjang', 'keterangan' => 'Celana panjang reguler'],
            ['kode_jenis' => 'JNS003', 'nama_jenis' => 'Celana Pendek', 'keterangan' => 'Celana pendek dan training'],
            ['kode_jenis' => 'JNS004', 'nama_jenis' => 'Kaos', 'keterangan' => 'Kaos oblong dan t-shirt'],
            ['kode_jenis' => 'JNS005', 'nama_jenis' => 'Rok', 'keterangan' => 'Rok panjang dan pendek'],
        ];

        foreach ($jenisPakaianData as $data) {
            JenisPakaian::create(array_merge($data, ['status' => 'Aktif']));
        }

        $this->command->info('   ✓ Created 5 jenis pakaian');
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
                'include' => ['Cuci', 'Kering', 'Setrika', 'Lipat'],
                'exclude' => [],
            ],
            [
                'kode_layanan' => 'LYN003',
                'nama_layanan' => 'Express 6 Jam',
                'tipe_layanan' => 'per_kg',
                'harga_per_kg' => 12000,
                'harga_per_satuan' => null,
                'satuan' => 'kg',
                'durasi_jam' => 6,
                'deskripsi' => 'Layanan kilat 6 jam jadi',
                'is_popular' => true,
                'include' => ['Cuci', 'Kering', 'Setrika', 'Lipat'],
                'exclude' => [],
            ],
        ];

        foreach ($layananData as $data) {
            Layanan::create(array_merge($data, ['status' => 'Aktif']));
        }

        $this->command->info('   ✓ Created 3 layanan');
    }

    /**
     * Seed 1 Admin User
     */
    private function seedAdmin(): void
    {
        $this->command->info('👤 Seeding Admin...');

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
            'email_verified_at' => now(),
        ]);

        $this->command->info('   ✓ Created 1 admin');
    }

    /**
     * Seed 2 Kurir
     */
    private function seedKurir(): void
    {
        $this->command->info('🏍️  Seeding Kurir...');

        // Kurir 1
        Kurir::create([
            'kode_kurir' => 'KUR001',
            'nama' => 'Kurir Satu',
            'no_hp' => '81234567891',
            'email' => 'kurir1@aktiflaundry.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Ahmad Yani No. 45, Kel. Mandonga, Kec. Mandonga, Kota Kendari, Sulawesi Tenggara',
            'detail_alamat' => 'Jl. Ahmad Yani No. 45',
            'kelurahan' => 'Mandonga',
            'kecamatan' => 'Mandonga',
            'kabupaten_kota' => 'Kota Kendari',
            'provinsi' => 'Sulawesi Tenggara',
            'latitude' => -3.9689,
            'longitude' => 122.5129,
            'no_kendaraan' => 'DT 1234 AB',
            'jenis_kendaraan' => 'Motor',
            'tanggal_bergabung' => now()->subMonths(6),
            'status' => 'Aktif',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Kurir Satu',
            'emergency_contact_name' => 'Siti Kurir',
            'emergency_contact_phone' => '81298765432',
            'emergency_contact_relation' => 'Saudara',
        ]);

        // Kurir 2
        Kurir::create([
            'kode_kurir' => 'KUR002',
            'nama' => 'Kurir Dua',
            'no_hp' => '81234567892',
            'email' => 'kurir2@aktiflaundry.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Pattimura No. 12, Kel. Wua-Wua, Kec. Wua-Wua, Kota Kendari, Sulawesi Tenggara',
            'detail_alamat' => 'Jl. Pattimura No. 12',
            'kelurahan' => 'Wua-Wua',
            'kecamatan' => 'Wua-Wua',
            'kabupaten_kota' => 'Kota Kendari',
            'provinsi' => 'Sulawesi Tenggara',
            'latitude' => -3.9750,
            'longitude' => 122.5200,
            'no_kendaraan' => 'DT 5678 CD',
            'jenis_kendaraan' => 'Motor',
            'tanggal_bergabung' => now()->subMonths(3),
            'status' => 'Aktif',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '0987654321',
            'bank_account_name' => 'Kurir Dua',
            'emergency_contact_name' => 'Budi Kurir',
            'emergency_contact_phone' => '81298765433',
            'emergency_contact_relation' => 'Orang Tua',
        ]);

        $this->command->info('   ✓ Created 2 kurir');
    }

    /**
     * Seed 2 Pelanggan
     */
    private function seedPelanggan(): void
    {
        $this->command->info('👥 Seeding Pelanggan...');

        // Pelanggan 1
        Pelanggan::create([
            'kode_pelanggan' => 'PLG001',
            'nama' => 'Pelanggan Satu',
            'email' => 'pelanggan1@aktiflaundry.com',
            'no_hp' => '81234567893',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'alamat' => 'Jl. Haluoleo No. 10, Kel. Mandonga, Kec. Mandonga, Kota Kendari, Sulawesi Tenggara',
            'detail_alamat' => 'Jl. Haluoleo No. 10',
            'kelurahan' => 'Mandonga',
            'kecamatan' => 'Mandonga',
            'kabupaten_kota' => 'Kota Kendari',
            'provinsi' => 'Sulawesi Tenggara',
            'latitude' => -3.9689,
            'longitude' => 122.5129,
            'status' => 'Aktif',
            'loyalty_points' => 100,
            'tanggal_daftar' => now()->subMonths(2),
        ]);

        // Pelanggan 2
        Pelanggan::create([
            'kode_pelanggan' => 'PLG002',
            'nama' => 'Pelanggan Dua',
            'email' => 'pelanggan2@aktiflaundry.com',
            'no_hp' => '81234567894',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'alamat' => 'Jl. Sudirman No. 25, Kel. Poasia, Kec. Poasia, Kota Kendari, Sulawesi Tenggara',
            'detail_alamat' => 'Jl. Sudirman No. 25',
            'kelurahan' => 'Poasia',
            'kecamatan' => 'Poasia',
            'kabupaten_kota' => 'Kota Kendari',
            'provinsi' => 'Sulawesi Tenggara',
            'latitude' => -3.9800,
            'longitude' => 122.5300,
            'status' => 'Aktif',
            'loyalty_points' => 50,
            'tanggal_daftar' => now()->subMonth(),
        ]);

        $this->command->info('   ✓ Created 2 pelanggan');
    }
}
