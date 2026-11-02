<?php

namespace App\Http\Controllers;

use App\Models\BroadcastReport;
use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\RepeatOrderPrediction;
use App\Support\PhoneNumberSql;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
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
            ->whereNotNull('brand')
            ->whereRaw("TRIM(brand) <> ''")
            ->selectRaw('UPPER(TRIM(brand)) as brand_code, SUM(quantity) as total_quantity')
            ->groupByRaw('UPPER(TRIM(brand))')
            ->orderByDesc('total_quantity')
            ->get();

        $brandSales = [];
        foreach ($brandTotals as $brandTotal) {
            $brandName = $this->mapBrandName((string) $brandTotal->brand_code);
            $brandSales[$brandName] = ($brandSales[$brandName] ?? 0) + (int) round((float) $brandTotal->total_quantity);
        }
        arsort($brandSales);
        $brandOptions = array_keys($brandSales);
        sort($brandOptions);

        if ($selectedBrand !== '' && ! in_array($selectedBrand, $brandOptions, true)) {
            $selectedBrand = '';
        }

        if ($selectedBrand !== '') {
            $matchingBrandCodes = CrossSelling::query()
                ->whereNotNull('brand')
                ->whereRaw("TRIM(brand) <> ''")
                ->distinct()
                ->pluck('brand')
                ->map(fn ($brandCode) => strtoupper(preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $brandCode))) ?? ''))
                ->filter(fn ($brandCode) => $this->mapBrandName($brandCode) === $selectedBrand)
                ->unique()
                ->values();

            $crossSellingQuery->where(function ($query) use ($matchingBrandCodes, $selectedBrand) {
                foreach ($matchingBrandCodes as $brandCode) {
                    $query->orWhereRaw('UPPER(TRIM(brand)) = ?', [$brandCode]);
                }

                if ($selectedBrand === 'LAINNYA' && $matchingBrandCodes->isEmpty()) {
                    $query->whereRaw('1 = 0');
                }
            });
        }

        $periods = (clone $crossSellingQuery)
            ->whereNotNull('period_label')
            ->selectRaw('period_label, COUNT(*) as transaction_count, COUNT(DISTINCT customer_vendor_code) as customer_count, SUM(harga_jual) as total_value')
            ->groupBy('period_label')
            ->orderByDesc('period_label')
            ->get();

        $filteredBrandSales = [];
        foreach ($brandTotals as $brandTotal) {
            $brandName = $this->mapBrandName((string) $brandTotal->brand_code);
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
