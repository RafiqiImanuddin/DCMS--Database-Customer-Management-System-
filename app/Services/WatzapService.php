<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Throwable;

class WatzapService
{
    public function getApiKey(?string $channel = null): string
    {
        if ($channel === 'inbox' || $channel === 'inbox_fu') {
            $same = Setting::get('watzap_inbox_same_as_broadcast', '1');
            if ($same !== '1') {
                $inboxKey = trim((string) Setting::get('watzap_inbox_api_key', ''));
                if ($inboxKey !== '') {
                    return $inboxKey;
                }
            }
        }

        return trim((string) Setting::get('watzap_api_key', (string) config('services.watzap.api_key', '')));
    }

    public function getNumberKey(?string $channel = null): string
    {
        if ($channel === 'inbox' || $channel === 'inbox_fu') {
            $same = Setting::get('watzap_inbox_same_as_broadcast', '1');
            if ($same !== '1') {
                $inboxNum = trim((string) Setting::get('watzap_inbox_number_key', ''));
                if ($inboxNum !== '') {
                    return $inboxNum;
                }
            }
        }

        return trim((string) Setting::get('watzap_number_key', (string) config('services.watzap.number_key', '')));
    }

    public function getBaseUrl(): string
    {
        return rtrim((string) config('services.watzap.base_url', 'https://api.watzap.id/v1'), '/');
    }

    public function getWebUrl(): string
    {
        $custom = trim((string) Setting::get('watzap_web_url', ''));

        return $custom !== '' ? $custom : (string) config('services.watzap.web_url', 'https://watzap.chat');
    }

    public function isConfigured(?string $channel = null): bool
    {
        return $this->getApiKey($channel) !== '' && $this->getNumberKey($channel) !== '';
    }

