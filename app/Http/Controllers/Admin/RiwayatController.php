<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Use case Riwayat Campaign untuk Admin (Tabel 26, wireframe Gambar 77): semua campaign yang sudah selesai
class RiwayatController extends Controller
{
    private const PER_HALAMAN = 10;

    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari'));
        $kategori = $request->query('kategori');

        $daftar = Campaign::query()
            ->where('statusCampaign', 'selesai')
            ->withCount(['pendaftaran as jumlahInfluencer' => fn ($q) => $q->where('statusPendaftaran', 'diterima')])
            ->when($cari !== '', fn ($q) => $q->where('namaCampaign', 'like', '%' . addcslashes($cari, '%_\\') . '%'))
            ->when(in_array($kategori, config('aura.kategori'), true), fn ($q) => $q->where('kategoriInfluencer', $kategori))
            ->orderByDesc('tanggalSelesai')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('admin.riwayat', [
            'daftar' => $daftar,
            'filter' => compact('cari', 'kategori'),
        ]);
    }
}