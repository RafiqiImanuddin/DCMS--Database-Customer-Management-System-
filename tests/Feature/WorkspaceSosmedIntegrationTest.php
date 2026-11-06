<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Setting;
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
        $response->assertDontSee('Kembali ke DCMS');
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

    public function test_sidebar_has_collapsible_groups(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('toggleSidebarGroup(\'customer\')', false);
        $response->assertSee('toggleSidebarGroup(\'workspace\')', false);
        $response->assertSee('sidebar-group-customer');
        $response->assertSee('sidebar-group-workspace');
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
        $response->assertDontSee('Kembali ke DCMS');
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
        $resAdmin->assertDontSee('Kembali ke DCMS');
        $resAdmin->assertSee('Workspace Sosmed');
        $resAdmin->assertSee('Dashboard Sosmed');
        $resAdmin->assertSee('Inbox Follow-Up');

        // 2. Super Admin
        $resSuper = $this->actingAs($superadmin)->get(route('workspace-sosmed.index'));
        $resSuper->assertOk();
        $resSuper->assertDontSee('header-pill-nav');
        $resSuper->assertDontSee('tabBtn1');
        $resSuper->assertDontSee('tabBtn9');
        $resSuper->assertDontSee('Kembali ke DCMS');
        $resSuper->assertSee('Workspace Sosmed');
        $resSuper->assertSee('Dashboard Sosmed');
        $resSuper->assertSee('Inbox Follow-Up');
    }

    public function test_superadmin_can_upload_cross_selling_chunks_and_sync(): void
    {
        $superadmin = User::factory()->superadmin()->create(['name' => 'Pak Bos Superadmin']);

        $chunk1 = 'DummyChunkContentPart1';
        $chunk2 = 'DummyChunkContentPart2';

        // Upload chunk 0 of 2
        $res1 = $this->actingAs($superadmin)->post(
            route('api.sosmed.upload_excel_chunk'),
            ['chunk' => $chunk1],
            [
                'X-Upload-Id' => 'test_sync_123',
                'X-Chunk-Index' => '0',
                'X-Total-Chunks' => '2',
                'X-File-Type' => 'croselling',
                'X-Original-Filename' => 'CROSSELLING-NEW.xlsx',
            ]
        );
        $res1->assertOk();
        $res1->assertJson(['completed' => false]);

        // Upload chunk 1 of 2 (finishing chunk)
        $res2 = $this->actingAs($superadmin)->post(
            route('api.sosmed.upload_excel_chunk'),
            ['chunk' => $chunk2],
            [
                'X-Upload-Id' => 'test_sync_123',
                'X-Chunk-Index' => '1',
                'X-Total-Chunks' => '2',
                'X-File-Type' => 'croselling',
                'X-Original-Filename' => 'CROSSELLING-NEW.xlsx',
            ]
        );
        $res2->assertOk();
        $res2->assertJson(['completed' => true, 'uploader' => 'Pak Bos Superadmin']);

        // Check database status reflects new mtime and uploader
        $statusRes = $this->get(route('api.sosmed.database_status'));
        $statusRes->assertOk();
        $status = $statusRes->json();
        $this->assertTrue($status['croselling']['exists']);
        $this->assertEquals('Pak Bos Superadmin', $status['croselling']['uploader']);

        // Get excel returns custom file
        $getRes = $this->get(route('api.sosmed.get_excel', ['type' => 'croselling']));
        $getRes->assertOk();
        $this->assertEquals('true', $getRes->headers->get('X-Custom-File'));
        $this->assertEquals('Pak Bos Superadmin', $getRes->headers->get('X-File-Uploader'));
    }

    public function test_staff_cannot_upload_cross_selling(): void
    {
        $staff = User::factory()->admin()->create(['name' => 'Staf Biasa']);

        $resChunk = $this->actingAs($staff)->post(
            route('api.sosmed.upload_excel_chunk'),
            ['chunk' => 'dummy'],
            [
                'X-Upload-Id' => 'staff_try',
                'X-Chunk-Index' => '0',
                'X-Total-Chunks' => '1',
            ]
        );
        $resChunk->assertStatus(403);

        $resSave = $this->actingAs($staff)->post(
            route('api.sosmed.save_excel', ['type' => 'croselling']),
            [],
            [],
            'fake binary content'
        );
        $resSave->assertStatus(403);
    }

    public function test_cek_ar_ui_role_adaptation(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $staff = User::factory()->admin()->create();

        // Superadmin sees upload button
        $superRes = $this->actingAs($superadmin)->get(WorkspaceUrlCipher::url(3));
        $superRes->assertOk();
        $superRes->assertSee('Akses Superadmin');
        $superRes->assertSee('Unggah File Cross Selling (.xlsx)');

        // Staff sees auto-sync indicator, not the upload button
        $staffRes = $this->actingAs($staff)->get(WorkspaceUrlCipher::url(3));
        $staffRes->assertOk();
        $staffRes->assertSee('Staf (Auto-Sync)');
        $staffRes->assertSee('Cek Sinkronisasi Server');
        $staffRes->assertDontSee('id="crosellingFileInput"', false);
    }

    public function test_workspace_targets_photos_archives_sync_to_settings(): void
    {
        $user = User::factory()->superadmin()->create();

        // 1. Monthly targets
        $targetPayload = ['Banjarmasin' => ['target' => 50000000]];
        $resTarget = $this->actingAs($user)->postJson(route('api.sosmed.monthly_targets'), $targetPayload);
        $resTarget->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('settings', ['key' => 'workspace_monthly_targets']);

        $resGetTarget = $this->actingAs($user)->getJson(route('api.sosmed.monthly_targets'));
        $resGetTarget->assertOk()->assertJson($targetPayload);

        // 2. Sales photos
        $photosPayload = ['Sales Ahmad' => 'data:image/png;base64,samplephoto'];
        $resPhoto = $this->actingAs($user)->postJson(route('api.sosmed.sales_photos'), $photosPayload);
        $resPhoto->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('settings', ['key' => 'workspace_sales_photos']);

        $resGetPhoto = $this->actingAs($user)->getJson(route('api.sosmed.sales_photos'));
        $resGetPhoto->assertOk()->assertJson($photosPayload);

        // 3. Monthly archives
        $archivesPayload = ['2026-10' => ['total' => 150000000]];
        $resArchive = $this->actingAs($user)->postJson(route('api.sosmed.monthly_archives'), $archivesPayload);
        $resArchive->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('settings', ['key' => 'workspace_monthly_archives']);

        $resGetArchive = $this->actingAs($user)->getJson(route('api.sosmed.monthly_archives'));
        $resGetArchive->assertOk()->assertJson($archivesPayload);
    }

    public function test_workspace_settings_api_returns_employees_and_matrix_weights(): void
    {
        Employee::create([
            'nik' => 'BJM.SOS.999',
            'name' => 'Budi Sosmed',
            'role' => 'Admin Sales',
            'target' => 999000000,
            'cabang' => 'BJMA',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        Setting::updateOrCreate(
            ['key' => 'workspace_matrix_weights'],
            ['value' => json_encode(['omzet' => 40.0, 'gp' => 30.0, 'cross_selling' => 30.0])]
        );

        $res = $this->getJson(route('api.sosmed.workspace_settings'));
        $res->assertOk();
        $res->assertJsonStructure([
            'matrix_weights' => ['omzet', 'gp', 'cross_selling'],
            'employees' => [
                '*' => ['id', 'code', 'nik', 'name', 'target', 'active', 'status', 'cabang', 'role'],
            ],
        ]);

        $this->assertEquals(40.0, $res->json('matrix_weights.omzet'));
        $this->assertEquals(30.0, $res->json('matrix_weights.gp'));
        $this->assertEquals(30.0, $res->json('matrix_weights.cross_selling'));
    }

    public function test_employee_settings_can_store_and_update_target(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        // 1. Store
        $storeRes = $this->actingAs($superadmin)->post(route('settings.karyawan.store'), [
            'nik' => 'SLS-NEW-01',
            'name' => 'New Sales Person',
            'role' => 'Admin Sales',
            'target' => 1250000000,
            'cabang' => 'Head Office',
            'phone' => '081999888777',
            'status' => 'active',
        ]);
        $storeRes->assertRedirect(route('settings.karyawan.index', ['tab' => 'employees']));
        $this->assertDatabaseHas('employees', [
            'nik' => 'SLS-NEW-01',
            'target' => 1250000000,
        ]);

        $emp = Employee::where('nik', 'SLS-NEW-01')->firstOrFail();

        // 2. Update
        $updateRes = $this->actingAs($superadmin)->put(route('settings.karyawan.update', $emp->id), [
            'nik' => 'SLS-NEW-01',
            'name' => 'New Sales Person Updated',
            'role' => 'Senior Sales',
            'target' => 1500000000,
            'cabang' => 'Head Office',
            'phone' => '081999888777',
            'status' => 'active',
        ]);
        $updateRes->assertRedirect(route('settings.karyawan.index', ['tab' => 'employees']));
        $this->assertDatabaseHas('employees', [
            'id' => $emp->id,
            'target' => 1500000000,
        ]);
    }

    public function test_employee_settings_can_update_matrix_weights(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        // Valid 100% matrix weights
        $res = $this->actingAs($superadmin)->post(route('settings.kpi_weights.update'), [
            'matrix_weights' => [
                'omzet' => 45.0,
                'gp' => 35.0,
                'cross_selling' => 20.0,
            ],
        ]);
        $res->assertRedirect(route('settings.karyawan.index', ['tab' => 'weights']));
        $res->assertSessionHas('success');

        $saved = json_decode(Setting::where('key', 'workspace_matrix_weights')->value('value'), true);
        $this->assertEquals(45.0, $saved['omzet']);
        $this->assertEquals(35.0, $saved['gp']);
        $this->assertEquals(20.0, $saved['cross_selling']);

        // Invalid matrix weights (sum != 100)
        $invalidRes = $this->actingAs($superadmin)->post(route('settings.kpi_weights.update'), [
            'matrix_weights' => [
                'omzet' => 50.0,
                'gp' => 50.0,
                'cross_selling' => 20.0, // total 120%
            ],
        ]);
        $invalidRes->assertRedirect(route('settings.karyawan.index', ['tab' => 'weights']));
        $invalidRes->assertSessionHas('error');
    }

    public function test_employee_setting_monthly_targets_tab_and_post_update(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $admin = User::factory()->admin()->create();

        $emp = Employee::firstOrCreate(
            ['nik' => 'BJM.SOS.005'],
            [
                'name' => 'Sifa',
                'role' => 'Sales',
                'cabang' => 'Banjarmasin',
                'phone' => '081234567890',
                'status' => 'active',
                'target' => 867261886.00,
            ]
        );

        // 1. Visit Tab 3: monthly_targets
        $response = $this->actingAs($superadmin)->get(route('settings.karyawan.index', [
            'tab' => 'monthly_targets',
            'target_month' => 11,
            'target_year' => 2026,
        ]));
        $response->assertOk();
        $response->assertSee('Target Omzet Periode November 2026');
        $response->assertSee('BJM.SOS.005');

        // 2. Superadmin updates monthly targets for November 2026
        $postRes = $this->actingAs($superadmin)->post(route('settings.monthly_targets.update'), [
            'target_month' => 11,
            'target_year' => 2026,
            'team_target' => '9.500.000.000',
            'sales_targets' => [
                'BJM.SOS.005' => '1.200.000.000',
            ],
        ]);
        $postRes->assertRedirect(route('settings.karyawan.index', [
            'tab' => 'monthly_targets',
            'target_month' => 11,
            'target_year' => 2026,
        ]));
        $postRes->assertSessionHas('success');

        // 3. Verify in database
        $saved = json_decode(Setting::where('key', 'workspace_monthly_targets')->value('value'), true);
        $this->assertIsArray($saved);
        $this->assertArrayHasKey('2026-11', $saved);
        $this->assertEquals(9500000000, $saved['2026-11']['teamTarget']);
        $this->assertEquals(1200000000, $saved['2026-11']['salesTargets']['BJM.SOS.005']);

        // 4. Verify workspace settings API returns monthly_targets
        $settingsApiRes = $this->actingAs($superadmin)->getJson(route('api.sosmed.workspace_settings'));
        $settingsApiRes->assertOk();
        $settingsData = $settingsApiRes->json();
        $this->assertArrayHasKey('monthly_targets', $settingsData);
        $this->assertArrayHasKey('2026-11', $settingsData['monthly_targets']);

        // 5. Staff admin is forbidden to update monthly targets
        $forbiddenRes = $this->actingAs($admin)->post(route('settings.monthly_targets.update'), [
            'target_month' => 11,
            'target_year' => 2026,
            'team_target' => '9.500.000.000',
        ]);
        $forbiddenRes->assertForbidden();
    }
}

