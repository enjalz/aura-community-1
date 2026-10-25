<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
}