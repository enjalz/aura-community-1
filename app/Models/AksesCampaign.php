<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tabel 35
class AksesCampaign extends ModelAura
{
    protected $table = 'AksesCampaign';
    protected $primaryKey = 'idAkses';
    protected $fillable = ['idCampaign', 'idAdmin', 'idPengguna'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'idCampaign');
    }

    // Admin yang memberikan akses
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'idAdmin');
    }

    public function ownerBrand(): BelongsTo
    {
        return $this->belongsTo(OwnerBrand::class, 'idPengguna');
    }
}