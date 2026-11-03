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

        $apiKey = Setting::get('watzap_api_key', (string) config('services.watzap.api_key', ''));
        $numberKey = Setting::get('watzap_number_key', (string) config('services.watzap.number_key', ''));
        $webUrl = Setting::get('watzap_web_url', (string) config('services.watzap.web_url', 'https://watzap.chat'));

        $deviceStatus = null;
        if ($watzap->isConfigured()) {
            $deviceStatus = $watzap->getDeviceStatus();
        }

        return view('settings.watzap', [
            'apiKey' => $apiKey,
            'numberKey' => $numberKey,
            'webUrl' => $webUrl,
            'isConfigured' => $watzap->isConfigured(),
            'deviceStatus' => $deviceStatus,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat mengubah pengaturan ini.');

        $validated = $request->validate([
            'watzap_api_key' => ['nullable', 'string', 'max:255'],
            'watzap_number_key' => ['nullable', 'string', 'max:255'],
            'watzap_web_url' => ['nullable', 'url', 'max:255'],
        ]);

        Setting::set('watzap_api_key', trim($validated['watzap_api_key'] ?? ''));
        Setting::set('watzap_number_key', trim($validated['watzap_number_key'] ?? ''));
        Setting::set('watzap_web_url', trim($validated['watzap_web_url'] ?? 'https://watzap.chat'));

        return redirect()->route('settings.watzap.index')->with(
            'success',
            'Pengaturan WatZap.id berhasil disimpan.',
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

        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $res = $watzap->sendMessage($validated['phone'], $validated['message']);

        if ($res['success']) {
            return redirect()->route('settings.watzap.index')->with(
                'success',
                'Uji coba kirim pesan berhasil ke '.$validated['phone'].' via WatZap! ('.$res['message'].')',
            );
        }

        return redirect()->route('settings.watzap.index')->with(
            'error',
            'Uji coba gagal: '.$res['message'].($res['raw'] ? ' | '.$res['raw'] : ''),
        );
    }
}
