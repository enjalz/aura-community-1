{{-- Detail Data Owner Brand (Tabel 13, langkah 4: "Sistem menampilkan detail data Owner Brand") --}}
@extends('layouts.dashboard')

@php
    $pengguna = $ownerBrand->pengguna;
    $labelStatus = ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'suspended' => 'Suspended'];
    $labelCampaign = ['draft' => 'Draft', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'];
@endphp

@section('title', $ownerBrand->namaBrand . ' - Data Owner Brand - Aura Community')
@section('judul', 'Detail Owner Brand')

@section('isi')
    <div class="kepala-halaman">
        <a href="{{ route('admin.owner-brand') }}" class="tautan-kembali">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Kembali ke Data Owner Brand
        </a>

        <div class="aksi-kepala">
            <a href="{{ route('admin.owner-brand.ubah', $ownerBrand) }}" class="btn-aura btn-aura-outline">
                <i class="bi bi-pencil" aria-hidden="true"></i>
                Ubah
            </a>
            <form method="POST" action="{{ route('admin.owner-brand.hapus', $ownerBrand) }}"
                  data-konfirmasi="Hapus akun {{ $ownerBrand->namaBrand }}? Akses ke campaign juga akan dihapus.">
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
        <div class="col-lg-6 tumpukan">
            <section>
                <h2 class="judul-bagian">Data Brand</h2>
                <dl class="daftar-data">
                    <div class="daftar-data-baris"><dt>Nama Brand</dt><dd>{{ $ownerBrand->namaBrand }}</dd></div>
                    <div class="daftar-data-baris"><dt>Nama Perusahaan</dt><dd>{{ $ownerBrand->namaPerusahaan }}</dd></div>
                </dl>
            </section>

            <section>
                <h2 class="judul-bagian">Data Pengguna</h2>
                <dl class="daftar-data">
                    <div class="daftar-data-baris"><dt>Nama</dt><dd>{{ $pengguna->nama }}</dd></div>
                    <div class="daftar-data-baris"><dt>Email</dt><dd>{{ $pengguna->email }}</dd></div>
                    <div class="daftar-data-baris"><dt>No. Telepon</dt><dd>{{ $pengguna->noTelepon }}</dd></div>
                    <div class="daftar-data-baris"><dt>Alamat</dt><dd>{{ $pengguna->alamat }}</dd></div>
                    <div class="daftar-data-baris">
                        <dt>Status Akun</dt>
                        <dd>
                            <span @class(['badge-status', $pengguna->statusAkun === 'aktif' ? 'badge-aktif' : 'badge-nonaktif'])>
                                {{ $labelStatus[$pengguna->statusAkun] }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- Campaign yang bisa dilihat owner brand ini (dari tabel AksesCampaign).
             Akses diberikan di menu Campaign (Modul 4, Tabel 15). --}}
        <div class="col-lg-6">
            <section>
                <h2 class="judul-bagian">Campaign yang Dapat Diakses ({{ $ownerBrand->campaign->count() }})</h2>

                @if ($ownerBrand->campaign->isEmpty())
                    <div class="kosong">Belum ada campaign yang diberikan akses ke brand ini.</div>
                @else
                    <div class="tabel-wrap">
                        <table class="tabel-aura">
                            <thead>
                                <tr>
                                    <th scope="col">Nama Campaign</th>
                                    <th scope="col">Periode</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ownerBrand->campaign as $campaign)
                                    <tr>
                                        <td>{{ $campaign->namaCampaign }}</td>
                                        <td>{{ $campaign->tanggalMulai->format('d/m/Y') }} - {{ $campaign->tanggalSelesai->format('d/m/Y') }}</td>
                                        <td><span class="badge-status">{{ $labelCampaign[$campaign->statusCampaign] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection