                <section id="tabContent8" class="tab-content {{ (int) ($activeTab ?? 1) === 8 ? 'active' : '' }}">
                    <div class="admin-grid">
                        <!-- Card Master Foto & Avatar Profil Sales (Sesuai Permintaan User) -->
                        <div class="card-tegas admin-card" style="grid-column: 1 / -1; margin-bottom: 24px;">
                            <div class="admin-card-header">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
                                    <div>
                                        <h3>📷 Master Foto Profil Sales & Leaderboard</h3>
                                        <p>Unggah foto asli setiap PIC sales sosmed untuk ditampilkan pada <strong>Sales
                                                Leaderboard</strong>. Foto disimpan permanen ke server dan
                                            tersinkronisasi ke seluruh perangkat.</p>
                                    </div>
                                    <button class="btn btn-outline btn-sm" onclick="resetAllPhotosToDefault()">
                                        🔄 Reset ke Foto Standar
                                    </button>
                                </div>
                            </div>
                            <div class="admin-card-body">
                                <div class="table-responsive">
                                    <table class="kpi-matrix-table" id="adminPhotoTable">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 75px;">FOTO</th>
                                                <th class="text-left" style="min-width: 140px;">NAMA PIC</th>
                                                <th class="text-left" style="min-width: 150px;">KODE SOSMED</th>
                                                <th class="text-left" style="min-width: 250px;">UNGGAH GAMBAR (FILE)
                                                </th>
                                                <th class="text-left" style="min-width: 250px;">ATAU PASTE URL GAMBAR
                                                </th>
                                                <th class="text-center" style="width: 110px;">AKSI</th>
                                            </tr>
                                        </thead>
                                        <tbody id="adminPhotoTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- Form Target Bulanan (Bisa Diubah Per Bulan) -->
                        <div class="card-tegas admin-card">
                            <div class="admin-card-header">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                                    <div>
                                        <h3>🎯 Pengaturan Target Bulanan (Bisa Diatur Per Bulan)</h3>
                                        <p>Target omzet tidak sama setiap bulannya. Pilih periode bulan dan tahun di
                                            bawah ini untuk melihat dan mengubah target periode tersebut.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="admin-card-body">
                                <!-- Month & Year Selector -->
                                <div class="admin-target-period-picker mb-16"
                                    style="display: flex; gap: 12px; background: #f8fafc; padding: 14px 16px; border-radius: 12px; border: 1.5px solid #e2e8f0; flex-wrap: wrap; align-items: flex-end;">
                                    <div style="flex: 1; min-width: 140px;">
                                        <label for="adminTargetMonthSelect"
                                            style="display: block; font-size: 0.76rem; font-weight: 800; color: #475569; margin-bottom: 4px; text-transform: uppercase;">Pilih
                                            Bulan:</label>
                                        <select id="adminTargetMonthSelect" class="form-input"
                                            onchange="onAdminTargetMonthChange()">
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
                                    <div style="width: 110px;">
                                        <label for="adminTargetYearInput"
                                            style="display: block; font-size: 0.76rem; font-weight: 800; color: #475569; margin-bottom: 4px; text-transform: uppercase;">Tahun:</label>
                                        <input type="number" id="adminTargetYearInput" value="2026" class="form-input"
                                            min="2024" max="2030" onchange="onAdminTargetMonthChange()">
                                    </div>
                                    <div style="flex: 1.5; min-width: 220px;">
                                        <label for="adminTeamTargetInput"
                                            style="display: block; font-size: 0.76rem; font-weight: 800; color: #475569; margin-bottom: 4px; text-transform: uppercase;">Target
                                            Omzet Tim Bulan Ini (Rp):</label>
                                        <input type="number" id="adminTeamTargetInput" value="8447356034"
                                            class="form-input"
                                            style="font-weight: 800; color: #c8102e; font-size: 1.05rem;">
                                    </div>
                                </div>

                                <!-- List Target Per Sales -->
                                <div
                                    style="font-size: 0.78rem; font-weight: 800; color: #64748b; margin-bottom: 8px; text-transform: uppercase;">
                                    Target Omzet Individu Per Sales:
                                </div>
                                <div id="adminTargetList"></div>
                            </div>
                            <div class="admin-card-footer">
                                <button class="btn btn-crimson" onclick="saveAdminTargets()">💾 Simpan Target Bulan Ini
                                    ke Server</button>
                            </div>
                        </div>

                        <!-- Form Master Sales Sosmed -->
                        <div class="card-tegas admin-card">
                            <div class="admin-card-header">
                                <h3>👥 Master Kode Sosmed & PIC Sales</h3>
                                <p>Kelola pemetaan kode transaksi SAP (<code>BJM.SOS.xxx</code>) dengan nama PIC Sales.
                                </p>
                            </div>
                            <div class="admin-card-body">
                                <div class="add-master-row">
                                    <input type="text" id="newMasterCode" placeholder="Kode (misal: BJM.SOS.023)"
                                        class="form-input">
                                    <input type="text" id="newMasterName" placeholder="Nama PIC (misal: NADIA)"
                                        class="form-input">
                                    <button class="btn btn-primary btn-sm" onclick="addNewMasterCode()">+ Tambah
                                        Sales</button>
                                </div>
                                <div class="table-responsive mt-14" style="max-height: 400px;">
                                    <table class="kpi-matrix-table" id="adminMasterTable">
                                        <thead>
                                            <tr>
                                                <th class="text-left">KODE MASTER</th>
                                                <th class="text-left">NAMA PIC</th>
                                                <th>STATUS</th>
                                                <th>AKSI</th>
                                            </tr>
                                        </thead>
                                        <tbody id="adminMasterTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="admin-card-footer">
                                <button class="btn btn-outline" onclick="resetMasterToDefault()">Reset ke Master
                                    Standar</button>
                            </div>
                        </div>
                    </div>
                </section>

