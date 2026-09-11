<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaketPengadaan;
use App\Models\Opd;
use App\Models\Pokja;
use Carbon\Carbon;

/**
 * Dummy pekerjaan dari data ASLI SIRUP Inaproc (sirup.inaproc.id).
 * Sumber: datatablectr/dataruppenyediasatker
 *   - Satker 169447 -> Dinas Kesehatan (paket Puskesmas: Punggelan, Susukan, Wanayasa, Wanadadi, Bawang)
 *   - Satker 169424 -> Sekretariat Daerah (belanja umum kantor)
 *   - Satker 169275 -> Bappeda ("Bapperida/Baperlitbang Banjarnegara")
 * Pemetaan metode: E-Purchasing=epurchasing, Pengadaan Langsung=pengadaan_langsung,
 *                  Dikecualikan=penunjukan_langsung (pendekatan terdekat)
 * Sumber dana BLUD dipetakan ke 'lainnya' (enum DB belum punya BLUD).
 */
class SirupDummySeeder extends Seeder
{
    public function run(): void
    {
        $opd = [
            'Dinkes' => Opd::where('singkatan', 'Dinkes')->value('id'),
            'Setda'  => Opd::where('singkatan', 'Setda')->value('id'),
            'Bappeda'=> Opd::where('singkatan', 'Bappeda')->value('id'),
        ];

        $pokja = [
            'I'  => Pokja::where('nama', 'Pokja I')->value('id'),   // barang & e-purchasing
            'IV' => Pokja::where('nama', 'Pokja IV')->value('id'),  // jasa lainnya / dikecualikan
        ];

        // [kode_rup, nama, pagu, metode, sumber_dana, opd, pokja, jenis, status, bulan_mulai (Y-m)]
        $rows = [
            // ===== Satker 169447 (Dinkes) TA 2026 =====
            ['67783410', 'Pengadaan Alat rumah tangga lainnya Mandiraja 2', 46_000_000, 'epurchasing', 'lainnya', 'Dinkes', 'I', 'barang', 'persiapan', '2026-01'],
            ['67768089', 'Pengadaan Belanja Modal Alat Laboratorium Umum Puskesmas Susukan 1', 60_000_000, 'epurchasing', 'lainnya', 'Dinkes', 'I', 'barang', 'persiapan', '2026-01'],
            ['67768077', 'Pengadaan Belanja Modal Mebel Puskesmas Susukan 1', 37_000_000, 'epurchasing', 'lainnya', 'Dinkes', 'I', 'barang', 'pemilihan', '2026-01'],
            ['67768045', 'Pengadaan Belanja Modal Alat Kedokteran Gigi Puskesmas Susukan 1', 12_122_470, 'pengadaan_langsung', 'lainnya', 'Dinkes', 'I', 'barang', 'draft', '2026-01'],
            ['67768056', 'Pengadaan Belanja Modal Personal Komputer Puskesmas Susukan 1', 27_000_000, 'epurchasing', 'lainnya', 'Dinkes', 'I', 'barang', 'pemilihan', '2026-01'],
            // ===== Satker 169447 (Dinkes) TA 2025 =====
            ['62522373', 'Belanja Makanan dan Minuman BLUD UPTD Puskesmas Punggelan 2', 257_400_000, 'epurchasing', 'lainnya', 'Dinkes', 'I', 'barang', 'selesai', '2025-01'],
            ['62504470', 'Pengadaan Obat-obatan BLUD UPTD Puskesmas Wanayasa 1', 9_700_000, 'pengadaan_langsung', 'lainnya', 'Dinkes', 'I', 'barang', 'selesai', '2025-02'],
            ['62350325', 'Belanja Jasa Tenaga Ahli, Jasa Audit/Surveillance ISO, Jasa Kalibrasi, Jasa Pengolahan Sampah Medis dan Non Medis Puskesmas Bawang 2', 55_750_000, 'pengadaan_langsung', 'lainnya', 'Dinkes', 'IV', 'jasa_lainnya', 'selesai', '2025-01'],
            ['62350534', 'Belanja Alat/Bahan Perabot Kantor dan Kegiatan Kantor lainnya Puskesmas Bawang 2', 37_380_800, 'pengadaan_langsung', 'lainnya', 'Dinkes', 'I', 'barang', 'pelaksanaan', '2025-01'],
            // ===== Satker 169424 (Setda) TA 2026 =====
            ['67543958', 'Belanja Hibah Barang kepada Badan dan Lembaga Nirlaba, Sukarela dan Sosial yang Telah Memiliki Surat Keterangan Terdaftar', 54_879_000, 'epurchasing', 'apbd', 'Setda', 'IV', 'barang', 'persiapan', '2026-06'],
            ['65830765', 'Pengadaan Bahan-Bahan Bangunan dan Konstruksi', 251_795_700, 'epurchasing', 'apbd', 'Setda', 'I', 'barang', 'pemilihan', '2026-02'],
            ['65830811', 'Pengadaan Natura dan Pakan-Natura', 25_900_000, 'epurchasing', 'apbd', 'Setda', 'I', 'barang', 'persiapan', '2026-07'],
            ['65053898', 'Pembayaran Tagihan Listrik', 48_000_000, 'penunjukan_langsung', 'apbd', 'Setda', 'IV', 'jasa_lainnya', 'draft', '2026-01'],
            // ===== Satker 169424 (Setda) TA 2025 =====
            ['61944666', 'Belanja Bahan-Bahan Bangunan dan Konstruksi', 220_175_300, 'epurchasing', 'apbd', 'Setda', 'I', 'barang', 'selesai', '2025-03'],
            ['61346636', 'Belanja Makanan dan Minuman Aktivitas Lapangan', 276_800_000, 'epurchasing', 'apbd', 'Setda', 'I', 'barang', 'selesai', '2025-01'],
            ['61168706', 'Belanja Hibah Barang kepada Badan dan Lembaga Nirlaba, Sukarela Bersifat Sosial Kemasyarakatan', 90_000_000, 'pengadaan_langsung', 'apbd', 'Setda', 'IV', 'barang', 'selesai', '2025-09'],
            // ===== Satker 169275 (Bappeda) TA 2026 =====
            ['67307285', 'PEKAN INOVASI DAN BUDAYA DAERAH (Banjarnegara Innovation And Culture Week)', 40_000_000, 'epurchasing', 'apbd', 'Bappeda', 'IV', 'jasa_lainnya', 'persiapan', '2026-05'],
            ['66402430', 'Belanja Alat/Bahan untuk Kegiatan Kantor- Bahan Cetak pada Bapperida Banjarnegara', 89_371_600, 'epurchasing', 'apbd', 'Bappeda', 'I', 'barang', 'pemilihan', '2026-01'],
            // ===== Satker 169275 (Bappeda) TA 2025 =====
            ['60390423', 'Belanja Makanan dan minuman Rapat pada Bidang Pemkesos Baperlitbang Banjarnegara', 122_400_000, 'epurchasing', 'apbd', 'Bappeda', 'I', 'barang', 'selesai', '2025-01'],
            ['60382714', 'Belanja Alat Tulis Kantor, Kertas Cover, Cetak, dan Bahan Komputer pada Bidang Litbang PP Baperlitbang Banjarnegara', 87_301_900, 'epurchasing', 'apbd', 'Bappeda', 'I', 'barang', 'pelaksanaan', '2025-01'],
        ];

        $progressMap = ['draft' => 0, 'persiapan' => 5, 'pemilihan' => 12, 'kontrak' => 25, 'pelaksanaan' => 65, 'selesai' => 100];
        $tahapMap = ['draft' => 'persiapan', 'persiapan' => 'persiapan', 'pemilihan' => 'pengumuman', 'kontrak' => 'evaluasi', 'pelaksanaan' => 'penetapan', 'selesai' => 'penetapan'];

        foreach ($rows as [$rup, $nama, $pagu, $metode, $sumber, $opdKey, $pokjaKey, $jenis, $status, $bulan]) {
            $mulai = Carbon::parse($bulan . '-15');
            $selesai = (clone $mulai)->addMonths(3);

            // Kontrak: paket yang sudah jalan/selesai dapat nilai (efisiensi 92-96%)
            $kontrak = in_array($status, ['kontrak', 'pelaksanaan', 'selesai'])
                ? (int) round($pagu * [0.92, 0.94, 0.96][array_rand([0.92, 0.94, 0.96])])
                : 0;

            PaketPengadaan::updateOrCreate(
                ['kode_paket' => "RUP-{$rup}"],
                [
                    'nama_paket' => $nama,
                    'opd_id' => $opd[$opdKey],
                    'pokja_id' => $pokja[$pokjaKey],
                    'penyedia_id' => null,
                    'jenis' => $jenis,
                    'metode' => $metode,
                    'sumber_dana' => $sumber,
                    'pagu' => $pagu,
                    'hps' => (int) round($pagu * 0.98),
                    'nilai_kontrak' => $kontrak,
                    'tahun_anggaran' => (int) $mulai->format('Y'),
                    'status' => $status,
                    'risiko' => $pagu >= 200_000_000 ? 'sedang' : 'rendah',
                    'tender_gagal' => false,
                    'tahap_tender' => $tahapMap[$status],
                    'progress' => $progressMap[$status],
                    'tanggal_mulai' => $mulai,
                    'tanggal_selesai' => $selesai,
                    'lokasi' => 'Kabupaten Banjarnegara',
                    'keterangan' => 'Data dummy uji coba — diambil dari SIRUP Inaproc (sirup.inaproc.id), kode RUP asli.',
                ]
            );
        }

        $this->command->info('✓ SirupDummySeeder: 20 paket dummy dari SIRUP (Dinkes/Setda/Bappeda) tersimpan');
    }
}
