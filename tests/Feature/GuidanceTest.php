<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuidanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('guidance.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_superadmin_can_view_guidance_and_full_formulas(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)->get(route('guidance.index'));

        $response->assertOk();
        $response->assertSee('Rumus Sistem');
        $response->assertSee('ENGINE PREDIKSI REPEAT ORDER');
        $response->assertSee('PredictedDate = LastOrderDate + MedianGap');
        $response->assertSee('MAD = Median(|gapᵢ - MedianGap|)');
        $response->assertSee('Rumus &amp; Panduan', false);
        $response->assertSee('Rumus');
    }

    public function test_superadmin_can_view_kpi_assessment_formula_tab(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)->get(route('guidance.index', ['tab' => 'formula_penilaian']));

        $response->assertOk();
        $response->assertSee('Rumus &amp; Standar Penilaian Kinerja Admin Sales Harian', false);
        $response->assertSee('Total Skor Terbobot Harian');
        $response->assertSee('Cara Balas Chat Konsumen');
        $response->assertSee('GRADE A');
        $response->assertSee('GRADE D');
    }

    public function test_staff_admin_can_view_general_sop_only(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('guidance.index'));

        $response->assertOk();
        $response->assertSee('Panduan &amp; Standar Operasional Sales', false);
        $response->assertSee('Cara Balas Chat Konsumen');
        $response->assertSee('Respon Time &amp; Standby', false);
        $response->assertSee('Etika &amp; Keramahan 3S', false);
        $response->assertSee('Panduan &amp; SOP Sales', false);

        // Staff must NOT see internal math formulas
        $response->assertDontSee('ENGINE PREDIKSI REPEAT ORDER');
        $response->assertDontSee('PredictedDate = LastOrderDate + MedianGap');
        $response->assertDontSee('MAD = Median(|gapᵢ - MedianGap|)');
        $response->assertDontSee('Formula Total Skor Terbobot Harian');
    }

    public function test_staff_admin_cannot_access_formula_tabs_via_query_param(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('guidance.index', ['tab' => 'formula_potensi']));

        $response->assertOk();
        // Server overrides activeTab to sop_chat for non-superadmin
        $response->assertDontSee('ENGINE PREDIKSI REPEAT ORDER');
        $response->assertDontSee('PredictedDate = LastOrderDate + MedianGap');
        $response->assertSee('Standar Cara Balas Chat Konsumen');
    }

    public function test_login_triggers_guidance_modal_session_and_renders_popup(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'sales@gadgetmart.id',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'sales@gadgetmart.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(session('show_login_guidance') === true);

        // Follow redirect to dashboard and assert modal is present
        $dashboardResponse = $this->actingAs($user)
            ->withSession(['show_login_guidance' => true])
            ->get(route('dashboard'));
            
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('id="login-guidance-modal"', false);
        $dashboardResponse->assertSee('SOP Layanan Prima Sales Konsumen');
        $dashboardResponse->assertSee('Saya Mengerti');
    }

    public function test_superadmin_sees_executive_formula_modal_on_login(): void
    {
        $superadmin = User::factory()->superadmin()->create([
            'email' => 'manager@gadgetmart.id',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'manager@gadgetmart.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(session('show_login_guidance') === true);

        $dashboardResponse = $this->actingAs($superadmin)
            ->withSession(['show_login_guidance' => true])
            ->get(route('dashboard'));

        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('id="login-guidance-modal"', false);
        $dashboardResponse->assertSee('Ringkasan Rumus &amp; Algoritma DCMS', false);
        $dashboardResponse->assertSee('Superadmin Guidance &amp; Formula', false);
        $dashboardResponse->assertSee('Saya Mengerti &amp; Mulai Bekerja', false);
    }

    public function test_dismiss_guidance_prompt_clears_session(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->withSession(['show_login_guidance' => true])
            ->postJson(route('guidance.dismiss'));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertFalse(session()->has('show_login_guidance'));
    }
}
