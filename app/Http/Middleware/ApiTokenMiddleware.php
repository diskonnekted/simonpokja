<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware autentikasi token untuk API integrasi (SIBIJAK).
 *
 * Token dikirim melalui header "Authorization: Bearer <token>" atau
 * (untuk kemudahan pengujian) parameter query "?token=<token>".
 */
class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->ambilToken($request);
        $terdaftar = $this->tokenTerdaftar();

        $sah = $token !== null && collect($terdaftar)->contains(
            fn ($t) => hash_equals($t, $token)
        );

        if (! $sah) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak terautentikasi: token API tidak valid atau tidak disertakan.',
            ], 401);
        }

        return $next($request);
    }

    private function ambilToken(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if ($header && preg_match('/Bearer\s+(\S+)/i', $header, $m)) {
            return $m[1];
        }

        return $request->query('token');
    }

    /**
     * Daftar token yang sah, berasal dari config('sibijak.token').
     */
    private function tokenTerdaftar(): array
    {
        $raw = trim((string) config('sibijak.token', ''));

        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}