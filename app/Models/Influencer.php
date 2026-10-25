<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// Tabel 30
class Influencer extends ModelAura
{
    protected $table = 'Influencer';
    protected $primaryKey = 'idPengguna';
    public $incrementing = false;

    protected $fillable = [
        'idPengguna', 'kategori', 'statusVerifikasi',
        'statusBlacklist', 'alasanBlacklist', 'tanggalBlacklist',
    ];

    protected function casts(): array
    {
        return [
            'statusBlacklist' => 'boolean',
            'tanggalBlacklist' => 'date',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'idPengguna');
    }

    public function akunSosmed(): HasMany
    {
        return $this->hasMany(AkunSosmed::class, 'idPengguna');
    }

    public function rekening(): HasOne
    {
        return $this->hasOne(RekeningInfluencer::class, 'idPengguna');
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(PendaftaranCampaign::class, 'idPengguna');
    }

        // Menyimpan akun Instagram dan TikTok dari form (kunci field: instagram_username, tiktok_pengikut, dan seterusnya).
    // Diisi = dibuat atau diperbarui. Dikosongkan = dihapus.
    public function simpanAkunSosmed(array $data): void
    {
        foreach (config('aura.platform') as $kunci => $nama) {
            $kondisi = ['idPengguna' => $this->idPengguna, 'platform' => $nama];

            if (empty($data["{$kunci}_username"])) {
                AkunSosmed::where($kondisi)->delete();
                continue;
            }

            AkunSosmed::updateOrCreate($kondisi, [
                'username' => $data["{$kunci}_username"],
                'linkProfil' => $data["{$kunci}_link"],
                'jumlahPengikut' => $data["{$kunci}_pengikut"],
            ]);
        }
    }
}