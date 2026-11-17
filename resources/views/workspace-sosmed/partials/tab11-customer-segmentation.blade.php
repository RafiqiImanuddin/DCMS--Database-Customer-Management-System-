<section id="tabContent11" class="tab-content mb-16 {{ (int) ($activeTab ?? 1) === 11 ? 'active' : '' }}">
    <div class="w-full space-y-5">
        <!-- 1. Hero Header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-pink-500/10 px-2.5 py-0.5 text-xs font-bold text-pink-700">
                        <span class="size-2 rounded-full bg-pink-500"></span>
                        Customer Segmentation &amp; Sales Insights
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs font-semibold text-slate-500" id="csHeroSubtitle">Analisis transaksi item terjual &amp; metode kredit based on format keep</span>
                </div>
                <div class="mt-1 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customer Segmentation &amp; Item Terjual</h1>
                    <div class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 border border-indigo-200/80 px-2.5 py-1 text-xs font-bold text-indigo-800 shadow-2xs">
                        <span class="size-1.5 rounded-full bg-indigo-500"></span>
                        <span id="csDataLiveBadge">Data Format Keep Aktif</span>
                    </div>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition cursor-pointer" onclick="refreshCustomerSegmentationData()">
                    <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    <span>Refresh Analisis</span>
                </button>
            </div>
        </div>

        <!-- 2. KPI Cards: Ringkasan Segmentasi Pelanggan -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <!-- Card 1: Total Customer Keep -->
            <div class="p-4 rounded-2xl border border-slate-200/80 border-l-4 border-l-blue-500 bg-white shadow-2xs">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-800">Total Customer Keep</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-blue-50 text-blue-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-blue-900 tracking-tight" id="csStatTotalKeep">0</div>
                <div class="mt-0.5 text-[11px] text-blue-600 font-semibold" id="csStatTotalKeepSub">Booking Masuk Sosmed</div>
            </div>

            <!-- Card 2: Closing / Item Terjual (WIN) -->
            <div class="p-4 rounded-2xl border border-slate-200/80 border-l-4 border-l-emerald-500 bg-white shadow-2xs">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Item Terjual (WIN)</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-emerald-700 tracking-tight" id="csStatTotalWin">0</div>
                <div class="mt-0.5 text-[11px] text-emerald-600 font-bold" id="csStatWinConversionRate">Conversion Rate: 0%</div>
            </div>

            <!-- Card 3: Transaksi Kredit -->
            <div class="p-4 rounded-2xl border border-slate-200/80 border-l-4 border-l-indigo-500 bg-white shadow-2xs">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-800">Transaksi Kredit</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-indigo-700 tracking-tight" id="csStatTotalCredit">0</div>
                <div class="mt-0.5 text-[11px] text-indigo-600 font-semibold" id="csStatCreditRatio">Rasio Kredit: 0%</div>
            </div>

            <!-- Card 4: Transaksi Non-Kredit (Cash / Transfer) -->
            <div class="p-4 rounded-2xl border border-slate-200/80 border-l-4 border-l-teal-500 bg-white shadow-2xs">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-teal-800">Non-Kredit / Cash</span>
                    <div class="grid size-7 place-items-center rounded-lg bg-teal-50 text-teal-600">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-black text-teal-700 tracking-tight" id="csStatTotalNonCredit">0</div>
                <div class="mt-0.5 text-[11px] text-teal-600 font-semibold" id="csStatNonCreditRatio">Rasio Non-Kredit: 0%</div>
            </div>
        </div>

        <!-- 3. Filter Bar -->
        <div class="p-4 rounded-2xl border border-slate-200/80 bg-white shadow-2xs space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5">
                <!-- Search Input -->
                <div class="relative sm:col-span-2">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </span>
                    <input type="search" id="csSearchInput" name="search_cs_segmentation_unique" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly')" class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pl-10 pr-10 text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-brand-500 focus:outline-none transition shadow-2xs"
                        placeholder="Cari Customer, No WA, Barang, PIC..." oninput="applyCustomerSegmentationFilters(); toggleCsClearBtn();">
                    <button type="button" id="btnCsClearSearch" class="hidden absolute inset-y-0 right-0 items-center pr-3 text-slate-400 hover:text-slate-600 transition" onclick="clearCsSearch();" title="Hapus pencarian">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                    </button>
                </div>

                <!-- Filter Metode Pembayaran (Kredit vs Non-Kredit) -->
                <div>
                    <select id="csFilterMetode" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none transition"
                        onchange="applyCustomerSegmentationFilters()">
                        <option value="ALL">Semua Metode (Kredit &amp; Non-Kredit)</option>
                        <option value="KREDIT">Kredit Saja (Leasing / Cicilan)</option>
                        <option value="NON_KREDIT">Non-Kredit Saja (Cash / Transfer)</option>
                    </select>
                </div>

                <!-- Filter Cabang -->
                <div>
                    <select id="csFilterCabang" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none transition"
                        onchange="applyCustomerSegmentationFilters()">
                        <option value="ALL">Semua Cabang Toko</option>
                    </select>
                </div>

                <!-- Filter Status Keep -->
                <div>
                    <select id="csFilterStatus" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none transition"
                        onchange="applyCustomerSegmentationFilters()">
                        <option value="ALL">Semua Status Keep</option>
                        <option value="WIN" selected>WIN / Closing Terjual Saja</option>
                        <option value="NEED_TO_CHECK">Need To Check</option>
                        <option value="ON_PROGRESS">On Progress</option>
                        <option value="LOSE">Lose / Batal</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 4. Visual Charts Row 1: Donut Kredit vs Non-Kredit & Horizontal Bar Top Item Terjual -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <!-- Chart 1: Donut Perbandingan Kredit vs Non-Kredit (5 Cols) -->
            <div class="lg:col-span-5 p-5 rounded-2xl border border-slate-200/80 bg-white shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-indigo-600"></span>
                            <h3 class="text-sm font-black text-slate-900 tracking-tight">Segmentasi Metode Pembayaran</h3>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600" id="csDonutBadge">Kredit vs Cash</span>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-500">Proporsi customer yang membeli secara kredit vs pembayaran tunai/non-kredit</p>
                </div>

                <div class="relative py-4 flex items-center justify-center min-h-[260px]">
                    <canvas id="csChartCreditDonut" height="240"></canvas>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-100 text-center">
                    <div class="p-2 rounded-xl bg-indigo-50/60 border border-indigo-100">
                        <div class="text-[10px] font-bold text-indigo-800 uppercase tracking-wider">Kredit</div>
                        <div class="text-base font-black text-indigo-900 mt-0.5" id="csDonutCreditCount">0 Unit</div>
                        <div class="text-[10px] font-bold text-indigo-600" id="csDonutCreditPct">0%</div>
                    </div>
                    <div class="p-2 rounded-xl bg-teal-50/60 border border-teal-100">
                        <div class="text-[10px] font-bold text-teal-800 uppercase tracking-wider">Non-Kredit / Cash</div>
                        <div class="text-base font-black text-teal-900 mt-0.5" id="csDonutNonCreditCount">0 Unit</div>
                        <div class="text-[10px] font-bold text-teal-600" id="csDonutNonCreditPct">0%</div>
                    </div>
                </div>
            </div>

            <!-- Chart 2: Top Item Terjual Based on Format Keep (7 Cols) -->
            <div class="lg:col-span-7 p-5 rounded-2xl border border-slate-200/80 bg-white shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-pink-600"></span>
                            <h3 class="text-sm font-black text-slate-900 tracking-tight">Top Item &amp; Produk Terjual (Format Keep)</h3>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="rounded-full bg-pink-50 px-2 py-0.5 text-[10px] font-bold text-pink-700">Top 10 Produk</span>
                            <button type="button" 
                                    id="btnToggleCsItemRegulation" 
                                    onclick="toggleCsItemRegulation()" 
                                    class="inline-flex items-center gap-1 rounded-full bg-indigo-50 border border-indigo-200/80 px-2.5 py-0.5 text-[10px] font-bold text-indigo-700 hover:bg-indigo-100 transition shadow-2xs cursor-pointer"
                                    title="Klik untuk beralih antara Regulasi Model Bersih atau Nama Varian Asli">
                                <span>✨</span>
                                <span id="csItemRegulationLabel">Model Ter-Regulasi (ON)</span>
                            </button>
                        </div>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-500">Produk yang paling banyak dibeli dan berhasil closing berdasarkan format keep customer</p>
                </div>

                <div class="relative py-2 min-h-[290px] flex items-center">
                    <canvas id="csChartTopItems" height="270"></canvas>
                </div>
            </div>
        </div>

        <!-- 5. Visual Charts Row 2: Perbandingan Kredit per Cabang & Provider Pembiayaan -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <!-- Chart 3: Kredit vs Non-Kredit per Cabang (8 Cols) -->
            <div class="lg:col-span-8 p-5 rounded-2xl border border-slate-200/80 bg-white shadow-2xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-amber-500"></span>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight">Persebaran Kredit vs Non-Kredit per Cabang Toko</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-600">Komparasi Cabang</span>
                </div>
                <div class="relative py-3 min-h-[280px]">
                    <canvas id="csChartBranchCredit" height="260"></canvas>
                </div>
            </div>

            <!-- Chart 4: Provider Leasing / Pembiayaan (4 Cols) -->
            <div class="lg:col-span-4 p-5 rounded-2xl border border-slate-200/80 bg-white shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-violet-600"></span>
                            <h3 class="text-sm font-black text-slate-900 tracking-tight">Mitra Pembiayaan / Leasing</h3>
                        </div>
                        <span class="rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-bold text-violet-700">Fintech</span>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-500">Penyedia kredit yang paling diminati customer (HCI, Kredivo, dll.)</p>
                </div>

                <div class="relative py-2 flex items-center justify-center min-h-[220px]">
                    <canvas id="csChartLeasing" height="210"></canvas>
                </div>

                <div id="csLeasingTopSummary" class="text-xs text-slate-600 pt-2 border-t border-slate-100 space-y-1"></div>
            </div>
        </div>

        <!-- 6. Tabel Rincian Data Segmentasi Customer -->
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-slate-50/60">
                <div class="flex items-center gap-2">
                    <svg class="size-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Daftar Transaksi Format Keep Customer</span>
                </div>
                <div class="text-xs font-semibold text-slate-500" id="csTableCountBadge">Menampilkan 0 Data</div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-600">
                            <th class="py-3 px-4 w-12 text-center font-bold text-[11px]">NO</th>
                            <th class="py-3 px-4 font-bold text-[11px]">CUSTOMER &amp; WA</th>
                            <th class="py-3 px-4 font-bold text-[11px]">BARANG KEEP</th>
                            <th class="py-3 px-4 font-bold text-[11px] text-center">CABANG</th>
                            <th class="py-3 px-4 font-bold text-[11px] text-center">METODE BAYAR</th>
                            <th class="py-3 px-4 font-bold text-[11px] text-center">STATUS KEEP</th>
                            <th class="py-3 px-4 font-bold text-[11px]">PROVIDER LEASING / CATATAN</th>
                            <th class="py-3 px-4 font-bold text-[11px]">PIC SOSMED</th>
                            <th class="py-3 px-4 font-bold text-[11px] text-center">TANGGAL</th>
                        </tr>
                    </thead>
                    <tbody id="csTableBody" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 text-xs font-medium">Memuat data analisis customer segmentation...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modern Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-t border-slate-100 bg-slate-50/60">
                <div id="csPaginationInfo" class="text-xs font-semibold text-slate-500">Menampilkan 0 dari 0 Transaksi</div>
                <div class="flex items-center gap-2">
                    <button class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs disabled:opacity-50 transition cursor-pointer" id="btnCsPrev" onclick="changeCsPage(-1)">&laquo; Sebelumnya</button>
                    <span id="csCurrentPageText" class="text-xs font-extrabold text-slate-700 px-2.5 bg-slate-100 rounded-lg py-1">Halaman 1 / 1</span>
                    <button class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs disabled:opacity-50 transition cursor-pointer" id="btnCsNext" onclick="changeCsPage(1)">Selanjutnya &raquo;</button>
                </div>
            </div>
        </div>
    </div>
</section>
