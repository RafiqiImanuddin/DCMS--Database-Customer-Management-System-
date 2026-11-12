<?php

namespace Tests\Feature;

use App\Models\Quotation;
use App\Models\User;
use App\Support\DataMasker;
use App\Support\RouteCipher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class GuestAccessAndDataMaskingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_displays_login_as_guest_button(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Masuk sebagai Tamu (Guest)');
        $response->assertSee('Mode Hanya-Lihat (Read-Only)');
        $response->assertSee(route('login.guest'));
    }

    public function test_login_as_guest_authenticates_guest_user_and_redirects_to_dashboard(): void
    {
        $response = $this->get(route('login.guest'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = Auth::user();
        $this->assertNotNull($user);
        $this->assertEquals('guest@gadgetmart.id', $user->email);
        $this->assertEquals('guest', $user->role);
        $this->assertTrue($user->isGuest());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_guest_can_access_all_main_navigation_routes(): void
    {
        $guest = User::factory()->create([
            'email' => 'guest@gadgetmart.id',
            'role' => 'guest',
        ]);

        $routesToTest = [
            route('dashboard'),
            route('predictions.index'),
            route('leads.index'),
            route('broadcasts.index'),
            route('pricelist.index'),
            route('quotations.index'),
            route('workspace-sosmed.index'),
            route('monitoring.sales.index'),
            route('shifts.index'),
            route('imports.index'),
        ];

        foreach ($routesToTest as $routeUrl) {
            $response = $this->actingAs($guest)->get($routeUrl);
            $response->assertStatus(200);
            $response->assertSee('Mode Tamu (Guest • Read-Only)');
        }
    }

    public function test_prevent_guest_modification_middleware_blocks_all_mutating_requests(): void
    {
        $guest = User::factory()->create([
            'email' => 'guest@gadgetmart.id',
            'role' => 'guest',
        ]);

        // 1. Web mutation attempt (redirects back with error flash)
        $response = $this->actingAs($guest)->post(route('quotations.store'), [
            'customer_name' => 'Customer Test',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $responseShift = $this->actingAs($guest)->post(route('shifts.store'), [
            'admin_name' => 'Admin Test',
            'shift_date' => now()->format('Y-m-d'),
            'shift_type' => 'pagi',
        ]);
        $responseShift->assertRedirect();
        $responseShift->assertSessionHas('error');

        // 2. AJAX / JSON mutation attempt (returns JSON 403)
        $responseAjax = $this->actingAs($guest)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->delete(route('leads.bulkDestroy'), [
                'codes' => ['CUST001'],
            ]);

        $responseAjax->assertStatus(403);
        $responseAjax->assertJson([
            'success' => false,
            'error' => 'READ_ONLY_GUEST',
        ]);
    }

    public function test_guest_cannot_download_raw_customer_or_sales_lists(): void
    {
        $guest = User::factory()->create([
            'email' => 'guest@gadgetmart.id',
            'role' => 'guest',
        ]);

        // Customer predictions download
        $responsePredictions = $this->actingAs($guest)->get(route('predictions.download'));
        $responsePredictions->assertStatus(403);

        // Leads download
        $responseLeads = $this->actingAs($guest)->get(route('leads.download'));
        $responseLeads->assertStatus(403);

        // Sales monitoring export
        $responseExport = $this->actingAs($guest)->get(route('monitoring.sales.export'));
        $responseExport->assertStatus(403);
    }

    public function test_data_masker_helper_masks_sensitive_data_properly_for_guest(): void
    {
        $guest = User::factory()->create(['role' => 'guest']);
        $admin = User::factory()->create(['role' => 'admin']);

        // When acting as Admin
        $this->actingAs($admin);
        $this->assertFalse(DataMasker::isGuest());
        $this->assertEquals('081234567890', DataMasker::maskPhone('081234567890'));
        $this->assertEquals('Rp 1.500.000', DataMasker::maskCurrency(1500000));
        $this->assertEquals(50, DataMasker::maskNumber(50));
        $this->assertEquals('85.5%', DataMasker::maskPercentage(85.5));

        // When acting as Guest
        $this->actingAs($guest);
        $this->assertTrue(DataMasker::isGuest());
        $this->assertEquals('0812****890', DataMasker::maskPhone('081234567890'));
        $this->assertEquals('Rp ***.***', DataMasker::maskCurrency(1500000));
        $this->assertEquals('***.***', DataMasker::maskNumber(50));
        $this->assertEquals('***%', DataMasker::maskPercentage(85.5));
        $this->assertEquals('javascript:void(0)', DataMasker::maskWaLink('081234567890'));
    }

    public function test_quotation_create_and_edit_redirects_guest_back_to_index(): void
    {
        $guest = User::factory()->create(['role' => 'guest']);
        $quotation = Quotation::create([
            'quotation_number' => 'QUO/TEST/001',
            'customer_name' => 'Budi Santoso',
            'customer_destination' => 'Banjarmasin',
            'letter_date' => now(),
            'items' => [['description' => 'iPhone 15 Pro', 'price' => 18000000, 'qty' => 1, 'total' => 18000000]],
            'total_amount' => 18000000,
            'signer_name' => 'Manager Gadgetmart',
            'signer_title' => 'Branch Manager',
        ]);

        $responseCreate = $this->actingAs($guest)->get(route('quotations.create'));
        $responseCreate->assertRedirect(route('quotations.index'));
        $responseCreate->assertSessionHas('error');

        $responseEdit = $this->actingAs($guest)->get(route('quotations.edit', $quotation));
        $responseEdit->assertRedirect(route('quotations.index'));
        $responseEdit->assertSessionHas('error');
    }

    public function test_quotations_pricelist_search_api_masks_prices_to_zero_for_guest(): void
    {
        $guest = User::factory()->create(['role' => 'guest']);

        // Mock temporary pricelist file
        $pricelistPath = storage_path('app/pricelist_data.json');
        $originalContent = file_exists($pricelistPath) ? file_get_contents($pricelistPath) : null;

        $dummyPricelist = [
            'sheets' => [
                [
                    'name' => 'APPLE',
                    'data' => [
                        [
                            'type' => 'iPhone 15 128GB',
                            'srp_promo' => 16499000,
                            'brand' => 'APPLE',
                        ],
                    ],
                ],
            ],
        ];
        file_put_contents($pricelistPath, json_encode($dummyPricelist));

        try {
            $response = $this->actingAs($guest)->getJson(route('quotations.pricelist.search', ['q' => 'iPhone']));

            $response->assertStatus(200);
            $results = $response->json('results');
            $this->assertIsArray($results);
            $this->assertNotEmpty($results);
            foreach ($results as $item) {
                $this->assertEquals(0, $item['price'], 'Price must be 0 for guest in pricelist search');
            }
        } finally {
            if ($originalContent !== null) {
                file_put_contents($pricelistPath, $originalContent);
            } elseif (file_exists($pricelistPath)) {
                unlink($pricelistPath);
            }
        }
    }

    public function test_quotation_show_and_print_masks_prices_for_guest(): void
    {
        $guest = User::factory()->create(['role' => 'guest']);
        $quotation = Quotation::create([
            'quotation_number' => 'QUO/TEST/002',
            'customer_name' => 'Siti Nurhaliza',
            'customer_destination' => 'Banjarmasin',
            'letter_date' => now(),
            'items' => [
                ['description' => 'iPad Air 5 64GB', 'price' => 10500000, 'qty' => 1, 'total' => 10500000],
            ],
            'subtotal' => 10500000,
            'total_amount' => 10500000,
            'signer_name' => 'Manager Gadgetmart',
            'signer_title' => 'Branch Manager',
        ]);

        $responseShow = $this->actingAs($guest)->get(route('quotations.show', $quotation));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Rp ***.***');
        $responseShow->assertDontSee('Rp. 10.500.000');
        $responseShow->assertDontSee('Edit Surat'); // Edit button must be hidden for guest

        $responsePrint = $this->actingAs($guest)->get(route('quotations.print', $quotation));
        $responsePrint->assertStatus(200);
        $responsePrint->assertSee('Rp ***.***');
        $responsePrint->assertDontSee('Rp. 10.500.000');
    }

    public function test_workspace_sosmed_search_ar_masks_phone_and_price_for_guest(): void
    {
        $guest = User::factory()->create(['role' => 'guest']);

        \App\Models\CrossSelling::create([
            'document_number' => 'INV-TEST-001',
            'customer_vendor_code' => 'CUST001',
            'customer_vendor_name' => 'Customer Rahasia',
            'item_code' => 'IPHONE15',
            'dscription' => 'iPhone 15 Pro Max',
            'no_hp_customer' => '081234567890',
            'harga_jual' => 20000000,
            'cabang' => 'DUTA MALL',
            'posting_date' => now(),
        ]);

        $response = $this->actingAs($guest)->getJson(route('api.sosmed.search_ar', ['query' => 'INV-TEST-001']));
        $response->assertStatus(200);

        $rows = $response->json('rows');
        $this->assertNotEmpty($rows);
        $first = $rows[0];
        $this->assertEquals('0812****890', $first['no_hp_customer']);
        $this->assertEquals(0, $first['harga_jual']);
    }

    public function test_sosmed_keeps_api_masks_phone_numbers_for_guest(): void
    {
        $guest = User::factory()->create(['role' => 'guest']);

        \App\Models\SosmedKeep::create([
            'nama' => 'Pelanggan Keep',
            'nomor_wa' => '089876543210',
            'barang_keep' => 'MacBook Air M2',
            'tanggal_ambil' => now()->format('Y-m-d'),
            'status' => 'KEEP',
            'nomor_sender' => '081122334455',
            'nomor_follow_up' => '089988776655',
        ]);

        $response = $this->actingAs($guest)->getJson(route('api.sosmed.get_keeps'));
        $response->assertStatus(200);

        $keeps = $response->json('data');
        $this->assertNotEmpty($keeps);
        $first = $keeps[0];
        $this->assertEquals('0898****210', $first['nomor_wa']);
        $this->assertEquals('0811****455', $first['nomor_sender']);
    }
}
