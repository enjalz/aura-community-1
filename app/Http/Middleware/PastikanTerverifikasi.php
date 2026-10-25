<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Influencer yang belum diverifikasi Admin hanya boleh membuka halaman Status Verifikasi
// (Tabel 6, alternative scenario).
class PastikanTerverifikasi
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->influencer?->statusVerifikasi !== 'diverifikasi') {
            return redirect()->route('influencer.status');
        }

        return $next($request);
    }
}