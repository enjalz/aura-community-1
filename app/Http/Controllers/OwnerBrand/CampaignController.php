<?php

namespace App\Http\Controllers\OwnerBrand;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\HasilPengerjaan;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Use case Melihat Detail Campaign (Tabel 16) untuk Owner Brand.
// Hanya campaign yang diberi akses oleh Admin (tabel AksesCampaign, Tabel 15) yang tampil.
class CampaignController extends Controller
{
    private const PER_HALAMAN = 10;

    public function index(Request $request): View
    {
        $ownerBrand = $request->user()->ownerBrand;
        $cari = trim((string) $request->query('cari'));
        $status = $request->query('status');

        // $ownerBrand->campaign() = relasi lewat tabel AksesCampaign (model OwnerBrand, Modul 1)
        $daftar = $ownerBrand->campaign()
            ->when($cari !== '', fn ($q) => $q->where('namaCampaign', 'like', '%' . addcslashes($cari, '%_\\') . '%'))
            ->when(array_key_exists((string) $status, config('aura.statusCampaign')), fn ($q) => $q->where('statusCampaign', $status))
            ->orderByDesc('tanggalMulai')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        // Tabel 16 alternative scenario: campaign tanpa akses tidak bisa dibuka, walaupun ID-nya diketik di URL.
        // find() di relasi ini hanya mencari di antara campaign yang diberi akses.
        $terpilih = $request->integer('pilih')
            ? $ownerBrand->campaign()->find($request->integer('pilih'))
            : null;
        $terpilih ??= $daftar->first();
        $terpilih?->load('ketentuanPlatform');

        return view('owner-brand.campaign', [
            'daftar' => $daftar,
            'terpilih' => $terpilih,
            'perkembangan' => $terpilih ? $this->perkembangan($terpilih) : null,
            'filter' => compact('cari', 'status'),
        ]);
    }

    // Angka "Perkembangan Campaign" di wireframe Gambar 88
    private function perkembangan(Campaign $campaign): array
    {
        $hasilCampaign = HasilPengerjaan::whereHas('pendaftaran', fn ($q) => $q->where('idCampaign', $campaign->idCampaign));

        return [
            'diterima' => $campaign->pendaftaran()->where('statusPendaftaran', 'diterima')->count(),
            'draftDisetujui' => (clone $hasilCampaign)->where('statusDraft', 'disetujui')->count(),
            'insightDisetujui' => (clone $hasilCampaign)->where('statusInsight', 'disetujui')->count(),
        ];
    }
}