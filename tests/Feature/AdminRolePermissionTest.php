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

    public function test_both_superadmin_and_admin_can_access_import_page_and_upload(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();

        // Admin can access import page
        $this->actingAs($admin)
            ->get(route('imports.index'))
            ->assertOk();

        // Admin can download template
        $this->actingAs($admin)
            ->get(route('imports.template'))
            ->assertOk();

        // Admin can upload import file
        $header = "Cabang,Posting Date,Document Number,Customer/Vendor Code,Customer/Vendor Name,ItemCode,Dscription,Brand,Quantity,Harga Jual,Nama Sales,No HP Konsumen\n";
        $row = "Banjarmasin,2026-10-18,INV-001,CUST-001,Budi Santoso,ACC-001,Kabel Data Type-C Fast Charging,XIAOMI,1,50000,Sales Ahmad,081234567890\n";
        $file = UploadedFile::fake()->createWithContent('sample.csv', $header . $row);

        $this->actingAs($admin)
            ->post(route('imports.store'), ['file' => $file])
            ->assertRedirect(route('imports.index'));

        $this->assertDatabaseCount('cross_selling_imports', 1);
    }

    public function test_guest_cannot_access_import_page_or_upload(): void
    {
        $this->get(route('imports.index'))->assertRedirect(route('login'));
        $file = UploadedFile::fake()->create('sample.csv', 10, 'text/csv');
        $this->post(route('imports.store'), ['file' => $file])->assertRedirect(route('login'));
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
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('template-cross-selling.xlsx', $response->headers->get('Content-Disposition'));
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

        // Download Master Customer as Excel (.xlsx)
        $predDownload = $this->actingAs($admin)->get(route('predictions.download'));
        $predDownload->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $predDownload->headers->get('Content-Type'));
        $this->assertStringContainsString('.xlsx', $predDownload->headers->get('Content-Disposition'));

        // Download Leads as Excel (.xlsx)
        $leadDownload = $this->actingAs($admin)->get(route('leads.download'));
        $leadDownload->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $leadDownload->headers->get('Content-Type'));
        $this->assertStringContainsString('.xlsx', $leadDownload->headers->get('Content-Disposition'));
    }
}
