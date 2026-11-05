<?php

namespace App\Http\Controllers;

use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\RepeatOrderPrediction;
use App\Models\Turnover;
use App\Models\TurnoverImport;
use App\Services\CrossSellingImporter;
use App\Services\FastXlsxWriter;
use App\Services\RepeatOrderPredictor;
use App\Services\TurnoverImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CrossSellingImportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat mengelola import data.');

        return view('imports.index', [
            'imports' => CrossSellingImport::with('user')->latest()->paginate(10, ['*'], 'cross_page'),
            'turnoverImports' => TurnoverImport::with('user')->latest()->paginate(10, ['*'], 'turnover_page'),
        ]);
    }

    public function template(): BinaryFileResponse
    {
        $headers = [
            'Cabang',
            'Posting Date',
            'Document Number',
            'Customer/Vendor Code',
            'Customer/Vendor Name',
            'ItemCode',
            'Dscription',
            'Brand',
            'Quantity',
            'Harga Jual',
            'Nama Sales',
            'No HP Konsumen',
        ];

        $sampleRows = [
            [
                'Banjarmasin',
                '2026-10-18',
                'INV-001',
                'CUST-001',
                'Budi Santoso',
                'ACC-001',
                'Kabel Data Type-C Fast Charging',
                'XIAOMI',
                1,
                50000,
                'Sales Ahmad',
                '081234567890',
            ],
            [
                'Banjarmasin',
                '2026-10-19',
                'INV-002',
                'CUST-002',
                'Siti Rahma',
                'HP-001',
                'Samsung Galaxy A55 5G 8/256GB',
                'SAMSUNG',
                1,
                5999000,
                'Sales Budi',
                '082345678901',
            ],
        ];

        return FastXlsxWriter::download('template-cross-selling.xlsx', $headers, $sampleRows);
    }

    public function templateTurnover(): BinaryFileResponse
    {
        $headers = [
            'Cabang',
            'Posting Date',
            'Document Number',
            'Sales Employee Name',
            'Remarks',
            'SalesCabang',
            'DPP',
            'GrossProfit',
            'Total',
        ];

        $sampleRows = [
            [
                'Banjarmasin',
                '2026-10-18',
                '260274906',
                'bjm.sos.01',
                'Order Online WA',
                'Banjarmasin',
                5000000,
                250000,
                5550000,
            ],
            [
                'Banjarmasin',
                '2026-10-19',
                '260274907',
                'bjm.sos.02',
                'Order Online IG',
                'Banjarmasin',
                3500000,
                175000,
                3885000,
            ],
        ];

        return FastXlsxWriter::download('template-turnover.xlsx', $headers, $sampleRows);
    }

    public function store(
        Request $request,
        CrossSellingImporter $crossSellingImporter,
        TurnoverImporter $turnoverImporter,
        RepeatOrderPredictor $predictor
    ): JsonResponse|RedirectResponse {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat mengimpor data.');

        $hasCross = $request->hasFile('file');
        $hasTurnover = $request->hasFile('turnover_file');

        if (! $hasCross && ! $hasTurnover) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Pilih minimal satu file (Cross-Selling atau Turnover) untuk diunggah.'], 422);
            }
            return back()->withErrors(['file' => 'Pilih minimal satu file (Cross-Selling atau Turnover) untuk diunggah.']);
        }

        $rules = [];
        if ($hasCross) {
            $rules['file'] = ['file', 'mimes:xlsx,xls,csv', 'max:3584'];
        }
        if ($hasTurnover) {
            $rules['turnover_file'] = ['file', 'mimes:xlsx,xls,csv', 'max:3584'];
        }

        try {
            $validated = $request->validate($rules);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            }
            throw $e;
        }

        $results = [];

        // 1. Process Cross-Selling
        if ($hasCross && isset($validated['file'])) {
            $storedPath = $validated['file']->store('cross-selling-imports', 'local');
            $cImport = CrossSellingImport::create([
                'user_id'       => $request->user()->id,
                'original_name' => $validated['file']->getClientOriginalName(),
                'stored_path'   => $storedPath,
                'status'        => 'processing',
            ]);

            try {
                $crossSellingImporter->import($cImport);
                $cImport->refresh();
                if ($cImport->status === 'completed') {
                    if ($request->boolean('sync_rebuild', false)) {
                        $predictor->rebuild();
                    } else {
                        try {
                            \App\Jobs\ImportCrossSellingJob::dispatch($cImport);
                        } catch (\Throwable $e) {}
                    }
                }
            } catch (\Throwable $e) {}

            $results['cross_selling'] = [
                'id' => $cImport->id,
                'original_name' => $cImport->original_name,
                'status' => $cImport->status,
                'row_count' => $cImport->row_count,
                'success_count' => $cImport->success_count,
                'failed_count' => $cImport->failed_count,
            ];
        }

        // 2. Process Turnover
        if ($hasTurnover && isset($validated['turnover_file'])) {
            $storedTurnoverPath = $validated['turnover_file']->store('turnover-imports', 'local');
            $tImport = TurnoverImport::create([
                'user_id'       => $request->user()->id,
                'original_name' => $validated['turnover_file']->getClientOriginalName(),
                'stored_path'   => $storedTurnoverPath,
                'status'        => 'processing',
            ]);

            try {
                $turnoverImporter->import($tImport);
                $tImport->refresh();
            } catch (\Throwable $e) {}

            $results['turnover'] = [
                'id' => $tImport->id,
                'original_name' => $tImport->original_name,
                'status' => $tImport->status,
                'row_count' => $tImport->row_count,
                'success_count' => $tImport->success_count,
                'failed_count' => $tImport->failed_count,
            ];
        }

        $messageParts = [];
        if (isset($results['cross_selling'])) {
            $c = $results['cross_selling'];
            $messageParts[] = "Cross-Selling ({$c['success_count']} baris)";
        }
        if (isset($results['turnover'])) {
            $t = $results['turnover'];
            $messageParts[] = "Turnover ({$t['success_count']} baris)";
        }
        $summaryMessage = 'File ' . implode(' & ', $messageParts) . ' berhasil diproses dan disinkronkan ke seluruh sistem!';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $summaryMessage,
                'results' => $results,
            ]);
        }

        return redirect()->route('imports.index')->with('success', $summaryMessage);
    }

    public function status(Request $request, CrossSellingImport $import): JsonResponse
    {
        abort_unless($import->user_id === $request->user()->id, 404);

        return response()->json([
            'status' => $import->status,
            'row_count' => $import->row_count,
            'success_count' => $import->success_count,
            'failed_count' => $import->failed_count,
        ]);
    }

    public function destroy(Request $request, CrossSellingImport $import, RepeatOrderPredictor $predictor): RedirectResponse
    {
        abort_unless($import->user_id === $request->user()->id, 404);

        $storedPath = $import->stored_path;

        DB::transaction(function () use ($import): void {
            RepeatOrderPrediction::where('cross_selling_import_id', $import->id)->delete();
            CrossSelling::where('cross_selling_import_id', $import->id)->delete();
            $import->delete();
        });

        if ($storedPath) {
            Storage::disk('local')->delete($storedPath);
        }

        $predictor->rebuild();

        return redirect()->route('imports.index')->with('success', 'Data import berhasil dihapus.');
    }

    public function destroyTurnover(Request $request, TurnoverImport $import): RedirectResponse
    {
        abort_unless($import->user_id === $request->user()->id || $request->user()->isSuperAdmin(), 404);

        $storedPath = $import->stored_path;

        DB::transaction(function () use ($import): void {
            Turnover::where('turnover_import_id', $import->id)->delete();
            $import->delete();
        });

        if ($storedPath) {
            Storage::disk('local')->delete($storedPath);
        }

        return redirect()->route('imports.index')->with('success', 'Data import turnover berhasil dihapus.');
    }
}
