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
        Schema::create('chatbot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('system_prompt');
            $table->boolean('is_active')->default(true);
            $table->boolean('enable_chatbot')->default(true);
            $table->integer('max_tokens')->default(65536);
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->integer('timeout')->default(25);
            $table->text('fallback_message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_settings');
    }
};
