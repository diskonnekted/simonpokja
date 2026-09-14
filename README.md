# SIMONPOKJA

Sistem Informasi Monitoring Kinerja Pokja (Panitia Pengadaan) untuk Lingkup Layanan Pengadaan Secara Elektronik Kabupaten Banjarnegara.

SIMONPOKJA adalah aplikasi web berbasis Laravel yang dirancang untuk memantau dan menilai kinerja panitia pengadaan barang/jasa (Pokja Pemilihan) di lingkungan Pemerintah Kabupaten Banjarnegara. Aplikasi ini menggantikan pemantauan manual berbasis lembar kerja dengan pencatatan terpusat, indikator risiko otomatis, dan rekam jejak (audit trail) yang dapat dipertanggungjawabkan.

## Fitur Utama

### Dashboard Kinerja Pokja
- Ringkasan indikator utama: paket aktif, tender gagal, kepatuhan SLA sanggahan, dan alert anomali.
- Skor risiko 0-100 per Pokja dengan label RENDAH, TINGGI, atau KRITIS yang dihitung dari beban kerja, pola pengunduran jadwal, tender gagal, dan pelanggaran SLA.
- Grafik distribusi tahapan paket dan tren perubahan jadwal (Chart.js).
- Manajemen multi-Pokja: Pokja I (Barang), Pokja II (Jasa Konsultansi), Pokja III (Konstruksi), Pokja IV (Jasa Lainnya), dan Pokja V (Swakelola), termasuk CRUD anggota.

### Pemantauan Paket Bermasalah
- Daftar paket dengan skor masalah yang diurutkan berdasarkan tingkat keparahan.
- Pencatatan perubahan jadwal tahapan: pengunduran, penyesuaian, dan reopening.
- Deteksi otomatis pola anomali: pengunduran 4 kali atau lebih, perubahan jadwal tanpa Berita Acara, perubahan jadwal pada akhir jam kerja.
- Manajemen sanggahan penyedia dengan pemantauan SLA jawaban 3 hari kerja.
- Mini bar progres tahapan (Persiapan, Pemilihan, Kontrak, Pelaksanaan, Serah Terima) dengan persentase pada tiap kartu paket.

### Checklist Audit Kepatuhan
- Enam kategori audit: beban kerja dan konflik kepentingan, kedisiplinan jadwal, reviu dokumen, evaluasi dan kualifikasi, integritas dan audit trail, serta penanganan sanggahan.
- Jawaban berpolaritas temuan: jawaban "ya" selalu berarti ditemukan masalah, konsisten dengan indikator risiko di seluruh aplikasi.
- Rekapitulasi persentase temuan per kategori dengan indikator visual.

### Data Pengadaan dan Laporan
- Master data OPD, penyedia, dan paket pengadaan (termasuk impor/kelola paket dengan kode RUP).
- Laporan rekapitulasi yang dapat dicetak dengan identitas dokumen.

### Peta Sebaran Kegiatan Pengadaan
- Visualisasi pemetaan spasial paket pengadaan berbasis OpenStreetMap (Leaflet.js) untuk 20 kecamatan dan 275 desa di Kabupaten Banjarnegara.
- Ringkasan statistik sebaran anggaran dan status pelaksanaan fisik paket di lapangan.

### Perpesanan Pekerjaan (LPSE & Pokja)
- Ruang koordinasi chat per paket pekerjaan antara Kepala LPSE dan Pokja terkait.
- Rekam jejak klarifikasi kendala lapangan, instruksi percepatan, dan riwayat komunikasi per paket.

### Push Notifikasi Multi-Akun & In-App Center
- **Notifikasi Peramban Instan (W3C Web Push VAPID):** Notifikasi tetap terkirim secara *real-time* ke perangkat pengguna meski browser berada di latar belakang atau tab ditutup.
- **In-App Notification Center:** Dropdown lonceng pada navbar dengan badge dinamis jumlah belum dibaca, panel preview cepat, dan halaman riwayat lengkap (`/notifikasi`).
- **Pemicu Notifikasi Multi-Event:**
  - *Perpesanan Paket:* Notifikasi langsung saat ada pesan masuk baru dari Pokja atau Kepala LPSE.
  - *Early Warning System (EWS):* Peringatan kritis ketika paket mengalami pengunduran jadwal >3x atau sanggahan rekanan mendekati batas SLA 3 hari.
  - *Pembaruan Progres:* Pemberitahuan otomatis saat Pokja menginput capaian progres fisik pekerjaan.
- **Live Synthesized Audio Alert:** Peringatan audio menggunakan Web Audio API Synthesizer (nada ganda harmonis 520Hz & 660Hz) tanpa berkas MP3 eksternal, anti-gagal, dan tanpa latensi jaringan.
- **Desain Toast Anti-AI-Slop:** Toast notifikasi krisp 4px (`.toast-gov`) berstandar kontras tinggi WCAG AA, non-floating, dan bergaya *Swiss Government Data-Dense*.

