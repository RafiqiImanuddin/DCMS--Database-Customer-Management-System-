                <section id="tabContent5" class="tab-content mb-16 {{ (int) ($activeTab ?? 1) === 5 ? 'active' : '' }}">
                    <div class="w-full space-y-5">
                        <!-- 1. Header: Selaras dengan Customer Outreach & Format Keep -->
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-500/10 px-2.5 py-0.5 text-xs font-bold text-brand-700">
                                        <span class="size-2 rounded-full bg-brand-500"></span>
                                        Tracking AR
                                    </span>
                                    <span class="text-xs text-slate-400">&bull;</span>
                                    <span class="text-xs font-medium text-slate-500">Audit Faktur AR & VLOOKUP Keep Sosmed</span>
                                </div>
                                <h1 class="mt-1 text-2xl font-black text-slate-900">Audit Tracking AR</h1>
                            </div>
                            <!-- Action Buttons -->
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50/80 px-3.5 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 shadow-xs transition" onclick="claimAllFilteredMissingAR()"
                                    title="Klaim semua AR yang belum terinput ke Modul Klaim AR">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
                                    <span>Klaim Semua AR Belum Sosmed</span>
                                </button>
                                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-xs transition" onclick="runARMissingSosmedAudit()">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    <span>Refresh Audit</span>
                                </button>
                                <div class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 shadow-xs" id="arAuditStatusPill">
                                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Audit Siap</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Metric Cards: Color Palette & Border-L Selaras Customer & Outreach -->
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                            <!-- Card 1: Total Dokumen AR -->
                            <div class="dcms-card p-4 border-l-4 border-l-brand-500 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span class="text-[11px] font-bold uppercase tracking-wider">Total Dokumen AR</span>
                                    <div class="grid size-7 place-items-center rounded-lg bg-brand-50 text-brand-600">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    </div>
                                </div>
                                <div class="mt-2 text-2xl font-black text-slate-900" id="auditTotalCount">0</div>
                                <div class="mt-0.5 text-[11px] text-slate-500">Total faktur terdeteksi</div>
                            </div>

                            <!-- Card 2: Sudah Terinput (Hijau) -->
                            <div class="dcms-card p-4 border-l-4 border-l-emerald-500 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Sudah Terinput</span>
                                    <div class="grid size-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    </div>
                                </div>
                                <div class="mt-2 text-2xl font-black text-emerald-700" id="auditAllSosmedCount">0</div>
                                <div class="mt-0.5 text-[11px] text-emerald-600 font-semibold">Lengkap terdata sosmed</div>
                            </div>

                            <!-- Card 3: Terinput Sebagian (Orange) -->
                            <div class="dcms-card p-4 border-l-4 border-l-amber-500 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Terinput Sebagian</span>
                                    <div class="grid size-7 place-items-center rounded-lg bg-amber-50 text-amber-600">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                    </div>
                                </div>
                                <div class="mt-2 text-2xl font-black text-amber-700" id="auditPartialMissingCount">0</div>
                                <div class="mt-0.5 text-[11px] text-amber-600 font-semibold">Sebagian produk missing</div>
                            </div>

                            <!-- Card 4: Tidak Terinput (Merah) -->
                            <div class="dcms-card p-4 border-l-4 border-l-rose-500 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Tidak Terinput</span>
                                    <div class="grid size-7 place-items-center rounded-lg bg-rose-50 text-rose-600">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    </div>
                                </div>
                                <div class="mt-2 text-2xl font-black text-rose-700" id="auditAllMissingCount">0</div>
                                <div class="mt-0.5 text-[11px] text-rose-600 font-semibold">Belum masuk kode sosmed</div>
                            </div>

                            <!-- Card 5: Potensi Omzet Belum Terklaim -->
                            <div class="dcms-card p-4 border-l-4 border-l-red-600 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition duration-150 select-none">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-red-700">Potensi Omzet</span>
                                    <div class="grid size-7 place-items-center rounded-lg bg-red-50 text-red-600">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    </div>
                                </div>
                                <div class="mt-2 text-2xl font-black text-red-600" id="auditTotalPotensiRp">Rp 0</div>
                                <div class="mt-0.5 text-[11px] text-red-500 font-semibold">Nilai belum terklaim</div>
                            </div>
                        </div>

                        <!-- 3. Search & Filter Bar: Modern White Card -->
                        <div class="dcms-card p-4 rounded-2xl border border-slate-200/80 bg-white shadow-xs space-y-3">
                            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="relative flex-1">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                    </span>
                                    <input type="text" id="auditSearchInput" class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pl-10 pr-12 text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-brand-500 focus:outline-none transition"
                                        placeholder="Cari nama customer, nomor WA, barang keep, atau no AR..." oninput="applyAuditFilters()">
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[10px] font-bold text-slate-400">⌘K</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition" id="btnToggleAuditFilters"
                                        onclick="toggleAuditFiltersSection()">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                                        <span>Filter Detail</span>
                                        <span class="size-4 rounded-full bg-brand-500 text-white text-[10px] flex items-center justify-center font-bold" id="auditFilterActiveCount">4</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2.5 pt-2 border-t border-slate-100" id="auditFilterSection">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1" for="auditFilterPeriode">Periode Keep</label>
                                    <select id="auditFilterPeriode" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none"
                                        onchange="applyAuditFilters()">
                                        <option value="ALL">Semua Periode</option>
                                        <option value="November-26" selected>November 2026 (Bulan ini)</option>
                                        <option value="Oktober-26">Oktober 2026</option>
                                        <option value="September-26">September 2026</option>
                                        <option value="Agustus-26">Agustus 2026</option>
                                        <option value="Juli-26">Juli 2026</option>
                                        <option value="Juni-26">Juni 2026</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1" for="auditFilterSosmed">Kode Sosmed</label>
                                    <select id="auditFilterSosmed" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none"
                                        onchange="applyAuditFilters()">
                                        <option value="ALL">Semua Kode Sosmed</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1" for="auditFilterTanggalVlookup">Tgl Faktur (VLOOKUP)</label>
                                    <input type="date" id="auditFilterTanggalVlookup" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none"
                                        onchange="applyAuditFilters()">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1" for="auditFilterTanggalReport">Tgl Report AR</label>
                                    <input type="date" id="auditFilterTanggalReport" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none"
                                        onchange="applyAuditFilters()">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1" for="auditFilterStatus">Status AR</label>
                                    <select id="auditFilterStatus" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none"
                                        onchange="applyAuditFilters()">
                                        <option value="ALL">Semua Status (Lengkap/Sebagian)</option>
                                        <option value="ALL_SOSMED">✅ Sudah Terinput Semua (Hijau)</option>
                                        <option value="PARTIAL_MISSING">⚠️ Terinput Sebagian (Orange)</option>
                                        <option value="ALL_MISSING">❌ Tidak Terinput (Merah)</option>
                                        <option value="ANY_MISSING">🚨 Belum & Sebagian Terinput</option>
                                    </select>
                                </div>

                                <div class="flex items-end gap-2">
                                    <div class="relative flex-1">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Kolom</label>
                                        <button type="button" class="w-full inline-flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100" id="btnToggleAuditCols"
                                            onclick="toggleAuditColDropdown(event)">
                                            <span>Atur Kolom</span>
                                            <span>▾</span>
                                        </button>
                                        <div id="auditColDropdownMenu" class="audit-col-dropdown-menu" style="display: none;">
                                            <div class="col-menu-header">
                                                <span>Atur Tampilan Kolom</span>
                                                <button type="button" class="btn-xs-link" onclick="resetAuditColumns()">Tampilkan Semua</button>
                                            </div>
                                            <div class="col-menu-list">
                                                <label><input type="checkbox" data-col="audit-col-no" checked onchange="toggleAuditColumn('audit-col-no', this.checked)"> No</label>
                                                <label><input type="checkbox" data-col="audit-col-keep-date" checked onchange="toggleAuditColumn('audit-col-keep-date', this.checked)"> Tanggal Format Keep</label>
                                                <label><input type="checkbox" data-col="audit-col-cust" checked onchange="toggleAuditColumn('audit-col-cust', this.checked)"> Customer</label>
                                                <label><input type="checkbox" data-col="audit-col-wa" checked onchange="toggleAuditColumn('audit-col-wa', this.checked)"> Nomor WA</label>
                                                <label><input type="checkbox" data-col="audit-col-sosmed" checked onchange="toggleAuditColumn('audit-col-sosmed', this.checked)"> Kode Sosmed</label>
                                                <label><input type="checkbox" data-col="audit-col-keep" checked onchange="toggleAuditColumn('audit-col-keep', this.checked)"> Barang Keep</label>
                                                <label><input type="checkbox" data-col="audit-col-notes" checked onchange="toggleAuditColumn('audit-col-notes', this.checked)"> Catatan</label>
                                                <label><input type="checkbox" data-col="audit-col-status-keep" checked onchange="toggleAuditColumn('audit-col-status-keep', this.checked)"> Status Keep</label>
                                                <label><input type="checkbox" data-col="audit-col-report-ar" checked onchange="toggleAuditColumn('audit-col-report-ar', this.checked)"> Report AR dan Produk</label>
                                                <label><input type="checkbox" data-col="audit-col-report-date" checked onchange="toggleAuditColumn('audit-col-report-date', this.checked)"> Tanggal Report AR</label>
                                                <label><input type="checkbox" data-col="audit-col-vlookup-ar" checked onchange="toggleAuditColumn('audit-col-vlookup-ar', this.checked)"> AR VLOOKUP</label>
                                                <label><input type="checkbox" data-col="audit-col-vlookup-date" checked onchange="toggleAuditColumn('audit-col-vlookup-date', this.checked)"> Tanggal No AR VLOOKUP</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100" onclick="resetAuditFilters()"
                                        title="Reset semua filter">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                        <span>Reset</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Table Container: Modern Tailwind Card Shell -->
                        <div class="dcms-card p-0 rounded-2xl border border-slate-200/80 bg-white shadow-xs overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="modern-tegas-table w-full text-left" id="auditTable">
                                    <thead>
                                        <tr class="bg-slate-50/90 border-b border-slate-200">
                                            <th class="audit-col-no" style="text-align: center; width: 3%;">NO</th>
                                            <th class="audit-col-keep-date" style="text-align: center; width: 7.5%;">TANGGAL FORMAT KEEP</th>
                                            <th class="audit-col-cust" style="text-align: left; width: 10%;">CUSTOMER</th>
                                            <th class="audit-col-sosmed" style="text-align: center; width: 9%;">KODE SOSMED (KEEP)</th>
                                            <th class="audit-col-keep" style="text-align: left; width: 14%;">BARANG KEEP</th>
                                            <th class="audit-col-notes" style="text-align: left; width: 14%;">CATATAN</th>
                                            <th class="audit-col-status-keep" style="text-align: center; width: 9.5%;">STATUS KEEP</th>
                                            <th class="audit-col-report-ar" style="text-align: center; width: 8.5%;">REPORT AR DARI PRODUK</th>
                                            <th class="audit-col-report-date" style="text-align: center; width: 7.5%;">TANGGAL REPORT AR</th>
                                            <th class="audit-col-vlookup-ar" style="text-align: center; width: 8.5%;">AR VLOOKUP</th>
                                            <th class="audit-col-vlookup-date" style="text-align: center; width: 8.5%;">TANGGAL NO AR VLOOKUP</th>
                                        </tr>
                                    </thead>
                                    <tbody id="auditTableBody" class="divide-y divide-slate-100"></tbody>
                                </table>
                            </div>

                            <!-- Modern Pagination Bar -->
                            <div class="flex items-center justify-between p-4 border-t border-slate-100 bg-slate-50/50">
                                <div id="auditPaginationInfo" class="text-xs font-semibold text-slate-500">Menampilkan 0 dari 0 Dokumen AR</div>
                                <div class="flex items-center gap-2">
                                    <button class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs disabled:opacity-50" id="btnAuditPrev" onclick="changeAuditPage(-1)">&laquo; Sebelumnya</button>
                                    <span id="auditCurrentPageText" class="text-xs font-bold text-slate-600 px-2">Halaman 1 / 1</span>
                                    <button class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs disabled:opacity-50" id="btnAuditNext" onclick="changeAuditPage(1)">Selanjutnya &raquo;</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>