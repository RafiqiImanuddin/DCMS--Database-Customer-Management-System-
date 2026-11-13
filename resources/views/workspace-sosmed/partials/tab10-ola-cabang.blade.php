<section id="tabContent10" class="tab-content mb-16">
    <div class="w-full space-y-5">
        <!-- 1. Header: Selaras dengan Desain Modern Workspace Sosmed -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-700">
                        <span class="size-2 rounded-full bg-amber-500"></span>
                        SLA / OLA Cabang
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs font-medium text-slate-500">Operational Level Agreement Kecepatan Respon Tim FU</span>
                </div>
                <h1 class="mt-1 text-2xl font-black text-slate-900 tracking-tight">Top Performance OLA / SLA Cabang</h1>
                <p class="mt-0.5 text-xs text-slate-500">Monitoring real-time disiplin waktu respon claim format keep (standar respon &le; 15 menit) antar cabang toko Gadgetmart.</p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex items-center gap-2">
                <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 hover:text-slate-900 transition cursor-pointer" onclick="switchTab(4)" title="Lihat Data Detail di Format Keep">
                    <svg class="size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    <span>Buka Format Keep</span>
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-brand-700 transition cursor-pointer" onclick="refreshTab10Data()" title="Muat ulang data performa OLA cabang">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    <span>Refresh Data</span>
                </button>
            </div>
        </div>

        <!-- 2. Filter Bar: Periode Bulan & Cakupan Hari Ini vs Bulan Berjalan -->
        <div class="dcms-card p-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Dropdown Periode Bulan -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 whitespace-nowrap">Periode:</span>
                        <select id="tab10FilterPeriode" class="dcms-input py-2 text-xs font-bold text-slate-700 w-48" onchange="renderTab10OLADashboard()">
                            <option value="ALL">Semua Periode</option>
                            <option value="November-26" selected>November 2026 (Terbaru)</option>
                            <option value="Oktober-26">Oktober 2026</option>
                            <option value="September-26">September 2026</option>
                            <option value="Agustus-26">Agustus 2026</option>
                            <option value="Juli-26">Juli 2026</option>
                            <option value="Juni-26">Juni 2026</option>
                        </select>
                    </div>

                    <!-- Scope Switcher: Bulan Berjalan vs Hari Ini Saja -->
                    <div class="inline-flex rounded-xl bg-slate-100 p-0.5 text-xs font-semibold text-slate-600">
                        <button type="button" id="tab10BtnScopePeriod" class="rounded-lg px-3 py-1.5 text-xs font-bold transition bg-white text-brand-700 shadow-xs cursor-pointer" onclick="setTab10Scope('PERIOD')">
                            🗓️ Sepanjang Periode
                        </button>
                        <button type="button" id="tab10BtnScopeToday" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition text-slate-500 hover:text-slate-800 cursor-pointer" onclick="setTab10Scope('TODAY')">
                            ⚡ Hari Ini Saja (Realtime)
                        </button>
                    </div>
                </div>

                <!-- Live Indicator Pill -->
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 font-semibold text-emerald-700">
                        <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span id="tab10LiveStatusText">Standar OLA: Maksimal 15 Menit</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. Metric KPI Cards -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Card 1: Top Cabang #1 -->
            <div class="dcms-card p-4 border-l-4 border-l-amber-500">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">🥇 Cabang Terdisiplin #1</span>
                    <span class="text-xs font-black bg-amber-100 text-amber-800 px-2 py-0.5 rounded-lg">Rank 1</span>
                </div>
                <div class="mt-2 text-xl font-black text-slate-900 truncate" id="tab10TopBranchName">-</div>
                <div class="mt-0.5 text-[11px] text-amber-700 font-semibold" id="tab10TopBranchMeta">Kepatuhan OLA: 0%</div>
            </div>

            <!-- Card 2: Tingkat Kepatuhan SLA Jaringan -->
            <div class="dcms-card p-4 border-l-4 border-l-emerald-500">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">🎯 Kepatuhan SLA Jaringan</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-emerald-700" id="tab10NetworkRate">0%</div>
                <div class="mt-0.5 text-[11px] text-slate-500" id="tab10NetworkMeta">0 dari 0 lead tepat waktu (&le;15m)</div>
            </div>

            <!-- Card 3: Leads Overdue -->
            <div class="dcms-card p-4 border-l-4 border-l-rose-500 cursor-pointer hover:shadow-md transition" onclick="switchTab(4); setTimeout(() => filterByKeepStatus('OLA_OVERDUE'), 200);" title="Klik untuk membuka lead overdue di tabel Format Keep">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-800">🚨 Leads Overdue</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-rose-50 text-rose-600">
                        <span class="size-2 rounded-full bg-rose-500 animate-ping"></span>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-rose-600" id="tab10OverdueCount">0</div>
                <div class="mt-0.5 text-[11px] text-rose-600 font-medium">&gt;15 menit belum di-claim (Klik periksa)</div>
            </div>

            <!-- Card 4: Total Closing WIN -->
            <div class="dcms-card p-4 border-l-4 border-l-indigo-500 cursor-pointer hover:shadow-md transition" onclick="switchTab(4); setTimeout(() => filterByKeepStatus('WIN'), 200);" title="Klik untuk memfilter transaksi WIN di Format Keep">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-800">✨ Total Closing WIN</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-indigo-700" id="tab10TotalWin">0</div>
                <div class="mt-0.5 text-[11px] text-slate-500" id="tab10WinMeta">Closing rate dari keep ter-claim</div>
            </div>
        </div>

        <!-- 4. CHART UTAMA: Persis Desain & Style Referensi User (Card Tegas + Chart.js) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Chart 1: Bar Chart Performa OLA Cabang (2 Kolom di Desktop) -->
            <div class="lg:col-span-2 dcms-card p-5 border border-slate-200/90 shadow-sm rounded-2xl bg-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 mb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base">📈</span>
                            <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight">Trend Kepatuhan OLA &amp; Respon Cabang</h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Perbandingan Lead Tepat Waktu (&le;15 Menit) vs Overdue &amp; Target SLA 90% per Cabang Toko</p>
                    </div>

                    <!-- Chart Legend Indicators -->
                    <div class="flex items-center gap-3 text-xs">
                        <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                            <span class="size-3 rounded-xs bg-[#c9182b]"></span>
                            <span>Tepat Waktu (&le;15m)</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                            <span class="size-3 rounded-xs bg-slate-400"></span>
                            <span>Overdue (&gt;15m)</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                            <span class="w-4 border-t-2 border-dashed border-slate-800"></span>
                            <span>Target SLA 90%</span>
                        </span>
                    </div>
                </div>

                <!-- Canvas Chart.js Container -->
                <div class="relative w-full" style="height: 340px;">
                    <canvas id="tab10OlaChartCanvas"></canvas>
                </div>
            </div>

            <!-- Chart 2: Komposisi Respon Lead Jaringan (1 Kolom di Desktop) -->
            <div class="dcms-card p-5 border border-slate-200/90 shadow-sm rounded-2xl bg-white flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                        <span class="text-base">🥧</span>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight">Komposisi Respon Lead</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Proporsi status kecepatan tindak lanjut tim FU</p>
                        </div>
                    </div>

                    <div class="relative w-full flex items-center justify-center my-3" style="height: 220px;">
                        <canvas id="tab10DonutChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Donut Legend Mini Stats -->
                <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-emerald-500"></span>
                            <span class="font-medium">Tepat Waktu (&le;15m)</span>
                        </div>
                        <span class="font-bold text-slate-900" id="tab10DonutStatOnTime">0</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-rose-500"></span>
                            <span class="font-medium">Overdue (&gt;15m)</span>
                        </div>
                        <span class="font-bold text-rose-600" id="tab10DonutStatOverdue">0</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-slate-300"></span>
                            <span class="font-medium">Pending Belum Claim</span>
                        </div>
                        <span class="font-bold text-slate-600" id="tab10DonutStatPending">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. LEADERBOARD MATRIX TABLE: Lengkap per Cabang -->
        <div class="dcms-card p-5 border border-slate-200/90 shadow-sm rounded-2xl bg-white space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2">
                <div>
                    <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight">🏆 Peringkat &amp; Evaluasi Lengkap Cabang Toko</h3>
                    <p class="text-xs text-slate-500">Rincian performa kedisiplinan dan rasio keberhasilan closing keep per cabang toko</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                <table class="w-full min-w-[850px] text-left text-xs" id="tab10LeaderboardTable">
                    <thead class="bg-slate-50/90 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-center" style="width: 8%;">Rank</th>
                            <th class="px-4 py-3 text-left" style="width: 22%;">Cabang Toko</th>
                            <th class="px-3 py-3 text-center" style="width: 10%;">Total Keep</th>
                            <th class="px-3 py-3 text-center" style="width: 12%;">Tepat Waktu (&le;15m)</th>
                            <th class="px-3 py-3 text-center" style="width: 12%;">Overdue (&gt;15m)</th>
                            <th class="px-3 py-3 text-center" style="width: 10%;">Pending</th>
                            <th class="px-4 py-3 text-left" style="width: 16%;">Kepatuhan OLA</th>
                            <th class="px-3 py-3 text-center" style="width: 10%;">Closing WIN</th>
                            <th class="px-3 py-3 text-center" style="width: 8%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tab10LeaderboardBody" class="divide-y divide-slate-100">
                        <tr><td colspan="9" class="px-4 py-8 text-center text-xs text-slate-400">Memuat data performa OLA cabang...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
