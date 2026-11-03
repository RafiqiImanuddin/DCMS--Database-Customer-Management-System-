@extends('layouts.app')
@section('title', 'Master Customer')
@section('content')
<!-- Header Halaman -->
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-500/10 px-2.5 py-0.5 text-xs font-bold text-brand-700">
                <span class="size-2 rounded-full bg-brand-500"></span>
                Master Customer & Repeat Order Intelligence
            </span>
            <span class="text-xs text-slate-400">&bull;</span>
            <span class="text-xs font-medium text-slate-500">Database Konsumen & Retensi</span>
        </div>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Master Customer</h1>
        <p class="mt-1 text-xs text-slate-500">Analisis menyeluruh loyalitas konsumen repeat order, prediksi jadwal beli kembali, dan follow up berkala.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('broadcasts.templates.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300">
            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            Template Pesan
        </a>
        <p class="hidden text-sm text-slate-500 md:block">{{ number_format($predictions->total(), 0, ',', '.') }} data</p>
        @if ($predictions->total() <= 25000)
            <a href="{{ route('predictions.download', request()->except('page')) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300">
                <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                Download Excel (.xlsx)
            </a>
        @else
            <details class="relative inline-block text-left">
                <summary class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-800 shadow-sm transition hover:bg-emerald-100 list-none">
                    <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    Download Excel ({{ number_format($predictions->total(), 0, ',', '.') }} data)
                    <svg class="size-3.5 text-emerald-700" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                </summary>
                <div class="absolute right-0 z-30 mt-2 w-64 origin-top-right rounded-xl border border-slate-200 bg-white p-2 shadow-xl ring-1 ring-black/5 focus:outline-none">
                    <div class="px-2 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pilih Batch (Maks 25k data/file)</div>
                    @php
                        $totalBatches = (int) ceil($predictions->total() / 25000);
                    @endphp
                    @for ($b = 1; $b <= $totalBatches; $b++)
                        @php
                            $start = ($b - 1) * 25000 + 1;
                            $end = min($b * 25000, $predictions->total());
                        @endphp
                        <a href="{{ route('predictions.download', array_merge(request()->except(['page', 'batch']), ['batch' => $b])) }}" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition">
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

<!-- ======================================================== -->
<!-- RINGKASAN METRIK REPEAT ORDER (5 KPI CARDS)               -->
<!-- ======================================================== -->
<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
    <!-- Card 1: Total Konsumen Repeat Order -->
    <div class="dcms-card p-5 border-l-4 border-l-brand-600">
        <div class="flex items-center justify-between text-slate-500">
            <span class="text-xs font-bold uppercase tracking-wider">Konsumen Repeat</span>
            <div class="grid size-8 place-items-center rounded-lg bg-brand-50 text-brand-600">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10 2v6m3-3h-6"/></svg>
            </div>
        </div>
        <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($totalRepeatCustomers) }}</div>
        <div class="mt-1 flex items-center gap-1 text-[11px] text-slate-500">
            <span>Retensi:</span>
            <span class="font-bold text-brand-700">{{ $repeatOrderRate }}%</span>
            <span>dari {{ number_format($totalAllCustomers) }} customer</span>
        </div>
    </div>

    <!-- Card 2: Segera / Lewat Jadwal Repeat Order -->
    <div class="dcms-card p-5 border-l-4 border-l-amber-500">
        <div class="flex items-center justify-between text-slate-500">
            <span class="text-xs font-bold uppercase tracking-wider">Jatuh Tempo Prediksi</span>
            <div class="grid size-8 place-items-center rounded-lg bg-amber-50 text-amber-600">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <div class="mt-2 text-2xl font-black text-amber-600">{{ number_format($overdueCount + $dueNext30DaysCount) }}</div>
        <div class="mt-1 text-[11px] text-slate-500 truncate">
            <span class="font-bold text-rose-600">{{ number_format($overdueCount) }}</span> terlewat &bull; <span class="font-bold text-amber-600">{{ number_format($dueNext30DaysCount) }}</span> &le;30 hari
        </div>
    </div>

    <!-- Card 3: Rata-Rata Siklus (Gap Days) -->
    <div class="dcms-card p-5 border-l-4 border-l-blue-500">
        <div class="flex items-center justify-between text-slate-500">
            <span class="text-xs font-bold uppercase tracking-wider">Siklus Beli Rata-rata</span>
            <div class="grid size-8 place-items-center rounded-lg bg-blue-50 text-blue-600">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
        </div>
        <div class="mt-2 text-2xl font-black text-slate-900">{{ $avgGapDays }} <span class="text-sm font-semibold text-slate-500">Hari</span></div>
        <div class="mt-1 text-[11px] text-slate-500">Rata-rata jeda antar transaksi</div>
    </div>

    <!-- Card 4: WhatsApp Siap Broadcast -->
    <div class="dcms-card p-5 border-l-4 border-l-emerald-500">
        <div class="flex items-center justify-between text-slate-500">
            <span class="text-xs font-bold uppercase tracking-wider">WA Siap Broadcast</span>
            <div class="grid size-8 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a4 4 0 0 0 5 5"/></svg>
            </div>
        </div>
        <div class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($validPhoneCount) }}</div>
        <div class="mt-1 text-[11px] text-slate-500">Kontak valid terverifikasi</div>
    </div>

    <!-- Card 5: Potensi Tinggi (High Potential) -->
    <div class="dcms-card p-5 border-l-4 border-l-purple-500">
        <div class="flex items-center justify-between text-slate-500">
            <span class="text-xs font-bold uppercase tracking-wider">Potensi Tinggi</span>
            <div class="grid size-8 place-items-center rounded-lg bg-purple-50 text-purple-600">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
        </div>
        <div class="mt-2 text-2xl font-black text-purple-700">{{ number_format($highPotentialCount) }}</div>
        <div class="mt-1 text-[11px] text-slate-500 truncate" title="Rp {{ number_format($totalEstimatedValue, 0, ',', '.') }}">
            Estimasi: Rp {{ number_format($totalEstimatedValue / 1000000, 1) }} Jt
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- ANALISIS SEGMENTASI & DISTRIBUSI (3 KOLOM KARTU)         -->
<!-- ======================================================== -->
<div class="mb-6 grid gap-6 lg:grid-cols-3">
    <!-- Segmen 1: Frekuensi Transaksi Pelanggan (Loyalitas) -->
    <section class="dcms-card p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-black text-slate-900">Segmen Loyalitas (Frekuensi Beli)</h3>
                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">Transaksi</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Distribusi konsumen berdasarkan seberapa sering mereka berbelanja kembali.</p>

            <div class="mt-4 space-y-3">
                @php
                    $p2 = $totalRepeatOrders > 0 ? round(($freq2 / $totalRepeatOrders) * 100, 1) : 0;
                    $p3to5 = $totalRepeatOrders > 0 ? round(($freq3to5 / $totalRepeatOrders) * 100, 1) : 0;
                    $pAbove5 = $totalRepeatOrders > 0 ? round(($freqAbove5 / $totalRepeatOrders) * 100, 1) : 0;
                @endphp
                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-700">
                        <span>2x Transaksi (Returning Buyer)</span>
                        <span>{{ number_format($freq2) }} ({{ $p2 }}%)</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-brand-500 rounded-full" style="width: {{ $p2 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-700">
                        <span>3 - 5x Transaksi (Loyal Customer)</span>
                        <span>{{ number_format($freq3to5) }} ({{ $p3to5 }}%)</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $p3to5 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-700">
                        <span>> 5x Transaksi (VIP Champion)</span>
                        <span>{{ number_format($freqAbove5) }} ({{ $pAbove5 }}%)</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-purple-500 rounded-full" style="width: {{ $pAbove5 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
            Pelanggan dengan &ge;3 transaksi memiliki tingkat retensi paling tinggi.
        </div>
    </section>

    <!-- Segmen 2: Distribusi Siklus Hari Pembelian (Gap Cycle) -->
    <section class="dcms-card p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-black text-slate-900">Siklus Waktu Repeat Order</h3>
                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">Jeda Hari</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Berapa lama waktu yang dibutuhkan konsumen sebelum melakukan repeat order.</p>

            <div class="mt-4 space-y-3">
                @php
                    $pgUnder30 = $totalRepeatOrders > 0 ? round(($gapUnder30 / $totalRepeatOrders) * 100, 1) : 0;
                    $pg30to60 = $totalRepeatOrders > 0 ? round(($gap30to60 / $totalRepeatOrders) * 100, 1) : 0;
                    $pg60to90 = $totalRepeatOrders > 0 ? round(($gap60to90 / $totalRepeatOrders) * 100, 1) : 0;
                    $pgAbove90 = $totalRepeatOrders > 0 ? round(($gapAbove90 / $totalRepeatOrders) * 100, 1) : 0;
                @endphp
                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-700">
                        <span>Cepat (&lt; 30 Hari)</span>
                        <span>{{ number_format($gapUnder30) }} ({{ $pgUnder30 }}%)</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pgUnder30 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-700">
                        <span>Sedang (30 - 60 Hari)</span>
                        <span>{{ number_format($gap30to60) }} ({{ $pg30to60 }}%)</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ $pg30to60 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-700">
                        <span>Standar (60 - 90 Hari)</span>
                        <span>{{ number_format($gap60to90) }} ({{ $pg60to90 }}%)</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-amber-500 rounded-full" style="width: {{ $pg60to90 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-700">
                        <span>Panjang (&gt; 90 Hari)</span>
                        <span>{{ number_format($gapAbove90) }} ({{ $pgAbove90 }}%)</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-slate-400 rounded-full" style="width: {{ $pgAbove90 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
            Kategori cepat (&lt;30 hari) didominasi aksesoris & perlengkapan pendukung.
        </div>
    </section>

    <!-- Segmen 3: Kategori Produk Repeat Order -->
    <section class="dcms-card p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-black text-slate-900">Kategori Produk Repeat Order</h3>
                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">Produk</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Perbandingan produk yang paling sering dibeli kembali oleh konsumen.</p>

            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-2.5">
                    <div class="flex items-center gap-2.5">
                        <div class="grid size-8 place-items-center rounded-lg bg-blue-100 text-blue-700 font-bold text-xs">📱</div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Smartphone</div>
                            <div class="text-[10px] text-slate-500">Unit Handphone & Gadget Utama</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-black text-slate-900">{{ number_format($smartphoneCount) }}</div>
                        <div class="text-[10px] font-semibold text-slate-400">{{ $totalRepeatOrders > 0 ? round(($smartphoneCount/$totalRepeatOrders)*100, 1) : 0 }}%</div>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-2.5">
                    <div class="flex items-center gap-2.5">
                        <div class="grid size-8 place-items-center rounded-lg bg-amber-100 text-amber-700 font-bold text-xs">🎧</div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Aksesoris (ACC)</div>
                            <div class="text-[10px] text-slate-500">Charger, Case, TWS, Glass</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-black text-slate-900">{{ number_format($accCount) }}</div>
                        <div class="text-[10px] font-semibold text-slate-400">{{ $totalRepeatOrders > 0 ? round(($accCount/$totalRepeatOrders)*100, 1) : 0 }}%</div>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-2.5">
                    <div class="flex items-center gap-2.5">
                        <div class="grid size-8 place-items-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">📦</div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Lainnya</div>
                            <div class="text-[10px] text-slate-500">Voucher, Jasa & Produk Lain</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-black text-slate-900">{{ number_format($otherCategoryCount) }}</div>
                        <div class="text-[10px] font-semibold text-slate-400">{{ $totalRepeatOrders > 0 ? round(($otherCategoryCount/$totalRepeatOrders)*100, 1) : 0 }}%</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
            Sesuai aturan sistem: Seluruh kode item bertanda ACC dikategorikan sebagai Lainnya.
        </div>
    </section>
</div>

<!-- ======================================================== -->
<!-- FORM PENCARIAN & FILTER TERPADU                          -->
<!-- ======================================================== -->
<form method="GET" class="dcms-card mb-5 grid gap-3 p-4 lg:grid-cols-[minmax(200px,1fr)_160px_160px_140px_140px_140px_auto]">
    <input name="search" value="{{ request('search') }}" placeholder="Cari pelanggan, item, atau HP" class="dcms-input py-2.5 text-xs">
    
    <select name="classification" class="dcms-input py-2.5 text-xs">
        <option value="">Semua klasifikasi</option>
        @foreach(['tinggi', 'sedang', 'rendah', 'baru'] as $value)
            <option value="{{ $value }}" @selected(request('classification') === $value)>{{ ucfirst($value) }}</option>
        @endforeach
    </select>

    <select name="period" class="dcms-input py-2.5 text-xs">
        <option value="">Semua jadwal / periode</option>
        <option value="overdue" @selected(request('period') === 'overdue')>⚠️ Terlewat (Overdue)</option>
        <option value="this_month" @selected(request('period') === 'this_month')>📅 Bulan Ini</option>
        <option value="next_30_days" @selected(request('period') === 'next_30_days')>⏱️ 30 Hari ke Depan</option>
        @php
            $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        @endphp
        @foreach($periods as $period)
            @php
                $monthNumber = (int) substr((string) $period, 5, 2);
            @endphp
            <option value="{{ $period }}" @selected(request('period') === $period)>{{ $monthNames[$monthNumber] ?? $period }}</option>
        @endforeach
    </select>

    <select name="category" class="dcms-input py-2.5 text-xs">
        <option value="">Semua kategori</option>
        <option value="SMARTPHONE" @selected(request('category') === 'SMARTPHONE')>📱 Smartphone</option>
        <option value="ACC" @selected(request('category') === 'ACC')>🎧 Aksesoris (ACC)</option>
        <option value="LAINNYA" @selected(request('category') === 'LAINNYA')>📦 Lainnya</option>
    </select>

    <select name="brand" class="dcms-input py-2.5 text-xs">
        <option value="">Semua brand</option>
        @foreach($brandOptions as $brandOption)
            <option value="{{ $brandOption }}" @selected($selectedBrand === $brandOption)>{{ $brandOption }}</option>
        @endforeach
    </select>

    <select name="phone_status" class="dcms-input py-2.5 text-xs">
        <option value="">Semua WhatsApp</option>
        <option value="valid" @selected($selectedPhoneStatus === 'valid')>WhatsApp valid</option>
        <option value="invalid" @selected($selectedPhoneStatus === 'invalid')>Nomor tidak valid</option>
        <option value="missing" @selected($selectedPhoneStatus === 'missing')>Tanpa nomor</option>
    </select>

    <button class="dcms-primary-button px-5 text-xs font-bold">Filter</button>
</form>

<!-- Visualisasi Filter Cepat: Potensi & WhatsApp -->
<div class="mb-5 grid gap-4 lg:grid-cols-2">
    <!-- Klasifikasi Potensi -->
    @php
        $potentialMax = max(1, (int) $potentialChart->max());
    @endphp
    <section class="dcms-card p-4">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wide text-slate-700">Filter Potensi Repeat Order</h2>
            <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ number_format($predictions->total()) }} data</span>
        </div>
        <div class="grid gap-2 grid-cols-2 sm:grid-cols-4">
            @foreach(['tinggi' => 'bg-emerald-500', 'sedang' => 'bg-amber-500', 'rendah' => 'bg-sky-500', 'baru' => 'bg-slate-400'] as $classification => $color)
                @php
                    $count = (int) ($potentialChart[$classification] ?? 0);
                    $width = round(($count / $potentialMax) * 100, 1);
                    $chartParams = array_filter([
                        'classification' => request('classification') === $classification ? null : $classification,
                        'search' => request('search'),
                        'period' => request('period'),
                        'category' => request('category'),
                        'brand' => request('brand'),
                        'phone_status' => request('phone_status'),
                    ], fn ($value) => filled($value));
                @endphp
                <a href="{{ route('predictions.index', $chartParams) }}"
                    class="block rounded-lg border p-2.5 transition hover:border-brand-300 hover:bg-white {{ request('classification') === $classification ? 'border-brand-400 bg-brand-50 ring-2 ring-brand-100' : 'border-slate-200 bg-slate-50/70' }}">
                    <div class="flex items-center justify-between text-xs"><span class="font-bold capitalize text-slate-700">{{ $classification }}</span><span class="font-black text-slate-900">{{ number_format($count) }}</span></div>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white"><div class="h-full rounded-full {{ $color }}" style="width: {{ $width }}%"></div></div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Validasi WhatsApp -->
    @php
        $phoneMax = max(1, ...array_values($phoneChart));
    @endphp
    <section class="dcms-card p-4">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wide text-slate-700">Status Nomor WhatsApp</h2>
            <span class="text-[10px] text-slate-400">Klik untuk filter cepat</span>
        </div>
        <div class="grid gap-2 grid-cols-3">
            @foreach([
                'valid' => ['label' => 'WA Valid', 'color' => 'bg-emerald-500'],
                'invalid' => ['label' => 'Tidak Valid', 'color' => 'bg-amber-500'],
                'missing' => ['label' => 'Tanpa Nomor', 'color' => 'bg-slate-400'],
            ] as $phoneStatus => $phoneMeta)
                @php
                    $count = (int) ($phoneChart[$phoneStatus] ?? 0);
                    $width = round(($count / $phoneMax) * 100, 1);
                    $chartParams = array_filter([
                        'phone_status' => request('phone_status') === $phoneStatus ? null : $phoneStatus,
                        'classification' => request('classification'),
                        'search' => request('search'),
                        'period' => request('period'),
                        'category' => request('category'),
                        'brand' => request('brand'),
                    ], fn ($value) => filled($value));
                @endphp
                <a href="{{ route('predictions.index', $chartParams) }}"
                    class="block rounded-lg border p-2.5 transition hover:border-brand-300 hover:bg-white {{ request('phone_status') === $phoneStatus ? 'border-brand-400 bg-brand-50 ring-2 ring-brand-100' : 'border-slate-200 bg-slate-50/70' }}">
                    <div class="flex items-center justify-between text-xs"><span class="font-bold text-slate-700">{{ $phoneMeta['label'] }}</span><span class="font-black text-slate-900">{{ number_format($count) }}</span></div>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white"><div class="h-full rounded-full {{ $phoneMeta['color'] }}" style="width: {{ $width }}%"></div></div>
                </a>
            @endforeach
        </div>
    </section>
</div>

<!-- ======================================================== -->
<!-- FORM AKSI FOLLOW UP (ASSIGN KE LEADS / QUICK BROADCAST)   -->
<!-- ======================================================== -->
<form method="POST" action="{{ route('predictions.assign') }}" id="bulk-broadcast-form">
    @csrf
    <section class="dcms-card mb-4 flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 flex-1 flex-col">
            <p class="text-sm font-bold text-slate-800">Pilih Konsumen untuk Tindak Lanjut</p>
            <p class="text-xs text-slate-500">Pindahkan data konsumen yang dicentang ke menu Leads Customer atau kirim broadcast langsung.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button id="open-quick-broadcast-modal-btn" type="button" disabled onclick="openQuickBroadcastModal()" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-xs font-bold text-emerald-800 shadow-sm transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50">
                <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a4 4 0 0 0 5 5"/></svg>
                Broadcast WhatsApp <span class="selected-count-badge">(0)</span>
            </button>
            <button id="bulk-broadcast-button" type="submit" disabled class="dcms-primary-button px-5 py-2.5 text-xs font-bold disabled:cursor-not-allowed disabled:opacity-50">
                Assign ke Leads <span class="selected-count-badge">(0)</span>
            </button>
        </div>
    </section>

    <!-- Tabel Data Master Customer -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/90 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5"><input id="select-all-predictions" type="checkbox" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" aria-label="Pilih semua pelanggan"></th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-3 py-3.5">No. HP Konsumen</th>
                        <th class="px-3 py-3.5">Item & Kategori</th>
                        <th class="px-3 py-3.5 text-center">Transaksi</th>
                        <th class="px-3 py-3.5">Order Terakhir</th>
                        <th class="px-3 py-3.5">Prediksi Beli</th>
                        <th class="px-3 py-3.5">Siklus</th>
                        <th class="px-3 py-3.5">Potensi</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($predictions as $prediction)
                    @php
                        $isOverdue = $prediction->predicted_repeat_date && $prediction->predicted_repeat_date->toDateString() < $today;
                    @endphp
                    <tr class="border-b border-slate-100 align-middle transition hover:bg-brand-50/40">
                        <td class="px-5 py-4">
                            <input type="checkbox" name="prediction_ids[]" value="{{ $prediction->id }}" class="prediction-checkbox size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" aria-label="Pilih {{ $prediction->customer_vendor_name ?: 'Kaka' }}">
                        </td>
                        <td class="px-5 py-4">
                            <div class="max-w-48 truncate font-bold text-slate-900">{{ $prediction->customer_vendor_name ?: 'Kaka' }}</div>
                            <div class="mt-1 text-xs text-slate-500 font-mono">{{ $prediction->customer_vendor_code }}{{ $prediction->cabang ? ' · '.$prediction->cabang : '' }}</div>
                        </td>
                        <td class="px-3 py-4">
                            @if($prediction->no_hp_customer)
                                @php
                                    $phoneDigits = preg_replace('/[^0-9]/', '', (string) $prediction->no_hp_customer) ?? '';
                                    $phoneValid = str_starts_with($phoneDigits, '62') && strlen($phoneDigits) >= 10 && strlen($phoneDigits) <= 15;
                                @endphp
                                @if($phoneValid)
                                    <a href="https://wa.me/{{ $phoneDigits }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 font-semibold text-emerald-700 hover:text-emerald-800">
                                        <span>{{ $prediction->no_hp_customer }}</span>
                                    </a>
                                    <div class="mt-0.5 text-[10px] font-bold text-emerald-600 flex items-center gap-1">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span> WhatsApp Valid
                                    </div>
                                @else
                                    <span class="font-semibold text-amber-700">{{ $prediction->no_hp_customer }}</span>
                                    <div class="mt-0.5 text-[10px] font-bold text-amber-600 flex items-center gap-1">
                                        <span class="size-1.5 rounded-full bg-amber-500"></span> Nomor Tidak Valid
                                    </div>
                                @endif
                            @else
                                <span class="text-xs text-slate-400">No HP belum ada</span>
                                <div class="mt-0.5 text-[10px] font-bold text-slate-400">Tanpa Nomor</div>
                            @endif
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-medium text-slate-800">{{ $prediction->item_code ?: '-' }}</span>
                                @if(str_contains(strtoupper((string) $prediction->item_code), 'ACC') || $prediction->category === 'LAINNYA')
                                    <span class="inline-flex items-center rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">Lainnya</span>
                                @else
                                    <span class="inline-flex items-center rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold text-blue-700">{{ $prediction->category }}</span>
                                @endif
                            </div>
                            <div class="mt-1 max-w-56 truncate text-xs text-slate-500" title="{{ $prediction->dscription }}">{{ $prediction->dscription ?: '-' }}</div>
                        </td>
                        <td class="px-3 py-4 text-center">
                            <span class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-800">
                                {{ $prediction->transaction_count }}x
                            </span>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-xs text-slate-600">
                            {{ $prediction->last_order_date?->format('d M Y') ?: '-' }}
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap">
                            <div class="font-bold {{ $isOverdue ? 'text-rose-600' : 'text-brand-700' }}">
                                {{ $prediction->predicted_repeat_date?->format('d M Y') ?: '-' }}
                            </div>
                            @if($isOverdue)
                                <span class="mt-0.5 inline-flex items-center rounded bg-rose-50 px-1.5 py-0.2 text-[9px] font-bold text-rose-700">⚠️ Terlewat</span>
                            @endif
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-xs text-slate-600">
                            {{ $prediction->average_gap_days ? number_format((float) $prediction->average_gap_days, 0).' hari' : '-' }}
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold capitalize {{ $prediction->classification === 'tinggi' ? 'bg-emerald-100 text-emerald-700' : ($prediction->classification === 'sedang' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ $prediction->classification }} · {{ $prediction->confidence }}%
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <button type="button" onclick="singleQuickBroadcast({{ $prediction->id }}, '{{ addslashes($prediction->customer_vendor_name ?: 'Kaka') }}', '{{ addslashes($prediction->dscription ?: ($prediction->item_code ?: 'produk')) }}', '{{ $prediction->no_hp_customer }}')" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-emerald-700 shadow-sm hover:border-emerald-300 hover:bg-emerald-50 transition">
                                <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a4 4 0 0 0 5 5"/></svg>
                                Broadcast
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-5 py-12 text-center text-slate-500">Belum ada data pelanggan yang sesuai dengan filter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</form>

<div class="mt-4">{{ $predictions->links() }}</div>

<!-- ======================================================== -->
<!-- MODAL QUICK BROADCAST DENGAN TEMPLATE & GAMBAR           -->
<!-- ======================================================== -->
<div id="quick-broadcast-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm sm:p-6 md:p-10">
    <div class="mx-auto max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="grid size-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a4 4 0 0 0 5 5"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900">Kirim Broadcast WhatsApp</h3>
                    <p id="modal-target-summary" class="text-xs text-slate-500">Kirim ke pelanggan terpilih</p>
                </div>
            </div>
            <button type="button" onclick="closeQuickBroadcastModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="modal-broadcast-form" method="POST" action="{{ route('broadcasts.bulk') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="redirect_to" value="predictions.index">
            <div id="modal-prediction-ids-container"></div>

            <!-- Pilih Template -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700">Pilih Template Broadcast</label>
                    <a href="{{ route('broadcasts.templates.index') }}" target="_blank" class="text-[11px] font-bold text-brand-600 hover:underline">+ Kelola Template</a>
                </div>
                <select id="modal-template-select" class="dcms-input text-xs font-medium" onchange="applyModalTemplate(this)">
                    <option value="">⚡ Gunakan Template Pesan...</option>
                    @foreach($templates as $tmpl)
                        <option value="{{ $tmpl->id }}" data-message="{{ $tmpl->message }}" data-image="{{ $tmpl->image_url ?? '' }}">{{ $tmpl->title }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Isi Pesan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pesan WhatsApp <span class="text-rose-500">*</span></label>
                <textarea id="modal-message-input" name="message" required rows="4" maxlength="2500" class="dcms-input text-xs leading-relaxed" placeholder="Halo Kak {nama}, promo repeat order menarik untuk Anda..."></textarea>
                
                <div class="mt-1.5 flex flex-wrap items-center gap-1 text-[11px] text-slate-500">
                    <span class="font-bold">Variabel:</span>
                    <button type="button" onclick="insertModalPlaceholder('{nama}')" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-brand-700 hover:bg-brand-100">{nama}</button>
                    <button type="button" onclick="insertModalPlaceholder('{item}')" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-brand-700 hover:bg-brand-100">{item}</button>
                    <button type="button" onclick="insertModalPlaceholder('{kode}')" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-brand-700 hover:bg-brand-100">{kode}</button>
                </div>
            </div>

            <!-- Lampiran Gambar -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <label class="block text-xs font-bold text-slate-700 mb-1">Lampirkan Gambar (Flyer / Promo)</label>
                <p class="text-[11px] text-slate-500 mb-2">Unggah file foto gambar langsung (JPG, PNG, WebP maks 5MB).</p>
                <input type="file" id="modal-image-file" name="image" accept="image/*" class="dcms-input text-xs" onchange="previewModalUploadImage(this)">

                <div id="modal-image-preview-box" class="mt-2.5 hidden rounded-xl border border-slate-200 bg-white p-2 text-center">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-bold text-slate-600">Pratinjau Gambar:</span>
                        <button type="button" onclick="removeModalUploadImage()" class="text-[10px] font-bold text-rose-600 hover:underline">Hapus</button>
                    </div>
                    <img id="modal-image-preview-el" src="#" alt="Pratinjau" class="max-h-36 w-auto rounded-lg object-contain mx-auto shadow-sm">
                </div>

                <input type="hidden" id="modal-template-image-url" name="image_url" value="">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeQuickBroadcastModal()" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="dcms-primary-button px-5 py-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-500">
                    🚀 Kirim Sekarang via WatZap
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Checkbox update badge helper
function syncSelectedCounts() {
    const selected = document.querySelectorAll('.prediction-checkbox:checked').length;
    document.querySelectorAll('.selected-count-badge').forEach(el => {
        el.textContent = `(${selected})`;
    });
    const broadcastBtn = document.getElementById('open-quick-broadcast-modal-btn');
    if (broadcastBtn) {
        broadcastBtn.disabled = selected === 0;
    }
}

// Hook into change events
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.prediction-checkbox').forEach(cb => {
        cb.addEventListener('change', syncSelectedCounts);
    });
    const selectAll = document.getElementById('select-all-predictions');
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            setTimeout(syncSelectedCounts, 50);
        });
    }
    syncSelectedCounts();
});

function openQuickBroadcastModal() {
    const checked = [...document.querySelectorAll('.prediction-checkbox:checked')].map(el => el.value);
    if (checked.length === 0) return;

    const container = document.getElementById('modal-prediction-ids-container');
    container.innerHTML = '';
    checked.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'prediction_ids[]';
        input.value = id;
        container.appendChild(input);
    });

    document.getElementById('modal-target-summary').textContent = `Mengirim ke ${checked.length} pelanggan terpilih`;
    document.getElementById('quick-broadcast-modal').classList.remove('hidden');
}

function singleQuickBroadcast(id, name, item, phone) {
    const container = document.getElementById('modal-prediction-ids-container');
    container.innerHTML = '';
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'prediction_ids[]';
    input.value = id;
    container.appendChild(input);

    document.getElementById('modal-target-summary').textContent = `Target: ${name} (${phone || 'Tanpa No HP'})`;
    
    // Auto-fill personalized preview
    const textarea = document.getElementById('modal-message-input');
    if (textarea && !textarea.value) {
        textarea.value = `Halo Kak ${name}! Terima kasih telah setia berbelanja di Gadgetmart. Kami ada info promo spesial untuk produk ${item}. Balas pesan ini untuk info lengkapnya ya! 📱✨`;
    }

    document.getElementById('quick-broadcast-modal').classList.remove('hidden');
}

function closeQuickBroadcastModal() {
    document.getElementById('quick-broadcast-modal').classList.add('hidden');
}

function applyModalTemplate(select) {
    const selectedOption = select.options[select.selectedIndex];
    const message = selectedOption.getAttribute('data-message') || '';
    const image = selectedOption.getAttribute('data-image') || '';

    const textarea = document.getElementById('modal-message-input');
    if (textarea && message) {
        textarea.value = message;
    }

    const imageUrlInput = document.getElementById('modal-template-image-url');
    const previewBox = document.getElementById('modal-image-preview-box');
    const previewEl = document.getElementById('modal-image-preview-el');

    if (image && imageUrlInput && previewBox && previewEl) {
        imageUrlInput.value = image;
        previewEl.src = image;
        previewBox.classList.remove('hidden');
    }
}

function insertModalPlaceholder(placeholder) {
    const textarea = document.getElementById('modal-message-input');
    if (!textarea) return;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
    textarea.focus();
}

function previewModalUploadImage(input) {
    const previewBox = document.getElementById('modal-image-preview-box');
    const previewEl = document.getElementById('modal-image-preview-el');
    const imageUrlInput = document.getElementById('modal-template-image-url');

    if (imageUrlInput) imageUrlInput.value = '';

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewEl.src = e.target.result;
            previewBox.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        previewBox.classList.add('hidden');
    }
}

function removeModalUploadImage() {
    const input = document.getElementById('modal-image-file');
    const previewBox = document.getElementById('modal-image-preview-box');
    const previewEl = document.getElementById('modal-image-preview-el');
    const imageUrlInput = document.getElementById('modal-template-image-url');

    if (input) input.value = '';
    if (previewEl) previewEl.src = '#';
    if (imageUrlInput) imageUrlInput.value = '';
    if (previewBox) previewBox.classList.add('hidden');
}
</script>
@endsection
