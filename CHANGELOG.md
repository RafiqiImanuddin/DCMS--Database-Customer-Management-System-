# Changelog DCMS Gadgetmart

## [V2.9.1] - 2026-11-13
### Penambahan Kolom Tim Follow Up (Nama & No. HP) dari Webhook Reaction WhatsApp & Sinkronisasi Realtime
- **Kolom Baru "TIM FOLLOW UP" pada Tabel Format Keep Customer (Tab 4)**:
  - Menambahkan kolom permanen **Tim Follow Up** di antara kolom `Report AR` dan `Status` pada tabel antarmuka Workspace Sosmed.
  - Memperluas lebar tabel (`min-w-[1360px]`) dan mengatur distribusi proporsi lebar kolom agar informasi staf terlihat rapi, jelas, dan proporsional.
- **Tampilan Identitas Staf Sales yang Mengklaim Transaksi**:
  - Menampilkan badge inisial avatar berwarna emerald, nama lengkap staf sales (diambil otomatis dari webhook reaction bot Baileys yang mencocokkan ke tabel master `employees`), serta nomor telepon WhatsApp.
  - Nomor telepon dilengkapi tautan langsung *Click-to-Chat* (`wa.me/{nomor}`) untuk mempermudah admin menghubungi staf yang bertugas.
  - Mendukung enkripsi/masking nomor telepon otomatis (`DataMasker`) ketika pengguna masuk sebagai Tamu/Guest mode.
- **Indikator Transaksi Belum Terklaim**:
  - Jika format keep belum mendapatkan respon emoji/reaksi dari tim toko cabang, kolom menampilkan status indikator `[Belum di-claim]` dengan penanda visual abu-abu halus (*waiting response*).
- **Sinkronisasi Pembaruan Realtime Tanpa Reload Browser**:
  - Memperbarui API `GET /api/sosmed-keeps/latest` agar mendukung parameter `updated_since`.
  - Frontend polling otomatis mendeteksi perubahan data format keep yang diklaim oleh bot Baileys di latar belakang dan memperbarui baris data di memori serta me-render ulang tabel dalam interval 3 detik tanpa mengganggu sesi pengguna.
- **Peningkatan Pencarian Fleksibel (Search Bar Filter)**:
  - Kotak pencarian format keep kini mendukung pencarian nama staf sales follow up dan nomor telepon sales, baik di sisi filter frontend JavaScript maupun backend query SQL.

## [V2.9.0] - 2026-11-12
### Engine Auto-Claim Format Keep via WhatsApp Reaction (Baileys Listener & Render.com Cloud Deployment)
- **Implementasi Bot Listener WhatsApp Baileys Mandiri (`baileys-bot/`)**:
  - Dibuat sebagai modul layanan mikro mandiri berbasis Node.js 20 dan `@whiskeysockets/baileys` yang siap di-deploy secara instan ke cloud PaaS gratis seperti **Render.com**.
  - Dilengkapi antarmuka Web Dashboard responsif (`GET /`) untuk memantau status koneksi, scan QR code langsung di peramban, serta fitur **Pairing Code 8-digit** (`POST /api/pair`) tanpa perlu akses terminal interaktif.
  - Listener event `messages.upsert` cerdas dengan buffer in-memory (1.000 pesan terakhir) untuk merekam nomor telepon konsumen dari format keep di grup.
  - Listener event `messages.reaction` untuk mendeteksi reaksi emoji (👍, 🙋‍♂️, ✅, dll.) dari staf sales di 18 grup WhatsApp cabang secara realtime 24/7.
  - Dilengkapi mekanisme anti-sleep interval otomatis untuk menjaga Web Service Render tetap aktif.
