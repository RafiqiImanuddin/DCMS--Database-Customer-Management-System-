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

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1');
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

    // Monitoring Kinerja Admin Sales (Manager & Admin)
    Route::get('/monitoring/sales', [SalesMonitoringController::class, 'index'])->name('monitoring.sales.index');
    Route::post('/monitoring/sales/assessments', [SalesMonitoringController::class, 'storeAssessment'])->name('monitoring.sales.assessment.store');
    Route::get('/monitoring/sales/assessments/{assessment}', [SalesMonitoringController::class, 'showAssessment'])->name('monitoring.sales.assessment.show');
    Route::get('/monitoring/sales/export', [SalesMonitoringController::class, 'export'])->name('monitoring.sales.export');
    Route::post('/monitoring/sales/auto-sync', [SalesMonitoringController::class, 'autoSyncMetrics'])->name('monitoring.sales.auto_sync');

    // Pengaturan Data Karyawan & Bobot Penilaian KPI
    Route::get('/settings/karyawan', [EmployeeSettingController::class, 'index'])->name('settings.karyawan.index');
    Route::post('/settings/karyawan', [EmployeeSettingController::class, 'storeEmployee'])->name('settings.karyawan.store');
    Route::put('/settings/karyawan/{employee}', [EmployeeSettingController::class, 'updateEmployee'])->name('settings.karyawan.update');
    Route::delete('/settings/karyawan/{employee}', [EmployeeSettingController::class, 'destroyEmployee'])->name('settings.karyawan.destroy');
    Route::post('/settings/kpi-weights', [EmployeeSettingController::class, 'updateKpiWeights'])->name('settings.kpi_weights.update');
    Route::post('/settings/kpi-criteria', [EmployeeSettingController::class, 'storeKpiCriterion'])->name('settings.kpi_criteria.store');
    Route::delete('/settings/kpi-criteria/{criterion}', [EmployeeSettingController::class, 'destroyKpiCriterion'])->name('settings.kpi_criteria.destroy');

    // Guidance & SOP (Rumus Potensi & Penilaian untuk Superadmin, Guidance Umum untuk Staff)
    Route::get('/guidance', [GuidanceController::class, 'index'])->name('guidance.index');
    Route::post('/guidance/dismiss', [GuidanceController::class, 'dismissLoginPrompt'])->name('guidance.dismiss');

    // Modul Terintegrasi: Workspace Sosmed (Sosmed KPI, Report, Cek AR, Keep & Follow-Up)
    Route::get('/workspace-sosmed/{token?}', [WorkspaceSosmedController::class, 'index'])->name('workspace-sosmed.index');
});

// Workspace Sosmed API Endpoints (REPORT Engine)
$registerSosmedApiRoutes = function () {
    Route::get('/database-status', [WorkspaceSosmedController::class, 'databaseStatus'])->name('api.sosmed.database_status');
    Route::match(['get', 'post'], '/monthly-targets', [WorkspaceSosmedController::class, 'monthlyTargets'])->name('api.sosmed.monthly_targets');
    Route::match(['get', 'post'], '/sales-photos', [WorkspaceSosmedController::class, 'salesPhotos'])->name('api.sosmed.sales_photos');
    Route::match(['get', 'post'], '/monthly-archives', [WorkspaceSosmedController::class, 'monthlyArchives'])->name('api.sosmed.monthly_archives');
    Route::get('/sync-keep', [WorkspaceSosmedController::class, 'syncKeep'])->name('api.sosmed.sync_keep');
    Route::match(['get', 'post'], '/update-keep-status', [WorkspaceSosmedController::class, 'updateKeepStatus'])->name('api.sosmed.update_keep_status');
    Route::match(['get', 'post'], '/update-report-ar', [WorkspaceSosmedController::class, 'updateReportAr'])->name('api.sosmed.update_report_ar');
    Route::post('/chat-ai', [WorkspaceSosmedController::class, 'chatAi'])->name('api.sosmed.chat_ai');
    Route::post('/watzap-send', [WorkspaceSosmedController::class, 'watzapSend'])->name('api.sosmed.watzap_send');
    Route::match(['get', 'post'], '/watzap-webhook', [WorkspaceSosmedController::class, 'watzapWebhook'])->name('api.sosmed.watzap_webhook');
    Route::get('/watzap-status', [WorkspaceSosmedController::class, 'watzapStatus'])->name('api.sosmed.watzap_status');
    Route::match(['get', 'post'], '/omni-resolve', [WorkspaceSosmedController::class, 'omniResolve'])->name('api.sosmed.omni_resolve');
    Route::get('/watzap-media', [WorkspaceSosmedController::class, 'watzapMedia'])->name('api.sosmed.watzap_media');
    Route::post('/watzap-simulate-receive', [WorkspaceSosmedController::class, 'watzapSimulateReceive'])->name('api.sosmed.watzap_simulate_receive');
    Route::match(['get', 'delete'], '/get-excel', [WorkspaceSosmedController::class, 'getExcel'])->name('api.sosmed.get_excel');
    Route::post('/save-excel', [WorkspaceSosmedController::class, 'saveExcel'])->name('api.sosmed.save_excel');
    Route::post('/upload-excel-chunk', [WorkspaceSosmedController::class, 'uploadExcelChunk'])->name('api.sosmed.upload_excel_chunk');
    Route::get('/search-ar', [WorkspaceSosmedController::class, 'searchAr'])->name('api.sosmed.search_ar');
    Route::get('/turnover-records', [WorkspaceSosmedController::class, 'turnoverRecords'])->name('api.sosmed.turnover_records');
};

Route::prefix('api')->group($registerSosmedApiRoutes);
$registerSosmedApiRoutes();
