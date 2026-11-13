@extends('layouts.app')

@section('title', 'Kelola Akun & Hak Akses Fitur')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-purple-500/10 px-2.5 py-1 text-xs font-bold text-purple-700">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                    </svg>
                    Master Setting
                </span>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-semibold text-slate-500">Hak Akses & Akun</span>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">Kelola Akun & Hak Akses Fitur</h1>
            <p class="text-xs text-slate-500 sm:text-sm">Konfigurasikan checklist perizinan fitur untuk setiap akun selain Superadmin secara granular.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="openAddUserModal()" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-purple-600/20 hover:bg-purple-700 transition cursor-pointer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Akun Baru</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 flex-wrap gap-1">
        <a href="{{ route('settings.karyawan.index', ['tab' => 'employees']) }}" 
           class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-bold border-transparent text-slate-500 hover:text-slate-800 transition">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
            <span>Data Karyawan Sales</span>
        </a>

        <a href="{{ route('settings.karyawan.index', ['tab' => 'weights']) }}" 
           class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-bold border-transparent text-slate-500 hover:text-slate-800 transition">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z"/></svg>
            <span>Bobot & Kriteria Penilaian</span>
        </a>

        <a href="{{ route('settings.karyawan.index', ['tab' => 'monthly_targets']) }}" 
           class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-bold border-transparent text-slate-500 hover:text-slate-800 transition">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span>Target Omzet Bulanan</span>
        </a>

        <a href="{{ route('settings.users.index') }}" 
           class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-bold border-brand-600 text-brand-600 transition">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
            </svg>
            <span>Hak Akses & Akun</span>
            <span class="rounded-full bg-purple-100 text-purple-700 px-2 py-0.5 text-xs font-bold">{{ $totalUsers }} Akun</span>
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">Total Akun Terdaftar</span>
                <span class="rounded-lg bg-slate-100 p-2 text-slate-600">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ $totalUsers }}</div>
            <p class="mt-0.5 text-[11px] text-slate-400">Pengguna sistem DCMS</p>
        </div>

        <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-800">Sedang Online</span>
                <span class="size-2.5 rounded-full bg-emerald-500"></span>
            </div>
            <div id="onlineUsersCountDisplay" class="mt-2 text-2xl font-black text-emerald-900">{{ $onlineUsersCount }}</div>
            <p class="mt-0.5 text-[11px] text-emerald-700/80">Aktif 5 menit terakhir</p>
        </div>

        <div class="rounded-2xl border border-amber-200/80 bg-amber-50/50 p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-800">Superadmin</span>
                <span class="rounded-lg bg-amber-500/10 p-2 text-amber-700">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-amber-900">{{ $superadminCount }}</div>
            <p class="mt-0.5 text-[11px] text-amber-700/80">Akses penuh tanpa batas</p>
        </div>

        <div class="rounded-2xl border border-indigo-200/80 bg-indigo-50/50 p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-indigo-800">Admin</span>
                <span class="rounded-lg bg-indigo-500/10 p-2 text-indigo-700">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.999-3.199a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-indigo-900">{{ $adminCount }}</div>
            <p class="mt-0.5 text-[11px] text-indigo-700/80">Checklist izin disesuaikan</p>
        </div>

        <div class="rounded-2xl border border-sky-200/80 bg-sky-50/50 p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-sky-800">Staff / User</span>
                <span class="rounded-lg bg-sky-500/10 p-2 text-sky-700">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-sky-900">{{ $staffCount }}</div>
            <p class="mt-0.5 text-[11px] text-sky-700/80">Checklist izin disesuaikan</p>
        </div>
    </div>

    <!-- Alert / Petunjuk Superadmin -->
    <div class="flex items-start gap-3 rounded-2xl border border-purple-200 bg-purple-50/70 p-4 text-purple-900">
        <svg class="size-5 shrink-0 text-purple-600 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
        </svg>
        <div class="text-xs leading-relaxed">
            <strong class="font-bold text-purple-950">Panduan Checklist Hak Akses:</strong>
            Akun bertipe <strong>Superadmin</strong> memiliki akses penuh otomatis ke seluruh {{ count($availableFeatures) }} modul tanpa batasan.
            Untuk akun <strong>Admin</strong> dan <strong>Staff/User</strong>, Superadmin dapat mengklik tombol <span class="rounded bg-purple-200 px-1.5 py-0.5 font-bold text-purple-900">Atur Checklist Fitur</span> pada baris akun untuk mengaktifkan atau menonaktifkan fitur secara spesifik.
        </div>
    </div>

    <!-- Filter & Actions Bar -->
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs md:flex-row md:items-center md:justify-between">
        <form method="GET" action="{{ route('settings.users.index') }}" class="flex flex-1 flex-wrap items-center gap-3">
            <!-- Search Input -->
            <div class="relative min-w-[220px] flex-1">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email pengguna..." class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3.5 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/10">
            </div>

            <!-- Status Online Filter -->
            <div class="w-40">
                <select name="status" class="dcms-select w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 focus:border-brand-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="online" {{ ($statusFilter ?? '') === 'online' ? 'selected' : '' }}>● Online ({{ $onlineUsersCount }})</option>
                    <option value="offline" {{ ($statusFilter ?? '') === 'offline' ? 'selected' : '' }}>○ Offline</option>
                </select>
            </div>

            <!-- Role Filter -->
            <div class="w-44">
                <select name="role" class="dcms-select w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 focus:border-brand-500 focus:outline-none">
                    <option value="">Semua Peran (Role)</option>
                    <option value="superadmin" {{ $roleFilter === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                    <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ $roleFilter === 'user' ? 'selected' : '' }}>Staff / User</option>
                </select>
            </div>

            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white hover:bg-slate-800 transition">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <span>Filter</span>
            </button>

            @if ($search !== '' || $roleFilter !== '' || ($statusFilter ?? '') !== '')
            <a href="{{ route('settings.users.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-800">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Reset</span>
            </a>
            @endif
        </form>
    </div>

    <!-- Table of Users -->
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3.5">Akun Pengguna</th>
                        <th class="px-4 py-3.5">Peran (Role)</th>
                        <th class="px-4 py-3.5">Status Hak Akses Fitur</th>
                        <th class="px-4 py-3.5">Terdaftar Pada</th>
                        <th class="px-5 py-3.5 text-right">Aksi Checklist</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse ($users as $u)
                        @php
                            $effectivePermissions = $u->getEffectivePermissions();
                            $activeCount = count($effectivePermissions);
                            $totalFeaturesCount = count($availableFeatures);
                            $isCustom = ($u->permissions !== null);
                            $lastAct = $userLastActivities[$u->id] ?? null;
                            $isOnline = ($lastAct && $lastAct >= $onlineThreshold);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition" data-user-row="{{ $u->id }}">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative shrink-0">
                                        <div class="size-9 rounded-xl flex items-center justify-center font-bold text-xs {{ $u->isSuperAdmin() ? 'bg-amber-100 text-amber-800 border border-amber-300' : ($u->role === 'admin' ? 'bg-indigo-100 text-indigo-800 border border-indigo-300' : 'bg-sky-100 text-sky-800 border border-sky-300') }}">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        @if ($isOnline)
                                            <span data-user-dot class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full bg-emerald-500 border-2 border-white" title="Online"></span>
                                        @else
                                            <span data-user-dot class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full bg-slate-300 border-2 border-white" title="Offline"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 flex items-center flex-wrap gap-1.5">
                                            <span>{{ $u->name }}</span>
                                            @if ($u->id === auth()->id())
                                                <span class="rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-bold text-brand-700 border border-brand-200">Anda</span>
                                            @endif
                                            <span data-user-badge>
                                                @if ($isOnline)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200 shadow-xs" title="Sedang online">
                                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                                        <span>Online</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500 border border-slate-200" title="Offline">
                                                        <span class="size-1.5 rounded-full bg-slate-400"></span>
                                                        <span>Offline</span>
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 flex items-center flex-wrap gap-1.5">
                                            <span>{{ $u->email }}</span>
                                            @if ($u->phone)
                                                <span class="text-slate-300">•</span>
                                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $u->phone)) }}" target="_blank" class="inline-flex items-center gap-1 font-mono font-semibold text-emerald-600 hover:text-emerald-700 hover:underline" title="Hubungi via WhatsApp">
                                                    <svg class="size-3 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                                    <span>{{ $u->phone }}</span>
                                                </a>
                                            @endif
                                            <span data-user-lastseen>
                                                @if ($isOnline)
                                                    <span class="text-emerald-600 font-semibold">• Sedang aktif</span>
                                                @elseif ($lastAct)
                                                    <span class="text-slate-400">• Aktif {{ \Carbon\Carbon::createFromTimestamp($lastAct)->locale('id')->diffForHumans() }}</span>
                                                @else
                                                    <span class="text-slate-400">• Belum ada riwayat sesi</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                @if ($u->isSuperAdmin())
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-1 text-[11px] font-bold text-amber-700 border border-amber-500/20">
                                        <svg class="size-3 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                                        Superadmin
                                    </span>
                                @elseif ($u->role === 'admin')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-500/10 px-2.5 py-1 text-[11px] font-bold text-indigo-700 border border-indigo-500/20">
                                        Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-500/10 px-2.5 py-1 text-[11px] font-bold text-sky-700 border border-sky-500/20">
                                        Staff / User
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                @if ($u->isSuperAdmin())
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200">
                                            <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            Akses Penuh (Semua {{ $totalFeaturesCount }} Fitur)
                                        </span>
                                    </div>
                                    <div class="mt-1 text-[10px] text-slate-400">Tidak dapat dibatasi (Hak Superadmin)</div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-md {{ $activeCount > 0 ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }} px-2 py-0.5 text-xs font-bold">
                                            {{ $activeCount }} / {{ $totalFeaturesCount }} Fitur Aktif
                                        </span>
                                        @if ($isCustom)
                                            <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[9px] font-extrabold uppercase text-amber-800" title="Checklist telah dikustomisasi secara spesifik">Kustom</span>
                                        @else
                                            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-semibold text-slate-500" title="Menggunakan izin bawaan dari perannya">Default Role</span>
                                        @endif
                                    </div>
                                    <div class="mt-1.5 flex flex-wrap gap-1 max-w-md">
                                        @php $shownCount = 0; @endphp
                                        @foreach ($effectivePermissions as $permKey)
                                            @if (isset($availableFeatures[$permKey]) && $shownCount < 4)
                                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-medium text-slate-600 truncate max-w-[130px]" title="{{ $availableFeatures[$permKey]['name'] }}">
                                                    {{ $availableFeatures[$permKey]['name'] }}
                                                </span>
                                                @php $shownCount++; @endphp
                                            @endif
                                        @endforeach
                                        @if ($activeCount > 4)
                                            <span class="rounded bg-purple-100 px-1.5 py-0.5 text-[9px] font-bold text-purple-700">
                                                +{{ $activeCount - 4 }} lainnya
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-slate-500 whitespace-nowrap">
                                <div>{{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $u->created_at ? $u->created_at->format('H:i') . ' WITA' : '' }}</div>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($u->isSuperAdmin())
                                        @if ($u->id === auth()->id())
                                            <a href="{{ route('profile.edit') }}" 
                                               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 transition"
                                               title="Buka Pengaturan Akun Anda untuk mengatur profil & widget live chat">
                                                <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                                <span>Pengaturan Akun</span>
                                            </a>
                                        @else
                                            <span class="text-xs font-medium text-slate-400 italic">Superadmin Tetap</span>
                                        @endif
                                    @else
                                        <button type="button" 
                                                onclick="openEditPermissionsModal({{ json_encode([
                                                    'id' => $u->id,
                                                    'name' => $u->name,
                                                    'email' => $u->email,
                                                    'role' => $u->role ?? 'user',
                                                    'permissions' => $effectivePermissions,
                                                    'is_custom' => $isCustom,
                                                ]) }})" 
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-purple-700 transition cursor-pointer"
                                                title="Buka Checklist Fitur untuk Akun Ini">
                                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                            <span>Atur Checklist Fitur</span>
                                        </button>

                                        @if ($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('settings.users.destroy', $u) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $u->name }}? Tindakan ini tidak dapat dibatalkan.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition" title="Hapus Akun">
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            </button>
                                        </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="size-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                    <div class="mt-2 text-sm font-bold text-slate-700">Tidak ada pengguna yang cocok</div>
                                    <p class="text-xs text-slate-400">Silakan ubah filter atau kata kunci pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
        <div class="border-t border-slate-200 px-5 py-4">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: ATUR CHECKLIST HAK AKSES FITUR USER -->
