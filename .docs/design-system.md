# Design System & Visual DNA — SIMONPOKJA Admin Interface

Dokumentasi ini adalah **Single Source of Truth (SSOT)** visual, geometri, tata letak, dan komponen antarmuka admin SIMONPOKJA (Sistem Informasi Monitoring Kinerja Pokja LPSE Kabupaten Banjarnegara).

---

## 1. Visual DNA & Filosofi Desain

- **Arketipe / Karakter UI:** Clean Modern Government Dashboard.
- **Kesan Visual:** Profesional, akuntabel, tegas, terstruktur, berwibawa, dan ramah aksesibilitas (WCAG 2.1 AA compliant).
- **Pendekatan Aset:** Native CSS + Bootstrap 5.3.3 CDN + Bootstrap Icons 1.11.3 + Chart.js 4.4.3 + Leaflet.js (tanpa dependensi bundler Node.js).
- **Aksesibilitas Kontras:** Seluruh teks sekunder dan muted diwajibkan memenuhi rasio kontras minimal 4.5:1 terhadap latar belakang (menggunakan `#5a6268`).

---

## 2. Token Warna & Palet Semantik

### A. Palet Utama (Core Tokens)
| Token CSS / Variabel | Kode HEX / Nilai | Peruntukan / Penggunaan |
|---|---|---|
| `--sidebar-bg` | `#1e293b` (Slate 800) | Latar belakang sidebar navigasi utama |
| `--sidebar-active` | `#2563eb` (Blue 600) | Item navigasi aktif, hover topbar |
| `--sidebar-width` | `260px` | Lebar standar desktop sidebar |
| `--topbar-height` | `60px` | Tinggi topbar header aplikasi |
| `--primary-color` | `#2563eb` (Blue 600) | Warna aksen utama tombol, tautan, dan indikator |
| `--vibe-background` | `#f1f5f9` (Slate 100) | Latar belakang kanvas aplikasi (`body`) |
| `--vibe-surface` | `#ffffff` | Latar belakang kartu (`.card`), modal, dan dropdown |
| `--vibe-text-main` | `#1e293b` (Slate 800) | Teks judul, label utama, dan heading |
| `--vibe-text-sub` | `#5a6268` (Slate Muted) | Teks penjelas, deskripsi, audit kontras WCAG AA |
| `--vibe-border` | `#e2e8f0` (Slate 200) | Garis pemisah tabel, border topbar, garis kartu |
| `--vibe-accent-2` | `#7c3aed` (Purple 600) | Aksen khusus (`badge.purple` untuk penanda unik) |

### B. Palet Indikator Status & Risiko (Risk Level Matrix)
| Level Risiko | Kelas Bootstrap | Hex Utama | Hex Background Subtle | Icon Default |
|---|---|---|---|---|
| **Rendah / Normal** | `.text-bg-success` | `#16a34a` | `#dcfce7` (`.bg-success-subtle`) | `bi-check-circle`, `bi-shield-check` |
| **Sedang / Ditinjau** | `.text-bg-warning` | `#d97706` | `#fef3c7` (`.bg-warning-subtle`) | `bi-exclamation-triangle`, `bi-stopwatch` |
| **Kritis / Tinggi** | `.text-bg-danger` | `#dc2626` | `#fee2e2` (`.bg-danger-subtle`) | `bi-exclamation-octagon-fill`, `bi-x-octagon` |
| **Informasi** | `.text-bg-info` | `#0284c7` | `#e0f2fe` (`.bg-info-subtle`) | `bi-info-circle`, `bi-chat-dots` |
| **Tertunda / Menunggu**| `.text-bg-secondary` | `#64748b` | `#f1f5f9` (`.bg-light`) | `bi-clock-history` |

### C. Gradien Khusus
- **Sidebar Brand Icon:** `linear-gradient(135deg, #2563eb, #1d4ed8)`
- **Halaman Login Backdrop:** `linear-gradient(135deg, #1e293b 0%, #2563eb 100%)`

---

## 3. Tipografi & Hirarki Teks

Menggunakan Google Fonts **Inter** untuk teks umum dan **JetBrains Mono** untuk angka/nominal:
`font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;`

