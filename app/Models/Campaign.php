<?php

namespace App\Models;

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
        'lokasiCampaign', 'feePerInfluencer', 'tanggalMulai', 'tanggalSelesai',
        'statusCampaign', 'kuotaInfluencer',
    ];

    protected function casts(): array
    {
        return [
            'feePerInfluencer' => 'decimal:2',
            'tanggalMulai' => 'date',
            'tanggalSelesai' => 'date',
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
}