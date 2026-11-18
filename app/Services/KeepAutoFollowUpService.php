<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Setting;
use App\Models\SosmedKeep;
use App\Models\User;
use App\Models\WhatsAppChat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class KeepAutoFollowUpService
{
    public const DEFAULT_HEADER = "☀️ *REKAP AUTO FOLLOW-UP H+1 KEEP SOSMED*\nHalo Kak *{pic_name}*! 👋\n\nBerikut daftar *{total_items} Customer Keep* kamu kemarin (*{target_date}*) yang masih berstatus *ON PROGRESS* dan perlu kamu follow-up pagi ini:\n-----------------------------------------";

    public const DEFAULT_ITEM = "{num}. *{customer_name}* ({customer_phone})\n   📦 Barang: {barang_keep}\n   🏢 Cabang: {cabang}\n   🏷️ Admin Sosmed: {kode_sosmed}\n   📝 Catatan: {catatan}\n   👉 *Klik Chat Customer:* {chat_link}";

    public const DEFAULT_SINGLE_ALERT = "⏰ *REMINDER 24 JAM KEEP SOSMED*\nHalo Kak *{pic_name}*! 👋\n\nCustomer berikut sudah 24 jam sejak keep tercatat (*{jam_masuk}*) dan masih berstatus *ON PROGRESS*:\n\n👤 *{customer_name}* ({customer_phone})\n📦 Barang: {barang_keep}\n🏢 Cabang: {cabang}\n🏷️ Admin Sosmed: {kode_sosmed}\n📝 Catatan: {catatan}\n\n👉 *Langsung Chat Customer:* {chat_link}\n\nYuk follow-up sekarang dan kabari update-nya di dashboard! Semangat! 🚀💪";

    public const DEFAULT_FOOTER = "-----------------------------------------\nYuk follow-up sekarang dan giring jadi *WIN* hari ini! Jangan lupa update statusnya di Dashboard ya! Semangat! 🚀💪";

    public const DEFAULT_GREETING = "Halo Kak {customer_name}, kami dari Gadgetmart ingin menanyakan perihal keep barang {barang_keep} kemarin, apakah jadi diambil hari ini? Terima kasih 🙏";

    public function __construct(
        protected WatzapService $watzapService
    ) {}

    /**
     * In-memory cache for resolved PIC phones to avoid repetitive database queries.
     *
     * @var array<string, ?string>
     */
    protected array $resolvedPhonesCache = [];

    /**
     * Cached list of employees with phones.
     */
    protected ?\Illuminate\Support\Collection $cachedEmployees = null;

    /**
     * Cached list of users with phones.
     */
    protected ?\Illuminate\Support\Collection $cachedUsers = null;

    /**
     * Cached PIC phones from SosmedKeep table.
     */
    protected ?array $cachedSosmedKeepPhones = null;

    /**
     * Get Follow-up H+1 Schedule and Template Settings.
     */
    public function getSettings(): array
    {
        return [
            'enabled' => Setting::get('keep_auto_fu_h1_enabled', '1') === '1',
            'work_start' => Setting::get('keep_auto_fu_h1_work_start', '08:00'),
            'work_end' => Setting::get('keep_auto_fu_h1_work_end', '22:00'),
            'time' => Setting::get('keep_auto_fu_h1_time', '09:00'),
            'delay_min' => (int) Setting::get('keep_auto_fu_h1_delay_min', 3),
            'delay_max' => (int) Setting::get('keep_auto_fu_h1_delay_max', 8),
            'batch_size' => (int) Setting::get('keep_auto_fu_h1_batch_size', 10),
            'batch_cooldown' => (int) Setting::get('keep_auto_fu_h1_batch_cooldown', 30),
            'template_single_alert' => Setting::get('keep_auto_fu_h1_template_single_alert', self::DEFAULT_SINGLE_ALERT),
            'template_header' => Setting::get('keep_auto_fu_h1_template_header', self::DEFAULT_HEADER),
            'template_item' => Setting::get('keep_auto_fu_h1_template_item', self::DEFAULT_ITEM),
            'template_footer' => Setting::get('keep_auto_fu_h1_template_footer', self::DEFAULT_FOOTER),
            'greeting_customer' => Setting::get('keep_auto_fu_h1_greeting_customer', self::DEFAULT_GREETING),
        ];
    }

    /**
     * Check if current time is within operational work hours (default 08:00 - 22:00 WITA).
     */
    public function isWithinWorkHours(?Carbon $now = null): bool
    {
        $settings = $this->getSettings();
        if (! $settings['enabled']) {
            return false;
        }

        $now = $now ? $now->copy() : Carbon::now();
        $currentTime = $now->format('H:i');
        $workStart = $settings['work_start'] ?? '08:00';
        $workEnd = $settings['work_end'] ?? '22:00';

        return $currentTime >= $workStart && $currentTime <= $workEnd;
    }

    /**
     * Save Follow-up H+1 Schedule and Template Settings.
     */
    public function saveSettings(array $data): void
    {
        if (isset($data['enabled'])) {
            Setting::set('keep_auto_fu_h1_enabled', ! empty($data['enabled']) ? '1' : '0');
        }
        if (! empty($data['work_start'])) {
            Setting::set('keep_auto_fu_h1_work_start', trim((string) $data['work_start']));
        }
        if (! empty($data['work_end'])) {
            Setting::set('keep_auto_fu_h1_work_end', trim((string) $data['work_end']));
        }
        if (! empty($data['time'])) {
            Setting::set('keep_auto_fu_h1_time', trim((string) $data['time']));
        }
        if (isset($data['delay_min'])) {
            Setting::set('keep_auto_fu_h1_delay_min', (string) max(1, (int) $data['delay_min']));
        }
        if (isset($data['delay_max'])) {
            $minVal = max(1, (int) ($data['delay_min'] ?? Setting::get('keep_auto_fu_h1_delay_min', 3)));
            Setting::set('keep_auto_fu_h1_delay_max', (string) max($minVal, (int) $data['delay_max']));
        }
        if (isset($data['batch_size'])) {
            Setting::set('keep_auto_fu_h1_batch_size', (string) max(2, (int) $data['batch_size']));
        }
        if (isset($data['batch_cooldown'])) {
            Setting::set('keep_auto_fu_h1_batch_cooldown', (string) max(0, (int) $data['batch_cooldown']));
        }
        if (isset($data['template_single_alert'])) {
            Setting::set('keep_auto_fu_h1_template_single_alert', (string) $data['template_single_alert']);
        }
        if (isset($data['template_header'])) {
            Setting::set('keep_auto_fu_h1_template_header', (string) $data['template_header']);
        }
        if (isset($data['template_item'])) {
            Setting::set('keep_auto_fu_h1_template_item', (string) $data['template_item']);
        }
        if (isset($data['template_footer'])) {
            Setting::set('keep_auto_fu_h1_template_footer', (string) $data['template_footer']);
        }
        if (isset($data['greeting_customer'])) {
            Setting::set('keep_auto_fu_h1_greeting_customer', (string) $data['greeting_customer']);
        }
    }

    /**
     * Resolve target date for H+1 (Default: Yesterday / H-1).
     */
    public function resolveTargetDate(?string $date = null): string
    {
        if (! empty($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        return Carbon::yesterday()->toDateString();
    }

    /**
     * Get all keeps for H+1 follow-up on a specific keep date.
     */
    public function getH1KeepsQuery(?string $targetDate = null)
    {
        $targetDate = $this->resolveTargetDate($targetDate);

        return SosmedKeep::query()
            ->where(function ($q) use ($targetDate) {
                $q->whereBetween('keep_timestamp', ["{$targetDate} 00:00:00", "{$targetDate} 23:59:59"])
                    ->orWhere(function ($sub) use ($targetDate) {
                        $sub->whereNull('keep_timestamp')
                            ->whereBetween('created_at', ["{$targetDate} 00:00:00", "{$targetDate} 23:59:59"]);
                    });
            })
            ->whereNotNull('nomor_wa')
            ->where('nomor_wa', '!=', '');
    }

    /**
     * Get Dashboard Summary data for UI.
     */
    public function getDashboardData(?string $targetDate = null): array
    {
        $targetDate = $this->resolveTargetDate($targetDate);
        $allKeeps = $this->getH1KeepsQuery($targetDate)->get();

        $totalKeeps = $allKeeps->count();
        $totalWin = $allKeeps->filter(fn ($k) => strtoupper(trim((string) $k->status)) === 'WIN')->count();
        $totalLose = $allKeeps->filter(fn ($k) => strtoupper(trim((string) $k->status)) === 'LOSE')->count();
        $totalOnProgress = $totalKeeps - $totalWin - $totalLose;

        // Group by PIC (nomor_fu / nama_tim_follow_up)
        $picGroups = [];
        $unassignedKeeps = [];

        foreach ($allKeeps as $k) {
            $rawPicPhone = $k->nomor_fu ?: $k->nomor_follow_up;
            $cleanPicPhone = $this->normalizePhone($rawPicPhone);
            $picName = trim((string) ($k->nama_tim_follow_up ?: ''));

            // Smart resolve real mobile phone if cleanPicPhone is LID (>13 digits or invalid)
            $resolvedRealPhone = $this->resolvePicRealPhone($cleanPicPhone, $picName);
            if ($resolvedRealPhone) {
                $cleanPicPhone = $resolvedRealPhone;
            } elseif ($this->isLidPhone($cleanPicPhone)) {
                $cleanPicPhone = null;
            }

            if (! $cleanPicPhone && ! $picName) {
                $unassignedKeeps[] = $this->transformKeepItem($k);
                continue;
            }

            // Group key by clean phone or fallback name
            $groupKey = $cleanPicPhone ?: ('NAME_'.strtoupper(preg_replace('/[^A-Z0-9]/', '', $picName)));

            if (! isset($picGroups[$groupKey])) {
                $resolvedPicName = $picName ?: $this->resolvePicNameByPhone($cleanPicPhone);
                $isLid = $this->isLidPhone($rawPicPhone);
                $isValidPhone = ! empty($cleanPicPhone) && ! $this->isLidPhone($cleanPicPhone) && strlen($cleanPicPhone) >= 9;

                $picGroups[$groupKey] = [
                    'group_key' => $groupKey,
                    'pic_name' => $resolvedPicName,
                    'pic_phone' => $cleanPicPhone,
                    'raw_pic_phone' => $rawPicPhone,
                    'pic_display_phone' => $this->formatPhoneDisplay($cleanPicPhone ?: $rawPicPhone),
                    'is_valid_phone' => $isValidPhone,
                    'is_lid' => $isLid,
                    'cabang' => $k->cabang ?? '-',
                    'total_items' => 0,
                    'total_win' => 0,
                    'total_on_progress' => 0,
                    'wa_status' => 'PENDING',
                    'wa_sent_at' => null,
                    'wa_note' => null,
                    'items' => [],
                ];
            }

            $isWin = strtoupper(trim((string) $k->status)) === 'WIN';
            $picGroups[$groupKey]['total_items']++;
            if ($isWin) {
                $picGroups[$groupKey]['total_win']++;
            } else {
                $picGroups[$groupKey]['total_on_progress']++;
            }

            if (! empty($k->cabang) && $picGroups[$groupKey]['cabang'] === '-') {
                $picGroups[$groupKey]['cabang'] = $k->cabang;
            }

            if (! empty($k->fu_h1_note)) {
                $picGroups[$groupKey]['wa_note'] = $k->fu_h1_note;
            }
            if ($k->fu_h1_status === 'FAILED') {
                $picGroups[$groupKey]['wa_status'] = 'FAILED';
            } elseif ($k->fu_h1_status === 'SENT' || $k->fu_h1_sent_at) {
                $picGroups[$groupKey]['wa_status'] = 'SENT';
                $picGroups[$groupKey]['wa_sent_at'] = $this->formatDateTimeSafely($k->fu_h1_sent_at);
            }

            if (empty($picGroups[$groupKey]['wa_note'])) {
                if (! $picGroups[$groupKey]['is_valid_phone']) {
                    if ($picGroups[$groupKey]['is_lid']) {
                        $picGroups[$groupKey]['wa_note'] = 'Format nomor terdeteksi LID WhatsApp Multi-Device (' . ($rawPicPhone ?: '-') . '), bukan nomor HP seluler';
                    } else {
                        $picGroups[$groupKey]['wa_note'] = 'Nomor WhatsApp tidak valid atau belum diisi (' . ($rawPicPhone ?: '-') . ')';
                    }
                } elseif ($picGroups[$groupKey]['total_on_progress'] === 0) {
                    $picGroups[$groupKey]['wa_note'] = 'Dilewati: Seluruh keep sudah WIN / LOSE (0 On Progress)';
                } else {
                    $picGroups[$groupKey]['wa_note'] = 'Siap dikirim (Menunggu antrean)';
                }
            }

            $picGroups[$groupKey]['items'][] = $this->transformKeepItem($k);
        }

        $picGroupsList = array_values($picGroups);

        $totalPics = count($picGroupsList);
        $totalSentPics = count(array_filter($picGroupsList, fn ($g) => $g['wa_status'] === 'SENT'));
        $totalFailedPics = count(array_filter($picGroupsList, fn ($g) => $g['wa_status'] === 'FAILED'));
        $totalPendingPics = $totalPics - $totalSentPics - $totalFailedPics;

        // Baileys status
        $baileysStatus = $this->watzapService->getBaileysStatus();

        return [
            'target_date' => $targetDate,
            'target_date_formatted' => Carbon::parse($targetDate)->locale('id')->isoFormat('dddd, D MMMM Y'),
            'today_formatted' => Carbon::today()->locale('id')->isoFormat('dddd, D MMMM Y'),
            'baileys_online' => $baileysStatus['online'] ?? false,
            'baileys_message' => $baileysStatus['message'] ?? 'Gateway Bot',
            'summary' => [
                'total_keeps' => $totalKeeps,
                'total_pics' => $totalPics,
                'total_sent_pics' => $totalSentPics,
                'total_failed_pics' => $totalFailedPics,
                'total_pending_pics' => $totalPendingPics,
                'total_win' => $totalWin,
                'total_lose' => $totalLose,
                'total_on_progress' => $totalOnProgress,
                'total_unassigned' => count($unassignedKeeps),
            ],
            'pic_groups' => $picGroupsList,
            'unassigned_keeps' => $unassignedKeeps,
            'all_keeps' => $allKeeps->map(fn ($k) => $this->transformKeepItem($k))->values()->all(),
            'settings' => $this->getSettings(),
        ];
    }

    /**
     * Build WhatsApp Digest Message text for a PIC (Opsi 2).
     */
    public function buildDigestMessage(string $picName, array $keeps, string $targetDate): string
    {
        $settings = $this->getSettings();
        $targetFormatted = Carbon::parse($targetDate)->locale('id')->isoFormat('D MMMM Y');
        $activeKeeps = array_filter($keeps, function ($k) {
            $st = strtoupper(trim((string) ($k['status'] ?? '')));
            return $st !== 'WIN' && $st !== 'LOSE';
        });

        if (empty($activeKeeps)) {
            $activeKeeps = $keeps;
        }

        $count = count($activeKeeps);
        $nameDisplay = ! empty($picName) ? $picName : 'Tim Sales';

        // 1. Header
        $headerTpl = $settings['template_header'] ?: self::DEFAULT_HEADER;
        $header = str_replace(
            ['{pic_name}', '{target_date}', '{total_items}'],
            [$nameDisplay, $targetFormatted, $count],
            $headerTpl
        );

        // 2. Items
        $itemTpl = $settings['template_item'] ?: self::DEFAULT_ITEM;
        $greetingTpl = $settings['greeting_customer'] ?: self::DEFAULT_GREETING;

        $itemsTextList = [];
        $num = 1;
        foreach ($activeKeeps as $item) {
            $custName = $item['nama'] ?: 'Customer';
            $custPhone = $item['nomor_wa'] ?: '-';
            $normCust = $this->normalizePhone($custPhone);
            $barang = $item['barang_keep'] ?: '-';
            $cabang = $item['cabang'] ?: 'Gadgetmart';
            $catatan = $item['catatan'] ? trim((string) $item['catatan']) : '-';

            $rawGreeting = str_replace(
                ['{customer_name}', '{barang_keep}', '{cabang}', '{kode_sosmed}', '{kode}'],
                [$custName, $barang, $cabang, $item['kode_sosmed'] ?? '-', $item['kode_sosmed'] ?? '-'],
                $greetingTpl
            );
            $encodedGreeting = rawurlencode($rawGreeting);
            $waLink = $normCust ? "https://wa.me/{$normCust}?text={$encodedGreeting}" : '-';

            $kodeSosmed = $item['kode_sosmed'] ?? '-';
            $renderedItem = str_replace(
                ['{num}', '{customer_name}', '{customer_phone}', '{barang_keep}', '{cabang}', '{kode_sosmed}', '{kode}', '{catatan}', '{chat_link}'],
                [$num, $custName, $custPhone, $barang, $cabang, $kodeSosmed, $kodeSosmed, $catatan, $waLink],
                $itemTpl
            );

            $itemsTextList[] = $renderedItem;
            $num++;
        }

        // 3. Footer
        $footerTpl = $settings['template_footer'] ?: self::DEFAULT_FOOTER;

        return trim($header) . "\n\n" . implode("\n\n", $itemsTextList) . "\n\n" . trim($footerTpl);
    }

    /**
     * Build WhatsApp Single Alert Message for a specific Customer Keep (Strict 24h Model A).
     */
    public function buildSingleAlertMessage(string $picName, array $item): string
    {
        $settings = $this->getSettings();
        $tpl = $settings['template_single_alert'] ?: self::DEFAULT_SINGLE_ALERT;
        $greetingTpl = $settings['greeting_customer'] ?: self::DEFAULT_GREETING;

        $nameDisplay = ! empty($picName) ? $picName : 'Tim Sales';
        $custName = $item['nama'] ?: 'Customer';
        $custPhone = $item['nomor_wa'] ?: '-';
        $normCust = $this->normalizePhone($custPhone);
        $barang = $item['barang_keep'] ?: '-';
        $cabang = $item['cabang'] ?: 'Gadgetmart';
        $catatan = $item['catatan'] ? trim((string) $item['catatan']) : '-';
        $kodeSosmed = $item['kode_sosmed'] ?: '-';
        $jamMasuk = $item['timestamp'] ?: '-';

        $rawGreeting = str_replace(
            ['{customer_name}', '{barang_keep}', '{cabang}', '{kode_sosmed}', '{kode}'],
            [$custName, $barang, $cabang, $kodeSosmed, $kodeSosmed],
            $greetingTpl
        );
        $encodedGreeting = rawurlencode($rawGreeting);
        $waLink = $normCust ? "https://wa.me/{$normCust}?text={$encodedGreeting}" : '-';

        return str_replace(
            ['{pic_name}', '{customer_name}', '{customer_phone}', '{barang_keep}', '{cabang}', '{kode_sosmed}', '{kode}', '{catatan}', '{jam_masuk}', '{chat_link}'],
            [$nameDisplay, $custName, $custPhone, $barang, $cabang, $kodeSosmed, $kodeSosmed, $catatan, $jamMasuk, $waLink],
            $tpl
        );
    }

    /**
     * Send Single 24h Alert to a PIC for one specific Keep item.
     */
    public function sendSingleAlertToPic(string $picPhone, string $picName, array $item): array
    {
        $cleanPhone = $this->normalizePhone($picPhone);
        if ($this->isLidPhone($cleanPhone)) {
            $resolved = $this->resolvePicRealPhone($cleanPhone, $picName);
            if ($resolved) {
                $cleanPhone = $resolved;
            }
        }

        $keepId = $item['id'] ?? null;

        if (! $cleanPhone || strlen($cleanPhone) < 9 || $this->isLidPhone($cleanPhone)) {
            $invalidReason = $this->isLidPhone($picPhone)
                ? "Format nomor terdeteksi LID WhatsApp Multi-Device ('{$picPhone}'), bukan nomor HP seluler"
                : "Nomor WhatsApp PIC tidak valid ('{$picPhone}')";

            if ($keepId) {
                try {
                    SosmedKeep::where('id', $keepId)->update([
                        'fu_h1_status' => 'FAILED',
                        'fu_h1_note' => $invalidReason,
                    ]);
                } catch (Throwable) {}
            }

            return [
                'success' => false,
                'error_type' => 'INVALID_PHONE',
                'message' => "Nomor WhatsApp PIC ({$picName}) belum valid: {$invalidReason}.",
            ];
        }

        $message = $this->buildSingleAlertMessage($picName, $item);

        try {
            $res = $this->watzapService->sendViaBaileys($cleanPhone, $message, '6285111426699');

            if (! empty($res['success'])) {
                if ($keepId) {
                    try {
                        SosmedKeep::where('id', $keepId)->update([
                            'fu_h1_sent_at' => now(),
                            'fu_h1_status' => 'SENT',
                            'fu_h1_note' => 'Dikirim via Baileys Bot (24h Alert)',
                        ]);
                    } catch (Throwable $dbErr) {
                        Log::warning('Gagal update status keep di DB: ' . $dbErr->getMessage());
                    }
                }

                return [
                    'success' => true,
                    'pic_name' => $picName,
                    'pic_phone' => $cleanPhone,
                    'item' => $item,
                    'message' => "Reminder 24 jam berhasil dikirim ke WhatsApp Kak {$picName} ({$cleanPhone}) via Baileys Bot.",
                    'data' => $res,
                ];
            }

            if ($keepId) {
                try {
                    SosmedKeep::where('id', $keepId)->update([
                        'fu_h1_status' => 'FAILED',
                        'fu_h1_note' => 'Gagal via Baileys Bot: ' . ($res['message'] ?? 'Error'),
                    ]);
                } catch (Throwable) {}
            }

            return [
                'success' => false,
                'pic_name' => $picName,
                'pic_phone' => $cleanPhone,
                'message' => 'Gagal via Baileys Bot: ' . ($res['message'] ?? 'Unknown error'),
            ];
        } catch (Throwable $e) {
            Log::error('Send H+1 Single Alert via Baileys Error', [
                'pic_phone' => $cleanPhone,
                'pic_name' => $picName,
                'keep_id' => $keepId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'pic_name' => $picName,
                'pic_phone' => $cleanPhone,
                'message' => 'Exception koneksi Baileys Bot: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send Digest to a single PIC.
     */
    public function sendDigestToPic(string $picPhone, string $picName, array $keeps, string $targetDate): array
    {
        $cleanPhone = $this->normalizePhone($picPhone);
        if ($this->isLidPhone($cleanPhone)) {
            $resolved = $this->resolvePicRealPhone($cleanPhone, $picName);
            if ($resolved) {
                $cleanPhone = $resolved;
            }
        }

        $keepIds = array_filter(array_column($keeps, 'id'));

        if (! $cleanPhone || strlen($cleanPhone) < 9 || $this->isLidPhone($cleanPhone)) {
            $invalidReason = $this->isLidPhone($picPhone)
                ? "Format nomor terdeteksi LID WhatsApp Multi-Device ('{$picPhone}'), bukan nomor HP seluler"
                : "Nomor WhatsApp PIC tidak valid ('{$picPhone}')";

            if (! empty($keepIds)) {
                try {
                    SosmedKeep::whereIn('id', $keepIds)->update([
                        'fu_h1_status' => 'FAILED',
                        'fu_h1_note' => $invalidReason,
                    ]);
                } catch (Throwable $e) {}
            }

            return [
                'success' => false,
                'error_type' => 'INVALID_PHONE',
                'message' => "Nomor WhatsApp PIC ({$picName}) belum valid: {$invalidReason}.",
            ];
        }

        $activeKeeps = array_filter($keeps, function ($k) {
            $st = strtoupper(trim((string) ($k['status'] ?? '')));
            return $st !== 'WIN' && $st !== 'LOSE';
        });

        if (empty($activeKeeps)) {
            return [
                'success' => false,
                'error_type' => 'NO_ACTIVE_KEEPS',
                'message' => "Seluruh customer keep Kak {$picName} sudah WIN/LOSE (tidak ada keep On Progress).",
            ];
        }

        $message = $this->buildDigestMessage($picName, $keeps, $targetDate);

        try {
            // Pengiriman 100% eksklusif Baileys Bot Gateway (+62 851-1142-6699) tanpa fallback WatZap Cloud
            $res = $this->watzapService->sendViaBaileys($cleanPhone, $message, '6285111426699');

            if (! empty($res['success'])) {
                // Update keeps in database safely
                if (! empty($keepIds)) {
                    try {
                        SosmedKeep::whereIn('id', $keepIds)->update([
                            'fu_h1_sent_at' => now(),
                            'fu_h1_status' => 'SENT',
                            'fu_h1_note' => 'Dikirim via ' . ($res['source'] ?? 'Baileys Bot (+62 851-1142-6699)'),
                        ]);
                    } catch (Throwable $dbErr) {
                        Log::warning('Gagal update status keep di DB: ' . $dbErr->getMessage());
                    }
                }

                return [
                    'success' => true,
                    'pic_name' => $picName,
                    'pic_phone' => $cleanPhone,
                    'items_count' => count($activeKeeps),
                    'message' => "Rekap berhasil dikirim ke WhatsApp Kak {$picName} ({$cleanPhone}) via Baileys Bot (+62 851-1142-6699).",
                    'data' => $res,
                ];
            }

            if (! empty($keepIds)) {
                try {
                    SosmedKeep::whereIn('id', $keepIds)->update([
                        'fu_h1_status' => 'FAILED',
                        'fu_h1_note' => 'Gagal via Baileys Bot (+62 851-1142-6699): ' . ($res['message'] ?? 'Error'),
                    ]);
                } catch (Throwable) {}
            }

            return [
                'success' => false,
                'pic_name' => $picName,
                'pic_phone' => $cleanPhone,
                'message' => 'Gagal via Baileys Bot: ' . ($res['message'] ?? 'Unknown error'),
            ];
        } catch (Throwable $e) {
            Log::error('Send H+1 Digest via Baileys Error', [
                'pic_phone' => $cleanPhone,
                'pic_name' => $picName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'pic_name' => $picName,
                'pic_phone' => $cleanPhone,
                'message' => 'Exception koneksi Baileys Bot: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send all pending H+1 digests for a target date with Anti-Suspend Delay and Rest Cycle.
     */
    public function sendAllPendingDigests(?string $targetDate = null, bool $force = false, ?callable $progressCallback = null): array
    {
        $dashboardData = $this->getDashboardData($targetDate);
        $groups = $dashboardData['pic_groups'];
        $date = $dashboardData['target_date'];
        $settings = $this->getSettings();

        $delayMin = max(1, (int) ($settings['delay_min'] ?? 3));
        $delayMax = max($delayMin, (int) ($settings['delay_max'] ?? 8));
        $batchSize = max(2, (int) ($settings['batch_size'] ?? 10));
        $batchCooldown = max(0, (int) ($settings['batch_cooldown'] ?? 30));

        $results = [
            'total_pics' => count($groups),
            'sent_count' => 0,
            'failed_count' => 0,
            'skipped_count' => 0,
            'details' => [],
        ];

        $totalGroups = count($groups);
        $sendAttemptCounter = 0;

        foreach ($groups as $index => $g) {
            // If not forcing, skip groups that have already received WhatsApp reminder
            if (! $force && $g['wa_status'] === 'SENT') {
                $results['skipped_count']++;
                $results['details'][] = [
                    'pic_name' => $g['pic_name'],
                    'pic_phone' => $g['pic_phone'],
                    'status' => 'SKIPPED',
                    'message' => 'Sudah pernah dikirim sebelumnya.',
                ];
                continue;
            }

            // Only send if PIC has active items that are not WIN
            $activeItems = array_filter($g['items'], function ($item) {
                return strtoupper(trim((string) $item['status'])) !== 'WIN';
            });

            if (empty($activeItems)) {
                $results['skipped_count']++;
                $results['details'][] = [
                    'pic_name' => $g['pic_name'],
                    'pic_phone' => $g['pic_phone'],
                    'status' => 'SKIPPED',
                    'message' => 'Seluruh customer PIC ini sudah berstatus WIN (Closing).',
                ];
                continue;
            }

            if (! $g['pic_phone']) {
                $results['failed_count']++;
                $results['details'][] = [
                    'pic_name' => $g['pic_name'],
                    'pic_phone' => null,
                    'status' => 'FAILED',
                    'message' => 'Nomor WhatsApp PIC tidak ditemukan.',
                ];
                continue;
            }

            $sendRes = $this->sendDigestToPic($g['pic_phone'], $g['pic_name'], $g['items'], $date);
            $sendAttemptCounter++;

            if (! empty($sendRes['success'])) {
                $results['sent_count']++;
                $results['details'][] = [
                    'pic_name' => $g['pic_name'],
                    'pic_phone' => $g['pic_phone'],
                    'status' => 'SENT',
                    'message' => $sendRes['message'],
                ];
            } else {
                $results['failed_count']++;
                $results['details'][] = [
                    'pic_name' => $g['pic_name'],
                    'pic_phone' => $g['pic_phone'],
                    'status' => 'FAILED',
                    'message' => $sendRes['message'],
                ];
            }

            // Throttling: Delay Antar Pesan & Rest Cycle (Anti-Suspend WhatsApp)
            $isLast = ($index === $totalGroups - 1);
            if (! $isLast) {
                // Cek apakah mencapai batas batch untuk siklus istirahat (Rest Cycle)
                if ($batchSize > 0 && $batchCooldown > 0 && ($sendAttemptCounter % $batchSize === 0)) {
                    if ($progressCallback) {
                        $progressCallback('REST_CYCLE', [
                            'sent_count' => $sendAttemptCounter,
                            'cooldown_seconds' => $batchCooldown,
                        ]);
                    }
                    sleep($batchCooldown);
                } else {
                    // Jeda acak antar pesan (Humanized Jitter)
                    $sleepSec = rand($delayMin, $delayMax);
                    if ($progressCallback) {
                        $progressCallback('DELAY', [
                            'delay_seconds' => $sleepSec,
                            'sent_count' => $sendAttemptCounter,
                        ]);
                    }
                    sleep($sleepSec);
                }
            }
        }

        return $results;
    }

    /**
     * Scan and Send Strict 1x24 Hour Follow-Up Alerts (Runs every 5 minutes).
     *
     * Operational Rules:
     * - Only runs between work hours (default: 08:00 - 22:00 WITA).
     * - Outside work hours, messages are safely held and postponed.
     * - At 08:00 morning opening: if >= 2 keeps accumulated for a PIC, sent as 1 digest (Rekap Pagi).
     * - Between 08:05 - 22:00: sent as specific Single Alert per customer at the exact 24h milestone.
     */
    public function sendStrict24hAlerts(bool $force = false, ?callable $progressCallback = null): array
    {
        $settings = $this->getSettings();

        if (! $settings['enabled']) {
            return [
                'success' => false,
                'message' => 'Auto Follow-Up H+1 dinonaktifkan di pengaturan.',
                'sent_count' => 0,
                'failed_count' => 0,
                'skipped_count' => 0,
                'details' => [],
            ];
        }

        if (! $this->isWithinWorkHours()) {
            $now = Carbon::now();
            $start = $settings['work_start'] ?? '08:00';
            $end = $settings['work_end'] ?? '22:00';
            return [
                'success' => true,
                'message' => "Di luar jam operasional ({$start} - {$end} WITA). Waktu saat ini: {$now->format('H:i')} WITA. Pengiriman ditunda hingga jam kerja.",
                'sent_count' => 0,
                'failed_count' => 0,
                'skipped_count' => 0,
                'details' => [],
            ];
        }

        $now = Carbon::now();
        $twentyFourHoursAgo = $now->copy()->subHours(24);
        $sevenDaysAgo = $now->copy()->subDays(7);

        $query = SosmedKeep::query()
            ->where(function ($q) use ($twentyFourHoursAgo, $sevenDaysAgo) {
                $q->where(function ($sub) use ($twentyFourHoursAgo, $sevenDaysAgo) {
                    $sub->whereNotNull('keep_timestamp')
                        ->where('keep_timestamp', '<=', $twentyFourHoursAgo)
                        ->where('keep_timestamp', '>=', $sevenDaysAgo);
                })->orWhere(function ($sub) use ($twentyFourHoursAgo, $sevenDaysAgo) {
                    $sub->whereNull('keep_timestamp')
                        ->where('created_at', '<=', $twentyFourHoursAgo)
                        ->where('created_at', '>=', $sevenDaysAgo);
                });
            })
            ->whereNotNull('nomor_wa')
            ->where('nomor_wa', '!=', '')
            ->whereRaw("UPPER(TRIM(COALESCE(status, ''))) NOT IN ('WIN', 'LOSE', 'BATAL', 'CANCEL', 'TAKE OUT')");

        if (! $force) {
            $query->where(function ($q) {
                $q->whereNull('fu_h1_sent_at')
                    ->where(function ($sub) {
                        $sub->whereNull('fu_h1_status')
                            ->orWhere('fu_h1_status', '!=', 'SENT');
                    });
            });
        }

        $eligibleKeeps = $query->orderBy('keep_timestamp', 'asc')->get();

        if ($eligibleKeeps->isEmpty()) {
            return [
                'success' => true,
                'message' => 'Tidak ada format keep yang jatuh tempo 24 jam saat ini.',
                'total_eligible' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'skipped_count' => 0,
                'details' => [],
            ];
        }

        // Group keeps by PIC
        $picGroups = [];
        foreach ($eligibleKeeps as $k) {
            $rawPicPhone = $k->nomor_fu ?: $k->nomor_follow_up;
            $cleanPicPhone = $this->normalizePhone($rawPicPhone);
            $picName = trim((string) ($k->nama_tim_follow_up ?: ''));

            $resolvedRealPhone = $this->resolvePicRealPhone($cleanPicPhone, $picName);
            if ($resolvedRealPhone) {
                $cleanPicPhone = $resolvedRealPhone;
            } elseif ($this->isLidPhone($cleanPicPhone)) {
                $cleanPicPhone = null;
            }

            if (! $cleanPicPhone && ! $picName) {
                continue; // Skip unassigned
            }

            $groupKey = $cleanPicPhone ?: ('NAME_' . strtoupper(preg_replace('/[^A-Z0-9]/', '', $picName)));

            if (! isset($picGroups[$groupKey])) {
                $resolvedPicName = $picName ?: $this->resolvePicNameByPhone($cleanPicPhone);
                $isLid = $this->isLidPhone($rawPicPhone);
                $isValidPhone = ! empty($cleanPicPhone) && ! $this->isLidPhone($cleanPicPhone) && strlen($cleanPicPhone) >= 9;

                $picGroups[$groupKey] = [
                    'group_key' => $groupKey,
                    'pic_name' => $resolvedPicName,
                    'pic_phone' => $cleanPicPhone,
                    'raw_pic_phone' => $rawPicPhone,
                    'is_valid_phone' => $isValidPhone,
                    'is_lid' => $isLid,
                    'items' => [],
                ];
            }

            $picGroups[$groupKey]['items'][] = $this->transformKeepItem($k);
        }

        $delayMin = max(1, (int) ($settings['delay_min'] ?? 3));
        $delayMax = max($delayMin, (int) ($settings['delay_max'] ?? 8));
        $batchSize = max(2, (int) ($settings['batch_size'] ?? 10));
        $batchCooldown = max(0, (int) ($settings['batch_cooldown'] ?? 30));

        $results = [
            'success' => true,
            'message' => 'Selesai memproses antrean follow-up 24 jam.',
            'total_eligible' => $eligibleKeeps->count(),
            'total_pics' => count($picGroups),
            'sent_count' => 0,
            'failed_count' => 0,
            'skipped_count' => 0,
            'details' => [],
        ];

        $groupsList = array_values($picGroups);
        $totalGroups = count($groupsList);
        $sendAttemptCounter = 0;

        foreach ($groupsList as $index => $group) {
            $picName = $group['pic_name'];
            $picPhone = $group['pic_phone'];
            $items = $group['items'];
            $itemsCount = count($items);

            if (! $group['is_valid_phone']) {
                $invalidReason = $group['is_lid']
                    ? "Format nomor terdeteksi LID WhatsApp Multi-Device ('{$group['raw_pic_phone']}'), bukan nomor HP seluler"
                    : "Nomor WhatsApp PIC tidak valid ('{$group['raw_pic_phone']}')";

                $keepIds = array_column($items, 'id');
                if (! empty($keepIds)) {
                    try {
                        SosmedKeep::whereIn('id', $keepIds)->update([
                            'fu_h1_status' => 'FAILED',
                            'fu_h1_note' => $invalidReason,
                        ]);
                    } catch (Throwable) {}
                }

                $results['failed_count'] += $itemsCount;
                $results['details'][] = [
                    'pic_name' => $picName,
                    'pic_phone' => $picPhone,
                    'items_count' => $itemsCount,
                    'mode' => $itemsCount > 1 ? 'DIGEST' : 'SINGLE',
                    'status' => 'FAILED',
                    'message' => "Nomor WhatsApp PIC tidak valid: {$invalidReason}",
                ];
                continue;
            }

            // Decide model: If >= 2 keeps (e.g. overnight batch at morning opening) -> Send Digest. If 1 -> Send Single Alert.
            if ($itemsCount >= 2) {
                $targetDate = Carbon::yesterday()->toDateString();
                $sendRes = $this->sendDigestToPic($picPhone, $picName, $items, $targetDate);
                $mode = 'DIGEST';
            } else {
                $sendRes = $this->sendSingleAlertToPic($picPhone, $picName, $items[0]);
                $mode = 'SINGLE';
            }

            $sendAttemptCounter++;

            if (! empty($sendRes['success'])) {
                $results['sent_count'] += $itemsCount;
                $results['details'][] = [
                    'pic_name' => $picName,
                    'pic_phone' => $picPhone,
                    'items_count' => $itemsCount,
                    'mode' => $mode,
                    'status' => 'SENT',
                    'message' => $sendRes['message'],
                ];
            } else {
                $results['failed_count'] += $itemsCount;
                $results['details'][] = [
                    'pic_name' => $picName,
                    'pic_phone' => $picPhone,
                    'items_count' => $itemsCount,
                    'mode' => $mode,
                    'status' => 'FAILED',
                    'message' => $sendRes['message'],
                ];
            }

            // Throttling: Delay & Rest Cycle
            $isLast = ($index === $totalGroups - 1);
            if (! $isLast) {
                if ($batchSize > 0 && $batchCooldown > 0 && ($sendAttemptCounter % $batchSize === 0)) {
                    if ($progressCallback) {
                        $progressCallback('REST_CYCLE', [
                            'sent_count' => $sendAttemptCounter,
                            'cooldown_seconds' => $batchCooldown,
                        ]);
                    }
                    sleep($batchCooldown);
                } else {
                    $sleepSec = rand($delayMin, $delayMax);
                    if ($progressCallback) {
                        $progressCallback('DELAY', [
                            'delay_seconds' => $sleepSec,
                            'sent_count' => $sendAttemptCounter,
                        ]);
                    }
                    sleep($sleepSec);
                }
            }
        }

        return $results;
    }

    /**
     * Transform Keep item for clean API response.
     */
    protected function transformKeepItem(SosmedKeep $k): array
    {
        $cleanCustWA = $this->normalizePhone($k->nomor_wa);

        return [
            'id' => $k->id,
            'message_id' => $k->message_id,
            'timestamp' => $this->formatDateTimeSafely($k->keep_timestamp) ?: ($this->formatDateTimeSafely($k->created_at) ?: '-'),
            'nama' => $k->nama ?: '-',
            'nomor_wa' => $k->nomor_wa ?: '-',
            'nomor_wa_norm' => $cleanCustWA,
            'barang_keep' => $k->barang_keep ?: '-',
            'catatan' => $k->catatan ?: '-',
            'cabang' => $k->cabang ?: '-',
            'kode_sosmed' => $k->kode_sosmed ?: '-',
            'report_ar' => $k->report_ar ?: '-',
            'status' => $k->status ?: 'ON PROGRESS',
            'nomor_fu' => $k->nomor_fu ?: $k->nomor_follow_up,
            'nama_tim_follow_up' => $k->nama_tim_follow_up ?: '-',
            'fu_h1_sent_at' => $this->formatDateTimeSafely($k->fu_h1_sent_at),
            'fu_h1_status' => $k->fu_h1_status ?: 'PENDING',
            'fu_h1_note' => $k->fu_h1_note ?: null,
        ];
    }

    /**
     * Safely format datetime to string without crashing on strings.
     */
    protected function formatDateTimeSafely(mixed $val): ?string
    {
        if (empty($val)) return null;
        if ($val instanceof \DateTimeInterface) {
            return $val->format('d/m/Y H:i');
        }
        try {
            return Carbon::parse($val)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $val;
        }
    }

    /**
     * Normalize phone to 628xxx.
     */
    protected function normalizePhone(?string $phone): string
    {
        if (! $phone) return '';
        $digits = preg_replace('/\D/', '', (string) $phone);
        if ($digits === '') return '';

        if (str_starts_with($digits, '08')) {
            return '628' . substr($digits, 2);
        }
        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }
        return $digits;
    }

    /**
     * Format phone for display (0812-3456-7890).
     */
    protected function formatPhoneDisplay(?string $phone): string
    {
        if (! $phone) return '-';
        $norm = $this->normalizePhone($phone);
        if (str_starts_with($norm, '628')) {
            $local = '08' . substr($norm, 3);
            if (strlen($local) >= 10) {
                return substr($local, 0, 4) . '-' . substr($local, 4, 4) . '-' . substr($local, 8);
            }
            return $local;
        }
        return $phone;
    }

    /**
     * Try to find employee name by phone.
     */
    protected function resolvePicNameByPhone(string $cleanPhone): string
    {
        if (! $cleanPhone) return 'Tim Sales';
        $last8 = substr($cleanPhone, -8);
        $emp = Employee::where('phone', 'like', "%{$last8}%")->first();
        if ($emp && ! empty($emp->name)) {
            return $emp->name;
        }
        return 'PIC (' . $this->formatPhoneDisplay($cleanPhone) . ')';
    }

    /**
     * Check if a phone string is a WhatsApp LID (Linked Device Identifier, not a real mobile number).
     */
    public function isLidPhone(?string $phone): bool
    {
        if (! $phone) return false;
        $clean = preg_replace('/\D+/', '', $phone);
        if (strlen($clean) > 13) return true;
        if (! str_starts_with($clean, '628') && ! str_starts_with($clean, '08') && ! str_starts_with($clean, '8')) return true;
        return false;
    }

    /**
     * Smart resolve real phone number if current phone is WhatsApp LID or missing.
     */
    public function resolvePicRealPhone(?string $phone, ?string $salesName): ?string
    {
        $clean = $this->normalizePhone($phone);
        $isLid = $this->isLidPhone($clean);

        if (! $isLid && strlen($clean) >= 9 && strlen($clean) <= 14) {
            return $clean;
        }

        if (empty($salesName)) {
            return $isLid ? null : $clean;
        }

        $cacheKey = $salesName . '|' . ($clean ?: '');
        if (array_key_exists($cacheKey, $this->resolvedPhonesCache)) {
            return $this->resolvedPhonesCache[$cacheKey];
        }

        // Ekstrak kode dan nama dari salesName (e.g. "BJM.SOS.015 - DANIEL" atau "SC.MTP.001 - IRMADAMAYANTI")
        $codePart = '';
        $namePart = trim((string) $salesName);
        if (str_contains($salesName, '-')) {
            $parts = explode('-', $salesName);
            $codePart = trim($parts[0]);
            $namePart = trim(end($parts));
        }

        // 0. Cek mapping nomor PIC dari riwayat SosmedKeep (preload sekali saja untuk semua baris)
        if ($this->cachedSosmedKeepPhones === null) {
            $this->cachedSosmedKeepPhones = [];
            try {
                $picRecords = SosmedKeep::whereNotNull('nomor_fu')
                    ->where('nomor_fu', '!=', '')
                    ->whereNotNull('nama_tim_follow_up')
                    ->where('nama_tim_follow_up', '!=', '')
                    ->orderByDesc('id')
                    ->limit(200)
                    ->get(['nama_tim_follow_up', 'nomor_fu']);

                foreach ($picRecords as $pr) {
                    $norm = $this->normalizePhone($pr->nomor_fu);
                    if ($norm && ! $this->isLidPhone($norm) && strlen($norm) >= 9 && strlen($norm) <= 14) {
                        $nmKey = strtolower(trim((string) $pr->nama_tim_follow_up));
                        if (! isset($this->cachedSosmedKeepPhones[$nmKey])) {
                            $this->cachedSosmedKeepPhones[$nmKey] = $norm;
                        }
                    }
                }
            } catch (\Throwable) {}
        }

        $salesKey = strtolower($salesName);
        if (isset($this->cachedSosmedKeepPhones[$salesKey])) {
            return $this->resolvedPhonesCache[$cacheKey] = $this->cachedSosmedKeepPhones[$salesKey];
        }
        if ($namePart && strlen($namePart) >= 3) {
            $nameKey = strtolower($namePart);
            if (isset($this->cachedSosmedKeepPhones[$nameKey])) {
                return $this->resolvedPhonesCache[$cacheKey] = $this->cachedSosmedKeepPhones[$nameKey];
            }
        }

        // 1 & 2. Preload Employee satu kali saja untuk semua baris
        if ($this->cachedEmployees === null) {
            try {
                $this->cachedEmployees = Employee::whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->get(['id', 'nik', 'name', 'phone']);
            } catch (\Throwable) {
                $this->cachedEmployees = collect();
            }
        }

        // Cek berdasarkan NIK/kode
        if ($codePart) {
            $emp = $this->cachedEmployees->first(function ($e) use ($codePart) {
                return ! empty($e->nik) && stripos((string) $e->nik, $codePart) !== false;
            });
            if ($emp && ! empty($emp->phone)) {
                $empNorm = $this->normalizePhone($emp->phone);
                if (! $this->isLidPhone($empNorm) && strlen($empNorm) >= 9 && strlen($empNorm) <= 14) {
                    return $this->resolvedPhonesCache[$cacheKey] = $empNorm;
                }
            }
        }

        // Cek berdasarkan nama di Employee
        if ($namePart && strlen($namePart) >= 3) {
            $emp = $this->cachedEmployees->first(function ($e) use ($namePart) {
                return ! empty($e->name) && stripos((string) $e->name, $namePart) !== false;
            });
            if ($emp && ! empty($emp->phone)) {
                $empNorm = $this->normalizePhone($emp->phone);
                if (! $this->isLidPhone($empNorm) && strlen($empNorm) >= 9 && strlen($empNorm) <= 14) {
                    return $this->resolvedPhonesCache[$cacheKey] = $empNorm;
                }
            }

            // 3. Preload User satu kali saja
            if ($this->cachedUsers === null) {
                try {
                    $this->cachedUsers = User::whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->get(['id', 'name', 'phone']);
                } catch (\Throwable) {
                    $this->cachedUsers = collect();
                }
            }

            $u = $this->cachedUsers->first(function ($user) use ($namePart) {
                return ! empty($user->name) && stripos((string) $user->name, $namePart) !== false;
            });
            if ($u && ! empty($u->phone)) {
                $uNorm = $this->normalizePhone($u->phone);
                if (! $this->isLidPhone($uNorm) && strlen($uNorm) >= 9 && strlen($uNorm) <= 14) {
                    return $this->resolvedPhonesCache[$cacheKey] = $uNorm;
                }
            }
        }

        return $this->resolvedPhonesCache[$cacheKey] = ($isLid ? null : $clean);
    }
}
