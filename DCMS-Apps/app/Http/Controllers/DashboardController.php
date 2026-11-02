<?php

namespace App\Http\Controllers;

use App\Models\BroadcastReport;
use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\RepeatOrderPrediction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $periods = CrossSelling::query()
            ->whereNotNull('period_label')
            ->selectRaw('period_label, COUNT(*) as transaction_count, COUNT(DISTINCT customer_vendor_code) as customer_count, SUM(harga_jual) as total_value')
            ->groupBy('period_label')
            ->orderByDesc('period_label')
            ->get();

        return view('dashboard.index', [
            'totalTransactions' => CrossSelling::count(),
            'totalCustomers' => RepeatOrderPrediction::distinct('customer_vendor_code')->count('customer_vendor_code'),
            'totalPredictions' => RepeatOrderPrediction::count(),
            'highPotentials' => RepeatOrderPrediction::where('classification', 'tinggi')->count(),
            'broadcastSent' => BroadcastReport::where('status', 'sent')->count(),
            'latestImports' => CrossSellingImport::latest()->take(5)->get(),
            'topPredictions' => RepeatOrderPrediction::whereNotNull('predicted_repeat_date')->orderBy('predicted_repeat_date')->take(10)->get(),
            'periods' => $periods,
        ]);
    }
}
