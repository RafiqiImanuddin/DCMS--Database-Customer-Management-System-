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
            $nomorWA = $request->input('nomorWA') ?? $request->input('nomor_wa');
            $status = $request->input('status');
            $feedback = $request->input('feedback');

            return response()->json($this->updateStatusKeep($nomorWA, $status, $feedback));
        }

        // =========================================================================
        // 2. UPDATE REPORT AR DARI DASHBOARD
        // =========================================================================
        if ($action === 'UPDATE_REPORT_AR') {
            $nomorWA = $request->input('nomorWA') ?? $request->input('nomor_wa');
            $arDoc = $request->input('arDoc') ?? $request->input('reportAR') ?? $request->input('report_ar');

            return response()->json($this->updateReportAR($nomorWA, $arDoc));
        }

        // =========================================================================
        // 3. FITUR BOT VPS: PENCARIAN BOTTOM-UP BERDASARKAN NO HP CUSTOMER (UPDATE KOLOM AC)
        // =========================================================================
        if ($action === 'UPDATE_REACTION_FU') {
            $custPhone = $request->input('customerPhone') ?? $request->input('customer_phone') ?? $request->input('nomorWA');
            $nomorFU = $request->input('nomorFU') ?? $request->input('nomor_fu');

            return response()->json($this->updateNomorFUByCustomerPhone($custPhone, $nomorFU));
        }

        // =========================================================================
        // 4. FITUR BATAL JEMPOL: KOSONGKAN KOLOM AC BERDASARKAN NO HP CUSTOMER
        // =========================================================================
        if ($action === 'CLEAR_REACTION_FU') {
            $custPhone = $request->input('customerPhone') ?? $request->input('customer_phone') ?? $request->input('nomorWA');

            return response()->json($this->clearNomorFUByCustomerPhone($custPhone));
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
            return response('IGNORED_DAILY_REPORT', 200)->header('Content-Type', 'text/plain');
        }

        // Filter wajib ada kata remaks/remarks
        if (! preg_match('/remar?ks/i', $message)) {
            return response('IGNORED_NO_REMAKS', 200)->header('Content-Type', 'text/plain');
        }

        $keep = $this->parseKeep($message);

        if (! isset($keep['remaks']) || trim($keep['remaks']) === '') {
            return response('IGNORED_NO_REMAKS', 200)->header('Content-Type', 'text/plain');
        }

        $isMarketplace = (bool) preg_match('/shopee|tokopedia|tiktok/i', $message);
        if (! $isMarketplace && (! isset($keep['nomor']) || trim($keep['nomor']) === '')) {
            return response('IGNORED_NO_NOMOR_WA', 200)->header('Content-Type', 'text/plain');
        }

        $isGroup = filter_var($request->input('isgroup', false), FILTER_VALIDATE_BOOLEAN) || (bool) $request->input('isGroup');
        $nomorPengirim = $isGroup ? ($request->input('member') ?: $request->input('sender')) : $request->input('sender');
        $groupId = $isGroup ? $request->input('sender') : '';
        $jenisChat = $isGroup ? 'GRUP' : 'PRIBADI';
        $messageId = $request->input('inboxid') ?: $request->input('id') ?: '';
        $senderName = $request->input('name') ?: '';

        // Tentukan periode (e.g. November-26)
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $now = now();
        $periode = ($monthNames[$now->month] ?? 'Bulan').'-'.substr((string) $now->year, -2);
        $cabang = $this->fonnteService->detectCabangFromRemarks($keep['remaks'] ?? '');

        $newKeep = SosmedKeep::create([
            'message_id' => $messageId,
            'keep_timestamp' => $now,
            'nomor_sender' => (string) $nomorPengirim,
            'grup_id' => (string) $groupId,
            'sender' => (string) $senderName,
            'grup' => $request->input('group_name') ?: ($isGroup ? 'GRUP' : ''),
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
                        $request->input('group_name') ?: ($isGroup ? 'GRUP' : ''),
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
                    ->orWhere('report_ar', 'like', "%{$search}%");
                if ($digits !== '') {
                    $q->orWhere('nomor_wa', 'like', "%{$digits}%");
                }
            });
        }

        $limit = (int) $request->input('limit', 25000);
        if ($limit <= 0 || $limit > 50000) {
            $limit = 25000;
        }

        $fields = [
            'id', 'keep_timestamp', 'nomor_sender', 'grup_id', 'sender', 'grup', 'jenis_chat',
            'nama', 'nomor_wa', 'barang_keep', 'tanggal_ambil', 'catatan', 'remarks',
            'kode_sosmed', 'report_ar', 'tanggal_input_ar', 'ar_vlookup', 'tanggal_vlookup',
            'cabang', 'evidence', 'nomor_follow_up', 'nama_tim_follow_up', 'status',
            'feedback_tim_fu', 'nama_toko_kompetitor', 'user_kredit', 'periode', 'item', 'nomor_fu'
        ];

        $keeps = $query->latest('id')->limit($limit)->toBase()->get($fields);

        return response()->json([
            'success' => true,
            'total' => $keeps->count(),
            'data' => $keeps,
        ]);
    }

    /**
     * Update Status Keep (WIN / CLOSING / LOSS / ON PROGRESS)
     */
    private function updateStatusKeep(?string $targetPhone, ?string $newStatus, ?string $feedback = null): array
    {
        if (! $targetPhone || ! $newStatus) {
            return ['success' => false, 'message' => 'Parameter nomorWA atau status kosong'];
        }

        $cleanPhone = preg_replace('/\D/', '', (string) $targetPhone);
        if ($cleanPhone === '') {
            return ['success' => false, 'message' => 'Nomor WhatsApp tidak valid'];
        }
        $last9 = substr($cleanPhone, -9);

        $keep = SosmedKeep::where(function ($q) use ($cleanPhone, $last9) {
            $q->where('nomor_wa', $cleanPhone)
                ->orWhere('nomor_wa', 'like', "%{$last9}");
        })->latest('id')->first();

        if (! $keep) {
            return ['success' => false, 'message' => "Nomor WA {$cleanPhone} tidak ditemukan di database."];
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
    private function updateReportAR(?string $targetPhone, ?string $arDoc): array
    {
        if (! $targetPhone) {
            return ['success' => false, 'message' => 'Parameter nomorWA kosong'];
        }

        $cleanPhone = preg_replace('/\D/', '', (string) $targetPhone);
        $last9 = substr($cleanPhone, -9);
        $cleanARDoc = trim((string) $arDoc);

        $keep = SosmedKeep::where(function ($q) use ($cleanPhone, $last9) {
            $q->where('nomor_wa', $cleanPhone)
                ->orWhere('nomor_wa', 'like', "%{$last9}");
        })->latest('id')->first();

        if (! $keep) {
            return ['success' => false, 'message' => "Nomor WA {$cleanPhone} tidak ditemukan di database."];
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
     * Update Reaction FU Bot VPS (isi Kolom AC / nomor_fu)
     */
    private function updateNomorFUByCustomerPhone(?string $customerPhone, ?string $nomorFU): array
    {
        if (! $customerPhone || ! $nomorFU) {
            return ['success' => false, 'message' => 'Nomor Customer atau Nomor FU kosong'];
        }

        $cleanCustPhone = preg_replace('/\D/', '', (string) $customerPhone);
        $last9 = substr($cleanCustPhone, -9);
        $cleanNomorFU = trim((string) $nomorFU);

        // Bottom-Up Search: Order by ID DESC (transaksi terbaru)
        $keep = SosmedKeep::where(function ($q) use ($cleanCustPhone, $last9) {
            $q->where('nomor_wa', $cleanCustPhone)
                ->orWhere('nomor_wa', 'like', "%{$last9}");
        })->latest('id')->first();

        if (! $keep) {
            return ['success' => false, 'message' => "Nomor Customer {$customerPhone} tidak ditemukan di database."];
        }

        $keep->nomor_fu = $cleanNomorFU;
        $keep->save();

        return [
            'success' => true,
            'message' => "Berhasil dicatat ke database baris ID {$keep->id}",
            'row' => $keep->id,
            'customer' => $cleanCustPhone,
            'pic' => $cleanNomorFU,
        ];
    }

    /**
     * Clear Reaction FU Bot VPS (Batal Jempol)
     */
    private function clearNomorFUByCustomerPhone(?string $customerPhone): array
    {
        if (! $customerPhone) {
            return ['success' => false, 'message' => 'Nomor Customer kosong'];
        }

        $cleanCustPhone = preg_replace('/\D/', '', (string) $customerPhone);
        $last9 = substr($cleanCustPhone, -9);

        $keep = SosmedKeep::where(function ($q) use ($cleanCustPhone, $last9) {
            $q->where('nomor_wa', $cleanCustPhone)
                ->orWhere('nomor_wa', 'like', "%{$last9}");
        })->latest('id')->first();

        if (! $keep) {
            return ['success' => false, 'message' => 'Nomor Customer tidak ditemukan untuk dikosongkan.'];
        }

        $keep->nomor_fu = null;
        $keep->save();

        return [
            'success' => true,
            'message' => "Nomor FU untuk baris ID {$keep->id} berhasil dikosongkan.",
        ];
    }

    /**
     * Parser Pesan Keep dari Format WhatsApp
     */
    private function parseKeep(string $text): array
    {
        $result = ['nama' => '', 'nomor' => '', 'barang' => '', 'tanggal' => '', 'catatan' => '', 'remaks' => ''];
        if ($text === '') {
            return $result;
        }

        $cleanText = str_replace(["\xC2\xA0", "\r\n", "\r"], [' ', "\n", "\n"], $text);
        $lines = explode("\n", $cleanText);
        $currentField = '';

        foreach ($lines as $rawLine) {
            $rawLine = trim($rawLine);
            if ($rawLine === '') {
                continue;
            }

            $plainLine = trim(preg_replace('/[*_~]/', '', $rawLine));

            if (preg_match('/^(tanggal\s*keep|tgl\s*keep)/i', $plainLine)) {
                $currentField = '';
                continue;
            }

            $colonIdx = strpos($plainLine, ':');
            if ($colonIdx !== false) {
                $key = strtolower(trim(substr($plainLine, 0, $colonIdx)));
                $rawColonIdx = strpos($rawLine, ':');
                $val = trim(preg_replace('/^[*_~]+|[*_~]+$/', '', substr($rawLine, $rawColonIdx + 1)));

                if ($key === 'nama') {
                    $currentField = 'nama';
                    $result['nama'] = $val;
                } elseif (in_array($key, ['nomor wa', 'no wa', 'nomor', 'no hp', 'telepon', 'hp'], true)) {
                    $currentField = 'nomor';
                    $result['nomor'] = $val;
                } elseif (in_array($key, ['barang', 'barang keep'], true)) {
                    $currentField = 'barang';
                    $result['barang'] = $val;
                } elseif (in_array($key, ['tanggal', 'tanggal pengambilan'], true)) {
                    $currentField = 'tanggal';
                    $result['tanggal'] = $val;
                } elseif ($key === 'catatan') {
                    $currentField = 'catatan';
                    $result['catatan'] = $val;
                } elseif ($key === 'remaks' || $key === 'remarks') {
                    $currentField = 'remaks';
                    $result['remaks'] = $val;
                } else {
                    $currentField = '';
                }
            } elseif ($currentField !== '') {
                $result[$currentField] .= "\n".trim(preg_replace('/^[*_~]+|[*_~]+$/', '', $rawLine));
            }
        }

        // Ekstraksi Khusus Field Remaks (Kode SOSMED)
        if ($result['remaks'] !== '') {
            $cleanRemaks = preg_replace('/[*_~]/', '', $result['remaks']);
            $cleanRemaks = preg_replace('/@\S+/', '', $cleanRemaks);
            $cleanRemaks = trim((string) preg_replace('/\s+/', ' ', $cleanRemaks));

            if (str_contains($cleanRemaks, '-')) {
                $parts = explode('-', $cleanRemaks);
                $cleanRemaks = trim(end($parts));
            }

            $result['remaks'] = $cleanRemaks;
        }

        // Pembersihan Khusus Nomor WA
        if ($result['nomor'] !== '') {
            $cleanNum = preg_replace('/\D/', '', (string) $result['nomor']);
            if (str_starts_with($cleanNum, '0')) {
                $cleanNum = '62'.substr($cleanNum, 1);
            }
            $result['nomor'] = $cleanNum;
        }

        return $result;
    }

    /**
     * Kirim Balasan WhatsApp via Fonnte API
     */
    private function kirimWA(string $target, string $pesan, bool $isGroup = false): void
    {
        $this->fonnteService->sendMessage($target, $pesan, $isGroup);
    }
}
