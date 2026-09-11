<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaketPengadaan;
use App\Models\Opd;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $opdId = $request->get('opd_id');
        $status = $request->get('status');

        $query = PaketPengadaan::with(['opd', 'penyedia'])
            ->where('tahun_anggaran', $tahun);

        if ($opdId) {
            $query->where('opd_id', $opdId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $paket = $query->orderBy('opd_id')->orderByDesc('pagu')->get();

        $totalPagu = $paket->sum('pagu');
        $totalKontrak = $paket->sum('nilai_kontrak');

        $opds = Opd::orderBy('nama')->get(['id', 'nama', 'singkatan']);
        $tahuns = PaketPengadaan::select('tahun_anggaran')->distinct()
            ->orderByDesc('tahun_anggaran')->pluck('tahun_anggaran');

        return view('laporan.index', compact(
            'paket', 'totalPagu', 'totalKontrak', 'opds', 'tahuns', 'tahun', 'opdId', 'status'
        ));
    }

    public function export(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $opdId = $request->get('opd_id');
        $status = $request->get('status');

        $query = PaketPengadaan::with(['opd', 'penyedia'])
            ->where('tahun_anggaran', $tahun);

        if ($opdId) {
            $query->where('opd_id', $opdId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $paket = $query->orderBy('opd_id')->orderByDesc('pagu')->get();

        $filename = "laporan-pengadaan-{$tahun}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($paket) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, [
                'Kode Paket', 'Nama Paket', 'OPD', 'Penyedia', 'Jenis', 'Metode',
                'Pagu (Rp)', 'HPS (Rp)', 'Nilai Kontrak (Rp)', 'Status', 'Progress (%)', 'Tahun',
            ]);

            foreach ($paket as $p) {
                fputcsv($file, [
                    $p->kode_paket,
                    $p->nama_paket,
                    $p->opd->singkatan ?? $p->opd->nama ?? '-',
                    $p->penyedia->nama ?? '-',
                    $p->jenis_label,
                    $p->metode_label,
                    $p->pagu,
                    $p->hps,
                    $p->nilai_kontrak,
                    $p->status_label,
                    $p->progress,
                    $p->tahun_anggaran,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
