<div class="workspace-header-wrapper mb-5" id="workspaceHeaderWrapper" style="{{ ($activeTab ?? 1) == 4 ? 'display: none;' : '' }}">
    <div class="workspace-header-pill flex items-center justify-between gap-3 flex-wrap">
        <div class="workspace-subbar !m-0">
            <span class="subbar-label">Modul aktif:</span>
            <span class="subbar-active-title" id="activeModuleName">Dashboard</span>
        </div>

        <div class="header-pill-actions flex items-center gap-2">
            <!-- Quick Action Buttons: Sync Sheets -->
            <div class="header-pill-quick-actions">
                <button type="button" class="btn-header-quick btn-header-sync" id="headerBtnSyncSheets"
                    onclick="syncLiveKeepData()" title="Sinkronkan data live terbaru dari Google Sheets">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    <span>Sync Sheets</span>
                </button>
            </div>

            <div class="header-action-separator"></div>

            <a href="https://wa.me/6289603864476?text=Halo%20Yahya,%20ada%20bug/ada%20trouble%20di%20Gadgetmart's%20Workspace.%20Tolong%20perbaiki%20segera."
                target="_blank" class="btn-pill-outline hidden sm:inline-flex" title="Hubungi Yahya via WhatsApp" style="border-color: #a7f3d0; background: #ecfdf5; color: #047857 !important;">
                WA Yahya
            </a>

            <div class="header-gear-dropdown-wrap">
                <button type="button" class="btn-pill-icon" id="headerGearBtn"
                    onclick="toggleSettingsDropdown(event)" title="Pengaturan & Menu Tambahan">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path
                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                        </path>
                    </svg>
                </button>
                <div class="header-gear-dropdown" id="headerGearDropdown">
                    <div class="dropdown-group-title">ADMINISTRASI & TOOLS</div>
                    <button type="button" class="gear-dropdown-item"
                        onclick="openMonthlyTargetModal(); closeSettingsDropdown();">
                        <span class="dropdown-icon">🎯</span>
                        <span class="dropdown-text">Atur Target Omzet Bulanan</span>
                    </button>
                    <button type="button" class="gear-dropdown-item" id="tabBtn8"
                        onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(8)}else{switchTab(8)}; closeSettingsDropdown();">
                        <span class="dropdown-icon">⚙️</span>
                        <span class="dropdown-text">Pengaturan Admin</span>
                    </button>
                    <button type="button" class="gear-dropdown-item" id="tabBtn7"
                        onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(7)}else{switchTab(7)}; closeSettingsDropdown();">
                        <span class="dropdown-icon">📁</span>
                        <span class="dropdown-text">Arsip Pencapaian Bulanan</span>
                    </button>
                    <div class="dropdown-separator"></div>
                    <button type="button" class="gear-dropdown-item" id="headerLoginBtn"
                        onclick="openLoginModal(); closeSettingsDropdown();">
                        <span class="dropdown-icon">🔐</span>
                        <span class="dropdown-text">Login Akun Workspace</span>
                    </button>
                    <a href="https://kandidatgadgetmart.my.id" target="_blank" class="gear-dropdown-item"
                        style="text-decoration:none;">
                        <span class="dropdown-icon">🚀</span>
                        <span class="dropdown-text">Kandidat Gadgetmart</span>
                        <span class="dropdown-ext-arrow">↗</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
