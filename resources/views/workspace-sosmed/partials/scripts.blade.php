  <script>
    window.WORKSPACE_ROUTING_TOKENS = @json($tabTokens ?? \App\Support\WorkspaceUrlCipher::allTokens());
    window.WORKSPACE_ENCRYPTED_URLS = @json($encryptedTabUrls ?? \App\Support\RouteCipher::allWorkspaceUrls());
    window.WORKSPACE_ACTIVE_TAB = {{ (int) ($activeTab ?? 1) }};
    window.WORKSPACE_INITIAL_SETTINGS = @json($initialWorkspaceSettings ?? []);
  </script>
  <script src="{{ asset('workspace-sosmed/client.js') }}?v={{ @filemtime(public_path('workspace-sosmed/client.js')) ?: '7.3' }}"></script>
    <!-- Omnichannel Image Lightbox Modal with Full-Screen Stage & Free Zoom/Pan -->
    <div id="omniImageModal" class="omni-img-modal" style="display:none;">
        <!-- Close Button (Fixed Top-Right) -->
        <button type="button" class="omni-img-modal-close" onclick="closeOmniImageModal()" title="Tutup Preview (Esc)">&times;</button>
        
        <!-- Zoom & Pan Stage (No inner overflow clipping) -->
        <div id="omniImageStage" class="omni-img-stage">
            <img id="omniImageModalImg" src="" alt="WhatsApp Media Preview" class="omni-img-modal-image">
        </div>

        <!-- Floating Controls Toolbar (Fixed Bottom-Center) -->
        <div class="omni-img-modal-toolbar" onclick="event.stopPropagation()">
            <div class="omni-zoom-controls">
                <button type="button" class="omni-zoom-btn" onclick="zoomOmni(-0.25)" title="Perkecil (Zoom Out)">−</button>
                <span id="omniImageZoomLevel" class="omni-zoom-level" onclick="resetOmniZoom()" title="Klik untuk reset zoom (100%)">100%</span>
                <button type="button" class="omni-zoom-btn" onclick="zoomOmni(0.25)" title="Perbesar (Zoom In)">+</button>
                <button type="button" class="omni-zoom-btn omni-zoom-btn-reset" onclick="resetOmniZoom()" title="Reset ukuran asli">↺ Reset</button>
                <span class="omni-zoom-hint">🖱️ Scroll mouse untuk zoom in/out • Drag untuk geser</span>
            </div>
            <div style="margin-top:8px;">
                <a id="omniImageModalDownload" href="#" target="_blank" download="whatsapp_image.jpg" class="btn btn-outline btn-sm omni-download-btn">
                    💾 Buka / Unduh Gambar Resolusi Penuh
                </a>
            </div>
        </div>
    </div>

    <!-- Hidden audio element for message chime -->
    <audio id="omniAudioChime" preload="auto">
        <source src="data:audio/mp3;base64,//uQRAAAAWMSLwUIYAAsYkXgoQwAEaCDWAEWWWxIN3GMAAOggAcDgOA8Hg8Hg8Hg8Hg8Pg8AgMBgMD///8PD4PD4PD4PD4PD4PD4PAIDAIDAIDAIDAIDAIDAIDAIDAIDAIDAIDAIDAIDAIDAIDAIDAIDAIDAIDAIDAID///8AAAA=" type="audio/mp3">
    </audio>

  <script>
    function updateSidebarActiveTab(tabIndex) {
        document.querySelectorAll('.workspace-sidebar-link').forEach(link => {
            const isTarget = link.getAttribute('data-tab') == tabIndex;
            if (link.getAttribute('data-tab') == '2') {
                link.className = 'workspace-sidebar-link flex-1 flex items-center gap-3 px-3.5 py-2.5 font-semibold transition min-w-0 ' + (isTarget ? 'text-white' : 'text-slate-300 hover:text-white');
            } else {
                link.className = 'workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition ' + (isTarget ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white');
            }
        });

        const wrapMatrix = document.getElementById('wrap-report-matrix-btn');
        if (wrapMatrix) {
            if (tabIndex == 2) {
                wrapMatrix.className = 'flex items-center justify-between rounded-xl transition bg-brand-600 text-white shadow-lg shadow-brand-600/20';
            } else {
                wrapMatrix.className = 'flex items-center justify-between rounded-xl transition text-slate-300 hover:bg-white/10 hover:text-white';
            }
        }

        // Expand/collapse Report Matrix sub-items in sidebar
        const subMatrix = document.getElementById('submenu-report-matrix');
        const chevMatrix = document.getElementById('chevron-report-matrix');
        if (subMatrix) subMatrix.classList.toggle('hidden', tabIndex != 2);
        if (chevMatrix) {
            chevMatrix.classList.toggle('rotate-90', tabIndex == 2);
            chevMatrix.classList.toggle('text-white', tabIndex == 2);
            chevMatrix.classList.toggle('text-slate-500', tabIndex != 2);
        }
    }

    function updateReportSubmenuActive(mode) {
        const targetMode = (mode === 'r2') ? 'r2' : 'r1';
        const map = {
            'r1': 'subitem-report-r1',
            'r2': 'subitem-report-r2'
        };
        ['r1', 'r2'].forEach(m => {
            const el = document.getElementById(map[m]);
            if (el) {
                if (m === targetMode) {
                    el.className = 'report-matrix-subitem flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold transition bg-white/15 text-white';
                } else {
                    el.className = 'report-matrix-subitem flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold transition text-slate-400 hover:text-white hover:bg-white/10';
                }
            }
        });
    }
    window.updateReportSubmenuActive = updateReportSubmenuActive;

    // Intercept switchTab so ANY tab switch updates the DCMS sidebar & sets the encrypted URL
    const origSwitchTab = window.switchTab;
    window.switchTab = function(tabIndex) {
        if (typeof origSwitchTab === 'function') {
            origSwitchTab(tabIndex);
        }
        updateSidebarActiveTab(tabIndex);

        const headerWrapper = document.getElementById('workspaceHeaderWrapper') || document.querySelector('.workspace-header-wrapper');
        if (headerWrapper) {
            headerWrapper.style.display = (tabIndex === 4) ? 'none' : '';
        }

        try {
            if (window.WORKSPACE_ENCRYPTED_URLS && window.WORKSPACE_ENCRYPTED_URLS[tabIndex]) {
                window.history.replaceState({}, '', window.WORKSPACE_ENCRYPTED_URLS[tabIndex]);
            } else {
                const token = (window.WORKSPACE_ROUTING_TOKENS && window.WORKSPACE_ROUTING_TOKENS[tabIndex]) ? window.WORKSPACE_ROUTING_TOKENS[tabIndex] : tabIndex;
                const url = new URL(window.location);
                url.searchParams.delete('v');
                url.searchParams.set('tab', token);
                window.history.replaceState({}, '', url);
            }
        } catch(e) {}
    };

    window.switchWorkspaceTab = function(tabIndex) {
        window.switchTab(tabIndex);
        if (window.innerWidth < 768 && typeof window.toggleDcmsSidebar === 'function') {
            window.toggleDcmsSidebar(false);
        }
    };

    window.addEventListener('DOMContentLoaded', () => {
        const initTab = window.WORKSPACE_ACTIVE_TAB || 1;
        setTimeout(() => {
            if (typeof window.switchWorkspaceTab === 'function') {
                window.switchWorkspaceTab(initTab);
            } else if (typeof window.switchTab === 'function') {
                window.switchTab(initTab);
            }
        }, 100);
    });
  </script>
