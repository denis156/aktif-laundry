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
        Schema::create('transaksi_layanan', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('transaksi_id')->constrained('transaksi')->onDelete('cascade');
            $table->foreignId('layanan_id')->constrained('layanan')->onDelete('restrict');
            $table->string('nama_layanan')->comment('Snapshot nama layanan saat transaksi');

            // Untuk layanan per_kg
            $table->jsonb('jenis_pakaian')->nullable()->comment('[{"jenis_id":"1","nama":"Baju","jumlah":5},{"jenis_id":"2","nama":"Celana","jumlah":7}]');
            $table->decimal('berat_kg', 8, 2)->nullable();
            $table->integer('harga_per_kg')->nullable();

            // Untuk layanan per_satuan
            $table->integer('jumlah_satuan')->nullable();
            $table->integer('harga_per_satuan')->nullable();

            // Common fields
            $table->integer('subtotal')->comment('Subtotal untuk layanan ini');

            // Flexible data storage
            $table->jsonb('metadata')->nullable()->comment('Flexible data: catatan khusus per layanan, dll');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('transaksi_id');
            $table->index('layanan_id');
            $table->index(['transaksi_id', 'layanan_id'], 'idx_transaksi_layanan_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_layanan');
    }
};
