{{-- Laporan Hasil Campaign untuk Admin (wireframe Gambar 76, Tabel 25) --}}
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
                        <th scope="col">Status Laporan</th>
                        <th scope="col" class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftar as $campaign)
                        <tr @class(['is-terpilih' => $terpilih?->is($campaign)])>
                            <td>{{ $campaign->namaCampaign }}</td>
                            <td>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</td>
                            <td>{{ $campaign->jumlahInfluencer }}</td>
                            <td><x-status jenis="statusLaporan" :nilai="$campaign->laporan?->statusLaporan ?? 'belum'" /></td>
                            <td class="kolom-aksi">
                                <a class="tautan" href="{{ request()->fullUrlWithQuery(['pilih' => $campaign->idCampaign, 'halaman-rincian' => null]) }}">Pilih</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $daftar->links('partials.paginasi', ['label' => 'campaign selesai']) }}
    @endif

    {{-- ===== PANEL LAPORAN ===== --}}
    @if ($terpilih)
        @php
            $laporan = $terpilih->laporan;
            $statusLaporan = $laporan?->statusLaporan ?? 'belum';
            $sudahFinal = $statusLaporan === 'final';
        @endphp

        <section class="panel-laporan">
            <div class="kepala-langkah">
                <h2 class="judul-panel mb-0">Laporan: {{ $terpilih->namaCampaign }}</h2>
                <x-status jenis="statusLaporan" :nilai="$statusLaporan" />
            </div>

            {{-- Tabel 25 langkah 3: statistik diakumulasi otomatis --}}
            <p class="label-grup">Rekapitulasi (dihitung otomatis oleh sistem)</p>
            <x-kartu-statistik :ringkasan="$ringkasan" />

            <p class="label-grup">Rincian per Influencer</p>
            @if ($rincian->isEmpty())
                <div class="kosong">Belum ada insight yang disetujui untuk campaign ini.</div>
            @else
                <div class="tabel-wrap">
                    <table class="tabel-aura">
                        <thead>
                            <tr>
                                <th scope="col">Influencer</th>
                                <th scope="col">Akun Media Sosial</th>
                                <th scope="col">Views</th>
                                <th scope="col">Likes</th>
                                <th scope="col">Komentar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rincian as $hasil)
                                <tr>
                                    <td>{{ $hasil->pendaftaran->influencer->pengguna->nama }}</td>
                                    <td>{{ $hasil->akunSosmed->platform }} - &#64;{{ $hasil->akunSosmed->username }}</td>
                                    <td>{{ number_format((int) $hasil->jumlahViews, 0, ',', '.') }}</td>
                                    <td>{{ number_format((int) $hasil->jumlahLikes, 0, ',', '.') }}</td>
                                    <td>{{ number_format((int) $hasil->jumlahKomentar, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $rincian->links('partials.paginasi', ['label' => 'hasil pengerjaan']) }}
            @endif

            {{-- Tabel 25 langkah 4-5: catatan evaluasi, simpan draft atau finalisasi --}}
            @if ($sudahFinal)
                <p class="label-grup">Catatan Evaluasi</p>
                <div class="kotak-catatan teks-panjang">{{ $laporan->catatanEvaluasi }}</div>
                <p class="teks-bantuan">Dipublikasikan pada {{ $laporan->tanggalSelesaiCampaign->format('d/m/Y') }} dan dapat dilihat Owner Brand yang memiliki akses ke campaign ini.</p>
            @else
                <form method="POST" action="{{ route('admin.laporan.simpan', $terpilih) }}" class="tumpukan-kecil" novalidate>
                    @csrf
                    <div class="field">
                        <label for="catatanEvaluasi" class="label-grup">Catatan Evaluasi</label>
                        <textarea id="catatanEvaluasi" name="catatanEvaluasi" rows="5" placeholder="Tulis catatan evaluasi akhir campaign"
                                  @class(['input-aura', 'is-invalid' => $errors->has('catatanEvaluasi')])>{{ old('catatanEvaluasi', $laporan?->catatanEvaluasi) }}</textarea>
                        @error('catatanEvaluasi')
                            <p class="pesan-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-aksi mt-0">
                        <button type="submit" name="aksi" value="draft" class="btn-aura btn-aura-outline">Simpan Draft</button>
                        <button type="submit" name="aksi" value="final" class="btn-aura btn-aura-primary"
                                data-konfirmasi="Finalisasi dan publikasikan laporan ini? Laporan tidak dapat diubah lagi setelah dipublikasikan.">
                            Finalisasi dan Publikasikan
                        </button>
                    </div>
                </form>
            @endif
        </section>
    @endif
@endsection