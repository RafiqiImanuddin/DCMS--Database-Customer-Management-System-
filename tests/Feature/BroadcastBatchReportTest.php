<?php

namespace Tests\Feature;

use App\Models\BroadcastBatch;
use App\Models\BroadcastReport;
use App\Models\RepeatOrderPrediction;
use App\Models\User;
use App\Services\WatzapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BroadcastBatchReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_manage_broadcast_page_with_batches(): void
    {
        $user = User::factory()->create();

        $batch = BroadcastBatch::create([
            'user_id' => $user->id,
            'title' => 'REMINDER H-1',
            'message' => 'Halo Kak, ada pengingat service rutin.',
            'total_recipients' => 15,
            'total_sent' => 14,
            'total_failed' => 1,
            'delivery_rate' => 93.33,
            'status' => 'completed',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('broadcasts.index'));

        $response->assertOk();
        $response->assertSee('Manage Broadcast');
        $response->assertSee('REMINDER H-1');
        $response->assertSee('14');
        $response->assertSee('/ 15');
        $response->assertSee('93.33%');
        $response->assertSee('Completed');
    }

    public function test_bulk_broadcast_creates_batch_with_calculated_metrics(): void
    {
        $user = User::factory()->create();

        $pred1 = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C9900122',
            'customer_vendor_name' => 'Budi',
            'no_hp_customer' => '081234567890',
            'item_code' => 'IPHONE15',
            'classification' => 'tinggi',
        ]);

        $pred2 = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C9900076',
            'customer_vendor_name' => 'Dewi',
            'no_hp_customer' => '089876543210',
            'item_code' => 'IPHONE15',
            'classification' => 'tinggi',
        ]);

        $watzapMock = Mockery::mock(WatzapService::class);
        $watzapMock->shouldReceive('isConfigured')->andReturn(true);
        $watzapMock->shouldReceive('sendMessage')
            ->twice()
            ->andReturn([
                'success' => true,
                'message' => 'Pesan terkirim',
                'status' => '200',
                'data' => [],
                'raw' => '',
            ]);

        $this->app->instance(WatzapService::class, $watzapMock);

        $response = $this->actingAs($user)->post(route('broadcasts.bulk'), [
            'title' => 'PROMO 10.10',
            'prediction_ids' => [$pred1->id, $pred2->id],
            'message' => 'Halo {nama}, nikmati promo {item} spesial!',
        ]);

        $response->assertRedirect(route('predictions.index'));

        $this->assertDatabaseHas('broadcast_batches', [
            'user_id' => $user->id,
            'title' => 'PROMO 10.10',
            'total_recipients' => 2,
            'total_sent' => 2,
            'total_failed' => 0,
            'delivery_rate' => 100.00,
            'status' => 'completed',
        ]);

        $batch = BroadcastBatch::where('title', 'PROMO 10.10')->first();
        $this->assertNotNull($batch);

        $this->assertDatabaseHas('broadcast_reports', [
            'broadcast_batch_id' => $batch->id,
            'customer_vendor_code' => 'C9900122',
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('broadcast_reports', [
            'broadcast_batch_id' => $batch->id,
            'customer_vendor_code' => 'C9900076',
            'status' => 'sent',
        ]);
    }

    public function test_user_can_view_batch_detail_and_recipients(): void
    {
        $user = User::factory()->create();

        $batch = BroadcastBatch::create([
            'user_id' => $user->id,
            'title' => 'OKTOSPILL',
            'message' => 'Promo heboh bulan Oktober untuk Anda.',
            'total_recipients' => 2,
            'total_sent' => 2,
            'total_failed' => 0,
            'delivery_rate' => 100.0,
            'status' => 'completed',
            'sent_at' => now(),
        ]);

        BroadcastReport::create([
            'user_id' => $user->id,
            'broadcast_batch_id' => $batch->id,
            'customer_vendor_code' => 'CUST_OKTO_1',
            'phone_number' => '08111111111',
            'channel' => 'whatsapp',
            'message' => 'Halo CUST_OKTO_1...',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('broadcasts.batches.show', $batch));

        $response->assertOk();
        $response->assertSee('OKTOSPILL');
        $response->assertSee('CUST_OKTO_1');
        $response->assertSee('08111111111');
        $response->assertSee('Terkirim');
    }

    public function test_user_can_download_batch_excel(): void
    {
        $user = User::factory()->create();

        $batch = BroadcastBatch::create([
            'user_id' => $user->id,
            'title' => 'AFTER SERVICE H+1',
            'message' => 'Bagaimana pelayanan kemarin Kak?',
            'total_recipients' => 1,
            'total_sent' => 1,
            'total_failed' => 0,
            'delivery_rate' => 100.0,
            'status' => 'completed',
            'sent_at' => now(),
        ]);

        BroadcastReport::create([
            'user_id' => $user->id,
            'broadcast_batch_id' => $batch->id,
            'customer_vendor_code' => 'CUST_SERV_1',
            'phone_number' => '08222222222',
            'channel' => 'whatsapp',
            'message' => 'Bagaimana pelayanan kemarin Kak?',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('broadcasts.batches.download', $batch));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml', (string) $response->headers->get('content-type'));
    }

    public function test_user_can_delete_batch_and_its_reports(): void
    {
        $user = User::factory()->create();

        $batch = BroadcastBatch::create([
            'user_id' => $user->id,
            'title' => 'BATCH_TO_DELETE',
            'message' => 'Testing delete batch',
            'total_recipients' => 1,
            'total_sent' => 1,
            'total_failed' => 0,
            'delivery_rate' => 100.0,
            'status' => 'completed',
            'sent_at' => now(),
        ]);

        $report = BroadcastReport::create([
            'user_id' => $user->id,
            'broadcast_batch_id' => $batch->id,
            'customer_vendor_code' => 'CUST_DEL',
            'phone_number' => '08333333333',
            'channel' => 'whatsapp',
            'message' => 'Testing delete batch',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->delete(route('broadcasts.batches.destroy', $batch));

        $response->assertRedirect(route('broadcasts.index'));
        $this->assertDatabaseMissing('broadcast_batches', ['id' => $batch->id]);
        $this->assertDatabaseMissing('broadcast_reports', ['id' => $report->id]);
    }
}

