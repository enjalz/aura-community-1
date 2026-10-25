<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\RekeningInfluencer;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

// Penghubung ke Xendit Payouts API (https://docs.xendit.co/apidocs/create-payout).
// Semua hasil dikembalikan dalam bentuk yang sama:
// ['id' => id payout atau null, 'status' => pending|berhasil|gagal, 'pesan' => keterangan, 'diperbarui' => waktu dari Xendit]
class XenditPayout
{
    // Status Xendit => status di tabel Pembayaran (Tabel 40)
    private const STATUS = [
        'ACCEPTED' => 'pending',
        'REQUESTED' => 'pending',
        'SUCCEEDED' => 'berhasil',
        'FAILED' => 'gagal',
        'CANCELLED' => 'gagal',
        'REVERSED' => 'gagal',
    ];

    // Membuat payout baru (Tabel 24, langkah 4: Admin memproses pembayaran)
    public function buatPayout(Pembayaran $pembayaran, RekeningInfluencer $rekening): array
    {
        if (blank(config('services.xendit.secret'))) {
            return $this->gagal('XENDIT_SECRET_KEY belum diisi di file .env.');
        }

        $kodeKanal = $this->kodeKanal($rekening->namaPenyedia);

        if (! $kodeKanal) {
            return $this->gagal("Penyedia rekening \"{$rekening->namaPenyedia}\" belum didukung. Minta influencer memilih penyedia dari daftar.");
        }

        // reference_id sekaligus Idempotency-key. Ditambah waktu, supaya proses ulang setelah gagal memakai kunci baru.
        $referensi = 'aura-' . $pembayaran->idPembayaran . '-' . now()->timestamp;

        try {
            $respons = $this->klien()
                ->withHeaders(['Idempotency-key' => $referensi])
                ->post('/v2/payouts', [
                    'reference_id' => $referensi,
                    'channel_code' => $kodeKanal,
                    'channel_properties' => [
                        'account_number' => $rekening->nomorRekening,
                        'account_holder_name' => $rekening->namaPemilik,
                    ],
                    'amount' => (int) $pembayaran->jumlahPembayaran,   // IDR harus bilangan bulat
                    'currency' => 'IDR',
                    // Keterangan di mutasi rekening penerima. Dibuat pendek, karena beberapa bank membatasi 14 karakter.
                    'description' => 'Fee Aura #' . $pembayaran->idPembayaran,
                ]);
        } catch (ConnectionException) {
            return $this->gagal('Tidak dapat terhubung ke Xendit. Periksa koneksi internet.');
        }

        return $this->baca($respons);
    }

    // Menanyakan status payout yang masih pending (pengganti webhook untuk server lokal)
    public function cekStatus(string $idPayout): array
    {
        if (blank(config('services.xendit.secret'))) {
            return $this->gagal('XENDIT_SECRET_KEY belum diisi di file .env.');
        }

        try {
            $respons = $this->klien()->get('/v2/payouts/' . rawurlencode($idPayout));
        } catch (ConnectionException) {
            return $this->gagal('Tidak dapat terhubung ke Xendit. Periksa koneksi internet.');
        }

        return $this->baca($respons);
    }

    // Mencari kode kanal dari nama penyedia. Spasi dan huruf besar/kecil diabaikan,
    // jadi "GoPay", "gopay", dan "Go Pay" sama-sama menjadi ID_GOPAY.
    public function kodeKanal(string $namaPenyedia): ?string
    {
        $rapikan = fn (string $teks) => strtoupper(preg_replace('/[^a-z0-9]/i', '', $teks));

        foreach (config('aura.kanalPembayaran') as $daftarPenyedia) {
            foreach ($daftarPenyedia as $nama => $kode) {
                if ($rapikan($nama) === $rapikan($namaPenyedia)) {
                    return $kode;
                }
            }
        }

        return null;
    }

    // Autentikasi Xendit: Basic Auth dengan secret key sebagai username dan password kosong
    private function klien(): PendingRequest
    {
        return Http::baseUrl(config('services.xendit.url'))
            ->withBasicAuth(config('services.xendit.secret'), '')
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    private function baca(Response $respons): array
    {
        // Contoh error dari Xendit: {"error_code": "CHANNEL_CODE_NOT_SUPPORTED", "error_message": "..."}
        if ($respons->failed()) {
            $pesan = $respons->json('error_message') ?? 'Permintaan ditolak Xendit (HTTP ' . $respons->status() . ').';

            return $this->gagal($pesan);
        }

        return [
            'id' => $respons->json('id'),
            'status' => self::STATUS[$respons->json('status')] ?? 'pending',
            'pesan' => $respons->json('failure_code'),   // contoh: INVALID_DESTINATION
            'diperbarui' => $respons->json('updated'),
        ];
    }

    private function gagal(string $pesan): array
    {
        return ['id' => null, 'status' => 'gagal', 'pesan' => $pesan, 'diperbarui' => null];
    }
}