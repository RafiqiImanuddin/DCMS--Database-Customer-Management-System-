<!-- TAB 12: REKAP AUTO FOLLOW UP H+1 FORMAT KEEP -->
<section id="tabContent12" class="tab-content mb-16 {{ (int) ($activeTab ?? 1) === 12 ? 'active' : '' }}">
    <div class="w-full space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-3xl bg-white p-6 shadow-xs border border-slate-200/80">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 border border-teal-200/70 px-2.5 py-0.5 text-[11px] font-bold text-teal-700">
                    <span class="size-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                    <span>Otomasi WhatsApp Baileys</span>
                </span>
                <span class="text-xs text-slate-300">•</span>
                <span class="text-xs font-semibold text-slate-500">Format Keep x Follow Up H+1</span>
            </div>
            <h2 class="mt-1 text-xl sm:text-2xl font-black tracking-tight text-slate-900">
                Rekap Auto Follow Up H+1
            </h2>
            <p class="text-xs text-slate-500 sm:text-sm mt-0.5">
                Monitoring dan pengiriman broadcast WhatsApp rekap customer keep H+1 ke PIC Sales via Baileys Bot Gateway.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Baileys Bot Status Indicator -->
            <div id="fu12BaileysStatusPill" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 border border-slate-200 px-3.5 py-2 text-xs font-bold text-slate-600">
                <span class="size-2 rounded-full bg-slate-400"></span>
                <span id="fu12BaileysStatusText">Bot Baileys (+62 851-1142-6699) Memeriksa...</span>
            </div>

            <!-- Settings Button -->
            <button type="button" onclick="switchFu12View('settings')" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs transition cursor-pointer" title="Pengaturan Jadwal & Template Pesan">
                <svg class="size-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                <span>Pengaturan &amp; Template</span>
            </button>

            <!-- Refresh Button -->
            <button type="button" onclick="loadAutoFuH1Data()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs transition cursor-pointer" title="Muat ulang data">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                <span>Refresh</span>
            </button>

            <!-- Broadcast Trigger Button -->
            <button type="button" onclick="confirmSendAllH1Reminders()" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-xs font-bold shadow-sm shadow-emerald-600/20 transition cursor-pointer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                <span>⚡ Kirim Semua Reminder WA</span>
            </button>
        </div>
    </div>

    <!-- Filter & Date Controls (Hanya tampil di Tab 1 & Tab 2) -->
    <div id="fu12FilterSection" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label for="fu12TargetDateInput" class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tanggal Keep (H-1):</label>
                    <input type="date" id="fu12TargetDateInput" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-800 focus:bg-white focus:border-brand-500 focus:outline-none transition cursor-pointer" onchange="onAutoFuDateChange()">
                </div>
                <span id="fu12TargetDateBadge" class="inline-flex items-center rounded-lg bg-brand-50 border border-brand-200/60 px-2.5 py-1 text-xs font-bold text-brand-700">
                    Kemarin
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Search Box -->
                <div class="relative min-w-[220px]">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    </span>
                    <input type="search" 
                           id="fu12SearchInput" 
                           name="fu12_search_query_unique" 
                           autocomplete="new-password" 
                           autocorrect="off" 
                           autocapitalize="off" 
                           spellcheck="false" 
                           data-lpignore="true" 
                           data-form-type="other" 
                           readonly 
                           onfocus="this.removeAttribute('readonly');" 
                           placeholder="Cari PIC, Customer, Barang..." 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-8 py-1.5 text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-brand-500 focus:outline-none transition" 
                           oninput="toggleFu12ClearBtn(); if(window.debouncedApplyAutoFuFilter){window.debouncedApplyAutoFuFilter();}else if(window.applyAutoFuFilter){applyAutoFuFilter();}">
                    <button type="button" 
                            id="btnFu12ClearSearch" 
                            onclick="clearFu12Search()" 
                            class="absolute inset-y-0 right-0 hidden items-center pr-2.5 text-slate-400 hover:text-slate-600 transition cursor-pointer" 
                            title="Hapus pencarian">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Status Filter Dropdown -->
                <select id="fu12StatusFilter" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none transition cursor-pointer" onchange="applyAutoFuFilter()">
                    <option value="ALL">Semua Status Keep</option>
                    <option value="ON_PROGRESS">Masih On Progress</option>
                    <option value="WIN">Sudah Closing (WIN)</option>
                    <option value="LOSE">Batal / Lose</option>
                </select>

                <!-- WA Status Filter -->
                <select id="fu12WaStatusFilter" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none transition cursor-pointer" onchange="applyAutoFuFilter()">
                    <option value="ALL">Semua Status Notif WA</option>
                    <option value="SENT">✅ Sudah Terkirim</option>
                    <option value="PENDING">⏳ Belum Terkirim</option>
                </select>
            </div>
        </div>
    </div>

    <!-- KPI Summary Grid (4 Cards) -->
    <div id="fu12KpiSection" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Customer Keep H+1 -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Customer Keep H+1</p>
                <h3 id="fu12KpiTotalKeeps" class="text-2xl font-black text-slate-900 mt-1">0</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Total transaksi keep kemarin</p>
            </div>
            <div class="grid size-12 place-items-center rounded-2xl bg-brand-50 text-brand-600 font-bold text-xl">
                📋
            </div>
        </div>

        <!-- Card 2: PIC Sales Terlibat -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">PIC Sales Terlibat</p>
                <h3 id="fu12KpiTotalPics" class="text-2xl font-black text-slate-900 mt-1">0</h3>
                <p class="text-[11px] text-slate-400 mt-0.5"><span id="fu12KpiUnassigned" class="text-amber-600 font-semibold">0</span> belum ada PIC</p>
            </div>
            <div class="grid size-12 place-items-center rounded-2xl bg-indigo-50 text-indigo-600 font-bold text-xl">
                👥
            </div>
        </div>

        <!-- Card 3: Status Notifikasi WhatsApp -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Status Kirim WA</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 id="fu12KpiSentPics" class="text-2xl font-black text-emerald-600">0</h3>
                    <span class="text-xs text-slate-400 font-bold">/ <span id="fu12KpiTotalPicsTarget">0</span> PIC</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5"><span id="fu12KpiPendingPics" class="text-amber-600 font-bold">0</span> PIC belum dikirim</p>
            </div>
            <div class="grid size-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-600 font-bold text-xl">
                📲
            </div>
        </div>

        <!-- Card 4: Closing WIN Rate -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Hasil Closing (WIN)</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 id="fu12KpiTotalWin" class="text-2xl font-black text-emerald-600">0</h3>
                    <span id="fu12KpiWinRateBadge" class="text-[11px] font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/70">0%</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5"><span id="fu12KpiOnProgress" class="text-brand-600 font-semibold">0</span> masih On Progress</p>
            </div>
            <div class="grid size-12 place-items-center rounded-2xl bg-yellow-50 text-yellow-600 font-bold text-xl">
                🏆
            </div>
        </div>
    </div>

    <!-- Sub-View Switcher (4 Tabs: Rekap per PIC vs Detail Semua Customer vs Riwayat Pengiriman vs Pengaturan Jadwal) -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 rounded-2xl w-fit border border-slate-200/80 flex-wrap">
        <button id="fu12ViewBtnCards" type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition cursor-pointer bg-white text-slate-900 shadow-xs border border-slate-200/80" onclick="switchFu12View('cards')">
            <span>👥</span>
            <span>Rangkuman Rekap per PIC</span>
        </button>
        <button id="fu12ViewBtnTable" type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-white/60" onclick="switchFu12View('table')">
            <span>📋</span>
            <span>Tabel Detail Seluruh Customer</span>
        </button>
        <button id="fu12ViewBtnHistory" type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-white/60" onclick="switchFu12View('history')">
            <span>📜</span>
            <span>Riwayat &amp; Status Pengiriman</span>
        </button>
        <button id="fu12ViewBtnSettings" type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-white/60" onclick="switchFu12View('settings')">
            <span>⚙️</span>
            <span>Pengaturan Jadwal &amp; Template</span>
        </button>
    </div>

    <!-- VIEW 1: REKAP PER PIC SALES (ROSTER TABLE EFISIEN & ACCORDION DRAWER) -->
    <div id="fu12ViewCards" class="space-y-4">
        <div class="rounded-2xl border border-slate-200/90 bg-white overflow-hidden shadow-xs">
            <!-- Header Toolbar Tabel Rekap PIC -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-b border-slate-200/80 bg-slate-50/70">
                <div class="flex items-center gap-2.5">
                    <div class="grid size-8 place-items-center rounded-xl bg-teal-500/10 text-teal-700 font-bold text-sm">
                        👥
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Rangkuman Rekap PIC Sales</h3>
                            <span id="fu12PicCountBadge" class="inline-flex items-center rounded-full bg-slate-200/80 px-2 py-0.5 text-[10px] font-bold text-slate-700">0 PIC</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Tampilan efisien per PIC sales &bull; Klik tombol <span class="font-bold text-slate-700">Rincian</span> untuk melihat customer keep masing-masing PIC</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-center">
                    <button type="button" onclick="toggleAllFu12Drawers(true)" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs transition cursor-pointer" title="Buka seluruh rincian customer">
                        <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                        <span>Buka Semua</span>
                    </button>
                    <button type="button" onclick="toggleAllFu12Drawers(false)" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs transition cursor-pointer" title="Tutup seluruh rincian customer">
                        <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                        <span>Tutup Semua</span>
                    </button>
                </div>
            </div>

            <!-- Table Roster PIC -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse" id="fu12PicRosterTable">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-600 text-[10px] font-bold uppercase tracking-wider">
                            <th class="py-3 px-3.5 text-center w-12">NO</th>
                            <th class="py-3 px-4 min-w-[220px]">PIC SALES (FOLLOW UP)</th>
                            <th class="py-3 px-3.5 min-w-[120px]">CABANG</th>
                            <th class="py-3 px-3.5 text-center min-w-[100px]">TOTAL KEEP</th>
                            <th class="py-3 px-3.5 min-w-[190px]">STATUS PROGRES</th>
                            <th class="py-3 px-3.5 text-center min-w-[140px]">NOTIF WA PIC</th>
                            <th class="py-3 px-3.5 text-center min-w-[130px]">RINCIAN</th>
                            <th class="py-3 px-4 text-center min-w-[160px]">AKSI WHATSAPP</th>
                        </tr>
                    </thead>
                    <tbody id="fu12CardsContainer" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                <div class="inline-flex items-center gap-2">
                                    <svg class="animate-spin size-4 text-brand-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span>Memuat rangkuman rekap PIC...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- VIEW 2: TABEL DETAIL SELURUH CUSTOMER KEEP -->
    <div id="fu12ViewTable" class="space-y-4" style="display:none;">
        <div class="rounded-2xl border border-slate-200/80 bg-white overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse" id="fu12AllItemsTable">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-600 text-[10px] font-bold uppercase tracking-wider">
                            <th class="py-3 px-3.5 text-center w-12">NO</th>
                            <th class="py-3 px-3.5">WAKTU KEEP</th>
                            <th class="py-3 px-3.5">CUSTOMER &amp; WA</th>
                            <th class="py-3 px-3.5">BARANG KEEP</th>
                            <th class="py-3 px-3.5">CABANG</th>
                            <th class="py-3 px-3.5">PIC SALES (FU)</th>
                            <th class="py-3 px-3.5 text-center">STATUS KEEP</th>
                            <th class="py-3 px-3.5 text-center">NOTIF WA PIC</th>
                            <th class="py-3 px-3.5 text-center">AKSI CHAT</th>
                        </tr>
                    </thead>
                    <tbody id="fu12AllItemsTableBody" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">Memuat rincian customer...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- VIEW 3: PENGATURAN JADWAL & TEMPLATE PESAN -->
    <div id="fu12ViewSettings" class="space-y-6" style="display:none;">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Kolom Kiri: Form Konfigurasi (7 Kolom) -->
            <div class="lg:col-span-7 space-y-5">
                <!-- Banner Gateway Baileys Dedicated (+62 851-1142-6699) -->
                <div class="rounded-2xl border border-teal-200/80 bg-gradient-to-r from-teal-50/90 via-emerald-50/60 to-teal-50/90 p-4 shadow-2xs">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl shrink-0">🤖</span>
                        <div class="space-y-1 text-xs">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-black text-teal-950">Gateway WhatsApp: Baileys Bot Eksklusif</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 px-2.5 py-0.5 text-[10px] font-bold">100% Baileys Gateway</span>
                            </div>
                            <p class="text-slate-600 text-[11px] leading-relaxed">
                                Nomor Pengirim Resmi: <strong class="font-mono text-teal-950 font-black bg-white px-2 py-0.5 rounded-md border border-teal-200 text-xs">+62 851-1142-6699</strong>. Seluruh proses pengiriman pesan Auto Follow-Up H+1 ditangani secara langsung oleh server Baileys tanpa menggunakan pihak ketiga (WatZap Cloud).
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Box 1: Jadwal Pengiriman Otomatis & Jam Operasional (Strict 1x24 Jam) -->
                <div class="rounded-2xl border border-slate-200/90 bg-white p-5 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">⏰</span>
                            <div>
                                <h4 class="text-sm font-black text-slate-900">Jadwal &amp; Jam Operasional (Strict 1x24 Jam)</h4>
                                <p class="text-[11px] text-slate-400">Pengiriman reminder strict 24 jam presisi sejak keep masuk (berjalan tiap 5 menit di jam kerja).</p>
                            </div>
                        </div>
                        <span id="fu12ScheduleStatusBadge" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Aktif</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Toggle Aktifkan Cron -->
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                            <div>
                                <label for="fu12SettingEnabled" class="text-xs font-bold text-slate-800 block cursor-pointer">Auto Follow-Up</label>
                                <span class="text-[11px] text-slate-500">Scheduler cron aktif</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="fu12SettingEnabled" class="sr-only peer" checked onchange="onFu12SettingChange()">
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <!-- Jam Mulai Kerja (Buka Toko) -->
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1">
                            <label for="fu12SettingWorkStart" class="text-xs font-bold text-slate-800 block">Jam Buka Toko (WITA)</label>
                            <div class="flex items-center gap-2">
                                <input type="time" id="fu12SettingWorkStart" value="08:00" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-black text-slate-900 focus:border-brand-500 focus:outline-none transition cursor-pointer" oninput="onFu12SettingChange()">
                                <span class="text-[11px] text-slate-500 font-medium">Mulai Kerja</span>
                            </div>
                        </div>

                        <!-- Jam Selesai Kerja (Tutup Toko) -->
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1">
                            <label for="fu12SettingWorkEnd" class="text-xs font-bold text-slate-800 block">Jam Tutup Toko (WITA)</label>
                            <div class="flex items-center gap-2">
                                <input type="time" id="fu12SettingWorkEnd" value="22:00" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-black text-slate-900 focus:border-brand-500 focus:outline-none transition cursor-pointer" oninput="onFu12SettingChange()">
                                <span class="text-[11px] text-slate-500 font-medium">Batas Malam</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-200/70 text-xs text-blue-900 leading-relaxed flex items-start gap-2.5">
                        <span class="text-base shrink-0">💡</span>
                        <div class="text-[11px] space-y-1">
                            <div><strong>Sistem Strict 1x24 Jam Presisi:</strong> Cron scheduler mengecek antrean setiap 5 menit (<code>php artisan keep:send-auto-fu-h1</code>). Jika format keep masuk kemarin jam 09:32, reminder terkirim tepat jam 09:32 hari ini.</div>
                            <div class="text-slate-600">🌙 <strong>Proteksi Malam Hari:</strong> Di luar jam operasional (22:00 - 07:59 WITA), bot <strong>TIDAK</strong> akan mengirim pesan agar tidak mengganggu istirahat. Keep yang jatuh tempo malam hari akan tertahan dan dikirimkan saat toko buka pukul 08:00 pagi (dirangkum jika &ge; 2 keep).</div>
                        </div>
                    </div>
                </div>

                <!-- Box 1.5: Proteksi Anti-Suspend WhatsApp (Jeda Acak & Rest Cycle) -->
                <div class="rounded-2xl border border-slate-200/90 bg-white p-5 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">🛡️</span>
                            <div>
                                <h4 class="text-sm font-black text-slate-900">Proteksi Anti-Suspend &amp; Jeda Pengiriman</h4>
                                <p class="text-[11px] text-slate-400">Atur jeda acak antar pesan dan siklus istirahat (rest cycle) untuk meminimalisir risiko suspend / pemblokiran dari WhatsApp.</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                            <span>⚡ Humanized Jitter</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Jeda Antar Pesan (Delay Min - Max) -->
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                    <span>⏱️</span>
                                    <span>1. Jeda Antar Pesan</span>
                                </span>
                                <span class="rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800">Random Jitter</span>
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Bot memberi jeda waktu acak antar pesan PIC agar ritme bervariasi dan tidak terbaca bot oleh WhatsApp.
                            </p>
                            <div class="grid grid-cols-2 gap-2.5 pt-1">
                                <div>
                                    <label for="fu12DelayMin" class="text-[11px] font-bold text-slate-700 block mb-1">Jeda Min</label>
                                    <div class="relative">
                                        <input type="number" id="fu12DelayMin" min="1" max="60" value="3" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-mono font-bold text-slate-900 focus:border-brand-500 focus:outline-none transition pr-11">
                                        <span class="absolute right-2 top-2 text-[10px] text-slate-400 font-semibold pointer-events-none">detik</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-0.5 block">Saran: 3-5 dtk</span>
                                </div>
                                <div>
                                    <label for="fu12DelayMax" class="text-[11px] font-bold text-slate-700 block mb-1">Jeda Max</label>
                                    <div class="relative">
                                        <input type="number" id="fu12DelayMax" min="1" max="180" value="8" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-mono font-bold text-slate-900 focus:border-brand-500 focus:outline-none transition pr-11">
                                        <span class="absolute right-2 top-2 text-[10px] text-slate-400 font-semibold pointer-events-none">detik</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-0.5 block">Saran: 8-15 dtk</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rest Cycle / Istirahat Berkala (Batch Cooldown) -->
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                    <span>☕</span>
                                    <span>2. Rest Cycle (Cooldown)</span>
                                </span>
                                <span class="rounded bg-purple-100 px-2 py-0.5 text-[10px] font-bold text-purple-800">Cegah Flood Ban</span>
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Bot istirahat sejenak setelah mengirim sejumlah pesan berturut-turut untuk memutus pola lonjakan trafik.
                            </p>
                            <div class="grid grid-cols-2 gap-2.5 pt-1">
                                <div>
                                    <label for="fu12BatchSize" class="text-[11px] font-bold text-slate-700 block mb-1">Rest Setiap</label>
                                    <div class="relative">
                                        <input type="number" id="fu12BatchSize" min="2" max="100" value="10" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-mono font-bold text-slate-900 focus:border-brand-500 focus:outline-none transition pr-12">
                                        <span class="absolute right-2 top-2 text-[10px] text-slate-400 font-semibold pointer-events-none">pesan</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-0.5 block">Saran: 10-15 pesan</span>
                                </div>
                                <div>
                                    <label for="fu12BatchCooldown" class="text-[11px] font-bold text-slate-700 block mb-1">Durasi Rest</label>
                                    <div class="relative">
                                        <input type="number" id="fu12BatchCooldown" min="0" max="600" value="30" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-mono font-bold text-slate-900 focus:border-brand-500 focus:outline-none transition pr-11">
                                        <span class="absolute right-2 top-2 text-[10px] text-slate-400 font-semibold pointer-events-none">detik</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-0.5 block">Saran: 30-60 dtk</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-purple-50/70 border border-purple-200/70 text-xs text-purple-900 leading-relaxed flex items-start gap-2.5">
                        <span class="text-base shrink-0">💡</span>
                        <div class="text-[11px]">
                            <strong>Tips Keamanan Broadcast:</strong> Jeda acak 3-8 detik dengan istirahat 30 detik tiap 10 pesan sangat ideal untuk puluhan PIC sales tanpa membebani gateway bot dan aman dari deteksi anomali WhatsApp.
                        </div>
                    </div>
                </div>

                <!-- Box 2: Template Teks Pesan WhatsApp -->
                <div class="rounded-2xl border border-slate-200/90 bg-white p-5 space-y-5 shadow-xs">
                    <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">✍️</span>
                            <div>
                                <h4 class="text-sm font-black text-slate-900">Kustomisasi Template Pesan WhatsApp</h4>
                                <p class="text-[11px] text-slate-400">Atur pesan reminder 24 jam per customer dan rekap pagi bila ada beberapa keep tertahan.</p>
                            </div>
                        </div>
                        <button type="button" onclick="resetFu12TemplateDefault()" class="text-[11px] font-bold text-rose-600 hover:text-rose-700 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200/70 transition cursor-pointer" title="Kembalikan semua teks ke default">
                            🔄 Reset Default
                        </button>
                    </div>

                    <!-- MODEL A: Template Pesan 24 Jam Presisi (Single Alert per Customer) -->
                    <div class="p-4 rounded-xl border border-brand-200/80 bg-brand-50/30 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-base">⏰</span>
                                <h5 class="text-xs font-black text-brand-900 uppercase tracking-wide">Model A: Pesan Presisi 24 Jam (Single Alert per Customer)</h5>
                            </div>
                            <span class="rounded bg-brand-100 px-2 py-0.5 text-[10px] font-bold text-brand-800">08:05 - 22:00 WITA</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Format pesan ini dikirimkan otomatis tepat saat keep mencapai 24 jam (misal masuk kemarin 09:32, dikirim hari ini 09:32).
                        </p>
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between flex-wrap gap-1">
                                <label for="fu12TplSingleAlert" class="text-xs font-bold text-slate-800">Isi Teks Template Model A:</label>
                                <div class="flex items-center gap-1 flex-wrap">
                                    <span class="text-[10px] text-slate-400">Variabel:</span>
                                    <button type="button" onclick="insertFu12Tag('fu12TplSingleAlert', '{pic_name}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{pic_name}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplSingleAlert', '{customer_name}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{customer_name}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplSingleAlert', '{customer_phone}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{customer_phone}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplSingleAlert', '{barang_keep}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{barang_keep}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplSingleAlert', '{cabang}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{cabang}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplSingleAlert', '{kode_sosmed}')" class="px-1.5 py-0.5 rounded bg-brand-100 hover:bg-brand-200 border border-brand-300 text-[10px] font-mono font-bold text-brand-900">{kode_sosmed}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplSingleAlert', '{catatan}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{catatan}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplSingleAlert', '{jam_masuk}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{jam_masuk}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplSingleAlert', '{chat_link}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{chat_link}</button>
                                </div>
                            </div>
                            <textarea id="fu12TplSingleAlert" rows="6" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-xs font-mono text-slate-800 focus:border-brand-500 focus:outline-none transition leading-relaxed resize-y" oninput="updateFu12LivePreview()"></textarea>
                        </div>
                    </div>

                    <!-- MODEL B: Template Rekap Pagi (Jika >= 2 Keep Tertahan Semalam) -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-base">📋</span>
                                <h5 class="text-xs font-black text-slate-800 uppercase tracking-wide">Model B: Rekap Pagi (Jika ≥ 2 Keep Tertahan Semalam)</h5>
                            </div>
                            <span class="rounded bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-800">Buka Toko 08:00 WITA</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Bila dalam 1 PIC terdapat 2 atau lebih customer keep yang tertahan semalam, dikirimkan dalam 1 pesan rekap ringkas ini agar WhatsApp PIC tidak banjir chat.
                        </p>

                        <!-- 1. Header Template -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label for="fu12TplHeader" class="text-xs font-bold text-slate-800">1. Teks Pembuka / Header Rekap:</label>
                                <div class="flex items-center gap-1">
                                    <span class="text-[10px] text-slate-400">Variabel:</span>
                                    <button type="button" onclick="insertFu12Tag('fu12TplHeader', '{pic_name}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{pic_name}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplHeader', '{target_date}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{target_date}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplHeader', '{total_items}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{total_items}</button>
                                </div>
                            </div>
                            <textarea id="fu12TplHeader" rows="3" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-xs font-mono text-slate-800 focus:border-brand-500 focus:outline-none transition leading-relaxed resize-y" oninput="updateFu12LivePreview()"></textarea>
                        </div>

                        <!-- 2. Item Template -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between flex-wrap gap-1">
                                <label for="fu12TplItem" class="text-xs font-bold text-slate-800">2. Format Tiap Baris Customer (Diulang Otomatis):</label>
                                <div class="flex items-center gap-1 flex-wrap">
                                    <span class="text-[10px] text-slate-400">Variabel:</span>
                                    <button type="button" onclick="insertFu12Tag('fu12TplItem', '{num}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{num}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplItem', '{customer_name}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{customer_name}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplItem', '{customer_phone}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{customer_phone}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplItem', '{barang_keep}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{barang_keep}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplItem', '{cabang}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{cabang}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplItem', '{kode_sosmed}')" class="px-1.5 py-0.5 rounded bg-brand-100 hover:bg-brand-200 border border-brand-300 text-[10px] font-mono font-bold text-brand-900">{kode_sosmed}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplItem', '{catatan}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{catatan}</button>
                                    <button type="button" onclick="insertFu12Tag('fu12TplItem', '{chat_link}')" class="px-1.5 py-0.5 rounded bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">{chat_link}</button>
                                </div>
                            </div>
                            <textarea id="fu12TplItem" rows="4" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-xs font-mono text-slate-800 focus:border-brand-500 focus:outline-none transition leading-relaxed resize-y" oninput="updateFu12LivePreview()"></textarea>
                        </div>

                        <!-- 3. Footer Template -->
                        <div class="space-y-1.5">
                            <label for="fu12TplFooter" class="text-xs font-bold text-slate-800 block">3. Teks Penutup / Motivasi Closing:</label>
                            <textarea id="fu12TplFooter" rows="2" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-xs font-mono text-slate-800 focus:border-brand-500 focus:outline-none transition leading-relaxed resize-y" oninput="updateFu12LivePreview()"></textarea>
                        </div>
                    </div>

                    <!-- 4. Draft Greeting Customer for wa.me -->
                    <div class="space-y-1.5 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <label for="fu12GreetingCustomer" class="text-xs font-bold text-slate-800">4. Teks Otomatis Chat WhatsApp Customer (Di dalam Tautan wa.me):</label>
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="insertFu12Tag('fu12GreetingCustomer', '{customer_name}')" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-[10px] font-mono text-slate-700">{customer_name}</button>
                                <button type="button" onclick="insertFu12Tag('fu12GreetingCustomer', '{barang_keep}')" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-[10px] font-mono text-slate-700">{barang_keep}</button>
                                <button type="button" onclick="insertFu12Tag('fu12GreetingCustomer', '{cabang}')" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-[10px] font-mono text-slate-700">{cabang}</button>
                                <button type="button" onclick="insertFu12Tag('fu12GreetingCustomer', '{kode_sosmed}')" class="px-1.5 py-0.5 rounded bg-brand-100 hover:bg-brand-200 border border-brand-300 text-[10px] font-mono font-bold text-brand-900">{kode_sosmed}</button>
                            </div>
                        </div>
                        <textarea id="fu12GreetingCustomer" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs font-mono text-slate-800 focus:bg-white focus:border-brand-500 focus:outline-none transition leading-relaxed resize-y" oninput="updateFu12LivePreview()"></textarea>
                        <p class="text-[11px] text-slate-400">Teks ini akan otomatis terisi saat PIC mengklik tautan chat customer di WhatsApp mereka.</p>
                    </div>

                    <!-- Save Button -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" onclick="saveFu12Settings(this)" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 text-xs font-bold shadow-sm shadow-brand-600/20 transition cursor-pointer">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            <span>Simpan Pengaturan &amp; Template</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Pratinjau Live WhatsApp Chat (5 Kolom) -->
            <div class="lg:col-span-5 sticky top-20 space-y-3">
                <div class="flex items-center justify-between px-1">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        <span>Pratinjau Pesan WhatsApp</span>
                    </span>
                    <!-- Switch Preview Mode -->
                    <div class="inline-flex rounded-lg bg-slate-100 p-0.5 border border-slate-200 text-[10px] font-bold">
                        <button type="button" id="fu12BtnPreviewSingle" onclick="setFu12PreviewMode('single')" class="px-2.5 py-0.5 rounded-md bg-white text-slate-900 shadow-2xs transition">Pesan 24 Jam</button>
                        <button type="button" id="fu12BtnPreviewDigest" onclick="setFu12PreviewMode('digest')" class="px-2.5 py-0.5 rounded-md text-slate-600 hover:text-slate-900 transition">Rekap Pagi</button>
                    </div>
                </div>

                <!-- Smartphone Mock Container -->
                <div class="rounded-3xl border-4 border-slate-800 bg-[#efeae2] p-4 shadow-xl space-y-3 overflow-hidden">
                    <!-- WhatsApp Header Mockup -->
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-300/80">
                        <div class="grid size-9 place-items-center rounded-full bg-emerald-600 font-bold text-white text-xs">
                            🤖
                        </div>
                        <div class="min-w-0 flex-1">
                            <h5 class="text-xs font-black text-slate-900 leading-tight">Bot Gadgetmart Sosmed</h5>
                            <span class="text-[10px] text-emerald-700 font-medium">online</span>
                        </div>
                    </div>

                    <!-- Chat Bubble Message -->
                    <div class="flex flex-col items-start">
                        <div class="relative max-w-full rounded-2xl rounded-tl-xs bg-white text-slate-900 p-4 shadow-sm border border-slate-200/60 text-xs font-mono leading-relaxed whitespace-pre-wrap break-words" id="fu12LivePreviewBox">
                            Memuat pratinjau pesan...
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 self-end font-mono" id="fu12LivePreviewTime">09:32 WITA ✓✓</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- VIEW 4: RIWAYAT & STATUS PENGIRIMAN (LOG LENGKAP DETAIL GAGAL VS BERHASIL) -->
    <div id="fu12ViewHistory" class="space-y-4" style="display:none;">
        <!-- Ringkasan Status Pengiriman Mini Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Target PIC</span>
                    <span id="fu12HistStatTotal" class="text-xl font-black text-slate-900 mt-0.5 block">0</span>
                    <span class="text-[10px] text-slate-400">Total PIC terdaftar</span>
                </div>
                <div class="grid size-10 place-items-center rounded-xl bg-slate-100 text-slate-600 font-bold text-base">
                    👥
                </div>
            </div>

            <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-3.5 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider block">Berhasil Terkirim</span>
                    <span id="fu12HistStatSent" class="text-xl font-black text-emerald-800 mt-0.5 block">0</span>
                    <span class="text-[10px] text-emerald-600 font-medium">Terkirim ke WhatsApp PIC</span>
                </div>
                <div class="grid size-10 place-items-center rounded-xl bg-emerald-500/10 text-emerald-600 font-bold text-base">
                    ✅
                </div>
            </div>

            <div class="rounded-2xl border border-rose-200/80 bg-rose-50/50 p-3.5 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider block">Gagal / Invalid</span>
                    <span id="fu12HistStatFailed" class="text-xl font-black text-rose-800 mt-0.5 block">0</span>
                    <span class="text-[10px] text-rose-600 font-medium">Nomor LID / Error sistem</span>
                </div>
                <div class="grid size-10 place-items-center rounded-xl bg-rose-500/10 text-rose-600 font-bold text-base">
                    ❌
                </div>
            </div>

            <div class="rounded-2xl border border-amber-200/80 bg-amber-50/50 p-3.5 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider block">Pending / Antrean</span>
                    <span id="fu12HistStatPending" class="text-xl font-black text-amber-800 mt-0.5 block">0</span>
                    <span class="text-[10px] text-amber-600 font-medium">Menunggu dikirim</span>
                </div>
                <div class="grid size-10 place-items-center rounded-xl bg-amber-500/10 text-amber-600 font-bold text-base">
                    ⏳
                </div>
            </div>
        </div>

        <!-- Tabel Log Riwayat Pengiriman -->
        <div class="rounded-2xl border border-slate-200/90 bg-white overflow-hidden shadow-xs">
            <!-- Header Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-b border-slate-200/80 bg-slate-50/70">
                <div class="flex items-center gap-2.5">
                    <div class="grid size-8 place-items-center rounded-xl bg-teal-500/10 text-teal-700 font-bold text-sm">
                        📜
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Log Riwayat &amp; Status Pengiriman</h3>
                            <span id="fu12HistCountBadge" class="inline-flex items-center rounded-full bg-slate-200/80 px-2 py-0.5 text-[10px] font-bold text-slate-700">0 Data</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Pantau status terkirim dan detail penyebab jika pesan reminder gagal terkirim</p>
                    </div>
                </div>

                <!-- History Specific Filters -->
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="relative">
                        <input type="search" id="fu12HistSearchInput" name="fu12_history_search_query" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other" readonly onfocus="this.removeAttribute('readonly');" oninput="renderFu12HistoryTable()" placeholder="Cari PIC, Cabang, Catatan..." class="w-48 sm:w-60 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                    <select id="fu12HistStatusFilter" onchange="renderFu12HistoryTable()" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 focus:border-teal-500 focus:outline-none">
                        <option value="ALL">Semua Status (Semua)</option>
                        <option value="SENT">✅ Berhasil Terkirim</option>
                        <option value="FAILED">❌ Gagal Kirim / Error Gateway</option>
                        <option value="INVALID">⚠️ Nomor Tidak Valid / LID</option>
                        <option value="PENDING">⏳ Belum Dikirim / Pending</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Riwayat -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-600 divide-x divide-slate-100">
                            <th class="py-3 px-3.5 text-center w-12">No</th>
                            <th class="py-3 px-3.5 w-36">Waktu Kirim</th>
                            <th class="py-3 px-4 min-w-[180px]">PIC Sales &amp; Cabang</th>
                            <th class="py-3 px-3.5 min-w-[140px]">Nomor WhatsApp</th>
                            <th class="py-3 px-3.5 text-center w-24">Keep</th>
                            <th class="py-3 px-3.5 text-center w-32">Status Kirim</th>
                            <th class="py-3 px-4 min-w-[260px]">Keterangan / Diagnosa Sistem</th>
                            <th class="py-3 px-3.5 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="fu12HistoryTableBody" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 text-xs">Memuat data riwayat...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Live Broadcast Progress Modal (Tampilan Rapih & Modern Tanpa Native Browser Alert) -->
    <div id="fu12ProgressModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" style="display:none;">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- 1. Active Progress State -->
            <div id="fu12ProgressActiveContent" class="p-6 space-y-5">
                <div class="flex items-center gap-3">
                    <div class="grid size-11 place-items-center rounded-xl bg-teal-50 border border-teal-200 text-xl shrink-0">
                        🤖
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Mengirim Reminder Auto Follow-Up H+1</h3>
                        <p class="text-xs text-slate-500 font-mono">Gateway: Baileys Bot (+62 851-1142-6699)</p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span id="fu12ProgressCounter" class="text-slate-700">Memulai pengiriman...</span>
                        <span id="fu12ProgressPercent" class="text-teal-600 font-mono" style="color: #0d9488;">0%</span>
                    </div>
                    <div class="w-full rounded-full h-3.5 overflow-hidden border p-0.5" style="background-color: #f1f5f9; border-color: #cbd5e1;">
                        <div id="fu12ProgressBar" class="h-full rounded-full transition-all duration-300" style="width: 0%; background: linear-gradient(90deg, #0d9488 0%, #10b981 100%); box-shadow: 0 1px 3px rgba(16, 185, 129, 0.4);"></div>
                    </div>
                </div>

                <!-- Live Status Card -->
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 space-y-1 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-teal-500 animate-ping"></span>
                        <span id="fu12ProgressCurrentPic" class="font-bold text-slate-800 truncate">Menyiapkan antrean PIC...</span>
                    </div>
                    <div id="fu12ProgressDetail" class="text-[11px] text-slate-500 font-mono truncate">
                        Proteksi Anti-Suspend WhatsApp aktif
                    </div>
                </div>

                <!-- Action / Cancel -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <span id="fu12ProgressSummary" class="text-xs text-slate-500 font-medium">0 Terkirim &middot; 0 Gagal</span>
                    <button type="button" id="btnCancelFu12Broadcast" onclick="cancelFu12Broadcast()" class="rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold px-3 py-1.5 text-xs transition cursor-pointer">
                        Hentikan
                    </button>
                </div>
            </div>

            <!-- 2. Completion / Done State -->
            <div id="fu12ProgressDoneContent" class="p-6 space-y-5 text-center" style="display:none;">
                <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-emerald-50 border border-emerald-200 text-3xl shadow-xs">
                    🎉
                </div>

                <div class="space-y-1">
                    <h3 id="fu12DoneTitle" class="text-base font-black text-slate-900">Pengiriman Selesai!</h3>
                    <p class="text-xs text-slate-500">
                        Reminder auto follow-up telah diproses via Bot Baileys (+62 851-1142-6699).
                    </p>
                </div>

                <!-- Result Box Stats -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 text-center">
                        <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider block">Terkirim</span>
                        <span id="fu12DoneSentCount" class="text-2xl font-black text-emerald-800 mt-0.5 block">0</span>
                        <span class="text-[10px] text-emerald-600 font-medium">Pesan Berhasil</span>
                    </div>
                    <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-3 text-center">
                        <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider block">Gagal / Dilewati</span>
                        <span id="fu12DoneFailedCount" class="text-2xl font-black text-rose-800 mt-0.5 block">0</span>
                        <span class="text-[10px] text-rose-600 font-medium">LID / Invalid / Error</span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80 leading-relaxed text-left">
                    💡 <span class="font-bold text-slate-700">Tips:</span> Anda dapat memeriksa rincian status pengiriman dan penyebab kegagalan secara detail pada sub-tab <span class="font-bold text-teal-700">Riwayat &amp; Status Pengiriman</span>.
                </p>

                <div class="flex flex-col gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeFu12ModalAndOpenHistory()" class="w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>📜 Buka Tab Riwayat &amp; Status Pengiriman</span>
                    </button>
                    <button type="button" onclick="closeFu12ProgressModal()" class="w-full py-2 px-4 rounded-xl border border-slate-200 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Koreksi Nomor PIC (Atasi Nomor LID / Invalid) -->
    <div id="fu12UpdatePhoneModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" style="display:none;">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-amber-50 to-orange-50">
                <div class="flex items-center gap-2.5">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-amber-500 text-white font-black text-base shadow-xs">📱</span>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 leading-tight">Koreksi Nomor WhatsApp PIC</h4>
                        <p class="text-[11px] text-slate-500">Perbaiki nomor LID WhatsApp ke nomor HP seluler asli</p>
                    </div>
                </div>
                <button type="button" onclick="closeFu12UpdatePhoneModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1 cursor-pointer">&times;</button>
            </div>
            <div class="p-5 space-y-4">
                <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-3 text-[11px] text-amber-900 leading-relaxed">
                    <span class="font-bold">ℹ️ Info WhatsApp:</span> Nomor format <em>LID</em> muncul saat staf berinteraksi di grup via WhatsApp Web (nomor asli disembunyikan WhatsApp). Masukkan nomor HP aktif staf agar bot dapat mengirimkan reminder secara japri.
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama PIC Sales</label>
                    <input type="text" id="fu12ModalPicName" readonly class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-bold text-slate-800 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Saat Ini (LID / Tidak Lengkap)</label>
                    <input type="text" id="fu12ModalOldPhone" readonly class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-mono text-slate-600 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Seluler Asli <span class="text-rose-500">*</span></label>
                    <input type="tel" id="fu12ModalNewPhone" placeholder="Contoh: 081234567890 / 6281234567890" class="w-full rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 px-3 py-2 text-xs font-mono font-bold text-slate-900 outline-hidden">
                    <span class="text-[10px] text-slate-500 mt-1 block">Nomor ini otomatis tersimpan ke riwayat Keep dan disinkronkan ke Master Karyawan.</span>
                </div>
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeFu12UpdatePhoneModal()" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition cursor-pointer">
                    Batal
                </button>
                <button type="button" id="btnSubmitFu12PhoneUpdate" onclick="submitFu12PhoneUpdate()" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <span>Simpan &amp; Jadwalkan Ulang</span>
                </button>
            </div>
        </div>
    </div>
