@extends('layouts.app')
@section('title', 'Dashboard Konsumen Repeat Order')
@section('content')
<!-- Header Halaman -->
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-700">
                <span class="size-2 rounded-full bg-amber-500 animate-pulse"></span>
                Customer Retention Intelligence
            </span>
            <span class="text-xs text-slate-400">&bull;</span>
            <span class="text-xs font-medium text-slate-500">Analisis Perilaku Konsumen Berulang</span>
        </div>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Dashboard Konsumen Repeat Order</h1>
        <p class="mt-1 text-xs text-slate-500">Pantau loyalitas konsumen, siklus pembelian, prediksi jadwal beli kembali, dan peluang broadcast terarah.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('predictions.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <svg class="size-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10 2v6m3-3h-6"/></svg>
            Data Master Customer
        </a>
        <a href="{{ route('broadcasts.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-500">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/></svg>
            Laporan Broadcast
        </a>
    </div>
</div>

<!-- ======================================================== -->
<!-- RINGKASAN METRIK UTAMA (5 KPI CARDS)                      -->
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

            <div class="mt-5 space-y-4">
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <div class="flex items-center gap-3">
                        <div class="grid size-9 place-items-center rounded-lg bg-blue-100 text-blue-700 font-black text-xs">📱</div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Smartphone</div>
                            <div class="text-[11px] text-slate-500">Unit Handphone & Gadget Utama</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-black text-slate-900">{{ number_format($smartphoneCount) }}</div>
                        <div class="text-[10px] font-semibold text-slate-400">{{ $totalRepeatOrders > 0 ? round(($smartphoneCount/$totalRepeatOrders)*100, 1) : 0 }}%</div>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <div class="flex items-center gap-3">
                        <div class="grid size-9 place-items-center rounded-lg bg-amber-100 text-amber-700 font-black text-xs">🎧</div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Aksesoris (ACC)</div>
                            <div class="text-[11px] text-slate-500">Charger, Case, TWS, Tempered Glass</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-black text-slate-900">{{ number_format($accCount) }}</div>
                        <div class="text-[10px] font-semibold text-slate-400">{{ $totalRepeatOrders > 0 ? round(($accCount/$totalRepeatOrders)*100, 1) : 0 }}%</div>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <div class="flex items-center gap-3">
                        <div class="grid size-9 place-items-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">📦</div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Lainnya</div>
                            <div class="text-[11px] text-slate-500">Voucher, Layanan & Produk Lain</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-black text-slate-900">{{ number_format($otherCategoryCount) }}</div>
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
<!-- FILTER INTERAKTIF DATA KONSUMEN                          -->
<!-- ======================================================== -->
<section class="dcms-card p-5 mb-6">
    <form method="GET" action="{{ route('repeat-orders.dashboard') }}" class="space-y-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-base font-black text-slate-900">Konsumen Repeat Order Prioritas</h2>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">Urutan Prioritas:</span>
                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">Tanggal Prediksi Terdekat &bull; Transaksi Terbanyak</span>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <!-- Filter Periode Prediksi -->
            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-600">Jadwal Repeat Order</label>
                <select name="period" class="dcms-input text-xs" onchange="this.form.submit()">
                    <option value="all" @selected($periodFilter === 'all')>Semua Jadwal</option>
                    <option value="overdue" @selected($periodFilter === 'overdue')>⚠️ Terlewat (Overdue)</option>
                    <option value="this_month" @selected($periodFilter === 'this_month')>📅 Bulan Ini</option>
                    <option value="next_30_days" @selected($periodFilter === 'next_30_days')>⏱️ 30 Hari ke Depan</option>
                </select>
            </div>

            <!-- Filter Klasifikasi Potensi -->
            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-600">Klasifikasi Potensi</label>
                <select name="classification" class="dcms-input text-xs" onchange="this.form.submit()">
                    <option value="all" @selected($classificationFilter === 'all')>Semua Potensi</option>
                    <option value="tinggi" @selected($classificationFilter === 'tinggi')>Tinggi (High Priority)</option>
                    <option value="sedang" @selected($classificationFilter === 'sedang')>Sedang</option>
                    <option value="rendah" @selected($classificationFilter === 'rendah')>Rendah</option>
                </select>
            </div>

            <!-- Filter Kategori Produk -->
            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-600">Kategori Produk</label>
                <select name="category" class="dcms-input text-xs" onchange="this.form.submit()">
                    <option value="all" @selected($categoryFilter === 'all')>Semua Kategori</option>
                    <option value="SMARTPHONE" @selected($categoryFilter === 'SMARTPHONE')>📱 Smartphone</option>
                    <option value="LAINNYA" @selected($categoryFilter === 'LAINNYA' || $categoryFilter === 'ACC')>🎧 Aksesoris & Lainnya</option>
                </select>
            </div>

            <!-- Filter WhatsApp -->
            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-600">Status WhatsApp</label>
                <select name="phone_status" class="dcms-input text-xs" onchange="this.form.submit()">
                    <option value="all" @selected($phoneStatusFilter === 'all')>Semua WhatsApp</option>
                    <option value="valid" @selected($phoneStatusFilter === 'valid')>✅ WhatsApp Valid</option>
                    <option value="missing" @selected($phoneStatusFilter === 'missing')>❌ Tanpa Nomor</option>
                </select>
            </div>

            <!-- Search Box -->
            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-600">Pencarian Konsumen</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama / No HP / Item..." class="dcms-input pr-8 text-xs">
                    <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<!-- ======================================================== -->
<!-- TABEL KONSUMEN REPEAT ORDER PRIORITAS                     -->
<!-- ======================================================== -->
<div class="dcms-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3.5">Konsumen</th>
                    <th class="px-4 py-3.5">Kontak WhatsApp</th>
                    <th class="px-4 py-3.5">Item & Kategori</th>
                    <th class="px-4 py-3.5 text-center">Frekuensi</th>
                    <th class="px-4 py-3.5 text-center">Jeda Rata-rata</th>
                    <th class="px-4 py-3.5">Order Terakhir</th>
                    <th class="px-4 py-3.5">Prediksi Repeat</th>
                    <th class="px-4 py-3.5">Potensi</th>
                    <th class="px-4 py-3.5 text-right">Aksi Cepat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($priorityCustomers as $p)
                @php
                    $isOverdue = $p->predicted_repeat_date && $p->predicted_repeat_date->isPast();
                    $isSoon = $p->predicted_repeat_date && ! $isOverdue && $p->predicted_repeat_date->diffInDays(now()) <= 30;
                    $phone = $p->no_hp_customer;
                    $isValidPhone = ! empty($phone) && str_starts_with($phone, '62') && strlen($phone) >= 10 && strlen($phone) <= 15;
                @endphp
                <tr class="hover:bg-slate-50/80 transition">
                    <!-- Konsumen -->
                    <td class="px-4 py-3">
                        <div class="font-bold text-slate-900">{{ $p->customer_vendor_name }}</div>
                        <div class="text-[11px] text-slate-400">{{ $p->customer_vendor_code }} &bull; {{ $p->cabang ?: 'Pusat' }}</div>
                    </td>

                    <!-- Kontak WhatsApp -->
                    <td class="px-4 py-3">
                        @if($isValidPhone)
                            <div class="font-mono font-semibold text-slate-800">{{ $phone }}</div>
                            <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-1.5 py-0.2 text-[10px] font-bold text-emerald-700">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                WhatsApp Valid
                            </span>
                        @elseif(! empty($phone))
                            <div class="font-mono text-slate-600">{{ $phone }}</div>
                            <span class="text-[10px] text-amber-600 font-semibold">Format tidak standar</span>
                        @else
                            <span class="text-[11px] text-slate-400 italic">Tanpa nomor</span>
                        @endif
                    </td>

                    <!-- Item & Kategori -->
                    <td class="px-4 py-3 max-w-xs">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-semibold text-slate-800 truncate">{{ $p->item_code ?: '-' }}</span>
                            @if(str_contains(strtoupper($p->item_code), 'ACC'))
                                <span class="rounded bg-slate-100 px-1.5 py-0.2 text-[10px] font-bold text-slate-600">Lainnya</span>
                            @else
                                <span class="rounded bg-brand-50 px-1.5 py-0.2 text-[10px] font-bold text-brand-700">{{ $p->category }}</span>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-500 truncate" title="{{ $p->dscription }}">{{ $p->dscription ?: '-' }}</div>
                    </td>

                    <!-- Frekuensi Transaksi -->
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-2 py-1 text-xs font-black text-slate-800">
                            {{ $p->transaction_count }}x
                        </span>
                        @if($p->total_value > 0)
                            <div class="text-[10px] text-slate-400 mt-0.5">Rp {{ number_format($p->total_value / 1000, 0, ',', '.') }}k</div>
                        @endif
                    </td>

                    <!-- Jeda Hari Rata-rata -->
                    <td class="px-4 py-3 text-center">
                        <div class="font-bold text-slate-700">{{ $p->average_gap_days ? number_format((float) $p->average_gap_days, 0) : '-' }}</div>
                        <span class="text-[10px] text-slate-400">Hari</span>
                    </td>

                    <!-- Order Terakhir -->
                    <td class="px-4 py-3 text-slate-600">
                        {{ $p->last_order_date ? $p->last_order_date->format('d M Y') : '-' }}
                    </td>

                    <!-- Prediksi Repeat Order -->
                    <td class="px-4 py-3">
                        <div class="font-bold {{ $isOverdue ? 'text-rose-600' : ($isSoon ? 'text-amber-600' : 'text-brand-700') }}">
                            {{ $p->predicted_repeat_date ? $p->predicted_repeat_date->format('d M Y') : '-' }}
                        </div>
                        @if($isOverdue)
                            <span class="inline-flex items-center gap-1 rounded bg-rose-50 px-1.5 py-0.2 text-[10px] font-bold text-rose-700">
                                Lewat Jadwal
                            </span>
                        @elseif($isSoon)
                            <span class="inline-flex items-center gap-1 rounded bg-amber-50 px-1.5 py-0.2 text-[10px] font-bold text-amber-700">
                                Segera Beli
                            </span>
                        @endif
                    </td>

                    <!-- Potensi -->
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold capitalize {{ $p->classification === 'tinggi' ? 'bg-emerald-100 text-emerald-800' : ($p->classification === 'sedang' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                            {{ $p->classification }} &bull; {{ $p->confidence }}%
                        </span>
                    </td>

                    <!-- Aksi Cepat -->
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-1.5">
                            <!-- Follow Up ke Leads Form -->
                            <form method="POST" action="{{ route('predictions.assign') }}">
                                @csrf
                                <input type="hidden" name="prediction_ids[]" value="{{ $p->id }}">
                                <button type="submit" title="Tugaskan ke Leads Customer" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                                    <svg class="size-3 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10 2v6m3-3h-6"/></svg>
                                    <span>Follow Up</span>
                                </button>
                            </form>

                            @if($isValidPhone)
                            <!-- Tombol WhatsApp Broadcast -->
                            <form method="POST" action="{{ route('broadcasts.store', $p->id) }}">
                                @csrf
                                <input type="hidden" name="message" value="Halo kak {{ $p->customer_vendor_name }}, terima kasih sudah setia berbelanja di Gadgetmart! Kami ada penawaran promo spesial terbaru untuk produk favorit Kakak. Mau Mimin bantu info promonya? ✨">
                                <button type="submit" onclick="return confirm('Kirim pesan broadcast WhatsApp ke {{ $p->customer_vendor_name }} ({{ $phone }})?')" title="Kirim WhatsApp via WatZap" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2 py-1 text-[11px] font-bold text-white hover:bg-emerald-500 transition shadow-2xs">
                                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/></svg>
                                    <span>WA</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="p-8 text-center text-xs text-slate-500">
                        Tidak ada data konsumen repeat order yang sesuai dengan filter yang dipilih.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($priorityCustomers->hasPages())
    <div class="border-t border-slate-100 p-4">
        {{ $priorityCustomers->links() }}
    </div>
    @endif
</div>
@endsection
