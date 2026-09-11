<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pokja;
use App\Models\PaketPengadaan;
use App\Models\PerubahanJadwal;
use App\Models\Sanggahan;
use App\Models\AlertAnomali;
use App\Models\AuditChecklist;

class PokjaController extends Controller
{
    // ===== DASHBOARD MONITORING KINERJA POKJA =====
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));

        $base = PaketPengadaan::query()->tahun($tahun);

        // ===== KPI UTAMA (sesuai dokumen Dasbor Pengawasan) =====
        $totalPaketAktif = (clone $base)->whereNotIn('status', ['selesai', 'batal'])->count();

        $totalTender = (clone $base)->whereIn('metode', ['tender', 'seleksi'])->count();
        $totalGagal = (clone $base)->where('tender_gagal', true)->count();
        $persenGagal = $totalTender > 0 ? round(($totalGagal / $totalTender) * 100, 1) : 0;

        // Kepatuhan SLA: % paket dengan pengunduran <= 3x
        $paketDelayBerulang = PerubahanJadwal::select('paket_id')
            ->selectRaw('COUNT(*) as jumlah')
            ->groupBy('paket_id')
            ->havingRaw('COUNT(*) > 3')
            ->pluck('paket_id');
        $paketDenganJadwal = PerubahanJadwal::distinct('paket_id')->count('paket_id');
        $kepatuhanSla = $totalPaketAktif > 0
            ? round((($totalPaketAktif - $paketDelayBerulang->count()) / $totalPaketAktif) * 100, 1)
            : 100.0;

        $alertAktif = AlertAnomali::where('status', 'aktif')->count();
        $alertCritical = AlertAnomali::where('status', 'aktif')->where('tingkat', 'critical')->count();

        $kpi = [
            'paket_aktif' => $totalPaketAktif,
            'tender_gagal_persen' => $persenGagal,
            'tender_gagal_jumlah' => $totalGagal,
            'kepatuhan_sla' => $kepatuhanSla,
            'alert_aktif' => $alertAktif,
            'alert_critical' => $alertCritical,
            'delay_berulang' => $paketDelayBerulang->count(),
            'sanggah_menunggu' => Sanggahan::where('hasil', 'menunggu')->count(),
        ];

        // ===== KARTU POKJA (beban, risiko, SLA per pokja) =====
        $pokjas = Pokja::where('aktif', true)
            ->withCount(['pakets as total_paket' => fn ($q) => $q->where('tahun_anggaran', $tahun)])
            ->get()
            ->each(function ($pokja) {
                $pokja->paket_aktif = $pokja->pakets()->whereNotIn('status', ['selesai', 'batal'])->count();
            })
            ->sortByDesc(fn ($p) => $p->skor_risiko)
            ->values();

        // ===== DISTRIBUSI TAHAPAN TENDER =====
        $tahapanDistribusi = (clone $base)
            ->selectRaw('tahap_tender, COUNT(*) as jumlah')
            ->whereNotIn('status', ['selesai', 'batal'])
            ->groupBy('tahap_tender')
            ->pluck('jumlah', 'tahap_tender');

        // ===== SEGMENTASI RISIKO PAKET =====
        $risikoDistribusi = (clone $base)
            ->selectRaw('risiko, COUNT(*) as jumlah')
            ->groupBy('risiko')
            ->pluck('jumlah', 'risiko');

        // ===== TREN PERUBAHAN JADWAL (6 bulan terakhir) =====
        $trenJadwal = PerubahanJadwal::selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan, COUNT(*) as jumlah")
            ->where('tanggal', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Perubahan tanpa BA (kepatuhan dokumen)
        $tanpaBa = PerubahanJadwal::where('ada_berita_acara', false)->count();
        $totalPerubahan = PerubahanJadwal::count();
        $persenTanpaBa = $totalPerubahan > 0 ? round(($tanpaBa / $totalPerubahan) * 100, 1) : 0;

        // ===== ALERT TERBARU =====
        $alerts = AlertAnomali::with(['paket', 'pokja'])
            ->where('status', 'aktif')
            ->orderByRaw("FIELD(tingkat, 'critical', 'warning', 'info')")
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // ===== PAKET RISIKO KRITIS =====
        $paketKritis = (clone $base)->with(['pokja', 'opd'])
            ->where('risiko', 'kritis')
            ->orderByDesc('pagu')
            ->limit(6)
            ->get();

        $tahuns = PaketPengadaan::select('tahun_anggaran')->distinct()
            ->orderByDesc('tahun_anggaran')->pluck('tahun_anggaran');

        return view('pokja.index', compact(
            'kpi', 'pokjas', 'tahapanDistribusi', 'risikoDistribusi', 'trenJadwal',
            'persenTanpaBa', 'alerts', 'paketKritis', 'tahuns', 'tahun'
        ));
    }

    // ===== DETAIL POKJA: KINERJA + CHECKLIST AUDIT =====
    public function show(Request $request, Pokja $pokja)
    {
        $tahun = $request->get('tahun', date('Y'));

        $pakets = $pokja->pakets()->with(['opd', 'penyedia', 'tahapans'])
            ->where('tahun_anggaran', $tahun)
            ->orderByDesc('pagu')->get();

        // Statistik pokja
        $stat = [
            'total_paket' => $pakets->count(),
            'paket_aktif' => $pakets->whereNotIn('status', ['selesai', 'batal'])->count(),
            'total_pagu' => $pakets->sum('pagu'),
            'tender_gagal' => $pakets->where('tender_gagal', true)->count(),
            'risiko_kritis' => $pakets->where('risiko', 'kritis')->count(),
        ];

        // Delay per paket (paket dengan pengunduran terbanyak)
        $delayPerPaket = PerubahanJadwal::select('paket_id')
            ->selectRaw('COUNT(*) as jumlah, SUM(CASE WHEN ada_berita_acara = 0 THEN 1 ELSE 0 END) as tanpa_ba')
            ->whereIn('paket_id', $pakets->pluck('id'))
            ->groupBy('paket_id')
            ->orderByDesc('jumlah')
            ->limit(6)
            ->get()
            ->map(function ($row) {
                $row->paket = PaketPengadaan::with('opd')->find($row->paket_id);
                return $row;
            });

        // Sanggahan pada paket pokja ini
        $sanggahans = Sanggahan::with(['paket', 'penyedia'])
            ->whereIn('paket_id', $pakets->pluck('id'))
            ->orderByDesc('tanggal_masuk')
            ->limit(8)
            ->get();

        // Checklist audit 6 kategori
        $kategoriList = AuditChecklist::kategoriList();
        $checklists = $pokja->checklists()->orderBy('kategori')->get()->groupBy('kategori');

        $alerts = $pokja->alerts()->with('paket')->where('status', 'aktif')->latest()->limit(5)->get();

        $tahuns = PaketPengadaan::select('tahun_anggaran')->distinct()
            ->orderByDesc('tahun_anggaran')->pluck('tahun_anggaran');

        return view('pokja.show', compact(
            'pokja', 'pakets', 'stat', 'delayPerPaket', 'sanggahans',
            'kategoriList', 'checklists', 'alerts', 'tahuns', 'tahun'
        ));
    }

    // ===== SIMPAN JAWABAN CHECKLIST AUDIT =====
    public function simpanChecklist(Request $request, Pokja $pokja)
    {
        $validated = $request->validate([
            'kategori' => 'required|integer|between:1,6',
            'poin' => 'required|string|max:255',
            'jawaban' => 'required|in:ya,tidak,na',
            'catatan' => 'nullable|string|max:1000',
        ]);

        AuditChecklist::updateOrCreate(
            [
                'pokja_id' => $pokja->id,
                'kategori' => $validated['kategori'],
                'poin' => $validated['poin'],
            ],
            [
                'jawaban' => $validated['jawaban'],
                'catatan' => $validated['catatan'] ?? null,
                'auditor_id' => auth()->id(),
            ]
        );

        return back()->with('success', 'Checklist audit tersimpan.');
    }

    // ===== UPDATE STATUS ALERT =====
    public function updateAlert(Request $request, AlertAnomali $alert)
    {
        $validated = $request->validate([
            'status' => 'required|in:aktif,ditinjau,selesai',
        ]);

        $alert->update($validated);
        return back()->with('success', 'Status alert diperbarui.');
    }

    // ===== KELOLA POKJA: DAFTAR =====
    public function kelola()
    {
        $pokjas = Pokja::withCount('pakets')->orderBy('nama')->get();

        return view('pokja.kelola', compact('pokjas'));
    }

    // ===== KELOLA POKJA: FORM TAMBAH =====
    public function create()
    {
        return view('pokja.form', ['pokja' => new Pokja()]);
    }

    // ===== KELOLA POKJA: SIMPAN =====
    public function storePokja(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|max:100|unique:pokjas,nama',
            'bidang' => 'nullable|max:150',
            'ketua' => 'nullable|max:255',
            'nip_ketua' => 'nullable|max:30',
            'anggota' => 'nullable|array',
            'anggota.*' => 'nullable|string|max:255',
            'kapasitas_ideal' => 'required|integer|min:1|max:20',
            'aktif' => 'boolean',
        ]);

        // Buang anggota kosong
        $validated['anggota'] = array_values(array_filter($validated['anggota'] ?? []));
        $validated['aktif'] = $request->boolean('aktif');

        Pokja::create($validated);
        return redirect()->route('pokja.kelola')->with('success', 'Pokja berhasil didaftarkan.');
    }

    // ===== KELOLA POKJA: FORM EDIT =====
    public function edit(Pokja $pokja)
    {
        return view('pokja.form', ['pokja' => $pokja]);
    }

    // ===== KELOLA POKJA: UPDATE =====
    public function updatePokja(Request $request, Pokja $pokja)
    {
        $validated = $request->validate([
            'nama' => 'required|max:100|unique:pokjas,nama,' . $pokja->id,
            'bidang' => 'nullable|max:150',
            'ketua' => 'nullable|max:255',
            'nip_ketua' => 'nullable|max:30',
            'anggota' => 'nullable|array',
            'anggota.*' => 'nullable|string|max:255',
            'kapasitas_ideal' => 'required|integer|min:1|max:20',
            'aktif' => 'boolean',
        ]);

        $validated['anggota'] = array_values(array_filter($validated['anggota'] ?? []));
        $validated['aktif'] = $request->boolean('aktif');

        $pokja->update($validated);
        return redirect()->route('pokja.kelola')->with('success', 'Data pokja diperbarui.');
    }

    // ===== KELOLA POKJA: HAPUS =====
    public function destroyPokja(Pokja $pokja)
    {
        if ($pokja->pakets()->exists()) {
            return back()->with('error', 'Pokja masih menangani paket. Pindahkan paketnya terlebih dahulu.');
        }

        $pokja->delete();
        return redirect()->route('pokja.kelola')->with('success', 'Pokja dihapus.');
    }
}
