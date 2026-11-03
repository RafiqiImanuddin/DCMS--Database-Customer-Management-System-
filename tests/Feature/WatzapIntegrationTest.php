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

    public function test_superadmin_can_access_watzap_settings_page(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->get(route('settings.watzap.index'));
        $response->assertOk();
        $response->assertSee('Setup WhatsApp (WatZap.id)');
        $response->assertSee('Kredensial API WatZap');
    }

    public function test_superadmin_can_update_watzap_settings(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->post(route('settings.watzap.update'), [
            'watzap_api_key' => 'api_key_secret_123',
            'watzap_number_key' => 'num_key_device_456',
            'watzap_web_url' => 'https://watzap.chat',
        ]);

        $response->assertRedirect(route('settings.watzap.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('api_key_secret_123', Setting::get('watzap_api_key'));
        $this->assertEquals('num_key_device_456', Setting::get('watzap_number_key'));
    }

    public function test_admin_cannot_update_watzap_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Admin can view status
        $response = $this->actingAs($admin)->get(route('settings.watzap.index'));
        $response->assertOk();
        $response->assertSee('Mode Lihat');

        // Admin cannot update
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
        $respSuper->assertSee('Setup WhatsApp', false);

        $respAdmin = $this->actingAs($admin)->get(route('whatsapp.index'));
        $respAdmin->assertOk();
        $respAdmin->assertSee('Setup WhatsApp', false);
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
        $report = $service->send($prediction, 'Halo Budi, ada promo baru!', $user->id);

        $this->assertEquals('sent', $report->status);
        $this->assertDatabaseHas('broadcast_reports', [
            'customer_vendor_code' => 'CUST001',
            'phone_number' => '081234567890',
            'status' => 'sent',
        ]);
    }

    public function test_superadmin_can_test_send_whatsapp_message(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        Setting::set('watzap_api_key', 'test_key');
        Setting::set('watzap_number_key', 'test_number');

        Http::fake([
            'https://api.watzap.id/v1/send_message' => Http::response([
                'status' => '200',
                'ack' => 'successfully',
                'message' => 'Message sent successfully',
            ], 200),
        ]);

        $resp = $this->actingAs($superadmin)->post(route('settings.watzap.test_send'), [
            'phone' => '081234567890',
            'message' => 'Testing WatZap setup',
        ]);

        $resp->assertRedirect(route('settings.watzap.index'));
        $resp->assertSessionHas('success');
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
}
