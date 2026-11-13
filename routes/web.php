<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BroadcastMediaController;
use App\Http\Controllers\BroadcastReportController;
use App\Http\Controllers\BroadcastTemplateController;
use App\Http\Controllers\CrossSellingImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeSettingController;
use App\Http\Controllers\EncryptedRouteController;
use App\Http\Controllers\GuidanceController;
use App\Http\Controllers\LeadCustomerController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\RepeatOrderPredictionController;
use App\Http\Controllers\SalesMonitoringController;
use App\Http\Controllers\WatzapSettingController;
use App\Http\Controllers\WorkspaceSosmedController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

// Endpoint publik untuk akses file media broadcast (agar WatZap server dapat mengunduh gambar)
Route::get('/media/broadcasts/{media}/{filename?}', [BroadcastMediaController::class, 'show'])->name('broadcasts.media.show');

// Live Dashboard Publik (Views Only: Report Matrix Sosmed & Jadwal Shift Karyawan - Tanpa Login)
Route::get('/public-dashboard', [\App\Http\Controllers\PublicDashboardController::class, 'index'])->name('public.dashboard');
Route::get('/display', [\App\Http\Controllers\PublicDashboardController::class, 'index']);
Route::get('/live-dashboard', [\App\Http\Controllers\PublicDashboardController::class, 'index']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1');
    Route::match(['get', 'post'], '/login/guest', [AuthController::class, 'loginAsGuest'])->name('login.guest');
    Route::get('/register', [\App\Http\Controllers\UserController::class, 'create'])->name('register');
    Route::post('/register', [\App\Http\Controllers\UserController::class, 'store'])->name('register.store')->middleware('throttle:5,1');
    Route::get('/register/verify-otp', [\App\Http\Controllers\UserController::class, 'showOtpForm'])->name('register.otp.form');
    Route::post('/register/verify-otp', [\App\Http\Controllers\UserController::class, 'verifyOtp'])->name('register.otp.verify')->middleware('throttle:10,1');
    Route::post('/register/resend-otp', [\App\Http\Controllers\UserController::class, 'resendOtp'])->name('register.otp.resend')->middleware('throttle:3,1');
    Route::post('/register/cancel', [\App\Http\Controllers\UserController::class, 'cancelRegistration'])->name('register.cancel');
});

