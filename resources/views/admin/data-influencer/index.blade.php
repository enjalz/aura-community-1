{{-- Data Influencer --}}
@extends('layouts.dashboard')

@section('title', 'Data Influencer - Aura Community')
@section('judul', 'Data Influencer')

{{-- filter.js: pilihan kategori dan status langsung diterapkan tanpa tombol --}}
@push('scripts')
    <script src="{{ asset('js/filter.js') }}"></script>
@endpush

@section('isi')
    {{-- Toolbar. method GET supaya hasil pencarian muncul di URL (bisa di-refresh dan dibagikan). --}}
    <form method="GET" action="{{ route('admin.influencer') }}" class="toolbar" data-filter-otomatis>
        <div class="input-cari">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="cari" value="{{ $filter['cari'] }}" class="input-aura"
                   placeholder="Cari nama atau username influencer" aria-label="Cari influencer">
        </div>

        <select name="kategori" class="input-aura input-pilih" aria-label="Filter kategori">
            <option value="">Semua Kategori</option>
            @foreach ($kategori as $item)
                <option value="{{ $item }}" @selected($filter['kategori'] === $item)>{{ $item }}</option>
            @endforeach
        </select>

        <select name="status" class="input-aura input-pilih" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="aktif" @selected($filter['status'] === 'aktif')>Aktif</option>
            <option value="blacklist" @selected($filter['status'] === 'blacklist')>Blacklist</option>
        </select>

        <a href="{{ route('admin.influencer.tambah') }}" class="btn-aura btn-aura-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            Tambah Influencer
        </a>
    </form>

    @if ($daftar->isEmpty())
        <div class="kosong">
            {{ array_filter($filter) ? 'Tidak ada influencer yang cocok dengan pencarian atau filter.' : 'Belum ada data influencer.' }}
        </div>
    @else
        <div class="tabel-wrap">
            <table class="tabel-aura">
                <thead>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Pengikut Instagram</th>
                        <th scope="col">Pengikut TikTok</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftar as $influencer)
                        @php
                            $instagram = $influencer->akunSosmed->firstWhere('platform', 'Instagram');
                            $tiktok = $influencer->akunSosmed->firstWhere('platform', 'TikTok');
                        @endphp
                        <tr>
                            <td>{{ $influencer->pengguna->nama }}</td>
                            <td>{{ $influencer->kategori }}</td>
                            <td>{{ $instagram ? number_format($instagram->jumlahPengikut, 0, ',', '.') : '-' }}</td>
                            <td>{{ $tiktok ? number_format($tiktok->jumlahPengikut, 0, ',', '.') : '-' }}</td>
                            <td>
                                @if ($influencer->statusBlacklist)
                                    <span class="badge-status badge-blacklist">Blacklist</span>
                                @else
                                    <span class="badge-status badge-aktif">Aktif</span>
                                @endif
                            </td>
                            <td class="kolom-aksi">
                                <div class="aksi-baris">
                                    <a class="tautan" href="{{ route('admin.influencer.detail', $influencer) }}">Detail</a>
                                    <a class="tautan" href="{{ route('admin.influencer.ubah', $influencer) }}">Ubah</a>

                                    {{-- Hapus memakai form DELETE (bukan tautan), dengan konfirmasi --}}
                                    <form method="POST" action="{{ route('admin.influencer.hapus', $influencer) }}" class="form-sebaris"
                                          data-konfirmasi="Hapus data influencer {{ $influencer->pengguna->nama }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tautan-tombol">Hapus</button>
                                    </form>

                                    {{-- Wireframe: aksi Blacklist hanya ada untuk influencer yang belum di-blacklist --}}
                                    <a class="tautan" href="{{ route('admin.influencer.detail', $influencer) }}#blacklist">
                                        {{ $influencer->statusBlacklist ? 'Cabut Blacklist' : 'Blacklist' }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $daftar->links('partials.paginasi', ['label' => 'influencer']) }}
    @endif
@endsection