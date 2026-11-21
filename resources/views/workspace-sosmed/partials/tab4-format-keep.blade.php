                <section id="tabContent4" class="tab-content mb-16 {{ (int) ($activeTab ?? 1) === 4 ? 'active' : '' }}">
    <div class="w-full space-y-5">
        <!-- 1. Header: Selaras dengan Customer & Outreach (Leads & Master Customer) -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-500/10 px-2.5 py-0.5 text-xs font-bold text-brand-700">
                        <span class="size-2 rounded-full bg-brand-500"></span>
                        Format Keep
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs font-medium text-slate-500">Rekap Data Booking & Keep Sosmed</span>
                </div>
                <h1 class="mt-1 text-2xl font-black text-slate-900">Format Keep Customer</h1>
            </div>

            <!-- View Toggle & Export XLSX Button -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Segmented Switch View Toggle: Tabel vs Dashboard Admin Sosmed -->
                <div class="inline-flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200 shadow-2xs">
                    <button type="button" id="btnKeepViewTable" onclick="switchKeepView('table')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-slate-800 shadow-xs cursor-pointer">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        <span>Tabel Data Keep</span>
                    </button>
                    <button type="button" id="btnKeepViewDashboard" onclick="switchKeepView('dashboard')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-500 hover:text-slate-800 cursor-pointer">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        <span>Dashboard Admin Sosmed</span>
                    </button>
                </div>

                <button type="button" 
                        onclick="exportKeepDataXlsx()" 
                        id="btnExportKeepXlsx" 
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white px-4 py-2 text-xs font-bold shadow-sm shadow-emerald-600/20 transition cursor-pointer"
                        title="Export data Format Keep yang tampil ke file Excel (.xlsx)">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    <span>Export Excel (.xlsx)</span>
                </button>
            </div>
        </div>

                <div id="keepTableViewContainer" class="space-y-5">
