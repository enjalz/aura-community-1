<?php

namespace App\Http\Requests;

use App\Http\Requests\Admin\InfluencerRequest;
use App\Models\Influencer;
use Illuminate\Validation\Rule;

// Validasi Mengelola Profil (Tabel 7) untuk Influencer (wireframe Gambar 86).
// Turunan dari InfluencerRequest (Modul 3B), jadi aturan data diri dan akun media sosial dipakai ulang.
// Tambahannya: password saat ini dan rekening.
class ProfilInfluencerRequest extends InfluencerRequest
{
    public function rules(): array
    {
        $aturan = parent::rules();

        // Rekening yang sudah ada tidak boleh dikosongkan, karena dipakai untuk pembayaran fee.
        // Kalau belum ada, boleh dikosongkan seluruhnya, atau diisi keempatnya sekaligus.
        $wajib = $this->user()->influencer->rekening ? 'required' : 'nullable';
        $daftarPenyedia = collect(config('aura.kanalPembayaran'))->flatMap(fn ($daftar) => array_keys($daftar))->all();

        $aturan['jenisRekening'] = [$wajib, 'required_with:namaPenyedia,nomorRekening,namaPemilik', Rule::in(['bank', 'ewallet'])];
        $aturan['namaPenyedia'] = [$wajib, 'required_with:jenisRekening,nomorRekening,namaPemilik', Rule::in($daftarPenyedia)];
        $aturan['nomorRekening'] = [$wajib, 'required_with:jenisRekening,namaPenyedia,namaPemilik', 'regex:/^[0-9]{5,30}$/'];
        $aturan['namaPemilik'] = [$wajib, 'required_with:jenisRekening,namaPenyedia,nomorRekening', 'string', 'max:100'];

        $aturan['passwordSaatIni'] = ['nullable', 'required_with:password', 'current_password'];

        return $aturan;
    }

    public function messages(): array
    {
        return [
            'nomorRekening.regex' => 'Nomor Rekening hanya berisi angka, 5 sampai 30 digit.',
            'passwordSaatIni.required_with' => 'Isi password saat ini untuk mengganti password.',
            'current_password' => 'Password saat ini salah.',
        ] + parent::messages();
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'jenisRekening' => 'Jenis Rekening',
            'namaPenyedia' => 'Nama Penyedia',
            'nomorRekening' => 'Nomor Rekening',
            'namaPemilik' => 'Nama Pemilik',
        ];
    }

    // Di InfluencerRequest, influencer diambil dari URL (Admin mengubah data orang lain).
    // Di profil, influencer adalah pengguna yang sedang login.
    protected function influencerYangDiubah(): ?Influencer
    {
        return $this->user()->influencer;
    }
}