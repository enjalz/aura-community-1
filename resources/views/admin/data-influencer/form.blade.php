{{-- Form Tambah dan Ubah Data Influencer (Tabel 10).
     Tata letak mengikuti wireframe Profil Influencer (Gambar 86): Data Diri di kiri, Akun Media Sosial di kanan.
     $influencer = null saat Tambah, berisi data saat Ubah. --}}
@extends('layouts.dashboard')

@php
    $ubah = $influencer !== null;
    $pengguna = $influencer?->pengguna;
@endphp

@section('title', ($ubah ? 'Ubah' : 'Tambah') . ' Influencer - Aura Community')
@section('judul', $ubah ? 'Ubah Data Influencer' : 'Tambah Influencer')

@section('isi')
    <a href="{{ $ubah ? route('admin.influencer.detail', $influencer) : route('admin.influencer') }}" class="tautan-kembali">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        Kembali
    </a>

    <form method="POST" action="{{ $ubah ? route('admin.influencer.perbarui', $influencer) : route('admin.influencer.simpan') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if ($ubah)
            @method('PUT')
        @endif

        <div class="row g-4">
            {{-- ===== DATA DIRI ===== --}}
            <div class="col-lg-6">
                <section class="form-kartu">
                    <h2 class="form-judul">Data Diri</h2>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-input name="nama" label="Nama" :value="$pengguna?->nama" placeholder="Nama lengkap" />
                        </div>
                        <div class="col-md-6">
                            <x-input name="email" label="Email" type="email" :value="$pengguna?->email" placeholder="email@contoh.com" />
                        </div>
                        <div class="col-md-6">
                            <x-input name="noTelepon" label="No. Telepon" type="tel" :value="$pengguna?->noTelepon" placeholder="Nomor WhatsApp aktif" />
                        </div>
                        <div class="col-md-6">
                            <div class="field">
                                <label for="kategori" class="field-label">Kategori</label>
                                <select id="kategori" name="kategori" @class(['input-aura', 'is-invalid' => $errors->has('kategori')])>
                                    <option value="" disabled @selected(! old('kategori', $influencer?->kategori))>Pilih kategori</option>
                                    @foreach ($kategori as $item)
                                        <option value="{{ $item }}" @selected(old('kategori', $influencer?->kategori) === $item)>{{ $item }}</option>
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
                                          @class(['input-aura', 'is-invalid' => $errors->has('alamat')])>{{ old('alamat', $pengguna?->alamat) }}</textarea>
                                @error('alamat')
                                    <p class="pesan-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <x-input-password name="password"
                                              :label="$ubah ? 'Password Baru' : 'Password'"
                                              :placeholder="$ubah ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter'"
                                              autocomplete="new-password" />
                        </div>
                    </div>
                </section>
            </div>

            {{-- ===== AKUN MEDIA SOSIAL ===== --}}
            <div class="col-lg-6">
                <section class="form-kartu">
                    <h2 class="form-judul">Akun Media Sosial</h2>
                    <p class="teks-bantuan">Isi minimal satu akun. Demografi audiens boleh dikosongkan, karena dapat dilengkapi influencer melalui menu Profil.</p>

                    @error('sosmed')
                        <div class="alert-aura alert-gagal" role="alert">{{ $message }}</div>
                    @enderror

                    @foreach (config('aura.platform') as $kunci => $nama)
                        @php($akun = $influencer?->akunSosmed->firstWhere('platform', $nama))

                        <p class="form-sub">{{ $nama }}</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-input :name="$kunci . '_username'" label="Username" :value="$akun?->username" placeholder="username" />
                            </div>
                            <div class="col-md-6">
                                <x-input :name="$kunci . '_pengikut'" label="Jumlah Pengikut" type="number" min="0" :value="$akun?->jumlahPengikut" placeholder="0" />
                            </div>
                            <div class="col-12">
                                <x-input :name="$kunci . '_link'" label="Link Profil" type="url" :value="$akun?->linkProfil" :placeholder="'Tautan profil ' . $nama" />
                            </div>

                            @include('partials.input-demografi', ['kunci' => $kunci, 'nama' => $nama, 'akun' => $akun])
                        </div>
                    @endforeach
                </section>
            </div>
        </div>

        <div class="form-aksi">
            <a href="{{ $ubah ? route('admin.influencer.detail', $influencer) : route('admin.influencer') }}" class="btn-aura btn-aura-outline">Batal</a>
            <button type="submit" class="btn-aura btn-aura-primary">{{ $ubah ? 'Simpan Perubahan' : 'Simpan' }}</button>
        </div>
    </form>
@endsection