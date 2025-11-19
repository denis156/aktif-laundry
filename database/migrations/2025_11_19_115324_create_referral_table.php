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
        Schema::create('referral', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade');
            $table->string('kode_referral')->unique()->comment('REF-ABC123');

            // Reward Configuration
            $table->integer('poin_referrer')->default(10000)->comment('Poin untuk yang punya kode');
            $table->integer('diskon_referee')->default(25000)->comment('Diskon untuk yang pakai kode');
            $table->integer('min_transaksi_referee')->default(50000)->comment('Min transaksi pertama untuk claim');

            // Statistics
            $table->integer('total_referral')->default(0)->comment('Berapa orang yang pakai kode ini');
            $table->integer('total_poin')->default(0)->comment('Total poin yang didapat');
            $table->integer('total_berhasil')->default(0)->comment('Berapa referral yang berhasil (sudah transaksi)');

            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');

            // Flexible data storage
            $table->jsonb('metadata')->nullable()->comment('Flexible data: reward_history, campaign_source, dll');

            $table->timestamps();

            // Indexes
            $table->index('pelanggan_id');
            $table->index('kode_referral');
            $table->index('status');
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
