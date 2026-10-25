{{-- Dashboard Admin (wireframe Gambar 68, Tabel 6) --}}
@extends('layouts.dashboard')

@section('title', 'Dashboard Admin - Aura Community')
@section('judul', 'Dashboard')

@section('isi')
    <section>
        <h2 class="judul-bagian">Ringkasan Pengguna</h2>
        <div class="statistik-grid">
            @foreach ($ringkasanPengguna as $label => $angka)
                <div class="kartu-angka">
                    <span>{{ $label }}</span>
                    <strong>{{ number_format($angka, 0, ',', '.') }}</strong>
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="judul-bagian">Ringkasan Campaign</h2>
        <div class="statistik-grid statistik-grid-3">
            @foreach ($ringkasanCampaign as $label => $angka)
                <div class="kartu-angka">
                    <span>{{ $label }}</span>
                    <strong>{{ number_format($angka, 0, ',', '.') }}</strong>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Pekerjaan yang menunggu tindakan, dengan tombol menuju halamannya --}}
    <section>
        <h2 class="judul-bagian">Perlu Ditindak</h2>
        <div class="statistik-grid statistik-grid-3">
            @foreach ($perluDitindak as $tugas)
                <div class="kartu-tugas">
                    <div>
                        <span>{{ $tugas['label'] }}</span>
                        <strong>{{ number_format($tugas['jumlah'], 0, ',', '.') }}</strong>
                    </div>
                    <a href="{{ route($tugas['route']) }}" class="btn-aura btn-aura-outline btn-aura-sm">Lihat</a>
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="judul-bagian">Campaign Berjalan</h2>
        @if ($campaignBerjalan->isEmpty())
            <div class="kosong">Tidak ada campaign yang sedang berjalan.</div>
        @else
            <div class="tabel-wrap">
                <table class="tabel-aura">
                    <thead>
                        <tr>
                            <th scope="col">Nama Campaign</th>
                            <th scope="col">Kategori Influencer</th>
                            <th scope="col">Kuota Influencer</th>
                            <th scope="col">Tanggal Mulai</th>
                            <th scope="col">Tanggal Selesai</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaignBerjalan as $campaign)
                            <tr>
                                <td>{{ $campaign->namaCampaign }}</td>
                                <td>{{ $campaign->kategoriInfluencer }}</td>
                                <td>{{ $campaign->kuotaInfluencer }}</td>
                                <td>{{ $campaign->tanggalMulai->format('d/m/Y') }}</td>
                                <td>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</td>
                                <td><x-status jenis="statusCampaign" :nilai="$campaign->statusCampaign" /></td>
                                <td class="kolom-aksi"><a class="tautan" href="{{ route('admin.campaign.detail', $campaign) }}">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection