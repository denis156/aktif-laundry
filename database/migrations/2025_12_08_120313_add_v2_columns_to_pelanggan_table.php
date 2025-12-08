<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, clean up empty emails to NULL before adding unique constraint
        DB::table('pelanggan')->where('email', '')->update(['email' => null]);

        // Handle duplicate emails by appending pelanggan_id to make them unique
        // Keep the first occurrence, modify the rest
        $duplicateEmails = DB::table('pelanggan')
            ->select('email', DB::raw('COUNT(*) as count'))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->groupBy('email')
            ->having('count', '>', 1)
            ->pluck('email');

        foreach ($duplicateEmails as $email) {
            $pelanggans = DB::table('pelanggan')
                ->where('email', $email)
                ->orderBy('id')
                ->get();

            // Skip first one, modify the rest
            foreach ($pelanggans->skip(1) as $pelanggan) {
                DB::table('pelanggan')
                    ->where('id', $pelanggan->id)
                    ->update(['email' => $pelanggan->id . '_' . $email]);
            }
        }

        Schema::table('pelanggan', function (Blueprint $table) {
            // Modify existing columns
            $table->string('no_hp', 15)->nullable()->change();
            $table->string('email')->nullable()->unique()->change();

            // Add new v2 columns
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('password')->nullable()->comment('Password untuk login aplikasi customer (optional)')->after('email_verified_at');
            $table->string('device_token')->nullable()->comment('token untuk push notification')->after('password');

            // Modify alamat to be nullable
            $table->text('alamat')->nullable()->change();

            // Add detail alamat and location fields
            $table->text('detail_alamat')->nullable()->comment('Detail alamat atau patokan (jalan, nomor, RT/RW)')->after('alamat');
            $table->string('kelurahan', 100)->nullable()->index()->after('detail_alamat');
            $table->string('kecamatan', 100)->nullable()->index()->after('kelurahan');
            $table->string('kabupaten_kota', 100)->default('Kota Kendari')->after('kecamatan');
            $table->string('provinsi', 100)->default('Sulawesi Tenggara')->after('kabupaten_kota');
            $table->decimal('latitude', 10, 8)->nullable()->after('provinsi');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');

            // Move tanggal_daftar and status after location fields
            // They already exist, no need to recreate

            // Add membership & loyalty fields
            $table->integer('loyalty_points')->default(0)->comment('Poin loyalty pelanggan')->after('status');
            $table->string('member_card', 50)->nullable()->comment('Nomor kartu member')->after('loyalty_points');
            $table->string('avatar_url', 255)->nullable()->comment('URL avatar pelanggan')->after('member_card');

            // Add referral foreign key
            $table->foreignId('direferensikan_oleh')->nullable()->after('avatar_url')->constrained('pelanggan')->onDelete('set null')->comment('ID pelanggan yang nge-refer');

            // Add remember token
            $table->rememberToken()->after('direferensikan_oleh');

            // Drop old total_transaksi column
            $table->dropColumn('total_transaksi');

            // Add new indexes
            $table->index('loyalty_points');
            $table->index('direferensikan_oleh');
            $table->index(['status', 'tanggal_daftar'], 'idx_pelanggan_status_tanggal');
            $table->index(['latitude', 'longitude'], 'idx_pelanggan_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('idx_pelanggan_status_tanggal');
            $table->dropIndex('idx_pelanggan_location');
            $table->dropIndex(['loyalty_points']);
            $table->dropIndex(['direferensikan_oleh']);

            // Drop foreign key
            $table->dropForeign(['direferensikan_oleh']);

            // Drop columns
            $table->dropColumn([
                'email_verified_at',
                'password',
                'device_token',
                'detail_alamat',
                'kelurahan',
                'kecamatan',
                'kabupaten_kota',
                'provinsi',
                'latitude',
                'longitude',
                'loyalty_points',
                'member_card',
                'avatar_url',
                'direferensikan_oleh',
                'remember_token',
            ]);

            // Add back total_transaksi
            $table->integer('total_transaksi')->default(0);
        });
    }
};