| Elemen | Ukuran Font | Weight | Transform / Letter Spacing | Warna |
|---|---|---|---|---|
| **Base Body Text** | `14px` (`0.875rem`) | 400 - 500 | Normal (`line-height: 1.5`) | `#0f172a` |
| **Page Title (H1/H4)** | `1.2rem - 1.35rem` (`18px - 20px`) | 700 (Bold) | Normal / Uppercase (`0.5px`) | `#0f172a` |
| **Section / Panel Header (H5/Gov-Title)**| `13px - 14px` | 700 (Bold) | Uppercase (`letter-spacing: 0.5px`) | `#0f172a` |
| **Executive Metric Number** | `24px` | 700 (Bold) | Normal (`line-height: 1.15`) | `#0f172a` / `#16a34a` |
| **Metric Caption / Category** | `11.5px - 12px` | 600 (Semibold)| Uppercase (`letter-spacing: 0.5px`) | `#475569` |
| **Table Header (th)**| `11.5px` | 600 (Semibold)| Uppercase (`letter-spacing: 0.5px`) | `#334155` |
| **Table Body (td)** | `13.5px - 14px` | 400 - 600 | Normal (`padding: 10px 14px`) | `#0f172a` |
| **Monospace Values (Rp / Kode)**| `13.5px` | 500 - 600 | JetBrains Mono | `#0f172a` / `#1d4ed8` |
| **Form Controls & Inputs** | `13.5px` (sm: `12.5px`) | 400 - 500 | Normal | `#0f172a` |
| **Buttons (`.btn`)** | `13px` (sm: `12.5px`) | 500 (Medium) | Normal | `#ffffff` / `#475569` |
| **Small / Metadata (`.small`)** | `12.5px` | 400 - 500 | Normal | `#5a6268` (`.text-secondary`) |
| **Sidebar Menu Links** | `13.5px` | 500 - 600 | Normal (`icon: 16px`) | `#cbd5e1` / `#ffffff` |


---

## 4. Geometri, Radius & Elevasi (Rigid Data-Dense System)

### A. Border Radius (Crisp 4px Standard)
- **Panel & Kartu Utama (`.gov-panel`, `.card`):** `4px` (`var(--radius-krisp)`) kaku, dengan border eksplisit `1px solid #cbd5e1`.
- **Tombol Standar (`.btn`, `.btn-gov`):** `3px` (`var(--radius-input)`)
- **Input Form (`.form-control`, `.form-select`):** `3px` (`var(--radius-input)`)
- **Executive Metrics Strip (`.metrics-strip`):** `4px` terpadu dengan sekat vertikal `1px solid #cbd5e1`.
- **Indicator Dot (`.status-dot`):** `50%` lingkaran mikro 6px.
- **DILARANG:** Bentuk rounded membal 10–16px, kapsul pill pada teks biasa, atau badge mengambang.

### B. Elevasi & Bayangan (Zero Floating Shadow)
- **Card & Panel Default:** `box-shadow: none;` (flat and anchored to border `1px solid #cbd5e1`).
- **Dropdown Menu:** `box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #cbd5e1;`
- **Mobile Sidebar Drawer:** `box-shadow: 0 0 40px rgba(0, 0, 0, 0.3);`
- **DILARANG:** Glow neon, floating capsule shadow (`box-shadow: 0 4px 14px rgba(37,99,235,0.35)`), atau decorative border shadow pada logo/kontainer.

---

## 5. Sistem Spacing Semantik (Grid 8pt, Padding, Margin, Gap)

Seluruh jarak tata letak menggunakan skala kelipatan **8pt Grid** (dengan toleransi 4px untuk ruang mikro).

### A. Skala Spacing Baku
| Skala Token | Ukuran Piksel | Nilai REM | Penggunaan Baku |
|---|---|---|---|
| **Micro (1)** | `4px` | `0.25rem` | Jarak mikro label-ke-input (`mb-1`), offset border, gap badge |
| **Small (2)** | `8px` | `0.5rem` | Jarak ikon ke teks (`me-1.5` / `gap-2`), padding badge |
| **Regular (3)** | `16px` | `1.0rem` | Jarak antar kartu kecil (`g-3`), margin form group (`mb-3`), padding card body ringkas |
| **Medium (4)** | `24px` | `1.5rem` | Card body standar, padding main content desktop, margin antar-seksi (`mb-4`, `g-4`) |
| **Large (5)** | `32px` | `2.0rem` | Jarak pemisah modul besar, margin tabel ke footer |
| **X-Large (6)**| `48px` | `3.0rem` | Empty state container padding, batas bawah laporan cetak |

