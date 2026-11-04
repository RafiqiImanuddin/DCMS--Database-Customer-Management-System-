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

<!-- TAB NAVIGATION: WA BROADCAST vs WA INBOX FOLLOW UP -->
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
                        <input id="watzap_api_key" name="watzap_api_key" type="password" value="{{ old('watzap_api_key', $apiKey) }}" @disabled(! $isSuperAdmin) placeholder="Contoh: wt_sec_xxxxxxxxxxxx" class="dcms-input pr-10 font-mono text-xs disabled:bg-slate-100 disabled:text-slate-500">
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

@else
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

            <form method="POST" action="{{ route('settings.watzap.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="target_tab" value="inbox">

                <!-- Opsi Mode Nomor Gateway Inbox -->
                <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                    <span class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Pilihan Nomor Pengirim Inbox Follow-Up:</span>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 text-xs font-semibold text-slate-800 cursor-pointer">
                            <input type="radio" name="watzap_inbox_same_as_broadcast" value="1" {{ old('watzap_inbox_same_as_broadcast', $inboxSame) === '1' ? 'checked' : '' }} onchange="toggleInboxDedicatedFields(this.value)" class="text-emerald-600 focus:ring-emerald-500">
                            <span>Gunakan Nomor yang Sama dengan WA Broadcast (Single Gateway)</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs font-semibold text-slate-800 cursor-pointer">
                            <input type="radio" name="watzap_inbox_same_as_broadcast" value="0" {{ old('watzap_inbox_same_as_broadcast', $inboxSame) === '0' ? 'checked' : '' }} onchange="toggleInboxDedicatedFields(this.value)" class="text-emerald-600 focus:ring-emerald-500">
                            <span>Gunakan Nomor Khusus untuk CS / Tim Follow-Up (Dedicated Number)</span>
                        </label>
                    </div>
                </div>

                <!-- Input Khusus jika Nomor Terpisah -->
                <div id="inbox-dedicated-fields" class="{{ old('watzap_inbox_same_as_broadcast', $inboxSame) === '0' ? '' : 'hidden' }} space-y-4 pt-2">
                    <div>
                        <label for="watzap_inbox_api_key" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">
                            API Key Khusus Inbox (Opsional)
                        </label>
                        <input id="watzap_inbox_api_key" name="watzap_inbox_api_key" type="password" value="{{ old('watzap_inbox_api_key', $inboxApiKey) }}" @disabled(! $isSuperAdmin) placeholder="Kosongkan jika menggunakan API Key WatZap yang sama" class="dcms-input font-mono text-xs disabled:bg-slate-100 disabled:text-slate-500">
                        <p class="mt-1 text-[11px] text-slate-400">Jika akun WatZap sama, kosongkan kolom ini (otomatis menggunakan API Key utama).</p>
                    </div>

                    <div>
                        <label for="watzap_inbox_number_key" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">
                            Number Key Khusus Inbox FU (Device Key) <span class="text-rose-500">*</span>
                        </label>
                        <input id="watzap_inbox_number_key" name="watzap_inbox_number_key" type="text" value="{{ old('watzap_inbox_number_key', $inboxNumberKey) }}" @disabled(! $isSuperAdmin) placeholder="Contoh: nk_cs_xxxxxxxxxxxx" class="dcms-input font-mono text-xs disabled:bg-slate-100 disabled:text-slate-500">
                        <p class="mt-1 text-[11px] text-slate-400">ID Number Key khusus nomor WhatsApp CS/Follow-Up yang sudah terhubung di WatZap.</p>
                    </div>
                </div>

                <!-- Webhook URL Inbound Setting -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4 pt-3">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-extrabold uppercase tracking-wide text-emerald-950 flex items-center gap-1.5">
                            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Webhook URL Penerima Chat Masuk (Inbound Webhook)
                        </label>
                        <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Auto Sync</span>
                    </div>
                    <p class="text-[11px] text-slate-600 mb-2.5 leading-relaxed">
                        Salin URL webhook di bawah ini dan pasang pada Member Area WatZap (<strong>Menu Webhook / Webhook Setting</strong>). Setiap kali ada chat baru dari PIC FU atau Customer (berupa teks, gambar, atau file), WatZap akan otomatis mengirimkannya ke Inbox DCMS Anda.
                    </p>
                    <div class="flex items-center gap-2">
                        <input id="webhook_url_display" type="text" readonly value="{{ $webhookUrl }}" class="dcms-input font-mono text-xs bg-white select-all">
                        <button type="button" onclick="copyWebhookUrl()" id="btn-copy-webhook" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2.5 text-xs font-bold text-white transition hover:bg-slate-800 shadow-sm">
                            <svg class="size-3.5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            <span>Salin URL</span>
                        </button>
                    </div>
                </div>

                @if($isSuperAdmin)
                <div class="flex items-center justify-between pt-3">
                    <button type="button" id="btn-check-api-inbox" onclick="checkApiConnection()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Tes Koneksi Gateway
                    </button>
                    <button type="submit" class="dcms-primary-button px-6 py-2.5 text-xs font-bold">
                        Simpan Pengaturan Inbox
                    </button>
                </div>
                @endif
            </form>
        </section>

        <!-- Uji Coba Kirim Chat Inbox Follow-Up -->
        @if($isSuperAdmin)
        <section class="dcms-card p-6">
            <div class="mb-4 border-b border-slate-100 pb-3">
                <h2 class="font-black text-slate-900">Uji Coba Kirim Chat Inbox Follow-Up</h2>
                <p class="mt-1 text-xs text-slate-500">Kirim pesan WhatsApp percobaan menggunakan nomor CS/Inbox Follow-Up untuk memverifikasi pengiriman 2-arah.</p>
            </div>
            <form method="POST" action="{{ route('settings.watzap.test_send') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="channel" value="inbox">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="test_phone_inbox" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">Nomor WhatsApp Tujuan</label>
                        <input id="test_phone_inbox" name="phone" type="text" required placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx" value="{{ old('phone') }}" class="dcms-input text-xs">
                    </div>
                    <div>
                        <label for="test_message_inbox" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">Isi Pesan Uji Coba</label>
                        <input id="test_message_inbox" name="message" type="text" required placeholder="Halo, ini tes pesan CS Inbox FU!" value="{{ old('message', 'Halo! Ini adalah pesan uji coba CS dari modul Inbox Follow-Up DCMS Gadgetmart.') }}" class="dcms-input text-xs">
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" @disabled(! $isConfiguredInbox) class="dcms-primary-button px-5 py-2.5 text-xs font-bold disabled:cursor-not-allowed disabled:opacity-50">
                        Kirim Pesan Uji Coba Inbox
                    </button>
                </div>
            </form>
        </section>
        @endif
    </div>

    <!-- Kolom Kanan: Status Device & Panduan Inbox -->
    <div class="space-y-6">
        <section class="dcms-card p-6">
            <h2 class="font-black text-slate-900">Status Device WA Inbox</h2>
            <p class="mt-1 text-xs text-slate-500">Nomor WhatsApp CS/Follow-Up yang melayani percakapan.</p>

            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Status Pengirim</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold {{ ($deviceStatusInbox['online'] ?? false) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                        <span class="size-2 rounded-full {{ ($deviceStatusInbox['online'] ?? false) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $deviceStatusInbox['status'] ?? ($isConfiguredInbox ? 'Cek Koneksi' : 'Belum Terhubung') }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Nomor CS Follow-Up</span>
                    <span class="font-mono text-xs font-bold text-slate-900">{{ $deviceStatusInbox['phone'] ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Mode Nomor</span>
                    <span class="text-xs font-semibold text-slate-900">
                        {{ $inboxSame === '1' ? 'Sama dg WA Broadcast' : 'Nomor Khusus Terpisah' }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Dukungan Media</span>
                    <span class="text-xs font-bold text-emerald-700">Teks, Foto & File PDF/Doc</span>
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
</script>
@endsection