</section>

<script>
// =========================================================================
// TAB 12 ENGINE: REKAP AUTO FOLLOW-UP H+1 FORMAT KEEP
// =========================================================================
let fu12CurrentData = null;
let fu12CurrentView = 'cards';
let fu12IsBroadcasting = false;
let fu12CancelBroadcast = false;

/**
 * Parser respon JSON yang aman dari error 'Unexpected token <'
 */
async function safeParseJsonResponse(res) {
    const text = await res.text();
    try {
        return JSON.parse(text);
    } catch (e) {
        if (res.status === 419) {
            throw new Error('Sesi login telah kedaluwarsa (CSRF 419). Silakan muat ulang (refresh) halaman lalu coba lagi.');
        } else if (res.status === 404) {
            throw new Error('Endpoint API tidak ditemukan (404). Pastikan deployment cPanel telah diekstrak dengan lengkap.');
        } else if (res.status === 504 || res.status === 502) {
            throw new Error('Gateway/Server Timeout (' + res.status + '). Server membutuhkan waktu terlalu lama.');
        } else {
            const clean = text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 150);
            throw new Error(`Respon server (HTTP ${res.status}): ${clean || 'Format bukan JSON'}`);
        }
    }
}

const FU12_DEFAULTS = {
    enabled: true,
    work_start: '08:00',
    work_end: '22:00',
    time: '09:00',
    delay_min: 3,
    delay_max: 8,
    batch_size: 10,
    batch_cooldown: 30,
    template_single_alert: "⏰ *REMINDER 24 JAM KEEP SOSMED*\nHalo Kak *{pic_name}*! 👋\n\nCustomer berikut sudah 24 jam sejak keep tercatat (*{jam_masuk}*) dan masih berstatus *ON PROGRESS*:\n\n👤 *{customer_name}* ({customer_phone})\n📦 Barang: {barang_keep}\n🏢 Cabang: {cabang}\n🏷️ Admin Sosmed: {kode_sosmed}\n📝 Catatan: {catatan}\n\n👉 *Langsung Chat Customer:* {chat_link}\n\nYuk follow-up sekarang dan kabari update-nya di dashboard! Semangat! 🚀💪",
    header: "☀️ *REKAP AUTO FOLLOW-UP H+1 KEEP SOSMED*\nHalo Kak *{pic_name}*! 👋\n\nBerikut daftar *{total_items} Customer Keep* kamu kemarin (*{target_date}*) yang masih berstatus *ON PROGRESS* dan perlu kamu follow-up pagi ini:\n-----------------------------------------",
    item: "{num}. *{customer_name}* ({customer_phone})\n   📦 Barang: {barang_keep}\n   🏢 Cabang: {cabang}\n   🏷️ Admin Sosmed: {kode_sosmed}\n   📝 Catatan: {catatan}\n   👉 *Klik Chat Customer:* {chat_link}",
    footer: "-----------------------------------------\nYuk follow-up sekarang dan giring jadi *WIN* hari ini! Jangan lupa update statusnya di Dashboard ya! Semangat! 🚀💪",
    greeting: "Halo Kak {customer_name}, kami dari Gadgetmart ingin menanyakan perihal keep barang {barang_keep} kemarin, apakah jadi diambil hari ini? Terima kasih 🙏"
};

