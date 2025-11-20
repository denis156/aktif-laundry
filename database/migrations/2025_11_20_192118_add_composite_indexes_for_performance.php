<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add composite indexes to improve query performance for common query patterns
     */
    public function up(): void
    {
        // Transaksi table composite indexes
        Schema::table('transaksi', function (Blueprint $table) {
            // For customer transaction history queries (WHERE pelanggan_id = ? ORDER BY tanggal_masuk)
            $table->index(['pelanggan_id', 'tanggal_masuk'], 'idx_transaksi_pelanggan_tanggal');

            // For filtering transactions by status and date (WHERE status = ? ORDER BY tanggal_masuk)
            $table->index(['status', 'tanggal_masuk'], 'idx_transaksi_status_tanggal');

            // For kasir performance queries (WHERE kasir_id = ? ORDER BY tanggal_masuk)
            $table->index(['kasir_id', 'tanggal_masuk'], 'idx_transaksi_kasir_tanggal');
        });

        // Transaksi Layanan table composite indexes
        Schema::table('transaksi_layanan', function (Blueprint $table) {
            // For transaction detail queries (WHERE transaksi_id = ? AND layanan_id = ?)
            $table->index(['transaksi_id', 'layanan_id'], 'idx_transaksi_layanan_detail');
        });

        // Pengiriman table composite indexes
        Schema::table('pengiriman', function (Blueprint $table) {
            // For delivery queries by transaction and type (WHERE transaksi_id = ? AND tipe = ?)
            $table->index(['transaksi_id', 'tipe'], 'idx_pengiriman_transaksi_tipe');

            // For courier workload queries (WHERE kurir_id = ? AND status = ?)
            $table->index(['kurir_id', 'status'], 'idx_pengiriman_kurir_status');

            // For date-based delivery queries (WHERE tipe = ? ORDER BY jadwal_waktu)
            $table->index(['tipe', 'jadwal_waktu'], 'idx_pengiriman_tipe_tanggal');
        });

        // Pembayaran table composite indexes
        Schema::table('pembayaran', function (Blueprint $table) {
            // For payment status queries (WHERE transaksi_id = ? AND status = ?)
            $table->index(['transaksi_id', 'status'], 'idx_pembayaran_transaksi_status');

            // For payment verification queries (WHERE status = ? ORDER BY tanggal_bayar)
            $table->index(['status', 'tanggal_bayar'], 'idx_pembayaran_status_tanggal');
        });

        // Pelanggan table composite indexes
        Schema::table('pelanggan', function (Blueprint $table) {
            // For active customer queries (WHERE status = ? ORDER BY tanggal_daftar)
            $table->index(['status', 'tanggal_daftar'], 'idx_pelanggan_status_tanggal');
        });

        // Kurir table composite indexes
        Schema::table('kurir', function (Blueprint $table) {
            // For active courier queries (WHERE status = ? ORDER BY tanggal_bergabung)
            $table->index(['status', 'tanggal_bergabung'], 'idx_kurir_status_tanggal');
        });

        // Promo table composite indexes
        Schema::table('promo', function (Blueprint $table) {
            // For active promo queries (WHERE tanggal_mulai <= ? AND tanggal_berakhir >= ?)
            $table->index(['tanggal_mulai', 'tanggal_berakhir'], 'idx_promo_periode');
        });

        // Referral table composite indexes
        Schema::table('referral', function (Blueprint $table) {
            // For referrer queries (WHERE pelanggan_id = ? ORDER BY created_at)
            $table->index(['pelanggan_id', 'created_at'], 'idx_referral_pelanggan_created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Remove all composite indexes added in up()
     */
    public function down(): void
    {
        // Drop transaksi indexes
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropIndex('idx_transaksi_pelanggan_tanggal');
            $table->dropIndex('idx_transaksi_status_tanggal');
            $table->dropIndex('idx_transaksi_kasir_tanggal');
        });

        // Drop transaksi_layanan indexes
        Schema::table('transaksi_layanan', function (Blueprint $table) {
            $table->dropIndex('idx_transaksi_layanan_detail');
        });

        // Drop pengiriman indexes
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropIndex('idx_pengiriman_transaksi_tipe');
            $table->dropIndex('idx_pengiriman_kurir_status');
            $table->dropIndex('idx_pengiriman_tipe_tanggal');
        });

        // Drop pembayaran indexes
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropIndex('idx_pembayaran_transaksi_status');
            $table->dropIndex('idx_pembayaran_status_tanggal');
        });

        // Drop pelanggan indexes
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropIndex('idx_pelanggan_status_tanggal');
        });

        // Drop kurir indexes
        Schema::table('kurir', function (Blueprint $table) {
            $table->dropIndex('idx_kurir_status_tanggal');
        });

        // Drop promo indexes
        Schema::table('promo', function (Blueprint $table) {
            $table->dropIndex('idx_promo_periode');
        });

        // Drop referral indexes
        Schema::table('referral', function (Blueprint $table) {
            $table->dropIndex('idx_referral_pelanggan_created');
        });
    }
};
