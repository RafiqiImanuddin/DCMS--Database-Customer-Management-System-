<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\WorkspaceUrlCipher;
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
        $response->assertSee(WorkspaceUrlCipher::url(1));
        $response->assertSee(WorkspaceUrlCipher::url(9));
        $response->assertSee('Workspace Sosmed');
        $response->assertSee('Dashboard Sosmed');
        $response->assertSee('Report Matrix');
        $response->assertSee('Cek Status AR');
        $response->assertSee('Format Keep');
        $response->assertSee('Tracking AR');
        $response->assertSee('Klaim AR');
        $response->assertSee('Inbox Follow-Up');
    }

    public function test_raw_numeric_tab_redirects_to_encrypted_routing(): void
    {
        $user = User::factory()->create();

        // /workspace-sosmed?tab=9 harus otomatis diredirect ke URL terenkripsi
        $response = $this->actingAs($user)->get('/workspace-sosmed?tab=9');
        $response->assertRedirect(WorkspaceUrlCipher::url(9));
    }

    public function test_encrypted_routing_renders_target_tab(): void
    {
        $user = User::factory()->create();

        // Mengakses dengan URL terenkripsi
        $response = $this->actingAs($user)->get(WorkspaceUrlCipher::url(9));
        $response->assertStatus(200);
        $response->assertSee('Workspace Sosmed');
        $response->assertSee('Kembali ke DCMS');
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

    public function test_top_navbar_is_removed_for_both_superadmin_and_staff_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superadmin()->create();

        // 1. Staff Admin
        $resAdmin = $this->actingAs($admin)->get(route('workspace-sosmed.index'));
        $resAdmin->assertOk();
        $resAdmin->assertDontSee('header-pill-nav');
        $resAdmin->assertDontSee('tabBtn1');
        $resAdmin->assertDontSee('tabBtn9');
        $resAdmin->assertSee('Workspace Sosmed');
        $resAdmin->assertSee('Dashboard Sosmed');
        $resAdmin->assertSee('Inbox Follow-Up');

        // 2. Super Admin
        $resSuper = $this->actingAs($superadmin)->get(route('workspace-sosmed.index'));
        $resSuper->assertOk();
        $resSuper->assertDontSee('header-pill-nav');
        $resSuper->assertDontSee('tabBtn1');
        $resSuper->assertDontSee('tabBtn9');
        $resSuper->assertSee('Workspace Sosmed');
        $resSuper->assertSee('Dashboard Sosmed');
        $resSuper->assertSee('Inbox Follow-Up');
    }
}

