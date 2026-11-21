<!-- Modal: 🚀 Live Broadcast Progress Bar -->
<div id="broadcast-progress-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5" style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs"></div>
    
    <!-- Modal Dialog Card -->
    <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden flex flex-col z-10 animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/90 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 shadow-2xs">
                    <svg class="size-5 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 id="bp-modal-title" class="text-base font-black text-slate-900 uppercase tracking-tight">Kirim Broadcast WhatsApp</h3>
                        <span id="bp-modal-badge" class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800">
                            <span class="size-1.5 rounded-full bg-amber-500 animate-ping"></span>
                            <span id="bp-modal-badge-text">Menyiapkan...</span>
                        </span>
                    </div>
                    <p id="bp-modal-subtitle" class="text-xs text-slate-500 mt-0.5">Memulai pengiriman pesan ke target terpilih...</p>
                </div>
            </div>
        </div>

        <!-- Content Body -->
        <div class="p-5 sm:p-6 space-y-4">
            <!-- Progress Bar Section -->
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4 space-y-2.5">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5">
                        <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                        <span>Progres Pengiriman</span>
                        <span id="bp-progress-text" class="text-emerald-700 font-extrabold">(0 / 0)</span>
                    </span>
                    <span id="bp-progress-percent" class="text-xl font-black text-emerald-600 font-mono">0%</span>
                </div>
                <!-- Track -->
                <div class="h-3.5 w-full rounded-full bg-slate-200/80 overflow-hidden p-0.5 shadow-inner">
                    <div id="bp-progress-bar" class="h-full rounded-full bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 transition-all duration-300 shadow-sm" style="width: 0%;"></div>
                </div>
            </div>

            <!-- 3 Summary Mini Cards -->
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Target</span>
                    <span id="bp-stat-total" class="mt-0.5 text-xl font-black text-slate-800 block">0</span>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-3 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Berhasil Terkirim</span>
                    <span id="bp-stat-sent" class="mt-0.5 text-xl font-black text-emerald-600 block">0</span>
                </div>
                <div class="rounded-xl border border-rose-200 bg-rose-50/40 p-3 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 block">Gagal Terkirim</span>
                    <span id="bp-stat-failed" class="mt-0.5 text-xl font-black text-rose-600 block">0</span>
                </div>
            </div>

            <!-- Live Status / Delay Countdown Notice -->
            <div id="bp-current-status-box" class="rounded-xl border border-blue-200 bg-blue-50/60 p-3.5 flex items-start gap-3 transition">
                <div class="shrink-0 mt-0.5">
                    <svg id="bp-status-icon" class="size-4 text-blue-600 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div id="bp-status-main" class="text-xs font-bold text-blue-900 truncate">Menghubungkan ke server...</div>
                    <div id="bp-status-desc" class="text-[11px] text-blue-700 mt-0.5 leading-relaxed">Memvalidasi kuota dan menyiapkan antrean pesan.</div>
                </div>
            </div>

            <!-- Live Activity Log Terminal -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Live Log Aktivitas</span>
                    <span class="text-[10px] text-slate-400">Otomatis bergulir</span>
                </div>
                <div id="bp-log-container" class="rounded-xl bg-slate-900 border border-slate-800 p-3 font-mono text-[11px] text-slate-200 max-h-36 overflow-y-auto space-y-1 shadow-inner leading-relaxed">
                    <div class="text-slate-400 italic">[Sistem] Siap memulai pengiriman broadcast...</div>
                </div>
            </div>

            <!-- Completion Alert Banner -->
            <div id="bp-completion-banner" class="hidden rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-center space-y-1">
                <div class="text-sm font-black text-emerald-800 flex items-center justify-center gap-1.5">
                    <span>🎉 Broadcast WhatsApp Selesai!</span>
                </div>
                <p id="bp-completion-desc" class="text-xs text-emerald-700">Seluruh pesan dalam batch telah selesai diproses.</p>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/80 px-5 py-3.5">
            <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                <svg class="size-3.5 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <span class="hidden sm:inline">Jangan tutup tab browser selama proses berlangsung.</span>
                <span class="sm:hidden">Jangan tutup halaman.</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="bp-cancel-btn" onclick="cancelBroadcastProcess()" class="rounded-xl border border-rose-200 bg-white px-3.5 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 shadow-2xs transition">
                    Hentikan
                </button>
                <button type="button" id="bp-done-btn" onclick="finishAndReloadLeads()" class="hidden rounded-xl bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-emerald-500 transition">
                    Selesai & Muat Ulang
                </button>
                <a id="bp-report-btn" href="{{ route('broadcasts.index') }}" target="_blank" class="hidden rounded-xl border border-purple-200 bg-purple-50 px-3.5 py-1.5 text-xs font-bold text-purple-700 hover:bg-purple-100 shadow-2xs transition">
                    Buka Laporan ↗
                </a>
            </div>
        </div>
    </div>
