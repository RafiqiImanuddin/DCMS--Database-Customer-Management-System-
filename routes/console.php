<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto Follow-Up H+1 Format Keep ke WhatsApp PIC Sales (Jadwal Dinamis dari Dashboard Pengaturan)
$autoFuTime = '09:00';
try {
    $autoFuTime = \App\Models\Setting::get('keep_auto_fu_h1_time', '09:00') ?: '09:00';
} catch (\Throwable) {
    $autoFuTime = '09:00';
}

Schedule::command('keep:send-auto-fu-h1')
    ->dailyAt($autoFuTime)
    ->when(function () {
        try {
            return \App\Models\Setting::get('keep_auto_fu_h1_enabled', '1') === '1';
        } catch (\Throwable) {
            return false;
        }
    })
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/keep_auto_fu_h1.log'));

