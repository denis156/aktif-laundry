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
        Schema::create('kurir', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kurir')->unique()->comment('KUR001, KUR002, etc');
            $table->string('nama');
            $table->string('no_hp', 15)->unique();
            $table->string('email')->nullable()->unique();
            $table->text('alamat')->nullable();

            // Vehicle info
            $table->string('no_kendaraan')->nullable()->comment('Plat nomor');
            $table->enum('jenis_kendaraan', ['Motor', 'Mobil', 'Sepeda'])->nullable();

            // Profile
            $table->string('foto_profil')->nullable();
            $table->date('tanggal_bergabung');
            $table->enum('status', ['Aktif', 'Tidak Aktif', 'Cuti'])->default('Aktif');

            // Statistics
            $table->integer('total_antar')->default(0);
            $table->integer('total_jemput')->default(0);

            // Auth untuk aplikasi courier
            $table->string('password')->nullable()->comment('Untuk login di app courier');
            $table->string('device_token')->nullable()->comment('FCM token untuk push notification');

            // Flexible data storage
            $table->jsonb('metadata')->nullable()->comment('Flexible data: rating, area_coverage, bank_info, emergency_contact, dll');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_kurir');
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
        Schema::dropIfExists('kurir');
    }
};
