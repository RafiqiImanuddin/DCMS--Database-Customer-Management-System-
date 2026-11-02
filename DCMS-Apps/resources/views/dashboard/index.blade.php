@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @foreach([
        ['Transaksi', $totalTransactions, 'blue'],
        ['Pelanggan Potensial', $totalCustomers, 'indigo'],
        ['Prediksi', $totalPredictions, 'violet'],
        ['Potensi Tinggi', $highPotentials, 'emerald'],
        ['Broadcast Terkirim', $broadcastSent, 'amber'],
    ] as [$label, $value, $color])
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ $label }}</div>
            <div class="mt-2 text-3xl font-bold text-{{ $color }}-600">{{ number_format($value) }}</div>
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 font-bold">Periode Cross-selling</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b text-slate-500"><tr><th class="py-2">Periode</th><th>Transaksi</th><th>Pelanggan</th><th class="text-right">Nilai</th></tr></thead>
                <tbody>
                @foreach($periods as $period)
                    <tr class="border-b border-slate-100"><td class="py-3 font-semibold">{{ $period->period_label }}</td><td>{{ number_format($period->transaction_count) }}</td><td>{{ number_format($period->customer_count) }}</td><td class="text-right">{{ number_format((float) $period->total_value) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 font-bold">Prediksi Terdekat</h2>
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
