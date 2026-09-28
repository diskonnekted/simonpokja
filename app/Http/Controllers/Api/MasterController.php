<?php

namespace App\Http\Controllers\Api;

use App\Models\Opd;
use App\Models\Penyedia;
use App\Models\Pokja;
use Illuminate\Http\Request;

class MasterController extends ApiController
{
    // =====================================================
    // OPD
    // =====================================================
    public function opds(Request $request)
    {
        $opds = Opd::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('nama', 'like', "%{$request->q}%")
                        ->orWhere('kode', 'like', "%{$request->q}%")
                        ->orWhere('singkatan', 'like', "%{$request->q}%");
                });
            })
            ->withCount('pakets')
            ->orderBy('kode')
            ->paginate($this->limit($request))
            ->through(fn (Opd $o) => [
                'id' => $o->id,
                'kode' => $o->kode,
                'nama' => $o->nama,
                'singkatan' => $o->singkatan,
                'jumlah_paket' => (int) $o->pakets_count,
            ]);

        return $this->page($opds);
    }

    public function opdDetail($id)
    {
        $o = Opd::withCount('pakets')->find($id);

        if (! $o) {
            return $this->notFound('OPD tidak ditemukan.');
        }

        return $this->ok([
            'id' => $o->id,
            'kode' => $o->kode,
            'nama' => $o->nama,
            'singkatan' => $o->singkatan,
            'kepala' => $o->kepala,
            'nip_kepala' => $o->nip_kepala,
            'alamat' => $o->alamat,
            'telepon' => $o->telepon,
            'email' => $o->email,
            'jumlah_paket' => (int) $o->pakets_count,
            'created_at' => $o->created_at?->toIso8601String(),
            'updated_at' => $o->updated_at?->toIso8601String(),
        ]);
    }

    // =====================================================
    // Pokja
    // =====================================================
    public function pokjas(Request $request)
    {
        $pokjas = Pokja::query()
            ->when($request->has('aktif'), function ($q) use ($request) {
                $q->where('aktif', filter_var($request->aktif, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('nama', 'like', "%{$request->q}%")
                        ->orWhere('ketua', 'like', "%{$request->q}%");
                });
            })
            ->orderBy('nama')
            ->get();

        return $this->ok($pokjas->map(fn (Pokja $p) => $this->mapPokja($p)));
    }

    public function pokjaDetail($id)
    {
        $p = Pokja::find($id);

        if (! $p) {
            return $this->notFound('Pokja tidak ditemukan.');
        }

        $paket = $p->pakets()
            ->select('id', 'kode_paket', 'nama_paket', 'tahun_anggaran', 'status', 'pagu', 'nilai_kontrak', 'tender_gagal', 'risiko')
            ->orderByDesc('tahun_anggaran')
            ->orderBy('kode_paket')
            ->get();

        return $this->ok([
            ...$this->mapPokja($p),
            'paket' => $paket,
        ]);
    }

    // =====================================================
    // Penyedia
    // =====================================================
    public function penyedias(Request $request)
    {
        $penyedia = Penyedia::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('nama', 'like', "%{$request->q}%")
                        ->orWhere('npwp', 'like', "%{$request->q}%")
                        ->orWhere('nib', 'like', "%{$request->q}%");
                });
            })
            ->when($request->filled('jenis_usaha'), fn ($q) => $q->where('jenis_usaha', $request->jenis_usaha))
            ->when($request->filled('kualifikasi'), fn ($q) => $q->where('kualifikasi', $request->kualifikasi))
            ->withCount('pakets')
            ->orderBy('nama')
            ->paginate($this->limit($request))
            ->through(fn (Penyedia $p) => [
                'id' => $p->id,
                'npwp' => $p->npwp,
                'nib' => $p->nib,
                'nama' => $p->nama,
                'jenis_usaha' => $p->jenis_usaha,
                'kualifikasi' => $p->kualifikasi,
                'alamat' => $p->alamat,
                'direktur' => $p->direktur,
                'telepon' => $p->telepon,
                'email' => $p->email,
                'aktif' => (bool) $p->aktif,
                'jumlah_paket' => (int) $p->pakets_count,
            ]);

        return $this->page($penyedia);
    }

    public function penyediaDetail($id)
    {
        $p = Penyedia::withCount('pakets')->find($id);

        if (! $p) {
            return $this->notFound('Penyedia tidak ditemukan.');
        }

        return $this->ok([
            'id' => $p->id,
            'npwp' => $p->npwp,
            'nib' => $p->nib,
            'nama' => $p->nama,
            'jenis_usaha' => $p->jenis_usaha,
            'kualifikasi' => $p->kualifikasi,
            'alamat' => $p->alamat,
            'direktur' => $p->direktur,
            'telepon' => $p->telepon,
            'email' => $p->email,
            'aktif' => (bool) $p->aktif,
            'jumlah_paket' => (int) $p->pakets_count,
            'created_at' => $p->created_at?->toIso8601String(),
            'updated_at' => $p->updated_at?->toIso8601String(),
        ]);
    }
}