<?php

namespace App\Console\Commands;

use App\Models\SosmedKeep;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncGoogleSheetKeepCommand extends Command
{
    protected $signature = 'keep:sync-google-sheet 
                            {--url= : URL Google Spreadsheet} 
                            {--sheet=DATA KEEP : Nama sheet yang akan disinkron}
                            {--truncate : Kosongkan tabel sebelum sinkronisasi}';

    protected $description = 'Sinkronisasi data format keep dari Google Spreadsheet real ke tabel sosmed_keeps MySQL';

    public function handle(): int
    {
        $rawUrl = (string) ($this->option('url') ?: 'https://docs.google.com/spreadsheets/d/1V0pIa0NZCprJqRwnSnikFaLZ-dd50nY0uE3bjVYsUe0/edit?gid=848355344#gid=848355344');
        $sheetName = (string) ($this->option('sheet') ?: 'DATA KEEP');

        // Extract Spreadsheet ID and GID
        if (preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $rawUrl, $matches)) {
            $spreadsheetId = $matches[1];
        } else {
            $spreadsheetId = '1V0pIa0NZCprJqRwnSnikFaLZ-dd50nY0uE3bjVYsUe0';
        }

        $gid = '1014933310';
        if (preg_match('#[?&#]gid=([0-9]+)#', $rawUrl, $gidMatches)) {
            $gid = $gidMatches[1];
        }

        $exportUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv&gid={$gid}";

        $this->info("Menghubungi Google Spreadsheet ID: {$spreadsheetId} (GID: {$gid})");
        $this->info("Sheet Target: {$sheetName}");
        $this->info("URL Export: {$exportUrl}");

        $response = Http::timeout(120)
            ->withoutVerifying()
            ->get($exportUrl);

        if (! $response->successful()) {
            $this->error("Gagal mengunduh spreadsheet. Status HTTP: " . $response->status());
            return 1;
        }

        $body = $response->body();
        if (strlen($body) < 50) {
            $this->error("Respons spreadsheet terlalu pendek atau kosong.");
            return 1;
        }

        if ($this->option('truncate')) {
            $this->warn('Mengosongkan tabel sosmed_keeps...');
            DB::table('sosmed_keeps')->truncate();
        }

        // Simpan snapshot lokal untuk backup
        $localSnapshot = public_path('workspace-sosmed' . DIRECTORY_SEPARATOR . 'keep-data.csv');
        file_put_contents($localSnapshot, $body);
        $this->info("Snapshot lokal berhasil dicadangkan ke: {$localSnapshot}");

        // Parse CSV stream dari memory string
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $body);
        rewind($stream);

        $header = fgetcsv($stream);
        if (! $header) {
            $this->error('Header CSV tidak ditemukan.');
            fclose($stream);
            return 1;
        }

        $cleanHeader = array_map(function ($col) {
            $c = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', trim((string) $col));
            return strtolower($c);
        }, $header);

        $cleanUtf8 = function (?string $str, int $limit = 0): string {
            if ($str === null || $str === '') {
                return '';
            }
            $clean = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
            $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);
            $clean = trim((string) $clean);
            if ($limit > 0 && mb_strlen($clean) > $limit) {
                return mb_substr($clean, 0, $limit);
            }
            return $clean;
        };

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

        $batch = [];
        $batchSize = 1000;
        $totalImported = 0;
        $now = now();

        $this->info('Memulai impor baris ke tabel MySQL sosmed_keeps...');

        while (($row = fgetcsv($stream)) !== false) {
            if (count($row) === 1 && empty($row[0])) {
                continue;
            }

            $data = [];
            foreach ($cleanHeader as $idx => $key) {
                $data[$key] = $row[$idx] ?? null;
            }

            // Normalisasi nomor WA
            $rawWA = preg_replace('/\D/', '', (string) ($data['nomor wa'] ?? $data['no wa'] ?? $data['nomor'] ?? ''));
            if ($rawWA !== '' && str_starts_with($rawWA, '0')) {
                $rawWA = '62' . substr($rawWA, 1);
            }

            $tglInputRaw = $cleanUtf8($data['tanggal input ar'] ?? null);
            $parsedTglInput = $parseDate($tglInputRaw);

            $timestampRaw = $cleanUtf8($data['timestamp'] ?? null);
            $parsedTimestamp = $parseDate($timestampRaw);

            $statusRaw = $cleanUtf8($data['status'] ?? 'PENDING', 50);

            $nama = $cleanUtf8($data['nama'] ?? null, 150);
            $barangKeep = $cleanUtf8($data['barang keep'] ?? $data['barang'] ?? null);

            // Lewati baris kosong artifak rumus spreadsheet (mis. baris Desember-99 kosong)
            if (empty($nama) && empty($barangKeep) && empty($rawWA) && empty($parsedTimestamp)) {
                continue;
            }

            $batch[] = [
                'message_id' => $cleanUtf8($data['messageid'] ?? $data['message id'] ?? null, 100),
                'keep_timestamp' => $parsedTimestamp ?: $now,
                'nomor_sender' => $cleanUtf8($data['nomor sender'] ?? null, 35),
                'grup_id' => $cleanUtf8($data['grup id'] ?? null, 120),
                'sender' => $cleanUtf8($data['sender'] ?? null, 120),
                'grup' => $cleanUtf8($data['grup'] ?? null, 150),
                'jenis_chat' => $cleanUtf8($data['!grup'] ?? $data['jenis chat'] ?? 'GRUP', 20) ?: 'GRUP',
                'nama' => $nama,
                'nomor_wa' => $rawWA,
                'barang_keep' => $barangKeep,
                'tanggal_ambil' => $cleanUtf8($data['tanggal ambil'] ?? $data['tanggal'] ?? null, 100),
                'catatan' => $cleanUtf8($data['catatan'] ?? null),
                'remarks' => $cleanUtf8($data['remarks'] ?? $data['remaks'] ?? null, 120),
                'kode_sosmed' => $cleanUtf8($data['kode sosmed'] ?? null, 60),
                'report_ar' => $cleanUtf8($data['report ar'] ?? null, 50),
                'tanggal_input_ar' => $parsedTglInput,
                'ar_vlookup' => $cleanUtf8($data['ar dari vlookup'] ?? null, 50),
                'tanggal_vlookup' => $cleanUtf8($data['tanggal vlookup'] ?? null, 50),
                'cabang' => $cleanUtf8($data['cabang'] ?? null, 50),
                'evidence' => $cleanUtf8($data['evidence'] ?? null),
                'nomor_follow_up' => $cleanUtf8($data['nomor follow up'] ?? null, 35),
                'nama_tim_follow_up' => $cleanUtf8($data['nama tim follow up'] ?? null, 120),
                'status' => $statusRaw ?: 'PENDING',
                'feedback_tim_fu' => $cleanUtf8($data['feedback tim fu'] ?? null),
                'nama_toko_kompetitor' => $cleanUtf8($data['nama toko kompetitor (jika user deal di toko lain)'] ?? $data['nama toko kompetitor'] ?? null, 150),
                'user_kredit' => $cleanUtf8($data['user kredit'] ?? null, 50),
                'periode' => $cleanUtf8($data['periode'] ?? null, 35),
                'item' => $cleanUtf8($data['item'] ?? null, 150),
                'nomor_fu' => $cleanUtf8($data['nomor fu'] ?? null, 50),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('sosmed_keeps')->insert($batch);
                $totalImported += count($batch);
                $this->output->write("<comment>.</comment>");
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            DB::table('sosmed_keeps')->insert($batch);
            $totalImported += count($batch);
        }

        fclose($stream);

        $this->newLine();
        $this->info("✅ Sukses! Total {$totalImported} data keep berhasil disinkronkan ke tabel MySQL sosmed_keeps.");

        return 0;
    }
}
