@extends('layouts.app')
@section('title', 'Setup WhatsApp Gateway (One Gateway)')
@section('content')
<div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                <span class="size-2 rounded-full {{ !empty($baileysStatus['online']) ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' }}"></span>
                {{ !empty($baileysStatus['online']) ? 'One Gateway Aktif' : 'Perlu Scan QR' }}
            </span>
            <span class="text-xs text-slate-400">•</span>
            <p class="text-xs font-medium text-slate-500">Pengaturan Sistem & WhatsApp</p>
        </div>
        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">Setup WhatsApp Gateway</h1>
        <p class="mt-1 text-xs text-slate-500">
            Satu gateway unifikasi (Baileys Bot) untuk <strong>WA Broadcast (Outreach)</strong>, <strong>Inbox Follow-Up</strong>, dan <strong>Reaksi Keep PIC</strong>.
        </p>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" id="btn-manual-refresh" onclick="checkStatusBaileys(true)" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-xs transition hover:bg-slate-50">
            <svg id="refresh-icon" class="size-4 text-emerald-600 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span id="refresh-text">Periksa Status</span>
        </button>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-xs">
    <svg class="mt-0.5 size-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="text-xs font-semibold leading-relaxed">{{ session('success') }}</div>
</div>
@endif

@if(session('error'))
<div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-800 shadow-xs">
    <div class="flex items-start gap-3">
        <svg class="mt-0.5 size-5 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div class="flex-1">
            <div class="text-xs font-bold text-rose-900">Perhatian:</div>
            <div class="mt-1 text-xs leading-relaxed text-rose-800">{{ session('error') }}</div>
        </div>
    </div>
</div>
@endif

@if(! $isSuperAdmin)
<div class="mb-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 shadow-xs">
    <svg class="mt-0.5 size-5 shrink-0 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div class="text-xs leading-relaxed">
        <strong>Mode Lihat:</strong> Anda sedang login sebagai Staff/Admin. Anda dapat melihat status kesiapan gateway WhatsApp di bawah. Pengaturan URL server & uji coba kirim pesan hanya dapat dilakukan oleh Superadmin.
    </div>
</div>
@endif

