<?php

namespace App\Http\Controllers;

use App\Models\LeadCustomer;
use App\Models\RepeatOrderPrediction;
use App\Support\PhoneNumberSql;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->buildFilteredQuery($request);

        return view('leads.index', [
            'leads' => $query->latest('assigned_at')->paginate(20)->withQueryString(),
        ]);
    }

    public function download(Request $request): StreamedResponse
    {
        $query = $this->buildFilteredQuery($request);

        $filename = 'leads-customer-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Kode Pelanggan',
                'Nama Pelanggan',
                'No HP Konsumen',
                'Kode Item',
                'Nama Barang',
                'Klasifikasi',
                'Status',
                'Tanggal Masuk Lead',
            ]);

            $query->latest('assigned_at')->chunk(500, function ($leads) use ($output) {
                foreach ($leads as $lead) {
                    fputcsv($output, [
                        $lead->customer_vendor_code,
                        $lead->customer_vendor_name,
                        $lead->no_hp_customer,
                        $lead->item_code,
                        $lead->dscription,
                        ucfirst($lead->classification ?? '-'),
                        $lead->status ?? '-',
                        $lead->assigned_at?->format('d-m-Y H:i') ?? '-',
                    ]);
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildFilteredQuery(Request $request): Builder
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
                ->whereRaw(PhoneNumberSql::numericOnly());
        } elseif ($phoneStatus === 'invalid') {
            $query->whereNotNull('no_hp_customer')
                ->whereRaw("TRIM(no_hp_customer) <> ''")
                ->whereRaw(PhoneNumberSql::invalidWhatsApp());
        } elseif ($phoneStatus === 'missing') {
            $query->where(function ($builder) {
                $builder->whereNull('no_hp_customer')
                    ->orWhereRaw("TRIM(no_hp_customer) = ''");
            });
        }

        return $query;
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