<!-- ========================================== -->
<div id="modal-permissions" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="relative flex flex-col w-full max-w-4xl max-h-[92vh] rounded-3xl bg-white shadow-2xl overflow-hidden transform scale-95 transition-transform duration-200" id="modal-permissions-panel">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-900 px-6 py-4 text-white shrink-0">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-purple-500/20 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-purple-300">Checklist Hak Akses</span>
                    <span class="text-xs text-slate-400" id="modal-user-role-badge">Admin</span>
                </div>
                <h3 class="text-lg font-black tracking-tight text-white mt-0.5" id="modal-user-name">Nama Pengguna</h3>
                <p class="text-xs text-slate-400" id="modal-user-email">email@gadgetmart.id</p>
            </div>
            <button type="button" onclick="closePermissionsModal()" class="rounded-xl p-2 text-slate-400 hover:bg-white/10 hover:text-white transition cursor-pointer">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Modal Form -->
        <form id="form-update-permissions" method="POST" action="" class="flex-1 flex flex-col min-h-0">
            @csrf
            @method('PUT')
            <input type="hidden" name="action" id="perm-form-action" value="save">

            <!-- Subheader: Role selector & Toolbar actions -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-6 py-3 shrink-0">
                <div class="flex items-center gap-3">
                    <label class="text-xs font-bold text-slate-700">Peran Akun:</label>
                    <select name="role" id="modal-role-select" onchange="onRoleChangedInModal(this.value)" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-bold text-slate-800 focus:border-brand-500 focus:outline-none">
                        <option value="admin">Admin (Monitoring, Setup & Workspace Lengkap)</option>
                        <option value="user">Staff / User (Operasional & Panduan SOP)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="checkAllFeatures(true)" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                        ✓ Pilih Semua
                    </button>
                    <button type="button" onclick="checkAllFeatures(false)" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                        ✕ Hapus Semua
                    </button>
                    <button type="button" onclick="resetToRoleDefaults()" class="rounded-lg border border-purple-300 bg-purple-50 px-2.5 py-1 text-xs font-bold text-purple-700 hover:bg-purple-100 transition cursor-pointer">
                        ↺ Default Role
                    </button>
                </div>
            </div>

            <!-- Scrollable Feature Checklists by Category -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                @foreach ($groupedFeatures as $categoryName => $catFeatures)
                    <div class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                <span class="size-2 rounded-full bg-purple-600"></span>
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">{{ $categoryName }}</h4>
                                <span class="rounded-full bg-slate-200/80 px-2 py-0.2 text-[10px] font-bold text-slate-600">{{ count($catFeatures) }} Fitur</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="toggleCategoryCheckboxes('{{ Str::slug($categoryName) }}', true)" class="text-[11px] font-bold text-purple-600 hover:text-purple-800 cursor-pointer">Pilih Semua</button>
                                <span class="text-slate-300">|</span>
                                <button type="button" onclick="toggleCategoryCheckboxes('{{ Str::slug($categoryName) }}', false)" class="text-[11px] font-bold text-slate-500 hover:text-slate-700 cursor-pointer">Batal</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-4">
                            @foreach ($catFeatures as $fKey => $fData)
                                <label class="group relative flex items-start gap-3 rounded-xl border border-slate-200/80 p-3 hover:border-purple-300 hover:bg-purple-50/20 transition cursor-pointer select-none">
                                    <div class="flex h-5 items-center">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $fKey }}" 
                                               id="perm-{{ $fKey }}"
                                               data-category="{{ Str::slug($categoryName) }}"
                                               class="perm-checkbox size-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500/20 cursor-pointer">
                                    </div>
                                    <div class="flex-1 text-xs">
                                        <div class="font-bold text-slate-900 group-hover:text-purple-900 flex items-center justify-between">
                                            <span>{{ $fData['name'] }}</span>
                                            @if (!empty($fData['default_roles']))
                                                <span class="rounded bg-slate-100 px-1 py-0.2 text-[9px] font-semibold text-slate-500 uppercase">
                                                    {{ implode(', ', $fData['default_roles']) }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-0.5 text-[11px] text-slate-500 leading-snug">{{ $fData['description'] }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-4 shrink-0">
                <div class="text-xs font-bold text-slate-600">
                    <span id="active-feature-counter" class="text-purple-700 font-black">0</span> dari {{ count($availableFeatures) }} fitur aktif terpilih
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closePermissionsModal()" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" onclick="submitResetDefault()" class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-xs font-bold text-amber-800 hover:bg-amber-100 transition cursor-pointer">
                        Reset ke Bawaan Role
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-5 py-2 text-xs font-bold text-white shadow-sm shadow-purple-600/30 hover:bg-purple-700 transition cursor-pointer">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        <span>Simpan Hak Akses</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: TAMBAH AKUN BARU OLEH SUPERADMIN    -->
<!-- ========================================== -->
<div id="modal-add-user" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="relative flex flex-col w-full max-w-xl max-h-[90vh] rounded-3xl bg-white shadow-2xl overflow-hidden transform scale-95 transition-transform duration-200" id="modal-add-user-panel">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-900 px-6 py-4 text-white shrink-0">
            <div>
                <span class="rounded-full bg-purple-500/20 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-purple-300">Pengguna Baru</span>
                <h3 class="text-lg font-black tracking-tight text-white mt-0.5">Tambah Akun Baru</h3>
            </div>
            <button type="button" onclick="closeAddUserModal()" class="rounded-xl p-2 text-slate-400 hover:bg-white/10 hover:text-white transition cursor-pointer">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('settings.users.store') }}" class="p-6 space-y-4 overflow-y-auto">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Budi Pratama" class="mt-1 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/10">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Alamat Email <span class="text-rose-500">*</span></label>
                <input type="email" name="email" required placeholder="budi@gadgetmart.id" class="mt-1 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/10">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Nomor WhatsApp / HP</label>
                <input type="text" name="phone" placeholder="Contoh: 081234567890" class="mt-1 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/10">
                <p class="mt-1 text-[10px] text-slate-400">Digunakan untuk notifikasi sistem & keamanan OTP.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Peran Akun (Role) <span class="text-rose-500">*</span></label>
                <select name="role" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-purple-500 focus:outline-none">
                    <option value="user">Staff / User (Akses default: CRM & Workspace umum)</option>
                    <option value="admin">Admin (Akses default: Monitoring Sales & Gateway)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Kata Sandi Akun <span class="text-rose-500">*</span></label>
                <input type="password" name="password" minlength="6" required placeholder="Minimal 6 karakter" class="mt-1 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/10">
                <p class="mt-1 text-[11px] text-slate-400">Pengguna dapat mengganti kata sandi ini sewaktu-waktu di halaman Pengaturan Akun mereka.</p>
            </div>

            <div class="rounded-xl border border-purple-200 bg-purple-50/50 p-3 text-xs text-purple-900 leading-relaxed">
                💡 Setelah akun dibuat, checklist izin fitur akan otomatis disetel ke izin bawaan peran tersebut. Anda dapat menyesuaikannya kapan saja.
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeAddUserModal()" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-5 py-2 text-xs font-bold text-white shadow-sm shadow-purple-600/30 hover:bg-purple-700 transition cursor-pointer">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    <span>Simpan Akun Baru</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const roleDefaultPermissions = {
    'superadmin': @json(array_keys(\App\Models\User::availableFeatures())),
    'admin': @json(\App\Models\User::defaultPermissionsForRole('admin')),
    'user': @json(\App\Models\User::defaultPermissionsForRole('user')),
};

