<?php

namespace Tests\Feature;

use App\Models\RepeatOrderPrediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepeatOrderDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_repeat_order_dashboard(): void
    {
        $response = $this->get(route('repeat-orders.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_repeat_order_dashboard_redirects_to_master_customer(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('repeat-orders.dashboard'));
        $response->assertRedirect(route('predictions.index'));
    }

    public function test_authenticated_user_can_access_master_customer_with_repeat_order_intelligence(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'CUST001',
            'customer_vendor_name' => 'Budi Santoso',
            'no_hp_customer' => '628123456789',
            'cabang' => 'GM Banjarmasin',
            'item_code' => 'GAD.SAM.A55',
            'dscription' => 'Samsung Galaxy A55 5G',
            'transaction_count' => 3,
            'total_quantity' => 3,
            'total_value' => 15000000,
            'average_gap_days' => 45.5,
            'predicted_repeat_date' => now()->addDays(10),
            'confidence' => 85,
            'classification' => 'tinggi',
        ]);

        $response = $this->actingAs($user)->get(route('predictions.index'));

        $response->assertOk();
        $response->assertSee('Master Customer');
        $response->assertSee('Konsumen Repeat');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Samsung Galaxy A55 5G');
        $response->assertSee('46 hari'); // average_gap_days formatted
    }

    public function test_master_customer_repeat_order_filters(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);

        // Overdue prediction
        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'CUST_OVERDUE',
            'customer_vendor_name' => 'Pelanggan Overdue',
            'no_hp_customer' => '62811111111',
            'item_code' => 'GAD.XMI.01',
            'transaction_count' => 2,
            'average_gap_days' => 30,
            'predicted_repeat_date' => now()->subDays(5),
            'classification' => 'tinggi',
        ]);

        // Future prediction
        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'CUST_FUTURE',
            'customer_vendor_name' => 'Pelanggan Masa Depan',
            'no_hp_customer' => '62822222222',
            'item_code' => 'ACC.CASE.01',
            'transaction_count' => 4,
            'average_gap_days' => 90,
            'predicted_repeat_date' => now()->addDays(60),
            'classification' => 'sedang',
        ]);

        // Test overdue filter
        $respOverdue = $this->actingAs($user)->get(route('predictions.index', ['period' => 'overdue']));
        $respOverdue->assertOk();
        $respOverdue->assertSee('Pelanggan Overdue');
        $respOverdue->assertDontSee('Pelanggan Masa Depan');

        // Test category filter (ACC)
        $respCategory = $this->actingAs($user)->get(route('predictions.index', ['category' => 'ACC']));
        $respCategory->assertOk();
        $respCategory->assertSee('Pelanggan Masa Depan');
        $respCategory->assertDontSee('Pelanggan Overdue');
    }

    public function test_master_customer_can_be_filtered_by_cabang(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Cabang BJMC
        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'CUST_BJMC',
            'customer_vendor_name' => 'Pelanggan Banjarmasin',
            'no_hp_customer' => '628123456789',
            'cabang' => 'BJMC',
            'item_code' => 'GAD.SAM.A55',
            'transaction_count' => 3,
            'average_gap_days' => 45,
            'predicted_repeat_date' => now()->addDays(10),
            'classification' => 'tinggi',
        ]);

        // Cabang TJGC
        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'CUST_TJGC',
            'customer_vendor_name' => 'Pelanggan Tanjung',
            'no_hp_customer' => '628987654321',
            'cabang' => 'TJGC',
            'item_code' => 'GAD.APL.IP15',
            'transaction_count' => 2,
            'average_gap_days' => 60,
            'predicted_repeat_date' => now()->addDays(15),
            'classification' => 'sedang',
        ]);

        // Filter BJMC
        $responseBjmc = $this->actingAs($user)->get(route('predictions.index', ['cabang' => 'BJMC']));
        $responseBjmc->assertOk();
        $responseBjmc->assertSee('Pelanggan Banjarmasin');
        $responseBjmc->assertDontSee('Pelanggan Tanjung');
        $responseBjmc->assertSee('Fokus Cabang: BJMC');

        // Filter TJGC
        $responseTjgc = $this->actingAs($user)->get(route('predictions.index', ['cabang' => 'TJGC']));
        $responseTjgc->assertOk();
        $responseTjgc->assertSee('Pelanggan Tanjung');
        $responseTjgc->assertDontSee('Pelanggan Banjarmasin');
        $responseTjgc->assertSee('Fokus Cabang: TJGC');

        // Download filtered by Cabang
        $downloadResponse = $this->actingAs($user)->get(route('predictions.download', ['cabang' => 'BJMC']));
        $downloadResponse->assertOk();
        $disposition = (string) $downloadResponse->headers->get('content-disposition');
        $this->assertStringContainsString('cabang-bjmc', $disposition);
    }

    public function test_master_customer_displays_no_name_when_customer_name_is_empty(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C203111',
            'customer_vendor_name' => '',
            'no_hp_customer' => '6283823241594',
            'cabang' => 'BTCC',
            'item_code' => 'ACC.APL.SG00096',
            'transaction_count' => 1,
            'average_gap_days' => 0,
            'predicted_repeat_date' => now()->addDays(10),
            'classification' => 'baru',
        ]);

        $response = $this->actingAs($user)->get(route('predictions.index'));
        $response->assertOk();
        $response->assertSee('No Name');
        $response->assertDontSee('Kaka');
    }
}