- **Logika "Siapa Cepat Dia Dapat" (*First-Reaction Wins*) pada Backend DCMS**:
  - Peningkatan controller `SosmedKeepWebhookController` pada aksi `UPDATE_REACTION_FU` dan `CLEAR_REACTION_FU`.
  - Pengecekan ketat: Transaksi format keep hanya terkunci kepada sales yang memberikan reaksi emoji pertama kali. Jika sales lain bereaksi belakangan pada keep yang sama, sistem otomatis menolak klaim dan mempertahankan sales pertama.
  - Otomatis mengubah status transaksi format keep menjadi **`ON PROGRESS`** begitu diklaim.
  - Pencocokan nomor WhatsApp sales secara otomatis ke master tabel `employees` di MySQL untuk mencatat nama lengkap sales di kolom `Nama Tim Follow Up`.
  - Dukungan fitur **Batal Jempol (*Unclaim*)**: jika sales yang mengunci mencabut reaksinya, transaksi dikembalikan ke status `PENDING` sehingga dapat diklaim oleh rekan kerja lainnya.
- **Arsip Konfigurasi Cloud Blueprint & Dokumentasi Lengkap**:
  - Berkas `baileys-bot/render.yaml` untuk deploy 1-klik di Render.com.
  - Berkas `baileys-bot/Dockerfile` untuk opsi deployment berbasis container/VPS.
  - Panduan deploy bertahap berbahasa Indonesia di `baileys-bot/README.md`.

## [V2.8.5] - 2026-11-12
### Tampilan Full Screen Live Display (/display), Auto-Lookup Cross-Selling Cepat O(1) MySQL, & Perbaikan Bug Indikator Turnover
- **Tampilan Full Screen Lebar Penuh (W-Full) di Live Office Display (`/display`)**:
  - Menghapus pembatasan `max-w-7xl` (1280px) pada header dan kontainer utama di `public-dashboard.blade.php`.
  - Mengubah layout menjadi `w-full` dengan padding adaptif (`px-4 sm:px-6 md:px-8`), sehingga seluruh kolom PIC Report 1 (Daniel, Marlina, Sifa, Marisa, Bintang, Qona, Febri, Dea, dll) dan tabel Report 2 terbentang lebar tanpa terpotong di layar TV kantor atau monitor widescreen.
- **Pembersihan Banner Status / Last Update Turnover pada Live Display**:
  - Menyembunyikan banner turnover indicator (`.turnover-report-banner`) sepenuhnya pada halaman publik display sehingga layout langsung fokus dan bersih ke tabel Report 1 tanpa memakan ruang vertikal layar TV.
  - Menghapus teks waktu pembaruan manual (*"File turnover terupdate per: ..."*) karena data transaksi turnover kini bersumber secara realtime langsung dari database MySQL.
- **Auto-Lookup Cross-Selling Langsung via API MySQL (`/api/croselling-records`)**:
  - Menyediakan endpoint API JSON berkecepatan tinggi `GET /api/croselling-records?format=client&sosmed_only=1` (~80KB) yang membaca 1.346 baris item faktur terkait sosmed langsung dari database MySQL dalam hitungan milidetik.
  - Memperbarui `loadCrosellingDataAsync()` untuk memprioritaskan endpoint SQL ini, menghindari ketergantungan download file Excel 6.6MB yang lambat.
  - Nilai Omzet (DPP) dan Gross Profit (GP) tetap murni bersumber dari transaksi turnover, sementara evaluasi unit Gadget dan akumulasi Aksesoris (>50K) ter-lookup secara presisi O(1) berdasarkan `Document Number`.
- **Perbaikan Bug TypeError & Indikator Turnover Banner**:
  - Memperbaiki fatal error `TypeError: Cannot set properties of null (setting 'innerText')` pada `renderReport2Tables` yang disebabkan elemen DOM yang tidak ada di tampilan publik (`timegoneDayBadge` dan `kpiTimegoneVal`).
  - Menambahkan proteksi null-guarding pada seluruh manipulasi DOM Report 2.
  - Memperbarui fungsi `setReportViewMode` agar mendukung mode `'all'` (menampilkan Report 1 dan Report 2 sekaligus) serta mengingat preferensi di `localStorage`.
