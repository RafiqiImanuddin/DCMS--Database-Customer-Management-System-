@extends('layouts.app')
@section('title', 'Pusat Unggah Data Harian & Multi-Periode')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="inline-flex items-center gap-2 rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700">
            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Sistem Sinkronisasi Terpadu (Superadmin)
        </div>
        <h1 class="mt-2 text-2xl font-black text-slate-900 tracking-tight">Import Cross-selling &amp; Turnover Harian</h1>
        <p class="mt-1 text-xs sm:text-sm text-slate-500">1x Unggah untuk memperbarui Customer Outreach, Prediksi Repeat Order, Report Matrix Sosmed, & Format Keep tanpa menimpa data periode sebelumnya.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('imports.template') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300">
            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
            Template Cross-selling (.xlsx)
        </a>
        <a href="{{ route('imports.template_turnover') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300">
            <svg class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
            Template Turnover (.xlsx)
        </a>
    </div>
</div>

{{-- 3 Highlights Architecture Banner --}}
<div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
    <div class="flex items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4">
        <div class="grid size-9 shrink-0 place-items-center rounded-xl bg-emerald-600 text-white shadow-sm">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-900">Smart Anti-Duplikasi</h2>
            <p class="mt-0.5 text-xs text-emerald-800 leading-relaxed">Transaksi harian otomatis dicocokkan via Nomor Faktur. Upload ulang hari yang overlap tidak akan pernah dobel.</p>
        </div>
    </div>
    <div class="flex items-start gap-3 rounded-2xl border border-blue-100 bg-blue-50/70 p-4">
        <div class="grid size-9 shrink-0 place-items-center rounded-xl bg-blue-600 text-white shadow-sm">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
        </div>
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-blue-900">Kumulatif & Multi-Periode</h2>
            <p class="mt-0.5 text-xs text-blue-800 leading-relaxed">Data bulan sebelumnya (Oktober, kemarin, dll.) tetap tersimpan utuh di Supabase. Tidak ada data yang hilang/tertimpa.</p>
        </div>
    </div>
    <div class="flex items-start gap-3 rounded-2xl border border-purple-100 bg-purple-50/70 p-4">
        <div class="grid size-9 shrink-0 place-items-center rounded-xl bg-purple-600 text-white shadow-sm">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        </div>
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-purple-900">Otomatis Sinkron Ganda</h2>
            <p class="mt-0.5 text-xs text-purple-800 leading-relaxed">Cross-selling otomatis mengalir ke Customer Outreach, Cek AR Sosmed, dan auto-matching Google Sheets Format Keep.</p>
        </div>
    </div>
</div>

