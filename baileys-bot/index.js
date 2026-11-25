/**
 * ==============================================================================
 * DCMS BAILEYS REACTION BOT - AUTO-CLAIM FORMAT KEEP (RENDER.COM / CLOUD PAAS)
 * ==============================================================================
 * Menggunakan @whiskeysockets/baileys untuk mendengarkan reaksi emoji (jempol 👍)
 * dari staf cabang di 18 grup WhatsApp Gadgetmart, dan secara otomatis
 * mengklaim transaksi Format Keep di sistem DCMS ("Siapa Cepat Dia Dapat").
 * ==============================================================================
 */

require('dotenv').config();
const express = require('express');
const axios = require('axios');
const qrcode = require('qrcode');
const path = require('path');
const fs = require('fs');
const pino = require('pino');
const {
  default: makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
  fetchLatestBaileysVersion,
  delay,
  proto
} = require('@whiskeysockets/baileys');

// ==============================================================================
// 1. KONFIGURASI APLIKASI
// ==============================================================================
const PORT = process.env.PORT || 3000;
const DCMS_WEBHOOK_URL = process.env.DCMS_WEBHOOK_URL || 'https://dcms-sosmed.my.id/api/fonnte-webhook';
const DCMS_INBOX_WEBHOOK_URL = process.env.DCMS_INBOX_WEBHOOK_URL || 
  (DCMS_WEBHOOK_URL.includes('fonnte-webhook') 
    ? DCMS_WEBHOOK_URL.replace('fonnte-webhook', 'watzap-webhook') 
    : 'https://dcms-sosmed.my.id/api/watzap-webhook');
const DCMS_SESSION_URL = process.env.DCMS_SESSION_URL || 
  (DCMS_WEBHOOK_URL.includes('fonnte-webhook') 
    ? DCMS_WEBHOOK_URL.replace('fonnte-webhook', 'baileys-session') 
    : 'https://dcms-sosmed.my.id/api/baileys-session');
const DCMS_API_KEY = process.env.DCMS_API_KEY || 'dcms2026';
const DCMS_BASE_URL = process.env.DCMS_BASE_URL || (
  DCMS_WEBHOOK_URL.includes('/api/') 
    ? DCMS_WEBHOOK_URL.split('/api/')[0] 
    : 'https://dcms-sosmed.my.id'
);
const DCMS_CRON_URL = process.env.DCMS_CRON_URL || `${DCMS_BASE_URL}/system/cron-run?key=${DCMS_API_KEY}`;
const DCMS_FALLBACK_CRON_URL = `${DCMS_BASE_URL}/system/migrate?key=${DCMS_API_KEY}&action=auto-fu-h1`;
const TARGET_GROUPS = (process.env.TARGET_GROUPS || '')
  .split(',')
  .map(g => g.trim())
  .filter(Boolean);
const RENDER_EXTERNAL_URL = process.env.RENDER_EXTERNAL_URL || '';

const logger = pino({ level: 'info' });

// State Runtime Bot (Multi-Session Registry)
// Mendukung Bot Utama ('default') untuk 18 Grup Format Keep & Reaction Auto-Claim,
// serta Session Mandiri per Admin Sosmed ('sosmed_4', 'sosmed_16', dll) untuk Inbox WhatsApp masing-masing
const sessions = new Map();

function getOrCreateSession(sessionId = 'default') {
  const sId = String(sessionId || 'default').trim().toLowerCase();
  if (sessions.has(sId)) {
    return sessions.get(sId);
  }
  const session = {
    id: sId,
    sock: null,
    state: 'DISCONNECTED', // 'DISCONNECTED' | 'CONNECTING' | 'WAITING_QR' | 'CONNECTED'
    qr: null,
    pairingCode: null,
    user: null,
    authDir: path.join(__dirname, sId === 'default' ? 'auth_info' : `auth_info_${sId}`),
    lastDisconnectReason: null,
    pushTimeout: null,
    isPushing: false,
    restoredFromDCMS: false,
    lastPushedAt: null
  };
  sessions.set(sId, session);
  return session;
}

// Global default backward compatibility references
let sock = null;
let connectionState = 'DISCONNECTED';
let currentQR = null;
let currentPairingCode = null;
let connectedUser = null;
let lastDisconnectReason = null;
let isSessionRestoredFromDCMS = false;
let lastSessionPushedAt = null;
let pushSessionTimeout = null;
let isPushingSession = false;

// Riwayat buffer in-memory untuk pesan keep (maksimal 1.000 pesan terakhir)
const messageCache = new Map();
const MAX_CACHE = 1000;

// Riwayat raw message store untuk merespon Signal Protocol retry receipts ("Waiting for this message")
const rawMessageStore = new Map();
const MAX_RAW_MESSAGES = 2000;

function saveRawMessage(id, message) {
  if (!id || !message) return;
  rawMessageStore.set(id, message);
  if (rawMessageStore.size > MAX_RAW_MESSAGES) {
    const first = rawMessageStore.keys().next().value;
    rawMessageStore.delete(first);
  }
}

// Riwayat log aktivitas reaksi (maksimal 40 log terakhir untuk tampilan dashboard)
const reactionLogs = [];
const MAX_LOGS = 40;

// Riwayat log runtime terpadu (Cron Scheduler, Reaksi, Pesan Keluar, Event Sistem)
const runtimeLogs = [];
const MAX_RUNTIME_LOGS = 120;

const cronStats = {
  interval: '5 Menit',
  total_runs: 0,
  successful_runs: 0,
  failed_runs: 0,
  last_run_time: null,
  last_status: 'INITIALIZING',
  last_message: 'Menunggu inisialisasi socket Baileys'
};

function addRuntimeLog(type, level, message, detail = null) {
  const entry = {
    id: Date.now() + Math.random().toString(36).substring(2, 6),
    time: new Date().toLocaleTimeString('id-ID', { timeZone: 'Asia/Makassar', hour12: false }) + ' WITA',
    timestamp: new Date().toISOString(),
    type,   // 'CRON', 'REACTION', 'SEND', 'SYSTEM', 'AUTH'
    level,  // 'SUCCESS', 'INFO', 'WARN', 'ERROR'
    message,
    detail
  };
  runtimeLogs.unshift(entry);
  if (runtimeLogs.length > MAX_RUNTIME_LOGS) {
    runtimeLogs.pop();
  }
}

function addReactionLog(log) {
  reactionLogs.unshift({
    id: Date.now() + Math.random().toString(36).substring(2, 6),
    time: new Date().toLocaleTimeString('id-ID', { timeZone: 'Asia/Makassar', hour12: false }) + ' WITA',
    ...log
  });
  if (reactionLogs.length > MAX_LOGS) {
    reactionLogs.pop();
  }

  // Rekam ke runtime log terpadu sesuai jenis event sebenarnya (bukan semuanya dilabeli REACTION)
  const evType = String(log.type || '').toUpperCase();
  const salesLabel = log.sales || log.salesName || 'PIC';
  const groupLabel = log.group || log.groupName || log.groupId || '-';
  const custLabel = log.customer || '-';
  const respLabel = log.response ? String(log.response).trim() : '-';

  if (evType === 'SYSTEM') {
    return; // Sudah dicatat oleh addRuntimeLog('AUTH', ...)
  }

  if (evType === 'CLAIM') {
    const level = log.status === 'BERHASIL' ? 'SUCCESS' : (log.status === 'DITOLAK_LATE' ? 'WARN' : 'ERROR');
    addRuntimeLog(
      'REACTION',
      level,
      `Format Keep di-claim oleh ${salesLabel} (${log.emoji || '👍'})`,
      `Grup: ${groupLabel} | Customer: ${custLabel} | Status: ${log.status || '-'} | Respon: ${respLabel}`
    );
  } else if (evType === 'UNCLAIM') {
    const level = log.status === 'BERHASIL' ? 'INFO' : 'WARN';
    addRuntimeLog(
      'REACTION',
      level,
      `Claim Format Keep dibatalkan oleh ${salesLabel} (${log.emoji || '❌'})`,
      `Grup: ${groupLabel} | Customer: ${custLabel} | Status: ${log.status || '-'} | Respon: ${respLabel}`
    );
  } else if (evType === 'INBOX_IN') {
    addRuntimeLog(
      'CHAT',
      'INFO',
      `Chat Masuk dari ${salesLabel} (${log.emoji || '💬'})`,
      `Nomor: ${custLabel} | Pesan: ${respLabel}`
    );
  } else if (evType === 'INBOX_OUT') {
    addRuntimeLog(
      'SEND',
      'SUCCESS',
      `Pesan WA Terkirim ke ${salesLabel} (${log.emoji || '📤'})`,
      `Tujuan: ${custLabel} | Status: ${log.status || 'TERKIRIM'} | Pesan: ${respLabel}`
    );
  } else {
    addRuntimeLog(
      'REACTION',
      'INFO',
      `Aktivitas ${evType || 'BOT'} oleh ${salesLabel} (${log.emoji || 'ℹ️'})`,
      `Grup: ${groupLabel} | Info: ${respLabel}`
    );
  }
}

