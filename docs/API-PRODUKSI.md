# Dokumentasi API SIMONPOKJA — Server Produksi

> Lingkungan **PRODUKSI**. Konsumen: aplikasi **SIBIJAK**.
> Versi API: **v1** · Status: **read-only (GET)**.

Dokumen ini khusus untuk konsumen yang mengakses server produksi.
Untuk referensi lengkap tiap endpoint (parameter, contoh respons, kamus enum),
lihat [`docs/API.md`](API.md).

---

## 1. Informasi Server Produksi

| Item | Nilai |
|---|---|
| Base URL | `https://simonja.rapidnet.id/api/v1` |
| Skema | HTTPS |
| Lingkungan | `production` (`APP_DEBUG=false`) |
| Metode | `GET` saja |
| Format | JSON (notasi `snake_case`) |
| Zona waktu server | UTC (`+00:00`) — konversi ke WIB saat tampil |
| Rate limit | Throttle Laravel (`429` bila berlebih) |

Contoh lengkap:
```
https://simonja.rapidnet.id/api/v1/paket?tahun=2026&per_page=10
```

---

## 2. Autentikasi

Setiap permintaan wajib menyertakan token **bearer** pada header:

```http
Authorization: Bearer <token-produksi>
```

Nilai token produksi **tidak dipublikasikan di dokumen ini** karena repo ini publik.
Token disimpan di `.env` server produksi (`SIBIJAK_API_TOKEN`) dan diberikan kepada
konsumen lewat kanal aman. Gunakan nilai tersebut pada contoh di bawah dengan
mengganti `<TOKEN_PRODUKSI>`.

> 🔒 **RAHASIA.** Token ini setara kunci akses data pengadaan. Distribusikan hanya
> lewat kanal aman (jangan lewat git publik/chat publik). Bila sempat bocor, minta
> admin mengganti nilai `SIBIJAK_API_TOKEN` di `.env` lalu jalankan
> `php artisan config:cache`.

**Respons bila token tidak/salah disertakan:**

```json
HTTP/1.1 401 Unauthorized
{ "success": false, "message": "Tidak terautentikasi: token API tidak valid atau tidak disertakan." }
```

---

## 3. CORS

Origin pemanggil diizinkan lewat `CORS_ALLOWED_ORIGINS` di `.env`.

Saat ini di produksi bernilai `*` (semua origin) — **masih sementara**. Setelah
domain produksi SIBIJAK diketahui, admin akan membatasi menjadi:

```env
CORS_ALLOWED_ORIGINS=https://<domain-sibijak-produksi>
```

---

## 4. Daftar Endpoint (ringkas)

| Grup | Endpoint |
|---|---|
| Health | `GET /ping` |
| Master | `GET /opd` · `GET /opd/{id}` · `GET /pokja` · `GET /pokja/{id}` · `GET /penyedia` · `GET /penyedia/{id}` |
| Paket | `GET /paket` · `GET /paket/{id}` · `GET /paket/{id}/tahapan` · `/evaluasi` · `/sanggahan` · `/progres` · `/perubahan-jadwal` · `/pesan` · `/alert` |
| Monitoring | `GET /tahapan` · `/evaluasi` · `/sanggahan` · `/progres` · `/alert` · `/perubahan-jadwal` · `/pesan` · `/audit-checklist` · `/audit-log` |
| Agregat | `GET /statistik` · `GET /kinerja-pokja` |

Total **27 route**. Detail parameter & respons → `docs/API.md`.

---

## 5. Contoh Permintaan (curl)

```bash
TOKEN="<TOKEN_PRODUKSI>"

# 1) Cek koneksi & validitas token
curl -i -H "Authorization: Bearer $TOKEN" https://simonja.rapidnet.id/api/v1/ping

# 2) Paket tahun 2026 status kontrak (10 item)
curl -s -H "Authorization: Bearer $TOKEN" \
  "https://simonja.rapidnet.id/api/v1/paket?tahun=2026&status=kontrak&per_page=10"

# 3) Statistik agregat (dashboard)
curl -s -H "Authorization: Bearer $TOKEN" \
  "https://simonja.rapidnet.id/api/v1/statistik?tahun=2026"
```

### Contoh JavaScript (fetch)

```js
const BASE = 'https://simonja.rapidnet.id/api/v1';
const TOKEN = '<TOKEN_PRODUKSI>';

const res = await fetch(`${BASE}/statistik`, {
  headers: { Authorization: `Bearer ${TOKEN}` },
});
const body = await res.json(); // { success, data }
```

---

## 6. Format Respons Standar

```json
{
  "success": true,
  "message": "OK",
  "data": { },              // objek / array
  "meta": {                 // hanya endpoint terpaginasi
    "current_page": 1,
    "per_page": 15,
    "total": 65,
    "last_page": 5
  },
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." }
}
```

Klien disarankan memeriksa `success === true` sebelum membaca `data`.

---

## 7. Beda dengan Lingkungan Development

| Aspek | Development | Produksi |
|---|---|---|
| Base URL | `http://127.0.0.1:8081/api/v1` | `https://simonja.rapidnet.id/api/v1` |
| Token | `<TOKEN_DEV>` | `<TOKEN_PRODUKSI>` |
| CORS | origin dev SIBIJAK (8086) | `*` (sementara) |
| Skema | HTTP | HTTPS |

> Struktur path (`/api/v1/...`) **identik** di kedua lingkungan.

---

## 8. Postman

Buka koleksi `SIMONPOKJA-API.postman_collection.json`, lalu ubah dua variabel:

| Variabel | Nilai produksi |
|---|---|
| `baseUrl` | `https://simonja.rapidnet.id/api/v1` |
| `token` | `<TOKEN_PRODUKSI>` |

---

*Kontak teknis: pengelola SIMONPOKJA (admin LPSE Kab. Banjarnegara).*