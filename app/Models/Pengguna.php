<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

// Tabel 27. Dipakai Laravel untuk login (lihat AUTH_MODEL di .env).
class Pengguna extends Authenticatable
{
    protected $table = 'Pengguna';
    protected $primaryKey = 'idPengguna';
    public $timestamps = false;

    // Tabel Pengguna tidak punya kolom remember_token
    protected $rememberTokenName = '';

    protected $fillable = ['nama', 'email', 'password', 'noTelepon', 'alamat', 'statusAkun'];
    protected $hidden = ['password'];

    // Peran disimpan setelah dicek sekali, supaya tidak query berulang dalam satu request
    private ?string $peranTersimpan = null;

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'idPengguna');
    }

    public function ownerBrand(): HasOne
    {
        return $this->hasOne(OwnerBrand::class, 'idPengguna');
    }

    public function influencer(): HasOne
    {
        return $this->hasOne(Influencer::class, 'idPengguna');
    }

    // Peran dibaca dari tabel turunan tempat data pengguna berada (generalisasi)
    public function peran(): ?string
    {
        $this->peranTersimpan ??= match (true) {
            $this->admin()->exists() => 'admin',
            $this->ownerBrand()->exists() => 'owner_brand',
            $this->influencer()->exists() => 'influencer',
            default => '',
        };

        return $this->peranTersimpan ?: null;
    }

        // Memperbarui data profil (Tabel 7). Dipakai oleh profil Admin, Owner Brand, dan Influencer.
    // Password hanya diganti kalau kolom password baru diisi.
    public function perbaruiProfil(array $data): void
    {
        $this->fill([
            'nama' => $data['nama'],
            'email' => $data['email'],
            'noTelepon' => $data['noTelepon'],
            'alamat' => $data['alamat'],
        ]);

        if (! empty($data['password'])) {
            $this->password = $data['password'];   // otomatis di-hash oleh cast 'hashed'
        }

        $this->save();
    }
}