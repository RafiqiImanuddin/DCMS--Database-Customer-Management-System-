<?php

namespace App\Console\Commands;

use App\Models\SosmedKeep;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportKeepCsvCommand extends Command
{
    protected $signature = 'keep:import-csv {file?} {--truncate : Kosongkan tabel sebelum impor}';

    protected $description = 'Impor data dari keep-data.csv ke tabel sosmed_keeps MySQL';

    public function handle(): int
    {
        $filePath = $this->argument('file') 
            ?: public_path('workspace-sosmed' . DIRECTORY_SEPARATOR . 'keep-data.csv');

        if (! file_exists($filePath)) {
            $this->error("File CSV tidak ditemukan: {$filePath}");
            return 1;
        }

        if ($this->option('truncate')) {
            $this->warn('Mengosongkan tabel sosmed_keeps...');
            DB::table('sosmed_keeps')->truncate();
        }

        $this->info("Membuka file: {$filePath}");
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            $this->error('Gagal membuka file CSV.');
            return 1;
        }

        // Header mapping
        $header = fgetcsv($handle);
        if (! $header) {
            $this->error('File CSV kosong.');
            fclose($handle);
            return 1;
        }

        // Clean BOM and lowercase header keys
        $cleanHeader = array_map(function ($col) {
            $c = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', trim((string) $col));
            return strtolower($c);
        }, $header);

        $this->info('Memulai impor batch...');
        $batch = [];
        $batchSize = 1000;
        $totalImported = 0;
        $now = now();

        $parseDate = function (?string $val): ?string {
            if (! $val || trim($val) === '' || trim($val) === '-') {
                return null;
            }
            try {
                return Carbon::parse(trim($val))->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                return null;
            }
        };

        $cleanUtf8 = function (?string $str, int $limit = 0): string {
            if ($str === null || $str === '') {
                return '';
            }
            // Ensure valid UTF-8
            $clean = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
            // Clean invalid non-printable control characters except newline and tab
            $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);
            $clean = trim((string) $clean);
            if ($limit > 0 && mb_strlen($clean) > $limit) {
                return mb_substr($clean, 0, $limit);
            }
            return $clean;
        };

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 1 && empty($row[0])) {
                continue;
            }

            $data = [];
            foreach ($cleanHeader as $idx => $key) {
                $data[$key] = $row[$idx] ?? null;
            }

            $rawWA = preg_replace('/\D/', '', (string) ($data['nomor wa'] ?? $data['no wa'] ?? $data['nomor'] ?? ''));
            if ($rawWA !== '' && str_starts_with($rawWA, '0')) {
                $rawWA = '62' . substr($rawWA, 1);
            }

            $tglInputRaw = $cleanUtf8($data['tanggal input ar'] ?? null);
            $parsedTglInput = $parseDate($tglInputRaw);

            $nama = $cleanUtf8($data['nama'] ?? '', 150);
            $barangKeep = $cleanUtf8($data['barang keep'] ?? '');
            $keepTs = $parseDate($cleanUtf8($data['timestamp'] ?? null));

            // Skip empty/ghost rows from spreadsheet formulas (e.g. Desember-99 artefacts)
            if (empty($nama) && empty($barangKeep) && empty($rawWA) && empty($keepTs)) {
                continue;
            }

            $batch[] = [
                'message_id' => null,
                'keep_timestamp' => $keepTs,
                'nomor_sender' => $cleanUtf8($data['nomor sender'] ?? '', 35),
                'grup_id' => $cleanUtf8($data['grup id'] ?? '', 120),
                'sender' => $cleanUtf8($data['sender'] ?? '', 120),
                'grup' => $cleanUtf8($data['grup'] ?? '', 150),
                'jenis_chat' => $cleanUtf8($data['!grup'] ?? 'GRUP', 20),
                'nama' => $nama,
                'nomor_wa' => $cleanUtf8($rawWA, 35),
                'barang_keep' => $barangKeep,
                'tanggal_ambil' => $cleanUtf8($data['tanggal ambil'] ?? '', 100),
                'catatan' => $cleanUtf8($data['catatan'] ?? ''),
                'remarks' => $cleanUtf8($data['remarks'] ?? '', 120),
                'kode_sosmed' => $cleanUtf8($data['kode sosmed'] ?? '', 60),
                'report_ar' => $cleanUtf8($data['report ar'] ?? '', 50),
                'tanggal_input_ar' => $parsedTglInput,
                'ar_vlookup' => $cleanUtf8($data['ar dari vlookup'] ?? '', 50),
                'tanggal_vlookup' => $cleanUtf8($data['tanggal vlookup'] ?? '', 50),
                'cabang' => $cleanUtf8($data['cabang'] ?? '', 50),
                'evidence' => $cleanUtf8($data['evidence'] ?? ''),
                'nomor_follow_up' => $cleanUtf8($data['nomor follow up'] ?? '', 35),
                'nama_tim_follow_up' => $cleanUtf8($data['nama tim follow up'] ?? '', 120),
                'status' => $cleanUtf8($data['status'] ?? '', 50),
                'feedback_tim_fu' => $cleanUtf8($data['feedback tim fu'] ?? ''),
                'nama_toko_kompetitor' => $cleanUtf8($data['nama toko kompetitor'] ?? '', 150),
                'user_kredit' => $cleanUtf8($data['user kredit'] ?? '', 50),
                'periode' => $cleanUtf8($data['periode'] ?? '', 35),
                'item' => $cleanUtf8($data['item'] ?? '', 150),
                'nomor_fu' => $cleanUtf8($data['nomor fu'] ?? '', 50),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('sosmed_keeps')->insert($batch);
                $totalImported += count($batch);
                $this->info("Telah mengimpor {$totalImported} baris...");
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            DB::table('sosmed_keeps')->insert($batch);
            $totalImported += count($batch);
        }

        fclose($handle);

        $this->info("✅ SELESAI! Sebanyak {$totalImported} baris data keep berhasil dimigrasikan ke MySQL!");
        return 0;
    }
}
