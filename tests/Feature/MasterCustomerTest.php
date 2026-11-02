<?php

namespace Tests\Feature;

use App\Models\RepeatOrderPrediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_master_customer_page(): void
    {
        $user = User::factory()->create();

        RepeatOrderPrediction::create([
            'customer_vendor_code' => 'CUST-001',
            'customer_vendor_name' => 'Pelanggan Test',
            'no_hp_customer' => '628123456789',
            'item_code' => 'ITEM-001',
            'classification' => 'tinggi',
        ]);

        $this->actingAs($user)
            ->get('/predictions')
            ->assertOk()
            ->assertSee('Master Customer');
    }

    public function test_master_customer_phone_filters_return_successful_response(): void
    {
        $user = User::factory()->create();

        foreach ([['valid', '628123456789'], ['invalid', '08123456789'], ['missing', null]] as $phoneStatus) {
            [$label, $phone] = $phoneStatus;

            RepeatOrderPrediction::create([
                'customer_vendor_code' => 'CUST-'.$label,
                'customer_vendor_name' => 'Pelanggan '.$label,
                'no_hp_customer' => $phone,
                'item_code' => 'ITEM-'.$label,
                'classification' => 'baru',
            ]);
        }

        foreach (['valid', 'invalid', 'missing'] as $phoneStatus) {
            $this->actingAs($user)
                ->get('/predictions?phone_status='.$phoneStatus)
                ->assertOk();
        }
    }
}
