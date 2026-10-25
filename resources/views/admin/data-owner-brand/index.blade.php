{{-- Data Owner Brand (wireframe Gambar 71, Tabel 13) --}}
@extends('layouts.dashboard')

@section('title', 'Data Owner Brand - Aura Community')
@section('judul', 'Data Owner Brand')

@php
    // Teks label untuk setiap nilai statusAkun (Tabel 27)
    $labelStatus = ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'suspended' => 'Suspended'];
@endphp

@section('isi')
    {{-- Toolbar: pencarian dikirim dengan menekan Enter --}}
    <form method="GET" action="{{ route('admin.owner-brand') }}" class="toolbar">
        <div class="input-cari">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="cari" value="{{ $cari }}" class="input-aura"
                   placeholder="Cari nama brand atau perusahaan" aria-label="Cari owner brand">
        </div>

        <a href="{{ route('admin.owner-brand.buat') }}" class="btn-aura btn-aura-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            Buat Akun Owner Brand
        </a>
    </form>

    @if ($daftar->isEmpty())
        <div class="kosong">
            {{ $cari !== '' ? 'Tidak ada owner brand yang cocok dengan pencarian.' : 'Belum ada akun owner brand.' }}
        </div>
    @else
        <div class="tabel-wrap">
            <table class="tabel-aura">
                <thead>
                    <tr>
                        <th scope="col">Nama Brand</th>
                        <th scope="col">Nama Perusahaan</th>
                        <th scope="col">Nama Pengguna</th>
                        <th scope="col">Email</th>
                        <th scope="col">Status Akun</th>
                        <th scope="col" class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftar as $ownerBrand)
                        <tr>
                            <td>{{ $ownerBrand->namaBrand }}</td>
                            <td>{{ $ownerBrand->namaPerusahaan }}</td>
                            <td>{{ $ownerBrand->pengguna->nama }}</td>
                            <td>{{ $ownerBrand->pengguna->email }}</td>
                            <td>
                                <span @class(['badge-status', $ownerBrand->pengguna->statusAkun === 'aktif' ? 'badge-aktif' : 'badge-nonaktif'])>
                                    {{ $labelStatus[$ownerBrand->pengguna->statusAkun] }}
                                </span>
                            </td>
                            <td class="kolom-aksi">
                                <div class="aksi-baris">
                                    <a class="tautan" href="{{ route('admin.owner-brand.detail', $ownerBrand) }}">Detail</a>
                                    <a class="tautan" href="{{ route('admin.owner-brand.ubah', $ownerBrand) }}">Ubah</a>
                                    <form method="POST" action="{{ route('admin.owner-brand.hapus', $ownerBrand) }}" class="form-sebaris"
                                          data-konfirmasi="Hapus akun {{ $ownerBrand->namaBrand }}? Akses ke campaign juga akan dihapus.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tautan-tombol">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $daftar->links('partials.paginasi', ['label' => 'owner brand']) }}
    @endif
@endsection