    <!-- Modal: Gemini API Key Config -->
    <div id="geminiModal" class="modal-overlay" style="display:none">
        <div class="modal-dialog card-tegas">
            <div class="modal-header">
                <h3>Konfigurasi Gemini API</h3>
                <button class="btn-close" onclick="closeGeminiModal()">✕</button>
            </div>
            <div class="modal-body">
                <p class="modal-description">
                    Hubungkan aplikasi langsung dengan model <strong>Google Gemini API</strong> untuk analisis performa
                    sosmed otomatis.
                </p>
                <div class="form-group">
                    <label for="geminiApiKeyInput">Gemini API Key (Google AI Studio):</label>
                    <input type="password" id="geminiApiKeyInput" placeholder="AQ.Ab8RN6Lvfri..." class="form-input">
                </div>
                <div class="form-group">
                    <label for="geminiModelSelect">Model Gemini:</label>
                    <select id="geminiModelSelect" class="form-input">
                        <option value="gemini-3.5-flash-lite" selected>Gemini 3.5 Flash Lite (Aktif, Sangat Cepat &
                            Stabil)</option>
                        <option value="gemini-3.1-flash-lite">Gemini 3.1 Flash Lite</option>
                        <option value="gemini-3.8-flash">Gemini 3.8 Flash</option>
                        <option value="gemini-flash-latest">Gemini Flash Latest</option>
                    </select>
                </div>
                <div class="info-alert">
                    💡 <em>Smart NLP Engine bawaan:</em> Aplikasi tetap bisa merespons pertanyaan harian seperti
                    <em>"report daily achievement per hari ini"</em> atau <em>"cek ar 260274906"</em> secara instan
                    tanpa perlu API key.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeGeminiModal()">Tutup</button>
                <button class="btn btn-crimson" onclick="saveGeminiConfig()">Simpan Konfigurasi</button>
            </div>
        </div>
    </div>

    <!-- Modal: Update Target Omzet Bulanan (Pop-up Cepat dari Report Matrix) -->
    <div id="monthlyTargetModal" class="modal-overlay" style="display:none" onclick="if(event.target===this) closeMonthlyTargetModal()">
        <div class="modal-dialog card-tegas" style="max-width: 860px; width: 95%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1.5px solid #e2e8f0; padding: 16px 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">🎯</span>
                    <div>
                        <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #0f172a;">Update Target Omzet Bulanan</h3>
                        <p style="margin: 2px 0 0 0; font-size: 0.76rem; color: #64748b;">Target tersimpan per bulan & otomatis sinkron dengan Master Karyawan.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" onclick="closeMonthlyTargetModal()" style="cursor: pointer;">✕</button>
            </div>

            <div class="modal-body" style="overflow-y: auto; overflow-x: hidden; padding: 20px 22px; flex: 1;">
                <!-- Period Switcher inside Modal -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 16px 18px; margin-bottom: 16px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; align-items: flex-end;">
                        <div>
                            <label for="modalTargetMonthSelect" style="display: block; font-size: 0.74rem; font-weight: 800; color: #475569; margin-bottom: 6px; text-transform: uppercase;">Bulan Target:</label>
                            <select id="modalTargetMonthSelect" class="form-input" onchange="onModalTargetMonthChange()" style="font-weight: 700; width: 100%;">
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11" selected>November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>

                        <div style="max-width: 140px;">
                            <label for="modalTargetYearInput" style="display: block; font-size: 0.74rem; font-weight: 800; color: #475569; margin-bottom: 6px; text-transform: uppercase;">Tahun:</label>
                            <input type="number" id="modalTargetYearInput" value="2026" min="2024" max="2030" class="form-input" onchange="onModalTargetMonthChange()" style="font-weight: 700; text-align: center; width: 100%;">
                        </div>

                        <div style="grid-column: span 2; min-width: 220px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <label for="modalTeamTargetInput" style="font-size: 0.74rem; font-weight: 800; color: #475569; text-transform: uppercase;">Target Tim Bulan Ini (Rp):</label>
                                <span id="modalTeamGPPreview" style="font-size: 0.72rem; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">GP 5%: Rp 0</span>
                            </div>
                            <input type="text" id="modalTeamTargetInput" class="form-input" oninput="onModalTeamInputChange(this)" placeholder="8.672.618.860" style="font-weight: 800; font-size: 1.05rem; color: #c8102e; width: 100%; font-family: monospace;">
                        </div>
                    </div>
                </div>

                <!-- Helper Quick Action Buttons -->
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; justify-content: space-between; align-items: center;">
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="modalBagiRataSales()" style="font-weight: 700; border-color: #3b82f6; color: #1d4ed8; background: #eff6ff; padding: 6px 12px; border-radius: 8px; cursor: pointer;">
                            ⚡ Bagi Rata ke Seluruh Sales Aktif
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-success" onclick="modalHitungTotalSales()" style="font-weight: 700; border-color: #10b981; color: #047857; background: #ecfdf5; padding: 6px 12px; border-radius: 8px; cursor: pointer;">
                            🧮 Hitung Total dari Penjumlahan Sales
                        </button>
                    </div>
                    <span id="modalTargetBalanceStatus" style="font-size: 0.75rem; font-weight: 700; color: #059669;">Seimbang</span>
                </div>

                <!-- Active Sales Target List Header -->
                <div style="font-size: 0.78rem; font-weight: 800; color: #475569; margin-bottom: 8px; text-transform: uppercase;">
                    Target Individu Masing-Masing Sales:
                </div>

                <div id="modalSalesTargetListContainer" style="display: flex; flex-direction: column; gap: 8px; max-height: 380px; overflow-y: auto; overflow-x: hidden; padding-right: 4px;">
                    <!-- Dynamically populated rows -->
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1.5px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <div style="font-size: 0.80rem; color: #64748b;">
                    Total Sales: <strong id="modalFooterTotalSales" style="color: #0f172a; font-size: 0.95rem; margin-left: 4px;">Rp 0</strong>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-outline" onclick="closeMonthlyTargetModal()" style="cursor: pointer;">Batal</button>
                    <button type="button" class="btn btn-crimson" onclick="saveModalMonthlyTargets()" style="cursor: pointer;">💾 Simpan Target Omzet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Rumus Logika -->
    <div id="formulaModal" class="modal-overlay" style="display:none">
        <div class="modal-dialog modal-large card-tegas">
            <div class="modal-header">
                <h3>Logika & Rumus Perhitungan Dashboard Sosmed</h3>
                <button class="btn-close" onclick="closeFormulaModal()">✕</button>
            </div>
            <div class="modal-body">
                <div class="formula-section">
                    <h4>1. TIMEGONE & Status Emotikon</h4>
                    <p>Timegone adalah persentase hari berjalan terhadap total hari bulan tersebut:</p>
                    <div class="formula-box"><code>Timegone = (Hari Berjalan / Total Hari Bulan) &times; 100%</code>
                    </div>
                    <ul>
                        <li>🖤 <strong>On Track:</strong> Capaian &ge; Timegone (atau toleransi &ge; 38%).</li>
                        <li>💔 <strong>Warning:</strong> Capaian antara 35% – 37%.</li>
                        <li>☠️ <strong>Critical:</strong> Capaian &lt; 35%.</li>
                    </ul>
                </div>
                <div class="formula-section">
                    <h4>2. Target Gross Profit (GP) 5%</h4>
                    <div class="formula-box"><code>Target GP = 5% &times; Target Omzet Bulanan</code></div>
                    <p>Contoh: Target Omzet Rp 1.081.261.572 &times; 5% = <strong>Rp 54.063.079</strong>.</p>
                </div>
                <div class="formula-section">
                    <h4>3. Target Rasio Cross Selling 1 : 3</h4>
                    <div class="formula-box">
                        <code>% CROSSELLING = MIN( 100%, (Jlh Cross >50K / Jlh Gadget) &times; 3 )</code></div>
                    <p>Jika mencapai minimal 1 cross sell per 3 gadget (33,33%), maka KPI Cross-selling mendapat nilai
                        100%.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-crimson" onclick="closeFormulaModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal: Detail AR & Pelaporan ke WhatsApp Akmal (+62 823-5771-2674) -->
    <div id="arDetailModal" class="modal-overlay" style="display:none">
        <div class="modal-dialog modal-xlarge card-tegas">
            <div class="modal-header"
                style="display: flex; align-items: center; justify-content: space-between; position: relative;">
                <div style="flex: 1; text-align: center;">
                    <h3 id="modalDocMainTitle"
                        style="font-size: 1.3rem; font-weight: 800; letter-spacing: -0.01em; margin: 0; color: #0f172a; text-transform: uppercase;">
                        TRACK AR BERDASARKAN VLOOKUP</h3>
                    <span style="font-size:0.78rem; color:var(--text-muted);">Dokumen AR: <strong id="modalDocTitle"
                            class="text-crimson">-</strong> | Tanggal: <span id="modalMetaDate">-</span> | Cabang: <span
                            id="modalMetaCabang" class="font-bold text-crimson">-</span></span>
                </div>
                <button class="btn-close" onclick="closeARDetailModal()"
                    style="position: absolute; right: 18px; top: 16px;">✕</button>
            </div>

            <div class="modal-body">
                <!-- Sub-Tabs Navigation inside Modal -->
                <div class="modal-tabs">
                    <button id="btnModalSubTab1" class="modal-tab-btn active" onclick="switchModalSubTab(1)">
                        🔍 1. Rincian Produk & Status Sosmed
                    </button>
                    <button id="btnModalSubTab2" class="modal-tab-btn btn-tab-report" onclick="switchModalSubTab(2)">
                        🚨 2. Form Lapor ke Akmal (WhatsApp)
                    </button>
                </div>

                <!-- Sub-View 1: Rincian Produk & Checkbox Item -->
                <div id="modalSubTabView1">
                    <!-- Top 5-Column Summary Table Matching Image 2 -->
                    <div class="ar-track-summary-card mb-20"
                        style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
                        <table class="ar-track-summary-table"
                            style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                    <th
                                        style="padding: 10px 14px; font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase;">
                                        CUSTOMER</th>
                                    <th
                                        style="padding: 10px 14px; font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase;">
                                        NOMOR WA</th>
                                    <th
                                        style="padding: 10px 14px; font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; text-align: center;">
                                        KODE SOSMED</th>
                                    <th
                                        style="padding: 10px 14px; font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase;">
                                        BARANG KEEP</th>
                                    <th
                                        style="padding: 10px 14px; font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase;">
                                        CATATAN</th>
                                    <th
                                        style="padding: 10px 14px; font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; text-align: center;">
                                        STATUS KEEP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding: 12px 14px; font-size: 0.85rem; font-weight: 700; color: #0f172a;"
                                        id="modalTrackCust">-</td>
                                    <td style="padding: 12px 14px; font-size: 0.85rem;" id="modalTrackWA">-</td>
                                    <td style="padding: 12px 14px; text-align: center;" id="modalTrackSosmed">-</td>
                                    <td style="padding: 12px 14px; font-size: 0.85rem; font-weight: 600; color: #1e293b;"
                                        id="modalTrackKeepBarang">-</td>
                                    <td style="padding: 12px 14px; font-size: 0.76rem; line-height: 1.35; color: #334155;"
                                        id="modalTrackCatatan">-</td>
                                    <td style="padding: 12px 14px; text-align: center;" id="modalTrackStatusKeep">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden elements for backward compatibility -->
                    <div style="display:none;">
                        <span id="modalMetaDoc">-</span>
                        <span id="modalMetaCust">-</span>
                        <span id="modalMetaWA">-</span>
                        <span id="modalMetaTotal">-</span>
                        <span id="modalMetaRemarks">-</span>
                    </div>

                    <!-- Status Banner -->
                    <div id="modalStatusBanner" class="ar-status-banner mb-20" style="margin-bottom: 16px;">
                        <div id="modalStatusIcon" class="status-icon-banner"></div>
                        <div class="status-text-banner">
                            <h3 id="modalStatusTitle">Status AR</h3>
                            <p id="modalStatusSubtitle">Detail status verifikasi</p>
                        </div>
                    </div>

                    <!-- Items Table Header -->
                    <div class="items-section-header">
                        <span>Daftar Produk dalam AR Ini (Centang item yang belum terinput sosmed):</span>
                        <span class="kpi-badge badge-ink" id="modalItemsCountBadge">0 Item</span>
                    </div>

                    <div class="table-responsive">
                        <table class="kpi-matrix-table" id="modalItemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">
                                        <input type="checkbox" id="modalSelectAllItems" class="checkbox-custom"
                                            onchange="toggleSelectAllModalItems(this.checked)" title="Pilih Semua">
                                    </th>
                                    <th class="col-sticky" style="width: 40px;">NO</th>
                                    <th class="text-left" style="min-width: 220px;">NAMA PRODUK / ITEM DESCRIPTION</th>
                                    <th>QTY</th>
                                    <th>HARGA SATUAN</th>
                                    <th>TOTAL</th>
                                    <th class="text-left" style="min-width: 180px;">SALES EMPLOYEE TERINPUT</th>
                                    <th class="text-center">STATUS SOSMED</th>
                                    <th class="text-center" style="width: 100px;">POTENSI DENDA</th>
                                    <th class="text-center" style="width: 110px;">SALIN EXCEL</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemsTableBody"></tbody>
                            <tfoot id="modalItemsTableFoot"></tfoot>
                        </table>
                    </div>

                    <div
                        style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <button class="btn btn-outline" onclick="copyModalSelectedItems(this)"
                                style="font-weight: 700; border: 1.5px solid #0f172a;"
                                title="Salin semua item yang dicentang ke format 12 kolom Excel (tanpa header)">
                                📋 Salin Item Tercentang (Format Excel)
                            </button>
                            <button class="btn btn-outline" onclick="claimModalSelectedItems(this)"
                                style="font-weight: 700; border: 1.5px solid #c8102e; color: #c8102e;"
                                title="Masukkan item tercentang ke Modul Klaim AR (Pengumpulan 3 Hari)">
                                Masukkan ke Klaim Cabang
                            </button>
                        </div>
                        <button class="btn btn-crimson" onclick="switchModalSubTab(2)">
                            🚨 Laporkan Ada yang Belum Terinput / Salah ke Akmal &raquo;
                        </button>
                    </div>
                </div>

                <!-- Sub-View 2: Form Pelaporan ke WhatsApp Akmal -->
                <div id="modalSubTabView2" style="display:none;">
                    <div class="report-form-box">
                        <div class="report-form-header">
                            <span style="font-size: 1.8rem;">🚨</span>
                            <div>
                                <h4>Form Pelaporan Revisi AR ke Mas Akmal</h4>
                                <span class="kpi-badge badge-accent">Tujuan WhatsApp: Mas Akmal (+62
                                    823-5771-2674)</span>
                            </div>
                        </div>

                        <!-- Selected Items to report -->
                        <div class="report-form-row">
                            <label>1. Item yang Belum Terinput / Salah Kode Sosmed:</label>
                            <div id="modalReportSelectedItemsList" class="selected-items-list">
                                <em>Belum ada item yang dicentang. Silakan kembali ke tab 1 atau pilih item di bawah
                                    ini.</em>
                            </div>
                        </div>

                        <!-- Target Sosmed Code -->
                        <div class="report-form-row">
                            <label for="modalReportSosmedSelect">2. Harusnya Diinput Kode Sosmed Berapa?</label>
                            <select id="modalReportSosmedSelect" class="form-input"
                                onchange="updateWhatsAppMessagePreview()" style="font-weight: 700;">
                                <option value="">-- Pilih Kode Sales Sosmed --</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="report-form-row">
                            <label for="modalReportNotes">3. Catatan / Alasan untuk Mas Akmal:</label>
                            <textarea id="modalReportNotes" class="form-input" rows="3"
                                style="width: 100%; resize: vertical;"
                                oninput="updateWhatsAppMessagePreview()"></textarea>
                        </div>

                        <!-- Live Preview of WA Message -->
                        <div class="report-form-row">
                            <label>4. Pratinjau Teks Pesan WhatsApp yang Akan Dikirim:</label>
                            <pre id="modalWAPreview"
                                style="background:#0f172a; color:#f8fafc; padding:14px; border-radius:10px; font-family:'JetBrains Mono', monospace; font-size:0.8rem; white-space:pre-wrap; max-height:180px; overflow-y:auto; line-height:1.5;"></pre>
                        </div>

                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; flex-wrap: wrap; gap: 10px;">
                            <button class="btn btn-outline" onclick="switchModalSubTab(1)">&laquo; Kembali ke Rincian
                                AR</button>
                            <button class="btn-whatsapp" onclick="sendReportToAkmalWhatsApp()"
                                style="padding: 10px 22px; font-size: 0.95rem;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z" />
                                </svg>
                                Kirim Laporan ke WhatsApp Akmal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeARDetailModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Workspace Login Modal -->
    <div id="workspaceLoginModal" class="workspace-modal" style="display:none;">
        <div class="workspace-modal-backdrop" onclick="closeLoginModal()"></div>
        <div class="workspace-modal-dialog">
            <div class="workspace-modal-header">
                <h3 class="font-heading">🔐 Login Gadgetmart's Workspace</h3>
                <button class="modal-close-btn" onclick="closeLoginModal()">&times;</button>
            </div>
            <div class="workspace-modal-body">
                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="display:block; margin-bottom: 6px; font-weight:600; font-size:0.88rem;">Pilih Role /
                        Pengguna</label>
                    <select id="loginRoleSelect" class="form-select"
                        style="width:100%; padding: 8px 12px; border-radius:8px; border:1.5px solid #cbd5e1; font-family:'Inter',sans-serif;">
                        <option value="admin">Administrator / Manager</option>
                        <option value="sosmed">Tim PIC Sales Sosmed</option>
                        <option value="cabang">Kepala Cabang / Kasir</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display:block; margin-bottom: 6px; font-weight:600; font-size:0.88rem;">PIN / Password
                        Akses</label>
                    <input type="password" id="loginPinInput" class="form-input" placeholder="Masukkan PIN akses..."
                        style="width:100%; padding: 8px 12px; border-radius:8px; border:1.5px solid #cbd5e1; font-family:'Inter',sans-serif;"
                        onkeyup="if(event.key==='Enter') executeWorkspaceLogin()">
                    <span style="font-size:0.75rem; color:#64748b; margin-top:4px; display:block;">Default PIN admin:
                        1234</span>
                </div>
                <div id="loginFeedback"
                    style="display:none; margin-bottom:12px; font-size:0.85rem; padding:8px 12px; border-radius:6px;">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button class="btn btn-secondary" onclick="closeLoginModal()"
                        style="padding:7px 16px;">Batal</button>
                    <button class="btn btn-primary" onclick="executeWorkspaceLogin()"
                        style="padding:7px 18px; background:#0f172a; color:#fff; border-color:#0f172a;">Masuk</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden File Input for Evidence Image Upload (Multiple Support) -->
    <input type="file" id="evidenceFileInput" accept="image/*" multiple style="display:none;"
        onchange="handleEvidenceFileSelected(this)">

    <!-- Evidence Image Zoom & Multi-Gallery Modal -->
    <div id="evidenceImageModal" class="modal-overlay" style="display:none;" onclick="closeEvidenceImageModal(event)">
        <div class="modal-dialog card-tegas"
            style="max-width: 860px; width: 94%; padding: 20px; text-align: center; background: #ffffff; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);"
            onclick="event.stopPropagation()">
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 10px;">
                <div style="text-align: left;">
                    <h4 id="evidenceModalTitle"
                        style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a;">Bukti Format Keep</h4>
                    <span id="evidenceModalSub" style="font-size: 0.76rem; color: var(--text-muted);">-</span>
                </div>
                <button class="btn-close" onclick="closeEvidenceImageModal()"
                    style="font-size: 1.1rem; cursor: pointer;">✕</button>
            </div>

            <!-- Main Image Display with Navigation Arrows -->
            <div
                style="position: relative; max-height: 65vh; min-height: 240px; overflow: hidden; border-radius: 8px; border: 1.5px solid #e2e8f0; background: #0f172a; display: flex; align-items: center; justify-content: center; padding: 10px;">
                <button type="button" class="evidence-nav-btn evidence-nav-prev" onclick="prevEvidenceModalImage()"
                    title="Foto Sebelumnya (Panah Kiri)">❮</button>
                <img id="evidenceFullImg" src="" alt="Bukti Format Keep"
                    style="max-width: 100%; max-height: 62vh; object-fit: contain; border-radius: 6px;">
                <button type="button" class="evidence-nav-btn evidence-nav-next" onclick="nextEvidenceModalImage()"
                    title="Foto Selanjutnya (Panah Kanan)">❯</button>
                <div id="evidenceModalCounter" class="evidence-modal-counter-badge">Foto 1 dari 1</div>
            </div>

            <!-- Thumbnails Carousel Strip for multiple images -->
            <div id="evidenceModalGalleryStrip" class="evidence-modal-gallery-strip" style="display:none;"></div>

            <!-- Modal Action Buttons -->
            <div
                style="margin-top: 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; gap: 8px;">
                    <button class="btn btn-outline btn-sm" onclick="triggerEvidenceUploadFromModal()"
                        style="font-weight: 700; color: #0f172a; font-size: 0.78rem;"
                        title="Tambahkan foto bukti tambahan untuk nomor AR ini">
                        ➕ Tambah Foto Bukti
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="removeCurrentModalEvidence()"
                        style="color: #e11d48; border-color: #fecdd3; font-size: 0.78rem;"
                        title="Hapus foto bukti yang sedang aktif">
                        🗑️ Hapus Foto Ini
                    </button>
                </div>
                <div style="display: flex; gap: 8px;">
                    <a id="btnDownloadEvidence" href="#" download="evidence_keep.jpg" class="btn btn-outline btn-sm"
                        style="font-size: 0.78rem;">
                        💾 Unduh Foto Ini
                    </a>
                    <button class="btn btn-crimson btn-sm" onclick="closeEvidenceImageModal()"
                        style="font-size: 0.78rem;">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Input Nomor Faktur AR (Selaras DCMS Tailwind) -->
    <div id="keepInputARModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" style="display:none;" onclick="if(event.target===this) closeKeepInputARModal()">
        <div class="w-full max-w-md rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xl transition-all" onclick="event.stopPropagation()">
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-xl bg-brand-50 text-brand-600 border border-brand-200/50">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Input Nomor Faktur AR</h3>
                        <p class="text-xs text-slate-500">Hubungkan transaksi keep dengan nomor faktur AR di SAP</p>
                    </div>
                </div>
                <button type="button" class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer" onclick="closeKeepInputARModal()">✕</button>
            </div>

            <div class="mt-4 space-y-4">
                <!-- Customer Context Box -->
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 p-3.5 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Customer</span>
                        <span id="keepInputARCustPhone" class="text-xs font-semibold text-emerald-700 font-mono">-</span>
                    </div>
                    <div id="keepInputARCustName" class="text-sm font-bold text-slate-900">-</div>
                    <div id="keepInputARBarang" class="text-xs text-slate-600 truncate">-</div>
                </div>

                <!-- Input Box -->
                <div>
                    <label for="keepInputARField" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nomor Faktur AR</label>
                    <input type="text" id="keepInputARField" class="dcms-input py-2.5 px-3.5 text-sm font-mono font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-sans placeholder:font-normal" placeholder="Contoh: 260278899" onkeydown="if(event.key==='Enter'){event.preventDefault();submitKeepInputARModal();}">
                    <p class="mt-1.5 text-[11px] text-slate-400">Nomor faktur akan otomatis disinkronkan ke database sistem.</p>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="mt-6 flex items-center justify-end gap-2.5 border-t border-slate-100 pt-4">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300 cursor-pointer" onclick="closeKeepInputARModal()">Batal</button>
                <button type="button" id="btnSubmitKeepInputAR" class="dcms-primary-button px-5 py-2.5 text-xs font-bold cursor-pointer" onclick="submitKeepInputARModal()">Simpan Nomor AR</button>
            </div>
        </div>
    </div>

    <!-- Modal: Quick Update Status Keep (Selaras DCMS Tailwind) -->
    <div id="keepStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" style="display:none;" onclick="if(event.target===this) closeKeepStatusModal()">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xl transition-all" onclick="event.stopPropagation()">
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-black text-slate-900">Ubah Status Customer Keep</h3>
                    <p id="keepStatusModalCustName" class="text-xs text-slate-500 font-semibold truncate max-w-[240px]">-</p>
                </div>
                <button type="button" class="grid size-7 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer" onclick="closeKeepStatusModal()">✕</button>
            </div>

            <div class="mt-3.5 space-y-2">
                <!-- WIN -->
                <button type="button" onclick="selectQuickKeepStatus('WIN')" class="w-full flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50/60 px-3.5 py-2.5 text-xs font-bold text-emerald-800 transition hover:bg-emerald-100 hover:border-emerald-300 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        <span>WIN (Closing)</span>
                    </span>
                    <span class="text-[10px] text-emerald-600 font-medium">Deal Transaksi</span>
                </button>

                <!-- ON PROGRESS HOT -->
                <button type="button" onclick="selectQuickKeepStatus('ON PROGRESS HOT')" class="w-full flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50/60 px-3.5 py-2.5 text-xs font-bold text-amber-800 transition hover:bg-amber-100 hover:border-amber-300 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-amber-500"></span>
                        <span>On Progress Hot</span>
                    </span>
                    <span class="text-[10px] text-amber-600 font-medium">Sangat Potensial</span>
                </button>

                <!-- ON PROGRESS MEDIUM -->
                <button type="button" onclick="selectQuickKeepStatus('ON PROGRESS MEDIUM')" class="w-full flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50/40 px-3.5 py-2.5 text-xs font-bold text-amber-800 transition hover:bg-amber-100 hover:border-amber-300 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-amber-400"></span>
                        <span>On Progress Medium</span>
                    </span>
                    <span class="text-[10px] text-amber-600 font-medium">Sedang Follow Up</span>
                </button>

                <!-- ON PROGRESS LOW -->
                <button type="button" onclick="selectQuickKeepStatus('ON PROGRESS LOW')" class="w-full flex items-center justify-between rounded-xl border border-sky-200 bg-sky-50/60 px-3.5 py-2.5 text-xs font-bold text-sky-800 transition hover:bg-sky-100 hover:border-sky-300 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-sky-500"></span>
                        <span>On Progress Low</span>
                    </span>
                    <span class="text-[10px] text-sky-600 font-medium">Respon Lambat</span>
                </button>

                <!-- LOSE -->
                <button type="button" onclick="selectQuickKeepStatus('LOSE')" class="w-full flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50/60 px-3.5 py-2.5 text-xs font-bold text-rose-800 transition hover:bg-rose-100 hover:border-rose-300 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-rose-500"></span>
                        <span>LOSE</span>
                    </span>
                    <span class="text-[10px] text-rose-600 font-medium">Batal / Lost</span>
                </button>

                <!-- TAKE OUT -->
                <button type="button" onclick="selectQuickKeepStatus('TAKE OUT')" class="w-full flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-100 hover:border-slate-300 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-slate-400"></span>
                        <span>TAKE OUT</span>
                    </span>
                    <span class="text-[10px] text-slate-500 font-medium">Dikeluarkan</span>
                </button>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex justify-end">
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 cursor-pointer" onclick="closeKeepStatusModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal: Assign Manual Tim Follow Up (Tanpa Jempol/Reaction) -->
    <div id="assignManualFUModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" style="display:none;" onclick="if(event.target===this) closeAssignManualFUModal()">
        <div class="w-full max-w-md rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xl transition-all" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="size-9 rounded-xl bg-indigo-500/10 text-indigo-700 flex items-center justify-center font-bold">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Assign Manual Tim Follow Up</h3>
                        <p class="text-xs text-slate-500">Tugaskan PIC follow up tanpa perlu reaksi WhatsApp</p>
                    </div>
                </div>
                <button type="button" class="grid size-7 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer" onclick="closeAssignManualFUModal()">✕</button>
            </div>

            <!-- Info Singkat Customer / Barang -->
            <div class="mt-3.5 rounded-xl bg-slate-50 border border-slate-200/70 p-3 text-xs space-y-1.5">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Customer:</span>
                    <span id="assignManualCustName" class="font-bold text-slate-800 truncate max-w-[200px]">-</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">WhatsApp:</span>
                    <span id="assignManualCustWA" class="font-mono font-bold text-indigo-700">-</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Barang Keep:</span>
                    <span id="assignManualBarang" class="font-bold text-slate-800 truncate max-w-[200px]">-</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Cabang:</span>
                    <span id="assignManualCabang" class="font-bold text-sky-800">-</span>
                </div>
            </div>

            <!-- Form Input -->
            <form onsubmit="submitAssignManualFU(event)" class="mt-4 space-y-3.5">
                <!-- Pilihan Cepat dari Master PIC Cabang / Semua PIC -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih dari Master PIC (Opsi Cepat)</label>
                    <select id="assignManualSelectPic" onchange="onSelectMasterPicChanged(this.value)" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                        <option value="">-- Ketik Manual atau Pilih Master PIC --</option>
                    </select>
                </div>

                <!-- Input Nama Tim Follow Up -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Tim Follow Up <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="assignManualFUName" required placeholder="Contoh: FL.PLHC.07 - MUHAMMAD... atau RARA SAMSUNG" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" />
                </div>

                <!-- Input Nomor WhatsApp / HP FU -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nomor WhatsApp / HP FU <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs font-bold">📱</span>
                        <input type="tel" id="assignManualFUPhone" required placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx" class="w-full rounded-xl border border-slate-200 bg-white pl-8 pr-3 py-2 text-xs font-mono font-medium text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" />
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Nomor ini untuk komunikasi direct WhatsApp & identifikasi inbox follow up.</p>
                </div>

                <!-- Action Buttons -->
                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 cursor-pointer" onclick="closeAssignManualFUModal()">Batal</button>
                    <button type="submit" id="btnSubmitAssignManualFU" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-xs font-bold shadow-md shadow-indigo-600/20 transition cursor-pointer">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Simpan & Assign</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Konfirmasi Hapus Format Keep (Superadmin Only) -->
    <div id="deleteKeepConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" style="display:none;" onclick="if(event.target===this) closeDeleteKeepModal()">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xl transition-all" onclick="event.stopPropagation()">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900">Hapus Format Keep?</h3>
                    <p class="text-xs text-slate-500">Aksi ini permanen dan menghapus baris dari database.</p>
                </div>
            </div>

            <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200/80 p-3 text-xs space-y-1.5">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Customer:</span>
                    <span id="delModalCustName" class="font-bold text-slate-800 truncate max-w-[180px]">-</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">WhatsApp:</span>
                    <span id="delModalCustWA" class="font-mono font-bold text-slate-700">-</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Barang:</span>
                    <span id="delModalBarang" class="font-bold text-slate-800 truncate max-w-[180px]">-</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Cabang:</span>
                    <span id="delModalCabang" class="font-bold text-sky-800">-</span>
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 cursor-pointer" onclick="closeDeleteKeepModal()">Batal</button>
                <button type="button" id="btnConfirmDeleteKeep" class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 text-xs font-bold shadow-md shadow-rose-600/20 transition cursor-pointer" onclick="executeDeleteKeepRow()">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Ya, Hapus Data</span>
                </button>
            </div>
        </div>
    </div>

