{{-- Floating Live WhatsApp Widget (Global on All Pages) --}}
<style>
/* DCMS Live WhatsApp Widget Styles */
#dcmsLiveWidgetContainer {
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    box-sizing: border-box;
}
#dcmsLiveWidgetContainer * {
    box-sizing: border-box;
}
.dcms-lw-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.45), 0 8px 10px -6px rgba(16, 185, 129, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 99998;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    border: 2px solid rgba(255, 255, 255, 0.3);
    outline: none;
}
.dcms-lw-fab:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 14px 30px -5px rgba(16, 185, 129, 0.55), 0 10px 14px -6px rgba(16, 185, 129, 0.35);
}
.dcms-lw-fab:active {
    transform: scale(0.95);
}
.dcms-lw-fab-badge {
    position: absolute;
    top: -3px;
    right: -3px;
    background: #ef4444;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    min-width: 22px;
    height: 22px;
    line-height: 22px;
    padding: 0 6px;
    border-radius: 11px;
    text-align: center;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.5);
    display: none;
    animation: dcmsLwPulse 2s infinite;
}
@keyframes dcmsLwPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.15); box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.25); }
    100% { transform: scale(1); }
}
.dcms-lw-window {
    position: fixed;
    bottom: 96px;
    right: 24px;
    width: 410px;
    max-width: calc(100vw - 32px);
    height: 580px;
    max-height: calc(100vh - 120px);
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(15, 23, 42, 0.05);
    display: none;
    flex-direction: column;
    overflow: hidden;
    z-index: 99999;
    transform-origin: bottom right;
    animation: dcmsLwOpen 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes dcmsLwOpen {
    from { opacity: 0; transform: scale(0.92) translateY(16px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.dcms-lw-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #ffffff;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.dcms-lw-btn-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
    outline: none;
}
.dcms-lw-btn-icon:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}
.dcms-lw-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #f8fafc;
    overflow: hidden;
    position: relative;
}
.dcms-lw-list-search {
    padding: 10px 14px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
}
.dcms-lw-search-input {
    width: 100%;
    padding: 7px 12px;
    font-size: 12px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    outline: none;
    background: #f1f5f9;
    transition: border 0.15s, background 0.15s;
}
.dcms-lw-search-input:focus {
    border-color: #10b981;
    background: #ffffff;
}
.dcms-lw-chats-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 8px 10px;
}
.dcms-lw-chat-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    margin-bottom: 6px;
    cursor: pointer;
    transition: all 0.15s;
}
.dcms-lw-chat-card:hover {
    background: #f0fdf4;
    border-color: #a7f3d0;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.dcms-lw-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
}
.dcms-lw-chat-room {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #efeae2;
    background-image: radial-gradient(#d1d5db 0.75px, transparent 0.75px);
    background-size: 16px 16px;
    overflow: hidden;
}
.dcms-lw-room-header {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.dcms-lw-full-inbox-banner {
    background: #ecfdf5;
    border-bottom: 1px solid #a7f3d0;
    padding: 8px 12px;
    font-size: 11px;
    color: #065f46;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.dcms-lw-full-inbox-banner a {
    color: #047857;
    font-weight: 700;
    text-decoration: underline;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.dcms-lw-messages-stream {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.dcms-lw-bubble {
    max-width: 86%;
    padding: 8px 12px;
    border-radius: 13px;
    font-size: 12.5px;
    line-height: 1.5;
    word-break: break-word;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    position: relative;
}
.dcms-lw-bubble-in {
    align-self: flex-start;
    background: #ffffff;
    color: #1e293b;
    border-bottom-left-radius: 3px;
    border: 1px solid #f1f5f9;
}
.dcms-lw-bubble-out {
    align-self: flex-end;
    background: #d9fdd3;
    color: #0f172a;
    border-bottom-right-radius: 3px;
}
.dcms-lw-bubble-text {
    line-height: 1.55;
    word-break: break-word;
    letter-spacing: 0.01em;
}
.dcms-lw-bubble-text strong {
    font-weight: 700;
}
.dcms-lw-bubble-text em {
    font-style: italic;
}
.dcms-lw-bubble-text del {
    text-decoration: line-through;
    opacity: 0.75;
}
.dcms-lw-msg-label {
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.02em;
    display: inline;
}
.dcms-lw-bubble-out .dcms-lw-msg-label {
    color: #065f46;
}
.dcms-lw-bubble-in .dcms-lw-msg-label {
    color: #0369a1;
}
.dcms-lw-msg-link {
    color: #0284c7;
    text-decoration: underline;
    word-break: break-all;
}
.dcms-lw-msg-link:hover {
    color: #0369a1;
}
.dcms-lw-code-inline {
    background: rgba(0, 0, 0, 0.06);
    padding: 1px 4px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 11.5px;
}
.dcms-lw-code-block {
    background: rgba(0, 0, 0, 0.07);
    padding: 6px 8px;
    border-radius: 6px;
    font-family: monospace;
    font-size: 11px;
    overflow-x: auto;
    margin: 4px 0;
    white-space: pre-wrap;
}
.dcms-lw-bubble-meta {
    font-size: 9.5px;
    color: #64748b;
    text-align: right;
    margin-top: 3px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 3px;
}
.dcms-lw-input-bar {
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    padding: 8px 10px;
    display: flex;
    align-items: flex-end;
    gap: 8px;
}
.dcms-lw-input-bar textarea,
.dcms-lw-input-bar input {
    flex: 1;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 8px 12px;
    font-size: 12px;
    outline: none;
    background: #f8fafc;
    transition: all 0.15s;
    resize: none;
    height: 38px;
    max-height: 110px;
    line-height: 1.45;
    font-family: inherit;
    box-sizing: border-box;
}
.dcms-lw-input-bar textarea:focus,
.dcms-lw-input-bar input:focus {
    border-color: #10b981;
    background: #ffffff;
}
.dcms-lw-btn-send {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #10b981;
    color: #ffffff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
    outline: none;
    flex-shrink: 0;
}
.dcms-lw-btn-send:hover {
    background: #059669;
    transform: scale(1.06);
}
.dcms-lw-btn-send:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    transform: none;
}
.dcms-lw-toast {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    background: #1e293b;
    color: #ffffff;
    font-size: 11px;
    padding: 6px 14px;
    border-radius: 20px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
    display: none;
    z-index: 100;
    animation: dcmsLwToast 0.2s ease;
}
@keyframes dcmsLwToast {
    from { opacity: 0; transform: translate(-50%, -8px); }
    to { opacity: 1; transform: translate(-50%, 0); }
}

/* Notifikasi Pop-Up Chat Baru Mengambang */
.dcms-lw-notify-popup {
    position: fixed;
    bottom: 96px;
    right: 24px;
    width: 340px;
    max-width: calc(100vw - 32px);
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #10b981;
    border-radius: 16px;
    box-shadow: 0 20px 35px -8px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(0, 0, 0, 0.04);
    padding: 12px 14px;
    cursor: pointer;
    z-index: 99998;
    overflow: hidden;
    animation: dcmsLwSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    transition: transform 0.15s, box-shadow 0.15s, right 0.2s, bottom 0.2s;
}
.dcms-lw-notify-popup:hover {
    transform: translateY(-2px);
    box-shadow: 0 25px 40px -8px rgba(15, 23, 42, 0.28);
}
@keyframes dcmsLwSlideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.dcms-lw-notify-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.dcms-lw-notify-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(16, 185, 129, 0.35);
}
.dcms-lw-notify-body {
    flex: 1;
    min-width: 0;
}
.dcms-lw-notify-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2px;
    padding-right: 20px;
}
.dcms-lw-notify-badge {
    background: #ecfdf5;
    color: #047857;
    font-size: 9.5px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 10px;
    border: 1px solid #a7f3d0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.dcms-lw-notify-badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    animation: dcmsLwPulse 1.5s infinite;
}
@keyframes dcmsLwPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.8); }
}
.dcms-lw-notify-time {
    font-size: 10px;
    color: #94a3b8;
}
.dcms-lw-notify-sender {
    font-weight: 700;
    font-size: 13px;
    color: #0f172a;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-top: 3px;
}
.dcms-lw-notify-text {
    font-size: 11.5px;
    color: #475569;
    line-height: 1.45;
    margin-top: 3px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
}
.dcms-lw-notify-hint {
    font-size: 10px;
    color: #10b981;
    font-weight: 600;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.dcms-lw-notify-close {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
    outline: none;
    z-index: 2;
}
.dcms-lw-notify-close:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.dcms-lw-notify-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, #10b981, #047857);
    width: 100%;
    transform-origin: left;
    animation: dcmsLwProgress 7s linear forwards;
}
@keyframes dcmsLwProgress {
    from { width: 100%; }
    to { width: 0%; }
}
</style>

