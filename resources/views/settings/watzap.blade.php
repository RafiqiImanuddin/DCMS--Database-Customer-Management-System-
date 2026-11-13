@extends('layouts.app')
@section('title', 'Setup WhatsApp (WatZap)')
@section('content')
<div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-emerald-600">Pengaturan Sistem & Gateway</p>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Setup WhatsApp (WatZap.id)</h1>
        <p class="mt-1 text-xs text-slate-500">Konfigurasi gateway WhatsApp untuk WA Broadcast (Outreach) dan WA Inbox Follow-Up (CS & PIC FU).</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="https://api-docs.watzap.id/" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Dokumentasi API
        </a>
        <a href="https://app.watzap.id" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-500">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Member Area WatZap
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm">
    <svg class="mt-0.5 size-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="text-xs font-semibold leading-relaxed">{{ session('success') }}</div>
</div>
@endif

@if(session('error'))
<div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-800 shadow-sm">
    <div class="flex items-start gap-3">
        <svg class="mt-0.5 size-5 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div class="flex-1">
            <div class="text-xs font-bold text-rose-900">Perhatian: Pengiriman Pesan Belum Berhasil</div>
            <div class="mt-1 text-xs leading-relaxed text-rose-800">{{ session('error') }}</div>
            @if(str_contains(session('error'), 'Not Started') || str_contains(session('error'), 'Offline') || str_contains(session('error'), 'Scan QR'))
            <div class="mt-3 flex items-center gap-2">
                <a href="https://app.watzap.id" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-rose-500">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Buka Member Area WatZap & Scan QR
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@if(! $isSuperAdmin)
<div class="mb-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 shadow-sm">
    <svg class="mt-0.5 size-5 shrink-0 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div class="text-xs leading-relaxed">
        <strong>Mode Lihat:</strong> Anda sedang login sebagai Staff/Admin. Anda dapat melihat status kesiapan gateway WhatsApp di bawah. Kredensial API Key & Number Key hanya dapat diubah oleh Superadmin.
    </div>
</div>
@endif

<!-- TAB NAVIGATION: WA BROADCAST vs WA INBOX FOLLOW UP vs FONNTE -->
<div class="mb-6 flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
    <a href="{{ route('settings.watzap.index', ['tab' => 'broadcast']) }}" class="inline-flex items-center gap-2.5 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-2xs {{ $activeTab === 'broadcast' ? 'bg-emerald-600 text-white shadow-emerald-600/20' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
        <svg class="size-4 {{ $activeTab === 'broadcast' ? 'text-white' : 'text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 0 1-3.417.592l-2.147-6.15M18 13a3 3 0 1 0 0-6M5.436 13.683A4.001 4.001 0 0 1 7 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 0 1-1.564-.317z"/></svg>
        <span>WA Broadcast (Outreach)</span>
        <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $activeTab === 'broadcast' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
            {{ $isConfiguredBroadcast ? 'Aktif' : 'Belum Set' }}
        </span>
    </a>

    <a href="{{ route('settings.watzap.index', ['tab' => 'inbox']) }}" class="inline-flex items-center gap-2.5 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-2xs {{ $activeTab === 'inbox' ? 'bg-emerald-600 text-white shadow-emerald-600/20' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
        <svg class="size-4 {{ $activeTab === 'inbox' ? 'text-white' : 'text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.84-.84c.125-.68.39-1.53.844-2.228C4.015 16.544 3 14.39 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
        <span>WA Inbox Follow Up (CS & PIC)</span>
        <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $activeTab === 'inbox' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
            {{ $isConfiguredInbox ? 'Aktif' : 'Belum Set' }}
        </span>
    </a>

    <a href="{{ route('settings.watzap.index', ['tab' => 'fonnte']) }}" class="inline-flex items-center gap-2.5 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-2xs {{ $activeTab === 'fonnte' ? 'bg-emerald-600 text-white shadow-emerald-600/20' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
        <svg class="size-4 {{ $activeTab === 'fonnte' ? 'text-white' : 'text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
        <span>Fonnte (Bot Keep & Webhook)</span>
        <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $activeTab === 'fonnte' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
            {{ $fonnteIsConfigured ? 'Aktif' : 'Belum Set' }}
        </span>
    </a>
</div>

