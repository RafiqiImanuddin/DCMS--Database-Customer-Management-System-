# Changelog DCMS Gadgetmart

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
