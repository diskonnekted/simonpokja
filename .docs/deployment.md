# Deployment Guide — SIMONPOKJA

## Deployment Targets
| Target | Tipe | Port | Stack Support |
|---|---|---|---|
| Development Lokal | XAMPP / Laragon / Portable PHP (`.tools/`) | `8080` | PHP 8.3 (Portable), MySQL 8 |
| Produksi Server LPSE | Nginx / Apache on Linux/Windows | `80` / `443` | PHP-FPM 8.2+, MariaDB 10.4+ |

### Portable Runtime Lokal (Windows)
- Direktori `.tools/php/`: PHP 8.3.33 NTS (x64) dengan modul `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`.
- Direktori `.tools/composer.phar`: Binary Composer mandiri.
- Eksekusi instan: `run.bat` (CMD) atau `.\serve.ps1` (PowerShell).

## Environment Mapping
| Variable | Development | Production | Notes |
|---|---|---|---|
| `APP_ENV` | `local` | `production` | Mode aplikasi |
| `APP_DEBUG` | `true` | `false` | Wajib false di produksi |
| `APP_URL` | `http://127.0.0.1:8080` | `https://simonpokja.banjarnegarakab.go.id` | Domain resmi (Wajib HTTPS untuk Web Push) |
| `DB_CONNECTION` | `mysql` | `mysql` | Driver database |
| `DB_DATABASE` | `monitoring_pokja` | `simonpokja_prod` | Nama database |
| `VAPID_PUBLIC_KEY` | *(Tersedia di .env)* | *(Generate khusus prod)* | Kunci publik peramban Web Push W3C |
| `VAPID_PRIVATE_KEY`| *(Tersedia di .env)* | *(Rahasia server prod)* | Kunci privat penandatangan JWT VAPID |
| `VAPID_SUBJECT` | `mailto:admin@banjarnegara.go.id` | `mailto:lpse@banjarnegarakab.go.id` | Kontak identitas pengirim push service |

## Pre-Deploy Checklist
- [ ] Berkas `.env` disalin dari `.env.example`
- [ ] `php artisan key:generate` sudah dijalankan
- [ ] Database dibuat dan kredensial disesuaikan
- [ ] `php artisan migrate --force` sukses tanpa error (termasuk migrasi `push_subscriptions` & `notifikasis`)
- [ ] `php artisan db:seed --class=DatabaseSeeder --force` (untuk data inisialisasi)
- [ ] Kunci `VAPID_PUBLIC_KEY` dan `VAPID_PRIVATE_KEY` terkonfigurasi di `.env`
- [ ] Protokol HTTPS aktif dengan sertifikat SSL valid (Prasyarat wajib browser untuk Service Worker `sw.js` & Web Push API)
- [ ] Ekstensi PHP `openssl`, `curl`, `mbstring`, `fileinfo`, dan `gmp`/`bcmath` aktif
- [ ] Direktori `storage` dan `bootstrap/cache` memiliki izin tulis (writable / 775)
- [ ] `APP_DEBUG=false` untuk mencegah information disclosure

## Post-Deploy Verification
1. Verifikasi login dengan akun `admin@banjarnegara.go.id`
2. Cek render grafik Chart.js dan layer Peta Leaflet
3. Uji coba pengiriman pesan paket dan verifikasi penerimaan Web Push notifikasi di browser
4. Cek pendaftaran subscription di tabel `push_subscriptions`
5. Cek error log di `storage/logs/laravel.log`

## Rollback Plan
| Skenario | Aksi |
|---|---|
| Migration gagal | Jalankan `php artisan migrate:rollback` jika aman |
| Kesalahan kode | Checkout git tag rilis stabil sebelumnya |
