<?php

namespace App\Models;

use App\Services\RekapCampaign;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

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

    // Class Diagram (Gambar 7): hitungEngagementRate().
    // Mengembalikan daftar influencer beserta ER dan label performanya (di_atas / di_bawah).
    // Perhitungannya ada di RekapCampaign supaya rumusnya hanya ditulis di satu tempat.
    public function hitungEngagementRate(): Collection
    {
        return app(RekapCampaign::class)->performa($this->campaign)['perInfluencer'];
    }

    // Class Diagram (Gambar 7): hitungRataRataER().
    // Rata-rata ER seluruh influencer di campaign laporan ini (Bab 2.1.4).
    public function hitungRataRataER(): ?float
    {
        return app(RekapCampaign::class)->performa($this->campaign)['rataRataEr'];
    }
}