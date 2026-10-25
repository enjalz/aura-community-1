<?php

namespace App\Http\Controllers\OwnerBrand;

use App\Http\Controllers\Controller;
use App\Services\RekapCampaign;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Use case Laporan Hasil Campaign untuk Owner Brand (Tabel 25 langkah 6-7, wireframe Gambar 89).
// Hanya laporan FINAL dari campaign yang diberi akses (AksesCampaign).
class LaporanController extends Controller
{
    private const PER_HALAMAN = 10;

    public function index(Request $request, RekapCampaign $rekap): View
    {
        $cari = trim((string) $request->query('cari'));

        $query = $request->user()->ownerBrand->campaign()
            ->whereHas('laporan', fn ($q) => $q->where('statusLaporan', 'final'))
            ->with('laporan')
            ->withCount(['pendaftaran as jumlahInfluencer' => fn ($q) => $q->where('statusPendaftaran', 'diterima')])
            ->when($cari !== '', fn ($q) => $q->where('namaCampaign', 'like', '%' . addcslashes($cari, '%_\\') . '%'))
            ->orderByDesc('tanggalSelesai');

        $daftar = (clone $query)->paginate(self::PER_HALAMAN)->withQueryString();

        // Campaign tanpa akses atau laporannya belum final tidak bisa dibuka, walaupun ID-nya diketik di URL
        $terpilih = $request->integer('pilih') ? (clone $query)->find($request->integer('pilih')) : null;
        $terpilih ??= $daftar->first();

        return view('owner-brand.laporan', [
            'daftar' => $daftar,
            'terpilih' => $terpilih,
            'ringkasan' => $terpilih ? $rekap->ringkasan($terpilih) : null,
            'cari' => $cari,
        ]);
    }
}