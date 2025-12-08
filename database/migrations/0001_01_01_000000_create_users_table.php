<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('no_hp', 15)->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar_url')->nullable();
            $table->boolean('super_admin')->default(false);

            // Data Kepegawaian
            $table->integer('gaji')->nullable()->comment('Gaji pokok pegawai');
            $table->time('jam_masuk')->nullable()->comment('Jam masuk kerja');
            $table->time('jam_keluar')->nullable()->comment('Jam keluar kerja');

            // Data Bank (untuk penggajian)
            $table->string('bank_name', 100)->nullable()->comment('Nama bank untuk penggajian');
            $table->string('bank_account_number', 50)->nullable()->comment('Nomor rekening');
            $table->string('bank_account_name', 255)->nullable()->comment('Nama pemilik rekening');

            // Alamat lengkap
            $table->text('alamat')->nullable()->comment('Alamat lengkap (display)');
            $table->text('detail_alamat')->nullable()->comment('Detail alamat atau patokan (jalan, nomor, RT/RW)');
            $table->string('kelurahan', 100)->nullable()->index();
            $table->string('kecamatan', 100)->nullable()->index();
            $table->string('kabupaten_kota', 100)->default('Kota Kendari');
            $table->string('provinsi', 100)->default('Sulawesi Tenggara');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->rememberToken();
            $table->timestamps();

            // Composite index untuk location
            $table->index(['latitude', 'longitude'], 'idx_users_location');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('kurir_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('pelanggan_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('kurir_password_reset_tokens');
        Schema::dropIfExists('pelanggan_password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