function renderTab12AutoFollowUpH1() {
    if (!fu12CurrentData) {
        // Init default date to Yesterday
        const dateInput = document.getElementById('fu12TargetDateInput');
        if (dateInput && !dateInput.value) {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            dateInput.value = yesterday.toISOString().split('T')[0];
        }
        loadAutoFuH1Data();
    }
}

function onAutoFuDateChange() {
    loadAutoFuH1Data();
}

function switchFu12View(mode) {
    fu12CurrentView = mode;
    const btnCards = document.getElementById('fu12ViewBtnCards');
    const btnTable = document.getElementById('fu12ViewBtnTable');
    const btnHistory = document.getElementById('fu12ViewBtnHistory');
    const btnSettings = document.getElementById('fu12ViewBtnSettings');
    const viewCards = document.getElementById('fu12ViewCards');
    const viewTable = document.getElementById('fu12ViewTable');
    const viewHistory = document.getElementById('fu12ViewHistory');
    const viewSettings = document.getElementById('fu12ViewSettings');
    const filterSection = document.getElementById('fu12FilterSection');
    const kpiSection = document.getElementById('fu12KpiSection');

    const activeClasses = ['bg-white', 'text-slate-900', 'shadow-xs', 'border', 'border-slate-200/80'];
    const inactiveClasses = ['text-slate-600', 'hover:text-slate-900', 'hover:bg-white/60'];

    [btnCards, btnTable, btnHistory, btnSettings].forEach(b => {
        if (b) {
            b.classList.remove(...activeClasses);
            b.classList.add(...inactiveClasses);
        }
    });

    if (viewCards) viewCards.style.display = 'none';
    if (viewTable) viewTable.style.display = 'none';
    if (viewHistory) viewHistory.style.display = 'none';
    if (viewSettings) viewSettings.style.display = 'none';

    // Badge KPI konsisten selalu tampil di semua tab
    if (kpiSection) kpiSection.style.display = 'grid';

    if (mode === 'cards') {
        if (viewCards) viewCards.style.display = 'block';
        if (btnCards) {
            btnCards.classList.add(...activeClasses);
            btnCards.classList.remove(...inactiveClasses);
        }
        if (filterSection) filterSection.style.display = 'block';
    } else if (mode === 'table') {
        if (viewTable) viewTable.style.display = 'block';
        if (btnTable) {
            btnTable.classList.add(...activeClasses);
            btnTable.classList.remove(...inactiveClasses);
        }
        if (filterSection) filterSection.style.display = 'block';
    } else if (mode === 'history') {
        if (viewHistory) viewHistory.style.display = 'block';
        if (btnHistory) {
            btnHistory.classList.add(...activeClasses);
            btnHistory.classList.remove(...inactiveClasses);
        }
        if (filterSection) filterSection.style.display = 'none';
        renderFu12HistoryTable();
    } else if (mode === 'settings') {
        if (viewSettings) viewSettings.style.display = 'block';
        if (btnSettings) {
            btnSettings.classList.add(...activeClasses);
            btnSettings.classList.remove(...inactiveClasses);
        }
        if (filterSection) filterSection.style.display = 'none';
        initFu12SettingsForm();
    }
}

