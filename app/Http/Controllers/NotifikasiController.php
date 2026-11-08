<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Membaca, menandai, dan menghapus notifikasi. Dipakai semua peran (Admin, Owner Brand, Influencer).
class NotifikasiController extends Controller
{
    // Klik notifikasi: ditandai dibaca, lalu membuka halaman tujuannya
    public function buka(Request $request, Notifikasi $notifikasi): RedirectResponse
    {
        $this->pastikanMilik($request, $notifikasi);
        $notifikasi->update(['sudahDibaca' => true]);

        return $notifikasi->tautan ? redirect($notifikasi->tautan) : back();
    }

    public function tandaiDibaca(Request $request, Notifikasi $notifikasi): RedirectResponse
    {
        $this->pastikanMilik($request, $notifikasi);
        $notifikasi->update(['sudahDibaca' => true]);

        return back();
    }

    public function tandaiSemuaDibaca(Request $request): RedirectResponse
    {
        $request->user()->notifikasi()->where('sudahDibaca', false)->update(['sudahDibaca' => true]);

        return back();
    }

    public function hapus(Request $request, Notifikasi $notifikasi): RedirectResponse
    {
        $this->pastikanMilik($request, $notifikasi);
        $notifikasi->delete();

        return back();
    }

    // Notifikasi milik pengguna lain tidak boleh dibuka lewat URL yang diubah
    private function pastikanMilik(Request $request, Notifikasi $notifikasi): void
    {
        abort_unless((int) $notifikasi->idPengguna === (int) $request->user()->idPengguna, 404);
    }
}