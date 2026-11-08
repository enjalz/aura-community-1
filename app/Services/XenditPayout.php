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
// ['id' => id payout atau null, 'status' => pending|berhasil|gagal, 'pesan' => keterangan,
//  'kode' => kode kegagalan atau null, 'diperbarui' => waktu dari Xendit]
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

    // Kode kegagalan yang berarti rekening tujuan bermasalah, jadi influencer perlu mengganti rekening.
    // Kode lain (misalnya saldo Xendit kurang atau gangguan bank) cukup diproses ulang oleh Admin.
    private const MASALAH_REKENING = [
        'INVALID_DESTINATION' => 'Nomor rekening atau nama pemilik tidak dikenali oleh bank/e-wallet tujuan.',
        'REJECTED_BY_CHANNEL' => 'Bank/e-wallet tujuan menolak transfer, misalnya karena rekening tidak aktif atau diblokir.',
        'DESTINATION_MAXIMUM_LIMIT' => 'Saldo e-wallet tujuan sudah mencapai batas maksimum.',
        'PENYEDIA_TIDAK_DIDUKUNG' => 'Bank/e-wallet yang dipilih belum didukung untuk pembayaran.',
    ];

    // Membuat payout baru (Tabel 24, langkah 4: Admin memproses pembayaran)
    public function buatPayout(Pembayaran $pembayaran, RekeningInfluencer $rekening): array
    {
        if (blank(config('services.xendit.secret'))) {
            return $this->gagal('XENDIT_SECRET_KEY belum diisi di file .env.');
        }

        $kodeKanal = $this->kodeKanal($rekening->namaPenyedia);

        if (! $kodeKanal) {
            return $this->gagal(
                "Penyedia rekening \"{$rekening->namaPenyedia}\" belum didukung. Minta influencer memilih penyedia dari daftar.",
                'PENYEDIA_TIDAK_DIDUKUNG'
            );
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

    // True kalau kegagalan disebabkan rekening tujuan, sehingga influencer perlu mengganti rekening
    public function masalahRekening(?string $kode): bool
    {
        return $kode !== null && array_key_exists($kode, self::MASALAH_REKENING);
    }

    // Penjelasan kegagalan rekening dalam bahasa yang mudah dipahami influencer
    public function alasanRekening(?string $kode): string
    {
        return self::MASALAH_REKENING[$kode] ?? 'Rekening tujuan tidak dapat menerima transfer.';
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

            return $this->gagal($pesan, $respons->json('error_code'));
        }

        return [
            'id' => $respons->json('id'),
            'status' => self::STATUS[$respons->json('status')] ?? 'pending',
            'pesan' => $respons->json('failure_code'),   // contoh: INVALID_DESTINATION
            'kode' => $respons->json('failure_code'),
            'diperbarui' => $respons->json('updated'),
        ];
    }

    private function gagal(string $pesan, ?string $kode = null): array
    {
        return ['id' => null, 'status' => 'gagal', 'pesan' => $pesan, 'kode' => $kode, 'diperbarui' => null];
    }
}