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

            // Cost & Distance
            $table->integer('biaya_antar')->default(0);
            $table->decimal('jarak_km', 8, 2)->nullable();

            // Status
            $table->enum('status', ['Menunggu', 'Dijadwalkan', 'Dalam Perjalanan', 'Selesai', 'Batal'])->default('Menunggu');

            // Notes & Evidence
            $table->text('catatan')->nullable();
            $table->string('foto_bukti')->nullable()->comment('Foto bukti delivery');

            // Flexible data storage
            $table->jsonb('metadata')->nullable()->comment('Flexible data: lokasi GPS, tracking history, rating, signature, dll');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_pengiriman');
            $table->index('transaksi_id');
            $table->index('kurir_id');
            $table->index('status');
            $table->index('tipe');
            $table->index('jadwal_waktu');
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
