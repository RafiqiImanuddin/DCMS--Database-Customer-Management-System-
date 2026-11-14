<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ARClaimsSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $filePath = storage_path('app/ar_claims.json');
        $backupPath = storage_path('app/ar_claims.backup.json');

        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        if (file_exists($backupPath)) {
            @unlink($backupPath);
        }

        parent::tearDown();
    }

    public function test_ar_claims_requires_authentication(): void
    {
        $response = $this->getJson('/api/ar-claims');
        $response->assertStatus(401);

        $postResponse = $this->postJson('/api/ar-claims', [['id' => 'CLM-1']]);
        $postResponse->assertStatus(401);
    }

    public function test_authenticated_user_can_read_and_persist_ar_claims(): void
    {
        $user = User::factory()->create([
            'email' => 'admin_test@gadgetmart.co.id',
            'role' => 'admin',
        ]);

        $testData = [
            [
                'id' => 'CLM-TEST-001',
                'docNumber' => '1002345',
                'productName' => 'Samsung Galaxy S24 Ultra',
                'hargaJual' => 21999000,
                'targetSosmedCode' => 'BJM.SOS.01',
                'status' => 'COLLECTING',
                'isHandphone' => true,
                'penaltyAmount' => 50000,
            ],
        ];

        // 1. Simpan data klaim via POST
        $postResponse = $this->actingAs($user)->postJson('/api/ar-claims', $testData);
        $postResponse->assertStatus(200);
        $postResponse->assertJson([
            'success' => true,
            'count' => 1,
        ]);

        $this->assertFileExists(storage_path('app/ar_claims.json'));

        // 2. Baca kembali data klaim via GET
        $getResponse = $this->actingAs($user)->getJson('/api/ar-claims');
        $getResponse->assertStatus(200);
        $getResponse->assertJsonCount(1);
        $getResponse->assertJsonFragment([
            'id' => 'CLM-TEST-001',
            'docNumber' => '1002345',
            'productName' => 'Samsung Galaxy S24 Ultra',
        ]);
    }
}
