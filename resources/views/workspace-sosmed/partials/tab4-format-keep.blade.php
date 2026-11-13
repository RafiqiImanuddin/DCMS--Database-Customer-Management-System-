                <section id="tabContent4" class="tab-content mb-16">
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
        </div>

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
                        <input type="search" name="search_keep_filter_query" id="keepSearchInput" class="dcms-input pl-10 pr-9 py-2.5 text-xs" placeholder="Cari nama, nomor WA, atau barang..." autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly')" oninput="applyKeepFilters(); if(window.toggleKeepClearBtn){window.toggleKeepClearBtn();}">
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
</section>

