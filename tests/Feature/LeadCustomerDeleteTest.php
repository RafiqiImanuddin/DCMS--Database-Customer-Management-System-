<?php

namespace Tests\Feature;

use App\Models\LeadCustomer;
use App\Models\RepeatOrderPrediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadCustomerDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_leads_page_renders_single_and_bulk_delete_buttons(): void
    {
        $user = User::factory()->create();

        $pred = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C99001',
            'customer_vendor_name' => 'Budi Santoso',
            'no_hp_customer' => '6281234567890',
            'item_code' => 'GAD.A55',
            'dscription' => 'Samsung Galaxy A55',
            'classification' => 'tinggi',
        ]);

        $lead = LeadCustomer::create([
            'user_id' => $user->id,
            'repeat_order_prediction_id' => $pred->id,
            'customer_vendor_code' => 'C99001',
            'customer_vendor_name' => 'Budi Santoso',
            'no_hp_customer' => '6281234567890',
            'classification' => 'tinggi',
            'item_code' => 'GAD.A55',
            'dscription' => 'Samsung Galaxy A55',
            'status' => 'follow_up',
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('leads.index'));

        $response->assertOk();
        $response->assertSee('Hapus');
        $response->assertSee('Hapus <span id="lead-bulk-delete-count">(0)</span> Lead Terpilih', false);
        $response->assertSee('confirmDeleteSingleLead');
        $response->assertSee('confirmBulkDeleteLeads');
    }

    public function test_user_can_delete_single_lead_and_it_returns_to_master_customer(): void
    {
        $user = User::factory()->create();

        $prediction = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C000429',
            'customer_vendor_name' => 'SRI C000429',
            'no_hp_customer' => '6281528758281',
            'item_code' => 'ACC.UNILLND0026',
            'dscription' => 'PELINDUNG KABEL SPIRAL',
            'classification' => 'tinggi',
        ]);

        $lead = LeadCustomer::create([
            'user_id' => $user->id,
            'repeat_order_prediction_id' => $prediction->id,
            'customer_vendor_code' => 'C000429',
            'customer_vendor_name' => 'SRI C000429',
            'no_hp_customer' => '6281528758281',
            'classification' => 'tinggi',
            'item_code' => 'ACC.UNILLND0026',
            'dscription' => 'PELINDUNG KABEL SPIRAL',
            'status' => 'follow_up',
            'assigned_at' => now(),
        ]);

        // While in Leads, Master Customer hides this prediction
        $masterBefore = $this->actingAs($user)->get(route('predictions.index'));
        $masterBefore->assertOk();
        $masterBefore->assertDontSee('SRI C000429');

        // Leads Customer shows this lead
        $leadsBefore = $this->actingAs($user)->get(route('leads.index'));
        $leadsBefore->assertOk();
        $leadsBefore->assertSee('SRI C000429');

        // Delete the lead
        $deleteResponse = $this->actingAs($user)->delete(route('leads.destroy', $lead));
        $deleteResponse->assertRedirect(route('leads.index'));
        $deleteResponse->assertSessionHas('success');

        // Assert lead is deleted from lead_customers table
        $this->assertDatabaseMissing('lead_customers', [
            'id' => $lead->id,
        ]);

        // Assert prediction still safely exists in repeat_order_predictions
        $this->assertDatabaseHas('repeat_order_predictions', [
            'id' => $prediction->id,
            'customer_vendor_code' => 'C000429',
        ]);

        // Leads page no longer shows this lead item in table
        $leadsAfter = $this->actingAs($user)->get(route('leads.index'));
        $leadsAfter->assertOk();
        $leadsAfter->assertDontSee('PELINDUNG KABEL SPIRAL');
        $leadsAfter->assertSee('Belum ada Leads Customer');

        // Master Customer NOW SHOWS this customer again (returned to master customer)!
        $masterAfter = $this->actingAs($user)->get(route('predictions.index'));
        $masterAfter->assertOk();
        $masterAfter->assertSee('SRI C000429');
    }

    public function test_user_can_bulk_delete_leads_and_they_return_to_master_customer(): void
    {
        $user = User::factory()->create();

        $pred1 = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C101',
            'customer_vendor_name' => 'Lead Bulk 1',
            'no_hp_customer' => '628111111111',
            'item_code' => 'ITEM1',
            'classification' => 'tinggi',
        ]);

        $pred2 = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C102',
            'customer_vendor_name' => 'Lead Bulk 2',
            'no_hp_customer' => '628222222222',
            'item_code' => 'ITEM2',
            'classification' => 'tinggi',
        ]);

        $lead1 = LeadCustomer::create([
            'user_id' => $user->id,
            'repeat_order_prediction_id' => $pred1->id,
            'customer_vendor_code' => 'C101',
            'customer_vendor_name' => 'Lead Bulk 1',
            'status' => 'follow_up',
            'assigned_at' => now(),
        ]);

        $lead2 = LeadCustomer::create([
            'user_id' => $user->id,
            'repeat_order_prediction_id' => $pred2->id,
            'customer_vendor_code' => 'C102',
            'customer_vendor_name' => 'Lead Bulk 2',
            'status' => 'follow_up',
            'assigned_at' => now(),
        ]);

        // Bulk delete both leads
        $response = $this->actingAs($user)->delete(route('leads.bulkDestroy'), [
            'lead_ids' => [$lead1->id, $lead2->id],
        ]);

        $response->assertRedirect(route('leads.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('lead_customers', ['id' => $lead1->id]);
        $this->assertDatabaseMissing('lead_customers', ['id' => $lead2->id]);

        // Both predictions return to Master Customer
        $masterAfter = $this->actingAs($user)->get(route('predictions.index'));
        $masterAfter->assertOk();
        $masterAfter->assertSee('Lead Bulk 1');
        $masterAfter->assertSee('Lead Bulk 2');
    }

    public function test_user_cannot_delete_another_users_lead_unless_superadmin(): void
    {
        $owner = User::factory()->admin()->create();
        $otherUser = User::factory()->admin()->create();
        $superadmin = User::factory()->superadmin()->create();

        $pred = RepeatOrderPrediction::create([
            'customer_vendor_code' => 'C200',
            'customer_vendor_name' => 'Owner Lead',
            'classification' => 'tinggi',
        ]);

        $lead = LeadCustomer::create([
            'user_id' => $owner->id,
            'repeat_order_prediction_id' => $pred->id,
            'customer_vendor_code' => 'C200',
            'customer_vendor_name' => 'Owner Lead',
            'status' => 'follow_up',
            'assigned_at' => now(),
        ]);

        // Other user attempts to delete -> 403 Forbidden
        $this->actingAs($otherUser)
            ->delete(route('leads.destroy', $lead))
            ->assertStatus(403);

        $this->assertDatabaseHas('lead_customers', ['id' => $lead->id]);

        // Superadmin deletes -> Allowed
        $this->actingAs($superadmin)
            ->delete(route('leads.destroy', $lead))
            ->assertRedirect(route('leads.index'));

        $this->assertDatabaseMissing('lead_customers', ['id' => $lead->id]);
    }
}
