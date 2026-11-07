                <section id="tabContent7" class="tab-content">
                    <!-- Card Upload File Excel Per Bulan ke Arsip (Permintaan User) -->
                    <div class="card-tegas mb-24" style="padding: 22px 26px; border-left: 7px solid #c8102e;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px;">
                            <div>
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                                    📂 Unggah File Excel turnover.xlsx Per Bulan ke Arsip
                                </h3>
                                <p style="font-size: 0.85rem; color: #64748b;">
                                    Pilih bulan dan tahun, lalu unggah file Excel turnover.xlsx untuk bulan tersebut.
                                    Data pencapaian akan otomatis terhitung dan masuk ke <strong>Grafik Tren Bulan ke
                                        Bulan</strong> di Dashboard!
                                </p>
                            </div>
                        </div>

                        <div
                            style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap; margin-top: 16px;">
                            <div class="filter-group" style="min-width: 170px;">
                                <label for="archiveUploadMonth">Pilih Bulan:</label>
                                <select id="archiveUploadMonth" class="form-input">
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10" selected>Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>

                            <div class="filter-group" style="min-width: 110px;">
                                <label for="archiveUploadYear">Tahun:</label>
                                <input type="number" id="archiveUploadYear" class="form-input" value="2026" min="2024"
                                    max="2030">
                            </div>

                            <div class="filter-group" style="flex: 1; min-width: 250px;">
                                <label for="archiveFileInput">File Excel turnover.xlsx:</label>
                                <input type="file" id="archiveFileInput" accept=".xlsx, .xls" class="form-input"
                                    style="padding: 6px;">
                            </div>

                            <button class="btn btn-crimson" style="height: 42px;" onclick="handleArchiveExcelUpload()">
                                🚀 Proses & Simpan ke Arsip
                            </button>
                        </div>
                    </div>

                    <div class="card-tegas table-card">
                        <div class="table-toolbar">
                            <div class="toolbar-title">
                                <h2>ARSIP & HISTORI PENCAPAIAN BULANAN</h2>
                                <span class="table-meta-tag">Tersimpan Permanen di Database Server</span>
                            </div>
                            <div class="toolbar-controls">
                                <button class="btn btn-crimson btn-sm" onclick="saveCurrentMonthToArchive()">
                                    💾 Simpan Snapshot Bulan Ini
                                </button>
                            </div>
                        </div>

                        <div class="archive-container" style="padding: 24px;">
                            <div class="archive-cards-grid" id="archiveGrid"></div>
                            <div id="archiveEmptyState" class="empty-state text-center" style="display:none">
                                Belum ada arsip bulan tersimpan. Silakan unggah file turnover.xlsx per bulan di atas.
                            </div>
                        </div>
                    </div>
                </section>

