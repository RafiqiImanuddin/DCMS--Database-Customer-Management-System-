<?php

namespace App\Http\Controllers;

use App\Models\BroadcastTemplate;
use App\Models\LeadCustomer;
use App\Models\RepeatOrderPrediction;
use App\Services\FastXlsxWriter;
use App\Support\PhoneNumberSql;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LeadCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->buildFilteredQuery($request);

        return view('leads.index', [
            'leads' => $query->latest('assigned_at')->paginate(20)->withQueryString(),
            'templates' => BroadcastTemplate::orderBy('id')->get(),
        ]);
    }

    public function download(Request $request): BinaryFileResponse
    {
        $query = $this->buildFilteredQuery($request);

        $perBatch = 25000;
        $batch = max(1, (int) $request->integer('batch', 1));
        $offset = ($batch - 1) * $perBatch;

        $totalFiltered = (clone $query)->count();
        $query->latest('assigned_at')->skip($offset)->take($perBatch);

        $filename = 'leads-customer';
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
            'Status',
            'Tanggal Masuk Lead',
        ];

        $rowsGenerator = function () use ($query) {
            foreach ($query->cursor() as $lead) {
                yield [
                    $lead->customer_vendor_code,
                    $lead->customer_vendor_name,
                    $lead->no_hp_customer,
                    $lead->item_code,
                    $lead->category,
                    $lead->dscription,
                    ucfirst($lead->classification ?? '-'),
                    $lead->status ?? '-',
                    $lead->assigned_at?->format('d-m-Y H:i') ?? '-',
                ];
            }
        };

        return FastXlsxWriter::download($filename, $headers, $rowsGenerator());
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

    public function destroy(Request $request, LeadCustomer $lead): RedirectResponse
    {
        if ((int) $lead->user_id !== (int) $request->user()->id && ! $request->user()->isSuperAdmin()) {
            abort(403);
        }

        $customerName = $lead->customer_vendor_name ?: ($lead->customer_vendor_code ?: 'Lead');
        $lead->delete();

        return redirect()->route('leads.index')->with(
            'success',
            'Lead "'.$customerName.'" berhasil dihapus dan dikembalikan ke Master Customer.'
        );
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lead_ids' => ['required_without:prediction_ids', 'array'],
            'lead_ids.*' => ['integer', 'exists:lead_customers,id'],
            'prediction_ids' => ['required_without:lead_ids', 'array'],
            'prediction_ids.*' => ['integer'],
        ]);

        $query = LeadCustomer::query();

        if (! $request->user()->isSuperAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        if (! empty($validated['lead_ids'])) {
            $query->whereIn('id', $validated['lead_ids']);
        } elseif (! empty($validated['prediction_ids'])) {
            $query->whereIn('repeat_order_prediction_id', $validated['prediction_ids']);
        }

        $count = $query->delete();

        return redirect()->route('leads.index')->with(
            'success',
            $count.' lead berhasil dihapus dan dikembalikan ke Master Customer.'
        );
    }
}
