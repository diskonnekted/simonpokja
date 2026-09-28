# Dokumentasi API SIMONPOKJA

> REST API integrasi untuk konsumen eksternal (SIBIJAK).
> Versi terkini: **v1** — Status: **read-only (GET)**.

Dokumen ini menjelaskan cara memanggil API SIMONPOKJA (Sistem Informasi Monitoring
Kinerja Pokja LPSE Kabupaten Banjarnegara) agar data pengadaan, Pokja, dan hasil
pemantauannya dapat diambil aplikasi lain secara terprogram.

---

## Daftar Isi

1. [Gambaran Umum](#1-gambaran-umum)
2. [URL Dasar & Lingkungan](#2-url-dasar--lingkungan)
3. [Autentikasi](#3-autentikasi)
4. [CORS](#4-cors)
5. [Format Respons Standar](#5-format-respons-standar)
6. [Status Kode & Penanganan Error](#6-status-kode--penanganan-error)
7. [Daftar Endpoint](#7-daftar-endpoint)
8. [Detail Endpoint](#8-detail-endpoint)
9. [Kamus Nilai (Enum)](#9-kamus-nilai-enum)
10. [Model Data & Relasi](#10-model-data--relasi)
11. [Contoh Integrasi](#11-contoh-integrasi)
12. [Catatan & Batasan](#12-catatan--batasan)

---

## 1. Gambaran Umum

API ini menyediakan akses **baca (GET)** ke data:

- **Master data**: OPD, Pokja (lengkap dengan indikator kinerja), dan Penyedia.
- **Paket pengadaan**: daftar/filter, detail lengkap, dan seluruh data turunannya
  (tahapan, evaluasi, sanggahan, progres pekerjaan, perubahan jadwal, pesan, alert anomali, audit checklist).
- **Data monitoring** global lintas paket.
- **Agregat/statistik** siap tampil untuk dashboard.

Semua respons berformat **JSON** dan memakai envelope baku (lihat [§5](#5-format-respons-standar)).

---

## 2. URL Dasar & Lingkungan

| Lingkungan | URL Dasar |
|---|---|
| Pengembangan lokal | `http://127.0.0.1:8081/api/v1` |
| Produksi | `https://<domain-anda>/api/v1` |

Seluruh endpoint ditulis relatif terhadap URL dasar tersebut.
Contoh lengkap: `http://127.0.0.1:8081/api/v1/paket?tahun=2026`.

---

## 3. Autentikasi

Setiap permintaan harus menyertakan **token bearer** pada header:

```http
Authorization: Bearer <token>
```

Token dikelola oleh admin SIMONPOKJA melalui variabel `SIBIJAK_API_TOKEN` pada `.env`.
Mendukung **beberapa token** yang dipisahkan koma:

```env
SIBIJAK_API_TOKEN=token-sibijak-1,token-sibijak-2
```

> Untuk pengujian cepat saja, token juga dapat dikirim lewat query string:
> `GET /api/v1/paket?token=<token>`. **Jangan** gunakan cara ini di produksi.

**Tanpa token / token salah**, API mengembalikan:

```json
HTTP/1.1 401 Unauthorized
{
  "success": false,
  "message": "Tidak terautentikasi: token API tidak valid atau tidak disertakan."
}
```

---

## 4. CORS

Agar dapat dipanggil dari browser aplikasi lain, origin klien harus diizinkan lewat
variabel `CORS_ALLOWED_ORIGINS` di `.env` (pisahkan koma):

```env
CORS_ALLOWED_ORIGINS=http://127.0.0.1:8086,http://localhost:8086
```

Gunakan `*` hanya untuk pengembangan lokal.

---

## 5. Format Respons Standar

### 5.1 Respons sukses (objek/list)

```json
{
  "success": true,
  "message": "OK",
  "data": { "..." }
}
```

### 5.2 Respons sukses terpaginasi

Endpoint daftar mengembalikan `meta` dan `links` tambahan:

```json
{
  "success": true,
  "message": "OK",
  "data": [ "...", "..." ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "from": 1,
    "to": 15,
    "total": 65,
    "last_page": 5
  },
  "links": {
    "first": "http://127.0.0.1:8081/api/v1/paket?page=1",
    "last":  "http://127.0.0.1:8081/api/v1/paket?page=5",
    "prev":  null,
    "next":  "http://127.0.0.1:8081/api/v1/paket?page=2"
  }
}
```

### 5.3 Respons error

```json
{
  "success": false,
  "message": "Data tidak ditemukan."
}
```

> **Konvensi umum:** klien disarankan memeriksa `success` terlebih dahulu sebelum
> membaca `data`. Semua field memakai notasi `snake_case`.

---

## 6. Status Kode & Penanganan Error

| Kode | Arti | Kondisi |
|---|---|---|
| `200` | OK | Permintaan berhasil. |
| `401` | Unauthorized | Token tidak ada/salah. |
| `404` | Not Found | Record `{id}` tidak ditemukan. |
| `405` | Method Not Allowed | Memakai verb selain GET. |
| `429` | Too Many Requests | Melewati batas *rate limit* Laravel (`throttle:api`). |
| `500` | Server Error | Kegagalan internal (laporkan ke pengelola). |

---

## 7. Daftar Endpoint

### Health
| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/ping` | Uji koneksi & validitas token. |

### Master data
| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/opd` | Daftar OPD (filter `q`) |
| GET | `/opd/{id}` | Detail satu OPD |
| GET | `/pokja` | Daftar Pokja + indikator kinerja |
| GET | `/pokja/{id}` | Detail Pokja + daftar paketnya |
| GET | `/penyedia` | Daftar penyedia (filter `q`, `jenis_usaha`, `kualifikasi`) |
| GET | `/penyedia/{id}` | Detail satu penyedia |

### Paket pengadaan
| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/paket` | Daftar paket (banyak filter + pagination) |
| GET | `/paket/{id}` | Detail paket + semua data turunan |
| GET | `/paket/{id}/tahapan` | Tahapan proses paket |
| GET | `/paket/{id}/evaluasi` | Evaluasi peserta paket |
| GET | `/paket/{id}/sanggahan` | Sanggahan terhadap paket |
| GET | `/paket/{id}/progres` | Riwayat progres pekerjaan |
| GET | `/paket/{id}/perubahan-jadwal` | Riwayat perubahan jadwal |
| GET | `/paket/{id}/pesan` | Pesan antar pengguna |
| GET | `/paket/{id}/alert` | Alert anomali paket |

### Data monitoring (global)
| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/tahapan` | Semua tahapan (filter `paket_id`, `status`) |
| GET | `/evaluasi` | Semua evaluasi |
| GET | `/sanggahan` | Semua sanggahan |
| GET | `/progres` | Semua progres pekerjaan |
| GET | `/alert` | Semua alert anomali |
| GET | `/perubahan-jadwal` | Semua perubahan jadwal |
| GET | `/pesan` | Semua pesan |
| GET | `/audit-checklist` | Semua checklist audit |
| GET | `/audit-log` | Rekam jejak (jejak audit) |

### Agregat
| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/statistik` | Agregat ringkas (opsional `tahun`) |
| GET | `/kinerja-pokja` | Ringkasan kinerja seluruh Pokja |

---

## 8. Detail Endpoint

### 8.1 `GET /ping`

Uji koneksi sekaligus validitas token.

```http
GET /api/v1/ping
Authorization: Bearer <token>
```

**Contoh respons:**

```json
{
  "success": true,
  "message": "API aktif — token valid.",
  "data": {
    "aplikasi": "SIMONPOKJA (Monitoring Pokja LPSE)",
    "framework": "Laravel 12.0.0",
    "waktu_server": "2026-09-28T10:15:00+00:00",
    "zona_waktu": "Asia/Jakarta"
  }
}
```

---

### 8.2 `GET /paket`

Daftar paket pengadaan.

**Parameter query:**

| Parameter | Tipe | Keterangan |
|---|---|---|
| `tahun` | integer | Tahun anggaran |
| `status` | string | Lihat [enum status paket](#91-paket-pengadaan) |
| `jenis` | string | Lihat enum jenis |
| `metode` | string | Lihat enum metode |
| `sumber_dana` | string | Lihat enum sumber dana |
| `risiko` | string | `rendah` / `sedang` / `kritis` |
| `opd_id` | integer | Filter OPD |
| `pokja_id` | integer | Filter Pokja |
| `penyedia_id` | integer | Filter penyedia |
| `tender_gagal` | boolean | `1`/`0` atau `true`/`false` |
| `q` | string | Cari `nama_paket` atau `kode_paket` |
| `dari` / `sampai` | date | Rentang `tanggal_mulai` (`YYYY-MM-DD`) |
| `sort` | string | Kolom sortir (lihat daftar di bawah) |
| `order` | string | `asc` / `desc` (default `desc`) |
| `per_page` | integer | 1–100 (default 15) |
| `page` | integer | Nomor halaman |

Kolom yang dapat dipakai pada `sort`: `kode_paket`, `nama_paket`, `tahun_anggaran`,
`pagu`, `hps`, `nilai_kontrak`, `tanggal_mulai`, `tanggal_selesai`, `created_at`, `updated_at`.

**Contoh permintaan:**

```http
GET /api/v1/paket?tahun=2026&status=kontrak&per_page=2
Authorization: Bearer <token>
```

**Contoh respons (satu item `data`):**

```json
{
  "id": 94,
  "kode_paket": "SIM-2026-SWA-002",
  "nama_paket": "Swakelola Pengelolaan Parkir Wisata Curug Sidandang",
  "tahun_anggaran": 2026,
  "jenis": "jasa_lainnya",
  "jenis_label": "Jasa Lainnya",
  "metode": "swakelola",
  "metode_label": "Swakelola",
  "sumber_dana": "apbd",
  "status": "kontrak",
  "status_label": "Kontrak",
  "risiko": "rendah",
  "tender_gagal": false,
  "tahap_tender": "penetapan",
  "pagu": 350000000,
  "hps": 339500000,
  "nilai_kontrak": 329000000,
  "deviasi_persen": 6,
  "progres_persen": 70,
  "tanggal_mulai": "2026-07-28",
  "tanggal_selesai": "2027-01-09",
  "lokasi": "Kabupaten Banjarnegara",
  "desa": "Desa Kalibening",
  "kecamatan": "Kec.Kalibening",
  "latitude": -7.246249,
  "longitude": 109.625169,
  "keterangan": "Paket simulasi kinerja Pokja TA 2026 (data dummy untuk pengujian)",
  "deadline": { "status": "normal", "sisa_hari": 103 },
  "opd_id": 19,
  "pokja_id": 5,
  "penyedia_id": null,
  "opd": { "id": 19, "kode": "...", "nama": "...", "singkatan": "..." },
  "pokja": { "id": 5, "nama": "Pokja V", "bidang": "Swakelola & Pengadaan Terintegrasi" },
  "penyedia": null,
  "created_at": "2026-09-11T11:58:26+00:00",
  "updated_at": "2026-09-11T11:58:26+00:00"
}
```

> **Catatan field turunan:** `jenis_label`, `metode_label`, `status_label` adalah label
> Bahasa Indonesia; `deviasi_persen` dihitung `(pagu − nilai_kontrak) / pagu × 100`;
> `progres_persen` dihitung dari status tahapan (0–100, kelipatan 5); `deadline.status`
> bernilai `normal` / `mendekati` / `mendesak` / `lewat`; `deadline.sisa_hari` adalah
> selisih hari ke `tanggal_selesai` (hanya untuk paket yang belum selesai/batal).

---

### 8.3 `GET /paket/{id}`

Detail lengkap satu paket beserta **semua** data turunannya.

```http
GET /api/v1/paket/94
Authorization: Bearer <token>
```

**Struktur respons:**

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "paket": { "...": "sama seperti item pada 8.2" },
    "tahapan": [ { "id": 1, "paket_id": 94, "nama_tahap": "Persiapan", "urutan": 1, "status": "selesai", "tanggal_rencana": "2026-07-01", "tanggal_aktual": "2026-07-10", "keterangan": null, "created_at": "...", "updated_at": "..." } ],
    "evaluasi": [ { "id": 1, "paket_id": 94, "penyedia_id": 2, "jenis": "kualifikasi", "skor": 88.5, "hasil": "lolos", "catatan": null, "tanggal": "2026-08-01", "penyedia": { "id": 2, "nama": "PT Contoh" }, "created_at": "...", "updated_at": "..." } ],
    "sanggahan": [ { "id": 1, "paket_id": 94, "penyedia_id": 2, "tanggal_masuk": "2026-08-05", "tanggal_dijawab": "2026-08-07", "hasil": "ditolak", "substantif": false, "catatan": null, "penyedia": { "id": 2, "nama": "PT Contoh" }, "created_at": "...", "updated_at": "..." } ],
    "progres_pekerjaan": [ { "id": 1, "paket_id": 94, "user_id": 5, "progress": 70, "status": "berjalan", "catatan": null, "user": { "id": 5, "nama": "..." }, "created_at": "...", "updated_at": "..." } ],
    "perubahan_jadwal": [ { "id": 70, "paket_id": 93, "tanggal": "2026-08-24", "jam": "09:30:00", "jenis": "penyesuaian", "tahap_terkait": "Pelaksanaan", "alasan": "...", "ada_berita_acara": true, "created_at": "...", "updated_at": "..." } ],
    "pesan": [ { "id": 1, "paket_id": 94, "user_id": 1, "pesan": "...", "dibaca_pada": null, "user": { "id": 1, "nama": "..." }, "created_at": "...", "updated_at": "..." } ],
    "alert_anomali": [ { "id": 1, "paket_id": 94, "pokja_id": 5, "jenis": "delay_berulang", "tingkat": "critical", "deskripsi": "...", "status": "aktif", "pokja": { "id": 5, "nama": "Pokja V" }, "created_at": "...", "updated_at": "..." } ],
    "audit_checklist": [ { "id": 1, "pokja_id": 5, "paket_id": 94, "kategori": 2, "kategori_label": "Kepatuhan Jadwal & Pengunduran Lelang (SLA)", "poin": "...", "jawaban": "tidak", "catatan": null, "auditor_id": 1, "pokja": {...}, "paket": {...}, "created_at": "...", "updated_at": "..." } ],
    "ringkasan": {
      "tahapan": 5, "evaluasi": 2, "sanggahan": 1, "progres_pekerjaan": 3,
      "perubahan_jadwal": 0, "pesan": 2, "alert_anomali": 1, "audit_checklist": 0
    }
  }
}
```

> Record bernilai `null` pada relasi (`penyedia`) berarti belum ada data terkait.
> Endpoint turunan (`/paket/{id}/tahapan`, dst.) mengembalikan **array saja** di `data`.

---

### 8.4 `GET /pokja`

Daftar Pokja beserta indikator kinerja terhitung.

**Parameter query:** `aktif` (`1`/`0`), `q` (cari `nama`/`ketua`).

```http
GET /api/v1/pokja
Authorization: Bearer <token>
```

**Contoh respons (diringkas):**

```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 2,
      "nama": "Pokja II",
      "bidang": "Jasa Konsultansi",
      "ketua": "Ir. Slamet Santoso, M.T.",
      "nip_ketua": "19680422 199501 1 002",
      "anggota": ["Rina Kartika, S.T.", "Joko Purnomo, S.E.", "Ahmad Dahlan, S.H."],
      "jumlah_anggota": 3,
      "kapasitas_ideal": 5,
      "aktif": true,
      "kinerja": {
        "beban_kerja": 15,
        "rasio_beban": 5.0,
        "overload": false,
        "kepatuhan_sla": 82.4,
        "tender_gagal_persen": 11.8,
        "alert_critical": 6,
        "skor_risiko": 79,
        "label_risiko": "KRITIS",
        "warna_risiko": "danger"
      }
    }
  ]
}
```

**Makna `kinerja`:**

| Field | Arti |
|---|---|
| `beban_kerja` | Jumlah paket aktif (belum selesai/batal) |
| `rasio_beban` | Beban kerja per anggota |
| `overload` | `true` bila rasio melebihi kapasitas ideal |
| `kepatuhan_sla` | % paket tanpa pengunduran jadwal berulang (>3×) |
| `tender_gagal_persen` | % paket dengan tender gagal |
| `alert_critical` | Jumlah alert aktif tingkat `critical` |
| `skor_risiko` | Skor risiko 0–100 |
| `label_risiko` | `RENDAH` / `SEDANG` / `TINGGI` / `KRITIS` |
| `warna_risiko` | Kelas warna Bootstrap: `success` / `info` / `warning` / `danger` |

---

### 8.5 `GET /pokja/{id}`

Seperti `/pokja`, ditambah daftar `paket` yang ditangani Pokja tersebut.

```json
{
  "...": "semua field 8.4",
  "paket": [
    { "id": 41, "kode_paket": "RUP-65830765", "nama_paket": "...", "tahun_anggaran": "2026", "status": "pemilihan", "pagu": 251795700, "nilai_kontrak": 0, "tender_gagal": 0, "risiko": "sedang" }
  ]
}
```

> Catatan: nilai numerik kecil pada `paket` Pokja (mis. `tender_gagal: 0`) berasal dari
> tabel mentah dan bisa berupa `0`/`1`, bukan boolean literal.

---

### 8.6 `GET /opd` & `GET /opd/{id}`

**List** (filter `q`, pagination):

```json
{
  "success": true,
  "message": "OK",
  "data": [
    { "id": 1, "kode": "1.01.2.22.0.00.01.0000", "nama": "Dewan Perwakilan Rakyat Daerah", "singkatan": "DPRD", "jumlah_paket": 0 }
  ],
  "meta": { "...": "..." },
  "links": { "...": "..." }
}
```

**Detail** menambahkan `kepala`, `nip_kepala`, `alamat`, `telepon`, `email`, dan `jumlah_paket`.

---

### 8.7 `GET /penyedia` & `GET /penyedia/{id}`

```json
{
  "id": 11,
  "npwp": "89.012.345.6-401.000",
  "nib": null,
  "nama": "CV Berkah Jaya Farmasi",
  "jenis_usaha": "kecil",
  "kualifikasi": "kecil",
  "alamat": "Banjarnegara / Purwokerto / Semarang",
  "direktur": "Lilis Suryani",
  "telepon": "028x-374248",
  "email": "berkahjayafarmasi@gmail.com",
  "aktif": true,
  "jumlah_paket": 2
}
```

**Filter list:** `q`, `jenis_usaha`, `kualifikasi`.

---

### 8.8 `GET /statistik`

Agregat ringkas untuk dashboard. Opsional `?tahun=2026`.

```http
GET /api/v1/statistik?tahun=2026
Authorization: Bearer <token>
```

**Contoh respons (diringkas):**

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "tahun": 2026,
    "total_paket": 65,
    "total_pagu": 117673976770,
    "total_hps": 113415597235,
    "total_nilai_kontrak": 69042893604,
    "total_tender_gagal": 5,
    "berdasarkan_status": { "draft": 5, "persiapan": 10, "pemilihan": 11, "kontrak": 10, "pelaksanaan": 12, "selesai": 17 },
    "berdasarkan_jenis": { "barang": 29, "jasa_konsultansi": 12, "konstruksi": 18, "jasa_lainnya": 6 },
    "berdasarkan_sumber_dana": { "apbd": 44, "blm": 6, "dak": 6, "lainnya": 9 },
    "berdasarkan_risiko": { "rendah": 46, "sedang": 14, "kritis": 5 },
    "berdasarkan_opd": { "2": 10, "8": 14, "9": 12 },
    "total_opd": 25,
    "total_penyedia": 12,
    "total_pokja": 5,
    "total_pokja_aktif": 5,
    "total_sanggahan": 16,
    "sanggahan_berdasarkan_hasil": { "menunggu": 5, "diterima": 4, "ditolak": 7 },
    "total_alert_anomali": 13,
    "alert_berdasarkan_tingkat": { "info": 2, "warning": 4, "critical": 7 },
    "tahun_tersedia": [ "2026", "2025" ]
  }
}
```

> Kunci pada `berdasarkan_opd` adalah `opd_id`; padanan nama OPD dapat dilihat di `/opd`.

---

### 8.9 `GET /kinerja-pokja`

Alias ringkas penilaian kinerja seluruh Pokja. Struktur sama dengan `GET /pokja`.

---

### 8.10 Endpoint monitoring global

Endpoint berikut mengembalikan list terpaginasi dan menerima filter berbasis `paket_id`:

| Endpoint | Filter tambahan |
|---|---|
| `/tahapan` | `paket_id`, `status` |
| `/evaluasi` | `paket_id`, `penyedia_id`, `jenis`, `hasil` |
| `/sanggahan` | `paket_id`, `penyedia_id`, `hasil`, `substantif` |
| `/progres` | `paket_id`, `user_id` |
| `/alert` | `paket_id`, `pokja_id`, `tingkat`, `status` |
| `/perubahan-jadwal` | `paket_id`, `jenis` |
| `/pesan` | `paket_id`, `user_id` |
| `/audit-checklist` | `paket_id`, `pokja_id`, `kategori`, `jawaban` |
| `/audit-log` | `tabel`, `aksi`, `user_id`, `dari`, `sampai` |

Format item sama dengan contoh pada [§8.3](#83-get-paketid).

---

## 9. Kamus Nilai (Enum)

### 9.1 Paket pengadaan

| Field | Nilai yang mungkin |
|---|---|
| `status` | `draft`, `persiapan`, `pemilihan`, `kontrak`, `pelaksanaan`, `selesai`, `batal` |
| `jenis` | `barang`, `jasa_konsultansi`, `konstruksi`, `jasa_lainnya` |
| `metode` | `tender`, `seleksi`, `epurchasing`, `penunjukan_langsung`, `pengadaan_langsung`, `swakelola` |
| `sumber_dana` | `apbd`, `blm`, `dak`, `lainnya` |
| `risiko` | `rendah`, `sedang`, `kritis` |
| `tahap_tender` | `persiapan`, `pengumuman`, `evaluasi`, `penetapan`, `sanggah` |
| `deadline.status` | `normal`, `mendekati`, `mendesak`, `lewat` |

### 9.2 Tahapan

| Field | Nilai |
|---|---|
| `nama_tahap` | `Persiapan`, `Pemilihan`, `Kontrak`, `Pelaksanaan`, `Serah Terima` |
| `status` | `belum`, `proses`, `selesai` |

### 9.3 Evaluasi

| Field | Nilai |
|---|---|
| `jenis` | `kualifikasi`, `harga` |
| `hasil` | `lolos`, `gugur` |

### 9.4 Sanggahan

| Field | Nilai |
|---|---|
| `hasil` | `menunggu`, `diterima`, `ditolak` |
| `substantif` | `true` / `false` |

### 9.5 Alert anomali

| Field | Nilai |
|---|---|
| `jenis` | `beban_overload`, `delay_berulang`, `jam_akhir_kerja`, `sanggah_tidak_tepat_waktu`, `tanpa_ba`, `tender_gagal_berulang` |
| `tingkat` | `info`, `warning`, `critical` |
| `status` | `aktif` (dan status lain bila ditutup) |

### 9.6 Perubahan jadwal

| Field | Nilai |
|---|---|
| `jenis` | `pengunduran`, `reopening`, `penyesuaian` |
| `ada_berita_acara` | `true` / `false` |

### 9.7 Audit checklist

| Kategori (`kategori`) | Label |
|---|---|
| `1` | Beban Kerja & Distribusi Penugasan |
| `2` | Kepatuhan Jadwal & Pengunduran Lelang (SLA) |
| `3` | Spesifikasi Teknis & Dokumen Pemilihan |
| `4` | Objektivitas Evaluasi & Penilaian Penawaran |
| `5` | Jejak Digital & Komunikasi Formal |
| `6` | Penanganan Sanggahan & Tender Gagal |

`jawaban`: `ya` / `tidak` (polaritas temuan: `ya` = ditemukan masalah).

---

## 10. Model Data & Relasi

```
opd (1) ───────< paket_pengadaans >────── (1) penyedia
                    │  ▲
                    │  └─── pokja (1) ────< paket_pengadaans
                    │
                    ├──< tahapans             (1 paket : N tahapan)
                    ├──< evaluasis            (1 paket : N evaluasi, via penyedia)
                    ├──< sanggahans           (1 paket : N sanggahan, via penyedia)
                    ├──< progres_pekerjaans   (1 paket : N progres, via user)
                    ├──< perubahan_jadwals    (1 paket : N perubahan jadwal)
                    ├──< pesan_pakets         (1 paket : N pesan, via user)
                    └──< alert_anomalis       (1 paket : N alert, via pokja)

pokja (1) ──────< audit_checklists (N) ──> paket_pengadaans
users (1) ──────< audit_logs (N)  (jejak audit)
```

Tabel utama: `opds`, `pokjas`, `penyedias`, `paket_pengadaans`, `tahapans`,
`evaluasis`, `sanggahans`, `progres_pekerjaans`, `perubahan_jadwals`, `pesan_pakets`,
`alert_anomalis`, `audit_checklists`, `audit_logs`.

---

## 11. Contoh Integrasi

### 11.1 cURL

```bash
TOKEN="<token-anda>"

# Uji koneksi
curl -H "Authorization: Bearer $TOKEN" http://127.0.0.1:8081/api/v1/ping

# Paket kontrak tahun 2026
curl -H "Authorization: Bearer $TOKEN" \
  "http://127.0.0.1:8081/api/v1/paket?tahun=2026&status=kontrak&per_page=10"

# Statistik tahun berjalan
curl -H "Authorization: Bearer $TOKEN" "http://127.0.0.1:8081/api/v1/statistik?tahun=2026"
```

### 11.2 JavaScript (fetch)

```js
const BASE = 'http://127.0.0.1:8081/api/v1';
const TOKEN = '<token-anda>';

async function ambilPaket({ tahun, status, perPage = 15 } = {}) {
  const qs = new URLSearchParams({ per_page: perPage });
  if (tahun) qs.set('tahun', tahun);
  if (status) qs.set('status', status);

  const res = await fetch(`${BASE}/paket?${qs}`, {
    headers: { Authorization: `Bearer ${TOKEN}` },
  });

  if (!res.ok) {
    const err = await res.json();
    throw new Error(err.message || `HTTP ${res.status}`);
  }

  const body = await res.json();
  return { data: body.data, meta: body.meta, links: body.links };
}
```

### 11.3 PHP (Guzzle)

```php
$client = new \GuzzleHttp\Client(['base_uri' => 'http://127.0.0.1:8081/api/v1']);
$res = $client->request('GET', 'paket', [
    'query' => ['tahun' => 2026, 'per_page' => 10],
    'headers' => ['Authorization' => 'Bearer ' . $token],
]);
$json = json_decode($res->getBody(), true);
```

---

## 12. Catatan & Batasan

- **Read-only:** seluruh endpoint saat ini hanya mendukung `GET`. Endpoint tulis
  (POST/PUT/DELETE) dapat ditambahkan bila dibutuhkan.
- **Rate limit:** mengikuti konfigurasi Laravel (`throttle:api`). Jika menerima `429`,
  kurangi frekuensi pemanggilan atau lakukan *caching* di sisi konsumen.
- **Waktu:** `created_at`/`updated_at` memakai format ISO 8601 UTC (`+00:00`).
  Konversikan ke zona waktu lokal saat tampil.
- **Token rahasia:** simpan token di sisi server, jangan di-hardcode pada aset front-end
  publik. Ganti token sebelum produksi.
- **Idempotensi data dummy:** sebagian data merupakan data simulasi/dummy untuk pengujian.

---

*Dokumentasi ini dapat dihasilkan ulang dan diperbarui mengikuti perkembangan API.
Untuk pertanyaan teknis, hubungi pengelola SIMONPOKJA.*