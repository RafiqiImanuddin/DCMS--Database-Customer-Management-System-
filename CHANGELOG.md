# Changelog DCMS Gadgetmart

## [V2.3] - 2026-11-05
### Migrasi cPanel, Sinkronisasi Realtime Workspace Sosmed & Deteksi Periode Dinamis
- **Sinkronisasi Otomatis Pusat Unggah Data &rarr; Workspace Sosmed**:
  - File Cross-Selling dan Turnover yang diunggah melalui halaman Pusat Unggah Data (`/imports`) kini langsung disinkronkan secara realtime ke database `Setting` (`workspace_turnover_file_base64`, `workspace_croselling_file_base64`) dan file fisik publik (`public/workspace-sosmed/`).
  - Pengguna tidak perlu lagi melakukan re-upload manual di halaman Workspace Sosmed.
  - Peramban diubah agar selalu memprioritaskan mengambil file terbaru dari server API (`/api/get-excel?type=turnover`) sebelum menggunakan cache lokal IndexedDB.
- **Deteksi Periode Dinamis November 2026 & Resolusi Tanggal SAP**:
  - Engine Report Matrix kini otomatis mendeteksi periode bulan dan tahun aktif dari data transaksi turnover (mendukung 32.322+ baris transaksi November 2026).
  - Judul laporan (*Daily Achievement Sosmed*), jumlah hari (30 hari), dan target bulanan otomatis beradaptasi tanpa hardcoded Oktober.
  - Penanganan khusus *Inverted US-Locale Date* (`MM/DD/YYYY`) dari SAP untuk bulan November (`d.d === activeMonth`).
  - Dropdown filter Format Keep dan Audit AR kini membaca seluruh periode unik dari data Google Sheets secara dinamis dan otomatis memilih periode terbaru (`November-26 (Terbaru)`).
- **Fitur Hide / Collapse Sidebar Desktop (Open Workspace)**:
  - Sidebar desktop kini dapat di-minimize/sembunyikan secara halus (`margin-left: -18rem`, opacity 0, pointer-events none) agar area workspace report & tabel spreadsheet menjadi luas dan leluasa.
  - Ditambahkan tombol toggle collapse dedicated (`<<`) di bagian atas sidebar serta tombol hamburger di topbar yang selalu dapat diakses.
  - Ditambahkan keyboard shortcut global: `Ctrl+B` atau `Alt+S` untuk toggle sidebar instan.
  - Status collapse tersimpan di `localStorage` (`dcms_sidebar_collapsed`) dengan inline script pada `<head>` untuk mencegah efek loncat/flicker saat refresh.
- **Perbaikan Routing Controller & Stabilitas Engine**:
  - Pendaftaran rute `imports.destroy` dan `imports.destroy_turnover` pada `RouteCipher` untuk mencegah error routing controller saat menghapus data riwayat impor.
  - Proteksi `try/catch` pada rebuilding model repeat order predictor saat proses hapus impor agar tidak memicu timeout atau fatal error.
  - Memperbaiki deklarasi fungsi `handleOmniResolveClick` pada Omnichannel Inbox.
  - Isolasi inisialisasi awal aplikasi (`initSosmedApp`) dengan proteksi `try/catch` mandiri dan pengecekan aman `Array.isArray()` pada `renderArchiveGrid()`, sehingga kegagalan satu modul tidak menghentikan modul lainnya.
- **Migrasi Hosting cPanel & MySQL Localhost**:
  - Migrasi koneksi database dari Supabase PostgreSQL ke MySQL lokal dan cPanel (`dcms-sosmed.my.id`).
  - Penyediaan berkas dump database siap impor `dcms_cpanel_full.sql.zip` dan paket deploy siap pakai `dcms_cpanel_deploy.zip`.
  - Verifikasi menyeluruh: seluruh **148 automated tests** lulus 100% (green).

## [V2.2] - 2026-11-05
### Monitoring Kinerja Sales KPI & Manajemen Bobot
- **Modul Monitoring Kinerja Sales**:
  - Dashboard evaluasi KPI harian sales sosmed dengan pemisahan akses: Superadmin memiliki hak evaluasi & setting bobot, Admin view-only.
  - Filter cabang dan reset 24 jam untuk laporan broadcast.

## [V2.1] - 2026-11-04
### Fitur Baru & Peningkatan
- **Broadcast Delay & Proteksi Anti-Banned WhatsApp**:
  - Penambahan input konfigurasi delay per pengiriman pesan pada pembuatan broadcast WhatsApp.
  - Delay acak aman untuk meminimalkan risiko nomor terblokir / suspend oleh sistem spam filter WhatsApp.
  - Laporan status dan batch pengiriman broadcast per kampanye.
- **Integrasi WhatsApp Gateway WatZap (Broadcast & Inbox Follow-Up)**:
  - Pemisahan nomor sender antara WA Broadcast promosi dan nomor CS/Inbox Follow-Up.
  - Tombol pendaftaran otomatis Inbound Webhook (`/api/watzap-webhook`) langsung ke server WatZap via API.
  - Tombol pilihan cepat (*One-Click Selector*) nomor aktif yang terhubung di akun WatZap.
  - Normalisasi otomatis typo font antara huruf `l` (L kecil) dan `I` (i kapital) pada device number key.
  - Proteksi autofill browser password manager pada kolom API Key.
- **Penyempurnaan Antarmuka & UX**:
  - Perbaikan tuntas issue dropdown double / duplikat di seluruh modul aplikasi (Leads Customer, Prediksi RO, Laporan Broadcast, dll.).
  - Menggunakan native single `<select>` modern dengan chevron SVG custom dan transisi fokus yang halus.
  - Pembersihan header roomchat Workspace Sosmed (Inbox FU), menyisakan indikator status Online/Offline WhatsApp Gateway.

