<?php

namespace Tests\Feature;

use App\Models\ActivityNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_notification_can_be_logged_and_retrieved(): void
    {
        $user = User::factory()->create([
            'name' => 'Yahya',
            'role' => 'admin',
        ]);

        ActivityNotification::log(
            title: 'Upload Turnover SAP',
            message: 'Yahya berhasil mengunggah 580 data Turnover SAP (turnover.xlsx)',
            user: $user,
            type: 'success',
            action: 'upload_turnover',
            icon: 'upload'
        );

        $this->assertDatabaseHas('activity_notifications', [
            'user_id' => $user->id,
            'user_name' => 'Yahya',
            'title' => 'Upload Turnover SAP',
            'action' => 'upload_turnover',
        ]);

        $response = $this->actingAs($user)->getJson(route('api.notifications.index'));
        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('notifications.0.user_name', 'Yahya');
        $response->assertJsonPath('notifications.0.title', 'Upload Turnover SAP');
        $response->assertJsonPath('notifications.0.message', 'Yahya berhasil mengunggah 580 data Turnover SAP (turnover.xlsx)');
    }

    public function test_cross_selling_upload_logs_activity_notification_with_uploader_name(): void
    {
        Storage::fake('local');

        $user = User::factory()->superadmin()->create([
            'name' => 'Yahya Super',
        ]);

        $csv = implode("\n", [
            'Cabang,Posting Date,Document Number,Customer/Vendor Code,Customer/Vendor Name,ItemCode,Dscription,Brand,Quantity,Harga Jual,Nama Sales,No HP Konsumen',
            'Banjarmasin,2026-10-18,DOC-NOTIF-01,CUST-NOTIF-01,Pelanggan A,ITM-1,iPhone 15,IPHONE,1,15000000,Sales 1,081234567890',
        ]);
        $file = UploadedFile::fake()->createWithContent('cross_yahya.csv', $csv);

        $response = $this->actingAs($user)->post(route('imports.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('imports.index'));

        $this->assertDatabaseHas('activity_notifications', [
            'user_id' => $user->id,
            'user_name' => 'Yahya Super',
            'action' => 'upload_cross_selling',
            'title' => 'Upload Cross-Selling SAP',
        ]);
    }

    public function test_turnover_upload_logs_activity_notification_with_uploader_name(): void
    {
        Storage::fake('local');

        $user = User::factory()->superadmin()->create([
            'name' => 'Yahya Turn',
        ]);

        $csv = implode("\n", [
            'Cabang,Posting Date,Document Number,Sales Employee Name,Remarks,SalesCabang,DPP,GrossProfit,Total',
            'Banjarmasin,2026-10-18,DOC-NOTIF-02,Sales 1,Order Online,Banjarmasin,8000000,400000,8880000',
        ]);
        $file = UploadedFile::fake()->createWithContent('turnover_yahya.csv', $csv);

        $response = $this->actingAs($user)->post(route('imports.store'), [
            'turnover_file' => $file,
        ]);

        $response->assertRedirect(route('imports.index'));

        $this->assertDatabaseHas('activity_notifications', [
            'user_id' => $user->id,
            'user_name' => 'Yahya Turn',
            'action' => 'upload_turnover',
            'title' => 'Upload Turnover SAP',
        ]);
    }

    public function test_clear_notifications_endpoint_clears_records(): void
    {
        $user = User::factory()->superadmin()->create();

        ActivityNotification::log('Test', 'Pesan', $user);
        $this->assertDatabaseCount('activity_notifications', 1);

        $response = $this->actingAs($user)->deleteJson(route('api.notifications.clear'));
        $response->assertOk();
        $this->assertDatabaseCount('activity_notifications', 0);
    }
}
