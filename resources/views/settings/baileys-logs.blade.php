@extends('layouts.app')
@section('title', 'Log Render Baileys & Cron H+1')

@section('content')
<div class="space-y-5" style="font-family: inherit;">
    <!-- 1. Header & Unified Action Toolbar -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-1">
        <div>
            <div class="flex items-center gap-2">
                <span id="badge-render-status" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ !empty($logsData['online']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}" style="border-radius: 9999px; padding: 2px 10px; font-size: 12px; font-weight: 600;">
                    <span class="size-2 rounded-full {{ !empty($logsData['online']) ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}" style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px;"></span>
                    <span id="text-render-status">{{ !empty($logsData['online']) ? 'Server Render Online' : 'Server Render Offline' }}</span>
                </span>
                <span class="text-xs text-slate-300">•</span>
                <span class="text-xs font-medium text-slate-500">Scheduler H+1 Strict 24 Jam Presisi</span>
            </div>
            <h1 class="mt-1 text-2xl font-black text-slate-900 tracking-tight" style="font-weight: 900; color: #0f172a; font-size: 24px;">Log Runtime Render &amp; Cron H+1</h1>
            <p class="mt-0.5 text-xs text-slate-500" style="color: #64748b; font-size: 12px;">
                Monitoring otomatisasi scheduler follow-up 24 jam, respon reaksi WhatsApp, dan status gateway runtime.
            </p>
        </div>

        <!-- Action Toolbar -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Trigger Cron -->
            <button type="button" 
                    id="btn-trigger-cron"
                    onclick="triggerCronManually(this)" 
                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white px-3.5 py-2 text-xs font-bold shadow-sm transition cursor-pointer"
                    style="border-radius: 0.75rem; background-color: #059669; color: #ffffff; padding: 8px 14px; font-weight: 700; font-size: 12px; border: none;"
                    title="Picu pengecekan antrean follow-up 24 jam sekarang">
                <svg class="size-4" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                <span>Trigger Cron</span>
            </button>

            <!-- Clear Cache cPanel -->
            <a href="{{ url('/system/migrate?key=dcms2026') }}" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 active:scale-95 px-3 py-2 text-xs font-bold text-slate-700 shadow-sm transition"
               style="border-radius: 0.75rem; border: 1px solid #e2e8f0; background-color: #ffffff; color: #334155; padding: 8px 12px; font-weight: 700; font-size: 12px; text-decoration: none;"
               title="Sinkronkan route dan bersihkan cache cPanel">
                <svg class="size-3.5 text-slate-500" style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                <span>Clear Cache</span>
            </a>

            <!-- Refresh Button -->
            <button type="button" 
                    id="btn-refresh-logs"
                    onclick="fetchRealtimeLogs(true)" 
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 active:scale-95 px-3 py-2 text-xs font-bold text-slate-700 shadow-sm transition cursor-pointer"
                    style="border-radius: 0.75rem; border: 1px solid #e2e8f0; background-color: #ffffff; color: #334155; padding: 8px 12px; font-weight: 700; font-size: 12px;"
                    title="Segarkan data log dari server Render">
                <svg id="icon-refresh-logs" class="size-3.5 text-slate-500" style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Segarkan</span>
            </button>

            <!-- Render External Console -->
            <a href="https://dashboard.render.com" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 shadow-sm transition"
               style="border-radius: 0.75rem; border: 1px solid #e2e8f0; background-color: #ffffff; color: #475569; padding: 8px 12px; font-weight: 700; font-size: 12px; text-decoration: none;"
               title="Buka console resmi Render.com">
                <span>Console</span>
                <svg class="size-3 text-slate-400" style="width: 12px; height: 12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            </a>
        </div>
    </div>

    <!-- 2. Smart Diagnostic Alert (Hanya muncul jika cron paling akhir bermasalah) -->
    <div id="diagnostic-alert" class="hidden rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-amber-900 shadow-sm transition" style="border-radius: 0.75rem; border: 1px solid #fde68a; background-color: #fefce8;">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="flex size-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700 shrink-0" style="width: 28px; height: 28px; border-radius: 0.5rem; background-color: #fef3c7; color: #b45309;">
                    <svg class="size-4" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-amber-900" style="font-weight: 700; color: #78350f;">Perhatian: </span>
                    <span id="diagnostic-message" class="text-xs text-amber-800" style="color: #92400e;"></span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ url('/system/migrate?key=dcms2026') }}" target="_blank" class="rounded-lg bg-amber-200/80 hover:bg-amber-200 px-2.5 py-1 text-[11px] font-bold text-amber-900 transition" style="border-radius: 0.5rem; background-color: #fde68a; color: #78350f; font-weight: 700; font-size: 11px; text-decoration: none; padding: 4px 10px;">
                    Sync Route cPanel
                </a>
                <button type="button" onclick="document.getElementById('diagnostic-alert').classList.add('hidden')" class="text-amber-500 hover:text-amber-800 p-1 cursor-pointer" style="background: none; border: none; cursor: pointer;">
                    <svg class="size-4" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- 3. 4 Clean & Uniform KPI Cards (Menggunakan dcms-card Resmi) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1: Server Host Render -->
        <div class="dcms-card p-5 hover:border-slate-300 transition flex flex-col justify-between" style="border-radius: 1rem; border: 1px solid #e2e8f0; background-color: #ffffff; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04); padding: 18px;">
            <div>
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400" style="font-size: 11px; font-weight: 700; color: #94a3b8; letter-spacing: 0.05em;">Host Server Render</span>
                    <div class="flex size-8 items-center justify-center rounded-xl bg-slate-50 border border-slate-100 text-slate-600" style="width: 32px; height: 32px; border-radius: 0.75rem; background-color: #f8fafc; border: 1px solid #f1f5f9; color: #475569;">
                        <svg class="size-4" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z"/></svg>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 id="kpi-server-status" class="text-lg font-black text-slate-900 tracking-tight truncate" style="font-size: 18px; font-weight: 900; color: #0f172a;">
                        {{ !empty($logsData['online']) ? 'Online (Render)' : 'Menghubungi...' }}
                    </h3>
                    <p id="kpi-server-url" class="mt-0.5 text-[11px] font-mono text-slate-500 truncate" style="font-size: 11px; color: #64748b; font-family: monospace;" title="{{ $baileysBotUrl }}">
                        {{ str_replace(['https://', 'http://'], '', $baileysBotUrl) }}
                    </p>
                </div>
            </div>
            <div class="mt-4 pt-2.5 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-medium text-slate-500" style="border-top: 1px solid #f1f5f9; padding-top: 10px; font-size: 11px; color: #64748b;">
                <span class="size-2 rounded-full bg-emerald-500" style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background-color: #10b981;"></span>
                <span>Node.js Baileys 24/7</span>
            </div>
        </div>

        <!-- Card 2: Status WhatsApp Gateway -->
        <div class="dcms-card p-5 hover:border-slate-300 transition flex flex-col justify-between" style="border-radius: 1rem; border: 1px solid #e2e8f0; background-color: #ffffff; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04); padding: 18px;">
            <div>
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400" style="font-size: 11px; font-weight: 700; color: #94a3b8; letter-spacing: 0.05em;">WhatsApp Gateway</span>
                    <div class="flex size-8 items-center justify-center rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600" style="width: 32px; height: 32px; border-radius: 0.75rem; background-color: #ecfdf5; border: 1px solid #d1fae5; color: #059669;">
                        <svg class="size-4" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.774-.95 4.975 4.975 0 0 0 1.054-2.124A8.167 8.167 0 0 1 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                    </div>
                </div>
                <div class="mt-3">
                    @php
                        $connState = $logsData['connection_state'] ?? '';
                        $stateLabel = 'Menunggu Socket';
                        $stateColor = '#d97706';
                        if ($connState === 'CONNECTED') {
                            $stateLabel = 'Terhubung (Online)';
                            $stateColor = '#059669';
                        } elseif ($connState === 'WAITING_QR') {
                            $stateLabel = 'Perlu Scan QR';
                            $stateColor = '#d97706';
                        }
                    @endphp
                    <h3 id="kpi-bot-state" class="text-lg font-black tracking-tight truncate" style="font-size: 18px; font-weight: 900; color: {{ $stateColor }};">
                        {{ $stateLabel }}
                    </h3>
                    <p id="kpi-bot-phone" class="mt-0.5 text-[11px] font-mono text-slate-500 truncate" style="font-size: 11px; color: #64748b; font-family: monospace;">
                        {{ !empty($logsData['bot_phone']) ? ('+' . $logsData['bot_phone']) : 'Nomor Belum Login' }}
                    </p>
                </div>
            </div>
            <div class="mt-4 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]" style="border-top: 1px solid #f1f5f9; padding-top: 10px; font-size: 11px;">
                <div class="flex items-center gap-1.5 font-medium text-slate-500" style="color: #64748b;">
                    <span id="dot-bot-state" class="size-2 rounded-full" style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background-color: {{ ($connState === 'CONNECTED') ? '#10b981' : '#f59e0b' }};"></span>
                    <span id="label-bot-state">{{ ($connState === 'CONNECTED') ? 'Bot Siap Kirim' : 'Scan di Menu Setup WA' }}</span>
                </div>
                <a href="{{ \App\Support\RouteCipher::url('settings.watzap.index') }}" class="font-bold text-emerald-700 hover:underline text-[11px]" style="font-weight: 700; color: #047857; text-decoration: none;">
                    Setup WA &rarr;
                </a>
            </div>
        </div>

        <!-- Card 3: Scheduler Follow-Up -->
        <div class="dcms-card p-5 hover:border-slate-300 transition flex flex-col justify-between" style="border-radius: 1rem; border: 1px solid #e2e8f0; background-color: #ffffff; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04); padding: 18px;">
            <div>
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400" style="font-size: 11px; font-weight: 700; color: #94a3b8; letter-spacing: 0.05em;">Scheduler Follow-Up</span>
                    <div class="flex size-8 items-center justify-center rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600" style="width: 32px; height: 32px; border-radius: 0.75rem; background-color: #eef2ff; border: 1px solid #e0e7ff; color: #4f46e5;">
                        <svg class="size-4" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 id="kpi-cron-interval" class="text-lg font-black text-slate-900 tracking-tight truncate" style="font-size: 18px; font-weight: 900; color: #0f172a;">
                        Tiap 5 Menit
                    </h3>
                    <p class="mt-0.5 text-[11px] text-slate-500 truncate" style="font-size: 11px; color: #64748b;">
                        Total Eksekusi: <strong id="kpi-cron-total" class="font-bold text-slate-800" style="font-weight: 700; color: #1e293b;">{{ $logsData['cron_stats']['total_runs'] ?? 0 }} kali</strong>
                    </p>
                </div>
            </div>
            <div class="mt-4 pt-2.5 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-medium text-slate-500" style="border-top: 1px solid #f1f5f9; padding-top: 10px; font-size: 11px; color: #64748b;">
                <span class="size-2 rounded-full bg-indigo-500" style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background-color: #6366f1;"></span>
                <span>Strict 24 Jam Presisi</span>
            </div>
        </div>

        <!-- Card 4: Uptime Runtime -->
        <div class="dcms-card p-5 hover:border-slate-300 transition flex flex-col justify-between" style="border-radius: 1rem; border: 1px solid #e2e8f0; background-color: #ffffff; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04); padding: 18px;">
            <div>
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400" style="font-size: 11px; font-weight: 700; color: #94a3b8; letter-spacing: 0.05em;">Uptime Runtime</span>
                    <div class="flex size-8 items-center justify-center rounded-xl bg-violet-50 border border-violet-100 text-violet-600" style="width: 32px; height: 32px; border-radius: 0.75rem; background-color: #f5f3ff; border: 1px solid #ede9fe; color: #7c3aed;">
                        <svg class="size-4" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 id="kpi-uptime" class="text-lg font-black text-slate-900 tracking-tight truncate" style="font-size: 18px; font-weight: 900; color: #0f172a;">
                        {{ $logsData['uptime_formatted'] ?? '0 jam 0 menit' }}
                    </h3>
                    <p class="mt-0.5 text-[11px] text-slate-500 truncate" style="font-size: 11px; color: #64748b;">
                        Koneksi: <span class="font-semibold text-slate-700" style="font-weight: 600; color: #334155;">Dual-Engine Active</span>
                    </p>
                </div>
            </div>
            <div class="mt-4 pt-2.5 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-medium text-slate-500" style="border-top: 1px solid #f1f5f9; padding-top: 10px; font-size: 11px; color: #64748b;">
                <span class="size-2 rounded-full bg-violet-500" style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background-color: #8b5cf6;"></span>
                <span>Zero Downtime Cloud</span>
            </div>
        </div>
    </div>

    <!-- 4. Main Section: Log Table Console (Clean SaaS Style) -->
    <div class="dcms-card overflow-hidden" style="border-radius: 1rem; border: 1px solid #e2e8f0; background-color: #ffffff; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);">
        <!-- Control Toolbar -->
        <div class="border-b border-slate-200 bg-slate-50/80 p-3.5" style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc; padding: 14px 18px;">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <!-- Filter Tabs -->
                <div class="flex flex-wrap items-center gap-1 rounded-xl bg-slate-200/70 p-1" style="border-radius: 0.75rem; background-color: #e2e8f0; padding: 4px;">
                    <button type="button" onclick="setLogFilter('ALL')" id="tab-filter-ALL" class="log-filter-btn rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer bg-white text-slate-900 shadow-sm" style="border-radius: 0.5rem; padding: 6px 12px; font-size: 12px; font-weight: 700; border: none; cursor: pointer; background: #ffffff; color: #0f172a;">
                        Semua (<span id="count-all">0</span>)
                    </button>
                    <button type="button" onclick="setLogFilter('CRON')" id="tab-filter-CRON" class="log-filter-btn rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer" style="border-radius: 0.5rem; padding: 6px 12px; font-size: 12px; font-weight: 500; border: none; cursor: pointer; background: transparent; color: #475569;">
                        Cron H+1 (<span id="count-cron">0</span>)
                    </button>
                    <button type="button" onclick="setLogFilter('REACTION')" id="tab-filter-REACTION" class="log-filter-btn rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer" style="border-radius: 0.5rem; padding: 6px 12px; font-size: 12px; font-weight: 500; border: none; cursor: pointer; background: transparent; color: #475569;">
                        Reaksi Keep (<span id="count-reaction">0</span>)
                    </button>
                    <button type="button" onclick="setLogFilter('AUTH')" id="tab-filter-AUTH" class="log-filter-btn rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer" style="border-radius: 0.5rem; padding: 6px 12px; font-size: 12px; font-weight: 500; border: none; cursor: pointer; background: transparent; color: #475569;">
                        Auth &amp; Socket (<span id="count-auth">0</span>)
                    </button>
                </div>

                <!-- Tools: Search, View Switcher, Auto-Refresh -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Search Input -->
                    <div class="relative min-w-[200px]">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="size-3.5" style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        </span>
                        <input type="search" 
                               id="logSearchInput" 
                               placeholder="Cari kata kunci log..." 
                               oninput="renderLogs()"
                               class="dcms-input py-1.5 pl-8 pr-3 text-xs"
                               style="border-radius: 0.75rem; border: 1px solid #cbd5e1; background: #ffffff; padding: 6px 12px 6px 30px; font-size: 12px; width: 100%;">
                    </div>

                    <!-- View Switcher (Tabel vs Terminal) -->
                    <div class="flex items-center rounded-xl border border-slate-200 bg-white p-0.5" style="border-radius: 0.75rem; border: 1px solid #e2e8f0; background: #ffffff; padding: 2px;">
                        <button type="button" 
                                onclick="setViewMode('TABLE')" 
                                id="btn-view-table" 
                                class="flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition cursor-pointer bg-slate-900 text-white shadow-sm"
                                style="border-radius: 0.5rem; padding: 4px 10px; font-size: 12px; font-weight: 700; border: none; background: #0f172a; color: #ffffff;">
                            <svg class="size-3.5" style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                            <span>Tabel</span>
                        </button>
                        <button type="button" 
                                onclick="setViewMode('TERMINAL')" 
                                id="btn-view-terminal" 
                                class="flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer"
                                style="border-radius: 0.5rem; padding: 4px 10px; font-size: 12px; font-weight: 500; border: none; background: transparent; color: #475569;">
                            <svg class="size-3.5" style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6.75 7.5 3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                            <span>Terminal</span>
                        </button>
                    </div>

                    <!-- Auto Refresh Toggle -->
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm" style="border-radius: 0.75rem; border: 1px solid #e2e8f0; background: #ffffff; padding: 6px 12px; font-size: 12px;">
                        <input type="checkbox" id="autoRefreshToggle" class="sr-only peer" checked onchange="toggleAutoRefresh(this.checked)">
                        <div class="relative h-4 w-7 rounded-full bg-slate-300 peer-checked:bg-emerald-600 transition after:absolute after:top-[2px] after:left-[2px] after:size-3 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-3"></div>
                        <span id="labelAutoRefresh" class="font-mono text-[11px] text-slate-600" style="font-family: monospace; font-size: 11px;">Auto 10s</span>
                    </label>

                    <!-- Copy Button -->
                    <button type="button" 
                            onclick="copyLogsToClipboard()" 
                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-700 shadow-sm transition cursor-pointer"
                            style="border-radius: 0.75rem; border: 1px solid #e2e8f0; background: #ffffff; padding: 6px 10px; font-size: 12px;"
                            title="Salin seluruh baris log">
                        <svg class="size-3.5 text-slate-500" style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
                        <span>Salin</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- VIEW 1: CLEAN SAAS TABLE (Default View) -->
        <div id="view-container-table" class="overflow-x-auto max-h-[580px] overflow-y-auto">
            <table class="w-full text-left border-collapse text-xs" style="width: 100%; font-size: 12px;">
                <thead class="sticky top-0 bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 z-10" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700;">
                    <tr>
                        <th class="py-3 px-4 w-32" style="padding: 12px 16px;">Waktu (WITA)</th>
                        <th class="py-3 px-4 w-28" style="padding: 12px 16px;">Kategori</th>
                        <th class="py-3 px-4 w-24" style="padding: 12px 16px;">Status</th>
                        <th class="py-3 px-4" style="padding: 12px 16px;">Aktivitas &amp; Respon</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody" class="divide-y divide-slate-100 bg-white" style="background-color: #ffffff;">
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400" style="padding: 32px; text-align: center; color: #94a3b8;">
                            <div class="inline-flex items-center gap-2">
                                <span class="size-2 rounded-full bg-slate-400 animate-ping" style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px;"></span>
                                <span>Menghubungi runtime server Render Baileys...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- VIEW 2: SOLID DARK TERMINAL -->
        <div id="view-container-terminal" class="hidden" style="background-color: #0f172a !important;">
            <div class="flex items-center justify-between border-b border-slate-800 px-4 py-2 text-[11px] font-mono text-slate-400" style="background-color: #020617 !important; border-bottom: 1px solid #1e293b; padding: 8px 16px; font-size: 11px; color: #94a3b8; font-family: monospace;">
                <div class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-emerald-500" style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background-color: #10b981;"></span>
                    <span>console@render: dcms-baileys-bot</span>
                </div>
                <span id="terminal-line-count">0 baris log</span>
            </div>
            <div id="terminal-body" class="max-h-[580px] min-h-[360px] overflow-y-auto p-4 font-mono text-[11.5px] leading-relaxed space-y-1.5 select-text text-slate-300" style="background-color: #0f172a !important; padding: 16px; font-family: monospace; font-size: 11.5px; line-height: 1.6; color: #cbd5e1;">
                <div class="py-8 text-center text-slate-500">Memuat log terminal...</div>
            </div>
        </div>

        <!-- Footer Info Bar -->
        <div class="border-t border-slate-100 bg-slate-50/70 px-4 py-2.5 text-[11px] text-slate-500 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2" style="border-top: 1px solid #f1f5f9; background-color: #f8fafc; padding: 10px 16px; font-size: 11px; color: #64748b;">
            <div>
                Alur Scheduler: <code>Render (Tiap 5m) &rarr; /system/cron-run (Fallback: system/migrate)</code>
            </div>
            <div class="font-mono text-slate-400" style="font-family: monospace; color: #94a3b8;">
                Diperbarui: <span id="labelLastUpdated" class="text-slate-600 font-bold" style="font-weight: 700; color: #475569;">-</span>
            </div>
        </div>
    </div>
