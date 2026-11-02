<?php

namespace Tests\Feature;

use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\RepeatOrderPrediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrossSellingImportDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_render_import_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('imports.index'));

        $response->assertOk();
        $response->assertSee('Import Cross-selling');
        $response->assertSee('Belum ada import.');
    }

    public function test_owner_can_delete_import_and_related_data(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $import = CrossSellingImport::create([
            'user_id' => $user->id,
            'original_name' => 'penjualan.xlsx',
            'stored_path' => 'cross-selling-imports/penjualan.xlsx',
            'status' => 'completed',
        ]);
        Storage::disk('local')->put($import->stored_path, 'test');
        CrossSelling::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'C001',
            'customer_vendor_name' => 'Pelanggan Potensial',
            'posting_date' => '2026-10-01',
            'period_label' => '2026-10',
        ]);
        $prediction = RepeatOrderPrediction::create([
            'cross_selling_import_id' => $import->id,
            'customer_vendor_code' => 'C001',
            'customer_vendor_name' => 'Pelanggan Potensial',
            'classification' => 'tinggi',
            'confidence' => 90,
        ]);

        $response = $this->actingAs($user)->delete(route('imports.destroy', $import));

        $response->assertRedirect(route('imports.index'));
        $response->assertSessionHas('success', 'Data import berhasil dihapus.');
        $this->assertModelMissing($import);
        $this->assertDatabaseMissing('cross_sellings', ['cross_selling_import_id' => $import->id]);
        $this->assertDatabaseMissing('repeat_order_predictions', ['id' => $prediction->id]);
        Storage::disk('local')->assertMissing($import->stored_path);
    }

    public function test_user_cannot_delete_another_users_import(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $import = CrossSellingImport::create([
            'user_id' => $owner->id,
            'original_name' => 'penjualan.xlsx',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($otherUser)->delete(route('imports.destroy', $import));

        $response->assertNotFound();
        $this->assertModelExists($import);
    }
}
