<?php

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
            $table->foreignId('transaksi_id')->constrained('transaksi')->onDelete('cascade');
            $table->foreignId('layanan_id')->constrained('layanan')->onDelete('restrict');
            $table->string('nama_layanan');

            // Untuk layanan per_kg
            $table->json('jenis_pakaian')->nullable()->comment('[{"nama":"Baju","jumlah":5},{"nama":"Celana","jumlah":7}]');
            $table->decimal('berat_kg', 8, 2)->nullable();
            $table->integer('harga_per_kg')->nullable();

            // Untuk layanan per_satuan
            $table->integer('jumlah_satuan')->nullable();
            $table->integer('harga_per_satuan')->nullable();

            // Common fields
            $table->integer('subtotal')->comment('Subtotal untuk layanan ini');
            $table->timestamps();

            // Indexes
            $table->index('transaksi_id');
            $table->index('layanan_id');
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
