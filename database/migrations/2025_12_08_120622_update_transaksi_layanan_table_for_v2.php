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
        Schema::table('transaksi_layanan', function (Blueprint $table) {
            // Add new v2 field
            $table->text('catatan_khusus')->nullable()->after('subtotal')->comment('Catatan khusus untuk layanan ini');

            // Add composite index
            $table->index(['transaksi_id', 'layanan_id'], 'idx_transaksi_layanan_detail');

            // deleted_at already added from v1 migration add_deleted_at_to_transaksi_layanan_table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_layanan', function (Blueprint $table) {
            $table->dropIndex('idx_transaksi_layanan_detail');
            $table->dropColumn('catatan_khusus');
        });
    }
};
