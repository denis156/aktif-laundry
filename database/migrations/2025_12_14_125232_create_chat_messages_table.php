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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();

            // Conversation reference
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');

            // Sender (polymorphic)
            $table->string('sender_type'); // User, Kurir, Pelanggan
            $table->unsignedBigInteger('sender_id');

            // Message content
            $table->text('message');

            // File attachment (optional)
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable(); // image, document, pdf, etc
            $table->unsignedInteger('file_size')->nullable(); // in bytes

            // Read receipt
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes untuk performa
            $table->index(['conversation_id', 'created_at']);
            $table->index(['sender_type', 'sender_id'], 'idx_sender');
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
