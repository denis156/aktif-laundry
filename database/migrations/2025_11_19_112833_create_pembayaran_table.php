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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pembayaran')->unique()->comment('PBY001, PBY002, etc');

            // Relations
            $table->foreignId('transaksi_id')->constrained('transaksi')->onDelete('cascade');

            // Payment details
            $table->integer('jumlah_bayar')->comment('Jumlah yang dibayarkan');
            $table->integer('kembalian')->default(0)->comment('Uang kembalian');
            $table->enum('metode', ['Tunai', 'Transfer', 'QRIS', 'Debit', 'E-Wallet']);
            $table->dateTime('tanggal_bayar');

            // Verification
            $table->enum('status', ['Pending', 'Verified', 'Rejected'])->default('Pending');
            $table->string('bukti_transfer')->nullable()->comment('Path foto bukti transfer');
            $table->dateTime('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('catatan')->nullable();

            // Flexible data storage
            $table->jsonb('metadata')->nullable()->comment('Flexible data: bank_tujuan, nama_pengirim, dll');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_pembayaran');
            $table->index('transaksi_id');
            $table->index('status');
            $table->index('metode');
            $table->index('tanggal_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
