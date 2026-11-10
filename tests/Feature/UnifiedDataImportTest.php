<?php

namespace Tests\Feature;

use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\Turnover;
use App\Models\TurnoverImport;
use App\Models\User;
use App\Services\CrossSellingImporter;
use App\Services\TurnoverImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UnifiedDataImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_unified_import_page(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)->get(route('imports.index'));
        $response->assertStatus(200);
        $response->assertSee('Pusat Unggah Data Harian & Multi-Periode');
        $response->assertSee('File Cross-Selling SAP');
        $response->assertSee('File Turnover SAP');
        $response->assertSee('Riwayat Cross-Selling');
        $response->assertSee('Riwayat Turnover');
    }

    public function test_staff_cannot_access_unified_import_page(): void
    {
        $staff = User::factory()->admin()->create();

        $response = $this->actingAs($staff)->get(route('imports.index'));
        $response->assertStatus(403);
    }

    public function test_superadmin_can_download_both_templates(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $resCross = $this->actingAs($superadmin)->get(route('imports.template'));
        $resCross->assertStatus(200);
        $resCross->assertHeader('Content-Disposition', 'attachment; filename=template-cross-selling.xlsx');

        $resTurnover = $this->actingAs($superadmin)->get(route('imports.template_turnover'));
        $resTurnover->assertStatus(200);
        $resTurnover->assertHeader('Content-Disposition', 'attachment; filename=template-turnover.xlsx');
    }

    public function test_superadmin_can_upload_cross_selling_alone(): void
    {
        Storage::fake('local');
        $superadmin = User::factory()->superadmin()->create();

        $csv = implode("\n", [
            'Cabang,Posting Date,Document Number,Customer/Vendor Code,Customer/Vendor Name,ItemCode,Dscription,Brand,Quantity,Harga Jual,Nama Sales,No HP Konsumen',
            'Banjarmasin,2026-10-18,DOC-101,CUST-101,Ahmad,ITM-1,HP Samsung,SAMSUNG,1,5000000,Sales 1,081234567890',
        ]);
        $file = UploadedFile::fake()->createWithContent('cross.csv', $csv);

        $response = $this->actingAs($superadmin)->post(route('imports.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('imports.index'));
        $this->assertDatabaseHas('cross_selling_imports', ['original_name' => 'cross.csv', 'status' => 'completed']);
        $this->assertDatabaseHas('cross_sellings', ['document_number' => 'DOC-101']);
    }

    public function test_superadmin_can_upload_turnover_alone(): void
    {
        Storage::fake('local');
        $superadmin = User::factory()->superadmin()->create();

        $csv = implode("\n", [
            'Cabang,Posting Date,Document Number,Sales Employee Name,Remarks,SalesCabang,DPP,GrossProfit,Total',
            'Banjarmasin,2026-10-18,DOC-201,Sales 1,Order Online,Banjarmasin,10000000,500000,11100000',
        ]);
        $file = UploadedFile::fake()->createWithContent('turnover.csv', $csv);

        $response = $this->actingAs($superadmin)->post(route('imports.store'), [
            'turnover_file' => $file,
        ]);

        $response->assertRedirect(route('imports.index'));
        $this->assertDatabaseHas('turnover_imports', ['original_name' => 'turnover.csv', 'status' => 'completed']);
        $this->assertDatabaseHas('turnovers', ['document_number' => 'DOC-201', 'dpp' => 10000000]);
    }

    public function test_server_upload_limit_error_on_turnover_file_returns_clear_actionable_message(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        // Simulate a file rejected by PHP ini directive (UPLOAD_ERR_INI_SIZE = 1)
        $brokenFile = new UploadedFile(
            path: tempnam(sys_get_temp_dir(), 'test_'),
            originalName: 'turnover_besar.xlsx',
            mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            error: UPLOAD_ERR_INI_SIZE,
            test: true
        );

        $response = $this->actingAs($superadmin)->postJson(route('imports.store'), [
            'turnover_file' => $brokenFile,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
        $message = $response->json('message');
        $this->assertStringContainsString('upload_max_filesize', $message);
        $this->assertStringContainsString('turnover_besar.xlsx', $message);
    }

    public function test_server_upload_limit_error_on_cross_file_returns_clear_actionable_message(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $brokenFile = new UploadedFile(
            path: tempnam(sys_get_temp_dir(), 'test_'),
            originalName: 'cross_besar.xlsx',
            mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            error: UPLOAD_ERR_INI_SIZE,
            test: true
        );

        $response = $this->actingAs($superadmin)->postJson(route('imports.store'), [
            'file' => $brokenFile,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
        $message = $response->json('message');
        $this->assertStringContainsString('upload_max_filesize', $message);
        $this->assertStringContainsString('cross_besar.xlsx', $message);
    }

    public function test_superadmin_can_upload_both_files_together_in_single_submission(): void
    {
        Storage::fake('local');
        $superadmin = User::factory()->superadmin()->create();

        $crossCsv = implode("\n", [
            'Cabang,Posting Date,Document Number,Customer/Vendor Code,Customer/Vendor Name,ItemCode,Dscription,Brand,Quantity,Harga Jual,Nama Sales,No HP Konsumen',
            'Banjarmasin,2026-11-01,DOC-301,CUST-301,Siti,ITM-2,iPhone 15,IPHONE,1,15000000,Sales 2,081299998888',
        ]);
        $turnoverCsv = implode("\n", [
            'Cabang,Posting Date,Document Number,Sales Employee Name,Remarks,SalesCabang,DPP,GrossProfit,Total',
            'Banjarmasin,2026-11-01,DOC-301,Sales 2,Order Sosmed,Banjarmasin,15000000,750000,16650000',
        ]);

        $crossFile = UploadedFile::fake()->createWithContent('cross_nov.csv', $crossCsv);
        $turnoverFile = UploadedFile::fake()->createWithContent('turnover_nov.csv', $turnoverCsv);

        $response = $this->actingAs($superadmin)->postJson(route('imports.store'), [
            'file' => $crossFile,
            'turnover_file' => $turnoverFile,
        ]);

        $response->assertOk();
        $response->assertJsonPath('results.cross_selling.status', 'completed');
        $response->assertJsonPath('results.turnover.status', 'completed');

        $this->assertDatabaseHas('cross_sellings', ['document_number' => 'DOC-301']);
        $this->assertDatabaseHas('turnovers', ['document_number' => 'DOC-301']);
    }

    public function test_turnover_multi_period_retention_and_api(): void
    {
        $user = User::factory()->superadmin()->create();

        // 1. Create October record
        Turnover::create([
            'posting_date' => '2026-10-15',
            'document_number' => 'DOC-OCT-01',
            'slp_name' => 'Sales Ahmad',
            'dpp' => 5000000,
            'gross_profit' => 250000,
            'total' => 5550000,
            'period_label' => '2026-10',
            'cleaning_hash' => 'hash_oct_01',
        ]);

        // 2. Create November record
        Turnover::create([
            'posting_date' => '2026-11-05',
            'document_number' => 'DOC-NOV-01',
            'slp_name' => 'Sales Budi',
            'dpp' => 8000000,
            'gross_profit' => 400000,
            'total' => 8880000,
            'period_label' => '2026-11',
            'cleaning_hash' => 'hash_nov_01',
        ]);

        // Verify both periods exist simultaneously (no overwrite)
        $this->assertDatabaseCount('turnovers', 2);

        // Query API for November only
        $resNov = $this->actingAs($user)->getJson(route('api.sosmed.turnover_records', ['month' => '2026-11']));
        $resNov->assertOk();
        $resNov->assertJsonPath('total_count', 1);
        $resNov->assertJsonPath('total_dpp', 8000000);

        // Query API for October only
        $resOct = $this->actingAs($user)->getJson(route('api.sosmed.turnover_records', ['month' => '2026-10']));
        $resOct->assertOk();
        $resOct->assertJsonPath('total_count', 1);
        $resOct->assertJsonPath('total_dpp', 5000000);
    }

    public function test_superadmin_can_delete_turnover_import(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $import = TurnoverImport::create([
            'user_id' => $superadmin->id,
            'original_name' => 'to_delete.csv',
            'stored_path' => 'turnover-imports/to_delete.csv',
            'status' => 'completed',
        ]);
        Turnover::create([
            'turnover_import_id' => $import->id,
            'posting_date' => '2026-11-05',
            'document_number' => 'DOC-DEL-01',
            'slp_name' => 'Sales Test',
            'dpp' => 1000000,
            'cleaning_hash' => 'hash_del_01',
        ]);

        $this->assertDatabaseCount('turnovers', 1);

        $response = $this->actingAs($superadmin)->delete(route('imports.destroy_turnover', $import));
        $response->assertRedirect(route('imports.index'));

        $this->assertDatabaseCount('turnover_imports', 0);
        $this->assertDatabaseCount('turnovers', 0);
    }

    public function test_get_import_by_id_redirects_to_imports_index(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)->get('/imports/4');
        $response->assertRedirect(route('imports.index'));
    }

    public function test_superadmin_can_delete_cross_selling_import_uploaded_by_another_user(): void
    {
        $otherUser = User::factory()->create();
        $superadmin = User::factory()->superadmin()->create();

        $import = CrossSellingImport::create([
            'user_id' => $otherUser->id,
            'original_name' => 'other_file.xlsx',
            'stored_path' => 'cross-selling-imports/other_file.xlsx',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($superadmin)->delete(route('imports.destroy', $import));
        $response->assertRedirect(route('imports.index'));
        $this->assertDatabaseMissing('cross_selling_imports', ['id' => $import->id]);
    }

    public function test_cross_selling_file_larger_than_old_3_5mb_limit_is_accepted(): void
    {
        Storage::fake('local');
        $superadmin = User::factory()->superadmin()->create();

        // 10 MB fake file (previously failed with max:3584)
        $file = UploadedFile::fake()->create('large_cross.csv', 10240, 'text/csv');

        $response = $this->actingAs($superadmin)->post(route('imports.store'), [
            'file' => $file,
        ]);

        // Should NOT fail validation
        $response->assertSessionDoesntHaveErrors(['file']);
    }

    public function test_cross_selling_file_exceeding_40mb_returns_custom_error(): void
    {
        Storage::fake('local');
        $superadmin = User::factory()->superadmin()->create();

        // 45 MB fake file (exceeds max:40960)
        $file = UploadedFile::fake()->create('huge_cross.csv', 46080, 'text/csv');

        $response = $this->actingAs($superadmin)->postJson(route('imports.store'), [
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
        $response->assertJsonFragment([
            'message' => 'Ukuran berkas Cross-Selling terlalu besar (maksimal 40 MB).',
        ]);
    }

    public function test_superadmin_can_bulk_delete_cross_selling_imports(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $import1 = CrossSellingImport::create([
            'user_id' => $superadmin->id,
            'original_name' => 'batch1.csv',
            'stored_path' => 'cross-selling-imports/batch1.csv',
            'status' => 'completed',
        ]);
        CrossSelling::create([
            'cross_selling_import_id' => $import1->id,
            'document_number' => 'DOC-B1',
            'posting_date' => '2026-11-01',
        ]);

        $import2 = CrossSellingImport::create([
            'user_id' => $superadmin->id,
            'original_name' => 'batch2.csv',
            'stored_path' => 'cross-selling-imports/batch2.csv',
            'status' => 'completed',
        ]);
        CrossSelling::create([
            'cross_selling_import_id' => $import2->id,
            'document_number' => 'DOC-B2',
            'posting_date' => '2026-11-02',
        ]);

        $this->assertDatabaseCount('cross_selling_imports', 2);
        $this->assertDatabaseCount('cross_sellings', 2);

        $response = $this->actingAs($superadmin)->delete(route('imports.bulk_destroy'), [
            'ids' => [$import1->id, $import2->id],
        ]);

        $response->assertRedirect(route('imports.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('cross_selling_imports', 0);
        $this->assertDatabaseCount('cross_sellings', 0);
    }

    public function test_superadmin_can_bulk_delete_turnover_imports(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $t1 = TurnoverImport::create([
            'user_id' => $superadmin->id,
            'original_name' => 'turnover1.csv',
            'stored_path' => 'turnover-imports/turnover1.csv',
            'status' => 'completed',
        ]);
        Turnover::create([
            'turnover_import_id' => $t1->id,
            'document_number' => 'T-DOC-1',
            'slp_name' => 'Sales Test 1',
            'posting_date' => '2026-11-01',
            'cleaning_hash' => 'hash_t1',
        ]);

        $t2 = TurnoverImport::create([
            'user_id' => $superadmin->id,
            'original_name' => 'turnover2.csv',
            'stored_path' => 'turnover-imports/turnover2.csv',
            'status' => 'completed',
        ]);
        Turnover::create([
            'turnover_import_id' => $t2->id,
            'document_number' => 'T-DOC-2',
            'slp_name' => 'Sales Test 2',
            'posting_date' => '2026-11-02',
            'cleaning_hash' => 'hash_t2',
        ]);

        $this->assertDatabaseCount('turnover_imports', 2);
        $this->assertDatabaseCount('turnovers', 2);

        $response = $this->actingAs($superadmin)->delete(route('imports.bulk_destroy_turnover'), [
            'ids' => [$t1->id, $t2->id],
        ]);

        $response->assertRedirect(route('imports.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('turnover_imports', 0);
        $this->assertDatabaseCount('turnovers', 0);
    }
}

