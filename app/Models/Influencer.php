<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    // Menyimpan akun Instagram dan TikTok dari form (kunci field: instagram_username, tiktok_pengikut, dst.).
    // Diisi = dibuat atau diperbarui. Dikosongkan = dihapus (beserta screenshot demografinya).
    // Dipakai oleh registrasi, profil influencer, dan form data influencer Admin.
    public function simpanAkunSosmed(array $data): void
    {
        $kolomDemografi = ['domisili' => 'domisiliAudiens', 'usia' => 'usiaDominan', 'gender' => 'genderDominan'];

        foreach (config('aura.platform') as $kunci => $nama) {
            $kondisi = ['idPengguna' => $this->idPengguna, 'platform' => $nama];
            $akunLama = AkunSosmed::where($kondisi)->first();

            if (empty($data["{$kunci}_username"])) {
                if ($akunLama) {
                    if ($akunLama->fileDemografi) {
                        Storage::disk('local')->delete($akunLama->fileDemografi);
                    }
                    $akunLama->delete();
                }
                continue;
            }

            $isian = [
                'username' => $data["{$kunci}_username"],
                'linkProfil' => $data["{$kunci}_link"],
                'jumlahPengikut' => $data["{$kunci}_pengikut"],
            ];

            // Field demografi hanya diubah kalau ada di form
            foreach ($kolomDemografi as $field => $kolom) {
                if (array_key_exists("{$kunci}_{$field}", $data)) {
                    $isian[$kolom] = filled($data["{$kunci}_{$field}"]) ? $data["{$kunci}_{$field}"] : null;
                }
            }

            // Screenshot baru menggantikan yang lama
            $berkas = $data["{$kunci}_demografi"] ?? null;

            if ($berkas instanceof UploadedFile) {
                $isian['fileDemografi'] = $berkas->store('demografi', 'local');

                if ($akunLama?->fileDemografi) {
                    Storage::disk('local')->delete($akunLama->fileDemografi);
                }
            }

            AkunSosmed::updateOrCreate($kondisi, $isian);
        }
    }
}