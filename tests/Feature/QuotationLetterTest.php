<?php

namespace Tests\Feature;

use App\Models\ActivityNotification;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationLetterTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $adminUser;
    private User $restrictedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'email' => 'superadmin@gmail.com',
            'role' => 'admin',
        ]);

        $this->adminUser = User::factory()->create([
            'email' => 'admin@gmail.com',
            'role' => 'admin',
            'permissions' => ['quotations', 'monitoring_sales'],
        ]);

        $this->restrictedUser = User::factory()->create([
            'email' => 'staff@gmail.com',
            'role' => 'user',
            'permissions' => ['dashboard'],
        ]);
    }

    public function test_superadmin_and_authorized_user_can_access_quotations_index(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('quotations.index'));
        $response->assertOk();
        $response->assertSee('Surat Penawaran Harga');
        $response->assertSee('Dashboard Manager');

        $responseAdmin = $this->actingAs($this->adminUser)->get(route('quotations.index'));
        $responseAdmin->assertOk();
    }

    public function test_restricted_user_cannot_access_quotations(): void
    {
        $response = $this->actingAs($this->restrictedUser)->get(route('quotations.index'));
        $response->assertForbidden();
    }

    public function test_can_create_quotation_and_logs_activity(): void
    {
        $postData = [
            'quotation_number' => 'SPH/GM/202611/001',
            'customer_name' => 'PT. Pelayaran Meratus',
            'customer_destination' => 'di-tempat',
            'letter_date' => '2026-11-10',
            'letter_city' => 'Banjarmasin',
            'greeting_text' => 'Dengan hormat, kami ingin memberikan penawaran harga.',
            'items' => [
                [
                    'description' => 'IPHONE 16 PRO MAX 256GB',
                    'price' => 24999000,
                    'qty' => 2,
                ],
                [
                    'description' => 'ADAPTOR 20W TYPE-C',
                    'price' => 450000,
                    'qty' => 2,
                ],
            ],
            'discount' => 1000000,
            'notes' => '*Harga yang tertera dapat berubah sewaktu-waktu',
            'closing_text' => 'Demikian penawaran yang kami sampaikan.',
            'signer_name' => 'RAFIQI IMANUDDIN',
            'signer_title' => 'Marketing Gadget Mart',
            'include_signature' => 1,
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->superadmin)->post(route('quotations.store'), $postData);

        $quotation = Quotation::where('quotation_number', 'SPH/GM/202611/001')->first();
        $this->assertNotNull($quotation);
        $this->assertEquals('PT. Pelayaran Meratus', $quotation->customer_name);
        $this->assertEquals(50898000, $quotation->subtotal);
        $this->assertEquals(1000000, $quotation->discount);
        $this->assertEquals(49898000, $quotation->total_amount);

        $response->assertRedirect(route('quotations.show', $quotation));

        // Ensure ActivityNotification was logged
        $this->assertDatabaseHas('activity_notifications', [
            'action' => 'create_quotation',
        ]);
    }

    public function test_can_view_and_print_quotation(): void
    {
        $quotation = Quotation::create([
            'user_id' => $this->superadmin->id,
            'quotation_number' => 'SPH/GM/202611/002',
            'customer_name' => 'PT. Sumber Rezeki',
            'customer_destination' => 'di-tempat',
            'letter_date' => '2026-11-10',
            'letter_city' => 'Banjarmasin',
            'items' => [
                [
                    'description' => 'SAMSUNG GALAXY S24 ULTRA',
                    'price' => 21999000,
                    'qty' => 1,
                    'total' => 21999000,
                ],
            ],
            'subtotal' => 21999000,
            'total_amount' => 21999000,
            'signer_name' => 'RAFIQI IMANUDDIN',
            'signer_title' => 'Marketing Gadget Mart',
            'include_signature' => true,
        ]);

        $showResponse = $this->actingAs($this->superadmin)->get(route('quotations.show', $quotation));
        $showResponse->assertOk();
        $showResponse->assertSee('PT. Sumber Rezeki');
        $showResponse->assertSee('SAMSUNG GALAXY S24 ULTRA');
        $showResponse->assertSee('RAFIQI IMANUDDIN');

        $printResponse = $this->actingAs($this->superadmin)->get(route('quotations.print', $quotation));
        $printResponse->assertOk();
        $printResponse->assertSee('PT. Sumber Rezeki');
    }

    public function test_discount_is_displayed_when_present(): void
    {
        $quotation = Quotation::create([
            'user_id' => $this->superadmin->id,
            'quotation_number' => 'SPH/GM/202611/005',
            'customer_name' => 'PT. Diskon Sukses',
            'customer_destination' => 'di-tempat',
            'letter_date' => '2026-11-10',
            'letter_city' => 'Banjarmasin',
            'items' => [
                [
                    'description' => 'IPHONE 16 PRO 128GB',
                    'price' => 20000000,
                    'qty' => 1,
                    'total' => 20000000,
                ],
            ],
            'subtotal' => 20000000,
            'discount' => 500000,
            'total_amount' => 19500000,
            'signer_name' => 'RAFIQI IMANUDDIN',
            'signer_title' => 'Marketing Gadget Mart',
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('quotations.show', $quotation));
        $response->assertOk();
        $response->assertSee('SUBTOTAL:');
        $response->assertSee('DISKON:');
        $response->assertSee('500.000');
        $response->assertSee('TOTAL AKHIR:');
        $response->assertSee('19.500.000');
    }

    public function test_discount_is_hidden_when_zero_or_null(): void
    {
        $quotation = Quotation::create([
            'user_id' => $this->superadmin->id,
            'quotation_number' => 'SPH/GM/202611/006',
            'customer_name' => 'PT. Tanpa Diskon',
            'customer_destination' => 'di-tempat',
            'letter_date' => '2026-11-10',
            'letter_city' => 'Banjarmasin',
            'items' => [
                [
                    'description' => 'XIAOMI 14T PRO',
                    'price' => 8999000,
                    'qty' => 1,
                    'total' => 8999000,
                ],
            ],
            'subtotal' => 8999000,
            'discount' => 0,
            'total_amount' => 8999000,
            'signer_name' => 'RAFIQI IMANUDDIN',
            'signer_title' => 'Marketing Gadget Mart',
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('quotations.show', $quotation));
        $response->assertOk();
        $response->assertDontSee('DISKON:');
        $response->assertDontSee('SUBTOTAL:');
    }

    public function test_can_duplicate_quotation(): void
    {
        $quotation = Quotation::create([
            'user_id' => $this->superadmin->id,
            'quotation_number' => 'SPH/GM/202611/003',
            'customer_name' => 'PT. Borneo Niaga',
            'customer_destination' => 'di-tempat',
            'letter_date' => '2026-11-05',
            'letter_city' => 'Banjarmasin',
            'items' => [
                [
                    'description' => 'MACBOOK AIR M3 13 INCH',
                    'price' => 18999000,
                    'qty' => 1,
                    'total' => 18999000,
                ],
            ],
            'subtotal' => 18999000,
            'total_amount' => 18999000,
            'signer_name' => 'RAFIQI IMANUDDIN',
            'signer_title' => 'Marketing Gadget Mart',
        ]);

        $response = $this->actingAs($this->superadmin)->post(route('quotations.duplicate', $quotation));
        
        $duplicated = Quotation::where('customer_name', 'PT. Borneo Niaga')
            ->where('id', '!=', $quotation->id)
            ->first();

        $this->assertNotNull($duplicated);
        $this->assertNotEquals($quotation->quotation_number, $duplicated->quotation_number);
        $response->assertRedirect(route('quotations.edit', $duplicated));
    }

    public function test_can_delete_quotation(): void
    {
        $quotation = Quotation::create([
            'user_id' => $this->superadmin->id,
            'quotation_number' => 'SPH/GM/202611/004',
            'customer_name' => 'PT. Hapus Test',
            'customer_destination' => 'di-tempat',
            'letter_date' => '2026-11-10',
            'letter_city' => 'Banjarmasin',
            'items' => [],
            'subtotal' => 0,
            'total_amount' => 0,
            'signer_name' => 'RAFIQI IMANUDDIN',
            'signer_title' => 'Marketing Gadget Mart',
        ]);

        $response = $this->actingAs($this->superadmin)->delete(route('quotations.destroy', $quotation));
        $response->assertRedirect(route('quotations.index'));

        $this->assertDatabaseMissing('quotations', ['id' => $quotation->id]);
    }

    public function test_can_upload_custom_signature_file(): void
    {
        $fakeFile = \Illuminate\Http\UploadedFile::fake()->image('ttd_custom.png', 150, 80);

        $postData = [
            'quotation_number' => 'SPH/GM/202611/007',
            'customer_name' => 'PT. Upload TTD',
            'customer_destination' => 'di-tempat',
            'letter_date' => '2026-11-10',
            'letter_city' => 'Banjarmasin',
            'items' => [
                [
                    'description' => 'IPAD AIR 11 INCH',
                    'price' => 11999000,
                    'qty' => 1,
                ],
            ],
            'signer_name' => 'BAMBANG WIJAYA',
            'signer_title' => 'Supervisor Sales',
            'include_signature' => 1,
            'signature_file' => $fakeFile,
        ];

        $response = $this->actingAs($this->superadmin)->post(route('quotations.store'), $postData);
        $quotation = Quotation::where('quotation_number', 'SPH/GM/202611/007')->first();
        $this->assertNotNull($quotation);
        $this->assertNotNull($quotation->signature_image);
        $this->assertFileExists(public_path($quotation->signature_image));

        // Clean up uploaded test file
        if (file_exists(public_path($quotation->signature_image))) {
            unlink(public_path($quotation->signature_image));
        }

        $response->assertRedirect(route('quotations.show', $quotation));
    }

    public function test_can_save_signature_from_base64_processed_canvas(): void
    {
        // 1x1 transparent png in base64
        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $postData = [
            'quotation_number' => 'SPH/GM/202611/008',
            'customer_name' => 'PT. Transparan Signature',
            'customer_destination' => 'di-tempat',
            'letter_date' => '2026-11-10',
            'letter_city' => 'Banjarmasin',
            'items' => [
                [
                    'description' => 'SAMSUNG S24 ULTRA',
                    'price' => 21999000,
                    'qty' => 1,
                ],
            ],
            'signer_name' => 'RAFIQI IMANUDDIN',
            'signer_title' => 'Marketing Gadget Mart',
            'include_signature' => 1,
            'signature_base64' => $fakeBase64,
            'signature_scale' => 125,
        ];

        $response = $this->actingAs($this->superadmin)->post(route('quotations.store'), $postData);
        $quotation = Quotation::where('quotation_number', 'SPH/GM/202611/008')->first();
        $this->assertNotNull($quotation);
        $this->assertEquals(125, $quotation->signature_scale);
        $this->assertNotNull($quotation->signature_image);
        $this->assertStringEndsWith('.png', $quotation->signature_image);
        $this->assertFileExists(public_path($quotation->signature_image));

        // Clean up
        if (file_exists(public_path($quotation->signature_image))) {
            unlink(public_path($quotation->signature_image));
        }

        $response->assertRedirect(route('quotations.show', $quotation));
    }
}
