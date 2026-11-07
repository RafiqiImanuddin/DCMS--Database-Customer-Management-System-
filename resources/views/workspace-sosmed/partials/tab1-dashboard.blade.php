                <section id="tabContent1" class="tab-content active">
                    <!-- Interactive Dashboard Filter Bar (Dihapus dari Tampilan Sesuai Permintaan User) -->
                    <div class="card-tegas dashboard-filter-bar mb-20" id="dashFilterBar"
                        style="display: none !important;">
                        <div class="filter-bar-title">
                            <span class="icon-filter">⚡</span>
                            <div>
                                <h4>Filter Interaktif Dashboard</h4>
                                <p>Sesuaikan tampilan data revenue, gross profit, dan grafik pencapaian</p>
                            </div>
                        </div>
                        <div class="filter-controls-inline">
                            <div class="filter-inline-item">
                                <label for="dashFilterMonth">Bulan / Periode:</label>
                                <select id="dashFilterMonth" class="form-input-sm" onchange="applyDashboardFilters()">
                                    <option value="12">Desember 2026</option>
                                    <option value="11" selected>November 2026 (Bulan Berjalan)</option>
                                    <option value="10">Oktober 2026</option>
                                    <option value="9">September 2026</option>
                                    <option value="8">Agustus 2026</option>
                                    <option value="7">Juli 2026</option>
                                    <option value="6">Juni 2026</option>
                                    <option value="5">Mei 2026</option>
                                    <option value="4">April 2026</option>
                                    <option value="3">Maret 2026</option>
                                    <option value="2">Februari 2026</option>
                                    <option value="1">Januari 2026</option>
                                    <option value="all">Sepanjang Tahun 2026 (All)</option>
                                </select>
                            </div>
                            <div class="filter-inline-item">
                                <label for="dashFilterCabang">Cabang Penjualan:</label>
                                <select id="dashFilterCabang" class="form-input-sm" onchange="applyDashboardFilters()">
                                    <option value="all">Semua Cabang Toko</option>
                                </select>
                            </div>
                            <div class="filter-inline-item">
                                <label for="dashFilterSales">Sales PIC:</label>
                                <select id="dashFilterSales" class="form-input-sm" onchange="applyDashboardFilters()">
                                    <option value="all">Semua Sales Sosmed</option>
                                </select>
                            </div>
                            <div class="filter-inline-item">
                                <label>Basis Nilai:</label>
                                <div class="filter-switch-btn-group">
                                    <button class="btn-filter-toggle active" id="dashFilterBtnDPP"
                                        onclick="setDashCalculationMode('DPP')">DPP</button>
                                    <button class="btn-filter-toggle" id="dashFilterBtnGross"
                                        onclick="setDashCalculationMode('GROSS')">Gross</button>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline"
                                style="border-radius:999px; height:34px; margin-top:auto;"
                                onclick="resetDashboardFilters()">🔄 Reset</button>
                        </div>
                    </div>


                    <!-- 1. Executive Speedometer Gauges (Referensi BaHunda Dashboard) -->
                    <div class="dashboard-gauges-grid mb-24">
                        <!-- Gauge 1: Omzet vs Target -->
                        <div class="card-tegas gauge-card">
                            <div class="gauge-card-header">
                                <span class="gauge-title">ACHIEVEMENT OMZET (M-T-D)</span>
                                <span class="kpi-pill-badge badge-crimson">DPP Engine</span>
                            </div>
                            <div class="gauge-wrapper">
                                <canvas id="gaugeOmzetCanvas" width="220" height="120"></canvas>
                                <div class="gauge-center-text">
                                    <span class="gauge-pct" id="gaugeOmzetPct">0%</span>
                                </div>
                            </div>
                            <div class="gauge-footer-meta">
                                <div class="gauge-meta-row">
                                    <span>Pencapaian:</span>
                                    <strong class="text-crimson" id="gaugeOmzetVal">Rp 0</strong>
                                </div>
                                <div class="gauge-meta-row">
                                    <span>Target:</span>
                                    <span id="gaugeOmzetTarget">Rp 8.447.356.032</span>
                                </div>
                            </div>
                        </div>

                        <!-- Gauge 2: Gross Profit vs Target 5% -->
                        <div class="card-tegas gauge-card">
                            <div class="gauge-card-header">
                                <span class="gauge-title">GROSS PROFIT (GP)</span>
                                <span class="kpi-pill-badge badge-green">Target 5%</span>
                            </div>
                            <div class="gauge-wrapper">
                                <canvas id="gaugeGPCanvas" width="220" height="120"></canvas>
                                <div class="gauge-center-text">
                                    <span class="gauge-pct text-green" id="gaugeGPPct">0%</span>
                                </div>
                            </div>
                            <div class="gauge-footer-meta">
                                <div class="gauge-meta-row">
                                    <span>Realisasi GP:</span>
                                    <strong class="text-green" id="gaugeGPVal">Rp 0</strong>
                                </div>
                                <div class="gauge-meta-row">
                                    <span>Target GP (5%):</span>
                                    <span id="gaugeGPTarget">Rp 422.367.801</span>
                                </div>
                            </div>
                        </div>

                        <!-- Gauge 3: Cross Selling Gadget Ratio -->
                        <div class="card-tegas gauge-card">
                            <div class="gauge-card-header">
                                <span class="gauge-title">CROSS SELLING GADGET</span>
                                <span class="kpi-pill-badge badge-gold">Target 1 : 3</span>
                            </div>
                            <div class="gauge-wrapper">
                                <canvas id="gaugeCrossCanvas" width="220" height="120"></canvas>
                                <div class="gauge-center-text">
                                    <span class="gauge-pct text-gold" id="gaugeCrossPct">0%</span>
                                </div>
                            </div>
                            <div class="gauge-footer-meta">
                                <div class="gauge-meta-row">
                                    <span>Item Cross (>50K):</span>
                                    <strong class="text-gold" id="gaugeCrossItems">0 item</strong>
                                </div>
                                <div class="gauge-meta-row">
                                    <span>Total Gadget:</span>
                                    <span id="gaugeCrossGadget">0 unit</span>
                                </div>
                            </div>
                        </div>

                        <!-- Gauge 4: Timegone Bulan Ini -->
                        <div class="card-tegas gauge-card">
                            <div class="gauge-card-header">
                                <span class="gauge-title">TIMEGONE BULAN BERJALAN</span>
                                <span class="kpi-pill-badge badge-dark" id="gaugeTimegoneDay">Hari ke-13 / 31</span>
                            </div>
                            <div class="gauge-wrapper">
                                <canvas id="gaugeTimegoneCanvas" width="220" height="120"></canvas>
                                <div class="gauge-center-text">
                                    <span class="gauge-pct" id="gaugeTimegonePct">42%</span>
                                </div>
                            </div>
                            <div class="gauge-footer-meta">
                                <div class="gauge-meta-row">
                                    <span>Status Tim:</span>
                                    <strong id="gaugeTimegoneStatus" class="status-icon-lg">🖤 On Track</strong>
                                </div>
                                <div class="gauge-meta-row">
                                    <span>Kondisi:</span>
                                    <span id="gaugeTimegoneNote">Sesuai timeline bulan</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Interactive Charts (Combo Charts + TOP 3 Cabang + MoM Growth) -->
                    <div class="charts-section mb-24">
                        <!-- Row 1: Realisasi Omzet & Gross Profit -->
                        <div class="charts-grid-2col">
                            <!-- Chart 1: Realisasi Omzet vs Target Bulanan -->
                            <div class="card-tegas chart-card">
                                <div class="chart-card-header">
                                    <div class="chart-header-title">
                                        <h3>📈 Trend Realisasi Omzet & Target Bulanan</h3>
                                        <p>Perbandingan Pencapaian Omzet vs Target dari bulan ke bulan (Januari -
                                            Desember 2026)</p>
                                    </div>
                                </div>
                                <div class="chart-canvas-container">
                                    <canvas id="chartTrendOmzet"></canvas>
                                </div>
                            </div>

                            <!-- Chart 2: Gross Profit (GP) Bulanan -->
                            <div class="card-tegas chart-card">
                                <div class="chart-card-header">
                                    <div class="chart-header-title">
                                        <h3>💹 Trend Gross Profit (GP) vs Target 5%</h3>
                                        <p>Pergerakan Gross Profit tim sosmed dibandingkan standar target profit 5%
                                            bulanan</p>
                                    </div>
                                </div>
                                <div class="chart-canvas-container">
                                    <canvas id="chartTrendGP"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: TOP 3 Cabang Penjualan Terbanyak & MoM Growth -->
                        <div class="charts-grid-2col mt-24">
                            <!-- Chart 4: Penjualan Divisi Sosmed di Pontianak, Balikpapan & Sungai Danau -->
                            <div class="card-tegas chart-card">
                                <div class="chart-card-header">
                                    <div class="chart-header-title">
                                        <h3>📍 Penjualan Divisi Sosmed di Pontianak, Balikpapan & Sungai Danau</h3>
                                        <p>Perbandingan penjualan sosmed (Acuan Kode Sales: BJM.SOS.0 & BJM.SOS.K)</p>
                                    </div>
                                </div>
                                <div class="regional-stats-chips">
                                    <div class="regional-chip chip-ptk">
                                        <span class="chip-dot"></span>
                                        <div class="chip-info">
                                            <span class="chip-name">Pontianak (PTKC)</span>
                                            <strong class="chip-val" id="chipValPtk">0 Penjualan (Rp 0)</strong>
                                        </div>
                                    </div>
                                    <div class="regional-chip chip-bpp">
                                        <span class="chip-dot"></span>
                                        <div class="chip-info">
                                            <span class="chip-name">Balikpapan (BPPC/BP2C)</span>
                                            <strong class="chip-val" id="chipValBpp">0 Penjualan (Rp 0)</strong>
                                        </div>
                                    </div>
                                    <div class="regional-chip chip-sdn">
                                        <span class="chip-dot"></span>
                                        <div class="chip-info">
                                            <span class="chip-name">Sungai Danau (SDNC)</span>
                                            <strong class="chip-val" id="chipValSdn">0 Penjualan (Rp 0)</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="chart-canvas-container" style="height: 250px;">
                                    <canvas id="chartRegionalSosmed"></canvas>
                                </div>
                            </div>

                            <!-- Chart 5: Pertumbuhan Bulan ke Bulan (MoM % Growth) -->
                            <div class="card-tegas chart-card">
                                <div class="chart-card-header">
                                    <div class="chart-header-title">
                                        <h3>📊 Laju Pertumbuhan Bulan ke Bulan (MoM Growth %)</h3>
                                        <p>Persentase kenaikan/penurunan omzet dan profit dibandingkan bulan sebelumnya
                                        </p>
                                    </div>
                                </div>
                                <div class="chart-canvas-container">
                                    <canvas id="chartMoMGrowth"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Trend Cross Selling Bulanan -->
                        <div class="card-tegas chart-card mt-24">
                            <div class="chart-card-header">
                                <div class="chart-header-title">
                                    <h3>📱 Trend Cross Selling (>50K) Bulanan</h3>
                                    <p>Jumlah aksesoris & cross selling dibandingkan total unit gadget terjual per bulan
                                    </p>
                                </div>
                            </div>
                            <div class="chart-canvas-container" style="height: 300px;">
                                <canvas id="chartTrendCross"></canvas>
                            </div>
                        </div>

                        <!-- Table: Ringkasan Evaluasi Bulan ke Bulan (Januari - Desember) - DIBIKIN LEBAR 100% -->
                        <div class="card-tegas table-card mt-24">
                            <div class="table-toolbar">
                                <div class="toolbar-title">
                                    <h2>📑 RINGKASAN EVALUASI PENCAPAIAN BULAN KE BULAN (2026)</h2>
                                    <span class="pill-badge-red">Data Konsolidasi Tahunan Bersumber dari Arsip
                                        Bulanan</span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="kpi-matrix-table" id="monthSummaryTable" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th class="text-left" style="min-width: 160px; width: 180px;">BULAN</th>
                                            <th style="min-width: 160px;">TARGET OMZET</th>
                                            <th style="min-width: 160px;">PENCAPAIAN</th>
                                            <th class="text-center" style="min-width: 100px;">% OMZET</th>
                                            <th style="min-width: 150px;">TARGET GP (5%)</th>
                                            <th style="min-width: 150px;">ACV GP</th>
                                            <th class="text-center" style="min-width: 100px;">% GP</th>
                                            <th class="text-center" style="min-width: 150px;">STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monthSummaryTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Sales Leaderboard (Hanya untuk OMZET Sesuai Permintaan User) -->
                    <div class="card-tegas leaderboard-container-card mb-28" id="leaderboardSection">
                        <div class="leaderboard-header-bar">
                            <div class="leaderboard-title-group">
                                <span class="leaderboard-eyebrow">DIVISI SOSMED GADGETMART</span>
                                <h2 id="leaderboardTitleText">🏆 SALES LEADERBOARD &bull; REALISASI OMZET BULAN BERJALAN
                                </h2>
                                <p id="leaderboardSubText">Peringkat performa sales sosmed berdasarkan kontribusi
                                    realisasi omzet tertinggi.</p>
                            </div>
                        </div>

                        <!-- 10 Leaderboard Cards Grid (Image 3 Match) -->
                        <div class="sales-leaderboard-grid" id="salesLeaderboardGrid">
                            <!-- Populated dynamically via renderSalesLeaderboard() -->
                        </div>
                    </div>
                </section>

