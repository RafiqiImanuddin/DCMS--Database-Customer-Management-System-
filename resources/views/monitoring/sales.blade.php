@extends('layouts.app')

@section('title', 'Monitoring Kinerja Admin Sales - Harian')

@section('content')
<div class="space-y-6">
    <!-- Header Page & Date Navigator -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-700">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5"/></svg>
                    Manager Control Room
                </span>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-semibold text-slate-500">Evaluasi Kualitas Sales Harian</span>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">Monitoring Kualitas Kerja Admin Sales</h1>
            <p class="text-xs text-slate-500 sm:text-sm">Penilaian harian cara balas chat konsumen, respon time, etika pelayanan, dan kepatuhan penawaran promo.</p>
        </div>

        <!-- Quick Date Jump & Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Jump to Yesterday -->
            <a href="{{ route('monitoring.sales.index', ['date' => $yesterdayDate, 'cabang' => $selectedCabang]) }}" 
               class="inline-flex items-center gap-1 rounded-xl border {{ $selectedPeriod === $yesterdayDate ? 'border-indigo-600 bg-indigo-50 text-indigo-700 font-black' : 'border-slate-200 bg-white text-slate-700 font-semibold hover:bg-slate-50' }} px-3 py-2 text-xs transition shadow-2xs">
                <span>◀ Kemarin</span>
            </a>

            <!-- Jump to Today -->
            <a href="{{ route('monitoring.sales.index', ['date' => $todayDate, 'cabang' => $selectedCabang]) }}" 
               class="inline-flex items-center gap-1.5 rounded-xl border {{ $selectedPeriod === $todayDate ? 'border-indigo-600 bg-indigo-600 text-white font-black shadow-indigo-600/20' : 'border-slate-200 bg-white text-slate-700 font-semibold hover:bg-slate-50' }} px-3.5 py-2 text-xs transition shadow-2xs">
                <span class="size-2 rounded-full {{ $selectedPeriod === $todayDate ? 'bg-white' : 'bg-emerald-500 animate-pulse' }}"></span>
                <span>Hari Ini</span>
            </a>

            <!-- Export CSV -->
            <a href="{{ route('monitoring.sales.export', ['period' => $selectedPeriod, 'cabang' => $selectedCabang]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <span>Export CSV</span>
            </a>

            @if (auth()->check() && auth()->user()->isSuperAdmin())
            <!-- Setting Karyawan & Bobot (Superadmin Only) -->
            <a href="{{ \App\Support\RouteCipher::url('settings.karyawan.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:bg-slate-800 transition">
                <svg class="size-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z"/></svg>
                <span>Atur Bobot KPI</span>
            </a>
            @endif
        </div>
    </div>

    <!-- KPI Summary Metric Cards (Daily Performance) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1: Rata-rata Skor Harian -->
        <div class="dcms-card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rata-rata Skor Tim</span>
                <span class="grid size-9 place-items-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black tracking-tight text-slate-900">{{ $averageTeamScore }}</span>
                <span class="text-xs font-bold text-slate-400">/ 100</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="font-medium text-slate-500">Dari {{ $evaluatedCount }} sales dinilai</span>
                <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $averageTeamScore >= 85 ? 'bg-emerald-50 text-emerald-700' : ($averageTeamScore >= 75 ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">
                    {{ $averageTeamScore >= 85 ? 'Target Tercapai' : ($averageTeamScore >= 75 ? 'Standard Baik' : 'Butuh Dorongan') }}
                </span>
            </div>
        </div>

        <!-- Card 2: Status Evaluasi Harian -->
        <div class="dcms-card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Admin Sales Aktif</span>
                <span class="grid size-9 place-items-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black tracking-tight text-slate-900">{{ $evaluatedCount }}</span>
                <span class="text-xs font-bold text-slate-400">/ {{ $allEmployees->count() }} Sales</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-emerald-700 font-bold">{{ $evaluatedCount }} Sudah Diisi</span>
                <span class="font-bold {{ $allEmployees->count() - $evaluatedCount > 0 ? 'text-amber-600' : 'text-slate-400' }}">{{ $allEmployees->count() - $evaluatedCount }} Menunggu Input</span>
            </div>
        </div>

        <!-- Card 3: Top Performer Hari Ini -->
        <div class="dcms-card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Top Performer Hari Ini</span>
                <span class="grid size-9 place-items-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                </span>
            </div>
            @if ($topPerformer)
                <div class="mt-2 truncate font-black text-slate-900 text-base leading-tight">{{ $topPerformer['employee']->name }}</div>
                <div class="mt-1 flex items-center justify-between text-xs">
                    <span class="rounded bg-slate-100 px-1.5 py-0.5 font-bold text-slate-700">🏢 {{ $topPerformer['employee']->cabang }}</span>
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 font-black text-emerald-800">Skor {{ $topPerformer['score'] }} (Grade {{ $topPerformer['grade'] }})</span>
                </div>
            @else
                <div class="mt-3 text-sm font-semibold text-slate-400">Belum ada penilaian</div>
                <div class="mt-2 text-xs text-slate-400">Input nilai evaluasi hari ini</div>
            @endif
        </div>

        <!-- Card 4: Perlu Coaching / Bimbingan Hari Ini -->
        <div class="dcms-card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Perlu Coaching</span>
                <span class="grid size-9 place-items-center rounded-xl bg-red-50 text-red-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black tracking-tight text-slate-900">{{ $coachingNeededCount }}</span>
                <span class="text-xs font-bold text-slate-400">karyawan</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                <span>Skor di bawah target (&lt; 75 / Grade C-D)</span>
            </div>
        </div>
    </div>

    <!-- Filter & Toolbar Tanggal Harian -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
        <form method="GET" action="{{ route('monitoring.sales.index') }}" class="flex flex-wrap items-center gap-3">
            <!-- Daily Date Picker -->
            <div class="w-56">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Evaluasi Harian</label>
                <div class="relative mt-1">
                    <input type="date" 
                           name="date" 
                           value="{{ $isDaily ? $selectedPeriod : $todayDate }}" 
                           onchange="this.form.submit()" 
                           class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-900 focus:border-indigo-600 focus:outline-none">
                </div>
            </div>

            <!-- Cabang Filter -->
            <div class="w-44">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Cabang</label>
                <select name="cabang" onchange="this.form.submit()" class="dcms-select mt-1 w-full text-xs font-semibold text-slate-700">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b }}" {{ $selectedCabang === $b ? 'selected' : '' }}>Cabang {{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Grade Filter -->
            <div class="w-40">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Predikat / Grade</label>
                <select name="grade" onchange="this.form.submit()" class="dcms-select mt-1 w-full text-xs font-semibold text-slate-700">
                    <option value="">Semua Grade</option>
                    <option value="A" {{ $selectedGrade === 'A' ? 'selected' : '' }}>Grade A (Sangat Baik)</option>
                    <option value="B" {{ $selectedGrade === 'B' ? 'selected' : '' }}>Grade B (Baik)</option>
                    <option value="C" {{ $selectedGrade === 'C' ? 'selected' : '' }}>Grade C (Cukup)</option>
                    <option value="D" {{ $selectedGrade === 'D' ? 'selected' : '' }}>Grade D (Perlu Coaching)</option>
                    <option value="NONE" {{ $selectedGrade === 'NONE' ? 'selected' : '' }}>Belum Dinilai</option>
                </select>
            </div>

            <!-- Search Input -->
            <div class="min-w-[180px] flex-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Cari Karyawan</label>
                <div class="relative mt-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NIK sales..." class="w-full rounded-xl border border-slate-200 bg-white pl-8 pr-3 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:outline-none">
                </div>
            </div>

            <div class="flex items-end gap-2 pt-4">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 transition">Terapkan</button>
                @if ($selectedCabang || $selectedGrade || $search || ($selectedPeriod !== $todayDate))
                    <a href="{{ route('monitoring.sales.index', ['date' => $todayDate]) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Reset Hari Ini</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Active Filter Badge Info -->
    <div class="flex items-center justify-between rounded-xl bg-indigo-50/70 border border-indigo-100 px-4 py-2 text-xs text-indigo-900 font-medium">
        <div class="flex items-center gap-2">
            <span class="font-bold">📅 Menampilkan Evaluasi Harian:</span>
            <span class="rounded-md bg-white px-2.5 py-0.5 font-black text-indigo-700 shadow-2xs border border-indigo-200">{{ $periodDisplayLabel }}</span>
            @if (!auth()->check() || !auth()->user()->isSuperAdmin())
                <span class="inline-flex items-center gap-1 rounded-md bg-amber-100/80 px-2 py-0.5 font-bold text-amber-800 border border-amber-300 text-[10px]">
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                    <span>Mode Baca (Hanya Superadmin yang dapat mengisi/edit nilai)</span>
                </span>
            @endif
        </div>
        <div class="text-[11px] text-indigo-600 hidden sm:block">
            4 Aspek Penilaian: <strong>Balas Chat</strong>, <strong>Respon Time</strong>, <strong>Etika</strong>, & <strong>Kepatuhan Promo</strong> (Masing-masing 25%)
        </div>
    </div>

    <!-- Monitoring Table -->
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3.5 text-center w-12">No</th>
                        <th class="px-5 py-3.5">Admin Sales</th>
                        <th class="px-5 py-3.5">Cabang</th>
                        <th class="px-5 py-3.5">Skor Hari Ini</th>
                        <th class="px-5 py-3.5 text-center">Grade</th>
                        <th class="px-5 py-3.5">4 Kriteria Penilaian Harian</th>
                        <th class="px-5 py-3.5">Catatan Evaluator</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($allEmployees as $index => $emp)
                    @php
                        $assessment = $emp->assessmentForPeriod;
                        $score = $assessment ? (float)$assessment->final_score : null;
                        $grade = $assessment ? strtoupper($assessment->grade) : null;
                        $scores = $assessment?->scores ?? [];
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-4 text-center font-bold text-slate-400">
                            {{ $index + 1 }}
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="grid size-9 shrink-0 place-items-center rounded-xl font-black text-white text-xs shadow-xs" style="background-color: {{ $emp->avatar_color ?: '#2563eb' }}">
                                    {{ $emp->initials }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $emp->name }}</div>
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                        <span class="font-mono">{{ $emp->nik }}</span>
                                        <span>•</span>
                                        <span>{{ $emp->phone }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">
                                🏢 {{ $emp->cabang }}
                            </span>
                        </td>

                        <td class="px-5 py-4">
                            @if ($score !== null)
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-base font-black text-slate-900">{{ $score }}</span>
                                    <span class="text-[10px] text-slate-400 font-semibold">/ 100</span>
                                </div>
                                <div class="mt-1 h-1.5 w-28 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full transition-all {{ $score >= 90 ? 'bg-emerald-500' : ($score >= 80 ? 'bg-blue-500' : ($score >= 70 ? 'bg-amber-500' : 'bg-red-500')) }}" style="width: {{ $score }}%"></div>
                                </div>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-400 italic">Belum Dinilai</span>
                            @endif
                        </td>

                        <td class="px-5 py-4 text-center">
                            @if ($grade)
                                <span class="inline-flex items-center justify-center rounded-xl border px-3 py-1 font-black text-xs {{ \App\Models\SalesAssessment::getGradeBadgeClasses($grade) }}">
                                    Grade {{ $grade }}
                                </span>
                            @else
                                <span class="text-slate-300 font-bold">-</span>
                            @endif
                        </td>

                        <td class="px-5 py-4">
                            @if ($assessment && !empty($scores))
                                <div class="grid grid-cols-2 gap-1.5 max-w-xs text-[11px]">
                                    <!-- 1. Balas Chat -->
                                    <div class="flex items-center justify-between rounded bg-slate-50 px-2 py-0.5 border border-slate-200/60" title="Cara Balas Chat Konsumen">
                                        <span class="text-slate-500 text-[10px] font-bold">💬 Chat:</span>
                                        <span class="font-bold {{ ($scores['chat_reply'] ?? 0) >= 85 ? 'text-emerald-700' : (($scores['chat_reply'] ?? 0) >= 75 ? 'text-blue-700' : 'text-amber-700') }}">{{ $scores['chat_reply'] ?? ($scores['csat_quality'] ?? '-') }}</span>
                                    </div>
                                    <!-- 2. Respon Time -->
                                    <div class="flex items-center justify-between rounded bg-slate-50 px-2 py-0.5 border border-slate-200/60" title="Respon Time (Kecepatan Balas)">
                                        <span class="text-slate-500 text-[10px] font-bold">⚡ Respon:</span>
                                        <span class="font-bold {{ ($scores['resp_time'] ?? 0) >= 85 ? 'text-emerald-700' : (($scores['resp_time'] ?? 0) >= 75 ? 'text-blue-700' : 'text-amber-700') }}">{{ $scores['resp_time'] ?? '-' }}</span>
                                    </div>
                                    <!-- 3. Etika -->
                                    <div class="flex items-center justify-between rounded bg-slate-50 px-2 py-0.5 border border-slate-200/60" title="Etika & Keramahan">
                                        <span class="text-slate-500 text-[10px] font-bold">🤝 Etika:</span>
                                        <span class="font-bold {{ ($scores['ethics'] ?? 0) >= 85 ? 'text-emerald-700' : (($scores['ethics'] ?? 0) >= 75 ? 'text-blue-700' : 'text-amber-700') }}">{{ $scores['ethics'] ?? '-' }}</span>
                                    </div>
                                    <!-- 4. Promo -->
                                    <div class="flex items-center justify-between rounded bg-slate-50 px-2 py-0.5 border border-slate-200/60" title="Kesesuaian Penawaran Promo">
                                        <span class="text-slate-500 text-[10px] font-bold">🏷️ Promo:</span>
                                        <span class="font-bold {{ ($scores['promo_check'] ?? 0) >= 85 ? 'text-emerald-700' : (($scores['promo_check'] ?? 0) >= 75 ? 'text-blue-700' : 'text-amber-700') }}">{{ $scores['promo_check'] ?? '-' }}</span>
                                    </div>
                                </div>
                            @else
                                <span class="text-[11px] text-slate-400 italic">Belum ada input</span>
                            @endif
                        </td>

                        <td class="px-5 py-4 max-w-xs truncate text-[11px] text-slate-600">
                            @if ($assessment && $assessment->notes)
                                <span title="{{ $assessment->notes }}">{{ Str::limit($assessment->notes, 40) }}</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if ($assessment)
                                    <button type="button" 
                                            onclick="openReportModal({{ $assessment->id }})" 
                                            class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50/50 px-2.5 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition shadow-2xs"
                                            title="Lihat Rapor Harian & Salin Format WhatsApp">
                                        <svg class="size-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                        <span>Rapor</span>
                                    </button>
                                @endif

                                @if (auth()->check() && auth()->user()->isSuperAdmin())
                                    <button type="button" 
                                            onclick="openAssessModal({{ json_encode($emp) }}, {{ json_encode($assessment) }})" 
                                            class="inline-flex items-center gap-1 rounded-lg {{ $assessment ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-brand-600 hover:bg-brand-700 text-white animate-pulse' }} px-3 py-1.5 text-xs font-bold shadow-xs transition"
                                            title="{{ $assessment ? 'Edit Nilai Hari Ini' : 'Beri Nilai Hari Ini' }}">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                        <span>{{ $assessment ? 'Edit Nilai' : 'Input Nilai' }}</span>
                                    </button>
                                @elseif (!$assessment)
                                    <span class="text-[11px] font-medium text-slate-400 italic px-2">Hanya Superadmin</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                            <p class="text-sm font-semibold text-slate-600">Tidak ada data sales yang cocok dengan filter saat ini</p>
                            <p class="text-xs text-slate-400">Coba ubah cabang atau reset filter tanggal.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if (auth()->check() && auth()->user()->isSuperAdmin())
<!-- MODAL EVALUASI & PENILAIAN HARIAN SALES (INTERACTIVE MANUAL SCORER) -->
<div id="assessModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeAssessModal()"></div>
    <div class="relative w-full max-w-2xl max-h-[92vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl transition">
        <!-- Modal Header -->
        <div class="flex items-start justify-between border-b border-slate-100 pb-3">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700">
                    Input Evaluasi Harian Manager
                </span>
                <h3 id="assessModalTitle" class="mt-1 text-base font-bold text-slate-900">Penilaian Kinerja Sales Harian</h3>
                <p id="assessModalSubtitle" class="text-xs text-slate-500">Evaluasi cara balas chat, respon time, etika & promo</p>
            </div>
            <button type="button" onclick="closeAssessModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">✕</button>
        </div>

        <form id="assessForm" method="POST" action="{{ route('monitoring.sales.assessment.store') }}" class="mt-4 space-y-5">
            @csrf
            <input type="hidden" id="assessEmployeeId" name="employee_id" value="">

            <!-- Tanggal Evaluasi Selector di dalam Modal -->
            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3 border border-slate-200">
                <div class="text-xs">
                    <span class="font-bold text-slate-700">📅 Tanggal Evaluasi:</span>
                    <span class="text-slate-500 ml-1">Pastikan tanggal sesuai dengan hari kerja yang dinilai</span>
                </div>
                <input type="date" 
                       id="assessPeriod" 
                       name="period" 
                       value="{{ $isDaily ? $selectedPeriod : $todayDate }}" 
                       class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-bold text-slate-800 focus:border-indigo-600 focus:outline-none">
            </div>

            <!-- Live Score Card & Auto-Diagnostic Preview -->
            <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Skor Akhir Terbobot (100%)</div>
                        <div class="flex items-baseline gap-2 mt-0.5">
                            <span id="previewFinalScore" class="text-3xl font-black text-indigo-950">0.00</span>
                            <span class="text-xs font-bold text-indigo-400">/ 100</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Predikat Evaluasi</div>
                        <div id="previewGradeBadge" class="mt-1 inline-flex items-center rounded-xl border border-emerald-300 bg-emerald-100 px-3 py-1 font-black text-xs text-emerald-800">
                            Grade A
                        </div>
                    </div>
                </div>

                <!-- Live Auto-Diagnostic Output -->
                <div id="liveDiagnosticBox" class="rounded-lg bg-white p-2.5 border border-indigo-100 text-[11px] space-y-1 text-slate-700">
                    <div class="flex items-center gap-1.5 font-bold text-indigo-900">
                        <span>💡 Analisis Otomatis Hari Ini:</span>
                    </div>
                    <div id="liveStrengthsText" class="text-emerald-700">✓ Menghitung analisis kekuatan...</div>
                    <div id="liveImprovementsText" class="text-amber-700">• Menghitung poin perbaikan...</div>
                </div>
            </div>

            <!-- 4 Kriteria Penilaian Harian Interaktif -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">4 Aspek Penilaian Kinerja Harian (0 - 100)</span>
                    <button type="button" onclick="autoSyncMetrics()" class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 hover:bg-slate-200 transition">
                        ⚡ Tarik Metrik Awal
                    </button>
                </div>

                @php
                    $presetCriterionMeta = [
                        'chat_reply' => [
                            'icon' => '💬',
                            'title' => '1. Cara Balas Chat Konsumen',
                            'desc' => 'Kualitas jawaban, kejelasan solusi produk, solutif, dan tidak kaku.',
                            'tags' => ['+ Solutif', '+ Penjelasan Jelas', '- Kurang Persuasif', '- Terlalu Kaku/Robot'],
                        ],
                        'resp_time' => [
                            'icon' => '⚡',
                            'title' => '2. Respon Time (Kecepatan Balas)',
                            'desc' => 'Kecepatan membalas pesan masuk dan follow-up prospek saat jam operasional.',
                            'tags' => ['+ < 2 Menit', '+ Standby Disiplin', '- Delay Siang Hari', '- Chat Tertumpuk'],
                        ],
                        'ethics' => [
                            'icon' => '🤝',
                            'title' => '3. Etika & Keramahan',
                            'desc' => 'Sopan santun, keramahan bahasa, salam greeting pembuka & penutup yang hangat.',
                            'tags' => ['+ Sangat Ramah', '+ Greeting Lengkap', '- Kurang Senyum/Kaku', '- Nada Dingin/Judes'],
                        ],
                        'promo_check' => [
                            'icon' => '🏷️',
                            'title' => '4. Kesesuaian Penawaran Promo',
                            'desc' => 'Kepatuhan menawarkan promo aktif (diskon, bundling, cicilan) dan keakuratan info.',
                            'tags' => ['+ Promo Aktif Ditawarkan', '+ Upselling Berhasil', '- Lupa Tawarkan Promo', '- Salah Info Syarat'],
                        ],
                    ];
                @endphp

                @foreach ($kpiCriteria as $crit)
                @php
                    $meta = $presetCriterionMeta[$crit->code] ?? [
                        'icon' => '📌',
                        'title' => $crit->name,
                        'desc' => $crit->description,
                        'tags' => ['+ Sangat Bagus', '+ Sesuai Standar', '- Perlu Evaluasi', '- Butuh Perhatian'],
                    ];
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-3.5 space-y-2.5 transition hover:border-slate-300">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-1.5 font-bold text-xs text-slate-900">
                                <span>{{ $meta['icon'] }}</span>
                                <span>{{ $meta['title'] }}</span>
                                <span class="rounded-full bg-slate-100 px-2 py-0.2 text-[10px] font-bold text-slate-600">Bobot {{ $crit->weight }}%</span>
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $meta['desc'] }}</div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <input type="number" 
                                   id="input_score_{{ $crit->code }}" 
                                   name="scores[{{ $crit->code }}]" 
                                   min="0" 
                                   max="100" 
                                   step="1" 
                                   value="85" 
                                   data-weight="{{ $crit->weight }}"
                                   oninput="updateSliderFromInput('{{ $crit->code }}')" 
                                   class="score-number-input w-16 rounded-lg border border-slate-300 px-2 py-1 text-center text-xs font-black text-slate-900 focus:border-indigo-600 focus:outline-none">
                            <span class="text-xs text-slate-400 font-bold">pt</span>
                        </div>
                    </div>

                    <!-- Slider Bar -->
                    <div class="flex items-center gap-3">
                        <input type="range" 
                               id="slider_score_{{ $crit->code }}" 
                               min="0" 
                               max="100" 
                               value="85" 
                               oninput="updateInputFromSlider('{{ $crit->code }}')"
                               class="w-full accent-indigo-600 cursor-pointer">
                    </div>

                    <!-- Preset Nilai Cepat & Quick Tags -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-slate-100 text-[10px]">
                        <!-- Quick Score Buttons -->
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400 font-bold mr-1">Nilai Cepat:</span>
                            <button type="button" onclick="setQuickScore('{{ $crit->code }}', 100)" class="rounded bg-emerald-50 px-1.5 py-0.5 font-bold text-emerald-700 hover:bg-emerald-100">100</button>
                            <button type="button" onclick="setQuickScore('{{ $crit->code }}', 90)" class="rounded bg-blue-50 px-1.5 py-0.5 font-bold text-blue-700 hover:bg-blue-100">90</button>
                            <button type="button" onclick="setQuickScore('{{ $crit->code }}', 80)" class="rounded bg-slate-100 px-1.5 py-0.5 font-bold text-slate-700 hover:bg-slate-200">80</button>
                            <button type="button" onclick="setQuickScore('{{ $crit->code }}', 70)" class="rounded bg-amber-50 px-1.5 py-0.5 font-bold text-amber-700 hover:bg-amber-100">70</button>
                            <button type="button" onclick="setQuickScore('{{ $crit->code }}', 50)" class="rounded bg-red-50 px-1.5 py-0.5 font-bold text-red-700 hover:bg-red-100">50</button>
                        </div>

                        <!-- Quick Tags to Append to Notes -->
                        <div class="flex flex-wrap items-center gap-1">
                            @foreach ($meta['tags'] as $tag)
                                <button type="button" onclick="appendTagToNotes('{{ $tag }}')" class="rounded border border-slate-200 bg-white px-1.5 py-0.5 text-slate-600 hover:border-indigo-400 hover:text-indigo-600 transition">
                                    {{ $tag }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Evaluator Name -->
            <div>
                <label class="block text-xs font-bold text-slate-700">Nama Evaluator (Manager)</label>
                <input type="text" name="evaluator_name" id="assessEvaluatorName" value="{{ auth()->user()->name ?? 'Manager Sales' }}" class="dcms-input mt-1">
            </div>

            <!-- Catatan Evaluasi Harian -->
            <div>
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700">Catatan Khusus Kinerja Harian</label>
                    <span class="text-[10px] text-slate-400">Klik tag di atas untuk otomatis menambahkan catatan</span>
                </div>
                <textarea name="notes" id="assessNotes" rows="2" placeholder="Tuliskan apresiasi cara komunikasi, kecepatan respon, atau kendala promo hari ini..." class="dcms-input mt-1"></textarea>
            </div>

            <!-- Rekomendasi Coaching Besok -->
            <div>
                <label class="block text-xs font-bold text-slate-700">Rekomendasi / Coaching Plan Hari Berikutnya</label>
                <textarea name="recommendations" id="assessRecommendations" rows="2" placeholder="Langkah perbaikan esok hari (misal: ingatkan flyer promo, pasang notifikasi suara)..." class="dcms-input mt-1"></textarea>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeAssessModal()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-indigo-700">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    <span>Simpan Penilaian Harian</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- MODAL RAPOR KINERJA HARIAN (PRINTABLE SCORECARD & WHATSAPP GENERATOR) -->
<div id="reportModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeReportModal()"></div>
    <div class="relative w-full max-w-2xl max-h-[92vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl transition">
        <!-- Print Header -->
        <div class="flex items-start justify-between border-b border-slate-200 pb-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/gadgetmart-logo.png') }}" alt="Gadgetmart" class="h-10 w-10 shrink-0 rounded-lg bg-slate-100 object-contain p-1">
                <div>
                    <h2 class="text-base font-black text-slate-900 tracking-tight">RAPOR KINERJA HARIAN ADMIN SALES</h2>
                    <p class="text-xs text-slate-500">Database Customer Management System • Gadgetmart</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="btnCopyWa" onclick="copyWhatsAppReport()" class="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition shadow-2xs">
                    📋 Salin Format WhatsApp
                </button>
                <button type="button" onclick="window.print()" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    🖨️ Cetak
                </button>
                <button type="button" onclick="closeReportModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">✕</button>
            </div>
        </div>

        <div id="reportModalContent" class="mt-4 space-y-4">
            <!-- Will be dynamically filled by JS -->
            <div class="py-8 text-center text-slate-400">Memuat data rapor harian...</div>
        </div>
    </div>
</div>

<script>
const activeCriteria = @json($kpiCriteria);
let currentReportWaText = '';

function openAssessModal(employee, assessment) {
    const modal = document.getElementById('assessModal');
    if (!modal) return;

    document.getElementById('assessEmployeeId').value = employee.id;
    document.getElementById('assessModalTitle').innerText = 'Evaluasi Harian: ' + employee.name + ' (' + employee.cabang + ')';
    document.getElementById('assessModalSubtitle').innerText = 'NIK: ' + employee.nik + ' • Posisi: ' + employee.role;

    // Use current date or assessment period
    const defaultDate = assessment && assessment.period ? assessment.period : '{{ $isDaily ? $selectedPeriod : $todayDate }}';
    document.getElementById('assessPeriod').value = defaultDate;

    // Populate existing scores or defaults
    const scores = assessment && assessment.scores ? assessment.scores : {};

    activeCriteria.forEach(c => {
        let val = 85;
        if (scores[c.code] !== undefined) {
            val = scores[c.code];
        } else if (c.code === 'chat_reply' && scores['csat_quality'] !== undefined) {
            val = scores['csat_quality'];
        }
        
        const input = document.getElementById('input_score_' + c.code);
        const slider = document.getElementById('slider_score_' + c.code);
        if (input) input.value = val;
        if (slider) slider.value = val;
    });

    document.getElementById('assessNotes').value = assessment ? (assessment.notes || '') : '';
    document.getElementById('assessRecommendations').value = assessment ? (assessment.recommendations || '') : '';
    if (assessment && assessment.evaluator_name) {
        document.getElementById('assessEvaluatorName').value = assessment.evaluator_name;
    }

    calculateLiveScore();

    document.getElementById('assessModal').classList.remove('hidden');
    document.getElementById('assessModal').classList.add('flex');
}

function closeAssessModal() {
    const modal = document.getElementById('assessModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function setQuickScore(code, score) {
    const input = document.getElementById('input_score_' + code);
    const slider = document.getElementById('slider_score_' + code);
    if (input) input.value = score;
    if (slider) slider.value = score;
    calculateLiveScore();
}

function appendTagToNotes(tag) {
    const notesEl = document.getElementById('assessNotes');
    if (!notesEl) return;
    const current = notesEl.value.trim();
    if (current === '') {
        notesEl.value = tag;
    } else if (!current.includes(tag)) {
        notesEl.value = current + ', ' + tag;
    }
}

function updateSliderFromInput(code) {
    const input = document.getElementById('input_score_' + code);
    const slider = document.getElementById('slider_score_' + code);
    if (input && slider) {
        slider.value = input.value;
    }
    calculateLiveScore();
}

function updateInputFromSlider(code) {
    const input = document.getElementById('input_score_' + code);
    const slider = document.getElementById('slider_score_' + code);
    if (input && slider) {
        input.value = slider.value;
    }
    calculateLiveScore();
}

function calculateLiveScore() {
    let totalScore = 0;
    let totalWeight = 0;
    const currentScores = {};

    activeCriteria.forEach(c => {
        const input = document.getElementById('input_score_' + c.code);
        const val = input ? parseFloat(input.value) || 0 : 0;
        const w = parseFloat(c.weight) || 0;
        totalScore += (val * w) / 100;
        totalWeight += w;
        currentScores[c.code] = val;
    });

    if (totalWeight > 0 && Math.abs(totalWeight - 100) > 0.1) {
        totalScore = (totalScore / totalWeight) * 100;
    }

    totalScore = Math.round(totalScore * 100) / 100;
    document.getElementById('previewFinalScore').innerText = totalScore.toFixed(2);

    let grade = 'D';
    let badgeClass = 'bg-red-100 text-red-800 border-red-300';
    if (totalScore >= 90) {
        grade = 'Grade A (Sangat Memuaskan)';
        badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
    } else if (totalScore >= 80) {
        grade = 'Grade B (Baik / On Target)';
        badgeClass = 'bg-blue-100 text-blue-800 border-blue-300';
    } else if (totalScore >= 70) {
        grade = 'Grade C (Cukup / Butuh Peningkatan)';
        badgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
    } else {
        grade = 'Grade D (Perlu Pembinaan Intensif)';
        badgeClass = 'bg-red-100 text-red-800 border-red-300';
    }

    const badge = document.getElementById('previewGradeBadge');
    badge.className = 'mt-1 inline-flex items-center rounded-xl border px-3 py-1 font-black text-xs ' + badgeClass;
    badge.innerText = grade;

    // Live auto-diagnostic summary
    const strengths = [];
    const deficits = [];

    if ((currentScores['chat_reply'] || 0) >= 90) strengths.push('Cara balas chat solutif & jelas');
    else if ((currentScores['chat_reply'] || 0) < 75) deficits.push('Cara balas chat kaku / kurang persuasif');

    if ((currentScores['resp_time'] || 0) >= 90) strengths.push('Respon time tanggap & standby');
    else if ((currentScores['resp_time'] || 0) < 75) deficits.push('Respon time ada delay / tertumpuk');

    if ((currentScores['ethics'] || 0) >= 90) strengths.push('Etika & greeting sangat ramah');
    else if ((currentScores['ethics'] || 0) < 75) deficits.push('Perlu lebih ramah & lengkapi greeting');

    if ((currentScores['promo_check'] || 0) >= 90) strengths.push('Promo aktif tersampaikan optimal');
    else if ((currentScores['promo_check'] || 0) < 75) deficits.push('Promo aktif belum optimal ditawarkan');

    const strEl = document.getElementById('liveStrengthsText');
    const defEl = document.getElementById('liveImprovementsText');

    if (strEl) {
        strEl.innerText = strengths.length > 0 ? '✓ Kelebihan: ' + strengths.join(', ') : '✓ Standar operasional terpenuhi';
    }
    if (defEl) {
        defEl.innerText = deficits.length > 0 ? '⚠️ Perhatian: ' + deficits.join(', ') : '✓ Tidak ada aspek kritis di bawah target';
    }
}

function autoSyncMetrics() {
    const empId = document.getElementById('assessEmployeeId').value;
    const period = document.getElementById('assessPeriod').value;
    if (!empId) return;

    fetch("{{ route('monitoring.sales.auto_sync') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ employee_id: empId, period: period })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.suggested_scores) {
            for (const [code, val] of Object.entries(data.suggested_scores)) {
                const input = document.getElementById('input_score_' + code);
                const slider = document.getElementById('slider_score_' + code);
                if (input) input.value = val;
                if (slider) slider.value = val;
            }
            calculateLiveScore();
            alert('Nilai kriteria berhasil disinkronkan otomatis berdasarkan database aktivitas!');
        }
    })
    .catch(err => {
        alert('Gagal mengambil metrik otomatis: ' + err.message);
    });
}