Route::middleware('auth')->group(function () {
    // Gateway Routing Terenkripsi (Semua modul DCMS & Workspace)
    Route::get('/r/{token}', EncryptedRouteController::class)->name('encrypted.route');
    Route::get('/app/{token}', EncryptedRouteController::class);

    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Dashboard Repeat Order dialihkan langsung ke Master Customer
    Route::get('/repeat-orders', fn () => redirect()->route('predictions.index'))->name('repeat-orders.dashboard');

    // Fitur Import Data (Khusus Superadmin)
    Route::get('/imports/template', [CrossSellingImportController::class, 'template'])->name('imports.template');
    Route::get('/imports/template-turnover', [CrossSellingImportController::class, 'templateTurnover'])->name('imports.template_turnover');
    Route::get('/imports', [CrossSellingImportController::class, 'index'])->name('imports.index');
    Route::post('/imports', [CrossSellingImportController::class, 'store'])->name('imports.store');
    Route::get('/imports/{import}/status', [CrossSellingImportController::class, 'status'])->name('imports.status');
    Route::delete('/imports/bulk', [CrossSellingImportController::class, 'bulkDestroy'])->name('imports.bulk_destroy');
    Route::delete('/imports/turnover-bulk', [CrossSellingImportController::class, 'bulkDestroyTurnover'])->name('imports.bulk_destroy_turnover');
    Route::delete('/imports/{import}', [CrossSellingImportController::class, 'destroy'])->name('imports.destroy');
    Route::delete('/imports/turnover/{import}', [CrossSellingImportController::class, 'destroyTurnover'])->name('imports.destroy_turnover');
    Route::get('/imports/{import}', fn () => redirect()->route('imports.index'));
    Route::get('/imports/turnover/{import}', fn () => redirect()->route('imports.index'));

    // Fitur Data Master Customer & Download (Sudah Terpadu dengan Repeat Order Dashboard)
    Route::get('/predictions/download', [RepeatOrderPredictionController::class, 'download'])->name('predictions.download');
    Route::post('/predictions/rebuild', [RepeatOrderPredictionController::class, 'rebuild'])->name('predictions.rebuild');
    Route::get('/predictions', RepeatOrderPredictionController::class)->name('predictions.index');
    Route::post('/predictions/assign', [LeadCustomerController::class, 'assign'])->name('predictions.assign');

    // Fitur Leads Customer & Download
    Route::get('/leads/download', [LeadCustomerController::class, 'download'])->name('leads.download');
    Route::get('/leads', [LeadCustomerController::class, 'index'])->name('leads.index');
    Route::delete('/leads/bulk', [LeadCustomerController::class, 'bulkDestroy'])->name('leads.bulkDestroy');
    Route::delete('/leads/{lead}', [LeadCustomerController::class, 'destroy'])->name('leads.destroy');

    // Fitur Broadcast, Template & Download
    Route::get('/broadcasts/templates', [BroadcastTemplateController::class, 'index'])->name('broadcasts.templates.index');
    Route::post('/broadcasts/templates', [BroadcastTemplateController::class, 'store'])->name('broadcasts.templates.store');
    Route::put('/broadcasts/templates/{template}', [BroadcastTemplateController::class, 'update'])->name('broadcasts.templates.update');
    Route::delete('/broadcasts/templates/{template}', [BroadcastTemplateController::class, 'destroy'])->name('broadcasts.templates.destroy');

    Route::get('/broadcasts', [BroadcastReportController::class, 'index'])->name('broadcasts.index');
    Route::get('/broadcasts/download', [BroadcastReportController::class, 'download'])->name('broadcasts.download');
    Route::get('/broadcasts/batches/{batch}', [BroadcastReportController::class, 'showBatch'])->name('broadcasts.batches.show');
    Route::get('/broadcasts/batches/{batch}/download', [BroadcastReportController::class, 'downloadBatch'])->name('broadcasts.batches.download');

    // Menu Pricelist & Skema Gadget (Superadmin View-Only)
    Route::get('/pricelist', [\App\Http\Controllers\PricelistController::class, 'index'])->name('pricelist.index');
    Route::post('/pricelist/refresh', [\App\Http\Controllers\PricelistController::class, 'updateFromExcel'])->name('pricelist.refresh');
    Route::post('/pricelist/sync-dropbox', [\App\Http\Controllers\PricelistController::class, 'syncDropbox'])->name('pricelist.sync_dropbox');
    Route::get('/pricelist/check-status', [\App\Http\Controllers\PricelistController::class, 'checkDropboxStatus'])->name('pricelist.check_status');
    Route::delete('/broadcasts/batches/{batch}', [BroadcastReportController::class, 'destroyBatch'])->name('broadcasts.batches.destroy');
    Route::post('/broadcasts/bulk', [BroadcastReportController::class, 'bulkStore'])->name('broadcasts.bulk');
    Route::post('/broadcasts/settings', [BroadcastReportController::class, 'updateSettings'])->name('broadcasts.settings.update');
    Route::post('/broadcasts/reset-cycle', [BroadcastReportController::class, 'resetCycle'])->name('broadcasts.reset-cycle');
    Route::delete('/broadcasts/{report}', [BroadcastReportController::class, 'destroy'])->name('broadcasts.destroy');
    Route::post('/predictions/{prediction}/broadcast', [BroadcastReportController::class, 'store'])->name('broadcasts.store');

    // Setup & Pengaturan WhatsApp (WatZap Gateway)
    Route::get('/whatsapp', [WatzapSettingController::class, 'index'])->name('whatsapp.index');
    Route::get('/settings/watzap', [WatzapSettingController::class, 'index'])->name('settings.watzap.index');
    Route::post('/settings/watzap', [WatzapSettingController::class, 'update'])->name('settings.watzap.update');
    Route::post('/settings/watzap/check', [WatzapSettingController::class, 'check'])->name('settings.watzap.check');
    Route::post('/settings/watzap/test-send', [WatzapSettingController::class, 'testSend'])->name('settings.watzap.test_send');
    Route::post('/settings/watzap/register-webhook', [WatzapSettingController::class, 'registerWebhook'])->name('settings.watzap.register_webhook');
    Route::get('/settings/watzap/get-webhook', [WatzapSettingController::class, 'getWebhookStatus'])->name('settings.watzap.get_webhook');
    Route::post('/settings/watzap/check-waha', [WatzapSettingController::class, 'checkWaha'])->name('settings.watzap.check_waha');
    Route::post('/settings/watzap/check-baileys', [WatzapSettingController::class, 'checkBaileys'])->name('settings.watzap.check_baileys');
    Route::post('/settings/watzap/check-fonnte', [WatzapSettingController::class, 'checkFonnte'])->name('settings.watzap.check_fonnte');
    Route::post('/settings/watzap/register-fonnte-webhook', [WatzapSettingController::class, 'registerFonnteWebhook'])->name('settings.watzap.register_fonnte_webhook');
    Route::post('/settings/watzap/test-send-fonnte', [WatzapSettingController::class, 'testSendFonnte'])->name('settings.watzap.test_send_fonnte');
    Route::post('/settings/watzap/sync-google-sheet', [WatzapSettingController::class, 'syncGoogleSheetKeep'])->name('settings.watzap.sync_google_sheet');

    // Dashboard Manager: Monitoring Kinerja Admin Sales (Manager & Admin)
    Route::get('/monitoring/sales', [SalesMonitoringController::class, 'index'])->name('monitoring.sales.index');
    Route::post('/monitoring/sales/assessments', [SalesMonitoringController::class, 'storeAssessment'])->name('monitoring.sales.assessment.store');
    Route::get('/monitoring/sales/assessments/{assessment}', [SalesMonitoringController::class, 'showAssessment'])->name('monitoring.sales.assessment.show');
    Route::get('/monitoring/sales/export', [SalesMonitoringController::class, 'export'])->name('monitoring.sales.export');
    Route::post('/monitoring/sales/auto-sync', [SalesMonitoringController::class, 'autoSyncMetrics'])->name('monitoring.sales.auto_sync');

    // Dashboard Manager: Pembuat Surat Penawaran Harga
    Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
    Route::get('/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
    Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
    Route::get('/quotations/pricelist/search', [QuotationController::class, 'searchPricelist'])->name('quotations.pricelist.search');
    Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
    Route::get('/quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
    Route::put('/quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
    Route::delete('/quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
    Route::post('/quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('quotations.duplicate');
    Route::get('/quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');

    // Dashboard Manager: Kelola Jadwal Shift Admin Sosmed
    Route::get('/shifts', [\App\Http\Controllers\SosmedShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts', [\App\Http\Controllers\SosmedShiftController::class, 'store'])->name('shifts.store');
    Route::post('/shifts/batch', [\App\Http\Controllers\SosmedShiftController::class, 'batchStore'])->name('shifts.batch');
    Route::put('/shifts/{shift}', [\App\Http\Controllers\SosmedShiftController::class, 'update'])->name('shifts.update');
    Route::delete('/shifts/{shift}', [\App\Http\Controllers\SosmedShiftController::class, 'destroy'])->name('shifts.destroy');

    // Pengaturan Data Karyawan & Bobot Penilaian KPI
    Route::get('/settings/karyawan', [EmployeeSettingController::class, 'index'])->name('settings.karyawan.index');
    Route::post('/settings/karyawan', [EmployeeSettingController::class, 'storeEmployee'])->name('settings.karyawan.store');
    Route::put('/settings/karyawan/{employee}', [EmployeeSettingController::class, 'updateEmployee'])->name('settings.karyawan.update');
    Route::delete('/settings/karyawan/{employee}', [EmployeeSettingController::class, 'destroyEmployee'])->name('settings.karyawan.destroy');
    Route::post('/settings/kpi-weights', [EmployeeSettingController::class, 'updateKpiWeights'])->name('settings.kpi_weights.update');
    Route::post('/settings/kpi-criteria', [EmployeeSettingController::class, 'storeKpiCriterion'])->name('settings.kpi_criteria.store');
    Route::delete('/settings/kpi-criteria/{criterion}', [EmployeeSettingController::class, 'destroyKpiCriterion'])->name('settings.kpi_criteria.destroy');
    Route::post('/settings/monthly-targets', [EmployeeSettingController::class, 'updateMonthlyTargets'])->name('settings.monthly_targets.update');

    // Guidance & SOP (Rumus Potensi & Penilaian untuk Superadmin, Guidance Umum untuk Staff)
    Route::get('/guidance', [GuidanceController::class, 'index'])->name('guidance.index');
    Route::post('/guidance/dismiss', [GuidanceController::class, 'dismissLoginPrompt'])->name('guidance.dismiss');

    // Manajemen Akun & Checklist Hak Akses Fitur (Khusus Superadmin)
    Route::get('/settings/users', [\App\Http\Controllers\UserController::class, 'manageUsers'])->name('settings.users.index');
    Route::post('/settings/users', [\App\Http\Controllers\UserController::class, 'storeByAdmin'])->name('settings.users.store');
    Route::put('/settings/users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'updatePermissions'])->name('settings.users.permissions.update');
    Route::delete('/settings/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('settings.users.destroy');

    // Pengaturan Akun Sendiri (Untuk semua user yang login)
    Route::get('/settings/profile', [\App\Http\Controllers\UserController::class, 'profile'])->name('profile.edit');
    Route::put('/settings/profile', [\App\Http\Controllers\UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/settings/profile/password', [\App\Http\Controllers\UserController::class, 'updatePassword'])->name('profile.password');
    Route::post('/settings/profile/toggle-live-chat-widget', [\App\Http\Controllers\UserController::class, 'toggleLiveChatWidget'])->name('settings.profile.toggle_live_chat_widget');

    // Modul Terintegrasi: Workspace Sosmed (Sosmed KPI, Report, Cek AR, Keep & Follow-Up)
    Route::get('/workspace-sosmed/{token?}', [WorkspaceSosmedController::class, 'index'])->name('workspace-sosmed.index');
});

// Workspace Sosmed API Endpoints (REPORT Engine)
$registerSosmedApiRoutes = function () {
    Route::get('/database-status', [WorkspaceSosmedController::class, 'databaseStatus'])->name('api.sosmed.database_status');
    Route::get('/workspace-settings', [WorkspaceSosmedController::class, 'workspaceSettings'])->name('api.sosmed.workspace_settings');
    Route::match(['get', 'post'], '/monthly-targets', [WorkspaceSosmedController::class, 'monthlyTargets'])->name('api.sosmed.monthly_targets');
    Route::match(['get', 'post'], '/sales-photos', [WorkspaceSosmedController::class, 'salesPhotos'])->name('api.sosmed.sales_photos');
    Route::match(['get', 'post'], '/monthly-archives', [WorkspaceSosmedController::class, 'monthlyArchives'])->name('api.sosmed.monthly_archives');
    Route::get('/sync-keep', [WorkspaceSosmedController::class, 'syncKeep'])->name('api.sosmed.sync_keep');
    Route::match(['get', 'post'], '/update-keep-status', [WorkspaceSosmedController::class, 'updateKeepStatus'])->name('api.sosmed.update_keep_status');
    Route::match(['get', 'post'], '/update-report-ar', [WorkspaceSosmedController::class, 'updateReportAr'])->name('api.sosmed.update_report_ar');
    Route::match(['get', 'post'], '/assign-keep-fu', [WorkspaceSosmedController::class, 'assignKeepFU'])->name('api.sosmed.assign_keep_fu');
    Route::match(['get', 'post'], '/api/assign-keep-fu', [WorkspaceSosmedController::class, 'assignKeepFU'])->name('api.sosmed.assign_keep_fu_alias');
    Route::match(['get', 'post', 'delete'], '/delete-keep', [WorkspaceSosmedController::class, 'deleteKeep'])->name('api.sosmed.delete_keep');
    Route::match(['get', 'post', 'delete'], '/api/delete-keep', [WorkspaceSosmedController::class, 'deleteKeep'])->name('api.sosmed.delete_keep_alias');
    Route::match(['get', 'post'], '/toggle-keep-delete', [WorkspaceSosmedController::class, 'toggleKeepDeleteFeature'])->name('api.sosmed.toggle_keep_delete');
    Route::match(['get', 'post'], '/api/toggle-keep-delete', [WorkspaceSosmedController::class, 'toggleKeepDeleteFeature'])->name('api.sosmed.toggle_keep_delete_alias');
    Route::match(['get', 'post'], '/webhook/fonnte-keep', [\App\Http\Controllers\Api\SosmedKeepWebhookController::class, 'handle'])->name('api.sosmed.webhook_fonnte_keep');
    Route::match(['get', 'post'], '/fonnte-webhook', [\App\Http\Controllers\Api\SosmedKeepWebhookController::class, 'handle'])->name('api.fonnte_webhook');
    Route::match(['get', 'post'], '/api/fonnte-webhook', [\App\Http\Controllers\Api\SosmedKeepWebhookController::class, 'handle'])->name('api.fonnte_webhook_alias');
    Route::get('/sosmed-keeps', [\App\Http\Controllers\Api\SosmedKeepWebhookController::class, 'getKeeps'])->name('api.sosmed.get_keeps');
    Route::get('/sosmed-keeps/latest', [\App\Http\Controllers\Api\SosmedKeepWebhookController::class, 'getLatestKeeps'])->name('api.sosmed.get_latest_keeps');
    Route::get('/api/sosmed-keeps/latest', [\App\Http\Controllers\Api\SosmedKeepWebhookController::class, 'getLatestKeeps'])->name('api.sosmed.get_latest_keeps_alias');
    Route::post('/chat-ai', [WorkspaceSosmedController::class, 'chatAi'])->name('api.sosmed.chat_ai');
    Route::post('/watzap-send', [WorkspaceSosmedController::class, 'watzapSend'])->name('api.sosmed.watzap_send');
    Route::match(['get', 'post'], '/watzap-webhook', [WorkspaceSosmedController::class, 'watzapWebhook'])->name('api.sosmed.watzap_webhook');
    Route::match(['get', 'post', 'delete'], '/baileys-session', [WorkspaceSosmedController::class, 'baileysSession'])->name('api.sosmed.baileys_session');
    Route::get('/watzap-status', [WorkspaceSosmedController::class, 'watzapStatus'])->name('api.sosmed.watzap_status');
    Route::match(['get', 'post'], '/omni-resolve', [WorkspaceSosmedController::class, 'omniResolve'])->name('api.sosmed.omni_resolve');
    Route::get('/watzap-media', [WorkspaceSosmedController::class, 'watzapMedia'])->name('api.sosmed.watzap_media');
    Route::post('/watzap-simulate-receive', [WorkspaceSosmedController::class, 'watzapSimulateReceive'])->name('api.sosmed.watzap_simulate_receive');
    Route::match(['get', 'delete'], '/get-excel', [WorkspaceSosmedController::class, 'getExcel'])->name('api.sosmed.get_excel');
    Route::post('/save-excel', [WorkspaceSosmedController::class, 'saveExcel'])->name('api.sosmed.save_excel');
    Route::post('/upload-excel-chunk', [WorkspaceSosmedController::class, 'uploadExcelChunk'])->name('api.sosmed.upload_excel_chunk');
    Route::get('/search-ar', [WorkspaceSosmedController::class, 'searchAr'])->name('api.sosmed.search_ar');
    Route::get('/turnover-records', [WorkspaceSosmedController::class, 'turnoverRecords'])->name('api.sosmed.turnover_records');
    Route::get('/croselling-records', [WorkspaceSosmedController::class, 'crosellingRecords'])->name('api.sosmed.croselling_records');
    Route::get('/notifications', [\App\Http\Controllers\ActivityNotificationController::class, 'index'])->name('api.notifications.index');
    Route::delete('/notifications', [\App\Http\Controllers\ActivityNotificationController::class, 'clear'])->name('api.notifications.clear');
};

Route::prefix('api')->group($registerSosmedApiRoutes);
$registerSosmedApiRoutes();

// Direct notification endpoint for global navbar
Route::get('/api/notifications', [\App\Http\Controllers\ActivityNotificationController::class, 'index'])->name('api.notifications.direct_index');
Route::delete('/api/notifications', [\App\Http\Controllers\ActivityNotificationController::class, 'clear'])->name('api.notifications.direct_clear');

// Route Helper untuk migrasi database & clear cache via browser (solusi cPanel tanpa Terminal)
Route::get('/system/migrate', function (\Illuminate\Http\Request $request) {
    if ($request->query('key') !== 'dcms2026') {
        abort(403, 'Akses Ditolak. Gunakan parameter key yang benar: ?key=dcms2026');
    }

    $action = strtolower((string) $request->query('action', 'migrate'));
    $actionOutput = '';
    $actionTitle = 'DATABASE MIGRATION & OPTIMIZATION';

    try {
        if ($action === 'import-keeps') {
            $actionTitle = 'IMPOR DATA KEEP DARI CSV';
            \Illuminate\Support\Facades\Artisan::call('keep:import-csv', ['--truncate' => true]);
            $actionOutput = \Illuminate\Support\Facades\Artisan::output();
        } elseif ($action === 'sync-google-sheet') {
            $actionTitle = 'SINKRONISASI DATA KEEP DARI GOOGLE SPREADSHEET';
            \Illuminate\Support\Facades\Artisan::call('keep:sync-google-sheet', ['--truncate' => true]);
            $actionOutput = \Illuminate\Support\Facades\Artisan::output();
        } elseif ($action === 'export-core') {
            $actionTitle = 'EKSPOR CORE DATABASE SQL & DATA KEEP';
            \Illuminate\Support\Facades\Artisan::call('db:export-cpanel');
            $actionOutput = \Illuminate\Support\Facades\Artisan::output();
        } elseif ($action === 'match-cross-selling') {
            $actionTitle = 'PENCOCOKAN FORMAT KEEP VS CROSS-SELLING (CLOSING WIN)';
            \Illuminate\Support\Facades\Artisan::call('keep:match-cross-selling');
            $actionOutput = \Illuminate\Support\Facades\Artisan::output();
        } elseif ($action === 'clean-duplicate-keeps') {
            $actionTitle = 'PEMBERSIHAN DUPLIKASI DATA FORMAT KEEP';
            $duplicates = \App\Models\SosmedKeep::whereNotNull('nomor_wa')
                ->where('nomor_wa', '!=', '')
                ->select('nomor_wa', 'barang_keep', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
                ->groupBy('nomor_wa', 'barang_keep')
                ->having('total', '>', 1)
                ->get();

            $deletedCount = 0;
            foreach ($duplicates as $dup) {
                $rows = \App\Models\SosmedKeep::where('nomor_wa', $dup->nomor_wa)
                    ->where('barang_keep', $dup->barang_keep)
                    ->orderByDesc('id')
                    ->get();

                // Cari baris yang paling lengkap (misal yang sudah di-claim ON PROGRESS / WIN)
                $keepRow = $rows->first(fn ($r) => in_array(strtoupper((string) $r->status), ['ON PROGRESS', 'WIN', 'LOSE']) || ! empty($r->nomor_fu))
                    ?? $rows->first();

                foreach ($rows as $r) {
                    if ($r->id !== $keepRow->id) {
                        $r->delete();
                        $deletedCount++;
                    }
                }
            }
            $actionOutput = "Berhasil membersihkan {$deletedCount} baris data keep duplikat!\nData keep yang memiliki status aktif (On Progress / Win) dan nomor follow up tetap dipertahankan.";
        } else {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = \Illuminate\Support\Facades\Artisan::output();

            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            $clearOutput = \Illuminate\Support\Facades\Artisan::output();

            $actionOutput = ($migrateOutput ?: "Semua tabel database sudah yang terbaru (Nothing to migrate).\n")
                . "\n🧹 === CLEAR CACHE & OPTIMIZATION ===\n\n" . $clearOutput;
        }

        $totalKeeps = \Illuminate\Support\Facades\Schema::hasTable('sosmed_keeps')
            ? \Illuminate\Support\Facades\DB::table('sosmed_keeps')->count()
            : 0;
        $totalUsers = \Illuminate\Support\Facades\Schema::hasTable('users')
            ? \Illuminate\Support\Facades\DB::table('users')->count()
            : 0;

        $phpInfo = sprintf(
            "PHP Version       : %s\nupload_max_filesize: %s\npost_max_size      : %s\nmemory_limit       : %s\nmax_execution_time : %ss",
            PHP_VERSION,
            ini_get('upload_max_filesize') ?: 'N/A',
            ini_get('post_max_size') ?: 'N/A',
            ini_get('memory_limit') ?: 'N/A',
            ini_get('max_execution_time') ?: 'N/A'
        );

        $actionsHtml = <<<HTML
<div style="margin: 20px 0; display: flex; gap: 10px; flex-wrap: wrap;">
    <a href="?key=dcms2026&action=migrate" style="background:#2563eb; color:#fff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:bold; font-size:13px;">⚡ 1. Jalankan Migrasi Skema</a>
    <a href="?key=dcms2026&action=import-keeps" style="background:#059669; color:#fff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:bold; font-size:13px;">📥 2. Impor 13k+ Data Keep dari CSV</a>
    <a href="?key=dcms2026&action=sync-google-sheet" style="background:#d97706; color:#fff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:bold; font-size:13px;">🔄 3. Sinkronisasi Langsung dari Google Sheet</a>
    <a href="?key=dcms2026&action=match-cross-selling" style="background:#7c3aed; color:#fff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:bold; font-size:13px;">🎯 4. Cocokkan Keep vs Cross-Selling</a>
    <a href="?key=dcms2026&action=clean-duplicate-keeps" style="background:#e11d48; color:#fff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:bold; font-size:13px;">🧹 5. Bersihkan Data Keep Duplikat</a>
    <a href="?key=dcms2026&action=export-core" style="background:#4b5563; color:#fff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:bold; font-size:13px;">📦 6. Generate Ulang Core Dump</a>
</div>
HTML;

        return response('<div style="background:#0f172a; color:#f8fafc; padding:30px; font-family:monospace; font-size:14px; border-radius:12px; line-height:1.6; max-width:900px; margin:20px auto; box-shadow:0 10px 25px -5px rgba(0,0,0,0.5);">'
            . '<h2 style="color:#38bdf8; margin:0 0 10px 0;">🚀 DCMS DATABASE MANAGEMENT CONSOLE</h2>'
            . '<p style="color:#94a3b8; margin:0 0 15px 0;">Panel kontrol migrasi mandiri dan migrasi data untuk hosting cPanel.</p>'
            . $actionsHtml
            . '<div style="background:#1e293b; padding:15px; border-radius:8px; margin:15px 0; border-left:4px solid #10b981;">'
            . '📊 <strong>Status Database:</strong> ' . number_format($totalUsers) . ' Pengguna | ' . number_format($totalKeeps) . ' Data Keep Tercatat'
            . '</div>'
            . '<h3 style="color:#10b981; margin:20px 0 10px 0;">=== ' . $actionTitle . ' ===</h3>'
            . '<pre style="color:#a7f3d0; background:#020617; padding:16px; border-radius:8px; overflow-x:auto;">' . htmlspecialchars($actionOutput) . '</pre>'
            . '<h3 style="color:#94a3b8; margin:20px 0 10px 0;">⚙️ === SERVER ENVIRONMENT ===</h3>'
            . '<pre style="color:#cbd5e1; background:#020617; padding:16px; border-radius:8px; overflow-x:auto;">' . htmlspecialchars($phpInfo) . '</pre>'
            . '<div style="margin-top:20px; color:#10b981; font-weight:bold;">✅ PROSES SELESAI DENGAN SUKSES!</div>'
            . '</div>');
    } catch (\Throwable $e) {
        return response('<div style="background:#0f172a; color:#ef4444; padding:30px; font-family:monospace; font-size:14px; border-radius:12px; line-height:1.6; max-width:900px; margin:20px auto;">'
            . '<h2 style="color:#f87171; margin:0 0 10px 0;">❌ TERJADI KESALAHAN SAAT MIGRASI</h2>'
            . '<pre style="color:#fca5a5; background:#020617; padding:16px; border-radius:8px; overflow-x:auto;">' . htmlspecialchars($e->getMessage()) . '</pre>'
            . '</div>', 500);
    }
})->name('system.migrate');

