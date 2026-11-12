<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SosmedKeep;
use App\Services\FonnteService;
use App\Services\WatzapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class WatzapSettingController extends Controller
{
    public function index(Request $request, WatzapService $watzap, FonnteService $fonnte): View
    {
        abort_unless($request->user()->canAccessFeature('watzap_settings'), 403, 'Akses terbatas: Fitur Setup WhatsApp Gateway tidak diaktifkan untuk akun Anda.');

        $isSuperAdmin = $request->user()->isSuperAdmin();

        $activeTab = $request->query('tab', 'broadcast');
        if (! in_array($activeTab, ['broadcast', 'inbox', 'fonnte'], true)) {
            $activeTab = 'broadcast';
        }

        // Kredensial WA Broadcast
        $apiKey = Setting::get('watzap_api_key', (string) config('services.watzap.api_key', ''));
        $numberKey = Setting::get('watzap_number_key', (string) config('services.watzap.number_key', ''));
        $webUrl = Setting::get('watzap_web_url', (string) config('services.watzap.web_url', 'https://watzap.chat'));
        $isConfiguredBroadcast = $watzap->isConfigured('broadcast');
        $deviceStatusBroadcast = ($activeTab === 'broadcast' && $isConfiguredBroadcast) ? $watzap->getDeviceStatus('broadcast') : null;

        // Kredensial WA Inbox Follow Up
        $inboxSame = Setting::get('watzap_inbox_same_as_broadcast', '1');
        $inboxApiKey = Setting::get('watzap_inbox_api_key', '');
        $inboxNumberKey = Setting::get('watzap_inbox_number_key', '');
        $isConfiguredInbox = $watzap->isConfigured('inbox');
        $deviceStatusInbox = ($activeTab === 'inbox' && $isConfiguredInbox) ? $watzap->getDeviceStatus('inbox') : null;

        // Kredensial Dual-Gateway WAHA VPS (Primary Gateway Inbox FU)
        $wahaEnabled = $watzap->isWahaEnabled();
        $wahaUrl = $watzap->getWahaUrl();
        $wahaApiKey = $watzap->getWahaApiKey();
        $wahaSession = $watzap->getWahaSession();
        $wahaStatus = ($activeTab === 'inbox') ? $watzap->getWahaStatus() : ['online' => false, 'status' => 'Disabled'];

        // Kredensial Fonnte (Bot Keep & Webhook Migrasi Spreadsheet)
        $fonnteToken = Setting::get('fonnte_token', 'NMGid9rgBKYAAyZ7wmSn');
        $fonnteAutoreply = Setting::get('fonnte_autoreply_enabled', '1');
        $fonnteAutoreplyMsg = Setting::get('fonnte_autoreply_message', "✅ Data KEEP berhasil direkap.\n\nNama : {nama}\nNomor WA : {nomor}\nBarang : {barang}\nTanggal Pengambilan : {tanggal}\nRemaks : {remaks}");
        $fonnteWebhookUrl = url('/api/fonnte-webhook');
        $fonnteIsConfigured = ! empty($fonnteToken);
        $fonnteDeviceStatus = ($activeTab === 'fonnte' && $fonnteIsConfigured) ? $fonnte->getDeviceStatus($fonnteToken) : null;

        $webhookUrl = url('/api/watzap-webhook');
        $availableSenders = $isConfiguredBroadcast ? $watzap->getAvailableSenders($apiKey) : [];

        if ($request->user()->isGuest()) {
            $apiKey = $apiKey ? 'wt_sec_********' : '';
            $numberKey = $numberKey ? 'nk_********' : '';
            $inboxApiKey = $inboxApiKey ? 'wt_sec_********' : '';
            $inboxNumberKey = $inboxNumberKey ? 'nk_********' : '';
            $wahaApiKey = $wahaApiKey ? '********' : '';
            $fonnteToken = $fonnteToken ? '********' : '';
            $availableSenders = [];
        }

        return view('settings.watzap', [
            'activeTab' => $activeTab,
            'apiKey' => $apiKey,
            'numberKey' => $numberKey,
            'webUrl' => $webUrl,
            'isConfigured' => $isConfiguredBroadcast,
            'isConfiguredBroadcast' => $isConfiguredBroadcast,
            'deviceStatus' => $deviceStatusBroadcast,
            'deviceStatusBroadcast' => $deviceStatusBroadcast,
            'inboxSame' => $inboxSame,
            'inboxApiKey' => $inboxApiKey,
            'inboxNumberKey' => $inboxNumberKey,
            'isConfiguredInbox' => $isConfiguredInbox,
            'deviceStatusInbox' => $deviceStatusInbox,
            'wahaEnabled' => $wahaEnabled,
            'wahaUrl' => $wahaUrl,
            'wahaApiKey' => $wahaApiKey,
            'wahaSession' => $wahaSession,
            'wahaStatus' => $wahaStatus,
            'fonnteToken' => $fonnteToken,
            'fonnteAutoreply' => $fonnteAutoreply,
            'fonnteAutoreplyMsg' => $fonnteAutoreplyMsg,
            'fonnteWebhookUrl' => $fonnteWebhookUrl,
            'fonnteIsConfigured' => $fonnteIsConfigured,
            'fonnteDeviceStatus' => $fonnteDeviceStatus,
            'totalSosmedKeeps' => SosmedKeep::count(),
            'availableSenders' => $availableSenders,
            'webhookUrl' => $webhookUrl,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function update(Request $request, WatzapService $watzap): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat mengubah pengaturan ini.');

        $targetTab = $request->input('target_tab', 'broadcast');
        if (! in_array($targetTab, ['broadcast', 'inbox', 'fonnte'], true)) {
            $targetTab = 'broadcast';
        }

        $validated = $request->validate([
            'watzap_api_key' => ['nullable', 'string', 'max:255'],
            'watzap_number_key' => ['nullable', 'string', 'max:255'],
            'watzap_web_url' => ['nullable', 'url', 'max:255'],
            'watzap_inbox_same_as_broadcast' => ['nullable', 'in:0,1'],
            'watzap_inbox_api_key' => ['nullable', 'string', 'max:255'],
            'watzap_inbox_number_key' => ['nullable', 'string', 'max:255'],
            'waha_enabled' => ['nullable', 'in:0,1'],
            'waha_vps_url' => ['nullable', 'url', 'max:255'],
            'waha_api_key' => ['nullable', 'string', 'max:255'],
            'waha_session' => ['nullable', 'string', 'max:255'],
            'fonnte_token' => ['nullable', 'string', 'max:255'],
            'fonnte_autoreply_enabled' => ['nullable', 'in:0,1'],
            'fonnte_autoreply_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $masterApiKey = trim($validated['watzap_api_key'] ?? Setting::get('watzap_api_key', ''));

        if ($request->has('watzap_api_key')) {
            Setting::set('watzap_api_key', trim($validated['watzap_api_key'] ?? ''));
        }
        if ($request->has('watzap_number_key')) {
            $rawKey = trim($validated['watzap_number_key'] ?? '');
            $resolved = $rawKey !== '' ? $watzap->resolveNumberKey($rawKey, $masterApiKey ?: null) : '';
            Setting::set('watzap_number_key', $resolved);
        }
        if ($request->has('watzap_web_url')) {
            Setting::set('watzap_web_url', trim($validated['watzap_web_url'] ?? 'https://watzap.chat'));
        }

        if ($request->has('watzap_inbox_same_as_broadcast')) {
            Setting::set('watzap_inbox_same_as_broadcast', $validated['watzap_inbox_same_as_broadcast'] ?? '1');
        }
        if ($request->has('watzap_inbox_api_key')) {
            Setting::set('watzap_inbox_api_key', trim($validated['watzap_inbox_api_key'] ?? ''));
        }
        if ($request->has('watzap_inbox_number_key')) {
            $rawKey = trim($validated['watzap_inbox_number_key'] ?? '');
            $inboxApi = trim($validated['watzap_inbox_api_key'] ?? '') ?: $masterApiKey;
            $resolved = $rawKey !== '' ? $watzap->resolveNumberKey($rawKey, $inboxApi ?: null) : '';
            Setting::set('watzap_inbox_number_key', $resolved);
        }

        // Simpan Konfigurasi WAHA VPS (Primary Gateway Inbox FU)
        if ($request->has('waha_enabled')) {
            Setting::set('waha_enabled', $validated['waha_enabled'] ?? '1');
        }
        if ($request->has('waha_vps_url')) {
            Setting::set('waha_vps_url', trim($validated['waha_vps_url'] ?? 'http://178.83.188.100:3088'));
        }
        if ($request->has('waha_api_key')) {
            Setting::set('waha_api_key', trim($validated['waha_api_key'] ?? ''));
        }
        if ($request->has('waha_session')) {
            Setting::set('waha_session', trim($validated['waha_session'] ?? 'default'));
        }

        // Simpan Konfigurasi Fonnte (Bot Keep & Webhook Migrasi Spreadsheet)
        if ($request->has('fonnte_token')) {
            Setting::set('fonnte_token', trim($validated['fonnte_token'] ?? ''));
        }
        if ($request->has('fonnte_autoreply_enabled')) {
            Setting::set('fonnte_autoreply_enabled', $validated['fonnte_autoreply_enabled'] ?? '1');
        }
        if ($request->has('fonnte_autoreply_message')) {
            Setting::set('fonnte_autoreply_message', trim($validated['fonnte_autoreply_message'] ?? ''));
        }

        $label = match ($targetTab) {
            'inbox' => 'WA Inbox Follow Up',
            'fonnte' => 'Fonnte (Bot Keep & Webhook)',
            default => 'WA Broadcast',
        };
        $redirectUrl = $targetTab === 'broadcast'
            ? route('settings.watzap.index')
            : route('settings.watzap.index', ['tab' => $targetTab]);

        return redirect()->to($redirectUrl)->with(
            'success',
            "Pengaturan {$label} berhasil disimpan.",
        );
    }

    public function check(Request $request, WatzapService $watzap): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        $apiKey = $request->string('api_key')->trim()->toString();
        $res = $watzap->checkingKey($apiKey ?: null);

        return response()->json($res);
    }

    public function testSend(Request $request, WatzapService $watzap): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        $channel = $request->input('channel', 'broadcast');
        if (! in_array($channel, ['broadcast', 'inbox'], true)) {
            $channel = 'broadcast';
        }

        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $res = $watzap->sendMessage($validated['phone'], $validated['message'], $channel);

        $label = $channel === 'inbox' ? 'WA Inbox Follow Up' : 'WA Broadcast';
        $redirectUrl = $channel === 'inbox'
            ? route('settings.watzap.index', ['tab' => 'inbox'])
            : route('settings.watzap.index');

        if ($res['success']) {
            return redirect()->to($redirectUrl)->with(
                'success',
                "Uji coba kirim pesan ({$label}) berhasil ke {$validated['phone']} via WatZap! ({$res['message']})",
            );
        }

        return redirect()->to($redirectUrl)->with(
            'error',
            $res['message'],
        );
    }

    public function registerWebhook(Request $request, WatzapService $watzap): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat mendaftarkan webhook.');

        $channel = $request->input('channel', 'inbox');
        $webhookUrl = url('/api/watzap-webhook');

        $overrideApiKey = $request->string('api_key')->trim()->toString();
        $overrideNumberKey = $request->string('number_key')->trim()->toString();

        if ($overrideNumberKey !== '') {
            $overrideNumberKey = $watzap->resolveNumberKey($overrideNumberKey, $overrideApiKey ?: null);
            if ($channel === 'inbox') {
                Setting::set('watzap_inbox_number_key', $overrideNumberKey);
                if ($overrideApiKey !== '') {
                    Setting::set('watzap_inbox_api_key', $overrideApiKey);
                }
            } else {
                Setting::set('watzap_number_key', $overrideNumberKey);
                if ($overrideApiKey !== '') {
                    Setting::set('watzap_api_key', $overrideApiKey);
                }
            }
        }

        $res = $watzap->setWebhook($webhookUrl, $channel, $overrideApiKey ?: null, $overrideNumberKey ?: null);

        return response()->json($res);
    }

    public function getWebhookStatus(Request $request, WatzapService $watzap): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        $channel = $request->query('channel', 'inbox');
        $overrideApiKey = $request->string('api_key')->trim()->toString();
        $overrideNumberKey = $request->string('number_key')->trim()->toString();

        $res = $watzap->getWebhook($channel, $overrideApiKey ?: null, $overrideNumberKey ?: null);

        return response()->json($res);
    }

    public function checkWaha(Request $request, WatzapService $watzap): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        $session = (string) $request->input('session', $watzap->getWahaSession());
        $res = $watzap->getWahaStatus($session);

        return response()->json([
            'success' => $res['online'],
            'status' => $res['status'],
            'session' => $res['name'],
            'me' => $res['me'],
            'message' => $res['message'],
        ]);
    }

    public function checkFonnte(Request $request, FonnteService $fonnte): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        $token = $request->string('token')->trim()->toString();
        $res = $fonnte->getDeviceStatus($token ?: null, forceRefresh: true);

        return response()->json($res);
    }

    public function registerFonnteWebhook(Request $request, FonnteService $fonnte): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        $webhookUrl = $request->string('webhook_url')->trim()->toString();
        if ($webhookUrl === '') {
            return response()->json(['success' => false, 'message' => 'URL Webhook wajib diisi.'], 422);
        }

        $token = $request->string('token')->trim()->toString() ?: null;
        $res = $fonnte->setWebhook($webhookUrl, $token);

        return response()->json($res);
    }

    public function testSendFonnte(Request $request, FonnteService $fonnte): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $res = $fonnte->sendMessage($validated['phone'], $validated['message']);

        if (! empty($res['success'])) {
            return redirect()->route('settings.watzap.index', ['tab' => 'fonnte'])->with(
                'success',
                "Pesan tes berhasil dikirim ke {$validated['phone']} via Fonnte."
            );
        }

        return redirect()->route('settings.watzap.index', ['tab' => 'fonnte'])->with(
            'error',
            'Pengiriman pesan tes via Fonnte gagal: ' . ($res['message'] ?? 'Periksa token Fonnte Anda.')
        );
    }

    public function syncGoogleSheetKeep(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        $url = $request->string('spreadsheet_url')->trim()->toString()
            ?: 'https://docs.google.com/spreadsheets/d/1V0pIa0NZCprJqRwnSnikFaLZ-dd50nY0uE3bjVYsUe0/edit?gid=848355344#gid=848355344';

        $sheetName = $request->string('sheet_name')->trim()->toString() ?: 'DATA KEEP';

        try {
            $exitCode = Artisan::call('keep:sync-google-sheet', [
                '--url' => $url,
                '--sheet' => $sheetName,
                '--truncate' => $request->boolean('truncate', true),
            ]);

            if ($exitCode === 0) {
                $total = SosmedKeep::count();
                return redirect()->route('settings.watzap.index', ['tab' => 'fonnte'])->with(
                    'success',
                    "Sinkronisasi data Google Spreadsheet berhasil! Total {$total} data keep tersimpan di database MySQL."
                );
            }
        } catch (\Throwable $e) {
            return redirect()->route('settings.watzap.index', ['tab' => 'fonnte'])->with(
                'error',
                'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage()
            );
        }

        return redirect()->route('settings.watzap.index', ['tab' => 'fonnte'])->with(
            'error',
            'Sinkronisasi Google Spreadsheet gagal. Periksa koneksi internet atau hak akses sheet.'
        );
    }
}
