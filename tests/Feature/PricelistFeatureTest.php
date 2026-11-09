<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricelistFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_pricelist(): void
    {
        $response = $this->get(route('pricelist.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_superadmin_cannot_access_pricelist(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('pricelist.index'));
        $response->assertStatus(403);
    }

    public function test_superadmin_can_access_pricelist_view(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->get(route('pricelist.index'));
        $response->assertStatus(200);
        $response->assertSee('Pricelist & Skema Gadget');
        $response->assertSee('HARGA FIX');
        $response->assertSee('Update Harga - SM');
        $response->assertSee('HP Xiaomi Store');
        $response->assertSee('Modal By IMEI');
    }
}
