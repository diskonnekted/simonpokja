<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Cegah 419 Page Expired saat pengguna menekan tombol Keluar / Logout
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Jika sesi habis di halaman mana pun, arahkan ke login dengan pesan ramah (bukan layar 419)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->route('login')->with('warning', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
        });
    })->create();
