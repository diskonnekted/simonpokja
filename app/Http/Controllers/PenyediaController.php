<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penyedia;
use App\Models\PaketPengadaan;

class PenyediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Penyedia::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('npwp', 'like', "%{$q}%");
            });
        }
        if ($request->filled('kualifikasi')) {
            $query->where('kualifikasi', $request->kualifikasi);
        }

        $penyedias = $query->withCount('pakets')->orderBy('nama')->paginate(20)->withQueryString();

        return view('penyedia.index', compact('penyedias'));
    }

    public function create()
    {
        return view('penyedia.form', ['penyedia' => new Penyedia()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|max:255',
            'npwp' => 'nullable|max:30|unique:penyedias,npwp',
            'nib' => 'nullable|max:50',
            'jenis_usaha' => 'required|in:kecil,non_kecil,perseorangan,koperasi',
            'kualifikasi' => 'required|in:kecil,menengah,besar',
            'alamat' => 'nullable|max:255',
            'direktur' => 'nullable|max:255',
            'telepon' => 'nullable|max:20',
            'email' => 'nullable|email|max:255',
            'aktif' => 'boolean',
        ]);

        $validated['aktif'] = $request->boolean('aktif');
        Penyedia::create($validated);
        return redirect()->route('penyedia.index')->with('success', 'Penyedia berhasil ditambahkan.');
    }

    public function show(Penyedia $penyedia)
    {
        $pakets = PaketPengadaan::where('penyedia_id', $penyedia->id)
            ->with('opd')
            ->orderByDesc('tahun_anggaran')
            ->paginate(10);

        return view('penyedia.show', compact('penyedia', 'pakets'));
    }

    public function edit(Penyedia $penyedia)
    {
        return view('penyedia.form', ['penyedia' => $penyedia]);
    }

    public function update(Request $request, Penyedia $penyedia)
    {
        $validated = $request->validate([
            'nama' => 'required|max:255',
            'npwp' => 'nullable|max:30|unique:penyedias,npwp,' . $penyedia->id,
            'nib' => 'nullable|max:50',
            'jenis_usaha' => 'required|in:kecil,non_kecil,perseorangan,koperasi',
            'kualifikasi' => 'required|in:kecil,menengah,besar',
            'alamat' => 'nullable|max:255',
            'direktur' => 'nullable|max:255',
            'telepon' => 'nullable|max:20',
            'email' => 'nullable|email|max:255',
            'aktif' => 'boolean',
        ]);

        $validated['aktif'] = $request->boolean('aktif');
        $penyedia->update($validated);
        return redirect()->route('penyedia.index')->with('success', 'Penyedia berhasil diperbarui.');
    }

    public function destroy(Penyedia $penyedia)
    {
        $penyedia->delete();
        return redirect()->route('penyedia.index')->with('success', 'Penyedia berhasil dihapus.');
    }
}
