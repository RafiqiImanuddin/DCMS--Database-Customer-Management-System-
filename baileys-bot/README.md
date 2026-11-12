# ⚡ DCMS Baileys Reaction Bot (Auto-Claim Format Keep via WhatsApp)

Bot listener WhatsApp berbasis [Baileys](https://github.com/WhiskeySockets/Baileys) dan **Express.js** yang dirancang khusus untuk di-deploy secara mandiri di cloud PaaS seperti **[Render.com](https://render.com/)**, VPS, atau Railway.

Bot ini bertugas mendengarkan reaksi emoji (jempol 👍, acungan tangan 🙋‍♂️, centang ✅, dsb.) dari tim frontline toko/cabang pada pesan Format Keep di 18 grup WhatsApp Gadgetmart, lalu secara otomatis mengunci transaksi tersebut di sistem DCMS dengan prinsip **"Siapa Cepat Dia Dapat" (*First-Reaction Wins*)**.

---

## 🚀 Alur Kerja Sistem (Workflow)

```
[Admin Sosmed] ➔ Kirim pesan Format Keep ke Grup WhatsApp Toko Cabang
                      │
                      ▼
[Staf Sales Toko] ➔ Memberi reaksi emoji (👍) pada pesan Format Keep
                      │
                      ▼
[Baileys Bot di Render.com] ➔ Menangkap event 'messages.reaction'
                      │   - Ekstraksi Message ID, Nomor Sales, & Emoji
                      ▼
[DCMS Webhook API] ➔ POST /api/fonnte-webhook (action: UPDATE_REACTION_FU)
                      │
                      ├─► Jika belum ada yang klaim:
                      │   ✅ Status diubah menjadi "ON PROGRESS"
                      │   ✅ Kolom Nomor FU & Nama Tim FU otomatis terisi nama sales
                      │
                      └─► Jika sudah diklaim sales lain lebih dulu:
                          ⛔ Klaim ditolak ("Sudah diklaim lebih dulu oleh [Nama Sales]")
```

---

## 🛠️ Panduan Deploy ke Render.com (Gratis & 5 Menit Jadi)

### Langkah 1: Siapkan Akun di Render.com
1. Buka [https://render.com/](https://render.com/) dan login menggunakan akun GitHub Anda.

### Langkah 2: Buat Web Service Baru
1. Pada Dashboard Render, klik tombol **New +** ➔ pilih **Web Service**.
2. Pilih opsi **Build and deploy from a Git repository**.
3. Pilih repository Anda: `DCMS--Database-Customer-Management-System-`.

### Langkah 3: Konfigurasi Pengaturan Web Service
Isi formulir konfigurasi dengan nilai berikut:
* **Name:** `dcms-baileys-reaction-bot` (atau nama pilihan Anda)
* **Region:** `Singapore` (terdekat dengan Indonesia untuk latensi terendah)
* **Branch:** `main`
* **Root Directory:** `baileys-bot` *(PENTING: arahkan ke folder bot)*
* **Runtime:** `Node`
* **Build Command:** `npm install`
* **Start Command:** `npm start`
* **Instance Type:** `Free` (atau Starter)

### Langkah 4: Tambahkan Environment Variables
Scroll ke bawah ke seksi **Environment Variables**, tambahkan:

| Key | Value Contoh | Keterangan |
| :--- | :--- | :--- |
| `NODE_VERSION` | `20.12.0` | Versi Node.js LTS |
| `DCMS_WEBHOOK_URL` | `https://dcms-sosmed.my.id/api/fonnte-webhook` | URL webhook cPanel / domain DCMS Anda |
| `DCMS_API_KEY` | `dcms2026` | Kunci otentikasi header API |
| `TARGET_GROUPS` | *(kosongkan)* | Kosongkan untuk memproses semua grup, atau isi koma ID grup tertentu |

Klik tombol **Create Web Service**.

---

## 📱 Langkah 5: Hubungkan WhatsApp (Scan QR atau Pairing Code)

Setelah proses build Render selesai (biasanya ~1–2 menit):
1. Buka URL Web Service Render Anda di browser (contoh: `https://dcms-baileys-reaction-bot.onrender.com`).
2. Anda akan melihat **Web Dashboard DCMS Baileys Reaction Bot**:
   * **Metode 1 (Scan QR):** Buka WhatsApp di ponsel ➔ Titik tiga / Pengaturan ➔ **Perangkat Tertaut** ➔ **Tautkan Perangkat** ➔ Scan kode QR di layar.
   * **Metode 2 (Pairing Code):** Masukkan nomor telepon WhatsApp listener (contoh: `628123456789`) pada kotak input di bawah QR ➔ Klik **Dapatkan Kode Pairing** ➔ Masukkan 8 digit kode yang muncul ke WhatsApp Anda.
3. Begitu terhubung, status di dashboard akan berubah menjadi:  
   `🟢 TERHUBUNG (628xxxxxx - Bot WhatsApp)`

---

## 🎯 Fitur & Keunggulan

1. **Logika Siapa Cepat Dia Dapat (*First-Reaction Wins*):**
   * Transaksi hanya terkunci ke staf sales yang memberikan reaksi emoji pertama kali.
   * Reaksi dari sales kedua atau ketiga pada format keep yang sama akan ditolak secara otomatis dan tidak akan menimpa pemilik klaim pertama.
2. **Auto-Detect Nama Sales dari Database Pegawai:**
   * Nomor WhatsApp pengirim reaksi otomatis dicocokkan ke master tabel `employees` di DCMS, sehingga kolom `Nama Tim Follow Up` langsung terisi nama lengkap staf toko.
3. **Dukungan Batal Jempol (*Unclaim*):**
   * Jika staf yang mengunci mencabut jempolnya, bot mengirim event `CLEAR_REACTION_FU` sehingga status transaksi kembali menjadi `PENDING` dan dapat diklaim ulang oleh rekan kerja lainnya.
4. **Dashboard Pemantau Realtime:**
   * Dilengkapi tabel riwayat reaksi langsung di browser untuk melihat siapa saja yang mengklaim format keep beserta respons dari sistem DCMS.
5. **Anti-Sleep Interval untuk Render Free Tier:**
   * Script otomatis melakukan *self-ping* berkala untuk mencegah Web Service Render masuk mode *sleep*.

---

## 🧪 Menjalankan di Komputer Lokal (Testing / Dev)

Jika ingin mencoba di localhost terlebih dahulu:
```bash
cd baileys-bot
npm install
npm start
```
Buka browser di `http://localhost:3000`.
