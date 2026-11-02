<?php

namespace App\Http\Controllers;

use App\Models\BroadcastReport;
use App\Models\RepeatOrderPrediction;
use App\Services\BroadcastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BroadcastReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = BroadcastReport::query()
            ->where('user_id', $request->user()->id)
            ->with(['prediction', 'user']);
        if (($status = $request->string('status')->toString()) && in_array($status, ['sent', 'failed', 'logged', 'queued'], true)) {
            $query->where('status', $status);
        }

        return view('broadcasts.index', [
            'reports' => $query->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function store(Request $request, RepeatOrderPrediction $prediction, BroadcastService $service): RedirectResponse
    {
        $validated = $request->validate(['message' => ['required', 'string', 'max:2000']]);
        $report = $service->send($prediction, $validated['message'], $request->user()->id);

        return redirect()->route('broadcasts.index')->with(
            'success',
            'Pesan ditambahkan ke laporan broadcast dengan status '.$report->status.'.',
        );
    }

    public function bulkStore(Request $request, BroadcastService $service): RedirectResponse
    {
        $validated = $request->validate([
            'prediction_ids' => ['required', 'array', 'min:1'],
            'prediction_ids.*' => ['integer', 'distinct', 'exists:repeat_order_predictions,id'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $predictions = RepeatOrderPrediction::whereIn('id', $validated['prediction_ids'])->get();
        foreach ($predictions as $prediction) {
            $service->send($prediction, $validated['message'], $request->user()->id);
        }

        return redirect()->route('predictions.index')->with(
            'success',
            count($predictions).' pelanggan ditambahkan ke antrean/laporan broadcast.',
        );
    }

    public function download(Request $request): StreamedResponse
    {
        $query = BroadcastReport::query()->where('user_id', $request->user()->id);
        if (($status = $request->string('status')->toString()) && in_array($status, ['sent', 'failed', 'logged', 'queued'], true)) {
            $query->where('status', $status);
        }

        $filename = 'laporan-broadcast-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Kode Pelanggan', 'No HP', 'Channel', 'Status', 'Pesan', 'Waktu', 'Respons Provider']);

            $query->latest()->chunk(500, function ($reports) use ($output) {
                foreach ($reports as $report) {
                    fputcsv($output, [
                        $this->csvSafe($report->customer_vendor_code),
                        $this->csvSafe($report->phone_number),
                        $this->csvSafe($report->channel),
                        $this->csvSafe($report->status),
                        $this->csvSafe($report->message),
                        $report->sent_at?->format('d-m-Y H:i:s') ?? '',
                        $this->csvSafe($report->provider_response),
                    ]);
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function destroy(Request $request, BroadcastReport $report): RedirectResponse
    {
        if ((int) $report->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        $report->delete();

        return redirect()->route('broadcasts.index')->with('success', 'Laporan broadcast berhasil dihapus.');
    }

    private function csvSafe(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
}
