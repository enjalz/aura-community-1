{{-- Profil Admin (wireframe Gambar 78, Tabel 7) --}}
@extends('layouts.dashboard')

@section('title', 'Profil - Aura Community')
@section('judul', 'Profil')

@section('isi')
    <h2 class="judul-bagian mb-0">Data Profil</h2>

    <form method="POST" action="{{ route('admin.profil.perbarui') }}" class="form-sempit" novalidate>
        @csrf
        @method('PUT')

        <section class="form-kartu">
            <div class="row g-3">
                <div class="col-md-6">
                    <x-input name="nama" label="Nama" :value="$pengguna->nama" />
                </div>
                <div class="col-md-6">
                    <x-input name="email" label="Email" type="email" :value="$pengguna->email" autocomplete="email" />
                </div>
                <div class="col-md-6">
                    <x-input name="noTelepon" label="No. Telepon" type="tel" :value="$pengguna->noTelepon" />
                </div>
                <div class="col-12">
                    <div class="field">
                        <label for="alamat" class="field-label">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3"
                                  @class(['input-aura', 'is-invalid' => $errors->has('alamat')])>{{ old('alamat', $pengguna->alamat) }}</textarea>
                        @error('alamat')
                            <p class="pesan-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="garis">
            <p class="form-sub">Ganti Password</p>
            @include('partials.form-password')
        </section>

        <div class="form-aksi">
            <a href="{{ route('admin.dashboard') }}" class="btn-aura btn-aura-outline">Batal</a>
            <button type="submit" class="btn-aura btn-aura-primary">Simpan Perubahan</button>
        </div>
    </form>
@endsection