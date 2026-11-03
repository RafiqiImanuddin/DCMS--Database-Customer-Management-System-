<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Throwable;

class WatzapService
{
    public function getApiKey(): string
    {
        return trim((string) Setting::get('watzap_api_key', (string) config('services.watzap.api_key', '')));
    }

    public function getNumberKey(): string
    {
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

    public function isConfigured(): bool
    {
        return $this->getApiKey() !== '' && $this->getNumberKey() !== '';
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
    public function sendMessage(string $rawPhone, string $message): array
    {
        if (! $this->isConfigured()) {
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
                    'api_key' => $this->getApiKey(),
                    'number_key' => $this->getNumberKey(),
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
     * Send an image with optional caption via WatZap REST API (/send_image_url).
     *
     * @return array{success: bool, message: string, status: string, data: array, raw: string}
     */
    public function sendImage(string $rawPhone, string $imageUrl, ?string $caption = null): array
    {
        if (! $this->isConfigured()) {
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
                'api_key' => $this->getApiKey(),
                'number_key' => $this->getNumberKey(),
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
            $isSuccess = ($status === '200' || $ack === 'successfully');
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
    public function getDeviceStatus(): array
    {
        if (! $this->isConfigured()) {
            return [
                'configured' => false,
                'status' => 'Belum Dikonfigurasi',
                'online' => false,
                'phone' => '-',
                'account_name' => '-',
                'package' => '-',
            ];
        }

        $check = $this->checkingKey();
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
        $accountName = is_array($accountData)
            ? ($accountData['name'] ?? $accountData['email'] ?? '-')
            : ($data['account_name'] ?? $data['name'] ?? '-');

        $packageData = $data['package'] ?? [];
        $packageName = is_array($packageData)
            ? ($packageData['name'] ?? $packageData['status'] ?? '-')
            : ($data['package_name'] ?? $data['package'] ?? '-');

        $senders = $data['senders'] ?? [];
        $currentNumberKey = $this->getNumberKey();

        $senderMatch = null;
        if (is_array($senders)) {
            foreach ($senders as $s) {
                if (($s['number_key'] ?? '') === $currentNumberKey) {
                    $senderMatch = $s;
                    break;
                }
            }
            if ($senderMatch === null && ! empty($senders)) {
                $senderMatch = $senders[0];
            }
        }

        $senderStatus = (string) ($senderMatch['status'] ?? 'Belum Terhubung');
        $isOnline = strcasecmp($senderStatus, 'online') === 0;
        $phone = (string) ($senderMatch['sender_number'] ?? $senderMatch['phone_number'] ?? $senderMatch['phone'] ?? '-');

        return [
            'configured' => true,
            'status' => $isOnline ? 'Online' : ($senderStatus ?: 'Offline'),
            'online' => $isOnline,
            'phone' => $phone,
            'account_name' => $accountName,
            'package' => $packageName,
        ];
    }
}
