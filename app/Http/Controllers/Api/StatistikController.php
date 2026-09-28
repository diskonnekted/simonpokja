<?php

namespace App\Http\Controllers\Api;

use App\Models\AlertAnomali;
use App\Models\Opd;
use App\Models\PaketPengadaan;
use App\Models\Penyedia;
use App\Models\Pokja;
use App\Models\Sanggahan;
use Illuminate\Http\Request;

class StatistikController extends ApiController
{
    /**
     * GET /api/v1/ping — uji koneksi & validitas token.
     */
    public function ping()
    {
        return $this->ok([
            'aplikasi' => config('sibijak.app'),
            'framework' => 'Laravel ' . app()->version(),
            'waktu_server' => now()->toIso8601String(),
            'zona_waktu' => config('app.timezone'),
        ], 'API aktif — token valid.');
    }

    /**
     * GET /api/v1/statistik — agregat ringkas (opsional filter ?tahun=).
     */
    public function statistik(Request $request)
    {
        $tahun = $request->filled('tahun') ? (int) $request->tahun : null;

        $dasar = PaketPengadaan::query();
        if ($tahun) {
            $dasar->tahun($tahun);
        }

        $status = (clone $dasar)->selectRaw('status, COUNT(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');
        $jenis = (clone $dasar)->selectRaw('jenis, COUNT(*) as jumlah')->groupBy('jenis')->pluck('jumlah', 'jenis');
        $sumberDana = (clone $dasar)->selectRaw('sumber_dana, COUNT(*) as jumlah')->groupBy('sumber_dana')->pluck('jumlah', 'sumber_dana');
        $risiko = (clone $dasar)->selectRaw('risiko, COUNT(*) as jumlah')->groupBy('risiko')->pluck('jumlah', 'risiko');
        $perOpd = (clone $dasar)->selectRaw('opd_id, COUNT(*) as jumlah')->groupBy('opd_id')->pluck('jumlah', 'opd_id');

        $total = (clone $dasar)->count();
        $pagu = (int) (clone $dasar)->sum('pagu');
        $hps = (int) (clone $dasar)->sum('hps');
        $kontrak = (int) (clone $dasar)->sum('nilai_kontrak');
        $tenderGagal = (clone $dasar)->where('tender_gagal', 1)->count();

        $sanggahan = Sanggahan::query();
        $alert = AlertAnomali::query();
        if ($tahun) {
            $sanggahan->whereHas('paket', fn ($q) => $q->tahun($tahun));
            $alert->whereHas('paket', fn ($q) => $q->tahun($tahun));
        }

        return $this->ok([
            'tahun' => $tahun,
            'total_paket' => $total,
            'total_pagu' => $pagu,
            'total_hps' => $hps,
            'total_nilai_kontrak' => $kontrak,
            'total_tender_gagal' => $tenderGagal,
            'berdasarkan_status' => $status,
            'berdasarkan_jenis' => $jenis,
            'berdasarkan_sumber_dana' => $sumberDana,
            'berdasarkan_risiko' => $risiko,
            'berdasarkan_opd' => $perOpd,
            'total_opd' => Opd::count(),
            'total_penyedia' => Penyedia::count(),
            'total_pokja' => Pokja::count(),
            'total_pokja_aktif' => Pokja::where('aktif', 1)->count(),
            'total_sanggahan' => (clone $sanggahan)->count(),
            'sanggahan_berdasarkan_hasil' => (clone $sanggahan)->selectRaw('hasil, COUNT(*) as jumlah')->groupBy('hasil')->pluck('jumlah', 'hasil'),
            'total_alert_anomali' => (clone $alert)->count(),
            'alert_berdasarkan_tingkat' => (clone $alert)->selectRaw('tingkat, COUNT(*) as jumlah')->groupBy('tingkat')->pluck('jumlah', 'tingkat'),
            'tahun_tersedia' => PaketPengadaan::distinct()->orderByDesc('tahun_anggaran')->pluck('tahun_anggaran'),
        ]);
    }

    /**
     * GET /api/v1/kinerja-pokja — ringkasan kinerja seluruh Pokja.
     */
    public function kinerjaPokja(Request $request)
    {
        $pokjas = Pokja::query()
            ->when($request->has('aktif'), fn ($q) => $q->where('aktif', filter_var($request->aktif, FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('nama')
            ->get();

        return $this->ok($pokjas->map(fn (Pokja $p) => $this->mapPokja($p)));
    }
}