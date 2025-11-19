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
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('kode_referral_dipakai')->nullable()->after('status')
                  ->comment('Kode referral yang dipakai saat daftar');
            $table->foreignId('direferensikan_oleh')->nullable()->after('kode_referral_dipakai')
                  ->constrained('pelanggan')->onDelete('set null')
                  ->comment('ID pelanggan yang nge-refer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropForeign(['direferensikan_oleh']);
            $table->dropColumn(['kode_referral_dipakai', 'direferensikan_oleh']);
        });
    }
};
