<?php

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
        Schema::table('layanan', function (Blueprint $table) {
            $table->enum('tipe_layanan', ['per_kg', 'per_satuan'])->default('per_kg')->after('nama_layanan');
            $table->integer('harga_per_satuan')->nullable()->after('harga_per_kg');
            $table->string('satuan')->default('kg')->after('harga_per_satuan')->comment('kg atau pcs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layanan', function (Blueprint $table) {
            $table->dropColumn(['tipe_layanan', 'harga_per_satuan', 'satuan']);
        });
    }
};
