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

### Keamanan dan Aksesibilitas
- Autentikasi sesi Laravel dengan proteksi middleware pada seluruh rute.
- Kontras warna memenuhi standar WCAG 2.1 level AA (diverifikasi dengan skrip pemeriksa kontras).
- Tabel responsif yang berubah menjadi kartu pada layar mobile.

## Teknologi

| Komponen | Keterangan |
|---|---|
| Framework | Laravel 12 (PHP 8.2) |
| Database | MySQL 8 (kompatibel MariaDB 10.4+) |
| Antarmuka | Bootstrap 5.3.3, Bootstrap Icons |
| Grafik | Chart.js 4.4.3 |
| Build aset | Tanpa Vite; seluruh aset dimuat melalui CDN |

## Persyaratan Sistem

- PHP 8.2 atau lebih baru dengan ekstensi: pdo_mysql, mbstring, openssl, ctype, json, fileinfo.
- Composer 2.
- MySQL 8 atau MariaDB 10.4+.
- Web server lokal (contoh: XAMPP, Laragon) atau server produksi dengan Nginx/Apache.

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

## Akun Bawaan

| Peran | Email | Kata Sandi |
|---|---|---|
| Administrator | admin@banjarnegara.go.id | password |

Segera ubah kata sandi bawaan setelah penerapan pada lingkungan produksi.

## Struktur Direktori Penting

```
app/
  Http/Controllers/      Logika kontroler (Pokja, Pemantauan, Laporan, Master data)
  Models/                Model Eloquent beserta atribut turunan (skor risiko, progres tahapan)
database/
  migrations/            Skema tabel: pokjas, paket_pengadaans, tahapans, perubahan_jadwals,
                         sanggahans, alert_anomalis, audit_checklists, audit_logs, dan lainnya
  seeders/               PokjaSeeder, SirupDummySeeder, SimulasiKinerjaSeeder
resources/views/
  layouts/               Kerangka layout utama dengan sidebar dan CSS responsif
  pokja/                 Dashboard kinerja, detail Pokja, kelola Pokja
  pemantauan/            Paket bermasalah, perubahan jadwal, sanggahan
routes/web.php          Definisi seluruh rute aplikasi
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

## Perintah Artisan yang Relevan

```bash
php artisan migrate --seed                                # Migrasi dan data awal
php artisan db:seed --class=SimulasiKinerjaSeeder --force # Data simulasi kinerja
php artisan db:seed --class=SirupDummySeeder --force      # Paket dummy berkode RUP
php artisan serve                                         # Server pengembangan
```

## Lisensi

Aplikasi ini dikembangkan untuk kebutuhan internal Pemerintah Kabupaten Banjarnegara.
