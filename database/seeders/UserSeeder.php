<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin User
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@aktiflaundry.com',
            'password' => Hash::make('password'),
            'super_admin' => true,
            'no_hp' => '81234567890',
            'alamat' => 'Jl. Admin No. 1',
        ]);
    }
}
