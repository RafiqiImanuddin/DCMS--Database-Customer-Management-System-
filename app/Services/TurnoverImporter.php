<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Turnover;
use App\Models\TurnoverImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

class TurnoverChunkFilter implements IReadFilter
{
    public function __construct(
        private readonly int $startRow,
        private readonly int $endRow,
        private readonly array $allowedColumnIndexes = [],
    ) {}

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        if ($row < $this->startRow || $row > $this->endRow) {
            return false;
        }

        if ($this->allowedColumnIndexes === []) {
            return true;
        }

        return in_array(Coordinate::columnIndexFromString($columnAddress) - 1, $this->allowedColumnIndexes, true);
    }
}

class TurnoverImporter
{
    /**
     * Map of standard field names to possible Excel header variations.
     */
    private const HEADER_SYNONYMS = [
        'posting_date' => ['posting date', 'tanggal posting', 'date', 'tgl posting', 'docdate'],
        'document_number' => ['document number', 'docnum', 'no faktur', 'no dokumen', 'document no', 'faktur'],
        'slp_name' => ['sales employee name', 'slpname', 'nama sales', 'sales name', 'sales'],
        'remarks' => ['remarks', 'keterangan', 'catatan', 'memo'],
        'sales_cabang' => ['salescabang', 'sales cabang', 'cabang', 'branch'],
        'dpp' => ['dpp', 'nilai dpp', 'amount dpp', 'omset dpp'],
        'gross_profit' => ['fix', 'grossprofit', 'gross profit', 'gp', 'laba kotor'],
        'total' => ['total', 'grand total', 'total amount', 'nilai transaksi'],
    ];

    public function import(TurnoverImport $import): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $rowCount = 0;
        $successCount = 0;
        $errors = [];
        $minimumDate = null;
        $maximumDate = null;

