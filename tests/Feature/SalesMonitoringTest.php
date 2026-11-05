<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\KpiCriterion;
use App\Models\SalesAssessment;
use App\Models\User;
use App\Support\RouteCipher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'superadmin',
        ]);
    }

    public function test_manager_can_access_monitoring_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('monitoring.sales.index'));

        $response->assertStatus(200);
        $response->assertSee('Monitoring Kualitas Kerja Admin Sales');
        $response->assertSee('Rata-rata Skor Tim');
        $response->assertSee('Admin Sales Aktif');
        $response->assertSee('Top Performer');
        $response->assertSee('Perlu Coaching');
    }

    public function test_manager_can_filter_monitoring_by_cabang(): void
    {
        $response = $this->actingAs($this->user)->get(route('monitoring.sales.index', [
            'cabang' => 'AMTC',
            'period' => '2026-11',
        ]));

        $response->assertStatus(200);
        $response->assertSee('AMTC');
    }

    public function test_manager_can_store_and_update_assessment(): void
    {
        $employee = Employee::first() ?? Employee::create([
            'nik' => 'TEST-01',
            'name' => 'Tester Sales',
            'role' => 'Admin Sales',
            'cabang' => 'AMTC',
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        $payload = [
            'employee_id' => $employee->id,
            'period' => '2026-11',
            'scores' => [
                'resp_time' => 95,
                'broadcast_vol' => 90,
                'closing_rate' => 92,
                'crm_discipline' => 88,
                'csat_quality' => 94,
            ],
            'evaluator_name' => 'Manager Toko',
            'notes' => 'Performa sangat bagus dan tanggap',
            'recommendations' => 'Tingkatkan kuota broadcast bulan depan',
        ];

        $response = $this->actingAs($this->user)->post(route('monitoring.sales.assessment.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales_assessments', [
            'employee_id' => $employee->id,
            'period' => '2026-11',
            'grade' => 'A',
            'evaluator_name' => 'Manager Toko',
        ]);
    }

    public function test_manager_can_fetch_assessment_json(): void
    {
        $assessment = SalesAssessment::first();
        if (! $assessment) {
            $employee = Employee::first();
            $assessment = SalesAssessment::create([
                'employee_id' => $employee->id,
                'period' => '2026-11',
                'final_score' => 88.0,
                'grade' => 'B',
                'scores' => ['resp_time' => 88],
            ]);
        }

        $response = $this->actingAs($this->user)->get(route('monitoring.sales.assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'assessment' => [
                'id' => $assessment->id,
            ],
        ]);
    }

    public function test_manager_can_auto_sync_metrics(): void
    {
        $employee = Employee::first();

        $response = $this->actingAs($this->user)->post(route('monitoring.sales.auto_sync'), [
            'employee_id' => $employee->id,
            'period' => '2026-11',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'suggested_scores',
            'message',
        ]);
    }

    public function test_manager_can_export_csv(): void
    {
        $response = $this->actingAs($this->user)->get(route('monitoring.sales.export', [
            'period' => '2026-11',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_access_employee_settings(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.karyawan.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Karyawan');
        $response->assertSee('Data Karyawan Sales');
        $response->assertSee('Kriteria Penilaian');
    }

    public function test_admin_can_create_and_update_employee(): void
    {
        $createPayload = [
            'nik' => 'NEW-SLS-99',
            'name' => 'Karyawan Baru',
            'role' => 'Admin Sales',
            'cabang' => 'BJMA',
            'phone' => '089988776655',
            'email' => 'baru@gadgetmart.id',
            'status' => 'active',
            'notes' => 'Sales trainee',
        ];

        $response = $this->actingAs($this->user)->post(route('settings.karyawan.store'), $createPayload);
        $response->assertRedirect();
        $this->assertDatabaseHas('employees', ['nik' => 'NEW-SLS-99', 'name' => 'Karyawan Baru']);

        $employee = Employee::where('nik', 'NEW-SLS-99')->firstOrFail();

        $updatePayload = array_merge($createPayload, [
            'name' => 'Karyawan Baru Updated',
            'phone' => '089988776600',
        ]);

        $updateResponse = $this->actingAs($this->user)->put(route('settings.karyawan.update', $employee->id), $updatePayload);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('employees', ['nik' => 'NEW-SLS-99', 'name' => 'Karyawan Baru Updated']);
    }

    public function test_admin_cannot_save_weights_unless_sum_is_100_percent(): void
    {
        $criteria = KpiCriterion::all();
        $weights = [];
        $actives = [];

        foreach ($criteria as $c) {
            $weights[$c->id] = 10; // Sum < 100%
            $actives[$c->id] = '1';
        }

        $response = $this->actingAs($this->user)->post(route('settings.kpi_weights.update'), [
            'weights' => $weights,
            'actives' => $actives,
        ]);

        $response->assertSessionHas('error');

        // Now test valid 100% sum
        $count = $criteria->count();
        $evenWeight = round(100.0 / $count, 2);
        $remainder = 100.0 - ($evenWeight * ($count - 1));

        $validWeights = [];
        $i = 0;
        foreach ($criteria as $c) {
            $validWeights[$c->id] = ($i === $count - 1) ? $remainder : $evenWeight;
            $i++;
        }

        $validResponse = $this->actingAs($this->user)->post(route('settings.kpi_weights.update'), [
            'weights' => $validWeights,
            'actives' => $actives,
        ]);

        $validResponse->assertSessionHas('success');
    }

    public function test_admin_can_add_custom_kpi_criterion(): void
    {
        $payload = [
            'name' => 'Disiplin Standby Shift',
            'code' => 'shift_standby',
            'weight' => 5,
            'target_benchmark' => 'Hadir 10 menit sebelum shift',
            'description' => 'Ketepatan waktu membuka channel CS di jam operasional',
        ];

        $response = $this->actingAs($this->user)->post(route('settings.kpi_criteria.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('kpi_criteria', ['code' => 'shift_standby']);
    }

    public function test_regular_admin_cannot_create_update_or_delete_employee(): void
    {
        $regularAdmin = User::factory()->create([
            'role' => 'admin',
        ]);

        $employee = Employee::first();

        // Attempt create
        $this->actingAs($regularAdmin)->post(route('settings.karyawan.store'), [
            'nik' => 'FAIL-01',
            'name' => 'Hacker',
            'role' => 'Admin Sales',
            'cabang' => 'Head Office',
            'phone' => '08123456789',
            'status' => 'active',
        ])->assertStatus(403);

        // Attempt update
        $this->actingAs($regularAdmin)->put(route('settings.karyawan.update', $employee->id), [
            'nik' => $employee->nik,
            'name' => 'Changed Name',
            'role' => 'Admin Sales',
            'cabang' => 'Head Office',
            'phone' => '08123456789',
            'status' => 'active',
        ])->assertStatus(403);

        // Attempt delete
        $this->actingAs($regularAdmin)->delete(route('settings.karyawan.destroy', $employee->id))
            ->assertStatus(403);
    }

    public function test_regular_admin_cannot_update_kpi_weights(): void
    {
        $regularAdmin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($regularAdmin)->post(route('settings.kpi_weights.update'), [
            'weights' => [1 => 100],
        ])->assertStatus(403);
    }

    public function test_default_branches_include_head_office(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.karyawan.index'));
        $response->assertStatus(200);
        $response->assertSee('Head Office');
    }

    public function test_route_cipher_encrypted_access_to_new_pages(): void
    {
        $monitoringUrl = RouteCipher::url('monitoring.sales.index');
        $settingUrl = RouteCipher::url('settings.karyawan.index');

        $this->actingAs($this->user)->get($monitoringUrl)->assertStatus(200);
        $this->actingAs($this->user)->get($settingUrl)->assertStatus(200);
    }

    public function test_manager_can_store_daily_assessment_with_four_operational_criteria(): void
    {
        $employee = Employee::first() ?? Employee::create([
            'nik' => 'DAILY-01',
            'name' => 'Sales Harian',
            'role' => 'Admin Sales',
            'cabang' => 'Head Office',
            'phone' => '081234567891',
            'status' => 'active',
        ]);

        $payload = [
            'employee_id' => $employee->id,
            'period' => '2026-11-05',
            'scores' => [
                'chat_reply' => 95,
                'resp_time' => 90,
                'ethics' => 96,
                'promo_check' => 92,
            ],
            'evaluator_name' => 'Manager Gadgetmart',
            'notes' => 'Cara balas chat sangat solutif dan promo aktif berhasil disampaikan',
            'recommendations' => 'Pertahankan konsistensi respon cepat',
        ];

        $response = $this->actingAs($this->user)->postJson(route('monitoring.sales.assessment.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'final_score' => 93.25,
            'grade' => 'A',
        ]);

        $this->assertDatabaseHas('sales_assessments', [
            'employee_id' => $employee->id,
            'period' => '2026-11-05',
            'grade' => 'A',
            'evaluator_name' => 'Manager Gadgetmart',
        ]);
    }

    public function test_smart_diagnostic_and_whatsapp_format_generation(): void
    {
        $employee = Employee::first();
        $diagnostic = SalesAssessment::generateSmartDiagnostic(
            $employee,
            '2026-11-05',
            93.25,
            'A',
            [
                'chat_reply' => 95,
                'resp_time' => 90,
                'ethics' => 96,
                'promo_check' => 92,
            ],
            'Catatan performa luar biasa',
            'Tingkatkan follow-up leads sore'
        );

        $this->assertNotEmpty($diagnostic['strengths']);
        $this->assertStringContainsString('RAPOR EVALUASI HARIAN ADMIN SALES', $diagnostic['whatsapp_text']);
        $this->assertStringContainsString('Cara Balas Chat: 95/100', $diagnostic['whatsapp_text']);
        $this->assertStringContainsString('Respon Time: 90/100', $diagnostic['whatsapp_text']);
        $this->assertStringContainsString('Etika & Keramahan: 96/100', $diagnostic['whatsapp_text']);
        $this->assertStringContainsString('Kesesuaian Promo: 92/100', $diagnostic['whatsapp_text']);
        $this->assertStringContainsString('Catatan performa luar biasa', $diagnostic['whatsapp_text']);
    }

    public function test_manager_can_filter_monitoring_by_daily_date(): void
    {
        $response = $this->actingAs($this->user)->get(route('monitoring.sales.index', [
            'date' => '2026-11-05',
        ]));

        $response->assertStatus(200);
        $response->assertSee('2026-11-05');
        $response->assertSee('Cara Balas Chat');
        $response->assertSee('Respon Time');
        $response->assertSee('Etika');
        $response->assertSee('Kepatuhan Promo');
    }
}
