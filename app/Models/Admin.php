<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tabel 29
class Admin extends ModelAura
{
    protected $table = 'Admin';
    protected $primaryKey = 'idPengguna';
    public $incrementing = false;
    protected $fillable = ['idPengguna'];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'idPengguna');
    }
}