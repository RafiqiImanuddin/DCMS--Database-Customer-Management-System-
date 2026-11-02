@extends('layouts.app')
@section('title', 'Master Customer')
@section('content')
<div class="mb-5 flex items-end justify-between gap-4">
    <div><p class="text-sm font-medium text-brand-600">Customer Intelligence</p><h1 class="mt-1 text-2xl font-black text-slate-900">Master Customer</h1></div>
    <p class="hidden text-sm text-slate-500 sm:block">{{ $predictions->total() }} rekomendasi ditemukan</p>
</div>
<form method="GET" class="dcms-card mb-5 grid gap-3 p-4 lg:grid-cols-[minmax(220px,1fr)_170px_150px_150px_150px_auto]">
    <input name="search" value="{{ request('search') }}" placeholder="Cari pelanggan, item, atau nomor HP" class="dcms-input py-2.5">
    <select name="classification" class="dcms-input py-2.5">
        <option value="">Semua klasifikasi</option>
        @foreach(['tinggi','sedang','rendah','baru'] as $value)
            <option value="{{ $value }}" @selected(request('classification') === $value)>{{ ucfirst($value) }}</option>
        @endforeach
    </select>
    <select name="period" class="dcms-input py-2.5">
        <option value="">Semua periode</option>
        @php($monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'])
        @foreach($periods as $period)
            @php($monthNumber = (int) substr((string) $period, 5, 2))
            <option value="{{ $period }}" @selected(request('period') === $period)>{{ $monthNames[$monthNumber] ?? $period }}</option>
        @endforeach
    </select>
    <select name="brand" class="dcms-input py-2.5">
        <option value="">Semua brand</option>
        @foreach($brandOptions as $brandOption)
            <option value="{{ $brandOption }}" @selected($selectedBrand === $brandOption)>{{ $brandOption }}</option>
        @endforeach
    </select>
    <select name="phone_status" class="dcms-input py-2.5">
        <option value="">Semua WhatsApp</option>
        <option value="valid" @selected($selectedPhoneStatus === 'valid')>WhatsApp valid</option>
        <option value="invalid" @selected($selectedPhoneStatus === 'invalid')>Nomor tidak valid</option>
        <option value="missing" @selected($selectedPhoneStatus === 'missing')>Tanpa nomor</option>
    </select>
    <button class="dcms-primary-button px-5">Filter</button>
</form>
@php($potentialMax = max(1, (int) $potentialChart->max()))
<section class="dcms-card mb-5 p-5">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div><h2 class="font-black text-slate-900">Master Customer</h2><p class="mt-1 text-sm text-slate-500">Pelanggan yang belum dipindahkan ke Leads Customer.</p></div>
        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ number_format($predictions->total()) }} total</span>
    </div>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach(['tinggi' => 'bg-emerald-500', 'sedang' => 'bg-amber-500', 'rendah' => 'bg-sky-500', 'baru' => 'bg-slate-400'] as $classification => $color)
            @php($count = (int) ($potentialChart[$classification] ?? 0))
            @php($width = round(($count / $potentialMax) * 100, 1))
            @php($chartParams = array_filter([
                'classification' => request('classification') === $classification ? null : $classification,
                'search' => request('search'),
                'period' => request('period'),
                'brand' => request('brand'),
                'phone_status' => request('phone_status'),
            ], fn ($value) => filled($value)))
            <a href="{{ route('predictions.index', $chartParams) }}"
                class="block rounded-xl border p-4 transition hover:-translate-y-0.5 hover:border-brand-300 hover:bg-white hover:shadow-sm {{ request('classification') === $classification ? 'border-brand-400 bg-brand-50 ring-2 ring-brand-100' : 'border-slate-200 bg-slate-50/70' }}">
                <div class="flex items-center justify-between gap-2 text-sm"><span class="font-bold capitalize text-slate-700">{{ $classification }}</span><span class="font-black text-slate-900">{{ number_format($count) }}</span></div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-white"><div class="h-full rounded-full {{ $color }}" style="width: {{ $width }}%"></div></div>
            </a>
        @endforeach
    </div>
</section>
@php($phoneMax = max(1, ...array_values($phoneChart)))
<section class="dcms-card mb-5 p-5">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div><h2 class="font-black text-slate-900">Validasi WhatsApp</h2><p class="mt-1 text-sm text-slate-500">Data Master Customer berdasarkan nomor WhatsApp.</p></div>
    </div>
    <div class="grid gap-3 sm:grid-cols-3">
        @foreach([
            'valid' => ['label' => 'WhatsApp valid', 'color' => 'bg-emerald-500'],
            'invalid' => ['label' => 'Nomor tidak valid', 'color' => 'bg-amber-500'],
            'missing' => ['label' => 'Tanpa nomor', 'color' => 'bg-slate-400'],
        ] as $phoneStatus => $phoneMeta)
            @php($count = (int) ($phoneChart[$phoneStatus] ?? 0))
            @php($width = round(($count / $phoneMax) * 100, 1))
            @php($chartParams = array_filter([
                'phone_status' => request('phone_status') === $phoneStatus ? null : $phoneStatus,
                'classification' => request('classification'),
                'search' => request('search'),
                'period' => request('period'),
                'brand' => request('brand'),
            ], fn ($value) => filled($value)))
            <a href="{{ route('predictions.index', $chartParams) }}"
                class="block rounded-xl border p-4 transition hover:-translate-y-0.5 hover:border-brand-300 hover:bg-white hover:shadow-sm {{ request('phone_status') === $phoneStatus ? 'border-brand-400 bg-brand-50 ring-2 ring-brand-100' : 'border-slate-200 bg-slate-50/70' }}">
                <div class="flex items-center justify-between gap-2 text-sm"><span class="font-bold text-slate-700">{{ $phoneMeta['label'] }}</span><span class="font-black text-slate-900">{{ number_format($count) }}</span></div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-white"><div class="h-full rounded-full {{ $phoneMeta['color'] }}" style="width: {{ $width }}%"></div></div>
            </a>
        @endforeach
    </div>