### B. Standar Padding & Margin per Komponen
1. **Card Container:**
   - Standar body: `padding: 20px 24px;`
   - Compact body: `padding: 14px 16px;`
   - Header & Footer kartu: `padding: 12px 20px; border-bottom: 1px solid #e2e8f0;`
2. **Tabel Data:**
   - Header sel (`th`): `padding: 10px 16px;`
   - Isi sel (`td`): `padding: 12px 16px; vertical-align: middle;`
   - Compact table (`.table-sm td, .table-sm th`): `padding: 6px 10px;`
3. **Form Controls:**
   - Input biasa (`.form-control`, `.form-select`): `padding: 7px 12px; font-size: 0.875rem;`
   - Input kecil (`.form-control-sm`, `.form-select-sm`): `padding: 4px 9px; font-size: 0.78rem;`
   - Form label: `margin-bottom: 4px; font-weight: 600; font-size: 0.82rem;`
4. **Modal Dialog:**
   - Header: `padding: 16px 24px;`
   - Body: `padding: 24px;`
   - Footer: `padding: 14px 24px;`
5. **Dropdown Menu:**
   - Container: `padding: 6px 0; margin-top: 4px;`
   - Menu Item: `padding: 7px 16px; font-size: 0.84rem;`

---

## 6. Sistem Tombol (Button Hierarchy & States)

Tombol didesain dengan hierarki yang jelas untuk mencegah kerancuan tindakan pengguna.

### A. Varian Tombol & Penggunaan
| Varian Kelas | Background / Border | Warna Teks | Peruntukan Aksi |
|---|---|---|---|
| `.btn-primary` | `#2563eb` / `#2563eb` | `#ffffff` | Aksi utama halaman, Simpan, Tambah, Submit formulir |
| `.btn-secondary` | `#64748b` / `#64748b` | `#ffffff` | Aksi pendukung, Batal, Kembali |
| `.btn-outline-secondary` | `transparent` / `#cbd5e1` | `#475569` | Filter, Reset, Navigasi sekunder non-destruktif |
| `.btn-success` | `#16a34a` / `#16a34a` | `#ffffff` | Verifikasi, Selesai, Setujui rekomendasi |
| `.btn-danger` | `#dc2626` / `#dc2626` | `#ffffff` | Hapus, Batalkan tender, Tindakan destruktif |
| `.btn-warning` | `#d97706` / `#d97706` | `#1e293b` | Peringatan, Tindak lanjuti anomali |
| `.btn-outline-primary` | `transparent` / `#2563eb` | `#2563eb` | Tombol detail, ekspor data, aksi baris tabel |

### B. Ukuran & Anatomi Tombol
- **Tombol Kecil (`.btn-sm`):** Digunakan pada baris tabel, aksi kartu, toolbar peta.
  - Padding: `4px 10px; font-size: 0.78rem; border-radius: 6px;`
- **Tombol Standar (`.btn`):** Digunakan pada form umum dan aksi modal.
  - Padding: `7px 16px; font-size: 0.875rem; border-radius: 8px; font-weight: 500;`
- **Tombol Besar (`.btn-lg`):** Digunakan pada halaman login dan landing CTA.
  - Padding: `10px 22px; font-size: 1rem; border-radius: 10px; font-weight: 600;`

### C. State Interaksi & Focus Ring
- **Hover:** Kontras sedikit dipergelap (`filter: brightness(0.92)` atau shade spesifik, misal `#2563eb` → `#1d4ed8`).
- **Active / Pressed:** `transform: translateY(1px);`
- **Focus Visible:** Wajib memiliki focus ring yang jelas untuk keyboard navigation:
  `box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.25); outline: 0;`
- **Icon dalam Tombol:** Jarak ikon ke teks wajib `me-1.5` atau `gap: 6px` dengan posisi vertikal sejajar.

---

## 7. Sistem Ikon Resmi (Bootstrap Icons Standard)