function updateActiveCounter() {
    const checked = document.querySelectorAll('.perm-checkbox:checked').length;
    const counter = document.getElementById('active-feature-counter');
    if (counter) counter.innerText = checked;
}

document.addEventListener('change', function(e) {
    if (e.target && e.target.classList.contains('perm-checkbox')) {
        updateActiveCounter();
    }
});

function openEditPermissionsModal(userData) {
    const modal = document.getElementById('modal-permissions');
    const panel = document.getElementById('modal-permissions-panel');
    const form = document.getElementById('form-update-permissions');
    
    document.getElementById('modal-user-name').innerText = userData.name;
    document.getElementById('modal-user-email').innerText = userData.email;
    document.getElementById('modal-user-role-badge').innerText = (userData.role || 'user').toUpperCase();
    document.getElementById('modal-role-select').value = userData.role || 'user';
    document.getElementById('perm-form-action').value = 'save';
    
    form.action = '/settings/users/' + userData.id + '/permissions';

    let permsArray = userData.permissions;
    if ((!permsArray || permsArray.length === 0) && userData.role === 'superadmin') {
        permsArray = roleDefaultPermissions['superadmin'] || [];
    }
    const currentPerms = new Set(permsArray || []);
    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        cb.checked = currentPerms.has(cb.value);
    });

    updateActiveCounter();

    modal.classList.remove('opacity-0', 'pointer-events-none');
    panel.classList.remove('scale-95');
    panel.classList.add('scale-100');
}

