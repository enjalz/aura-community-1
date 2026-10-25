<?php

namespace App\Http\Controllers\Influencer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Use case Riwayat Campaign untuk Influencer (Tabel 26, wireframe Gambar 85):
// campaign yang pernah diikuti (diterima) dan sudah selesai atau sudah ada pembayarannya
class RiwayatController extends Controller
{
    private const PER_HALAMAN = 10;

    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari'));

        $daftar = $request->user()->influencer->pendaftaran()
            ->where('statusPendaftaran', 'diterima')
            ->where(fn ($q) => $q->whereHas('campaign', fn ($c) => $c->where('statusCampaign', 'selesai'))->orHas('pembayaran'))
            ->when($cari !== '', fn ($q) => $q->whereHas('campaign', fn ($c) => $c->where('namaCampaign', 'like', '%' . addcslashes($cari, '%_\\') . '%')))
            ->with(['campaign', 'pembayaran'])
            ->orderByDesc('idPendaftaran')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('influencer.riwayat', [
            'daftar' => $daftar,
            'cari' => $cari,
        ]);
    }
}