<!-- 2. Metric Cards: Color Palette & Border-L Selaras Customer & Outreach (Clickable Filters) -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-7">
            <!-- Card 1: Total Keep -->
            <div id="cardKeepTotal" class="dcms-card p-4 border-l-4 border-l-brand-500 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none" onclick="filterByKeepStatus('ALL')" title="Klik untuk menampilkan semua data keep">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider">Total Keep</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-brand-50 text-brand-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-slate-900" id="keepTotalCount">0</div>
                <div class="mt-0.5 text-[11px] text-slate-500">Total data transaksi</div>
            </div>

            <!-- Card 2: Unassign (Belum di-claim FU) -->
            <div id="cardKeepUnassign" class="dcms-card p-4 border-l-4 border-l-amber-500 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none" onclick="filterByKeepStatus('UNASSIGN')" title="Klik untuk memfilter transaksi Unassign (Belum ada Tim Follow Up / Belum di-claim)">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Unassign</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-amber-50 text-amber-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-amber-700" id="keepUnassignCount">0</div>
                <div class="mt-0.5 text-[11px] text-amber-600 font-semibold">Belum di-claim FU</div>
            </div>

            <!-- Card 3: Closing WIN -->
            <div id="cardKeepWin" class="dcms-card p-4 border-l-4 border-l-emerald-500 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none" onclick="filterByKeepStatus('WIN')" title="Klik untuk memfilter transaksi Closing WIN">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Closing WIN</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-emerald-700" id="keepWinCount">0</div>
                <div class="mt-0.5 text-[11px] text-slate-500">Transaksi closing deal</div>
            </div>

            <!-- Card 4: Need to Check -->
            <div id="cardKeepNeedToCheck" class="dcms-card p-4 border-l-4 border-l-orange-500 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none" onclick="filterByKeepStatus('NEED TO CHECK')" title="Klik untuk memfilter transaksi Need to Check">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-orange-700">Need to Check</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-orange-50 text-orange-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-orange-700" id="keepNeedToCheckCount">0</div>
                <div class="mt-0.5 text-[11px] text-orange-600 font-semibold">Perlu kroscek admin</div>
            </div>

            <!-- Card 5: On Progress -->
            <div id="cardKeepProgress" class="dcms-card p-4 border-l-4 border-l-sky-500 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none" onclick="filterByKeepStatus('ON PROGRESS')" title="Klik untuk memfilter transaksi On Progress">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-800">On Progress</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-sky-50 text-sky-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-sky-600" id="keepProgressCount">0</div>
                <div class="mt-0.5 text-[11px] text-slate-500">Sedang ditindaklanjuti</div>
            </div>

            <!-- Card 6: Lose / Cancel -->
            <div id="cardKeepLose" class="dcms-card p-4 border-l-4 border-l-rose-500 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none" onclick="filterByKeepStatus('LOSE')" title="Klik untuk memfilter transaksi Lose / Cancel">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-800">Lose / Cancel</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-rose-50 text-rose-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-rose-600" id="keepLoseCount">0</div>
                <div class="mt-0.5 text-[11px] text-slate-500">Batal / tidak respon</div>
            </div>

            <!-- Card 7: Closing Rate -->
            <div id="cardKeepClosingRateWrapper" class="dcms-card p-4 border-l-4 border-l-purple-500 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none" onclick="filterByKeepStatus('WIN')" title="Rasio Keberhasilan WIN (Klik untuk memfilter Closing WIN)">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-purple-800">Closing Rate</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-purple-50 text-purple-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-purple-700" id="keepClosingRate">0%</div>
                <div class="mt-0.5 text-[11px] text-slate-500">Rasio keberhasilan WIN</div>
            </div>
        </div>

        <!-- 3. Filter Bar: Rapi, 1-Row / Responsive Grid Selaras Customer & Outreach -->
        <div class="dcms-card p-4">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12 items-center" id="keepFilterSection">
                <!-- Search Input with Icon -->
                <div class="sm:col-span-2 lg:col-span-4">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <input type="search" name="search_keep_filter_query" id="keepSearchInput" class="dcms-input pl-10 pr-9 py-2.5 text-xs" placeholder="Cari nama, nomor WA, atau barang..." autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly')" oninput="if(window.toggleKeepClearBtn){window.toggleKeepClearBtn();} if(window.debouncedApplyKeepFilters){window.debouncedApplyKeepFilters();}else{applyKeepFilters();}">
                        <button type="button" id="btnClearKeepSearch" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 size-5 items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer" onclick="if(window.clearKeepSearchInput){window.clearKeepSearchInput();}" title="Hapus pencarian">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Periode Dropdown -->
                <div class="lg:col-span-2">
                    <select id="keepFilterPeriode" class="dcms-input py-2.5 text-xs font-semibold text-slate-700" onchange="applyKeepFilters()">
                        <option value="ALL">Semua Periode</option>
                        <option value="November-26" selected>November 2026 (Terbaru)</option>
                        <option value="Oktober-26">Oktober 2026</option>
                        <option value="September-26">September 2026</option>
                        <option value="Agustus-26">Agustus 2026</option>
                        <option value="Juli-26">Juli 2026</option>
                        <option value="Juni-26">Juni 2026</option>
                    </select>
                </div>

                <!-- Tanggal Input -->
                <div class="lg:col-span-2">
                    <input type="date" id="keepFilterTanggal" class="dcms-input py-2.5 text-xs font-semibold text-slate-700" onchange="applyKeepFilters()" title="Filter tanggal format keep">
                </div>

                <!-- Kode Sosmed Dropdown -->
                <div class="lg:col-span-2">
                    <select id="keepFilterSosmed" class="dcms-input py-2.5 text-xs font-semibold text-slate-700" onchange="applyKeepFilters()">
                        <option value="ALL">Semua Kode Sosmed</option>
                    </select>
                </div>

                <!-- Status & Reset Button -->
                <div class="lg:col-span-2 flex items-center gap-2">
                    <select id="keepFilterStatus" class="dcms-input py-2.5 text-xs font-semibold text-slate-700 flex-1" onchange="applyKeepFilters()">
                        <option value="ALL">Semua Status</option>
                        <option value="UNASSIGN">⏳ Unassign (Belum Di-claim FU)</option>
                        <option value="OLA_OVERDUE">🚨 OLA Overdue (&gt;15m Belum Di-claim)</option>
                        <option value="OLA_PENDING">⏱️ OLA Pending (Belum Di-claim)</option>
                        <option value="WIN">WIN (Closing)</option>
                        <option value="NEED TO CHECK">⚠️ NEED TO CHECK (Kroscek)</option>
                        <option value="ON PROGRESS">On Progress (Semua)</option>
                        <option value="ON PROGRESS HOT">On Progress Hot</option>
                        <option value="ON PROGRESS MEDIUM">On Progress Medium</option>
                        <option value="ON PROGRESS LOW">On Progress Low</option>
                        <option value="LOSE">Lose / Take Out / Cancel</option>
                        <option value="TAKE OUT">Take Out</option>
                        <option value="BELUM DIINPUT">Belum diinput</option>
                    </select>
                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 shrink-0 cursor-pointer" onclick="resetKeepFilters()" title="Reset Filter">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden inputs/counts required by client.js logic -->
        <input type="text" id="keepFilterReportAR" style="display:none;">
        <span id="keepFilterActiveCount" style="display:none;">0</span>

        <!-- 4. Table Container: Selaras 100% dengan Master Customer & Leads Customer -->
        <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1360px] text-left text-sm" id="keepTable">
                    <thead class="border-b border-slate-200 bg-slate-50/90 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5 text-left" style="width: 13%;">Customer</th>
                            <th class="px-3 py-3.5 text-center" style="width: 6%;">Tanggal</th>
                            <th class="px-3 py-3.5 text-center" style="width: 7%;">Timestamp</th>
                            <th class="px-4 py-3.5 text-left" style="width: 12%;">Barang Keep</th>
                            <th class="px-3 py-3.5 text-center" style="width: 6%;">Tgl. Ambil</th>
                            <th class="px-4 py-3.5 text-left" style="width: 11%;">Catatan</th>
                            <th class="px-3 py-3.5 text-center" style="width: 6%;">Kode Sosmed</th>
                            <th class="px-3 py-3.5 text-center" style="width: 6%;">Cabang</th>
                            <th class="px-3 py-3.5 text-center" style="width: 7%;">Report AR</th>
                            <th class="px-4 py-3.5 text-left" style="width: 12%;">Tim Follow Up</th>
                            <th class="px-3 py-3.5 text-center" style="width: 7%;">Status</th>
                            <th class="px-4 py-3.5 text-left" style="width: 15%;">Feedback Tim FU</th>
                            <th class="px-3 py-3.5 text-center" style="width: 4%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="keepTableBody" class="divide-y divide-slate-100 text-xs"></tbody>
                </table>
            </div>

            <!-- Footer Pagination: Selaras Customer & Outreach -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t border-slate-100 bg-slate-50/60 text-xs text-slate-500 font-medium">
                <div id="keepPaginationInfo" class="font-medium text-slate-600">Menampilkan 0 dari 0 Data</div>
                <div class="flex items-center gap-2">
                    <button class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer" id="btnKeepPrev" onclick="changeKeepPage(-1)">&laquo; Sebelumnya</button>
                    <span id="keepCurrentPageText" class="px-3 py-1.5 font-bold text-slate-700 bg-white border border-slate-200 rounded-xl shadow-xs">Halaman 1 / 1</span>
                    <button class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer" id="btnKeepNext" onclick="changeKeepPage(1)">Selanjutnya &raquo;</button>
                </div>
            </div>
        </section>
    </div>

    <!-- 3. DASHBOARD FORMAT KEEP ADMIN SOSMED -->
        <div id="keepAdminDashboardContainer" style="display: none;" class="space-y-5">
            <!-- Top KPI Summary Cards -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Card 1: Total Keep Admin Sosmed -->
                <div class="dcms-card p-4 border-l-4 border-l-brand-500 shadow-xs">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-[11px] font-bold uppercase tracking-wider">Total Keep Terkirim</span>
                        <div class="grid size-7 place-items-center rounded-lg bg-brand-50 text-brand-600">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-black text-slate-900" id="dashAdminTotalKeep">0</div>
                    <div class="mt-0.5 text-[11px] text-slate-500">Akumulasi seluruh keep masuk</div>
                </div>

                <!-- Card 2: Closing WIN -->
                <div class="dcms-card p-4 border-l-4 border-l-emerald-500 shadow-xs">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Total Closing (WIN)</span>
                        <div class="grid size-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-black text-emerald-600" id="dashAdminTotalWin">0</div>
                    <div class="mt-0.5 text-[11px] text-slate-500" id="dashAdminWinShare">0% dari total keep</div>
                </div>

                <!-- Card 3: Overall Closing Rate -->
                <div class="dcms-card p-4 border-l-4 border-l-blue-500 shadow-xs">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Closing Rate Rata-rata</span>
                        <div class="grid size-7 place-items-center rounded-lg bg-blue-50 text-blue-600">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-black text-blue-600" id="dashAdminClosingRate">0.0%</div>
                    <div class="mt-0.5 text-[11px] text-slate-500" id="dashAdminClosingRateSubtext">Rasio konversi WIN vs total keep</div>
                </div>

                <!-- Card 4: Top Contributor Admin -->
                <div class="dcms-card p-4 border-l-4 border-l-purple-500 shadow-xs">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-purple-700">Top Admin Sosmed</span>
                        <div class="grid size-7 place-items-center rounded-lg bg-purple-50 text-purple-600">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.01-6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM12 1.5v3m0 16.5v1.5"/></svg>
                        </div>
                    </div>
                    <div class="mt-2 text-lg font-black text-purple-700 truncate" id="dashAdminTopName">-</div>
                    <div class="mt-0.5 text-[11px] text-slate-500" id="dashAdminTopSubtext">Kontributor keep terbanyak</div>
                </div>
            </div>

            <!-- Filter Bar Dashboard Admin Sosmed -->
            <div class="dcms-card p-4 space-y-3">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Search Admin -->
                        <div class="relative min-w-[220px]">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </span>
                            <input type="search" id="dashAdminSearchInput" name="q_live_filter_term" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-1p-ignore="true" data-bwignore="true" data-form-type="other" readonly onfocus="this.removeAttribute('readonly');" oninput="renderKeepAdminDashboard()" placeholder="Cari admin sosmed / sender..." class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 py-1.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none transition">
                        </div>

                        <!-- Filter Periode -->
                        <select id="dashAdminPeriodFilter" onchange="renderKeepAdminDashboard()" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 focus:border-brand-500 focus:outline-none transition">
                            <option value="ALL">📅 Semua Periode</option>
                            <option value="THIS_MONTH" selected>📅 Bulan Ini</option>
                            <option value="LAST_7_DAYS">📅 7 Hari Terakhir</option>
                            <option value="TODAY">📅 Hari Ini</option>
                        </select>

                        <!-- Filter Cabang -->
                        <select id="dashAdminCabangFilter" onchange="renderKeepAdminDashboard()" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 focus:border-brand-500 focus:outline-none transition">
                            <option value="ALL">🏢 Semua Cabang</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                        <span id="dashAdminTotalFilteredCount">Memuat data...</span>
                    </div>
                </div>
            </div>

                        <!-- Section Visualisasi Grafik & Donut Chart Admin Sosmed -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Chart 1: Bar Chart Trend Performa Admin Sosmed (2 Kolom di Desktop) -->
                <div class="lg:col-span-2 dcms-card p-5 border border-slate-200/90 shadow-xs rounded-2xl bg-white">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 mb-4 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-base">📈</span>
                                <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight">Trend & Performa Format Keep per Admin</h3>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Perbandingan Total Keep, Closing WIN, dan On Progress per Admin Sosmed Resmi</p>
                        </div>

                        <!-- Chart Legend Indicators -->
                        <div class="flex items-center gap-3 text-xs flex-wrap">
                            <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                                <span class="size-3 rounded-xs bg-[#2563eb]"></span>
                                <span>Total Keep</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                                <span class="size-3 rounded-xs bg-[#10b981]"></span>
                                <span>Closing WIN</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                                <span class="size-3 rounded-xs bg-[#0ea5e9]"></span>
                                <span>On Progress</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                                <span class="w-4 border-t-2 border-dashed border-slate-800"></span>
                                <span>Closing Rate (%)</span>
                            </span>
                        </div>
                    </div>

                    <!-- Canvas Chart.js Container -->
                    <div class="relative w-full" style="height: 320px;">
                        <canvas id="dashAdminBarChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Chart 2: Donut Chart Komposisi Format Keep (1 Kolom di Desktop) -->
                <div class="dcms-card p-5 border border-slate-200/90 shadow-xs rounded-2xl bg-white flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="text-base">🍩</span>
                                <div>
                                    <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight">Komposisi Format Keep</h3>
                                    <p class="text-xs text-slate-500 mt-0.5" id="dashAdminDonutSubTitle">Total keep, unassign &amp; follow up</p>
                                </div>
                            </div>
                        </div>

                        <!-- Donut Chart Container dengan Center Text Total Keep -->
                        <div class="relative w-full flex items-center justify-center my-2" style="height: 200px;">
                            <canvas id="dashAdminDonutChartCanvas"></canvas>
                            <div id="dashAdminDonutCenterLabel" class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-2xl sm:text-3xl font-black text-slate-900 leading-none tracking-tight" id="dashAdminDonutCenterTotal">0</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1">Total Keep</span>
                            </div>
                        </div>
                    </div>

                    <!-- Donut Legend Mini Stats (Belum FU, Progress, WIN, Lose) -->
                    <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-amber-500"></span>
                                <span class="font-medium">Unassign (Belum Claim)</span>
                            </div>
                            <span class="font-bold text-amber-700" id="dashAdminDonutStatUnassign">0</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-sky-500"></span>
                                <span class="font-medium">Di Follow Up (Progress)</span>
                            </div>
                            <span class="font-bold text-sky-700" id="dashAdminDonutStatProgress">0</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-emerald-500"></span>
                                <span class="font-medium">Closing WIN</span>
                            </div>
                            <span class="font-bold text-emerald-700" id="dashAdminDonutStatWin">0</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-rose-500"></span>
                                <span class="font-medium">Lose / Cancel</span>
                            </div>
                            <span class="font-bold text-rose-700" id="dashAdminDonutStatLose">0</span>
                        </div>
                    </div>
                </div>
            </div>

