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
        Schema::create('layanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_layanan')->unique()->comment('LYN001, LYN002, etc');
            $table->string('nama_layanan');
            $table->enum('tipe_layanan', ['per_kg', 'per_satuan'])->default('per_kg');
            $table->string('satuan')->default('kg')->comment('kg, pcs, lembar, dll');

            // Pricing
            $table->integer('harga_per_kg')->default(0)->comment('Price in Rupiah');
            $table->integer('harga_per_satuan')->nullable()->comment('Price per unit in Rupiah');

            $table->integer('durasi_jam')->comment('Duration in hours');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');

            // Flexible data storage
            $table->jsonb('metadata')->nullable()->comment('Flexible data: include, exclude, min_order, max_order, icon, dll');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_layanan');
            $table->index('tipe_layanan');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};
