<?php

namespace App\Services;

use App\Models\BroadcastReport;
use App\Models\RepeatOrderPrediction;
use Throwable;

class BroadcastService
{
    public function __construct(
        protected WatzapService $watzap
    ) {}

    public function send(RepeatOrderPrediction $prediction, string $message, int $userId): BroadcastReport
    {
        $report = BroadcastReport::create([
            'user_id' => $userId,
            'repeat_order_prediction_id' => $prediction->id,
            'customer_vendor_code' => $prediction->customer_vendor_code,
            'phone_number' => $prediction->no_hp_customer,
            'channel' => 'whatsapp',
            'message' => $message,
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
            $res = $this->watzap->sendMessage($prediction->no_hp_customer, $message);

            $report->update([
                'status' => $res['success'] ? 'sent' : 'failed',
                'provider_response' => $res['message'].($res['raw'] ? ' | '.$res['raw'] : ''),
                'sent_at' => $res['success'] ? now() : null,
            ]);
        } catch (Throwable $exception) {
            $report->update([
                'status' => 'failed',
                'provider_response' => 'Terjadi kesalahan: '.$exception->getMessage(),
            ]);
        }

        return $report;
    }
}
