<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Pokja;
use App\Models\Opd;
use App\Models\Penyedia;
use App\Models\PaketPengadaan;
use App\Models\PerubahanJadwal;
use App\Models\Sanggahan;
use App\Models\AlertAnomali;
use App\Models\AuditChecklist;
use Carbon\Carbon;

/**
 * SimulasiKinerjaSeeder
 * Memperkaya data simulasi monitoring kinerja untuk SETIAP Pokja,
 * dengan fokus Pokja II (Jasa Konsultansi) sebagai kasus KRITIS:
 *  - Paket baru sesuai bidang masing-masing Pokja (tahun anggaran BERJALAN)
 *  - Pola pengunduran jadwal realistis (berulang >3x, tanpa BA, akhir jam kerja 16:50)
 *  - Sanggahan termasuk yang melewati SLA 3 hari
 *  - Alert anomali otomatis sesuai pola
 *  - Checklist audit 6 kategori terisi penuh untuk Pokja II
 * Seeder ini idempotent: aman dijalankan berulang.
 */
class SimulasiKinerjaSeeder extends Seeder
{
    public function run(): void
    {
        // Paket simulasi SELALU ditempatkan pada tahun anggaran berjalan agar
        // tampil di halaman monitoring yang default-nya memfilter tahun sekarang.
        $TA = (int) date('Y');

        $this->bersihkanDuplikat($TA);

        $opd = [];
        foreach (Opd::all() as $o) {
            $opd[$o->singkatan] = $o->id;
        }
        $pen = [];
        foreach (Penyedia::all() as $p) {
            $pen[$p->nama] = $p->id;
        }

        $pokja = [];
        foreach (Pokja::all() as $pk) {
            $pokja[$pk->nama] = $pk->id;
        }

        // =========================================================
        // 1. PAKET BARU PER POKJA (sesuai bidang masing-masing)
        // =========================================================
        $now = Carbon::now();
        $paketBaru = [
            // [kode, nama, opd, pokja, penyedia|null, jenis, metode, pagu, status, tahap_tender, gagal, progress]
            // --- Pokja II: Jasa Konsultansi (fokus simulasi halaman /kinerja-pokja/2) ---
            ['SIM-JK-001', 'Jasa Konsultan Supervisi Pembangunan Pasar Apu', 'DPUPR', 'Pokja II', 'PT Konsultan Cipta Rencana', 'jasa_konsultansi', 'seleksi', 1_200_000_000, 'kontrak', 'evaluasi', false, 25],
            ['SIM-JK-002', 'Jasa Konsultan DED Jaringan Irigasi Wanayasa', 'DKPP', 'Pokja II', null, 'jasa_konsultansi', 'seleksi', 890_000_000, 'pemilihan', 'evaluasi', false, 0],
            ['SIM-JK-003', 'Jasa Konsultan Perencanaan Terminal Tipe B Pekasiran', 'Dishub', 'Pokja II', null, 'jasa_konsultansi', 'seleksi', 1_500_000_000, 'pemilihan', 'pengumuman', true, 0],
            ['SIM-JK-004', 'Jasa Konsultan Manajemen Konstruksi RSUD Widas', 'Dinkes', 'Pokja II', 'PT Konsultan Cipta Rencana', 'jasa_konsultansi', 'seleksi', 950_000_000, 'kontrak', 'sanggah', false, 20],
            ['SIM-JK-005', 'Jasa Konsultan Kajian Sosial RTRW Kabupaten', 'Bappeda', 'Pokja II', null, 'jasa_konsultansi', 'seleksi', 620_000_000, 'persiapan', 'persiapan', false, 5],
            ['SIM-JK-006', 'Jasa Konsultan Audit Energi Gedung Kantor Pemda', 'BKD', 'Pokja II', 'PT Mitra Solusi Informatika', 'jasa_konsultansi', 'seleksi', 480_000_000, 'pemilihan', 'pengumuman', true, 0],
            ['SIM-JK-007', 'Jasa Konsultan Supervisi Drainase Pekuncen Tahap II', 'DPUPR', 'Pokja II', 'PT Konsultan Cipta Rencana', 'jasa_konsultansi', 'seleksi', 780_000_000, 'pelaksanaan', 'penetapan', false, 60],
            ['SIM-JK-008', 'Jasa Konsultan Penyusunan DED Embung Baturaden', 'DPUPR', 'Pokja II', null, 'jasa_konsultansi', 'seleksi', 1_100_000_000, 'persiapan', 'persiapan', false, 0],
            // --- Pokja III: Pekerjaan Konstruksi ---
            ['SIM-KON-001', 'Peningkatan Jalan Kabupaten Karangkobar - Wanayasa', 'DPUPR', 'Pokja III', 'PT Adhi Karya Persada', 'konstruksi', 'tender', 7_400_000_000, 'pelaksanaan', 'evaluasi', false, 55],
            ['SIM-KON-002', 'Pembangunan Saluran Drainase Mandiraja Kota', 'DPUPR', 'Pokja III', 'CV Sinar Teknik', 'konstruksi', 'pengadaan_langsung', 1_950_000_000, 'kontrak', 'penetapan', false, 30],
            ['SIM-KON-003', 'Rehabilitasi Bangunan SDN 2 Pagentan', 'Disdikbud', 'Pokja III', 'CV Karya Mandiri Banjarnegara', 'konstruksi', 'pengadaan_langsung', 1_150_000_000, 'persiapan', 'persiapan', false, 5],
            // --- Pokja IV: Jasa Lainnya ---
            ['SIM-JL-001', 'Jasa Penyediaan Makanan Minuman Rapat Koordinasi Setda', 'Setda', 'Pokja IV', 'Koperasi Sejahtera Bersama', 'jasa_lainnya', 'pengadaan_langsung', 320_000_000, 'pelaksanaan', 'penetapan', false, 70],
            ['SIM-JL-002', 'Jasa Pemeliharaan Taman Kota Banjarnegara', 'DLH', 'Pokja IV', 'UD Tani Makmur', 'jasa_lainnya', 'pengadaan_langsung', 210_000_000, 'kontrak', 'penetapan', false, 25],
            // --- Pokja V: Swakelola & Terintegrasi ---
            ['SIM-SWA-001', 'Swakelola Peningkatan Lapangan Olahraga Sigaluh', 'Dispora', 'Pokja V', null, 'konstruksi', 'swakelola', 980_000_000, 'pelaksanaan', 'penetapan', false, 40],
            ['SIM-SWA-002', 'Swakelola Pengelolaan Parkir Wisata Curug Sidandang', 'Dispora', 'Pokja V', null, 'jasa_lainnya', 'swakelola', 350_000_000, 'kontrak', 'penetapan', false, 35],
        ];

        foreach ($paketBaru as [$kode, $nama, $o, $pk, $penyedia, $jenis, $metode, $pagu, $status, $tahap, $gagal, $progress]) {
            // Seri "SIM-JK-001" → kode lengkap "SIM-<TA>-JK-001" (hindari prefix ganda)
            $kodeLengkap = 'SIM-' . $TA . '-' . preg_replace('/^SIM-/', '', $kode);
            PaketPengadaan::updateOrCreate(
                ['kode_paket' => $kodeLengkap],
                [
                    'nama_paket' => $nama,
                    'opd_id' => $opd[$o] ?? null,
                    'pokja_id' => $pokja[$pk] ?? null,
                    'penyedia_id' => $penyedia ? ($pen[$penyedia] ?? null) : null,
                    'jenis' => $jenis,
                    'metode' => $metode,
                    'sumber_dana' => 'apbd',
                    'pagu' => $pagu,
                    'hps' => (int) round($pagu * 0.97),
                    'nilai_kontrak' => in_array($status, ['kontrak', 'pelaksanaan']) ? (int) round($pagu * 0.94) : 0,
                    'tahun_anggaran' => $TA,
                    'status' => $status,
                    'risiko' => $pagu >= 5_000_000_000 ? 'kritis' : ($pagu >= 1_000_000_000 ? 'sedang' : 'rendah'),
                    'tender_gagal' => $gagal,
                    'tahap_tender' => $tahap,
                    'progress' => $progress,
                    'tanggal_mulai' => $now->copy()->subDays(45),
                    'tanggal_selesai' => $now->copy()->addDays(120),
                    'lokasi' => 'Kabupaten Banjarnegara',
                    'keterangan' => "Paket simulasi kinerja Pokja TA {$TA} (data dummy untuk pengujian)",
                ]
            );
        }

        // Kode paket simulasi lengkap: SIM-<tahun>-JK-001 (seri tanpa prefix SIM- ganda)
        $kode = fn (string $seri) => 'SIM-' . $TA . '-' . preg_replace('/^SIM-/', '', $seri);
        $idPaket = fn (string $seri) => PaketPengadaan::where('kode_paket', $kode($seri))->value('id');

        // =========================================================
        // 2. PERUBAHAN JADWAL — pola realistis per Pokja
        // =========================================================
        // [seri, jenis, tahap, alasan, BA?, akhir jam kerja?]
        $delaySim = [
            // Pokja II: paket dengan delay BERULANG 4x (2 tanpa BA, 2 di jam 16:50)
            ['SIM-JK-001', 'pengunduran', 'Evaluasi Penawaran', 'Reviu dokumen penawaran belum selesai', true, false],
            ['SIM-JK-001', 'pengunduran', 'Klarifikasi Teknis dan Harga', 'Menunggu kelengkapan data dari PPK', false, true],
            ['SIM-JK-001', 'pengunduran', 'Evaluasi Penawaran', 'Undangan evaluasi terlambat terkirim', false, false],
            ['SIM-JK-001', 'pengunduran', 'Penetapan Pemenang', 'Konsultasi hasil evaluasi dengan PPK', false, true],
            // Pokja II: tender gagal + reopening
            ['SIM-JK-003', 'reopening', 'Bid Opening Dokumen', 'Peserta kurang dari 3, perlu peninjauan HPS', false, false],
            ['SIM-JK-003', 'pengunduran', 'Pengumuman Hasil', 'Persiapan dokumen tender gagal ulang', false, false],
            // Pokja II: tunggal dengan BA (wajar)
            ['SIM-JK-004', 'pengunduran', 'Sanggahan dan Penjelasan', 'Antisipasi masuknya sanggahan penyedia', true, false],
            ['SIM-JK-007', 'penyesuaian', 'Kontrak', 'Penyesuaian lingkup pekerjaan addendum I', true, false],
            // Pokja II: paket lama 026 naikkan jadi berulang
            ['POKJA-2025-026', 'pengunduran', 'Evaluasi', 'Keterlambatan undangan klarifikasi', false, false],
            ['POKJA-2025-026', 'pengunduran', 'Evaluasi', 'Reviu berkas kualifikasi belum tuntas', false, true],
            ['POKJA-2025-026', 'pengunduran', 'Penetapan Pemenang', 'Menunggu hasil verifikasi dokumen', true, false],
            // Pokja I: wajar, ada BA
            ['POKJA-2025-001', 'pengunduran', 'Kontrak', 'Penyesuaian jadwal penandatanganan kontrak', true, false],
            ['POKJA-2025-024', 'penyesuaian', 'Pelaksanaan', 'Penyesuaian jadwal serah terima perangkat', true, false],
            // Pokja III: 1 tanpa BA (kasus di checklist)
            ['SIM-KON-001', 'pengunduran', 'Evaluasi Penawaran', 'Klarifikasi dokumen jaminan peserta', false, false],
            ['SIM-KON-002', 'pengunduran', 'Pelaksanaan', 'Cuaca tidak mendukung', true, false],
            // Pokja IV: 1 di akhir jam kerja tanpa BA
            ['RUP-65053898', 'pengunduran', 'Kontrak', 'Penyesuaian jadwal penandatanganan', false, true],
            // Pokja V: 1 wajar dengan BA
            ['SIM-SWA-001', 'penyesuaian', 'Pelaksanaan', 'Penyesuaian kalender kerja swakelola', true, false],
        ];

        foreach ($delaySim as $i => [$seri, $jenis, $tahap, $alasan, $ba, $akhirJam]) {
            // Seri berformat SIM-* memakai kode lengkap ber-tahun berjalan
            $pid = str_starts_with($seri, 'SIM-') ? $idPaket($seri) : PaketPengadaan::where('kode_paket', $seri)->value('id');
            if (! $pid) {
                continue;
            }
            $sudahAda = PerubahanJadwal::where('paket_id', $pid)
                ->where('alasan', $alasan)->exists();
            if ($sudahAda) {
                continue;
            }
            PerubahanJadwal::create([
                'paket_id' => $pid,
                'tanggal' => $now->copy()->subDays(50 - $i * 2),
                'jam' => $akhirJam ? '16:50' : '09:30',
                'jenis' => $jenis,
                'tahap_terkait' => $tahap,
                'alasan' => $alasan,
                'ada_berita_acara' => $ba,
            ]);
        }

        // =========================================================
        // 3. SANGGAHAN — termasuk lewat SLA 3 hari
        // =========================================================
        // [seri, penyedia, masuk(hari relatif), dijawab|null, hasil, substantif, catatan]
        $sanggahSim = [
            // Pokja II: menunggu 6 hari → PELANGGARAN SLA
            ['SIM-JK-001', 'PT Konsultan Cipta Rencana', -6, null, 'menunggu', false, 'Belum ada tanggapan resmi Pokja — melewati SLA 3 hari kerja.'],
            ['SIM-JK-003', 'CV Sinar Teknik', -20, -18, 'ditolak', true, 'Ditolak: sanggahan tidak menyertakan bukti administratif yang sah.'],
            ['SIM-JK-007', 'PT Wijaya Kusuma Konstruksi', -30, -27, 'diterima', true, 'Diterima sebagian: koreksi kriteria kualifikasi pada dokumen pemilihan.'],
            ['POKJA-2025-005', 'PT Graha Persada Propertindo', -9, null, 'menunggu', false, 'Menunggu jadwal rapat sanggahan — melewati SLA.'],
            ['POKJA-2025-011', 'CV Tunas Baru Abadi', -15, -13, 'ditolak', true, 'Ditolak: evaluasi sudah sesuai parameter yang ditetapkan awal.'],
            // Pokja I (belum punya sanggahan)
            ['POKJA-2025-008', 'PT Graha Persada Propertindo', -4, null, 'menunggu', false, 'Sanggahan baru masuk, masih dalam jangka waktu SLA.'],
            ['POKJA-2025-003', 'CV Berkah Jaya Farmasi', -35, -33, 'ditolak', true, 'Ditolak: spesifikasi obat sesuai Formulir Pemilihan standar.'],
            ['POKJA-2025-027', 'PT Graha Persada Propertindo', -14, -11, 'diterima', true, 'Diterima: perbaikan kriteria pengalaman sejenis pada jadwal ulang.'],
            // Pokja IV
            ['RUP-62350325', 'CV Berkah Jaya Farmasi', -10, -8, 'diterima', true, 'Diterima: klarifikasi lingkup jasa kalibrasi alat kesehatan.'],
            ['RUP-65053898', 'UD Tani Makmur', -12, -9, 'ditolak', true, 'Ditolak: penunjukan langsung telah sesuai ketentuan pemilihan.'],
            // Pokja V
            ['SIM-SWA-001', 'CV Sinar Teknik', -7, -4, 'ditolak', true, 'Ditolak: swakelola tidak membuka persaingan penyedia eksternal.'],
        ];

        foreach ($sanggahSim as [$seri, $namaPenyedia, $masuk, $jawab, $hasil, $substantif, $catatan]) {
            $pid = str_starts_with($seri, 'SIM-') ? $idPaket($seri) : PaketPengadaan::where('kode_paket', $seri)->value('id');
            if (! $pid) {
                continue;
            }
            $sudahAda = Sanggahan::where('paket_id', $pid)
                ->where('penyedia_id', $pen[$namaPenyedia] ?? 0)
                ->where('tanggal_masuk', $now->copy()->addDays($masuk)->toDateString())
                ->exists();
            if ($sudahAda) {
                continue;
            }
            Sanggahan::create([
                'paket_id' => $pid,
                'penyedia_id' => $pen[$namaPenyedia] ?? null,
                'tanggal_masuk' => $now->copy()->addDays($masuk),
                'tanggal_dijawab' => $jawab ? $now->copy()->addDays($jawab) : null,
                'hasil' => $hasil,
                'substantif' => $substantif,
                'catatan' => $catatan,
            ]);
        }

        // =========================================================
        // 4. ALERT ANOMALI sesuai pola data
        // =========================================================
        $alertSim = [
            // Pokja II — critical
            ['SIM-JK-001', 'Pokja II', 'delay_berulang', 'critical', "SIM-{$TA}-JK-001: Pengunduran jadwal 4x pada satu seleksi — 2x tanpa Berita Acara, 2x pukul 16:50 (akhir jam kerja)."],
            ['SIM-JK-003', 'Pokja II', 'tender_gagal_berulang', 'critical', "SIM-{$TA}-JK-003: Tender gagal ulang (peserta <3) — pola sama dengan SIM-{$TA}-JK-006. Perlu reviu kualifikasi/HPS."],
            ['SIM-JK-001', 'Pokja II', 'sanggah_tidak_tepat_waktu', 'critical', 'Sanggahan PT Konsultan Cipta Rencana pada paket supervisi Pasar Apu belum dijawab 6 hari — melewati SLA 3 hari kerja.'],
            // Pokja I — warning
            ['POKJA-2025-008', 'Pokja I', 'sanggah_tidak_tepat_waktu', 'warning', 'Sanggahan pada POKJA-2025-008 menunggu tanggapan (pantau batas SLA 3 hari).'],
            // Pokja III — warning
            ['SIM-KON-001', 'Pokja III', 'tanpa_ba', 'warning', "SIM-{$TA}-KON-001: Perubahan jadwal tanpa Berita Acara resmi."],
            // Pokja IV — info
            ['RUP-65053898', 'Pokja IV', 'jam_akhir_kerja', 'info', 'Perubahan jadwal pada RUP-65053898 dicatat pukul 16:50 tanpa BA — perlu klarifikasi prosedur.'],
        ];

        foreach ($alertSim as [$seri, $namaPokja, $jenis, $tingkat, $deskripsi]) {
            $pid = str_starts_with($seri, 'SIM-') ? $idPaket($seri) : ($seri === 'POKJA-2025-008' ? PaketPengadaan::where('kode_paket', $seri)->value('id') : ($seri === 'RUP-65053898' ? PaketPengadaan::where('kode_paket', $seri)->value('id') : null));
            $sudahAda = AlertAnomali::where('jenis', $jenis)
                ->where('deskripsi', $deskripsi)
                ->exists();
            if ($sudahAda) {
                continue;
            }
            AlertAnomali::create([
                'paket_id' => $pid,
                'pokja_id' => $pokja[$namaPokja] ?? null,
                'jenis' => $jenis,
                'tingkat' => $tingkat,
                'deskripsi' => $deskripsi,
            ]);
        }

        // =========================================================
        // 5. CHECKLIST AUDIT — Pokja II lengkap 6 kategori (banyak temuan)
        // Semua poin berpolaritas TEMUAN: "ya" = ditemukan masalah.
        // =========================================================
        $isiChecklist = [
            'Pokja II' => [
                1 => [['ya', "Beban paket aktif melebihi ambang ideal rasio per anggota (lihat {$TA}-JK-001 s.d. 008)."], ['ya', 'Paket pagu >1 M menumpuk pada satu personel evaluasi.'], ['tidak', 'Tidak ditemukan keterkaitan anggota dengan PPK/penyedia.']],
                2 => [['ya', "SIM-{$TA}-JK-001 diundur 4x; POKJA-2025-026 juga berulang."], ['ya', '8 perubahan jadwal tanpa Berita Acara pada kuartal ini.'], ['ya', 'Beberapa perubahan tercatat pukul 16:50 menjelang tutup penawaran.']],
                3 => [['tidak', 'Reviu dokumen bersama PPK berjalan sesuai prosedur.'], ['tidak', 'Kriteria kualifikasi tidak diubah setelah pengumuman.'], ['ya', "Addendum mendadak pada SIM-{$TA}-JK-007 berpotensi menggugurkan peserta."]],
                4 => [['tidak', 'Evaluasi sesuai parameter & bobot awal.'], ['tidak', 'Pengguguran berbasis bukti teknis.'], ['ya', "Indikasi perbedaan perlakuan klarifikasi antar peserta pada SIM-{$TA}-JK-002."]],
                5 => [['tidak', 'Audit trail SPSE terekam lengkap.'], ['ya', 'Terdapat indikasi komunikasi klarifikasi via WA di luar sistem.'], ['tidak', 'Akses akun dari lingkungan resmi.']],
                6 => [['ya', '2 sanggahan melewati SLA 3 hari tanpa tanggapan substantif.'], ['ya', 'Sanggahan CV Sinar Teknik mengindikasikan penyimpangan jadwal.'], ['ya', "Penetapan tender gagal SIM-{$TA}-JK-003 & 006 perlu reviu ulang."]],
            ],
            'Pokja III' => [
                2 => [['tidak', 'Tidak ada pengunduran >3 kali.'], ['ya', '1 perubahan jadwal tanpa Berita Acara.'], ['tidak', 'Tidak ada pola akhir jam kerja.']],
                6 => [['tidak', 'Sanggahan dijawab tepat waktu.'], ['tidak', 'Tidak ada indikasi penyimpangan.'], ['tidak', 'Tender gagal sesuai ketentuan.']],
            ],
            'Pokja I' => [
                2 => [['tidak', 'Pengunduran jarang, maksimal 1x per paket.'], ['ya', 'Sebagian perubahan jadwal tanpa BA — perlu perbaikan administrasi.'], ['tidak', 'Tidak ada pola akhir jam kerja.']],
                6 => [['tidak', 'Sanggahan dijawab tepat waktu & substantif.'], ['tidak', 'Tidak ada indikasi penyimpangan.'], ['ya', 'Tender gagal POKJA-2025-008 perlu evaluasi penyebab.']],
            ],
            'Pokja IV' => [
                1 => [['tidak', 'Beban sesuai kapasitas anggota.'], ['tidak', 'Distribusi paket merata.'], ['tidak', 'Tidak ada konflik kepentingan.']],
                5 => [['tidak', 'Audit trail lengkap.'], ['tidak', 'Tidak ada komunikasi informal.'], ['tidak', 'Akses dari lingkungan resmi.']],
            ],
            'Pokja V' => [
                1 => [['tidak', 'Beban ringan, sesuai kapasitas.'], ['tidak', 'Tidak ada penumpukan paket.'], ['tidak', 'Tidak ada keterkaitan personel.']],
            ],
        ];

        foreach ($isiChecklist as $namaPokja => $perKategori) {
            foreach ($perKategori as $kategori => $poinJawaban) {
                // Ganti total jawaban lama untuk kategori ini agar data simulasi konsisten
                AuditChecklist::where('pokja_id', $pokja[$namaPokja])
                    ->where('kategori', $kategori)
                    ->delete();
                foreach (AuditChecklist::poinDefault($kategori) as $idx => $poin) {
                    $jawab = $poinJawaban[$idx] ?? ['na', null];
                    AuditChecklist::create([
                        'pokja_id' => $pokja[$namaPokja],
                        'kategori' => $kategori,
                        'poin' => $poin,
                        'jawaban' => $jawab[0],
                        'catatan' => $jawab[1],
                        'auditor_id' => 1,
                    ]);
                }
            }
        }

        $this->command->info("✓ SimulasiKinerjaSeeder: paket, delay, sanggahan, alert & checklist audit per Pokja diperkaya (TA {$TA}, Pokja II = KRITIS)");
    }

