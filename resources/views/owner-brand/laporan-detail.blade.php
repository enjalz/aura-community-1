{{-- Detail Laporan Hasil Campaign untuk Owner Brand (wireframe Gambar 91, Tabel 25 langkah 7).
     Hanya bisa dilihat (read-only), tidak ada form. --}}
@extends('layouts.dashboard')

@section('title', 'Laporan ' . $campaign->namaCampaign . ' - Aura Community')
@section('judul', 'Laporan Hasil Campaign')

@section('isi')
    <nav class="mb-3" aria-label="Navigasi laporan">
        <a class="tautan" href="{{ route('owner.laporan') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Laporan Hasil Campaign</a>
        <span class="teks-redup"> / {{ $campaign->namaCampaign }}</span>
    </nav>

    <section class="panel-laporan">
        <div class="kepala-langkah">
            <h2 class="judul-panel mb-0">Laporan: {{ $campaign->namaCampaign }}</h2>
            <span class="teks-redup">Selesai {{ $campaign->tanggalSelesai->format('d/m/Y') }}</span>
        </div>
        <p class="teks-bantuan">
            Periode {{ $campaign->tanggalMulai->format('d/m/Y') }} - {{ $campaign->tanggalSelesai->format('d/m/Y') }}
            | Kategori {{ $campaign->kategoriInfluencer }}
            | {{ $campaign->ketentuanPlatform->pluck('platform')->unique()->join(', ', ' dan ') }}
        </p>

        @include('partials.laporan-performa')

        <p class="label-grup">Catatan Evaluasi</p>
        <div class="kotak-catatan teks-panjang">{{ $laporan->catatanEvaluasi }}</div>
    </section>
@endsection