</section>
<form method="POST" action="{{ route('predictions.assign') }}" id="bulk-broadcast-form">
    @csrf
    <section class="dcms-card mb-4 flex flex-col gap-3 p-4 lg:flex-row lg:items-end">
        <div class="flex min-w-0 flex-1 flex-col gap-2">
            <p class="text-sm font-bold text-slate-700">Pilih pelanggan untuk follow up</p>
            <p class="text-xs text-slate-500">Pelanggan yang dipilih akan berpindah ke halaman Leads Customer.</p>
        </div>
        <button id="bulk-broadcast-button" type="submit" disabled class="dcms-primary-button shrink-0 px-5 py-3 disabled:cursor-not-allowed disabled:opacity-50">Assign <span id="selected-count">(0)</span> pelanggan</button>
    </section>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1080px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50/90 text-xs font-bold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3.5"><input id="select-all-predictions" type="checkbox" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" aria-label="Pilih semua pelanggan"></th>
                    <th class="px-5 py-3.5">Pelanggan</th>
                    <th class="px-3 py-3.5">No. HP Konsumen</th>
                    <th class="px-3 py-3.5">Item</th>
                    <th class="px-3 py-3.5 text-center">Transaksi</th>
                    <th class="px-3 py-3.5">Terakhir</th>
                    <th class="px-3 py-3.5">Prediksi</th>
                    <th class="px-3 py-3.5">Jarak</th>
                    <th class="px-3 py-3.5">Potensi</th>
                    <th class="px-5 py-3.5">Broadcast</th>
                </tr>
            </thead>
            <tbody>
            @forelse($predictions as $prediction)
                <tr class="border-b border-slate-100 align-middle transition hover:bg-brand-50/40">
                    <td class="px-5 py-4"><input type="checkbox" name="prediction_ids[]" value="{{ $prediction->id }}" class="prediction-checkbox size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" aria-label="Pilih {{ $prediction->customer_vendor_name ?: 'Kaka' }}"></td>
                    <td class="px-5 py-4">
                        <div class="max-w-44 truncate font-semibold text-slate-900">{{ $prediction->customer_vendor_name ?: 'Kaka' }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $prediction->customer_vendor_code }}{{ $prediction->cabang ? ' · '.$prediction->cabang : '' }}</div>
                    </td>
                    <td class="px-3 py-4">
                        @if($prediction->no_hp_customer)
                            @php($phoneDigits = preg_replace('/[^0-9]/', '', $prediction->no_hp_customer) ?? '')
                            @php($phoneValid = str_starts_with($phoneDigits, '62') && strlen($phoneDigits) >= 10 && strlen($phoneDigits) <= 15)
                            @if($phoneValid)
                            <a href="https://wa.me/{{ $phoneDigits }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 font-semibold text-emerald-700 hover:text-emerald-800">
                                <span>{{ $prediction->no_hp_customer }}</span>
                            </a>
                            <div class="mt-1 text-[11px] font-bold text-emerald-600">WhatsApp valid</div>
                            @else
                            <span class="font-semibold text-amber-700">{{ $prediction->no_hp_customer }}</span>
                            <div class="mt-1 text-[11px] font-bold text-amber-600">Nomor tidak valid</div>
                            @endif
                        @else
                            <span class="text-xs text-slate-400">No HP belum tersedia</span>
                            <div class="mt-1 text-[11px] font-bold text-slate-500">Tanpa nomor</div>
                        @endif
                    </td>
                    <td class="px-3 py-4">
                        <div class="font-medium text-slate-800">{{ $prediction->item_code ?: '-' }}</div>
                        <div class="mt-1 max-w-52 truncate text-xs text-slate-500" title="{{ $prediction->dscription }}">{{ $prediction->dscription ?: '-' }}</div>
                    </td>
                    <td class="px-3 py-4 text-center font-semibold text-slate-700">{{ $prediction->transaction_count }}</td>
                    <td class="px-3 py-4 whitespace-nowrap">{{ $prediction->last_order_date?->format('d M Y') ?: '-' }}</td>
                    <td class="px-3 py-4 whitespace-nowrap font-semibold text-brand-700">{{ $prediction->predicted_repeat_date?->format('d M Y') ?: '-' }}</td>
                    <td class="px-3 py-4 whitespace-nowrap">{{ $prediction->average_gap_days ? number_format((float) $prediction->average_gap_days, 0).' hari' : '-' }}</td>
                    <td class="px-3 py-4">
                        <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ $prediction->classification === 'tinggi' ? 'bg-emerald-100 text-emerald-700' : ($prediction->classification === 'sedang' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">{{ $prediction->classification }} · {{ $prediction->confidence }}%</span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-xs font-semibold text-slate-500">Pilih dan broadcast</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="px-5 py-12 text-center text-slate-500">Belum ada prediksi yang sesuai.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
</form>
<div class="mt-4">{{ $predictions->links() }}</div>
@endsection