Aplikasi SIMONPOKJA menerapkan **Aturan Satu Keluarga Ikon** untuk konsistensi visual dan profesionalitas instansi.

### A. Ikon Library Tunggal
- Seluruh ikon wajib menggunakan **Bootstrap Icons 1.11.3** (`bi bi-*`).
- **Dilarang mencampur:** FontAwesome, Feather, Dashicons, SVG kustom mentah, atau library pihak ketiga lainnya.

### B. Hierarki & Ukuran Ikon
| Kategori | Kelas / Ukuran | Contoh Penggunaan |
|---|---|---|
| **Micro Icon** | `0.75rem` (`small` / inline) | Pin lokasi di tabel, chevron dropdown, tanda centang verifikasi |
| **Body / Button** | `0.875rem - 1rem` | Ikon di tombol aksi, menu navigasi sidebar, input search |
| **Navigation Head**| `1.15rem - 1.25rem` | Ikon modul utama di sidebar, header modal |
| **KPI / Metric** | `1.5rem - 1.75rem` | Ikon di dalam container stat-card (`48px x 48px`) |
| **Empty State** | `2.5rem - 3rem` | Indikator visual ketika data kosong / tidak ditemukan |

### C. Penjajaran Vertikal (*Vertical Alignment*)
Ikon font secara inheren dapat bergeser 1-2 piksel dari baseline teks. Aturan penjajaran:
- Gunakan flexbox inline: `<span class="d-inline-flex align-items-center gap-1.5"><i class="bi bi-*"></i> Teks</span>`
- Atau berikan offset baseline standar: `.bi { vertical-align: -1px; }`

---

## 8. Katalog Komponen Spesifik SIMONPOKJA

### A. Stat Card (Widget KPI Metrik Utama)
- **Struktur:** Kartu dengan aksen garis tebal di sisi kiri (`.card.stat-card.h-100.border-start.border-4`).
- **Aksen Warna Sisi:**
  - Biru: `.border-primary`
  - Hijau: `.border-success`
  - Kuning: `.border-warning`
  - Merah: `.border-danger`
- **Icon Container:** Ukuran `48px x 48px`, `border-radius: 12px`, warna ikon sesuai status dengan latar belakang lembut (`bg-*-subtle`).

### B. Kartu Pokja (Workload & Risk Card)
- **Struktur:** `.card.h-100.pokja-card.position-relative.overflow-hidden`
- **Badge Risiko Mengambang:** Posisi `.position-absolute.top-0.end-0.mt-2.me-2` dengan warna semantik risiko.
- **Indikator Overload:** Ikon peringatan segitiga merah `<i class="bi bi-exclamation-triangle text-danger">` bila rasio paket melebihi kapasitas ideal pokja.
- **Bar Skor Risiko:** Progress bar mikro `height: 6px; border-radius: 999px` dengan nilai skor 0-100.

### C. Tabel Responsif Transformatif (`.table-card`)
- Memecahkan masalah keterbacaan tabel data pengadaan pada layar ponsel:
  - Pada layar `< 768px`, tag `<thead>` disembunyikan.
  - Setiap baris `<tr>` berubah menjadi kartu individual dengan border dan bayangan halus.
  - Setiap sel `<td>` ditampilkan sebagai baris flexbox: atribut `data-label` di sebelah kiri dan data aktual di sebelah kanan.

### D. Panel Diskusi Per Pekerjaan (`pesan._chat`)
- **Ruang Percakapan:** Kontainer pesan dengan batas tinggi `max-height: 360px; min-height: 160px` dan scroll otomatis ke bawah saat pesan baru tiba.
- **Bubble Pesan Milik Sendiri:** Rata kanan (`.justify-content-end`), latar biru `.bg-primary.text-white`, sudut membulat `.rounded-3.shadow-sm`, status baca `bi-check2` (terkirim) atau `bi-check2-all` (dibaca).
- **Bubble Lawan Bicara:** Rata kiri, latar abu-abu terang `.bg-light.border`, identitas nama pengirim & badge peran.
- **Form Pengiriman:** Textarea elastis dengan tombol kirim kompak dan dukungan pintasan keyboard `Enter` (kirim) serta `Shift+Enter` (baris baru).

