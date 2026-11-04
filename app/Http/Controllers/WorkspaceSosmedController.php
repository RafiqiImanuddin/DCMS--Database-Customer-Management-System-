<?php

namespace App\Http\Controllers;

use App\Models\BroadcastMedia;
use App\Services\WatzapService;
use App\Support\WorkspaceUrlCipher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        return response()->json([
            'turnover' => $check('turnover.xlsx'),
            'croselling' => $check('CROSSELLING-12102026.xlsx'),
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
            if (File::exists($file)) {
                return response()->json(json_decode(File::get($file), true) ?? []);
            }
            return response()->json([]);
        }

        $payload = $request->all();
        if (!empty($payload)) {
            @File::put($file, json_encode($payload, JSON_PRETTY_PRINT));
            return response()->json(['success' => true, 'message' => 'Monthly targets berhasil disimpan!']);
        }

        return response()->json(['error' => 'Data targets kosong.'], 400);
    }

    public function salesPhotos(Request $request): JsonResponse
    {
        $file = $this->storagePath . DIRECTORY_SEPARATOR . 'sales_photos.json';

        if ($request->isMethod('get')) {
            if (File::exists($file)) {
                return response()->json(json_decode(File::get($file), true) ?? []);
            }
            return response()->json([]);
        }

        $payload = $request->all();
        if (is_array($payload)) {
            @File::put($file, json_encode($payload, JSON_PRETTY_PRINT));
            return response()->json(['success' => true, 'message' => 'Foto sales berhasil disimpan!']);
        }

        return response()->json(['error' => 'Data kosong.'], 400);
    }

    public function monthlyArchives(Request $request): JsonResponse
    {
        $file = $this->storagePath . DIRECTORY_SEPARATOR . 'monthly_archives.json';

        if ($request->isMethod('get')) {
            if (File::exists($file)) {
                return response()->json(json_decode(File::get($file), true) ?? []);
            }
            return response()->json([]);
        }

        $payload = $request->all();
        if (is_array($payload)) {
            @File::put($file, json_encode($payload, JSON_PRETTY_PRINT));
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

    public function getExcel(Request $request): BinaryFileResponse|JsonResponse
    {
        $fileType = $request->query('type', 'turnover');
        $targetFileName = $fileType === 'croselling' ? 'CROSSELLING-12102026.xlsx' : 'turnover.xlsx';
        $path = $this->storagePath . DIRECTORY_SEPARATOR . $targetFileName;

        if ($request->isMethod('delete')) {
            return response()->json(['success' => true, 'message' => "File {$fileType} di-reset ke default."]);
        }

        if (File::exists($path)) {
            return response()->download($path, $targetFileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return response()->json(['error' => 'File tidak ditemukan'], 404);
    }

    public function saveExcel(Request $request): JsonResponse
    {
        $fileType = $request->query('type', 'turnover');
        $targetFileName = $fileType === 'croselling' ? 'CROSSELLING-12102026.xlsx' : 'turnover.xlsx';
        $targetPath = $this->storagePath . DIRECTORY_SEPARATOR . $targetFileName;

        $content = $request->getContent();
        if (empty($content)) {
            return response()->json(['error' => 'Data file kosong.'], 400);
        }

        @File::put($targetPath, $content);

        return response()->json([
            'success' => true,
            'message' => "File {$targetFileName} berhasil disimpan!",
        ]);
    }
}
