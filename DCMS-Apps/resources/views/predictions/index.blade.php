@extends('layouts.app')
@section('title', 'Prediksi Repeat Order')
@section('content')
<form method="GET" class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
    <input name="search" value="{{ request('search') }}" placeholder="Cari pelanggan atau item" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
    <select name="classification" class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><option value="">Semua klasifikasi</option>@foreach(['tinggi','sedang','monitor','baru'] as $value)<option value="{{ $value }}" @selected(request('classification') === $value)>{{ ucfirst($value) }}</option>@endforeach</select>
    <select name="period" class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><option value="">Semua periode</option>@foreach($periods as $period)<option value="{{ $period }}" @selected(request('period') === $period)>{{ $period }}</option>@endforeach</select>
    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
</form>
<section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">Pelanggan</th><th>Item</th><th>Transaksi</th><th>Terakhir</th><th>Prediksi</th><th>Jarak Rata-rata</th><th>Potensi</th><th>Broadcast</th></tr></thead>
            <tbody>
            @forelse($predictions as $prediction)
                <tr class="border-b border-slate-100 align-top">
                    <td class="px-5 py-3"><div class="font-semibold">{{ $prediction->customer_vendor_name ?: $prediction->customer_vendor_code }}</div><div class="text-xs text-slate-500">{{ $prediction->customer_vendor_code }} · {{ $prediction->no_hp_customer ?: 'No HP kosong' }}</div></td>
                    <td class="py-3"><div>{{ $prediction->item_code ?: '-' }}</div><div class="max-w-56 truncate text-xs text-slate-500">{{ $prediction->dscription }}</div></td>
                    <td class="py-3">{{ $prediction->transaction_count }}</td><td>{{ $prediction->last_order_date?->format('d M Y') }}</td><td class="font-semibold">{{ $prediction->predicted_repeat_date?->format('d M Y') ?: '-' }}</td><td>{{ $prediction->average_gap_days ? number_format((float) $prediction->average_gap_days, 0).' hari' : '-' }}</td>
                    <td><span class="rounded-full px-2 py-1 text-xs capitalize {{ $prediction->classification === 'tinggi' ? 'bg-emerald-100 text-emerald-700' : ($prediction->classification === 'sedang' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">{{ $prediction->classification }} · {{ $prediction->confidence }}%</span></td>
                    <td>
                        <form method="POST" action="{{ route('broadcasts.store', $prediction) }}" class="space-y-2">
                            @csrf
                            <textarea name="message" required maxlength="2000" rows="2" class="w-64 rounded-lg border border-slate-300 px-2 py-1 text-xs" placeholder="Pesan promo">{{ 'Halo '.$prediction->customer_vendor_name.', kami punya promo khusus untuk kebutuhan berikutnya.' }}</textarea>
                            <button class="rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white">Kirim/Lapor</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-5 py-8 text-center text-slate-500">Belum ada prediksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
<div class="mt-4">{{ $predictions->links() }}</div>
@endsection