</div>

<script>
let currentLogs = [];
let currentFilter = 'ALL';
let currentViewMode = 'TABLE';
let autoRefreshTimer = null;
let isFetching = false;

function setViewMode(mode) {
    currentViewMode = mode;
    const btnTerminal = document.getElementById('btn-view-terminal');
    const btnTable = document.getElementById('btn-view-table');
    const containerTerminal = document.getElementById('view-container-terminal');
    const containerTable = document.getElementById('view-container-table');

    if (mode === 'TABLE') {
        btnTable.className = 'flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition cursor-pointer bg-slate-900 text-white shadow-sm';
        btnTable.style.background = '#0f172a';
        btnTable.style.color = '#ffffff';
        btnTable.style.fontWeight = '700';

        btnTerminal.className = 'flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer';
        btnTerminal.style.background = 'transparent';
        btnTerminal.style.color = '#475569';
        btnTerminal.style.fontWeight = '500';

        containerTable.classList.remove('hidden');
        containerTerminal.classList.add('hidden');
    } else {
        btnTerminal.className = 'flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition cursor-pointer bg-slate-900 text-white shadow-sm';
        btnTerminal.style.background = '#0f172a';
        btnTerminal.style.color = '#ffffff';
        btnTerminal.style.fontWeight = '700';

        btnTable.className = 'flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer';
        btnTable.style.background = 'transparent';
        btnTable.style.color = '#475569';
        btnTable.style.fontWeight = '500';

        containerTerminal.classList.remove('hidden');
        containerTable.classList.add('hidden');
    }

    renderLogs();
}

