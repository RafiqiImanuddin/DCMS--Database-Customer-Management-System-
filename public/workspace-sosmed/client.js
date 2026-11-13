/**
 * GadgetMart Sosmed KPI & Daily Achievement Engine
 * Features: turnover.xlsx integration, Report 1 (Daily Matrix), Report 2 (KPI & GP vs Timegone),
 * Tab 3: Cek Status AR & Verifikasi Sosmed, Tab 4: Arsip Bulanan, Tab 5: Admin Panel,
 * Full-height PNG Screenshot Download Engine, and Gemini AI Assistant.
 */

// 1. DEFAULT MASTER SALES & TARGETS CONFIGURATION (FALLBACK JIKA OFFLINE)
const DEFAULT_SALES_MASTER = [
  { code: 'BJM.SOS.005', name: 'SIFA', target: 1081261572, active: true, level: 'Karyawan' },
  { code: 'BJM.SOS.015', name: 'DANIEL', target: 1081261572, active: true, level: 'Karyawan' },
  { code: 'BJM.SOS.017', name: 'MARLINA', target: 1081261572, active: true, level: 'Karyawan' },
  { code: 'BJM.SOS.004', name: 'BINTANG', target: 867261886, active: true, level: 'Karyawan' },
  { code: 'BJM.SOS.007', name: 'FEBRI', target: 867261886, active: true, level: 'Karyawan' },
  { code: 'BJM.SOS.016', name: 'DEA', target: 867261886, active: true, level: 'Karyawan' },
  { code: 'BJM.SOS.018', name: 'MARISA', target: 867261886, active: true, level: 'Karyawan' },
  { code: 'BJM.SOS.019', name: 'ZOFIA', target: 867261886, active: true, level: 'Karyawan' },
  { code: 'BJM.SOS.022', name: 'QONA', target: 867261886, active: true, level: 'Karyawan' },
  // Extended Master Sales
  { code: 'BJM.SOS.021', name: 'RARA', target: 867261886, active: true, level: 'Karyawan' },
  { code: 'BJM.SOS.013', name: 'TIKA', target: 867261886, active: false, level: 'Karyawan' },
  { code: 'BJM.SOS.001', name: 'DIANA', target: 867261886, active: false, level: 'Karyawan' },
  { code: 'BJM.SOS.012', name: 'QOFA', target: 867261886, active: false, level: 'Karyawan' },
  { code: 'BJM.SOS.010', name: 'RIMA', target: 867261886, active: false, level: 'Karyawan' },
  { code: 'BJM.SOS.020', name: 'REVA', target: 867261886, active: false, level: 'Karyawan' },
  { code: 'BJM.SOS.000', name: 'NOL', target: 0, active: false, level: 'Karyawan' }
];

// Global State & Cache Declarations (Wajib diinisialisasi di paling atas sebelum fungsi berjalan)
let currentCalculationMode = 'DPP'; // 'DPP' or 'GROSS'
let rawTurnoverRows = [];     // Source for Report 1 & 2 Achievement (with DPP)
let rawCrosellingRows = [];   // Source for Tab 3: Cek AR & Pencarian WhatsApp Pelanggan (Google Sheets)
let rawKeepRows = [];
let filteredKeepRows = [];
let keepCurrentPage = 1;
const KEEP_PAGE_SIZE = 50;
let parsedDailyMatrix = {};
let activeMonth = 11;
let activeYear = 2026;
let daysInMonth = 30;
let lastActiveDay = 13;
let timegonePct = 42;
let currentActiveTab = 1;

// Executive Dashboard & Leaderboard State
let currentLeaderboardSort = 'omzet'; // 'omzet', 'gp', 'score'
let leaderboardPeriodMode = 'current'; // 'current' or 'ath'
let dashFilters = { month: '11', cabang: 'all', sales: 'all', mode: 'DPP' };
let cachedMonthlyTargets = {};
let lastProcessedPicStats = null;
let lastActivePICs = [];
let lastDocTransactions = {};
let lastTotalSosmedAchieve = 0;
let lastTotalSosmedGP = 0;
let lastTotalSosmedGadget = 0;
let lastTotalSosmedCross = 0;
let cachedSalesPhotos = {};
let calculatedSalesKPI = [];
let cachedMonthlyArchives = [];
let chartInstances = {};

// Centralized dynamic state (Satu Gerbang dengan /settings/karyawan)
let dynamicMasterSales = null;
let dynamicMatrixWeights = { omzet: 35.0, gp: 35.0, cross_selling: 30.0 };

function applyWorkspaceSettings(data) {
  if (!data) return;
  if (Array.isArray(data.employees) && data.employees.length > 0) {
    dynamicMasterSales = data.employees.map(emp => ({
      id: emp.id,
      code: emp.code || emp.nik,
      nik: emp.nik,
      name: String(emp.name || '').toUpperCase().trim(),
      target: Number(emp.target) || 867261886,
      active: emp.active !== false && emp.status !== 'inactive',
      role: emp.role,
      level: emp.level || 'Karyawan',
      cabang: emp.cabang,
      phone: emp.phone,
    })).sort((a, b) => {
      const levelA = (a.level || 'Karyawan').trim().toLowerCase();
      const levelB = (b.level || 'Karyawan').trim().toLowerCase();
      if (levelA !== levelB) {
        return levelA === 'karyawan' ? -1 : 1;
      }
      return 0;
    });
  }
  if (data.matrix_weights && typeof data.matrix_weights === 'object') {
    dynamicMatrixWeights = {
      omzet: parseFloat(data.matrix_weights.omzet) || 35.0,
      gp: parseFloat(data.matrix_weights.gp) || 35.0,
      cross_selling: parseFloat(data.matrix_weights.cross_selling) || 30.0,
    };
  }
  if (data.monthly_targets && typeof data.monthly_targets === 'object' && Object.keys(data.monthly_targets).length > 0) {
    cachedMonthlyTargets = data.monthly_targets;
    console.log('✅ Monthly targets synced from central database:', cachedMonthlyTargets);
  }
}

function initWorkspaceSettings() {
  if (window.WORKSPACE_INITIAL_SETTINGS && Array.isArray(window.WORKSPACE_INITIAL_SETTINGS.employees)) {
    applyWorkspaceSettings(window.WORKSPACE_INITIAL_SETTINGS);
  }
  fetchWorkspaceSettings();
}

async function fetchWorkspaceSettings() {
  try {
    const res = await fetch(`/api/workspace-settings?t=${Date.now()}`);
    if (res.ok) {
      const data = await res.json();
      applyWorkspaceSettings(data);
    }
  } catch(e) {
    console.warn('Gagal memuat setting master dari server:', e);
  }
}

initWorkspaceSettings();

function getMasterSales() {
  if (dynamicMasterSales && dynamicMasterSales.length > 0) {
    return dynamicMasterSales;
  }
  const saved = localStorage.getItem('gadgetmart_sosmed_master');
  if (saved) {
    try { return JSON.parse(saved); } catch(e){}
  }
  return DEFAULT_SALES_MASTER;
}

function saveMasterSales(list) {
  localStorage.setItem('gadgetmart_sosmed_master', JSON.stringify(list));
}

// Default Gemini API configuration
const DEFAULT_GEMINI_API_KEY = '';
const DEFAULT_GEMINI_MODEL = 'gemini-3.5-flash-lite';



// Utility Formatters
function formatRupiah(num) {
  if (window.IS_GUEST) return 'Rp ***.***';
  if (num === null || num === undefined || isNaN(num) || num === 0) return 'Rp -';
  const isNeg = num < 0;
  const absVal = Math.abs(num);
  const formatted = absVal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  return (isNeg ? '- Rp ' : 'Rp ') + formatted;
}

function formatPercent(pct) {
  if (window.IS_GUEST) return '***%';
  if (isNaN(pct)) return '0%';
  return `${Math.round(pct)}%`;
}

// Phone Normalizer & Formatter
function normalizePhone(p) {
  if (!p) return '';
  let str = String(p).replace(/\D/g, '');
  if (str.startsWith('62')) return str;
  if (str.startsWith('0')) return '62' + str.slice(1);
  if (str.startsWith('8')) return '62' + str;
  return str;
}

function formatPhoneDisplay(p) {
  if (!p) return '-';
  const norm = normalizePhone(p);
  if (window.IS_GUEST) {
    if (norm && norm.length >= 8) {
      return norm.slice(0, 4) + '****' + norm.slice(-3);
    }
    return '****';
  }
  if (!norm || norm.length < 9) return p || '-';
  if (norm.startsWith('62')) {
    const rest = norm.slice(2);
    return `+62 ${rest.slice(0, 3)}-${rest.slice(3, 7)}-${rest.slice(7)}`;
  }
  return norm;
}

// RESOLVE POSTING DATE: Handles Inverted US Locale Date (MM/DD/YYYY exported from SAP turnover.xlsx)
function resolvePostingDay(pd) {
  if (typeof pd === 'number') {
    const d = XLSX.SSF.parse_date_code(pd);
    // In turnover.xlsx US inverted locale: if day matches activeMonth (e.g. 10 for Oct, 11 for Nov) and d.m is in valid range
    if ((d.d === activeMonth || d.d === 10 || d.d === 11 || d.d === 12) && d.m >= 1 && d.m <= 31) {
      return d.m;
    }
    return d.d;
  }
  if (typeof pd === 'string') {
    const clean = pd.trim().split(' ')[0];
    const parts = clean.split(/[\/\-]/);
    if (parts.length >= 3) {
      if (parts[0].length === 4) {
        return parseInt(parts[2], 10) || 1;
      }
      return parseInt(parts[0], 10) || 1;
    } else if (parts.length >= 2) {
      return parseInt(parts[0], 10) || 1;
    }
  }
  return 1;
}

function formatDisplayDate(pd) {
  const day = resolvePostingDay(pd);
  return `${String(day).padStart(2, '0')}/${String(activeMonth).padStart(2, '0')}/${activeYear}`;
}

// Normal Date Formatter for Google Sheets / Croselling rows
function formatCrosellingDate(pd) {
  if (!pd) return '-';
  if (typeof pd === 'number') {
    const d = XLSX.SSF.parse_date_code(pd);
    if (d) {
      return `${String(d.d).padStart(2, '0')}/${String(d.m).padStart(2, '0')}/${d.y}`;
    }
  }
  if (typeof pd === 'string') {
    const parts = pd.trim().split(/[\/\-]/);
    if (parts.length >= 3) {
      let y = parts[2].trim().split(' ')[0];
      if (y.length === 2) y = '20' + y;
      return `${String(parts[0]).padStart(2, '0')}/${String(parts[1]).padStart(2, '0')}/${y}`;
    }
    return pd;
  }
  return '-';
}

// ==========================================================================
// INDEXEDDB ENGINE: BROWSER PERSISTENT STORAGE FOR EXCEL FILES
// ==========================================================================
const GM_DB_NAME = 'GadgetMartReportDB';
const GM_DB_VERSION = 1;
const GM_STORE_NAME = 'uploaded_files';

function openReportDB() {
  return new Promise((resolve) => {
    if (typeof window === 'undefined' || !window.indexedDB) {
      resolve(null);
      return;
    }
    const req = window.indexedDB.open(GM_DB_NAME, GM_DB_VERSION);
    req.onupgradeneeded = (e) => {
      const db = e.target.result;
      if (!db.objectStoreNames.contains(GM_STORE_NAME)) {
        db.createObjectStore(GM_STORE_NAME);
      }
    };
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => {
      console.warn('IndexedDB gagal dibuka:', req.error);
      resolve(null);
    };
  });
}

async function saveFileToIndexedDB(key, arrayBuffer) {
  try {
    const db = await openReportDB();
    if (!db) return false;
    return new Promise((resolve) => {
      const tx = db.transaction(GM_STORE_NAME, 'readwrite');
      const store = tx.objectStore(GM_STORE_NAME);
      store.put(arrayBuffer, key);
      tx.oncomplete = () => resolve(true);
      tx.onerror = () => resolve(false);
    });
  } catch (e) {
    console.warn('saveFileToIndexedDB error:', e);
    return false;
  }
}

async function getFileFromIndexedDB(key) {
  try {
    const db = await openReportDB();
    if (!db) return null;
    return new Promise((resolve) => {
      const tx = db.transaction(GM_STORE_NAME, 'readonly');
      const store = tx.objectStore(GM_STORE_NAME);
      const req = store.get(key);
      req.onsuccess = () => resolve(req.result || null);
      req.onerror = () => resolve(null);
    });
  } catch (e) {
    console.warn('getFileFromIndexedDB error:', e);
    return null;
  }
}

async function clearFileFromIndexedDB(key) {
  try {
    const db = await openReportDB();
    if (!db) return;
    const tx = db.transaction(GM_STORE_NAME, 'readwrite');
    tx.objectStore(GM_STORE_NAME).delete(key);
  } catch (e) {}
}

function getFormattedTimestamp(date = new Date()) {
  const pad = (n) => String(n).padStart(2, '0');
  const d = pad(date.getDate());
  const m = pad(date.getMonth() + 1);
  const y = date.getFullYear();
  const hr = pad(date.getHours());
  const min = pad(date.getMinutes());
  return `${d}/${m}/${y} ${hr}:${min} WITA`;
}

function updateTurnoverIndicator(text) {
  const el1 = document.getElementById('turnoverUpdateText');
  if (el1) el1.innerText = text;
  const el2 = document.getElementById('turnoverBannerText');
  if (el2) el2.innerText = text;
}

async function resetTurnoverToDefault() {
  if (confirm("Apakah Anda yakin ingin menghapus file turnover yang diunggah dan kembali ke data default sistem?")) {
    try {
      await fetch('/api/get-excel?type=turnover', { method: 'DELETE' });
    } catch(e) {}
    await clearFileFromIndexedDB('turnover_file');
    localStorage.removeItem('gadgetmart_turnover_timestamp');
    localStorage.removeItem('gadgetmart_turnover_is_custom');
    alert("File turnover kustom telah dihapus dari database & browser. Halaman akan dimuat ulang dengan data default sistem.");
    window.location.reload();
  }
}

// MATCH ALL SOSMED SALES EMPLOYEE CODES (BOTH "BJM.SOS.0" AND "BJM.SOS.K.0")
function isSosmedSalesCode(empStr, remStr = '', cabStr = '') {
  const emp = String(empStr || '').trim().toLowerCase();
  const rem = String(remStr || '').trim().toLowerCase();
  const cab = String(cabStr || '').trim().toLowerCase();

  // All sales employee codes starting with "bjm.sos" (both bjm.sos.0 and bjm.sos.k.0)
  if (emp.startsWith('bjm.sos')) return true;
  if (/bjm\.sos(?:\.k)?\./i.test(emp)) return true;
  if (emp.includes('bjm.sos')) return true;

  // Check remarks and sales cabang if emp is empty
  if (/bjm\.sos(?:\.k)?\./i.test(rem) || rem.includes('bjm.sos')) return true;
  if (/bjm\.sos(?:\.k)?\./i.test(cab) || cab.includes('bjm.sos')) return true;

  return false;
}

// Validasi ketat kode sales item faktur: harus benar-benar diinput dengan KODE SALES SOSMED (BJM.SOS)
// Catatan: Jika kode salesnya FL. (Floor/Toko) atau SC. (Sales Counter Toko), meskipun di Remarks ada tulisan 'sosmed',
// item tersebut TETAP BELUM TERINPUT SOSMED di SAP kasir!
function isItemInputSosmed(row) {
  if (!row) return false;
  const schl = String(row['SC/HL Code'] || '').trim();
  const slp = String(row['SlpName'] || row['Sales Employee Name'] || '').trim();

  if (/^bjm\.sos/i.test(schl) || /bjm\.sos(?:\.k)?\./i.test(schl)) return true;
  if (/^bjm\.sos/i.test(slp) || /bjm\.sos(?:\.k)?\./i.test(slp)) return true;

  return false;
}

// Barang yang bebas dari kewajiban input sosmed (misal TAS GADGETMART 25x25 / kantong belanja)
// Jika customer beli HP + Tas Gadgetmart, dan HP-nya sudah sosmed, faktur tetap dihitung 100% SUDAH SOSMED
function isItemExemptFromSosmed(row) {
  if (!row) return false;
  const desc = String(row['Dscription'] || row['Item/Service Description'] || '').trim().toUpperCase();
  const code = String(row['Item No.'] || row['Item Code'] || '').trim().toUpperCase();

  if (desc.includes('TAS GADGETMART') || 
      desc.includes('TAS GADGET MART') || 
      desc.includes('TAS 25X25') || 
      desc.includes('TAS 25 X 25') || 
      desc.includes('KANTONG GADGETMART') ||
      desc.includes('KANTONG 25X25') ||
      desc.includes('KANTONG 25 X 25')) {
    return true;
  }

  if ((code.includes('TAS') || desc.includes('TAS')) && (code.includes('25X25') || desc.includes('25X25') || desc.includes('25 X 25'))) {
    return true;
  }

  return false;
}

// Helper untuk memilih sheet transaksi turnover yang tepat (misal TARIKKAN pada file KPI SOSMED)
function resolveTurnoverSheet(workbook) {
  if (workbook.SheetNames.includes('TARIKKAN')) return workbook.Sheets['TARIKKAN'];
  if (workbook.SheetNames.includes('Sheet1')) return workbook.Sheets['Sheet1'];
  return workbook.Sheets[workbook.SheetNames[0]];
}

// Helper untuk membaca data turnover secara cepat & hemat memori (mencegah crash/OOM peramban pada file >50MB)
function readTurnoverJsonFromBuffer(arrayBuffer) {
  let targetSheetName = 'TARIKKAN';
  try {
    const metaWb = XLSX.read(arrayBuffer, { type: 'array', bookSheets: true });
    if (metaWb && Array.isArray(metaWb.SheetNames) && metaWb.SheetNames.length > 0) {
      if (metaWb.SheetNames.includes('TARIKKAN')) targetSheetName = 'TARIKKAN';
      else if (metaWb.SheetNames.includes('Sheet1')) targetSheetName = 'Sheet1';
      else targetSheetName = metaWb.SheetNames[0];
    }
  } catch (e) {
    console.warn('Meta read error:', e);
  }

  try {
    const wb = XLSX.read(arrayBuffer, {
      type: 'array',
      sheets: [targetSheetName],
      cellFormula: false,
      cellHTML: false,
      cellText: false
    });
    const sheet = wb.Sheets[targetSheetName];
    if (sheet) return XLSX.utils.sheet_to_json(sheet);
  } catch (err) {
    console.warn('Optimized read failed, fallback to standard read:', err);
  }

  const standardWb = XLSX.read(arrayBuffer, { type: 'array' });
  const sheet = resolveTurnoverSheet(standardWb);
  return XLSX.utils.sheet_to_json(sheet);
}

// Helper untuk mendeteksi bulan dan tahun aktif dari data turnover secara otomatis
function autoDetectTurnoverMonth(rows) {
  if (!rows || rows.length === 0) return null;
  const monthCounts = {};
  const yearCounts = {};
  const sample = rows.slice(0, 1000);
  for (const r of sample) {
    const raw = r['Posting Date'];
    if (!raw) continue;
    let m = null, y = null;
    if (typeof raw === 'number') {
      const d = XLSX.SSF.parse_date_code(raw);
      if (d) {
        if (d.m >= 1 && d.m <= 12) {
          m = d.m;
        }
        y = d.y || 2026;
      }
    } else if (typeof raw === 'string') {
      const p = raw.split(/[\/\-]/);
      if (p.length >= 3) {
        const p1 = parseInt(p[0], 10);
        const p2 = parseInt(p[1], 10);
        const p3 = parseInt(p[2], 10);
        y = p3 > 1000 ? p3 : (p1 > 1000 ? p1 : 2026);
        if (p1 > 12) m = p2;
        else if (p2 > 12) m = p1;
        else m = p2;
      }
    }
    if (m && m >= 1 && m <= 12) {
      monthCounts[m] = (monthCounts[m] || 0) + 1;
      if (y) yearCounts[y] = (yearCounts[y] || 0) + 1;
    }
  }

  let dominantMonth = null;
  let maxCount = 0;
  for (const [m, cnt] of Object.entries(monthCounts)) {
    if (cnt > maxCount) {
      maxCount = cnt;
      dominantMonth = parseInt(m, 10);
    }
  }

  let dominantYear = 2026;
  let maxYearCount = 0;
  for (const [y, cnt] of Object.entries(yearCounts)) {
    if (cnt > maxYearCount) {
      maxYearCount = cnt;
      dominantYear = parseInt(y, 10);
    }
  }

  return dominantMonth ? { month: dominantMonth, year: dominantYear } : null;
}

// 2. PARSER ENGINE FOR turnover.xlsx (Achievement DPP)
function parseTurnoverBuffer(arrayBuffer) {
  try {
    const jsonData = readTurnoverJsonFromBuffer(arrayBuffer);

    if (!jsonData || jsonData.length === 0) return;
    rawTurnoverRows = jsonData;
    console.log(`turnover.xlsx loaded: ${jsonData.length} rows`);

    const detected = autoDetectTurnoverMonth(jsonData);
    if (detected) {
      activeMonth = detected.month;
      activeYear = detected.year;
      daysInMonth = new Date(activeYear, activeMonth, 0).getDate();
      console.log(`📅 Periode turnover terdeteksi otomatis: Bulan ${activeMonth}, Tahun ${activeYear} (${daysInMonth} hari)`);
      const dashSel = document.getElementById('dashFilterMonth');
      if (dashSel) {
        dashSel.value = String(activeMonth);
      }
    }

    processAllReports();
    updateDatabaseStatus();
  } catch (err) {
    console.error("Gagal membaca turnover.xlsx:", err);
  }
}

// PARSER ENGINE FOR Google Sheets / Croselling (AR & WhatsApp Customer Search)
function parseCrosellingBuffer(arrayBuffer) {
  try {
    const workbook = XLSX.read(arrayBuffer, { type: 'array' });
    const sheetName = workbook.SheetNames[0];
    const sheet = workbook.Sheets[sheetName];
    const jsonData = XLSX.utils.sheet_to_json(sheet);

    if (!jsonData || jsonData.length === 0) return;
    rawCrosellingRows = jsonData;
    console.log(`Google Sheets croselling loaded: ${jsonData.length} rows`);
    const pill = document.getElementById('crosellingStatusPill');
    if (pill) {
      pill.innerHTML = `<span>📁 Data Source: Google Sheets (${jsonData.length.toLocaleString('id-ID')} Baris Data - Ada No WhatsApp)</span>`;
    }
    updateDatabaseStatus();
    if (typeof rawKeepRows !== 'undefined' && rawKeepRows.length > 0 && typeof runARMissingSosmedAudit === 'function') {
      runARMissingSosmedAudit();
    }
    // Auto-recalculate Report 2 Cross-Selling once Google Sheets / Croselling data is ready
    if (typeof rawTurnoverRows !== 'undefined' && rawTurnoverRows.length > 0 && typeof processAllReports === 'function') {
      processAllReports();
    }
  } catch (err) {
    console.error("Gagal membaca Google Sheets croselling:", err);
  }
}

function updateDatabaseStatus() {
  const title = document.getElementById('fileStatusTitle');
  const sub = document.getElementById('fileStatusSub');
  if (!title || !sub) return;

  const tCount = rawTurnoverRows.length;
  const cCount = rawCrosellingRows.length;

  if (tCount > 0 && cCount > 0) {
    title.innerText = `Dual Engine Siap: turnover.xlsx (${tCount.toLocaleString('id-ID')} Baris) & Google Sheets (${cCount.toLocaleString('id-ID')} Baris)`;
    sub.innerHTML = `✅ <strong>turnover.xlsx</strong> aktif untuk Report 1 & 2 (DPP) &bull; ✅ <strong>Google Sheets</strong> aktif untuk Cek AR & No. WhatsApp!`;
  } else if (tCount > 0) {
    title.innerText = `turnover.xlsx Dimuat (${tCount.toLocaleString('id-ID')} Baris Data)`;
    sub.innerHTML = `Report 1 & 2 Achievement terhitung menggunakan Nilai DPP. Menunggu data Google Sheets...`;
  } else if (cCount > 0) {
    title.innerText = `Google Sheets Dimuat (${cCount.toLocaleString('id-ID')} Baris Data)`;
    sub.innerHTML = `Database AR & WhatsApp siap digunakan untuk pencarian.`;
  }
}

// SMART FILE UPLOAD HANDLER (WITH INDEXEDDB PERSISTENCE & LIVE INDICATOR)
async function parseExcelFile(arrayBuffer, fileName = '') {
  try {
    const jsonData = readTurnoverJsonFromBuffer(arrayBuffer);

    if (!jsonData || jsonData.length === 0) {
      alert("File Excel kosong atau tidak memiliki data.");
      return;
    }

    const firstRow = jsonData[0] || {};
    const hasWA = 'No HP Customer' in firstRow || 'SC/HL Code' in firstRow || fileName.toLowerCase().includes('croselling') || fileName.toLowerCase().includes('sheet');
    const hasDPP = 'DPP' in firstRow || fileName.toLowerCase().includes('turnover');
    const updateTs = getFormattedTimestamp();

    if (hasWA && !hasDPP) {
      rawCrosellingRows = jsonData;
      await saveFileToIndexedDB('croselling_file', arrayBuffer);
      localStorage.setItem('gadgetmart_croselling_timestamp', updateTs);
      saveExcelToServer(arrayBuffer, 'croselling');
      alert(`Berhasil mengimpor Data Google Sheets (${jsonData.length.toLocaleString('id-ID')} baris data)!\nDatabase AR & WhatsApp telah diperbarui dan tetap tersimpan saat refresh.`);
      switchTab(3);
    } else if (hasDPP) {
      rawTurnoverRows = jsonData;
      await saveFileToIndexedDB('turnover_file', arrayBuffer);
      localStorage.setItem('gadgetmart_turnover_timestamp', updateTs);
      localStorage.setItem('gadgetmart_turnover_is_custom', 'true');
      updateTurnoverIndicator('Database Turnover Realtime (MySQL)');
      saveExcelToServer(arrayBuffer, 'turnover');
      processAllReports();
      alert(`Berhasil mengimpor turnover.xlsx (${jsonData.length.toLocaleString('id-ID')} baris data)!\nDatabase turnover realtime aktif.`);
      switchTab(2);
    } else {
      // Default: check if it has No HP
      if ('No HP Customer' in firstRow) {
        rawCrosellingRows = jsonData;
        await saveFileToIndexedDB('croselling_file', arrayBuffer);
        localStorage.setItem('gadgetmart_croselling_timestamp', updateTs);
        saveExcelToServer(arrayBuffer, 'croselling');
        switchTab(3);
      } else {
        rawTurnoverRows = jsonData;
        await saveFileToIndexedDB('turnover_file', arrayBuffer);
        localStorage.setItem('gadgetmart_turnover_timestamp', updateTs);
        localStorage.setItem('gadgetmart_turnover_is_custom', 'true');
        updateTurnoverIndicator('Database Turnover Realtime (MySQL)');
        saveExcelToServer(arrayBuffer, 'turnover');
        processAllReports();
        switchTab(2);
      }
    }
    updateDatabaseStatus();
  } catch (err) {
    console.error("Gagal membaca Excel:", err);
    alert("Gagal membaca file Excel. Pastikan format file valid.");
  }
}

// 3. CORE PROCESSING (REPORT 1 & REPORT 2)
function processAllReports() {
  if (!rawTurnoverRows || rawTurnoverRows.length === 0) return;

  const masterList = getMasterSales();
  // Urutkan: Karyawan terlebih dahulu, kemudian Internship
  const activePICs = masterList
    .filter(m => m.active)
    .sort((a, b) => {
      const levelA = (a.level || 'Karyawan').trim().toLowerCase();
      const levelB = (b.level || 'Karyawan').trim().toLowerCase();
      if (levelA !== levelB) {
        return levelA === 'karyawan' ? -1 : 1;
      }
      return 0;
    })
    .map(m => m.name);

  // Initialize Report 1 Matrix (Day 1..31)
  parsedDailyMatrix = {};
  for (let day = 1; day <= daysInMonth; day++) {
    const dayStr = String(day).padStart(2, '0');
    const monthStr = String(activeMonth).padStart(2, '0');
    const dateStr = `${dayStr}/${monthStr}/${activeYear}`;

    let weekLabel = 'WEEK 1';
    if (day >= 8 && day <= 14) weekLabel = 'WEEK 2';
    else if (day >= 15 && day <= 21) weekLabel = 'WEEK 3';
    else if (day >= 22 && day <= 28) weekLabel = 'WEEK 4';
    else if (day >= 29) weekLabel = 'WEEK 5';

    parsedDailyMatrix[day] = {
      day,
      dateStr,
      weekLabel,
      sales: {},
      totalDay: 0,
      avg: 0
    };

    activePICs.forEach(pic => {
      parsedDailyMatrix[day].sales[pic] = 0;
    });
  }

  // Aggregate stats per PIC for Report 2
  const picStats = {};
  activePICs.forEach(pic => {
    const m = masterList.find(x => x.name === pic);
    const target = m ? getSalesTargetForMonth(m.code, activeMonth, activeYear, m.target, m.name) : 867261886;
    picStats[pic] = {
      name: pic,
      target: target,
      pencapaian: 0,
      targetGP: Math.round(target * 0.05),
      acvGP: 0,
      gadgetCount: 0,
      crossCount: 0
    };
  });

  const docTransactions = {};
  let maxActiveDay = 1;
  let totalSosmedAchieve = 0;
  let totalSosmedGP = 0;

  // Pre-index rawCrosellingRows by Document Number for fast auto-lookup of Gadget & Aksesoris items
  const crosellingDocMap = new Map();
  if (Array.isArray(rawCrosellingRows) && rawCrosellingRows.length > 0) {
    rawCrosellingRows.forEach(crow => {
      const cdoc = String(crow['Document Number'] || crow['document_number'] || '').trim().replace(/\D/g, '');
      if (!cdoc) return;
      if (!crosellingDocMap.has(cdoc)) {
        crosellingDocMap.set(cdoc, []);
      }
      crosellingDocMap.get(cdoc).push(crow);
    });
  }

  for (const row of rawTurnoverRows) {
    const emp = String(row['Sales Employee Name'] || '');
    const rem = String(row['Remarks'] || '');
    const cab = String(row['SalesCabang'] || '');

    // Check if transaction belongs to Sosmed (matches all codes starting with bjm.sos, including bjm.sos.0 and bjm.sos.k.0)
    const isSos = isSosmedSalesCode(emp, rem, cab);
    if (!isSos) continue;

    // Resolve date with inverted US-locale fix
    const day = resolvePostingDay(row['Posting Date']);
    if (day >= 1 && day <= daysInMonth) {
      if (day > maxActiveDay) maxActiveDay = day;
    }

    // Amounts
    const dpp = Number(row['DPP']) || 0;
    const total = Number(row['Total']) || 0;
    // GP: Gunakan kolom 'FIX' jika tersedia (karena di Excel kolom FIX = GrossProfit + Cashback - Admin Kredit - Admin Marketplace),
    // jika kolom 'FIX' tidak ada (misal file turnover mentah standar), gunakan 'GrossProfit'.
    const fixVal = (row['FIX'] !== undefined && row['FIX'] !== '') ? Number(row['FIX']) : NaN;
    const rawGP = Number(row['GrossProfit']) || 0;
    const gp = !isNaN(fixVal) ? fixVal : rawGP;
    const amount = currentCalculationMode === 'DPP' ? dpp : total;

    // Accumulate total division sosmed achievement
    totalSosmedAchieve += amount;
    totalSosmedGP += gp;

    // Identify sales PIC
    let matchedPIC = null;
    const empUpper = emp.toUpperCase();
    const remUpper = rem.toUpperCase();
    const cabUpper = cab.toUpperCase();

    for (const m of masterList) {
      const codeUpper = String(m.code || '').toUpperCase();
      const nameUpper = String(m.name || '').toUpperCase();
      const nikUpper = String(m.nik || '').toUpperCase();

      const numMatch = codeUpper.match(/\d+/);
      const codeNum = numMatch ? parseInt(numMatch[0], 10) : null;
      const sosmedAlias = codeNum !== null ? `SOSMED ${codeNum}` : null;
      const sosmedPad = codeNum !== null ? `SOSMED ${String(codeNum).padStart(3, '0')}` : null;

      if (
        (codeUpper && (empUpper.includes(codeUpper) || remUpper.includes(codeUpper) || cabUpper.includes(codeUpper))) ||
        (nikUpper && (empUpper.includes(nikUpper) || remUpper.includes(nikUpper) || cabUpper.includes(nikUpper))) ||
        (sosmedAlias && (empUpper.includes(sosmedAlias) || remUpper.includes(sosmedAlias) || cabUpper.includes(sosmedAlias))) ||
        (sosmedPad && (empUpper.includes(sosmedPad) || remUpper.includes(sosmedPad) || cabUpper.includes(sosmedPad))) ||
        (nameUpper && nameUpper.length >= 3 && (empUpper.includes(nameUpper) || remUpper.includes(nameUpper) || cabUpper.includes(nameUpper)))
      ) {
        matchedPIC = m.name;
        break;
      }
    }

    // Accumulate individual PIC stats if matched and active
    if (matchedPIC && activePICs.includes(matchedPIC)) {
      if (parsedDailyMatrix[day]) {
        parsedDailyMatrix[day].sales[matchedPIC] += amount;
      }
      picStats[matchedPIC].pencapaian += amount;
      picStats[matchedPIC].acvGP += gp;
    }

    // Grouping for Cross-selling calculation (by Document Number)
    const docNo = row['Document Number'];
    if (docNo) {
      const cleanDoc = String(docNo).trim().replace(/\D/g, '');
      const docKey = cleanDoc || String(docNo).trim();

      if (!docTransactions[docKey]) {
        docTransactions[docKey] = {
          pic: matchedPIC || null,
          gadgetPics: new Set(),
          accPics: new Set(),
          allPics: new Set(),
          accTotal: 0,
          hasGadget: false,
          hasCross: false,
          evaluatedFromCrosselling: false
        };
      }
      if (matchedPIC) {
        if (!docTransactions[docKey].pic) {
          docTransactions[docKey].pic = matchedPIC;
        }
        docTransactions[docKey].allPics.add(matchedPIC);
      }

      // Mode 1: Jika turnover row sudah memiliki kolom 'Group Name' (misal file turnover mentah Excel)
      const itemDesc = String(row['Item/Service Description'] || '');
      const grpRaw = String(row['Group Name'] || '').trim().toUpperCase();
      let grp = grpRaw.slice(0, 3);
      if (itemDesc.startsWith('TM ')) grp = 'X';
      else if (itemDesc.startsWith('GMART CARE') || itemDesc.startsWith('JASA SERVICE T') || itemDesc.startsWith('JASA SERVICE FLEX T')) grp = 'ACC';

      if (grp === 'GAD') {
        docTransactions[docKey].hasGadget = true;
        if (matchedPIC) {
          docTransactions[docKey].gadgetPics.add(matchedPIC);
          docTransactions[docKey].pic = matchedPIC;
        }
      } else if (grp === 'ACC') {
        docTransactions[docKey].accTotal += total;
        if (matchedPIC) {
          docTransactions[docKey].accPics.add(matchedPIC);
        }
      } else if (!docTransactions[docKey].evaluatedFromCrosselling && crosellingDocMap.has(cleanDoc)) {
        // Mode 2: Auto-lookup ke data CROSSELLING jika kolom Group Name tidak ada pada data turnover (misal sumber SQL)
        docTransactions[docKey].evaluatedFromCrosselling = true;
        const cItems = crosellingDocMap.get(cleanDoc);
        cItems.forEach(it => {
          const itDesc = String(it['Dscription'] || it['Item/Service Description'] || it['ItemCode'] || it['dscription'] || '').trim().toUpperCase();
          const itGrp = String(it['ItmsGrpNam'] || it['Group Name'] || it['Group'] || '').trim().toUpperCase();
          const itPrice = Number(it['Harga Jual'] || it['Total'] || it['harga_jual'] || 0);

          if (itDesc.startsWith('TM ')) return; // Tebus Murah diabaikan

          const isGadget = isHandphoneProduct(it) || itGrp.startsWith('GAD') || itGrp.includes('GADGET') || itGrp.includes('HANDPHONE') || itGrp.includes('SMARTPHONE');
          const isAcc = itGrp.startsWith('ACC') || itGrp.includes('ACCES') || itDesc.startsWith('GMART CARE') || itDesc.startsWith('JASA SERVICE');

          if (isGadget) {
            docTransactions[docKey].hasGadget = true;
            if (matchedPIC) {
              docTransactions[docKey].gadgetPics.add(matchedPIC);
              docTransactions[docKey].pic = matchedPIC;
            }
          } else if (isAcc || (!isGadget && !isItemExemptFromSosmed(it))) {
            docTransactions[docKey].accTotal += itPrice;
            if (matchedPIC) {
              docTransactions[docKey].accPics.add(matchedPIC);
            }
          }
        });
      }
    }
  }

  // Calculate Cross-Selling Counts from Document groups
  // Rumus Standar Excel: JLH CROSS >50K adalah akumulasi Total Aksesoris dalam satu faktur/nota > 50.000
  let totalSosmedGadget = 0;
  let totalSosmedCross = 0;
  for (const doc in docTransactions) {
    const item = docTransactions[doc];
    if (item.hasGadget) {
      totalSosmedGadget++;
      const isCross = item.accTotal > 50000;
      item.hasCross = isCross;
      if (isCross) {
        totalSosmedCross++;
      }

      // Pastikan gadgetPics memiliki PIC jika doc memiliki pic/allPics
      const targetGadgetPics = item.gadgetPics.size > 0 
        ? item.gadgetPics 
        : (item.pic ? new Set([item.pic]) : (item.allPics || new Set()));

      // Hitung Gadget per PIC
      targetGadgetPics.forEach(pic => {
        if (picStats[pic]) picStats[pic].gadgetCount++;
      });

      // Hitung Cross (>50K) per PIC
      if (isCross) {
        const targetAccPics = item.accPics.size > 0 ? item.accPics : targetGadgetPics;
        targetAccPics.forEach(pic => {
          if (picStats[pic]) picStats[pic].crossCount++;
        });
      }
    }
  }

  // Calculate day total and AVG for Report 1 strictly based on active PIC sales day-by-day
  let grandActiveAchieve = 0;
  let grandActiveGP = 0;
  activePICs.forEach(pic => {
    grandActiveAchieve += (picStats[pic] ? picStats[pic].pencapaian : 0);
    grandActiveGP += (picStats[pic] ? picStats[pic].acvGP : 0);
  });

  for (let day = 1; day <= daysInMonth; day++) {
    let daySum = 0;
    activePICs.forEach(pic => {
      daySum += (parsedDailyMatrix[day].sales[pic] || 0);
    });
    parsedDailyMatrix[day].totalDay = daySum;
    parsedDailyMatrix[day].avg = activePICs.length > 0 ? Math.round(daySum / activePICs.length) : 0;
  }

  // Calculate Timegone based on max active day (e.g. 13 / 31 = 42%)
  lastActiveDay = maxActiveDay > 0 ? maxActiveDay : 13;
  timegonePct = Math.round((lastActiveDay / daysInMonth) * 100);

  // Save for global usage in dashboard & leaderboard
  lastProcessedPicStats = picStats;
  lastActivePICs = activePICs;
  lastDocTransactions = docTransactions;
  lastTotalSosmedAchieve = grandActiveAchieve > 0 ? grandActiveAchieve : totalSosmedAchieve;
  lastTotalSosmedGP = grandActiveGP !== 0 ? grandActiveGP : totalSosmedGP;
  lastTotalSosmedGadget = totalSosmedGadget;
  lastTotalSosmedCross = totalSosmedCross;

  // Render all views
  renderReport1Table(activePICs);
  renderReport2Tables(picStats, activePICs);
  updateExecutiveKPICards(picStats, activePICs);
  populateDashboardFilterDropdowns();
  applyDashboardFilters();
  renderSpeedometerGauges(picStats, activePICs);
  renderMonthToMonthCharts();
  renderSalesLeaderboard(currentLeaderboardSort);
  populateAdminPanel(masterList);
}

// 4. RENDER REPORT 1: DAILY MATRIX
function renderReport1Table(activePICs) {
  const theadRow = document.getElementById('matrixHeaderRow');
  const tbody = document.getElementById('matrixTableBody');
  const tfoot = document.getElementById('matrixTableFoot');

  const titleEl = document.getElementById('report1MainTitle');
  if (titleEl) {
    const monthNames = ['', 'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
    const mStr = monthNames[Number(activeMonth)] || String(activeMonth).toUpperCase();
    titleEl.innerText = `REPORT 1: DAILY ACHIEVEMENT SOSMED ${mStr} ${activeYear}`;
  }

  const masterList = getMasterSales();
  let headerHtml = `<th class="col-sticky col-date">TANGGAL</th>`;
  activePICs.forEach(pic => {
    const emp = masterList.find(x => x.name === pic);
    const isIntern = emp && (emp.level || '').toLowerCase() === 'internship';
    const badge = isIntern 
      ? ` <span class="matrix-intern-badge">INTERNSHIP</span>` 
      : '';
    headerHtml += `<th class="col-pic"><div class="matrix-pic-head"><span class="pic-name">${pic}</span>${badge}</div></th>`;
  });
  headerHtml += `<th class="col-achieve">ACHIEVE DAY</th><th class="col-week">WEEK</th><th class="col-avg">AVG</th>`;
  theadRow.innerHTML = headerHtml;

  tbody.innerHTML = '';
  const colTotals = {};
  activePICs.forEach(pic => colTotals[pic] = 0);
  let grandTotalAchieve = 0;

  for (let day = 1; day <= daysInMonth; day++) {
    const row = parsedDailyMatrix[day];
    const tr = document.createElement('tr');
    tr.id = `row-date-${day}`;
    const isToday = day === lastActiveDay;
    if (isToday) tr.classList.add('row-today');

    let trHtml = `<td class="col-sticky">
      <div class="date-cell-inner">
        <span class="date-text">${row.dateStr}</span>
        ${isToday ? '<span class="today-tag" title="Tanggal Berjalan">HARI INI</span>' : ''}
      </div>
    </td>`;

    let daySum = 0;
    activePICs.forEach(pic => {
      const val = row.sales[pic] || 0;
      daySum += val;
      colTotals[pic] += val;
      const isZero = val === 0;
      const valDisplay = isZero ? '<span class="val-dash">-</span>' : formatRupiah(val);
      const valClass = isZero ? 'cell-zero' : 'cell-value';
      trHtml += `<td class="${valClass}">${valDisplay}</td>`;
    });

    row.totalDay = daySum;
    row.avg = activePICs.length > 0 ? Math.round(daySum / activePICs.length) : 0;
    grandTotalAchieve += daySum;

    const isTotalZero = daySum === 0;
    const totalDisplay = isTotalZero ? '<span class="val-dash">-</span>' : formatRupiah(daySum);
    const achieveClass = isTotalZero ? 'cell-zero' : 'font-bold';
    trHtml += `<td class="col-achieve ${achieveClass}">${totalDisplay}</td>`;
    trHtml += `<td class="col-week"><span class="week-pill">${row.weekLabel}</span></td>`;
    const isAvgZero = row.avg === 0;
    const avgDisplay = isAvgZero ? '<span class="val-dash">-</span>' : formatRupiah(row.avg);
    trHtml += `<td class="col-avg ${isAvgZero ? 'cell-zero' : ''}">${avgDisplay}</td>`;

    tr.innerHTML = trHtml;
    tbody.appendChild(tr);
  }

  // Footer: TOTAL, SISA, TARGET, PROGRESS
  let totalHtml = `<tr class="row-total"><td class="col-sticky"><span class="footer-label">TOTAL</span></td>`;
  let sisaHtml = `<tr class="row-sisa"><td class="col-sticky"><span class="footer-label">SISA</span></td>`;
  let targetHtml = `<tr class="row-target"><td class="col-sticky"><span class="footer-label">TARGET</span></td>`;
  let progressHtml = `<tr class="row-progress"><td class="col-sticky"><span class="footer-label">PROGRESS</span></td>`;

  let sumTarget = 0;
  activePICs.forEach(pic => {
    const m = masterList.find(x => x.name === pic);
    const target = m ? getSalesTargetForMonth(m.code, activeMonth, activeYear, m.target, m.name) : 867261886;
    sumTarget += target;
    const total = colTotals[pic];
    const sisa = total - target;
    const pct = target > 0 ? (total / target) * 100 : 0;
    const sisaClass = sisa >= 0 ? 'text-positive' : 'text-negative';

    totalHtml += `<td>${formatRupiah(total)}</td>`;
    sisaHtml += `<td class="${sisaClass}">${formatRupiah(sisa)}</td>`;
    targetHtml += `<td>${formatRupiah(target)}</td>`;
    progressHtml += `<td>
      <div class="progress-cell-inner">
        <span class="progress-val">${formatPercent(pct)}</span>
        <div class="mini-progress-track">
          <div class="mini-progress-bar" style="width:${Math.min(100, Math.max(0, Math.round(pct)))}%;"></div>
        </div>
      </div>
    </td>`;
  });

  const grandSisa = grandTotalAchieve - sumTarget;
  const grandPct = sumTarget > 0 ? (grandTotalAchieve / sumTarget) * 100 : 0;
  const grandSisaClass = grandSisa >= 0 ? 'text-positive' : 'text-negative';

  totalHtml += `<td class="col-achieve">${formatRupiah(grandTotalAchieve)}</td><td class="col-week text-center">-</td><td class="col-avg text-center">-</td></tr>`;
  sisaHtml += `<td class="col-achieve ${grandSisaClass}">${formatRupiah(grandSisa)}</td><td class="col-week text-center">-</td><td class="col-avg text-center">-</td></tr>`;
  targetHtml += `<td class="col-achieve">${formatRupiah(sumTarget)}</td><td class="col-week text-center">-</td><td class="col-avg text-center">-</td></tr>`;
  progressHtml += `<td class="col-achieve">
    <div class="progress-cell-inner">
      <span class="progress-val">${formatPercent(grandPct)}</span>
      <div class="mini-progress-track">
        <div class="mini-progress-bar" style="width:${Math.min(100, Math.max(0, Math.round(grandPct)))}%;"></div>
      </div>
    </div>
  </td><td class="col-week text-center">-</td><td class="col-avg text-center">-</td></tr>`;

  tfoot.innerHTML = totalHtml + sisaHtml + targetHtml + progressHtml;
}

// 5. RENDER REPORT 2: KPI SUMMARY & TIMEGONE
function renderReport2Tables(picStats, activePICs) {
  const elTgHeader = document.getElementById('timegoneHeaderTag');
  if (elTgHeader) elTgHeader.innerText = `${timegonePct}%`;
  const elTgBadge = document.getElementById('timegoneDayBadge');
  if (elTgBadge) elTgBadge.innerText = `Hari ke-${lastActiveDay} / ${daysInMonth}`;
  const elTgVal = document.getElementById('kpiTimegoneVal');
  if (elTgVal) elTgVal.innerText = `${timegonePct}%`;

  const summaryBody = document.getElementById('kpiSummaryBody');
  const summaryFoot = document.getElementById('kpiSummaryFoot');
  const crossBody = document.getElementById('kpiCrossBody');
  const crossFoot = document.getElementById('kpiCrossFoot');

  if (!summaryBody || !crossBody) return;

  summaryBody.innerHTML = '';
  crossBody.innerHTML = '';

  let sumTarget = 0, sumAch = 0, sumTargetGP = 0, sumAcvGP = 0;
  let sumGadget = 0, sumCross = 0;
  const tg = (typeof timegonePct === 'number' && timegonePct > 0) ? timegonePct : 100;

  activePICs.forEach(pic => {
    const s = picStats[pic];
    sumTarget += s.target;
    sumAch += s.pencapaian;
    sumTargetGP += s.targetGP;
    sumAcvGP += s.acvGP;
    sumGadget += s.gadgetCount;
    sumCross += s.crossCount;

    // Ratio & Status based on Excel formula:
    // Ratio = (Achievement% / Timegone%) * 100
    // If Ratio < 80% => ☠️, If Ratio < 90% => 💔, Else => 🖤

    // Omzet % and status
    const pctOmzet = s.target > 0 ? (s.pencapaian / s.target) * 100 : 0;
    const ratioOmzet = (pctOmzet / tg) * 100;
    const statusOmzet = ratioOmzet >= 90 ? '🖤' : (ratioOmzet >= 80 ? '💔' : '☠️');

    // GP % and status
    const pctGP = s.targetGP > 0 ? (s.acvGP / s.targetGP) * 100 : 0;
    const ratioGP = (pctGP / tg) * 100;
    const statusGP = ratioGP >= 90 ? '🖤' : (ratioGP >= 80 ? '💔' : '☠️');

    // Cross selling % (target ratio 1/3)
    const ratioActual = s.gadgetCount > 0 ? (s.crossCount / s.gadgetCount) : 0;
    const pctCross = Math.min(100, Math.round(ratioActual * 3 * 100));

    // Final Report % based on central KPI weights from /settings/karyawan ("satu gerbang")
    const wOmzet = (dynamicMatrixWeights.omzet || 35.0) / 100;
    const wGP = (dynamicMatrixWeights.gp || 35.0) / 100;
    const wCross = (dynamicMatrixWeights.cross_selling || 30.0) / 100;

    const omzetNorm = Math.min(100, Math.max(0, pctOmzet));
    const gpNorm = Math.min(100, Math.max(0, pctGP));
    const pctAkhir = (omzetNorm * wOmzet) + (gpNorm * wGP) + (pctCross * wCross);

    const ratioAkhir = (pctAkhir / tg) * 100;
    const statusAkhir = ratioAkhir >= 90 ? '🖤' : (ratioAkhir >= 80 ? '💔' : '☠️');

    // Attach calculated metrics directly to PIC stats object
    s.pctOmzet = Math.round(pctOmzet * 10) / 10;
    s.ratioOmzet = ratioOmzet;
    s.statusOmzet = statusOmzet;
    s.pctGP = Math.round(pctGP * 10) / 10;
    s.ratioGP = ratioGP;
    s.statusGP = statusGP;
    s.pctCross = pctCross;
    s.pctAkhir = Math.round(pctAkhir * 10) / 10;
    s.ratioAkhir = ratioAkhir;
    s.statusAkhir = statusAkhir;

    // Table 1 Row
    const tr1 = document.createElement('tr');
    tr1.innerHTML = `
      <td class="text-left font-bold">${pic}</td>
      <td>${formatRupiah(s.target)}</td>
      <td>${formatRupiah(s.pencapaian)}</td>
      <td class="font-bold">${formatPercent(pctOmzet)}</td>
      <td class="text-center status-icon-lg">${statusOmzet}</td>
      <td>${formatRupiah(s.targetGP)}</td>
      <td class="${s.acvGP < 0 ? 'text-crimson' : ''}">${formatRupiah(s.acvGP)}</td>
      <td class="font-bold">${formatPercent(pctGP)}</td>
      <td class="text-center status-icon-lg">${statusGP}</td>
    `;
    summaryBody.appendChild(tr1);

    // Table 2 Row
    const tr2 = document.createElement('tr');
    tr2.innerHTML = `
      <td class="text-left font-bold">${pic}</td>
      <td>${s.gadgetCount}</td>
      <td>${s.crossCount}</td>
      <td class="font-bold">${formatPercent(pctCross)}</td>
      <td class="font-bold">${formatPercent(pctAkhir)}</td>
      <td class="text-center status-icon-lg">${statusAkhir}</td>
      <td></td>
    `;
    crossBody.appendChild(tr2);
  });

  // Table 1 MANAJER Total Row
  // Use lastTotalSosmedAchieve and lastTotalSosmedGP which includes ALL sales employee codes starting with bjm.sos (both bjm.sos.0 and bjm.sos.k.0)
  const managerAchieve = (typeof lastTotalSosmedAchieve === 'number' && lastTotalSosmedAchieve > 0) ? lastTotalSosmedAchieve : sumAch;
  const managerGP = (typeof lastTotalSosmedGP === 'number' && lastTotalSosmedGP !== 0) ? lastTotalSosmedGP : sumAcvGP;
  const managerGadget = (typeof lastTotalSosmedGadget === 'number' && lastTotalSosmedGadget > 0) ? lastTotalSosmedGadget : sumGadget;
  const managerCross = (typeof lastTotalSosmedCross === 'number' && lastTotalSosmedCross > 0) ? lastTotalSosmedCross : sumCross;

  const totalPctOmzet = sumTarget > 0 ? (managerAchieve / sumTarget) * 100 : 0;
  const totalPctGP = sumTargetGP > 0 ? (managerGP / sumTargetGP) * 100 : 0;
  const ratioMgrOmzet = (totalPctOmzet / tg) * 100;
  const statusMgrOmzet = ratioMgrOmzet >= 90 ? '🖤' : (ratioMgrOmzet >= 80 ? '💔' : '☠️');
  const ratioMgrGP = (totalPctGP / tg) * 100;
  const statusMgrGP = ratioMgrGP >= 90 ? '🖤' : (ratioMgrGP >= 80 ? '💔' : '☠️');

  summaryFoot.innerHTML = `
    <tr class="row-manajer">
      <td class="text-left font-bold">MANAJER</td>
      <td>${formatRupiah(sumTarget)}</td>
      <td>${formatRupiah(managerAchieve)}</td>
      <td class="font-bold">${formatPercent(totalPctOmzet)}</td>
      <td class="text-center status-icon-lg">${statusMgrOmzet}</td>
      <td>${formatRupiah(sumTargetGP)}</td>
      <td>${formatRupiah(managerGP)}</td>
      <td class="font-bold">${formatPercent(totalPctGP)}</td>
      <td class="text-center status-icon-lg">${statusMgrGP}</td>
    </tr>
  `;

  // Table 2 MANAJER Total Row
  const totalRatio = managerGadget > 0 ? (managerCross / managerGadget) : 0;
  const totalPctCross = Math.min(100, Math.round(totalRatio * 3 * 100));
  const wOmzet = (dynamicMatrixWeights.omzet || 35.0) / 100;
  const wGP = (dynamicMatrixWeights.gp || 35.0) / 100;
  const wCross = (dynamicMatrixWeights.cross_selling || 30.0) / 100;
  const totalPctAkhir = (Math.min(100, Math.max(0, totalPctOmzet)) * wOmzet) + (Math.min(100, Math.max(0, totalPctGP)) * wGP) + (totalPctCross * wCross);
  const ratioMgrAkhir = (totalPctAkhir / tg) * 100;
  const statusMgrAkhir = ratioMgrAkhir >= 90 ? '🖤' : (ratioMgrAkhir >= 80 ? '💔' : '☠️');

  crossFoot.innerHTML = `
    <tr>
      <td class="text-left font-bold">MANAJER</td>
      <td>${managerGadget}</td>
      <td>${managerCross}</td>
      <td class="font-bold">${formatPercent(totalPctCross)}</td>
      <td class="font-bold">${formatPercent(totalPctAkhir)}</td>
      <td class="text-center status-icon-lg">${statusMgrAkhir}</td>
      <td></td>
    </tr>
  `;

  const crossMetaTag = document.querySelector('#kpiCrossTable')?.closest('.table-card')?.querySelector('.table-meta-tag');
  if (crossMetaTag) {
    crossMetaTag.innerHTML = `Standar Rasio: 1 Cross per 3 Gadget &bull; <strong style="color:#0f172a;">Bobot: ${dynamicMatrixWeights.omzet}% Omzet + ${dynamicMatrixWeights.gp}% GP + ${dynamicMatrixWeights.cross_selling}% Cross</strong> (Satu Gerbang Setting)`;
  }
}

// 6. UPDATE EXECUTIVE KPI SUMMARY CARDS
function updateExecutiveKPICards(picStats, activePICs) {
  let grandTarget = 0, grandGPTarget = 0;
  let sumAch = 0, sumGP = 0, sumGadget = 0, sumCross = 0;

  activePICs.forEach(pic => {
    const s = picStats[pic];
    grandTarget += s.target;
    grandGPTarget += s.targetGP;
    sumAch += s.pencapaian;
    sumGP += s.acvGP;
    sumGadget += s.gadgetCount;
    sumCross += s.crossCount;
  });

  const explicitTeamTarget = getTeamTargetForMonth(activeMonth, activeYear);
  if (explicitTeamTarget && explicitTeamTarget > 0) {
    grandTarget = explicitTeamTarget;
    grandGPTarget = Math.round(grandTarget * 0.05);
  }

  const grandTotal = (typeof lastTotalSosmedAchieve === 'number' && lastTotalSosmedAchieve > 0) ? lastTotalSosmedAchieve : sumAch;
  const grandGP = (typeof lastTotalSosmedGP === 'number' && lastTotalSosmedGP !== 0) ? lastTotalSosmedGP : sumGP;
  const grandGadget = (typeof lastTotalSosmedGadget === 'number' && lastTotalSosmedGadget > 0) ? lastTotalSosmedGadget : sumGadget;
  const grandCross = (typeof lastTotalSosmedCross === 'number' && lastTotalSosmedCross > 0) ? lastTotalSosmedCross : sumCross;

  const pct = grandTarget > 0 ? (grandTotal / grandTarget) * 100 : 0;
  const sisa = grandTotal - grandTarget;
  const gpPct = grandGPTarget > 0 ? (grandGP / grandGPTarget) * 100 : 0;
  const crossRatio = grandGadget > 0 ? Math.min(100, Math.round((grandCross / grandGadget) * 3 * 100)) : 0;

  const elAch = document.getElementById('kpiTotalAchieve');
  if (elAch) elAch.innerText = formatRupiah(grandTotal);

  const elTgtText = document.getElementById('kpiTargetSubText');
  if (elTgtText) {
    elTgtText.innerText = `Target: ${formatRupiah(grandTarget)}`;
  } else {
    const elTgt = document.getElementById('kpiTargetSub');
    if (elTgt) elTgt.innerHTML = `<span>Target: ${formatRupiah(grandTarget)}</span> <button type="button" onclick="openMonthlyTargetModal()" class="kpi-target-edit-btn" title="Ubah target omzet bulan ini" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 2px 7px; font-size: 11px; font-weight: 700; cursor: pointer; margin-left: 8px;">✏️ Ubah Target</button>`;
  }
  const elPct = document.getElementById('kpiProgressPct');
  if (elPct) elPct.innerText = formatPercent(pct);
  const elBar = document.getElementById('kpiProgressBar');
  if (elBar) elBar.style.width = `${Math.min(pct, 100)}%`;
  const elSisa = document.getElementById('kpiSisaTarget');
  if (elSisa) elSisa.innerText = formatRupiah(sisa);

  const elGP = document.getElementById('kpiTotalGP');
  if (elGP) elGP.innerText = formatRupiah(grandGP);
  const elTgtGP = document.getElementById('kpiTargetGP');
  if (elTgtGP) elTgtGP.innerText = formatRupiah(grandGPTarget);
  const elGPPct = document.getElementById('kpiGPPct');
  if (elGPPct) elGPPct.innerText = formatPercent(gpPct);

  const elCross = document.getElementById('kpiCrossRatio');
  if (elCross) elCross.innerText = formatPercent(crossRatio);
  const elGadget = document.getElementById('kpiTotalGadget');
  if (elGadget) elGadget.innerText = grandGadget.toLocaleString('id-ID');

  const teamStatus = pct >= (timegonePct - 4) ? '🖤 On Track' : '💔 Warning';
  const elStatus = document.getElementById('kpiTeamStatusIcon');
  if (elStatus) elStatus.innerText = teamStatus;
}

// 7. TAB NAVIGATION & VIEW MODE FILTER (SUPPORT 7 UNIFIED TABS)
let currentReportViewMode = 'r1'; // 'r1', 'r2'

function toggleSettingsDropdown(e) {
  if (e) e.stopPropagation();
  const dropdown = document.getElementById('headerGearDropdown');
  if (dropdown) dropdown.classList.toggle('show');
}

function closeSettingsDropdown() {
  const dropdown = document.getElementById('headerGearDropdown');
  if (dropdown) dropdown.classList.remove('show');
}

function toggleWorkspaceMenu(e) {
  toggleSettingsDropdown(e);
}

function closeWorkspaceMenu() {
  closeSettingsDropdown();
}

// Close workspace dropdown menu when clicking anywhere outside
document.addEventListener('click', function(e) {
  const wrapper = document.querySelector('.header-gear-dropdown-wrap') || document.querySelector('.header-menu-wrapper');
  const dropdown = document.getElementById('headerGearDropdown') || document.getElementById('workspaceNavDropdown');
  if (dropdown && wrapper && !wrapper.contains(e.target)) {
    dropdown.classList.remove('show');
  }
});

// Workspace Login Modal Handlers
function openLoginModal() {
  const modal = document.getElementById('workspaceLoginModal');
  if (modal) {
    modal.style.display = 'flex';
    const pin = document.getElementById('loginPinInput');
    const fb = document.getElementById('loginFeedback');
    if (fb) fb.style.display = 'none';
    if (pin) { pin.value = ''; pin.focus(); }
  }
}

function closeLoginModal() {
  const modal = document.getElementById('workspaceLoginModal');
  if (modal) modal.style.display = 'none';
}

function executeWorkspaceLogin() {
  const roleSelect = document.getElementById('loginRoleSelect');
  const role = roleSelect ? roleSelect.value : 'admin';
  const pinInput = document.getElementById('loginPinInput');
  const pin = pinInput ? pinInput.value.trim() : '';
  const feedback = document.getElementById('loginFeedback');

  if (pin === '1234' || pin === 'admin' || pin === 'gadgetmart' || pin === '') {
    showSaveToast(`✅ Berhasil login sebagai ${role.toUpperCase()}!`);
    closeLoginModal();
    const loginBtn = document.getElementById('headerLoginBtn');
    if (loginBtn) {
      loginBtn.innerHTML = `👤 ${role === 'admin' ? 'Admin' : (role === 'sosmed' ? 'Sosmed' : 'Cabang')}`;
      loginBtn.style.color = '#ffc72c';
      loginBtn.style.fontWeight = '700';
    }
  } else {
    if (feedback) {
      feedback.style.display = 'block';
      feedback.style.background = '#ffe4e6';
      feedback.style.color = '#be123c';
      feedback.innerText = 'PIN atau password tidak cocok (Gunakan PIN default: 1234)';
    }
  }
}

function toggleSidebarMobile() {
  toggleWorkspaceMenu();
}

function switchTab(tabIndex) {
  currentActiveTab = tabIndex;
  for (let i = 1; i <= 10; i++) {
    const btn = document.getElementById(`tabBtn${i}`);
    const content = document.getElementById(`tabContent${i}`);
    if (btn) btn.classList.toggle('active', i === tabIndex);
    if (content) content.classList.toggle('active', i === tabIndex);
  }

  // Update dynamic active module name in subbar (Sesuai Mockup Gambar User)
  const moduleNames = {
    1: 'Dashboard',
    2: 'Report',
    3: 'Cek AR',
    4: 'Format Keep',
    5: 'Tracking AR',
    6: 'Klaim AR',
    7: 'Arsip Pencapaian Bulanan',
    8: 'Pengaturan Admin',
    9: 'Omnichannel Inbox FU (Preview)',
    10: 'Dashboard OLA Cabang'
  };
  const activeModEl = document.getElementById('activeModuleName');
  if (activeModEl) {
    activeModEl.textContent = moduleNames[tabIndex] || 'Dashboard';
  }

  // Sembunyikan subbar header di menu Format Keep (Tab 4) dan Dashboard OLA (Tab 10)
  const headerWrapper = document.getElementById('workspaceHeaderWrapper') || document.querySelector('.workspace-header-wrapper');
  if (headerWrapper) {
    headerWrapper.style.display = (tabIndex === 4 || tabIndex === 10) ? 'none' : '';
  }

  closeWorkspaceMenu();
  closeSettingsDropdown();

  // Toggle KPI Cards and Gemini Prompt visibility
  const kpiSection = document.getElementById('kpiGridSection');
  const aiSection = document.getElementById('aiPromptSection');
  if (kpiSection) kpiSection.style.display = 'none'; // Sesuai instruksi: kartu KPI atas disembunyikan
  if (tabIndex === 1) {
    if (aiSection) aiSection.style.display = 'flex';
    populateDashboardFilterDropdowns();
    applyDashboardFilters();
    if (lastProcessedPicStats && lastActivePICs.length > 0) {
      renderSpeedometerGauges(lastProcessedPicStats, lastActivePICs);
      renderMonthToMonthCharts();
      renderSalesLeaderboard('omzet');
    }
  } else if (tabIndex === 2) {
    if (aiSection) aiSection.style.display = 'flex';
    if (!currentReportViewMode || currentReportViewMode === 'all') {
      currentReportViewMode = 'r1';
    }
    setReportViewMode(currentReportViewMode);
  } else {
    if (aiSection) aiSection.style.display = 'none';
  }

  if (tabIndex === 4) {
    if (typeof sanitizeKeepAutofill === 'function') sanitizeKeepAutofill();
    if (typeof toggleKeepClearBtn === 'function') toggleKeepClearBtn();
    if (rawKeepRows.length === 0) {
      loadKeepData();
    } else {
      renderKeepPage();
    }
  }
  if (tabIndex === 5 && rawAuditARList.length === 0) runARMissingSosmedAudit();
  if (tabIndex === 6) renderARClaimsModule();
  if (tabIndex === 7) renderArchiveGrid();
  if (tabIndex === 8) {
    populateAdminPanel(getMasterSales());
    onAdminTargetMonthChange();
    renderAdminPhotoTable();
  }
  if (tabIndex === 9) renderOmniInboxModule();
  if (tabIndex === 10) {
    if (rawKeepRows.length === 0) {
      loadKeepData(() => {
        if (typeof renderTab10OLADashboard === 'function') renderTab10OLADashboard();
      });
    } else {
      if (typeof renderTab10OLADashboard === 'function') renderTab10OLADashboard();
    }
  }
}
window.switchTab = switchTab;

function setReportViewMode(mode) {
  if (mode !== 'r2' && mode !== 'all') mode = 'r1';
  currentReportViewMode = mode;
  const btnR1 = document.getElementById('filterBtnR1');
  const btnR2 = document.getElementById('filterBtnR2');
  const btnAll = document.getElementById('filterBtnAll');

  if (btnR1) btnR1.classList.toggle('active', mode === 'r1');
  if (btnR2) btnR2.classList.toggle('active', mode === 'r2');
  if (btnAll) btnAll.classList.toggle('active', mode === 'all');

  if (typeof updateReportSubmenuActive === 'function') {
    try { updateReportSubmenuActive(mode); } catch (e) {}
  }

  const r1Wrap = document.getElementById('report1ViewWrap');
  const r2Wrap = document.getElementById('report2ViewWrap');
  const kpiSection = document.getElementById('kpiGridSection');

  if (mode === 'r1') {
    if (r1Wrap) r1Wrap.style.display = 'block';
    if (r2Wrap) r2Wrap.style.display = 'none';
    if (kpiSection) kpiSection.style.display = 'block';
  } else if (mode === 'r2') {
    if (r1Wrap) r1Wrap.style.display = 'none';
    if (r2Wrap) r2Wrap.style.display = 'block';
    if (kpiSection) kpiSection.style.display = 'none';
  } else if (mode === 'all') {
    if (r1Wrap) r1Wrap.style.display = 'block';
    if (r2Wrap) r2Wrap.style.display = 'block';
    if (kpiSection) kpiSection.style.display = 'block';
  }
}

// AUTO-SAVE EXCEL TO SERVER FOR LAN PERSISTENCE
async function saveExcelToServer(arrayBuffer, type = 'turnover') {
  try {
    // Vercel serverless functions memiliki batas payload maksimal 4.5 MB.
    // Jika file berukuran besar (> 4MB seperti KPI SOSMED 53MB), simpan lokal di browser (IndexedDB)
    // dan lewati upload serverless untuk mencegah error 413 Payload Too Large pada cloud Vercel.
    const isVercel = window.location.hostname.endsWith('vercel.app') || window.location.hostname.includes('vercel');
    if (arrayBuffer.byteLength > 4 * 1024 * 1024 && isVercel) {
      console.log(`File ${(arrayBuffer.byteLength / 1024 / 1024).toFixed(1)} MB disimpan di IndexedDB browser (melebihi limit 4.5MB serverless Vercel).`);
      showSaveToast(`⚠️ File (${(arrayBuffer.byteLength / 1024 / 1024).toFixed(1)} MB) disimpan di browser PC ini (IndexedDB). Di Cloud Vercel, serverless membatasi upload maks 4.5 MB.`);
      return;
    }
    const res = await fetch(`/api/save-excel?type=${encodeURIComponent(type)}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/octet-stream' },
      body: arrayBuffer
    });
    if (res.ok) {
      const data = await res.json();
      console.log('💾 File saved to server disk successfully:', data);
      showSaveToast(`💾 Database ${type === 'croselling' ? 'Google Sheets' : 'turnover.xlsx'} tersimpan di Server! Tersedia untuk semua device di LAN & tetap tersimpan saat refresh.`);
      updateDatabaseBadge(true);
    }
  } catch (err) {
    console.warn("Gagal auto-save ke server:", err);
  }
}

function showSaveToast(message) {
  let toast = document.getElementById('saveToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'saveToast';
    toast.className = 'save-toast';
    document.body.appendChild(toast);
  }
  toast.innerHTML = `<span>${message}</span>`;
  toast.style.display = 'flex';
  setTimeout(() => {
    if (toast) toast.style.display = 'none';
  }, 4000);
}

function showToast(message) {
  showSaveToast(message);
}

// ==========================================================================
// FLOATING ANIMATED PERCENTAGE PROGRESS NOTIFICATION ENGINE
// ==========================================================================
function getProgressToastContainer() {
  let container = document.getElementById('progressToastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'progressToastContainer';
    container.className = 'progress-toast-container';
    document.body.appendChild(container);
  }
  return container;
}

function showProgressNotification({ id = `prog-${Date.now()}`, title = 'Memproses Data...', detail = '', initialPct = 0 }) {
  const container = getProgressToastContainer();
  let toast = document.getElementById(id);

  if (!toast) {
    toast = document.createElement('div');
    toast.id = id;
    toast.className = 'progress-toast';
    toast.innerHTML = `
      <div class="progress-toast-header">
        <div class="progress-toast-title-wrap">
          <span class="progress-toast-spinner" id="${id}-spinner"></span>
          <span class="progress-toast-title" id="${id}-title">${title}</span>
        </div>
        <span class="progress-toast-pct" id="${id}-pct">${Math.round(initialPct)}%</span>
      </div>
      <div class="progress-bar-track">
        <div class="progress-bar-fill" id="${id}-fill" style="width: ${initialPct}%;"></div>
      </div>
      <div class="progress-toast-detail" id="${id}-detail">${detail}</div>
    `;
    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
  }

  const titleEl = document.getElementById(`${id}-title`);
  const pctEl = document.getElementById(`${id}-pct`);
  const fillEl = document.getElementById(`${id}-fill`);
  const detailEl = document.getElementById(`${id}-detail`);

  let currentPercent = initialPct;

  const controller = {
    id,
    update({ pct, title: newTitle, detail: newDetail }) {
      if (typeof pct === 'number') {
        currentPercent = Math.max(0, Math.min(100, pct));
        if (pctEl) pctEl.innerText = `${Math.round(currentPercent)}%`;
        if (fillEl) fillEl.style.width = `${currentPercent}%`;
      }
      if (newTitle && titleEl) titleEl.innerText = newTitle;
      if (newDetail !== undefined && detailEl) detailEl.innerText = newDetail;
    },
    complete({ title: compTitle = '✅ Selesai (100%)', detail: compDetail = 'Data berhasil diproses' } = {}) {
      if (pctEl) pctEl.innerText = '100%';
      if (fillEl) fillEl.style.width = '100%';
      if (titleEl) titleEl.innerHTML = compTitle;
      if (detailEl) detailEl.innerText = compDetail;
      toast.classList.remove('error');
      toast.classList.add('success');

      setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 400);
      }, 2500);
    },
    fail({ title: errTitle = '❌ Gagal', detail: errDetail = 'Terjadi kesalahan saat memproses data' } = {}) {
      if (pctEl) pctEl.innerText = 'Gagal';
      if (fillEl) {
        fillEl.style.width = '100%';
        fillEl.style.background = '#ef4444';
      }
      if (titleEl) titleEl.innerHTML = errTitle;
      if (detailEl) detailEl.innerText = errDetail;
      toast.classList.remove('success');
      toast.classList.add('error');

      setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 400);
      }, 4000);
    },
    dismiss() {
      toast.classList.remove('show');
      toast.classList.add('hide');
      setTimeout(() => toast.remove(), 400);
    }
  };

  return controller;
}
window.showProgressNotification = showProgressNotification;

function startSmoothProgress({ 
  id = `smooth-${Date.now()}`, 
  title = 'Menyimpan ke Google Sheets...', 
  detail = 'Menghubungkan ke server...',
  timeoutMs = 45000,
  onTimeout
} = {}) {
  const ctrl = showProgressNotification({ id, title, detail, initialPct: 10 });
  let current = 10;
  let isDone = false;

  const interval = setInterval(() => {
    if (isDone) {
      clearInterval(interval);
      return;
    }
    // Naik dinamis di awal, melambat di atas 85% menunggu respon jaringan
    if (current < 45) current += Math.floor(Math.random() * 8) + 6;
    else if (current < 75) current += Math.floor(Math.random() * 5) + 3;
    else if (current < 92) current += Math.floor(Math.random() * 3) + 1;
    
    current = Math.min(92, current);
    ctrl.update({ pct: current });
  }, 130);

  // Safety timer agar notifikasi tidak tertahan selamanya di 92% jika jaringan bermasalah
  const safetyTimer = setTimeout(() => {
    if (!isDone) {
      isDone = true;
      clearInterval(interval);
      ctrl.fail({
        title: '⚠️ Batas Waktu Terlampaui (Timeout)',
        detail: `Koneksi jaringan melebihi batas (${Math.round(timeoutMs / 1000)} detik). Silakan coba lagi atau pastikan server aktif.`
      });
      if (typeof onTimeout === 'function') {
        try { onTimeout(); } catch (_) {}
      }
    }
  }, timeoutMs);

  return {
    id: ctrl.id,
    update: ctrl.update,
    complete(opts) {
      if (isDone) return;
      isDone = true;
      clearInterval(interval);
      clearTimeout(safetyTimer);
      ctrl.update({ pct: 100 });
      ctrl.complete(opts);
    },
    fail(opts) {
      if (isDone) return;
      isDone = true;
      clearInterval(interval);
      clearTimeout(safetyTimer);
      ctrl.fail(opts);
    },
    dismiss() {
      if (isDone) return;
      isDone = true;
      clearInterval(interval);
      clearTimeout(safetyTimer);
      ctrl.dismiss();
    }
  };
}
window.startSmoothProgress = startSmoothProgress;

function updateDatabaseBadge(isOk) {
  const text = document.getElementById('dbSyncStatusText');
  if (text) {
    text.innerText = isOk ? 'Database Server: Tersimpan & Tersinkron LAN' : 'Database Server: Menghubungkan...';
  }
}

// 8. TAB 2: CEK AR & PENCARIAN WHATSAPP CUSTOMER (GOOGLE SHEETS INTEGRATION)
function executeARSearch() {
  const input = document.getElementById('arInputSearch');
  const query = input.value.trim();
  if (!query) {
    alert("Masukkan nomor Dokumen AR atau Nomor WhatsApp Customer!");
    return;
  }
  performSearch(query);
}

function quickCheckAR(query) {
  switchTab(2);
  document.getElementById('arInputSearch').value = query;
  performSearch(query);
}

function performSearch(query) {
  if ((!rawCrosellingRows || rawCrosellingRows.length === 0) && (!rawTurnoverRows || rawTurnoverRows.length === 0)) {
    alert("Database belum dimuat. Silakan tunggu sebentar atau muat ulang data default.");
    return;
  }

  const cleanQ = String(query).trim();
  const digitsOnly = cleanQ.replace(/\D/g, '');
  const normPhone = normalizePhone(cleanQ);

  // Search in Google Sheets rows first (has WhatsApp numbers & per-item sales PIC)
  let matchedByPhone = [];
  let matchedByDoc = [];

  if (rawCrosellingRows && rawCrosellingRows.length > 0) {
    if (digitsOnly.length >= 8) {
      matchedByPhone = rawCrosellingRows.filter(r => {
        const hp = normalizePhone(r['No HP Customer']);
        return hp === normPhone || hp.endsWith(digitsOnly.slice(-9));
      });
    }
    matchedByDoc = rawCrosellingRows.filter(r => {
      const doc = String(r['Document Number'] || '').replace(/\D/g, '');
      return doc === digitsOnly;
    });
  }

  // Fallback to turnover.xlsx if not found in Google Sheets
  if (matchedByDoc.length === 0 && rawTurnoverRows && rawTurnoverRows.length > 0) {
    const fallbackTurnover = rawTurnoverRows.filter(r => {
      const doc = String(r['Document Number'] || '').replace(/\D/g, '');
      return doc === digitsOnly;
    });
    if (fallbackTurnover.length > 0) {
      renderARDocumentView(cleanQ, fallbackTurnover, 'TURNOVER');
      return;
    }
  }

  // Decision: WhatsApp Customer Search vs AR Document Search
  if (matchedByPhone.length > 0 && (matchedByDoc.length === 0 || cleanQ.startsWith('08') || cleanQ.startsWith('628') || cleanQ.startsWith('+62') || (digitsOnly.length >= 10 && !cleanQ.startsWith('26')))) {
    renderWhatsAppCustomerView(cleanQ, matchedByPhone);
  } else if (matchedByDoc.length > 0) {
    renderARDocumentView(cleanQ, matchedByDoc, 'CROSSELLING');
  } else if (matchedByPhone.length > 0) {
    renderWhatsAppCustomerView(cleanQ, matchedByPhone);
  } else {
    // Coba pencarian langsung ke Database Pusat jika data memori belum sinkron
    searchArFromDatabase(cleanQ, query);
  }
}

// PENCARIAN DATABASE PUSAT (POSTGRESQL DCMS FALLBACK)
async function searchArFromDatabase(cleanQ, query) {
  showSaveToast(`🔍 Mencari data "${query}" di Database Pusat...`);
  try {
    const res = await fetch(`/api/search-ar?query=${encodeURIComponent(cleanQ)}`);
    if (res.ok) {
      const data = await res.json();
      if (data.rows && data.rows.length > 0) {
        const mappedRows = data.rows.map(r => ({
          'Document Number': r.document_number,
          'No HP Customer': r.no_hp_customer,
          'Name': r.name || r.customer_vendor_name,
          'Customer/Vendor Name': r.customer_vendor_name || r.name,
          'Customer/Vendor Code': r.customer_vendor_code,
          'Dscription': r.dscription,
          'ItemCode': r.item_code,
          'Brand': r.brand,
          'Quantity': r.quantity,
          'Harga Jual': r.harga_jual,
          'SC/HL Code': r.sc_hl_code,
          'SlpName': r.slp_name,
          'Remarks': r.remarks,
          'Posting Date': r.posting_date,
          'Cabang': r.cabang
        }));

        const digitsOnly = cleanQ.replace(/\D/g, '');
        if (cleanQ.startsWith('08') || cleanQ.startsWith('628') || cleanQ.startsWith('+62') || (digitsOnly.length >= 10 && !cleanQ.startsWith('26'))) {
          renderWhatsAppCustomerView(cleanQ, mappedRows);
        } else {
          renderARDocumentView(cleanQ, mappedRows, 'DATABASE');
        }
        showSaveToast(`✅ Data ditemukan dari Database Pusat (${data.rows.length} item)!`);
        return;
      }
    }
  } catch (err) {
    console.warn('Gagal search AR dari database:', err);
  }

  alert(`Data untuk AR atau Nomor WhatsApp "${query}" tidak ditemukan dalam database.`);
  const container = document.getElementById('arResultContainer');
  if (container) container.style.display = 'none';
}

// 8A. VIEW PENCARIAN NOMOR WHATSAPP: MENAMPILKAN SEMUA PRODUK YANG DIBELI CUSTOMER
function renderWhatsAppCustomerView(query, rows) {
  const resContainer = document.getElementById('arResultContainer');
  resContainer.style.display = 'block';

  // Toggle UI visibility
  document.getElementById('customerProfileBanner').style.display = 'block';
  document.getElementById('arStatusBanner').style.display = 'none';
  document.getElementById('arDetailsGrid').style.display = 'none';

  const first = rows[0];
  const custName = first['Name'] || first['Customer/Vendor Name'] || 'Customer';
  const custCode = first['Customer/Vendor Code'] || '-';
  const rawWA = first['No HP Customer'] || query;
  const normWA = normalizePhone(rawWA);
  const displayWA = formatPhoneDisplay(rawWA);

  const uniqueDocs = Array.from(new Set(rows.map(r => r['Document Number'])));
  const uniqueCabs = Array.from(new Set(rows.map(r => r['Cabang']).filter(Boolean)));
  const totalQty = rows.reduce((acc, r) => acc + (Number(r['Quantity']) || 1), 0);
  const totalSpent = rows.reduce((acc, r) => acc + (Number(r['Harga Jual']) || 0), 0);

  // Populate Customer Profile Banner
  document.getElementById('custAvatar').innerText = '📱';
  document.getElementById('custProfileName').innerText = `${custName} (${first['Customer/Vendor Name'] || ''})`;
  document.getElementById('custProfileCode').innerText = `Kode Customer: ${custCode} &bull; Sumber: Google Sheets`;
  document.getElementById('custProfileWA').innerText = displayWA;
  document.getElementById('custProfileTotalSpent').innerText = formatRupiah(totalSpent);
  document.getElementById('custProfileInvoicesCount').innerText = `${uniqueDocs.length} Faktur AR`;
  document.getElementById('custProfileItemsCount').innerText = `${totalQty} Item Produk`;
  document.getElementById('custProfileCabang').innerText = uniqueCabs.join(', ') || '-';

  document.getElementById('custWhatsAppAction').innerHTML = window.IS_GUEST ? `
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-500 font-bold text-xs">
      🔒 Nomor WA Disensor
    </span>
  ` : `
    <a href="https://wa.me/${normWA}" target="_blank" class="btn-whatsapp">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
      </svg>
      Chat Customer di WhatsApp
    </a>
  `;

  // Global state for Tab 3 Cek AR search results & copy
  currentARSearchResults = rows;
  currentARSearchDoc = '';

  // Update Table Headers
  document.getElementById('itemsTableTitle').innerText = `🛒 Riwayat Pembelian Produk oleh Customer (${displayWA} - ${custName}):`;
  document.getElementById('arItemsCountBadge').innerText = `${rows.length} Baris Produk (${totalQty} Qty)`;

  document.getElementById('arItemsTableHead').innerHTML = `
    <th class="col-sticky" style="width: 35px; text-align: center;">NO</th>
    <th>NO. AR / FAKTUR</th>
    <th>TANGGAL & WAKTU</th>
    <th>CABANG</th>
    <th class="text-left" style="min-width: 200px;">NAMA PRODUK / ITEM DESCRIPTION</th>
    <th style="width: 45px;">QTY</th>
    <th>HARGA SATUAN</th>
    <th>TOTAL BELANJA</th>
    <th class="text-left" style="min-width: 170px;">SALES EMPLOYEE (PER ITEM)</th>
    <th class="text-center" style="width: 110px;">STATUS SOSMED</th>
    <th class="text-center" style="width: 90px;">POTENSI DENDA</th>
    <th class="text-center" style="width: 100px;">SALIN EXCEL</th>
  `;

  // Render Rows
  const tbody = document.getElementById('arItemsBody');
  tbody.innerHTML = '';

  rows.forEach((r, idx) => {
    const qty = Number(r['Quantity']) || 1;
    const subtotal = Number(r['Harga Jual']) || 0;
    const price = qty > 0 ? (subtotal / qty) : subtotal;
    const schl = String(r['SC/HL Code'] || '');
    const slp = String(r['SlpName'] || '');
    const rem = String(r['Remarks'] || '');
    const dateFormatted = formatCrosellingDate(r['Posting Date']);
    const waktu = r['Waktu'] || '';

    // Check if sales code indicates sosmed
    const isSosmed = isItemInputSosmed(r);
    const empDisplay = schl ? schl : (slp ? slp : '-');
    const empBadge = isSosmed 
      ? `<span class="kpi-badge badge-crimson font-bold">✨ ${empDisplay}</span>`
      : `<span class="kpi-badge badge-ink">${empDisplay}</span>`;

    const statusBadge = isSosmed
      ? `<span class="status-badge badge-accent">✨ Terinput Sosmed</span>`
      : `<span class="status-badge badge-ink">🏢 Offline Toko / Kasir</span>`;

    const dendaStatus = getItemPotensiDenda(r);
    const dendaBadge = dendaStatus === 'DENDA'
      ? `<span class="kpi-badge badge-crimson font-bold" style="font-size:0.68rem; padding:2px 6px;">🚨 DENDA</span>`
      : `<span class="kpi-badge badge-ink" style="font-size:0.68rem; padding:2px 6px;">PASS</span>`;

    const copyBtn = `
      <button class="btn-copy-item-sm" onclick="copyARSearchItemToClipboard(${idx}, this)" title="Salin item ini ke format 12 kolom Excel (tanpa header)">
        📋 Copy Rekap
      </button>
    `;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="col-sticky">${idx + 1}</td>
      <td class="font-bold text-crimson cursor-pointer" onclick="quickCheckAR('${r['Document Number']}')" title="Klik untuk cek detail AR ini">📄 ${r['Document Number']}</td>
      <td>${dateFormatted} ${waktu}</td>
      <td class="font-bold">${r['Cabang'] || '-'}</td>
      <td class="text-left font-bold">${r['Dscription'] || '-'}</td>
      <td>${qty}</td>
      <td>${formatRupiah(price)}</td>
      <td class="font-bold">${formatRupiah(subtotal)}</td>
      <td class="text-left">${empBadge}</td>
      <td class="text-center">${statusBadge}</td>
      <td class="text-center">${dendaBadge}</td>
      <td class="text-center">${copyBtn}</td>
    `;
    tbody.appendChild(tr);
  });

  document.getElementById('arItemsFoot').innerHTML = `
    <tr class="row-total">
      <td colspan="5" class="text-left font-bold">TOTAL PEMBELIAN CUSTOMER (KUMULATIF):</td>
      <td><strong>${totalQty}</strong></td>
      <td>-</td>
      <td class="font-bold">${formatRupiah(totalSpent)}</td>
      <td colspan="4" class="text-left font-bold text-accent">${uniqueDocs.length} Faktur Dokumen</td>
    </tr>
  `;

  resContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// 8B. VIEW PENCARIAN DOKUMEN AR: RINCIAN FAKTUR & VERIFIKASI SALES PER ITEM
function renderARDocumentView(docNumber, rows, source = 'CROSSELLING') {
  const resContainer = document.getElementById('arResultContainer');
  resContainer.style.display = 'block';

  document.getElementById('customerProfileBanner').style.display = 'none';
  document.getElementById('arStatusBanner').style.display = 'flex';
  document.getElementById('arDetailsGrid').style.display = 'grid';

  const first = rows[0];
  const masterList = getMasterSales();

  // Validasi ketat kode sales:
  // Item hanya diakui terinput Sosmed jika kode salesnya benar-benar BJM.SOS,
  // BUKAN sekadar ada tulisan 'sosmed' pada Remarks/Keterangan kasir!
  const sosmedItems = rows.filter(r => isItemInputSosmed(r));
  const missingItems = rows.filter(r => !isItemInputSosmed(r));

  let detectedPIC = null;
  let detectedCode = null;

  // Cari identitas PIC dari item yang berkode BJM.SOS
  for (const r of sosmedItems) {
    const schl = String(r['SC/HL Code'] || '');
    const slp = String(r['SlpName'] || r['Sales Employee Name'] || '');

    for (const m of masterList) {
      if (schl.includes(m.code) || slp.includes(m.code)) {
        detectedPIC = m.name;
        detectedCode = m.code;
        break;
      }
    }
    if (detectedPIC) break;

    const matchCode = (schl + ' ' + slp).match(/bjm\.sos(?:\.k)?\.\w+/i);
    if (matchCode) {
      detectedCode = matchCode[0].toUpperCase();
      detectedPIC = 'TIM SOSMED';
      break;
    }
  }

  // Update Status Banner
  const banner = document.getElementById('arStatusBanner');
  const icon = document.getElementById('arStatusIcon');
  const title = document.getElementById('arStatusTitle');
  const subtitle = document.getElementById('arStatusSubtitle');

  const remarksText = String(first['Remarks'] || '').trim();
  const remarksHasSosmed = /sosmed|sos/i.test(remarksText);

  if (missingItems.length === 0 && sosmedItems.length > 0) {
    // SEMUA ITEM SUDAH BERKODE SOSMED
    banner.className = 'ar-status-banner banner-success';
    icon.innerHTML = '✅';
    title.innerText = 'SUDAH DIINPUT KODE SOSMED';
    subtitle.innerText = `Semua item (${rows.length} item) dalam dokumen ini telah terverifikasi dengan Sales Sosmed: ${detectedPIC || 'SOSMED'} (${detectedCode || 'BJM.SOS'})`;
  } else if (sosmedItems.length > 0 && missingItems.length > 0) {
    // SEBAGIAN ITEM SUDAH, SEBAGIAN BELUM
    banner.className = 'ar-status-banner banner-warning';
    icon.innerHTML = '⚠️';
    title.innerText = 'SEBAGIAN ITEM BELUM DIINPUT SOSMED';
    subtitle.innerText = `Peringatan: ${sosmedItems.length} item berkode Sosmed, tetapi ${missingItems.length} item lainnya masih berkode Toko Offline! Segera klaim item yang belum terinput.`;
  } else {
    // SEMUA ITEM BELUM DIINPUT SOSMED
    banner.className = 'ar-status-banner banner-danger';
    icon.innerHTML = '❌';
    title.innerText = 'BELUM DIINPUT KODE SOSMED';
    if (remarksHasSosmed) {
      subtitle.innerText = `Peringatan: Kasir menulis "${remarksText}" di Remarks, tetapi KODE SALES di SAP masih offline (${first['SC/HL Code'] || first['SlpName'] || 'Toko'}). Belum terhitung omzet Sosmed, segera klaim ke cabang!`;
    } else {
      subtitle.innerText = `Peringatan: Dokumen ini belum memiliki kode BJM.SOS. Masih tercatat sebagai transaksi offline/toko fisik (${first['SC/HL Code'] || first['SlpName'] || 'Toko'}).`;
    }
  }

  // Calculate totals and metadata
  let totalDocVal = 0;
  let totalDocQty = 0;
  const rawWA = first['No HP Customer'] || '';
  const normWA = normalizePhone(rawWA);
  const displayWA = formatPhoneDisplay(rawWA);

  document.getElementById('arMetaDoc').innerText = first['Document Number'] || docNumber;
  document.getElementById('arMetaDate').innerText = source === 'CROSSELLING' ? `${formatCrosellingDate(first['Posting Date'])} ${first['Waktu'] || ''}` : formatDisplayDate(first['Posting Date']);
  document.getElementById('arMetaCabang').innerText = first['Cabang'] || '-';
  document.getElementById('arMetaCust').innerText = `${first['Customer/Vendor Name'] || '-'} ${first['Name'] ? '(' + first['Name'] + ')' : ''}`;
  
  if (normWA && normWA.length >= 9) {
    document.getElementById('arMetaWA').innerHTML = `
      <span class="font-bold">${displayWA}</span>
      ${!window.IS_GUEST ? `
      <a href="https://wa.me/${normWA}" target="_blank" class="btn-whatsapp" style="margin-left: 8px; padding: 2px 8px; font-size: 0.72rem;">
        💬 Chat WA
      </a>
      ` : '<span class="text-xs text-slate-400 ml-2 font-mono">🔒 (Disensor)</span>'}
    `;
  } else {
    document.getElementById('arMetaWA').innerText = 'Tidak tersedia di dokumen ini';
  }

  document.getElementById('arMetaRemarks').innerText = first['Remarks'] || '- (Tidak ada catatan)';

  // Global state for Tab 3 Cek AR search results & copy
  currentARSearchResults = rows;
  currentARSearchDoc = docNumber;

  // Table Headers
  document.getElementById('itemsTableTitle').innerText = `Daftar Item / Produk dalam Dokumen AR ${first['Document Number'] || docNumber}:`;
  document.getElementById('arItemsTableHead').innerHTML = `
    <th class="col-sticky" style="width: 35px; text-align: center;">NO</th>
    <th class="text-left" style="min-width: 200px;">NAMA PRODUK / ITEM DESCRIPTION</th>
    <th class="text-center">KATEGORI / BRAND</th>
    <th style="width: 45px;">QTY</th>
    <th class="text-left" style="min-width: 170px;">SALES EMPLOYEE (PER ITEM)</th>
    <th class="text-left" style="min-width: 120px;">CABANG</th>
    <th>HARGA SATUAN</th>
    <th>TOTAL</th>
    <th class="text-center" style="width: 100px;">STATUS SOSMED</th>
    <th class="text-center" style="width: 90px;">POTENSI DENDA</th>
    <th class="text-center" style="width: 100px;">SALIN EXCEL</th>
  `;

  const tbody = document.getElementById('arItemsBody');
  tbody.innerHTML = '';

  rows.forEach((r, idx) => {
    const qty = Number(r['Quantity']) || 1;
    const subtotal = source === 'CROSSELLING' ? (Number(r['Harga Jual']) || 0) : (Number(r['Total']) || 0);
    const price = qty > 0 ? (subtotal / qty) : subtotal;
    totalDocVal += subtotal;
    totalDocQty += qty;

    const schl = r['SC/HL Code'] || '';
    const slp = r['SlpName'] || '';
    const emp = r['Sales Employee Name'] || '';
    const empDisplay = schl ? schl : (slp ? slp : (emp || '-'));

    const isSosmedItem = isItemInputSosmed(r);
    const empBadge = isSosmedItem
      ? `<span class="kpi-badge badge-crimson font-bold">✨ ${empDisplay}</span>`
      : `<span class="kpi-badge badge-ink">${empDisplay}</span>`;

    const dendaStatus = getItemPotensiDenda(r);
    const dendaBadge = dendaStatus === 'DENDA'
      ? `<span class="kpi-badge badge-crimson font-bold" style="font-size:0.68rem; padding:2px 6px;">🚨 DENDA</span>`
      : `<span class="kpi-badge badge-ink" style="font-size:0.68rem; padding:2px 6px;">PASS</span>`;

    const copyBtn = `
      <button class="btn-copy-item-sm" onclick="copyARSearchItemToClipboard(${idx}, this)" title="Salin item ini ke format 12 kolom Excel (tanpa header)">
        📋 Copy Rekap
      </button>
    `;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="col-sticky">${idx + 1}</td>
      <td class="text-left font-bold">${r['Dscription'] || r['Item/Service Description'] || '-'}</td>
      <td class="text-center"><span class="kpi-badge badge-ink">${r['Brand'] || r['ItmsGrpNam'] || r['Group Name'] || '-'}</span></td>
      <td>${qty}</td>
      <td class="text-left">${empBadge}</td>
      <td class="text-left font-bold">${r['Cabang'] || r['SalesCabang'] || '-'}</td>
      <td>${formatRupiah(price)}</td>
      <td class="font-bold">${formatRupiah(subtotal)}</td>
      <td class="text-center">${isSosmedItem ? '<span class="status-badge badge-accent">✨ Sosmed</span>' : '<span class="status-badge badge-ink">🏢 Offline</span>'}</td>
      <td class="text-center">${dendaBadge}</td>
      <td class="text-center">${copyBtn}</td>
    `;
    tbody.appendChild(tr);
  });

  document.getElementById('arMetaTotal').innerText = formatRupiah(totalDocVal);
  document.getElementById('arItemsCountBadge').innerText = `${rows.length} Item (${totalDocQty} Qty)`;

  document.getElementById('arItemsFoot').innerHTML = `
    <tr class="row-total">
      <td colspan="3" class="text-left font-bold">TOTAL DOKUMEN AR:</td>
      <td><strong>${totalDocQty}</strong></td>
      <td colspan="3"></td>
      <td class="font-bold">${formatRupiah(totalDocVal)}</td>
      <td colspan="3"></td>
    </tr>
  `;

  resContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// 9. TAB 4: DATA KEEP & FOLLOW UP CUSTOMER (GOOGLE SHEETS INTEGRATION)
// rawKeepRows, filteredKeepRows, keepCurrentPage, KEEP_PAGE_SIZE telah diinisialisasi secara global di bagian atas

function parseRFC4180CSV(str) {
  const arr = [];
  let quote = false;
  let row = [''];
  for (let c = 0; c < str.length; c++) {
    const cc = str[c], nc = str[c+1];
    if (cc === '"') {
      if (quote && nc === '"') { row[row.length - 1] += '"'; c++; }
      else { quote = !quote; }
    } else if (cc === ',' && !quote) {
      row.push('');
    } else if ((cc === '\r' || cc === '\n') && !quote) {
      if (cc === '\r' && nc === '\n') { c++; }
      arr.push(row);
      row = [''];
    } else {
      row[row.length - 1] += cc;
    }
  }
  if (row.length > 1 || row[0] !== '') arr.push(row);
  return arr;
}
window.parseRFC4180CSV = parseRFC4180CSV;

function parseKeepCSV(csvText, isSilent = false) {
  try {
    if (!csvText || typeof csvText !== 'string') return;
    const table = parseRFC4180CSV(csvText);
    if (!table || table.length < 2) return;

    const headers = table[0].map(h => h.trim());
    const jsonData = [];
    for (let i = 1; i < table.length; i++) {
      const rowArr = table[i];
      if (rowArr.length === 1 && !rowArr[0]) continue;
      const rowObj = {};
      for (let j = 0; j < headers.length; j++) {
        rowObj[headers[j]] = rowArr[j] !== undefined ? rowArr[j] : '';
      }
      rowObj._gsheetsIndex = i - 1;
      // Skip ghost/empty rows from spreadsheet formula artefacts
      const nama = String(rowObj['Nama'] || '').trim();
      const barang = String(rowObj['Barang Keep'] || '').trim();
      const wa = String(rowObj['Nomor WA'] || '').trim();
      const ts = String(rowObj['Timestamp'] || '').trim();
      if (!nama && !barang && !wa && !ts) continue;
      jsonData.push(rowObj);
    }

    if (jsonData.length === 0) return;
    rawKeepRows = jsonData;
    console.log(`Keep CSV parsed successfully (Fast RFC4180): ${rawKeepRows.length} rows`);

    const pill = document.getElementById('keepStatusPill');
    if (pill) {
      pill.innerHTML = `<span>📁 Sumber: Google Sheets Live (${rawKeepRows.length.toLocaleString('id-ID')} Baris Data)</span>`;
    }

    populateKeepPeriodeDropdown();
    populateKeepSosmedDropdown();
    populateAuditPeriodeDropdown();
    applyKeepFilters();

    if (typeof rawCrosellingRows !== 'undefined' && rawCrosellingRows.length > 0 && typeof runARMissingSosmedAudit === 'function') {
      runARMissingSosmedAudit();
    }
  } catch(e) {
    console.error("Error parsing Keep CSV:", e);
  }
}
window.parseKeepCSV = parseKeepCSV;

async function loadKeepData(isSilent = false) {
  const pill = document.getElementById('keepStatusPill');
  if (pill && !isSilent) pill.innerHTML = '<span>⏳ Membaca Data Keep MySQL...</span>';

  // 1. Coba baca langsung dari Database SQL DCMS
  try {
    const resSql = await fetch(`/api/sosmed-keeps?t=${Date.now()}`);
    if (resSql.ok) {
      const json = await resSql.json();
      if (json && json.success && Array.isArray(json.data) && json.data.length > 0) {
        // Deduplikasi cerdas di client: satukan duplikat ganda jika ada 2 pesan sama dari Fonnte + Baileys
        const deduplicated = [];
        const seenMap = new Map();

        json.data.forEach((item) => {
          const cleanWA = String(item.nomor_wa || '').replace(/\D/g, '');
          const last8 = cleanWA.length >= 8 ? cleanWA.slice(-8) : cleanWA;
          const cleanBarang = String(item.barang_keep || '').toLowerCase().replace(/[^a-z0-9]/g, '');
          const cleanNama = String(item.nama || '').toLowerCase().replace(/[^a-z0-9]/g, '');
          const dedupeKey = last8 ? `${last8}_${cleanBarang.slice(0, 16)}` : `id_${item.id}`;

          if (seenMap.has(dedupeKey)) {
            const existingIdx = seenMap.get(dedupeKey);
            const existing = deduplicated[existingIdx];
            // Jika baris yang sudah ada belum di-claim tapi item duplikat sudah di-claim, ganti dengan item yang sudah di-claim
            if ((!existing.nama_tim_follow_up && item.nama_tim_follow_up) || (!existing.nomor_fu && item.nomor_fu)) {
              deduplicated[existingIdx] = item;
            }
            return;
          }

          seenMap.set(dedupeKey, deduplicated.length);
          deduplicated.push(item);
        });

        rawKeepRows = deduplicated.map((item, idx) => ({
          'id': item.id || null,
          'Timestamp': item.keep_timestamp || '',
          'Nomor Sender': item.nomor_sender || '',
          'Grup ID': item.grup_id || '',
          'Sender': item.sender || '',
          'GRUP': item.grup || '',
          '!grup': item.jenis_chat || 'GRUP',
          'Nama': item.nama || '',
          'Nomor WA': item.nomor_wa || '',
          'Barang Keep': item.barang_keep || '',
          'Tanggal Ambil': item.tanggal_ambil || '',
          'Catatan': item.catatan || '',
          'Remarks': item.remarks || '',
          'Kode Sosmed': item.kode_sosmed || '',
          'REPORT AR': item.report_ar || '',
          'Tanggal Input AR': item.tanggal_input_ar || '',
          'AR dari VLOOKUP': item.ar_vlookup || '',
          'Tanggal VLOOKUP': item.tanggal_vlookup || '',
          'Cabang': item.cabang || '',
          'Evidence': item.evidence || '',
          'Nomor Follow Up': item.nomor_follow_up || '',
          'Nama Tim Follow Up': item.nama_tim_follow_up || '',
          'Status': item.status || '',
          'Feedback Tim FU': item.feedback_tim_fu || '',
          'NAMA TOKO KOMPETITOR\n(JIKA USER DEAL DI TOKO LAIN)': item.nama_toko_kompetitor || '',
          'USER KREDIT': item.user_kredit || '',
          'PERIODE': item.periode || '',
          'ITEM': item.item || '',
          'HAPUS DATA': 'FALSE',
          'Nomor FU': item.nomor_fu || '',
          _gsheetsIndex: idx
        }));

        let maxId = 0;
        for (let i = 0; i < Math.min(json.data.length, 100); i++) {
          const nid = parseInt(json.data[i].id, 10);
          if (!isNaN(nid) && nid > maxId) maxId = nid;
        }
        if (maxId > 0) {
          latestKnownKeepId = Math.max(latestKnownKeepId, maxId);
        }

        if (pill) {
          pill.innerHTML = `<span>⚡ Sumber: Realtime MySQL (${rawKeepRows.length.toLocaleString('id-ID')} Data dari Fonnte)</span>`;
        }

        populateKeepPeriodeDropdown();
        populateKeepSosmedDropdown();
        populateAuditPeriodeDropdown();
        applyKeepFilters();
        renderKeepPage();

        // Mulai polling background realtime data format keep
        startKeepRealtimePolling();

        if (typeof rawCrosellingRows !== 'undefined' && rawCrosellingRows.length > 0 && typeof runARMissingSosmedAudit === 'function') {
          runARMissingSosmedAudit();
        }
        return;
      }
    }
  } catch (sqlErr) {
    console.warn("Koneksi SQL sosmed-keeps belum siap:", sqlErr);
  }

  // 2. Fallback baca dari CSV lokal jika endpoint SQL kosong / belum siap
  try {
    const candidatePaths = [
      `/workspace-sosmed/keep-data.csv?t=${Date.now()}`,
      `keep-data.csv?t=${Date.now()}`,
      `workspace-sosmed/keep-data.csv?t=${Date.now()}`
    ];

    let csvRes = null;
    for (const p of candidatePaths) {
      try {
        const testRes = await fetch(p);
        if (testRes.ok) {
          csvRes = testRes;
          break;
        }
      } catch (e) {}
    }

    if (csvRes && csvRes.ok) {
      const csvText = await csvRes.text();
      if (csvText && csvText.trim().length > 100) {
        parseKeepCSV(csvText, isSilent);
        if (pill) {
          pill.innerHTML = `<span>📂 Sumber: CSV Lokal (${rawKeepRows.length.toLocaleString('id-ID')} Data)</span>`;
        }
        return;
      }
    }
  } catch (csvErr) {
    console.warn("Fallback CSV gagal:", csvErr);
  }

  // 3. Jika database memang masih kosong (0 data) dan CSV belum tersedia
  rawKeepRows = [];
  filteredKeepRows = [];
  populateKeepPeriodeDropdown();
  populateKeepSosmedDropdown();
  populateAuditPeriodeDropdown();
  applyKeepFilters();
  renderKeepPage();

  if (pill && !isSilent) {
    pill.innerHTML = '<span>⚡ Sumber: Realtime MySQL (0 Data)</span>';
  }
}
window.loadKeepData = loadKeepData;

function renderKeepTable() {
  applyKeepFilters();
}
window.renderKeepTable = renderKeepTable;

async function syncLiveKeepData() {
  const btn = document.getElementById('btnSyncKeepData');
  const headerBtn = document.getElementById('headerBtnSyncSheets');
  const originalText = btn ? btn.innerHTML : '';
  const originalHeaderText = headerBtn ? headerBtn.innerHTML : '';
  if (btn) btn.innerHTML = '⏳ Menyegarkan...';
  if (headerBtn) headerBtn.innerHTML = '<span>Refreshing...</span>';

  const prog = startSmoothProgress({
    id: 'sync-live-keep-progress',
    title: '🔄 Muat Ulang Data Realtime',
    detail: 'Menghubungkan & mengambil data format keep dari database DCMS...',
    timeoutMs: 30000
  });

  try {
    // 1. Panggil syncKeep di backend (jika tabel sosmed_keeps masih 0, backend otomatis mengimpor 13k+ baris CSV lokal)
    try {
      const syncRes = await fetch('/api/sync-keep?t=' + Date.now());
      if (syncRes.ok) {
        const syncJson = await syncRes.json();
        if (syncJson && syncJson.imported) {
          console.info("Auto-import CSV berhasil:", syncJson.total_rows);
        }
      }
    } catch (e) {
      console.warn("Sync keep API check skipped:", e);
    }

    // 2. Muat ulang data keep
    await loadKeepData();
    applyKeepFilters();
    prog.complete({
      title: '✅ Data Realtime Berhasil Dimuat!',
      detail: `Total ${rawKeepRows.length.toLocaleString('id-ID')} baris data transaksi siap digunakan`
    });

    if (typeof window.showDcmsToast === 'function') {
      window.showDcmsToast({
        title: 'Database Realtime Terkini',
        message: `Total ${rawKeepRows.length.toLocaleString('id-ID')} baris transaksi termuat langsung dari database.`,
        type: 'success'
      });
    }
  } catch (err) {
    console.error("Refresh error:", err);
    prog.fail({
      title: '❌ Gagal Muat Data Realtime',
      detail: err.message || 'Koneksi ke database DCMS terganggu'
    });
    await loadKeepData();
  } finally {
    if (btn) btn.innerHTML = originalText;
    if (headerBtn) headerBtn.innerHTML = originalHeaderText;
  }
}
window.syncLiveKeepData = syncLiveKeepData;

// Global listener saat background auto-sync selesai memperbarui data
window.addEventListener('dcms:keep-synced', async (e) => {
  console.log('🔄 [Workspace Sosmed] Sinyal dcms:keep-synced diterima dari background sync:', e.detail);
  try {
    if (typeof loadKeepData === 'function') {
      await loadKeepData();
      if (typeof applyKeepFilters === 'function') {
        applyKeepFilters();
      }
    }
  } catch (err) {
    console.warn('Gagal memperbarui tampilan keep:', err);
  }
});

const KEEP_MONTH_WEIGHTS = {
  'januari': 1, 'jan': 1,
  'februari': 2, 'feb': 2,
  'maret': 3, 'mar': 3,
  'april': 4, 'apr': 4,
  'mei': 5, 'may': 5,
  'juni': 6, 'jun': 6,
  'juli': 7, 'jul': 7,
  'agustus': 8, 'aug': 8, 'agu': 8,
  'september': 9, 'sep': 9,
  'oktober': 10, 'oct': 10, 'okt': 10,
  'november': 11, 'nov': 11,
  'desember': 12, 'dec': 12, 'des': 12
};

const KEEP_MONTH_NAMES = {
  1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
  5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
  9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
};

function parseKeepPeriodInfo(str) {
  if (!str || typeof str !== 'string') return null;
  const clean = str.trim();
  if (!clean || clean === '-' || clean === 'undefined' || clean === 'null') return null;

  const parts = clean.split(/[-_/ ]+/);
  if (parts.length < 2) return null;
  const mStr = (parts[0] || '').toLowerCase();
  const m = KEEP_MONTH_WEIGHTS[mStr];
  if (!m) return null;

  let y = parseInt(parts[1], 10);
  if (isNaN(y)) return null;
  if (y < 100) y += 2000;

  // Filter out invalid/ghost formula years (e.g. 99 -> 2099 or 1899)
  if (y < 2020 || y > 2040) return null;

  return {
    raw: clean,
    month: m,
    year: y,
    weight: y * 100 + m,
    label: `${KEEP_MONTH_NAMES[m]} ${y}`
  };
}

function getSortedValidKeepPeriods(rows) {
  if (!rows || rows.length === 0) return [];
  const periodMap = new Map();

  rows.forEach(r => {
    const p = String(r['PERIODE'] || '').trim();
    if (p && !periodMap.has(p)) {
      const info = parseKeepPeriodInfo(p);
      if (info) {
        periodMap.set(p, info);
      }
    }
  });

  return Array.from(periodMap.values()).sort((a, b) => b.weight - a.weight);
}

function populateKeepPeriodeDropdown() {
  const select = document.getElementById('keepFilterPeriode');
  if (!select || !rawKeepRows || rawKeepRows.length === 0) return;

  const currentVal = select.value;
  const sortedPeriods = getSortedValidKeepPeriods(rawKeepRows);

  if (sortedPeriods.length === 0) return;

  const newestPeriod = sortedPeriods[0];

  let chosenVal = 'ALL';
  if (currentVal && currentVal !== 'ALL' && sortedPeriods.some(p => p.raw.toLowerCase() === currentVal.toLowerCase())) {
    chosenVal = currentVal;
  } else if (newestPeriod) {
    chosenVal = newestPeriod.raw;
  }

  select.innerHTML = '<option value="ALL">Semua Periode</option>' + 
    sortedPeriods.map(p => {
      const isNewest = (p.raw === newestPeriod.raw) ? ' (Terbaru)' : '';
      return `<option value="${p.raw}">${p.label}${isNewest}</option>`;
    }).join('');

  select.value = chosenVal;
}

function populateAuditPeriodeDropdown() {
  const select = document.getElementById('auditFilterPeriode');
  if (!select || !rawKeepRows || rawKeepRows.length === 0) return;

  const currentVal = select.value;
  const sortedPeriods = getSortedValidKeepPeriods(rawKeepRows);

  if (sortedPeriods.length === 0) return;

  const newestPeriod = sortedPeriods[0];

  let chosenVal = 'ALL';
  if (currentVal && currentVal !== 'ALL' && sortedPeriods.some(p => p.raw.toLowerCase() === currentVal.toLowerCase())) {
    chosenVal = currentVal;
  } else if (newestPeriod) {
    chosenVal = newestPeriod.raw;
  }

  select.innerHTML = '<option value="ALL">Semua Periode</option>' + 
    sortedPeriods.map(p => {
      const isNewest = (p.raw === newestPeriod.raw) ? ' (Bulan ini)' : '';
      return `<option value="${p.raw}">${p.label}${isNewest}</option>`;
    }).join('');

  select.value = chosenVal;
}

function populateKeepSosmedDropdown() {
  const select = document.getElementById('keepFilterSosmed');
  if (!select) return;

  const currentVal = select.value;
  const uniqueCodes = new Set();
  rawKeepRows.forEach(r => {
    const code = String(r['Kode Sosmed'] || '').trim();
    if (code && code !== 'undefined') uniqueCodes.add(code);
  });

  const sortedCodes = Array.from(uniqueCodes).sort();
  select.innerHTML = '<option value="ALL">Semua Kode Sosmed</option>' + 
    sortedCodes.map(c => `<option value="${c}">${c}</option>`).join('');

  if (currentVal && sortedCodes.includes(currentVal)) {
    select.value = currentVal;
  }
}

// Peta Resmi ID Grup WhatsApp Gadgetmart ke Cabang
const KEEP_GROUP_CABANG_MAP = {
  '120363331030729373@g.us': { cabang: 'BJMC', grup: 'HASIL TIM FU EXPERT' },
  '120363328092703569@g.us': { cabang: 'PONTIANAK', grup: 'GM PTK' },
  '120363309345392029@g.us': { cabang: 'BATULICIN', grup: 'GM BTC' },
  '120363331600007500@g.us': { cabang: 'SAMPIT', grup: 'GM SPT' },
  '120363327077123880@g.us': { cabang: 'BALIKPAPAN', grup: 'GM BPP' },
  '120363327671212528@g.us': { cabang: 'BANJARBARU', grup: 'GM BJB' },
  '6287832697916-1594798567@g.us': { cabang: 'BANJARMASIN AREA', grup: 'TL SOSMED GADGETMART' },
  '120363309019909873@g.us': { cabang: 'TANJUNG', grup: 'GM TJG' },
  '120363327089337045@g.us': { cabang: 'KAPUAS', grup: 'GM KPS' },
  '120363312505493730@g.us': { cabang: 'MARTAPURA', grup: 'GM MTP' },
  '120363329573289564@g.us': { cabang: 'KOTABARU', grup: 'GM KTB' },
  '120363328889884494@g.us': { cabang: 'AMUNTAI', grup: 'GM AMT' },
  '120363295811163792@g.us': { cabang: 'BJMA', grup: 'COD ACC GADGETMART' },
  '120363326647069711@g.us': { cabang: 'PELAIHARI', grup: 'GM PLH' },
  '120363429425612636@g.us': { cabang: 'SUNGAI DANAU', grup: 'GM SDN (SUNGAI DANAU)' },
  '120363374619118540@g.us': { cabang: 'ONLINE', grup: 'REMARKS SHOPEE TOKOPEDIA TIKTOK' },
  '120363428947855050@g.us': { cabang: 'BJMX', grup: 'MISTORE BELITUNG' },
  '120363312009640450@g.us': { cabang: 'BATAS KOTA', grup: 'GM BBK - QMALL' },
  '120363429226529886@g.us': { cabang: 'BJMC', grup: 'Tes BOT' },
};

function isKeepUnassigned(r) {
  if (!r) return false;
  const fuName = String(r['Nama Tim Follow Up'] || r['nama_tim_follow_up'] || r['fu_name'] || '').trim();
  const rawFU = String(r['Nomor Follow Up'] || r['nomor_follow_up'] || r['Nomor FU'] || r['nomor_fu'] || '').trim();
  const cleanDigits = rawFU.replace(/\D/g, '');
  const hasPIC = (fuName && fuName !== '-' && fuName !== '""') || (cleanDigits.length >= 8);
  return !hasPIC;
}
window.isKeepUnassigned = isKeepUnassigned;

function updateKeepActiveCardStyle(currentStatus) {
  const cards = {
    'ALL': document.getElementById('cardKeepTotal'),
    'UNASSIGN': document.getElementById('cardKeepUnassign'),
    'WIN': document.getElementById('cardKeepWin'),
    'NEED TO CHECK': document.getElementById('cardKeepNeedToCheck'),
    'ON PROGRESS': document.getElementById('cardKeepProgress'),
    'LOSE': document.getElementById('cardKeepLose')
  };

  const ringClasses = [
    'ring-2', 'ring-brand-500', 'ring-emerald-500', 'ring-amber-500', 'ring-sky-500', 'ring-rose-500',
    'bg-brand-50/40', 'bg-emerald-50/50', 'bg-amber-50/50', 'bg-sky-50/50', 'bg-rose-50/50', 'shadow-md'
  ];

  Object.values(cards).forEach(el => {
    if (el) el.classList.remove(...ringClasses);
  });

  const st = (currentStatus || 'ALL').toUpperCase();
  if (st === 'WIN') {
    cards['WIN']?.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50/50', 'shadow-md');
  } else if (st === 'NEED TO CHECK') {
    cards['NEED TO CHECK']?.classList.add('ring-2', 'ring-amber-500', 'bg-amber-50/50', 'shadow-md');
  } else if (st.includes('PROGRESS')) {
    cards['ON PROGRESS']?.classList.add('ring-2', 'ring-sky-500', 'bg-sky-50/50', 'shadow-md');
  } else if (st === 'LOSE' || st === 'TAKE OUT' || st.includes('CANCEL') || st.includes('BATAL')) {
    cards['LOSE']?.classList.add('ring-2', 'ring-rose-500', 'bg-rose-50/50', 'shadow-md');
  } else if (st === 'ALL') {
    cards['ALL']?.classList.add('ring-2', 'ring-brand-500', 'bg-brand-50/40', 'shadow-md');
  }
}

function applyKeepFilters() {
  if (!rawKeepRows || rawKeepRows.length === 0) return;

  const periodVal = document.getElementById('keepFilterPeriode') ? document.getElementById('keepFilterPeriode').value : 'ALL';
  const sosmedVal = document.getElementById('keepFilterSosmed') ? document.getElementById('keepFilterSosmed').value : 'ALL';
  const statusVal = document.getElementById('keepFilterStatus') ? document.getElementById('keepFilterStatus').value : 'ALL';
  const tanggalVal = document.getElementById('keepFilterTanggal') ? document.getElementById('keepFilterTanggal').value : '';
  const reportARVal = (document.getElementById('keepFilterReportAR') ? document.getElementById('keepFilterReportAR').value : '').trim().toLowerCase();
  const keyword = (document.getElementById('keepSearchInput') ? document.getElementById('keepSearchInput').value : '').trim().toLowerCase();

  // 1. Filter dasar (Periode, Kode Sosmed, Tanggal, Report AR, dan Keyword Pencarian termasuk Cabang)
  const baseRows = rawKeepRows.filter(r => {
    // 1. Period filter
    if (periodVal !== 'ALL') {
      const p = String(r['PERIODE'] || '').trim().toLowerCase();
      if (p !== periodVal.toLowerCase()) return false;
    }

    // 2. Sosmed filter
    if (sosmedVal !== 'ALL') {
      const s = String(r['Kode Sosmed'] || '').trim();
      if (s !== sosmedVal) return false;
    }

    // 3. Filter Tanggal Format Keep (Timestamp Google Sheets)
    if (tanggalVal) {
      const keepDateIso = parseDateToISO(r['Timestamp']);
      if (keepDateIso !== tanggalVal) return false;
    }

    // 4. Filter by Report No. AR
    if (reportARVal) {
      const repAR = String(r['REPORT AR'] || '').toLowerCase();
      if (!repAR.includes(reportARVal)) return false;
    }

    // 5. Keyword search (Name, Phone, Item, AR, Notes, Cabang, Sosmed, Tim FU)
    if (keyword) {
      const cab = String(r['Cabang'] || (r['Grup ID'] && KEEP_GROUP_CABANG_MAP[r['Grup ID']] ? KEEP_GROUP_CABANG_MAP[r['Grup ID']].cabang : '') || '');
      const fuName = String(r['Nama Tim Follow Up'] || r['nama_tim_follow_up'] || '');
      const fuPhone = String(r['Nomor FU'] || r['nomor_fu'] || r['Nomor Follow Up'] || '');
      const combined = `${r['Nama'] || ''} ${r['Nomor WA'] || ''} ${r['Barang Keep'] || ''} ${r['REPORT AR'] || ''} ${r['Catatan'] || ''} ${r['Feedback Tim FU'] || ''} ${cab} ${r['Kode Sosmed'] || ''} ${fuName} ${fuPhone}`.toLowerCase();
      if (!combined.includes(keyword)) return false;
    }

    return true;
  });

  // 2. Hitung Summary KPI Stats dari baseRows (agar angka di kartu tetap informatif saat difilter)
  let winCount = 0;
  let needToCheckCount = 0;
  let progressCount = 0;
  let loseCount = 0;
  let unassignCount = 0;

  baseRows.forEach(r => {
    const st = String(r['Status'] || '').toUpperCase();
    if (isKeepUnassigned(r)) unassignCount++;

    if (st === 'WIN') winCount++;
    else if (st === 'NEED TO CHECK') needToCheckCount++;
    else if (st.includes('PROGRESS')) progressCount++;
    else if (st === 'LOSE' || st === 'TAKE OUT' || st.includes('CANCEL') || st.includes('BATAL')) loseCount++;
  });

  const total = baseRows.length;
  const closingRate = total > 0 ? Math.round((winCount / total) * 100) : 0;

  if (document.getElementById('keepTotalCount')) document.getElementById('keepTotalCount').innerText = total.toLocaleString('id-ID');
  if (document.getElementById('keepUnassignCount')) document.getElementById('keepUnassignCount').innerText = unassignCount.toLocaleString('id-ID');
  if (document.getElementById('keepWinCount')) document.getElementById('keepWinCount').innerText = winCount.toLocaleString('id-ID');
  if (document.getElementById('keepNeedToCheckCount')) document.getElementById('keepNeedToCheckCount').innerText = needToCheckCount.toLocaleString('id-ID');
  if (document.getElementById('keepProgressCount')) document.getElementById('keepProgressCount').innerText = progressCount.toLocaleString('id-ID');
  if (document.getElementById('keepLoseCount')) document.getElementById('keepLoseCount').innerText = loseCount.toLocaleString('id-ID');
  if (document.getElementById('keepClosingRate')) document.getElementById('keepClosingRate').innerText = `${closingRate}%`;

  // Render / Update Dashboard Top Performance OLA / SLA Cabang
  if (typeof renderBranchOLADashboard === 'function') {
    renderBranchOLADashboard(baseRows);
  }

  // 3. Terapkan Filter Status pada data tabel
  if (statusVal === 'ALL') {
    filteredKeepRows = baseRows;
  } else if (statusVal === 'UNASSIGN') {
    filteredKeepRows = baseRows.filter(r => isKeepUnassigned(r));
  } else if (statusVal === 'OLA_OVERDUE') {
    filteredKeepRows = baseRows.filter(r => {
      const ola = getKeepOLAMeta(r);
      return !ola.isClaimed && ola.isOverdue;
    });
  } else if (statusVal === 'OLA_PENDING') {
    filteredKeepRows = baseRows.filter(r => {
      const ola = getKeepOLAMeta(r);
      return !ola.isClaimed;
    });
  } else {
    filteredKeepRows = baseRows.filter(r => {
      const st = String(r['Status'] || '').trim().toUpperCase();
      if (statusVal === 'ON PROGRESS') {
        return st.includes('PROGRESS');
      }
      if (statusVal === 'LOSE') {
        return st === 'LOSE' || st === 'TAKE OUT' || st.includes('CANCEL') || st.includes('BATAL');
      }
      return st === statusVal.toUpperCase();
    });
  }

  // 4. Update visual active ring pada kartu metrik
  updateKeepActiveCardStyle(statusVal);

  let activeKeepCount = 0;
  if (document.getElementById('keepFilterPeriode') && document.getElementById('keepFilterPeriode').value !== 'ALL') activeKeepCount++;
  if (document.getElementById('keepFilterSosmed') && document.getElementById('keepFilterSosmed').value !== 'ALL') activeKeepCount++;
  if (document.getElementById('keepFilterTanggal') && document.getElementById('keepFilterTanggal').value) activeKeepCount++;
  if (document.getElementById('keepFilterStatus') && document.getElementById('keepFilterStatus').value !== 'ALL') activeKeepCount++;
  if (document.getElementById('keepSearchInput') && document.getElementById('keepSearchInput').value.trim()) activeKeepCount++;
  const badgeKeep = document.getElementById('keepFilterActiveCount');
  if (badgeKeep) badgeKeep.innerText = activeKeepCount;

  keepCurrentPage = 1;
  renderKeepPage();
}

function toggleKeepFiltersSection() {
  const el = document.getElementById('keepFilterSection');
  if (el) {
    el.style.display = (el.style.display === 'none' ? 'flex' : 'none');
  }
}

function toggleAuditFiltersSection() {
  const el = document.getElementById('auditFilterSection');
  if (el) {
    el.style.display = (el.style.display === 'none' ? 'flex' : 'none');
  }
}

function sanitizeKeepAutofill() {
  const inp = document.getElementById('keepSearchInput');
  if (!inp) return;
  const val = (inp.value || '').trim();
  if (val.includes('@') || val.toLowerCase().includes('admin') || val.toLowerCase().includes('.com')) {
    inp.value = '';
    if (typeof toggleKeepClearBtn === 'function') toggleKeepClearBtn();
    if (typeof applyKeepFilters === 'function') applyKeepFilters();
  }
}
window.sanitizeKeepAutofill = sanitizeKeepAutofill;

function toggleKeepClearBtn() {
  const inp = document.getElementById('keepSearchInput');
  const btn = document.getElementById('btnClearKeepSearch');
  if (!btn) return;
  if (inp && inp.value.trim().length > 0) {
    btn.classList.remove('hidden');
    btn.classList.add('flex');
  } else {
    btn.classList.remove('flex');
    btn.classList.add('hidden');
  }
}
window.toggleKeepClearBtn = toggleKeepClearBtn;

function clearKeepSearchInput() {
  const inp = document.getElementById('keepSearchInput');
  if (inp) {
    inp.value = '';
    inp.focus();
  }
  toggleKeepClearBtn();
  applyKeepFilters();
}
window.clearKeepSearchInput = clearKeepSearchInput;

function resetKeepFilters() {
  const kSel = document.getElementById('keepFilterPeriode');
  if (kSel) {
    kSel.value = kSel.options.length > 1 ? kSel.options[1].value : 'ALL';
  }
  if (document.getElementById('keepFilterSosmed')) document.getElementById('keepFilterSosmed').value = 'ALL';
  if (document.getElementById('keepFilterStatus')) document.getElementById('keepFilterStatus').value = 'ALL';
  if (document.getElementById('keepFilterTanggal')) document.getElementById('keepFilterTanggal').value = '';
  if (document.getElementById('keepFilterReportAR')) document.getElementById('keepFilterReportAR').value = '';
  if (document.getElementById('keepSearchInput')) document.getElementById('keepSearchInput').value = '';
  toggleKeepClearBtn();
  applyKeepFilters();
}

function filterByKeepStatus(targetStatus) {
  const statusEl = document.getElementById('keepFilterStatus');
  if (!statusEl) return;
  const currentVal = (statusEl.value || 'ALL').toUpperCase();
  const targetVal = (targetStatus || 'ALL').toUpperCase();

  // Toggle kembali ke ALL jika kartu yang sama diklik dua kali
  if (currentVal === targetVal && targetVal !== 'ALL') {
    statusEl.value = 'ALL';
  } else {
    statusEl.value = targetStatus;
  }
  applyKeepFilters();
}
window.filterByKeepStatus = filterByKeepStatus;

function changeKeepPage(delta) {
  const maxPage = Math.max(1, Math.ceil(filteredKeepRows.length / KEEP_PAGE_SIZE));
  const newPage = keepCurrentPage + delta;
  if (newPage >= 1 && newPage <= maxPage) {
    keepCurrentPage = newPage;
    renderKeepPage();
  }
}

function getCustomerInitials(name) {
  if (!name || name === '-') return 'CU';
  const clean = String(name).replace(/[^a-zA-Z0-9\s]/g, '').trim();
  const parts = clean.split(/\s+/).filter(Boolean);
  if (parts.length === 0) return 'CU';
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[1][0]).toUpperCase();
}

function formatKeepDisplayDate(ts) {
  if (!ts) return '-';
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  if (typeof ts === 'number') {
    if (typeof XLSX !== 'undefined' && XLSX.SSF && XLSX.SSF.parse_date_code) {
      const d = XLSX.SSF.parse_date_code(ts);
      if (d) {
        const mStr = months[(d.m - 1) % 12] || 'Jan';
        return `${d.d} ${mStr} ${d.y}`;
      }
    }
  }
  if (ts instanceof Date) {
    return `${ts.getDate()} ${months[ts.getMonth()]} ${ts.getFullYear()}`;
  }
  if (typeof ts === 'string') {
    const trimmed = ts.trim();
    if (trimmed.endsWith('Z') || trimmed.includes('+00:00')) {
      try {
        const d = new Date(trimmed);
        if (!isNaN(d.getTime())) {
          const dWITA = new Date(d.toLocaleString('en-US', { timeZone: 'Asia/Makassar' }));
          return `${dWITA.getDate()} ${months[dWITA.getMonth()]} ${dWITA.getFullYear()}`;
        }
      } catch (e) {}
    }
    // 1. ISO format: 2026-11-05 or 2026-11-05T21:10:56... or 2026-11-05 21:10:56
    const datePart = trimmed.split('T')[0].split(' ')[0];
    const isoParts = datePart.split('-');
    if (isoParts.length === 3 && isoParts[0].length === 4) {
      const y = isoParts[0];
      const m = parseInt(isoParts[1], 10);
      const day = parseInt(isoParts[2], 10);
      if (!isNaN(day) && !isNaN(m) && m >= 1 && m <= 12) {
        return `${day} ${months[m - 1]} ${y}`;
      }
    }
    // 2. Slash format: 11/1/2026 or 1/11/2026 or 11/01/2026
    const slashParts = datePart.split('/');
    if (slashParts.length >= 3) {
      const p0 = parseInt(slashParts[0], 10);
      const p1 = parseInt(slashParts[1], 10);
      let y = slashParts[2].trim();
      if (y.length === 2) y = '20' + y;
      if (!isNaN(p0) && !isNaN(p1)) {
        let day, m;
        if (p0 > 12) {
          day = p0;
          m = p1;
        } else if (p1 > 12) {
          day = p1;
          m = p0;
        } else {
          m = p0;
          day = p1;
        }
        if (m >= 1 && m <= 12) {
          return `${day} ${months[m - 1]} ${y}`;
        }
      }
    }
    return ts;
  }
  return String(ts);
}

function formatKeepDisplayTime(ts) {
  if (!ts) return '-';
  if (ts instanceof Date) {
    const h = String(ts.getHours()).padStart(2, '0');
    const m = String(ts.getMinutes()).padStart(2, '0');
    const s = String(ts.getSeconds()).padStart(2, '0');
    return `${h}:${m}:${s} WITA`;
  }
  if (typeof ts === 'string') {
    const trimmed = ts.trim();
    // Jika timestamp memiliki penanda UTC (akhiran Z atau +00:00), konversikan ke timezone WITA (UTC+8)
    if (trimmed.endsWith('Z') || trimmed.includes('+00:00')) {
      try {
        const d = new Date(trimmed);
        if (!isNaN(d.getTime())) {
          const timeStr = d.toLocaleTimeString('id-ID', { timeZone: 'Asia/Makassar', hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
          return `${timeStr.replace(/\./g, ':')} WITA`;
        }
      } catch (e) {}
    }
    if (trimmed.includes('T') || trimmed.includes(' ')) {
      const timePart = trimmed.includes('T') ? trimmed.split('T')[1] : trimmed.split(' ')[1];
      if (timePart) {
        const cleanTime = timePart.split('.')[0].split('+')[0].split('Z')[0].trim();
        const parts = cleanTime.split(':');
        if (parts.length >= 2) {
          const h = parts[0].padStart(2, '0');
          const m = parts[1].padStart(2, '0');
          const s = (parts[2] || '00').padStart(2, '0');
          return `${h}:${m}:${s} WITA`;
        }
      }
    }
    const colonParts = trimmed.split(':');
    if (colonParts.length >= 2 && colonParts.length <= 3 && trimmed.length <= 8) {
      const h = colonParts[0].padStart(2, '0');
      const m = colonParts[1].padStart(2, '0');
      const s = (colonParts[2] || '00').padStart(2, '0');
      return `${h}:${m}:${s} WITA`;
    }
    try {
      const d = new Date(trimmed);
      if (!isNaN(d.getTime())) {
        const h = String(d.getHours()).padStart(2, '0');
        const m = String(d.getMinutes()).padStart(2, '0');
        const s = String(d.getSeconds()).padStart(2, '0');
        return `${h}:${m}:${s} WITA`;
      }
    } catch (e) {}
  }
  return '-';
}
window.formatKeepDisplayTime = formatKeepDisplayTime;

function parseKeepDateObj(ts) {
  if (!ts) return null;
  if (ts instanceof Date) return isNaN(ts.getTime()) ? null : ts;

  if (typeof ts === 'number') {
    if (ts > 30000 && ts < 60000) {
      const ms = (ts - 25569) * 86400 * 1000;
      const d = new Date(ms);
      return isNaN(d.getTime()) ? null : d;
    }
  }

  if (typeof ts === 'string') {
    const trimmed = ts.trim();
    if (!trimmed || trimmed === '-') return null;

    if (trimmed.endsWith('Z') || trimmed.includes('+00:00')) {
      const d = new Date(trimmed);
      if (!isNaN(d.getTime())) return d;
    }

    if (!isNaN(Number(trimmed)) && !trimmed.includes(':') && !trimmed.includes('-') && !trimmed.includes('/')) {
      const num = Number(trimmed);
      if (num > 30000 && num < 60000) {
        const ms = (num - 25569) * 86400 * 1000;
        const d = new Date(ms);
        if (!isNaN(d.getTime())) return d;
      }
    }

    const isoMatch = trimmed.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})(?:[ T](\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?/);
    if (isoMatch) {
      const y = parseInt(isoMatch[1], 10);
      const m = parseInt(isoMatch[2], 10) - 1;
      const d = parseInt(isoMatch[3], 10);
      const hh = parseInt(isoMatch[4] || '0', 10);
      const mm = parseInt(isoMatch[5] || '0', 10);
      const ss = parseInt(isoMatch[6] || '0', 10);
      const dateObj = new Date(y, m, d, hh, mm, ss);
      if (!isNaN(dateObj.getTime())) return dateObj;
    }

    const slashMatch = trimmed.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{2,4})(?:[ T](\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?/);
    if (slashMatch) {
      let p0 = parseInt(slashMatch[1], 10);
      let p1 = parseInt(slashMatch[2], 10);
      let y = parseInt(slashMatch[3], 10);
      if (y < 100) y += 2000;
      let d, m;
      if (p0 > 12) {
        d = p0; m = p1 - 1;
      } else if (p1 > 12) {
        d = p1; m = p0 - 1;
      } else {
        m = p0 - 1; d = p1;
      }
      const hh = parseInt(slashMatch[4] || '0', 10);
      const mm = parseInt(slashMatch[5] || '0', 10);
      const ss = parseInt(slashMatch[6] || '0', 10);
      const dateObj = new Date(y, m, d, hh, mm, ss);
      if (!isNaN(dateObj.getTime())) return dateObj;
    }

    const fallback = new Date(trimmed);
    if (!isNaN(fallback.getTime())) return fallback;
  }
  return null;
}
window.parseKeepDateObj = parseKeepDateObj;

function getKeepOLAMeta(r, olaLimitMinutes = 15) {
  if (!r) return { isClaimed: false, isOverdue: false, diffMinutes: 0, badgeHtml: '' };

  const fuName = String(r['Nama Tim Follow Up'] || '').trim();
  const fuNumber = String(r['Nomor Follow Up'] || '').trim();
  const rawSt = String(r['Status'] || '').trim().toUpperCase();

  // Keep dianggap sudah di-claim jika memiliki PIC Tim FU atau status sudah berproses
  const isClaimed = Boolean(
    fuName || 
    fuNumber || 
    rawSt.includes('PROGRESS') || 
    rawSt === 'WIN' || 
    rawSt === 'LOSE' || 
    rawSt === 'TAKE OUT' || 
    rawSt.includes('CLOSING')
  );

  if (isClaimed) {
    return {
      isClaimed: true,
      isOverdue: false,
      diffMinutes: 0,
      badgeHtml: `<span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded" title="Sudah di-claim oleh tim follow up"><span class="text-emerald-600 font-bold">✓</span><span>Di-claim</span></span>`
    };
  }

  // Belum di-claim: hitung waktu respon OLA
  const ts = r['Timestamp'] || r['keep_timestamp'] || r.created_at;
  const dateObj = parseKeepDateObj(ts);

  if (!dateObj) {
    return {
      isClaimed: false,
      isOverdue: false,
      diffMinutes: 0,
      badgeHtml: `<span class="inline-flex items-center text-[10px] text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded">OLA -</span>`
    };
  }

  const now = new Date();
  const diffMs = now.getTime() - dateObj.getTime();
  const diffMinutes = Math.max(0, Math.floor(diffMs / 60000));

  // Jika data dari hari sebelumnya (> 12 jam atau tanggal kalender berbeda)
  const isDifferentDay = (now.toDateString() !== dateObj.toDateString()) && (diffMinutes > 720);
  if (isDifferentDay) {
    return {
      isClaimed: false,
      isOverdue: true,
      diffMinutes,
      badgeHtml: `<span class="inline-flex items-center text-[10px] font-medium text-slate-500 bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded" title="Format keep dari hari sebelumnya (Belum di-claim)">OLA: Lewat Hari</span>`
    };
  }

  if (diffMinutes <= olaLimitMinutes) {
    // Sesuai batas OLA (<= 15 menit)
    return {
      isClaimed: false,
      isOverdue: false,
      diffMinutes,
      badgeHtml: `<span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-1.5 py-0.5 rounded" title="Waktu tunggu respon: ${diffMinutes} menit (Batas OLA ${olaLimitMinutes} menit)"><span class="size-1.5 rounded-full bg-emerald-500"></span><span>OLA ${diffMinutes}m</span></span>`
    };
  } else {
    // Overdue melebihi 15 menit
    return {
      isClaimed: false,
      isOverdue: true,
      diffMinutes,
      badgeHtml: `<span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded shadow-2xs" title="Overdue: ${diffMinutes} menit belum di-claim (Batas OLA ${olaLimitMinutes} menit)"><span class="size-1.5 rounded-full bg-rose-500 animate-ping"></span><span>OLA +${diffMinutes}m</span></span>`
    };
  }
}
window.getKeepOLAMeta = getKeepOLAMeta;

// =========================================================================
// DASHBOARD TOP PERFORMANCE OLA / SLA CABANG (LEADERBOARD & METRICS)
// =========================================================================
let currentOLADashboardScope = 'PERIOD'; // 'PERIOD' or 'TODAY'
let isOLADashboardCollapsed = false;

function setOLADashboardScope(scope) {
  currentOLADashboardScope = scope;
  const btnPeriod = document.getElementById('btnOLAPeriodSelected');
  const btnToday = document.getElementById('btnOLAPeriodToday');
  if (btnPeriod && btnToday) {
    if (scope === 'TODAY') {
      btnToday.className = 'rounded-lg px-2.5 py-1 text-xs font-bold transition bg-white text-brand-700 shadow-xs cursor-pointer';
      btnPeriod.className = 'rounded-lg px-2.5 py-1 text-xs font-semibold transition text-slate-500 hover:text-slate-800 cursor-pointer';
    } else {
      btnPeriod.className = 'rounded-lg px-2.5 py-1 text-xs font-bold transition bg-white text-brand-700 shadow-xs cursor-pointer';
      btnToday.className = 'rounded-lg px-2.5 py-1 text-xs font-semibold transition text-slate-500 hover:text-slate-800 cursor-pointer';
    }
  }
  if (typeof renderBranchOLADashboard === 'function') {
    renderBranchOLADashboard();
  }
}
window.setOLADashboardScope = setOLADashboardScope;

function toggleOLADashboardView() {
  const body = document.getElementById('olaDashboardBody');
  const txt = document.getElementById('txtToggleOLADashboard');
  const icon = document.getElementById('iconToggleOLADashboard');
  if (!body) return;
  isOLADashboardCollapsed = !isOLADashboardCollapsed;
  if (isOLADashboardCollapsed) {
    body.style.display = 'none';
    if (txt) txt.innerText = 'Tampilkan';
    if (icon) icon.style.transform = 'rotate(180deg)';
  } else {
    body.style.display = 'block';
    if (txt) txt.innerText = 'Ciutkan';
    if (icon) icon.style.transform = 'rotate(0deg)';
  }
  try {
    localStorage.setItem('dcms_ola_dashboard_collapsed', isOLADashboardCollapsed ? '1' : '0');
  } catch (e) {}
}
window.toggleOLADashboardView = toggleOLADashboardView;

function initOLADashboardState() {
  try {
    const isCollapsed = localStorage.getItem('dcms_ola_dashboard_collapsed') === '1';
    if (isCollapsed) {
      isOLADashboardCollapsed = true;
      const body = document.getElementById('olaDashboardBody');
      const txt = document.getElementById('txtToggleOLADashboard');
      const icon = document.getElementById('iconToggleOLADashboard');
      if (body) body.style.display = 'none';
      if (txt) txt.innerText = 'Tampilkan';
      if (icon) icon.style.transform = 'rotate(180deg)';
    }
  } catch (e) {}
}

function getBranchOLASummary(sourceRows, scope = 'PERIOD') {
  if (!Array.isArray(sourceRows) || sourceRows.length === 0) {
    return {
      branches: [],
      totals: { totalKeep: 0, totalOnTime: 0, totalOverdue: 0, totalPending: 0, totalWin: 0, networkComplianceRate: 0 }
    };
  }

  let rows = sourceRows;
  if (scope === 'TODAY') {
    const today = new Date();
    rows = sourceRows.filter(r => {
      const ts = r['Timestamp'] || r['keep_timestamp'] || r.created_at;
      const d = parseKeepDateObj(ts);
      if (!d) return false;
      return d.toDateString() === today.toDateString();
    });
  }

  const branchMap = {};

  rows.forEach(r => {
    let cab = String(r['Cabang'] || '').trim();
    if (!cab && r['Grup ID'] && KEEP_GROUP_CABANG_MAP[r['Grup ID']]) {
      cab = KEEP_GROUP_CABANG_MAP[r['Grup ID']].cabang;
    }
    if (!cab || cab === '-' || cab === '""') {
      cab = 'LAINNYA';
    }
    cab = cab.toUpperCase();

    if (!branchMap[cab]) {
      branchMap[cab] = {
        name: cab,
        total: 0,
        claimed: 0,
        onTime: 0,
        overdue: 0,
        pending: 0,
        win: 0,
        lose: 0
      };
    }

    const b = branchMap[cab];
    b.total++;

    const rawSt = String(r['Status'] || '').trim().toUpperCase();
    if (rawSt === 'WIN') b.win++;
    if (rawSt === 'LOSE' || rawSt === 'TAKE OUT' || rawSt.includes('CANCEL') || rawSt.includes('BATAL')) b.lose++;

    const ola = getKeepOLAMeta(r, 15);
    if (ola.isClaimed) {
      b.claimed++;
      b.onTime++; // Lead sudah di-claim tim FU
    } else {
      b.pending++;
      if (ola.isOverdue) {
        b.overdue++;
      } else {
        b.onTime++; // Belum lewat 15 menit
      }
    }
  });

  const branchList = Object.values(branchMap).map(b => {
    const complianceRate = b.total > 0 ? Math.round((b.onTime / b.total) * 100) : 0;
    const winRate = b.total > 0 ? Math.round((b.win / b.total) * 100) : 0;
    return {
      ...b,
      complianceRate,
      winRate
    };
  });

  // Urutkan peringkat cabang:
  // 1. Kepatuhan OLA % tertinggi
  // 2. Overdue terminimal
  // 3. Total Closing WIN terbanyak
  // 4. Total Keep terbanyak
  branchList.sort((a, b) => {
    if (b.complianceRate !== a.complianceRate) return b.complianceRate - a.complianceRate;
    if (a.overdue !== b.overdue) return a.overdue - b.overdue;
    if (b.win !== a.win) return b.win - a.win;
    return b.total - a.total;
  });

  let totalKeep = 0;
  let totalOnTime = 0;
  let totalOverdue = 0;
  let totalPending = 0;
  let totalWin = 0;

  branchList.forEach(b => {
    totalKeep += b.total;
    totalOnTime += b.onTime;
    totalOverdue += b.overdue;
    totalPending += b.pending;
    totalWin += b.win;
  });

  const networkComplianceRate = totalKeep > 0 ? Math.round((totalOnTime / totalKeep) * 100) : 0;

  return {
    branches: branchList,
    totals: {
      totalKeep,
      totalOnTime,
      totalOverdue,
      totalPending,
      totalWin,
      networkComplianceRate
    }
  };
}
window.getBranchOLASummary = getBranchOLASummary;

function renderBranchOLADashboard(baseRows = null) {
  const container = document.getElementById('branchOLADashboardContainer');
  if (!container) return;

  const rows = baseRows || (typeof filteredKeepRows !== 'undefined' && filteredKeepRows.length > 0 ? filteredKeepRows : rawKeepRows);
  const data = getBranchOLASummary(rows, currentOLADashboardScope);
  const { branches, totals } = data;

  // 1. Update 4 Highlight Cards
  const topBranch = branches[0];
  const elTopName = document.getElementById('olaTopBranchName');
  const elTopMeta = document.getElementById('olaTopBranchMeta');
  if (elTopName) {
    elTopName.innerText = topBranch ? topBranch.name : '-';
  }
  if (elTopMeta) {
    elTopMeta.innerText = topBranch 
      ? `Kepatuhan SLA ${topBranch.complianceRate}% (${topBranch.onTime}/${topBranch.total} Tepat Waktu)` 
      : 'Belum ada data';
  }

  const elNetRate = document.getElementById('olaNetworkComplianceRate');
  const elNetMeta = document.getElementById('olaNetworkComplianceMeta');
  if (elNetRate) elNetRate.innerText = `${totals.networkComplianceRate}%`;
  if (elNetMeta) elNetMeta.innerText = `${totals.totalOnTime.toLocaleString('id-ID')} dari ${totals.totalKeep.toLocaleString('id-ID')} lead tepat waktu`;

  const elOverdue = document.getElementById('olaTotalOverdueCount');
  if (elOverdue) elOverdue.innerText = totals.totalOverdue.toLocaleString('id-ID');

  const elWin = document.getElementById('olaTotalClosingWin');
  const elWinMeta = document.getElementById('olaClosingRateMeta');
  if (elWin) elWin.innerText = totals.totalWin.toLocaleString('id-ID');
  if (elWinMeta) {
    const winRate = totals.totalKeep > 0 ? Math.round((totals.totalWin / totals.totalKeep) * 100) : 0;
    elWinMeta.innerText = `Closing rate: ${winRate}% (${totals.totalWin} deal)`;
  }

  // Update Tab 1 Executive Dashboard cards if present
  const dashTopBranch = document.getElementById('dashOLATopBranch');
  const dashTopMeta = document.getElementById('dashOLATopMeta');
  if (dashTopBranch) dashTopBranch.innerText = topBranch ? topBranch.name : '-';
  if (dashTopMeta) dashTopMeta.innerText = topBranch ? `Kepatuhan SLA ${topBranch.complianceRate}% (${topBranch.onTime}/${topBranch.total})` : 'Belum ada data';

  const dashNetRate = document.getElementById('dashOLANetworkRate');
  const dashNetMeta = document.getElementById('dashOLANetworkMeta');
  if (dashNetRate) dashNetRate.innerText = `${totals.networkComplianceRate}%`;
  if (dashNetMeta) dashNetMeta.innerText = `${totals.totalOnTime.toLocaleString('id-ID')} dari ${totals.totalKeep.toLocaleString('id-ID')} tepat waktu`;

  const dashOverdue = document.getElementById('dashOLAOverdueCount');
  if (dashOverdue) dashOverdue.innerText = totals.totalOverdue.toLocaleString('id-ID');

  const dashWin = document.getElementById('dashOLATotalWin');
  const dashWinMeta = document.getElementById('dashOLATotalWinMeta');
  if (dashWin) dashWin.innerText = totals.totalWin.toLocaleString('id-ID');
  if (dashWinMeta) {
    const winRate = totals.totalKeep > 0 ? Math.round((totals.totalWin / totals.totalKeep) * 100) : 0;
    dashWinMeta.innerText = `Closing rate: ${winRate}% (${totals.totalWin} deal)`;
  }

  // 2. Render Leaderboard Table Rows
  const tbody = document.getElementById('olaBranchTableBody');
  if (!tbody) return;

  if (branches.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Tidak ada data cabang untuk periode ini</td></tr>`;
    return;
  }

  let html = '';
  branches.forEach((b, idx) => {
    const rank = idx + 1;
    let rankBadge = '';
    if (rank === 1) {
      rankBadge = `<span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800 font-black text-xs shadow-2xs">🥇 1</span>`;
    } else if (rank === 2) {
      rankBadge = `<span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full bg-slate-200 text-slate-700 font-black text-xs shadow-2xs">🥈 2</span>`;
    } else if (rank === 3) {
      rankBadge = `<span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full bg-amber-700/10 text-amber-800 font-black text-xs shadow-2xs">🥉 3</span>`;
    } else {
      rankBadge = `<span class="font-bold text-slate-500 font-mono text-xs">#${rank}</span>`;
    }

    let barColor = 'bg-emerald-500';
    let textColor = 'text-emerald-700';
    if (b.complianceRate < 70) {
      barColor = 'bg-rose-500';
      textColor = 'text-rose-700';
    } else if (b.complianceRate < 85) {
      barColor = 'bg-amber-500';
      textColor = 'text-amber-700';
    }

    html += `
      <tr class="hover:bg-slate-50/80 transition cursor-pointer select-none" onclick="filterKeepByBranch('${escapeHtml(b.name)}')">
        <td class="px-3.5 py-2.5 text-center align-middle whitespace-nowrap">${rankBadge}</td>
        <td class="px-3.5 py-2.5 text-left align-middle whitespace-nowrap">
          <div class="flex items-center gap-1.5">
            <span class="inline-flex items-center gap-1 rounded-lg bg-sky-50 border border-sky-200/80 px-2 py-0.5 text-xs font-bold text-sky-800 tracking-tight shadow-2xs">
              <span class="size-1.5 rounded-full bg-sky-500"></span>
              <span>${escapeHtml(b.name)}</span>
            </span>
          </div>
        </td>
        <td class="px-3 py-2.5 text-center align-middle font-bold text-slate-800 whitespace-nowrap">${b.total.toLocaleString('id-ID')}</td>
        <td class="px-3 py-2.5 text-center align-middle whitespace-nowrap">
          <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded text-[11px]">
            <span class="text-emerald-500 font-bold">✓</span>
            ${b.onTime}
          </span>
        </td>
        <td class="px-3 py-2.5 text-center align-middle whitespace-nowrap">
          ${b.overdue > 0 
            ? `<span class="inline-flex items-center gap-1 font-bold text-rose-700 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded text-[11px] shadow-2xs animate-pulse"><span class="size-1.5 rounded-full bg-rose-500"></span>${b.overdue}</span>`
            : `<span class="text-slate-400 font-mono text-[11px]">0</span>`}
        </td>
        <td class="px-3 py-2.5 text-center align-middle font-medium text-slate-600 whitespace-nowrap">${b.pending}</td>
        <td class="px-3.5 py-2.5 text-left align-middle min-w-[140px]">
          <div class="flex items-center gap-2">
            <div class="w-20 bg-slate-100 rounded-full h-2 overflow-hidden shrink-0">
              <div class="${barColor} h-2 rounded-full transition-all duration-300" style="width: ${b.complianceRate}%;"></div>
            </div>
            <span class="inline-flex items-center text-[11px] font-bold ${textColor}">
              ${b.complianceRate}%
            </span>
          </div>
        </td>
        <td class="px-3.5 py-2.5 text-center align-middle whitespace-nowrap">
          <button type="button" class="inline-flex items-center justify-center size-7 rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-brand-600 hover:border-brand-300 transition shadow-2xs cursor-pointer" onclick="event.stopPropagation(); filterKeepByBranch('${escapeHtml(b.name)}')" title="Filter tabel untuk cabang ${escapeHtml(b.name)}">
            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
          </button>
        </td>
      </tr>
    `;
  });

  tbody.innerHTML = html;
}
window.renderBranchOLADashboard = renderBranchOLADashboard;

function filterKeepByBranch(branchName) {
  if (!branchName) return;
  const searchInput = document.getElementById('keepSearchInput');
  if (!searchInput) return;
  const cleanBranch = branchName.trim();
  if (searchInput.value.trim().toLowerCase() === cleanBranch.toLowerCase()) {
    searchInput.value = '';
  } else {
    searchInput.value = cleanBranch;
  }
  if (typeof toggleKeepClearBtn === 'function') toggleKeepClearBtn();
  if (typeof applyKeepFilters === 'function') applyKeepFilters();
  
  const table = document.getElementById('keepTable');
  if (table) {
    table.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}
window.filterKeepByBranch = filterKeepByBranch;

// =========================================================================
// TAB 10: DEDICATED DASHBOARD TOP PERFORMANCE OLA / SLA CABANG
// =========================================================================
let tab10OlaChartInstance = null;
let tab10DonutChartInstance = null;
let currentTab10Scope = 'PERIOD';

function setTab10Scope(scope) {
  currentTab10Scope = scope;
  const btnPeriod = document.getElementById('tab10BtnScopePeriod');
  const btnToday = document.getElementById('tab10BtnScopeToday');
  if (btnPeriod && btnToday) {
    if (scope === 'TODAY') {
      btnToday.className = 'rounded-lg px-3 py-1.5 text-xs font-bold transition bg-white text-brand-700 shadow-xs cursor-pointer';
      btnPeriod.className = 'rounded-lg px-3 py-1.5 text-xs font-semibold transition text-slate-500 hover:text-slate-800 cursor-pointer';
    } else {
      btnPeriod.className = 'rounded-lg px-3 py-1.5 text-xs font-bold transition bg-white text-brand-700 shadow-xs cursor-pointer';
      btnToday.className = 'rounded-lg px-3 py-1.5 text-xs font-semibold transition text-slate-500 hover:text-slate-800 cursor-pointer';
    }
  }
  renderTab10OLADashboard();
}
window.setTab10Scope = setTab10Scope;

function refreshTab10Data() {
  if (typeof syncLiveKeepData === 'function') {
    syncLiveKeepData(() => {
      renderTab10OLADashboard();
    });
  } else {
    loadKeepData(() => {
      renderTab10OLADashboard();
    });
  }
}
window.refreshTab10Data = refreshTab10Data;

function jumpToKeepWithBranchFilter(branchName) {
  if (typeof switchTab === 'function') switchTab(4);
  setTimeout(() => {
    if (typeof filterKeepByBranch === 'function') filterKeepByBranch(branchName);
  }, 100);
}
window.jumpToKeepWithBranchFilter = jumpToKeepWithBranchFilter;

function renderTab10OLADashboard() {
  const container = document.getElementById('tabContent10');
  if (!container) return;

  if (!rawKeepRows || rawKeepRows.length === 0) {
    if (typeof loadKeepData === 'function') {
      loadKeepData(() => {
        renderTab10OLADashboard();
      });
    }
    return;
  }

  const periodSelect = document.getElementById('tab10FilterPeriode');
  const periodVal = periodSelect ? periodSelect.value : 'November-26';

  let sourceRows = rawKeepRows;
  if (periodVal !== 'ALL') {
    sourceRows = rawKeepRows.filter(r => {
      const p = String(r['PERIODE'] || '').trim().toLowerCase();
      return p === periodVal.toLowerCase();
    });
  }

  const { branches, totals } = getBranchOLASummary(sourceRows, currentTab10Scope);

  // 0. Update 7 Banner KPI Cards (Total Keep, Unassign, On Progress, Need to Check, Closing WIN, Lose, Closing Rate)
  let tab10Win = 0;
  let tab10Progress = 0;
  let tab10NeedToCheck = 0;
  let tab10Lose = 0;
  let tab10Unassign = 0;

  sourceRows.forEach(r => {
    const st = String(r['Status'] || '').toUpperCase();
    if (isKeepUnassigned(r)) tab10Unassign++;
    if (st === 'WIN') tab10Win++;
    else if (st === 'NEED TO CHECK') tab10NeedToCheck++;
    else if (st.includes('PROGRESS')) tab10Progress++;
    else if (st === 'LOSE' || st === 'TAKE OUT' || st.includes('CANCEL') || st.includes('BATAL')) tab10Lose++;
  });

  const tab10Total = sourceRows.length;
  const tab10ClosingRate = tab10Total > 0 ? Math.round((tab10Win / tab10Total) * 100) : 0;

  const elKpiTotal = document.getElementById('tab10KpiTotalKeep');
  const elKpiUnassign = document.getElementById('tab10KpiUnassign');
  const elKpiProgress = document.getElementById('tab10KpiProgress');
  const elKpiNeedToCheck = document.getElementById('tab10KpiNeedToCheck');
  const elKpiWin = document.getElementById('tab10KpiWin');
  const elKpiLose = document.getElementById('tab10KpiLose');
  const elKpiRate = document.getElementById('tab10KpiClosingRate');

  if (elKpiTotal) elKpiTotal.innerText = tab10Total.toLocaleString('id-ID');
  if (elKpiUnassign) elKpiUnassign.innerText = tab10Unassign.toLocaleString('id-ID');
  if (elKpiProgress) elKpiProgress.innerText = tab10Progress.toLocaleString('id-ID');
  if (elKpiNeedToCheck) elKpiNeedToCheck.innerText = tab10NeedToCheck.toLocaleString('id-ID');
  if (elKpiWin) elKpiWin.innerText = tab10Win.toLocaleString('id-ID');
  if (elKpiLose) elKpiLose.innerText = tab10Lose.toLocaleString('id-ID');
  if (elKpiRate) elKpiRate.innerText = `${tab10ClosingRate}%`;

  // 1. Update 4 Metric KPI Cards OLA
  const topBranch = branches[0];
  const elTopName = document.getElementById('tab10TopBranchName');
  const elTopMeta = document.getElementById('tab10TopBranchMeta');
  if (elTopName) elTopName.innerText = topBranch ? topBranch.name : '-';
  if (elTopMeta) elTopMeta.innerText = topBranch ? `Kepatuhan SLA ${topBranch.complianceRate}% (${topBranch.onTime}/${topBranch.total} Tepat Waktu)` : 'Belum ada data';

  const elNetRate = document.getElementById('tab10NetworkRate');
  const elNetMeta = document.getElementById('tab10NetworkMeta');
  if (elNetRate) elNetRate.innerText = `${totals.networkComplianceRate}%`;
  if (elNetMeta) elNetMeta.innerText = `${totals.totalOnTime.toLocaleString('id-ID')} dari ${totals.totalKeep.toLocaleString('id-ID')} lead tepat waktu (≤15m)`;

  const elOverdue = document.getElementById('tab10OverdueCount');
  if (elOverdue) elOverdue.innerText = totals.totalOverdue.toLocaleString('id-ID');

  const elTotalWin = document.getElementById('tab10TotalWin');
  const elWinMeta = document.getElementById('tab10WinMeta');
  if (elTotalWin) elTotalWin.innerText = totals.totalWin.toLocaleString('id-ID');
  if (elWinMeta) {
    const winRate = totals.totalKeep > 0 ? Math.round((totals.totalWin / totals.totalKeep) * 100) : 0;
    elWinMeta.innerText = `Closing rate: ${winRate}% (${totals.totalWin} transaksi deal)`;
  }

  // 2. Render Leaderboard Matrix Table
  const tbody = document.getElementById('tab10LeaderboardBody');
  if (tbody) {
    if (branches.length === 0) {
      tbody.innerHTML = `<tr><td colspan="9" class="px-4 py-8 text-center text-slate-400">Tidak ada data cabang untuk filter yang dipilih.</td></tr>`;
    } else {
      let tbodyHtml = '';
      branches.forEach((b, idx) => {
        const rank = idx + 1;
        let rankBadge = '';
        if (rank === 1) {
          rankBadge = `<span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-black text-xs shadow-2xs">🥇 1</span>`;
        } else if (rank === 2) {
          rankBadge = `<span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-black text-xs shadow-2xs">🥈 2</span>`;
        } else if (rank === 3) {
          rankBadge = `<span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-amber-700/10 text-amber-800 font-black text-xs shadow-2xs">🥉 3</span>`;
        } else {
          rankBadge = `<span class="font-bold text-slate-500 font-mono text-xs">#${rank}</span>`;
        }

        let barColor = 'bg-emerald-500';
        let textColor = 'text-emerald-700';
        if (b.complianceRate < 70) {
          barColor = 'bg-rose-500';
          textColor = 'text-rose-700';
        } else if (b.complianceRate < 85) {
          barColor = 'bg-amber-500';
          textColor = 'text-amber-700';
        }

        tbodyHtml += `
          <tr class="hover:bg-slate-50/80 transition cursor-pointer select-none" onclick="jumpToKeepWithBranchFilter('${escapeHtml(b.name)}')">
            <td class="px-4 py-3 text-center align-middle whitespace-nowrap">${rankBadge}</td>
            <td class="px-4 py-3 text-left align-middle whitespace-nowrap">
              <span class="inline-flex items-center gap-1 rounded-lg bg-sky-50 border border-sky-200/80 px-2.5 py-1 text-xs font-bold text-sky-800 tracking-tight shadow-2xs">
                <span class="size-1.5 rounded-full bg-sky-500"></span>
                <span>${escapeHtml(b.name)}</span>
              </span>
            </td>
            <td class="px-3 py-3 text-center align-middle font-bold text-slate-800 whitespace-nowrap">${b.total.toLocaleString('id-ID')}</td>
            <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
              <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-xs">
                <span class="text-emerald-500 font-bold">✓</span>
                ${b.onTime.toLocaleString('id-ID')}
              </span>
            </td>
            <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
              ${b.overdue > 0 
                ? `<span class="inline-flex items-center gap-1 font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded text-xs shadow-2xs animate-pulse"><span class="size-1.5 rounded-full bg-rose-500"></span>${b.overdue}</span>`
                : `<span class="text-slate-400 font-mono text-xs">0</span>`}
            </td>
            <td class="px-3 py-3 text-center align-middle font-semibold text-slate-600 whitespace-nowrap">${b.pending.toLocaleString('id-ID')}</td>
            <td class="px-4 py-3 text-left align-middle min-w-[160px]">
              <div class="flex items-center gap-2">
                <div class="w-24 bg-slate-100 rounded-full h-2.5 overflow-hidden shrink-0">
                  <div class="${barColor} h-2.5 rounded-full transition-all duration-300" style="width: ${b.complianceRate}%;"></div>
                </div>
                <span class="inline-flex items-center text-xs font-bold ${textColor}">
                  ${b.complianceRate}%
                </span>
              </div>
            </td>
            <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
              <div class="text-xs font-bold text-slate-900">${b.win} Deal</div>
              <div class="text-[10px] text-slate-400">${b.winRate}% Closing</div>
            </td>
            <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
              <button type="button" class="inline-flex items-center justify-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-1 text-[11px] font-bold text-slate-600 hover:border-brand-500 hover:text-brand-600 hover:bg-brand-50 transition shadow-2xs cursor-pointer" onclick="event.stopPropagation(); jumpToKeepWithBranchFilter('${escapeHtml(b.name)}')" title="Buka data keep untuk cabang ${escapeHtml(b.name)}">
                <span>Cek</span>
                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
              </button>
            </td>
          </tr>
        `;
      });
      tbody.innerHTML = tbodyHtml;
    }
  }

  // 3. Render Chart.js Bar Chart (matching user reference screenshot!)
  renderTab10BarChart(branches);

  // 4. Render Chart.js Donut Chart
  renderTab10DonutChart(totals);
}
window.renderTab10OLADashboard = renderTab10OLADashboard;

function renderTab10BarChart(branches) {
  const canvas = document.getElementById('tab10OlaChartCanvas');
  if (!canvas || typeof Chart === 'undefined') return;

  if (tab10OlaChartInstance) {
    try { tab10OlaChartInstance.destroy(); } catch (e) {}
    tab10OlaChartInstance = null;
  }

  const topBranches = branches.slice(0, 12);
  const labels = topBranches.map(b => b.name);
  const onTimeData = topBranches.map(b => b.onTime);
  const overdueData = topBranches.map(b => b.overdue);
  const targetLineData = topBranches.map(b => Math.round(b.total * 0.9));

  const ctx = canvas.getContext('2d');
  tab10OlaChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          type: 'line',
          label: 'Target SLA 90%',
          data: targetLineData,
          borderColor: '#1e293b',
          borderWidth: 2,
          borderDash: [5, 5],
          pointBackgroundColor: '#1e293b',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 1.5,
          pointRadius: 4,
          pointHoverRadius: 6,
          fill: false,
          tension: 0.1,
          order: 1
        },
        {
          type: 'bar',
          label: 'Tepat Waktu (≤15m)',
          data: onTimeData,
          backgroundColor: '#c9182b',
          hoverBackgroundColor: '#a51122',
          borderRadius: 6,
          borderSkipped: false,
          barPercentage: 0.65,
          categoryPercentage: 0.8,
          order: 2
        },
        {
          type: 'bar',
          label: 'Overdue (>15m)',
          data: overdueData,
          backgroundColor: '#94a3b8',
          hoverBackgroundColor: '#64748b',
          borderRadius: 6,
          borderSkipped: false,
          barPercentage: 0.65,
          categoryPercentage: 0.8,
          order: 3
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false
      },
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: '#1e293b',
          titleColor: '#ffffff',
          titleFont: { weight: 'bold', size: 12 },
          bodyColor: '#e2e8f0',
          bodyFont: { size: 11 },
          padding: 12,
          cornerRadius: 10,
          boxPadding: 4,
          callbacks: {
            title: function(items) {
              return items.length > 0 ? items[0].label : '';
            },
            afterBody: function(items) {
              if (items.length > 0) {
                const idx = items[0].dataIndex;
                const b = topBranches[idx];
                if (b) {
                  return `Kepatuhan OLA: ${b.complianceRate}%\nTotal Keep: ${b.total} Lead`;
                }
              }
              return '';
            }
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            font: { size: 10, weight: '600' },
            color: '#64748b',
            maxRotation: 35,
            minRotation: 0
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#f1f5f9',
            drawBorder: false
          },
          ticks: {
            font: { size: 10 },
            color: '#94a3b8',
            precision: 0
          }
        }
      }
    }
  });
}

function renderTab10DonutChart(totals) {
  const canvas = document.getElementById('tab10DonutChartCanvas');
  if (!canvas || typeof Chart === 'undefined') return;

  if (tab10DonutChartInstance) {
    try { tab10DonutChartInstance.destroy(); } catch (e) {}
    tab10DonutChartInstance = null;
  }

  const elOnTime = document.getElementById('tab10DonutStatOnTime');
  const elOverdue = document.getElementById('tab10DonutStatOverdue');
  const elPending = document.getElementById('tab10DonutStatPending');
  if (elOnTime) elOnTime.innerText = totals.totalOnTime.toLocaleString('id-ID');
  if (elOverdue) elOverdue.innerText = totals.totalOverdue.toLocaleString('id-ID');
  if (elPending) elPending.innerText = totals.totalPending.toLocaleString('id-ID');

  const ctx = canvas.getContext('2d');
  tab10DonutChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Tepat Waktu (≤15m)', 'Overdue (>15m)', 'Pending Belum Claim'],
      datasets: [
        {
          data: [totals.totalOnTime, totals.totalOverdue, totals.totalPending],
          backgroundColor: ['#10b981', '#f43f5e', '#cbd5e1'],
          borderWidth: 2,
          borderColor: '#ffffff',
          hoverOffset: 4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: '#1e293b',
          titleColor: '#ffffff',
          titleFont: { weight: 'bold' },
          padding: 10,
          cornerRadius: 8
        }
      },
      cutout: '72%'
    }
  });
}

function formatPickupDate(val) {
  if (!val || val === '-') return '-';
  const lower = String(val).toLowerCase().trim();
  if (lower.includes('hari ini')) {
    return `<span class="text-crimson font-bold">Hari ini</span>`;
  }
  return val;
}

function getModernStatusPill(st) {
  const norm = String(st || '').trim().toUpperCase();
  if (norm === 'WIN') {
    return `<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200/80 shadow-xs"><span class="size-1.5 rounded-full bg-emerald-500"></span>Win</span>`;
  }
  if (norm === 'ON PROGRESS LOW' || norm === 'LOW') {
    return `<span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-sky-700 border border-sky-200/80 shadow-xs"><span class="size-1.5 rounded-full bg-sky-500"></span>Low</span>`;
  }
  if (norm.includes('PROGRESS')) {
    return `<span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 border border-amber-200/80 shadow-xs"><span class="size-1.5 rounded-full bg-amber-500"></span>On Progress</span>`;
  }
  if (norm === 'LOSE' || norm === 'TAKE OUT') {
    return `<span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 border border-rose-200/80 shadow-xs"><span class="size-1.5 rounded-full bg-rose-500"></span>Lose</span>`;
  }
  return `<span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 border border-slate-200/80 shadow-xs"><span class="size-1.5 rounded-full bg-slate-400"></span>${st || '-'}</span>`;
}

let currentKeepStatusContext = null;
function openStatusChangeQuickModal(rawWA, keepRowIdx, currentStatus) {
  const row = filteredKeepRows[keepRowIdx];
  currentKeepStatusContext = { rawWA, keepRowIdx, currentStatus, row };
  const modal = document.getElementById('keepStatusModal');
  if (!modal) return;
  const nameEl = document.getElementById('keepStatusModalCustName');
  if (nameEl && row) {
    nameEl.innerText = row['Nama'] ? `Customer: ${row['Nama']}` : '-';
  }
  modal.style.display = 'flex';
}

function closeKeepStatusModal() {
  const modal = document.getElementById('keepStatusModal');
  if (modal) modal.style.display = 'none';
  currentKeepStatusContext = null;
}

function selectQuickKeepStatus(newStatus) {
  if (!currentKeepStatusContext) return;
  const { rawWA, keepRowIdx, currentStatus, row } = currentKeepStatusContext;
  const cleanSt = String(newStatus).trim().toUpperCase();

  if (cleanSt !== currentStatus) {
    if (filteredKeepRows[keepRowIdx]) {
      const oldSt = filteredKeepRows[keepRowIdx]['Status'];
      const keepId = row ? row.id : null;
      filteredKeepRows[keepRowIdx]['Status'] = cleanSt;
      renderKeepPage();
      triggerBackgroundKeepStatusSync(rawWA, cleanSt, oldSt, () => {
        filteredKeepRows[keepRowIdx]['Status'] = oldSt;
        renderKeepPage();
      }, keepId);
    }
  }
  closeKeepStatusModal();
}
window.openStatusChangeQuickModal = openStatusChangeQuickModal;
window.closeKeepStatusModal = closeKeepStatusModal;
window.selectQuickKeepStatus = selectQuickKeepStatus;

let currentManualAssignContext = null;

function openManualAssignFUModal(keepRowIdx) {
  const row = filteredKeepRows[keepRowIdx];
  if (!row) return;

  currentManualAssignContext = { keepRowIdx, row };
  const modal = document.getElementById('assignManualFUModal');
  if (!modal) return;

  const custNameEl = document.getElementById('assignManualCustName');
  const custWAEl = document.getElementById('assignManualCustWA');
  const barangEl = document.getElementById('assignManualBarang');
  const cabangEl = document.getElementById('assignManualCabang');
  const nameInput = document.getElementById('assignManualFUName');
  const phoneInput = document.getElementById('assignManualFUPhone');
  const selectPicEl = document.getElementById('assignManualSelectPic');

  const custName = row['Nama'] || row['Customer'] || '-';
  const rawWA = row['Nomor WhatsApp'] || row['nomor_wa'] || row['WA'] || '-';
  const barang = row['Barang'] || row['Barang Keep'] || row['barang'] || '-';
  const cabang = String(row['Cabang'] || (row['Grup ID'] && KEEP_GROUP_CABANG_MAP[row['Grup ID']] ? KEEP_GROUP_CABANG_MAP[row['Grup ID']].cabang : '') || '-').trim();

  if (custNameEl) custNameEl.innerText = custName;
  if (custWAEl) custWAEl.innerText = rawWA;
  if (barangEl) barangEl.innerText = barang;
  if (cabangEl) cabangEl.innerText = cabang || '-';

  const curFUName = String(row['Nama Tim Follow Up'] || row['nama_tim_follow_up'] || row['fu_name'] || '').trim();
  const curFUPhone = String(row['Nomor Follow Up'] || row['nomor_follow_up'] || row['Nomor FU'] || row['nomor_fu'] || '').trim();

  if (nameInput) nameInput.value = curFUName;
  if (phoneInput) phoneInput.value = curFUPhone;

  // Populate dropdown master PIC
  if (selectPicEl) {
    let picOptions = '<option value="">-- Ketik Manual atau Pilih Master PIC --</option>';
    if (typeof getMasterDataPICs === 'function') {
      const pics = getMasterDataPICs();
      if (Array.isArray(pics) && pics.length > 0) {
        // Kelompokkan PIC cabang yang sama lebih dahulu
        const sameBranch = [];
        const otherBranch = [];

        pics.forEach(p => {
          if (!p.picName) return;
          const isMatchCabang = cabang && p.cabang && (cabang.toLowerCase().includes(p.cabang.toLowerCase()) || p.cabang.toLowerCase().includes(cabang.toLowerCase()));
          if (isMatchCabang) sameBranch.push(p);
          else otherBranch.push(p);
        });

        if (sameBranch.length > 0) {
          picOptions += `<optgroup label="PIC Cabang ${escapeHtml(cabang || 'Ini')}">`;
          sameBranch.forEach(p => {
            const val = JSON.stringify({ name: p.picName, phone: p.wa || '' }).replace(/"/g, '&quot;');
            picOptions += `<option value="${val}">★ ${escapeHtml(p.picName)} (${escapeHtml(p.wa || '-')})</option>`;
          });
          picOptions += '</optgroup>';
        }

        if (otherBranch.length > 0) {
          picOptions += '<optgroup label="PIC Cabang Lain / Semua">';
          otherBranch.forEach(p => {
            const val = JSON.stringify({ name: p.picName, phone: p.wa || '' }).replace(/"/g, '&quot;');
            picOptions += `<option value="${val}">${escapeHtml(p.picName)} - ${escapeHtml(p.cabang || '-')} (${escapeHtml(p.wa || '-')})</option>`;
          });
          picOptions += '</optgroup>';
        }
      }
    }
    selectPicEl.innerHTML = picOptions;
    selectPicEl.value = '';
  }

  modal.style.display = 'flex';
  setTimeout(() => {
    if (nameInput) nameInput.focus();
  }, 50);
}

function closeAssignManualFUModal() {
  const modal = document.getElementById('assignManualFUModal');
  if (modal) modal.style.display = 'none';
  currentManualAssignContext = null;
}

function onSelectMasterPicChanged(val) {
  if (!val) return;
  try {
    const data = JSON.parse(val);
    const nameInput = document.getElementById('assignManualFUName');
    const phoneInput = document.getElementById('assignManualFUPhone');
    if (nameInput && data.name) nameInput.value = data.name;
    if (phoneInput && data.phone) phoneInput.value = data.phone;
  } catch (e) {}
}

async function submitAssignManualFU(event) {
  if (event) event.preventDefault();
  if (!currentManualAssignContext) return;

  const { keepRowIdx, row } = currentManualAssignContext;
  const nameInput = document.getElementById('assignManualFUName');
  const phoneInput = document.getElementById('assignManualFUPhone');

  const fuName = (nameInput ? nameInput.value : '').trim();
  let fuPhone = (phoneInput ? phoneInput.value : '').trim();

  if (!fuName) {
    alert('Silakan masukkan Nama Tim Follow Up.');
    if (nameInput) nameInput.focus();
    return;
  }
  if (!fuPhone) {
    alert('Silakan masukkan Nomor WhatsApp/HP Tim Follow Up.');
    if (phoneInput) phoneInput.focus();
    return;
  }

  // Normalisasi nomor telepon
  let cleanPhone = fuPhone.replace(/\D/g, '');
  if (cleanPhone.startsWith('08')) cleanPhone = '628' + cleanPhone.slice(2);
  else if (cleanPhone.startsWith('8')) cleanPhone = '628' + cleanPhone.slice(1);

  // 1. OPTIMISTIC UPDATE LANGSUNG DI TABEL (0ms)
  row['Nama Tim Follow Up'] = fuName;
  row['nama_tim_follow_up'] = fuName;
  row['fu_name'] = fuName;
  row['Nomor Follow Up'] = cleanPhone;
  row['nomor_follow_up'] = cleanPhone;
  row['Nomor FU'] = cleanPhone;
  row['nomor_fu'] = cleanPhone;

  const currentSt = String(row['Status'] || '').trim().toUpperCase();
  if (!currentSt || currentSt === 'PENDING' || currentSt === 'BELUM DIINPUT' || currentSt === 'NEW') {
    row['Status'] = 'ON PROGRESS';
  }

  if (filteredKeepRows[keepRowIdx]) {
    filteredKeepRows[keepRowIdx]['Nama Tim Follow Up'] = fuName;
    filteredKeepRows[keepRowIdx]['nama_tim_follow_up'] = fuName;
    filteredKeepRows[keepRowIdx]['fu_name'] = fuName;
    filteredKeepRows[keepRowIdx]['Nomor Follow Up'] = cleanPhone;
    filteredKeepRows[keepRowIdx]['nomor_follow_up'] = cleanPhone;
    filteredKeepRows[keepRowIdx]['Nomor FU'] = cleanPhone;
    filteredKeepRows[keepRowIdx]['nomor_fu'] = cleanPhone;
    if (!currentSt || currentSt === 'PENDING' || currentSt === 'BELUM DIINPUT' || currentSt === 'NEW') {
      filteredKeepRows[keepRowIdx]['Status'] = 'ON PROGRESS';
    }
  }

  renderKeepPage();
  closeAssignManualFUModal();
  if (typeof showSaveToast === 'function') {
    showSaveToast(`✓ Tim Follow Up berhasil di-assign ke ${fuName}!`);
  }

  // 2. SINKRONISASI KE DATABASE SQL DCMS
  try {
    const rawWA = row['Nomor WhatsApp'] || row['nomor_wa'] || '';
    const normCustWA = String(rawWA).replace(/\D/g, '');
    const keepId = row.id || null;

    const res = await fetch('/api/assign-keep-fu', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: keepId,
        nomorWA: normCustWA,
        nama_fu: fuName,
        nomor_fu: cleanPhone
      })
    });

    if (!res.ok) {
      console.warn('Backend assign-keep-fu non-200, mencoba webhook fonnte...');
      await fetch('/api/fonnte-webhook', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'UPDATE_REACTION_FU',
          id: keepId,
          nomorWA: normCustWA,
          nomor_fu: cleanPhone,
          nama_sales: fuName
        })
      });
    }
  } catch (err) {
    console.error('Gagal simpan assign manual ke backend:', err);
  }
}

window.openManualAssignFUModal = openManualAssignFUModal;
window.closeAssignManualFUModal = closeAssignManualFUModal;
window.onSelectMasterPicChanged = onSelectMasterPicChanged;
window.submitAssignManualFU = submitAssignManualFU;

let currentDeleteKeepContext = null;

function canDeleteKeepFormat() {
  if (typeof window.CAN_DELETE_KEEP !== 'undefined') {
    return Boolean(window.CAN_DELETE_KEEP);
  }
  return Boolean(window.IS_SUPERADMIN && !window.IS_GUEST);
}

function isSuperadminDeleteKeepEnabled() {
  return canDeleteKeepFormat();
}

async function toggleSuperadminKeepDelete(checked) {
  // Backward compatibility stub jika dipanggil dari luar
  window.CAN_DELETE_KEEP = Boolean(checked);
  renderKeepPage();
}

function confirmDeleteKeepRow(keepRowIdx) {
  if (!canDeleteKeepFormat()) {
    alert('Akses ditolak: Anda tidak memiliki hak akses untuk menghapus data format keep.');
    return;
  }

  const row = filteredKeepRows[keepRowIdx];
  if (!row) return;

  currentDeleteKeepContext = { keepRowIdx, row };
  const modal = document.getElementById('deleteKeepConfirmModal');
  if (!modal) return;

  const nameEl = document.getElementById('delModalCustName');
  const waEl = document.getElementById('delModalCustWA');
  const barangEl = document.getElementById('delModalBarang');
  const cabangEl = document.getElementById('delModalCabang');

  if (nameEl) nameEl.innerText = row['Nama'] || row['Customer'] || '-';
  if (waEl) waEl.innerText = row['Nomor WhatsApp'] || row['nomor_wa'] || row['WA'] || '-';
  if (barangEl) barangEl.innerText = row['Barang'] || row['Barang Keep'] || row['barang'] || '-';
  if (cabangEl) cabangEl.innerText = row['Cabang'] || '-';

  modal.style.display = 'flex';
}

function closeDeleteKeepModal() {
  const modal = document.getElementById('deleteKeepConfirmModal');
  if (modal) modal.style.display = 'none';
  currentDeleteKeepContext = null;
}

async function executeDeleteKeepRow() {
  if (!currentDeleteKeepContext) return;
  const { keepRowIdx, row } = currentDeleteKeepContext;
  const btn = document.getElementById('btnConfirmDeleteKeep');
  if (btn) btn.disabled = true;

  const keepId = row.id || null;
  const rawWA = row['Nomor WhatsApp'] || row['nomor_wa'] || '';
  const normWA = String(rawWA).replace(/\D/g, '');

  // 1. Optimistic removal di tabel (0ms)
  filteredKeepRows.splice(keepRowIdx, 1);
  if (Array.isArray(rawKeepRows)) {
    const rawIdx = rawKeepRows.findIndex(r => (keepId && r.id === keepId) || (normWA && String(r['Nomor WA'] || r.nomor_wa).replace(/\D/g, '') === normWA));
    if (rawIdx !== -1) rawKeepRows.splice(rawIdx, 1);
  }

  closeDeleteKeepModal();
  renderKeepPage();
  if (typeof applyKeepFilters === 'function') applyKeepFilters();
  if (typeof showSaveToast === 'function') {
    showSaveToast('✓ Data format keep berhasil dihapus!');
  }

  // 2. Hapus di database SQL DCMS
  try {
    const res = await fetch('/api/delete-keep', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ id: keepId, nomorWA: normWA })
    });

    if (!res.ok) {
      console.warn('Backend delete-keep failed, trying fallback webhook...');
      await fetch('/api/fonnte-webhook', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'DELETE_KEEP', id: keepId, nomorWA: normWA })
      });
    }
  } catch (err) {
    console.error('Gagal hapus data keep di backend:', err);
  } finally {
    if (btn) btn.disabled = false;
  }
}

window.isSuperadminDeleteKeepEnabled = isSuperadminDeleteKeepEnabled;
window.toggleSuperadminKeepDelete = toggleSuperadminKeepDelete;
window.confirmDeleteKeepRow = confirmDeleteKeepRow;
window.closeDeleteKeepModal = closeDeleteKeepModal;
window.executeDeleteKeepRow = executeDeleteKeepRow;

function renderKeepPage() {
  const tbody = document.getElementById('keepTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  const total = filteredKeepRows.length;
  const maxPage = Math.max(1, Math.ceil(total / KEEP_PAGE_SIZE));
  const startIdx = (keepCurrentPage - 1) * KEEP_PAGE_SIZE;
  const endIdx = Math.min(startIdx + KEEP_PAGE_SIZE, total);
  const pageRows = filteredKeepRows.slice(startIdx, endIdx);

  // Update hero subtitle and pagination info
  const heroSub = document.getElementById('customerHeroSub');
  if (heroSub) {
    heroSub.innerText = `${total.toLocaleString('id-ID')} Data Realtime`;
  }

  const paginationInfoEl = document.getElementById('keepPaginationInfo');
  if (paginationInfoEl) {
    paginationInfoEl.innerText = total > 0 
      ? `Menampilkan ${startIdx + 1} - ${endIdx} dari total ${total.toLocaleString('id-ID')} Data` 
      : 'Tidak ada data customer yang sesuai filter';
  }
  const curPageTextEl = document.getElementById('keepCurrentPageText');
  if (curPageTextEl) curPageTextEl.innerText = `Halaman ${keepCurrentPage} / ${maxPage}`;
  const btnPrev = document.getElementById('btnKeepPrev');
  if (btnPrev) btnPrev.disabled = keepCurrentPage <= 1;
  const btnNext = document.getElementById('btnKeepNext');
  if (btnNext) btnNext.disabled = keepCurrentPage >= maxPage;

  if (pageRows.length === 0) {
    tbody.innerHTML = `<tr><td colspan="13" class="px-6 py-12 text-center text-xs font-medium text-slate-400 bg-white">Tidak ada data customer untuk filter yang dipilih.</td></tr>`;
    return;
  }

  pageRows.forEach((r, i) => {
    const rawWA = String(r['Nomor WA'] || '').replace(/\D/g, '');
    const normWA = normalizePhone(rawWA);
    const displayWA = formatPhoneDisplay(rawWA);
    const custName = r['Nama'] || '-';
    const initials = getCustomerInitials(custName);
    const keepRowIdx = startIdx + i;
    const olaMeta = getKeepOLAMeta(r);

    // 1. CUSTOMER CELL: Avatar Circle + Name Bold + Phone Subtext (Dengan tombol hapus jika memiliki izin)
    const canDeleteKeep = canDeleteKeepFormat();
    const deleteBtnHtml = canDeleteKeep ? `
      <button type="button" class="size-7 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white border border-rose-200/90 flex items-center justify-center shrink-0 transition cursor-pointer shadow-2xs group" onclick="confirmDeleteKeepRow(${keepRowIdx})" title="Hapus format keep ini">
        <svg class="size-3.5 text-rose-500 group-hover:text-white transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
      </button>
    ` : '';

    const custHtml = `
      <div class="flex items-center gap-2">
        ${deleteBtnHtml}
        <div class="size-9 rounded-xl bg-brand-500/10 text-brand-700 border border-brand-500/20 font-black text-xs flex items-center justify-center shrink-0 tracking-wider shadow-xs" title="${custName}">
          ${initials}
        </div>
        <div class="min-w-0">
          <div class="font-bold text-slate-900 text-xs truncate max-w-[160px]" title="${custName}">${custName}</div>
          ${(normWA && normWA.length >= 8 && !window.IS_GUEST) ? `
            <a href="https://wa.me/${normWA}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 transition" title="Hubungi customer di WhatsApp">
              <span class="size-1.5 rounded-full bg-emerald-500"></span>
              <span>${displayWA}</span>
            </a>
          ` : `<span class="text-[11px] text-slate-500 font-mono">${displayWA || '-'}</span>`}
        </div>
      </div>
    `;

    // 2. REPORT AR CELL
    const arDoc = String(r['REPORT AR'] || '').trim();
    const arHtml = arDoc ? `
      <div class="inline-flex items-center gap-1.5 bg-brand-50 border border-brand-200 rounded-lg px-2.5 py-1" data-wa="${rawWA}" data-keep-idx="${keepRowIdx}" data-current-ar="${arDoc}">
        <span class="font-mono text-xs font-bold text-brand-700 cursor-pointer hover:underline" onclick="openARDetailModalFromKeep(${keepRowIdx})" title="Klik untuk cek detail faktur AR">
          ${arDoc}
        </span>
        ${!window.IS_GUEST ? `
        <button type="button" class="text-slate-400 hover:text-brand-600 transition cursor-pointer" onclick="event.stopPropagation(); openKeepInputARModal(${keepRowIdx})" title="Ubah nomor AR">
          <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
        </button>
        ` : ''}
      </div>
    ` : `
      <div class="inline-block" data-wa="${rawWA}" data-keep-idx="${keepRowIdx}" data-current-ar="">
        ${!window.IS_GUEST ? `
        <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-dashed border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-500 hover:border-brand-500 hover:text-brand-600 hover:bg-brand-50/50 transition cursor-pointer" onclick="openKeepInputARModal(${keepRowIdx})" title="Klik untuk menginput nomor AR">
          <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> Input AR
        </button>
        ` : '<span class="text-xs text-slate-400">-</span>'}
      </div>
    `;

    // 3. STATUS PILL
    const rawSt = String(r['Status'] || '').trim().toUpperCase();
    const statusPillHtml = `
      <div class="inline-block ${window.IS_GUEST ? 'cursor-default' : 'cursor-pointer transition hover:scale-105'}" ${window.IS_GUEST ? '' : `onclick="openStatusChangeQuickModal('${rawWA}', ${keepRowIdx}, '${rawSt}')"`} title="${window.IS_GUEST ? 'Status transaksi' : 'Klik untuk mengubah status customer'}">
        ${getModernStatusPill(rawSt)}
      </div>
    `;

    // 4. CABANG BADGE (Resolusi dari field Cabang atau fallback dari Grup WhatsApp)
    const rawCabang = String(r['Cabang'] || (r['Grup ID'] && KEEP_GROUP_CABANG_MAP[r['Grup ID']] ? KEEP_GROUP_CABANG_MAP[r['Grup ID']].cabang : '') || '').trim();
    const cabangBadgeHtml = rawCabang ? `
      <span class="inline-flex items-center gap-1 rounded-lg bg-sky-50 border border-sky-200/80 px-2 py-0.5 text-[11px] font-bold text-sky-800 tracking-tight shadow-2xs whitespace-nowrap" title="Cabang: ${escapeHtml(rawCabang)}">
        <span class="size-1.5 rounded-full bg-sky-500 shrink-0"></span>
        <span>${escapeHtml(rawCabang)}</span>
      </span>
    ` : `<span class="text-xs text-slate-400 font-medium">-</span>`;

    // 5. TIM FOLLOW UP CELL (Auto-Claim via Webhook Reaction Baileys/WhatsApp)
    const fuName = String(r['Nama Tim Follow Up'] || r['nama_tim_follow_up'] || r['fu_name'] || '').trim();
    const rawFU = String(r['Nomor Follow Up'] || r['nomor_follow_up'] || r['Nomor FU'] || r['nomor_fu'] || '').trim();
    const cleanFUDigits = rawFU.replace(/\D/g, '');

    // Resolusi nomor WhatsApp asli jika nomor_fu berupa WhatsApp LID (15-18 digit acak)
    let actualPhone = cleanFUDigits;
    const isLid = cleanFUDigits.length > 13 || (!cleanFUDigits.startsWith('628') && !cleanFUDigits.startsWith('08') && !cleanFUDigits.startsWith('8'));
    if (isLid && fuName) {
      const masterPics = getMasterDataPICs();
      const matched = masterPics.find(m => m.picName && m.picName.toLowerCase().includes(fuName.toLowerCase()) && m.wa && m.wa.length >= 9 && m.wa.length <= 14);
      if (matched && matched.wa) {
        actualPhone = matched.wa.replace(/\D/g, '');
      }
    }

    const normFU = normalizePhone(actualPhone);
    const isValidMobile = normFU.startsWith('628') && normFU.length >= 10 && normFU.length <= 15;
    const displayFU = isValidMobile ? formatPhoneDisplay(normFU) : (rawFU || '-');

    let fuHtml = '';
    if (fuName || cleanFUDigits) {
      const fuInitials = fuName ? getCustomerInitials(fuName) : 'FU';
      const fuDisplayName = fuName || 'Tim Follow Up';
      fuHtml = `
        <div class="flex items-center gap-2.5">
          <div class="size-7 rounded-lg bg-emerald-500/10 text-emerald-700 border border-emerald-500/20 font-bold text-[10px] flex items-center justify-center shrink-0 shadow-2xs cursor-pointer hover:bg-emerald-500/20 hover:scale-105 transition" title="Klik untuk membuka Live Chat WhatsApp & follow up keep ini" onclick="openDcmsFollowUpLiveChatFromKeep(${keepRowIdx})">
            ${fuInitials}
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-1.5">
              <div class="font-bold text-slate-800 text-xs truncate max-w-[145px] cursor-pointer hover:text-emerald-700 transition" title="Klik untuk membuka Live Chat WhatsApp & follow up keep ini" onclick="openDcmsFollowUpLiveChatFromKeep(${keepRowIdx})">
                ${escapeHtml(fuDisplayName)}
              </div>
              ${(!window.IS_GUEST) ? `
                <button type="button" class="text-slate-300 hover:text-indigo-600 transition p-0.5 rounded cursor-pointer shrink-0" title="Ubah / Re-assign Tim Follow Up" onclick="openManualAssignFUModal(${keepRowIdx})">
                  <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
              ` : ''}
            </div>
            ${(isValidMobile && !window.IS_GUEST) ? `
              <div class="flex items-center gap-1.5">
                <button type="button" class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 hover:underline transition cursor-pointer text-left" onclick="openDcmsFollowUpLiveChatFromKeep(${keepRowIdx})" title="Buka Live Chat Widget untuk follow up format keep ini">
                  <span class="size-1.5 rounded-full bg-emerald-500"></span>
                  <span>${displayFU}</span>
                  <svg class="size-3 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </button>
                <a href="https://wa.me/${normFU}" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-emerald-600 transition p-0.5" title="Buka di tab WhatsApp Web: wa.me/${normFU}">
                  <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
              </div>
            ` : (displayFU ? `
              <span class="inline-flex items-center gap-1 text-[11px] text-slate-500 font-mono hover:text-indigo-600 transition" title="ID WhatsApp: ${escapeHtml(displayFU)} (Klik untuk salin)" onclick="navigator.clipboard && navigator.clipboard.writeText('${escapeHtml(displayFU)}'); if (typeof showSaveToast === 'function') showSaveToast('ID WhatsApp disalin: ${escapeHtml(displayFU)}');" style="cursor: pointer;">
                <span class="size-1.5 rounded-full bg-slate-400"></span>
                <span>${displayFU}</span>
              </span>
            ` : '')}
          </div>
        </div>
      `;
    } else {
      fuHtml = `
        <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 border border-dashed border-slate-300 hover:border-indigo-400 hover:bg-indigo-50/70 hover:text-indigo-700 px-2 py-0.5 text-[11px] font-semibold text-slate-500 transition cursor-pointer shadow-2xs group" onclick="openManualAssignFUModal(${keepRowIdx})" title="Klik untuk assign manual Tim Follow Up (Nama & No HP)">
          <span class="size-1.5 rounded-full bg-amber-400 group-hover:scale-125 transition"></span>
          <span>• Belum di-claim</span>
          <svg class="size-3 text-slate-400 group-hover:text-indigo-600 ml-0.5 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        </button>
      `;
    }

    const tr = document.createElement('tr');
    tr.setAttribute('data-keep-id', r.id || '');
    let rowClass = `border-b border-slate-100 hover:bg-slate-50/80 transition ${ (rawSt === 'LOSE' || rawSt === 'TAKE OUT') ? 'bg-rose-50/30' : '' }`;
    if (r._isNewlyArrived) {
      rowClass += ' bg-emerald-50/80 border-l-4 border-l-emerald-500 animate-pulse';
    }
    tr.className = rowClass;

    tr.innerHTML = `
      <td class="px-5 py-3.5 align-middle text-left">${custHtml}</td>
      <td class="px-3 py-3.5 align-middle text-center font-bold text-slate-700 whitespace-nowrap">${formatKeepDisplayDate(r['Timestamp'])}</td>
      <td class="px-3 py-3.5 align-middle text-center whitespace-nowrap">
        <div class="flex flex-col items-center justify-center gap-1">
          <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200/80 px-2 py-0.5 rounded-lg shadow-2xs">
            <svg class="size-3 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            ${formatKeepDisplayTime(r['Timestamp'])}
          </span>
          ${olaMeta.badgeHtml}
        </div>
      </td>
      <td class="px-4 py-3.5 align-middle text-left font-bold text-slate-900">${r['Barang Keep'] || '-'}</td>
      <td class="px-3 py-3.5 align-middle text-center text-slate-600 whitespace-nowrap">${formatPickupDate(r['Tanggal Ambil'])}</td>
      <td class="px-4 py-3.5 align-middle text-left text-slate-600 leading-snug">${r['Catatan'] || '-'}</td>
      <td class="px-3 py-3.5 align-middle text-center"><span class="inline-flex items-center rounded-lg bg-slate-100 border border-slate-200 px-2 py-0.5 text-[11px] font-bold text-slate-700 font-mono tracking-tight">${r['Kode Sosmed'] || '-'}</span></td>
      <td class="px-3 py-3.5 align-middle text-center">${cabangBadgeHtml}</td>
      <td class="px-3 py-3.5 align-middle text-center">${arHtml}</td>
      <td class="px-4 py-3.5 align-middle text-left">${fuHtml}</td>
      <td class="px-3 py-3.5 align-middle text-center">${statusPillHtml}</td>
      <td class="px-4 py-3 align-middle text-left min-w-[200px]">
        <textarea 
          rows="2"
          class="w-full rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-white focus:bg-white px-2.5 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/10 shadow-xs transition leading-relaxed resize-y"
          placeholder="Tulis feedback FU..."
          title="Ketik feedback lalu klik di luar untuk menyimpan"
          onchange="saveKeepFeedbackInline(this, '${rawWA}', ${keepRowIdx})"
          oninput="this.style.height = 'auto'; this.style.height = (this.scrollHeight + 2) + 'px';"
          style="min-height: ${Math.max(42, Math.min(140, Math.ceil(((r['Feedback Tim FU'] || '').trim().length || 1) / 28) * 20))}px;"
        >${escapeHtml(r['Feedback Tim FU'] || '')}</textarea>
      </td>
      <td class="px-3 py-3.5 align-middle text-center">
        <button type="button" class="grid size-7 mx-auto place-items-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:border-brand-500 hover:bg-brand-50 hover:text-brand-600 transition shadow-xs cursor-pointer" onclick="openARDetailModalFromKeep(${keepRowIdx})" title="Buka Detail Faktur">
          <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

// =========================================================================
// REALTIME KEEP NOTIFICATION & POLLING ENGINE (POP-UP TOAST + AUDIO CHIME)
// =========================================================================
let latestKnownKeepId = 0;
let keepRealtimePollingInterval = null;
let isKeepPollingRunning = false;
let lastKeepPollServerTime = '';

function playKeepNotificationChime() {
  try {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;
    const ctx = new AudioCtx();
    if (ctx.state === 'suspended') {
      ctx.resume();
    }
    const now = ctx.currentTime;

    // Two-tone bell chime: E5 (659Hz) -> A5 (880Hz)
    const osc1 = ctx.createOscillator();
    const gain1 = ctx.createGain();
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(659.25, now);
    gain1.gain.setValueAtTime(0.18, now);
    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
    osc1.connect(gain1);
    gain1.connect(ctx.destination);
    osc1.start(now);
    osc1.stop(now + 0.35);

    const osc2 = ctx.createOscillator();
    const gain2 = ctx.createGain();
    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(880, now + 0.12);
    gain2.gain.setValueAtTime(0.22, now + 0.12);
    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.65);
    osc2.connect(gain2);
    gain2.connect(ctx.destination);
    osc2.start(now + 0.12);
    osc2.stop(now + 0.65);
  } catch (e) {
    console.warn('Keep audio notification note:', e);
  }
}
window.playKeepNotificationChime = playKeepNotificationChime;

function showKeepNewNotification(item) {
  const container = document.getElementById('liveKeepToastContainer');
  if (!container) return;

  const id = item.id || `toast_${Date.now()}_${Math.random().toString(36).substring(2, 7)}`;
  const custName = item['Nama'] || item.nama || 'Customer';
  const rawWA = String(item['Nomor WA'] || item.nomor_wa || '').replace(/\D/g, '');
  const displayWA = (typeof formatPhoneDisplay === 'function') ? formatPhoneDisplay(rawWA) : rawWA;
  const barangKeep = item['Barang Keep'] || item.barang_keep || '-';
  const catatan = (item['Catatan'] || item.catatan || '').trim();
  const kodeSosmed = item['Kode Sosmed'] || item.kode_sosmed || '-';
  const tglAmbil = item['Tanggal Ambil'] || item.tanggal_ambil || '-';
  const ts = item['Timestamp'] || item.keep_timestamp || new Date().toISOString();
  const displayTime = (typeof formatKeepDisplayTime === 'function') ? formatKeepDisplayTime(ts) : 'Baru Saja';
  const isKredit = String(item['USER KREDIT'] || item.user_kredit || '').toUpperCase() === 'KREDIT';

  // Batasi maksimal 4 toast bertumpuk
  while (container.children.length >= 4) {
    container.removeChild(container.firstChild);
  }

  const toast = document.createElement('div');
  toast.id = `keepToast_${id}`;
  toast.className = 'pointer-events-auto w-full max-w-sm sm:max-w-md bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border-2 border-emerald-400/80 p-4 transition-all duration-300 transform translate-x-10 opacity-0';
  toast.style.boxShadow = '0 20px 35px -10px rgba(16, 185, 129, 0.25), 0 10px 15px -5px rgba(0, 0, 0, 0.05)';

  toast.innerHTML = `
    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
      <div class="flex items-center gap-2">
        <span class="relative flex h-2.5 w-2.5">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
        </span>
        <span class="text-[10px] font-black tracking-wider uppercase text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
          ⚡ Format Keep Baru
        </span>
        <span class="text-[11px] font-mono font-bold text-slate-500">
          ${displayTime}
        </span>
      </div>
      <button type="button" onclick="closeKeepToast('${id}')" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg transition hover:bg-slate-100 cursor-pointer" title="Tutup Notifikasi">
        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="cursor-pointer group" onclick="focusKeepRow('${id}')" title="Klik untuk membuka dan melihat data di tabel">
      <div class="flex items-start justify-between gap-2">
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-1.5 flex-wrap">
            <span class="font-extrabold text-slate-900 text-xs truncate max-w-[190px]">${escapeHtml(custName)}</span>
            ${isKredit ? '<span class="text-[9px] font-black bg-purple-100 text-purple-700 border border-purple-200 px-1.5 py-0.2 rounded-full">KREDIT</span>' : ''}
          </div>
          <div class="text-[11px] font-semibold text-emerald-600 font-mono mt-0.5 flex items-center gap-1">
            <svg class="size-3 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <span>${escapeHtml(displayWA)}</span>
          </div>
        </div>
        <span class="shrink-0 text-[10px] font-bold font-mono text-slate-700 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg shadow-2xs">
          ${escapeHtml(kodeSosmed)}
        </span>
      </div>
      <div class="mt-2 bg-slate-50/90 rounded-xl p-2.5 border border-slate-100">
        <div class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">Barang Keep</div>
        <div class="font-bold text-xs text-slate-800 line-clamp-2 mt-0.5">${escapeHtml(barangKeep)}</div>
        ${catatan ? `<div class="text-[11px] text-slate-500 mt-1 line-clamp-2 italic">"${escapeHtml(catatan)}"</div>` : ''}
      </div>
      <div class="mt-2.5 flex items-center justify-between text-xs pt-1 border-t border-slate-100/60">
        <span class="text-[10px] text-slate-400">Ambil: <strong class="text-slate-700">${escapeHtml(tglAmbil)}</strong></span>
        <span class="inline-flex items-center gap-1 font-bold text-[11px] text-emerald-600 group-hover:text-emerald-700 transition">
          Lihat di Tabel <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </span>
      </div>
    </div>
  `;

  container.appendChild(toast);

  // Animasi slide-in smooth
  requestAnimationFrame(() => {
    toast.classList.remove('translate-x-10', 'opacity-0');
    toast.classList.add('translate-x-0', 'opacity-100');
  });

  // Otomatis dismiss setelah 9 detik
  setTimeout(() => {
    closeKeepToast(id);
  }, 9000);
}
window.showKeepNewNotification = showKeepNewNotification;

function closeKeepToast(id) {
  const toast = document.getElementById(`keepToast_${id}`);
  if (!toast) return;
  toast.classList.remove('translate-x-0', 'opacity-100');
  toast.classList.add('translate-x-10', 'opacity-0');
  setTimeout(() => {
    if (toast && toast.parentNode) {
      toast.parentNode.removeChild(toast);
    }
  }, 320);
}
window.closeKeepToast = closeKeepToast;

function focusKeepRow(id) {
  if (typeof switchTab === 'function') {
    switchTab(4);
  }
  const searchInput = document.getElementById('keepSearchInput');
  if (searchInput) searchInput.value = '';
  keepCurrentPage = 1;
  applyKeepFilters();
  renderKeepPage();

  setTimeout(() => {
    const tbody = document.getElementById('keepTableBody');
    if (tbody) {
      const target = tbody.querySelector(`tr[data-keep-id="${id}"]`) || tbody.firstElementChild;
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        target.classList.add('ring-4', 'ring-emerald-400', 'bg-emerald-100/60');
        setTimeout(() => {
          target.classList.remove('ring-4', 'ring-emerald-400', 'bg-emerald-100/60');
        }, 4500);
      }
    }
  }, 200);
}
window.focusKeepRow = focusKeepRow;

async function pollLatestKeepData() {
  if (isKeepPollingRunning) return;
  isKeepPollingRunning = true;

  try {
    // Inisialisasi ID tertinggi jika belum tersedia
    if (!latestKnownKeepId || latestKnownKeepId <= 0) {
      const initRes = await fetch(`/api/sosmed-keeps/latest?after_id=0&t=${Date.now()}`);
      if (initRes.ok) {
        const initJson = await initRes.json();
        if (initJson && initJson.latest_id) {
          latestKnownKeepId = parseInt(initJson.latest_id, 10) || 0;
        }
        if (initJson && initJson.server_time) {
          lastKeepPollServerTime = initJson.server_time;
        }
      }
      isKeepPollingRunning = false;
      return;
    }

    const res = await fetch(`/api/sosmed-keeps/latest?after_id=${latestKnownKeepId}&updated_since=${encodeURIComponent(lastKeepPollServerTime || '')}&t=${Date.now()}`);
    if (!res.ok) {
      isKeepPollingRunning = false;
      return;
    }

    const json = await res.json();
    if (json && json.server_time) {
      lastKeepPollServerTime = json.server_time;
    }

    // 1. Tangani update pada data format keep yang sudah ada (misal: auto-claim reaksi staf sales)
    let hasUpdates = false;
    if (json && json.success && Array.isArray(json.updated) && json.updated.length > 0) {
      json.updated.forEach(upItem => {
        if (!upItem || !upItem.id) return;
        const numId = parseInt(upItem.id, 10);
        const match = (rawKeepRows || []).find(r => parseInt(r.id, 10) === numId);
        if (match) {
          if (upItem.nomor_follow_up) match['Nomor Follow Up'] = upItem.nomor_follow_up;
          if (upItem.nama_tim_follow_up) match['Nama Tim Follow Up'] = upItem.nama_tim_follow_up;
          if (upItem.nomor_fu) match['Nomor FU'] = upItem.nomor_fu;
          if (upItem.status) match['Status'] = upItem.status;
          if (upItem.feedback_tim_fu) match['Feedback Tim FU'] = upItem.feedback_tim_fu;
          if (upItem.report_ar) match['REPORT AR'] = upItem.report_ar;
          hasUpdates = true;
        }
      });
    }

    // 2. Tangani penambahan format keep baru
    if (json && json.success && Array.isArray(json.data) && json.data.length > 0) {
      if (json.latest_id && json.latest_id > latestKnownKeepId) {
        latestKnownKeepId = json.latest_id;
      }

      // Urutkan ascending berdasarkan id agar item terlama dimasukkan dahulu, dan yang terbaru di index 0
      const sortedNewItems = [...json.data].sort((a, b) => (parseInt(a.id, 10) || 0) - (parseInt(b.id, 10) || 0));

      let addedCount = 0;
      sortedNewItems.forEach(item => {
        const numId = parseInt(item.id, 10);
        if (numId > latestKnownKeepId) {
          latestKnownKeepId = numId;
        }

        // Cek duplikasi di client: ID sama ATAU (Nomor WA sama & Barang Keep sama)
        const itemCleanWA = String(item.nomor_wa || '').replace(/\D/g, '');
        const itemLast8 = itemCleanWA.length >= 8 ? itemCleanWA.slice(-8) : itemCleanWA;
        const itemCleanBarang = String(item.barang_keep || '').toLowerCase().replace(/[^a-z0-9]/g, '');

        const existingDupIdx = Array.isArray(rawKeepRows) ? rawKeepRows.findIndex(r => {
          if (r.id === item.id) return true;
          if (!itemLast8) return false;
          const rCleanWA = String(r['Nomor WA'] || r.nomor_wa || '').replace(/\D/g, '');
          const rLast8 = rCleanWA.length >= 8 ? rCleanWA.slice(-8) : rCleanWA;
          if (rLast8 !== itemLast8) return false;
          const rCleanBarang = String(r['Barang Keep'] || r.barang_keep || '').toLowerCase().replace(/[^a-z0-9]/g, '');
          return (rCleanBarang === itemCleanBarang || rCleanBarang.includes(itemCleanBarang) || itemCleanBarang.includes(rCleanBarang));
        }) : -1;

        if (existingDupIdx !== -1) {
          // Jika row lama belum diklaim tapi data baru membawa klaim tim follow up, perbarui baris lama
          const existingRow = rawKeepRows[existingDupIdx];
          if ((!existingRow['Nama Tim Follow Up'] && item.nama_tim_follow_up) || (!existingRow['Nomor Follow Up'] && item.nomor_fu)) {
            existingRow['Nama Tim Follow Up'] = item.nama_tim_follow_up || existingRow['Nama Tim Follow Up'];
            existingRow['Nomor Follow Up'] = item.nomor_fu || existingRow['Nomor Follow Up'];
            existingRow['Status'] = item.status || existingRow['Status'];
            hasUpdates = true;
          }
          return;
        }

        const newRow = {
          'id': item.id,
          'Timestamp': item.keep_timestamp || new Date().toISOString(),
          'Nomor Sender': item.nomor_sender || '',
          'Grup ID': item.grup_id || '',
          'Sender': item.sender || '',
          'GRUP': item.grup || '',
          '!grup': item.jenis_chat || 'GRUP',
          'Nama': item.nama || '',
          'Nomor WA': item.nomor_wa || '',
          'Barang Keep': item.barang_keep || '',
          'Tanggal Ambil': item.tanggal_ambil || '',
          'Catatan': item.catatan || '',
          'Remarks': item.remarks || '',
          'Kode Sosmed': item.kode_sosmed || '',
          'REPORT AR': item.report_ar || '',
          'Tanggal Input AR': item.tanggal_input_ar || '',
          'AR dari VLOOKUP': item.ar_vlookup || '',
          'Tanggal VLOOKUP': item.tanggal_vlookup || '',
          'Cabang': item.cabang || '',
          'Evidence': item.evidence || '',
          'Nomor Follow Up': item.nomor_follow_up || '',
          'Nama Tim Follow Up': item.nama_tim_follow_up || '',
          'Status': item.status || 'PENDING',
          'Feedback Tim FU': item.feedback_tim_fu || '',
          'NAMA TOKO KOMPETITOR\n(JIKA USER DEAL DI TOKO LAIN)': item.nama_toko_kompetitor || '',
          'USER KREDIT': item.user_kredit || 'NON KREDIT',
          'PERIODE': item.periode || '',
          'ITEM': item.item || '',
          'HAPUS DATA': 'FALSE',
          'Nomor FU': item.nomor_fu || '',
          _isNewlyArrived: true,
          _gsheetsIndex: (rawKeepRows || []).length
        };

        if (Array.isArray(rawKeepRows)) {
          rawKeepRows.unshift(newRow);
        }
        addedCount++;

        // Trigger notifikasi pop-up & suara audio chime
        showKeepNewNotification(newRow);
        playKeepNotificationChime();

        // Hilangkan sorotan baris baru setelah 18 detik
        setTimeout(() => {
          newRow._isNewlyArrived = false;
          if (typeof currentActiveTab !== 'undefined' && currentActiveTab === 4) {
            renderKeepPage();
          }
        }, 18000);
      });

      if (addedCount > 0) {
        console.log(`⚡ [Realtime Keep Engine] ${addedCount} format keep baru diterima secara realtime!`);
        const pill = document.getElementById('keepStatusPill');
        if (pill && Array.isArray(rawKeepRows)) {
          pill.innerHTML = `<span>⚡ Sumber: Database SQL MySQL (${rawKeepRows.length.toLocaleString('id-ID')} Data Realtime)</span>`;
        }
        keepCurrentPage = 1;
        applyKeepFilters();
        if (typeof currentActiveTab !== 'undefined' && currentActiveTab === 4) {
          renderKeepPage();
        }
      }
    } else if (hasUpdates) {
      applyKeepFilters();
      if (typeof currentActiveTab !== 'undefined' && currentActiveTab === 4) {
        renderKeepPage();
      }
    }
  } catch (err) {
    console.warn('Realtime keep poll error:', err);
  } finally {
    isKeepPollingRunning = false;
  }
}
window.pollLatestKeepData = pollLatestKeepData;

function startKeepRealtimePolling() {
  if (keepRealtimePollingInterval) {
    clearInterval(keepRealtimePollingInterval);
  }
  // Polling setiap 3 detik
  keepRealtimePollingInterval = setInterval(pollLatestKeepData, 3000);
  console.log('⚡ Keep Realtime Polling Engine aktif (Interval 3 detik)');
}
window.startKeepRealtimePolling = startKeepRealtimePolling;

async function saveKeepFeedbackInline(inputEl, rawWA, keepRowIdx) {
  if (!inputEl) return;
  const newFeedback = inputEl.value.trim();
  const row = filteredKeepRows[keepRowIdx];
  const oldFeedback = row ? (row['Feedback Tim FU'] || '') : '';

  if (newFeedback === oldFeedback) return;

  // Optimistic update in-memory
  if (row) {
    row['Feedback Tim FU'] = newFeedback;
  }
  const cleanWA = String(rawWA || '').replace(/\D/g, '');
  if (typeof rawKeepRows !== 'undefined' && Array.isArray(rawKeepRows)) {
    rawKeepRows.forEach(k => {
      if (String(k['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
        k['Feedback Tim FU'] = newFeedback;
      }
    });
  }

  // Visual save indicator
  inputEl.classList.add('!border-emerald-500', '!bg-emerald-50/50');
  setTimeout(() => {
    inputEl.classList.remove('!border-emerald-500', '!bg-emerald-50/50');
  }, 1200);

  // Sync ke database SQL & Google Apps Script
  const keepId = row ? row.id : null;
  try {
    await fetch('/api/update-keep-status', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        id: keepId,
        nomorWA: cleanWA,
        status: row ? (row['Status'] || '') : '',
        feedback: newFeedback,
        feedbackTimFU: newFeedback
      })
    });
  } catch (err) {
    console.warn('Gagal sync feedback ke backend:', err);
  }
}
window.saveKeepFeedbackInline = saveKeepFeedbackInline;

let currentKeepInputARContext = null;

function openKeepInputARModal(keepIndex) {
  const row = filteredKeepRows[keepIndex];
  if (!row) return;
  const rawWA = String(row['Nomor WA'] || '').replace(/\D/g, '');
  currentKeepInputARContext = { keepIndex, rawWA, row };

  const modal = document.getElementById('keepInputARModal');
  if (!modal) return;
  const custNameEl = document.getElementById('keepInputARCustName');
  const custPhoneEl = document.getElementById('keepInputARCustPhone');
  const barangEl = document.getElementById('keepInputARBarang');
  const field = document.getElementById('keepInputARField');

  if (custNameEl) custNameEl.innerText = row['Nama'] || '-';
  if (custPhoneEl) custPhoneEl.innerText = (rawWA ? formatPhoneDisplay(rawWA) : '-');
  if (barangEl) barangEl.innerText = row['Barang Keep'] ? `Barang: ${row['Barang Keep']}` : '-';
  if (field) {
    field.value = row['REPORT AR'] || '';
    setTimeout(() => {
      field.focus();
      field.select();
    }, 60);
  }
  modal.style.display = 'flex';
}

function closeKeepInputARModal() {
  const modal = document.getElementById('keepInputARModal');
  if (modal) modal.style.display = 'none';
  currentKeepInputARContext = null;
}

function submitKeepInputARModal() {
  if (!currentKeepInputARContext) return;
  const field = document.getElementById('keepInputARField');
  const cleanAR = field ? field.value.trim().replace(/\D/g, '') : '';
  if (!cleanAR) {
    if (field) {
      field.focus();
      field.classList.add('border-rose-500');
      setTimeout(() => field.classList.remove('border-rose-500'), 1500);
    }
    return;
  }

  const { keepIndex, rawWA, row } = currentKeepInputARContext;
  const keepId = row ? row.id : null;
  row['REPORT AR'] = cleanAR;

  if (typeof rawKeepRows !== 'undefined' && Array.isArray(rawKeepRows)) {
    rawKeepRows.forEach(k => {
      if ((keepId && k.id === keepId) || (rawWA && String(k['Nomor WA'] || '').replace(/\D/g, '') === rawWA)) {
        k['REPORT AR'] = cleanAR;
      }
    });
  }

  renderKeepPage();
  closeKeepInputARModal();

  triggerBackgroundReportARSync(rawWA, cleanAR, '', () => {
    row['REPORT AR'] = '';
    renderKeepPage();
  }, keepId);
}
window.openKeepInputARModal = openKeepInputARModal;
window.closeKeepInputARModal = closeKeepInputARModal;
window.submitKeepInputARModal = submitKeepInputARModal;

function openARDetailModalFromKeep(keepIndex) {
  const row = filteredKeepRows[keepIndex];
  if (!row) return;
  const arDoc = row['REPORT AR'];
  if (!arDoc) {
    openKeepInputARModal(keepIndex);
    return;
  }
  openARDetailModal(arDoc, row);
}

function openDcmsFollowUpLiveChatFromKeep(keepIndex) {
  const row = filteredKeepRows[keepIndex];
  if (!row) return;

  const fuName = String(row['Nama Tim Follow Up'] || row['nama_tim_follow_up'] || row['fu_name'] || '').trim();
  const rawFU = String(row['Nomor Follow Up'] || row['nomor_follow_up'] || row['Nomor FU'] || row['nomor_fu'] || '').trim();
  const cleanFUDigits = rawFU.replace(/\D/g, '');

  let actualPhone = cleanFUDigits;
  const isLid = cleanFUDigits.length > 13 || (!cleanFUDigits.startsWith('628') && !cleanFUDigits.startsWith('08') && !cleanFUDigits.startsWith('8'));
  if (isLid && fuName) {
    const masterPics = getMasterDataPICs();
    const matched = masterPics.find(m => m.picName && m.picName.toLowerCase().includes(fuName.toLowerCase()) && m.wa && m.wa.length >= 9 && m.wa.length <= 14);
    if (matched && matched.wa) {
      actualPhone = matched.wa.replace(/\D/g, '');
    }
  }

  const normFU = normalizePhone(actualPhone);
  if (!normFU || normFU.length < 8) {
    if (typeof showSaveToast === 'function') {
      showSaveToast('Nomor WhatsApp tim follow up belum tersedia.');
    } else {
      alert('Nomor WhatsApp tim follow up belum tersedia.');
    }
    return;
  }

  const custName = row['Nama'] || '-';
  const rawWA = String(row['Nomor WA'] || '').replace(/\D/g, '');
  const custWA = formatPhoneDisplay(rawWA) || '-';
  const barang = row['Barang Keep'] || row['barang_keep'] || row['Barang'] || '-';
  const cabang = row['Cabang'] || (row['Grup ID'] && KEEP_GROUP_CABANG_MAP[row['Grup ID']] ? KEEP_GROUP_CABANG_MAP[row['Grup ID']].cabang : '') || '-';
  const tglAmbil = row['Tgl. Ambil'] || row['tgl_ambil'] || row['Tanggal Ambil'] || '-';
  const catatan = row['Catatan'] || row['catatan'] || '';

  const keepDetails = {
    customer: custName,
    wa: custWA,
    barang: barang,
    cabang: cabang,
    tglAmbil: tglAmbil,
    catatan: catatan
  };

  const displayName = fuName || ('PIC ' + normFU);

  if (typeof window.openDcmsFollowUpLiveChat === 'function') {
    window.openDcmsFollowUpLiveChat(normFU, displayName, keepDetails);
  } else if (typeof window.openDcmsLiveWidgetRoom === 'function') {
    if (typeof window.toggleDcmsLiveWidget === 'function') {
      window.toggleDcmsLiveWidget(true);
    }
    const prefill = `Halo kak ${displayName}, mau konfirmasi tindak lanjut format keep:\n` +
                    `• Customer: ${custName} (${custWA})\n` +
                    `• Barang: ${barang}\n` +
                    `• Cabang: ${cabang}\n` +
                    `• Tgl Ambil: ${tglAmbil}\n` +
                    (catatan ? `• Catatan: ${catatan}\n` : '') +
                    `\nApakah customer-nya sudah dihubungi kak? Mohon infonya ya, terima kasih 🙏`;
    window.openDcmsLiveWidgetRoom(normFU, displayName, prefill);
  } else {
    // Fallback external WhatsApp Web
    const text = encodeURIComponent(
      `Halo kak ${displayName}, mau konfirmasi tindak lanjut format keep:\n` +
      `• Customer: ${custName} (${custWA})\n` +
      `• Barang: ${barang}\n` +
      `• Cabang: ${cabang}\n` +
      `• Tgl Ambil: ${tglAmbil}\n` +
      (catatan ? `• Catatan: ${catatan}\n` : '') +
      `\nApakah customer-nya sudah dihubungi kak? Terima kasih 🙏`
    );
    window.open(`https://wa.me/${normFU}?text=${text}`, '_blank');
  }
}
window.openDcmsFollowUpLiveChatFromKeep = openDcmsFollowUpLiveChatFromKeep;

function pickBestKeep(matchedKeeps, postingDate) {
  if (!matchedKeeps || matchedKeeps.length === 0) return null;
  if (matchedKeeps.length === 1) return matchedKeeps[0];

  const monthMap = {
    '01': 'januari', '1': 'januari',
    '02': 'februari', '2': 'februari',
    '03': 'maret', '3': 'maret',
    '04': 'april', '4': 'april',
    '05': 'mei', '5': 'mei',
    '06': 'juni', '6': 'juni',
    '07': 'juli', '7': 'juli',
    '08': 'agustus', '8': 'agustus',
    '09': 'september', '9': 'september',
    '10': 'oktober',
    '11': 'november',
    '12': 'desember'
  };

  let monthName = '';
  if (typeof postingDate === 'number') {
    const d = XLSX.SSF.parse_date_code(postingDate);
    if (d && d.m) monthName = monthMap[String(d.m)] || '';
  } else if (postingDate instanceof Date) {
    monthName = monthMap[String(postingDate.getMonth() + 1)] || '';
  } else if (typeof postingDate === 'string') {
    const cleanStr = postingDate.trim().split(' ')[0];
    const parts = cleanStr.split(/[\/\-]/);
    if (parts.length >= 3) {
      const mCandidate = parts[0].length === 4 ? parts[1] : parts[1];
      monthName = monthMap[mCandidate] || monthMap[String(Number(mCandidate))] || '';
    }
  }

  if (monthName) {
    const match = matchedKeeps.find(k => String(k['PERIODE'] || '').toLowerCase().includes(monthName));
    if (match) return match;
  }

  // Fallback to the latest/newest keep row in Google Sheets
  return matchedKeeps[matchedKeeps.length - 1];
}

// ==================== POP-UP MODAL DETAIL AR & PELAPORAN WHATSAPP AKMAL ====================
let currentModalDoc = null;
let currentModalRows = [];
let currentModalKeep = null;
let currentModalCheckedItems = new Set();

function openARDetailModal(docNumber, keepContext = null, sourceTitle = '') {
  const digitsOnly = String(docNumber || '').replace(/\D/g, '');
  if (!digitsOnly) {
    alert("Nomor Dokumen AR tidak valid.");
    return;
  }

  // 1. Find AR rows in rawCrosellingRows (or rawTurnoverRows fallback)
  let matchedRows = [];
  if (rawCrosellingRows && rawCrosellingRows.length > 0) {
    matchedRows = rawCrosellingRows.filter(r => String(r['Document Number'] || '').replace(/\D/g, '') === digitsOnly);
  }
  if (matchedRows.length === 0 && rawTurnoverRows && rawTurnoverRows.length > 0) {
    matchedRows = rawTurnoverRows.filter(r => String(r['Document Number'] || '').replace(/\D/g, '') === digitsOnly);
  }

  if (matchedRows.length === 0) {
    alert(`Dokumen AR ${docNumber} tidak ditemukan di database transaksi croselling/turnover.`);
    return;
  }

  currentModalDoc = docNumber;
  currentModalRows = matchedRows;
  currentModalKeep = keepContext;

  // If keepContext was not passed, look up in rawKeepRows by AR or phone
  if (!currentModalKeep && rawKeepRows && rawKeepRows.length > 0) {
    currentModalKeep = rawKeepRows.find(k => String(k['REPORT AR'] || '').replace(/\D/g, '') === digitsOnly);
    if (!currentModalKeep && matchedRows[0]['No HP Customer']) {
      const norm = normalizePhone(matchedRows[0]['No HP Customer']);
      const allPhoneMatches = rawKeepRows.filter(k => normalizePhone(k['Nomor WA']) === norm);
      currentModalKeep = pickBestKeep(allPhoneMatches, matchedRows[0]['Posting Date']);
    }
  }

  // Populate Title & Header (Matching Image 2)
  const mainTitleEl = document.getElementById('modalDocMainTitle');
  if (mainTitleEl) {
    if (sourceTitle === 'REPORT AR') {
      mainTitleEl.innerText = 'TRACK AR BERDASARKAN REPORT AR';
    } else if (sourceTitle === 'AR VLOOKUP') {
      mainTitleEl.innerText = 'TRACK AR BERDASARKAN VLOOKUP';
    } else {
      mainTitleEl.innerText = `TRACK AR DOKUMEN ${docNumber}`;
    }
  }

  const docTitleEl = document.getElementById('modalDocTitle');
  if (docTitleEl) docTitleEl.innerText = docNumber;

  const first = matchedRows[0];
  const metaDateEl = document.getElementById('modalMetaDate');
  if (metaDateEl) metaDateEl.innerText = formatCrosellingDate(first['Posting Date']) + (first['Waktu'] ? ` ${first['Waktu']}` : '');
  const metaCabangEl = document.getElementById('modalMetaCabang');
  if (metaCabangEl) metaCabangEl.innerText = first['Cabang'] || '-';

  // Populate Top 5-Column Summary Table (Matching Image 2 Mockup)
  const custName = (currentModalKeep && currentModalKeep['Nama']) ? currentModalKeep['Nama'] : (first['Name'] || first['Customer/Vendor Name'] || '-');
  const rawWA = (currentModalKeep && currentModalKeep['Nomor WA']) ? currentModalKeep['Nomor WA'] : (first['No HP Customer'] || '');
  const displayWA = formatPhoneDisplay(rawWA);
  const normWA = normalizePhone(rawWA);
  const waHtml = (normWA && normWA.length >= 8)
    ? `<div style="display:flex; align-items:center; gap:6px;">
        <span class="font-bold">${displayWA}</span>
        <a href="https://wa.me/${normWA}" target="_blank" class="btn-whatsapp" style="padding:2px 8px; font-size:0.72rem; border-radius:4px;" title="Chat customer ini di WhatsApp">
          💬 WA
        </a>
       </div>`
    : (displayWA || '-');

  const sosmedCode = (currentModalKeep && currentModalKeep['Kode Sosmed']) ? currentModalKeep['Kode Sosmed'] : '-';
  const keepBarang = (currentModalKeep && currentModalKeep['Barang Keep']) ? currentModalKeep['Barang Keep'] : '-';

  const keepCatatan = (currentModalKeep && currentModalKeep['Catatan']) ? String(currentModalKeep['Catatan']).trim() : '';
  const arRemarks = first['Remarks'] ? String(first['Remarks']).trim() : '';
  let catatanModalHtml = '-';
  if (keepCatatan && arRemarks && keepCatatan.toLowerCase() !== arRemarks.toLowerCase() && keepCatatan !== '-' && arRemarks !== '-') {
    catatanModalHtml = `
      <div style="font-size:0.76rem; color:#0f172a; margin-bottom:2px; word-break:break-word;">${keepCatatan}</div>
      <div style="font-size:0.70rem; color:var(--text-muted); word-break:break-word;"><em>Kasir: ${arRemarks}</em></div>
    `;
  } else if (keepCatatan && keepCatatan !== '-') {
    catatanModalHtml = `<div style="font-size:0.76rem; color:#0f172a; word-break:break-word;">${keepCatatan}</div>`;
  } else if (arRemarks && arRemarks !== '-') {
    catatanModalHtml = `<div style="font-size:0.72rem; color:var(--text-muted); word-break:break-word;"><em>Kasir: ${arRemarks}</em></div>`;
  }

  const elCust = document.getElementById('modalTrackCust');
  const elWA = document.getElementById('modalTrackWA');
  const elSos = document.getElementById('modalTrackSosmed');
  const elBarang = document.getElementById('modalTrackKeepBarang');
  const elCat = document.getElementById('modalTrackCatatan');
  const elStatus = document.getElementById('modalTrackStatusKeep');

  if (elCust) elCust.innerText = custName;
  if (elWA) elWA.innerHTML = waHtml;
  if (elSos) elSos.innerHTML = `<span class="badge-sosmed-pill">${sosmedCode}</span>`;
  if (elBarang) elBarang.innerText = keepBarang;
  if (elCat) elCat.innerHTML = catatanModalHtml;

  if (elStatus) {
    const rawModalWA = String(currentModalKeep && currentModalKeep['Nomor WA'] || '').replace(/\D/g, '');
    const rawModalStatus = currentModalKeep && currentModalKeep['Status'];
    const currentStatus = (rawModalStatus && String(rawModalStatus).trim() !== '-') ? String(rawModalStatus).trim().toUpperCase() : '';
    const statusSelectClass = getKeepStatusClass(currentStatus);

    let statusOptionsHtml = '';
    if (currentStatus && !KEEP_STATUS_OPTIONS.some(o => o.value.toUpperCase() === currentStatus)) {
      statusOptionsHtml += `<option value="${currentStatus}" selected>${currentStatus}</option>`;
    }
    KEEP_STATUS_OPTIONS.forEach(opt => {
      const isSel = (opt.value.toUpperCase() === currentStatus);
      statusOptionsHtml += `<option value="${opt.value}" ${isSel ? 'selected' : ''}>${opt.label}</option>`;
    });

    elStatus.innerHTML = (rawModalWA && rawModalWA.length >= 8) ? `
      <select class="select-status-keep ${statusSelectClass}" data-prev="${currentStatus}" onchange="handleModalKeepStatusChange(this, '${rawModalWA}')" title="Klik untuk mengubah status follow-up customer ini (otomatis tersimpan ke Google Sheets)">
        ${statusOptionsHtml}
      </select>
    ` : `<span class="kpi-badge badge-ink">-</span>`;
  }

  // Backward compatibility metadata
  const metaDocEl = document.getElementById('modalMetaDoc');
  if (metaDocEl) metaDocEl.innerText = first['Document Number'] || docNumber;
  const metaCustEl = document.getElementById('modalMetaCust');
  if (metaCustEl) metaCustEl.innerText = custName;
  const metaWAEl = document.getElementById('modalMetaWA');
  if (metaWAEl) metaWAEl.innerHTML = waHtml;
  const metaRemarksEl = document.getElementById('modalMetaRemarks');
  if (metaRemarksEl) metaRemarksEl.innerText = first['Remarks'] || '-';

  // Check Sosmed Status per Item
  let totalDocVal = 0;
  let allMissing = true;
  let missingCount = 0;
  currentModalCheckedItems.clear();

  matchedRows.forEach((r, idx) => {
    const qty = Number(r['Quantity']) || 1;
    const subtotal = Number(r['Harga Jual']) || 0;
    totalDocVal += subtotal;

    const isExempt = isItemExemptFromSosmed(r);
    const isSosmed = isItemInputSosmed(r);

    if (isExempt) {
      // Tas Gadgetmart 25x25 dll tidak perlu diinput sosmed (dianggap pass / exempt)
      return;
    }

    if (isSosmed) {
      allMissing = false;
    } else {
      missingCount++;
      // Pre-check missing items so user can report them directly!
      currentModalCheckedItems.add(idx);
    }
  });

  document.getElementById('modalMetaTotal').innerText = formatRupiah(totalDocVal);
  document.getElementById('modalItemsCountBadge').innerText = `${matchedRows.length} Item`;

  // Status Banner in Modal
  const banner = document.getElementById('modalStatusBanner');
  const icon = document.getElementById('modalStatusIcon');
  const title = document.getElementById('modalStatusTitle');
  const subtitle = document.getElementById('modalStatusSubtitle');

  if (allMissing) {
    banner.className = 'ar-status-banner banner-danger';
    icon.innerHTML = '❌';
    title.innerText = 'BELUM DIINPUT KODE SOSMED (100% OFFLINE)';
    subtitle.innerText = `Seluruh item dalam AR ini tercatat dengan kode sales toko fisik/kasir, belum ada kode sosmed.`;
  } else if (missingCount > 0) {
    banner.className = 'ar-status-banner banner-warning';
    icon.innerHTML = '⚠️';
    title.innerText = `SEBAGIAN ITEM BELUM TERINPUT (${missingCount} DARI ${matchedRows.length} ITEM)`;
    subtitle.innerText = `Sebagian produk sudah berstatus sosmed, namun masih ada ${missingCount} item yang belum terinput sosmed.`;
  } else {
    banner.className = 'ar-status-banner banner-success';
    icon.innerHTML = '✅';
    title.innerText = 'SEMUA ITEM SUDAH TERINPUT SOSMED (100%)';
    subtitle.innerText = `Semua item dalam dokumen ini sudah valid terinput kode sales sosmed (termasuk item tas yang bebas sosmed).`;
  }

  // Render Table Items with Checkbox
  renderModalItemsTable();

  // Populate Target Sosmed Dropdown for SubTab 2
  populateModalSosmedSelect();

  // Reset Notes
  const defaultNote = (currentModalKeep && currentModalKeep['Barang Keep'])
    ? `Customer keep barang "${currentModalKeep['Barang Keep']}". Mohon bantuannya Mas Akmal untuk input sales sosmed pada item terpilih di AR ini. Terima kasih!`
    : 'Mohon bantuannya Mas Akmal untuk input sales sosmed pada item terpilih di AR ini. Terima kasih!';
  document.getElementById('modalReportNotes').value = defaultNote;

  // Default to Sub-Tab 1
  switchModalSubTab(1);

  // Show Modal
  document.getElementById('arDetailModal').style.display = 'flex';
}

function closeARDetailModal() {
  document.getElementById('arDetailModal').style.display = 'none';
}

function switchModalSubTab(tabIdx) {
  const tab1 = document.getElementById('modalSubTabView1');
  const tab2 = document.getElementById('modalSubTabView2');
  const btn1 = document.getElementById('btnModalSubTab1');
  const btn2 = document.getElementById('btnModalSubTab2');

  if (tabIdx === 1) {
    tab1.style.display = 'block';
    tab2.style.display = 'none';
    btn1.classList.add('active');
    btn2.classList.remove('active');
  } else {
    tab1.style.display = 'none';
    tab2.style.display = 'block';
    btn1.classList.remove('active');
    btn2.classList.add('active');
    updateSelectedItemsForReport();
    updateWhatsAppMessagePreview();
  }
}

function renderModalItemsTable() {
  const tbody = document.getElementById('modalItemsTableBody');
  tbody.innerHTML = '';

  let totalQty = 0;
  let totalVal = 0;

  currentModalRows.forEach((r, idx) => {
    const qty = Number(r['Quantity']) || 1;
    const subtotal = Number(r['Harga Jual']) || 0;
    const price = qty > 0 ? (subtotal / qty) : subtotal;
    totalQty += qty;
    totalVal += subtotal;

    const isExempt = isItemExemptFromSosmed(r);
    const isSosmed = isItemInputSosmed(r);
    const schl = String(r['SC/HL Code'] || '').trim();
    const slp = String(r['SlpName'] || '').trim();
    const emp = String(r['Sales Employee Name'] || '').trim();
    const empDisplay = schl || slp || emp || '-';

    const isChecked = currentModalCheckedItems.has(idx);

    // Potensi Denda: Handphone = DENDA, Aksesoris/Tas/lainnya = PASS
    const dendaStatus = isExempt ? 'PASS' : getItemPotensiDenda(r);
    const dendaBadge = dendaStatus === 'DENDA'
      ? '<span class="kpi-badge badge-crimson font-bold">🚨 DENDA</span>'
      : '<span class="kpi-badge badge-ink">PASS</span>';

    let statusSosmedBadge = '';
    if (isExempt) {
      statusSosmedBadge = '<span class="status-badge" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-weight:700;">🛍️ Bebas Sosmed</span>';
    } else if (isSosmed) {
      statusSosmedBadge = '<span class="status-badge badge-accent">✅ Sudah Sosmed</span>';
    } else {
      statusSosmedBadge = '<span class="status-badge badge-crimson font-bold">❌ Belum Sosmed</span>';
    }

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="text-align: center;">
        <input type="checkbox" class="checkbox-custom" ${isChecked ? 'checked' : ''} onchange="toggleModalItemCheck(${idx}, this.checked)">
      </td>
      <td class="col-sticky">${idx + 1}</td>
      <td class="text-left font-bold" style="color:var(--ink);">${r['Dscription'] || '-'}</td>
      <td>${qty}</td>
      <td>${formatRupiah(price)}</td>
      <td class="font-bold">${formatRupiah(subtotal)}</td>
      <td class="text-left">${empDisplay}</td>
      <td class="text-center">${statusSosmedBadge}</td>
      <td class="text-center">${dendaBadge}</td>
      <td class="text-center">
        <button class="btn btn-outline btn-xs" onclick="copyModalSingleItem(${idx}, this)" style="padding: 3px 8px; font-size: 0.74rem; font-weight:700;" title="Salin baris item ini ke format 12 kolom Excel (tanpa header)">
          📋 Copy Rekap
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  document.getElementById('modalItemsTableFoot').innerHTML = `
    <tr class="row-total">
      <td colspan="3" class="text-left font-bold">TOTAL DOKUMEN AR:</td>
      <td><strong>${totalQty}</strong></td>
      <td>-</td>
      <td class="font-bold">${formatRupiah(totalVal)}</td>
      <td colspan="4" class="text-left font-bold text-accent">${currentModalRows.length} Baris Produk</td>
    </tr>
  `;

  const selectAll = document.getElementById('modalSelectAllItems');
  if (selectAll) {
    selectAll.checked = currentModalRows.length > 0 && currentModalCheckedItems.size === currentModalRows.length;
  }
}

function toggleModalItemCheck(idx, checked) {
  if (checked) {
    currentModalCheckedItems.add(idx);
  } else {
    currentModalCheckedItems.delete(idx);
  }
  const selectAll = document.getElementById('modalSelectAllItems');
  if (selectAll) {
    selectAll.checked = currentModalCheckedItems.size === currentModalRows.length;
  }
  updateSelectedItemsForReport();
  updateWhatsAppMessagePreview();
}

function toggleSelectAllModalItems(checked) {
  currentModalCheckedItems.clear();
  if (checked) {
    currentModalRows.forEach((_, idx) => currentModalCheckedItems.add(idx));
  }
  renderModalItemsTable();
  updateSelectedItemsForReport();
  updateWhatsAppMessagePreview();
}

function populateModalSosmedSelect() {
  const select = document.getElementById('modalReportSosmedSelect');
  if (!select) return;

  const masterList = getMasterSales().filter(m => m.active);
  let keepSosmed = '';
  if (currentModalKeep && currentModalKeep['Kode Sosmed']) {
    keepSosmed = String(currentModalKeep['Kode Sosmed']).trim();
  }

  let optionsHtml = '<option value="">-- Pilih Kode Sales Sosmed --</option>';
  masterList.forEach(m => {
    const fullVal = `${m.code} - ${m.name}`;
    const isSelected = keepSosmed && (keepSosmed.includes(m.code) || keepSosmed.includes(m.name));
    optionsHtml += `<option value="${fullVal}" ${isSelected ? 'selected' : ''}>${fullVal}</option>`;
  });

  if (keepSosmed && !masterList.some(m => keepSosmed.includes(m.code))) {
    optionsHtml += `<option value="${keepSosmed}" selected>⭐ ${keepSosmed} (Dari Data Keep)</option>`;
  }

  select.innerHTML = optionsHtml;
}

function updateSelectedItemsForReport() {
  const container = document.getElementById('modalReportSelectedItemsList');
  if (!container) return;

  if (currentModalCheckedItems.size === 0) {
    container.innerHTML = '<em style="color:var(--text-muted);">Belum ada item yang dicentang. Silakan kembali ke sub-tab 1 untuk memilih item, atau centang semua item.</em>';
    return;
  }

  const itemsHtml = Array.from(currentModalCheckedItems).sort((a,b)=>a-b).map(idx => {
    const r = currentModalRows[idx];
    const desc = r['Dscription'] || `Item #${idx+1}`;
    const qty = Number(r['Quantity']) || 1;
    return `
      <div class="selected-item-tag">
        <span><strong>${idx + 1}. ${desc}</strong> (Qty: ${qty})</span>
        <button type="button" class="btn-remove-tag" onclick="toggleModalItemCheck(${idx}, false); renderModalItemsTable();" title="Batal lapor item ini">✕</button>
      </div>
    `;
  }).join('');

  container.innerHTML = itemsHtml;
}

function getReportWhatsAppText() {
  const doc = currentModalDoc || '-';
  const first = currentModalRows[0] || {};
  const dateStr = formatCrosellingDate(first['Posting Date']);
  const cabang = first['Cabang'] || '-';
  const custName = (currentModalKeep && currentModalKeep['Nama']) ? currentModalKeep['Nama'] : (first['Name'] || '-');
  const custWA = (currentModalKeep && currentModalKeep['Nomor WA']) ? formatPhoneDisplay(currentModalKeep['Nomor WA']) : formatPhoneDisplay(first['No HP Customer']);
  const barangKeep = (currentModalKeep && currentModalKeep['Barang Keep']) ? currentModalKeep['Barang Keep'] : '-';
  const targetSosmed = document.getElementById('modalReportSosmedSelect').value || '(Belum Dipilih)';
  const notes = document.getElementById('modalReportNotes').value.trim() || '-';

  const selectedItemsList = Array.from(currentModalCheckedItems).sort((a,b)=>a-b).map((idx, num) => {
    const r = currentModalRows[idx];
    const desc = r['Dscription'] || '-';
    const qty = Number(r['Quantity']) || 1;
    const subtotal = Number(r['Harga Jual']) || 0;
    return `${num + 1}. *${desc}* (Qty: ${qty} | ${formatRupiah(subtotal)})`;
  });

  const itemsText = selectedItemsList.length > 0
    ? selectedItemsList.join('\n')
    : '(Belum ada item yang dicentang)';

  return `🚨 *LAPORAN REVISI / ITEM BELUM TERINPUT SOSMED*
-----------------------------------------
*No. AR / Faktur:* ${doc}
*Tanggal AR:* ${dateStr}
*Cabang:* ${cabang}
*Nama Customer:* ${custName}
*No. WhatsApp:* ${custWA}
*Barang Keep:* ${barangKeep}

*Daftar Item yang Belum Terinput Sosmed:*
${itemsText}

*Harusnya Diinput Kode Sosmed:*
👉 *${targetSosmed}*

*Catatan / Keterangan:*
${notes}
-----------------------------------------
_Dikirim via Dashboard Sosmed GadgetMart_`;
}

function updateWhatsAppMessagePreview() {
  const pre = document.getElementById('modalWAPreview');
  if (pre) {
    pre.innerText = getReportWhatsAppText();
  }
}

function sendReportToAkmalWhatsApp() {
  if (currentModalCheckedItems.size === 0) {
    alert("Pilih minimal 1 item yang belum terinput sosmed sebelum mengirim laporan!");
    switchModalSubTab(1);
    return;
  }

  const targetSosmed = document.getElementById('modalReportSosmedSelect').value;
  if (!targetSosmed) {
    alert("Pilih 'Harusnya Diinput Kode Sosmed Berapa' terlebih dahulu!");
    const el = document.getElementById('modalReportSosmedSelect');
    if (el && typeof el.focus === 'function') el.focus();
    return;
  }

  const message = getReportWhatsAppText();
  // Akmal's WhatsApp: +62 823-5771-2674 -> 6282357712674
  const akmalWA = '6282357712674';
  const url = `https://wa.me/${akmalWA}?text=${encodeURIComponent(message)}`;
  window.open(url, '_blank');
}

// ==================== TAB 5: TRACKING AR (AUDIT MISSING SOSMED) ====================
let rawAuditARList = [];
let filteredAuditARList = [];
let auditCurrentPage = 1;
const AUDIT_PAGE_SIZE = 50;

function parseKeepTimestampToEpoch(ts) {
  if (!ts) return 0;
  if (typeof ts === 'number') {
    return (ts - 25569) * 86400 * 1000;
  }
  if (ts instanceof Date) {
    return ts.getTime();
  }
  if (typeof ts === 'string') {
    const s = ts.trim();
    if (!s) return 0;
    const [datePart, timePart] = s.split(/\s+/);
    let hours = 0, minutes = 0, seconds = 0;
    if (timePart) {
      const tPieces = timePart.split(':');
      if (tPieces.length >= 2) {
        hours = parseInt(tPieces[0], 10) || 0;
        minutes = parseInt(tPieces[1], 10) || 0;
        seconds = parseInt(tPieces[2], 10) || 0;
      }
    }

    const dPieces = datePart.split(/[\/\-\.]/);
    if (dPieces.length === 3) {
      let y, m, d;
      if (dPieces[0].length === 4) {
        y = parseInt(dPieces[0], 10);
        m = parseInt(dPieces[1], 10) - 1;
        d = parseInt(dPieces[2], 10);
      } else {
        const p0 = parseInt(dPieces[0], 10);
        const p1 = parseInt(dPieces[1], 10);
        let p2 = parseInt(dPieces[2], 10);
        if (p2 < 100) p2 += 2000;

        if (p0 > 12) {
          d = p0;
          m = p1 - 1;
          y = p2;
        } else if (p1 > 12) {
          m = p0 - 1;
          d = p1;
          y = p2;
        } else {
          d = p0;
          m = p1 - 1;
          y = p2;
        }
      }
      return new Date(y, m, d, hours, minutes, seconds).getTime();
    }
  }
  return 0;
}

function parseDateToISO(dateVal) {
  if (!dateVal) return '';
  if (typeof dateVal === 'number') {
    if (typeof XLSX !== 'undefined' && XLSX.SSF && XLSX.SSF.parse_date_code) {
      const d = XLSX.SSF.parse_date_code(dateVal);
      if (d) {
        return `${d.y}-${String(d.m).padStart(2, '0')}-${String(d.d).padStart(2, '0')}`;
      }
    }
  }
  if (dateVal instanceof Date) {
    return `${dateVal.getFullYear()}-${String(dateVal.getMonth() + 1).padStart(2, '0')}-${String(dateVal.getDate()).padStart(2, '0')}`;
  }
  if (typeof dateVal === 'string') {
    const cleanStr = dateVal.trim().split(' ')[0].split('T')[0];
    const parts = cleanStr.split(/[\/\-\.]/);
    if (parts.length >= 3) {
      if (parts[0].length === 4) {
        return `${parts[0]}-${String(parts[1]).padStart(2, '0')}-${String(parts[2]).padStart(2, '0')}`;
      } else {
        let y = parts[2].trim();
        if (y.length === 2) y = '20' + y;
        const p0 = Number(parts[0].trim());
        const p1 = Number(parts[1].trim());
        if (p0 > 12) {
          return `${y}-${String(p1).padStart(2, '0')}-${String(p0).padStart(2, '0')}`;
        }
        if (p1 > 12) {
          return `${y}-${String(p0).padStart(2, '0')}-${String(p1).padStart(2, '0')}`;
        }
        return `${y}-${String(p1).padStart(2, '0')}-${String(p0).padStart(2, '0')}`;
      }
    }
  }
  return '';
}

function runARMissingSosmedAudit() {
  const pill = document.getElementById('arAuditStatusPill');
  if (pill) pill.innerHTML = '<span>⏳ Menganalisis Transaksi vs Data Keep...</span>';

  if (!rawCrosellingRows || rawCrosellingRows.length === 0 || !rawKeepRows || rawKeepRows.length === 0) {
    if (pill) pill.innerHTML = '<span style="color:#e11d48;">⚠️ Data Google Sheets / Keep belum siap</span>';
    return;
  }

  // 1. Map phone -> Keep rows & Map reportAR -> Keep row
  const phoneToKeep = new Map();
  const phoneLast9ToKeep = new Map();
  const arDocToKeep = new Map();

  rawKeepRows.forEach((k, idx) => {
    k._gsheetsIndex = idx;
    const norm = normalizePhone(k['Nomor WA']);
    if (norm && norm.length >= 8) {
      if (!phoneToKeep.has(norm)) phoneToKeep.set(norm, []);
      phoneToKeep.get(norm).push(k);

      if (norm.length >= 9) {
        const last9 = norm.slice(-9);
        if (!phoneLast9ToKeep.has(last9)) phoneLast9ToKeep.set(last9, []);
        phoneLast9ToKeep.get(last9).push(k);
      }
    }

    const arRaw = String(k['REPORT AR'] || '').replace(/\D/g, '');
    if (arRaw) {
      if (!arDocToKeep.has(arRaw)) arDocToKeep.set(arRaw, []);
      arDocToKeep.get(arRaw).push(k);
    }
  });

  // 2. Group croselling by Document Number
  const docMap = new Map();
  const phoneToAR = new Map();
  rawCrosellingRows.forEach(r => {
    const doc = String(r['Document Number'] || '').trim();
    if (!doc) return;
    if (!docMap.has(doc)) docMap.set(doc, []);
    docMap.get(doc).push(r);

    const normHp = normalizePhone(r['No HP Customer']);
    if (normHp && normHp.length >= 8 && !phoneToAR.has(normHp)) {
      phoneToAR.set(normHp, doc);
    }
  });

  // 3. Match and audit
  rawAuditARList = [];
  docMap.forEach((items, doc) => {
    const rawHp = items[0]['No HP Customer'];
    const normHp = normalizePhone(rawHp);
    const cleanDoc = doc.replace(/\D/g, '');
    let matchedKeeps = [];
    let isVlookupMatch = false;

    if (normHp && phoneToKeep.has(normHp)) {
      matchedKeeps = phoneToKeep.get(normHp);
      isVlookupMatch = true;
    } else if (normHp && normHp.length >= 9 && phoneLast9ToKeep.has(normHp.slice(-9))) {
      matchedKeeps = phoneLast9ToKeep.get(normHp.slice(-9));
      isVlookupMatch = true;
    }

    // Direct match by REPORT AR document number
    if (matchedKeeps.length === 0 && cleanDoc && arDocToKeep.has(cleanDoc)) {
      matchedKeeps = arDocToKeep.get(cleanDoc);
      isVlookupMatch = false;
    }

    if (matchedKeeps.length > 0) {
      const bestKeep = pickBestKeep(matchedKeeps, items[0]['Posting Date']);
      let totalItems = items.length;
      let auditedItemsCount = 0;
      let sosmedItems = 0;
      let missingItems = 0;
      let missingTotalValue = 0;
      let totalDocValue = 0;

      items.forEach(it => {
        const lineTotal = Number(it['Harga Jual']) || 0;
        totalDocValue += lineTotal;

        const isExempt = isItemExemptFromSosmed(it);
        const hasSosmed = isItemInputSosmed(it);

        if (isExempt) {
          // TAS GADGETMART 25x25 tidak perlu diinput sosmed.
          // Jangan dianggap sebagai missing/wajib sosmed!
          if (hasSosmed) {
            sosmedItems++;
          }
          return;
        }

        auditedItemsCount++;
        if (hasSosmed) {
          sosmedItems++;
        } else {
          missingItems++;
          missingTotalValue += lineTotal;
        }
      });

      // Status AR:
      // Hijau (ALL_SOSMED): Sudah terinput semua (100% sosmed)
      // Orange (PARTIAL_MISSING): Terinput sebagian
      // Merah (ALL_MISSING): Tidak terinput sama sekali
      let arStatus = 'ALL_SOSMED';
      if (missingItems > 0) {
        arStatus = (sosmedItems > 0) ? 'PARTIAL_MISSING' : 'ALL_MISSING';
      } else {
        arStatus = 'ALL_SOSMED';
      }

      // Resolve VLOOKUP AR info
      let vlookupARDoc = isVlookupMatch ? doc : '-';
      let vlookupARDate = isVlookupMatch ? items[0]['Posting Date'] : null;
      let vlookupARCabang = isVlookupMatch ? items[0]['Cabang'] : '-';
      let vlookupARItems = isVlookupMatch ? items : [];

      if (!isVlookupMatch) {
        const keepNormWA = normalizePhone(bestKeep['Nomor WA']);
        if (keepNormWA && phoneToAR.has(keepNormWA)) {
          vlookupARDoc = phoneToAR.get(keepNormWA);
          if (docMap.has(vlookupARDoc)) {
            vlookupARDate = docMap.get(vlookupARDoc)[0]['Posting Date'];
            vlookupARCabang = docMap.get(vlookupARDoc)[0]['Cabang'];
            vlookupARItems = docMap.get(vlookupARDoc);
          }
        }
      }

      // Resolve REPORT AR info
      const repARRaw = String(bestKeep['REPORT AR'] || '').replace(/\D/g, '');
      let reportARDoc = repARRaw || (!isVlookupMatch ? doc : '-');
      let reportARDate = null;
      let reportARCabang = '-';
      let reportARItems = [];

      if (reportARDoc && reportARDoc !== '-' && docMap.has(reportARDoc)) {
        reportARDate = docMap.get(reportARDoc)[0]['Posting Date'];
        reportARCabang = docMap.get(reportARDoc)[0]['Cabang'];
        reportARItems = docMap.get(reportARDoc);
      } else if (!isVlookupMatch) {
        reportARDate = items[0]['Posting Date'];
        reportARCabang = items[0]['Cabang'];
        reportARItems = items;
      }

      // Parse ISO dates for filtering
      const vlookupARDateIso = parseDateToISO(vlookupARDate);
      const reportARDateIso = parseDateToISO(reportARDate);

      // Parse Keep Timestamp for sorting
      const keepTimestampEpoch = parseKeepTimestampToEpoch(bestKeep['Timestamp']);

      rawAuditARList.push({
        doc,
        postingDate: items[0]['Posting Date'],
        cabang: items[0]['Cabang'],
        totalItems,
        sosmedItems,
        missingItems,
        missingTotalValue,
        totalDocValue,
        status: arStatus,
        keep: bestKeep,
        keepGSheetsIndex: (bestKeep && typeof bestKeep._gsheetsIndex === 'number') ? bestKeep._gsheetsIndex : 9999999,
        items: items,
        vlookupARDoc,
        vlookupARDate,
        vlookupARCabang,
        vlookupARItems,
        vlookupARDateIso,
        reportARDoc,
        reportARDate,
        reportARCabang,
        reportARItems,
        reportARDateIso,
        keepTimestampEpoch
      });
    }
  });

  // Urutkan tanggal format keep dari yang terlama sampai terbaru (persis urutan baris Google Sheets dari atas ke bawah)
  rawAuditARList.sort((a, b) => {
    // 1. Urutkan berdasarkan index baris asli Google Sheets (baris terlama di atas, terbaru di bawah)
    const idxA = (typeof a.keepGSheetsIndex === 'number') ? a.keepGSheetsIndex : 9999999;
    const idxB = (typeof b.keepGSheetsIndex === 'number') ? b.keepGSheetsIndex : 9999999;
    if (idxA !== idxB) {
      return idxA - idxB;
    }
    // 2. Fallback berdasarkan timestamp epoch (terlama ke terbaru)
    return (a.keepTimestampEpoch || 0) - (b.keepTimestampEpoch || 0);
  });

  if (pill) {
    pill.innerHTML = `<span>📁 ${rawAuditARList.length} AR Ditemukan</span>`;
  }

  populateAuditSosmedDropdown();
  applyAuditColStyles();
  applyAuditFilters();
}

function populateAuditSosmedDropdown() {
  const select = document.getElementById('auditFilterSosmed');
  if (!select) return;

  const currentVal = select.value;
  const uniqueCodes = new Set();
  rawAuditARList.forEach(a => {
    const code = String(a.keep['Kode Sosmed'] || '').trim();
    if (code && code !== 'undefined') uniqueCodes.add(code);
  });

  const sorted = Array.from(uniqueCodes).sort();
  select.innerHTML = '<option value="ALL">Semua Kode Sosmed</option>' +
    sorted.map(c => `<option value="${c}">${c}</option>`).join('');

  if (currentVal && sorted.includes(currentVal)) {
    select.value = currentVal;
  }
}

function applyAuditFilters() {
  if (!rawAuditARList) return;

  const periodeEl = document.getElementById('auditFilterPeriode');
  const sosmedEl = document.getElementById('auditFilterSosmed');
  const tglVlookupEl = document.getElementById('auditFilterTanggalVlookup');
  const tglReportEl = document.getElementById('auditFilterTanggalReport');
  const statusEl = document.getElementById('auditFilterStatus');
  const searchEl = document.getElementById('auditSearchInput');

  const periodeVal = periodeEl ? periodeEl.value : 'ALL';
  const sosmedVal = sosmedEl ? sosmedEl.value : 'ALL';
  const tanggalVlookupVal = tglVlookupEl ? tglVlookupEl.value : '';
  const tanggalReportVal = tglReportEl ? tglReportEl.value : '';
  const statusVal = statusEl ? statusEl.value : 'ALL';
  const keyword = (searchEl ? searchEl.value : '').trim().toLowerCase();

  filteredAuditARList = rawAuditARList.filter(r => {
    // 1. Periode filter
    if (periodeVal !== 'ALL') {
      const p = String(r.keep['PERIODE'] || '').trim().toLowerCase();
      if (p !== periodeVal.toLowerCase()) return false;
    }

    // 2. Sosmed filter
    if (sosmedVal !== 'ALL') {
      const s = String(r.keep['Kode Sosmed'] || '').trim();
      if (s !== sosmedVal) return false;
    }

    // 3. Filter Tanggal Faktur AR (VLOOKUP)
    if (tanggalVlookupVal) {
      if (r.vlookupARDateIso !== tanggalVlookupVal) return false;
    }

    // 4. Filter Tanggal Faktur Report AR
    if (tanggalReportVal) {
      if (r.reportARDateIso !== tanggalReportVal) return false;
    }

    // 5. Status AR filter
    if (statusVal !== 'ALL') {
      if (statusVal === 'ANY_MISSING') {
        if (r.status !== 'PARTIAL_MISSING' && r.status !== 'ALL_MISSING') return false;
      } else if (r.status !== statusVal) {
        return false;
      }
    }

    // 6. Keyword search
    if (keyword) {
      const itemsText = r.items.map(it => it['Dscription'] || '').join(' ');
      const arRemarks = (r.items && r.items[0] && r.items[0]['Remarks']) || '';
      const combined = `${r.doc} ${r.vlookupARDoc || ''} ${r.reportARDoc || ''} ${r.keep['Nama'] || ''} ${r.keep['Nomor WA'] || ''} ${r.keep['Barang Keep'] || ''} ${r.keep['Catatan'] || ''} ${arRemarks} ${r.cabang || ''} ${itemsText}`.toLowerCase();
      if (!combined.includes(keyword)) return false;
    }

    return true;
  });

  // Calculate KPI counters (Hijau: Sudah Terinput, Orange: Sebagian, Merah: Tidak Terinput)
  let allSosmedCount = 0;
  let partialMissingCount = 0;
  let allMissingCount = 0;
  let totalPotensiRp = 0;

  filteredAuditARList.forEach(r => {
    if (r.status === 'ALL_SOSMED') allSosmedCount++;
    else if (r.status === 'PARTIAL_MISSING') partialMissingCount++;
    else if (r.status === 'ALL_MISSING') allMissingCount++;
    totalPotensiRp += (r.missingTotalValue || 0);
  });

  const totalEl = document.getElementById('auditTotalCount');
  const allSosmedEl = document.getElementById('auditAllSosmedCount');
  const partialEl = document.getElementById('auditPartialMissingCount');
  const allMissingEl = document.getElementById('auditAllMissingCount');
  const potensiEl = document.getElementById('auditTotalPotensiRp');

  if (totalEl) totalEl.innerText = filteredAuditARList.length.toLocaleString('id-ID');
  if (allSosmedEl) allSosmedEl.innerText = allSosmedCount.toLocaleString('id-ID');
  if (partialEl) partialEl.innerText = partialMissingCount.toLocaleString('id-ID');
  if (allMissingEl) allMissingEl.innerText = allMissingCount.toLocaleString('id-ID');
  if (potensiEl) potensiEl.innerText = formatRupiah(totalPotensiRp);

  let activeAuditCount = 0;
  if (periodeEl && periodeEl.value !== 'ALL') activeAuditCount++;
  if (sosmedEl && sosmedEl.value !== 'ALL') activeAuditCount++;
  if (tglVlookupEl && tglVlookupEl.value) activeAuditCount++;
  if (tglReportEl && tglReportEl.value) activeAuditCount++;
  if (statusEl && statusEl.value !== 'ALL') activeAuditCount++;
  if (searchEl && searchEl.value.trim()) activeAuditCount++;
  const badgeAudit = document.getElementById('auditFilterActiveCount');
  if (badgeAudit) badgeAudit.innerText = activeAuditCount;

  auditCurrentPage = 1;
  renderAuditPage();
}

function resetAuditFilters() {
  const periodeEl = document.getElementById('auditFilterPeriode');
  const sosmedEl = document.getElementById('auditFilterSosmed');
  const tglVlookupEl = document.getElementById('auditFilterTanggalVlookup');
  const tglReportEl = document.getElementById('auditFilterTanggalReport');
  const statusEl = document.getElementById('auditFilterStatus');
  const searchEl = document.getElementById('auditSearchInput');

  if (periodeEl) {
    periodeEl.value = periodeEl.options.length > 1 ? periodeEl.options[1].value : 'ALL';
  }
  if (sosmedEl) sosmedEl.value = 'ALL';
  if (tglVlookupEl) tglVlookupEl.value = '';
  if (tglReportEl) tglReportEl.value = '';
  if (statusEl) statusEl.value = 'ALL';
  if (searchEl) searchEl.value = '';

  applyAuditFilters();
}

function changeAuditPage(delta) {
  const maxPage = Math.max(1, Math.ceil(filteredAuditARList.length / AUDIT_PAGE_SIZE));
  const newPage = auditCurrentPage + delta;
  if (newPage >= 1 && newPage <= maxPage) {
    auditCurrentPage = newPage;
    renderAuditPage();
  }
}

// ==========================================================================
// STATUS KEEP (GOOGLE SHEETS LIVE UPDATE ENGINE)
// ==========================================================================
const KEEP_STATUS_OPTIONS = [
  { value: '', label: '- Kosong -' },
  { value: 'WIN', label: 'WIN' },
  { value: 'NEED TO CHECK', label: 'NEED TO CHECK' },
  { value: 'LOSE', label: 'LOSE' },
  { value: 'BELUM DIINPUT', label: 'BELUM DIINPUT' },
  { value: 'DOUBLE KEEP', label: 'DOUBLE KEEP' },
  { value: 'ON PROGRESS LOW', label: 'ON PROGRESS LOW' },
  { value: 'ON PROGRESS MEDIUM', label: 'ON PROGRESS MEDIUM' },
  { value: 'ON PROGRESS HOT', label: 'ON PROGRESS HOT' },
  { value: 'TAKE OUT', label: 'TAKE OUT' }
];

function getKeepStatusClass(status) {
  const s = String(status || '').trim().toUpperCase();
  if (!s || s === '-' || s === 'KOSONG') return 'status-empty';
  if (s === 'WIN') return 'status-win';
  if (s === 'NEED TO CHECK') return 'status-need-to-check';
  if (s === 'LOSE') return 'status-lose';
  if (s === 'BELUM DIINPUT' || s === 'BELUM INPUT') return 'status-belum-diinput';
  if (s === 'DOUBLE KEEP') return 'status-double-keep';
  if (s === 'ON PROGRESS LOW') return 'status-on-progress-low';
  if (s === 'ON PROGRESS MEDIUM') return 'status-on-progress-medium';
  if (s === 'ON PROGRESS HOT') return 'status-on-progress-hot';
  if (s === 'TAKE OUT') return 'status-take-out';
  return 'status-empty';
}

function getAuditRowClassByKeepStatus(keepStatus, arStatus) {
  const s = String(keepStatus || '').trim().toUpperCase();
  if (s === 'WIN') return 'audit-row-win';
  if (s === 'NEED TO CHECK') return 'audit-row-need-to-check';
  if (s === 'LOSE') return 'audit-row-lose';
  if (s === 'BELUM DIINPUT' || s === 'BELUM INPUT') return 'audit-row-belum-diinput';
  if (s === 'DOUBLE KEEP') return 'audit-row-double-keep';
  if (s === 'ON PROGRESS LOW') return 'audit-row-on-progress-low';
  if (s === 'ON PROGRESS MEDIUM') return 'audit-row-on-progress-medium';
  if (s === 'ON PROGRESS HOT') return 'audit-row-on-progress-hot';
  if (s === 'TAKE OUT') return 'audit-row-take-out';

  // Jika status keep tidak ada / kosong: gunakan indikator outline sosmed AR
  if (arStatus === 'ALL_SOSMED') return 'audit-row-sosmed';
  if (arStatus === 'PARTIAL_MISSING') return 'audit-row-partial';
  return 'audit-row-missing';
}

async function updateKeepStatusInGoogleSheets(rawWA, newStatus, keepId = null) {
  const normWA = String(rawWA || '').replace(/\D/g, '');
  if (!normWA && !keepId) throw new Error('Nomor WhatsApp atau ID tidak valid untuk update status.');

  // Langsung simpan ke Database SQL DCMS secara realtime
  const res = await fetch('/api/update-keep-status', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id: keepId,
      nomorWA: normWA,
      status: newStatus
    }),
    signal: AbortSignal.timeout ? AbortSignal.timeout(8000) : undefined
  });

  if (res.ok) {
    const result = await res.json();
    if (result && result.success) {
      return result;
    }
    throw new Error((result && result.message) || 'Gagal menyimpan status ke database.');
  }

  throw new Error(`HTTP Error ${res.status}: Gagal menyimpan status ke server database.`);
}

// Antrean update status Database DCMS di background (non-blocking)
const pendingKeepStatusUpdates = new Map();

async function triggerBackgroundKeepStatusSync(rawWA, newVal, oldVal, onRollback, keepId = null) {
  const normWA = String(rawWA || '').replace(/\D/g, '');
  if (!normWA && !keepId) return;

  const syncKey = keepId ? `id_${keepId}` : normWA;
  pendingKeepStatusUpdates.set(syncKey, newVal);

  try {
    await updateKeepStatusInGoogleSheets(rawWA, newVal, keepId);
    pendingKeepStatusUpdates.delete(syncKey);
  } catch (err) {
    console.error('Realtime update status keep gagal:', err);
    if (pendingKeepStatusUpdates.get(syncKey) === newVal) {
      pendingKeepStatusUpdates.delete(syncKey);
      if (typeof onRollback === 'function') {
        onRollback(err);
      }
    }
  }
}

async function updateReportARInGoogleSheets(rawWA, arDoc, keepId = null) {
  const normWA = String(rawWA || '').replace(/\D/g, '');
  if (!normWA && !keepId) throw new Error('Nomor WhatsApp atau ID tidak valid untuk update Report AR.');

  // Langsung simpan ke Database SQL DCMS secara realtime
  const res = await fetch('/api/update-report-ar', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id: keepId,
      nomorWA: normWA,
      arDoc: arDoc
    }),
    signal: AbortSignal.timeout ? AbortSignal.timeout(8000) : undefined
  });

  if (res.ok) {
    const result = await res.json();
    if (result && result.success) {
      return result;
    }
    throw new Error((result && result.message) || 'Gagal menyimpan nomor AR ke database.');
  }

  throw new Error(`HTTP Error ${res.status}: Gagal menyimpan nomor AR ke server database.`);
}

// Antrean update REPORT AR Database DCMS di background (non-blocking)
async function triggerBackgroundReportARSync(rawWA, newAR, oldAR, onRollback, keepId = null) {
  const normWA = String(rawWA || '').replace(/\D/g, '');
  if (!normWA && !keepId) return;

  try {
    await updateReportARInGoogleSheets(rawWA, newAR, keepId);
  } catch (err) {
    console.error('Realtime update Report AR gagal:', err);
    if (typeof onRollback === 'function') {
      onRollback(err);
    }
  }
}


function handleTableKeepStatusChange(selectEl, rawWA, auditRowIdx) {
  if (!selectEl) return;
  const oldVal = selectEl.getAttribute('data-prev') || selectEl.value;
  const newVal = selectEl.value;
  if (newVal === oldVal) return;

  const originalClass = selectEl.className;
  const tr = selectEl.closest('tr');
  const arObj = filteredAuditARList[auditRowIdx];
  const arSt = arObj ? arObj.status : '';
  const originalRowClass = tr ? tr.className : '';

  // 1. OPTIMISTIC UI UPDATE (0ms) - Seketika ubah dropdown & warna blok baris tabel tanpa freeze
  selectEl.setAttribute('data-prev', newVal);
  selectEl.className = `select-status-keep ${getKeepStatusClass(newVal)}`;
  if (tr) {
    tr.className = getAuditRowClassByKeepStatus(newVal, arSt);
  }

  // 2. Perbarui state memori seketika (0ms)
  if (filteredAuditARList[auditRowIdx] && filteredAuditARList[auditRowIdx].keep) {
    filteredAuditARList[auditRowIdx].keep['Status'] = newVal;
  }
  const cleanWA = String(rawWA).replace(/\D/g, '');
  if (typeof rawKeepRows !== 'undefined' && Array.isArray(rawKeepRows)) {
    rawKeepRows.forEach(k => {
      if (String(k['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
        k['Status'] = newVal;
      }
    });
  }
  if (typeof rawAuditARList !== 'undefined' && Array.isArray(rawAuditARList)) {
    rawAuditARList.forEach(a => {
      if (a.keep && String(a.keep['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
        a.keep['Status'] = newVal;
      }
    });
  }

  const toastLabel = newVal ? `ke "${newVal}"` : 'dikosongkan';
  showSaveToast(`⚡ Status keep WA ${formatPhoneDisplay(rawWA)} diubah ${toastLabel}! (Tersimpan di background)`);

  // 3. Kirim ke Google Sheets di background (Rollback otomatis jika gagal)
  triggerBackgroundKeepStatusSync(rawWA, newVal, oldVal, (err) => {
    alert(`⚠️ Gagal menyimpan status ke Google Sheets:\n${err.message}\nStatus dikembalikan ke sebelumnya.`);
    selectEl.value = oldVal;
    selectEl.setAttribute('data-prev', oldVal);
    selectEl.className = originalClass;
    if (tr) tr.className = originalRowClass;
    if (filteredAuditARList[auditRowIdx] && filteredAuditARList[auditRowIdx].keep) {
      filteredAuditARList[auditRowIdx].keep['Status'] = oldVal;
    }
  });
}

function handleModalKeepStatusChange(selectEl, rawWA) {
  if (!selectEl) return;
  const oldVal = selectEl.getAttribute('data-prev') || selectEl.value;
  const newVal = selectEl.value;
  if (newVal === oldVal) return;

  const originalClass = selectEl.className;

  // 1. OPTIMISTIC UI UPDATE (0ms)
  selectEl.setAttribute('data-prev', newVal);
  selectEl.className = `select-status-keep ${getKeepStatusClass(newVal)}`;

  if (currentModalKeep) {
    currentModalKeep['Status'] = newVal;
  }
  const cleanWA = String(rawWA).replace(/\D/g, '');
  if (typeof rawAuditARList !== 'undefined' && Array.isArray(rawAuditARList)) {
    rawAuditARList.forEach(a => {
      if (a.keep && String(a.keep['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
        a.keep['Status'] = newVal;
      }
    });
  }
  if (typeof rawKeepRows !== 'undefined' && Array.isArray(rawKeepRows)) {
    rawKeepRows.forEach(k => {
      if (String(k['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
        k['Status'] = newVal;
      }
    });
  }

  showSaveToast(`⚡ Status keep WA ${formatPhoneDisplay(rawWA)} diubah ke "${newVal}"! (Tersimpan di background)`);
  if (typeof renderAuditPage === 'function') {
    renderAuditPage();
  }

  // 2. Kirim ke Google Sheets di background
  triggerBackgroundKeepStatusSync(rawWA, newVal, oldVal, (err) => {
    alert(`⚠️ Gagal menyimpan status ke Google Sheets:\n${err.message}\nStatus dikembalikan ke sebelumnya.`);
    selectEl.value = oldVal;
    selectEl.setAttribute('data-prev', oldVal);
    selectEl.className = originalClass;
    if (currentModalKeep) {
      currentModalKeep['Status'] = oldVal;
    }
    if (typeof renderAuditPage === 'function') {
      renderAuditPage();
    }
  });
}

// ==========================================================================
// INLINE AR INPUT & EDIT ENGINE (TAB AUDIT AR & TAB FORMAT KEEP)
// ==========================================================================
function startEditAuditReportAR(containerEl, rawWA, auditRowIdx) {
  if (!containerEl) return;
  const currentAR = containerEl.getAttribute('data-current-ar') || '';
  
  containerEl.innerHTML = `
    <input type="text" class="input-inline-ar" value="${currentAR}" 
      placeholder="No. AR..." 
      onkeydown="if(event.key==='Enter') saveInlineAuditReportAR(this, '${rawWA}', ${auditRowIdx}); else if(event.key==='Escape') cancelInlineAuditReportAR(this, '${currentAR}', ${auditRowIdx});"
      onblur="saveInlineAuditReportAR(this, '${rawWA}', ${auditRowIdx})"
    />
  `;
  const input = containerEl.querySelector('input');
  if (input) {
    input.focus();
    input.select();
  }
}

function saveInlineAuditReportAR(inputEl, rawWA, auditRowIdx) {
  const container = inputEl.parentElement;
  if (!container) return;
  const oldAR = container.getAttribute('data-current-ar') || '';
  const newAR = inputEl.value.trim().replace(/\D/g, '');

  if (newAR === oldAR) {
    cancelInlineAuditReportAR(inputEl, oldAR, auditRowIdx);
    return;
  }

  // 1. Optimistic Update Local Memory (0ms)
  const cleanWA = String(rawWA).replace(/\D/g, '');
  if (filteredAuditARList[auditRowIdx]) {
    filteredAuditARList[auditRowIdx].reportARDoc = newAR;
    if (filteredAuditARList[auditRowIdx].keep) {
      filteredAuditARList[auditRowIdx].keep['REPORT AR'] = newAR;
    }
    if (newAR && typeof docMap !== 'undefined' && docMap.has(newAR)) {
      filteredAuditARList[auditRowIdx].reportARItems = docMap.get(newAR);
      filteredAuditARList[auditRowIdx].reportARDate = filteredAuditARList[auditRowIdx].reportARItems[0]['Posting Date'];
      filteredAuditARList[auditRowIdx].reportARCabang = filteredAuditARList[auditRowIdx].reportARItems[0]['Cabang'];
    }
  }

  if (typeof rawKeepRows !== 'undefined' && Array.isArray(rawKeepRows)) {
    rawKeepRows.forEach(k => {
      if (String(k['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
        k['REPORT AR'] = newAR;
      }
    });
  }

  // 2. Render ulang baris seketika agar status badge langsung update (0ms)
  renderAuditPage();

  // 3. Simpan ke Google Sheets di background (non-blocking)
  triggerBackgroundReportARSync(rawWA, newAR, oldAR, () => {
    // Rollback jika error
    if (filteredAuditARList[auditRowIdx]) {
      filteredAuditARList[auditRowIdx].reportARDoc = oldAR;
      if (filteredAuditARList[auditRowIdx].keep) {
        filteredAuditARList[auditRowIdx].keep['REPORT AR'] = oldAR;
      }
    }
    renderAuditPage();
  });
}

function cancelInlineAuditReportAR(inputEl, oldAR, auditRowIdx) {
  renderAuditPage();
}

function startEditKeepReportAR(containerEl, rawWA, keepRowIdx) {
  if (!containerEl) return;
  const currentAR = containerEl.getAttribute('data-current-ar') || '';
  
  containerEl.innerHTML = `
    <input type="text" class="input-inline-ar" value="${currentAR}" 
      placeholder="No. AR..." 
      onkeydown="if(event.key==='Enter') saveInlineKeepReportAR(this, '${rawWA}', ${keepRowIdx}); else if(event.key==='Escape') cancelInlineKeepReportAR(this, '${currentAR}', ${keepRowIdx});"
      onblur="saveInlineKeepReportAR(this, '${rawWA}', ${keepRowIdx})"
    />
  `;
  const input = containerEl.querySelector('input');
  if (input) {
    input.focus();
    input.select();
  }
}

function saveInlineKeepReportAR(inputEl, rawWA, keepRowIdx) {
  const container = inputEl.parentElement;
  if (!container) return;
  const oldAR = container.getAttribute('data-current-ar') || '';
  const newAR = inputEl.value.trim().replace(/\D/g, '');

  if (newAR === oldAR) {
    cancelInlineKeepReportAR(inputEl, oldAR, keepRowIdx);
    return;
  }

  // 1. Optimistic Update Local Memory (0ms)
  const cleanWA = String(rawWA).replace(/\D/g, '');
  if (filteredKeepRows[keepRowIdx]) {
    filteredKeepRows[keepRowIdx]['REPORT AR'] = newAR;
  }

  if (typeof rawKeepRows !== 'undefined' && Array.isArray(rawKeepRows)) {
    rawKeepRows.forEach(k => {
      if (String(k['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
        k['REPORT AR'] = newAR;
      }
    });
  }

  if (typeof rawAuditARList !== 'undefined' && Array.isArray(rawAuditARList)) {
    rawAuditARList.forEach(a => {
      if (a.keep && String(a.keep['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
        a.reportARDoc = newAR;
        a.keep['REPORT AR'] = newAR;
        if (newAR && typeof docMap !== 'undefined' && docMap.has(newAR)) {
          a.reportARItems = docMap.get(newAR);
          a.reportARDate = a.reportARItems[0]['Posting Date'];
          a.reportARCabang = a.reportARItems[0]['Cabang'];
        }
      }
    });
  }

  // 2. Render ulang halaman Format Keep (0ms)
  renderKeepPage();

  // 3. Simpan ke Google Sheets di background (non-blocking)
  triggerBackgroundReportARSync(rawWA, newAR, oldAR, () => {
    if (filteredKeepRows[keepRowIdx]) {
      filteredKeepRows[keepRowIdx]['REPORT AR'] = oldAR;
    }
    renderKeepPage();
  });
}

function cancelInlineKeepReportAR(inputEl, oldAR, keepRowIdx) {
  renderKeepPage();
}

// Helper mandiri untuk menghitung badge status dokumen faktur
function getDocBadgeInfo(docItems) {
  if (!docItems || docItems.length === 0) {
    return { badgeClass: 'badge-status-ink', badgeIcon: '❓', statusLabel: 'Data Item Faktur Tidak Ditemukan' };
  }
  let sosmedCount = 0;
  let missingCount = 0;
  docItems.forEach(it => {
    const isExempt = isItemExemptFromSosmed(it);
    const hasSosmed = isItemInputSosmed(it);
    if (isExempt) {
      if (hasSosmed) sosmedCount++;
      return;
    }
    if (hasSosmed) sosmedCount++;
    else missingCount++;
  });

  if (missingCount === 0) {
    return { badgeClass: 'badge-status-green', badgeIcon: '✅', statusLabel: 'Sudah Terinput Semua (100% Sosmed)' };
  } else if (sosmedCount > 0) {
    return { badgeClass: 'badge-status-orange', badgeIcon: '⚠️', statusLabel: 'Terinput Sebagian' };
  } else {
    return { badgeClass: 'badge-status-red', badgeIcon: '❌', statusLabel: 'Tidak Terinput Sosmed Sama Sekali' };
  }
}

function renderAuditPage() {
  const tbody = document.getElementById('auditTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  const total = filteredAuditARList.length;
  const maxPage = Math.max(1, Math.ceil(total / AUDIT_PAGE_SIZE));
  const startIdx = (auditCurrentPage - 1) * AUDIT_PAGE_SIZE;
  const endIdx = Math.min(startIdx + AUDIT_PAGE_SIZE, total);
  const pageRows = filteredAuditARList.slice(startIdx, endIdx);

  // Pagination info
  const paginationInfoEl = document.getElementById('auditPaginationInfo');
  if (paginationInfoEl) {
    paginationInfoEl.innerText = total > 0
      ? `Menampilkan ${startIdx + 1} - ${endIdx} dari total ${total.toLocaleString('id-ID')} Dokumen AR`
      : 'Tidak ada dokumen AR yang sesuai filter';
  }
  const curPageTextEl = document.getElementById('auditCurrentPageText');
  if (curPageTextEl) curPageTextEl.innerText = `Halaman ${auditCurrentPage} / ${maxPage}`;
  const btnPrev = document.getElementById('btnAuditPrev');
  if (btnPrev) btnPrev.disabled = auditCurrentPage <= 1;
  const btnNext = document.getElementById('btnAuditNext');
  if (btnNext) btnNext.disabled = auditCurrentPage >= maxPage;

  if (pageRows.length === 0) {
    tbody.innerHTML = `<tr><td colspan="12" class="text-center" style="padding: 30px; color: var(--text-muted);">Tidak ada data tracking AR yang sesuai filter.</td></tr>`;
    return;
  }

  pageRows.forEach((r, i) => {
    const globalIdx = startIdx + i + 1;
    const auditRowIdx = startIdx + i;
    const keepObj = r.keep || {};
    const rawWA = String(keepObj['Nomor WA'] || '').replace(/\D/g, '');
    const normWA = normalizePhone(rawWA);
    const displayWA = formatPhoneDisplay(rawWA);

    const waHtml = (normWA && normWA.length >= 8) ? `
      <div style="display:flex; align-items:center; gap:5px;">
        <span class="font-bold">${displayWA}</span>
        <a href="https://wa.me/${normWA}" target="_blank" class="btn-whatsapp" style="padding:1px 6px; font-size:0.68rem; border-radius:4px;" title="Chat customer ini di WhatsApp">
          💬 WA
        </a>
      </div>
    ` : (displayWA || '-');

    // Notes HTML
    const keepCatatan = String(keepObj['Catatan'] || '').trim();
    const arRemarks = String((r.items && r.items[0] && r.items[0]['Remarks']) || '').trim();

    let catatanHtml = '-';
    if (keepCatatan && arRemarks && keepCatatan.toLowerCase() !== arRemarks.toLowerCase() && keepCatatan !== '-' && arRemarks !== '-') {
      catatanHtml = `
        <div style="font-size:0.73rem; line-height:1.25; color:#0f172a; word-break:break-word;">${keepCatatan}</div>
        <div style="font-size:0.68rem; color:var(--text-muted); margin-top:2px; word-break:break-word;"><em>Kasir: ${arRemarks}</em></div>
      `;
    } else if (keepCatatan && keepCatatan !== '-') {
      catatanHtml = `<div style="font-size:0.73rem; line-height:1.25; color:#0f172a; word-break:break-word;">${keepCatatan}</div>`;
    } else if (arRemarks && arRemarks !== '-') {
      catatanHtml = `<div style="font-size:0.70rem; line-height:1.25; color:var(--text-muted); word-break:break-word;"><em>Kasir: ${arRemarks}</em></div>`;
    }

    // Status Keep Dropdown
    const rawStatus = keepObj['Status'];
    const currentStatus = (rawStatus && String(rawStatus).trim() !== '-') ? String(rawStatus).trim().toUpperCase() : '';
    const statusSelectClass = getKeepStatusClass(currentStatus);

    let statusOptionsHtml = '';
    if (currentStatus && !KEEP_STATUS_OPTIONS.some(o => o.value.toUpperCase() === currentStatus)) {
      statusOptionsHtml += `<option value="${currentStatus}" selected>${currentStatus}</option>`;
    }
    KEEP_STATUS_OPTIONS.forEach(opt => {
      const isSel = (opt.value.toUpperCase() === currentStatus);
      statusOptionsHtml += `<option value="${opt.value}" ${isSel ? 'selected' : ''}>${opt.label}</option>`;
    });

    const statusDropdownHtml = (rawWA && rawWA.length >= 8) ? `
      <select class="select-status-keep ${statusSelectClass}" data-prev="${currentStatus}" onchange="handleTableKeepStatusChange(this, '${rawWA}', ${auditRowIdx})" title="Klik untuk mengubah status follow-up customer ini (otomatis tersimpan ke Google Sheets)">
        ${statusOptionsHtml}
      </select>
    ` : `<span class="kpi-badge badge-ink">-</span>`;

    // Evaluasi Badge Masing-Masing Dokumen Secara Mandiri (Akurat 100%)
    const repBadge = getDocBadgeInfo(r.reportARItems && r.reportARItems.length > 0 ? r.reportARItems : (r.reportARDoc === r.doc ? r.items : []));
    const vlookBadge = getDocBadgeInfo(r.vlookupARItems && r.vlookupARItems.length > 0 ? r.vlookupARItems : (r.vlookupARDoc === r.doc ? r.items : []));

    // Status Warna Baris Tabel
    const effectiveStatus = (r.reportARDoc && r.reportARDoc !== '-') ? repBadge.statusLabel : r.status;
    const rowStatusClass = getAuditRowClassByKeepStatus(currentStatus, effectiveStatus);

    // Report AR cell (Dengan Fitur Input & Edit Cepat)
    const reportARDocDisplay = (r.reportARDoc && r.reportARDoc !== '-')
      ? `<div class="cell-report-ar-wrapper" data-wa="${rawWA}" data-audit-idx="${auditRowIdx}" data-current-ar="${r.reportARDoc}">
           <span class="badge-status-link ${repBadge.badgeClass}" onclick="openARDetailModal('${r.reportARDoc}', filteredAuditARList[${auditRowIdx}].keep, 'REPORT AR')" title="Status: ${repBadge.statusLabel}. Klik untuk cek detail Dokumen Report AR">
             <span>${repBadge.badgeIcon}</span> ${r.reportARDoc}
           </span>
           <button type="button" class="btn-edit-inline-ar" onclick="event.stopPropagation(); startEditAuditReportAR(this.parentElement, '${rawWA}', ${auditRowIdx})" title="Ubah nomor AR">✏️</button>
         </div>`
      : `<div class="cell-report-ar-wrapper" data-wa="${rawWA}" data-audit-idx="${auditRowIdx}" data-current-ar="">
           <button type="button" class="btn-input-new-ar" onclick="startEditAuditReportAR(this.parentElement, '${rawWA}', ${auditRowIdx})" title="Klik untuk menginput nomor AR">+ Input AR</button>
         </div>`;

    const reportARDateDisplay = r.reportARDate
      ? `${formatCrosellingDate(r.reportARDate)} ${r.reportARCabang || ''}`.trim()
      : '-';

    // Vlookup AR cell
    const vlookupARDocDisplay = (r.vlookupARDoc && r.vlookupARDoc !== '-')
      ? `<span class="badge-status-link ${vlookBadge.badgeClass}" onclick="openARDetailModal('${r.vlookupARDoc}', filteredAuditARList[${auditRowIdx}].keep, 'AR VLOOKUP')" title="Status: ${vlookBadge.statusLabel}. Klik untuk cek detail Dokumen AR VLOOKUP"><span>${vlookBadge.badgeIcon}</span> ${r.vlookupARDoc}</span>`
      : '-';

    const vlookupARDateDisplay = r.vlookupARDate
      ? `${formatCrosellingDate(r.vlookupARDate)} ${r.vlookupARCabang || ''}`.trim()
      : '-';

    const tr = document.createElement('tr');
    tr.className = rowStatusClass;
    if (currentStatus === 'LOSE' || currentStatus === 'TAKE OUT') {
      tr.classList.add('row-lose-tint');
    }
    tr.innerHTML = `
      <td class="audit-col-no" style="text-align: center;">${globalIdx}</td>
      <td class="audit-col-keep-date" style="text-align: center; font-weight: 600; white-space: nowrap;">${formatKeepDisplayDate(keepObj['Timestamp'])}</td>
      <td class="audit-col-cust text-left" style="color: #0f172a; word-break: break-word;">
        <div class="font-bold">${keepObj['Nama'] || '-'}</div>
        ${(normWA && normWA.length >= 8) ? `
          <a href="https://wa.me/${normWA}" target="_blank" style="font-size:0.75rem; color:#64748b; text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
            📞 ${displayWA}
          </a>
        ` : `<div style="font-size:0.75rem; color:#64748b;">${displayWA || '-'}</div>`}
      </td>
      <td class="audit-col-sosmed" style="text-align: center;">
        <span class="badge-sosmed-pill">${keepObj['Kode Sosmed'] || '-'}</span>
      </td>
      <td class="audit-col-keep text-left font-bold" style="color: #1e293b; word-break: break-word;">${keepObj['Barang Keep'] || '-'}</td>
      <td class="audit-col-notes text-left" style="word-break: break-word; font-size: 0.78rem;">${catatanHtml}</td>
      <td class="audit-col-status-keep" style="text-align: center;">${statusDropdownHtml}</td>
      <td class="audit-col-report-ar" style="text-align: center;">${reportARDocDisplay}</td>
      <td class="audit-col-report-date" style="text-align: center; font-size: 0.76rem; white-space: nowrap;">${reportARDateDisplay}</td>
      <td class="audit-col-vlookup-ar" style="text-align: center;">${vlookupARDocDisplay}</td>
      <td class="audit-col-vlookup-date" style="text-align: center; font-size: 0.76rem; white-space: nowrap;">${vlookupARDateDisplay}</td>
    `;
    tbody.appendChild(tr);
  });
}

function openARDetailModalFromAudit(auditIndex) {
  const auditItem = filteredAuditARList[auditIndex];
  if (!auditItem) return;
  openARDetailModal(auditItem.doc, auditItem.keep);
}

// ==========================================================================
// COLUMN HIDING ENGINE (TAMPILKAN / SEMBUNYIKAN KOLOM TRACKING AR)
// ==========================================================================
let auditHiddenCols = new Set(JSON.parse(localStorage.getItem('audit_hidden_cols') || '[]'));

function applyAuditColStyles() {
  let styleEl = document.getElementById('auditDynamicColStyles');
  if (!styleEl) {
    styleEl = document.createElement('style');
    styleEl.id = 'auditDynamicColStyles';
    document.head.appendChild(styleEl);
  }
  const rules = Array.from(auditHiddenCols).map(col => `.${col} { display: none !important; }`).join('\n');
  styleEl.textContent = rules;

  const menu = document.getElementById('auditColDropdownMenu');
  if (menu) {
    const checkboxes = menu.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => {
      const col = cb.getAttribute('data-col');
      if (col) {
        cb.checked = !auditHiddenCols.has(col);
      }
    });
  }
}

function toggleAuditColumn(colClass, isVisible) {
  if (isVisible) {
    auditHiddenCols.delete(colClass);
  } else {
    auditHiddenCols.add(colClass);
  }
  localStorage.setItem('audit_hidden_cols', JSON.stringify(Array.from(auditHiddenCols)));
  applyAuditColStyles();
}

function resetAuditColumns() {
  auditHiddenCols.clear();
  localStorage.setItem('audit_hidden_cols', JSON.stringify([]));
  applyAuditColStyles();
}

function toggleAuditColDropdown(event) {
  if (event) {
    event.stopPropagation();
  }
  const menu = document.getElementById('auditColDropdownMenu');
  if (!menu) return;
  menu.style.display = (menu.style.display === 'none' || !menu.style.display) ? 'block' : 'none';
}

document.addEventListener('click', function(e) {
  const menu = document.getElementById('auditColDropdownMenu');
  const btn = document.getElementById('btnToggleAuditCols');
  if (menu && menu.style.display === 'block') {
    if (!menu.contains(e.target) && (!btn || !btn.contains(e.target))) {
      menu.style.display = 'none';
    }
  }
});

// ==========================================================================
// AUDIT AR ITEM CLIPBOARD EXPORT ENGINE (12 KOLOM EXCEL TANPA HEADER)
// ==========================================================================

function isHandphoneProduct(item) {
  if (!item) return false;
  const desc = String(item['Dscription'] || item['Item/Service Description'] || '').trim().toUpperCase();
  const grp = String(item['ItmsGrpNam'] || item['Group Name'] || item['Group'] || '').trim().toUpperCase();

  // Aksesoris / Jasa / Opr / Bukan Unit Handphone
  const isAcc = desc.startsWith('SG ') || desc.includes('TMPR') || desc.includes('CASE ') || 
                desc.includes('CASING') || desc.includes('TEMPERED') || desc.includes('HYDROGEL') || 
                desc.includes('ADAPTOR') || desc.includes('CABLE') || desc.includes('KABEL') || 
                desc.includes('CHARGER') || desc.includes('TWS') || desc.includes('HEADSET') || 
                desc.includes('EARBUDS') || desc.includes('POWERBANK') || desc.includes('SPEAKER') ||
                desc.includes('MEMORY') || desc.includes('STRAP') || desc.includes('HOLDER') ||
                desc.includes('KARTU') || desc.startsWith('TM ') || desc.includes('GMART CARE') ||
                desc.includes('SERVICE') || desc.includes('JASA') || grp.startsWith('ACC') || grp.startsWith('OPR');
  if (isAcc) return false;

  if (grp.startsWith('GAD') || grp.includes('GADGET') || grp.includes('HANDPHONE') || grp.includes('SMARTPHONE')) {
    return true;
  }

  const phoneKeywords = [
    'IPHONE', 'GALAXY', 'SAMSUNG R', 'SAMSUNG A', 'SAMSUNG S', 'SAMSUNG Z',
    'OPPO A', 'OPPO RENO', 'OPPO FIND', 'VIVO Y', 'VIVO V', 'VIVO X',
    'REDMI', 'XIAOMI', 'POCO', 'REALME', 'INFINIX', 'TECNO', 'ITEL',
    'SMARTPHONE', 'HANDPHONE'
  ];
  return phoneKeywords.some(k => desc.includes(k));
}

function getItemPotensiDenda(item) {
  return isHandphoneProduct(item) ? 'DENDA' : 'PASS';
}

function formatKeepTextForClipboard(keep) {
  if (!keep) return '-';
  const tagSender = keep['Sender'] ? `@${keep['Sender']}` : '';
  const nama = keep['Nama'] || '-';
  const rawWa = keep['Nomor WA'] || '-';
  const cleanWa = String(rawWa).replace(/\D/g, '');
  const barang = keep['Barang Keep'] || '-';
  const tglAmbil = keep['Tanggal Ambil'] || '-';
  const catatan = keep['Catatan'] || '';

  const lines = [];
  if (tagSender) lines.push(tagSender);
  lines.push('*Format Keep*');
  lines.push(`Nama: ${nama}`);
  lines.push(`Nomor WA: ${cleanWa}`);
  lines.push(`Barang keep: ${barang}`);
  lines.push(`Tanggal pengambilan: ${tglAmbil}`);
  lines.push(`Catatan: ${catatan}`);

  return lines.join('\n');
}

function generateAuditItemTSV(auditItem, item) {
  if (!auditItem || !item) return '';

  // 1. TANGGAL (format DD/MM/YYYY)
  const tgl = formatCrosellingDate(auditItem.postingDate);

  // 2. PERIODE (e.g. Oktober-26)
  const periode = (auditItem.keep && auditItem.keep['PERIODE']) 
    ? String(auditItem.keep['PERIODE']).trim() 
    : 'Oktober-26';

  // 3. NO AR
  const noAR = String(auditItem.doc || '').trim();

  // 4. NO.HP USER
  const rawHp = (auditItem.keep && auditItem.keep['Nomor WA']) 
    ? auditItem.keep['Nomor WA'] 
    : (auditItem.items && auditItem.items[0] && auditItem.items[0]['No HP Customer'] ? auditItem.items[0]['No HP Customer'] : '');
  const noHP = String(rawHp).replace(/\D/g, '');

  // 5. KODE SOSMED
  const kodeSosmed = (auditItem.keep && auditItem.keep['Kode Sosmed']) 
    ? String(auditItem.keep['Kode Sosmed']).trim() 
    : '-';

  // 6. PIC SOSMED
  let picSosmed = (auditItem.keep && auditItem.keep['Sender']) 
    ? String(auditItem.keep['Sender']).trim() 
    : '';
  if (!picSosmed && kodeSosmed && typeof getMasterSales === 'function') {
    const m = getMasterSales().find(x => kodeSosmed.includes(x.code) || kodeSosmed.includes(x.name));
    if (m) picSosmed = m.name;
  }
  if (!picSosmed) picSosmed = '-';

  // 7. CABANG
  const cabang = String(auditItem.cabang || (auditItem.items && auditItem.items[0] ? auditItem.items[0]['Cabang'] : '') || '-').trim();

  // 8. POTENSI DENDA (HP = DENDA, Aksesoris/lainnya = PASS)
  const potensiDenda = getItemPotensiDenda(item);

  // 9. BARANG (Item description yang dibeli di faktur AR)
  const namaBarang = String(item['Dscription'] || item['Item/Service Description'] || '-').trim();

  // 10. KEEP (Format Keep WhatsApp lengkap dibungkus kutip dua untuk in-cell newline di Excel)
  const rawKeepText = formatKeepTextForClipboard(auditItem.keep);
  const keepEscaped = `"${rawKeepText.replace(/"/g, '""')}"`;

  // 11. EVIDENCE (dikosongin sesuai permintaan)
  const evidence = '';

  // 12. AR SALES EMPLOYEE (tim toko offline yang menginput di AR)
  const salesOffline = String(item['SC/HL Code'] || item['SlpName'] || item['Sales Employee Name'] || item['User Input'] || '-').trim();

  return [
    tgl,
    periode,
    noAR,
    noHP,
    kodeSosmed,
    picSosmed,
    cabang,
    potensiDenda,
    namaBarang,
    keepEscaped,
    evidence,
    salesOffline
  ].join('\t');
}

async function writeTextToClipboard(text) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    return navigator.clipboard.writeText(text);
  }
  return new Promise((resolve, reject) => {
    try {
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.focus();
      ta.select();
      const ok = document.execCommand('copy');
      document.body.removeChild(ta);
      if (ok) resolve();
      else reject(new Error('execCommand copy failed'));
    } catch (e) {
      reject(e);
    }
  });
}

async function copyAuditItemToClipboard(auditIndex, itemIndex, btnElement = null) {
  const auditItem = filteredAuditARList[auditIndex];
  if (!auditItem || !auditItem.items || !auditItem.items[itemIndex]) {
    alert("Data item audit AR tidak ditemukan.");
    return;
  }

  const item = auditItem.items[itemIndex];
  const tsvText = generateAuditItemTSV(auditItem, item);

  try {
    await writeTextToClipboard(tsvText);
    const itemName = item['Dscription'] || `Item #${itemIndex + 1}`;
    showSaveToast(`📋 Rekap "${itemName}" berhasil disalin! Format 12 kolom siap di-paste ke Excel.`);

    if (btnElement) {
      const origText = btnElement.innerHTML;
      btnElement.innerHTML = '✅ Tersalin!';
      btnElement.classList.add('btn-copied-pulse');
      setTimeout(() => {
        btnElement.innerHTML = origText;
        btnElement.classList.remove('btn-copied-pulse');
      }, 1500);
    }
  } catch (err) {
    console.error("Gagal menyalin:", err);
    alert("Gagal menyalin ke clipboard: " + err.message);
  }
}

async function copyModalSingleItem(itemIndex, btnElement = null) {
  if (!currentModalRows || !currentModalRows[itemIndex]) return;
  const item = currentModalRows[itemIndex];
  const auditItem = {
    doc: currentModalDoc,
    postingDate: item['Posting Date'],
    cabang: item['Cabang'],
    items: currentModalRows,
    keep: currentModalKeep
  };

  const tsvText = generateAuditItemTSV(auditItem, item);
  try {
    await writeTextToClipboard(tsvText);
    const itemName = item['Dscription'] || `Item #${itemIndex + 1}`;
    showSaveToast(`📋 Rekap "${itemName}" berhasil disalin! Format 12 kolom siap di-paste ke Excel.`);

    if (btnElement) {
      const origText = btnElement.innerHTML;
      btnElement.innerHTML = '✅ Tersalin!';
      btnElement.classList.add('btn-copied-pulse');
      setTimeout(() => {
        btnElement.innerHTML = origText;
        btnElement.classList.remove('btn-copied-pulse');
      }, 1500);
    }
  } catch (err) {
    alert("Gagal menyalin: " + err.message);
  }
}

async function copyModalSelectedItems(btnElement = null) {
  if (currentModalCheckedItems.size === 0) {
    alert("Silakan centang minimal 1 item produk yang ingin disalin.");
    return;
  }

  const lines = [];
  Array.from(currentModalCheckedItems).sort((a,b)=>a-b).forEach(idx => {
    const item = currentModalRows[idx];
    const auditItem = {
      doc: currentModalDoc,
      postingDate: item['Posting Date'],
      cabang: item['Cabang'],
      items: currentModalRows,
      keep: currentModalKeep
    };
    lines.push(generateAuditItemTSV(auditItem, item));
  });

  const fullText = lines.join('\n');
  try {
    await writeTextToClipboard(fullText);
    showSaveToast(`📋 ${lines.length} baris item berhasil disalin! Format 12 kolom siap di-paste ke Excel.`);

    if (btnElement) {
      const origText = btnElement.innerHTML;
      btnElement.innerHTML = '✅ Tersalin!';
      btnElement.classList.add('btn-copied-pulse');
      setTimeout(() => {
        btnElement.innerHTML = origText;
        btnElement.classList.remove('btn-copied-pulse');
      }, 1500);
    }
  } catch (err) {
    alert("Gagal menyalin: " + err.message);
  }
}

function findKeepForARItem(item, docNumber = null) {
  const doc = docNumber || (item ? item['Document Number'] : '') || '';
  const digitsOnly = String(doc).replace(/\D/g, '');
  const rawWA = item ? (item['No HP Customer'] || '') : '';
  const normWA = normalizePhone(rawWA);

  let matched = null;
  if (rawKeepRows && rawKeepRows.length > 0) {
    if (digitsOnly) {
      matched = rawKeepRows.find(k => String(k['REPORT AR'] || '').replace(/\D/g, '') === digitsOnly);
    }
    if (!matched && normWA && normWA.length >= 8) {
      const allMatches = rawKeepRows.filter(k => normalizePhone(k['Nomor WA']) === normWA);
      matched = pickBestKeep(allMatches, item ? item['Posting Date'] : null);
    }
  }

  // Fallback: If not found in rawKeepRows (e.g. offline customer), build fallback keep object
  if (!matched) {
    matched = {
      'PERIODE': 'Oktober-26',
      'Nomor WA': rawWA,
      'Nama': (item && (item['Name'] || item['Customer/Vendor Name'])) || '-',
      'Barang Keep': (item && (item['Dscription'] || item['Item/Service Description'])) || '-',
      'Kode Sosmed': '-',
      'Sender': '-',
      'Tanggal Ambil': '-',
      'Catatan': (item && item['Remarks']) || ''
    };
  }
  return matched;
}

async function copyARSearchItemToClipboard(rowIdx, btnElement = null) {
  if (!currentARSearchResults || !currentARSearchResults[rowIdx]) {
    alert("Data item tidak ditemukan.");
    return;
  }
  const item = currentARSearchResults[rowIdx];
  const docNum = item['Document Number'] || currentARSearchDoc || '';
  const matchedKeep = findKeepForARItem(item, docNum);

  const auditItem = {
    doc: docNum,
    postingDate: item['Posting Date'],
    cabang: item['Cabang'] || item['SalesCabang'] || '-',
    items: [item],
    keep: matchedKeep
  };

  const tsvText = generateAuditItemTSV(auditItem, item);
  try {
    await writeTextToClipboard(tsvText);
    const itemName = item['Dscription'] || item['Item/Service Description'] || `Item #${rowIdx + 1}`;
    showSaveToast(`📋 Rekap "${itemName}" berhasil disalin! Format 12 kolom siap di-paste ke Excel.`);

    if (btnElement) {
      const origText = btnElement.innerHTML;
      btnElement.innerHTML = '✅ Tersalin!';
      btnElement.classList.add('btn-copied-pulse');
      setTimeout(() => {
        btnElement.innerHTML = origText;
        btnElement.classList.remove('btn-copied-pulse');
      }, 1500);
    }
  } catch (err) {
    console.error("Gagal menyalin:", err);
    alert("Gagal menyalin: " + err.message);
  }
}

// ==========================================================================
// 10. EXECUTIVE DASHBOARD: SPEEDOMETER GAUGES (BAHUNDA STYLE)
// ==========================================================================
function drawSpeedometer(canvasId, valuePct, color) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const dpr = window.devicePixelRatio || 1;
  const width = 220;
  const height = 120;
  canvas.width = width * dpr;
  canvas.height = height * dpr;
  canvas.style.width = `${width}px`;
  canvas.style.height = `${height}px`;
  ctx.scale(dpr, dpr);

  ctx.clearRect(0, 0, width, height);

  const cx = width / 2;
  const cy = height - 16;
  const radius = 78;
  const lineWidth = 15;
  const startAngle = Math.PI;
  const endAngle = 2 * Math.PI;

  // 1. Background Track Arc
  ctx.beginPath();
  ctx.arc(cx, cy, radius, startAngle, endAngle, false);
  ctx.lineWidth = lineWidth;
  ctx.strokeStyle = '#e2e8f0';
  ctx.lineCap = 'round';
  ctx.stroke();

  // 2. Active Progress Arc
  const clampedPct = Math.max(0, Math.min(valuePct, 150));
  const progressRatio = Math.min(clampedPct / 100, 1.0);
  const currentAngle = startAngle + (progressRatio * Math.PI);

  if (progressRatio > 0) {
    ctx.beginPath();
    ctx.arc(cx, cy, radius, startAngle, currentAngle, false);
    ctx.lineWidth = lineWidth;
    ctx.strokeStyle = color;
    ctx.lineCap = 'round';
    ctx.stroke();
  }

  // 3. Pointer Tick / Indicator Dot
  const dotX = cx + radius * Math.cos(currentAngle);
  const dotY = cy + radius * Math.sin(currentAngle);
  ctx.beginPath();
  ctx.arc(dotX, dotY, 4.5, 0, 2 * Math.PI);
  ctx.fillStyle = '#0f172a';
  ctx.fill();

  // 4. Min (0%) and Max (100%) Labels
  ctx.font = '600 10px "Inter", sans-serif';
  ctx.fillStyle = '#94a3b8';
  ctx.textAlign = 'left';
  ctx.fillText('0', 16, cy + 13);
  ctx.textAlign = 'right';
  ctx.fillText('100%', width - 16, cy + 13);
}

function renderSpeedometerGauges(picStats, activePICs) {
  if (!picStats || !activePICs || activePICs.length === 0) return;
  let grandTarget = 0, grandGPTarget = 0;
  let sumAch = 0, sumGP = 0, sumGadget = 0, sumCross = 0;

  activePICs.forEach(pic => {
    const s = picStats[pic];
    if (s) {
      grandTarget += s.target;
      grandGPTarget += s.targetGP;
      sumAch += s.pencapaian;
      sumGP += s.acvGP;
      sumGadget += s.gadgetCount;
      sumCross += s.crossCount;
    }
  });

  const explicitTeamTarget = getTeamTargetForMonth(activeMonth, activeYear);
  if (explicitTeamTarget && explicitTeamTarget > 0) {
    grandTarget = explicitTeamTarget;
    grandGPTarget = Math.round(grandTarget * 0.05);
  }

  const grandTotal = (typeof lastTotalSosmedAchieve === 'number' && lastTotalSosmedAchieve > 0) ? lastTotalSosmedAchieve : sumAch;
  const grandGP = (typeof lastTotalSosmedGP === 'number' && lastTotalSosmedGP !== 0) ? lastTotalSosmedGP : sumGP;
  const grandGadget = (typeof lastTotalSosmedGadget === 'number' && lastTotalSosmedGadget > 0) ? lastTotalSosmedGadget : sumGadget;
  const grandCross = (typeof lastTotalSosmedCross === 'number' && lastTotalSosmedCross > 0) ? lastTotalSosmedCross : sumCross;

  const pctOmzet = grandTarget > 0 ? (grandTotal / grandTarget) * 100 : 0;
  const pctGP = grandGPTarget > 0 ? (grandGP / grandGPTarget) * 100 : 0;
  const crossRatio = grandGadget > 0 ? Math.min(100, Math.round((grandCross / grandGadget) * 3 * 100)) : 0;

  // Text Metas
  const omzetPctEl = document.getElementById('gaugeOmzetPct');
  const omzetValEl = document.getElementById('gaugeOmzetVal');
  const omzetTargetEl = document.getElementById('gaugeOmzetTarget');
  if (omzetPctEl) omzetPctEl.innerText = formatPercent(pctOmzet);
  if (omzetValEl) omzetValEl.innerText = formatRupiah(grandTotal);
  if (omzetTargetEl) omzetTargetEl.innerText = formatRupiah(grandTarget);

  const gpPctEl = document.getElementById('gaugeGPPct');
  const gpValEl = document.getElementById('gaugeGPVal');
  const gpTargetEl = document.getElementById('gaugeGPTarget');
  if (gpPctEl) gpPctEl.innerText = formatPercent(pctGP);
  if (gpValEl) gpValEl.innerText = formatRupiah(grandGP);
  if (gpTargetEl) gpTargetEl.innerText = formatRupiah(grandGPTarget);

  const crossPctEl = document.getElementById('gaugeCrossPct');
  const crossItemsEl = document.getElementById('gaugeCrossItems');
  const crossGadgetEl = document.getElementById('gaugeCrossGadget');
  if (crossPctEl) crossPctEl.innerText = formatPercent(crossRatio);
  if (crossItemsEl) crossItemsEl.innerText = `${grandCross.toLocaleString('id-ID')} item`;
  if (crossGadgetEl) crossGadgetEl.innerText = `${grandGadget.toLocaleString('id-ID')} unit`;

  const timegonePctEl = document.getElementById('gaugeTimegonePct');
  const timegoneDayEl = document.getElementById('gaugeTimegoneDay');
  const timegoneStatusEl = document.getElementById('gaugeTimegoneStatus');
  if (timegonePctEl) timegonePctEl.innerText = `${timegonePct}%`;
  if (timegoneDayEl) timegoneDayEl.innerText = `Hari ke-${lastActiveDay} / ${daysInMonth}`;
  if (timegoneStatusEl) {
    const isOk = pctOmzet >= (timegonePct - 4);
    timegoneStatusEl.innerText = isOk ? '🖤 On Track' : '💔 Warning';
  }

  // Draw Speedometer Canvases
  drawSpeedometer('gaugeOmzetCanvas', pctOmzet, '#c8102e');
  drawSpeedometer('gaugeGPCanvas', pctGP, '#10b981');
  drawSpeedometer('gaugeCrossCanvas', crossRatio, '#f59e0b');
  drawSpeedometer('gaugeTimegoneCanvas', timegonePct, '#0f172a');
}

// ==========================================================================
// 11. MONTH-TO-MONTH COMBO CHARTS & SUMMARY TABLE (BAHUNDA STYLE)
// ==========================================================================
function renderMonthToMonthCharts() {
  if (typeof Chart === 'undefined') return;

  const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const fullMonthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

  // Base Historical Targets & Baseline Data (Jan-Sep 2026) using getTeamTargetForMonth
  const monthlyTargets = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map(m => getTeamTargetForMonth(m, activeYear));
  const monthlyOmzetData = [7420000000, 7650000000, 7890000000, 8120000000, 8350000000, 7950000000, 8280000000, 8520000000, 8310000000, 8447356034, null, null];
  const monthlyGPData = [380000000, 395000000, 410000000, 435000000, 460000000, 420000000, 445000000, 470000000, 430000000, 422367802, null, null];
  const monthlyCrossItems = [120, 135, 150, 180, 210, 175, 190, 220, 195, 230, null, null];
  const monthlyGadgetUnits = [320, 340, 370, 410, 440, 390, 420, 450, 410, 460, null, null];

  // Fill in active month with real active calculation dynamically
  const activeMonthIdx = (typeof activeMonth === 'number' && activeMonth >= 1 && activeMonth <= 12) ? (activeMonth - 1) : 10;
  if (lastProcessedPicStats && lastActivePICs.length > 0) {
    let currTotal = 0, currGP = 0, currCross = 0, currGadget = 0;
    lastActivePICs.forEach(p => {
      currTotal += lastProcessedPicStats[p].pencapaian;
      currGP += lastProcessedPicStats[p].acvGP;
      currCross += lastProcessedPicStats[p].crossCount;
      currGadget += lastProcessedPicStats[p].gadgetCount;
    });
    monthlyOmzetData[activeMonthIdx] = currTotal;
    monthlyGPData[activeMonthIdx] = currGP;
    monthlyCrossItems[activeMonthIdx] = currCross;
    monthlyGadgetUnits[activeMonthIdx] = currGadget;
  }

  // Override with any user uploaded archives from cachedMonthlyArchives
  if (cachedMonthlyArchives && cachedMonthlyArchives.length > 0) {
    cachedMonthlyArchives.forEach(arc => {
      const idx = (arc.month || 1) - 1;
      if (idx >= 0 && idx < 12) {
        if (arc.totalAchieveRaw) monthlyOmzetData[idx] = arc.totalAchieveRaw;
        if (arc.totalGPRaw) monthlyGPData[idx] = arc.totalGPRaw;
        if (arc.crossCount) monthlyCrossItems[idx] = arc.crossCount;
        if (arc.gadgetCount) monthlyGadgetUnits[idx] = arc.gadgetCount;
      }
    });
  }

  // Calculate percentage arrays
  const monthlyPctOmzet = monthlyOmzetData.map((val, i) => val !== null ? Math.round((val / monthlyTargets[i]) * 100) : null);
  const monthlyTargetGP = monthlyTargets.map(t => Math.round(t * 0.05));
  const monthlyPctGP = monthlyGPData.map((gp, i) => gp !== null ? Math.round((gp / monthlyTargetGP[i]) * 100) : null);

  const defaultFont = { family: "'Plus Jakarta Sans', sans-serif" };

  // --- CHART 1: Realisasi Omzet vs Target Bulanan (Berdasarkan Angka Miliar, Bukan Persen) ---
  const ctxOmzet = document.getElementById('chartTrendOmzet');
  if (ctxOmzet) {
    if (chartInstances.omzet) chartInstances.omzet.destroy();
    chartInstances.omzet = new Chart(ctxOmzet, {
      type: 'bar',
      data: {
        labels: monthLabels,
        datasets: [
          {
            type: 'bar',
            label: 'Realisasi Omzet (Miliar)',
            data: monthlyOmzetData.map(v => v !== null ? (Math.round((v / 1000000000) * 100) / 100) : null),
            backgroundColor: '#c8102e',
            borderRadius: 6
          },
          {
            type: 'line',
            label: 'Target Omzet (Miliar)',
            data: monthlyTargets.map(v => v !== null ? (Math.round((v / 1000000000) * 100) / 100) : null),
            borderColor: '#0f172a',
            borderWidth: 2.5,
            borderDash: [5, 5],
            pointBackgroundColor: '#0f172a',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            tension: 0.2
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top', labels: { font: defaultFont, boxWidth: 12 } },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const idx = ctx.dataIndex;
                const realisasi = monthlyOmzetData[idx];
                const target = monthlyTargets[idx];
                const pct = (realisasi && target) ? Math.round((realisasi / target) * 100) : 0;
                if (ctx.dataset.type === 'bar') {
                  return `Realisasi Omzet: ${formatRupiah(realisasi)} (${pct}% dari Target)`;
                }
                return `Target Omzet: ${formatRupiah(target)}`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Omzet & Target (Rp Miliar)', font: defaultFont },
            ticks: { callback: v => `Rp ${v}M`, font: defaultFont }
          },
          x: { ticks: { font: defaultFont } }
        }
      }
    });
  }

  // --- CHART 2: Gross Profit vs Target 5% (Dengan Bullet Poin Yang Jelas Naik/Turunnya) ---
  const ctxGP = document.getElementById('chartTrendGP');
  if (ctxGP) {
    if (chartInstances.gp) chartInstances.gp.destroy();
    chartInstances.gp = new Chart(ctxGP, {
      type: 'bar',
      data: {
        labels: monthLabels,
        datasets: [
          {
            type: 'bar',
            label: 'Realisasi GP (Juta)',
            data: monthlyGPData.map(v => v !== null ? (Math.round((v / 1000000) * 10) / 10) : null),
            backgroundColor: '#10b981',
            borderRadius: 6
          },
          {
            type: 'line',
            label: 'Target GP 5% (Rp 422Jt)',
            data: monthlyTargetGP.map(v => Math.round((v / 1000000) * 10) / 10),
            borderColor: '#0f172a',
            borderWidth: 2.5,
            borderDash: [5, 5],
            pointRadius: 6,
            pointHoverRadius: 8,
            pointBackgroundColor: '#0f172a',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            tension: 0.2
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top', labels: { font: defaultFont, boxWidth: 12 } },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const idx = ctx.dataIndex;
                const realisasiGP = monthlyGPData[idx];
                const targetGP = monthlyTargetGP[idx];
                const pct = (realisasiGP && targetGP) ? Math.round((realisasiGP / targetGP) * 100) : 0;
                if (ctx.dataset.type === 'bar') {
                  return `Realisasi GP: ${formatRupiah(realisasiGP)} (${pct}% dari Target)`;
                }
                return `Target GP 5%: ${formatRupiah(targetGP)}`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: v => `Rp ${v}Jt`, font: defaultFont },
            title: { display: true, text: 'Gross Profit (Juta Rp)', font: defaultFont }
          },
          x: { ticks: { font: defaultFont } }
        }
      }
    });
  }

  // --- CHART 3: Cross Selling vs Unit Gadget ---
  const ctxCross = document.getElementById('chartTrendCross');
  if (ctxCross) {
    if (chartInstances.cross) chartInstances.cross.destroy();
    chartInstances.cross = new Chart(ctxCross, {
      type: 'bar',
      data: {
        labels: monthLabels,
        datasets: [
          {
            type: 'bar',
            label: 'Item Cross (>50K)',
            data: monthlyCrossItems,
            backgroundColor: '#f59e0b',
            borderRadius: 6
          },
          {
            type: 'line',
            label: 'Total Unit Gadget',
            data: monthlyGadgetUnits,
            borderColor: '#0284c7',
            borderWidth: 2.5,
            pointBackgroundColor: '#0284c7',
            pointRadius: 4,
            tension: 0.2
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top', labels: { font: defaultFont, boxWidth: 12 } }
        },
        scales: {
          y: { ticks: { font: defaultFont }, title: { display: true, text: 'Jumlah Unit / Item', font: defaultFont } },
          x: { ticks: { font: defaultFont } }
        }
      }
    });
  }

  // --- CHART 4: Penjualan Divisi Sosmed di Pontianak, Balikpapan & Sungai Danau ---
  renderRegionalSosmedChart();

  // --- CHART 5: Pertumbuhan Bulan ke Bulan (MoM % Growth) ---
  renderMoMGrowthChart(monthlyOmzetData);

  // --- TABLE: Ringkasan Evaluasi Bulan ke Bulan ---
  const tbody = document.getElementById('monthSummaryTableBody');
  if (tbody) {
    tbody.innerHTML = fullMonthNames.map((mName, i) => {
      const omzetVal = monthlyOmzetData[i];
      const targetVal = monthlyTargets[i];
      const pctOmzet = monthlyPctOmzet[i];
      const gpVal = monthlyGPData[i];
      const targetGPVal = monthlyTargetGP[i];
      const pctGP = monthlyPctGP[i];

      const isCurrent = (i === (activeMonth - 1));
      const hasData = omzetVal !== null;
      let statusBadge = '<span class="status-badge badge-ink">Mendatang</span>';
      if (hasData) {
        if (pctOmzet >= 95) statusBadge = '<span class="status-badge badge-accent">🖤 On Track</span>';
        else if (pctOmzet >= 85) statusBadge = '<span class="status-badge badge-gold">⚠️ Evaluasi</span>';
        else statusBadge = '<span class="status-badge badge-crimson">💔 Under Target</span>';
      }

      return `
        <tr class="${isCurrent ? 'row-highlight-active' : ''}">
          <td class="text-left font-bold" style="white-space: nowrap; font-size: 0.85rem;">${mName} ${activeYear} ${isCurrent ? '<span class="kpi-pill-badge badge-crimson" style="font-size:0.68rem; margin-left:8px;">BULAN INI</span>' : ''}</td>
          <td>${formatRupiah(targetVal)}</td>
          <td class="font-bold">${hasData ? formatRupiah(omzetVal) : '-'}</td>
          <td class="font-bold text-center">${hasData ? `${pctOmzet}%` : '-'}</td>
          <td>${formatRupiah(targetGPVal)}</td>
          <td class="font-bold">${hasData ? formatRupiah(gpVal) : '-'}</td>
          <td class="font-bold text-center">${hasData ? `${pctGP}%` : '-'}</td>
          <td class="text-center">${statusBadge}</td>
        </tr>
      `;
    }).join('');
  }
}

// Branch friendly name dictionary
const CABANG_NAMES = {
  'BJMC': 'BJMC (Belitung)',
  'BTCC': 'BTCC (Batulicin)',
  'BJMA': 'BJMA (A. Yani)',
  'SPTC': 'SPTC (Sampit)',
  'KPSC': 'KPSC (Kapuas)',
  'BJB2': 'BJB2 (Banjarbaru 2)',
  'BJMP': 'BJMP (Perintis)',
  'PTKC': 'PTKC (Pontianak)',
  'MTPC': 'MTPC (Martapura)',
  'TJGC': 'TJGC (Tanjung)',
  'AMTC': 'AMTC (Amuntai)',
  'BJMY': 'BJMY (Veteran)',
  'BJMX': 'BJMX (Express)',
  'BJBC': 'BJBC (Banjarbaru)',
  'PLHC': 'PLHC (Pelaihari)',
  'BDM3': 'BDM3 (D. Mall)',
  'KTBC': 'KTBC (Kotabaru)',
  'BP2C': 'BP2C (Balikpapan 2)',
  'BBKC': 'BBKC (Barabai)',
  'BPPC': 'BPPC (Balikpapan)',
  'SPT2': 'SPT2 (Sampit 2)',
  'SESQ': 'SESQ (Samsung)',
  'SDNC': 'SDNC (Sudimampir)',
  'BJMD': 'BJMD (D. Indah)'
};

// Chart Penjualan Divisi Sosmed di Pontianak, Balikpapan & Sungai Danau (Acuan Kode: BJM.SOS.0 & BJM.SOS.K)
function renderRegionalSosmedChart() {
  const canvas = document.getElementById('chartRegionalSosmed');
  if (!canvas || typeof Chart === 'undefined') return;

  const isDPP = (currentCalculationMode === 'DPP');

  const regionalData = {
    'Pontianak': {
      label: 'Pontianak (PTKC)',
      codes: ['PTKC', 'PTKX'],
      docs: new Set(),
      itemCount: 0,
      omzetDPP: 0,
      omzetGross: 0,
      color: '#0284c7'
    },
    'Balikpapan': {
      label: 'Balikpapan (BPPC/BP2C)',
      codes: ['BPPC', 'BP2C', 'BP2X'],
      docs: new Set(),
      itemCount: 0,
      omzetDPP: 0,
      omzetGross: 0,
      color: '#c8102e'
    },
    'Sungai Danau': {
      label: 'Sungai Danau (SDNC)',
      codes: ['SDNC'],
      docs: new Set(),
      itemCount: 0,
      omzetDPP: 0,
      omzetGross: 0,
      color: '#10b981'
    }
  };

  if (rawTurnoverRows && rawTurnoverRows.length > 0) {
    rawTurnoverRows.forEach(r => {
      const emp = String(r['Sales Employee Name'] || '');
      const rem = String(r['Remarks'] || '');
      const cab = String(r['Cabang'] || r['SalesCabang'] || '').trim().toUpperCase();

      // Acuan data ditarik dari kode sales employee "bjm.sos.0" dan "bjm.sos.k"
      if (!isSosmedSalesCode(emp, rem, cab)) return;

      const docNo = r['Document Number'];
      const dpp = Number(r['DPP']) || 0;
      const total = Number(r['Total']) || 0;

      for (const regKey in regionalData) {
        const reg = regionalData[regKey];
        const isMatch = reg.codes.some(code => cab.includes(code)) || 
                        cab.startsWith(regKey.slice(0, 3).toUpperCase());
        if (isMatch) {
          reg.itemCount++;
          if (docNo) reg.docs.add(docNo);
          reg.omzetDPP += dpp;
          reg.omzetGross += total;
          break;
        }
      }
    });
  }

  // Update stat chips dengan JUMLAH TRANSAKSI FAKTUR & NOMINAL PENJUALAN DPP
  const ptkNominal = formatRupiah(isDPP ? regionalData['Pontianak'].omzetDPP : regionalData['Pontianak'].omzetGross);
  const elPtk = document.getElementById('chipValPtk');
  if (elPtk) elPtk.innerHTML = `<strong>${ptkNominal}</strong> (${regionalData['Pontianak'].docs.size} Trx Faktur &bull; ${regionalData['Pontianak'].itemCount} Item)`;

  const bppNominal = formatRupiah(isDPP ? regionalData['Balikpapan'].omzetDPP : regionalData['Balikpapan'].omzetGross);
  const elBpp = document.getElementById('chipValBpp');
  if (elBpp) elBpp.innerHTML = `<strong>${bppNominal}</strong> (${regionalData['Balikpapan'].docs.size} Trx Faktur &bull; ${regionalData['Balikpapan'].itemCount} Item)`;

  const sdnNominal = formatRupiah(isDPP ? regionalData['Sungai Danau'].omzetDPP : regionalData['Sungai Danau'].omzetGross);
  const elSdn = document.getElementById('chipValSdn');
  if (elSdn) elSdn.innerHTML = `<strong>${sdnNominal}</strong> (${regionalData['Sungai Danau'].docs.size} Trx Faktur &bull; ${regionalData['Sungai Danau'].itemCount} Item)`;

  // Plot grafik berdasarkan NOMINAL PENJUALAN DPP (Juta Rupiah)
  const labels = Object.values(regionalData).map(r => r.label);
  const nominalJuta = Object.values(regionalData).map(r => Math.round(((isDPP ? r.omzetDPP : r.omzetGross) / 1000000) * 10) / 10);
  const colors = Object.values(regionalData).map(r => r.color);

  const defaultFont = { family: "'Plus Jakarta Sans', sans-serif" };

  if (chartInstances.regionalSosmed) chartInstances.regionalSosmed.destroy();
  chartInstances.regionalSosmed = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: isDPP ? 'Total Penjualan DPP (Juta Rp)' : 'Total Penjualan Gross (Juta Rp)',
          data: nominalJuta,
          backgroundColor: colors,
          borderRadius: 8,
          barPercentage: 0.55
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => {
              const regKey = Object.keys(regionalData)[ctx.dataIndex];
              const reg = regionalData[regKey];
              const valNominal = isDPP ? reg.omzetDPP : reg.omzetGross;
              return [
                ` Penjualan ${isDPP ? 'DPP' : 'Gross'}: ${formatRupiah(valNominal)}`,
                ` Transaksi Faktur Customer: ${reg.docs.size} Faktur Unik`,
                ` Total Item Terjual: ${reg.itemCount} Barang`
              ];
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { ...defaultFont, weight: 600, size: 11 }, color: '#334155' }
        },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0,0,0,0.06)' },
          ticks: {
            font: { ...defaultFont, size: 10 },
            color: '#64748b',
            callback: (v) => `Rp ${v} Jt`
          }
        }
      }
    }
  });
}

// Chart MoM Growth
function renderMoMGrowthChart(monthlyOmzetData) {
  const canvas = document.getElementById('chartMoMGrowth');
  if (!canvas || typeof Chart === 'undefined') return;

  const allMonthShorts = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const currentMonthIdx = (typeof activeMonth === 'number' && activeMonth >= 1 && activeMonth <= 12) ? (activeMonth - 1) : 10;
  const targetEndIdx = Math.max(9, currentMonthIdx);

  const monthLabels = [];
  const growthRates = [];
  const barColors = [];

  for (let i = 1; i <= targetEndIdx; i++) {
    monthLabels.push(allMonthShorts[i]);
    const prev = monthlyOmzetData[i - 1];
    const curr = monthlyOmzetData[i];
    if (prev && curr) {
      const rate = Math.round(((curr - prev) / prev) * 1000) / 10;
      growthRates.push(rate);
      barColors.push(rate >= 0 ? '#10b981' : '#c8102e');
    } else {
      growthRates.push(0);
      barColors.push('#94a3b8');
    }
  }

  if (chartInstances.momGrowth) chartInstances.momGrowth.destroy();
  chartInstances.momGrowth = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: monthLabels,
      datasets: [{
        label: 'MoM Growth Rate (%)',
        data: growthRates,
        backgroundColor: barColors,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ` Pertumbuhan: ${ctx.parsed.y > 0 ? '+' : ''}${ctx.parsed.y}%`
          }
        }
      },
      scales: {
        y: {
          ticks: { callback: v => `${v}%`, font: { family: "'Plus Jakarta Sans', sans-serif" } },
          title: { display: true, text: 'Growth Rate (%)', font: { family: "'Plus Jakarta Sans', sans-serif" } }
        },
        x: { ticks: { font: { family: "'Plus Jakarta Sans', sans-serif" } } }
      }
    }
  });
}

// ==========================================================================
// 12. SALES LEADERBOARD (HANYA UNTUK OMZET - SESUAI PERMINTAAN USER)
// ==========================================================================
function setLeaderboardPeriodMode(mode) {
  leaderboardPeriodMode = 'current';
  renderSalesLeaderboard('omzet');
}

function setLeaderboardSort(sortBy) {
  currentLeaderboardSort = 'omzet';
  renderSalesLeaderboard('omzet');
}

function renderSalesLeaderboard(sortBy = 'omzet') {
  const grid = document.getElementById('salesLeaderboardGrid');
  if (!grid) return;
  if (!lastProcessedPicStats || !lastActivePICs || lastActivePICs.length === 0) {
    grid.innerHTML = '<div style="grid-column: 1 / -1; padding: 30px; text-align: center; color: #e0f2fe;">Memuat data sales leaderboard...</div>';
    return;
  }

  // Update title with active month and year
  const titleEl = document.getElementById('leaderboardTitleText');
  if (titleEl) {
    const monthNames = ['', 'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
    const mStr = monthNames[Number(activeMonth)] || String(activeMonth).toUpperCase();
    titleEl.innerHTML = `🏆 SALES LEADERBOARD &bull; REALISASI OMZET ${mStr} ${activeYear}`;
  }

  // Count deals closed per PIC from raw turnover documents
  const picDeals = {};
  lastActivePICs.forEach(p => picDeals[p] = 0);
  if (lastDocTransactions) {
    for (const doc in lastDocTransactions) {
      const item = lastDocTransactions[doc];
      if (item && item.pic && picDeals[item.pic] !== undefined) {
        picDeals[item.pic]++;
      }
    }
  }

  // Build ranking dataset strictly based on current month real performance
  const rankingList = lastActivePICs.map(pic => {
    const stat = lastProcessedPicStats[pic] || {};
    const currOmzet = stat.pencapaian || 0;
    const currGP = stat.acvGP || 0;
    const currDeals = picDeals[pic] || Math.round(currOmzet / 18500000) || 0;
    const currPctOmzet = (stat.target > 0) ? Math.round((currOmzet / stat.target) * 100) : (stat.pctOmzet || 0);
    const currPctGP = (stat.targetGP > 0) ? Math.round((currGP / stat.targetGP) * 100) : (stat.pctGP || 0);
    const currScore = stat.pctAkhir || 0;

    return {
      name: pic,
      omzet: currOmzet,
      gp: currGP,
      deals: currDeals,
      pctOmzet: currPctOmzet,
      pctGP: currPctGP,
      score: currScore,
      isATH: false
    };
  });

  // Urutkan PIC HANYA berdasarkan OMZET tertinggi (Instruksi: leaderboard hanya untuk OMZET)
  rankingList.sort((a, b) => b.omzet - a.omzet);

  const masterList = getMasterSales();

  grid.innerHTML = rankingList.slice(0, 10).map((item, index) => {
    const rank = index + 1;
    const pic = item.name;
    const m = masterList.find(x => x.name === pic);
    const code = m ? m.code : 'BJM.SOS';

    // Ribbon style
    let ribbonClass = 'rank-blue';
    if (rank === 1) ribbonClass = 'rank-1';
    else if (rank === 2) ribbonClass = 'rank-2';
    else if (rank === 3) ribbonClass = 'rank-3';

    // Custom Photo vs Fallback Avatar
    const photoUrl = cachedSalesPhotos[pic];
    let photoHtml = '';
    if (photoUrl) {
      photoHtml = `<img src="${photoUrl}" class="sales-card-img" alt="${pic}" onerror="this.parentElement.innerHTML='<div class=\\'sales-avatar-fallback\\'><span class=\\'avatar-initials\\'>${pic.slice(0,2)}</span><span class=\\'avatar-code\\'>${code}</span></div>'">`;
    } else {
      photoHtml = `
        <div class="sales-avatar-fallback">
          <span class="avatar-initials">${pic.slice(0, 2)}</span>
          <span class="avatar-code">${code}</span>
        </div>
      `;
    }

    // Metric display based on sort
    let metricDisplay = formatRupiah(item.omzet);
    let subMetric = `${item.deals} Deals Closed`;
    if (sortBy === 'gp') {
      metricDisplay = formatRupiah(item.gp);
      subMetric = `GP: ${formatRupiah(item.gp)}`;
    } else if (sortBy === 'score') {
      metricDisplay = `${item.score}% Rapor`;
      subMetric = `Omzet: ${item.pctOmzet}%`;
    }

    const targetProgress = Math.min(item.pctOmzet, 100);

    return `
      <div class="sales-card" title="Peringkat ${rank}: ${pic}">
        <div class="rank-ribbon ${ribbonClass}">${rank}</div>
        ${item.isATH ? '<span class="ath-badge-card">👑 ATH</span>' : ''}
        <div class="sales-card-photo-wrap">
          ${photoHtml}
        </div>
        <div class="sales-card-footer">
          <div class="sales-card-name">${pic}</div>
          <div class="sales-card-omzet">${metricDisplay}</div>
          <div class="sales-card-deals">
            <span>${subMetric}</span>
            <strong>${item.pctOmzet}%</strong>
          </div>
          <div class="sales-card-target-bar">
            <div class="sales-card-target-fill" style="width: ${targetProgress}%;"></div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

// ==========================================================================
// 13. MONTHLY ARCHIVES (SERVER PERSISTENCE & EXCEL UPLOAD PROCESSOR)
// ==========================================================================
async function loadMonthlyArchives() {
  try {
    const res = await fetch(`/api/monthly-archives?t=${Date.now()}`);
    if (res.ok) {
      const serverData = await res.json();
      let localData = [];
      try {
        const stored = JSON.parse(localStorage.getItem('gadgetmart_sosmed_archives') || '[]');
        localData = Array.isArray(stored) ? stored : Object.values(stored || {});
      } catch (e) {
        localData = [];
      }
      const map = {};
      if (Array.isArray(localData)) {
        localData.forEach(item => { if (item && typeof item === 'object') map[item.title || `${item.month}_${item.year}`] = item; });
      }
      if (Array.isArray(serverData)) {
        serverData.forEach(item => { if (item && typeof item === 'object') map[item.title || `${item.month}_${item.year}`] = item; });
      } else if (serverData && typeof serverData === 'object') {
        Object.keys(serverData).forEach(k => {
          if (serverData[k] && typeof serverData[k] === 'object') {
            map[k] = serverData[k];
          }
        });
      }
      cachedMonthlyArchives = Object.values(map);
    }
  } catch(e) {
    try {
      const stored = JSON.parse(localStorage.getItem('gadgetmart_sosmed_archives') || '[]');
      cachedMonthlyArchives = Array.isArray(stored) ? stored : Object.values(stored || {});
    } catch (err) {
      cachedMonthlyArchives = [];
    }
  }
  if (!Array.isArray(cachedMonthlyArchives)) {
    cachedMonthlyArchives = [];
  }
}

async function persistMonthlyArchivesToServer() {
  localStorage.setItem('gadgetmart_sosmed_archives', JSON.stringify(cachedMonthlyArchives));
  try {
    await fetch('/api/monthly-archives', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(cachedMonthlyArchives)
    });
  } catch(e) {
    console.warn("Gagal menyimpan arsip bulanan ke server:", e);
  }
}

async function saveCurrentMonthToArchive() {
  const monthName = prompt("Masukkan nama periode arsip:", `Oktober ${activeYear}`);
  if (!monthName) return;

  const snapshot = {
    id: Date.now(),
    month: activeMonth,
    year: activeYear,
    title: monthName,
    dateSaved: new Date().toLocaleDateString('id-ID'),
    timegone: timegonePct,
    totalAchieve: document.getElementById('kpiTotalAchieve').innerText,
    totalGP: document.getElementById('kpiTotalGP').innerText,
    crossRatio: document.getElementById('kpiCrossRatio').innerText,
    progress: document.getElementById('kpiProgressPct').innerText
  };

  cachedMonthlyArchives = cachedMonthlyArchives.filter(a => a.title !== monthName);
  cachedMonthlyArchives.unshift(snapshot);
  await persistMonthlyArchivesToServer();
  alert(`Arsip "${monthName}" berhasil disimpan ke server!`);
  renderArchiveGrid();
  renderMonthToMonthCharts();
}

function renderArchiveGrid() {
  const grid = document.getElementById('archiveGrid');
  const empty = document.getElementById('archiveEmptyState');
  if (!grid || !empty) return;

  if (!Array.isArray(cachedMonthlyArchives) || cachedMonthlyArchives.length === 0) {
    grid.innerHTML = '';
    empty.style.display = 'block';
    return;
  }

  empty.style.display = 'none';
  grid.innerHTML = cachedMonthlyArchives.map(a => `
    <div class="archive-month-card">
      <div class="archive-month-header">
        <h3 class="archive-month-title">📁 ${a.title}</h3>
        <span class="kpi-badge badge-crimson">${a.dateSaved || 'Tersimpan'}</span>
      </div>
      <div class="archive-stats" style="font-size:0.83rem; line-height:1.6; color:#334155;">
        <p style="display:flex; justify-content:space-between; align-items:center;">
          <span>Pencapaian Omzet:</span>
          <span style="display:inline-flex; align-items:center; gap:6px;">
            <strong style="color:#0f172a;">${a.totalAchieve}</strong>
            <button class="btn-archive-edit-gp" onclick="editArchiveOmzet(${a.month}, ${a.year})" title="Setting / Edit Pencapaian Omzet ${a.title}">✏️</button>
          </span>
        </p>
        <p style="display:flex; justify-content:space-between; align-items:center;">
          <span>Gross Profit:</span>
          <span style="display:inline-flex; align-items:center; gap:6px;">
            <strong style="color:#10b981;">${a.totalGP}</strong>
            <button class="btn-archive-edit-gp" onclick="editArchiveGP(${a.month}, ${a.year})" title="Edit Gross Profit ${a.title}">✏️</button>
          </span>
        </p>
        <p style="display:flex; justify-content:space-between;"><span>Timegone:</span> <strong>${a.timegone}%</strong></p>
        <p style="display:flex; justify-content:space-between;"><span>Progress Target:</span> <strong style="color:#c8102e;">${a.progress}</strong></p>
        <p style="display:flex; justify-content:space-between;"><span>Cross-Selling:</span> <strong>${a.crossRatio}</strong></p>
      </div>
      <div class="archive-card-actions" style="margin-top:14px; padding-top:10px; border-top:1px dashed #e2e8f0; display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap;">
        <div style="display:flex; gap:6px;">
          <button class="btn btn-outline btn-xs" onclick="editArchiveOmzet(${a.month}, ${a.year})">
            ✏️ Setting Pencapaian
          </button>
          <button class="btn btn-outline btn-xs" onclick="editArchiveGP(${a.month}, ${a.year})">
            ✏️ Edit GP
          </button>
        </div>
        <button class="btn btn-outline btn-xs" style="color:#ef4444; border-color:#fca5a5;" onclick="deleteArchiveMonth(${a.month}, ${a.year})">
          🗑️ Hapus
        </button>
      </div>
    </div>
  `).join('');
}

// FITUR SETTING / EDIT PENCAPAIAN OMZET DI ARSIP PENCAPAIAN (PERMINTAAN USER)
async function editArchiveOmzet(month, year) {
  const archive = cachedMonthlyArchives.find(a => Number(a.month) === Number(month) && Number(a.year) === Number(year));
  if (!archive) {
    alert("Arsip bulan tidak ditemukan!");
    return;
  }
  const currentVal = archive.totalAchieveRaw || Number(String(archive.totalAchieve || '0').replace(/[^0-9.-]+/g, ''));
  const promptVal = prompt(
    `Setting / Edit Realisasi Pencapaian Omzet untuk "${archive.title}":\n\nMasukkan nominal angka baru (contoh: 8500000000 atau Rp 8.500.000.000):`,
    currentVal
  );
  if (promptVal === null) return; // Dibatalkan pengguna

  const cleanNum = Number(String(promptVal).replace(/[^0-9.-]+/g, ''));
  if (isNaN(cleanNum) || cleanNum < 0) {
    alert("Nominal pencapaian omzet tidak valid! Harap masukkan angka yang benar.");
    return;
  }

  archive.totalAchieveRaw = cleanNum;
  archive.totalAchieve = formatRupiah(cleanNum);
  if (archive.targetOmzet && archive.targetOmzet > 0) {
    archive.pctOmzet = Math.round((cleanNum / archive.targetOmzet) * 100);
    archive.progress = `${archive.pctOmzet}%`;
  }

  await persistMonthlyArchivesToServer();
  renderArchiveGrid();
  renderMonthToMonthCharts();
  alert(`✅ Pencapaian Omzet untuk "${archive.title}" berhasil diatur menjadi ${formatRupiah(cleanNum)}!\nGrafik tren omzet bulanan telah otomatis diperbarui.`);
}

// EDIT GROSS PROFIT PADA SNAPSHOT ARSIP BULANAN (PERMINTAAN USER)
async function editArchiveGP(month, year) {
  const archive = cachedMonthlyArchives.find(a => Number(a.month) === Number(month) && Number(a.year) === Number(year));
  if (!archive) {
    alert("Arsip bulan tidak ditemukan!");
    return;
  }
  const currentVal = archive.totalGPRaw || Number(String(archive.totalGP || '0').replace(/[^0-9.-]+/g, ''));
  const promptVal = prompt(
    `Edit Gross Profit untuk "${archive.title}":\n\nMasukkan nominal angka baru (contoh: 420000000 atau Rp 420.000.000):`,
    currentVal
  );
  if (promptVal === null) return; // Dibatalkan pengguna

  const cleanNum = Number(String(promptVal).replace(/[^0-9.-]+/g, ''));
  if (isNaN(cleanNum) || cleanNum < 0) {
    alert("Nominal Gross Profit tidak valid! Harap masukkan angka yang benar.");
    return;
  }

  archive.totalGPRaw = cleanNum;
  archive.totalGP = formatRupiah(cleanNum);
  if (archive.targetGP && archive.targetGP > 0) {
    archive.pctGP = Math.round((cleanNum / archive.targetGP) * 100);
  }

  await persistMonthlyArchivesToServer();
  renderArchiveGrid();
  renderMonthToMonthCharts();
  alert(`✅ Gross Profit untuk "${archive.title}" berhasil diubah menjadi ${formatRupiah(cleanNum)}!\nGrafik tren bulanan telah otomatis diperbarui.`);
}

async function deleteArchiveMonth(month, year) {
  const archive = cachedMonthlyArchives.find(a => Number(a.month) === Number(month) && Number(a.year) === Number(year));
  if (!archive) return;
  if (!confirm(`Apakah Anda yakin ingin menghapus arsip "${archive.title}" dari database?`)) return;

  cachedMonthlyArchives = cachedMonthlyArchives.filter(a => !(Number(a.month) === Number(month) && Number(a.year) === Number(year)));
  await persistMonthlyArchivesToServer();
  renderArchiveGrid();
  renderMonthToMonthCharts();
  alert(`Arsip "${archive.title}" berhasil dihapus.`);
}

async function handleArchiveExcelUpload() {
  const monthSelect = document.getElementById('archiveUploadMonth');
  const yearInput = document.getElementById('archiveUploadYear');
  const fileInput = document.getElementById('archiveFileInput');

  if (!fileInput.files || fileInput.files.length === 0) {
    alert('Pilih file Excel turnover terlebih dahulu!');
    return;
  }

  const file = fileInput.files[0];
  const month = parseInt(monthSelect.value, 10);
  const year = parseInt(yearInput.value, 10);
  const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  const monthTitle = `${monthNames[month]} ${year}`;

  const reader = new FileReader();
  reader.onload = async (e) => {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheet = workbook.SheetNames[0];
      const rows = XLSX.utils.sheet_to_json(workbook.Sheets[firstSheet]);

      if (!rows || rows.length === 0) {
        alert('File Excel kosong atau tidak terbaca!');
        return;
      }

      const masterList = getMasterSales();
      let totalAchieve = 0;
      let totalGP = 0;
      let totalGadget = 0;
      let totalCross = 0;
      const docGroups = {};

      for (const row of rows) {
        const emp = String(row['Sales Employee Name'] || '');
        const rem = String(row['Remarks'] || '');
        const cab = String(row['SalesCabang'] || '');

        let matched = false;
        for (const m of masterList) {
          if (emp.includes(m.code) || rem.includes(m.code) || cab.includes(m.code)) {
            matched = true;
            break;
          }
        }
        if (!matched) continue;

        const dpp = Number(row['DPP']) || 0;
        const total = Number(row['Total']) || 0;
        const fixVal = (row['FIX'] !== undefined && row['FIX'] !== '') ? Number(row['FIX']) : NaN;
        const rawGP = Number(row['GrossProfit']) || 0;
        const gp = !isNaN(fixVal) ? fixVal : rawGP;
        const amt = currentCalculationMode === 'DPP' ? dpp : total;

        totalAchieve += amt;
        totalGP += gp;

        const docNo = row['Document Number'];
        if (docNo) {
          if (!docGroups[docNo]) docGroups[docNo] = { hasGadget: false, accTotal: 0, hasCross: false };
          // TM = Tebus Murah (diberi 'X' agar tidak dihitung sebagai gadget/croselling reguler)
          const itemDesc = String(row['Item/Service Description'] || '');
          const grpRaw = String(row['Group Name'] || '').trim().toUpperCase();
          let grp = grpRaw.slice(0, 3);
          if (itemDesc.startsWith('TM ')) grp = 'X';
          else if (itemDesc.startsWith('GMART CARE') || itemDesc.startsWith('JASA SERVICE T') || itemDesc.startsWith('JASA SERVICE FLEX T')) grp = 'ACC';

          if (grp === 'GAD') docGroups[docNo].hasGadget = true;
          if (grp === 'ACC') docGroups[docNo].accTotal += total;
        }
      }

      for (const d in docGroups) {
        if (docGroups[d].hasGadget) {
          totalGadget++;
          if (docGroups[d].accTotal > 50000) totalCross++;
        }
      }

      const targetOmzet = 8447356034;
      const targetGP = Math.round(targetOmzet * 0.05);
      const pctOmzet = targetOmzet > 0 ? Math.round((totalAchieve / targetOmzet) * 100) : 0;
      const pctGP = targetGP > 0 ? Math.round((totalGP / targetGP) * 100) : 0;
      const crossRatio = totalGadget > 0 ? Math.min(100, Math.round((totalCross / totalGadget) * 3 * 100)) : 0;

      const newArchive = {
        month,
        year,
        title: monthTitle,
        totalAchieve: formatRupiah(totalAchieve),
        totalAchieveRaw: totalAchieve,
        totalGP: formatRupiah(totalGP),
        totalGPRaw: totalGP,
        targetOmzet,
        targetGP,
        pctOmzet,
        pctGP,
        timegone: 100,
        progress: `${pctOmzet}%`,
        crossRatio: `${crossRatio}%`,
        gadgetCount: totalGadget,
        crossCount: totalCross,
        dateSaved: new Date().toLocaleDateString('id-ID')
      };

      cachedMonthlyArchives = cachedMonthlyArchives.filter(a => !(a.month === month && a.year === year));
      cachedMonthlyArchives.unshift(newArchive);

      await persistMonthlyArchivesToServer();
      renderArchiveGrid();
      renderMonthToMonthCharts();

      alert(`🎉 Arsip ${monthTitle} berhasil diproses dan disimpan ke database server! Grafik tren dari bulan ke bulan telah diperbarui.`);
      fileInput.value = '';
    } catch (err) {
      console.error(err);
      alert(`Gagal memproses file Excel: ${err.message}`);
    }
  };
  reader.readAsArrayBuffer(file);
}

// ==========================================================================
// 14. ADMIN PANEL & SALES PHOTO MANAGEMENT
// ==========================================================================
async function loadSalesPhotos() {
  try {
    const res = await fetch(`/api/sales-photos?t=${Date.now()}`);
    if (res.ok) {
      const serverData = await res.json();
      const localData = JSON.parse(localStorage.getItem('gadgetmart_sales_photos') || '{}');
      cachedSalesPhotos = Object.assign({}, localData, serverData);
    }
  } catch(e) {
    cachedSalesPhotos = JSON.parse(localStorage.getItem('gadgetmart_sales_photos') || '{}');
  }
}

async function persistSalesPhotosToServer() {
  localStorage.setItem('gadgetmart_sales_photos', JSON.stringify(cachedSalesPhotos));
  try {
    await fetch('/api/sales-photos', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(cachedSalesPhotos)
    });
  } catch(e) {
    console.warn("Gagal menyimpan foto sales ke server:", e);
  }
}

function renderAdminPhotoTable() {
  const tbody = document.getElementById('adminPhotoTableBody');
  if (!tbody) return;
  const masterList = getMasterSales();

  tbody.innerHTML = masterList.map(m => {
    const photo = cachedSalesPhotos[m.name];
    const previewSrc = photo || 'logo.png';
    return `
      <tr>
        <td class="text-center">
          <img src="${previewSrc}" id="photoThumb_${m.name}" class="admin-photo-thumb" alt="${m.name}">
        </td>
        <td class="text-left font-bold">${m.name}</td>
        <td class="text-left font-mono">${m.code}</td>
        <td class="text-left">
          <input type="file" accept="image/*" class="form-input" style="padding: 4px;" onchange="handleAdminPhotoUpload('${m.name}', event)">
        </td>
        <td class="text-left">
          <div style="display: flex; gap: 6px;">
            <input type="text" id="photoUrlInput_${m.name}" value="${photo && photo.startsWith('http') ? photo : ''}" placeholder="https://..." class="form-input" style="padding: 4px 8px; font-size: 0.82rem;">
            <button class="btn btn-sm btn-crimson" onclick="saveAdminPhotoUrl('${m.name}')">Simpan</button>
          </div>
        </td>
        <td class="text-center">
          ${photo ? `<button class="btn btn-sm btn-outline text-crimson" onclick="deleteSalesPhoto('${m.name}')">Hapus</button>` : '-'}
        </td>
      </tr>
    `;
  }).join('');
}

function handleAdminPhotoUpload(picName, event) {
  const file = event.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = async (e) => {
    const base64 = e.target.result;
    cachedSalesPhotos[picName] = base64;
    await persistSalesPhotosToServer();
    renderAdminPhotoTable();
    renderSalesLeaderboard(currentLeaderboardSort);
    alert(`Foto untuk ${picName} berhasil disimpan permanen ke server!`);
  };
  reader.readAsDataURL(file);
}

async function saveAdminPhotoUrl(picName) {
  const input = document.getElementById(`photoUrlInput_${picName}`);
  if (!input) return;
  const url = input.value.trim();
  if (!url) {
    alert('Masukkan URL foto gambar terlebih dahulu!');
    return;
  }
  cachedSalesPhotos[picName] = url;
  await persistSalesPhotosToServer();
  renderAdminPhotoTable();
  renderSalesLeaderboard(currentLeaderboardSort);
  alert(`URL foto untuk ${picName} berhasil disimpan permanen ke server!`);
}

async function deleteSalesPhoto(picName) {
  if (!confirm(`Hapus foto profil sales ${picName}?`)) return;
  delete cachedSalesPhotos[picName];
  await persistSalesPhotosToServer();
  renderAdminPhotoTable();
  renderSalesLeaderboard(currentLeaderboardSort);
}

async function resetAllPhotosToDefault() {
  if (!confirm('Reset semua foto sales profil ke standar?')) return;
  cachedSalesPhotos = {};
  await persistSalesPhotosToServer();
  renderAdminPhotoTable();
  renderSalesLeaderboard(currentLeaderboardSort);
}

// ==========================================================================
// 15. MONTHLY TARGETS ENGINE (TARGET PER BULAN)
// ==========================================================================
async function loadMonthlyTargets() {
  try {
    const res = await fetch(`/api/monthly-targets?t=${Date.now()}`);
    if (res.ok) {
      cachedMonthlyTargets = await res.json();
      console.log('✅ Monthly targets loaded from server:', cachedMonthlyTargets);
    }
  } catch(e) {
    cachedMonthlyTargets = JSON.parse(localStorage.getItem('gadgetmart_monthly_targets') || '{}');
  }
}

async function persistMonthlyTargetsToServer() {
  localStorage.setItem('gadgetmart_monthly_targets', JSON.stringify(cachedMonthlyTargets));
  try {
    await fetch('/api/monthly-targets', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(cachedMonthlyTargets)
    });
  } catch(e) {
    console.warn("Gagal simpan target bulanan ke server:", e);
  }
}

function getMonthlyTargetKey(m, y) {
  return `${y}-${String(m).padStart(2, '0')}`;
}

function getTeamTargetForMonth(m, y) {
  const key = getMonthlyTargetKey(m, y);
  if (cachedMonthlyTargets && cachedMonthlyTargets[key] && cachedMonthlyTargets[key].teamTarget) {
    return Number(cachedMonthlyTargets[key].teamTarget);
  }
  const master = getMasterSales().filter(x => x.active);
  const sumSales = master.reduce((acc, curr) => acc + (Number(curr.target) || 867261886), 0);
  return sumSales > 0 ? sumSales : 8447356034;
}

function getSalesTargetForMonth(picCode, m, y, defaultTarget, picName) {
  const key = getMonthlyTargetKey(m, y);
  if (cachedMonthlyTargets && cachedMonthlyTargets[key] && cachedMonthlyTargets[key].salesTargets) {
    const st = cachedMonthlyTargets[key].salesTargets;
    if (picCode && st[picCode] !== undefined) return Number(st[picCode]);
    if (picName && st[picName] !== undefined) return Number(st[picName]);
    if (picName) {
      const matchKey = Object.keys(st).find(k => k.toUpperCase() === picName.toUpperCase());
      if (matchKey && st[matchKey] !== undefined) return Number(st[matchKey]);
    }
  }
  return defaultTarget;
}

function onAdminTargetMonthChange() {
  const mSelect = document.getElementById('adminTargetMonthSelect');
  const yInput = document.getElementById('adminTargetYearInput');
  const teamInput = document.getElementById('adminTeamTargetInput');
  if (!mSelect || !yInput) return;

  const m = parseInt(mSelect.value, 10);
  const y = parseInt(yInput.value, 10);
  const key = getMonthlyTargetKey(m, y);
  const monthData = cachedMonthlyTargets[key] || {};

  if (teamInput) {
    teamInput.value = monthData.teamTarget || 8447356034;
  }

  const masterList = getMasterSales();
  const targetList = document.getElementById('adminTargetList');
  if (targetList) {
    targetList.innerHTML = masterList.filter(x => x.active).map(x => {
      const targetVal = (monthData.salesTargets && monthData.salesTargets[x.code] !== undefined) 
        ? monthData.salesTargets[x.code] 
        : x.target;
      return `
        <div class="admin-target-row">
          <label>Target ${x.name} (${x.code}):</label>
          <input type="number" id="targetInput_${x.code}" value="${targetVal}" class="form-input">
        </div>
      `;
    }).join('');
  }
}

function populateAdminPanel(masterList) {
  const masterTable = document.getElementById('adminMasterTableBody');
  onAdminTargetMonthChange();

  if (masterTable) {
    masterTable.innerHTML = masterList.map((m, idx) => `
      <tr>
        <td class="text-left font-bold">${m.code}</td>
        <td class="text-left">${m.name}</td>
        <td class="text-center">
          <span class="status-badge ${m.active ? 'badge-accent' : 'badge-ink'}">${m.active ? 'Aktif' : 'Non-Aktif'}</span>
        </td>
        <td class="text-center">
          <button class="btn btn-sm btn-outline" onclick="toggleSalesActive(${idx})">${m.active ? 'Nonaktifkan' : 'Aktifkan'}</button>
        </td>
      </tr>
    `).join('');
  }

  renderAdminPhotoTable();
}

async function saveAdminTargets() {
  const mSelect = document.getElementById('adminTargetMonthSelect');
  const yInput = document.getElementById('adminTargetYearInput');
  const teamInput = document.getElementById('adminTeamTargetInput');
  
  const m = mSelect ? parseInt(mSelect.value, 10) : activeMonth;
  const y = yInput ? parseInt(yInput.value, 10) : activeYear;
  const key = getMonthlyTargetKey(m, y);

  const teamTarget = teamInput ? (parseFloat(teamInput.value) || 8447356034) : 8447356034;
  const salesTargets = {};
  const masterList = getMasterSales();

  masterList.forEach(item => {
    const input = document.getElementById(`targetInput_${item.code}`);
    if (input) {
      const val = parseFloat(input.value) || 0;
      salesTargets[item.code] = val;
      salesTargets[item.name] = val;
      if (m === activeMonth && y === activeYear) {
        item.target = val;
      }
    }
  });

  cachedMonthlyTargets[key] = {
    month: m,
    year: y,
    teamTarget: teamTarget,
    salesTargets: salesTargets
  };

  saveMasterSales(masterList);
  await persistMonthlyTargetsToServer();
  const monthName = mSelect ? mSelect.options[mSelect.selectedIndex].text : `Bulan ${m}`;
  showToast(`✅ Target bulanan untuk ${monthName} ${y} berhasil disimpan permanen ke server!`);
  processAllReports();
}

// ==========================================================================
// 15B. INTERACTIVE MONTHLY TARGET MODAL ENGINE (POPOVER DARI REPORT MATRIX)
// ==========================================================================
function openMonthlyTargetModal(m, y) {
  const modal = document.getElementById('monthlyTargetModal');
  if (!modal) return;

  const targetM = (m !== undefined && m !== null) ? parseInt(m, 10) : (activeMonth || (new Date().getMonth() + 1));
  const targetY = (y !== undefined && y !== null) ? parseInt(y, 10) : (activeYear || new Date().getFullYear());

  const mSelect = document.getElementById('modalTargetMonthSelect');
  const yInput = document.getElementById('modalTargetYearInput');
  if (mSelect) mSelect.value = String(targetM);
  if (yInput) yInput.value = String(targetY);

  onModalTargetMonthChange();
  modal.style.display = 'flex';
}

function closeMonthlyTargetModal() {
  const modal = document.getElementById('monthlyTargetModal');
  if (modal) modal.style.display = 'none';
}

function formatRupiahMask(val) {
  const digits = String(val ?? '').replace(/\D/g, '');
  if (!digits) return '';
  return parseInt(digits, 10).toLocaleString('id-ID');
}

function parseRupiahMask(val) {
  const digits = String(val ?? '').replace(/\D/g, '');
  return digits === '' ? 0 : parseInt(digits, 10);
}

function onModalTargetMonthChange() {
  const mSelect = document.getElementById('modalTargetMonthSelect');
  const yInput = document.getElementById('modalTargetYearInput');
  const teamInput = document.getElementById('modalTeamTargetInput');
  if (!mSelect || !yInput) return;

  const m = parseInt(mSelect.value, 10);
  const y = parseInt(yInput.value, 10);
  const key = getMonthlyTargetKey(m, y);
  const monthData = cachedMonthlyTargets[key] || {};

  const masterList = getMasterSales()
    .filter(x => x.active)
    .sort((a, b) => {
      const levelA = (a.level || 'Karyawan').trim().toLowerCase();
      const levelB = (b.level || 'Karyawan').trim().toLowerCase();
      if (levelA !== levelB) {
        return levelA === 'karyawan' ? -1 : 1;
      }
      return 0;
    });
  let defaultTeam = 0;
  masterList.forEach(x => { defaultTeam += (Number(x.target) || 867261886); });
  if (defaultTeam <= 0) defaultTeam = 8447356034;

  const teamTarget = monthData.teamTarget !== undefined ? Number(monthData.teamTarget) : defaultTeam;
  if (teamInput) teamInput.value = formatRupiahMask(teamTarget);

  const container = document.getElementById('modalSalesTargetListContainer');
  if (container) {
    container.innerHTML = masterList.map(item => {
      let currentVal = item.target || 867261886;
      if (monthData.salesTargets) {
        if (monthData.salesTargets[item.code] !== undefined) {
          currentVal = Number(monthData.salesTargets[item.code]);
        } else if (monthData.salesTargets[item.name] !== undefined) {
          currentVal = Number(monthData.salesTargets[item.name]);
        }
      }
      const gpVal = Math.round(currentVal * 0.05);
      const isIntern = (item.level || '').toLowerCase() === 'internship';
      const levelBadge = isIntern
        ? `<span style="display:inline-block; font-size:10px; font-weight:700; color:#7c3aed; background:#f3e8ff; border:1px solid #ddd6fe; border-radius:4px; padding:1px 6px; margin-left:6px;">Internship</span>`
        : `<span style="display:inline-block; font-size:10px; font-weight:700; color:#2563eb; background:#eff6ff; border:1px solid #bfdbfe; border-radius:4px; padding:1px 6px; margin-left:6px;">Karyawan</span>`;
      return `
        <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: nowrap; box-shadow: 0 1px 2px rgba(15,23,42,0.03);">
          <div style="min-width: 0; flex: 1;">
            <div style="font-weight: 800; font-size: 0.88rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center;">${item.name} ${levelBadge}</div>
            <div style="font-size: 0.73rem; color: #64748b; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;">
              <span style="font-family: monospace; font-weight: 700; color: #475569;">${item.code}</span> &bull; Cabang ${item.cabang || '-'}
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 12px; flex-shrink: 0;">
            <div style="font-size: 0.72rem; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 3px 8px; text-align: right; white-space: nowrap;" id="modalRowGP_${item.code}">
              GP: ${formatRupiah(gpVal)}
            </div>
            <div style="position: relative; width: 200px;">
              <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.74rem; font-weight: 700; color: #94a3b8; pointer-events: none;">Rp</span>
              <input type="text" 
                     id="modalSalesInput_${item.code}" 
                     data-code="${item.code}" 
                     data-name="${item.name}" 
                     value="${formatRupiahMask(currentVal)}" 
                     placeholder="867.261.886"
                     oninput="onModalSalesRowChange('${item.code}', this)" 
                     class="form-input modal-sales-target-input" 
                     style="padding-left: 32px; padding-right: 12px; text-align: right; font-weight: 700; font-size: 0.88rem; width: 100%; border-radius: 8px; font-family: monospace;">
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  updateModalTargetSummary();
}

function onModalTeamInputChange(inp) {
  if (inp) {
    const num = parseRupiahMask(inp.value);
    inp.value = num > 0 ? num.toLocaleString('id-ID') : '';
  }
  updateModalTargetSummary();
}

function onModalSalesRowChange(code, inp) {
  const el = inp || document.getElementById(`modalSalesInput_${code}`);
  if (el) {
    const num = parseRupiahMask(el.value);
    el.value = num > 0 ? num.toLocaleString('id-ID') : '';
    const gpEl = document.getElementById(`modalRowGP_${code}`);
    if (gpEl) {
      gpEl.innerText = `GP: ${formatRupiah(Math.round(num * 0.05))}`;
    }
  }
  updateModalTargetSummary();
}

function updateModalTargetSummary() {
  const teamInput = document.getElementById('modalTeamTargetInput');
  const teamVal = teamInput ? parseRupiahMask(teamInput.value) : 0;

  const teamGPEl = document.getElementById('modalTeamGPPreview');
  if (teamGPEl) {
    teamGPEl.innerText = `GP 5%: ${formatRupiah(Math.round(teamVal * 0.05))}`;
  }

  let sumSales = 0;
  const inputs = document.querySelectorAll('.modal-sales-target-input');
  inputs.forEach(inp => {
    sumSales += parseRupiahMask(inp.value);
  });

  const footerTotalEl = document.getElementById('modalFooterTotalSales');
  if (footerTotalEl) {
    footerTotalEl.innerText = formatRupiah(sumSales);
  }

  const statusEl = document.getElementById('modalTargetBalanceStatus');
  if (statusEl) {
    const diff = sumSales - teamVal;
    if (Math.abs(diff) < 1000) {
      statusEl.style.color = '#059669';
      statusEl.innerText = '✅ Seimbang dengan Target Tim';
    } else if (diff > 0) {
      statusEl.style.color = '#2563eb';
      statusEl.innerText = `ℹ️ Sales > Target Tim (+${formatRupiah(diff)})`;
    } else {
      statusEl.style.color = '#d97706';
      statusEl.innerText = `⚠️ Sales < Target Tim (-${formatRupiah(Math.abs(diff))})`;
    }
  }
}

function modalBagiRataSales() {
  const teamInput = document.getElementById('modalTeamTargetInput');
  const teamVal = teamInput ? parseRupiahMask(teamInput.value) : 0;
  const inputs = document.querySelectorAll('.modal-sales-target-input');
  if (inputs.length === 0 || teamVal <= 0) {
    alert('Isi Target Tim terlebih dahulu.');
    return;
  }
  const perSales = Math.round(teamVal / inputs.length);
  inputs.forEach(inp => {
    inp.value = formatRupiahMask(perSales);
    const code = inp.getAttribute('data-code');
    const gpEl = document.getElementById(`modalRowGP_${code}`);
    if (gpEl) gpEl.innerText = `GP: ${formatRupiah(Math.round(perSales * 0.05))}`;
  });
  updateModalTargetSummary();
}

function modalHitungTotalSales() {
  let sumSales = 0;
  const inputs = document.querySelectorAll('.modal-sales-target-input');
  inputs.forEach(inp => {
    sumSales += parseRupiahMask(inp.value);
  });
  const teamInput = document.getElementById('modalTeamTargetInput');
  if (teamInput) {
    teamInput.value = formatRupiahMask(sumSales);
  }
  updateModalTargetSummary();
}

async function saveModalMonthlyTargets() {
  const mSelect = document.getElementById('modalTargetMonthSelect');
  const yInput = document.getElementById('modalTargetYearInput');
  const teamInput = document.getElementById('modalTeamTargetInput');

  const m = mSelect ? parseInt(mSelect.value, 10) : (activeMonth || 11);
  const y = yInput ? parseInt(yInput.value, 10) : (activeYear || 2026);
  const key = getMonthlyTargetKey(m, y);

  const teamTarget = teamInput ? (parseRupiahMask(teamInput.value) || 8447356034) : 8447356034;
  const salesTargets = {};
  const masterList = getMasterSales();

  const inputs = document.querySelectorAll('.modal-sales-target-input');
  inputs.forEach(inp => {
    const code = inp.getAttribute('data-code');
    const name = inp.getAttribute('data-name');
    const val = parseRupiahMask(inp.value);
    if (code) salesTargets[code] = val;
    if (name) salesTargets[name] = val;

    if (m === activeMonth && y === activeYear) {
      const item = masterList.find(x => x.code === code || x.name === name);
      if (item) item.target = val;
    }
  });

  cachedMonthlyTargets[key] = {
    month: m,
    year: y,
    teamTarget: teamTarget,
    salesTargets: salesTargets
  };

  saveMasterSales(masterList);
  await persistMonthlyTargetsToServer();

  closeMonthlyTargetModal();

  if (document.getElementById('adminTargetMonthSelect')) {
    onAdminTargetMonthChange();
  }

  const mName = mSelect ? mSelect.options[mSelect.selectedIndex].text : `Bulan ${m}`;
  showToast(`🎯 Target omzet ${mName} ${y} (${formatRupiah(teamTarget)}) berhasil disimpan dan langsung diperbarui!`);

  processAllReports();
}

function addNewMasterCode() {
  const code = document.getElementById('newMasterCode').value.trim();
  const name = document.getElementById('newMasterName').value.trim().toUpperCase();
  if (!code || !name) {
    alert("Isi kode dan nama PIC sales!");
    return;
  }
  const masterList = getMasterSales();
  masterList.push({ code, name, target: 867261886, active: true });
  saveMasterSales(masterList);
  document.getElementById('newMasterCode').value = '';
  document.getElementById('newMasterName').value = '';
  populateAdminPanel(masterList);
  processAllReports();
}

function toggleSalesActive(index) {
  const masterList = getMasterSales();
  if (masterList[index]) {
    masterList[index].active = !masterList[index].active;
    saveMasterSales(masterList);
    populateAdminPanel(masterList);
    processAllReports();
  }
}

function resetMasterToDefault() {
  if (confirm("Reset seluruh target dan master sales ke master database?")) {
    localStorage.removeItem('gadgetmart_sosmed_master');
    dynamicMasterSales = null;
    fetchWorkspaceSettings();
  }
}

// 11. UNDUH GAMBAR PNG LENGKAP TANPA TERPOTONG SCROLLBAR
function downloadReportAsPNG() {
  if (currentActiveTab === 2) {
    downloadElementAsPNG('report2Container', 'Report2_KPI_Summary_Timegone');
  } else if (currentActiveTab === 3) {
    downloadElementAsPNG('arResultContainer', 'Cek_AR_Invoice_Status');
  } else if (currentActiveTab === 4) {
    downloadElementAsPNG('tabContent4', 'Data_Keep_Customer');
  } else if (currentActiveTab === 5) {
    downloadElementAsPNG('tabContent5', 'Audit_AR_Belum_Sosmed');
  } else if (currentActiveTab === 6) {
    downloadElementAsPNG('tabContent6', 'Arsip_Pencapaian_Bulanan');
  } else {
    downloadElementAsPNG('report1Container', 'Report1_Daily_Achievement_Sosmed');
  }
}

async function downloadElementAsPNG(elementId, filePrefix) {
  const el = document.getElementById(elementId);
  if (!el) {
    alert("Elemen report tidak ditemukan.");
    return;
  }

  // Visual button feedback without mutating layout or page structure
  const btn = (typeof event !== 'undefined' && event && event.currentTarget) 
    ? event.currentTarget 
    : (el.querySelector('.btn-download-themed') || document.getElementById('btnDownloadPNG'));
  const origBtnContent = btn ? btn.innerHTML : '';
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = `<span>⏳ Menyiapkan Gambar...</span>`;
  }

  try {
    // We strictly use onclone hook so the LIVE DOM on user screen is NEVER touched or shifted
    const canvas = await html2canvas(el, {
      scale: 2, // High resolution Retina quality
      useCORS: true,
      logging: false,
      backgroundColor: '#ffffff',
      onclone: (clonedDoc) => {
        const clonedEl = clonedDoc.getElementById(elementId);
        if (!clonedEl) return;

        // 1. Hide all interactive buttons in the exported image
        clonedEl.querySelectorAll('button, .btn, .btn-download-themed, [data-html2canvas-ignore="true"]').forEach(b => {
          b.style.display = 'none';
        });

        // 2. Unroll scroll area completely in the cloned document so all 31 days are visible
        clonedEl.querySelectorAll('.table-responsive, .report1-scroll-area').forEach(sc => {
          sc.style.setProperty('max-height', 'none', 'important');
          sc.style.setProperty('height', 'auto', 'important');
          sc.style.setProperty('overflow', 'visible', 'important');
          sc.style.setProperty('overflow-x', 'visible', 'important');
          sc.style.setProperty('overflow-y', 'visible', 'important');
        });

        // 3. Set sticky elements to static positioning in clone only so html2canvas renders natural order
        clonedEl.querySelectorAll('.col-sticky, thead, thead th, tfoot, tfoot tr, tfoot td').forEach(st => {
          st.style.setProperty('position', 'static', 'important');
          st.style.setProperty('top', 'auto', 'important');
          st.style.setProperty('bottom', 'auto', 'important');
          st.style.setProperty('left', 'auto', 'important');
          st.style.setProperty('box-shadow', 'none', 'important');
        });

        // 4. Force clone container width and remove clipping
        clonedEl.style.setProperty('overflow', 'visible', 'important');
        clonedEl.style.setProperty('width', 'max-content', 'important');
        clonedEl.style.setProperty('max-width', 'none', 'important');

        // 5. Sembunyikan label Nilai DPP, INTERNSHIP, dan HARI INI pada hasil unduh gambar
        clonedEl.querySelectorAll('#modeIndicatorTag, .pill-badge-red, .matrix-intern-badge, .today-tag').forEach(b => {
          b.style.setProperty('display', 'none', 'important');
        });
      }
    });

    const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const mStr = monthNames[Number(activeMonth)] || activeMonth;
    const link = document.createElement('a');
    link.download = `GadgetMart_${filePrefix}_${mStr}_${activeYear}_${new Date().toISOString().slice(0,10)}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
  } catch (err) {
    console.error("Gagal membuat PNG:", err);
    alert("Gagal mengunduh gambar PNG. Pastikan tabel ditampilkan.");
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = origBtnContent;
    }
  }
}

// 12. CALCULATION MODE TOGGLE (DPP VS GROSS)
function setCalculationMode(mode) {
  currentCalculationMode = mode;
  const btnDPP = document.getElementById('btnModeDPP');
  const btnGross = document.getElementById('btnModeGross');
  if (btnDPP) btnDPP.classList.toggle('active', mode === 'DPP');
  if (btnGross) btnGross.classList.toggle('active', mode === 'GROSS');
  const tag = document.getElementById('modeIndicatorTag');
  if (tag) tag.innerText = mode === 'DPP' ? 'Nilai DPP (Sebelum PPN 11%)' : 'Nilai Gross (Harga Jual Fisik)';
  processAllReports();
}

// 13. GEMINI AI SMART SALES ASSISTANT
async function executeGeminiPrompt() {
  const input = document.getElementById('geminiPromptInput');
  const prompt = input.value.trim();
  if (!prompt) return;

  const card = document.getElementById('aiResponseCard');
  const body = document.getElementById('aiResponseContent');
  const time = document.getElementById('aiResponseTime');
  card.style.display = 'block';
  time.innerText = new Date().toLocaleTimeString('id-ID');
  body.innerHTML = '<div style="color:var(--crimson-primary);font-weight:700;">✨ Gemini sedang menganalisis query...</div>';

  // 13A. Check if query is checking WhatsApp number e.g. "cek wa 081386413707"
  const waMatch = prompt.match(/(?:wa|whatsapp|nomor|no\s*hp|telepon|hp)?\s*(\+?62\d{8,13}|08\d{8,12})/i);
  if (waMatch) {
    const phone = waMatch[1];
    quickCheckAR(phone);
    body.innerHTML = `<p>📱 <strong>Hasil Pencarian Pelanggan WhatsApp (${phone}):</strong> Layar telah beralih ke <strong>Tab 3 (Cek AR & WhatsApp)</strong> untuk menampilkan profil pelanggan dan rincian seluruh produk yang pernah dibeli!</p>`;
    return;
  }

  // 13B. Check if query is checking AR number e.g. "cek ar 260274906"
  const arMatch = prompt.match(/\b(26\d{7})\b/);
  if (arMatch) {
    const docNo = arMatch[1];
    quickCheckAR(docNo);
    body.innerHTML = `<p>🔍 <strong>Hasil Pengecekan AR ${docNo}:</strong> Layar telah beralih ke <strong>Tab 2 (Cek AR & WhatsApp)</strong> untuk menampilkan verifikasi kode sales sosmed dan rincian produk per item.</p>`;
    return;
  }

  // 13C. Check if query is asking about Keep data
  if (prompt.toLowerCase().includes('keep')) {
    switchTab(3);
    body.innerHTML = `<p>📋 <strong>Layar Beralih ke Tab 3 (Data Keep Customer):</strong> Menampilkan data keep Google Sheets dengan filter Kode Sosmed, Status, dan Periode. Filter dapat disesuaikan langsung di layar.</p>`;
    return;
  }

  const apiKey = localStorage.getItem('gemini_api_key') || DEFAULT_GEMINI_API_KEY;
  const model = localStorage.getItem('gemini_model') || DEFAULT_GEMINI_MODEL;

  if (apiKey) {
    try {
      const context = {
        totalAchieve: document.getElementById('kpiTotalAchieve').innerText,
        totalGP: document.getElementById('kpiTotalGP').innerText,
        timegone: `${timegonePct}%`,
        targetGP: document.getElementById('kpiTargetGP').innerText
      };
      const res = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          contents: [{ parts: [{ text: `Kamu adalah Senior Commercial Analyst GadgetMart. Berikan evaluasi eksekutif berdasarkan data berikut: ${JSON.stringify(context)}. Pertanyaan: "${prompt}". Jawab singkat, padat, dan tajam dalam Bahasa Indonesia.` }] }]
        })
      });
      const data = await res.json();
      if (data.candidates && data.candidates[0].content) {
        body.innerHTML = data.candidates[0].content.parts[0].text.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        handlePromptHighlight(prompt);
        return;
      }
    } catch(e) { console.warn("Gemini API call fallback to local engine:", e); }
  }

  // Fallback Smart Local Engine
  setTimeout(() => {
    const q = prompt.toLowerCase();
    let text = '';
    if (q.includes('hari ini') || q.includes('daily')) {
      switchTab(1);
      setReportViewMode('r1');
      const row = parsedDailyMatrix[lastActiveDay] || {};
      text = `<p>📌 <strong>Laporan Harian per Tgl ${row.dateStr}:</strong> Total achieve hari ini adalah <strong>${formatRupiah(row.totalDay)}</strong> dengan rata-rata per PIC <strong>${formatRupiah(row.avg)}</strong>. Baris tanggal ini telah disorot di Tab 1!</p>`;
      handlePromptHighlight(prompt);
    } else if (q.includes('timegone') || q.includes('gp') || q.includes('kpi')) {
      switchTab(1);
      setReportViewMode('r2');
      text = `<p>🎯 <strong>Evaluasi KPI vs Timegone (${timegonePct}%):</strong> Capaian omzet tim berada di posisi <strong>${document.getElementById('kpiProgressPct').innerText}</strong> (Status: <strong>🖤 On Track</strong>). Perhatikan performa GP yang masih perlu didorong menuju target 5%!</p>`;
    } else {
      text = `<p>Data berhasil dievaluasi untuk <em>"${prompt}"</em>. Total pencapaian tim per tanggal terakhir adalah <strong>${document.getElementById('kpiTotalAchieve').innerText}</strong>.</p>`;
    }
    body.innerHTML = text;
  }, 350);
}

function handlePromptHighlight(prompt) {
  document.querySelectorAll('.row-highlight').forEach(el => el.classList.remove('row-highlight'));
  const targetTr = document.getElementById(`row-date-${lastActiveDay}`);
  if (targetTr) {
    targetTr.classList.add('row-highlight');
    targetTr.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

function quickPrompt(text) {
  document.getElementById('geminiPromptInput').value = text;
  executeGeminiPrompt();
}

function resetTableHighlight() {
  document.querySelectorAll('.row-highlight').forEach(el => el.classList.remove('row-highlight'));
}

function filterTableDate(query) {
  const q = query.trim().toLowerCase();
  for (let d = 1; d <= daysInMonth; d++) {
    const tr = document.getElementById(`row-date-${d}`);
    if (!tr) continue;
    const dateStr = parsedDailyMatrix[d].dateStr;
    tr.style.display = (!q || dateStr.includes(q)) ? '' : 'none';
  }
}

function handleTurnoverFileSelect(e) {
  const file = e.target.files[0];
  if (!file) return;

  const fileNameLower = (file.name || '').toLowerCase();
  if (fileNameLower.includes('croselling') || fileNameLower.includes('crosselling')) {
    handleCrosellingFileSelect(e);
    return;
  }

  const fileSizeMb = (file.size / (1024 * 1024)).toFixed(1);
  showSaveToast(`⏳ Sedang membaca file Excel (${fileSizeMb} MB)... Mohon tunggu beberapa detik.`);
  const reader = new FileReader();
  reader.onload = async (evt) => {
    try {
      const buffer = evt.target.result;
      const jsonData = readTurnoverJsonFromBuffer(buffer);
      if (!jsonData || jsonData.length === 0) {
        alert("File Excel turnover kosong atau sheet transaksi tidak terbaca!");
        return;
      }
      // Check if it's actually croselling data (has 'No HP Customer' and not 'DPP')
      const firstRow = jsonData[0] || {};
      if ('No HP Customer' in firstRow && !('DPP' in firstRow)) {
        rawCrosellingRows = jsonData;
        const updateTs = getFormattedTimestamp();
        await saveFileToIndexedDB('croselling_file', buffer);
        localStorage.setItem('gadgetmart_croselling_timestamp', updateTs);
        updateCrosellingIndicator(`File Cross Selling terupdate per ${updateTs}`);
        saveExcelToServer(buffer, 'croselling');
        if (typeof runARMissingSosmedAudit === 'function') {
          runARMissingSosmedAudit();
        }
        alert(`✅ File terdeteksi sebagai data Cross Selling (${jsonData.length.toLocaleString('id-ID')} baris data)!\nDatabase Cek AR & Audit AR telah diperbarui.`);
        return;
      }

      rawTurnoverRows = jsonData;
      const updateTs = getFormattedTimestamp();
      await saveFileToIndexedDB('turnover_file', buffer);
      localStorage.setItem('gadgetmart_turnover_timestamp', updateTs);
      localStorage.setItem('gadgetmart_turnover_is_custom', 'true');
      updateTurnoverIndicator('Database Turnover Realtime (MySQL)');
      saveExcelToServer(buffer, 'turnover');
      processAllReports();
      alert(`✅ Berhasil mengunggah file turnover (${jsonData.length.toLocaleString('id-ID')} baris transaksi)!\nDashboard & Laporan Pencapaian telah diperbarui dan data tersimpan permanen.`);
    } catch (err) {
      console.error("Gagal membaca file turnover:", err);
      alert(`Gagal membaca file Excel turnover: ${err.message || 'Format file atau memori peramban tidak mencukupi'}.`);
    }
  };
  reader.readAsArrayBuffer(file);
}

function handleCrosellingFileSelect(e) {
  const file = e.target.files[0];
  if (!file) return;
  const fileSizeMb = (file.size / (1024 * 1024)).toFixed(1);
  showSaveToast(`⏳ Sedang membaca file Cross Selling (${fileSizeMb} MB)... Mohon tunggu beberapa detik.`);
  const reader = new FileReader();
  reader.onload = async (evt) => {
    try {
      const buffer = evt.target.result;
      const workbook = XLSX.read(buffer, { type: 'array' });
      const sheetName = workbook.SheetNames[0];
      const sheet = workbook.Sheets[sheetName];
      const jsonData = XLSX.utils.sheet_to_json(sheet);
      if (!jsonData || jsonData.length === 0) {
        alert("File Excel Cross Selling kosong atau tidak terbaca!");
        return;
      }
      rawCrosellingRows = jsonData;
      const updateTs = getFormattedTimestamp();
      await saveFileToIndexedDB('croselling_file', buffer);
      localStorage.setItem('gadgetmart_croselling_timestamp', updateTs);
      updateCrosellingIndicator(`File Cross Selling terupdate per ${updateTs} (Superadmin Upload)`);
      uploadCrosellingFileToServer(buffer, file.name);
      if (typeof runARMissingSosmedAudit === 'function') {
        runARMissingSosmedAudit();
      }
      alert(`✅ Berhasil membaca file Cross Selling (${jsonData.length.toLocaleString('id-ID')} baris data)!\nProses sinkronisasi ke server sedang berjalan di latar belakang...`);
      if (document.getElementById('arInputSearch') && document.getElementById('arInputSearch').value.trim()) {
        executeARSearch();
      }
    } catch (err) {
      console.error("Gagal membaca file cross selling:", err);
      alert("Gagal membaca file Excel Cross Selling. Pastikan format file valid.");
    }
  };
  reader.readAsArrayBuffer(file);
}

// UPLOAD & CHUNKED SYNC ENGINE FOR CROSS SELLING (SUPERADMIN TO ALL PCS)
async function uploadCrosellingFileToServer(buffer, originalName = 'CROSSELLING-12102026.xlsx') {
  const chunkSize = 2.5 * 1024 * 1024; // 2.5 MB per chunk (aman dari batas 4.5MB Vercel)
  const totalSize = buffer.byteLength;

  if (totalSize <= chunkSize) {
    try {
      showSaveToast(`⏳ Menyinkronkan file Cross Selling ke server pusat...`);
      const res = await fetch('/api/save-excel?type=croselling', {
        method: 'POST',
        headers: { 'Content-Type': 'application/octet-stream' },
        body: buffer
      });
      if (res.ok) {
        const data = await res.json();
        showSaveToast(`✅ Database Cross Selling tersimpan di server & otomatis tersinkron ke seluruh PC!`);
        if (data.mtimeMs) lastKnownSyncStatus.crosellingMtime = data.mtimeMs;
      } else {
        const err = await res.json().catch(() => ({}));
        showSaveToast(`⚠️ ${err.error || 'Gagal sinkron ke server'}`);
      }
    } catch (e) {
      console.warn('Gagal upload single request:', e);
    }
    return;
  }

  // Chunked upload untuk file besar (> 2.5 MB)
  const totalChunks = Math.ceil(totalSize / chunkSize);
  const uploadId = 'cross_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8);
  showSaveToast(`⏳ Memulai sinkronisasi file (${(totalSize / 1024 / 1024).toFixed(1)} MB) ke server...`);

  for (let i = 0; i < totalChunks; i++) {
    const start = i * chunkSize;
    const end = Math.min(start + chunkSize, totalSize);
    const chunkBlob = buffer.slice(start, end);
    const percent = Math.round(((i + 1) / totalChunks) * 100);
    showSaveToast(`⏳ Menyinkronkan data ke server: ${percent}% (bagian ${i + 1}/${totalChunks})...`);

    try {
      const res = await fetch('/api/upload-excel-chunk', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/octet-stream',
          'X-Upload-Id': uploadId,
          'X-Chunk-Index': String(i),
          'X-Total-Chunks': String(totalChunks),
          'X-File-Type': 'croselling',
          'X-Original-Filename': originalName
        },
        body: chunkBlob
      });

      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        showSaveToast(`⚠️ Gagal pada bagian ${i + 1}: ${err.error || 'Server error'}`);
        return;
      }

      const resData = await res.json();
      if (resData.completed) {
        showSaveToast(`✅ Berhasil! File Cross Selling telah tersimpan di Server & otomatis tersinkron ke semua PC staf.`);
        if (resData.mtimeMs) lastKnownSyncStatus.crosellingMtime = resData.mtimeMs;
      }
    } catch (e) {
      console.error('Error uploading chunk:', e);
      showSaveToast(`⚠️ Gangguan jaringan saat menyinkronkan file.`);
      return;
    }
  }
}

// FORCE SYNC DARI SERVER (UNTUK AKUN STAF / MANUAL CHECK)
async function forceSyncCrosellingFromServer() {
  showSaveToast('⏳ Memeriksa pembaruan database Cross Selling dari Superadmin...');
  try {
    const resStatus = await fetch(`/api/database-status?t=${Date.now()}`, { cache: 'no-store' });
    if (!resStatus.ok) throw new Error('Gagal terhubung ke server');
    const s = await resStatus.json();
    
    const res = await fetch(`/api/get-excel?type=croselling&t=${Date.now()}`);
    if (!res.ok) throw new Error('File belum tersedia di server');
    
    const buf = await res.arrayBuffer();
    parseCrosellingBuffer(buf);
    await saveFileToIndexedDB('croselling_file', buf);
    const uploader = (s.croselling && s.croselling.uploader) || 'Superadmin';
    const ts = getFormattedTimestamp();
    localStorage.setItem('gadgetmart_croselling_timestamp', ts);
    updateCrosellingIndicator(`File Cross Selling terupdate per ${ts} (Sinkron dari ${uploader})`);
    showSaveToast(`✅ Sukses! Database Cross Selling telah tersinkronisasi terbaru dari ${uploader}.`);
  } catch (err) {
    console.error('Gagal force sync:', err);
    showSaveToast(`⚠️ Gagal sinkronisasi: ${err.message || 'Server belum memiliki data custom'}`);
  }
}
window.forceSyncCrosellingFromServer = forceSyncCrosellingFromServer;

function updateCrosellingIndicator(text) {
  const el = document.getElementById('crosellingUpdateText');
  if (el) el.innerText = text;
}

// Function alias for backwards compatibility
function handleFileSelect(e) {
  handleTurnoverFileSelect(e);
}

const dropZone = document.getElementById('dropZone');
if (dropZone) {
  dropZone.addEventListener('dragover', (e) => { e.preventDefault(); });
  dropZone.addEventListener('dragleave', (e) => { e.preventDefault(); });
  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    alert('ℹ️ Seluruh upload file data SAP (Cross-Selling & Turnover) kini dipusatkan di 1 Pintu: Pusat Unggah Data.\nSilakan gunakan menu Pusat Unggah Data.');
  });
}

// Dedicated Cross Selling dropzone (diarahkan ke Pusat Unggah Data)
const crosellingDropZone = document.getElementById('crosellingDropZone');
if (crosellingDropZone) {
  crosellingDropZone.addEventListener('dragover', (e) => { e.preventDefault(); });
  crosellingDropZone.addEventListener('dragleave', (e) => { e.preventDefault(); });
  crosellingDropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    alert('ℹ️ Seluruh upload file data SAP (Cross-Selling & Turnover) kini dipusatkan di 1 Pintu: Pusat Unggah Data.\nSilakan gunakan menu Pusat Unggah Data.');
  });
}

// ==========================================================================
// 14. REALTIME CROSS-DEVICE SYNC ENGINE & SERVER DUAL-LOADER
// ==========================================================================
let lastKnownSyncStatus = {
  turnoverMtime: 0,
  crosellingMtime: 0,
  archivesMtime: 0,
  targetsMtime: 0,
  photosMtime: 0,
  keepMtime: 0
};

// LOAD BOTH DEFAULT EXCEL FILES - PRIORITASKAN CUSTOM UPLOAD (DATABASE / INDEXEDDB) AGAR DATA TIDAK HILANG SAAT REFRESH
async function loadAllDefaultFiles() {
  const statusTitle = document.getElementById('fileStatusTitle');
  if (statusTitle) statusTitle.innerText = 'Menghubungkan ke Server Database...';
  
  // 1. Fetch turnover.xlsx / SQL Database Records:
  let turnoverLoaded = false;

  // A0. Prioritas Utama: Ambil langsung dari Database SQL MySQL (/api/turnover-records) untuk kecepatan maksimal & anti-kosong
  try {
    const resTurnoverJson = await fetch(`/api/turnover-records?format=client&sosmed_only=1&t=${Date.now()}`);
    if (resTurnoverJson.ok) {
      const dataTurnover = await resTurnoverJson.json();
      if (dataTurnover && dataTurnover.success && Array.isArray(dataTurnover.rows) && dataTurnover.rows.length > 0) {
        rawTurnoverRows = dataTurnover.rows;
        turnoverLoaded = true;
        const detected = autoDetectTurnoverMonth(dataTurnover.rows);
        if (detected) {
          activeMonth = detected.month;
          activeYear = detected.year;
          daysInMonth = new Date(activeYear, activeMonth, 0).getDate();
          const dashSel = document.getElementById('dashFilterMonth');
          if (dashSel) dashSel.value = String(activeMonth);
        }
        updateTurnoverIndicator('Database Turnover Realtime (MySQL)');
        processAllReports();
        updateDatabaseStatus();
        console.log(`✅ ${dataTurnover.rows.length} transaksi turnover dimuat langsung dari Database SQL!`);
      }
    }
  } catch (e) {
    console.warn('Gagal fetch turnover JSON dari database server:', e);
  }

  // A. Fallback jika API JSON gagal: Ambil dari Database Server (/api/get-excel?type=turnover)
  if (!turnoverLoaded) {
    try {
      const resTurnover = await fetch(`/api/get-excel?type=turnover&t=${Date.now()}`);
      if (resTurnover.ok) {
        const isDbCustom = resTurnover.headers.get('X-Custom-File') === 'true';
        const serverTs = resTurnover.headers.get('X-File-Timestamp');
        const uploader = resTurnover.headers.get('X-File-Uploader') || 'Superadmin';
        const buf1 = await resTurnover.arrayBuffer();
        if (buf1 && buf1.byteLength > 100) {
          parseTurnoverBuffer(buf1);
          turnoverLoaded = true;
          await saveFileToIndexedDB('turnover_file', buf1);
          localStorage.setItem('gadgetmart_turnover_is_custom', 'true');
          const ts = serverTs ? new Date(serverTs).toLocaleString('id-ID') : getFormattedTimestamp();
          localStorage.setItem('gadgetmart_turnover_timestamp', ts);
          updateTurnoverIndicator('Database Turnover Realtime (MySQL)');
          console.log('✅ turnover.xlsx dimuat dari database server!');
        }
      }
    } catch (e) {
      console.warn('Gagal fetch turnover dari server:', e);
    }
  }

  // B. Fallback ke IndexedDB jika koneksi server gagal/offline
  if (!turnoverLoaded) {
    try {
      const cachedTurnover = await getFileFromIndexedDB('turnover_file');
      if (cachedTurnover && cachedTurnover.byteLength > 100) {
        parseTurnoverBuffer(cachedTurnover);
        turnoverLoaded = true;
        updateTurnoverIndicator('Database Turnover Realtime (MySQL)');
        console.log('✅ turnover.xlsx dimuat dari IndexedDB lokal!');
      }
    } catch (e) {}
  }

  // C. Fallback ke file statis jika server belum punya custom upload
  if (!turnoverLoaded) {
    try {
      const resTurnover = await fetch(`/workspace-sosmed/turnover.xlsx?t=${Date.now()}`);
      if (resTurnover.ok) {
        const buf1 = await resTurnover.arrayBuffer();
        if (buf1 && buf1.byteLength > 100) {
          parseTurnoverBuffer(buf1);
          turnoverLoaded = true;
          updateTurnoverIndicator('Database Turnover Realtime (MySQL)');
        }
      }
    } catch (e) {}
  }

  // 2. Fetch CROSSELLING in Background (Async Non-blocking agar Report Matrix langsung tampil instan)
  loadCrosellingDataAsync();

  // Inisialisasi status sync server baseline
  try {
    const resStatus = await fetch(`/api/database-status?t=${Date.now()}`, { cache: 'no-store' });
    if (resStatus.ok) {
      const s = await resStatus.json();
      if (s.turnover && s.turnover.mtimeMs) lastKnownSyncStatus.turnoverMtime = s.turnover.mtimeMs;
      if (s.archives && s.archives.mtimeMs) lastKnownSyncStatus.archivesMtime = s.archives.mtimeMs;
      if (s.croselling && s.croselling.mtimeMs) lastKnownSyncStatus.crosellingMtime = s.croselling.mtimeMs;
      if (s.targets && s.targets.mtimeMs) lastKnownSyncStatus.targetsMtime = s.targets.mtimeMs;
      if (s.photos && s.photos.mtimeMs) lastKnownSyncStatus.photosMtime = s.photos.mtimeMs;
      if (s.keep && s.keep.mtimeMs) lastKnownSyncStatus.keepMtime = s.keep.mtimeMs;
    }
  } catch(e) {}
}

// Background Loader untuk Google Sheets / Croselling XLSX (tidak memblokir render Report Matrix)
async function loadCrosellingDataAsync() {
  let crosellingLoaded = false;

  // A0. Prioritas Utama: Ambil langsung dari Database SQL MySQL (/api/croselling-records) untuk kecepatan maksimal O(1) & anti-lag
  try {
    const resCrossJson = await fetch(`/api/croselling-records?format=client&sosmed_only=1&t=${Date.now()}`);
    if (resCrossJson.ok) {
      const dataCross = await resCrossJson.json();
      if (dataCross && dataCross.success && Array.isArray(dataCross.rows) && dataCross.rows.length > 0) {
        rawCrosellingRows = dataCross.rows;
        crosellingLoaded = true;
        console.log(`✅ ${dataCross.rows.length} data cross-selling dimuat langsung dari Database SQL!`);
        updateDatabaseStatus();
        if (typeof rawTurnoverRows !== 'undefined' && rawTurnoverRows.length > 0 && typeof processAllReports === 'function') {
          processAllReports();
        }
      }
    }
  } catch (e) {
    console.warn('Gagal fetch cross-selling JSON dari database server:', e);
  }

  if (!crosellingLoaded) {
    try {
      const resCroselling = await fetch(`/api/get-excel?type=croselling&t=${Date.now()}`);
      if (resCroselling.ok) {
        const uploader = resCroselling.headers.get('X-File-Uploader') || 'Superadmin';
        const serverTs = resCroselling.headers.get('X-File-Timestamp');
        const buf2 = await resCroselling.arrayBuffer();
        parseCrosellingBuffer(buf2);
        crosellingLoaded = true;
        await saveFileToIndexedDB('croselling_file', buf2);
        const ts = serverTs ? new Date(serverTs).toLocaleString('id-ID') : (localStorage.getItem('gadgetmart_croselling_timestamp') || 'Default Sistem');
        localStorage.setItem('gadgetmart_croselling_timestamp', ts);
        updateCrosellingIndicator(`File Cross Selling terupdate per ${ts} (Sinkron dari ${uploader})`);
      }
    } catch(e) {}
  }

  if (!crosellingLoaded) {
    try {
      const cachedCroselling = await getFileFromIndexedDB('croselling_file');
      if (cachedCroselling) {
        parseCrosellingBuffer(cachedCroselling);
        crosellingLoaded = true;
        const ts = localStorage.getItem('gadgetmart_croselling_timestamp') || 'Tersimpan di Peramban';
        updateCrosellingIndicator(`File Cross Selling terupdate per ${ts}`);
      }
    } catch (e) {}
  }

  if (!crosellingLoaded) {
    try {
      const resCroselling = await fetch(`/workspace-sosmed/CROSSELLING-12102026.xlsx?t=${Date.now()}`);
      if (resCroselling.ok) {
        const buf2 = await resCroselling.arrayBuffer();
        parseCrosellingBuffer(buf2);
        crosellingLoaded = true;
        const ts = localStorage.getItem('gadgetmart_croselling_timestamp') || 'Default Sistem (12/10/2026)';
        updateCrosellingIndicator(`File Cross Selling terupdate per ${ts}`);
      }
    } catch (e) {}
  }

  if (!crosellingLoaded) {
    updateCrosellingIndicator('Belum diunggah di browser ini');
  }
}

// REALTIME POLLING: MENDETEKSI PERUBAHAN & EDIT DARI LAPTOP LAIN SECARA LIVE
async function pollServerSyncStatus() {
  try {
    const res = await fetch(`/api/database-status?t=${Date.now()}`, { cache: 'no-store' });
    if (!res.ok) return;
    const status = await res.json();
    if (!status) return;

    // A. Deteksi jika turnover.xlsx diperbarui dari laptop lain
    if (status.turnover && status.turnover.exists && status.turnover.mtimeMs) {
      if (lastKnownSyncStatus.turnoverMtime && status.turnover.mtimeMs > lastKnownSyncStatus.turnoverMtime) {
        console.log('🔄 [Sync Engine] File turnover.xlsx baru terdeteksi dari laptop lain!');
        lastKnownSyncStatus.turnoverMtime = status.turnover.mtimeMs;
        let resTurnover = await fetch(`/api/get-excel?type=turnover&t=${Date.now()}`);
        if (!resTurnover.ok) {
          resTurnover = await fetch(`/workspace-sosmed/turnover.xlsx?t=${Date.now()}`);
        }
        if (resTurnover.ok) {
          const buf = await resTurnover.arrayBuffer();
          parseTurnoverBuffer(buf);
          await saveFileToIndexedDB('turnover_file', buf);
          processAllReports();
          const uploader = status.turnover.uploader || 'Superadmin';
          updateTurnoverIndicator('Database Turnover Realtime (MySQL)');
          showSaveToast('🔄 File turnover.xlsx telah diperbarui otomatis dari server!');
        }
      } else if (!lastKnownSyncStatus.turnoverMtime) {
        lastKnownSyncStatus.turnoverMtime = status.turnover.mtimeMs;
      }
    }

    // B. Deteksi jika Arsip Bulanan / Gross Profit diedit dari laptop lain
    if (status.archives && status.archives.exists && status.archives.mtimeMs) {
      if (lastKnownSyncStatus.archivesMtime && status.archives.mtimeMs > lastKnownSyncStatus.archivesMtime) {
        console.log('🔄 [Sync Engine] Arsip bulanan / Gross Profit diperbarui dari laptop lain!');
        lastKnownSyncStatus.archivesMtime = status.archives.mtimeMs;
        const resArc = await fetch(`/api/monthly-archives?t=${Date.now()}`);
        if (resArc.ok) {
          cachedMonthlyArchives = await resArc.json();
          localStorage.setItem('gadgetmart_sosmed_archives', JSON.stringify(cachedMonthlyArchives));
          renderArchiveGrid();
          renderMonthToMonthCharts();
          showSaveToast('🔄 Data arsip bulanan & Gross Profit telah diperbarui otomatis dari laptop lain!');
        }
      } else if (!lastKnownSyncStatus.archivesMtime) {
        lastKnownSyncStatus.archivesMtime = status.archives.mtimeMs;
      }
    }

    // C. Deteksi jika File Cross Selling diperbarui dari laptop lain / Superadmin
    if (status.croselling && status.croselling.exists && status.croselling.mtimeMs) {
      if (lastKnownSyncStatus.crosellingMtime && status.croselling.mtimeMs > lastKnownSyncStatus.crosellingMtime) {
        console.log('🔄 [Sync Engine] File Cross Selling baru terdeteksi dari Superadmin!');
        lastKnownSyncStatus.crosellingMtime = status.croselling.mtimeMs;
        let resCross = await fetch(`/api/get-excel?type=croselling&t=${Date.now()}`);
        if (!resCross.ok) {
          resCross = await fetch(`/workspace-sosmed/CROSSELLING-12102026.xlsx?t=${Date.now()}`);
        }
        if (resCross.ok) {
          const buf = await resCross.arrayBuffer();
          parseCrosellingBuffer(buf);
          await saveFileToIndexedDB('croselling_file', buf);
          const uploader = status.croselling.uploader || 'Superadmin';
          const ts = getFormattedTimestamp();
          localStorage.setItem('gadgetmart_croselling_timestamp', ts);
          updateCrosellingIndicator(`File Cross Selling terupdate per ${ts} (Sinkron dari ${uploader})`);
          showSaveToast(`🔄 Database Cross Selling telah diperbarui otomatis dari ${uploader}!`);
        }
      } else if (!lastKnownSyncStatus.crosellingMtime) {
        lastKnownSyncStatus.crosellingMtime = status.croselling.mtimeMs;
      }
    }

    // D. Deteksi jika Target Bulanan diedit dari laptop lain
    if (status.targets && status.targets.exists && status.targets.mtimeMs) {
      if (lastKnownSyncStatus.targetsMtime && status.targets.mtimeMs > lastKnownSyncStatus.targetsMtime) {
        lastKnownSyncStatus.targetsMtime = status.targets.mtimeMs;
        await loadMonthlyTargets();
        renderMonthToMonthCharts();
      } else if (!lastKnownSyncStatus.targetsMtime) {
        lastKnownSyncStatus.targetsMtime = status.targets.mtimeMs;
      }
    }

    // E. Deteksi jika Foto Sales diedit dari laptop lain
    if (status.photos && status.photos.exists && status.photos.mtimeMs) {
      if (lastKnownSyncStatus.photosMtime && status.photos.mtimeMs > lastKnownSyncStatus.photosMtime) {
        lastKnownSyncStatus.photosMtime = status.photos.mtimeMs;
        await loadSalesPhotos();
        renderSalesLeaderboard('omzet');
      } else if (!lastKnownSyncStatus.photosMtime) {
        lastKnownSyncStatus.photosMtime = status.photos.mtimeMs;
      }
    }
  } catch (err) {
    // Silent fail if network briefly disconnected
  }
}

// ==========================================
// SERVER-SENT EVENTS (SSE) LIVE SEAMLESS PUSH
// ==========================================
let liveEventsSource = null;

function initLiveEventsSSE() {
  if (typeof EventSource === 'undefined') {
    console.warn('Browser tidak mendukung EventSource SSE');
    return;
  }
  if (liveEventsSource) {
    try { liveEventsSource.close(); } catch (e) {}
  }

  try {
    liveEventsSource = new EventSource('/api/events');

    liveEventsSource.onopen = () => {
      console.log('⚡ [SSE Live Push] Terhubung ke siaran real-time server lokal!');
    };

    liveEventsSource.addEventListener('REPORT_AR_UPDATED', (e) => {
      try {
        const data = JSON.parse(e.data || '{}');
        if (!data.nomorWA) return;
        const cleanWA = String(data.nomorWA).replace(/\D/g, '');
        const newAR = String(data.arDoc || '').trim();

        console.log(`⚡ [SSE Broadcast] REPORT AR Diperbarui secara live: WA=${cleanWA} -> AR=${newAR}`);

        // 1. Update memory rawKeepRows
        if (typeof rawKeepRows !== 'undefined' && Array.isArray(rawKeepRows)) {
          rawKeepRows.forEach(k => {
            if (String(k['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
              k['REPORT AR'] = newAR;
            }
          });
        }

        // 2. Update memory rawAuditARList & filteredAuditARList
        if (typeof rawAuditARList !== 'undefined' && Array.isArray(rawAuditARList)) {
          rawAuditARList.forEach(a => {
            if (a.keep && String(a.keep['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
              a.reportARDoc = newAR;
              a.keep['REPORT AR'] = newAR;
              if (newAR && typeof docMap !== 'undefined' && docMap.has(newAR)) {
                a.reportARItems = docMap.get(newAR);
                a.reportARDate = a.reportARItems[0]['Posting Date'];
                a.reportARCabang = a.reportARItems[0]['Cabang'];
              }
            }
          });
        }

        // 3. Highlight efek visual live update pada sel
        const targetCells = document.querySelectorAll(`.cell-report-ar-wrapper[data-wa="${cleanWA}"]`);
        targetCells.forEach(cell => {
          cell.classList.add('cell-live-updated');
          setTimeout(() => cell.classList.remove('cell-live-updated'), 2500);
        });

        // 4. Re-render tampilan yang aktif tanpa reload halaman
        const activeTab = document.querySelector('.tab-content.active');
        if (activeTab && activeTab.id === 'tab-audit-ar') {
          renderAuditPage();
        } else if (activeTab && activeTab.id === 'tab-keep-data') {
          renderKeepPage();
        }
      } catch (err) {
        console.warn('Gagal memproses event REPORT_AR_UPDATED:', err);
      }
    });

    liveEventsSource.addEventListener('KEEP_STATUS_UPDATED', (e) => {
      try {
        const data = JSON.parse(e.data || '{}');
        if (!data.nomorWA) return;
        const cleanWA = String(data.nomorWA).replace(/\D/g, '');
        const newStatus = String(data.status || '').trim().toUpperCase();

        console.log(`⚡ [SSE Broadcast] Status Keep Diperbarui secara live: WA=${cleanWA} -> Status=${newStatus}`);

        if (typeof rawKeepRows !== 'undefined' && Array.isArray(rawKeepRows)) {
          rawKeepRows.forEach(k => {
            if (String(k['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
              k['Status'] = newStatus;
            }
          });
        }

        if (typeof rawAuditARList !== 'undefined' && Array.isArray(rawAuditARList)) {
          rawAuditARList.forEach(a => {
            if (a.keep && String(a.keep['Nomor WA'] || '').replace(/\D/g, '') === cleanWA) {
              a.keep['Status'] = newStatus;
            }
          });
        }

        const selects = document.querySelectorAll(`select.select-status-keep`);
        selects.forEach(sel => {
          const tr = sel.closest('tr');
          if (tr && tr.innerHTML.includes(cleanWA)) {
            sel.value = newStatus;
            sel.setAttribute('data-prev', newStatus);
            sel.className = `select-status-keep ${getKeepStatusClass(newStatus)}`;
            tr.className = getAuditRowClassByKeepStatus(newStatus);
            tr.classList.add('cell-live-updated');
            setTimeout(() => tr.classList.remove('cell-live-updated'), 2500);
          }
        });
      } catch (err) {
        console.warn('Gagal memproses event KEEP_STATUS_UPDATED:', err);
      }
    });

    liveEventsSource.addEventListener('KEEP_CSV_SYNCED', () => {
      console.log('📡 [SSE Broadcast] File keep-data.csv disinkronkan di server.');
      loadKeepData(true);
    });

    liveEventsSource.onerror = (err) => {
      // EventSource otomatis mencoba reconnect secara berkala
    };
  } catch (e) {
    console.warn('Inisialisasi SSE gagal:', e);
  }
}

let syncEngineInterval = null;
function startRealtimeSyncEngine() {
  if (syncEngineInterval) clearInterval(syncEngineInterval);
  // Mulai siaran live SSE ke server
  initLiveEventsSSE();
  // Polling fallback status server setiap 10 detik
  syncEngineInterval = setInterval(pollServerSyncStatus, 10000);
  console.log('📡 Realtime Cross-Device SSE & Fallback Sync Engine aktif');

  // Mulai engine realtime polling format keep & pop-up toast notifikasi
  try {
    startKeepRealtimePolling();
  } catch (e) {
    console.warn('startKeepRealtimePolling error:', e);
  }
}


// Legacy function alias
function loadDefaultTurnover() {
  loadAllDefaultFiles();
}

// 15. EXPORT EXCEL
function exportActiveReport() {
  const wb = XLSX.utils.book_new();
  const tbl1 = document.getElementById('matrixTable');
  const ws1 = XLSX.utils.table_to_sheet(tbl1);
  XLSX.utils.book_append_sheet(wb, ws1, "DAILY_ACHIEVEMENT");

  const tbl2 = document.getElementById('kpiSummaryTable');
  const ws2 = XLSX.utils.table_to_sheet(tbl2);
  XLSX.utils.book_append_sheet(wb, ws2, "KPI_SUMMARY_GP");

  XLSX.writeFile(wb, `GadgetMart_Sosmed_Achievement_Report_${activeMonth}_${activeYear}.xlsx`);
}

// 16. MODALS
function openGeminiModal() {
  document.getElementById('geminiModal').style.display = 'flex';
  document.getElementById('geminiApiKeyInput').value = localStorage.getItem('gemini_api_key') || DEFAULT_GEMINI_API_KEY;
  document.getElementById('geminiModelSelect').value = localStorage.getItem('gemini_model') || DEFAULT_GEMINI_MODEL;
}
function closeGeminiModal() { document.getElementById('geminiModal').style.display = 'none'; }
function saveGeminiConfig() {
  localStorage.setItem('gemini_api_key', document.getElementById('geminiApiKeyInput').value.trim());
  localStorage.setItem('gemini_model', document.getElementById('geminiModelSelect').value);
  alert("Konfigurasi Gemini API berhasil disimpan!");
  closeGeminiModal();
}
function openFormulaModal() { document.getElementById('formulaModal').style.display = 'flex'; }
function closeFormulaModal() { document.getElementById('formulaModal').style.display = 'none'; }

// ==========================================================================
// 16. DASHBOARD INTERACTIVE FILTERS & REVENUE / GP METRICS ENGINE
// ==========================================================================
function populateDashboardFilterDropdowns() {
  const cabangSel = document.getElementById('dashFilterCabang');
  const salesSel = document.getElementById('dashFilterSales');
  if (!cabangSel || !salesSel) return;

  const currentCabang = dashFilters.cabang;
  const currentSales = dashFilters.sales;

  // Cabang list from rawTurnoverRows
  const cabSet = new Set();
  if (rawTurnoverRows) {
    rawTurnoverRows.forEach(r => {
      const c = String(r['Cabang'] || r['SalesCabang'] || '').trim();
      if (c && c !== 'undefined') cabSet.add(c);
    });
  }
  const sortedCabang = Array.from(cabSet).sort();
  cabangSel.innerHTML = '<option value="all">Semua Cabang Toko</option>' + 
    sortedCabang.map(c => {
      const label = CABANG_NAMES[c] || c;
      return `<option value="${c}" ${c === currentCabang ? 'selected' : ''}>${label}</option>`;
    }).join('');

  // Sales list from active PICs
  const masterList = getMasterSales().filter(x => x.active);
  salesSel.innerHTML = '<option value="all">Semua PIC Sales Sosmed</option>' + 
    masterList.map(s => `<option value="${s.code}" ${s.code === currentSales ? 'selected' : ''}>${s.name} (${s.code})</option>`).join('');
}

function setDashCalculationMode(mode) {
  dashFilters.mode = mode;
  setCalculationMode(mode);
  const btnDPP = document.getElementById('dashBtnDPP');
  const btnGross = document.getElementById('dashBtnGross');
  if (btnDPP) btnDPP.classList.toggle('active', mode === 'DPP');
  if (btnGross) btnGross.classList.toggle('active', mode === 'GROSS');
  applyDashboardFilters();
}

function resetDashboardFilters() {
  const currentMonth = String(new Date().getMonth() + 1);
  dashFilters = { month: currentMonth, cabang: 'all', sales: 'all', mode: currentCalculationMode };
  const mSel = document.getElementById('dashFilterMonth');
  const cSel = document.getElementById('dashFilterCabang');
  const sSel = document.getElementById('dashFilterSales');
  if (mSel) {
    mSel.value = currentMonth;
    if (!mSel.value) mSel.value = 'all';
  }
  if (cSel) cSel.value = 'all';
  if (sSel) sSel.value = 'all';
  applyDashboardFilters();
}

function applyDashboardFilters() {
  const mSel = document.getElementById('dashFilterMonth');
  const cSel = document.getElementById('dashFilterCabang');
  const sSel = document.getElementById('dashFilterSales');

  if (mSel) dashFilters.month = mSel.value;
  if (cSel) dashFilters.cabang = cSel.value;
  if (sSel) dashFilters.sales = sSel.value;

  const targetMonth = dashFilters.month === 'all' ? null : parseInt(dashFilters.month, 10);
  const targetCabang = dashFilters.cabang;
  const targetSales = dashFilters.sales;
  const isDPP = (currentCalculationMode === 'DPP');

  let totalRev = 0;
  let totalGP = 0;
  const dealDocs = new Set();

  if (rawTurnoverRows && rawTurnoverRows.length > 0) {
    rawTurnoverRows.forEach(r => {
      // 1. Cabang filter
      if (targetCabang !== 'all') {
        const c = String(r['Cabang'] || r['SalesCabang'] || '').trim();
        if (c !== targetCabang) return;
      }

      // 2. Sales filter
      const emp = String(r['Sales Employee Name'] || '');
      const rem = String(r['Remarks'] || '');
      const cab = String(r['SalesCabang'] || '');
      if (targetSales === 'all') {
        if (!isSosmedSalesCode(emp, rem, cab)) return;
      } else {
        if (!emp.includes(targetSales) && !rem.includes(targetSales) && !cab.includes(targetSales)) return;
      }

      const dpp = Number(r['DPP']) || 0;
      const total = Number(r['Total']) || 0;
      const fixVal = (r['FIX'] !== undefined && r['FIX'] !== '') ? Number(r['FIX']) : NaN;
      const rawGP = Number(r['GrossProfit']) || 0;
      const gp = !isNaN(fixVal) ? fixVal : rawGP;
      const amt = isDPP ? dpp : total;

      totalRev += amt;
      totalGP += gp;
      const doc = r['Document Number'];
      if (doc) dealDocs.add(doc);
    });
  }

  // Update Summary Strip UI
  const targetTeam = getTeamTargetForMonth(targetMonth || activeMonth, activeYear);
  const gap = totalRev - targetTeam;

  const elRev = document.getElementById('dashSummaryRevenue');
  const elGP = document.getElementById('dashSummaryGP');
  const elDeals = document.getElementById('dashSummaryDeals');
  const elTarget = document.getElementById('dashSummaryTarget');
  const elGap = document.getElementById('dashSummaryGap');

  if (elRev) elRev.innerText = formatRupiah(totalRev);
  if (elGP) elGP.innerText = formatRupiah(totalGP);
  if (elDeals) elDeals.innerText = `${dealDocs.size.toLocaleString('id-ID')} Trx`;
  if (elTarget) elTarget.innerText = formatRupiah(targetTeam);
  if (elGap) {
    elGap.innerText = formatRupiah(gap);
    elGap.style.color = gap >= 0 ? '#10b981' : '#f43f5e';
  }
}

// (Yipyep Floating AI Chat Widget telah dihapus sesuai permintaan)

// Live Real-Time Date and Clock Engine for Report Header
function initLiveClock() {
  function update() {
    const now = new Date();
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const dayName = days[now.getDay()];
    const dateNum = String(now.getDate()).padStart(2, '0');
    const monthName = months[now.getMonth()];
    const year = now.getFullYear();

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    const dateEl = document.getElementById('liveClockDate');
    const timeEl = document.getElementById('liveClockTime');
    if (dateEl) dateEl.innerText = `${dayName}, ${dateNum} ${monthName} ${year}`;
    if (timeEl) timeEl.innerText = `${hours}:${minutes}:${seconds} WITA`;
  }
  update();
  setInterval(update, 1000);
}

// ==========================================================================
// MODUL KLAIM AR & PENGAWASAN DENDA CABANG (SIKLUS 3 HARI + H+1)
// ==========================================================================
let rawARClaimsList = [];
let filteredARClaimsList = [];
let currentClaimsSubTab = 'COLLECTING'; // 'COLLECTING', 'SHARED', 'PENALTY', 'RESOLVED', 'INVALID'
let claimsCurrentPage = 1;
const CLAIMS_PAGE_SIZE = 50;
let currentEvidenceTargetClaimId = null;

async function loadARClaimsData() {
  try {
    const res = await fetch('/api/ar-claims');
    if (res.ok) {
      const data = await res.json();
      if (Array.isArray(data)) {
        rawARClaimsList = data;
      }
    }
  } catch (e) {
    console.warn('Gagal load /api/ar-claims, fallback ke localStorage:', e);
  }
  if (!rawARClaimsList || rawARClaimsList.length === 0) {
    const local = localStorage.getItem('gadgetmart_ar_claims_data');
    if (local) {
      try { rawARClaimsList = JSON.parse(local); } catch(err){}
    }
  }

  // Normalize properties
  rawARClaimsList.forEach(c => {
    c.validationStatus = c.validationStatus || 'VALID';
    c.invalidReason = c.invalidReason || '';
    if (!Array.isArray(c.evidenceImages)) {
      c.evidenceImages = c.evidenceImage ? [c.evidenceImage] : [];
    }
    c.evidenceImage = c.evidenceImages[0] || null;
    c.periode = c.periode || 'Oktober-26';
    c.keepText = c.keepText || '';
  });

  checkARClaimsAutoResolution(false);
  renderARClaimsModule();
}

async function saveARClaimsData() {
  localStorage.setItem('gadgetmart_ar_claims_data', JSON.stringify(rawARClaimsList));
  try {
    await fetch('/api/ar-claims', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(rawARClaimsList)
    });
  } catch (e) {
    console.warn('Gagal simpan ke backend /api/ar-claims:', e);
  }
}

function addSingleARToClaims(auditItem, customItems = null) {
  if (!auditItem) return;
  const docNum = String(auditItem.doc || auditItem.reportARDoc || auditItem.vlookupARDoc || '').trim();
  if (!docNum || docNum === '-') {
    alert('Dokumen AR tidak valid untuk diklaim.');
    return;
  }

  const itemsToClaim = (customItems && customItems.length > 0) ? customItems : (auditItem.items || []);
  const missingOnly = itemsToClaim.filter(it => !isItemExemptFromSosmed(it) && !isItemInputSosmed(it));
  const finalItems = missingOnly.length > 0 ? missingOnly : itemsToClaim;

  const periode = (auditItem.keep && auditItem.keep['PERIODE']) 
    ? String(auditItem.keep['PERIODE']).trim() 
    : 'Oktober-26';
  const keepFormatted = (typeof formatKeepTextForClipboard === 'function') 
    ? formatKeepTextForClipboard(auditItem.keep) 
    : '';

  let addedCount = 0;
  finalItems.forEach((it, idx) => {
    const desc = it['Dscription'] || it['Item/Service Description'] || '-';
    const isHP = isHandphoneProduct(it);
    const harga = Number(it['Harga Jual']) || 0;
    const offlineSales = it['SlpName'] || it['Sales Employee Name'] || it['User Input'] || it['SC/HL Code'] || '-';
    const targetSosmed = (auditItem.keep && auditItem.keep['Kode Sosmed']) ? auditItem.keep['Kode Sosmed'] : 'BJM.SOS.xxx';
    const picSos = (auditItem.keep && auditItem.keep['Sender']) ? auditItem.keep['Sender'] : '-';
    const cust = (auditItem.keep && auditItem.keep['Nama']) ? auditItem.keep['Nama'] : (it['Name'] || '-');
    const wa = (auditItem.keep && auditItem.keep['Nomor WA']) ? auditItem.keep['Nomor WA'] : (it['No HP Customer'] || '-');
    const cabang = it['Cabang'] || auditItem.cabang || '-';

    const exists = rawARClaimsList.some(c => c.docNumber === docNum && c.productName === desc && c.status !== 'RESOLVED_BY_STORE');
    if (!exists) {
      rawARClaimsList.unshift({
        id: `CLM-${docNum}-${idx + 1}-${Date.now()}-${Math.floor(Math.random() * 1000)}`,
        docNumber: docNum,
        postingDate: it['Posting Date'] || auditItem.postingDate || '',
        periode: periode,
        cabang: cabang,
        productName: desc,
        hargaJual: harga,
        currentSalesCode: offlineSales,
        targetSosmedCode: targetSosmed,
        picSosmed: picSos,
        customerName: cust,
        customerPhone: wa,
        keepText: keepFormatted,
        evidenceImage: null,
        evidenceImages: [],
        validationStatus: 'VALID',
        invalidReason: '',
        isHandphone: isHP,
        collectDate: new Date().toISOString(),
        collectTimestamp: Date.now(),
        status: 'COLLECTING',
        shareTimestamp: null,
        deadlineH1Timestamp: null,
        penaltyAmount: isHP ? 50000 : 0,
        penaltyStatus: isHP ? 'POTENSI' : 'PASS',
        manualResolvedBy: '',
        manualResolvedAt: null
      });
      addedCount++;
    }
  });

  if (addedCount > 0) {
    saveARClaimsData();
    showSaveToast(`${addedCount} item dari AR ${docNum} berhasil dimasukkan ke Keranjang Klaim (Batch 3 Hari).`);
    renderARClaimsModule();
  } else {
    showSaveToast(`Item AR ${docNum} sudah ada di daftar klaim sebelumnya.`);
  }
}

function claimAllFilteredMissingAR() {
  if (!filteredAuditARList || filteredAuditARList.length === 0) {
    alert('Tidak ada data AR yang terfilter saat ini.');
    return;
  }

  const missingARs = filteredAuditARList.filter(r => r.status === 'ALL_MISSING' || r.status === 'PARTIAL_MISSING');
  if (missingARs.length === 0) {
    alert('Semua AR yang terfilter saat ini sudah berstatus 100% sosmed (Hijau).');
    return;
  }

  if (!confirm(`Apakah Anda yakin ingin memasukkan seluruh ${missingARs.length} dokumen AR yang belum sosmed ke Modul Klaim AR (Pengumpulan 3 Hari)?`)) {
    return;
  }

  let totalAdded = 0;
  missingARs.forEach(auditItem => {
    const docNum = String(auditItem.doc || auditItem.reportARDoc || auditItem.vlookupARDoc || '').trim();
    const itemsToClaim = (auditItem.items || []).filter(it => !isItemExemptFromSosmed(it) && !isItemInputSosmed(it));
    const periode = (auditItem.keep && auditItem.keep['PERIODE']) 
      ? String(auditItem.keep['PERIODE']).trim() 
      : 'Oktober-26';
    const keepFormatted = (typeof formatKeepTextForClipboard === 'function') 
      ? formatKeepTextForClipboard(auditItem.keep) 
      : '';

    itemsToClaim.forEach((it, idx) => {
      const desc = it['Dscription'] || it['Item/Service Description'] || '-';
      const exists = rawARClaimsList.some(c => c.docNumber === docNum && c.productName === desc && c.status !== 'RESOLVED_BY_STORE');
      if (!exists) {
        const isHP = isHandphoneProduct(it);
        rawARClaimsList.unshift({
          id: `CLM-${docNum}-${idx + 1}-${Date.now()}-${Math.floor(Math.random() * 1000)}`,
          docNumber: docNum,
          postingDate: it['Posting Date'] || auditItem.postingDate || '',
          periode: periode,
          cabang: it['Cabang'] || auditItem.cabang || '-',
          productName: desc,
          hargaJual: Number(it['Harga Jual']) || 0,
          currentSalesCode: it['SlpName'] || it['Sales Employee Name'] || it['User Input'] || it['SC/HL Code'] || '-',
          targetSosmedCode: (auditItem.keep && auditItem.keep['Kode Sosmed']) ? auditItem.keep['Kode Sosmed'] : 'BJM.SOS.xxx',
          picSosmed: (auditItem.keep && auditItem.keep['Sender']) ? auditItem.keep['Sender'] : '-',
          customerName: (auditItem.keep && auditItem.keep['Nama']) ? auditItem.keep['Nama'] : (it['Name'] || '-'),
          customerPhone: (auditItem.keep && auditItem.keep['Nomor WA']) ? auditItem.keep['Nomor WA'] : (it['No HP Customer'] || '-'),
          keepText: keepFormatted,
          evidenceImage: null,
          evidenceImages: [],
          validationStatus: 'VALID',
          invalidReason: '',
          isHandphone: isHP,
          collectDate: new Date().toISOString(),
          collectTimestamp: Date.now(),
          status: 'COLLECTING',
          shareTimestamp: null,
          deadlineH1Timestamp: null,
          penaltyAmount: isHP ? 50000 : 0,
          penaltyStatus: isHP ? 'POTENSI' : 'PASS',
          manualResolvedBy: '',
          manualResolvedAt: null
        });
        totalAdded++;
      }
    });
  });

  saveARClaimsData();
  showSaveToast(`Berhasil menambahkan ${totalAdded} item belum sosmed ke Modul Klaim AR.`);
  renderARClaimsModule();
}

function claimModalSelectedItems(btnElement = null) {
  if (currentModalCheckedItems.size === 0) {
    alert('Silakan centang minimal 1 item produk yang ingin dimasukkan ke Klaim Cabang.');
    return;
  }

  const items = Array.from(currentModalCheckedItems).map(idx => currentModalRows[idx]).filter(Boolean);
  const auditItem = {
    doc: currentModalDoc,
    items: currentModalRows,
    keep: currentModalKeep
  };
  addSingleARToClaims(auditItem, items);
  closeARDetailModal();
}

// Evidence Image Handlers (Dukungan Beberapa Evidence per Baris & Carousel Modal)
let currentEvidenceModalIndex = 0;

function triggerEvidenceUpload(claimId) {
  currentEvidenceTargetClaimId = claimId;
  const input = document.getElementById('evidenceFileInput');
  if (input) {
    input.value = '';
    input.click();
  }
}

function triggerEvidenceUploadFromModal() {
  const claimId = currentEvidenceTargetClaimId;
  if (!claimId) return;
  triggerEvidenceUpload(claimId);
}

function handleEvidenceFileSelected(fileInput) {
  if (!fileInput || !fileInput.files || fileInput.files.length === 0) return;
  const rawFiles = Array.from(fileInput.files);
  const imageFiles = rawFiles.filter(f => f.type.startsWith('image/'));

  if (imageFiles.length === 0) {
    alert('File yang dipilih harus berupa file gambar (JPG, PNG, WEBP).');
    return;
  }

  const claimId = currentEvidenceTargetClaimId;
  const item = rawARClaimsList.find(c => c.id === claimId);
  if (!item) return;

  if (!Array.isArray(item.evidenceImages)) {
    item.evidenceImages = item.evidenceImage ? [item.evidenceImage] : [];
  }

  const compressSingleFile = (file) => {
    return new Promise((resolve) => {
      const reader = new FileReader();
      reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
          const canvas = document.createElement('canvas');
          let width = img.width;
          let height = img.height;
          const maxDim = 1200;
          if (width > maxDim || height > maxDim) {
            if (width > height) {
              height = Math.round((height * maxDim) / width);
              width = maxDim;
            } else {
              width = Math.round((width * maxDim) / height);
              height = maxDim;
            }
          }
          canvas.width = width;
          canvas.height = height;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, width, height);

          const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.82);
          resolve(compressedDataUrl);
        };
        img.onerror = () => resolve(null);
        img.src = e.target.result;
      };
      reader.onerror = () => resolve(null);
      reader.readAsDataURL(file);
    });
  };

  Promise.all(imageFiles.map(compressSingleFile)).then(results => {
    const validImages = results.filter(Boolean);
    if (validImages.length === 0) return;

    item.evidenceImages.push(...validImages);
    item.evidenceImage = item.evidenceImages[0] || null;

    saveARClaimsData();
    showSaveToast(`📸 Berhasil menambahkan ${validImages.length} bukti gambar untuk AR ${item.docNumber} (Total: ${item.evidenceImages.length} foto).`);
    renderARClaimsTable();

    // Jika modal zoom sedang terbuka, refresh langsung ke foto baru
    const modal = document.getElementById('evidenceImageModal');
    if (modal && modal.style.display !== 'none' && currentEvidenceTargetClaimId === claimId) {
      openEvidenceImageModal(claimId, item.evidenceImages.length - 1);
    }
  });
}

function removeEvidenceImage(claimId, imageIdx = null) {
  const item = rawARClaimsList.find(c => c.id === claimId);
  if (!item) return;
  if (!Array.isArray(item.evidenceImages)) {
    item.evidenceImages = item.evidenceImage ? [item.evidenceImage] : [];
  }
  if (item.evidenceImages.length === 0) return;

  if (imageIdx !== null && imageIdx >= 0 && imageIdx < item.evidenceImages.length) {
    if (!confirm(`Hapus foto bukti ke-${imageIdx + 1} untuk AR ${item.docNumber}?`)) return;
    item.evidenceImages.splice(imageIdx, 1);
  } else {
    if (!confirm(`Hapus seluruh (${item.evidenceImages.length}) bukti gambar untuk AR ${item.docNumber}?`)) return;
    item.evidenceImages = [];
  }

  item.evidenceImage = item.evidenceImages[0] || null;
  saveARClaimsData();
  showSaveToast('Bukti gambar berhasil dihapus.');
  renderARClaimsTable();

  const modal = document.getElementById('evidenceImageModal');
  if (modal && modal.style.display !== 'none' && currentEvidenceTargetClaimId === claimId) {
    if (item.evidenceImages.length === 0) {
      closeEvidenceImageModal();
    } else {
      const nextIdx = Math.min(imageIdx !== null ? imageIdx : 0, item.evidenceImages.length - 1);
      openEvidenceImageModal(claimId, nextIdx);
    }
  }
}

function openEvidenceImageModal(claimId, activeIndex = 0) {
  const item = rawARClaimsList.find(c => c.id === claimId);
  if (!item) return;
  if (!Array.isArray(item.evidenceImages)) {
    item.evidenceImages = item.evidenceImage ? [item.evidenceImage] : [];
  }
  if (item.evidenceImages.length === 0) return;

  currentEvidenceTargetClaimId = claimId;
  currentEvidenceModalIndex = Math.max(0, Math.min(activeIndex, item.evidenceImages.length - 1));

  renderEvidenceModalContent();

  const modal = document.getElementById('evidenceImageModal');
  if (modal) modal.style.display = 'flex';
}

function renderEvidenceModalContent() {
  const item = rawARClaimsList.find(c => c.id === currentEvidenceTargetClaimId);
  if (!item || !item.evidenceImages || item.evidenceImages.length === 0) {
    closeEvidenceImageModal();
    return;
  }

  const imgs = item.evidenceImages;
  const currentIdx = Math.max(0, Math.min(currentEvidenceModalIndex, imgs.length - 1));
  const currentImgUrl = imgs[currentIdx];

  const titleEl = document.getElementById('evidenceModalTitle');
  const subEl = document.getElementById('evidenceModalSub');
  const fullImg = document.getElementById('evidenceFullImg');
  const btnDownload = document.getElementById('btnDownloadEvidence');
  const counterEl = document.getElementById('evidenceModalCounter');
  const galleryStrip = document.getElementById('evidenceModalGalleryStrip');

  if (titleEl) titleEl.innerText = `📷 Bukti Format Keep: AR ${item.docNumber} (${currentIdx + 1} dari ${imgs.length})`;
  if (subEl) subEl.innerText = `Customer: ${item.customerName || '-'} (${item.customerPhone || '-'}) | Cabang: ${item.cabang} | Barang: ${item.productName}`;
  if (fullImg) fullImg.src = currentImgUrl;
  if (btnDownload) {
    btnDownload.href = currentImgUrl;
    btnDownload.download = `Evidence_Keep_AR_${item.docNumber}_Foto_${currentIdx + 1}.jpg`;
  }
  if (counterEl) {
    counterEl.innerText = `Foto ${currentIdx + 1} dari ${imgs.length}`;
  }

  // Thumbnails carousel strip
  if (galleryStrip) {
    if (imgs.length > 1) {
      galleryStrip.style.display = 'flex';
      galleryStrip.innerHTML = imgs.map((img, i) => `
        <div class="evidence-modal-thumb ${i === currentIdx ? 'active' : ''}" onclick="switchEvidenceModalIndex(${i})" title="Buka foto ke-${i + 1}">
          <img src="${img}" alt="Thumb ${i + 1}">
        </div>
      `).join('');
    } else {
      galleryStrip.style.display = 'none';
      galleryStrip.innerHTML = '';
    }
  }
}

function switchEvidenceModalIndex(newIdx) {
  const item = rawARClaimsList.find(c => c.id === currentEvidenceTargetClaimId);
  if (!item || !item.evidenceImages || item.evidenceImages.length === 0) return;
  if (newIdx < 0) newIdx = item.evidenceImages.length - 1;
  if (newIdx >= item.evidenceImages.length) newIdx = 0;
  currentEvidenceModalIndex = newIdx;
  renderEvidenceModalContent();
}

function nextEvidenceModalImage() {
  switchEvidenceModalIndex(currentEvidenceModalIndex + 1);
}

function prevEvidenceModalImage() {
  switchEvidenceModalIndex(currentEvidenceModalIndex - 1);
}

function removeCurrentModalEvidence() {
  removeEvidenceImage(currentEvidenceTargetClaimId, currentEvidenceModalIndex);
}

function closeEvidenceImageModal(e = null) {
  const modal = document.getElementById('evidenceImageModal');
  if (modal) modal.style.display = 'none';
}

// Navigasi keyboard (Panah Kiri & Kanan) untuk galeri bukti foto
document.addEventListener('keydown', function(e) {
  const modal = document.getElementById('evidenceImageModal');
  if (modal && modal.style.display === 'flex') {
    if (e.key === 'ArrowRight') {
      nextEvidenceModalImage();
    } else if (e.key === 'ArrowLeft') {
      prevEvidenceModalImage();
    } else if (e.key === 'Escape') {
      closeEvidenceImageModal();
    }
  }
});

// Validation Handlers
function handleClaimValidityChange(claimId, newStatus) {
  const item = rawARClaimsList.find(c => c.id === claimId);
  if (!item) return;

  if (newStatus === 'TIDAK VALID') {
    const reason = prompt(`Item AR ${item.docNumber} ditandai TIDAK VALID.\nMasukkan alasannya:`, item.invalidReason || 'Salah tag / Bukan nomor keep');
    if (reason === null) {
      renderARClaimsTable();
      return;
    }
    item.validationStatus = 'TIDAK VALID';
    item.invalidReason = reason.trim() || 'Tidak Valid';
    showSaveToast(`🚫 AR ${item.docNumber} ditandai TIDAK VALID dan dipindahkan ke sub-tab "5. Tidak Valid".`);
  } else {
    item.validationStatus = 'VALID';
    item.invalidReason = '';
    showSaveToast(`✅ AR ${item.docNumber} dikembalikan ke status VALID.`);
  }

  saveARClaimsData();
  renderARClaimsModule();
}

function handleClaimInvalidReasonChange(claimId, newReason) {
  const item = rawARClaimsList.find(c => c.id === claimId);
  if (!item) return;
  item.invalidReason = newReason.trim();
  saveARClaimsData();
}

// ==========================================
// EXCEL EXPORT ENGINE (EXCELJS + FALLBACK)
// ==========================================
function parseSortDate(d) {
  if (!d) return 0;
  if (typeof d === 'number') return d;
  if (d instanceof Date) return d.getTime();
  const s = String(d).trim();
  if (/^\d{4}-\d{2}-\d{2}/.test(s)) {
    return new Date(s).getTime() || 0;
  }
  const parts = s.split(/[\/\-]/);
  if (parts.length >= 3) {
    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1;
    let year = parseInt(parts[2].split(' ')[0], 10);
    if (year < 100) year += 2000;
    return new Date(year, month, day).getTime() || 0;
  }
  return 0;
}

function sortARClaimsRows(rows, groupByBranch = true) {
  return [...rows].sort((a, b) => {
    if (groupByBranch) {
      const cabA = String(a.cabang || '').toUpperCase();
      const cabB = String(b.cabang || '').toUpperCase();
      if (cabA !== cabB) return cabA.localeCompare(cabB);
    }
    const dateA = parseSortDate(a.postingDate);
    const dateB = parseSortDate(b.postingDate);
    if (dateA !== dateB) return dateA - dateB;

    const docA = String(a.docNumber || '');
    const docB = String(b.docNumber || '');
    return docA.localeCompare(docB, undefined, { numeric: true });
  });
}

async function buildClaimsWorksheet(wb, sheetName, rows) {
  let safeName = String(sheetName || 'Klaim').replace(/[:\\/?*\[\]]/g, '_').slice(0, 31).trim();
  if (!safeName) safeName = 'Klaim';
  let uniqueName = safeName;
  let nameCounter = 1;
  while (wb.getWorksheet(uniqueName)) {
    uniqueName = `${safeName.slice(0, 27)}_${nameCounter++}`;
  }
  const ws = wb.addWorksheet(uniqueName);

  // 1. Urutkan data secara rapi dan kronologis
  const isAllSheet = (sheetName === 'SEMUA CABANG');
  const sortedRows = sortARClaimsRows(rows, isAllSheet);

  // 2. Hitung jumlah maksimum foto bukti pada baris-baris ini
  let maxEv = 1;
  sortedRows.forEach(r => {
    const imgs = (Array.isArray(r.evidenceImages) && r.evidenceImages.length > 0)
      ? r.evidenceImages
      : (r.evidenceImage ? [r.evidenceImage] : []);
    if (imgs.length > maxEv) maxEv = imgs.length;
  });

  // 3. Bangun kolom tabel secara berurutan
  const cols = [
    { header: 'NO', key: 'no', width: 6 },
    { header: 'TANGGAL', key: 'tanggal', width: 14 },
    { header: 'PERIODE', key: 'periode', width: 14 },
    { header: 'NO AR', key: 'noAR', width: 16 },
    { header: 'NO.HP USER', key: 'noHP', width: 18 },
    { header: 'KODE SOSMED', key: 'kodeSosmed', width: 16 },
    { header: 'PIC SOSMED', key: 'picSosmed', width: 16 },
    { header: 'CABANG', key: 'cabang', width: 14 },
    { header: 'POTENSI DENDA', key: 'potensiDenda', width: 16 },
    { header: 'BARANG', key: 'barang', width: 34 },
    { header: 'KEEP', key: 'keep', width: 36 }
  ];

  // Kolom Evidence terpisah per foto bukti dengan lebar besar (width: 48 ≈ 370px) agar muat foto besar tanpa bertumpuk
  for (let e = 0; e < maxEv; e++) {
    const headerTitle = maxEv === 1 
      ? 'EVIDENCE FORMAT KEEP' 
      : `EVIDENCE ${e + 1} (FORMAT KEEP)`;
    cols.push({
      header: headerTitle,
      key: `evidence_${e}`,
      width: 48
    });
  }

  cols.push(
    { header: 'AR SALES EMPLOYEE', key: 'salesEmployee', width: 22 },
    { header: 'STATUS VALIDASI', key: 'statusValidasi', width: 16 },
    { header: 'ALASAN TIDAK VALID', key: 'alasanTidakValid', width: 24 }
  );

  ws.columns = cols;

  const headerRow = ws.getRow(1);
  headerRow.height = 30;
  headerRow.eachCell((cell) => {
    cell.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'FF991B1B' } // Crimson / Red
    };
    cell.font = {
      name: 'Arial',
      size: 10,
      bold: true,
      color: { argb: 'FFFFFFFF' }
    };
    cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
    cell.border = {
      top: { style: 'thin', color: { argb: 'FF7F1D1D' } },
      left: { style: 'thin', color: { argb: 'FF7F1D1D' } },
      bottom: { style: 'medium', color: { argb: 'FF450A0A' } },
      right: { style: 'thin', color: { argb: 'FF7F1D1D' } }
    };
  });

  const evidenceStartCol = 11; // 0-indexed column 11 = Column L

  for (let idx = 0; idx < sortedRows.length; idx++) {
    const r = sortedRows[idx];
    const rowNumber = idx + 2;
    const imgs = (Array.isArray(r.evidenceImages) && r.evidenceImages.length > 0)
      ? r.evidenceImages
      : (r.evidenceImage ? [r.evidenceImage] : []);

    const rowObj = {
      no: idx + 1,
      tanggal: formatCrosellingDate(r.postingDate),
      periode: r.periode || 'Oktober-26',
      noAR: r.docNumber,
      noHP: r.customerPhone || '-',
      kodeSosmed: r.targetSosmedCode,
      picSosmed: r.picSosmed || '-',
      cabang: r.cabang,
      potensiDenda: r.isHandphone ? 'DENDA Rp 50.000' : 'PASS Rp 0',
      barang: r.productName,
      keep: r.keepText || '-',
      salesEmployee: r.currentSalesCode || '-',
      statusValidasi: r.validationStatus || 'VALID',
      alasanTidakValid: r.invalidReason || '-'
    };

    for (let e = 0; e < maxEv; e++) {
      rowObj[`evidence_${e}`] = imgs[e] ? '' : '-';
    }

    const row = ws.addRow(rowObj);

    // Jika ada bukti foto: buat baris tinggi besar (200pt ≈ 267px) agar tulisan WhatsApp terbaca jelas!
    const hasImgs = imgs.length > 0;
    row.height = hasImgs ? 200 : 36;

    row.alignment = { vertical: 'middle', horizontal: 'left', wrapText: true };
    row.getCell('no').alignment = { vertical: 'middle', horizontal: 'center' };
    row.getCell('tanggal').alignment = { vertical: 'middle', horizontal: 'center' };
    row.getCell('periode').alignment = { vertical: 'middle', horizontal: 'center' };
    row.getCell('noAR').alignment = { vertical: 'middle', horizontal: 'center' };
    row.getCell('noHP').alignment = { vertical: 'middle', horizontal: 'center' };
    row.getCell('kodeSosmed').alignment = { vertical: 'middle', horizontal: 'center' };
    row.getCell('picSosmed').alignment = { vertical: 'middle', horizontal: 'center' };
    row.getCell('cabang').alignment = { vertical: 'middle', horizontal: 'center' };
    row.getCell('potensiDenda').alignment = { vertical: 'middle', horizontal: 'center' };
    row.getCell('statusValidasi').alignment = { vertical: 'middle', horizontal: 'center' };

    for (let e = 0; e < maxEv; e++) {
      row.getCell(`evidence_${e}`).alignment = { vertical: 'middle', horizontal: 'center' };
    }

    // Border grid rapi pada setiap cell
    row.eachCell((cell) => {
      cell.border = {
        top: { style: 'thin', color: { argb: 'FFE2E8F0' } },
        left: { style: 'thin', color: { argb: 'FFE2E8F0' } },
        bottom: { style: 'thin', color: { argb: 'FFE2E8F0' } },
        right: { style: 'thin', color: { argb: 'FFE2E8F0' } }
      };
    });

    // Embed setiap foto bukti secara berurutan pada kolomnya masing-masing dengan ukuran BESAR presisi di dalam sel (Two-Cell Anchor)
    if (hasImgs) {
      imgs.forEach((imgStr, imgIdx) => {
        if (!imgStr || typeof imgStr !== 'string') return;
        try {
          const isPng = imgStr.toLowerCase().includes('image/png') || imgStr.toLowerCase().includes('.png');
          const isGif = imgStr.toLowerCase().includes('image/gif');
          const extension = isPng ? 'png' : (isGif ? 'gif' : 'jpeg');
          const rawBase64 = imgStr.includes(',') ? imgStr.split(',')[1] : imgStr;
          const cleanBase64 = rawBase64.trim().replace(/\s/g, '');

          const imgId = wb.addImage({
            base64: cleanBase64,
            extension: extension
          });

          // Letakkan tepat di dalam kolom bukti masing-masing (Col 11 + imgIdx) dengan twoCellAnchor agar tidak overlap & 100% valid DrawingML
          ws.addImage(imgId, {
            tl: { col: evidenceStartCol + imgIdx + 0.02, row: rowNumber - 1 + 0.02 },
            br: { col: evidenceStartCol + imgIdx + 0.98, row: rowNumber - 0.02 },
            editAs: 'oneCell'
          });
        } catch (imgErr) {
          console.warn('Gagal embed image ExcelJS:', imgErr);
          row.getCell(`evidence_${imgIdx}`).value = `(Bukti Foto ${imgIdx + 1})`;
        }
      });
    }
  }
}

// 1. UNDUH SELURUH KLAIM JADI 1 FILE EXCEL GABUNGAN
async function downloadARClaimsSingleExcel(autoShare = false) {
  if (!rawARClaimsList || rawARClaimsList.length === 0) {
    alert('Belum ada data klaim AR untuk diunduh.');
    return;
  }

  const collectingItems = rawARClaimsList.filter(c => c.status === 'COLLECTING' && c.validationStatus !== 'TIDAK VALID');
  let itemsToExport = [];
  let doAdvanceToShared = false;

  if (autoShare) {
    itemsToExport = collectingItems;
    doAdvanceToShared = true;
  } else if (currentClaimsSubTab === 'COLLECTING' && collectingItems.length > 0) {
    const userChoice = confirm(
      `📊 UNDUH SEMUANYA JADI 1 FILE EXCEL (.xlsx)\n\n` +
      `Ditemukan ${collectingItems.length} item AR dalam antrean "1. Pengumpulan (Batch 3 Hari)".\n\n` +
      `• Klik [OK] untuk mengunduh 1 file Excel Gabungan (diurutkan rapi & gambar besar) dan SEKALIGUS memindahkan status ke "Cek AR Setelah di Share (Tenggat H+1)".\n` +
      `• Klik [Batal] jika hanya ingin mengunduh data tampilan saat ini tanpa memindahkan status klaim.`
    );
    if (userChoice) {
      itemsToExport = collectingItems;
      doAdvanceToShared = true;
    } else {
      itemsToExport = filteredARClaimsList.length > 0 ? filteredARClaimsList : rawARClaimsList;
    }
  } else {
    itemsToExport = filteredARClaimsList.length > 0 ? filteredARClaimsList : rawARClaimsList;
  }

  if (itemsToExport.length === 0) {
    alert('Tidak ada data klaim yang memenuhi kriteria untuk diunduh.');
    return;
  }

  const nowStr = new Date().toISOString().split('T')[0];

  // Gunakan ExcelJS untuk menghasilkan 1 file Excel lengkap dengan sheet gabungan & sheet per cabang serta gambar ter-embed besar
  if (typeof ExcelJS !== 'undefined') {
    try {
      const wb = new ExcelJS.Workbook();
      wb.creator = 'GadgetMart Sosmed System';
      wb.title = 'Klaim AR Semua Cabang';

      // 1. Sheet Master Gabungan Seluruh Cabang (Urut Cabang A-Z -> Tanggal -> No AR)
      await buildClaimsWorksheet(wb, 'SEMUA CABANG', itemsToExport);

      // 2. Sheet per Cabang terpisah di file Excel yang sama (Urut Alfabetis Tab Cabang)
      const byBranch = {};
      itemsToExport.forEach(c => {
        const cab = c.cabang || 'LAINNYA';
        if (!byBranch[cab]) byBranch[cab] = [];
        byBranch[cab].push(c);
      });

      const sortedCabangList = Object.keys(byBranch).sort();
      for (const cab of sortedCabangList) {
        await buildClaimsWorksheet(wb, cab, byBranch[cab]);
      }

      const buffer = await wb.xlsx.writeBuffer();
      const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = `Klaim_AR_Semua_Cabang_Gabungan_${nowStr}.xlsx`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);

      if (doAdvanceToShared) {
        const shareTime = Date.now();
        const deadlineH1 = shareTime + (24 * 60 * 60 * 1000);
        itemsToExport.forEach(c => {
          c.status = 'SHARED';
          c.shareTimestamp = shareTime;
          c.deadlineH1Timestamp = deadlineH1;
        });
        await saveARClaimsData();
        showSaveToast(`🚀 ${itemsToExport.length} item AR berhasil diunduh jadi 1 Excel & dipindahkan ke sub-tab "Cek AR Setelah di Share" (H+1)!`);
        setClaimsSubTab('SHARED');
      } else {
        showSaveToast(`✅ Berhasil mengunduh 1 File Excel Gabungan (${itemsToExport.length} item AR)!`);
      }
      return;
    } catch (excelErr) {
      console.warn('ExcelJS error, fallback ke SheetJS:', excelErr);
    }
  }

  // Fallback SheetJS jika ExcelJS bermasalah
  const wb = XLSX.utils.book_new();
  const buildSheetJSData = (rows) => {
    const sorted = sortARClaimsRows(rows, true);
    let maxEv = 1;
    sorted.forEach(r => {
      const imgs = (Array.isArray(r.evidenceImages) && r.evidenceImages.length > 0)
        ? r.evidenceImages
        : (r.evidenceImage ? [r.evidenceImage] : []);
      if (imgs.length > maxEv) maxEv = imgs.length;
    });

    const headers = [
      'NO', 'TANGGAL', 'PERIODE', 'NO AR', 'NO.HP USER', 'KODE SOSMED', 'PIC SOSMED', 'CABANG', 'POTENSI DENDA', 'BARANG', 'KEEP'
    ];
    for (let e = 0; e < maxEv; e++) {
      headers.push(maxEv === 1 ? 'EVIDENCE FORMAT KEEP' : `EVIDENCE ${e + 1}`);
    }
    headers.push('AR SALES EMPLOYEE', 'STATUS VALIDASI', 'ALASAN TIDAK VALID');

    const data = [headers];
    sorted.forEach((r, idx) => {
      const imgs = (Array.isArray(r.evidenceImages) && r.evidenceImages.length > 0)
        ? r.evidenceImages
        : (r.evidenceImage ? [r.evidenceImage] : []);
      const rArr = [
        idx + 1,
        formatCrosellingDate(r.postingDate),
        r.periode || 'Oktober-26',
        r.docNumber,
        r.customerPhone || '-',
        r.targetSosmedCode,
        r.picSosmed || '-',
        r.cabang,
        r.isHandphone ? 'DENDA Rp 50.000' : 'PASS Rp 0',
        r.productName,
        r.keepText || '-'
      ];
      for (let e = 0; e < maxEv; e++) {
        rArr.push(imgs[e] ? `(Bukti Foto ${e + 1})` : '-');
      }
      rArr.push(
        r.currentSalesCode || '-',
        r.validationStatus || 'VALID',
        r.invalidReason || '-'
      );
      data.push(rArr);
    });
    return XLSX.utils.aoa_to_sheet(data);
  };

  XLSX.utils.book_append_sheet(wb, buildSheetJSData(itemsToExport), 'SEMUA CABANG');

  const byBranch = {};
  itemsToExport.forEach(c => {
    const cab = c.cabang || 'LAINNYA';
    if (!byBranch[cab]) byBranch[cab] = [];
    byBranch[cab].push(c);
  });
  const sortedCabangList = Object.keys(byBranch).sort();
  for (const cab of sortedCabangList) {
    XLSX.utils.book_append_sheet(wb, buildSheetJSData(byBranch[cab]), cab.slice(0, 31));
  }

  XLSX.writeFile(wb, `Klaim_AR_Semua_Cabang_Gabungan_${nowStr}.xlsx`);

  if (doAdvanceToShared) {
    const shareTime = Date.now();
    const deadlineH1 = shareTime + (24 * 60 * 60 * 1000);
    itemsToExport.forEach(c => {
      c.status = 'SHARED';
      c.shareTimestamp = shareTime;
      c.deadlineH1Timestamp = deadlineH1;
    });
    await saveARClaimsData();
    showSaveToast(`🚀 ${itemsToExport.length} item AR berhasil diunduh jadi 1 Excel & dipindahkan ke sub-tab "Cek AR Setelah di Share" (H+1)!`);
    setClaimsSubTab('SHARED');
  } else {
    showSaveToast(`✅ Berhasil mengunduh 1 File Excel Gabungan (${itemsToExport.length} item AR)!`);
  }
}
window.downloadARClaimsSingleExcel = downloadARClaimsSingleExcel;

// 2. DISTRIBUSI KLAIM HARI KE-4 (DENGAN PILIHAN 1 EXCEL ATAU PAKET ZIP)
async function shareARClaimsBatchDay4() {
  const collectingItems = rawARClaimsList.filter(c => c.status === 'COLLECTING' && c.validationStatus !== 'TIDAK VALID');
  if (collectingItems.length === 0) {
    alert('Tidak ada item valid dalam antrean "1. Pengumpulan (Batch 3 Hari)".\nSilakan kumpulkan AR belum sosmed dari Tab 5 (Tracking AR) terlebih dahulu.');
    return;
  }

  const branchCount = new Set(collectingItems.map(c => c.cabang)).size;
  const choiceMsg = `📦 DISTRIBUSI KLAIM AR HARI KE-4:\n\n` +
                    `Total: ${collectingItems.length} item AR dari ${branchCount} cabang toko siap didistribusikan.\n\n` +
                    `Pilih format unduhan:\n` +
                    `• Klik [OK] untuk mengunduh SEMUANYA JADI 1 FILE EXCEL GABUNGAN (.xlsx) (diurutkan rapi & gambar besar).\n` +
                    `• Klik [Batal] untuk mengunduh PAKET ZIP FILE TERPISAH PER CABANG (.zip).`;

  const wantSingleExcel = confirm(choiceMsg);

  if (wantSingleExcel) {
    await downloadARClaimsSingleExcel(true);
    return;
  }

  // Jika user memilih Batal, unduh paket zip terpisah per cabang
  const byBranch = {};
  collectingItems.forEach(c => {
    const cab = c.cabang || 'LAINNYA';
    if (!byBranch[cab]) byBranch[cab] = [];
    byBranch[cab].push(c);
  });

  const nowStr = new Date().toISOString().split('T')[0];
  const zip = (typeof JSZip !== 'undefined') ? new JSZip() : null;

  const sortedCabangList = Object.keys(byBranch).sort();
  for (const cab of sortedCabangList) {
    const rows = byBranch[cab];

    if (typeof ExcelJS !== 'undefined') {
      try {
        const wb = new ExcelJS.Workbook();
        wb.creator = 'GadgetMart Sosmed System';
        await buildClaimsWorksheet(wb, cab, rows);
        const buffer = await wb.xlsx.writeBuffer();
        if (zip) {
          zip.file(`Klaim_AR_${cab}_${nowStr}.xlsx`, buffer);
        }
        continue;
      } catch (excelJsErr) {
        console.warn('ExcelJS error, fallback ke SheetJS:', excelJsErr);
      }
    }

    // Fallback SheetJS
    const sortedCabRows = sortARClaimsRows(rows, false);
    let maxEv = 1;
    sortedCabRows.forEach(r => {
      const imgs = (Array.isArray(r.evidenceImages) && r.evidenceImages.length > 0)
        ? r.evidenceImages
        : (r.evidenceImage ? [r.evidenceImage] : []);
      if (imgs.length > maxEv) maxEv = imgs.length;
    });

    const headers = [
      'NO', 'TANGGAL', 'PERIODE', 'NO AR', 'NO.HP USER', 'KODE SOSMED', 'PIC SOSMED', 'CABANG', 'POTENSI DENDA', 'BARANG', 'KEEP'
    ];
    for (let e = 0; e < maxEv; e++) {
      headers.push(maxEv === 1 ? 'EVIDENCE FORMAT KEEP' : `EVIDENCE ${e + 1}`);
    }
    headers.push('AR SALES EMPLOYEE', 'STATUS VALIDASI', 'ALASAN TIDAK VALID');

    const excelData = [headers];
    sortedCabRows.forEach((r, idx) => {
      const imgs = (Array.isArray(r.evidenceImages) && r.evidenceImages.length > 0)
        ? r.evidenceImages
        : (r.evidenceImage ? [r.evidenceImage] : []);
      const rArr = [
        idx + 1,
        formatCrosellingDate(r.postingDate),
        r.periode || 'Oktober-26',
        r.docNumber,
        r.customerPhone || '-',
        r.targetSosmedCode,
        r.picSosmed || '-',
        r.cabang,
        r.isHandphone ? 'DENDA Rp 50.000' : 'PASS Rp 0',
        r.productName,
        r.keepText || '-'
      ];
      for (let e = 0; e < maxEv; e++) {
        rArr.push(imgs[e] ? `(Bukti Foto ${e + 1})` : '-');
      }
      rArr.push(
        r.currentSalesCode || '-',
        r.validationStatus || 'VALID',
        r.invalidReason || '-'
      );
      excelData.push(rArr);
    });
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, cab.slice(0, 31));
    if (zip) {
      const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
      zip.file(`Klaim_AR_${cab}_${nowStr}.xlsx`, wbout);
    }
  }

  if (zip) {
    try {
      const zipBlob = await zip.generateAsync({ type: 'blob' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(zipBlob);
      a.download = `Paket_Klaim_AR_Semua_Cabang_${nowStr}.zip`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    } catch (zipErr) {
      console.error('Gagal generate zip:', zipErr);
    }
  }

  const shareTime = Date.now();
  const deadlineH1 = shareTime + (24 * 60 * 60 * 1000);
  collectingItems.forEach(c => {
    c.status = 'SHARED';
    c.shareTimestamp = shareTime;
    c.deadlineH1Timestamp = deadlineH1;
  });

  await saveARClaimsData();
  showSaveToast(`🚀 ${collectingItems.length} item AR berhasil diunduh paket zip & dipindahkan ke "Cek AR Setelah di Share" (Tenggat H+1 aktif)!`);
  setClaimsSubTab('SHARED');
}

function checkARClaimsAutoResolution(manualTrigger = false) {
  if (!rawARClaimsList || rawARClaimsList.length === 0) return;

  const now = Date.now();
  let resolvedCount = 0;
  let escalatedPenaltyCount = 0;

  const currentSalesMap = new Map();
  const allTx = (typeof rawTurnoverRows !== 'undefined' && rawTurnoverRows.length > 0)
    ? rawTurnoverRows
    : ((typeof rawCrosellingRows !== 'undefined' && rawCrosellingRows.length > 0) ? rawCrosellingRows : []);

  allTx.forEach(t => {
    const doc = String(t['Document Number'] || '').replace(/\D/g, '');
    const slp = String(t['SlpName'] || t['Sales Employee Name'] || t['User Input'] || t['SC/HL Code'] || '').toUpperCase();
    if (doc) {
      if (!currentSalesMap.has(doc)) currentSalesMap.set(doc, []);
      currentSalesMap.get(doc).push(slp);
    }
  });

  rawARClaimsList.forEach(c => {
    // Abaikan item yang tidak valid
    if (c.validationStatus === 'TIDAK VALID') return;

    const cleanDoc = String(c.docNumber).replace(/\D/g, '');

    // Check if store already resolved it (changed code to BJM.SOS)
    if (currentSalesMap.has(cleanDoc)) {
      const slpList = currentSalesMap.get(cleanDoc);
      const isNowSosmed = slpList.some(s => s.includes('BJM.SOS') || s.includes('SOSMED'));
      if (isNowSosmed && c.status !== 'RESOLVED_BY_STORE') {
        c.status = 'RESOLVED_BY_STORE';
        c.penaltyStatus = 'PASS';
        c.resolvedTimestamp = now;
        resolvedCount++;
        return;
      }
    }

    // Check if SHARED and passed H+1 deadline!
    if (c.status === 'SHARED' && c.deadlineH1Timestamp && now > c.deadlineH1Timestamp) {
      c.status = 'PENALTY_REAL';
      c.penaltyStatus = c.isHandphone ? 'REAL_DENDA' : 'PASS';
      c.penaltyAmount = c.isHandphone ? 50000 : 0;
      escalatedPenaltyCount++;
    }
  });

  if (resolvedCount > 0 || escalatedPenaltyCount > 0) {
    saveARClaimsData();
    if (manualTrigger) {
      alert(`Pengecekan Selesai:\n\n✅ ${resolvedCount} item berhasil direvisi oleh cabang (Selesai).\n🚨 ${escalatedPenaltyCount} item telah lewat batas H+1 dan dieksekusi menjadi Denda Sungguhan.`);
    }
  } else if (manualTrigger) {
    alert('Pengecekan Selesai:\nBelum ada perubahan status baru pada data klaim.');
  }

  renderARClaimsModule();
}

function getH1CountdownBadge(deadlineTimestamp) {
  if (!deadlineTimestamp) return '-';
  const diff = deadlineTimestamp - Date.now();
  if (diff <= 0) {
    return `<span class="badge-countdown expired">🚨 Lewat H+1 (Jatuh Tempo)</span>`;
  }
  const hours = Math.floor(diff / (1000 * 60 * 60));
  const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  if (hours < 4) {
    return `<span class="badge-countdown warning">⚠️ Sisa ${hours}j ${mins}m</span>`;
  }
  return `<span class="badge-countdown safe">⏳ Sisa ${hours}j ${mins}m</span>`;
}

function markClaimResolvedManually(claimId) {
  const item = rawARClaimsList.find(c => c.id === claimId);
  if (!item) return;

  const note = prompt(`Masukkan catatan pengubahan mandiri untuk AR ${item.docNumber}:`, 'Sudah diubah sendiri oleh tim sosmed di SAP');
  if (note === null) return;

  item.status = 'RESOLVED_MANUAL_SOSMED';
  item.manualResolvedBy = 'Tim Sosmed';
  item.manualResolvedAt = Date.now();
  item.manualInputNote = note;
  saveARClaimsData();
  showSaveToast(`✅ Status AR ${item.docNumber} ditandai sudah diubah sendiri oleh Tim Sosmed!`);
  renderARClaimsModule();
}

function deleteARClaimItem(claimId) {
  const idx = rawARClaimsList.findIndex(c => c.id === claimId);
  if (idx === -1) return;
  if (!confirm(`Hapus item AR ${rawARClaimsList[idx].docNumber} dari daftar klaim?`)) return;
  rawARClaimsList.splice(idx, 1);
  saveARClaimsData();
  showSaveToast('🗑️ Item berhasil dihapus dari daftar klaim.');
  renderARClaimsModule();
}

function copyBranchWAFollowupMessage(cabangCode) {
  const branchItems = rawARClaimsList.filter(c => c.cabang === cabangCode && c.validationStatus !== 'TIDAK VALID' && (c.status === 'SHARED' || c.status === 'PENALTY_REAL'));
  if (branchItems.length === 0) {
    alert(`Tidak ada klaim aktif yang sedang menunggu revisi untuk cabang ${cabangCode}.`);
    return;
  }

  const lines = [
    `*PEMBERITAHUAN KLAIM AR BELUM SOSMED - CABANG ${cabangCode}*`,
    `Halo tim toko/kasir cabang ${cabangCode}, mohon segera mengubah sales code di SAP untuk faktur sosmed berikut sebelum batas H+1:`,
    ''
  ];

  branchItems.forEach((it, idx) => {
    lines.push(`${idx + 1}. No. AR: *${it.docNumber}*`);
    lines.push(`   - Barang: ${it.productName}`);
    lines.push(`   - Sales Terinput: ${it.currentSalesCode}`);
    lines.push(`   - Seharusnya: *${it.targetSosmedCode}*`);
    if (it.status === 'PENALTY_REAL') {
      lines.push(`   - 🚨 *STATUS: LEWAT H+1 (DENDA SUNGGUHAN Rp 50.000)*`);
    } else {
      lines.push(`   - ⏳ *Tenggat: H+1*`);
    }
    lines.push('');
  });

  lines.push('Harap konfirmasi jika sales code sudah diubah di SAP. Terima kasih!');
  writeTextToClipboard(lines.join('\n'));
  showSaveToast(`📋 Format pesan follow-up WA untuk cabang ${cabangCode} berhasil disalin!`);
}

function exportClaimsPenaltyReport() {
  const penaltyItems = rawARClaimsList.filter(c => c.validationStatus !== 'TIDAK VALID' && (c.status === 'PENALTY_REAL' || c.status === 'RESOLVED_MANUAL_SOSMED'));
  if (penaltyItems.length === 0) {
    alert('Belum ada item AR yang terkena Denda Sungguhan (semua cabang masih dalam masa tenggang atau sudah direvisi tepat waktu).');
    return;
  }

  const byCab = {};
  let grandTotal = 0;
  penaltyItems.forEach(p => {
    const cab = p.cabang || 'LAINNYA';
    if (!byCab[cab]) byCab[cab] = { count: 0, totalDenda: 0, items: [] };
    byCab[cab].count++;
    byCab[cab].totalDenda += (p.penaltyAmount || 0);
    byCab[cab].items.push(p);
    grandTotal += (p.penaltyAmount || 0);
  });

  const nowStr = new Date().toISOString().split('T')[0];
  const excelData = [
    ['LAPORAN DENDA KASIR CABANG - FAKTUR AR LEWAT H+1', '', '', '', '', '', '', '', ''],
    [`Tanggal Rekap: ${formatCrosellingDate(nowStr)}`, '', '', '', '', '', '', '', ''],
    [`Total Denda Keseluruhan: ${formatRupiah(grandTotal)} (${penaltyItems.length} Kasus)`, '', '', '', '', '', '', '', ''],
    [''],
    ['NO', 'TANGGAL', 'PERIODE', 'NO. AR', 'CABANG', 'CUSTOMER & WA', 'NAMA BARANG', 'KASIR / SALES OFFLINE', 'KODE SOSMED', 'STATUS PENANGANAN', 'NOMINAL DENDA']
  ];

  penaltyItems.forEach((r, idx) => {
    excelData.push([
      idx + 1,
      formatCrosellingDate(r.postingDate),
      r.periode || 'Oktober-26',
      r.docNumber,
      r.cabang,
      `${r.customerName || '-'} (${r.customerPhone || '-'})`,
      r.productName,
      r.currentSalesCode || '-',
      r.targetSosmedCode,
      r.status === 'RESOLVED_MANUAL_SOSMED' ? 'Sudah Diubah Mandiri oleh Tim Sosmed' : 'Belum Diubah (Denda Aktif)',
      r.penaltyAmount || 0
    ]);
  });

  const ws = XLSX.utils.aoa_to_sheet(excelData);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Rekap_Denda_Cabang');
  XLSX.writeFile(wb, `Rekap_Denda_Cabang_GadgetMart_${nowStr}.xlsx`);
  showSaveToast(`📋 Laporan Rekap Denda Cabang berhasil diunduh (${penaltyItems.length} kasus)!`);
}

function setClaimsSubTab(subTabName) {
  currentClaimsSubTab = subTabName;
  ['COLLECTING', 'SHARED', 'PENALTY', 'RESOLVED', 'INVALID'].forEach(st => {
    const btn = document.getElementById(`subtabBtn${st.charAt(0) + st.slice(1).toLowerCase()}`);
    if (btn) btn.classList.toggle('active', st === subTabName);
  });
  claimsCurrentPage = 1;
  applyClaimsFilters();
}

function applyClaimsFilters() {
  const cabEl = document.getElementById('claimsFilterCabang');
  const sosEl = document.getElementById('claimsFilterSosmed');
  const tglEl = document.getElementById('claimsFilterTanggal');
  const searchEl = document.getElementById('claimsSearchInput');

  const cabVal = cabEl ? cabEl.value : 'ALL';
  const sosVal = sosEl ? sosEl.value : 'ALL';
  const tglVal = tglEl ? tglEl.value : '';
  const keyword = (searchEl ? searchEl.value : '').trim().toLowerCase();

  filteredARClaimsList = rawARClaimsList.filter(c => {
    if (currentClaimsSubTab === 'INVALID') {
      if (c.validationStatus !== 'TIDAK VALID') return false;
    } else {
      // Sub-tab lain hanya menampilkan yang VALID
      if (c.validationStatus === 'TIDAK VALID') return false;

      if (currentClaimsSubTab === 'COLLECTING') {
        if (c.status !== 'COLLECTING') return false;
      } else if (currentClaimsSubTab === 'SHARED') {
        if (c.status !== 'SHARED') return false;
      } else if (currentClaimsSubTab === 'PENALTY') {
        if (c.status !== 'PENALTY_REAL' && c.status !== 'RESOLVED_MANUAL_SOSMED') return false;
      } else if (currentClaimsSubTab === 'RESOLVED') {
        if (c.status !== 'RESOLVED_BY_STORE') return false;
      }
    }

    if (cabVal !== 'ALL' && c.cabang !== cabVal) return false;
    if (sosVal !== 'ALL' && c.targetSosmedCode !== sosVal) return false;

    if (tglVal) {
      const itemDate = parseDateToISO(c.collectDate || c.postingDate);
      if (itemDate !== tglVal) return false;
    }

    if (keyword) {
      const comb = `${c.docNumber} ${c.productName} ${c.cabang} ${c.currentSalesCode} ${c.targetSosmedCode} ${c.customerName} ${c.customerPhone} ${c.invalidReason || ''}`.toLowerCase();
      if (!comb.includes(keyword)) return false;
    }

    return true;
  });

  let activeCount = 0;
  if (cabVal !== 'ALL') activeCount++;
  if (sosVal !== 'ALL') activeCount++;
  if (tglVal) activeCount++;
  const badgeEl = document.getElementById('claimsFilterActiveCount');
  if (badgeEl) badgeEl.innerText = activeCount;

  renderARClaimsTable();
}

function toggleClaimsFiltersSection() {
  const el = document.getElementById('claimsFilterSection');
  if (el) {
    el.style.display = (el.style.display === 'none' ? 'flex' : 'none');
  }
}
window.toggleClaimsFiltersSection = toggleClaimsFiltersSection;

function renderARClaimsModule() {
  populateClaimsDropdowns();

  let countCollecting = 0;
  let countShared = 0;
  let countPenalty = 0;
  let countResolved = 0;
  let countInvalid = 0;
  let totalPenaltyRp = 0;

  rawARClaimsList.forEach(c => {
    if (c.validationStatus === 'TIDAK VALID') {
      countInvalid++;
      return;
    }
    if (c.status === 'COLLECTING') countCollecting++;
    else if (c.status === 'SHARED') countShared++;
    else if (c.status === 'PENALTY_REAL' || c.status === 'RESOLVED_MANUAL_SOSMED') {
      countPenalty++;
      totalPenaltyRp += (c.penaltyAmount || 0);
    } else if (c.status === 'RESOLVED_BY_STORE') countResolved++;
  });

  const heroSub = document.getElementById('claimsHeroSub');
  if (heroSub) {
    heroSub.innerText = `${rawARClaimsList.length.toLocaleString('id-ID')} data klaim faktur AR dari siklus pengawasan denda`;
  }

  const elColl = document.getElementById('claimCountCollecting');
  const elShar = document.getElementById('claimCountShared');
  const elPen = document.getElementById('claimCountPenalty');
  const elPenRp = document.getElementById('claimTotalPenaltyRp');
  const elRes = document.getElementById('claimCountResolved');
  const elInv = document.getElementById('claimCountInvalid');

  if (elColl) elColl.innerText = countCollecting.toLocaleString('id-ID');
  if (elShar) elShar.innerText = countShared.toLocaleString('id-ID');
  if (elPen) elPen.innerText = countPenalty.toLocaleString('id-ID');
  if (elPenRp) elPenRp.innerText = `${formatRupiah(totalPenaltyRp)} (${countPenalty} Item)`;
  if (elRes) elRes.innerText = countResolved.toLocaleString('id-ID');
  if (elInv) elInv.innerText = countInvalid.toLocaleString('id-ID');

  const bColl = document.getElementById('badgeSubtabCollecting');
  const bShar = document.getElementById('badgeSubtabShared');
  const bPen = document.getElementById('badgeSubtabPenalty');
  const bRes = document.getElementById('badgeSubtabResolved');
  const bInv = document.getElementById('badgeSubtabInvalid');

  if (bColl) bColl.innerText = countCollecting;
  if (bShar) bShar.innerText = countShared;
  if (bPen) bPen.innerText = countPenalty;
  if (bRes) bRes.innerText = countResolved;
  if (bInv) bInv.innerText = countInvalid;

  const btnShare = document.getElementById('btnShareBatchDay4');
  if (btnShare) {
    btnShare.style.display = (currentClaimsSubTab === 'COLLECTING') ? 'inline-flex' : 'none';
    btnShare.innerText = `📦 Unduh Paket Cabang & Share (${countCollecting})`;
  }

  const btnSingle = document.getElementById('btnDownloadSingleExcel');
  if (btnSingle) {
    btnSingle.innerHTML = (currentClaimsSubTab === 'COLLECTING' && countCollecting > 0)
      ? `📊 Unduh Jadi 1 Excel (${countCollecting})`
      : `📊 Unduh Jadi 1 Excel`;
  }

  applyClaimsFilters();
}

function renderARClaimsTable() {
  const tbody = document.getElementById('claimsTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  const total = filteredARClaimsList.length;
  const maxPage = Math.max(1, Math.ceil(total / CLAIMS_PAGE_SIZE));
  const startIdx = (claimsCurrentPage - 1) * CLAIMS_PAGE_SIZE;
  const endIdx = Math.min(startIdx + CLAIMS_PAGE_SIZE, total);
  const pageRows = filteredARClaimsList.slice(startIdx, endIdx);

  const heroSub = document.getElementById('claimsHeroSub');
  if (heroSub) {
    heroSub.innerText = `${total.toLocaleString('id-ID')} data klaim faktur AR pada sub-tab ini`;
  }

  const infoEl = document.getElementById('claimsPaginationInfo');
  if (infoEl) {
    infoEl.innerText = total > 0 
      ? `Menampilkan ${startIdx + 1} - ${endIdx} dari total ${total.toLocaleString('id-ID')} Data Klaim`
      : 'Tidak ada data klaim untuk filter ini';
  }
  const curPageText = document.getElementById('claimsCurrentPageText');
  if (curPageText) curPageText.innerText = `Halaman ${claimsCurrentPage} / ${maxPage}`;
  const btnPrev = document.getElementById('btnClaimsPrev');
  if (btnPrev) btnPrev.disabled = claimsCurrentPage <= 1;
  const btnNext = document.getElementById('btnClaimsNext');
  if (btnNext) btnNext.disabled = claimsCurrentPage >= maxPage;

  if (pageRows.length === 0) {
    tbody.innerHTML = `<tr><td colspan="13" class="text-center" style="padding: 40px; color: var(--text-muted); font-size: 0.9rem;">Tidak ada data klaim AR pada status ini.</td></tr>`;
    return;
  }

  pageRows.forEach((r, i) => {
    const rawWA = String(r.customerPhone || '').replace(/\D/g, '');
    const normWA = normalizePhone(rawWA);
    const displayWA = formatPhoneDisplay(rawWA);
    const custName = r.customerName || '-';
    const initials = getCustomerInitials(custName);

    // 1. CUSTOMER: Avatar Circle (slate-900) + Name Bold + Phone Subtext (Persis Format Keep)
    const custHtml = `
      <div class="customer-cell-flex">
        <div class="avatar-circle-sm" title="${custName}">${initials}</div>
        <div class="customer-info-box">
          <span class="customer-name-bold">${custName}</span>
          ${(normWA && normWA.length >= 8) ? `
            <a href="https://wa.me/${normWA}" target="_blank" class="customer-phone-sub" title="Hubungi customer di WhatsApp">
              ${displayWA}
            </a>
          ` : `<span class="customer-phone-sub">${displayWA || '-'}</span>`}
        </div>
      </div>
    `;

    // 2. TANGGAL: Formatted Date + Periode
    const dateFormatted = formatCrosellingDate(r.postingDate) || '-';
    const periodeDisplay = r.periode || '';
    const dateHtml = `
      <div style="font-weight: 700; color: #0f172a; white-space: nowrap; font-variant-numeric: tabular-nums;">${dateFormatted}</div>
      ${periodeDisplay ? `<div style="font-size: 0.70rem; color: #64748b; margin-top: 2px;">${periodeDisplay}</div>` : ''}
    `;

    // 3. NO. AR: Bold Red/Blue Clickable Number
    const arHtml = `
      <div class="cell-report-ar-wrapper" style="justify-content: center;">
        <span class="text-ar-number-bold" onclick="openARDetailModal('${r.docNumber}')" title="Klik untuk cek detail faktur AR" style="cursor: pointer; font-variant-numeric: tabular-nums;">
          ${r.docNumber}
        </span>
      </div>
    `;

    // 4. BARANG: Product name bold + Harga Jual
    const barangHtml = `
      <div style="font-weight: 700; color: #0f172a; font-size: 0.82rem; line-height: 1.35;">${r.productName || '-'}</div>
      <div style="font-size: 0.72rem; color: #64748b; margin-top: 3px; font-variant-numeric: tabular-nums; font-weight: 600;">${formatRupiah(r.hargaJual)}</div>
    `;

    // 5. KODE SOSMED: Pill badge + PIC
    const sosmedHtml = `
      <span class="badge-sosmed-pill" style="font-size: 0.72rem; padding: 3px 8px; font-weight: 700;">${r.targetSosmedCode || '-'}</span>
      ${r.picSosmed ? `<div style="font-size: 0.70rem; color: #64748b; margin-top: 3px;">${r.picSosmed}</div>` : ''}
    `;

    // 6. CABANG: Bold Branch Name
    const cabangHtml = `
      <span style="font-weight: 800; font-size: 0.80rem; color: #0f172a; letter-spacing: 0.02em;">${r.cabang || '-'}</span>
    `;

    // 7. DENDA: Modern Status Pill with Dot (Anti-Slop Clean Style)
    let dendaDisplay = '';
    if (r.status === 'COLLECTING' || r.status === 'SHARED') {
      dendaDisplay = r.isHandphone 
        ? `<span class="modern-status-pill status-pill-orange"><span class="status-dot"></span>Potensi Rp 50.000</span>` 
        : `<span class="modern-status-pill status-pill-gray"><span class="status-dot"></span>Pass (Rp 0)</span>`;
    } else if (r.status === 'PENALTY_REAL' || r.status === 'RESOLVED_MANUAL_SOSMED') {
      dendaDisplay = r.isHandphone 
        ? `<span class="modern-status-pill status-pill-red"><span class="status-dot"></span>Denda Rp 50.000</span>` 
        : `<span class="modern-status-pill status-pill-gray"><span class="status-dot"></span>Pass (Rp 0)</span>`;
    } else if (r.status === 'RESOLVED_BY_STORE') {
      dendaDisplay = `<span class="modern-status-pill status-pill-green"><span class="status-dot"></span>Gugur (Pass)</span>`;
    } else {
      dendaDisplay = `<span class="modern-status-pill status-pill-gray"><span class="status-dot"></span>-</span>`;
    }

    // 8. FORMAT KEEP: Clean snippet box
    const keepHtml = r.keepText ? `
      <div class="claim-keep-box" title="Format Keep WhatsApp">${r.keepText}</div>
    ` : `<span style="color: #94a3b8; font-size: 0.76rem;">-</span>`;

    // 9. EVIDENCE: Clean thumbnail or multi-photo strip
    const imgs = (Array.isArray(r.evidenceImages) && r.evidenceImages.length > 0)
      ? r.evidenceImages
      : (r.evidenceImage ? [r.evidenceImage] : []);

    let evidenceHtml = '';
    if (imgs.length > 0) {
      const thumbsHtml = imgs.slice(0, 2).map((imgUrl, i) => `
        <div class="evidence-thumb-wrapper" onclick="openEvidenceImageModal('${r.id}', ${i})" title="Bukti ${i + 1} dari ${imgs.length} (Klik untuk perbesar)">
          <img src="${imgUrl}" class="evidence-thumb-img" alt="Bukti ${i + 1}">
          <div class="evidence-zoom-hint">${i + 1}</div>
        </div>
      `).join('');

      const moreBadge = imgs.length > 2 
        ? `<span class="evidence-more-badge" onclick="openEvidenceImageModal('${r.id}', 2)" title="Lihat ${imgs.length - 2} bukti lainnya">+${imgs.length - 2}</span>` 
        : '';

      evidenceHtml = `
        <div class="evidence-cell">
          <div class="evidence-thumbs-strip">
            ${thumbsHtml}
            ${moreBadge}
          </div>
          <div class="evidence-cell-actions" style="margin-top: 3px;">
            <button type="button" class="btn-evidence-tiny" onclick="triggerEvidenceUpload('${r.id}')" title="Tambah foto bukti lain pada baris ini">+ Tambah</button>
            <button type="button" class="btn-evidence-tiny" onclick="openEvidenceImageModal('${r.id}', 0)" title="Lihat galeri semua foto bukti (${imgs.length})">Lihat (${imgs.length})</button>
            <button type="button" class="btn-evidence-tiny btn-evidence-del" onclick="removeEvidenceImage('${r.id}')" title="Hapus foto bukti baris ini">Hapus</button>
          </div>
        </div>
      `;
    } else {
      evidenceHtml = `
        <button type="button" class="btn-upload-evidence" onclick="triggerEvidenceUpload('${r.id}')" title="Klik untuk mengunggah bukti format keep (bisa pilih beberapa gambar)">
          + Unggah Bukti
        </button>
      `;
    }

    // 10. AR SALES EMPLOYEE
    const salesHtml = `
      <span style="font-size: 0.76rem; font-weight: 600; color: #334155;">${r.currentSalesCode || '-'}</span>
    `;

    // 11. STATUS VALIDASI
    const isInvalid = (r.validationStatus === 'TIDAK VALID');
    const validityHtml = `
      <select class="select-claim-validity ${isInvalid ? 'invalid' : 'valid'}" onchange="handleClaimValidityChange('${r.id}', this.value)" title="Ubah status validasi klaim">
        <option value="VALID" ${!isInvalid ? 'selected' : ''}>Valid</option>
        <option value="TIDAK VALID" ${isInvalid ? 'selected' : ''}>Tidak Valid</option>
      </select>
    `;

    // 12. ALASAN TIDAK VALID
    const reasonHtml = isInvalid ? `
      <input type="text" class="input-claim-reason" value="${r.invalidReason || ''}" placeholder="Tulis alasan..." onchange="handleClaimInvalidReasonChange('${r.id}', this.value)" title="Alasan tidak valid">
    ` : `<span style="color: #94a3b8; font-size: 0.76rem;">-</span>`;

    // 13. AKSI: Anti-Slop Clean Flat Buttons
    let actionBtn = '';
    if (r.status === 'COLLECTING') {
      actionBtn = `<button type="button" class="btn-claim-action btn-claim-delete" onclick="deleteARClaimItem('${r.id}')" title="Hapus dari antrean klaim">Hapus</button>`;
    } else if (r.status === 'SHARED') {
      actionBtn = `
        <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
          <button type="button" class="btn-claim-action btn-claim-secondary" onclick="copyBranchWAFollowupMessage('${r.cabang}')" title="Salin pesan follow-up WA untuk cabang ini">Chat WA</button>
          <button type="button" class="btn-claim-action btn-claim-delete" onclick="deleteARClaimItem('${r.id}')" title="Hapus">Hapus</button>
        </div>
      `;
    } else if (r.status === 'PENALTY_REAL') {
      actionBtn = `
        <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
          <button type="button" class="btn-claim-action btn-claim-primary" onclick="markClaimResolvedManually('${r.id}')" title="Tandai sudah direvisi mandiri oleh tim sosmed di SAP">Ubah Mandiri</button>
          <button type="button" class="btn-claim-action btn-claim-secondary" onclick="copyBranchWAFollowupMessage('${r.cabang}')" title="Kirim teguran denda ke cabang">Tegur</button>
        </div>
      `;
    } else {
      actionBtn = `<button type="button" class="btn-claim-action btn-claim-delete" onclick="deleteARClaimItem('${r.id}')" title="Hapus">Hapus</button>`;
    }

    const tr = document.createElement('tr');
    if (isInvalid) {
      tr.classList.add('row-lose-tint');
    }

    tr.innerHTML = `
      <td class="text-left">${custHtml}</td>
      <td class="text-center">${dateHtml}</td>
      <td class="text-center">${arHtml}</td>
      <td class="text-left">${barangHtml}</td>
      <td class="text-center">${sosmedHtml}</td>
      <td class="text-center">${cabangHtml}</td>
      <td class="text-center">${dendaDisplay}</td>
      <td class="text-left">${keepHtml}</td>
      <td class="text-center">${evidenceHtml}</td>
      <td class="text-left">${salesHtml}</td>
      <td class="text-center">${validityHtml}</td>
      <td class="text-left">${reasonHtml}</td>
      <td class="text-center">${actionBtn}</td>
    `;
    tbody.appendChild(tr);
  });
}

function populateClaimsDropdowns() {
  const cabSelect = document.getElementById('claimsFilterCabang');
  const sosSelect = document.getElementById('claimsFilterSosmed');

  if (cabSelect) {
    const curCab = cabSelect.value;
    const branches = Array.from(new Set(rawARClaimsList.map(c => c.cabang).filter(Boolean))).sort();
    let cabOpts = '<option value="ALL">Semua Cabang Toko</option>';
    branches.forEach(b => {
      cabOpts += `<option value="${b}" ${b === curCab ? 'selected' : ''}>${b}</option>`;
    });
    cabSelect.innerHTML = cabOpts;
  }

  if (sosSelect) {
    const curSos = sosSelect.value;
    const sosList = Array.from(new Set(rawARClaimsList.map(c => c.targetSosmedCode).filter(Boolean))).sort();
    let sosOpts = '<option value="ALL">Semua Kode Sosmed</option>';
    sosList.forEach(s => {
      sosOpts += `<option value="${s}" ${s === curSos ? 'selected' : ''}>${s}</option>`;
    });
    sosSelect.innerHTML = sosOpts;
  }
}

function resetClaimsFilters() {
  if (document.getElementById('claimsFilterCabang')) document.getElementById('claimsFilterCabang').value = 'ALL';
  if (document.getElementById('claimsFilterSosmed')) document.getElementById('claimsFilterSosmed').value = 'ALL';
  if (document.getElementById('claimsFilterTanggal')) document.getElementById('claimsFilterTanggal').value = '';
  if (document.getElementById('claimsSearchInput')) document.getElementById('claimsSearchInput').value = '';
  claimsCurrentPage = 1;
  applyClaimsFilters();
}

function changeClaimsPage(delta) {
  const maxPage = Math.max(1, Math.ceil(filteredARClaimsList.length / CLAIMS_PAGE_SIZE));
  const newPage = claimsCurrentPage + delta;
  if (newPage >= 1 && newPage <= maxPage) {
    claimsCurrentPage = newPage;
    renderARClaimsTable();
  }
}

// Auto init on page load with state resilience (Super fast priority rendering for Report Matrix)
async function initSosmedApp() {
  // 1. Tampilkan tab target segera tanpa menunggu network
  const initialActiveTab = (typeof window.WORKSPACE_ACTIVE_TAB === 'number') ? window.WORKSPACE_ACTIVE_TAB : 1;
  const keepInput = document.getElementById('keepSearchInput');
  if (keepInput) keepInput.value = '';
  if (typeof toggleKeepClearBtn === 'function') toggleKeepClearBtn();
  if (typeof sanitizeKeepAutofill === 'function') sanitizeKeepAutofill();

  // Tangkal delay autofill agresif dari password manager Chrome/Edge
  [100, 300, 600, 1200].forEach(delay => {
    setTimeout(() => {
      if (typeof sanitizeKeepAutofill === 'function') sanitizeKeepAutofill();
    }, delay);
  });

  if (typeof switchTab === 'function') {
    try { switchTab(initialActiveTab); } catch (e) { console.warn('switchTab init error:', e); }
  }

  try { initLiveClock(); } catch (e) { console.warn('initLiveClock error:', e); }

  // 2. Muat metadata esensial secara paralel (non-blocking)
  await Promise.allSettled([
    loadMonthlyTargets(),
    loadSalesPhotos(),
    loadMonthlyArchives(),
    loadARClaimsData()
  ]);

  try { populateAdminPanel(getMasterSales()); } catch (e) { console.warn('populateAdminPanel error:', e); }
  try { onAdminTargetMonthChange(); } catch (e) { console.warn('onAdminTargetMonthChange error:', e); }
  try { renderArchiveGrid(); } catch (e) { console.warn('renderArchiveGrid error:', e); }
  try { initOLADashboardState(); } catch (e) {}

  // 3. Muat data turnover SQL (cepat ~50ms) & langsung render Report 1 dan 2
  try { await loadAllDefaultFiles(); } catch (e) { console.error('loadAllDefaultFiles error:', e); }
  try { populateDashboardFilterDropdowns(); } catch (e) { console.warn('populateDashboardFilterDropdowns error:', e); }
  try { applyDashboardFilters(); } catch (e) { console.warn('applyDashboardFilters error:', e); }

  // 4. Muat data sekunder berukuran besar (Keep Google Sheets & Realtime Poller) di background
  setTimeout(() => {
    try { loadKeepData(true); } catch (e) { console.warn('loadKeepData background error:', e); }
    try { startRealtimeSyncEngine(); } catch (e) { console.warn('startRealtimeSyncEngine error:', e); }
  }, 100);
}
window.initSosmedApp = initSosmedApp;

if (document.readyState === 'loading') {
  window.addEventListener('DOMContentLoaded', initSosmedApp);
} else {
  initSosmedApp();
}

// Shortcut Global Cmd+K / Ctrl+K untuk Fokus ke Search Bar Aktif (Gambar 1 & 2)
document.addEventListener('keydown', (e) => {
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    if (currentActiveTab === 4) {
      const inp = document.getElementById('keepSearchInput');
      if (inp) { inp.focus(); inp.select(); }
    } else if (currentActiveTab === 5) {
      const inp = document.getElementById('auditSearchInput');
      if (inp) { inp.focus(); inp.select(); }
    } else if (currentActiveTab === 9) {
      const inp = document.getElementById('omniSearchInput');
      if (inp) { inp.focus(); inp.select(); }
    }
  }
});

// ==========================================================================
// ==========================================================================
// 17. OMNICHANNEL INBOX FU CONSOLE (LIGHT CRM ENGINE - CLEAN MONOCHROME)
// ==========================================================================

// GEOMETRIC BOT AVATAR GENERATOR (Ringan, Unik per Pengirim Sesuai Referensi)
function generateBotAvatarSvg(identifier, size) {
  size = size || 36;
  let hash = 0;
  const str = String(identifier || 'PIC');
  for (let i = 0; i < str.length; i++) {
    hash = ((hash << 5) - hash) + str.charCodeAt(i);
    hash |= 0;
  }
  const absHash = Math.abs(hash);
  const colors = [
    '#f97316', '#ef4444', '#10b981', '#3b82f6',
    '#8b5cf6', '#ec4899', '#d97706', '#06b6d4',
    '#64748b', '#14b8a6', '#f43f5e', '#84cc16'
  ];
  const color = colors[absHash % colors.length];
  const shapeIndex = Math.floor(absHash / colors.length) % 7;

  let shapePath = '';
  let eyes = '<circle cx="16" cy="19" r="2.6" fill="#0f172a"/><circle cx="24" cy="19" r="2.6" fill="#0f172a"/>';

  if (shapeIndex === 0) {
    // Circle
    shapePath = '<circle cx="20" cy="20" r="18" fill="' + color + '"/>';
  } else if (shapeIndex === 1) {
    // Squircle (Rounded Square)
    shapePath = '<rect x="3" y="3" width="34" height="34" rx="10" fill="' + color + '"/>';
  } else if (shapeIndex === 2) {
    // Pill / Stadium
    shapePath = '<rect x="2" y="8" width="36" height="24" rx="12" fill="' + color + '"/>';
    eyes = '<circle cx="16" cy="20" r="2.6" fill="#0f172a"/><circle cx="24" cy="20" r="2.6" fill="#0f172a"/>';
  } else if (shapeIndex === 3) {
    // Rounded Triangle
    shapePath = '<path d="M 18 5.5 C 19 3.5 21 3.5 22 5.5 L 36.5 30 C 37.5 32 36.5 35 34 35 L 6 35 C 3.5 35 2.5 32 3.5 30 Z" fill="' + color + '"/>';
    eyes = '<circle cx="16" cy="24" r="2.5" fill="#0f172a"/><circle cx="24" cy="24" r="2.5" fill="#0f172a"/>';
  } else if (shapeIndex === 4) {
    // Hexagon
    shapePath = '<path d="M 20 3 L 34.5 11 C 36 12 37 14 37 16 L 37 24 C 37 26 36 28 34.5 29 L 20 37 C 19 37.5 17 37.5 16 37 L 5.5 29 C 4 28 3 26 3 24 L 3 16 C 3 14 4 12 5.5 11 Z" fill="' + color + '"/>';
    eyes = '<circle cx="16" cy="20" r="2.6" fill="#0f172a"/><circle cx="24" cy="20" r="2.6" fill="#0f172a"/>';
  } else if (shapeIndex === 5) {
    // Cloud
    shapePath = '<path d="M 10 32 L 30 32 A 6 6 0 0 0 36 26 A 5 5 0 0 0 33 18 A 8 8 0 0 0 18 13 A 6 6 0 0 0 8 19 A 5 5 0 0 0 4 25 A 6 6 0 0 0 10 32 Z" fill="' + color + '"/>';
    eyes = '<circle cx="17" cy="23" r="2.4" fill="#0f172a"/><circle cx="23" cy="23" r="2.4" fill="#0f172a"/>';
  } else {
    // Droplet / Teardrop
    shapePath = '<path d="M 20 3 C 20 3 35 18 35 26 C 35 33.5 28.5 37 20 37 C 11.5 37 5 33.5 5 26 C 5 18 20 3 20 3 Z" fill="' + color + '"/>';
    eyes = '<circle cx="16" cy="25" r="2.5" fill="#0f172a"/><circle cx="24" cy="25" r="2.5" fill="#0f172a"/>';
  }

  return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 40 40" style="display:block;">' + shapePath + eyes + '</svg>';
}

// QUOTED REPLY & IMAGE LIGHTBOX STATE
let activeQuotedMsg = null; // { text: '...', senderName: '...' }

function parseQuotedFromText(rawText) {
  if (!rawText || typeof rawText !== 'string') return { cleanText: rawText || '', replyTo: null };
  const trimmed = rawText.trim();
  if (!trimmed.startsWith('>')) return { cleanText: rawText, replyTo: null };

  const m1 = trimmed.match(/^>(?: \*(.*?)\*:?)?\s*([\s\S]*?)\n\n([\s\S]*)$/);
  if (m1) {
    const sender = (m1[1] || 'Pesan').trim();
    const quoted = m1[2].replace(/^>\s?/gm, '').trim();
    const actual = m1[3].trim();
    return {
      cleanText: actual,
      replyTo: { senderName: sender, text: quoted }
    };
  }

  const m2 = trimmed.match(/^> \[(.*?)\]\s*([\s\S]*?)\n\n([\s\S]*)$/);
  if (m2) {
    const sender = (m2[1] || 'Pesan').trim();
    const quoted = m2[2].replace(/^>\s?/gm, '').trim();
    const actual = m2[3].trim();
    return {
      cleanText: actual,
      replyTo: { senderName: sender, text: quoted }
    };
  }

  const m3 = trimmed.match(/^> ([\s\S]*?)\n\n([\s\S]*)$/);
  if (m3) {
    const quoted = m3[1].replace(/^>\s?/gm, '').trim();
    const actual = m3[2].trim();
    return {
      cleanText: actual,
      replyTo: { senderName: 'Pesan', text: quoted }
    };
  }

  return { cleanText: rawText, replyTo: null };
}

function startOmniQuotedReply(msgId, text, senderName) {
  if (arguments.length === 2) {
    senderName = text;
    text = msgId;
    msgId = '';
  }
  activeQuotedMsg = { id: msgId || '', text: text || '', senderName: senderName || 'Pesan' };
  const bar = document.getElementById('omniQuotedReplyBar');
  const titleSpan = document.getElementById('omniQuotedSenderName');
  const snippetDiv = document.getElementById('omniQuotedSnippet');
  if (bar && titleSpan && snippetDiv) {
    titleSpan.innerText = activeQuotedMsg.senderName;
    snippetDiv.innerText = activeQuotedMsg.text;
    bar.style.display = 'flex';
  }
  const inp = document.getElementById('omniReplyInput');
  if (inp) {
    inp.focus();
    inp.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
}

function cancelOmniQuotedReply() {
  activeQuotedMsg = null;
  const bar = document.getElementById('omniQuotedReplyBar');
  if (bar) bar.style.display = 'none';
}

function findTargetQuotedRow(replyTo) {
  if (!replyTo) return null;
  const stream = document.getElementById('omniMessagesStream');
  if (!stream) return null;

  const allRows = Array.from(stream.querySelectorAll('.omni-msg-row'));
  if (allRows.length === 0) return null;

  // 1. Jika ada ID spesifik pesan
  if (replyTo.id) {
    const byId = document.getElementById('omniMsg_' + replyTo.id) || stream.querySelector('[data-msg-id="' + replyTo.id + '"]');
    if (byId) return byId;
  }

  const rawQuotedText = String(replyTo.text || '').trim();
  if (!rawQuotedText) return null;

  function getBubbleCleanText(row) {
    const bubble = row.querySelector('.omni-bubble');
    if (!bubble) return '';
    const clone = bubble.cloneNode(true);
    const rb = clone.querySelector('.omni-bubble-reply-block');
    if (rb) rb.remove();
    const timeEl = clone.querySelector('.omni-bubble-time');
    if (timeEl) timeEl.remove();
    const senderEl = clone.querySelector('.omni-bubble-sender');
    if (senderEl) senderEl.remove();
    return clone.innerText.trim();
  }

  // 2. Ekstraksi spesifik jika merupakan Format Keep follow-up
  const waMatch = rawQuotedText.match(/nomor\s+whatsapp\s*:\s*([0-9+]+)/i);
  const custMatch = rawQuotedText.match(/nama\s+customer\s*:\s*([^\n\r]+)/i);
  const tglMatch = rawQuotedText.match(/tanggal\s+keep\s*:\s*([^\n\r]+)/i);
  const barangMatch = rawQuotedText.match(/barang\s*:\s*([^\n\r]+)/i);

  const targetWA = waMatch ? waMatch[1].replace(/\D/g, '') : '';
  const targetCust = custMatch ? custMatch[1].trim().toLowerCase() : '';
  const targetTgl = tglMatch ? tglMatch[1].trim().toLowerCase() : '';
  const targetBarang = barangMatch ? barangMatch[1].trim().toLowerCase() : '';

  if (targetWA || targetCust || targetTgl) {
    let bestRow = null;
    let highestScore = 0;

    for (const row of allRows) {
      const bubbleText = getBubbleCleanText(row).toLowerCase();
      const bubbleDigits = bubbleText.replace(/\D/g, '');
      let score = 0;

      if (targetWA && (bubbleText.includes(targetWA) || (targetWA.length >= 9 && bubbleDigits.includes(targetWA.slice(-9))))) {
        score += 10;
      }
      if (targetCust && targetCust.length > 2 && bubbleText.includes(targetCust)) {
        score += 8;
      }
      if (targetTgl && bubbleText.includes(targetTgl)) {
        score += 5;
      }
      if (targetBarang && targetBarang.length > 3 && bubbleText.includes(targetBarang)) {
        score += 4;
      }

      if (score > highestScore) {
        highestScore = score;
        bestRow = row;
      }
    }

    if (bestRow && highestScore >= 5) {
      return bestRow;
    }
  }

  // 3. Pencarian berbasis baris-baris unik dari teks kutipan
  const lines = rawQuotedText.split('\n')
    .map(function(l) { return l.trim().toLowerCase(); })
    .filter(function(l) { return l.length >= 8 && !l.startsWith('halo kak') && !l.startsWith('mimin mau') && !l.startsWith('silakan bantu'); });

  let bestMatch = null;
  let bestScore = 0;

  for (const row of allRows) {
    const bubbleText = getBubbleCleanText(row).toLowerCase();
    if (!bubbleText) continue;

    let score = 0;
    for (const line of lines) {
      if (bubbleText.includes(line)) {
        score += line.length;
      }
    }

    if (bubbleText === rawQuotedText.toLowerCase() || bubbleText.includes(rawQuotedText.toLowerCase())) {
      score += 100;
    }

    if (score > bestScore) {
      bestScore = score;
      bestMatch = row;
    }
  }

  if (bestMatch && bestScore > 0) {
    return bestMatch;
  }

  // 4. Fallback: pencarian substring umum
  const normalizedQuoted = rawQuotedText.toLowerCase().replace(/\s+/g, ' ');
  const fallbackShort = normalizedQuoted.slice(0, 30);
  for (let i = allRows.length - 1; i >= 0; i--) {
    const bubbleText = getBubbleCleanText(allRows[i]).toLowerCase();
    if (bubbleText.includes(fallbackShort)) {
      return allRows[i];
    }
  }

  return null;
}

function handleOmniQuotedClick(idx) {
  const msgs = window.currentOmniChatMessages || [];
  const msg = msgs[idx];
  if (!msg || !msg.replyTo) return;

  const targetEl = findTargetQuotedRow(msg.replyTo);
  if (targetEl) {
    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    targetEl.classList.remove('omni-msg-focused');
    void targetEl.offsetWidth; // trigger reflow
    targetEl.classList.add('omni-msg-focused');

    setTimeout(function() {
      if (targetEl) targetEl.classList.remove('omni-msg-focused');
    }, 2000);
  }
}
window.handleOmniQuotedClick = handleOmniQuotedClick;

function scrollToOmniQuotedMessage(quotedId, quotedText) {
  const targetEl = findTargetQuotedRow({ id: quotedId, text: quotedText });
  if (targetEl) {
    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    targetEl.classList.remove('omni-msg-focused');
    void targetEl.offsetWidth;
    targetEl.classList.add('omni-msg-focused');

    setTimeout(function() {
      if (targetEl) targetEl.classList.remove('omni-msg-focused');
    }, 2000);
  }
}
window.scrollToOmniQuotedMessage = scrollToOmniQuotedMessage;

let omniZoom = 1;
let omniPanX = 0;
let omniPanY = 0;
let isOmniDragging = false;
let omniDragStartX = 0;
let omniDragStartY = 0;
let omniHasMoved = false;
let isOmniZoomInitialized = false;

function updateOmniImageTransform() {
  const img = document.getElementById('omniImageModalImg');
  const stage = document.getElementById('omniImageStage');
  const zoomText = document.getElementById('omniImageZoomLevel');
  if (!img) return;

  img.style.transform = `translate(${omniPanX}px, ${omniPanY}px) scale(${omniZoom})`;

  if (zoomText) {
    zoomText.textContent = Math.round(omniZoom * 100) + '%';
  }
  if (stage) {
    if (omniZoom > 1.05) {
      stage.style.cursor = isOmniDragging ? 'grabbing' : 'grab';
    } else {
      stage.style.cursor = isOmniDragging ? 'grabbing' : 'zoom-in';
    }
  }
}

function resetOmniZoom() {
  omniZoom = 1;
  omniPanX = 0;
  omniPanY = 0;
  isOmniDragging = false;
  omniHasMoved = false;
  updateOmniImageTransform();
}

function zoomOmni(delta) {
  const oldZoom = omniZoom;
  let newZoom = Math.round((omniZoom + delta) * 100) / 100;
  newZoom = Math.min(Math.max(0.5, newZoom), 5.0); // 50% hingga 500%

  if (Math.abs(newZoom - oldZoom) < 0.001) return;

  omniZoom = newZoom;

  // Jika kembali ke ukuran 100% atau lebih kecil, selalu kunci posisi center (0, 0)
  if (omniZoom <= 1.0) {
    omniPanX = 0;
    omniPanY = 0;
  }

  updateOmniImageTransform();
}

function initOmniZoomEvents() {
  if (isOmniZoomInitialized) return;
  const modal = document.getElementById('omniImageModal');
  const stage = document.getElementById('omniImageStage');
  const img = document.getElementById('omniImageModalImg');
  if (!modal || !stage || !img) return;

  isOmniZoomInitialized = true;

  // Zoom dengan scroll wheel mouse selalu center di tengah layar
  modal.addEventListener('wheel', (e) => {
    if (modal.style.display === 'none') return;
    e.preventDefault();
    e.stopPropagation();
    const delta = e.deltaY < 0 ? 0.2 : -0.2;
    zoomOmni(delta);
  }, { passive: false });

  // Drag / Pan untuk menggeser gambar secara leluasa
  stage.addEventListener('mousedown', (e) => {
    if (e.button !== 0) return; // Hanya klik kiri
    isOmniDragging = true;
    omniHasMoved = false;
    omniDragStartX = e.clientX - omniPanX;
    omniDragStartY = e.clientY - omniPanY;
    stage.classList.add('is-dragging');
    e.preventDefault();
  });

  window.addEventListener('mousemove', (e) => {
    if (!isOmniDragging) return;
    const dx = Math.abs(e.clientX - (omniDragStartX + omniPanX));
    const dy = Math.abs(e.clientY - (omniDragStartY + omniPanY));
    if (dx > 3 || dy > 3) omniHasMoved = true;
    omniPanX = e.clientX - omniDragStartX;
    omniPanY = e.clientY - omniDragStartY;
    updateOmniImageTransform();
  });

  window.addEventListener('mouseup', (e) => {
    if (isOmniDragging) {
      isOmniDragging = false;
      stage.classList.remove('is-dragging');
      updateOmniImageTransform();

      // Jika klik di luar gambar (latar belakang gelap) dan tidak melakukan drag, tutup modal
      if (!omniHasMoved && e.target === stage) {
        closeOmniImageModal();
      }
    }
  });

  // Double click pada gambar/stage untuk zoom 1.8x atau reset ke center
  stage.addEventListener('dblclick', (e) => {
    e.preventDefault();
    if (omniZoom > 1.05 || omniPanX !== 0 || omniPanY !== 0) {
      resetOmniZoom();
    } else {
      omniZoom = 1.8;
      omniPanX = 0;
      omniPanY = 0;
      updateOmniImageTransform();
    }
  });

  // Pintasan keyboard (Escape, +, -, 0)
  window.addEventListener('keydown', (e) => {
    if (modal.style.display === 'none') return;
    if (e.key === 'Escape') {
      closeOmniImageModal();
    } else if (e.key === '+' || e.key === '=') {
      zoomOmni(0.25);
    } else if (e.key === '-' || e.key === '_') {
      zoomOmni(-0.25);
    } else if (e.key === '0') {
      resetOmniZoom();
    }
  });
}

function openOmniImageModal(src) {
  const modal = document.getElementById('omniImageModal');
  const img = document.getElementById('omniImageModalImg');
  const dl = document.getElementById('omniImageModalDownload');
  if (modal && img) {
    // Pindahkan modal langsung ke document.body agar posisi fixed selalu 100% full screen dan center
    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
    initOmniZoomEvents();
    resetOmniZoom();
    img.src = src;
    if (dl) dl.href = src;
    modal.style.display = 'flex';
  }
}

function closeOmniImageModal() {
  const modal = document.getElementById('omniImageModal');
  if (modal) {
    modal.style.display = 'none';
    resetOmniZoom();
  }
}

window.zoomOmni = zoomOmni;
window.resetOmniZoom = resetOmniZoom;
window.openOmniImageModal = openOmniImageModal;
window.closeOmniImageModal = closeOmniImageModal;

let currentOmniView = 'CHATS'; // 'CHATS' or 'MASTER'
let currentOmniFolder = 'ALL'; // 'ALL', 'ASSIGNED', 'UNRESPONDED', 'RESOLVED'
let selectedOmniPicKey = null; // Key (clean WA or picKey) of active PIC FU
let selectedOmniKeepSubTab = 'PENDING'; // 'PENDING' (Belum Diisi) | 'RESOLVED' (Sudah Selesai)
let lastWatzapSyncTime = 0;
let isPollingWatzap = false;
let watzapPollInterval = null;
let omniPendingFile = null;

const OMNI_MASTER_STORAGE_KEY = 'gadgetmart_pic_master';

const OMNI_STATUS_PALETTE = {
  'WIN': { bg: '#ecfdf5', text: '#065f46', border: '#10b981' },
  'NEED TO CHECK': { bg: '#fef3c7', text: '#b45309', border: '#f59e0b' },
  'LOSE': { bg: '#fef2f2', text: '#991b1b', border: '#ef4444' },
  'BELUM DIINPUT': { bg: '#f1f5f9', text: '#475569', border: '#cbd5e1' },
  'DOUBLE KEEP': { bg: '#eff6ff', text: '#1e40af', border: '#3b82f6' },
  'ON PROGRESS LOW': { bg: '#f8fafc', text: '#334155', border: '#94a3b8' },
  'ON PROGRESS MEDIUM': { bg: '#f7fee7', text: '#3f6212', border: '#84cc16' },
  'ON PROGRESS HOT': { bg: '#faf5ff', text: '#6b21a8', border: '#a855f7' },
  'TAKE OUT': { bg: '#fff1f2', text: '#9f1239', border: '#f43f5e' }
};

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function cleanPhoneDigits(raw) {
  return normalizeWhatsAppPhone(raw);
}

function normalizeWhatsAppPhone(raw) {
  if (!raw) return '';
  let digits = String(raw).replace(/@.*$/, '').replace(/\D/g, '');
  if (!digits) return '';
  if (digits.startsWith('08')) {
    digits = '62' + digits.slice(1);
  } else if (digits.startsWith('8') && digits.length >= 9 && digits.length <= 13) {
    digits = '62' + digits;
  }
  return digits;
}

function isGroupWhatsApp(raw) {
  if (!raw) return false;
  const s = String(raw);
  if (s.includes('@g.us')) return true;
  const d = s.replace(/\D/g, '');
  // Hanya grup WhatsApp (awalan 120363). Jangan batasi d.length > 16 karena akun WhatsApp LID (Linked Identity) memiliki 15-18 digit angka unik.
  return d.startsWith('120363');
}

function parseOmniTimestamp(raw) {
  if (!raw) return Date.now();
  if (typeof raw === 'number') {
    return raw < 1e11 ? raw * 1000 : raw;
  }
  const str = String(raw).trim();
  const num = Number(str);
  if (!isNaN(num) && num > 0) {
    return num < 1e11 ? num * 1000 : num;
  }
  const parsed = new Date(str).getTime();
  return (!isNaN(parsed) && parsed > 0) ? parsed : Date.now();
}

// --------------------------------------------------------------------------
// MASTER DATA PIC FU MANAGEMENT
// --------------------------------------------------------------------------
function getMasterDataPICs() {
  try {
    const raw = localStorage.getItem(OMNI_MASTER_STORAGE_KEY);
    if (raw) {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    }
  } catch (e) {}
  return seedMasterDataFromKeepRows();
}

function saveMasterDataPICs(list) {
  try {
    localStorage.setItem(OMNI_MASTER_STORAGE_KEY, JSON.stringify(list || []));
  } catch (e) {}
}

function seedMasterDataFromKeepRows() {
  const masterMap = new Map();
  if (Array.isArray(rawKeepRows) && rawKeepRows.length > 0) {
    for (let i = 0; i < rawKeepRows.length; i++) {
      const r = rawKeepRows[i];
      const waRaw = r['Nomor Follow Up'] || r['Nomor FU'] || '';
      const nameRaw = (r['Nama Tim Follow Up'] || r['Nama Tim FU'] || '').trim();
      const cabangRaw = (r['Cabang'] || '-').trim();
      const cleanWA = cleanPhoneDigits(waRaw);
      if (cleanWA && cleanWA.length >= 8 && nameRaw && nameRaw !== '-' && nameRaw !== '""') {
        if (!masterMap.has(cleanWA)) {
          masterMap.set(cleanWA, {
            wa: cleanWA,
            picName: nameRaw,
            cabang: cabangRaw || '-'
          });
        }
      }
    }
  }
  const result = Array.from(masterMap.values());
  if (result.length > 0) {
    saveMasterDataPICs(result);
  }
  return result;
}

function syncMasterDataFromKeepCSV() {
  const current = getMasterDataPICs();
  const map = new Map();
  current.forEach(item => {
    const c = cleanPhoneDigits(item.wa);
    if (c) map.set(c, item);
  });
  let added = 0;
  if (Array.isArray(rawKeepRows)) {
    rawKeepRows.forEach(r => {
      const waRaw = r['Nomor Follow Up'] || r['Nomor FU'] || '';
      const nameRaw = (r['Nama Tim Follow Up'] || r['Nama Tim FU'] || '').trim();
      const cabangRaw = (r['Cabang'] || '-').trim();
      const cleanWA = cleanPhoneDigits(waRaw);
      if (cleanWA && cleanWA.length >= 8 && nameRaw && nameRaw !== '-' && nameRaw !== '""') {
        if (!map.has(cleanWA)) {
          map.set(cleanWA, { wa: cleanWA, picName: nameRaw, cabang: cabangRaw || '-' });
          added++;
        }
      }
    });
  }
  const updated = Array.from(map.values());
  saveMasterDataPICs(updated);
  if (typeof showSaveToast === 'function') {
    showSaveToast('Berhasil sinkronisasi Master Data PIC: total ' + updated.length + ' PIC (' + added + ' baru).');
  }
  renderOmniMasterTable();
  renderOmniInboxModule();
}

function matchPicFromMasterData(waNumber, fallbackName, fallbackCabang) {
  const master = getMasterDataPICs();
  const clean = cleanPhoneDigits(waNumber);
  if (!clean) {
    return {
      wa: waNumber || '',
      picName: fallbackName || 'PIC Tidak Diketahui',
      cabang: fallbackCabang || '-',
      matched: false
    };
  }
  const last9 = clean.slice(-9);
  const found = master.find(m => {
    const mClean = cleanPhoneDigits(m.wa);
    return mClean === clean || (last9.length >= 9 && mClean.endsWith(last9));
  });
  if (found) {
    return {
      wa: found.wa,
      picName: found.picName,
      cabang: found.cabang || fallbackCabang || '-',
      matched: true
    };
  }

  // Jika merupakan WhatsApp LID atau nomor belum cocok, cocokkan berdasarkan kesamaan nama
  if (fallbackName && (clean.length > 13 || !clean.startsWith('628'))) {
    const fClean = String(fallbackName).toLowerCase().replace(/[^a-z0-9]/g, '');
    const foundByName = master.find(m => {
      if (!m.picName) return false;
      const mClean = m.picName.toLowerCase().replace(/[^a-z0-9]/g, '');
      return (mClean.length >= 4 && fClean.includes(mClean)) || (fClean.length >= 4 && mClean.includes(fClean));
    });
    if (foundByName) {
      return {
        wa: foundByName.wa,
        picName: foundByName.picName,
        cabang: foundByName.cabang || fallbackCabang || '-',
        matched: true
      };
    }
  }

  return {
    wa: waNumber,
    picName: fallbackName || ('PIC (' + clean + ')'),
    cabang: fallbackCabang || '-',
    matched: false
  };
}

function switchOmniView(view) {
  currentOmniView = view;
  const chatsWorkspace = document.getElementById('omniViewWorkspaceChats');
  const masterWorkspace = document.getElementById('omniViewWorkspaceMaster');
  const btnChats = document.getElementById('btnOmniTabChats');
  const btnMaster = document.getElementById('btnOmniTabMaster');
  if (view === 'MASTER') {
    if (chatsWorkspace) chatsWorkspace.style.display = 'none';
    if (masterWorkspace) masterWorkspace.style.display = 'flex';
    if (btnChats) btnChats.classList.remove('active');
    if (btnMaster) btnMaster.classList.add('active');
    renderOmniMasterTable();
  } else {
    if (chatsWorkspace) chatsWorkspace.style.display = 'flex';
    if (masterWorkspace) masterWorkspace.style.display = 'none';
    if (btnChats) btnChats.classList.add('active');
    if (btnMaster) btnMaster.classList.remove('active');
    renderOmniInboxModule();
  }
}

function renderOmniMasterTable() {
  const tbody = document.getElementById('omniMasterTableBody');
  if (!tbody) return;
  const qInp = document.getElementById('omniMasterSearchInput');
  const query = qInp ? qInp.value.toLowerCase().trim() : '';
  const master = getMasterDataPICs();
  const keepCountMap = {};
  if (Array.isArray(rawKeepRows)) {
    rawKeepRows.forEach(r => {
      const wa = cleanPhoneDigits(r['Nomor Follow Up'] || r['Nomor FU'] || '');
      if (wa) {
        keepCountMap[wa] = (keepCountMap[wa] || 0) + 1;
        const last9 = wa.slice(-9);
        if (last9.length >= 9) keepCountMap[last9] = (keepCountMap[last9] || 0) + 1;
      }
    });
  }
  const filtered = master.filter(item => {
    if (!query) return true;
    return (item.picName || '').toLowerCase().includes(query) ||
           (item.wa || '').includes(query) ||
           (item.cabang || '').toLowerCase().includes(query);
  });
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:30px; color:#64748b;">Tidak ada data PIC yang cocok. Klik <strong>"Sinkronkan dari Data Keep"</strong> atau <strong>"Tambah PIC Baru"</strong>.</td></tr>';
    return;
  }
  tbody.innerHTML = filtered.map((item, idx) => {
    const cleanWA = cleanPhoneDigits(item.wa);
    const last9 = cleanWA.slice(-9);
    const keepTotal = keepCountMap[cleanWA] || keepCountMap[last9] || 0;
    return '<tr>' +
      '<td style="color:#64748b; font-size:0.75rem;">' + (idx + 1) + '</td>' +
      '<td><span style="font-family:monospace; font-weight:700; color:#0f172a;">' + (item.wa || '-') + '</span></td>' +
      '<td><div style="font-weight:700; color:#0f172a;">' + escapeHtml(item.picName || '-') + '</div></td>' +
      '<td><span class="omni-badge-fu-tag">' + escapeHtml(item.cabang || '-') + '</span></td>' +
      '<td style="text-align:center;"><span style="font-weight:700; font-size:0.78rem; color:' + (keepTotal > 0 ? '#0f172a' : '#94a3b8') + ';">' + keepTotal + ' keep</span></td>' +
      '<td style="text-align:center;">' +
        '<button type="button" class="omni-action-btn" title="Edit PIC" onclick="openEditPicModal(\'' + item.wa + '\')">' +
          '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>' +
        '</button>' +
        '<button type="button" class="omni-action-btn delete" title="Hapus PIC" onclick="deletePicMaster(\'' + item.wa + '\')">' +
          '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>' +
        '</button>' +
      '</td>' +
    '</tr>';
  }).join('');
}

let editingPicWA = null;

function openAddPicModal() {
  editingPicWA = null;
  const title = document.getElementById('omniPicModalTitle');
  if (title) title.innerText = 'Tambah PIC FU Baru';
  const inpWA = document.getElementById('modalPicWA');
  const inpName = document.getElementById('modalPicName');
  const inpCabang = document.getElementById('modalPicCabang');
  if (inpWA) inpWA.value = '';
  if (inpName) inpName.value = '';
  if (inpCabang) inpCabang.value = '';
  const modal = document.getElementById('omniPicModal');
  if (modal) modal.style.display = 'flex';
}

function openEditPicModal(wa) {
  editingPicWA = wa;
  const master = getMasterDataPICs();
  const item = master.find(m => cleanPhoneDigits(m.wa) === cleanPhoneDigits(wa));
  if (!item) return;
  const title = document.getElementById('omniPicModalTitle');
  if (title) title.innerText = 'Edit Master Data PIC FU';
  const inpWA = document.getElementById('modalPicWA');
  const inpName = document.getElementById('modalPicName');
  const inpCabang = document.getElementById('modalPicCabang');
  if (inpWA) inpWA.value = item.wa;
  if (inpName) inpName.value = item.picName;
  if (inpCabang) inpCabang.value = item.cabang;
  const modal = document.getElementById('omniPicModal');
  if (modal) modal.style.display = 'flex';
}

function closePicModal() {
  const modal = document.getElementById('omniPicModal');
  if (modal) modal.style.display = 'none';
}

function savePicModal() {
  const inpWA = document.getElementById('modalPicWA');
  const inpName = document.getElementById('modalPicName');
  const inpCabang = document.getElementById('modalPicCabang');
  const wa = inpWA ? inpWA.value.trim() : '';
  const picName = inpName ? inpName.value.trim() : '';
  const cabang = inpCabang ? inpCabang.value.trim().toUpperCase() : '-';
  if (!wa || !picName) {
    alert('Nomor WhatsApp dan Nama PIC FU wajib diisi!');
    return;
  }
  const clean = cleanPhoneDigits(wa);
  let master = getMasterDataPICs();
  if (editingPicWA) {
    const editClean = cleanPhoneDigits(editingPicWA);
    master = master.map(m => cleanPhoneDigits(m.wa) === editClean ? { wa, picName, cabang } : m);
  } else {
    const exists = master.some(m => cleanPhoneDigits(m.wa) === clean);
    if (exists) {
      alert('Nomor WhatsApp ini sudah terdaftar dalam Master Data!');
      return;
    }
    master.unshift({ wa, picName, cabang });
  }
  saveMasterDataPICs(master);
  closePicModal();
  if (typeof showSaveToast === 'function') {
    showSaveToast('PIC FU ' + picName + ' berhasil disimpan.');
  }
  renderOmniMasterTable();
  renderOmniInboxModule();
}

function deletePicMaster(wa) {
  if (!confirm('Hapus PIC dengan nomor ' + wa + ' dari Master Data?')) return;
  const clean = cleanPhoneDigits(wa);
  let master = getMasterDataPICs();
  master = master.filter(m => cleanPhoneDigits(m.wa) !== clean);
  saveMasterDataPICs(master);
  renderOmniMasterTable();
  renderOmniInboxModule();
}

// --------------------------------------------------------------------------
// ROOMCHAT THREAD PERSISTENCE (100% CERMIN WHATSAPP)
// --------------------------------------------------------------------------
function getOmniThreads() {
  try {
    const raw = JSON.parse(localStorage.getItem('gadgetmart_omni_threads') || '{}');
    const cleaned = {};
    let modified = false;
    Object.keys(raw).forEach(function(k) {
      // Hapus data grup WhatsApp agar tidak mengotori chat inbox
      if (isGroupWhatsApp(k)) {
        modified = true;
        return;
      }
      let targetKey = normalizeWhatsAppPhone(k);
      if (!targetKey || targetKey.length < 8) {
        if (k.toLowerCase().includes('yahya')) {
          targetKey = '6289603864476';
          modified = true;
        } else {
          return;
        }
      }
      if (targetKey !== k) modified = true;

      if (!cleaned[targetKey]) cleaned[targetKey] = [];
      (raw[k] || []).forEach(function(msg) {
        const msgTs = Number(msg.timestamp || 0);
        const msgText = (msg.text || msg.message || '').trim();
        const exists = cleaned[targetKey].some(function(m) {
          return (m.id && msg.id && m.id === msg.id) ||
            (Number(m.timestamp || 0) === msgTs && (m.text || '').trim() === msgText);
        });
        if (!exists) cleaned[targetKey].push(msg);
      });
      cleaned[targetKey].sort(function(a, b) {
        return (Number(a.timestamp || 0)) - (Number(b.timestamp || 0));
      });
    });

    if (modified) {
      localStorage.setItem('gadgetmart_omni_threads', JSON.stringify(cleaned));

    // Sinkronisasi realtime ke Live Chat Widget di pojok kanan bawah
    if (typeof window.renderDcmsLwChatsList === 'function') {
      try { window.renderDcmsLwChatsList(); } catch (e) {}
    } else if (typeof renderChatsList === 'function') {
      try { renderChatsList(); } catch (e) {}
    }
    if (typeof window.updateDcmsLwUnreadBadge === 'function') {
      try { window.updateDcmsLwUnreadBadge(); } catch (e) {}
    } else if (typeof updateUnreadBadge === 'function') {
      try { updateUnreadBadge(); } catch (e) {}
    }
    if (typeof window.renderDcmsLwActiveRoomMessages === 'function') {
      try { window.renderDcmsLwActiveRoomMessages(); } catch (e) {}
    } else if (typeof renderActiveRoomMessages === 'function') {
      try { renderActiveRoomMessages(); } catch (e) {}
    }
    }
    return cleaned;
  } catch (e) {
    return {};
  }
}

function saveOmniThreads(threads) {
  try {
    const cleaned = {};
    Object.keys(threads || {}).forEach(function(k) {
      if (isGroupWhatsApp(k)) return;
      let targetKey = normalizeWhatsAppPhone(k);
      if (!targetKey || targetKey.length < 8) {
        if (k.toLowerCase().includes('yahya')) targetKey = '6289603864476';
        else return;
      }
      cleaned[targetKey] = (threads[k] || []).map(function(m) {
        if (m.mediaUrl && m.mediaUrl.length > 500 && m.mediaUrl.startsWith('data:')) {
          const copy = Object.assign({}, m);
          copy.mediaUrl = '';
          return copy;
        }
        return m;
      });
    });
    localStorage.setItem('gadgetmart_omni_threads', JSON.stringify(cleaned));

    // Sinkronisasi realtime ke Live Chat Widget di pojok kanan bawah
    if (typeof window.renderDcmsLwChatsList === 'function') {
      try { window.renderDcmsLwChatsList(); } catch (e) {}
    } else if (typeof renderChatsList === 'function') {
      try { renderChatsList(); } catch (e) {}
    }
    if (typeof window.updateDcmsLwUnreadBadge === 'function') {
      try { window.updateDcmsLwUnreadBadge(); } catch (e) {}
    } else if (typeof updateUnreadBadge === 'function') {
      try { updateUnreadBadge(); } catch (e) {}
    }
    if (typeof window.renderDcmsLwActiveRoomMessages === 'function') {
      try { window.renderDcmsLwActiveRoomMessages(); } catch (e) {}
    } else if (typeof renderActiveRoomMessages === 'function') {
      try { renderActiveRoomMessages(); } catch (e) {}
    }
  } catch (e) {
    console.warn('saveOmniThreads warning:', e);
  }
}

function setOmniFolder(folder) {
  currentOmniFolder = folder || 'ALL';
  ['All', 'Assigned', 'Unresponded', 'Resolved'].forEach(function(f) {
    const el = document.getElementById('omniFilter' + f);
    if (el) el.classList.remove('active');
  });
  const map = {
    'ALL': 'omniFilterAll',
    'ASSIGNED': 'omniFilterAssigned',
    'UNRESPONDED': 'omniFilterUnresponded',
    'RESOLVED': 'omniFilterResolved'
  };
  const activeEl = document.getElementById(map[currentOmniFolder]);
  if (activeEl) activeEl.classList.add('active');
  const titleEl = document.getElementById('omniChatListHeaderTitle');
  if (titleEl) {
    const titleMap = {
      'ALL': 'All chats',
      'ASSIGNED': 'Assigned chats',
      'UNRESPONDED': 'Unresponded',
      'RESOLVED': 'Resolved'
    };
    titleEl.innerText = titleMap[currentOmniFolder] || 'All chats';
  }
  renderOmniInboxModule();
}

function setOmniKeepSubTab(subTab) {
  selectedOmniKeepSubTab = subTab;
  const allPics = getOmniPicChats();
  const currentPic = allPics.find(function(p) { return p.key === selectedOmniPicKey; });
  if (currentPic) {
    renderOmniKeepPanel(currentPic);
  }
}

function filterOmniChats() {
  renderOmniInboxModule();
}

function isEligibleKeep(ts) {
  if (!ts) return false;
  const firstSpace = ts.indexOf(' ');
  const datePart = firstSpace > 0 ? ts.slice(0, firstSpace) : ts;
  const parts = datePart.split('/');
  if (parts.length !== 3) return false;

  const m = parseInt(parts[0], 10);
  const d = parseInt(parts[1], 10);
  const y = parseInt(parts[2], 10);
  if (isNaN(m) || isNaN(d) || isNaN(y)) return false;

  const keepMidnight = new Date(y, m - 1, d, 0, 0, 0, 0).getTime();
  const now = new Date();
  // 2 HARI TERAKHIR: Mulai dari kemarin jam 00:00:00 hingga hari ini
  const yesterdayMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1, 0, 0, 0, 0).getTime();
  return keepMidnight >= yesterdayMidnight;
}

window.omniResolvedDbMap = null;

function getOmniResolvedChats() {
  if (window.omniResolvedDbMap && typeof window.omniResolvedDbMap === 'object') {
    return window.omniResolvedDbMap;
  }
  try {
    const local = JSON.parse(localStorage.getItem('omni_resolved_chats_v1') || '{}');
    window.omniResolvedDbMap = local;
    return local;
  } catch (e) {
    return {};
  }
}

function setOmniChatResolved(key, isResolved) {
  const normKey = normalizeWhatsAppPhone(key) || key;
  const map = getOmniResolvedChats();
  if (isResolved) {
    map[normKey] = true;
  } else {
    delete map[normKey];
  }
  window.omniResolvedDbMap = map;
  try {
    localStorage.setItem('omni_resolved_chats_v1', JSON.stringify(map));
  } catch (e) {}

  // SINKRONISASI PERMANEN KE NEON POSTGRESQL (Tidak akan hilang saat redeploy / beda device)
  try {
    fetch('/api/omni-resolve', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        phone: normKey,
        isResolved: Boolean(isResolved),
        resolvedBy: 'Admin'
      })
    }).catch(function(err) {
      console.warn('Gagal sinkron status resolve ke PostgreSQL:', err);
    });
  } catch (e) {}
}

async function fetchOmniResolvedStatuses() {
  try {
    const res = await fetch('/api/omni-resolve');
    if (!res.ok) return;
    const json = await res.json();
    if (json && json.success && json.resolvedChats) {
      const dbMap = {};
      Object.keys(json.resolvedChats).forEach(function(phone) {
        if (json.resolvedChats[phone] && json.resolvedChats[phone].isResolved) {
          const norm = normalizeWhatsAppPhone(phone) || phone;
          dbMap[norm] = true;
        }
      });
      window.omniResolvedDbMap = Object.assign({}, getOmniResolvedChats(), dbMap);
      try {
        localStorage.setItem('omni_resolved_chats_v1', JSON.stringify(window.omniResolvedDbMap));
      } catch (e) {}
      if (typeof renderOmniInboxSidebar === 'function') {
        renderOmniInboxSidebar();
      }
    }
  } catch (err) {
    console.warn('Gagal memuat status resolve dari Neon DB:', err);
  }
}

// --------------------------------------------------------------------------
// CACHE & MEMOIZATION UNTUK KECEPATAN EKSTREM (0.1ms vs 540ms)
// --------------------------------------------------------------------------
let cachedOmniKeepsMap = null;
let lastIndexedKeepRowsLength = -1;
window.omni2DayKeepsMap = {};

function getIndexedOmniKeeps() {
  if (!Array.isArray(rawKeepRows)) return {};
  if (cachedOmniKeepsMap && lastIndexedKeepRowsLength === rawKeepRows.length) {
    return cachedOmniKeepsMap;
  }

  const picKeepsMap = {};
  const master = getMasterDataPICs();
  const masterMap = {};
  master.forEach(function(m) {
    const c = normalizeWhatsAppPhone(m.wa);
    if (c) masterMap[c] = m;
  });

  // Iterasi baris keep-data (hanya data 2 hari terakhir: kemarin & hari ini)
  for (let i = rawKeepRows.length - 1; i >= 0; i--) {
    const r = rawKeepRows[i];
    const ts = r['Timestamp'];
    if (!ts || !isEligibleKeep(ts)) continue;

    const customerWA = normalizeWhatsAppPhone(r['Nomor WA'] || '');
    if (!customerWA || customerWA.length < 8) continue;

    const rawPicWA = normalizeWhatsAppPhone(r['Nomor Follow Up'] || r['Nomor FU'] || '');
    const rawPicName = (r['Nama Tim Follow Up'] || r['Nama Tim FU'] || '').trim();
    const rawCabang = (r['Cabang'] || '-').trim();
    const sender = (r['Sender'] || '-').trim();

    let matched = masterMap[rawPicWA] || masterMap[customerWA];
    if (!matched) {
      const last9 = (rawPicWA || customerWA).slice(-9);
      if (last9 && last9.length >= 9) {
        matched = master.find(function(m) {
          const mc = normalizeWhatsAppPhone(m.wa);
          return mc && mc.endsWith(last9);
        });
      }
    }

    const picWA = (matched ? matched.wa : '') || rawPicWA || customerWA;
    const picName = matched ? matched.picName : (rawPicName || ('PIC ' + (rawPicWA || customerWA)));
    const cabang = (matched ? matched.cabang : '') || rawCabang;
    
    // STANDARISASI: Key percakapan SELALU menggunakan nomor HP E.164 (628...)
    const picKey = normalizeWhatsAppPhone(picWA) || picWA;

    // Abaikan jika terdeteksi ID grup
    if (isGroupWhatsApp(picKey)) continue;

    const customerName = (r['Nama'] || 'Customer').trim();
    const barang = (r['Barang Keep'] || '-').trim();
    const catatan = (r['Catatan'] || '-').trim();
    const status = (r['Status'] || 'BELUM DIINPUT').trim() || 'BELUM DIINPUT';
    const feedback = (r['Feedback Tim FU'] || '').trim();
    const remarks = (r['Remarks'] || r['Kode Sosmed'] || sender || '-').trim();
    const reportAR = (r['REPORT AR'] || r['Report AR'] || r['AR'] || '').trim();
    const isPending = (!feedback || feedback === '-' || feedback === '""') || status === 'BELUM DIINPUT' || !status;

    const keepItem = {
      customerWA: customerWA,
      customerName: customerName,
      barang: barang,
      catatan: catatan,
      cabang: cabang,
      sender: sender,
      remarks: remarks,
      reportAR: reportAR,
      status: status,
      feedback: feedback,
      timestamp: ts,
      isPending: isPending,
      rawRow: r
    };

    if (!picKeepsMap[picKey]) picKeepsMap[picKey] = [];
    picKeepsMap[picKey].push(keepItem);

    if (picWA && picWA !== picKey) {
      if (!picKeepsMap[picWA]) picKeepsMap[picWA] = picKeepsMap[picKey];
      const last9 = picWA.slice(-9);
      if (last9.length >= 9 && !picKeepsMap[last9]) picKeepsMap[last9] = picKeepsMap[picKey];
    }
  }

  cachedOmniKeepsMap = picKeepsMap;
  window.omni2DayKeepsMap = picKeepsMap;
  lastIndexedKeepRowsLength = rawKeepRows.length;
  return cachedOmniKeepsMap;
}

function getOmniPicChats() {
  const threads = getOmniThreads();
  // Filter ketat: HANYA chat personal (termasuk WhatsApp LID), TOLAK grup WhatsApp
  const activeKeys = Object.keys(threads).filter(function(k) {
    return !k.includes('@g.us') && !k.startsWith('120363') && Array.isArray(threads[k]) && threads[k].length > 0;
  });

  if (activeKeys.length === 0) return [];

  const picKeepsMap = getIndexedOmniKeeps();
  const resolvedMap = getOmniResolvedChats();

  const result = [];
  activeKeys.forEach(function(key) {
    const cleanKey = normalizeWhatsAppPhone(key) || key;
    const chatMsgs = threads[key] || [];
    const lastMsg = chatMsgs[chatMsgs.length - 1];

    const matched = matchPicFromMasterData(cleanKey, 'PIC ' + cleanKey, '-');
    const picWA = matched.wa || cleanKey;
    const picName = matched.picName;
    const cabang = matched.cabang || '-';

    const last9 = picWA.slice(-9);
    const keeps = picKeepsMap[cleanKey] || picKeepsMap[picWA] || (last9.length >= 9 ? picKeepsMap[last9] : []) || [];
    const pendingCount = keeps.filter(function(k) { return k.isPending; }).length;
    const resolvedCount = keeps.filter(function(k) { return !k.isPending; }).length;

    let latestTimestamp = '';
    let latestEpochMs = 0;
    if (lastMsg && lastMsg.timestamp) {
      latestEpochMs = parseOmniTimestamp(lastMsg.timestamp);
      const d = new Date(latestEpochMs);
      latestTimestamp = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    const isResolved = !!resolvedMap[cleanKey] || !!resolvedMap[picWA];
    const lastSender = lastMsg ? (lastMsg.sender || 'PIC') : '';
    const isUnreplied = lastSender !== 'ADMIN';
    const isUnresponded = !isResolved && (isUnreplied || pendingCount > 0);

    result.push({
      key: cleanKey,
      picName: picName,
      picWA: picWA,
      cabang: cabang,
      keeps: keeps,
      pendingCount: pendingCount,
      resolvedCount: resolvedCount,
      isResolved: isResolved,
      isUnresponded: isUnresponded,
      lastSender: lastSender,
      latestEpochMs: latestEpochMs,
      latestTimestamp: latestTimestamp,
      latestSnippet: lastMsg ? (lastMsg.sender === 'ADMIN' ? 'Anda: ' + lastMsg.text : lastMsg.text) : 'Percakapan baru'
    });
  });

  return result;
}

function renderOmniInboxModule() {
  fetchOmniResolvedStatuses();
  startWatzapPolling();
  const allChats = getOmniPicChats();

  // Auto-sinkronisasi awal dari WAHA VPS jika data lokal masih kosong
  if (!window._hasAutoSyncedWaha && allChats.length === 0) {
    window._hasAutoSyncedWaha = true;
    setTimeout(function() {
      syncOmniChatsFromWhatsApp(false);
    }, 50);
  }
  const bAll = document.getElementById('omniBadgeAll');
  if (bAll) bAll.innerText = allChats.length;
  const bAssigned = document.getElementById('omniBadgeAssigned');
  const bUnresponded = document.getElementById('omniBadgeUnresponded');
  const bResolved = document.getElementById('omniBadgeResolved');

  const unrespCount = allChats.filter(function(p) { return p.isUnresponded && !p.isResolved; }).length;
  const assignedCount = allChats.filter(function(p) { return !p.isResolved; }).length;
  const resCount = allChats.filter(function(p) { return p.isResolved; }).length;

  if (bAssigned) bAssigned.innerText = assignedCount;
  if (bUnresponded) bUnresponded.innerText = unrespCount;
  if (bResolved) bResolved.innerText = resCount;

  let filtered = allChats;
  if (currentOmniFolder === 'ASSIGNED') {
    filtered = allChats.filter(function(p) { return !p.isResolved; });
  } else if (currentOmniFolder === 'UNRESPONDED') {
    filtered = allChats.filter(function(p) { return p.isUnresponded && !p.isResolved; });
  } else if (currentOmniFolder === 'RESOLVED') {
    filtered = allChats.filter(function(p) { return p.isResolved; });
  }

  // POPULATE DROPDOWN FILTER CABANG (Sesuai Permintaan)
  const branchSelect = document.getElementById('omniBranchFilterSelect');
  if (branchSelect) {
    const existingVal = branchSelect.value || 'ALL';
    const branchesSet = new Set();
    allChats.forEach(function(c) {
      if (c.cabang && c.cabang !== '-' && c.cabang !== 'undefined') {
        branchesSet.add(c.cabang.toUpperCase().trim());
      }
    });
    const sortedBranches = Array.from(branchesSet).sort();
    let branchOptionsMarkup = '<option value="ALL">🏢 Semua Cabang (' + sortedBranches.length + ' Cabang)</option>';
    sortedBranches.forEach(function(b) {
      branchOptionsMarkup += '<option value="' + b + '" ' + (b === existingVal ? 'selected' : '') + '>' + b + '</option>';
    });
    branchSelect.innerHTML = branchOptionsMarkup;

    // Filter berdasarkan cabang yang dipilih
    const curBranch = branchSelect.value;
    if (curBranch && curBranch !== 'ALL') {
      filtered = filtered.filter(function(p) {
        return (p.cabang || '').toUpperCase().trim() === curBranch;
      });
    }
  }

  const searchInp = document.getElementById('omniSearchInput');
  const query = (searchInp && searchInp.value) ? String(searchInp.value).toLowerCase().trim() : '';
  if (query) {
    filtered = filtered.filter(function(pic) {
      const matchName = String(pic.picName || '').toLowerCase().includes(query);
      const matchWA = String(pic.picWA || '').includes(query);
      const matchCabang = String(pic.cabang || '').toLowerCase().includes(query);
      const matchKeep = (pic.keeps || []).some(function(k) {
        return String(k.customerName || '').toLowerCase().includes(query) ||
          String(k.barang || '').toLowerCase().includes(query) ||
          String(k.customerWA || '').includes(query) ||
          (k.feedback && String(k.feedback).toLowerCase().includes(query));
      });
      return matchName || matchWA || matchCabang || matchKeep;
    });
  }

  const countEl = document.getElementById('omniChatListTotalCount');
  if (countEl) countEl.innerText = filtered.length + ' chat';

  const listContainer = document.getElementById('omniChatListContainer');
  if (!listContainer) return;

  if (filtered.length === 0) {
    listContainer.innerHTML = '<div style="padding: 36px 18px; text-align: center; color: #64748b; font-size: 0.78rem; line-height: 1.5;">Tidak ada percakapan pada folder ini.<br><span style="font-size:0.72rem; color:#94a3b8;">Chat dari PIC FU WhatsApp akan otomatis masuk ke sini tanpa grup.</span></div>';
    const chatView = document.getElementById('omniChatViewContainer');
    const detailsView = document.getElementById('omniDetailsPanelContainer');
    if (chatView && !selectedOmniPicKey) {
      chatView.innerHTML = renderOmniEmptyState();
    }
    if (detailsView && !selectedOmniPicKey) {
      detailsView.innerHTML = '';
    }
    return;
  }

  // URUTAN WHATSAPP REALTIME: Chat terbaru SELALU berada di urutan paling atas (#1)!
  filtered.sort(function(a, b) {
    return (b.latestEpochMs || 0) - (a.latestEpochMs || 0);
  });

  listContainer.innerHTML = filtered.map(function(pic) {
    const isSelected = pic.key === selectedOmniPicKey;
    const botAvatar = generateBotAvatarSvg(pic.picWA || pic.key, 36);
    let timeDisplay = '';
    if (pic.latestTimestamp) {
      const parts = pic.latestTimestamp.split(' ');
      timeDisplay = parts[1] ? parts[1].slice(0, 5) : parts[0];
    }

    return '<div class="omni-chat-card ' + (isSelected ? 'active' : '') + '" onclick="selectOmniPic(\'' + pic.key + '\')">' +
      '<div class="omni-card-avatar-wrap">' +
        '<div class="omni-avatar-circle">' + botAvatar + '</div>' +
        '<span class="omni-badge-wa-dot"></span>' +
      '</div>' +
      '<div class="omni-card-body">' +
        '<div class="omni-card-top-row">' +
          '<span class="omni-card-name" title="' + escapeHtml(pic.picName) + '">' + escapeHtml(pic.picName) + '</span>' +
          '<span class="omni-card-time">' + timeDisplay + '</span>' +
        '</div>' +
        '<div class="omni-card-snippet" title="' + escapeHtml(pic.latestSnippet) + '">' + escapeHtml(pic.latestSnippet) + '</div>' +
        '<div class="omni-card-bottom-row">' +
          '<span class="omni-channel-tag">' + escapeHtml(pic.cabang) + ' &middot; WhatsApp Official</span>' +
          '<div class="omni-card-badges-right">' +
            (pic.pendingCount > 0 ? '<span class="omni-red-badge" title="' + pic.pendingCount + ' format keep belum ada feedback">' + pic.pendingCount + '</span>' : '') +
            '<span class="omni-status-tag-assigned" style="color: ' + (pic.isResolved ? '#059669' : '#b91c1c') + '; font-weight:700;">' +
              (pic.isResolved ? 'Resolved' : 'Pending') +
            '</span>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }).join('');

  if (selectedOmniPicKey) {
    const isSelectedInFiltered = filtered.some(function(p) {
      return p.key === selectedOmniPicKey || normalizeWhatsAppPhone(p.picWA) === selectedOmniPicKey || (p.key && p.key.endsWith(selectedOmniPicKey.slice(-9)));
    });
    if (isSelectedInFiltered) {
      const currentPic = filtered.find(function(p) {
        return p.key === selectedOmniPicKey || normalizeWhatsAppPhone(p.picWA) === selectedOmniPicKey || (p.key && p.key.endsWith(selectedOmniPicKey.slice(-9)));
      });
      renderOmniActiveChat(currentPic);
      renderOmniKeepPanel(currentPic);
    } else if (filtered.length > 0) {
      selectOmniPic(filtered[0].key);
    } else {
      selectedOmniPicKey = null;
      const chatView = document.getElementById('omniChatViewContainer');
      const detailsView = document.getElementById('omniDetailsPanelContainer');
      if (chatView) chatView.innerHTML = renderOmniEmptyState();
      if (detailsView) detailsView.innerHTML = '';
    }
  } else if (filtered.length > 0) {
    selectOmniPic(filtered[0].key);
  }
}

function selectOmniPic(key) {
  const normKey = normalizeWhatsAppPhone(key) || key;
  selectedOmniPicKey = normKey;
  const allPics = getOmniPicChats();
  let pic = allPics.find(function(p) {
    return p.key === normKey || normalizeWhatsAppPhone(p.picWA) === normKey || (p.key && p.key.endsWith(normKey.slice(-9)));
  });
  
  if (!pic) {
    const matched = matchPicFromMasterData(normKey, 'PIC ' + normKey, '-');
    const keepsMap = getIndexedOmniKeeps();
    const last9 = normKey.slice(-9);
    const keeps = keepsMap[normKey] || (last9.length >= 9 ? keepsMap[last9] : []) || [];
    pic = {
      key: normKey,
      picName: matched.picName,
      picWA: normKey,
      cabang: matched.cabang || '-',
      keeps: keeps,
      pendingCount: keeps.filter(function(k) { return k.isPending; }).length,
      resolvedCount: keeps.filter(function(k) { return !k.isPending; }).length,
      isResolved: false,
      isUnresponded: true
    };
  }

  // Highlight kartu aktif secara instan (0ms)
  const cards = document.querySelectorAll('.omni-chat-card');
  cards.forEach(function(c) {
    const oc = c.getAttribute('onclick') || '';
    if (oc.includes(normKey)) {
      c.classList.add('active');
    } else {
      c.classList.remove('active');
    }
  });

  renderOmniActiveChat(pic);
  renderOmniKeepPanel(pic);
}

function generateWhatsAppBotMessage(k, picName) {
  if (!k) return '';
  return 'halo kak ' + (picName || 'PIC') + ' mimin mau follow-up update progress dari format keep berikut ya^^\n\n' +
    'tanggal keep: ' + (k.timestamp || '-') + '\n' +
    'nama customer: ' + (k.customerName || '-') + '\n' +
    'nomor whatsapp: ' + (k.customerWA || '-') + '\n' +
    'barang: ' + (k.barang || '-') + '\n' +
    'catatan: ' + (k.catatan || '-') + '\n' +
    'remarks: ' + (k.remarks || '-') + '\n\n' +
    'silakan bantu screenshot chatnya + infoin ke kami sudah sampai mana progressnya ya ^^ jangan lupa follow up kembali customernya jika ghosting agar peluang closing meningkat ya 👍';
}

function formatOmniDateDivider(timestamp) {
  const pad = (n) => String(n).padStart(2, '0');
  const now = new Date();
  const defaultToday = `Today, ${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()}`;
  if (!timestamp) return defaultToday;

  const msgDate = new Date(Number(timestamp) || timestamp);
  if (isNaN(msgDate.getTime())) return defaultToday;

  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);

  const dateStr = `${pad(msgDate.getDate())}/${pad(msgDate.getMonth() + 1)}/${msgDate.getFullYear()}`;

  const isToday = msgDate.getFullYear() === today.getFullYear() &&
                  msgDate.getMonth() === today.getMonth() &&
                  msgDate.getDate() === today.getDate();
  if (isToday) {
    return `Today, ${dateStr}`;
  }

  const isYesterday = msgDate.getFullYear() === yesterday.getFullYear() &&
                      msgDate.getMonth() === yesterday.getMonth() &&
                      msgDate.getDate() === yesterday.getDate();
  if (isYesterday) {
    return `Kemarin, ${dateStr}`;
  }

  return dateStr;
}

function renderOmniActiveChat(pic) {
  const container = document.getElementById('omniChatViewContainer');
  if (!container) return;

  const initial = (pic.picName || 'P').charAt(0).toUpperCase();
  const cleanWA = pic.picWA;
  const threads = getOmniThreads();
  const customMessages = threads[pic.key] || threads[cleanWA] || threads[normalizeWhatsAppPhone(cleanWA)] || [];

  // Sort messages chronologically
  const sortedMessages = (customMessages || []).slice().sort(function(a, b) {
    return (Number(a.timestamp) || 0) - (Number(b.timestamp) || 0);
  });
  window.currentOmniChatMessages = sortedMessages;

  let lastDateGroup = null;
  const messagesRendered = [];

  sortedMessages.forEach(function(msg, idx) {
    const msgTs = msg.timestamp ? Number(msg.timestamp) : Date.now();
    const d = new Date(msgTs);
    const dateKey = isNaN(d.getTime()) ? 'today' : `${d.getFullYear()}-${d.getMonth() + 1}-${d.getDate()}`;
    if (dateKey !== lastDateGroup) {
      lastDateGroup = dateKey;
      const dateLabel = formatOmniDateDivider(msgTs);
      messagesRendered.push(
        '<div class="omni-date-divider">' +
          '<span class="omni-date-pill">' + escapeHtml(dateLabel) + '</span>' +
        '</div>'
      );
    }

    if (msg.sender === 'SYSTEM') {
      messagesRendered.push(
        '<div style="display:flex; justify-content:center; margin: 10px 0;">' +
          '<span style="background: #f1f5f9; border: 1px solid #e2e8f0; color: #475569; font-size: 0.70rem; font-weight: 600; padding: 4px 12px; border-radius: 12px; text-align: center;">' +
            escapeHtml(msg.text) +
          '</span>' +
        '</div>'
      );
      return;
    }

    const isOutgoing = msg.sender === 'ADMIN';
    const timeStr = !isNaN(d.getTime()) ? d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
    const tickMarkup = isOutgoing ? (
      '<span class="omni-msg-status-tick" id="tick_' + (msg.id || '') + '">' +
        (msg.status === 'sending'
          ? '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="display:inline; vertical-align:middle; margin-left:3px;"><polyline points="20 6 9 17 4 12"/></svg>'
          : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#53bdeb" stroke-width="2" style="display:inline; vertical-align:middle; margin-left:3px;"><polyline points="20 6 9 17 4 12"/><polyline points="20 12 13 19"/></svg>') +
      '</span>'
    ) : '';

    let replyToObj = msg.replyTo || null;
    let displayText = msg.text || '';
    if (displayText.startsWith('>')) {
      const parsed = parseQuotedFromText(displayText);
      if (parsed.cleanText) {
        displayText = parsed.cleanText;
        if (!replyToObj && parsed.replyTo) {
          replyToObj = parsed.replyTo;
        }
      }
    }

    const msgId = String(msg.id || msg.messageId || ('msg_' + (msg.timestamp || Math.random().toString(36).slice(2))));
    const safeMsgId = escapeHtml(msgId).replace(/'/g, "\\'");

    const quotedBlock = replyToObj ? (
      '<div class="omni-bubble-reply-block" onclick="handleOmniQuotedClick(' + idx + ')" title="Klik untuk melompat ke pesan yang direply">' +
        '<div class="omni-reply-block-sender">' + escapeHtml(replyToObj.senderName || 'Balasan') + '</div>' +
        '<div class="omni-reply-block-text">' + escapeHtml(replyToObj.text || '') + '</div>' +
      '</div>'
    ) : '';

    const mediaRawUrl = msg.mediaUrl || msg.image || '';
    const isDoc = (msg.mediaType === 'document' || msg.messageType === 'document' || (mediaRawUrl && !mediaRawUrl.match(/\.(jpe?g|png|webp|gif)/i) && (msg.filename || mediaRawUrl.includes('.txt') || mediaRawUrl.includes('.pdf') || mediaRawUrl.includes('.doc') || mediaRawUrl.includes('.xls'))));
    const hasImage = Boolean(mediaRawUrl && !isDoc);
    let mediaMarkup = '';
    if (hasImage) {
      mediaMarkup = (
        '<div class="omni-bubble-media" onclick="openOmniImageModal(\'' + escapeHtml(mediaRawUrl) + '\')">' +
          '<img src="' + escapeHtml(mediaRawUrl) + '" alt="WhatsApp Image" class="omni-chat-image" loading="lazy">' +
        '</div>'
      );
    } else if (isDoc) {
      const docName = msg.filename || 'Dokumen WhatsApp';
      mediaMarkup = (
        '<div class="omni-bubble-document" style="display:flex; align-items:center; gap:8px; padding:7px 10px; background:rgba(0,0,0,0.05); border:1px solid rgba(0,0,0,0.06); border-radius:6px; margin:4px 0; max-width:260px;">' +
          '<span style="font-size:1.4rem; line-height:1;">📄</span>' +
          '<div style="flex:1; min-width:0;">' +
            '<div style="font-weight:600; font-size:0.75rem; color:#0f172a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="' + escapeHtml(docName) + '">' + escapeHtml(docName) + '</div>' +
            '<div style="font-size:0.65rem; color:#64748b;">Dokumen WhatsApp</div>' +
          '</div>' +
          '<a href="' + escapeHtml(mediaRawUrl) + '" target="_blank" download="' + escapeHtml(docName) + '" style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:6px; background:#0284c7; color:#fff; text-decoration:none;" title="Unduh / Buka Dokumen">' +
            '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>' +
          '</a>' +
        '</div>'
      );
    } else if (msg.messageType === 'image') {
      mediaMarkup = (
        '<div class="omni-bubble-media-placeholder">' +
          '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>' +
          '<span>📷 Foto / Gambar WhatsApp</span>' +
        '</div>'
      );
    }

    const senderDisplayName = isOutgoing ? 'Admin (Anda)' : escapeHtml(pic.picName || 'PIC');
    const safeText = escapeHtml(displayText || msg.text || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    const safeSender = escapeHtml(senderDisplayName).replace(/'/g, "\\'");
    const replyBtn = '<button type="button" class="omni-btn-bubble-reply" onclick="startOmniQuotedReply(\'' + safeMsgId + '\', \'' + safeText + '\', \'' + safeSender + '\')" title="Balas pesan ini"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg></button>';

    let cleanTextRender = displayText;
    if (hasImage && (cleanTextRender === '[Foto/Gambar]' || cleanTextRender === '[Pesan Masuk]' || cleanTextRender === '[Image]')) {
      cleanTextRender = '';
    }
    const textHtml = cleanTextRender ? ('<div style="white-space:pre-wrap; word-break:break-word;' + (hasImage ? ' margin-top:6px;' : '') + '">' + escapeHtml(cleanTextRender) + '</div>') : '';

    messagesRendered.push(
      '<div class="omni-msg-row ' + (isOutgoing ? 'omni-msg-outgoing' : 'omni-msg-incoming') + '" id="omniMsg_' + safeMsgId + '" data-msg-id="' + safeMsgId + '">' +
        replyBtn +
        '<div class="omni-bubble ' + (isOutgoing ? 'omni-bubble-outgoing' : 'omni-bubble-incoming') + '">' +
          '<div class="omni-bubble-sender">' + senderDisplayName + '</div>' +
          quotedBlock +
          mediaMarkup +
          textHtml +
          '<span class="omni-bubble-time">' + timeStr + tickMarkup + '</span>' +
        '</div>' +
      '</div>'
    );
  });

  if (messagesRendered.length === 0) {
    messagesRendered.push(
      '<div class="omni-date-divider">' +
        '<span class="omni-date-pill">' + formatOmniDateDivider(Date.now()) + '</span>' +
      '</div>' +
      '<div style="text-align:center; padding: 40px 16px; color:#94a3b8; font-size:0.75rem;">' +
        'Belum ada pesan percakapan dengan kontak ini.' +
      '</div>'
    );
  }

  container.innerHTML = '<div class="omni-chatview-header">' +
    '<div class="omni-header-contact">' +
      '<div class="omni-header-avatar" style="background:transparent; border:none; padding:0;">' +
        generateBotAvatarSvg(pic.picWA || pic.key, 38) +
        '<span class="omni-badge-wa-dot"></span>' +
      '</div>' +
      '<div class="omni-header-info">' +
        '<div class="omni-header-name">' +
          '<span>' + escapeHtml(pic.picName) + '</span>' +
          '<span class="omni-badge-fu-tag">' + escapeHtml(pic.cabang) + '</span>' +
        '</div>' +
        '<div class="omni-header-meta">' +
          '<span class="omni-dot-online"></span>' +
          '<span>WhatsApp Official (' + cleanWA + ')</span>' +
        '</div>' +
      '</div>' +
    '</div>' +
    '<div class="omni-header-actions" style="display: flex; align-items: center; gap: 8px;">' +
      '<button type="button" class="omni-btn-resolve ' + (pic.isResolved ? 'is-resolved' : '') + '" onclick="handleOmniResolveClick(\'' + pic.key + '\')" title="Selesaikan chat jika semua format keep tgl 3 ke atas sudah diisi">' +
        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>' +
        '<span>' + (pic.isResolved ? '✓ Resolved' : 'Resolve Chat') + '</span>' +
      '</button>' +
    '</div>' +
  '</div>' +
  '<div class="omni-messages-stream" id="omniMessagesStream">' +
    messagesRendered.join('') +
  '</div>' +
  '<button type="button" id="omniNewMessagePill" class="omni-new-msg-pill" style="display:none;" onclick="scrollOmniStreamToBottom()">' +
    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>' +
    '<span>Pesan Baru</span>' +
  '</button>' +
  '<div class="omni-chatview-footer-modern">' +
    '<div id="omniQuotedReplyBar" class="omni-quoted-reply-bar" style="display:none;">' +
      '<div class="omni-quoted-bar-left">' +
        '<div class="omni-quoted-reply-title">Membalas ke <span id="omniQuotedSenderName"></span></div>' +
        '<div class="omni-quoted-reply-snippet" id="omniQuotedSnippet"></div>' +
      '</div>' +
      '<button type="button" class="omni-quoted-close-btn" onclick="cancelOmniQuotedReply()">&times;</button>' +
    '</div>' +
    '<div class="omni-input-box-wrapper">' +
      '<textarea id="omniReplyInput" class="omni-textarea-modern" rows="2" placeholder="Ketik pesan balasan... (Tekan Enter untuk kirim, Shift + Enter untuk baris baru)" onkeydown="handleOmniInputKey(event)" oninput="handleOmniInputTyping(this)"></textarea>' +
    '</div>' +
    '<div id="omniAttachmentPreview" class="omni-attachment-preview-bar" style="display:none; align-items:center; gap:8px; padding:6px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; margin-bottom:8px; font-size:0.75rem;">' +
      '<span id="omniAttachmentIcon" style="font-size:1.1rem;">📎</span>' +
      '<span id="omniAttachmentName" class="omni-attachment-filename" style="font-weight:600; color:#1e293b; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>' +
      '<button type="button" class="omni-attachment-remove-btn" onclick="removeOmniAttachment()" style="border:none; background:transparent; cursor:pointer; color:#ef4444; font-weight:700; font-size:1.1rem; line-height:1;" title="Hapus lampiran">&times;</button>' +
    '</div>' +
    '<div class="omni-input-bottom-bar">' +
      '<div class="omni-bottom-left-actions">' +
        '<button type="button" class="omni-btn-attachment-modern" title="Kirim Foto / Dokumen / File" onclick="handleOmniAttachmentClick()">' +
          '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>' +
        '</button>' +
        '<input type="file" id="omniFileInput" style="display:none;" onchange="handleOmniFileSelected(this)" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">' +
      '</div>' +
      '<div class="omni-bottom-right-actions">' +
        '<button type="button" id="omniBtnSend" class="omni-btn-send-modern" onclick="sendOmniReply()" title="Kirim Balasan (Enter)">' +
          '<span>Send</span>' +
        '</button>' +
      '</div>' +
    '</div>' +
  '</div>';

  const stream = document.getElementById('omniMessagesStream');
  if (stream) {
    stream.scrollTop = stream.scrollHeight;
    stream.addEventListener('scroll', function() {
      const isNearBottom = (stream.scrollHeight - stream.scrollTop - stream.clientHeight) <= 80;
      if (isNearBottom) {
        const pill = document.getElementById('omniNewMessagePill');
        if (pill) pill.style.display = 'none';
      }
    });
  }
}

function scrollOmniStreamToBottom() {
  const stream = document.getElementById('omniMessagesStream');
  if (stream) {
    stream.scrollTo({ top: stream.scrollHeight, behavior: 'smooth' });
  }
  const pill = document.getElementById('omniNewMessagePill');
  if (pill) pill.style.display = 'none';
}

function insertQuickReply(type) {
  const inp = document.getElementById('omniReplyInput');
  if (!inp) return;
  let text = '';
  if (type === 'promo') {
    text = 'Halo Kak! Berikut update promo menarik hari ini:\n- Diskon cashback spesial\n- Free aksesoris original\n- Garansi resmi & bisa COD\nBisa kami bantu proseskan pesanannya sekarang kak? 🙏';
  } else if (type === 'katalog') {
    text = 'Halo Kak, untuk daftar harga dan katalog tipe gadget terbaru kami, silakan akses link resmi:\nhttps://gadgetmart.id/katalog\nAda seri yang paling diminati kak?';
  } else if (type === 'cicilan') {
    text = 'Halo Kak, untuk simulasi cicilan tanpa kartu kredit (Home Credit / Kredivo / Akulaku / Spaylater):\n- DP mulai 0%\n- Bunga promo ringan\n- Cukup KTP proses 15 menit langsung bawa pulang unit!\nMau kami bantu hitungkan rinciannya?';
  } else if (type === 'shopee') {
    text = 'Halo Kak, jika ingin transaksi lebih praktis dengan voucher gratis ongkir ekstra, kakak bisa checkout langsung di Shopee Official Store kami:\nhttps://shopee.co.id/gadgetmart_official\nKabari kami jika sudah checkout ya kak!';
  }
  if (text) {
    if (inp.value && inp.value.trim().length > 0) {
      inp.value += '\n\n' + text;
    } else {
      inp.value = text;
    }
    inp.focus();
    inp.style.height = 'auto';
    inp.style.height = Math.min(inp.scrollHeight, 140) + 'px';
    const sendBtn = document.getElementById('omniBtnSend');
    if (sendBtn) sendBtn.classList.add('active');
  }
}

function handleOmniInputTyping(el) {
  if (!el) return;
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 140) + 'px';
  const sendBtn = document.getElementById('omniBtnSend');
  if (sendBtn) {
    if (el.value.trim().length > 0) {
      sendBtn.classList.add('active');
    } else {
      sendBtn.classList.remove('active');
    }
  }
}

function handleOmniAttachmentClick() {
  const input = document.getElementById('omniFileInput');
  if (input) {
    input.click();
  } else {
    alert('📎 Fitur Kirim Lampiran: Anda dapat melampirkan screenshot chat atau dokumen pendukung.');
  }
}

function handleOmniFileSelected(input) {
  if (!input || !input.files || input.files.length === 0) return;
  omniPendingFile = input.files[0];
  const previewWrap = document.getElementById('omniAttachmentPreview');
  const previewName = document.getElementById('omniAttachmentName');
  const previewIcon = document.getElementById('omniAttachmentIcon');
  const isImg = omniPendingFile.type && omniPendingFile.type.startsWith('image/');
  if (previewWrap) previewWrap.style.display = 'flex';
  if (previewName) previewName.innerText = omniPendingFile.name + ' (' + Math.round(omniPendingFile.size / 1024) + ' KB)';
  if (previewIcon) previewIcon.innerText = isImg ? '🖼️' : '📄';
  const sendBtn = document.getElementById('omniBtnSend');
  if (sendBtn) sendBtn.classList.add('active');
}

function removeOmniAttachment() {
  omniPendingFile = null;
  const input = document.getElementById('omniFileInput');
  if (input) input.value = '';
  const previewWrap = document.getElementById('omniAttachmentPreview');
  if (previewWrap) previewWrap.style.display = 'none';
}

function handleOmniResolveClick(picKey) {
  const allChats = getOmniPicChats();
  const pic = allChats.find(function(p) { return p.key === picKey; });
  if (!pic) return;

  if (pic.isResolved) {
    if (confirm('Percakapan ini sudah berstatus Resolved. Apakah Anda ingin membuka kembali (Unresolve) percakapan ini?')) {
      setOmniChatResolved(picKey, false);
      const threads = getOmniThreads();
      if (!threads[picKey]) threads[picKey] = [];
      threads[picKey].push({
        sender: 'SYSTEM',
        text: 'ℹ️ Percakapan dibuka kembali oleh Admin.',
        timestamp: Date.now()
      });
      saveOmniThreads(threads);
      if (typeof showSaveToast === 'function') {
        showSaveToast('Percakapan dibuka kembali.');
      }
      renderOmniInboxModule();
    }
    return;
  }

  // ATURAN USER: Hanya bisa diresolve jika format keep 2 hari terakhir sudah diinput status dan feedbacknya
  const keeps = pic.keeps || [];
  const eligibleKeeps = keeps.filter(function(k) { return isEligibleKeep(k.timestamp); });

  const uncompleted = eligibleKeeps.filter(function(k) {
    const st = (k.status || '').trim();
    const fb = (k.feedback || '').trim();
    return !st || st === 'BELUM DIINPUT' || !fb || fb === '-' || fb === '""';
  });

  if (uncompleted.length > 0) {
    alert('⚠️ PERCAKAPAN BELUM BISA DI-RESOLVE!\n\n' +
      'Masih ada ' + uncompleted.length + ' format keep (2 hari terakhir) yang belum diinput Status atau Feedback-nya:\n\n' +
      uncompleted.slice(0, 3).map(function(u) { return '• ' + u.customerName + ' (' + u.customerWA + ') - Status: ' + (u.status || 'Belum diinput'); }).join('\n') +
      (uncompleted.length > 3 ? '\n...dan ' + (uncompleted.length - 3) + ' lainnya' : '') +
      '\n\nHarap isi Status (Kolom V) dan Feedback Tim FU (Kolom W) pada panel sebelah kanan terlebih dahulu!');
    return;
  }

  setOmniChatResolved(picKey, true);
  const threads = getOmniThreads();
  if (!threads[picKey]) threads[picKey] = [];
  threads[picKey].push({
    sender: 'SYSTEM',
    text: '✅ Percakapan telah di-resolve oleh Admin (Status & Feedback format keep lengkap).',
    timestamp: Date.now()
  });
  saveOmniThreads(threads);

  if (typeof showSaveToast === 'function') {
    showSaveToast('✅ Percakapan berhasil di-resolve!');
  }
  renderOmniInboxModule();
}

function playOmniMessageChime() {
  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;
    const ctx = new AudioContext();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.setValueAtTime(587.33, ctx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12);
    gain.gain.setValueAtTime(0.06, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.22);
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start();
    osc.stop(ctx.currentTime + 0.22);
  } catch (e) {}
}

function appendOmniBubbleToStream(msg, senderDisplayName) {
  const stream = document.getElementById('omniMessagesStream');
  if (!stream) return;

  const isOutgoing = msg.sender === 'ADMIN';
  const timeStr = new Date(msg.timestamp || Date.now()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  const msgId = String(msg.id || msg.messageId || ('msg_' + (msg.timestamp || Math.random().toString(36).slice(2))));
  const safeMsgId = escapeHtml(msgId).replace(/'/g, "\\'");

  const row = document.createElement('div');
  row.className = 'omni-msg-row ' + (isOutgoing ? 'omni-msg-outgoing' : 'omni-msg-incoming');
  row.id = 'omniMsg_' + safeMsgId;
  row.setAttribute('data-msg-id', safeMsgId);
  row.style.animation = 'fadeIn 0.2s ease-in-out';

  const tickSvg = isOutgoing ? (
    '<span class="omni-msg-status-tick" id="tick_' + safeMsgId + '">' +
      (msg.status === 'sending'
        ? '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="display:inline; vertical-align:middle; margin-left:3px;"><polyline points="20 6 9 17 4 12"/></svg>'
        : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#53bdeb" stroke-width="2" style="display:inline; vertical-align:middle; margin-left:3px;"><polyline points="20 6 9 17 4 12"/><polyline points="20 12 13 19"/></svg>') +
    '</span>'
  ) : '';

  let replyToObj = msg.replyTo || null;
  let displayText = msg.text || msg.message || '';
  if (displayText.startsWith('>')) {
    const parsed = parseQuotedFromText(displayText);
    if (parsed.cleanText) {
      displayText = parsed.cleanText;
      if (!replyToObj && parsed.replyTo) {
        replyToObj = parsed.replyTo;
      }
    }
  }

  if (!window.currentOmniChatMessages) window.currentOmniChatMessages = [];
  const streamIdx = window.currentOmniChatMessages.length;
  window.currentOmniChatMessages.push(msg);

  const quotedBlock = replyToObj ? (
    '<div class="omni-bubble-reply-block" onclick="handleOmniQuotedClick(' + streamIdx + ')" title="Klik untuk melompat ke pesan yang direply">' +
      '<div class="omni-reply-block-sender">' + escapeHtml(replyToObj.senderName || 'Balasan') + '</div>' +
      '<div class="omni-reply-block-text">' + escapeHtml(replyToObj.text || '') + '</div>' +
    '</div>'
  ) : '';

  const mediaRawUrl = msg.mediaUrl || msg.image || '';
  const isDoc = (msg.mediaType === 'document' || msg.messageType === 'document' || (mediaRawUrl && !mediaRawUrl.match(/\.(jpe?g|png|webp|gif)/i) && (msg.filename || mediaRawUrl.includes('.txt') || mediaRawUrl.includes('.pdf') || mediaRawUrl.includes('.doc') || mediaRawUrl.includes('.xls'))));
  const hasImage = Boolean(mediaRawUrl && !isDoc);
  let mediaMarkupStream = '';
  if (hasImage) {
    mediaMarkupStream = (
      '<div class="omni-bubble-media" onclick="openOmniImageModal(\'' + escapeHtml(mediaRawUrl) + '\')">' +
        '<img src="' + escapeHtml(mediaRawUrl) + '" alt="WhatsApp Image" class="omni-chat-image" loading="lazy">' +
      '</div>'
    );
  } else if (isDoc) {
    const docName = msg.filename || 'Dokumen WhatsApp';
    mediaMarkupStream = (
      '<div class="omni-bubble-document" style="display:flex; align-items:center; gap:8px; padding:7px 10px; background:rgba(0,0,0,0.05); border:1px solid rgba(0,0,0,0.06); border-radius:6px; margin:4px 0; max-width:260px;">' +
        '<span style="font-size:1.4rem; line-height:1;">📄</span>' +
        '<div style="flex:1; min-width:0;">' +
          '<div style="font-weight:600; font-size:0.75rem; color:#0f172a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="' + escapeHtml(docName) + '">' + escapeHtml(docName) + '</div>' +
          '<div style="font-size:0.65rem; color:#64748b;">Dokumen WhatsApp</div>' +
        '</div>' +
        '<a href="' + escapeHtml(mediaRawUrl) + '" target="_blank" download="' + escapeHtml(docName) + '" style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:6px; background:#0284c7; color:#fff; text-decoration:none;" title="Unduh / Buka Dokumen">' +
          '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>' +
        '</a>' +
      '</div>'
    );
  } else if (msg.messageType === 'image') {
    mediaMarkupStream = (
      '<div class="omni-bubble-media-placeholder">' +
        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>' +
        '<span>📷 Foto / Gambar WhatsApp</span>' +
      '</div>'
    );
  }

  const senderNameText = isOutgoing ? 'Admin (Anda)' : (senderDisplayName || 'PIC');
  const safeText = escapeHtml(displayText || msg.text || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
  const safeSender = escapeHtml(senderNameText).replace(/'/g, "\\'");
  const replyBtn = '<button type="button" class="omni-btn-bubble-reply" onclick="startOmniQuotedReply(\'' + safeMsgId + '\', \'' + safeText + '\', \'' + safeSender + '\')" title="Balas pesan ini"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg></button>';

  let cleanTextRenderStream = displayText;
  if (hasImage && (cleanTextRenderStream === '[Foto/Gambar]' || cleanTextRenderStream === '[Pesan Masuk]' || cleanTextRenderStream === '[Image]')) {
    cleanTextRenderStream = '';
  }
  const textHtmlStream = cleanTextRenderStream ? ('<div style="white-space:pre-wrap; word-break:break-word;' + (hasImage ? ' margin-top:6px;' : '') + '">' + escapeHtml(cleanTextRenderStream) + '</div>') : '';

  row.innerHTML = replyBtn +
    '<div class="omni-bubble ' + (isOutgoing ? 'omni-bubble-outgoing' : 'omni-bubble-incoming') + '">' +
      '<div class="omni-bubble-sender">' + escapeHtml(senderNameText) + '</div>' +
      quotedBlock +
      mediaMarkupStream +
      textHtmlStream +
      '<span class="omni-bubble-time">' + timeStr + tickSvg + '</span>' +
    '</div>';
  stream.appendChild(row);

  // Smart Auto-Scroll
  const isNearBottom = (stream.scrollHeight - stream.scrollTop - stream.clientHeight) <= 120;
  if (isOutgoing || isNearBottom) {
    stream.scrollTo({ top: stream.scrollHeight, behavior: 'smooth' });
  } else {
    const pill = document.getElementById('omniNewMessagePill');
    if (pill) pill.style.display = 'flex';
  }
}

function updateOmniCardInSidebar(picKey, lastMsg, picName, picCabang) {
  const listContainer = document.getElementById('omniChatListContainer');
  if (!listContainer) return;
  const normKey = normalizeWhatsAppPhone(picKey) || picKey;

  const cards = listContainer.querySelectorAll('.omni-chat-card');
  let targetCard = null;
  cards.forEach(function(card) {
    const oc = card.getAttribute('onclick') || '';
    if (oc.includes(normKey)) {
      targetCard = card;
    }
  });

  const timeStr = lastMsg ? new Date(lastMsg.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
  const snippet = lastMsg ? (lastMsg.sender === 'ADMIN' ? 'Anda: ' + lastMsg.text : lastMsg.text) : '';

  if (targetCard) {
    const snippetEl = targetCard.querySelector('.omni-card-snippet');
    if (snippetEl) snippetEl.innerText = snippet;
    const timeEl = targetCard.querySelector('.omni-card-time');
    if (timeEl) timeEl.innerText = timeStr;
    // Pindahkan ke paling atas secara halus (posisi #1)
    if (listContainer.firstChild !== targetCard) {
      listContainer.insertBefore(targetCard, listContainer.firstChild);
    }
  } else {
    renderOmniInboxModule();
  }
}

async function sendOmniReply() {
  const inp = document.getElementById('omniReplyInput');
  if (!inp || !selectedOmniPicKey) return;
  const text = inp.value.trim();
  const fileToSend = omniPendingFile;
  if (!text && !fileToSend) return;

  const allPics = getOmniPicChats();
  const currentPic = allPics.find(function(p) { return p.key === selectedOmniPicKey; });
  const picWA = currentPic ? currentPic.picWA : selectedOmniPicKey;
  const picName = currentPic ? currentPic.picName : 'PIC FU';

  const now = Date.now();
  const msgId = 'out_' + now + '_' + Math.random().toString(36).slice(2, 7);
  let textToSend = text;

  let localMediaUrl = null;
  let mediaType = null;
  let fileName = null;
  let fileSize = null;

  if (fileToSend) {
    localMediaUrl = URL.createObjectURL(fileToSend);
    mediaType = fileToSend.type.startsWith('image/') ? 'image' : 'document';
    fileName = fileToSend.name;
    fileSize = fileToSend.size;
  }

  const outgoingMsg = {
    id: msgId,
    messageId: msgId,
    sender: 'ADMIN',
    text: text,
    timestamp: now,
    status: 'sending',
    mediaUrl: localMediaUrl,
    media_url: localMediaUrl,
    media_type: mediaType,
    messageType: mediaType,
    filename: fileName,
    file_size: fileSize
  };

  // Cek jika sedang mereply pesan tertentu (Quoted Reply)
  if (activeQuotedMsg) {
    outgoingMsg.replyTo = {
      id: activeQuotedMsg.id || '',
      text: activeQuotedMsg.text,
      senderName: activeQuotedMsg.senderName
    };
    textToSend = text;
    cancelOmniQuotedReply();
  }

  const threads = getOmniThreads();
  if (!threads[selectedOmniPicKey]) threads[selectedOmniPicKey] = [];
  threads[selectedOmniPicKey].push(outgoingMsg);
  saveOmniThreads(threads);

  // Clear input immediately (0ms UI latency) & maintain focus
  inp.value = '';
  inp.style.height = 'auto';
  inp.focus();
  removeOmniAttachment();
  const sendBtn = document.getElementById('omniBtnSend');
  if (sendBtn) sendBtn.classList.remove('active');

  // Optimistic UI: langsung tempel bubble ke obrolan secara instan!
  appendOmniBubbleToStream(outgoingMsg, 'Admin (Anda)');
  updateOmniCardInSidebar(selectedOmniPicKey, outgoingMsg, picName, currentPic ? currentPic.cabang : '-');

  // Kirim langsung ke WhatsApp PIC via API WatZap / WAHA di latar belakang
  try {
    let res;
    if (fileToSend) {
      const formData = new FormData();
      formData.append('phone', picWA);
      if (textToSend) formData.append('message', textToSend);
      formData.append('file', fileToSend);
      formData.append('messageId', msgId);
      formData.append('timestamp', now);
      if (outgoingMsg.replyTo) {
        formData.append('replyTo', JSON.stringify(outgoingMsg.replyTo));
      }
      res = await fetch('/api/watzap-send', {
        method: 'POST',
        body: formData
      });
    } else {
      res = await fetch('/api/watzap-send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          phone: picWA,
          message: textToSend,
          messageId: msgId,
          timestamp: now,
          replyTo: outgoingMsg.replyTo || null
        })
      });
    }

    const json = await res.json();
    if (json.success) {
      outgoingMsg.status = 'sent';
      if (json.entry && (json.entry.media_url || json.entry.mediaUrl)) {
        outgoingMsg.mediaUrl = json.entry.media_url || json.entry.mediaUrl;
        outgoingMsg.media_url = outgoingMsg.mediaUrl;
      }
      const currentThreads = getOmniThreads();
      if (currentThreads[selectedOmniPicKey]) {
        const found = currentThreads[selectedOmniPicKey].find(function(m) {
          return m.id === msgId || m.messageId === msgId;
        });
        if (found) {
          found.status = 'sent';
          if (outgoingMsg.mediaUrl) found.mediaUrl = outgoingMsg.mediaUrl;
        }
        saveOmniThreads(currentThreads);
      } else {
        saveOmniThreads(threads);
      }
      const tick = document.getElementById('tick_' + msgId);
      if (tick) {
        tick.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#53bdeb" stroke-width="2" style="display:inline; vertical-align:middle; margin-left:3px;"><polyline points="20 6 9 17 4 12"/><polyline points="20 12 13 19"/></svg>';
      }
      if (typeof showSaveToast === 'function') {
        showSaveToast('Pesan ' + (fileToSend ? (fileToSend.type.startsWith('image/') ? 'gambar' : 'dokumen') : 'teks') + ' terkirim ke WhatsApp ' + picName + '!');
      }
    } else {
      console.warn('WatZap API response:', json);
      if (typeof showSaveToast === 'function') {
        showSaveToast('Pesan tersimpan lokal. WatZap info: ' + (json.message || 'Gagal terkirim'));
      }
    }
  } catch (err) {
    console.error('Gagal kirim via WatZap:', err);
  }
}

async function pollWatzapIncomingMessages() {
  // OPTIMASI: HANYA poll jika user sedang berada di Tab 9 (Omnichannel)!
  if (typeof currentActiveTab !== 'undefined' && currentActiveTab !== 9) {
    return;
  }
  if (isPollingWatzap) return;
  isPollingWatzap = true;

  try {
    const res = await fetch('/api/watzap-webhook?since=' + lastWatzapSyncTime);
    if (!res.ok) return;
    const data = await res.json();
    if (!data.success) return;

    // SINKRONISASI STATUS RESOLVE PERMANEN DARI NEON POSTGRESQL (Tidak pernah hilang saat redeploy)
    if (data.resolvedChats && typeof data.resolvedChats === 'object') {
      const dbMap = {};
      Object.keys(data.resolvedChats).forEach(function(ph) {
        if (data.resolvedChats[ph] && data.resolvedChats[ph].isResolved) {
          const norm = normalizeWhatsAppPhone(ph) || ph;
          dbMap[norm] = true;
        }
      });
      window.omniResolvedDbMap = Object.assign({}, getOmniResolvedChats(), dbMap);
      try {
        localStorage.setItem('omni_resolved_chats_v1', JSON.stringify(window.omniResolvedDbMap));
      } catch (e) {}
    }

    if (!Array.isArray(data.messages) || data.messages.length === 0) return;

    const isInitialLoad = (lastWatzapSyncTime === 0);
    let hasNew = false;
    let playedSound = false;
    const threads = getOmniThreads();

    data.messages.forEach(function(msg) {
      const ts = parseOmniTimestamp(msg.timestamp);
      if (ts > lastWatzapSyncTime) lastWatzapSyncTime = ts;

      const cleanPhone = normalizeWhatsAppPhone(msg.phone);
      if (!cleanPhone) return;

      // STRICT: TOLAK & ABAIKAN GRUP
      if (msg.isGroup || isGroupWhatsApp(cleanPhone)) return;

      const matched = matchPicFromMasterData(cleanPhone, msg.senderName || ('PIC ' + cleanPhone), '-');
      const picKey = cleanPhone; // Standarisasi: Key percakapan SELALU nomor telepon
      const text = msg.message || msg.text || '';
      const incomingMediaUrl = msg.mediaUrl || msg.media_url || msg.image || msg.url || '';

      if (!threads[picKey]) threads[picKey] = [];

      // Cari apakah pesan sudah pernah tercatat
      let existingMsg = null;
      threads[picKey].forEach(function(t) {
        if (t.id && msg.messageId && t.id === msg.messageId) {
          existingMsg = t;
        } else if (Math.abs(Number(t.timestamp) - ts) < 5000 && (t.text || '').trim() === text.trim() && (t.sender || 'PIC') === (msg.sender || 'PIC')) {
          existingMsg = t;
        }
      });

      if (existingMsg) {
        // Jika sebelumnya belum ada mediaUrl dan sekarang ada, perbarui data media
        if (!existingMsg.mediaUrl && incomingMediaUrl) {
          existingMsg.mediaUrl = incomingMediaUrl;
          existingMsg.messageType = 'image';
          hasNew = true;
        }
      } else {
        let replyToObj = msg.replyTo || null;
        let cleanMsgText = text;
        if (!replyToObj && cleanMsgText.startsWith('>')) {
          const parsed = parseQuotedFromText(cleanMsgText);
          if (parsed.replyTo) {
            replyToObj = parsed.replyTo;
            cleanMsgText = parsed.cleanText;
          }
        }

        const newMsgObj = {
          id: msg.messageId || ('in_' + ts),
          sender: msg.sender || 'PIC',
          senderName: msg.senderName || matched.picName,
          text: cleanMsgText,
          timestamp: ts,
          status: 'sent',
          mediaUrl: incomingMediaUrl,
          messageType: msg.messageType || (incomingMediaUrl ? 'image' : 'text'),
          replyTo: replyToObj
        };
        threads[picKey].push(newMsgObj);
        // CATATAN: Sekali di-resolve, tetap berstatus resolve permanen di database Neon PostgreSQL!
        hasNew = true;

        if (!isInitialLoad) {
          // INCREMENTAL UPDATE: Jika chat ini sedang dibuka di layar, langsung tempel bubble!
          const isCurrentChat = (selectedOmniPicKey === picKey || 
            normalizeWhatsAppPhone(selectedOmniPicKey) === cleanPhone);

          if (isCurrentChat) {
            appendOmniBubbleToStream(newMsgObj, matched.picName);
          }

          // Perbarui kartu di sidebar kiri
          updateOmniCardInSidebar(picKey, newMsgObj, matched.picName, matched.cabang);

          // Mainkan notifikasi chime lembut jika pesan masuk dari PIC
          if (msg.sender !== 'ADMIN' && !playedSound) {
            playOmniMessageChime();
            playedSound = true;
          }
        }
      }
    });

    if (hasNew) {
      saveOmniThreads(threads);
      if (isInitialLoad) {
        renderOmniInboxModule();
        const allChats = getOmniPicChats();
        if (selectedOmniPicKey) {
          selectOmniPic(selectedOmniPicKey);
        } else if (allChats.length > 0) {
          selectOmniPic(allChats[0].key);
        }
      } else {
        const listContainer = document.getElementById('omniChatListContainer');
        const allChats = getOmniPicChats();
        const currentCardsCount = listContainer ? listContainer.querySelectorAll('.omni-chat-card').length : 0;
        if (allChats.length !== currentCardsCount) {
          renderOmniInboxModule();
        } else {
          const bAssigned = document.getElementById('omniBadgeAssigned');
          const bUnresponded = document.getElementById('omniBadgeUnresponded');
          const bResolved = document.getElementById('omniBadgeResolved');
          const unrespCount = allChats.filter(function(p) { return p.isUnresponded && !p.isResolved; }).length;
          const assignedCount = allChats.filter(function(p) { return !p.isResolved; }).length;
          const resCount = allChats.filter(function(p) { return p.isResolved; }).length;
          if (bAssigned) bAssigned.innerText = assignedCount;
          if (bUnresponded) bUnresponded.innerText = unrespCount;
          if (bResolved) bResolved.innerText = resCount;
        }
      }
    }
  } catch (e) {
  } finally {
    isPollingWatzap = false;
  }
}

function startWatzapPolling() {
  if (watzapPollInterval) return;
  pollWatzapIncomingMessages();
  // Polling super cepat (1.2 detik) langsung ke Neon PostgreSQL
  watzapPollInterval = setInterval(pollWatzapIncomingMessages, 1200);

  // Saat user berpindah window (Alt+Tab) dan kembali, langsung sinkronkan tanpa refresh
  document.addEventListener('visibilitychange', function() {
    if (!document.hidden && typeof currentActiveTab !== 'undefined' && currentActiveTab === 9) {
      pollWatzapIncomingMessages();
    }
  });
}

async function syncOmniChatsFromWhatsApp(isManual) {
  const btn = document.getElementById('btnSyncOmniWa');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" style="width:12px;height:12px;border-width:2px;display:inline-block;vertical-align:middle;border-radius:50%;border:2px solid currentColor;border-right-color:transparent;animation:omni-spin .75s linear infinite;"></span> <span>Syncing...</span>';
  }

  try {
    const res = await fetch('/api/watzap-webhook?sync=1&since=0');
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    if (data.success && Array.isArray(data.messages)) {
      const threads = getOmniThreads();
      let addedCount = 0;

      data.messages.forEach(function(msg) {
        const ts = parseOmniTimestamp(msg.timestamp);
        if (ts > lastWatzapSyncTime) lastWatzapSyncTime = ts;

        const cleanPhone = normalizeWhatsAppPhone(msg.phone);
        if (!cleanPhone || msg.isGroup || isGroupWhatsApp(cleanPhone)) return;

        const picKey = cleanPhone;
        const text = msg.message || msg.text || '';
        const incomingMediaUrl = msg.mediaUrl || msg.media_url || msg.image || msg.url || '';
        const matched = matchPicFromMasterData(cleanPhone, msg.senderName || ('PIC ' + cleanPhone), '-');

        if (!threads[picKey]) threads[picKey] = [];

        let existingMsg = null;
        threads[picKey].forEach(function(t) {
          if (t.id && msg.messageId && t.id === msg.messageId) {
            existingMsg = t;
          } else if (Math.abs(Number(t.timestamp) - ts) < 5000 && (t.text || '').trim() === text.trim()) {
            existingMsg = t;
          }
        });

        if (existingMsg) {
          if (!existingMsg.mediaUrl && incomingMediaUrl) {
            existingMsg.mediaUrl = incomingMediaUrl;
            existingMsg.messageType = 'image';
          }
        } else {
          let replyToObj = msg.replyTo || null;
          let cleanMsgText = text;
          if (!replyToObj && cleanMsgText.startsWith('>')) {
            const parsed = parseQuotedFromText(cleanMsgText);
            if (parsed.replyTo) {
              replyToObj = parsed.replyTo;
              cleanMsgText = parsed.cleanText;
            }
          }

          threads[picKey].push({
            id: msg.messageId || ('in_' + ts),
            sender: msg.sender || 'PIC',
            senderName: msg.senderName || matched.picName,
            text: cleanMsgText,
            timestamp: ts,
            status: 'sent',
            mediaUrl: incomingMediaUrl,
            messageType: msg.messageType || (incomingMediaUrl ? 'image' : 'text'),
            replyTo: replyToObj
          });
          addedCount++;
        }
      });

      // Urutkan pesan per thread berdasarkan timestamp
      Object.keys(threads).forEach(function(k) {
        if (Array.isArray(threads[k])) {
          threads[k].sort(function(a, b) {
            return (a.timestamp || 0) - (b.timestamp || 0);
          });
        }
      });

      saveOmniThreads(threads);
      renderOmniInboxModule();

      const allChats = getOmniPicChats();
      if (!selectedOmniPicKey && allChats.length > 0) {
        selectOmniPic(allChats[0].key);
      } else if (selectedOmniPicKey) {
        selectOmniPic(selectedOmniPicKey);
      }

      if (isManual && typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'success',
          title: 'Sinkronisasi Selesai!',
          text: 'Berhasil menyinkronkan chat WhatsApp WAHA VPS (' + allChats.length + ' percakapan).',
          timer: 2000,
          showConfirmButton: false,
          toast: true,
          position: 'top-end'
        });
      }
    }
  } catch (err) {
    console.error('Gagal sync chat WhatsApp:', err);
    if (isManual && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Sinkronisasi Gagal',
        text: 'Tidak dapat menghubungi server: ' + (err.message || err),
        confirmButtonText: 'Tutup'
      });
    }
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> <span>Sync WA</span>';
    }
  }
}

function handleOmniInputKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendOmniReply();
  }
}

function openWhatsAppChat(cleanWA, picName) {
  if (!cleanWA) return;
  const phone = cleanWA.startsWith('62') ? cleanWA : (cleanWA.startsWith('0') ? '62' + cleanWA.slice(1) : '62' + cleanWA);
  const text = encodeURIComponent('Halo Kak ' + (picName || '') + ', ini dari Admin Sosmed terkait follow-up format keep.');
  window.open('https://wa.me/' + phone + '?text=' + text, '_blank');
}

// --------------------------------------------------------------------------
// DEDICATED FORMAT KEEP PANEL (RIGHT COLUMN - MODERNIZED)
// --------------------------------------------------------------------------
function renderOmniKeepPanel(pic) {
  const container = document.getElementById('omniDetailsPanelContainer');
  if (!container) return;
  if (!pic) {
    container.innerHTML = '';
    return;
  }
  const pendingKeeps = (pic.keeps || []).filter(function(k) { return k.isPending; });
  const resolvedKeeps = (pic.keeps || []).filter(function(k) { return !k.isPending; });
  const displayKeeps = selectedOmniKeepSubTab === 'PENDING' ? pendingKeeps : resolvedKeeps;

  const statusOptions = [
    'WIN',
    'LOSE',
    'BELUM DIINPUT',
    'DOUBLE KEEP',
    'ON PROGRESS LOW',
    'ON PROGRESS MEDIUM',
    'ON PROGRESS HOT',
    'TAKE OUT'
  ];

  // User requirement: Yang kotak warna hijau tolong tulisannya dihapus aja (jika 0 maka kosong tanpa teks / notifikasi)
  const cardsMarkup = displayKeeps.length === 0 ? '' : displayKeeps.map(function(k) {
    const curStatus = k.status || 'BELUM DIINPUT';
    const optMarkup = statusOptions.map(function(opt) {
      return '<option value="' + opt + '" ' + (opt === curStatus ? 'selected' : '') + '>' + opt + '</option>';
    }).join('');
    const dateFormatted = k.timestamp ? k.timestamp.split(' ')[0] : '-';

    return '<div class="omni-keep-card-modern" id="keepCard_' + k.customerWA + '">' +
      '<div class="omni-keep-card-top-row">' +
        '<div>' +
          '<div class="omni-keep-cust-name">' + escapeHtml(k.customerName) + '</div>' +
          '<a href="https://wa.me/' + k.customerWA + '" target="_blank" class="omni-keep-cust-phone" title="Chat WhatsApp Customer">' +
            k.customerWA + ' &middot; ' + escapeHtml(k.cabang) +
          '</a>' +
        '</div>' +
        '<span class="omni-keep-date-chip">' + dateFormatted + '</span>' +
      '</div>' +
      '<div class="omni-keep-info-box">' +
        '<div class="omni-keep-info-row">' +
          '<span class="omni-keep-info-label">Barang:</span>' +
          '<strong>' + escapeHtml(k.barang) + '</strong>' +
        '</div>' +
        '<div class="omni-keep-info-row">' +
          '<span class="omni-keep-info-label">Catatan:</span>' +
          '<span>' + escapeHtml(k.catatan || '-') + '</span>' +
        '</div>' +
        '<div class="omni-keep-info-row">' +
          '<span class="omni-keep-info-label">Remarks:</span>' +
          '<span>' + escapeHtml(k.remarks || '-') + '</span>' +
        '</div>' +
      '</div>' +
      '<div class="omni-keep-form-modern">' +
        '<div>' +
          '<label class="omni-keep-label-modern">REPORT AR (Kolom N):</label>' +
          '<input type="text" class="omni-keep-input-modern" id="omniReportARInp_' + k.customerWA + '" value="' + escapeHtml(k.reportAR || '') + '" placeholder="Nomor Dokumen AR..." />' +
        '</div>' +
        '<div>' +
          '<label class="omni-keep-label-modern">Status (Kolom V):</label>' +
          '<select class="omni-keep-select-modern" id="omniStatusSelect_' + k.customerWA + '">' +
            optMarkup +
          '</select>' +
        '</div>' +
        '<div>' +
          '<label class="omni-keep-label-modern">Feedback Tim FU (Kolom W):</label>' +
          '<textarea class="omni-keep-textarea-modern" id="omniFeedbackInp_' + k.customerWA + '" placeholder="Tulis feedback dari PIC FU...">' + escapeHtml(k.feedback || '') + '</textarea>' +
        '</div>' +
        '<button type="button" class="omni-btn-save-modern" id="btnSaveKeep_' + k.customerWA + '" onclick="saveSingleKeepFeedback(\'' + k.customerWA + '\', \'' + pic.key + '\')">' +
          '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>' +
          '<span>Simpan Feedback & AR</span>' +
        '</button>' +
      '</div>' +
    '</div>';
  }).join('');

  container.innerHTML = '<div class="omni-details-top">' +
    '<div class="omni-details-title-row">' +
      '<span class="omni-details-heading">Format Keep PIC</span>' +
      '<span class="omni-keep-period-chip">2 Hari Terakhir</span>' +
    '</div>' +
    '<span class="omni-keep-counter-badge ' + (pendingKeeps.length === 0 ? 'zero' : '') + '">' +
      pendingKeeps.length + ' Pending' +
    '</span>' +
  '</div>' +
  '<div class="omni-keep-subtabs-wrap">' +
    '<div class="omni-keep-subtabs">' +
      '<button type="button" class="omni-subtab-btn ' + (selectedOmniKeepSubTab === 'PENDING' ? 'active' : '') + '" onclick="setOmniKeepSubTab(\'PENDING\')">' +
        'Belum Diisi (' + pendingKeeps.length + ')' +
      '</button>' +
      '<button type="button" class="omni-subtab-btn ' + (selectedOmniKeepSubTab === 'RESOLVED' ? 'active' : '') + '" onclick="setOmniKeepSubTab(\'RESOLVED\')">' +
        'Selesai (' + resolvedKeeps.length + ')' +
      '</button>' +
    '</div>' +
  '</div>' +
  '<div class="omni-details-scroll">' +
    cardsMarkup +
  '</div>';
}

async function saveSingleKeepFeedback(customerWA, picKey) {
  const statusEl = document.getElementById('omniStatusSelect_' + customerWA);
  const feedbackEl = document.getElementById('omniFeedbackInp_' + customerWA);
  const arEl = document.getElementById('omniReportARInp_' + customerWA);
  const saveBtn = document.getElementById('btnSaveKeep_' + customerWA);
  if (!statusEl || !feedbackEl) return;

  const newStatus = statusEl.value;
  const newFeedback = feedbackEl.value.trim();
  const newReportAR = arEl ? arEl.value.trim() : '';

  let matchedRow = null;
  if (Array.isArray(rawKeepRows)) {
    for (let i = 0; i < rawKeepRows.length; i++) {
      const rowWA = cleanPhoneDigits(rawKeepRows[i]['Nomor WA'] || '');
      if (rowWA === customerWA) {
        rawKeepRows[i]['Status'] = newStatus;
        rawKeepRows[i]['Feedback Tim FU'] = newFeedback;
        rawKeepRows[i]['REPORT AR'] = newReportAR;
        matchedRow = rawKeepRows[i];
        break;
      }
    }
  }

  const threads = getOmniThreads();
  if (!threads[picKey]) threads[picKey] = [];
  let updateText = '[UPDATE FORMAT KEEP] Status: ' + newStatus + ' | Feedback: ' + (newFeedback || '-');
  if (newReportAR) updateText += ' | AR: ' + newReportAR;
  updateText += ' (' + (matchedRow ? matchedRow['Nama'] : customerWA) + ')';

  threads[picKey].push({
    sender: 'ADMIN',
    text: updateText,
    timestamp: Date.now()
  });
  saveOmniThreads(threads);

  if (saveBtn) {
    saveBtn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span>✓ Tersimpan</span>';
    saveBtn.style.background = '#059669';
  }

  // Update in-memory 2-day keep cache (0ms)
  const keepsMap = getIndexedOmniKeeps();
  Object.keys(keepsMap).forEach(function(k) {
    (keepsMap[k] || []).forEach(function(item) {
      if (item.customerWA === customerWA) {
        item.status = newStatus;
        item.feedback = newFeedback;
        item.reportAR = newReportAR;
        item.isPending = (newStatus === 'BELUM DIINPUT');
      }
    });
  });

  try {
    let success = false;
    // 1. Coba update via API backend
    try {
      const res = await fetch('/api/update-keep-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          nomorWA: customerWA,
          status: newStatus,
          feedback: newFeedback,
          feedbackTimFU: newFeedback,
          reportAR: newReportAR,
          cabang: matchedRow ? matchedRow['Cabang'] : '',
          namaTimFollowUp: matchedRow ? (matchedRow['Nama Tim Follow Up'] || matchedRow['Nama Tim FU']) : ''
        })
      });
      if (res.ok) {
        const j = await res.json();
        if (j && j.success) success = true;
      }
    } catch (e) {
      console.warn('API /api/update-keep-status gagal:', e);
    }

    if (newReportAR) {
      try {
        await fetch('/api/update-report-ar', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            nomorWA: customerWA,
            arDoc: newReportAR
          })
        });
      } catch (e) {
        console.warn('API /api/update-report-ar gagal:', e);
      }
    }
  } catch (err) {
    console.error('Gagal update keep status ke backend:', err);
  }

  if (typeof showSaveToast === 'function') {
    showSaveToast('Format keep & REPORT AR berhasil disimpan!');
  }

  renderOmniInboxModule();
}

function clearOmniTestChats() {
  if (confirm('Apakah Anda yakin ingin membersihkan riwayat chat uji coba dan grup?')) {
    localStorage.removeItem('gadgetmart_omni_threads');
    localStorage.removeItem('omni_resolved_chats_v1');
    cachedOmniKeepsMap = null;
    selectedOmniPicKey = null;
    lastWatzapSyncTime = 0;
    renderOmniInboxModule();
    if (typeof showSaveToast === 'function') {
      showSaveToast('Riwayat chat berhasil dibersihkan.');
    }
  }
}


// ==========================================================================
// SIMULASI CHAT MASUK DARI WHATSAPP (TESTING HARNESS)
// ==========================================================================
function openSimulateChatModal(cleanWA, picName) {
  const existing = document.getElementById('omniSimulateModal');
  if (existing) existing.remove();

  const overlay = document.createElement('div');
  overlay.id = 'omniSimulateModal';
  overlay.className = 'omni-sim-modal-overlay';
  overlay.innerHTML = '<div class="omni-sim-modal-card">' +
    '<div class="omni-sim-modal-header">' +
      '<div class="omni-sim-modal-title">' +
        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>' +
        '<span>Simulasi Chat Masuk dari WhatsApp</span>' +
      '</div>' +
      '<button type="button" class="omni-sim-modal-close" onclick="closeSimulateChatModal()">&times;</button>' +
    '</div>' +
    '<div class="omni-sim-modal-body">' +
      '<div class="omni-sim-form-group">' +
        '<label class="omni-sim-label">Pengirim (PIC / Kontak):</label>' +
        '<input type="text" id="simSenderName" class="omni-sim-input" value="' + escapeHtml(picName || 'PIC Follow Up') + '" />' +
      '</div>' +
      '<div class="omni-sim-form-group">' +
        '<label class="omni-sim-label">Nomor WhatsApp Pengirim:</label>' +
        '<input type="text" id="simSenderPhone" class="omni-sim-input" value="' + escapeHtml(cleanWA || '081234567890') + '" />' +
      '</div>' +
      '<div class="omni-sim-form-group">' +
        '<label class="omni-sim-label">Tipe Pesan Masuk:</label>' +
        '<select id="simMessageType" class="omni-sim-select" onchange="toggleSimMediaInputs(this.value)">' +
          '<option value="text">💬 Pesan Teks Saja</option>' +
          '<option value="image">📷 Pesan Gambar / Foto</option>' +
          '<option value="document">📄 Pesan File / Dokumen (PDF, Excel, dll)</option>' +
        '</select>' +
      '</div>' +
      '<div class="omni-sim-form-group" id="simFileGroup" style="display:none;">' +
        '<label class="omni-sim-label" id="simFileLabel">Unggah File Lampiran:</label>' +
        '<input type="file" id="simFileInput" class="omni-sim-input" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt" />' +
      '</div>' +
      '<div class="omni-sim-form-group">' +
        '<label class="omni-sim-label">Isi Pesan / Keterangan (Caption):</label>' +
        '<textarea id="simMessageText" class="omni-sim-textarea" rows="3" placeholder="Tulis isi pesan chat yang seolah-olah dikirim oleh PIC/Customer...">Halo Admin, ini chat balasan konfirmasi dari tim lapangan.</textarea>' +
      '</div>' +
    '</div>' +
    '<div class="omni-sim-modal-footer">' +
      '<button type="button" class="omni-quick-pill" onclick="closeSimulateChatModal()">Batal</button>' +
      '<button type="button" id="btnSubmitSimulate" class="omni-btn-save-modern" style="width:auto; padding:8px 18px;" onclick="submitSimulateChat()">' +
        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>' +
        '<span>Kirim Chat Masuk</span>' +
      '</button>' +
    '</div>' +
  '</div>';

  document.body.appendChild(overlay);
}

function closeSimulateChatModal() {
  const el = document.getElementById('omniSimulateModal');
  if (el) el.remove();
}

function toggleSimMediaInputs(val) {
  const grp = document.getElementById('simFileGroup');
  const lbl = document.getElementById('simFileLabel');
  const inp = document.getElementById('simFileInput');
  const txt = document.getElementById('simMessageText');
  if (!grp) return;

  if (val === 'text') {
    grp.style.display = 'none';
    if (txt) txt.value = 'Halo Admin, ini chat balasan konfirmasi dari tim lapangan.';
  } else if (val === 'image') {
    grp.style.display = 'flex';
    if (lbl) lbl.innerText = 'Unggah Gambar (JPG/PNG/WebP):';
    if (inp) inp.accept = 'image/*';
    if (txt) txt.value = 'Halo Admin, ini bukti foto nota / kondisi unit fisik.';
  } else {
    grp.style.display = 'flex';
    if (lbl) lbl.innerText = 'Unggah Dokumen (PDF, XLSX, DOCX):';
    if (inp) inp.accept = '.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv';
    if (txt) txt.value = 'Halo Admin, berikut terlampir berkas data rekapitulasi.';
  }
}

async function submitSimulateChat() {
  const phone = document.getElementById('simSenderPhone')?.value.trim() || '081234567890';
  const name = document.getElementById('simSenderName')?.value.trim() || 'PIC Follow Up';
  const type = document.getElementById('simMessageType')?.value || 'text';
  const text = document.getElementById('simMessageText')?.value.trim() || '';
  const fileInp = document.getElementById('simFileInput');
  const file = fileInp && fileInp.files && fileInp.files[0] ? fileInp.files[0] : null;

  const btn = document.getElementById('btnSubmitSimulate');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span>Mengirim simulasi...</span>';
  }

  try {
    const formData = new FormData();
    formData.append('phone', phone);
    formData.append('sender_name', name);
    formData.append('message', text);
    formData.append('media_type', type);
    if (file) {
      formData.append('file', file);
    }

    const res = await fetch('/api/watzap-simulate-receive', {
      method: 'POST',
      body: formData
    });
    const json = await res.json();

    closeSimulateChatModal();
    if (typeof showSaveToast === 'function') {
      showSaveToast('✅ Simulasi pesan masuk berhasil diterima!');
    }

    // Trigger instant webhook poll
    await pollWatzapIncomingMessages();
    renderOmniInboxModule();
    if (selectedOmniPicKey) {
      selectOmniPic(selectedOmniPicKey);
    }
  } catch (err) {
    console.error('Gagal simulasi chat:', err);
    alert('Gagal mengirim simulasi: ' + err.message);
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<span>Kirim Chat Masuk</span>';
    }
  }
}

// Global Window Exports
window.switchOmniView = switchOmniView;
window.renderOmniMasterTable = renderOmniMasterTable;
window.syncMasterDataFromKeepCSV = syncMasterDataFromKeepCSV;
window.openAddPicModal = openAddPicModal;
window.openEditPicModal = openEditPicModal;
window.closePicModal = closePicModal;
window.savePicModal = savePicModal;
window.deletePicMaster = deletePicMaster;
window.setOmniFolder = setOmniFolder;
window.setOmniKeepSubTab = setOmniKeepSubTab;
window.filterOmniChats = filterOmniChats;
window.selectOmniPic = selectOmniPic;
window.renderOmniInboxModule = renderOmniInboxModule;
window.saveSingleKeepFeedback = saveSingleKeepFeedback;
window.sendOmniReply = sendOmniReply;
window.scrollOmniStreamToBottom = scrollOmniStreamToBottom;
window.handleOmniInputKey = handleOmniInputKey;
window.handleOmniInputTyping = handleOmniInputTyping;
window.insertQuickReply = insertQuickReply;
window.handleOmniAttachmentClick = handleOmniAttachmentClick;
window.handleOmniResolveClick = handleOmniResolveClick;
window.openWhatsAppChat = openWhatsAppChat;
window.clearOmniTestChats = clearOmniTestChats;
window.generateBotAvatarSvg = generateBotAvatarSvg;
window.startOmniQuotedReply = startOmniQuotedReply;
window.cancelOmniQuotedReply = cancelOmniQuotedReply;
window.openOmniImageModal = openOmniImageModal;
window.closeOmniImageModal = closeOmniImageModal;
window.handleOmniFileSelected = handleOmniFileSelected;
window.removeOmniAttachment = removeOmniAttachment;
window.openSimulateChatModal = openSimulateChatModal;
window.closeSimulateChatModal = closeSimulateChatModal;
window.toggleSimMediaInputs = toggleSimMediaInputs;
window.submitSimulateChat = submitSimulateChat;
window.syncOmniChatsFromWhatsApp = syncOmniChatsFromWhatsApp;
window.parseOmniTimestamp = parseOmniTimestamp;
