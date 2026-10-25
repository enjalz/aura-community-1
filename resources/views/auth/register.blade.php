{{-- Wireframe Gambar 66 --}}
@extends('layouts.auth')

@section('title', 'Registrasi Influencer - Aura Community')

@section('tombol-navbar')
    <a href="{{ route('login') }}" class="btn-aura btn-aura-outline btn-aura-sm">Login</a>
@endsection

@section('isi')
    <div class="auth-card auth-card-lebar">
        <div class="auth-head">
            <h1>Registrasi Influencer</h1>
            <p>Isi data berikut untuk bergabung dengan Aura Community. Data akan diperiksa dan diverifikasi oleh Admin sebelum akun dapat digunakan.</p>
        </div>

        <form method="POST" action="{{ route('register.simpan') }}" class="auth-form" novalidate>
            @csrf

            <hr class="garis">
            <p class="label-kelompok">Data Diri</p>

            <div class="row g-3">
                <div class="col-md-6">
                    <x-input name="nama" label="Nama" placeholder="Nama lengkap" autocomplete="name" />
                </div>
                <div class="col-md-6">
                    <x-input name="email" label="Email" type="email" placeholder="email@contoh.com" autocomplete="email" />
                </div>
                <div class="col-md-6">
                    <x-input-password name="password" label="Password" placeholder="Minimal 8 karakter" autocomplete="new-password" />
                </div>
                <div class="col-md-6">
                    <x-input-password name="password_confirmation" label="Konfirmasi Password" placeholder="Ulangi password" autocomplete="new-password" />
                </div>
                <div class="col-md-6">
                    <x-input name="noTelepon" label="No. Telepon" type="tel" placeholder="Nomor WhatsApp aktif" autocomplete="tel" />
                </div>
                <div class="col-md-6">
                    <div class="field">
                        <label for="kategori" class="field-label">Kategori</label>
                        <select id="kategori" name="kategori" @class(['input-aura', 'is-invalid' => $errors->has('kategori')])>
                            <option value="" disabled @selected(! old('kategori'))>Pilih kategori</option>
                            @foreach ($kategori as $item)
                                <option value="{{ $item }}" @selected(old('kategori') === $item)>{{ $item }}</option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <p class="pesan-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="col-12">
                    <div class="field">
                        <label for="alamat" class="field-label">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3" placeholder="Alamat lengkap"
                                  @class(['input-aura', 'is-invalid' => $errors->has('alamat')])>{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <p class="pesan-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="garis">
            <p class="label-kelompok">Akun Media Sosial</p>
            <p class="teks-bantuan">Isi minimal satu akun.</p>

            @error('sosmed')
                <div class="alert-aura alert-gagal" role="alert">{{ $message }}</div>
            @enderror

            @foreach ($platform as $kunci => $nama)
                <p class="sub-platform">{{ $nama }}</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input :name="$kunci . '_username'" label="Username" placeholder="username" />
                    </div>
                    <div class="col-md-6">
                        <x-input :name="$kunci . '_pengikut'" label="Jumlah Pengikut" type="number" placeholder="0" min="0" />
                    </div>
                    <div class="col-12">
                        <x-input :name="$kunci . '_link'" label="Link Profil" type="url" :placeholder="'Tautan profil ' . $nama" />
                    </div>
                </div>
            @endforeach

            <hr class="garis">
            <button type="submit" class="btn-aura btn-aura-primary btn-aura-besar w-100">Daftar</button>
        </form>

        <p class="auth-foot">Sudah punya akun? <a href="{{ route('login') }}" class="tautan">Login</a></p>
    </div>
@endsection