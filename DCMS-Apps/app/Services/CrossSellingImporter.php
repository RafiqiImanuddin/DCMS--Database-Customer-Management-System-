<?php

namespace App\Services;

use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

class CrossSellingImporter
{
    public function import(CrossSellingImport $import): void
    {
        $rowCount = 0;
        $successCount = 0;
        $errors = [];
        $minimumDate = null;
        $maximumDate = null;

        try {
            $path = Storage::disk('local')->path($import->stored_path);
            $spreadsheet = IOFactory::load($path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, false);
            $headers = array_map(fn ($header) => trim((string) $header), $rows[0] ?? []);

            if (! in_array('Posting Date', $headers, true)) {
                throw new \RuntimeException('Header "Posting Date" tidak ditemukan pada file.');
            }

            for ($index = 1; $index < count($rows); $index++) {
                $row = $rows[$index];
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $rowCount++;
                $attributes = ['cross_selling_import_id' => $import->id];

                foreach ($headers as $columnIndex => $header) {
                    if (! isset(CrossSelling::HEADER_MAP[$header])) {
                        continue;
                    }

                    $attribute = CrossSelling::HEADER_MAP[$header];
                    $value = $row[$columnIndex] ?? null;
                    $numericFields = [
                        'quantity', 'harga_jual', 'harga_sebelum_potongan',
                        'selisih_total_potongan', 'p_bonus', 'p_voucher',
                        'p_nego', 'p_leasing', 'p_program', 'p_edc',
                        'p_otorisasi_posvit', 'harga_modal', 'gross_profit',
                        'beban_subsidi_leasing',
                    ];

                    if (in_array($attribute, $numericFields, true)) {
                        $value = $this->decimal($value);
                    } elseif ($attribute === 'posting_date') {
                        $value = $this->date($value);
                    } else {
                        $value = $value === null ? null : trim((string) $value);
                        $value = $value === '' ? null : $value;
                    }

                    $attributes[$attribute] = $value;
                }

                if (! empty($attributes['posting_date'])) {
                    $carbonDate = Carbon::parse($attributes['posting_date']);
                    $attributes['period_start'] = $carbonDate->copy()->startOfMonth()->toDateString();
                    $attributes['period_end'] = $carbonDate->copy()->endOfMonth()->toDateString();
                    $attributes['period_label'] = $carbonDate->format('Y-m');
                    $minimumDate = $minimumDate === null || $carbonDate->lt($minimumDate) ? $carbonDate->copy() : $minimumDate;
                    $maximumDate = $maximumDate === null || $carbonDate->gt($maximumDate) ? $carbonDate->copy() : $maximumDate;
                }

                $attributes['source_file'] = $import->original_name;
                $attributes['source_row'] = $index + 1;

                try {
                    CrossSelling::create($attributes);
                    $successCount++;
                } catch (Throwable $exception) {
                    if (count($errors) < 20) {
                        $errors[] = 'Baris '.$attributes['source_row'].': '.$exception->getMessage();
                    }
                }
            }

            $spreadsheet->disconnectWorksheets();
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

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
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

        try {
            return Carbon::parse(trim((string) $value))->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
