{{-- Detail Data Influencer (Tabel 10 "melihat") dan Blacklist Influencer (Tabel 11) --}}
@extends('layouts.dashboard')

@php($pengguna = $influencer->pengguna)

@section('title', $pengguna->nama . ' - Data Influencer - Aura Community')
@section('judul', 'Detail Influencer')

@section('isi')
    <div class="kepala-halaman">
        <a href="{{ route('admin.influencer') }}" class="tautan-kembali">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Kembali ke Data Influencer
        </a>

        <div class="aksi-kepala">
            <a href="{{ route('admin.influencer.ubah', $influencer) }}" class="btn-aura btn-aura-outline">
                <i class="bi bi-pencil" aria-hidden="true"></i>
                Ubah
            </a>
            <form method="POST" action="{{ route('admin.influencer.hapus', $influencer) }}"
                  data-konfirmasi="Hapus data influencer {{ $pengguna->nama }}?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-aura btn-aura-outline">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        {{-- ===== KIRI: DATA ===== --}}
        <div class="col-lg-7 tumpukan">
            <section>
                <h2 class="judul-bagian">Data Diri</h2>
                <dl class="daftar-data">
                    <div class="daftar-data-baris"><dt>Nama</dt><dd>{{ $pengguna->nama }}</dd></div>
                    <div class="daftar-data-baris"><dt>Email</dt><dd>{{ $pengguna->email }}</dd></div>
                    <div class="daftar-data-baris"><dt>No. Telepon</dt><dd>{{ $pengguna->noTelepon }}</dd></div>
                    <div class="daftar-data-baris"><dt>Alamat</dt><dd>{{ $pengguna->alamat }}</dd></div>
                    <div class="daftar-data-baris"><dt>Kategori</dt><dd>{{ $influencer->kategori }}</dd></div>
                </dl>
            </section>

            <section>
                <h2 class="judul-bagian">Akun Media Sosial</h2>
                <dl class="daftar-data">
                    @foreach (config('aura.platform') as $namaPlatform)
                        @php($akun = $influencer->akunSosmed->firstWhere('platform', $namaPlatform))
                        <div class="daftar-data-baris">
                            <dt>{{ $namaPlatform }}</dt>
                            <dd>
                                @if ($akun)
                                    {{-- Status verifikasi akun media sosial --}}
                                    <div class="mb-1">
                                        @if ($akun->statusVerifikasi === 'menunggu')
                                            <span class="badge-status badge-menunggu">Menunggu verifikasi</span>
                                            <a class="tautan" href="{{ route('admin.verifikasi') }}">Periksa</a>
                                        @elseif ($akun->statusVerifikasi === 'ditolak')
                                            <span class="badge-status badge-ditolak">Ditolak</span>
                                        @else
                                            <span class="badge-status badge-diverifikasi">Terverifikasi</span>
                                        @endif
                                    </div>

                                    <span @class(['teks-redup' => ! $akun->terverifikasi()])>
                                        &#64;{{ $akun->username }} ({{ number_format($akun->jumlahPengikut, 0, ',', '.') }} pengikut)
                                    </span>
                                    @if (\Illuminate\Support\Str::startsWith($akun->linkProfil, ['https://', 'http://']))
                                        - <a class="tautan" href="{{ $akun->linkProfil }}" target="_blank" rel="noopener noreferrer">Lihat profil</a>
                                    @endif

                                    {{-- Data audiens dan bukti screenshot --}}
                                    <div class="teks-redup">
                                        Audiens: {{ $akun->domisiliAudiens ?? '-' }}
                                        · {{ $akun->usiaDominan ? config('aura.rentangUsia.' . $akun->usiaDominan) : '-' }}
                                        · {{ $akun->genderDominan ? config('aura.genderAudiens.' . $akun->genderDominan) : '-' }}
                                        @if ($akun->fileDemografi)
                                            · <a class="tautan" href="{{ route('berkas.demografi', $akun) }}" target="_blank" rel="noopener noreferrer">Screenshot</a>
                                        @endif
                                    </div>

                                    @if ($akun->statusVerifikasi === 'ditolak' && $akun->alasanPenolakan)
                                        <div class="teks-redup">Alasan ditolak: {{ $akun->alasanPenolakan }}</div>
                                    @endif
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section>
                <h2 class="judul-bagian">Rekening</h2>
                @if ($influencer->rekening)
                    <dl class="daftar-data">
                        <div class="daftar-data-baris"><dt>Jenis Rekening</dt><dd>{{ $influencer->rekening->jenisRekening === 'bank' ? 'Bank' : 'E-Wallet' }}</dd></div>
                        <div class="daftar-data-baris"><dt>Nama Penyedia</dt><dd>{{ $influencer->rekening->namaPenyedia }}</dd></div>
                        <div class="daftar-data-baris"><dt>Nomor Rekening</dt><dd>{{ $influencer->rekening->nomorRekening }}</dd></div>
                        <div class="daftar-data-baris"><dt>Nama Pemilik</dt><dd>{{ $influencer->rekening->namaPemilik }}</dd></div>
                    </dl>
                @else
                    {{-- Rekening diisi influencer sendiri saat mendaftar campaign (catatan wireframe Gambar 83) --}}
                    <div class="kosong">Influencer belum mengisi data rekening.</div>
                @endif
            </section>
        </div>

        {{-- ===== KANAN: STATUS BLACKLIST ===== --}}
        <div class="col-lg-5">
            <section id="blacklist">
                <h2 class="judul-bagian">Status Blacklist</h2>

                @if ($influencer->statusBlacklist)
                    {{-- Sudah di-blacklist: tampilkan tanggal dan alasan, beserta tombol untuk mencabutnya --}}
                    <form method="POST" action="{{ route('admin.influencer.cabut-blacklist', $influencer) }}" class="form-kartu"
                          data-konfirmasi="Cabut status blacklist {{ $pengguna->nama }}? Alasan dan tanggal blacklist akan dihapus.">
                        @csrf
                        <span class="badge-status badge-blacklist align-self-start">Blacklist</span>
                        <dl class="daftar-data daftar-data-tumpuk">
                            <div class="daftar-data-baris"><dt>Tanggal Blacklist</dt><dd>{{ $influencer->tanggalBlacklist?->format('d/m/Y') ?? '-' }}</dd></div>
                            <div class="daftar-data-baris"><dt>Alasan</dt><dd>{{ $influencer->alasanBlacklist ?: '-' }}</dd></div>
                        </dl>
                        <p class="teks-bantuan">Influencer yang di-blacklist tidak dapat mendaftar campaign. Setelah dicabut, influencer dapat mendaftar campaign kembali.</p>

                        <button type="submit" class="btn-aura btn-aura-outline">Cabut Blacklist</button>
                    </form>
                @else
                    {{-- Tabel 11 langkah 4: memberi status blacklist dan alasan jika diperlukan --}}
                    <form method="POST" action="{{ route('admin.influencer.blacklist', $influencer) }}" class="form-kartu"
                          data-konfirmasi="Masukkan {{ $pengguna->nama }} ke daftar blacklist?">
                        @csrf
                        <span class="badge-status badge-aktif align-self-start">Aktif</span>
                        <p class="teks-bantuan">Influencer yang di-blacklist tidak dapat mendaftar campaign.</p>

                        <div class="field">
                            <label for="alasanBlacklist" class="field-label">Alasan (opsional)</label>
                            <textarea id="alasanBlacklist" name="alasanBlacklist" rows="4"
                                      placeholder="Contoh: tidak mengirim konten sesuai tenggat"
                                      @class(['input-aura', 'is-invalid' => $errors->has('alasanBlacklist')])>{{ old('alasanBlacklist') }}</textarea>
                            @error('alasanBlacklist')
                                <p class="pesan-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn-aura btn-aura-primary">Blacklist Influencer</button>
                    </form>
                @endif
            </section>
        </div>
    </div>
@endsection