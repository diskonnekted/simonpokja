# Arsitektur Sistem — SIMONPOKJA

## 1. Ikhtisar Arsitektur
SIMONPOKJA menerapkan arsitektur Monolitik Klasik berbasis Laravel 12 dengan pemisahan tanggung jawab yang jelas (MVC), integrasi frontend CDN tanpa kompilasi build kompleks, serta sistem **Web Push Notification W3C VAPID** dan **In-App Notification Center**.

```
[ Klien Browser / Mobile (Desktop OS & Android) ]
        │                                ▲
        │ HTTP / Session Cookies         │ Web Push Service (W3C Push API / FCM Gateway)
        ▼                                │
┌────────────────────────────────────────┴────────────────────┐
│                         LARAVEL 12                          │
│                                                             │
│   [ Routing & Middleware Chain ]                            │
│     ├── Authenticate (Session)                              │
│     └── RoleMiddleware (admin, pokja)                       │
│            │                                                │
│            ▼                                                │
│   [ Presentation Layer (Blade Views & Service Worker) ]     │
│     ├── Bootstrap 5.3.3 + Bootstrap Icons 1.11.3            │
│     ├── Chart.js 4.4.3 & Leaflet.js                         │
│     ├── Service Worker (public/sw.js)                       │
│     ├── In-App Notification Center (_notifications.blade)   │
│     └── Swiss Government Crisp Toast (.toast-gov)           │
│            │                                                │
│            ▼                                                │
│   [ Controllers Layer (app/Http/Controllers) ]              │
│     ├── PokjaController (Skor Risiko, Checklist 6 Kategori) │
│     ├── PemantauanController (Anomali, Perubahan, Sanggahan)│
│     ├── PetaController (GeoJSON & Koordinat Paket)          │
│     ├── PesanPaketController (Ruang Chat LPSE <-> Pokja)    │
│     ├── PokjaDashboardController (Dasbor Taktis, Sanggahan, Jadwal, Kendali) │
│     └── NotifikasiController (API Unread & Push Subscribe)  │
│            │                                                │
│            ▼                                                │
│   [ Services Layer (app/Services) ]                         │
│     └── NotificationService (DB Log + Minishlink WebPush)   │
│            │                                                │
│            ▼                                                │
│   [ Domain / Business Logic (Eloquent Models) ]             │
│     ├── Pokja (Kalkulator Risiko, Agregasi Beban Kerja)     │
│     ├── PaketPengadaan (Progress, Tahapan, Scoring)         │
│     ├── Notifikasi (Riwayat In-App, Unread Scope)           │
│     ├── PushSubscription (Endpoint, P256DH, Auth Token)     │
│     └── AuditLog (Pencatatan Perubahan Sistem Otomatis)     │
│            │                                                │
│            ▼                                                │
│   [ Data Access Layer (MySQL 8 / MariaDB 10.4+) ]           │
└─────────────────────────────────────────────────────────────┘
```

## 2. Aliran Data Utama

### 1. Pemantauan Kinerja & Skor Risiko:
- Data paket pengadaan dan tahapan dibaca oleh Model `Pokja`.
- Menghitung agregat rasio beban, riwayat perubahan jadwal tanpa BA, pelanggaran SLA sanggahan (3 hari kerja), dan status tender gagal.
- Menghasilkan status risiko: `RENDAH`, `SEDANG`, `TINGGI`, atau `KRITIS`.

### 2. Audit Kepatuhan 6 Kategori:
- Evaluasi form audit polaritas terbalik (jawaban "Ya" = Terjadi Pelanggaran/Temuan).
- Rekap persentase temuan disajikan secara real-time pada kartu Pokja.

### 3. Perpesanan Progres Paket:
- Kolaborasi Kepala LPSE dan Tim Pokja terikat per ID paket pengadaan (`pesan_pakets`).
- HTTP Polling berkala (8 detik) untuk pembaruan instan percakapan.

### 4. Sistem Push Notifikasi & Alert Real-time:
- Setiap kejadian kritis (Pesan Masuk, Peringatan EWS Keterlambatan, Sanggahan Baru, atau Update Progres Fisik) memicu `NotificationService`.
- Notifikasi disimpan ke database tabel `notifikasis` untuk riwayat in-app dan counter badge lonceng navbar.
- Pada saat bersamaan, `NotificationService` memuat endpoint terdaftar di `push_subscriptions`, menandatangani token VAPID JWT, dan menembakkan pesan push ke gateway browser (W3C Push API).
- *Service Worker* (`sw.js`) di latar belakang menangkap event `push` dan menampilkan notifikasi pop-up sistem operasi di layar desktop/ponsel pengguna.

### 5. Portal Taktis Pokja & Kepatuhan SLA:
- Tim Pokja memantau beban pekerjaan dengan filter chip instan dan triage-preserving sort.
- Modul Sanggahan mengawasi countdown SLA 3 hari kerja dan memproses jawaban resmi yang langsung menyelesaikan alert keterlambatan di tabel `alert_anomalis`.
- Modul Jadwal mencatat addendum dan memvalidasi Berita Acara (BA) untuk mencegah risiko anomali pengadaan.
- Modul Lembar Kendali menghasilkan dokumen pertanggungjawaban fisik siap cetak (*print-ready*) dengan format dinas Pemkab Banjarnegara.

