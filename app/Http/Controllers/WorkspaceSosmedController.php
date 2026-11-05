<?php

namespace App\Http\Controllers;

use App\Models\BroadcastMedia;
use App\Models\Setting;
use App\Services\WatzapService;
use App\Support\WorkspaceUrlCipher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class WorkspaceSosmedController extends Controller
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = public_path('workspace-sosmed');
    }

    public function index(Request $request)
    {
        // Enkripsi routing: Jika diakses secara langsung bukan via gateway encrypted route dengan angka raw ?tab=9, otomatis redirect ke URL terenkripsi
        if (! $request->routeIs('encrypted.route')) {
            $rawTab = $request->query('tab');
            if ($rawTab !== null && is_numeric($rawTab) && (int) $rawTab >= 1 && (int) $rawTab <= 9) {
                return redirect()->to(\App\Support\RouteCipher::url('workspace-sosmed.index', [
                    'tab' => (int) $rawTab,
                ]));
            }
        }

        $activeTab = \App\Support\RouteCipher::resolveWorkspaceTab($request) 
            ?? WorkspaceUrlCipher::resolveTab($request);

        return view('workspace-sosmed.index', [
            'activeTab' => $activeTab,
            'tabTokens' => WorkspaceUrlCipher::allTokens(),
            'encryptedTabUrls' => \App\Support\RouteCipher::allWorkspaceUrls(),
        ]);
    }

    public function databaseStatus(): JsonResponse
    {
        $check = function (string $filename) {
            $path = $this->storagePath . DIRECTORY_SEPARATOR . $filename;
            if (File::exists($path)) {
                return [
                    'exists' => true,
                    'size' => File::size($path),
                    'mtimeMs' => File::lastModified($path) * 1000,
                    'mtime' => date('c', File::lastModified($path)),
                ];
            }
            return ['exists' => false, 'mtimeMs' => 0];
        };

        $turnoverStatus = $check('turnover.xlsx');
        $dbTurnoverMtime = (int) (Setting::where('key', 'workspace_turnover_mtime')->value('value') ?? 0);
        $dbTurnoverUploader = Setting::where('key', 'workspace_turnover_uploader')->value('value') ?? 'Superadmin';
        $dbTurnoverFilesize = (int) (Setting::where('key', 'workspace_turnover_filesize')->value('value') ?? 0);

        if ($dbTurnoverMtime > ($turnoverStatus['mtimeMs'] ?? 0)) {
            $turnoverStatus['exists'] = true;
            $turnoverStatus['mtimeMs'] = $dbTurnoverMtime;
            $turnoverStatus['mtime'] = date('c', (int) ($dbTurnoverMtime / 1000));
            if ($dbTurnoverFilesize > 0) {
                $turnoverStatus['size'] = $dbTurnoverFilesize;
            }
        }
        $turnoverStatus['uploader'] = $dbTurnoverUploader;

        $crossStatus = $check('CROSSELLING-12102026.xlsx');
        $dbCrossMtime = (int) (Setting::where('key', 'workspace_croselling_mtime')->value('value') ?? 0);
        $dbCrossUploader = Setting::where('key', 'workspace_croselling_uploader')->value('value') ?? 'Superadmin';
        $dbCrossFilesize = (int) (Setting::where('key', 'workspace_croselling_filesize')->value('value') ?? 0);

        if ($dbCrossMtime > ($crossStatus['mtimeMs'] ?? 0)) {
            $crossStatus['exists'] = true;
            $crossStatus['mtimeMs'] = $dbCrossMtime;
            $crossStatus['mtime'] = date('c', (int) ($dbCrossMtime / 1000));
            if ($dbCrossFilesize > 0) {
                $crossStatus['size'] = $dbCrossFilesize;
            }
        }
        $crossStatus['uploader'] = $dbCrossUploader;

        return response()->json([
            'turnover' => $turnoverStatus,
            'croselling' => $crossStatus,
            'keep' => $check('keep-data.csv'),
            'archives' => $check('monthly_archives.json'),
            'targets' => $check('monthly_targets.json'),
            'photos' => $check('sales_photos.json'),
            'serverTime' => now()->toIso8601String(),
        ]);
    }

    public function monthlyTargets(Request $request): JsonResponse
    {
        $file = $this->storagePath . DIRECTORY_SEPARATOR . 'monthly_targets.json';

        if ($request->isMethod('get')) {
            $dbVal = Setting::where('key', 'workspace_monthly_targets')->value('value');
            if (!empty($dbVal)) {
                $decoded = json_decode($dbVal, true);
                if (is_array($decoded)) {
                    return response()->json($decoded);
                }
            }
            if (File::exists($file)) {
                $content = File::get($file);
                $decoded = json_decode($content, true) ?? [];
                if (!empty($decoded)) {
                    Setting::updateOrCreate(['key' => 'workspace_monthly_targets'], ['value' => $content]);
                }
                return response()->json($decoded);
            }
            return response()->json([]);
        }

        $payload = $request->all();
        if (!empty($payload)) {
            $json = json_encode($payload, JSON_PRETTY_PRINT);
            @File::put($file, $json);
            Setting::updateOrCreate(['key' => 'workspace_monthly_targets'], ['value' => $json]);
            return response()->json(['success' => true, 'message' => 'Monthly targets berhasil disimpan!']);
        }

        return response()->json(['error' => 'Data targets kosong.'], 400);
    }

    public function salesPhotos(Request $request): JsonResponse
    {
        $file = $this->storagePath . DIRECTORY_SEPARATOR . 'sales_photos.json';

        if ($request->isMethod('get')) {
            $dbVal = Setting::where('key', 'workspace_sales_photos')->value('value');
            if (!empty($dbVal)) {
                $decoded = json_decode($dbVal, true);
                if (is_array($decoded)) {
                    return response()->json($decoded);
                }
            }
            if (File::exists($file)) {
                $content = File::get($file);
                $decoded = json_decode($content, true) ?? [];
                if (!empty($decoded)) {
                    Setting::updateOrCreate(['key' => 'workspace_sales_photos'], ['value' => $content]);
                }
                return response()->json($decoded);
            }
            return response()->json([]);
        }

        $payload = $request->all();
        if (is_array($payload)) {
            $json = json_encode($payload, JSON_PRETTY_PRINT);
            @File::put($file, $json);
            Setting::updateOrCreate(['key' => 'workspace_sales_photos'], ['value' => $json]);
            return response()->json(['success' => true, 'message' => 'Foto sales berhasil disimpan!']);
        }

        return response()->json(['error' => 'Data kosong.'], 400);
    }

    public function monthlyArchives(Request $request): JsonResponse
    {
        $file = $this->storagePath . DIRECTORY_SEPARATOR . 'monthly_archives.json';

        if ($request->isMethod('get')) {
            $dbVal = Setting::where('key', 'workspace_monthly_archives')->value('value');
            if (!empty($dbVal)) {
                $decoded = json_decode($dbVal, true);
                if (is_array($decoded)) {
                    return response()->json($decoded);
                }
            }
            if (File::exists($file)) {
                $content = File::get($file);
                $decoded = json_decode($content, true) ?? [];
                if (!empty($decoded)) {
                    Setting::updateOrCreate(['key' => 'workspace_monthly_archives'], ['value' => $content]);
                }
                return response()->json($decoded);
            }
            return response()->json([]);
        }

        $payload = $request->all();
        if (is_array($payload)) {
            $json = json_encode($payload, JSON_PRETTY_PRINT);
            @File::put($file, $json);
            Setting::updateOrCreate(['key' => 'workspace_monthly_archives'], ['value' => $json]);
            return response()->json(['success' => true, 'message' => 'Arsip bulanan berhasil disimpan!']);
        }

        return response()->json(['error' => 'Data kosong.'], 400);
    }

    public function syncKeep(): JsonResponse
    {
        $url = 'https://docs.google.com/spreadsheets/d/1V0pIa0NZCprJqRwnSnikFaLZ-dd50nY0uE3bjVYsUe0/export?format=csv&gid=1014933310';

        try {
            $res = Http::timeout(20)->withoutVerifying()->get($url);
            if ($res->successful()) {
                $csv = $res->body();
                $localFile = $this->storagePath . DIRECTORY_SEPARATOR . 'keep-data.csv';
                @File::put($localFile, $csv);

                return response()->json([
                    'success' => true,
                    'message' => 'Data keep berhasil disinkronkan langsung dari Google Sheets!',
                    'csvText' => $csv,
                    'rowsSample' => substr($csv, 0, 150),
                ]);
            }

            return response()->json(['error' => 'Gagal mengambil data dari Google Sheets. HTTP: ' . $res->status()], 502);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateKeepStatus(Request $request): JsonResponse
    {
        $nomorWA = $request->input('nomorWA', '');
        $status = $request->input('status', '');
        $feedback = $request->input('feedback', '');

        if (!$nomorWA) {
            return response()->json(['error' => 'Nomor WA wajib diisi.'], 400);
        }

        $appsScriptUrl = 'https://script.google.com/macros/s/AKfycbzdEByURvrvCNAJ-TlaxlU5GoNvmClgxynK_vYrORzXiTXFoYArwRMCJzdW2LWvT5wfUw/exec';

        try {
            Http::timeout(10)->withoutVerifying()->get($appsScriptUrl, [
                'nomorWA' => $nomorWA,
                'status' => $status,
                'feedback' => $feedback,
            ]);
        } catch (\Throwable $e) {
            Log::warning('WorkspaceSosmed updateKeepStatus Google Apps Script warning: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Status keep berhasil diperbarui!',
            'nomorWA' => $nomorWA,
            'status' => $status,
        ]);
    }

    public function updateReportAr(Request $request): JsonResponse
    {
        $nomorWA = $request->input('nomorWA', '');
        $arDoc = $request->input('arDoc', '');

        if (!$nomorWA) {
            return response()->json(['error' => 'Nomor WA wajib diisi.'], 400);
        }

        $appsScriptUrl = 'https://script.google.com/macros/s/AKfycbzdEByURvrvCNAJ-TlaxlU5GoNvmClgxynK_vYrORzXiTXFoYArwRMCJzdW2LWvT5wfUw/exec';

        try {
            Http::timeout(10)->withoutVerifying()->get($appsScriptUrl, [
                'nomorWA' => $nomorWA,
                'arDoc' => $arDoc,
            ]);
        } catch (\Throwable $e) {
            Log::warning('WorkspaceSosmed updateReportAr Google Apps Script warning: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen AR berhasil diperbarui!',
            'nomorWA' => $nomorWA,
            'arDoc' => $arDoc,
        ]);
    }

    public function chatAi(Request $request): JsonResponse
    {
        $prompt = $request->input('prompt', '');
        $systemInstruction = $request->input('systemInstruction', '');
        $apiKey = $request->input('apiKey') ?: env('GEMINI_API_KEY', '');
        $requestedModel = $request->input('model', 'gemini-2.5-flash');

        if (!$prompt) {
            return response()->json(['success' => false, 'error' => 'Prompt is required'], 400);
        }

        $models = array_unique(array_filter([
            $requestedModel,
            'gemini-2.5-flash',
            'gemini-1.5-flash',
            'gemini-2.0-flash',
        ]));

        $fullPrompt = $systemInstruction ? "{$systemInstruction}\n\nPertanyaan User: \"{$prompt}\"" : $prompt;

        foreach ($models as $m) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$m}:generateContent?key={$apiKey}";
                $res = Http::timeout(15)->post($url, [
                    'contents' => [
                        ['parts' => [['text' => $fullPrompt]]]
                    ]
                ]);

                if ($res->successful()) {
                    $json = $res->json();
                    $parts = $json['candidates'][0]['content']['parts'] ?? [];
                    $text = collect($parts)->pluck('text')->implode('');

                    return response()->json([
                        'success' => true,
                        'modelUsed' => $m,
                        'text' => $text,
                    ]);
                }
            } catch (\Throwable $e) {
                // Try next model
            }
        }

        return response()->json(['success' => false, 'error' => 'Gagal memproses dengan Gemini AI model.'], 500);
    }

    public function watzapSend(Request $request, WatzapService $watzapService): JsonResponse
    {
        $rawPhone = $request->input('phone') ?: $request->input('phone_no');
        $message = trim((string) $request->input('message', ''));

        $uploadedFile = $request->file('file') ?: $request->file('media');
        $mediaUrl = $request->input('media_url') ?: $request->input('file_url');
        $mediaType = $request->input('media_type');
        $originalName = null;
        $fileSize = null;

        if (! $rawPhone) {
            return response()->json(['success' => false, 'message' => 'Nomor telepon tujuan wajib diisi!'], 400);
        }

        if (! $message && ! $uploadedFile && ! $mediaUrl) {
            return response()->json(['success' => false, 'message' => 'Pesan atau file lampiran wajib diisi!'], 400);
        }

        if ($uploadedFile) {
            $originalName = $uploadedFile->getClientOriginalName() ?: ('file_' . time());
            $mimeType = $uploadedFile->getMimeType() ?: 'application/octet-stream';
            $fileSize = $uploadedFile->getSize();
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            $isImage = str_starts_with($mimeType, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
            $mediaType = $isImage ? 'image' : 'document';

            $media = BroadcastMedia::create([
                'user_id' => $request->user()?->id,
                'filename' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'data' => base64_encode($uploadedFile->getContent()),
            ]);

            $mediaUrl = route('broadcasts.media.show', [
                'media' => $media->id,
                'filename' => $originalName,
            ], true);
        }

        if (! $mediaType && $mediaUrl) {
            $pathExt = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $mediaType = in_array($pathExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? 'image' : 'document';
        }

        // Kirim via WatzapService (channel inbox follow-up)
        if ($mediaUrl) {
            if ($mediaType === 'image') {
                $result = $watzapService->sendImage($rawPhone, $mediaUrl, $message ?: null, 'inbox');
            } else {
                $result = $watzapService->sendFile($rawPhone, $mediaUrl, $message ?: null, 'inbox');
            }
        } else {
            $result = $watzapService->sendMessage($rawPhone, $message, 'inbox');
        }

        // Catat ke pesan keluar lokal
        $entry = [
            'messageId' => 'out_' . uniqid(),
            'phone' => $rawPhone,
            'message' => $message,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
            'filename' => $originalName,
            'file_size' => $fileSize,
            'timestamp' => now()->toIso8601String(),
            'status' => $result['success'] ? 'sent' : 'failed',
            'direction' => 'outbound',
            'sender' => 'ADMIN',
        ];

        try {
            $msgFile = $this->storagePath . DIRECTORY_SEPARATOR . 'watzap_messages.json';
            $messages = File::exists($msgFile) ? json_decode(File::get($msgFile), true) ?? [] : [];
            $messages[] = $entry;
            $trimmed = array_slice($messages, -1000);
            @File::put($msgFile, json_encode($trimmed, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => $result['success'],
            'status' => $result['status'] ?? '500',
            'message' => $result['message'] ?? 'Proses kirim selesai',
            'entry' => $entry,
            'raw' => $result['raw'] ?? [],
        ]);
    }

    public function watzapStatus(WatzapService $watzapService): JsonResponse
    {
        $status = $watzapService->getDeviceStatus('inbox');
        return response()->json($status);
    }

    public function watzapWebhook(Request $request): JsonResponse
    {
        $msgFile = $this->storagePath . DIRECTORY_SEPARATOR . 'watzap_messages.json';
        $messages = File::exists($msgFile) ? json_decode(File::get($msgFile), true) ?? [] : [];

        // Mode GET: Polling dari frontend untuk mengambil chat masuk
        if ($request->isMethod('get')) {
            $since = $request->query('since');
            $phone = $request->query('phone');
            $inboundOnly = $request->boolean('inbound_only', true);

            $filtered = array_values(array_filter($messages, function ($m) use ($since, $phone, $inboundOnly) {
                if ($inboundOnly && ($m['direction'] ?? '') !== 'inbound') {
                    return false;
                }
                if ($phone && isset($m['phone'])) {
                    $p1 = preg_replace('/\D/', '', (string) $m['phone']);
                    $p2 = preg_replace('/\D/', '', (string) $phone);
                    if ($p1 && $p2 && ! str_contains($p1, $p2) && ! str_contains($p2, $p1)) {
                        return false;
                    }
                }
                if ($since) {
                    $mTime = isset($m['timestamp']) ? strtotime($m['timestamp']) : 0;
                    $sTime = is_numeric($since) ? (int) ($since > 1e11 ? $since / 1000 : $since) : strtotime($since);
                    if ($mTime <= $sTime) {
                        return false;
                    }
                }
                return true;
            }));

            return response()->json([
                'success' => true,
                'messages' => $filtered,
                'total' => count($filtered),
            ]);
        }

        // Mode POST: Webhook masuk dari WatZap API atau gateway
        $data = $request->all();
        $payload = (isset($data['data']) && is_array($data['data'])) ? $data['data'] : $data;

        $phone = $payload['phone_no'] ?? $payload['sender_number'] ?? $payload['phone'] ?? $payload['from'] ?? $payload['wa_number'] ?? $data['phone_no'] ?? $data['sender_number'] ?? $data['phone'] ?? '';
        $message = $payload['message'] ?? $payload['text'] ?? $payload['caption'] ?? $payload['body'] ?? $data['message'] ?? $data['text'] ?? '';
        $mediaUrl = $payload['media_url'] ?? $payload['url'] ?? $payload['image_url'] ?? $payload['file_url'] ?? $data['media_url'] ?? $data['url'] ?? null;
        $mediaType = $payload['media_type'] ?? $payload['type'] ?? $data['media_type'] ?? null;
        $filename = $payload['filename'] ?? $payload['file_name'] ?? $data['filename'] ?? null;
        $fileSize = $payload['file_size'] ?? $data['file_size'] ?? null;
        $senderName = $payload['sender_name'] ?? $payload['name'] ?? $payload['pushname'] ?? $data['sender_name'] ?? $data['name'] ?? null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName() ?: ('file_' . time());
            $mimeType = $file->getMimeType() ?: 'application/octet-stream';
            $fileSize = $file->getSize();
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $isImage = str_starts_with($mimeType, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
            $mediaType = $isImage ? 'image' : 'document';

            $media = BroadcastMedia::create([
                'user_id' => $request->user()?->id,
                'filename' => $filename,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'data' => base64_encode($file->getContent()),
            ]);

            $mediaUrl = route('broadcasts.media.show', [
                'media' => $media->id,
                'filename' => $filename,
            ], true);
        }

        if (! $mediaType && $mediaUrl) {
            $pathExt = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $mediaType = in_array($pathExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? 'image' : 'document';
        }

        $entry = [
            'messageId' => $data['message_id'] ?? ('in_' . uniqid()),
            'phone' => $phone,
            'message' => $message,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
            'filename' => $filename,
            'file_size' => $fileSize,
            'timestamp' => now()->toIso8601String(),
            'status' => 'received',
            'direction' => 'inbound',
            'sender' => 'PIC',
            'senderName' => $senderName,
            'raw' => $data,
        ];

        try {
            $messages[] = $entry;
            $trimmed = array_slice($messages, -1000);
            @File::put($msgFile, json_encode($trimmed, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => '200',
            'message' => 'Webhook received',
            'entry' => $entry,
        ]);
    }

    public function watzapSimulateReceive(Request $request): JsonResponse
    {
        $phone = $request->input('phone') ?: '6281234567890';
        $message = trim((string) $request->input('message', ''));
        $senderName = $request->input('sender_name') ?: 'PIC FU Gadgetmart';
        $mediaType = $request->input('media_type');
        $mediaUrl = $request->input('media_url');
        $filename = $request->input('filename');
        $fileSize = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName() ?: ('sim_' . time());
            $mimeType = $file->getMimeType() ?: 'application/octet-stream';
            $fileSize = $file->getSize();
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $isImage = str_starts_with($mimeType, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
            $mediaType = $isImage ? 'image' : 'document';

            $media = BroadcastMedia::create([
                'user_id' => $request->user()?->id,
                'filename' => $filename,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'data' => base64_encode($file->getContent()),
            ]);

            $mediaUrl = route('broadcasts.media.show', [
                'media' => $media->id,
                'filename' => $filename,
            ], true);
        }

        if (! $message && ! $mediaUrl) {
            $message = 'Halo Admin, ini chat simulasi konfirmasi follow-up customer.';
        }

        $entry = [
            'messageId' => 'sim_in_' . uniqid(),
            'phone' => $phone,
            'message' => $message,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
            'filename' => $filename,
            'file_size' => $fileSize,
            'timestamp' => now()->toIso8601String(),
            'status' => 'received',
            'direction' => 'inbound',
            'sender' => 'PIC',
            'senderName' => $senderName,
        ];

        try {
            $msgFile = $this->storagePath . DIRECTORY_SEPARATOR . 'watzap_messages.json';
            $messages = File::exists($msgFile) ? json_decode(File::get($msgFile), true) ?? [] : [];
            $messages[] = $entry;
            $trimmed = array_slice($messages, -1000);
            @File::put($msgFile, json_encode($trimmed, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Simulasi pesan WhatsApp masuk berhasil dibuat!',
            'entry' => $entry,
        ]);
    }

    public function getExcel(Request $request): BinaryFileResponse|Response|JsonResponse
    {
        $fileType = $request->query('type', 'turnover');
        $targetFileName = $fileType === 'croselling' ? 'CROSSELLING-12102026.xlsx' : 'turnover.xlsx';
        $path = $this->storagePath . DIRECTORY_SEPARATOR . $targetFileName;

        $prefix = $fileType === 'croselling' ? 'workspace_croselling' : 'workspace_turnover';

        if ($request->isMethod('delete')) {
            $user = $request->user();
            if (! $user || ! $user->isSuperAdmin()) {
                return response()->json(['error' => 'Akses ditolak: Hanya Superadmin yang dapat mereset data.'], 403);
            }
            Setting::whereIn('key', [
                "{$prefix}_mtime",
                "{$prefix}_uploader",
                "{$prefix}_file_base64",
                "{$prefix}_filesize",
                "{$prefix}_filename",
            ])->delete();
            return response()->json(['success' => true, 'message' => "File {$fileType} di-reset ke default."]);
        }

        // Check if database has newer custom file
        $dbMtime = (int) (Setting::where('key', "{$prefix}_mtime")->value('value') ?? 0);
        $diskMtime = File::exists($path) ? File::lastModified($path) * 1000 : 0;
        $uploader = Setting::where('key', "{$prefix}_uploader")->value('value') ?? 'Superadmin';

        if ($dbMtime > $diskMtime) {
            $base64 = Setting::where('key', "{$prefix}_file_base64")->value('value');
            if (! empty($base64)) {
                $binary = base64_decode($base64);
                return response($binary, 200, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$targetFileName}\"",
                    'X-Custom-File' => 'true',
                    'X-File-Timestamp' => date('c', (int) ($dbMtime / 1000)),
                    'X-File-Uploader' => $uploader,
                ]);
            }
        }

        if (File::exists($path)) {
            return response()->download($path, $targetFileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'X-Custom-File' => 'true',
                'X-File-Timestamp' => date('c', File::lastModified($path)),
                'X-File-Uploader' => $uploader,
            ]);
        }

        return response()->json(['error' => 'File tidak ditemukan'], 404);
    }

    public function saveExcel(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isSuperAdmin()) {
            return response()->json([
                'error' => 'Akses ditolak: Hanya pengguna dengan akses Superadmin yang dapat memperbarui database Cross Selling.',
            ], 403);
        }

        $fileType = $request->query('type', 'turnover');
        $targetFileName = $fileType === 'croselling' ? 'CROSSELLING-12102026.xlsx' : 'turnover.xlsx';
        $targetPath = $this->storagePath . DIRECTORY_SEPARATOR . $targetFileName;

        $content = $request->getContent();
        if (empty($content)) {
            return response()->json(['error' => 'Data file kosong.'], 400);
        }

        @File::put($targetPath, $content);

        $nowMs = (string) round(microtime(true) * 1000);
        $prefix = $fileType === 'croselling' ? 'workspace_croselling' : 'workspace_turnover';
        Setting::updateOrCreate(['key' => "{$prefix}_mtime"], ['value' => $nowMs]);
        Setting::updateOrCreate(['key' => "{$prefix}_uploader"], ['value' => $user->name]);
        Setting::updateOrCreate(['key' => "{$prefix}_filesize"], ['value' => (string) strlen($content)]);
        Setting::updateOrCreate(['key' => "{$prefix}_file_base64"], ['value' => base64_encode($content)]);

        return response()->json([
            'success' => true,
            'message' => "File {$targetFileName} berhasil disimpan dan disinkronkan ke seluruh perangkat!",
            'mtimeMs' => (int) $nowMs,
            'uploader' => $user->name,
        ]);
    }

    public function uploadExcelChunk(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isSuperAdmin()) {
            return response()->json([
                'error' => 'Akses ditolak: Hanya pengguna dengan akses Superadmin yang dapat memperbarui database Cross Selling.',
            ], 403);
        }

        $uploadId = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($request->input('upload_id') ?: $request->header('X-Upload-Id', '')));
        $chunkIndex = (int) ($request->input('chunk_index') ?? $request->header('X-Chunk-Index', -1));
        $totalChunks = (int) ($request->input('total_chunks') ?? $request->header('X-Total-Chunks', 0));
        $fileType = (string) ($request->input('file_type') ?: $request->header('X-File-Type', 'croselling'));
        $originalName = (string) ($request->input('original_name') ?: $request->header('X-Original-Filename', 'CROSSELLING-12102026.xlsx'));

        if (empty($uploadId) || $chunkIndex < 0 || $totalChunks <= 0) {
            return response()->json(['error' => 'Parameter chunk upload tidak valid.'], 400);
        }

        if ($request->hasFile('chunk')) {
            $content = File::get($request->file('chunk')->getRealPath());
        } elseif ($request->filled('chunk')) {
            $content = (string) $request->input('chunk');
        } else {
            $content = $request->getContent();
        }

        if (empty($content)) {
            return response()->json(['error' => 'Isi chunk data kosong.'], 400);
        }

        $tempDir = storage_path("app/tmp_chunks/{$uploadId}");
        File::ensureDirectoryExists($tempDir);
        File::put("{$tempDir}/chunk_{$chunkIndex}", $content);

        // Check if all chunks have been received
        $receivedAll = true;
        for ($i = 0; $i < $totalChunks; $i++) {
            if (! File::exists("{$tempDir}/chunk_{$i}")) {
                $receivedAll = false;
                break;
            }
        }

        if (! $receivedAll) {
            return response()->json([
                'success' => true,
                'chunk_index' => $chunkIndex,
                'completed' => false,
                'message' => "Chunk {$chunkIndex} berhasil diunggah.",
            ]);
        }

        // Reassemble full file
        $fullBuffer = '';
        for ($i = 0; $i < $totalChunks; $i++) {
            $fullBuffer .= File::get("{$tempDir}/chunk_{$i}");
        }

        // Save to public workspace folder
        $targetFileName = $fileType === 'croselling' ? 'CROSSELLING-12102026.xlsx' : 'turnover.xlsx';
        $targetPath = $this->storagePath . DIRECTORY_SEPARATOR . $targetFileName;
        @File::put($targetPath, $fullBuffer);

        $nowMs = (string) round(microtime(true) * 1000);
        $prefix = $fileType === 'croselling' ? 'workspace_croselling' : 'workspace_turnover';
        Setting::updateOrCreate(['key' => "{$prefix}_mtime"], ['value' => $nowMs]);
        Setting::updateOrCreate(['key' => "{$prefix}_uploader"], ['value' => $user->name]);
        Setting::updateOrCreate(['key' => "{$prefix}_filename"], ['value' => $originalName]);
        Setting::updateOrCreate(['key' => "{$prefix}_filesize"], ['value' => (string) strlen($fullBuffer)]);
        Setting::updateOrCreate(['key' => "{$prefix}_file_base64"], ['value' => base64_encode($fullBuffer)]);

        // Clean up temporary chunks
        File::deleteDirectory($tempDir);

        return response()->json([
            'success' => true,
            'completed' => true,
            'message' => "File {$originalName} berhasil diunggah dan disinkronkan ke seluruh perangkat!",
            'mtimeMs' => (int) $nowMs,
            'uploader' => $user->name,
        ]);
    }

    public function searchAr(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('query', ''));
        if ($query === '') {
            return response()->json(['rows' => []]);
        }

        $digits = preg_replace('/\D/', '', $query);

        $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $results = DB::table('cross_sellings')
            ->where(function ($q) use ($query, $digits, $likeOp) {
                if ($digits !== '') {
                    $q->where('document_number', $digits)
                      ->orWhere('no_hp_customer', 'like', "%{$digits}%");
                }
                $q->orWhere('document_number', $query)
                  ->orWhere('name', $likeOp, "%{$query}%")
                  ->orWhere('customer_vendor_name', $likeOp, "%{$query}%");
            })
            ->limit(100)
            ->get();

        return response()->json([
            'query' => $query,
            'count' => $results->count(),
            'rows' => $results,
        ]);
    }

    public function turnoverRecords(Request $request): JsonResponse
    {
        $month = $request->query('month');
        $date = $request->query('date');
        $sales = $request->query('sales');
        $limit = min((int) ($request->query('limit', 100)), 500);

        $query = DB::table('turnovers');

        if ($date) {
            $query->whereDate('posting_date', $date);
        } elseif ($month) {
            if (is_numeric($month) && strlen((string) $month) <= 2) {
                $query->whereMonth('posting_date', (int) $month);
            } else {
                $query->where('period_label', (string) $month);
            }
        }

        if ($sales && $sales !== 'all') {
            $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where('slp_name', $likeOp, "%{$sales}%");
        }

        $totalCount = (clone $query)->count();
        $totalDpp = (float) ((clone $query)->sum('dpp') ?? 0);
        $totalGp = (float) ((clone $query)->sum('gross_profit') ?? 0);

        $rows = $query->orderBy('posting_date', 'desc')->limit($limit)->get();

        $availablePeriods = DB::table('turnovers')
            ->select('period_label', DB::raw('count(*) as count'), DB::raw('sum(dpp) as dpp_sum'))
            ->whereNotNull('period_label')
            ->groupBy('period_label')
            ->orderBy('period_label', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'total_count' => $totalCount,
            'total_dpp' => $totalDpp,
            'total_gp' => $totalGp,
            'available_periods' => $availablePeriods,
            'rows' => $rows,
        ]);
    }
}