### Portal Taktis Pokja Pemilihan (Anti-AI-Slop)
- **Dasbor Taktis & Triage:** 4 kartu status terarah (*Butuh Tindakan Segera*, *Sedang Berjalan*, *Selesai Tuntas*, dan *Skor Risiko Pokja Interaktif*), modal rincian risiko transparan, filter chip instan, dan pengurutan prioritas triage.
- **Sanggahan Rekanan & SLA 3 Hari Kerja (`/pokja-sanggahan`):** Pemantauan keberatan rekanan khusus paket kelolaan Pokja, countdown status SLA (*Terlewat, Mendesak, Berjalan, Selesai*), serta modal keputusan resmi Pokja yang otomatis menyelesaikan alert anomali.
- **Linimasa & Log Audit Perubahan Jadwal (`/pokja-jadwal`):** Audit addendum linimasa tahapan tender, deteksi dini perubahan tanpa Berita Acara (BA), dan pemantauan paket berisiko tinggi (jadwal diubah $>3\times$).
- **Lembar Kendali Kepatuhan Print-Ready (`/pokja-laporan`):** Dokumen kendali kepatuhan dinas resmi ber-kop Pemerintah Kabupaten Banjarnegara / UKPBJ LPSE, ringkasan eksekutif, rekap paket, dan kolom tanda tangan fisik sah (Kepala UKPBJ & Ketua Pokja).

### Keamanan dan Aksesibilitas
- Autentikasi sesi Laravel dengan proteksi middleware pada seluruh rute.
- Pengecualian CSRF pada rute logout untuk mencegah error 419 Page Expired saat sesi berakhir.
- Kontras warna memenuhi standar WCAG 2.1 level AA (diverifikasi dengan skrip pemeriksa kontras).
- Tabel responsif yang berubah menjadi kartu pada layar mobile.

## Teknologi

| Komponen | Keterangan |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Push Notification | W3C Web Push API, `minishlink/web-push`, Service Worker (`sw.js`) VAPID |
| Database | MySQL 8 (kompatibel MariaDB 10.4+) |
| Antarmuka | Bootstrap 5.3.3, Bootstrap Icons |
| Peta Spasial | Leaflet.js, OpenStreetMap |
| Grafik | Chart.js 4.4.3 |
| Build aset | Tanpa Vite; seluruh aset dimuat melalui CDN & runtime mandiri |

## Persyaratan Sistem

- PHP 8.2 atau lebih baru dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `ctype`, `json`, dan `gmp`/`bcmath`.
- Composer 2.
- MySQL 8 atau MariaDB 10.4+.
- Web server lokal (contoh: XAMPP, Laragon, atau portable PHP di `.tools/`) atau server produksi dengan Nginx/Apache.

## Instalasi

1. Kloning repositori dan masuk ke direktori aplikasi:

```bash
git clone https://github.com/diskonnekted/simonpokja.git
cd simonpokja
```

2. Pasang dependensi:

```bash
composer install
```

3. Salin berkas lingkungan dan buat kunci aplikasi:

```bash
copy .env.example .env
php artisan key:generate
```

4. Sesuaikan konfigurasi database pada berkas `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitoring_pokja
DB_USERNAME=root
DB_PASSWORD=
```

5. Buat skema database, lalu jalankan seeder:

```bash
php artisan migrate --seed
```

6. (Opsional) Muat data simulasi kinerja untuk pengujian:

```bash
php artisan db:seed --class=SimulasiKinerjaSeeder --force
```

Seeder ini bersifat idempotent dan aman dijalankan berulang. Data simulasi otomatis mengikuti tahun anggaran berjalan sehingga langsung tampil di halaman monitoring.

7. Jalankan aplikasi:

```bash
php artisan serve
```

Aplikasi dapat diakses pada alamat `http://127.0.0.1:8080`.

### Menjalankan dengan Portable PHP (Windows)

Tersedia bundel PHP 8.3 portable di dalam direktori `.tools/` untuk lingkungan Windows tanpa perlu instalasi global:

```cmd
run.bat
```

Atau melalui PowerShell:
```powershell
.\serve.ps1
```

## Akun Bawaan

