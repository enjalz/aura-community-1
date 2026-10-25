{{-- Form Buat Akun Owner Brand (Tabel 12) dan Ubah Data Owner Brand (Tabel 13).
     Susunan mengikuti wireframe Profil Owner Brand (Gambar 91): DATA BRAND, lalu DATA PENGGUNA.
     $ownerBrand = null saat Buat, berisi data saat Ubah. --}}
@extends('layouts.dashboard')

@php
    $ubah = $ownerBrand !== null;
    $pengguna = $ownerBrand?->pengguna;
    $kembali = $ubah ? route('admin.owner-brand.detail', $ownerBrand) : route('admin.owner-brand');
@endphp

@section('title', ($ubah ? 'Ubah Owner Brand' : 'Buat Akun Owner Brand') . ' - Aura Community')
@section('judul', $ubah ? 'Ubah Data Owner Brand' : 'Buat Akun Owner Brand')

@section('isi')
    <a href="{{ $kembali }}" class="tautan-kembali">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        Kembali
    </a>

    <form method="POST" action="{{ $ubah ? route('admin.owner-brand.perbarui', $ownerBrand) : route('admin.owner-brand.simpan') }}"
          class="form-sempit" novalidate>
        @csrf
        @if ($ubah)
            @method('PUT')
        @endif

        <section class="form-kartu">
            {{-- ===== DATA BRAND ===== --}}
            <p class="form-sub">Data Brand</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <x-input name="namaBrand" label="Nama Brand" :value="$ownerBrand?->namaBrand" placeholder="Contoh: GlowUp Skincare" />
                </div>
                <div class="col-md-6">
                    <x-input name="namaPerusahaan" label="Nama Perusahaan" :value="$ownerBrand?->namaPerusahaan" placeholder="Contoh: PT Glow Cantik Indonesia" />
                </div>
            </div>

            <hr class="garis">

            {{-- ===== DATA PENGGUNA (orang yang mewakili brand / PIC) ===== --}}
            <p class="form-sub">Data Pengguna</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <x-input name="nama" label="Nama" :value="$pengguna?->nama" placeholder="Nama penanggung jawab" />
                </div>
                <div class="col-md-6">
                    <x-input name="email" label="Email" type="email" :value="$pengguna?->email" placeholder="email@brand.com" autocomplete="off" />
                </div>
                <div class="col-md-6">
                    <x-input name="noTelepon" label="No. Telepon" type="tel" :value="$pengguna?->noTelepon" placeholder="Nomor WhatsApp aktif" />
                </div>
                <div class="col-md-6">
                    <x-input-password name="password"
                                      :label="$ubah ? 'Password Baru' : 'Password'"
                                      :placeholder="$ubah ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter'"
                                      autocomplete="new-password" />
                </div>
                <div class="col-12">
                    <div class="field">
                        <label for="alamat" class="field-label">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3" placeholder="Alamat lengkap perusahaan"
                                  @class(['input-aura', 'is-invalid' => $errors->has('alamat')])>{{ old('alamat', $pengguna?->alamat) }}</textarea>
                        @error('alamat')
                            <p class="pesan-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Status akun hanya muncul saat Ubah. Akun baru selalu aktif. --}}
                @if ($ubah)
                    <div class="col-md-6">
                        <div class="field">
                            <label for="statusAkun" class="field-label">Status Akun</label>
                            <select id="statusAkun" name="statusAkun" @class(['input-aura', 'is-invalid' => $errors->has('statusAkun')])>
                                @foreach (['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'suspended' => 'Suspended'] as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected(old('statusAkun', $pengguna->statusAkun) === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('statusAkun')
                                <p class="pesan-error">{{ $message }}</p>
                            @enderror
                            <p class="teks-bantuan">Akun yang tidak aktif tidak dapat login.</p>
                        </div>
                    </div>
                @endif
            </div>

            @unless ($ubah)
                {{-- Tabel 12, langkah 8 --}}
                <p class="teks-bantuan">Email dan password ini akan Anda berikan kepada pihak brand untuk login ke sistem.</p>
            @endunless
        </section>

        <div class="form-aksi">
            <a href="{{ $kembali }}" class="btn-aura btn-aura-outline">Batal</a>
            <button type="submit" class="btn-aura btn-aura-primary">{{ $ubah ? 'Simpan Perubahan' : 'Buat Akun' }}</button>
        </div>
    </form>
@endsection