// ==============================================================================
// 2. HELPER EKSTRAKSI NOMOR CUSTOMER DARI FORMAT KEEP
// ==============================================================================
function extractCustomerPhone(text) {
  if (!text || typeof text !== 'string') return '';
  const match = text.match(/(?:nomor\s*wa|no\s*wa|nomor|no\s*hp|hp|telepon|no\s*whatsapp)\s*[:=]\s*([+0-9\s-]{8,})/i);
  if (match && match[1]) {
    let clean = match[1].replace(/\D/g, '');
    if (clean.startsWith('0')) {
      clean = '62' + clean.substring(1);
    }
    return clean;
  }
  return '';
}

/**
 * Ekstraksi teks dari berbagai tipe struktur pesan WhatsApp Baileys
 * (termasuk disappearing/ephemeralMessage, viewOnce, dan media caption).
 */
function getMessageText(message) {
  if (!message) return '';
  if (message.ephemeralMessage) {
    message = message.ephemeralMessage.message;
  }
  if (message.viewOnceMessage) {
    message = message.viewOnceMessage.message;
  }
  if (!message) return '';
  return message.conversation ||
    message.extendedTextMessage?.text ||
    message.imageMessage?.caption ||
    message.documentMessage?.caption ||
    message.videoMessage?.caption || '';
}

// ==============================================================================
// 3. AUTO-SAVE & SINKRONISASI SESI AUTH CLOUD (ANTI-LOGOUT DARI REDEPLOY RENDER)
// ==============================================================================
/**
 * Memulihkan sesi auth Baileys dari database DCMS saat bot boot di Render.com.
 * Ini memastikan bot tidak perlu scan QR ulang setiap kali Render rebuild/redeploy container.
 */
async function pullSessionFromDCMS(authDir, sessionId = 'default') {
  try {
    const sId = String(sessionId || 'default').trim().toLowerCase();
    logger.info(`[Auto-Save] Memeriksa cadangan sesi (${sId}) di database DCMS (${DCMS_SESSION_URL})...`);
    const res = await axios.get(DCMS_SESSION_URL, {
      params: { session_id: sId },
      headers: {
        'X-Api-Key': DCMS_API_KEY
      },
      timeout: 10000
    });

    if (res.data && res.data.success && res.data.exists && res.data.session) {
      const sessionFiles = res.data.session;
      const fileNames = Object.keys(sessionFiles);

      if (fileNames.length > 0 && sessionFiles['creds.json']) {
        if (!fs.existsSync(authDir)) {
          fs.mkdirSync(authDir, { recursive: true });
        }

        for (const fileName of fileNames) {
          const content = sessionFiles[fileName];
          if (typeof content === 'string') {
            fs.writeFileSync(path.join(authDir, fileName), content, 'utf8');
          }
        }

        const sess = getOrCreateSession(sId);
        sess.restoredFromDCMS = true;
        if (sId === 'default') isSessionRestoredFromDCMS = true;
        logger.info(`[Auto-Save] ✅ Berhasil memulihkan ${fileNames.length} file sesi (${sId}) dari DCMS! Bot akan langsung terhubung tanpa scan QR.`);
        return true;
      }
    }
    logger.info(`[Auto-Save] Belum ada cadangan sesi (${sId}) di DCMS atau sesi kosong. Menunggu scan QR baru.`);
  } catch (err) {
    logger.warn(`[Auto-Save] Gagal memulihkan sesi (${sessionId}) dari DCMS: ${err.message}`);
  }
  return false;
}

/**
 * Menyimpan snapshot seluruh file auth Baileys ke database DCMS secara berkala.
 * Menggunakan debounce 2.5 detik per session agar efisien dan tidak flooding HTTP saat handshaking keys.
 */
function schedulePushSessionToDCMS(authDir, sessionId = 'default') {
  const sId = String(sessionId || 'default').trim().toLowerCase();
  const sess = getOrCreateSession(sId);

  if (sess.pushTimeout) {
    clearTimeout(sess.pushTimeout);
  }

  sess.pushTimeout = setTimeout(async () => {
    if (sess.isPushing) return;
    sess.isPushing = true;

    try {
      if (!fs.existsSync(authDir)) return;
      const credsPath = path.join(authDir, 'creds.json');
      if (!fs.existsSync(credsPath)) return;

      const files = fs.readdirSync(authDir);
      const sessionPayload = {};

      for (const file of files) {
        if (file.endsWith('.json')) {
          const filePath = path.join(authDir, file);
          sessionPayload[file] = fs.readFileSync(filePath, 'utf8');
        }
      }

      if (!sessionPayload['creds.json']) return;

      const res = await axios.post(DCMS_SESSION_URL, {
        session_id: sId,
        session: sessionPayload
      }, {
        headers: {
          'X-Api-Key': DCMS_API_KEY,
          'Content-Type': 'application/json'
        },
        timeout: 15000,
        maxContentLength: 50 * 1024 * 1024
      });

      if (res.data && res.data.success) {
        sess.lastPushedAt = new Date().toISOString();
        if (sId === 'default') lastSessionPushedAt = sess.lastPushedAt;
        logger.info(`[Auto-Save] ☁️ Sesi auth WhatsApp (${sId}) berhasil dicadangkan ke database DCMS (${res.data.count} file).`);
      }
    } catch (err) {
      logger.warn(`[Auto-Save] Gagal mencadangkan sesi (${sId}) ke DCMS: ${err.message}`);
    } finally {
      sess.isPushing = false;
    }
  }, 2500);
}

/**
 * Menghapus sesi auth di database DCMS saat WhatsApp terdeteksi logout permanen.
 */
async function clearSessionInDCMS(sessionId = 'default') {
  const sId = String(sessionId || 'default').trim().toLowerCase();
  try {
    logger.warn(`[Auto-Save] Menghapus sesi auth (${sId}) di database DCMS karena perangkat logout/reset...`);
    await axios.delete(DCMS_SESSION_URL, {
      params: { session_id: sId },
      headers: {
        'X-Api-Key': DCMS_API_KEY
      },
      timeout: 10000
    });
    logger.info(`[Auto-Save] Sesi auth (${sId}) berhasil dibersihkan dari database DCMS.`);
  } catch (err) {
    logger.warn(`[Auto-Save] Gagal menghapus sesi (${sId}) di DCMS: ${err.message}`);
  }
}

/**
 * Mereset sesi Baileys: menghapus file kredensial auth lokal,
 * menghapus cadangan sesi di DCMS, dan membuat socket fresh untuk generate QR code baru.
 */
async function resetBaileysSession(reason = 'Reset manual diminta oleh pengguna', sessionId = 'default') {
  const sId = String(sessionId || 'default').trim().toLowerCase();
  logger.warn(`[Reset] Melakukan reset sesi Baileys (${sId}: ${reason})...`);

  const sess = getOrCreateSession(sId);
  if (sess.pushTimeout) {
    clearTimeout(sess.pushTimeout);
    sess.pushTimeout = null;
  }

  try {
    if (sess.sock) {
      try { sess.sock.ev.removeAllListeners(); } catch (e) {}
      try { sess.sock.end(); } catch (e) {}
      try { sess.sock.ws?.close(); } catch (e) {}
      sess.sock = null;
    }
  } catch (err) {
    logger.warn(`[Reset] Error saat menutup socket lama (${sId}): ` + err.message);
  }

  sess.state = 'DISCONNECTED';
  sess.qr = null;
  sess.pairingCode = null;
  sess.user = null;
  sess.restoredFromDCMS = false;
  sess.lastPushedAt = null;

  if (sId === 'default') {
    sock = null;
    connectionState = 'DISCONNECTED';
    currentQR = null;
    currentPairingCode = null;
    connectedUser = null;
    isSessionRestoredFromDCMS = false;
    lastSessionPushedAt = null;
  }

  // Bersihkan backup sesi di DCMS
  await clearSessionInDCMS(sId);

  // Hapus folder auth lokal
  const authDir = sess.authDir;
  try {
    if (fs.existsSync(authDir)) {
      fs.rmSync(authDir, { recursive: true, force: true });
      logger.info(`[Reset] Folder auth (${authDir}) berhasil dihapus.`);
    }
  } catch (e) {
    logger.warn(`[Reset] Gagal menghapus folder auth (${authDir}): ` + e.message);
  }

  // Mulai ulang socket fresh untuk generate QR code baru
  setTimeout(() => {
    logger.info(`[Reset] Memulai socket fresh (${sId}) untuk scan QR code nomor baru...`);
    startBaileysSocket(sId).catch(err => logger.error(`[Reset] Gagal start fresh socket (${sId}):`, err));
  }, 1200);

  return true;
}

