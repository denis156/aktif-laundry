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
        Schema::table('layanan', function (Blueprint $table) {
            // tipe_layanan, harga_per_satuan, satuan already added from v1 migrations
            // Just ensure harga_per_kg allows default 0
            $table->integer('harga_per_kg')->default(0)->change();

            // Add new v2 fields
            $table->integer('min_order')->nullable()->comment('Minimum order (kg atau satuan)')->after('status');
            $table->integer('max_order')->nullable()->comment('Maximum order (kg atau satuan)')->after('min_order');
            $table->boolean('is_popular')->default(false)->comment('Apakah layanan populer')->after('max_order');
            $table->string('icon', 100)->nullable()->comment('Icon untuk UI')->after('is_popular');

            // JSON fields untuk include/exclude list
            $table->json('include')->nullable()->comment('Array of items included in service, e.g., ["Cuci", "Setrika", "Lipat"]')->after('icon');
            $table->json('exclude')->nullable()->comment('Array of items excluded from service, e.g., ["Bed Cover", "Karpet"]')->after('include');

            // Add new indexes
            $table->index('is_popular', 'idx_layanan_popular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layanan', function (Blueprint $table) {
            $table->dropIndex('idx_layanan_popular');

            $table->dropColumn([
                'min_order',
                'max_order',
                'is_popular',
                'icon',
                'include',
                'exclude',
            ]);
        });
    }
};
