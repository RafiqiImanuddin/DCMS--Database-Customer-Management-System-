@extends('layouts.app')
@section('title', 'Leads Customer')
@section('content')
<div class="mb-5 flex items-end justify-between gap-4">
    <div><p class="text-sm font-medium text-brand-600">Follow Up</p><h1 class="mt-1 text-2xl font-black text-slate-900">Leads Customer</h1></div>
    <div class="flex flex-wrap items-center gap-2">
        <p class="hidden text-sm text-slate-500 md:block">{{ number_format($leads->total(), 0, ',', '.') }} lead</p>
        @if ($leads->total() <= 25000)
            <a href="{{ route('leads.download', request()->except('page')) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300">
                <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                Download Leads (Excel)
            </a>
        @else
            <details class="relative inline-block text-left">
                <summary class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-800 shadow-sm transition hover:bg-emerald-100 list-none">
                    <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    Download Leads ({{ number_format($leads->total(), 0, ',', '.') }} data)
                    <svg class="size-3.5 text-emerald-700" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                </summary>
                <div class="absolute right-0 z-30 mt-2 w-64 origin-top-right rounded-xl border border-slate-200 bg-white p-2 shadow-xl ring-1 ring-black/5 focus:outline-none">
                    <div class="px-2 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pilih Batch (Maks 25k data/file)</div>
                    @php($totalBatches = (int) ceil($leads->total() / 25000))
                    @for ($b = 1; $b <= $totalBatches; $b++)
                        @php($start = ($b - 1) * 25000 + 1)
                        @php($end = min($b * 25000, $leads->total()))
                        <a href="{{ route('leads.download', array_merge(request()->except(['page', 'batch']), ['batch' => $b])) }}" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition">
                            <span class="flex items-center gap-2">
                                <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                                Batch {{ $b }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ number_format($start, 0, ',', '.') }} - {{ number_format($end, 0, ',', '.') }}</span>
                        </a>
                    @endfor
                </div>
            </details>
        @endif
    </div>
</div>

<form method="GET" class="dcms-card mb-5 grid gap-3 p-4 md:grid-cols-[minmax(220px,1fr)_160px_160px_auto]">
    <input name="search" value="{{ request('search') }}" placeholder="Cari pelanggan, item, atau nomor HP" class="dcms-input py-2.5">
    <select name="classification" class="dcms-input py-2.5">
        <option value="">Semua klasifikasi</option>
        @foreach(['tinggi', 'sedang', 'rendah', 'baru'] as $value)
            <option value="{{ $value }}" @selected(request('classification') === $value)>{{ ucfirst($value) }}</option>
        @endforeach
    </select>
    <select name="phone_status" class="dcms-input py-2.5">
        <option value="">Semua WhatsApp</option>
        <option value="valid" @selected(request('phone_status') === 'valid')>WhatsApp valid</option>
        <option value="invalid" @selected(request('phone_status') === 'invalid')>Nomor tidak valid</option>
        <option value="missing" @selected(request('phone_status') === 'missing')>Tanpa nomor</option>
    </select>
    <button class="dcms-primary-button px-5">Filter</button>
</form>

<form method="POST" action="{{ route('broadcasts.bulk') }}" id="lead-broadcast-form">
    @csrf
    <section class="dcms-card mb-4 flex flex-col gap-3 p-4 lg:flex-row lg:items-end">
        <div class="flex min-w-0 flex-1 flex-col gap-2">
            <label for="lead-bulk-message" class="text-sm font-bold text-slate-700">Pesan broadcast terpilih</label>
            <textarea id="lead-bulk-message" name="message" required maxlength="2000" rows="2" class="dcms-input min-h-20 resize-y py-2.5" placeholder="Halo, kami punya promo khusus untuk Anda."></textarea>
        </div>
        <button id="lead-broadcast-button" type="submit" disabled class="dcms-primary-button shrink-0 px-5 py-3 disabled:cursor-not-allowed disabled:opacity-50">Broadcast <span id="lead-selected-count">(0)</span> lead</button>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/90 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5"><input id="select-all-leads" type="checkbox" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" aria-label="Pilih semua lead"></th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-3 py-3.5">No. HP Konsumen</th>
                        <th class="px-3 py-3.5">Item</th>
                        <th class="px-3 py-3.5">Klasifikasi</th>
                        <th class="px-3 py-3.5">Ditetapkan</th>
                        <th class="px-5 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($leads as $lead)
                    <tr class="border-b border-slate-100 align-middle transition hover:bg-brand-50/40">
                        <td class="px-5 py-4">
                            @if($lead->prediction)
                                @php($leadPhoneDigits = preg_replace('/[^0-9]/', '', (string) $lead->no_hp_customer) ?? '')
                                @php($leadPhoneValid = str_starts_with($leadPhoneDigits, '62') && strlen($leadPhoneDigits) >= 10 && strlen($leadPhoneDigits) <= 15)
                                <input type="checkbox" name="prediction_ids[]" value="{{ $lead->prediction->id }}" class="lead-checkbox size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" aria-label="Pilih {{ $lead->customer_vendor_name ?: 'Kaka' }}" @disabled(! $leadPhoneValid)>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $lead->customer_vendor_name ?: 'Kaka' }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $lead->customer_vendor_code }}</div>
                        </td>
                        <td class="px-3 py-4">
                            @if($lead->no_hp_customer)
                                @php($leadPhoneDigits = preg_replace('/[^0-9]/', '', (string) $lead->no_hp_customer) ?? '')
                                @php($leadPhoneValid = str_starts_with($leadPhoneDigits, '62') && strlen($leadPhoneDigits) >= 10 && strlen($leadPhoneDigits) <= 15)
                                @if($leadPhoneValid)
                                    <a href="https://wa.me/{{ $leadPhoneDigits }}" target="_blank" rel="noopener" class="font-semibold text-emerald-700 hover:text-emerald-800">{{ $lead->no_hp_customer }}</a>
                                    <div class="mt-1 text-[11px] font-bold text-emerald-600">WhatsApp valid</div>
                                @else
                                    <span class="font-semibold text-amber-700">{{ $lead->no_hp_customer }}</span>
                                    <div class="mt-1 text-[11px] font-bold text-amber-600">Nomor tidak valid</div>
                                @endif
                            @else
                                <span class="text-xs text-slate-400">No HP belum tersedia</span>
                                <div class="mt-1 text-[11px] font-bold text-slate-500">Tanpa nomor</div>
                            @endif
                        </td>
                        <td class="px-3 py-4"><div class="font-medium text-slate-800">{{ $lead->item_code ?: '-' }}</div><div class="mt-1 max-w-52 truncate text-xs text-slate-500">{{ $lead->dscription ?: '-' }}</div></td>
                        <td class="px-3 py-4"><span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold capitalize text-slate-700">{{ $lead->classification }}</span></td>
                        <td class="px-3 py-4 whitespace-nowrap text-slate-600">{{ $lead->assigned_at?->format('d M Y H:i') ?: '-' }}</td>
                        <td class="px-5 py-4"><span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Follow Up</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">Belum ada Leads Customer.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</form>
<div class="mt-4">{{ $leads->links() }}</div>
@endsection
