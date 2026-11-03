<?php

namespace Tests\Feature;

use App\Models\BroadcastMedia;
use App\Models\BroadcastReport;
use App\Models\BroadcastTemplate;
use App\Models\RepeatOrderPrediction;
use App\Models\User;
use App\Services\BroadcastService;
use App\Services\WatzapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class BroadcastTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_templates_page(): void
    {
        $user = User::factory()->create();

        BroadcastTemplate::create([
            'title' => 'Promo Diskon Gadget',
            'message' => 'Halo Kak {nama}, dapatkan diskon spesial!',
        ]);

        $response = $this->actingAs($user)->get(route('broadcasts.templates.index'));

        $response->assertOk();
        $response->assertSee('Kelola Template Broadcast');
        $response->assertSee('Promo Diskon Gadget');
    }

    public function test_user_can_create_template_with_image_upload(): void
    {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('promo.png', 400, 400);

        $response = $this->actingAs($user)->post(route('broadcasts.templates.store'), [
            'title' => 'Template Foto Baru',
            'message' => 'Pesan dengan gambar flyer promo.',
            'image' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('broadcast_templates', [
            'title' => 'Template Foto Baru',
        ]);

        $template = BroadcastTemplate::where('title', 'Template Foto Baru')->first();
        $this->assertNotNull($template->image_url);
        $this->assertDatabaseHas('broadcast_media', [
            'filename' => 'promo.png',
        ]);
    }

    public function test_bulk_broadcast_with_image_upload_and_placeholders(): void
    {
        $user = User::factory()->create();

        $prediction = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'CUST_IMG',
            'customer_vendor_name' => 'Siti Rahma',
            'no_hp_customer' => '628987654321',
            'item_code' => 'GAD.SAM.A55',
            'dscription' => 'Samsung A55',
            'classification' => 'tinggi',
        ]);

        $watzapMock = Mockery::mock(WatzapService::class);
        $watzapMock->shouldReceive('isConfigured')->andReturn(true);
        $watzapMock->shouldReceive('sendImage')
            ->once()
            ->withArgs(function ($phone, $imageUrl, $caption) {
                return $phone === '628987654321'
                    && !empty($imageUrl)
                    && str_contains($caption, 'Halo Siti Rahma');
            })
            ->andReturn([
                'success' => true,
                'message' => 'Gambar berhasil dikirim',
                'status' => '200',
                'data' => [],
                'raw' => '',
            ]);

        $this->app->instance(WatzapService::class, $watzapMock);

        $file = UploadedFile::fake()->image('flyer.jpg', 600, 600);

        $response = $this->actingAs($user)->post(route('broadcasts.bulk'), [
            'prediction_ids' => [$prediction->id],
            'message' => 'Halo {nama}! Cek promo {item} terbaru.',
            'image' => $file,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('broadcast_reports', [
            'customer_vendor_code' => 'CUST_IMG',
            'phone_number' => '628987654321',
            'status' => 'sent',
        ]);

        $report = BroadcastReport::where('customer_vendor_code', 'CUST_IMG')->first();
        $this->assertNotNull($report->image_url);
        $this->assertStringContainsString('Halo Siti Rahma!', $report->message);
    }
}

