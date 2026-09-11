<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaketPengadaan;
use App\Models\Tahapan;
use App\Models\Evaluasi;
use App\Models\Opd;
use App\Models\Penyedia;

class PaketController extends Controller
{
    public function index(Request $request)
    {
        $query = PaketPengadaan::with(['opd', 'penyedia']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_paket', 'like', "%{$q}%")
                    ->orWhere('kode_paket', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('opd_id')) {
            $query->where('opd_id', $request->opd_id);
        }
        if ($request->filled('metode')) {
            $query->where('metode', $request->metode);
        }
        $query->where('tahun_anggaran', $request->get('tahun', date('Y')));

        $paket = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $opds = Opd::orderBy('nama')->get(['id', 'nama', 'singkatan']);
        $tahuns = PaketPengadaan::select('tahun_anggaran')->distinct()
            ->orderByDesc('tahun_anggaran')->pluck('tahun_anggaran');
        $statuses = [
            'draft' => 'Draft', 'persiapan' => 'Persiapan', 'pemilihan' => 'Pemilihan',
            'kontrak' => 'Kontrak', 'pelaksanaan' => 'Pelaksanaan', 'selesai' => 'Selesai', 'batal' => 'Batal',
        ];
        $metodes = [
            'tender' => 'Tender', 'seleksi' => 'Seleksi', 'epurchasing' => 'e-Purchasing',
            'penunjukan_langsung' => 'Penunjukan Langsung', 'pengadaan_langsung' => 'Pengadaan Langsung', 'swakelola' => 'Swakelola',
        ];

        return view('paket.index', compact('paket', 'opds', 'tahuns', 'statuses', 'metodes'));
    }

    public function create()
    {
        return view('paket.form', [
            'paket' => new PaketPengadaan(),
            'opds' => Opd::orderBy('nama')->get(),
            'penyedias' => Penyedia::where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_paket' => 'required|max:50|unique:paket_pengadaans,kode_paket',
            'nama_paket' => 'required|max:255',
            'opd_id' => 'required|exists:opds,id',
            'penyedia_id' => 'nullable|exists:penyedias,id',
            'jenis' => 'required|in:barang,jasa_konsultansi,konstruksi,jasa_lainnya',
            'metode' => 'required|in:tender,seleksi,epurchasing,penunjukan_langsung,pengadaan_langsung,swakelola',
            'sumber_dana' => 'required|in:apbd,apbn,blm,dak,did,lainnya',
            'pagu' => 'required|numeric|min:0',
            'hps' => 'nullable|numeric|min:0',
            'nilai_kontrak' => 'nullable|numeric|min:0',
            'tahun_anggaran' => 'required|digits:4',
            'status' => 'required|in:draft,persiapan,pemilihan,kontrak,pelaksanaan,selesai,batal',
            'progress' => 'nullable|integer|min:0|max:100',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'nullable|max:255',
            'keterangan' => 'nullable',
        ]);

        $validated['penyedia_id'] = $request->filled('penyedia_id') ? $request->penyedia_id : null;
        $validated['progress'] = $request->progress ?? 0;
        $validated['hps'] = empty($request->hps) ? 0 : $request->hps;
        $validated['nilai_kontrak'] = empty($request->nilai_kontrak) ? 0 : $request->nilai_kontrak;

        PaketPengadaan::create($validated);
        return redirect()->route('paket.index')->with('success', 'Paket pengadaan berhasil ditambahkan.');
    }

    public function show(PaketPengadaan $paket)
    {
        $paket->load(['opd', 'penyedia', 'tahapans', 'evaluasis.penyedia', 'pokja']);
        return view('paket.show', ['paket' => $paket]);
    }

    public function edit(PaketPengadaan $paket)
    {
        return view('paket.form', [
            'paket' => $paket,
            'opds' => Opd::orderBy('nama')->get(),
            'penyedias' => Penyedia::where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, PaketPengadaan $paket)
    {
        $validated = $request->validate([
            'kode_paket' => 'required|max:50|unique:paket_pengadaans,kode_paket,' . $paket->id,
            'nama_paket' => 'required|max:255',
            'opd_id' => 'required|exists:opds,id',
            'penyedia_id' => 'nullable|exists:penyedias,id',
            'jenis' => 'required|in:barang,jasa_konsultansi,konstruksi,jasa_lainnya',
            'metode' => 'required|in:tender,seleksi,epurchasing,penunjukan_langsung,pengadaan_langsung,swakelola',
            'sumber_dana' => 'required|in:apbd,apbn,blm,dak,did,lainnya',
            'pagu' => 'required|numeric|min:0',
            'hps' => 'nullable|numeric|min:0',
            'nilai_kontrak' => 'nullable|numeric|min:0',
            'tahun_anggaran' => 'required|digits:4',
            'status' => 'required|in:draft,persiapan,pemilihan,kontrak,pelaksanaan,selesai,batal',
            'progress' => 'nullable|integer|min:0|max:100',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'nullable|max:255',
            'keterangan' => 'nullable',
        ]);

        $validated['penyedia_id'] = $request->filled('penyedia_id') ? $request->penyedia_id : null;
        $validated['progress'] = $request->progress ?? 0;
        $validated['hps'] = empty($request->hps) ? 0 : $request->hps;
        $validated['nilai_kontrak'] = empty($request->nilai_kontrak) ? 0 : $request->nilai_kontrak;

        $paket->update($validated);
        return redirect()->route('paket.show', $paket)->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(PaketPengadaan $paket)
    {
        $paket->delete();
        return redirect()->route('paket.index')->with('success', 'Paket berhasil dihapus.');
    }

    public function storeTahapan(Request $request, PaketPengadaan $paket)
    {
        $validated = $request->validate([
            'nama_tahap' => 'required|max:255',
            'urutan' => 'nullable|integer|min:1',
            'tanggal_rencana' => 'nullable|date',
            'keterangan' => 'nullable',
        ]);

        $paket->tahapans()->create([
            'nama_tahap' => $validated['nama_tahap'],
            'urutan' => $validated['urutan'] ?? ($paket->tahapans()->count() + 1),
            'status' => 'belum',
            'tanggal_rencana' => $validated['tanggal_rencana'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return back()->with('success', 'Tahapan berhasil ditambahkan.');
    }

    public function updateTahapan(Request $request, Tahapan $tahapan)
    {
        $validated = $request->validate([
            'status' => 'required|in:belum,proses,selesai',
            'tanggal_aktual' => 'nullable|date',
        ]);

        $tahapan->update($validated);
        return back()->with('success', 'Status tahapan diperbarui.');
    }

    public function storeEvaluasi(Request $request, PaketPengadaan $paket)
    {
        $validated = $request->validate([
            'penyedia_id' => 'required|exists:penyedias,id',
            'jenis' => 'required|in:kualifikasi,teknis,harga',
            'skor' => 'required|numeric|min:0|max:100',
            'hasil' => 'required|in:lolos,gugur,menunggu',
            'catatan' => 'nullable',
        ]);

        $paket->evaluasis()->create([
            'penyedia_id' => $validated['penyedia_id'],
            'jenis' => $validated['jenis'],
            'skor' => $validated['skor'],
            'hasil' => $validated['hasil'],
            'catatan' => $validated['catatan'] ?? null,
            'tanggal' => now(),
        ]);

        return back()->with('success', 'Evaluasi berhasil disimpan.');
    }
}
