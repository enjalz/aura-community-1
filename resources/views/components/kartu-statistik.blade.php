{{-- Empat kotak angka rekapitulasi (wireframe Gambar 76 dan 89).
     Contoh: <x-kartu-statistik :ringkasan="$ringkasan" />
     $ringkasan berasal dari App\Services\RekapCampaign::ringkasan() --}}
@props(['ringkasan'])

@php
    $daftarAngka = [
        'Influencer Selesai' => $ringkasan['influencer'],
        'Total Views' => $ringkasan['views'],
        'Total Likes' => $ringkasan['likes'],
        'Total Komentar' => $ringkasan['komentar'],
    ];
@endphp

<div class="statistik-grid">
    @foreach ($daftarAngka as $label => $angka)
        <div class="kartu-angka">
            <span>{{ $label }}</span>
            <strong>{{ number_format($angka, 0, ',', '.') }}</strong>
        </div>
    @endforeach
</div>