<!-- Leaderboard Kinerja Admin Sosmed Table -->
            <div class="dcms-card overflow-hidden">
                <div class="p-4 border-b border-slate-200/80 flex items-center justify-between flex-wrap gap-2 bg-slate-50/50">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <span>🏆 Leaderboard & Performa Admin Sosmed</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Statistik format keep yang dikirim, status closing WIN, dan distribusi transaksi per admin sosmed.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-brand-50 text-brand-700 border border-brand-200/60">
                        <span class="size-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                        <span>Auto Aggregate Live</span>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200/90 bg-slate-100/75 text-[11px] font-black uppercase tracking-wider text-slate-600">
                                <th class="py-3 px-3.5 text-center w-14">#Rank</th>
                                <th class="py-3 px-3.5">Admin Sosmed (Sender)</th>
                                <th class="py-3 px-3.5 text-center text-brand-700 bg-brand-50/50" title="Skor Performa Kombinasi: 60% Closing Rate + 40% Volume Format Keep">⚡ Skor KPI</th>
                                <th class="py-3 px-3.5 text-center">Total Keep</th>
                                <th class="py-3 px-3.5 text-center text-emerald-700">WIN</th>
                                <th class="py-3 px-3.5 text-center text-amber-700">On Progress</th>
                                <th class="py-3 px-3.5 text-center text-slate-600">Lose / Drop</th>
                                <th class="py-3 px-3.5 text-center text-rose-600">Belum FU</th>
                                <th class="py-3 px-3.5 text-center w-36">Closing Rate</th>
                                <th class="py-3 px-3.5 text-center">Cabang Utama</th>
                                <th class="py-3 px-3.5 text-center">Kredit vs Non</th>
                            </tr>
                        </thead>
                        <tbody id="dashAdminTableBody" class="divide-y divide-slate-100 font-medium text-slate-700">
                            <tr>
                                <td colspan="11" class="py-12 text-center text-slate-400">
                                    <span class="inline-block size-5 border-2 border-brand-600 border-t-transparent rounded-full animate-spin"></span>
                                    <span class="ml-2">Menganalisis performa admin sosmed...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
