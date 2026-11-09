# DCMS (Database Customer Management System) — GadgetMart

> **Enterprise Customer Relationship Management, Omni-Channel Outreach, & Sales Performance Matrix System.**  
> Dibangun dengan arsitektur modern berbasis **Laravel 12 (PHP 8.5)**, **Tailwind CSS**, dan integrasi database **MySQL**.

---

## 🌟 Daftar Fitur yang Tersedia (Current Features)

### 1. Customer & Outreach Management
- **Dashboard Customer Analytics:** Metrik ringkasan eksekutif (Total Pelanggan, Leads Baru, Pelanggan Aktif, Tingkat Repeat Order, dan Tren Omzet).
- **Master Customer Database:** Sentralisasi data pelanggan lintas cabang GadgetMart dengan profil riwayat transaksi, nomor WhatsApp terverifikasi, dan analisis segmentasi RFM.
- **Leads Customer:** Pelacakan prospek prospektif dari berbagai kanal sosial media dan chat.
- **Laporan Broadcast WhatsApp:** Rekap dan monitoring pengiriman pesan broadcast berbasis template dinamis terintegrasi gateway Watzap.

---

### 2. Workspace Sosmed & Report Matrix
- **Collapsible Sub-Bar Navigation:** Navigasi sub-menu fleksibel di sidebar yang dapat dibuka/tutup secara responsif untuk Report Matrix, Report 1 (Daily Matrix), dan Report 2 (Omzet & GP vs Timegone).
- **Report 1: Daily Achievement Matrix:**
  - Matriks pencapaian omzet harian sales (Tanggal 1–31).
  - **Level Karyawan Prioritized Sorting:** Otomatis mengurutkan kolom sales bertipe **`Karyawan`** di bagian awal, disusul oleh sales **`Internship`** (lengkap dengan badge penanda `INTERN`).
  - Total harian, rata-rata, akumulasi per week, dan progress bar terhadap target individu.
- **Report 2: Omzet & GP vs Timegone:**
  - Analisis perbandingan performa Omzet dan Gross Profit (GP 5%) terhadap laju hari berjalan (*timegone*).
  - Mode kalkulasi ganda: **Nilai DPP** (sebelum PPN 11%) atau **Nilai Gross** (setelah PPN).
  - Proyeksi akhir bulan (*run rate projection*) dan identifikasi kesenjangan (*target gap*).
- **Executive KPI Cards & Speedometer Gauges:** Visualisasi skor komposit performa sales real-time.
- **Cek Status AR & Verifikasi Sosmed:** Validasi instan nomor faktur AR terhadap database transaksi turnover dan cross-selling.

---

### 3. Format Keep Customer (Booking & Order Hold)
- **Desain Selaras Customer & Outreach:** Tampilan konsisten dengan palet warna DCMS brand GadgetMart.
- **5 KPI Summary Cards:**
  - *Total Keep* (Total transaksi hold)
  - *Closing WIN* (Transaksi sukses closing)
  - *On Progress* (Sedang dalam proses follow-up)
  - *Lose / Cancel* (Batal / tidak respon)
  - *Closing Rate %* (Rasio keberhasilan closing)
- **Pencarian & Multi-Filter:** Filter cepat berdasarkan periode bulan, tanggal spesifik, kode sosmed, dan status keep.
- **Interactive Modals:**
  - Modal **Input Faktur AR** terintegrasi validasi database.
  - Modal **Pilih Status Keep** 1-klik (*WIN*, *Proses*, *Cancel*, dll.).
- **Feedback Tim FU Inline Textbox:** Kolom catatan feedback tim follow-up berupa input langsung dengan indikator penyimpanan otomatis (*auto-save*).

---

### 4. Tracking AR & Klaim AR
- **Tracking Faktur AR:** Pelacakan status faktur penjualan dari admin cabang ke finance.
- **Klaim Komisi AR:** Verifikasi pencairan klaim reward transaksi penjualan admin sales sosmed.

---

### 5. Inbox Follow-Up & Integrasi WhatsApp Gateway (Watzap)
- **Omni-Channel Chat Window:** Live chat follow-up pelanggan langsung dari dashboard.
- **Sinkronisasi Kontak & Pesan:** Deteksi nomor WhatsApp otomatis dan pengiriman pesan follow-up 1-klik.

---

