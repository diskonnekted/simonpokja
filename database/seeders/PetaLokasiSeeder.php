<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaketPengadaan;
use App\Services\LokasiResolver;

/**
 * PetaLokasiSeeder
 * Mengisi koordinat (latitude/longitude) + desa/kecamatan untuk semua paket.
 * 
 * Aturan:
 * - Paket dengan lokasi spesifik (mengandung nama desa/kecamatan nyata) di-resolve
 *   ke centroid wilayah tersebut (presisi desa/kecamatan).
 * - Paket dengan lokasi generik ("Kabupaten Banjarnegara" dsb.) disebarkan
 *   deterministik (hash kode paket) ke titik desa acak-stabil + jitter.
 * Idempotent: updateOrCreate berbasis kode paket, aman dijalankan ulang.
 */
class PetaLokasiSeeder extends Seeder
{
    public function run(): void
    {
        $desas = LokasiResolver::desaList();
        $kecs = LokasiResolver::kecamatanList();

        if (empty($desas) || empty($kecs)) {
            $this->command->error('Geojson tidak ditemukan di public/. Seeder dilewati.');

            return;
        }

        $this->command->line('  - Referensi wilayah: ' . count($kecs) . ' kecamatan, ' . count($desas) . ' desa');

        $dipetakan = 0;
        $sebar = 0;

        foreach (PaketPengadaan::all() as $p) {
            $teks = LokasiResolver::normalisasi($p->lokasi ?? '');

            $desaKetemu = null;
            $kecKetemu = null;

            // Coba cocokkan desa dulu (nama desa saja, hindari "kabupaten X")
            if ($teks !== '') {
                foreach ($desas as $d) {
                    if (str_contains($teks, LokasiResolver::normalisasi($d['nama']))) {
                        $desaKetemu = $d;
                        break;
                    }
                }
                // Lalu kecamatan — cocokkan nama kecamatan TEPAT sebagai kata
                // (mencegah "kabupaten banjarnegara" cocok dengan kecamatan "banjarnegara")
                if (! $desaKetemu) {
                    foreach ($kecs as $k) {
                        if (preg_match('/\b' . preg_quote(LokasiResolver::normalisasi($k['nama']), '/') . '\b/u', $teks)
                            && stripos($p->lokasi ?? '', 'kabupaten') === false) {
                            $kecKetemu = $k;
                            break;
                        }
                    }
                }
            }

            // PERBAIKAN: centroid geojson berbentuk [lng, lat] — jangan ditukar
            if ($desaKetemu) {
                $lng = $desaKetemu['centroid'][0];
                $lat = $desaKetemu['centroid'][1];
                [$jLat, $jLng] = $this->jitter($p->kode_paket, 0.008);
                $p->update([
                    'latitude' => round($lat + $jLat, 6),
                    'longitude' => round($lng + $jLng, 6),
                    'desa' => $desaKetemu['nama'],
                    'kecamatan' => $desaKetemu['kecamatan'],
                ]);
                $dipetakan++;
            } elseif ($kecKetemu) {
                $lng = $kecKetemu['centroid'][0];
                $lat = $kecKetemu['centroid'][1];
                [$jLat, $jLng] = $this->jitter($p->kode_paket, 0.015);
                $p->update([
                    'latitude' => round($lat + $jLat, 6),
                    'longitude' => round($lng + $jLng, 6),
                    'desa' => null,
                    'kecamatan' => $kecKetemu['nama'],
                ]);
                $dipetakan++;
            } else {
                // Lokasi generik: sebar deterministik ke desa acak-stabil
                [$lat, $lng] = LokasiResolver::sebarDeterministik($p->kode_paket);
                // Pilih data desa yang sama dengan hasil sebar (untuk label)
                $h = crc32($p->kode_paket);
                $desa = $desas[$h % count($desas)];
                $p->update([
                    'latitude' => round($lat, 6),
                    'longitude' => round($lng, 6),
                    'desa' => $desa['nama'],
                    'kecamatan' => $desa['kecamatan'],
                ]);
                $sebar++;
            }
        }

        $this->command->info("  - PetaLokasiSeeder: {$dipetakan} paket dipetakan dari teks lokasi, {$sebar} disebarkan deterministik");
    }

    /** Jitter deterministik dari kode paket. */
    private function jitter(string $kode, float $radius): array
    {
        $h = crc32($kode);

        return [
            ((($h >> 5) % 1000) / 1000 - 0.5) * 2 * $radius,
            ((($h >> 13) % 1000) / 1000 - 0.5) * 2 * $radius,
        ];
    }
}
