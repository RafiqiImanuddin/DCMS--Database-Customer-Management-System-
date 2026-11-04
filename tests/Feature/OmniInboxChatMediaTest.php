<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WatzapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class OmniInboxChatMediaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);

        // Mock WatzapService so real HTTP calls to external WatZap are not made during testing
        $mockWatzap = $this->createMock(WatzapService::class);
        $mockWatzap->method('sendMessage')->willReturn([
            'success' => true,
            'message' => 'Message sent',
            'status' => '200',
            'data' => [],
            'raw' => '',
        ]);
        $mockWatzap->method('sendImage')->willReturn([
            'success' => true,
            'message' => 'Image sent',
            'status' => '200',
            'data' => [],
            'raw' => '',
        ]);
        $mockWatzap->method('sendFile')->willReturn([
            'success' => true,
            'message' => 'File sent',
            'status' => '200',
            'data' => [],
            'raw' => '',
        ]);

        $this->app->instance(WatzapService::class, $mockWatzap);
    }

    public function test_can_send_text_chat(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/watzap-send', [
            'phone' => '081234567890',
            'message' => 'Halo ini pesan teks follow up.',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'entry' => [
                'phone' => '081234567890',
                'message' => 'Halo ini pesan teks follow up.',
                'direction' => 'outbound',
            ],
        ]);
    }

    public function test_can_send_image_chat(): void
    {
        $image = UploadedFile::fake()->image('promo_gadget.jpg', 600, 600);

        $response = $this->actingAs($this->user)->post('/api/watzap-send', [
            'phone' => '081234567890',
            'message' => 'Silakan cek brosur promo berikut kak!',
            'file' => $image,
        ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals('image', $data['entry']['media_type']);
        $this->assertStringContainsString('/media/broadcasts/', $data['entry']['media_url']);
    }

    public function test_can_send_document_file_chat(): void
    {
        $doc = UploadedFile::fake()->create('surat_penawaran.pdf', 120, 'application/pdf');

        $response = $this->actingAs($this->user)->post('/api/watzap-send', [
            'phone' => '081234567890',
            'message' => 'Berikut file penawaran resmi dari kami.',
            'file' => $doc,
        ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals('document', $data['entry']['media_type']);
        $this->assertEquals('surat_penawaran.pdf', $data['entry']['filename']);
        $this->assertStringContainsString('/media/broadcasts/', $data['entry']['media_url']);
    }

    public function test_can_receive_webhook_and_poll_messages(): void
    {
        // 1. Post simulated inbound webhook
        $postRes = $this->postJson('/api/watzap-webhook', [
            'phone_no' => '081234567890',
            'message' => 'Terima kasih infonya, saya mau ambil barangnya.',
            'sender_name' => 'Budi Santoso',
        ]);

        $postRes->assertOk();
        $postRes->assertJson([
            'status' => '200',
            'message' => 'Webhook received',
        ]);

        // 2. Poll messages via GET
        $getRes = $this->getJson('/api/watzap-webhook');
        $getRes->assertOk();
        $data = $getRes->json();
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['messages']);

        $latest = end($data['messages']);
        $this->assertEquals('inbound', $latest['direction']);
        $this->assertEquals('081234567890', $latest['phone']);
    }

    public function test_can_simulate_receive_with_document_file(): void
    {
        $doc = UploadedFile::fake()->create('bukti_transfer.pdf', 50, 'application/pdf');

        $response = $this->actingAs($this->user)->post('/api/watzap-simulate-receive', [
            'phone' => '081234567890',
            'message' => 'Ini bukti transfer dp saya kak.',
            'sender_name' => 'Customer Budi',
            'file' => $doc,
        ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals('document', $data['entry']['media_type']);
        $this->assertEquals('bukti_transfer.pdf', $data['entry']['filename']);
        $this->assertEquals('inbound', $data['entry']['direction']);
    }
}

