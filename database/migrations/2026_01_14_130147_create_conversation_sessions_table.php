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
        Schema::create('conversation_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 20)->index();
            $table->enum('flow_type', ['registration', 'order', 'inquiry'])->default('inquiry');
            $table->enum('current_step', [
                'ask_name',
                'ask_intent',
                'ask_detail_alamat',
                'ask_location',
                'ask_kelurahan',
                'ask_kecamatan',
                'confirm_data',
                'completed',
                'cancelled',
            ])->default('ask_name');
            $table->json('collected_data')->nullable();
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_sessions');
    }
};
