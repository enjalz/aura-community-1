<?php

namespace App\Http\Controllers\OwnerBrand;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\RekapCampaign;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Use case Laporan Hasil Campaign untuk Owner Brand (Tabel 25 langkah 6-7).
// Wireframe Gambar 90 (daftar) dan Gambar 91 (detail).
// Hanya laporan FINAL dari campaign yang diberi akses (AksesCampaign).
class LaporanController extends Controller
{
    private const PER_HALAMAN = 10;

    public function index(Request $request, RekapCampaign $rekap): View
    {
        $cari = trim((string) $request->query('cari'));

        $daftar = $request->user()->ownerBrand->campaign()
            ->whereHas('laporan', fn ($q) => $q->where('statusLaporan', 'final'))
            ->with('laporan')
            ->withCount(['pendaftaran as jumlahInfluencer' => fn ($q) => $q->where('statusPendaftaran', 'diterima')])
            ->when($cari !== '', fn ($q) => $q->where('namaCampaign', 'like', '%' . addcslashes($cari, '%_\\') . '%'))
            ->orderByDesc('tanggalSelesai')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        $ringkasan = $daftar->getCollection()
            ->mapWithKeys(fn (Campaign $campaign) => [$campaign->idCampaign => $rekap->ringkasan($campaign)]);

        return view('owner-brand.laporan', [
            'daftar'    => $daftar,
            'ringkasan' => $ringkasan,
            'cari'      => $cari,
        ]);
    }

    public function detail(Request $request, Campaign $campaign, RekapCampaign $rekap): View
    {
        // Keamanan: campaign dicari ULANG lewat relasi milik owner brand yang sedang login.
        // Jadi campaign tanpa akses, atau laporannya belum final, tidak bisa dibuka
        // walaupun ID-nya diketik langsung di URL (hasilnya 404).
        $terpilih = $request->user()->ownerBrand->campaign()
            ->whereHas('laporan', fn ($q) => $q->where('statusLaporan', 'final'))
            ->with(['laporan', 'ketentuanPlatform'])
            ->find($campaign->idCampaign);

        abort_unless($terpilih, 404);

        return view('owner-brand.laporan-detail', [
            'campaign'  => $terpilih,
            'laporan'   => $terpilih->laporan,
            'ringkasan' => $rekap->ringkasan($terpilih),
            'performa'  => $rekap->performa($terpilih),
            'rincian'   => $rekap->rincian($terpilih),
        ]);
    }
}