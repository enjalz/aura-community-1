<?php

namespace App\Http\Controllers;

use App\Models\AkunSosmed;
use App\Models\Influencer;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Use case Registrasi Akun (Tabel 5). Hanya untuk influencer;
// akun Owner Brand dibuat oleh Admin.
class RegistrasiController extends Controller
{
    public function tampilkan(): View
    {
        return view('auth.register', [
            'kategori' => config('aura.kategori'),
            'platform' => config('aura.platform'),
        ]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        $platform = config('aura.platform');

        // Rapikan input sebelum divalidasi
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'instagram_username' => ltrim(trim((string) $request->input('instagram_username')), '@'),
            'tiktok_username' => ltrim(trim((string) $request->input('tiktok_username')), '@'),
        ]);

        $aturan = [
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('Pengguna', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'noTelepon' => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'kategori' => ['required', Rule::in(config('aura.kategori'))],
            'alamat' => ['required', 'string', 'max:1000'],
        ];

        $namaKolom = [
            'nama' => 'Nama',
            'email' => 'Email',
            'password' => 'Password',
            'noTelepon' => 'No. Telepon',
            'kategori' => 'Kategori',
            'alamat' => 'Alamat',
        ];

        // Satu platform boleh dikosongkan seluruhnya. Kalau salah satu kolomnya diisi,
        // ketiganya wajib diisi. Pasangan platform + username tidak boleh sudah terdaftar.
        foreach ($platform as $kunci => $nama) {
            $aturan["{$kunci}_username"] = [
                'nullable', 'string', 'max:100',
                "required_with:{$kunci}_pengikut,{$kunci}_link",
                Rule::unique('AkunSosmed', 'username')->where('platform', $nama),
            ];
            $aturan["{$kunci}_pengikut"] = [
                'nullable', 'integer', 'min:0', 'max:2147483647',
                "required_with:{$kunci}_username,{$kunci}_link",
            ];
            $aturan["{$kunci}_link"] = [
                'nullable', 'url:http,https', 'max:255',
                "required_with:{$kunci}_username,{$kunci}_pengikut",
            ];

            $namaKolom["{$kunci}_username"] = "Username {$nama}";
            $namaKolom["{$kunci}_pengikut"] = "Jumlah pengikut {$nama}";
            $namaKolom["{$kunci}_link"] = "Link profil {$nama}";
        }

        $pesan = [
            'required' => ':attribute wajib diisi.',
            'required_with' => ':attribute wajib diisi karena data lain pada platform ini sudah diisi.',
            'email' => 'Format email tidak valid.',
            'unique' => ':attribute sudah terdaftar.',
            'min.string' => ':attribute minimal :min karakter.',
            'max.string' => ':attribute maksimal :max karakter.',
            'min.numeric' => ':attribute tidak boleh negatif.',
            'confirmed' => 'Konfirmasi password tidak sama.',
            'in' => 'Pilihan :attribute tidak valid.',
            'integer' => ':attribute harus berupa angka bulat.',
            'url' => ':attribute harus berupa tautan lengkap, contoh https://instagram.com/namaanda.',
            'regex' => 'Format :attribute tidak valid. Gunakan angka, 8 sampai 20 digit.',
        ];

        $validator = Validator::make($request->all(), $aturan, $pesan, $namaKolom);

        // Minimal satu akun media sosial (wireframe Gambar 66)
        $validator->after(function ($validator) use ($request) {
            if (! $request->filled('instagram_username') && ! $request->filled('tiktok_username')) {
                $validator->errors()->add('sosmed', 'Isi minimal satu akun media sosial.');
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $data = $validator->validated();

        // Disimpan ke tiga tabel sekaligus. Kalau satu gagal, semuanya dibatalkan.
        DB::transaction(function () use ($data, $platform) {
            $pengguna = Pengguna::create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'password' => $data['password'],
                'noTelepon' => $data['noTelepon'],
                'alamat' => $data['alamat'],
                'statusAkun' => 'aktif',
            ]);

            Influencer::create([
                'idPengguna' => $pengguna->idPengguna,
                'kategori' => $data['kategori'],
                'statusVerifikasi' => 'menunggu',
            ]);

            foreach ($platform as $kunci => $nama) {
                if (empty($data["{$kunci}_username"])) {
                    continue;
                }

                AkunSosmed::create([
                    'idPengguna' => $pengguna->idPengguna,
                    'platform' => $nama,
                    'username' => $data["{$kunci}_username"],
                    'linkProfil' => $data["{$kunci}_link"],
                    'jumlahPengikut' => $data["{$kunci}_pengikut"],
                ]);
            }
        });

        return redirect()->route('login')->with(
            'sukses',
            'Registrasi berhasil. Data Anda akan diperiksa dan diverifikasi oleh Admin sebelum akun dapat digunakan.'
        );
    }
}