    /**
     * Bersihkan baris duplikat hasil seeding berulang dan paket simulasi
     * dari tahun anggaran sebelumnya (beserta data turunannya).
     */
    private function bersihkanDuplikat(int $TA): void
    {
        // 1. Hapus paket simulasi lama (tahun != TA berjalan) beserta data turunannya
        $simLama = PaketPengadaan::where('kode_paket', 'like', 'SIM-%')
            ->where('tahun_anggaran', '!=', $TA)
            ->pluck('id');

        // 1b. Hapus paket simulasi berkode prefix ganda (SIM-<TA>-SIM-*) beserta turunannya
        $simGanda = PaketPengadaan::where('kode_paket', 'like', 'SIM-%-SIM-%')->pluck('id');
        $simLama = $simLama->merge($simGanda)->unique();

        if ($simLama->isNotEmpty()) {
            PerubahanJadwal::whereIn('paket_id', $simLama)->delete();
            Sanggahan::whereIn('paket_id', $simLama)->delete();
            AlertAnomali::whereIn('paket_id', $simLama)->delete();
            PaketPengadaan::whereIn('id', $simLama)->delete();
            $this->command->line("  - Paket simulasi lama/berkode ganda: {$simLama->count()} paket + data turunan dihapus");
        }

        // 2. Alert simulasi lama (referensi kode SIM tahun sebelumnya, paket_id bisa null)
        $alertLama = AlertAnomali::where('deskripsi', 'like', '%SIM-%')->get();
        $hapusAlert = $alertLama->filter(fn ($a) => preg_match('/SIM-\d{4}-/', $a->deskripsi) && ! str_contains($a->deskripsi, "SIM-{$TA}-"));
        if ($hapusAlert->isNotEmpty()) {
            AlertAnomali::whereIn('id', $hapusAlert->pluck('id'))->delete();
            $this->command->line('  - AlertAnomali: ' . $hapusAlert->count() . ' alert simulasi lama dihapus');
        }

        // 3. Duplikat baris identik (hasil seeding berulang)
        $kunciDuplikat = [
            PerubahanJadwal::class => ['paket_id', 'tanggal', 'jam', 'jenis', 'tahap_terkait', 'alasan'],
            Sanggahan::class => ['paket_id', 'penyedia_id', 'tanggal_masuk', 'hasil'],
            AlertAnomali::class => ['pokja_id', 'paket_id', 'jenis', 'deskripsi'],
            AuditChecklist::class => ['pokja_id', 'kategori', 'poin'],
        ];

        foreach ($kunciDuplikat as $model => $kolom) {
            $rows = $model::query()
                ->select(array_merge(['id'], $kolom))
                ->orderBy('id')
                ->get();
            $lihat = [];
            $hapus = [];
            foreach ($rows as $r) {
                $kunci = implode('|', array_map(fn ($c) => $r->{$c}, $kolom));
                if (isset($lihat[$kunci])) {
                    $hapus[] = $r->id;
                } else {
                    $lihat[$kunci] = true;
                }
            }
            if ($hapus) {
                $model::whereIn('id', $hapus)->delete();
                $this->command->line('  - ' . class_basename($model) . ': ' . count($hapus) . ' duplikat dibersihkan');
            }
        }

        // 4. Hapus checklist dengan teks poin lama (tidak ada di poinDefault saat ini)
        $semuaPoin = [];
        foreach (range(1, 6) as $kat) {
            foreach (AuditChecklist::poinDefault($kat) as $poin) {
                $semuaPoin[] = $poin;
            }
        }
        $stale = AuditChecklist::whereNotIn('poin', $semuaPoin)->pluck('id');
        if ($stale->isNotEmpty()) {
            AuditChecklist::whereIn('id', $stale)->delete();
            $this->command->line('  - AuditChecklist: ' . $stale->count() . ' poin lama (redaksi usang) dihapus');
        }
    }
}
