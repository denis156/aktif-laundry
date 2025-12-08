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
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pengiriman')->unique()->comment('PNG001, PNG002, etc');

            // Relations
            $table->foreignId('transaksi_id')->constrained('transaksi')->onDelete('cascade');
            $table->foreignId('kurir_id')->nullable()->constrained('kurir')->onDelete('set null');

            // Delivery type
            $table->enum('tipe', ['Jemput', 'Antar'])->comment('Jemput cucian atau Antar cucian');

            // Destination info
            $table->text('alamat_tujuan');
            $table->string('nama_penerima');
            $table->string('no_hp_penerima', 15);

            // Koordinat GPS Tujuan
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Timeline
            $table->dateTime('jadwal_waktu')->comment('Waktu dijadwalkan');
            $table->dateTime('waktu_mulai')->nullable()->comment('Waktu courier mulai berangkat');
            $table->dateTime('waktu_selesai')->nullable()->comment('Waktu selesai delivery');

            // Status
            $table->enum('status', ['Menunggu', 'Dijadwalkan', 'Dalam Perjalanan', 'Selesai', 'Batal'])->default('Menunggu');

            // Notes & Evidence
            $table->text('catatan')->nullable();
            $table->string('foto_bukti')->nullable()->comment('Foto bukti delivery');

            // Lokasi pickup
            $table->decimal('lokasi_pickup_latitude', 10, 8)->nullable()->comment('Latitude lokasi pickup');
            $table->decimal('lokasi_pickup_longitude', 11, 8)->nullable()->comment('Longitude lokasi pickup');
            $table->text('lokasi_pickup_address')->nullable()->comment('Alamat pickup lengkap');

            // Review customer
            $table->tinyInteger('review_rating')->nullable()->comment('Rating 1-5 dari customer');
            $table->text('review_text')->nullable()->comment('Komentar review dari customer');

            // Tracking
            $table->json('tracking')->nullable()->comment('Array of tracking points, e.g., [{"time":"2024-01-01 10:00","lat":-3.9,"lng":122.5,"status":"On the way"}]');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_pengiriman');
            $table->index('transaksi_id');
            $table->index('kurir_id');
            $table->index('status');
            $table->index('tipe');
            $table->index('jadwal_waktu');
            $table->index(['transaksi_id', 'tipe'], 'idx_pengiriman_transaksi_tipe');
            $table->index(['kurir_id', 'status'], 'idx_pengiriman_kurir_status');
            $table->index(['tipe', 'jadwal_waktu'], 'idx_pengiriman_tipe_tanggal');
            $table->index(['lokasi_pickup_latitude', 'lokasi_pickup_longitude'], 'idx_pengiriman_pickup_location');
            $table->index('review_rating', 'idx_pengiriman_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};
