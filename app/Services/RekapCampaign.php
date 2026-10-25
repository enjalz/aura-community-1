<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\HasilPengerjaan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

// Menghitung statistik laporan campaign (Tabel 25: "sistem mengakumulasi data views, likes,
// dan komentar dari seluruh influencer yang selesai").
// Hanya hasil pengerjaan yang insight-nya sudah DISETUJUI Admin yang dihitung.
// Dipakai oleh laporan Admin, laporan Owner Brand, dan dashboard.
class RekapCampaign
{
    // Rekapitulasi: jumlah influencer selesai, total views, likes, dan komentar
    public function ringkasan(Campaign $campaign): array
    {
        $total = $this->hasilDisetujui($campaign)
            ->toBase()   // hasil berupa angka biasa, bukan model
            ->selectRaw('COUNT(DISTINCT HasilPengerjaan.idPendaftaran) AS influencer')
            ->selectRaw('COALESCE(SUM(jumlahViews), 0) AS views')
            ->selectRaw('COALESCE(SUM(jumlahLikes), 0) AS likes')
            ->selectRaw('COALESCE(SUM(jumlahKomentar), 0) AS komentar')
            ->first();

        return [
            'influencer' => (int) $total->influencer,
            'views' => (int) $total->views,
            'likes' => (int) $total->likes,
            'komentar' => (int) $total->komentar,
        ];
    }

    // Rincian per influencer (wireframe Gambar 76: "Rincian per Influencer"), views terbanyak di atas.
    // Nama halaman paginasinya dibedakan ("halaman-rincian") supaya tidak bentrok dengan paginasi daftar campaign.
    public function rincian(Campaign $campaign, int $perHalaman = 10): LengthAwarePaginator
    {
        return $this->hasilDisetujui($campaign)
            ->with(['pendaftaran.influencer.pengguna', 'akunSosmed'])
            ->orderByDesc('jumlahViews')
            ->paginate($perHalaman, ['*'], 'halaman-rincian')
            ->withQueryString();
    }

    private function hasilDisetujui(Campaign $campaign): Builder
    {
        return HasilPengerjaan::query()
            ->where('statusInsight', 'disetujui')
            ->whereHas('pendaftaran', fn ($q) => $q->where('idCampaign', $campaign->idCampaign));
    }
}