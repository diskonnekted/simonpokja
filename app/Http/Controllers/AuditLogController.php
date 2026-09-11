<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        return view('audit.index', compact('logs', 'tabels'));
    }
}
