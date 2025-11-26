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

            // Detail fields (moved from metadata)
            $table->integer('min_order')->nullable()->comment('Minimum order (kg atau satuan)');
            $table->integer('max_order')->nullable()->comment('Maximum order (kg atau satuan)');
            $table->boolean('is_popular')->default(false)->comment('Apakah layanan populer');
            $table->string('icon', 100)->nullable()->comment('Icon untuk UI');

            // JSON fields untuk include/exclude list
            $table->json('include')->nullable()->comment('Array of items included in service, e.g., ["Cuci", "Setrika", "Lipat"]');
            $table->json('exclude')->nullable()->comment('Array of items excluded from service, e.g., ["Bed Cover", "Karpet"]');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_layanan');
            $table->index('tipe_layanan');
            $table->index('status');
            $table->index('is_popular', 'idx_layanan_popular');
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