- **Rencana & Proyeksi Rilis Berikutnya (Next Update / Roadmap)**:
  - *Engine Auto-Claim Format Keep via WhatsApp Reaction*: Integrasi listener Baileys di cloud PaaS otomatis (Render.com) untuk mendengarkan event `messages.reaction` di 18 grup WhatsApp cabang.
  - *Logika Siapa Cepat Dia Dapat*: Otomasi penguncian staf sales yang memberikan reaksi emoji pertama kali pada pesan format keep, mengubah status transaksi menjadi `ON PROGRESS` dan menetapkan `Followed Up By` secara instan tanpa perlu input manual.

## [V2.8.4] - 2026-11-12
### Tampilan Penuh Feedback Tim FU & Penghapusan Notifikasi Progress Update Status
- **Tampilan Penuh (Full Text) Feedback Tim FU**:
  - Mengubah input Feedback Tim FU dari single-line text yang terpotong menjadi multi-line `<textarea>` auto-wrap dan auto-expanding.
  - Memperluas lebar kolom tabel menjadi 17% (`min-w-[200px]`) dan menambahkan kalkulasi tinggi otomatis agar seluruh isi catatan tim FU terbaca utuh tanpa terpotong.
  - Tetap mempertahankan fitur autosave instan ke MySQL saat user selesai mengetik (`onchange`) dengan animasi indikator hijau.
- **Penghapusan Notifikasi Progress Simpan Status & AR**:
  - Menghapus pemanggilan pop-up progress bar hitam (`startSmoothProgress`) saat mengubah status transaksi atau nomor faktur AR.
  - Mengubah proses simpan status dan nomor AR menjadi operasi instan murni non-blocking di latar belakang tanpa mengganggu tampilan layar admin.

## [V2.8.3] - 2026-11-12
### Kolom Cabang Format Keep, Filter Kartu Metrik Interaktif (Clickable), & Pemetaan Grup WhatsApp
- **Kolom Cabang di Tabel Format Keep**:
  - Menambahkan kolom **Cabang** di antara kolom `Kode Sosmed` dan `Report AR` dengan badge visual elegan (`bg-sky-50 text-sky-800 border-sky-200/80`).
  - Mengintegrasikan pemetaan grup WhatsApp resmi (`GROUP_CABANG_MAP`) untuk 18 cabang/grup Gadgetmart (BJMC, Banjarbaru, Tanjung, Sampit, Balikpapan, Pontianak, Batulicin, Kotabaru, Kapuas, Martapura, Pelaihari, Amuntai, Sungai Danau, dll).
  - Mengintegrasikan nama cabang ke dalam input pencarian realtime format keep.
- **Kartu Metrik Interaktif (Clickable Status Filter)**:
  - Seluruh kartu metrik Format Keep (**Total Keep**, **Closing WIN**, **Need to Check**, **On Progress**, **Lose / Cancel**, dan **Closing Rate**) kini dapat diklik langsung untuk memfilter baris tabel.
  - Dilengkapi efek active ring highlight (`ring-2`) dan perubahan background halus untuk menandai kartu mana yang sedang aktif.
  - Fitur toggle interaktif: mengklik kembali kartu yang sedang aktif akan mereset filter kembali ke `ALL` (menampilkan semua data).
  - Angka metrik di setiap kartu tetap konsisten dan informatif saat filter status aktif.
- **Backend Auto-Detect Cabang & Migrasi Backfill**:
  - Memperbarui `FonnteService` dan `SosmedKeepWebhookController` agar otomatis mendeteksi cabang dan nama grup WhatsApp resmi dari `grup_id` pada setiap webhook masuk.
  - Menambahkan migrasi Laravel `2026_11_12_170000_backfill_sosmed_keeps_cabang_and_grup.php` untuk mem-backfill seluruh data transaksi format keep yang belum memiliki cabang di database MySQL.

## [V2.8.2] - 2026-11-12
### Pembersihan UI Sync Manual & Transformasi Pusat Notifikasi Realtime
- **Penghapusan Tombol Sync & Pill Status di Format Keep**:
  - Menghapus badge pill `Sumber: Realtime MySQL (Fonnte)` dan tombol `Refresh Realtime` di tab Format Keep karena sistem sudah terhubung penuh secara realtime otomatis.
