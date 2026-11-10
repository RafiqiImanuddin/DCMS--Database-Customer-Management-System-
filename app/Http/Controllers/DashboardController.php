<?php

namespace App\Http\Controllers;

use App\Models\BroadcastReport;
use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\RepeatOrderPrediction;
use App\Support\BrandCategory;
use App\Support\PhoneNumberSql;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user() && ! $request->user()->canAccessFeature('dashboard')) {
            return redirect()->to($request->user()->getFirstAccessibleRoute());
        }
        $selectedBrand = $request->string('brand')->trim()->toString();
        $selectedPhoneStatus = $request->string('phone_status')->trim()->toString();
        if (! in_array($selectedPhoneStatus, ['valid', 'invalid', 'missing'], true)) {
            $selectedPhoneStatus = '';
        }

        $crossSellingQuery = CrossSelling::query();
        if ($selectedPhoneStatus !== '') {
            $crossSellingQuery->whereExists(function ($exists) use ($selectedPhoneStatus) {
                $exists->selectRaw('1')
                    ->from('repeat_order_predictions')
                    ->whereColumn('repeat_order_predictions.customer_vendor_code', 'cross_sellings.customer_vendor_code');

                if ($selectedPhoneStatus === 'valid') {
                    $exists->where($this->validWhatsAppCondition());
                } elseif ($selectedPhoneStatus === 'invalid') {
                    $exists->where($this->phonePresentCondition())
                        ->whereRaw(PhoneNumberSql::invalidWhatsApp());
                } else {
                    $exists->where($this->missingPhoneCondition());
                }
            });
        }

        $brandTotals = (clone $crossSellingQuery)
            ->selectRaw("
                CASE 
                    WHEN UPPER(item_code) LIKE '%ACC%' THEN 'LAINNYA'
                    ELSE UPPER(TRIM(COALESCE(brand, '')))
                END as brand_code, 
                SUM(quantity) as total_quantity
            ")
            ->where(function ($q) {
                $q->whereRaw("UPPER(item_code) LIKE '%ACC%'")
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('brand')->whereRaw("TRIM(brand) <> ''");
                  });
            })
            ->groupByRaw("
                CASE 
                    WHEN UPPER(item_code) LIKE '%ACC%' THEN 'LAINNYA'
                    ELSE UPPER(TRIM(COALESCE(brand, '')))
                END
            ")
            ->orderByDesc('total_quantity')
            ->get();

        $brandSales = [];
        foreach ($brandTotals as $brandTotal) {
            $brandCode = (string) $brandTotal->brand_code;
            $brandName = $brandCode === BrandCategory::DEFAULT_CATEGORY ? BrandCategory::DEFAULT_CATEGORY : BrandCategory::resolve(null, $brandCode);
            $brandSales[$brandName] = ($brandSales[$brandName] ?? 0) + (int) round((float) $brandTotal->total_quantity);
        }
        arsort($brandSales);
        $brandOptions = array_keys($brandSales);
        sort($brandOptions);

        if ($selectedBrand !== '' && ! in_array($selectedBrand, $brandOptions, true)) {
            $selectedBrand = '';
        }

        if ($selectedBrand !== '') {
            if ($selectedBrand === BrandCategory::DEFAULT_CATEGORY) {
                $knownBrandCodes = collect(BrandCategory::allKnownBrandCodes());
                $placeholders = implode(',', array_fill(0, $knownBrandCodes->count(), '?'));
                $crossSellingQuery->where(function ($query) use ($knownBrandCodes, $placeholders) {
                    $query->whereRaw("UPPER(item_code) LIKE '%ACC%'")
                        ->orWhere(function ($sub) use ($knownBrandCodes, $placeholders) {
                            $sub->whereNotNull('brand')
                                ->whereRaw("TRIM(brand) <> ''")
                                ->whereRaw("UPPER(TRIM(brand)) NOT IN ($placeholders)", $knownBrandCodes->map(fn ($code) => strtoupper($code))->all());
                        });
                });
            } else {
                $matchingBrandCodes = collect(BrandCategory::brandCodesFor($selectedBrand));
                $placeholders = implode(',', array_fill(0, $matchingBrandCodes->count(), '?'));
                $crossSellingQuery->whereRaw("UPPER(item_code) NOT LIKE '%ACC%'")
                    ->whereRaw("UPPER(TRIM(brand)) IN ($placeholders)", $matchingBrandCodes->map(fn ($code) => strtoupper($code))->all());
            }
        }

        $periods = (clone $crossSellingQuery)
            ->whereNotNull('period_label')
            ->selectRaw('period_label, COUNT(*) as transaction_count, COUNT(DISTINCT customer_vendor_code) as customer_count, SUM(harga_jual) as total_value')
            ->groupBy('period_label')
            ->orderByDesc('period_label')
            ->get();

        $filteredBrandSales = [];
        foreach ($brandTotals as $brandTotal) {
            $brandCode = (string) $brandTotal->brand_code;
            $brandName = $brandCode === BrandCategory::DEFAULT_CATEGORY ? BrandCategory::DEFAULT_CATEGORY : BrandCategory::resolve(null, $brandCode);
            if ($selectedBrand === '' || $brandName === $selectedBrand) {
                $filteredBrandSales[$brandName] = ($filteredBrandSales[$brandName] ?? 0) + (int) round((float) $brandTotal->total_quantity);
            }
        }
        arsort($filteredBrandSales);
        $filteredBrandSales = collect($filteredBrandSales)->map(fn (int $quantity, string $brand) => [
            'brand' => $brand,
            'quantity' => $quantity,
        ])->values();

        $predictionQuery = RepeatOrderPrediction::query();
        if ($selectedPhoneStatus === 'valid') {
            $predictionQuery->where($this->validWhatsAppCondition());
        } elseif ($selectedPhoneStatus === 'invalid') {
            $predictionQuery->where($this->phonePresentCondition())
                ->whereRaw(PhoneNumberSql::invalidWhatsApp());
        } elseif ($selectedPhoneStatus === 'missing') {
            $predictionQuery->where($this->missingPhoneCondition());
        }

        if ($selectedBrand !== '') {
            if ($selectedBrand === BrandCategory::DEFAULT_CATEGORY) {
                $knownBrandCodes = collect(BrandCategory::allKnownBrandCodes());
                $placeholders = implode(',', array_fill(0, $knownBrandCodes->count(), '?'));
                $predictionQuery->where(function ($sub) use ($knownBrandCodes, $placeholders) {
                    $sub->whereRaw("UPPER(repeat_order_predictions.item_code) LIKE '%ACC%'")
                        ->orWhereExists(function ($exists) use ($knownBrandCodes, $placeholders) {
                            $exists->selectRaw('1')
                                ->from('cross_sellings')
                                ->whereColumn('cross_sellings.customer_vendor_code', 'repeat_order_predictions.customer_vendor_code')
                                ->whereColumn('cross_sellings.item_code', 'repeat_order_predictions.item_code')
                                ->whereNotNull('cross_sellings.brand')
                                ->whereRaw("TRIM(cross_sellings.brand) <> ''")
                                ->whereRaw("UPPER(TRIM(cross_sellings.brand)) NOT IN ($placeholders)", $knownBrandCodes->map(fn ($code) => strtoupper($code))->all());
                        });
                });
            } else {
                $matchingBrandCodes = collect(BrandCategory::brandCodesFor($selectedBrand));
                if ($matchingBrandCodes->isEmpty()) {
                    $predictionQuery->whereRaw('1 = 0');
                } else {
                    $placeholders = implode(',', array_fill(0, $matchingBrandCodes->count(), '?'));
                    $predictionQuery->whereRaw("UPPER(repeat_order_predictions.item_code) NOT LIKE '%ACC%'")
                        ->whereExists(function ($exists) use ($matchingBrandCodes, $placeholders) {
                            $exists->selectRaw('1')
                                ->from('cross_sellings')
                                ->whereColumn('cross_sellings.customer_vendor_code', 'repeat_order_predictions.customer_vendor_code')
                                ->whereColumn('cross_sellings.item_code', 'repeat_order_predictions.item_code')
                                ->whereRaw("UPPER(TRIM(cross_sellings.brand)) IN ($placeholders)", $matchingBrandCodes->map(fn ($code) => strtoupper($code))->all());
                        });
                }
            }
        }

        $potentialChart = (clone $predictionQuery)
            ->selectRaw('classification, COUNT(*) as total')
            ->groupBy('classification')
            ->pluck('total', 'classification');

        return view('dashboard.index', [
            'totalTransactions' => (clone $crossSellingQuery)->count(),
            'totalCustomers' => (clone $predictionQuery)->distinct('customer_vendor_code')->count('customer_vendor_code'),
            'totalPredictions' => (clone $predictionQuery)->count(),
            'highPotentials' => (clone $predictionQuery)->where('classification', 'tinggi')->count(),
            'broadcastSent' => BroadcastReport::where('status', 'sent')->count(),
            'latestImports' => CrossSellingImport::latest()->take(5)->get(),
            'topPredictions' => (clone $predictionQuery)->whereNotNull('predicted_repeat_date')->orderBy('predicted_repeat_date')->take(10)->get(),
            'periods' => $periods,
            'brandSales' => $filteredBrandSales,
            'potentialChart' => $potentialChart,
            'brandOptions' => $brandOptions,
            'selectedBrand' => $selectedBrand,
            'selectedPhoneStatus' => $selectedPhoneStatus,
        ]);
    }

    private function validWhatsAppCondition(): callable
    {
        return function ($builder) {
            $builder->whereNotNull('no_hp_customer')
                ->whereRaw("TRIM(no_hp_customer) <> ''")
                ->whereRaw("no_hp_customer LIKE '62%'")
                ->whereRaw('LENGTH(no_hp_customer) BETWEEN 10 AND 15')
                ->whereRaw(PhoneNumberSql::numericOnly());
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
}