{{-- Unified Dual-Dropzone Form --}}
<section class="dcms-card mb-6 p-5 sm:p-6 shadow-sm border border-slate-200/80">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-base font-black text-slate-900">Unggah Data SAP (Bisa 1x Unggah Keduanya)</h2>
            <p class="mt-0.5 text-xs text-slate-500">Anda dapat memilih kedua file sekaligus untuk proses 1x klik, atau memilih salah satu file saja jika hanya ada 1 pembaruan.</p>
        </div>
    </div>

    <form id="import-upload-form" method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            {{-- Slot 1: Cross-Selling --}}
            <div class="relative rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50/30 p-4 transition hover:border-emerald-400">
                <div class="mb-2 flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-800">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        1. File Cross-Selling SAP
                    </span>
                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">XLSX / CSV</span>
                </div>
                <p class="mb-3 text-xs text-slate-600">Detail nomor faktur, no. WhatsApp, nama pelanggan, item produk, & nama sales.</p>
                <input id="file" name="file" type="file" accept=".xlsx,.xls,.csv" class="dcms-input block w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-emerald-700 cursor-pointer">
                <div id="file-info-cross" class="mt-2 hidden items-center gap-2 rounded-lg bg-emerald-100/70 px-2.5 py-1 text-xs font-semibold text-emerald-900">
                    <svg class="size-3.5 shrink-0 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="truncate" id="file-name-cross"></span>
                    <span class="ml-auto text-[10px] text-emerald-700 shrink-0 font-bold" id="file-size-cross"></span>
                </div>
                <div class="mt-2.5 flex flex-wrap gap-1.5">
                    <span class="rounded-md bg-white/80 px-2 py-0.5 text-[10px] font-semibold text-slate-600 border border-slate-200">🎯 Customer Dashboard</span>
                    <span class="rounded-md bg-white/80 px-2 py-0.5 text-[10px] font-semibold text-slate-600 border border-slate-200">🤖 Repeat Order AI</span>
                    <span class="rounded-md bg-white/80 px-2 py-0.5 text-[10px] font-semibold text-slate-600 border border-slate-200">📋 Format Keep & Cek AR</span>
                </div>
                @error('file')<p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>@enderror
            </div>

            {{-- Slot 2: Turnover --}}
            <div class="relative rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50/30 p-4 transition hover:border-blue-400">
                <div class="mb-2 flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-800">
                        <span class="size-2 rounded-full bg-blue-500"></span>
                        2. File Turnover SAP
                    </span>
                    <span class="rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800">XLSX / CSV</span>
                </div>
                <p class="mb-3 text-xs text-slate-600">Omset DPP pencapaian sales, gross profit, cabang, & tanggal transaksi SAP.</p>
                <input id="turnover_file" name="turnover_file" type="file" accept=".xlsx,.xls,.csv" class="dcms-input block w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-blue-700 cursor-pointer">
                <div id="file-info-turnover" class="mt-2 hidden items-center gap-2 rounded-lg bg-blue-100/70 px-2.5 py-1 text-xs font-semibold text-blue-900">
                    <svg class="size-3.5 shrink-0 text-blue-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="truncate" id="file-name-turnover"></span>
                    <span class="ml-auto text-[10px] text-blue-700 shrink-0 font-bold" id="file-size-turnover"></span>
                </div>
                <div class="mt-2.5 flex flex-wrap gap-1.5">
                    <span class="rounded-md bg-white/80 px-2 py-0.5 text-[10px] font-semibold text-slate-600 border border-slate-200">📊 Report 1: Daily Matrix</span>
                    <span class="rounded-md bg-white/80 px-2 py-0.5 text-[10px] font-semibold text-slate-600 border border-slate-200">🏆 Report 2: KPI & GP Timegone</span>
                    <span class="rounded-md bg-white/80 px-2 py-0.5 text-[10px] font-semibold text-slate-600 border border-slate-200">☁️ Cloud Table turnovers</span>
                </div>
                @error('turnover_file')<p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-100">
            <p class="text-xs text-slate-500">
                <span class="font-bold text-slate-700">Tips:</span> File diunggah secara langsung tanpa pemotongan batch. Data ribuan baris akan diproses cepat dan aman dalam transaksi cloud.
            </p>
            <button id="import-submit-button" type="submit" class="dcms-primary-button inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-bold shadow-md hover:shadow-lg transition">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Mulai Import</span>
            </button>
        </div>
    </form>
</section>

@php
    $activeTab = (request('tab') === 'turnover' || request()->has('turnover_page')) ? 'turnover' : 'cross';
@endphp