</div>

<script>
let isBroadcastRunning = false;
let shouldStopBroadcast = false;
let currentBatchId = null;
let currentBroadcastTargets = [];

function openBroadcastProgressModal(totalCount, batchTitle) {
    const modal = document.getElementById('broadcast-progress-modal');
    if (!modal) return;

    modal.style.display = 'flex';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    document.getElementById('bp-modal-title').textContent = batchTitle || 'Broadcast WhatsApp';
    document.getElementById('bp-modal-subtitle').textContent = `Memproses pengiriman ke ${totalCount} penerima...`;
    document.getElementById('bp-stat-total').textContent = totalCount;
    document.getElementById('bp-stat-sent').textContent = '0';
    document.getElementById('bp-stat-failed').textContent = '0';

    updateBpProgressBar(0, totalCount, 0);

    const completion = document.getElementById('bp-completion-banner');
    if (completion) completion.classList.add('hidden');

    const doneBtn = document.getElementById('bp-done-btn');
    if (doneBtn) doneBtn.classList.add('hidden');

    const reportBtn = document.getElementById('bp-report-btn');
    if (reportBtn) reportBtn.classList.add('hidden');

    const cancelBtn = document.getElementById('bp-cancel-btn');
    if (cancelBtn) {
        cancelBtn.classList.remove('hidden');
        cancelBtn.textContent = 'Hentikan';
        cancelBtn.className = 'rounded-xl border border-rose-200 bg-white px-3.5 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 shadow-2xs transition';
        cancelBtn.onclick = cancelBroadcastProcess;
    }

    const log = document.getElementById('bp-log-container');
    if (log) log.innerHTML = '';
}

