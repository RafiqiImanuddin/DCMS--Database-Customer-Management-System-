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
        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('customer_vendor_code', 'like', '%'.$search.'%')
                    ->orWhere('customer_vendor_name', 'like', '%'.$search.'%')
                    ->orWhere('item_code', 'like', '%'.$search.'%');
            });
        }
        if ($classification = $request->string('classification')->toString()) {
            if (in_array($classification, ['tinggi', 'sedang', 'monitor', 'baru'], true)) {
                $query->where('classification', $classification);
            }
        }
        if ($period = $request->string('period')->toString()) {
            $query->where('period_label', $period);
        }

        return view('predictions.index', [
            'predictions' => $query->orderBy('predicted_repeat_date')->paginate(20)->withQueryString(),
            'periods' => RepeatOrderPrediction::whereNotNull('period_label')->distinct()->orderByDesc('period_label')->pluck('period_label'),
        ]);
    }
}
