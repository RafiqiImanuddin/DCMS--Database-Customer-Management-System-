<section id="tabContent6" class="tab-content mb-16 {{ (int) ($activeTab ?? 1) === 6 ? 'active' : '' }}">
    <div class="w-full space-y-5">
        <!-- 1. Hero Header: Selaras dengan Customer Outreach & Format Keep -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-500/10 px-2.5 py-0.5 text-xs font-bold text-brand-700">
                        <span class="size-2 rounded-full bg-brand-500"></span>
                        Klaim AR &amp; Denda Cabang
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs font-semibold text-slate-500" id="claimsHeroSub">Pengawasan faktur AR belum sosmed, siklus batch 3 hari, masa tenggang H+1, dan eskalasi denda</span>
                </div>
                <div class="mt-1 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Klaim AR &amp; Pengawasan Denda</h1>
                    <div class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200/80 px-2.5 py-1 text-xs font-bold text-amber-800 shadow-2xs" id="claimCycleBadge">
                        <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Siklus 3 Hari</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons: Modern Grouped Actions -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 px-3.5 py-2 text-xs font-bold text-indigo-800 hover:bg-indigo-100 shadow-2xs transition cursor-pointer"
                    onclick="syncMissingARToClaims(false)"
                    title="Tarik transaksi AR yang belum sosmed dari Tracking AR ke antrean klaim">
                    <svg class="size-3.5 text-indigo-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    <span>Sinkron AR</span>
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-teal-200 bg-teal-50 px-3.5 py-2 text-xs font-bold text-teal-800 hover:bg-teal-100 shadow-2xs transition cursor-pointer" id="btnDownloadSingleExcel"
                    onclick="downloadARClaimsSingleExcel()"
                    title="Unduh seluruh data klaim AR ke dalam SATU file Excel (.xlsx) gabungan">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h2"/><path d="M8 17h2"/><path d="M14 13h2"/><path d="M14 17h2"/></svg>
                    <span>Unduh 1 Excel</span>
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-2 text-xs font-bold text-blue-800 hover:bg-blue-100 shadow-2xs transition cursor-pointer" id="btnShareBatchDay4"
                    onclick="shareARClaimsBatchDay4()"
                    title="Unduh paket file Excel per cabang (.zip) atau 1 file gabungan dan pindahkan ke antrean 'Cek AR Setelah di Share'">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    <span>Paket &amp; Share</span>
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition cursor-pointer"
                    onclick="checkARClaimsAutoResolution(true)"
                    title="Cek apakah ada AR yang sudah direvisi cabang di transaksi SAP atau sudah lewat batas H+1">
                    <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Cek Revisi</span>
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition cursor-pointer"
                    onclick="exportClaimsPenaltyReport()"
                    title="Salin atau unduh rekap denda kasir per cabang untuk diserahkan ke Manajemen/HRD">
                    <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                    <span>Rekap Denda</span>
                </button>
            </div>
        </div>

        <!-- 2. Metric Cards: 5 Tahap Siklus Klaim AR (Interactive Filter Cards) -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <!-- Card 1: 1. Pengumpulan (3 Hari) -->
            <div class="dcms-card p-4 rounded-2xl border border-slate-200/80 border-l-4 border-l-amber-500 bg-amber-50/20 ring-2 ring-amber-500 shadow-sm cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none"
                onclick="setClaimsSubTab('COLLECTING')" id="cardStatCollecting"
                title="Klik untuk filter Batch Pengumpulan">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800">1. Pengumpulan</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-amber-50 text-amber-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-amber-700 tracking-tight" id="claimCountCollecting">0</div>
                <div class="mt-0.5 text-[11px] text-amber-600 font-semibold">Menunggu Hari ke-4</div>
            </div>

            <!-- Card 2: 2. Setelah di Share (H+1) -->
            <div class="dcms-card p-4 rounded-2xl border border-slate-200/80 border-l-4 border-l-orange-500 bg-white shadow-2xs cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none"
                onclick="setClaimsSubTab('SHARED')" id="cardStatShared"
                title="Klik untuk filter Masa Tenggang">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-orange-800">2. Di Share (H+1)</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-orange-50 text-orange-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-orange-700 tracking-tight" id="claimCountShared">0</div>
                <div class="mt-0.5 text-[11px] text-orange-600 font-semibold">Masa Tenggang Cabang</div>
            </div>

            <!-- Card 3: 3. Denda Sungguhan -->
            <div class="dcms-card p-4 rounded-2xl border border-slate-200/80 border-l-4 border-l-rose-500 bg-white shadow-2xs cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none"
                onclick="setClaimsSubTab('PENALTY')" id="cardStatPenalty"
                title="Klik untuk filter Denda Real">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-800">3. Denda Cabang</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-rose-50 text-rose-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-rose-700 tracking-tight" id="claimCountPenalty">0</div>
                <div class="mt-0.5 text-[11px] text-rose-600 font-bold truncate" id="claimTotalPenaltyRp">Rp 0 (Lewat H+1)</div>
            </div>

            <!-- Card 4: 4. Selesai Direvisi -->
            <div class="dcms-card p-4 rounded-2xl border border-slate-200/80 border-l-4 border-l-emerald-500 bg-white shadow-2xs cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none"
                onclick="setClaimsSubTab('RESOLVED')" id="cardStatResolved"
                title="Klik untuk filter Selesai Direvisi">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">4. Selesai Revisi</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-emerald-700 tracking-tight" id="claimCountResolved">0</div>
                <div class="mt-0.5 text-[11px] text-emerald-600 font-semibold">Sudah Jadi BJM.SOS</div>
            </div>

            <!-- Card 5: 5. Tidak Valid -->
            <div class="dcms-card p-4 rounded-2xl border border-slate-200/80 border-l-4 border-l-slate-400 bg-white shadow-2xs cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none"
                onclick="setClaimsSubTab('INVALID')" id="cardStatInvalid"
                title="Klik untuk filter Tidak Valid">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">5. Tidak Valid</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-slate-100 text-slate-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-slate-700 tracking-tight" id="claimCountInvalid">0</div>
                <div class="mt-0.5 text-[11px] text-slate-500 font-medium">Dianulir / Batal</div>
            </div>
        </div>

        <!-- 3. Modern Search & Filter Panel -->
        <div class="dcms-card p-4 rounded-2xl border border-slate-200/80 bg-white shadow-2xs space-y-3">
            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </span>
                    <input type="search" id="claimsSearchInput" name="search_claims_query_unique" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly')" class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pl-10 pr-12 text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-brand-500 focus:outline-none transition shadow-2xs"
                        placeholder="Cari No. AR, Customer, No. WA, Cabang, Barang, Sales..." oninput="window._userExplicitTypedClaimsSearch = true; applyClaimsFilters()">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[10px] font-bold text-slate-400">⌘K</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition shadow-2xs" id="btnToggleClaimsFilters"
                        onclick="toggleClaimsFiltersSection()">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        <span>Filter Detail</span>
                        <span class="size-4 rounded-full bg-brand-500 text-white text-[10px] flex items-center justify-center font-bold" id="claimsFilterActiveCount">0</span>
                    </button>
                </div>
            </div>

            <!-- Filter Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 pt-3 border-t border-slate-100" id="claimsFilterSection">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1" for="claimsFilterCabang">Cabang Toko</label>
                    <select id="claimsFilterCabang" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none transition"
                        onchange="applyClaimsFilters()">
                        <option value="ALL">Semua Cabang Toko</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1" for="claimsFilterSosmed">Target Sosmed</label>
                    <select id="claimsFilterSosmed" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none transition"
                        onchange="applyClaimsFilters()">
                        <option value="ALL">Semua Kode Sosmed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1" for="claimsFilterTanggal">Tanggal Masuk / Share</label>
                    <input type="date" id="claimsFilterTanggal" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none transition"
                        onchange="applyClaimsFilters()" title="Filter tanggal">
                </div>

                <div class="flex items-end">
                    <button type="button" class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 transition" onclick="resetClaimsFilters()"
                        title="Reset semua filter">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        <span>Reset Filter</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 5. Table Container: Modern Tailwind Card Shell -->
        <div class="dcms-card p-0 rounded-2xl border border-slate-200/80 bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="modern-tegas-table w-full text-left" id="claimsTable">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200">
                            <th style="width: 14%; text-align: left;">CUSTOMER</th>
                            <th style="width: 8%; text-align: center;">TANGGAL</th>
                            <th style="width: 8.5%; text-align: center;">NO. AR</th>
                            <th style="width: 14%; text-align: left;">BARANG &amp; HARGA</th>
                            <th style="width: 8%; text-align: center;">KODE SOSMED</th>
                            <th style="width: 7%; text-align: center;">CABANG</th>
                            <th style="width: 9%; text-align: center;">STATUS DENDA</th>
                            <th style="width: 12%; text-align: left;">FORMAT KEEP</th>
                            <th style="width: 6.5%; text-align: center;">EVIDENCE</th>
                            <th style="width: 7.5%; text-align: left;">AR SALES</th>
                            <th style="width: 6%; text-align: center;">VALIDASI</th>
                            <th style="width: 7%; text-align: left;">ALASAN</th>
                            <th style="width: 5%; text-align: center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="claimsTableBody" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>

            <!-- Modern Pagination Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-t border-slate-100 bg-slate-50/60">
                <div id="claimsPaginationInfo" class="text-xs font-semibold text-slate-500">Menampilkan 0 dari 0 Data Klaim</div>
                <div class="flex items-center gap-2">
                    <button class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs disabled:opacity-50 transition cursor-pointer" id="btnClaimsPrev" onclick="changeClaimsPage(-1)">&laquo; Sebelumnya</button>
                    <span id="claimsCurrentPageText" class="text-xs font-extrabold text-slate-700 px-2 bg-slate-100 rounded-lg py-1">Halaman 1 / 1</span>
                    <button class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs disabled:opacity-50 transition cursor-pointer" id="btnClaimsNext" onclick="changeClaimsPage(1)">Selanjutnya &raquo;</button>
                </div>
            </div>
        </div>
    </div>
</section>
