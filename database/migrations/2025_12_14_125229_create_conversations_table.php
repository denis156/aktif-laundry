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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // Participant One (polymorphic)
            $table->string('participant_one_type'); // User, Kurir, Pelanggan
            $table->unsignedBigInteger('participant_one_id');

            // Participant Two (polymorphic)
            $table->string('participant_two_type'); // User, Kurir, Pelanggan
            $table->unsignedBigInteger('participant_two_id');

            // Last message info
            $table->timestamp('last_message_at')->nullable();

            $table->timestamps();

            // Indexes untuk performa
            $table->index(['participant_one_type', 'participant_one_id'], 'idx_participant_one');
            $table->index(['participant_two_type', 'participant_two_id'], 'idx_participant_two');
            $table->index('last_message_at');

            // Composite index untuk mencari conversation antara 2 participant
            $table->index([
                'participant_one_type',
                'participant_one_id',
                'participant_two_type',
                'participant_two_id',
            ], 'idx_conversation_pair');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
