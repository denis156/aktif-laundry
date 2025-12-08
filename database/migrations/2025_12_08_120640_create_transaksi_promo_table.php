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
        Schema::create('transaksi_promo', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('transaksi_id')->constrained('transaksi')->onDelete('cascade');
            $table->foreignId('promo_id')->constrained('promo')->onDelete('restrict');

            // Snapshot data promo (saat transaksi dibuat - untuk audit trail)
            $table->string('kode_promo')->comment('Snapshot kode promo');
            $table->string('nama_promo')->comment('Snapshot nama promo');
            $table->string('tipe_diskon', 50)->comment('Snapshot: persen, nominal, gratis_kg, gratis_hari, cashback, gratis_ongkir');
            $table->integer('nilai_diskon_persen')->nullable()->comment('Nilai persen jika tipe diskon persen, e.g., 20 untuk 20%');
            $table->integer('nilai_diskon_nominal')->default(0)->comment('Nilai diskon dalam Rupiah yang didapat dari promo ini (0 jika gratis_ongkir/gratis_kg/gratis_hari)');
            $table->integer('diskon_maksimal')->nullable()->comment('Snapshot max diskon jika pakai persen');
            $table->integer('gratis_kg')->nullable()->comment('Jumlah kg gratis jika tipe diskon gratis_kg');
            $table->integer('gratis_hari')->nullable()->comment('Jumlah hari express gratis jika tipe diskon gratis_hari');

            // Applied to (promo diterapkan kemana)
            $table->enum('diterapkan_ke', ['subtotal', 'layanan_tertentu'])->default('subtotal')->comment('Promo diterapkan ke mana');
            $table->foreignId('layanan_id')->nullable()->constrained('layanan')->nullOnDelete()->comment('Jika promo khusus untuk 1 layanan tertentu');

            // Priority untuk multiple promo (yang lebih kecil diapply duluan)
            $table->tinyInteger('urutan_apply')->default(1)->comment('Urutan apply promo jika ada multiple promo (1=first)');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('transaksi_id');
            $table->index('promo_id');
            $table->index('layanan_id');
            $table->index(['transaksi_id', 'promo_id'], 'idx_transaksi_promo_detail');
            $table->index(['transaksi_id', 'urutan_apply'], 'idx_transaksi_promo_urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_promo');
    }
};