- **Transformasi Dropdown Lonceng Header menjadi Pusat Notifikasi Murni**:
  - Menghapus kartu status sync manual (`Database Format Keep`, `Sync otomatis tiap 3 menit`, tombol `Sync Sekarang`).
  - Mengubah dropdown lonceng menjadi **Pusat Notifikasi** yang bersih dan lapang khusus untuk notifikasi aktivitas tim, upload data, dan alert sistem secara realtime.
- **Pembersihan Background Worker**:
  - Menonaktifkan polling periodic sync Google Sheets (`/api/sync-keep`) setiap 3 menit di `dcms-notification-sync.js`, menghemat resource server dan bandwidth.

## [V2.8.1] - 2026-11-12
### Sinkronisasi Timezone WITA (UTC+8), Optimasi Performa 240x Format Keep, dan Pembaruan Webhook Fonnte
- **Sinkronisasi Timezone WITA (Asia/Makassar, UTC+8)**:
  - Mengubah konfigurasi `APP_TIMEZONE` dan default Laravel menjadi `Asia/Makassar` (WITA / UTC+8).
  - Controller `SosmedKeepWebhookController` mencatat dan memformat `keep_timestamp` serta `server_time` secara presisi dalam zona waktu WITA.
  - Memperbarui parser tampilan frontend `formatKeepDisplayTime` dan `formatKeepDisplayDate` di `client.js` untuk mendeteksi ISO UTC dan menampilkannya akurat dalam waktu lokal WITA.
  - Menyesuaikan riwayat transaksi yang terekam pada hari ini (+8 jam) agar sinkron dengan jam pengiriman WhatsApp asli.
- **Optimasi Performa Pengambilan Data Format Keep (240x Lebih Cepat)**:
  - Menurunkan batas awal kueri `/api/sosmed-keeps` dari 25.000 baris menjadi 3.000 baris terbaru (seluruh data bulan berjalan).
  - Waktu muat terpangkas dari 26 detik (19.5 MB) menjadi hanya 0.19 detik (2.3 MB), mencegah timeout Error 524 pada Cloudflare Tunnel.
- **Pembaruan Parameter Device Webhook Fonnte**:
  - Mendaftarkan flag `group=true` dan `personal=true` ke API Fonnte untuk menjamin pesan dari chat pribadi maupun grup WhatsApp diteruskan tanpa hambatan.

## [V2.8] - 2026-11-12
### Sub-Menu Navigasi Format Keep, Auto-Lookup Cross-Selling vs Omzet & GP, Pembersihan Autofill Pencarian, dan Cloudflare Tunnel
- **Restrukturisasi Navigasi Sidebar Format Keep**:
  - Mengelompokkan menu **Cek Status AR** (Tab 3), **Tracking AR** (Tab 5), dan **Klaim AR** (Tab 6) menjadi sub-menu dropdown rapi di bawah menu induk **Format Keep** (Tab 4).
  - Dilengkapi animasi collapsible, rotasi chevron, dan penanda tab aktif yang sinkron dengan SPA Workspace Sosmed.
- **Auto-Lookup Cross-Selling SAP vs Omzet & Gross Profit (Report 2)**:
  - Pemetaan O(1) berbasis `Document Number` antara data omzet/GP (dataset turnover) dengan baris detail faktur (master cross-selling).
  - Melindungi integritas data finansial: angka Omzet dan GP tetap murni dihitung dari turnover, sementara jumlah transaksi gadget, cross-selling > 50K, dan rasio % Cross-Selling terisi otomatis tanpa distorsi KPI.
- **Penyelesaian Tuntas Masalah Autofill Input Pencarian (Format Keep Search)**:
  - Memperbaiki celah browser Chrome/Edge yang menembakkan email login (`superadmin@gmail.com`) ke dalam kolom pencarian saat membuka menu Format Keep.
  - Mengubah input menjadi `type="search"`, menambahkan `name="search_keep_filter_query"`, `autocomplete="new-password"`, `readonly onfocus="this.removeAttribute('readonly')"`, dan tombol 1-klik hapus pencarian (`✕`).
  - Penambahan pembersih otomatis `sanitizeKeepAutofill()` saat inisialisasi aplikasi dan perpindahan tab agar tabel tidak terfilter kosong.
