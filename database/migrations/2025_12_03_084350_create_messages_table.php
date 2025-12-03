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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('target');
            $table->text('message');
            $table->string('url')->nullable();
            $table->string('filename')->nullable();
            $table->string('schedule')->nullable();
            $table->string('typing')->nullable();
            $table->string('delay')->nullable();
            $table->string('countryCode')->nullable();
            $table->string('file')->nullable();
            $table->string('location')->nullable();
            $table->string('followup')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
