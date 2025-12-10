<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique()->comment('TRX001, TRX002, etc');
            $table->dateTime('tanggal_masuk');
            $table->foreignId('kasir_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('restrict');
            $table->string('nama_pelanggan');
            $table->foreignId('layanan_id')->constrained('layanan')->onDelete('restrict');
            $table->string('nama_layanan');
            $table->json('jenis_pakaian')->comment('Format: [{"nama": "Kemeja", "jumlah": 3}, {"nama": "Celana", "jumlah": 2}]');
            $table->decimal('berat_kg', 8, 2)->comment('Weight in kg with 2 decimals');
            $table->integer('harga_per_kg')->comment('Price per kg in Rupiah');
            $table->integer('subtotal')->comment('Subtotal in Rupiah');
            $table->integer('diskon')->default(0)->comment('Discount in Rupiah');
            $table->integer('total')->comment('Total in Rupiah');
            $table->enum('metode_pembayaran', ['Tunai', 'Transfer', 'QRIS', 'Debit']);
            $table->dateTime('tanggal_selesai')->nullable();
            $table->enum('status', ['Menunggu', 'Proses', 'Selesai', 'Diambil', 'Batal'])->default('Menunggu');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kasir_id');
            $table->index('pelanggan_id');
            $table->index('layanan_id');
            $table->index('tanggal_masuk');
            $table->index('tanggal_selesai');
            $table->index('status');
            $table->index('metode_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
