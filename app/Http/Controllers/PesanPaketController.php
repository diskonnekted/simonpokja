<?php

namespace App\Http\Controllers;

use App\Models\PaketPengadaan;
use App\Models\PesanPaket;
use Illuminate\Http\Request;

/**
 * Perpesanan per pekerjaan: Kepala LPSE (admin, selaku supervisor)
 * berdiskusi dengan Pokja pemilik paket langsung pada konteks pekerjaan.
 */
class PesanPaketController extends Controller
{
    /**
     * Otorisasi: admin bebas; user pokja hanya pada paket milik Pokja-nya.
     */
    private function otorisasi(Request $request, PaketPengadaan $paket): void
    {
        $user = $request->user();
        if (! $user->isAdmin() && $user->pokja_id !== $paket->pokja_id) {
            abort(403, 'Anda tidak berhak mengakses pesan pekerjaan ini.');
        }
    }

    /**
     * Daftar pesan satu paket (JSON, untuk polling). Sekaligus menandai
     * pesan lawan bicara sebagai sudah dibaca.
     */
    public function index(Request $request, PaketPengadaan $paket)
    {
        $this->otorisasi($request, $paket);

        $paket->pesans()
            ->where('user_id', '!=', $request->user()->id)
            ->whereNull('dibaca_pada')
            ->update(['dibaca_pada' => now()]);

        $pesans = $paket->pesans()->with('user:id,name,role')->get();

        return response()->json([
            'pesans' => $pesans->map(fn ($p) => [
                'id' => $p->id,
                'pesan' => $p->pesan,
                'milik_sendiri' => $p->user_id === $request->user()->id,
                'pengirim' => $p->user?->name,
                'peran' => $p->user?->role === 'admin' ? 'Kepala LPSE' : ($paket->pokja?->nama ?? 'Pokja'),
                'waktu' => $p->created_at->translatedFormat('d M Y H:i'),
                'dibaca' => ! is_null($p->dibaca_pada),
            ]),
        ]);
    }

    /**
     * Kirim pesan baru pada sebuah paket.
     */
    public function store(Request $request, PaketPengadaan $paket)
    {
        $this->otorisasi($request, $paket);

        $data = $request->validate([
            'pesan' => ['required', 'string', 'max:2000'],
        ]);

        $pesan = PesanPaket::create([
            'paket_id' => $paket->id,
            'user_id' => $request->user()->id,
            'pesan' => $data['pesan'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'id' => $pesan->id], 201);
        }

        return back()->with('success', 'Pesan terkirim.');
    }
}
