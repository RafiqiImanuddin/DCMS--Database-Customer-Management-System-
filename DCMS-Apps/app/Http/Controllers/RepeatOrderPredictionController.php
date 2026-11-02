<?php

namespace App\Http\Controllers;

use App\Models\RepeatOrderPrediction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepeatOrderPredictionController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = RepeatOrderPrediction::query();
        $chartQuery = RepeatOrderPrediction::query();
        $applyMasterCustomerScope = function ($builder) use ($request) {
            $builder->whereNotExists(function ($exists) use ($request) {
                $exists->selectRaw('1')
                    ->from('lead_customers')
                    ->whereColumn('lead_customers.repeat_order_prediction_id', 'repeat_order_predictions.id')
                    ->where('lead_customers.user_id', $request->user()->id);
            });
        };
        $query->where($applyMasterCustomerScope);
        $chartQuery->where($applyMasterCustomerScope);

        $brandOptions = collect([
            'IQOO', 'ZTE', 'REALME', 'XIAOMI', 'POCO', 'IPHONE', 'ITEL', 'TECNO',
            'INFINIX', 'MOTOROLA', 'HUAWEI', 'HONOR', 'ADVAN', 'VILLAON',
            'OPPO', 'VIVO', 'SAMSUNG', 'LAINNYA',
        ]);
        $selectedBrand = $request->string('brand')->trim()->toString();
        if (! $brandOptions->contains($selectedBrand)) {
            $selectedBrand = '';
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('customer_vendor_code', 'like', '%'.$search.'%')
                    ->orWhere('customer_vendor_name', 'like', '%'.$search.'%')
                    ->orWhere('no_hp_customer', 'like', '%'.$search.'%')
                    ->orWhere('item_code', 'like', '%'.$search.'%')
                    ->orWhere('dscription', 'like', '%'.$search.'%');
            });
            $chartQuery->where(function ($builder) use ($search) {
                $builder->where('customer_vendor_code', 'like', '%'.$search.'%')
                    ->orWhere('customer_vendor_name', 'like', '%'.$search.'%')
                    ->orWhere('no_hp_customer', 'like', '%'.$search.'%')
                    ->orWhere('item_code', 'like', '%'.$search.'%')
                    ->orWhere('dscription', 'like', '%'.$search.'%');
            });
        }
        if ($classification = $request->string('classification')->toString()) {
            if (in_array($classification, ['tinggi', 'sedang', 'rendah', 'baru'], true)) {
                $query->where('classification', $classification);
            }
        }
        if ($period = $request->string('period')->toString()) {
            $query->where('period_label', $period);
            $chartQuery->where('period_label', $period);
        }
        $selectedPhoneStatus = $request->string('phone_status')->toString();
        if (! in_array($selectedPhoneStatus, ['valid', 'invalid', 'missing'], true)) {
            $selectedPhoneStatus = '';
        }
        if ($selectedPhoneStatus !== '') {
            if ($selectedPhoneStatus === 'valid') {
                $query->where($this->validWhatsAppCondition());
            } elseif ($selectedPhoneStatus === 'invalid') {
                $query->where($this->phonePresentCondition())
                    ->where(function ($builder) {
                        $builder->whereRaw("NOT (no_hp_customer LIKE '62%' AND LENGTH(no_hp_customer) BETWEEN 10 AND 15 AND no_hp_customer NOT GLOB '*[^0-9]*')");
                    });
            } else {
                $query->where($this->missingPhoneCondition());
            }
        }
        if ($selectedBrand !== '') {
            if ($selectedBrand === 'LAINNYA') {
                $knownBrandCodes = collect($this->allKnownBrandCodes());
                $placeholders = implode(',', array_fill(0, $knownBrandCodes->count(), '?'));
                $applyBrandFilter = function ($builder) use ($knownBrandCodes, $placeholders) {
                    $builder->whereExists(function ($exists) use ($knownBrandCodes, $placeholders) {
                        $exists->selectRaw('1')
                            ->from('cross_sellings')
                            ->whereColumn('cross_sellings.customer_vendor_code', 'repeat_order_predictions.customer_vendor_code')
                            ->whereColumn('cross_sellings.item_code', 'repeat_order_predictions.item_code')
                            ->whereNotNull('cross_sellings.brand')
                            ->whereRaw("TRIM(cross_sellings.brand) <> ''")
                            ->whereRaw("UPPER(TRIM(cross_sellings.brand)) NOT IN ($placeholders)", $knownBrandCodes->map(fn ($code) => strtoupper($code))->all());
                    });
                };
                $query->where($applyBrandFilter);
                $chartQuery->where($applyBrandFilter);
            } else {
                $matchingBrandCodes = collect($this->brandCodesFor($selectedBrand));
                if ($matchingBrandCodes->isEmpty()) {
                    $query->whereRaw('1 = 0');
                    $chartQuery->whereRaw('1 = 0');
                } else {
                    $placeholders = implode(',', array_fill(0, $matchingBrandCodes->count(), '?'));
                    $applyBrandFilter = function ($builder) use ($matchingBrandCodes, $placeholders) {
                        $builder->whereExists(function ($exists) use ($matchingBrandCodes, $placeholders) {
                            $exists->selectRaw('1')
                                ->from('cross_sellings')
                                ->whereColumn('cross_sellings.customer_vendor_code', 'repeat_order_predictions.customer_vendor_code')
                                ->whereColumn('cross_sellings.item_code', 'repeat_order_predictions.item_code')
                                ->whereRaw("UPPER(TRIM(cross_sellings.brand)) IN ($placeholders)", $matchingBrandCodes->map(fn ($code) => strtoupper($code))->all());
                        });
                    };
                    $query->where($applyBrandFilter);
                    $chartQuery->where($applyBrandFilter);
                }
            }
        }

        $phoneChartBase = clone $chartQuery;
        $phoneChart = [
            'valid' => (clone $phoneChartBase)->where($this->validWhatsAppCondition())->count(),
            'invalid' => (clone $phoneChartBase)->where($this->phonePresentCondition())
                ->whereRaw("NOT (no_hp_customer LIKE '62%' AND LENGTH(no_hp_customer) BETWEEN 10 AND 15 AND no_hp_customer NOT GLOB '*[^0-9]*')")
                ->count(),
            'missing' => (clone $phoneChartBase)->where($this->missingPhoneCondition())->count(),
        ];

        return view('predictions.index', [
            'predictions' => $query->orderBy('predicted_repeat_date')->paginate(20)->withQueryString(),
            'periods' => RepeatOrderPrediction::whereNotNull('period_label')->distinct()->orderByDesc('period_label')->pluck('period_label'),
            'brandOptions' => $brandOptions,
            'selectedBrand' => $selectedBrand,
            'selectedPhoneStatus' => $selectedPhoneStatus,
            'phoneChart' => $phoneChart,
            'potentialChart' => $chartQuery
                ->selectRaw('classification, COUNT(*) as total')
                ->groupBy('classification')
                ->pluck('total', 'classification'),
        ]);
    }

    private function validWhatsAppCondition(): callable
    {
        return function ($builder) {
            $builder->whereNotNull('no_hp_customer')
                ->whereRaw("TRIM(no_hp_customer) <> ''")
                ->whereRaw("no_hp_customer LIKE '62%'")
                ->whereRaw('LENGTH(no_hp_customer) BETWEEN 10 AND 15')
                ->whereRaw("no_hp_customer NOT GLOB '*[^0-9]*'");
        };
    }

    private function missingPhoneCondition(): callable
    {
        return function ($builder) {
            $builder->where(function ($missing) {
                $missing->whereNull('no_hp_customer')
                    ->orWhereRaw("TRIM(no_hp_customer) = ''");
            });
        };
    }

    private function phonePresentCondition(): callable
    {
        return function ($builder) {
            $builder->whereNotNull('no_hp_customer')
                ->whereRaw("TRIM(no_hp_customer) <> ''");
        };
    }

    private function brandCodesFor(string $brandName): array
    {
        return match ($brandName) {
            'IQOO' => ['IQO', 'IQOO'],
            'REALME' => ['RLM', 'REALME'],
            'XIAOMI' => ['XMI', 'XIAOMI'],
            'POCO' => ['POC', 'POCO'],
            'IPHONE' => ['APL', 'APPLE', 'IPHONE'],
            'ITEL' => ['ITV', 'ITEL'],
            'TECNO' => ['TCN', 'TECNO'],
            'INFINIX' => ['IFX', 'INFINIX'],
            'MOTOROLA' => ['MTL', 'MOTOROLA'],
            'HUAWEI' => ['HWP', 'HWI', 'HKM', 'HUAWEI'],
            'HONOR' => ['HON', 'HONOR'],
            'ADVAN' => ['ADV', 'ADVAN'],
            'VILLAON' => ['VVN', 'VLN', 'VILLAON'],
            'OPPO' => ['OPO', 'OPPO'],
            'VIVO' => ['VIV', 'VIVO'],
            'SAMSUNG' => ['SAM', 'SAMSUNG'],
            'ZTE' => ['ZTE'],
            default => [$brandName],
        };
    }

    private function allKnownBrandCodes(): array
    {
        $codes = [];
        foreach ([
            'IQOO', 'ZTE', 'REALME', 'XIAOMI', 'POCO', 'IPHONE', 'ITEL', 'TECNO',
            'INFINIX', 'MOTOROLA', 'HUAWEI', 'HONOR', 'ADVAN', 'VILLAON',
            'OPPO', 'VIVO', 'SAMSUNG',
        ] as $brandName) {
            $codes = array_merge($codes, $this->brandCodesFor($brandName));
        }

        return array_values(array_unique($codes));
    }

    private function mapBrandName(string $brandCode): string
    {
        $code = strtoupper(preg_replace('/[^A-Z0-9]/', '', $brandCode) ?? '');
        $brands = [
            'IQO' => 'IQOO', 'IQOO' => 'IQOO',
            'ZTE' => 'ZTE',
            'RLM' => 'REALME', 'REALME' => 'REALME',
            'XMI' => 'XIAOMI', 'XIAOMI' => 'XIAOMI',
            'POC' => 'POCO', 'POCO' => 'POCO',
            'APL' => 'IPHONE', 'APPLE' => 'IPHONE', 'IPHONE' => 'IPHONE',
            'ITV' => 'ITEL', 'ITEL' => 'ITEL',
            'TCN' => 'TECNO', 'TECNO' => 'TECNO',
            'IFX' => 'INFINIX', 'INFINIX' => 'INFINIX',
            'MTL' => 'MOTOROLA', 'MOTOROLA' => 'MOTOROLA',
            'HWP' => 'HUAWEI', 'HWI' => 'HUAWEI', 'HKM' => 'HUAWEI', 'HUAWEI' => 'HUAWEI',
            'HON' => 'HONOR', 'HONOR' => 'HONOR',
            'ADV' => 'ADVAN', 'ADVAN' => 'ADVAN',
            'VVN' => 'VILLAON', 'VLN' => 'VILLAON', 'VILLAON' => 'VILLAON',
            'OPO' => 'OPPO', 'OPPO' => 'OPPO',
            'VIV' => 'VIVO', 'VIVO' => 'VIVO',
            'SAM' => 'SAMSUNG', 'SAMSUNG' => 'SAMSUNG',
        ];

        return $brands[$code] ?? 'LAINNYA';
    }
}
