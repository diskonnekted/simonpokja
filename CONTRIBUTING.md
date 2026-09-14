# Panduan Kolaborasi & Git Workflow — SIMONPOKJA

Dokumen ini memuat standar dan etika alur kerja kolaborasi Git/GitHub untuk seluruh kontributor pengembang sistem **SIMONPOKJA** (Layanan Pengadaan Secara Elektronik Kabupaten Banjarnegara).

---

## 1. Prinsip Utama Kolaborasi

> 💡 **Aturan Emas:** **Dilarang melakukan push atau commit langsung ke branch `main`.**

1. **Branch `main` adalah Kode Suci:** Branch `main` harus selalu berada dalam kondisi stabil, dapat dijalankan (*build pass*), dan siap dideploy ke server (*production-ready*).
2. **Isolasi Fitur:** Seluruh pekerjaan fitur baru, perbaikan bug, atau dokumentasi wajib dikerjakan di dalam **Branch Terpisah (Feature Branch)**.
3. **Penyatuan via Pull Request (PR):** Penggabungan kode ke `main` hanya dilakukan melalui proses Pull Request setelah melewati review dan pengecekan perbedaan kode (*diff*).

---

## 2. Standar Penamaan Branch

Gunakan format: `<tipe>/<deskripsi-singkat>` (huruf kecil semua, pisahkan kata dengan tanda hubung `-`):

| Awalan | Tujuan Penggunaan | Contoh Nama Branch |
|---|---|---|
| `feat/` | Penambahan fitur baru atau modul halaman baru | `feat/webpush-dan-portal-pokja`, `feat/export-excel` |
| `fix/` | Perbaikan bug atau penanganan error | `fix/sla-sanggahan-counter`, `fix/null-safety-pokja` |
| `refactor/` | Penataan ulang struktur kode tanpa merubah fungsi | `refactor/clean-model-scopes`, `refactor/service-layer` |
| `docs/` | Penambahan atau pembaruan dokumentasi proyek | `docs/update-contributing`, `docs/api-spec` |
| `style/` | Penyesuaian visual, CSS, tata letak antarmuka | `style/toast-gov-contrast`, `style/pokja-table-padding` |
| `chore/` | Pengaturan build, dependensi composer/npm | `chore/update-web-push-deps` |

---

## 3. Format Pesan Commit (Conventional Commits)

Pesan commit harus singkat, terarah, dan bermakna. Gunakan standar **Conventional Commits**:

```text
<tipe>(<lingkup/scope>): <deskripsi singkat dalam bahasa Inggris atau Indonesia yang jelas>
```

### Contoh Praktik Baik:
- `feat(notification): implement W3C Web Push VAPID and in-app notification center`
- `feat(pokja): add tactical operational pages for sanggahan, timeline, and reporting`
- `fix(core): enhance fillable attributes, user relations, and view null-safety`
- `docs(guide): add collaboration guidelines and git workflow standard`

> ❌ **Hindari pesan commit tidak bermakna:**  
> `update file`, `fix error`, `revisi lagi`, `commit sekarang`.

---

## 4. Perlindungan Data Sensitif & Sanitasi Berkas (Zero Leak)

Sebelum melakukan `git add` dan `git commit`, pastikan kepatuhan keamanan berikut:

1. **Dilarang Keras Memasukkan Kredensial:**
   - Berkas konfigurasi rahasia: `.env`, `.env.local`, `.env.production`
   - Kunci privat VAPID, token API, password database asli
   - Dump basis data lokal (`*.sqlite`, `*.sql`)
2. **Penggunaan `.env.example`:**
   - Jika menambahkan variabel lingkungan baru (contoh: `VAPID_PUBLIC_KEY`), daftarkan variabel tersebut di `.env.example` dengan **nilai placeholder kosong**.
3. **Eksklusi Berkas Internal:**
   - Berkas artefak sesi AI (`handover.md`, `app-context.md`, `scratch/`) tidak boleh dipublikasikan ke repositori publik/tim dan wajib masuk ke `.gitignore`.

---

## 5. Alur Kerja Langkah demi Langkah (Step-by-Step)

### A. Memulai Tugas Baru
```bash
# 1. Selalu sinkronkan branch main lokal dengan versi terbaru di GitHub
git checkout main
git pull origin main

# 2. Buat branch baru dari main
git checkout -b feat/nama-fitur-anda
```

### B. Menyimpan Progres Kerja (Commit Terarah / Atomic Commit)
Pecah perubahan menjadi commit-commit kecil yang terfokus:
```bash
# Periksa status perubahan
git status

# Tambahkan file spesifik (hindari git add . tanpa seleksi)
git add app/Http/Controllers/FiturBaruController.php resources/views/fitur/index.blade.php

# Lakukan commit dengan pesan bermakna
git commit -m "feat(fitur): implement new feature controller and view"
```

### C. Mengunggah ke GitHub & Mengajukan Pull Request (PR)
```bash
# Push branch Anda ke remote origin
git push -u origin feat/nama-fitur-anda
```

Setelah push berhasil:
1. Buka repositori di GitHub: [github.com/diskonnekted/simonpokja](https://github.com/diskonnekted/simonpokja).
2. Klik tombol hijau **"Compare & pull request"**.
3. Tuliskan deskripsi ringkas:
   - Apa saja perubahan yang dibuat.
   - Cara menguji fitur tersebut secara lokal.
4. Klik **"Create pull request"**.
5. Tunggu proses peninjauan (*code review*) dari rekan tim / maintainer sebelum kode di-merge ke branch `main`.

---

## 6. Penyelesaian Konflik (Merge Conflict)

Jika branch `main` telah diperbarui oleh kolaborator lain saat Anda sedang mengerjakan branch fitur:
```bash
# Tarik update main ke branch fitur Anda
git checkout feat/nama-fitur-anda
git fetch origin
git merge origin/main

# Selesaikan konflik pada editor kode jika ada, lalu:
git add <file-yang-sudah-dibereskan>
git commit -m "chore: merge origin/main into feat/nama-fitur-anda"
git push origin feat/nama-fitur-anda
```
