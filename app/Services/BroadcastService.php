<?php

namespace App\Services;

use App\Models\BroadcastReport;
use App\Models\RepeatOrderPrediction;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Throwable;

class BroadcastService
{
    public function __construct(
        protected WatzapService $watzap
    ) {}

    public function send(RepeatOrderPrediction $prediction, string $message, int $userId, ?string $imageUrl = null, ?int $batchId = null): BroadcastReport
    {
        // Spintax processing: {opsi1|opsi2|opsi3} jika diaktifkan
        $spintaxEnabled = Setting::get('broadcast_spintax_enabled', '1') === '1';
        $preparedMessage = $spintaxEnabled ? $this->processSpintax($message) : $message;

        // Personalize message placeholders
        $rawName = trim((string) $prediction->customer_vendor_name);
        $customerName = ($rawName !== '' && strcasecmp($rawName, 'No Name') !== 0) ? $rawName : 'Kak';
        $itemDesc = trim((string) ($prediction->dscription ?: ($prediction->item_code ?: 'produk')));
        $customerCode = trim((string) ($prediction->customer_vendor_code ?: ''));

        $personalizedMessage = str_replace(
            ['{nama}', '{nama_pelanggan}', '{item}', '{kode}'],
            [$customerName, $customerName, $itemDesc, $customerCode],
            $preparedMessage
        );

        $report = BroadcastReport::create([
            'user_id' => $userId,
            'repeat_order_prediction_id' => $prediction->id,
            'broadcast_batch_id' => $batchId,
            'customer_vendor_code' => $prediction->customer_vendor_code,
            'phone_number' => $prediction->no_hp_customer,
            'channel' => 'whatsapp',
            'message' => $personalizedMessage,
            'image_url' => $imageUrl,
            'status' => 'queued',
        ]);

        if (! $prediction->no_hp_customer) {
            $report->update([
                'status' => 'logged',
                'provider_response' => 'Nomor WhatsApp pelanggan belum tersedia. Pesan tersimpan sebagai laporan.',
            ]);

            return $report;
        }

        if (! $this->watzap->isConfigured()) {
            $report->update([
                'status' => 'logged',
                'provider_response' => 'WatZap belum dikonfigurasi. Silakan isi API Key dan Number Key di menu Pengaturan Super Admin.',
            ]);

            return $report;
        }

        try {
            if ($imageUrl !== null && trim($imageUrl) !== '') {
                $res = $this->watzap->sendImage($prediction->no_hp_customer, $imageUrl, $personalizedMessage);
            } else {
                $res = $this->watzap->sendMessage($prediction->no_hp_customer, $personalizedMessage);
            }

            $report->update([
                'status' => $res['success'] ? 'sent' : 'failed',
                'provider_response' => $res['message'].($res['raw'] ? ' | '.$res['raw'] : ''),
                'sent_at' => $res['success'] ? now() : null,
            ]);

            if ($res['success']) {
                $this->recordCycleStart();
            }
        } catch (Throwable $exception) {
            $report->update([
                'status' => 'failed',
                'provider_response' => 'Terjadi kesalahan: '.$exception->getMessage(),
            ]);
        }

        return $report;
    }

    /**
     * Process Spintax formatting: {option1|option2|option3}
     * Randomly picks one variation for each block to make every sent message unique and prevent WhatsApp anti-spam bans.
     */
    public function processSpintax(string $text): string
    {
        return (string) preg_replace_callback('/\{([^{}]+)\}/', function ($matches) {
            $content = $matches[1];
            // Skip recognized system variables
            if (in_array(strtolower($content), ['nama', 'nama_pelanggan', 'item', 'kode'], true)) {
                return '{'.$content.'}';
            }
            if (str_contains($content, '|')) {
                $options = array_map('trim', explode('|', $content));
                $filtered = array_filter($options, fn ($opt) => $opt !== '');
                if (! empty($filtered)) {
                    $keys = array_keys($filtered);
                    return $filtered[$keys[array_rand($keys)]];
                }
            }
            return $matches[0];
        }, $text);
    }

    /**
     * Start the 24-hour cycle if it has not been started yet or if the previous cycle has expired.
     */
    public function recordCycleStart(): void
    {
        $cycleStartedAtStr = Setting::get('broadcast_cycle_started_at');
        $isActive = false;

        if ($cycleStartedAtStr) {
            try {
                $started = Carbon::parse($cycleStartedAtStr);
                if (now()->lessThan($started->copy()->addHours(24))) {
                    $isActive = true;
                }
            } catch (Throwable $e) {
                $isActive = false;
            }
        }

        if (! $isActive) {
            Setting::set('broadcast_cycle_started_at', now()->toIso8601String());
        }
    }

