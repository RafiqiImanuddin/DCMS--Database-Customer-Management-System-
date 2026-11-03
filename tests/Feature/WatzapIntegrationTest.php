<?php

namespace Tests\Feature;

use App\Models\BroadcastReport;
use App\Models\RepeatOrderPrediction;
use App\Models\Setting;
use App\Models\User;
use App\Services\BroadcastService;
use App\Services\WatzapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WatzapIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_and_save_watzap_settings(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->get(route('settings.watzap.index'));
        $response->assertOk();
        $response->assertSee('Pengaturan WatZap.id');

        $postResp = $this->actingAs($superadmin)->post(route('settings.watzap.update'), [
            'watzap_api_key' => 'wt_test_secret_123',
            'watzap_number_key' => 'nk_device_456',
            'watzap_web_url' => 'https://watzap.chat',
        ]);

        $postResp->assertRedirect(route('settings.watzap.index'));
        $this->assertEquals('wt_test_secret_123', Setting::get('watzap_api_key'));
        $this->assertEquals('nk_device_456', Setting::get('watzap_number_key'));
        $this->assertEquals('https://watzap.chat', Setting::get('watzap_web_url'));
    }

    public function test_admin_cannot_access_watzap_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('settings.watzap.index'));
        $response->assertForbidden();

        $postResp = $this->actingAs($admin)->post(route('settings.watzap.update'), [
            'watzap_api_key' => 'hacked_key',
        ]);
        $postResp->assertForbidden();
    }

    public function test_both_superadmin_and_admin_can_access_whatsapp_page(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $respSuper = $this->actingAs($superadmin)->get(route('whatsapp.index'));
        $respSuper->assertOk();
        $respSuper->assertSee('WhatsApp', false);
        $respSuper->assertSee('Gadget', false);

        $respAdmin = $this->actingAs($admin)->get(route('whatsapp.index'));
        $respAdmin->assertOk();
        $respAdmin->assertSee('WhatsApp', false);
        $respAdmin->assertSee('Gadget', false);
    }

    public function test_watzap_service_phone_normalization(): void
    {
        $service = app(WatzapService::class);

        $this->assertEquals('628123456789', $service->normalizePhone('08123456789'));
        $this->assertEquals('628123456789', $service->normalizePhone('8123456789'));
        $this->assertEquals('628123456789', $service->normalizePhone('+62 812-3456-789'));
        $this->assertEquals('628123456789', $service->normalizePhone('628123456789'));
        $this->assertNull($service->normalizePhone('12345')); // too short
        $this->assertNull($service->normalizePhone(null));
    }

    public function test_broadcast_service_sends_via_watzap_api_when_configured(): void
    {
        Setting::set('watzap_api_key', 'test_key');
        Setting::set('watzap_number_key', 'test_number');

        Http::fake([
            'https://api.watzap.id/v1/send_message' => Http::response([
                'status' => '200',
                'ack' => 'successfully',
                'message' => 'Message sent successfully',
            ], 200),
        ]);

        $user = User::factory()->create(['role' => 'admin']);
        $prediction = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'CUST001',
            'customer_vendor_name' => 'Budi Santoso',
            'no_hp_customer' => '081234567890',
            'item_code' => 'GAD.XMI.01',
            'classification' => 'tinggi',
        ]);

        $service = app(BroadcastService::class);
        $report = $service->send($prediction, 'Halo Kak Budi, ada promo menarik!', $user->id);

        $this->assertEquals('sent', $report->status);
        $this->assertNotNull($report->sent_at);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.watzap.id/v1/send_message'
                && $request['api_key'] === 'test_key'
                && $request['number_key'] === 'test_number'
                && $request['phone_no'] === '6281234567890'
                && $request['message'] === 'Halo Kak Budi, ada promo menarik!';
        });
    }

    public function test_superadmin_can_test_check_api_key(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        Http::fake([
            'https://api.watzap.id/v1/checking_key' => Http::response([
                'status' => '200',
                'ack' => 'successfully',
                'message' => 'API key is valid',
                'data' => [
                    'account_name' => 'Gadgetmart Official',
                    'package' => 'Premium',
                    'senders' => [
                        ['number_key' => 'nk_123', 'phone_number' => '62811111111', 'status' => 'Online'],
                    ],
                ],
            ], 200),
        ]);

        $resp = $this->actingAs($superadmin)->postJson(route('settings.watzap.check'), [
            'api_key' => 'valid_key_test',
        ]);

        $resp->assertOk();
        $resp->assertJsonPath('success', true);
        $resp->assertJsonPath('message', 'API key is valid');
    }

    public function test_staff_can_send_message_in_whatsapp_chat(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chat = \App\Models\WhatsAppChat::create([
            'customer_name' => 'Test Customer',
            'phone_number' => '628999888777',
            'cabang' => 'GM AMT',
            'folder' => 'all',
            'format_type' => 'Keep',
        ]);

        $resp = $this->actingAs($admin)->post(route('whatsapp.send', $chat->id), [
            'message' => 'Halo kak, barang ready!',
        ]);

        $resp->assertRedirect();
        $this->assertDatabaseHas('whatsapp_messages', [
            'whatsapp_chat_id' => $chat->id,
            'message' => 'Halo kak, barang ready!',
            'sender_type' => 'agent',
        ]);
    }

    public function test_staff_can_save_crm_data_in_whatsapp(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chat = \App\Models\WhatsAppChat::create([
            'customer_name' => 'Amel',
            'phone_number' => '628999888777',
            'cabang' => 'TANPA CABANG',
            'folder' => 'all',
        ]);

        $resp = $this->actingAs($admin)->post(route('whatsapp.crm', $chat->id), [
            'customer_name' => 'Amel Updated',
            'phone_number' => '628999888777',
            'cabang' => 'GM AMT',
            'format_type' => 'Keep',
            'barang_keep' => 'Infinix XPAD 20 PRO',
            'tanggal_pengambilan' => '2026-11-10',
            'notes' => 'Catatan customer',
            'tags' => ['potensi', 'infinix'],
        ]);

        $resp->assertRedirect();
        $chat->refresh();
        $this->assertEquals('Amel Updated', $chat->customer_name);
        $this->assertEquals('GM AMT', $chat->cabang);
        $this->assertEquals('Infinix XPAD 20 PRO', $chat->barang_keep);
        $this->assertContains('potensi', $chat->tags);
    }
}
