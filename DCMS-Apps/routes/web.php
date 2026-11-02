<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BroadcastReportController;
use App\Http\Controllers\CrossSellingImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RepeatOrderPredictionController;
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
    Route::get('/imports', [CrossSellingImportController::class, 'index'])->name('imports.index');
    Route::post('/imports', [CrossSellingImportController::class, 'store'])->name('imports.store');
    Route::get('/predictions', RepeatOrderPredictionController::class)->name('predictions.index');
    Route::get('/broadcasts', [BroadcastReportController::class, 'index'])->name('broadcasts.index');
    Route::post('/predictions/{prediction}/broadcast', [BroadcastReportController::class, 'store'])->name('broadcasts.store');
});
