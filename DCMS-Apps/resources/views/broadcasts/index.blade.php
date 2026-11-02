@extends('layouts.app')
@section('title', 'Laporan Broadcast')
@section('content')
<form method="GET" class="mb-6">
    <select name="status" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
        <option value="">Semua status</option>
        @foreach(['sent','failed','logged','queued'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach
    </select>
</form>
<section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">Pelanggan</th><th>Channel</th><th>Status</th><th>Pesan</th><th>Waktu</th><th>Respons</th></tr></thead>
            <tbody>
            @forelse($reports as $report)
                <tr class="border-b border-slate-100 align-top"><td class="px-5 py-3"><div class="font-semibold">{{ $report->customer_vendor_code ?: '-' }}</div><div class="text-xs text-slate-500">{{ $report->phone_number ?: 'No HP kosong' }}</div></td><td>{{ $report->channel }}</td><td><span class="rounded-full px-2 py-1 text-xs {{ $report->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : ($report->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700') }}">{{ $report->status }}</span></td><td class="max-w-md py-3"><div class="line-clamp-2">{{ $report->message }}</div></td><td>{{ $report->sent_at?->format('d M Y H:i') ?: '-' }}</td><td class="max-w-xs py-3 text-xs text-slate-500">{{ $report->provider_response ?: '-' }}</td></tr>
            @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada laporan broadcast.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
<div class="mt-4">{{ $reports->links() }}</div>
@endsection
