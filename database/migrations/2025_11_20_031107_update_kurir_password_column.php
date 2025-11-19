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
     * Update kolom password di tabel kurir menjadi required (not nullable)
     * Sebelumnya: nullable
     * Sesudahnya: required
     */
    public function up(): void
    {
        Schema::table('kurir', function (Blueprint $table) {
            // Update password column dari nullable menjadi required
            $table->string('password')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Kembalikan password ke nullable jika rollback
     */
    public function down(): void
    {
        Schema::table('kurir', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }
};
