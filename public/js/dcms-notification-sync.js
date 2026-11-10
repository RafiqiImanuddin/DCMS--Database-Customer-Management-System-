/**
 * DCMS Google Sheets Background Auto-Sync & Notification Engine
 * - Background periodic synchronization (every 3 minutes)
 * - Top-Right sleek floating toast notifications
 * - Top-Bar Notification Bell dropdown with live status & activity log
 */

(function () {
  'use strict';

  const STORAGE_KEY = 'dcms_sync_notifications_v1';
  const SYNC_INTERVAL_MS = 3 * 60 * 1000; // 3 menit
  let isSyncing = false;
  let syncTimer = null;

  // Format waktu lokal (WITA / browser local)
  function formatCurrentTime() {
    const d = new Date();
    const hours = String(d.getHours()).padStart(2, '0');
    const mins = String(d.getMinutes()).padStart(2, '0');
    return `${hours}:${mins} WITA`;
  }

  function formatFullDate(isoString) {
    if (!isoString) return 'Baru saja';
    try {
      const d = new Date(isoString);
      return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      }) + ' WITA';
    } catch (e) {
      return 'Baru saja';
    }
  }

  // =========================================================================
  // 1. FLOATING TOAST NOTIFICATION ENGINE (TOP-RIGHT)
  // =========================================================================
  function getToastContainer() {
    let container = document.getElementById('dcmsTopRightToastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'dcmsTopRightToastContainer';
      container.className = 'fixed top-5 right-5 z-[9999999] flex flex-col gap-2.5 max-w-sm sm:max-w-md w-full pointer-events-none pr-2';
      document.body.appendChild(container);
    }
    return container;
  }

  function showDcmsToast({ title = 'Pemberitahuan', message = '', type = 'success', duration = 5000 }) {
    const container = getToastContainer();
    const toast = document.createElement('div');
    const toastId = 'dcms-toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
    toast.id = toastId;

    const isSuccess = type === 'success';
    const isError = type === 'error';
    const isInfo = type === 'info';

    let iconBg = 'bg-emerald-50 text-emerald-600 border-emerald-100';
    let iconSvg = `
      <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 6L9 17l-5-5"></path>
      </svg>
    `;

    if (isError) {
      iconBg = 'bg-rose-50 text-rose-600 border-rose-100';
      iconSvg = `
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      `;
    } else if (isInfo) {
      iconBg = 'bg-sky-50 text-sky-600 border-sky-100';
      iconSvg = `
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="16" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
      `;
    }

    toast.className = 'pointer-events-auto relative flex items-start gap-3 rounded-2xl bg-white/95 backdrop-blur-md p-4 shadow-2xl border border-slate-200/90 transform transition-all duration-300 translate-x-full opacity-0 overflow-hidden';
    toast.innerHTML = `
      <div class="size-9 rounded-xl flex items-center justify-center shrink-0 border ${iconBg}">
        ${iconSvg}
      </div>
      <div class="flex-1 min-w-0 pr-1">
        <div class="flex items-center justify-between gap-2">
          <h5 class="text-xs font-black text-slate-900 tracking-tight">${title}</h5>
          <span class="text-[10px] text-slate-400 font-semibold">${formatCurrentTime()}</span>
        </div>
        <p class="text-xs text-slate-600 mt-1 leading-relaxed font-medium">${message}</p>
      </div>
      <button type="button" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition shrink-0 cursor-pointer" aria-label="Tutup" onclick="this.closest('.pointer-events-auto').remove()">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
      <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-slate-100 overflow-hidden">
        <div class="h-full bg-emerald-500 transition-all ease-linear" style="width: 100%; transition-duration: ${duration}ms;"></div>
      </div>
    `;

    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
      toast.classList.remove('translate-x-full', 'opacity-0');
      toast.classList.add('translate-x-0', 'opacity-100');
      const progressBar = toast.querySelector('.bg-emerald-500');
      if (progressBar) {
        requestAnimationFrame(() => {
          progressBar.style.width = '0%';
        });
      }
    });

    // Auto dismiss
    let dismissTimeout = setTimeout(() => {
      dismissToast(toast);
    }, duration);

    // Pause on hover
    toast.addEventListener('mouseenter', () => clearTimeout(dismissTimeout));
    toast.addEventListener('mouseleave', () => {
      dismissTimeout = setTimeout(() => dismissToast(toast), 1500);
    });

    function dismissToast(el) {
      if (!el || !el.parentNode) return;
      el.classList.remove('translate-x-0', 'opacity-100');
      el.classList.add('translate-x-full', 'opacity-0');
      setTimeout(() => el.remove(), 350);
    }

    return toast;
  }
  window.showDcmsToast = showDcmsToast;

  // =========================================================================
  // 2. TEAM ACTIVITY NOTIFICATION STORE & SYNC ENGINE
  // =========================================================================
  let cachedTeamNotifications = [];
  const LAST_SEEN_STORAGE_KEY = 'dcms_last_seen_activity_id_v2';

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function getUserBadgeStyle(name) {
    const lower = (name || '').toLowerCase();
    if (lower.includes('superadmin')) {
      return 'bg-purple-100 text-purple-800 border border-purple-200';
    }
    if (lower.includes('sistem')) {
      return 'bg-slate-100 text-slate-700 border border-slate-200';
    }
    return 'bg-blue-100 text-blue-800 border border-blue-200';
  }

  async function fetchTeamNotifications(isUserInitiated = false) {
    try {
      const res = await fetch(`/api/notifications?t=${Date.now()}`, {
        headers: { 'Accept': 'application/json' },
        cache: 'no-store'
      });
      if (!res.ok) return;

      const data = await res.json();
      if (data && Array.isArray(data.notifications)) {
        const prevList = cachedTeamNotifications;
        cachedTeamNotifications = data.notifications;

        const lastSeenId = parseInt(localStorage.getItem(LAST_SEEN_STORAGE_KEY) || '0', 10);
        const topNotif = cachedTeamNotifications[0];

        // Jika ada notifikasi baru yang belum dilihat
        if (topNotif && topNotif.id > lastSeenId) {
          updateBellBadge(true);

          // Jika ini background polling (bukan buka dropdown sendiri) dan ada notifikasi baru
          if (!isUserInitiated && prevList.length > 0 && topNotif.id !== prevList[0]?.id) {
            showDcmsToast({
              title: topNotif.title,
              message: topNotif.message,
              type: topNotif.type || 'success',
              duration: 6000
            });
          }
        } else if (!topNotif) {
          updateBellBadge(false);
        }

        renderNotificationList();
      }
    } catch (e) {
      console.warn('[Notifications Fetch Error]', e);
    }
  }
  window.fetchTeamNotifications = fetchTeamNotifications;

  function addDcmsNotification({ title, message, type = 'success' }) {
    fetchTeamNotifications(true);
  }
  window.addDcmsNotification = addDcmsNotification;

  async function clearDcmsNotifications() {
    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      await fetch('/api/notifications', {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token || ''
        }
      });
    } catch (e) {}

    cachedTeamNotifications = [];
    localStorage.setItem(LAST_SEEN_STORAGE_KEY, '999999999');
    renderNotificationList();
    updateBellBadge(false);
  }
  window.clearDcmsNotifications = clearDcmsNotifications;

  function updateBellBadge(hasNew = false) {
    const badge = document.getElementById('dcmsNotificationBadge');
    if (!badge) return;
    if (hasNew) {
      badge.classList.remove('hidden');
      badge.style.display = 'flex';
      const ping = badge.querySelector('.animate-ping');
      if (ping) ping.classList.remove('hidden');
    } else {
      badge.classList.add('hidden');
      badge.style.display = 'none';
    }
  }

  function renderNotificationList() {
    const container = document.getElementById('dcmsNotificationList');
    if (!container) return;

    if (cachedTeamNotifications.length === 0) {
      container.innerHTML = `
        <div class="py-6 text-center text-slate-400">
          <svg class="size-7 mx-auto mb-1.5 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
          </svg>
          <p class="text-xs font-semibold text-slate-500">Belum ada aktivitas pembaruan</p>
          <span class="text-[10px] text-slate-400">Aktivitas upload & perubahan dari tim akan tampil di sini</span>
        </div>
      `;
      return;
    }

    container.innerHTML = cachedTeamNotifications.map(item => {
      let dotColor = 'bg-emerald-500';
      if (item.type === 'error' || item.type === 'danger') dotColor = 'bg-rose-500';
      else if (item.type === 'warning') dotColor = 'bg-amber-500';
      else if (item.type === 'info') dotColor = 'bg-sky-500';

      const badgeStyle = getUserBadgeStyle(item.user_name);

      return `
        <div class="p-2.5 rounded-xl bg-slate-50/90 border border-slate-100 flex items-start gap-2.5 hover:bg-slate-100/80 transition">
          <div class="size-2 rounded-full ${dotColor} mt-1.5 shrink-0 animate-pulse"></div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-1 mb-0.5">
              <div class="flex items-center gap-1.5 min-w-0">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-tight shrink-0 ${badgeStyle}">
                  ${escapeHtml(item.user_name)}
                </span>
                <h6 class="text-[11px] font-bold text-slate-900 truncate" title="${escapeHtml(item.title)}">${escapeHtml(item.title)}</h6>
              </div>
              <span class="text-[10px] text-slate-400 shrink-0 font-medium" title="${escapeHtml(item.time_formatted || '')}">${escapeHtml(item.time_ago)}</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-snug break-words">${escapeHtml(item.message)}</p>
          </div>
        </div>
      `;
    }).join('');
  }

  // =========================================================================
  // 3. DROPDOWN TOGGLE ENGINE
  // =========================================================================
  function toggleDcmsNotificationDropdown(forceState) {
    const menu = document.getElementById('dcmsNotificationMenu');
    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');
    const shouldShow = forceState !== undefined ? forceState : isHidden;

    if (shouldShow) {
      fetchTeamNotifications(true);
      menu.classList.remove('hidden');
      requestAnimationFrame(() => {
        menu.classList.remove('scale-95', 'opacity-0');
        menu.classList.add('scale-100', 'opacity-100');
      });

      // Tandai sudah dilihat
      if (cachedTeamNotifications.length > 0) {
        localStorage.setItem(LAST_SEEN_STORAGE_KEY, String(cachedTeamNotifications[0].id));
      }
      const badgePing = document.querySelector('#dcmsNotificationBadge .animate-ping');
      if (badgePing) badgePing.classList.add('hidden');
    } else {
      menu.classList.remove('scale-100', 'opacity-100');
      menu.classList.add('scale-95', 'opacity-0');
      setTimeout(() => {
        menu.classList.add('hidden');
      }, 200);
    }
  }
  window.toggleDcmsNotificationDropdown = toggleDcmsNotificationDropdown;

  // Click outside to close dropdown
  document.addEventListener('click', (e) => {
    const wrapper = document.getElementById('dcmsNotificationWrapper');
    const menu = document.getElementById('dcmsNotificationMenu');
    if (!wrapper || !menu || menu.classList.contains('hidden')) return;

    if (!wrapper.contains(e.target)) {
      toggleDcmsNotificationDropdown(false);
    }
  });

  // ESC to close
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      toggleDcmsNotificationDropdown(false);
    }
  });

  // =========================================================================
  // 4. BACKGROUND AUTO-SYNC & MANUAL SYNC ENGINE
  // =========================================================================
  async function runKeepSync({ isManual = false, force = false } = {}) {
    if (isSyncing) return;
    isSyncing = true;

    const syncBtnLabel = document.getElementById('syncBtnLabel');
    const syncIconSpin = document.getElementById('syncIconSpin');
    if (syncBtnLabel) syncBtnLabel.textContent = 'Syncing...';
    if (syncIconSpin) syncIconSpin.classList.add('animate-spin');

    // Also animate header button if exists in subbar
    const headerBtn = document.getElementById('headerBtnSyncSheets');
    if (headerBtn) {
      headerBtn.classList.add('opacity-75', 'pointer-events-none');
    }

    try {
      const url = `/api/sync-keep?force=${force ? 1 : 0}&t=${Date.now()}`;
      const res = await fetch(url, {
        headers: { 'Accept': 'application/json' },
        cache: 'no-store'
      });

      if (!res.ok) {
        throw new Error(`HTTP Error ${res.status}`);
      }

      const data = await res.json();

      if (data.success) {
        const rowCount = data.total_rows || 0;
        const formattedCount = Number(rowCount).toLocaleString('id-ID');

        // Update UI counters in dropdown
        const countEl = document.getElementById('dcmsKeepCountText');
        if (countEl) countEl.textContent = `${formattedCount} Transaksi`;

        const lastSyncEl = document.getElementById('dcmsLastSyncText');
        if (lastSyncEl) lastSyncEl.textContent = data.synced_at_formatted || formatCurrentTime();

        // Check if there was actual change or if it was manually triggered
        if (data.has_changed) {
          showDcmsToast({
            title: 'Google Sheets Diperbarui!',
            message: `Ditemukan transaksi baru (${formattedCount} data). Database berhasil disinkronkan otomatis.`,
            type: 'success',
            duration: 6000,
          });

          addDcmsNotification({
            title: 'Update Google Sheets Diterapkan',
            message: `Sinkronisasi otomatis berhasil memuat total ${formattedCount} data transaksi terbaru.`,
            type: 'success',
          });

          // Sinyal ke seluruh modul (Workspace Sosmed, Format Keep, dll)
          window.dispatchEvent(new CustomEvent('dcms:keep-synced', { detail: data }));
        } else if (isManual) {
          showDcmsToast({
            title: 'Sinkronisasi Selesai',
            message: `Seluruh data (${formattedCount} data transaksi) sudah mutakhir dan sinkron dengan Google Sheets.`,
            type: 'success',
            duration: 4000,
          });

          addDcmsNotification({
            title: 'Sinkronisasi Manual Berhasil',
            message: `Pengecekan selesai, seluruh ${formattedCount} transaksi sudah yang terbaru.`,
            type: 'success',
          });

          window.dispatchEvent(new CustomEvent('dcms:keep-synced', { detail: data }));
        }
      }
    } catch (err) {
      console.warn('[Auto-Sync Error]', err);
      if (isManual) {
        showDcmsToast({
          title: 'Gagal Sinkronisasi',
          message: 'Terjadi kendala saat menghubungi server Google Sheets: ' + (err.message || 'Koneksi terputus'),
          type: 'error',
          duration: 5000,
        });
      }
    } finally {
      isSyncing = false;
      if (syncBtnLabel) syncBtnLabel.textContent = 'Sync Sekarang';
      if (syncIconSpin) syncIconSpin.classList.remove('animate-spin');
      if (headerBtn) {
        headerBtn.classList.remove('opacity-75', 'pointer-events-none');
      }
    }
  }

  function triggerManualSyncKeep() {
    runKeepSync({ isManual: true, force: true });
  }
  window.triggerManualSyncKeep = triggerManualSyncKeep;

  // Check initial database status on page load
  async function fetchInitialStatus() {
    try {
      const res = await fetch(`/api/database-status?t=${Date.now()}`, { cache: 'no-store' });
      if (res.ok) {
        const data = await res.json();
        if (data.keep) {
          const count = data.keep.totalRows || 0;
          const countEl = document.getElementById('dcmsKeepCountText');
          if (countEl) countEl.textContent = `${Number(count).toLocaleString('id-ID')} Transaksi`;

          const lastSyncEl = document.getElementById('dcmsLastSyncText');
          if (lastSyncEl) lastSyncEl.textContent = data.keep.lastSyncFormatted || 'Baru saja';
        }
      }
    } catch (e) {}
  }

  // Schedule background periodic sync
  function startBackgroundAutoSync() {
    if (syncTimer) clearInterval(syncTimer);

    // Initial notifications & status fetch
    setTimeout(() => {
      fetchTeamNotifications(false);
      fetchInitialStatus();
    }, 1000);

    // Initial background check after 10s if on workspace
    setTimeout(() => {
      runKeepSync({ isManual: false, force: false });
    }, 10000);

    // Periodic check every 3 minutes for sheets & team notifications
    syncTimer = setInterval(() => {
      if (!document.hidden) {
        fetchTeamNotifications(false);
        runKeepSync({ isManual: false, force: false });
      }
    }, SYNC_INTERVAL_MS);

    // Catch up when user switches back to tab if it was hidden
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) {
        fetchTeamNotifications(false);
        fetchInitialStatus();
      }
    });
  }

  // Boot on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      fetchTeamNotifications(false);
      startBackgroundAutoSync();
    });
  } else {
    fetchTeamNotifications(false);
    startBackgroundAutoSync();
  }
})();
