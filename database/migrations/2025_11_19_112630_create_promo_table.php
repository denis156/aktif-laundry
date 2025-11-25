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
        Schema::create('promo', function (Blueprint $table) {
            $table->id();
            $table->string('kode_promo')->unique()->comment('DISC20, NEWYEAR25, etc');
            $table->string('nama_promo');
            $table->text('deskripsi')->nullable();

            // Tipe diskon
            $table->string('tipe_diskon', 50)->comment('persen, nominal, gratis_kg, gratis_hari, cashback, gratis_ongkir');
            $table->integer('nilai_diskon')->nullable()->comment('20 untuk 20% atau 50000 untuk Rp 50.000 (null jika gratis_ongkir)');
            $table->integer('diskon_maksimal')->nullable()->comment('Max diskon jika pakai persen');

            // Minimum transaksi
            $table->integer('min_transaksi')->nullable()->comment('Min belanja Rp');

            // Periode promo
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_berakhir');

            // Limit penggunaan
            $table->integer('kuota_total')->nullable()->comment('Total kuota penggunaan');
            $table->integer('kuota_terpakai')->default(0)->comment('Sudah dipakai berapa kali');
            $table->integer('max_per_user')->default(1)->comment('Max penggunaan per user');

            // Target
            $table->enum('berlaku_untuk', ['semua', 'pelanggan_baru', 'pelanggan_lama'])->default('semua');

            $table->enum('status', ['Aktif', 'Tidak Aktif', 'Habis'])->default('Aktif');

            // Detail fields (moved from metadata)
            $table->string('banner_image', 255)->nullable()->comment('Path gambar banner promo');
            $table->text('terms_conditions')->nullable()->comment('Syarat dan ketentuan promo');
            $table->boolean('auto_apply')->default(false)->comment('Apakah promo otomatis terapply');
            $table->decimal('min_berat', 8, 2)->nullable()->comment('Minimum berat untuk promo (kg)');
            $table->decimal('max_berat', 8, 2)->nullable()->comment('Maximum berat untuk promo (kg)');

            // JSON fields untuk layanan & pelanggan filter
            $table->jsonb('layanan_ids')->nullable()->comment('Array of layanan IDs that eligible for this promo, e.g., [1, 3, 5]');
            $table->jsonb('exclude_pelanggan_ids')->nullable()->comment('Array of pelanggan IDs to exclude from this promo, e.g., [10, 25]');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_promo');
            $table->index('status');
            $table->index('tanggal_mulai');
            $table->index('tanggal_berakhir');
            $table->index('berlaku_untuk');
            $table->index(['tanggal_mulai', 'tanggal_berakhir'], 'idx_promo_periode');
            $table->index('auto_apply', 'idx_promo_auto_apply');
            $table->index(['min_berat', 'max_berat'], 'idx_promo_berat_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo');
    }
};
