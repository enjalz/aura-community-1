{{-- Wireframe Gambar 79 (menunggu), 80 (ditolak), 81 (diverifikasi) --}}
@extends('layouts.dashboard')

@section('title', 'Status Verifikasi - Aura Community')
@section('judul', 'Status Verifikasi')

@section('isi')
    <div class="status-box">
        <div class="status-ikon status-ikon-{{ $influencer->statusVerifikasi }}">
            <i class="bi bi-{{ $status['ikon'] }}" aria-hidden="true"></i>
        </div>
        <div class="status-teks">
            <h2>{{ $status['judul'] }}</h2>
            <p>{{ $status['deskripsi'] }}</p>
        </div>
        <span class="badge-status badge-{{ $influencer->statusVerifikasi }}">{{ $status['label'] }}</span>
    </div>

    <section>
        <h2 class="judul-bagian">Data Registrasi</h2>
        <dl class="daftar-data">
            <div class="daftar-data-baris"><dt>Nama</dt><dd>{{ $pengguna->nama }}</dd></div>
            <div class="daftar-data-baris"><dt>Email</dt><dd>{{ $pengguna->email }}</dd></div>
            <div class="daftar-data-baris"><dt>No. Telepon</dt><dd>{{ $pengguna->noTelepon }}</dd></div>
            <div class="daftar-data-baris"><dt>Alamat</dt><dd>{{ $pengguna->alamat }}</dd></div>
            <div class="daftar-data-baris"><dt>Kategori</dt><dd>{{ $influencer->kategori }}</dd></div>

            @foreach (config('aura.platform') as $namaPlatform)
                @php($akun = $akunSosmed->get($namaPlatform))
                <div class="daftar-data-baris">
                    <dt>Akun {{ $namaPlatform }}</dt>
                    <dd>
                        @if ($akun)
                            &#64;{{ $akun->username }} ({{ number_format($akun->jumlahPengikut, 0, ',', '.') }} pengikut)
                        @else
                            -
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </section>

    @if ($influencer->statusVerifikasi === 'menunggu')
        <div class="catatan">
            Menu lain dinonaktifkan sampai akun diverifikasi oleh Admin. Data registrasi tidak dapat diubah selama menunggu verifikasi.
        </div>
    @endif
@endsection