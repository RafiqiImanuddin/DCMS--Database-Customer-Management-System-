                <section id="tabContent3" class="tab-content">
                    @php
                        $isSuperAdmin = auth()->check() && auth()->user()->isSuperAdmin();
                    @endphp
                    <!-- Dedicated Cross Selling File Upload for Cek AR (Sinkronisasi Antar Perangkat & Hak Akses Superadmin) -->
                    <div class="croselling-upload-card mb-20" id="crosellingDropZone">
                        <div class="croselling-upload-main">
                            <div class="croselling-upload-icon-wrap">
                                <span class="croselling-badge-icon">📑</span>
                            </div>
                            <div class="croselling-upload-info">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <h4 class="croselling-upload-title" style="margin:0;">Database Cross Selling & No. WhatsApp (Khusus Menu Cek AR)</h4>
                                    @if ($isSuperAdmin)
                                        <span style="background:#fef3c7; color:#92400e; font-size:10px; font-weight:800; padding:2px 8px; border-radius:6px; border:1px solid #fde68a;">Akses Superadmin</span>
                                    @else
                                        <span style="background:#e0f2fe; color:#0369a1; font-size:10px; font-weight:800; padding:2px 8px; border-radius:6px; border:1px solid #bae6fd;">Staf (Auto-Sync)</span>
                                    @endif
                                </div>
                                <p class="croselling-upload-sub" style="margin-top:4px;">
                                    @if ($isSuperAdmin)
                                        Unggah file Excel <strong>CROSSELLING-12102026.xlsx</strong> baru. Data otomatis disinkronkan ke seluruh PC staf secara realtime.
                                    @else
                                        Database Cross Selling tersinkronisasi otomatis dari unggahan Superadmin untuk pencarian faktur AR dan WhatsApp customer.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="croselling-upload-action-row">
                            <button class="btn-croselling-upload-pill" style="opacity: 0.9; background: #0f172a; cursor: pointer;"
                                onclick="forceSyncCrosellingFromServer()" title="Klik untuk sinkronisasi ulang data terbaru dari database">
                                🔄 Cek Sinkronisasi Server
                            </button>
                            <div class="turnover-status-badge croselling-status-badge" id="crosellingStatusBadge"
                                title="Indikator Pembaruan File Cross Selling">
                                <span class="turnover-status-dot dot-green"></span>
                                <span id="crosellingUpdateText">File Cross Selling: Memuat status...</span>
                            </div>
                        </div>
                    </div>
                    <div class="ar-search-minimal-box">
                        <div class="ar-search-header-minimal">
                            <h3 class="ar-search-title">🔍 Cek AR & Pencarian Pembelian via No. WhatsApp (Google Sheets)
                            </h3>
                            <p class="ar-search-sub">Cari transaksi berdasarkan <strong>Nomor AR (Document
                                    Number)</strong> ATAU <strong>Nomor WhatsApp Customer</strong> untuk mengecek apa
                                saja produk yang dibeli dan verifikasi kode sales sosmed.</p>
                        </div>

                        <div class="ar-search-pill-row">
                            <input type="text" id="arInputSearch"
                                placeholder="Masukkan No. AR (misal: 260274906) ATAU No. WhatsApp" class="ar-input-pill"
                                onkeydown="if(event.key==='Enter') executeARSearch()">
                            <button class="btn-search-pill" onclick="executeARSearch()">
                                🔍 Cari Data
                            </button>
                        </div>
                    </div>

                    <!-- Dynamic AR / WhatsApp Result Container -->
                    <div id="arResultContainer" style="display:none; margin-top:24px;">

                        <!-- Customer Profile Banner (muncul saat pencarian WhatsApp maupun AR) -->
                        <div id="customerProfileBanner" class="customer-profile-banner" style="display:none;">
                            <div class="customer-profile-header">
                                <div class="customer-identity">
                                    <div class="customer-avatar" id="custAvatar">👤</div>
                                    <div class="customer-name-group">
                                        <h3 id="custProfileName">Nama Pelanggan</h3>
                                        <span id="custProfileCode"
                                            style="font-size:0.8rem;color:#15803d;font-weight:700;">Kode: -</span>
                                    </div>
                                </div>
                                <div id="custWhatsAppAction">
                                    <!-- Tombol Chat WA -->
                                </div>
                            </div>
                            <div class="customer-stats-grid">
                                <div class="customer-stat-box">
                                    <div class="stat-label">Nomor WhatsApp</div>
                                    <div class="stat-value text-accent" id="custProfileWA">-</div>
                                </div>
                                <div class="customer-stat-box">
                                    <div class="stat-label">Total Pembelian</div>
                                    <div class="stat-value font-bold" id="custProfileTotalSpent">Rp 0</div>
                                </div>
                                <div class="customer-stat-box">
                                    <div class="stat-label">Jumlah Faktur AR</div>
                                    <div class="stat-value" id="custProfileInvoicesCount">0 Faktur</div>
                                </div>
                                <div class="customer-stat-box">
                                    <div class="stat-label">Total Item Terbeli</div>
                                    <div class="stat-value" id="custProfileItemsCount">0 Item</div>
                                </div>
                                <div class="customer-stat-box">
                                    <div class="stat-label">Cabang Langganan</div>
                                    <div class="stat-value text-crimson" id="custProfileCabang">-</div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Banner (Khusus Pencarian Dokumen AR) -->
                        <div id="arStatusBanner" class="ar-status-banner">
                            <div id="arStatusIcon" class="status-icon-banner"></div>
                            <div class="status-text-banner">
                                <h3 id="arStatusTitle">Status AR</h3>
                                <p id="arStatusSubtitle">Detail status verifikasi kode sales</p>
                            </div>
                        </div>

                        <!-- Transaction Metadata Grid (Khusus Dokumen AR) -->
                        <div class="ar-details-grid" id="arDetailsGrid">
                            <div class="ar-meta-item">
                                <span class="ar-meta-label">Nomor Document / AR:</span>
                                <span class="ar-meta-val font-bold" id="arMetaDoc">-</span>
                            </div>
                            <div class="ar-meta-item">
                                <span class="ar-meta-label">Tanggal Transaksi:</span>
                                <span class="ar-meta-val" id="arMetaDate">-</span>
                            </div>
                            <div class="ar-meta-item">
                                <span class="ar-meta-label">Cabang Transaksi:</span>
                                <span class="ar-meta-val text-crimson" id="arMetaCabang">-</span>
                            </div>
                            <div class="ar-meta-item">
                                <span class="ar-meta-label">Customer / Pelanggan:</span>
                                <span class="ar-meta-val" id="arMetaCust">-</span>
                            </div>
                            <div class="ar-meta-item">
                                <span class="ar-meta-label">No. WhatsApp Customer:</span>
                                <span class="ar-meta-val text-accent" id="arMetaWA">-</span>
                            </div>
                            <div class="ar-meta-item">
                                <span class="ar-meta-label">Total Penjualan:</span>
                                <span class="ar-meta-val font-bold" id="arMetaTotal">-</span>
                            </div>
                            <div class="ar-meta-item" style="grid-column: 1 / -1;">
                                <span class="ar-meta-label">Remarks / Keterangan:</span>
                                <span class="ar-meta-val" id="arMetaRemarks" style="color:var(--text-muted);">-</span>
                            </div>
                        </div>

                        <!-- Items in this AR or WhatsApp Purchases -->
                        <div class="items-section-header">
                            <span id="itemsTableTitle">Daftar Produk Pembelian:</span>
                            <span class="kpi-badge badge-ink" id="arItemsCountBadge">0 Item</span>
                        </div>

                        <div class="table-responsive">
                            <table class="kpi-matrix-table" id="arItemsTable">
                                <thead>
                                    <tr id="arItemsTableHead">
                                        <th class="col-sticky" style="width: 35px; text-align: center;">NO</th>
                                        <th class="text-left">PRODUK / ITEM DESCRIPTION</th>
                                        <th class="text-center" style="width: 105px;">KATEGORI</th>
                                        <th style="width: 45px;">QTY</th>
                                        <th class="text-left" style="width: 130px;">SALES EMPLOYEE</th>
                                        <th class="text-left" style="width: 90px;">CABANG</th>
                                        <th style="width: 85px;">HARGA</th>
                                        <th style="width: 90px;">TOTAL</th>
                                        <th class="text-center" style="width: 95px;">STATUS SOSMED</th>
                                        <th class="text-center" style="width: 85px;">POTENSI DENDA</th>
                                        <th class="text-center" style="width: 100px;">SALIN EXCEL</th>
                                    </tr>
                                </thead>
                                <tbody id="arItemsBody"></tbody>
                                <tfoot id="arItemsFoot"></tfoot>
                            </table>
                        </div>
                    </div>
                </section>

