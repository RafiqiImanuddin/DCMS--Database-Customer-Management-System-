<?php

namespace Tests\Feature;

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

    public function test_watzap_service_send_image_posts_correct_payload(): void
    {
        Setting::set('watzap_api_key', 'my_api_key');
        Setting::set('watzap_number_key', 'my_num_key');

        Http::fake([
            'https://api.watzap.id/v1/send_image_url' => function ($request) {
                $data = $request->data();
                $isValid = ($data['api_key'] ?? '') === 'my_api_key'
                    && ($data['number_key'] ?? '') === 'my_num_key'
                    && ($data['phone_no'] ?? '') === '628123456789'
                    && ($data['url'] ?? '') === 'https://dcms-gadgetmart-app.vercel.app/media/broadcasts/1/promo.jpg'
                    && ($data['message'] ?? '') === 'Cek promo gadget terbaru!';

                if (! $isValid) {
                    return Http::response(['status' => '1001', 'message' => 'Wrong Parameters'], 200);
                }

                return Http::response([
                    'status' => '200',
                    'ack' => 'successfully',
                    'message' => 'Image sent successfully',
                ], 200);
            },
        ]);

        $service = app(WatzapService::class);
        $result = $service->sendImage(
            '08123456789',
            'https://dcms-gadgetmart-app.vercel.app/media/broadcasts/1/promo.jpg',
            'Cek promo gadget terbaru!'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('200', $result['status']);
    }

    public function test_watzap_service_validate_number(): void
    {
        Setting::set('watzap_api_key', 'my_api_key');
        Setting::set('watzap_number_key', 'my_num_key');

        Http::fake([
            'https://api.watzap.id/v1/validate_number' => function ($request) {
                $data = $request->data();
                $isValid = ($data['api_key'] ?? '') === 'my_api_key'
                    && ($data['number_key'] ?? '') === 'my_num_key'
                    && ($data['phone_no'] ?? '') === '628123456789';

                if ($isValid) {
                    return Http::response([
                        'status' => '200',
                        'ack' => 'successfully',
                        'message' => 'Number is registered on WhatsApp',
                    ], 200);
                }

                return Http::response(['status' => '1004', 'message' => 'Invalid Phone'], 200);
            },
        ]);

        $service = app(WatzapService::class);
        $result = $service->validateNumber('08123456789');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_valid']);
        $this->assertEquals('200', $result['status']);
    }

    public function test_superadmin_can_update_inbox_follow_up_settings(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->post(route('settings.watzap.update'), [
            'target_tab' => 'inbox',
            'watzap_inbox_same_as_broadcast' => '0',
            'watzap_inbox_api_key' => 'inbox_api_secret_789',
            'watzap_inbox_number_key' => 'inbox_num_device_999',
        ]);

        $response->assertRedirect(route('settings.watzap.index', ['tab' => 'inbox']));
        $response->assertSessionHas('success');

        $this->assertEquals('0', Setting::get('watzap_inbox_same_as_broadcast'));
        $this->assertEquals('inbox_api_secret_789', Setting::get('watzap_inbox_api_key'));
        $this->assertEquals('inbox_num_device_999', Setting::get('watzap_inbox_number_key'));

        $service = app(WatzapService::class);
        $this->assertEquals('inbox_api_secret_789', $service->getApiKey('inbox'));
        $this->assertEquals('inbox_num_device_999', $service->getNumberKey('inbox'));
    }

    public function test_sidebar_displays_setting_category_with_setup_whatsapp(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Customer & Outreach', false);
        $response->assertSee('Workspace Sosmed');
        $response->assertSee('Pengaturan');
        $response->assertSee('Pengaturan WhatsApp');
    }

    public function test_superadmin_can_register_webhook_via_api(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        Setting::set('watzap_api_key', 'my_api_key');
        Setting::set('watzap_number_key', 'my_number_key');

        Http::fake([
            'https://api.watzap.id/v1/set_webhook' => function ($request) {
                $data = $request->data();
                $this->assertEquals('my_api_key', $data['api_key']);
                $this->assertEquals('my_number_key', $data['number_key']);
                $this->assertNotEmpty($data['endpoint_url']);

                return Http::response([
                    'status' => '200',
                    'ack' => 'successfully',
                    'message' => 'Webhook URL updated successfully',
                ], 200);
            },
        ]);

        $response = $this->actingAs($superadmin)->postJson(route('settings.watzap.register_webhook'), [
            'channel' => 'inbox',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_watzap_service_resolves_number_key_with_font_ambiguity_and_phone(): void
    {
        $service = app(WatzapService::class);

        Http::fake([
            'https://api.watzap.id/v1/checking_key' => Http::response([
                'status' => true,
                'data' => [
                    'licenses_key' => [
                        [
                            'key' => 'gqLbEDAZ4lReCCME',
                            'wa_number' => '6285111426699',
                            'is_connected' => true,
                            'status' => 'Online',
                        ],
                        [
                            'key' => 'pL46HTdCO6vUgNfC',
                            'wa_number' => '6289677027059',
                            'is_connected' => true,
                            'status' => 'Online',
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Character 10 is 'I' (capital i) instead of 'l' (lowercase L)
        $resolvedTypo = $service->resolveNumberKey('gqLbEDAZ4IReCCME', 'test_api_key');
        $this->assertEquals('gqLbEDAZ4lReCCME', $resolvedTypo);

        // Phone number input
        $resolvedPhone = $service->resolveNumberKey('085111426699', 'test_api_key');
        $this->assertEquals('gqLbEDAZ4lReCCME', $resolvedPhone);

        // Exact match
        $resolvedExact = $service->resolveNumberKey('pL46HTdCO6vUgNfC', 'test_api_key');
        $this->assertEquals('pL46HTdCO6vUgNfC', $resolvedExact);
    }

    public function test_superadmin_can_update_waha_vps_settings(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->post(route('settings.watzap.update'), [
            'target_tab' => 'inbox',
            'waha_enabled' => '1',
            'waha_vps_url' => 'http://178.83.188.100:3088',
            'waha_api_key' => 'gadgetmart123',
            'waha_session' => 'default',
        ]);

        $response->assertRedirect(route('settings.watzap.index', ['tab' => 'inbox']));
        $this->assertEquals('http://178.83.188.100:3088', Setting::get('waha_vps_url'));
        $this->assertEquals('gadgetmart123', Setting::get('waha_api_key'));
        $this->assertEquals('default', Setting::get('waha_session'));
        $this->assertEquals('1', Setting::get('waha_enabled'));
    }

    public function test_superadmin_can_check_waha_status_via_ajax(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        Http::fake([
            'http://178.83.188.100:3088/api/sessions' => Http::response([
                [
                    'name' => 'default',
                    'status' => 'WORKING',
                    'me' => [
                        'id' => '6285111426699@c.us',
                        'pushName' => 'admin divisi sosmed',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($superadmin)->postJson(route('settings.watzap.check_waha'), [
            'session' => 'default',
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        $this->assertEquals('WORKING', $response->json('status'));
        $this->assertEquals('6285111426699@c.us', $response->json('me.id'));
    }

    public function test_superadmin_can_update_baileys_gateway_settings(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->post(route('settings.watzap.update'), [
            'target_tab' => 'inbox',
            'baileys_bot_enabled' => '1',
            'baileys_bot_url' => 'https://dcms-baileys-bot.onrender.com',
            'baileys_bot_api_key' => 'secret_baileys_key',
        ]);

        $response->assertRedirect(route('settings.watzap.index', ['tab' => 'inbox']));
        $this->assertEquals('https://dcms-baileys-bot.onrender.com', Setting::get('baileys_bot_url'));
        $this->assertEquals('secret_baileys_key', Setting::get('baileys_bot_api_key'));
        $this->assertEquals('1', Setting::get('baileys_bot_enabled'));
    }

    public function test_superadmin_can_check_baileys_status_via_ajax(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        Http::fake([
            'http://127.0.0.1:3000/api/status' => Http::response([
                'success' => true,
                'connected' => true,
                'state' => 'CONNECTED',
                'phone' => '6289988776655',
                'name' => 'Bot DCMS Khusus',
            ], 200),
        ]);

        $response = $this->actingAs($superadmin)->postJson(route('settings.watzap.check_baileys'));

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        $this->assertEquals('CONNECTED', $response->json('status'));
        $this->assertEquals('6289988776655', $response->json('phone'));
    }

    public function test_send_omnichannel_prioritizes_baileys_gateway(): void
    {
        Setting::set('baileys_bot_enabled', '1');
        Setting::set('baileys_bot_url', 'http://127.0.0.1:3000');

        Http::fake([
            'http://127.0.0.1:3000/api/send-message' => Http::response([
                'success' => true,
                'message_id' => 'baileys_msg_9999',
                'status' => '200',
                'phone' => '628123456789',
            ], 200),
        ]);

        $service = app(WatzapService::class);
        $result = $service->sendOmnichannel('08123456789', 'Halo tim follow up!');

        $this->assertTrue($result['success']);
        $this->assertEquals('BAILEYS_GATEWAY', $result['source']);
        $this->assertEquals('baileys_msg_9999', $result['message_id']);
    }

    public function test_inbound_baileys_gateway_message_via_watzap_webhook(): void
    {
        $payload = [
            'event' => 'message',
            'payload' => [
                'id' => 'baileys_in_123',
                'from' => '6281987654321@s.whatsapp.net',
                'fromMe' => false,
                'body' => 'Siap admin, order keep ini saya follow up ke customer.',
                'pushname' => 'Staf Mega Gadgetmart',
                'source' => 'BAILEYS_GATEWAY',
            ],
        ];

        $response = $this->postJson(route('api.sosmed.watzap_webhook'), $payload);

        $response->assertOk();
        $this->assertEquals('200', $response->json('status'));
        $this->assertEquals('baileys_in_123', $response->json('entry.messageId'));
        $this->assertEquals('BAILEYS_GATEWAY', $response->json('entry.gateway'));
        $this->assertEquals('6281987654321', $response->json('entry.phone'));
    }
}
