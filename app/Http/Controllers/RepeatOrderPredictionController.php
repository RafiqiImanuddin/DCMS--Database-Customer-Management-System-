<?php

namespace App\Http\Controllers;

use App\Models\RepeatOrderPrediction;
use App\Services\FastXlsxWriter;
use App\Support\BrandCategory;
use App\Support\PhoneNumberSql;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RepeatOrderPredictionController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = $this->buildFilteredQuery($request);
        $chartQuery = $this->buildFilteredQuery($request, false);

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

    public function download(Request $request): BinaryFileResponse
    {
        $query = $this->buildFilteredQuery($request);

        $perBatch = 25000;
        $batch = max(1, (int) $request->integer('batch', 1));
        $offset = ($batch - 1) * $perBatch;

        $totalFiltered = (clone $query)->count();
        $query->orderBy('predicted_repeat_date')->skip($offset)->take($perBatch);

        $filename = 'master-customer';
        if ($totalFiltered > $perBatch) {
            $filename .= '-batch-'.$batch;
        }
        $filename .= '-'.now()->format('Y-m-d').'.xlsx';

        $headers = [
            'Kode Pelanggan',
            'Nama Pelanggan',
            'No HP Konsumen',
            'Kode Item',
            'Kategori',
            'Nama Barang',
            'Klasifikasi',
            'Confidence (%)',
            'Interval Hari',
            'Total Transaksi',
            'Total Nominal',
            'Prediksi Repeat Date',
            'Periode',
        ];

        $rowsGenerator = function () use ($query) {
            foreach ($query->cursor() as $row) {
                yield [
                    $row->customer_vendor_code,
                    $row->customer_vendor_name,
                    $row->no_hp_customer,
                    $row->item_code,
                    $row->category,
                    $row->dscription,
                    ucfirst($row->classification ?? '-'),
                    $row->confidence ?? 0,
                    $row->interval_days ?? 0,
                    $row->transaction_count ?? 0,
                    $row->total_nominal ?? 0,
                    $row->predicted_repeat_date?->format('d-m-Y') ?? '-',
                    $row->period_label ?? '-',
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
                    ->orWhere('dscription', 'like', '%'.$search.'%');
            });
        }

        if ($applyClassification && ($classification = $request->string('classification')->toString())) {
            if (in_array($classification, ['tinggi', 'sedang', 'rendah', 'baru'], true)) {
                $query->where('classification', $classification);
            }
        }

        if ($period = $request->string('period')->toString()) {
            $query->where('period_label', $period);
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
}
