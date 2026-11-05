<?php

namespace App\Http\Controllers;

use App\Models\BroadcastBatch;
use App\Models\BroadcastMedia;
use App\Models\BroadcastReport;
use App\Models\BroadcastTemplate;
use App\Models\LeadCustomer;
use App\Models\RepeatOrderPrediction;
use App\Models\Setting;
use App\Services\BroadcastService;
use App\Services\FastXlsxWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BroadcastReportController extends Controller
{
    public function index(Request $request, BroadcastService $broadcastService): View
    {
        $userId = $request->user()->id;
        $tab = $request->query('tab', 'batches');

        // Query Batches
        $batchesQuery = BroadcastBatch::query()
            ->where('user_id', $userId);

        if ($search = trim((string) $request->input('search'))) {
            $batchesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if (($status = $request->string('status')->toString()) && in_array($status, ['completed', 'failed', 'processing', 'partially_failed'], true)) {
            $batchesQuery->where('status', $status);
        }

        $batches = $batchesQuery->latest('sent_at')->paginate(15)->withQueryString();

        // Individual messages (for "Semua Pesan" tab)
        $reportsQuery = BroadcastReport::query()
            ->where('user_id', $userId)
            ->with(['prediction', 'user', 'batch']);

        if (($repStatus = $request->string('status')->toString()) && in_array($repStatus, ['sent', 'failed', 'logged', 'queued'], true)) {
            $reportsQuery->where('status', $repStatus);
        }

        if ($search) {
            $reportsQuery->where(function ($q) use ($search) {
                $q->where('customer_vendor_code', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $reports = $reportsQuery->latest()->paginate(20)->withQueryString();

        // KPI Summary
        $totalBatches = BroadcastBatch::where('user_id', $userId)->count();
        $totalRecipients = (int) BroadcastBatch::where('user_id', $userId)->sum('total_recipients');
        $totalSent = (int) BroadcastBatch::where('user_id', $userId)->sum('total_sent');
        $avgDeliveryRate = $totalRecipients > 0 ? round(($totalSent / $totalRecipients) * 100, 1) : 0;

        $templates = BroadcastTemplate::orderBy('id')->get();

        $safetySettings = [
            'delay_min' => (int) Setting::get('broadcast_delay_min', '5'),
            'delay_max' => (int) Setting::get('broadcast_delay_max', '15'),
            'batch_size' => (int) Setting::get('broadcast_batch_size', '20'),
            'batch_cooldown' => (int) Setting::get('broadcast_batch_cooldown', '30'),
            'spintax_enabled' => Setting::get('broadcast_spintax_enabled', '1') === '1',
            'daily_limit' => (int) Setting::get('broadcast_daily_limit', '250'),
        ];

        $cycleInfo = $broadcastService->getCycleInfo();

        return view('broadcasts.index', [
            'tab' => $tab,
            'batches' => $batches,
            'reports' => $reports,
            'templates' => $templates,
            'totalBatches' => $totalBatches,
            'totalRecipients' => $totalRecipients,
            'totalSent' => $totalSent,
            'avgDeliveryRate' => $avgDeliveryRate,
            'safetySettings' => $safetySettings,
            'cycleInfo' => $cycleInfo,
            'todaySentCount' => $cycleInfo['sent_count'],
        ]);
    }

    public function showBatch(Request $request, BroadcastBatch $batch): View
    {
        if ((int) $batch->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        $reportsQuery = $batch->reports()->with(['prediction', 'user']);
        if (($status = $request->string('status')->toString()) && in_array($status, ['sent', 'failed', 'logged', 'queued'], true)) {
            $reportsQuery->where('status', $status);
        }

        $reports = $reportsQuery->latest()->paginate(25)->withQueryString();

        return view('broadcasts.show', [
            'batch' => $batch,
            'reports' => $reports,
        ]);
    }

    public function downloadBatch(Request $request, BroadcastBatch $batch): BinaryFileResponse
    {
        if ((int) $batch->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        $query = $batch->reports()->latest();
        $filename = 'laporan-batch-'.Str::slug($batch->title).'-'.now()->format('Y-m-d').'.xlsx';
        $headers = ['Kode Pelanggan', 'No HP', 'Channel', 'Status', 'Pesan', 'Lampiran Gambar', 'Waktu'];

        $rowsGenerator = function () use ($query) {
            foreach ($query->cursor() as $report) {
                yield [
                    $report->customer_vendor_code,
                    $report->phone_number,
                    $report->channel,
                    $report->status,
                    $report->message,
                    $report->image_url ?? '-',
                    $report->sent_at?->format('d-m-Y H:i:s') ?? '-',
                ];
            }
        };

        return FastXlsxWriter::download($filename, $headers, $rowsGenerator());
    }

    public function destroyBatch(Request $request, BroadcastBatch $batch): RedirectResponse
    {
        if ((int) $batch->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        $title = $batch->title;
        $batch->reports()->delete();
        $batch->delete();

        return redirect()->route('broadcasts.index')->with(
            'success',
            'Batch broadcast "'.$title.'" beserta seluruh laporannya berhasil dihapus.'
        );
    }

    public function store(Request $request, RepeatOrderPrediction $prediction, BroadcastService $service): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2500'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'image_url' => ['nullable', 'url', 'max:500'],
        ]);

        $cycleInfo = $service->getCycleInfo();
        if ($cycleInfo['remaining_quota'] < 1) {
            $resetNotice = $cycleInfo['reset_at']
                ? ' Kuota akan direset otomatis pada '.$cycleInfo['reset_time_formatted'].' (sisa '.$cycleInfo['remaining_text'].').'
                : ' Kuota broadcast 1x24 jam telah habis.';

            return redirect()->back()->with(
                'error',
                'Batas kuota broadcast ('.$cycleInfo['daily_limit'].' pesan / 1x24 jam) telah tercapai.'.$resetNotice
            );
        }

        $imageUrl = $this->resolveImageUrl($request, $validated['image_url'] ?? null);
        $title = trim((string) ($request->input('title') ?: '')) ?: ('Broadcast - '.($prediction->customer_vendor_name ?: $prediction->customer_vendor_code ?: now()->format('d M Y H:i')));

        $batch = BroadcastBatch::create([
            'user_id' => $request->user()->id,
            'title' => $title,
            'message' => $validated['message'],
            'image_url' => $imageUrl,
            'total_recipients' => 1,
            'total_sent' => 0,
            'total_failed' => 0,
            'delivery_rate' => 0,
            'status' => 'processing',
            'sent_at' => now(),
        ]);

        $report = $service->send($prediction, $validated['message'], $request->user()->id, $imageUrl, $batch->id);
        $isSent = $report->status === 'sent';

        $batch->update([
            'total_sent' => $isSent ? 1 : 0,
            'total_failed' => $isSent ? 0 : 1,
            'delivery_rate' => $isSent ? 100 : 0,
            'status' => $isSent ? 'completed' : 'failed',
        ]);

        LeadCustomer::where('repeat_order_prediction_id', $prediction->id)->delete();

        return redirect()->route('broadcasts.index')->with(
            'success',
            'Pesan broadcast "'.$title.'" ditambahkan dengan status '.$report->status.'.',
        );
    }

    public function bulkStore(Request $request, BroadcastService $service): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'prediction_ids' => ['required', 'array', 'min:1'],
            'prediction_ids.*' => ['integer', 'distinct', 'exists:repeat_order_predictions,id'],
            'message' => ['required', 'string', 'max:2500'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'image_url' => ['nullable', 'url', 'max:500'],
        ]);

        $imageUrl = $this->resolveImageUrl($request, $validated['image_url'] ?? null);
        $title = trim((string) ($request->input('title') ?: '')) ?: ('Broadcast - '.now()->format('d M Y H:i'));

        $predictions = RepeatOrderPrediction::whereIn('id', $validated['prediction_ids'])->get();

        $cycleInfo = $service->getCycleInfo();
        $targetCount = count($predictions);

        if ($cycleInfo['remaining_quota'] < 1) {
            $resetNotice = $cycleInfo['reset_at']
                ? ' Kuota akan direset otomatis pada '.$cycleInfo['reset_time_formatted'].' (sisa '.$cycleInfo['remaining_text'].').'
                : ' Kuota 1x24 jam telah habis.';

            return redirect()->back()->with(
                'error',
                'Batas kuota broadcast ('.$cycleInfo['daily_limit'].' pesan / 1x24 jam) telah tercapai.'.$resetNotice
            );
        }

        if ($targetCount > $cycleInfo['remaining_quota']) {
            $resetNotice = $cycleInfo['reset_at']
                ? ' Kuota akan direset pada '.$cycleInfo['reset_time_formatted'].'.'
                : '';

            return redirect()->back()->with(
                'error',
                "Target penerima ({$targetCount} kontak) melebihi sisa kuota broadcast ({$cycleInfo['remaining_quota']} dari batas {$cycleInfo['daily_limit']} per 24 jam). Silakan sesuaikan jumlah target atau tunggu reset kuota.{$resetNotice}"
            );
        }

        $batch = BroadcastBatch::create([
            'user_id' => $request->user()->id,
            'title' => $title,
            'message' => $validated['message'],
            'image_url' => $imageUrl,
            'total_recipients' => count($predictions),
            'total_sent' => 0,
            'total_failed' => 0,
            'delivery_rate' => 0,
            'status' => 'processing',
            'sent_at' => now(),
        ]);

        @set_time_limit(0);

        $delayMin = (int) Setting::get('broadcast_delay_min', '5');
        $delayMax = (int) Setting::get('broadcast_delay_max', '15');
        $batchSize = (int) Setting::get('broadcast_batch_size', '20');
        $batchCooldown = (int) Setting::get('broadcast_batch_cooldown', '30');

        if ($delayMin < 1) $delayMin = 1;
        if ($delayMax < $delayMin) $delayMax = $delayMin;

        $sentCount = 0;
        $failedCount = 0;
        $processedInBatch = 0;
        $totalItems = count($predictions);

        foreach ($predictions as $index => $prediction) {
            $report = $service->send($prediction, $validated['message'], $request->user()->id, $imageUrl, $batch->id);
            if ($report->status === 'sent') {
                $sentCount++;
            } else {
                $failedCount++;
            }

            $processedInBatch++;

            // Proteksi Anti-Banned & Anti-Suspend: Jeda acak antar pesan dan jeda batch
            if ($index < $totalItems - 1) {
                if ($batchSize > 0 && $processedInBatch >= $batchSize && $batchCooldown > 0) {
                    $processedInBatch = 0;
                    if (! app()->runningUnitTests()) {
                        sleep($batchCooldown);
                    }
                } else {
                    $jitter = rand($delayMin, $delayMax);
                    if (! app()->runningUnitTests()) {
                        sleep($jitter);
                    }
                }
            }
        }

        $total = count($predictions);
        $deliveryRate = $total > 0 ? round(($sentCount / $total) * 100, 1) : 0;
        $batchStatus = ($failedCount === 0) ? 'completed' : (($sentCount === 0) ? 'failed' : 'completed');

        $batch->update([
            'total_sent' => $sentCount,
            'total_failed' => $failedCount,
            'delivery_rate' => $deliveryRate,
            'status' => $batchStatus,
        ]);

        LeadCustomer::whereIn('repeat_order_prediction_id', $validated['prediction_ids'])->delete();

        $redirectRoute = $request->input('redirect_to', 'predictions.index');
        $validRoutes = ['leads.index', 'predictions.index', 'broadcasts.index'];
        if (! in_array($redirectRoute, $validRoutes, true)) {
            $redirectRoute = 'predictions.index';
        }

        $successMessage = $redirectRoute === 'leads.index'
            ? count($predictions).' pelanggan berhasil dibroadcast, dihapus dari list Leads, dan tersimpan di Laporan Broadcast.'
            : count($predictions).' pelanggan ditambahkan ke antrean/laporan broadcast.'.($imageUrl ? ' (dengan lampiran gambar)' : '');

        return redirect()->route($redirectRoute)->with('success', $successMessage);
    }

    public function download(Request $request): BinaryFileResponse
    {
        $query = BroadcastReport::query()->where('user_id', $request->user()->id);
        if (($status = $request->string('status')->toString()) && in_array($status, ['sent', 'failed', 'logged', 'queued'], true)) {
            $query->where('status', $status);
        }

        $filename = 'laporan-broadcast-'.now()->format('Y-m-d').'.xlsx';

        $headers = ['Kode Pelanggan', 'No HP', 'Channel', 'Status', 'Pesan', 'Lampiran Gambar', 'Waktu'];

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
                ];
            }
        };

        return FastXlsxWriter::download($filename, $headers, $rowsGenerator());
    }

    public function destroy(Request $request, BroadcastReport $report): RedirectResponse
    {
        if ((int) $report->user_id !== (int) $request->user()->id && ! $request->user()->isSuperAdmin()) {
            abort(404);
        }

        $report->delete();

        return redirect()->route('broadcasts.index')->with('success', 'Laporan broadcast berhasil dihapus.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delay_min' => ['required', 'integer', 'min:1', 'max:120'],
            'delay_max' => ['required', 'integer', 'min:1', 'max:300'],
            'batch_size' => ['required', 'integer', 'min:5', 'max:500'],
            'batch_cooldown' => ['required', 'integer', 'min:0', 'max:600'],
            'spintax_enabled' => ['nullable'],
            'daily_limit' => ['required', 'integer', 'min:10', 'max:5000'],
        ]);

        $min = (int) $validated['delay_min'];
        $max = (int) $validated['delay_max'];
        if ($min > $max) {
            $max = $min;
        }

        Setting::set('broadcast_delay_min', (string) $min);
        Setting::set('broadcast_delay_max', (string) $max);
        Setting::set('broadcast_batch_size', (string) $validated['batch_size']);
        Setting::set('broadcast_batch_cooldown', (string) $validated['batch_cooldown']);
        Setting::set('broadcast_spintax_enabled', $request->has('spintax_enabled') ? '1' : '0');
        Setting::set('broadcast_daily_limit', (string) $validated['daily_limit']);

        return redirect()->back()->with('success', 'Pengaturan Delay & Anti-Banned WhatsApp Broadcast berhasil disimpan!');
    }

    public function resetCycle(BroadcastService $service): RedirectResponse
    {
        $service->resetCycle();

        return redirect()->route('broadcasts.index')->with(
            'success',
            'Siklus kuota broadcast 1x24 jam berhasil di-reset ulang. Kuota diatur ulang ke 0 dan siap digunakan.'
        );
    }

    private function resolveImageUrl(Request $request, ?string $fallbackUrl): ?string
    {
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $originalName = $file->getClientOriginalName() ?: 'broadcast-image.jpg';
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg');
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $ext = 'jpg';
            }
            $safeName = (pathinfo($originalName, PATHINFO_FILENAME) ?: 'broadcast-image').'.'.$ext;

            $media = BroadcastMedia::create([
                'user_id' => $request->user()->id,
                'filename' => $safeName,
                'mime_type' => $file->getMimeType() ?: 'image/jpeg',
                'file_size' => $file->getSize(),
                'data' => base64_encode($file->getContent()),
            ]);

            return route('broadcasts.media.show', [
                'media' => $media->id,
                'filename' => $safeName,
            ], true);
        }

        return $fallbackUrl ?: null;
    }
}
