<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto Follow-Up H+1 Format Keep Strict 1x24 Jam Presisi ke WhatsApp PIC Sales (Jam Kerja 08:00 - 22:00 WITA)
Schedule::command('keep:send-auto-fu-h1')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/keep_auto_fu_h1.log'));

