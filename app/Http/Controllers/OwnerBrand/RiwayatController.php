<?php

namespace App\Http\Controllers\OwnerBrand;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Use case Riwayat Campaign untuk Owner Brand (Tabel 26, wireframe Gambar 90):
// campaign yang sudah selesai dan diberi akses ke owner brand ini
class RiwayatController extends Controller
{
    private const PER_HALAMAN = 10;

    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari'));

        $daftar = $request->user()->ownerBrand->campaign()
            ->where('statusCampaign', 'selesai')
            ->withCount(['pendaftaran as jumlahInfluencer' => fn ($q) => $q->where('statusPendaftaran', 'diterima')])
            ->when($cari !== '', fn ($q) => $q->where('namaCampaign', 'like', '%' . addcslashes($cari, '%_\\') . '%'))
            ->orderByDesc('tanggalSelesai')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('owner-brand.riwayat', [
            'daftar' => $daftar,
            'cari' => $cari,
        ]);
    }
}