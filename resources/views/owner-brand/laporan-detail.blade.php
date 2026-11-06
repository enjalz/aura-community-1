{{-- Detail Laporan Hasil Campaign untuk Owner Brand (wireframe Gambar 91, Tabel 25 langkah 7).
     Hanya bisa dilihat (read-only), tidak ada form. --}}
@extends('layouts.dashboard')

@section('title', 'Laporan ' . $campaign->namaCampaign . ' - Aura Community')
@section('judul', 'Laporan Hasil Campaign')

@section('isi')
{{-- Tombol kembali ke daftar laporan, sama seperti halaman detail lainnya --}}
    <a href="{{ route('owner.laporan') }}" class="tautan-kembali mb-3">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        Kembali ke Laporan
    </a>

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