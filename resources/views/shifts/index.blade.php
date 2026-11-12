@extends('layouts.app')

@section('title', 'Jadwal Shift Admin Sosmed - Dashboard Manager')

@section('content')
<div class="space-y-6">
    <!-- Header & Action Bar -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <span>Dashboard Manager</span>
                <span>/</span>
                <span class="text-brand-600 font-bold">Jadwal Shift Sosmed</span>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl flex items-center gap-2.5">
                <span>Jadwal Shift Admin Sosmed</span>
                <span class="rounded-full bg-cyan-100 px-3 py-0.5 text-xs font-bold text-cyan-800">4 Shift Aktif</span>
            </h1>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Kelola rotasi jadwal kerja admin sosmed, pantau siapa yang bertugas hari ini, dan atur shift mingguan secara cepat.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if (!\App\Support\DataMasker::isGuest())
            <button type="button" 
                    onclick="openBatchShiftModal()" 
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                <svg class="size-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                <span>Atur Shift Massal</span>
            </button>

            <button type="button" 
                    onclick="openAddShiftModal()" 
                    class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-brand-600/20 hover:bg-brand-700 transition">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>+ Tambah Jadwal Shift</span>
            </button>
            @endif

            <button type="button" 
                    onclick="window.print()" 
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white p-2 text-xs font-bold text-slate-600 shadow-2xs hover:bg-slate-50 transition" 
                    title="Cetak Jadwal Shift (Print)">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.13-1.28-3.09m0 0A9.954 9.954 0 0 1 12 6.75c3.284 0 6.22 1.583 8.04 4.02m-8.04-4.02V3m0 3.75v10.5m0 0-3-3m3 3 3-3"/></svg>
            </button>
        </div>
    </div>

    <!-- Alert Sukses Flash -->
    @if (session('success'))
    <div class="flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-900 shadow-xs">
        <div class="flex items-center gap-2">
            <svg class="size-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900">&times;</button>
    </div>
    @endif

    <!-- 4 Legend Cards Shift Hari Ini (Today Shift Board) -->
    <div class="rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jadwal Shift Hari Ini</span>
                <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">{{ $today->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                Total Bertugas: <strong class="text-slate-900">{{ $todayStats['total_active'] }} Admin</strong>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <!-- 1. Shift Pagi -->
            <div class="rounded-2xl border border-sky-200 bg-sky-50/50 p-4 transition hover:shadow-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="rounded-lg bg-sky-500/10 p-2 text-sky-600">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41m14.14-14.14-1.41 1.41"/></svg>
                        </span>
                        <div>
                            <div class="text-xs font-bold text-sky-950">Shift Pagi</div>
                            <div class="text-[11px] font-semibold text-sky-700">09:00 s/d 16:30</div>
                        </div>
                    </div>
                    <span class="text-xl font-black text-sky-900">{{ $todayStats['pagi']->count() }}</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-1.5 min-h-[28px]">
                    @forelse ($todayStats['pagi'] as $s)
                        <span class="inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 text-[11px] font-bold text-sky-800 border border-sky-200 shadow-2xs" title="{{ $s->notes ?: 'Shift Pagi' }}">
                            <span>{{ $s->admin_name }}</span>
                        </span>
                    @empty
                        <span class="text-[11px] text-slate-400 italic">Belum ada admin ditugaskan</span>
                    @endforelse
                </div>
            </div>

            <!-- 2. Shift Siang -->
            <div class="rounded-2xl border border-indigo-200 bg-indigo-50/50 p-4 transition hover:shadow-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="rounded-lg bg-indigo-500/10 p-2 text-indigo-600">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                        </span>
                        <div>
                            <div class="text-xs font-bold text-indigo-950">Shift Siang</div>
                            <div class="text-[11px] font-semibold text-indigo-700">14:30 s/d 22:00</div>
                        </div>
                    </div>
                    <span class="text-xl font-black text-indigo-900">{{ $todayStats['siang']->count() }}</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-1.5 min-h-[28px]">
                    @forelse ($todayStats['siang'] as $s)
                        <span class="inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 text-[11px] font-bold text-indigo-800 border border-indigo-200 shadow-2xs" title="{{ $s->notes ?: 'Shift Siang' }}">
                            <span>{{ $s->admin_name }}</span>
                        </span>
                    @empty
                        <span class="text-[11px] text-slate-400 italic">Belum ada admin ditugaskan</span>
                    @endforelse
                </div>
            </div>

            <!-- 3. Shift Lembur -->
            <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 transition hover:shadow-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="rounded-lg bg-amber-500/10 p-2 text-amber-600">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                        </span>
                        <div>
                            <div class="text-xs font-bold text-amber-950">Shift Lembur</div>
                            <div class="text-[11px] font-semibold text-amber-700">09:00 s/d 22:00 (Full)</div>
                        </div>
                    </div>
                    <span class="text-xl font-black text-amber-900">{{ $todayStats['lembur']->count() }}</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-1.5 min-h-[28px]">
                    @forelse ($todayStats['lembur'] as $s)
                        <span class="inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 text-[11px] font-bold text-amber-900 border border-amber-300 shadow-2xs" title="{{ $s->notes ?: 'Shift Lembur' }}">
                            <span>{{ $s->admin_name }}</span>
                        </span>
                    @empty
                        <span class="text-[11px] text-slate-400 italic">Tidak ada lembur hari ini</span>
                    @endforelse
                </div>
            </div>

            <!-- 4. Shift Off -->
            <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 transition hover:shadow-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="rounded-lg bg-slate-200 p-2 text-slate-600">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/></svg>
                        </span>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Off / Libur</div>
                            <div class="text-[11px] font-semibold text-slate-500">Lepas Piket</div>
                        </div>
                    </div>
                    <span class="text-xl font-black text-slate-700">{{ $todayStats['off']->count() }}</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-1.5 min-h-[28px]">
                    @forelse ($todayStats['off'] as $s)
                        <span class="inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 text-[11px] font-medium text-slate-600 border border-slate-200" title="{{ $s->notes ?: 'Libur / Off' }}">
                            <span>{{ $s->admin_name }}</span>
                        </span>
                    @empty
                        <span class="text-[11px] text-slate-400 italic">Tidak ada yang libur</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Controls Bar: View Toggle, Navigation Minggu, Search & Filter -->
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs md:flex-row md:items-center md:justify-between">
        <!-- View Toggle & Navigation -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Toggle Mode View -->
            <div class="inline-flex rounded-xl bg-slate-100 p-1">
                <a href="{{ route('shifts.index', array_merge(request()->query(), ['view' => 'roster'])) }}" 
                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $viewMode === 'roster' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                    <span>Roster Mingguan</span>
                </a>
                <a href="{{ route('shifts.index', array_merge(request()->query(), ['view' => 'list'])) }}" 
                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $viewMode === 'list' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                    <span>Daftar Tabel</span>
                </a>
            </div>

            <!-- Minggu Navigator (Hanya untuk Roster Mode) -->
            @if ($viewMode === 'roster')
            <div class="flex items-center gap-1 bg-slate-50 rounded-xl p-1 border border-slate-200">
                <a href="{{ route('shifts.index', ['view' => 'roster', 'week' => $startOfWeek->copy()->subWeek()->format('Y-m-d'), 'search' => $searchAdmin]) }}" 
                   class="grid size-7 place-items-center rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 transition" 
                   title="Minggu Sebelumnya">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </a>

                <span class="px-2 text-xs font-bold text-slate-800">
                    {{ $startOfWeek->format('d M') }} - {{ $endOfWeek->format('d M Y') }}
                </span>

                <a href="{{ route('shifts.index', ['view' => 'roster', 'week' => $startOfWeek->copy()->addWeek()->format('Y-m-d'), 'search' => $searchAdmin]) }}" 
                   class="grid size-7 place-items-center rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 transition" 
                   title="Minggu Berikutnya">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </a>

                @if (!$startOfWeek->isSameDay($today->copy()->startOfWeek(\Carbon\Carbon::MONDAY)))
                <a href="{{ route('shifts.index', ['view' => 'roster', 'search' => $searchAdmin]) }}" class="ml-1 rounded-md bg-brand-50 px-2 py-1 text-[10px] font-bold text-brand-700 hover:bg-brand-100 transition">
                    Hari Ini
                </a>
                @endif
            </div>
            @endif
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('shifts.index') }}" class="flex flex-1 flex-wrap items-center justify-end gap-2">
            <input type="hidden" name="view" value="{{ $viewMode }}">
            @if ($viewMode === 'roster')
                <input type="hidden" name="week" value="{{ $startOfWeek->format('Y-m-d') }}">
            @endif

            <!-- Search Admin Input -->
            <div class="relative min-w-[180px]">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="text" name="search" value="{{ $searchAdmin }}" placeholder="Cari nama karyawan..." class="w-full rounded-xl border border-slate-200 bg-white pl-8 pr-3 py-1.5 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>

            @if ($viewMode === 'list')
                <!-- Filter Shift -->
                <div class="w-32">
                    <select name="shift_type" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:border-brand-500 focus:outline-none">
                        <option value="">Semua Shift</option>
                        <option value="pagi" {{ $filterShift === 'pagi' ? 'selected' : '' }}>Pagi</option>
                        <option value="siang" {{ $filterShift === 'siang' ? 'selected' : '' }}>Siang</option>
                        <option value="lembur" {{ $filterShift === 'lembur' ? 'selected' : '' }}>Lembur</option>
                        <option value="off" {{ $filterShift === 'off' ? 'selected' : '' }}>Off</option>
                    </select>
                </div>

                <!-- Filter Bulan -->
                <div class="w-36">
                    <input type="month" name="month" value="{{ $filterMonth }}" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:border-brand-500 focus:outline-none">
                </div>
            @endif

            <button type="submit" class="rounded-xl bg-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition">
                Filter
            </button>

            @if ($searchAdmin !== '' || ($viewMode === 'list' && ($filterShift !== '' || $filterMonth !== $today->format('Y-m'))))
            <a href="{{ route('shifts.index', ['view' => $viewMode]) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                Reset
            </a>
            @endif
        </form>
    </div>

    @if ($viewMode === 'roster')
    <!-- VIEW 1: MATRIX ROSTER MINGGUAN -->
    <div class="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/90 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="px-5 py-3.5 min-w-[200px] sticky left-0 bg-slate-50/95 z-10 border-r border-slate-200">
                            <div class="flex items-center gap-1.5">
                                <span>Karyawan (Bobot KPI)</span>
                            </div>
                            <div class="text-[10px] text-slate-400 font-semibold mt-0.5 normal-case">{{ count($activeRosterEmployees) }} Karyawan Aktif</div>
                        </th>
                        @foreach ($weekDays as $day)
                            <th class="px-3 py-3 text-center min-w-[130px] {{ $day['is_today'] ? 'bg-brand-50/60 text-brand-900 font-extrabold border-x border-brand-200' : 'border-r border-slate-100' }}">
                                <div class="flex items-center justify-center gap-1">
                                    <span>{{ $day['day_name'] }}</span>
                                    @if ($day['is_today'])
                                        <span class="rounded bg-brand-600 text-white text-[9px] px-1 py-0.2">HARI INI</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ $day['short_date'] }}</div>
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
                            <td class="px-5 py-3.5 sticky left-0 bg-white group-hover:bg-slate-50/95 z-10 border-r border-slate-200">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-8 shrink-0 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700">
                                        {{ strtoupper(substr($adminName, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-slate-900 truncate">{{ $adminName }}</span>
                                            @if ($empLevel === 'Internship')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-2 py-0.5 text-[9px] font-bold text-purple-700 border border-purple-200/90 shadow-2xs">
                                                    <span class="size-1 rounded-full bg-purple-500"></span>
                                                    Internship
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[9px] font-bold text-blue-700 border border-blue-200/90 shadow-2xs">
                                                    <span class="size-1 rounded-full bg-blue-500"></span>
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
                                <td class="px-2 py-2.5 text-center align-middle {{ $day['is_today'] ? 'bg-brand-50/20 border-x border-brand-100' : 'border-r border-slate-100' }}">
                                    @if ($shift)
                                        @php $meta = $shift->shift_meta; @endphp
                                        <div class="relative group/cell">
                                            @if (\App\Support\DataMasker::isGuest())
                                                <div class="w-full text-left rounded-xl p-2 border {{ $meta['card_accent'] }}">
                                                    <div class="flex items-center justify-between">
                                                        <span class="inline-flex items-center gap-1 font-extrabold text-[11px]">
                                                            <span class="size-1.5 rounded-full {{ $meta['badge_dot'] }}"></span>
                                                            <span>{{ $meta['name'] }}</span>
                                                        </span>
                                                    </div>
                                                    <div class="text-[10px] opacity-80 mt-0.5 truncate font-medium">
                                                        {{ $meta['time_label'] }}
                                                    </div>
                                                    @if ($shift->notes)
                                                        <div class="text-[9px] text-slate-500 italic truncate mt-1" title="{{ $shift->notes }}">
                                                            "{{ $shift->notes }}"
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <button type="button" 
                                                        onclick="openEditShiftModal({{ $shift->id }}, '{{ addslashes($shift->admin_name) }}', '{{ $shift->shift_date->format('Y-m-d') }}', '{{ $shift->shift_type }}', '{{ addslashes($shift->notes ?? '') }}')"
                                                        class="w-full text-left rounded-xl p-2 border {{ $meta['card_accent'] }} transition hover:scale-102 hover:shadow-xs cursor-pointer">
                                                    <div class="flex items-center justify-between">
                                                        <span class="inline-flex items-center gap-1 font-extrabold text-[11px]">
                                                            <span class="size-1.5 rounded-full {{ $meta['badge_dot'] }}"></span>
                                                            <span>{{ $meta['name'] }}</span>
                                                        </span>
                                                        <span class="text-[10px] opacity-60 group-hover/cell:opacity-100">&times;</span>
                                                    </div>
                                                    <div class="text-[10px] opacity-80 mt-0.5 truncate font-medium">
                                                        {{ $meta['time_label'] }}
                                                    </div>
                                                    @if ($shift->notes)
                                                        <div class="text-[9px] text-slate-500 italic truncate mt-1" title="{{ $shift->notes }}">
                                                            "{{ $shift->notes }}"
                                                        </div>
                                                    @endif
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        @if (\App\Support\DataMasker::isGuest())
                                            <div class="w-full rounded-xl border border-dashed border-slate-200 py-3 text-[11px] font-semibold text-slate-300">
                                                -
                                            </div>
                                        @else
                                            <!-- Tombol Tambah Cepat jika belum ada jadwal -->
                                            <button type="button" 
                                                    onclick="openAddShiftModal('{{ addslashes($adminName) }}', '{{ $day['date'] }}')" 
                                                    class="w-full rounded-xl border border-dashed border-slate-200 py-3 text-[11px] font-semibold text-slate-400 hover:border-brand-400 hover:bg-brand-50/50 hover:text-brand-700 transition cursor-pointer"
                                                    title="Klik untuk tentukan shift {{ $adminName }} ({{ $day['day_name'] }})">
                                                + Atur
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <p class="text-sm font-semibold">Tidak ada karyawan yang ditemukan di bagian Karyawan & Bobot KPI</p>
                                <p class="text-xs mt-1">Pastikan data karyawan telah didaftarkan pada menu Pengaturan Karyawan & Bobot KPI.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @else
    <!-- VIEW 2: DAFTAR TABEL (LIST VIEW) -->
    <div class="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3.5">Tanggal & Hari</th>
                        <th class="px-4 py-3.5">Admin Sosmed</th>
                        <th class="px-4 py-3.5">Jenis Shift</th>
                        <th class="px-4 py-3.5">Jam Kerja</th>
                        <th class="px-4 py-3.5">Keterangan / Notes</th>
                        <th class="px-4 py-3.5">Diinput Oleh</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse ($listShifts as $s)
                        @php $meta = $s->shift_meta; @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $s->shift_date->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
                                @if ($s->shift_date->isSameDay($today))
                                    <span class="inline-block mt-0.5 rounded bg-brand-50 px-1.5 py-0.2 text-[9px] font-extrabold text-brand-700 border border-brand-200">HARI INI</span>
                                @endif
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-8 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700">
                                        {{ $s->initials }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-900">{{ $s->admin_name }}</span>
                                            @php
                                                $sEmpLevel = $s->employee?->level ?? 'Karyawan';
                                            @endphp
                                            @if ($sEmpLevel === 'Internship')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-1.5 py-0.5 text-[9px] font-bold text-purple-700 border border-purple-200/90 shadow-2xs">
                                                    Internship
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-1.5 py-0.5 text-[9px] font-bold text-blue-700 border border-blue-200/90 shadow-2xs">
                                                    Karyawan
                                                </span>
                                            @endif
                                        </div>
                                        @if ($s->employee?->nik)
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $s->employee->nik }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 rounded-full {{ $meta['badge_bg'] }} {{ $meta['badge_text'] }} border {{ $meta['badge_border'] }} px-2.5 py-1 text-xs font-bold">
                                    <span class="size-1.5 rounded-full {{ $meta['badge_dot'] }}"></span>
                                    <span>{{ $meta['name'] }}</span>
                                </span>
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap font-semibold text-slate-800">
                                {{ $meta['time_label'] }}
                            </td>

                            <td class="px-4 py-4 max-w-xs truncate text-slate-500">
                                {{ $s->notes ?: '-' }}
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap text-slate-400 text-[11px]">
                                {{ $s->creator?->name ?: 'Manager' }}
                            </td>

                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                @if (\App\Support\DataMasker::isGuest())
                                    <span class="text-slate-300 text-xs">-</span>
                                @else
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" 
                                            onclick="openEditShiftModal({{ $s->id }}, '{{ addslashes($s->admin_name) }}', '{{ $s->shift_date->format('Y-m-d') }}', '{{ $s->shift_type }}', '{{ addslashes($s->notes ?? '') }}')" 
                                            class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition" 
                                            title="Edit Shift">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                    </button>

                                    <form method="POST" action="{{ route('shifts.destroy', $s) }}" onsubmit="return confirm('Hapus jadwal shift {{ $s->admin_name }} tanggal {{ $s->shift_date->format('d/m/Y') }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition" title="Hapus Jadwal">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                Tidak ada data jadwal shift yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($listShifts->hasPages())
        <div class="border-t border-slate-100 p-4">
            {{ $listShifts->links() }}
        </div>
        @endif
    </div>
    @endif
</div>

<!-- ============================================================== -->
<!-- MODAL 1: TAMBAH / EDIT JADWAL SHIFT TUNGGAL                     -->
<!-- ============================================================== -->
<div id="modalShiftForm" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 hidden transition-opacity">
    <div class="relative flex flex-col w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden border border-slate-200">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
            <div>
                <h3 id="modalShiftTitle" class="text-base font-black text-slate-900">Atur Jadwal Shift Admin</h3>
                <p class="text-xs text-slate-500">Tentukan shift kerja (Pagi, Siang, Lembur, atau Off)</p>
            </div>
            <button type="button" onclick="closeShiftModal()" class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition">&times;</button>
        </div>

        <form id="shiftFormElement" method="POST" action="{{ route('shifts.store') }}" class="p-6 space-y-4">
            @csrf
            <div id="shiftMethodContainer"></div>

            <!-- Pilih Karyawan dari Bagian Karyawan & Bobot KPI -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Karyawan (Bobot KPI) <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select id="modalAdminNameInput" 
                            name="admin_name" 
                            required 
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/10">
                        <option value="" disabled selected>-- Pilih Karyawan --</option>
                        <optgroup label="💼 KARYAWAN TETAP">
                            @foreach ($employees->filter(fn($e) => ($e->level ?? 'Karyawan') !== 'Internship') as $emp)
                                <option value="{{ $emp->name }}">{{ $emp->name }} ({{ $emp->nik }} - {{ $emp->cabang ?? 'Head Office' }})</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="🎓 INTERNSHIP">
                            @foreach ($employees->filter(fn($e) => ($e->level ?? 'Karyawan') === 'Internship') as $emp)
                                <option value="{{ $emp->name }}">{{ $emp->name }} ({{ $emp->nik }} - {{ $emp->cabang ?? 'Head Office' }})</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>

            <!-- Tanggal Shift -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Tanggal Shift <span class="text-rose-500">*</span></label>
                <input type="date" 
                       id="modalShiftDateInput" 
                       name="shift_date" 
                       required 
                       value="{{ $today->format('Y-m-d') }}" 
                       class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-brand-500 focus:outline-none">
            </div>

            <!-- 4 Opsi Shift (Radio Cards) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Pilih Shift Kerja <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-2.5">
                    <!-- Pagi -->
                    <label class="relative flex flex-col p-3 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-sky-300 transition shift-radio-card" data-shift="pagi">
                        <input type="radio" name="shift_type" value="pagi" class="sr-only" required checked onchange="updateShiftRadioCards()">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-sky-900 flex items-center gap-1.5">
                                ☀️ Pagi
                            </span>
                            <span class="size-4 rounded-full border-2 border-slate-300 flex items-center justify-center shift-radio-dot"></span>
                        </div>
                        <div class="text-[11px] font-bold text-sky-700 mt-1">09:00 s/d 16:30</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Piket pagi sosmed</div>
                    </label>

                    <!-- Siang -->
                    <label class="relative flex flex-col p-3 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-indigo-300 transition shift-radio-card" data-shift="siang">
                        <input type="radio" name="shift_type" value="siang" class="sr-only" onchange="updateShiftRadioCards()">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-indigo-900 flex items-center gap-1.5">
                                🌤️ Siang
                            </span>
                            <span class="size-4 rounded-full border-2 border-slate-300 flex items-center justify-center shift-radio-dot"></span>
                        </div>
                        <div class="text-[11px] font-bold text-indigo-700 mt-1">14:30 s/d 22:00</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Piket sore s/d malam</div>
                    </label>

                    <!-- Lembur -->
                    <label class="relative flex flex-col p-3 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-amber-300 transition shift-radio-card" data-shift="lembur">
                        <input type="radio" name="shift_type" value="lembur" class="sr-only" onchange="updateShiftRadioCards()">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-amber-950 flex items-center gap-1.5">
                                ⚡ Lembur
                            </span>
                            <span class="size-4 rounded-full border-2 border-slate-300 flex items-center justify-center shift-radio-dot"></span>
                        </div>
                        <div class="text-[11px] font-bold text-amber-700 mt-1">09:00 s/d 22:00</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Piket sehari penuh</div>
                    </label>

                    <!-- Off -->
                    <label class="relative flex flex-col p-3 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-slate-400 transition shift-radio-card" data-shift="off">
                        <input type="radio" name="shift_type" value="off" class="sr-only" onchange="updateShiftRadioCards()">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                                🏖️ Off / Libur
                            </span>
                            <span class="size-4 rounded-full border-2 border-slate-300 flex items-center justify-center shift-radio-dot"></span>
                        </div>
                        <div class="text-[11px] font-bold text-slate-500 mt-1">Lepas Piket</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Libur kerja</div>
                    </label>
                </div>
            </div>

            <!-- Notes / Catatan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Catatan (Opsional)</label>
                <input type="text" 
                       id="modalShiftNotesInput" 
                       name="notes" 
                       placeholder="Misal: Tukar shift dengan Akmal, piket live chat, dll..." 
                       class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeShiftModal()" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="rounded-xl bg-brand-600 px-5 py-2 text-xs font-bold text-white shadow-md shadow-brand-600/20 hover:bg-brand-700 transition">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: ATUR SHIFT MASSAL / MULTI-HARI                         -->
<!-- ============================================================== -->
<div id="modalBatchShift" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 hidden transition-opacity">
    <div class="relative flex flex-col w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden border border-slate-200">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
            <div>
                <h3 class="text-base font-black text-slate-900">Atur Shift Massal (Multi-Hari)</h3>
                <p class="text-xs text-slate-500">Terapkan satu jenis shift untuk beberapa hari sekaligus</p>
            </div>
            <button type="button" onclick="closeBatchShiftModal()" class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition">&times;</button>
        </div>

        <form method="POST" action="{{ route('shifts.batch') }}" class="p-6 space-y-4">
            @csrf

            <!-- Pilih Karyawan dari Bagian Karyawan & Bobot KPI -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Karyawan (Bobot KPI) <span class="text-rose-500">*</span></label>
                <select name="admin_name" 
                        required 
                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-brand-500 focus:outline-none">
                    <option value="" disabled selected>-- Pilih Karyawan --</option>
                    <optgroup label="💼 KARYAWAN TETAP">
                        @foreach ($employees->filter(fn($e) => ($e->level ?? 'Karyawan') !== 'Internship') as $emp)
                            <option value="{{ $emp->name }}">{{ $emp->name }} ({{ $emp->nik }} - {{ $emp->cabang ?? 'Head Office' }})</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="🎓 INTERNSHIP">
                        @foreach ($employees->filter(fn($e) => ($e->level ?? 'Karyawan') === 'Internship') as $emp)
                            <option value="{{ $emp->name }}">{{ $emp->name }} ({{ $emp->nik }} - {{ $emp->cabang ?? 'Head Office' }})</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>

            <!-- Pilih Jenis Shift -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Jenis Shift <span class="text-rose-500">*</span></label>
                <select name="shift_type" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:border-brand-500 focus:outline-none">
                    <option value="pagi">☀️ Shift Pagi (09:00 s/d 16:30)</option>
                    <option value="siang">🌤️ Shift Siang (14:30 s/d 22:00)</option>
                    <option value="lembur">⚡ Shift Lembur (09:00 s/d 22:00)</option>
                    <option value="off">🏖️ Off / Libur</option>
                </select>
            </div>

            <!-- Checklist Tanggal Minggu Berjalan -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Pilih Tanggal Berlaku <span class="text-rose-500">*</span></label>
                    <button type="button" onclick="toggleAllBatchDates()" class="text-[11px] font-bold text-brand-600 hover:underline">Pilih Semua</button>
                </div>
                <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-1 border border-slate-100 rounded-xl">
                    @foreach ($weekDays as $day)
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-slate-100 cursor-pointer text-xs font-semibold text-slate-800">
                            <input type="checkbox" name="dates[]" value="{{ $day['date'] }}" class="batch-date-cb rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <span>{{ $day['day_name'] }} ({{ $day['short_date'] }})</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Catatan (Opsional)</label>
                <input type="text" name="notes" placeholder="Catatan shift massal..." class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 focus:border-brand-500 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeBatchShiftModal()" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="rounded-xl bg-brand-600 px-5 py-2 text-xs font-bold text-white shadow-md shadow-brand-600/20 hover:bg-brand-700 transition">
                    Terapkan Shift Massal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateShiftRadioCards() {
    const selected = document.querySelector('input[name="shift_type"]:checked')?.value;
    document.querySelectorAll('.shift-radio-card').forEach(card => {
        const val = card.getAttribute('data-shift');
        const dot = card.querySelector('.shift-radio-dot');
        if (val === selected) {
            if (val === 'pagi') {
                card.className = 'relative flex flex-col p-3 rounded-2xl border-2 border-sky-500 bg-sky-50/80 cursor-pointer transition shift-radio-card ring-2 ring-sky-500/10';
            } else if (val === 'siang') {
                card.className = 'relative flex flex-col p-3 rounded-2xl border-2 border-indigo-500 bg-indigo-50/80 cursor-pointer transition shift-radio-card ring-2 ring-indigo-500/10';
            } else if (val === 'lembur') {
                card.className = 'relative flex flex-col p-3 rounded-2xl border-2 border-amber-500 bg-amber-50/80 cursor-pointer transition shift-radio-card ring-2 ring-amber-500/10';
            } else {
                card.className = 'relative flex flex-col p-3 rounded-2xl border-2 border-slate-600 bg-slate-100 cursor-pointer transition shift-radio-card ring-2 ring-slate-600/10';
            }
            if (dot) dot.innerHTML = '<span class="size-2 rounded-full bg-brand-600"></span>';
        } else {
            card.className = 'relative flex flex-col p-3 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-slate-300 transition shift-radio-card';
            if (dot) dot.innerHTML = '';
        }
    });
}

function openAddShiftModal(adminName = '', date = '') {
    const modal = document.getElementById('modalShiftForm');
    document.getElementById('modalShiftTitle').textContent = 'Tambah Jadwal Shift Admin';
    const form = document.getElementById('shiftFormElement');
    form.action = "{{ route('shifts.store') }}";
    document.getElementById('shiftMethodContainer').innerHTML = '';

    if (adminName) {
        document.getElementById('modalAdminNameInput').value = adminName;
    }
    if (date) {
        document.getElementById('modalShiftDateInput').value = date;
    }
    document.getElementById('modalShiftNotesInput').value = '';

    // Default shift Pagi
    const pagiRadio = document.querySelector('input[name="shift_type"][value="pagi"]');
    if (pagiRadio) {
        pagiRadio.checked = true;
    }
    updateShiftRadioCards();

    modal.classList.remove('hidden');
}

function openEditShiftModal(id, adminName, date, shiftType, notes) {
    const modal = document.getElementById('modalShiftForm');
    document.getElementById('modalShiftTitle').textContent = 'Edit Jadwal Shift: ' + adminName;
    const form = document.getElementById('shiftFormElement');
    form.action = "/shifts/" + id;
    document.getElementById('shiftMethodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    document.getElementById('modalAdminNameInput').value = adminName;
    document.getElementById('modalShiftDateInput').value = date;
    document.getElementById('modalShiftNotesInput').value = notes || '';

    const targetRadio = document.querySelector(`input[name="shift_type"][value="${shiftType}"]`);
    if (targetRadio) {
        targetRadio.checked = true;
    }
    updateShiftRadioCards();

    modal.classList.remove('hidden');
}

function closeShiftModal() {
    document.getElementById('modalShiftForm').classList.add('hidden');
}

function openBatchShiftModal() {
    document.getElementById('modalBatchShift').classList.remove('hidden');
}

function closeBatchShiftModal() {
    document.getElementById('modalBatchShift').classList.add('hidden');
}

let allBatchChecked = false;
function toggleAllBatchDates() {
    allBatchChecked = !allBatchChecked;
    document.querySelectorAll('.batch-date-cb').forEach(cb => {
        cb.checked = allBatchChecked;
    });
}

// Inisialisasi tampilan awal
document.addEventListener('DOMContentLoaded', function() {
    updateShiftRadioCards();
});
</script>
@endsection
