<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Hanya pengguna dengan peran yang diizinkan yang boleh membuka halaman.
// Contoh di route: ->middleware('peran:admin')
class PastikanPeran
{
    public function handle(Request $request, Closure $next, string ...$peranDiizinkan): Response
    {
        $pengguna = $request->user();

        // Akun yang dinonaktifkan Admin saat sedang login langsung dikeluarkan
        if ($pengguna->statusAkun !== 'aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda tidak aktif. Silakan hubungi Admin.']);
        }

        if (! in_array($pengguna->peran(), $peranDiizinkan, true)) {
            abort(403);
        }

        return $next($request);
    }
}