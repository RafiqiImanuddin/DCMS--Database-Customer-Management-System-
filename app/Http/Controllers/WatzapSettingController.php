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
    /**
     * Tampilan utama Pengaturan WhatsApp Gateway (Baileys Bot).
     */
    public function index(Request $request, WatzapService $watzap): View
    {
        abort_unless($request->user()->canAccessFeature('watzap_settings'), 403, 'Akses terbatas: Fitur Setup WhatsApp Gateway tidak diaktifkan untuk akun Anda.');

        $isSuperAdmin = $request->user()->isSuperAdmin();

        // Kredensial Baileys Gateway (One Gateway WhatsApp DCMS)
        $baileysBotEnabled = $watzap->isBaileysEnabled();
        $baileysBotUrl = $watzap->getBaileysUrl();
        $baileysBotApiKey = $watzap->getBaileysApiKey();
        $baileysStatus = $watzap->getBaileysStatus(forceFresh: true);
        $baileysAuthSavedAt = Setting::get('baileys_auth_saved_at');
        $baileysHasSavedSession = ! empty(Setting::get('baileys_auth_session'));

        if ($request->user()->isGuest()) {
            $baileysBotApiKey = $baileysBotApiKey ? '********' : '';
        }

        return view('settings.watzap', [
            'baileysBotEnabled' => $baileysBotEnabled,
            'baileysBotUrl' => $baileysBotUrl,
            'baileysBotApiKey' => $baileysBotApiKey,
            'baileysStatus' => $baileysStatus,
            'baileysAuthSavedAt' => $baileysAuthSavedAt,
            'baileysHasSavedSession' => $baileysHasSavedSession,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    /**
     * Simpan konfigurasi Baileys Gateway.
     */
    public function update(Request $request, WatzapService $watzap): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat mengubah pengaturan ini.');

        $validated = $request->validate([
            'baileys_bot_enabled' => ['nullable', 'in:0,1'],
            'baileys_bot_url' => ['nullable', 'string', 'max:255'],
            'baileys_bot_api_key' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::set('baileys_bot_enabled', $validated['baileys_bot_enabled'] ?? '1');

        if ($request->has('baileys_bot_url')) {
            Setting::set('baileys_bot_url', trim($validated['baileys_bot_url'] ?? 'http://127.0.0.1:3000'));
        }
        if ($request->has('baileys_bot_api_key')) {
            Setting::set('baileys_bot_api_key', trim($validated['baileys_bot_api_key'] ?? ''));
        }

        // Hapus cache status agar ter-refresh
        cache()->forget('baileys_status_cache_v2');

        return redirect()->route('settings.watzap.index')->with(
            'success',
            'Pengaturan WhatsApp Gateway (Baileys) berhasil disimpan.',
        );
    }

    /**
     * Uji coba kirim pesan WhatsApp menggunakan Baileys Bot.
     */
    public function testSend(Request $request, WatzapService $watzap): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:9', 'max:25'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $res = $watzap->sendOmnichannel($validated['phone'], $validated['message']);

        if (! empty($res['success'])) {
            return redirect()->route('settings.watzap.index')->with(
                'success',
                "Uji coba kirim pesan WhatsApp berhasil ke {$validated['phone']} via Baileys Bot! (" . ($res['message'] ?? 'Terkirim') . ')',
            );
        }

        return redirect()->route('settings.watzap.index')->with(
            'error',
            'Gagal mengirim pesan uji coba: ' . ($res['message'] ?? 'Periksa koneksi WhatsApp Bot.'),
        );
    }

    /**
     * AJAX endpoint untuk mengecek status dan QR Code Baileys secara real-time.
     */
    public function checkBaileys(Request $request, WatzapService $watzap): JsonResponse
    {
        $res = $watzap->getBaileysStatus(forceFresh: true);

        return response()->json([
            'success' => $res['online'],
            'status' => $res['status'],
            'phone' => $res['phone'] ?? null,
            'name' => $res['name'] ?? null,
            'message' => $res['message'],
            'hasQR' => $res['hasQR'] ?? false,
            'qr' => $res['qr'] ?? null,
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    /**
     * AJAX endpoint untuk mereset sesi WhatsApp Baileys dan memunculkan QR Code baru.
     */
    public function resetBaileys(Request $request, WatzapService $watzap): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat mereset sesi WhatsApp.');

        $result = $watzap->resetBaileysSession();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Tampilan Menu "Log Render Baileys" di Pengaturan.
     */
    public function baileysLogs(Request $request, WatzapService $watzap): View
    {
        abort_unless($request->user()->canAccessFeature('watzap_settings') || $request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        $logsData = $watzap->getBaileysLogs();
        $isSuperAdmin = $request->user()->isSuperAdmin();

        return view('settings.baileys-logs', [
            'logsData' => $logsData,
            'isSuperAdmin' => $isSuperAdmin,
            'baileysBotUrl' => $watzap->getBaileysUrl(),
        ]);
    }

    /**
     * AJAX endpoint untuk fetch live logs data secara realtime.
     */
    public function baileysLogsData(Request $request, WatzapService $watzap): JsonResponse
    {
        abort_unless($request->user()->canAccessFeature('watzap_settings') || $request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        $logsData = $watzap->getBaileysLogs();

        return response()->json($logsData);
    }

    /**
     * AJAX endpoint pemicu instan (Manual Trigger) Cron Follow-Up H+1.
     */
    public function triggerCron(Request $request, \App\Services\KeepAutoFollowUpService $service): JsonResponse
    {
        abort_unless($request->user()->canAccessFeature('watzap_settings') || $request->user()->isSuperAdmin(), 403, 'Akses terbatas.');

        if ($request->user()->isGuest()) {
            return response()->json(['success' => false, 'message' => 'Akun Guest tidak diizinkan memicu cron.'], 403);
        }

        try {
            @set_time_limit(300);
            $results = $service->sendStrict24hAlerts(force: $request->boolean('force', false));

            return response()->json([
                'success' => true,
                'message' => $results['message'] ?? 'Cron berhasil dipicu.',
                'data' => $results,
                'executed_at' => now()->format('d/m/Y H:i:s') . ' WITA',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memicu cron: ' . $e->getMessage(),
            ], 500);
        }
    }
}