function setLogFilter(filter) {
    currentFilter = filter;
    document.querySelectorAll('.log-filter-btn').forEach(btn => {
        btn.className = 'log-filter-btn rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer';
        btn.style.background = 'transparent';
        btn.style.color = '#475569';
        btn.style.fontWeight = '500';
    });

    const activeBtn = document.getElementById('tab-filter-' + filter);
    if (activeBtn) {
        activeBtn.className = 'log-filter-btn rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer bg-white text-slate-900 shadow-sm';
        activeBtn.style.background = '#ffffff';
        activeBtn.style.color = '#0f172a';
        activeBtn.style.fontWeight = '700';
    }

    renderLogs();
}

function getFilteredLogs() {
    const search = (document.getElementById('logSearchInput')?.value || '').toLowerCase().trim();

    return currentLogs.filter(item => {
        if (currentFilter !== 'ALL' && item.type !== currentFilter) return false;
        if (search) {
            const haystack = ((item.message || '') + ' ' + (item.detail || '') + ' ' + (item.time || '') + ' ' + (item.type || '') + ' ' + (item.level || '')).toLowerCase();
            return haystack.includes(search);
        }
        return true;
    });
}

function renderLogs() {
    const filtered = getFilteredLogs();

    // Counts
    const countAll = currentLogs.length;
    const countCron = currentLogs.filter(i => i.type === 'CRON').length;
    const countReaction = currentLogs.filter(i => i.type === 'REACTION').length;
    const countAuth = currentLogs.filter(i => i.type === 'AUTH').length;

    if (document.getElementById('count-all')) document.getElementById('count-all').innerText = countAll;
    if (document.getElementById('count-cron')) document.getElementById('count-cron').innerText = countCron;
    if (document.getElementById('count-reaction')) document.getElementById('count-reaction').innerText = countReaction;
    if (document.getElementById('count-auth')) document.getElementById('count-auth').innerText = countAuth;
    if (document.getElementById('terminal-line-count')) document.getElementById('terminal-line-count').innerText = filtered.length + ' baris log';

    renderTable(filtered);
    renderTerminal(filtered);
}