{{-- Import History with Tabs --}}
<section class="dcms-card overflow-hidden border border-slate-200/80 shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50/80 px-5 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="font-black text-slate-900 text-sm">Riwayat Batch Data Tersimpan (Supabase Cloud)</h2>
            <p class="text-xs text-slate-500">Klik tab di sebelah kanan untuk beralih antara Riwayat Cross-Selling &amp; Turnover.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            {{-- Bulk Delete Actions for Cross-Selling --}}
            <div id="bulk-bar-cross" class="hidden items-center gap-2 bg-red-50 border border-red-200 px-3 py-1 rounded-xl">
                <span class="text-xs font-bold text-red-800"><span id="bulk-count-cross">0</span> batch dipilih</span>
                <form id="form-bulk-cross" method="POST" action="{{ route('imports.bulk_destroy') }}">
                    @csrf
                    @method('DELETE')
                    <div id="bulk-inputs-cross"></div>
                    <button type="button" onclick="confirmForm(this, { title: 'Hapus Batch Cross-Selling Terpilih?', message: 'Seluruh transaksi dari batch terpilih akan dihapus secara permanen dari sistem.', confirmText: 'Ya, Hapus Data Terpilih' })" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition cursor-pointer">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Hapus Terpilih</span>
                    </button>
                </form>
            </div>

            {{-- Bulk Delete Actions for Turnover --}}
            <div id="bulk-bar-turnover" class="hidden items-center gap-2 bg-red-50 border border-red-200 px-3 py-1 rounded-xl">
                <span class="text-xs font-bold text-red-800"><span id="bulk-count-turnover">0</span> batch dipilih</span>
                <form id="form-bulk-turnover" method="POST" action="{{ route('imports.bulk_destroy_turnover') }}">
                    @csrf
                    @method('DELETE')
                    <div id="bulk-inputs-turnover"></div>
                    <button type="button" onclick="confirmForm(this, { title: 'Hapus Batch Turnover Terpilih?', message: 'Seluruh baris transaksi turnover dari batch terpilih akan dihapus secara permanen.', confirmText: 'Ya, Hapus Data Terpilih' })" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition cursor-pointer">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Hapus Terpilih</span>
                    </button>
                </form>
            </div>

            {{-- Tab Switcher Pills --}}
            <div class="inline-flex rounded-xl bg-slate-200/80 p-1 text-xs font-bold">
                <button type="button" id="tab-btn-cross" onclick="switchImportTab('cross')" class="rounded-lg px-3.5 py-1.5 transition cursor-pointer {{ $activeTab === 'cross' ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Riwayat Cross-Selling ({{ $imports->total() }})
                </button>
                <button type="button" id="tab-btn-turnover" onclick="switchImportTab('turnover')" class="rounded-lg px-3.5 py-1.5 transition cursor-pointer {{ $activeTab === 'turnover' ? 'bg-white text-blue-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Riwayat Turnover ({{ $turnoverImports->total() }})
                </button>
            </div>
        </div>
    </div>

    {{-- Tab 1: Cross Selling Table --}}
    <div id="tab-panel-cross" class="overflow-x-auto {{ $activeTab === 'cross' ? '' : 'hidden' }}">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="w-10 px-4 py-3.5 text-center">
                        <input type="checkbox" id="check-all-cross" onchange="toggleSelectAll('cross')" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer" title="Pilih Semua">
                    </th>
                    <th class="px-5 py-3.5">File &amp; ID</th>
                    <th class="px-3 py-3.5">Periode Terdeteksi</th>
                    <th class="px-3 py-3.5">Status Cloud</th>
                    <th class="px-3 py-3.5 text-right">Total Baris</th>
                    <th class="px-3 py-3.5 text-right">Tersimpan</th>
                    <th class="px-3 py-3.5 text-right">Dilewati</th>
                    <th class="px-3 py-3.5">Waktu Unggah</th>
                    <th class="px-5 py-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($imports as $import)
                <tr id="row-cross-{{ $import->id }}" class="border-b border-slate-100 transition hover:bg-slate-50/70">
                    <td class="w-10 px-4 py-4 text-center">
                        <input type="checkbox" value="{{ $import->id }}" onchange="updateSelectedBulk('cross')" class="cross-cb rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                    </td>
                    <td class="px-5 py-4">
                        <div class="max-w-64 truncate font-bold text-slate-900">{{ $import->original_name }}</div>
                        <div class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                            <span class="rounded bg-emerald-50 px-1.5 py-0.5 font-bold text-emerald-700">Cross-Selling #{{ $import->id }}</span>
                            <span>oleh {{ $import->user?->name ?? 'Superadmin' }}</span>
                        </div>
                    </td>
                    <td class="px-3 py-4 text-xs font-medium text-slate-700">
                        {{ $import->batch_label ?? ($import->period_start ? $import->period_start . ' s/d ' . $import->period_end : '-') }}
                    </td>
                    <td class="px-3 py-4">
                        @if($import->status === 'processing')
                            <div class="min-w-32" data-import-status data-import-id="{{ $import->id }}" data-status-url="{{ route('imports.status', $import) }}">
                                <span class="inline-flex items-center gap-1.5 font-bold text-amber-700 text-xs">
                                    <span class="size-2 animate-pulse rounded-full bg-amber-500"></span>Memproses
                                </span>
                            </div>
                        @else
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold capitalize {{ $import->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $import->status }}
                            </span>
                        @endif
                    </td>
                    <td class="px-3 py-4 text-right text-xs font-bold text-slate-900">{{ number_format($import->row_count) }}</td>
                    <td class="px-3 py-4 text-right text-xs font-semibold text-emerald-700">{{ number_format($import->success_count) }}</td>
                    <td class="px-3 py-4 text-right text-xs font-semibold text-slate-400">{{ number_format($import->failed_count) }}</td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs text-slate-600">{{ $import->created_at?->format('d M Y H:i') }}</td>
                    <td class="px-5 py-4 text-center">
                        <form method="POST" action="{{ route('imports.destroy', ['import' => $import->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmForm(this, { title: 'Hapus Batch Cross-Selling?', message: 'Data riwayat import ini beserta seluruh transaksi terkait akan dihapus secara permanen.', itemName: 'Import #{{ $import->id }} ({{ addslashes($import->original_name) }})' })" class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-700 shadow-2xs hover:bg-red-50 hover:border-red-300 transition cursor-pointer">
                                <svg class="size-3.5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus</span>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-5 py-8 text-center text-slate-500">Belum ada import.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $imports->links() }}</div>
    </div>

    {{-- Tab 2: Turnover Table --}}
    <div id="tab-panel-turnover" class="overflow-x-auto {{ $activeTab === 'turnover' ? '' : 'hidden' }}">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="w-10 px-4 py-3.5 text-center">
                        <input type="checkbox" id="check-all-turnover" onchange="toggleSelectAll('turnover')" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer" title="Pilih Semua">
                    </th>
                    <th class="px-5 py-3.5">File &amp; ID</th>
                    <th class="px-3 py-3.5">Periode Terdeteksi</th>
                    <th class="px-3 py-3.5">Status Cloud</th>
                    <th class="px-3 py-3.5 text-right">Total Baris</th>
                    <th class="px-3 py-3.5 text-right">Tersimpan</th>
                    <th class="px-3 py-3.5 text-right">Dilewati</th>
                    <th class="px-3 py-3.5">Waktu Unggah</th>
                    <th class="px-5 py-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($turnoverImports as $tImport)
                <tr id="row-turnover-{{ $tImport->id }}" class="border-b border-slate-100 transition hover:bg-slate-50/70">
                    <td class="w-10 px-4 py-4 text-center">
                        <input type="checkbox" value="{{ $tImport->id }}" onchange="updateSelectedBulk('turnover')" class="turnover-cb rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                    </td>
                    <td class="px-5 py-4">
                        <div class="max-w-64 truncate font-bold text-slate-900">{{ $tImport->original_name }}</div>
                        <div class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                            <span class="rounded bg-blue-50 px-1.5 py-0.5 font-bold text-blue-700">Turnover #{{ $tImport->id }}</span>
                            <span>oleh {{ $tImport->user?->name ?? 'Superadmin' }}</span>
                        </div>
                    </td>
                    <td class="px-3 py-4 text-xs font-medium text-slate-700">
                        {{ $tImport->batch_label ?? ($tImport->period_start ? $tImport->period_start . ' s/d ' . $tImport->period_end : '-') }}
                    </td>
                    <td class="px-3 py-4">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold capitalize {{ $tImport->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $tImport->status }}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-right text-xs font-bold text-slate-900">{{ number_format($tImport->row_count) }}</td>
                    <td class="px-3 py-4 text-right text-xs font-semibold text-blue-700">{{ number_format($tImport->success_count) }}</td>
                    <td class="px-3 py-4 text-right text-xs font-semibold text-slate-400">{{ number_format($tImport->failed_count) }}</td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs text-slate-600">{{ $tImport->created_at?->format('d M Y H:i') }}</td>
                    <td class="px-5 py-4 text-center">
                        <form method="POST" action="{{ route('imports.destroy_turnover', ['import' => $tImport->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmForm(this, { title: 'Hapus Batch Turnover?', message: 'Data riwayat turnover ini beserta seluruh baris terkait akan dihapus secara permanen.', itemName: 'Turnover #{{ $tImport->id }} ({{ addslashes($tImport->original_name) }})' })" class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-700 shadow-2xs hover:bg-red-50 hover:border-red-300 transition cursor-pointer">
                                <svg class="size-3.5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus</span>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-5 py-8 text-center text-slate-500">Belum ada riwayat import Turnover.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $turnoverImports->links() }}</div>
    </div>
