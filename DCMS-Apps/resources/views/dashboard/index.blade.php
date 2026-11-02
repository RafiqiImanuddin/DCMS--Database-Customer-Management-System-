@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="mb-5">
    <p class="text-sm font-medium text-brand-600">Ringkasan Bisnis</p>
    <h1 class="mt-1 text-2xl font-black text-slate-900">Selamat datang di DCMS Gadgetmart</h1>
</div>
<form method="GET" action="{{ route('dashboard') }}" class="dcms-card mb-5 flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
    <div class="flex-1">
        <label for="brand-filter" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Filter Brand</label>
        <select id="brand-filter" name="brand" class="dcms-input py-2.5">
            <option value="">Semua Brand</option>
            @foreach($brandOptions as $brandOption)
                <option value="{{ $brandOption }}" @selected($selectedBrand === $brandOption)>{{ $brandOption }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="dcms-primary-button px-5 py-2.5">Terapkan</button>
        @if($selectedBrand !== '')
            <a href="{{ route('dashboard') }}" class="dcms-secondary-button px-4 py-2.5">Reset</a>
        @endif
    </div>
</form>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @foreach([
        ['Transaksi', $totalTransactions, 'bg-blue-50 text-blue-700'],
        ['Pelanggan Potensial', $totalCustomers, 'bg-indigo-50 text-indigo-700'],
        ['Prediksi', $totalPredictions, 'bg-violet-50 text-violet-700'],
        ['Potensi Tinggi', $highPotentials, 'bg-emerald-50 text-emerald-700'],
        ['Broadcast Terkirim', $broadcastSent, 'bg-amber-50 text-amber-700'],
    ] as [$label, $value, $classes])
        <div class="dcms-card p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
            <div class="grid size-10 place-items-center rounded-xl {{ $classes }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V9m5 10V5m5 14v-7m5 7V3"/></svg>
            </div>
            <div class="mt-4 text-sm font-semibold text-slate-500">{{ $label }}</div>
            <div class="mt-1 text-3xl font-black text-slate-900">{{ number_format($value) }}</div>
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    @php($potentialMax = max(1, (int) $potentialChart->max()))
    <section class="dcms-card p-5">
        <div class="mb-5 flex items-start justify-between gap-3">
            <div><h2 class="font-black text-slate-900">Pelanggan Potensial</h2><p class="mt-1 text-sm text-slate-500">Distribusi berdasarkan klasifikasi prediksi.</p></div>
            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ number_format($totalPredictions) }} data</span>
        </div>
        <div class="space-y-4">
            @foreach(['tinggi' => 'bg-emerald-500', 'sedang' => 'bg-amber-500', 'rendah' => 'bg-sky-500', 'baru' => 'bg-slate-400'] as $classification => $color)
                @php($count = (int) ($potentialChart[$classification] ?? 0))
                @php($width = round(($count / $potentialMax) * 100, 1))
                <a href="{{ route('predictions.index', ['classification' => $classification]) }}" class="group block rounded-lg transition hover:bg-slate-50">
                    <div class="mb-1.5 flex justify-between gap-3 text-sm"><span class="font-semibold capitalize text-slate-700">{{ $classification }}</span><span class="font-black text-slate-900">{{ number_format($count) }}</span></div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $color }}" style="width: {{ $width }}%"></div></div>
                </a>
            @endforeach
        </div>
    </section>
    <section class="dcms-card p-5 lg:col-span-2">
        <div class="mb-5 flex items-start justify-between gap-3">
            <div>
                <h2 class="font-black text-slate-900">Brand Terlaris</h2>
                <p class="mt-1 text-sm text-slate-500">Jumlah unit terjual berdasarkan data brand.</p>
            </div>
            @if($selectedBrand !== '')
                <span class="shrink-0 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700">{{ $selectedBrand }}</span>
            @else
                <span class="shrink-0 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700">Per Brand</span>
            @endif
        </div>
        @php($brandMax = max(1, (int) ($brandSales->max('quantity') ?? 0)))
        <div class="grid gap-x-8 gap-y-3 md:grid-cols-2">
            @forelse($brandSales as $brandSale)
                @php($barWidth = $brandSale['quantity'] > 0 ? max(4, round(($brandSale['quantity'] / $brandMax) * 100, 1)) : 0)
                @php($brandParams = $selectedBrand === $brandSale['brand'] ? [] : ['brand' => $brandSale['brand']])
                <a href="{{ route('dashboard', $brandParams) }}" class="rounded-lg transition hover:bg-slate-50">
                    <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                        <span class="font-semibold text-slate-700">{{ $brandSale['brand'] }}</span>
                        <span class="shrink-0 font-black text-slate-900">{{ number_format($brandSale['quantity']) }} unit</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full {{ $brandSale['brand'] === 'LAINNYA' ? 'bg-slate-400' : 'bg-brand-600' }}" style="width: {{ $barWidth }}%"></div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-500 md:col-span-2">Belum ada data penjualan brand.</p>
            @endforelse
        </div>
    </section>
    <section class="dcms-card p-5">
        <div class="mb-4 flex items-center justify-between"><h2 class="font-black text-slate-900">Periode Cross-selling</h2><span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700">Ringkasan</span></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500"><tr><th class="py-2.5">Periode</th><th>Transaksi</th><th>Pelanggan</th><th class="text-right">Nilai</th></tr></thead>
                <tbody>
                @php($monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'])
                @foreach($periods as $period)
                    @php($monthNumber = (int) substr((string) $period->period_label, 5, 2))
                    <tr class="border-b border-slate-100 last:border-0"><td class="py-3 font-semibold">{{ $monthNames[$monthNumber] ?? $period->period_label }}</td><td>{{ number_format($period->transaction_count) }}</td><td>{{ number_format($period->customer_count) }}</td><td class="text-right font-semibold">{{ number_format((float) $period->total_value) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
    <section class="dcms-card p-5">
        <div class="mb-4 flex items-center justify-between"><h2 class="font-black text-slate-900">Prediksi Terdekat</h2><a href="{{ route('predictions.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Lihat semua</a></div>
        <div class="space-y-3">
            @forelse($topPredictions as $prediction)
                <div class="rounded-lg border border-slate-200 p-3">
                    <div class="flex justify-between gap-2"><div><div class="font-semibold">{{ $prediction->customer_vendor_name ?: $prediction->customer_vendor_code }}</div><div class="text-xs text-slate-500">{{ $prediction->item_code }}</div></div><div class="text-right"><div class="font-bold text-blue-700">{{ $prediction->predicted_repeat_date?->format('d M Y') }}</div><div class="text-xs capitalize">{{ $prediction->classification }}</div></div></div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada prediksi.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
