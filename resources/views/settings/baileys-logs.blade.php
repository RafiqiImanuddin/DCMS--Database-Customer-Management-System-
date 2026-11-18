@extends('layouts.app')
@section('title', 'Log Render Baileys & Cron H+1')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span id="badge-render-status" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ !empty($logsData['online']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                    <span class="size-2 rounded-full {{ !empty($logsData['online']) ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                    <span id="text-render-status">{{ !empty($logsData['online']) ? 'Server Render Online' : 'Server Render Offline / Loading' }}</span>
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-medium text-slate-500">Monitoring Baileys 24/7 &amp; Scheduler H+1</span>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">Log Render Baileys</h1>
            <p class="mt-1 text-xs text-slate-500">
                Pantau eksekusi otomatis scheduler Cron Follow-Up H+1, aktivitas bot reaksi WhatsApp, dan status runtime server Render secara realtime.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Manual Trigger Cron Button -->
            <button type="button" 
                    id="btn-trigger-cron"
                    onclick="triggerCronManually(this)" 
                    class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white px-3.5 py-2 text-xs font-bold shadow-sm shadow-teal-600/20 transition cursor-pointer"
                    title="Picu pengecekan antrean follow-up 24 jam sekarang juga">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                <span>⚡ Trigger Cron Sekarang</span>
            </button>

            <!-- Refresh Button -->
            <button type="button" 
                    id="btn-refresh-logs"
                    onclick="fetchRealtimeLogs(true)" 
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs transition cursor-pointer">
                <svg id="icon-refresh-logs" class="size-4 text-slate-600 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                <span>Segarkan</span>
            </button>

            <!-- Render Dashboard External Link -->
            <a href="https://dashboard.render.com" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs transition"
               title="Buka console resmi Render.com">
                <span>Render Console</span>
                <span class="text-slate-400">↗</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <!-- Card 1: Server Status -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4.5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Server Host Render</span>
                <h3 id="kpi-server-status" class="text-lg font-black text-slate-900 mt-1 truncate">
                    {{ !empty($logsData['online']) ? 'Online (Render)' : 'Menghubungi...' }}
                </h3>
                <p id="kpi-server-url" class="text-[11px] text-slate-500 font-mono mt-0.5 truncate" title="{{ $baileysBotUrl }}">
                    {{ str_replace(['https://', 'http://'], '', $baileysBotUrl) }}
                </p>
            </div>
            <div class="grid size-11 place-items-center rounded-2xl bg-teal-50 text-teal-600 font-bold text-xl shrink-0">
                ☁️
            </div>
        </div>

        <!-- Card 2: Status WhatsApp Bot -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4.5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">WhatsApp Socket</span>
                <h3 id="kpi-bot-state" class="text-lg font-black {{ ($logsData['connection_state'] ?? '') === 'CONNECTED' ? 'text-emerald-600' : 'text-amber-600' }} mt-1 truncate">
                    {{ ($logsData['connection_state'] ?? '') === 'CONNECTED' ? 'Terhubung (Online)' : ($logsData['connection_state'] ?? 'Menunggu Socket') }}
                </h3>
                <p id="kpi-bot-phone" class="text-[11px] text-slate-500 font-mono mt-0.5 truncate">
                    {{ !empty($logsData['bot_phone']) ? ('+' . $logsData['bot_phone']) : '(Belum Login)' }}
                </p>
            </div>
            <div class="grid size-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-600 font-bold text-xl shrink-0">
                🤖
            </div>
        </div>

        <!-- Card 3: Cron Scheduler Stats -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4.5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Scheduler H+1 Strict 24h</span>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <h3 id="kpi-cron-interval" class="text-lg font-black text-indigo-600 truncate">Tiap 5 Menit</h3>
                </div>
                <p id="kpi-cron-last-run" class="text-[11px] text-slate-500 mt-0.5 truncate">
                    Total Trigger: <strong id="kpi-cron-total" class="text-slate-800">{{ $logsData['cron_stats']['total_runs'] ?? 0 }}x</strong>
                </p>
            </div>
            <div class="grid size-11 place-items-center rounded-2xl bg-indigo-50 text-indigo-600 font-bold text-xl shrink-0">
                ⏰
            </div>
        </div>

        <!-- Card 4: Uptime Service -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4.5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Uptime Server</span>
                <h3 id="kpi-uptime" class="text-lg font-black text-slate-900 mt-1 truncate">
                    {{ $logsData['uptime_formatted'] ?? '0 menit' }}
                </h3>
                <p class="text-[11px] text-emerald-600 font-semibold mt-0.5 flex items-center gap-1 truncate">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                    <span>Auto-Deploy Aktif</span>
                </p>
            </div>
            <div class="grid size-11 place-items-center rounded-2xl bg-purple-50 text-purple-600 font-bold text-xl shrink-0">
                ⏱️
            </div>
        </div>
    </div>

    <!-- Main Card: Live Log Console -->
    <div class="rounded-2xl border border-slate-200/90 bg-white shadow-xs overflow-hidden">
        <!-- Control Header Bar -->
        <div class="p-4 border-b border-slate-100 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between bg-slate-50/50">
            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button" onclick="setLogFilter('ALL')" id="tab-filter-ALL" class="log-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-900 text-white shadow-2xs">
                    Semua Log (<span id="count-all">0</span>)
                </button>
                <button type="button" onclick="setLogFilter('CRON')" id="tab-filter-CRON" class="log-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-white text-slate-600 hover:text-slate-900 border border-slate-200/70">
                    ⏰ Cron Scheduler (<span id="count-cron">0</span>)
                </button>
                <button type="button" onclick="setLogFilter('REACTION')" id="tab-filter-REACTION" class="log-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-white text-slate-600 hover:text-slate-900 border border-slate-200/70">
                    💬 Reaksi Keep PIC (<span id="count-reaction">0</span>)
                </button>
                <button type="button" onclick="setLogFilter('AUTH')" id="tab-filter-AUTH" class="log-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-white text-slate-600 hover:text-slate-900 border border-slate-200/70">
                    🔐 Auth &amp; Socket (<span id="count-auth">0</span>)
                </button>
            </div>

            <!-- Search & Auto-Refresh Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search Input -->
                <div class="relative min-w-[220px]">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    </span>
                    <input type="search" 
                           id="logSearchInput" 
                           placeholder="Cari log..." 
                           oninput="renderLogsTable()"
                           class="w-full rounded-xl border border-slate-200 bg-white pl-8 pr-3 py-1.5 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:outline-none transition">
                </div>

                <!-- Auto-Refresh Toggle -->
                <label class="inline-flex items-center gap-2 cursor-pointer select-none text-xs font-bold text-slate-700 bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-2xs">
                    <input type="checkbox" id="autoRefreshToggle" class="sr-only peer" checked onchange="toggleAutoRefresh(this.checked)">
                    <div class="w-8 h-4 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-600 relative"></div>
                    <span id="labelAutoRefresh">Auto 10s</span>
                </label>
            </div>
        </div>

        <!-- Terminal Table Container -->
        <div class="overflow-x-auto max-h-[620px] overflow-y-auto font-mono text-xs" id="logsScrollContainer">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-slate-900 text-slate-300 font-bold uppercase tracking-wider text-[10px] z-10 border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-32">Waktu (WITA)</th>
                        <th class="py-3 px-4 w-28">Kategori</th>
                        <th class="py-3 px-4 w-24">Status</th>
                        <th class="py-3 px-4">Pesan Aktivitas &amp; Respon</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody" class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400">
                            <div class="inline-flex items-center gap-2">
                                <span class="size-2 rounded-full bg-teal-500 animate-ping"></span>
                                <span>Menghubungi server Render Baileys...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Bar with Quick Tips -->
        <div class="p-3.5 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between text-[11px] text-slate-500 gap-2">
            <div class="flex items-center gap-2">
                <span>⚡ <strong>Mekanisme Cron:</strong> Node.js di Render memanggil <code>https://dcms-sosmed.my.id/system/cron-run?key=dcms2026</code> setiap 5 menit.</span>
            </div>
            <div class="font-mono text-slate-400">
                Terakhir Diperbarui: <span id="labelLastUpdated">-</span>
            </div>
        </div>
    </div>
</div>

<script>
let currentLogs = [];
let currentFilter = 'ALL';
let autoRefreshTimer = null;
let isFetching = false;

function formatBadgeLevel(level) {
    level = (level || 'INFO').toUpperCase();
    if (level === 'SUCCESS') {
        return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">✅ SUKSES</span>';
    } else if (level === 'ERROR') {
        return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">❌ GAGAL</span>';
    } else if (level === 'WARN') {
        return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">⚠️ PERHATIAN</span>';
    }
    return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">ℹ️ INFO</span>';
}

function formatBadgeType(type) {
    type = (type || 'SYSTEM').toUpperCase();
    if (type === 'CRON') {
        return '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">⏰ CRON</span>';
    } else if (type === 'REACTION') {
        return '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">💬 REAKSI</span>';
    } else if (type === 'AUTH') {
        return '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">🔐 AUTH</span>';
    } else if (type === 'SEND') {
        return '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">📲 OUTBOUND</span>';
    }
    return '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">⚙️ SISTEM</span>';
}

function setLogFilter(filter) {
    currentFilter = filter;
    document.querySelectorAll('.log-filter-btn').forEach(btn => {
        btn.className = 'log-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-white text-slate-600 hover:text-slate-900 border border-slate-200/70 cursor-pointer';
    });

    const activeBtn = document.getElementById('tab-filter-' + filter);
    if (activeBtn) {
        activeBtn.className = 'log-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-900 text-white shadow-2xs cursor-pointer';
    }

    renderLogsTable();
}

function renderLogsTable() {
    const tbody = document.getElementById('logsTableBody');
    if (!tbody) return;

    const search = (document.getElementById('logSearchInput')?.value || '').toLowerCase().trim();

    const filtered = currentLogs.filter(item => {
        if (currentFilter !== 'ALL' && item.type !== currentFilter) return false;
        if (search) {
            const haystack = ((item.message || '') + ' ' + (item.detail || '') + ' ' + (item.time || '') + ' ' + (item.type || '')).toLowerCase();
            return haystack.includes(search);
        }
        return true;
    });

    // Update counts
    const countAll = currentLogs.length;
    const countCron = currentLogs.filter(i => i.type === 'CRON').length;
    const countReaction = currentLogs.filter(i => i.type === 'REACTION').length;
    const countAuth = currentLogs.filter(i => i.type === 'AUTH').length;

    if (document.getElementById('count-all')) document.getElementById('count-all').innerText = countAll;
    if (document.getElementById('count-cron')) document.getElementById('count-cron').innerText = countCron;
    if (document.getElementById('count-reaction')) document.getElementById('count-reaction').innerText = countReaction;
    if (document.getElementById('count-auth')) document.getElementById('count-auth').innerText = countAuth;

    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="p-8 text-center text-slate-400">
                    Belum ada log yang sesuai dengan filter.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filtered.map(log => {
        return `
            <tr class="hover:bg-slate-50/80 transition">
                <td class="py-2.5 px-4 font-mono text-slate-600 whitespace-nowrap align-top">
                    ${log.time || '-'}
                </td>
                <td class="py-2.5 px-4 align-top whitespace-nowrap">
                    ${formatBadgeType(log.type)}
                </td>
                <td class="py-2.5 px-4 align-top whitespace-nowrap">
                    ${formatBadgeLevel(log.level)}
                </td>
                <td class="py-2.5 px-4 text-slate-800 leading-relaxed align-top">
                    <div class="font-bold text-xs">${escapeHtml(log.message || '-')}</div>
                    ${log.detail ? `<div class="mt-1 text-[11px] font-mono text-slate-500 bg-slate-50 p-1.5 rounded border border-slate-200/60 whitespace-pre-wrap break-words">${escapeHtml(log.detail)}</div>` : ''}
                </td>
            </tr>
        `;
    }).join('');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

async function fetchRealtimeLogs(isManual = false) {
    if (isFetching) return;
    isFetching = true;

    const iconRefresh = document.getElementById('icon-refresh-logs');
    if (iconRefresh && isManual) {
        iconRefresh.classList.add('animate-spin');
    }

    try {
        const res = await fetch('/api/settings/baileys-logs/data', {
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        // Update KPIs
        if (document.getElementById('kpi-server-status')) {
            document.getElementById('kpi-server-status').innerText = data.online ? 'Online (Render)' : 'Offline / Error';
            document.getElementById('kpi-server-status').className = 'text-lg font-black mt-1 truncate ' + (data.online ? 'text-slate-900' : 'text-rose-600');
        }

        const badgeRender = document.getElementById('badge-render-status');
        const textRender = document.getElementById('text-render-status');
        if (badgeRender && textRender) {
            if (data.online) {
                badgeRender.className = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                textRender.innerText = 'Server Render Online';
            } else {
                badgeRender.className = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200';
                textRender.innerText = 'Server Render Offline';
            }
        }

        if (document.getElementById('kpi-bot-state')) {
            const isConn = data.connection_state === 'CONNECTED';
            document.getElementById('kpi-bot-state').innerText = isConn ? 'Terhubung (Online)' : (data.connection_state || 'Menunggu QR');
            document.getElementById('kpi-bot-state').className = 'text-lg font-black mt-1 truncate ' + (isConn ? 'text-emerald-600' : 'text-amber-600');
        }

        if (document.getElementById('kpi-bot-phone')) {
            document.getElementById('kpi-bot-phone').innerText = data.bot_phone ? ('+' + data.bot_phone) : '(Belum Login)';
        }

        if (document.getElementById('kpi-uptime')) {
            document.getElementById('kpi-uptime').innerText = data.uptime_formatted || '0 menit';
        }

        if (document.getElementById('kpi-cron-total')) {
            document.getElementById('kpi-cron-total').innerText = (data.cron_stats?.total_runs || 0) + 'x';
        }

        currentLogs = data.logs || [];
        renderLogsTable();

        const labelLastUpdated = document.getElementById('labelLastUpdated');
        if (labelLastUpdated) {
            const now = new Date();
            labelLastUpdated.innerText = now.toLocaleTimeString('id-ID', { timeZone: 'Asia/Makassar' }) + ' WITA';
        }
    } catch (err) {
        console.warn('Gagal fetch live logs:', err);
    } finally {
        isFetching = false;
        if (iconRefresh) {
            iconRefresh.classList.remove('animate-spin');
        }
    }
}

async function triggerCronManually(btn) {
    if (!confirm('Picu eksekusi Cron Auto Follow-Up H+1 sekarang juga?\nSistem akan memproses semua keep yang sudah mencapai 24 jam.')) return;

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-flex items-center gap-1.5"><span class="size-3 rounded-full border-2 border-white border-t-transparent animate-spin"></span><span>Memproses...</span></span>';

    try {
        const res = await fetch('/api/settings/baileys-logs/trigger-cron', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ force: false })
        });

        const json = await res.json();
        if (json.success) {
            alert(`✅ ${json.message}\nWaktu: ${json.executed_at}\nTerkirim: ${json.data?.sent_count || 0} keep`);
            fetchRealtimeLogs(true);
        } else {
            alert(`❌ Gagal: ${json.message || 'Unknown error'}`);
        }
    } catch (err) {
        alert('Terjadi kesalahan: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function toggleAutoRefresh(enabled) {
    if (autoRefreshTimer) {
        clearInterval(autoRefreshTimer);
        autoRefreshTimer = null;
    }
    const label = document.getElementById('labelAutoRefresh');
    if (enabled) {
        if (label) label.innerText = 'Auto 10s';
        autoRefreshTimer = setInterval(() => fetchRealtimeLogs(false), 10000);
    } else {
        if (label) label.innerText = 'Manual';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    fetchRealtimeLogs(false);
    toggleAutoRefresh(true);
});
</script>
@endsection
