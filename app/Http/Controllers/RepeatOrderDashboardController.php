<?php

namespace App\Http\Controllers;

use App\Models\CrossSelling;
use App\Models\RepeatOrderPrediction;
use App\Support\PhoneNumberSql;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RepeatOrderDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $periodFilter = $request->string('period', 'all')->toString();
        $classificationFilter = $request->string('classification', 'all')->toString();
        $categoryFilter = $request->string('category', 'all')->toString();
        $phoneStatusFilter = $request->string('phone_status', 'all')->toString();
        $search = $request->string('search')->trim()->toString();

        $today = now()->toDateString();
        $in30Days = now()->addDays(30)->toDateString();
        $thisMonthStart = now()->startOfMonth()->toDateString();
        $thisMonthEnd = now()->endOfMonth()->toDateString();

        // 1. Executive Summary Statistics
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

        // 5. Filtered Table of Priority Repeat Order Consumers
        $query = RepeatOrderPrediction::query();

        // Apply Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('customer_vendor_name', 'ilike', "%{$search}%")
                    ->orWhere('customer_vendor_code', 'ilike', "%{$search}%")
                    ->orWhere('no_hp_customer', 'like', "%{$search}%")
                    ->orWhere('item_code', 'ilike', "%{$search}%")
                    ->orWhere('dscription', 'ilike', "%{$search}%");
            });
        }

        // Apply Period Filter
        if ($periodFilter === 'overdue') {
            $query->whereNotNull('predicted_repeat_date')->where('predicted_repeat_date', '<', $today);
        } elseif ($periodFilter === 'this_month') {
            $query->whereNotNull('predicted_repeat_date')->whereBetween('predicted_repeat_date', [$thisMonthStart, $thisMonthEnd]);
        } elseif ($periodFilter === 'next_30_days') {
            $query->whereNotNull('predicted_repeat_date')->whereBetween('predicted_repeat_date', [$today, $in30Days]);
        }

        // Apply Classification Filter
        if ($classificationFilter !== 'all') {
            $query->where('classification', $classificationFilter);
        }

        // Apply Category Filter
        if ($categoryFilter === 'ACC' || $categoryFilter === 'LAINNYA') {
            $query->where(function ($q) {
                $q->whereRaw("UPPER(item_code) LIKE '%ACC%'")
                    ->orWhereRaw("UPPER(item_code) NOT LIKE 'GAD.%'");
            });
        } elseif ($categoryFilter === 'SMARTPHONE') {
            $query->whereRaw("UPPER(item_code) NOT LIKE '%ACC%'")
                ->where(function ($q) {
                    $q->whereRaw("UPPER(item_code) LIKE 'GAD.%'")
                        ->orWhereRaw("UPPER(item_code) LIKE 'HP%'");
                });
        }

        // Apply Phone Status Filter
        if ($phoneStatusFilter === 'valid') {
            $query->whereNotNull('no_hp_customer')
                ->whereRaw("TRIM(no_hp_customer) <> ''")
                ->whereRaw("no_hp_customer LIKE '62%'")
                ->whereRaw('LENGTH(no_hp_customer) BETWEEN 10 AND 15')
                ->whereRaw(PhoneNumberSql::numericOnly());
        } elseif ($phoneStatusFilter === 'missing') {
            $query->where(function ($q) {
                $q->whereNull('no_hp_customer')->orWhereRaw("TRIM(no_hp_customer) = ''");
            });
        }

        // Order by priority: due date earliest first, then highest transaction count
        $orderClause = DB::connection()->getDriverName() === 'pgsql'
            ? 'predicted_repeat_date ASC NULLS LAST'
            : 'CASE WHEN predicted_repeat_date IS NULL THEN 1 ELSE 0 END, predicted_repeat_date ASC';

        $priorityCustomers = $query->orderByRaw($orderClause)
            ->orderByDesc('transaction_count')
            ->orderByDesc('confidence')
            ->paginate(15)
            ->withQueryString();

        return view('repeat-orders.dashboard', [
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

            // Table & Filters
            'priorityCustomers' => $priorityCustomers,
            'periodFilter' => $periodFilter,
            'classificationFilter' => $classificationFilter,
            'categoryFilter' => $categoryFilter,
            'phoneStatusFilter' => $phoneStatusFilter,
            'search' => $search,
            'today' => $today,
        ]);
    }
}

