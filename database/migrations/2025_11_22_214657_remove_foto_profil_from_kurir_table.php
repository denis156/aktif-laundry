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
        Schema::table('kurir', function (Blueprint $table) {
            $table->dropColumn('foto_profil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kurir', function (Blueprint $table) {
            $table->string('foto_profil')->nullable()->after('jenis_kendaraan');
        });
    }
};
