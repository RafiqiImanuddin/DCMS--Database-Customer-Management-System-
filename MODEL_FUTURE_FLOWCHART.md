# Flowchart Pengembangan Model DCMS

Model masa depan bertujuan memprediksi probabilitas repeat purchase, risiko reaktivasi, dan rekomendasi item berikutnya. Model saat ini yang berbasis aturan RFM dijadikan baseline, lalu dibandingkan dengan beberapa kandidat machine learning menggunakan data historis.

```mermaid
flowchart TD
    A["Data sumber<br/>Excel / CSV Turnover"] --> B["Ingest dan validasi data"]
    B --> C{"Kolom wajib<br/>dan format valid?"}
    C -- "Tidak" --> D["Tolak data dan tampilkan<br/>daftar kolom bermasalah"]
    D --> B
    C -- "Ya" --> E["Pembersihan data<br/>tanggal, nilai transaksi, customer, item"]
    E --> F["Feature engineering<br/>RFM, perangkat, item, cabang, sales, tren"]
    F --> G["Pembuatan target<br/>repeat / reaktivasi / item berikutnya"]
    G --> H["Time-based train-test split"]
    H --> I["Baseline rule-based<br/>skor RFM saat ini"]
    H --> J["Candidate models<br/>Logistic Regression, Random Forest,<br/>Gradient Boosting, LightGBM/XGBoost"]

    I --> K["Evaluasi kandidat"]
    J --> K
    K --> L["Metrik<br/>Precision, Recall, ROC-AUC, PR-AUC,<br/>Lift dan nilai transaksi yang berhasil dijaga"]
    L --> M{"Model memenuhi<br/>threshold bisnis?"}
    M -- "Tidak" --> N["Tuning fitur dan hyperparameter<br/>atau koreksi target"]
    N --> F
    M -- "Ya" --> O["Cross-validation per periode"]
    O --> P{"Hasil stabil lintas<br/>periode dan cabang?"}
    P -- "Tidak" --> N
    P -- "Ya" --> Q["Pilih champion model"]
    Q --> R["Uji shadow / pilot terbatas"]
    R --> S{"A/B test menunjukkan<br/>hasil lebih baik?"}
    S -- "Tidak" --> I
    S -- "Ya" --> T["Deploy model dan AI service/API"]
    T --> U["Dashboard scoring pelanggan<br/>follow-up dan rekomendasi item"]

    U --> V["Monitor data drift,<br/>model drift, dan perubahan performa"]
    V --> W{"Performa turun atau<br/>data kadaluarsa?"}
    W -- "Tidak" --> X["Pertahankan model"]
    X --> V
    W -- "Ya" --> Y["Retraining dan evaluasi ulang"]
    Y --> H
```

## Rekomendasi Implementasi

- Gunakan model rule-based lama sebagai baseline agar peningkatan dapat diukur.
- Prioritaskan model Gradient Boosting atau LightGBM/XGBoost jika dataset cukup besar dan memiliki pola non-linear.
- Gunakan Logistic Regression jika fokus utama adalah interpretabilitas.
- Gunakan time-based split agar transaksi masa depan tidak bocor ke proses training.
- Tentukan threshold berdasarkan biaya follow-up dan nilai pelanggan, bukan sekadar mengambil threshold `0,5`.
- Jangan mengganti seluruh sistem sekaligus: jalankan model baru sebagai shadow/pilot dahulu.
- Retrain berkala berdasarkan performa dan ketersediaan data baru, misalnya bulanan atau saat metrik turun melewati batas.
