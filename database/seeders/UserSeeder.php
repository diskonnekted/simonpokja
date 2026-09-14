<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Pokja;
use App\Models\User;
use App\Models\PaketPengadaan;
use App\Models\ProgresPekerjaan;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ===== ADMIN =====
        User::updateOrCreate(
            ['email' => 'admin@banjarnegara.go.id'],
            [
                'name' => 'Administrator Pokja',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'pokja_id' => null,
                'email_verified_at' => now(),
            ]
        );

        // ===== 5 AKUN POKJA =====
        $pokjas = Pokja::orderBy('id')->get();

        foreach ($pokjas as $i => $pokja) {
            $n = $i + 1;
            User::updateOrCreate(
                ['email' => "pokja{$n}@banjarnegara.go.id"],
                [
                    'name' => 'Pokja ' . preg_replace('/^Pokja\s*/i', '', (string) $pokja->nama) ?: "Ke-{$n}",
                    'password' => Hash::make('password'),
                    'role' => 'pokja',
                    'pokja_id' => $pokja->id,
                    'email_verified_at' => now(),
                ]
            );
        }

        // ===== DUMMY PROGRES PEKERJAAN (riwayat realistis) =====
        // Hanya untuk paket aktif milik masing-masing Pokja, progress konsisten naik.
        $catatanContoh = [
            'Dokumen persiapan telah direview, menunggu persetujuan PPJK.',
            'Evaluasi penawaran selesai, berkas lanjut ke tahap klarifikasi.',
            'Rapat panitia terlaksana, keputusan dituangkan dalam berita acara.',
            'Koordinasi dengan OPD pengguna barang terkait penyesuaian lingkup pekerjaan.',
            'Monitoring lapangan: pelaksanaan sesuai jadwal, tidak ada kendala.',
            'Terdapat keterlambatan pengiriman barang, sudah dikirim surat peringatan.',
            'Serah terima barang diselesaikan sebagian, sisa menunggu pengadaan tambahan.',
        ];

        foreach ($pokjas as $i => $pokja) {
            $user = User::where('email', 'pokja' . ($i + 1) . '@banjarnegara.go.id')->first();
            if (! $user) {
                continue;
            }

            ProgresPekerjaan::where('user_id', $user->id)->delete();

            $paketIds = $pokja->pakets()
                ->whereNotIn('status', ['draft', 'batal'])
                ->pluck('id')
                ->take(4);

            foreach ($paketIds as $j => $paketId) {
                $paket = PaketPengadaan::find($paketId);
                if (! $paket) {
                    continue;
                }

                // Bangun riwayat 2-3 entri yang berakhir di progres terkini paket
                $progresAkhir = (int) $paket->progress;
                $langkah = max(1, (int) ceil($progresAkhir / 3));
                $riwayat = [];
                for ($k = 1; $langkah * $k < $progresAkhir && count($riwayat) < 2; $k++) {
                    $riwayat[] = min($langkah * $k, 99);
                }
                $riwayat[] = $progresAkhir;

                foreach ($riwayat as $k => $nilai) {
                    $isTerakhir = ($k === count($riwayat) - 1);
                    $statusItem = $isTerakhir ? $paket->status : ($paket->status === 'selesai' ? 'pelaksanaan' : $paket->status);
                    ProgresPekerjaan::create([
                        'paket_id' => $paketId,
                        'user_id' => $user->id,
                        'progress' => min(100, max(0, $nilai)),
                        'status' => $statusItem,
                        'catatan' => $catatanContoh[($i + $j + $k) % count($catatanContoh)],
                        'created_at' => now()->subDays(21 - $k * 7),
                        'updated_at' => now()->subDays(21 - $k * 7),
                    ]);
                }
            }
        }
    }
}
