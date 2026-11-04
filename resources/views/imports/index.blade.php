@extends('layouts.app')
@section('title', 'Import Cross-selling')
@section('head')
@endsection
@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-sm font-medium text-brand-600">Pengelolaan Data (Superadmin)</p>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Import Cross-selling</h1>
    </div>
    <a href="{{ route('imports.template') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300">
        <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
        Download Template Excel (.xlsx)
    </a>
</div>
<section class="dcms-card mb-6 p-5 sm:p-6">
    <div class="mb-5 flex items-start gap-3"><div class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7 9m5-5 5 5M5 20h14"/></svg></div><div><h2 class="font-black text-slate-900">Upload file bulanan atau gabungan</h2><p class="mt-1 text-sm text-slate-500">File besar akan dipecah otomatis menjadi batch maksimal 3,5 MB dan batch dapat dipilih sebelum upload.</p></div></div>
    <form id="import-upload-form" method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" class="flex flex-col gap-3 sm:flex-row sm:items-end">
        @csrf
        <div class="flex-1">
            <label class="mb-2 block text-sm font-semibold" for="file">File XLSX, XLS, atau CSV</label>
            <input id="file" name="file" type="file" required accept=".xlsx,.xls,.csv" class="dcms-input file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-bold file:text-brand-700">
            @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <button id="import-submit-button" class="dcms-primary-button">Mulai Import</button>
    </form>
    <div id="import-batch-panel" class="mt-4 hidden rounded-xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3 flex items-center justify-between gap-3">
            <div><p class="text-sm font-bold text-slate-700">Pilih batch upload</p><p id="import-batch-summary" class="mt-1 text-xs text-slate-500"></p></div>
            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600"><input id="select-all-import-batches" type="checkbox" checked class="size-4 rounded border-slate-300 text-brand-600">Pilih semua</label>
        </div>
        <div id="import-batch-list" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3"></div>
    </div>
    <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-500"><span class="font-bold text-slate-700">Data yang diproses:</span> cabang, pelanggan, item, nominal, tanggal, nomor transaksi, nomor telepon, nama konsumen, dan nama sales.</div>
</section>
<section class="dcms-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3.5">File</th><th class="px-3 py-3.5">Periode</th><th class="px-3 py-3.5">Status</th><th class="px-3 py-3.5 text-right">Baris</th><th class="px-3 py-3.5 text-right">Sukses</th><th class="px-3 py-3.5 text-right">Gagal</th><th class="px-3 py-3.5">Tanggal</th><th class="px-5 py-3.5 text-center">Aksi</th></tr></thead>
            <tbody>
            @forelse($imports as $import)
                <tr class="border-b border-slate-100 transition hover:bg-slate-50/70">
                    <td class="px-5 py-4">
                        <div class="max-w-64 truncate font-bold text-slate-900">{{ $import->original_name }}</div>
                        <div class="mt-1 text-xs text-slate-400">ID Import #{{ $import->id }}</div>
                    </td>
                    <td class="px-3 py-4">
                        @php
                            $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                            $periodNames = [];
                            preg_match_all('/\d{4}-(\d{2})/', (string) $import->batch_label, $matches);
                            foreach ($matches[1] ?? [] as $monthNumber) {
                                $monthNumber = (int) $monthNumber;
                                $periodNames[] = $monthNames[$monthNumber] ?? $import->batch_label;
                            }
                            $displayPeriod = $periodNames === [] ? '-' : implode(' s/d ', array_unique($periodNames));
                        @endphp
                        {{ $displayPeriod }}
                    </td>
                    <td class="px-3 py-4">
                        @if($import->status === 'processing')
                            <div class="min-w-36" data-import-status data-import-id="{{ $import->id }}" data-status-url="{{ route('imports.status', $import) }}">
                                <div class="mb-1.5 flex items-center justify-between gap-2 text-xs">
                                    <span class="inline-flex items-center gap-1.5 font-bold text-amber-700"><span class="size-2 animate-pulse rounded-full bg-amber-500"></span>Memproses</span>
                                    <span data-import-row-count class="text-slate-500">{{ number_format($import->row_count) }} baris</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div data-import-progress class="h-full w-2/5 animate-pulse rounded-full bg-amber-500"></div>
                                </div>
                            </div>
                        @else
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ $import->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $import->status }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-4 text-right">{{ number_format($import->row_count) }}</td>
                    <td class="px-3 py-4 text-right font-semibold text-emerald-700">{{ number_format($import->success_count) }}</td>
                    <td class="px-3 py-4 text-right font-semibold text-red-600">{{ number_format($import->failed_count) }}</td>
                    <td class="px-3 py-4 whitespace-nowrap text-slate-600">{{ $import->created_at?->format('d M Y H:i') }}</td>
                    <td class="px-5 py-4 text-center">
                        <form method="POST" action="{{ route('imports.destroy', ['import' => $import->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmForm(this, { title: 'Hapus Import Data?', message: 'Data riwayat import ini beserta seluruh catatan terkait akan dihapus secara permanen.', itemName: 'Import #{{ $import->id }} ({{ $import->file_name }})' })" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 transition">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-5 py-8 text-center text-slate-500">Belum ada import.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
<div class="mt-4">{{ $imports->links() }}</div>

<div id="import-loading-overlay" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/55 px-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="import-loading-title">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-center gap-4">
            <div class="grid size-12 shrink-0 animate-pulse place-items-center rounded-xl bg-brand-50 text-brand-700">
                <svg class="size-6 animate-bounce" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7 9m5-5 5 5M5 20h14"/></svg>
            </div>
            <div>
                <h2 id="import-loading-title" class="font-black text-slate-900">Mengunggah dan memproses file</h2>
                <p class="mt-1 text-sm text-slate-500">Jangan tutup halaman ini.</p>
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
