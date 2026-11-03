<?php

namespace Tests\Feature;

use App\Models\CrossSellingImport;
use App\Models\LeadCustomer;
use App\Models\RepeatOrderPrediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_access_import_page_or_upload(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();

        // Admin cannot access import page
        $this->actingAs($admin)
            ->get(route('imports.index'))
            ->assertStatus(403);

        // Admin cannot upload import file
        $file = UploadedFile::fake()->create('sample.csv', 10, 'text/csv');
        $this->actingAs($admin)
            ->post(route('imports.store'), ['file' => $file])
            ->assertStatus(403);

        $this->assertDatabaseCount('cross_selling_imports', 0);
    }

    public function test_superadmin_can_access_import_and_download_template(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->get(route('imports.index'))
            ->assertOk();

        $response = $this->actingAs($superadmin)
            ->get(route('imports.template'));

        $response->assertOk();
        $this->assertStringContainsString('Cabang', $response->streamedContent());
        $this->assertStringContainsString('Posting Date', $response->streamedContent());
    }

    public function test_admin_can_download_master_customer_and_leads_data(): void
    {
        $admin = User::factory()->admin()->create();
        $import = CrossSellingImport::create([
            'user_id' => $admin->id,
            'original_name' => 'test.xlsx',
            'status' => 'completed',
        ]);

        $prediction = RepeatOrderPrediction::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'CUST001',
            'customer_vendor_name' => 'Budi Santoso',
            'no_hp_customer' => '628123456789',
            'classification' => 'tinggi',
            'confidence' => 95,
            'item_code' => 'ACC01',
            'dscription' => 'Kabel Data',
        ]);

        LeadCustomer::create([
            'user_id' => $admin->id,
            'repeat_order_prediction_id' => $prediction->id,
            'customer_vendor_code' => 'CUST001',
            'customer_vendor_name' => 'Budi Santoso',
            'no_hp_customer' => '628123456789',
            'classification' => 'tinggi',
            'item_code' => 'ACC01',
            'dscription' => 'Kabel Data',
            'status' => 'follow_up',
            'assigned_at' => now(),
        ]);

        // Download Master Customer
        $predDownload = $this->actingAs($admin)->get(route('predictions.download'));
        $predDownload->assertOk();
        $this->assertStringContainsString('Kode Pelanggan', $predDownload->streamedContent());
        $this->assertStringContainsString('Nama Pelanggan', $predDownload->streamedContent());

        // Download Leads
        $leadDownload = $this->actingAs($admin)->get(route('leads.download'));
        $leadDownload->assertOk();
        $this->assertStringContainsString('Kode Pelanggan', $leadDownload->streamedContent());
        $this->assertStringContainsString('Budi Santoso', $leadDownload->streamedContent());
    }
}