function renderTable(filtered) {
    const tbody = document.getElementById('logsTableBody');
    if (!tbody) return;

    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="p-8 text-center text-slate-400" style="padding: 32px; text-align: center; color: #94a3b8;">
                    Tidak ada catatan log yang cocok dengan filter '${currentFilter}'.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filtered.map(log => {
        const level = (log.level || 'INFO').toUpperCase();
        let badgeLevelClass = 'bg-slate-100 text-slate-600 border border-slate-200';
        let dotColor = '#94a3b8';

        if (level === 'SUCCESS') {
            badgeLevelClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
            dotColor = '#10b981';
        } else if (level === 'ERROR') {
            badgeLevelClass = 'bg-rose-50 text-rose-700 border border-rose-200';
            dotColor = '#ef4444';
        } else if (level === 'WARN') {
            badgeLevelClass = 'bg-amber-50 text-amber-700 border border-amber-200';
            dotColor = '#f59e0b';
        }

        const type = (log.type || 'SYSTEM').toUpperCase();
        let badgeTypeClass = 'bg-slate-100 text-slate-600 border border-slate-200';
        if (type === 'CRON') badgeTypeClass = 'bg-indigo-50 text-indigo-700 border border-indigo-200';
        if (type === 'REACTION') badgeTypeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
        if (type === 'AUTH') badgeTypeClass = 'bg-teal-50 text-teal-700 border border-teal-200';

        return `
            <tr class="hover:bg-slate-50/70 transition" style="border-bottom: 1px solid #f1f5f9;">
                <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap align-top text-[11px]" style="padding: 12px 16px; font-family: monospace; font-size: 11px; color: #64748b;">
                    ${escapeHtml(log.time || '-')}
                </td>
                <td class="py-3 px-4 align-top whitespace-nowrap" style="padding: 12px 16px;">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold ${badgeTypeClass}" style="border-radius: 6px; padding: 2px 8px; font-size: 10px; font-weight: 700;">
                        ${type}
                    </span>
                </td>
                <td class="py-3 px-4 align-top whitespace-nowrap" style="padding: 12px 16px;">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold ${badgeLevelClass}" style="border-radius: 6px; padding: 2px 8px; font-size: 10px; font-weight: 700;">
                        <span class="size-1.5 rounded-full" style="display: inline-block; width: 6px; height: 6px; border-radius: 9999px; background-color: ${dotColor};"></span>
                        <span>${level}</span>
                    </span>
                </td>
                <td class="py-3 px-4 text-slate-800 align-top" style="padding: 12px 16px; color: #1e293b;">
                    <div class="font-bold text-xs text-slate-900" style="font-weight: 700; color: #0f172a; font-size: 12px;">${escapeHtml(log.message || '-')}</div>
                    ${log.detail ? `
                        <div class="mt-1 text-[11px] font-mono text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-200/80 whitespace-pre-wrap break-all" style="margin-top: 4px; font-family: monospace; font-size: 11px; color: #64748b; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px;">
                            ${escapeHtml(log.detail)}
                        </div>
                    ` : ''}
                </td>
            </tr>
        `;
    }).join('');
}

