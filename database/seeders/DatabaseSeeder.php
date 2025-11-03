<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pelanggan;
use App\Models\Layanan;
use App\Models\JenisPakaian;
use App\Models\Transaksi;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Users (Kasir)
        $this->command->info('Seeding Users...');
        User::factory()->create([
            'name' => 'Admin',
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

        // Seed Layanan (8 layanan sesuai dummy data)
        $this->command->info('Seeding Layanan...');
        Layanan::factory()->count(8)->create();

        // Seed Jenis Pakaian (15 jenis sesuai dummy data)
        $this->command->info('Seeding Jenis Pakaian...');
        JenisPakaian::factory()->count(15)->create();

        // Seed Pelanggan (20 pelanggan)
        $this->command->info('Seeding Pelanggan...');
        Pelanggan::factory()->count(20)->create();

        // Seed Transaksi (50 transaksi)
        $this->command->info('Seeding Transaksi...');
        Transaksi::factory()->count(50)->create();

        // Seed Settings
        $this->command->info('Seeding Settings...');
        Setting::create([
            'key' => 'nama_toko',
            'value' => 'Aktif Laundry',
            'deskripsi' => 'Nama toko laundry',
        ]);

        Setting::create([
            'key' => 'whatsapp',
            'value' => '81234567890',
            'deskripsi' => 'Nomor WhatsApp (format: 8xxx tanpa 0)',
        ]);

        Setting::create([
            'key' => 'email',
            'value' => 'info@aktiflaundry.com',
            'deskripsi' => 'Email toko',
        ]);

        Setting::create([
            'key' => 'jam_buka',
            'value' => '08:00',
            'deskripsi' => 'Jam buka toko',
        ]);

        Setting::create([
            'key' => 'jam_tutup',
            'value' => '21:00',
            'deskripsi' => 'Jam tutup toko',
        ]);

        $this->command->info('Database seeded successfully!');
    }
}
