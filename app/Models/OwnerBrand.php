<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// Tabel 28
class OwnerBrand extends ModelAura
{
    protected $table = 'OwnerBrand';
    protected $primaryKey = 'idPengguna';
    public $incrementing = false;
    protected $fillable = ['idPengguna', 'namaBrand', 'namaPerusahaan'];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'idPengguna');
    }

    // Campaign yang boleh dilihat owner brand ini, lewat tabel AksesCampaign
    public function campaign(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'AksesCampaign', 'idPengguna', 'idCampaign');
    }
}