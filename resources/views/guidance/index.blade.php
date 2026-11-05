@extends('layouts.app')

@section('title', $isSuperAdmin ? 'Rumus Sistem & Panduan Superadmin' : 'Panduan & SOP Sales')

@section('content')
<div class="space-y-6">
    <!-- Header Page & Navigation -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-lg {{ $isSuperAdmin ? 'bg-purple-500/10 text-purple-700' : 'bg-sky-500/10 text-sky-700' }} px-2.5 py-1 text-xs font-bold">
                    @if ($isSuperAdmin)
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        Executive Guidance Room
                    @else
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                        Standard Operating Procedure (SOP)
                    @endif
                </span>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-semibold text-slate-500">{{ $isSuperAdmin ? 'Rumus Sistem & Metodologi' : 'Panduan Pelayanan Konsumen' }}</span>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">
                {{ $isSuperAdmin ? 'Rumus Sistem & Panduan Operasional' : 'Panduan & Standar Operasional Sales' }}
            </h1>
            <p class="text-xs text-slate-500 sm:text-sm">
                @if ($isSuperAdmin)
                    Dokumentasi algoritma prediksi repeat order pelanggan, formula terbobot evaluasi KPI sales, dan supervisi layanan.
                @else
                    Pedoman kerja harian dalam membalas chat konsumen, kecepatan respon, etika 3S, dan penawaran promo resmi Gadgetmart.
                @endif
            </p>
        </div>

        <!-- Quick Links -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ \App\Support\RouteCipher::url('monitoring.sales.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                <svg class="size-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                <span>{{ $isSuperAdmin ? 'Buka Monitoring Sales' : 'Lihat Rapor Performa' }}</span>
            </a>

            @if ($isSuperAdmin)
            <a href="{{ \App\Support\RouteCipher::url('settings.karyawan.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:bg-slate-800 transition">
                <svg class="size-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z"/></svg>
                <span>Kelola Bobot KPI</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-2 sm:space-x-4 overflow-x-auto" aria-label="Tabs">
            @if ($isSuperAdmin)
                <!-- SUPERADMIN TABS -->
                <a href="{{ route('guidance.index', ['tab' => 'formula_potensi']) }}" 
                   class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-3 text-xs sm:text-sm font-bold transition {{ $activeTab === 'formula_potensi' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    <span class="grid size-6 place-items-center rounded-lg {{ $activeTab === 'formula_potensi' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }} text-xs font-black font-mono">f(x)</span>
                    <span>Rumus Potensi Pelanggan</span>
                    <span class="rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-bold text-purple-800">Repeat Order</span>
                </a>

                <a href="{{ route('guidance.index', ['tab' => 'formula_penilaian']) }}" 
                   class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-3 text-xs sm:text-sm font-bold transition {{ $activeTab === 'formula_penilaian' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    <span class="grid size-6 place-items-center rounded-lg {{ $activeTab === 'formula_penilaian' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600' }} text-xs font-black font-mono">∑</span>
                    <span>Rumus Penilaian Sales</span>
                    <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-800">KPI Harian</span>
                </a>

                <a href="{{ route('guidance.index', ['tab' => 'sop_supervisi']) }}" 
                   class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-3 text-xs sm:text-sm font-bold transition {{ $activeTab === 'sop_supervisi' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                    <span>SOP Pelayanan Staf Sales</span>
                </a>
            @else
                <!-- STAFF ADMIN TABS (SOP ONLY) -->
                <a href="{{ route('guidance.index', ['tab' => 'sop_chat']) }}" 
                   class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-3 text-xs sm:text-sm font-bold transition {{ $activeTab === 'sop_chat' ? 'border-sky-600 text-sky-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    <span class="grid size-5 place-items-center rounded bg-sky-100 text-sky-700 text-xs font-black">1</span>
                    <span>Cara Balas Chat</span>
                </a>

                <a href="{{ route('guidance.index', ['tab' => 'sop_respon']) }}" 
                   class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-3 text-xs sm:text-sm font-bold transition {{ $activeTab === 'sop_respon' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    <span class="grid size-5 place-items-center rounded bg-emerald-100 text-emerald-700 text-xs font-black">2</span>
                    <span>Respon Time &amp; Standby</span>
                </a>

                <a href="{{ route('guidance.index', ['tab' => 'sop_etika']) }}" 
                   class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-3 text-xs sm:text-sm font-bold transition {{ $activeTab === 'sop_etika' ? 'border-amber-600 text-amber-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    <span class="grid size-5 place-items-center rounded bg-amber-100 text-amber-700 text-xs font-black">3</span>
                    <span>Etika &amp; Keramahan 3S</span>
                </a>

                <a href="{{ route('guidance.index', ['tab' => 'sop_promo']) }}" 
                   class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-3 text-xs sm:text-sm font-bold transition {{ $activeTab === 'sop_promo' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    <span class="grid size-5 place-items-center rounded bg-indigo-100 text-indigo-700 text-xs font-black">4</span>
                    <span>Penawaran Promo &amp; Upselling</span>
                </a>

                <a href="{{ route('guidance.index', ['tab' => 'sop_leads']) }}" 
                   class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-3 text-xs sm:text-sm font-bold transition {{ $activeTab === 'sop_leads' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    <span class="grid size-5 place-items-center rounded bg-purple-100 text-purple-700 text-xs font-black">5</span>
                    <span>Alur Leads, Keep &amp; AR</span>
                </a>
            @endif
        </nav>
    </div>

    <!-- TAB 1: RUMUS POTENSI PELANGGAN (SUPERADMIN ONLY) -->
    @if ($isSuperAdmin && $activeTab === 'formula_potensi')
    <div class="space-y-6">
        <!-- Overview Card -->
        <div class="rounded-3xl border border-purple-200/70 bg-gradient-to-br from-purple-50/60 via-white to-indigo-50/40 p-6 sm:p-8 shadow-xs">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-purple-600 text-white px-2.5 py-1 text-xs font-black">
                    ENGINE PREDIKSI REPEAT ORDER
                </span>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                    Metodologi Algoritma Prediksi Potensi Repeat Order
                </h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Sistem DCMS menganalisis setiap riwayat transaksi historis cross-selling per pelanggan dan per produk. Algoritma menggunakan <strong class="text-slate-900">Median Gap Days</strong> dan <strong class="text-slate-900">Median Absolute Deviation (MAD)</strong> untuk meredam pencilan (outlier), menghasilkan tanggal estimasi beli kembali yang sangat presisi dan skor keyakinan statistik.
                </p>
            </div>

            <!-- Mathematical Formula Box -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-purple-200 bg-white p-5 shadow-xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-purple-700">Formula Tanggal Prediksi</span>
                    <div class="mt-3 rounded-xl bg-slate-950 p-4 font-mono text-sm text-purple-300 shadow-inner">
                        <div class="text-slate-400 text-xs">// 1. Hitung Gap Selisih Hari</div>
                        <div class="mt-1">Gaps = [d₂ - d₁, d₃ - d₂, ..., dₙ - dₙ₋₁]</div>
                        <div class="mt-2 text-slate-400 text-xs">// 2. Median Gap Hari & Estimasi Tanggal</div>
                        <div class="mt-1 text-emerald-400 font-bold">MedianGap = Median(Gaps)</div>
                        <div class="mt-1 text-amber-300 font-bold">PredictedDate = LastOrderDate + MedianGap</div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                        Jika transaksi unik <strong class="text-slate-700">&ge; 2</strong>, selisih hari diurutkan untuk mengambil nilai tengah sehingga tahan dari distorsi libur panjang atau lonjakan pesanan acak.
                    </p>
                </div>

                <div class="rounded-2xl border border-indigo-200 bg-white p-5 shadow-xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Formula Tingkat Keyakinan (Confidence Score)</span>
                    <div class="mt-3 rounded-xl bg-slate-950 p-4 font-mono text-sm text-indigo-300 shadow-inner">
                        <div class="text-slate-400 text-xs">// 3. Deviasi Absolut & Koefisien Variasi</div>
                        <div class="mt-1">MAD = Median(|gapᵢ - MedianGap|)</div>
                        <div class="mt-1">Koefisien = MAD / MedianGap</div>
                        <div class="mt-2 text-slate-400 text-xs">// 4. Skor Keyakinan dengan Penalti Inkonsistensi</div>
                        <div class="mt-1 text-indigo-200 font-bold">Confidence = Baseline - min(30, Koefisien &times; 30)</div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                        Semakin konsisten ritme belanja pelanggan (MAD kecil), semakin tinggi tingkat keyakinan sistem (hingga batas maksimal 95%).
                    </p>
                </div>
            </div>
        </div>

        <!-- Step-by-Step Breakdown Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Step 1: Baseline Transaksi -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="grid size-8 place-items-center rounded-xl bg-purple-100 text-purple-700 font-black text-xs">01</span>
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Tahap Frekuensi</span>
                </div>
                <h3 class="mt-3 text-base font-bold text-slate-900">Baseline Confidence</h3>
                <p class="mt-1 text-xs text-slate-500">Nilai awal keyakinan berdasarkan jumlah transaksi unik:</p>
                
                <div class="mt-3 space-y-1.5 text-xs">
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-1.5">
                        <span class="text-slate-600">&ge; 5 Transaksi Unik</span>
                        <span class="font-black text-purple-700">90%</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-1.5">
                        <span class="text-slate-600">4 Transaksi Unik</span>
                        <span class="font-black text-purple-700">85%</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-1.5">
                        <span class="text-slate-600">3 Transaksi Unik</span>
                        <span class="font-black text-purple-700">75%</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-1.5">
                        <span class="text-slate-600">2 Transaksi Unik</span>
                        <span class="font-black text-purple-700">55%</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-1.5">
                        <span class="text-slate-600">1 Transaksi Unik (Baru)</span>
                        <span class="font-black text-slate-500">20%</span>
                    </div>
                </div>
            </div>

            <!-- Step 2: Penalti Variabilitas -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="grid size-8 place-items-center rounded-xl bg-indigo-100 text-indigo-700 font-black text-xs">02</span>
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Tahap Stabilitas</span>
                </div>
                <h3 class="mt-3 text-base font-bold text-slate-900">Koreksi MAD (Stabilitas)</h3>
                <p class="mt-1 text-xs text-slate-500">Mereduksi bias jika siklus beli tidak teratur:</p>

                <div class="mt-3 space-y-2 text-xs">
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-2.5">
                        <span class="font-bold text-indigo-900 block">Sangat Konsisten (MAD &asymp; 0)</span>
                        <span class="text-slate-600 text-[11px]">Penalti = 0%. Confidence tetap optimal pada batas baseline.</span>
                    </div>
                    <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-2.5">
                        <span class="font-bold text-amber-900 block">Sedikit Berfluktuasi</span>
                        <span class="text-slate-600 text-[11px]">Penalti 5% - 15% proporsional terhadap rasio deviasi.</span>
                    </div>
                    <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-2.5">
                        <span class="font-bold text-rose-900 block">Sangat Acak / Fluktuatif</span>
                        <span class="text-slate-600 text-[11px]">Maksimal penalti 30%. Skor dibatasi minimal 10%.</span>
                    </div>
                </div>
            </div>

            <!-- Step 3: Klasifikasi Potensi -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="grid size-8 place-items-center rounded-xl bg-emerald-100 text-emerald-700 font-black text-xs">03</span>
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Tahap Keputusan</span>
                </div>
                <h3 class="mt-3 text-base font-bold text-slate-900">Klasifikasi Potensi</h3>
                <p class="mt-1 text-xs text-slate-500">Dihitung dari selisih hari ke prediksi (DaysUntil):</p>

                <div class="mt-3 space-y-1.5 text-xs">
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-2">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-emerald-900">🟢 Potensi TINGGI</span>
                            <span class="font-mono text-[11px] text-emerald-800 font-bold">&le; 30 Hari</span>
                        </div>
                        <span class="text-[11px] text-emerald-700 block mt-0.5">Jendela waktu emas beli kembali. Prioritas broadcast & follow-up langsung.</span>
                    </div>

                    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-2">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-amber-900">🟡 Potensi SEDANG</span>
                            <span class="font-mono text-[11px] text-amber-800 font-bold">31 - 60 Hari</span>
                        </div>
                        <span class="text-[11px] text-amber-700 block mt-0.5">Masa persiapan minat. Kirim edukasi produk atau promo baru.</span>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-2">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-slate-800">⚪ Potensi RENDAH</span>
                            <span class="font-mono text-[11px] text-slate-600 font-bold">&gt; 60 Hari</span>
                        </div>
                        <span class="text-[11px] text-slate-500 block mt-0.5">Jeda beli masih panjang. Hindari kontak agresif.</span>
                    </div>

                    <div class="rounded-xl border border-blue-200 bg-blue-50/70 p-2">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-blue-900">🔵 Pelanggan BARU</span>
                            <span class="font-mono text-[11px] text-blue-800 font-bold">1 Transaksi</span>
                        </div>
                        <span class="text-[11px] text-blue-700 block mt-0.5">Belum memiliki riwayat gap. Masuk alur purnajual (H+7).</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Example Case Walkthrough -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xs">
            <h3 class="text-base font-bold text-slate-900">Contoh Simulasi Nyata Perhitungan Sistem</h3>
            <p class="mt-1 text-xs text-slate-500">Simulasi penghitungan pelanggan dengan 3 transaksi riwayat historis:</p>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-slate-500">
                            <th class="px-4 py-2.5 font-bold">Order Ke</th>
                            <th class="px-4 py-2.5 font-bold">Tanggal Transaksi</th>
                            <th class="px-4 py-2.5 font-bold">Item Terbeli</th>
                            <th class="px-4 py-2.5 font-bold">Gap Hari (Selisih)</th>
                            <th class="px-4 py-2.5 font-bold">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-mono">
                        <tr>
                            <td class="px-4 py-2.5">Order 1</td>
                            <td class="px-4 py-2.5">01 Januari 2026</td>
                            <td class="px-4 py-2.5 font-sans">Smartphone Samsung A55</td>
                            <td class="px-4 py-2.5 text-slate-400">-</td>
                            <td class="px-4 py-2.5 font-sans text-slate-500">Order Perdana</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5">Order 2</td>
                            <td class="px-4 py-2.5">15 Februari 2026</td>
                            <td class="px-4 py-2.5 font-sans">Aksesoris TWS & Fast Charger</td>
                            <td class="px-4 py-2.5 font-bold text-purple-700">45 Hari</td>
                            <td class="px-4 py-2.5 font-sans text-slate-500">Gap = 45 hari dari Order 1</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5">Order 3</td>
                            <td class="px-4 py-2.5">01 April 2026</td>
                            <td class="px-4 py-2.5 font-sans">Upgrade Smartphone Samsung S24</td>
                            <td class="px-4 py-2.5 font-bold text-purple-700">45 Hari</td>
                            <td class="px-4 py-2.5 font-sans text-slate-500">Gap = 45 hari dari Order 2</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-2xl bg-slate-50 p-4 border border-slate-200/80 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Hasil Median Gap:</span>
                    <span class="font-bold text-slate-900 font-mono text-sm">45 Hari</span>
                    <span class="text-[11px] text-slate-500 block">MAD = 0 (Sangat stabil)</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Tanggal Prediksi Repeat Order:</span>
                    <span class="font-bold text-purple-700 font-mono text-sm">16 Mei 2026</span>
                    <span class="text-[11px] text-slate-500 block">01 April 2026 + 45 Hari</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Confidence & Klasifikasi:</span>
                    <span class="font-bold text-emerald-700 font-mono text-sm">75% &bull; Potensi TINGGI</span>
                    <span class="text-[11px] text-slate-500 block">Masuk prioritas broadcast batch</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 2: RUMUS PENILAIAN SALES (SUPERADMIN ONLY) -->
    @if ($isSuperAdmin && $activeTab === 'formula_penilaian')
    <div class="space-y-6">
        <!-- Overview Box -->
        <div class="rounded-3xl border border-indigo-200/70 bg-gradient-to-br from-indigo-50/60 via-white to-purple-50/40 p-6 sm:p-8 shadow-xs">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 text-white px-2.5 py-1 text-xs font-black">
                    STANDARDISASI EVALUASI SALES
                </span>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                    Rumus &amp; Standar Penilaian Kinerja Admin Sales Harian
                </h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Evaluasi performa sales dilakukan secara objektif setiap hari berdasarkan 4 aspek layanan operasional utama dengan sistem skor terbobot 100%. Superadmin bertindak sebagai penilai tunggal untuk menjaga konsistensi mutu tim.
                </p>
            </div>

            <!-- Mathematical Formula Box -->
            <div class="mt-6 rounded-2xl border border-indigo-200 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Rumus Matematis Akumulasi Skor Terbobot Harian</span>
                <div class="mt-3 rounded-xl bg-slate-950 p-4 font-mono text-sm text-indigo-300 shadow-inner">
                    <div class="text-slate-400 text-xs">// Formula Total Skor Terbobot Harian (Skala 0 - 100)</div>
                    <div class="mt-1 text-white font-bold">
                        Skor Akhir = ∑ (Nilai Kriteriaᵢ × (Bobotᵢ / 100))
                    </div>
                    <div class="mt-2 text-indigo-200 text-xs">
                        = (Nilai Chat × 25%) + (Nilai Respon × 25%) + (Nilai Etika × 25%) + (Nilai Promo × 25%)
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Criteria Breakdown Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($kpiCriteria as $criterion)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs hover:border-indigo-300 transition">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 border border-indigo-100 px-2.5 py-1 text-xs font-bold text-indigo-700">
                        Bobot: {{ number_format($criterion->weight, 0) }}%
                    </span>
                    <span class="font-mono text-xs text-slate-400">Kode: {{ $criterion->code }}</span>
                </div>
                <h3 class="mt-3 text-base font-black text-slate-900">{{ $criterion->name }}</h3>
                <p class="mt-1 text-xs text-slate-600 leading-relaxed">{{ $criterion->description }}</p>
                
                @if ($criterion->target_benchmark)
                <div class="mt-3 rounded-xl bg-slate-50 border border-slate-100 p-2.5 text-xs text-slate-700">
                    <span class="text-slate-400 text-[11px] block font-semibold">Target Standar / Benchmark:</span>
                    <span class="font-semibold text-indigo-950">{{ $criterion->target_benchmark }}</span>
                </div>
                @endif
            </div>
            @empty
            <div class="col-span-2 rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                Data kriteria penilaian KPI belum diset. Silakan konfigurasi melalui menu Setting Karyawan & Bobot KPI.
            </div>
            @endforelse
        </div>

        <!-- Grade Scale & Action Plan -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xs">
            <h3 class="text-base font-bold text-slate-900">Skala Penentuan Predikat (Grade) & Tindak Lanjut Evaluator</h3>
            <p class="mt-1 text-xs text-slate-500">Kategori hasil akhir penilaian harian beserta rekomendasi tindak lanjut bagi manager:</p>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Grade A -->
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4">
                    <div class="flex items-center justify-between">
                        <span class="rounded-lg bg-emerald-600 text-white font-black text-xs px-2.5 py-1">GRADE A</span>
                        <span class="font-mono text-xs font-bold text-emerald-800">&ge; 90.0</span>
                    </div>
                    <div class="mt-2 text-sm font-bold text-emerald-950">Top Performer</div>
                    <p class="mt-1 text-xs text-emerald-800 leading-relaxed">
                        Layanan konsumen sangat prima. Respon kilat, komunikasi solutif, etika santun, dan aktif menawarkan promo serta upselling. Berikan apresiasi atau reward berkala.
                    </p>
                </div>

                <!-- Grade B -->
                <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4">
                    <div class="flex items-center justify-between">
                        <span class="rounded-lg bg-blue-600 text-white font-black text-xs px-2.5 py-1">GRADE B</span>
                        <span class="font-mono text-xs font-bold text-blue-800">80.0 &ndash; 89.9</span>
                    </div>
                    <div class="mt-2 text-sm font-bold text-blue-950">Memenuhi Standar</div>
                    <p class="mt-1 text-xs text-blue-800 leading-relaxed">
                        Performa solid dan stabil sesuai SOP. Terus didorong agar lebih inisiatif menawarkan promo bundling pelengkap untuk naik ke Grade A.
                    </p>
                </div>

                <!-- Grade C -->
                <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4">
                    <div class="flex items-center justify-between">
                        <span class="rounded-lg bg-amber-600 text-white font-black text-xs px-2.5 py-1">GRADE C</span>
                        <span class="font-mono text-xs font-bold text-amber-800">70.0 &ndash; 79.9</span>
                    </div>
                    <div class="mt-2 text-sm font-bold text-amber-950">Perlu Evaluasi</div>
                    <p class="mt-1 text-xs text-amber-800 leading-relaxed">
                        Terdapat kelemahan spesifik (misal respon sempat lambat di jam tertentu atau kurang menguasai promo aktif). Memerlukan review mingguan.
                    </p>
                </div>

                <!-- Grade D -->
                <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-4">
                    <div class="flex items-center justify-between">
                        <span class="rounded-lg bg-rose-600 text-white font-black text-xs px-2.5 py-1">GRADE D</span>
                        <span class="font-mono text-xs font-bold text-rose-800">&lt; 70.0</span>
                    </div>
                    <div class="mt-2 text-sm font-bold text-rose-950">Butuh Coaching</div>
                    <p class="mt-1 text-xs text-rose-800 leading-relaxed">
                        Kualitas layanan di bawah standar minimal perusahaan. Wajib mendapatkan sesi coaching 1-on-1 langsung dari Manager dan latihan simulasi balas chat.
                    </p>
                </div>
            </div>

            <!-- Access Control Rule -->
            <div class="mt-6 rounded-2xl bg-slate-900 p-4 text-white flex items-start gap-3">
                <svg class="size-5 text-amber-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                <div class="text-xs leading-relaxed">
                    <span class="font-bold text-amber-300 block">Kebijakan Hak Akses & Keamanan Penilaian:</span>
                    Semua tombol input nilai, modal ubah nilai, ekspor CSV, dan pengubahan bobot KPI hanya aktif untuk role <strong class="text-white">Superadmin</strong>. Akun staf admin yang login hanya dapat melihat rekapitulasi penilaian dirinya dalam mode baca (Read-Only) demi menjamin integritas data.
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 3: SOP PENGAWASAN SALES UNTUK SUPERADMIN / ATAU TAB SPESIFIK UNTUK STAFF ADMIN -->
    @if (($isSuperAdmin && $activeTab === 'sop_supervisi') || (! $isSuperAdmin && $activeTab === 'sop_chat'))
    <div class="space-y-6">
        <div class="rounded-3xl border border-sky-200/70 bg-gradient-to-br from-sky-50/60 via-white to-blue-50/40 p-6 sm:p-8 shadow-xs">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-sky-600 text-white px-2.5 py-1 text-xs font-black">
                    SOP PILAR 1: KOMUNIKASI CHAT
                </span>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                    Standar Cara Balas Chat Konsumen
                </h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Setiap pesan masuk dari calon pembeli adalah potensi omzet. Balasan harus solutif, ramah, to-the-point, dan tidak terkesan kaku atau robotik.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Do's -->
            <div class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-xs">
                <div class="flex items-center gap-2 text-emerald-700 font-bold text-sm mb-3">
                    <span class="grid size-6 place-items-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-black">✓</span>
                    <span>Praktik Terbaik (Direkomendasikan)</span>
                </div>
                <ul class="space-y-2.5 text-xs text-slate-600">
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-500 font-bold">•</span>
                        <span><strong>Sapa Hangat & Sebut Nama:</strong> Awali dengan salam hangat dan menyebut nama customer ("Halo Kak Budi, selamat pagi...").</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-500 font-bold">•</span>
                        <span><strong>To-The-Point:</strong> Langsung berikan jawaban yang dicari (stok, spesifikasi, harga resmi) tanpa membuat pelanggan bingung.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-500 font-bold">•</span>
                        <span><strong>Gunakan Kalimat Solutif:</strong> Jika warna yang dicari kosong, langsung tawarkan opsi warna lain yang ready atau estimasi restock.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-500 font-bold">•</span>
                        <span><strong>Call to Action:</strong> Akhiri chat dengan pertanyaan pemantik ("Kira-kira mau diambil hari ini atau kami kirim ke alamat kak?").</span>
                    </li>
                </ul>
            </div>

            <!-- Don'ts -->
            <div class="rounded-2xl border border-rose-200 bg-white p-5 shadow-xs">
                <div class="flex items-center gap-2 text-rose-700 font-bold text-sm mb-3">
                    <span class="grid size-6 place-items-center rounded-full bg-rose-100 text-rose-700 text-xs font-black">✕</span>
                    <span>Hal yang Dilarang (Poin Penalti)</span>
                </div>
                <ul class="space-y-2.5 text-xs text-slate-600">
                    <li class="flex items-start gap-2">
                        <span class="text-rose-500 font-bold">•</span>
                        <span><strong>Jawaban Singkat Sepatah Kata:</strong> Menjawab hanya "Ya", "Gak ada", "Kosong" tanpa salam dan alternatif.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-rose-500 font-bold">•</span>
                        <span><strong>Huruf Kapital Semua (Caps-Lock):</strong> Dilarang menggunakan caps lock penuh karena terkesan membentak atau emosional.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-rose-500 font-bold">•</span>
                        <span><strong>Copy-Paste Kaku Tanpa Penyesuaian:</strong> Mengirimkan teks template panjang yang tidak menjawab pertanyaan spesifik pembeli.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-rose-500 font-bold">•</span>
                        <span><strong>Membiarkan Chat Menggantung:</strong> Meninggalkan percakapan tanpa kalimat penutup atau kepastian.</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Template Examples -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
            <h3 class="text-base font-bold text-slate-900">Contoh Template Respon Percakapan Standar</h3>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                    <span class="font-bold text-slate-800 block text-xs mb-1">Contoh Menjawab Ketersediaan Unit Ready:</span>
                    <p class="text-slate-600 italic">
                        "Halo Kak Rizky, terima kasih sudah menghubungi Gadgetmart! Untuk unit iPhone 15 Pro 128GB Black Titanium saat ini ready ya kak di cabang kami. Hari ini sedang ada promo potongan Rp 500.000 + bonus tempered glass. Mau kami keep kan unitnya kak?"
                    </p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                    <span class="font-bold text-slate-800 block text-xs mb-1">Contoh Menjawab Varian Kosong:</span>
                    <p class="text-slate-600 italic">
                        "Halo Kak Siti, untuk warna Silver saat ini baru saja sold out kak. Namun untuk warna Natural Titanium ready ya kak dengan promo cashback yang sama. Warnanya sangat mewah kak, mau kami fotokan unit aslinya?"
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB: SOP RESPON TIME & STANDBY -->
    @if (($isSuperAdmin && $activeTab === 'sop_supervisi') || (! $isSuperAdmin && $activeTab === 'sop_respon'))
    <div class="space-y-6 {{ ($isSuperAdmin && $activeTab === 'sop_supervisi') ? 'pt-6 border-t border-slate-200' : '' }}">
        <div class="rounded-3xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50/60 via-white to-teal-50/40 p-6 sm:p-8 shadow-xs">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 text-white px-2.5 py-1 text-xs font-black">
                    SOP PILAR 2: RESPON TIME & STANDBY
                </span>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                    Kecepatan Respon & Konsistensi Standby
                </h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Target emas respon pertama (First Response Time) adalah <strong class="text-slate-900">&lt; 3 menit</strong>. Kecepatan tanggap secara langsung mempengaruhi tingkat closing transaksi penjualan.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <span class="grid size-8 place-items-center rounded-xl bg-emerald-100 text-emerald-700 font-bold text-xs">⚡</span>
                <h4 class="mt-3 text-sm font-bold text-slate-900">Target &lt; 3 Menit</h4>
                <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                    Segera sapa dan tanggapi pesan konsumen sebelum mereka mencari penawaran di toko kompetitor.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <span class="grid size-8 place-items-center rounded-xl bg-blue-100 text-blue-700 font-bold text-xs">🖥️</span>
                <h4 class="mt-3 text-sm font-bold text-slate-900">Standby Penuh Shift</h4>
                <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                    Selalu buka tab WhatsApp Web dan Workspace Sosmed. Dilarang meninggalkan meja tanpa izin atau pergantian shift.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <span class="grid size-8 place-items-center rounded-xl bg-amber-100 text-amber-700 font-bold text-xs">🔔</span>
                <h4 class="mt-3 text-sm font-bold text-slate-900">Antrian Jam Sibuk</h4>
                <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                    Saat traffic pesan padat (12.00 - 14.00 & 19.00 - 21.00), kirim salam pengakuan cepat agar pelanggan tahu sedang diproses.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB: SOP ETIKA & 3S -->
    @if (($isSuperAdmin && $activeTab === 'sop_supervisi') || (! $isSuperAdmin && $activeTab === 'sop_etika'))
    <div class="space-y-6 {{ ($isSuperAdmin && $activeTab === 'sop_supervisi') ? 'pt-6 border-t border-slate-200' : '' }}">
        <div class="rounded-3xl border border-amber-200/70 bg-gradient-to-br from-amber-50/60 via-white to-orange-50/40 p-6 sm:p-8 shadow-xs">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 text-white px-2.5 py-1 text-xs font-black">
                    SOP PILAR 3: ETIKA PELAYANAN & 3S
                </span>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                    Standar Etika, Keramahan & Budaya 3S
                </h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Terapkan <strong class="text-slate-900">Senyum, Salam, Sapa</strong> dalam setiap interaksi digital. Pelayanan yang hangat membangun loyalitas jangka panjang pelanggan Gadgetmart.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-700">1. Senyum (Smile)</span>
                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                    Gunakan intonasi ramah dalam pemilihan kata dan sisipkan emoji yang wajar (😊, 🙏, ✨) agar nuansa chat terasa bersahabat dan tidak kaku.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-700">2. Salam (Greeting)</span>
                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                    Sesuaikan waktu (Selamat pagi / siang / sore) dan sebutkan nama toko Gadgetmart pada pesan pertama sebagai identitas resmi brand.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-700">3. Sapa (Personalize)</span>
                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                    Gunakan panggilan "Kak [Nama]" yang santun. Hindari memanggil hanya dengan "Gan", "Bro", atau "Bos" agar tetap profesional.
                </p>
            </div>
        </div>

        <!-- Handling Complaints -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
            <h3 class="text-base font-bold text-slate-900">Protokol Penanganan Komplain & Kendala (Metode L.A.S.T)</h3>
            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="font-black text-slate-900 block text-xs mb-1">L - Listen</span>
                    <span class="text-slate-600 leading-relaxed">Dengarkan dan baca keluhan konsumen secara tuntas tanpa menyela atau membantah.</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="font-black text-slate-900 block text-xs mb-1">A - Apologize</span>
                    <span class="text-slate-600 leading-relaxed">Sampaikan permohonan maaf atas ketidaknyamanan yang dialami pelanggan dengan tulus.</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="font-black text-slate-900 block text-xs mb-1">S - Solve</span>
                    <span class="text-slate-600 leading-relaxed">Berikan opsi solusi nyata (cek teknisi, klaim garansi, tukar unit sesuai syarat).</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="font-black text-slate-900 block text-xs mb-1">T - Thank</span>
                    <span class="text-slate-600 leading-relaxed">Ucapkan terima kasih atas masukan berharga demi perbaikan layanan Gadgetmart.</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB: SOP PENAWARAN PROMO & UPSELLING -->
    @if (($isSuperAdmin && $activeTab === 'sop_supervisi') || (! $isSuperAdmin && $activeTab === 'sop_promo'))
    <div class="space-y-6 {{ ($isSuperAdmin && $activeTab === 'sop_supervisi') ? 'pt-6 border-t border-slate-200' : '' }}">
        <div class="rounded-3xl border border-indigo-200/70 bg-gradient-to-br from-indigo-50/60 via-white to-purple-50/40 p-6 sm:p-8 shadow-xs">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 text-white px-2.5 py-1 text-xs font-black">
                    SOP PILAR 4: PENAWARAN PROMO & UPSELLING
                </span>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                    Kepatuhan Promo & Strategi Cross-Selling
                </h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Setiap admin sales wajib mengetahui promo aktif harian sebelum mulai shift kerja dan aktif menawarkan bundling aksesoris pelengkap saat konsumen memilih unit utama.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <h4 class="text-sm font-bold text-slate-900">Kepatuhan Promo Resmi</h4>
                <ul class="mt-3 space-y-2 text-xs text-slate-600">
                    <li class="flex items-start gap-2">
                        <span class="text-indigo-600 font-bold">1.</span>
                        <span>Cek info promo/katalog terbaru di grup briefing atau materi broadcast sebelum jam shift dimulai.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-indigo-600 font-bold">2.</span>
                        <span>Jelaskan syarat dan ketentuan promo secara transparan (misal cashback metode bayar atau periode promo).</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-indigo-600 font-bold">3.</span>
                        <span>Dilarang menjanjikan diskon pribadi di luar ketentuan resmi perusahaan.</span>
                    </li>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <h4 class="text-sm font-bold text-slate-900">Formula Bundling & Cross-Selling Aksesoris</h4>
                <div class="mt-3 space-y-2 text-xs text-slate-600">
                    <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-2.5">
                        <span class="font-bold text-indigo-900 block">Unit Smartphone Baru &rarr; Paket Proteksi</span>
                        <span class="text-[11px] text-slate-600">Tawarkan langsung Tempered Glass + Casing Anti-Drop + Adaptor Fast Charging jika tidak disertakan dalam boks.</span>
                    </div>
                    <div class="rounded-xl bg-purple-50 border border-purple-100 p-2.5">
                        <span class="font-bold text-purple-900 block">Unit Tablet / iPad &rarr; Paket Produktivitas</span>
                        <span class="text-[11px] text-slate-600">Tawarkan Stylus Pen + Bluetooth Keyboard Case untuk menunjang aktivitas kerja dan belajar.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB: SOP LEADS, KEEP & AR -->
    @if (($isSuperAdmin && $activeTab === 'sop_supervisi') || (! $isSuperAdmin && $activeTab === 'sop_leads'))
    <div class="space-y-6 {{ ($isSuperAdmin && $activeTab === 'sop_supervisi') ? 'pt-6 border-t border-slate-200' : '' }}">
        <div class="rounded-3xl border border-purple-200/70 bg-gradient-to-br from-purple-50/60 via-white to-pink-50/40 p-6 sm:p-8 shadow-xs">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-purple-600 text-white px-2.5 py-1 text-xs font-black">
                    SOP PILAR 5: LEADS, KEEP & TRACKING AR
                </span>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                    Alur Pencatatan Leads, Keep Barang & Monitoring AR
                </h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Tertib administrasi menjamin kepastian stok dan meminimalisir piutang macet. Catat setiap prospek pada menu Leads Customer dan Workspace Sosmed.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-purple-700">1. Catat Leads Prospek</span>
                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                    Setiap konsumen yang menanyakan ketersediaan dan tertarik namun belum closing, wajib diinput nomor WA dan produk minatnya ke menu Leads Customer.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-purple-700">2. Format Keep Barang</span>
                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                    Konsumen yang meminta simpan unit wajib mengisi Format Keep lengkap dengan nama, nomor HP, tipe barang, cabang, dan batas waktu pengambilan.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-purple-700">3. Cek Status AR & Follow-Up</span>
                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                    Cek secara berkala di menu Cek Status AR / Tracking AR. Lakukan konfirmasi ramah pada H-1 sebelum batas waktu keep berakhir.
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
