<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tabel 39
class Feedback extends ModelAura
{
    protected $table = 'Feedback';
    protected $primaryKey = 'idFeedback';
    protected $fillable = ['idHasil', 'idPengguna', 'isiFeedback', 'tanggalFeedback'];

    protected function casts(): array
    {
        return ['tanggalFeedback' => 'date'];
    }

    public function hasilPengerjaan(): BelongsTo
    {
        return $this->belongsTo(HasilPengerjaan::class, 'idHasil');
    }
}