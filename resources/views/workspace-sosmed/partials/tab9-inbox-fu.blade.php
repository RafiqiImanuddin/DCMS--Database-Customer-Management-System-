                <section id="tabContent9" class="tab-content {{ (int) ($activeTab ?? 1) === 9 ? 'active' : '' }} w-full h-[calc(100vh-56px)] max-h-[calc(100vh-56px)] flex flex-col overflow-hidden m-0 p-0">
                    <!-- 2. Omnichannel Workspace App Shell (Full Flex Fill 100% Height Tanpa Header Atas) -->
                    <div class="omni-app-container flex-1 min-h-0 w-full bg-white overflow-hidden flex flex-col border-0 rounded-none shadow-none">
                        <!-- MAIN WORKSPACE BODY -->
                        <div class="omni-main-body flex flex-1 min-h-0 overflow-hidden">
                            <!-- FOLDERS & NAVIGATION SIDEBAR -->
                            <aside class="omni-col-sidebar w-40 sm:w-44 lg:w-48 shrink-0 flex flex-col border-r border-slate-200 bg-slate-50/70 overflow-y-auto">
                                <!-- VIEW SWITCHER -->
                                <div class="omni-sidebar-view-switcher p-2">
                                    <button type="button" class="omni-sidebar-nav-btn active w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition" id="btnOmniTabChats"
                                        onclick="switchOmniView('CHATS')">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                        </svg>
                                        <span class="truncate">Inbox Chat</span>
                                    </button>
                                    <button type="button" class="omni-sidebar-nav-btn w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition mt-1" id="btnOmniTabMaster"
                                        onclick="switchOmniView('MASTER')">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                            <circle cx="9" cy="7" r="4" />
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                        </svg>
                                        <span class="truncate">Master PIC FU</span>
                                    </button>
                                </div>

                                <div class="omni-sidebar-divider border-t border-slate-200/80 my-1"></div>

                                <div class="omni-inbox-header px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-slate-400">
                                    <span class="omni-inbox-title">INBOX</span>
                                </div>

                                <div class="omni-folder-list px-2 space-y-1">
                                    <div class="omni-nav-item active flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition" id="omniFilterAll" onclick="setOmniFolder('ALL')">
                                        <span class="omni-nav-name truncate">All chats</span>
                                        <span class="omni-nav-badge text-[11px] font-bold px-1.5 py-0.5 rounded-md" id="omniBadgeAll">0</span>
                                    </div>
                                    <div class="omni-nav-item flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition" id="omniFilterAssigned"
                                        onclick="setOmniFolder('ASSIGNED')">
                                        <span class="omni-nav-name truncate">Assigned</span>
                                        <span class="omni-nav-badge text-[11px] font-bold px-1.5 py-0.5 rounded-md" id="omniBadgeAssigned">0</span>
                                    </div>
                                    <div class="omni-nav-item flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition" id="omniFilterUnresponded"
                                        onclick="setOmniFolder('UNRESPONDED')">
                                        <span class="omni-nav-name truncate">Unresponded</span>
                                        <span class="omni-nav-badge badge-unread text-[11px] font-bold px-1.5 py-0.5 rounded-md bg-rose-500 text-white" id="omniBadgeUnresponded">0</span>
                                    </div>
                                    <div class="omni-nav-item flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition" id="omniFilterResolved"
                                        onclick="setOmniFolder('RESOLVED')">
                                        <span class="omni-nav-name truncate">Resolved</span>
                                        <span class="omni-nav-badge text-[11px] font-bold px-1.5 py-0.5 rounded-md" id="omniBadgeResolved">0</span>
                                    </div>
                                </div>
                            </aside>

                            <!-- 1. VIEW CHATS: CHATLIST + CHATVIEW + DETAILS -->
                            <div class="omni-chats-wrapper flex-1 min-h-0 flex overflow-hidden" id="omniViewWorkspaceChats">
                                <!-- CHAT LIST (ALL CHATS) -->
                                <div class="omni-col-chatlist w-72 lg:w-80 shrink-0 flex flex-col border-r border-slate-200 bg-white min-h-0">
                                    <div class="omni-chatlist-header p-2.5 border-b border-slate-200 bg-white shrink-0">
                                        <div class="omni-chatlist-top-bar flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-1.5">
                                                <span class="omni-chatlist-main-title text-xs font-black text-slate-900 uppercase tracking-tight" id="omniChatListHeaderTitle">All chats</span>
                                                <span class="omni-chatlist-total-count text-[11px] font-bold text-slate-500" id="omniChatListTotalCount">0</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="omni-action-btn" id="btnSyncOmniWa" onclick="syncOmniChatsFromWhatsApp(true)"
                                                    title="Sinkronkan Chat dari WhatsApp Gateway" style="padding:2px 7px; display:inline-flex; align-items:center; gap:4px; font-size:0.70rem; font-weight:700; color:#059669; border-color:#a7f3d0; background:#ecfdf5; border-radius:6px;">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                                    <span>Sync WA</span>
                                                </button>
                                                <button type="button" class="omni-action-btn" onclick="syncOmniSenderNames(true)"
                                                    title="Sinkronkan Nama Pengirim WhatsApp" style="padding:2px 6px; display:inline-flex; align-items:center; gap:3px; font-size:0.70rem; font-weight:700; color:#4338ca; border-color:#c7d2fe; background:#eef2ff; border-radius:6px;">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                                                    <span>Sync Nama</span>
                                                </button>
                                                <button type="button" class="omni-action-btn" onclick="clearOmniTestChats()"
                                                    title="Bersihkan riwayat chat uji coba" style="padding:2px 5px; border-radius:6px;">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="omni-search-input-wrap relative">
                                            <span class="omni-search-icon absolute left-2.5 top-2 text-slate-400">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <circle cx="11" cy="11" r="8" />
                                                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                                </svg>
                                            </span>
                                            <input type="text" id="omniSearchInput" name="search_chat_filter_unique" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" class="omni-search-input w-full bg-slate-50 border border-slate-200 text-slate-900 rounded-lg pl-8 pr-3 py-1.5 text-xs focus:outline-none focus:border-slate-400 focus:bg-white transition"
                                                placeholder="Search name, phone, message..." oninput="filterOmniChats()">
                                        </div>
                                    </div>

                                    <div class="omni-chatlist-items flex-1 min-h-0 overflow-y-auto" id="omniChatListContainer">
                                        <!-- Rendered via client.js -->
                                    </div>
                                </div>

                                <!-- ROOMCHAT (MODERN CONSOLE) -->
                                <div class="omni-col-chatview flex-1 min-h-0 flex flex-col bg-slate-100/70 overflow-hidden relative h-full" id="omniChatViewContainer" style="display: flex !important; flex-direction: column !important; height: 100% !important; min-height: 0 !important; overflow: hidden !important; position: relative !important;">
                                    <!-- Rendered via client.js: Modern Empty State OR WhatsApp Roomchat -->
                                </div>

                                <!-- PANEL UPDATE FEEDBACK FORMAT KEEP -->
                                <aside class="omni-col-details w-72 2xl:w-80 shrink-0 flex flex-col border-l border-slate-200 bg-white min-h-0" id="omniDetailsPanelContainer">
                                    <!-- Rendered via client.js -->
                                </aside>
                            </div>

                            <!-- 2. VIEW MASTER DATA: DATABASE PIC FU -->
                            <div class="omni-master-container flex-1 min-h-0 flex flex-col bg-white overflow-hidden" id="omniViewWorkspaceMaster" style="display:none;">
                                <div class="omni-master-header p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
                                    <div>
                                        <div class="omni-master-title text-base font-black text-slate-900">Master Data PIC Follow-Up</div>
                                        <div class="omni-master-sub text-xs text-slate-500">Pencocokan otomatis nomor WhatsApp dengan Nama Tim Follow-Up resmi toko cabang.</div>
                                    </div>
                                    <div class="omni-master-actions flex items-center gap-2 flex-wrap">
                                        <div class="omni-master-search-wrap">
                                            <input type="text" id="omniMasterSearchInput" class="omni-master-search-input text-xs px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none"
                                                placeholder="Cari nama PIC, no WA, atau cabang..."
                                                oninput="renderOmniMasterTable()">
                                        </div>
                                        <button type="button" class="omni-btn-secondary text-xs px-3 py-1.5 rounded-lg font-bold border border-slate-200 hover:bg-slate-50"
                                            onclick="syncMasterDataFromKeepCSV()"
                                            title="Tarik nama & nomor PIC FU otomatis dari data keep">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1"><polyline points="23 4 23 10 17 10" /><polyline points="1 20 1 14 7 14" /><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" /></svg>
                                            <span>Sinkronkan dari Data Keep</span>
                                        </button>
                                        <button type="button" class="omni-btn-primary text-xs px-3 py-1.5 rounded-lg font-bold bg-slate-900 text-white hover:bg-slate-800" onclick="openAddPicModal()">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1"><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                            <span>Tambah PIC Baru</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="omni-master-table-wrapper flex-1 min-h-0 overflow-y-auto p-4">
                                    <table class="omni-table w-full text-left text-xs">
                                        <thead class="sticky top-0 bg-slate-50 border-b border-slate-200 z-10">
                                            <tr>
                                                <th style="width: 50px;" class="py-2.5 px-3">No</th>
                                                <th style="width: 170px;" class="py-2.5 px-3">Nomor WhatsApp</th>
                                                <th class="py-2.5 px-3">Nama Tim Follow-Up (Resmi)</th>
                                                <th style="width: 160px;" class="py-2.5 px-3">Cabang Toko</th>
                                                <th style="width: 130px; text-align: center;" class="py-2.5 px-3">Format Keep</th>
                                                <th style="width: 110px; text-align: center;" class="py-2.5 px-3">Aksi</th>
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
                </section>
