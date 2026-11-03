<?php

namespace Tests\Feature;

use App\Models\CrossSelling;
use App\Models\RepeatOrderPrediction;
use App\Models\User;
use App\Support\BrandCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandCategoryAccTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_with_acc_is_classified_as_lainnya(): void
    {
        $this->assertTrue(BrandCategory::isAccessory('ACC.APL.SLK29783'));
        $this->assertTrue(BrandCategory::isAccessory('ACC_APL.SLK29783'));
        $this->assertTrue(BrandCategory::isAccessory('ACC.UGR.TC00053'));
        $this->assertFalse(BrandCategory::isAccessory('GAD.XMI.HP01237'));
        $this->assertFalse(BrandCategory::isAccessory('GAD.SAM.HP01311'));

        $this->assertEquals('LAINNYA', BrandCategory::resolve('ACC_APL.SLK29783'));
        $this->assertEquals('LAINNYA', BrandCategory::resolve('ACC.UGR.TC00053'));
        $this->assertEquals('XIAOMI', BrandCategory::resolve('GAD.XMI.HP01237'));
        $this->assertEquals('SAMSUNG', BrandCategory::resolve('GAD.SAM.HP01311'));
        $this->assertEquals('IPHONE', BrandCategory::resolve('GAD.APL.HP001'));
    }

    public function test_master_customer_filter_brand_separates_acc_into_lainnya(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Handphone Apple
        $gadgetApple = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C001',
            'customer_vendor_name' => 'Budi',
            'no_hp_customer' => '628123456789',
            'item_code' => 'GAD.APL.HP001',
            'dscription' => 'iPhone 15 Pro',
            'classification' => 'tinggi',
            'predicted_repeat_date' => now()->addDays(10),
        ]);
        CrossSelling::create([
            'customer_vendor_code' => 'C001',
            'item_code' => 'GAD.APL.HP001',
            'brand' => 'APL',
            'quantity' => 1,
            'harga_jual' => 20000000,
        ]);

        // Casing Apple (ACC)
        $caseApple = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C002',
            'customer_vendor_name' => 'Kaka',
            'no_hp_customer' => '628123456788',
            'item_code' => 'ACC_APL.SLK29783',
            'dscription' => 'SLK APL IP 13 PRO PL5 CLR LIST PINK',
            'classification' => 'tinggi',
            'predicted_repeat_date' => now()->addDays(5),
        ]);
        CrossSelling::create([
            'customer_vendor_code' => 'C002',
            'item_code' => 'ACC_APL.SLK29783',
            'brand' => 'APL',
            'quantity' => 1,
            'harga_jual' => 250000,
        ]);

        // Filter IPHONE: Hanya GAD.APL.HP001 yang muncul, ACC_APL TIDAK BOLEH muncul
        $respIphone = $this->actingAs($admin)->get(route('predictions.index', ['brand' => 'IPHONE']));
        $respIphone->assertOk();
        $respIphone->assertSee('GAD.APL.HP001');
        $respIphone->assertDontSee('ACC_APL.SLK29783');

        // Filter LAINNYA: ACC_APL HARUS muncul di sini
        $respLainnya = $this->actingAs($admin)->get(route('predictions.index', ['brand' => 'LAINNYA']));
        $respLainnya->assertOk();
        $respLainnya->assertSee('ACC_APL.SLK29783');
        $respLainnya->assertDontSee('GAD.APL.HP001');
    }

    public function test_dashboard_sales_categorizes_acc_as_lainnya(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // GAD Apple
        CrossSelling::create([
            'customer_vendor_code' => 'C001',
            'item_code' => 'GAD.APL.HP001',
            'brand' => 'APL',
            'quantity' => 2,
            'harga_jual' => 40000000,
        ]);

        // ACC Apple
        CrossSelling::create([
            'customer_vendor_code' => 'C002',
            'item_code' => 'ACC_APL.SLK29783',
            'brand' => 'APL',
            'quantity' => 5,
            'harga_jual' => 1250000,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();

        $brandSales = $response->viewData('brandSales');
        $appleSale = collect($brandSales)->firstWhere('brand', 'IPHONE');
        $lainnyaSale = collect($brandSales)->firstWhere('brand', 'LAINNYA');

        $this->assertEquals(2, $appleSale['quantity'] ?? 0);
        $this->assertEquals(5, $lainnyaSale['quantity'] ?? 0);
    }
}
