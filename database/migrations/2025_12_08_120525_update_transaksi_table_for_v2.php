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
        Schema::table('transaksi', function (Blueprint $table) {
            // Modify kasir_id to be nullable
            $table->foreignId('kasir_id')->nullable()->change();

            // Add new v2 foreign keys and fields
            $table->foreignId('referral_id')->nullable()->after('pelanggan_id')->constrained('referral')->nullOnDelete()->comment('Referral yang dipakai (jika ada)');

            // total_berat, total_item, jumlah_layanan already added from v1 migration update_transaksi_table_for_multi_layanan

            // Modify metode_pembayaran enum
            $table->dropColumn('metode_pembayaran');
            $table->enum('metode_pembayaran', ['Bayar Saat Jemput', 'Bayar Saat Antar'])->default('Bayar Saat Jemput')->comment('Bayar saat kurir jemput cucian kotor atau saat antar cucian bersih')->after('total');

            // Add new payment fields
            $table->enum('tipe_bayar', ['Tunai', 'Non-Tunai'])->nullable()->comment('Tunai (cash ke kurir) atau Non-Tunai (Transfer/QRIS/E-Wallet)')->after('metode_pembayaran');
            $table->enum('status_bayar', ['Belum Bayar', 'Menunggu Verifikasi', 'Sudah Bayar', 'Ditolak'])->default('Belum Bayar')->comment('Status pembayaran')->after('tipe_bayar');
            $table->dateTime('tanggal_bayar')->nullable()->comment('Tanggal pembayaran berhasil diverifikasi')->after('status_bayar');
            $table->integer('jumlah_bayar')->nullable()->comment('Jumlah yang dibayarkan customer')->after('tanggal_bayar');

            // Add kurir foreign keys
            $table->foreignId('kurir_jemput_id')->nullable()->after('jumlah_bayar')->constrained('kurir')->onDelete('set null')->comment('Kurir yang jemput cucian');
            $table->foreignId('kurir_antar_id')->nullable()->after('kurir_jemput_id')->constrained('kurir')->onDelete('set null')->comment('Kurir yang antar cucian');

            // Modify status column to string type
            $table->string('status')->default('Menunggu')->change();

            // Add snapshot kurir names
            $table->string('kurir_jemput_nama')->nullable()->after('catatan')->comment('Snapshot nama kurir jemput saat transaksi');
            $table->string('kurir_antar_nama')->nullable()->after('kurir_jemput_nama')->comment('Snapshot nama kurir antar saat transaksi');

            // Add bukti & internal notes
            $table->text('catatan_internal')->nullable()->after('kurir_antar_nama')->comment('Catatan internal staff');
            $table->json('foto_bukti_timbangan')->nullable()->after('catatan_internal')->comment('Array of image URLs for timbangan, e.g., ["url1.jpg", "url2.jpg"]');
            $table->json('foto_bukti_pembayaran')->nullable()->after('foto_bukti_timbangan')->comment('Array foto bukti transfer/QRIS untuk Non-Tunai, e.g., ["url1.jpg", "url2.jpg"]');

            // Add new indexes
            $table->index('referral_id');
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
        Schema::table('transaksi', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('idx_transaksi_metode_status');
            $table->dropIndex('idx_transaksi_bayar');
            $table->dropIndex('idx_transaksi_kasir_tanggal');
            $table->dropIndex('idx_transaksi_status_tanggal');
            $table->dropIndex('idx_transaksi_pelanggan_tanggal');
            $table->dropIndex('idx_transaksi_kurir_antar');
            $table->dropIndex('idx_transaksi_kurir_jemput');
            $table->dropIndex(['tanggal_bayar']);
            $table->dropIndex(['status_bayar']);
            $table->dropIndex(['referral_id']);

            // Drop foreign keys
            $table->dropForeign(['referral_id']);
            $table->dropForeign(['kurir_jemput_id']);
            $table->dropForeign(['kurir_antar_id']);

            // Drop columns
            $table->dropColumn([
                'referral_id',
                'tipe_bayar',
                'status_bayar',
                'tanggal_bayar',
                'jumlah_bayar',
                'kurir_jemput_id',
                'kurir_antar_id',
                'kurir_jemput_nama',
                'kurir_antar_nama',
                'catatan_internal',
                'foto_bukti_timbangan',
                'foto_bukti_pembayaran',
            ]);

            // Restore old metode_pembayaran
            $table->dropColumn('metode_pembayaran');
            $table->enum('metode_pembayaran', ['Tunai', 'Transfer', 'QRIS', 'Debit']);
        });
    }
};
