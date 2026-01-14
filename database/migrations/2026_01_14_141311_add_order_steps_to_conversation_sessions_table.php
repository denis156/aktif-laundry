<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new enum values to current_step column for order flow
        DB::statement("ALTER TABLE conversation_sessions MODIFY COLUMN current_step ENUM(
            'ask_name',
            'ask_intent',
            'ask_detail_alamat',
            'ask_location',
            'ask_kelurahan',
            'ask_kecamatan',
            'confirm_data',
            'ask_order_service',
            'confirm_order',
            'completed',
            'cancelled'
        ) DEFAULT 'ask_name'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove new enum values
        DB::statement("ALTER TABLE conversation_sessions MODIFY COLUMN current_step ENUM(
            'ask_name',
            'ask_intent',
            'ask_detail_alamat',
            'ask_location',
            'ask_kelurahan',
            'ask_kecamatan',
            'confirm_data',
            'completed',
            'cancelled'
        ) DEFAULT 'ask_name'");
    }
};
