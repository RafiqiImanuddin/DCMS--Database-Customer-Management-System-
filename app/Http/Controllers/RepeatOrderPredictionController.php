<?php

namespace App\Http\Controllers;

use App\Models\BroadcastTemplate;
use App\Models\CrossSelling;
use App\Models\RepeatOrderPrediction;
use App\Services\FastXlsxWriter;
use App\Support\BrandCategory;
use App\Support\PhoneNumberSql;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RepeatOrderPredictionController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = $this->buildFilteredQuery($request);
        $chartQuery = $this->buildFilteredQuery($request, false);

        $today = now()->toDateString();
        $in30Days = now()->addDays(30)->toDateString();
        $thisMonthStart = now()->startOfMonth()->toDateString();
        $thisMonthEnd = now()->endOfMonth()->toDateString();

        // 1. Executive Summary Statistics (Repeat Order Intelligence)
        $totalRepeatCustomers = RepeatOrderPrediction::distinct('customer_vendor_code')->count('customer_vendor_code');
        $totalRepeatOrders = RepeatOrderPrediction::count();
        $totalAllCustomers = CrossSelling::distinct('customer_vendor_code')->count('customer_vendor_code');
        $repeatOrderRate = $totalAllCustomers > 0 ? round(($totalRepeatCustomers / $totalAllCustomers) * 100, 1) : 0;
        $avgGapDays = round((float) RepeatOrderPrediction::avg('average_gap_days'), 1);

        $overdueCount = RepeatOrderPrediction::whereNotNull('predicted_repeat_date')
            ->where('predicted_repeat_date', '<', $today)
            ->count();

        $dueThisMonthCount = RepeatOrderPrediction::whereNotNull('predicted_repeat_date')
            ->whereBetween('predicted_repeat_date', [$thisMonthStart, $thisMonthEnd])
            ->count();

        $dueNext30DaysCount = RepeatOrderPrediction::whereNotNull('predicted_repeat_date')
            ->whereBetween('predicted_repeat_date', [$today, $in30Days])
            ->count();

        $highPotentialCount = RepeatOrderPrediction::where('classification', 'tinggi')->count();

        $validPhoneCount = RepeatOrderPrediction::whereNotNull('no_hp_customer')
            ->whereRaw("TRIM(no_hp_customer) <> ''")
            ->whereRaw("no_hp_customer LIKE '62%'")
            ->whereRaw('LENGTH(no_hp_customer) BETWEEN 10 AND 15')
            ->whereRaw(PhoneNumberSql::numericOnly())
            ->count();

        $totalEstimatedValue = (float) RepeatOrderPrediction::sum('total_value');

        // 2. Loyalty Segmentation Breakdown
        $freq2 = RepeatOrderPrediction::where('transaction_count', 2)->count();
        $freq3to5 = RepeatOrderPrediction::whereBetween('transaction_count', [3, 5])->count();
        $freqAbove5 = RepeatOrderPrediction::where('transaction_count', '>', 5)->count();

        // 3. Purchase Cycle (Gap Days) Distribution
        $gapUnder30 = RepeatOrderPrediction::where('average_gap_days', '<', 30)->count();
        $gap30to60 = RepeatOrderPrediction::whereBetween('average_gap_days', [30, 60])->count();
        $gap60to90 = RepeatOrderPrediction::whereBetween('average_gap_days', [60.01, 90])->count();
        $gapAbove90 = RepeatOrderPrediction::where('average_gap_days', '>', 90)->count();

        // 4. Product Category Breakdown (ACC is always LAINNYA)
        $accCount = RepeatOrderPrediction::whereRaw("UPPER(item_code) LIKE '%ACC%'")->count();
        $smartphoneCount = RepeatOrderPrediction::whereRaw("UPPER(item_code) NOT LIKE '%ACC%'")
            ->where(function ($q) {
                $q->whereRaw("UPPER(item_code) LIKE 'GAD.%'")
                    ->orWhereRaw("UPPER(item_code) LIKE 'HP%'");
            })->count();
        $otherCategoryCount = max(0, $totalRepeatOrders - $accCount - $smartphoneCount);

        // 5. Filter Options & Charts
        $brandOptions = collect(BrandCategory::BRAND_OPTIONS);
        $selectedBrand = $request->string('brand')->trim()->toString();
        if (! $brandOptions->contains($selectedBrand)) {
            $selectedBrand = '';
        }

        $selectedPhoneStatus = $request->string('phone_status')->toString();
        if (! in_array($selectedPhoneStatus, ['valid', 'invalid', 'missing'], true)) {
            $selectedPhoneStatus = '';
        }

        $phoneChartBase = clone $chartQuery;
        $phoneChart = [
            'valid' => (clone $phoneChartBase)->where($this->validWhatsAppCondition())->count(),
            'invalid' => (clone $phoneChartBase)->where($this->phonePresentCondition())
                ->whereRaw(PhoneNumberSql::invalidWhatsApp())
                ->count(),
            'missing' => (clone $phoneChartBase)->where($this->missingPhoneCondition())->count(),
        ];

        // 6. Broadcast Templates
        $templates = BroadcastTemplate::orderBy('id')->get();

        $branches = RepeatOrderPrediction::whereNotNull('cabang')
            ->whereRaw("TRIM(cabang) <> ''")
            ->distinct()
            ->orderBy('cabang')
            ->pluck('cabang');

        $selectedCabang = $request->string('cabang')->trim()->toString();
        if ($selectedCabang !== '' && ! $branches->contains($selectedCabang)) {
            $selectedCabang = '';
        }

        $perPage = (int) $request->integer('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100, 200], true)) {
            $perPage = 20;
        }

        $orderClause = DB::connection()->getDriverName() === 'pgsql'
            ? 'predicted_repeat_date ASC NULLS LAST'
            : 'CASE WHEN predicted_repeat_date IS NULL THEN 1 ELSE 0 END, predicted_repeat_date ASC';

        return view('predictions.index', [
            'predictions' => $query->with('crossSellingImport')->orderByRaw($orderClause)->orderByDesc('transaction_count')->paginate($perPage)->withQueryString(),
            'periods' => RepeatOrderPrediction::whereNotNull('period_label')->distinct()->orderByDesc('period_label')->pluck('period_label'),
            'branches' => $branches,
            'selectedCabang' => $selectedCabang,
            'perPage' => $perPage,
            'brandOptions' => $brandOptions,
            'selectedBrand' => $selectedBrand,
            'selectedPhoneStatus' => $selectedPhoneStatus,
            'phoneChart' => $phoneChart,
            'potentialChart' => $chartQuery
                ->selectRaw('classification, COUNT(*) as total')
                ->groupBy('classification')
                ->pluck('total', 'classification'),

            // Executive Repeat Order Analytics
            'totalRepeatCustomers' => $totalRepeatCustomers,
            'totalRepeatOrders' => $totalRepeatOrders,
            'totalAllCustomers' => $totalAllCustomers,
            'repeatOrderRate' => $repeatOrderRate,
            'avgGapDays' => $avgGapDays,
            'overdueCount' => $overdueCount,
            'dueThisMonthCount' => $dueThisMonthCount,
            'dueNext30DaysCount' => $dueNext30DaysCount,
            'highPotentialCount' => $highPotentialCount,
            'validPhoneCount' => $validPhoneCount,
            'totalEstimatedValue' => $totalEstimatedValue,

            // Segmentation
            'freq2' => $freq2,
            'freq3to5' => $freq3to5,
            'freqAbove5' => $freqAbove5,
            'gapUnder30' => $gapUnder30,
            'gap30to60' => $gap30to60,
            'gap60to90' => $gap60to90,
            'gapAbove90' => $gapAbove90,
            'smartphoneCount' => $smartphoneCount,
            'accCount' => $accCount,
            'otherCategoryCount' => $otherCategoryCount,

            // Templates & Date
            'templates' => $templates,
            'today' => $today,
        ]);
    }

    public function download(Request $request): BinaryFileResponse
    {
        $query = $this->buildFilteredQuery($request);

        $perBatch = 25000;
        $batch = max(1, (int) $request->integer('batch', 1));
        $offset = ($batch - 1) * $perBatch;

        $totalFiltered = (clone $query)->count();
        $orderClause = DB::connection()->getDriverName() === 'pgsql'
            ? 'predicted_repeat_date ASC NULLS LAST'
            : 'CASE WHEN predicted_repeat_date IS NULL THEN 1 ELSE 0 END, predicted_repeat_date ASC';

        $query->with('crossSellingImport')->orderByRaw($orderClause)->orderByDesc('transaction_count')->skip($offset)->take($perBatch);

        $selectedCabang = $request->string('cabang')->trim()->toString();
        $filename = 'master-customer-repeat-order';
        if ($selectedCabang !== '') {
            $filename .= '-cabang-'.Str::slug($selectedCabang);
        }
        if ($totalFiltered > $perBatch) {
            $filename .= '-batch-'.$batch;
        }
        $filename .= '-'.now()->format('Y-m-d').'.xlsx';

        $headers = [
            'Kode Pelanggan',
            'Nama Pelanggan',
            'No HP Konsumen',
            'Cabang',
            'Kode Item',
            'Kategori',
            'Nama Barang',
            'Klasifikasi',
            'Confidence (%)',
            'Interval / Siklus Hari',
            'Total Transaksi',
            'Total Nominal',
            'Prediksi Repeat Date',
            'Periode',
            'Tanggal Dispatch',
            'Sumber File',
        ];

        $rowsGenerator = function () use ($query) {
            foreach ($query->cursor() as $row) {
                $dispatch = $row->crossSellingImport?->created_at ?? $row->created_at;
                $sourceFile = $row->crossSellingImport?->original_name ?? ($row->cross_selling_import_id ? 'Batch #' . $row->cross_selling_import_id : '-');

                yield [
                    $row->customer_vendor_code,
                    $row->customer_vendor_name,
                    $row->no_hp_customer,
                    $row->cabang ?? '-',
                    $row->item_code,
                    $row->category,
                    $row->dscription,
                    ucfirst($row->classification ?? '-'),
                    $row->confidence ?? 0,
                    $row->average_gap_days ?? $row->interval_days ?? 0,
                    $row->transaction_count ?? 0,
                    $row->total_value ?? $row->total_nominal ?? 0,
                    $row->predicted_repeat_date?->format('d-m-Y') ?? '-',
                    $row->period_label ?? '-',
                    $dispatch?->format('d-m-Y H:i') ?? '-',
                    $sourceFile,
                ];
            }
        };

        return FastXlsxWriter::download($filename, $headers, $rowsGenerator());
    }

    private function buildFilteredQuery(Request $request, bool $applyClassification = true): Builder
    {
        $query = RepeatOrderPrediction::query();
        $applyMasterCustomerScope = function ($builder) use ($request) {
            $builder->whereNotExists(function ($exists) use ($request) {
                $exists->selectRaw('1')
                    ->from('lead_customers')
                    ->whereColumn('lead_customers.repeat_order_prediction_id', 'repeat_order_predictions.id')
                    ->where('lead_customers.user_id', $request->user()->id);
            });
        };
        $query->where($applyMasterCustomerScope);

        $selectedCabang = $request->string('cabang')->trim()->toString();
        if ($selectedCabang !== '') {
            $query->where('cabang', $selectedCabang);
        }

        $brandOptions = collect(BrandCategory::BRAND_OPTIONS);
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
                    ->orWhere('dscription', 'like', '%'.$search.'%')
                    ->orWhere('cabang', 'like', '%'.$search.'%');
            });
        }

        if ($applyClassification && ($classification = $request->string('classification')->toString())) {
            if (in_array($classification, ['tinggi', 'sedang', 'rendah', 'baru'], true)) {
                $query->where('classification', $classification);
            }
        }

        $today = now()->toDateString();
        $in30Days = now()->addDays(30)->toDateString();
        $thisMonthStart = now()->startOfMonth()->toDateString();
        $thisMonthEnd = now()->endOfMonth()->toDateString();

        if ($period = $request->string('period')->toString()) {
            if ($period === 'overdue') {
                $query->whereNotNull('predicted_repeat_date')->where('predicted_repeat_date', '<', $today);
            } elseif ($period === 'this_month') {
                $query->whereNotNull('predicted_repeat_date')->whereBetween('predicted_repeat_date', [$thisMonthStart, $thisMonthEnd]);
            } elseif ($period === 'next_30_days') {
                $query->whereNotNull('predicted_repeat_date')->whereBetween('predicted_repeat_date', [$today, $in30Days]);
            } else {
                $query->where('period_label', $period);
            }
        }

        if ($category = $request->string('category')->toString()) {
            if ($category === 'ACC' || $category === 'LAINNYA') {
                $query->where(function ($q) {
                    $q->whereRaw("UPPER(repeat_order_predictions.item_code) LIKE '%ACC%'")
                        ->orWhereRaw("UPPER(repeat_order_predictions.item_code) NOT LIKE 'GAD.%'");
                });
            } elseif ($category === 'SMARTPHONE') {
                $query->whereRaw("UPPER(repeat_order_predictions.item_code) NOT LIKE '%ACC%'")
                    ->where(function ($q) {
                        $q->whereRaw("UPPER(repeat_order_predictions.item_code) LIKE 'GAD.%'")
                            ->orWhereRaw("UPPER(repeat_order_predictions.item_code) LIKE 'HP%'");
                    });
            }
        }

        $selectedPhoneStatus = $request->string('phone_status')->toString();
        if ($selectedPhoneStatus === 'valid') {
            $query->where($this->validWhatsAppCondition());
        } elseif ($selectedPhoneStatus === 'invalid') {
            $query->where($this->phonePresentCondition())
                ->where(function ($builder) {
                    $builder->whereRaw(PhoneNumberSql::invalidWhatsApp());
                });
        } elseif ($selectedPhoneStatus === 'missing') {
            $query->where($this->missingPhoneCondition());
        }

        if ($selectedBrand !== '') {
            if ($selectedBrand === BrandCategory::DEFAULT_CATEGORY) {
                $knownBrandCodes = collect(BrandCategory::allKnownBrandCodes());
                $placeholders = implode(',', array_fill(0, $knownBrandCodes->count(), '?'));
                $query->where(function ($sub) use ($knownBrandCodes, $placeholders) {
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
                    $query->whereRaw('1 = 0');
                } else {
                    $placeholders = implode(',', array_fill(0, $matchingBrandCodes->count(), '?'));
                    $query->whereRaw("UPPER(repeat_order_predictions.item_code) NOT LIKE '%ACC%'")
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

        return $query;
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

    public function rebuild(Request $request, \App\Services\RepeatOrderPredictor $predictor): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Hanya Superadmin yang dapat memperbarui prediksi AI.');

        $predictor->rebuild();
        $count = RepeatOrderPrediction::count();
        $msg = "Analisis Prediksi Repeat Order AI berhasil diperbarui! Total {$count} data pelanggan.";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'count' => $count]);
        }

        return redirect()->route('predictions.index')->with('success', $msg);
    }
}