async function loadAutoFuH1Data() {
    const dateInput = document.getElementById('fu12TargetDateInput');
    const dateVal = dateInput ? dateInput.value : '';

    const cardsContainer = document.getElementById('fu12CardsContainer');
    if (cardsContainer) {
        cardsContainer.innerHTML = `
            <tr>
                <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                    <div class="inline-flex items-center gap-2">
                        <svg class="animate-spin size-4 text-brand-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span>Mengambil data Auto Follow-Up H+1...</span>
                    </div>
                </td>
            </tr>
        `;
    }

    try {
        const url = `/api/auto-fu-h1?date=${encodeURIComponent(dateVal)}`;
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });
        const json = await safeParseJsonResponse(res);

        if (json.success && json.data) {
            fu12CurrentData = json.data;
            renderFu12UI(json.data);
            initFu12SettingsForm();
        } else {
            if (cardsContainer) {
                cardsContainer.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-rose-500 font-bold text-xs">${json.message || 'Gagal memuat data.'}</td></tr>`;
            }
        }
    } catch (err) {
        console.error('Error loadAutoFuH1Data:', err);
        if (cardsContainer) {
            cardsContainer.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-rose-500 font-bold text-xs">Terjadi kesalahan koneksi saat memuat data: ${err.message}</td></tr>`;
        }
    }
}

function renderFu12UI(data) {
    if (!data) return;

    // 1. Update Date Badge
    const dateBadge = document.getElementById('fu12TargetDateBadge');
    if (dateBadge) {
        dateBadge.innerText = data.target_date_formatted || data.target_date;
    }

    // 2. Update Baileys Status Pill
    const botPill = document.getElementById('fu12BaileysStatusPill');
    const botText = document.getElementById('fu12BaileysStatusText');
    if (botPill && botText) {
        if (data.baileys_online) {
            botPill.className = 'inline-flex items-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200 px-3.5 py-2 text-xs font-bold text-emerald-700';
            botText.innerHTML = '<span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span><span>Bot Baileys (+62 851-1142-6699) Online</span>';
        } else {
            botPill.className = 'inline-flex items-center gap-2 rounded-xl bg-amber-50 border border-amber-200 px-3.5 py-2 text-xs font-bold text-amber-700';
            botText.innerHTML = '<span class="size-2 rounded-full bg-amber-500"></span><span>Bot Baileys (+62 851-1142-6699) Belum Konek</span>';
        }
    }

    // Hindari autofill browser (email superadmin) pada search bar
    const searchEl = document.getElementById('fu12SearchInput');
    if (searchEl && searchEl.value.includes('@')) {
        searchEl.value = '';
    }

    // 3. Update KPI Metrics
    const s = data.summary || {};
    const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };
    setTxt('fu12KpiTotalKeeps', s.total_keeps || 0);
    setTxt('fu12KpiTotalPics', s.total_pics || 0);
    setTxt('fu12KpiUnassigned', s.total_unassigned || 0);
    setTxt('fu12KpiSentPics', s.total_sent_pics || 0);
    setTxt('fu12KpiTotalPicsTarget', s.total_pics || 0);
    setTxt('fu12KpiPendingPics', s.total_pending_pics || 0);
    setTxt('fu12KpiTotalWin', s.total_win || 0);
    setTxt('fu12KpiOnProgress', s.total_on_progress || 0);

    const winRate = s.total_keeps > 0 ? Math.round((s.total_win / s.total_keeps) * 100) : 0;
    const winRateBadge = document.getElementById('fu12KpiWinRateBadge');
    if (winRateBadge) winRateBadge.innerText = `${winRate}%`;

    // 4. Render Cards & Table
    sanitizeFu12Autofill();
    applyAutoFuFilter();
}

function toggleFu12ClearBtn() {
    const inp = document.getElementById('fu12SearchInput');
    const btn = document.getElementById('btnFu12ClearSearch');
    if (!btn) return;
    if (inp && inp.value.trim().length > 0) {
        btn.classList.remove('hidden');
        btn.classList.add('flex');
    } else {
        btn.classList.remove('flex');
        btn.classList.add('hidden');
    }
}

function clearFu12Search() {
    const inp = document.getElementById('fu12SearchInput');
    if (inp) {
        inp.value = '';
        toggleFu12ClearBtn();
        applyAutoFuFilter();
    }
}

