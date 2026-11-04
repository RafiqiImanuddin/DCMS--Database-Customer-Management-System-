<?php

namespace App\Services;

use App\Models\CrossSelling;
use App\Models\RepeatOrderPrediction;
use Carbon\Carbon;

class RepeatOrderPredictor
{
    public function rebuild(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        RepeatOrderPrediction::query()->delete();

        $currentKey = null;
        $group = null;
        $predictionBatch = [];

        CrossSelling::query()
            ->whereNotNull('customer_vendor_code')
            ->whereNotNull('posting_date')
            ->where('quantity', '>', 0)
            ->whereNotNull('no_hp_customer')
            ->whereRaw("TRIM(no_hp_customer) <> ''")
            ->orderBy('customer_vendor_code')
            ->orderBy('item_code')
            ->orderBy('posting_date')
            ->cursor()
            ->each(function (CrossSelling $row) use (&$currentKey, &$group, &$predictionBatch) {
                $key = $row->customer_vendor_code.'|'.($row->item_code ?? '');

                if ($currentKey !== null && $currentKey !== $key) {
                    if ($prediction = $this->buildPrediction($group)) {
                        $predictionBatch[] = $prediction;
                    }
                    if (count($predictionBatch) >= 500) {
                        $this->insertPredictionBatch($predictionBatch);
                        $predictionBatch = [];
                    }
                    $group = null;
                }

                $currentKey = $key;
                $date = $row->posting_date->toDateString();

                if ($group === null) {
                    $group = [
                        'cross_selling_import_id' => $row->cross_selling_import_id,
                        'customer_vendor_code' => $row->customer_vendor_code,
                        'customer_vendor_name' => $row->name ?: null,
                        'no_hp_customer' => $row->no_hp_customer,
                        'cabang' => $row->cabang,
                        'item_code' => $row->item_code,
                        'dscription' => $row->dscription,
                        'first_order_date' => $date,
                        'last_order_date' => $date,
                        'dates' => [$date => true],
                        'total_quantity' => 0,
                        'total_value' => 0,
                        'period_label' => $row->period_label,
                    ];
                } else {
                    $group['customer_vendor_name'] = $row->name ?: $group['customer_vendor_name'];
                    $group['no_hp_customer'] = $row->no_hp_customer ?: $group['no_hp_customer'];
                    $group['cabang'] = $row->cabang ?: $group['cabang'];
                    $group['dscription'] = $row->dscription ?: $group['dscription'];
                    $group['cross_selling_import_id'] = $row->cross_selling_import_id ?: $group['cross_selling_import_id'];
                    $group['period_label'] = $row->period_label ?: $group['period_label'];
                    $group['last_order_date'] = $date;
                    $group['dates'][$date] = true;
                }

                $group['total_quantity'] += (float) $row->quantity;
                $group['total_value'] += (float) $row->harga_jual;
            });

        if ($group !== null) {
            if ($prediction = $this->buildPrediction($group)) {
                $predictionBatch[] = $prediction;
            }
        }
        if ($predictionBatch) {
            $this->insertPredictionBatch($predictionBatch);
        }
    }

    private function buildPrediction(array $group): ?array
    {
        if (empty($group['no_hp_customer']) || trim($group['no_hp_customer']) === '') {
            return null;
        }
        $dates = array_keys($group['dates']);
        sort($dates);
        $carbonDates = array_map(fn (string $date) => Carbon::parse($date), $dates);
        $gaps = [];

        for ($index = 1; $index < count($carbonDates); $index++) {
            $gaps[] = $carbonDates[$index - 1]->diffInDays($carbonDates[$index]);
        }

        $medianGap = $this->median($gaps);
        $mad = $medianGap === null ? null : $this->median(array_map(
            fn (float $gap) => abs($gap - $medianGap),
            $gaps,
        ));
        $predictedDate = $medianGap === null
            ? null
            : Carbon::parse($group['last_order_date'])->addDays((int) round($medianGap));

        $uniqueCount = count($dates);
        $confidence = match (true) {
            $uniqueCount >= 5 => 90,
            $uniqueCount >= 4 => 85,
            $uniqueCount === 3 => 75,
            $uniqueCount === 2 => 55,
            default => 20,
        };
        if ($medianGap !== null && $mad !== null && $medianGap > 0) {
            $coefficient = $mad / $medianGap;
            $confidence -= (int) round(min(30, $coefficient * 30));
        }
        $confidence = max(10, min(95, $confidence));

        $classification = 'baru';
        if ($predictedDate !== null) {
            $daysUntil = Carbon::today()->diffInDays($predictedDate, false);
            $classification = $daysUntil <= 30
                ? 'tinggi'
                : ($daysUntil <= 60 ? 'sedang' : 'rendah');
        }

        return [
            'cross_selling_import_id' => $group['cross_selling_import_id'],
            'customer_vendor_code' => $group['customer_vendor_code'],
            'customer_vendor_name' => $group['customer_vendor_name'],
            'no_hp_customer' => $group['no_hp_customer'],
            'cabang' => $group['cabang'],
            'item_code' => $group['item_code'],
            'dscription' => $group['dscription'],
            'first_order_date' => $group['first_order_date'],
            'last_order_date' => $group['last_order_date'],
            'transaction_count' => $uniqueCount,
            'total_quantity' => $group['total_quantity'],
            'total_value' => $group['total_value'],
            'average_gap_days' => $medianGap,
            'predicted_repeat_date' => $predictedDate?->toDateString(),
            'confidence' => $confidence,
            'classification' => $classification,
            'period_label' => $group['period_label'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function median(array $values): ?float
    {
        if ($values === []) {
            return null;
        }

        sort($values, SORT_NUMERIC);
        $count = count($values);
        $middle = intdiv($count, 2);

        return $count % 2 === 1
            ? (float) $values[$middle]
            : ((float) $values[$middle - 1] + (float) $values[$middle]) / 2;
    }

    private function insertPredictionBatch(array $predictions): void
    {
        RepeatOrderPrediction::insert($predictions);
    }
}