<div id="dcmsLiveWidgetContainer">
    {{-- Pop-Up Notifikasi Pesan Masuk Mengambang --}}
    <div id="dcmsLwNotifyPopup" class="dcms-lw-notify-popup" style="display:none;" onclick="handleDcmsLwPopupClick()" title="Klik untuk membuka pesan">
        <button type="button" class="dcms-lw-notify-close" onclick="closeDcmsLwNotifyPopup(event)" title="Tutup Notifikasi">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="dcms-lw-notify-content">
            <div class="dcms-lw-notify-avatar" id="dcmsLwNotifyAvatar">WA</div>
            <div class="dcms-lw-notify-body">
                <div class="dcms-lw-notify-header">
                    <span class="dcms-lw-notify-badge">
                        <span class="dcms-lw-notify-badge-dot"></span>
                        Chat WhatsApp Baru
                    </span>
                    <span class="dcms-lw-notify-time" id="dcmsLwNotifyTime">Baru saja</span>
                </div>
                <div class="dcms-lw-notify-sender" id="dcmsLwNotifySender">Customer</div>
                <div class="dcms-lw-notify-text" id="dcmsLwNotifyText">Pesan baru masuk...</div>
                <div class="dcms-lw-notify-hint">
                    <span>Klik untuk membalas</span>
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </div>
        </div>
        <div class="dcms-lw-notify-progress" id="dcmsLwNotifyProgress"></div>
    </div>
    {{-- Floating Action Button (FAB) --}}
    <button type="button" id="dcmsLwFab" class="dcms-lw-fab" onclick="toggleDcmsLiveWidget()" title="Live WhatsApp Inbox (Follow-Up)" aria-label="Buka Live Chat WhatsApp">
        <span id="dcmsLwBadge" class="dcms-lw-fab-badge">0</span>
        {{-- Default Chat Icon --}}
        <svg id="dcmsLwFabIconChat" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
        </svg>
        {{-- Close Icon when opened --}}
        <svg id="dcmsLwFabIconClose" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>

    {{-- Floating Window Popup --}}
    <div id="dcmsLwWindow" class="dcms-lw-window">
        {{-- Header --}}
        <div class="dcms-lw-header">
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="display:inline-block; width:9px; height:9px; border-radius:50%; background:#10b981; box-shadow:0 0 8px #10b981;"></span>
                <div>
                    <div style="font-weight:800; font-size:13px; letter-spacing:-0.2px; line-height:1.2;">Live WhatsApp Inbox</div>
                    <div id="dcmsLwGatewaySub" style="font-size:10px; color:#94a3b8;">WA Bot: 0851-1142-6699</div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                {{-- Sync Button --}}
                <button type="button" class="dcms-lw-btn-icon" onclick="syncDcmsLiveWidget()" title="Sinkronisasi Pesan WhatsApp Baru">
                    <svg id="dcmsLwSyncIcon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                </button>
                {{-- Open Full Inbox FU Link --}}
                <a href="{{ \App\Support\RouteCipher::url('workspace-sosmed.index', ['tab' => 9]) }}" 
                   onclick="return handleOpenFullInbox(event)" 
                   class="dcms-lw-btn-icon" 
                   title="Buka Modul Inbox Follow-Up Lengkap (Kirim File / Format Keep)" 
                   style="text-decoration:none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                </a>
                {{-- Sembunyikan / Nonaktifkan Widget --}}
                <button type="button" class="dcms-lw-btn-icon" onclick="disableDcmsLiveWidgetDirectly()" title="Nonaktifkan Widget Ini dari Layar (Bisa diaktifkan lagi di Hak Akses / Pengaturan Profil)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
                {{-- Minimize Button --}}
                <button type="button" class="dcms-lw-btn-icon" onclick="toggleDcmsLiveWidget(false)" title="Tutup Jendela">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Toast Inside Widget --}}
        <div id="dcmsLwToast" class="dcms-lw-toast">Pesan terkirim</div>

        {{-- Body --}}
        <div class="dcms-lw-body">
            {{-- VIEW 1: CONVERSATIONS LIST --}}
            <div id="dcmsLwViewList" style="display:flex; flex-direction:column; height:100%;">
                <div class="dcms-lw-list-search">
                    <input type="text" id="dcmsLwSearch" class="dcms-lw-search-input" placeholder="Cari kontak WhatsApp atau isi pesan..." oninput="filterDcmsLiveWidgetChats(this.value)">
                </div>
                <div id="dcmsLwChatsList" class="dcms-lw-chats-scroll">
                    {{-- Dynamically populated --}}
                </div>
            </div>

            {{-- VIEW 2: CHAT ROOM VIEW --}}
            <div id="dcmsLwViewRoom" class="dcms-lw-chat-room" style="display:none;">
                <div class="dcms-lw-room-header">
                    <button type="button" onclick="closeDcmsLiveWidgetRoom()" class="dcms-lw-btn-icon" style="background:#f1f5f9; color:#475569;" title="Kembali ke Daftar Percakapan">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <div style="flex:1; min-width:0;">
                        <div id="dcmsLwActiveName" style="font-weight:700; font-size:12.5px; color:#0f172a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">Customer</div>
                        <div id="dcmsLwActivePhone" style="font-size:10px; color:#64748b; font-family:monospace;">628xxxx</div>
                    </div>
                </div>

                {{-- Banner Redirect jika butuh kirim file atau respon kompleks --}}
                <div class="dcms-lw-full-inbox-banner">
                    <span>💡 Perlu kirim file, foto, atau format keep?</span>
                    <a id="dcmsLwFullLink" href="{{ \App\Support\RouteCipher::url('workspace-sosmed.index', ['tab' => 9]) }}" onclick="return handleOpenFullInbox(event)">
                        Buka Inbox FU ↗
                    </a>
                </div>

                {{-- Message stream --}}
                <div id="dcmsLwMessagesStream" class="dcms-lw-messages-stream">
                    {{-- Messages rendered here --}}
                </div>

                {{-- Input Bar --}}
                <div class="dcms-lw-input-bar">
                    <textarea id="dcmsLwInput" rows="1" placeholder="Ketik pesan... (Enter kirim, Shift+Enter baris baru)" onkeydown="handleDcmsLiveWidgetKey(event)" oninput="this.style.height='auto'; this.style.height=Math.min(this.scrollHeight, 110)+'px';"></textarea>
                    <button type="button" id="dcmsLwBtnSend" class="dcms-lw-btn-send" onclick="sendDcmsLiveWidgetReply()" title="Kirim Balasan (Enter)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let dcmsLwIsOpen = false;
    let dcmsLwActivePhone = null;
    let dcmsLwActiveName = null;
    let dcmsLwMessages = [];
    let dcmsLwLastSync = 0;
    let dcmsLwPollingTimer = null;
    let dcmsLwPopupTimeout = null;
    let dcmsLwLastPopupMsg = null;

    // Sembunyikan FAB jika sedang berada di Tab 9 (Inbox FU) pada halaman Workspace Sosmed
    function checkTabVisibility() {
        const fab = document.getElementById('dcmsLwFab');
        const win = document.getElementById('dcmsLwWindow');
        const popup = document.getElementById('dcmsLwNotifyPopup');
        if (!fab) return;
        if (typeof window.currentActiveTab !== 'undefined' && window.currentActiveTab === 9) {
            fab.style.display = 'none';
            if (win) win.style.display = 'none';
            if (popup) popup.style.display = 'none';
        } else {
            fab.style.display = 'flex';
        }
    }

    window.toggleDcmsLiveWidget = function(force) {
        const win = document.getElementById('dcmsLwWindow');
        const iconChat = document.getElementById('dcmsLwFabIconChat');
        const iconClose = document.getElementById('dcmsLwFabIconClose');
        const popup = document.getElementById('dcmsLwNotifyPopup');
        if (!win) return;

        dcmsLwIsOpen = (typeof force === 'boolean') ? force : !dcmsLwIsOpen;

        if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
            Notification.requestPermission().catch(() => {});
        }

        if (dcmsLwIsOpen) {
            win.style.display = 'flex';
            if (iconChat) iconChat.style.display = 'none';
            if (iconClose) iconClose.style.display = 'block';
            if (popup && popup.style.display !== 'none') {
                popup.style.right = '440px';
            }
            loadDcmsLiveWidgetData();
        } else {
            win.style.display = 'none';
            if (iconChat) iconChat.style.display = 'block';
            if (iconClose) iconClose.style.display = 'none';
            if (popup && popup.style.display !== 'none') {
                popup.style.right = '24px';
            }
        }
    };

    window.handleOpenFullInbox = function(e) {
        if (typeof window.switchWorkspaceTab === 'function') {
            e.preventDefault();
            window.switchWorkspaceTab(9);
            toggleDcmsLiveWidget(false);
            return false;
        }
        return true;
    };

    window.disableDcmsLiveWidgetDirectly = async function() {
        if (!confirm('Apakah Anda ingin menonaktifkan Live Chat Widget WhatsApp ini dari layar Anda?\n\nAnda dapat mengaktifkannya kembali kapan saja melalui menu Pengaturan Admin > Hak Akses Akun atau Pengaturan Akun.')) {
            return;
        }

        try {
            const res = await fetch('{{ route("settings.profile.toggle_live_chat_widget") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ enabled: false })
            });
            const data = await res.json();
            if (data.success) {
                const container = document.getElementById('dcmsLiveWidgetContainer');
                if (container) container.remove();
                if (typeof showSaveToast === 'function') {
                    showSaveToast('Live Chat Widget dinonaktifkan.');
                } else {
                    alert('Live Chat Widget berhasil dinonaktifkan dari layar.');
                }
            }
        } catch (e) {
            location.reload();
        }
    };

    function playLiveWidgetChime() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(659.25, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.14);
            gain.gain.setValueAtTime(0.08, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.25);
        } catch (e) {}
    }

    function showWidgetToast(text) {
        const t = document.getElementById('dcmsLwToast');
        if (!t) return;
        t.innerText = text;
        t.style.display = 'block';
        setTimeout(() => { t.style.display = 'none'; }, 2500);
    }

    function normalizePhone(p) {
        if (!p) return '';
        let clean = String(p).replace(/@.*$/, '').replace(/\D/g, '');
        if (clean.startsWith('08')) clean = '62' + clean.slice(1);
        else if (clean.startsWith('8')) clean = '62' + clean;
        return clean;
    }

    function formatTime(ts) {
        if (!ts) return '';
        const d = new Date(Number(ts) < 1e11 ? Number(ts) * 1000 : (isNaN(ts) ? ts : Number(ts)));
        if (isNaN(d.getTime())) return '';
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function formatDcmsLiveMessage(rawText) {
        if (!rawText) return '';
        let text = String(rawText);

        // Sisipkan newline jika ada label template keep yang terhimpit tanpa enter di depannya
        text = text.replace(/([^\n\r])\s*(tanggal\s*keep|nama\s*customer|nomor\s*whatsapp|nomor\s*wa|barang\s*keep|barang|catatan|remaks|remarks|cabang|customer|handover|pesan\s*terakhir):/gi, '$1\n$2:');

        // Escape HTML untuk mencegah XSS
        let escaped = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        // Monospace code blocks ```code```
        escaped = escaped.replace(/```([\s\S]*?)```/g, function(match, code) {
            return '<pre class="dcms-lw-code-block">' + code.trim() + '</pre>';
        });

        // Inline code `code`
        escaped = escaped.replace(/`([^`\n]+)`/g, '<code class="dcms-lw-code-inline">$1</code>');

        // WhatsApp Bold: *text*
        escaped = escaped.replace(/(^|[^\w*])\*([^*\n\r]+?)\*(?=[^\w*]|$)/g, '$1<strong>$2</strong>');

        // WhatsApp Italic: _text_
        escaped = escaped.replace(/(^|[^\w_])_([^_\n\r]+?)_(?=[^\w_]|$)/g, '$1<em>$2</em>');

        // WhatsApp Strikethrough: ~text~
        escaped = escaped.replace(/(^|[^\w~])~([^~\n\r]+?)~(?=[^\w~]|$)/g, '$1<del>$2</del>');

        // Auto-link URLs
        escaped = escaped.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener noreferrer" class="dcms-lw-msg-link">$1</a>');

        // Format label field agar tampak tebal & terstruktur rapi
        escaped = escaped.replace(/(^|\n|\r)(tanggal\s*keep|nama\s*customer|nomor\s*whatsapp|nomor\s*wa|barang\s*keep|barang|catatan|remaks|remarks|cabang|customer|handover|pesan\s*terakhir):\s*/gi, function(match, prefix, label) {
            return prefix + '<span class="dcms-lw-msg-label">' + label.toUpperCase() + ':</span> ';
        });

        // Konversi newline (\n) menjadi <br>
        escaped = escaped.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        escaped = escaped.replace(/\n{3,}/g, '\n\n');
        escaped = escaped.replace(/\n/g, '<br>');

        return escaped;
    }

    function triggerDcmsLwPopup(msg) {
        if (!msg) return;

        const normPhone = normalizePhone(msg.phone);
        const win = document.getElementById('dcmsLwWindow');
        const isWinOpen = win && win.style.display === 'flex';

        // Jika room chat dengan pengirim ini sedang dibuka langsung, jangan tampilkan popup agar tidak mengganggu
        if (isWinOpen && dcmsLwActivePhone === normPhone) {
            return;
        }

        dcmsLwLastPopupMsg = msg;

        const popup = document.getElementById('dcmsLwNotifyPopup');
        const senderEl = document.getElementById('dcmsLwNotifySender');
        const textEl = document.getElementById('dcmsLwNotifyText');
        const avatarEl = document.getElementById('dcmsLwNotifyAvatar');
        const timeEl = document.getElementById('dcmsLwNotifyTime');
        const progressEl = document.getElementById('dcmsLwNotifyProgress');
        if (!popup) return;

        const senderName = msg.senderName || ('PIC ' + (msg.phone || 'WhatsApp'));
        const cleanMsg = (msg.message || (msg.media_type ? ('[' + msg.media_type.toUpperCase() + ' Foto/Dokumen]') : 'Pesan baru masuk'))
            .replace(/[\r\n]+/g, ' ')
            .replace(/[*_~`]/g, '');

        if (senderEl) senderEl.innerText = senderName;
        if (textEl) textEl.innerText = cleanMsg;
        if (avatarEl) avatarEl.innerText = (senderName || 'WA').slice(0, 2).toUpperCase();
        if (timeEl) timeEl.innerText = formatTime(msg.timestamp) || 'Baru saja';

        // Sesuaikan posisi jika window chat sedang terbuka
        if (isWinOpen) {
            popup.style.right = '440px';
        } else {
            popup.style.right = '24px';
        }

        // Reset progress bar animation
        if (progressEl) {
            progressEl.style.animation = 'none';
            void progressEl.offsetWidth; // trigger reflow
            progressEl.style.animation = 'dcmsLwProgress 7s linear forwards';
        }

        popup.style.display = 'block';

        // Auto close setelah 7 detik
        if (dcmsLwPopupTimeout) clearTimeout(dcmsLwPopupTimeout);
        dcmsLwPopupTimeout = setTimeout(() => {
            closeDcmsLwNotifyPopup();
        }, 7000);

        // Web Notification browser jika diizinkan
        if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
            try {
                const notif = new Notification('Chat Masuk: ' + senderName, {
                    body: cleanMsg,
                    icon: '/favicon.ico'
                });
                notif.onclick = function() {
                    window.focus();
                    handleDcmsLwPopupClick();
                };
            } catch (e) {}
        }
    }

    window.closeDcmsLwNotifyPopup = function(e) {
        if (e && typeof e.stopPropagation === 'function') e.stopPropagation();
        const popup = document.getElementById('dcmsLwNotifyPopup');
        if (popup) popup.style.display = 'none';
        if (dcmsLwPopupTimeout) clearTimeout(dcmsLwPopupTimeout);
    };

    window.handleDcmsLwPopupClick = function() {
        if (!dcmsLwLastPopupMsg) return;
        const phone = normalizePhone(dcmsLwLastPopupMsg.phone);
        const name = dcmsLwLastPopupMsg.senderName || ('PIC ' + phone);

        closeDcmsLwNotifyPopup();

        // Buka widget window jika belum terbuka
        const win = document.getElementById('dcmsLwWindow');
        if (win && win.style.display !== 'flex') {
            toggleDcmsLiveWidget(true);
        }

        // Buka chat room pengirim
        openDcmsLiveWidgetRoom(phone, name);
    };

    async function loadDcmsLiveWidgetData(isAuto) {
        try {
            const res = await fetch('/api/watzap-webhook?sync=1');
            if (!res.ok) return;
            const data = await res.json();
            if (!data.success) return;

            if (data.gateway_phone) {
                const sub = document.getElementById('dcmsLwGatewaySub');
                if (sub) {
                    let formatted = String(data.gateway_phone);
                    if (formatted.startsWith('62')) formatted = '0' + formatted.slice(2);
                    sub.innerHTML = 'WA Bot: <strong style="color:#a7f3d0;">' + formatted + '</strong>';
                }
            }

            const newMessages = data.messages || [];
            if (newMessages.length > 0) {
                const existingMap = {};
                dcmsLwMessages.forEach(m => { if (m.messageId) existingMap[m.messageId] = true; });

                let hasNewInbound = false;
                let latestInboundMsg = null;
                newMessages.forEach(m => {
                    if (m.messageId && !existingMap[m.messageId]) {
                        dcmsLwMessages.push(m);
                        if (m.direction === 'inbound') {
                            hasNewInbound = true;
                            latestInboundMsg = m;
                        }

                        // Sinkronkan juga pesan baru dari webhook langsung ke gadgetmart_omni_threads
                        try {
                            const threads = JSON.parse(localStorage.getItem('gadgetmart_omni_threads') || '{}');
                            const ph = normalizePhone(m.phone);
                            if (ph) {
                                if (!threads[ph]) threads[ph] = [];
                                const threadExists = threads[ph].some(tMsg => tMsg.id === m.messageId);
                                if (!threadExists) {
                                    threads[ph].push({
                                        id: m.messageId,
                                        sender: m.direction === 'inbound' ? (m.sender || 'PIC') : 'ADMIN',
                                        senderName: m.senderName || (m.direction === 'inbound' ? 'PIC' : 'Admin'),
                                        text: m.message || '',
                                        message: m.message || '',
                                        timestamp: m.timestamp || Date.now(),
                                        mediaUrl: m.media_url || null,
                                        messageType: m.media_type || 'text',
                                        fromMe: m.direction !== 'inbound'
                                    });
                                    if (typeof saveOmniThreads === 'function') {
                                        saveOmniThreads(threads);
                                    } else {
                                        localStorage.setItem('gadgetmart_omni_threads', JSON.stringify(threads));
                                    }
                                    if (typeof renderOmniInboxModule === 'function') {
                                        renderOmniInboxModule();
                                    }
                                }
                            }
                        } catch (eOmni) {}
                    }
                });

                dcmsLwLastSync = Date.now();
                if (hasNewInbound && isAuto) {
                    playLiveWidgetChime();
                    if (latestInboundMsg) {
                        triggerDcmsLwPopup(latestInboundMsg);
                    }
                }
            }

            renderChatsList();
            updateUnreadBadge();

            // Jika sedang membuka room chat, perbarui pesan di stream
            if (dcmsLwActivePhone) {
                renderActiveRoomMessages();
            }
        } catch (e) {
            console.warn('Live Widget sync error:', e);
        }
    }

    function formatDcmsPhoneDisplay(p) {
        if (!p) return '-';
        let clean = String(p).replace(/@.*$/, '').replace(/\D/g, '');
        if (clean.startsWith('08')) clean = '62' + clean.slice(1);
        else if (clean.startsWith('8')) clean = '62' + clean;
        if (clean.startsWith('628') && clean.length >= 10) {
            return '+62 ' + clean.slice(2, 5) + '-' + clean.slice(5, 9) + '-' + clean.slice(9);
        }
        return '+' + clean;
    }

    function getDcmsLwInitials(name) {
        if (!name) return 'WA';
        const clean = name.replace(/[^a-zA-Z0-9\s]/g, '').trim();
        const parts = clean.split(/\s+/).filter(Boolean);
        if (parts.length >= 2) {
            return (parts[0][0] + parts[1][0]).toUpperCase();
        }
        return clean.slice(0, 2).toUpperCase() || 'WA';
    }

    function getGroupedChats() {
        // PRIORITAS 1: Sinkronisasi 100% langsung dengan engine Tab 9 (Inbox Follow-Up) jika getOmniPicChats tersedia
        if (typeof getOmniPicChats === 'function') {
            try {
                const omniChats = getOmniPicChats();
                if (Array.isArray(omniChats) && omniChats.length > 0) {
                    return omniChats.map(c => {
                        const targetPh = normalizePhone(c.picWA || c.cleanKey || c.key);
                        return {
                            phone: targetPh,
                            name: c.picName || formatDcmsPhoneDisplay(targetPh),
                            lastMessage: c.lastMessageText || '',
                            lastTime: c.latestEpochMs || 0,
                            lastDirection: (c.lastSender === 'ADMIN' || c.lastSender === 'admin') ? 'outbound' : 'inbound',
                            cabang: c.cabang || '-',
                            isUnresponded: Boolean(c.isUnresponded),
                            isResolved: Boolean(c.isResolved),
                            pendingCount: c.pendingCount || 0,
                            messages: (c.messages || []).map(m => ({
                                messageId: m.id || ('omni_' + (m.timestamp || Date.now())),
                                phone: targetPh,
                                message: m.text || m.message || '',
                                timestamp: m.timestamp || Date.now(),
                                direction: (m.sender === 'ADMIN' || m.sender === 'admin' || m.fromMe) ? 'outbound' : 'inbound',
                                sender: (m.sender === 'ADMIN' || m.sender === 'admin' || m.fromMe) ? 'ADMIN' : 'PIC',
                                senderName: m.senderName || c.picName || 'PIC',
                                media_url: m.mediaUrl || m.media_url || null,
                                media_type: m.messageType || m.media_type || null,
                                reply_to: m.replyTo || m.reply_to || null
                            }))
                        };
                    });
                }
            } catch (e) {
                console.warn('getOmniPicChats bridge warning:', e);
            }
        }

        // PRIORITAS 2: Baca dari localStorage gadgetmart_omni_threads (Inbox Follow-Up) agar data selalu sinkron
        const omniThreads = (() => {
            try { return JSON.parse(localStorage.getItem('gadgetmart_omni_threads') || '{}'); } catch(e) { return {}; }
        })();

        const groups = {};

        // 1. Muat seluruh percakapan yang ada di Inbox Follow-Up
        Object.keys(omniThreads).forEach(k => {
            const ph = normalizePhone(k);
            if (!ph) return;
            const msgs = omniThreads[k] || [];
            if (msgs.length === 0) return;

            const lastM = msgs[msgs.length - 1];
            let name = 'PIC ' + ph;
            let cabang = '-';
            if (typeof getMasterDataPICs === 'function') {
                const pics = getMasterDataPICs();
                const matched = pics.find(p => p.wa && normalizePhone(p.wa) === ph);
                if (matched && matched.picName) {
                    name = matched.picName;
                    cabang = matched.cabang || '-';
                }
            }

            groups[ph] = {
                phone: ph,
                name: name,
                cabang: cabang,
                lastMessage: lastM.text || lastM.message || '',
                lastTime: lastM.timestamp || Date.now(),
                lastDirection: (lastM.sender === 'ADMIN' || lastM.sender === 'admin') ? 'outbound' : 'inbound',
                messages: msgs.map(m => ({
                    messageId: m.id,
                    phone: ph,
                    message: m.text || m.message || '',
                    timestamp: m.timestamp || Date.now(),
                    direction: (m.sender === 'ADMIN' || m.sender === 'admin' || m.fromMe) ? 'outbound' : 'inbound',
                    sender: (m.sender === 'ADMIN' || m.sender === 'admin' || m.fromMe) ? 'ADMIN' : 'PIC',
                    senderName: m.senderName || name,
                    media_url: m.mediaUrl || m.media_url || null,
                    media_type: m.messageType || m.media_type || null,
                    reply_to: m.replyTo || m.reply_to || null
                }))
            };
        });

        // 2. Gabungkan dengan dcmsLwMessages (pesan realtime dari server/webhook)
        dcmsLwMessages.forEach(m => {
            const ph = normalizePhone(m.phone);
            if (!ph) return;
            if (!groups[ph]) {
                let validSenderName = '';
                const sName = String(m.senderName || '').trim();
                if (sName && sName.toLowerCase() !== 'admin' && !sName.toLowerCase().startsWith('admin')) {
                    validSenderName = sName;
                }
                groups[ph] = {
                    phone: ph,
                    name: validSenderName || formatDcmsPhoneDisplay(ph),
                    cabang: '-',
                    lastMessage: m.message || (m.media_type ? ('[' + m.media_type + ']') : ''),
                    lastTime: m.timestamp,
                    lastDirection: m.direction || 'inbound',
                    messages: []
                };
            }
            const exists = groups[ph].messages.some(ex => ex.messageId && m.messageId && ex.messageId === m.messageId);
            if (!exists) {
                groups[ph].messages.push(m);
                groups[ph].lastMessage = m.message || (m.media_type ? ('[' + m.media_type + ']') : '');
                groups[ph].lastTime = m.timestamp;
                groups[ph].lastDirection = m.direction || 'inbound';
            }
        });

        // Resolve nama kontak dari Master Data PIC / Keep jika nama masih belum terdeteksi
        Object.values(groups).forEach(g => {
            if (!g.name) {
                if (typeof getMasterDataPICs === 'function') {
                    const pics = getMasterDataPICs();
                    const found = pics.find(p => p.wa && normalizePhone(p.wa) === g.phone);
                    if (found && found.picName) {
                        g.name = found.picName + (found.cabang ? ' (' + found.cabang + ')' : '');
                    }
                }
            }
            if (!g.name) {
                g.name = formatDcmsPhoneDisplay(g.phone);
            }
        });

        // Auto-merge WhatsApp LID (panjang > 13 digit) dengan kontak nomor asli yang cocok
        const phones = Object.keys(groups);
        phones.forEach(ph => {
            const isLid = ph.length > 13 || (!ph.startsWith('628') && !ph.startsWith('08') && !ph.startsWith('8'));
            if (!isLid) return;

            const lidGroup = groups[ph];
            if (!lidGroup) return;

            const lidCleanName = String(lidGroup.name || '').toLowerCase().replace(/[^a-z0-9]/g, '');
            let targetRealPhone = null;

            phones.forEach(otherPh => {
                if (otherPh === ph) return;
                const otherIsLid = otherPh.length > 13 || (!otherPh.startsWith('628') && !otherPh.startsWith('08') && !otherPh.startsWith('8'));
                if (otherIsLid) return; // Hanya merge ke nomor HP asli

                const otherGroup = groups[otherPh];
                if (!otherGroup) return;
                const otherCleanName = String(otherGroup.name || '').toLowerCase().replace(/[^a-z0-9]/g, '');

                if (lidCleanName && otherCleanName) {
                    if (otherCleanName.includes(lidCleanName) || lidCleanName.includes(otherCleanName)) {
                        targetRealPhone = otherPh;
                    }
                }
            });

            if (targetRealPhone && groups[targetRealPhone]) {
                const realGroup = groups[targetRealPhone];
                // Gabungkan pesan LID ke dalam riwayat nomor asli
                realGroup.messages.push(...lidGroup.messages);
                // Urutkan ulang pesan berdasarkan timestamp
                realGroup.messages.sort((a, b) => {
                    const tA = isNaN(a.timestamp) ? (new Date(a.timestamp).getTime() || 0) : Number(a.timestamp);
                    const tB = isNaN(b.timestamp) ? (new Date(b.timestamp).getTime() || 0) : Number(b.timestamp);
                    return tA - tB;
                });
                const lastMsg = realGroup.messages[realGroup.messages.length - 1];
                if (lastMsg) {
                    realGroup.lastMessage = lastMsg.message || (lastMsg.media_type ? ('[' + lastMsg.media_type + ']') : '');
                    realGroup.lastTime = lastMsg.timestamp;
                    realGroup.lastDirection = lastMsg.direction || 'inbound';
                }
                // Hapus grup LID terpisah agar menyatu
                delete groups[ph];
            }
        });

        // Urutkan percakapan dari yang paling baru
        return Object.values(groups).sort((a, b) => {
            const tA = isNaN(a.lastTime) ? (new Date(a.lastTime).getTime() || 0) : (Number(a.lastTime) < 1e11 ? Number(a.lastTime) * 1000 : Number(a.lastTime));
            const tB = isNaN(b.lastTime) ? (new Date(b.lastTime).getTime() || 0) : (Number(b.lastTime) < 1e11 ? Number(b.lastTime) * 1000 : Number(b.lastTime));
            return tB - tA;
        });
    }

    function updateUnreadBadge() {
        const badge = document.getElementById('dcmsLwBadge');
        if (!badge) return;

        // Hitung chat yang pesan terakhirnya adalah inbound (perlu dibalas)
        const chats = getGroupedChats();
        const pendingCount = chats.filter(c => c.lastDirection === 'inbound').length;

        if (pendingCount > 0) {
            badge.innerText = pendingCount > 99 ? '99+' : pendingCount;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }

    function renderChatsList(filterQuery) {
        const container = document.getElementById('dcmsLwChatsList');
        if (!container) return;

        let chats = getGroupedChats();
        if (filterQuery) {
            const q = filterQuery.toLowerCase();
            chats = chats.filter(c => c.name.toLowerCase().includes(q) || c.phone.includes(q) || c.lastMessage.toLowerCase().includes(q));
        }

        if (chats.length === 0) {
            container.innerHTML = '<div style="text-align:center; padding:50px 20px; color:#94a3b8; font-size:12px;">' +
                '<span style="font-size:28px; display:block; margin-bottom:8px;">💬</span>' +
                'Belum ada percakapan WhatsApp yang tersimpan.' +
            '</div>';
            return;
        }

        let html = '';
        chats.forEach(c => {
            const isPending = c.lastDirection === 'inbound';
            const displayName = c.name || formatDcmsPhoneDisplay(c.phone);
            const initials = getDcmsLwInitials(displayName);
            const displayPhone = formatDcmsPhoneDisplay(c.phone);
            const previewText = (c.lastMessage || 'Tidak ada pesan')
                .replace(/[\r\n]+/g, ' ')
                .replace(/[*_~`]/g, '')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            html += '<div class="dcms-lw-chat-card" onclick="openDcmsLiveWidgetRoom(\'' + c.phone + '\', \'' + (displayName.replace(/'/g, "\\'")) + '\')">' +
                '<div class="dcms-lw-avatar">' + initials + '</div>' +
                '<div style="flex:1; min-width:0;">' +
                    '<div style="display:flex; justify-content:space-between; align-items:center;">' +
                        '<div style="font-weight:700; font-size:12px; color:#0f172a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px;" title="' + displayName + '">' + displayName + '</div>' +
                        '<div style="font-size:10px; color:#94a3b8;">' + formatTime(c.lastTime) + '</div>' +
                    '</div>' +
                    '<div style="display:flex; justify-content:space-between; align-items:center; margin-top:1px;">' +
                        '<div style="font-size:10.5px; color:#64748b; font-family:monospace;">' + displayPhone + '</div>' +
                        (isPending ? '<span style="background:#ef4444; color:#fff; font-size:9px; font-weight:800; border-radius:10px; padding:1px 6px;">Baru</span>' : '') +
                    '</div>' +
                    '<div style="display:flex; justify-content:space-between; align-items:center; margin-top:2px;">' +
                        '<div style="font-size:11.5px; color:' + (isPending ? '#0f172a; font-weight:600;' : '#64748b;') + ' overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:200px;">' +
                            (c.lastDirection === 'outbound' ? '<span style="color:#10b981;">✓ </span>' : '') +
                            previewText +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        });

        container.innerHTML = html;
    }

    window.filterDcmsLiveWidgetChats = function(query) {
        renderChatsList(query);
    };

    window.openDcmsLiveWidgetRoom = function(phone, name, prefillMessage) {
        dcmsLwActivePhone = phone;
        dcmsLwActiveName = name;

        document.getElementById('dcmsLwViewList').style.display = 'none';
        document.getElementById('dcmsLwViewRoom').style.display = 'flex';

        document.getElementById('dcmsLwActiveName').innerText = name;
        document.getElementById('dcmsLwActivePhone').innerText = phone;

        renderActiveRoomMessages();

        const input = document.getElementById('dcmsLwInput');
        if (input) {
            input.value = prefillMessage || '';
            input.style.height = 'auto';
            if (prefillMessage) {
                input.style.height = Math.min(input.scrollHeight || 60, 110) + 'px';
            } else {
                input.style.height = '38px';
            }
            setTimeout(() => {
                input.focus();
                if (input.setSelectionRange) {
                    input.setSelectionRange(input.value.length, input.value.length);
                }
            }, 60);
        }
    };

    window.openDcmsFollowUpLiveChat = function(phone, name, keepDetails) {
        if (!phone) return;
        const norm = normalizePhone(phone);
        const win = document.getElementById('dcmsLwWindow');
        if (win && win.style.display !== 'flex') {
            toggleDcmsLiveWidget(true);
        }

        let prefill = '';
        if (keepDetails && typeof keepDetails === 'object') {
            prefill = `Halo kak ${name || 'Tim Follow Up'}, mau konfirmasi tindak lanjut format keep:\n` +
                      `• Customer: ${keepDetails.customer || '-'}\n` +
                      `• No. WA: ${keepDetails.wa || '-'}\n` +
                      `• Barang: ${keepDetails.barang || '-'}\n` +
                      `• Cabang: ${keepDetails.cabang || '-'}\n` +
                      `• Tgl Ambil: ${keepDetails.tglAmbil || '-'}\n` +
                      (keepDetails.catatan ? `• Catatan: ${keepDetails.catatan}\n` : '') +
                      `\nApakah customer-nya sudah dihubungi kak? Mohon infonya ya, terima kasih 🙏`;
        }

        openDcmsLiveWidgetRoom(norm, name || ('PIC ' + norm), prefill);
    };

    window.closeDcmsLiveWidgetRoom = function() {
        dcmsLwActivePhone = null;
        dcmsLwActiveName = null;
        document.getElementById('dcmsLwViewRoom').style.display = 'none';
        document.getElementById('dcmsLwViewList').style.display = 'flex';
        renderChatsList();
    };

    function renderActiveRoomMessages() {
        const stream = document.getElementById('dcmsLwMessagesStream');
        if (!stream || !dcmsLwActivePhone) return;

        // Ambil riwayat pesan lengkap dari getGroupedChats() (Inbox Follow-Up + pesan realtime)
        const chats = getGroupedChats();
        const activeChat = chats.find(c => normalizePhone(c.phone) === normalizePhone(dcmsLwActivePhone));
        let messages = (activeChat && Array.isArray(activeChat.messages) && activeChat.messages.length > 0)
            ? [...activeChat.messages]
            : dcmsLwMessages.filter(m => normalizePhone(m.phone) === normalizePhone(dcmsLwActivePhone));

        // Urutkan pesan dari yang paling lama ke paling baru (kronologis)
        messages.sort((a, b) => {
            const tA = isNaN(a.timestamp) ? (new Date(a.timestamp).getTime() || 0) : (Number(a.timestamp) < 1e11 ? Number(a.timestamp) * 1000 : Number(a.timestamp));
            const tB = isNaN(b.timestamp) ? (new Date(b.timestamp).getTime() || 0) : (Number(b.timestamp) < 1e11 ? Number(b.timestamp) * 1000 : Number(b.timestamp));
            return tA - tB;
        });

        if (messages.length === 0) {
            stream.innerHTML = '<div style="text-align:center; padding:30px 10px; color:#94a3b8; font-size:11.5px;">Belum ada riwayat percakapan dengan nomor ini. Silakan kirim pesan follow up langsung di bawah:</div>';
            return;
        }

        let html = '';
        messages.forEach(m => {
            const isOut = m.direction === 'outbound' || m.sender === 'ADMIN' || m.sender === 'admin' || m.fromMe;
            const timeStr = formatTime(m.timestamp);
            const tick = isOut ? '<span style="color:#0284c7; font-size:10px; margin-left:3px;">✓✓</span>' : '';
            const isImg = m.media_type === 'image' || m.mediaType === 'image';
            const isDoc = m.media_type === 'document' || m.mediaType === 'document';
            const mediaTag = isImg 
                ? '<div style="font-size:11px; color:#0284c7; margin-bottom:4px; font-weight:600;">📷 Foto WhatsApp</div>'
                : (isDoc ? '<div style="font-size:11px; color:#0284c7; margin-bottom:4px; font-weight:600;">📄 Dokumen WhatsApp</div>' : '');

            const msgContent = m.message || m.text || '';
            html += '<div class="dcms-lw-bubble ' + (isOut ? 'dcms-lw-bubble-out' : 'dcms-lw-bubble-in') + '">' +
                mediaTag +
                '<div class="dcms-lw-bubble-text">' + formatDcmsLiveMessage(msgContent) + '</div>' +
                '<div class="dcms-lw-bubble-meta">' + timeStr + tick + '</div>' +
            '</div>';
        });

        stream.innerHTML = html;
        stream.scrollTop = stream.scrollHeight;
    }

    window.handleDcmsLiveWidgetKey = function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendDcmsLiveWidgetReply();
        }
    };

    window.sendDcmsLiveWidgetReply = async function() {
        const input = document.getElementById('dcmsLwInput');
        const btn = document.getElementById('dcmsLwBtnSend');
        if (!input || !dcmsLwActivePhone) return;

        const text = input.value.trim();
        if (!text) return;

        const targetPhone = dcmsLwActivePhone;
        const now = Date.now();
        const tempMsg = {
            messageId: 'out_' + now,
            phone: targetPhone,
            message: text,
            text: text,
            timestamp: now,
            direction: 'outbound',
            sender: 'ADMIN',
            status: 'sent'
        };

        // 1. Optimistic UI di widget
        dcmsLwMessages.push(tempMsg);

        // 2. Sinkronkan langsung ke localStorage gadgetmart_omni_threads (Inbox Follow-Up)
        try {
            const threads = JSON.parse(localStorage.getItem('gadgetmart_omni_threads') || '{}');
            const threadKey = targetPhone;
            if (!threads[threadKey]) threads[threadKey] = [];
            threads[threadKey].push({
                id: tempMsg.messageId,
                messageId: tempMsg.messageId,
                sender: 'ADMIN',
                senderName: 'Admin (Anda)',
                text: text,
                message: text,
                timestamp: now,
                fromMe: true
            });
            if (typeof saveOmniThreads === 'function') {
                saveOmniThreads(threads);
            } else {
                localStorage.setItem('gadgetmart_omni_threads', JSON.stringify(threads));
            }
            if (typeof renderOmniInboxModule === 'function') {
                renderOmniInboxModule();
            }
        } catch (errOmni) {
            console.warn('Gagal sinkron widget ke omniThreads:', errOmni);
        }

        renderActiveRoomMessages();
        renderChatsList();
        input.value = '';
        input.focus();
        if (btn) btn.disabled = true;

        try {
            const res = await fetch('/api/watzap-send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    phone: targetPhone,
                    message: text
                })
            });

            const json = await res.json();
            if (json.success) {
                showWidgetToast('Pesan terkirim ke WhatsApp');
            } else {
                showWidgetToast('Pesan tersimpan lokal: ' + (json.message || 'Gagal'));
            }
        } catch (err) {
            console.error('Gagal kirim via Live Widget:', err);
            showWidgetToast('Gagal kirim: Periksa jaringan');
        } finally {
            if (btn) btn.disabled = false;
        }
    };

    window.syncDcmsLiveWidget = function() {
        const icon = document.getElementById('dcmsLwSyncIcon');
        if (icon) icon.style.animation = 'spin 0.8s linear infinite';
        loadDcmsLiveWidgetData(false).finally(() => {
            if (icon) icon.style.animation = '';
            showWidgetToast('Sinkronisasi selesai');
        });
    };

    // Ekspos fungsi rendering Live Chat Widget ke global window agar Tab 9 Inbox Follow-Up dapat memanggil secara realtime
    window.renderDcmsLwChatsList = renderChatsList;
    window.updateDcmsLwUnreadBadge = updateUnreadBadge;
    window.renderDcmsLwActiveRoomMessages = renderActiveRoomMessages;
    window.renderChatsList = renderChatsList;
    window.updateUnreadBadge = updateUnreadBadge;
    window.renderActiveRoomMessages = renderActiveRoomMessages;

    // Auto-polling setiap 5 detik
    document.addEventListener('DOMContentLoaded', function() {
        checkTabVisibility();
        loadDcmsLiveWidgetData(false);

        dcmsLwPollingTimer = setInterval(function() {
            checkTabVisibility();
            loadDcmsLiveWidgetData(true);
        }, 5000);
    });
})();
</script>