function sanitizeFu12Autofill() {
    const inp = document.getElementById('fu12SearchInput');
    if (!inp) return;
    const val = (inp.value || '').trim();
    if (val.includes('@') || val.toLowerCase().includes('admin') || val.toLowerCase().includes('.com')) {
        inp.value = '';
        toggleFu12ClearBtn();
        applyAutoFuFilter();
    }
}

function applyAutoFuFilter() {
    if (!fu12CurrentData) return;

    const query = (document.getElementById('fu12SearchInput')?.value || '').toLowerCase().trim();
    const statusFilter = document.getElementById('fu12StatusFilter')?.value || 'ALL';
    const waFilter = document.getElementById('fu12WaStatusFilter')?.value || 'ALL';

    const groups = fu12CurrentData.pic_groups || [];
    const allKeeps = fu12CurrentData.all_keeps || [];

    // Filter Groups
    const filteredGroups = groups.filter(g => {
        // WA Status Filter
        if (waFilter !== 'ALL' && g.wa_status !== waFilter) return false;

        // Query check on PIC name / phone
        const matchesPic = (g.pic_name || '').toLowerCase().includes(query) || (g.pic_phone || '').includes(query);

        // Or query check on item
        const hasMatchingItem = g.items.some(it => {
            const matchText = `${it.nama} ${it.nomor_wa} ${it.barang_keep} ${it.cabang}`.toLowerCase();
            return matchText.includes(query);
        });

        if (query && !matchesPic && !hasMatchingItem) return false;

        // Status Filter on items
        if (statusFilter === 'ON_PROGRESS' && g.total_on_progress === 0) return false;
        if (statusFilter === 'WIN' && g.total_win === 0) return false;

        return true;
    });

    renderFu12Cards(filteredGroups);

    // Filter All Keeps for Table View
    const filteredAllKeeps = allKeeps.filter(it => {
        const matchText = `${it.nama} ${it.nomor_wa} ${it.barang_keep} ${it.cabang} ${it.nama_tim_follow_up}`.toLowerCase();
        if (query && !matchText.includes(query)) return false;

        const isWin = (it.status || '').toUpperCase() === 'WIN';
        const isLose = (it.status || '').toUpperCase() === 'LOSE';
        if (statusFilter === 'WIN' && !isWin) return false;
        if (statusFilter === 'LOSE' && !isLose) return false;
        if (statusFilter === 'ON_PROGRESS' && (isWin || isLose)) return false;

        if (waFilter === 'SENT' && it.fu_h1_status !== 'SENT') return false;
        if (waFilter === 'PENDING' && it.fu_h1_status === 'SENT') return false;

        return true;
    });

    renderFu12Table(filteredAllKeeps);

    if (fu12CurrentView === 'history') {
        renderFu12HistoryTable();
    }
}

function renderFu12Cards(groups) {
    const container = document.getElementById('fu12CardsContainer');
    if (!container) return;

    const countBadge = document.getElementById('fu12PicCountBadge');
    if (countBadge) countBadge.innerText = `${groups.length} PIC`;

    if (groups.length === 0) {
        container.innerHTML = `
            <tr>
                <td colspan="8" class="py-12 text-center text-slate-400 bg-slate-50/40">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <span class="text-3xl">📭</span>
                        <h4 class="text-xs font-bold text-slate-700">Tidak ada data rekap PIC</h4>
                        <p class="text-[11px] text-slate-400">Tidak ditemukan PIC atau customer keep yang cocok dengan filter yang dipilih.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    const html = groups.map((g, idx) => {
        const picKey = 'pic_' + (g.pic_phone || 'unassigned_' + idx).replace(/[^a-zA-Z0-9_]/g, '_');
        const isSent = g.wa_status === 'SENT';

        // PIC Initial & Name
        const initial = (g.pic_name || 'FU').substring(0, 2).toUpperCase();

        // Unique Branches
        const branches = Array.from(new Set(g.items.map(it => it.cabang).filter(Boolean)));
        const cabangHtml = branches.length > 0
            ? branches.map(b => `<span class="inline-flex items-center rounded-md bg-slate-100 text-slate-700 px-2 py-0.5 text-[11px] font-semibold">${b}</span>`).join(' ')
            : '<span class="text-slate-400 text-xs">-</span>';

        // Progress Badges
        const progressBadges = [];
        if (g.total_on_progress > 0) {
            progressBadges.push(`<span class="inline-flex items-center gap-1 rounded-md bg-amber-50 border border-amber-200/80 px-2 py-0.5 text-[11px] font-bold text-amber-700"><span class="size-1.5 rounded-full bg-amber-500"></span>${g.total_on_progress} On Progress</span>`);
        }
        if (g.total_win > 0) {
            progressBadges.push(`<span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 text-[11px] font-bold text-emerald-700"><span class="size-1.5 rounded-full bg-emerald-500"></span>${g.total_win} WIN</span>`);
        }
        const totalLose = g.items.filter(it => (it.status || '').toUpperCase() === 'LOSE').length;
        if (totalLose > 0) {
            progressBadges.push(`<span class="inline-flex items-center gap-1 rounded-md bg-rose-50 border border-rose-200/80 px-2 py-0.5 text-[11px] font-bold text-rose-700">${totalLose} Lose</span>`);
        }
        const progressHtml = progressBadges.length > 0 ? progressBadges.join(' ') : '<span class="text-slate-400 text-xs">-</span>';

        // WA Status Badge
        const waStatusBadge = isSent
            ? `<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
                 <span class="size-1.5 rounded-full bg-emerald-500"></span>
                 <span>Terkirim</span>
               </span>`
            : `<span class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 px-2.5 py-1 text-[11px] font-bold text-amber-700">
                 <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                 <span>Belum</span>
               </span>`;

        // Customer Cards for Accordion Drawer
        const customerCards = g.items.map((it, num) => {
            const isWin = (it.status || '').toUpperCase() === 'WIN';
            const isLose = (it.status || '').toUpperCase() === 'LOSE';
            const statusBadge = isWin
                ? '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800">WIN</span>'
                : (isLose
                    ? '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-800">LOSE</span>'
                    : '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800">ON PROGRESS</span>');

            const normWA = it.nomor_wa_norm;
            const waGreeting = encodeURIComponent(`Halo Kak ${it.nama}, kami dari Gadgetmart ingin menanyakan perihal keep barang ${it.barang_keep} kemarin, apakah jadi diambil hari ini? Terima kasih 🙏`);
            const chatLink = normWA ? `https://wa.me/${normWA}?text=${waGreeting}` : '#';

            return `
                <div class="p-3 rounded-xl bg-white border border-slate-200/90 shadow-2xs flex flex-col justify-between gap-2 hover:border-brand-300 transition">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-slate-900 text-xs truncate">${num + 1}. ${it.nama}</span>
                            ${statusBadge}
                        </div>
                        <div class="text-[11px] font-mono text-slate-500">${it.nomor_wa || '-'}</div>
                        <div class="text-xs text-slate-800 font-medium">
                            📦 <span class="font-bold text-slate-900">${it.barang_keep}</span>
                        </div>
                        <div class="text-[11px] text-slate-500 flex items-center gap-1">
                            <span>🏢 ${it.cabang || '-'}</span>
                        </div>
                        ${it.catatan && it.catatan !== '-' ? `<div class="text-[10px] text-slate-600 bg-slate-50 p-1.5 rounded-lg border border-slate-100 italic mt-1 leading-snug">📝 "${it.catatan}"</div>` : ''}
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] text-slate-400 font-mono">${it.timestamp || ''}</span>
                        ${normWA ? `
                        <a href="${chatLink}" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-2 py-1 text-[11px] font-bold border border-emerald-200 transition" title="Chat langsung ke WhatsApp customer">
                            <svg class="size-3 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            <span>Chat</span>
                        </a>` : '<span class="text-slate-400 text-[10px]">No WA kosong</span>'}
                    </div>
                </div>
            `;
        }).join('');

        return `
            <!-- Main Row PIC -->
            <tr class="hover:bg-slate-50/70 transition-colors text-xs divide-x divide-slate-100 border-b border-slate-100">
                <td class="py-3 px-3.5 text-center font-bold text-slate-400">${idx + 1}</td>
                <td class="py-3 px-4">
                    <div class="flex items-center gap-3">
                        <div class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 font-black text-white text-xs shadow-2xs shrink-0">
                            ${initial}
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-black text-slate-900 text-xs truncate">${g.pic_name}</h4>
                            <div class="text-[11px] text-slate-500 font-mono">📱 ${g.pic_display_phone || '-'}</div>
                        </div>
                    </div>
                </td>
                <td class="py-3 px-3.5">
                    <div class="flex flex-wrap gap-1">
                        ${cabangHtml}
                    </div>
                </td>
                <td class="py-3 px-3.5 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-800 border border-slate-200/60">
                        ${g.total_items} Keep
                    </span>
                </td>
                <td class="py-3 px-3.5">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        ${progressHtml}
                    </div>
                </td>
                <td class="py-3 px-3.5 text-center">
                    ${waStatusBadge}
                </td>
                <td class="py-3 px-3.5 text-center">
                    <button type="button" onclick="toggleFu12Drawer('${picKey}')" id="fu12BtnDrawer_${picKey}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100/80 px-2.5 py-1.5 text-xs font-bold text-slate-700 transition shadow-2xs cursor-pointer group">
                        <span>Rincian (${g.total_items})</span>
                        <svg id="fu12Arrow_${picKey}" class="size-3 text-slate-400 transition-transform duration-200 group-hover:text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                </td>
                <td class="py-3 px-4 text-center">
                    <button type="button" onclick="sendSinglePicDigest('${g.pic_phone}', '${g.pic_name}')" class="inline-flex items-center gap-1.5 rounded-xl ${isSent ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200' : 'bg-slate-900 hover:bg-slate-800 text-white shadow-2xs'} px-3 py-1.5 text-xs font-bold transition cursor-pointer">
                        <svg class="size-3.5 ${isSent ? 'text-slate-500' : 'text-emerald-400'}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        <span>${isSent ? 'Kirim Ulang WA' : 'Kirim Rekap WA'}</span>
                    </button>
                </td>
            </tr>

            <!-- Accordion Drawer Row -->
            <tr id="fu12Drawer_${picKey}" class="fu12-drawer-row bg-slate-50/70 border-b border-slate-200/80" style="display:none;">
                <td colspan="8" class="p-4 bg-slate-50/80">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-800">Daftar Customer Keep ${g.pic_name}:</span>
                                <span class="rounded-full bg-slate-200/90 text-slate-700 px-2 py-0.5 text-[10px] font-bold">${g.total_items} Customer</span>
                            </div>
                            <button type="button" onclick="sendSinglePicDigest('${g.pic_phone}', '${g.pic_name}')" class="text-xs font-bold text-teal-700 hover:text-teal-800 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                <span>Kirim Rekap WA ke ${g.pic_name} (${g.pic_display_phone || '-'})</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
                            ${customerCards}
                        </div>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    container.innerHTML = html;
}

function toggleFu12Drawer(picKey) {
    const drawer = document.getElementById(`fu12Drawer_${picKey}`);
    const arrow = document.getElementById(`fu12Arrow_${picKey}`);
    if (!drawer) return;
    const isHidden = drawer.style.display === 'none' || drawer.classList.contains('hidden');
    if (isHidden) {
        drawer.style.display = '';
        drawer.classList.remove('hidden');
        if (arrow) arrow.style.transform = 'rotate(180deg)';
    } else {
        drawer.style.display = 'none';
        drawer.classList.add('hidden');
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    }
}

function toggleAllFu12Drawers(open) {
    const drawers = document.querySelectorAll('.fu12-drawer-row');
    drawers.forEach(drawer => {
        const key = drawer.id.replace('fu12Drawer_', '');
        const arrow = document.getElementById(`fu12Arrow_${key}`);
        if (open) {
            drawer.style.display = '';
            drawer.classList.remove('hidden');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            drawer.style.display = 'none';
            drawer.classList.add('hidden');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    });
}

function renderFu12Table(items) {
    const tbody = document.getElementById('fu12AllItemsTableBody');
    if (!tbody) return;

    if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-slate-400">Tidak ada rincian customer keep.</td></tr>`;
        return;
    }

    const html = items.map((it, idx) => {
        const isWin = (it.status || '').toUpperCase() === 'WIN';
        const isLose = (it.status || '').toUpperCase() === 'LOSE';
        const statusBadge = isWin
            ? '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-100 text-emerald-800">WIN</span>'
            : (isLose
                ? '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-rose-100 text-rose-800">LOSE</span>'
                : '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-800">ON PROGRESS</span>');

        const waStatusBadge = it.fu_h1_status === 'SENT'
            ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">✅ Terkirim (${it.fu_h1_sent_at ? it.fu_h1_sent_at.split(' ')[1] : ''})</span>`
            : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500">⏳ Belum</span>`;

        const normWA = it.nomor_wa_norm;
        const waGreeting = encodeURIComponent(`Halo Kak ${it.nama}, kami dari Gadgetmart ingin menanyakan perihal keep barang ${it.barang_keep} kemarin, apakah jadi diambil hari ini? Terima kasih 🙏`);
        const chatLink = normWA ? `https://wa.me/${normWA}?text=${waGreeting}` : '#';

        return `
            <tr class="hover:bg-slate-50/70 transition-colors text-xs divide-x divide-slate-100">
                <td class="py-2.5 px-3.5 text-center font-bold text-slate-400">${idx + 1}</td>
                <td class="py-2.5 px-3.5 font-mono text-slate-500 text-[11px]">${it.timestamp}</td>
                <td class="py-2.5 px-3.5 font-bold text-slate-900">
                    <div>${it.nama}</div>
                    <div class="text-[11px] text-slate-400 font-mono font-normal">${it.nomor_wa}</div>
                </td>
                <td class="py-2.5 px-3.5 font-semibold text-slate-800">
                    <div>${it.barang_keep}</div>
                    ${it.catatan && it.catatan !== '-' ? `<div class="text-[10px] text-slate-500 italic">"${it.catatan}"</div>` : ''}
                </td>
                <td class="py-2.5 px-3.5 text-slate-600">${it.cabang}</td>
                <td class="py-2.5 px-3.5 font-bold text-slate-900">
                    <div>${it.nama_tim_follow_up}</div>
                    <div class="text-[11px] text-slate-500 font-mono font-normal">${it.nomor_fu || '-'}</div>
                </td>
                <td class="py-2.5 px-3.5 text-center">${statusBadge}</td>
                <td class="py-2.5 px-3.5 text-center">${waStatusBadge}</td>
                <td class="py-2.5 px-3.5 text-center">
                    ${normWA ? `
                    <a href="${chatLink}" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-2.5 py-1 text-[11px] font-bold border border-emerald-200 transition" title="Chat Customer">
                        💬 WA
                    </a>` : '-'}
                </td>
            </tr>
        `;
    }).join('');

    tbody.innerHTML = html;
}

