{{-- Riwayat Campaign untuk Influencer (wireframe Gambar 85, Tabel 26) --}}
@extends('layouts.dashboard')

@section('title', 'Riwayat Campaign - Aura Community')
@section('judul', 'Riwayat Campaign')

@section('isi')
    <form method="GET" action="{{ route('influencer.riwayat') }}" class="toolbar">
        <div class="input-cari">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="cari" value="{{ $cari }}" class="input-aura"
                   placeholder="Cari nama campaign" aria-label="Cari campaign">
        </div>
    </form>

    @if ($daftar->isEmpty())
        <div class="kosong">{{ $cari !== '' ? 'Tidak ada riwayat yang cocok dengan pencarian.' : 'Riwayat campaign belum tersedia.' }}</div>
    @else
        <div class="tabel-wrap">
            <table class="tabel-aura">
                <thead>
                    <tr>
                        <th scope="col">Nama Campaign</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Fee</th>
                        <th scope="col">Status Pembayaran</th>
                        <th scope="col" class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftar as $pendaftaran)
                        <tr>
                            <td>{{ $pendaftaran->campaign->namaCampaign }}</td>
                            <td>{{ $pendaftaran->campaign->tanggalMulai->format('d/m/Y') }}</td>
                            <td>{{ $pendaftaran->campaign->tanggalSelesai->format('d/m/Y') }}</td>
                            <td><x-rupiah :nilai="$pendaftaran->pembayaran?->jumlahPembayaran ?? $pendaftaran->campaign->feePerInfluencer" /></td>
                            <td><x-status jenis="statusPembayaran" :nilai="$pendaftaran->pembayaran?->statusPembayaran ?? 'belum'" /></td>
                            <td class="kolom-aksi">
                                <a class="tautan" href="{{ route('influencer.campaign', ['pilih' => $pendaftaran->idCampaign]) }}">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $daftar->links('partials.paginasi', ['label' => 'campaign']) }}
    @endif
@endsection