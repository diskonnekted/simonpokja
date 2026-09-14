# Dependency Graph & Critical Files — SIMONPOKJA

## 🔴 Critical Files (Paling banyak di-import/digunakan — JANGAN ubah tanpa reviu menyeluruh)
| File | Digunakan Oleh | Tipe | Peran Kritis |
|---|---|---|---|
| `app/Models/Pokja.php` | Controller Pokja, Dashboard, Seeder, Views | Model | Kalkulasi skor risiko Pokja, agregasi anomali dan beban |
| `app/Models/PaketPengadaan.php` | PaketController, Pemantauan, Peta, Pokja | Model | Entitas sentral pengadaan, pagu, koordinat, tahapan |
| `resources/views/layouts/app.blade.php` | Seluruh Blade Views | Layout View | Menampung Sidebar, Topbar, Token CSRF, Notifikasi & Toast, CSS & JS CDN |
| `app/Services/NotificationService.php` | PesanPaketController, Pemantauan, PokjaDashboard | Service | Orkestrator notifikasi multi-akun: rekam DB, broadcast Web Push VAPID, penanganan SSL Windows |
| `app/Http/Controllers/PokjaController.php` | Routes Kinerja Pokja, Checklist, Alert | Controller | Logika utama monitoring risiko dan audit 6 kategori |
| `routes/web.php` | Seluruh request HTTP | Routing | Pengaturan seluruh rute dan proteksi middleware role |

## 🟡 High-Impact Files (Perubahan berdampak luas)
| File | Bergantung Pada | Digunakan Oleh | Tingkat Dampak |
|---|---|---|---|
| `app/Models/PerubahanJadwal.php` | PaketPengadaan, Tahapan | PemantauanController | Deteksi anomali jadwal pengadaan |
| `app/Models/Sanggahan.php` | PaketPengadaan, Penyedia | PemantauanController | Kalkulator kepatuhan SLA 3 hari kerja |
| `app/Http/Controllers/PemantauanController.php` | Paket, Sanggahan, Perubahan, NotificationService | Rute Pemantauan | Pemantauan paket berisiko, trigger notifikasi EWS |
| `app/Http/Controllers/PokjaDashboardController.php` | Paket, Sanggahan, PerubahanJadwal, Progres | Rute Pokja Portal | Orkestrator Dasbor Taktis, Sanggahan SLA, Jadwal, dan Lembar Kendali |
| `app/Http/Controllers/NotifikasiController.php` | PushSubscription, Notifikasi | Rute Notifikasi & Push API | Manajemen pendaftaran browser client & tanda baca |
| `resources/views/layouts/_notifications.blade.php`| Bootstrap Icons, Web Audio API, Service Worker | layouts/app.blade.php | Komponen lonceng interaktif, live chime sintetis, & permission prompt |
| `public/sw.js` | Web Push API W3C | Browser Peramban | Background worker penerima payload push saat tab tertutup |
| `resources/views/pesan/_chat-script.blade.php` | PesanPaketController | Pesan & Dasbor Pokja | Skrip interaktif pengiriman chat per pekerjaan |

## 🟢 Leaf Files (Aman diubah — ketergantungan terisolasi)
| File | Fungsi |
|---|---|
| `resources/views/pokja/sanggahan.blade.php` | Tampilan pengawasan Sanggahan & SLA 3 hari kerja Pokja |
| `resources/views/pokja/jadwal.blade.php` | Tampilan linimasa tahapan & log addendum jadwal Pokja |
| `resources/views/pokja/laporan.blade.php` | Tampilan Lembar Kendali Kepatuhan dinas print-ready |
| `resources/views/notifikasi/index.blade.php` | Tampilan pusat log notifikasi pengguna |
| `resources/views/opd/form.blade.php` | Formulir input master OPD |
| `resources/views/penyedia/form.blade.php` | Formulir input master Penyedia |
| `resources/views/peta/index.blade.php` | Tampilan visual peta geografis paket |
| `database/seeders/PetaLokasiSeeder.php` | Data dummy koordinat geografis paket pengadaan |