| Peran | Email | Kata Sandi | Hak Akses |
|---|---|---|---|
| Administrator LPSE | `admin@banjarnegara.go.id` | `password` | Akses penuh seluruh modul admin, peta, pemantauan, dan audit |
| Pokja I (Barang) | `pokja1@banjarnegara.go.id` | `password` | Dasbor kerja Pokja I, pembaruan progres paket, & perpesanan |
| Pokja II (Konsultansi) | `pokja2@banjarnegara.go.id` | `password` | Dasbor kerja Pokja II, pembaruan progres paket, & perpesanan |
| Pokja III (Konstruksi) | `pokja3@banjarnegara.go.id` | `password` | Dasbor kerja Pokja III, pembaruan progres paket, & perpesanan |
| Pokja IV (Jasa Lainnya) | `pokja4@banjarnegara.go.id` | `password` | Dasbor kerja Pokja IV, pembaruan progres paket, & perpesanan |
| Pokja V (Swakelola) | `pokja5@banjarnegara.go.id` | `password` | Dasbor kerja Pokja V, pembaruan progres paket, & perpesanan |

Segera ubah kata sandi bawaan setelah penerapan pada lingkungan produksi.

## Struktur Direktori Penting

```
app/
  Http/Controllers/      Logika kontroler (Pokja, Pemantauan, Notifikasi, Master data)
  Models/                Model Eloquent (Pokja, PaketPengadaan, PushSubscription, Notifikasi)
  Services/              NotificationService (Orkestrator Web Push VAPID & log database)
database/
  migrations/            Skema tabel pengadaan, audit, pesan, dan push notifikasi
  seeders/               PokjaSeeder, SirupDummySeeder, SimulasiKinerjaSeeder
public/
  sw.js                  Service Worker W3C penangan event push background
resources/views/
  layouts/               Layout utama, lonceng notifikasi (_notifications.blade.php), toast
  pokja/                 Dashboard kinerja, detail Pokja, kelola Pokja
  pemantauan/            Paket bermasalah, perubahan jadwal, sanggahan
  notifikasi/            Halaman log riwayat notifikasi pengguna
routes/web.php          Definisi seluruh rute aplikasi dan API Web Push
```

## Model Data Utama

| Tabel | Fungsi |
|---|---|
| pokjas | Identitas Pokja, bidang, anggota (JSON), trigger penilaian |
| paket_pengadaans | Paket pengadaan dengan kode RUP, pagu, HPS, status, risiko, tahap tender |
| tahapans | Baris tahapan proses per paket (urutan, status, tanggal rencana/aktual) |
| perubahan_jadwals | Riwayat perubahan jadwal dengan penanda Berita Acara dan jam pencatatan |
| sanggahans | Sanggahan penyedia, tanggal masuk/jawab, hasil, substantif |
| alert_anomalis | Peringatan otomatis berjenjang (info, warning, critical) |
| audit_checklists | Hasil checklist audit 6 kategori per Pokja |
| audit_logs | Rekam jejak perubahan data untuk kepentingan audit |
| pesan_pakets | Riwayat percakapan/koordinasi pekerjaan per paket |
| push_subscriptions | Pendaftaran endpoint & public key browser per pengguna (Web Push VAPID) |
| notifikasis | Log riwayat pemberitahuan in-app & alert sistem |

## Perhitungan Skor Risiko

Skor risiko Pokja (0-100) merupakan agregasi tertimbang dari:

- Rasio beban kerja terhadap kapasitas anggota.
- Persentase paket dengan pengunduran jadwal berulang.
- Persentase tender gagal.
- Kepatuhan SLA jawaban sanggahan (batas 3 hari kerja).
- Jumlah alert anomali ber tingkat kritis.

Label risiko: RENDAH (skor rendah), TINGGI (skor menengah), KRITIS (skor tinggi). Label dan ambang batas dihitung pada atribut turunan model `Pokja` sehingga konsisten antara dashboard, detail Pokja, dan laporan.

## Struktur Checklist Audit

| Kategori | Fokus Pemeriksaan |
|---|---|
| 1 | Beban kerja dan konflik kepentingan |
| 2 | Kedisiplinan jadwal tahapan (pengunduran, Berita Acara, jam kerja) |
| 3 | Reviu dokumen pemilihan bersama PPK |
| 4 | Evaluasi dan kualifikasi peserta |
| 5 | Integritas dan audit trail |
| 6 | Penanganan sanggahan dan tender gagal |

Seluruh butir berpolaritas temuan: jawaban "ya" menandakan adanya masalah atau penyimpangan yang perlu ditindaklanjuti.

## Perintah Artisan & Pengujian Sistem

```bash
php artisan migrate --seed                                # Migrasi dan data awal
php artisan db:seed --class=SimulasiKinerjaSeeder --force # Data simulasi kinerja
php artisan db:seed --class=SirupDummySeeder --force      # Paket dummy berkode RUP
php artisan serve                                         # Server pengembangan

# Menjalankan Uji Otomasi Notifikasi & Simulasi Pengadaan
php .tools/php/php.exe scratch/test_notifications.php
php .tools/php/php.exe scratch/test_procurement_simulation.php
```

## Lisensi

Aplikasi ini dikembangkan untuk kebutuhan internal Pemerintah Kabupaten Banjarnegara.
