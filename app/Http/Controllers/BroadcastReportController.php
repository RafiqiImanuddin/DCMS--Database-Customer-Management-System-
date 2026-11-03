<?php

namespace App\Http\Controllers;

use App\Models\BroadcastMedia;
use App\Models\BroadcastReport;
use App\Models\BroadcastTemplate;
use App\Models\RepeatOrderPrediction;
use App\Services\BroadcastService;
use App\Services\FastXlsxWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        $templates = BroadcastTemplate::orderBy('id')->get();

        return view('broadcasts.index', [
            'reports' => $query->latest()->paginate(20)->withQueryString(),
            'templates' => $templates,
        ]);
    }

    public function store(Request $request, RepeatOrderPrediction $prediction, BroadcastService $service): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2500'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'image_url' => ['nullable', 'url', 'max:500'],
        ]);

        $imageUrl = $this->resolveImageUrl($request, $validated['image_url'] ?? null);
        $report = $service->send($prediction, $validated['message'], $request->user()->id, $imageUrl);

        return redirect()->route('broadcasts.index')->with(
            'success',
            'Pesan broadcast ditambahkan dengan status '.$report->status.'.',
        );
    }

    public function bulkStore(Request $request, BroadcastService $service): RedirectResponse
    {
        $validated = $request->validate([
            'prediction_ids' => ['required', 'array', 'min:1'],
            'prediction_ids.*' => ['integer', 'distinct', 'exists:repeat_order_predictions,id'],
            'message' => ['required', 'string', 'max:2500'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'image_url' => ['nullable', 'url', 'max:500'],
        ]);

        $imageUrl = $this->resolveImageUrl($request, $validated['image_url'] ?? null);

        $predictions = RepeatOrderPrediction::whereIn('id', $validated['prediction_ids'])->get();
        foreach ($predictions as $prediction) {
            $service->send($prediction, $validated['message'], $request->user()->id, $imageUrl);
        }

        $redirectRoute = $request->input('redirect_to', 'predictions.index');
        $validRoutes = ['leads.index', 'predictions.index', 'broadcasts.index'];
        if (! in_array($redirectRoute, $validRoutes, true)) {
            $redirectRoute = 'predictions.index';
        }

        return redirect()->route($redirectRoute)->with(
            'success',
            count($predictions).' pelanggan ditambahkan ke antrean/laporan broadcast.'.($imageUrl ? ' (dengan lampiran gambar)' : ''),
        );
    }

    public function download(Request $request): BinaryFileResponse
    {
        $query = BroadcastReport::query()->where('user_id', $request->user()->id);
        if (($status = $request->string('status')->toString()) && in_array($status, ['sent', 'failed', 'logged', 'queued'], true)) {
            $query->where('status', $status);
        }

        $filename = 'laporan-broadcast-'.now()->format('Y-m-d').'.xlsx';

        $headers = ['Kode Pelanggan', 'No HP', 'Channel', 'Status', 'Pesan', 'Lampiran Gambar', 'Waktu', 'Respons Provider'];

        $rowsGenerator = function () use ($query) {
            foreach ($query->latest()->cursor() as $report) {
                yield [
                    $report->customer_vendor_code,
                    $report->phone_number,
                    $report->channel,
                    $report->status,
                    $report->message,
                    $report->image_url ?? '-',
                    $report->sent_at?->format('d-m-Y H:i:s') ?? '-',
                    $report->provider_response,
                ];
            }
        };

        return FastXlsxWriter::download($filename, $headers, $rowsGenerator());
    }

    public function destroy(Request $request, BroadcastReport $report): RedirectResponse
    {
        if ((int) $report->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        $report->delete();

        return redirect()->route('broadcasts.index')->with('success', 'Laporan broadcast berhasil dihapus.');
    }

    private function resolveImageUrl(Request $request, ?string $fallbackUrl): ?string
    {
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $media = BroadcastMedia::create([
                'user_id' => $request->user()->id,
                'filename' => $file->getClientOriginalName() ?: 'broadcast-image.jpg',
                'mime_type' => $file->getMimeType() ?: 'image/jpeg',
                'file_size' => $file->getSize(),
                'data' => base64_encode($file->getContent()),
            ]);

            return route('broadcasts.media.show', ['media' => $media->id], true);
        }

        return $fallbackUrl ?: null;
    }
}
