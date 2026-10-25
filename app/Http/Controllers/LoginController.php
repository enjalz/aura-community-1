<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    // Maksimal percobaan gagal per menit untuk satu email dari satu alamat IP
    private const BATAS_PERCOBAAN = 5;

    public function tampilkan(): View
    {
        return view('auth.login');
    }

    public function masuk(Request $request): RedirectResponse
    {
        $data = $request->validate(
            [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
            ]
        );

        $email = Str::lower(trim($data['email']));
        $kunciBatas = 'login:' . $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($kunciBatas, self::BATAS_PERCOBAAN)) {
            $detik = RateLimiter::availableIn($kunciBatas);

            return back()
                ->withErrors(['email' => "Terlalu banyak percobaan login. Coba lagi dalam {$detik} detik."])
                ->onlyInput('email');
        }

        if (! Auth::attempt(['email' => $email, 'password' => $data['password']])) {
            RateLimiter::hit($kunciBatas, 60);

            // Pesan sama untuk email salah maupun password salah
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        if (Auth::user()->statusAkun !== 'aktif') {
            $this->hapusSesi($request);

            return back()
                ->withErrors(['email' => 'Akun Anda tidak aktif. Silakan hubungi Admin.'])
                ->onlyInput('email');
        }

        RateLimiter::clear($kunciBatas);

        // ID sesi baru setelah login, mencegah session fixation
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function keluar(Request $request): RedirectResponse
    {
        $this->hapusSesi($request);

        return redirect()->route('beranda');
    }

    private function hapusSesi(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}