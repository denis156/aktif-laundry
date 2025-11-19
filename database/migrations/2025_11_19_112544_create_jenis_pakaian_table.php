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
        Schema::create('jenis_pakaian', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jenis')->unique()->comment('JNS001, JNS002, etc');
            $table->string('nama_jenis');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');

            // Flexible data storage
            $table->jsonb('metadata')->nullable()->comment('Flexible data: estimasi_berat, kategori, tips_perawatan, icon, dll');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_jenis');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_pakaian');
    }
};
