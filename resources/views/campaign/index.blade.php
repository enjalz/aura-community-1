{{-- Daftar Campaign (wireframe Gambar 72, Tabel 14) --}}
@extends('layouts.dashboard')

@section('title', 'Campaign - Aura Community')
@section('judul', 'Campaign')

@push('scripts')
    <script src="{{ asset('js/filter.js') }}"></script>
@endpush

@section('isi')
    <form method="GET" action="{{ route('admin.campaign') }}" class="toolbar" data-filter-otomatis>
        <div class="input-cari">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="cari" value="{{ $filter['cari'] }}" class="input-aura"
                   placeholder="Cari nama campaign" aria-label="Cari campaign">
        </div>

        <select name="status" class="input-aura input-pilih" aria-label="Filter status">
            <option value="">Semua Status</option>
            @foreach (config('aura.statusCampaign') as $nilai => $label)
                <option value="{{ $nilai }}" @selected($filter['status'] === $nilai)>{{ $label }}</option>
            @endforeach
        </select>

        <a href="{{ route('admin.campaign.tambah') }}" class="btn-aura btn-aura-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            Tambah Campaign
        </a>
    </form>

    @if ($daftar->isEmpty())
        <div class="kosong">
            {{ array_filter($filter) ? 'Tidak ada campaign yang cocok dengan pencarian atau filter.' : 'Belum ada campaign.' }}
        </div>
    @else
        <div class="tabel-wrap">
            <table class="tabel-aura">
                <thead>
                    <tr>
                        <th scope="col">Nama Campaign</th>
                        <th scope="col">Kategori Influencer</th>
                        <th scope="col">Fee per Influencer</th>
                        <th scope="col">Kuota Influencer</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftar as $campaign)
                        <tr>
                            <td>{{ $campaign->namaCampaign }}</td>
                            <td>{{ $campaign->kategoriInfluencer }}</td>
                            <td><x-rupiah :nilai="$campaign->feePerInfluencer" /></td>
                            <td>{{ $campaign->kuotaInfluencer }}</td>
                            <td>{{ $campaign->tanggalMulai->format('d/m/Y') }}</td>
                            <td>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge-status badge-{{ $campaign->statusCampaign }}">
                                    {{ config('aura.statusCampaign.' . $campaign->statusCampaign) }}
                                </span>
                            </td>
                            <td class="kolom-aksi">
                                <div class="aksi-baris">
                                    <a class="tautan" href="{{ route('admin.campaign.detail', $campaign) }}">Detail</a>
                                    <a class="tautan" href="{{ route('admin.campaign.ubah', $campaign) }}">Ubah</a>
                                    <form method="POST" action="{{ route('admin.campaign.hapus', $campaign) }}" class="form-sebaris"
                                          data-konfirmasi="Hapus {{ $campaign->namaCampaign }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tautan-tombol">Hapus</button>
                                    </form>
                                    <a class="tautan" href="{{ route('admin.campaign.akses', $campaign) }}">Akses</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $daftar->links('partials.paginasi', ['label' => 'campaign']) }}
    @endif
@endsection