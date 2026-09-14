<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi akses berdasar peran pengguna.
 * Dipakai sebagai alias 'role' dengan argumen peran yang diizinkan, mis.:
 *   ->middleware('role:admin')
 *   ->middleware('role:pokja,admin')  // pokja ATAU admin
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($peran !== [] && ! in_array($user->role, $peran, true)) {
            if ($user->isPokja()) {
                return redirect()->route('pokja.dasbor')->with('warning', 'Halaman tersebut khusus Administrator. Anda telah diarahkan ke Dasbor Pokja.');
            }
            abort(403, 'Anda tidak memiliki kewenangan untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
