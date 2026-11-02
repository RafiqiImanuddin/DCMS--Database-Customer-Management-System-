@extends('layouts.app')
@section('title', 'Import Cross-selling')
@section('content')
<section class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="mb-4 font-bold">Upload file bulanan atau gabungan</h2>
    <form method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" class="flex flex-col gap-3 sm:flex-row sm:items-end">
        @csrf
        <div class="flex-1">
            <label class="mb-2 block text-sm font-semibold" for="file">File XLSX, XLS, atau CSV</label>
            <input id="file" name="file" type="file" required accept=".xlsx,.xls,.csv" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
            @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <button class="rounded-lg bg-blue-700 px-5 py-2.5 font-semibold text-white hover:bg-blue-800">Import</button>
    </form>
    <p class="mt-3 text-xs text-slate-500">Periode bulan otomatis terbaca dari kolom Posting Date dan batch gabungan tetap dipisahkan per bulan.</p>
</section>
<section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">File</th><th>Periode</th><th>Status</th><th>Baris</th><th>Sukses</th><th>Gagal</th><th>Tanggal</th></tr></thead>
            <tbody>
            @forelse($imports as $import)
                <tr class="border-b border-slate-100"><td class="px-5 py-3 font-semibold">{{ $import->original_name }}</td><td>{{ $import->batch_label ?: '-' }}</td><td><span class="rounded-full px-2 py-1 text-xs {{ $import->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($import->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $import->status }}</span></td><td>{{ $import->row_count }}</td><td>{{ $import->success_count }}</td><td>{{ $import->failed_count }}</td><td>{{ $import->created_at?->format('d M Y H:i') }}</td></tr>
            @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada import.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
<div class="mt-4">{{ $imports->links() }}</div>
@endsection
