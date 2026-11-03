@extends('layouts.app')
@section('title', 'WhatsApp Web Tim')
@section('content')
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-emerald-600">Komunikasi & CS Terpusat</p>
        <h1 class="mt-1 text-2xl font-black text-slate-900">WhatsApp Web & Team Chat</h1>
        <p class="mt-1 text-xs text-slate-500">1 Nomor WhatsApp CS resmi toko terhubung bersama untuk Super Admin dan seluruh Staff.</p>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="openWhatsAppWorkspace()" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-700/20 transition hover:bg-emerald-500 hover:scale-[1.02] active:scale-95">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Buka di Jendela Bebas Logout
        </button>
        @if($isSuperAdmin)
        <a href="{{ route('settings.watzap.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <svg class="size-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.3 4.3a2 2 0 0 1 3.4 0l.9 1.6a2 2 0 0 0 1.5.9l1.8.3a2 2 0 0 1 1.7 1.7l.3 1.8a2 2 0 0 0 .9 1.5l1.6.9a2 2 0 0 1 0 3.4l-1.6.9a2 2 0 0 0-.9 1.5l-.3 1.8a2 2 0 0 1-1.7 1.7l-1.8.3a2 2 0 0 0-1.5.9l-.9 1.6a2 2 0 0 1-3.4 0l-.9-1.6a2 2 0 0 0-1.5-.9l-1.8-.3a2 2 0 0 1-1.7-1.7l-.3-1.8a2 2 0 0 0-.9-1.5l-1.6-.9a2 2 0 0 1 0-3.4l1.6-.9a2 2 0 0 0 .9-1.5l.3-1.8a2 2 0 0 1 1.7-1.7l1.8-.3a2 2 0 0 0 1.5-.9l.9-1.6z"/><circle cx="12" cy="12" r="3"/></svg>
            Pengaturan API
        </a>
        @endif
    </div>
</div>

