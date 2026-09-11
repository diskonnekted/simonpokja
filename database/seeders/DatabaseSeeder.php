<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Opd;
use App\Models\Penyedia;
use App\Models\PaketPengadaan;
use App\Models\Tahapan;
use App\Models\User;
use App\Models\Evaluasi;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===================== USERS =====================
        User::updateOrCreate(
            ['email' => 'admin@banjarnegara.go.id'],
            [
                'name' => 'Administrator Pokja',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // ===================== OPD KABUPATEN BANJARNEGARA =====================
        $opdList = [
            ['kode' => '1.01.2.22.0.00.01.0000', 'nama' => 'Dewan Perwakilan Rakyat Daerah', 'singkatan' => 'DPRD', 'kepala' => 'H. Satum, S.E., M.M.'],
            ['kode' => '4.01.2.22.0.00.01.0000', 'nama' => 'Sekretariat Daerah', 'singkatan' => 'Setda', 'kepala' => 'Drs. H. Sumarno, M.Si.'],
            ['kode' => '4.02.2.22.0.00.01.0000', 'nama' => 'Sekretariat DPRD', 'singkatan' => 'Setwan', 'kepala' => 'Dewi Kartika, S.Sos.'],
            ['kode' => '5.02.2.22.0.00.01.0000', 'nama' => 'Badan Pengembangan Sumber Daya Manusia', 'singkatan' => 'BPSDM', 'kepala' => 'Dr. Rina Widyastuti, M.M.'],
            ['kode' => '1.02.2.22.0.00.01.0000', 'nama' => 'Badan Keuangan Daerah', 'singkatan' => 'BKD', 'kepala' => 'Hendi Suryana, S.E., M.Si., Ak.'],
            ['kode' => '5.01.2.22.0.00.01.0000', 'nama' => 'Badan Perencanaan Pembangunan Daerah', 'singkatan' => 'Bappeda', 'kepala' => 'Ir. H. Joko Susilo, M.T.'],
            ['kode' => '1.03.2.22.0.00.01.0000', 'nama' => 'Dinas Pendidikan dan Kebudayaan', 'singkatan' => 'Disdikbud', 'kepala' => 'Dr. Suryadi, M.Pd.'],
            ['kode' => '1.04.2.22.0.00.01.0000', 'nama' => 'Dinas Kesehatan', 'singkatan' => 'Dinkes', 'kepala' => 'dr. Ani Susilawati, M.Kes.'],
            ['kode' => '2.01.2.22.0.00.01.0000', 'nama' => 'Dinas Pekerjaan Umum dan Penataan Ruang', 'singkatan' => 'DPUPR', 'kepala' => 'Ir. Bambang Hermanto, M.T.'],
            ['kode' => '2.02.2.22.0.00.01.0000', 'nama' => 'Dinas Perumahan, Kawasan Permukiman dan Lingkungan Hidup', 'singkatan' => 'DPKPLH', 'kepala' => 'Sri Wahyuni, S.T., M.T.'],
            ['kode' => '2.03.2.22.0.00.01.0000', 'nama' => 'Dinas Ketahanan Pangan dan Pertanian', 'singkatan' => 'DKPP', 'kepala' => 'H. Ahmad Fauzi, S.P., M.Si.'],
            ['kode' => '2.04.2.22.0.00.01.0000', 'nama' => 'Dinas Kependudukan dan Pencatatan Sipil', 'singkatan' => 'Disdukcapil', 'kepala' => 'Endang Sutrisni, S.Si., M.M.'],
            ['kode' => '2.05.2.22.0.00.01.0000', 'nama' => 'Dinas Sosial', 'singkatan' => 'Dinsos', 'kepala' => 'Sutrisno, S.Sos., M.M.'],
            ['kode' => '2.06.2.22.0.00.01.0000', 'nama' => 'Dinas Tenaga Kerja dan Transmigrasi', 'singkatan' => 'Disnakertrans', 'kepala' => 'Hery Setiawan, S.E., M.M.'],
            ['kode' => '2.08.2.22.0.00.01.0000', 'nama' => 'Dinas Koperasi, UKM dan Perindustrian', 'singkatan' => 'Diskoperindag', 'kepala' => 'Rina Marlina, S.E., M.M.'],
            ['kode' => '4.01.2.22.0.00.02.0000', 'nama' => 'Satuan Polisi Pamong Praja', 'singkatan' => 'Satpol PP', 'kepala' => 'H. Wawan Setiawan, S.H., M.H.'],
            ['kode' => '5.02.2.22.0.00.02.0000', 'nama' => 'Dinas Pemadam Kebakaran', 'singkatan' => 'Damkar', 'kepala' => 'Dedi Kurniawan, S.T.'],
            ['kode' => '2.07.2.22.0.00.01.0000', 'nama' => 'Dinas Komunikasi dan Informatika', 'singkatan' => 'Diskominfo', 'kepala' => 'Agus Prasetyo, S.Kom., M.T.I.'],
            ['kode' => '2.09.2.22.0.00.01.0000', 'nama' => 'Dinas Pemuda dan Olahraga', 'singkatan' => 'Dispora', 'kepala' => 'Yuyun Yunanhel, S.Or., M.Pd.'],
            ['kode' => '2.10.2.22.0.00.01.0000', 'nama' => 'Dinas Perpustakaan dan Kearsipan', 'singkatan' => 'Dispusip', 'kepala' => 'Siti Aminah, S.IP.'],
            ['kode' => '1.05.2.22.0.00.01.0000', 'nama' => 'Dinas Pengendalian Penduduk, KB, PPA dan Perlindungan Anak', 'singkatan' => 'DPPKBPPA', 'kepala' => 'Dra. Endang Kusumawati, M.Si.'],
            ['kode' => '2.11.2.22.0.00.01.0000', 'nama' => 'Dinas Perhubungan', 'singkatan' => 'Dishub', 'kepala' => 'Slamet Riyadi, S.T., M.T.'],
            ['kode' => '2.12.2.22.0.00.01.0000', 'nama' => 'Dinas Lingkungan Hidup', 'singkatan' => 'DLH', 'kepala' => 'Wiwik Hidayati, S.T., M.Si.'],
            ['kode' => '4.04.2.22.0.00.01.0000', 'nama' => 'Badan Pendapatan Daerah', 'singkatan' => 'Bapenda', 'kepala' => 'Dwi Cahyono, S.E., M.Si., Ak.'],
            ['kode' => '1.06.2.22.0.00.01.0000', 'nama' => 'Dinas Paribasa dan Kebudayaan', 'singkatan' => 'Disparbud', 'kepala' => 'Yusuf Hariyadi, S.E., M.M.'],
        ];

        $opdIds = [];
        foreach ($opdList as $opd) {
            $model = Opd::updateOrCreate(['kode' => $opd['kode']], array_merge($opd, [
                'email' => strtolower(str_replace([' ', ','], ['', ''], $opd['singkatan'])) . '@banjarnegarakab.go.id',
                'alamat' => 'Kabupaten Banjarnegara, Jawa Tengah',
            ]));
            $opdIds[$opd['singkatan']] = $model->id;
        }

        // ===================== PENYEDIA =====================
        $penyediaList = [
            ['npwp' => '91.234.567.8-401.000', 'nama' => 'PT Adhi Karya Persada', 'jenis_usaha' => 'non_kecil', 'kualifikasi' => 'besar', 'direktur' => 'Ir. Handoko Sugiharto'],
            ['npwp' => '92.345.678.9-401.000', 'nama' => 'CV Karya Mandiri Banjarnegara', 'jenis_usaha' => 'kecil', 'kualifikasi' => 'kecil', 'direktur' => 'Sugeng Riyadi'],
            ['npwp' => '93.456.789.0-401.000', 'nama' => 'PT Wijaya Kusuma Konstruksi', 'jenis_usaha' => 'non_kecil', 'kualifikasi' => 'menengah', 'direktur' => 'Joko Prasetyo, S.T.'],
            ['npwp' => '94.567.890.1-401.000', 'nama' => 'CV Sinar Teknik', 'jenis_usaha' => 'kecil', 'kualifikasi' => 'kecil', 'direktur' => 'Agus Setiawan'],
            ['npwp' => '95.678.901.2-401.000', 'nama' => 'PT Mitra Solusi Informatika', 'jenis_usaha' => 'non_kecil', 'kualifikasi' => 'menengah', 'direktur' => 'Rudi Hartono, M.Kom.'],
            ['npwp' => '96.789.012.3-401.000', 'nama' => 'Koperasi Sejahtera Bersama', 'jenis_usaha' => 'koperasi', 'kualifikasi' => 'kecil', 'direktur' => 'Siti Nurhaliza, S.E.'],
            ['npwp' => '97.890.123.4-401.000', 'nama' => 'CV Tunas Baru Abadi', 'jenis_usaha' => 'kecil', 'kualifikasi' => 'kecil', 'direktur' => 'Hadi Susanto'],
            ['npwp' => '98.901.234.5-401.000', 'nama' => 'PT Graha Persada Propertindo', 'jenis_usaha' => 'non_kecil', 'kualifikasi' => 'menengah', 'direktur' => 'Budi Santoso, S.T.'],
            ['npwp' => '99.012.345.6-401.000', 'nama' => 'CV Pilar Utama', 'jenis_usaha' => 'kecil', 'kualifikasi' => 'kecil', 'direktur' => 'Eko Purnomo'],
            ['npwp' => '90.123.456.7-401.000', 'nama' => 'PT Konsultan Cipta Rencana', 'jenis_usaha' => 'non_kecil', 'kualifikasi' => 'menengah', 'direktur' => 'Dr. Ir. Wawan Gunawan, M.T.'],
            ['npwp' => '89.012.345.6-401.000', 'nama' => 'CV Berkah Jaya Farmasi', 'jenis_usaha' => 'kecil', 'kualifikasi' => 'kecil', 'direktur' => 'Lilis Suryani'],
            ['npwp' => '88.012.345.6-401.000', 'nama' => 'UD Tani Makmur', 'jenis_usaha' => 'perseorangan', 'kualifikasi' => 'kecil', 'direktur' => 'Sarwo Edhi'],
        ];

        $penyediaIds = [];
        foreach ($penyediaList as $p) {
            $model = Penyedia::updateOrCreate(['npwp' => $p['npwp']], array_merge($p, [
                'alamat' => 'Banjarnegara / Purwokerto / Semarang',
                'telepon' => '028x-' . random_int(100000, 999999),
                'email' => strtolower(str_replace([' ', 'PT', 'CV', 'UD', '.'], '', $p['nama'])) . '@gmail.com',
                'aktif' => true,
            ]));
            $penyediaIds[$p['nama']] = $model->id;
        }

        // ===================== PAKET PENGADAAN =====================
        $paketList = [
            // [kode, nama, opd, penyedia, jenis, metode, pagu, hps, kontrak, status, progress]
            ['POKJA-2025-001', 'Rehabilitasi Ruang Kelas SDN 1 Banjarnegara', 'Disdikbud', 'CV Karya Mandiri Banjarnegara', 'konstruksi', 'pengadaan_langsung', 850000000, 820000000, 785000000, 'pelaksanaan', 65],
            ['POKJA-2025-002', 'Peningkatan Jalan Kabupaten Rawalo - Kalibenda', 'DPUPR', 'PT Adhi Karya Persada', 'konstruksi', 'tender', 12500000000, 11900000000, 11250000000, 'pelaksanaan', 45],
            ['POKJA-2025-003', 'Pengadaan Alat Kesehatan RSUD Banjarnegara', 'Dinkes', 'CV Berkah Jaya Farmasi', 'barang', 'epurchasing', 2100000000, 2050000000, 1980000000, 'selesai', 100],
            ['POKJA-2025-004', 'Pembangunan Drainase di Kecamatan Pekuncen', 'DPUPR', 'PT Wijaya Kusuma Konstruksi', 'konstruksi', 'tender', 6500000000, 6200000000, 5950000000, 'kontrak', 10],
            ['POKJA-2025-005', 'Jasa Konsultan Supervisi Jalan Pengereman', 'DPUPR', 'PT Konsultan Cipta Rencana', 'jasa_konsultansi', 'seleksi', 950000000, 920000000, 890000000, 'kontrak', 20],
            ['POKJA-2025-006', 'Pengadaan Perangkat Komputer dan Laptop', 'Diskominfo', 'PT Mitra Solusi Informatika', 'barang', 'epurchasing', 1750000000, 1700000000, 1685000000, 'selesai', 100],
            ['POKJA-2025-007', 'Rehabilitasi Jaringan Irigasi Rawakesawa', 'DKPP', 'CV Sinar Teknik', 'konstruksi', 'pengadaan_langsung', 1450000000, 1400000000, 1350000000, 'pelaksanaan', 80],
            ['POKJA-2025-008', 'Pengadaan Kendaraan Operasional Dinas', 'Setda', 'PT Graha Persada Propertindo', 'barang', 'tender', 3200000000, 3100000000, null, 'pemilihan', 0],
            ['POKJA-2025-009', 'Pembangunan Gedung PKL Pasar Wangon', 'Diskoperindag', 'PT Wijaya Kusuma Konstruksi', 'konstruksi', 'tender', 8900000000, 8500000000, 8320000000, 'pelaksanaan', 55],
            ['POKJA-2025-010', 'Pengadaan Obat-obatan RSUD', 'Dinkes', 'CV Berkah Jaya Farmasi', 'barang', 'epurchasing', 4700000000, 4600000000, 4450000000, 'kontrak', 90],
            ['POKJA-2025-011', 'Peningkatan Jalan Sigaluh - Madukara', 'DPUPR', 'CV Pilar Utama', 'konstruksi', 'pengadaan_langsung', 2100000000, 2050000000, 1990000000, 'persiapan', 0],
            ['POKJA-2025-012', 'Jasa Konsultan Perencanaan PDAM Mandiri', 'Bappeda', 'PT Konsultan Cipta Rencana', 'jasa_konsultansi', 'seleksi', 550000000, 530000000, 515000000, 'selesai', 100],
            ['POKJA-2025-013', 'Pengadaan Seragam Pegawai', 'BKD', 'Koperasi Sejahtera Bersama', 'barang', 'epurchasing', 890000000, 870000000, 858000000, 'selesai', 100],
            ['POKJA-2025-014', 'Pembangunan Embung Baturaden', 'DPUPR', null, 'konstruksi', 'tender', 15500000000, 14800000000, null, 'persiapan', 0],
            ['POKJA-2025-015', 'Pengadaan Buku Perpustakaan Sekolah', 'Disdikbud', 'CV Tunas Baru Abadi', 'barang', 'pengadaan_langsung', 420000000, 410000000, 398000000, 'selesai', 100],
            ['POKJA-2025-016', 'Peningkatan Jalan Lingkar Purwonegoro', 'DPUPR', null, 'konstruksi', 'tender', 9850000000, 9500000000, null, 'draft', 0],
            ['POKJA-2025-017', 'Pengadaan Alat Tulis Kantor (ATK)', 'Setwan', 'UD Tani Makmur', 'barang', 'pengadaan_langsung', 185000000, 180000000, 176000000, 'selesai', 100],
            ['POKJA-2025-018', 'Rehabilitasi Kolam Renang Baturaden', 'Dispora', 'CV Karya Mandiri Banjarnegara', 'konstruksi', 'penunjukan_langsung', 1200000000, 1150000000, 1125000000, 'pelaksanaan', 35],
            ['POKJA-2025-019', 'Pengadaan Mesin Fotokopi dan Printer', 'Dispusip', 'PT Mitra Solusi Informatika', 'barang', 'epurchasing', 320000000, 310000000, 302000000, 'kontrak', 70],
            ['POKJA-2025-020', 'Jasa Konsultan DED Wisata Rakit Gembira Loka', 'Disparbud', 'PT Konsultan Cipta Rencana', 'jasa_konsultansi', 'seleksi', 680000000, 660000000, 645000000, 'pemilihan', 0],
            ['POKJA-2025-021', 'Pembangunan Jembatan Gantung Karangjambu', 'DPUPR', 'CV Sinar Teknik', 'konstruksi', 'pengadaan_langsung', 1850000000, 1800000000, 1755000000, 'pelaksanaan', 70],
            ['POKJA-2025-022', 'Pengadaan Mobil Ambulans', 'Dinkes', null, 'barang', 'tender', 1400000000, 1350000000, null, 'draft', 0],
            ['POKJA-2025-023', 'Peningkatan Saluran Irigasi Kalibangkang', 'DKPP', 'CV Pilar Utama', 'konstruksi', 'pengadaan_langsung', 2600000000, 2500000000, 2430000000, 'kontrak', 15],
            ['POKJA-2025-024', 'Pengadaan Server dan Infrastruktur Jaringan', 'Diskominfo', 'PT Mitra Solusi Informatika', 'barang', 'epurchasing', 2900000000, 2800000000, 2750000000, 'selesai', 100],
            ['POKJA-2025-025', 'Peningkatan Lapangan Olahraga Sigaluh', 'Dispora', 'CV Tunas Baru Abadi', 'konstruksi', 'pengadaan_langsung', 980000000, 950000000, 920000000, 'persiapan', 0],
            ['POKJA-2025-026', 'Jasa Konsultan Manajemen Konstruksi Puskesmas', 'Dinkes', 'PT Konsultan Cipta Rencana', 'jasa_konsultansi', 'seleksi', 720000000, 700000000, null, 'pemilihan', 0],
            ['POKJA-2025-027', 'Pengadaan Alat Praktik Sekolah Kejuruan', 'Disdikbud', 'PT Graha Persada Propertindo', 'barang', 'tender', 3200000000, 3100000000, 3010000000, 'kontrak', 25],
            ['POKJA-2025-028', 'Pembangunan Pasar Apu Kabupaten', 'Diskoperindag', null, 'konstruksi', 'tender', 6500000000, 6300000000, null, 'persiapan', 0],
            ['POKJA-2025-029', 'Rehabilitasi Kantor Camat Punggelan', 'Setda', 'CV Pilar Utama', 'konstruksi', 'pengadaan_langsung', 750000000, 730000000, 710000000, 'selesai', 100],
            ['POKJA-2025-030', 'Pengadaan Kendaraan Motor Roda Tiga', 'Dinsos', 'PT Graha Persada Propertindo', 'barang', 'epurchasing', 450000000, 440000000, 428000000, 'selesai', 100],
        ];

        $statusMap = ['draft' => 0, 'persiapan' => 5, 'pemilihan' => 15, 'kontrak' => 25, 'pelaksanaan' => 50, 'selesai' => 100];

        foreach ($paketList as $i => $p) {
            [$kode, $nama, $opd, $penyedia, $jenis, $metode, $pagu, $hps, $kontrak, $status, $progress] = $p;

            $paket = PaketPengadaan::updateOrCreate(['kode_paket' => $kode], [
                'nama_paket' => $nama,
                'opd_id' => $opdIds[$opd],
                'penyedia_id' => $penyedia ? $penyediaIds[$penyedia] : null,
                'jenis' => $jenis,
                'metode' => $metode,
                'sumber_dana' => ['apbd', 'apbd', 'dak', 'apbd', 'blm'][$i % 5],
                'pagu' => $pagu,
                'hps' => $hps,
                'nilai_kontrak' => $kontrak ?? 0,
                'tahun_anggaran' => 2025,
                'status' => $status,
                'progress' => $progress,
                'tanggal_mulai' => now()->subDays(random_int(30, 200)),
                'tanggal_selesai' => now()->addDays(random_int(10, 250)),
                'lokasi' => 'Banjarnegara',
                'keterangan' => 'Paket pengadaan tahun anggaran 2025',
            ]);

            // Tahapan otomatis sesuai status
            $tahapanList = ['Persiapan', 'Pemilihan', 'Kontrak', 'Pelaksanaan', 'Serah Terima'];
            $currentIdx = array_search($status, ['draft', 'persiapan', 'pemilihan', 'kontrak', 'pelaksanaan', 'selesai']);
            foreach ($tahapanList as $idx => $namaTahap) {
                $tahapStatus = 'belum';
                if ($currentIdx !== false && $idx < $currentIdx) {
                    $tahapStatus = 'selesai';
                } elseif ($currentIdx !== false && $idx == $currentIdx) {
                    $tahapStatus = 'proses';
                }
                Tahapan::create([
                    'paket_id' => $paket->id,
                    'nama_tahap' => $namaTahap,
                    'urutan' => $idx + 1,
                    'status' => $tahapStatus,
                    'tanggal_rencana' => now()->addDays(($idx - 2) * 30),
                    'tanggal_aktual' => $tahapStatus == 'selesai' ? now()->subDays(random_int(5, 60)) : null,
                ]);
            }
        }

        // ===================== EVALUASI =====================
        $evalData = [
            ['POKJA-2025-002', 'PT Adhi Karya Persada', 'kualifikasi', 88.50, 'lolos'],
            ['POKJA-2025-002', 'PT Wijaya Kusuma Konstruksi', 'kualifikasi', 82.00, 'lolos'],
            ['POKJA-2025-002', 'CV Sinar Teknik', 'kualifikasi', 65.00, 'gugur'],
            ['POKJA-2025-002', 'PT Adhi Karya Persada', 'harga', 92.30, 'lolos'],
            ['POKJA-2025-002', 'PT Wijaya Kusuma Konstruksi', 'harga', 85.70, 'gugur'],
            ['POKJA-2025-004', 'PT Wijaya Kusuma Konstruksi', 'kualifikasi', 90.00, 'lolos'],
            ['POKJA-2025-004', 'PT Wijaya Kusuma Konstruksi', 'harga', 89.50, 'lolos'],
            ['POKJA-2025-009', 'PT Wijaya Kusuma Konstruksi', 'kualifikasi', 86.00, 'lolos'],
            ['POKJA-2025-009', 'PT Wijaya Kusuma Konstruksi', 'harga', 88.20, 'lolos'],
        ];

        foreach ($evalData as [$kode, $penyedia, $jenis, $skor, $hasil]) {
            Evaluasi::create([
                'paket_id' => PaketPengadaan::where('kode_paket', $kode)->value('id'),
                'penyedia_id' => $penyediaIds[$penyedia],
                'jenis' => $jenis,
                'skor' => $skor,
                'hasil' => $hasil,
                'tanggal' => now()->subDays(random_int(10, 90)),
                'catatan' => $hasil == 'lolos' ? 'Memenuhi syarat administrasi dan teknis' : 'Tidak memenuhi kualifikasi',
            ]);
        }

        $this->command->info('✓ Seeder selesai! Login: admin@banjarnegara.go.id / password');

        // ===== POKJA / MONITORING KINERJA =====
        $this->call(PokjaSeeder::class);
        $this->call(SimulasiKinerjaSeeder::class);
    }
}
