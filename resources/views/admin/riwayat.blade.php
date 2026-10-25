{{-- Riwayat Campaign untuk Admin (wireframe Gambar 77, Tabel 26) --}}
@extends('layouts.dashboard')

@section('title', 'Riwayat Campaign - Aura Community')
@section('judul', 'Riwayat Campaign')

@push('scripts')
    <script src="{{ asset('js/filter.js') }}"></script>
@endpush

@section('isi')
    <form method="GET" action="{{ route('admin.riwayat') }}" class="toolbar" data-filter-otomatis>
        <div class="input-cari">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="cari" value="{{ $filter['cari'] }}" class="input-aura"
                   placeholder="Cari nama campaign" aria-label="Cari campaign">
        </div>
        <select name="kategori" class="input-aura input-pilih" aria-label="Filter kategori">
            <option value="">Semua Kategori</option>
            @foreach (config('aura.kategori') as $item)
                <option value="{{ $item }}" @selected($filter['kategori'] === $item)>{{ $item }}</option>
            @endforeach
        </select>
    </form>

    @if ($daftar->isEmpty())
        {{-- Tabel 26 alternative scenario --}}
        <div class="kosong">{{ array_filter($filter) ? 'Tidak ada riwayat yang cocok dengan pencarian atau filter.' : 'Riwayat campaign belum tersedia.' }}</div>
    @else
        <div class="tabel-wrap">
            <table class="tabel-aura">
                <thead>
                    <tr>
                        <th scope="col">Nama Campaign</th>
                        <th scope="col">Kategori Influencer</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Jumlah Influencer</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftar as $campaign)
                        <tr>
                            <td>{{ $campaign->namaCampaign }}</td>
                            <td>{{ $campaign->kategoriInfluencer }}</td>
                            <td>{{ $campaign->tanggalMulai->format('d/m/Y') }}</td>
                            <td>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</td>
                            <td>{{ $campaign->jumlahInfluencer }}</td>
                            <td><x-status jenis="statusCampaign" :nilai="$campaign->statusCampaign" /></td>
                            <td class="kolom-aksi">
                                {{-- Tabel 26 langkah 3-4: membuka detail riwayat --}}
                                <a class="tautan" href="{{ route('admin.campaign.detail', $campaign) }}">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $daftar->links('partials.paginasi', ['label' => 'campaign']) }}
    @endif
@endsection