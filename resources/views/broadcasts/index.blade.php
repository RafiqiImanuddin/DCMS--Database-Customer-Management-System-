@extends('layouts.app')
@section('title', 'Manage Broadcast')
@section('content')

<!-- Header Section (Manage Broadcast) -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manage Broadcast</h1>
            <span class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-800 shadow-xs">
                <svg class="size-3 text-amber-600 fill-amber-500" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                Unlimited Broadcasts
            </span>
        </div>
        <p class="mt-0.5 text-xs text-slate-500">Pantau performa pengiriman broadcast WhatsApp per batch kampanye outreach.</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('broadcasts.templates.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-xs transition hover:bg-slate-50">
            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            Kelola Template
        </a>

        @if($tab === 'all')
            <a href="{{ route('broadcasts.download', request()->only('status')) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-xs transition hover:bg-slate-50">
                <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 5-5m-5 5-5-5M4 21h16"/></svg>
                Download Excel (.xlsx)
            </a>
        @endif

        <button type="button" onclick="openNewBroadcastModal()" class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-rose-500 focus:ring-2 focus:ring-rose-500/20">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            + New Broadcast
        </button>
    </div>
</div>

<!-- KPI Mini Summary Cards -->
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="dcms-card p-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Batch</p>
        <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($totalBatches) }}</p>
        <p class="mt-0.5 text-[10px] text-slate-400">Kampanye dijalankan</p>
    </div>
    <div class="dcms-card p-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Target</p>
        <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($totalRecipients) }}</p>
        <p class="mt-0.5 text-[10px] text-slate-400">Nomor penerima</p>
    </div>
    <div class="dcms-card p-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pesan Terkirim</p>
        <p class="mt-1 text-2xl font-black text-emerald-600">{{ number_format($totalSent) }}</p>
        <p class="mt-0.5 text-[10px] text-slate-400">Sukses terdistribusi</p>
    </div>
    <div class="dcms-card p-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Rata-rata Delivery</p>
        <p class="mt-1 text-2xl font-black text-purple-600">{{ $avgDeliveryRate }}%</p>
        <p class="mt-0.5 text-[10px] text-slate-400">Tingkat keberhasilan</p>
    </div>
</div>

