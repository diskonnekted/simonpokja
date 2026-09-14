# Routes Catalog — SIMONPOKJA

## Frontend Routes (Pages)
| Route Path | File / Controller | Page Title / Deskripsi | Auth | Role | Status |
|---|---|---|---|---|---|
| `/login` | `AuthController@showLogin` | Halaman Masuk Aplikasi | Guest | All | STABLE |
| `/` | `DashboardController@index` | Dashboard Utama Admin | Auth | `admin` | STABLE |
| `/peta-kegiatan` | `PetaController@index` | Peta Sebaran Lokasi Paket | Auth | `admin` | STABLE |
| `/kinerja-pokja` | `PokjaController@index` | Monitoring Kinerja Pokja Pemilihan | Auth | `admin` | STABLE |
| `/kinerja-pokja/{pokja}` | `PokjaController@show` | Detail & Rekam Jejak Pokja | Auth | `admin` | STABLE |
| `/kelola-pokja` | `PokjaController@kelola` | Daftar Master Pokja | Auth | `admin` | STABLE |
| `/kelola-pokja/tambah` | `PokjaController@create` | Form Tambah Pokja | Auth | `admin` | STABLE |
| `/kelola-pokja/{pokja}/edit`| `PokjaController@edit` | Form Edit Pokja | Auth | `admin` | STABLE |
| `/pemantauan` | `PemantauanController@index` | Pemantauan Paket Bermasalah | Auth | `admin` | STABLE |
| `/pemantauan/{paket}` | `PemantauanController@show` | Detail Paket & Timeline Jadwal | Auth | `admin` | STABLE |
| `/paket` | `PaketController@index` | Master Data Paket Pengadaan | Auth | `admin` | STABLE |
| `/paket/create` | `PaketController@create` | Form Tambah Paket | Auth | `admin` | STABLE |
| `/paket/{paket}` | `PaketController@show` | Detail Paket Pengadaan | Auth | `admin` | STABLE |
| `/paket/{paket}/edit` | `PaketController@edit` | Form Edit Paket | Auth | `admin` | STABLE |
| `/opd` | `OpdController@index` | Master Data Perangkat Daerah (OPD) | Auth | `admin` | STABLE |
| `/penyedia` | `PenyediaController@index` | Master Data Rekanan / Penyedia | Auth | `admin` | STABLE |
| `/laporan` | `LaporanController@index` | Rekapitulasi & Cetak Laporan | Auth | `admin` | STABLE |
| `/laporan/export` | `LaporanController@export`| Cetak Dokumen Rekapitulasi | Auth | `admin` | STABLE |
| `/audit` | `AuditLogController@index` | Audit Trail Riwayat Sistem | Auth | `admin` | STABLE |
| `/pokja-dasbor` | `PokjaDashboardController@index` | Dasbor Kerja Khusus Pokja | Auth | `admin,pokja`| STABLE |
| `/pokja-dasbor/paket/{paket}/riwayat` | `PokjaDashboardController@riwayat` | Riwayat Progres Paket Pokja | Auth | `admin,pokja`| STABLE |
| `/pokja-sanggahan` | `PokjaDashboardController@sanggahan` | Sanggahan Rekanan & SLA 3 Hari Kerja | Auth | `admin,pokja`| STABLE |
| `/pokja-jadwal` | `PokjaDashboardController@jadwal` | Linimasa Tahapan & Log Addendum Jadwal | Auth | `admin,pokja`| STABLE |
| `/pokja-laporan` | `PokjaDashboardController@laporan` | Lembar Kendali Kepatuhan Print-Ready | Auth | `admin,pokja`| STABLE |
| `/paket/{paket}/pesan` | `PesanPaketController@index` | Room Chat Per Paket (LPSE & Pokja) | Auth | `admin,pokja`| STABLE |
| `/notifikasi` | `NotifikasiController@index` | Pusat Notifikasi & Log Riwayat Pemberitahuan | Auth | All (`admin,pokja`) | STABLE |

