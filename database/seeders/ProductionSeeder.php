<?php

namespace Database\Seeders;

use App\Models\Pokja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder khusus PRODUKSI.
 *
 * Hanya membuat data master yang benar-benar diperlukan untuk mulai operasional
 * (5 Pokja + akun admin + 5 akun Pokja). TANPA data dummy paket/kinerja/sanggahan.
 *
 * Jalankan pada produksi:
 *   php artisan db:seed --class=ProductionSeeder --force
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Menyiapkan master Pokja & akun login (produksi)...');

        // ===== 5 POKJA LPSE BANJARNEGARA =====
        $pokja = [
            [
                'bidang' => 'Barang & e-Purchasing',
                'ketua' => 'Drs. Hendra Wijaya, M.Si.',
                'nip_ketua' => '19710512 199803 1 004',
                'anggota' => ['Sri Mulyani, S.E.', 'Agus Priyanto, S.T.', 'Dewi Lestari, S.Sos.', 'Bambang Sutrisno'],
                'kapasitas_ideal' => 5,
            ],
            [
                'bidang' => 'Jasa Konsultansi',
                'ketua' => 'Ir. Slamet Santoso, M.T.',
                'nip_ketua' => '19680422 199501 1 002',
                'anggota' => ['Rina Kartika, S.T.', 'Joko Purnomo, S.E.', 'Ahmad Dahlan, S.H.'],
                'kapasitas_ideal' => 5,
            ],
            [
                'bidang' => 'Pekerjaan Konstruksi',
                'ketua' => 'Dr. Endang Sutrisna, M.M.',
                'nip_ketua' => '19750930 200312 1 009',
                'anggota' => ['Fitri Handayani, S.E., M.Si.', 'Gunawan Wibisono, S.T.', 'Lia Ambarwati, S.Sos.', 'Prasetyo Adi, S.T.', 'Nur Hidayat'],
                'kapasitas_ideal' => 6,
            ],
            [
                'bidang' => 'Jasa Lainnya',
                'ketua' => 'Sumardi, S.Sos., M.A.P.',
                'nip_ketua' => '19820214 201001 1 011',
                'anggota' => ['Wulan Sari, S.E.', 'Taufik Hidayat, S.T.', 'Dian Puspita, S.E.'],
                'kapasitas_ideal' => 5,
            ],
            [
                'bidang' => 'Swakelola & Pengadaan Terintegrasi',
                'ketua' => 'Hj. Siti Aminah, M.M.',
                'nip_ketua' => '19790815 200604 2 007',
                'anggota' => ['Rudi Hartanto, S.T.', 'Maya Anggraeni, S.E.', 'Hendra Gunawan'],
                'kapasitas_ideal' => 4,
            ],
        ];

        $pokjaIds = [];
        foreach ($pokja as $i => $p) {
            $model = Pokja::updateOrCreate(
                ['nama' => 'Pokja ' . ['I', 'II', 'III', 'IV', 'V'][$i]],
                array_merge($p, ['aktif' => true])
            );
            $pokjaIds[] = $model->id;
        }

        // ===== AKUN ADMIN =====
        $adminPassword = env('ADMIN_DEFAULT_PASSWORD', 'password');
        User::updateOrCreate(
            ['email' => 'admin@banjarnegara.go.id'],
            [
                'name' => 'Administrator Pokja',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'pokja_id' => null,
                'email_verified_at' => now(),
            ]
        );

        // ===== 5 AKUN POKJA =====
        foreach ($pokjaIds as $i => $pokjaId) {
            $n = $i + 1;
            User::updateOrCreate(
                ['email' => "pokja{$n}@banjarnegara.go.id"],
                [
                    'name' => 'Pokja ' . ['I', 'II', 'III', 'IV', 'V'][$i],
                    'password' => Hash::make($adminPassword),
                    'role' => 'pokja',
                    'pokja_id' => $pokjaId,
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info('âœ“ ProductionSeeder selesai: 5 Pokja + 1 admin + 5 akun Pokja.');
        $this->command->warn('Catatan: akun memakai password default "' . $adminPassword . '" — segera ubah setelah login.');
    }
}