function openReportModal(assessmentId) {
    document.getElementById('reportModal').classList.remove('hidden');
    document.getElementById('reportModal').classList.add('flex');
    document.getElementById('reportModalContent').innerHTML = '<div class="py-8 text-center text-slate-400">Memuat data rapor harian...</div>';

    fetch("/monitoring/sales/assessments/" + assessmentId)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderReportCard(data);
        }
    })
    .catch(err => {
        document.getElementById('reportModalContent').innerHTML = '<div class="py-8 text-center text-red-500">Gagal memuat rapor: ' + err.message + '</div>';
    });
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    document.getElementById('reportModal').classList.remove('flex');
}

function copyWhatsAppReport() {
    if (!currentReportWaText) {
        alert('Format pesan WhatsApp belum siap.');
        return;
    }

    navigator.clipboard.writeText(currentReportWaText).then(() => {
        const btn = document.getElementById('btnCopyWa');
        const originalText = btn.innerText;
        btn.innerText = '✅ Tersalin!';
        btn.classList.add('bg-emerald-800');
        setTimeout(() => {
            btn.innerText = originalText;
            btn.classList.remove('bg-emerald-800');
        }, 2000);
    }).catch(err => {
        alert('Gagal menyalin: ' + err.message);
    });
}

function renderReportCard(data) {
    const emp = data.employee;
    const ass = data.assessment;
    const criteria = data.criteria;
    const diag = data.diagnostic || {};
    const scores = ass.scores || {};
    const history = data.recent_history || [];

    currentReportWaText = diag.whatsapp_text || '';

    let criteriaRows = '';
    criteria.forEach((c, idx) => {
        const val = scores[c.code] !== undefined ? scores[c.code] : (c.code === 'chat_reply' ? (scores['csat_quality'] || 0) : 0);
        const weighted = ((val * parseFloat(c.weight)) / 100).toFixed(2);
        criteriaRows += `
            <tr class="border-b border-slate-100">
                <td class="py-2.5 px-3 font-semibold text-slate-900">${idx + 1}. ${c.name}</td>
                <td class="py-2.5 px-3 text-center font-bold text-slate-600">${c.weight}%</td>
                <td class="py-2.5 px-3 text-center font-black ${val >= 85 ? 'text-emerald-700' : (val >= 75 ? 'text-blue-700' : 'text-amber-700')}">${val}</td>
                <td class="py-2.5 px-3 text-right font-black text-indigo-700">${weighted}</td>
            </tr>
        `;
    });

    let historyHtml = '';
    if (history.length > 0) {
        let historyRows = '';
        history.forEach(h => {
            historyRows += `
                <tr class="border-b border-slate-100 text-[11px] ${h.period === ass.period ? 'bg-indigo-50/50 font-bold' : ''}">
                    <td class="py-1.5 px-2">${h.period} ${h.period === ass.period ? '<span class="text-indigo-600">(Hari Ini)</span>' : ''}</td>
                    <td class="py-1.5 px-2 text-center font-black">${h.final_score}</td>
                    <td class="py-1.5 px-2 text-center"><span class="rounded px-1.5 py-0.2 text-[10px] font-bold ${h.grade === 'A' ? 'bg-emerald-100 text-emerald-800' : (h.grade === 'B' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800')}">Grade ${h.grade}</span></td>
                    <td class="py-1.5 px-2 text-slate-500 truncate max-w-[200px]">${h.notes || '-'}</td>
                </tr>
            `;
        });

        historyHtml = `
            <div class="rounded-xl border border-slate-200 overflow-hidden text-xs">
                <div class="bg-slate-100 px-3 py-2 font-bold text-slate-700 flex items-center justify-between">
                    <span>📈 Riwayat Penilaian Harian Terakhir (${history.length} Hari)</span>
                    <span class="text-[10px] text-slate-500 font-normal">Memantau konsistensi performa harian</span>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-1.5 px-2">Tanggal</th>
                            <th class="py-1.5 px-2 text-center">Skor</th>
                            <th class="py-1.5 px-2 text-center">Grade</th>
                            <th class="py-1.5 px-2">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>${historyRows}</tbody>
                </table>
            </div>
        `;
    }

    const strengthsList = (diag.strengths || []).map(s => `<li class="flex items-start gap-1.5"><span class="text-emerald-600 font-bold">✓</span><span>${s}</span></li>`).join('');
    const improvementsList = (diag.improvements || []).map(i => `<li class="flex items-start gap-1.5"><span class="text-amber-600 font-bold">•</span><span>${i}</span></li>`).join('');

    const html = `
        <div class="grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-4 text-xs border border-slate-200">
            <div>
                <div class="text-slate-400 text-[10px] font-bold uppercase">Nama Karyawan</div>
                <div class="font-black text-slate-900 text-sm mt-0.5">${emp.name}</div>
                <div class="text-slate-500 mt-0.5">NIK: ${emp.nik} • ${emp.role}</div>
            </div>
            <div class="text-right">
                <div class="text-slate-400 text-[10px] font-bold uppercase">Cabang & Tanggal</div>
                <div class="font-bold text-slate-900 text-sm mt-0.5">Cabang ${emp.cabang}</div>
                <div class="text-indigo-600 font-black mt-0.5">${ass.period}</div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100/75 text-[10px] uppercase font-bold text-slate-500">
                    <tr>
                        <th class="py-2.5 px-3">Aspek Penilaian Harian</th>
                        <th class="py-2.5 px-3 text-center">Bobot</th>
                        <th class="py-2.5 px-3 text-center">Nilai (0-100)</th>
                        <th class="py-2.5 px-3 text-right">Poin Terbobot</th>
                    </tr>
                </thead>
                <tbody>
                    ${criteriaRows}
                </tbody>
                <tfoot class="bg-indigo-50/70 border-t border-indigo-100 font-bold">
                    <tr>
                        <td colspan="3" class="py-3 px-3 text-indigo-950 font-black">SKOR AKHIR HARIAN:</td>
                        <td class="py-3 px-3 text-right text-base font-black text-indigo-700">${ass.final_score} / 100</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="flex items-center justify-between rounded-xl border border-slate-200 p-4">
            <div>
                <div class="text-[10px] uppercase font-bold text-slate-400">Predikat Evaluasi</div>
                <div class="text-sm font-black text-slate-900 mt-0.5">${data.grade_label}</div>
            </div>
            <span class="inline-flex items-center rounded-xl border px-3 py-1 font-black text-sm ${data.grade_badge}">
                Grade ${ass.grade}
            </span>
        </div>

        <!-- Smart Diagnostic Box: Strengths & Weaknesses -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-3.5 space-y-1.5">
                <div class="font-bold text-emerald-900 flex items-center gap-1.5">
                    <span>🌟 Kekuatan Utama Hari Ini:</span>
                </div>
                <ul class="space-y-1 text-slate-700 text-[11px]">${strengthsList}</ul>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-3.5 space-y-1.5">
                <div class="font-bold text-amber-900 flex items-center gap-1.5">
                    <span>⚠️ Poin Perlu Ditingkatkan:</span>
                </div>
                <ul class="space-y-1 text-slate-700 text-[11px]">${improvementsList}</ul>
            </div>
        </div>

        ${ass.notes ? `
        <div class="rounded-xl border border-slate-200 p-3.5 bg-slate-50/50 text-xs">
            <div class="text-[10px] uppercase font-bold text-slate-400">Catatan Khusus Manager:</div>
            <div class="mt-1 text-slate-700">${ass.notes}</div>
        </div>
        ` : ''}

        ${ass.recommendations ? `
        <div class="rounded-xl border border-slate-200 p-3.5 bg-slate-50/50 text-xs">
            <div class="text-[10px] uppercase font-bold text-slate-400">Rekomendasi / Coaching Plan Besok:</div>
            <div class="mt-1 text-slate-700">${ass.recommendations}</div>
        </div>
        ` : ''}

        ${historyHtml}

        <div class="flex items-center justify-between pt-4 text-[11px] text-slate-400 border-t border-slate-100">
            <div>Evaluator: <strong class="text-slate-700">${ass.evaluator_name || 'Manager Sales'}</strong></div>
            <div>Dicetak Resmi: ${new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
        </div>
    `;

    document.getElementById('reportModalContent').innerHTML = html;
}
</script>
@endsection
