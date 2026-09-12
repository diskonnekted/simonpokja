<?php

namespace App\Http\Controllers;

use App\Models\PaketPengadaan;
use App\Models\PesanPaket;
use App\Models\ProgresPekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dasbor untuk user dengan peran Pokja: mengelola laporan progres
 * pekerjaan milik Pokja-nya sendiri. Admin dapat meninjau semua Pokja
 * (read-only).
 */
class PokjaDashboardController extends Controller
{
    /**
     * Dasbor pokja: ?pokja=x untuk admin melihat Pokja tertentu.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Admin boleh pilih Pokja apa pun; user pokja terkunci ke Pokja-nya
        if ($user->isAdmin()) {
            $pokjaId = (int) $request->query('pokja', 0) ?: \App\Models\Pokja::orderBy('id')->value('id');
            $bolehSimpan = false;
        } else {
            $pokjaId = $user->pokja_id;
            $bolehSimpan = true;
        }

        $pokja = \App\Models\Pokja::findOrFail($pokjaId);
        $listPokja = \App\Models\Pokja::orderBy('id')->get(['id', 'nama']);

        $pakets = PaketPengadaan::with(['opd:id,singkatan', 'penyedia:id,nama', 'tahapans', 'progresTerakhir'])
            ->where('pokja_id', $pokja->id)
            ->where('tahun_anggaran', (int) date('Y'))
            ->get()
            // Urut: lewat deadline -> mendesak -> mendekati -> aman -> tanggal terdekat
            ->sort(function ($a, $b) {
                $urut = ['lewat' => 0, 'mendesak' => 1, 'mendekati' => 2, 'normal' => 3];
                $ka = $urut[$a->status_deadline[0]] <=> $urut[$b->status_deadline[0]];
                if ($ka !== 0) {
                    return $ka;
                }

                return $a->tanggal_selesai?->valueOf() <=> $b->tanggal_selesai?->valueOf();
            })
            ->values();

        $hitung = $pakets->countBy(fn ($p) => $p->status_deadline[0]);

        // Statistik pesan per paket: total & yang belum dibaca user saat ini
        $statPesan = PesanPaket::whereIn('paket_id', $pakets->pluck('id'))
            ->selectRaw('paket_id, COUNT(*) as total, SUM(CASE WHEN dibaca_pada IS NULL AND user_id != ? THEN 1 ELSE 0 END) as baru', [$user->id])
            ->groupBy('paket_id')
            ->get()
            ->keyBy('paket_id');

        return view('pokja.dasbor', [
            'user' => $user,
            'pokja' => $pokja,
            'listPokja' => $listPokja,
            'pakets' => $pakets,
            'bolehSimpan' => $bolehSimpan,
            'statPesan' => $statPesan,
            'nLewat' => $hitung['lewat'] ?? 0,
            'nMendesak' => $hitung['mendesak'] ?? 0,
            'nMendekati' => $hitung['mendekati'] ?? 0,
        ]);
    }

    /**
     * Simpan progres pekerjaan (riwayat + sinkron ke paket).
     */
    public function simpanProgres(Request $request, PaketPengadaan $paket)
    {
        $user = $request->user();

        // Policy: user pokja hanya boleh paket milik pokja-nya; admin hanya tinjau
        if ($user->isAdmin()) {
            abort(403, 'Admin berperan sebagai pengamat; penyimpanan progres dilakukan oleh akun Pokja.');
        }
        if ($user->pokja_id !== $paket->pokja_id) {
            abort(403, 'Paket ini bukan milik Pokja Anda.');
        }

        $data = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', 'in:draft,persiapan,pemilihan,kontrak,pelaksanaan,selesai,batal'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $user, $paket) {
            // Riwayat
            ProgresPekerjaan::create([
                'paket_id' => $paket->id,
                'user_id' => $user->id,
                'progress' => $data['progress'],
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
            ]);
            // Sinkron ke paket agar dashboard admin & peta konsisten
            $paket->update([
                'progress' => $data['progress'],
                'status' => $data['status'],
            ]);
        });

        return back()->with('success', 'Progres "' . $paket->nama_paket . '" tersimpan (' . $data['progress'] . '%, ' . $data['status'] . ').');
    }

    /**
     * Riwayat progres satu paket.
     */
    public function riwayat(Request $request, PaketPengadaan $paket)
    {
        $user = $request->user();
        if (! $user->isAdmin() && $user->pokja_id !== $paket->pokja_id) {
            abort(403, 'Paket ini bukan milik Pokja Anda.');
        }

        $riwayat = $paket->progresPekerjaans()->with('user:id,name')->get();

        return response()->json([
            'paket' => [
                'kode' => $paket->kode_paket,
                'nama' => $paket->nama_paket,
                'progress' => $paket->progress,
            ],
            'riwayat' => $riwayat->map(fn ($r) => [
                'waktu' => $r->created_at->translatedFormat('d M Y H:i'),
                'user' => $r->user?->name,
                'progress' => $r->progress,
                'status' => $r->status,
                'catatan' => $r->catatan,
            ]),
        ]);
    }
}