@if($activeTab === 'broadcast')
<!-- ========================================================================= -->
<!-- TAB 1: PENGATURAN WA BROADCAST -->
<!-- ========================================================================= -->
<div class="grid gap-6 lg:grid-cols-3">
    <!-- Kolom Kiri: Form Konfigurasi Broadcast (2 kolom span) -->
    <div class="space-y-6 lg:col-span-2">
        <section class="dcms-card p-6">
            <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="font-black text-slate-900">Kredensial API WatZap (WA Broadcast)</h2>
                    <p class="mt-1 text-xs text-slate-500">Kredensial ini digunakan untuk mengeksekusi pengiriman pesan broadcast WhatsApp secara otomatis ke pelanggan (Master Customer & Leads).</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $isConfiguredBroadcast ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    <span class="size-2 rounded-full {{ $isConfiguredBroadcast ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ $isConfiguredBroadcast ? 'Terkonfigurasi' : 'Belum Lengkap' }}
                </span>
            </div>

            <form method="POST" action="{{ route('settings.watzap.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="target_tab" value="broadcast">
                <div>
                    <label for="watzap_api_key" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">
                        API Key (Secret Key) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="watzap_api_key" name="watzap_api_key" type="password" autocomplete="new-password" data-lpignore="true" value="{{ old('watzap_api_key', $apiKey) }}" @disabled(! $isSuperAdmin) placeholder="Contoh: wt_sec_xxxxxxxxxxxx" class="dcms-input pr-10 font-mono text-xs disabled:bg-slate-100 disabled:text-slate-500">
                        <button type="button" onclick="togglePasswordVisibility('watzap_api_key')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">Didapatkan dari WatZap Member Area &rarr; WhatsApp (WA Unofficial) &rarr; Tab API.</p>
                </div>

                <div>
                    <label for="watzap_number_key" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">
                        Number Key Broadcast (Device Key) <span class="text-rose-500">*</span>
                    </label>
                    <input id="watzap_number_key" name="watzap_number_key" type="text" value="{{ old('watzap_number_key', $numberKey) }}" @disabled(! $isSuperAdmin) placeholder="Contoh: nk_xxxxxxxxxxxx" class="dcms-input font-mono text-xs disabled:bg-slate-100 disabled:text-slate-500">
                    <p class="mt-1 text-[11px] text-slate-400">ID Number Key khusus untuk nomor WhatsApp pengirim broadcast promosi toko yang sudah terhubung (Online) di WatZap.</p>
                    @if(!empty($availableSenders))
                    <div class="mt-2 flex flex-wrap items-center gap-1.5 pt-1">
                        <span class="text-[11px] font-semibold text-slate-500">Pilih cepat nomor terhubung:</span>
                        @foreach($availableSenders as $s)
                            <button type="button" 
                                onclick="document.getElementById('watzap_number_key').value='{{ $s['key'] }}'"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-2xs transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700">
                                <span class="size-2 rounded-full {{ $s['is_online'] ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                <span>{{ $s['phone'] ?: $s['key'] }}</span>
                                <span class="text-[10px] text-slate-400">({{ $s['status'] }})</span>
                            </button>
                        @endforeach
                    </div>
                    @endif
                </div>

                @if($isSuperAdmin)
                <div class="flex items-center justify-between pt-3">
                    <button type="button" id="btn-check-api" onclick="checkApiConnection()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Tes Koneksi API
                    </button>
                    <button type="submit" class="dcms-primary-button px-6 py-2.5 text-xs font-bold">
                        Simpan Pengaturan Broadcast
                    </button>
                </div>
                @endif
            </form>

            <div id="check-result-container" class="mt-4 hidden rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-700" id="check-result-title">Hasil Pemeriksaan API:</span>
                    <span id="check-result-badge" class="rounded px-2 py-0.5 text-[10px] font-bold"></span>
                </div>
                <div id="check-result-message" class="mt-2 text-slate-600"></div>
                <pre id="check-result-json" class="mt-2 max-h-40 overflow-auto rounded bg-slate-900 p-2 text-[10px] text-emerald-300 font-mono"></pre>
            </div>
        </section>

        <!-- Uji Coba Kirim Pesan Broadcast (Hanya jika Super Admin) -->
        @if($isSuperAdmin)
        <section class="dcms-card p-6">
            <div class="mb-4 border-b border-slate-100 pb-3">
                <h2 class="font-black text-slate-900">Uji Coba Kirim Pesan WA Broadcast</h2>
                <p class="mt-1 text-xs text-slate-500">Kirim pesan WhatsApp percobaan secara instan untuk memastikan integrasi broadcast berjalan lancar.</p>
            </div>
            <form method="POST" action="{{ route('settings.watzap.test_send') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="channel" value="broadcast">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="test_phone" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">Nomor WhatsApp Tujuan</label>
                        <input id="test_phone" name="phone" type="text" required placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx" value="{{ old('phone') }}" class="dcms-input text-xs">
                    </div>
                    <div>
                        <label for="test_message" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">Isi Pesan Uji Coba</label>
                        <input id="test_message" name="message" type="text" required placeholder="Halo, ini tes pesan broadcast!" value="{{ old('message', 'Halo! Ini adalah pesan uji coba integrasi WA Broadcast WatZap dari DCMS Gadgetmart.') }}" class="dcms-input text-xs">
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" @disabled(! $isConfiguredBroadcast) class="dcms-primary-button px-5 py-2.5 text-xs font-bold disabled:cursor-not-allowed disabled:opacity-50">
                        Kirim Pesan Uji Coba Broadcast
                    </button>
                </div>
            </form>
        </section>
        @endif
    </div>

    <!-- Kolom Kanan: Status Device & Langkah Setup Broadcast -->
    <div class="space-y-6">
        <section class="dcms-card p-6">
            <h2 class="font-black text-slate-900">Status Device WA Broadcast</h2>
            <p class="mt-1 text-xs text-slate-500">Nomor WhatsApp resmi broadcast toko di WatZap.</p>

            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Status Pengirim</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold {{ ($deviceStatusBroadcast['online'] ?? false) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                        <span class="size-2 rounded-full {{ ($deviceStatusBroadcast['online'] ?? false) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $deviceStatusBroadcast['status'] ?? ($isConfiguredBroadcast ? 'Cek Koneksi' : 'Belum Terhubung') }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Nomor Broadcast Toko</span>
                    <span class="font-mono text-xs font-bold text-slate-900">{{ $deviceStatusBroadcast['phone'] ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Nama Akun WatZap</span>
                    <span class="text-xs font-semibold text-slate-900">{{ $deviceStatusBroadcast['account_name'] ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Paket Langganan</span>
                    <span class="text-xs font-bold text-brand-700">{{ $deviceStatusBroadcast['package'] ?? '-' }}</span>
                </div>
            </div>

            <div class="mt-5 border-t border-slate-100 pt-4">
                <a href="{{ route('broadcasts.index') }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800">
                    <svg class="size-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/></svg>
                    Lihat Laporan Broadcast
                </a>
            </div>
        </section>

        <section class="dcms-card p-6">
            <h3 class="font-bold text-slate-900">Petunjuk Setup WA Broadcast</h3>
            <ul class="mt-3 space-y-2.5 text-xs text-slate-600">
                <li class="flex items-start gap-2">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-emerald-50 text-[11px] font-bold text-emerald-700">1</span>
                    <span>Login ke <a href="https://app.watzap.id" target="_blank" rel="noopener" class="font-semibold text-emerald-700 underline">Member Area WatZap</a> dan hubungkan nomor WhatsApp broadcast dengan scan QR Code.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-emerald-50 text-[11px] font-bold text-emerald-700">2</span>
                    <span>Buka tab <strong>API</strong> di WatZap, lalu salin <strong>API Key</strong> dan <strong>Number Key</strong> ke form di samping.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-emerald-50 text-[11px] font-bold text-emerald-700">3</span>
                    <span>Klik tombol <strong>Tes Koneksi API</strong> untuk memverifikasi keabsahan API Key.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-emerald-50 text-[11px] font-bold text-emerald-700">4</span>
                    <span>Kirim pesan uji coba ke nomor Anda sendiri. Jika masuk, fitur broadcast di menu Master Customer & Leads siap digunakan!</span>
                </li>
            </ul>
        </section>
    </div>
</div>

@elseif($activeTab === 'inbox')
<!-- ========================================================================= -->
<!-- TAB 2: PENGATURAN WA INBOX FOLLOW UP -->
<!-- ========================================================================= -->
<div class="grid gap-6 lg:grid-cols-3">
    <!-- Kolom Kiri: Form Konfigurasi Inbox Follow-Up (2 kolom span) -->
    <div class="space-y-6 lg:col-span-2">
        <section class="dcms-card p-6">
            <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="font-black text-slate-900">Kredensial Gateway (WA Inbox Follow-Up)</h2>
                    <p class="mt-1 text-xs text-slate-500">Kredensial ini digunakan untuk percakapan chat 2-arah (kirim teks, foto, dokumen) di menu <strong>Inbox Follow-Up</strong> dengan PIC FU & Pelanggan.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $isConfiguredInbox ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    <span class="size-2 rounded-full {{ $isConfiguredInbox ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ $isConfiguredInbox ? 'Terkonfigurasi' : 'Belum Lengkap' }}
                </span>
            </div>

            <form method="POST" action="{{ route('settings.watzap.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="target_tab" value="inbox">

                <!-- 0. ONE GATEWAY: BAILEYS BOT (NOMOR KHUSUS: REACTION & INBOX) -->
                <div class="rounded-2xl border-2 border-indigo-500/30 bg-gradient-to-br from-indigo-50/70 via-white to-slate-50 p-5 shadow-xs space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-indigo-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="grid size-8 place-items-center rounded-xl bg-indigo-600 text-white shadow-sm font-bold text-xs">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </span>
                            <div>
                                <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                    <span>One Gateway: Baileys Bot (Nomor Khusus Reaction & Inbox)</span>
                                    <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-[10px] font-black text-indigo-800">Rekomendasi</span>
                                </h3>
                                <p class="text-[11px] text-slate-500">Nomor khusus terpadu untuk auto-claim Keep via reaksi 👍 di 18 grup dan chat personal 2 arah dengan Tim Follow Up.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="baileys_bot_enabled" value="1" {{ old('baileys_bot_enabled', $baileysBotEnabled) ? 'checked' : '' }} @disabled(! $isSuperAdmin) class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ml-2 text-xs font-bold text-slate-800">Aktifkan Baileys</span>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="baileys_bot_url" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-700">
                                URL Server Baileys Bot <span class="text-rose-500">*</span>
                            </label>
                            <input id="baileys_bot_url" name="baileys_bot_url" type="text" value="{{ old('baileys_bot_url', $baileysBotUrl) }}" @disabled(! $isSuperAdmin) placeholder="http://127.0.0.1:3000 atau https://dcms-baileys-bot.onrender.com" class="dcms-input font-mono text-xs">
                            <p class="mt-1 text-[11px] text-slate-400">Endpoint HTTP API Baileys Bot (Render.com atau VPS).</p>
                        </div>
                        <div>
                            <label for="baileys_bot_api_key" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-700">
                                X-Api-Key Baileys Bot
                            </label>
                            <input id="baileys_bot_api_key" name="baileys_bot_api_key" type="password" autocomplete="new-password" data-lpignore="true" value="{{ old('baileys_bot_api_key', $baileysBotApiKey) }}" @disabled(! $isSuperAdmin) placeholder="dcms2026" class="dcms-input font-mono text-xs">
                            <p class="mt-1 text-[11px] text-slate-400">Kunci keamanan untuk header otentikasi X-Api-Key.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            @if($isSuperAdmin)
                            <button type="button" id="btn-check-baileys" onclick="checkBaileysConnection()" class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-300 bg-white px-3.5 py-2 text-xs font-bold text-indigo-800 shadow-2xs hover:bg-indigo-50 transition">
                                <svg class="size-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Tes Koneksi Baileys</span>
                            </button>
                            @endif
                            <a href="{{ $baileysBotUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                <span>Buka Web Dashboard Baileys (Scan QR)</span>
                            </a>
                        </div>
                    </div>
                    <div id="baileys-check-feedback" class="mt-2.5 hidden text-xs font-semibold"></div>
                </div>

                <!-- 1. DUAL-GATEWAY CADANGAN: WAHA VPS (Self-Hosted WhatsApp API) -->
                <div class="rounded-2xl border-2 border-emerald-500/30 bg-gradient-to-br from-emerald-50/70 via-white to-slate-50 p-5 shadow-xs space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-emerald-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="grid size-8 place-items-center rounded-xl bg-emerald-600 text-white shadow-sm font-bold text-xs">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                            <div>
                                <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                    <span>Gateway WhatsApp: WAHA VPS (High Performance & Quoted Reply)</span>
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-black text-emerald-800">Aktif</span>
                                </h3>
                                <p class="text-[11px] text-slate-500">Mendukung koneksi langsung super cepat, Native Quoted Reply WhatsApp, dan media resolusi tinggi tanpa batas pihak ketiga.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="waha_enabled" value="1" {{ old('waha_enabled', $wahaEnabled) ? 'checked' : '' }} @disabled(! $isSuperAdmin) class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span class="ml-2 text-xs font-bold text-slate-800">Aktifkan WAHA</span>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="waha_vps_url" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-700">
                                URL Server WAHA VPS <span class="text-rose-500">*</span>
                            </label>
                            <input id="waha_vps_url" name="waha_vps_url" type="text" value="{{ old('waha_vps_url', $wahaUrl) }}" @disabled(! $isSuperAdmin) placeholder="http://178.83.188.100:3088" class="dcms-input font-mono text-xs">
                            <p class="mt-1 text-[11px] text-slate-400">Endpoint HTTP API WAHA VPS yang terpasang.</p>
                        </div>
                        <div>
                            <label for="waha_session" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-700">
                                Nama Session WhatsApp <span class="text-rose-500">*</span>
                            </label>
                            <input id="waha_session" name="waha_session" type="text" value="{{ old('waha_session', $wahaSession) }}" @disabled(! $isSuperAdmin) placeholder="default" class="dcms-input font-mono text-xs">
                            <p class="mt-1 text-[11px] text-slate-400">Default: <code>default</code> (Nama sesi yang di-scan QR di WAHA).</p>
                        </div>
                    </div>

                    <div>
                        <label for="waha_api_key" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-700">
                            X-Api-Key WAHA VPS
                        </label>
                        <input id="waha_api_key" name="waha_api_key" type="password" autocomplete="new-password" data-lpignore="true" value="{{ old('waha_api_key', $wahaApiKey) }}" @disabled(! $isSuperAdmin) placeholder="gadgetmart123" class="dcms-input font-mono text-xs">
                        <p class="mt-1 text-[11px] text-slate-400">Kunci keamanan untuk header otentikasi X-Api-Key WAHA VPS.</p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            @if($isSuperAdmin)
                            <button type="button" id="btn-check-waha" onclick="checkWahaConnection()" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-white px-3.5 py-2 text-xs font-bold text-emerald-800 shadow-2xs hover:bg-emerald-50 transition">
                                <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Tes Koneksi WAHA VPS</span>
                            </button>
                            @endif
                            <a href="{{ $wahaUrl }}/dashboard" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                <span>Buka Web Console WAHA (Scan QR)</span>
                            </a>
                        </div>
                    </div>
                    <div id="waha-check-feedback" class="mt-2.5 hidden text-xs font-semibold"></div>
                </div>

                @if($isSuperAdmin)
                <div class="flex items-center justify-end pt-3 border-t border-slate-100">
                    <button type="submit" class="dcms-primary-button px-6 py-2.5 text-xs font-bold">
                        Simpan Pengaturan WAHA VPS
                    </button>
                </div>
                @endif
            </form>
        </section>
    </div>

    <!-- Kolom Kanan: Status Device & Panduan Inbox -->
    <div class="space-y-6">
        <!-- Status One Gateway: Baileys Bot (Nomor Khusus) -->
        <section class="dcms-card p-6 border-2 border-indigo-500/30 bg-gradient-to-br from-indigo-50/30 to-white">
            <div class="flex items-center justify-between">
                <h2 class="font-black text-slate-900 flex items-center gap-1.5">
                    <span>⚡ Status Baileys Bot</span>
                    <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-black text-indigo-800">One Gateway</span>
                </h2>
                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold {{ ($baileysStatus['online'] ?? false) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    <span class="size-2 rounded-full {{ ($baileysStatus['online'] ?? false) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    {{ ($baileysStatus['online'] ?? false) ? 'Terhubung' : 'Terputus' }}
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Nomor Khusus: Reaksi Keep (Tab 4) & Chat Personal Tim FU (Tab 9).</p>

            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-white/80 p-3">
                    <span class="text-xs text-slate-500">Nomor Khusus Gateway</span>
                    <span class="font-mono text-xs font-bold text-indigo-700">
                        {{ !empty($baileysStatus['phone']) ? $baileysStatus['phone'] : '-' }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-white/80 p-3">
                    <span class="text-xs text-slate-500">Nama Bot WhatsApp</span>
                    <span class="text-xs font-semibold text-slate-900">{{ $baileysStatus['name'] ?? 'Bot DCMS' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-white/80 p-3">
                    <span class="text-xs text-slate-500">Status Socket</span>
                    <span class="font-mono text-xs font-bold text-slate-900">{{ $baileysStatus['status'] ?? 'OFFLINE' }}</span>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ $baileysBotUrl }}" target="_blank" rel="noopener noreferrer" class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition hover:bg-indigo-700">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Buka Dashboard Bot / Scan QR
                </a>
            </div>
        </section>

        <!-- Status Gateway Utama: WAHA VPS -->
        <section class="dcms-card p-6 border-2 border-emerald-500/20">
            <div class="flex items-center justify-between">
                <h2 class="font-black text-slate-900">Status WAHA VPS (Primary)</h2>
                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold {{ ($wahaStatus['online'] ?? false) && ($wahaStatus['status'] ?? '') === 'WORKING' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    <span class="size-2 rounded-full {{ ($wahaStatus['online'] ?? false) && ($wahaStatus['status'] ?? '') === 'WORKING' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    {{ ($wahaStatus['online'] ?? false) ? ($wahaStatus['status'] ?? 'Online') : 'Offline' }}
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Gateway utama pengiriman & penerimaan chat Inbox FU.</p>

            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Nomor WhatsApp</span>
                    <span class="font-mono text-xs font-bold text-slate-900">
                        {{ !empty($wahaStatus['me']['id']) ? str_replace('@c.us', '', $wahaStatus['me']['id']) : '-' }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Nama Akun WhatsApp</span>
                    <span class="text-xs font-semibold text-slate-900">{{ $wahaStatus['me']['pushName'] ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Nama Sesi</span>
                    <span class="font-mono text-xs font-bold text-slate-900">{{ $wahaStatus['name'] ?? $wahaSession }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Quoted Reply</span>
                    <span class="text-xs font-bold text-emerald-700">Native WhatsApp Aktif</span>
                </div>
            </div>

            <div class="mt-5 border-t border-slate-100 pt-4">
                <a href="{{ \App\Support\RouteCipher::url('workspace-sosmed.index', ['tab' => 9]) }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800">
                    <svg class="size-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.84-.84c.125-.68.39-1.53.844-2.228C4.015 16.544 3 14.39 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                    Buka Modul Inbox Follow-Up
                </a>
            </div>
        </section>

        <section class="dcms-card p-6">
            <h3 class="font-bold text-slate-900">Cara Kerja Inbox Follow-Up</h3>
            <ul class="mt-3 space-y-2.5 text-xs text-slate-600">
                <li class="flex items-start gap-2">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-emerald-50 text-[11px] font-bold text-emerald-700">1</span>
                    <span>Admin atau CS membalas chat ke PIC FU melalui menu <strong>Inbox Follow-Up</strong> (bisa mengirim teks, foto brosur promo, atau dokumen penawaran).</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-emerald-50 text-[11px] font-bold text-emerald-700">2</span>
                    <span>Ketika PIC FU atau Pelanggan membalas chat di WhatsApp, WatZap mengirim notifikasi ke <strong>Webhook URL</strong> DCMS secara otomatis.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-emerald-50 text-[11px] font-bold text-emerald-700">3</span>
                    <span>Pesan baru langsung muncul di stream percakapan tanpa perlu me-refresh halaman, lengkap dengan pratinjau gambar atau dokumen yang dikirimkan.</span>
                </li>
            </ul>
        </section>
    </div>
</div>

@else
<!-- ========================================================================= -->
<!-- TAB 3: PENGATURAN FONNTE (BOT KEEP & WEBHOOK REALTIME SQL) -->
<!-- ========================================================================= -->
<div class="grid gap-6 lg:grid-cols-3">
    <!-- Kolom Kiri: Form Konfigurasi Token & Webhook Fonnte (2 kolom span) -->
    <div class="space-y-6 lg:col-span-2">
        <!-- 1. Form Kredensial Token Fonnte -->
        <section class="dcms-card p-6">
            <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="font-black text-slate-900">Kredensial API Fonnte (Bot Keep WhatsApp)</h2>
                    <p class="mt-1 text-xs text-slate-500">Kredensial ini digunakan untuk integrasi bot WhatsApp keep dan pengiriman balasan otomatis saat data format KEEP masuk.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $fonnteIsConfigured ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    <span class="size-2 rounded-full {{ $fonnteIsConfigured ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ $fonnteIsConfigured ? 'Terkonfigurasi' : 'Belum Lengkap' }}
                </span>
            </div>

            <form method="POST" action="{{ route('settings.watzap.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="target_tab" value="fonnte">

                <!-- Token Fonnte -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="fonnte_token" class="block text-xs font-bold uppercase tracking-wide text-slate-700">
                            Token Fonnte <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-500">Ambil dari <a href="https://md.fonnte.com/device" target="_blank" class="font-bold text-emerald-600 underline">Fonnte Device ↗</a></span>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <div class="relative flex-1">
                            <input type="password" id="fonnte_token" name="fonnte_token" value="{{ old('fonnte_token', $fonnteToken) }}" required placeholder="Contoh: NMGid9rgBKYAAyZ7wmSn" class="dcms-input pr-10 font-mono text-xs font-semibold w-full" {{ ! $isSuperAdmin ? 'disabled' : '' }}>
                            <button type="button" onclick="togglePasswordVisibility('fonnte_token')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition" title="Lihat/Sembunyikan Token">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        <button type="button" id="btn-check-fonnte" onclick="checkFonnteConnection()" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition shrink-0 shadow-2xs">
                            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <span>Periksa Status Device</span>
                        </button>
                    </div>
                    <div id="fonnte-check-feedback" class="mt-2.5 hidden text-xs font-semibold"></div>
                </div>

                <!-- Toggle Auto-Reply Konfirmasi Keep -->
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-800">Auto-Reply Konfirmasi Rekap Keep</span>
                            <p class="text-[11px] text-slate-500">Kirim pesan balasan otomatis via Fonnte ke pengirim/grup saat format KEEP berhasil direkap ke database SQL.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="fonnte_autoreply_enabled" value="0">
                            <input type="checkbox" name="fonnte_autoreply_enabled" value="1" class="sr-only peer" {{ $fonnteAutoreply === '1' ? 'checked' : '' }} {{ ! $isSuperAdmin ? 'disabled' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <!-- Template Pesan Balasan -->
                    <div class="pt-2 border-t border-slate-200/60">
                        <label for="fonnte_autoreply_message" class="mb-1 block text-xs font-bold text-slate-700">
                            Template Teks Pesan Balasan:
                        </label>
                        <textarea id="fonnte_autoreply_message" name="fonnte_autoreply_message" rows="5" class="dcms-input text-xs font-mono" {{ ! $isSuperAdmin ? 'disabled' : '' }}>{{ old('fonnte_autoreply_message', $fonnteAutoreplyMsg) }}</textarea>
                        <div class="mt-2 flex flex-wrap gap-1.5 text-[10px]">
                            <span class="text-slate-500 font-medium">Tag yang tersedia:</span>
                            <span class="rounded bg-emerald-100 px-1.5 py-0.5 font-mono font-bold text-emerald-800">{nama}</span>
                            <span class="rounded bg-emerald-100 px-1.5 py-0.5 font-mono font-bold text-emerald-800">{nomor}</span>
                            <span class="rounded bg-emerald-100 px-1.5 py-0.5 font-mono font-bold text-emerald-800">{barang}</span>
                            <span class="rounded bg-emerald-100 px-1.5 py-0.5 font-mono font-bold text-emerald-800">{tanggal}</span>
                            <span class="rounded bg-emerald-100 px-1.5 py-0.5 font-mono font-bold text-emerald-800">{remaks}</span>
                        </div>
                    </div>
                </div>

                @if($isSuperAdmin)
                <div class="flex items-center justify-end pt-3 border-t border-slate-100">
                    <button type="submit" class="dcms-primary-button px-6 py-2.5 text-xs font-bold">
                        Simpan Pengaturan Fonnte
                    </button>
                </div>
                @endif
            </form>
        </section>

        <!-- 2. Card URL Webhook Fonnte (Siap Pakai untuk Migrasi) -->
        <section class="dcms-card p-6 border-2 border-indigo-500/20 bg-gradient-to-br from-indigo-50/40 via-white to-slate-50">
            <div class="flex items-start gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-indigo-600 text-white font-black text-sm shadow-sm">
                    ⚡
                </span>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h2 class="font-black text-slate-900">URL Webhook Fonnte (Migrasi Realtime ke SQL)</h2>
                        <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-[10px] font-black text-indigo-800">Endpoint Aktif</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                        Pasang URL ini pada pengaturan webhook di akun Fonnte Anda agar setiap pesan format KEEP yang dikirim ke nomor WhatsApp Anda <strong>langsung tersimpan otomatis dan realtime ke tabel SQL <code>sosmed_keeps</code></strong> (menggantikan Google Spreadsheet).
                    </p>

                    <div class="mt-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Webhook URL Fonnte
                        </label>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            <input type="text" id="fonnte-webhook-url-input" value="{{ $fonnteWebhookUrl }}" class="dcms-input font-mono text-xs bg-slate-50 font-bold select-all flex-1 text-indigo-950 focus:bg-white" placeholder="Contoh: https://yourdomain.com/webhook/fonnte-keep">
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" onclick="copyFonnteWebhookUrl()" id="btn-copy-fonnte-webhook" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-500">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                    <span>Salin URL</span>
                                </button>
                                <button type="button" onclick="registerFonnteWebhookAuto()" id="btn-register-fonnte-webhook" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-500">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    <span>Daftarkan Otomatis ke Fonnte</span>
                                </button>
                            </div>
                        </div>
                        <div id="fonnte-webhook-feedback" class="hidden"></div>
                    </div>

                    <!-- Langkah Pemasangan di Dashboard Fonnte -->
                    <div class="mt-5 rounded-xl border border-indigo-100 bg-white p-4 space-y-2.5 text-xs text-slate-700">
                        <p class="font-bold text-slate-900 flex items-center gap-1.5">
                            <svg class="size-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                            Cara Pemasangan di Dashboard Fonnte:
                        </p>
                        <ol class="list-decimal pl-5 space-y-1.5 text-slate-600">
                            <li>Buka menu <a href="https://md.fonnte.com/device" target="_blank" class="font-bold text-indigo-600 underline">Fonnte Device</a> dan pilih device WhatsApp Anda.</li>
                            <li>Buka tab / menu <strong>Webhook</strong>.</li>
                            <li>Tempel (Paste) URL Webhook di atas ke kolom input URL Webhook Fonnte.</li>
                            <li>Pada pilihan Event, pastikan mencentang <strong>Incoming Message</strong>.</li>
                            <li>Klik <strong>Save / Simpan</strong>. Selesai! Pesan format KEEP akan otomatis masuk realtime ke SQL.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2B. Card Sinkronisasi Google Spreadsheet ke SQL -->
        <section class="dcms-card p-6 border-2 border-emerald-500/20 bg-gradient-to-br from-emerald-50/30 via-white to-slate-50">
            <div class="flex items-start gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-emerald-600 text-white font-black text-sm shadow-sm">
                    📊
                </span>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-black text-slate-900">Sinkronisasi Data Google Spreadsheet ke SQL</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Tarik riwayat transaksi keep dari Google Spreadsheet real ke tabel MySQL <code>sosmed_keeps</code>.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800 border border-emerald-200">
                                Total di SQL: {{ number_format($totalSosmedKeeps ?? 0, 0, ',', '.') }} data
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('settings.watzap.sync_google_sheet') }}" class="mt-4 space-y-3.5" onsubmit="return confirm('Mulai sinkronisasi data dari Google Spreadsheet ke database SQL? Tindakan ini akan memperbarui data tabel sosmed_keeps.')">
                        @csrf
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label for="spreadsheet_url" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                    URL / ID Google Spreadsheet
                                </label>
                                <input type="text" id="spreadsheet_url" name="spreadsheet_url" value="https://docs.google.com/spreadsheets/d/1V0pIa0NZCprJqRwnSnikFaLZ-dd50nY0uE3bjVYsUe0/edit?gid=848355344#gid=848355344" required class="dcms-input font-mono text-xs" {{ ! $isSuperAdmin ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label for="sheet_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                    Nama Sheet Target
                                </label>
                                <input type="text" id="sheet_name" name="sheet_name" value="DATA KEEP" required class="dcms-input font-mono text-xs font-bold" {{ ! $isSuperAdmin ? 'disabled' : '' }}>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                            <label class="flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer">
                                <input type="checkbox" name="truncate" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" {{ ! $isSuperAdmin ? 'disabled' : '' }}>
                                <span>Gantikan data lama di SQL (Truncate & Replace agar data sama persis dengan Spreadsheet)</span>
                            </label>

                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-500 {{ ! $isSuperAdmin ? 'opacity-50 cursor-not-allowed' : '' }}" {{ ! $isSuperAdmin ? 'disabled' : '' }}>
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                                <span>Sinkronkan Sekarang</span>
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 p-3 text-xs text-slate-600">
                        <span class="font-bold text-slate-800">Perintah Terminal / CLI:</span> Anda juga dapat menjalankan sinkronisasi kapan saja melalui command line:
                        <code class="mt-1 block rounded bg-slate-900 px-3 py-1.5 font-mono text-emerald-400 text-[11px] select-all">php artisan keep:sync-google-sheet --truncate</code>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Tabel Dokumentasi API Action Webhook -->
        <section class="dcms-card p-6">
            <h2 class="font-black text-slate-900">Spesifikasi Action Webhook Fonnte (Kompatibel Google Apps Script)</h2>
            <p class="mt-1 text-xs text-slate-500">Endpoint webhook di atas juga mendukung seluruh parameter GET & POST dari script Apps Script lama Anda:</p>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-100 rounded-xl overflow-hidden">
                    <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="p-2.5">Action / Tipe</th>
                            <th class="p-2.5">Parameter Utama</th>
                            <th class="p-2.5">Fungsi ke Database SQL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="p-2.5 font-bold text-emerald-700 font-mono">Format Pesan KEEP</td>
                            <td class="p-2.5 font-mono text-[11px]">message, sender, isgroup</td>
                            <td class="p-2.5 text-slate-600">Merekap transaksi keep baru otomatis ke tabel SQL <code>sosmed_keeps</code> & kirim konfirmasi balasan.</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-bold text-indigo-700 font-mono">UPDATE_STATUS</td>
                            <td class="p-2.5 font-mono text-[11px]">action=UPDATE_STATUS&nomorWA=...&status=...</td>
                            <td class="p-2.5 text-slate-600">Memperbarui status transaksi keep (WIN, LOSE, ON PROGRESS) di SQL.</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-bold text-indigo-700 font-mono">UPDATE_REPORT_AR</td>
                            <td class="p-2.5 font-mono text-[11px]">action=UPDATE_REPORT_AR&nomorWA=...&arDoc=...</td>
                            <td class="p-2.5 text-slate-600">Mencatat nomor dokumen Report AR dan tanggal input AR ke SQL.</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-bold text-indigo-700 font-mono">UPDATE_REACTION_FU</td>
                            <td class="p-2.5 font-mono text-[11px]">action=UPDATE_REACTION_FU&customerPhone=...&nomorFU=...</td>
                            <td class="p-2.5 text-slate-600">Pencarian bottom-up & input kode PIC FU ke kolom <code>nomor_fu</code> di SQL.</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-bold text-indigo-700 font-mono">CLEAR_REACTION_FU</td>
                            <td class="p-2.5 font-mono text-[11px]">action=CLEAR_REACTION_FU&customerPhone=...</td>
                            <td class="p-2.5 text-slate-600">Fitur batal jempol: mengosongkan kolom <code>nomor_fu</code> di SQL.</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-bold text-rose-700 font-mono">DELETE_KEEP / HAPUS_KEEP</td>
                            <td class="p-2.5 font-mono text-[11px]">action=DELETE_KEEP&customerPhone=...</td>
                            <td class="p-2.5 text-slate-600">Kompatibel trigger onEdit: menghapus baris transaksi keep fisik dari database SQL.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Kolom Kanan: Status Device & Tes Kirim Fonnte -->
    <div class="space-y-6">
        <!-- Status Device Fonnte -->
        <section class="dcms-card p-6 border-2 border-emerald-500/20 bg-gradient-to-br from-emerald-50/20 via-white to-slate-50">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <h2 class="font-black text-slate-900">Status Device Fonnte</h2>
                    <button type="button" onclick="checkFonnteConnection()" id="btn-refresh-fonnte-status" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 rounded-lg px-2.5 py-1 transition border border-emerald-200" title="Segarkan status device dari server Fonnte">
                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        <span>Segarkan</span>
                    </button>
                </div>
                <span id="fonnte-card-status-badge" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold {{ ($fonnteDeviceStatus['status'] ?? '') === 'Connected' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    <span id="fonnte-card-status-dot" class="size-2 rounded-full {{ ($fonnteDeviceStatus['status'] ?? '') === 'Connected' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    <span id="fonnte-card-status-text">{{ $fonnteDeviceStatus['status'] ?? 'Unknown' }}</span>
                </span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Status koneksi nomor WhatsApp yang terdaftar di Fonnte.</p>

            <div class="mt-4 space-y-2.5">
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500 font-medium">Nomor WhatsApp</span>
                    <span id="fonnte-card-device" class="text-xs font-mono font-bold text-slate-900">{{ $fonnteDeviceStatus['device'] ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500 font-medium">Nama Device</span>
                    <span id="fonnte-card-name" class="text-xs font-semibold text-slate-900">{{ $fonnteDeviceStatus['name'] ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500 font-medium">Paket & Kuota</span>
                    <span id="fonnte-card-package" class="text-xs font-semibold text-slate-900">{{ $fonnteDeviceStatus['package'] ?? 'Free' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500 font-medium">Masa Aktif</span>
                    <span id="fonnte-card-expired" class="text-xs font-semibold text-slate-900">{{ $fonnteDeviceStatus['expired'] ?? '-' }}</span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="https://md.fonnte.com" target="_blank" rel="noopener" class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <span>Buka Dashboard Fonnte ↗</span>
                </a>
            </div>
        </section>

        <!-- Form Tes Kirim Pesan via Fonnte -->
        @if($isSuperAdmin)
        <section class="dcms-card p-6">
            <h2 class="font-black text-slate-900">Uji Coba Kirim Pesan (Fonnte)</h2>
            <p class="mt-1 text-xs text-slate-500">Kirim pesan WhatsApp tes untuk memverifikasi apakah token Fonnte aktif.</p>

            <form method="POST" action="{{ route('settings.watzap.test_send_fonnte') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label for="test_phone_fonnte" class="mb-1 block text-xs font-bold text-slate-700">Nomor WhatsApp Tujuan</label>
                    <input type="text" id="test_phone_fonnte" name="phone" required placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx" class="dcms-input text-xs font-mono">
                </div>
                <div>
                    <label for="test_msg_fonnte" class="mb-1 block text-xs font-bold text-slate-700">Isi Pesan Uji Coba</label>
                    <textarea id="test_msg_fonnte" name="message" rows="3" required class="dcms-input text-xs">Halo! Ini adalah pesan uji coba dari DCMS via Gateway Fonnte.</textarea>
                </div>
                <button type="submit" class="dcms-primary-button w-full py-2.5 text-xs font-bold">
                    Kirim Pesan Tes via Fonnte
                </button>
            </form>
        </section>
        @endif
    </div>
</div>
@endif

<script>
function togglePasswordVisibility(fieldId) {
    const input = document.getElementById(fieldId);
    if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
    }
}

function toggleInboxDedicatedFields(val) {
    const fields = document.getElementById('inbox-dedicated-fields');
    if (!fields) return;
    if (val === '0') {
        fields.classList.remove('hidden');
    } else {
        fields.classList.add('hidden');
    }
}

function copyWebhookUrl() {
    const input = document.getElementById('webhook_url_display');
    const btn = document.getElementById('btn-copy-webhook');
    if (!input) return;

    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        if (btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '<span class="text-emerald-300">✓ Tersalin!</span>';
            setTimeout(function() { btn.innerHTML = original; }, 2000);
        }
    });
}

async function autoRegisterWebhook() {
    const btn = document.getElementById('btn-register-webhook');
    const feedback = document.getElementById('webhook-register-feedback');
    if (!btn || !feedback) return;

    // Ambil nilai yang sedang aktif di form jika ada
    const dedicatedRadio = document.querySelector('input[name="watzap_inbox_same_as_broadcast"]:checked');
    const isDedicated = dedicatedRadio ? dedicatedRadio.value === '0' : false;

    let numberKey = '';
    let apiKey = '';

    if (isDedicated) {
        const numInp = document.getElementById('watzap_inbox_number_key');
        const apiInp = document.getElementById('watzap_inbox_api_key');
        numberKey = numInp ? numInp.value.trim() : '';
        apiKey = apiInp ? apiInp.value.trim() : '';
    } else {
        const numInp = document.getElementById('watzap_number_key');
        const apiInp = document.getElementById('watzap_api_key');
        numberKey = numInp ? numInp.value.trim() : '';
        apiKey = apiInp ? apiInp.value.trim() : '';
    }

    btn.disabled = true;
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<span class="flex items-center gap-1.5"><span class="size-3 rounded-full border-2 border-white border-t-transparent animate-spin"></span> Menghubungkan...</span>';
    feedback.className = 'mt-2.5 text-xs font-semibold text-slate-600 flex items-center gap-2 p-2 bg-slate-100 rounded-lg';
    feedback.innerHTML = '<span class="size-2 rounded-full bg-amber-500 animate-pulse"></span> Mengirim permintaan pendaftaran webhook ke server WatZap...';
    feedback.classList.remove('hidden');

    try {
        const response = await fetch('{{ route("settings.watzap.register_webhook") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                channel: 'inbox',
                api_key: apiKey,
                number_key: numberKey
            })
        });

        const data = await response.json();
        if (data.success) {
            feedback.className = 'mt-2.5 text-xs font-bold text-emerald-800 bg-emerald-100/90 border border-emerald-200 rounded-lg p-2.5 flex items-center gap-2';
            feedback.innerHTML = '<svg class="size-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> ' +
                (data.message || 'Webhook berhasil didaftarkan ke WatZap! Chat masuk dari WhatsApp otomatis masuk ke Inbox FU.');
        } else {
            feedback.className = 'mt-2.5 text-xs font-bold text-rose-800 bg-rose-50 border border-rose-200 rounded-lg p-2.5 flex items-center gap-2';
            feedback.innerHTML = '<svg class="size-4 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> ' +
                (data.message || 'Gagal mendaftarkan webhook. Pastikan API Key dan Number Key sudah terisi dan device Online.');
        }
    } catch (err) {
        feedback.className = 'mt-2.5 text-xs font-bold text-rose-800 bg-rose-50 border border-rose-200 rounded-lg p-2.5';
        feedback.innerHTML = '⚠️ Terjadi kesalahan: ' + err.message;
    } finally {
        btn.disabled = false;
        btn.innerHTML = oldHtml;
    }
}

async function checkWebhookStatus() {
    const feedback = document.getElementById('webhook-register-feedback');
    const btn = document.getElementById('btn-check-webhook');
    if (!feedback) return;

    if (btn) btn.disabled = true;
    feedback.className = 'mt-2.5 text-xs font-semibold text-slate-600 flex items-center gap-2 p-2.5 bg-slate-100 rounded-lg';
    feedback.innerHTML = '<span class="size-2 rounded-full bg-blue-500 animate-pulse"></span> Memeriksa status webhook di server WatZap...';
    feedback.classList.remove('hidden');

    try {
        const res = await fetch('{{ route("settings.watzap.get_webhook") }}?channel=inbox');
        const data = await res.json();

        if (data.success && data.message) {
            feedback.className = 'mt-2.5 text-xs font-bold text-emerald-800 bg-emerald-100/90 border border-emerald-200 rounded-lg p-2.5 flex items-center gap-2';
            feedback.innerHTML = '<svg class="size-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> ' +
                '<span>Webhook Terdaftar & Aktif di WatZap: <code class="bg-white/80 px-1.5 py-0.5 rounded text-[11px] font-mono">' + data.message + '</code></span>';
        } else {
            feedback.className = 'mt-2.5 text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 rounded-lg p-2.5 flex items-center gap-2';
            feedback.innerHTML = '<svg class="size-4 shrink-0 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> ' +
                '<span>' + (data.message || 'Belum ada webhook terdaftar di WatZap untuk nomor ini. Silakan klik tombol ⚡ Daftarkan Otomatis di atas.') + '</span>';
        }
    } catch (e) {
        feedback.className = 'mt-2.5 text-xs font-bold text-rose-800 bg-rose-50 border border-rose-200 rounded-lg p-2.5';
        feedback.innerHTML = 'Gagal memeriksa webhook: ' + e.message;
    } finally {
        if (btn) btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if ('{{ $activeTab }}' === 'inbox') {
        checkWebhookStatus();
    }
});

async function checkApiConnection() {
    const btn = document.getElementById('btn-check-api') || document.getElementById('btn-check-api-inbox');
    const container = document.getElementById('check-result-container');
    const title = document.getElementById('check-result-title');
    const badge = document.getElementById('check-result-badge');
    const msg = document.getElementById('check-result-message');
    const jsonPre = document.getElementById('check-result-json');
    const apiKeyInput = document.getElementById('watzap_api_key');

    if (!btn) return;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-1">↻</span> Memeriksa...';

    if (container) {
        container.classList.remove('hidden');
        if (msg) msg.textContent = 'Menghubungi server WatZap.id...';
        if (jsonPre) jsonPre.textContent = '';
        if (badge) {
            badge.className = 'rounded px-2 py-0.5 text-[10px] font-bold bg-slate-200 text-slate-700';
            badge.textContent = 'Loading';
        }
    }

    try {
        const response = await fetch('{{ route("settings.watzap.check") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                api_key: apiKeyInput ? apiKeyInput.value : ''
            })
        });

        const data = await response.json();
        if (badge && msg && jsonPre) {
            if (data.success) {
                badge.className = 'rounded px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800';
                badge.textContent = 'Online / Valid';
                msg.textContent = data.message || 'API Key valid dan terhubung dengan server WatZap.';
            } else {
                badge.className = 'rounded px-2 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-800';
                badge.textContent = 'Gagal';
                msg.textContent = data.message || 'Gagal memverifikasi API Key.';
            }
            jsonPre.textContent = JSON.stringify(data, null, 2);
        } else {
            alert(data.message || (data.success ? 'Koneksi Berhasil' : 'Koneksi Gagal'));
        }
    } catch (err) {
        if (badge && msg) {
            badge.className = 'rounded px-2 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-800';
            badge.textContent = 'Error';
            msg.textContent = 'Koneksi gagal: ' + err.message;
        } else {
            alert('Koneksi gagal: ' + err.message);
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function checkBaileysConnection() {
    const btn = document.getElementById('btn-check-baileys');
    const fb = document.getElementById('baileys-check-feedback');
    if (!btn) return;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-1">↻</span> Memeriksa Baileys Bot...';

    if (fb) {
        fb.className = 'mt-2.5 text-xs font-semibold text-slate-500';
        fb.textContent = 'Menghubungi server Baileys Gateway...';
        fb.classList.remove('hidden');
    }

    try {
        const response = await fetch('{{ route("settings.watzap.check_baileys") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();
        if (fb) {
            if (data.success && data.status === 'CONNECTED') {
                const phone = data.phone || '-';
                const name = data.name || 'Bot DCMS';
                fb.className = 'mt-2.5 text-xs font-semibold text-indigo-900 bg-indigo-50 border border-indigo-200 rounded-xl p-3 leading-relaxed';
                fb.innerHTML = '✅ <strong>Koneksi Sukses:</strong> Baileys Bot Online! Nomor Khusus Gateway: <strong>' + phone + '</strong> (' + name + ').';
            } else if (data.success) {
                fb.className = 'mt-2.5 text-xs font-semibold text-amber-800 bg-amber-50 border border-amber-200 rounded-xl p-3 leading-relaxed';
                fb.innerHTML = '⚠️ <strong>Peringatan:</strong> Server Baileys aktif namun socket berstatus: <code>' + (data.status || 'WAITING_QR') + '</code>. Silakan buka Dashboard untuk scan QR atau pairing.';
            } else {
                fb.className = 'mt-2.5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200 rounded-xl p-3 leading-relaxed';
                fb.innerHTML = '❌ <strong>Gagal:</strong> ' + (data.message || 'Koneksi ke Baileys Bot gagal.');
            }
        } else {
            alert(data.message || (data.success ? 'Koneksi Berhasil' : 'Koneksi Gagal'));
        }
    } catch (err) {
        if (fb) {
            fb.className = 'mt-2.5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200 rounded-xl p-3';
            fb.textContent = 'Koneksi gagal: ' + err.message;
        } else {
            alert('Koneksi gagal: ' + err.message);
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function checkWahaConnection() {
    const btn = document.getElementById('btn-check-waha');
    const fb = document.getElementById('waha-check-feedback');
    if (!btn) return;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-1">↻</span> Memeriksa WAHA VPS...';

    if (fb) {
        fb.className = 'mt-2.5 text-xs font-semibold text-slate-500';
        fb.textContent = 'Menghubungi server WAHA VPS...';
        fb.classList.remove('hidden');
    }

    try {
        const response = await fetch('{{ route("settings.watzap.check_waha") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                url: document.getElementById('waha_vps_url')?.value || '',
                api_key: document.getElementById('waha_api_key')?.value || '',
                session: document.getElementById('waha_session')?.value || ''
            })
        });

        const data = await response.json();
        if (fb) {
            if (data.success && data.status === 'WORKING') {
                const phone = data.me ? (data.me.id || '').replace('@c.us', '') : '-';
                const name = data.me ? (data.me.pushName || '') : '';
                fb.className = 'mt-2.5 text-xs font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl p-3 leading-relaxed';
                fb.innerHTML = '✅ <strong>Koneksi Sukses:</strong> Server WAHA VPS Online! Sesi <code>' + (data.session || 'default') + '</code> aktif terhubung ke nomor <strong>' + phone + '</strong> (' + name + ').';
            } else if (data.success) {
                fb.className = 'mt-2.5 text-xs font-semibold text-amber-800 bg-amber-50 border border-amber-200 rounded-xl p-3 leading-relaxed';
                fb.innerHTML = '⚠️ <strong>Peringatan:</strong> ' + (data.message || 'Sesi ditemukan namun belum siap.');
            } else {
                fb.className = 'mt-2.5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200 rounded-xl p-3 leading-relaxed';
                fb.innerHTML = '❌ <strong>Gagal:</strong> ' + (data.message || 'Koneksi ke WAHA VPS gagal.');
            }
        } else {
            alert(data.message || (data.success ? 'Koneksi Berhasil' : 'Koneksi Gagal'));
        }
    } catch (err) {
        if (fb) {
            fb.className = 'mt-2.5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200 rounded-xl p-3';
            fb.textContent = 'Koneksi gagal: ' + err.message;
        } else {
            alert('Koneksi gagal: ' + err.message);
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function copyFonnteWebhookUrl() {
    const input = document.getElementById('fonnte-webhook-url-input');
    const btn = document.getElementById('btn-copy-fonnte-webhook');
    if (!input) return;

    input.select();
    input.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(input.value).then(() => {
        if (btn) {
            const originalHTML = btn.innerHTML;
            btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-500');
            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-500');
            btn.innerHTML = '<span>✓ Webhook Tersalin!</span>';
            setTimeout(() => {
                btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-500');
                btn.classList.add('bg-indigo-600', 'hover:bg-indigo-500');
                btn.innerHTML = originalHTML;
            }, 2500);
        }
    }).catch(err => {
        alert('Gagal menyalin: ' + err);
    });
}

async function checkFonnteConnection() {
    const btn = document.getElementById('btn-check-fonnte');
    const fb = document.getElementById('fonnte-check-feedback');
    if (!btn) return;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-1">↻</span> Memeriksa Akun Fonnte...';

    if (fb) {
        fb.className = 'mt-2.5 text-xs font-semibold text-slate-500';
        fb.textContent = 'Menghubungi API Fonnte...';
        fb.classList.remove('hidden');
    }

    try {
        const response = await fetch('{{ route("settings.watzap.check_fonnte") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                token: document.getElementById('fonnte_token')?.value || ''
            })
        });

        const data = await response.json();
        const isConn = data.success && (data.status === 'Connected' || data.connected || (data.raw && (data.raw.device_status === 'connect' || data.raw.status === true)));

        // Update card status elements live
        const elDevice = document.getElementById('fonnte-card-device');
        const elName = document.getElementById('fonnte-card-name');
        const elPkg = document.getElementById('fonnte-card-package');
        const elExp = document.getElementById('fonnte-card-expired');
        const elBadge = document.getElementById('fonnte-card-status-badge');
        const elDot = document.getElementById('fonnte-card-status-dot');
        const elText = document.getElementById('fonnte-card-status-text');

        if (data.success && isConn) {
            if (elDevice) elDevice.textContent = data.device || '-';
            if (elName) elName.textContent = data.name || '-';
            if (elPkg) elPkg.textContent = data.package || 'Free';
            if (elExp) elExp.textContent = data.expired || '-';
            if (elBadge && elDot && elText) {
                elBadge.className = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-800';
                elDot.className = 'size-2 rounded-full bg-emerald-500';
                elText.textContent = 'Connected';
            }
        }

        if (fb) {
            if (isConn) {
                fb.className = 'mt-2.5 text-xs font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl p-3 leading-relaxed';
                fb.innerHTML = '✅ <strong>Koneksi Sukses:</strong> Akun Fonnte Terhubung! Device: <strong>' + (data.device || '-') + '</strong> | Nama: <strong>' + (data.name || '-') + '</strong> | Paket: ' + (data.package || '-');
            } else if (data.success) {
                fb.className = 'mt-2.5 text-xs font-semibold text-amber-800 bg-amber-50 border border-amber-200 rounded-xl p-3 leading-relaxed';
                fb.innerHTML = '⚠️ <strong>Perhatian:</strong> ' + (data.message || 'Token valid namun device belum terhubung (Disconnect).');
            } else {
                fb.className = 'mt-2.5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200 rounded-xl p-3 leading-relaxed';
                fb.innerHTML = '❌ <strong>Gagal:</strong> ' + (data.message || 'Koneksi ke Fonnte gagal.');
            }
        } else {
            alert(data.message || (data.success ? 'Koneksi Berhasil' : 'Koneksi Gagal'));
        }
    } catch (err) {
        if (fb) {
            fb.className = 'mt-2.5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200 rounded-xl p-3';
            fb.textContent = 'Koneksi gagal: ' + err.message;
        } else {
            alert('Koneksi gagal: ' + err.message);
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function registerFonnteWebhookAuto() {
    const input = document.getElementById('fonnte-webhook-url-input');
    const btn = document.getElementById('btn-register-fonnte-webhook');
    const fb = document.getElementById('fonnte-webhook-feedback');
    const token = document.getElementById('fonnte_token')?.value || '';
    if (!input || !btn) return;

    const url = input.value.trim();
    if (!url) {
        alert('Masukkan URL Webhook terlebih dahulu.');
        return;
    }

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-1">↻</span> Mendaftarkan...';
    if (fb) {
        fb.className = 'mt-2.5 text-xs font-semibold text-slate-500';
        fb.textContent = 'Menghubungi server Fonnte...';
        fb.classList.remove('hidden');
    }

    try {
        const response = await fetch('{{ route("settings.watzap.register_fonnte_webhook") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                webhook_url: url,
                token: token
            })
        });

        const data = await response.json();
        if (fb) {
            if (data.success) {
                fb.className = 'mt-2.5 text-xs font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl p-3 leading-relaxed';
                fb.innerHTML = '✅ <strong>Sukses:</strong> ' + (data.message || 'Webhook berhasil didaftarkan ke Fonnte!') + '<br><span class="text-[11px] text-emerald-700">Setiap ada format KEEP di nomor Fonnte ini, pesan akan langsung direkap otomatis ke SQL.</span>';
            } else {
                fb.className = 'mt-2.5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200 rounded-xl p-3 leading-relaxed';
                fb.innerHTML = '❌ <strong>Gagal:</strong> ' + (data.message || 'Gagal mendaftarkan webhook ke Fonnte.');
            }
        }
    } catch (err) {
        if (fb) {
            fb.className = 'mt-2.5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200 rounded-xl p-3 leading-relaxed';
            fb.innerHTML = '❌ <strong>Error:</strong> ' + err.message;
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

@if($activeTab === 'fonnte' && ($fonnteDeviceStatus['status'] ?? '') !== 'Connected')
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        checkFonnteConnection();
    }, 400);
});
@endif
</script>
@endsection
