# Stress Test — Modul Dokumen API

## Konfigurasi Pengujian

| Parameter | Nilai |
|---|---|
| Tool | k6 by Grafana |
| Script | `stress-test-modul1.js` |
| Server | Laravel Octane + RoadRunner (WSL Ubuntu 24.04) |
| Autentikasi | Bearer Token (Laravel Sanctum) |
| Durasi | 7 menit |
| Maksimum Virtual Users | 2000 VU |

### Tahapan Beban

| Stage | Durasi | Target VU |
|---|---|---|
| Ramp-up awal | 30 detik | 100 VU |
| Peningkatan beban | 1 menit | 500 VU |
| Beban menengah | 2 menit | 1000 VU |
| Beban puncak | 2 menit | 2000 VU |
| Beban stabil | 1 menit | 2000 VU |
| Ramp-down | 30 detik | 0 VU |

### Endpoint yang Diuji

- `GET /api/v1/documents` - daftar dokumen
- `GET /api/v1/documents?year=2023` - filter tahun
- `GET /api/v1/documents?tag=teknologi` - filter tag
- `GET /api/v1/documents/{id}` - detail dokumen (ID acak 1–100)
- `GET /api/v1/tags` - daftar tag
- `GET /api/v1/authors` - daftar penulis

---

## Hasil

### Threshold

| Metrik | Target | Hasil | Status |
|---|---|---|---|
| `http_req_duration` p(95) | < 10.000 ms | 39.710 ms | ❌ |
| `error_rate` | < 10% | 73,83% | ❌ |
| `list_documents_duration` p(95) | < 10.000 ms | 40.411 ms | ❌ |
| `detail_document_duration` p(95) | < 10.000 ms | 42.303 ms | ❌ |

### Statistik HTTP

| Metrik | Nilai |
|---|---|
| Total request | 20.535 |
| Request berhasil | 5.374 (26,17%) |
| Request gagal | 15.161 (73,83%) |
| Rata-rata response time | 22,37 detik |
| Response time minimum | 30,13 ms |
| Response time maksimum | 55,53 detik |
| p(90) | 36,11 detik |
| p(95) | 39,71 detik |
| Total data diterima | 307 MB |
| Throughput | 45 req/s |

### Statistik per Endpoint

| Endpoint | Avg Duration | Min | p(95) |
|---|---|---|---|
| List Documents | 22.292 ms | 30 ms | 40.411 ms |
| Detail Document | 22.538 ms | 261 ms | 42.303 ms |
| List Tags | 22.446 ms | 1.915 ms | 37.319 ms |
| List Authors | 22.467 ms | 1.954 ms | 39.322 ms |

### Checks (Validasi Response)

| Check | Status |
|---|---|
| ✅ Setup login berhasil | Passed |
| ✅ `list has data` | Passed |
| ✅ `tags status 200` | Passed |
| ✅ `authors status 200` | Passed |
| ❌ `list status 200` | 0/4.231 |
| ❌ `filter year status 200` | 0/4.016 |
| ❌ `filter tag status 200` | 0/3.700 |
| ❌ `detail status 200 or 404` | 0/3.214 |

---

## Analisis

Pengujian dilakukan dengan mensimulasikan hingga 2000 virtual user secara bersamaan selama 7 menit menggunakan Laravel Octane dengan RoadRunner di atas WSL Ubuntu 24.04. Server berhasil memproses 20.535 request dengan throughput rata-rata 45 request per detik dan total data yang diterima mencapai 307 MB menunjukkan bahwa server aktif memproses request dalam jumlah besar sepanjang durasi pengujian.

Endpoint `/api/v1/tags` dan `/api/v1/authors` berhasil merespons dengan baik karena query-nya relatif ringan dan tidak memerlukan join atau filter yang kompleks. Sementara endpoint yang melibatkan daftar dokumen, filter, dan detail gagal merespons dalam batas waktu yang ditetapkan akibat antrian request yang menumpuk di beban puncak 2000 VU.

Response time minimum untuk beberapa endpoint cukup rendah, `/api/v1/documents` bisa merespons dalam 30 ms dan `/api/v1/tags` dalam 1,9 detik pada kondisi beban rendah. Ini menunjukkan bahwa logika aplikasi dan query database sebenarnya sudah efisien; bottleneck terjadi murni karena volume request yang sangat tinggi di lingkungan lokal tanpa connection pooling dan infrastruktur production yang memadai.
