<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BroadcastReportController;
use App\Http\Controllers\CrossSellingImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadCustomerController;
use App\Http\Controllers\RepeatOrderPredictionController;
use App\Http\Controllers\WatzapSettingController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Fitur Import Data (Khusus Superadmin)
    Route::get('/imports/template', [CrossSellingImportController::class, 'template'])->name('imports.template');
    Route::get('/imports', [CrossSellingImportController::class, 'index'])->name('imports.index');
    Route::post('/imports', [CrossSellingImportController::class, 'store'])->name('imports.store');
    Route::get('/imports/{import}/status', [CrossSellingImportController::class, 'status'])->name('imports.status');
    Route::delete('/imports/{import}', [CrossSellingImportController::class, 'destroy'])->name('imports.destroy');

    // Fitur Data Master Customer & Download
    Route::get('/predictions/download', [RepeatOrderPredictionController::class, 'download'])->name('predictions.download');
    Route::get('/predictions', RepeatOrderPredictionController::class)->name('predictions.index');
    Route::post('/predictions/assign', [LeadCustomerController::class, 'assign'])->name('predictions.assign');

    // Fitur Leads Customer & Download
    Route::get('/leads/download', [LeadCustomerController::class, 'download'])->name('leads.download');
    Route::get('/leads', [LeadCustomerController::class, 'index'])->name('leads.index');

    // Fitur Broadcast & Download
    Route::get('/broadcasts', [BroadcastReportController::class, 'index'])->name('broadcasts.index');
    Route::get('/broadcasts/download', [BroadcastReportController::class, 'download'])->name('broadcasts.download');
    Route::post('/broadcasts/bulk', [BroadcastReportController::class, 'bulkStore'])->name('broadcasts.bulk');
    Route::delete('/broadcasts/{report}', [BroadcastReportController::class, 'destroy'])->name('broadcasts.destroy');
    Route::post('/predictions/{prediction}/broadcast', [BroadcastReportController::class, 'store'])->name('broadcasts.store');

    // WhatsApp Web & Team Chat (Super Admin & Staff)
    Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');

    // Pengaturan Gateway WatZap (Khusus Superadmin)
    Route::get('/settings/watzap', [WatzapSettingController::class, 'index'])->name('settings.watzap.index');
    Route::post('/settings/watzap', [WatzapSettingController::class, 'update'])->name('settings.watzap.update');
    Route::post('/settings/watzap/check', [WatzapSettingController::class, 'check'])->name('settings.watzap.check');
    Route::post('/settings/watzap/test-send', [WatzapSettingController::class, 'testSend'])->name('settings.watzap.test_send');
});