### 6. Monitoring Kinerja Sales & Evaluasi (Manager View)
- **Monitoring Kualitas Kerja:** Evaluasi kualitatif respon chat, keramahan, kepatuhan promo, dan disiplin SOP.
- **AI Performance Diagnostic:** Analisis kekuatan dan area perbaikan sales dengan format WhatsApp text report instan untuk evaluasi berkala.

---

### 7. Master Setting / Karyawan & Bobot KPI (Satu Gerbang)
- **Manajemen Data Karyawan Sales:**
  - Penambahan, pengeditan, dan penghapusan master data sales (NIK/ID Karyawan, Cabang, Role, WhatsApp, Email, Catatan).
  - **Status Level Karyawan (2 Status):** Pilihan kategori **`Karyawan`** atau **`Internship`** yang terintegrasi langsung dengan tata letak Daily Matrix.
  - Filter data berdasarkan Cabang, Status Aktif/Nonaktif, dan Level Karyawan.
- **Pengaturan Target Omzet Bulanan:**
  - Sinkronisasi target tim bulanan dan target individu sales.
  - Fitur *Bagi Rata Otomatis* dan *Hitung Penjumlahan Sales*.
  - Modal target responsif dengan layout adaptif (*auto-fit*).
- **Pengaturan Bobot KPI:** Konfigurasi persentase Rapor Matrix (Omzet, GP, Cross-Selling) dan kriteria kualitatif layanan manajerial dengan validasi tepat 100%.

---

### 8. Pusat Unggah Data 1-Pintu (Unified Data Import)
- **Single Portal Upload:** Pengunggahan seluruh data operasional (file Excel `turnover.xlsx`, file `CROSSELLING`, dan CSV `keep-data.csv`) terpusat di `/imports`.
- **Validasi & Proteksi Data:** Menjamin format file sesuai standar sistem sebelum diproses ke database.

---

## 🚀 Rencana Pembaruan Selanjutnya (Next Updates / Roadmap)

1. **🔔 Notifikasi Otomatis WhatsApp via Watzap Gateway (Trigger Closing WIN):**
   - Mengirimkan pesan konfirmasi otomatis ke nomor pelanggan dan notifikasi ke kepala toko saat status Keep diubah menjadi *Closing WIN*.
2. **🔌 Integrasi Real-Time SAP ERP API Gateway:**
   - Sinkronisasi API langsung dengan ERP SAP perusahaan untuk data cross-selling dan faktur turnover tanpa unduh/unggah file Excel manual.
3. **🏢 Granular Multi-Branch Role & Permission Hierarchy:**
   - Pembagian hak akses berjenjang yang lebih spesifik antara *Superadmin*, *Manager Area*, *Kepala Toko Cabang*, *Admin Sales*, dan *Finance*.
4. **🧠 AI Smart Cross-Selling Recommendation Engine:**
   - Rekomendasi produk aksesoris pendukung cerdas (*upselling / cross-selling*) berdasarkan profil transaksi dan kategori unit yang dibeli pelanggan.
5. **📊 Automated PDF / Excel Report Scheduler:**
   - Penjadwalan pengiriman otomatis Rapor Matrix Mingguan dan Bulanan ke email Direksi dan grup WhatsApp Manajerial.
6. **📱 Mobile-Optimized Executive View:**
   - Pengoptimalan tampilan antarmuka seluler untuk dashboard monitoring manajer saat berada di luar toko.

---

## 💻 Panduan Instalasi & Menjalankan Proyek

### Prasyarat
- **PHP** >= 8.2 (Direkomendasikan PHP 8.5)
- **Composer** >= 2.x
- **Node.js** >= 18.x & **NPM**
- **MySQL Database**

### Langkah-langkah
```bash
# 1. Clone repositori
git clone <repository_url>
cd dcms

# 2. Install dependensi PHP & JavaScript
composer install
npm install

# 3. Salin environment dan generate application key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi koneksi database di file .env, lalu jalankan migrasi
php artisan migrate

# 5. Kompilasi asset frontend
npm run build

# 6. Jalankan web server lokal
php artisan serve
```

---

## 🧪 Pengujian Sistem
Proyek ini dilengkapi dengan rangkaian pengujian fitur dan unit komprehensif:
```bash
php artisan test
```
*Status Terakhir:* **158 Passed (930 Assertions)** ✅

---

## 📄 Lisensi
Sistem ini bersifat hak milik eksklusif **GadgetMart**. Seluruh hak cipta dilindungi.
