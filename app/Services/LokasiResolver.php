<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Resolusi lokasi paket pengadaan ke titik peta.
 *
 * Prioritas:
 *  1. Koordinat eksplisit (latitude/longitude terisi).
 *  2. Kecocokan nama desa pada teks lokasi dengan geojson desa.
 *  3. Kecocokan nama kecamatan pada teks lokasi dengan geojson kecamatan.
 *  4. Penyebaran deterministik ke wilayah kabupaten (fallback simulasi),
 *     memakai hash kode paket agar hasil stabil antar pemanggilan.
 */
class LokasiResolver
{
    /** Pusat kabupaten Banjarnegara */
    public const PUSAT = [-7.4256, 109.6916];

    public static function kecamatanList(): array
    {
        return Cache::remember('peta_kecamatan_list', 3600, function () {
            $file = public_path('peta_kecamatan.geojson');
            if (! is_file($file)) {
                return [];
            }
            $json = json_decode(file_get_contents($file));

            return collect($json->features ?? [])
                ->map(fn ($f) => [
                    'nama' => $f->properties->Kecamatan ?? null,
                    'centroid' => self::centroid($f->geometry),
                ])
                ->filter(fn ($k) => $k['nama'] && $k['centroid'])
                ->values()
                ->toArray();
        });
    }

    public static function desaList(): array
    {
        return Cache::remember('peta_desa_list', 3600, function () {
            $file = public_path('peta_desa_v3.geojson');
            if (! is_file($file)) {
                return [];
            }
            $json = json_decode(file_get_contents($file));

            return collect($json->features ?? [])
                ->map(function ($f) {
                    $nama = $f->properties->Nama_Desa_ ?? $f->properties->Nama_Desa ?? null;
                    $kec = $f->properties->Kecamatan ?? null;
                    $c = self::centroid($f->geometry);

                    return $nama && $kec && $c
                        ? ['nama' => $nama, 'kecamatan' => $kec, 'centroid' => $c]
                        : null;
                })
                ->filter()
                ->values()
                ->toArray();
        });
    }

    /**
     * Resolusi satu paket menjadi [lat, lng, presisi].
     * presisi: 'tepat' (koordinat manual tanpa label wilayah), 'desa', 'kecamatan', 'sebar' (fallback).
     * Catatan: seeder PetaLokasiSeeder selalu mengisi desa/kecamatan asal titik,
     * sehingga presisi dihitung dari label wilayah tersebut (bukan sekadar ada koordinat).
     */
    public static function resolve(array $paket): array
    {
        $punyaKoordinat = ! empty($paket['latitude']) && ! empty($paket['longitude']);

        // 1. Label wilayah tersimpan (diisi seeder/pendataan) menentukan presisi
        if (! empty($paket['desa'])) {
            $titik = $punyaKoordinat
                ? [(float) $paket['latitude'], (float) $paket['longitude']]
                : self::cariDesa($paket['desa']);
            if ($titik) {
                return [$titik[0], $titik[1], 'desa'];
            }
        }
        if (! empty($paket['kecamatan'])) {
            $titik = $punyaKoordinat
                ? [(float) $paket['latitude'], (float) $paket['longitude']]
                : self::cariKecamatan($paket['kecamatan']);
            if ($titik) {
                return [$titik[0], $titik[1], 'kecamatan'];
            }
        }

        // 2. Koordinat eksplisit tanpa label wilayah = titik tepat manual
        if ($punyaKoordinat) {
            return [(float) $paket['latitude'], (float) $paket['longitude'], 'tepat'];
        }

        // 3. Resolusi dari teks lokasi
        $teks = self::normalisasi($paket['lokasi'] ?? '');
        if ($teks !== '' && ! str_contains($teks, 'kabupaten') && $teks !== 'banjarnegara') {
            foreach (self::desaList() as $d) {
                if (str_contains($teks, self::normalisasi($d['nama']))) {
                    return [$d['centroid'][1], $d['centroid'][0], 'desa'];
                }
            }
            foreach (self::kecamatanList() as $k) {
                if (preg_match('/\b' . preg_quote(self::normalisasi($k['nama']), '/') . '\b/u', $teks)) {
                    return [$k['centroid'][1], $k['centroid'][0], 'kecamatan'];
                }
            }
        }

        // 4. Penyebaran deterministik berdasar kode paket
        return self::sebarDeterministik($paket['kode_paket'] ?? 'x');
    }

