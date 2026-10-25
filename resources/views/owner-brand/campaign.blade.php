{{-- Campaign untuk Owner Brand (wireframe Gambar 88, Tabel 16).
     Hanya campaign yang diberi akses oleh Admin. --}}
@extends('layouts.dashboard')

@section('title', 'Campaign - Aura Community')
@section('judul', 'Campaign')

@push('scripts')
    <script src="{{ asset('js/filter.js') }}"></script>
@endpush

@section('isi')
    <form method="GET" action="{{ route('owner.campaign') }}" class="toolbar" data-filter-otomatis>
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
    </form>

    @if ($daftar->isEmpty())
        {{-- Tabel 16 alternative scenario --}}
        <div class="kosong">
            {{ array_filter($filter) ? 'Tidak ada campaign yang cocok dengan pencarian atau filter.' : 'Belum ada campaign yang dapat Anda akses. Akses campaign diberikan oleh Admin Aura Community.' }}
        </div>
    @else
        <div class="tabel-wrap">
            <table class="tabel-aura">
                <thead>
                    <tr>
                        <th scope="col">Nama Campaign</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Kuota Influencer</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftar as $campaign)
                        <tr @class(['is-terpilih' => $terpilih?->is($campaign)])>
                            <td>{{ $campaign->namaCampaign }}</td>
                            <td>{{ $campaign->tanggalMulai->format('d/m/Y') }}</td>
                            <td>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</td>
                            <td>{{ $campaign->kuotaInfluencer }}</td>
                            <td>
                                <span class="badge-status badge-{{ $campaign->statusCampaign }}">
                                    {{ config('aura.statusCampaign.' . $campaign->statusCampaign) }}
                                </span>
                            </td>
                            <td class="kolom-aksi">
                                <a class="tautan" href="{{ request()->fullUrlWithQuery(['pilih' => $campaign->idCampaign]) }}">Pilih</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $daftar->links('partials.paginasi', ['label' => 'campaign']) }}
    @endif

    {{-- ===== DETAIL CAMPAIGN ===== --}}
    @if ($terpilih)
        <section>
            <h2 class="judul-bagian">Detail Campaign</h2>

            <div class="panel-detail">
                <div class="panel-detail-utama">
                    <p class="label-grup">Informasi Campaign</p>
                    <dl class="info-daftar">
                        <div><dt>Nama Campaign</dt><dd>{{ $terpilih->namaCampaign }}</dd></div>
                        <div><dt>Deskripsi</dt><dd class="teks-panjang">{{ $terpilih->deskripsi }}</dd></div>
                        <div><dt>Target Audiens</dt><dd>{{ $terpilih->targetAudiens }}</dd></div>
                        <div><dt>Kategori Influencer</dt><dd>{{ $terpilih->kategoriInfluencer }}</dd></div>
                        <div><dt>Lokasi Campaign</dt><dd>{{ $terpilih->lokasiCampaign }}</dd></div>
                        <div><dt>Periode</dt><dd>{{ $terpilih->tanggalMulai->format('d/m/Y') }} - {{ $terpilih->tanggalSelesai->format('d/m/Y') }}</dd></div>
                        <div>
                            <dt>Ketentuan Platform</dt>
                            {{-- Contoh hasil: "Instagram, 1 video. TikTok, 1 video." (sesuai wireframe) --}}
                            <dd>
                                {{ $terpilih->ketentuanPlatform
                                    ->map(fn ($k) => $k->platform . ', ' . $k->jumlahPostingan . ' ' . strtolower(config('aura.jenisKonten.' . $k->jenisKonten)) . '.')
                                    ->join(' ') }}
                            </dd>
                        </div>
                        <div>
                            <dt>Status</dt>
                            <dd><span class="badge-status badge-{{ $terpilih->statusCampaign }}">{{ config('aura.statusCampaign.' . $terpilih->statusCampaign) }}</span></dd>
                        </div>
                    </dl>
                </div>

                <div class="garis-tegak" aria-hidden="true"></div>

                {{-- Perkembangan campaign (wireframe Gambar 88) --}}
                <div class="panel-detail-samping">
                    <p class="label-grup">Perkembangan Campaign</p>
                    <div class="kotak-angka">
                        <span>Influencer diterima</span>
                        <strong>{{ $perkembangan['diterima'] }} dari {{ $terpilih->kuotaInfluencer }}</strong>
                    </div>
                    <div class="kotak-angka">
                        <span>Draft disetujui</span>
                        <strong>{{ $perkembangan['draftDisetujui'] }}</strong>
                    </div>
                    <div class="kotak-angka">
                        <span>Insight disetujui</span>
                        <strong>{{ $perkembangan['insightDisetujui'] }}</strong>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection