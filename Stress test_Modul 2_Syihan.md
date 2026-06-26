# 📊 Analisis Hasil Stress Test - E-JOURNAL TIM 8

Dokumen ini berisi laporan dan analisis hasil simulasi *stress testing* (*load testing*) yang dilakukan secara internal terhadap endpoint pencarian dokumen:
`GET /api/v1/documents/search` dengan parameter pencarian dinamis (acak).

---

## 🚀 Ringkasan Eksekusi
* **Target Server**: Laravel Octane (RoadRunner)
* **Target Endpoint**: `http://127.0.0.1:8000/api/v1/documents/search`
* **Metode Pengujian**: Simulasi konkurensi bertingkat (*asynchronous HTTP pool*) dari **10** hingga **2000** *Virtual Users* (VU) bersamaan.
* **Tingkat Keberhasilan**: **100%** (0 request gagal di semua tingkat beban).

---

## 📈 Data Hasil Pengujian

Berikut adalah tabel performa sistem berdasarkan jumlah *Virtual Users* bersamaan:

| Virtual Users (VU) | Total Waktu (ms) | Rata-rata per User (ms) | Sukses | Gagal | Total Payload (MB) | Rerata Payload/User (KB) |
| :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **10** (Warm-up) | 5,292.18 | 529.22 | 10 | 0 | 0.10 | 10.47 |
| **50** | 398.75 | 7.98 | 50 | 0 | 0.51 | 10.47 |
| **100** | 662.18 | 6.62 | 100 | 0 | 1.02 | 10.47 |
| **200** | 1,545.22 | 7.73 | 200 | 0 | 2.05 | 10.47 |
| **500** | 4,407.47 | 8.81 | 500 | 0 | 5.11 | 10.47 |
| **1000** | 11,970.90 | 11.97 | 1000 | 0 | 10.23 | 10.47 |
| **2000** | 25,421.29 | 12.71 | 2000 | 0 | 20.45 | 10.47 |

---

## 📊 Hasil Benchmark Performa (Database vs Redis)

Untuk mengukur dampak optimasi secara terisolasi pada database dan cache, kami melakukan benchmark internal (`php artisan benchmark:run`) dengan data dummy sebanyak 5000+ dokumen. Berikut adalah hasil pengujian performa query:

### 1. Pengujian Single Query (1 User)
* **Pencarian tanpa Index Database**: **275.92 ms**
* **Pencarian dengan Index Database**: **1.13 ms** (🚀 **244.2x lebih cepat**)
* **Pencarian menggunakan Redis Cache (RAM)**: **0.58 ms** (⚡ **11.8x lebih cepat** dibandingkan DB ter-indeks)

### 2. Pengujian Konkurensi (Simulasi 100 Users / 100 Hits)
* **Mengambil dari Database (Tanpa Index)**: **24,391.82 ms** (24.39 detik)
* **Mengambil dari Database (Dengan Index)**: **96.95 ms**
* **Mengambil dari Redis Cache**: **43.18 ms** (⚡ **2.2x lebih cepat** dari DB ter-indeks, atau **564.8x lebih cepat** dibanding DB tanpa indeks)

---

## 🔍 Analisis Mendalam & Korelasi

### 1. ⚙️ Efek Warm-Up (Fase Awal)
* **Gejala**: Pengujian pertama dengan **10 Users** membutuhkan rata-rata waktu **529.22 ms per request**, jauh lebih lambat dibanding saat beban bertambah.
* **Penyebab**: Hal ini wajar karena sistem melakukan inisialisasi awal (*cold start*) yang mencakup pembukaan koneksi database, pembentukan cache Redis, dan *bootstrap* framework.
* **Korelasi dengan Benchmark**: Waktu respon cold start ini sebanding dengan performa database tanpa index dan inisialisasi awal Redis connection.

### 2. ⚡ Peran Krusial Database Indexing pada Beban Konkuren
* Tanpa indeks, memproses 100 request saja memakan waktu hingga **24.39 detik** (sistem langsung bottleneck).
* Dengan penambahan indeks (`idx_documents_author_year`), waktu pemrosesan untuk 100 request terpangkas menjadi **96.95 ms**. Hal ini membuktikan bahwa database index adalah fondasi utama sebelum caching diterapkan.

### 3. 💾 Efisiensi Redis Caching pada Load Ekstrem
* Caching di Redis memangkas waktu akses single query hingga kurang dari **1 ms** (**0.58 ms**).
* Pada simulasi beban tinggi (1000 hingga 2000 Virtual Users), rata-rata response time terjaga sangat stabil di kisaran **11 ms - 12 ms** karena beban query tidak pernah menyentuh database utama (MySQL/PostgreSQL), melainkan langsung dilayani oleh memori RAM (Redis) dengan tingkat keberhasilan **100%**.

---

## 💡 Rekomendasi Selanjutnya
1. **Penerapan Index secara Konsisten**: Pastikan seluruh query pencarian yang menggunakan filter kolom tertentu selalu didukung oleh *composite index* di database untuk mengantisipasi jika terjadi *cache miss* (kedaluwarsa atau belum ter-cache).
2. **Database Connection Pool**: Karena RoadRunner menjaga koneksi database tetap hidup (*persistent*), pastikan parameter `max_connections` database utama cukup besar untuk menampung lonjakan koneksi apabila cache Redis dibersihkan secara massal.
3. **Rate Limiting & Gzip**: Terapkan rate limiter untuk mencegah serangan DDoS dan aktifkan kompresi GZIP/Brotli pada web server untuk meminimalkan beban payload transmisi data.
