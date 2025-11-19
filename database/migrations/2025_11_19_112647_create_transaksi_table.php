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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique()->comment('TRX001, TRX002, etc');
            $table->dateTime('tanggal_masuk');

            // Relations
            $table->foreignId('kasir_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('restrict');
            $table->string('nama_pelanggan')->comment('Snapshot nama pelanggan saat transaksi');

            // Summary fields (calculated from transaksi_layanan)
            $table->decimal('total_berat', 8, 2)->default(0)->comment('Total berat dari semua layanan per kg');
            $table->integer('total_item')->default(0)->comment('Total item dari semua layanan per satuan');
            $table->integer('jumlah_layanan')->default(1)->comment('Jumlah layanan dalam transaksi ini');

            // Financial
            $table->integer('subtotal')->comment('Subtotal sebelum diskon');
            $table->integer('diskon')->default(0)->comment('Discount in Rupiah');
            $table->integer('total')->comment('Total after discount');
            $table->enum('metode_pembayaran', ['Tunai', 'Non-Tunai']);

            // Status & Timeline
            $table->dateTime('tanggal_selesai')->nullable();
            $table->enum('status', ['Menunggu', 'Proses', 'Selesai', 'Diambil', 'Batal'])->default('Menunggu');
            $table->text('catatan')->nullable();

            // Flexible data storage
            $table->jsonb('metadata')->nullable()->comment('Flexible data: catatan_internal, antar_jemput, pembayaran, promo, petugas, foto_bukti, dll');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_transaksi');
            $table->index('kasir_id');
            $table->index('pelanggan_id');
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