// ==============================================================================
// 4. INISIALISASI BAILEYS WHATSAPP CLIENT
// ==============================================================================
async function startBaileysSocket(sessionId = 'default') {
  const sId = String(sessionId || 'default').trim().toLowerCase();
  const sess = getOrCreateSession(sId);

  // Cegah instansiasi ganda jika socket sudah aktif/sedang proses koneksi
  if (sess.sock && (sess.state === 'CONNECTING' || sess.state === 'CONNECTED')) {
    return sess.sock;
  }

  const authDir = sess.authDir;
  if (!fs.existsSync(authDir)) {
    fs.mkdirSync(authDir, { recursive: true });
  }

  // Jika folder auth lokal belum memiliki creds.json (misal setelah redeploy Render),
  // coba ambil sesi cadangan dari database DCMS terlebih dahulu
  if (!fs.existsSync(path.join(authDir, 'creds.json'))) {
    await pullSessionFromDCMS(authDir, sId);
  }

  const { state, saveCreds } = await useMultiFileAuthState(authDir);
  const { version, isLatest } = await fetchLatestBaileysVersion();
  logger.info(`Memulai Baileys (${sId}) v${version.join('.')} (Latest: ${isLatest})...`);

  sess.state = 'CONNECTING';
  if (sId === 'default') {
    connectionState = 'CONNECTING';
  }

  // Cache Retry Counter untuk mencegah dan menyelesaikan error Signal decryption delay ("Waiting for this message")
  const msgRetryCounterCache = {
    _map: new Map(),
    get(key) { return this._map.get(key); },
    set(key, val) { this._map.set(key, val); return this; },
    del(key) { this._map.delete(key); },
    delete(key) { this._map.delete(key); }
  };

  const clientBrowserName = sId === 'default' ? 'DCMS Reaction Bot' : `DCMS Sosmed (${sId.toUpperCase()})`;

  const sSock = makeWASocket({
    version,
    auth: state,
    logger: pino({ level: 'silent' }), // Heningkan log verbose Baileys
    printQRInTerminal: sId === 'default',
    browser: [clientBrowserName, 'Chrome', '1.0.0'],
    generateHighQualityLinkPreview: false,
    syncFullHistory: false,
    msgRetryCounterCache,
    retryRequestDelayMs: 250,
    getMessage: async (key) => {
      // Callback getMessage agar Baileys dapat merespon retry receipt saat terjadi Bad MAC / Signal decryption delay
      if (key && key.id) {
        if (rawMessageStore.has(key.id)) {
          return rawMessageStore.get(key.id);
        }
        if (messageCache.has(key.id)) {
          const cached = messageCache.get(key.id);
          return proto.Message.fromObject({
            conversation: cached.text || ''
          });
        }
      }
      return undefined;
    }
  });

  sess.sock = sSock;
  if (sId === 'default') {
    sock = sSock;
  }

  // Simpan kredensial setiap kali diperbarui & cadangkan ke database DCMS
  sSock.ev.on('creds.update', async () => {
    await saveCreds();
    schedulePushSessionToDCMS(authDir, sId);
  });

  // Monitor Pembaruan Koneksi (QR, Status, Reconnect)
  sSock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      sess.qr = await qrcode.toDataURL(qr, { margin: 2, scale: 7 });
      sess.state = 'WAITING_QR';
      if (sId === 'default') {
        currentQR = sess.qr;
        connectionState = 'WAITING_QR';
      }
      logger.info(`Kode QR baru (${sId}) dihasilkan, siap di-scan.`);
      addRuntimeLog('AUTH', 'INFO', `Kode QR baru (${sId}) dihasilkan, siap di-scan pada dashboard.`);
    }

    if (connection === 'close') {
      sess.qr = null;
      sess.user = null;
      if (sId === 'default') {
        currentQR = null;
        connectedUser = null;
      }

      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const isLoggedOut = statusCode === DisconnectReason.loggedOut;
      const isBannedOrForbidden = statusCode === 401 || statusCode === 403;
      const shouldResetAuth = isLoggedOut || isBannedOrForbidden;
      sess.lastDisconnectReason = statusCode;
      sess.state = 'DISCONNECTED';
      if (sId === 'default') {
        lastDisconnectReason = statusCode;
        connectionState = 'DISCONNECTED';
      }

      logger.warn(`Koneksi (${sId}) terputus (Status: ${statusCode}). Reset Auth: ${shouldResetAuth}`);
      addRuntimeLog('AUTH', 'WARN', `Koneksi WhatsApp (${sId}) terputus (Status HTTP: ${statusCode || 'Unknown'})`, `Reset Auth: ${shouldResetAuth ? 'YES' : 'NO'}`);

      if (shouldResetAuth) {
        logger.error(`Akun (${sId}) telah Logout atau Disuspend WhatsApp (Status: ${statusCode}). Mereset sesi...`);
        await resetBaileysSession(`Akun terputus permanen / disuspend WhatsApp (Status: ${statusCode})`, sId);
      } else {
        logger.info(`Mencoba menghubungkan kembali (${sId}) dalam 5 detik...`);
        setTimeout(() => {
          startBaileysSocket(sId).catch(err => logger.error(`Gagal restart socket (${sId}):`, err));
        }, 5000);
      }
    } else if (connection === 'open') {
      sess.state = 'CONNECTED';
      sess.qr = null;
      sess.pairingCode = null;
      sess.user = sSock.user;
      if (sId === 'default') {
        connectionState = 'CONNECTED';
        currentQR = null;
        currentPairingCode = null;
        connectedUser = sSock.user;
      }

      const botPhone = sSock.user.id.split(':')[0];
      const botName = sSock.user.name || (sId === 'default' ? 'Bot Gadgetmart Sosmed' : `Admin ${sId.toUpperCase()}`);
      logger.info(`✅ Baileys (${sId}) Berhasil Terhubung! Akun: ${botPhone} (${botName})`);
      addRuntimeLog('AUTH', 'SUCCESS', `Baileys (${sId}) Berhasil Terhubung: ${botPhone} (${botName})`);

      if (sId === 'default') {
        addReactionLog({
          type: 'SYSTEM',
          group: 'GLOBAL',
          sales: botPhone,
          emoji: '🟢',
          status: 'ONLINE',
          response: 'Bot WhatsApp berhasil terhubung ke jaringan WhatsApp.'
        });
      }
      // Pastikan sesi langsung tersimpan ke DCMS saat koneksi terbuka
      schedulePushSessionToDCMS(authDir, sId);
    }
  });

  // ----------------------------------------------------------------------------
  // LISTENER 1: SINGLE GATEWAY (REACTION GRUP + INBOX PERSONAL DM)
  // ----------------------------------------------------------------------------
  sSock.ev.on('messages.upsert', async ({ messages, type }) => {
    if (!Array.isArray(messages)) return;

    for (const msg of messages) {
      if (!msg.key || !msg.message) continue;
      if (msg.key.id) {
        saveRawMessage(msg.key.id, msg.message);
      }
      const remoteJid = msg.key.remoteJid || '';

      // Abaikan status broadcast WhatsApp
      if (remoteJid === 'status@broadcast') continue;

      // ========================================================================
      // RESTRIKSI KEAMANAN MULTI-SESSION:
      // Sesi Admin Sosmed mandiri (sId !== 'default') DILARANG KERAS
      // mendengarkan / memproses grup chat (@g.us) dan reaksi emoji grup,
      // agar tidak ada duplikasi webhook format keep maupun auto-claim!
      // ========================================================================
      if (sId !== 'default' && remoteJid.endsWith('@g.us')) {
        continue; // Langsung lewati semua aktivitas grup untuk sesi admin mandiri!
      }

      // ========================================================================
      // CABANG A: JALUR INBOX PERSONAL (DM TIM FOLLOW UP / STAF CABANG / CUSTOMER)
      // SINKRONISASI LENGKAP: INBOUND (MASUK) & OUTBOUND (BALASAN HP / WA WEB / DCMS)
      // ========================================================================
      if (!remoteJid.endsWith('@g.us')) {
        const isFromMe = Boolean(msg.key.fromMe);

        const text = msg.message?.conversation ||
          msg.message?.extendedTextMessage?.text ||
          msg.message?.imageMessage?.caption ||
          msg.message?.documentMessage?.caption || '';

        let mediaType = null;
        if (msg.message?.imageMessage) {
          mediaType = 'image';
        } else if (msg.message?.documentMessage) {
          mediaType = 'document';
        }

        // Berlangganan presence update agar menerima event 'sedang mengetik' dari kontak ini
        try {
          if (sock?.presenceSubscribe) {
            sock.presenceSubscribe(remoteJid).catch(() => {});
          }
        } catch (e) {}

        let contactPhone = remoteJid.replace(/@.+/, '').replace(/\D/g, '');
        let resolvedPhone = null;

        // Jika remoteJid adalah WhatsApp LID (Linked Identifier, format @lid), coba resolve ke nomor HP asli
        if (remoteJid.endsWith('@lid') || (contactPhone.length > 13 && !contactPhone.startsWith('628'))) {
          resolvedPhone = await resolveParticipantPhone(remoteJid, '', isFromMe ? '' : msg.pushName);
          if (resolvedPhone) {
            contactPhone = resolvedPhone;
            logger.info(`[LID Resolved] Berhasil memetakan LID ${remoteJid} ke nomor asli: ${resolvedPhone}`);
          }
        }

        const senderName = isFromMe ? 'Admin' : (msg.pushName || ('Staf ' + contactPhone));
        const messageId = msg.key.id;

        // Ekstraksi Quoted Reply
        let replyTo = null;
        const contextInfo = msg.message?.extendedTextMessage?.contextInfo ||
          msg.message?.imageMessage?.contextInfo ||
          msg.message?.documentMessage?.contextInfo;

        if (contextInfo && contextInfo.quotedMessage) {
          const quotedBody = contextInfo.quotedMessage.conversation ||
            contextInfo.quotedMessage.extendedTextMessage?.text ||
            contextInfo.quotedMessage.imageMessage?.caption || '';
          if (quotedBody) {
            replyTo = {
              id: contextInfo.stanzaId || '',
              text: quotedBody,
              body: quotedBody,
              senderName: isFromMe ? 'PIC' : 'Admin'
            };
          }
        }

        const myJid = sock.user?.id ? sock.user.id.replace(/:.*@/, '@') : '6285111426699@s.whatsapp.net';
        const contactJid = resolvedPhone ? (resolvedPhone + '@s.whatsapp.net') : remoteJid;

        logger.info(`[Inbox DM ${isFromMe ? 'Keluar (HP/Web/DCMS)' : 'Masuk'}] ${isFromMe ? 'Ke' : 'Dari'} ${senderName} (${contactPhone}): ${(text || '[' + (mediaType || 'Pesan') + ']').substring(0, 50)}`);

        // Forward aktivitas chat (inbound maupun outbound dari HP/Web) ke Webhook Inbox DCMS
        const inboxPayload = {
          event: 'message',
          payload: {
            id: messageId,
            from: isFromMe ? myJid : contactJid,
            to: isFromMe ? contactJid : myJid,
            fromMe: isFromMe,
            body: text || (mediaType ? `[${mediaType}]` : ''),
            pushname: senderName,
            timestamp: msg.messageTimestamp,
            hasMedia: Boolean(mediaType),
            mediaType: mediaType,
            replyTo: replyTo,
            source: 'BAILEYS_GATEWAY'
          }
        };

        sendToDCMSInbox(inboxPayload).catch(err => {
          logger.error('[DCMS Inbox Gagal]', err.message);
        });

        addReactionLog({
          type: isFromMe ? 'INBOX_OUT' : 'INBOX_IN',
          group: 'DM_PERSONAL',
          sales: isFromMe ? `Admin -> ${contactPhone}` : (contactPhone + (senderName ? ` (${senderName})` : '')),
          emoji: isFromMe ? '📤' : '💬',
          message_id: messageId,
          customer: contactPhone,
          status: 'DITERUSKAN',
          response: (text || `[${mediaType}]`).substring(0, 50)
        });

        continue;
      }

      // ========================================================================
      // CABANG B: JALUR GRUP (REAKSI EMOJI AUTO-CLAIM & FORMAT KEEP CACHING)
      // ========================================================================
      // 1A. Deteksi reaksi emoji via messages.upsert (lengkap dengan pushName WhatsApp)
      const reactionMsg = msg.message?.reactionMessage || msg.message?.ephemeralMessage?.message?.reactionMessage;
      if (reactionMsg && reactionMsg.key) {
        const targetMessageId = reactionMsg.key.id;
        // Orang yang me-react adalah participant pengirim pesan reaksi ini (Tim Follow Up)
        const reactorJid = msg.key?.participantPn ||
                           msg.key?.participant ||
                           msg.participant ||
                           (msg.key?.fromMe ? (sock.user?.id || '') : '') || '';
        const emoji = (reactionMsg.text || '').trim();
        const pushName = msg.pushName || '';
        logger.info(`[Event messages.upsert reaction] Target: ${targetMessageId}, By: ${reactorJid}, Emoji: "${emoji}", PushName: "${pushName}"`);
        await handleReactionEvent(targetMessageId, remoteJid, reactorJid, emoji, pushName);
        continue;
      }

      // 1B. Ambil isi teks pesan format keep
      const text = getMessageText(msg.message);

      if (!text) continue;

      // Cek apakah format keep yang dikirimkan oleh Tim Admin Sosmed
      const isFormatKeep = /format\s*(?:keep|kredit|po|preorder)|barang\s*(?:keep|po|kredit)|remar?ks/i.test(text);
      if (isFormatKeep) {
        const custPhone = extractCustomerPhone(text);
        // Sender di sini adalah Admin Sosmed yang mengirimkan format keep ke grup
        const adminSosmedSender = (msg.key?.participantPn || msg.key?.participant || msg.participant || '').replace(/@.+/, '');

        messageCache.set(msg.key.id, {
          id: msg.key.id,
          remoteJid,
          text,
          customerPhone: custPhone,
          adminSosmed: adminSosmedSender,
          timestamp: Date.now()
        });

        // Batasi ukuran cache
        if (messageCache.size > MAX_CACHE) {
          const firstKey = messageCache.keys().next().value;
          messageCache.delete(firstKey);
        }

        logger.info(`[Keep Masuk dari Admin Sosmed] ID: ${msg.key.id} di grup ${remoteJid} (Customer: ${custPhone || '-'}, Admin: ${adminSosmedSender})`);

        // Forward juga format keep ke DCMS agar tersimpan di database MySQL bahkan jika nomor Fonnte belum dimasukkan ke grup
        const keepPayload = {
          source: 'BAILEYS_BOT',
          message: text,
          sender: remoteJid,
          member: adminSosmedSender,
          isGroup: true,
          inboxid: msg.key.id,
          id: msg.key.id,
          name: msg.pushName || 'Admin Sosmed',
          timestamp: Math.round(Date.now() / 1000)
        };
        sendToDCMS(keepPayload).catch(err => {
          logger.warn(`[Auto-Sync Keep ke DCMS] Gagal: ${err.message}`);
        });
      }
    }
  });

  // ----------------------------------------------------------------------------
  // LISTENER 2: REAKSI EMOJI WHATSAPP DARI TIM FOLLOW UP
  // ----------------------------------------------------------------------------
  // ----------------------------------------------------------------------------
  // LISTENER 3: PRESENCE UPDATE (STATUS SEDANG MENGETIK / COMPOSING / RECORDING)
  // ----------------------------------------------------------------------------
  sSock.ev.on('presence.update', async ({ id, presences }) => {
    try {
      if (!id || !presences) return;
      if (id.includes('@g.us')) return; // Abaikan grup WhatsApp

      const pKeys = Object.keys(presences);
      for (const pKey of pKeys) {
        const pres = presences[pKey];
        const status = pres?.lastKnownPresence; // 'composing' | 'recording' | 'paused' | 'available' | 'unavailable'

        let targetPhone = id.replace(/@.*$/, '').replace(/\D/g, '');
        if (targetPhone.startsWith('0')) {
          targetPhone = '62' + targetPhone.substring(1);
        }

        if (id.endsWith('@lid') || (targetPhone.length > 13 && !targetPhone.startsWith('628'))) {
          const resolved = await resolveParticipantPhone(id, '', '');
          if (resolved) targetPhone = resolved;
        }

        logger.info(`[Presence Update] Kontak ${targetPhone} (${id}): ${status}`);

        sendToDCMSInbox({
          event: 'presence.update',
          payload: {
            id: targetPhone,
            phone: targetPhone,
            presence: status || 'paused',
            timestamp: Date.now()
          }
        }).catch(() => {});
      }
    } catch (err) {
      logger.warn(`[Presence Error] ${err.message}`);
    }
  });

  // Handler Event Delivery & Read Receipts (Centang 1, Centang 2 Abu, Centang 2 Biru)
  sock.ev.on('messages.update', async (updates) => {
    try {
      if (!Array.isArray(updates)) return;
      for (const item of updates) {
        if (!item.key || !item.update) continue;
        const msgId = item.key.id;
        const remoteJid = item.key.remoteJid;
        const fromMe = Boolean(item.key.fromMe);
        const statusNum = item.update.status;

        // Status mapping Baileys:
        // 2: SERVER_ACK -> 'sent' (centang 1 abu-abu)
        // 3: DELIVERY_ACK -> 'delivered' (centang 2 abu-abu)
        // 4: READ -> 'read' (centang 2 biru)
        // 5: PLAYED -> 'read' (centang 2 biru)
        let statusStr = null;
        if (statusNum === 2) statusStr = 'sent';
        else if (statusNum === 3) statusStr = 'delivered';
        else if (statusNum === 4 || statusNum === 5) statusStr = 'read';

        if (statusStr && fromMe && remoteJid) {
          const cleanPhone = remoteJid.replace(/@.*$/, '').replace(/\D/g, '');
          sendToDCMSInbox({
            event: 'message.status_update',
            action: 'MESSAGE_STATUS_UPDATE',
            payload: {
              id: msgId,
              messageId: msgId,
              phone: cleanPhone,
              status: statusStr,
              remoteJid: remoteJid
            }
          }).catch(() => {});
        }
      }
    } catch (e) {
      logger.warn(`[Messages.update Error] ${e.message}`);
    }
  });

  // Handler Event Delivery & Read Receipts Spesifik (message-receipt.update)
  sSock.ev.on('message-receipt.update', async (receipts) => {
    try {
      if (!Array.isArray(receipts)) return;
      for (const item of receipts) {
        if (!item.key || !item.key.id) continue;
        const msgId = item.key.id;
        const remoteJid = item.key.remoteJid;
        if (!remoteJid || remoteJid.endsWith('@g.us')) continue; // Khusus Inbox DM Personal

        const cleanPhone = remoteJid.replace(/@.*$/, '').replace(/\D/g, '');
        let statusStr = 'delivered';
        const r = item.receipt;
        if (r) {
          if (r.readTimestamp || r.playedTimestamp) {
            statusStr = 'read';
          } else if (r.receiptTimestamp || r.deliveryTimestamp) {
            statusStr = 'delivered';
          }
        }

        sendToDCMSInbox({
          event: 'message.status_update',
          action: 'MESSAGE_STATUS_UPDATE',
          payload: {
            id: msgId,
            messageId: msgId,
            phone: cleanPhone,
            status: statusStr,
            remoteJid: remoteJid
          }
        }).catch(() => {});
      }
    } catch (err) {
      logger.warn(`[Message-receipt.update Error] ${err.message}`);
    }
  });

  sSock.ev.on('messages.reaction', async (reactions) => {
    if (!Array.isArray(reactions)) return;

    for (const r of reactions) {
      try {
        logger.info(`[Event messages.reaction Raw] ${JSON.stringify(r)}`);
        const remoteJid = r.key?.remoteJid || '';
        const targetMessageId = r.key?.id;

        // Di Baileys, pembuat reaksi dalam grup ada di r.key.participant (atau r.participantPn / r.participant)
        const reactorJid = r.key?.participantPn ||
                           r.participantPn ||
                           r.key?.participant ||
                           r.participant ||
                           (r.key?.fromMe ? (sock.user?.id || '') : '') ||
                           (r.reaction?.key?.fromMe ? (sock.user?.id || '') : '') || '';
        const emoji = (r.reaction?.text || '').trim();

        await handleReactionEvent(targetMessageId, remoteJid, reactorJid, emoji);
      } catch (err) {
        logger.error('[Error Reaksi]', err.message);
      }
    }
  });

  // Cache Group Metadata in-memory untuk pemetaan cepat LID ke nomor WhatsApp (628xxx)
  const groupMetadataCache = new Map();

  async function getGroupMetadataCached(groupId) {
    if (!sock) return null;
    const cached = groupMetadataCache.get(groupId);
    if (cached && (Date.now() - cached.time) < 10 * 60 * 1000) {
      return cached.data;
    }
    try {
      const data = await sock.groupMetadata(groupId);
      if (data) {
        groupMetadataCache.set(groupId, { time: Date.now(), data });
      }
      return data;
    } catch (e) {
      return cached?.data || null;
    }
  }

  /**
   * Mengonversi JID participant grup WhatsApp (baik @s.whatsapp.net maupun @lid)
   * menjadi nomor telepon WhatsApp asli (format 628xxx).
   */
  async function resolveParticipantPhone(reactorJid, remoteJid, pushName = '') {
    if (!reactorJid) return '';

    // 1. Jika sudah mengandung @s.whatsapp.net (nomor HP standar WhatsApp)
    if (reactorJid.includes('@s.whatsapp.net')) {
      const num = reactorJid.split('@')[0].split(':')[0].replace(/\D/g, '');
      if (num.length >= 9 && num.length <= 15) {
        return num;
      }
    }

    // 2. Jika merupakan @lid, gunakan internal signalRepository.lidMapping bawaan Baileys
    if (reactorJid.endsWith('@lid') && sock?.signalRepository?.lidMapping?.getPNForLID) {
      try {
        const pn = await sock.signalRepository.lidMapping.getPNForLID(reactorJid);
        if (pn && pn.includes('@s.whatsapp.net')) {
          const cleanPn = pn.split('@')[0].split(':')[0].replace(/\D/g, '');
          if (cleanPn.length >= 9 && cleanPn.length <= 15) {
            logger.info(`[LID Resolved via signalRepo] ${reactorJid} -> ${cleanPn}`);
            return cleanPn;
          }
        }
      } catch (e) {}
    }

    // 3. Cari dari groupMetadata participants (WhatsApp group memetakan p.id dan p.lid)
    if (remoteJid && remoteJid.endsWith('@g.us')) {
      try {
        const meta = await getGroupMetadataCached(remoteJid);
        if (meta && Array.isArray(meta.participants)) {
          const matched = meta.participants.find(p => 
            p.lid === reactorJid || 
            p.id === reactorJid || 
            (p.lid && reactorJid && p.lid.replace(/@.+/, '') === reactorJid.replace(/@.+/, ''))
          );
          if (matched) {
            const candidate = matched.id || matched.pn || matched.jid || '';
            if (candidate.includes('@s.whatsapp.net')) {
              const cleanPn = candidate.split('@')[0].split(':')[0].replace(/\D/g, '');
              if (cleanPn.length >= 9 && cleanPn.length <= 15) {
                logger.info(`[LID Resolved via groupMetadata] ${reactorJid} -> ${cleanPn}`);
                return cleanPn;
              }
            }
          }
        }
      } catch (e) {}
    }

    return (reactorJid || '').replace(/@.+/, '').replace(/\D/g, '');
  }

  // Helper pemrosesan reaksi emoji Tim Follow Up
  async function handleReactionEvent(targetMessageId, remoteJid, reactorJid, emoji, pushName = '') {
    if (!targetMessageId || !remoteJid) return;
    if (!remoteJid.endsWith('@g.us')) return;
    if (TARGET_GROUPS.length > 0 && !TARGET_GROUPS.includes(remoteJid)) return;

    // Nomor HP staf Tim Follow Up yang me-react format keep (resolusi LID -> 628xxx)
    const fuPhone = await resolveParticipantPhone(reactorJid, remoteJid, pushName);
    if (!fuPhone) return;

    const cachedKeep = messageCache.get(targetMessageId);
    const custPhone = cachedKeep ? cachedKeep.customerPhone : '';

    if (emoji !== '') {
      // =====================================================================
      // AKSI: TIM FOLLOW UP MENGKLAIM FORMAT KEEP (UPDATE_REACTION_FU)
      // =====================================================================
      logger.info(`[Reaksi Tim Follow Up] ${emoji} oleh ${fuPhone}${pushName ? ' (' + pushName + ')' : ''} pada pesan keep ${targetMessageId}`);

      const payload = {
        action: 'UPDATE_REACTION_FU',
        message_id: targetMessageId,
        customerPhone: custPhone,
        nomorFU: fuPhone,
        sales_name: pushName || '',
        emoji: emoji,
        group_id: remoteJid
      };

      const dcmsRes = await sendToDCMS(payload);

      addReactionLog({
        type: 'CLAIM',
        group: remoteJid.replace('@g.us', ''),
        sales: fuPhone + (pushName ? ` (${pushName})` : ''),
        emoji: emoji,
        message_id: targetMessageId,
        customer: custPhone,
        status: dcmsRes.claimed ? 'BERHASIL' : (dcmsRes.already_claimed ? 'DITOLAK_LATE' : 'GAGAL'),
        response: dcmsRes.message || JSON.stringify(dcmsRes)
      });
    } else {
      // =====================================================================
      // AKSI: TIM FOLLOW UP MEMBATALKAN REAKSI (CLEAR_REACTION_FU)
      // =====================================================================
      logger.info(`[Reaksi Tim Follow Up Batal] Reaksi dicabut oleh ${fuPhone} pada pesan keep ${targetMessageId}`);

      const payload = {
        action: 'CLEAR_REACTION_FU',
        message_id: targetMessageId,
        customerPhone: custPhone,
        nomorFU: fuPhone,
        group_id: remoteJid
      };

      const dcmsRes = await sendToDCMS(payload);

      addReactionLog({
        type: 'UNCLAIM',
        group: remoteJid.replace('@g.us', ''),
        sales: fuPhone,
        emoji: '❌ (Batal)',
        message_id: targetMessageId,
        customer: custPhone,
        status: dcmsRes.unclaimed ? 'BERHASIL' : 'GAGAL',
        response: dcmsRes.message || JSON.stringify(dcmsRes)
      });
    }
  }
}

