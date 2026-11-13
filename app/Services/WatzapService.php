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

    public function getWahaUrl(): string
    {
        return rtrim((string) Setting::get('waha_vps_url', (string) config('services.waha.url', 'http://178.83.188.100:3088')), '/');
    }

    public function getWahaApiKey(): string
    {
        return trim((string) Setting::get('waha_api_key', (string) config('services.waha.api_key', 'gadgetmart123')));
    }

    public function getWahaSession(): string
    {
        return trim((string) Setting::get('waha_session', 'default'));
    }

    public function isWahaEnabled(): bool
    {
        return (string) Setting::get('waha_enabled', '1') !== '0';
    }

    public function getBaileysUrl(): string
    {
        return rtrim((string) Setting::get('baileys_bot_url', (string) config('services.baileys.url', env('BAILEYS_BOT_URL', 'https://dcms-baileys-bot.onrender.com'))), '/');
    }

    public function getBaileysApiKey(): string
    {
        return trim((string) Setting::get('baileys_bot_api_key', (string) config('services.baileys.api_key', env('BAILEYS_BOT_API_KEY', 'dcms2026'))));
    }

    public function isBaileysEnabled(): bool
    {
        return (string) Setting::get('baileys_bot_enabled', (string) env('BAILEYS_BOT_ENABLED', '1')) === '1';
    }

    /**
     * Check status bot Baileys Gateway.
     *
     * @return array{online: bool, status: string, phone: string|null, name: string|null, message: string}
     */
    public function getBaileysStatus(): array
    {
        $baileysUrl = $this->getBaileysUrl();
        $apiKey = $this->getBaileysApiKey();

        try {
            $res = Http::timeout(3)
                ->withHeaders(['X-Api-Key' => $apiKey])
                ->get("{$baileysUrl}/api/status");

            if ($res->ok()) {
                $data = $res->json();

                return [
                    'online' => ! empty($data['connected']),
                    'status' => (string) ($data['state'] ?? 'UNKNOWN'),
                    'phone' => ! empty($data['phone']) ? (string) $data['phone'] : null,
                    'name' => ! empty($data['name']) ? (string) $data['name'] : 'Bot DCMS',
                    'message' => ! empty($data['connected']) ? 'Baileys Bot Terhubung' : 'Baileys Bot Belum Terhubung (Scan QR)',
                ];
            }
        } catch (Throwable $e) {
            return [
                'online' => false,
                'status' => 'OFFLINE',
                'phone' => null,
                'name' => null,
                'message' => 'Tidak dapat terhubung ke server Baileys: '.$e->getMessage(),
            ];
        }

        return [
            'online' => false,
            'status' => 'DISCONNECTED',
            'phone' => null,
            'name' => null,
            'message' => 'Status Baileys tidak aktif',
        ];
    }

    /**
     * Check status session WAHA VPS.
     *
     * @return array{online: bool, status: string, name: string, me: array|null, message: string}
     */
    public function getWahaStatus(?string $sessionName = null): array
    {
        $targetSession = $sessionName ?: $this->getWahaSession();
        $wahaUrl = $this->getWahaUrl();
        $apiKey = $this->getWahaApiKey();

        try {
            $res = Http::timeout(4)
                ->withHeaders(['X-Api-Key' => $apiKey])
                ->get("{$wahaUrl}/api/sessions");

            if ($res->ok()) {
                $sessions = $res->json();
                if (is_array($sessions)) {
                    foreach ($sessions as $s) {
                        if (($s['name'] ?? '') === $targetSession) {
                            return [
                                'online' => true,
                                'status' => $s['status'] ?? 'UNKNOWN',
                                'name' => $targetSession,
                                'me' => $s['me'] ?? null,
                                'message' => 'Terhubung ke WAHA VPS ('.($s['status'] ?? 'UNKNOWN').')',
                            ];
                        }
                    }

                    return [
                        'online' => true,
                        'status' => 'NOT_FOUND',
                        'name' => $targetSession,
                        'me' => null,
                        'message' => "Sesi '{$targetSession}' belum dibuat di WAHA VPS.",
                    ];
                }
            }

            return [
                'online' => false,
                'status' => 'ERROR',
                'name' => $targetSession,
                'me' => null,
                'message' => 'Gagal membaca sesi WAHA: HTTP '.$res->status(),
            ];
        } catch (Throwable $e) {
            return [
                'online' => false,
                'status' => 'OFFLINE',
                'name' => $targetSession,
                'me' => null,
                'message' => 'Koneksi ke WAHA VPS gagal: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Sinkronisasi chat dan pesan terbaru dari WAHA VPS ke sistem lokal.
     * Sangat penting agar chat fisik selalu tersinkronisasi realtime tanpa tergantung webhook!
     *
     * @return array<int, array>
     */
    public function syncWahaMessages(int $limitChats = 30, int $limitMessagesPerChat = 20): array
    {
        if (! $this->isWahaEnabled()) {
            return [];
        }

        $wahaUrl = $this->getWahaUrl();
        $apiKey = $this->getWahaApiKey();
        $session = $this->getWahaSession();

        try {
            $chatsRes = Http::timeout(4)
                ->withHeaders(['X-Api-Key' => $apiKey])
                ->get("{$wahaUrl}/api/{$session}/chats");

            if (! $chatsRes->ok()) {
                return [];
            }

            $chats = $chatsRes->json();
            if (! is_array($chats)) {
                return [];
            }

            $syncedEntries = [];
            $chatCount = 0;

            foreach ($chats as $chat) {
                if (! empty($chat['isGroup'])) {
                    continue;
                }

                $chatId = is_array($chat['id']) ? ($chat['id']['_serialized'] ?? '') : (string) ($chat['id'] ?? '');
                if ($chatId === '' || str_contains($chatId, '@g.us') || str_contains($chatId, 'broadcast')) {
                    continue;
                }

                $chatName = trim((string) ($chat['name'] ?? ''));
                $cleanPhone = $this->resolvePhoneOrLid($chatId);
                if (! $cleanPhone || strlen($cleanPhone) < 9) {
                    $cleanPhone = preg_replace('/@.*$/', '', $chatId);
                    $cleanPhone = preg_replace('/\D/', '', (string) $cleanPhone);
                }

                $chatCount++;
                if ($chatCount > $limitChats) {
                    break;
                }

                try {
                    $msgRes = Http::timeout(3)
                        ->withHeaders(['X-Api-Key' => $apiKey])
                        ->get("{$wahaUrl}/api/{$session}/chats/".urlencode($chatId)."/messages?limit={$limitMessagesPerChat}");

                    if ($msgRes->ok()) {
                        $messages = $msgRes->json();
                        if (is_array($messages)) {
                            foreach ($messages as $m) {
                                $msgId = (string) ($m['id'] ?? ('waha_'.uniqid()));
                                $isFromMe = ! empty($m['fromMe']) || ! empty($m['_data']['id']['fromMe']);
                                $direction = $isFromMe ? 'outbound' : 'inbound';
                                $senderRole = $isFromMe ? 'ADMIN' : 'PIC';
                                $senderName = $isFromMe ? 'Admin' : ($chatName ?: 'PIC '.$cleanPhone);
                                $rawBody = trim((string) ($m['body'] ?? ($m['_data']['body'] ?? '')));
                                $ts = isset($m['timestamp']) ? (int) $m['timestamp'] : time();
                                $epochMs = $ts > 1e11 ? $ts : ($ts * 1000);

                                // Quoted Reply parsing
                                $replyTo = null;
                                if (! empty($m['replyTo']) && is_array($m['replyTo'])) {
                                    $replyTo = [
                                        'id' => (string) ($m['replyTo']['id'] ?? ''),
                                        'senderName' => $isFromMe ? 'PIC' : 'Admin',
                                        'text' => trim((string) ($m['replyTo']['body'] ?? '')),
                                    ];
                                }

                                $hasMedia = ! empty($m['hasMedia']) || ! empty($m['media']);
                                $mediaUrl = null;
                                $mediaType = null;
                                $filename = null;
                                if ($hasMedia) {
                                    $mime = is_array($m['media'] ?? null) ? ($m['media']['mimetype'] ?? '') : ($m['_data']['mimetype'] ?? '');
                                    $filename = is_array($m['media'] ?? null) ? ($m['media']['filename'] ?? null) : ($m['_data']['filename'] ?? null);
                                    $isImage = str_starts_with($mime, 'image/') || (! $mime && str_ends_with(strtolower((string) $filename), '.jpeg')) || (($m['type'] ?? '') === 'image');
                                    $mediaType = $isImage ? 'image' : 'document';

                                    if (is_array($m['media'] ?? null) && ! empty($m['media']['url'])) {
                                        $parsedPath = parse_url((string) $m['media']['url'], PHP_URL_PATH);
                                        if ($parsedPath && str_contains($parsedPath, '/api/files/')) {
                                            $subPath = substr($parsedPath, strpos($parsedPath, '/api/files/') + 11);
                                            $mediaUrl = '/api/watzap-media?file='.urlencode($subPath);
                                        } else {
                                            $mediaUrl = (string) $m['media']['url'];
                                        }
                                    } elseif (is_array($m['media'] ?? null) && ! empty($m['media']['filename'])) {
                                        $mediaUrl = '/api/watzap-media?file='.urlencode("{$session}/".$m['media']['filename']);
                                    } else {
                                        $mediaUrl = '/api/watzap-media?file='.urlencode("{$session}/{$msgId}".($isImage ? '.jpeg' : ''));
                                    }
                                }

                                $entry = [
                                    'messageId' => $msgId,
                                    'phone' => $cleanPhone,
                                    'message' => $rawBody,
                                    'media_url' => $mediaUrl,
                                    'media_type' => $mediaType,
                                    'filename' => $filename,
                                    'reply_to' => $replyTo,
                                    'timestamp' => $epochMs,
                                    'status' => 'received',
                                    'direction' => $direction,
                                    'sender' => $senderRole,
                                    'senderName' => $senderName,
                                    'gateway' => 'WAHA_VPS',
                                    'raw' => $m,
                                ];

                                $syncedEntries[] = $entry;

                                try {
                                    if ($cleanPhone) {
                                        $dbChat = \App\Models\WhatsAppChat::firstOrCreate(
                                            ['phone_number' => $cleanPhone],
                                            [
                                                'customer_name' => $chatName ?: ('PIC '.$cleanPhone),
                                                'folder' => 'all',
                                                'unread_count' => 0,
                                            ]
                                        );
                                        $dbChat->update([
                                            'last_message' => $rawBody ?: ($mediaType ? "[$mediaType]" : 'Pesan'),
                                            'last_message_at' => date('Y-m-d H:i:s', $ts),
                                        ]);

                                        \App\Models\WhatsAppMessage::firstOrCreate(
                                            ['message_id' => $msgId],
                                            [
                                                'whatsapp_chat_id' => $dbChat->id,
                                                'sender_type' => $isFromMe ? 'admin' : 'customer',
                                                'agent_name' => $senderName,
                                                'message' => $rawBody,
                                                'media_url' => $mediaUrl,
                                                'media_type' => $mediaType,
                                                'reply_to' => $replyTo,
                                                'raw_payload' => $m,
                                                'status' => 'received',
                                                'created_at' => date('Y-m-d H:i:s', $ts),
                                            ]
                                        );
                                    }
                                } catch (\Throwable) {
                                }
                            }
                        }
                    }
                } catch (\Throwable) {
                }
            }

            return $syncedEntries;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Resolves phone or WhatsApp LID (@lid) to a normalized WhatsApp phone number (e.g. 628xxx).
     */
    public function resolvePhoneOrLid(?string $raw): string
    {
        if ($raw === null) {
            return '';
        }

        $str = trim($raw);
        if ($str === '') {
            return '';
        }

        if (str_ends_with($str, '@lid') || (strlen($str) >= 13 && ! str_starts_with($str, '628') && ! str_starts_with($str, '08') && ! str_starts_with($str, '8'))) {
            try {
                $q = str_contains($str, '@') ? $str : "{$str}@lid";
                $wahaUrl = $this->getWahaUrl().'/api/contacts?session='.urlencode($this->getWahaSession()).'&contactId='.urlencode($q);
                $res = Http::timeout(3)
                    ->withHeaders(['X-Api-Key' => $this->getWahaApiKey()])
                    ->get($wahaUrl);
                if ($res->ok()) {
                    $d = $res->json();
                    $num = $d['id'] ?? '';
                    $clean = preg_replace('/@.*$/', '', (string) $num) ?? '';
                    $clean = preg_replace('/\D/', '', $clean) ?? '';
                    if (strlen($clean) >= 9 && strlen($clean) <= 15) {
                        if (str_starts_with($clean, '08')) {
                            return '62'.substr($clean, 1);
                        }
                        if (str_starts_with($clean, '8')) {
                            return '62'.$clean;
                        }

                        return $clean;
                    }
                }
            } catch (Throwable) {
            }
        }

        $clean = preg_replace('/@.*$/', '', $str) ?? '';
        $clean = preg_replace('/\D/', '', $clean) ?? '';
        if (str_starts_with($clean, '08')) {
            $clean = '62'.substr($clean, 1);
        } elseif (str_starts_with($clean, '8') && strlen($clean) <= 13) {
            $clean = '62'.$clean;
        } elseif (! str_starts_with($clean, '62') && strlen($clean) >= 9 && strlen($clean) <= 13) {
            $clean = '62'.$clean;
        }

        return $clean;
    }

    /**
     * Send Omnichannel message with Dual-Gateway (WAHA VPS primary + WatZap fallback)
     * and native quoted reply support.
     *
     * @param  array{id?: string, messageId?: string, senderName?: string, text?: string}|null  $replyTo
     * @return array{success: bool, message: string, status: string, source: string, message_id: string, data: array, raw: string}
     */
    public function sendOmnichannel(string $rawPhone, string $message, ?string $mediaUrl = null, ?string $mediaType = null, ?array $replyTo = null, ?array $fileData = null): array
    {
        $cleanPhone = $this->resolvePhoneOrLid($rawPhone);
        if (! $cleanPhone) {
            return [
                'success' => false,
                'message' => 'Nomor telepon tujuan tidak valid.',
                'status' => 'invalid_phone',
                'source' => 'NONE',
                'message_id' => 'err_'.uniqid(),
                'data' => [],
                'raw' => '',
            ];
        }

        // 1. Prioritaskan Baileys Bot Gateway jika aktif (Single Gateway Nomor Khusus)
        if ($this->isBaileysEnabled()) {
            try {
                $baileysUrl = $this->getBaileysUrl();
                $payload = [
                    'phone' => $cleanPhone,
                    'message' => $message,
                ];

                if ($fileData && ! empty($fileData['base64'])) {
                    $payload['file_base64'] = (string) $fileData['base64'];
                    $payload['mimetype'] = $fileData['mimetype'] ?? ($mediaType === 'image' ? 'image/jpeg' : 'application/octet-stream');
                    $payload['filename'] = $fileData['filename'] ?? ('file_'.time());
                    $payload['media_type'] = $mediaType ?: 'document';
                } elseif ($mediaUrl) {
                    $payload['media_url'] = $mediaUrl;
                    $payload['media_type'] = $mediaType ?: 'image';
                }

                $replyId = $replyTo['id'] ?? $replyTo['messageId'] ?? null;
                if ($replyId) {
                    $payload['reply_to'] = (string) $replyId;
                    $payload['reply_text'] = (string) ($replyTo['text'] ?? $replyTo['body'] ?? '');
                }

                $response = Http::timeout(6)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Api-Key' => $this->getBaileysApiKey(),
                    ])
                    ->post("{$baileysUrl}/api/send-message", $payload);

                if ($response->successful()) {
                    $json = $response->json();
                    $msgId = (string) ($json['message_id'] ?? $json['messageId'] ?? ('baileys_'.time()));

                    return [
                        'success' => true,
                        'source' => 'BAILEYS_GATEWAY',
                        'message_id' => $msgId,
                        'status' => '200',
                        'message' => 'Pesan berhasil dikirim via Baileys Bot Gateway.',
                        'data' => is_array($json) ? $json : [],
                        'raw' => $response->body(),
                    ];
                }
            } catch (Throwable) {
                // Fallback lanjut ke WAHA VPS atau WatZap Cloud jika Baileys offline
            }
        }

        // 2. Prioritaskan Pengiriman via WAHA VPS (Super Cepat & Native Quoted Reply)
        if ($this->isWahaEnabled()) {
            try {
                $hasFile = ($fileData && ! empty($fileData['base64'])) || ! empty($mediaUrl);
                $endpoint = $hasFile
                    ? ($mediaType === 'image' ? '/api/sendImage' : '/api/sendFile')
                    : '/api/sendText';

                $wahaUrl = $this->getWahaUrl().$endpoint;
                $payload = [
                    'session' => $this->getWahaSession(),
                    'chatId' => "{$cleanPhone}@c.us",
                ];

                if ($fileData && ! empty($fileData['base64'])) {
                    $payload['file'] = [
                        'mimetype' => $fileData['mimetype'] ?? ($mediaType === 'image' ? 'image/jpeg' : 'application/octet-stream'),
                        'filename' => $fileData['filename'] ?? ('file_'.time()),
                        'data' => (string) $fileData['base64'],
                    ];
                    if ($message !== '') {
                        $payload['caption'] = $message;
                    }
                } elseif ($mediaUrl) {
                    $payload['file'] = ['url' => $mediaUrl];
                    if ($message !== '') {
                        $payload['caption'] = $message;
                    }
                } else {
                    $payload['text'] = $message;
                }

                $replyId = $replyTo['id'] ?? $replyTo['messageId'] ?? null;
                if ($replyId) {
                    $payload['reply_to'] = (string) $replyId;
                }

                $response = Http::timeout(6)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Api-Key' => $this->getWahaApiKey(),
                    ])
                    ->post($wahaUrl, $payload);

                if (! $response->successful() && isset($payload['reply_to'])) {
                    // Fallback retry tanpa reply_to jika ID pesan yang dibalas tidak ditemukan di memory WhatsApp
                    unset($payload['reply_to']);
                    $response = Http::timeout(6)
                        ->withHeaders([
                            'Content-Type' => 'application/json',
                            'X-Api-Key' => $this->getWahaApiKey(),
                        ])
                        ->post($wahaUrl, $payload);
                }

                if ($response->successful()) {
                    $json = $response->json();
                    $msgId = 'waha_'.time();
                    if (isset($json['id'])) {
                        $msgId = is_array($json['id']) ? (string) ($json['id']['_serialized'] ?? $json['id']['id'] ?? $msgId) : (string) $json['id'];
                    }

                    return [
                        'success' => true,
                        'source' => 'WAHA_VPS',
                        'message_id' => $msgId,
                        'status' => '200',
                        'message' => 'Pesan berhasil dikirim via WAHA VPS.',
                        'data' => is_array($json) ? $json : [],
                        'raw' => $response->body(),
                    ];
                }
            } catch (Throwable) {
                // Lanjut fallback ke WatZap API
            }
        }

        // 2. Fallback ke WatZap Cloud API
        if ($mediaUrl) {
            if ($mediaType === 'image') {
                $result = $this->sendImage($cleanPhone, $mediaUrl, $message ?: null, 'inbox');
            } else {
                $result = $this->sendFile($cleanPhone, $mediaUrl, $message ?: null, 'inbox');
            }
        } else {
            $result = $this->sendMessage($cleanPhone, $message, 'inbox');
        }

        $result['source'] = 'WATZAP';
        $result['message_id'] = 'out_'.uniqid();

        return $result;
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
