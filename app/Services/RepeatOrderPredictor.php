<?php

namespace App\Services;

use App\Models\LeadCustomer;
use App\Models\RepeatOrderPrediction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $todayTimestamp = strtotime(Carbon::today()->toDateString());
        $now = now();

        DB::table('cross_sellings')
            ->select([
                'cross_selling_import_id',
                'customer_vendor_code',
                'customer_vendor_name',
                'name',
                'no_hp_customer',
                'cabang',
                'item_code',
                'dscription',
                'posting_date',
                'quantity',
                'harga_jual',
                'period_label',
            ])
            ->whereNotNull('customer_vendor_code')
            ->whereNotNull('posting_date')
            ->where('quantity', '>', 0)
            ->whereNotNull('no_hp_customer')
            ->whereRaw("TRIM(no_hp_customer) <> ''")
            ->orderBy('customer_vendor_code')
            ->orderBy('item_code')
            ->orderBy('posting_date')
            ->cursor()
            ->each(function (object $row) use (&$currentKey, &$group, &$predictionBatch, $todayTimestamp, $now) {
                $key = $row->customer_vendor_code.'|'.($row->item_code ?? '');

                if ($currentKey !== null && $currentKey !== $key) {
                    if ($prediction = $this->buildPrediction($group, $todayTimestamp, $now)) {
                        $predictionBatch[] = $prediction;
                    }
                    if (count($predictionBatch) >= 500) {
                        $this->insertPredictionBatch($predictionBatch);
                        $predictionBatch = [];
                    }
                    $group = null;
                }

                $currentKey = $key;
                $date = substr((string) $row->posting_date, 0, 10);
                $resolvedName = $this->resolveCustomerName($row->name ?? null, $row->customer_vendor_name ?? null);

                if ($group === null) {
                    $group = [
                        'cross_selling_import_id' => $row->cross_selling_import_id,
                        'customer_vendor_code' => $row->customer_vendor_code,
                        'customer_vendor_name' => $resolvedName ?: 'No Name',
                        'no_hp_customer' => $row->no_hp_customer,
                        'cabang' => $row->cabang,
                        'item_code' => $row->item_code,
                        'dscription' => $row->dscription,
                        'first_order_date' => $date,
                        'last_order_date' => $date,
                        'dates' => [$date => true],
                        'total_quantity' => 0.0,
                        'total_value' => 0.0,
                        'period_label' => $row->period_label,
                    ];
                } else {
                    if ($resolvedName !== '') {
                        $group['customer_vendor_name'] = $resolvedName;
                    }
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
            if ($prediction = $this->buildPrediction($group, $todayTimestamp, $now)) {
                $predictionBatch[] = $prediction;
            }
        }
        if ($predictionBatch) {
            $this->insertPredictionBatch($predictionBatch);
        }

        $this->relinkExistingLeads();
    }

    private function resolveCustomerName(?string $name, ?string $vendorName): string
    {
        $cleanName = trim((string) $name);
        if ($cleanName !== '' && $cleanName !== '-') {
            return $cleanName;
        }

        $cleanVendor = trim((string) $vendorName);
        if ($cleanVendor === '' || $cleanVendor === '-') {
            return '';
        }

        $upperVendor = strtoupper($cleanVendor);
        $genericMarkers = [
            'CASH OR USER',
            'DEBIT ',
            'QRIS ',
            'KREDIVO',
            'AKULAKU',
            'SHOPEE',
            'TOKOPEDIA',
            'CREDIT ',
            'TRANSFER ',
            'OFFLINE) MPL',
            'ONLINE) MPL',
        ];

        foreach ($genericMarkers as $marker) {
            if (str_contains($upperVendor, $marker)) {
                return '';
            }
        }

        return $cleanVendor;
    }

    private function buildPrediction(array $group, int $todayTimestamp, mixed $now): ?array
    {
        if (empty($group['no_hp_customer']) || trim((string) $group['no_hp_customer']) === '') {
            return null;
        }
        $dates = array_keys($group['dates']);
        sort($dates);
        $gaps = [];

        $countDates = count($dates);
        for ($index = 1; $index < $countDates; $index++) {
            $t1 = strtotime($dates[$index - 1]);
            $t2 = strtotime($dates[$index]);
            if ($t1 !== false && $t2 !== false) {
                $gaps[] = (float) round(abs($t2 - $t1) / 86400);
            }
        }

        $medianGap = $this->median($gaps);
        $mad = $medianGap === null ? null : $this->median(array_map(
            fn (float $gap) => abs($gap - $medianGap),
            $gaps,
        ));

        $predictedDateStr = null;
        $daysUntil = null;
        if ($medianGap !== null) {
            $lastTs = strtotime($group['last_order_date']);
            if ($lastTs !== false) {
                $addDays = (int) round($medianGap);
                $predictedTs = strtotime("+{$addDays} days", $lastTs);
                if ($predictedTs !== false) {
                    $predictedDateStr = date('Y-m-d', $predictedTs);
                    $daysUntil = (int) round(($predictedTs - $todayTimestamp) / 86400);
                }
            }
        }

        $uniqueCount = $countDates;
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
        if ($daysUntil !== null) {
            $classification = $daysUntil <= 30
                ? 'tinggi'
                : ($daysUntil <= 60 ? 'sedang' : 'rendah');
        }

        return [
            'cross_selling_import_id' => $group['cross_selling_import_id'],
            'customer_vendor_code' => $group['customer_vendor_code'],
            'customer_vendor_name' => trim((string) $group['customer_vendor_name']) ?: 'No Name',
            'no_hp_customer' => $group['no_hp_customer'],
            'cabang' => $group['cabang'],
            'item_code' => $group['item_code'],
            'dscription' => $group['dscription'],
            'first_order_date' => $group['first_order_date'],
            'last_order_date' => $group['last_order_date'],
            'transaction_count' => $uniqueCount,
            'total_quantity' => $group['total_quantity'],
            'total_value' => $group['total_value'],
            'average_gap_days' => $medianGap ?? 0,
            'predicted_repeat_date' => $predictedDateStr,
            'confidence' => $confidence,
            'classification' => $classification,
            'period_label' => $group['period_label'],
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function relinkExistingLeads(): void
    {
        try {
            $leads = LeadCustomer::query()->get(['id', 'customer_vendor_code', 'item_code', 'no_hp_customer']);
            foreach ($leads as $lead) {
                $matchId = RepeatOrderPrediction::query()
                    ->where('customer_vendor_code', $lead->customer_vendor_code)
                    ->when($lead->item_code, fn ($q) => $q->where('item_code', $lead->item_code))
                    ->value('id');

                if ($matchId) {
                    LeadCustomer::where('id', $lead->id)->update(['repeat_order_prediction_id' => $matchId]);
                }
            }
        } catch (\Throwable) {
            // Ignore if table does not exist during partial migrations
        }
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
