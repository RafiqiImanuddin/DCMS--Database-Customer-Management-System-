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
  delay
} = require('@whiskeysockets/baileys');

// ==============================================================================
// 1. KONFIGURASI APLIKASI
// ==============================================================================
const PORT = process.env.PORT || 3000;
const DCMS_WEBHOOK_URL = process.env.DCMS_WEBHOOK_URL || 'https://dcms-sosmed.my.id/api/fonnte-webhook';
const DCMS_API_KEY = process.env.DCMS_API_KEY || 'dcms2026';
const TARGET_GROUPS = (process.env.TARGET_GROUPS || '')
  .split(',')
  .map(g => g.trim())
  .filter(Boolean);
const RENDER_EXTERNAL_URL = process.env.RENDER_EXTERNAL_URL || '';

const logger = pino({ level: 'info' });

// State Runtime Bot
let sock = null;
let connectionState = 'DISCONNECTED'; // 'DISCONNECTED' | 'CONNECTING' | 'WAITING_QR' | 'CONNECTED'
let currentQR = null;
let currentPairingCode = null;
let connectedUser = null;
let lastDisconnectReason = null;

// Riwayat buffer in-memory untuk pesan keep (maksimal 1.000 pesan terakhir)
const messageCache = new Map();
const MAX_CACHE = 1000;

// Riwayat log aktivitas reaksi (maksimal 40 log terakhir untuk tampilan dashboard)
const reactionLogs = [];
const MAX_LOGS = 40;

