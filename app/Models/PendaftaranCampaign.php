<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

// Tabel 37
class PendaftaranCampaign extends ModelAura
{
    protected $table = 'PendaftaranCampaign';
    protected $primaryKey = 'idPendaftaran';
    protected $fillable = ['idCampaign', 'idPengguna', 'tanggalPendaftaran', 'statusPendaftaran'];

    protected function casts(): array
    {
        return ['tanggalPendaftaran' => 'date'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'idCampaign');
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class, 'idPengguna');
    }

    public function hasilPengerjaan(): HasMany
    {
        return $this->hasMany(HasilPengerjaan::class, 'idPendaftaran');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'idPendaftaran');
    }

    // Akun sosmed influencer yang platform-nya diminta campaign (KetentuanPlatform)
    public function akunRelevan(): Collection
    {
        $platform = $this->campaign->ketentuanPlatform->pluck('platform')->unique();
        $akun = $this->influencer->akunSosmed;

        return $platform->isEmpty() ? $akun : $akun->whereIn('platform', $platform->all())->values();
    }

    // Tingkat kecocokan influencer dengan target campaign (skor dari 4 kriteria)
    public function hitungKecocokan(): array
    {
        $campaign = $this->campaign;
        $akun = $this->akunRelevan();
        $lokasi = mb_strtolower(trim((string) $campaign->lokasiCampaign));

        $rincian = [
            'kategori' => $this->influencer->kategori === $campaign->kategoriInfluencer,

            // "Nasional" atau "Indonesia" berarti tidak membatasi lokasi
            'lokasi' => in_array($lokasi, ['', 'nasional', 'indonesia'], true)
                || $akun->contains(function (AkunSosmed $a) use ($lokasi) {
                    $domisili = mb_strtolower(trim((string) $a->domisiliAudiens));

                    return $domisili !== '' && (str_contains($domisili, $lokasi) || str_contains($lokasi, $domisili));
                }),

            'usia' => ! $campaign->targetUsia || $akun->contains('usiaDominan', $campaign->targetUsia),

            'pengikut' => $akun->contains(fn (AkunSosmed $a) => $a->jumlahPengikut >= $campaign->minimalPengikut),
        ];

        return [
            'skor'    => count(array_filter($rincian)),
            'maks'    => count($rincian),
            'rincian' => $rincian,
        ];
    }
}