<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Tabel 38
class HasilPengerjaan extends ModelAura
{
    protected $table = 'HasilPengerjaan';
    protected $primaryKey = 'idHasil';

    protected $fillable = [
        'idPendaftaran', 'idAkun', 'draftKonten', 'tanggalPengirimanDraft',
        'linkKonten', 'fileInsight', 'jumlahViews', 'jumlahLikes', 'jumlahKomentar',
        'jumlahPengikut', 
        'tanggalPengirimanInsight', 'statusDraft', 'statusInsight',
    ];

    protected function casts(): array
    {
        return [
            'tanggalPengirimanDraft' => 'date',
            'tanggalPengirimanInsight' => 'date',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranCampaign::class, 'idPendaftaran');
    }

    public function akunSosmed(): BelongsTo
    {
        return $this->belongsTo(AkunSosmed::class, 'idAkun');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'idHasil');
    }
}