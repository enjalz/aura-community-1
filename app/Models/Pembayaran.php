<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tabel 40
class Pembayaran extends ModelAura
{
    protected $table = 'Pembayaran';
    protected $primaryKey = 'idPembayaran';

    protected $fillable = [
        'idPendaftaran', 'idPengguna', 'idRekening', 'jumlahPembayaran', 'tanggalPembayaran',
        'statusPembayaran', 'idTransaksiGateway', 'waktuKonfirmasi', 'kodeGagal', 'rekeningGagal',
    ];

    protected function casts(): array
    {
        return [
            'jumlahPembayaran' => 'decimal:2',
            'tanggalPembayaran' => 'date',
            'waktuKonfirmasi' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranCampaign::class, 'idPendaftaran');
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(RekeningInfluencer::class, 'idRekening');
    }

    // Ringkasan rekening, contoh "BCA · 121212 · Salsa Anindya".
    // Disimpan di rekeningGagal saat transfer gagal karena rekening, untuk mengetahui apakah influencer sudah menggantinya.
    public static function ringkasRekening(?RekeningInfluencer $rekening): ?string
    {
        return $rekening
            ? "{$rekening->namaPenyedia} · {$rekening->nomorRekening} · {$rekening->namaPemilik}"
            : null;
    }

    // True kalau gagal karena rekening dan influencer BELUM mengganti rekeningnya.
    // Dipakai untuk mengunci tombol bayar ulang di halaman Admin.
    public function menungguRekeningBaru(?RekeningInfluencer $rekeningSekarang): bool
    {
        if ($this->statusPembayaran !== 'gagal' || $this->rekeningGagal === null) {
            return false;
        }

        return ! $rekeningSekarang || self::ringkasRekening($rekeningSekarang) === $this->rekeningGagal;
    }
}