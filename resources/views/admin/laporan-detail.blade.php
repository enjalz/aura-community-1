{{-- Detail Laporan Hasil Campaign untuk Admin --}}
@extends('layouts.dashboard')

@section('title', 'Laporan ' . $campaign->namaCampaign . ' - Aura Community')
@section('judul', 'Laporan Hasil Campaign')

@section('isi')
    @php
        $statusLaporan = $laporan?->statusLaporan ?? 'belum';
        $sudahFinal = $statusLaporan === 'final';
    @endphp

    {{-- Navigasi kembali: ← Laporan Hasil Campaign / Nama Campaign --}}
    <nav class="mb-3" aria-label="Navigasi laporan">
        <a class="tautan" href="{{ route('admin.laporan') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Laporan Hasil Campaign</a>
        <span class="teks-redup"> / {{ $campaign->namaCampaign }}</span>
    </nav>

    <section class="panel-laporan">
        <div class="kepala-langkah">
            <h2 class="judul-panel mb-0">Laporan: {{ $campaign->namaCampaign }}</h2>
            <x-status jenis="statusLaporan" :nilai="$statusLaporan" />
        </div>
        <p class="teks-bantuan">
            Periode {{ $campaign->tanggalMulai->format('d/m/Y') }} - {{ $campaign->tanggalSelesai->format('d/m/Y') }}
            | Kategori {{ $campaign->kategoriInfluencer }}
            | {{ $campaign->ketentuanPlatform->pluck('platform')->unique()->join(', ', ' dan ') }}
        </p>

        {{-- Statistik, engagement, performa influencer, dan rincian konten --}}
        @include('partials.laporan-performa')

        {{-- Tabel 25 langkah 4-5: catatan evaluasi, simpan draft atau finalisasi --}}
        @if ($sudahFinal)
            <p class="label-grup">Catatan Evaluasi</p>
            <div class="kotak-catatan teks-panjang">{{ $laporan->catatanEvaluasi }}</div>
            <p class="teks-bantuan">Dipublikasikan pada {{ $laporan->tanggalSelesaiCampaign->format('d/m/Y') }} dan dapat dilihat Owner Brand yang memiliki akses ke campaign ini.</p>
        @else
            <form method="POST" action="{{ route('admin.laporan.simpan', $campaign) }}" class="tumpukan-kecil" novalidate>
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
@endsection