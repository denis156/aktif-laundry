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
        // Add kurir and pelanggan password reset tokens tables
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

        // Add v2 columns to users table
        Schema::table('users', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('users', 'no_hp')) {
                $table->string('no_hp', 15)->unique()->nullable()->after('email');
            }

            // avatar_url and super_admin already exist from v1 migration
            // Skip if they exist

            // Data Kepegawaian
            $table->integer('gaji')->nullable()->comment('Gaji pokok pegawai')->after('super_admin');
            $table->time('jam_masuk')->nullable()->comment('Jam masuk kerja')->after('gaji');
            $table->time('jam_keluar')->nullable()->comment('Jam keluar kerja')->after('jam_masuk');

            // Data Bank (untuk penggajian)
            $table->string('bank_name', 100)->nullable()->comment('Nama bank untuk penggajian')->after('jam_keluar');
            $table->string('bank_account_number', 50)->nullable()->comment('Nomor rekening')->after('bank_name');
            $table->string('bank_account_name', 255)->nullable()->comment('Nama pemilik rekening')->after('bank_account_number');

            // Alamat lengkap
            $table->text('alamat')->nullable()->comment('Alamat lengkap (display)')->after('bank_account_name');
            $table->text('detail_alamat')->nullable()->comment('Detail alamat atau patokan (jalan, nomor, RT/RW)')->after('alamat');
            $table->string('kelurahan', 100)->nullable()->index()->after('detail_alamat');
            $table->string('kecamatan', 100)->nullable()->index()->after('kelurahan');
            $table->string('kabupaten_kota', 100)->default('Kota Kendari')->after('kecamatan');
            $table->string('provinsi', 100)->default('Sulawesi Tenggara')->after('kabupaten_kota');
            $table->decimal('latitude', 10, 8)->nullable()->after('provinsi');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');

            // Composite index untuk location
            $table->index(['latitude', 'longitude'], 'idx_users_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurir_password_reset_tokens');
        Schema::dropIfExists('pelanggan_password_reset_tokens');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_location');

            $table->dropColumn([
                'no_hp',
                'gaji',
                'jam_masuk',
                'jam_keluar',
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'alamat',
                'detail_alamat',
                'kelurahan',
                'kecamatan',
                'kabupaten_kota',
                'provinsi',
                'latitude',
                'longitude',
            ]);
        });
    }
};