<div class="grid gap-6 lg:grid-cols-12">
    <!-- ========================================================================= -->
    <!-- KOLOM KIRI (7 SPAN): STATUS GATEWAY & LIVE QR CODE -->
    <!-- ========================================================================= -->
    <div class="space-y-6 lg:col-span-7">
        <!-- CARD 1: STATUS PERANGKAT / DEVICE -->
        <section class="dcms-card p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="font-black text-slate-900">Status Perangkat WhatsApp</h2>
                        <p class="text-xs text-slate-500">Koneksi socket aktif Baileys Multi-Device</p>
                    </div>
                </div>

                <div id="badge-status-container">
                    @if(!empty($baileysStatus['online']))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                            <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Terhubung (Online)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 border border-rose-200">
                            <span class="size-2 rounded-full bg-rose-500"></span>
                            Belum Terhubung
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nomor WhatsApp Bot</p>
                    <p id="info-phone" class="mt-1 font-mono text-sm font-extrabold text-slate-900">
                        {{ $baileysStatus['phone'] ?? '+62 851-1142-6699' }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Perangkat</p>
                    <p id="info-name" class="mt-1 text-sm font-bold text-slate-900">
                        {{ $baileysStatus['name'] ?? 'DCMS Bot (+62 851-1142-6699)' }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Status Socket State</p>
                    <p id="info-state" class="mt-1 font-mono text-xs font-bold text-slate-700">
                        {{ $baileysStatus['status'] ?? 'DISCONNECTED' }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Penyimpanan Sesi (Anti-Logout)</p>
                    <p class="mt-1 text-xs font-bold {{ $baileysHasSavedSession ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $baileysHasSavedSession ? 'Tersimpan di Database SQL' : 'Belum Tersimpan' }}
                    </p>
                    @if($baileysAuthSavedAt)
                        <p class="mt-0.5 text-[10px] text-slate-400">Sinkronisasi: {{ $baileysAuthSavedAt }}</p>
                    @endif
                </div>
            </div>
        </section>

        <!-- CARD 2: QR CODE LIVE RESCAN CONTAINER -->
        <section id="qr-section" class="dcms-card p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="font-black text-slate-900">Pindai QR Code WhatsApp</h2>
                    <p class="text-xs text-slate-500">Scan QR Code ini dari ponsel jika WhatsApp terputus atau perlu re-login</p>
                </div>
                <span id="qr-badge-indicator" class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ !empty($baileysStatus['online']) ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700' }}">
                    <span class="size-1.5 rounded-full {{ !empty($baileysStatus['online']) ? 'bg-emerald-500' : 'bg-indigo-500 animate-ping' }}"></span>
                    <span id="qr-badge-text">{{ !empty($baileysStatus['online']) ? 'Sesi Terhubung' : 'Live Auto-Refresh' }}</span>
                </span>
            </div>

            <!-- TAMPILAN JIKA SUDAH CONNECTED -->
            <div id="view-connected" class="{{ !empty($baileysStatus['online']) ? '' : 'hidden' }} py-8 text-center">
                <div class="mx-auto flex size-20 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 shadow-inner">
                    <svg class="size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="mt-4 text-base font-black text-slate-900">WhatsApp Gateway Aktif & Siap Digunakan</h3>
                <p class="mx-auto mt-1 max-w-md text-xs leading-relaxed text-slate-500">
                    Nomor WhatsApp bot <strong class="text-slate-700">{{ $baileysStatus['phone'] ?? '+62 851-1142-6699' }}</strong> telah terhubung. Semua pesan Broadcast, Inbox Follow-Up, dan Reaksi Tim FU berjalan otomatis via gateway ini.
                </p>
                <div class="mt-5 inline-flex items-center gap-2 rounded-xl bg-slate-50 border border-slate-200 px-4 py-2 text-xs text-slate-600 font-medium">
                    <svg class="size-4 text-emerald-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    Sesi aman tersimpan otomatis ke database DCMS
                </div>
            </div>

            <!-- TAMPILAN JIKA BELUM CONNECTED / MENUNGGU SCAN QR -->
            <div id="view-waiting-qr" class="{{ !empty($baileysStatus['online']) ? 'hidden' : '' }} py-4">
                <div class="flex flex-col items-center justify-center">
                    <!-- FRAME QR CODE -->
                    <div class="relative flex items-center justify-center rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                        <div id="qr-loading-spinner" class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 backdrop-blur-xs rounded-2xl z-10 {{ !empty($baileysStatus['qr']) ? 'hidden' : '' }}">
                            <div class="size-8 animate-spin rounded-full border-3 border-emerald-600 border-t-transparent"></div>
                            <p class="mt-2 text-xs font-semibold text-slate-600">Memuat QR Code...</p>
                        </div>

                        <img id="qr-image" 
                             src="{{ $baileysStatus['qr'] ?? '' }}" 
                             alt="QR Code WhatsApp" 
                             class="size-64 rounded-lg object-contain {{ empty($baileysStatus['qr']) ? 'invisible' : '' }}" />
                    </div>

                    <!-- PANDUAN SCAN -->
                    <div class="mt-5 w-full max-w-md rounded-xl bg-indigo-50/70 border border-indigo-100 p-4 text-indigo-950">
                        <h4 class="text-xs font-extrabold uppercase tracking-wide text-indigo-900 mb-2 flex items-center gap-1.5">
                            <svg class="size-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Cara Memindai QR Code:
                        </h4>
                        <ol class="list-decimal pl-4 space-y-1 text-xs text-indigo-900/80 leading-relaxed font-medium">
                            <li>Buka aplikasi <strong>WhatsApp</strong> di ponsel dengan nomor <strong>+62 851-1142-6699</strong>.</li>
                            <li>Ketuk menu titik tiga <span class="font-bold">⋮</span> (Android) atau <strong>Pengaturan</strong> (iOS).</li>
                            <li>Pilih <strong>Perangkat Tertaut (Linked Devices)</strong> &gt; ketuk <strong>Tautkan Perangkat</strong>.</li>
                            <li>Arahkan kamera ponsel Anda ke gambar QR Code di atas.</li>
                        </ol>
                        <p class="mt-3 text-[11px] text-indigo-700/90 font-medium italic">
                            * QR code akan otomatis berganti jika kedaluwarsa. Setelah discan, status akan langsung berubah hijau tanpa perlu refresh halaman.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- ========================================================================= -->
    <!-- KOLOM KANAN (5 SPAN): FORM UJI COBA & KONFIGURASI SERVER -->
    <!-- ========================================================================= -->
    <div class="space-y-6 lg:col-span-5">
        <!-- CARD 3: UJI COBA KIRIM PESAN -->
        <section class="dcms-card p-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div>
                    <h2 class="font-black text-slate-900">Uji Coba Kirim Pesan WA</h2>
                    <p class="text-xs text-slate-500">Tes kirim pesan langsung via Baileys Bot</p>
                </div>
            </div>

            <form action="{{ route('settings.watzap.test_send') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="test_phone" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-700">
                        Nomor WhatsApp Tujuan <span class="text-rose-500">*</span>
                    </label>
                    <input id="test_phone" 
                           name="phone" 
                           type="text" 
                           required 
                           value="{{ old('phone') }}" 
                           placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx" 
                           @disabled(! $isSuperAdmin)
                           class="dcms-input text-xs font-mono">
                    <p class="mt-1 text-[11px] text-slate-400">Masukkan nomor yang ingin menerima pesan tes.</p>
                </div>

                <div>
                    <label for="test_message" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-700">
                        Isi Pesan Uji Coba <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="test_message" 
                              name="message" 
                              rows="3" 
                              required 
                              @disabled(! $isSuperAdmin)
                              placeholder="Halo! Ini adalah pesan uji coba dari One Gateway WhatsApp DCMS." 
                              class="dcms-input text-xs leading-relaxed">{{ old('message', 'Halo! Ini adalah pesan uji coba dari One Gateway WhatsApp DCMS.') }}</textarea>
                </div>

                <div class="pt-1">
                    <button type="submit" 
                            @disabled(! $isSuperAdmin)
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800 disabled:opacity-50">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Kirim Pesan Uji Coba</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- CARD 4: PENGATURAN SERVER BAILEYS (SUPERADMIN) -->
        <section class="dcms-card p-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h2 class="font-black text-slate-900">Konfigurasi Server Gateway</h2>
                    <p class="text-xs text-slate-500">Endpoint HTTP API Baileys Bot Node.js</p>
                </div>
            </div>

            <form action="{{ route('settings.watzap.update') }}" method="POST" class="mt-4 space-y-4">
                @csrf

                <div class="flex items-center justify-between rounded-xl bg-slate-50 border border-slate-200 p-3">
                    <div>
                        <p class="text-xs font-bold text-slate-800">Status Gateway Baileys</p>
                        <p class="text-[11px] text-slate-500">Aktifkan untuk One Gateway WhatsApp DCMS</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="baileys_bot_enabled" value="1" {{ old('baileys_bot_enabled', $baileysBotEnabled) ? 'checked' : '' }} @disabled(! $isSuperAdmin) class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <div>
                    <label for="baileys_bot_url" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-700">
                        URL Server Baileys Bot <span class="text-rose-500">*</span>
                    </label>
                    <input id="baileys_bot_url" 
                           name="baileys_bot_url" 
                           type="text" 
                           value="{{ old('baileys_bot_url', $baileysBotUrl) }}" 
                           @disabled(! $isSuperAdmin) 
                           placeholder="https://dcms-baileys-bot.onrender.com atau http://127.0.0.1:3000" 
                           class="dcms-input font-mono text-xs">
                    <p class="mt-1 text-[11px] text-slate-400">Endpoint API bot tempat service Baileys berjalan.</p>
                </div>

                <div>
                    <label for="baileys_bot_api_key" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-700">
                        X-Api-Key Baileys Bot
                    </label>
                    <input id="baileys_bot_api_key" 
                           name="baileys_bot_api_key" 
                           type="text" 
                           value="{{ old('baileys_bot_api_key', $baileysBotApiKey) }}" 
                           @disabled(! $isSuperAdmin) 
                           placeholder="dcms-secret-baileys-key" 
                           class="dcms-input font-mono text-xs">
                    <p class="mt-1 text-[11px] text-slate-400">Kunci otentikasi header X-Api-Key bot.</p>
                </div>

                @if($isSuperAdmin)
                <div class="pt-2">
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-500">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Konfigurasi</span>
                    </button>
                </div>
                @endif
            </form>
        </section>

        <!-- CARD 5: INFORMASI INTEGRASI -->
        <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-slate-700">
            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 mb-3 flex items-center gap-2">
                <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Arsitektur One Gateway WhatsApp
            </h4>
            <div class="space-y-2 text-xs leading-relaxed text-slate-600 font-medium">
                <div class="flex items-start gap-2">
                    <span class="text-emerald-600 font-bold">✓</span>
                    <span><strong>WA Broadcast (Outreach):</strong> Otomatis mengirim pesan promosi & edukasi menggunakan nomor Baileys bot tanpa batas third-party.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-emerald-600 font-bold">✓</span>
                    <span><strong>Inbox Follow-Up Chat:</strong> Mengirim & menerima chat dua arah langsung dengan pelanggan & tim FU.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-emerald-600 font-bold">✓</span>
                    <span><strong>Reaksi Jempol Tim FU:</strong> Mendeteksi balasan "ya" dan memberi reaksi 👍 secara otomatis.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-emerald-600 font-bold">✓</span>
                    <span><strong>Anti-Logout Session:</strong> Data sesi disimpan otomatis ke database MySQL DCMS, sehingga aman saat restart service.</span>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
let pollingTimer = null;
let isPollingActive = true;

async function checkStatusBaileys(isManual = false) {
    const refreshIcon = document.getElementById('refresh-icon');
    const refreshText = document.getElementById('refresh-text');
    
    if (isManual && refreshIcon) {
        refreshIcon.classList.add('animate-spin');
        if (refreshText) refreshText.textContent = 'Memeriksa...';
    }

    try {
        const res = await fetch('{{ route('settings.watzap.check_baileys') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        if (!res.ok) throw new Error('Network response not ok');
        const data = await res.json();

        // Update Phone & Name & State
        const infoPhone = document.getElementById('info-phone');
        const infoName = document.getElementById('info-name');
        const infoState = document.getElementById('info-state');
        const badgeStatusContainer = document.getElementById('badge-status-container');
        const viewConnected = document.getElementById('view-connected');
        const viewWaitingQr = document.getElementById('view-waiting-qr');
        const qrImage = document.getElementById('qr-image');
        const qrLoadingSpinner = document.getElementById('qr-loading-spinner');
        const qrBadgeIndicator = document.getElementById('qr-badge-indicator');
        const qrBadgeText = document.getElementById('qr-badge-text');

        if (infoPhone && data.phone) infoPhone.textContent = data.phone;
        if (infoName && data.name) infoName.textContent = data.name;
        if (infoState && data.status) infoState.textContent = data.status;

        if (data.success) {
            // STATE: CONNECTED
            if (badgeStatusContainer) {
                badgeStatusContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Terhubung (Online)
                    </span>
                `;
            }
            if (viewConnected) viewConnected.classList.remove('hidden');
            if (viewWaitingQr) viewWaitingQr.classList.add('hidden');
            if (qrBadgeIndicator) {
                qrBadgeIndicator.className = 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-emerald-50 text-emerald-700';
            }
            if (qrBadgeText) qrBadgeText.textContent = 'Sesi Terhubung';
        } else {
            // STATE: DISCONNECTED / WAITING_QR
            if (badgeStatusContainer) {
                badgeStatusContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 border border-rose-200">
                        <span class="size-2 rounded-full bg-rose-500"></span>
                        Belum Terhubung
                    </span>
                `;
            }
            if (viewConnected) viewConnected.classList.add('hidden');
            if (viewWaitingQr) viewWaitingQr.classList.remove('hidden');

            if (data.hasQR && data.qr) {
                if (qrImage) {
                    qrImage.src = data.qr;
                    qrImage.classList.remove('invisible');
                }
                if (qrLoadingSpinner) qrLoadingSpinner.classList.add('hidden');
                if (qrBadgeText) qrBadgeText.textContent = 'Silakan Scan QR';
            } else {
                if (qrLoadingSpinner) qrLoadingSpinner.classList.remove('hidden');
                if (qrBadgeText) qrBadgeText.textContent = 'Menyiapkan QR...';
            }
        }
    } catch (e) {
        console.warn('Gagal cek status Baileys:', e);
    } finally {
        if (isManual && refreshIcon) {
            refreshIcon.classList.remove('animate-spin');
            if (refreshText) refreshText.textContent = 'Periksa Status';
        }
    }
}

// Auto-polling tiap 5 detik jika halaman aktif
document.addEventListener('DOMContentLoaded', () => {
    pollingTimer = setInterval(() => {
        if (document.visibilityState === 'visible') {
            checkStatusBaileys(false);
        }
    }, 5000);
});

// Refresh saat tab browser kembali aktif
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        checkStatusBaileys(true);
    }
});
</script>
@endsection
