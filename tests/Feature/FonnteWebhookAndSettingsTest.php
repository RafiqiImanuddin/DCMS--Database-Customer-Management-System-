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

    public function test_fonnte_webhook_action_delete_keep(): void
    {
        $keep = SosmedKeep::create([
            'nama' => 'Rian Hidayat',
            'nomor_wa' => '6281122334455',
            'barang_keep' => 'MacBook Air M2',
            'remarks' => 'SOSMED 4',
        ]);

        $payload = [
            'action' => 'DELETE_KEEP',
            'customerPhone' => '081122334455',
        ];

        $response = $this->post(route('api.fonnte_webhook'), $payload);
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'deletedId' => $keep->id,
        ]);

        $this->assertDatabaseMissing('sosmed_keeps', [
            'id' => $keep->id,
        ]);
    }

    public function test_workspace_sosmed_update_keep_status_and_report_ar_by_id(): void
    {
        $keep = SosmedKeep::create([
            'nama' => 'Pelanggan ID Test',
            'nomor_wa' => '',
            'barang_keep' => 'iPhone 16 Pro Max',
            'remarks' => 'SOSMED 5',
            'status' => 'PENDING',
        ]);

        // Test update status by ID
        $response = $this->postJson(route('api.sosmed.update_keep_status'), [
            'id' => $keep->id,
            'status' => 'WIN',
            'feedback' => 'Deal via WhatsApp',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => 'WIN',
            'mysql_updated' => true,
        ]);

        $this->assertDatabaseHas('sosmed_keeps', [
            'id' => $keep->id,
            'status' => 'WIN',
            'feedback_tim_fu' => 'Deal via WhatsApp',
        ]);

        // Test update Report AR by ID
        $arResponse = $this->postJson(route('api.sosmed.update_report_ar'), [
            'id' => $keep->id,
            'arDoc' => 'AR-2026-9999',
        ]);

        $arResponse->assertOk();
        $arResponse->assertJson([
            'success' => true,
            'arDoc' => 'AR-2026-9999',
            'mysql_updated' => true,
        ]);

        $this->assertDatabaseHas('sosmed_keeps', [
            'id' => $keep->id,
            'report_ar' => 'AR-2026-9999',
        ]);
    }

    public function test_get_keeps_api_returns_paginated_keeps(): void
    {
        SosmedKeep::create([
            'nama' => 'User Keep Test',
            'nomor_wa' => '628123456789',
            'barang_keep' => 'Samsung S24 Ultra',
            'remarks' => 'SOSMED 1',
            'status' => 'WIN',
            'periode' => 'November-26',
        ]);

        $response = $this->getJson(route('api.sosmed.get_keeps'));
        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertGreaterThanOrEqual(1, $response->json('total'));
        $this->assertNotEmpty($response->json('data'));

        // Test with /api/sosmed-keeps path directly
        $apiResponse = $this->getJson('/api/sosmed-keeps');
        $apiResponse->assertOk();
        $apiResponse->assertJson([
            'success' => true,
        ]);
    }

    public function test_fonnte_webhook_accepts_remark_singular_and_dot_in_no_wa(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $message = "*FORMAT KEEP*\n*Nama :* Siti Rahmah\n*No. WA :* 085248123456\n*Barang :* SAMSUNG A55 5G\n*Tanggal Ambil :* Besok sore\n*Catatan :* DP 100rb\n*Remark :* SOSMED 3";

        $response = $this->post(route('api.fonnte_webhook'), [
            'message' => $message,
            'sender' => '628111222333',
            'id' => 'msg_test_remark_singular',
        ]);

        $response->assertOk();
        $this->assertEquals('OK', $response->getContent());

        $this->assertDatabaseHas('sosmed_keeps', [
            'nama' => 'Siti Rahmah',
            'nomor_wa' => '6285248123456',
            'barang_keep' => 'SAMSUNG A55 5G',
            'remarks' => 'SOSMED 3',
        ]);
    }

    public function test_fonnte_webhook_accepts_kode_sosmed_and_equal_separator(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $message = "Format Keep\nNama Cust = Dewi Sartika\nNo. Telp = 087812345678\nUnit = Redmi Note 13 Pro\nJadwal = Nanti malam\nNote = Ambil di store\nKode Sosmed = SOSMED 17";

        $response = $this->post(route('api.fonnte_webhook'), [
            'message' => $message,
            'sender' => '628777888999',
            'id' => 'msg_test_equal_sep',
        ]);

        $response->assertOk();
        $this->assertEquals('OK', $response->getContent());

        $this->assertDatabaseHas('sosmed_keeps', [
            'nama' => 'Dewi Sartika',
            'nomor_wa' => '6287812345678',
            'barang_keep' => 'Redmi Note 13 Pro',
            'remarks' => 'SOSMED 17',
        ]);
    }

    public function test_fonnte_webhook_detects_cabang_from_group_name(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $message = "Format Keep\nNama: Budi\nNo WA: 081234567890\nBarang: Vivo V30\nRemarks: SOSMED 10";

        $response = $this->post(route('api.fonnte_webhook'), [
            'message' => $message,
            'sender' => '120363327671212528@g.us',
            'group_name' => 'GM BJB (BANJARBARU)',
            'isgroup' => true,
            'id' => 'msg_test_group_cabang',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('sosmed_keeps', [
            'nama' => 'Budi',
            'cabang' => 'BANJARBARU',
        ]);
    }
}
