                <section id="tabContent5" class="tab-content {{ (int) ($activeTab ?? 1) === 5 ? 'active' : '' }}">
                    <div class="modern-module-container mb-24">
                        <!-- Top Action Bar -->
                        <div
                            style="padding: 12px 20px; background: #ffffff; border-bottom: 1.5px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span
                                    style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.15rem; color: #0f172a;">Audit
                                    Tracking AR</span>
                                <span style="font-size: 0.78rem; color: var(--text-muted);">(VLOOKUP & Report AR di
                                    Keep)</span>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                <button class="btn btn-outline btn-sm" onclick="claimAllFilteredMissingAR()"
                                    title="Klaim semua AR yang belum terinput ke Modul Klaim AR"
                                    style="border-color: #c8102e; color: #c8102e; font-weight: 700;">
                                    🏷️ Klaim Semua AR Belum Sosmed
                                </button>
                                <button class="btn btn-outline btn-sm" onclick="runARMissingSosmedAudit()">
                                    🔄 Refresh Audit
                                </button>
                                <div class="database-status-pill pill-active" id="arAuditStatusPill"
                                    style="padding: 4px 10px; font-size: 0.75rem;">
                                    <span>📁 Audit Siap</span>
                                </div>
                            </div>
                        </div>

                        <!-- 1. Top KPI Summary Strip (Persis Mockup Gambar 2) -->
                        <div class="modern-kpi-summary-strip">
                            <div class="modern-kpi-strip-card card-accent-yellow">
                                <div class="kpi-strip-label">TOTAL DOKUMEN AR</div>
                                <div class="kpi-strip-val" id="auditTotalCount">0</div>
                            </div>
                            <div class="modern-kpi-strip-card card-accent-green">
                                <div class="kpi-strip-label">SUDAH TERINPUT (HIJAU)</div>
                                <div class="kpi-strip-val" style="color: #10b981;" id="auditAllSosmedCount">0</div>
                            </div>
                            <div class="modern-kpi-strip-card card-accent-orange">
                                <div class="kpi-strip-label">TERINPUT SEBAGIAN (ORANGE)</div>
                                <div class="kpi-strip-val" style="color: #f59e0b;" id="auditPartialMissingCount">0</div>
                            </div>
                            <div class="modern-kpi-strip-card card-accent-red">
                                <div class="kpi-strip-label">TIDAK TERINPUT (MERAH)</div>
                                <div class="kpi-strip-val" style="color: #ef4444;" id="auditAllMissingCount">0</div>
                            </div>
                            <div class="modern-kpi-strip-card card-accent-crimson">
                                <div class="kpi-strip-label">POTENSI OMZET BELUM TERKLAIM</div>
                                <div class="kpi-strip-val" style="color: #e11d48;" id="auditTotalPotensiRp">Rp 0</div>
                            </div>
                        </div>

                        <!-- 2. Search & Filter Panel (Persis Mockup Gambar 2) -->
                        <div class="modern-filter-panel">
                            <div class="modern-search-row">
                                <div class="modern-search-box">
                                    <span class="search-icon">🔍</span>
                                    <input type="text" id="auditSearchInput" class="modern-search-input"
                                        placeholder="Cari nama, nomor WA, atau barang..." oninput="applyAuditFilters()">
                                    <span class="kbd-shortcut">⌘K</span>
                                </div>
                                <button type="button" class="btn-modern-filter" id="btnToggleAuditFilters"
                                    onclick="toggleAuditFiltersSection()">
                                    <span class="filter-icon">⚙️</span> Filter <span class="filter-badge-count"
                                        id="auditFilterActiveCount">4</span>
                                </button>
                            </div>

                            <div class="modern-filter-cards-grid" id="auditFilterSection">
                                <div class="modern-filter-card">
                                    <label class="modern-filter-label" for="auditFilterPeriode">PERIODE KEEP:</label>
                                    <select id="auditFilterPeriode" class="modern-filter-select"
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

                                <div class="modern-filter-card">
                                    <label class="modern-filter-label" for="auditFilterSosmed">KODE SOSMED
                                        (KEEP):</label>
                                    <select id="auditFilterSosmed" class="modern-filter-select"
                                        onchange="applyAuditFilters()">
                                        <option value="ALL">Semua Kode Sosmed</option>
                                    </select>
                                </div>

                                <div class="modern-filter-card">
                                    <label class="modern-filter-label" for="auditFilterTanggalVlookup">TANGGAL FAKTUR AR
                                        (VLOOKUP):</label>
                                    <input type="date" id="auditFilterTanggalVlookup" class="modern-filter-input"
                                        onchange="applyAuditFilters()">
                                </div>

                                <div class="modern-filter-card">
                                    <label class="modern-filter-label" for="auditFilterTanggalReport">TANGGAL FAKTUR
                                        REPORT AR:</label>
                                    <input type="date" id="auditFilterTanggalReport" class="modern-filter-input"
                                        onchange="applyAuditFilters()">
                                </div>

                                <div class="modern-filter-card">
                                    <label class="modern-filter-label" for="auditFilterStatus">STATUS AR:</label>
                                    <select id="auditFilterStatus" class="modern-filter-select"
                                        onchange="applyAuditFilters()">
                                        <option value="ALL">Semua Status (Lengkap, Sebagian)</option>
                                        <option value="ALL_SOSMED">✅ Sudah Terinput Semua (Hijau)</option>
                                        <option value="PARTIAL_MISSING">⚠️ Terinput Sebagian (Orange)</option>
                                        <option value="ALL_MISSING">❌ Tidak Terinput Sama Sekali (Merah)</option>
                                        <option value="ANY_MISSING">🚨 Belum & Sebagian Terinput</option>
                                    </select>
                                </div>

                                <!-- Atur Kolom Dropdown -->
                                <div class="modern-filter-card" style="position: relative;">
                                    <label class="modern-filter-label">KOLOM:</label>
                                    <button type="button" class="btn-modern-col-toggle" id="btnToggleAuditCols"
                                        onclick="toggleAuditColDropdown(event)">
                                        ☷ Atur Kolom ▾
                                    </button>
                                    <div id="auditColDropdownMenu" class="audit-col-dropdown-menu"
                                        style="display: none;">
                                        <div class="col-menu-header">
                                            <span>Atur Tampilan Kolom</span>
                                            <button type="button" class="btn-xs-link"
                                                onclick="resetAuditColumns()">Tampilkan Semua</button>
                                        </div>
                                        <div class="col-menu-list">
                                            <label><input type="checkbox" data-col="audit-col-no" checked
                                                    onchange="toggleAuditColumn('audit-col-no', this.checked)">
                                                No</label>
                                            <label><input type="checkbox" data-col="audit-col-keep-date" checked
                                                    onchange="toggleAuditColumn('audit-col-keep-date', this.checked)">
                                                Tanggal Format Keep</label>
                                            <label><input type="checkbox" data-col="audit-col-cust" checked
                                                    onchange="toggleAuditColumn('audit-col-cust', this.checked)">
                                                Customer</label>
                                            <label><input type="checkbox" data-col="audit-col-wa" checked
                                                    onchange="toggleAuditColumn('audit-col-wa', this.checked)"> Nomor
                                                WA</label>
                                            <label><input type="checkbox" data-col="audit-col-sosmed" checked
                                                    onchange="toggleAuditColumn('audit-col-sosmed', this.checked)"> Kode
                                                Sosmed</label>
                                            <label><input type="checkbox" data-col="audit-col-keep" checked
                                                    onchange="toggleAuditColumn('audit-col-keep', this.checked)"> Barang
                                                Keep</label>
                                            <label><input type="checkbox" data-col="audit-col-notes" checked
                                                    onchange="toggleAuditColumn('audit-col-notes', this.checked)">
                                                Catatan</label>
                                            <label><input type="checkbox" data-col="audit-col-status-keep" checked
                                                    onchange="toggleAuditColumn('audit-col-status-keep', this.checked)">
                                                Status Keep</label>
                                            <label><input type="checkbox" data-col="audit-col-report-ar" checked
                                                    onchange="toggleAuditColumn('audit-col-report-ar', this.checked)">
                                                Report AR dan Produk</label>
                                            <label><input type="checkbox" data-col="audit-col-report-date" checked
                                                    onchange="toggleAuditColumn('audit-col-report-date', this.checked)">
                                                Tanggal Report AR</label>
                                            <label><input type="checkbox" data-col="audit-col-vlookup-ar" checked
                                                    onchange="toggleAuditColumn('audit-col-vlookup-ar', this.checked)">
                                                AR VLOOKUP</label>
                                            <label><input type="checkbox" data-col="audit-col-vlookup-date" checked
                                                    onchange="toggleAuditColumn('audit-col-vlookup-date', this.checked)">
                                                Tanggal No AR VLOOKUP</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="modern-filter-card-action">
                                    <button type="button" class="btn-modern-reset" onclick="resetAuditFilters()"
                                        title="Reset semua filter">
                                        <span class="reset-icon">🔄</span> Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Table Container: Linenya Tegas & Header Kuning Solid (Persis Gambar 2) -->
                        <div class="modern-table-wrapper">
                            <table class="modern-tegas-table" id="auditTable">
                                <thead>
                                    <tr>
                                        <th class="audit-col-no" style="text-align: center; width: 3%;">NO</th>
                                        <th class="audit-col-keep-date" style="text-align: center; width: 7.5%;">TANGGAL
                                            FORMAT KEEP</th>
                                        <th class="audit-col-cust" style="text-align: left; width: 10%;">CUSTOMER</th>
                                        <th class="audit-col-sosmed" style="text-align: center; width: 9%;">KODE SOSMED
                                            (KEEP)</th>
                                        <th class="audit-col-keep" style="text-align: left; width: 14%;">BARANG KEEP
                                        </th>
                                        <th class="audit-col-notes" style="text-align: left; width: 14%;">CATATAN</th>
                                        <th class="audit-col-status-keep" style="text-align: center; width: 9.5%;">
                                            STATUS KEEP</th>
                                        <th class="audit-col-report-ar" style="text-align: center; width: 8.5%;">REPORT
                                            AR DARI PRODUK</th>
                                        <th class="audit-col-report-date" style="text-align: center; width: 7.5%;">
                                            TANGGAL REPORT AR</th>
                                        <th class="audit-col-vlookup-ar" style="text-align: center; width: 8.5%;">AR
                                            VLOOKUP</th>
                                        <th class="audit-col-vlookup-date" style="text-align: center; width: 8.5%;">
                                            TANGGAL NO AR VLOOKUP</th>
                                    </tr>
                                </thead>
                                <tbody id="auditTableBody"></tbody>
                            </table>
                        </div>

                        <!-- 4. Modern Pagination Bar -->
                        <div class="modern-pagination-bar">
                            <div id="auditPaginationInfo" class="modern-pagination-info">Menampilkan 0 dari 0 Dokumen AR
                            </div>
                            <div class="modern-pagination-controls">
                                <button class="btn-page" id="btnAuditPrev" onclick="changeAuditPage(-1)">&laquo;
                                    Sebelumnya</button>
                                <span id="auditCurrentPageText" class="pagination-page-label">Halaman 1 / 1</span>
                                <button class="btn-page" id="btnAuditNext" onclick="changeAuditPage(1)">Selanjutnya
                                    &raquo;</button>
                            </div>
                        </div>
                    </div>
                </section>

