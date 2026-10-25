{{-- Daftar Laporan Hasil Campaign untuk Admin (wireframe Gambar 76, Tabel 25).
     Detail laporan dibuka di halaman terpisah lewat tombol Pilih. --}}
@extends('layouts.dashboard')

@section('title', 'Laporan Hasil Campaign - Aura Community')
@section('judul', 'Laporan Hasil Campaign')

@push('scripts')
    <script src="{{ asset('js/filter.js') }}"></script>
@endpush

@section('isi')
    <form method="GET" action="{{ route('admin.laporan') }}" class="toolbar" data-filter-otomatis>
        <div class="input-cari">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="cari" value="{{ $filter['cari'] }}" class="input-aura"
                   placeholder="Cari nama campaign" aria-label="Cari campaign">
        </div>
        <select name="status" class="input-aura input-pilih" aria-label="Filter status laporan">
            <option value="">Semua Status Laporan</option>
            @foreach (config('aura.statusLaporan') as $nilai => $label)
                <option value="{{ $nilai }}" @selected($filter['status'] === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    @if ($daftar->isEmpty())
        <div class="kosong">
            {{ array_filter($filter) ? 'Tidak ada campaign yang cocok dengan pencarian atau filter.' : 'Belum ada campaign yang selesai. Laporan dapat dibuat setelah status campaign diubah menjadi Selesai.' }}
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
                        <th scope="col">Status Laporan</th>
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
                            <td><x-status jenis="statusLaporan" :nilai="$campaign->laporan?->statusLaporan ?? 'belum'" /></td>
                            <td class="kolom-aksi">
                                <a class="tautan" href="{{ route('admin.laporan.detail', $campaign) }}">Pilih</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $daftar->links('partials.paginasi', ['label' => 'campaign selesai']) }}
    @endif
@endsection