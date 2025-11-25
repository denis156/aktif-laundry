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
            // Change tanggal_bergabung from date to datetime
            $table->dateTime('tanggal_bergabung')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kurir', function (Blueprint $table) {
            // Revert back to date
            $table->date('tanggal_bergabung')->change();
        });
    }
};
