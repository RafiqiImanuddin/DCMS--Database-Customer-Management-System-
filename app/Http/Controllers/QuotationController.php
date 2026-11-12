<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuotationController extends Controller
{
    /**
     * Tampilkan daftar riwayat surat penawaran harga.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canAccessFeature('quotations'), 403, 'Akses terbatas: Fitur Surat Penawaran Harga tidak diaktifkan untuk akun Anda.');

        $search = $request->string('search')->trim()->toString();
        $dateFrom = $request->string('date_from')->trim()->toString();
        $dateTo = $request->string('date_to')->trim()->toString();

        $query = Quotation::query()->with('user')->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('signer_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($dateFrom !== '') {
            $query->whereDate('letter_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('letter_date', '<=', $dateTo);
        }

        $quotations = $query->paginate(15)->withQueryString();

        $stats = [
            'total_count' => Quotation::count(),
            'total_amount' => (int) Quotation::sum('total_amount'),
            'this_month_count' => Quotation::whereYear('letter_date', Carbon::now()->year)
                ->whereMonth('letter_date', Carbon::now()->month)
                ->count(),
        ];

        return view('quotations.index', [
            'quotations' => $quotations,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'stats' => $stats,
        ]);
    }

    /**
     * Form pembuatan surat penawaran baru.
     */
    public function create(Request $request): View|RedirectResponse
    {
        abort_unless($request->user()?->canAccessFeature('quotations'), 403, 'Akses terbatas: Fitur Surat Penawaran Harga tidak diaktifkan untuk akun Anda.');

        if ($request->user()?->isGuest()) {
            return redirect()->route('quotations.index')->with('error', 'Akses dibatasi: Akun Tamu (Guest) hanya memiliki hak akses lihat (Read-Only).');
        }

        $defaultQuotation = new Quotation([
            'quotation_number' => Quotation::generateNextNumber(),
            'customer_name' => 'PT. ',
            'customer_destination' => 'di-tempat',
            'letter_date' => Carbon::now()->format('Y-m-d'),
            'letter_city' => 'Banjarmasin',
            'greeting_text' => "Dengan hormat,\n\nGadget Mart merupakan store retail handphone terkemuka dan terbesar di Kota Banjarmasin yang bergerak di bidang penjualan Handphone, Aksesoris, Laptop. Kami ingin memberikan penawaran harga barang dengan rincian sebagai berikut:",
            'items' => [
                [
                    'description' => '',
                    'price' => 0,
                    'qty' => 1,
                    'total' => 0,
                ],
            ],
            'subtotal' => 0,
            'discount' => 0,
            'total_amount' => 0,
            'notes' => '*Harga yang tertera dapat berubah sewaktu-waktu',
            'closing_text' => 'Demikian penawaran yang kami sampaikan, atas perhatiannya kami ucapkan terima kasih.',
            'signer_name' => 'RAFIQI IMANUDDIN',
            'signer_title' => 'Marketing Gadget Mart',
            'include_signature' => true,
            'status' => 'draft',
        ]);

        return view('quotations.form', [
            'quotation' => $defaultQuotation,
            'isEdit' => false,
            'pricelistProducts' => $this->getPricelistQuickOptions(),
        ]);
    }

    /**
     * Simpan surat penawaran baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canAccessFeature('quotations'), 403, 'Akses terbatas: Fitur Surat Penawaran Harga tidak diaktifkan untuk akun Anda.');

        $validated = $this->validateQuotation($request);

        $validated['user_id'] = $request->user()->id;
        $itemsData = $this->processItems($validated['items'] ?? []);
        $validated['items'] = $itemsData['items'];
        $validated['subtotal'] = $itemsData['subtotal'];
        $validated['discount'] = max(0, (int) ($validated['discount'] ?? 0));
        $validated['total_amount'] = max(0, $itemsData['subtotal'] - $validated['discount']);
        $validated['signature_scale'] = max(30, min(250, (int) ($validated['signature_scale'] ?? 100)));

        if ($request->boolean('reset_signature_to_default')) {
            $validated['signature_image'] = null;
        } elseif ($request->filled('signature_base64') && str_starts_with($request->input('signature_base64'), 'data:image')) {
            $data = $request->input('signature_base64');
            $parts = explode(',', $data, 2);
            if (count($parts) === 2) {
                $decoded = base64_decode($parts[1]);
                if ($decoded !== false) {
                    $filename = 'sig_' . time() . '_' . uniqid() . '.png';
                    $dest = public_path('uploads/signatures');
                    if (! file_exists($dest)) {
                        mkdir($dest, 0755, true);
                    }
                    file_put_contents($dest . DIRECTORY_SEPARATOR . $filename, $decoded);
                    $validated['signature_image'] = 'uploads/signatures/' . $filename;
                }
            }
        } elseif ($request->hasFile('signature_file')) {
            $file = $request->file('signature_file');
            $filename = 'sig_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('uploads/signatures');
            if (! file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, $filename);
            $validated['signature_image'] = 'uploads/signatures/' . $filename;
        }

        $quotation = Quotation::create($validated);

        try {
            $user = $request->user();
            ActivityNotification::log(
                title: 'Surat Penawaran Dibuat',
                message: "{$user->name} membuat surat penawaran baru {$quotation->quotation_number} untuk {$quotation->customer_name}",
                user: $user,
                type: 'success',
                action: 'create_quotation',
                icon: 'document'
            );
        } catch (\Throwable) {}

        return redirect()->route('quotations.show', $quotation)
            ->with('success', "Surat penawaran {$quotation->quotation_number} berhasil dibuat.");
    }

    /**
     * Tampilkan detail surat penawaran untuk preview & cetak.
     */
    public function show(Request $request, Quotation $quotation): View
    {
        abort_unless($request->user()?->canAccessFeature('quotations'), 403, 'Akses terbatas.');

        return view('quotations.show', [
            'quotation' => $quotation,
        ]);
    }

    /**
     * Form edit surat penawaran.
     */
    public function edit(Request $request, Quotation $quotation): View|RedirectResponse
    {
        abort_unless($request->user()?->canAccessFeature('quotations'), 403, 'Akses terbatas.');

        if ($request->user()?->isGuest()) {
            return redirect()->route('quotations.index')->with('error', 'Akses dibatasi: Akun Tamu (Guest) hanya memiliki hak akses lihat (Read-Only).');
        }

        return view('quotations.form', [
            'quotation' => $quotation,
            'isEdit' => true,
            'pricelistProducts' => $this->getPricelistQuickOptions(),
        ]);
    }

    /**
     * Update data surat penawaran.
     */
    public function update(Request $request, Quotation $quotation): RedirectResponse
    {
        abort_unless($request->user()?->canAccessFeature('quotations'), 403, 'Akses terbatas.');

        $validated = $this->validateQuotation($request, $quotation->id);

        $itemsData = $this->processItems($validated['items'] ?? []);
        $validated['items'] = $itemsData['items'];
        $validated['subtotal'] = $itemsData['subtotal'];
        $validated['discount'] = max(0, (int) ($validated['discount'] ?? 0));
        $validated['total_amount'] = max(0, $itemsData['subtotal'] - $validated['discount']);
        $validated['signature_scale'] = max(30, min(250, (int) ($validated['signature_scale'] ?? 100)));

        if ($request->boolean('reset_signature_to_default')) {
            $validated['signature_image'] = null;
        } elseif ($request->filled('signature_base64') && str_starts_with($request->input('signature_base64'), 'data:image')) {
            $data = $request->input('signature_base64');
            $parts = explode(',', $data, 2);
            if (count($parts) === 2) {
                $decoded = base64_decode($parts[1]);
                if ($decoded !== false) {
                    $filename = 'sig_' . time() . '_' . uniqid() . '.png';
                    $dest = public_path('uploads/signatures');
                    if (! file_exists($dest)) {
                        mkdir($dest, 0755, true);
                    }
                    file_put_contents($dest . DIRECTORY_SEPARATOR . $filename, $decoded);
                    $validated['signature_image'] = 'uploads/signatures/' . $filename;
                }
            }
        } elseif ($request->hasFile('signature_file')) {
            $file = $request->file('signature_file');
            $filename = 'sig_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('uploads/signatures');
            if (! file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, $filename);
            $validated['signature_image'] = 'uploads/signatures/' . $filename;
        }

        $quotation->update($validated);

        try {
            $user = $request->user();
            ActivityNotification::log(
                title: 'Surat Penawaran Diperbarui',
                message: "{$user->name} memperbarui surat penawaran {$quotation->quotation_number} ({$quotation->customer_name})",
                user: $user,
                type: 'info',
                action: 'update_quotation',
                icon: 'document'
            );
        } catch (\Throwable) {}

        return redirect()->route('quotations.show', $quotation)
            ->with('success', "Surat penawaran {$quotation->quotation_number} berhasil diperbarui.");
    }

    /**
     * Hapus surat penawaran.
     */
    public function destroy(Request $request, Quotation $quotation): RedirectResponse
    {
        abort_unless($request->user()?->canAccessFeature('quotations'), 403, 'Akses terbatas.');

        $number = $quotation->quotation_number;
        $customer = $quotation->customer_name;
        $quotation->delete();

        try {
            $user = $request->user();
            ActivityNotification::log(
                title: 'Surat Penawaran Dihapus',
                message: "{$user->name} menghapus surat penawaran {$number} ({$customer})",
                user: $user,
                type: 'warning',
                action: 'delete_quotation',
                icon: 'trash'
            );
        } catch (\Throwable) {}

        return redirect()->route('quotations.index')
            ->with('success', "Surat penawaran {$number} berhasil dihapus.");
    }

    /**
     * Duplikasi surat penawaran menjadi surat baru.
     */
    public function duplicate(Request $request, Quotation $quotation): RedirectResponse
    {
        abort_unless($request->user()?->canAccessFeature('quotations'), 403, 'Akses terbatas.');

        $newQuotation = $quotation->replicate();
        $newQuotation->quotation_number = Quotation::generateNextNumber();
        $newQuotation->letter_date = Carbon::now()->format('Y-m-d');
        $newQuotation->user_id = $request->user()->id;
        $newQuotation->status = 'draft';
        $newQuotation->save();

        try {
            $user = $request->user();
            ActivityNotification::log(
                title: 'Surat Penawaran Diduplikasi',
                message: "{$user->name} menduplikasi surat penawaran {$quotation->quotation_number} menjadi {$newQuotation->quotation_number}",
                user: $user,
                type: 'info',
                action: 'duplicate_quotation',
                icon: 'document'
            );
        } catch (\Throwable) {}

        return redirect()->route('quotations.edit', $newQuotation)
            ->with('success', "Surat penawaran berhasil diduplikasi sebagai {$newQuotation->quotation_number}. Silakan sesuaikan.");
    }

    /**
     * Halaman cetak / print khusus tanpa navigasi.
     */
    public function print(Request $request, Quotation $quotation): View
    {
        abort_unless($request->user()?->canAccessFeature('quotations'), 403, 'Akses terbatas.');

        return view('quotations.print', [
            'quotation' => $quotation,
        ]);
    }

    /**
     * API untuk pencarian barang dari Pricelist.
     */
    public function searchPricelist(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canAccessFeature('quotations'), 403, 'Akses terbatas.');

        $query = strtolower(trim((string) $request->input('q', '')));
        $products = $this->getPricelistQuickOptions();

        if ($query !== '') {
            $products = array_filter($products, function ($item) use ($query) {
                return str_contains(strtolower($item['name']), $query)
                    || str_contains(strtolower($item['brand'] ?? ''), $query);
            });
            $products = array_values($products);
        }

        if ($request->user()?->isGuest()) {
            foreach ($products as &$item) {
                $item['price'] = 0;
            }
            unset($item);
        }

        return response()->json([
            'results' => array_slice($products, 0, 30),
        ]);
    }

    /**
     * Dapatkan daftar ringkas opsi produk dari pricelist_data.json
     *
     * @return array<array{name: string, price: int, brand: string}>
     */
    private function getPricelistQuickOptions(): array
    {
        $path = storage_path('app/pricelist_data.json');
        if (! file_exists($path)) {
            return [];
        }

        $json = @file_get_contents($path);
        if (! $json) {
            return [];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded) || ! isset($decoded['sheets'])) {
            return [];
        }

        $products = [];
        foreach ($decoded['sheets'] as $sheet) {
            if (! isset($sheet['data']) || ! is_array($sheet['data'])) {
                continue;
            }
            foreach ($sheet['data'] as $row) {
                $name = trim((string) ($row['type'] ?? $row['tipe'] ?? $row['nama'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $price = (int) ($row['srp_promo'] ?? $row['modal_sesudah_cb'] ?? $row['harga'] ?? 0);
                $brand = trim((string) ($row['brand'] ?? ''));
                $products[] = [
                    'name' => $name,
                    'price' => $price,
                    'brand' => $brand,
                ];
            }
        }

        return array_values(array_unique($products, SORT_REGULAR));
    }

    /**
     * Validasi data input form penawaran harga.
     */
    private function validateQuotation(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:quotations,quotation_number';
        if ($ignoreId !== null) {
            $uniqueRule .= ",{$ignoreId}";
        }

        return $request->validate([
            'quotation_number' => ['required', 'string', 'max:100', $uniqueRule],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_destination' => ['required', 'string', 'max:100'],
            'letter_date' => ['required', 'date'],
            'letter_city' => ['required', 'string', 'max:100'],
            'greeting_text' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'discount' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'closing_text' => ['nullable', 'string'],
            'signer_name' => ['required', 'string', 'max:150'],
            'signer_title' => ['required', 'string', 'max:150'],
            'include_signature' => ['boolean'],
            'signature_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:5120'],
            'signature_base64' => ['nullable', 'string'],
            'signature_scale' => ['nullable', 'integer', 'min:30', 'max:250'],
            'reset_signature_to_default' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'in:draft,sent,accepted'],
        ]);
    }

    /**
     * Hitung subtotal dan susun array items yang bersih.
     *
     * @return array{items: array, subtotal: int}
     */
    private function processItems(array $rawItems): array
    {
        $processed = [];
        $subtotal = 0;

        foreach ($rawItems as $row) {
            $desc = trim((string) ($row['description'] ?? ''));
            if ($desc === '') {
                continue;
            }

            $price = (int) ($row['price'] ?? 0);
            $qty = max(1, (int) ($row['qty'] ?? 1));
            $total = $price * $qty;

            $processed[] = [
                'description' => $desc,
                'price' => $price,
                'qty' => $qty,
                'total' => $total,
            ];

            $subtotal += $total;
        }

        if (empty($processed)) {
            $processed[] = [
                'description' => 'Produk Gadget Mart',
                'price' => 0,
                'qty' => 1,
                'total' => 0,
            ];
        }

        return [
            'items' => $processed,
            'subtotal' => $subtotal,
        ];
    }
}
