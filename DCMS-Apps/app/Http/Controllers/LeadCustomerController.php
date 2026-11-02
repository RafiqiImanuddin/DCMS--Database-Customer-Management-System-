<?php

namespace App\Http\Controllers;

use App\Models\LeadCustomer;
use App\Models\RepeatOrderPrediction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = LeadCustomer::query()
            ->where('user_id', $request->user()->id)
            ->with('prediction');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($builder) use ($search) {
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

        $phoneStatus = $request->string('phone_status')->toString();
        if (! in_array($phoneStatus, ['valid', 'invalid', 'missing'], true)) {
            $phoneStatus = '';
        }

        if ($phoneStatus === 'valid') {
            $query->whereNotNull('no_hp_customer')
                ->whereRaw("TRIM(no_hp_customer) <> ''")
                ->whereRaw("no_hp_customer LIKE '62%'")
                ->whereRaw('LENGTH(no_hp_customer) BETWEEN 10 AND 15')
                ->whereRaw("no_hp_customer NOT GLOB '*[^0-9]*'");
        } elseif ($phoneStatus === 'invalid') {
            $query->whereNotNull('no_hp_customer')
                ->whereRaw("TRIM(no_hp_customer) <> ''")
                ->whereRaw("NOT (no_hp_customer LIKE '62%' AND LENGTH(no_hp_customer) BETWEEN 10 AND 15 AND no_hp_customer NOT GLOB '*[^0-9]*')");
        } elseif ($phoneStatus === 'missing') {
            $query->where(function ($builder) {
                $builder->whereNull('no_hp_customer')
                    ->orWhereRaw("TRIM(no_hp_customer) = ''");
            });
        }

        return view('leads.index', [
            'leads' => $query->latest('assigned_at')->paginate(20)->withQueryString(),
        ]);
    }

    public function assign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prediction_ids' => ['required', 'array', 'min:1'],
            'prediction_ids.*' => ['required', 'integer', 'distinct', 'exists:repeat_order_predictions,id'],
        ]);

        $assigned = 0;
        $predictions = RepeatOrderPrediction::whereIn('id', $validated['prediction_ids'])->get();

        foreach ($predictions as $prediction) {
            LeadCustomer::query()->updateOrCreate([
                'user_id' => $request->user()->id,
                'repeat_order_prediction_id' => $prediction->id,
            ], [
                'customer_vendor_code' => $prediction->customer_vendor_code,
                'customer_vendor_name' => $prediction->customer_vendor_name,
                'no_hp_customer' => $prediction->no_hp_customer,
                'classification' => $prediction->classification,
                'item_code' => $prediction->item_code,
                'dscription' => $prediction->dscription,
                'status' => 'follow_up',
                'assigned_at' => now(),
            ]);
            $assigned++;
        }

        return redirect()->route('leads.index')->with(
            'success',
            $assigned.' pelanggan berhasil dipindahkan ke Leads Customer.',
        );
    }
}
