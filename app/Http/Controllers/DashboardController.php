<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaketPengadaan;
use App\Models\Opd;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Non-admin (pokja/ppk) tidak punya dasbor global — arahkan ke beranda masing-masing
        if (! $request->user()?->isAdmin()) {
            return redirect()->route('pokja.dasbor');
        }

        $tahun = $request->get('tahun', date('Y'));

        $base = PaketPengadaan::query()->tahun($tahun);

        // ================= STATISTIK UTAMA =================
        $stats = [
            'total_paket' => (clone $base)->count(),
            'total_pagu' => (clone $base)->sum('pagu'),
            'total_kontrak' => (clone $base)->sum('nilai_kontrak'),
            'selesai' => (clone $base)->where('status', 'selesai')->count(),
            'berjalan' => (clone $base)->whereIn('status', ['kontrak', 'pelaksanaan'])->count(),
            'pemilihan' => (clone $base)->whereIn('status', ['persiapan', 'pemilihan'])->count(),
            'batal' => (clone $base)->where('status', 'batal')->count(),
        ];

        $stats['efisiensi'] = $stats['total_pagu'] > 0 && $stats['total_kontrak'] > 0
            ? round((($stats['total_pagu'] - $stats['total_kontrak']) / $stats['total_pagu']) * 100, 2)
            : 0;
        $stats['progress_rata'] = round((clone $base)->avg('progress') ?? 0);
        $stats['penyedia_aktif'] = (clone $base)->whereNotNull('penyedia_id')->distinct('penyedia_id')->count('penyedia_id');

        // ================= PALET PER STATUS (BAR) =================
        $perStatus = (clone $base)
            ->selectRaw('status, COUNT(*) as jumlah, SUM(pagu) as total_pagu, SUM(nilai_kontrak) as total_kontrak')
            ->groupBy('status')
            ->get();

        // ================= PALET PER OPD (TOP 8) =================
        $perOpd = (clone $base)
            ->join('opds', 'paket_pengadaans.opd_id', '=', 'opds.id')
            ->selectRaw('opds.singkatan, opds.nama, COUNT(*) as jumlah, SUM(paket_pengadaans.pagu) as total_pagu')
            ->groupBy('opds.id', 'opds.singkatan', 'opds.nama')
            ->orderByDesc('total_pagu')
            ->limit(8)
            ->get();

        // ================= PALET PER METODE =================
        $perMetode = (clone $base)
            ->selectRaw('metode, COUNT(*) as jumlah, SUM(pagu) as total_pagu')
            ->groupBy('metode')
            ->get();

        // ================= PALET PER JENIS =================
        $perJenis = (clone $base)
            ->selectRaw('jenis, COUNT(*) as jumlah')
            ->groupBy('jenis')
            ->pluck('jumlah', 'jenis');

        // ================= PALET PER BULAN (line chart progress kontrak) =================
        $perBulan = (clone $base)
            ->whereNotNull('nilai_kontrak')
            ->where('nilai_kontrak', '>', 0)
            ->selectRaw('MONTH(tanggal_mulai) as bulan, SUM(nilai_kontrak) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        // ================= PAKET TERBESAR =================
        $paketTerbesar = (clone $base)->with('opd')
            ->orderByDesc('pagu')->limit(5)->get();

        // ================= PAKET PROGRESS TERKINI =================
        $paketBerjalan = (clone $base)->with('opd', 'penyedia')
            ->whereIn('status', ['kontrak', 'pelaksanaan'])
            ->orderByDesc('updated_at')->limit(6)->get();

        // ================= TAHUN TERSEDIA =================
        $tahuns = PaketPengadaan::select('tahun_anggaran')->distinct()->orderByDesc('tahun_anggaran')->pluck('tahun_anggaran');

        // ================= DAFTAR OPD UNTUK FILTER =================
        $opds = Opd::orderBy('nama')->get(['id', 'nama', 'singkatan']);

        return view('dashboard', compact(
            'stats', 'perStatus', 'perOpd', 'perMetode', 'perJenis', 'perBulan',
            'paketTerbesar', 'paketBerjalan', 'tahuns', 'tahun', 'opds'
        ));
    }
}
