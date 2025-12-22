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
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->renameColumn('device_token', 'fcm_token');
        });

        Schema::table('kurir', function (Blueprint $table) {
            $table->renameColumn('device_token', 'fcm_token');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->renameColumn('fcm_token', 'device_token');
        });

        Schema::table('kurir', function (Blueprint $table) {
            $table->renameColumn('fcm_token', 'device_token');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
    }
};