// =========================================================================
// TAB 4 ENGINE: RIWAYAT & STATUS PENGIRIMAN DETAIL
// =========================================================================
function renderFu12HistoryTable() {
    if (!fu12CurrentData || !Array.isArray(fu12CurrentData.pic_groups)) return;

    const tbody = document.getElementById('fu12HistoryTableBody');
    if (!tbody) return;

    const searchInput = (document.getElementById('fu12HistSearchInput')?.value || '').toLowerCase().trim();
    const statusFilter = document.getElementById('fu12HistStatusFilter')?.value || 'ALL';

    const groups = fu12CurrentData.pic_groups;

    // Hitung statistik keseluruhan untuk mini cards
    const totalTarget = groups.length;
    const totalSent = groups.filter(g => g.wa_status === 'SENT').length;
    const totalFailed = groups.filter(g => g.wa_status === 'FAILED' || !g.is_valid_phone).length;
    const totalPending = groups.filter(g => g.wa_status !== 'SENT' && g.wa_status !== 'FAILED' && g.is_valid_phone).length;

    const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };
    setTxt('fu12HistStatTotal', totalTarget);
    setTxt('fu12HistStatSent', totalSent);
    setTxt('fu12HistStatFailed', totalFailed);
    setTxt('fu12HistStatPending', totalPending);

    // Filter daftar riwayat
    const filtered = groups.filter(g => {
        const isSent = g.wa_status === 'SENT';
        const isFailed = g.wa_status === 'FAILED';
        const isInvalid = !g.is_valid_phone;
        const isPending = !isSent && !isFailed && !isInvalid;

        if (statusFilter === 'SENT' && !isSent) return false;
        if (statusFilter === 'FAILED' && !isFailed) return false;
        if (statusFilter === 'INVALID' && !isInvalid) return false;
        if (statusFilter === 'PENDING' && !isPending) return false;

        if (searchInput) {
            const txt = `${g.pic_name || ''} ${g.pic_phone || ''} ${g.raw_pic_phone || ''} ${g.cabang || ''} ${g.wa_note || ''}`.toLowerCase();
            if (!txt.includes(searchInput)) return false;
        }

        return true;
    });

    const countBadge = document.getElementById('fu12HistCountBadge');
    if (countBadge) countBadge.innerText = `${filtered.length} Data`;

    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="py-10 text-center text-slate-400 bg-slate-50/40">
                    <div class="flex flex-col items-center justify-center gap-1.5">
                        <span class="text-2xl">📭</span>
                        <span class="text-xs font-bold text-slate-700">Tidak ada data riwayat pengiriman</span>
                        <span class="text-[11px] text-slate-400">Tidak ditemukan PIC yang cocok dengan filter yang dipilih.</span>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    const html = filtered.map((g, idx) => {
        const isSent = g.wa_status === 'SENT';
        const isFailed = g.wa_status === 'FAILED';
        const isInvalid = !g.is_valid_phone;

        // Waktu Kirim
        const waktuKirim = g.wa_sent_at
            ? `<div class="font-bold text-slate-800 text-[11px]">${g.wa_sent_at}</div><div class="text-[10px] text-emerald-600 font-mono">✓✓ Berhasil Terkirim</div>`
            : `<span class="text-slate-400 text-xs italic font-mono">-</span>`;

        // Nomor Phone Badge
        let phoneBadge = '';
        if (g.is_valid_phone) {
            phoneBadge = `<span class="text-[11px] font-mono text-slate-700 font-bold">📱 ${g.pic_display_phone || g.pic_phone}</span>`;
        } else if (g.is_lid) {
            phoneBadge = `
                <div>
                    <span class="text-[11px] font-mono text-amber-700 font-bold block">⚠️ ${g.raw_pic_phone || '-'}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-200">Format LID WA</span>
                </div>
            `;
        } else {
            phoneBadge = `
                <div>
                    <span class="text-[11px] font-mono text-rose-700 font-bold block">⚠️ ${g.raw_pic_phone || 'Nomor Kosong'}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-rose-100 text-rose-800 border border-rose-200">Tidak Valid</span>
                </div>
            `;
        }

        // Status Badge Pill
        let statusBadge = '';
        if (isSent) {
            statusBadge = `
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-1 text-[11px] font-black text-emerald-700">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                    <span>Terkirim</span>
                </span>
            `;
        } else if (isInvalid) {
            statusBadge = `
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 px-2.5 py-1 text-[11px] font-black text-amber-800">
                    <span class="size-1.5 rounded-full bg-amber-500"></span>
                    <span>Nomor Tidak Valid</span>
                </span>
            `;
        } else if (isFailed) {
            statusBadge = `
                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 border border-rose-200 px-2.5 py-1 text-[11px] font-black text-rose-700">
                    <span class="size-1.5 rounded-full bg-rose-500"></span>
                    <span>Gagal Kirim</span>
                </span>
            `;
        } else if (g.total_on_progress === 0) {
            statusBadge = `
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 border border-slate-200 px-2.5 py-1 text-[11px] font-black text-slate-600">
                    <span>Dilewati (0 On Progress)</span>
                </span>
            `;
        } else {
            statusBadge = `
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 border border-slate-200 px-2.5 py-1 text-[11px] font-black text-slate-600">
                    <span class="size-1.5 rounded-full bg-slate-400"></span>
                    <span>Menunggu Antrean</span>
                </span>
            `;
        }

        // Diagnosa / Keterangan Penyebab Kegagalan
        let keteranganHtml = '';
        if (isSent) {
            keteranganHtml = `
                <div class="text-xs text-emerald-700 flex items-start gap-1.5">
                    <span class="text-sm shrink-0">✅</span>
                    <div>
                        <div class="font-bold">Sukses Terkirim</div>
                        <div class="text-[11px] text-emerald-600 leading-snug">${g.wa_note || 'Pesan rekap H+1 berhasil masuk ke WhatsApp PIC via Baileys Bot (+62 851-1142-6699)'}</div>
                    </div>
                </div>
            `;
        } else if (isInvalid) {
            keteranganHtml = `
                <div class="text-xs text-amber-800 flex items-start gap-1.5 bg-amber-50/70 p-2 rounded-xl border border-amber-200/70">
                    <span class="text-sm shrink-0">⚠️</span>
                    <div>
                        <div class="font-bold">Kegagalan: Nomor Tidak Valid</div>
                        <div class="text-[11px] text-amber-700 leading-snug">${g.wa_note || (g.is_lid ? 'Terdeteksi nomor format LID sistem WhatsApp Multi-Device. Nomor ini bukan nomor HP seluler.' : 'Nomor tidak terdaftar atau format digit kurang lengkap.')}</div>
                    </div>
                </div>
            `;
        } else if (isFailed) {
            keteranganHtml = `
                <div class="text-xs text-rose-800 flex items-start gap-1.5 bg-rose-50/70 p-2 rounded-xl border border-rose-200/70">
                    <span class="text-sm shrink-0">❌</span>
                    <div>
                        <div class="font-bold">Kegagalan: Error Gateway / Sistem</div>
                        <div class="text-[11px] text-rose-700 leading-snug">${g.wa_note || 'Gagal mengirim pesan melalui Baileys Bot. Silakan periksa koneksi bot atau coba kirim ulang.'}</div>
                    </div>
                </div>
            `;
        } else if (g.total_on_progress === 0) {
            keteranganHtml = `
                <div class="text-xs text-slate-600 flex items-start gap-1.5">
                    <span class="text-sm shrink-0">ℹ️</span>
                    <div>
                        <div class="font-bold text-slate-700">Dilewati Otomatis</div>
                        <div class="text-[11px] text-slate-500 leading-snug">Semua transaksi customer keep PIC ini sudah closing WIN atau LOSE (tidak ada keep On Progress yang perlu diingatkan).</div>
                    </div>
                </div>
            `;
        } else {
            keteranganHtml = `
                <div class="text-xs text-slate-600 flex items-start gap-1.5">
                    <span class="text-sm shrink-0">⏳</span>
                    <div>
                        <div class="font-bold text-slate-700">Menunggu Pengiriman</div>
                        <div class="text-[11px] text-slate-500 leading-snug">${g.wa_note || 'PIC siap menerima rekap follow-up H+1.'}</div>
                    </div>
                </div>
            `;
        }

        // Aksi Kirim Ulang
        let aksiHtml = '';
        if (g.is_valid_phone && g.total_on_progress > 0) {
            aksiHtml = `
                <button type="button" onclick="sendSinglePicDigest('${g.pic_phone}', '${g.pic_name}')" class="inline-flex items-center gap-1.5 rounded-xl ${isSent ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200' : 'bg-teal-600 hover:bg-teal-700 text-white shadow-2xs'} px-2.5 py-1.5 text-xs font-bold transition cursor-pointer" title="Kirim Ulang Reminder WhatsApp">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    <span>${isSent ? 'Kirim Ulang' : 'Kirim'}</span>
                </button>
            `;
        } else if (!g.is_valid_phone) {
            const safePicName = (g.pic_name || '').replace(/'/g, "\\'");
            const safeOldPhone = (g.raw_pic_phone || g.pic_phone || '').replace(/'/g, "\\'");
            aksiHtml = `
                <button type="button" onclick="openUpdatePicPhoneModal('${safePicName}', '${safeOldPhone}')" class="inline-flex items-center gap-1 rounded-xl bg-amber-500 hover:bg-amber-600 text-white px-2.5 py-1.5 text-xs font-bold shadow-2xs transition cursor-pointer" title="Koreksi Nomor WhatsApp PIC (Ubah dari LID ke nomor HP)">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                    <span>Koreksi No</span>
                </button>
            `;
        } else {
            aksiHtml = `
                <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-400">
                    Selesai
                </span>
            `;
        }

        return `
            <tr class="hover:bg-slate-50/70 transition-colors text-xs divide-x divide-slate-100 border-b border-slate-100">
                <td class="py-3 px-3.5 text-center font-bold text-slate-400">${idx + 1}</td>
                <td class="py-3 px-3.5">${waktuKirim}</td>
                <td class="py-3 px-4">
                    <div class="font-black text-slate-900">${g.pic_name}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">🏢 Cabang: <span class="font-bold text-slate-700">${g.cabang || '-'}</span></div>
                </td>
                <td class="py-3 px-3.5">${phoneBadge}</td>
                <td class="py-3 px-3.5 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                        ${g.total_items} (${g.total_on_progress} On Progress)
                    </span>
                </td>
                <td class="py-3 px-3.5 text-center">${statusBadge}</td>
                <td class="py-3 px-4">${keteranganHtml}</td>
                <td class="py-3 px-3.5 text-center">${aksiHtml}</td>
            </tr>
        `;
    }).join('');

    tbody.innerHTML = html;
}

function closeFu12ProgressModal() {
    const modal = document.getElementById('fu12ProgressModal');
    if (modal) modal.style.display = 'none';
}

function closeFu12ModalAndOpenHistory() {
    closeFu12ProgressModal();
    switchFu12View('history');
}

// =========================================================================
// PENGATURAN JADWAL & TEMPLATE LIVE ENGINE
// =========================================================================
function initFu12SettingsForm() {
    const s = (fu12CurrentData && fu12CurrentData.settings) ? fu12CurrentData.settings : FU12_DEFAULTS;

    const elEnabled = document.getElementById('fu12SettingEnabled');
    if (elEnabled) elEnabled.checked = (s.enabled !== false && s.enabled !== '0');

    const elWorkStart = document.getElementById('fu12SettingWorkStart');
    if (elWorkStart) elWorkStart.value = s.work_start || FU12_DEFAULTS.work_start;

    const elWorkEnd = document.getElementById('fu12SettingWorkEnd');
    if (elWorkEnd) elWorkEnd.value = s.work_end || FU12_DEFAULTS.work_end;

    const elTime = document.getElementById('fu12SettingTime');
    if (elTime) elTime.value = s.time || FU12_DEFAULTS.time;

    const elDelayMin = document.getElementById('fu12DelayMin');
    if (elDelayMin) elDelayMin.value = (s.delay_min !== undefined) ? s.delay_min : FU12_DEFAULTS.delay_min;

    const elDelayMax = document.getElementById('fu12DelayMax');
    if (elDelayMax) elDelayMax.value = (s.delay_max !== undefined) ? s.delay_max : FU12_DEFAULTS.delay_max;

    const elBatchSize = document.getElementById('fu12BatchSize');
    if (elBatchSize) elBatchSize.value = (s.batch_size !== undefined) ? s.batch_size : FU12_DEFAULTS.batch_size;

    const elBatchCooldown = document.getElementById('fu12BatchCooldown');
    if (elBatchCooldown) elBatchCooldown.value = (s.batch_cooldown !== undefined) ? s.batch_cooldown : FU12_DEFAULTS.batch_cooldown;

    const elSingle = document.getElementById('fu12TplSingleAlert');
    if (elSingle) elSingle.value = s.template_single_alert || FU12_DEFAULTS.template_single_alert;

    const elHeader = document.getElementById('fu12TplHeader');
    if (elHeader) elHeader.value = s.template_header || FU12_DEFAULTS.header;

    const elItem = document.getElementById('fu12TplItem');
    if (elItem) elItem.value = s.template_item || FU12_DEFAULTS.item;

    const elFooter = document.getElementById('fu12TplFooter');
    if (elFooter) elFooter.value = s.template_footer || FU12_DEFAULTS.footer;

    const elGreeting = document.getElementById('fu12GreetingCustomer');
    if (elGreeting) elGreeting.value = s.greeting_customer || FU12_DEFAULTS.greeting;

    onFu12SettingChange();
    updateFu12LivePreview();
}

function onFu12SettingChange() {
    const isEnabled = document.getElementById('fu12SettingEnabled')?.checked;
    const workStart = document.getElementById('fu12SettingWorkStart')?.value || '08:00';
    const workEnd = document.getElementById('fu12SettingWorkEnd')?.value || '22:00';
    const statusBadge = document.getElementById('fu12ScheduleStatusBadge');

    if (statusBadge) {
        if (isEnabled) {
            statusBadge.className = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
            statusBadge.innerHTML = `<span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span><span>Aktif (${workStart} - ${workEnd} WITA)</span>`;
        } else {
            statusBadge.className = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200';
            statusBadge.innerHTML = `<span class="size-2 rounded-full bg-slate-400"></span><span>Nonaktif</span>`;
        }
    }
}

function insertFu12Tag(elementId, tag) {
    const el = document.getElementById(elementId);
    if (!el) return;

    const start = el.selectionStart || 0;
    const end = el.selectionEnd || 0;
    const text = el.value;
    el.value = text.substring(0, start) + tag + text.substring(end);
    el.selectionStart = el.selectionEnd = start + tag.length;
    el.focus();
    updateFu12LivePreview();
}

function resetFu12TemplateDefault() {
    if (!confirm('Kembalikan konfigurasi jam kerja, jeda pengiriman, template pesan 24 jam presisi, dan rekap pagi ke format bawaan?')) return;

    if (document.getElementById('fu12SettingWorkStart')) document.getElementById('fu12SettingWorkStart').value = FU12_DEFAULTS.work_start;
    if (document.getElementById('fu12SettingWorkEnd')) document.getElementById('fu12SettingWorkEnd').value = FU12_DEFAULTS.work_end;
    if (document.getElementById('fu12DelayMin')) document.getElementById('fu12DelayMin').value = FU12_DEFAULTS.delay_min;
    if (document.getElementById('fu12DelayMax')) document.getElementById('fu12DelayMax').value = FU12_DEFAULTS.delay_max;
    if (document.getElementById('fu12BatchSize')) document.getElementById('fu12BatchSize').value = FU12_DEFAULTS.batch_size;
    if (document.getElementById('fu12BatchCooldown')) document.getElementById('fu12BatchCooldown').value = FU12_DEFAULTS.batch_cooldown;

    if (document.getElementById('fu12TplSingleAlert')) document.getElementById('fu12TplSingleAlert').value = FU12_DEFAULTS.template_single_alert;
    document.getElementById('fu12TplHeader').value = FU12_DEFAULTS.header;
    document.getElementById('fu12TplItem').value = FU12_DEFAULTS.item;
    document.getElementById('fu12TplFooter').value = FU12_DEFAULTS.footer;
    document.getElementById('fu12GreetingCustomer').value = FU12_DEFAULTS.greeting;

    onFu12SettingChange();
    updateFu12LivePreview();
}

let fu12PreviewMode = 'single'; // 'single' or 'digest'

function setFu12PreviewMode(mode) {
    fu12PreviewMode = mode;
    const btnSingle = document.getElementById('fu12BtnPreviewSingle');
    const btnDigest = document.getElementById('fu12BtnPreviewDigest');
    const timeBadge = document.getElementById('fu12LivePreviewTime');

    if (btnSingle && btnDigest) {
        if (mode === 'single') {
            btnSingle.className = 'px-2.5 py-0.5 rounded-md bg-white text-slate-900 shadow-2xs transition';
            btnDigest.className = 'px-2.5 py-0.5 rounded-md text-slate-600 hover:text-slate-900 transition';
            if (timeBadge) timeBadge.innerText = '09:32 WITA ✓✓';
        } else {
            btnDigest.className = 'px-2.5 py-0.5 rounded-md bg-white text-slate-900 shadow-2xs transition';
            btnSingle.className = 'px-2.5 py-0.5 rounded-md text-slate-600 hover:text-slate-900 transition';
            if (timeBadge) timeBadge.innerText = '08:00 WITA ✓✓';
        }
    }
    updateFu12LivePreview();
}

function updateFu12LivePreview() {
    const box = document.getElementById('fu12LivePreviewBox');
    if (!box) return;

    const singleTpl = document.getElementById('fu12TplSingleAlert')?.value || FU12_DEFAULTS.template_single_alert;
    const headerTpl = document.getElementById('fu12TplHeader')?.value || FU12_DEFAULTS.header;
    const itemTpl = document.getElementById('fu12TplItem')?.value || FU12_DEFAULTS.item;
    const footerTpl = document.getElementById('fu12TplFooter')?.value || FU12_DEFAULTS.footer;
    const greetingTpl = document.getElementById('fu12GreetingCustomer')?.value || FU12_DEFAULTS.greeting;

    // Dummy sample items
    const sampleItems = [
        { num: 1, name: 'Bpk. Hendra Saputra', phone: '0812-3456-7890', normWA: '6281234567890', barang: 'SAMSUNG GALAXY S25 8/256GB', cabang: 'Head Office', kode_sosmed: 'ADM-SBY-01', catatan: 'Minta keep sampai siang', jam: 'Kemarin, 09:32 WITA' },
        { num: 2, name: 'Ibu Siti Aisyah', phone: '0852-9876-5432', normWA: '6285298765432', barang: 'OPPO A60 8/128GB', cabang: 'Head Office', kode_sosmed: 'ADM-MTP-02', catatan: 'Tanya promo cicilan 0%', jam: 'Kemarin, 14:15 WITA' }
    ];

    if (fu12PreviewMode === 'single') {
        const it = sampleItems[0];
        const rawGreeting = greetingTpl
            .replace(/{customer_name}/g, it.name)
            .replace(/{barang_keep}/g, it.barang)
            .replace(/{cabang}/g, it.cabang)
            .replace(/{kode_sosmed}/g, it.kode_sosmed)
            .replace(/{kode}/g, it.kode_sosmed);
        const encodedGreeting = encodeURIComponent(rawGreeting);
        const link = `https://wa.me/${it.normWA}?text=${encodedGreeting}`;

        const renderedSingle = singleTpl
            .replace(/{pic_name}/g, 'Daniel')
            .replace(/{customer_name}/g, it.name)
            .replace(/{customer_phone}/g, it.phone)
            .replace(/{barang_keep}/g, it.barang)
            .replace(/{cabang}/g, it.cabang)
            .replace(/{kode_sosmed}/g, it.kode_sosmed)
            .replace(/{kode}/g, it.kode_sosmed)
            .replace(/{catatan}/g, it.catatan)
            .replace(/{jam_masuk}/g, it.jam)
            .replace(/{chat_link}/g, link);

        box.innerText = renderedSingle.trim();
    } else {
        // Render header
        const headerRendered = headerTpl
            .replace(/{pic_name}/g, 'Daniel')
            .replace(/{target_date}/g, '17 November 2026')
            .replace(/{total_items}/g, '2');

        // Render items
        const renderedItemsList = sampleItems.map(it => {
            const rawGreeting = greetingTpl
                .replace(/{customer_name}/g, it.name)
                .replace(/{barang_keep}/g, it.barang)
                .replace(/{cabang}/g, it.cabang)
                .replace(/{kode_sosmed}/g, it.kode_sosmed)
                .replace(/{kode}/g, it.kode_sosmed);
            const encodedGreeting = encodeURIComponent(rawGreeting);
            const link = `https://wa.me/${it.normWA}?text=${encodedGreeting}`;

            return itemTpl
                .replace(/{num}/g, it.num)
                .replace(/{customer_name}/g, it.name)
                .replace(/{customer_phone}/g, it.phone)
                .replace(/{barang_keep}/g, it.barang)
                .replace(/{cabang}/g, it.cabang)
                .replace(/{kode_sosmed}/g, it.kode_sosmed)
                .replace(/{kode}/g, it.kode_sosmed)
                .replace(/{catatan}/g, it.catatan)
                .replace(/{chat_link}/g, link);
        });

        const fullPreview = headerRendered.trim() + '\n\n' + renderedItemsList.join('\n\n') + '\n\n' + footerTpl.trim();
        box.innerText = fullPreview;
    }
}

async function saveFu12Settings(btn) {
    const isEnabled = document.getElementById('fu12SettingEnabled')?.checked;
    const workStartVal = document.getElementById('fu12SettingWorkStart')?.value || '08:00';
    const workEndVal = document.getElementById('fu12SettingWorkEnd')?.value || '22:00';
    const delayMinVal = parseInt(document.getElementById('fu12DelayMin')?.value, 10) || FU12_DEFAULTS.delay_min;
    const delayMaxVal = parseInt(document.getElementById('fu12DelayMax')?.value, 10) || FU12_DEFAULTS.delay_max;
    const batchSizeVal = parseInt(document.getElementById('fu12BatchSize')?.value, 10) || FU12_DEFAULTS.batch_size;
    const batchCooldownVal = parseInt(document.getElementById('fu12BatchCooldown')?.value, 10) || FU12_DEFAULTS.batch_cooldown;
    const singleAlertVal = document.getElementById('fu12TplSingleAlert')?.value || '';
    const headerVal = document.getElementById('fu12TplHeader')?.value || '';
    const itemVal = document.getElementById('fu12TplItem')?.value || '';
    const footerVal = document.getElementById('fu12TplFooter')?.value || '';
    const greetingVal = document.getElementById('fu12GreetingCustomer')?.value || '';

    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>Menyimpan...</span>';
    }

    try {
        const res = await fetch('/api/auto-fu-h1/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                enabled: isEnabled ? '1' : '0',
                work_start: workStartVal,
                work_end: workEndVal,
                delay_min: delayMinVal,
                delay_max: delayMaxVal,
                batch_size: batchSizeVal,
                batch_cooldown: batchCooldownVal,
                template_single_alert: singleAlertVal,
                template_header: headerVal,
                template_item: itemVal,
                template_footer: footerVal,
                greeting_customer: greetingVal
            })
        });

        const json = await safeParseJsonResponse(res);
        if (json.success) {
            alert(`✅ ${json.message}`);
            if (fu12CurrentData) {
                fu12CurrentData.settings = json.settings;
            }
            onFu12SettingChange();
        } else {
            alert(`❌ Gagal menyimpan: ${json.message || 'Unknown error'}`);
        }
    } catch (err) {
        alert(`❌ Terjadi error: ${err.message}`);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
}

