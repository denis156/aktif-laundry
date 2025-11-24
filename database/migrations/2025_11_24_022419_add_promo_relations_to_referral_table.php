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
        Schema::table('referral', function (Blueprint $table) {
            // Promo untuk referrer (yang mengajak)
            $table->foreignId('promo_referrer_id')
                ->nullable()
                ->after('pelanggan_id')
                ->constrained('promo')
                ->nullOnDelete();

            // Promo untuk referee (yang diajak)
            $table->foreignId('promo_referee_id')
                ->nullable()
                ->after('promo_referrer_id')
                ->constrained('promo')
                ->nullOnDelete();

            // Index untuk optimasi query
            $table->index('promo_referrer_id');
            $table->index('promo_referee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral', function (Blueprint $table) {
            $table->dropForeign(['promo_referrer_id']);
            $table->dropForeign(['promo_referee_id']);
            $table->dropIndex(['promo_referrer_id']);
            $table->dropIndex(['promo_referee_id']);
            $table->dropColumn(['promo_referrer_id', 'promo_referee_id']);
        });
    }
};
