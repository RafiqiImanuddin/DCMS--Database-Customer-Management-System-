<?php

namespace Tests\Feature;

use App\Models\BroadcastReport;
use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\LeadCustomer;
use App\Models\RepeatOrderPrediction;
use App\Models\User;
use App\Services\CrossSellingImporter;
use App\Services\RepeatOrderPredictor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrossSellingImportDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_selling_upload_rejects_files_above_vercel_payload_limit(): void
    {
        Queue::fake();
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('cross-selling.xlsx', 3585, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/imports', ['file' => $file])
            ->assertSessionHasErrors('file');

        Queue::assertNothingPushed();
        $this->assertDatabaseCount('cross_selling_imports', 0);
    }

    public function test_csv_import_cleans_rows_and_removes_duplicates(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $content = implode("\n", [
            'Cabang,Posting Date,Customer/Vendor Code,Customer/Vendor Name,ItemCode,Dscription,Quantity,Nominal Transaksi,Nama Sales,No HP Konsumen,Document Number,Kolom Tidak Penting',
            'Jakarta,02/01/2026,C001,Pelanggan Satu,ITEM-1,Laptop,2,15000000,Sales A,081234567890,DOC-1,diabaikan',
            'Jakarta,02/01/2026,C001,Pelanggan Satu,ITEM-1,Laptop,2,15000000,Sales A,081234567890,DOC-1,diabaikan',
            'Jakarta,03/02/2026,,Pelanggan Invalid,ITEM-2,Mouse,1,500000,Sales B,081111111111,DOC-2,diabaikan',
        ]);
        Storage::disk('local')->put('cross-selling-imports/clean.csv', $content);
        $import = CrossSellingImport::create([
            'user_id' => $user->id,
            'original_name' => 'clean.csv',
            'stored_path' => 'cross-selling-imports/clean.csv',
            'status' => 'processing',
        ]);

        app(CrossSellingImporter::class)->import($import);
        $import->refresh();

        $this->assertSame('completed', $import->status);
        $this->assertSame(3, $import->row_count);
        $this->assertSame(1, $import->success_count);
        $this->assertSame(2, $import->failed_count);
        $this->assertDatabaseHas('cross_sellings', [
            'customer_vendor_code' => 'C001',
            'item_code' => 'ITEM-1',
            'posting_date' => '2026-01-02',
            'no_hp_customer' => '6281234567890',
            'cabang' => 'Jakarta',
            'slp_name' => 'Sales A',
            'harga_jual' => 15000000,
        ]);
    }

    public function test_predictor_uses_unique_dates_and_median_gap(): void
    {
        $user = User::factory()->create();
        $import = CrossSellingImport::create([
            'user_id' => $user->id,
            'original_name' => 'test.xlsx',
            'stored_path' => 'cross-selling-imports/test.xlsx',
            'status' => 'completed',
        ]);
        foreach (['2026-01-01', '2026-01-31', '2026-03-02', '2026-04-01'] as $date) {
            CrossSelling::create([
                'cross_selling_import_id' => $import->id,
                'customer_vendor_code' => 'C001',
                'customer_vendor_name' => 'Pelanggan Satu',
                'name' => 'Nama Konsumen Satu',
                'item_code' => 'ITEM-1',
                'dscription' => 'Laptop',
                'posting_date' => Carbon::parse($date)->toDateString(),
                'period_label' => '2026-01',
                'quantity' => 1,
                'harga_jual' => 10000000,
            ]);
        }

        app(RepeatOrderPredictor::class)->rebuild();

        $this->assertDatabaseHas('repeat_order_predictions', [
            'customer_vendor_code' => 'C001',
            'item_code' => 'ITEM-1',
            'customer_vendor_name' => 'Nama Konsumen Satu',
            'transaction_count' => 4,
            'average_gap_days' => 30,
        ]);
    }

    public function test_bulk_broadcast_creates_reports_for_selected_predictions(): void
    {
        $user = User::factory()->create();
        $first = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C001',
            'customer_vendor_name' => 'Pelangan Satu',
            'no_hp_customer' => '6281234567890',
            'classification' => 'tinggi',
            'predicted_repeat_date' => '2026-12-01',
            'predicted_repeat_date' => '2026-12-01',
            'confidence' => 90,
        ]);
        $second = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C002',
            'customer_vendor_name' => 'Pelangan Dua',
            'classification' => 'sedang',
            'confidence' => 75,
        ]);

        $response = $this->actingAs($user)->post(route('broadcasts.bulk'), [
            'prediction_ids' => [$first->id, $second->id],
            'message' => 'Halo Kaka, ada promo khusus untuk Anda.',
        ]);

        $response->assertRedirect(route('predictions.index'));
        $response->assertSessionHas('success', '2 pelanggan ditambahkan ke antrean/laporan broadcast.');
        $this->assertDatabaseHas('broadcast_reports', [
            'repeat_order_prediction_id' => $first->id,
            'message' => 'Halo Kaka, ada promo khusus untuk Anda.',
        ]);
        $this->assertDatabaseHas('broadcast_reports', [
            'repeat_order_prediction_id' => $second->id,
            'message' => 'Halo Kaka, ada promo khusus untuk Anda.',
        ]);
    }

    public function test_store_creates_import_and_processes_synchronously(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create(
            'cross-selling.xlsx',
            100,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );

        $response = $this->actingAs($user)->post(route('imports.store'), ['file' => $file]);

        $response->assertRedirect(route('imports.index'));
        $response->assertSessionHas('success');
        // Import is processed synchronously; status is either completed or failed (not processing).
        $this->assertDatabaseHas('cross_selling_imports', [
            'user_id' => $user->id,
            'original_name' => 'cross-selling.xlsx',
        ]);
        $this->assertDatabaseMissing('cross_selling_imports', [
            'user_id' => $user->id,
            'status' => 'processing',
        ]);
    }

    public function test_predictions_page_filters_by_brand(): void
    {
        $user = User::factory()->create();
        $import = CrossSellingImport::create([
            'user_id' => $user->id,
            'original_name' => 'brand-filter.xlsx',
            'status' => 'completed',
        ]);

        $xiaomiPrediction = RepeatOrderPrediction::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'XMI001',
            'customer_vendor_name' => 'Pelanggan Xiaomi',
            'item_code' => 'XMI-ITEM-001',
            'classification' => 'tinggi',
            'confidence' => 90,
        ]);
        RepeatOrderPrediction::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'SAM001',
            'customer_vendor_name' => 'Pelanggan Samsung',
            'item_code' => 'SAM-ITEM-001',
            'classification' => 'sedang',
            'confidence' => 80,
        ]);
        CrossSelling::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'XMI001',
            'item_code' => 'XMI-ITEM-001',
            'brand' => 'XMI',
            'quantity' => 2,
        ]);
        CrossSelling::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'SAM001',
            'item_code' => 'SAM-ITEM-001',
            'brand' => 'SAM',
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('predictions.index', ['brand' => 'XIAOMI']));

        $response->assertOk();
        $response->assertSee('Pelanggan Xiaomi');
        $response->assertDontSee('Pelanggan Samsung');
        $this->assertTrue($response->viewData('selectedBrand') === 'XIAOMI');
        $this->assertTrue($xiaomiPrediction->exists);
    }

    public function test_potential_chart_classification_link_filters_predictions(): void
    {
        $user = User::factory()->create();
        $import = CrossSellingImport::create([
            'user_id' => $user->id,
            'original_name' => 'classification-test.xlsx',
            'status' => 'completed',
        ]);

        RepeatOrderPrediction::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'TINGGI001',
            'customer_vendor_name' => 'Pelanggan Tinggi',
            'classification' => 'tinggi',
            'confidence' => 95,
        ]);
        RepeatOrderPrediction::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'RENDAH001',
            'customer_vendor_name' => 'Pelanggan Rendah',
            'classification' => 'rendah',
            'confidence' => 60,
        ]);

        $response = $this->actingAs($user)->get(route('predictions.index', ['classification' => 'tinggi']));

        $response->assertOk();
        $response->assertSee('Pelanggan Tinggi');
        $response->assertDontSee('Pelanggan Rendah');
        $response->assertSee(route('predictions.index'), false);
        $response->assertSee(route('predictions.index', ['classification' => 'sedang']), false);
    }

    public function test_dashboard_maps_brands_and_renders_sales_chart(): void
    {
        $user = User::factory()->create();
        $import = CrossSellingImport::create([
            'user_id' => $user->id,
            'original_name' => 'brand-test.xlsx',
            'status' => 'completed',
        ]);
        foreach ([
            ['brand' => 'SAM', 'quantity' => 5],
            ['brand' => 'XMI', 'quantity' => 3],
            ['brand' => 'ETC', 'quantity' => 2],
        ] as $row) {
            CrossSelling::create([
                'cross_selling_import_id' => $import->id,
                'brand' => $row['brand'],
                'quantity' => $row['quantity'],
                'customer_vendor_code' => 'C001',
                'posting_date' => '2026-11-01',
                'period_label' => '2026-11',
            ]);
        }

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Brand Terlaris');
        $response->assertSee('SAMSUNG');
        $response->assertSee('XIAOMI');
        $response->assertSee('LAINNYA');
        $response->assertSee(route('predictions.index', ['classification' => 'tinggi']), false);

        $filteredResponse = $this->actingAs($user)->get(route('dashboard', ['brand' => 'XIAOMI']));

        $filteredResponse->assertOk();
        $filteredResponse->assertSee('Filter Brand');
        $filteredResponse->assertSee('XIAOMI');
        $filteredResponse->assertSee('3 unit');
    }

    public function test_owner_can_render_import_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('imports.index'));

        $response->assertOk();
        $response->assertSee('Import Cross-selling');
        $response->assertSee('Mengunggah dan memproses file');
        $response->assertSee('Belum ada import.');
    }

    public function test_owner_can_read_import_processing_status(): void
    {
        $owner = User::factory()->create();
        $import = CrossSellingImport::create([
            'user_id' => $owner->id,
            'original_name' => 'processing.xlsx',
            'status' => 'processing',
            'row_count' => 2500,
            'success_count' => 2400,
            'failed_count' => 100,
        ]);

        $response = $this->actingAs($owner)->getJson(route('imports.status', $import));

        $response->assertOk();
        $response->assertExactJson([
            'status' => 'processing',
            'row_count' => 2500,
            'success_count' => 2400,
            'failed_count' => 100,
        ]);
    }

    public function test_user_cannot_read_another_users_import_status(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $import = CrossSellingImport::create([
            'user_id' => $owner->id,
            'original_name' => 'private.xlsx',
            'status' => 'processing',
        ]);

        $response = $this->actingAs($otherUser)->getJson(route('imports.status', $import));

        $response->assertNotFound();
    }

    public function test_owner_can_delete_import_and_related_data(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $import = CrossSellingImport::create([
            'user_id' => $user->id,
            'original_name' => 'penjualan.xlsx',
            'stored_path' => 'cross-selling-imports/penjualan.xlsx',
            'status' => 'completed',
        ]);
        Storage::disk('local')->put($import->stored_path, 'test');
        CrossSelling::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'C001',
            'customer_vendor_name' => 'Pelanggan Potensial',
            'posting_date' => '2026-10-01',
            'period_label' => '2026-10',
        ]);
        $prediction = RepeatOrderPrediction::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'C001',
            'customer_vendor_name' => 'Pelanggan Potensial',
            'classification' => 'tinggi',
            'confidence' => 90,
        ]);

        $response = $this->actingAs($user)->delete(route('imports.destroy', $import));

        $response->assertRedirect(route('imports.index'));
        $response->assertSessionHas('success', 'Data import berhasil dihapus.');
        $this->assertModelMissing($import);
        $this->assertDatabaseMissing('cross_sellings', ['cross_selling_import_id' => $import->id]);
        $this->assertDatabaseMissing('repeat_order_predictions', ['id' => $prediction->id]);
        Storage::disk('local')->assertMissing($import->stored_path);
    }

    public function test_user_cannot_delete_another_users_import(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $import = CrossSellingImport::create([
            'user_id' => $owner->id,
            'original_name' => 'penjualan.xlsx',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($otherUser)->delete(route('imports.destroy', $import));

        $response->assertNotFound();
        $this->assertModelExists($import);
    }

    public function test_owner_can_download_filtered_broadcast_report_csv(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        BroadcastReport::create([
            'user_id' => $owner->id,
            'customer_vendor_code' => 'C001',
            'phone_number' => '6281234567890',
            'channel' => 'whatsapp',
            'message' => 'Pesan terkirim',
            'status' => 'sent',
        ]);
        BroadcastReport::create([
            'user_id' => $owner->id,
            'customer_vendor_code' => 'C002',
            'phone_number' => '6281111111111',
            'channel' => 'whatsapp',
            'message' => 'Pesan gagal',
            'status' => 'failed',
        ]);
        BroadcastReport::create([
            'user_id' => $otherUser->id,
            'customer_vendor_code' => 'OTHER',
            'phone_number' => '6280000000000',
            'channel' => 'whatsapp',
            'message' => 'Pesan user lain',
            'status' => 'sent',
        ]);

        $response = $this->actingAs($owner)->get(route('broadcasts.download', ['status' => 'sent']));

        $response->assertOk();
        $response->assertDownload();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', (string) $response->headers->get('content-type'));

        $zip = new \ZipArchive();
        $zip->open($response->getFile()->getPathname());
        $sheetXml = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        $this->assertStringContainsString('Kode Pelanggan', $sheetXml);
        $this->assertStringContainsString('C001', $sheetXml);
        $this->assertStringNotContainsString('C002', $sheetXml);
        $this->assertStringNotContainsString('OTHER', $sheetXml);
        $this->assertStringNotContainsString('Pesan user lain', $sheetXml);
    }

    public function test_owner_can_delete_broadcast_report(): void
    {
        $owner = User::factory()->create();
        $report = BroadcastReport::create([
            'user_id' => $owner->id,
            'customer_vendor_code' => 'C001',
            'phone_number' => '6281234567890',
            'channel' => 'whatsapp',
            'message' => 'Pesan broadcast',
            'status' => 'logged',
        ]);

        $response = $this->actingAs($owner)->delete(route('broadcasts.destroy', $report));

        $response->assertRedirect(route('broadcasts.index'));
        $response->assertSessionHas('success', 'Laporan broadcast berhasil dihapus.');
        $this->assertModelMissing($report);
    }

    public function test_user_cannot_delete_another_users_broadcast_report(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $report = BroadcastReport::create([
            'user_id' => $owner->id,
            'customer_vendor_code' => 'C001',
            'phone_number' => '6281234567890',
            'channel' => 'whatsapp',
            'message' => 'Pesan broadcast',
            'status' => 'logged',
        ]);

        $response = $this->actingAs($otherUser)->delete(route('broadcasts.destroy', $report));

        $response->assertNotFound();
        $this->assertModelExists($report);
    }

    public function test_dashboard_can_filter_by_whatsapp_status(): void
    {
        $user = User::factory()->create();

        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'DVALID',
            'customer_vendor_name' => 'Dashboard Valid',
            'no_hp_customer' => '6281234567890',
            'classification' => 'tinggi',
            'predicted_repeat_date' => '2026-12-01',
        ]);
        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'DINVALID',
            'customer_vendor_name' => 'Dashboard Invalid',
            'no_hp_customer' => '081234567890',
            'classification' => 'sedang',
            'predicted_repeat_date' => '2026-12-02',
        ]);
        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'DMISSING',
            'customer_vendor_name' => 'Dashboard Tanpa Nomor',
            'no_hp_customer' => null,
            'classification' => 'baru',
            'predicted_repeat_date' => '2026-12-03',
        ]);

        $validResponse = $this->actingAs($user)->get(route('dashboard', ['phone_status' => 'valid']));
        $invalidResponse = $this->actingAs($user)->get(route('dashboard', ['phone_status' => 'invalid']));
        $missingResponse = $this->actingAs($user)->get(route('dashboard', ['phone_status' => 'missing']));

        $validResponse->assertOk()->assertViewHas('totalPredictions', 1)->assertSee('Dashboard Valid');
        $invalidResponse->assertOk()->assertViewHas('totalPredictions', 1)->assertSee('Dashboard Invalid');
        $missingResponse->assertOk()->assertViewHas('totalPredictions', 1)->assertSee('Dashboard Tanpa Nomor');
        $validResponse->assertSee('Filter WhatsApp')->assertSee('WhatsApp valid');
    }

    public function test_master_customer_phone_chart_can_filter_valid_invalid_and_missing_numbers(): void
    {
        $user = User::factory()->create();
        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'VALID',
            'customer_vendor_name' => 'Pelanggan Valid',
            'no_hp_customer' => '6281234567890',
            'classification' => 'tinggi',
            'confidence' => 90,
        ]);
        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'INVALID',
            'customer_vendor_name' => 'Pelanggan Invalid',
            'no_hp_customer' => '081234567890',
            'classification' => 'sedang',
            'confidence' => 75,
        ]);
        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'MISSING',
            'customer_vendor_name' => 'Pelanggan Tanpa Nomor',
            'no_hp_customer' => null,
            'classification' => 'baru',
            'confidence' => 20,
        ]);

        $validResponse = $this->actingAs($user)->get(route('predictions.index', ['phone_status' => 'valid']));
        $invalidResponse = $this->actingAs($user)->get(route('predictions.index', ['phone_status' => 'invalid']));
        $missingResponse = $this->actingAs($user)->get(route('predictions.index', ['phone_status' => 'missing']));

        $validResponse->assertOk();
        $validResponse->assertSee('Pelanggan Valid');
        $validResponse->assertSee('WhatsApp valid');
        $validResponse->assertDontSee('Pelanggan Invalid');
        $validResponse->assertDontSee('Pelanggan Tanpa Nomor');

        $invalidResponse->assertOk();
        $invalidResponse->assertSee('Pelanggan Invalid');
        $invalidResponse->assertSee('Nomor tidak valid');
        $invalidResponse->assertDontSee('Pelanggan Valid');

        $missingResponse->assertOk();
        $missingResponse->assertSee('Pelanggan Tanpa Nomor');
        $missingResponse->assertSee('Tanpa nomor');
        $missingResponse->assertDontSee('Pelanggan Valid');
    }

    public function test_leads_customer_can_filter_by_whatsapp_status(): void
    {
        $user = User::factory()->create();

        foreach ([
            ['code' => 'LVALID', 'name' => 'Lead Valid', 'phone' => '6281234567890'],
            ['code' => 'LINVALID', 'name' => 'Lead Invalid', 'phone' => '081234567890'],
            ['code' => 'LMISSING', 'name' => 'Lead Tanpa Nomor', 'phone' => null],
        ] as $data) {
            $prediction = RepeatOrderPrediction::create([
                'customer_vendor_code' => $data['code'],
                'customer_vendor_name' => $data['name'],
                'no_hp_customer' => $data['phone'],
                'classification' => 'tinggi',
                'confidence' => 90,
            ]);
            LeadCustomer::create([
                'user_id' => $user->id,
                'repeat_order_prediction_id' => $prediction->id,
                'customer_vendor_code' => $data['code'],
                'customer_vendor_name' => $data['name'],
                'no_hp_customer' => $data['phone'],
                'classification' => 'tinggi',
                'status' => 'follow_up',
                'assigned_at' => now(),
            ]);
        }

        $validResponse = $this->actingAs($user)->get(route('leads.index', ['phone_status' => 'valid']));
        $invalidResponse = $this->actingAs($user)->get(route('leads.index', ['phone_status' => 'invalid']));
        $missingResponse = $this->actingAs($user)->get(route('leads.index', ['phone_status' => 'missing']));

        $validResponse->assertOk();
        $validResponse->assertSee('Lead Valid');
        $validResponse->assertDontSee('Lead Invalid');
        $validResponse->assertDontSee('Lead Tanpa Nomor');

        $invalidResponse->assertOk();
        $invalidResponse->assertSee('Lead Invalid');
        $invalidResponse->assertDontSee('Lead Valid');
        $invalidResponse->assertDontSee('Lead Tanpa Nomor');

        $missingResponse->assertOk();
        $missingResponse->assertSee('Lead Tanpa Nomor');
        $missingResponse->assertDontSee('Lead Valid');
        $missingResponse->assertDontSee('Lead Invalid');
    }

    public function test_selected_master_customers_can_be_assigned_to_leads(): void
    {
        $user = User::factory()->create();
        $prediction = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C001',
            'customer_vendor_name' => 'Pelanggan Satu',
            'no_hp_customer' => '6281234567890',
            'item_code' => 'ITEM-1',
            'dscription' => 'Laptop',
            'classification' => 'tinggi',
            'confidence' => 90,
        ]);

        $response = $this->actingAs($user)->post(route('predictions.assign'), [
            'prediction_ids' => [$prediction->id],
        ]);

        $response->assertRedirect(route('leads.index'));
        $response->assertSessionHas('success', '1 pelanggan berhasil dipindahkan ke Leads Customer.');
        $this->assertDatabaseHas('lead_customers', [
            'user_id' => $user->id,
            'repeat_order_prediction_id' => $prediction->id,
            'customer_vendor_code' => 'C001',
            'status' => 'follow_up',
        ]);

        $masterResponse = $this->actingAs($user)->get(route('predictions.index'));
        $leadsResponse = $this->actingAs($user)->get(route('leads.index'));

        $masterResponse->assertOk();
        $masterResponse->assertDontSee('Pelanggan Satu');
        $leadsResponse->assertOk();
        $leadsResponse->assertSee('Pelanggan Satu');
        $leadsResponse->assertSee('Leads Customer');
    }

    public function test_assign_requires_selected_predictions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('predictions.index'))->post(route('predictions.assign'), [
            'prediction_ids' => [],
        ]);

        $response->assertRedirect(route('predictions.index'));
        $response->assertSessionHasErrors('prediction_ids');
        $this->assertDatabaseCount('lead_customers', 0);
    }
}
