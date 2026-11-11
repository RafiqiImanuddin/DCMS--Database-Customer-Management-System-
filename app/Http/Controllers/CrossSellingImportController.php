<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\RepeatOrderPrediction;
use App\Models\Setting;
use App\Models\Turnover;
use App\Models\TurnoverImport;
use App\Services\CrossSellingImporter;
use App\Services\FastXlsxWriter;
use App\Services\KeepCrossSellingMatcher;
use App\Services\RepeatOrderPredictor;
use App\Services\TurnoverImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CrossSellingImportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canAccessFeature('imports'), 403, 'Akses terbatas: Fitur Pusat Unggah Data tidak diaktifkan untuk akun Anda.');

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
        RepeatOrderPredictor $predictor,
        KeepCrossSellingMatcher $keepMatcher
    ): JsonResponse|RedirectResponse {
        abort_unless($request->user()?->canAccessFeature('imports'), 403, 'Akses terbatas: Fitur Pusat Unggah Data tidak diaktifkan untuk akun Anda.');

        // 0. Deteksi jika request POST terpotong akibat melebihi post_max_size server hosting
        if (
            empty($_FILES) && empty($_POST)
            && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post'
            && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > 0
        ) {
            $postLimit = ini_get('post_max_size') ?: 'unknown';
            $msg = "Ukuran total berkas unggahan melebihi batas post_max_size server hosting (saat ini: {$postLimit}). Silakan naikkan batas post_max_size di cPanel -> MultiPHP INI Editor.";
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->withErrors(['file' => $msg]);
        }

        // 1. Deteksi apakah ada berkas yang dikirim tetapi ditolak oleh PHP (upload_max_filesize, partial, dsb)
        $detectUploadError = function (string $key, string $label) use ($request): ?string {
            $file = $request->file($key);
            if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile && ! $file->isValid()) {
                $errCode = $file->getError();
                // Jika error 4 (UPLOAD_ERR_NO_FILE), berarti slot ini sengaja dikosongkan oleh user
                if ($errCode === UPLOAD_ERR_NO_FILE) {
                    return null;
                }

                $origName = $file->getClientOriginalName() ?: $label;
                $iniLimit = ini_get('upload_max_filesize') ?: '2M';

                return match ($errCode) {
                    UPLOAD_ERR_INI_SIZE => "Berkas {$label} ('{$origName}') gagal diunggah karena ukurannya melebihi batas upload PHP server hosting (upload_max_filesize saat ini: {$iniLimit}). Silakan naikkan upload_max_filesize & post_max_size di cPanel -> MultiPHP INI Editor.",
                    UPLOAD_ERR_FORM_SIZE => "Berkas {$label} ('{$origName}') melebihi batas ukuran form yang diizinkan.",
                    UPLOAD_ERR_PARTIAL => "Berkas {$label} ('{$origName}') hanya terunggah sebagian (koneksi terputus saat upload). Silakan coba unggah ulang.",
                    UPLOAD_ERR_NO_TMP_DIR => "Direktori penampung sementara (tmp folder) di server hosting tidak ditemukan.",
                    UPLOAD_ERR_CANT_WRITE => "Server hosting gagal menulis berkas {$label} ke disk penyimpanan.",
                    UPLOAD_ERR_EXTENSION => "Unggahan berkas {$label} dihentikan oleh ekstensi PHP server.",
                    default => "Gagal mengunggah berkas {$label} (Error PHP: {$errCode}).",
                };
            }
            return null;
        };

        if ($errorTurnover = $detectUploadError('turnover_file', 'Turnover')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $errorTurnover], 422);
            }
            return back()->withErrors(['turnover_file' => $errorTurnover]);
        }

        if ($errorCross = $detectUploadError('file', 'Cross-Selling')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $errorCross], 422);
            }
            return back()->withErrors(['file' => $errorCross]);
        }

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
            $rules['file'] = ['file', 'mimes:xlsx,xls,csv', 'max:40960'];
        }
        if ($hasTurnover) {
            $rules['turnover_file'] = ['file', 'mimes:xlsx,xls,csv', 'max:40960'];
        }

        $messages = [
            'file.max' => 'Ukuran berkas Cross-Selling terlalu besar (maksimal 40 MB).',
            'file.mimes' => 'Format berkas Cross-Selling harus berupa .xlsx, .xls, atau .csv.',
            'turnover_file.max' => 'Ukuran berkas Turnover terlalu besar (maksimal 40 MB).',
            'turnover_file.mimes' => 'Format berkas Turnover harus berupa .xlsx, .xls, atau .csv.',
        ];

        try {
            $validated = $request->validate($rules, $messages);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                $firstError = collect($e->errors())->flatten()->first() ?? $e->getMessage();
                return response()->json(['message' => $firstError, 'errors' => $e->errors()], 422);
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
                    // Auto-matching: Cocokkan nomor HP dan item transaksi dengan data Format Keep (Closing WIN Detector)
                    try {
                        $matchStats = $keepMatcher->matchImport($cImport);
                        if (! empty($matchStats['matched_count'])) {
                            $results['keep_matches'] = $matchStats['matched_count'];
                            ActivityNotification::log(
                                title: 'Closing WIN Format Keep Otomatis',
                                message: "Auto-Match SAP: {$matchStats['matched_count']} booking/keep konsumen berhasil Closing WIN dari data Cross-Selling ({$cImport->original_name})",
                                user: $request->user(),
                                type: 'success',
                                action: 'keep_auto_match_win',
                                icon: 'check-circle'
                            );
                        }
                        if (! empty($matchStats['need_to_check_count'])) {
                            $results['keep_need_to_check'] = $matchStats['need_to_check_count'];
                            ActivityNotification::log(
                                title: 'Format Keep Perlu Kroscek (Need to Check)',
                                message: "Auto-Match SAP: {$matchStats['need_to_check_count']} transaksi No HP cocok namun barang berbeda dimasukkan ke Need to Check ({$cImport->original_name})",
                                user: $request->user(),
                                type: 'warning',
                                action: 'keep_auto_match_need_to_check',
                                icon: 'exclamation-triangle'
                            );
                        }
                    } catch (\Throwable $mErr) {
                        \Illuminate\Support\Facades\Log::warning('Keep auto-match error: ' . $mErr->getMessage());
                    }

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

            // Catat notifikasi aktivitas perubahan untuk seluruh tim
            try {
                $cUser = $request->user();
                $cUserName = $cUser ? $cUser->name : 'Superadmin';
                $cCount = number_format($cImport->row_count ?: 0, 0, ',', '.');
                ActivityNotification::log(
                    title: 'Upload Cross-Selling SAP',
                    message: "{$cUserName} berhasil mengunggah {$cCount} data Cross-Selling ({$cImport->original_name})",
                    user: $cUser,
                    type: 'success',
                    action: 'upload_cross_selling',
                    icon: 'upload'
                );
            } catch (\Throwable $e) {}

            // Sync langsung ke Workspace Sosmed
            try {
                $crossBytes = file_get_contents($validated['file']->getRealPath());
                $nowMs = (string) round(microtime(true) * 1000);
                $uploaderName = $request->user()->name ?? 'Superadmin';
                $origCrossName = $validated['file']->getClientOriginalName();

                Setting::updateOrCreate(['key' => 'workspace_croselling_mtime'], ['value' => $nowMs]);
                Setting::updateOrCreate(['key' => 'workspace_croselling_uploader'], ['value' => $uploaderName]);
                Setting::updateOrCreate(['key' => 'workspace_croselling_filename'], ['value' => $origCrossName]);
                Setting::updateOrCreate(['key' => 'workspace_croselling_filesize'], ['value' => (string) strlen($crossBytes)]);
                Setting::updateOrCreate(['key' => 'workspace_croselling_file_base64'], ['value' => base64_encode($crossBytes)]);

                $targetCross = public_path('workspace-sosmed' . DIRECTORY_SEPARATOR . 'CROSSELLING-12102026.xlsx');
                if (is_dir(dirname($targetCross))) {
                    @file_put_contents($targetCross, $crossBytes);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Workspace crosselling sync warning: ' . $e->getMessage());
            }
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

            // Catat notifikasi aktivitas perubahan untuk seluruh tim
            try {
                $tUser = $request->user();
                $tUserName = $tUser ? $tUser->name : 'Superadmin';
                $tCount = number_format($tImport->row_count ?: 0, 0, ',', '.');
                ActivityNotification::log(
                    title: 'Upload Turnover SAP',
                    message: "{$tUserName} berhasil mengunggah {$tCount} data Turnover SAP ({$tImport->original_name})",
                    user: $tUser,
                    type: 'success',
                    action: 'upload_turnover',
                    icon: 'upload'
                );
            } catch (\Throwable $e) {}

            // Sync langsung ke Workspace Sosmed
            try {
                $turnoverBytes = file_get_contents($validated['turnover_file']->getRealPath());
                $nowMs = (string) round(microtime(true) * 1000);
                $uploaderName = $request->user()->name ?? 'Superadmin';
                $origTurnoverName = $validated['turnover_file']->getClientOriginalName();

                Setting::updateOrCreate(['key' => 'workspace_turnover_mtime'], ['value' => $nowMs]);
                Setting::updateOrCreate(['key' => 'workspace_turnover_uploader'], ['value' => $uploaderName]);
                Setting::updateOrCreate(['key' => 'workspace_turnover_filename'], ['value' => $origTurnoverName]);
                Setting::updateOrCreate(['key' => 'workspace_turnover_filesize'], ['value' => (string) strlen($turnoverBytes)]);
                try {
                    Setting::updateOrCreate(['key' => 'workspace_turnover_file_base64'], ['value' => base64_encode($turnoverBytes)]);
                } catch (\Throwable $b64Err) {
                    \Illuminate\Support\Facades\Log::warning('Workspace turnover base64 warning: ' . $b64Err->getMessage());
                }

                $targetTurnover = public_path('workspace-sosmed' . DIRECTORY_SEPARATOR . 'turnover.xlsx');
                \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($targetTurnover));
                @file_put_contents($targetTurnover, $turnoverBytes);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Workspace turnover sync warning: ' . $e->getMessage());
            }
        }

        $messageParts = [];
        if (isset($results['cross_selling'])) {
            $c = $results['cross_selling'];
            $matchSuffix = '';
            if (! empty($results['keep_matches'])) {
                $matchSuffix .= " — 🎯 {$results['keep_matches']} closing WIN";
            }
            if (! empty($results['keep_need_to_check'])) {
                $matchSuffix .= ($matchSuffix !== '' ? ', ' : ' — ') . "⚠️ {$results['keep_need_to_check']} Need to Check";
            }
            $messageParts[] = "Cross-Selling ({$c['success_count']} baris{$matchSuffix})";
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
        abort_unless($import->user_id === $request->user()->id || $request->user()->isSuperAdmin(), 404);

        return response()->json([
            'status' => $import->status,
            'row_count' => $import->row_count,
            'success_count' => $import->success_count,
            'failed_count' => $import->failed_count,
        ]);
    }

    public function destroy(Request $request, CrossSellingImport $import, ?RepeatOrderPredictor $predictor = null): RedirectResponse
    {
        abort_unless($import->user_id === $request->user()->id || $request->user()->isSuperAdmin(), 404);

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $isMySql = str_contains(DB::getDriverName(), 'mysql');
        if ($isMySql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        try {
            $deletedName = $import->original_name;
            $deletedId = $import->id;
            $this->deleteSingleCrossImport($import, $isMySql);
            $this->syncRemainingCrossSelling();

            try {
                $actorName = $request->user()?->name ?? 'Superadmin';
                ActivityNotification::log(
                    title: 'Hapus Batch Cross-Selling',
                    message: "{$actorName} menghapus batch Cross-Selling #{$deletedId} ({$deletedName})",
                    user: $request->user(),
                    type: 'warning',
                    action: 'delete_cross_selling',
                    icon: 'trash'
                );
            } catch (\Throwable $e) {}
        } finally {
            if ($isMySql) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }

        return redirect()->route('imports.index')->with('success', 'Data import berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat menghapus data import.');

        $ids = $request->input('ids', []);
        if (empty($ids) || ! is_array($ids)) {
            return back()->with('error', 'Pilih minimal satu data import untuk dihapus.');
        }

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $isMySql = str_contains(DB::getDriverName(), 'mysql');
        if ($isMySql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $deletedCount = 0;
        try {
            $imports = CrossSellingImport::whereIn('id', $ids)->get();
            foreach ($imports as $import) {
                $this->deleteSingleCrossImport($import, $isMySql);
                $deletedCount++;
            }
            $this->syncRemainingCrossSelling();

            try {
                $actorName = $request->user()?->name ?? 'Superadmin';
                ActivityNotification::log(
                    title: 'Hapus Batch Cross-Selling',
                    message: "{$actorName} menghapus {$deletedCount} batch Cross-Selling secara massal",
                    user: $request->user(),
                    type: 'warning',
                    action: 'delete_cross_selling',
                    icon: 'trash'
                );
            } catch (\Throwable $e) {}
        } finally {
            if ($isMySql) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }

        return redirect()->route('imports.index')->with('success', "{$deletedCount} batch data Cross-Selling berhasil dihapus.");
    }

    public function destroyTurnover(Request $request, TurnoverImport $import): RedirectResponse
    {
        abort_unless($import->user_id === $request->user()->id || $request->user()->isSuperAdmin(), 404);

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $isMySql = str_contains(DB::getDriverName(), 'mysql');
        if ($isMySql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        try {
            $deletedName = $import->original_name;
            $deletedId = $import->id;
            $this->deleteSingleTurnoverImport($import, $isMySql);
            $this->syncRemainingTurnover();

            try {
                $actorName = $request->user()?->name ?? 'Superadmin';
                ActivityNotification::log(
                    title: 'Hapus Batch Turnover',
                    message: "{$actorName} menghapus batch Turnover #{$deletedId} ({$deletedName})",
                    user: $request->user(),
                    type: 'warning',
                    action: 'delete_turnover',
                    icon: 'trash'
                );
            } catch (\Throwable $e) {}
        } finally {
            if ($isMySql) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }

        return redirect()->route('imports.index')->with('success', 'Data import turnover berhasil dihapus.');
    }

    public function bulkDestroyTurnover(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang dapat menghapus data turnover.');

        $ids = $request->input('ids', []);
        if (empty($ids) || ! is_array($ids)) {
            return back()->with('error', 'Pilih minimal satu data turnover untuk dihapus.');
        }

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $isMySql = str_contains(DB::getDriverName(), 'mysql');
        if ($isMySql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $deletedCount = 0;
        try {
            $imports = TurnoverImport::whereIn('id', $ids)->get();
            foreach ($imports as $import) {
                $this->deleteSingleTurnoverImport($import, $isMySql);
                $deletedCount++;
            }
            $this->syncRemainingTurnover();

            try {
                $actorName = $request->user()?->name ?? 'Superadmin';
                ActivityNotification::log(
                    title: 'Hapus Batch Turnover',
                    message: "{$actorName} menghapus {$deletedCount} batch Turnover secara massal",
                    user: $request->user(),
                    type: 'warning',
                    action: 'delete_turnover',
                    icon: 'trash'
                );
            } catch (\Throwable $e) {}
        } finally {
            if ($isMySql) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }

        return redirect()->route('imports.index')->with('success', "{$deletedCount} batch data Turnover berhasil dihapus.");
    }

    protected function deleteSingleCrossImport(CrossSellingImport $import, bool $isMySql): void
    {
        $storedPath = $import->stored_path;

        RepeatOrderPrediction::where('cross_selling_import_id', $import->id)->delete();

        if ($isMySql) {
            do {
                $deleted = DB::delete('DELETE FROM cross_sellings WHERE cross_selling_import_id = ? LIMIT 25000', [$import->id]);
            } while ($deleted > 0);
        } else {
            CrossSelling::where('cross_selling_import_id', $import->id)->delete();
        }

        $import->delete();

        if ($storedPath) {
            Storage::disk('local')->delete($storedPath);
        }
    }

    protected function syncRemainingCrossSelling(): void
    {
        $latestRemaining = CrossSellingImport::where('status', 'completed')->latest()->first();
        if ($latestRemaining && $latestRemaining->stored_path && Storage::disk('local')->exists($latestRemaining->stored_path)) {
            try {
                $bytes = Storage::disk('local')->get($latestRemaining->stored_path);
                $nowMs = (string) round(microtime(true) * 1000);
                Setting::updateOrCreate(['key' => 'workspace_croselling_mtime'], ['value' => $nowMs]);
                Setting::updateOrCreate(['key' => 'workspace_croselling_filename'], ['value' => $latestRemaining->original_name]);
                Setting::updateOrCreate(['key' => 'workspace_croselling_filesize'], ['value' => (string) strlen($bytes)]);
                Setting::updateOrCreate(['key' => 'workspace_croselling_file_base64'], ['value' => base64_encode($bytes)]);
                $targetCross = public_path('workspace-sosmed' . DIRECTORY_SEPARATOR . 'CROSSELLING-12102026.xlsx');
                if (is_dir(dirname($targetCross))) {
                    @file_put_contents($targetCross, $bytes);
                }
            } catch (\Throwable $e) {}
        } elseif (! $latestRemaining) {
            Setting::whereIn('key', [
                'workspace_croselling_mtime',
                'workspace_croselling_uploader',
                'workspace_croselling_filename',
                'workspace_croselling_filesize',
                'workspace_croselling_file_base64',
            ])->delete();
        }
    }

    protected function deleteSingleTurnoverImport(TurnoverImport $import, bool $isMySql): void
    {
        $storedPath = $import->stored_path;

        if ($isMySql) {
            do {
                $deleted = DB::delete('DELETE FROM turnovers WHERE turnover_import_id = ? LIMIT 25000', [$import->id]);
            } while ($deleted > 0);
        } else {
            Turnover::where('turnover_import_id', $import->id)->delete();
        }

        $import->delete();

        if ($storedPath) {
            Storage::disk('local')->delete($storedPath);
        }
    }

    protected function syncRemainingTurnover(): void
    {
        $latestRemaining = TurnoverImport::where('status', 'completed')->latest()->first();
        if ($latestRemaining && $latestRemaining->stored_path && Storage::disk('local')->exists($latestRemaining->stored_path)) {
            try {
                $bytes = Storage::disk('local')->get($latestRemaining->stored_path);
                $nowMs = (string) round(microtime(true) * 1000);
                Setting::updateOrCreate(['key' => 'workspace_turnover_mtime'], ['value' => $nowMs]);
                Setting::updateOrCreate(['key' => 'workspace_turnover_filename'], ['value' => $latestRemaining->original_name]);
                Setting::updateOrCreate(['key' => 'workspace_turnover_filesize'], ['value' => (string) strlen($bytes)]);
                Setting::updateOrCreate(['key' => 'workspace_turnover_file_base64'], ['value' => base64_encode($bytes)]);
                $targetTurnover = public_path('workspace-sosmed' . DIRECTORY_SEPARATOR . 'turnover.xlsx');
                if (is_dir(dirname($targetTurnover))) {
                    @file_put_contents($targetTurnover, $bytes);
                }
            } catch (\Throwable $e) {}
        } elseif (! $latestRemaining) {
            Setting::whereIn('key', [
                'workspace_turnover_mtime',
                'workspace_turnover_uploader',
                'workspace_turnover_filename',
                'workspace_turnover_filesize',
                'workspace_turnover_file_base64',
            ])->delete();
        }
    }
}