### E. Checklist Audit 6 Kategori (Instrumen Pengawasan)
- **Polaritas Terbalik Konsisten:** Form penilaian di mana jawaban **"Ya"** menandakan adanya temuan masalah/anomali.
- **Indikator Persentase Temuan:**
  - Hijau (`text-bg-success`): Jika temuan "Ya" `< 50%`.
  - Merah (`text-bg-danger`): Jika temuan "Ya" `≥ 50%`.
- **Form Interaktif Auto-Save:** Dropdown jawaban (`N/A`, `Ya`, `Tidak`) selebar `92px` yang otomatis tersimpan saat diubah (`onchange="this.form.submit()"`).

### F. Peta Sebaran Spasial — Unified Collapsible Side Panel (Leaflet GIS)
- **Arsitektur Split-Screen:** Menggantikan floating buttons yang bertumpuk dengan panel samping terintegrasi (`.peta-side-panel`, lebar `380px` desktop) berdampingan dengan kanvas peta (`.peta-canvas-wrap`).
- **Dynamic Floating Capsule (Dual-State Toggle):**
  - **Saat Panel Terlipat (*Collapsed*):** Tombol melompat ke pojok kiri atas (`top: 14px; left: 14px;`) bertransformasi menjadi kapsul melayang (*floating capsule*) biru primer (`#2563eb`) mencolok dengan teks **"Daftar Pekerjaan"**, ikon `bi-list-ul`, badge angka counter paket real-time (misal: `15`), dan chevron `bi-chevron-right`. Desain ini menjamin pengguna baru langsung menyadari keberadaan panel kontrol pekerjaan.
  - **Saat Panel Terbuka (*Expanded*):** Tombol meramping ke batas pembatas panel (`left: 380px`, `width: 24px`) dengan chevron `bi-chevron-left` untuk melipat kembali tanpa mengaburkan area peta.
- **Segmented Tabs:** Menggabungkan *Daftar Pekerjaan* dan *Filter & Pencarian* dalam 1 panel terstruktur (`nav-pills`).
- **Kartu Pekerjaan Terstruktur (`.job-item-card`):**
  - Garis aksen status risiko di sisi kiri (`border-start border-4 border-risiko-{kritis|sedang|rendah}`).
  - Menampilkan judul pekerjaan tanpa pemotongan kaku, nominal pagu ringkas (`formatRupiah`), singkatan OPD, lokasi, dan badge progres.
- **Interaksi Dua Arah (Two-Way Sync):**
  - Mengarahkan mouse (*hover*) ke kartu daftar memicu efek pembesaran (*pulse/scale*) dan *tooltip* pada marker peta.
  - Menggeser (*pan*) atau memperbesar (*zoom*) peta secara dinamis memfilter daftar bila toggle *Hanya di layar peta* aktif (`sync-bounds`).
- **Legenda Kompak:** Diposisikan sebagai floating pill elegan di pojok kanan-bawah dengan tombol dropdown lipat (`.peta-legend-pill`).

### G. Toast Notifikasi Interaktif (`.toast-gov` — Swiss Government Crisp)
- **Arsitektur Visual Anti-AI-Slop:**
  - Sudut kaku `4px` (`var(--radius-krisp)`), border tipis `1px solid var(--border-panel)` berlatar putih solid `#ffffff`.
  - **DILARANG:** Bentuk rounded membal 12–16px, glow neon, gradient ungu/pink, atau bayangan mengambang berlebih (*floating bubble*).
  - Garis aksen semantik di sisi kiri tebal `4px`:
    - Biru (`.toast-info`, `.toast-primary`): Pesan baru & pembaruan sistem (`#1d4ed8`).
    - Merah (`.toast-critical`, `.toast-danger`): Peringatan EWS kritis & pelanggaran SLA (`#dc2626`).
    - Kuning (`.toast-warning`): Sanggahan rekanan & anomali jadwal tanpa BA (`#d97706`).
    - Hijau (`.toast-success`): Konfirmasi aksi berhasil disimpan (`#16a34a`).
