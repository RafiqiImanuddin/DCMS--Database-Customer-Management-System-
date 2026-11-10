<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RouteCipher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionChecklistTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_user_management_and_checklist_page(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)->get(route('settings.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelola Akun & Hak Akses Fitur');
        $response->assertSee('Tambah Akun Baru');
        $response->assertSee('Atur Checklist Fitur');
    }

    public function test_non_superadmin_cannot_access_user_management_page(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)->get(route('settings.users.index'))->assertStatus(403);
        $this->actingAs($staff)->get(route('settings.users.index'))->assertStatus(403);
    }

    public function test_superadmin_can_update_user_permissions_using_checklist(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $staff = User::factory()->create([
            'name' => 'Staf Operasional',
            'role' => 'user',
            'permissions' => null,
        ]);

        // Default staff cannot access pricelist
        $this->assertFalse($staff->canAccessFeature('pricelist'));

        // Superadmin gives access to pricelist and workspace_cek_ar, but revokes broadcasts
        $payload = [
            'role' => 'user',
            'permissions' => ['dashboard', 'predictions', 'pricelist', 'workspace_cek_ar'],
        ];

        $response = $this->actingAs($superadmin)->put(route('settings.users.permissions.update', $staff), $payload);

        $response->assertSessionHas('success');
        $staff->refresh();

        $this->assertSame(['dashboard', 'predictions', 'pricelist', 'workspace_cek_ar'], $staff->permissions);
        $this->assertTrue($staff->canAccessFeature('pricelist'));
        $this->assertTrue($staff->canAccessFeature('workspace_cek_ar'));
        $this->assertFalse($staff->canAccessFeature('broadcasts'));

        // Staff can now access pricelist page!
        $this->actingAs($staff)->get(route('pricelist.index'))->assertStatus(200);
    }

    public function test_superadmin_cannot_restrict_superadmin_permissions(): void
    {
        $superadmin1 = User::factory()->superadmin()->create();
        $superadmin2 = User::factory()->superadmin()->create(['name' => 'Boss 2']);

        $response = $this->actingAs($superadmin1)->put(route('settings.users.permissions.update', $superadmin2), [
            'permissions' => ['dashboard'],
        ]);

        $response->assertSessionHas('error');
        $this->assertTrue($superadmin2->fresh()->canAccessFeature('pricelist'));
    }

    public function test_superadmin_can_reset_user_permissions_to_role_default(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $admin = User::factory()->admin()->create([
            'permissions' => ['dashboard'], // previously restricted
        ]);

        $response = $this->actingAs($superadmin)->put(route('settings.users.permissions.update', $admin), [
            'action' => 'reset_default',
        ]);

        $response->assertSessionHas('success');
        $admin->refresh();

        $this->assertNull($admin->permissions);
        $this->assertTrue($admin->canAccessFeature('monitoring_sales'));
        $this->assertTrue($admin->canAccessFeature('watzap_settings'));
    }

    public function test_superadmin_can_create_user_with_assigned_permissions(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $payload = [
            'name' => 'Doni Sales',
            'email' => 'doni@gadgetmart.id',
            'role' => 'admin',
            'password' => 'secret12345',
            'permissions' => ['dashboard', 'monitoring_sales', 'workspace_dashboard'],
        ];

        $response = $this->actingAs($superadmin)->post(route('settings.users.store'), $payload);

        $response->assertSessionHas('success');

        $user = User::where('email', 'doni@gadgetmart.id')->first();
        $this->assertNotNull($user);
        $this->assertSame('Doni Sales', $user->name);
        $this->assertSame(['dashboard', 'monitoring_sales', 'workspace_dashboard'], $user->permissions);
    }

    public function test_route_cipher_supports_encrypted_url_for_settings_users(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $encryptedUrl = RouteCipher::url('settings.users.index');
        $this->assertStringContainsString('/r/', $encryptedUrl);

        $response = $this->actingAs($superadmin)->get($encryptedUrl);
        $response->assertStatus(200);
        $response->assertSee('Kelola Akun & Hak Akses Fitur');
    }
}
