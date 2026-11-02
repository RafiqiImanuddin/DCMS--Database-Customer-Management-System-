<?php

namespace App\Http\Controllers;

use App\Jobs\ImportCrossSellingJob;
use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\RepeatOrderPrediction;
use App\Services\RepeatOrderPredictor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CrossSellingImportController extends Controller
{
    public function index(): View
    {
        return view('imports.index', [
            'imports' => CrossSellingImport::with('user')->latest()->paginate(15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:102400'],
        ]);

        $storedPath = $validated['file']->store('cross-selling-imports', 'local');
        $import = CrossSellingImport::create([
            'user_id' => $request->user()->id,
            'original_name' => $validated['file']->getClientOriginalName(),
            'stored_path' => $storedPath,
            'status' => 'processing',
        ]);

        ImportCrossSellingJob::dispatch($import);

        return redirect()->route('imports.index')->with(
            'success',
            'File masuk ke antrean import. Status akan diperbarui setelah pemrosesan selesai.',
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
