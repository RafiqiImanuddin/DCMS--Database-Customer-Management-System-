<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\WatzapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatzapSettingController extends Controller
{
    public function index(Request $request, WatzapService $watzap): View
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();

        $activeTab = $request->query('tab', 'broadcast');
        if (! in_array($activeTab, ['broadcast', 'inbox'], true)) {
            $activeTab = 'broadcast';
        }

        // Kredensial WA Broadcast
        $apiKey = Setting::get('watzap_api_key', (string) config('services.watzap.api_key', ''));
        $numberKey = Setting::get('watzap_number_key', (string) config('services.watzap.number_key', ''));
        $webUrl = Setting::get('watzap_web_url', (string) config('services.watzap.web_url', 'https://watzap.chat'));
        $isConfiguredBroadcast = $watzap->isConfigured('broadcast');
        $deviceStatusBroadcast = $isConfiguredBroadcast ? $watzap->getDeviceStatus('broadcast') : null;

        // Kredensial WA Inbox Follow Up
        $inboxSame = Setting::get('watzap_inbox_same_as_broadcast', '1');
        $inboxApiKey = Setting::get('watzap_inbox_api_key', '');
        $inboxNumberKey = Setting::get('watzap_inbox_number_key', '');
        $isConfiguredInbox = $watzap->isConfigured('inbox');
        $deviceStatusInbox = $isConfiguredInbox ? $watzap->getDeviceStatus('inbox') : null;

        // Kredensial Dual-Gateway WAHA VPS (Primary Gateway Inbox FU)
        $wahaEnabled = $watzap->isWahaEnabled();
        $wahaUrl = $watzap->getWahaUrl();
        $wahaApiKey = $watzap->getWahaApiKey();
        $wahaSession = $watzap->getWahaSession();
        $wahaStatus = $watzap->getWahaStatus();

        $webhookUrl = url('/api/watzap-webhook');
        $availableSenders = $isConfiguredBroadcast ? $watzap->getAvailableSenders($apiKey) : [];

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
            'availableSenders' => $availableSenders,
            'webhookUrl' => $webhookUrl,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function update(Request $request, WatzapService $watzap): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat mengubah pengaturan ini.');

        $targetTab = $request->input('target_tab', 'broadcast');
        if (! in_array($targetTab, ['broadcast', 'inbox'], true)) {
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

        $label = $targetTab === 'inbox' ? 'WA Inbox Follow Up' : 'WA Broadcast';
        $redirectUrl = $targetTab === 'inbox'
            ? route('settings.watzap.index', ['tab' => 'inbox'])
            : route('settings.watzap.index');

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
}
