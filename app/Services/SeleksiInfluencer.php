<?php

namespace App\Services;

use App\Models\AkunSosmed;
use App\Models\Campaign;
use App\Models\HasilPengerjaan;
use App\Models\PendaftaranCampaign;
use Illuminate\Support\Collection;

// Data pendukung seleksi pendaftar campaign (subbab 2.1.4):
// kecocokan, ER, view rate, label perlu diperiksa, dan estimasi jangkauan.
class SeleksiInfluencer
{
    private ?array $rataRataRentang = null;

    public function daftarPendaftar(Campaign $campaign): Collection
    {
        $campaign->loadMissing([
            'ketentuanPlatform',
            'pendaftaran.influencer.pengguna',
            'pendaftaran.influencer.akunSosmed.hasilPengerjaan',
        ]);

        $rataRata = $this->rataRataViewRatePerRentang();

        return $campaign->pendaftaran->map(function (PendaftaranCampaign $pendaftaran) use ($campaign, $rataRata) {
            // Pakai campaign yang sudah dimuat, supaya tidak query ulang per baris
            $pendaftaran->setRelation('campaign', $campaign);

            $akun  = $pendaftaran->akunRelevan();
            $hasil = $akun->flatMap(fn (AkunSosmed $a) => $a->hasilDisetujui());

            $daftarViewRate = $hasil
                ->map(fn (HasilPengerjaan $h) => $h->viewRate())
                ->filter(fn ($nilai) => $nilai !== null);

            $daftarEstimasi = $akun
                ->map(fn (AkunSosmed $a) => $a->hitungEstimasiJangkauan())
                ->filter(fn ($nilai) => $nilai !== null);

            return (object) [
                'pendaftaran'    => $pendaftaran,
                'nama'           => $pendaftaran->influencer->pengguna->nama,
                'akun'           => $akun,
                'kecocokan'      => $pendaftaran->hitungKecocokan(),
                'er'             => RekapCampaign::hitungEr(
                    (int) $hasil->sum('jumlahLikes'),
                    (int) $hasil->sum('jumlahKomentar'),
                    (int) $hasil->sum('jumlahShare'),
                    (int) $hasil->sum('jumlahSave'),
                    (int) $hasil->sum('jumlahViews'),
                ),
                'viewRate'       => $daftarViewRate->isNotEmpty() ? round($daftarViewRate->avg(), 2) : null,
                'estimasi'       => $daftarEstimasi->isNotEmpty() ? (int) $daftarEstimasi->sum() : null,
                'perluDiperiksa' => $akun->contains(fn (AkunSosmed $a) => $a->cekIndikasiPengikut(
                    $rataRata[$a->platform . '|' . $a->rentangPengikut()] ?? null
                )),
            ];
        });
    }

    // Estimasi Jangkauan Campaign = jumlah estimasi influencer yang diterima
    public static function totalEstimasi(Collection $daftarPendaftar): ?int
    {
        $diterima = $daftarPendaftar->filter(
            fn ($baris) => $baris->pendaftaran->statusPendaftaran === 'diterima' && $baris->estimasi !== null
        );

        return $diterima->isNotEmpty() ? (int) $diterima->sum('estimasi') : null;
    }

    public function estimasiCampaign(Campaign $campaign): ?int
    {
        return self::totalEstimasi($this->daftarPendaftar($campaign));
    }

    // Rata-rata view rate seluruh akun yang punya riwayat, dikelompokkan per platform dan rentang pengikut
    private function rataRataViewRatePerRentang(): array
    {
        if ($this->rataRataRentang !== null) {
            return $this->rataRataRentang;
        }

        $disetujui = fn ($q) => $q->where('statusInsight', 'disetujui');

        return $this->rataRataRentang = AkunSosmed::whereHas('hasilPengerjaan', $disetujui)
            ->with(['hasilPengerjaan' => $disetujui])
            ->get()
            ->groupBy(fn (AkunSosmed $a) => $a->platform . '|' . $a->rentangPengikut())
            ->map(fn (Collection $grup) => $grup
                ->map(fn (AkunSosmed $a) => $a->hitungViewRateHistoris())
                ->filter(fn ($nilai) => $nilai !== null)
                ->avg())
            ->filter(fn ($nilai) => $nilai !== null)
            ->all();
    }
}