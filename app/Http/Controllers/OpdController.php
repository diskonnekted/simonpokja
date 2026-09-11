<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opd;
use App\Models\PaketPengadaan;

class OpdController extends Controller
{
    public function index(Request $request)
    {
        $query = Opd::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('singkatan', 'like', "%{$q}%")
                    ->orWhere('kode', 'like', "%{$q}%");
            });
        }

        $opds = $query->withCount('pakets')->orderBy('nama')->paginate(20)->withQueryString();

        return view('opd.index', compact('opds'));
    }

    public function create()
    {
        return view('opd.form', ['opd' => new Opd()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|max:30|unique:opds,kode',
            'nama' => 'required|max:255',
            'singkatan' => 'nullable|max:50',
            'kepala' => 'nullable|max:255',
            'nip_kepala' => 'nullable|max:30',
            'alamat' => 'nullable|max:255',
            'telepon' => 'nullable|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        Opd::create($validated);
        return redirect()->route('opd.index')->with('success', 'OPD berhasil ditambahkan.');
    }

    public function show(Opd $opd)
    {
        $pakets = PaketPengadaan::where('opd_id', $opd->id)
            ->with('penyedia')
            ->orderByDesc('pagu')
            ->paginate(10);

        $statistik = [
            'total_paket' => PaketPengadaan::where('opd_id', $opd->id)->count(),
            'total_pagu' => PaketPengadaan::where('opd_id', $opd->id)->sum('pagu'),
            'total_kontrak' => PaketPengadaan::where('opd_id', $opd->id)->sum('nilai_kontrak'),
            'selesai' => PaketPengadaan::where('opd_id', $opd->id)->where('status', 'selesai')->count(),
        ];

        return view('opd.show', compact('opd', 'pakets', 'statistik'));
    }

    public function edit(Opd $opd)
    {
        return view('opd.form', ['opd' => $opd]);
    }

    public function update(Request $request, Opd $opd)
    {
        $validated = $request->validate([
            'kode' => 'required|max:30|unique:opds,kode,' . $opd->id,
            'nama' => 'required|max:255',
            'singkatan' => 'nullable|max:50',
            'kepala' => 'nullable|max:255',
            'nip_kepala' => 'nullable|max:30',
            'alamat' => 'nullable|max:255',
            'telepon' => 'nullable|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $opd->update($validated);
        return redirect()->route('opd.index')->with('success', 'OPD berhasil diperbarui.');
    }

    public function destroy(Opd $opd)
    {
        $opd->delete();
        return redirect()->route('opd.index')->with('success', 'OPD berhasil dihapus.');
    }
}
