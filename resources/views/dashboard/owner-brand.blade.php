{{-- Dashboard Owner Brand (wireframe Gambar 87, Tabel 6) --}}
@extends('layouts.dashboard')

@section('title', 'Dashboard Owner Brand - Aura Community')
@section('judul', 'Dashboard')

@section('isi')
    <section>
        <h2 class="judul-bagian">Ringkasan Campaign</h2>
        <div class="statistik-grid statistik-grid-3">
            @foreach ($ringkasan as $label => $angka)
                <div class="kartu-angka">
                    <span>{{ $label }}</span>
                    <strong>{{ number_format($angka, 0, ',', '.') }}</strong>
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="judul-bagian">Campaign Saya</h2>
        @if ($campaignBerjalan->isEmpty())
            <div class="kosong">Belum ada campaign berjalan yang dapat Anda akses.</div>
        @else
            <div class="tabel-wrap">
                <table class="tabel-aura">
                    <thead>
                        <tr>
                            <th scope="col">Nama Campaign</th>
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
                                <td>{{ $campaign->tanggalMulai->format('d/m/Y') }}</td>
                                <td>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</td>
                                <td><x-status jenis="statusCampaign" :nilai="$campaign->statusCampaign" /></td>
                                <td class="kolom-aksi"><a class="tautan" href="{{ route('owner.campaign', ['pilih' => $campaign->idCampaign]) }}">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section>
        <h2 class="judul-bagian">Laporan Hasil Campaign</h2>
        @if ($laporan->isEmpty())
            <div class="kosong">Laporan hasil campaign belum tersedia.</div>
        @else
            <div class="tabel-wrap">
                <table class="tabel-aura">
                    <thead>
                        <tr>
                            <th scope="col">Nama Campaign</th>
                            <th scope="col">Tanggal Selesai</th>
                            <th scope="col">Views</th>
                            <th scope="col">Likes</th>
                            <th scope="col">Komentar</th>
                            <th scope="col" class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                                            @foreach ($laporan as $baris)
                        <tr>
                            <td>{{ $baris['campaign']->namaCampaign }}</td>
                            <td>{{ $baris['campaign']->tanggalSelesai->format('d/m/Y') }}</td>
                            <td>{{ number_format($baris['ringkasan']['views'], 0, ',', '.') }}</td>
                            <td>{{ number_format($baris['ringkasan']['likes'], 0, ',', '.') }}</td>
                            <td>{{ number_format($baris['ringkasan']['komentar'], 0, ',', '.') }}</td>
                            {{-- Tautan harus di dalam <td>, kalau tidak browser memindahkannya ke luar tabel --}}
                            <td class="kolom-aksi">
                                <a class="tautan" href="{{ route('owner.laporan.detail', $baris['campaign']) }}">Lihat Laporan</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection