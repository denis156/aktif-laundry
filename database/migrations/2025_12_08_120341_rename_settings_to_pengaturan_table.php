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
        // Rename table from settings to pengaturan
        Schema::rename('settings', 'pengaturan');

        // Add new v2 columns
        Schema::table('pengaturan', function (Blueprint $table) {
            $table->enum('type', ['string', 'number', 'boolean', 'json'])->default('string')->comment('Data type of the value')->after('value');
            $table->string('group')->default('general')->comment('Group for organization: general, payment, notification, etc')->after('type');

            // Add index for group
            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan', function (Blueprint $table) {
            $table->dropIndex(['group']);
            $table->dropColumn(['type', 'group']);
        });

        // Rename back to settings
        Schema::rename('pengaturan', 'settings');
    }
};
