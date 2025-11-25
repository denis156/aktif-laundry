<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pelanggan')->unique()->comment('PLG001, PLG002, etc');
            $table->string('nama');
            $table->string('no_hp', 15);
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable()->comment('Password untuk login aplikasi customer (optional)');
            $table->string('device_token')->nullable()->comment('token untuk push notification');
            $table->text('alamat')->comment('Alamat lengkap (auto-generated dari metadata)');

            $table->dateTime('tanggal_daftar');
            $table->integer('total_transaksi')->default(0);
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');

            // Referral
            $table->string('kode_referral_dipakai')->nullable()->comment('Kode referral yang dipakai saat daftar');
            $table->foreignId('direferensikan_oleh')->nullable()->constrained('pelanggan')->onDelete('set null')->comment('ID pelanggan yang nge-refer');

            // Flexible data storage
            $table->jsonb('metadata')->nullable()->comment('Flexible data: detail_alamat, kelurahan, kecamatan, kabupaten_kota, provinsi, latitude, longitude, instagram, preferensi, dll');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_pelanggan');
            $table->index('no_hp');
            $table->index('email');
            $table->index('status');
            $table->index(['status', 'tanggal_daftar'], 'idx_pelanggan_status_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