- **Standar Tipografi & Copywriting:**
  - **Kategori Header:** `11px`, `font-weight: 700`, uppercase (`letter-spacing: 0.5px`), warna `#475569`. Menggunakan label formal tegas: `PESAN BARU`, `PERINGATAN EWS`, `SANGGAHAN REKANAN`, `UPDATE PROGRES`, `STATUS BERHASIL`.
  - **DILARANG:** Kata-kata AI-slop cheesy ("Hooray!", "Awesome update!", "Hey there!", "Action required!").
  - **Judul:** `13px`, `font-weight: 600`, warna `#0f172a`.
  - **Pesan:** `12px`, warna `#475569`, maksimal 2 baris ringkas (`-webkit-line-clamp: 2`).
- **Penempatan & Perilaku:**
  - Posisi tetap di sudut kanan bawah layar (`position-fixed bottom-0 end-0 p-3`, `z-index: 1090`).
  - Animasi slide-in halus dari kanan (`0.2s cubic-bezier(0.16, 1, 0.3, 1)`), auto-dismiss `4.5 detik`, dan klik langsung menavigasi ke URL target paket.

---

## 9. Aturan Anti-AI-SLOP & Larangan Desain Komprehensif

Untuk menjaga standar profesionalitas sistem pemerintahan resmi, seluruh 18 larangan Anti-AI-SLOP berikut diberlakukan secara mutlak:

### 1. ❌ Dilarang Simbol Mentah & Emotikon Unicode (Anti-Emoji Slop)
- **DILARANG KERAS:** Memasukkan karakter emoji/simbol mentah seperti `⚠️`, `💰`, `📈`, `✓`, `→`, `❌`, `🔥`, `🚨`, `📅`, `📍` ke dalam teks antarmuka, judul kartu, label form, ataupun tombol.
- **ALASAN:** Emotikon unicode merusak tampilan resmi pemerintahan, inkonsisten antar-perangkat OS, dan terkesan murahan.
- **SOLUSI WAJIB:** Gunakan kelas ikon resmi Bootstrap Icons (`bi-exclamation-octagon`, `bi-cash-stack`, `bi-graph-up-arrow`, `bi-check-circle`, `bi-arrow-right`).

### 2. ❌ Dilarang Ikon SVG Mentah (Hand-Rolled Inline SVG)
- **DILARANG:** Menuliskan tag `<svg>` mentah sebaris tanpa standarisasi ukuran, stroke, dan viewBox di file Blade/HTML.
- **SOLUSI WAJIB:** Gunakan tag `<i>` dengan kelas ikon Bootstrap Icons terstandarisasi (`<i class="bi bi-*"></i>`).

### 3. ❌ Dilarang Logo Container AI-Slop (Wadah Logo Hiasan)
- **DILARANG KERAS:** Membungkus logo resmi instansi (Pemkab Banjarnegara / LPSE) dengan kontainer dekoratif berbentuk lingkaran/kotak bergradien neon, border tebal, outline mencolok, atau drop-shadow berlebihan.
- **SOLUSI WAJIB:** Logo instansi pemerintah WAJIB ditampilkan bersih (*as-is*), transparan, tanpa border/outline/shadow hiasan yang merusak integritas lambang daerah.

### 4. ❌ Dilarang Capsule/Eyebrow Badge Berlebihan (Capsule Ban)
- **DILARANG:** Memasang badge pil (*capsule/eyebrow*) kecil mengambang di atas heading form login/auth (misal: pill *"✨ Solusi Pengadaan Terkini"*).
- **BATASAN:** Maksimal 1 label eyebrow per 3 seksi di halaman konten umum. Capsule badge HANYA boleh dipakai untuk status semantik ringkas, counter angka numerik, atau floating handle fungsional.

### 5. ❌ Dilarang Formasi 3 Kartu Identik (3-Equal Cards Default Monoculture)
- **DILARANG:** Menyusun 3 kartu vertikal identik yang datar dan seragam di halaman depan/dasbor tanpa diferensiasi hierarki.
- **SOLUSI WAJIB:** Terapkan variasi visual seperti layout asimetris, kartu KPI dengan aksen strip risiko berbeda, atau Bento matrix.