// ==============================================================================
// 4. FORWARDER HTTP KE WEBHOOK DCMS
// ==============================================================================
// 4A. Forwarder Reaksi Format Keep (Tab 4)
async function sendToDCMS(payload) {
  try {
    const res = await axios.post(DCMS_WEBHOOK_URL, payload, {
      headers: {
        'Content-Type': 'application/json',
        'X-API-KEY': DCMS_API_KEY
      },
      timeout: 12000
    });
    return res.data || { success: true };
  } catch (err) {
    const errorMsg = err.response?.data?.message || err.message;
    logger.error(`[DCMS Webhook Gagal] ${errorMsg}`);
    return { success: false, message: `Koneksi DCMS Gagal: ${errorMsg}` };
  }
}

// 4B. Forwarder Chat Personal Masuk (Inbox Tab 9)
async function sendToDCMSInbox(payload) {
  try {
    const res = await axios.post(DCMS_INBOX_WEBHOOK_URL, payload, {
      headers: {
        'Content-Type': 'application/json',
        'X-API-KEY': DCMS_API_KEY
      },
      timeout: 12000
    });
    return res.data || { success: true };
  } catch (err) {
    const errorMsg = err.response?.data?.message || err.message;
    logger.error(`[DCMS Inbox Webhook Gagal] ${errorMsg}`);
    return { success: false, message: `Koneksi DCMS Inbox Gagal: ${errorMsg}` };
  }
}

