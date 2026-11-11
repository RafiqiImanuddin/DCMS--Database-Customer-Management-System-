<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RouteCipher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRegistrationAndRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_registration_page(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Baru');
        $response->assertSee('Customer Data Management System');
        $response->assertSee('Nama Lengkap');
        $response->assertSee('Alamat Email');
        $response->assertSee('Kata Sandi');
        $response->assertSee('Konfirmasi Sandi');
    }

    public function test_authenticated_user_is_redirected_away_from_registration_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_can_register_with_valid_data_and_gets_default_role(): void
    {
        $payload = [
            'name' => 'Ahmad Rizki',
            'email' => 'ahmad.rizki@gadgetmart.id',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->post(route('register.store'), $payload);

        $response->assertRedirect(route('register.otp.form'));

        $pending = session('pending_registration');
        $this->assertNotNull($pending);
        $otp = $pending['otp'];

        $verifyResponse = $this->post(route('register.otp.verify'), [
            'otp' => $otp,
        ]);

        $verifyResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'ahmad.rizki@gadgetmart.id')->first();
        $this->assertNotNull($user);
        $this->assertSame('Ahmad Rizki', $user->name);
        $this->assertSame('user', $user->role);
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isDefaultUser());
    }

    public function test_registration_validation_fails_on_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@gadgetmart.id']);

        $response = $this->post(route('register.store'), [
            'name' => 'New Guy',
            'email' => 'existing@gadgetmart.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_registration_validation_fails_on_unconfirmed_password(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'New Guy',
            'email' => 'newguy@gadgetmart.id',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_default_user_can_access_customer_outreach_workspace_and_guidance(): void
    {
        $defaultUser = User::factory()->create(['role' => 'user']);

        // 1. Dashboard
        $this->actingAs($defaultUser)->get(route('dashboard'))->assertStatus(200);

        // 2. Predictions / Master Customer
        $this->actingAs($defaultUser)->get(route('predictions.index'))->assertStatus(200);

        // 3. Leads Customer
        $this->actingAs($defaultUser)->get(route('leads.index'))->assertStatus(200);

        // 4. Broadcasts
        $this->actingAs($defaultUser)->get(route('broadcasts.index'))->assertStatus(200);

        // 5. Workspace Sosmed (Dashboard & Report Matrix)
        $this->actingAs($defaultUser)->get(route('workspace-sosmed.index'))->assertStatus(200);

        // 6. Guidance SOP
        $this->actingAs($defaultUser)->get(route('guidance.index'))->assertStatus(200);

        // 7. Profile Settings
        $this->actingAs($defaultUser)->get(route('profile.edit'))->assertStatus(200);
    }

    public function test_default_user_cannot_access_superadmin_and_admin_modules(): void
    {
        $defaultUser = User::factory()->create(['role' => 'user']);

        // 1. Pricelist (Superadmin only)
        $this->actingAs($defaultUser)->get(route('pricelist.index'))->assertStatus(403);

        // 2. Pusat Unggah Data / Imports (Superadmin only)
        $this->actingAs($defaultUser)->get(route('imports.index'))->assertStatus(403);

        // 3. Monitoring Sales (Admin & Superadmin only)
        $this->actingAs($defaultUser)->get(route('monitoring.sales.index'))->assertStatus(403);

        // 4. Setup WhatsApp (Admin & Superadmin only)
        $this->actingAs($defaultUser)->get(route('settings.watzap.index'))->assertStatus(403);

        // 5. Setting Karyawan (Admin & Superadmin only)
        $this->actingAs($defaultUser)->get(route('settings.karyawan.index'))->assertStatus(403);
    }

    public function test_default_user_can_update_own_profile(): void
    {
        $defaultUser = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'lama@gadgetmart.id',
            'role' => 'user',
        ]);

        $response = $this->actingAs($defaultUser)->put(route('profile.update'), [
            'name' => 'Nama Baru',
            'email' => 'baru@gadgetmart.id',
        ]);

        $response->assertSessionHas('success');
        $defaultUser->refresh();
        $this->assertSame('Nama Baru', $defaultUser->name);
        $this->assertSame('baru@gadgetmart.id', $defaultUser->email);
    }

    public function test_default_user_can_update_own_password(): void
    {
        $defaultUser = User::factory()->create([
            'password' => Hash::make('passwordLama123'),
            'role' => 'user',
        ]);

        // Wrong current password
        $failResponse = $this->actingAs($defaultUser)->put(route('profile.password'), [
            'current_password' => 'wrongpassword',
            'password' => 'passwordBaru123',
            'password_confirmation' => 'passwordBaru123',
        ]);
        $failResponse->assertSessionHasErrors(['current_password']);

        // Correct current password
        $successResponse = $this->actingAs($defaultUser)->put(route('profile.password'), [
            'current_password' => 'passwordLama123',
            'password' => 'passwordBaru123',
            'password_confirmation' => 'passwordBaru123',
        ]);
        $successResponse->assertSessionHas('success');

        $defaultUser->refresh();
        $this->assertTrue(Hash::check('passwordBaru123', $defaultUser->password));
    }

    public function test_route_cipher_encrypted_routing_works_for_profile_setting(): void
    {
        $defaultUser = User::factory()->create(['role' => 'user']);

        $encryptedUrl = RouteCipher::url('profile.edit');
        $this->assertStringContainsString('/r/', $encryptedUrl);

        $response = $this->actingAs($defaultUser)->get($encryptedUrl);
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun');
    }
}