<!-- Tabs & Filter Bar -->
<div class="dcms-card mb-5 p-3.5">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <!-- Tab Switcher -->
        <div class="flex items-center rounded-xl bg-slate-100 p-1 text-xs font-bold text-slate-600">
            <a href="{{ route('broadcasts.index', ['tab' => 'batches']) }}" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 transition {{ $tab !== 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900' }}">
                <svg class="size-4 {{ $tab !== 'all' ? 'text-purple-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                Laporan Per Batch
                <span class="rounded-full bg-slate-100 px-1.5 py-0.2 text-[10px] font-semibold text-slate-600">{{ $batches->total() }}</span>
            </a>
            <a href="{{ route('broadcasts.index', ['tab' => 'all']) }}" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 transition {{ $tab === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900' }}">
                <svg class="size-4 {{ $tab === 'all' ? 'text-brand-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                Semua Detail Pesan
                <span class="rounded-full bg-slate-100 px-1.5 py-0.2 text-[10px] font-semibold text-slate-600">{{ $reports->total() }}</span>
            </a>
        </div>

        <!-- Search & Filter Controls -->
        <form method="GET" action="{{ route('broadcasts.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            
            <div class="relative min-w-[240px] flex-1 sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Broadcast..." class="dcms-input py-2 pl-9 pr-3 text-xs w-full">
                <svg class="pointer-events-none absolute left-3 top-2.5 size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            </div>

            <select name="status" onchange="this.form.submit()" class="dcms-input py-2 text-xs font-medium">
                <option value="">Semua Status</option>
                @if($tab !== 'all')
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                    <option value="processing" @selected(request('status') === 'processing')>Processing</option>
                @else
                    <option value="sent" @selected(request('status') === 'sent')>Terkirim (Sent)</option>
                    <option value="failed" @selected(request('status') === 'failed')>Gagal (Failed)</option>
                    <option value="queued" @selected(request('status') === 'queued')>Queued</option>
                @endif
            </select>

            <button type="submit" class="dcms-primary-button px-3.5 py-2 text-xs font-bold">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('broadcasts.index', ['tab' => $tab]) }}" class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">Reset</a>
            @endif
        </form>
    </div>
</div>

@if($tab !== 'all')
    <!-- TAB 1: REPORT PER BATCH (MANAGE BROADCAST - EXACT SCREENSHOT STYLE) -->
    <section class="dcms-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/90 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="w-12 px-4 py-3.5 text-center">
                            <input type="checkbox" disabled class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        </th>
                        <th class="px-4 py-3.5">
                            <div class="flex items-center gap-1.5 cursor-pointer select-none">
                                <span>Broadcast Title</span>
                                <span class="text-slate-400 text-[10px]">⇕</span>
                            </div>
                        </th>
                        <th class="px-4 py-3.5 text-center">Sent / Recipient</th>
                        <th class="px-4 py-3.5 text-center">Delivery Rate</th>
                        <th class="px-4 py-3.5">Send At</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($batches as $batch)
                    @php
                        $rate = (float) $batch->delivery_rate;
                        $rateColor = $rate >= 80 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : ($rate >= 50 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-rose-700 bg-rose-50 border-rose-200');
                    @endphp
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="px-4 py-4 text-center">
                            <input type="checkbox" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-start gap-3">
                                @if($batch->image_url)
                                    <a href="{{ $batch->image_url }}" target="_blank" rel="noopener" class="group shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-white p-0.5 hover:border-brand-500 transition w-10 h-10 text-center shadow-2xs" title="Lampiran Gambar">
                                        <img src="{{ $batch->image_url }}" alt="Flyer" class="h-full w-full rounded object-cover">
                                    </a>
                                @endif
                                <div>
                                    <a href="{{ route('broadcasts.batches.show', $batch) }}" class="font-bold text-purple-700 hover:text-purple-900 hover:underline text-sm uppercase tracking-wide">
                                        {{ $batch->title }}
                                    </a>
                                    <p class="mt-0.5 max-w-md line-clamp-1 text-xs text-slate-500">{{ Str::limit($batch->message, 80) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <span class="font-bold text-slate-800 text-sm">{{ $batch->total_sent }}</span>
                            <span class="text-slate-400 font-medium text-xs">/ {{ $batch->total_recipients }}</span>
                            @if($batch->total_failed > 0)
                                <div class="text-[10px] text-rose-500 font-medium mt-0.5">({{ $batch->total_failed }} gagal)</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $rateColor }}">
                                {{ $batch->delivery_rate }}%
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-600">
                            {{ $batch->sent_at?->format('D, d M Y H:i') ?: '-' }} WIB
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
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
                        </td>
                        <td class="px-4 py-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('broadcasts.batches.show', $batch) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50" title="Lihat detail penerima">
                                    <svg class="size-3.5 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Detail
                                </a>
                                <a href="{{ route('broadcasts.batches.download', $batch) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-emerald-700 shadow-2xs hover:bg-emerald-50" title="Download Excel batch ini">
                                    <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    Excel
                                </a>
                                <form method="POST" action="{{ route('broadcasts.batches.destroy', $batch) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmForm(this, { title: 'Hapus Batch Broadcast?', message: 'Seluruh riwayat pengiriman pesan pelanggan pada batch ini akan dihapus secara permanen dari laporan.', itemName: '{{ addslashes($batch->title) }} ({{ $batch->total_recipients }} pesan)' })" class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Hapus Batch">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center">
                            <div class="mx-auto max-w-sm text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-purple-50 text-purple-600 mb-3">
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900">Belum Ada Batch Broadcast</h3>
                                <p class="mt-1 text-xs text-slate-500">Mulai kirimkan broadcast WhatsApp dari menu Master Customer atau Leads Customer untuk mencatat batch pertama.</p>
                                <button type="button" onclick="openNewBroadcastModal()" class="mt-4 dcms-primary-button px-4 py-2 text-xs">
                                    + Buat Broadcast Baru
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <div class="mt-4">{{ $batches->links() }}</div>

@else
    <!-- TAB 2: SEMUA DETAIL PESAN PER PENERIMA (ORIGINAL TABLE VIEW) -->
    <section class="dcms-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-3 py-3.5">Batch / Campaign</th>
                        <th class="px-3 py-3.5">Lampiran</th>
                        <th class="px-3 py-3.5">Pesan</th>
                        <th class="px-3 py-3.5">Status</th>
                        <th class="px-3 py-3.5">Waktu</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($reports as $report)
                    <tr class="align-top transition hover:bg-slate-50/70">
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-900">{{ $report->customer_vendor_code ?: '-' }}</div>
                            <div class="mt-1 text-xs text-slate-500 font-mono">{{ $report->phone_number ?: 'No HP kosong' }}</div>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap">
                            @if($report->batch)
                                <a href="{{ route('broadcasts.batches.show', $report->batch) }}" class="font-bold text-xs text-purple-700 hover:underline">
                                    {{ $report->batch->title }}
                                </a>
                            @else
                                <span class="text-xs text-slate-400">Direct Broadcast</span>
                            @endif
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
                            <form method="POST" action="{{ route('broadcasts.destroy', $report) }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmForm(this, { title: 'Hapus Pesan Broadcast?', message: 'Laporan pengiriman pesan ke pelanggan ini akan dihapus dari sistem.', itemName: '{{ addslashes($report->customer_vendor_code) }} ({{ $report->phone_number }})' })" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-600 transition hover:border-red-300 hover:bg-red-50">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/></svg>
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">Belum ada laporan pesan broadcast.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <div class="mt-4">{{ $reports->links() }}</div>
@endif

<!-- Modal: + New Broadcast (Launcher) -->
<div id="new-broadcast-launcher-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm sm:p-6 md:p-10 flex items-center justify-center">
    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="flex size-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </div>
                <h3 class="font-black text-slate-900 text-base">Buat Broadcast Baru</h3>
            </div>
            <button type="button" onclick="closeNewBroadcastModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <p class="mt-3 text-xs text-slate-500 leading-relaxed">
            Pilih sumber daftar pelanggan yang ingin Anda targetkan untuk kampanye broadcast WhatsApp ini:
        </p>

        <div class="mt-5 space-y-3">
            <a href="{{ route('leads.index') }}" class="group flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-2xs transition hover:border-brand-500 hover:bg-brand-50/30">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-700">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900 group-hover:text-brand-700">Dari Leads Customer</div>
                        <div class="text-[11px] text-slate-500">Targetkan pelanggan yang sudah di-assign ke tim sales/staff</div>
                    </div>
                </div>
                <svg class="size-4 text-slate-400 group-hover:translate-x-1 group-hover:text-brand-600 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>

            <a href="{{ route('predictions.index') }}" class="group flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-2xs transition hover:border-emerald-500 hover:bg-emerald-50/30">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.25 2.25L18 6.75"/></svg>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900 group-hover:text-emerald-700">Dari Master Customer</div>
                        <div class="text-[11px] text-slate-500">Pilih dari seluruh database prediksi repeat order toko</div>
                    </div>
                </div>
                <svg class="size-4 text-slate-400 group-hover:translate-x-1 group-hover:text-emerald-600 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>
    </div>
</div>

<script>
function openNewBroadcastModal() {
    const modal = document.getElementById('new-broadcast-launcher-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeNewBroadcastModal() {
    const modal = document.getElementById('new-broadcast-launcher-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}
</script>

@endsection
