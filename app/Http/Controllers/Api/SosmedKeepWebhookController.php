<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SosmedKeep;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SosmedKeepWebhookController extends Controller
{
    public function __construct(
        protected FonnteService $fonnteService
    ) {}

    /**
     * Webhook Handler Fonnte & Direct API (GET / POST compatible)
     * Menggantikan doGet dan doPost dari Google Apps Script secara realtime ke SQL.
     */
    public function handle(Request $request): Response|JsonResponse
    {
        $action = strtoupper((string) ($request->input('action') ?? ''));

        // =========================================================================
        // 1. UPDATE STATUS DARI DASHBOARD (WIN / LOSS / ON PROGRESS)
        // =========================================================================
        if ($action === 'UPDATE_STATUS') {
            $id = $request->input('id');
            $nomorWA = $request->input('nomorWA') ?? $request->input('nomor_wa');
            $status = $request->input('status');
            $feedback = $request->input('feedback') ?? $request->input('feedbackTimFU');

            return response()->json($this->updateStatusKeep($nomorWA, $status, $feedback, $id));
        }

        // =========================================================================
        // 2. UPDATE REPORT AR DARI DASHBOARD
        // =========================================================================
        if ($action === 'UPDATE_REPORT_AR') {
            $id = $request->input('id');
            $nomorWA = $request->input('nomorWA') ?? $request->input('nomor_wa');
            $arDoc = $request->input('arDoc') ?? $request->input('reportAR') ?? $request->input('report_ar');

            return response()->json($this->updateReportAR($nomorWA, $arDoc, $id));
        }

        // =========================================================================
        // 3. FITUR BOT BAILEYS / VPS: AUTO-CLAIM REACTION JEMPOL (UPDATE KOLOM AC & STATUS ON PROGRESS)
        // =========================================================================
        if ($action === 'UPDATE_REACTION_FU') {
            $messageId = $request->input('message_id') ?? $request->input('messageId');
            $custPhone = $request->input('customerPhone') ?? $request->input('customer_phone') ?? $request->input('nomorWA');
            $nomorFU = $request->input('nomorFU') ?? $request->input('nomor_fu');
            $salesName = $request->input('salesName') ?? $request->input('sales_name') ?? $request->input('nama_sales');
            $id = $request->input('id');

            return response()->json($this->updateNomorFUByCustomerPhone($custPhone, $nomorFU, $messageId, $salesName, $id));
        }

        // =========================================================================
        // 4. FITUR BATAL JEMPOL: KOSONGKAN KOLOM AC & KEMBALIKAN STATUS KE PENDING
        // =========================================================================
        if ($action === 'CLEAR_REACTION_FU') {
            $messageId = $request->input('message_id') ?? $request->input('messageId');
            $custPhone = $request->input('customerPhone') ?? $request->input('customer_phone') ?? $request->input('nomorWA');
            $nomorFU = $request->input('nomorFU') ?? $request->input('nomor_fu');
            $id = $request->input('id');

            return response()->json($this->clearNomorFUByCustomerPhone($custPhone, $nomorFU, $messageId, $id));
        }

        // =========================================================================
        // 4B. FITUR HAPUS BARIS KEEP (KOMPATIBEL DENGAN TRIGGER ONEDIT SHEET)
        // =========================================================================
        if ($action === 'DELETE_KEEP' || $action === 'HAPUS_KEEP') {
            $custPhone = $request->input('customerPhone') ?? $request->input('customer_phone') ?? $request->input('nomorWA');
            $id = $request->input('id');

            return response()->json($this->deleteKeep($custPhone, $id));
        }

        // =========================================================================
        // 5. FORMAT KEEP / KREDIT DARI FONNTE + INPUT MESSAGE ID
        // =========================================================================
        $message = trim((string) $request->input('message', ''));
        if ($message === '') {
            return response('IGNORED', 200)->header('Content-Type', 'text/plain');
        }

        // Filter rekap harian
        if (preg_match('/total chat sosmed|yang handle|alasan lost\/deal/i', $message)) {
            Log::info('Fonnte Webhook Ignored: DAILY_REPORT', ['sender' => $request->input('sender')]);
            return response('IGNORED_DAILY_REPORT', 200)->header('Content-Type', 'text/plain');
        }

        // Filter wajib ada kata remaks/remarks/kode sosmed/sosmed
        if (! preg_match('/remar?ks?|kode\s*sosmed|admin\s*sosmed|cs\s*sosmed|\bsosmed\s*\d+/i', $message)) {
            Log::info('Fonnte Webhook Ignored: NO_REMARKS', [
                'sender' => $request->input('sender'),
                'preview' => substr($message, 0, 100),
            ]);
            return response('IGNORED_NO_REMAKS', 200)->header('Content-Type', 'text/plain');
        }

        $keep = $this->parseKeep($message);

        if (! isset($keep['remaks']) || trim($keep['remaks']) === '') {
            Log::info('Fonnte Webhook Ignored: NO_REMAKS_AFTER_PARSE', [
                'sender' => $request->input('sender'),
                'preview' => substr($message, 0, 100),
            ]);
            return response('IGNORED_NO_REMAKS', 200)->header('Content-Type', 'text/plain');
        }

        $isMarketplace = (bool) preg_match('/shopee|tokopedia|tiktok/i', $message);
        if (! $isMarketplace && (! isset($keep['nomor']) || trim($keep['nomor']) === '')) {
            // Toleransi jika nomor WA dikosongkan (misal saat sampling/testing atau CS belum isi),
            // tetap izinkan simpan jika nama atau barang sudah terisi agar data format keep tidak hilang.
            if (empty($keep['nama']) && empty($keep['barang'])) {
                Log::info('Fonnte Webhook Ignored: NO_NOMOR_WA_AND_NO_DATA', [
                    'sender' => $request->input('sender'),
                    'preview' => substr($message, 0, 100),
                ]);
                return response('IGNORED_NO_NOMOR_WA', 200)->header('Content-Type', 'text/plain');
            }
        }

        $rawSender = (string) ($request->input('sender') ?? '');
        $rawMember = (string) ($request->input('member') ?? '');
        $isGroup = filter_var($request->input('isgroup', false), FILTER_VALIDATE_BOOLEAN)
            || (bool) $request->input('isGroup')
            || $rawMember !== ''
            || str_contains($rawSender, '@g.us')
            || str_contains($rawSender, '-');

        $nomorPengirim = $isGroup ? ($rawMember ?: $rawSender) : $rawSender;
        $groupId = $isGroup ? $rawSender : '';
        $jenisChat = $isGroup ? 'GRUP' : 'PRIBADI';
        $messageId = $request->input('inboxid') ?: $request->input('id') ?: '';
        $senderName = $request->input('name') ?: '';

        // Tentukan periode (e.g. November-26)
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $now = now('Asia/Makassar');
        if ($reqTs = $request->input('timestamp')) {
            try {
                if (is_numeric($reqTs)) {
                    $now = Carbon::createFromTimestamp((int) $reqTs, 'Asia/Makassar');
                } else {
                    $now = Carbon::parse($reqTs)->setTimezone('Asia/Makassar');
                }
            } catch (\Throwable) {}
        }
        $periode = ($monthNames[$now->month] ?? 'Bulan').'-'.substr((string) $now->year, -2);
        $resolvedCabangGroup = $this->fonnteService->resolveCabangAndGroup(
            $groupId,
            $request->input('group_name'),
            $keep['remaks'] ?? ''
        );
        $cabang = $resolvedCabangGroup['cabang'] ?? '';
        $grupName = $resolvedCabangGroup['grup'] !== ''
            ? $resolvedCabangGroup['grup']
            : ($request->input('group_name') ?: ($isGroup ? 'GRUP' : ''));

        // Deteksi transaksi Kredit vs Non Kredit
        $isKredit = (bool) preg_match('/format\s*kredit|kredit|pembiayaan|leasing|angsuran|cicilan/i', $message);
        $userKredit = $isKredit ? 'KREDIT' : 'NON KREDIT';

        // Deteksi brand / item dari nama barang
        $b = strtoupper($keep['barang'] ?? '');
        $item = '';
        if (str_contains($b, 'IPHONE') || str_contains($b, 'APPLE') || str_contains($b, 'IPAD') || str_contains($b, 'MACBOOK')) {
            $item = 'IPHONE';
        } elseif (str_contains($b, 'SAMSUNG')) {
            $item = 'SAMSUNG';
        } elseif (str_contains($b, 'OPPO')) {
            $item = 'OPPO';
        } elseif (str_contains($b, 'VIVO')) {
            $item = 'VIVO';
        } elseif (str_contains($b, 'POCO')) {
            $item = 'POCO';
        } elseif (str_contains($b, 'REDMI') || str_contains($b, 'XIAOMI')) {
            $item = 'XIAOMI';
        } elseif (str_contains($b, 'REALME')) {
            $item = 'REALME';
        } elseif (str_contains($b, 'INFINIX')) {
            $item = 'INFINIX';
        } elseif (str_contains($b, 'TECNO')) {
            $item = 'TECNO';
        } elseif (str_contains($b, 'ITEL')) {
            $item = 'ITEL';
        } elseif (str_contains($b, 'HUAWEI')) {
            $item = 'HUAWEI';
        } elseif (str_contains($b, 'ASUS') || str_contains($b, 'ROG')) {
            $item = 'ASUS';
        }

        $newKeep = SosmedKeep::create([
            'message_id' => $messageId,
            'keep_timestamp' => $now->format('Y-m-d H:i:s'),
            'nomor_sender' => (string) $nomorPengirim,
            'grup_id' => (string) $groupId,
            'sender' => (string) $senderName,
            'grup' => (string) $grupName,
            'jenis_chat' => $jenisChat,
            'nama' => $keep['nama'] ?? '',
            'nomor_wa' => $keep['nomor'] ?? '',
            'barang_keep' => $keep['barang'] ?? '',
            'tanggal_ambil' => $keep['tanggal'] ?? '',
            'catatan' => $keep['catatan'] ?? '',
            'remarks' => $keep['remaks'] ?? '',
            'kode_sosmed' => $keep['remaks'] ?? '',
            'cabang' => $cabang,
            'periode' => $periode,
            'status' => 'PENDING',
            'user_kredit' => $userKredit,
            'item' => $item,
        ]);

        Log::info("Fonnte Webhook Saved Keep ID: {$newKeep->id}", [
            'nama' => $newKeep->nama,
            'nomor_wa' => $newKeep->nomor_wa,
            'barang' => $newKeep->barang_keep,
            'remarks' => $newKeep->remarks,
            'cabang' => $newKeep->cabang,
        ]);

        // Simpan juga ke file CSV keep-data.csv untuk backward compatibility
        try {
            $csvFile = public_path('workspace-sosmed' . DIRECTORY_SEPARATOR . 'keep-data.csv');
            if (File::exists($csvFile)) {
                $fp = fopen($csvFile, 'a');
                if ($fp) {
                    fputcsv($fp, [
                        $now->format('Y-m-d H:i:s'),
                        (string) $nomorPengirim,
                        (string) $groupId,
                        (string) $senderName,
                        (string) $grupName,
                        $jenisChat,
                        $keep['nama'] ?? '',
                        $keep['nomor'] ?? '',
                        $keep['barang'] ?? '',
                        $keep['tanggal'] ?? '',
                        $keep['catatan'] ?? '',
                        $keep['remaks'] ?? '',
                        $keep['remaks'] ?? '',
                        '', // report ar
                        '', // tanggal input ar
                        '', // ar vlookup
                        '', // tanggal vlookup
                        $cabang,
                        '', // evidence
                        '', // nomor fu
                        '', // nama tim fu
                        'PENDING',
                        '', // feedback
                        '', // toko kompetitor
                        '', // user kredit
                        $periode,
                        '', // item
                        '', // nomor fu
                    ]);
                    fclose($fp);
                }
            }
        } catch (\Throwable) {}

        // Kirim konfirmasi balasan WA via Fonnte jika fitur diaktifkan
        $autoReplyEnabled = (string) Setting::get('fonnte_autoreply_enabled', '1') !== '0';
        $senderTarget = (string) ($request->input('sender') ?: $nomorPengirim);

        if ($autoReplyEnabled && $senderTarget !== '') {
            $defaultTpl = "✅ Data KEEP berhasil direkap.\n\nNama : {nama}\nNomor WA : {nomor}\nBarang : {barang}\nTanggal Pengambilan : {tanggal}\nRemaks : {remaks}";
            $template = Setting::get('fonnte_autoreply_message', $defaultTpl);
            $balasan = str_replace(
                ['{nama}', '{nomor}', '{barang}', '{tanggal}', '{remaks}'],
                [$keep['nama'] ?: '-', $keep['nomor'] ?: '-', $keep['barang'] ?: '-', $keep['tanggal'] ?: '-', $keep['remaks'] ?: '-'],
                $template
            );

            $this->fonnteService->sendMessage($senderTarget, $balasan, $isGroup);
        }

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * API Get Keeps untuk Frontend Workspace Sosmed
     */
    public function getKeeps(Request $request): JsonResponse
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('sosmed_keeps')) {
                return response()->json([
                    'success' => true,
                    'total' => 0,
                    'data' => [],
                    'message' => 'Tabel sosmed_keeps belum dibuat.',
                ]);
            }

            $count = SosmedKeep::count();
            if ($count === 0 && file_exists(public_path('workspace-sosmed' . DIRECTORY_SEPARATOR . 'keep-data.csv'))) {
                try {
                    @set_time_limit(180);
                    \Illuminate\Support\Facades\Artisan::call('keep:import-csv');
                } catch (\Throwable $impErr) {
                    \Illuminate\Support\Facades\Log::warning('Auto import CSV in getKeeps error: ' . $impErr->getMessage());
                }
            }

            $query = SosmedKeep::query();

            if ($periode = $request->input('periode')) {
                if ($periode !== 'ALL') {
                    $query->where('periode', $periode);
                }
            }

            if ($cabang = $request->input('cabang')) {
                if ($cabang !== 'ALL') {
                    $query->where('cabang', $cabang);
                }
            }

            if ($status = $request->input('status')) {
                if ($status !== 'ALL') {
                    $query->where('status', $status);
                }
            }

            if ($search = $request->input('q')) {
                $digits = preg_replace('/\D/', '', $search);
                $query->where(function ($q) use ($search, $digits) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('barang_keep', 'like', "%{$search}%")
                        ->orWhere('report_ar', 'like', "%{$search}%")
                        ->orWhere('nama_tim_follow_up', 'like', "%{$search}%");
                    if ($digits !== '') {
                        $q->orWhere('nomor_wa', 'like', "%{$digits}%")
                            ->orWhere('nomor_fu', 'like', "%{$digits}%")
                            ->orWhere('nomor_follow_up', 'like', "%{$digits}%");
                    }
                });
            }

            $limit = (int) $request->input('limit', 3000);
            if ($limit <= 0 || $limit > 50000) {
                $limit = 3000;
            }

            $fields = [
                'id', 'keep_timestamp', 'nomor_sender', 'grup_id', 'sender', 'grup', 'jenis_chat',
                'nama', 'nomor_wa', 'barang_keep', 'tanggal_ambil', 'catatan', 'remarks',
                'kode_sosmed', 'report_ar', 'tanggal_input_ar', 'ar_vlookup', 'tanggal_vlookup',
                'cabang', 'evidence', 'nomor_follow_up', 'nama_tim_follow_up', 'status',
                'feedback_tim_fu', 'nama_toko_kompetitor', 'user_kredit', 'periode', 'item', 'nomor_fu'
            ];

            $existingCols = \Illuminate\Support\Facades\Schema::getColumnListing('sosmed_keeps');
            $validFields = ! empty($existingCols) ? array_values(array_intersect($fields, $existingCols)) : ['*'];

            $keeps = $query->latest('id')->limit($limit)->toBase()->get($validFields);

            if ($request->user()?->isGuest()) {
                $keeps = $keeps->map(function ($k) {
                    $arr = (array) $k;
                    if (isset($arr['nomor_wa'])) {
                        $arr['nomor_wa'] = \App\Support\DataMasker::maskPhone($arr['nomor_wa']);
                    }
                    if (isset($arr['nomor_sender'])) {
                        $arr['nomor_sender'] = \App\Support\DataMasker::maskPhone($arr['nomor_sender']);
                    }
                    if (isset($arr['nomor_follow_up'])) {
                        $arr['nomor_follow_up'] = \App\Support\DataMasker::maskPhone($arr['nomor_follow_up']);
                    }
                    if (isset($arr['nomor_fu'])) {
                        $arr['nomor_fu'] = \App\Support\DataMasker::maskPhone($arr['nomor_fu']);
                    }
                    return (object) $arr;
                });
            }

            return response()->json([
                'success' => true,
                'total' => $keeps->count(),
                'data' => $keeps,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('getKeeps error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'total' => 0,
                'data' => [],
            ]);
        }
    }

    /**
     * API Polling Ringan untuk Deteksi Keep Baru secara Realtime (Auto-Update & Push Notification)
     */
    public function getLatestKeeps(Request $request): JsonResponse
    {
        $afterId = (int) $request->input('after_id', 0);
        $maxId = (int) SosmedKeep::max('id');
        $updatedSince = $request->input('updated_since');

        $fields = [
            'id', 'keep_timestamp', 'nomor_sender', 'grup_id', 'sender', 'grup', 'jenis_chat',
            'nama', 'nomor_wa', 'barang_keep', 'tanggal_ambil', 'catatan', 'remarks',
            'kode_sosmed', 'report_ar', 'tanggal_input_ar', 'ar_vlookup', 'tanggal_vlookup',
            'cabang', 'evidence', 'nomor_follow_up', 'nama_tim_follow_up', 'status',
            'feedback_tim_fu', 'nama_toko_kompetitor', 'user_kredit', 'periode', 'item', 'nomor_fu',
            'updated_at'
        ];

        $existingCols = \Illuminate\Support\Facades\Schema::getColumnListing('sosmed_keeps');
        $validFields = ! empty($existingCols) ? array_values(array_intersect($fields, $existingCols)) : ['*'];

        $newKeeps = collect();
        if ($afterId > 0 && $maxId > $afterId) {
            $newKeeps = SosmedKeep::where('id', '>', $afterId)
                ->latest('id')
                ->limit(30)
                ->toBase()
                ->get($validFields);
        }

        $updatedKeeps = collect();
        if (! empty($updatedSince)) {
            try {
                $sinceDt = \Carbon\Carbon::parse($updatedSince);
                $updatedKeeps = SosmedKeep::where('updated_at', '>=', $sinceDt)
                    ->where(function ($q) use ($afterId) {
                        if ($afterId > 0) {
                            $q->where('id', '<=', $afterId);
                        }
                    })
                    ->latest('updated_at')
                    ->limit(30)
                    ->toBase()
                    ->get($validFields);
            } catch (\Throwable) {
                // Ignore parse errors
            }
        }

        if ($request->user()?->isGuest()) {
            $maskFn = function ($k) {
                $arr = (array) $k;
                if (isset($arr['nomor_wa'])) {
                    $arr['nomor_wa'] = \App\Support\DataMasker::maskPhone($arr['nomor_wa']);
                }
                if (isset($arr['nomor_sender'])) {
                    $arr['nomor_sender'] = \App\Support\DataMasker::maskPhone($arr['nomor_sender']);
                }
                if (isset($arr['nomor_follow_up'])) {
                    $arr['nomor_follow_up'] = \App\Support\DataMasker::maskPhone($arr['nomor_follow_up']);
                }
                if (isset($arr['nomor_fu'])) {
                    $arr['nomor_fu'] = \App\Support\DataMasker::maskPhone($arr['nomor_fu']);
                }
                return (object) $arr;
            };
            $newKeeps = $newKeeps->map($maskFn);
            $updatedKeeps = $updatedKeeps->map($maskFn);
        }

        return response()->json([
            'success' => true,
            'count' => $newKeeps->count(),
            'latest_id' => $maxId,
            'server_time' => now('Asia/Makassar')->toIso8601String(),
            'data' => $newKeeps,
            'updated' => $updatedKeeps,
        ]);
    }

    /**
     * Update Status Keep (WIN / CLOSING / LOSS / ON PROGRESS)
     */
    private function updateStatusKeep(?string $targetPhone, ?string $newStatus, ?string $feedback = null, $id = null): array
    {
        if ((! $targetPhone && ! $id) || ! $newStatus) {
            return ['success' => false, 'message' => 'Parameter nomorWA/ID atau status kosong'];
        }

        $keep = null;
        if ($id) {
            $keep = SosmedKeep::find($id);
        }

        if (! $keep && $targetPhone) {
            $cleanPhone = preg_replace('/\D/', '', (string) $targetPhone);
            if ($cleanPhone !== '') {
                $last9 = substr($cleanPhone, -9);
                $keep = SosmedKeep::where(function ($q) use ($cleanPhone, $last9) {
                    $q->where('nomor_wa', $cleanPhone)
                        ->orWhere('nomor_wa', 'like', "%{$last9}");
                })->latest('id')->first();
            }
        }

        if (! $keep) {
            return ['success' => false, 'message' => 'Data keep tidak ditemukan di database.'];
        }

        $keep->status = trim($newStatus);
        if ($feedback !== null && $feedback !== '') {
            $keep->feedback_tim_fu = trim($feedback);
        }
        $keep->save();

        return [
            'success' => true,
            'rowUpdated' => $keep->id,
            'status' => $keep->status,
            'message' => "Status keep ID {$keep->id} berhasil diperbarui menjadi {$keep->status}",
        ];
    }

    /**
     * Update Report AR
     */
    private function updateReportAR(?string $targetPhone, ?string $arDoc, $id = null): array
    {
        if (! $targetPhone && ! $id) {
            return ['success' => false, 'message' => 'Parameter nomorWA atau ID kosong'];
        }

        $cleanARDoc = trim((string) $arDoc);
        $keep = null;
        if ($id) {
            $keep = SosmedKeep::find($id);
        }

        if (! $keep && $targetPhone) {
            $cleanPhone = preg_replace('/\D/', '', (string) $targetPhone);
            if ($cleanPhone !== '') {
                $last9 = substr($cleanPhone, -9);
                $keep = SosmedKeep::where(function ($q) use ($cleanPhone, $last9) {
                    $q->where('nomor_wa', $cleanPhone)
                        ->orWhere('nomor_wa', 'like', "%{$last9}");
                })->latest('id')->first();
            }
        }

        if (! $keep) {
            return ['success' => false, 'message' => 'Data keep tidak ditemukan di database.'];
        }

        $keep->report_ar = $cleanARDoc;
        $keep->tanggal_input_ar = $cleanARDoc !== '' ? now() : null;
        $keep->save();

        return [
            'success' => true,
            'rowUpdated' => $keep->id,
            'reportAR' => $keep->report_ar,
            'message' => "Report AR keep ID {$keep->id} berhasil diupdate: {$keep->report_ar}",
        ];
    }

    /**
     * Update Reaction FU Bot Baileys / VPS (Auto-Claim Logika Siapa Cepat Dia Dapat)
     */
    private function updateNomorFUByCustomerPhone(?string $customerPhone, ?string $nomorFU, ?string $messageId = null, ?string $salesName = null, mixed $id = null): array
    {
        if (! $customerPhone && ! $messageId && ! $id) {
            return ['success' => false, 'message' => 'Parameter pencarian (Nomor Customer, Message ID, atau ID Keep) kosong'];
        }

        if (! $nomorFU) {
            return ['success' => false, 'message' => 'Nomor WhatsApp staf follow up (nomor_fu) kosong'];
        }

        $cleanNomorFU = preg_replace('/\D/', '', (string) $nomorFU);
        if ($cleanNomorFU === '') {
            $cleanNomorFU = trim((string) $nomorFU);
        }

        // Cari record keep: 1) by ID, 2) by message_id WhatsApp, 3) by nomor_wa customer (Bottom-Up)
        $keep = null;
        if ($id) {
            $keep = SosmedKeep::find($id);
        }

        if (! $keep && $messageId) {
            $keep = SosmedKeep::where('message_id', $messageId)->first();
        }

        if (! $keep && $customerPhone) {
            $cleanCustPhone = preg_replace('/\D/', '', (string) $customerPhone);
            if ($cleanCustPhone !== '') {
                $last9 = substr($cleanCustPhone, -9);
                $keep = SosmedKeep::where(function ($q) use ($cleanCustPhone, $last9) {
                    $q->where('nomor_wa', $cleanCustPhone)
                        ->orWhere('nomor_wa', 'like', "%{$last9}");
                })->latest('id')->first();
            }
        }

        if (! $keep) {
            $target = $messageId ? "Message ID {$messageId}" : ($customerPhone ?? "ID {$id}");
            return ['success' => false, 'message' => "Data transaksi format keep ({$target}) tidak ditemukan di database."];
        }

        // =========================================================================
        // ATURAN "SIAPA CEPAT DIA DAPAT": Jika sudah diklaim orang lain, tolak klaim baru!
        // =========================================================================
        $existingCleanFU = preg_replace('/\D/', '', (string) $keep->nomor_fu);
        $incomingCleanFU = preg_replace('/\D/', '', (string) $cleanNomorFU);

        if (! empty($existingCleanFU) && $existingCleanFU !== $incomingCleanFU) {
            $holder = $keep->nama_tim_follow_up ?: $keep->nomor_fu;
            return [
                'success' => false,
                'already_claimed' => true,
                'claimed_by' => $keep->nomor_fu,
                'claimed_by_name' => $keep->nama_tim_follow_up,
                'message' => "Format keep ini sudah diklaim lebih dulu oleh {$holder}.",
                'row' => $keep->id,
                'customer' => $keep->nomor_wa,
            ];
        }

        // Deteksi Nama Sales otomatis dari master tabel employees jika nama belum disediakan
        if (empty($salesName)) {
            $last9FU = substr($incomingCleanFU, -9);
            $emp = \App\Models\Employee::where(function ($q) use ($incomingCleanFU, $last9FU) {
                $q->where('phone', $incomingCleanFU);
                if ($last9FU !== '') {
                    $q->orWhere('phone', 'like', "%{$last9FU}");
                }
            })->first();

            if ($emp) {
                $salesName = $emp->name;
            }
        }

        $keep->nomor_fu = $cleanNomorFU;
        if ($salesName) {
            $keep->nama_tim_follow_up = $salesName;
        }

        // Otomatis ubah status transaksi menjadi 'ON PROGRESS' jika belum closing/lost
        $currStatus = strtoupper(trim((string) $keep->status));
        if (empty($currStatus) || in_array($currStatus, ['PENDING', 'BELUM DIINPUT', 'NEW'], true)) {
            $keep->status = 'ON PROGRESS';
        }
        $keep->save();

        $picDisplayName = $keep->nama_tim_follow_up ?: $cleanNomorFU;

        return [
            'success' => true,
            'claimed' => true,
            'message' => "Format keep berhasil diklaim oleh {$picDisplayName} (Baris ID {$keep->id})",
            'row' => $keep->id,
            'customer' => $keep->nomor_wa,
            'pic' => $cleanNomorFU,
            'pic_name' => $keep->nama_tim_follow_up,
            'status' => $keep->status,
        ];
    }

    /**
     * Clear Reaction FU Bot Baileys / VPS (Batal Jempol)
     */
    private function clearNomorFUByCustomerPhone(?string $customerPhone, ?string $nomorFU = null, ?string $messageId = null, mixed $id = null): array
    {
        if (! $customerPhone && ! $messageId && ! $id) {
            return ['success' => false, 'message' => 'Parameter pencarian (Nomor Customer, Message ID, atau ID Keep) kosong'];
        }

        // Cari record keep
        $keep = null;
        if ($id) {
            $keep = SosmedKeep::find($id);
        }

        if (! $keep && $messageId) {
            $keep = SosmedKeep::where('message_id', $messageId)->first();
        }

        if (! $keep && $customerPhone) {
            $cleanCustPhone = preg_replace('/\D/', '', (string) $customerPhone);
            if ($cleanCustPhone !== '') {
                $last9 = substr($cleanCustPhone, -9);
                $keep = SosmedKeep::where(function ($q) use ($cleanCustPhone, $last9) {
                    $q->where('nomor_wa', $cleanCustPhone)
                        ->orWhere('nomor_wa', 'like', "%{$last9}");
                })->latest('id')->first();
            }
        }

        if (! $keep) {
            return ['success' => false, 'message' => 'Data format keep tidak ditemukan untuk dikosongkan.'];
        }

        // Validasi pembatal: Jika nomorFU dikirim, pastikan yang membatalkan adalah yang sebelumnya mengunci
        if ($nomorFU) {
            $existingCleanFU = preg_replace('/\D/', '', (string) $keep->nomor_fu);
            $incomingCleanFU = preg_replace('/\D/', '', (string) $nomorFU);
            if (! empty($existingCleanFU) && $existingCleanFU !== $incomingCleanFU) {
                return [
                    'success' => false,
                    'message' => 'Anda tidak dapat membatalkan klaim follow-up milik sales lain.',
                ];
            }
        }

        $prevPIC = $keep->nama_tim_follow_up ?: $keep->nomor_fu;
        $keep->nomor_fu = null;
        $keep->nama_tim_follow_up = null;

        // Jika statusnya saat ini ON PROGRESS, kembalikan menjadi PENDING
        if (strtoupper(trim((string) $keep->status)) === 'ON PROGRESS') {
            $keep->status = 'PENDING';
        }
        $keep->save();

        return [
            'success' => true,
            'unclaimed' => true,
            'message' => "Klaim follow up oleh {$prevPIC} pada baris ID {$keep->id} berhasil dibatalkan.",
            'row' => $keep->id,
            'customer' => $keep->nomor_wa,
        ];
    }

    /**
     * Hapus Baris Keep (Kompatibel dengan aksi Hapus di Google Spreadsheet)
     */
    private function deleteKeep(?string $customerPhone, mixed $id = null): array
    {
        $query = SosmedKeep::query();

        if ($id) {
            $keep = $query->find($id);
        } elseif ($customerPhone) {
            $cleanPhone = preg_replace('/\D/', '', (string) $customerPhone);
            $last9 = substr($cleanPhone, -9);
            $keep = $query->where(function ($q) use ($cleanPhone, $last9) {
                $q->where('nomor_wa', $cleanPhone)
                    ->orWhere('nomor_wa', 'like', "%{$last9}");
            })->latest('id')->first();
        } else {
            return ['success' => false, 'message' => 'Parameter ID atau nomorWA kosong'];
        }

        if (! $keep) {
            return ['success' => false, 'message' => 'Data keep tidak ditemukan'];
        }

        $rowId = $keep->id;
        $keep->delete();

        return [
            'success' => true,
            'message' => "Data keep ID {$rowId} berhasil dihapus dari database SQL.",
            'deletedId' => $rowId,
        ];
    }

    /**
     * Parser Pesan Keep dari Format WhatsApp
     */
    public function parseKeep(string $text): array
    {
        return $this->fonnteService->parseKeep($text);
    }

    /**
     * Kirim Balasan WhatsApp via Fonnte API
     */
    private function kirimWA(string $target, string $pesan, bool $isGroup = false): void
    {
        $this->fonnteService->sendMessage($target, $pesan, $isGroup);
    }
}
