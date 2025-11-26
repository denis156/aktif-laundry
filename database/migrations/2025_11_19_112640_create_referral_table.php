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
        Schema::create('referral', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade')->comment('Pelanggan yang punya kode referral ini');
            $table->string('kode_referral', 20)->unique()->comment('Kode referral unik, e.g., REF-ABC123');

            // Promo yang akan diberikan
            $table->foreignId('promo_referee_id')->nullable()->constrained('promo')->nullOnDelete()->comment('Promo yang diberikan ke orang yang pakai kode ini (referee)');
            $table->foreignId('promo_referrer_id')->nullable()->constrained('promo')->nullOnDelete()->comment('Promo yang diberikan ke pemilik kode referral (referrer)');

            // Statistics sederhana
            $table->integer('total_referral')->default(0)->comment('Berapa orang yang pakai kode ini');
            $table->integer('total_berhasil')->default(0)->comment('Berapa referral yang berhasil (sudah transaksi min amount)');

            // Campaign tracking
            $table->string('campaign_source', 100)->nullable()->comment('Source campaign: instagram, facebook, whatsapp, etc');

            $table->timestamps();

            // Indexes
            $table->index('pelanggan_id');
            $table->index('kode_referral');
            $table->index('promo_referee_id');
            $table->index('promo_referrer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral');
    }
};
