<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceSosmedIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_sosmed_requires_auth(): void
    {
        $response = $this->get(route('workspace-sosmed.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_workspace_sosmed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workspace-sosmed.index'));
        $response->assertStatus(200);
        $response->assertSee('Workspace Sosmed');
        $response->assertSee('Dashboard');
        $response->assertSee('Kembali ke DCMS');
    }

    public function test_sidebar_contains_workspace_sosmed_link(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee(route('workspace-sosmed.index'));
        $response->assertSee('Workspace Sosmed');
    }

    public function test_database_status_api_endpoint(): void
    {
        $response = $this->get(route('api.sosmed.database_status'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'turnover',
            'croselling',
            'keep',
            'archives',
            'targets',
            'photos',
            'serverTime',
        ]);
    }

    public function test_monthly_targets_api_endpoint(): void
    {
        $response = $this->get(route('api.sosmed.monthly_targets'));
        $response->assertStatus(200);
    }

    public function test_sales_photos_api_endpoint(): void
    {
        $response = $this->get(route('api.sosmed.sales_photos'));
        $response->assertStatus(200);
    }
}
