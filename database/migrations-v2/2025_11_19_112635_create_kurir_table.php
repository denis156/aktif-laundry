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
        Schema::create('kurir', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kurir')->unique()->comment('KUR001, KUR002, etc');
            $table->string('nama');
            $table->string('no_hp', 15)->unique();
            $table->string('email')->nullable()->unique();

            // Alamat lengkap
            $table->text('alamat')->nullable()->comment('Alamat lengkap (display)');
            $table->text('detail_alamat')->nullable()->comment('Detail alamat atau patokan (jalan, nomor, RT/RW)');
            $table->string('kelurahan', 100)->nullable()->index();
            $table->string('kecamatan', 100)->nullable()->index();
            $table->string('kabupaten_kota', 100)->default('Kota Kendari');
            $table->string('provinsi', 100)->default('Sulawesi Tenggara');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Vehicle info
            $table->string('no_kendaraan')->nullable()->comment('Plat nomor');
            $table->enum('jenis_kendaraan', ['Motor', 'Mobil'])->nullable();

            // Profile
            $table->dateTime('tanggal_bergabung');
            $table->enum('status', ['Aktif', 'Tidak Aktif', 'Cuti'])->default('Aktif');

            // Data Bank (untuk penggajian)
            $table->string('bank_name', 100)->nullable()->comment('Nama bank untuk penggajian');
            $table->string('bank_account_number', 50)->nullable()->comment('Nomor rekening');
            $table->string('bank_account_name', 255)->nullable()->comment('Nama pemilik rekening');

            // Emergency Contact (untuk safety)
            $table->string('emergency_contact_name', 255)->nullable()->comment('Nama kontak darurat');
            $table->string('emergency_contact_phone', 15)->nullable()->comment('Nomor kontak darurat');
            $table->string('emergency_contact_relation', 50)->nullable()->comment('Hubungan dengan kontak darurat');

            // Avatar
            $table->string('avatar_url', 255)->nullable()->comment('URL avatar kurir');

            // Auth untuk aplikasi courier
            $table->string('password')->comment('Untuk login di app courier');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('device_token')->nullable()->comment('token untuk push notification');

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_kurir');
            $table->index('no_hp');
            $table->index('email');
            $table->index('status');
            $table->index(['status', 'tanggal_bergabung'], 'idx_kurir_status_tanggal');
            $table->index(['latitude', 'longitude'], 'idx_kurir_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurir');
    }
};
