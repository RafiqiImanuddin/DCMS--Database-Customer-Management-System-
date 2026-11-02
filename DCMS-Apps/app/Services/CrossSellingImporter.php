<?php

namespace App\Services;

use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

class CrossSellingChunkFilter implements IReadFilter
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

class CrossSellingImporter
{
    public function import(CrossSellingImport $import): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::unprepared('PRAGMA busy_timeout = 60000');
            if (! DB::connection()->transactionLevel()) {
                try {
                    DB::unprepared('PRAGMA journal_mode = WAL');
                    DB::unprepared('PRAGMA synchronous = NORMAL');
                    DB::unprepared('PRAGMA temp_store = MEMORY');
                    DB::unprepared('PRAGMA cache_size = -65536');
                } catch (Throwable) {
                    // SQLite may reject these settings while another transaction owns the connection.
                }
            }
        }

        $rowCount = 0;
        $successCount = 0;
        $errors = [];
        $minimumDate = null;
        $maximumDate = null;

        try {
            DB::transaction(function () use ($import, &$rowCount, &$successCount, &$errors, &$minimumDate, &$maximumDate): void {
                $path = Storage::disk('local')->path($import->stored_path);
                CrossSelling::where('cross_selling_import_id', $import->id)->delete();

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
                    'error_log' => $errors ? implode("\n", $errors) : null,
                ]);
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
        CrossSellingImport $import,
        int &$rowCount,
        int &$successCount,
        array &$errors,
        ?Carbon &$minimumDate,
        ?Carbon &$maximumDate,
    ): void {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'xlsx' && class_exists(\ZipArchive::class)) {
            $this->importXlsx($path, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);

            return;
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        $headerFilter = new CrossSellingChunkFilter(1, 1);
        $reader->setReadFilter($headerFilter);
        $headerSpreadsheet = $reader->load($path);
        $headers = array_map(
            fn ($header) => trim((string) $header),
            $headerSpreadsheet->getActiveSheet()->rangeToArray('A1:ZZ1', null, true, false)[0] ?? [],
        );
        $headerSpreadsheet->disconnectWorksheets();
        unset($headerSpreadsheet);

        $this->validateHeaders($headers);
        $headerMap = $this->mapHeaders($headers);
        $allowedColumnIndexes = array_keys($headerMap);
        $emptyChunks = 0;

        for ($startRow = 2; $startRow <= 1000000 && $emptyChunks < 10; $startRow += 500) {
            $endRow = $startRow + 499;
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $reader->setReadFilter(new CrossSellingChunkFilter($startRow, $endRow, $allowedColumnIndexes));
            $spreadsheet = $reader->load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            $nonEmptyRows = array_filter($rows, fn (array $row) => ! $this->isEmptyRow($row));
            $emptyChunks = $nonEmptyRows === [] ? $emptyChunks + 1 : 0;
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            $this->processRows($rows, $headerMap, $startRow, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
            gc_collect_cycles();
        }
    }

    private function importCsv(
        string $path,
        CrossSellingImport $import,
        int &$rowCount,
        int &$successCount,
        array &$errors,
        ?Carbon &$minimumDate,
        ?Carbon &$maximumDate,
    ): void {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('File CSV tidak dapat dibaca.');
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            throw new \RuntimeException('File CSV kosong.');
        }

        $delimiter = str_contains($firstLine, ';') && ! str_contains($firstLine, ',') ? ';' : ',';
        rewind($handle);
        $headers = fgetcsv($handle, 0, $delimiter, '"', '\\');
        if (! is_array($headers)) {
            fclose($handle);
            throw new \RuntimeException('Header CSV tidak dapat dibaca.');
        }
        $this->validateHeaders($headers);
        $headerMap = $this->mapHeaders($headers);
        $rows = [];
        $sourceRow = 2;

        while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            $rows[] = $row;
            if (count($rows) >= 1000) {
                $this->processRows($rows, $headerMap, $sourceRow, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
                $sourceRow += count($rows);
                $rows = [];
            }
        }
        fclose($handle);

        if ($rows !== []) {
            $this->processRows($rows, $headerMap, $sourceRow, $import, $rowCount, $successCount, $errors, $minimumDate, $maximumDate);
        }
        gc_collect_cycles();
    }

    private function importXlsx(
        string $path,
        CrossSellingImport $import,
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

        $worksheetReader = new \XMLReader;
        if (! $worksheetReader->open('zip://'.$path.'#xl/worksheets/sheet1.xml')) {
            throw new \RuntimeException('Worksheet XLSX tidak dapat dibuka.');
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
                $headers = array_map(fn ($header) => trim((string) $header), $row);
                $this->validateHeaders($headers);
                $headerMap = $this->mapHeaders($headers);
                $sourceRow = $rowNumber + 1;

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

    private function readHighestRow(string $path): int
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($path);
        $highestRow = $spreadsheet->getActiveSheet()->getHighestDataRow();
        $spreadsheet->disconnectWorksheets();

        return $highestRow;
    }

    private function validateHeaders(array $headers): void
    {
        if (! $this->hasHeader($headers, 'Posting Date')) {
            throw new \RuntimeException('Header "Posting Date" tidak ditemukan pada file.');
        }
    }

    private function hasHeader(array $headers, string $header): bool
    {
        foreach ($headers as $candidate) {
            if ($this->cleanHeader($candidate) === $this->cleanHeader($header)) {
                return true;
            }
        }

        return false;
    }

    private function mapHeaders(array $headers): array
    {
        $map = [];
        foreach ($headers as $columnIndex => $header) {
            $cleanHeader = $this->cleanHeader($header);
            foreach (CrossSelling::HEADER_MAP as $source => $attribute) {
                if ($cleanHeader === $this->cleanHeader($source)) {
                    if (in_array($attribute, CrossSelling::ESSENTIAL_ATTRIBUTES, true)) {
                        $map[$columnIndex] = $attribute;
                    }
                    break;
                }
            }
        }

        return $map;
    }

    private function processRows(
        array $rows,
        array $headerMap,
        int $startRow,
        CrossSellingImport $import,
        int &$rowCount,
        int &$successCount,
        array &$errors,
        ?Carbon &$minimumDate,
        ?Carbon &$maximumDate,
    ): void {
        $batch = [];
        $seenInBatch = [];

        foreach ($rows as $offset => $row) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $sourceRow = $startRow === 2 ? $offset + $startRow : $startRow + $offset;
            $rowCount++;
            $attributes = ['cross_selling_import_id' => $import->id];

            foreach ($headerMap as $columnIndex => $attribute) {
                $value = $row[$columnIndex] ?? null;

                if (in_array($attribute, $this->numericFields(), true)) {
                    $value = $this->decimal($value);
                } elseif ($attribute === 'posting_date') {
                    $value = $this->date($value);
                } elseif (in_array($attribute, ['customer_vendor_code', 'item_code', 'document_number', 'no_reff', 'no_po'], true)) {
                    $value = $this->code($value);
                } elseif ($attribute === 'no_hp_customer') {
                    $value = $this->phone($value);
                } else {
                    $value = $this->text($value);
                }

                $attributes[$attribute] = $value;
            }

            if (empty($attributes['posting_date']) || empty($attributes['customer_vendor_code'])) {
                if (count($errors) < 20) {
                    $errors[] = 'Baris '.$sourceRow.': kode pelanggan atau tanggal posting kosong.';
                }

                continue;
            }

            $carbonDate = Carbon::parse($attributes['posting_date']);
            $attributes['period_start'] = $carbonDate->copy()->startOfMonth()->toDateString();
            $attributes['period_end'] = $carbonDate->copy()->endOfMonth()->toDateString();
            $attributes['period_label'] = $carbonDate->format('Y-m');
            $minimumDate = $minimumDate === null || $carbonDate->lt($minimumDate) ? $carbonDate->copy() : $minimumDate;
            $maximumDate = $maximumDate === null || $carbonDate->gt($maximumDate) ? $carbonDate->copy() : $maximumDate;
            $attributes['source_file'] = $import->original_name;
            $attributes['source_row'] = $sourceRow;

            $hashData = [
                $attributes['posting_date'],
                $attributes['document_number'] ?? '',
                $attributes['customer_vendor_code'],
                $attributes['item_code'],
                $attributes['quantity'] ?? 0,
                $attributes['harga_jual'] ?? 0,
            ];
            $hash = hash('sha256', implode('|', $hashData));
            if (isset($seenInBatch[$hash])) {
                continue;
            }

            $seenInBatch[$hash] = true;
            $attributes['cleaning_hash'] = $hash;
            $attributes['created_at'] = now();
            $attributes['updated_at'] = now();
            $batch[] = $attributes;

            if (count($batch) >= 1000) {
                $successCount += $this->insertCleanBatch($batch, $errors);
                $batch = [];
            }
        }

        if ($batch) {
            $successCount += $this->insertCleanBatch($batch, $errors);
        }
    }

    private function insertCleanBatch(array $batch, array &$errors): int
    {
        try {
            return (int) DB::table('cross_sellings')->insertOrIgnore($batch);
        } catch (Throwable $exception) {
            $inserted = 0;
            foreach ($batch as $attributes) {
                try {
                    DB::table('cross_sellings')->insert($attributes);
                    $inserted++;
                } catch (Throwable $rowException) {
                    if (count($errors) < 20) {
                        $errors[] = 'Baris '.$attributes['source_row'].': '.$rowException->getMessage();
                    }
                }
            }

            return $inserted;
        }
    }

    private function numericFields(): array
    {
        return [
            'quantity', 'harga_jual', 'harga_sebelum_potongan',
            'selisih_total_potongan', 'p_bonus', 'p_voucher',
            'p_nego', 'p_leasing', 'p_program', 'p_edc',
            'p_otorisasi_posvit', 'harga_modal', 'gross_profit',
            'beban_subsidi_leasing',
        ];
    }

    private function cleanHeader(string $header): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', strtoupper($header)) ?? '');
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function code(mixed $value): ?string
    {
        $value = $this->text($value);
        if ($value === null) {
            return null;
        }

        return strtoupper(preg_replace('/\s+/u', '', $value) ?? $value);
    }

    private function text(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/\s+/u', ' ', trim((string) $value));

        return $value === null || trim($value) === '' ? null : trim($value);
    }

    private function phone(mixed $value): ?string
    {
        $phone = $this->text($value);
        if ($phone === null) {
            return null;
        }

        $phone = preg_replace('/[^0-9+]/', '', $phone) ?? '';
        if (str_starts_with($phone, '08')) {
            $phone = '628'.substr($phone, 2);
        } elseif (str_starts_with($phone, '+62')) {
            $phone = substr($phone, 1);
        } elseif (str_starts_with($phone, '8') && strlen($phone) >= 10) {
            $phone = '62'.$phone;
        }

        return $phone !== '' ? $phone : null;
    }

    private function decimal(mixed $value): float
    {
        if ($value === null || trim((string) $value) === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = trim((string) $value);
        $normalized = str_replace(['Rp', ' ', "\u{00a0}"], '', $normalized);
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return is_numeric($normalized) ? (float) $normalized : 0;
    }

    private function date(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (Throwable) {
                // Continue with text parsing.
            }
        }

        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $text = trim((string) $value);
        foreach (['d/m/Y', 'd-m-Y', 'Y/m/d', 'Y-m-d'] as $format) {
            try {
                $date = \DateTimeImmutable::createFromFormat('!'.$format, $text);
                if ($date !== false && $date->format($format) === $text) {
                    return $date->format('Y-m-d');
                }
            } catch (Throwable) {
                // Continue with the next supported format.
            }
        }

        try {
            return Carbon::parse($text)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
