<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('aksi', 'like', "%{$q}%")
                    ->orWhere('tabel', 'like', "%{$q}%");
            });
        }
        if ($request->filled('tabel')) {
            $query->where('tabel', $request->tabel);
        }

        $logs = $query->paginate(25)->withQueryString();
        $tabels = AuditLog::select('tabel')->distinct()->orderBy('tabel')->pluck('tabel');

        // Monitoring Sesi Aktif dari MariaDB View
        $activeSessions = DB::table('v_active_sessions')
            ->whereNotNull('user_id')
            ->get();

        $guestSessionCount = DB::table('sessions')->whereNull('user_id')->count();
        $totalSessionCount = DB::table('sessions')->count();
        $currentSessionId = $request->session()->getId();

        return view('audit.index', compact(
            'logs', 'tabels', 'activeSessions', 'guestSessionCount', 'totalSessionCount', 'currentSessionId'
        ));
    }

    /**
     * Bersihkan sesi tamu atau sesi kedaluwarsa dari database.
     */
    public function bersihkanSesi(Request $request)
    {
        $currentId = $request->session()->getId();
        $lifetimeMinutes = (int) config('session.lifetime', 120);
        $threshold = now()->subMinutes($lifetimeMinutes)->timestamp;

        $deleted = DB::table('sessions')
            ->where('id', '!=', $currentId)
            ->where(function ($q) use ($threshold) {
                $q->whereNull('user_id')
                  ->orWhere('last_activity', '<', $threshold);
            })
            ->delete();

        return back()->with('success', "Berhasil membersihkan {$deleted} sesi usang/tamu dari basis data MariaDB.");
    }

    /**
     * Putus / terminate sesi pengguna tertentu secara paksa.
     */
    public function putusSesi(Request $request, string $sessionId)
    {
        if ($sessionId === $request->session()->getId()) {
            return back()->with('error', 'Tidak dapat memutus sesi Anda sendiri yang sedang aktif.');
        }

        $deleted = DB::table('sessions')->where('id', $sessionId)->delete();

        if ($deleted) {
            return back()->with('success', 'Sesi pengguna berhasil diputus secara paksa.');
        }

        return back()->with('error', 'Sesi tidak ditemukan atau telah kedaluwarsa.');
    }
}
