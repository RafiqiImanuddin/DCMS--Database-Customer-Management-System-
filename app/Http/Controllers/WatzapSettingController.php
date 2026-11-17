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
}
