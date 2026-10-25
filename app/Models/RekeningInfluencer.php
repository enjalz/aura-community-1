<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tabel 32
class RekeningInfluencer extends ModelAura
{
    protected $table = 'RekeningInfluencer';
    protected $primaryKey = 'idRekening';
    protected $fillable = ['idPengguna', 'jenisRekening', 'namaPenyedia', 'nomorRekening', 'namaPemilik'];

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class, 'idPengguna');
    }
}