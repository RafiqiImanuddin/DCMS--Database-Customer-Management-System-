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

    public function send(RepeatOrderPrediction $prediction, string $message, int $userId, ?string $imageUrl = null, ?int $batchId = null): BroadcastReport
    {
        // Personalize message placeholders
        $customerName = trim((string) ($prediction->customer_vendor_name ?: 'Kaka'));
        $itemDesc = trim((string) ($prediction->dscription ?: ($prediction->item_code ?: 'produk')));
        $customerCode = trim((string) ($prediction->customer_vendor_code ?: ''));

        $personalizedMessage = str_replace(
            ['{nama}', '{nama_pelanggan}', '{item}', '{kode}'],
            [$customerName, $customerName, $itemDesc, $customerCode],
            $message
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
        } catch (Throwable $exception) {
            $report->update([
                'status' => 'failed',
                'provider_response' => 'Terjadi kesalahan: '.$exception->getMessage(),
            ]);
        }

        return $report;
    }
}
