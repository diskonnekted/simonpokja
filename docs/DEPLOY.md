# Runbook Deployment Produksi — SIMONPOKJA

> Panduan men-deploy aplikasi ke server produksi **dengan tetap menjaga API integrasi SIBIJAK berjalan** (base URL & struktur `/api/v1` tidak berubah).

---

## Daftar Isi

1. [Prasyarat Server](#1-prasyarat-server)
2. [Ringkasan Langkah](#2-ringkasan-langkah)
3. [Langkah Detail](#3-langkah-detail)
4. [Konfigurasi NGINX](#4-konfigurasi-nginx)
5. [Bagian Khusus API Integrasi (WAJIB)](#5-bagian-khusus-api-integrasi-wajib)
6. [Verifikasi Pasca-Deploy](#6-verifikasi-pasca-deploy)
7. [Update/Rilis Berikutnya](#7-updaterilis-berikutnya)
8. [Daftar Periksa Keamanan](#8-daftar-periksa-keamanan)

---

## 1. Prasyarat Server

| Komponen | Spesifikasi |
|---|---|
| PHP | 8.2.x (FPM) |
| Ekstensi PHP | `bcmath, ctype, curl, fileinfo, json, mbstring, openssl, pdo_mysql, tokenizer, xml` |
| Database | MySQL 8 / MariaDB 10.4+ |
| Web server | NGINX (atau Apache) |
| Tool | Composer 2, Git |
| Direktori deploy | `/var/www/simonpokja` (sesuaikan) |

> Cek: `php -v`, `php -m | grep -E "pdo_mysql|mbstring|openssl"`, `composer --version`.

---

## 2. Ringkasan Langkah

1. Kloning repo → `composer install --no-dev`
2. Buat `.env` produksi → `php artisan key:generate`
3. Siapkan database & `php artisan migrate --force`
4. Seed **khusus produksi** (bukan `db:seed`!)
5. `storage:link`, izin folder, cache optimasi
6. Pasang konfigurasi NGINX
7. Isi token & CORS API → verifikasi `/api/v1/ping`

---

## 3. Langkah Detail

Dijalankan sebagai user `www-data` (atau user sistem) di server.

```bash
# 1. Kloning aplikasi
cd /var/www
git clone https://github.com/diskonnekted/simonpokja.git simonpokja
cd simonpokja

# 2. Install dependensi (mode produksi — tanpa dev)
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Siapkan .env dari template
cp .env.production.example .env
nano .env
#   → isi APP_URL, DB_*, SIBIJAK_API_TOKEN, CORS_ALLOWED_ORIGINS
#   → APP_KEY boleh dibiarkan kosong dulu, akan digenerate di langkah 4

# 4. Generate APP_KEY (mengisi APP_KEY di .env)
php artisan key:generate

# 5. Siapkan database (buat database + user dulu di MySQL bila belum ada)
php artisan migrate --force

# 6. Seed data master produksi (HANYA Pokja + akun login, TANPA data dummy)
php artisan db:seed --class=ProductionSeeder --force

# 7. Symlink storage (untuk upload dokumen, contoh: Berita Acara)
php artisan storage:link

# 8. Izin folder
sudo chown -R www-data:www-data /var/www/simonpokja
sudo chmod -R 775 storage bootstrap/cache

# 9. Optimasi cache (WAJIB di produksi)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache   # opsional

# 10. (Opsional) queue worker bila ada job antrian
#     php artisan queue:work --daemon
```

> ⚠️ **Jangan** menjalankan `php artisan db:seed` (tanpa `--class`) di produksi.
> Seeder utama (`DatabaseSeeder`) berisi **data simulasi/demo** (30 paket, provider,
> evaluasi, alert, dll.) untuk pengujian. Gunakan `ProductionSeeder` seperti di atas.

---

## 4. Konfigurasi NGINX

Template lengkap tersedia di [`deploy/nginx-simonpokja.conf`](../deploy/nginx-simonpokja.conf).

```bash
sudo cp deploy/nginx-simonpokja.conf /etc/nginx/sites-available/simonpokja
sudo nano /etc/nginx/sites-available/simonpokja  # sesuaikan server_name & root
sudo ln -s /etc/nginx/sites-available/simonpokja /etc/nginx/sites-enabled/
sudo nginx -t          # validasi konfigurasi
sudo systemctl reload nginx
```

**Poin penting API di NGINX** — blok ini memastikan `/api/v1/*` diteruskan ke Laravel:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Tanpa `try_files` ini, permintaan `/api/v1/paket` akan `404`. Template di atas sudah
mencakupnya, jadi struktur & base URL API tetap sama persis dengan development.

---

## 5. Bagian Khusus API Integrasi (WAJIB)

### 5.1 Base URL API

Di produksi, base URL API berubah menjadi:

```
https://<domain>/api/v1
```

Contoh: `https://simonpokja.banjarnegarakab.go.id/api/v1/paket`

Struktur path (`/api/v1/...`) **tidak berubah** karena didefinisikan di `routes/api.php`
dan di-register lewat `bootstrap/app.php`. Yang berubah hanya **host + skema (https)**.

### 5.2 Token API (SIBIJAK)

- **Buat token BARU untuk produksi** (jangan pakai token development di `.env` lokal):
  ```bash
  php -r "echo bin2hex(random_bytes(32));"
  ```
- Isi di `.env` produksi:
  ```env
  SIBIJAK_API_TOKEN=xxxx_ganti_dengan_hash_baru
  ```

### 5.3 CORS (origin SIBIJAK produksi)

```env
CORS_ALLOWED_ORIGINS=https://sibijak.banjarnegarakab.go.id,https://sibijak.example.go.id
```

> Hanya origin SIBIJAK **produksi** yang diizinkan. Jangan sertakan `http://127.0.0.1:8086`.

### 5.4 Setelah cache optimasi

Perubahan pada `.env` (token/CORS) baru terbaca setelah `config:cache` dijalankan ulang:

```bash
php artisan config:cache   # jalankan ulang setiap ganti nilai .env
```

### 5.5 Beri tahu tim SIBIJAK

Sampaikan ke tim SIBIJAK:
1. **Base URL baru** (`https://<domain>/api/v1`).
2. **Token baru** (kirim via kanal aman, jangan lewat git/chat publik).
3. Di Postman/koleksi: ubah variabel `baseUrl` dan `token` ke nilai produksi.

---

## 6. Verifikasi Pasca-Deploy

```bash
# 6.1 Aplikasi web (non-API)
curl -I https://<domain>/login          # harus 200

# 6.2 API — TANPA token (harus 401)
curl -i https://<domain>/api/v1/ping    # 401 Unauthorized

# 6.3 API — DENGAN token (harus 200)
curl -i -H "Authorization: Bearer $TOKEN" https://<domain>/api/v1/ping

# 6.4 API — data
curl -s -H "Authorization: Bearer $TOKEN" "https://<domain>/api/v1/statistik" | head
```

Centang bila semua:

- [ ] `/login` membuka halaman login
- [ ] `/api/v1/ping` tanpa token → `401`
- [ ] `/api/v1/ping` dengan token → `200` + JSON aplikasi
- [ ] `/api/v1/pstatistik`, `/api/v1/pokja`, `/api/v1/paket` → JSON valid
- [ ] Login admin berfungsi, lalu **ganti password default**
- [ ] Upload dokumen (jika ada) berfungsi lewat `/storage`

---

## 7. Update/Rilis Berikutnya

```bash
cd /var/www/simonpokja
php artisan down                    # mode maintenance (opsional)
git pull origin main
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force         # bila ada migrasi baru
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

---

## 8. Daftar Periksa Keamanan

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` terisi (bandingkan dengan lokal bila perlu)
- [ ] `SIBIJAK_API_TOKEN` token baru, bukan token development
- [ ] `CORS_ALLOWED_ORIGINS` = origin produksi SIBIJAK
- [ ] Password akun admin/Pokja **tidak** lagi `password`
- [ ] `.env` tidak ter-commit (sudah di `.gitignore`)
- [ ] HTTPS aktif (redirect HTTP → HTTPS di NGINX)
- [ ] Folder `storage/` & `bootstrap/cache/` tidak bisa diakses publik

---

*Dokumen teknis API untuk konsumen: [`docs/API.md`](API.md).*
*Koleksi Postman: `SIMONPOKJA-API.postman_collection.json`.*