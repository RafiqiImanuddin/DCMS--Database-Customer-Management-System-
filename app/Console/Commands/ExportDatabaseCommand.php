<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ExportDatabaseCommand extends Command
{
    protected $signature = 'db:export-cpanel {--full : Export full database including heavy transactions}';
    protected $description = 'Export lightweight core database and modular data for cPanel / phpMyAdmin';

    public function handle(): int
    {
        $this->info('🚀 Memulai ekspor database DCMS (Core Database & Modular Data)...');

        $dumpsDir = database_path('dumps');
        if (! is_dir($dumpsDir)) {
            mkdir($dumpsDir, 0755, true);
        }

        $coreSqlPath = $dumpsDir . DIRECTORY_SEPARATOR . 'dcms_cpanel_core.sql';
        $rootCorePath = database_path('dcms_core_database.sql');
        $keepsSqlPath = $dumpsDir . DIRECTORY_SEPARATOR . 'dcms_data_keeps.sql';
        $keepsZipPath = $dumpsDir . DIRECTORY_SEPARATOR . 'dcms_data_keeps.sql.zip';
        $fullSqlPath = $dumpsDir . DIRECTORY_SEPARATOR . 'dcms_cpanel_full.sql';
        $fullZipPath = $dumpsDir . DIRECTORY_SEPARATOR . 'dcms_cpanel_full.sql.zip';

        // Daftar tabel core yang datanya wajib disertakan
        $coreDataTables = [
            'migrations',
            'users',
            'settings',
            'employees',
            'kpi_criteria',
            'broadcast_templates',
        ];

        // Daftar tabel transaksi berat yang dipisahkan
        $heavyDataTables = [
            'sosmed_keeps',
            'cross_sellings',
            'turnovers',
            'repeat_order_predictions',
            'whatsapp_messages',
            'whatsapp_chats',
            'cross_selling_imports',
            'turnover_imports',
            'broadcast_batches',
            'broadcast_reports',
            'broadcast_media',
            'lead_customers',
            'sales_assessments',
            'activity_notifications',
            'cache',
            'cache_locks',
            'sessions',
            'jobs',
            'job_batches',
            'failed_jobs',
            'password_reset_tokens',
            'quotations',
            'sosmed_shifts',
        ];

        // Ambil semua tabel yang ada di database saat ini secara dinamis
        $allTables = $this->getAllTableNames();
        $this->info('📊 Ditemukan ' . count($allTables) . ' tabel pada database ' . config('database.connections.mysql.database'));

        // =========================================================================
        // 1. GENERATE CORE DATABASE SQL (Ultra-Lightweight: ~60-80 KB)
        // =========================================================================
        $this->info('📦 1. Menghasilkan dcms_cpanel_core.sql (Skema 29 Tabel Lengkap + Data Akun & Master)...');
        $coreHandle = fopen($coreSqlPath, 'w');
        $this->writeHeader($coreHandle, 'CORE DATABASE (Ultra-Fast Import)');
        $this->writeDynamicSchema($allTables, $coreHandle);

        foreach ($coreDataTables as $table) {
            if (in_array($table, $allTables, true)) {
                $this->exportTableData($table, $coreHandle, isCore: true);
            }
        }

        $this->writeFooter($coreHandle);
        fclose($coreHandle);

        // Salin juga ke database/dcms_core_database.sql
        copy($coreSqlPath, $rootCorePath);

        $coreSize = filesize($coreSqlPath);
        $this->info("   ✅ Selesai: {$coreSqlPath} (" . $this->formatSize($coreSize) . ')');
        $this->info("   ✅ Selesai: {$rootCorePath} (" . $this->formatSize($coreSize) . ')');

        // =========================================================================
        // 2. GENERATE MODULAR DATA KEEP SQL (Khusus Data Format Keep ~13k baris)
        // =========================================================================
        if (in_array('sosmed_keeps', $allTables, true)) {
            $keepCount = DB::table('sosmed_keeps')->count();
            if ($keepCount > 0) {
                $this->info("📦 2. Menghasilkan dcms_data_keeps.sql ({$keepCount} baris format keep)...");
                $keepHandle = fopen($keepsSqlPath, 'w');
                $this->writeHeader($keepHandle, 'DATA SOSMED KEEPS ONLY');
                $this->exportTableData('sosmed_keeps', $keepHandle, isCore: false);
                $this->writeFooter($keepHandle);
                fclose($keepHandle);

                $keepSize = filesize($keepsSqlPath);
                $this->info("   ✅ Selesai: {$keepsSqlPath} (" . $this->formatSize($keepSize) . ')');

                // Kompres data keep ke .zip
                $zipKeep = new ZipArchive();
                if ($zipKeep->open($keepsZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $zipKeep->addFile($keepsSqlPath, 'dcms_data_keeps.sql');
                    $zipKeep->close();
                    $this->info("   🗜️  Selesai ZIP: {$keepsZipPath} (" . $this->formatSize(filesize($keepsZipPath)) . ')');
                }
            }
        }

        // =========================================================================
        // 3. GENERATE FULL DATABASE SQL (Jika opsi --full dipanggil)
        // =========================================================================
        if ($this->option('full')) {
            $this->info('📦 3. Menghasilkan dcms_cpanel_full.sql (Termasuk seluruh tabel dan transaksi)...');
            $fullHandle = fopen($fullSqlPath, 'w');
            $this->writeHeader($fullHandle, 'FULL DATABASE DUMP');
            $this->writeDynamicSchema($allTables, $fullHandle);

            foreach ($allTables as $table) {
                $this->exportTableData($table, $fullHandle, isCore: false);
            }

            $this->writeFooter($fullHandle);
            fclose($fullHandle);

            $this->info("   ✅ Selesai: {$fullSqlPath} (" . $this->formatSize(filesize($fullSqlPath)) . ')');

            $zipFull = new ZipArchive();
            if ($zipFull->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zipFull->addFile($fullSqlPath, 'dcms_cpanel_full.sql');
                $zipFull->close();
                $this->info("   🗜️  Selesai ZIP: {$fullZipPath} (" . $this->formatSize(filesize($fullZipPath)) . ')');
            }
        }

        $this->newLine();
        $this->info('🎉 Ekspor Database Selesai!');
        $this->line("1. File Core Ringan (~80 KB, Rekomendasi cPanel): <info>{$coreSqlPath}</info>");
        $this->line("2. Data Keeps Terpisah (.zip): <info>{$keepsZipPath}</info>");
        $this->line("3. Impor Data Keeps via CLI: <comment>php artisan keep:import-csv</comment>");
        $this->line("4. Impor Data Keeps via Browser: <comment>https://domain/system/migrate?key=dcms2026&action=import-keeps</comment>");

        return Command::SUCCESS;
    }

    /**
     * Dapatkan semua nama tabel di database secara dinamis.
     *
     * @return array<int, string>
     */
    private function getAllTableNames(): array
    {
        $tables = DB::select('SHOW TABLES');
        $names = [];
        foreach ($tables as $t) {
            $arr = (array) $t;
            $names[] = (string) reset($arr);
        }

        return $names;
    }

    private function writeHeader($handle, string $title = 'DCMS DATABASE EXPORT'): void
    {
        $date = now()->toDateTimeString();
        $header = <<<SQL
-- ========================================================
-- Database Customer Management System (DCMS)
-- {$title}
-- Generated: {$date}
-- Target: MySQL / MariaDB (cPanel / Localhost)
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


SQL;
        fwrite($handle, $header);
    }

    private function writeFooter($handle): void
    {
        $footer = <<<SQL

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- Ekspor Selesai
-- ========================================================

SQL;
        fwrite($handle, $footer);
    }

    /**
     * Tulis struktur DDL tabel secara dinamis dari SHOW CREATE TABLE.
     *
     * @param  array<int, string>  $tables
     */
    private function writeDynamicSchema(array $tables, $handle): void
    {
        foreach ($tables as $table) {
            fwrite($handle, "\n-- --------------------------------------------------------\n");
            fwrite($handle, "-- Struktur tabel untuk `{$table}`\n");
            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            $res = DB::select("SHOW CREATE TABLE `{$table}`");
            $arr = (array) $res[0];
            $createSql = $arr['Create Table'] ?? reset($arr);
            fwrite($handle, $createSql . ";\n");
        }
    }

    private function exportTableData(string $tableName, $handle, bool $isCore = false): void
    {
        $query = DB::table($tableName);

        // Jika tabel settings dan ini core dump, kecualikan file base64 raksasa agar SQL tetap ringan (~60KB)
        if ($tableName === 'settings' && $isCore) {
            $query->where('key', '!=', 'workspace_turnover_file_base64');
        }

        $count = $query->count();
        if ($count === 0) {
            return;
        }

        $this->line("   -> Mengekspor data `{$tableName}` ({$count} baris)...");
        fwrite($handle, "\n-- --------------------------------------------------------\n");
        fwrite($handle, "-- Dumping data untuk tabel `{$tableName}` ({$count} baris)\n");
        fwrite($handle, "-- --------------------------------------------------------\n");

        $chunkSize = 500;
        $columns = [];

        $query->orderBy(DB::raw('1'))->chunk($chunkSize, function ($rows) use ($handle, $tableName, &$columns) {
            if ($rows->isEmpty()) {
                return;
            }

            if (empty($columns)) {
                $first = (array) $rows->first();
                $columns = array_keys($first);
            }

            $colList = '`' . implode('`, `', $columns) . '`';
            $valuesList = [];

            foreach ($rows as $row) {
                $rowArray = (array) $row;
                $rowValues = [];
                foreach ($columns as $col) {
                    $val = $rowArray[$col] ?? null;
                    if ($val === null) {
                        $rowValues[] = 'NULL';
                    } elseif (is_bool($val)) {
                        $rowValues[] = $val ? '1' : '0';
                    } elseif (is_numeric($val) && ! is_string($val)) {
                        $rowValues[] = $val;
                    } else {
                        $escaped = str_replace(
                            ["\\", "\x00", "\n", "\r", "'", '"', "\x1a"],
                            ["\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z"],
                            (string) $val
                        );
                        $rowValues[] = "'{$escaped}'";
                    }
                }
                $valuesList[] = '(' . implode(', ', $rowValues) . ')';
            }

            $sql = "INSERT INTO `{$tableName}` ({$colList}) VALUES\n" . implode(",\n", $valuesList) . ";\n";
            fwrite($handle, $sql);
        });
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