// ==============================================================================
// 5. SERVER EXPRESS WEB DASHBOARD & API GATEWAY (RENDER.COM COMPATIBLE)
// ==============================================================================
const app = express();
app.use(express.json({ limit: '20mb' }));
app.use(express.urlencoded({ extended: true, limit: '20mb' }));

// Endpoint Healthcheck untuk Render
app.get('/health', (req, res) => {
  res.json({
    status: 'ok',
    connection: connectionState,
    uptime: Math.round(process.uptime()),
    timestamp: new Date().toISOString()
  });
});

// Endpoint Status Lengkap untuk DCMS Backend / Dashboard
app.get('/api/status', (req, res) => {
  const sessionId = String(req.query.session || 'default').trim().toLowerCase();
  const session = getOrCreateSession(sessionId);

  // Jika sesi admin diminta tapi belum diinisialisasi, jalankan socket on-demand
  if (sessionId !== 'default' && !session.sock && session.state === 'DISCONNECTED') {
    startBaileysSocket(sessionId).catch(err => logger.error(`Gagal start socket on-demand (${sessionId}):`, err));
  }

  const u = session.user || (sessionId === 'default' ? connectedUser : null);
  const botPhone = u?.id ? u.id.split(':')[0].replace(/\D/g, '') : null;
  const isConn = session.state === 'CONNECTED';
  const qrData = session.qr || (sessionId === 'default' ? currentQR : null);

  res.json({
    success: true,
    session: sessionId,
    connected: isConn,
    state: session.state,
    phone: botPhone,
    name: u?.name || (sessionId === 'default' ? 'Bot DCMS' : `Admin ${sessionId.toUpperCase()}`),
    hasQR: Boolean(qrData),
    qr: qrData,
    uptime: Math.round(process.uptime()),
    cloudAutoSave: {
      enabled: true,
      restoredFromDCMS: session.restoredFromDCMS,
      lastPushedAt: session.lastPushedAt
    },
    timestamp: new Date().toISOString()
  });
});