    public function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '08')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        if (strlen($digits) < 10 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }

    /**
     * Send a WhatsApp message via WatZap REST API.
     *
     * @return array{success: bool, message: string, status: string, data: array, raw: string}
     */
    public function sendMessage(string $rawPhone, string $message, ?string $channel = null): array
    {
        if (! $this->isConfigured($channel)) {
            return [
                'success' => false,
                'message' => 'WatZap belum dikonfigurasi. Silakan lengkapi API Key dan Number Key di halaman Setup WhatsApp.',
                'status' => 'unconfigured',
                'data' => [],
                'raw' => '',
            ];
        }

        $phone = $this->normalizePhone($rawPhone);
        if (! $phone) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tidak valid (harus 10-15 digit, diawali 08/62).',
                'status' => 'invalid_phone',
                'data' => [],
                'raw' => '',
            ];
        }

        try {
            $url = $this->getBaseUrl().'/send_message';
            $response = Http::timeout(20)
                ->asJson()
                ->acceptJson()
                ->post($url, [
                    'api_key' => $this->getApiKey($channel),
                    'number_key' => $this->getNumberKey($channel),
                    'phone_no' => $phone,
                    'message' => $message,
                ]);

            $json = $response->json();
            $rawBody = $response->body();

            $status = (string) ($json['status'] ?? $response->status());
            $ack = strtolower((string) ($json['ack'] ?? ''));
            $isSuccess = ($status === '200' || $ack === 'successfully');
            $rawMsg = (string) ($json['message'] ?? '');

            if ($isSuccess) {
                $msg = $rawMsg ?: 'Pesan berhasil dikirim via WatZap.';
            } else {
                $waNumber = $json['wa_number'] ?? null;
                if ($status === '1001') {
                    $msg = 'Parameter pengiriman gambar tidak sesuai (Wrong Parameters).';
                } elseif ($status === '1005' || str_contains(strtolower($rawMsg), 'not started')) {
                    $numLabel = $waNumber ? " (Nomor: {$waNumber})" : '';
                    $msg = "WhatsApp Instance Not Started{$numLabel}. Sesi WhatsApp pada nomor ini di WatZap belum berjalan (status Offline). Silakan buka Member Area WatZap (https://app.watzap.id) -> menu WhatsApp (tab API) -> klik Connect dan Scan QR Code dengan WhatsApp di HP Anda agar statusnya Online.";
                } elseif ($status === '1002') {
                    $msg = 'API Key atau Number Key tidak valid / tidak cocok dengan akun WatZap.';
                } elseif ($status === '1003') {
                    $msg = 'Nomor pengirim di WatZap sedang Offline. Silakan buka member area WatZap dan lakukan scan ulang QR code.';
                } elseif ($status === '1004') {
                    $msg = 'Format nomor tujuan tidak valid.';
                } else {
                    $msg = $rawMsg ?: 'Gagal mengirim pesan via WatZap.';
                }
            }

            return [
                'success' => $isSuccess,
                'message' => $msg,
                'status' => $status,
                'data' => is_array($json) ? $json : [],
                'raw' => $rawBody,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi ke WatZap gagal: '.$e->getMessage(),
                'status' => 'connection_error',
                'data' => [],
                'raw' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a document/file with optional caption via WatZap REST API (/send_file_url).
     *
     * @return array{success: bool, message: string, status: string, data: array, raw: string}
     */
    public function sendFile(string $rawPhone, string $fileUrl, ?string $caption = null, ?string $channel = null): array
    {
        if (! $this->isConfigured($channel)) {
            return [
                'success' => false,
                'message' => 'WatZap belum dikonfigurasi. Silakan lengkapi API Key dan Number Key di halaman Setup WhatsApp.',
                'status' => 'unconfigured',
                'data' => [],
                'raw' => '',
            ];
        }

        $phone = $this->normalizePhone($rawPhone);
        if (! $phone) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tidak valid (harus 10-15 digit, diawali 08/62).',
                'status' => 'invalid_phone',
                'data' => [],
                'raw' => '',
            ];
        }

        try {
            $url = $this->getBaseUrl().'/send_file_url';
            $payload = [
                'api_key' => $this->getApiKey($channel),
                'number_key' => $this->getNumberKey($channel),
                'phone_no' => $phone,
                'url' => $fileUrl,
            ];

            if ($caption !== null && trim($caption) !== '') {
                $payload['message'] = $caption;
            }

            $response = Http::timeout(30)
                ->asJson()
                ->acceptJson()
                ->post($url, $payload);

            $json = $response->json();
            $rawBody = $response->body();

            $status = (string) ($json['status'] ?? $response->status());
            $ack = strtolower((string) ($json['ack'] ?? ''));
            $isSuccess = ($status === '200' || $ack === 'successfully');
            $rawMsg = (string) ($json['message'] ?? '');

            return [
                'success' => $isSuccess,
                'message' => $rawMsg ?: ($isSuccess ? 'File berhasil dikirim via WatZap.' : 'Gagal mengirim file via WatZap.'),
                'status' => $status,
                'data' => is_array($json) ? $json : [],
                'raw' => $rawBody,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi ke WatZap gagal: '.$e->getMessage(),
                'status' => 'connection_error',
                'data' => [],
                'raw' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send an image with optional caption via WatZap REST API (/send_image_url).
     *
     * @return array{success: bool, message: string, status: string, data: array, raw: string}
     */
    public function sendImage(string $rawPhone, string $imageUrl, ?string $caption = null, ?string $channel = null): array
    {
        if (! $this->isConfigured($channel)) {
            return [
                'success' => false,
                'message' => 'WatZap belum dikonfigurasi. Silakan lengkapi API Key dan Number Key di halaman Setup WhatsApp.',
                'status' => 'unconfigured',
                'data' => [],
                'raw' => '',
            ];
        }

        $phone = $this->normalizePhone($rawPhone);
        if (! $phone) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tidak valid (harus 10-15 digit, diawali 08/62).',
                'status' => 'invalid_phone',
                'data' => [],
                'raw' => '',
            ];
        }

        try {
            $url = $this->getBaseUrl().'/send_image_url';
            $payload = [
                'api_key' => $this->getApiKey($channel),
                'number_key' => $this->getNumberKey($channel),
                'phone_no' => $phone,
                'url' => $imageUrl,
            ];

            if ($caption !== null && trim($caption) !== '') {
                $payload['message'] = $caption;
            }

            $response = Http::timeout(25)
                ->asJson()
                ->acceptJson()
                ->post($url, $payload);

            $json = $response->json();
            $rawBody = $response->body();

            $status = (string) ($json['status'] ?? $response->status());
            $ack = strtolower((string) ($json['ack'] ?? ''));
            $isSuccess = ($status === '200' || $ack === 'successfully');
            $rawMsg = (string) ($json['message'] ?? '');

            if ($isSuccess) {
                $msg = $rawMsg ?: 'Gambar berhasil dikirim via WatZap.';
            } else {
                $waNumber = $json['wa_number'] ?? null;
                if ($status === '1001') {
                    $msg = 'Parameter pengiriman gambar tidak sesuai (Wrong Parameters).';
                } elseif ($status === '1005' || str_contains(strtolower($rawMsg), 'not started')) {
                    $numLabel = $waNumber ? " (Nomor: {$waNumber})" : '';
                    $msg = "WhatsApp Instance Not Started{$numLabel}. Sesi WhatsApp pada nomor ini di WatZap belum berjalan (status Offline). Silakan buka Member Area WatZap (https://app.watzap.id) -> menu WhatsApp (tab API) -> klik Connect dan Scan QR Code dengan WhatsApp di HP Anda agar statusnya Online.";
                } elseif ($status === '1002') {
                    $msg = 'API Key atau Number Key tidak valid / tidak cocok dengan akun WatZap.';
                } elseif ($status === '1003') {
                    $msg = 'Nomor pengirim di WatZap sedang Offline. Silakan buka member area WatZap dan lakukan scan ulang QR code.';
                } elseif ($status === '1004') {
                    $msg = 'Format nomor tujuan tidak valid.';
                } else {
                    $msg = $rawMsg ?: 'Gagal mengirim gambar via WatZap.';
                }
            }

            return [
                'success' => $isSuccess,
                'message' => $msg,
                'status' => $status,
                'data' => is_array($json) ? $json : [],
                'raw' => $rawBody,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi ke WatZap gagal: '.$e->getMessage(),
                'status' => 'connection_error',
                'data' => [],
                'raw' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate whether a phone number is registered and active on WhatsApp via WatZap REST API.
     *
     * @return array{success: bool, is_valid: bool, message: string, status: string, data: array, raw: string}
     */
    public function validateNumber(string $rawPhone): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'is_valid' => false,
                'message' => 'WatZap belum dikonfigurasi. Silakan isi API Key dan Number Key di halaman Setup WhatsApp.',
                'status' => 'unconfigured',
                'data' => [],
                'raw' => '',
            ];
        }

        $phone = $this->normalizePhone($rawPhone);
        if (! $phone) {
            return [
                'success' => false,
                'is_valid' => false,
                'message' => 'Format nomor WhatsApp tidak valid (harus 10-15 digit, diawali 08/62).',
                'status' => 'invalid_phone',
                'data' => [],
                'raw' => '',
            ];
        }

        try {
            $url = $this->getBaseUrl().'/validate_number';
            $response = Http::timeout(15)
                ->asJson()
                ->acceptJson()
                ->post($url, [
                    'api_key' => $this->getApiKey(),
                    'number_key' => $this->getNumberKey(),
                    'phone_no' => $phone,
                ]);

            $json = $response->json();
            $rawBody = $response->body();

            $status = (string) ($json['status'] ?? $response->status());
            $ack = strtolower((string) ($json['ack'] ?? ''));
            $isSuccess = ($status === '200' || $ack === 'successfully');
            $rawMsg = (string) ($json['message'] ?? '');

            return [
                'success' => $isSuccess,
                'is_valid' => $isSuccess,
                'message' => $rawMsg ?: ($isSuccess ? 'Nomor terdaftar aktif di WhatsApp.' : 'Nomor tidak terdaftar di WhatsApp.'),
                'status' => $status,
                'data' => is_array($json) ? $json : [],
                'raw' => $rawBody,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'is_valid' => false,
                'message' => 'Koneksi ke WatZap gagal: '.$e->getMessage(),
                'status' => 'connection_error',
                'data' => [],
                'raw' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check API Key and get connected account/sender info.
     *
     * @return array{success: bool, message: string, status: string, data: array, raw: string}
     */
    public function checkingKey(?string $apiKey = null): array
    {
        $key = $apiKey !== null && trim($apiKey) !== '' ? trim($apiKey) : $this->getApiKey();
        if ($key === '') {
            return [
                'success' => false,
                'message' => 'API Key belum diisi.',
                'status' => 'empty_key',
                'data' => [],
                'raw' => '',
            ];
        }

        try {
            $url = $this->getBaseUrl().'/checking_key';
            $response = Http::timeout(15)
                ->asJson()
                ->acceptJson()
                ->post($url, [
                    'api_key' => $key,
                ]);

            $json = $response->json();
            $status = (string) ($json['status'] ?? $response->status());
            $ack = strtolower((string) ($json['ack'] ?? ''));
            $rawMsg = strtolower((string) ($json['message'] ?? ''));
            $isSuccess = ($status === '200' || $status === '1' || ($json['status'] ?? null) === true || $ack === 'successfully' || $rawMsg === 'successfully');
            $msg = (string) ($json['message'] ?? ($isSuccess ? 'API Key valid' : 'API Key tidak valid'));

            return [
                'success' => $isSuccess,
                'message' => $msg,
                'status' => $status,
                'data' => is_array($json) ? $json : [],
                'raw' => $response->body(),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi ke server WatZap gagal: '.$e->getMessage(),
                'status' => 'connection_error',
                'data' => [],
                'raw' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get summary status of the current sender device.
     */
    public function getDeviceStatus(?string $channel = null): array
    {
        if (! $this->isConfigured($channel)) {
            return [
                'configured' => false,
                'status' => 'Belum Dikonfigurasi',
                'online' => false,
                'phone' => '-',
                'account_name' => '-',
                'package' => '-',
            ];
        }

        $check = $this->checkingKey($this->getApiKey($channel));
        if (! $check['success']) {
            return [
                'configured' => true,
                'status' => 'Periksa API Key / Koneksi',
                'online' => false,
                'phone' => '-',
                'account_name' => '-',
                'package' => '-',
                'message' => $check['message'],
            ];
        }

        $data = $check['data']['data'] ?? $check['data'] ?? [];

        $accountData = $data['account'] ?? [];
        $accountName = $data['name'] ?? ($accountData['name'] ?? $accountData['email'] ?? $data['account_name'] ?? '-');

        $packageData = $data['package'] ?? [];
        $packageName = $data['plan'] ?? ($packageData['name'] ?? $packageData['status'] ?? $data['package_name'] ?? '-');

        $senders = $data['licenses_key'] ?? $data['senders'] ?? [];
        $currentNumberKey = $this->getNumberKey($channel);

        $senderMatch = null;
        if (is_array($senders)) {
            foreach ($senders as $s) {
                $key = (string) ($s['key'] ?? $s['number_key'] ?? '');
                if ($key !== '' && $key === $currentNumberKey) {
                    $senderMatch = $s;
                    break;
                }
            }
            if ($senderMatch === null && ! empty($senders)) {
                $senderMatch = $senders[0];
            }
        }

        $isConnected = isset($senderMatch['is_connected']) ? (bool) $senderMatch['is_connected'] : null;
        $senderStatus = (string) ($senderMatch['status'] ?? ($isConnected ? 'Online' : 'Offline'));
        $isOnline = $isConnected === true || strcasecmp($senderStatus, 'online') === 0;
        $phone = (string) ($senderMatch['wa_number'] ?? $senderMatch['sender_number'] ?? $senderMatch['phone_number'] ?? $senderMatch['phone'] ?? '-');

        return [
            'configured' => true,
            'status' => $isOnline ? 'Online' : ($senderStatus ?: 'Offline'),
            'online' => $isOnline,
            'phone' => $phone,
            'account_name' => $accountName,
            'package' => $packageName,
        ];
    }

    /**
     * Get list of all available sender devices from WatZap account.
     *
     * @return array<int, array{key: string, phone: string, is_connected: bool|null, is_online: bool, status: string, plan: string, label: string}>
     */
    public function getAvailableSenders(?string $apiKey = null): array
    {
        $check = $this->checkingKey($apiKey);
        if (! $check['success']) {
            return [];
        }

        $data = $check['data']['data'] ?? $check['data'] ?? [];
        $licenses = $data['licenses_key'] ?? $data['senders'] ?? [];
        if (! is_array($licenses)) {
            return [];
        }

        $results = [];
        foreach ($licenses as $lic) {
            $key = (string) ($lic['key'] ?? $lic['number_key'] ?? '');
            if ($key === '') {
                continue;
            }
            $isConnected = isset($lic['is_connected']) ? (bool) $lic['is_connected'] : null;
            $status = (string) ($lic['status'] ?? ($isConnected ? 'Online' : 'Offline'));
            $isOnline = $isConnected === true || strcasecmp($status, 'online') === 0;
            $wa = (string) ($lic['wa_number'] ?? $lic['sender_number'] ?? $lic['phone'] ?? '');

            $results[] = [
                'key' => $key,
                'phone' => $wa,
                'is_connected' => $isConnected,
                'is_online' => $isOnline,
                'status' => $isOnline ? 'Online' : ($status ?: 'Offline'),
                'plan' => (string) ($lic['plan'] ?? ''),
                'label' => $wa !== '' ? "{$wa} (".($isOnline ? 'Online' : 'Offline').')' : $key,
            ];
        }

        return $results;
    }

    /**
     * Intelligently resolve input number key against connected WatZap licenses,
     * correcting font confusions (such as uppercase 'I' vs lowercase 'l' or digit '1'),
     * or looking up license by phone number.
     */
    public function resolveNumberKey(string $input, ?string $apiKey = null): string
    {
        $clean = trim($input);
        if ($clean === '') {
            return '';
        }

        $senders = $this->getAvailableSenders($apiKey);
        if (empty($senders)) {
            return $clean;
        }

        // 1. Direct exact match
        foreach ($senders as $s) {
            if ($s['key'] === $clean) {
                return $s['key'];
            }
        }

        // 2. Match by phone digits
        $phoneDigits = preg_replace('/[^0-9]/', '', $clean) ?? '';
        if (strlen($phoneDigits) >= 9) {
            foreach ($senders as $s) {
                $licPhone = preg_replace('/[^0-9]/', '', $s['phone']) ?? '';
                if ($licPhone === $phoneDigits || str_ends_with($licPhone, substr($phoneDigits, -9))) {
                    return $s['key'];
                }
            }
        }

        // 3. Match fuzzy (e.g. font confusion between 'l', 'I', '1')
        $cleanNormalized = strtolower(str_replace(['1', 'I', '|'], 'l', $clean));
        foreach ($senders as $s) {
            $keyNormalized = strtolower(str_replace(['1', 'I', '|'], 'l', $s['key']));
            if ($keyNormalized === $cleanNormalized) {
                return $s['key'];
            }
        }

        return $clean;
    }

    /**
     * Register / Set Webhook URL to WatZap API.
     *
     * @return array{success: bool, message: string, status: string, data: array}
     */
    public function setWebhook(string $webhookUrl, ?string $channel = 'inbox', ?string $overrideApiKey = null, ?string $overrideNumberKey = null): array
    {
        $apiKey = $overrideApiKey !== null && trim($overrideApiKey) !== ''
            ? trim($overrideApiKey)
            : $this->getApiKey($channel);

        $rawNumberKey = $overrideNumberKey !== null && trim($overrideNumberKey) !== ''
            ? trim($overrideNumberKey)
            : $this->getNumberKey($channel);

        $numberKey = $this->resolveNumberKey($rawNumberKey, $apiKey);

        if ($apiKey === '' || $numberKey === '') {
            return [
                'success' => false,
                'message' => 'API Key atau Number Key untuk channel ini belum dikonfigurasi.',
                'status' => 'not_configured',
                'data' => [],
            ];
        }

        try {
            $url = $this->getBaseUrl().'/set_webhook';
            $response = Http::timeout(15)
                ->asJson()
                ->acceptJson()
                ->post($url, [
                    'api_key' => $apiKey,
                    'number_key' => $numberKey,
                    'endpoint_url' => $webhookUrl,
                    'webhook_url' => $webhookUrl,
                ]);

            $json = $response->json();
            $status = (string) ($json['status'] ?? $response->status());
            $ack = strtolower((string) ($json['ack'] ?? ''));
            $isSuccess = ($status === '200' || $ack === 'successfully');
            $msg = (string) ($json['message'] ?? ($isSuccess ? 'Webhook berhasil didaftarkan ke WatZap.' : 'Gagal mendaftarkan webhook.'));

            return [
                'success' => $isSuccess,
                'message' => $msg,
                'status' => $status,
                'data' => is_array($json) ? $json : [],
                'raw' => $response->body(),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi ke server WatZap gagal: '.$e->getMessage(),
                'status' => 'connection_error',
                'data' => [],
            ];
        }
    }

    /**
     * Get currently registered Webhook from WatZap API.
     *
     * @return array{success: bool, message: string, status: string, data: array}
     */
    public function getWebhook(?string $channel = 'inbox', ?string $overrideApiKey = null, ?string $overrideNumberKey = null): array
    {
        $apiKey = $overrideApiKey !== null && trim($overrideApiKey) !== ''
            ? trim($overrideApiKey)
            : $this->getApiKey($channel);

        $rawNumberKey = $overrideNumberKey !== null && trim($overrideNumberKey) !== ''
            ? trim($overrideNumberKey)
            : $this->getNumberKey($channel);

        $numberKey = $this->resolveNumberKey($rawNumberKey, $apiKey);

        if ($apiKey === '' || $numberKey === '') {
            return [
                'success' => false,
                'message' => 'API Key atau Number Key belum diisi.',
                'status' => 'not_configured',
                'data' => [],
            ];
        }

        try {
            $url = $this->getBaseUrl().'/get_webhook';
            $response = Http::timeout(15)
                ->asJson()
                ->acceptJson()
                ->post($url, [
                    'api_key' => $apiKey,
                    'number_key' => $numberKey,
                ]);

            $json = $response->json();
            $status = (string) ($json['status'] ?? $response->status());
            $ack = strtolower((string) ($json['ack'] ?? ''));
            $isSuccess = ($status === '200' || $ack === 'successfully');
            $msg = (string) ($json['message'] ?? ($isSuccess ? 'Berhasil mengambil data webhook.' : 'Gagal mengambil webhook.'));

            return [
                'success' => $isSuccess,
                'message' => $msg,
                'status' => $status,
                'data' => is_array($json) ? $json : [],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi ke server WatZap gagal: '.$e->getMessage(),
                'status' => 'connection_error',
                'data' => [],
            ];
        }
    }
}
