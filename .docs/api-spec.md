# Spesifikasi API & Endpoint — SIMONPOKJA

## 1. Auth & Session
### POST `/login`
- **Method:** `POST`
- **Auth:** Public / Guest
- **Payload:**
  ```json
  {
    "email": "admin@banjarnegara.go.id",
    "password": "password",
    "_token": "CSRF_TOKEN"
  }
  ```
- **Response:** 302 Redirect ke `/` atau `/pokja-dasbor`

---

## 2. Peta Kegiatan
### GET `/api/peta/paket`
- **Method:** `GET`
- **Auth:** `auth`, `role:admin`
- **Deskripsi:** Mengembalikan daftar paket lengkap dengan titik koordinat latitude & longitude untuk dirender pada layer Leaflet.js.
- **Response:**
  ```json
  [
    {
      "id": 1,
      "kode_paket": "POKJA-2025-001",
      "nama_paket": "Rehabilitasi Ruang Kelas SDN 1 Banjarnegara",
      "latitude": -7.3986,
      "longitude": 109.6974,
      "pagu": 850000000,
      "status": "pelaksanaan",
      "progress": 65
    }
  ]
  ```

### GET `/api/peta/statistik`
- **Method:** `GET`
- **Auth:** `auth`, `role:admin`
- **Deskripsi:** Mengembalikan data ringkasan total nilai pagu, paket aktif, dan sebaran per kecamatan.

---

## 3. Monitoring & Evaluasi
### POST `/kinerja-pokja/{pokja}/checklist`
- **Method:** `POST`
- **Auth:** `auth`, `role:admin`
- **Payload:**
  - `jawaban[kategori][nomor_butir]`: 'ya' | 'tidak'
  - `catatan[kategori][nomor_butir]`: string (opsional)
- **Response:** 302 Redirect dengan flash status berhasil.

### POST `/pemantauan/{paket}/perubahan-jadwal`
- **Method:** `POST`
- **Auth:** `auth`, `role:admin`
- **Payload:**
  - `tahapan_id`: ID Tahapan
  - `jenis_perubahan`: 'pengunduran' | 'penyesuaian'
  - `alasan`: text
  - `ada_ba`: 1 | 0
  - `no_ba`: string

---

## 4. Perpesanan Progres
### GET `/paket/{paket}/pesan`
- **Method:** `GET`
- **Auth:** `auth`, `role:admin,pokja`
- **Deskripsi:** Menampilkan dialog chat interaktif per paket pekerjaan.

### POST `/paket/{paket}/pesan`
- **Method:** `POST`
- **Auth:** `auth`, `role:admin,pokja`
- **Payload:**
  - `pesan`: text
  - `lampiran`: file (opsional)

---

## 5. Web Push & In-App Notifikasi
### GET `/api/push/vapid-public-key`
- **Method:** `GET`
- **Auth:** `auth` (Semua role login)
- **Deskripsi:** Mengembalikan kunci publik VAPID dalam format string Base64URL untuk inisialisasi pendaftaran `PushManager` Service Worker di peramban pengguna.
- **Response:**
  ```json
  {
    "publicKey": "BGKnyljBh1TtuInc1RfOHj1asPdC_3ZnHSdjKqyi_jRXKSbtjKTaOf4qS8O0dMBm1ePrQhkiKUVKEbFYjfBRy54"
  }
  ```

### POST `/api/push/subscribe`
- **Method:** `POST`
- **Auth:** `auth`
- **Deskripsi:** Menyimpan pendaftaran endpoint Push Service beserta kunci enkripsi browser klien ke dalam tabel `push_subscriptions`.
- **Payload:**
  ```json
  {
    "endpoint": "https://fcm.googleapis.com/fcm/send/...",
    "keys": {
      "p256dh": "BLc91i...",
      "auth": "x8bJ..."
    }
  }
  ```