    private static function cariDesa(string $nama): ?array
    {
        $kunci = self::normalisasi($nama);
        foreach (self::desaList() as $d) {
            if (self::normalisasi($d['nama']) === $kunci) {
                return [$d['centroid'][1], $d['centroid'][0]];
            }
        }

        return null;
    }

    private static function cariKecamatan(string $nama): ?array
    {
        $kunci = self::normalisasi($nama);
        foreach (self::kecamatanList() as $k) {
            if (self::normalisasi($k['nama']) === $kunci) {
                return [$k['centroid'][1], $k['centroid'][0]];
            }
        }

        return null;
    }

    /**
     * Titik deterministik dari hash kode paket: pilih desa acak-stabil,
     * lalu beri jitter kecil di sekitar centroidnya agar marker tidak menumpuk.
     */
    public static function sebarDeterministik(string $kode): array
    {
        $desas = self::desaList();
        if (empty($desas)) {
            return [self::PUSAT[0], self::PUSAT[1], 'sebar'];
        }

        $h = crc32($kode);
        $desa = $desas[$h % count($desas)];

        // Jitter ~ hingga ±0.012 derajat (~1.3 km) dari centroid desa
        $jLat = ((($h >> 8) % 1000) / 1000 - 0.5) * 0.024;
        $jLng = ((($h >> 16) % 1000) / 1000 - 0.5) * 0.024;

        return [
            $desa['centroid'][1] + $jLat,
            $desa['centroid'][0] + $jLng,
            'sebar',
        ];
    }

    /** Normalisasi teks: huruf kecil, hapus gelar wilayah & tanda baca. */
    public static function normalisasi(string $teks): string
    {
        $teks = mb_strtolower(trim($teks));
        $teks = str_replace(['kel.', 'kel ', 'desa ', 'dist.', 'dist ', 'kec. ', 'kecamatan '], ' ', $teks);
        $teks = preg_replace('/[^a-z0-9\s]/', ' ', $teks);

        return preg_replace('/\s+/', ' ', trim($teks));
    }

    /**
     * Centroid geometry (mendukung Polygon, MultiPolygon, GeometryCollection).
     * Return [lng, lat] sesuai konvensi geojson. Null jika struktur tak valid.
     */
    public static function centroid(object $geometry): ?array
    {
        $ring = match ($geometry->type ?? '') {
            'Polygon' => self::ringPertama($geometry->coordinates ?? null, 1),
            'MultiPolygon' => self::ringPertama($geometry->coordinates ?? null, 2),
            'GeometryCollection' => self::centroidDariKoleksi($geometry->geometries ?? []),
            default => null,
        };

        if (empty($ring)) {
            return null;
        }

        $x = 0;
        $y = 0;
        $n = 0;
        foreach ($ring as $pt) {
            if (is_array($pt) && isset($pt[0], $pt[1]) && is_numeric($pt[0]) && is_numeric($pt[1])) {
                $x += $pt[0];
                $y += $pt[1];
                $n++;
            }
        }

        return $n ? [$x / $n, $y / $n] : null;
    }

    /**
     * Ambil ring terluar pertama dengan turun kedalaman nested array
     * hingga menemukan daftar titik [lng, lat].
     */
    private static function ringPertama(mixed $coordinates, int $kedalaman): ?array
    {
        $cur = $coordinates;
        for ($i = 0; $i < $kedalaman && is_array($cur); $i++) {
            $cur = $cur[0] ?? null;
        }

        return is_array($cur) ? $cur : null;
    }

    private static function centroidDariKoleksi(array $geometries): ?array
    {
        foreach ($geometries as $g) {
            if (in_array($g->type ?? '', ['Polygon', 'MultiPolygon'])) {
                $c = self::centroid($g);
                if ($c) {
                    return $c;
                }
            }
        }

        return null;
    }
}