## API Routes & Form Actions
| Method | Route Path | Handler | Auth | Role | Purpose |
|---|---|---|---|---|---|
| POST | `/login` | `AuthController@login` | Guest | All | Proses otentikasi sesi |
| GET, POST | `/logout` | `AuthController@logout` | Auth | All | Keluar dari sesi aplikasi (exempt CSRF) |
| GET | `/api/peta/paket` | `PetaController@paket` | Auth | `admin` | GeoJSON / data paket berkoordinat |
| GET | `/api/peta/statistik` | `PetaController@statistik` | Auth | `admin` | Statistik agregat peta kegiatan |
| POST | `/kinerja-pokja/{pokja}/checklist` | `PokjaController@simpanChecklist` | Auth | `admin` | Simpan 6 kategori checklist audit |
| PATCH | `/alert/{alert}` | `PokjaController@updateAlert` | Auth | `admin` | Perbarui status alert anomali |
| POST | `/kelola-pokja` | `PokjaController@storePokja` | Auth | `admin` | Buat Pokja baru |
| PUT | `/kelola-pokja/{pokja}` | `PokjaController@updatePokja` | Auth | `admin` | Simpan edit data Pokja |
| DELETE| `/kelola-pokja/{pokja}` | `PokjaController@destroyPokja`| Auth | `admin` | Hapus data Pokja |
| POST | `/pemantauan/{paket}/perubahan-jadwal` | `PemantauanController@storePerubahan` | Auth | `admin` | Catat pengunduran / ubah jadwal |
| POST | `/pemantauan/{paket}/sanggahan` | `PemantauanController@storeSanggahan` | Auth | `admin` | Catat sanggahan baru rekanan |
| POST | `/sanggahan/{sanggahan}/jawab` | `PemantauanController@jawabSanggahan` | Auth | `admin` | Rekam jawaban sanggahan + SLA (Admin) |
| POST | `/pokja-sanggahan/{sanggahan}/jawab` | `PokjaDashboardController@jawabSanggahan` | Auth | `admin,pokja` | Rekam tanggapan resmi sanggahan Pokja + SLA |
| POST | `/pokja-jadwal/{paket}/perubahan` | `PokjaDashboardController@simpanPerubahanJadwal` | Auth | `admin,pokja` | Rekam perubahan jadwal pengadaan oleh Pokja |
| POST | `/paket/{paket}/tahapan` | `PaketController@storeTahapan` | Auth | `admin` | Tambah tahapan pengadaan |
| PUT | `/tahapan/{tahapan}` | `PaketController@updateTahapan` | Auth | `admin` | Update tahapan pengadaan |
| POST | `/paket/{paket}/evaluasi` | `PaketController@storeEvaluasi` | Auth | `admin` | Catat skor evaluasi kualifikasi |
| POST | `/pokja-dasbor/paket/{paket}/progres` | `PokjaDashboardController@simpanProgres` | Auth | `pokja` | Simpan progres mingguan Pokja |
| POST | `/paket/{paket}/pesan` | `PesanPaketController@store` | Auth | `admin,pokja` | Kirim pesan chat pekerjaan |
| POST | `/notifikasi/{id}/baca` | `NotifikasiController@tandaiDibaca` | Auth | All | Tandai notifikasi spesifik telah dibaca |
| POST | `/notifikasi/baca-semua` | `NotifikasiController@tandaiSemuaDibaca` | Auth | All | Tandai seluruh notifikasi user telah dibaca |
| GET | `/api/push/vapid-public-key` | `NotifikasiController@vapidPublicKey` | Auth | All | Ambil kunci publik VAPID Web Push |
| POST | `/api/push/subscribe` | `NotifikasiController@subscribe` | Auth | All | Daftarkan endpoint browser Web Push |
| POST | `/api/push/unsubscribe` | `NotifikasiController@unsubscribe` | Auth | All | Hapus pendaftaran endpoint Web Push |

## Middleware Chain
| Middleware | Applied To | Purpose |
|---|---|---|
| `guest` | `/login` | Membatasi akses jika sudah login |
| `auth` | Semua rute internal | Memastikan session user terautentikasi |
| `role:admin` | Dashboard admin, kelola pokja, pemantauan, master data | Otorisasi khusus admin LPSE |
| `role:pokja` | Input progres mingguan di pokja-dasbor | Otorisasi khusus personel Pokja |
| `role:admin,pokja` | Perpesanan per paket, dasbor peninjauan pokja, pusat notifikasi | Akses kolaborasi interaktif |
