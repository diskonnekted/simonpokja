<?php

namespace App\Http\Controllers;

use App\Models\PaketPengadaan;
use App\Models\Pokja;
use App\Models\Opd;
use App\Services\LokasiResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PetaController extends Controller
{
    /**
     * Halaman dasbor peta kegiatan.
     */
    public function index()
    {
        $tahun = (int) request('tahun', date('Y'));

        return view('peta.index', [
            'tahun' => $tahun,
            'listTahun' => PaketPengadaan::select('tahun_anggaran')->distinct()->orderByDesc('tahun_anggaran')->pluck('tahun_anggaran'),
            'listOpd' => Opd::orderBy('singkatan')->get(['id', 'nama', 'singkatan']),
            'listPokja' => Pokja::orderBy('id')->get(['id', 'nama', 'bidang']),
        ]);
    }

    /**
     * API: seluruh paket (tahun tertentu) lengkap koordinat & atribut popup.
     */
    public function paket(Request $request): JsonResponse
    {
        $tahun = (int) $request->query('tahun', date('Y'));

        $pakets = PaketPengadaan::query()
            ->with(['opd:id,nama,singkatan', 'pokja:id,nama,bidang', 'penyedia:id,nama'])
            ->where('tahun_anggaran', $tahun)
            ->orderBy('kode_paket')
            ->get();

        $fitur = $pakets->map(function (PaketPengadaan $p) {
            [$lat, $lng, $presisi] = LokasiResolver::resolve($p->only([
                'kode_paket', 'lokasi', 'latitude', 'longitude', 'desa', 'kecamatan',
            ]));

            return [
                'id' => $p->id,
                'kode' => $p->kode_paket,
                'nama' => $p->nama_paket,
                'opd' => $p->opd?->nama,
                'opd_singkatan' => $p->opd?->singkatan,
                'opd_id' => $p->opd_id,
                'pokja' => $p->pokja?->nama,
                'pokja_id' => $p->pokja_id,
                'penyedia' => $p->penyedia?->nama,
                'jenis' => $p->jenis,
                'metode' => $p->metode,
                'status' => $p->status,
                'status_label' => $p->status_label,
                'risiko' => $p->risiko,
                'tender_gagal' => (bool) $p->tender_gagal,
                'pagu' => (int) $p->pagu,
                'progres' => (int) $p->progress,
                'progres_tahapan' => $p->progres_persen,
                'tahapan' => $p->tahapan_progres,
                'lokasi_teks' => $p->lokasi,
                'desa' => $p->desa,
                'kecamatan' => $p->kecamatan,
                'lat' => round($lat, 6),
                'lng' => round($lng, 6),
                'presisi' => $presisi,
                'url_detail' => route('pemantauan.show', $p),
            ];
        });

        return response()->json([
            'tahun' => $tahun,
            'jumlah' => $fitur->count(),
            'pusat' => LokasiResolver::PUSAT,
            'data' => $fitur,
        ]);
    }

    /**
     * API: statistik ringkas untuk strip di atas peta.
     */
    public function statistik(Request $request): JsonResponse
    {
        $tahun = (int) $request->query('tahun', date('Y'));
        $q = PaketPengadaan::where('tahun_anggaran', $tahun);

        return response()->json([
            'total' => (clone $q)->count(),
            'pagu' => (clone $q)->sum('pagu'),
            'kritis' => (clone $q)->where('risiko', 'kritis')->count(),
            'gagal' => (clone $q)->where('tender_gagal', true)->count(),
            'selesai' => (clone $q)->where('status', 'selesai')->count(),
        ]);
    }
}
