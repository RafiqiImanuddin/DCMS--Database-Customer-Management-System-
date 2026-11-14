                <section id="tabContent6" class="tab-content {{ (int) ($activeTab ?? 1) === 6 ? 'active' : '' }}">
                    <div class="modern-module-container mb-24">
                        <!-- 1. Hero Yellow Banner (Persis Format Keep) -->
                        <div class="customer-hero-banner">
                            <div class="customer-hero-content">
                                <h2 class="customer-hero-title">Klaim AR &amp; Pengawasan Denda</h2>
                                <p class="customer-hero-sub" id="claimsHeroSub">Pengawasan faktur AR belum sosmed,
                                    siklus batch 3 hari, masa tenggang H+1, dan eskalasi denda cabang</p>
                            </div>
                            <div class="customer-hero-right">
                                <button type="button" class="btn-sync-live-subtle" id="btnDownloadSingleExcel"
                                    onclick="downloadARClaimsSingleExcel()"
                                    title="Unduh seluruh data klaim AR ke dalam SATU file Excel (.xlsx) gabungan, lengkap dengan bukti gambar format keep"
                                    style="background: #0f766e; border-color: #14b8a6; color: #ffffff;">
                                    📊 Unduh Jadi 1 Excel
                                </button>
                                <button type="button" class="btn-sync-live-subtle" id="btnShareBatchDay4"
                                    onclick="shareARClaimsBatchDay4()"
                                    title="Unduh paket file Excel per cabang (.zip) atau 1 file gabungan dan pindahkan ke antrean 'Cek AR Setelah di Share'">
                                    📦 Unduh Paket Cabang &amp; Share
                                </button>
                                <button type="button" class="btn-sync-live-subtle"
                                    onclick="checkARClaimsAutoResolution(true)"
                                    title="Cek apakah ada AR yang sudah direvisi cabang di transaksi SAP atau sudah lewat batas H+1">
                                    Cek Status Revisi &amp; H+1
                                </button>
                                <button type="button" class="btn-sync-live-subtle" onclick="exportClaimsPenaltyReport()"
                                    title="Salin atau unduh rekap denda kasir per cabang untuk diserahkan ke Manajemen/HRD">
                                    Rekap Denda Cabang
                                </button>
                                <div class="customer-hero-badge" id="claimCycleBadge">
                                    <span class="live-dot-pulse"></span>
                                    <span>Siklus 3 Hari</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. KPI Summary Strip (Modern Tone Step) -->
                        <div class="modern-kpi-summary-strip">
                            <div class="modern-kpi-strip-card card-accent-yellow" style="cursor: pointer;"
                                onclick="setClaimsSubTab('COLLECTING')" id="cardStatCollecting"
                                title="Klik untuk filter Batch Pengumpulan">
                                <div class="kpi-strip-label">1. Pengumpulan (3 Hari)</div>
                                <div class="kpi-strip-val" id="claimCountCollecting">0</div>
                                <div style="font-size: 0.72rem; color: #64748b; margin-top: 4px; font-weight: 600;">
                                    Menunggu Hari ke-4</div>
                            </div>
                            <div class="modern-kpi-strip-card card-accent-orange" style="cursor: pointer;"
                                onclick="setClaimsSubTab('SHARED')" id="cardStatShared"
                                title="Klik untuk filter Masa Tenggang">
                                <div class="kpi-strip-label">2. Setelah di Share (H+1)</div>
                                <div class="kpi-strip-val" style="color: #b45309;" id="claimCountShared">0</div>
                                <div style="font-size: 0.72rem; color: #64748b; margin-top: 4px; font-weight: 600;">Masa
                                    Tenggang Cabang</div>
                            </div>
                            <div class="modern-kpi-strip-card card-accent-crimson" style="cursor: pointer;"
                                onclick="setClaimsSubTab('PENALTY')" id="cardStatPenalty"
                                title="Klik untuk filter Denda Real">
                                <div class="kpi-strip-label">3. Denda Sungguhan</div>
                                <div class="kpi-strip-val" style="color: #e11d48;" id="claimCountPenalty">0</div>
                                <div style="font-size: 0.72rem; color: #be123c; margin-top: 4px; font-weight: 700;"
                                    id="claimTotalPenaltyRp">Rp 0 (Lewat H+1)</div>
                            </div>
                            <div class="modern-kpi-strip-card card-accent-green" style="cursor: pointer;"
                                onclick="setClaimsSubTab('RESOLVED')" id="cardStatResolved"
                                title="Klik untuk filter Selesai Direvisi">
                                <div class="kpi-strip-label">4. Selesai Direvisi</div>
                                <div class="kpi-strip-val" style="color: #15803d;" id="claimCountResolved">0</div>
                                <div style="font-size: 0.72rem; color: #166534; margin-top: 4px; font-weight: 600;">
                                    Sudah Jadi BJM.SOS</div>
                            </div>
                            <div class="modern-kpi-strip-card" style="cursor: pointer;"
                                onclick="setClaimsSubTab('INVALID')" id="cardStatInvalid"
                                title="Klik untuk filter Tidak Valid">
                                <div class="kpi-strip-label">5. Tidak Valid</div>
                                <div class="kpi-strip-val" style="color: #64748b;" id="claimCountInvalid">0</div>
                                <div style="font-size: 0.72rem; color: #64748b; margin-top: 4px; font-weight: 600;">Item
                                    Dianulir / Dibatalkan</div>
                            </div>
                        </div>

                        <!-- 3. Sub-Tab Navigation Bar (Anti-Slop Clean Typography) -->
                        <div class="claims-subtabs-nav">
                            <button type="button" class="btn-subtab active" id="subtabBtnCollecting"
                                onclick="setClaimsSubTab('COLLECTING')">
                                1. Pengumpulan AR (Batch 3 Hari) <span class="subtab-badge"
                                    id="badgeSubtabCollecting">0</span>
                            </button>
                            <button type="button" class="btn-subtab" id="subtabBtnShared"
                                onclick="setClaimsSubTab('SHARED')">
                                2. Cek AR Setelah di Share (Tenggat H+1) <span class="subtab-badge"
                                    id="badgeSubtabShared">0</span>
                            </button>
                            <button type="button" class="btn-subtab" id="subtabBtnPenalty"
                                onclick="setClaimsSubTab('PENALTY')">
                                3. Denda Sungguhan &amp; Input Mandiri <span class="subtab-badge badge-red"
                                    id="badgeSubtabPenalty">0</span>
                            </button>
                            <button type="button" class="btn-subtab" id="subtabBtnResolved"
                                onclick="setClaimsSubTab('RESOLVED')">
                                4. Selesai Direvisi Cabang <span class="subtab-badge badge-green"
                                    id="badgeSubtabResolved">0</span>
                            </button>
                            <button type="button" class="btn-subtab" id="subtabBtnInvalid"
                                onclick="setClaimsSubTab('INVALID')">
                                5. Tidak Valid <span class="subtab-badge badge-gray" id="badgeSubtabInvalid">0</span>
                            </button>
                        </div>

                        <!-- 4. Modern Search & Filter Panel (Persis Format Keep) -->
                        <div class="modern-filter-panel">
                            <div class="modern-search-row">
                                <div class="modern-search-box">
                                    <span class="search-icon">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                        </svg>
                                    </span>
                                    <input type="text" id="claimsSearchInput" class="modern-search-input"
                                        placeholder="Cari No. AR, Customer, No. WA, Cabang, Barang, Sales..."
                                        oninput="applyClaimsFilters()">
                                    <span class="kbd-shortcut">&#8984;K</span>
                                </div>
                                <button type="button" class="btn-modern-filter" id="btnToggleClaimsFilters"
                                    onclick="toggleClaimsFiltersSection()">
                                    <span class="filter-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                        </svg>
                                    </span>
                                    Filter <span class="filter-badge-count" id="claimsFilterActiveCount">0</span>
                                </button>
                            </div>

                            <div class="modern-filter-cards-grid" id="claimsFilterSection">
                                <div class="modern-filter-card">
                                    <label class="modern-filter-label" for="claimsFilterCabang">CABANG TOKO</label>
                                    <select id="claimsFilterCabang" class="modern-filter-select"
                                        onchange="applyClaimsFilters()">
                                        <option value="ALL">Semua Cabang Toko</option>
                                    </select>
                                </div>

                                <div class="modern-filter-card">
                                    <label class="modern-filter-label" for="claimsFilterSosmed">TARGET SOSMED</label>
                                    <select id="claimsFilterSosmed" class="modern-filter-select"
                                        onchange="applyClaimsFilters()">
                                        <option value="ALL">Semua Kode Sosmed</option>
                                    </select>
                                </div>

                                <div class="modern-filter-card">
                                    <label class="modern-filter-label" for="claimsFilterTanggal">TANGGAL MASUK /
                                        SHARE</label>
                                    <input type="date" id="claimsFilterTanggal" class="modern-filter-input"
                                        onchange="applyClaimsFilters()" title="Filter tanggal">
                                </div>

                                <div class="modern-filter-card-action">
                                    <button type="button" class="btn-modern-reset" onclick="resetClaimsFilters()"
                                        title="Reset semua filter">
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Table Container: Yellow Header Solid & Firm Lines (Persis Format Keep) -->
                        <div class="modern-table-wrapper">
                            <table class="modern-tegas-table" id="claimsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 15%; text-align: left;">CUSTOMER</th>
                                        <th style="width: 7.5%; text-align: center;">TANGGAL</th>
                                        <th style="width: 8%; text-align: center;">NO. AR</th>
                                        <th style="width: 13%; text-align: left;">BARANG</th>
                                        <th style="width: 8%; text-align: center;">KODE SOSMED</th>
                                        <th style="width: 6.5%; text-align: center;">CABANG</th>
                                        <th style="width: 8.5%; text-align: center;">DENDA</th>
                                        <th style="width: 10%; text-align: left;">FORMAT KEEP</th>
                                        <th style="width: 7%; text-align: center;">EVIDENCE</th>
                                        <th style="width: 6.5%; text-align: left;">AR SALES</th>
                                        <th style="width: 6.5%; text-align: center;">VALIDASI</th>
                                        <th style="width: 7.5%; text-align: left;">ALASAN</th>
                                        <th style="width: 5%; text-align: center;">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody id="claimsTableBody"></tbody>
                            </table>
                        </div>

                        <!-- 6. Modern Pagination Bar (Persis Format Keep) -->
                        <div class="modern-pagination-bar">
                            <div id="claimsPaginationInfo" class="modern-pagination-info">Menampilkan 0 dari 0 Data
                                Klaim</div>
                            <div class="modern-pagination-controls">
                                <button class="btn-page" id="btnClaimsPrev" onclick="changeClaimsPage(-1)">&laquo;
                                    Sebelumnya</button>
                                <span id="claimsCurrentPageText" class="pagination-page-label">Halaman 1 / 1</span>
                                <button class="btn-page" id="btnClaimsNext" onclick="changeClaimsPage(1)">Selanjutnya
                                    &raquo;</button>
                            </div>
                        </div>
                    </div>
                </section>

