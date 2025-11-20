<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tambahkan kolom password ke tabel pelanggan (nullable)
     * Untuk mendukung login pelanggan di aplikasi mobile/web customer
     */
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            // Tambahkan password column setelah email (nullable untuk backward compatibility)
            $table->string('password')
                ->nullable()
                ->after('email')
                ->comment('Password untuk login aplikasi customer (optional)');

            // Tambahkan device_token untuk push notification (optional)
            $table->string('device_token')
                ->nullable()
                ->after('password')
                ->comment('FCM token untuk push notification');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Hapus kolom password dan device_token jika rollback
     */
    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn(['password', 'device_token']);
        });
    }
};
