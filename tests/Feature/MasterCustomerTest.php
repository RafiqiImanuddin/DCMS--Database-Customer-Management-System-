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

    public function test_master_customer_displays_dispatch_date_and_source_file(): void
    {
        $user = User::factory()->superadmin()->create();

        $import = \App\Models\CrossSellingImport::create([
            'user_id' => $user->id,
            'original_name' => 'SAP_HARIAN_05NOV.xlsx',
            'status' => 'completed',
        ]);

        RepeatOrderPrediction::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'CUST-TODAY',
            'customer_vendor_name' => 'Pelanggan Hari Ini',
            'no_hp_customer' => '628123456789',
            'item_code' => 'ITEM-001',
            'classification' => 'tinggi',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/predictions');
        $response->assertOk();
        $response->assertSee('Dispatch / Sumber File');
        $response->assertSee('SAP_HARIAN_05NOV.xlsx');
        $response->assertSee('Hari Ini');
        $response->assertSee('Perbarui Analisis AI');
    }

    public function test_superadmin_can_trigger_prediction_rebuild(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)->post(route('predictions.rebuild'));
        $response->assertRedirect(route('predictions.index'));
        $response->assertSessionHas('success');
    }

    public function test_master_customer_download_succeeds_with_dispatch_columns(): void
    {
        $user = User::factory()->create();

        $import = \App\Models\CrossSellingImport::create([
            'user_id' => $user->id,
            'original_name' => 'SAP_EXPORT_SOURCE.xlsx',
            'status' => 'completed',
        ]);

        RepeatOrderPrediction::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'CUST-EXP',
            'customer_vendor_name' => 'Pelanggan Export',
            'no_hp_customer' => '628123456789',
            'item_code' => 'ITEM-001',
            'classification' => 'tinggi',
        ]);

        $response = $this->actingAs($user)->get(route('predictions.download'));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
