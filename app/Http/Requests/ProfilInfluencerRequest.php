<?php

namespace App\Http\Requests;

use App\Http\Requests\Admin\InfluencerRequest;
use App\Models\AkunSosmed;
use App\Models\Influencer;
use App\Models\RekeningInfluencer;
use Illuminate\Validation\Validator;

// Validasi Mengelola Profil (Tabel 7) untuk Influencer (wireframe Gambar 86).
// Turunan dari InfluencerRequest (Modul 3B), jadi aturan data diri dan akun media sosial dipakai ulang.
// Tambahannya: password saat ini, rekening, dan demografi audiens yang wajib diisi.
class ProfilInfluencerRequest extends InfluencerRequest
{
    public function rules(): array
    {
        $aturan = parent::rules();

        // Di profil, demografi wajib diisi untuk setiap akun yang diisi.
        // Screenshot diperiksa terpisah di after(): wajib kalau belum ada atau data demografi diubah.
        foreach (config('aura.platform') as $kunci => $nama) {
            $aturan = array_merge($aturan, AkunSosmed::aturanDemografi($kunci, wajibIsian: true, wajibBerkas: false));
        }

        // Rekening yang sudah ada tidak boleh dikosongkan, karena dipakai untuk pembayaran fee.
        // Kalau belum ada, boleh dikosongkan seluruhnya, atau diisi keempatnya sekaligus.
        // Ketik ulang nomor dan centang pernyataan hanya wajib kalau rekening baru atau diubah.
        $rekening = $this->user()->influencer->rekening;

        $aturan = array_merge($aturan, RekeningInfluencer::aturanValidasi(
            $rekening ? 'required' : 'nullable',
            $this->rekeningBerubah($rekening)
        ));

        $aturan['passwordSaatIni'] = ['nullable', 'required_with:password', 'current_password'];

        return $aturan;
    }

    // Use Case Scenario Mengelola Profil (Tabel 7), alternative scenario:
    // screenshot wajib diunggah kalau belum pernah ada, atau kalau data demografi diubah.
    public function after(): array
    {
        return [
            ...parent::after(),
            function (Validator $validator) {
                $influencer = $this->influencerYangDiubah();

                foreach (config('aura.platform') as $kunci => $nama) {
                    if (! $this->filled("{$kunci}_username") || $this->hasFile("{$kunci}_demografi")) {
                        continue;
                    }

                    $akun = $influencer?->akunSosmed->firstWhere('platform', $nama);

                    if (! $akun?->fileDemografi) {
                        $validator->errors()->add("{$kunci}_demografi", "Screenshot insight audiens {$nama} wajib diunggah.");
                        continue;
                    }

                    $berubah = trim((string) $this->input("{$kunci}_domisili")) !== (string) $akun->domisiliAudiens
                        || (string) $this->input("{$kunci}_usia") !== (string) $akun->usiaDominan
                        || (string) $this->input("{$kunci}_gender") !== (string) $akun->genderDominan;

                    if ($berubah) {
                        $validator->errors()->add("{$kunci}_demografi", "Unggah screenshot insight audiens {$nama} terbaru karena data demografi diubah.");
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return RekeningInfluencer::pesanValidasi() + [
            'passwordSaatIni.required_with' => 'Isi password saat ini untuk mengganti password.',
            'current_password' => 'Password saat ini salah.',
        ] + parent::messages();
    }

    public function attributes(): array
    {
        return parent::attributes() + RekeningInfluencer::namaKolom();
    }

    // Di InfluencerRequest, influencer diambil dari URL (Admin mengubah data orang lain).
    // Di profil, influencer adalah pengguna yang sedang login.
    protected function influencerYangDiubah(): ?Influencer
    {
        return $this->user()->influencer;
    }

    // True kalau rekening baru diisi, atau salah satu dari empat datanya berbeda dari yang tersimpan
    private function rekeningBerubah(?RekeningInfluencer $rekening): bool
    {
        if (! $rekening) {
            return $this->filled('nomorRekening');
        }

        return (string) $this->input('jenisRekening') !== $rekening->jenisRekening
            || (string) $this->input('namaPenyedia') !== $rekening->namaPenyedia
            || (string) $this->input('nomorRekening') !== $rekening->nomorRekening
            || trim((string) $this->input('namaPemilik')) !== $rekening->namaPemilik;
    }
}