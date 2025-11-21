<?php

declare(strict_types=1);

namespace Database\Seeders;

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

        // Call UserSeeder
        $this->call([
            UserSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeded successfully!');
        $this->command->newLine();

        $this->command->info('🔑 Login Credentials:');
        $this->command->line('   Email: admin@aktiflaundry.com');
        $this->command->line('   Password: password');
        $this->command->newLine();
    }
}