async function sendSinglePicDigestDirect(picPhone, picName, dateVal) {
    const res = await fetch('/api/auto-fu-h1/send-digest', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            target: 'SINGLE',
            pic_phone: picPhone,
            pic_name: picName,
            date: dateVal
        })
    });
    return await safeParseJsonResponse(res);
}

async function sendSinglePicDigest(picPhone, picName) {
    if (!picPhone) {
        alert('Nomor WhatsApp PIC tidak valid.');
        return;
    }

    const dateVal = document.getElementById('fu12TargetDateInput')?.value || '';
    if (!confirm(`Kirim pesan WhatsApp rekap follow-up H+1 ke Kak ${picName} (${picPhone}) via Bot Baileys?`)) {
        return;
    }

    try {
        const json = await sendSinglePicDigestDirect(picPhone, picName, dateVal);
        if (json.success) {
            alert(`✅ ${json.message || 'Pesan rekap berhasil dikirim!'}`);
            loadAutoFuH1Data();
        } else {
            alert(`❌ Gagal mengirim: ${json.message || 'Unknown error'}`);
        }
    } catch (err) {
        alert(`❌ Terjadi error pengiriman: ${err.message}`);
    }
}

async function confirmSendAllH1Reminders() {
    if (fu12IsBroadcasting) {
        alert('Pengiriman broadcast sedang berjalan...');
        return;
    }

    if (!fu12CurrentData || !Array.isArray(fu12CurrentData.pic_groups)) {
        alert('Data belum dimuat. Silakan tunggu atau klik Refresh.');
        return;
    }

    const dateVal = document.getElementById('fu12TargetDateInput')?.value || fu12CurrentData.target_date || '';
    const pendingPics = fu12CurrentData.pic_groups.filter(g => {
        const isSent = (g.wa_status === 'SENT' || g.status_kirim === 'SENT');
        const onProgress = parseInt(g.total_on_progress || 0, 10);
        const hasValidPhone = g.pic_phone && g.pic_phone.length >= 9 && g.pic_phone.length <= 14 && !g.pic_phone.startsWith('78') && !g.pic_phone.startsWith('17') && !g.pic_phone.startsWith('36') && !g.pic_phone.startsWith('45') && !g.pic_phone.startsWith('32');
        return !isSent && hasValidPhone && onProgress > 0;
    });

    if (pendingPics.length === 0) {
        alert('Tidak ada antrean kirim: Seluruh PIC dengan nomor valid sudah terkirim atau semua keep berstatus WIN/LOSE.');
        return;
    }

    const s = fu12CurrentData.summary || {};
    const msg = `Apakah Anda yakin ingin mengirim pesan WhatsApp Rekap Auto Follow-Up H+1 ke ${pendingPics.length} PIC Sales?\n\nTanggal Keep: ${fu12CurrentData.target_date_formatted}\nTotal Customer: ${s.total_keeps}\n\nSetiap pesan dikirim berurutan via Baileys Bot (+62 851-1142-6699) dengan jeda aman anti-suspend.`;

    if (!confirm(msg)) {
        return;
    }

    fu12IsBroadcasting = true;
    fu12CancelBroadcast = false;

    const modal = document.getElementById('fu12ProgressModal');
    const activeContent = document.getElementById('fu12ProgressActiveContent');
    const doneContent = document.getElementById('fu12ProgressDoneContent');
    const pBar = document.getElementById('fu12ProgressBar');
    const pCounter = document.getElementById('fu12ProgressCounter');
    const pPercent = document.getElementById('fu12ProgressPercent');
    const pCurrent = document.getElementById('fu12ProgressCurrentPic');
    const pDetail = document.getElementById('fu12ProgressDetail');
    const pSummary = document.getElementById('fu12ProgressSummary');
    const btnCancel = document.getElementById('btnCancelFu12Broadcast');

    if (modal) modal.style.display = 'flex';
    if (activeContent) activeContent.style.display = 'block';
    if (doneContent) doneContent.style.display = 'none';
    if (btnCancel) {
        btnCancel.disabled = false;
        btnCancel.innerText = 'Hentikan';
    }

    const settings = fu12CurrentData.settings || {};
    const delayMin = Math.max(1, parseInt(settings.delay_min, 10) || FU12_DEFAULTS.delay_min);
    const delayMax = Math.max(delayMin, parseInt(settings.delay_max, 10) || FU12_DEFAULTS.delay_max);
    const batchSize = Math.max(2, parseInt(settings.batch_size, 10) || FU12_DEFAULTS.batch_size);
    const batchCooldown = Math.max(0, parseInt(settings.batch_cooldown, 10) || FU12_DEFAULTS.batch_cooldown);

    let sent = 0;
    let failed = 0;
    const sleep = (ms) => new Promise(r => setTimeout(r, ms));

    for (let i = 0; i < pendingPics.length; i++) {
        if (fu12CancelBroadcast) {
            if (pDetail) pDetail.innerText = 'Pengiriman dihentikan oleh pengguna.';
            break;
        }

        const pic = pendingPics[i];
        const currentIdx = i + 1;
        const pct = Math.round((currentIdx / pendingPics.length) * 100);

        if (pBar) pBar.style.width = pct + '%';
        if (pPercent) pPercent.innerText = pct + '%';
        if (pCounter) pCounter.innerText = `PIC ${currentIdx} dari ${pendingPics.length}`;
        if (pCurrent) pCurrent.innerText = `Mengirim ke Kak ${pic.pic_name} (${pic.pic_display_phone || pic.pic_phone})...`;
        if (pDetail) pDetail.innerText = `Memproses ${pic.total_items || pic.items?.length || 0} keep (${pic.total_on_progress || 0} On Progress)...`;

        try {
            const res = await sendSinglePicDigestDirect(pic.pic_phone, pic.pic_name, dateVal);
            if (res.success) {
                sent++;
                if (pDetail) pDetail.innerText = `✅ Berhasil dikirim ke Kak ${pic.pic_name}`;
            } else {
                failed++;
                if (pDetail) pDetail.innerText = `❌ Gagal: ${res.message || 'Respon gagal'}`;
            }
        } catch (err) {
            failed++;
            if (pDetail) pDetail.innerText = `❌ Error: ${err.message}`;
        }

        if (pSummary) pSummary.innerText = `${sent} Terkirim · ${failed} Gagal`;

        // Delay & Rest cycle antar pesan
        if (currentIdx < pendingPics.length && !fu12CancelBroadcast) {
            const isBatchBreak = (currentIdx % batchSize === 0) && (batchCooldown > 0);
            if (isBatchBreak) {
                for (let c = batchCooldown; c > 0; c--) {
                    if (fu12CancelBroadcast) break;
                    if (pCurrent) pCurrent.innerText = `☕ Rest Cycle: Batch ${currentIdx} selesai`;
                    if (pDetail) pDetail.innerText = `Istirahat proteksi anti-suspend WA: ${c} detik tersisa...`;
                    await sleep(1000);
                }
            } else {
                const waitSec = Math.floor(Math.random() * (delayMax - delayMin + 1)) + delayMin;
                for (let w = waitSec; w > 0; w--) {
                    if (fu12CancelBroadcast) break;
                    if (pDetail) pDetail.innerText = `⏱️ Jeda acak proteksi WhatsApp: ${w} detik...`;
                    await sleep(1000);
                }
            }
        }
    }

    fu12IsBroadcasting = false;

    // Tampilkan Completion View di dalam modal yang rapi dan elegan (tanpa alert native browser)
    if (activeContent) activeContent.style.display = 'none';
    if (doneContent) doneContent.style.display = 'block';

    const doneTitle = document.getElementById('fu12DoneTitle');
    const doneSent = document.getElementById('fu12DoneSentCount');
    const doneFailed = document.getElementById('fu12DoneFailedCount');

    if (doneTitle) {
        doneTitle.innerText = fu12CancelBroadcast ? 'Pengiriman Dihentikan' : 'Pengiriman Selesai!';
    }
    if (doneSent) doneSent.innerText = sent;
    if (doneFailed) doneFailed.innerText = failed;

    loadAutoFuH1Data();
}