- **Response:**
  ```json
  {
    "success": true,
    "message": "Subscription push notifikasi berhasil disimpan."
  }
  ```

### POST `/api/push/unsubscribe`
- **Method:** `POST`
- **Auth:** `auth`
- **Payload:**
  ```json
  {
    "endpoint": "https://fcm.googleapis.com/fcm/send/..."
  }
  ```
- **Response:**
  ```json
  {
    "success": true,
    "message": "Subscription berhasil dihapus."
  }
  ```

### POST `/notifikasi/{id}/baca`
- **Method:** `POST`
- **Auth:** `auth`
- **Deskripsi:** Mengubah status `is_read` menjadi `1` pada notifikasi milik user yang sedang aktif.
- **Response:**
  ```json
  {
    "success": true
  }
  ```

### POST `/notifikasi/baca-semua`
- **Method:** `POST`
- **Auth:** `auth`
- **Deskripsi:** Mengubah seluruh notifikasi milik user yang berstatus belum dibaca menjadi terbaca.
- **Response:** 302 Redirect kembali ke rute sebelumnya atau JSON `{ "success": true }`.

---

## 6. Operasional Pokja & Tanggapan Sanggahan
### POST `/pokja-sanggahan/{sanggahan}/jawab`
- **Method:** `POST`
- **Auth:** `auth`, `role:admin,pokja`
- **Deskripsi:** Merekam tanggapan resmi Pokja terhadap sanggahan rekanan, mencatat tanggal dijawab, dan menutup alert anomali terkait sanggahan kadaluarsa.
- **Payload:**
  ```json
  {
    "hasil": "ditolak", // 'ditolak' | 'diterima'
    "substantif": 1, // 1 | 0
    "catatan": "Uraian pertimbangan teknis Berita Acara Hasil Pemilihan (BAHP)...",
    "_token": "CSRF_TOKEN"
  }
  ```
- **Response:** 302 Redirect back dengan flash message sukses.

### POST `/pokja-jadwal/{paket}/perubahan`
- **Method:** `POST`
- **Auth:** `auth`, `role:admin,pokja`
- **Deskripsi:** Merekam addendum pergeseran tanggal dan jam tahapan pemilihan, disertai status Berita Acara (BA) resmi.
- **Payload:**
  ```json
  {
    "tanggal": "2026-09-15",
    "jam": "10:00",
    "jenis": "pengunduran", // 'pengunduran' | 'perpanjangan' | 'penyesuaian'
    "tahap_terkait": "Evaluasi Penawaran",
    "alasan": "Reviu klarifikasi teknis dokumen penawaran",
    "ada_berita_acara": 1,
    "_token": "CSRF_TOKEN"
  }
  ```
- **Response:** 302 Redirect back dengan flash message sukses.

### POST `/pokja-dasbor/paket/{paket}/progres`
- **Method:** `POST`
- **Auth:** `auth`, `role:pokja`
- **Deskripsi:** Merekam laporan mingguan progres fisik pekerjaan paket ke tabel `progres_pekerjaans` dan menyinkronkan ke tabel `paket_pengadaans`.
- **Payload:**
  ```json
  {
    "progress": 65,
    "status": "pelaksanaan",
    "catatan": "Pekerjaan pondasi tuntas",
    "_token": "CSRF_TOKEN"
  }
  ```
- **Response:** 302 Redirect back dengan flash message sukses.

### GET `/pokja-dasbor/paket/{paket}/riwayat`
- **Method:** `GET`
- **Auth:** `auth`, `role:admin,pokja`
- **Deskripsi:** Mengembalikan JSON riwayat entri progres fisik dari paket tertentu.
- **Response:**
  ```json
  {
    "paket": { "kode": "POKJA-2025-001", "nama": "...", "progress": 65 },
    "riwayat": [
      { "waktu": "12 Sep 2026 10:00", "user": "Pokja I", "progress": 65, "status": "pelaksanaan", "catatan": "..." }
    ]
  }
  ```

