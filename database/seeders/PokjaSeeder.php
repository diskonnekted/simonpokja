<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pokja;
use App\Models\PaketPengadaan;
use App\Models\PerubahanJadwal;
use App\Models\Sanggahan;
use App\Models\AlertAnomali;
use App\Models\AuditChecklist;
use Carbon\Carbon;

class PokjaSeeder extends Seeder
{
    public function run(): void
    {
        // ===== POKJA / PANITIA PENGADAAN LPSE BANJARNEGARA =====
        $pokja1 = Pokja::updateOrCreate(['nama' => 'Pokja I'], [
            'bidang' => 'Barang & e-Purchasing',
            'ketua' => 'Drs. Hendra Wijaya, M.Si.',
            'nip_ketua' => '19710512 199803 1 004',
            'anggota' => ['Sri Mulyani, S.E.', 'Agus Priyanto, S.T.', 'Dewi Lestari, S.Sos.', 'Bambang Sutrisno'],
            'kapasitas_ideal' => 5,
        ]);

        $pokja2 = Pokja::updateOrCreate(['nama' => 'Pokja II'], [
            'bidang' => 'Jasa Konsultansi',
            'ketua' => 'Ir. Slamet Santoso, M.T.',
            'nip_ketua' => '19680422 199501 1 002',
            'anggota' => ['Rina Kartika, S.T.', 'Joko Purnomo, S.E.', 'Ahmad Dahlan, S.H.'],
            'kapasitas_ideal' => 5,
        ]);

        $pokja3 = Pokja::updateOrCreate(['nama' => 'Pokja III'], [
            'bidang' => 'Pekerjaan Konstruksi',
            'ketua' => 'Dr. Endang Sutrisna, M.M.',
            'nip_ketua' => '19750930 200312 1 009',
            'anggota' => ['Fitri Handayani, S.E., M.Si.', 'Gunawan Wibisono, S.T.', 'Lia Ambarwati, S.Sos.', 'Prasetyo Adi, S.T.', 'Nur Hidayat'],
            'kapasitas_ideal' => 6,
        ]);

        $pokja4 = Pokja::updateOrCreate(['nama' => 'Pokja IV'], [
            'bidang' => 'Jasa Lainnya',
            'ketua' => 'Sumardi, S.Sos., M.A.P.',
            'nip_ketua' => '19820214 201001 1 011',
            'anggota' => ['Wulan Sari, S.E.', 'Taufik Hidayat, S.T.', 'Dian Puspita, S.E.'],
            'kapasitas_ideal' => 5,
        ]);

        $pokja5 = Pokja::updateOrCreate(['nama' => 'Pokja V'], [
            'bidang' => 'Swakelola & Pengadaan Terintegrasi',
            'ketua' => 'Hj. Siti Aminah, M.M.',
            'nip_ketua' => '19790815 200604 2 007',
            'anggota' => ['Rudi Hartanto, S.T.', 'Maya Anggraeni, S.E.', 'Hendra Gunawan'],
            'kapasitas_ideal' => 4,
        ]);

        $mapPokja = [
            'Pokja I' => $pokja1->id,
            'Pokja II' => $pokja2->id,
            'Pokja III' => $pokja3->id,
            'Pokja IV' => $pokja4->id,
            'Pokja V' => $pokja5->id,
        ];

        // ===== DISTRIBUSI PAKET KE POKJA =====
        // Pokja II sengaja overload (sesuai dokumen: kritikal karena beban + delay)
        $assignPokja = [
            'POKJA-2025-001' => 'Pokja II',  'POKJA-2025-002' => 'Pokja II',  'POKJA-2025-003' => 'Pokja I',
            'POKJA-2025-004' => 'Pokja II',  'POKJA-2025-005' => 'Pokja II',  'POKJA-2025-006' => 'Pokja I',
            'POKJA-2025-007' => 'Pokja II',  'POKJA-2025-008' => 'Pokja I',   'POKJA-2025-009' => 'Pokja III',
            'POKJA-2025-010' => 'Pokja I',   'POKJA-2025-011' => 'Pokja II',  'POKJA-2025-012' => 'Pokja II',
            'POKJA-2025-013' => 'Pokja I',   'POKJA-2025-014' => 'Pokja III', 'POKJA-2025-015' => 'Pokja I',
            'POKJA-2025-016' => 'Pokja III', 'POKJA-2025-017' => 'Pokja I',   'POKJA-2025-018' => 'Pokja III',
            'POKJA-2025-019' => 'Pokja I',   'POKJA-2025-020' => 'Pokja II',  'POKJA-2025-021' => 'Pokja III',
            'POKJA-2025-022' => 'Pokja IV',  'POKJA-2025-023' => 'Pokja III', 'POKJA-2025-024' => 'Pokja I',
            'POKJA-2025-025' => 'Pokja V',   'POKJA-2025-026' => 'Pokja II',  'POKJA-2025-027' => 'Pokja I',
            'POKJA-2025-028' => 'Pokja III', 'POKJA-2025-029' => 'Pokja IV',  'POKJA-2025-030' => 'Pokja V',
        ];

        // Tandai tender gagal (audit checklist: tender gagal berulang)
        $tenderGagal = ['POKJA-2025-008', 'POKJA-2025-016', 'POKJA-2025-022'];

        // Tahap tender saat ini
        $tahapTender = [
            'POKJA-2025-001' => 'evaluasi',   'POKJA-2025-002' => 'evaluasi',
            'POKJA-2025-004' => 'pengumuman', 'POKJA-2025-005' => 'sanggah',
            'POKJA-2025-008' => 'pengumuman', 'POKJA-2025-009' => 'evaluasi',
            'POKJA-2025-014' => 'persiapan',  'POKJA-2025-016' => 'pengumuman',
            'POKJA-2025-020' => 'pengumuman', 'POKJA-2025-022' => 'persiapan',
            'POKJA-2025-026' => 'evaluasi',   'POKJA-2025-028' => 'persiapan',
        ];

        foreach ($assignPokja as $kode => $namaPokja) {
            $update = ['pokja_id' => $mapPokja[$namaPokja]];
            if (in_array($kode, $tenderGagal)) {
                $update['tender_gagal'] = true;
            }
            if (isset($tahapTender[$kode])) {
                $update['tahap_tender'] = $tahapTender[$kode];
            }
            // Paket kritis: pagu besar + status pemilihan
            PaketPengadaan::where('kode_paket', $kode)->update($update);
        }

        // Risiko paket: pagu > 8 M + tahap tender = kritis
        PaketPengadaan::where('pagu', '>=', 8000000000)->update(['risiko' => 'kritis']);
        PaketPengadaan::whereBetween('pagu', [3000000000, 8000000000])->update(['risiko' => 'sedang']);

        // ===== PERUBAHAN JADWAL (SLA / DELAY) =====
        // Pokja II: pola delay berulang & tanpa BA (sesuai checklist audit)
        $delayData = [
            // [kode, jenis, tahap, alasan, BA?, jam akhir kerja?]
            ['POKJA-2025-002', 'pengunduran', 'Evaluasi Penawaran', 'Reviu dokumen belum selesai', true, false],
            ['POKJA-2025-002', 'pengunduran', 'Klarifikasi Teknis', 'Menunggu kelengkapan data PPK', false, true],  // jam 16:50!
            ['POKJA-2025-002', 'pengunduran', 'Evaluasi Penawaran', 'Keterlambatan undangan evaluasi', false, false],
            ['POKJA-2025-002', 'pengunduran', 'Penetapan Pemenang', 'Konsultasi dengan PPK', false, true],      // 4x!
            ['POKJA-2025-004', 'pengunduran', 'Administrasi & Teknis', 'Reviu dokumen peserta', true, false],
            ['POKJA-2025-004', 'reopening', 'Bid Opening', 'Koreksi sistem SPSE', true, false],
            ['POKJA-2025-008', 'pengunduran', 'Pengumuman Hasil', 'Persiapan dokumentasi BA', true, false],
            ['POKJA-2025-008', 'reopening', 'Bid Opening', 'Tidak ada peserta lolos admin', false, true],
            ['POKJA-2025-008', 'pengunduran', 'Pengumuman Hasil', 'Menunggu konfirmasi PPK', false, false],
            ['POKJA-2025-016', 'reopening', 'Bid Opening', 'Peserta kurang dari 3', false, false],
            ['POKJA-2025-016', 'pengunduran', 'Pengumuman Hasil', 'Reviu dokumen penawaran', true, false],
            ['POKJA-2025-016', 'reopening', 'Bid Opening', 'Tender gagal tahap 1', false, false],
            ['POKJA-2025-020', 'pengunduran', 'Evaluasi', 'Klarifikasi dengan penyedia', true, false],
            ['POKJA-2025-026', 'pengunduran', 'Evaluasi', 'Menunggu hasil verifikasi', false, true],
            ['POKJA-2025-009', 'pengunduran', 'Bid Opening', 'Perbaikan dokumen lingkup', true, false],
        ];

        foreach ($delayData as $i => [$kode, $jenis, $tahap, $alasan, $ba, $akhirJamKerja]) {
            PerubahanJadwal::create([
                'paket_id' => PaketPengadaan::where('kode_paket', $kode)->value('id'),
                'tanggal' => Carbon::now()->subDays(60 - $i * 3),
                'jam' => $akhirJamKerja ? '16:50' : '09:30',
                'jenis' => $jenis,
                'tahap_terkait' => $tahap,
                'alasan' => $alasan,
                'ada_berita_acara' => $ba,
            ]);
        }

        // ===== SANGGAHAN =====
        $sanggahanData = [
            // [kode, penyedia, masuk, dijawab, hasil, substantif]
            ['POKJA-2025-002', 'PT Wijaya Kusuma Konstruksi', -40, -36, 'ditolak', true],
            ['POKJA-2025-004', 'CV Pilar Utama', -25, null, 'menunggu', false],       // lewat SLA!
            ['POKJA-2025-009', 'PT Adhi Karya Persada', -18, -15, 'ditolak', true],
            ['POKJA-2025-016', 'CV Sinar Teknik', -12, -8, 'diterima', true],
            ['POKJA-2025-020', 'PT Konsultan Cipta Rencana', -5, null, 'menunggu', false],
        ];
        foreach ($sanggahanData as [$kode, $penyedia, $masuk, $jawab, $hasil, $substantif]) {
            Sanggahan::create([
                'paket_id' => PaketPengadaan::where('kode_paket', $kode)->value('id'),
                'penyedia_id' => \App\Models\Penyedia::where('nama', $penyedia)->value('id'),
                'tanggal_masuk' => Carbon::now()->addDays($masuk),
                'tanggal_dijawab' => $jawab ? Carbon::now()->addDays($jawab) : null,
                'hasil' => $hasil,
                'substantif' => $substantif,
                'catatan' => $substantif ? 'Dijawab dengan paparan substantif' : 'Menunggu tanggapan Pokja',
            ]);
        }

        // ===== ALERT ANOMALI (auto-generate dari pola) =====
        $paket2 = PaketPengadaan::where('kode_paket', 'POKJA-2025-002')->first();
        $paket8 = PaketPengadaan::where('kode_paket', 'POKJA-2025-008')->first();
        $paket16 = PaketPengadaan::where('kode_paket', 'POKJA-2025-016')->first();

        AlertAnomali::create([
            'paket_id' => $paket2->id, 'pokja_id' => $pokja2->id,
            'jenis' => 'delay_berulang', 'tingkat' => 'critical',
            'deskripsi' => 'POKJA-2025-002: Pengunduran jadwal 4x dalam satu tender — 2x tanpa Berita Acara, 2x di akhir jam kerja (16:50).',
        ]);
        AlertAnomali::create([
            'paket_id' => $paket8->id, 'pokja_id' => $pokja1->id,
            'jenis' => 'tender_gagal_berulang', 'tingkat' => 'critical',
            'deskripsi' => 'POKJA-2025-008: Tender gagal + reopening 3x. Pola serupa terjadi juga pada POKJA-2025-016 & 022.',
        ]);
        AlertAnomali::create([
            'paket_id' => $paket16->id, 'pokja_id' => $pokja3->id,
            'jenis' => 'sanggah_tidak_tepat_waktu', 'tingkat' => 'warning',
            'deskripsi' => 'Sanggahan CV Sinar Teknik pada POKJA-2025-016 dijawab melewati batas SLA 3 hari kerja.',
        ]);
        AlertAnomali::create([
            'pokja_id' => $pokja2->id,
            'jenis' => 'beban_overload', 'tingkat' => 'critical',
            'deskripsi' => 'Pokja II menangani beban melebihi kapasitas: rasio paket/anggota jauh di atas ambang ideal (5).',
        ]);
        AlertAnomali::create([
            'pokja_id' => $pokja3->id,
            'jenis' => 'tanpa_ba', 'tingkat' => 'warning',
            'deskripsi' => '3 perubahan jadwal pada kuartal ini tanpa Berita Acara resmi (Pokja III).',
        ]);

        // ===== CHECKLIST AUDIT 6 KATEGORI (contoh terisi untuk Pokja II) =====
        foreach (AuditChecklist::poinDefault(1) as $poin) {
            AuditChecklist::create([
                'pokja_id' => $pokja2->id, 'kategori' => 1, 'poin' => $poin,
                'jawaban' => 'ya', 'catatan' => 'Beban Pokja II melebihi ambang ideal.',
                'auditor_id' => 1,
            ]);
        }
        foreach (AuditChecklist::poinDefault(2) as $poin) {
            AuditChecklist::create([
                'pokja_id' => $pokja2->id, 'kategori' => 2, 'poin' => $poin,
                'jawaban' => 'ya', 'catatan' => 'Delay >3x, 2 perubahan tanpa BA.',
                'auditor_id' => 1,
            ]);
        }
        foreach (AuditChecklist::poinDefault(6) as $poin) {
            AuditChecklist::create([
                'pokja_id' => $pokja2->id, 'kategori' => 6, 'poin' => $poin,
                'jawaban' => 'tidak', 'catatan' => 'Sanggahan ada yang melewati SLA.',
                'auditor_id' => 1,
            ]);
        }
        // Pokja I: contoh kondisi baik
        foreach (AuditChecklist::poinDefault(1) as $poin) {
            AuditChecklist::create([
                'pokja_id' => $pokja1->id, 'kategori' => 1, 'poin' => $poin,
                'jawaban' => 'tidak', 'auditor_id' => 1,
            ]);
        }

        $this->command->info('✓ PokjaSeeder: 4 Pokja, distribusi paket, delay, sanggahan, alert, checklist audit');
    }
}