<!-- Card Utama: Penjelasan Bebas Logout & Akses Tim -->
<div class="mb-6 rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent p-5 sm:p-6 shadow-sm">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-4">
            <div class="grid size-12 shrink-0 place-items-center rounded-2xl bg-emerald-600 text-white shadow-md shadow-emerald-600/30">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a4 4 0 0 0 5 5"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-black text-slate-900">Sistem 1 Nomor WA Multi-Staff (Anti Logout)</h2>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800">
                        <span class="size-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                        Aktif
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-600 leading-relaxed max-w-2xl">
                    Jendela <strong>Standalone Workspace</strong> memungkinkan Anda tetap membuka chat pelanggan secara berdampingan. Anda dapat leluasa berpindah ke menu <strong>Master Customer</strong>, <strong>Leads</strong>, maupun <strong>Broadcast</strong> tanpa khawatir sesi WhatsApp tertutup atau ter-logout!
                </p>
            </div>
        </div>
        <div class="flex shrink-0 gap-2">
            <button type="button" onclick="openWhatsAppWorkspace()" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-xs font-bold text-white shadow-lg shadow-emerald-700/25 transition hover:bg-emerald-500">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6v6m0-6L10 14M9 21H3v-6m0 6l11-11"/></svg>
                Luncurkan Jendela WhatsApp
            </button>
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <!-- Kolom Utama: Launcher & Quick Chat -->
    <div class="space-y-6 lg:col-span-2">
        <!-- Opsi Akses WhatsApp -->
        <section class="dcms-card p-6">
            <h3 class="font-black text-slate-900">Pilihan Akses WhatsApp Tim</h3>
            <p class="mt-1 text-xs text-slate-500">Pilih metode yang paling nyaman untuk Anda dan tim staff.</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <!-- Opsi 1: WatZap Team Chat -->
                <div class="rounded-2xl border-2 border-emerald-400 bg-emerald-50/40 p-5 transition hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="rounded-lg bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white">Rekomendasi</span>
                        <svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0M15 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm6 3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM7 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/></svg>
                    </div>
                    <div class="mt-3 font-black text-slate-900">WatZap Team Inbox (Multi-Agent)</div>
                    <p class="mt-1 text-xs text-slate-600">Satu nomor WhatsApp dibuka bersamaan oleh Superadmin & seluruh Staff tanpa saling kick/logout.</p>
                    <div class="mt-4">
                        <button type="button" onclick="openWhatsAppWorkspace()" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-500">
                            Buka WatZap Chat
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Opsi 2: WhatsApp Web Standar -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-slate-300 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">Web Resmi</span>
                        <svg class="size-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 0 1-9 9m9-9a9 9 0 0 0-9-9m9 9H3m9 9a9 9 0 0 1-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 0 1 9-9"/></svg>
                    </div>
                    <div class="mt-3 font-black text-slate-900">WhatsApp Web Standar</div>
                    <p class="mt-1 text-xs text-slate-600">Membuka portal WhatsApp Web resmi (<span class="font-mono text-[11px]">web.whatsapp.com</span>) di tab baru.</p>
                    <div class="mt-4">
                        <a href="https://web.whatsapp.com" target="_blank" rel="noopener" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            Buka web.whatsapp.com
                            <svg class="size-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Chat Cepat ke Nomor Tertentu -->
        <section class="dcms-card p-6">
            <h3 class="font-black text-slate-900">Kirim Chat Langsung ke Pelanggan</h3>
            <p class="mt-1 text-xs text-slate-500">Ketik nomor HP pelanggan untuk membuka obrolan langsung via WhatsApp Web.</p>

            <form onsubmit="handleDirectChat(event)" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                <input id="direct-phone" type="text" required placeholder="Masukkan nomor (misal 08123456789 atau 628...)" class="dcms-input flex-1 py-2.5 text-xs font-mono">
                <button type="submit" class="dcms-primary-button px-5 py-2.5 text-xs font-bold">
                    Buka Chat Sekarang
                </button>
            </form>
        </section>
    </div>

    <!-- Kolom Kanan: Status & Tips Anti-Logout -->
    <div class="space-y-6">
        <section class="dcms-card p-6">
            <h3 class="font-black text-slate-900">Status Gateway WhatsApp</h3>
            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Status Server</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold {{ ($deviceStatus['online'] ?? false) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                        <span class="size-2 rounded-full {{ ($deviceStatus['online'] ?? false) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $deviceStatus['status'] ?? ($isConfigured ? 'Terkoneksi' : 'Belum Dikonfigurasi') }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Nomor CS Toko</span>
                    <span class="font-mono text-xs font-bold text-slate-900">{{ $deviceStatus['phone'] ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <span class="text-xs text-slate-500">Paket Gateway</span>
                    <span class="text-xs font-bold text-brand-700">{{ $deviceStatus['package'] ?? 'WatZap API' }}</span>
                </div>
            </div>
        </section>

        <section class="dcms-card p-6">
            <h3 class="font-bold text-slate-900">Tips Agar Bebas Logout</h3>
            <div class="mt-3 space-y-3 text-xs text-slate-600 leading-relaxed">
                <div class="flex items-start gap-2.5">
                    <svg class="mt-0.5 size-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span><strong>Gunakan Jendela Terpisah:</strong> Mengklik "Buka di Jendela Bebas Logout" membuka jendela tersendiri sehingga navigasi menu di DCMS tidak me-reload sesi Anda.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <svg class="mt-0.5 size-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span><strong>1 WA Dipakai Bersama:</strong> Melalui WatZap Chat, nomor CS tetap aktif melayani banyak obrolan oleh Superadmin maupun Staff sekaligus tanpa batasan 1 sesi device.</span>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
function openWhatsAppWorkspace() {
    const webUrl = '{{ $webUrl }}';
    const workspaceWindow = window.open(
        webUrl,
        'dcms_whatsapp_workspace',
        'width=1280,height=820,menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes'
    );
    if (workspaceWindow) {
        workspaceWindow.focus();
    } else {
        window.open(webUrl, '_blank');
    }
}

function handleDirectChat(e) {
    e.preventDefault();
    const phoneInput = document.getElementById('direct-phone');
    if (!phoneInput || !phoneInput.value.trim()) return;

    let phone = phoneInput.value.replace(/[^0-9]/g, '');
    if (phone.startsWith('08')) {
        phone = '62' + phone.substring(1);
    } else if (phone.startsWith('8')) {
        phone = '62' + phone;
    }

    if (phone.length < 10) {
        alert('Nomor HP tidak valid. Masukkan nomor dengan benar.');
        return;
    }

    window.open('https://wa.me/' + phone, '_blank');
}
</script>
@endsection