function closePermissionsModal() {
    const modal = document.getElementById('modal-permissions');
    const panel = document.getElementById('modal-permissions-panel');
    modal.classList.add('opacity-0', 'pointer-events-none');
    panel.classList.remove('scale-100');
    panel.classList.add('scale-95');
}

function checkAllFeatures(status) {
    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        cb.checked = status;
    });
    updateActiveCounter();
}

function toggleCategoryCheckboxes(catSlug, status) {
    document.querySelectorAll(`.perm-checkbox[data-category="${catSlug}"]`).forEach(cb => {
        cb.checked = status;
    });
    updateActiveCounter();
}

function resetToRoleDefaults() {
    const currentRole = document.getElementById('modal-role-select').value;
    const defaults = new Set(roleDefaultPermissions[currentRole] || []);
    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        cb.checked = defaults.has(cb.value);
    });
    updateActiveCounter();
}

function submitResetDefault() {
    if (confirm('Reset seluruh checklist perizinan akun ini ke pengaturan bawaan peran (default role)?')) {
        document.getElementById('perm-form-action').value = 'reset_default';
        document.getElementById('form-update-permissions').submit();
    }
}

function onRoleChangedInModal(newRole) {
    document.getElementById('modal-user-role-badge').innerText = newRole.toUpperCase();
}

