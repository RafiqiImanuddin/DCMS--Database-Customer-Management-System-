<?php

namespace App\Http\Controllers;

use App\Models\CrossSellingImport;
use App\Services\CrossSellingImporter;
use App\Services\RepeatOrderPredictor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrossSellingImportController extends Controller
{
    public function index(): View
    {
        return view('imports.index', [
            'imports' => CrossSellingImport::with('user')->latest()->paginate(15),
        ]);
    }

    public function store(Request $request, CrossSellingImporter $importer, RepeatOrderPredictor $predictor): RedirectResponse
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

        $importer->import($import);
        $import->refresh();

        if ($import->status === 'completed') {
            $predictor->rebuild();
        }

        return redirect()->route('imports.index')->with(
            $import->status === 'completed' ? 'success' : 'error',
            $import->status === 'completed'
                ? $import->success_count.' baris berhasil diimport dan prediksi diperbarui.'
                : 'Import gagal: '.$import->error_log,
        );
    }
}
