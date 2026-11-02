<?php

namespace App\Services;

use App\Models\BroadcastReport;
use App\Models\RepeatOrderPrediction;
use Illuminate\Support\Facades\Http;
use Throwable;

class BroadcastService
{
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

        $webhookUrl = config('services.whatsapp.webhook_url');
        if (! $webhookUrl || ! $prediction->no_hp_customer) {
            $report->update([
                'status' => 'logged',
                'provider_response' => $prediction->no_hp_customer
                    ? 'Webhook WhatsApp belum dikonfigurasi. Pesan tersimpan sebagai laporan.'
                    : 'Nomor WhatsApp pelanggan belum tersedia. Pesan tersimpan sebagai laporan.',
            ]);

            return $report;
        }

        try {
            $response = Http::timeout(15)->post($webhookUrl, [
                'phone' => $prediction->no_hp_customer,
                'message' => $message,
                'customer_code' => $prediction->customer_vendor_code,
                'customer_name' => $prediction->customer_vendor_name,
            ]);

            $report->update([
                'status' => $response->successful() ? 'sent' : 'failed',
                'provider_response' => $response->body(),
                'sent_at' => $response->successful() ? now() : null,
            ]);
        } catch (Throwable $exception) {
            $report->update([
                'status' => 'failed',
                'provider_response' => $exception->getMessage(),
            ]);
        }

        return $report;
    }
}