</section>

<script>
window.currentImportTab = '{{ $activeTab }}';

function switchImportTab(tabName) {
    window.currentImportTab = tabName;
    const btnCross = document.getElementById('tab-btn-cross');
    const btnTurnover = document.getElementById('tab-btn-turnover');
    const panelCross = document.getElementById('tab-panel-cross');
    const panelTurnover = document.getElementById('tab-panel-turnover');
    const bulkCross = document.getElementById('bulk-bar-cross');
    const bulkTurnover = document.getElementById('bulk-bar-turnover');

    if (tabName === 'turnover') {
        panelCross?.classList.add('hidden');
        panelTurnover?.classList.remove('hidden');

        btnCross?.classList.remove('bg-white', 'text-emerald-800', 'shadow-sm');
        btnCross?.classList.add('text-slate-600', 'hover:text-slate-900');

        btnTurnover?.classList.remove('text-slate-600', 'hover:text-slate-900');
        btnTurnover?.classList.add('bg-white', 'text-blue-800', 'shadow-sm');

        if (bulkCross) bulkCross.classList.add('hidden');
        updateSelectedBulk('turnover');
    } else {
        panelTurnover?.classList.add('hidden');
        panelCross?.classList.remove('hidden');

        btnTurnover?.classList.remove('bg-white', 'text-blue-800', 'shadow-sm');
        btnTurnover?.classList.add('text-slate-600', 'hover:text-slate-900');

        btnCross?.classList.remove('text-slate-600', 'hover:text-slate-900');
        btnCross?.classList.add('bg-white', 'text-emerald-800', 'shadow-sm');

        if (bulkTurnover) bulkTurnover.classList.add('hidden');
        updateSelectedBulk('cross');
    }
}

