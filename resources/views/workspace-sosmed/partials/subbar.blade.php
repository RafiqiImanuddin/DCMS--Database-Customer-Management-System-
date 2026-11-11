<div class="workspace-header-wrapper mb-5" id="workspaceHeaderWrapper" style="{{ ($activeTab ?? 1) == 4 ? 'display: none;' : '' }}">
    <div class="workspace-header-pill flex items-center justify-end gap-3 flex-wrap">
        <!-- Hidden container untuk mencegah JS error jika modul aktif di-set -->
        <span id="activeModuleName" class="hidden" style="display: none;"></span>

        <div class="header-pill-actions flex items-center gap-2 ml-auto">
            <!-- Quick Action Buttons: Refresh Database Realtime -->
            <div class="header-pill-quick-actions">
                <button type="button" class="btn-header-quick btn-header-sync" id="headerBtnSyncSheets"
                    onclick="syncLiveKeepData()" title="Muat ulang data realtime dari database DCMS">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    <span>Refresh Data</span>
                </button>
            </div>
        </div>
    </div>
</div>
