@extends('layouts.app')
@section('title', 'Pengaturan WatZap.id')
@section('content')
<div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-brand-600">Integrasi Gateway</p>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Pengaturan WatZap.id</h1>
        <p class="mt-1 text-xs text-slate-500">Konfigurasi API Key & Number Key untuk integrasi broadcast pesan dan WhatsApp Web tim.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="https://api-docs.watzap.id/" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <svg class="size-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
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
<div class="mb-5 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-800 shadow-sm">
    <svg class="mt-0.5 size-5 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <div class="text-xs font-semibold leading-relaxed">{{ session('error') }}</div>
</div>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    <!-- Kolom Kiri: Form Konfigurasi (2 kolom span) -->
    <div class="space-y-6 lg:col-span-2">
        <section class="dcms-card p-6">
            <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="font-black text-slate-900">Kredensial API WatZap</h2>
                    <p class="mt-1 text-xs text-slate-500">Kredensial ini digunakan untuk mengeksekusi pengiriman pesan broadcast WhatsApp secara otomatis.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $isConfigured ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    <span class="size-2 rounded-full {{ $isConfigured ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ $isConfigured ? 'Terkonfigurasi' : 'Belum Lengkap' }}
                </span>
            </div>

            <form method="POST" action="{{ route('settings.watzap.update') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="watzap_api_key" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">
                        API Key (Secret Key) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="watzap_api_key" name="watzap_api_key" type="password" value="{{ old('watzap_api_key', $apiKey) }}" placeholder="Contoh: wt_sec_xxxxxxxxxxxx" class="dcms-input pr-10 font-mono text-xs">
                        <button type="button" onclick="togglePasswordVisibility('watzap_api_key')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">Didapatkan dari WatZap Member Area &rarr; WhatsApp (WA Unofficial) &rarr; Tab API.</p>
                </div>

                <div>
                    <label for="watzap_number_key" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">
                        Number Key (Device Key) <span class="text-rose-500">*</span>
                    </label>
                    <input id="watzap_number_key" name="watzap_number_key" type="text" value="{{ old('watzap_number_key', $numberKey) }}" placeholder="Contoh: nk_xxxxxxxxxxxx" class="dcms-input font-mono text-xs">
                    <p class="mt-1 text-[11px] text-slate-400">ID Number Key khusus untuk nomor WhatsApp pengirim yang sudah terhubung (Online) di WatZap.</p>
                </div>

                <div>
                    <label for="watzap_web_url" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">
                        URL WhatsApp Web / Team Chat
                    </label>
                    <input id="watzap_web_url" name="watzap_web_url" type="url" value="{{ old('watzap_web_url', $webUrl) }}" placeholder="https://watzap.chat" class="dcms-input text-xs">
                    <p class="mt-1 text-[11px] text-slate-400">URL workspace live chat untuk multi-agent staff & super admin (default: <span class="font-mono font-bold">https://watzap.chat</span>).</p>
                </div>

                <div class="flex items-center justify-between pt-3">
                    <button type="button" id="btn-check-api" onclick="checkApiConnection()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Tes Koneksi API
                    </button>
                    <button type="submit" class="dcms-primary-button px-6 py-2.5 text-xs font-bold">
                        Simpan Pengaturan
                    </button>
                </div>
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

        <!-- Uji Coba Kirim Pesan -->
        <section class="dcms-card p-6">
            <div class="mb-4 border-b border-slate-100 pb-3">
                <h2 class="font-black text-slate-900">Uji Coba Kirim Pesan WhatsApp</h2>
                <p class="mt-1 text-xs text-slate-500">Kirim pesan WhatsApp percobaan secara instan untuk memastikan integrasi berjalan sempurna.</p>
            </div>
            <form method="POST" action="{{ route('settings.watzap.test_send') }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="test_phone" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">Nomor WhatsApp Tujuan</label>
                        <input id="test_phone" name="phone" type="text" required placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx" value="{{ old('phone') }}" class="dcms-input text-xs">
                    </div>
                    <div>
                        <label for="test_message" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-700">Isi Pesan Uji Coba</label>
                        <input id="test_message" name="message" type="text" required placeholder="Halo, ini tes pesan dari DCMS Gadgetmart!" value="{{ old('message', 'Halo! Ini adalah pesan uji coba integrasi WatZap dari DCMS Gadgetmart.') }}" class="dcms-input text-xs">
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" @disabled(! $isConfigured) class="dcms-primary-button px-5 py-2.5 text-xs font-bold disabled:cursor-not-allowed disabled:opacity-50">
                        Kirim Pesan Uji Coba
                    </button>
                </div>
            </form>
        </section>
    </div>

    <!-- Kolom Kanan: Status Device & Panduan Multi-Agent -->
    <div class="space-y-6">
        <section class="dcms-card p-6">
            <h2 class="font-black text-slate-900">Status Device WhatsApp</h2>
            <p class="mt-1 text-xs text-slate-500">Nomor customer service aktif di WatZap.</p>

            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Status Sender</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold {{ ($deviceStatus['online'] ?? false) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                        <span class="size-2 rounded-full {{ ($deviceStatus['online'] ?? false) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $deviceStatus['status'] ?? ($isConfigured ? 'Cek Koneksi' : 'Belum Terhubung') }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Nomor CS</span>
                    <span class="font-mono text-xs font-bold text-slate-900">{{ $deviceStatus['phone'] ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Akun WatZap</span>
                    <span class="text-xs font-semibold text-slate-900">{{ $deviceStatus['account_name'] ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Paket Langganan</span>
                    <span class="text-xs font-bold text-brand-700">{{ $deviceStatus['package'] ?? '-' }}</span>
                </div>
            </div>

            <div class="mt-5 border-t border-slate-100 pt-4">
                <a href="{{ route('whatsapp.index') }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-500">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a4 4 0 0 0 5 5"/></svg>
                    Buka WhatsApp Web / Team Chat
                </a>
            </div>
        </section>

        <section class="dcms-card p-6">
            <h3 class="font-bold text-slate-900">Petunjuk 1 WA Multi-Agent</h3>
            <ul class="mt-3 space-y-2.5 text-xs text-slate-600">
                <li class="flex items-start gap-2">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-brand-50 text-[11px] font-bold text-brand-700">1</span>
                    <span>Hubungkan nomor WhatsApp resmi CS toko di <strong>Member Area WatZap</strong> dengan scan QR.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-brand-50 text-[11px] font-bold text-brand-700">2</span>
                    <span>Salin <strong>API Key</strong> dan <strong>Number Key</strong> ke formulir pengaturan ini.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-brand-50 text-[11px] font-bold text-brand-700">3</span>
                    <span>Seluruh Staff & Superadmin dapat membuka <strong>WhatsApp Web</strong> bersamaan melalui menu atau jendela workspace tanpa saling keluar (logout).</span>
                </li>
            </ul>
        </section>
    </div>
</div>

<script>
function togglePasswordVisibility(fieldId) {
    const input = document.getElementById(fieldId);
    if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
    }
}

async function checkApiConnection() {
    const btn = document.getElementById('btn-check-api');
    const container = document.getElementById('check-result-container');
    const title = document.getElementById('check-result-title');
    const badge = document.getElementById('check-result-badge');
    const msg = document.getElementById('check-result-message');
    const jsonPre = document.getElementById('check-result-json');
    const apiKeyInput = document.getElementById('watzap_api_key');

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-1">↻</span> Memeriksa...';

    container.classList.remove('hidden');
    msg.textContent = 'Menghubungi server WatZap.id...';
    jsonPre.textContent = '';
    badge.className = 'rounded px-2 py-0.5 text-[10px] font-bold bg-slate-200 text-slate-700';
    badge.textContent = 'Loading';

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
    } catch (err) {
        badge.className = 'rounded px-2 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-800';
        badge.textContent = 'Error';
        msg.textContent = 'Koneksi gagal: ' + err.message;
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>
@endsection