function toggleSelectAll(type) {
    const master = document.getElementById('check-all-' + type);
    const checkboxes = document.querySelectorAll('.' + (type === 'cross' ? 'cross-cb' : 'turnover-cb'));
    if (!master) return;
    checkboxes.forEach(cb => {
        cb.checked = master.checked;
    });
    updateSelectedBulk(type);
}

function updateSelectedBulk(type) {
    const checkboxes = document.querySelectorAll('.' + (type === 'cross' ? 'cross-cb' : 'turnover-cb'));
    const selected = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
    const master = document.getElementById('check-all-' + type);
    const bulkBar = document.getElementById('bulk-bar-' + type);
    const countSpan = document.getElementById('bulk-count-' + type);
    const inputsContainer = document.getElementById('bulk-inputs-' + type);

    if (master) {
        master.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
    }

    checkboxes.forEach(cb => {
        const row = document.getElementById('row-' + type + '-' + cb.value);
        if (row) {
            if (cb.checked) {
                row.classList.add(type === 'cross' ? 'bg-emerald-50/50' : 'bg-blue-50/50');
            } else {
                row.classList.remove('bg-emerald-50/50', 'bg-blue-50/50');
            }
        }
    });

    if (countSpan) countSpan.textContent = selected.length;

    if (inputsContainer) {
        inputsContainer.innerHTML = '';
        selected.forEach(id => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'ids[]';
            hidden.value = id;
            inputsContainer.appendChild(hidden);
        });
    }

    if (bulkBar) {
        if (selected.length > 0 && window.currentImportTab === type) {
            bulkBar.classList.remove('hidden');
            bulkBar.classList.add('flex');
        } else {
            bulkBar.classList.add('hidden');
            bulkBar.classList.remove('flex');
        }
    }
}
</script>

{{-- Loading Overlay --}}
<div id="import-loading-overlay" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/55 px-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="import-loading-title">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-center gap-4">
            <div class="grid size-12 shrink-0 animate-pulse place-items-center rounded-xl bg-brand-50 text-brand-700">
                <svg class="size-6 animate-bounce" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7 9m5-5 5 5M5 20h14"/></svg>
            </div>
            <div>
                <h2 id="import-loading-title" class="font-black text-slate-900">Mengunggah dan memproses file</h2>
                <p class="mt-1 text-sm text-slate-500">Data sedang disinkronkan ke Cloud Supabase...</p>
            </div>
        </div>
        <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100">
            <div id="upload-progress-bar" class="h-full w-0 rounded-full bg-brand-600 transition-all duration-500"></div>
        </div>
        <div class="mt-2 flex justify-between text-xs font-semibold text-slate-500">
            <span id="upload-progress-label">Menyiapkan upload...</span>
            <span id="upload-progress-percent">0%</span>
        </div>
    </div>
</div>
@endsection
