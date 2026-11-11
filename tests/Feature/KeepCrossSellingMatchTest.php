<?php

namespace Tests\Feature;

use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\SosmedKeep;
use App\Models\User;
use App\Services\KeepCrossSellingMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KeepCrossSellingMatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_matcher_detects_item_match_and_updates_keep_to_win(): void
    {
        $keep = SosmedKeep::create([
            'nama' => 'Budi Santoso',
            'nomor_wa' => '081234567890',
            'barang_keep' => 'iPhone 15 128GB Black',
            'status' => 'PENDING',
            'remarks' => 'SOSMED 1 BJM',
            'cabang' => 'Banjarmasin',
        ]);

        $trx = CrossSelling::create([
            'no_hp_customer' => '6281234567890',
            'dscription' => 'IPHONE 15 128GB BLACK',
            'brand' => 'APPLE',
            'document_number' => 'AR-990011',
            'posting_date' => '2026-11-10',
            'cabang' => 'Banjarmasin',
        ]);

        $matcher = new KeepCrossSellingMatcher();
        $result = $matcher->matchTransactions(collect([$trx]));

        $this->assertEquals(1, $result['matched_count']);

        $keep->refresh();
        $this->assertEquals('WIN', $keep->status);
        $this->assertEquals('AR-990011', $keep->ar_vlookup);
        $this->assertEquals('2026-11-10', $keep->tanggal_vlookup);
        $this->assertEquals('AR-990011', $keep->report_ar);
        $this->assertStringContainsString('Auto-Match SAP', $keep->feedback_tim_fu);
    }

    public function test_matcher_rejects_accessory_purchase_when_keep_is_phone_unit(): void
    {
        $keep = SosmedKeep::create([
            'nama' => 'Citra Dewi',
            'nomor_wa' => '081299887766',
            'barang_keep' => 'iPhone 15 128GB',
            'status' => 'PENDING',
            'remarks' => 'SOSMED 2 BJM',
        ]);

        $trx = CrossSelling::create([
            'no_hp_customer' => '081299887766',
            'dscription' => 'TEMPERED GLASS IPHONE 15 CLEAR',
            'brand' => 'APPLE',
            'document_number' => 'AR-ACC-01',
            'posting_date' => '2026-11-10',
        ]);

        $matcher = new KeepCrossSellingMatcher();
        $result = $matcher->matchTransactions(collect([$trx]));

        $this->assertEquals(0, $result['matched_count']);

        $keep->refresh();
        $this->assertEquals('PENDING', $keep->status);
        $this->assertNull($keep->ar_vlookup);
    }

    public function test_matcher_rejects_different_phone_model(): void
    {
        $keep = SosmedKeep::create([
            'nama' => 'Doni Pratama',
            'nomor_wa' => '085211223344',
            'barang_keep' => 'Samsung Galaxy S24 Ultra 512GB',
            'status' => 'PENDING',
            'remarks' => 'SOSMED 3 BJM',
        ]);

        $trx = CrossSelling::create([
            'no_hp_customer' => '085211223344',
            'dscription' => 'SAMSUNG GALAXY A05 4/64GB BLACK',
            'brand' => 'SAMSUNG',
            'document_number' => 'AR-DIFF-01',
            'posting_date' => '2026-11-10',
        ]);

        $matcher = new KeepCrossSellingMatcher();
        $result = $matcher->matchTransactions(collect([$trx]));

        $this->assertEquals(0, $result['matched_count']);

        $keep->refresh();
        $this->assertEquals('PENDING', $keep->status);
    }

    public function test_matcher_allows_accessory_keep_when_same_accessory_is_purchased_in_same_ar(): void
    {
        $keep = SosmedKeep::create([
            'nama' => 'Fani Rahma',
            'nomor_wa' => '089612345678',
            'barang_keep' => 'Tempered Glass iPhone 15 Pro',
            'status' => 'PENDING',
            'remarks' => 'SOSMED 1 BJM',
        ]);

        $trx = CrossSelling::create([
            'no_hp_customer' => '089612345678',
            'dscription' => 'TEMPERED GLASS IPHONE 15 PRO BENING',
            'brand' => 'APPLE',
            'document_number' => 'AR-ACC-OK-99',
            'posting_date' => '2026-11-10',
        ]);

        $matcher = new KeepCrossSellingMatcher();
        $result = $matcher->matchTransactions(collect([$trx]));

        $this->assertEquals(1, $result['matched_count']);

        $keep->refresh();
        $this->assertEquals('WIN', $keep->status);
        $this->assertEquals('AR-ACC-OK-99', $keep->ar_vlookup);
    }

    public function test_matcher_matches_phone_keep_when_ar_contains_both_phone_and_accessories(): void
    {
        $keep = SosmedKeep::create([
            'nama' => 'Gilang Pratama',
            'nomor_wa' => '081377889900',
            'barang_keep' => 'iPhone 15 Pro 128GB',
            'status' => 'PENDING',
            'remarks' => 'SOSMED 2 BJM',
        ]);

        $trx1 = CrossSelling::create([
            'no_hp_customer' => '081377889900',
            'dscription' => 'TEMPERED GLASS IPHONE 15 PRO',
            'brand' => 'APPLE',
            'document_number' => 'AR-COMBO-77',
            'posting_date' => '2026-11-10',
        ]);

        $trx2 = CrossSelling::create([
            'no_hp_customer' => '081377889900',
            'dscription' => 'IPHONE 15 PRO 128GB NATURAL TITANIUM',
            'brand' => 'APPLE',
            'document_number' => 'AR-COMBO-77',
            'posting_date' => '2026-11-10',
        ]);

        $matcher = new KeepCrossSellingMatcher();
        $result = $matcher->matchTransactions(collect([$trx1, $trx2]));

        $this->assertEquals(1, $result['matched_count']);

        $keep->refresh();
        $this->assertEquals('WIN', $keep->status);
        $this->assertEquals('AR-COMBO-77', $keep->ar_vlookup);
        $this->assertStringContainsString('IPHONE 15 PRO 128GB NATURAL TITANIUM', $keep->feedback_tim_fu);
    }

    public function test_uploading_cross_selling_file_automatically_matches_and_updates_keep(): void
    {
        Storage::fake('local');
        $superadmin = User::factory()->superadmin()->create();

        $keep = SosmedKeep::create([
            'nama' => 'Eko Kurniawan',
            'nomor_wa' => '087811223344',
            'barang_keep' => 'Samsung A55 5G',
            'status' => 'PENDING',
            'remarks' => 'SOSMED 1 BJM',
            'cabang' => 'Banjarmasin',
        ]);

        $csv = implode("\n", [
            'Cabang,Posting Date,Document Number,Customer/Vendor Code,Customer/Vendor Name,ItemCode,Dscription,Brand,Quantity,Harga Jual,Nama Sales,No HP Konsumen',
            'Banjarmasin,2026-10-18,DOC-AUTO-55,CUST-55,Eko,ITM-A55,SAMSUNG GALAXY A55 5G 8/256GB NAVY,SAMSUNG,1,5999000,Sales 1,087811223344',
        ]);
        $file = UploadedFile::fake()->createWithContent('cross_match.csv', $csv);

        $response = $this->actingAs($superadmin)->post(route('imports.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('imports.index'));
        $keep->refresh();
        $this->assertEquals('WIN', $keep->status);
        $this->assertEquals('DOC-AUTO-55', $keep->ar_vlookup);
    }
}
