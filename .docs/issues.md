# Issue Tracker — SIMONPOKJA

## Open Issues
*Tidak ada isu terbuka saat ini.*

## Resolved Issues
| ID | Tanggal | Deskripsi | Solusi | Status |
|---|---|---|---|---|
| ISS-001 | 2026-09-12 | Integrasi perpesanan Kepala LPSE-Pokja per paket pekerjaan | Penambahan tabel `pesan_pakets`, controller `PesanPaketController`, dan modal chat di rute `/paket/{paket}/pesan` | RESOLVED |
| ISS-002 | 2026-09-12 | Kontras teks antarmuka memenuhi WCAG AA | Update kelas CSS `.text-secondary`, `.text-muted` dengan warna `#5a6268` di `layouts/app.blade.php` | RESOLVED |
| ISS-003 | 2026-09-12 | Fatal error PHP sistem XAMPP (`browscap.ini` missing & standard module fail) | Penyediaan runtime mandiri PHP 8.3.33 NTS + Composer di `.tools/` dan launcher `run.bat` & `serve.ps1` | RESOLVED |
| ISS-004 | 2026-09-12 | Teks tombol 'Detail Pekerjaan' dalam popup Leaflet tidak terbaca saat tidak di-hover | Specificity CSS bawaan Leaflet `.leaflet-container a { color: #0078A8 }` menimpa teks tombol biru. Diatasi dengan selector `.leaflet-container a.btn { color: #ffffff !important }` & `.text-white` | RESOLVED |
| ISS-005 | 2026-09-12 | Atribut `pokja_id`, `risiko`, `tender_gagal`, `tahap_tender`, `desa`, `kecamatan` belum ada di `$fillable` model `PaketPengadaan` | Menambahkan seluruh kolom pengadaan aktif ke dalam properti `$fillable` di `app/Models/PaketPengadaan.php` sehingga sinkronisasi beban kerja Pokja terjamin | RESOLVED |
| ISS-006 | 2026-09-12 | Potensi crash di halaman detail pemantauan jika paket belum memiliki asosiasi Pokja atau OPD | Menambahkan null-safe operator `$paket->pokja?->nama ?? '-'` dan fallback nama OPD di `resources/views/pemantauan/show.blade.php` | RESOLVED |
| ISS-007 | 2026-09-12 | Null safety user resolution pada endpoint API notifikasi saat dipanggil via worker/sub-request | Menerapkan fallback `$request->user() ?: auth()->user()` pada `NotifikasiController` dan validasi otentikasi di `resources/views/layouts/_notifications.blade.php` | RESOLVED |
| ISS-008 | 2026-09-12 | Ketiadaan sistem notifikasi push mandiri per akun untuk koordinasi chat paket dan peringatan EWS | Mengimplementasikan sistem Web Push W3C VAPID mandiri (`minishlink/web-push`), Service Worker `public/sw.js`, in-app center, live audio synthesizer chime (Web Audio API), dan toast notifikasi Swiss Government krisp 4px | RESOLVED |
| ISS-009 | 2026-09-12 | Dasbor Pokja 1 kosong akibat hardcoded filter tahun berjalan `date('Y')` | Menerapkan fallback tahun anggaran cerdas di `PokjaDashboardController` & `PokjaController` serta menambahkan filter dropdown TA di antarmuka | RESOLVED |
| ISS-010 | 2026-09-12 | Galat 419 "Page Expired" saat logout dari sesi peramban | Mengecualikan rute `logout` dari verifikasi CSRF di `bootstrap/app.php` dan mendukung metode GET/POST di `routes/web.php` | RESOLVED |
| ISS-011 | 2026-09-12 | Gagal login menggunakan nama email domain `@banjarnegarakab.go.id` | Menyeragamkan domain kredensial resmi pada seeder dan panduan sistem menjadi `@banjarnegara.go.id` | RESOLVED |
| ISS-012 | 2026-09-12 | Urutan prioritas triage di Dasbor Pokja teracak oleh skrip klik header tabel | Memperbaiki skrip pengurutan JavaScript agar memprioritaskan paket kritis/mendesak dan menyediakan *quick filter chips* | RESOLVED |