- **Parser Fonnte Webhook Cerdas & Fleksibel**:
  - Penambahan dukungan pemisah strip (`-`) pada chat WA (seperti `Remarks - SOSMED 17`).
  - Fallback pemindai cerdas `SOSMED \d+` di seluruh badan pesan jika format pemisah tidak standar, mencegah terbuangnya data format keep (`IGNORED_NO_REMAKS`).
- **Integrasi Cloudflare Tunnel untuk Uji Coba Webhook Lokal**:
  - Dukungan Cloudflare Tunnel bebas limit bandwidth dan ber-SSL valid (`https://railway-month-proxy-middle.trycloudflare.com`) mengarah ke `127.0.0.1:8000`.
  - Penyediaan script 1-klik `start_cloudflare_tunnel.bat` untuk menjalankan tunnel kapan saja secara mandiri.
- **Pembaruan Paket Deploy cPanel**:
  - Sinkronisasi seluruh perubahan ke dalam arsip `dcms_cpanel_deploy.zip` dan file master `CROSSELLING-12102026.xlsx`.
  - Verifikasi seluruh 232 test cases (1.281 assertions) lulus 100%.

## [V2.7] - 2026-11-12
### Akun Tamu (Guest Mode Read-Only), Pencegahan Mutasi Data, dan Penyensoran Data Sensitif (Data Masking)
- **Opsi Login 1-Klik Akun Tamu (Guest)**:
  - Tombol masuk langsung sebagai tamu pada halaman login tanpa perlu kata sandi (`guest@gadgetmart.id`, role `guest`).
  - Badge identitas akun tamu di sidebar dan sticky warning banner *Mode Tamu (Guest • Read-Only)* di seluruh halaman.
- **Proteksi Read-Only & Pencegahan Modifikasi Global**:
  - Middleware global `PreventGuestModification` memblokir seluruh request HTTP mutasi (`POST`, `PUT`, `PATCH`, `DELETE`) dari akun tamu dengan respon `403 Forbidden` untuk AJAX/JSON dan redirect error untuk form web.
  - Penonaktifan & penyembunyian seluruh tombol mutasi: Tambah/Edit/Hapus, Unggah file impor, Bulk Assign, Blast Broadcast, Atur Shift Massal, dan Simpan Target.
- **Penyensoran Data Sensitif (Data Masking Backend & Frontend)**:
  - **Nomor HP Konsumen**: Disensor menjadi format `0812****890` di seluruh modul (Master Customer, Leads, Broadcast, Workspace Sosmed Cek AR & Format Keep), dan tautan langsung WhatsApp dinonaktifkan (`javascript:void(0)`).
  - **Pricelist Toko**: Nilai modal, harga jual, SRP, margin, cashback, dan subsidi leasing dinolkan dari backend API dan ditampilkan sebagai `Rp ***.***` di antarmuka serta surat penawaran harga.
  - **Pencapaian & Finansial**: Nilai nominal DPP, Gross, Gross Profit pada Workspace Sosmed dinolkan untuk akun guest, formatter JavaScript menampilkan `Rp ***.***` dan `***%`, serta nominal target bulanan disensor menjadi `***.***`.
  - **Blokir Download Mentah**: Endpoint ekspor dan download file Excel (`predictions.download`, `leads.download`, `monitoring.sales.export`) diblokir `403 Forbidden` untuk akun tamu.
- **Pengujian Otomatis & Verifikasi**:
  - Penambahan 11 skenario pengujian fitur `GuestAccessAndDataMaskingTest` dengan kelulusan 100% (total 232 test suite lulus).

## [V2.6] - 2026-11-12
### Fonnte Realtime Webhook, Auto-Matching SAP Cross-Selling (Closing WIN & Need to Check), dan Proteksi Feedback Frontline
- **Sinkronisasi Format Keep 100% Realtime dari Fonnte (Lepas dari Google Sheets)**:
  - Alur data Format Keep kini 100% langsung: **WhatsApp Tim Sosmed ➔ Fonnte API ➔ DCMS Database MySQL** secara realtime.
  - Impor dan migrasi penuh 13.367+ data riwayat keep ke tabel `sosmed_keeps` MySQL.
  - Penambahan proteksi auto-seed di server lokal dan endpoint `/system/migrate?key=dcms2026&action=import-keeps`.
