<?php

namespace App\Services;

use App\Models\CrossSelling;
use App\Models\RepeatOrderPrediction;
use Carbon\Carbon;

class RepeatOrderPredictor
{
    public function rebuild(): void
    {
        RepeatOrderPrediction::query()->delete();

        CrossSelling::query()
            ->whereNotNull('customer_vendor_code')
            ->whereNotNull('posting_date')
            ->orderBy('customer_vendor_code')
            ->orderBy('item_code')
            ->orderBy('posting_date')
            ->get()
            ->groupBy(fn (CrossSelling $row) => $row->customer_vendor_code.'|'.($row->item_code ?? ''))
            ->each(function ($rows) {
                $datedRows = $rows->sortBy('posting_date')->values();
                $dates = $datedRows->pluck('posting_date')->unique(fn ($date) => $date->toDateString())->values();
                $first = $datedRows->first();
                $last = $datedRows->last();

                $gaps = [];
                for ($index = 1; $index < $dates->count(); $index++) {
                    $gaps[] = Carbon::parse($dates[$index - 1])->diffInDays(Carbon::parse($dates[$index]));
                }

                $averageGap = count($gaps) ? array_sum($gaps) / count($gaps) : null;
                $predictedDate = $averageGap !== null
                    ? Carbon::parse($last->posting_date)->addDays((int) round($averageGap))
                    : null;

                $confidence = match (true) {
                    $dates->count() >= 4 => 90,
                    $dates->count() === 3 => 75,
                    $dates->count() === 2 => 55,
                    default => 20,
                };

                $classification = 'baru';
                if ($predictedDate) {
                    $daysUntil = Carbon::today()->diffInDays($predictedDate, false);
                    $classification = $daysUntil <= 30 ? 'tinggi'
                        : ($daysUntil <= 60 ? 'sedang' : 'monitor');
                }

                RepeatOrderPrediction::create([
                    'cross_selling_import_id' => $last->cross_selling_import_id,
                    'customer_vendor_code' => $first->customer_vendor_code,
                    'customer_vendor_name' => $last->customer_vendor_name,
                    'no_hp_customer' => $last->no_hp_customer,
                    'cabang' => $last->cabang,
                    'item_code' => $last->item_code,
                    'dscription' => $last->dscription,
                    'first_order_date' => Carbon::parse($first->posting_date)->toDateString(),
                    'last_order_date' => Carbon::parse($last->posting_date)->toDateString(),
                    'transaction_count' => $datedRows->count(),
                    'total_quantity' => $datedRows->sum('quantity'),
                    'total_value' => $datedRows->sum('harga_jual'),
                    'average_gap_days' => $averageGap,
                    'predicted_repeat_date' => $predictedDate?->toDateString(),
                    'confidence' => $confidence,
                    'classification' => $classification,
                    'period_label' => $last->period_label,
                ]);
            });
    }
}
