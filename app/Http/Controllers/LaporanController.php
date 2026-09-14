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

        // R5: Ringkasan Agregat per Perangkat Daerah (OPD)
        $ringkasanPerOpd = $paket->groupBy('opd_id')->map(function ($items, $opdKey) {
            $first = $items->first();
            $namaOpd = $first->opd?->nama ?? 'Tanpa OPD';
            $singkatanOpd = $first->opd?->singkatan ?? $namaOpd;
            $pagu = $items->sum('pagu');
            $kontrak = $items->sum('nilai_kontrak');
            $efisiensi = $pagu > 0 && $kontrak > 0 ? max(0, $pagu - $kontrak) : 0;
            $persenEfisiensi = $pagu > 0 && $efisiensi > 0 ? round(($efisiensi / $pagu) * 100, 1) : 0;

            return [
                'opd_id' => $opdKey,
                'nama' => $namaOpd,
                'singkatan' => $singkatanOpd,
                'jumlah_paket' => $items->count(),
                'total_pagu' => $pagu,
                'total_kontrak' => $kontrak,
                'efisiensi' => $efisiensi,
                'persen_efisiensi' => $persenEfisiensi,
                'selesai' => $items->where('status', 'selesai')->count(),
                'berjalan' => $items->whereIn('status', ['persiapan', 'pemilihan', 'kontrak', 'pelaksanaan'])->count(),
                'batal' => $items->where('status', 'batal')->count(),
                'rata_progres' => round($items->avg('progress') ?? 0, 1),
            ];
        })->sortByDesc('total_pagu')->values();

        $opds = Opd::orderBy('nama')->get(['id', 'nama', 'singkatan']);
        $tahuns = PaketPengadaan::select('tahun_anggaran')->distinct()
            ->orderByDesc('tahun_anggaran')->pluck('tahun_anggaran');

        return view('laporan.index', compact(
            'paket', 'totalPagu', 'totalKontrak', 'ringkasanPerOpd', 'opds', 'tahuns', 'tahun', 'opdId', 'status'
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
