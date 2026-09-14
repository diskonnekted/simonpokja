<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $loginInput = trim($request->input('email') ?? '');
        $password = $request->input('password');

        if (empty($loginInput) || empty($password)) {
            return back()->withErrors([
                'email' => 'Email/Username dan password wajib diisi.',
            ])->withInput();
        }

        // Resolusi alias HANYA diizinkan pada mode local/development
        $email = $loginInput;
        if (app()->isLocal() && !str_contains($loginInput, '@')) {
            $cleaned = strtolower(str_replace([' ', '-', '_'], '', $loginInput));
            if ($cleaned === 'admin') {
                $email = 'admin@banjarnegara.go.id';
            } elseif (preg_match('/^pokja([1-5])$/', $cleaned, $m)) {
                $email = "pokja{$m[1]}@banjarnegara.go.id";
            } else {
                $userByName = User::where('name', 'like', "%{$loginInput}%")->first();
                if ($userByName) {
                    $email = $userByName->email;
                }
            }
        }

        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Jika user adalah Pokja, pastikan tidak diarahkan ke intended URL admin
            $intended = session('url.intended');
            if ($user->isPokja()) {
                if (!$intended || !str_contains($intended, 'pokja-dasbor')) {
                    session()->forget('url.intended');
                    return redirect()->route('pokja.dasbor');
                }
            }

            return redirect()->intended($user->routeBeranda());
        }

        return back()->withErrors([
            'email' => 'Username/Email atau password salah. (Gunakan password default: password)',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
