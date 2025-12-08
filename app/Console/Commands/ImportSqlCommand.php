<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSqlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:import-sql {filename} {--connection=mysql}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import SQL file to database. Drops all tables first if database exists.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filename = $this->argument('filename');
        $connection = $this->option('connection');

        // Build full path
        $sqlFile = database_path($filename);

        if (!file_exists($sqlFile)) {
            $this->error("SQL file not found: {$sqlFile}");

            return self::FAILURE;
        }

        $this->info("Importing SQL from: {$filename}");
        $this->info("Connection: {$connection}");

        if (!$this->confirm('This will DROP ALL TABLES in the database. Continue?', false)) {
            $this->info('Import cancelled.');

            return self::SUCCESS;
        }

        try {
            $pdo = DB::connection($connection)->getPdo();

            // Get database name
            $database = DB::connection($connection)->getDatabaseName();
            $this->info("Database: {$database}");

            // Disable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

            // Drop all tables
            $this->info('Dropping all existing tables...');
            $tables = DB::connection($connection)
                ->select('SHOW TABLES');

            $tableKey = "Tables_in_{$database}";
            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                $this->line("  - Dropping table: {$tableName}");
                DB::connection($connection)->statement("DROP TABLE IF EXISTS `{$tableName}`");
            }

            // Read and execute SQL file
            $this->info('Importing SQL file...');
            $sql = file_get_contents($sqlFile);

            $pdo->exec($sql);

            // Re-enable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

            $this->info('✓ SQL file imported successfully!');

            // Mark all CREATE TABLE migrations as completed
            $this->newLine();
            $this->info('Marking CREATE TABLE migrations as completed...');

            $createMigrations = [
                // Laravel base tables
                '0001_01_01_000000_create_users_table',
                '0001_01_01_000001_create_cache_table',
                '0001_01_01_000002_create_jobs_table',

                // V1 migrations
                '2025_11_07_160253_add_tipe_layanan_to_layanan_table',
                '2025_11_07_160302_create_transaksi_layanan_table',
                '2025_11_07_160419_update_transaksi_table_for_multi_layanan',
                '2025_11_07_171215_add_deleted_at_to_transaksi_layanan_table',

                // V2 CREATE migrations
                '2025_11_19_112258_create_pelanggan_table',
                '2025_11_19_112544_create_jenis_pakaian_table',
                '2025_11_19_112601_create_layanan_table',
                '2025_11_19_112620_create_pengaturan_table',
                '2025_11_19_112630_create_promo_table',
                '2025_11_19_112635_create_kurir_table',
                '2025_11_19_112640_create_referral_table',
                '2025_11_19_112647_create_transaksi_table',
                '2025_11_19_112713_create_transaksi_layanan_table',
                '2025_11_19_112804_create_pengiriman_table',
                '2025_11_26_071439_create_transaksi_promo_table',
                '2025_12_03_084350_create_messages_table',
                '2025_12_08_112526_create_v2_new_tables_promo_kurir_pengiriman_referral',
            ];

            $batch = 1;
            foreach ($createMigrations as $migration) {
                DB::connection($connection)->table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $batch,
                ]);
            }

            $this->info('✓ Marked ' . count($createMigrations) . ' CREATE migrations as completed');

            // Show table counts
            $this->newLine();
            $this->info('Table counts:');
            $tables = DB::connection($connection)->select('SHOW TABLES');

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                $count = DB::connection($connection)->table($tableName)->count();
                $this->line("  - {$tableName}: {$count} records");
            }

            $this->newLine();
            $this->info('✓ Ready for migrations! Run: php artisan migrate --force');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
