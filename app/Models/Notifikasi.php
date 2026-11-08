<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

// Notifikasi untuk Admin, Owner Brand, dan Influencer (tampil di lonceng topbar)
class Notifikasi extends ModelAura
{
    protected $table = 'Notifikasi';
    protected $primaryKey = 'idNotifikasi';

    protected $fillable = ['idPengguna', 'judul', 'pesan', 'tautan', 'sudahDibaca', 'waktuDibuat'];

    protected function casts(): array
    {
        return [
            'sudahDibaca' => 'boolean',
            'waktuDibuat' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'idPengguna');
    }

    // Mengirim notifikasi ke satu atau beberapa pengguna.
    // $tautan berupa path relatif, contoh route('influencer.profil', [], false),
    // supaya tetap benar walaupun alamat aplikasi (APP_URL) berubah.
    public static function kirim(int|array $idPengguna, string $judul, ?string $pesan = null, ?string $tautan = null): void
    {
        $waktu = now();

        $baris = collect((array) $idPengguna)->unique()->map(fn ($id) => [
            'idPengguna' => $id,
            'judul' => Str::limit($judul, 150, ''),
            'pesan' => $pesan !== null ? Str::limit($pesan, 1000) : null,
            'tautan' => $tautan,
            'sudahDibaca' => false,
            'waktuDibuat' => $waktu,
        ])->all();

        if ($baris) {
            static::insert($baris);
        }
    }

    // Mengirim notifikasi ke semua Admin
    public static function kirimKeAdmin(string $judul, ?string $pesan = null, ?string $tautan = null): void
    {
        static::kirim(Admin::pluck('idPengguna')->all(), $judul, $pesan, $tautan);
    }

    // Tautan ke halaman Hasil Pengerjaan influencer, langsung ke campaign dan tab platform-nya
    public static function tautanHasil(HasilPengerjaan $hasil): string
    {
        return route('influencer.hasil', [
            'pilih' => $hasil->idPendaftaran,
            'platform' => Str::lower($hasil->akunSosmed->platform),
        ], false);
    }
}