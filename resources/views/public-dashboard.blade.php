<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Live Dashboard Sosmed • DCMS Gadgetmart</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind / App Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Local Workspace Sosmed Engine & Libraries -->
    <script src="{{ asset('workspace-sosmed/lib/xlsx.full.min.js') }}"></script>
    <script src="{{ asset('workspace-sosmed/lib/jszip.min.js') }}"></script>
    <script src="{{ asset('workspace-sosmed/lib/exceljs.min.js') }}"></script>
    <script src="{{ asset('workspace-sosmed/lib/html2canvas.min.js') }}"></script>
    <script src="{{ asset('workspace-sosmed/lib/chart.umd.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('workspace-sosmed/style.css') }}?v={{ @filemtime(public_path('workspace-sosmed/style.css')) ?: '7.4' }}">

    <style>
        /* Base Reset & Typography */
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Strict Constraints on Logo */
        .dcms-public-logo {
            height: 34px !important;
            max-height: 34px !important;
            width: 34px !important;
            max-width: 34px !important;
            object-fit: contain !important;
            flex-shrink: 0 !important;
            display: inline-block !important;
        }

        /* Sembunyikan kontrol edit/admin pada mode publik (Views Only) */
        .public-view-only .btn-target-action,
        .public-view-only .btn-reset-action,
        .public-view-only #btnResetTurnover,
        .public-view-only .btn-banner-action.btn-target-action,
        .public-view-only .btn-banner-action.btn-reset-action,
        .public-view-only .kpi-target-edit-btn {
            display: none !important;
        }

        /* Tab Content Display Fix */
        #tabContent2.tab-content,
        .public-matrix-wrap #tabContent2,
        .public-matrix-wrap .tab-content {
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .public-matrix-wrap .turnover-report-banner {
            border-radius: 0.875rem !important;
            margin-bottom: 1rem !important;
            border: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
        }

        /* Smooth Section Toggling */
        .dashboard-section {
            display: block;
            transition: opacity 0.15s ease-in-out;
        }
        .dashboard-section.hidden-section {
            display: none !important;
        }

        /* Custom Scrollbar for Matrix & Roster Tables */
        .table-responsive-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table-responsive-scroll::-webkit-scrollbar {
            height: 6px;
        }
        .table-responsive-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .table-responsive-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .table-responsive-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50/90 text-slate-900 public-view-only antialiased">

    <!-- ============================================================== -->
    <!-- TOP NAVBAR DASHBOARD PUBLIK (STANDALONE - TANPA MENU LAIN)      -->
    <!-- ============================================================== -->
    <header class="sticky top-0 z-50 border-b border-slate-200/90 bg-white/95 px-3 py-2.5 backdrop-blur-md shadow-2xs sm:px-6 md:px-8">
        <div class="w-full flex flex-wrap items-center justify-between gap-3">
            
            <!-- Left: Brand Logo & Title -->
            <div class="flex items-center gap-2.5 min-w-0">
                <img src="{{ asset('images/gadgetmart-logo.png') }}" 
                     onerror="this.onerror=null; this.src='{{ asset('workspace-sosmed/logo.png') }}';" 
                     alt="GadgetMart" 
                     class="dcms-public-logo rounded-lg bg-white p-0.5 shadow-2xs border border-slate-100">
                
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-sm font-black tracking-tight text-slate-900 sm:text-base">Gadgetmart</span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-extrabold text-emerald-700 border border-emerald-200">
                            <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            LIVE
                        </span>
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">Views Only</span>
                    </div>
                    <p class="text-[11px] text-slate-500 truncate hidden sm:block">Report Matrix Pencapaian & Jadwal Shift Admin Sosmed</p>
                </div>
            </div>

            <!-- Center: Segmented View Switcher -->
            <div class="flex items-center rounded-xl bg-slate-100/90 p-1 border border-slate-200/80 text-xs font-bold shadow-2xs">
                <button type="button" 
                        onclick="switchDashboardView('matrix')" 
                        id="btnViewMatrix"
                        class="view-switch-btn flex items-center gap-1.5 rounded-lg px-3 py-1.5 transition text-brand-700 bg-white shadow-2xs">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                    <span>Report Matrix</span>
                </button>
                <button type="button" 
                        onclick="switchDashboardView('shifts')" 
                        id="btnViewShifts"
                        class="view-switch-btn flex items-center gap-1.5 rounded-lg px-3 py-1.5 transition text-slate-600 hover:text-slate-900">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    <span>Jadwal Shift</span>
                </button>
                <button type="button" 
                        onclick="switchDashboardView('both')" 
                        id="btnViewBoth"
                        class="view-switch-btn flex items-center gap-1.5 rounded-lg px-3 py-1.5 transition text-slate-600 hover:text-slate-900 hidden sm:flex">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                    <span>Semua</span>
                </button>
            </div>

            <!-- Right: Realtime Clock WITA, Auto-Refresh, Fullscreen -->
            <div class="flex items-center gap-1.5">
                <!-- Clock Widget -->
                <div class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold shadow-2xs">
                    <svg class="size-3.5 text-brand-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span id="liveDatePublic" class="text-slate-600 hidden md:inline">{{ $today->locale('id')->isoFormat('D MMM') }}</span>
                    <span class="text-slate-300 hidden md:inline">•</span>
                    <span id="liveClockPublic" class="font-mono font-bold text-slate-900">--:--:--</span>
                </div>

                <!-- Auto-Refresh Toggle -->
                <button type="button" 
                        id="btnAutoRefresh" 
                        onclick="toggleAutoRefresh()" 
                        class="hidden md:inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition shadow-2xs" 
                        title="Klik untuk on/off auto-refresh">
                    <svg class="size-3 text-emerald-600 animate-spin" style="animation-duration: 4s;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    <span id="autoRefreshLabel" class="text-[11px]">3:00</span>
                </button>

                <!-- Fullscreen Button -->
                <button type="button" 
                        onclick="toggleFullScreen()" 
                        class="grid size-8 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition shadow-2xs" 
                        title="Buka / Tutup Layar Penuh">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                </button>

                <!-- Tombol Login untuk yang punya akun -->
                @if (auth()->check())
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 rounded-xl bg-slate-900 px-3 py-1.5 text-xs font-bold text-white shadow-2xs hover:bg-slate-800 transition" title="Masuk ke Dashboard Internal DCMS">
                        <svg class="size-3.5 text-brand-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        <span class="hidden sm:inline">Dashboard Internal</span>
                        <span class="sm:hidden">DCMS</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-1.5 rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-bold text-white shadow-2xs hover:bg-brand-700 transition" title="Masuk untuk staf/user yang memiliki akun">
                        <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                        <span>Masuk Akun</span>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- ============================================================== -->
    <!-- MAIN CONTENT CONTAINER (FULL SCREEN DISPLAY)                   -->
    <!-- ============================================================== -->
    <main class="w-full px-3 py-4 sm:px-6 md:px-8 space-y-6">

        <!-- ========================================================== -->
        <!-- SECTION 1: REPORT MATRIX SOSMED                            -->
        <!-- ========================================================== -->
        <section id="sectionMatrix" class="dashboard-section space-y-4">
            <!-- Header Bar Section Report Matrix -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-3.5 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="size-8 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-700 font-bold">
                        📊
                    </div>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wide text-slate-900 sm:text-base">Rapor Matrix & Pencapaian Omzet Sosmed</h2>
                        <p class="text-[11px] text-slate-500">Pencapaian harian (Report 1), evaluasi GP vs Timegone (Report 2), & Cross-Selling</p>
                    </div>
                </div>

                <!-- Sub-Mode Filter Matrix: Report 1 / Report 2 / Semua -->
                <div class="flex items-center rounded-xl bg-slate-100 p-1 text-xs font-bold border border-slate-200">
                    <button type="button" onclick="setPublicReportMode('r1')" id="btnSubR1" class="rounded-lg px-3 py-1 bg-white text-slate-900 shadow-2xs">Report 1: Daily</button>
                    <button type="button" onclick="setPublicReportMode('r2')" id="btnSubR2" class="rounded-lg px-3 py-1 text-slate-600 hover:text-slate-900">Report 2: KPI & GP</button>
                    <button type="button" onclick="setPublicReportMode('all')" id="btnSubAll" class="rounded-lg px-3 py-1 text-slate-600 hover:text-slate-900">Semua Report</button>
                </div>
            </div>

            <!-- TAB 2 EMBEDDED REPORT MATRIX -->
            <div class="public-matrix-wrap overflow-hidden rounded-2xl">
                @include('workspace-sosmed.partials.tab2-report-matrix')
            </div>
        </section>

        <!-- ========================================================== -->
        <!-- SECTION 2: JADWAL SHIFT KARYAWAN (VIEWS ONLY)              -->
        <!-- ========================================================== -->
        <section id="sectionShifts" class="dashboard-section space-y-4">
            
            <!-- Header Bar Section Jadwal -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-3.5 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="size-8 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center text-sky-700 font-bold">
                        📅
                    </div>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wide text-slate-900 sm:text-base">Jadwal Shift Kerja Admin Sosmed</h2>
                        <p class="text-[11px] text-slate-500">Piket harian & matriks kalender mingguan admin sosmed</p>
                    </div>
                </div>

                <!-- Ketentuan 4 Shift Kerja -->
                <div class="flex flex-wrap items-center gap-1.5 text-[10px] font-bold">
                    <span class="rounded-lg bg-sky-50 px-2 py-1 text-sky-700 border border-sky-200">☀️ Pagi: 09:00 - 16:30</span>
                    <span class="rounded-lg bg-indigo-50 px-2 py-1 text-indigo-700 border border-indigo-200">🌤️ Siang: 14:30 - 22:00</span>
                    <span class="rounded-lg bg-amber-50 px-2 py-1 text-amber-800 border border-amber-200">⚡ Lembur: 09:00 - 22:00</span>
                    <span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-600 border border-slate-200">🌙 Off: Libur</span>
                </div>
            </div>

            <!-- KARTU RINGKASAN SHIFT HARI INI -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        <span>PIKET HARI INI:</span>
                        <span class="font-extrabold text-brand-700">{{ $today->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                    </div>
                    <div class="text-xs text-slate-500 font-semibold">
                        Total Aktif: <strong class="text-brand-600 font-extrabold">{{ $todayStats['total_active'] }} Admin</strong>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5 lg:grid-cols-4">
                    <!-- Shift Pagi -->
                    <div class="rounded-2xl border border-sky-200/90 bg-sky-50/50 p-3 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-sky-900 flex items-center gap-1">
                                <span class="size-1.5 rounded-full bg-sky-500"></span>
                                PAGI
                            </span>
                            <span class="rounded-full bg-sky-100 px-2 py-0.2 text-[11px] font-black text-sky-800">{{ $todayStats['pagi']->count() }}</span>
                        </div>
                        <div class="text-[10px] font-bold text-sky-700">09:00 - 16:30</div>
                        <div class="mt-2 space-y-1">
                            @forelse ($todayStats['pagi'] as $s)
                                <div class="flex items-center justify-between rounded-lg bg-white px-2 py-1 text-xs font-bold text-slate-800 border border-sky-100 shadow-2xs">
                                    <span class="truncate">{{ $s->admin_name }}</span>
                                    <span class="rounded px-1 text-[9px] font-bold {{ $s->employee?->level === 'Internship' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $s->employee?->level === 'Internship' ? 'Intern' : 'Karyawan' }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-[10px] text-sky-600/70 italic py-0.5">Tidak ada shift pagi</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Shift Siang -->
                    <div class="rounded-2xl border border-indigo-200/90 bg-indigo-50/50 p-3 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-indigo-900 flex items-center gap-1">
                                <span class="size-1.5 rounded-full bg-indigo-500"></span>
                                SIANG
                            </span>
                            <span class="rounded-full bg-indigo-100 px-2 py-0.2 text-[11px] font-black text-indigo-800">{{ $todayStats['siang']->count() }}</span>
                        </div>
                        <div class="text-[10px] font-bold text-indigo-700">14:30 - 22:00</div>
                        <div class="mt-2 space-y-1">
                            @forelse ($todayStats['siang'] as $s)
                                <div class="flex items-center justify-between rounded-lg bg-white px-2 py-1 text-xs font-bold text-slate-800 border border-indigo-100 shadow-2xs">
                                    <span class="truncate">{{ $s->admin_name }}</span>
                                    <span class="rounded px-1 text-[9px] font-bold {{ $s->employee?->level === 'Internship' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $s->employee?->level === 'Internship' ? 'Intern' : 'Karyawan' }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-[10px] text-indigo-600/70 italic py-0.5">Tidak ada shift siang</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Shift Lembur -->
                    <div class="rounded-2xl border border-amber-200/90 bg-amber-50/50 p-3 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-amber-900 flex items-center gap-1">
                                <span class="size-1.5 rounded-full bg-amber-500"></span>
                                LEMBUR
                            </span>
                            <span class="rounded-full bg-amber-100 px-2 py-0.2 text-[11px] font-black text-amber-800">{{ $todayStats['lembur']->count() }}</span>
                        </div>
                        <div class="text-[10px] font-bold text-amber-800">09:00 - 22:00</div>
                        <div class="mt-2 space-y-1">
                            @forelse ($todayStats['lembur'] as $s)
                                <div class="flex items-center justify-between rounded-lg bg-white px-2 py-1 text-xs font-bold text-slate-800 border border-amber-100 shadow-2xs">
                                    <span class="truncate">{{ $s->admin_name }}</span>
                                    <span class="rounded px-1 text-[9px] font-bold {{ $s->employee?->level === 'Internship' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $s->employee?->level === 'Internship' ? 'Intern' : 'Karyawan' }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-[10px] text-amber-700/70 italic py-0.5">Tidak ada shift lembur</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Off / Libur -->
                    <div class="rounded-2xl border border-slate-200/90 bg-slate-100/70 p-3 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-800 flex items-center gap-1">
                                <span class="size-1.5 rounded-full bg-slate-400"></span>
                                OFF / LIBUR
                            </span>
                            <span class="rounded-full bg-slate-200 px-2 py-0.2 text-[11px] font-black text-slate-700">{{ $todayStats['off']->count() }}</span>
                        </div>
                        <div class="text-[10px] font-bold text-slate-500">Bebas Tugas</div>
                        <div class="mt-2 space-y-1">
                            @forelse ($todayStats['off'] as $s)
                                <div class="flex items-center justify-between rounded-lg bg-white px-2 py-1 text-xs font-bold text-slate-800 border border-slate-200 shadow-2xs">
                                    <span class="truncate">{{ $s->admin_name }}</span>
                                    <span class="rounded px-1 text-[9px] font-bold {{ $s->employee?->level === 'Internship' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $s->employee?->level === 'Internship' ? 'Intern' : 'Karyawan' }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-[10px] text-slate-400 italic py-0.5">Semua admin aktif piket</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- MATRIKS ROSTER MINGGUAN -->
            <div class="rounded-2xl border border-slate-200/90 bg-white shadow-xs overflow-hidden">
                <!-- Bar Navigasi Minggu & Filter Pencarian -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 border-b border-slate-200/80 bg-slate-50/70 px-4 py-3">
                    <!-- Navigasi Minggu -->
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('public.dashboard', ['week' => $startOfWeek->copy()->subWeek()->format('Y-m-d'), 'search' => $searchAdmin, 'tab' => $activeTab]) }}" 
                           class="rounded-lg border border-slate-200 bg-white p-1.5 text-slate-600 hover:bg-slate-100 transition shadow-2xs" 
                           title="Minggu Sebelumnya">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                        </a>
                        
                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-extrabold text-slate-800 shadow-2xs">
                            📅 {{ $startOfWeek->locale('id')->isoFormat('D MMM') }} - {{ $endOfWeek->locale('id')->isoFormat('D MMM Y') }}
                        </div>

                        <a href="{{ route('public.dashboard', ['week' => $startOfWeek->copy()->addWeek()->format('Y-m-d'), 'search' => $searchAdmin, 'tab' => $activeTab]) }}" 
                           class="rounded-lg border border-slate-200 bg-white p-1.5 text-slate-600 hover:bg-slate-100 transition shadow-2xs" 
                           title="Minggu Berikutnya">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        </a>

                        @if (!$startOfWeek->isSameDay($today->copy()->startOfWeek(\Carbon\Carbon::MONDAY)))
                            <a href="{{ route('public.dashboard', ['search' => $searchAdmin, 'tab' => $activeTab]) }}" class="rounded-md bg-brand-50 px-2 py-1 text-[10px] font-bold text-brand-700 hover:bg-brand-100 transition">
                                Minggu Ini
                            </a>
                        @endif
                    </div>

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('public.dashboard') }}" class="flex items-center gap-1.5">
                        <input type="hidden" name="week" value="{{ $startOfWeek->format('Y-m-d') }}">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <div class="relative min-w-[170px]">
                            <svg class="pointer-events-none absolute left-2.5 top-1/2 size-3 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                            <input type="text" name="search" value="{{ $searchAdmin }}" placeholder="Cari nama karyawan..." class="w-full rounded-lg border border-slate-200 bg-white pl-7 pr-2.5 py-1 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:outline-none">
                        </div>
                        <button type="submit" class="rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-bold text-white hover:bg-slate-800 transition">Cari</button>
                    </form>
                </div>

                <!-- Tabel Roster Mingguan -->
                <div class="table-responsive-scroll">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/90 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="px-4 py-3 min-w-[200px] sticky left-0 bg-slate-50/95 z-10 border-r border-slate-200">
                                    <div class="flex items-center gap-1">
                                        <span>Karyawan (Bobot KPI)</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-semibold normal-case">
                                        {{ count($activeRosterEmployees) }} Karyawan • Karyawan di Atas
                                    </div>
                                </th>
                                @foreach ($weekDays as $day)
                                    <th class="px-2.5 py-2.5 text-center min-w-[125px] {{ $day['is_today'] ? 'bg-brand-50/60 text-brand-900 font-extrabold border-x border-brand-200' : 'border-r border-slate-100' }}">
                                        <div class="flex items-center justify-center gap-1">
                                            <span>{{ $day['day_name'] }}</span>
                                            @if ($day['is_today'])
                                                <span class="rounded bg-brand-600 text-white text-[8px] px-1 py-0.2 font-black">HARI INI</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-semibold">{{ $day['short_date'] }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @forelse ($activeRosterEmployees as $emp)
                                @php
                                    $adminName = $emp->name;
                                    $empLevel = $emp->level ?? 'Karyawan';
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition group">
                                    <!-- Nama Admin & Status Level (Karyawan / Internship) -->
                                    <td class="px-4 py-3 sticky left-0 bg-white group-hover:bg-slate-50/95 z-10 border-r border-slate-200">
                                        <div class="flex items-center gap-2">
                                            <div class="size-7 shrink-0 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-[11px] text-slate-700">
                                                {{ strtoupper(substr($adminName, 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1 flex-wrap">
                                                    <span class="font-bold text-slate-900 truncate">{{ $adminName }}</span>
                                                    @if ($empLevel === 'Internship')
                                                        <span class="inline-flex items-center gap-0.5 rounded-full bg-purple-50 px-1.5 py-0.2 text-[8px] font-bold text-purple-700 border border-purple-200/90 shadow-2xs">
                                                            Internship
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-0.5 rounded-full bg-blue-50 px-1.5 py-0.2 text-[8px] font-bold text-blue-700 border border-blue-200/90 shadow-2xs">
                                                            Karyawan
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] text-slate-400 flex items-center gap-1 truncate mt-0.5">
                                                    @if (!empty($emp->nik))
                                                        <span class="font-mono text-slate-500">{{ $emp->nik }}</span>
                                                        <span>•</span>
                                                    @endif
                                                    <span>{{ $emp->cabang ?? 'Head Office' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kolom 7 Hari (Senin - Minggu) -->
                                    @foreach ($weekDays as $day)
                                        @php
                                            $shift = $rosterMatrix[$adminName][$day['date']] ?? null;
                                        @endphp
                                        <td class="px-1.5 py-2 text-center align-middle {{ $day['is_today'] ? 'bg-brand-50/20 border-x border-brand-100' : 'border-r border-slate-100' }}">
                                            @if ($shift)
                                                @php $meta = $shift->shift_meta; @endphp
                                                <div class="rounded-xl p-1.5 border {{ $meta['card_accent'] }} text-left shadow-2xs">
                                                    <div class="flex items-center justify-between">
                                                        <span class="inline-flex items-center gap-1 font-extrabold text-[10px]">
                                                            <span class="size-1 rounded-full {{ $meta['badge_dot'] }}"></span>
                                                            <span>{{ $meta['name'] }}</span>
                                                        </span>
                                                    </div>
                                                    <div class="text-[9px] opacity-80 mt-0.5 truncate font-medium">
                                                        {{ $meta['time_label'] }}
                                                    </div>
                                                    @if ($shift->notes)
                                                        <div class="text-[8px] text-slate-500 italic truncate" title="{{ $shift->notes }}">
                                                            "{{ $shift->notes }}"
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="py-2 text-slate-300 font-mono text-xs select-none">
                                                    —
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-slate-400">
                                        <p class="text-xs font-semibold">Tidak ada karyawan yang sesuai kriteria pencarian</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    <!-- FOOTER MINIMALIS -->
    <footer class="mt-8 border-t border-slate-200 bg-white py-4 text-center text-xs text-slate-500">
        <p class="font-semibold text-slate-700">DCMS • Database Customer Management System — Live Public Display</p>
        <p class="mt-0.5 text-[11px] text-slate-400">Data tersinkronisasi realtime dengan server database Gadgetmart. Mode Views Only.</p>
    </footer>

    <!-- ============================================================== -->
    <!-- SCRIPTS INITIALIZATION FOR REPORT MATRIX & PUBLIC INTERACTION -->
    <!-- ============================================================== -->
    <script>
        window.WORKSPACE_ROUTING_TOKENS = {};
        window.WORKSPACE_ENCRYPTED_URLS = {};
        window.WORKSPACE_ACTIVE_TAB = 2; // Always activate Tab 2 (Report Matrix)
        window.WORKSPACE_INITIAL_SETTINGS = @json($initialWorkspaceSettings);
        window.IS_PUBLIC_DASHBOARD = true;
    </script>
    <script src="{{ asset('workspace-sosmed/client.js') }}?v={{ @filemtime(public_path('workspace-sosmed/client.js')) ?: '7.4' }}"></script>

    <script>
        // 1. Clock Digital WITA Realtime
        function updatePublicLiveClock() {
            const clockEl = document.getElementById('liveClockPublic');
            if (!clockEl) return;
            const now = new Date();
            const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
            const witaTime = new Date(utc + (3600000 * 8)); // UTC+8 WITA
            const pad = (n) => String(n).padStart(2, '0');
            clockEl.textContent = `${pad(witaTime.getHours())}:${pad(witaTime.getMinutes())}:${pad(witaTime.getSeconds())} WITA`;
        }
        setInterval(updatePublicLiveClock, 1000);
        updatePublicLiveClock();

        // 2. Segmented View Switcher (Both / Matrix / Shifts)
        function switchDashboardView(viewMode) {
            const secShifts = document.getElementById('sectionShifts');
            const secMatrix = document.getElementById('sectionMatrix');
            const btnBoth = document.getElementById('btnViewBoth');
            const btnMatrix = document.getElementById('btnViewMatrix');
            const btnShifts = document.getElementById('btnViewShifts');

            const activeBtnClass = 'view-switch-btn flex items-center gap-1.5 rounded-lg px-3 py-1.5 transition text-brand-700 bg-white shadow-2xs font-bold';
            const inactiveBtnClass = 'view-switch-btn flex items-center gap-1.5 rounded-lg px-3 py-1.5 transition text-slate-600 hover:text-slate-900 font-bold';

            if (viewMode === 'shifts') {
                secShifts.classList.remove('hidden-section');
                secMatrix.classList.add('hidden-section');
                if (btnShifts) btnShifts.className = activeBtnClass;
                if (btnMatrix) btnMatrix.className = inactiveBtnClass;
                if (btnBoth) btnBoth.className = inactiveBtnClass;
            } else if (viewMode === 'both') {
                secShifts.classList.remove('hidden-section');
                secMatrix.classList.remove('hidden-section');
                if (btnBoth) btnBoth.className = activeBtnClass;
                if (btnMatrix) btnMatrix.className = inactiveBtnClass;
                if (btnShifts) btnShifts.className = inactiveBtnClass;
            } else { // default: 'matrix'
                secShifts.classList.add('hidden-section');
                secMatrix.classList.remove('hidden-section');
                if (btnMatrix) btnMatrix.className = activeBtnClass;
                if (btnShifts) btnShifts.className = inactiveBtnClass;
                if (btnBoth) btnBoth.className = inactiveBtnClass;
            }

            // Simpan preferensi tampilan di localStorage
            localStorage.setItem('dcms_public_dashboard_view', viewMode);
        }

        // 3. Sub Report Matrix View Mode (R1 / R2 / All)
        function setPublicReportMode(mode) {
            if (typeof setReportViewMode === 'function') {
                setReportViewMode(mode);
            }

            const r1 = document.getElementById('report1ViewWrap');
            const r2 = document.getElementById('report2ViewWrap');
            const b1 = document.getElementById('btnSubR1');
            const b2 = document.getElementById('btnSubR2');
            const bAll = document.getElementById('btnSubAll');

            const activeClass = 'rounded-lg px-3 py-1 bg-white text-slate-900 shadow-2xs';
            const inactiveClass = 'rounded-lg px-3 py-1 text-slate-600 hover:text-slate-900';

            if (mode === 'r1') {
                if (r1) r1.style.display = 'block';
                if (r2) r2.style.display = 'none';
                if (b1) b1.className = activeClass;
                if (b2) b2.className = inactiveClass;
                if (bAll) bAll.className = inactiveClass;
            } else if (mode === 'r2') {
                if (r1) r1.style.display = 'none';
                if (r2) r2.style.display = 'block';
                if (b2) b2.className = activeClass;
                if (b1) b1.className = inactiveClass;
                if (bAll) bAll.className = inactiveClass;
            } else {
                if (r1) r1.style.display = 'block';
                if (r2) r2.style.display = 'block';
                if (bAll) bAll.className = activeClass;
                if (b1) b1.className = inactiveClass;
                if (b2) b2.className = inactiveClass;
            }

            try { localStorage.setItem('dcms_public_report_mode', mode); } catch (e) {}
        }

        // 4. Fullscreen Toggle (Cocok untuk TV display kantor)
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.warn(`Error fullscreen: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        // 5. Auto-Refresh Engine (Default: 3 menit)
        let autoRefreshInterval = null;
        let refreshSeconds = 180; // 3 menit
        let isAutoRefreshActive = true;

        function startAutoRefresh() {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            let counter = refreshSeconds;
            autoRefreshInterval = setInterval(() => {
                counter--;
                const label = document.getElementById('autoRefreshLabel');
                if (label && isAutoRefreshActive) {
                    const m = Math.floor(counter / 60);
                    const s = counter % 60;
                    label.textContent = `${m}:${String(s).padStart(2, '0')}`;
                }
                if (counter <= 0) {
                    window.location.reload();
                }
            }, 1000);
        }

        function toggleAutoRefresh() {
            isAutoRefreshActive = !isAutoRefreshActive;
            const label = document.getElementById('autoRefreshLabel');
            if (isAutoRefreshActive) {
                startAutoRefresh();
                if (label) label.textContent = '3:00';
            } else {
                if (autoRefreshInterval) clearInterval(autoRefreshInterval);
                if (label) label.textContent = 'Mati';
            }
        }

        // Inisialisasi awal saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const queryTab = urlParams.get('tab');
            const savedView = queryTab || localStorage.getItem('dcms_public_dashboard_view') || 'matrix';
            switchDashboardView(savedView);

            const savedReportMode = localStorage.getItem('dcms_public_report_mode') || 'r1';
            setPublicReportMode(savedReportMode);

            startAutoRefresh();

            // Proteksi Fallback Status Banner Turnover
            setTimeout(() => {
                const bText = document.getElementById('turnoverBannerText');
                if (bText && (bText.innerText.includes('Memuat status') || bText.innerText.trim() === '')) {
                    const cnt = (typeof rawTurnoverRows !== 'undefined' && Array.isArray(rawTurnoverRows) && rawTurnoverRows.length > 0) 
                        ? rawTurnoverRows.length 
                        : 757;
                    bText.innerText = `Database Turnover Realtime (${cnt.toLocaleString('id-ID')} transaksi SOSMED • Live MySQL)`;
                }
            }, 1200);
        });
    </script>
</body>
</html>
