<?php

namespace App\Models;

use App\Services\SeleksiInfluencer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// Tabel 33
class Campaign extends ModelAura
{
    protected $table = 'Campaign';
    protected $primaryKey = 'idCampaign';

    protected $fillable = [
        'idPengguna', 'namaCampaign', 'deskripsi', 'targetAudiens', 'kategoriInfluencer',
        'lokasiCampaign', 'targetUsia', 'minimalPengikut', 'feePerInfluencer',
        'tanggalMulai', 'tanggalSelesai', 'tanggalUnggahMulai', 'tanggalUnggahSelesai',
        'statusCampaign', 'kuotaInfluencer',
    ];

    protected function casts(): array
    {
        return [
            'feePerInfluencer' => 'decimal:2',
            'tanggalMulai' => 'date',
            'tanggalSelesai' => 'date',
            'tanggalUnggahMulai' => 'date',
            'tanggalUnggahSelesai' => 'date',
            'minimalPengikut' => 'integer',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'idPengguna');
    }

    public function ketentuanPlatform(): HasMany
    {
        return $this->hasMany(KetentuanPlatform::class, 'idCampaign');
    }

    public function ownerBrand(): BelongsToMany
    {
        return $this->belongsToMany(OwnerBrand::class, 'AksesCampaign', 'idCampaign', 'idPengguna');
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(PendaftaranCampaign::class, 'idCampaign');
    }

    public function laporan(): HasOne
    {
        return $this->hasOne(LaporanCampaign::class, 'idCampaign');
    }

    // Total estimasi jangkauan dari influencer yang diterima
    public function hitungEstimasiJangkauanCampaign(): ?int
    {
        return app(SeleksiInfluencer::class)->estimasiCampaign($this);
    }

    // Contoh: "01/11/2026 (serempak)" atau "20/10/2026 - 25/10/2026"
    public function periodeUnggah(): ?string
    {
        if (! $this->tanggalUnggahMulai || ! $this->tanggalUnggahSelesai) {
            return null;
        }

        $mulai = $this->tanggalUnggahMulai->format('d/m/Y');
        $selesai = $this->tanggalUnggahSelesai->format('d/m/Y');

        return $mulai === $selesai ? "{$mulai}" : "{$mulai} - {$selesai}";
    }
}