// Endpoint Live Logs untuk Tampilan Dashboard DCMS (Log Render Baileys)
app.get('/api/logs', (req, res) => {
  const botPhone = connectedUser?.id ? connectedUser.id.split(':')[0].replace(/\D/g, '') : null;
  const uptimeSec = Math.round(process.uptime());
  const hours = Math.floor(uptimeSec / 3600);
  const minutes = Math.floor((uptimeSec % 3600) / 60);

  res.json({
    success: true,
    server_time: new Date().toISOString(),
    server_time_wita: new Date().toLocaleTimeString('id-ID', { timeZone: 'Asia/Makassar', hour12: false }) + ' WITA',
    uptime_seconds: uptimeSec,
    uptime_formatted: `${hours} jam ${minutes} menit`,
    connection_state: connectionState,
    bot_phone: botPhone,
    bot_name: connectedUser?.name || 'Bot Gadgetmart Sosmed',
    has_qr: Boolean(currentQR),
    cron_stats: cronStats,
    logs: runtimeLogs,
    reaction_logs: reactionLogs.slice(0, 30)
  });
});

// Endpoint Reset Sesi & Request Fresh QR (Dipanggil dari DCMS saat nomor ganti atau disuspend)
app.post('/api/reset', async (req, res) => {
  const apiKey = req.headers['x-api-key'] || req.query.api_key || req.body?.api_key;
  if (DCMS_API_KEY && apiKey && apiKey !== DCMS_API_KEY) {
    return res.status(401).json({ success: false, message: 'Invalid API Key' });
  }

  try {
    await resetBaileysSession('Permintaan Reset dari API / DCMS');
    res.json({
      success: true,
      message: 'Sesi WhatsApp Baileys berhasil direset. Silakan tunggu beberapa detik untuk QR Code baru.'
    });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

// Alias Logout
app.post('/api/logout', async (req, res) => {
  const apiKey = req.headers['x-api-key'] || req.query.api_key || req.body?.api_key;
  if (DCMS_API_KEY && apiKey && apiKey !== DCMS_API_KEY) {
    return res.status(401).json({ success: false, message: 'Invalid API Key' });
  }

  try {
    await resetBaileysSession('Permintaan Logout dari API / DCMS');
    res.json({
      success: true,
      message: 'Sesi WhatsApp Baileys berhasil dilogout & direset.'
    });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

// Endpoint API Kirim Pesan Outbound (Dipanggil oleh DCMS Tab 9 saat Admin Membalas Chat)
app.post('/api/send-message', async (req, res) => {
  const apiKey = req.headers['x-api-key'] || req.query.api_key;
  if (DCMS_API_KEY && apiKey && apiKey !== DCMS_API_KEY) {
    return res.status(401).json({ success: false, message: 'Invalid API Key' });
  }

  const sessionId = String(req.body.session || req.query.session || 'default').trim().toLowerCase();
  const session = sessions.get(sessionId) || (sessionId === 'default' ? getOrCreateSession('default') : null);

  if (!session || session.state !== 'CONNECTED' || !session.sock) {
    return res.status(503).json({
      success: false,
      session: sessionId,
      message: `WhatsApp (${sessionId}) belum terhubung! Silakan scan QR atau pairing terlebih dahulu.`
    });
  }
  const targetSock = session.sock;

  try {
    const rawPhone = req.body.phone || req.body.chatId || '';
    let cleanPhone = rawPhone.replace(/\D/g, '');
    if (cleanPhone.startsWith('0')) {
      cleanPhone = '62' + cleanPhone.substring(1);
    }
    if (!cleanPhone || cleanPhone.length < 9) {
      return res.status(400).json({ success: false, message: 'Nomor telepon tujuan tidak valid.' });
    }

    const jid = cleanPhone.includes('@') ? cleanPhone : `${cleanPhone}@s.whatsapp.net`;
    const message = (req.body.message || req.body.text || '').trim();
    const mediaUrl = req.body.media_url || req.body.file_url || null;
    const fileBase64 = req.body.file_base64 || null;
    const mediaType = req.body.media_type || (mediaUrl && /\.(jpg|jpeg|png|webp|gif)/i.test(mediaUrl) ? 'image' : 'document');
    const replyToId = req.body.reply_to || req.body.replyToId || null;
    const replyText = req.body.reply_text || '';

    let quoteOptions = {};
    if (replyToId) {
      quoteOptions.quoted = {
        key: {
          remoteJid: jid,
          id: replyToId,
          fromMe: false
        },
        message: {
          conversation: replyText || 'Pesan'
        }
      };
    }

    let sentResult = null;

    if (fileBase64) {
      const buffer = Buffer.from(fileBase64, 'base64');
      const mimetype = req.body.mimetype || 'application/octet-stream';
      const filename = req.body.filename || `file_${Date.now()}`;
      if (mediaType === 'image' || mimetype.startsWith('image/')) {
        sentResult = await sock.sendMessage(jid, { image: buffer, caption: message }, quoteOptions);
      } else {
        sentResult = await sock.sendMessage(jid, { document: buffer, mimetype, fileName: filename, caption: message }, quoteOptions);
      }
    } else if (mediaUrl) {
      if (mediaType === 'image') {
        sentResult = await sock.sendMessage(jid, { image: { url: mediaUrl }, caption: message }, quoteOptions);
      } else {
        const filename = req.body.filename || path.basename(mediaUrl.split('?')[0]) || 'document.pdf';
        sentResult = await sock.sendMessage(jid, { document: { url: mediaUrl }, fileName: filename, caption: message }, quoteOptions);
      }
    } else {
      sentResult = await targetSock.sendMessage(jid, { text: message }, quoteOptions);
    }

    const sentId = sentResult?.key?.id || `out_${Date.now()}`;
    if (sentId && sentResult?.message) {
      saveRawMessage(sentId, sentResult.message);
    }
    logger.info(`[Outbound Chat Terkirim] ID: ${sentId} ke ${cleanPhone}`);

    addReactionLog({
      type: 'INBOX_OUT',
      group: 'DM_PERSONAL',
      sales: cleanPhone,
      emoji: '📤',
      message_id: sentId,
      customer: cleanPhone,
      status: 'TERKIRIM',
      response: message.substring(0, 50) || (mediaType ? `[${mediaType}]` : '-')
    });

    return res.json({
      success: true,
      message_id: sentId,
      messageId: sentId,
      status: '200',
      phone: cleanPhone,
      message: 'Pesan berhasil dikirim via Baileys WhatsApp Gateway'
    });
  } catch (err) {
    logger.error('[Outbound Gagal]', err);
    return res.status(500).json({
      success: false,
      message: err.message || 'Gagal mengirim pesan via WhatsApp'
    });
  }
});

// Endpoint Berlangganan Presence Update (Status Sedang Mengetik)
app.post('/api/subscribe-presence', async (req, res) => {
  try {
    const rawPhone = req.body.phone || req.body.chatId || '';
    let cleanPhone = rawPhone.replace(/\D/g, '');
    if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);
    const jid = cleanPhone.includes('@') ? cleanPhone : `${cleanPhone}@s.whatsapp.net`;
    if (sock && sock.presenceSubscribe) {
      await sock.presenceSubscribe(jid);
    }
    return res.json({ success: true, message: `Subscribed presence for ${cleanPhone}` });
  } catch (err) {
    return res.json({ success: false, message: err.message });
  }
});

// Endpoint Dapatkan URL Foto Profil WhatsApp Kontak
app.get('/api/profile-picture', async (req, res) => {
  try {
    const rawPhone = req.query.phone || req.query.jid || '';
    let cleanPhone = rawPhone.replace(/\D/g, '');
    if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);
    const jid = rawPhone.includes('@') ? rawPhone : `${cleanPhone}@s.whatsapp.net`;

    if (!sock || connectionState !== 'CONNECTED') {
      return res.json({ success: false, url: null, message: 'WhatsApp bot belum terhubung' });
    }

    let ppUrl = null;
    try {
      ppUrl = await sock.profilePictureUrl(jid, 'image');
    } catch (e1) {
      try {
        ppUrl = await sock.profilePictureUrl(jid, 'preview');
      } catch (e2) {}
    }

    if (ppUrl) {
      return res.json({ success: true, url: ppUrl });
    } else {
      return res.json({ success: false, url: null, message: 'Tidak ada foto profil atau disembunyikan' });
    }
  } catch (err) {
    return res.json({ success: false, url: null, message: err.message });
  }
});

// Endpoint Dapatkan Pairing Code (Alternatif Scan QR untuk Cloud Deployment)
app.post('/api/pair', async (req, res) => {
  const phone = (req.body.phone || '').replace(/\D/g, '');
  if (!phone || phone.length < 10) {
    return res.status(400).json({ success: false, message: 'Format nomor tidak valid. Contoh: 628123456789' });
  }

  if (connectionState === 'CONNECTED') {
    return res.json({ success: false, message: 'WhatsApp sudah terhubung!' });
  }

  try {
    if (!sock) {
      await startBaileysSocket();
      await delay(2000);
    }
    const code = await sock.requestPairingCode(phone);
    currentPairingCode = code;
    return res.json({ success: true, pairingCode: code });
  } catch (err) {
    logger.error('Pairing code error:', err);
    return res.status(500).json({ success: false, message: err.message || 'Gagal meminta kode pairing' });
  }
});

// Endpoint Test Webhook Langsung ke DCMS
app.post('/api/test-webhook', async (req, res) => {
  const testPayload = {
    action: 'TEST_PING',
    source: 'BAILEYS_RENDER_BOT',
    time: new Date().toISOString()
  };
  const result = await sendToDCMS(testPayload);
  res.json(result);
});

// Dashboard Utama Web UI
app.get('/', (req, res) => {
  const statusColor = connectionState === 'CONNECTED' ? '#10b981' : (connectionState === 'WAITING_QR' ? '#f59e0b' : '#ef4444');
  const statusText = connectionState === 'CONNECTED'
    ? `🟢 TERHUBUNG (${connectedUser?.id?.split(':')[0] || 'Bot WhatsApp'})`
    : (connectionState === 'WAITING_QR' ? '🟡 MENUNGGU SCAN QR / PAIRING' : '🔴 TERPUTUS / RECONNECTING');

  const rows = reactionLogs.map((l, i) => {
    let typeBg = '#ecfdf5';
    let typeColor = '#047857';
    if (l.type === 'UNCLAIM') {
      typeBg = '#fef2f2';
      typeColor = '#b91c1c';
    } else if (l.type === 'INBOX_IN') {
      typeBg = '#eff6ff';
      typeColor = '#1d4ed8';
    } else if (l.type === 'INBOX_OUT') {
      typeBg = '#f5f3ff';
      typeColor = '#6d28d9';
    } else if (l.type === 'SYSTEM') {
      typeBg = '#f8fafc';
      typeColor = '#475569';
    }

    let statusBg = '#d1fae5';
    let statusColor = '#065f46';
    if (l.status === 'DITOLAK_LATE') {
      statusBg = '#fef3c7';
      statusColor = '#92400e';
    } else if (l.status === 'GAGAL') {
      statusBg = '#fee2e2';
      statusColor = '#991b1b';
    } else if (l.status === 'DITERUSKAN' || l.status === 'TERKIRIM') {
      statusBg = '#e0e7ff';
      statusColor = '#3730a3';
    }

    return `
    <tr style="border-bottom: 1px solid #f1f5f9; font-size: 12px;">
      <td style="padding: 10px; font-weight: bold; color: #475569;">${l.time}</td>
      <td style="padding: 10px;"><span style="background: ${typeBg}; color: ${typeColor}; padding: 2px 8px; border-radius: 6px; font-weight: bold; font-size: 11px;">${l.type}</span></td>
      <td style="padding: 10px; font-family: monospace;">${l.sales || '-'}</td>
      <td style="padding: 10px; font-size: 16px;">${l.emoji || '-'}</td>
      <td style="padding: 10px; font-family: monospace; color: #64748b;">${l.group || '-'}</td>
      <td style="padding: 10px;"><span style="background: ${statusBg}; color: ${statusColor}; padding: 3px 8px; border-radius: 6px; font-weight: bold;">${l.status}</span></td>
      <td style="padding: 10px; color: #334155;">${l.response || '-'}</td>
    </tr>
  `;
  }).join('');

  res.send(`
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DCMS - Baileys Reaction Bot (Render.com)</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    body { background-color: #f8fafc; color: #0f172a; padding: 24px; }
    .container { max-width: 1100px; margin: 0 auto; }
    .header { background: #ffffff; padding: 20px 24px; border-radius: 16px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .status-badge { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 9999px; font-size: 12px; font-weight: bold; background: #ffffff; border: 1.5px solid ${statusColor}; color: ${statusColor}; }
    .grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 24px; margin-bottom: 24px; }
    .card { background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .card-title { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #334155; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; }
    .qr-box { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 250px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 16px; text-align: center; }
    .qr-box img { max-width: 220px; border-radius: 8px; border: 4px solid #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .btn { background: #059669; color: #ffffff; border: none; padding: 9px 16px; border-radius: 8px; font-size: 12px; font-weight: bold; cursor: pointer; transition: background 0.15s; }
    .btn:hover { background: #047857; }
    .input { border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 13px; width: 100%; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 10px; background: #f1f5f9; color: #475569; font-size: 11px; text-transform: uppercase; font-weight: 800; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h1 style="font-size: 20px; font-weight: 900; color: #0f172a;">⚡ DCMS Baileys Reaction Bot</h1>
        <p style="font-size: 12px; color: #64748b; margin-top: 3px;">Auto-Claim Format Keep via WhatsApp Emoji Reactions (Render.com Cloud PaaS)</p>
      </div>
      <div class="status-badge">${statusText}</div>
    </div>

    <div class="grid">
      <!-- Kolom 1: Otentikasi WhatsApp (Scan QR atau Pairing Code) -->
      <div class="card">
        <div class="card-title">
          <span>Otentikasi WhatsApp</span>
          <div style="display: flex; gap: 6px;">
            <button class="btn" style="padding: 4px 10px; font-size: 11px; background: #dc2626;" onclick="resetSession()">🗑️ Reset Sesi (Ganti Nomor)</button>
            <button class="btn" style="padding: 4px 10px; font-size: 11px;" onclick="location.reload()">🔄 Refresh</button>
          </div>
        </div>

        ${connectionState === 'CONNECTED' ? `
          <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 16px; text-align: center;">
            <div style="font-size: 32px; margin-bottom: 8px;">✅</div>
            <h3 style="color: #065f46; font-size: 15px; font-weight: bold;">WhatsApp Aktif & Siap!</h3>
            <p style="color: #047857; font-size: 12px; margin-top: 4px;">Akun: <strong>${connectedUser?.id?.split(':')[0]}</strong></p>
            <p style="color: #047857; font-size: 12px;">Nama: ${connectedUser?.name || 'Bot DCMS'}</p>
            <div style="margin-top: 12px; padding: 8px 12px; background: #ffffff; border-radius: 8px; border: 1px solid #6ee7b7; font-size: 11px; color: #047857; display: flex; align-items: center; justify-content: center; gap: 6px;">
              <span>☁️</span>
              <span><strong>Cloud Auto-Save Aktif:</strong> Sesi aman di database DCMS. Jika Render redeploy, bot langsung konek tanpa scan ulang.</span>
            </div>
          </div>
        ` : `
          <div class="qr-box">
            ${currentQR ? `
              <img src="${currentQR}" alt="Scan QR Code WhatsApp" />
              <p style="font-size: 12px; color: #475569; margin-top: 12px; font-weight: 600;">Scan QR ini dengan aplikasi WhatsApp di ponsel Anda</p>
              <p style="font-size: 11px; color: #94a3b8; margin-top: 2px;">QR otomatis berganti tiap 20 detik</p>
            ` : `
              <p style="font-size: 13px; color: #64748b;">Membuat sesi WhatsApp... Jika belum muncul, klik tombol Refresh di atas.</p>
            `}
          </div>

          <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
            <p style="font-size: 12px; font-weight: bold; color: #334155; margin-bottom: 6px;">Atau Tautkan via Pairing Code:</p>
            <input type="text" id="pairPhone" class="input" placeholder="Nomor WhatsApp (Contoh: 628123456789)" />
            <button class="btn" style="width: 100%;" onclick="requestPairing()">Dapatkan Kode Pairing (8 Digit)</button>
            <div id="pairResult" style="display: none; margin-top: 10px; padding: 10px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; text-align: center; font-size: 18px; font-weight: 900; letter-spacing: 2px; color: #0f172a;"></div>
          </div>
        `}

        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b; line-height: 1.6;">
          <strong>Target Webhook DCMS:</strong><br/>
          <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; word-break: break-all;">${DCMS_WEBHOOK_URL}</code>
        </div>
      </div>

      <!-- Kolom 2: Info & Statistik -->
      <div class="card">
        <div class="card-title">
          <span>Konfigurasi & Parameter Bot</span>
          <button class="btn" style="background: #3b82f6;" onclick="testWebhook()">⚡ Tes Webhook DCMS</button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px;">
            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: bold;">Logika Klaim</span>
            <div style="font-size: 14px; font-weight: 800; color: #0f172a; margin-top: 3px;">Siapa Cepat Dia Dapat</div>
            <span style="font-size: 10px; color: #10b981;">First-reaction lock aktif</span>
          </div>
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px;">
            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: bold;">Status Otomatis</span>
            <div style="font-size: 14px; font-weight: 800; color: #0f172a; margin-top: 3px;">ON PROGRESS</div>
            <span style="font-size: 10px; color: #3b82f6;">Auto-update status keep</span>
          </div>
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px;">
            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: bold;">Filter Grup</span>
            <div style="font-size: 14px; font-weight: 800; color: #0f172a; margin-top: 3px;">${TARGET_GROUPS.length > 0 ? TARGET_GROUPS.length + ' Grup Cabang' : 'Semua Grup Aktif'}</div>
            <span style="font-size: 10px; color: #64748b;">18 Grup WhatsApp Cabang</span>
          </div>
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px;">
            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: bold;">Keep-Alive Render</span>
            <div style="font-size: 14px; font-weight: 800; color: #0f172a; margin-top: 3px;">${RENDER_EXTERNAL_URL ? 'Aktif' : 'Standar'}</div>
            <span style="font-size: 10px; color: #64748b;">Anti-sleep interval 10m</span>
          </div>
        </div>

        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px; font-size: 12px; color: #166534; line-height: 1.5;">
          💡 <strong>Cara Kerja:</strong> Ketika admin sosmed mengirim format keep di grup WhatsApp cabang, staf toko cukup memberi reaksi emoji (misal jempol 👍) pada pesan tersebut. Bot akan menangkap event dan mengunci transaksi atas nama sales tersebut di DCMS secara realtime!
        </div>
      </div>
    </div>

    <!-- Tabel Riwayat Reaksi Realtime -->
    <div class="card">
      <div class="card-title">
        <span>Riwayat Aktivitas Reaksi Realtime (Auto-Claim Stream)</span>
        <span style="font-size: 11px; color: #64748b; font-weight: normal;">Diperbarui otomatis</span>
      </div>

      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Aksi</th>
              <th>Sales (PIC)</th>
              <th>Emoji</th>
              <th>Grup ID</th>
              <th>Status DCMS</th>
              <th>Respons API</th>
            </tr>
          </thead>
          <tbody>
            ${rows.length > 0 ? rows : '<tr><td colspan="7" style="padding: 24px; text-align: center; color: #94a3b8; font-size: 12px;">Belum ada aktivitas reaksi yang tercatat.</td></tr>'}
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    ${connectionState !== 'CONNECTED' ? 'setTimeout(() => location.reload(), 15000);' : ''}

    async function resetSession() {
      if (!confirm('Yakin ingin mereset sesi WhatsApp? File autentikasi lama akan dihapus dan QR Code baru akan dibuat untuk menghubungkan nomor baru.')) return;
      try {
        const res = await fetch('/api/reset', { method: 'POST' });
        const data = await res.json();
        alert(data.message || 'Sesi WhatsApp berhasil direset.');
        setTimeout(() => location.reload(), 2000);
      } catch (e) {
        alert('Gagal mereset sesi: ' + e.message);
      }
    }

    async function requestPairing() {
      const phone = document.getElementById('pairPhone').value;
      const resDiv = document.getElementById('pairResult');
      if (!phone) { alert('Masukkan nomor WhatsApp terlebih dahulu!'); return; }

      resDiv.style.display = 'block';
      resDiv.innerHTML = 'Meminta kode...';

      try {
        const res = await fetch('/api/pair', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ phone })
        });
        const data = await res.json();
        if (data.success && data.pairingCode) {
          resDiv.innerHTML = '<span style="color:#059669;">Kode: ' + data.pairingCode + '</span><br><span style="font-size:11px; font-weight:normal; color:#64748b;">Masukkan kode ini pada WhatsApp > Perangkat Tertaut > Tautkan dengan nomor telepon</span>';
        } else {
          resDiv.innerHTML = '<span style="color:#e11d48; font-size:12px;">' + (data.message || 'Gagal meminta kode') + '</span>';
        }
      } catch (e) {
        resDiv.innerHTML = '<span style="color:#e11d48; font-size:12px;">Error: ' + e.message + '</span>';
      }
    }

    async function testWebhook() {
      try {
        const res = await fetch('/api/test-webhook', { method: 'POST' });
        const data = await res.json();
        alert('Respons DCMS Webhook:\\n' + JSON.stringify(data, null, 2));
      } catch (e) {
        alert('Gagal menghubungi DCMS:\\n' + e.message);
      }
    }
  </script>
</body>
</html>
  `);
});

// ==============================================================================
// 6. KEEP-ALIVE SELF-PING INTERVAL (ANTI-SLEEP DI RENDER FREE TIER)
// ==============================================================================
if (RENDER_EXTERNAL_URL) {
  setInterval(async () => {
    try {
      await axios.get(`${RENDER_EXTERNAL_URL}/health`, { timeout: 8000 });
      logger.info('Keep-alive ping sent to ' + RENDER_EXTERNAL_URL);
    } catch (e) {
      // Ignore keep-alive errors
    }
  }, 10 * 60 * 1000); // Setiap 10 menit
}

// ==============================================================================
// 7. AUTO CRON SCHEDULER UNTUK DCMS FOLLOW-UP H+1 STRICT 24 JAM
// ==============================================================================
let isCronRunning = false;

async function triggerDcmsAutoFuCron() {
  if (isCronRunning) return;
  isCronRunning = true;
  cronStats.total_runs++;
  cronStats.last_run_time = new Date().toISOString();

  let targetUrl = DCMS_CRON_URL;
  let isFallback = false;

  try {
    let res;
    try {
      res = await axios.get(targetUrl, { 
        timeout: 40000,
        headers: { 'Accept': 'application/json, text/html' }
      });
    } catch (primaryErr) {
      // Jika 404 (endpoint belum sync di cPanel) atau 500 timeout, coba fallback ke migrate endpoint
      const statusCode = primaryErr.response?.status;
      if (statusCode === 404 || statusCode === 500 || statusCode === 504) {
        logger.warn(`[Render Cron Scheduler] Endpoint utama ${targetUrl} respon ${statusCode}. Mencoba fallback ke ${DCMS_FALLBACK_CRON_URL}...`);
        targetUrl = DCMS_FALLBACK_CRON_URL;
        isFallback = true;
        res = await axios.get(targetUrl, { timeout: 60000 });
      } else {
        throw primaryErr;
      }
    }

    // Evaluasi respon (baik JSON maupun HTML)
    let isSuccess = false;
    let message = '';
    let outputDetail = '';

    if (res.data && typeof res.data === 'object') {
      isSuccess = !!res.data.success;
      message = res.data.message || (isSuccess ? 'Cron H+1 berhasil dieksekusi.' : 'Cron merespon tanpa status sukses.');
      outputDetail = res.data.output ? res.data.output.replace(/[\r\n]+/g, ' | ').trim() : '';
    } else if (typeof res.data === 'string') {
      if (res.data.includes('PROSES SELESAI DENGAN SUKSES') || res.data.includes('EKSEKUSI CRON AUTO FOLLOW-UP H+1')) {
        isSuccess = true;
        message = isFallback 
          ? 'Cron H+1 berhasil diproses via Fallback Engine (system/migrate).' 
          : 'Cron H+1 berhasil diproses.';
        const preMatch = res.data.match(/<pre[^>]*>([\s\S]*?)<\/pre>/i);
        outputDetail = preMatch ? preMatch[1].replace(/[\r\n]+/g, ' | ').trim().slice(0, 300) : 'Antrean berhasil diperiksa';
      } else {
        message = 'Respon HTML dari server cPanel tanpa konfirmasi sukses.';
        outputDetail = res.data.slice(0, 150);
      }
    }

    if (isSuccess) {
      cronStats.successful_runs++;
      cronStats.last_status = 'SUCCESS';
      cronStats.last_message = message;
      addRuntimeLog('CRON', 'SUCCESS', `Trigger Cron H+1 Sukses: ${message}`, `Target: ${targetUrl} | Detail: ${outputDetail || 'Antrean aman'}`);
      logger.info(`[Render Cron Scheduler] ${message}`);
    } else {
      cronStats.failed_runs++;
      cronStats.last_status = 'WARNING';
      cronStats.last_message = message;
      addRuntimeLog('CRON', 'WARN', `Trigger Cron H+1 Direspon: ${message}`, `Target: ${targetUrl} | Detail: ${outputDetail}`);
      logger.warn(`[Render Cron Scheduler] Respon DCMS: ${message}`);
    }
  } catch (err) {
    cronStats.failed_runs++;
    cronStats.last_status = 'ERROR';
    const statusText = err.response ? `HTTP ${err.response.status}` : err.message;
    cronStats.last_message = `Gagal: ${statusText}`;
    addRuntimeLog('CRON', 'ERROR', `Gagal Menghubungi DCMS Cron: ${statusText}`, `Target URL: ${targetUrl} (Error: ${err.message})`);
    logger.warn(`[Render Cron Scheduler] Gagal panggil cron DCMS (${targetUrl}): ${err.message}`);
  } finally {
    isCronRunning = false;
  }
}

// Jalankan pertama kali 30 detik setelah bot start, lalu berkala setiap 5 menit
setTimeout(triggerDcmsAutoFuCron, 30 * 1000);
setInterval(triggerDcmsAutoFuCron, 5 * 60 * 1000);

// Start Server
app.listen(PORT, () => {
  logger.info(`🌐 Web Dashboard aktif di port ${PORT}`);
  startBaileysSocket().catch(err => logger.error('Gagal inisialisasi Baileys:', err));
});