function renderTerminal(filtered) {
    const termBody = document.getElementById('terminal-body');
    if (!termBody) return;

    if (filtered.length === 0) {
        termBody.innerHTML = `<div class="py-8 text-center text-slate-500 font-mono">[info] Tidak ada log yang sesuai filter.</div>`;
        return;
    }

    termBody.innerHTML = filtered.map(log => {
        const level = (log.level || 'INFO').toUpperCase();
        let levelColor = '#94a3b8';
        if (level === 'SUCCESS') levelColor = '#34d399';
        if (level === 'ERROR') levelColor = '#f87171';
        if (level === 'WARN') levelColor = '#fbbf24';

        const type = (log.type || 'SYSTEM').toUpperCase();
        let typeColor = '#cbd5e1';
        if (type === 'CRON') typeColor = '#818cf8';
        if (type === 'REACTION') typeColor = '#fcd34d';
        if (type === 'AUTH') typeColor = '#5eead4';

        return `
            <div class="py-1 border-b border-slate-800/50" style="border-bottom: 1px solid rgba(30, 41, 59, 0.5); padding: 4px 0;">
                <div class="flex flex-wrap items-baseline gap-2">
                    <span class="text-slate-500 text-[11px]" style="color: #64748b; font-size: 11px;">[${escapeHtml(log.time || '-')}]</span>
                    <span class="font-bold text-[11px]" style="color: ${typeColor}; font-size: 11px; font-weight: 700;">[${type}]</span>
                    <span class="text-[11px]" style="color: ${levelColor}; font-size: 11px; font-weight: 700;">[${level}]</span>
                    <span class="text-slate-200" style="color: #e2e8f0;">${escapeHtml(log.message || '-')}</span>
                </div>
                ${log.detail ? `
                    <div class="mt-0.5 ml-4 text-[11px] text-slate-400 font-mono whitespace-pre-wrap break-all" style="margin-left: 16px; margin-top: 2px; color: #94a3b8; font-family: monospace; font-size: 11px;">
                        &gt; ${escapeHtml(log.detail)}
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

async function fetchRealtimeLogs(isManual = false) {
    if (isFetching) return;
    isFetching = true;

    const iconRefresh = document.getElementById('icon-refresh-logs');
    if (iconRefresh && isManual) {
        iconRefresh.classList.add('animate-spin');
    }

    try {
        const res = await fetch('/api/settings/baileys-logs/data', {
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        // Update Server Status
        if (document.getElementById('kpi-server-status')) {
            document.getElementById('kpi-server-status').innerText = data.online ? 'Online (Render)' : 'Offline / Error';
            document.getElementById('kpi-server-status').style.color = data.online ? '#0f172a' : '#ef4444';
        }

        const badgeRender = document.getElementById('badge-render-status');
        const textRender = document.getElementById('text-render-status');
        if (badgeRender && textRender) {
            if (data.online) {
                badgeRender.className = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                textRender.innerText = 'Server Render Online';
            } else {
                badgeRender.className = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200';
                textRender.innerText = 'Server Render Offline';
            }
        }

        // Update Bot State
        if (document.getElementById('kpi-bot-state')) {
            const isConn = data.connection_state === 'CONNECTED';
            const isWaitingQr = data.connection_state === 'WAITING_QR';

            let stateLabel = 'Menunggu Socket';
            let colorHex = '#d97706';
            let dotColor = '#f59e0b';
            let subLabel = 'Scan di Menu Setup WA';

            if (isConn) {
                stateLabel = 'Terhubung (Online)';
                colorHex = '#059669';
                dotColor = '#10b981';
                subLabel = 'Bot Siap Kirim';
            } else if (isWaitingQr) {
                stateLabel = 'Perlu Scan QR';
                colorHex = '#d97706';
                dotColor = '#f59e0b';
                subLabel = 'Scan di Menu Setup WA';
            }

            document.getElementById('kpi-bot-state').innerText = stateLabel;
            document.getElementById('kpi-bot-state').style.color = colorHex;

            const dot = document.getElementById('dot-bot-state');
            if (dot) dot.style.backgroundColor = dotColor;

            const labelSub = document.getElementById('label-bot-state');
            if (labelSub) labelSub.innerText = subLabel;
        }

        if (document.getElementById('kpi-bot-phone')) {
            document.getElementById('kpi-bot-phone').innerText = data.bot_phone ? ('+' + data.bot_phone) : 'Nomor Belum Login';
        }

        if (document.getElementById('kpi-uptime')) {
            document.getElementById('kpi-uptime').innerText = data.uptime_formatted || '0 jam 0 menit';
        }

        if (document.getElementById('kpi-cron-total')) {
            document.getElementById('kpi-cron-total').innerText = (data.cron_stats?.total_runs || 0) + ' kali';
        }

        currentLogs = data.logs || [];
        renderLogs();

        // Cek apakah eksekusi CRON paling akhir (terbaru) mengalami error
        const cronLogs = currentLogs.filter(l => l.type === 'CRON');
        const latestCron = cronLogs.length > 0 ? cronLogs[0] : null;

        const diagAlert = document.getElementById('diagnostic-alert');
        const diagMsg = document.getElementById('diagnostic-message');

        if (diagAlert && diagMsg) {
            // Hanya tampilkan jika cron paling akhir benar-benar error
            if (latestCron && latestCron.level === 'ERROR') {
                diagAlert.classList.remove('hidden');
                let cleanMsg = latestCron.message || 'Koneksi cron timeout.';
                if (cleanMsg.includes('timeout')) {
                    cleanMsg = 'Koneksi cron sempat timeout (>40s). Render otomatis mengaktifkan Fallback Engine.';
                }
                diagMsg.innerText = cleanMsg;
            } else {
                diagAlert.classList.add('hidden');
            }
        }

        const labelLastUpdated = document.getElementById('labelLastUpdated');
        if (labelLastUpdated) {
            const now = new Date();
            labelLastUpdated.innerText = now.toLocaleTimeString('id-ID', { timeZone: 'Asia/Makassar' }) + ' WITA';
        }
    } catch (err) {
        console.warn('Gagal memuat live logs:', err);
    } finally {
        isFetching = false;
        if (iconRefresh) {
            iconRefresh.classList.remove('animate-spin');
        }
    }
}

async function triggerCronManually(btn) {
    if (!confirm('Picu eksekusi Cron Auto Follow-Up H+1 sekarang juga?\nSistem akan memproses data keep yang sudah genap 24 jam.')) return;

    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-flex items-center gap-1.5"><span class="size-3 rounded-full border-2 border-white border-t-transparent animate-spin"></span><span>Memproses...</span></span>';

    try {
        const res = await fetch('/api/settings/baileys-logs/trigger-cron', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ force: false })
        });

        const json = await res.json();
        if (json.success) {
            alert(`✅ ${json.message}\nWaktu: ${json.executed_at}\nTerkirim: ${json.data?.sent_count || 0} keep`);
            fetchRealtimeLogs(true);
        } else {
            alert(`❌ Gagal: ${json.message || 'Unknown error'}`);
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

function copyLogsToClipboard() {
    const filtered = getFilteredLogs();
    if (filtered.length === 0) {
        alert('Tidak ada log untuk disalin.');
        return;
    }

    const text = filtered.map(l => `[${l.time}] [${l.type}] [${l.level}] ${l.message} ${l.detail ? '(' + l.detail + ')' : ''}`).join('\n');
    navigator.clipboard.writeText(text).then(() => {
        alert('✅ Berhasil menyalin ' + filtered.length + ' baris log ke clipboard.');
    }).catch(err => {
        alert('Gagal menyalin: ' + err.message);
    });
}

function toggleAutoRefresh(enabled) {
    if (autoRefreshTimer) {
        clearInterval(autoRefreshTimer);
        autoRefreshTimer = null;
    }
    const label = document.getElementById('labelAutoRefresh');
    if (enabled) {
        if (label) label.innerText = 'Auto 10s';
        autoRefreshTimer = setInterval(() => fetchRealtimeLogs(false), 10000);
    } else {
        if (label) label.innerText = 'Manual';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    fetchRealtimeLogs(false);
    toggleAutoRefresh(true);
});
</script>
@endsection
