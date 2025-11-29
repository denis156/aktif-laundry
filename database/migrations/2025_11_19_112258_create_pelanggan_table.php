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
            $table->string('no_hp', 15)->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable()->comment('Password untuk login aplikasi customer (optional)');
            $table->string('device_token')->nullable()->comment('token untuk push notification');

            // Alamat lengkap
            $table->text('alamat')->nullable()->comment('Alamat lengkap (display)');
            $table->text('detail_alamat')->nullable()->comment('Detail alamat atau patokan (jalan, nomor, RT/RW)');
            $table->string('kelurahan', 100)->nullable()->index();
            $table->string('kecamatan', 100)->nullable()->index();
            $table->string('kabupaten_kota', 100)->default('Kota Kendari');
            $table->string('provinsi', 100)->default('Sulawesi Tenggara');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->dateTime('tanggal_daftar');
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');

            // Data Membership & Loyalty
            $table->integer('loyalty_points')->default(0)->comment('Poin loyalty pelanggan');
            $table->string('member_card', 50)->nullable()->comment('Nomor kartu member');
            $table->string('avatar_url', 255)->nullable()->comment('URL avatar pelanggan');

            // Referral (pelanggan yang pakai kode referral orang saat daftar)
            $table->foreignId('direferensikan_oleh')->nullable()->constrained('pelanggan')->onDelete('set null')->comment('ID pelanggan yang nge-refer');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_pelanggan');
            $table->index('no_hp');
            $table->index('email');
            $table->index('status');
            $table->index('loyalty_points');
            $table->index('direferensikan_oleh');
            $table->index(['status', 'tanggal_daftar'], 'idx_pelanggan_status_tanggal');
            $table->index(['latitude', 'longitude'], 'idx_pelanggan_location');
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
