# Skema Database & Relasi — SIMONPOKJA

## 1. Relasi Antar Tabel (Entity Relationship)
- `pokjas` **1 : N** `paket_pengadaans` (Setiap Pokja menangani beberapa paket pengadaan)
- `pokjas` **1 : N** `users` (Anggota Pokja berelasi dengan akun pengguna login)
- `pokjas` **1 : N** `audit_checklists` (Catatan evaluasi 6 kategori kepatuhan)
- `opds` **1 : N** `paket_pengadaans` (Paket dimiliki oleh OPD instansi pengusul)
- `penyedias` **1 : N** `paket_pengadaans` (Pemenang/pelaksana tender)
- `paket_pengadaans` **1 : N** `tahapans` (Tahapan persiapan, pemilihan, kontrak, dsb)
- `paket_pengadaans` **1 : N** `perubahan_jadwals` (Riwayat addendum/pengunduran)
- `paket_pengadaans` **1 : N** `sanggahans` (Sanggahan peserta seleksi)
- `paket_pengadaans` **1 : N** `alert_anomalis` (Peringatan sistematis)
- `paket_pengadaans` **1 : N** `progres_pekerjaans` (Log progres mingguan/harian)
- `paket_pengadaans` **1 : N** `pesan_pakets` (Chatroom koordinasi pekerjaan)
- `users` **1 : N** `push_subscriptions` (Pendaftaran endpoint browser Web Push VAPID)
- `users` **1 : N** `notifikasis` (Log riwayat notifikasi in-app & alert sistem)

## 2. Struktur Tabel Utama

### `pokjas`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Auto Increment |
| `kode` | VARCHAR(50) | Kode identitas Pokja (mis. POKJA-I) |
| `nama` | VARCHAR(255) | Nama Pokja (mis. Pokja I - Pengadaan Barang) |
| `bidang` | VARCHAR(100) | Bidang spesialisasi pengadaan |
| `keterangan` | TEXT | Catatan / deskripsi Pokja |

### `paket_pengadaans`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Auto Increment |
| `kode_paket` | VARCHAR(100) | Kode internal paket |
| `kode_rup` | VARCHAR(50) | Kode SiRUP LKPP |
| `nama_paket` | VARCHAR(255) | Nama pekerjaan |
| `opd_id` | BIGINT FK | Relasi ke `opds` |
| `penyedia_id`| BIGINT FK NULL | Relasi ke `penyedias` |
| `pokja_id` | BIGINT FK NULL | Relasi ke `pokjas` (Penugasan panitia kerja) |
| `jenis` | ENUM | barang, konstruksi, jasa_konsultansi, jasa_lainnya |
| `metode` | ENUM | tender, seleksi, pengadaan_langsung, epurchasing, dsb |
| `pagu` | DECIMAL(18,2) | Nilai pagu anggaran |
| `hps` | DECIMAL(18,2) | Harga Perkiraan Sendiri |
| `nilai_kontrak`| DECIMAL(18,2) | Nilai kesepakatan kontrak |
| `status` | ENUM | draft, persiapan, pemilihan, kontrak, pelaksanaan, selesai |
| `progress` | INT | Progres persentase 0-100 |
| `risiko` | ENUM | RENDAH, SEDANG, TINGGI, KRITIS |
| `tender_gagal` | BOOLEAN | Indikator kegagalan proses tender |
| `tahap_tender` | VARCHAR(100) | Tahapan tender aktif (mis. Evaluasi Penawaran) |
| `desa` | VARCHAR(100) NULL | Desa lokasi kegiatan fisik |
| `kecamatan` | VARCHAR(100) NULL | Kecamatan wilayah Kabupaten Banjarnegara |
| `latitude` | DECIMAL(10,8) | Koordinat lokasi fisik (Leaflet Map) |
| `longitude`| DECIMAL(11,8) | Koordinat lokasi fisik (Leaflet Map) |

### `perubahan_jadwals`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Auto Increment |
| `paket_id` | BIGINT FK | Relasi ke `paket_pengadaans` |
| `tahapan_id` | BIGINT FK | Relasi ke `tahapans` |
| `jenis_perubahan` | VARCHAR(50) | pengunduran, penyesuaian, perpanjangan |
| `alasan` | TEXT | Justifikasi perubahan |
| `ada_ba` | BOOLEAN | Apakah disertai Berita Acara resmi |
| `diluar_jam_kerja` | BOOLEAN | Apakah diubah di luar jam kerja (indikator anomali) |

### `sanggahans`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Auto Increment |
| `paket_id` | BIGINT FK | Relasi ke `paket_pengadaans` |
| `penyedia_id` | BIGINT FK | Relasi ke `penyedias` |
| `tgl_sanggah` | DATE | Tanggal surat sanggahan masuk |
| `tgl_jawab` | DATE NULL | Tanggal jawaban resmi diberikan |
| `status` | ENUM | belum_dijawab, dijawab, diterima, ditolak |
| `substantif` | BOOLEAN | Sanggahan bersifat substantif |

### `push_subscriptions`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Auto Increment |
| `user_id` | BIGINT FK | Relasi ke `users` (Cascade on delete) |
| `endpoint` | TEXT | URL Endpoint Push Service browser (FCM/Mozilla/Edge) |
| `p256dh_key` | VARCHAR(255) | Kunci enkripsi publik p256dh browser client |
| `auth_token` | VARCHAR(255) | Rahasia autentikasi klien Web Push |
| `user_agent` | VARCHAR(255) NULL | Identitas peramban perangkat terdaftar |
| `created_at` | TIMESTAMP | Waktu pendaftaran izin notifikasi |
| `updated_at` | TIMESTAMP | Waktu pembaruan subscription |

### `notifikasis`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Auto Increment |
| `user_id` | BIGINT FK | Relasi ke `users` penerima notifikasi |
| `judul` | VARCHAR(255) | Judul notifikasi (mis. Pesan Baru, Peringatan EWS) |
| `pesan` | TEXT | Isi ringkas pengumuman / pesan peringatan |
| `tipe` | VARCHAR(50) | `info`, `warning`, `danger`, `success` |
| `url` | VARCHAR(255) NULL | Tautan aksi saat notifikasi diklik |
| `icon` | VARCHAR(50) NULL | Kelas Bootstrap Icon (mis. `bi-chat-dots`) |
| `is_read` | BOOLEAN | Status keterbacaan (default: 0 / false) |
| `created_at` | TIMESTAMP | Waktu pengiriman notifikasi |
| `updated_at` | TIMESTAMP | Waktu pembaruan status |
