<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\HasilPengerjaan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

// Menghitung statistik laporan campaign dari insight yang sudah DISETUJUI Admin.
// Dipakai oleh laporan Admin, laporan Owner Brand, dan dashboard.
// ER = (Likes + Komentar + Share + Save) / Views x 100%
class RekapCampaign
{
    // Rekapitulasi satu campaign. Kunci lama (influencer, views, likes, komentar) tetap ada
    // supaya dashboard dan komponen kartu-statistik tidak berubah.
    public function ringkasan(Campaign $campaign): array
    {
        $total = $this->hasilDisetujui($campaign)
            ->toBase()
            ->selectRaw('COUNT(DISTINCT HasilPengerjaan.idPendaftaran) AS influencer')
            ->selectRaw('COUNT(*) AS konten')
            ->selectRaw('COALESCE(SUM(jumlahViews), 0) AS views')
            ->selectRaw('COALESCE(SUM(jumlahLikes), 0) AS likes')
            ->selectRaw('COALESCE(SUM(jumlahKomentar), 0) AS komentar')
            ->selectRaw('COALESCE(SUM(jumlahShare), 0) AS share')
            ->selectRaw('COALESCE(SUM(jumlahSave), 0) AS save')
            ->first();

        return [
            'influencer' => (int) $total->influencer,
            'konten'     => (int) $total->konten,
            'views'      => (int) $total->views,
            'likes'      => (int) $total->likes,
            'komentar'   => (int) $total->komentar,
            'share'      => (int) $total->share,
            'save'       => (int) $total->save,
        ];
    }

    // Rincian per konten, views terbanyak di atas
    public function rincian(Campaign $campaign, int $perHalaman = 10): LengthAwarePaginator
    {
        return $this->hasilDisetujui($campaign)
            ->with(['pendaftaran.influencer.pengguna', 'akunSosmed'])
            ->orderByDesc('jumlahViews')
            ->paginate($perHalaman, ['*'], 'halaman-rincian')
            ->withQueryString();
    }

    // Performa per influencer dan ringkasan engagement satu campaign
    public function performa(Campaign $campaign): array
    {
        $semuaHasil = $this->hasilDisetujui($campaign)
            ->with(['pendaftaran.influencer.pengguna', 'akunSosmed'])
            ->get();

        // Satu pendaftaran = satu influencer. ER dihitung dari gabungan seluruh kontennya.
        $perInfluencer = $semuaHasil
            ->groupBy('idPendaftaran')
            ->map(function (Collection $kontenInfluencer) {
                $views    = (int) $kontenInfluencer->sum('jumlahViews');
                $likes    = (int) $kontenInfluencer->sum('jumlahLikes');
                $komentar = (int) $kontenInfluencer->sum('jumlahKomentar');
                $share    = (int) $kontenInfluencer->sum('jumlahShare');
                $save     = (int) $kontenInfluencer->sum('jumlahSave');
                $pendaftaran = $kontenInfluencer->first()->pendaftaran;

                return (object) [
                    'pendaftaran'  => $pendaftaran,
                    'nama'         => $pendaftaran->influencer->pengguna->nama,
                    'jumlahKonten' => $kontenInfluencer->count(),
                    'views'        => $views,
                    'likes'        => $likes,
                    'komentar'     => $komentar,
                    'share'        => $share,
                    'save'         => $save,
                    'er'           => self::hitungEr($likes, $komentar, $share, $save, $views),
                    'label'        => null,
                ];
            })
            ->values();

        // Rata-rata ER = (ER1 + ... + ERn) / n
        $daftarEr   = $perInfluencer->pluck('er')->filter(fn ($er) => $er !== null);
        $rataRataEr = $daftarEr->isNotEmpty() ? round($daftarEr->avg(), 2) : null;

        // Label: sama dengan atau di atas rata-rata = di_atas, selain itu di_bawah
        $perInfluencer = $perInfluencer
            ->each(function (object $baris) use ($rataRataEr) {
                if ($baris->er !== null && $rataRataEr !== null) {
                    $baris->label = $baris->er >= $rataRataEr ? 'di_atas' : 'di_bawah';
                }
            })
            ->sortByDesc(fn (object $baris) => $baris->er ?? -1)
            ->values();

        $erKeseluruhan = self::hitungEr(
            (int) $semuaHasil->sum('jumlahLikes'),
            (int) $semuaHasil->sum('jumlahKomentar'),
            (int) $semuaHasil->sum('jumlahShare'),
            (int) $semuaHasil->sum('jumlahSave'),
            (int) $semuaHasil->sum('jumlahViews'),
        );

        $daftarViewRate = $semuaHasil
            ->map(fn (HasilPengerjaan $hasil) => $hasil->viewRate())
            ->filter(fn ($nilai) => $nilai !== null);

        return [
            'perInfluencer'    => $perInfluencer,
            'rataRataEr'       => $rataRataEr,
            'erKeseluruhan'    => $erKeseluruhan,
            'rataRataViewRate' => $daftarViewRate->isNotEmpty() ? round($daftarViewRate->avg(), 2) : null,
            'diAtas'           => $perInfluencer->where('label', 'di_atas')->count(),
            'diBawah'          => $perInfluencer->where('label', 'di_bawah')->count(),
        ];
    }

    // RUMUS 1: ER = (Likes + Komentar + Share + Save) / Views x 100%.
    // Nilai yang kosong (data lama) dianggap 0. Hasil null kalau views kosong atau 0.
    public static function hitungEr(?int $likes, ?int $komentar, ?int $share, ?int $save, ?int $views): ?float
    {
        if (! $views) {
            return null;
        }

        $interaksi = ($likes ?? 0) + ($komentar ?? 0) + ($share ?? 0) + ($save ?? 0);

        return round($interaksi / $views * 100, 2);
    }

    // RUMUS 2: View Rate = Views / Pengikut x 100%. Null kalau data pengikut belum ada atau 0.
    public static function hitungViewRate(?int $views, ?int $pengikut): ?float
    {
        if (! $pengikut || $views === null) {
            return null;
        }

        return round($views / $pengikut * 100, 2);
    }

    private function hasilDisetujui(Campaign $campaign): Builder
    {
        return HasilPengerjaan::query()
            ->where('statusInsight', 'disetujui')
            ->whereHas('pendaftaran', fn ($q) => $q->where('idCampaign', $campaign->idCampaign));
    }
}