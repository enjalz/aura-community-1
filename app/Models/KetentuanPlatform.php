<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tabel 34
class KetentuanPlatform extends ModelAura
{
    protected $table = 'KetentuanPlatform';
    protected $primaryKey = 'idKetentuan';
    protected $fillable = ['idCampaign', 'platform', 'jenisKonten', 'jumlahPostingan'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'idCampaign');
    }
}