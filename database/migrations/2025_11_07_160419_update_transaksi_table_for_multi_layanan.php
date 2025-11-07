<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 0: Tambahkan layanan Bed Cover, Selimut, Seprai jika belum ada
        $this->ensureSeparateLayananExists();

        // STEP 1: Migrate data dari transaksi lama ke transaksi_layanan SEBELUM drop kolom
        $this->migrateOldTransaksiData();

        // STEP 2: Baru kemudian drop kolom lama dan tambah kolom baru
        Schema::table('transaksi', function (Blueprint $table) {
            // Drop old columns yang tidak diperlukan lagi
            $table->dropForeign(['layanan_id']);
            $table->dropColumn(['layanan_id', 'nama_layanan', 'jenis_pakaian', 'berat_kg', 'harga_per_kg']);

            // Add new summary fields
            $table->decimal('total_berat', 8, 2)->default(0)->comment('Total berat dari semua layanan per kg');
            $table->integer('total_item')->default(0)->comment('Total item dari semua layanan per satuan');
            $table->integer('jumlah_layanan')->default(1)->comment('Jumlah layanan dalam transaksi ini');
        });
    }

    /**
     * Ensure Bed Cover, Selimut, Seprai layanan exists
     */
    protected function ensureSeparateLayananExists(): void
    {
        echo "\n🔧 Ensuring separate layanan (Bed Cover, Selimut, Seprai) exists...\n";

        $layananToAdd = [
            ['id' => 4, 'kode' => 'LAY004', 'nama' => 'Bed Cover', 'harga' => 30000],
            ['id' => 5, 'kode' => 'LAY005', 'nama' => 'Selimut', 'harga' => 25000],
            ['id' => 6, 'kode' => 'LAY006', 'nama' => 'Seprai', 'harga' => 10000],
        ];

        foreach ($layananToAdd as $lay) {
            $exists = DB::table('layanan')->where('id', $lay['id'])->exists();
            if (!$exists) {
                DB::table('layanan')->insert([
                    'id' => $lay['id'],
                    'kode_layanan' => $lay['kode'],
                    'nama_layanan' => $lay['nama'],
                    'tipe_layanan' => 'per_satuan',
                    'satuan' => 'lembar',
                    'harga_per_kg' => 0,
                    'harga_per_satuan' => $lay['harga'],
                    'durasi_jam' => 48,
                    'status' => 'Aktif',
                    'deskripsi' => 'Cuci ' . strtolower($lay['nama']) . ' per lembar',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                echo "  ✓ Added layanan: {$lay['nama']}\n";
            } else {
                echo "  - Layanan {$lay['nama']} already exists\n";
            }
        }

        // Hapus Bed Cover, Selimut, Seprai dari jenis_pakaian jika ada
        $deleted = DB::table('jenis_pakaian')
            ->whereIn('nama_jenis', ['Bed Cover', 'bed cover', 'Selimut', 'selimut', 'Seprai', 'seprai'])
            ->delete();

        if ($deleted > 0) {
            echo "  ✓ Removed {$deleted} items from jenis_pakaian\n";
        }

        echo "\n";
    }

    /**
     * Migrate old transaksi data to new structure
     */
    protected function migrateOldTransaksiData(): void
    {
        echo "\n🔄 Migrating old transaksi data to new structure...\n";

        $transaksiList = DB::table('transaksi')
            ->whereNotNull('layanan_id')
            ->get();

        $migratedCount = 0;

        // Mapping untuk item yang harus jadi layanan terpisah
        $itemToLayananMap = [
            'Bed Cover' => 4,  // ID layanan Bed Cover
            'bed cover' => 4,
            'Selimut' => 5,    // ID layanan Selimut
            'selimut' => 5,
            'Seprai' => 6,     // ID layanan Seprai
            'seprai' => 6,
        ];

        foreach ($transaksiList as $transaksi) {
            // Get layanan data
            $layanan = DB::table('layanan')
                ->where('id', $transaksi->layanan_id)
                ->first();

            if (!$layanan) {
                echo "⚠️  Skipping transaksi {$transaksi->id} - layanan not found\n";
                continue;
            }

            // Prepare data untuk transaksi_layanan
            $transaksiLayananData = [
                'transaksi_id' => $transaksi->id,
                'layanan_id' => $transaksi->layanan_id,
                'nama_layanan' => $transaksi->nama_layanan ?? $layanan->nama_layanan,
                'subtotal' => $transaksi->subtotal ?? 0,
                'created_at' => $transaksi->created_at,
                'updated_at' => $transaksi->updated_at,
            ];

            // Tentukan apakah per_kg atau per_satuan
            if ($layanan->tipe_layanan === 'per_kg' || !empty($transaksi->berat_kg)) {
                // Layanan per kg
                $transaksiLayananData['berat_kg'] = $transaksi->berat_kg ?? 0;
                $transaksiLayananData['harga_per_kg'] = $transaksi->harga_per_kg ?? $layanan->harga_per_kg ?? 0;

                // Decode dan encode ulang jenis_pakaian untuk fix double escape
                // DAN pisahkan Bed Cover, Selimut, Seprai jadi layanan terpisah
                if (!empty($transaksi->jenis_pakaian)) {
                    $decoded = null;

                    // Decode JSON
                    if (is_string($transaksi->jenis_pakaian)) {
                        $decoded = json_decode($transaksi->jenis_pakaian, true);
                        if (!is_array($decoded)) {
                            // Try double decode
                            $decoded = json_decode(json_decode($transaksi->jenis_pakaian, true), true);
                        }
                    }

                    if (is_array($decoded)) {
                        $normalJenisPakaian = [];
                        $separateLayananItems = [];

                        // Pisahkan jenis pakaian normal vs yang harus jadi layanan terpisah
                        foreach ($decoded as $jp) {
                            $namaItem = $jp['nama'] ?? '';

                            // Cek apakah ini Bed Cover, Selimut, atau Seprai
                            if (isset($itemToLayananMap[$namaItem])) {
                                // Ini harus jadi layanan terpisah
                                $separateLayananItems[] = [
                                    'layanan_id' => $itemToLayananMap[$namaItem],
                                    'jumlah' => (int) ($jp['jumlah'] ?? 1),
                                    'nama' => $namaItem
                                ];
                            } else {
                                // Ini jenis pakaian normal
                                if (!isset($jp['jenis_id']) && !empty($namaItem)) {
                                    // Cari jenis_id berdasarkan nama
                                    $jenisPakaian = DB::table('jenis_pakaian')
                                        ->where('nama_jenis', 'LIKE', '%' . $namaItem . '%')
                                        ->first();
                                    if ($jenisPakaian) {
                                        $jp['jenis_id'] = (string) $jenisPakaian->id;
                                    } else {
                                        $jp['jenis_id'] = '1';
                                    }
                                }
                                $normalJenisPakaian[] = $jp;
                            }
                        }

                        // Set jenis_pakaian hanya untuk yang normal
                        if (!empty($normalJenisPakaian)) {
                            $transaksiLayananData['jenis_pakaian'] = json_encode($normalJenisPakaian);
                        }

                        // Simpan info separate layanan untuk diproses nanti
                        if (!empty($separateLayananItems)) {
                            $transaksi->_separateLayananItems = $separateLayananItems;
                        }

                        // SKIP layanan per kg jika SEMUA item sudah jadi layanan terpisah
                        if (empty($normalJenisPakaian) && !empty($separateLayananItems)) {
                            $transaksi->_skipMainLayanan = true;
                            echo "  - Skipping main layanan for transaksi {$transaksi->id} (all items converted to separate layanan)\n";
                        }
                    }
                }

                // Recalculate subtotal if empty
                if (empty($transaksiLayananData['subtotal'])) {
                    $berat = (float) ($transaksiLayananData['berat_kg'] ?? 0);
                    $harga = (int) ($transaksiLayananData['harga_per_kg'] ?? 0);
                    $transaksiLayananData['subtotal'] = $berat * $harga;
                }
            } else {
                // Layanan per satuan
                $transaksiLayananData['jumlah_satuan'] = $transaksi->total_item ?? 1;
                $transaksiLayananData['harga_per_satuan'] = $layanan->harga_per_satuan ?? 0;

                // Recalculate subtotal if empty
                if (empty($transaksiLayananData['subtotal'])) {
                    $jumlah = (int) ($transaksiLayananData['jumlah_satuan'] ?? 1);
                    $harga = (int) ($transaksiLayananData['harga_per_satuan'] ?? 0);
                    $transaksiLayananData['subtotal'] = $jumlah * $harga;
                }
            }

            // Insert ke transaksi_layanan (layanan utama) - skip jika semua item jadi layanan terpisah
            if (!isset($transaksi->_skipMainLayanan) || !$transaksi->_skipMainLayanan) {
                DB::table('transaksi_layanan')->insert($transaksiLayananData);
            }

            // Insert layanan terpisah (Bed Cover, Selimut, Seprai) jika ada
            if (isset($transaksi->_separateLayananItems) && !empty($transaksi->_separateLayananItems)) {
                foreach ($transaksi->_separateLayananItems as $sepItem) {
                    $sepLayanan = DB::table('layanan')->where('id', $sepItem['layanan_id'])->first();

                    if ($sepLayanan) {
                        $separateLayananData = [
                            'transaksi_id' => $transaksi->id,
                            'layanan_id' => $sepItem['layanan_id'],
                            'nama_layanan' => $sepItem['nama'],
                            'jumlah_satuan' => $sepItem['jumlah'],
                            'harga_per_satuan' => $sepLayanan->harga_per_satuan ?? 0,
                            'subtotal' => ($sepItem['jumlah'] * ($sepLayanan->harga_per_satuan ?? 0)),
                            'created_at' => $transaksi->created_at,
                            'updated_at' => $transaksi->updated_at,
                        ];

                        DB::table('transaksi_layanan')->insert($separateLayananData);
                        echo "  ✓ Added separate layanan: {$sepItem['nama']} ({$sepItem['jumlah']} lembar) for transaksi {$transaksi->id}\n";
                    }
                }
            }

            $migratedCount++;
        }

        echo "✅ Migration completed! Migrated {$migratedCount} transaksi to new structure.\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            // Add back old columns
            $table->foreignId('layanan_id')->nullable()->constrained('layanan')->onDelete('restrict');
            $table->string('nama_layanan')->nullable();
            $table->json('jenis_pakaian')->nullable();
            $table->decimal('berat_kg', 8, 2)->nullable();
            $table->integer('harga_per_kg')->nullable();

            // Drop new columns
            $table->dropColumn(['total_berat', 'total_item', 'jumlah_layanan']);
        });
    }
};
