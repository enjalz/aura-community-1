<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// Validasi Mengelola Profil (Tabel 7) untuk Admin dan Owner Brand.
// Owner Brand mendapat dua kolom tambahan: Nama Brand dan Nama Perusahaan (wireframe Gambar 91).
class ProfilRequest extends FormRequest
{
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
        $pengguna = $this->user();

        $aturan = [
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('Pengguna', 'email')->ignore($pengguna->idPengguna, 'idPengguna')],
            'noTelepon' => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'alamat' => ['required', 'string', 'max:1000'],
            'password' => ['nullable', 'string', 'min:8'],
            // current_password: aturan bawaan Laravel yang mencocokkan isian dengan password akun yang sedang login
            'passwordSaatIni' => ['nullable', 'required_with:password', 'current_password'],
        ];

        if ($pengguna->peran() === 'owner_brand') {
            $aturan['namaBrand'] = ['required', 'string', 'max:100'];
            $aturan['namaPerusahaan'] = ['required', 'string', 'max:150'];
        }

        return $aturan;
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'max.string' => ':attribute maksimal :max karakter.',
            'password.min' => 'Password Baru minimal 8 karakter.',
            'regex' => 'Format :attribute tidak valid. Gunakan angka, 8 sampai 20 digit.',
            'passwordSaatIni.required_with' => 'Isi password saat ini untuk mengganti password.',
            'current_password' => 'Password saat ini salah.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama' => 'Nama',
            'email' => 'Email',
            'noTelepon' => 'No. Telepon',
            'alamat' => 'Alamat',
            'namaBrand' => 'Nama Brand',
            'namaPerusahaan' => 'Nama Perusahaan',
        ];
    }
}