<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Models\BroadcastMedia;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\WhatsAppChat;
use App\Models\WhatsAppMessage;
use App\Services\WatzapService;
use App\Support\RouteCipher;
use App\Support\WorkspaceUrlCipher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
                return redirect()->to(RouteCipher::url('workspace-sosmed.index', [
                    'tab' => (int) $rawTab,
                ]));
            }
        }

        $activeTab = RouteCipher::resolveWorkspaceTab($request)
            ?? WorkspaceUrlCipher::resolveTab($request);

        $tabFeatureMap = [
            1 => 'workspace_dashboard',
            2 => 'workspace_matrix',
            3 => 'workspace_cek_ar',
            4 => 'workspace_format_keep',
            5 => 'workspace_tracking_ar',
            6 => 'workspace_klaim_ar',
            7 => 'workspace_archives',
            8 => 'workspace_admin_settings',
            9 => 'workspace_inbox_fu',
        ];

        $user = $request->user();
        $targetFeature = $tabFeatureMap[$activeTab] ?? 'workspace_dashboard';

        if ($user && ! $user->canAccessFeature($targetFeature)) {
            $firstAllowedTab = null;
            foreach ($tabFeatureMap as $tabNum => $featureKey) {
                if ($user->canAccessFeature($featureKey)) {
                    $firstAllowedTab = $tabNum;
                    break;
                }
            }

            if ($firstAllowedTab !== null) {
                $activeTab = $firstAllowedTab;
            } else {
                abort(403, 'Akses terbatas: Fitur Workspace Sosmed tidak diaktifkan untuk akun Anda.');
            }
        }

        $initialWorkspaceSettings = $this->getWorkspaceSettingsData();

        return view('workspace-sosmed.index', [
            'activeTab' => $activeTab,
            'tabTokens' => WorkspaceUrlCipher::allTokens(),
            'encryptedTabUrls' => RouteCipher::allWorkspaceUrls(),
            'initialWorkspaceSettings' => $initialWorkspaceSettings,
        ]);
    }

    /**
     * Get centralized workspace settings (Employees & Matrix KPI Weights).
     */
    private function getWorkspaceSettingsData(): array
    {
        $matrixWeightsSetting = Setting::where('key', 'workspace_matrix_weights')->value('value');
        $matrixWeights = ! empty($matrixWeightsSetting) ? json_decode($matrixWeightsSetting, true) : [
            'omzet' => 35.0,
            'gp' => 35.0,
            'cross_selling' => 30.0,
        ];

        $monthlyTargetsSetting = Setting::where('key', 'workspace_monthly_targets')->value('value');
        $monthlyTargets = ! empty($monthlyTargetsSetting) ? json_decode($monthlyTargetsSetting, true) : [];
        if (! is_array($monthlyTargets)) {
            $monthlyTargets = [];
        }

        $isGuest = auth()->check() && (auth()->user()->role ?? '') === 'guest';
        if ($isGuest) {
            foreach ($monthlyTargets as $mKey => &$mVal) {
                if (is_array($mVal)) {
                    foreach ($mVal as $subKey => &$subVal) {
                        $subVal = 0;
                    }
                } else {
                    $mVal = 0;
                }
            }
            unset($mVal, $subVal);
        }

        $employeeQuery = Employee::query();
        if (Schema::hasColumn('employees', 'level')) {
            $employeeQuery->orderByRaw("CASE WHEN level = 'Internship' THEN 2 ELSE 1 END");
        }

        $employees = $employeeQuery
            ->orderBy('id')
            ->get()
            ->map(function ($emp) use ($isGuest) {
                return [
                    'id' => $emp->id,
                    'code' => $emp->nik,
                    'nik' => $emp->nik,
                    'name' => strtoupper($emp->name),
                    'target' => $isGuest ? 0 : (float) ($emp->target ?: 867261886.00),
                    'active' => $emp->status === 'active',
                    'status' => $emp->status,
                    'cabang' => $emp->cabang,
                    'role' => $emp->role,
                    'level' => $emp->level ?? 'Karyawan',
                    'phone' => $isGuest ? \App\Support\DataMasker::maskPhone($emp->phone) : $emp->phone,
                    'avatar_color' => $emp->avatar_color,
                ];
            })
            ->values()
            ->all();

        return [
            'matrix_weights' => $matrixWeights,
            'monthly_targets' => $monthlyTargets,
            'employees' => $employees,
        ];
    }

    public function workspaceSettings(): JsonResponse
    {
        return response()->json($this->getWorkspaceSettingsData());
    }

    public function databaseStatus(): JsonResponse
    {
        $check = function (string $filename) {
            $path = $this->storagePath.DIRECTORY_SEPARATOR.$filename;
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

        if ($dbTurnoverMtime > 0) {
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

        if ($dbCrossMtime > 0) {
            $crossStatus['exists'] = true;
            $crossStatus['mtimeMs'] = $dbCrossMtime;
            $crossStatus['mtime'] = date('c', (int) ($dbCrossMtime / 1000));
            if ($dbCrossFilesize > 0) {
                $crossStatus['size'] = $dbCrossFilesize;
            }
        }
        $crossStatus['uploader'] = $dbCrossUploader;

        $keepStatus = $check('keep-data.csv');
        $keepLastSync = Setting::where('key', 'workspace_keep_last_sync')->value('value');
        $keepLastCount = Setting::where('key', 'workspace_keep_last_count')->value('value');
        $keepStatus['totalRows'] = (int) ($keepLastCount ?? \App\Models\SosmedKeep::count());
        $keepStatus['lastSync'] = $keepLastSync;
        $keepStatus['lastSyncFormatted'] = $keepLastSync ? \Carbon\Carbon::parse($keepLastSync)->timezone('Asia/Makassar')->format('d M Y, H:i').' WITA' : 'Baru saja';

        return response()->json([
            'turnover' => $turnoverStatus,
            'croselling' => $crossStatus,
            'keep' => $keepStatus,
            'archives' => $check('monthly_archives.json'),
            'targets' => $check('monthly_targets.json'),
            'photos' => $check('sales_photos.json'),
            'serverTime' => now()->toIso8601String(),
        ]);
    }

    public function monthlyTargets(Request $request): JsonResponse
    {
        $file = $this->storagePath.DIRECTORY_SEPARATOR.'monthly_targets.json';

        if ($request->isMethod('get')) {
            $dbVal = Setting::where('key', 'workspace_monthly_targets')->value('value');
            if (! empty($dbVal)) {
                $decoded = json_decode($dbVal, true);
                if (is_array($decoded)) {
                    return response()->json($decoded);
                }
            }
            if (File::exists($file)) {
                $content = File::get($file);
                $decoded = json_decode($content, true) ?? [];
                if (! empty($decoded)) {
                    Setting::updateOrCreate(['key' => 'workspace_monthly_targets'], ['value' => $content]);
                }

                return response()->json($decoded);
            }

            return response()->json([]);
        }

        $payload = $request->all();
        if (! empty($payload)) {
            $existingJson = Setting::where('key', 'workspace_monthly_targets')->value('value');
            $existingData = ! empty($existingJson) ? json_decode($existingJson, true) : [];
            if (! is_array($existingData)) {
                $existingData = [];
            }

            if (isset($payload['month'], $payload['year'])) {
                $key = sprintf('%04d-%02d', (int) $payload['year'], (int) $payload['month']);
                $existingData[$key] = $payload;
                $merged = $existingData;
            } elseif (is_array($payload)) {
                $merged = array_merge($existingData, $payload);
            } else {
                $merged = $payload;
            }

            $json = json_encode($merged, JSON_PRETTY_PRINT);
            @File::put($file, $json);
            Setting::updateOrCreate(['key' => 'workspace_monthly_targets'], ['value' => $json]);

            try {
                $actor = $request->user();
                $actorName = $actor ? $actor->name : 'Superadmin';
                ActivityNotification::log(
                    title: 'Pembaruan Target Bulanan',
                    message: "{$actorName} memperbarui target pencapaian bulanan Sosmed",
                    user: $actor,
                    type: 'info',
                    action: 'update_target',
                    icon: 'edit'
                );
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'message' => 'Monthly targets berhasil disimpan!',
                'data' => $merged,
            ]);
        }

        return response()->json(['error' => 'Data targets kosong.'], 400);
    }

    public function salesPhotos(Request $request): JsonResponse
    {
        $file = $this->storagePath.DIRECTORY_SEPARATOR.'sales_photos.json';

        if ($request->isMethod('get')) {
            $dbVal = Setting::where('key', 'workspace_sales_photos')->value('value');
            if (! empty($dbVal)) {
                $decoded = json_decode($dbVal, true);
                if (is_array($decoded)) {
                    return response()->json($decoded);
                }
            }
            if (File::exists($file)) {
                $content = File::get($file);
                $decoded = json_decode($content, true) ?? [];
                if (! empty($decoded)) {
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
        $file = $this->storagePath.DIRECTORY_SEPARATOR.'monthly_archives.json';

        if ($request->isMethod('get')) {
            $dbVal = Setting::where('key', 'workspace_monthly_archives')->value('value');
            if (! empty($dbVal)) {
                $decoded = json_decode($dbVal, true);
                if (is_array($decoded)) {
                    return response()->json($decoded);
                }
            }
            if (File::exists($file)) {
                $content = File::get($file);
                $decoded = json_decode($content, true) ?? [];
                if (! empty($decoded)) {
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

    public function syncKeep(Request $request): JsonResponse
    {
        $rowCount = 0;
        $imported = false;

        if (\Illuminate\Support\Facades\Schema::hasTable('sosmed_keeps')) {
            $rowCount = \App\Models\SosmedKeep::count();
            if ($rowCount === 0) {
                $csvPath = public_path('workspace-sosmed' . DIRECTORY_SEPARATOR . 'keep-data.csv');
                if (file_exists($csvPath)) {
                    try {
                        @set_time_limit(180);
                        \Illuminate\Support\Facades\Artisan::call('keep:import-csv');
                        $rowCount = \App\Models\SosmedKeep::count();
                        $imported = $rowCount > 0;
                    } catch (\Throwable $impErr) {
                        \Illuminate\Support\Facades\Log::warning('Auto import CSV in syncKeep error: ' . $impErr->getMessage());
                    }
                }
            }
        }

        $syncedAt = now();
        $formattedTime = $syncedAt->timezone('Asia/Makassar')->format('d M Y, H:i').' WITA';

        Setting::updateOrCreate(['key' => 'workspace_keep_last_sync'], ['value' => $syncedAt->toIso8601String()]);
        Setting::updateOrCreate(['key' => 'workspace_keep_last_count'], ['value' => (string) $rowCount]);

        return response()->json([
            'success' => true,
            'has_changed' => false,
            'imported' => $imported,
            'total_rows' => $rowCount,
            'total_rows_formatted' => number_format($rowCount, 0, ',', '.'),
            'synced_at' => $syncedAt->toIso8601String(),
            'synced_at_formatted' => $formattedTime,
            'message' => "Data transaksi aktif dari database DCMS MySQL ({$rowCount} data realtime).",
        ]);
    }

    public function updateKeepStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $nomorWA = $request->input('nomorWA') ?? $request->input('nomor_wa', '');
        $status = $request->input('status', '');
        $feedback = $request->input('feedback') ?? $request->input('feedbackTimFU');

        if (! $id && ! $nomorWA) {
            return response()->json(['error' => 'ID transaksi atau Nomor WA wajib diisi.'], 400);
        }

        // 1. Update ke MySQL SosmedKeep secara realtime (prioritaskan pencarian by ID)
        $cleanPhone = preg_replace('/\D/', '', (string) $nomorWA);
        $last9 = $cleanPhone !== '' ? substr($cleanPhone, -9) : '';

        $keep = null;
        if ($id) {
            $keep = \App\Models\SosmedKeep::find($id);
        }

        if (! $keep && $cleanPhone !== '') {
            $keep = \App\Models\SosmedKeep::where(function ($q) use ($cleanPhone, $last9) {
                $q->where('nomor_wa', $cleanPhone)
                    ->orWhere('nomor_wa', 'like', "%{$last9}");
            })->latest('id')->first();
        }

        if ($keep) {
            if ($status !== null && $status !== '') {
                $keep->status = trim((string) $status);
            }
            if ($feedback !== null) {
                $keep->feedback_tim_fu = trim((string) $feedback);
            }
            $keep->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Status keep berhasil diperbarui di database SQL!',
            'id' => $keep ? $keep->id : $id,
            'nomorWA' => $keep ? $keep->nomor_wa : $nomorWA,
            'status' => $status,
            'mysql_updated' => (bool) $keep,
        ]);
    }

    public function updateReportAr(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $nomorWA = $request->input('nomorWA') ?? $request->input('nomor_wa', '');
        $arDoc = $request->input('arDoc') ?? $request->input('reportAR') ?? $request->input('report_ar', '');

        if (! $id && ! $nomorWA) {
            return response()->json(['error' => 'ID transaksi atau Nomor WA wajib diisi.'], 400);
        }

        // 1. Update ke MySQL SosmedKeep secara realtime (prioritaskan pencarian by ID)
        $cleanPhone = preg_replace('/\D/', '', (string) $nomorWA);
        $last9 = $cleanPhone !== '' ? substr($cleanPhone, -9) : '';
        $cleanARDoc = trim((string) $arDoc);

        $keep = null;
        if ($id) {
            $keep = \App\Models\SosmedKeep::find($id);
        }

        if (! $keep && $cleanPhone !== '') {
            $keep = \App\Models\SosmedKeep::where(function ($q) use ($cleanPhone, $last9) {
                $q->where('nomor_wa', $cleanPhone)
                    ->orWhere('nomor_wa', 'like', "%{$last9}");
            })->latest('id')->first();
        }

        if ($keep) {
            $keep->report_ar = $cleanARDoc;
            $keep->tanggal_input_ar = $cleanARDoc !== '' ? now() : null;
            $keep->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen AR berhasil diperbarui di database SQL!',
            'id' => $keep ? $keep->id : $id,
            'nomorWA' => $keep ? $keep->nomor_wa : $nomorWA,
            'arDoc' => $arDoc,
            'mysql_updated' => (bool) $keep,
        ]);
    }

    public function assignKeepFU(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $nomorWA = $request->input('nomorWA') ?? $request->input('nomor_wa', '');
        $namaFU = trim((string) ($request->input('nama_fu') ?? $request->input('nama_tim_follow_up') ?? ''));
        $nomorFU = trim((string) ($request->input('nomor_fu') ?? $request->input('nomor_follow_up') ?? ''));

        if (! $id && ! $nomorWA) {
            return response()->json(['error' => 'ID transaksi atau Nomor WA customer wajib diisi.'], 400);
        }

        if (empty($namaFU) && empty($nomorFU)) {
            return response()->json(['error' => 'Nama tim follow up atau nomor HP wajib diisi.'], 400);
        }

        $cleanNomorFU = preg_replace('/\D/', '', $nomorFU);
        if ($cleanNomorFU !== '') {
            if (str_starts_with($cleanNomorFU, '08')) {
                $cleanNomorFU = '628'.substr($cleanNomorFU, 2);
            } elseif (str_starts_with($cleanNomorFU, '8')) {
                $cleanNomorFU = '62'.$cleanNomorFU;
            }
        }

        $cleanCustPhone = preg_replace('/\D/', '', (string) $nomorWA);
        $last9 = $cleanCustPhone !== '' ? substr($cleanCustPhone, -9) : '';

        $keep = null;
        if ($id) {
            $keep = \App\Models\SosmedKeep::find($id);
        }

        if (! $keep && $cleanCustPhone !== '') {
            $keep = \App\Models\SosmedKeep::where(function ($q) use ($cleanCustPhone, $last9) {
                $q->where('nomor_wa', $cleanCustPhone)
                    ->orWhere('nomor_wa', 'like', "%{$last9}");
            })->latest('id')->first();
        }

        if ($keep) {
            $keep->nama_tim_follow_up = $namaFU ?: $keep->nama_tim_follow_up;
            $keep->nomor_fu = $cleanNomorFU ?: $keep->nomor_fu;
            $keep->nomor_follow_up = $cleanNomorFU ?: $keep->nomor_follow_up;

            $currStatus = strtoupper(trim((string) $keep->status));
            if (empty($currStatus) || in_array($currStatus, ['PENDING', 'BELUM DIINPUT', 'NEW'], true)) {
                $keep->status = 'ON PROGRESS';
            }
            $keep->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Tim Follow Up berhasil di-assign secara manual!',
            'id' => $keep ? $keep->id : $id,
            'customer' => $keep ? $keep->nomor_wa : $nomorWA,
            'pic_name' => $keep ? $keep->nama_tim_follow_up : $namaFU,
            'pic_phone' => $keep ? $keep->nomor_fu : $cleanNomorFU,
            'status' => $keep ? $keep->status : 'ON PROGRESS',
            'mysql_updated' => (bool) $keep,
        ]);
    }

    public function chatAi(Request $request): JsonResponse
    {
        $prompt = $request->input('prompt', '');
        $systemInstruction = $request->input('systemInstruction', '');
        $apiKey = $request->input('apiKey') ?: env('GEMINI_API_KEY', '');
        $requestedModel = $request->input('model', 'gemini-2.5-flash');

        if (! $prompt) {
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
                        ['parts' => [['text' => $fullPrompt]]],
                    ],
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

        $replyTo = $request->input('reply_to') ?: $request->input('replyTo');
        if (is_string($replyTo)) {
            $replyTo = json_decode($replyTo, true);
        }

        if (! $rawPhone) {
            return response()->json(['success' => false, 'message' => 'Nomor telepon tujuan wajib diisi!'], 400);
        }

        if (! $message && ! $uploadedFile && ! $mediaUrl) {
            return response()->json(['success' => false, 'message' => 'Pesan atau file lampiran wajib diisi!'], 400);
        }

        $fileData = null;
        if ($uploadedFile) {
            $originalName = $uploadedFile->getClientOriginalName() ?: ('file_'.time());
            $mimeType = $uploadedFile->getMimeType() ?: 'application/octet-stream';
            $fileSize = $uploadedFile->getSize();
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            $isImage = str_starts_with($mimeType, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
            $mediaType = $isImage ? 'image' : 'document';
            $rawBase64 = base64_encode($uploadedFile->getContent());

            $media = BroadcastMedia::create([
                'user_id' => $request->user()?->id,
                'filename' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'data' => $rawBase64,
            ]);

            $mediaUrl = route('broadcasts.media.show', [
                'media' => $media->id,
                'filename' => $originalName,
            ], true);

            $fileData = [
                'base64' => $rawBase64,
                'mimetype' => $mimeType,
                'filename' => $originalName,
            ];
        }

        if (! $mediaType && $mediaUrl) {
            $pathExt = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $mediaType = in_array($pathExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? 'image' : 'document';
        }

        // Kirim via Dual-Gateway Omnichannel (WAHA VPS primary + WatZap fallback)
        $result = $watzapService->sendOmnichannel($rawPhone, $message, $mediaUrl, $mediaType, is_array($replyTo) ? $replyTo : null, $fileData);
        $cleanPhone = $watzapService->resolvePhoneOrLid($rawPhone);
        $msgId = $result['message_id'] ?? ('out_'.uniqid());
        $isSuccess = ! empty($result['success']);

        // Catat ke pesan keluar lokal & Database
        $entry = [
            'messageId' => $msgId,
            'phone' => $cleanPhone ?: $rawPhone,
            'message' => $message,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
            'filename' => $originalName,
            'file_size' => $fileSize,
            'reply_to' => is_array($replyTo) ? $replyTo : null,
            'timestamp' => now()->toIso8601String(),
            'status' => $isSuccess ? 'sent' : 'failed',
            'direction' => 'outbound',
            'sender' => 'ADMIN',
            'gateway' => $result['source'] ?? 'WATZAP',
        ];

        try {
            if ($cleanPhone) {
                $chat = WhatsAppChat::firstOrCreate(
                    ['phone_number' => $cleanPhone],
                    [
                        'customer_name' => 'PIC '.$cleanPhone,
                        'folder' => 'all',
                        'unread_count' => 0,
                    ]
                );
                $chat->update([
                    'last_message' => $message ?: ($mediaType ? "[$mediaType]" : 'Pesan keluar'),
                    'last_message_at' => now(),
                ]);
                WhatsAppMessage::create([
                    'whatsapp_chat_id' => $chat->id,
                    'message_id' => $msgId,
                    'sender_type' => 'admin',
                    'user_id' => $request->user()?->id,
                    'agent_name' => $request->user()?->name ?: 'Admin',
                    'message' => $message ?: ($mediaType ? "[$mediaType]" : ''),
                    'media_url' => $mediaUrl,
                    'media_type' => $mediaType,
                    'reply_to' => is_array($replyTo) ? $replyTo : null,
                    'status' => $isSuccess ? 'sent' : 'failed',
                ]);
            }
        } catch (\Throwable) {
        }

        try {
            $msgFile = $this->storagePath.DIRECTORY_SEPARATOR.'watzap_messages.json';
            $messages = File::exists($msgFile) ? json_decode(File::get($msgFile), true) ?? [] : [];
            $messages[] = $entry;
            $trimmed = array_slice($messages, -1000);
            @File::put($msgFile, json_encode($trimmed, JSON_PRETTY_PRINT));
        } catch (\Throwable) {
        }

        return response()->json([
            'success' => $isSuccess,
            'status' => $result['status'] ?? ($isSuccess ? '200' : '500'),
            'message' => $result['message'] ?? ($isSuccess ? 'Pesan terkirim' : 'Proses kirim selesai'),
            'entry' => $entry,
            'source' => $result['source'] ?? 'WATZAP',
            'raw' => $result['raw'] ?? [],
        ]);
    }

    public function watzapStatus(WatzapService $watzapService): JsonResponse
    {
        $status = $watzapService->getDeviceStatus('inbox');
        $status['waha_enabled'] = $watzapService->isWahaEnabled();
        $status['waha_url'] = $watzapService->getWahaUrl();
        $status['baileys_enabled'] = $watzapService->isBaileysEnabled();
        $status['baileys_url'] = $watzapService->getBaileysUrl();

        if ($watzapService->isBaileysEnabled()) {
            $status['baileys_status'] = $watzapService->getBaileysStatus();
        }

        return response()->json($status);
    }

    /**
     * Endpoint API Sinkronisasi Sesi Baileys (Cloud Auto-Save).
     * Memungkinkan bot Baileys di cloud (Render.com) menyimpan dan memulihkan
     * kredensial multi-file auth ke database DCMS secara otomatis tanpa perlu scan QR ulang.
     */
    public function baileysSession(Request $request, WatzapService $watzapService): JsonResponse
    {
        $providedKey = (string) ($request->header('X-Api-Key') ?? $request->header('X-API-KEY') ?? $request->query('key') ?? $request->input('key'));
        $expectedKey = $watzapService->getBaileysApiKey();

        if (empty($providedKey) || ! hash_equals($expectedKey, $providedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: API Key Baileys tidak valid.',
            ], 401);
        }

        // 1. GET: Ambil snapshot sesi auth yang tersimpan di database DCMS
        if ($request->isMethod('get')) {
            $rawSession = (string) Setting::get('baileys_auth_session', '');
            $session = ! empty($rawSession) ? json_decode($rawSession, true) : null;

            if (empty($session) || ! is_array($session) || empty($session['creds.json'])) {
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'session' => null,
                    'message' => 'Belum ada sesi auth Baileys tersimpan di database.',
                ]);
            }

            return response()->json([
                'success' => true,
                'exists' => true,
                'count' => count($session),
                'saved_at' => Setting::get('baileys_auth_saved_at'),
                'session' => $session,
                'message' => 'Sesi auth Baileys berhasil dimuat dari database.',
            ]);
        }

        // 2. POST: Simpan atau perbarui snapshot sesi auth ke database DCMS
        if ($request->isMethod('post')) {
            $session = $request->input('session');

            if (! is_array($session) || empty($session['creds.json'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payload sesi tidak valid. File creds.json wajib disertakan.',
                ], 422);
            }

            // Sanitasi nama file untuk keamanan (mencegah directory traversal)
            $cleanSession = [];
            foreach ($session as $fileName => $fileContent) {
                $baseName = basename((string) $fileName);
                if (preg_match('/^[a-zA-Z0-9_\-\.]+$/', $baseName) && is_string($fileContent)) {
                    $cleanSession[$baseName] = $fileContent;
                }
            }

            if (empty($cleanSession['creds.json'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sanitasi gagal: creds.json tidak valid.',
                ], 422);
            }

            Setting::set('baileys_auth_session', json_encode($cleanSession));
            Setting::set('baileys_auth_saved_at', now()->toIso8601String());

            return response()->json([
                'success' => true,
                'count' => count($cleanSession),
                'saved_at' => now()->toIso8601String(),
                'message' => 'Sesi auth Baileys berhasil disimpan ke database DCMS.',
            ]);
        }

        // 3. DELETE: Hapus sesi auth tersimpan (misal saat logout dari WhatsApp)
        if ($request->isMethod('delete')) {
            Setting::forget('baileys_auth_session');
            Setting::forget('baileys_auth_saved_at');

            return response()->json([
                'success' => true,
                'message' => 'Sesi auth Baileys berhasil dihapus dari database DCMS.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Metode HTTP tidak didukung.',
        ], 405);
    }

    public function watzapWebhook(Request $request, WatzapService $watzapService): JsonResponse
    {
        $msgFile = $this->storagePath.DIRECTORY_SEPARATOR.'watzap_messages.json';
        $messages = File::exists($msgFile) ? json_decode(File::get($msgFile), true) ?? [] : [];

        // Mode GET: Polling dari frontend untuk mengambil chat masuk & riwayat pesan
        if ($request->isMethod('get')) {
            $since = $request->query('since');
            $phone = $request->query('phone');
            $inboundOnly = $request->boolean('inbound_only', false);
            $forceSync = $request->boolean('sync', false);

            // Sinkronisasi otomatis dari WAHA VPS jika aktif
            if ($watzapService->isWahaEnabled()) {
                // Throttle sinkronisasi ke WAHA VPS minimal 3 detik antar pemanggilan agar responsif & tidak flooding
                $lastWahaSync = (int) cache()->get('waha_last_sync_timestamp', 0);
                $shouldSync = $forceSync || (time() - $lastWahaSync) >= 3;
                if ($shouldSync) {
                    cache()->put('waha_last_sync_timestamp', time(), 120);
                    try {
                        $synced = $watzapService->syncWahaMessages(30, 20);
                        if (! empty($synced)) {
                            $existingIds = array_column($messages, 'messageId');
                            $existingIdMap = array_flip(array_filter($existingIds));
                            foreach ($synced as $sItem) {
                                if (! empty($sItem['messageId']) && isset($existingIdMap[$sItem['messageId']])) {
                                    continue;
                                }
                                $messages[] = $sItem;
                                if (! empty($sItem['messageId'])) {
                                    $existingIdMap[$sItem['messageId']] = true;
                                }
                            }
                            usort($messages, function ($a, $b) {
                                $tA = is_numeric($a['timestamp'] ?? 0) ? (int) $a['timestamp'] : strtotime((string) ($a['timestamp'] ?? ''));
                                $tB = is_numeric($b['timestamp'] ?? 0) ? (int) $b['timestamp'] : strtotime((string) ($b['timestamp'] ?? ''));

                                return $tA <=> $tB;
                            });
                            $trimmed = array_slice($messages, -1000);
                            @File::put($msgFile, json_encode($trimmed, JSON_PRETTY_PRINT));
                            $messages = $trimmed;
                        }
                    } catch (\Throwable) {
                    }
                }
            }

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
                    $mTime = is_numeric($m['timestamp'] ?? null) ? (int) $m['timestamp'] : (isset($m['timestamp']) ? strtotime((string) $m['timestamp']) * 1000 : 0);
                    $sTime = is_numeric($since) ? (int) ($since < 1e11 ? $since * 1000 : $since) : strtotime((string) $since) * 1000;
                    // Buffer toleransi 30 detik untuk mengantisipasi perbedaan clock server vs browser
                    if ($mTime <= ($sTime - 30000)) {
                        return false;
                    }
                }

                return true;
            }));

            $gatewayPhone = '6285111426699';
            try {
                if ($watzapService->isBaileysEnabled()) {
                    $bStatus = $watzapService->getBaileysStatus();
                    if (! empty($bStatus['phone'])) {
                        $gatewayPhone = preg_replace('/\D/', '', (string) $bStatus['phone']);
                    }
                } elseif ($watzapService->isWahaEnabled()) {
                    $wahaStatus = $watzapService->getWahaStatus();
                    if (! empty($wahaStatus['me']['id'])) {
                        $gatewayPhone = preg_replace('/\D/', '', (string) $wahaStatus['me']['id']);
                    }
                }
            } catch (\Throwable) {
            }

            return response()->json([
                'success' => true,
                'gateway_phone' => $gatewayPhone,
                'messages' => $filtered,
                'total' => count($filtered),
            ]);
        }

        // Mode POST: Webhook masuk (WAHA VPS atau WatZap Cloud API)
        $data = $request->all();

        // 1. Dukungan Webhook Resmi WAHA (WhatsApp HTTP API di VPS)
        if (isset($data['event']) && in_array($data['event'], ['message', 'message.any'], true) && isset($data['payload']) && is_array($data['payload'])) {
            $p = $data['payload'];
            $isFromMe = ! empty($p['fromMe']);
            $senderType = $isFromMe ? 'admin' : 'customer';
            $direction = $isFromMe ? 'outbound' : 'inbound';
            $senderRole = $isFromMe ? 'ADMIN' : 'PIC';

            $targetJid = $isFromMe
                ? (string) ($p['to'] ?? $p['_data']['id']['remote'] ?? $p['_data']['to'] ?? '')
                : (string) ($p['from'] ?? $p['_data']['id']['remote'] ?? '');

            // Abaikan grup WA & broadcast
            if (str_contains($targetJid, '@g.us') || str_contains($targetJid, 'broadcast') || str_contains((string) ($p['from'] ?? ''), '@g.us')) {
                return response()->json(['status' => 200, 'message' => 'Group or broadcast ignored']);
            }

            $cleanPhone = $watzapService->resolvePhoneOrLid($targetJid);
            $cleanPhone = preg_replace('/@.*$/', '', (string) $cleanPhone);
            $cleanPhone = preg_replace('/\D/', '', (string) $cleanPhone);
            if (! $cleanPhone || strlen($cleanPhone) < 9) {
                return response()->json(['status' => 200, 'message' => 'Invalid phone ignored']);
            }

            $messageId = (string) ($p['id'] ?? ('waha_'.time()));
            $senderName = $isFromMe
                ? 'Admin'
                : (string) ($p['_data']['notifyName'] ?? $p['pushname'] ?? $p['author'] ?? 'PIC');

            $rawText = trim((string) ($p['body'] ?? ''));
            $mediaUrl = null;
            $mediaType = null;

            if (! empty($p['hasMedia']) || ! empty($p['media'])) {
                if (is_array($p['media']) && ! empty($p['media']['url'])) {
                    $mediaUrl = (string) $p['media']['url'];
                } elseif (is_array($p['media']) && ! empty($p['media']['filename'])) {
                    $mediaUrl = '/api/watzap-media?file=default/'.$p['media']['filename'];
                } elseif (is_string($p['media'])) {
                    $mediaUrl = $p['media'];
                } else {
                    $mediaUrl = '/api/watzap-media?file=default/'.$messageId.'.jpeg';
                }
                $mediaType = 'image';
                if ($rawText === '') {
                    $rawText = '[Foto/Gambar]';
                }
            }

            // Quoted Reply Extraction
            $replyTo = null;
            if (! empty($p['replyTo']) && is_array($p['replyTo'])) {
                $quotedText = trim((string) ($p['replyTo']['body'] ?? ''));
                if ($quotedText !== '') {
                    $replyTo = [
                        'senderName' => $isFromMe ? 'PIC' : 'Admin',
                        'text' => $quotedText,
                        'id' => (string) ($p['replyTo']['id'] ?? ''),
                    ];
                }
            }

            if (! $replyTo) {
                $parsedQuote = $this->parseQuotedFromText($rawText);
                $rawText = $parsedQuote['cleanText'];
                $replyTo = $parsedQuote['replyTo'];
            }

            $entry = [
                'messageId' => $messageId,
                'phone' => $cleanPhone,
                'message' => $rawText,
                'media_url' => $mediaUrl,
                'media_type' => $mediaType,
                'reply_to' => $replyTo,
                'timestamp' => now()->toIso8601String(),
                'status' => 'received',
                'direction' => $direction,
                'sender' => $senderRole,
                'senderName' => $senderName,
                'gateway' => ($p['source'] ?? '') === 'BAILEYS_GATEWAY' ? 'BAILEYS_GATEWAY' : 'WAHA_VPS',
                'raw' => $data,
            ];

            try {
                $chat = WhatsAppChat::firstOrCreate(
                    ['phone_number' => $cleanPhone],
                    [
                        'customer_name' => $senderName ?: ('PIC '.$cleanPhone),
                        'folder' => 'all',
                        'unread_count' => 0,
                    ]
                );

                $chat->update([
                    'last_message' => $rawText,
                    'last_message_at' => now(),
                    'unread_count' => $direction === 'inbound' ? ($chat->unread_count + 1) : $chat->unread_count,
                ]);

                WhatsAppMessage::create([
                    'whatsapp_chat_id' => $chat->id,
                    'message_id' => $messageId,
                    'sender_type' => $senderType,
                    'agent_name' => $senderName,
                    'message' => $rawText,
                    'media_url' => $mediaUrl,
                    'media_type' => $mediaType,
                    'reply_to' => $replyTo,
                    'raw_payload' => $data,
                    'status' => 'received',
                ]);
            } catch (\Throwable) {
            }

            try {
                $messages[] = $entry;
                $trimmed = array_slice($messages, -1000);
                @File::put($msgFile, json_encode($trimmed, JSON_PRETTY_PRINT));
            } catch (\Throwable) {
            }

            return response()->json([
                'status' => '200',
                'message' => 'WAHA webhook received and processed',
                'entry' => $entry,
            ]);
        }

        // 2. Dukungan Webhook WatZap Cloud API Standar
        $payload = (isset($data['data']) && is_array($data['data'])) ? $data['data'] : $data;

        $rawPhone = $payload['phone_no'] ?? $payload['sender_number'] ?? $payload['phone'] ?? $payload['from'] ?? $payload['wa_number'] ?? $data['phone_no'] ?? $data['sender_number'] ?? $data['phone'] ?? '';
        $cleanPhone = $watzapService->resolvePhoneOrLid((string) $rawPhone);
        $message = $payload['message'] ?? $payload['text'] ?? $payload['caption'] ?? $payload['body'] ?? $data['message'] ?? $data['text'] ?? '';
        $mediaUrl = $payload['media_url'] ?? $payload['url'] ?? $payload['image_url'] ?? $payload['file_url'] ?? $data['media_url'] ?? $data['url'] ?? null;
        $mediaType = $payload['media_type'] ?? $payload['type'] ?? $data['media_type'] ?? null;
        $filename = $payload['filename'] ?? $payload['file_name'] ?? $data['filename'] ?? null;
        $fileSize = $payload['file_size'] ?? $data['file_size'] ?? null;
        $senderName = $payload['sender_name'] ?? $payload['name'] ?? $payload['pushname'] ?? $data['sender_name'] ?? $data['name'] ?? null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName() ?: ('file_'.time());
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

        // Quoted reply parser from text
        $parsedQuote = $this->parseQuotedFromText((string) $message);
        $message = $parsedQuote['cleanText'];
        $replyTo = $parsedQuote['replyTo'];

        $entry = [
            'messageId' => $data['message_id'] ?? ('in_'.uniqid()),
            'phone' => $cleanPhone ?: $rawPhone,
            'message' => $message,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
            'filename' => $filename,
            'file_size' => $fileSize,
            'reply_to' => $replyTo,
            'timestamp' => now()->toIso8601String(),
            'status' => 'received',
            'direction' => 'inbound',
            'sender' => 'PIC',
            'senderName' => $senderName,
            'gateway' => 'WATZAP',
            'raw' => $data,
        ];

        try {
            if ($cleanPhone) {
                $chat = WhatsAppChat::firstOrCreate(
                    ['phone_number' => $cleanPhone],
                    [
                        'customer_name' => $senderName ?: ('PIC '.$cleanPhone),
                        'folder' => 'all',
                        'unread_count' => 0,
                    ]
                );
                $chat->update([
                    'last_message' => $message,
                    'last_message_at' => now(),
                    'unread_count' => $chat->unread_count + 1,
                ]);
                WhatsAppMessage::create([
                    'whatsapp_chat_id' => $chat->id,
                    'message_id' => $entry['messageId'],
                    'sender_type' => 'customer',
                    'agent_name' => $senderName,
                    'message' => $message,
                    'media_url' => $mediaUrl,
                    'media_type' => $mediaType,
                    'reply_to' => $replyTo,
                    'raw_payload' => $data,
                    'status' => 'received',
                ]);
            }
        } catch (\Throwable) {
        }

        try {
            $messages[] = $entry;
            $trimmed = array_slice($messages, -1000);
            @File::put($msgFile, json_encode($trimmed, JSON_PRETTY_PRINT));
        } catch (\Throwable) {
        }

        return response()->json([
            'status' => '200',
            'message' => 'Webhook received',
            'entry' => $entry,
        ]);
    }

    public function omniResolve(Request $request): JsonResponse
    {
        $localFile = $this->storagePath.DIRECTORY_SEPARATOR.'omni_resolved_chats.json';

        if ($request->isMethod('get')) {
            $resolvedMap = [];
            try {
                $chats = WhatsAppChat::where('is_resolved', true)->get(['phone_number', 'resolved_at', 'resolved_by']);
                foreach ($chats as $c) {
                    $phone = $c->phone_number;
                    if ($phone) {
                        $resolvedMap[$phone] = [
                            'isResolved' => true,
                            'resolvedAt' => $c->resolved_at ? $c->resolved_at->getTimestampMs() : (time() * 1000),
                            'resolvedBy' => $c->resolved_by ?: 'Admin',
                        ];
                    }
                }
            } catch (\Throwable) {
                if (File::exists($localFile)) {
                    $resolvedMap = json_decode(File::get($localFile), true) ?: [];
                }
            }

            return response()->json([
                'success' => true,
                'resolvedChats' => $resolvedMap,
            ]);
        }

        // Mode POST: Toggle status resolved chat
        $body = $request->all();
        $rawPhone = $body['phone'] ?? $body['picKey'] ?? '';
        $phone = preg_replace('/\D/', '', (string) $rawPhone);
        if (str_starts_with($phone, '08')) {
            $phone = '62'.substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62'.$phone;
        }

        if (! $phone || strlen($phone) < 9) {
            return response()->json(['success' => false, 'error' => 'Nomor telepon tidak valid'], 400);
        }

        $isResolved = $request->boolean('is_resolved') || $request->boolean('isResolved') || ($body['action'] ?? '') === 'resolve';
        $resolvedBy = trim((string) ($body['resolvedBy'] ?? ($request->user()?->name ?: 'Admin')));
        $now = now();

        try {
            WhatsAppChat::updateOrCreate(
                ['phone_number' => $phone],
                [
                    'customer_name' => 'PIC '.$phone,
                    'is_resolved' => $isResolved,
                    'resolved_by' => $resolvedBy,
                    'resolved_at' => $isResolved ? $now : null,
                ]
            );
        } catch (\Throwable) {
        }

        try {
            $localData = File::exists($localFile) ? json_decode(File::get($localFile), true) ?: [] : [];
            if ($isResolved) {
                $localData[$phone] = [
                    'isResolved' => true,
                    'resolvedAt' => $now->getTimestampMs(),
                    'resolvedBy' => $resolvedBy,
                ];
            } else {
                unset($localData[$phone]);
            }
            @File::put($localFile, json_encode($localData, JSON_PRETTY_PRINT));
        } catch (\Throwable) {
        }

        return response()->json([
            'success' => true,
            'phone' => $phone,
            'is_resolved' => $isResolved,
            'isResolved' => $isResolved,
            'resolvedAt' => $now->getTimestampMs(),
            'resolvedBy' => $resolvedBy,
        ]);
    }

    public function watzapMedia(Request $request, WatzapService $watzapService): Response
    {
        $file = ltrim((string) $request->query('file', ''), '/\\');
        if ($file === '') {
            return response('Parameter file is required', 400);
        }

        try {
            $session = $watzapService->getWahaSession();
            $path = (str_starts_with($file, 'default/') || str_starts_with($file, "{$session}/"))
                ? $file
                : "{$session}/{$file}";

            $wahaUrl = $watzapService->getWahaUrl().'/api/files/'.$path;
            $res = Http::timeout(10)
                ->withHeaders(['X-Api-Key' => $watzapService->getWahaApiKey()])
                ->get($wahaUrl);

            if (! $res->successful()) {
                $fallbackUrl = $watzapService->getWahaUrl().'/api/files/'.$file;
                $res = Http::timeout(10)
                    ->withHeaders(['X-Api-Key' => $watzapService->getWahaApiKey()])
                    ->get($fallbackUrl);
            }

            if (! $res->successful()) {
                return response('Failed to fetch file from WAHA: '.$res->status(), $res->status());
            }

            $contentType = $res->header('Content-Type') ?: 'application/octet-stream';

            return response($res->body(), 200, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        } catch (\Throwable $e) {
            return response('Internal media proxy error: '.$e->getMessage(), 500);
        }
    }

    /**
     * Parse smart WhatsApp quoted text (> *Sender*:\n> message\n\nReply)
     *
     * @return array{cleanText: string, replyTo: array{senderName: string, text: string}|null}
     */
    protected function parseQuotedFromText(?string $rawText): array
    {
        if (! $rawText) {
            return ['cleanText' => '', 'replyTo' => null];
        }

        $trimmed = trim($rawText);
        if (! str_starts_with($trimmed, '>')) {
            return ['cleanText' => $rawText, 'replyTo' => null];
        }

        if (preg_match('/^>(?: \*(.*?)\*:?)?\s*([\s\S]*?)\n\n([\s\S]*)$/', $trimmed, $m1)) {
            $sender = trim($m1[1] ?: 'Pesan');
            $quoted = trim(preg_replace('/^>\s?/m', '', $m1[2]));
            $actual = trim($m1[3]);

            return [
                'cleanText' => $actual,
                'replyTo' => ['senderName' => $sender, 'text' => $quoted],
            ];
        }

        if (preg_match('/^> \[(.*?)\]\s*([\s\S]*?)\n\n([\s\S]*)$/', $trimmed, $m2)) {
            $sender = trim($m2[1] ?: 'Pesan');
            $quoted = trim(preg_replace('/^>\s?/m', '', $m2[2]));
            $actual = trim($m2[3]);

            return [
                'cleanText' => $actual,
                'replyTo' => ['senderName' => $sender, 'text' => $quoted],
            ];
        }

        if (preg_match('/^> ([\s\S]*?)\n\n([\s\S]*)$/', $trimmed, $m3)) {
            $quoted = trim(preg_replace('/^>\s?/m', '', $m3[1]));
            $actual = trim($m3[2]);

            return [
                'cleanText' => $actual,
                'replyTo' => ['senderName' => 'Pesan', 'text' => $quoted],
            ];
        }

        return ['cleanText' => $rawText, 'replyTo' => null];
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
            $filename = $file->getClientOriginalName() ?: ('sim_'.time());
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
            'messageId' => 'sim_in_'.uniqid(),
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
            $msgFile = $this->storagePath.DIRECTORY_SEPARATOR.'watzap_messages.json';
            $messages = File::exists($msgFile) ? json_decode(File::get($msgFile), true) ?? [] : [];
            $messages[] = $entry;
            $trimmed = array_slice($messages, -1000);
            @File::put($msgFile, json_encode($trimmed, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
        }

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
        $path = $this->storagePath.DIRECTORY_SEPARATOR.$targetFileName;

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

        // Check if database has custom file uploaded by user
        $dbMtime = (int) (Setting::where('key', "{$prefix}_mtime")->value('value') ?? 0);
        $uploader = Setting::where('key', "{$prefix}_uploader")->value('value') ?? 'Superadmin';

        if ($dbMtime > 0) {
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
        $targetPath = $this->storagePath.DIRECTORY_SEPARATOR.$targetFileName;

        $content = $request->getContent();
        if (empty($content)) {
            return response()->json(['error' => 'Data file kosong.'], 400);
        }

        @File::put($targetPath, $content);

        $nowMs = (string) round(microtime(true) * 1000);
        $prefix = $fileType === 'croselling' ? 'workspace_croselling' : 'workspace_turnover';
        try {
            Setting::updateOrCreate(['key' => "{$prefix}_mtime"], ['value' => $nowMs]);
            Setting::updateOrCreate(['key' => "{$prefix}_uploader"], ['value' => $user->name]);
            Setting::updateOrCreate(['key' => "{$prefix}_filesize"], ['value' => (string) strlen($content)]);
            try {
                Setting::updateOrCreate(['key' => "{$prefix}_file_base64"], ['value' => base64_encode($content)]);
            } catch (\Throwable $b64Err) {
                \Illuminate\Support\Facades\Log::warning("Workspace saveExcel base64 warning: {$b64Err->getMessage()}");
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Workspace saveExcel error: {$e->getMessage()}");
        }

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

        // Save to public workspace folder (skip writing real static file in testing environment)
        $targetFileName = $fileType === 'croselling' ? 'CROSSELLING-12102026.xlsx' : 'turnover.xlsx';
        $targetPath = $this->storagePath.DIRECTORY_SEPARATOR.$targetFileName;
        if (! app()->environment('testing')) {
            @File::put($targetPath, $fullBuffer);
        }

        $nowMs = (string) round(microtime(true) * 1000);
        $prefix = $fileType === 'croselling' ? 'workspace_croselling' : 'workspace_turnover';
        try {
            Setting::updateOrCreate(['key' => "{$prefix}_mtime"], ['value' => $nowMs]);
            Setting::updateOrCreate(['key' => "{$prefix}_uploader"], ['value' => $user->name]);
            Setting::updateOrCreate(['key' => "{$prefix}_filename"], ['value' => $originalName]);
            Setting::updateOrCreate(['key' => "{$prefix}_filesize"], ['value' => (string) strlen($fullBuffer)]);
            try {
                Setting::updateOrCreate(['key' => "{$prefix}_file_base64"], ['value' => base64_encode($fullBuffer)]);
            } catch (\Throwable $b64Err) {
                \Illuminate\Support\Facades\Log::warning("Workspace uploadExcelChunk base64 warning: {$b64Err->getMessage()}");
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Workspace uploadExcelChunk error: {$e->getMessage()}");
        }

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

        $isGuest = $request->user()?->isGuest();
        if ($isGuest) {
            $results = $results->map(function ($row) {
                $row->no_hp_customer = \App\Support\DataMasker::maskPhone($row->no_hp_customer ?? null);
                $row->harga_jual = 0;
                return $row;
            });
        }

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
        $format = $request->query('format');
        $sosmedOnly = $request->boolean('sosmed_only') || $request->query('sosmed_only') === '1';
        $limit = min((int) ($request->query('limit', 100)), 500);

        $query = DB::table('turnovers');

        // Pastikan hanya membaca batch turnover terbaru per periode/label agar tidak terjadi dobel perhitungan
        $activeTurnoverImportIds = DB::table('turnover_imports')
            ->where('status', 'completed')
            ->groupBy('batch_label')
            ->selectRaw('MAX(id) as id')
            ->pluck('id');

        if ($activeTurnoverImportIds->isNotEmpty()) {
            $query->whereIn('turnover_import_id', $activeTurnoverImportIds);
        }

        if ($date) {
            $query->whereDate('posting_date', $date);
        } elseif ($month) {
            if (is_numeric($month) && strlen((string) $month) <= 2) {
                $query->whereMonth('posting_date', (int) $month);
            } else {
                $query->where('period_label', (string) $month);
            }
        }

        $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($sales && $sales !== 'all') {
            $query->where('slp_name', $likeOp, "%{$sales}%");
        }

        if ($sosmedOnly) {
            $query->where(function ($sub) use ($likeOp) {
                $sub->where('slp_name', $likeOp, '%bjm.sos%')
                    ->orWhere('remarks', $likeOp, '%bjm.sos%')
                    ->orWhere('sales_cabang', $likeOp, '%bjm.sos%');
            });
        }

        $isGuest = $request->user()?->isGuest();

        if ($format === 'client') {
            $clientLimit = (int) $request->query('limit', 0);
            if ($clientLimit > 0) {
                $query->limit(min($clientLimit, 50000));
            }

            $rows = $query->orderBy('posting_date', 'asc')->get()->map(function ($r) use ($isGuest) {
                return [
                    'Cabang' => $r->cabang ?? '',
                    'Posting Date' => $r->posting_date,
                    'Document Number' => $r->document_number,
                    'Sales Employee Name' => $r->slp_name,
                    'Remarks' => $r->remarks ?? '',
                    'SalesCabang' => $r->sales_cabang ?? '',
                    'DPP' => $isGuest ? 0 : (float) $r->dpp,
                    'GrossProfit' => $isGuest ? 0 : (float) $r->gross_profit,
                    'Total' => (float) $r->total,
                ];
            });

            return response()->json([
                'success' => true,
                'total_count' => $rows->count(),
                'rows' => $rows,
            ]);
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

    /**
     * Endpoint API JSON untuk membaca rekaman cross-selling langsung dari Database SQL MySQL.
     * Mendukung auto-lookup cepat O(1) untuk Report 2 & Tab 3 Cek AR tanpa harus download 6.6MB file Excel.
     */
    public function crosellingRecords(Request $request): JsonResponse
    {
        $month = $request->query('month');
        $format = $request->query('format');
        $sosmedOnly = $request->boolean('sosmed_only') || $request->query('sosmed_only') === '1';

        $query = DB::table('cross_sellings');

        // Pastikan hanya membaca batch cross-selling terbaru per periode/label
        $activeCrossImportIds = DB::table('cross_selling_imports')
            ->where('status', 'completed')
            ->groupBy('batch_label')
            ->selectRaw('MAX(id) as id')
            ->pluck('id');

        if ($activeCrossImportIds->isNotEmpty()) {
            $query->whereIn('cross_selling_import_id', $activeCrossImportIds);
        }

        if ($month) {
            if (is_numeric($month) && strlen((string) $month) <= 2) {
                $query->whereMonth('posting_date', (int) $month);
            } else {
                $query->where('period_label', (string) $month);
            }
        }

        // Jika sosmed_only=1, hanya ambil baris yang document_number-nya tercatat di turnover sosmed
        if ($sosmedOnly) {
            $sosmedDocNumbers = DB::table('turnovers')
                ->where(function ($sub) {
                    $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
                    $sub->where('slp_name', $likeOp, '%bjm.sos%')
                        ->orWhere('remarks', $likeOp, '%bjm.sos%')
                        ->orWhere('sales_cabang', $likeOp, '%bjm.sos%');
                })
                ->whereNotNull('document_number')
                ->distinct()
                ->pluck('document_number');

            if ($sosmedDocNumbers->isNotEmpty()) {
                $query->whereIn('document_number', $sosmedDocNumbers);
            }
        }

        $isGuest = $request->user()?->isGuest();

        if ($format === 'client') {
            $rows = $query->orderBy('posting_date', 'asc')->get()->map(function ($r) use ($isGuest) {
                return [
                    'Document Number' => $r->document_number,
                    'document_number' => $r->document_number,
                    'ItemCode' => $r->item_code,
                    'Item Code' => $r->item_code,
                    'Dscription' => $r->dscription,
                    'Item/Service Description' => $r->dscription,
                    'ItmsGrpNam' => $r->itms_grp_nam,
                    'Group Name' => $r->group ?: $r->itms_grp_nam,
                    'Group' => $r->group ?: $r->itms_grp_nam,
                    'Harga Jual' => $isGuest ? 0 : (float) $r->harga_jual,
                    'Total' => $isGuest ? 0 : (float) $r->harga_jual,
                    'GrossProfit' => $isGuest ? 0 : (float) $r->gross_profit,
                    'Posting Date' => $r->posting_date,
                    'no_hp_customer' => $isGuest ? \App\Support\DataMasker::maskPhone($r->no_hp_customer ?? null) : $r->no_hp_customer,
                    'name' => $r->name,
                    'Customer/Vendor Name' => $r->customer_vendor_name,
                    'Sales Employee Name' => $r->slp_name,
                    'Cabang' => $r->cabang,
                ];
            });

            return response()->json([
                'success' => true,
                'total_count' => $rows->count(),
                'rows' => $rows,
            ]);
        }

        $totalCount = (clone $query)->count();
        $limit = min((int) ($request->query('limit', 100)), 500);
        $rows = $query->orderBy('posting_date', 'desc')->limit($limit)->get();

        return response()->json([
            'success' => true,
            'total_count' => $totalCount,
            'rows' => $rows,
        ]);
    }
}
