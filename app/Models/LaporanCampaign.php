<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tabel 36
class LaporanCampaign extends ModelAura
{
    protected $table = 'LaporanCampaign';
    protected $primaryKey = 'idLaporan';
    protected $fillable = ['idCampaign', 'idPengguna', 'catatanEvaluasi', 'tanggalSelesaiCampaign', 'statusLaporan'];

    protected function casts(): array
    {
        return ['tanggalSelesaiCampaign' => 'date'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'idCampaign');
    }
}