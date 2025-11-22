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
            $table->enum('tipe_diskon', ['persen', 'nominal'])->comment('Persen atau Rupiah');
            $table->integer('nilai_diskon')->comment('20 untuk 20% atau 50000 untuk Rp 50.000');
            $table->integer('diskon_maksimal')->nullable()->comment('Max diskon jika pakai persen');

            // Minimum transaksi
            $table->integer('min_transaksi')->default(0)->comment('Min belanja Rp');

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

            // Flexible data storage
            $table->jsonb('metadata')->nullable()->comment('Flexible data: layanan_id, exclude_pelanggan_id, banner_image, terms, dll');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_promo');
            $table->index('status');
            $table->index('tanggal_mulai');
            $table->index('tanggal_berakhir');
            $table->index('berlaku_untuk');
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