### 6. ❌ Dilarang Tata Letak Kotak-dalam-Kotak Datar (Flat Box-in-a-Box Monoculture)
- **DILARANG:** Menumpuk kontainer kotak persegi bertingkat tanpa kedalaman siluet, bayangan bertingkat, atau pemisah visual yang jelas.
- **SOLUSI WAJIB:** Berikan kontras latar belakang kanvas (`#f1f5f9`) dengan permukaan kartu putih bersih (`#ffffff`), border halus (`#e2e8f0`), dan elevasi lembut (`0 1px 3px rgba(0,0,0,0.06)`).

### 7. ❌ Dilarang Warna AI Generik Tanpa Token
- **DILARANG:** Menggunakan warna ungu AI generik `#6C63FF`, hijau datar `#4CAF50`, atau biru mentah `#2196F3`.
- **SOLUSI WAJIB:** Gunakan palet resmi SIMONPOKJA: Biru Utama `#2563eb`, Hijau `#16a34a`, Amber/Kuning `#d97706`, Merah `#dc2626`.

### 8. ❌ Dilarang Hardcode Nilai Warna (Hex/RGB/HSL Berserakan)
- **DILARANG:** Menuliskan kode warna heksadesimal langsung di inline style komponen baru tanpa merujuk ke token CSS `--vibe-*` atau kelas semantik Bootstrap.

### 9. ❌ Dilarang Spacing Acak Tanpa Grid 8pt
- **DILARANG:** Menggunakan margin/padding arbitrer ganjil seperti `13px`, `19px`, `23px`. Wajib mematuhi skala kelipatan 8pt (4px, 8px, 16px, 24px, 32px).

### 10. ❌ Dilarang Tipografi 'Inter' Tunggal Tanpa Hierarki
- **DILARANG:** Menggunakan 1 font family Inter generik tanpa variasi bobot tajam. Gunakan Segoe UI / System-UI dengan pembedaan kontras tegas antara Heading Bold (700) dan Body (400-500).

### 11. ❌ Dilarang Hardcode Radius Arbitrer
- **DILARANG:** Memakai `border-radius: 8px` acak di semua elemen tanpa hierarki. Ikuti hierarki: Kartu (`12px`), Form/Tombol (`8px`), Tombol mini (`6px`), Pill/Badge (`999px`).

### 12. ❌ Dilarang Box-Shadow Generik Murahan
- **DILARANG:** `box-shadow: 0 2px 4px rgba(0,0,0,0.1)` kusam. Gunakan bayangan halus dengan multi-layer opacity sesuai Section 4.B.

### 13. ❌ Dilarang Transisi Global Lambat
- **DILARANG:** `transition: all 0.3s ease` yang menyebabkan lag visual. Gunakan transisi spesifik pada properti yang berubah (`transition: background-color 0.15s ease, border-color 0.15s ease`).

### 14. ❌ Dilarang Hero Height Kaku (`h-screen`)
- **DILARANG:** `height: 100vh` pada container yang memiliki konten dinamis. Gunakan `min-height: calc(100vh - 60px)` atau `min-height: 100dvh`.

### 15. ❌ Dilarang Mencampur Library Ikon
- **DILARANG:** Menggabungkan ikon dari >1 pustaka (misal Bootstrap Icons dicampur FontAwesome). Wajib ONE ICON FAMILY.

### 16. ❌ Dilarang Teks Buzzword Hampa (Copy Anti-Slop)
- **DILARANG:** Menggunakan kata-kata klise marketing AI seperti *"Unlock"*, *"Empower"*, *"Revolutionize"*, *"Seamless"*, *"Cutting-edge"*, *"Next-gen"*, *"Game-changing"*, *"Elevate"*. Gunakan bahasa formal birokrasi pemerintahan: *"Monitoring"*, *"Pengawasan"*, *"Penyedia"*, *"Pagu Anggaran"*, *"Tender"*, *"SLA Sanggahan"*.

### 17. ❌ Dilarang Angka Presisi Palsu (Fake Precise Metrics)
- **DILARANG:** Mengarang angka persentase fiktif seperti *"98.4% efisiensi"* tanpa sumber data SQL aktual dari database.

### 18. ❌ Dilarang Menghasilkan Output Visual Tanpa Verifikasi Visual Gate
- **DILARANG:** Menulis perubahan CSS/UI tanpa mencantumkan log pengecekan Visual Gate:
  `[Visual Gate] Perubahan: ... — token: ... — sesuai Visual DNA: ✅`