function closeBroadcastProgressModal() {
    const modal = document.getElementById('broadcast-progress-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function updateBpProgressBar(current, total, percent) {
    const bar = document.getElementById('bp-progress-bar');
    const text = document.getElementById('bp-progress-text');
    const pct = document.getElementById('bp-progress-percent');

    if (bar) bar.style.width = percent + '%';
    if (text) text.textContent = `(${current} / ${total})`;
    if (pct) pct.textContent = percent + '%';
}

function updateBpStatus(mainText, descText, state = 'loading') {
    const box = document.getElementById('bp-current-status-box');
    const main = document.getElementById('bp-status-main');
    const desc = document.getElementById('bp-status-desc');
    const icon = document.getElementById('bp-status-icon');
    const badge = document.getElementById('bp-modal-badge');
    const badgeText = document.getElementById('bp-modal-badge-text');

    if (main) main.textContent = mainText;
    if (desc) desc.textContent = descText;

    if (!box) return;

    if (state === 'sending') {
        box.className = 'rounded-xl border border-emerald-200 bg-emerald-50/70 p-3.5 flex items-start gap-3 transition';
        if (main) main.className = 'text-xs font-bold text-emerald-950 truncate';
        if (desc) desc.className = 'text-[11px] text-emerald-700 mt-0.5 leading-relaxed';
        if (badge) badge.className = 'inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800';
        if (badgeText) badgeText.textContent = 'Mengirim...';
        if (icon) {
            icon.className = 'size-4 text-emerald-600 animate-spin';
            icon.innerHTML = '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>';
        }
    } else if (state === 'delay') {
        box.className = 'rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 flex items-start gap-3 transition';
        if (main) main.className = 'text-xs font-bold text-amber-950 truncate';
        if (desc) desc.className = 'text-[11px] text-amber-700 mt-0.5 leading-relaxed';
        if (badge) badge.className = 'inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800';
        if (badgeText) badgeText.textContent = 'Jeda Anti-Banned';
        if (icon) {
            icon.className = 'size-4 text-amber-600 animate-pulse';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        }
    } else if (state === 'completed') {
        box.className = 'rounded-xl border border-teal-200 bg-teal-50/70 p-3.5 flex items-start gap-3 transition';
        if (main) main.className = 'text-xs font-bold text-teal-950 truncate';
        if (desc) desc.className = 'text-[11px] text-teal-700 mt-0.5 leading-relaxed';
        if (badge) badge.className = 'inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800';
        if (badgeText) badgeText.textContent = 'Selesai';
        if (icon) {
            icon.className = 'size-4 text-emerald-600';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" stroke="currentColor" d="M4.5 12.75l6 6 9-13.5"></path>';
        }
    } else if (state === 'error') {
        box.className = 'rounded-xl border border-rose-200 bg-rose-50/70 p-3.5 flex items-start gap-3 transition';
        if (main) main.className = 'text-xs font-bold text-rose-950 truncate';
        if (desc) desc.className = 'text-[11px] text-rose-700 mt-0.5 leading-relaxed';
        if (badge) badge.className = 'inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-[10px] font-bold text-rose-800';
        if (badgeText) badgeText.textContent = 'Terhenti';
        if (icon) {
            icon.className = 'size-4 text-rose-600';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" d="M6 18L18 6M6 6l12 12"></path>';
        }
    } else {
        box.className = 'rounded-xl border border-blue-200 bg-blue-50/60 p-3.5 flex items-start gap-3 transition';
        if (main) main.className = 'text-xs font-bold text-blue-900 truncate';
        if (desc) desc.className = 'text-[11px] text-blue-700 mt-0.5 leading-relaxed';
        if (badge) badge.className = 'inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold text-blue-800';
        if (badgeText) badgeText.textContent = 'Memproses...';
        if (icon) {
            icon.className = 'size-4 text-blue-600 animate-spin';
            icon.innerHTML = '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>';
        }
    }
}

function appendBpLog(text) {
    const log = document.getElementById('bp-log-container');
    if (!log) return;

    const row = document.createElement('div');
    const now = new Date();
    const timeStr = now.toTimeString().split(' ')[0];

    row.textContent = `[${timeStr}] ${text}`;
    log.appendChild(row);
    log.scrollTop = log.scrollHeight;
}

function cancelBroadcastProcess() {
    if (confirm('Apakah Anda yakin ingin menghentikan sisa pengiriman broadcast ini? Pesan yang sudah terkirim tetap tersimpan.')) {
        shouldStopBroadcast = true;
        appendBpLog('[Perintah] Menghentikan pengiriman setelah pesan ini selesai...');
        updateBpStatus('Menghentikan...', 'Menyelesaikan antrean saat ini sebelum berhenti aman...', 'error');
    }
}

function finishAndReloadLeads() {
    closeBroadcastProgressModal();
    window.location.reload();
}

function handleBeforeUnload(e) {
    if (isBroadcastRunning) {
        e.preventDefault();
        e.returnValue = 'Broadcast sedang berlangsung. Menutup halaman akan membatalkan sisa pengiriman pesan.';
        return e.returnValue;
    }
}

function showBroadcastCompletion(data) {
    const banner = document.getElementById('bp-completion-banner');
    const desc = document.getElementById('bp-completion-desc');
    const doneBtn = document.getElementById('bp-done-btn');
    const reportBtn = document.getElementById('bp-report-btn');
    const cancelBtn = document.getElementById('bp-cancel-btn');

    if (banner) banner.classList.remove('hidden');
    if (desc) {
        const batch = data.batch || {};
        desc.textContent = `Berhasil mengirim ${batch.total_sent || 0} dari ${batch.total_recipients || 0} pesan (Rasio: ${batch.delivery_rate || 0}%).`;
    }

    if (doneBtn) doneBtn.classList.remove('hidden');
    if (reportBtn) {
        if (data.detail_url) reportBtn.href = data.detail_url;
        reportBtn.classList.remove('hidden');
    }
    if (cancelBtn) cancelBtn.classList.add('hidden');

    updateBpStatus('Broadcast Selesai', 'Seluruh data tersimpan ke Laporan Broadcast.', 'completed');
    appendBpLog('[SELESAI] Seluruh tahapan broadcast telah tuntas.');
}
</script>