async function ensureSheetJsLoaded() {
    if (typeof XLSX !== 'undefined') return true;
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = '/workspace-sosmed/lib/xlsx.full.min.js';
        script.onload = () => resolve(true);
        script.onerror = () => {
            const cdn = document.createElement('script');
            cdn.src = 'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js';
            cdn.onload = () => resolve(true);
            cdn.onerror = () => reject(new Error('Gagal memuat library Excel (XLSX).'));
            document.head.appendChild(cdn);
        };
        document.head.appendChild(script);
    });
}

async function exportKeepDataXlsx() {
    const rowsToExport = (typeof filteredKeepRows !== 'undefined' && filteredKeepRows.length > 0)
        ? filteredKeepRows
        : (typeof rawKeepRows !== 'undefined' ? rawKeepRows : []);

    if (!rowsToExport || rowsToExport.length === 0) {
        alert('Tidak ada data Format Keep yang dapat diexport saat ini.');
        return;
    }

    const btn = document.getElementById('btnExportKeepXlsx');
    const originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="inline-flex items-center gap-1.5"><span class="size-3.5 rounded-full border-2 border-white border-t-transparent animate-spin"></span><span>Memproses...</span></span>';
    }

    try {
        await ensureSheetJsLoaded();

        const headers = [
            'No',
            'Tanggal Keep',
            'Timestamp Keep',
            'Nama Customer',
            'Nomor WhatsApp',
            'Barang Keep',
            'Tanggal Ambil',
            'Catatan Customer',
            'Kode Sosmed',
            'Cabang',
            'Report No. AR',
            'Nama Tim Follow Up',
            'Nomor WhatsApp Tim FU',
            'Status Follow Up',
            'Feedback Tim Follow Up',
            'Nama Grup WhatsApp',
            'Nomor Sender'
        ];

        const data = [headers];

        rowsToExport.forEach((r, idx) => {
            const rawWA = String(r['Nomor WA'] || r['nomor_wa'] || '').replace(/'/g, '').trim();
            const rawFUWA = String(r['Nomor FU'] || r['nomor_fu'] || r['Nomor Follow Up'] || '').replace(/'/g, '').trim();
            const statusText = String(r['Status'] || r['status'] || 'UNASSIGN').toUpperCase();
            const cab = String(r['Cabang'] || r['cabang'] || (r['Grup ID'] && typeof KEEP_GROUP_CABANG_MAP !== 'undefined' && KEEP_GROUP_CABANG_MAP[r['Grup ID']] ? KEEP_GROUP_CABANG_MAP[r['Grup ID']].cabang : '') || '-');

            // Format phone with leading single quote so Excel displays full numbers properly
            const phoneStr = rawWA ? (rawWA.startsWith('0') || rawWA.startsWith('62') ? "'" + rawWA : rawWA) : '-';
            const fuPhoneStr = rawFUWA ? (rawFUWA.startsWith('0') || rawFUWA.startsWith('62') ? "'" + rawFUWA : rawFUWA) : '-';

            data.push([
                idx + 1,
                r['Tanggal Keep'] || r['tanggal'] || (r['Timestamp'] ? String(r['Timestamp']).split(' ')[0] : '-'),
                r['Timestamp'] || r['keep_timestamp'] || '-',
                r['Nama'] || r['nama'] || '-',
                phoneStr,
                r['Barang Keep'] || r['barang_keep'] || '-',
                r['Tanggal Ambil'] || r['tanggal_ambil'] || '-',
                r['Catatan'] || r['catatan'] || '-',
                r['Kode Sosmed'] || r['kode_sosmed'] || '-',
                cab,
                r['REPORT AR'] || r['report_ar'] || '-',
                r['Nama Tim Follow Up'] || r['nama_tim_follow_up'] || r['fu_name'] || '-',
                fuPhoneStr,
                statusText,
                r['Feedback Tim FU'] || r['feedback'] || r['feedback_tim_fu'] || '-',
                r['GRUP'] || r['grup'] || '-',
                r['Nomor Sender'] || r['nomor_sender'] || '-'
            ]);
        });

        const ws = XLSX.utils.aoa_to_sheet(data);

        // Styling: Lebar kolom terukur rapi
        ws['!cols'] = [
            { wch: 6 },  // No
            { wch: 15 }, // Tanggal Keep
            { wch: 20 }, // Timestamp Keep
            { wch: 24 }, // Nama Customer
            { wch: 18 }, // Nomor WhatsApp
            { wch: 34 }, // Barang Keep
            { wch: 15 }, // Tanggal Ambil
            { wch: 30 }, // Catatan Customer
            { wch: 14 }, // Kode Sosmed
            { wch: 15 }, // Cabang
            { wch: 16 }, // Report No. AR
            { wch: 22 }, // Nama Tim Follow Up
            { wch: 18 }, // Nomor WhatsApp Tim FU
            { wch: 18 }, // Status Follow Up
            { wch: 30 }, // Feedback Tim Follow Up
            { wch: 24 }, // Nama Grup WhatsApp
            { wch: 18 }  // Nomor Sender
        ];

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Format Keep Customer');

        const now = new Date();
        const dateStr = now.toISOString().slice(0, 10);
        const filterStatus = (document.getElementById('keepFilterStatus')?.value || 'ALL').replace(/[^A-Za-z0-9]/g, '_');
        const filename = `Format_Keep_Customer_${filterStatus}_${dateStr}.xlsx`;

        XLSX.writeFile(wb, filename);

        if (typeof showToast === 'function') {
            showToast(`✅ Berhasil mendownload ${rowsToExport.length} data Format Keep ke Excel!`, 'success');
        } else {
            alert(`✅ Berhasil mendownload ${rowsToExport.length} data Format Keep ke file Excel (${filename})!`);
        }
    } catch (err) {
        console.error('Error export XLSX:', err);
        alert('Gagal mengekspor file Excel: ' + err.message);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
}
window.exportKeepDataXlsx = exportKeepDataXlsx;
</script>

