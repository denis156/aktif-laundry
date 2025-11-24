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
        Schema::table('transaksi', function (Blueprint $table) {
            // Promo yang digunakan dalam transaksi
            $table->foreignId('promo_id')
                ->nullable()
                ->after('pelanggan_id')
                ->constrained('promo')
                ->nullOnDelete();

            // Kode referral yang digunakan (opsional)
            $table->foreignId('referral_id')
                ->nullable()
                ->after('promo_id')
                ->constrained('referral')
                ->nullOnDelete();

            // Simpan kode promo snapshot (untuk history)
            $table->string('kode_promo')->nullable()->after('referral_id');

            // Index untuk optimasi query
            $table->index('promo_id');
            $table->index('referral_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['promo_id']);
            $table->dropForeign(['referral_id']);
            $table->dropIndex(['promo_id']);
            $table->dropIndex(['referral_id']);
            $table->dropColumn(['promo_id', 'referral_id', 'kode_promo']);
        });
    }
};
