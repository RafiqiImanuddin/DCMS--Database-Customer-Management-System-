@extends('layouts.app')
@section('title', 'Detail Batch: ' . $batch->title)
@section('content')

<!-- Navigation & Header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <a href="{{ route('broadcasts.index', ['tab' => 'batches']) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-purple-700 transition">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Manage Broadcast
        </a>
        <div class="mt-2 flex flex-wrap items-center gap-2.5">
            <h1 class="text-2xl font-black text-purple-800 tracking-tight uppercase">{{ $batch->title }}</h1>
            @if($batch->status === 'completed' || $batch->status === 'sent')
                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                    Completed
                </span>
            @elseif($batch->status === 'failed')
                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">
                    Failed
                </span>
            @else
                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                    {{ ucfirst($batch->status) }}
                </span>
            @endif
        </div>
        <p class="mt-1 text-xs text-slate-500">
            Dikirim pada: <span class="font-bold text-slate-700">{{ $batch->sent_at?->format('D, d M Y H:i') ?: '-' }} WIB</span>
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('broadcasts.batches.download', $batch) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-3.5 py-2.5 text-xs font-bold text-emerald-800 shadow-xs transition hover:bg-emerald-100">
            <svg class="size-4 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 5-5m-5 5-5-5M4 21h16"/></svg>
            Download Excel Batch (.xlsx)
        </a>

        <form method="POST" action="{{ route('broadcasts.batches.destroy', $batch) }}" onsubmit="return confirm('Hapus seluruh riwayat batch broadcast ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-white px-3.5 py-2.5 text-xs font-bold text-red-600 shadow-xs transition hover:bg-red-50 hover:border-red-300">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus Batch
            </button>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="dcms-card p-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Target Penerima</p>
        <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($batch->total_recipients) }}</p>
        <p class="mt-0.5 text-[10px] text-slate-400">Total nomor terdaftar</p>
    </div>
    <div class="dcms-card p-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pesan Terkirim</p>
        <p class="mt-1 text-2xl font-black text-emerald-600">{{ number_format($batch->total_sent) }}</p>
        <p class="mt-0.5 text-[10px] text-slate-400">Sukses diterima WatZap</p>
    </div>
    <div class="dcms-card p-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pesan Gagal</p>
        <p class="mt-1 text-2xl font-black text-rose-600">{{ number_format($batch->total_failed) }}</p>
        <p class="mt-0.5 text-[10px] text-slate-400">Gagal / tidak terkirim</p>
    </div>
    <div class="dcms-card p-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Delivery Rate</p>
        <p class="mt-1 text-2xl font-black text-purple-600">{{ $batch->delivery_rate }}%</p>
        <p class="mt-0.5 text-[10px] text-slate-400">Persentase keberhasilan</p>
    </div>
</div>

<!-- Message & Media Overview -->
<div class="dcms-card mb-6 p-4">
    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Konten Broadcast Batch</h3>
    <div class="grid gap-4 md:grid-cols-12">
        <div class="md:col-span-8">
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 text-xs text-slate-800 font-sans leading-relaxed whitespace-pre-wrap">
                {{ $batch->message }}
            </div>
        </div>
        <div class="md:col-span-4">
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                <span class="block text-[11px] font-bold text-slate-500 mb-1.5">Lampiran Gambar:</span>
                @if($batch->image_url)
                    <div class="flex items-center gap-3">
                        <a href="{{ $batch->image_url }}" target="_blank" rel="noopener" class="group block overflow-hidden rounded-lg border border-slate-200 bg-white p-0.5 hover:border-brand-500 transition w-16 h-16 text-center shadow-xs">
                            <img src="{{ $batch->image_url }}" alt="Flyer Promo" class="h-full w-full rounded object-cover">
                        </a>
                        <div>
                            <span class="text-xs font-semibold text-slate-700 block">Gambar Terlampir</span>
                            <a href="{{ $batch->image_url }}" target="_blank" rel="noopener" class="text-[11px] text-brand-600 hover:underline">Buka Gambar Asli ↗</a>
                        </div>
                    </div>
                @else
                    <span class="text-xs text-slate-400 italic">Tidak ada lampiran gambar (Teks saja)</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recipient Breakdown Table -->
<section class="dcms-card overflow-hidden">
    <div class="border-b border-slate-100 px-5 py-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Rincian Penerima Batch ({{ $reports->total() }})</h3>
            <p class="text-xs text-slate-500">Daftar pelanggan yang masuk dalam pengiriman batch ini.</p>
        </div>
        
        <form method="GET" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="dcms-input py-1.5 text-xs">
                <option value="">Semua Status</option>
                <option value="sent" @selected(request('status') === 'sent')>Terkirim (Sent)</option>
                <option value="failed" @selected(request('status') === 'failed')>Gagal (Failed)</option>
                <option value="queued" @selected(request('status') === 'queued')>Queued</option>
            </select>
            @if(request('status'))
                <a href="{{ route('broadcasts.batches.show', $batch) }}" class="text-xs text-slate-500 hover:underline">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[750px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3.5">Pelanggan</th>
                    <th class="px-3 py-3.5">Nomor WhatsApp</th>
                    <th class="px-3 py-3.5">Status</th>
                    <th class="px-3 py-3.5">Waktu</th>
                    <th class="px-3 py-3.5">Pesan Terpersonalisasi</th>
                    <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($reports as $report)
                <tr class="align-top transition hover:bg-slate-50/70">
                    <td class="px-5 py-4">
                        <div class="font-bold text-slate-900">{{ $report->customer_vendor_code ?: '-' }}</div>
                        <div class="text-xs text-slate-500">{{ $report->prediction?->customer_vendor_name ?: '-' }}</div>
                    </td>
                    <td class="px-3 py-4 font-mono text-xs text-slate-700 whitespace-nowrap">
                        {{ $report->phone_number ?: 'Tidak ada No HP' }}
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
                    <td class="max-w-md px-3 py-4">
                        <div class="line-clamp-2 text-xs text-slate-700 whitespace-pre-wrap leading-relaxed">{{ $report->message }}</div>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <form method="POST" action="{{ route('broadcasts.destroy', $report) }}" onsubmit="return confirm('Hapus pesan pelanggan ini dari laporan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded p-1 text-slate-400 hover:text-red-600 transition" title="Hapus">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada penerima pada batch ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
<div class="mt-4">{{ $reports->links() }}</div>

@endsection
