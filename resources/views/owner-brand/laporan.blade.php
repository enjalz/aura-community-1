{{-- Daftar Laporan Hasil Campaign untuk Owner Brand (wireframe Gambar 90, Tabel 25 langkah 6) --}}
@extends('layouts.dashboard')

@section('title', 'Laporan Hasil Campaign - Aura Community')
@section('judul', 'Laporan Hasil Campaign')

@section('isi')
    <form method="GET" action="{{ route('owner.laporan') }}" class="toolbar">
        <div class="input-cari">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="cari" value="{{ $cari }}" class="input-aura"
                   placeholder="Cari nama campaign" aria-label="Cari campaign">
        </div>
    </form>

    @if ($daftar->isEmpty())
        {{-- Tabel 25 alternative scenario: laporan belum difinalisasi --}}
        <div class="kosong">
            {{ $cari !== '' ? 'Tidak ada laporan yang cocok dengan pencarian.' : 'Laporan hasil campaign belum tersedia. Laporan akan tampil setelah Admin mempublikasikannya.' }}
        </div>
    @else
        <div class="tabel-wrap">
            <table class="tabel-aura">
                <thead>
                    <tr>
                        <th scope="col">Nama Campaign</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Jumlah Influencer</th>
                        <th scope="col">Total Views</th>
                        <th scope="col" class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftar as $campaign)
                        <tr>
                            <td>{{ $campaign->namaCampaign }}</td>
                            <td>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</td>
                            <td>{{ $campaign->jumlahInfluencer }}</td>
                            <td>{{ number_format($ringkasan[$campaign->idCampaign]['views'], 0, ',', '.') }}</td>
                            <td class="kolom-aksi">
                                <a class="tautan" href="{{ route('owner.laporan.detail', $campaign) }}">Pilih</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $daftar->links('partials.paginasi', ['label' => 'laporan']) }}
    @endif
@endsection