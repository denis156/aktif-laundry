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
            $table->foreignId('kasir_id')->nullable()->constrained('users')->onDelete('restrict')->comment('Nullable untuk order dari pelanggan langsung');
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('restrict');
            $table->foreignId('referral_id')->nullable()->constrained('referral')->nullOnDelete()->comment('Referral yang dipakai (jika ada)');
            $table->string('nama_pelanggan')->comment('Snapshot nama pelanggan saat transaksi');

            // Summary fields (calculated from transaksi_layanan)
            $table->decimal('total_berat', 8, 2)->default(0)->comment('Total berat dari semua layanan per kg');
            $table->integer('total_item')->default(0)->comment('Total item dari semua layanan per satuan');
            $table->integer('jumlah_layanan')->default(1)->comment('Jumlah layanan dalam transaksi ini');

            // Financial
            $table->integer('subtotal')->comment('Subtotal sebelum diskon (sum dari transaksi_layanan)');
            $table->integer('total')->comment('Total akhir yang harus dibayar');

            // Payment Method
            $table->enum('metode_pembayaran', ['Bayar Saat Jemput', 'Bayar Saat Antar'])->default('Bayar Saat Jemput')->comment('Bayar saat kurir jemput cucian kotor atau saat antar cucian bersih');
            $table->enum('tipe_bayar', ['Tunai', 'Non-Tunai'])->nullable()->comment('Tunai (cash ke kurir) atau Non-Tunai (Transfer/QRIS/E-Wallet)');

            // Payment Status & Details
            $table->enum('status_bayar', ['Belum Bayar', 'Menunggu Verifikasi', 'Sudah Bayar', 'Ditolak'])->default('Belum Bayar')->comment('Status pembayaran');
            $table->dateTime('tanggal_bayar')->nullable()->comment('Tanggal pembayaran berhasil diverifikasi');
            $table->integer('jumlah_bayar')->nullable()->comment('Jumlah yang dibayarkan customer');

            // Kurir foreign keys (moved from metadata)
            $table->foreignId('kurir_jemput_id')->nullable()->constrained('kurir')->onDelete('set null')->comment('Kurir yang jemput cucian');
            $table->foreignId('kurir_antar_id')->nullable()->constrained('kurir')->onDelete('set null')->comment('Kurir yang antar cucian');

            // Status & Timeline
            $table->dateTime('tanggal_selesai')->nullable();
            $table->string('status')->default('Menunggu')->comment('Menunggu, Proses, Selesai, Diambil, Batal');
            $table->text('catatan')->nullable();

            // Snapshot kurir names (saat transaksi dibuat)
            $table->string('kurir_jemput_nama')->nullable()->comment('Snapshot nama kurir jemput saat transaksi');
            $table->string('kurir_antar_nama')->nullable()->comment('Snapshot nama kurir antar saat transaksi');

            // Bukti & Internal notes
            $table->text('catatan_internal')->nullable()->comment('Catatan internal staff');
            $table->json('foto_bukti_timbangan')->nullable()->comment('Array of image URLs for timbangan, e.g., ["url1.jpg", "url2.jpg"]');
            $table->json('foto_bukti_pembayaran')->nullable()->comment('Array foto bukti transfer/QRIS untuk Non-Tunai, e.g., ["url1.jpg", "url2.jpg"]');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_transaksi');
            $table->index('kasir_id');
            $table->index('pelanggan_id');
            $table->index('referral_id');
            $table->index('tanggal_masuk');
            $table->index('tanggal_selesai');
            $table->index('status');
            $table->index('metode_pembayaran');
            $table->index('status_bayar');
            $table->index('tanggal_bayar');
            $table->index('kurir_jemput_id', 'idx_transaksi_kurir_jemput');
            $table->index('kurir_antar_id', 'idx_transaksi_kurir_antar');
            $table->index(['pelanggan_id', 'tanggal_masuk'], 'idx_transaksi_pelanggan_tanggal');
            $table->index(['status', 'tanggal_masuk'], 'idx_transaksi_status_tanggal');
            $table->index(['kasir_id', 'tanggal_masuk'], 'idx_transaksi_kasir_tanggal');
            $table->index(['status_bayar', 'tanggal_bayar'], 'idx_transaksi_bayar');
            $table->index(['metode_pembayaran', 'status_bayar'], 'idx_transaksi_metode_status');
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
