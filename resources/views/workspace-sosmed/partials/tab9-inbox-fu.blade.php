                <section id="tabContent9" class="tab-content mb-12 {{ (int) ($activeTab ?? 1) === 9 ? 'active' : '' }}">
                    <div class="w-full space-y-4">
                        <!-- 1. Header: Selaras dengan Customer Outreach & Format Keep -->
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-500/10 px-2.5 py-0.5 text-xs font-bold text-brand-700">
                                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Live WhatsApp Official
                                    </span>
                                    <span class="text-xs text-slate-400">&bull;</span>
                                    <span class="text-xs font-medium text-slate-500">Inbox Percakapan & Follow Up Tim Toko</span>
                                </div>
                                <h1 class="mt-1 text-2xl font-black text-slate-900">Inbox Follow-Up Sosmed</h1>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-xs">
                                    <span class="size-2 rounded-full bg-emerald-500"></span>
                                    Bot Gateway: <span class="font-bold text-slate-900">085111426699</span>
                                </span>
                            </div>
                        </div>

                        <!-- 2. Omnichannel Workspace App Shell -->
                        <div class="omni-app-container dcms-card p-0 rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden flex flex-col">
                            <!-- MAIN WORKSPACE BODY -->
                            <div class="omni-main-body flex flex-1 overflow-hidden">
                                <!-- FOLDERS & NAVIGATION SIDEBAR -->
                                <aside class="omni-col-sidebar">
                                    <!-- VIEW SWITCHER: RELOCATED FROM NAVBAR -->
                                    <div class="omni-sidebar-view-switcher">
                                        <button type="button" class="omni-sidebar-nav-btn active" id="btnOmniTabChats"
                                            onclick="switchOmniView('CHATS')">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                            </svg>
                                            <span>Inbox Percakapan</span>
                                        </button>
                                        <button type="button" class="omni-sidebar-nav-btn" id="btnOmniTabMaster"
                                            onclick="switchOmniView('MASTER')">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                                <circle cx="9" cy="7" r="4" />
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                            </svg>
                                            <span>Master Data PIC FU</span>
                                        </button>
                                    </div>

                                    <div class="omni-sidebar-divider"></div>

                                    <div class="omni-inbox-header">
                                        <span class="omni-inbox-title">INBOX</span>
                                    </div>

                                    <div class="omni-folder-list">
                                        <div class="omni-nav-item active" id="omniFilterAll" onclick="setOmniFolder('ALL')">
                                            <span class="omni-nav-name">All chats</span>
                                            <span class="omni-nav-badge" id="omniBadgeAll">0</span>
                                        </div>
                                        <div class="omni-nav-item" id="omniFilterAssigned"
                                            onclick="setOmniFolder('ASSIGNED')">
                                            <span class="omni-nav-name">Assigned chats</span>
                                            <span class="omni-nav-badge" id="omniBadgeAssigned">0</span>
                                        </div>
                                        <div class="omni-nav-item" id="omniFilterUnresponded"
                                            onclick="setOmniFolder('UNRESPONDED')">
                                            <span class="omni-nav-name">Unresponded</span>
                                            <span class="omni-nav-badge badge-unread" id="omniBadgeUnresponded">0</span>
                                        </div>
                                        <div class="omni-nav-item" id="omniFilterResolved"
                                            onclick="setOmniFolder('RESOLVED')">
                                            <span class="omni-nav-name">Resolved</span>
                                            <span class="omni-nav-badge" id="omniBadgeResolved">0</span>
                                        </div>
                                    </div>
                                </aside>

                                <!-- 1. VIEW CHATS: CHATLIST + CHATVIEW + DETAILS -->
                                <div class="omni-chats-wrapper flex-1 flex overflow-hidden" id="omniViewWorkspaceChats">
                                    <!-- CHAT LIST (ALL CHATS) -->
                                    <div class="omni-col-chatlist">
                                        <div class="omni-chatlist-header">
                                            <div class="omni-chatlist-top-bar">
                                                <span class="omni-chatlist-main-title" id="omniChatListHeaderTitle">All
                                                    chats</span>
                                                <div style="display:flex; align-items:center; gap:6px;">
                                                    <span class="omni-chatlist-total-count" id="omniChatListTotalCount">0
                                                        chat</span>
                                                    <button type="button" class="omni-action-btn" id="btnSyncOmniWa" onclick="syncOmniChatsFromWhatsApp(true)"
                                                        title="Sinkronkan Chat dari WhatsApp Gateway" style="padding:3px 8px; display:inline-flex; align-items:center; gap:5px; font-size:0.72rem; font-weight:700; color:#059669; border-color:#a7f3d0; background:#ecfdf5; border-radius:8px;">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                                        <span>Sync WA</span>
                                                    </button>
                                                    <button type="button" class="omni-action-btn" onclick="clearOmniTestChats()"
                                                        title="Bersihkan riwayat chat uji coba" style="padding:3px 6px; border-radius:8px;">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6" />
                                                            <path
                                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="omni-search-input-wrap">
                                                <span class="omni-search-icon">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <circle cx="11" cy="11" r="8" />
                                                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                                    </svg>
                                                </span>
                                                <input type="text" id="omniSearchInput" class="omni-search-input"
                                                    placeholder="Search name, phone, message..." oninput="filterOmniChats()">
                                            </div>
                                        </div>

                                        <div class="omni-chatlist-items" id="omniChatListContainer">
                                            <!-- Rendered via client.js -->
                                        </div>
                                    </div>

                                    <!-- ROOMCHAT (MODERN CONSOLE) -->
                                    <main class="omni-col-chatview flex-1 flex flex-col overflow-hidden" id="omniChatViewContainer">
                                        <!-- Rendered via client.js: Modern Empty State OR WhatsApp Roomchat -->
                                    </main>

                                    <!-- PANEL UPDATE FEEDBACK FORMAT KEEP -->
                                    <aside class="omni-col-details" id="omniDetailsPanelContainer">
                                        <!-- Rendered via client.js -->
                                    </aside>
                                </div>

                                <!-- 2. VIEW MASTER DATA: DATABASE PIC FU -->
                                <div class="omni-master-container" id="omniViewWorkspaceMaster" style="display:none;">
                                    <div class="omni-master-header">
                                        <div>
                                            <div class="omni-master-title">Master Data PIC Follow-Up</div>
                                            <div class="omni-master-sub">Pencocokan otomatis nomor WhatsApp dengan Nama Tim
                                                Follow-Up resmi toko cabang.</div>
                                        </div>
                                        <div class="omni-master-actions">
                                            <div class="omni-master-search-wrap">
                                                <input type="text" id="omniMasterSearchInput" class="omni-master-search-input"
                                                    placeholder="Cari nama PIC, no WA, atau cabang..."
                                                    oninput="renderOmniMasterTable()">
                                            </div>
                                            <button type="button" class="omni-btn-secondary"
                                                onclick="syncMasterDataFromKeepCSV()"
                                                title="Tarik nama & nomor PIC FU otomatis dari data keep">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <polyline points="23 4 23 10 17 10" />
                                                    <polyline points="1 20 1 14 7 14" />
                                                    <path
                                                        d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
                                                </svg>
                                                <span>Sinkronkan dari Data Keep</span>
                                            </button>
                                            <button type="button" class="omni-btn-primary" onclick="openAddPicModal()">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <line x1="12" y1="5" x2="12" y2="19" />
                                                    <line x1="5" y1="12" x2="19" y2="12" />
                                                </svg>
                                                <span>Tambah PIC Baru</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="omni-master-table-wrapper">
                                        <table class="omni-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;">No</th>
                                                    <th style="width: 170px;">Nomor WhatsApp</th>
                                                    <th>Nama Tim Follow-Up (Resmi)</th>
                                                    <th style="width: 160px;">Cabang Toko</th>
                                                    <th style="width: 130px; text-align: center;">Format Keep Terkait</th>
                                                    <th style="width: 110px; text-align: center;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="omniMasterTableBody">
                                                <!-- Rendered via client.js -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="omniPicModal" class="modal-overlay" style="display:none">
                            <div class="modal-dialog card-tegas" style="max-width: 440px;">
                                <div class="modal-header">
                                    <h3 id="omniPicModalTitle"
                                        style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">Tambah PIC FU Baru
                                    </h3>
                                    <button type="button" class="btn-close" onclick="closePicModal()">✕</button>
                                </div>
                                <div class="modal-body" style="display:flex; flex-direction:column; gap:12px;">
                                    <div>
                                        <label
                                            style="font-size:0.72rem; font-weight:700; color:#475569; display:block; margin-bottom:4px;">NOMOR
                                            WHATSAPP PIC (Contoh: 628123456789):</label>
                                        <input type="text" id="modalPicWA" class="omni-keep-select"
                                            placeholder="628xxxxxxxxxx">
                                    </div>
                                    <div>
                                        <label
                                            style="font-size:0.72rem; font-weight:700; color:#475569; display:block; margin-bottom:4px;">NAMA
                                            TIM FOLLOW UP (Contoh: SC.BJMA.05 - MEGA):</label>
                                        <input type="text" id="modalPicName" class="omni-keep-select"
                                            placeholder="Nama resmi PIC FU">
                                    </div>
                                    <div>
                                        <label
                                            style="font-size:0.72rem; font-weight:700; color:#475569; display:block; margin-bottom:4px;">CABANG
                                            TOKO:</label>
                                        <input type="text" id="modalPicCabang" class="omni-keep-select"
                                            placeholder="Contoh: BJMA, TANJUNG, SAM">
                                    </div>
                                </div>
                                <div class="modal-footer"
                                    style="display:flex; justify-content:flex-end; gap:8px; padding-top:12px; border-top:1px solid #e2e8f0;">
                                    <button type="button" class="omni-btn-secondary"
                                        onclick="closePicModal()">Batal</button>
                                    <button type="button" class="omni-btn-primary" onclick="savePicModal()">Simpan
                                        PIC</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>