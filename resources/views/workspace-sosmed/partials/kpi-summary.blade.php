                <div id="saveToast" class="save-toast" style="display:none"></div>

                <!-- KPI Executive Summary Metrics (Dihapus dari Tampilan Sesuai Permintaan User) -->
                <section class="kpi-grid-container" id="kpiGridSection" style="display: none !important;">
                    <div class="kpi-grid">
                        <div class="card-tegas kpi-card">
                            <div class="kpi-header">
                                <span class="kpi-title">TOTAL ACHIEVEMENT OMZET</span>
                                <span class="kpi-pill-badge badge-crimson">M-T-D</span>
                            </div>
                            <div class="kpi-value text-crimson" id="kpiTotalAchieve">Rp 0</div>
                            <div class="kpi-sub" id="kpiTargetSub" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 4px;">
                                <span id="kpiTargetSubText">Target: Rp 8.447.356.034</span>
                                <button type="button" onclick="openMonthlyTargetModal()" class="kpi-target-edit-btn" title="Ubah target omzet bulan ini" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 2px 7px; font-size: 11px; font-weight: 700; cursor: pointer;">✏️ Ubah Target</button>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" id="kpiProgressBar" style="width: 0%"></div>
                            </div>
                            <div class="progress-labels">
                                <span>Progress: <strong id="kpiProgressPct">0%</strong></span>
                                <span>Sisa: <strong id="kpiSisaTarget">Rp 0</strong></span>
                            </div>
                        </div>

                        <div class="card-tegas kpi-card">
                            <div class="kpi-header">
                                <span class="kpi-title">TIMEGONE BULAN INI</span>
                                <span class="kpi-pill-badge badge-dark" id="timegoneDayBadge">Hari ke-13 / 31</span>
                            </div>
                            <div class="kpi-value text-ink" id="kpiTimegoneVal">42%</div>
                            <div class="kpi-meta" id="kpiTimegoneMeta">
                                Status Tim: <strong id="kpiTeamStatusIcon" class="status-icon-lg">🖤 On Track</strong>
                            </div>
                        </div>

                        <div class="card-tegas kpi-card">
                            <div class="kpi-header">
                                <span class="kpi-title">TOTAL GROSS PROFIT (GP)</span>
                                <span class="kpi-pill-badge badge-green">Target 5%</span>
                            </div>
                            <div class="kpi-value text-green" id="kpiTotalGP">Rp 0</div>
                            <div class="kpi-meta">
                                Target GP: <strong id="kpiTargetGP">Rp 422.367.802</strong> (<span
                                    id="kpiGPPct">0%</span>)
                            </div>
                        </div>

                        <div class="card-tegas kpi-card">
                            <div class="kpi-header">
                                <span class="kpi-title">CROSS SELLING GADGET</span>
                                <span class="kpi-pill-badge badge-gold">Target 1 : 3</span>
                            </div>
                            <div class="kpi-value text-gold" id="kpiCrossRatio">0%</div>
                            <div class="kpi-meta">
                                Total Transaksi Gadget: <strong id="kpiTotalGadget">0</strong> unit
                            </div>
                        </div>
                    </div>
                </section>