function addReactionLog(log) {
  reactionLogs.unshift({
    id: Date.now() + Math.random().toString(36).substring(2, 6),
    time: new Date().toLocaleTimeString('id-ID', { timeZone: 'Asia/Makassar', hour12: false }) + ' WITA',
    ...log
  });
  if (reactionLogs.length > MAX_LOGS) {
    reactionLogs.pop();
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

// ==============================================================================
// 3. INISIALISASI BAILEYS WHATSAPP CLIENT
// ==============================================================================
async function startBaileysSocket() {
  const authDir = path.join(__dirname, 'auth_info');
  if (!fs.existsSync(authDir)) {
    fs.mkdirSync(authDir, { recursive: true });
  }

  const { state, saveCreds } = await useMultiFileAuthState(authDir);
  const { version, isLatest } = await fetchLatestBaileysVersion();
  logger.info(`Memulai Baileys v${version.join('.')} (Latest: ${isLatest})...`);

  connectionState = 'CONNECTING';

  sock = makeWASocket({
    version,
    auth: state,
    logger: pino({ level: 'silent' }), // Heningkan log verbose Baileys
    printQRInTerminal: true,
    browser: ['DCMS Reaction Bot', 'Chrome', '1.0.0'],
    generateHighQualityLinkPreview: false,
    syncFullHistory: false
  });

  // Simpan kredensial setiap kali diperbarui
  sock.ev.on('creds.update', saveCreds);

  // Monitor Pembaruan Koneksi (QR, Status, Reconnect)
  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      currentQR = await qrcode.toDataURL(qr, { margin: 2, scale: 7 });
      connectionState = 'WAITING_QR';
      logger.info('Kode QR baru dihasilkan, siap di-scan.');
    }

    if (connection === 'close') {
      currentQR = null;
      connectedUser = null;
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
      lastDisconnectReason = statusCode;
      connectionState = 'DISCONNECTED';

      logger.warn(`Koneksi terputus (Status: ${statusCode}). Reconnect: ${shouldReconnect}`);

      if (shouldReconnect) {
        logger.info('Mencoba menghubungkan kembali dalam 5 detik...');
        setTimeout(() => {
          startBaileysSocket().catch(err => logger.error('Gagal restart socket:', err));
        }, 5000);
      } else {
        logger.error('Sesi telah Logout dari perangkat WhatsApp. Hapus folder auth_info untuk scan ulang.');
      }
    } else if (connection === 'open') {
      connectionState = 'CONNECTED';
      currentQR = null;
      currentPairingCode = null;
      connectedUser = sock.user;
      logger.info(`✅ Baileys Berhasil Terhubung! Akun: ${sock.user.id.split(':')[0]} (${sock.user.name || 'Bot'})`);
      addReactionLog({
        type: 'SYSTEM',
        group: 'GLOBAL',
        sales: sock.user.id.split(':')[0],
        emoji: '🟢',
        message: 'Bot WhatsApp berhasil terhubung ke jaringan WhatsApp.'
      });
    }
  });

  // ----------------------------------------------------------------------------
  // LISTENER 1: SIMPAN FORMAT KEEP MASUK KE IN-MEMORY CACHE & DETEKSI REAKSI UPSERT
  // ----------------------------------------------------------------------------
  sock.ev.on('messages.upsert', async ({ messages, type }) => {
    if (!Array.isArray(messages)) return;

    for (const msg of messages) {
      if (!msg.key || !msg.message) continue;
      const remoteJid = msg.key.remoteJid || '';

      // Abaikan jika bukan dari grup
      if (!remoteJid.endsWith('@g.us')) continue;

      // 1A. Deteksi reaksi emoji via messages.upsert (lengkap dengan pushName WhatsApp)
      const reactionMsg = msg.message?.reactionMessage;
      if (reactionMsg && reactionMsg.key) {
        const targetMessageId = reactionMsg.key.id;
        // Orang yang me-react adalah participant pengirim pesan reaksi ini (Tim Follow Up)
        const reactorJid = msg.key?.participant || msg.participant || (msg.key?.fromMe ? (sock.user?.id || '') : '');
        const emoji = (reactionMsg.text || '').trim();
        const pushName = msg.pushName || '';
        await handleReactionEvent(targetMessageId, remoteJid, reactorJid, emoji, pushName);
        continue;
      }

      // 1B. Ambil isi teks pesan format keep
      const text = msg.message?.conversation ||
        msg.message?.extendedTextMessage?.text ||
        msg.message?.imageMessage?.caption || '';

      if (!text) continue;

      // Cek apakah format keep yang dikirimkan oleh Tim Admin Sosmed
      const isFormatKeep = /format\s*(?:keep|kredit|po|preorder)|barang\s*(?:keep|po|kredit)|remar?ks/i.test(text);
      if (isFormatKeep) {
        const custPhone = extractCustomerPhone(text);
        // Sender di sini adalah Admin Sosmed yang mengirimkan format keep ke grup
        const adminSosmedSender = (msg.key.participant || msg.participant || '').replace(/@.+/, '');

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
      }
    }
  });

  // ----------------------------------------------------------------------------
  // LISTENER 2: REAKSI EMOJI WHATSAPP DARI TIM FOLLOW UP
  // ----------------------------------------------------------------------------
  sock.ev.on('messages.reaction', async (reactions) => {
    if (!Array.isArray(reactions)) return;

    for (const r of reactions) {
      try {
        const remoteJid = r.key?.remoteJid || '';
        const targetMessageId = r.key?.id;

        // PENTING: Orang yang me-react (Tim Follow Up) diambil dari r.reaction.key.participant!
        // JANGAN pernah menggunakan r.key.participant karena itu adalah pengirim format keep (Admin Sosmed)
        const reactorJid = r.reaction?.key?.participant || 
                           (r.reaction?.key?.fromMe ? (sock.user?.id || '') : '') || 
                           r.participant || '';
        const emoji = (r.reaction?.text || '').trim();

        await handleReactionEvent(targetMessageId, remoteJid, reactorJid, emoji);
      } catch (err) {
        logger.error('[Error Reaksi]', err.message);
      }
    }
  });

  // Helper pemrosesan reaksi emoji Tim Follow Up
  async function handleReactionEvent(targetMessageId, remoteJid, reactorJid, emoji, pushName = '') {
    if (!targetMessageId || !remoteJid) return;
    if (!remoteJid.endsWith('@g.us')) return;
    if (TARGET_GROUPS.length > 0 && !TARGET_GROUPS.includes(remoteJid)) return;

    // Nomor HP staf Tim Follow Up yang me-react format keep
    const fuPhone = (reactorJid || '').replace(/@.+/, '').replace(/\D/g, '');
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

// ==============================================================================
// 5. SERVER EXPRESS WEB DASHBOARD (RENDER.COM COMPATIBLE)
// ==============================================================================
const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Endpoint Healthcheck untuk Render
app.get('/health', (req, res) => {
  res.json({
    status: 'ok',
    connection: connectionState,
    uptime: Math.round(process.uptime()),
    timestamp: new Date().toISOString()
  });
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

  const rows = reactionLogs.map((l, i) => `
    <tr style="border-bottom: 1px solid #f1f5f9; font-size: 12px;">
      <td style="padding: 10px; font-weight: bold; color: #475569;">${l.time}</td>
      <td style="padding: 10px;"><span style="background: ${l.type === 'CLAIM' ? '#ecfdf5' : '#fef2f2'}; color: ${l.type === 'CLAIM' ? '#047857' : '#b91c1c'}; padding: 2px 8px; border-radius: 6px; font-weight: bold; font-size: 11px;">${l.type}</span></td>
      <td style="padding: 10px; font-family: monospace;">${l.sales || '-'}</td>
      <td style="padding: 10px; font-size: 16px;">${l.emoji || '-'}</td>
      <td style="padding: 10px; font-family: monospace; color: #64748b;">${l.group || '-'}</td>
      <td style="padding: 10px;"><span style="background: ${l.status === 'BERHASIL' ? '#d1fae5' : (l.status === 'DITOLAK_LATE' ? '#fef3c7' : '#fee2e2')}; color: ${l.status === 'BERHASIL' ? '#065f46' : (l.status === 'DITOLAK_LATE' ? '#92400e' : '#991b1b')}; padding: 3px 8px; border-radius: 6px; font-weight: bold;">${l.status}</span></td>
      <td style="padding: 10px; color: #334155;">${l.response || '-'}</td>
    </tr>
  `).join('');

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
          <button class="btn" style="padding: 4px 10px; font-size: 11px;" onclick="location.reload()">🔄 Refresh</button>
        </div>

        ${connectionState === 'CONNECTED' ? `
          <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 16px; text-align: center;">
            <div style="font-size: 32px; margin-bottom: 8px;">✅</div>
            <h3 style="color: #065f46; font-size: 15px; font-weight: bold;">WhatsApp Aktif & Siap!</h3>
            <p style="color: #047857; font-size: 12px; margin-top: 4px;">Akun: <strong>${connectedUser?.id?.split(':')[0]}</strong></p>
            <p style="color: #047857; font-size: 12px;">Nama: ${connectedUser?.name || 'Bot DCMS'}</p>
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

// Start Server
app.listen(PORT, () => {
  logger.info(`🌐 Web Dashboard aktif di port ${PORT}`);
  startBaileysSocket().catch(err => logger.error('Gagal inisialisasi Baileys:', err));
});
