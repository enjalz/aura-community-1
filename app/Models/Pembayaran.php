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
        'statusPembayaran', 'idTransaksiGateway', 'waktuKonfirmasi',
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
}