function cancelFu12Broadcast() {
    if (confirm('Yakin ingin menghentikan antrean pengiriman reminder?')) {
        fu12CancelBroadcast = true;
        const btnCancel = document.getElementById('btnCancelFu12Broadcast');
        if (btnCancel) {
            btnCancel.disabled = true;
            btnCancel.innerText = 'Menghentikan...';
        }
    }
}
window.cancelFu12Broadcast = cancelFu12Broadcast;

// Modal Koreksi Nomor PIC (Ubah dari LID / Nomor Tidak Valid ke HP Seluler)
function openUpdatePicPhoneModal(picName, oldPhone) {
    const elName = document.getElementById('fu12ModalPicName');
    const elOld = document.getElementById('fu12ModalOldPhone');
    const elNew = document.getElementById('fu12ModalNewPhone');
    if (elName) elName.value = picName || '';
    if (elOld) elOld.value = oldPhone || '';
    if (elNew) elNew.value = '';

    const modal = document.getElementById('fu12UpdatePhoneModal');
    if (modal) modal.style.display = 'flex';
    setTimeout(() => {
        if (elNew) elNew.focus();
    }, 120);
}

function closeFu12UpdatePhoneModal() {
    const modal = document.getElementById('fu12UpdatePhoneModal');
    if (modal) modal.style.display = 'none';
}

async function submitFu12PhoneUpdate() {
    const picName = (document.getElementById('fu12ModalPicName')?.value || '').trim();
    const oldPhone = (document.getElementById('fu12ModalOldPhone')?.value || '').trim();
    const newPhone = (document.getElementById('fu12ModalNewPhone')?.value || '').trim();

    if (!newPhone || newPhone.length < 9) {
        alert('Silakan masukkan nomor WhatsApp seluler yang valid (minimal 9 digit, contoh: 0812xxxx atau 62812xxxx).');
        return;
    }

    const btn = document.getElementById('btnSubmitFu12PhoneUpdate');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>Menyimpan...</span>';
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch('/api/auto-fu-h1/update-pic-phone', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ pic_name: picName, old_phone: oldPhone, new_phone: newPhone })
        });
        const json = await res.json();
        if (json.success) {
            alert(json.message);
            closeFu12UpdatePhoneModal();
            loadAutoFuH1Data(); // Refresh UI seketika
        } else {
            alert(json.message || 'Gagal memperbarui nomor PIC.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan: ' + e.message);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }
}
window.openUpdatePicPhoneModal = openUpdatePicPhoneModal;
window.closeFu12UpdatePhoneModal = closeFu12UpdatePhoneModal;
window.submitFu12PhoneUpdate = submitFu12PhoneUpdate;

// Window global exports
window.renderTab12AutoFollowUpH1 = renderTab12AutoFollowUpH1;
window.loadAutoFuH1Data = loadAutoFuH1Data;
window.switchFu12View = switchFu12View;
window.renderFu12HistoryTable = renderFu12HistoryTable;
window.closeFu12ProgressModal = closeFu12ProgressModal;
window.closeFu12ModalAndOpenHistory = closeFu12ModalAndOpenHistory;
window.onAutoFuDateChange = onAutoFuDateChange;
window.applyAutoFuFilter = applyAutoFuFilter;
window.sendSinglePicDigest = sendSinglePicDigest;
window.sendFu12SinglePicDigest = sendSinglePicDigest;
window.toggleFu12Drawer = toggleFu12Drawer;
window.toggleAllFu12Drawers = toggleAllFu12Drawers;
window.clearFu12Search = clearFu12Search;
window.toggleFu12ClearBtn = toggleFu12ClearBtn;
window.sanitizeFu12Autofill = sanitizeFu12Autofill;
window.confirmSendAllH1Reminders = confirmSendAllH1Reminders;
window.insertFu12Tag = insertFu12Tag;
window.updateFu12LivePreview = updateFu12LivePreview;
window.setFu12PreviewMode = setFu12PreviewMode;
window.resetFu12TemplateDefault = resetFu12TemplateDefault;
window.saveFu12Settings = saveFu12Settings;

// Auto-trigger load jika Tab 12 aktif saat load awal & tangkal autofill Chrome
document.addEventListener('DOMContentLoaded', () => {
    [50, 150, 300, 600, 1200, 2000].forEach(delay => {
        setTimeout(sanitizeFu12Autofill, delay);
    });

    if (window.WORKSPACE_ACTIVE_TAB === 12 || (typeof currentActiveTab !== 'undefined' && currentActiveTab === 12)) {
        setTimeout(() => {
            if (typeof renderTab12AutoFollowUpH1 === 'function') renderTab12AutoFollowUpH1();
        }, 150);
    }
});
</script>
