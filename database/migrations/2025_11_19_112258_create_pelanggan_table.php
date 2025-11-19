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
            $table->text('alamat');

            // Wilayah
            $table->string('kelurahan')->nullable()->comment('Kelurahan/Desa');
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten_kota')->nullable()->comment('Kabupaten/Kota');
            $table->string('provinsi')->nullable();

            // Koordinat GPS
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->dateTime('tanggal_daftar');
            $table->integer('total_transaksi')->default(0);
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');

            // Referral
            $table->string('kode_referral_dipakai')->nullable()->comment('Kode referral yang dipakai saat daftar');
            $table->foreignId('direferensikan_oleh')->nullable()->constrained('pelanggan')->onDelete('set null')->comment('ID pelanggan yang nge-refer');

            // Flexible data storage
            $table->jsonb('metadata')->nullable()->comment('Flexible data: instagram, preferensi, catatan khusus, dll');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_pelanggan');
            $table->index('no_hp');
            $table->index('email');
            $table->index('status');
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