function openAddUserModal() {
    const modal = document.getElementById('modal-add-user');
    const panel = document.getElementById('modal-add-user-panel');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    panel.classList.remove('scale-95');
    panel.classList.add('scale-100');
}

function closeAddUserModal() {
    const modal = document.getElementById('modal-add-user');
    const panel = document.getElementById('modal-add-user-panel');
    modal.classList.add('opacity-0', 'pointer-events-none');
    panel.classList.remove('scale-100');
    panel.classList.add('scale-95');
}

// Live polling status Online/Offline pengguna secara otomatis setiap 10 detik
setInterval(function() {
    fetch("{{ route('settings.users.index') }}", {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data || !data.userLastActivities) return;
        const countEl = document.getElementById('onlineUsersCountDisplay');
        if (countEl) countEl.textContent = data.onlineUsersCount;

        const threshold = data.onlineThreshold;
        const acts = data.userLastActivities;

        document.querySelectorAll('tr[data-user-row]').forEach(row => {
            const userId = parseInt(row.getAttribute('data-user-row'), 10);
            const isOnline = acts[userId] && acts[userId] >= threshold;

            const dot = row.querySelector('[data-user-dot]');
            const badge = row.querySelector('[data-user-badge]');
            const lastSeen = row.querySelector('[data-user-lastseen]');

            if (dot) {
                dot.className = isOnline 
                    ? 'absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full bg-emerald-500 border-2 border-white'
                    : 'absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full bg-slate-300 border-2 border-white';
                dot.title = isOnline ? 'Online' : 'Offline';
            }

            if (badge) {
                badge.innerHTML = isOnline
                    ? '<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200 shadow-xs" title="Sedang online"><span class="size-1.5 rounded-full bg-emerald-500"></span><span>Online</span></span>'
                    : '<span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500 border border-slate-200" title="Offline"><span class="size-1.5 rounded-full bg-slate-400"></span><span>Offline</span></span>';
            }

            if (lastSeen && isOnline) {
                lastSeen.innerHTML = '<span class="text-emerald-600 font-semibold">• Sedang aktif</span>';
            }
        });
    })
    .catch(() => {});
}, 10000);
</script>
@endsection