- **Engine Auto-Matching SAP Cross-Selling vs Format Keep**:
  - Algoritma pencocokan cerdas berbasis faktur AR tunggal (`document_number`) dan nomor WhatsApp konsumen.
  - Pengakuan **Closing WIN** untuk aksesoris jika item pada faktur AR cocok dengan barang di format keep.
  - Validasi kategori gadget vs produk non-gadget (kulkas, TV, AC, microwave) untuk mencegah klaim keliru.
  - Fitur kategori baru **"NEED TO CHECK"**: jika nomor HP cocok namun barang/tipe berbeda, transaksi otomatis ditandai untuk kroscek admin dengan kartu metrik khusus warna amber di Tab 4.
- **Proteksi Integritas Kolom Feedback Frontline**:
  - Kolom `feedback_tim_fu` (Kolom W) dijaga murni untuk catatan manual admin dan tracking follow-up cabang (status deal, lost, otw, pending gajian, dll.).
  - Script auto-matcher SAP dan bot dilarang menimpa atau mengotori kolom feedback manual admin.
- **Dual-Layer Protection Error SQL 1054**:
  - Proteksi fallback `Schema::hasColumn('employees', 'level')` dan `Schema::hasColumn('employees', 'target')` pada seluruh controller pegawai dan shift sosmed agar tetap aman saat deploy ke cPanel.
- **Pembaruan Core Database & Backup Checkpoint**:
  - Ekspor skema 29 tabel terbaru `dcms_cpanel_core.sql` dan arsip data keep terkompresi `dcms_data_keeps.sql.zip`.

## [V2.5] - 2026-11-06
### Satu Gerbang Pengaturan: Integrasi Report Matrix dengan Data Karyawan & Bobot KPI
- **Integrasi Master Data Karyawan (Satu Gerbang Setting)**:
  - Kolom Report 1 (Daily Achievement Matrix) dan baris Report 2 (Achievement Omzet & GP vs Timegone serta Evaluasi Cross-Selling) kini sepenuhnya dinamis membaca dari master tabel `employees` di database.
  - Penambahan kolom `Target Omzet Bulanan (Rp)` pada data karyawan di menu Master Setting (`/settings/karyawan`), tabel karyawan, serta form modal tambah/edit sales.
  - Setiap perubahan karyawan aktif/nonaktif, penambahan admin sales baru, atau penyesuaian target individu di Master Setting langsung otomatis tercermin di Report Matrix Workspace Sosmed.
  - Algoritma pencocokan kode sales SAP Turnover yang cerdas: mencocokkan kode NIK resmi (`BJM.SOS.xxx`), alias (`SOSMED x`), maupun nama sales secara case-insensitive.
- **Konfigurasi Bobot KPI Matrix Dinamis**:
  - Tab *Bobot & Kriteria Penilaian* di `/settings/karyawan` kini dibagi menjadi 2 seksi transparan:
    1. **Bobot Rapor Matrix Sosmed**: Mengatur persentase bobot Omzet (default 35%), Gross Profit / GP (default 35%), dan Cross-Selling Gadget (default 30%).
    2. **Kriteria Kinerja Kualitatif**: Evaluasi layanan chat konsumen, respon time, etika, dan kepatuhan promo untuk monitoring harian manager sales.
  - Perhitungan persentase **% AKHIR (RAPOR)** dan status emoji performa (🖤/💔/☠️) di Report 2 kini dinamis mengikuti bobot yang ditentukan di Master Setting KPI.
  - Tag info standar pada tabel Report 2 otomatis menampilkan formula bobot yang sedang aktif secara realtime.
  - Endpoint baru `GET /api/workspace-settings` dan injeksi server-side `window.WORKSPACE_INITIAL_SETTINGS` untuk loading instan dengan 0ms latency.
