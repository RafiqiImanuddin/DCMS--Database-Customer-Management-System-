@extends('layouts.app')
@section('title', 'Log Render Baileys & Cron H+1')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span id="badge-render-status" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ !empty($logsData['online']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80' }}">
                    <span class="relative flex size-2">
                        <span class="absolute inline-flex h-full w-full rounded-full {{ !empty($logsData['online']) ? 'bg-emerald-400 animate-ping opacity-75' : 'bg-rose-400' }}"></span>
                        <span class="relative inline-flex size-2 rounded-full {{ !empty($logsData['online']) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    </span>
                    <span id="text-render-status" class="tracking-wide">{{ !empty($logsData['online']) ? 'Server Render Online' : 'Server Render Offline / Loading' }}</span>
                </span>
                <span class="text-xs text-slate-300">•</span>
                <span class="text-xs font-medium text-slate-500">Scheduler H+1 Strict 24h &amp; Realtime Bot Monitoring</span>
            </div>
            <h1 class="mt-1.5 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Log Runtime Render &amp; Cron H+1</h1>
            <p class="mt-1 max-w-2xl text-xs text-slate-500 leading-relaxed">
                Pantau eksekusi otomatis Cron Follow-Up H+1 tiap 5 menit, aktivitas reaksi PIC WhatsApp, dan riwayat socket server Render secara langsung.
            </p>
        </div>

        <!-- Action Controls -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Trigger Cron Now -->
            <button type="button" 
                    id="btn-trigger-cron"
                    onclick="triggerCronManually(this)" 
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-600/20 hover:from-emerald-500 hover:to-cyan-500 active:scale-95 transition cursor-pointer"
                    title="Picu pengecekan antrean follow-up 24 jam sekarang juga">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                <span>⚡ Trigger Cron Sekarang</span>
            </button>

            <!-- Sync / Clear Cache cPanel -->
            <a href="{{ url('/system/migrate?key=dcms2026') }}" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="inline-flex items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100/80 px-3.5 py-2.5 text-xs font-bold text-amber-800 shadow-2xs transition"
               title="Buka konsol migrasi & bersihkan cache route cPanel untuk sinkronisasi route baru">
                <svg class="size-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                <span>Clear Cache cPanel</span>
            </a>

            <!-- Refresh Button -->
            <button type="button" 
                    id="btn-refresh-logs"
                    onclick="fetchRealtimeLogs(true)" 
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-2xs transition cursor-pointer">
                <svg id="icon-refresh-logs" class="size-4 text-slate-500 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Segarkan</span>
            </button>

            <!-- Render External Console -->
            <a href="https://dashboard.render.com" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3 py-2.5 text-xs font-bold text-slate-600 shadow-2xs transition"
               title="Buka console resmi Render.com">
                <span>Render</span>
                <svg class="size-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            </a>
        </div>
    </div>

    <!-- Diagnostic Smart Alert (Tampil jika terdeteksi error) -->
    <div id="diagnostic-alert" class="hidden rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-4 text-amber-900 shadow-xs">
        <div class="flex items-start gap-3">
            <div class="rounded-xl bg-amber-100 p-2 text-amber-700 shrink-0">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-xs font-bold text-amber-900">Perhatian: Sinkronisasi Route cPanel Diperlukan</h4>
                <p id="diagnostic-message" class="mt-0.5 text-xs text-amber-800 leading-relaxed">
                    Bot Render sempat mendapati status error saat memicu cron. Sistem Dual-Engine otomatis aktif menggunakan fallback.
                </p>
                <div class="mt-2.5 flex flex-wrap items-center gap-2 text-[11px]">
                    <a href="{{ url('/system/migrate?key=dcms2026') }}" target="_blank" class="inline-flex items-center gap-1 font-bold text-amber-900 underline hover:text-amber-950">
                        <span>1. Buka tautan pembersih cache route cPanel</span>
                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                    </a>
                    <span class="text-amber-400">•</span>
                    <span class="text-amber-800">2. Pastikan file deploy terbaru sudah di-pull / diekstrak di cPanel</span>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('diagnostic-alert').classList.add('hidden')" class="text-amber-500 hover:text-amber-700 cursor-pointer p-1">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- 4 Modern KPI Cards (No gaudy emojis, pure sleek SVG + gradients) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1: Server Host Render -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:shadow-sm transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Host Server Render</span>
                <div class="flex size-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600 border border-teal-100">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z"/></svg>
                </div>
            </div>
            <div class="mt-2.5">
                <h3 id="kpi-server-status" class="text-lg font-black text-slate-900 tracking-tight truncate">
                    {{ !empty($logsData['online']) ? 'Online (Render)' : 'Menghubungi...' }}
                </h3>
                <p id="kpi-server-url" class="mt-0.5 text-[11px] font-mono text-slate-500 truncate" title="{{ $baileysBotUrl }}">
                    {{ str_replace(['https://', 'http://'], '', $baileysBotUrl) }}
                </p>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-teal-700">
                <span class="size-1.5 rounded-full bg-teal-500"></span>
                <span>Node.js Baileys Gateway</span>
            </div>
        </div>

        <!-- Card 2: WhatsApp Bot Socket -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:shadow-sm transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">WhatsApp Socket</span>
                <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.774-.95 4.975 4.975 0 0 0 1.054-2.124A8.167 8.167 0 0 1 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                </div>
            </div>
            <div class="mt-2.5">
                <h3 id="kpi-bot-state" class="text-lg font-black tracking-tight truncate {{ ($logsData['connection_state'] ?? '') === 'CONNECTED' ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ ($logsData['connection_state'] ?? '') === 'CONNECTED' ? 'Terhubung (Online)' : ($logsData['connection_state'] ?? 'Menunggu Socket') }}
                </h3>
                <p id="kpi-bot-phone" class="mt-0.5 text-[11px] font-mono text-slate-500 truncate">
                    {{ !empty($logsData['bot_phone']) ? ('+' . $logsData['bot_phone']) : '(Belum Login)' }}
                </p>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700">
                <span class="size-1.5 rounded-full {{ ($logsData['connection_state'] ?? '') === 'CONNECTED' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                <span id="kpi-bot-role-text">Reaksi &amp; Notif H+1 Siap</span>
            </div>
        </div>

        <!-- Card 3: Scheduler H+1 Strict 24h -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:shadow-sm transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Scheduler H+1 Presisi</span>
                <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
            </div>
            <div class="mt-2.5">
                <h3 id="kpi-cron-interval" class="text-lg font-black tracking-tight text-indigo-700 truncate">
                    Tiap 5 Menit
                </h3>
                <p class="mt-0.5 text-[11px] text-slate-500 truncate">
                    Total Eksekusi: <strong id="kpi-cron-total" class="font-bold text-slate-800">{{ $logsData['cron_stats']['total_runs'] ?? 0 }} kali</strong>
                </p>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-indigo-700">
                <span class="size-1.5 rounded-full bg-indigo-500"></span>
                <span>Strict 1x24h (08:00 - 22:00)</span>
            </div>
        </div>

        <!-- Card 4: Uptime & Deploy -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:shadow-sm transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Uptime Server</span>
                <div class="flex size-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600 border border-violet-100">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                </div>
            </div>
            <div class="mt-2.5">
                <h3 id="kpi-uptime" class="text-lg font-black tracking-tight text-slate-900 truncate">
                    {{ $logsData['uptime_formatted'] ?? '0 jam 0 menit' }}
                </h3>
                <p class="mt-0.5 text-[11px] text-slate-500 truncate">
                    Koneksi: <span id="kpi-engine-mode" class="font-bold text-slate-700">Dual-Engine Active</span>
                </p>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-violet-700">
                <span class="size-1.5 rounded-full bg-violet-500"></span>
                <span>Zero Downtime Cloud</span>
            </div>
        </div>
    </div>

    <!-- Main Section: Live Log Console -->
    <div class="rounded-2xl border border-slate-200/90 bg-white shadow-xs overflow-hidden">
        <!-- Console Control Toolbar -->
        <div class="border-b border-slate-200/80 bg-slate-50/70 p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <!-- Segmented Tabs (iOS / Modern SaaS style) -->
                <div class="flex flex-wrap items-center gap-1.5 rounded-xl bg-slate-200/60 p-1">
                    <button type="button" onclick="setLogFilter('ALL')" id="tab-filter-ALL" class="log-filter-btn rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer bg-white text-slate-900 shadow-xs">
                        Semua (<span id="count-all">0</span>)
                    </button>
                    <button type="button" onclick="setLogFilter('CRON')" id="tab-filter-CRON" class="log-filter-btn rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer">
                        Cron H+1 (<span id="count-cron">0</span>)
                    </button>
                    <button type="button" onclick="setLogFilter('REACTION')" id="tab-filter-REACTION" class="log-filter-btn rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer">
                        Reaksi Keep (<span id="count-reaction">0</span>)
                    </button>
                    <button type="button" onclick="setLogFilter('AUTH')" id="tab-filter-AUTH" class="log-filter-btn rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer">
                        Auth &amp; Socket (<span id="count-auth">0</span>)
                    </button>
                </div>

                <!-- Right Tools: Search, View Mode, Auto-Refresh -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Search Input -->
                    <div class="relative min-w-[200px] flex-1 sm:flex-initial">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        </span>
                        <input type="search" 
                               id="logSearchInput" 
                               placeholder="Cari kata kunci log..." 
                               oninput="renderLogs()"
                               class="w-full rounded-xl border border-slate-200 bg-white py-1.5 pl-8 pr-3 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition">
                    </div>

                    <!-- View Mode Switcher: Terminal vs Tabel -->
                    <div class="flex items-center rounded-xl border border-slate-200 bg-white p-0.5 shadow-2xs">
                        <button type="button" 
                                onclick="setViewMode('TERMINAL')" 
                                id="btn-view-terminal" 
                                class="flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition cursor-pointer bg-slate-900 text-white shadow-2xs"
                                title="Tampilan Terminal Pengembang (Dark)">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6.75 7.5 3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                            <span>Terminal</span>
                        </button>
                        <button type="button" 
                                onclick="setViewMode('TABLE')" 
                                id="btn-view-table" 
                                class="flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer"
                                title="Tampilan Tabel Modern (Light)">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                            <span>Tabel</span>
                        </button>
                    </div>

                    <!-- Auto Refresh Toggle -->
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs">
                        <input type="checkbox" id="autoRefreshToggle" class="sr-only peer" checked onchange="toggleAutoRefresh(this.checked)">
                        <div class="relative h-4 w-7 rounded-full bg-slate-300 peer-checked:bg-emerald-600 transition after:absolute after:top-[2px] after:left-[2px] after:size-3 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-3"></div>
                        <span id="labelAutoRefresh" class="flex items-center gap-1 font-mono text-[11px]">
                            <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Auto 10s</span>
                        </span>
                    </label>

                    <!-- Copy All Logs -->
                    <button type="button" 
                            onclick="copyLogsToClipboard()" 
                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-600 shadow-2xs transition cursor-pointer"
                            title="Salin seluruh baris log ke clipboard">
                        <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
                        <span>Salin</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- VIEW 1: TERMINAL CONSOLE (Developer Dark Mode - Premium Aesthetic) -->
        <div id="view-container-terminal" class="bg-[#090d16] text-slate-200">
            <!-- Terminal Window Top Bar -->
            <div class="flex items-center justify-between border-b border-slate-800/80 bg-[#0d1424] px-4 py-2.5">
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-rose-500/80"></span>
                        <span class="size-2.5 rounded-full bg-amber-500/80"></span>
                        <span class="size-2.5 rounded-full bg-emerald-500/80"></span>
                    </div>
                    <span class="ml-2 font-mono text-[11px] text-slate-400">render-baileys-service • live-stream</span>
                </div>
                <div class="flex items-center gap-3 font-mono text-[11px] text-slate-500">
                    <span id="terminal-line-count">0 baris log</span>
                    <span>•</span>
                    <span class="text-emerald-400 flex items-center gap-1">
                        <span class="size-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        LIVE
                    </span>
                </div>
            </div>

            <!-- Terminal Output Scroll Box -->
            <div id="terminal-body" class="max-h-[580px] min-h-[380px] overflow-y-auto p-4 font-mono text-[12px] leading-relaxed space-y-2 select-text">
                <div class="flex items-center gap-2 text-slate-400 py-6 justify-center">
                    <span class="size-2 rounded-full bg-teal-400 animate-ping"></span>
                    <span>Menghubungi runtime Render Baileys...</span>
                </div>
            </div>
        </div>

        <!-- VIEW 2: MODERN SAAS TABLE (Light Mode - Clean Slate) -->
        <div id="view-container-table" class="hidden overflow-x-auto max-h-[580px] overflow-y-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="sticky top-0 bg-slate-50 border-b border-slate-200 text-[10px] font-bold uppercase tracking-wider text-slate-500 z-10">
                    <tr>
                        <th class="py-3 px-4 w-32 font-bold">Waktu (WITA)</th>
                        <th class="py-3 px-4 w-28 font-bold">Kategori</th>
                        <th class="py-3 px-4 w-24 font-bold">Status</th>
                        <th class="py-3 px-4 font-bold">Aktivitas &amp; Respon</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody" class="divide-y divide-slate-100 bg-white font-mono">
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400 font-sans">
                            <div class="inline-flex items-center gap-2">
                                <span class="size-2 rounded-full bg-teal-500 animate-ping"></span>
                                <span>Menghubungi server Render Baileys...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Console Footer Status Bar -->
        <div class="border-t border-slate-200/80 bg-slate-50/80 px-4 py-3 text-xs text-slate-500 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2 text-[11px]">
                <span class="inline-flex items-center gap-1 font-bold text-slate-700">
                    <svg class="size-3.5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                    Alur Cron:
                </span>
                <span class="text-slate-600">Render Cron (Tiap 5m) &rarr; <code>/system/cron-run</code> (Fallback: <code>/system/migrate?action=auto-fu-h1</code>)</span>
            </div>
            <div class="font-mono text-[11px] text-slate-400">
                Pembaruan: <span id="labelLastUpdated" class="text-slate-700 font-bold">-</span>
            </div>
        </div>
    </div>
</div>

<script>
let currentLogs = [];
let currentFilter = 'ALL';
let currentViewMode = 'TERMINAL';
let autoRefreshTimer = null;
let isFetching = false;

function setViewMode(mode) {
    currentViewMode = mode;
    const btnTerminal = document.getElementById('btn-view-terminal');
    const btnTable = document.getElementById('btn-view-table');
    const containerTerminal = document.getElementById('view-container-terminal');
    const containerTable = document.getElementById('view-container-table');

    if (mode === 'TERMINAL') {
        btnTerminal.className = 'flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition cursor-pointer bg-slate-900 text-white shadow-2xs';
        btnTable.className = 'flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer';
        containerTerminal.classList.remove('hidden');
        containerTable.classList.add('hidden');
    } else {
        btnTable.className = 'flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition cursor-pointer bg-slate-900 text-white shadow-2xs';
        btnTerminal.className = 'flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer';
        containerTable.classList.remove('hidden');
        containerTerminal.classList.add('hidden');
    }

    renderLogs();
}

function setLogFilter(filter) {
    currentFilter = filter;
    document.querySelectorAll('.log-filter-btn').forEach(btn => {
        btn.className = 'log-filter-btn rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 transition cursor-pointer';
    });

    const activeBtn = document.getElementById('tab-filter-' + filter);
    if (activeBtn) {
        activeBtn.className = 'log-filter-btn rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer bg-white text-slate-900 shadow-xs';
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

    // Update Counts
    const countAll = currentLogs.length;
    const countCron = currentLogs.filter(i => i.type === 'CRON').length;
    const countReaction = currentLogs.filter(i => i.type === 'REACTION').length;
    const countAuth = currentLogs.filter(i => i.type === 'AUTH').length;

    if (document.getElementById('count-all')) document.getElementById('count-all').innerText = countAll;
    if (document.getElementById('count-cron')) document.getElementById('count-cron').innerText = countCron;
    if (document.getElementById('count-reaction')) document.getElementById('count-reaction').innerText = countReaction;
    if (document.getElementById('count-auth')) document.getElementById('count-auth').innerText = countAuth;
    if (document.getElementById('terminal-line-count')) document.getElementById('terminal-line-count').innerText = filtered.length + ' baris';

    // Render Terminal Mode
    renderTerminal(filtered);

    // Render Table Mode
    renderTable(filtered);
}

function renderTerminal(filtered) {
    const termBody = document.getElementById('terminal-body');
    if (!termBody) return;

    if (filtered.length === 0) {
        termBody.innerHTML = `
            <div class="py-12 text-center text-slate-500 font-mono">
                [info] Tidak ada log yang sesuai dengan filter '${currentFilter}'.
            </div>
        `;
        return;
    }

    termBody.innerHTML = filtered.map(log => {
        const level = (log.level || 'INFO').toUpperCase();
        let levelColor = 'text-slate-400';
        let levelBg = 'bg-slate-800 text-slate-300';
        if (level === 'SUCCESS') {
            levelColor = 'text-emerald-400';
            levelBg = 'bg-emerald-950/80 text-emerald-300 border border-emerald-800/60';
        } else if (level === 'ERROR') {
            levelColor = 'text-rose-400';
            levelBg = 'bg-rose-950/80 text-rose-300 border border-rose-800/60';
        } else if (level === 'WARN') {
            levelColor = 'text-amber-400';
            levelBg = 'bg-amber-950/80 text-amber-300 border border-amber-800/60';
        }

        const type = (log.type || 'SYSTEM').toUpperCase();
        let typeColor = 'text-sky-400';
        if (type === 'CRON') typeColor = 'text-indigo-400';
        if (type === 'REACTION') typeColor = 'text-amber-400';
        if (type === 'AUTH') typeColor = 'text-teal-400';

        return `
            <div class="group rounded-lg p-2 hover:bg-slate-800/40 transition border border-transparent hover:border-slate-800">
                <div class="flex flex-wrap items-baseline gap-2 font-mono">
                    <span class="text-slate-500 select-none text-[11px]">${escapeHtml(log.time || '-')}</span>
                    <span class="font-bold ${typeColor} text-[11px]">[${type}]</span>
                    <span class="rounded px-1.5 py-0.2 text-[10px] font-bold ${levelBg}">${level}</span>
                    <span class="text-slate-100 font-medium">${escapeHtml(log.message || '-')}</span>
                </div>
                ${log.detail ? `
                    <div class="mt-1.5 ml-4 pl-3 border-l-2 border-slate-700/80 text-[11px] text-slate-400 select-text whitespace-pre-wrap break-all bg-slate-900/60 p-2 rounded">
                        ${escapeHtml(log.detail)}
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

function renderTable(filtered) {
    const tbody = document.getElementById('logsTableBody');
    if (!tbody) return;

    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="p-8 text-center text-slate-400 font-sans">
                    Tidak ada catatan log yang cocok dengan filter.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filtered.map(log => {
        const level = (log.level || 'INFO').toUpperCase();
        let badgeLevelClass = 'bg-slate-100 text-slate-600 border border-slate-200';
        let dotColor = 'bg-slate-400';

        if (level === 'SUCCESS') {
            badgeLevelClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
            dotColor = 'bg-emerald-500';
        } else if (level === 'ERROR') {
            badgeLevelClass = 'bg-rose-50 text-rose-700 border border-rose-200';
            dotColor = 'bg-rose-500';
        } else if (level === 'WARN') {
            badgeLevelClass = 'bg-amber-50 text-amber-700 border border-amber-200';
            dotColor = 'bg-amber-500';
        }

        const type = (log.type || 'SYSTEM').toUpperCase();
        let badgeTypeClass = 'bg-slate-100 text-slate-600 border border-slate-200';
        if (type === 'CRON') badgeTypeClass = 'bg-indigo-50 text-indigo-700 border border-indigo-200';
        if (type === 'REACTION') badgeTypeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
        if (type === 'AUTH') badgeTypeClass = 'bg-teal-50 text-teal-700 border border-teal-200';

        return `
            <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3 px-4 font-mono text-slate-600 whitespace-nowrap align-top text-[11px]">
                    ${escapeHtml(log.time || '-')}
                </td>
                <td class="py-3 px-4 align-top whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold ${badgeTypeClass}">
                        ${type}
                    </span>
                </td>
                <td class="py-3 px-4 align-top whitespace-nowrap">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold ${badgeLevelClass}">
                        <span class="size-1.5 rounded-full ${dotColor}"></span>
                        <span>${level}</span>
                    </span>
                </td>
                <td class="py-3 px-4 text-slate-800 align-top">
                    <div class="font-bold text-xs text-slate-900">${escapeHtml(log.message || '-')}</div>
                    ${log.detail ? `
                        <div class="mt-1 text-[11px] font-mono text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-200/70 whitespace-pre-wrap break-all">
                            ${escapeHtml(log.detail)}
                        </div>
                    ` : ''}
                </td>
            </tr>
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
            document.getElementById('kpi-server-status').className = 'text-lg font-black tracking-tight truncate ' + (data.online ? 'text-slate-900' : 'text-rose-600');
        }

        const badgeRender = document.getElementById('badge-render-status');
        const textRender = document.getElementById('text-render-status');
        if (badgeRender && textRender) {
            if (data.online) {
                badgeRender.className = 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80';
                textRender.innerText = 'Server Render Online';
            } else {
                badgeRender.className = 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/80';
                textRender.innerText = 'Server Render Offline';
            }
        }

        // Update Bot State
        if (document.getElementById('kpi-bot-state')) {
            const isConn = data.connection_state === 'CONNECTED';
            document.getElementById('kpi-bot-state').innerText = isConn ? 'Terhubung (Online)' : (data.connection_state || 'Menunggu QR');
            document.getElementById('kpi-bot-state').className = 'text-lg font-black tracking-tight truncate ' + (isConn ? 'text-emerald-600' : 'text-amber-600');
        }

        if (document.getElementById('kpi-bot-phone')) {
            document.getElementById('kpi-bot-phone').innerText = data.bot_phone ? ('+' + data.bot_phone) : '(Belum Login)';
        }

        if (document.getElementById('kpi-uptime')) {
            document.getElementById('kpi-uptime').innerText = data.uptime_formatted || '0 menit';
        }

        if (document.getElementById('kpi-cron-total')) {
            document.getElementById('kpi-cron-total').innerText = (data.cron_stats?.total_runs || 0) + ' kali';
        }

        currentLogs = data.logs || [];
        renderLogs();

        // Check if latest cron had error -> show diagnostic banner
        const latestCronError = currentLogs.find(l => l.type === 'CRON' && l.level === 'ERROR');
        const diagAlert = document.getElementById('diagnostic-alert');
        const diagMsg = document.getElementById('diagnostic-message');
        if (diagAlert && diagMsg) {
            if (latestCronError) {
                diagAlert.classList.remove('hidden');
                diagMsg.innerText = `Pesan Terakhir: ${latestCronError.message} (${latestCronError.detail || ''})`;
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
        console.warn('Gagal memuat log runtime:', err);
    } finally {
        isFetching = false;
        if (iconRefresh) {
            iconRefresh.classList.remove('animate-spin');
        }
    }
}

async function triggerCronManually(btn) {
    if (!confirm('Picu eksekusi Cron Auto Follow-Up H+1 sekarang juga?\nSistem akan memproses semua keep yang sudah genap 24 jam.')) return;

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
        if (label) label.innerHTML = '<span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span><span>Auto 10s</span>';
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
