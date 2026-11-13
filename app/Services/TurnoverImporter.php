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
        'posting_date' => ['posting date', 'tanggal posting', 'date', 'tgl posting', 'docdate', 'posting_date', 'tanggal', 'tgl'],
        'document_number' => ['document number', 'docnum', 'no faktur', 'no dokumen', 'document no', 'faktur', 'document_number', 'no_faktur', 'no_dokumen', 'doc_num', 'nomor dokumen'],
        'slp_name' => ['sales employee name', 'slpname', 'nama sales', 'sales name', 'sales', 'slp_name', 'nama_sales'],
        'remarks' => ['remarks', 'keterangan', 'catatan', 'memo'],
        'sales_cabang' => ['salescabang', 'sales cabang', 'cabang', 'branch', 'sales_cabang'],
        'dpp' => ['dpp', 'nilai dpp', 'amount dpp', 'omset dpp', 'nilai_dpp', 'dpp omset'],
        'gross_profit' => ['fix', 'grossprofit', 'gross profit', 'gp', 'laba kotor', 'gross_profit', 'laba_kotor'],
        'total' => ['total', 'grand total', 'total amount', 'nilai transaksi', 'grand_total', 'total_amount'],
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
        $path = Storage::disk('local')->path($import->stored_path);

        try {
            DB::transaction(function () use ($path, $import, &$rowCount, &$successCount, &$errors, &$minimumDate, &$maximumDate): void {
                Turnover::where('turnover_import_id', $import->id)->delete();

                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if ($extension === 'csv') {
                    $this->importCsv($path, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
                } elseif ($extension === 'xlsx' && class_exists(\ZipArchive::class)) {
                    $this->importXlsx($path, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
                } else {
                    $this->importSpreadsheet($path, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
                }

                $import->update([
                    'status' => 'completed',
                    'row_count' => $rowCount,
                    'success_count' => $successCount,
                    'failed_count' => max(0, $rowCount - $successCount),
                    'period_start' => $minimumDate?->toDateString(),
                    'period_end' => $maximumDate?->toDateString(),
                    'batch_label' => $minimumDate && $maximumDate
                        ? $minimumDate->format('Y-m').($minimumDate->format('Y-m') === $maximumDate->format('Y-m') ? '' : ' s/d '.$maximumDate->format('Y-m'))
                        : null,
                    'error_log' => $errors ? implode("\n", array_slice($errors, 0, 50)) : null,
                ]);
            });

            // Sync binary to Supabase settings table & local workspace folder (di luar transaksi agar tidak membebani DB)
            try {
                $fileBinary = @file_get_contents($path);
                if ($fileBinary) {
                    $nowMs = (string) round(microtime(true) * 1000);
                    $uploader = $import->user?->name ?? 'Superadmin';
                    Setting::updateOrCreate(['key' => 'workspace_turnover_mtime'], ['value' => $nowMs]);
                    Setting::updateOrCreate(['key' => 'workspace_turnover_uploader'], ['value' => $uploader]);
                    Setting::updateOrCreate(['key' => 'workspace_turnover_filename'], ['value' => $import->original_name]);
                    Setting::updateOrCreate(['key' => 'workspace_turnover_filesize'], ['value' => (string) strlen($fileBinary)]);

                    if (strlen($fileBinary) < 6 * 1024 * 1024) {
                        try {
                            Setting::updateOrCreate(['key' => 'workspace_turnover_file_base64'], ['value' => base64_encode($fileBinary)]);
                        } catch (\Throwable $b64Err) {
                            \Illuminate\Support\Facades\Log::warning('Setting base64 turnover warning: '.$b64Err->getMessage());
                        }
                    }

                    $workspaceTarget = public_path('workspace-sosmed/turnover.xlsx');
                    \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($workspaceTarget));
                    @\Illuminate\Support\Facades\File::put($workspaceTarget, $fileBinary);
                }
            } catch (\Throwable $syncErr) {
                \Illuminate\Support\Facades\Log::warning('Turnover workspace sync error: '.$syncErr->getMessage());
            }
        } catch (Throwable $exception) {
            $import->update([
                'status' => 'failed',
                'row_count' => $rowCount,
                'success_count' => $successCount,
                'failed_count' => max(0, $rowCount - $successCount),
                'error_log' => $exception->getMessage(),
            ]);
        }
    }

    private function importXlsx(
        string $path,
        TurnoverImport $import,
        int &$rowCount,
        int &$successCount,
        array &$errors,
        ?Carbon &$minimumDate,
        ?Carbon &$maximumDate,
    ): void {
        $sharedStrings = [];
        $sharedStringsReader = new \XMLReader;
        if ($sharedStringsReader->open('zip://'.$path.'#xl/sharedStrings.xml')) {
            $index = 0;
            while ($sharedStringsReader->read()) {
                if ($sharedStringsReader->nodeType !== \XMLReader::ELEMENT || $sharedStringsReader->localName !== 'si') {
                    continue;
                }

                $node = $sharedStringsReader->expand();
                $text = '';
                if ($node instanceof \DOMElement) {
                    foreach ($node->getElementsByTagName('t') as $textNode) {
                        $text .= $textNode->textContent;
                    }
                }
                $sharedStrings[$index++] = $text;
            }
            $sharedStringsReader->close();
        }

        // Cari worksheet TARIKKAN atau TURNOVER atau default sheet1
        $targetSheetXml = 'xl/worksheets/sheet1.xml';
        $workbookReader = new \XMLReader;
        if ($workbookReader->open('zip://'.$path.'#xl/workbook.xml')) {
            $sheetIndex = 1;
            while ($workbookReader->read()) {
                if ($workbookReader->nodeType === \XMLReader::ELEMENT && $workbookReader->localName === 'sheet') {
                    $sheetName = strtoupper(trim((string) $workbookReader->getAttribute('name')));
                    if ($sheetName === 'TARIKKAN' || $sheetName === 'TURNOVER' || str_contains($sheetName, 'TARIKKAN')) {
                        $targetSheetXml = 'xl/worksheets/sheet'.$sheetIndex.'.xml';
                        break;
                    }
                    $sheetIndex++;
                }
            }
            $workbookReader->close();
        }

        $worksheetReader = new \XMLReader;
        if (! $worksheetReader->open('zip://'.$path.'#'.$targetSheetXml)) {
            if (! $worksheetReader->open('zip://'.$path.'#xl/worksheets/sheet1.xml')) {
                throw new \RuntimeException('Worksheet XLSX turnover tidak dapat dibuka.');
            }
        }

        $headers = null;
        $headerMap = [];
        $batchRows = [];
        $sourceRow = 2;

        while ($worksheetReader->read()) {
            if ($worksheetReader->nodeType !== \XMLReader::ELEMENT || $worksheetReader->localName !== 'row') {
                continue;
            }

            $rowNode = $worksheetReader->expand();
            if (! $rowNode instanceof \DOMElement) {
                continue;
            }

            $rowNumber = (int) ($rowNode->getAttribute('r') ?: $sourceRow);
            $row = $this->readXlsxRow($rowNode, $sharedStrings);

            if ($headers === null) {
                $candidateHeaders = array_map(fn ($header) => trim((string) $header), $row);
                $candidateMap = $this->mapHeaders($candidateHeaders);
                if (count($candidateMap) >= 2) {
                    $headers = $candidateHeaders;
                    $headerMap = $candidateMap;
                    $sourceRow = $rowNumber + 1;
                    continue;
                }
                continue;
            }

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $batchRows[] = $row;
            if (count($batchRows) >= 1000) {
                $this->processRows($batchRows, $headerMap, $sourceRow, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
                $sourceRow = $rowNumber + 1;
                $batchRows = [];
                gc_collect_cycles();

                $import->update([
                    'row_count' => $rowCount,
                    'success_count' => $successCount,
                    'failed_count' => max(0, $rowCount - $successCount),
                ]);
            }
        }

        $worksheetReader->close();

        if ($batchRows !== []) {
            $this->processRows($batchRows, $headerMap, $sourceRow, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
        }
    }

    private function readXlsxRow(\DOMElement $rowNode, array $sharedStrings): array
    {
        $row = [];
        foreach ($rowNode->getElementsByTagName('c') as $cell) {
            if (! $cell instanceof \DOMElement) {
                continue;
            }

            $reference = $cell->getAttribute('r');
            if (! preg_match('/([A-Z]+)/i', $reference, $matches)) {
                continue;
            }

            $columnIndex = Coordinate::columnIndexFromString(strtoupper($matches[1])) - 1;
            $type = $cell->getAttribute('t');
            $value = null;

            if ($type === 'inlineStr') {
                foreach ($cell->getElementsByTagName('t') as $textNode) {
                    $value .= $textNode->textContent;
                }
            } elseif ($type === 's') {
                $valueNode = $cell->getElementsByTagName('v')->item(0);
                $sharedIndex = $valueNode ? (int) $valueNode->textContent : null;
                $value = $sharedIndex !== null ? ($sharedStrings[$sharedIndex] ?? null) : null;
            } else {
                $valueNode = $cell->getElementsByTagName('v')->item(0);
                $value = $valueNode?->textContent;
            }

            $row[$columnIndex] = $value;
        }

        ksort($row);

        return $row;
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

        // Select TARIKKAN or TURNOVER sheet if exists (case-insensitive), otherwise first sheet
        $sheetNames = $reader->listWorksheetNames($path);
        $targetSheet = null;
        foreach ($sheetNames as $sName) {
            $upper = strtoupper(trim($sName));
            if ($upper === 'TARIKKAN' || $upper === 'TURNOVER' || str_contains($upper, 'TARIKKAN')) {
                $targetSheet = $sName;
                break;
            }
        }
        if (! $targetSheet) {
            $targetSheet = $sheetNames[0] ?? null;
        }

        if ($targetSheet) {
            $reader->setLoadSheetsOnly([$targetSheet]);
        }

        // Single-pass load: load file once instead of reloading in a loop
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        if (Coordinate::columnIndexFromString($highestColumn) > 50) {
            $highestColumn = 'Z';
        }

        // Scan rows 1 to 5 to accurately detect the header row (handles title/empty rows at top)
        $headerRow = 1;
        $headers = [];
        $headerMap = [];
        $maxScan = min(5, $highestRow);

        for ($r = 1; $r <= $maxScan; $r++) {
            $candidateHeaders = array_map(
                fn ($header) => trim((string) $header),
                $sheet->rangeToArray("A{$r}:{$highestColumn}{$r}", null, true, false)[0] ?? [],
            );
            $candidateMap = $this->mapHeaders($candidateHeaders);
            if (count($candidateMap) >= 2) {
                $headerRow = $r;
                $headers = $candidateHeaders;
                $headerMap = $candidateMap;
                break;
            }
        }

        if (empty($headerMap)) {
            $headers = array_map(
                fn ($header) => trim((string) $header),
                $sheet->rangeToArray("A1:{$highestColumn}1", null, true, false)[0] ?? [],
            );
            $headerMap = $this->mapHeaders($headers);
        }

        for ($startRow = $headerRow + 1; $startRow <= $highestRow; $startRow += 1000) {
            $endRow = min($startRow + 999, $highestRow);
            $rows = $sheet->rangeToArray("A{$startRow}:{$highestColumn}{$endRow}", null, true, false);
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
        $headers = array_map(function ($header) {
            // Remove UTF-8 BOM if present
            $clean = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);
            return trim($clean);
        }, $headers);

        $headerMap = $this->mapHeaders($headers);

        // If row 1 did not have enough headers, check row 2
        $sourceRow = 2;
        if (count($headerMap) < 2) {
            $candidate = fgetcsv($handle, 0, $delimiter) ?: [];
            $candidateHeaders = array_map(fn ($h) => trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h)), $candidate);
            $candidateMap = $this->mapHeaders($candidateHeaders);
            if (count($candidateMap) >= 2) {
                $headers = $candidateHeaders;
                $headerMap = $candidateMap;
                $sourceRow = 3;
            } else {
                rewind($handle);
                fgetcsv($handle, 0, $delimiter); // skip row 1
            }
        }

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

