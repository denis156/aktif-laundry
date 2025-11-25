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
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Setting key identifier');
            $table->text('value')->comment('Setting value');
            $table->enum('type', ['string', 'number', 'boolean', 'json'])->default('string')->comment('Data type of the value');
            $table->string('group')->default('general')->comment('Group for organization: general, payment, notification, etc');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('key');
            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
