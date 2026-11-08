<?php

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

// Tabel 32
class RekeningInfluencer extends ModelAura
{
    protected $table = 'RekeningInfluencer';
    protected $primaryKey = 'idRekening';
    protected $fillable = ['idPengguna', 'jenisRekening', 'namaPenyedia', 'nomorRekening', 'namaPemilik'];

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class, 'idPengguna');
    }

    // Aturan validasi rekening. Dipakai di Profil (ProfilInfluencerRequest) dan Daftar Campaign (CampaignController),
    // supaya kesalahan ketik rekening dicegah sejak awal, sebelum pembayaran diproses.
    // $wajib: 'required' kalau rekening harus diisi, 'nullable' kalau boleh dikosongkan.
    // $perluKonfirmasi: true kalau rekening baru atau diubah, sehingga nomor wajib diketik ulang dan pernyataan wajib dicentang.
    public static function aturanValidasi(string $wajib, bool $perluKonfirmasi): array
    {
        $daftarPenyedia = collect(config('aura.kanalPembayaran'))->flatMap(fn ($daftar) => array_keys($daftar))->all();

        $aturan = [
            'jenisRekening' => [$wajib, 'required_with:namaPenyedia,nomorRekening,namaPemilik', Rule::in(['bank', 'ewallet'])],
            'namaPenyedia' => [$wajib, 'required_with:jenisRekening,nomorRekening,namaPemilik', Rule::in($daftarPenyedia)],
            'nomorRekening' => [$wajib, 'required_with:jenisRekening,namaPenyedia,namaPemilik', 'regex:/^[0-9]+$/', self::aturanFormatNomor()],
            'namaPemilik' => [$wajib, 'required_with:jenisRekening,namaPenyedia,nomorRekening', 'string', 'max:100', "regex:/^[\pL\s.'\-]+$/u"],
        ];

        if ($perluKonfirmasi) {
            $aturan['nomorRekening'][] = 'confirmed';          // harus sama dengan nomorRekening_confirmation
            $aturan['konfirmasiRekening'] = ['accepted'];     // checkbox pernyataan data rekening benar
        }

        return $aturan;
    }

    public static function pesanValidasi(): array
    {
        return [
            'nomorRekening.regex' => 'Nomor Rekening hanya berisi angka, tanpa spasi atau tanda baca.',
            'nomorRekening.confirmed' => 'Ketik ulang nomor rekening tidak sama. Periksa kembali nomornya.',
            'namaPemilik.regex' => 'Nama Pemilik hanya berisi huruf, spasi, titik, tanda petik, atau tanda hubung.',
            'konfirmasiRekening.accepted' => 'Centang pernyataan bahwa data rekening sudah benar.',
        ];
    }

    public static function namaKolom(): array
    {
        return [
            'jenisRekening' => 'Jenis Rekening',
            'namaPenyedia' => 'Nama Penyedia',
            'nomorRekening' => 'Nomor Rekening',
            'nomorRekening_confirmation' => 'Ketik Ulang Nomor Rekening',
            'namaPemilik' => 'Nama Pemilik',
            'konfirmasiRekening' => 'Konfirmasi Rekening',
        ];
    }

    // Kelompok penyedia dari config aura.kanalPembayaran: 'Bank', 'E-Wallet', atau null kalau tidak dikenal
    public static function kelompokPenyedia(string $namaPenyedia): ?string
    {
        foreach (config('aura.kanalPembayaran') as $kelompok => $daftarPenyedia) {
            if (array_key_exists($namaPenyedia, $daftarPenyedia)) {
                return $kelompok;
            }
        }

        return null;
    }

    // Cek format nomor sesuai penyedia yang dipilih:
    // - jenis rekening harus cocok dengan penyedia (BCA = bank, GoPay = e-wallet)
    // - e-wallet: nomor HP, diawali 08, 10 sampai 13 digit
    // - bank: panjang sesuai config aura.panjangRekening
    private static function aturanFormatNomor(): Closure
    {
        return function (string $atribut, $nilai, Closure $gagal) {
            $nomor = (string) $nilai;
            $penyedia = (string) request('namaPenyedia');
            $jenis = (string) request('jenisRekening');
            $kelompok = self::kelompokPenyedia($penyedia);

            // Bukan angka atau penyedia tidak dikenal: sudah ditangani aturan lain
            if (! ctype_digit($nomor) || $kelompok === null) {
                return;
            }

            if ($jenis === 'bank' && $kelompok !== 'Bank') {
                $gagal("{$penyedia} bukan bank. Ubah Jenis Rekening menjadi E-Wallet.");

                return;
            }

            if ($jenis === 'ewallet' && $kelompok !== 'E-Wallet') {
                $gagal("{$penyedia} bukan e-wallet. Ubah Jenis Rekening menjadi Bank.");

                return;
            }

            // Mode Test Xendit: nomor simulasi tetap diizinkan untuk menguji pembayaran gagal
            $modeTest = str_starts_with((string) config('services.xendit.secret'), 'xnd_development_');

            if ($modeTest && in_array($nomor, config('aura.nomorSimulasiXendit', []), true)) {
                return;
            }

            if ($kelompok === 'E-Wallet') {
                if (! preg_match('/^08[0-9]{8,11}$/', $nomor)) {
                    $gagal("Nomor {$penyedia} adalah nomor HP yang terdaftar, diawali 08 dan berisi 10 sampai 13 digit.");
                }

                return;
            }

            [$minimal, $maksimal] = config("aura.panjangRekening.{$penyedia}", [5, 30]);
            $panjang = strlen($nomor);

            if ($panjang < $minimal || $panjang > $maksimal) {
                $gagal($minimal === $maksimal
                    ? "Nomor rekening {$penyedia} terdiri dari {$minimal} digit."
                    : "Nomor rekening {$penyedia} terdiri dari {$minimal} sampai {$maksimal} digit.");
            }
        };
    }
}