<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     * Mengubah tipe_diskon dari enum ke varchar agar bisa dinamis
     */
    public function up(): void
    {
        // Drop constraint enum lama
        DB::statement('ALTER TABLE promo DROP CONSTRAINT IF EXISTS promo_tipe_diskon_check');

        // Ubah kolom ke varchar
        DB::statement('ALTER TABLE promo ALTER COLUMN tipe_diskon TYPE varchar(50)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum (hanya persen dan nominal yang sudah ada)
        DB::statement("ALTER TABLE promo ADD CONSTRAINT promo_tipe_diskon_check CHECK (tipe_diskon IN ('persen', 'nominal'))");
    }
};
