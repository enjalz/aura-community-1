<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tabel 31
class AkunSosmed extends ModelAura
{
    protected $table = 'AkunSosmed';
    protected $primaryKey = 'idAkun';
    protected $fillable = ['idPengguna', 'platform', 'username', 'linkProfil', 'jumlahPengikut'];

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class, 'idPengguna');
    }
}