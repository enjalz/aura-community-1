<?php

namespace App\Models;

use App\Services\RekapCampaign;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Tabel 38
class HasilPengerjaan extends ModelAura
{
    protected $table = 'HasilPengerjaan';
    protected $primaryKey = 'idHasil';

    // $fillable = daftar kolom yang BOLEH diisi lewat create()/update().
    protected $fillable = [
        'idPendaftaran', 'idAkun', 'draftKonten', 'tanggalPengirimanDraft',
        'linkKonten', 'fileInsight', 'jumlahViews', 'jumlahLikes', 'jumlahKomentar',
        'jumlahShare', 'jumlahSave', // interaksi tambahan untuk engagement rate
        'jumlahPengikut',            // pengikut akun saat insight dikirim
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

    // ER satu konten: (likes + komentar + share + save) / views x 100%
    public function engagementRate(): ?float
    {
        return RekapCampaign::hitungEr(
            $this->jumlahLikes, $this->jumlahKomentar, $this->jumlahShare, $this->jumlahSave, $this->jumlahViews
        );
    }

    // View rate satu konten. Null kalau data pengikut belum ada.
    public function viewRate(): ?float
    {
        return RekapCampaign::hitungViewRate($this->jumlahViews, $this->jumlahPengikut);
    }
}