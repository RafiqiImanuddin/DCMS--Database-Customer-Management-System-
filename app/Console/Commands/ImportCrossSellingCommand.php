<?php

namespace App\Console\Commands;

use App\Models\CrossSellingImport;
use App\Models\User;
use App\Services\CrossSellingImporter;
use App\Services\RepeatOrderPredictor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImportCrossSellingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:cross-selling {file : Path file Excel atau CSV} {--user-id= : ID user pemilik import (default: admin pertama)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import file cross-selling (XLSX / CSV) langsung ke database Supabase';

    /**
     * Execute the console command.
     */
    public function handle(CrossSellingImporter $importer, RepeatOrderPredictor $predictor): int
    {
        $filePath = $this->argument('file');

        if (! File::exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $userId = $this->option('user-id') ?? User::first()?->id ?? 1;
        $fileName = basename($filePath);
        $fileSizeMb = round(File::size($filePath) / 1024 / 1024, 2);

        $this->info("Menyiapkan import file: {$fileName} ({$fileSizeMb} MB)...");

        // Simpan file ke storage local menggunakan copy agar hemat memori
        $targetDir = Storage::disk('local')->path('cross-selling-imports');
        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }
        $storedName = 'cross-selling-imports/' . uniqid() . '-' . $fileName;
        File::copy($filePath, Storage::disk('local')->path($storedName));

        $import = CrossSellingImport::create([
            'user_id' => $userId,
            'original_name' => $fileName,
            'stored_path' => $storedName,
            'status' => 'processing',
        ]);

        $this->info("Memproses baris data ke database Supabase...");
        $startTime = microtime(true);

        try {
            $importer->import($import);
            $import->refresh();

            $elapsed = round(microtime(true) - $startTime, 2);

            if ($import->status === 'completed') {
                $this->info("Import data berhasil ({$elapsed} detik)!");
                $this->line("  - Total Baris  : " . number_format($import->row_count));
                $this->line("  - Berhasil     : " . number_format($import->success_count));
                $this->line("  - Gagal        : " . number_format($import->failed_count));
                $this->line("  - Periode      : " . ($import->batch_label ?? '-'));

                $this->info("Membangun model prediksi repeat order (ML)...");
                $predictor->rebuild();
                $this->info("Prediksi repeat order selesai dibangun!");

                return Command::SUCCESS;
            }

            $this->error("Import gagal: " . $import->error_log);
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Terjadi error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
