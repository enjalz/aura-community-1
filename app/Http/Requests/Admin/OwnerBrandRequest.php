<?php

namespace App\Http\Requests\Admin;

use App\Models\OwnerBrand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// Validasi form Buat Akun Owner Brand (Tabel 12, langkah 6) dan Ubah Data Owner Brand (Tabel 13, langkah 6).
// Dipakai oleh DataOwnerBrandController@store dan @update.
class OwnerBrandRequest extends FormRequest
{
    // Hak akses sudah dijaga middleware peran:admin di routes/web.php
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        // Saat Ubah, route membawa owner brand yang sedang diubah. Saat Buat, nilainya null.
        $ownerBrand = $this->route('ownerBrand');
        $ubah = $ownerBrand instanceof OwnerBrand;

        $aturan = [
            'namaBrand' => ['required', 'string', 'max:100'],
            'namaPerusahaan' => ['required', 'string', 'max:150'],
            'nama' => ['required', 'string', 'max:100'],
            // Tabel 12 alternative scenario: "email telah digunakan"
            'email' => ['required', 'email', 'max:100', Rule::unique('Pengguna', 'email')->ignore($ubah ? $ownerBrand->idPengguna : null, 'idPengguna')],
            'password' => [$ubah ? 'nullable' : 'required', 'string', 'min:8'],
            'noTelepon' => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'alamat' => ['required', 'string', 'max:1000'],
        ];

        // Status akun hanya bisa diubah di form Ubah. Akun baru selalu aktif.
        if ($ubah) {
            $aturan['statusAkun'] = ['required', Rule::in(['aktif', 'nonaktif', 'suspended'])];
        }

        return $aturan;
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'min.string' => ':attribute minimal :min karakter.',
            'max.string' => ':attribute maksimal :max karakter.',
            'in' => 'Pilihan :attribute tidak valid.',
            'regex' => 'Format :attribute tidak valid. Gunakan angka, 8 sampai 20 digit.',
        ];
    }

    public function attributes(): array
    {
        return [
            'namaBrand' => 'Nama Brand',
            'namaPerusahaan' => 'Nama Perusahaan',
            'nama' => 'Nama',
            'email' => 'Email',
            'password' => 'Password',
            'noTelepon' => 'No. Telepon',
            'alamat' => 'Alamat',
            'statusAkun' => 'Status Akun',
        ];
    }
}