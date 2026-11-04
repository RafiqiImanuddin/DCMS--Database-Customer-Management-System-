<?php

namespace Tests\Feature;

use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\RepeatOrderPrediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UnifiedCrossSellingSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_save_excel_croselling_synchronizes_to_dcms_dashboard_and_repeat_order_predictions(): void
    {
        $user = User::factory()->admin()->create();

        $header = "Cabang,Posting Date,Document Number,Customer/Vendor Code,Customer/Vendor Name,ItemCode,Dscription,Brand,Quantity,Harga Jual,Nama Sales,No HP Konsumen\n";
        $row1 = "Banjarmasin,2026-10-18,INV-100,CUST-001,Budi Santoso,ACC-001,Kabel Data Type-C,XIAOMI,1,50000,Sales Ahmad,081234567890\n";
        $row2 = "Banjarmasin,2026-10-25,INV-101,CUST-001,Budi Santoso,ACC-001,Kabel Data Type-C,XIAOMI,1,50000,Sales Ahmad,081234567890\n";
        $content = $header . $row1 . $row2;

        $response = $this->actingAs($user)
            ->call('POST', '/api/save-excel?type=croselling', [], [], [], [
                'CONTENT_TYPE' => 'application/octet-stream',
            ], $content);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'unified_sync' => true,
        ]);

        // 1. Verifikasi record di cross_selling_imports
        $this->assertDatabaseCount('cross_selling_imports', 1);
        $import = CrossSellingImport::first();
        $this->assertSame('completed', $import->status);
        $this->assertSame(2, $import->success_count);

        // 2. Verifikasi transaksi masuk ke tabel cross_sellings
        $this->assertDatabaseCount('cross_sellings', 2);
        $this->assertDatabaseHas('cross_sellings', [
            'document_number' => 'INV-100',
            'customer_vendor_name' => 'Budi Santoso',
            'no_hp_customer' => '6281234567890',
        ]);

        // 3. Verifikasi RepeatOrderPredictor otomatis membentuk data prediksi
        $this->assertDatabaseCount('repeat_order_predictions', 1);
        $prediction = RepeatOrderPrediction::first();
        $this->assertSame('6281234567890', $prediction->no_hp_customer);
        $this->assertSame(2, $prediction->transaction_count);
        $this->assertNotNull($prediction->classification);

        // 4. Verifikasi file tersimpan untuk Workspace Sosmed
        $this->assertTrue(File::exists(public_path('workspace-sosmed/CROSSELLING-12102026.xlsx')));
    }

    public function test_import_from_dcms_synchronizes_to_workspace_sosmed_file(): void
    {
        $admin = User::factory()->admin()->create();

        $header = "Cabang,Posting Date,Document Number,Customer/Vendor Code,Customer/Vendor Name,ItemCode,Dscription,Brand,Quantity,Harga Jual,Nama Sales,No HP Konsumen\n";
        $row = "Banjarmasin,2026-10-18,INV-200,CUST-002,Siti Aminah,HP-001,Samsung A55 5G,SAMSUNG,1,5999000,Sales Budi,082345678901\n";
        $file = UploadedFile::fake()->createWithContent('CROSSELLING-test.csv', $header . $row);

        $response = $this->actingAs($admin)
            ->post(route('imports.store'), ['file' => $file]);

        $response->assertRedirect(route('imports.index'));
        $this->assertDatabaseCount('cross_selling_imports', 1);
        $this->assertDatabaseCount('cross_sellings', 1);
        $this->assertDatabaseCount('repeat_order_predictions', 1);
    }
}
