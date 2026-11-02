@extends('layouts.app')
@section('title', 'Laporan Broadcast')
@section('content')
<div class="mb-6"><p class="text-sm font-medium text-brand-600">Aktivitas Outreach</p><h1 class="mt-1 text-2xl font-black text-slate-900">Laporan Broadcast</h1></div>
<form method="GET" class="dcms-card mb-6 flex flex-col gap-3 p-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <label for="broadcast-status-filter" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Status laporan</label>
        <select id="broadcast-status-filter" name="status" onchange="this.form.submit()" class="dcms-input w-full py-2.5 sm:w-64">
            <option value="">Semua status</option>
            @foreach(['sent','failed','logged','queued'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach
        </select>
    </div>
    <a href="{{ route('broadcasts.download', request()->only('status')) }}" class="dcms-primary-button inline-flex items-center justify-center gap-2 px-4 py-2.5">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 5-5m-5 5-5-5M4 21h16"/></svg>
        Download CSV
    </a>
</form>
<section class="dcms-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3.5">Pelanggan</th><th class="px-3 py-3.5">Channel</th><th class="px-3 py-3.5">Status</th><th class="px-3 py-3.5">Pesan</th><th class="px-3 py-3.5">Waktu</th><th class="px-3 py-3.5">Respons</th><th class="px-5 py-3.5 text-right">Aksi</th></tr></thead>
            <tbody>
            @forelse($reports as $report)
                <tr class="border-b border-slate-100 align-top transition hover:bg-slate-50/70">
                    <td class="px-5 py-4"><div class="font-bold text-slate-900">{{ $report->customer_vendor_code ?: '-' }}</div><div class="mt-1 text-xs text-slate-500">{{ $report->phone_number ?: 'No HP kosong' }}</div></td>
                    <td class="px-3 py-4 capitalize">{{ $report->channel }}</td>
                    <td class="px-3 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $report->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : ($report->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700') }}">{{ $report->status }}</span></td>
                    <td class="max-w-md px-3 py-4"><div class="line-clamp-2 leading-5 text-slate-700">{{ $report->message }}</div></td>
                    <td class="px-3 py-4 whitespace-nowrap text-slate-600">{{ $report->sent_at?->format('d M Y H:i') ?: '-' }}</td>
                    <td class="max-w-xs px-3 py-4 text-xs leading-5 text-slate-500">{{ $report->provider_response ?: '-' }}</td>
                    <td class="px-5 py-4 text-right">
                        <form method="POST" action="{{ route('broadcasts.destroy', $report) }}" onsubmit="return confirm('Hapus laporan broadcast ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-600 transition hover:border-red-300 hover:bg-red-50">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/></svg>
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada laporan broadcast.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
<div class="mt-4">{{ $reports->links() }}</div>
@endsection