    /**
     * Reset the 24-hour cycle manually.
     */
    public function resetCycle(): void
    {
        Setting::forget('broadcast_cycle_started_at');
    }

    /**
     * Get information about the current 1x24-hour broadcast cycle.
     *
     * @return array{
     *     is_active: bool,
     *     started_at: ?Carbon,
     *     reset_at: ?Carbon,
     *     remaining_seconds: int,
     *     remaining_text: string,
     *     remaining_human: string,
     *     reset_time_formatted: string,
     *     status_description: string,
     *     sent_count: int,
     *     daily_limit: int,
     *     remaining_quota: int
     * }
     */
    public function getCycleInfo(): array
    {
        $dailyLimit = (int) Setting::get('broadcast_daily_limit', '250');
        $cycleStartedAtStr = Setting::get('broadcast_cycle_started_at');

        $cycleStartedAt = null;
        if ($cycleStartedAtStr) {
            try {
                $cycleStartedAt = Carbon::parse($cycleStartedAtStr);
            } catch (Throwable $e) {
                $cycleStartedAt = null;
            }
        }

        // If no stored cycle start timestamp, check if there's any broadcast sent in the last 24h to bootstrap
        if (! $cycleStartedAt) {
            $earliestSent = BroadcastReport::where('status', 'sent')
                ->where('sent_at', '>=', now()->subHours(24))
                ->orderBy('sent_at', 'asc')
                ->first();

            if ($earliestSent && $earliestSent->sent_at) {
                $cycleStartedAt = $earliestSent->sent_at;
                Setting::set('broadcast_cycle_started_at', $cycleStartedAt->toIso8601String());
            }
        }

        $now = now();
        $isActive = false;
        $cycleResetAt = null;
        $remainingSeconds = 0;
        $sentCount = 0;

        if ($cycleStartedAt) {
            $cycleResetAt = $cycleStartedAt->copy()->addHours(24);

            if ($now->lessThan($cycleResetAt)) {
                $isActive = true;
                $remainingSeconds = (int) round(max(0, $now->diffInSeconds($cycleResetAt)));
                $sentCount = BroadcastReport::where('status', 'sent')
                    ->where('sent_at', '>=', $cycleStartedAt)
                    ->where('sent_at', '<=', $cycleResetAt)
                    ->count();
            } else {
                $isActive = false;
                $sentCount = 0;
            }
        }

        if ($isActive && $cycleResetAt) {
            $hours = (int) floor($remainingSeconds / 3600);
            $minutes = (int) floor(($remainingSeconds % 3600) / 60);
            $seconds = (int) ($remainingSeconds % 60);

            if ($hours > 0) {
                $remainingText = "{$hours} jam {$minutes} mnt";
                $remainingHuman = "{$hours}j {$minutes}m";
            } elseif ($minutes > 0) {
                $remainingText = "{$minutes} menit {$seconds} dtk";
                $remainingHuman = "{$minutes}m {$seconds}s";
            } else {
                $remainingText = "{$seconds} detik";
                $remainingHuman = "{$seconds}s";
            }

            $resetTimeFormatted = $cycleResetAt->translatedFormat('d M H:i').' WIB';
            $statusDescription = "Reset otomatis pada {$resetTimeFormatted} (sisa {$remainingText})";
        } else {
            $remainingText = '1x24 jam sejak broadcast pertama';
            $remainingHuman = '24 jam';
            $resetTimeFormatted = 'Setelah broadcast pertama';
            $statusDescription = 'Siklus 24 jam belum berjalan (akan aktif otomatis 1x24 jam sejak broadcast pertama dikirim)';
        }

        $remainingQuota = max(0, $dailyLimit - $sentCount);

        return [
            'is_active' => $isActive,
            'started_at' => $isActive ? $cycleStartedAt : null,
            'reset_at' => $isActive ? $cycleResetAt : null,
            'remaining_seconds' => $remainingSeconds,
            'remaining_text' => $remainingText,
            'remaining_human' => $remainingHuman,
            'reset_time_formatted' => $resetTimeFormatted,
            'status_description' => $statusDescription,
            'sent_count' => $sentCount,
            'daily_limit' => $dailyLimit,
            'remaining_quota' => $remainingQuota,
        ];
    }
}
