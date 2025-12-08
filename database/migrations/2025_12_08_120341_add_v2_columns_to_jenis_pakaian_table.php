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
        Schema::table('jenis_pakaian', function (Blueprint $table) {
            // Add new v2 fields
            $table->text('penanganan_khusus')->nullable()->comment('Instruksi penanganan khusus untuk jenis pakaian ini')->after('keterangan');
            $table->string('icon', 100)->nullable()->comment('Icon untuk UI (iconpark format)')->after('penanganan_khusus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_pakaian', function (Blueprint $table) {
            $table->dropColumn([
                'penanganan_khusus',
                'icon',
            ]);
        });
    }
};
