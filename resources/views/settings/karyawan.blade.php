@extends('layouts.app')

@section('title', 'Data Karyawan & Bobot Penilaian KPI')

@section('content')
@php
    $isSuperAdmin = auth()->check() && auth()->user()->isSuperAdmin();
@endphp
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-700">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
                    Master Setting
                </span>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-semibold text-slate-500">Karyawan & Bobot KPI</span>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">Data Karyawan & Bobot Penilaian</h1>
            <p class="text-xs text-slate-500 sm:text-sm">Kelola master data admin sales dan konfigurasikan bobot kriteria evaluasi kinerja untuk manager.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ \App\Support\RouteCipher::url('monitoring.sales.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-indigo-600/20 hover:bg-indigo-700 transition">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                <span>Buka Monitoring Manager</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200">
        <a href="{{ route('settings.karyawan.index', ['tab' => 'employees']) }}" 
           class="inline-flex items-center gap-2 border-b-2 px-5 py-3 text-sm font-bold transition {{ $activeTab === 'employees' ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
            <span>Data Karyawan Sales</span>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $employees->total() }}</span>
        </a>

        <a href="{{ route('settings.karyawan.index', ['tab' => 'weights']) }}" 
           class="inline-flex items-center gap-2 border-b-2 px-5 py-3 text-sm font-bold transition {{ $activeTab === 'weights' ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z"/></svg>
            <span>Bobot & Kriteria Penilaian</span>
            <span class="rounded-full {{ abs($totalMatrixWeight - 100) < 0.1 && abs($totalWeight - 100) < 0.1 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} px-2 py-0.5 text-xs font-bold">Matrix {{ $totalMatrixWeight }}% | Layanan {{ $totalWeight }}%</span>
        </a>
    </div>

    @if ($activeTab === 'employees')
    <!-- TAB 1: DATA KARYAWAN -->
    <div class="space-y-4">
        <!-- Filter & Actions Bar -->
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs md:flex-row md:items-center md:justify-between">
            <form method="GET" action="{{ route('settings.karyawan.index') }}" class="flex flex-1 flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="employees">

                <!-- Search Input -->
                <div class="relative min-w-[200px] flex-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIK, atau no WA..." class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3.5 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/10">
                </div>

                <!-- Cabang Filter -->
                <div class="w-40">
                    <select name="cabang" class="dcms-select w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 focus:border-brand-500 focus:outline-none">
                        <option value="">Semua Cabang</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b }}" {{ $cabang === $b ? 'selected' : '' }}>Cabang {{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="w-36">
                    <select name="status" class="dcms-select w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 focus:border-brand-500 focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 transition">Filter</button>
                @if ($search || $cabang || $status)
                    <a href="{{ route('settings.karyawan.index', ['tab' => 'employees']) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Reset</a>
                @endif
            </form>

            @if ($isSuperAdmin)
            <button type="button" onclick="openCreateEmployeeModal()" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700 transition">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Karyawan Baru</span>
            </button>
            @else
            <div class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-500">
                <svg class="size-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span>Mode Baca (Kelola Data: Khusus Superadmin)</span>
            </div>
            @endif
        </div>

        <!-- Table Karyawan -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3.5">Karyawan</th>
                            <th class="px-5 py-3.5">NIK / Kode Sales</th>
                            <th class="px-5 py-3.5">Cabang & Posisi</th>
                            <th class="px-5 py-3.5">Target Omzet (Rp)</th>
                            <th class="px-5 py-3.5">WhatsApp / Kontak</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-center">Riwayat Penilaian</th>
                            <th class="px-5 py-3.5 text-right">{{ $isSuperAdmin ? 'Aksi' : 'Otoritas' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($employees as $emp)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="grid size-10 shrink-0 place-items-center rounded-xl font-black text-white shadow-xs" style="background-color: {{ $emp->avatar_color ?: '#2563eb' }}">
                                        {{ $emp->initials }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $emp->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $emp->email ?: 'Tidak ada email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono font-semibold text-slate-600">
                                {{ $emp->nik }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-700">
                                    🏢 {{ $emp->cabang }}
                                </div>
                                <div class="mt-0.5 text-[11px] font-medium text-slate-500">{{ $emp->role }}</div>
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                Rp {{ number_format($emp->target ?? 867261886, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^\d]/', '', $emp->phone)) }}" target="_blank" class="inline-flex items-center gap-1.5 font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                                    <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/></svg>
                                    <span>{{ $emp->phone }}</span>
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                @if ($emp->status === 'active')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600 border border-slate-200">
                                        <span class="size-1.5 rounded-full bg-slate-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-700">
                                    {{ $emp->assessments_count }} Periode
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if ($isSuperAdmin)
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" 
                                            onclick="openEditEmployeeModal({{ json_encode($emp) }})" 
                                            class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition" 
                                            title="Edit Data Karyawan">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                    </button>

                                    <form method="POST" action="{{ route('settings.karyawan.destroy', $emp->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data karyawan {{ $emp->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Hapus Karyawan">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                                @else
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-400">Superadmin Only</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <svg class="mx-auto size-12 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                <p class="mt-2 text-sm font-semibold text-slate-600">Belum ada data karyawan sales</p>
                                <p class="text-xs text-slate-400">Silakan tambahkan data karyawan pertama menggunakan tombol di atas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($employees->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">
                {{ $employees->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif

    @if ($activeTab === 'weights')
    <!-- TAB 2: BOBOT & KRITERIA PENILAIAN -->
    <div class="space-y-6">
        <!-- Form Update Bobot -->
        <form method="POST" action="{{ route('settings.kpi_weights.update') }}">
            @csrf

            <!-- SECTION 1: BOBOT RAPOR MATRIX SOSMED -->
            <div class="rounded-2xl border border-indigo-200/80 bg-white p-6 shadow-xs space-y-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-indigo-50 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-700">
                                📊 Satu Gerbang Master Setting
                            </span>
                            <span class="text-xs font-semibold text-slate-400">•</span>
                            <span class="text-xs font-bold text-slate-600">Workspace Sosmed</span>
                        </div>
                        <h2 class="text-base font-black text-slate-900">Bobot Rapor Matrix Sosmed (Report 2: Evaluasi Akhir)</h2>
                        <p class="text-xs text-slate-500">Bobot komponen ini mengontrol secara langsung persentase <strong>% AKHIR (RAPOR)</strong> dan status emoji performa sales di halaman Report Matrix.</p>
                    </div>

                    <div class="shrink-0">
                        <span id="matrix-status-pill" class="inline-flex items-center gap-1.5 rounded-xl {{ abs($totalMatrixWeight - 100) < 0.1 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }} px-3.5 py-2 text-xs font-black">
                            <span class="size-2 rounded-full {{ abs($totalMatrixWeight - 100) < 0.1 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            <span>Total Bobot Matrix: <span id="matrix-total-val">{{ $totalMatrixWeight }}</span>%</span>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <!-- Bobot Omzet -->
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-indigo-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="rounded bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-800 uppercase">Komponen 1</span>
                                <div class="mt-1.5 font-black text-sm text-slate-900">Pencapaian Omzet</div>
                                <div class="text-[11px] text-slate-500">Target omzet bulanan admin sales</div>
                            </div>
                            <div class="text-right">
                                <div class="relative inline-flex items-center">
                                    <input type="number" 
                                           id="weight_matrix_omzet"
                                           name="matrix_weights[omzet]" 
                                           value="{{ $matrixWeights['omzet'] ?? 35 }}" 
                                           min="0" 
                                           max="100" 
                                           step="0.5" 
                                           {{ $isSuperAdmin ? '' : 'disabled' }}
                                           oninput="recalculateWeights()" 
                                           class="matrix-weight-input w-20 rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-right text-sm font-black text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 focus:outline-none">
                                    <span class="ml-1 text-xs font-bold text-slate-500">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bobot Gross Profit -->
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-indigo-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="rounded bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-800 uppercase">Komponen 2</span>
                                <div class="mt-1.5 font-black text-sm text-slate-900">Gross Profit (GP)</div>
                                <div class="text-[11px] text-slate-500">Target profit 5% dari target omzet</div>
                            </div>
                            <div class="text-right">
                                <div class="relative inline-flex items-center">
                                    <input type="number" 
                                           id="weight_matrix_gp"
                                           name="matrix_weights[gp]" 
                                           value="{{ $matrixWeights['gp'] ?? 35 }}" 
                                           min="0" 
                                           max="100" 
                                           step="0.5" 
                                           {{ $isSuperAdmin ? '' : 'disabled' }}
                                           oninput="recalculateWeights()" 
                                           class="matrix-weight-input w-20 rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-right text-sm font-black text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 focus:outline-none">
                                    <span class="ml-1 text-xs font-bold text-slate-500">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bobot Cross Selling -->
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-indigo-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="rounded bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-800 uppercase">Komponen 3</span>
                                <div class="mt-1.5 font-black text-sm text-slate-900">Cross-Selling (>50K)</div>
                                <div class="text-[11px] text-slate-500">Standar rasio 1 cross per 3 gadget</div>
                            </div>
                            <div class="text-right">
                                <div class="relative inline-flex items-center">
                                    <input type="number" 
                                           id="weight_matrix_cross"
                                           name="matrix_weights[cross_selling]" 
                                           value="{{ $matrixWeights['cross_selling'] ?? 30 }}" 
                                           min="0" 
                                           max="100" 
                                           step="0.5" 
                                           {{ $isSuperAdmin ? '' : 'disabled' }}
                                           oninput="recalculateWeights()" 
                                           class="matrix-weight-input w-20 rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-right text-sm font-black text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 focus:outline-none">
                                    <span class="ml-1 text-xs font-bold text-slate-500">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-indigo-50/70 p-3 text-xs text-indigo-900 flex items-center gap-2">
                    <svg class="size-4 shrink-0 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    <span>Formula Rapor Akhir: <strong id="formula-preview-text">% Akhir = ({{ $matrixWeights['omzet'] ?? 35 }}% x Omzet) + ({{ $matrixWeights['gp'] ?? 35 }}% x GP) + ({{ $matrixWeights['cross_selling'] ?? 30 }}% x Cross-Selling)</strong>.</span>
                </div>
            </div>

            <!-- SECTION 2: KRITERIA KINERJA KUALITATIF (MANAGER) -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-6 mt-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                💬 Evaluasi Layanan & Chat
                            </span>
                            <span class="text-xs font-semibold text-slate-400">•</span>
                            <span class="text-xs font-bold text-slate-600">Monitoring Manager</span>
                        </div>
                        <h2 class="mt-1 text-base font-black text-slate-900">Kriteria Penilaian Kinerja Kualitatif Sales</h2>
                        <p class="text-xs text-slate-500">Kriteria evaluasi harian manager untuk cara balas pesan konsumen, etika, respon time, dan penawaran promo.</p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <span id="qual-status-pill" class="inline-flex items-center gap-1.5 rounded-xl {{ abs($totalWeight - 100) < 0.1 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }} px-3.5 py-2 text-xs font-black">
                            <span class="size-2 rounded-full {{ abs($totalWeight - 100) < 0.1 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            <span>Total Kriteria Aktif: <span id="summary-total-weight">{{ $totalWeight }}</span>%</span>
                        </span>

                        @if ($isSuperAdmin)
                        <button type="button" onclick="openAddCriterionModal()" class="inline-flex items-center gap-2 rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-slate-700 border border-slate-300 shadow-xs hover:bg-slate-50 transition shrink-0">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span>Tambah Kriteria Kustom</span>
                        </button>
                        @endif
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach ($kpiCriteria as $crit)
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 transition hover:border-slate-300">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="space-y-1 flex-1">
                                <div class="flex items-center gap-2">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" 
                                               name="actives[{{ $crit->id }}]" 
                                               value="1" 
                                               {{ $crit->is_active ? 'checked' : '' }} 
                                               {{ $isSuperAdmin ? '' : 'disabled' }}
                                               onchange="recalculateWeights()" 
                                               class="criterion-active-checkbox rounded text-brand-600 focus:ring-brand-500">
                                        <span class="font-bold text-sm text-slate-900">{{ $crit->name }}</span>
                                    </label>
                                    <span class="rounded bg-slate-200/60 px-1.5 py-0.5 font-mono text-[10px] text-slate-600">{{ $crit->code }}</span>
                                </div>
                                <div class="text-xs text-slate-500">{{ $crit->description }}</div>
                                <div class="flex items-center gap-1.5 text-[11px] font-medium text-slate-600">
                                    <span class="text-slate-400">Benchmark Target:</span>
                                    <input type="text" 
                                           name="benchmarks[{{ $crit->id }}]" 
                                           value="{{ $crit->target_benchmark }}" 
                                           {{ $isSuperAdmin ? '' : 'disabled' }}
                                           placeholder="Contoh: Respon < 5 mnt" 
                                           class="rounded border border-slate-200 bg-white px-2 py-0.5 text-xs text-slate-700 focus:outline-none focus:border-brand-500">
                                </div>
                            </div>

                            <div class="flex items-center gap-4 shrink-0">
                                <div class="text-right">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Bobot (%)</div>
                                    <div class="relative mt-0.5 inline-flex items-center">
                                        <input type="number" 
                                               name="weights[{{ $crit->id }}]" 
                                               value="{{ $crit->weight }}" 
                                               min="0" 
                                               max="100" 
                                               step="0.5" 
                                               {{ $isSuperAdmin ? '' : 'disabled' }}
                                               oninput="recalculateWeights()" 
                                               class="criterion-weight-input w-24 rounded-xl border border-slate-300 bg-white px-3 py-2 text-right text-sm font-black text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 focus:outline-none">
                                        <span class="ml-1.5 text-xs font-bold text-slate-500">%</span>
                                    </div>
                                </div>

                                @if ($isSuperAdmin && !in_array($crit->code, ['resp_time', 'broadcast_vol', 'closing_rate', 'crm_discipline', 'csat_quality']))
                                    <form method="POST" action="{{ route('settings.kpi_criteria.destroy', $crit->id) }}" onsubmit="return confirm('Hapus kriteria {{ $crit->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Hapus Kriteria Kustom">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 pt-4">
                    <div class="text-xs text-slate-500">
                        Total Bobot Aktif: <span id="footer-total-weight" class="font-bold text-slate-900">{{ $totalWeight }}%</span>
                    </div>

                    @if ($isSuperAdmin)
                    <button type="submit" id="btn-save-weights" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700 transition">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        <span>Simpan Bobot Penilaian</span>
                    </button>
                    @else
                    <div class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-500">
                        <svg class="size-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>Pengubahan bobot kriteria hanya dapat dilakukan oleh Superadmin</span>
                    </div>
                    @endif
            </div>
        </form>
    </div>
    @endif
</div>

<!-- MODAL TAMBAH / EDIT KARYAWAN -->
<div id="employeeModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeEmployeeModal()"></div>
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl transition">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="employeeModalTitle" class="text-base font-bold text-slate-900">Tambah Data Karyawan Sales</h3>
            <button type="button" onclick="closeEmployeeModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">✕</button>
        </div>

        <form id="employeeForm" method="POST" action="{{ route('settings.karyawan.store') }}" class="mt-4 space-y-4">
            @csrf
            <div id="methodContainer"></div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">NIK / ID Karyawan <span class="text-red-500">*</span></label>
                    <input type="text" id="empNik" name="nik" required placeholder="Contoh: SLS-BJMA-01" class="dcms-input mt-1">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Cabang Toko <span class="text-red-500">*</span></label>
                    <select id="empCabang" name="cabang" required class="dcms-select mt-1 w-full">
                        @foreach ($branches as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Nama Lengkap Karyawan <span class="text-red-500">*</span></label>
                <input type="text" id="empName" name="name" required placeholder="Nama lengkap admin sales" class="dcms-input mt-1">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Jabatan / Role <span class="text-red-500">*</span></label>
                    <input type="text" id="empRole" name="role" required value="Admin Sales" placeholder="Admin Sales / CS" class="dcms-input mt-1">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Nomor WhatsApp <span class="text-red-500">*</span></label>
                    <input type="text" id="empPhone" name="phone" required placeholder="08123456789" class="dcms-input mt-1">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Target Omzet Bulanan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" id="empTarget" name="target" required value="867261886" min="0" step="1000" placeholder="867261886" class="dcms-input mt-1 font-mono font-bold">
                    <span class="text-[10px] text-slate-400">Target sales untuk Report Matrix (Rp 867.261.886 / Rp 1.081.261.572).</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Status Karyawan <span class="text-red-500">*</span></label>
                    <select id="empStatus" name="status" class="dcms-select mt-1 w-full">
                        <option value="active">Aktif (Tampil di Report Matrix)</option>
                        <option value="inactive">Nonaktif (Disembunyikan)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Email (Opsional)</label>
                <input type="email" id="empEmail" name="email" placeholder="sales@gadgetmart.id" class="dcms-input mt-1">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Catatan Khusus (Opsional)</label>
                <textarea id="empNotes" name="notes" rows="2" placeholder="Fokus penjualan, spesialisasi cabang, dll." class="dcms-input mt-1"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeEmployeeModal()" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white hover:bg-brand-700">Simpan Karyawan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH KRITERIA KUSTOM -->
<div id="criterionModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeAddCriterionModal()"></div>
    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl transition">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tambah Kriteria Penilaian Baru</h3>
            <button type="button" onclick="closeAddCriterionModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="{{ route('settings.kpi_criteria.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700">Nama Kriteria <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Cross-Selling Aksesoris" class="dcms-input mt-1">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Kode Unik <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required placeholder="cross_acc" class="dcms-input mt-1">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Bobot Awal (%) <span class="text-red-500">*</span></label>
                    <input type="number" name="weight" value="10" min="0" max="100" step="0.5" required class="dcms-input mt-1">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Target Benchmark</label>
                <input type="text" name="target_benchmark" placeholder="Contoh: Minimal 30 aksesoris / bulan" class="dcms-input mt-1">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Deskripsi Kriteria</label>
                <textarea name="description" rows="2" placeholder="Penjelasan aspek yang dinilai..." class="dcms-input mt-1"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeAddCriterionModal()" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white hover:bg-brand-700">Tambahkan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateEmployeeModal() {
    document.getElementById('employeeModalTitle').innerText = 'Tambah Data Karyawan Sales';
    const form = document.getElementById('employeeForm');
    form.action = "{{ route('settings.karyawan.store') }}";
    document.getElementById('methodContainer').innerHTML = '';
    document.getElementById('empNik').value = '';
    document.getElementById('empCabang').value = 'Head Office';
    document.getElementById('empName').value = '';
    document.getElementById('empRole').value = 'Admin Sales';
    document.getElementById('empTarget').value = '867261886';
    document.getElementById('empPhone').value = '';
    document.getElementById('empEmail').value = '';
    document.getElementById('empStatus').value = 'active';
    document.getElementById('empNotes').value = '';
    document.getElementById('employeeModal').classList.remove('hidden');
    document.getElementById('employeeModal').classList.add('flex');
}

function openEditEmployeeModal(emp) {
    document.getElementById('employeeModalTitle').innerText = 'Edit Data Karyawan: ' + emp.name;
    const form = document.getElementById('employeeForm');
    form.action = "/settings/karyawan/" + emp.id;
    document.getElementById('methodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('empNik').value = emp.nik || '';
    document.getElementById('empCabang').value = emp.cabang || '';
    document.getElementById('empName').value = emp.name || '';
    document.getElementById('empRole').value = emp.role || 'Admin Sales';
    document.getElementById('empTarget').value = emp.target || '867261886';
    document.getElementById('empPhone').value = emp.phone || '';
    document.getElementById('empEmail').value = emp.email || '';
    document.getElementById('empStatus').value = emp.status || 'active';
    document.getElementById('empNotes').value = emp.notes || '';
    document.getElementById('employeeModal').classList.remove('hidden');
    document.getElementById('employeeModal').classList.add('flex');
}

function closeEmployeeModal() {
    document.getElementById('employeeModal').classList.add('hidden');
    document.getElementById('employeeModal').classList.remove('flex');
}

function openAddCriterionModal() {
    document.getElementById('criterionModal').classList.remove('hidden');
    document.getElementById('criterionModal').classList.add('flex');
}

function closeAddCriterionModal() {
    document.getElementById('criterionModal').classList.add('hidden');
    document.getElementById('criterionModal').classList.remove('flex');
}

function recalculateWeights() {
    // 1. Recalculate Sosmed Matrix Weights
    const omzetInput = document.getElementById('weight_matrix_omzet');
    const gpInput = document.getElementById('weight_matrix_gp');
    const crossInput = document.getElementById('weight_matrix_cross');

    const wOmzet = omzetInput ? (parseFloat(omzetInput.value) || 0) : 35;
    const wGp = gpInput ? (parseFloat(gpInput.value) || 0) : 35;
    const wCross = crossInput ? (parseFloat(crossInput.value) || 0) : 30;
    const matrixTotal = Math.round((wOmzet + wGp + wCross) * 100) / 100;

    const matrixValEl = document.getElementById('matrix-total-val');
    const matrixPillEl = document.getElementById('matrix-status-pill');
    const formulaEl = document.getElementById('formula-preview-text');

    if (matrixValEl) matrixValEl.innerText = matrixTotal;
    if (formulaEl) {
        formulaEl.innerText = `% Akhir = (${wOmzet}% x Omzet) + (${wGp}% x GP) + (${wCross}% x Cross-Selling)`;
    }
    if (matrixPillEl) {
        if (Math.abs(matrixTotal - 100) < 0.1) {
            matrixPillEl.className = 'inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 px-3.5 py-2 text-xs font-black';
            matrixPillEl.innerHTML = '<span class="size-2 rounded-full bg-emerald-500"></span><span>Total Bobot Matrix: ' + matrixTotal + '%</span>';
        } else {
            matrixPillEl.className = 'inline-flex items-center gap-1.5 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 px-3.5 py-2 text-xs font-black';
            matrixPillEl.innerHTML = '<span class="size-2 rounded-full bg-amber-500"></span><span>Total Bobot Matrix: ' + matrixTotal + '% (Harus 100%)</span>';
        }
    }

    // 2. Recalculate Qualitative Kriteria Weights
    let qualTotal = 0;
    const inputs = document.querySelectorAll('.criterion-weight-input');
    inputs.forEach(input => {
        const row = input.closest('.rounded-xl');
        const checkbox = row ? row.querySelector('.criterion-active-checkbox') : null;
        if (!checkbox || checkbox.checked) {
            qualTotal += parseFloat(input.value) || 0;
        }
    });

    qualTotal = Math.round(qualTotal * 100) / 100;
    const sumEl = document.getElementById('summary-total-weight');
    const footerEl = document.getElementById('footer-total-weight');
    const qualPillEl = document.getElementById('qual-status-pill');

    if (sumEl) sumEl.innerText = qualTotal;
    if (footerEl) footerEl.innerText = qualTotal + '%';
    if (qualPillEl) {
        if (Math.abs(qualTotal - 100) < 0.1) {
            qualPillEl.className = 'inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 px-3.5 py-2 text-xs font-black';
            qualPillEl.innerHTML = '<span class="size-2 rounded-full bg-emerald-500"></span><span>Total Kriteria Aktif: ' + qualTotal + '%</span>';
        } else {
            qualPillEl.className = 'inline-flex items-center gap-1.5 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 px-3.5 py-2 text-xs font-black';
            qualPillEl.innerHTML = '<span class="size-2 rounded-full bg-amber-500"></span><span>Total Kriteria Aktif: ' + qualTotal + '% (Harus 100%)</span>';
        }
    }

    // 3. Save Button State
    const saveBtn = document.getElementById('btn-save-weights');
    if (saveBtn) {
        const matrixOk = Math.abs(matrixTotal - 100) < 0.1;
        const qualOk = Math.abs(qualTotal - 100) < 0.1;

        if (!matrixOk || !qualOk) {
            saveBtn.classList.remove('bg-brand-600', 'hover:bg-brand-700');
            saveBtn.classList.add('bg-amber-600', 'hover:bg-amber-700');
            saveBtn.innerText = 'Simpan (Matrix: ' + matrixTotal + '%, Layanan: ' + qualTotal + '% - Sesuaikan ke 100%)';
        } else {
            saveBtn.classList.remove('bg-amber-600', 'hover:bg-amber-700');
            saveBtn.classList.add('bg-brand-600', 'hover:bg-brand-700');
            saveBtn.innerText = 'Simpan Seluruh Bobot Penilaian (100% Pas)';
        }
    }
}
</script>
@endsection

