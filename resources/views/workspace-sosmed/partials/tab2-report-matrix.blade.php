                <section id="tabContent2" class="tab-content {{ (int) ($activeTab ?? 1) === 2 ? 'active' : '' }}">
                    <!-- Turnover Timestamp Indicator Banner in Tab 2 -->
                    <div class="turnover-report-banner" id="turnoverReportBanner">
                        <div class="banner-left">
                            <span class="banner-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                            </span>
                            <span id="turnoverBannerText" class="banner-text">Database Turnover Realtime (MySQL)</span>
                        </div>
                        <div class="banner-right">
                            <button type="button" class="btn-banner-action btn-target-action"
                                onclick="openMonthlyTargetModal()"
                                title="Atur target omzet tim dan sales untuk bulan ini">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <circle cx="12" cy="12" r="6"></circle>
                                    <circle cx="12" cy="12" r="2"></circle>
                                </svg>
                                <span>Target Omzet</span>
                            </button>
                            <button type="button" class="btn-banner-action btn-reset-action" id="btnResetTurnover"
                                onclick="resetTurnoverToDefault()"
                                title="Hapus cache file upload dan kembali ke data bawaan">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                    <path d="M3 3v5h5"></path>
                                </svg>
                                <span>Reset Data</span>
                            </button>
                        </div>
                    </div>

                    <!-- Section Report 1: Daily Achievement Matrix -->
                    <div id="report1ViewWrap">
                        <div class="card-tegas table-card report1-card-wrapper" id="report1Container">
                            <div class="table-toolbar report1-toolbar-themed">
                                <div class="toolbar-brand-section">
                                    <img src="{{ asset('workspace-sosmed/logo.png') }}" onerror="this.onerror=null; this.src='{{ asset('images/gadgetmart-logo.png') }}';" alt="GadgetMart" class="report-header-logo">
                                    <div class="toolbar-divider-v"></div>
                                    <div class="toolbar-title">
                                        <h2 id="report1MainTitle">REPORT 1: DAILY ACHIEVEMENT SOSMED OKTOBER 2026</h2>
                                        <span class="pill-badge-red" id="modeIndicatorTag">Nilai DPP (Sebelum PPN
                                            11%)</span>
                                    </div>
                                </div>
                                <div class="toolbar-controls">
                                    <div class="live-clock-badge" id="reportLiveClock"
                                        title="Waktu dan Tanggal Berjalan Realtime">
                                        <svg class="clock-svg-icon" width="15" height="15" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <span id="liveClockDate" class="clock-date-text">Memuat tanggal...</span>
                                        <span class="clock-dot-sep">•</span>
                                        <span id="liveClockTime" class="clock-time-val">00:00:00 WITA</span>
                                    </div>
                                    <button class="btn btn-sm btn-download-themed"
                                        data-html2canvas-ignore="true"
                                        onclick="downloadElementAsPNG('report1Container', 'Report1_Daily_Achievement_Sosmed')">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                            stroke-linejoin="round" style="margin-right:6px;">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                        Unduh Gambar Lengkap
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive report1-scroll-area" id="tableScrollArea">
                                <table class="kpi-matrix-table" id="matrixTable">
                                    <thead>
                                        <tr id="matrixHeaderRow">
                                            <th class="col-sticky col-date">TANGGAL</th>
                                            <th>SIFA</th>
                                            <th>DANIEL</th>
                                            <th>MARLINA</th>
                                            <th>BINTANG</th>
                                            <th>FEBRI</th>
                                            <th>DEA</th>
                                            <th>MARISA</th>
                                            <th>ZOFIA</th>
                                            <th>QONA</th>
                                            <th class="col-achieve">ACHIEVE DAY</th>
                                            <th class="col-week">WEEK</th>
                                            <th class="col-avg">AVG</th>
                                        </tr>
                                    </thead>
                                    <tbody id="matrixTableBody">
                                        <tr>
                                            <td colspan="13" class="text-center empty-state">Memuat data
                                                turnover.xlsx... Silakan tunggu sejenak atau pilih file.</td>
                                        </tr>
                                    </tbody>
                                    <tfoot id="matrixTableFoot"></tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section Report 2: KPI & GP Performance Summary -->
                    <div id="report2ViewWrap">
                        <div id="report2Container">
                            <!-- Table Atas: Omzet & Target GP 5% vs Timegone -->
                            <div class="card-tegas table-card mb-24">
                                <div class="table-toolbar toolbar-crimson">
                                    <div class="toolbar-title">
                                        <h2>REPORT 2: ACHIEVEMENT OMZET & GROSS PROFIT VS TIMEGONE</h2>
                                    </div>
                                    <div class="timegone-indicator-box">
                                        <span>TIMEGONE:</span>
                                        <strong class="timegone-tag" id="timegoneHeaderTag">42%</strong>
                                        <button class="btn btn-sm btn-crimson ml-10"
                                            data-html2canvas-ignore="true"
                                            onclick="downloadElementAsPNG('report2Container', 'Report2_KPI_Summary_Timegone')">📷
                                            Unduh Gambar Lengkap</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="kpi-matrix-table table-summary" id="kpiSummaryTable">
                                        <thead>
                                            <tr>
                                                <th class="text-left">NAMA</th>
                                                <th>TARGET</th>
                                                <th>PENCAPAIAN</th>
                                                <th>%</th>
                                                <th>STATUS</th>
                                                <th>TARGET GP (5%)</th>
                                                <th>ACV GP</th>
                                                <th>% GP</th>
                                                <th>STATUS GP</th>
                                            </tr>
                                        </thead>
                                        <tbody id="kpiSummaryBody"></tbody>
                                        <tfoot id="kpiSummaryFoot"></tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Table Bawah: Cross Selling & Nilai Akhir -->
                            <div class="card-tegas table-card">
                                <div class="table-toolbar">
                                    <div class="toolbar-title">
                                        <h2>EVALUASI CROSS SELLING (>50K) & NILAI AKHIR KPI</h2>
                                        <span class="table-meta-tag">Standar Rasio: 1 Cross per 3 Gadget (Max
                                            100%)</span>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="kpi-matrix-table table-summary" id="kpiCrossTable">
                                        <thead>
                                            <tr>
                                                <th class="text-left">NAMA</th>
                                                <th>JLH TRANSAKSI GADGET</th>
                                                <th>JLH CROSS >50K</th>
                                                <th>% CROSSELLING</th>
                                                <th>% AKHIR (RAPOR)</th>
                                                <th>STATUS AKHIR</th>
                                                <th>KOMISI</th>
                                            </tr>
                                        </thead>
                                        <tbody id="kpiCrossBody"></tbody>
                                        <tfoot id="kpiCrossFoot"></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