- **Paket Deployment & Migrasi Database cPanel**:
  - Penambahan file migrasi `database/migrations/2026_11_06_100000_add_target_to_employees_table.php`.
  - Database dump `dcms_cpanel_core.sql` dan paket deployment `dcms_cpanel_deploy.zip` telah diperbarui sehingga pengguna dapat langsung redeploy ke cPanel atau menjalankan `/system/migrate?key=dcms2026`.

## [V2.4] - 2026-11-06
### Live Floating WhatsApp Widget, Relokasi Pusat Unggah Data & Super-Fast Bulk Deletion
- **Live Floating WhatsApp Widget di Seluruh Halaman (Global FAB)**:
  - Tombol aksi mengambang (*Floating Action Button*) hijau WhatsApp di pojok kanan bawah di seluruh halaman sistem (`layouts.live-chat-widget`).
  - Badge notifikasi berdenyut (*pulse animation*) dan suara *chime* lembut saat ada chat WhatsApp inbound baru yang belum dibalas.
  - Jendela ngambang (*floating window*) responsif untuk melihat percakapan masuk terbaru dan membalas chat secara ringkas secara instan via WAHA VPS API (`/api/watzap-send`).
  - Banner & tombol shortcut khusus *"Buka Modul Inbox FU Lengkap ↗"* untuk beralih ke Workspace Sosmed Tab 9 jika memerlukan respon kompleks, lampiran file/dokumen, atau pengisian Format Keep.
  - Auto-hide cerdas saat pengguna berada di Tab 9 (Inbox FU) di Workspace Sosmed agar tidak menumpuk.
- **Relokasi Menu Pusat Unggah Data ke Grup Setting**:
  - Memindahkan menu **Pusat Unggah Data** (`/imports`) dari grup *Customer & Outreach* ke dalam grup **Setting** di sidebar dengan badge **`SAP`**.
  - Penyesuaian highlight aktif `$isSettingActive` otomatis saat berada di halaman impor data.
- **Super-Fast Bulk Delete Pusat Unggah Data (Optimasi 98%)**:
  - Penghapusan riwayat impor batch besar (hingga 150.000+ baris transaksi) yang sebelumnya memakan waktu 2–4 menit kini selesai dalam **~2–3 detik**.
  - Menghapus pemanggilan PHP cursor `$predictor->rebuild()` yang berat pada saat aksi delete.
  - Menggunakan penghapusan bertahap (*chunked delete* per 25.000 baris) dengan *temporary foreign key check bypass* di MySQL.
  - Indikator status visual berupa spinner *"Menghapus..."* pada tombol konfirmasi.
- **Fix & Penyempurnaan Modul Omnichannel Inbox Follow-Up**:
  - Memperbaiki bug JavaScript `ReferenceError: mediaImg is not defined` di `client.js` sehingga pesan balasan teks langsung terkirim secara instan ke WhatsApp tujuan.
  - Dukungan pengiriman dan penerimaan file multimedia (foto/gambar dan dokumen PDF/Word/Excel) menggunakan format raw base64 langsung ke WAHA VPS (`/api/sendImage` & `/api/sendFile`).
  - Penanganan fallback cerdas pada *Quoted Reply* (kutipan pesan) di `WatzapService.php` agar pesan tetap terkirim walaupun ID pesan kutipan tidak tersimpan di memori WhatsApp.
  - Pembersihan formulir gateway cadangan (WatZap Cloud API) di halaman `/settings/watzap`, menyisakan fokus penuh pada WAHA VPS Primary dengan tombol shortcut langsung ke Inbox Follow-Up.
- **Rute Helper Migrasi Database cPanel Otomatis**:
  - Penambahan rute aman `/system/migrate?key=dcms2026` untuk memudahkan deployment di hosting cPanel yang tidak memiliki akses Terminal/SSH.
  - Otomatis menjalankan `php artisan migrate --force` dan `php artisan optimize:clear` langsung dari browser.
- **Paket Deployment Siap Pakai**:
  - Pembuatan file ZIP deployment otomatis `dcms-deploy-latest.zip` (37.45 MB) lengkap dengan folder `vendor/` dan aset Vite terkompilasi.

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

