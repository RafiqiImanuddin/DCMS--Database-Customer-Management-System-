<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RouteCipher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncryptedRouteFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_encrypted_route_requires_auth(): void
    {
        $url = RouteCipher::url('dashboard');
        $response = $this->get($url);
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard_via_encrypted_route(): void
    {
        $user = User::factory()->create();

        $url = RouteCipher::url('dashboard');
        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);
        $response->assertSee('DCMS');
        $response->assertSee('Selamat datang di DCMS Gadgetmart');
    }

    public function test_authenticated_user_can_access_predictions_master_customer_via_encrypted_route(): void
    {
        $user = User::factory()->create();

        $url = RouteCipher::url('predictions.index');
        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);
        $response->assertSee('Master Customer');
    }

    public function test_authenticated_user_can_access_leads_customer_via_encrypted_route(): void
    {
        $user = User::factory()->create();

        $url = RouteCipher::url('leads.index');
        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);
        $response->assertSee('Leads Customer');
    }

    public function test_authenticated_user_can_access_broadcast_report_via_encrypted_route(): void
    {
        $user = User::factory()->create();

        $url = RouteCipher::url('broadcasts.index');
        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);
        $response->assertSee('Laporan Broadcast');
    }

    public function test_authenticated_user_can_access_workspace_tabs_via_encrypted_route(): void
    {
        $user = User::factory()->create();

        // Tab 1 (Dashboard Sosmed)
        $url1 = RouteCipher::url('workspace-sosmed.index', ['tab' => 1]);
        $response1 = $this->actingAs($user)->get($url1);
        $response1->assertStatus(200);
        $response1->assertSee('Workspace Sosmed');

        // Tab 4 (Format Keep)
        $url4 = RouteCipher::url('workspace-sosmed.index', ['tab' => 4]);
        $response4 = $this->actingAs($user)->get($url4);
        $response4->assertStatus(200);
        $response4->assertSee('Format Keep Customer');
        $response4->assertSee('<title>DCMS - SOSMED</title>', false);
        
        $content4 = $response4->getContent();
        preg_match('/<section id="tabContent1"[^>]*class="([^"]*)"/', $content4, $m1);
        preg_match('/<section id="tabContent4"[^>]*class="([^"]*)"/', $content4, $m4);
        $this->assertStringNotContainsString('active', $m1[1] ?? '');
        $this->assertStringContainsString('active', $m4[1] ?? '');

        // Tab 9 (Inbox FU)
        $url9 = RouteCipher::url('workspace-sosmed.index', ['tab' => 9]);
        $response9 = $this->actingAs($user)->get($url9);
        $response9->assertStatus(200);
        $response9->assertSee('Workspace Sosmed');
    }

    public function test_sidebar_contains_encrypted_urls(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // Verify that sidebar links point to encrypted URLs (/r/)
        $response->assertSee(RouteCipher::url('dashboard'));
        $response->assertSee(RouteCipher::url('predictions.index'));
        $response->assertSee(RouteCipher::url('leads.index'));
        $response->assertSee(RouteCipher::url('broadcasts.index'));
        $response->assertSee(RouteCipher::url('workspace-sosmed.index', ['tab' => 1]));
        $response->assertSee(RouteCipher::url('workspace-sosmed.index', ['tab' => 9]));
    }

    public function test_invalid_encrypted_token_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/r/invalid-tampered-token-12345');
        $response->assertStatus(404);
    }
}
