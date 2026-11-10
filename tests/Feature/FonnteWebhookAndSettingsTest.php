<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SosmedKeep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FonnteWebhookAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_view_fonnte_settings_tab(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->get(route('settings.watzap.index', ['tab' => 'fonnte']));
        $response->assertOk();
        $response->assertSee('Kredensial API Fonnte');
        $response->assertSee('URL Webhook Fonnte');
        $response->assertSee('fonnte-webhook');
    }

    public function test_superadmin_can_update_fonnte_settings(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->post(route('settings.watzap.update'), [
            'target_tab' => 'fonnte',
            'fonnte_token' => 'test_token_fonnte_12345',
            'fonnte_autoreply_enabled' => '1',
            'fonnte_autoreply_message' => 'Custom Reply {nama} {barang}',
        ]);

        $response->assertRedirect(route('settings.watzap.index', ['tab' => 'fonnte']));
        $response->assertSessionHas('success');

        $this->assertEquals('test_token_fonnte_12345', Setting::get('fonnte_token'));
        $this->assertEquals('1', Setting::get('fonnte_autoreply_enabled'));
        $this->assertEquals('Custom Reply {nama} {barang}', Setting::get('fonnte_autoreply_message'));
    }

    public function test_fonnte_webhook_saves_keep_message_to_sql_database(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $keepMessage = "FORMAT KEEP\nNama: Ahmad Maulana\nNo WA: 081234567890\nBarang: iPhone 15 128GB Black\nTanggal Ambil: 15/11/2026\nCatatan: DP 500rb\nRemarks: SOSMED 1 BJM";

        $payload = [
            'message' => $keepMessage,
            'sender' => '628999999999',
            'isgroup' => false,
            'id' => 'msg_fonnte_001',
        ];

        $response = $this->post(route('api.fonnte_webhook'), $payload);

        $response->assertOk();
        $this->assertEquals('OK', $response->getContent());

        $this->assertDatabaseHas('sosmed_keeps', [
            'nama' => 'Ahmad Maulana',
            'nomor_wa' => '6281234567890',
            'barang_keep' => 'iPhone 15 128GB Black',
            'catatan' => 'DP 500rb',
            'remarks' => 'SOSMED 1 BJM',
            'cabang' => 'Banjarmasin',
            'message_id' => 'msg_fonnte_001',
        ]);
    }

    public function test_fonnte_webhook_ignores_message_without_remarks(): void
    {
        $payload = [
            'message' => "Halo admin, ada stok iPhone 15?",
            'sender' => '628999999999',
        ];

        $response = $this->post(route('api.fonnte_webhook'), $payload);
        $response->assertOk();
        $this->assertEquals('IGNORED_NO_REMAKS', $response->getContent());

        $this->assertDatabaseCount('sosmed_keeps', 0);
    }

    public function test_fonnte_webhook_ignores_daily_report(): void
    {
        $payload = [
            'message' => "Total chat sosmed hari ini: 50\nRemarks: BJM",
            'sender' => '628999999999',
        ];

        $response = $this->post(route('api.fonnte_webhook'), $payload);
        $response->assertOk();
        $this->assertEquals('IGNORED_DAILY_REPORT', $response->getContent());

        $this->assertDatabaseCount('sosmed_keeps', 0);
    }

    public function test_fonnte_webhook_action_update_status(): void
    {
        SosmedKeep::create([
            'nama' => 'Budi Santoso',
            'nomor_wa' => '6285712345678',
            'barang_keep' => 'Samsung S24',
            'remarks' => 'SOSMED 2',
            'status' => 'PENDING',
        ]);

        $payload = [
            'action' => 'UPDATE_STATUS',
            'nomorWA' => '085712345678',
            'status' => 'WIN',
        ];

        $response = $this->post(route('api.fonnte_webhook'), $payload);
        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('sosmed_keeps', [
            'nomor_wa' => '6285712345678',
            'status' => 'WIN',
        ]);
    }

    public function test_fonnte_webhook_action_update_reaction_fu(): void
    {
        SosmedKeep::create([
            'nama' => 'Citra Lestari',
            'nomor_wa' => '6287899887766',
            'barang_keep' => 'iPad Air 5',
            'remarks' => 'SOSMED 3',
        ]);

        $payload = [
            'action' => 'UPDATE_REACTION_FU',
            'customerPhone' => '087899887766',
            'nomorFU' => 'FU-101',
        ];

        $response = $this->post(route('api.fonnte_webhook'), $payload);
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'pic' => 'FU-101',
        ]);

        $this->assertDatabaseHas('sosmed_keeps', [
            'nomor_wa' => '6287899887766',
            'nomor_fu' => 'FU-101',
        ]);
    }

    public function test_fonnte_webhook_action_clear_reaction_fu(): void
    {
        SosmedKeep::create([
            'nama' => 'Citra Lestari',
            'nomor_wa' => '6287899887766',
            'barang_keep' => 'iPad Air 5',
            'remarks' => 'SOSMED 3',
            'nomor_fu' => 'FU-101',
        ]);

        $payload = [
            'action' => 'CLEAR_REACTION_FU',
            'customerPhone' => '087899887766',
        ];

        $response = $this->post(route('api.fonnte_webhook'), $payload);
        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('sosmed_keeps', [
            'nomor_wa' => '6287899887766',
            'nomor_fu' => null,
        ]);
    }
}
