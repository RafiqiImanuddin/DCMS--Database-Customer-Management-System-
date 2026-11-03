<?php

namespace App\Http\Controllers;

use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\RepeatOrderPrediction;
use App\Services\CrossSellingImporter;
use App\Services\RepeatOrderPredictor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CrossSellingImportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat mengelola import data.');

        return view('imports.index', [
            'imports' => CrossSellingImport::with('user')->latest()->paginate(15),
        ]);
    }

    public function template(): StreamedResponse
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

        $sampleRow1 = [
            'Banjarmasin',
            '2026-10-18',
            'INV-001',
            'CUST-001',
            'Budi Santoso',
            'ACC-001',
            'Kabel Data Type-C Fast Charging',
            'XIAOMI',
            '1',
            '50000',
            'Sales Ahmad',
            '081234567890',
        ];

        $sampleRow2 = [
            'Banjarmasin',
            '2026-10-19',
            'INV-002',
            'CUST-002',
            'Siti Rahma',
            'HP-001',
            'Samsung Galaxy A55 5G 8/256GB',
            'SAMSUNG',
            '1',
            '5999000',
            'Sales Budi',
            '082345678901',
        ];

        return response()->streamDownload(function () use ($headers, $sampleRow1, $sampleRow2) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);
            fputcsv($output, $sampleRow1);
            fputcsv($output, $sampleRow2);
            fclose($output);
        }, 'template-cross-selling.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request, CrossSellingImporter $importer, RepeatOrderPredictor $predictor): JsonResponse|RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat mengimpor data.');

        try {
            $validated = $request->validate([
                'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:3584'],
            ]);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            }
            throw $e;
        }

        $storedPath = $validated['file']->store('cross-selling-imports', 'local');
        $import = CrossSellingImport::create([
            'user_id'       => $request->user()->id,
            'original_name' => $validated['file']->getClientOriginalName(),
            'stored_path'   => $storedPath,
            'status'        => 'processing',
        ]);

        // Process synchronously so data appears immediately (no queue worker on Vercel).
        try {
            $importer->import($import);
            $import->refresh();
            if ($import->status === 'completed') {
                $predictor->rebuild();
            }
        } catch (\Throwable $e) {
            // Error already recorded inside importer; continue to redirect.
        }

        if ($request->expectsJson()) {
            $import->refresh();
            return response()->json([
                'message'       => 'Import selesai.',
                'status'        => $import->status,
                'row_count'     => $import->row_count,
                'success_count' => $import->success_count,
                'failed_count'  => $import->failed_count,
            ]);
        }

        return redirect()->route('imports.index')->with(
            'success',
            'File berhasil diimport.',
        );
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
}
