<?php

namespace App\Http\Requests\Admin;

use App\Models\HasilPengerjaan;
use App\Models\Influencer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

// Validasi form Tambah dan Ubah Data Influencer (Tabel 10, langkah 4: "Sistem memvalidasi data").
// Dipakai oleh DataInfluencerController@store dan @update.
class InfluencerRequest extends FormRequest
{
    // Hak akses sudah dijaga middleware peran:admin di routes/web.php
    public function authorize(): bool
    {
        return true;
    }

    // Merapikan input sebelum divalidasi: email huruf kecil, username tanpa @ di depan
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
            'instagram_username' => ltrim(trim((string) $this->input('instagram_username')), '@'),
            'tiktok_username' => ltrim(trim((string) $this->input('tiktok_username')), '@'),
        ]);
    }

    public function rules(): array
    {
        // Saat Ubah, route membawa influencer yang sedang diubah. Saat Tambah, nilainya null.
        $influencer = $this->influencerYangDiubah();

        $aturan = [
            'nama' => ['required', 'string', 'max:100'],
            // ignore(): email milik influencer ini sendiri tidak dianggap "sudah terdaftar"
            'email' => ['required', 'email', 'max:100', Rule::unique('Pengguna', 'email')->ignore($influencer?->idPengguna, 'idPengguna')],
            // Tambah: password wajib. Ubah: boleh dikosongkan (artinya tidak diganti).
            'password' => [$influencer ? 'nullable' : 'required', 'string', 'min:8'],
            'noTelepon' => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'kategori' => ['required', Rule::in(config('aura.kategori'))],
            'alamat' => ['required', 'string', 'max:1000'],
        ];

        foreach (config('aura.platform') as $kunci => $nama) {
            $idAkunLama = $influencer?->akunSosmed->firstWhere('platform', $nama)?->idAkun;

            $aturan["{$kunci}_username"] = [
                'nullable', 'string', 'max:100',
                "required_with:{$kunci}_pengikut,{$kunci}_link",
                Rule::unique('AkunSosmed', 'username')->where('platform', $nama)->ignore($idAkunLama, 'idAkun'),
            ];
            $aturan["{$kunci}_pengikut"] = ['nullable', 'integer', 'min:0', 'max:2147483647', "required_with:{$kunci}_username,{$kunci}_link"];
            $aturan["{$kunci}_link"] = ['nullable', 'url:http,https', 'max:255', "required_with:{$kunci}_username,{$kunci}_pengikut"];
        }

        return $aturan;
    }

    // Pemeriksaan tambahan setelah aturan dasar di atas
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Minimal satu akun media sosial (sama seperti registrasi)
                if (! $this->filled('instagram_username') && ! $this->filled('tiktok_username')) {
                    $validator->errors()->add('sosmed', 'Isi minimal satu akun media sosial.');
                }

                // Akun yang sudah dipakai di hasil pengerjaan campaign tidak boleh dikosongkan,
                // karena tabel HasilPengerjaan masih merujuk ke akun itu (idAkun).
                $influencer = $this->influencerYangDiubah();

                foreach (config('aura.platform') as $kunci => $nama) {
                    $akun = $influencer?->akunSosmed->firstWhere('platform', $nama);

                    if ($akun && ! $this->filled("{$kunci}_username")
                        && HasilPengerjaan::where('idAkun', $akun->idAkun)->exists()) {
                        $validator->errors()->add("{$kunci}_username", "Akun {$nama} sudah dipakai di hasil pengerjaan campaign, sehingga tidak dapat dikosongkan.");
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_with' => ':attribute wajib diisi karena data lain pada platform ini sudah diisi.',
            'email' => 'Format email tidak valid.',
            'unique' => ':attribute sudah terdaftar.',
            'min.string' => ':attribute minimal :min karakter.',
            'max.string' => ':attribute maksimal :max karakter.',
            'min.numeric' => ':attribute tidak boleh negatif.',
            'in' => 'Pilihan :attribute tidak valid.',
            'integer' => ':attribute harus berupa angka bulat.',
            'url' => ':attribute harus berupa tautan lengkap, contoh https://instagram.com/namaanda.',
            'regex' => 'Format :attribute tidak valid. Gunakan angka, 8 sampai 20 digit.',
        ];
    }

    // Nama kolom yang tampil di pesan error
    public function attributes(): array
    {
        $nama = [
            'nama' => 'Nama',
            'email' => 'Email',
            'password' => 'Password',
            'noTelepon' => 'No. Telepon',
            'kategori' => 'Kategori',
            'alamat' => 'Alamat',
        ];

        foreach (config('aura.platform') as $kunci => $platform) {
            $nama["{$kunci}_username"] = "Username {$platform}";
            $nama["{$kunci}_pengikut"] = "Jumlah pengikut {$platform}";
            $nama["{$kunci}_link"] = "Link profil {$platform}";
        }

        return $nama;
    }

    protected function influencerYangDiubah(): ?Influencer
    {
        $influencer = $this->route('influencer');

        return $influencer instanceof Influencer ? $influencer : null;
    }
}