        try {
            DB::transaction(function () use ($import, &$rowCount, &$successCount, &$errors, &$minimumDate, &$maximumDate): void {
                $path = Storage::disk('local')->path($import->stored_path);
                Turnover::where('turnover_import_id', $import->id)->delete();

                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if ($extension === 'csv') {
                    $this->importCsv($path, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
                } else {
                    $this->importSpreadsheet($path, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
                }

                $import->update([
                    'status' => 'completed',
                    'row_count' => $rowCount,
                    'success_count' => $successCount,
                    'failed_count' => $rowCount - $successCount,
                    'period_start' => $minimumDate?->toDateString(),
                    'period_end' => $maximumDate?->toDateString(),
                    'batch_label' => $minimumDate && $maximumDate
                        ? $minimumDate->format('Y-m').($minimumDate->format('Y-m') === $maximumDate->format('Y-m') ? '' : ' s/d '.$maximumDate->format('Y-m'))
                        : null,
                    'error_log' => $errors ? implode("\n", array_slice($errors, 0, 50)) : null,
                ]);

                // Sync binary to Supabase settings table & local workspace folder
                $fileBinary = @file_get_contents($path);
                if ($fileBinary) {
                    $nowMs = (string) round(microtime(true) * 1000);
                    $uploader = $import->user?->name ?? 'Superadmin';
                    Setting::updateOrCreate(['key' => 'workspace_turnover_mtime'], ['value' => $nowMs]);
                    Setting::updateOrCreate(['key' => 'workspace_turnover_uploader'], ['value' => $uploader]);
                    Setting::updateOrCreate(['key' => 'workspace_turnover_filename'], ['value' => $import->original_name]);
                    Setting::updateOrCreate(['key' => 'workspace_turnover_filesize'], ['value' => (string) strlen($fileBinary)]);
                    Setting::updateOrCreate(['key' => 'workspace_turnover_file_base64'], ['value' => base64_encode($fileBinary)]);

                    $workspaceTarget = public_path('workspace-sosmed/turnover.xlsx');
                    @File::put($workspaceTarget, $fileBinary);
                }
            });
        } catch (Throwable $exception) {
            $import->update([
                'status' => 'failed',
                'row_count' => $rowCount,
                'success_count' => $successCount,
                'failed_count' => $rowCount - $successCount,
                'error_log' => $exception->getMessage(),
            ]);
        }
    }

    private function importSpreadsheet(
        string $path,
        TurnoverImport $import,
        int &$rowCount,
        int &$successCount,
        array &$errors,
        ?Carbon &$minimumDate,
        ?Carbon &$maximumDate,
    ): void {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        // Select TARIKKAN sheet if exists, otherwise first sheet
        $sheetNames = $reader->listWorksheetNames($path);
        $targetSheet = in_array('TARIKKAN', $sheetNames, true) ? 'TARIKKAN' : ($sheetNames[0] ?? null);
        if ($targetSheet) {
            $reader->setLoadSheetsOnly([$targetSheet]);
        }

        // Single-pass load: load file once instead of reloading in a loop
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        $headers = array_map(
            fn ($header) => trim((string) $header),
            $sheet->rangeToArray('A1:ZZ1', null, true, false)[0] ?? [],
        );

        $headerMap = $this->mapHeaders($headers);

        for ($startRow = 2; $startRow <= $highestRow; $startRow += 1000) {
            $endRow = min($startRow + 999, $highestRow);
            $rows = $sheet->rangeToArray("A{$startRow}:ZZ{$endRow}", null, true, false);
            $this->processRows($rows, $headerMap, $startRow, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    private function importCsv(
        string $path,
        TurnoverImport $import,
        int &$rowCount,
        int &$successCount,
        array &$errors,
        ?Carbon &$minimumDate,
        ?Carbon &$maximumDate,
    ): void {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return;
        }

        $firstLine = fgets($handle);
        $delimiter = str_contains((string) $firstLine, ';') ? ';' : ',';
        rewind($handle);

        $headers = fgetcsv($handle, 0, $delimiter) ?: [];
        $headers = array_map(fn ($header) => trim((string) $header), $headers);
        $headerMap = $this->mapHeaders($headers);

        $rows = [];
        $sourceRow = 2;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[$sourceRow] = $data;
            if (count($rows) >= 500) {
                $this->processRows($rows, $headerMap, $sourceRow - 499, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
                $rows = [];
            }
            $sourceRow++;
        }

        if ($rows !== []) {
            $this->processRows($rows, $headerMap, $sourceRow - count($rows), $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
        }

        fclose($handle);
    }

    private function processRows(
        array $rows,
        array $headerMap,
        int $startRow,
        TurnoverImport $import,
        int &$rowCount,
        int &$successCount,
        array &$errors,
        ?Carbon &$minimumDate,
        ?Carbon &$maximumDate,
    ): void {
        $batch = [];

        foreach ($rows as $relativeIndex => $row) {
            $sourceRow = is_int($relativeIndex) && $relativeIndex >= $startRow
                ? $relativeIndex
                : $startRow + (int) $relativeIndex;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $rowCount++;
            $attributes = [
                'turnover_import_id' => $import->id,
            ];

            foreach ($headerMap as $columnIndex => $fieldName) {
                $val = trim((string) ($row[$columnIndex] ?? ''));
                if ($fieldName === 'posting_date') {
                    $rawDate = $row[$columnIndex] ?? null;
                    $parsed = $this->parsePostingDate($rawDate);
                    $attributes['posting_date'] = $parsed?->toDateString();
                } elseif (in_array($fieldName, ['dpp', 'gross_profit', 'total'], true)) {
                    $numVal = preg_replace('/[^\d\.\-]/', '', str_replace(',', '.', $val));
                    $attributes[$fieldName] = is_numeric($numVal) ? (float) $numVal : 0.0;
                } else {
                    $attributes[$fieldName] = $val !== '' ? $val : null;
                }
            }

            if (empty($attributes['posting_date']) || empty($attributes['document_number'])) {
                if (count($errors) < 20) {
                    $errors[] = 'Baris '.$sourceRow.': Tanggal posting atau nomor dokumen kosong.';
                }
                continue;
            }

            $carbonDate = Carbon::parse($attributes['posting_date']);
            $attributes['period_label'] = $carbonDate->format('Y-m');
            $minimumDate = $minimumDate === null || $carbonDate->lt($minimumDate) ? $carbonDate->copy() : $minimumDate;
            $maximumDate = $maximumDate === null || $carbonDate->gt($maximumDate) ? $carbonDate->copy() : $maximumDate;

            // Smart Upsert Hash
            $hashParts = [
                $attributes['posting_date'],
                $attributes['document_number'],
                $attributes['slp_name'] ?? '',
                (string) ($attributes['dpp'] ?? 0),
                (string) ($attributes['gross_profit'] ?? 0),
            ];
            $attributes['cleaning_hash'] = hash('sha256', implode('|', $hashParts));
            $attributes['created_at'] = now();
            $attributes['updated_at'] = now();

            $batch[] = $attributes;

            if (count($batch) >= 500) {
                $successCount += $this->insertBatch($batch, $errors);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $successCount += $this->insertBatch($batch, $errors);
        }
    }

    private function insertBatch(array $batch, array &$errors): int
    {
        try {
            return (int) DB::table('turnovers')->insertOrIgnore($batch);
        } catch (Throwable $e) {
            $inserted = 0;
            foreach ($batch as $row) {
                try {
                    DB::table('turnovers')->insert($row);
                    $inserted++;
                } catch (Throwable $rowErr) {
                    if (count($errors) < 20) {
                        $errors[] = 'Gagal baris doc '.$row['document_number'].': '.$rowErr->getMessage();
                    }
                }
            }
            return $inserted;
        }
    }

    private function mapHeaders(array $headers): array
    {
        $map = [];
        $normalizedHeaders = array_map(fn ($h) => strtolower(trim((string) $h)), $headers);

        foreach (self::HEADER_SYNONYMS as $field => $synonyms) {
            foreach ($normalizedHeaders as $idx => $headerText) {
                if (in_array($headerText, $synonyms, true) && ! isset($map[$idx])) {
                    $map[$idx] = $field;
                    break;
                }
            }
        }

        return $map;
    }

    private function parsePostingDate(mixed $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                $dt = Date::excelToDateTimeObject((float) $value);
                return Carbon::instance($dt);
            } catch (Throwable) {}
        }

        $str = trim((string) $value);

        // Try standard YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $str)) {
            try {
                return Carbon::parse(substr($str, 0, 10));
            } catch (Throwable) {}
        }

        // Try DD/MM/YYYY or MM/DD/YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $str, $matches)) {
            $p1 = (int) $matches[1];
            $p2 = (int) $matches[2];
            $year = (int) $matches[3];

            // In SAP turnover.xlsx inverted US locale: month is often p1 when exported US style
            // If p1 > 12, then p1 must be day, p2 is month
            if ($p1 > 12) {
                return Carbon::createFromDate($year, $p2, $p1);
            }
            // If p2 > 12, then p2 must be day, p1 is month
            if ($p2 > 12) {
                return Carbon::createFromDate($year, $p1, $p2);
            }

            // Default assume DD/MM/YYYY for Indonesian context
            return Carbon::createFromDate($year, $p2, $p1);
        }

        try {
            return Carbon::parse($str);
        } catch (Throwable) {
            return null;
        }
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }
}

