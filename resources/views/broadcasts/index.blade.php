@extends('layouts.app')
@section('title', 'Laporan Broadcast')
@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-brand-600">Aktivitas Outreach</p>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Laporan Broadcast WhatsApp</h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('broadcasts.templates.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            Kelola Template Pesan
        </a>
        <a href="{{ route('broadcasts.download', request()->only('status')) }}" class="dcms-primary-button inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 5-5m-5 5-5-5M4 21h16"/></svg>
            Download Excel (.xlsx)
        </a>
    </div>
</div>

<form method="GET" class="dcms-card mb-6 flex flex-col gap-3 p-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <label for="broadcast-status-filter" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Status laporan</label>
        <select id="broadcast-status-filter" name="status" onchange="this.form.submit()" class="dcms-input w-full py-2.5 sm:w-64">
            <option value="">Semua status</option>
            @foreach(['sent','failed','logged','queued'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="text-xs text-slate-500">
        Menampilkan <span class="font-bold text-slate-800">{{ number_format($reports->total()) }}</span> total laporan
    </div>
</form>

<section class="dcms-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[850px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3.5">Pelanggan</th>
                    <th class="px-3 py-3.5">Lampiran</th>
                    <th class="px-3 py-3.5">Pesan</th>
                    <th class="px-3 py-3.5">Status</th>
                    <th class="px-3 py-3.5">Waktu</th>
                    <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($reports as $report)
                <tr class="border-b border-slate-100 align-top transition hover:bg-slate-50/70">
                    <td class="px-5 py-4">
                        <div class="font-bold text-slate-900">{{ $report->customer_vendor_code ?: '-' }}</div>
                        <div class="mt-1 text-xs text-slate-500 font-mono">{{ $report->phone_number ?: 'No HP kosong' }}</div>
                    </td>
                    <td class="px-3 py-4">
                        @if($report->image_url)
                            <a href="{{ $report->image_url }}" target="_blank" rel="noopener" class="group block overflow-hidden rounded-lg border border-slate-200 bg-white p-0.5 hover:border-brand-500 transition w-12 h-12 text-center shadow-xs" title="Klik untuk memperbesar gambar">
                                <img src="{{ $report->image_url }}" alt="Lampiran" class="h-full w-full rounded object-cover">
                            </a>
                        @else
                            <span class="text-xs text-slate-400">Teks saja</span>
                        @endif
                    </td>
                    <td class="max-w-lg px-3 py-4">
                        <div class="line-clamp-3 leading-relaxed text-xs text-slate-700 whitespace-pre-wrap font-sans">{{ $report->message }}</div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $report->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : ($report->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700') }}" title="{{ $report->provider_response }}">
                            {{ $report->status === 'sent' ? 'Terkirim' : ($report->status === 'failed' ? 'Gagal' : ucfirst($report->status)) }}
                        </span>
                        @if($report->status === 'failed' && $report->provider_response)
                            <div class="mt-1 max-w-xs truncate text-[10px] text-rose-600 font-medium" title="{{ $report->provider_response }}">
                                {{ Str::limit($report->provider_response, 45) }}
                            </div>
                        @endif
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs text-slate-600">
                        {{ $report->sent_at?->format('d M Y H:i') ?: '-' }}
                    </td>
                    <td class="px-5 py-4 text-right">
                        <form method="POST" action="{{ route('broadcasts.destroy', $report) }}" onsubmit="return confirm('Hapus laporan broadcast ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-600 transition hover:border-red-300 hover:bg-red-50">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/></svg>
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Belum ada laporan broadcast.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
<div class="mt-4">{{ $reports->links() }}</div>
@endsection
