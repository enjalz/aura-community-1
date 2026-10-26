{{-- Profil Influencer (wireframe Gambar 86, Tabel 7).
     Kiri: Data Diri. Kanan: Akun Media Sosial (termasuk demografi audiens) dan Rekening. --}}
@extends('layouts.dashboard')

@section('title', 'Profil - Aura Community')
@section('judul', 'Profil')

@section('isi')
    @php
        $rekening = $influencer->rekening;
    @endphp

    <form method="POST" action="{{ route('influencer.profil.perbarui') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- ===== DATA DIRI ===== --}}
            <div class="col-lg-6">
                <section class="form-kartu">
                    <h2 class="form-judul">Data Diri</h2>
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
                        <div class="col-md-6">
                            <div class="field">
                                <label for="kategori" class="field-label">Kategori</label>
                                <select id="kategori" name="kategori" @class(['input-aura', 'is-invalid' => $errors->has('kategori')])>
                                    @foreach (config('aura.kategori') as $item)
                                        <option value="{{ $item }}" @selected(old('kategori', $influencer->kategori) === $item)>{{ $item }}</option>
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
            </div>

            <div class="col-lg-6 tumpukan">
                {{-- ===== AKUN MEDIA SOSIAL ===== --}}
                <section class="form-kartu">
                    <h2 class="form-judul">Akun Media Sosial</h2>

                    @error('sosmed')
                        <div class="alert-aura alert-gagal" role="alert">{{ $message }}</div>
                    @enderror

                    @foreach (config('aura.platform') as $kunci => $namaPlatform)
                        @php
                            $akun = $influencer->akunSosmed->firstWhere('platform', $namaPlatform);
                        @endphp

                        @unless ($loop->first)
                            <hr class="garis">
                        @endunless

                        <p class="form-sub">{{ $namaPlatform }}</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-input :name="$kunci . '_username'" label="Username" :value="$akun?->username" placeholder="username" />
                            </div>
                            <div class="col-md-6">
                                <x-input :name="$kunci . '_pengikut'" label="Jumlah Pengikut" type="number" min="0" :value="$akun?->jumlahPengikut" />
                            </div>
                            <div class="col-12">
                                <x-input :name="$kunci . '_link'" label="Link Profil" type="url" :value="$akun?->linkProfil" />
                            </div>

                            @include('partials.input-demografi', ['kunci' => $kunci, 'nama' => $namaPlatform, 'akun' => $akun])
                        </div>
                    @endforeach
                </section>

                {{-- ===== REKENING ===== --}}
                <section class="form-kartu">
                    <h2 class="form-judul">Rekening</h2>
                    <p class="teks-bantuan">Dipakai untuk pembayaran fee campaign.</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field">
                                <label for="jenisRekening" class="field-label">Jenis Rekening</label>
                                <select id="jenisRekening" name="jenisRekening" @class(['input-aura', 'is-invalid' => $errors->has('jenisRekening')])>
                                    <option value="" @selected(! old('jenisRekening', $rekening?->jenisRekening))>Pilih jenis</option>
                                    <option value="bank" @selected(old('jenisRekening', $rekening?->jenisRekening) === 'bank')>Bank</option>
                                    <option value="ewallet" @selected(old('jenisRekening', $rekening?->jenisRekening) === 'ewallet')>E-Wallet</option>
                                </select>
                                @error('jenisRekening')
                                    <p class="pesan-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field">
                                <label for="namaPenyedia" class="field-label">Nama Penyedia</label>
                                <select id="namaPenyedia" name="namaPenyedia" @class(['input-aura', 'is-invalid' => $errors->has('namaPenyedia')])>
                                    <option value="" @selected(! old('namaPenyedia', $rekening?->namaPenyedia))>Pilih bank atau e-wallet</option>
                                    @foreach (config('aura.kanalPembayaran') as $kelompok => $daftarPenyedia)
                                        <optgroup label="{{ $kelompok }}">
                                            @foreach (array_keys($daftarPenyedia) as $penyedia)
                                                <option value="{{ $penyedia }}" @selected(old('namaPenyedia', $rekening?->namaPenyedia) === $penyedia)>{{ $penyedia }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('namaPenyedia')
                                    <p class="pesan-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <x-input name="nomorRekening" label="Nomor Rekening" inputmode="numeric" :value="$rekening?->nomorRekening" />
                        </div>
                        <div class="col-md-6">
                            <x-input name="namaPemilik" label="Nama Pemilik" :value="$rekening?->namaPemilik" />
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="form-aksi">
            <a href="{{ route('influencer.dashboard') }}" class="btn-aura btn-aura-outline">Batal</a>
            <button type="submit" class="btn-aura btn-aura-primary">Simpan Perubahan</button>
        </div>
    </form>
@endsection